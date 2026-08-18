<?php
declare(strict_types=1);

/**
 * Contact form handler — the only back-end on the site.
 *
 * Serves all three forms:
 *   hero-contact  the enquiry card on the ten service pages (SPEC §B.13)
 *   wizard        the 4-step consultation block in components/cta-wizard.php,
 *                 which adds genre / stage / budget
 *   lp-contact    the home-page hero card, which adds a `campaign` value. The
 *                 name is historical — it served the campaign landing pages,
 *                 which are gone; renaming it would break live leads in flight.
 *
 * Everything is checked here, never in the browser: CSRF, honeypot, field
 * validation, and a per-session and per-IP rate limit. The response is always a
 * 303 redirect (POST-redirect-GET) — a visitor is never left looking at this
 * URL, and a refresh cannot resend.
 *
 *   success  ->  /thankyou
 *   failure  ->  back to the page that posted, with the errors and the values
 *                the visitor typed
 *
 * Sending is NOT done here. ep_send_mail() in email.php owns the message, the
 * headers and the transport; this file owns whether a submission deserves to be
 * sent at all. That split is the point: change how mail goes out by editing one
 * file, without touching a line of validation.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../email.php';

ep_session_start();

// --- Tunables ---------------------------------------------------------------
const EP_FORM_MAX_NAME    = 80;
const EP_FORM_MAX_EMAIL   = 180;
const EP_FORM_MAX_PHONE   = 40;
const EP_FORM_MIN_MESSAGE = 10;
const EP_FORM_MAX_MESSAGE = 4000;

const EP_RATE_WINDOW = 600;   // seconds
const EP_RATE_MAX    = 5;     // submissions per window, per session and per IP

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Where to send the browser afterwards.
 *
 * The Referer is only trusted when it points back into this site — otherwise a
 * crafted header could turn the handler into an open redirect.
 */
function ep_return_url(): string
{
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    if ($referer !== '') {
        $parts = parse_url($referer);
        $host  = $parts['host'] ?? '';
        $path  = $parts['path'] ?? '';

        /* HTTP_HOST carries the port, parse_url()'s host never does. Comparing
           them raw made "127.0.0.1" != "127.0.0.1:8765" on any install not
           served from port 80/443, so every rejected submission was bounced to
           the contact page instead of back to the form the visitor was filling
           in — losing what they had typed. Invisible in production, wrong
           everywhere else, and one line to make correct in both. */
        $self = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?? '';

        if ($host !== '' && strcasecmp($host, $self) === 0
            && str_starts_with($path, EP_BASE)
            && !str_contains($path, 'contact-handler.php')
        ) {
            return $path;
        }
    }

    return url(ep_page_url('contact'));
}

/** Send the visitor to the thank-you page. Never returns. */
function ep_form_thanks(): void
{
    /* Nothing is carried across in the URL. The thank-you page needs no state,
       and a query string is the kind of thing that ends up pasted into a
       support ticket with someone's email address in it. */
    header('Location: ' . url('thankyou'), true, 303);
    exit;
}

/** Store the flash, redirect, stop. Never returns. */
function ep_form_finish(string $status, string $message, array $errors = [], array $old = [], string $fragment = ''): void
{
    $_SESSION['ep_form'] = [
        'status'  => $status,
        'message' => $message,
        'errors'  => $errors,
        'old'     => $old,
        'time'    => time(),
        // Which form posted. A service page carries BOTH the hero contact form
        // and the consultation wizard, and they share this one flash slot — so
        // each must be able to tell whether the result is its own. Without
        // this, a failed wizard submission renders its errors inside the hero
        // form instead.
        'form'    => (($_POST['_form'] ?? '') === 'wizard') ? 'wizard' : 'contact',
    ];

    // ep_return_url() returns a path with no query string, so the flag cannot
    // accumulate across repeated submissions.
    $url = ep_return_url() . '?form=' . ($status === 'ok' ? 'ok' : 'error') . $fragment;

    header('Location: ' . $url, true, 303);
    exit;
}

/** The client address, as far as it can be trusted on a direct connection. */
function ep_client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/**
 * Sliding-window rate limit, counted per session and per IP.
 *
 * The session counter stops one browser hammering the form; the IP file stops
 * someone throwing away their cookie between posts. A JSON file under data/ is
 * enough at this traffic level and needs no database.
 */
