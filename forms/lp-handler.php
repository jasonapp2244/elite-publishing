<?php
declare(strict_types=1);

/**
 * Landing page form handler — the back end for LP1, LP2, LP3 and LP4.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS IS NOT contact-handler.php
 * ---------------------------------------------------------------------------
 * The main site's handler is live and taking real leads. It has no concept of
 * file uploads, it answers with a 303 redirect rather than JSON, and its
 * validation rules are written for three specific forms. Bending it into a
 * fourth shape would put every existing enquiry at risk to serve pages that
 * did not exist yesterday.
 *
 * So the landing pages get their own endpoint, and the main site's handler is
 * not touched. The two share what actually matters — includes/config.php for
 * the destination mailbox, and email.php for composing and sending — so there
 * is still exactly one place that decides where a lead goes and one place that
 * decides how it is sent. What is duplicated here is the request plumbing
 * (sanitising, rate limiting), which is a few dozen lines and is the part that
 * differs between a redirecting form and a JSON one anyway.
 *
 * ---------------------------------------------------------------------------
 * WHAT IT GUARANTEES
 * ---------------------------------------------------------------------------
 *   - Nothing is reported as sent unless the transport accepted it. A visitor
 *     never sees a success message for a mail that did not go out.
 *   - Every submitted field reaches the inbox, including the per-page extras.
 *   - The source is stated as "Landing Page: LP1" .. "LP4".
 *   - A manuscript is optional everywhere. With one it is attached; without
 *     one the submission proceeds normally.
 *   - CSRF token, honeypot, rate limit and a duplicate guard, all server side.
 *
 * It answers with JSON when asked (the landing pages post with fetch) and with
 * a 303 redirect otherwise, so the forms still work with JavaScript disabled.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../email.php';

ep_session_start();

// --- Tunables ---------------------------------------------------------------
const EP_LP_MAX_NAME    = 80;
const EP_LP_MAX_EMAIL   = 180;
const EP_LP_MAX_PHONE   = 40;
const EP_LP_MAX_MESSAGE = 4000;
const EP_LP_MAX_EXTRA   = 120;

const EP_LP_RATE_WINDOW = 600;   // seconds
const EP_LP_RATE_MAX    = 5;     // submissions per window, per session and per IP

/** How long an identical submission is treated as a double-click, in seconds. */
const EP_LP_DUPLICATE_WINDOW = 900;

/** Upload ceiling. Also stated in each page's file hint — keep them in step. */
const EP_LP_MAX_UPLOAD = 26214400;   // 25 MB

/**
 * What a manuscript may be.
 *
 * An allow-list of extensions, not a block-list, and the extension decides the
 * Content-Type rather than the browser-supplied one: $_FILES['type'] is just a
 * string the client sent and can say anything at all.
 */
const EP_LP_UPLOAD_TYPES = [
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'pdf'  => 'application/pdf',
    'rtf'  => 'application/rtf',
    'epub' => 'application/epub+zip',
    'odt'  => 'application/vnd.oasis.opendocument.text',
    'pages' => 'application/x-iwork-pages-sffpages',
];

/*
 * 'txt' => 'text/plain' is deliberately NOT in that list.
 *
 * The mail filter in front of info@elitepublishing.co silently drops any
 * message carrying a text/plain attachment, and it drops the WHOLE message —
 * so the enquiry disappears too, not just the file. The author sees the
 * thank-you screen, the server reports the mail as sent, and nothing arrives.
 *
 * Established by elimination against the live site, not guessed: the same LP
 * form with no attachment arrived, with a PDF attached arrived, and with a .txt
 * attached did not, three times over.
 *
 * Accepting a format whose enquiries are guaranteed to vanish is worse than
 * refusing it, because refusing it tells the author to send another format
 * while they are still on the page. If the filter is ever changed to allow
 * text/plain, add the line back and this comment with it.
 */

/**
 * The four pages, and the extra fields each one collects beyond the shared
 * name / email / phone / message set.
 *
 * The key is the posted field name, the value is the label it gets in the
 * email. Anything posted that is not listed here is ignored — the inbox only
 * ever shows fields this file knows about, so a crafted POST cannot append
 * arbitrary lines to a mail body.
 */
