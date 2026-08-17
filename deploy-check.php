<?php
declare(strict_types=1);

/**
 * Deployment readiness check — run this ON the SiteGround server, right after
 * uploading, BEFORE announcing the site.
 *
 * ---------------------------------------------------------------------------
 * WHAT IT IS FOR
 * ---------------------------------------------------------------------------
 * Everything here is something that is true or false only on the live account
 * and cannot be established from a development machine: whether .htaccess is
 * actually in effect, whether PHP can write the lead log, whether the upload
 * limits match what the landing pages promise, whether the base path resolved
 * to a domain root, and whether files from the previous deploy are still lying
 * around. A local test suite passing says nothing about any of it.
 *
 * The HTTP checks are real loopback requests to this site's own URLs, so they
 * test the server's behaviour rather than reading the .htaccess and hoping.
 *
 * ---------------------------------------------------------------------------
 * HOW TO USE IT
 * ---------------------------------------------------------------------------
 * 1. Upload includes/mail-config.local.php (it is gitignored, so it will not
 *    arrive with a git deploy — copy it by hand). It already contains a key.
 *
 * 2. Visit  https://elitepublishing.co/deploy-check.php?key=<that key>
 *
 * 3. Fix every [BAD]. [WARN] is a judgement call, explained inline.
 *
 * 4. Then run mail-selftest.php, which covers mail specifically.
 *
 * 5. DELETE BOTH FILES when you are done. They report configuration.
 */

/* email.php, not just includes/mailer.php: EP_MAIL_FROM is defined in email.php
   and section 8 reads it. email.php pulls in the mailer itself, and its
   "refuse a direct hit" guard compares SCRIPT_FILENAME against its own path, so
   being required from here is fine. */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/email.php';

$cfg = ep_mail_config();
$key = (string) ($cfg['selftest_key'] ?? '');

if ($key === '' || !hash_equals($key, (string) ($_GET['key'] ?? ''))) {
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
    match ($status) { 'OK' => $ok++, 'WARN' => $warn++, default => $bad++ };
    printf("[%-4s] %-44s %s\n", $status, $label, $detail);
}
function heading(string $t): void { echo "\n$t\n" . str_repeat('-', strlen($t)) . "\n"; }
function note(string $t): void { echo "\n       " . str_replace("\n", "\n       ", $t) . "\n"; }

/** A loopback GET against this site. Returns [status, body, headers]. */
function fetchSelf(string $path, int $timeout = 12): array
{
    $url = rtrim(EP_ORIGIN, '/') . $path;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => false,   // self-request; the cert is not what is under test
            CURLOPT_USERAGENT      => 'ep-deploy-check',
        ]);
        $raw  = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hlen = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return [$code, substr($raw, $hlen), substr($raw, 0, $hlen)];
    }

    $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('~^HTTP/\S+\s+(\d{3})~', $h, $m)) { $code = (int) $m[1]; }
    }
    return [$code, (string) $body, implode("\n", $http_response_header ?? [])];
}

echo "Elite Publishing — deployment check\n";
echo "Run at " . date('Y-m-d H:i:s T') . " on " . ($_SERVER['HTTP_HOST'] ?? '?') . "\n";

// ---------------------------------------------------------------------------
heading('1. Environment');
// ---------------------------------------------------------------------------
line(EP_ENV === 'production' ? 'OK' : 'BAD', 'Environment resolves to production', EP_ENV);
if (EP_ENV !== 'production') {
    note("The site LOGS submissions instead of sending them in development.\n"
       . "EP_ENV is derived from the host name, so this being wrong on the live\n"
       . "domain means something odd about HTTP_HOST.");
}

line(
    version_compare(PHP_VERSION, '8.0', '>=') ? 'OK' : 'BAD',
    'PHP 8.0 or newer',
    PHP_VERSION . (version_compare(PHP_VERSION, '8.0', '>=') ? '' : ' — the code uses str_contains() and match()')
);

foreach (['mbstring' => 'BAD', 'fileinfo' => 'WARN', 'curl' => 'WARN', 'openssl' => 'WARN'] as $ext => $sev) {
    line(extension_loaded($ext) ? 'OK' : $sev, "Extension: $ext", extension_loaded($ext) ? 'loaded' : 'missing');
}
note("mbstring is required — every field is length-limited with mb_substr().\n"
   . "openssl is only needed if you switch mail to SMTP; curl only by this file.");

// ---------------------------------------------------------------------------
heading('2. Paths and URLs');
// ---------------------------------------------------------------------------
line(EP_BASE === '/' ? 'OK' : 'WARN', 'Base path is the domain root', EP_BASE);
if (EP_BASE !== '/') {
    note("The site is being served from a sub-directory. Everything still works —\n"
       . "url() and asset() adapt — but the .htaccess redirect for the legacy\n"
       . "/lp1.php URLs is written root-absolute and would send visitors to the\n"
       . "server root. Set RewriteBase in .htaccess if this is intentional.");
}
line('OK', 'Origin', EP_ORIGIN);
line('OK', 'Document root', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '?'));

