<?php
declare(strict_types=1);

/**
 * Live mail diagnostic — run this ON the SiteGround server.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS FILE EXISTS
 * ---------------------------------------------------------------------------
 * Every check in here is something that can only be answered from inside the
 * live account: whether Exim accepts a message, whether the SMTP password is
 * right, whether the domain publishes SPF, whether PHP can write the log.
 * None of it can be established from a development machine, and a form that
 * says "thank you" proves nothing about any of it.
 *
 * ---------------------------------------------------------------------------
 * HOW TO USE IT
 * ---------------------------------------------------------------------------
 * 1. includes/mail-config.local.php (gitignored) must carry a 'selftest_key'.
 *    One has already been generated in that file; upload it alongside this.
 *
 * 2. Visit  https://elitepublishing.co/mail-selftest.php?key=<that key>
 *    to run the read-only checks. Nothing is sent.
 *
 * 3. Add  &send=1  to actually send a test message. It goes to EP_MAIL_TO,
 *    or to the 'recipient' override if one is set — the report states which,
 *    in capitals, so the two can never be confused. Then go and look in that
 *    inbox, including its spam folder and SiteGround's separate spam
 *    quarantine. Delivery is the only check this script cannot make for you.
 *
 * 4. DELETE THIS FILE when the forms are confirmed working. It reports
 *    configuration and can send mail; it is not something to leave deployed.
 *
 * Without a key configured it refuses to run at all, so uploading it before
 * step 1 exposes nothing.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/email.php';

// ---------------------------------------------------------------------------
// Gate
// ---------------------------------------------------------------------------
$cfg = ep_mail_config();
$key = (string) ($cfg['selftest_key'] ?? '');

if ($key === '') {
    http_response_code(404);
    echo "Not configured. Set 'selftest_key' in includes/mail-config.local.php first.\n";
    exit;
}
if (!hash_equals($key, (string) ($_GET['key'] ?? ''))) {
    http_response_code(404);
    echo "Not found.\n";
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');

$ok = $warn = $bad = 0;

function line(string $status, string $label, string $detail = ''): void
{
    global $ok, $warn, $bad;
    match ($status) {
        'OK'   => $ok++,
        'WARN' => $warn++,
        default => $bad++,
    };
    printf("[%-4s] %-46s %s\n", $status, $label, $detail);
}

function heading(string $text): void
{
    echo "\n" . $text . "\n" . str_repeat('-', strlen($text)) . "\n";
}

$host   = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?? '';
$domain = strtolower(substr(strrchr(EP_MAIL_FROM, '@') ?: '@', 1));

echo "Elite Publishing — mail diagnostic\n";
echo "Run at " . date('Y-m-d H:i:s T') . " on " . ($host ?: 'unknown host') . "\n";

// ---------------------------------------------------------------------------
heading('1. Environment');
// ---------------------------------------------------------------------------
line(EP_ENV === 'production' ? 'OK' : 'BAD', 'Environment is production', EP_ENV);
if (EP_ENV !== 'production') {
    echo "\n       In development the site LOGS submissions instead of sending them.\n"
       . "       On the live domain this must say production, or no mail is ever sent.\n";
}
line('OK', 'PHP version', PHP_VERSION);
line('OK', 'Server software', (string) ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown'));

// ---------------------------------------------------------------------------
heading('2. Addresses');
// ---------------------------------------------------------------------------
line('OK', 'Sends AS (From / envelope sender)', EP_MAIL_FROM);
line('OK', 'Shown to visitors', EP_EMAIL);

if (ep_mail_recipient_overridden()) {
    /* Deliberately a warning, not an OK. This is a testing setting, and the
       symptom of forgetting it — the company inbox staying empty while the
       forms report success — is indistinguishable from the fault it was set
       to diagnose. */
    line('WARN', 'Delivers TO', ep_mail_recipient() . '   <-- OVERRIDE ACTIVE');
    echo "\n       *** MAIL IS BEING DIVERTED ***\n"
       . "       Enquiries are going to " . ep_mail_recipient() . ", NOT to "
       . EP_MAIL_TO . ".\n"
       . "       This comes from 'recipient' in includes/mail-config.local.php.\n"
       . "       Remove that line before this site takes real enquiries, or every\n"
       . "       lead will land in that inbox and " . EP_MAIL_TO . " will stay empty.\n";
} else {
    line('OK', 'Delivers TO', EP_MAIL_TO);
}

