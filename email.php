<?php
declare(strict_types=1);

/**
 * Mail transport — the one file to edit to change how mail is sent.
 *
 * ---------------------------------------------------------------------------
 * WHAT THIS IS, AND WHAT IT IS NOT
 * ---------------------------------------------------------------------------
 * This file SENDS. It does not validate, and it is not the form action.
 *
 * All three forms on the site post to forms/contact-handler.php, which checks
 * the CSRF token, the honeypot, the field rules and the rate limit, and only
 * then calls ep_send_mail() below. Pointing a form straight at this file would
 * skip every one of those checks and the address would be harvested within
 * days — so the forms stay where they are and this stays a transport.
 *
 * The forms it serves:
 *   wizard        the 4-step consultation block (home, contact, service pages)
 *   hero-contact  the enquiry card on the ten service pages
 *   lp-contact    the home-page hero card, carries a `campaign` value. Named
 *                 for the campaign landing pages it was built for; those pages
 *                 are gone, the key stayed so live leads keep routing.
 *   lp1 … lp4     the four standalone landing pages, posted by
 *                 forms/lp-handler.php. These carry a `landing_page` tag, a
 *                 free-form `fields` list, and optionally an attached
 *                 manuscript.
 *
 * ---------------------------------------------------------------------------
 * ATTACHMENTS AND TRANSPORT
 * ---------------------------------------------------------------------------
 * MIME assembly and the SMTP client live in includes/mailer.php, and which
 * transport is used is set in includes/mail-config.php. This file still owns
 * the decision of what a message says and who it is from.
 *
 * ---------------------------------------------------------------------------
 * SITEGROUND
 * ---------------------------------------------------------------------------
 * SiteGround runs a local Exim MTA, so PHP's mail() works with no SMTP
 * credentials — but only if the message looks like it came from the domain:
 *
 *   1. From: MUST be a mailbox on the site's own domain. Sending "from" the
 *      visitor's Gmail address is the single most common reason these mails
 *      land in spam: the domain's SPF record does not authorise SiteGround to
 *      send as gmail.com, so the message fails SPF and often DMARC too. The
 *      visitor's address goes in Reply-To, where hitting Reply still works.
 *
 *   2. The envelope sender (-f) is set to the same mailbox, so Return-Path
 *      matches From and bounces come back to you rather than to nobody.
 *      SiteGround permits -f for addresses that exist on the account.
 *
 *   3. Create the mailbox first, in Site Tools -> Email -> Accounts. If
 *      EP_MAIL_FROM does not exist, Exim will still accept the message but
 *      bounces vanish. Then point EP_MAIL_TO at wherever you actually read.
 *
 * If deliverability is still poor after that, the fix is authenticated SMTP,
 * not more headers — see ep_send_mail()'s note at the bottom.
 */

/* This is a library, not a page. Requesting it directly does nothing useful —
   it defines functions and returns a blank 200, which is just an endpoint for
   someone to probe. Refuse instead.

   The check compares the file the server was asked to run with this one, so it
   is true only for a direct hit and never when the handler includes it.
   .htaccess cannot do this job as well: the file has to stay in the web root
   because that is where it was asked to live. */
if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === realpath(__FILE__)) {
    http_response_code(404);
    exit;
}

if (!defined('EP_ROOT')) {
    require_once __DIR__ . '/includes/config.php';
}

require_once __DIR__ . '/includes/mailer.php';

/* --------------------------------------------------------------------------
 * Settings
 * -------------------------------------------------------------------------- */

/**
 * The mailbox mail is sent AS — not where it is delivered. That is EP_MAIL_TO
 * in includes/config.php, currently info@elitepublishing.co.
 *
 * It MUST exist on the site's own domain, or SiteGround's Exim has nothing to
 * authorise the -f envelope sender against and the message is refused or
 * silently dropped.
 *
 * This was derived from the request host, which resolved to
 * no-reply@elitepublishing.co — a mailbox nobody had created in Site Tools, so
 * every enquiry was being sent as an address that does not exist. It now sends
 * as EP_MAIL_TO instead, which is a mailbox that has to exist anyway because it
 * is where the mail is delivered. One mailbox, one thing to get wrong.
 *
 * The trade, so it is a decision and not an accident: a dedicated no-reply@
 * keeps automated mail out of the same thread as replies and makes bounces
 * obvious, and a few filters treat identical From and To as a mild spam signal.
 * If you create no-reply@elitepublishing.co in Site Tools -> Email -> Accounts
 * later, restore the old behaviour by replacing the line below with:
 *
 *     define('EP_MAIL_FROM', 'no-reply@elitepublishing.co');
 */