function ep_rate_limited(string $ip): bool
{
    $now    = time();
    $cutoff = $now - EP_RATE_WINDOW;

    // --- per session ---
    $hits = array_values(array_filter(
        (array) ($_SESSION['ep_form_hits'] ?? []),
        static fn($t): bool => (int) $t > $cutoff
    ));
    if (count($hits) >= EP_RATE_MAX) {
        $_SESSION['ep_form_hits'] = $hits;
        return true;
    }
    $hits[] = $now;
    $_SESSION['ep_form_hits'] = $hits;

    // --- per IP ---
    $file = EP_ROOT . '/data/rate-limit.json';
    $fh   = @fopen($file, 'c+');
    if ($fh === false) {
        return false;               // cannot track it; do not block the visitor
    }

    $limited = false;
    if (flock($fh, LOCK_EX)) {
        $raw  = (string) stream_get_contents($fh);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = [];
        }

        foreach ($data as $key => $stamps) {
            $kept = array_values(array_filter((array) $stamps, static fn($t): bool => (int) $t > $cutoff));
            if ($kept === []) {
                unset($data[$key]);
            } else {
                $data[$key] = $kept;
            }
        }

        $key = hash('sha256', $ip);           // no raw addresses on disk
        if (count($data[$key] ?? []) >= EP_RATE_MAX) {
            $limited = true;
        } else {
            $data[$key][] = $now;
        }

        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, (string) json_encode($data));
        fflush($fh);
        flock($fh, LOCK_UN);
    }
    fclose($fh);

    return $limited;
}

/** Trim, normalise newlines, and drop control characters. */
function ep_field(string $key, int $max): string
{
    $value = (string) ($_POST[$key] ?? '');
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

    return mb_substr(trim($value), 0, $max);
}

/* ep_header_safe() and ep_log_submission() moved to email.php when sending was
   split out of this file. They are mail concerns, and having one copy of them
   is the whole reason for the split — this file requires email.php at the top,
   so both are available here. */

// ---------------------------------------------------------------------------
// 1. Method
// ---------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . url(ep_page_url('contact')), true, 303);
    exit;
}

$isWizard = ($_POST['_form'] ?? '') === 'wizard';

// Both forms now render an alert carrying id="form-result", so both get the
// fragment — previously only the wizard did, and only the hero form had the id.
$fragment = '#form-result';

// ---------------------------------------------------------------------------
// 2. CSRF — before anything is read, and before the rate limit is spent
// ---------------------------------------------------------------------------
if (!ep_csrf_valid(isset($_POST['_token']) ? (string) $_POST['_token'] : null)) {
    // Hand back what was typed. An expired session is not the visitor's fault,
    // and making them retype a long enquiry to satisfy a token they never saw
    // is how a genuine lead gets abandoned. Values still go through ep_field(),
    // so nothing unsanitised is echoed back.
    ep_form_finish(
        'error',
        'Your session expired before the form was sent. Please try again.',
        [],
        [
            'full_name' => ep_field('full_name', EP_FORM_MAX_NAME) ?: ep_field('name', EP_FORM_MAX_NAME),
            'email'     => ep_field('email', EP_FORM_MAX_EMAIL),
            'phone'     => ep_field('phone', EP_FORM_MAX_PHONE),
            'message'   => ep_field('message', EP_FORM_MAX_MESSAGE),
        ],
        $fragment
    );
}

// ---------------------------------------------------------------------------
// 3. Honeypot
// ---------------------------------------------------------------------------
// A bot that fills the hidden `website` field is told the submission worked and
// nothing is delivered. Never tell it that it was caught — that is free
// feedback for whoever wrote it.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    if (EP_DEBUG) {
        ep_log_submission([
            'time'    => date('c'),
            'outcome' => 'honeypot',
            'form'    => $isWizard ? 'wizard' : 'contact',
            'ip'      => ep_client_ip(),
        ]);
    }
    ep_form_finish('ok', 'Thank you. Your message has been sent, and we will be in touch shortly.', [], [], $fragment);
}

// ---------------------------------------------------------------------------
// 4. Rate limit — REMOVED ON REQUEST
// ---------------------------------------------------------------------------
/* This refused a sixth submission from one IP inside ten minutes and told the
   visitor to wait a few minutes. Removed so every submission is simply sent and
   the visitor goes straight to the thank-you page.

   ep_rate_limited() is left defined but no longer called. Restoring it is
   uncommenting:

       if (ep_rate_limited(ep_client_ip())) {
           ep_form_finish('error', 'Please wait a few minutes and try again.', [], [], $fragment);
       }

   The CSRF token and the honeypot still apply, and neither ever delays a real
   visitor. Neither stops a script that reads the token from the page first,
   which is what the rate limit was for. */