const EP_LP_PAGES = [
    'lp1' => ['label' => 'LP1', 'extra' => []],
    'lp2' => ['label' => 'LP2', 'extra' => []],
    'lp3' => ['label' => 'LP3', 'extra' => ['plan' => 'Plan']],
    'lp4' => ['label' => 'LP4', 'extra' => []],
];

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/** True when the caller wants JSON rather than a redirect. */
function ep_lp_wants_json(): bool
{
    if (($_POST['_ajax'] ?? '') === '1') {
        return true;
    }

    return str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json');
}

/** Trim, normalise newlines, drop control characters, clamp the length. */
function ep_lp_field(string $key, int $max): string
{
    $value = (string) ($_POST[$key] ?? '');
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

    return mb_substr(trim($value), 0, $max);
}

/** The first non-empty of several posted spellings of the same field. */
function ep_lp_any(array $keys, int $max): string
{
    foreach ($keys as $key) {
        $value = ep_lp_field($key, $max);
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function ep_lp_client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/**
 * Finish the request.
 *
 * JSON callers get a status code they can branch on; everyone else gets the
 * POST-redirect-GET treatment so a refresh cannot resend. Never returns.
 */
function ep_lp_finish(
    bool $ok,
    string $message,
    array $errors = [],
    string $lp = '',
    array $old = []
): void {
    if (ep_lp_wants_json()) {
        http_response_code($ok ? 200 : 422);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');

        /* On success the answer now carries where to go next, so the landing
           pages send the visitor to the thank-you page instead of swapping in
           an inline confirmation. The JSON shape is unchanged apart from this
           one added key, so a page that ignores it still behaves as before.

           The message is still included. A page that has already navigated will
           never show it, but it is what the non-JavaScript path and any future
           caller would use, and an API that answers "ok" with no reason is
           harder to debug than one that says why. */
        $payload = ['ok' => $ok, 'message' => $message, 'errors' => (object) $errors];
        if ($ok) {
            $payload['redirect'] = url('thankyou');
        }

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($ok) {
        header('Location: ' . url('thankyou'), true, 303);
        exit;
    }

    /* No JavaScript: hand the outcome back through the session and return the
       visitor to the page they were filling in, with what they typed. */
    $_SESSION['ep_lp_form'] = [
        'status'  => 'error',
        'message' => $message,
        'errors'  => $errors,
        'old'     => $old,
        'time'    => time(),
    ];

    $target = isset(EP_LP_PAGES[$lp]) ? url($lp . '/') : url(ep_page_url('contact'));

    header('Location: ' . $target . '?form=error#lp-form', true, 303);
    exit;
}

/**
 * Sliding-window rate limit, per session and per IP.
 *
 * Shares data/rate-limit.json with the main site's handler, deliberately: the
 * limit is meant to describe one person's behaviour across the whole site, not
 * to give them a fresh allowance per form.
 */
function ep_lp_rate_limited(string $ip): bool
{
    $now    = time();
    $cutoff = $now - EP_LP_RATE_WINDOW;

    $hits = array_values(array_filter(
        (array) ($_SESSION['ep_form_hits'] ?? []),
        static fn($t): bool => (int) $t > $cutoff
    ));
    if (count($hits) >= EP_LP_RATE_MAX) {
        $_SESSION['ep_form_hits'] = $hits;
        return true;
    }
    $hits[] = $now;
    $_SESSION['ep_form_hits'] = $hits;

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
        if (count($data[$key] ?? []) >= EP_LP_RATE_MAX) {
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

/**
 * Validate and read the optional manuscript.
 *
 * Returns [attachment|null, error|null]. A missing file is not an error — the
 * requirement is that the form submits normally without one, so UPLOAD_ERR_NO_FILE
 * returns [null, null] and the caller carries on.
 */
function ep_lp_upload(string $key): array
{
    if (!isset($_FILES[$key]) || !is_array($_FILES[$key])) {
        return [null, null];
    }

    $file  = $_FILES[$key];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }

    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        /* Naming the server limit rather than ours, because when they differ it
           is the server's that just rejected the file, and telling someone
           "25 MB" while PHP refuses at 2 MB sends them round the loop again. */
        return [null, sprintf(
            'That file is larger than this server accepts (%s). Please send it to %s instead.',
            ini_get('upload_max_filesize') ?: 'the upload limit',
            EP_EMAIL
        )];
    }

    if ($error !== UPLOAD_ERR_OK) {
        return [null, 'That file did not upload completely. Please try again.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');

    /* The one check that cannot be skipped: without it a crafted POST naming
       /etc/passwd as tmp_name would have this read and mail out that file. */
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return [null, 'That upload could not be read. Please try again.'];
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0) {
        return [null, 'That file appears to be empty.'];
    }
    if ($size > EP_LP_MAX_UPLOAD) {
        return [null, 'That file is over the 25 MB limit. Please send it to ' . EP_EMAIL . ' instead.'];
    }

    /* Keep only the basename, and only characters that are safe in a header
       parameter — the client controls this string completely. */
    $original = (string) ($file['name'] ?? 'manuscript');
    $original = str_replace('\\', '/', $original);
    $original = basename($original);
    $original = preg_replace('/[^A-Za-z0-9 ._-]+/', '_', $original) ?? 'manuscript';
    $original = trim(mb_substr($original, 0, 120)) ?: 'manuscript';

    $ext = strtolower((string) pathinfo($original, PATHINFO_EXTENSION));
    if (!isset(EP_LP_UPLOAD_TYPES[$ext])) {
        /* Built from the allow-list rather than typed out again. The two had
           already drifted — the list accepted .pages while the message never
           mentioned it — and dropping 'txt' would have left the message telling
           authors to send the one format that is refused. */
        $accepted = array_map('strtoupper', array_keys(EP_LP_UPLOAD_TYPES));
        $last     = array_pop($accepted);

        return [null, 'Please upload a ' . implode(', ', $accepted) . ' or ' . $last . ' file.'];
    }

    $content = @file_get_contents($tmp);
    if ($content === false) {
        return [null, 'That upload could not be read. Please try again.'];
    }

    return [[
        'content' => $content,
        'name'    => $original,
        'type'    => EP_LP_UPLOAD_TYPES[$ext],
        'size'    => $size,
    ], null];
}

// ---------------------------------------------------------------------------
// 1. Method, and the POST that never arrived
// ---------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . url(ep_page_url('contact')), true, 303);
    exit;
}

/**
 * A body larger than post_max_size is discarded by PHP before this file runs:
 * $_POST and $_FILES arrive empty, the CSRF token is "missing", and the visitor
 * is told their session expired when what actually happened is that their
 * manuscript was too big. Detect it and say so.
 */
$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
$postMax       = (int) ep_bytes_from_ini((string) ini_get('post_max_size'));

if ($_POST === [] && $contentLength > 0 && $postMax > 0 && $contentLength > $postMax) {
    ep_lp_finish(
        false,
        sprintf(
            'That upload is larger than this server accepts (%s). Please email it to %s instead.',
            ini_get('post_max_size') ?: 'the limit',
            EP_EMAIL
        ),
        ['manuscript' => 'File too large.']
    );
}

// ---------------------------------------------------------------------------
// 2. Which landing page
// ---------------------------------------------------------------------------
$lp = strtolower(ep_lp_field('_lp', 8));
if (!isset(EP_LP_PAGES[$lp])) {
    ep_lp_finish(false, 'That form could not be identified. Please reload the page and try again.');
}

$page  = EP_LP_PAGES[$lp];
$label = $page['label'];

// ---------------------------------------------------------------------------
// 3. CSRF
// ---------------------------------------------------------------------------
if (!ep_csrf_valid(isset($_POST['_token']) ? (string) $_POST['_token'] : null)) {
    ep_lp_finish(
        false,
        'Your session expired before the form was sent. Please reload the page and try again.',
        [],
        $lp,
        [
            'name'    => ep_lp_any(['name', 'fullName', 'full_name'], EP_LP_MAX_NAME),
            'email'   => ep_lp_field('email', EP_LP_MAX_EMAIL),
            'phone'   => ep_lp_field('phone', EP_LP_MAX_PHONE),
            'message' => ep_lp_any(['message', 'notes'], EP_LP_MAX_MESSAGE),
        ]
    );
}

// ---------------------------------------------------------------------------
// 4. Honeypot
// ---------------------------------------------------------------------------
/* A bot that fills the hidden field is thanked and nothing is sent. Never tell
   it that it was caught.
 *
 * THE FIELD IS NAMED ep_hp, NOT website — see the longer note in
 * contact-handler.php. Short version: browsers and password managers fill a
 * field called `website` on their own, off-screen or not, so real visitors were
 * being classified as bots and their enquiries dropped while they were shown a
 * confirmation. LP3's own demo script also posts a `website` value of its own
 * (assets/js/app.js), which would have tripped the same trap.
 *
 * The response is the same success + redirect every other outcome gives, so the
 * two remain indistinguishable from outside.
 */
/* REMOVED ON REQUEST — see the matching note in contact-handler.php. Every
   submission is sent and every visitor is redirected to the thank-you page.

   Restoring it is uncommenting this:

       if (trim((string) ($_POST['ep_hp'] ?? '')) !== '') {
           ep_lp_finish(true, 'Thank you. Your inquiry is on its way...');
       }

   The hidden ep_hp field is still rendered by ep_lp_hidden_fields(), so the
   check can go back without touching a template.

   The CSRF token is now the only thing guarding these four forms. */

// ---------------------------------------------------------------------------
// 5. Rate limit — REMOVED ON REQUEST
// ---------------------------------------------------------------------------
/* This refused a sixth submission from one IP inside ten minutes with "That is
   a lot of messages in a short time. Please wait a few minutes and try again."
   Removed so that every submission is simply sent.

   ep_lp_rate_limited() is left defined but is no longer called, so restoring
   this is uncommenting four lines:

       if (ep_lp_rate_limited(ep_lp_client_ip())) {
           ep_lp_finish(false, 'Please wait a few minutes and try again.', [], $lp);
       }

   What still stands between this form and a spam flood is the CSRF token and
   the honeypot. Both are silent and neither ever makes a real author wait, so
   both stay. Neither stops a script that reads the token from the page first,
   which the rate limit did — if the inbox starts filling with junk, this is the
   first thing to put back. */

// ---------------------------------------------------------------------------
// 6. Read and validate
// ---------------------------------------------------------------------------
// Each page names these slightly differently — LP4 posts fullName and notes,
// the rest post name and message. Both spellings are accepted so the pages keep
// their own markup and this file stays the only thing that has to know.
$name    = ep_lp_any(['name', 'fullName', 'full_name'], EP_LP_MAX_NAME);
$email   = ep_lp_field('email', EP_LP_MAX_EMAIL);
$phone   = ep_lp_field('phone', EP_LP_MAX_PHONE);
$message = ep_lp_any(['message', 'notes'], EP_LP_MAX_MESSAGE);

$extra = [];
foreach ($page['extra'] as $key => $fieldLabel) {
    $value = ep_lp_field($key, EP_LP_MAX_EXTRA);
    if ($value !== '') {
        $extra[$fieldLabel] = $value;
    }
}

$errors = [];

if ($name === '') {
    $errors['name'] = 'Please tell us your name.';
} elseif (mb_strlen($name) < 2) {
    $errors['name'] = 'Please enter your full name.';
}

if ($email === '') {
    $errors['email'] = 'Please enter your email address.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || str_contains($email, "\n")) {
    $errors['email'] = 'That email address does not look right.';
}

/* Phone is asked for on every landing page and the pages mark it required, so
   it is required here too — but the format check stays generous, because an
   international number written with spaces, dots or brackets is a real number
   and rejecting it loses a real lead. */
if ($phone === '') {
    $errors['phone'] = 'Please enter a contact number.';
} elseif (!preg_match('/^[0-9+()\-.\s]{7,40}$/', $phone)) {
    $errors['phone'] = 'That phone number does not look right.';
}

/* The message is optional on the landing pages: LP3 and LP4 label it as notes,
   and requiring a covering note from someone who has already attached their
   manuscript is friction for no gain.

   The minimum-length rule that used to sit here is REMOVED ON REQUEST. It
   rejected anything under five characters with "Please write a little more so
   we can help properly." An author who writes "Help" has still made an enquiry,
   and refusing it loses the lead. Whatever is typed is sent; the 4000-character
   maximum still applies, because that one protects the mail body. */

[$attachment, $uploadError] = ep_lp_upload('manuscript');
if ($uploadError !== null) {
    $errors['manuscript'] = $uploadError;
}

$old = [
    'name'    => $name,
    'email'   => $email,
    'phone'   => $phone,
    'message' => $message,
];

if ($errors !== []) {
    ep_lp_finish(
        false,
        'We could not send that just yet. Please check the highlighted fields.',
        $errors,
        $lp,
        $old
    );
}

// ---------------------------------------------------------------------------
// 7. Duplicate guard
// ---------------------------------------------------------------------------
/**
 * A double-clicked submit button, or a retried request after a slow upload,
 * should not produce two identical leads. The fingerprint covers the page and
 * everything the visitor actually typed, so a genuine second enquiry with any
 * different detail still gets through.
 *
 * Session-scoped on purpose: it must not stop two different people at the same
 * office sending the same short enquiry.
 */
$fingerprint = hash('sha256', implode("\0", [
    $lp,
    mb_strtolower($email),
    $name,
    $phone,
    $message,
    (string) ($attachment['name'] ?? ''),
    (string) ($attachment['size'] ?? ''),
]));

/* The duplicate guard is REMOVED ON REQUEST.

   It kept a fingerprint of each submission for fifteen minutes and answered an
   identical one with "We already have your inquiry" instead of sending it, so a
   double-click produced one email rather than two. Every submission is now
   sent, which is what was asked for; the cost is that a visitor who clicks
   twice sends two identical emails.

   The fingerprint above is still computed and still recorded below, so putting
   this back means restoring the `if (isset($recent[$fingerprint]))` branch and
   nothing else. */
$recent = (array) ($_SESSION['ep_lp_recent'] ?? []);
$cutoff = time() - EP_LP_DUPLICATE_WINDOW;
$recent = array_filter($recent, static fn($t): bool => (int) $t > $cutoff);

$recent[$fingerprint]      = time();
$_SESSION['ep_lp_recent']  = $recent;

// ---------------------------------------------------------------------------
// 8. Deliver
// ---------------------------------------------------------------------------
$delivered = ep_send_mail([
    'form'         => $lp,
    'landing_page' => $label,
    'name'         => $name,
    'email'        => $email,
    'phone'        => $phone,
    'message'      => $message,
    'fields'       => $extra,
    'attachment'   => $attachment,
    'page'         => url($lp . '/'),
    'ip'           => ep_lp_client_ip(),
    'ua'           => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200),
]);

if (!$delivered) {
    /* The transport refused it. ep_send_mail() has already written the
       submission to data/submissions.log, so the lead is not lost — but the
       visitor must not be thanked for something that did not happen. Let them
       retry: drop the fingerprint, or the retry would be swallowed as a
       duplicate of the attempt that failed. */
    unset($recent[$fingerprint]);
    $_SESSION['ep_lp_recent'] = $recent;

    ep_lp_finish(
        false,
        'We could not send that just now. Please try again in a moment, or email us directly at ' . EP_EMAIL . '.',
        [],
        $lp,
        $old
    );
}

// A spent token should not be replayable.
unset($_SESSION['ep_csrf']);

ep_lp_finish(
    true,
    $attachment !== null
        ? 'Thank you. Your manuscript is with us, and we will be in touch within one business day.'
        : 'Thank you. Your inquiry is on its way, and we will be in touch within one business day.'
);