$fromDomain = strtolower(substr(strrchr(EP_MAIL_FROM, '@') ?: '@', 1));
$hostBase   = preg_replace('/^www\./', '', strtolower($host)) ?? '';

line(
    $fromDomain === $hostBase ? 'OK' : 'BAD',
    'From address is on this domain',
    $fromDomain === $hostBase
        ? $fromDomain
        : "From is @$fromDomain but the site is served from $hostBase — SPF will fail"
);

line(
    filter_var(ep_mail_recipient(), FILTER_VALIDATE_EMAIL) ? 'OK' : 'BAD',
    'Recipient address is well formed',
    ep_mail_recipient()
);

/* A configured override that does not parse is silently ignored by
   ep_mail_recipient(), so say so here — otherwise the only symptom is mail
   quietly continuing to go to the address you thought you had replaced. */
$rawOverride = trim((string) ($cfg['recipient'] ?? ''));
if ($rawOverride !== '' && !filter_var($rawOverride, FILTER_VALIDATE_EMAIL)) {
    line('BAD', 'Recipient override is unusable', "\"$rawOverride\" is not a valid address — it is being ignored");
}

echo "\n       The From mailbox must EXIST in Site Tools -> Email -> Accounts.\n"
   . "       This script cannot verify that a mailbox exists; the test send below\n"
   . "       is what proves it, because a missing mailbox bounces or is refused.\n";

// ---------------------------------------------------------------------------
heading('3. PHP mail configuration');
// ---------------------------------------------------------------------------
$disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
line(
    function_exists('mail') && !in_array('mail', $disabled, true) ? 'OK' : 'BAD',
    'mail() is available',
    in_array('mail', $disabled, true) ? 'disabled via disable_functions' : 'yes'
);
line('OK', 'sendmail_path', (string) (ini_get('sendmail_path') ?: '(not set — normal on some hosts)'));
line(
    (int) ini_get('max_execution_time') === 0 || (int) ini_get('max_execution_time') >= 30 ? 'OK' : 'WARN',
    'max_execution_time',
    (string) ini_get('max_execution_time') . 's'
);

// ---------------------------------------------------------------------------
heading('4. Uploads (the manuscript field)');
// ---------------------------------------------------------------------------
$uploadMax = ep_bytes_from_ini((string) ini_get('upload_max_filesize'));
$postMax   = ep_bytes_from_ini((string) ini_get('post_max_size'));
$wanted    = 26214400; // 25 MB, the limit the landing pages advertise

line(ini_get('file_uploads') ? 'OK' : 'BAD', 'file_uploads enabled', ini_get('file_uploads') ? 'yes' : 'no');
line(
    $uploadMax >= $wanted ? 'OK' : 'WARN',
    'upload_max_filesize >= 25M',
    (string) ini_get('upload_max_filesize')
);
line(
    $postMax >= $uploadMax ? 'OK' : 'WARN',
    'post_max_size >= upload_max_filesize',
    (string) ini_get('post_max_size')
);

if ($uploadMax < $wanted || $postMax < $uploadMax) {
    echo "\n       The pages offer a 25 MB manuscript upload. Raise these in\n"
       . "       Site Tools -> Devs -> PHP Variables (set post_max_size a little\n"
       . "       ABOVE upload_max_filesize — the POST carries the file plus the\n"
       . "       other fields). Do NOT use php_value lines in .htaccess on\n"
       . "       SiteGround: it runs PHP-FPM, and those cause a 500.\n"
       . "       Submissions that exceed the limit are refused with a clear\n"
       . "       message rather than failing silently, so this is a WARN.\n";
}

// ---------------------------------------------------------------------------
heading('5. Writable paths');
// ---------------------------------------------------------------------------
foreach ([
    'data/'                 => EP_ROOT . '/data',
    'data/submissions.log'  => EP_ROOT . '/data/submissions.log',
] as $label => $path) {
    $writable = is_dir($path) ? is_writable($path) : (is_file($path) ? is_writable($path) : is_writable(dirname($path)));
    line($writable ? 'OK' : 'WARN', "Writable: $label", $writable ? 'yes' : 'no — leads cannot be logged');
}