// ---------------------------------------------------------------------------
// 5. Read and validate
// ---------------------------------------------------------------------------
// The service-page hero card and the wizard use different names for the same
// field, so both spellings are accepted.
$name = ep_field('full_name', EP_FORM_MAX_NAME);
if ($name === '') {
    $name = ep_field('name', EP_FORM_MAX_NAME);
}
$email   = ep_field('email', EP_FORM_MAX_EMAIL);
$phone   = ep_field('phone', EP_FORM_MAX_PHONE);
$message = ep_field('message', EP_FORM_MAX_MESSAGE);

// Wizard answers are radio values; only the labels the wizard actually offers
// are accepted, so nothing arbitrary reaches the inbox.
$answers = [];
foreach (['genre', 'stage', 'budget'] as $step) {
    $answers[$step] = '';
}
foreach (ep_data_get('shared', 'wizard') as $step) {
    $key = $step['name'] ?? '';
    if (!array_key_exists($key, $answers)) {
        continue;
    }
    $allowed = array_column($step['options'] ?? [], 'label');
    $posted  = (string) ($_POST[$key] ?? '');
    if (in_array($posted, $allowed, true)) {
        $answers[$key] = $posted;
    }
}

$errors = [];

if ($name === '') {
    $errors['full_name'] = 'Please tell us your name.';
} elseif (mb_strlen($name) < 2) {
    $errors['full_name'] = 'Please enter your full name.';
}

if ($email === '') {
    $errors['email'] = 'Please enter your email address.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || str_contains($email, "\n")) {
    $errors['email'] = 'That email address does not look right.';
}

if ($phone !== '' && !preg_match('/^[0-9+()\-.\s]{5,40}$/', $phone)) {
    $errors['phone'] = 'That phone number does not look right.';
}

// The wizard's message box is optional in the design; the service-page hero
// form is a message form, so there it is required.
if ($message !== '' && mb_strlen($message) < EP_FORM_MIN_MESSAGE) {
    $errors['message'] = 'Please write a little more so we can help properly.';
} elseif (!$isWizard && $message === '') {
    $errors['message'] = 'Please tell us about your book.';
}

$old = [
    'full_name' => $name,
    'email'     => $email,
    'phone'     => $phone,
    'message'   => $message,
] + $answers;

if ($errors !== []) {
    ep_form_finish(
        'error',
        'We could not send that just yet. Please check the highlighted fields.',
        $errors,
        $old,
        $fragment
    );
}

// ---------------------------------------------------------------------------
// 6. Deliver
// ---------------------------------------------------------------------------
// Composing and sending belong to email.php. This hands it clean, validated
// values and does nothing with them but act on the answer.
$formKey = ($_POST['_form'] ?? '') === 'wizard' ? 'wizard'
         : (($_POST['_form'] ?? '') === 'lp-contact' ? 'lp-contact' : 'hero-contact');

$delivered = ep_send_mail([
    'form'     => $formKey,
    'name'     => $name,
    'email'    => $email,
    'phone'    => $phone,
    'message'  => $message,
    'genre'    => $answers['genre'],
    'stage'    => $answers['stage'],
    'budget'   => $answers['budget'],
    // Which service page or campaign produced the lead. Validated the same way
    // as everything else — these are echoed into a mail body.
    'service'  => ep_field('service', 120),
    'campaign' => ep_field('campaign', 40),
    'page'     => ep_return_url(),
    'ip'       => ep_client_ip(),
    'ua'       => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200),
]);

if (!$delivered) {
    /* The MTA refused it. ep_send_mail() has already written the submission to
       data/submissions.log, so the lead is not lost — but the visitor must not
       be thanked for something that did not happen. Send them back to the form
       with what they typed and a way to reach us that does not depend on the
       thing that just broke. */
    ep_form_finish(
        'error',
        'We could not send that just now. Please try again in a moment, or email us directly at ' . EP_EMAIL . '.',
        [],
        $old,
        $fragment
    );
}

// Fresh token for the next submission — a token that has been spent should not
// be replayable.
unset($_SESSION['ep_csrf']);

/* Success goes to a page of its own rather than back to the form with a banner.
   A dedicated URL is what analytics and ad platforms can count as a conversion,
   and the visitor gets a clean confirmation instead of hunting for a green
   message halfway up the page they were already on. */
ep_form_thanks();