if (!defined('EP_MAIL_FROM')) {
    define('EP_MAIL_FROM', EP_MAIL_TO);
}

/** Where a copy of every accepted submission is written, in addition to mail. */
if (!defined('EP_MAIL_LOG')) {
    define('EP_MAIL_LOG', EP_ROOT . '/data/submissions.log');
}

/* --------------------------------------------------------------------------
 * Helpers
 * -------------------------------------------------------------------------- */

/**
 * A value safe to drop into a mail header.
 *
 * Anything that could start a new header — CR, LF or a tab — is removed rather
 * than escaped, because a header is not the place to be clever. Without this,
 * a name of "Bob\r\nBcc: everyone@example.com" turns the contact form into an
 * open relay.
 */
function ep_header_safe(string $value): string
{
    return trim(str_replace(["\r", "\n", "\t"], ' ', $value));
}

/**
 * Encode a display name for a From/Reply-To header.
 *
 * Anything outside ASCII has to be MIME-encoded or the header is invalid and
 * some servers drop the message. Quotes are stripped rather than escaped so the
 * quoted-string cannot be closed early.
 */
function ep_mail_name(string $name): string
{
    $name = ep_header_safe(str_replace('"', '', $name));
    if ($name === '') {
        return '';
    }

    return preg_match('/[^\x20-\x7E]/', $name)
        ? '=?UTF-8?B?' . base64_encode($name) . '?='
        : '"' . $name . '"';
}

/** A byte count a human can read, for the attachment line in the body. */
function ep_mail_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024) . ' KB';
    }

    return $bytes . ' bytes';
}

/** Append one JSON line to data/submissions.log. Never fatal. */
function ep_log_submission(array $entry): bool
{
    $dir = dirname(EP_MAIL_LOG);
    if (!is_dir($dir)) {
        return false;
    }

    $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return $line !== false
        && file_put_contents(EP_MAIL_LOG, $line . "\n", FILE_APPEND | LOCK_EX) !== false;
}

/* --------------------------------------------------------------------------
 * Composing
 * -------------------------------------------------------------------------- */

/**
 * Build the plain-text body of an enquiry.
 *
 * Plain text on purpose: an HTML enquiry mail buys nothing, and a text/plain
 * body cannot carry a payload that renders in whoever's inbox reads it.
 *
 * @param array $d Sanitised fields — name, email, phone, message, form,
 *                 genre, stage, budget, service, campaign, page, ip, ua.
 */
function ep_mail_body(array $d): string
{
    $label = [
        'wizard'       => 'Consultation wizard',
        'lp-contact'   => 'Landing page form',
        'hero-contact' => 'Service page inquiry',
        'lp1'          => 'Landing page LP1 inquiry',
        'lp2'          => 'Landing page LP2 inquiry',
        'lp3'          => 'Landing page LP3 inquiry',
        'lp4'          => 'Landing page LP4 inquiry',
    ][$d['form'] ?? ''] ?? 'Website contact form';

    $lines = [
        $label,
        str_repeat('=', mb_strlen($label)),
        '',
    ];

    /* The brief asks for the source to be stated outright, in these words, so
       a lead can be attributed at a glance without decoding a form key. It goes
       above the contact details because it is the first thing anyone triaging
       the inbox wants to know. */
    if (($d['landing_page'] ?? '') !== '') {
        $lines[] = 'Landing Page: ' . $d['landing_page'];
        $lines[] = '';
    }

    /* These are one-line values, so any newline the visitor managed to get into
       them is flattened. Not a security fix — ep_header_safe() already keeps
       CRLF out of the actual headers — but a name submitted as "Evil\nBcc: …"
       otherwise renders as two body lines, the second of which reads exactly
       like a mail header and makes a harmless submission look like a
       successful attack to whoever is triaging the inbox. */
    $oneLine = static fn(string $v): string => trim(preg_replace('/\s*\R\s*/u', ' ', $v) ?? $v);

    $lines[] = 'Name:     ' . $oneLine((string) ($d['name'] ?? ''));
    $lines[] = 'Email:    ' . $oneLine((string) ($d['email'] ?? ''));
    $lines[] = 'Phone:    ' . (($d['phone'] ?? '') !== '' ? $oneLine((string) $d['phone']) : 'Not provided');

    if (($d['service'] ?? '') !== '') {
        $lines[] = 'Service:  ' . $d['service'];
    }
    if (($d['campaign'] ?? '') !== '') {
        $lines[] = 'Campaign: ' . $d['campaign'];
    }
    if (($d['form'] ?? '') === 'wizard') {
        $lines[] = 'Genre:    ' . (($d['genre']  ?? '') !== '' ? $d['genre']  : 'Not provided');
        $lines[] = 'Stage:    ' . (($d['stage']  ?? '') !== '' ? $d['stage']  : 'Not provided');
        $lines[] = 'Budget:   ' . (($d['budget'] ?? '') !== '' ? $d['budget'] : 'Not provided');
    }

    /* Whatever else the landing page collected. Each LP asks for a slightly
       different set — a plan tier, a narration mode, a preferred callback time
       — and the requirement is that everything submitted arrives, so the
       handler passes them through as label => value rather than this file
       having to know each page's fields. */
    foreach ((array) ($d['fields'] ?? []) as $fieldLabel => $value) {
        $value = trim((string) $value);
        if ($value === '') {
            continue;
        }
        $lines[] = str_pad(rtrim((string) $fieldLabel, ':') . ':', 10) . $value;
    }

    if (!empty($d['attachment'])) {
        $lines[] = 'Manuscript: ' . ($d['attachment']['name'] ?? 'attached')
                 . ' (' . ep_mail_size((int) ($d['attachment']['size'] ?? 0)) . ', attached)';
    }

    $lines[] = '';
    $lines[] = 'Message';
    $lines[] = '-------';
    $lines[] = ($d['message'] ?? '') !== '' ? $d['message'] : '(no message)';
    $lines[] = '';
    $lines[] = '---';
    $lines[] = 'Sent from ' . ($d['page'] ?? '') . ' at ' . date('Y-m-d H:i:s T');

    /* Normalise to CRLF. Some MTAs mangle bare LF bodies, and the result is a
       mail that arrives as one unbroken paragraph. */
    return str_replace("\n", "\r\n", implode("\n", $lines));
}

