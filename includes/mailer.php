<?php
declare(strict_types=1);

/**
 * Mail plumbing — MIME assembly and the two transports.
 *
 * email.php decides WHAT to send and to whom. This file knows how to turn that
 * into bytes on the wire, and nothing about enquiries, forms or landing pages.
 * The split exists so attachment encoding and SMTP can be fixed without
 * reading a line of enquiry logic, and vice versa.
 *
 * Nothing here is specific to Elite Publishing except the defaults it reads
 * from mail-config.php.
 */

if (!defined('EP_ROOT')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Transport settings: mail-config.php, overlaid with mail-config.local.php
 * when that exists.
 *
 * The local file is gitignored and holds the SMTP password, so it is optional
 * by design — a checkout with no local file falls back to mail(), which needs
 * no secrets.
 */
function ep_mail_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = require __DIR__ . '/mail-config.php';

    $local = __DIR__ . '/mail-config.local.php';
    if (is_file($local)) {
        $override = require $local;
        if (is_array($override)) {
            $config = $override + $config;
        }
    }

    return $config;
}

/**
 * Where enquiries are delivered.
 *
 * EP_MAIL_TO unless mail-config.local.php sets a 'recipient' override, which
 * exists so delivery can be tested against an inbox that shows full headers
 * without editing a tracked file.
 *
 * A malformed override is discarded rather than honoured: the failure mode of
 * trusting it is that every enquiry is addressed to a string that is not an
 * address, which loses the lead. Falling back to the real mailbox cannot.
 */
function ep_mail_recipient(): string
{
    $override = trim((string) (ep_mail_config()['recipient'] ?? ''));

    return ($override !== '' && filter_var($override, FILTER_VALIDATE_EMAIL))
        ? $override
        : EP_MAIL_TO;
}

/** True when mail is being diverted away from EP_MAIL_TO. */
function ep_mail_recipient_overridden(): bool
{
    return ep_mail_recipient() !== EP_MAIL_TO;
}

/**
 * Fold a header value that may be long, and strip anything that could inject a
 * second header.
 *
 * ep_header_safe() in email.php does the injection half; this adds RFC 2047
 * encoding for non-ASCII, which a filename in an attachment header regularly
 * needs and a subject occasionally does.
 */
function ep_mime_encode_header(string $value): string
{
    $value = trim(str_replace(["\r", "\n", "\t"], ' ', $value));

    if ($value === '' || !preg_match('/[^\x20-\x7E]/', $value)) {
        return $value;
    }

    /* Encode in chunks so no single encoded-word exceeds the 75-character
       limit; a longer one is legal-ish but some clients render it raw. */
    $out   = [];
    $chunk = '';
    foreach (preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
        if (strlen(base64_encode($chunk . $char)) > 42) {
            $out[]  = '=?UTF-8?B?' . base64_encode($chunk) . '?=';
            $chunk = '';
        }
        $chunk .= $char;
    }
    if ($chunk !== '') {
        $out[] = '=?UTF-8?B?' . base64_encode($chunk) . '?=';
    }

    return implode("\r\n ", $out);
}

/**
 * A boundary that cannot appear in the body it delimits.
 *
 * Random rather than derived from the content: a boundary collision silently
 * truncates the message at the point of collision, and "silently" is the part
 * that makes it expensive to find.
 */
function ep_mime_boundary(): string
{
    return '=_ep_' . bin2hex(random_bytes(16));
}

/**
 * Assemble the body and the content headers for one message.
 *
 * Returns ['headers' => [...], 'body' => string]. The headers returned are only
 * the MIME ones — From, Reply-To and the rest belong to the caller, which knows
 * what they should say.
 *
 * @param string     $text       Plain-text body, already CRLF-normalised.
 * @param array|null $attachment ['content' => raw bytes, 'name' => filename,
 *                               'type' => MIME type] or null for no attachment.
 */