// ---------------------------------------------------------------------------
heading('6. File encoding');
// ---------------------------------------------------------------------------
/**
 * A byte-order mark before "<?php" is output, not code. PHP sends it to the
 * browser, headers go out early, and every redirect on the site breaks — and
 * in a file that declares strict_types it is an outright fatal.
 *
 * This is checked because mail-config.local.php is the one file people edit by
 * hand on the server, and Windows Notepad and PowerShell's
 * `Set-Content -Encoding UTF8` both add a BOM without saying so.
 */
$bomFound = false;
foreach ([
    'includes/mail-config.local.php',
    'includes/mail-config.php',
    'includes/config.php',
    'email.php',
] as $relative) {
    $path = EP_ROOT . '/' . $relative;
    if (!is_file($path)) {
        continue;
    }
    $head = (string) @file_get_contents($path, false, null, 0, 3);
    if ($head === "\xEF\xBB\xBF") {
        line('BAD', "No BOM in $relative", 'starts with a UTF-8 BOM — re-save as UTF-8 without BOM');
        $bomFound = true;
    }
}
if (!$bomFound) {
    line('OK', 'No byte-order marks in the config files', 'clean');
}

// ---------------------------------------------------------------------------
heading('7. Transport');
// ---------------------------------------------------------------------------
$transport = (string) ($cfg['transport'] ?? 'mail');
line('OK', 'Configured transport', $transport);

if ($transport === 'smtp') {
    line(
        ($cfg['username'] ?? '') !== '' ? 'OK' : 'BAD',
        'SMTP username set',
        ($cfg['username'] ?? '') !== '' ? (string) $cfg['username'] : 'empty'
    );
    line(
        ($cfg['password'] ?? '') !== '' ? 'OK' : 'BAD',
        'SMTP password set',
        ($cfg['password'] ?? '') !== '' ? '(set)' : 'empty'
    );
    line('OK', 'SMTP endpoint', sprintf('%s:%s (%s)', $cfg['host'] ?? '', $cfg['port'] ?? '', $cfg['secure'] ?: 'no encryption'));

    /* Connect and authenticate, but send nothing. This separates "the
       credentials are wrong" from "the message was rejected", which are
       otherwise indistinguishable from a failed send. */
    $error = null;
    $probe = ep_smtp_send(EP_MAIL_FROM, [], '', '', $cfg, $error);
    if ($error !== null && str_contains($error, 'Every recipient was rejected')) {
        // Reached RCPT with no recipients — connection and AUTH both succeeded.
        line('OK', 'SMTP connect + authenticate', 'succeeded');
    } elseif ($probe) {
        line('OK', 'SMTP connect + authenticate', 'succeeded');
    } else {
        line('BAD', 'SMTP connect + authenticate', (string) $error);
    }
} else {
    echo "\n       Using PHP mail() -> SiteGround's local Exim. This needs no\n"
       . "       credentials and is the right default. If the test send below is\n"
       . "       accepted but nothing arrives, switch to SMTP: set transport,\n"
       . "       host, username and password in includes/mail-config.local.php.\n";
}