/* --------------------------------------------------------------------------
 * Sending
 * -------------------------------------------------------------------------- */

/**
 * Send one enquiry.
 *
 * Returns true when the MTA accepted the message. That is NOT proof of
 * delivery — no synchronous API can promise that — so the caller should treat
 * false as "tell the visitor to email us directly" and true as "thank them".
 *
 * A copy is written to data/submissions.log either way, so a lead is never lost
 * to a mail problem. That file holds personal data: it is gitignored and
 * .htaccess denies data/ over HTTP. Both are already in place — check they
 * survived the deploy.
 */
function ep_send_mail(array $d): bool
{
    /* Normally EP_MAIL_TO; a 'recipient' in mail-config.local.php diverts it
       for testing. EP_MAIL_FROM is deliberately NOT derived from this — the
       From and the -f envelope sender must stay on the site's own domain
       whatever the destination, or the message fails SPF. */
    $to   = ep_header_safe(ep_mail_recipient());
    $name = $d['name'] ?? '';

    /* A landing page names itself in the subject line, so four campaigns
       feeding one inbox can be filtered apart without opening anything. */
    $kind = ['wizard'     => 'Consultation request',
             'lp-contact' => 'Landing page inquiry'][$d['form'] ?? ''] ?? 'Website inquiry';
    if (($d['landing_page'] ?? '') !== '') {
        $kind = ep_header_safe($d['landing_page']) . ' inquiry';
    }

    $subject = ep_header_safe(sprintf(
        '%s: %s',
        $kind,
        $name !== '' ? $name : 'no name given'
    ));

    /**
     * The subject as it must appear ON THE WIRE.
     *
     * A mail header is ASCII by definition (RFC 5322); anything else has to be
     * RFC 2047 encoded. This subject is non-ASCII in every single message —
     * the em dash above guarantees it — and a visitor's name can add more.
     *
     * Encoded ONCE here because it is needed in three places, and the previous
     * arrangement encoded it in two of them: the SMTP branch and the .eml copy
     * both went through ep_mime_encode_header(), while the mail() branch passed
     * the raw 8-bit string. mail() is the branch that actually runs on
     * SiteGround, so the path that was never encoded was the only one that
     * mattered — and because the local .eml files WERE encoded, every test
     * confirmed behaviour that production did not have.
     *
     * A raw 8-bit subject is not merely untidy: filters score it, some MTAs
     * strip or mangle it, and clients that do not guess the charset show
     * mojibake. When mail is being accepted and not delivered, this is exactly
     * the sort of thing that does it.
     */
    $subjectHeader = ep_mime_encode_header($subject);

    $replyTo = filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL)
        ? trim(ep_mail_name($name) . ' <' . ep_header_safe($d['email']) . '>')
        : '';

    $attachment = is_array($d['attachment'] ?? null) ? $d['attachment'] : null;
    $mime       = ep_mime_build(ep_mail_body($d), $attachment);

    $headers = array_merge(
        ['From: ' . trim(ep_mail_name(EP_NAME) . ' <' . EP_MAIL_FROM . '>')],
        $mime['headers'],
        ['X-Mailer: Elite Publishing site']
    );
    if ($replyTo !== '') {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    /* The log is a lead record, not a mail archive. Dropping the attachment's
       bytes keeps submissions.log readable and stops a 25 MB manuscript being
       base64'd into a JSON line — the filename and size are what a human
       reading the log actually needs. */
    $entry = $d;
    if ($attachment !== null) {
        $entry['attachment'] = [
            'name' => $attachment['name'] ?? '',
            'size' => $attachment['size'] ?? 0,
            'type' => $attachment['type'] ?? '',
        ];
    }
    $entry += ['time' => date('c'), 'outcome' => 'accepted'];

    /* Record where it actually went whenever that is not the configured
       mailbox. Without this the log says a lead was "sent" while the inbox it
       was meant for stays empty, and nothing on disk explains the difference. */
    if (ep_mail_recipient_overridden()) {
        $entry['delivered_to'] = $to;
    }

    $cfg = ep_mail_config();

    /* The exact bytes, as they will go on the wire. Built once so the
       development copy, the production copy and the SMTP transport are
       provably the same message rather than three reconstructions of it. */
    $headerBlock = 'To: ' . $to . "\r\n"
        . 'Subject: ' . $subjectHeader . "\r\n"
        . implode("\r\n", $headers);

    /* Opt-in on a live server: keep_copies writes every outgoing message to
       data/mail-outbox/ as well as sending it. Worth switching on for a day
       when the inbox is empty and you need to know whether the message was
       ever composed, and worth switching off again afterwards — those files
       contain everything the enquirer typed, including any attachment. */
    if (EP_ENV === 'production' && !empty($cfg['keep_copies'])) {
        ep_mail_keep_copy(
            $headerBlock,
            $mime['body'],
            ($d['landing_page'] ?? '') !== '' ? (string) $d['landing_page'] : (string) ($d['form'] ?? 'mail')
        );
    }

    if (EP_ENV !== 'production') {
        /* No MTA on a dev machine. Write the exact composed message to
           data/mail-outbox/ so the headers, the encoding and any attachment can
           be inspected for real, then report success so the whole flow —
           including the thank-you redirect — can be exercised locally. */
        $copy = ep_mail_keep_copy(
            $headerBlock,
            $mime['body'],
            ($d['landing_page'] ?? '') !== '' ? (string) $d['landing_page'] : (string) ($d['form'] ?? 'mail')
        );

        $entry['outcome'] = 'logged (development)';
        if ($copy !== null) {
            $entry['eml'] = basename($copy);
        }
        ep_log_submission($entry);

        return true;
    }

    if (($cfg['transport'] ?? 'mail') === 'smtp') {
        $error = null;
        $sent  = ep_smtp_send(
            EP_MAIL_FROM,
            [$to],
            /* SMTP hands the server the whole message, so it must carry its own
               Date header. mail() adds one itself, which is why the header
               block is shared but the date is added only here. */
            $headerBlock . "\r\n" . 'Date: ' . date('r'),
            $mime['body'],
            $cfg,
            $error
        );

        $entry['outcome'] = $sent ? 'sent (smtp)' : 'smtp failed';
        if (!$sent && $error !== null) {
            /* The server's own words, kept with the lead. Without this an SMTP
               failure is indistinguishable from a mailbox that is simply full,
               and the two have completely different fixes. */
            $entry['error'] = $error;
        }
        ep_log_submission($entry);

        return $sent;
    }

    $sent = @mail(
        $to,
        $subjectHeader,              // encoded, exactly as the other paths send it
        $mime['body'],
        implode("\r\n", $headers),
        '-f' . EP_MAIL_FROM          // envelope sender, so Return-Path matches
    );

    $entry['outcome'] = $sent ? 'sent' : 'mail() refused';
    ep_log_submission($entry);       // always, so a refused mail is still a lead

    return $sent;
}

/*
 * If mail still lands in spam on SiteGround after the From/-f setup above, the
 * remaining fix is authenticated SMTP, which mail() cannot do. Install
 * PHPMailer and replace the @mail() call in ep_send_mail() with an SMTP send
 * against SiteGround's mail server — nothing else in this file, and nothing in
 * the handler, has to change. Publishing SPF and DKIM records for the domain in
 * Site Tools is worth doing either way.
 */