// ---------------------------------------------------------------------------
heading('3. Files that must be present');
// ---------------------------------------------------------------------------
$required = [
    'includes/config.php', 'includes/functions.php', 'includes/mailer.php',
    'includes/mail-config.php', 'includes/lp-bootstrap.php',
    'includes/lp-header.php', 'includes/lp-footer.php',
    'includes/components/site-header.php', 'includes/components/site-footer.php',
    'forms/contact-handler.php', 'forms/lp-handler.php',
    'email.php', 'assets/css/lp-chrome.css', 'assets/css/tokens.css',
    'assets/css/main.css', 'assets/js/main.js',
    'lp1/index.php', 'lp2/index.php', 'lp3/index.php', 'lp4/index.php',
];
$missing = [];
foreach ($required as $r) {
    if (!is_file(EP_ROOT . '/' . $r)) { $missing[] = $r; }
}
line($missing === [] ? 'OK' : 'BAD', 'All required files uploaded',
    $missing === [] ? count($required) . ' checked' : 'MISSING: ' . implode(', ', $missing));

line(
    is_file(EP_ROOT . '/includes/mail-config.local.php') ? 'OK' : 'WARN',
    'includes/mail-config.local.php present',
    is_file(EP_ROOT . '/includes/mail-config.local.php') ? 'yes' : 'absent — it is gitignored, upload it by hand'
);

// ---------------------------------------------------------------------------
heading('4. Files from the PREVIOUS deploy that should be removed');
// ---------------------------------------------------------------------------
/* The live site was running a build in which the landing pages were flat files
   (lp1.php) rendered by a shared template. Those are all superseded. They are
   not dangerous — .htaccess 301s /lp1.php to /lp1/ — but they are dead code on
   a public server, and lp-page.php still contains the old markup. */
$stale = [
    'lp1.php', 'lp2.php', 'lp3.php', 'lp4.php',
    'includes/lp-page.php',
    'assets/css/p-lp.css',
    'data/landing.php',
];
$found = [];
foreach ($stale as $s) {
    if (is_file(EP_ROOT . '/' . $s)) { $found[] = $s; }
}
line($found === [] ? 'OK' : 'WARN', 'No superseded files left on the server',
    $found === [] ? 'clean' : 'DELETE: ' . implode(', ', $found));

foreach (['mail-selftest.php', 'deploy-check.php'] as $temp) {
    if (is_file(EP_ROOT . '/' . $temp)) {
        line('WARN', "Diagnostic still deployed: $temp", 'delete once the site is verified');
    }
}

// ---------------------------------------------------------------------------
heading('5. Writable paths');
// ---------------------------------------------------------------------------
$dataDir = EP_ROOT . '/data';
line(is_dir($dataDir) ? 'OK' : 'BAD', 'data/ exists', $dataDir);
line(is_writable($dataDir) ? 'OK' : 'BAD', 'data/ is writable',
    is_writable($dataDir) ? 'yes' : 'no — leads cannot be logged and the rate limit cannot work');

$probe = $dataDir . '/.deploy-write-test';
$wrote = @file_put_contents($probe, 'x') !== false;
@unlink($probe);
line($wrote ? 'OK' : 'BAD', 'PHP can actually create files in data/', $wrote ? 'verified' : 'write failed');

// ---------------------------------------------------------------------------
heading('6. Upload limits (the manuscript field)');
// ---------------------------------------------------------------------------
$uploadMax = ep_bytes_from_ini((string) ini_get('upload_max_filesize'));
$postMax   = ep_bytes_from_ini((string) ini_get('post_max_size'));
$wanted    = 26214400;

line((bool) ini_get('file_uploads') ? 'OK' : 'BAD', 'file_uploads enabled', ini_get('file_uploads') ? 'yes' : 'no');
line($uploadMax >= $wanted ? 'OK' : 'WARN', 'upload_max_filesize >= 25M', (string) ini_get('upload_max_filesize'));
line($postMax > $uploadMax ? 'OK' : 'WARN', 'post_max_size above upload_max_filesize', (string) ini_get('post_max_size'));
if ($uploadMax < $wanted || $postMax <= $uploadMax) {
    note("The landing pages offer a 25 MB manuscript upload. Raise these in\n"
       . "Site Tools -> Devs -> PHP Variables. Set post_max_size a little ABOVE\n"
       . "upload_max_filesize: the POST carries the file plus the other fields.\n"
       . "Do NOT use php_value lines in .htaccess — SiteGround runs PHP-FPM and\n"
       . "those produce a 500. Oversized posts are refused with a clear message\n"
       . "rather than failing silently, which is why this is only a WARN.");
}

