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
 * The three forms it serves:
 *   wizard        the 4-step consultation block (home, contact, service pages)
 *   hero-contact  the enquiry card on the ten service pages
 *   lp-contact    the home-page hero card, carries a `campaign` value. Named
 *                 for the campaign landing pages it was built for; those pages
 *                 are gone, the key stayed so live leads keep routing.
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
        'hero-contact' => 'Service page enquiry',
    ][$d['form'] ?? ''] ?? 'Website contact form';

    $lines = [
        $label,
        str_repeat('=', mb_strlen($label)),
        '',
        'Name:     ' . ($d['name'] ?? ''),
        'Email:    ' . ($d['email'] ?? ''),
        'Phone:    ' . (($d['phone'] ?? '') !== '' ? $d['phone'] : '—'),
    ];

    if (($d['service'] ?? '') !== '') {
        $lines[] = 'Service:  ' . $d['service'];
    }
    if (($d['campaign'] ?? '') !== '') {
        $lines[] = 'Campaign: ' . $d['campaign'];
    }
    if (($d['form'] ?? '') === 'wizard') {
        $lines[] = 'Genre:    ' . (($d['genre']  ?? '') !== '' ? $d['genre']  : '—');
        $lines[] = 'Stage:    ' . (($d['stage']  ?? '') !== '' ? $d['stage']  : '—');
        $lines[] = 'Budget:   ' . (($d['budget'] ?? '') !== '' ? $d['budget'] : '—');
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
    $to      = ep_header_safe(EP_MAIL_TO);
    $name    = $d['name'] ?? '';
    $subject = ep_header_safe(sprintf(
        '%s — %s',
        ['wizard'     => 'Consultation request',
         'lp-contact' => 'Landing page enquiry'][$d['form'] ?? ''] ?? 'Website enquiry',
        $name !== '' ? $name : 'no name given'
    ));

    $replyTo = filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL)
        ? trim(ep_mail_name($name) . ' <' . ep_header_safe($d['email']) . '>')
        : '';

    $headers = [
        'From: ' . trim(ep_mail_name(EP_NAME) . ' <' . EP_MAIL_FROM . '>'),
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'MIME-Version: 1.0',
        'X-Mailer: Elite Publishing site',
    ];
    if ($replyTo !== '') {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    $entry = $d + ['time' => date('c'), 'outcome' => 'accepted'];

    if (EP_ENV !== 'production') {
        /* No MTA on a dev machine. Record it and report success so the whole
           flow — including the thank-you redirect — can be exercised locally. */
        $entry['outcome'] = 'logged (development)';
        ep_log_submission($entry);
        return true;
    }

    $sent = @mail(
        $to,
        $subject,
        ep_mail_body($d),
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
