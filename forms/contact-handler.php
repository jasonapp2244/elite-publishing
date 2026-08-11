<?php
declare(strict_types=1);

/**
 * Contact form handler — the only back-end on the site.
 *
 * Serves two posters:
 *   1. the hero contact card on the ten service pages (SPEC §B.13 "Hero contact
 *      form"): full_name / email / phone / message, no `_form` value;
 *   2. the 4-step consultation wizard in includes/components/cta-wizard.php,
 *      which posts `_form=wizard` plus genre / stage / budget and the four
 *      step-4 fields.
 *
 * Everything is checked here, never in the browser: CSRF, honeypot, field
 * validation, and a per-session and per-IP rate limit. The response is always a
 * 303 redirect back to the page that posted (POST-redirect-GET), carrying a
 * flash in the session — a visitor is never left looking at this URL.
 *
 * Delivery: mail() in production, one JSON line in data/submissions.log in
 * development. data/ is denied by .htaccess, so the log is not web-readable.
 */

require_once __DIR__ . '/../includes/config.php';

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
        $self  = (string) ($_SERVER['HTTP_HOST'] ?? '');

        if ($host !== '' && strcasecmp($host, $self) === 0
            && str_starts_with($path, EP_BASE)
            && !str_contains($path, 'contact-handler.php')
        ) {
            return $path;
        }
    }

    return url('contact.php');
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

/**
 * A value safe to drop into a mail header.
 *
 * Anything that could start a new header — CR, LF, or a bare colon-prefixed
 * line — is removed rather than escaped, because a header is not the place to
 * be clever.
 */
function ep_header_safe(string $value): string
{
    return trim(str_replace(["\r", "\n", "\t"], ' ', $value));
}

/** Append one JSON line to data/submissions.log (development delivery). */
function ep_log_submission(array $entry): bool
{
    $dir = EP_ROOT . '/data';
    if (!is_dir($dir)) {
        return false;
    }

    $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return $line !== false
        && file_put_contents($dir . '/submissions.log', $line . "\n", FILE_APPEND | LOCK_EX) !== false;
}

// ---------------------------------------------------------------------------
// 1. Method
// ---------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . url('contact.php'), true, 303);
    exit;
}

$isWizard = ($_POST['_form'] ?? '') === 'wizard';
$fragment = $isWizard ? '#form-result' : '';

// ---------------------------------------------------------------------------
// 2. CSRF — before anything is read, and before the rate limit is spent
// ---------------------------------------------------------------------------
if (!ep_csrf_valid(isset($_POST['_token']) ? (string) $_POST['_token'] : null)) {
    ep_form_finish(
        'error',
        'Your session expired before the form was sent. Please try again.',
        [],
        [],
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
    ep_form_finish('ok', 'Thank you — your message has been sent. We will be in touch shortly.', [], [], $fragment);
}

// ---------------------------------------------------------------------------
// 4. Rate limit
// ---------------------------------------------------------------------------
if (ep_rate_limited(ep_client_ip())) {
    ep_form_finish(
        'error',
        'That is a lot of messages in a short time. Please wait a few minutes and try again.',
        [],
        [],
        $fragment
    );
}

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
        'We could not send that just yet — please check the highlighted fields.',
        $errors,
        $old,
        $fragment
    );
}

// ---------------------------------------------------------------------------
// 6. Deliver
// ---------------------------------------------------------------------------
$entry = [
    'time'    => date('c'),
    'outcome' => 'accepted',
    'form'    => $isWizard ? 'wizard' : 'contact',
    'name'    => $name,
    'email'   => $email,
    'phone'   => $phone,
    'genre'   => $answers['genre'],
    'stage'   => $answers['stage'],
    'budget'  => $answers['budget'],
    'message' => $message,
    'page'    => ep_return_url(),
    'ip'      => ep_client_ip(),
    'ua'      => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200),
];

$delivered = false;

if (EP_ENV === 'production') {
    $subject = ep_header_safe(
        ($isWizard ? 'Consultation request' : 'Website enquiry') . ' — ' . $name
    );

    $lines = [
        'Form:    ' . ($isWizard ? 'Consultation wizard' : 'Contact form'),
        'Name:    ' . $name,
        'Email:   ' . $email,
        'Phone:   ' . ($phone !== '' ? $phone : '—'),
    ];
    if ($isWizard) {
        $lines[] = 'Genre:   ' . ($answers['genre'] !== '' ? $answers['genre'] : '—');
        $lines[] = 'Stage:   ' . ($answers['stage'] !== '' ? $answers['stage'] : '—');
        $lines[] = 'Budget:  ' . ($answers['budget'] !== '' ? $answers['budget'] : '—');
    }
    $lines[] = 'Page:    ' . $entry['page'];
    $lines[] = '';
    $lines[] = $message !== '' ? $message : '(no message)';

    // From is a mailbox on our own domain — sending as the visitor gets the
    // mail rejected by SPF. Their address goes in Reply-To.
    $fromDomain = preg_replace('/[^a-z0-9.\-]/i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $headers    = [
        'From: ' . ep_header_safe(EP_NAME) . ' <no-reply@' . $fromDomain . '>',
        'Reply-To: ' . ep_header_safe($name) . ' <' . ep_header_safe($email) . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];

    $delivered = mail(
        ep_header_safe(EP_MAIL_TO),
        $subject,
        implode("\n", $lines),
        implode("\r\n", $headers)
    );

    if (!$delivered) {
        // Never lose a lead because the MTA is down — keep it on disk too.
        $entry['outcome'] = 'mail-failed';
        ep_log_submission($entry);
    }
} else {
    $delivered = ep_log_submission($entry);
}

if (!$delivered && EP_ENV !== 'production') {
    ep_form_finish(
        'error',
        'Something went wrong on our side and the message was not saved. Please email us directly.',
        [],
        $old,
        $fragment
    );
}

// Fresh token for the next submission — a token that has been spent should not
// be replayable.
unset($_SESSION['ep_csrf']);

ep_form_finish(
    'ok',
    'Thank you — your message has been sent. Our editorial team will be in touch shortly.',
    [],
    [],
    $fragment
);