// ---------------------------------------------------------------------------
heading('7. Live HTTP behaviour (.htaccess actually in effect)');
// ---------------------------------------------------------------------------
[$code] = fetchSelf(EP_BASE);
line($code === 200 ? 'OK' : 'BAD', 'Home page responds', "HTTP $code");

if ($code === 0) {
    note("Loopback requests are failing, so the rest of this section cannot run.\n"
       . "Some hosts block a server from requesting its own domain. Check the\n"
       . "remaining rows by loading the URLs in a browser instead.");
} else {
    // Pretty URLs => mod_rewrite is live.
    [$c] = fetchSelf(EP_BASE . 'about-our-company');
    line($c === 200 ? 'OK' : 'BAD', 'mod_rewrite: pretty URLs resolve', "/about-our-company -> HTTP $c");

    [$c] = fetchSelf(EP_BASE . 'services/ghostwriting');
    line($c === 200 ? 'OK' : 'BAD', 'mod_rewrite: service URLs resolve', "/services/ghostwriting -> HTTP $c");

    // The landing pages.
    foreach (['lp1', 'lp2', 'lp3', 'lp4'] as $lp) {
        [$c, $body] = fetchSelf(EP_BASE . $lp . '/');
        $hasChrome = str_contains($body, 'class="ep-header"') && str_contains($body, 'class="ep-footer"');
        line(
            $c === 200 && $hasChrome ? 'OK' : 'BAD',
            "Landing page /$lp/ serves with shared chrome",
            "HTTP $c" . ($c === 200 && !$hasChrome ? ' but header/footer missing' : '')
        );
    }

    // Legacy flat URLs must forward, not 404.
    [$c, , $h] = fetchSelf(EP_BASE . 'lp1.php');
    preg_match('~^location:\s*(\S+)~mi', $h, $loc);
    line(in_array($c, [301, 302], true) ? 'OK' : 'WARN', 'Legacy /lp1.php forwards to /lp1/',
        "HTTP $c" . (isset($loc[1]) ? ' -> ' . $loc[1] : ''));

    // Directories that must never be readable.
    foreach (['includes/config.php', 'data/shared.php', 'includes/mail-config.php'] as $blocked) {
        [$c] = fetchSelf(EP_BASE . $blocked);
        line(in_array($c, [403, 404], true) ? 'OK' : 'BAD', "Blocked over HTTP: $blocked", "HTTP $c");
    }
    // The lead log is the one that matters most — it holds personal data.
    [$c] = fetchSelf(EP_BASE . 'data/submissions.log');
    line(in_array($c, [403, 404], true) ? 'OK' : 'BAD', 'Blocked over HTTP: data/submissions.log', "HTTP $c");

    // The form endpoint must exist and must refuse GET with a redirect.
    [$c] = fetchSelf(EP_BASE . 'forms/lp-handler.php');
    line(in_array($c, [301, 302, 303], true) ? 'OK' : 'BAD', 'LP form endpoint reachable', "GET -> HTTP $c (303 expected)");

    // Asset caching: LP assets must NOT be immutable (they carry no ?v=).
    [$c, , $h] = fetchSelf(EP_BASE . 'lp3/assets/css/landing.css');
    $immutable = stripos($h, 'immutable') !== false;
    line(
        $c === 200 && !$immutable ? 'OK' : ($c !== 200 ? 'BAD' : 'WARN'),
        'LP assets are not cached as immutable',
        "HTTP $c" . ($immutable ? ' — served immutable; a CSS fix would never reach returning visitors' : '')
    );
}

// ---------------------------------------------------------------------------
heading('8. Mail (summary only)');
// ---------------------------------------------------------------------------
line('OK', 'Sends AS', EP_MAIL_FROM);
line(ep_mail_recipient_overridden() ? 'WARN' : 'OK', 'Delivers TO', ep_mail_recipient()
    . (ep_mail_recipient_overridden() ? '   <-- TEST OVERRIDE IS ACTIVE' : ''));
line('OK', 'Transport', (string) ($cfg['transport'] ?? 'mail'));
note("Run mail-selftest.php next — it checks SPF/DKIM/DMARC and can send a real\n"
   . "test message. Accepting a message is not the same as delivering it.");

// ---------------------------------------------------------------------------
heading('Summary');
// ---------------------------------------------------------------------------
printf("%d OK, %d warnings, %d failures\n", $ok, $warn, $bad);
echo $bad === 0
    ? "\nNothing is failing. Run mail-selftest.php, confirm a real email arrives,\n"
      . "then DELETE both diagnostics.\n"
    : "\nFix the [BAD] lines before putting this in front of visitors.\n";