function ep_mime_build(string $text, ?array $attachment = null): array
{
    /**
     * Quoted-printable rather than 8bit.
     *
     * The body is non-ASCII in every message — ep_mail_body() writes an em dash
     * for a missing phone number, before the visitor types anything — and
     * declaring 8bit asks every relay in the chain to support 8BITMIME. Most
     * do; the ones that do not strip the high bit and deliver mojibake, and a
     * declared encoding that does not match the bytes is a spam signal in its
     * own right.
     *
     * Quoted-printable is 7-bit clean, so it survives any relay, and unlike
     * base64 it leaves ASCII legible — which matters because these messages get
     * read as raw text in logs and .eml copies while debugging exactly this.
     *
     * Named $encodedText, not $encoded: the attachment below produces its own
     * encoded blob, and when both were called $encoded the second assignment
     * silently replaced the first. Every message with a manuscript then carried
     * the attachment's base64 in place of the enquiry — no name, no email, no
     * message — while still looking structurally valid. Two encodings in one
     * function need two names that cannot be confused.
     */
    $encodedText = quoted_printable_encode($text);

    /* A message with nothing attached stays a flat text/plain part. Wrapping it
       in a multipart envelope "for consistency" would mean every routine
       enquiry arrives as an attachment-shaped message in clients that show a
       paperclip for any multipart, which trains the reader to ignore the
       paperclip that matters. */
    if ($attachment === null) {
        return [
            'headers' => [
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: quoted-printable',
            ],
            'body' => $encodedText,
        ];
    }

    $boundary = ep_mime_boundary();
    $name     = ep_mime_encode_header((string) ($attachment['name'] ?? 'attachment'));
    $type     = (string) ($attachment['type'] ?? 'application/octet-stream');

    /* Guard the parameters that sit inside a header: a quote or a newline in a
       filename would otherwise close the parameter early and let the rest of
       the name be read as header syntax. */
    $name = str_replace(['"', "\r", "\n"], '', $name);
    $type = preg_replace('~[^A-Za-z0-9!#$&^_.+\-/]~', '', $type) ?: 'application/octet-stream';

    $encodedFile = chunk_split(base64_encode((string) ($attachment['content'] ?? '')), 76, "\r\n");

    $body = implode("\r\n", [
        'This is a multi-part message in MIME format.',
        '',
        '--' . $boundary,
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: quoted-printable',
        '',
        $encodedText,                       // the enquiry
        '',
        '--' . $boundary,
        'Content-Type: ' . $type . '; name="' . $name . '"',
        'Content-Transfer-Encoding: base64',
        'Content-Disposition: attachment; filename="' . $name . '"',
        '',
        rtrim($encodedFile, "\r\n"),        // the manuscript
        '',
        '--' . $boundary . '--',
        '',
    ]);

    return [
        'headers' => [
            'MIME-Version: 1.0',
            'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
        ],
        'body' => $body,
    ];
}

/* --------------------------------------------------------------------------
 * SMTP
 * -------------------------------------------------------------------------- */

/** Read one complete SMTP reply, following multi-line continuations. */
function ep_smtp_read($socket, int $timeout): string
{
    $reply = '';

    while (($line = fgets($socket, 515)) !== false) {
        $reply .= $line;
        /* "250-EXTENSION" continues, "250 OK" ends. Anything shorter than four
           characters is a malformed line and there is nothing to wait for. */
        if (strlen($line) < 4 || $line[3] !== '-') {
            break;
        }
    }

    return $reply;
}

/** Send one command and return the reply. */
function ep_smtp_cmd($socket, string $command, int $timeout): string
{
    fwrite($socket, $command . "\r\n");
    return ep_smtp_read($socket, $timeout);
}

/** True when an SMTP reply carries one of the expected status codes. */
function ep_smtp_ok(string $reply, array $codes): bool
{
    return in_array((int) substr(trim($reply), 0, 3), $codes, true);
}

/**
 * Deliver one message over authenticated SMTP.
 *
 * $error is filled with the failing step and the server's own words, because
 * "SMTP failed" is not something anyone can act on, whereas "AUTH rejected:
 * 535 Incorrect authentication data" names the fix.
 */