// ---------------------------------------------------------------------------
heading('8. DNS — SPF, DKIM, DMARC');
// ---------------------------------------------------------------------------
if (!function_exists('dns_get_record')) {
    line('WARN', 'DNS lookups', 'dns_get_record() unavailable — check these in Site Tools instead');
} else {
    $mx = @dns_get_record($domain, DNS_MX) ?: [];
    line($mx !== [] ? 'OK' : 'WARN', 'MX records exist', $mx !== []
        ? implode(', ', array_map(static fn($r) => (string) ($r['target'] ?? ''), $mx))
        : 'none found');

    $txt = @dns_get_record($domain, DNS_TXT) ?: [];
    $spf = '';
    foreach ($txt as $record) {
        $value = (string) ($record['txt'] ?? '');
        if (stripos($value, 'v=spf1') === 0) {
            $spf = $value;
        }
    }
    line($spf !== '' ? 'OK' : 'BAD', 'SPF record published', $spf !== '' ? $spf : 'none — mail will often be marked as spam');

    $dmarcTxt = @dns_get_record('_dmarc.' . $domain, DNS_TXT) ?: [];
    $dmarc = '';
    foreach ($dmarcTxt as $record) {
        $value = (string) ($record['txt'] ?? '');
        if (stripos($value, 'v=DMARC1') === 0) {
            $dmarc = $value;
        }
    }
    line($dmarc !== '' ? 'OK' : 'WARN', 'DMARC record published', $dmarc !== '' ? $dmarc : 'none');

    /* DKIM lives at <selector>._domainkey, and the selector is chosen by
       whoever signs. SiteGround's is "default"; Google Workspace uses
       "google". Probing the common ones is the best a script can do without
       being told which is in use. */
    $foundSelector = '';
    foreach (['default', 'sg', 'google', 'k1', 'selector1', 'mail'] as $selector) {
        $records = @dns_get_record($selector . '._domainkey.' . $domain, DNS_TXT) ?: [];
        foreach ($records as $record) {
            if (str_contains((string) ($record['txt'] ?? ''), 'p=')) {
                $foundSelector = $selector;
                break 2;
            }
        }
    }
    line(
        $foundSelector !== '' ? 'OK' : 'WARN',
        'DKIM key found',
        $foundSelector !== ''
            ? "selector \"$foundSelector\""
            : 'none at the usual selectors — enable DKIM in Site Tools -> Email -> Authentication'
    );
}

// ---------------------------------------------------------------------------
heading('9. Test send');
// ---------------------------------------------------------------------------
if (($_GET['send'] ?? '') !== '1') {
    echo "Skipped. Add &send=1 to the URL to send a real message to " . ep_mail_recipient() . ".\n";
} else {
    $sent = ep_send_mail([
        'form'         => 'lp1',
        'landing_page' => 'DIAGNOSTIC',
        'name'         => 'Mail diagnostic',
        'email'        => ep_mail_recipient(),
        'phone'        => '',
        'message'      => "This is an automated test from mail-selftest.php.\n"
                        . "If you are reading it in the inbox, the transport works end to end.",
        'fields'       => [
            'Transport' => $transport,
            'Host'      => $host,
            'Sent at'   => date('Y-m-d H:i:s T'),
        ],
        'attachment'   => [
            /* A tiny attachment on purpose: it exercises the same MIME path the
               manuscript upload uses, so this test covers both message shapes. */
            'content' => "Attachment test.\nIf this file opens, manuscript uploads will arrive too.\n",
            'name'    => 'diagnostic.txt',
            'type'    => 'text/plain',
            'size'    => 74,
        ],
        'page'         => 'mail-selftest.php',
        'ip'           => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        'ua'           => 'mail-selftest',
    ]);

    line($sent ? 'OK' : 'BAD', 'Transport accepted the message', $sent ? 'yes' : 'no — see data/submissions.log');

    echo "\n       ACCEPTED IS NOT DELIVERED. Go and look in " . ep_mail_recipient() . " now,\n"
       . "       and in its spam folder. If it is not in either within a few\n"
       . "       minutes, the message was accepted by the server and dropped\n"
       . "       later — that is an SPF/DKIM or mailbox problem, not a code one.\n"
       . "\n       On SiteGround also check the spam QUARANTINE, which is separate\n"
       . "       from the Junk folder and holds mail that never reaches the\n"
       . "       mailbox at all: the domain's MX points at\n"
       . "       mx10.antispam.mailspamprotection.com, so every message passes\n"
       . "       through that filter before it is delivered.\n";
}

// ---------------------------------------------------------------------------
heading('Summary');
// ---------------------------------------------------------------------------
printf("%d OK, %d warnings, %d failures\n", $ok, $warn, $bad);
echo $bad === 0
    ? "\nNothing is failing. If mail still does not arrive, it is delivery, not configuration:\n"
      . "run with &send=1 and check the spam folder.\n"
    : "\nFix the [BAD] lines above first — each one is enough on its own to stop mail arriving.\n";

echo "\nDelete this file once the forms are confirmed working.\n";