function ep_smtp_send(
    string $from,
    array $recipients,
    string $headerBlock,
    string $body,
    array $cfg,
    ?string &$error = null
): bool {
    $error   = null;
    $timeout = (int) ($cfg['timeout'] ?? 20);
    $secure  = (string) ($cfg['secure'] ?? '');
    $host    = (string) ($cfg['host'] ?? '');
    $port    = (int) ($cfg['port'] ?? 587);

    if ($host === '') {
        $error = 'No SMTP host configured.';
        return false;
    }

    $context = stream_context_create([
        'ssl' => [
            'verify_peer'       => (bool) ($cfg['verify_peer'] ?? true),
            'verify_peer_name'  => (bool) ($cfg['verify_peer'] ?? true),
            'SNI_enabled'       => true,
            'allow_self_signed' => !($cfg['verify_peer'] ?? true),
        ],
    ]);

    $endpoint = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;

    $socket = @stream_socket_client(
        $endpoint,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if ($socket === false) {
        $error = sprintf('Cannot connect to %s (%d %s).', $endpoint, (int) $errno, (string) $errstr);
        return false;
    }

    stream_set_timeout($socket, $timeout);

    try {
        if (!ep_smtp_ok(ep_smtp_read($socket, $timeout), [220])) {
            $error = 'Server did not greet with 220.';
            return false;
        }

        /* The EHLO name should be the sending host. Some servers reject a bare
           hostname that does not resolve, so fall back to the domain of the
           envelope sender, which by definition exists. */
        $helo = (string) ($_SERVER['SERVER_NAME'] ?? '');
        if ($helo === '' || !str_contains($helo, '.')) {
            $helo = substr(strrchr($from, '@') ?: '@localhost', 1);
        }

        $reply = ep_smtp_cmd($socket, 'EHLO ' . $helo, $timeout);
        if (!ep_smtp_ok($reply, [250])) {
            $error = 'EHLO rejected: ' . trim($reply);
            return false;
        }

        if ($secure === 'tls') {
            if (!ep_smtp_ok(ep_smtp_cmd($socket, 'STARTTLS', $timeout), [220])) {
                $error = 'Server refused STARTTLS.';
                return false;
            }
            $crypto = @stream_socket_enable_crypto(
                $socket,
                true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT
            );
            if ($crypto !== true) {
                $error = 'TLS handshake failed. If the certificate is the problem, fix the '
                       . 'hostname rather than disabling verification.';
                return false;
            }
            /* The server's capabilities are re-advertised after the upgrade,
               and AUTH is usually only offered on the encrypted channel. */
            $reply = ep_smtp_cmd($socket, 'EHLO ' . $helo, $timeout);
            if (!ep_smtp_ok($reply, [250])) {
                $error = 'EHLO after STARTTLS rejected: ' . trim($reply);
                return false;
            }
        }

        $username = (string) ($cfg['username'] ?? '');
        $password = (string) ($cfg['password'] ?? '');

        if ($username !== '') {
            if ($secure === '') {
                $error = 'Refusing to send a password over an unencrypted connection. '
                       . "Set 'secure' to 'ssl' (port 465) or 'tls' (port 587).";
                return false;
            }

            if (!ep_smtp_ok(ep_smtp_cmd($socket, 'AUTH LOGIN', $timeout), [334])) {
                $error = 'Server did not offer AUTH LOGIN.';
                return false;
            }
            if (!ep_smtp_ok(ep_smtp_cmd($socket, base64_encode($username), $timeout), [334])) {
                $error = 'SMTP username rejected.';
                return false;
            }
            $reply = ep_smtp_cmd($socket, base64_encode($password), $timeout);
            if (!ep_smtp_ok($reply, [235])) {
                /* Never echo the password back into a log line, however much
                   easier that would make this to debug. */
                $error = 'SMTP authentication failed: ' . trim($reply);
                return false;
            }
        }

        $reply = ep_smtp_cmd($socket, 'MAIL FROM:<' . $from . '>', $timeout);
        if (!ep_smtp_ok($reply, [250])) {
            $error = 'MAIL FROM rejected: ' . trim($reply);
            return false;
        }

        $accepted = 0;
        foreach ($recipients as $recipient) {
            if (ep_smtp_ok(ep_smtp_cmd($socket, 'RCPT TO:<' . $recipient . '>', $timeout), [250, 251])) {
                $accepted++;
            }
        }
        if ($accepted === 0) {
            $error = 'Every recipient was rejected. Does the mailbox exist on this server?';
            return false;
        }

        if (!ep_smtp_ok(ep_smtp_cmd($socket, 'DATA', $timeout), [354])) {
            $error = 'Server refused DATA.';
            return false;
        }

        /* Dot-stuffing: a line consisting of a single "." ends the message, so
           any body line that starts with one gets a second. Without this, a
           message body can be truncated by its own content. */
        $data = $headerBlock . "\r\n\r\n" . $body;
        $data = preg_replace('/^\./m', '..', $data) ?? $data;

        fwrite($socket, $data . "\r\n.\r\n");

        $reply = ep_smtp_read($socket, $timeout);
        if (!ep_smtp_ok($reply, [250])) {
            $error = 'Message rejected at DATA: ' . trim($reply);
            return false;
        }

        return true;
    } finally {
        /* Best effort — the message is already accepted or already lost by the
           time this runs, so a failure to say goodbye politely changes nothing. */
        @fwrite($socket, "QUIT\r\n");
        @fclose($socket);
    }
}

/**
 * Write the exact bytes of a message to data/mail-outbox/ for inspection.
 *
 * This is how a send is verified where there is no MTA — the file is the real
 * composed message, headers, encoding and all, not a summary of it.
 */
function ep_mail_keep_copy(string $headerBlock, string $body, string $label): ?string
{
    $dir = EP_ROOT . '/data/mail-outbox';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
        return null;
    }

    /* The random suffix is not decoration. Named by timestamp and label alone,
       two submissions from the same landing page in the same second write the
       same filename and the second silently destroys the first — which is
       exactly what happens under any burst, and precisely when you most want
       both copies. */
    $file = sprintf(
        '%s/%s-%s-%s.eml',
        $dir,
        date('Ymd-His'),
        preg_replace('/[^a-z0-9]+/i', '-', $label) ?: 'mail',
        bin2hex(random_bytes(4))
    );

    return @file_put_contents($file, $headerBlock . "\r\n\r\n" . $body) !== false ? $file : null;
}
