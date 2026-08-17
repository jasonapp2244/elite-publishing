<?php
declare(strict_types=1);

/**
 * Mail transport settings.
 *
 * ---------------------------------------------------------------------------
 * WHICH TRANSPORT SHOULD I USE?
 * ---------------------------------------------------------------------------
 * 'mail'  PHP's mail(), which hands the message to whatever local MTA the host
 *         runs — Exim on both SiteGround and Hostinger shared hosting. NO
 *         credentials, no mailbox password, nothing to configure in code at
 *         all. This is the default and it is the "just send the email" option.
 *
 * 'smtp'  An authenticated SMTP session against a real mailbox. Slower and
 *         needs a password, but it is the fix when the local MTA accepts mail
 *         and the inbox never sees it, because the message is then
 *         authenticated as a mailbox rather than as "the web server".
 *
 * Start on 'mail'. Move to 'smtp' only if mail-selftest.php shows mail() being
 * accepted while nothing arrives — see the runbook in that file.
 *
 * ---------------------------------------------------------------------------
 * WHAT 'mail' STILL NEEDS FROM OUTSIDE THE CODE
 * ---------------------------------------------------------------------------
 * Nothing here, but two things in DNS, and neither is optional if the mail is
 * meant to be READ rather than merely accepted:
 *
 *   SPF must authorise the server the site runs on. The From address is on
 *   elitepublishing.co, so the receiving side asks whether that domain permits
 *   this IP to send for it. If it does not, the message is accepted by the
 *   local MTA, reported as sent, and filed as spam — the exact failure that
 *   looks like a broken form.
 *
 *   The destination mailbox must be reachable by MX. Mail to EP_MAIL_TO
 *   follows the domain's MX records, not the hosting account.
 *
 * Both are already satisfied. Verified against public DNS:
 *
 *   A       elitepublishing.co and mail.elitepublishing.co -> 35.209.251.68,
 *           i.e. the mailbox and the website are the same SiteGround server.
 *   SPF     "v=spf1 +a +mx include:elitepublishing.co.spf.auto.dnssmarthost.net ~all"
 *           The "+a" authorises the domain's own A record, which IS the web
 *           server, so mail() sending from the site passes SPF without any
 *           record being added.
 *   DKIM    default._domainkey is published (CNAME into dnssmarthost), so
 *           SiteGround's Exim signs outgoing mail for the domain.
 *   DMARC   "v=DMARC1; p=none; aspf=r; adkim=r" — relaxed alignment, no
 *           enforcement. Monitoring only; it will not reject anything.
 *   MX      mx10/20/30.antispam.mailspamprotection.com, SiteGround's filter.
 *
 * So nothing in DNS needs changing for the forms to deliver. If mail ever
 * stops arriving, re-check these before touching any code — a lapsed DKIM
 * CNAME or an edited SPF record looks exactly like a broken form.
 *
 * ---------------------------------------------------------------------------
 * HOW TO SET CREDENTIALS WITHOUT COMMITTING THEM
 * ---------------------------------------------------------------------------
 * Do NOT type a password into this file. It is tracked by git, so a password
 * here ends up in the repository history for good.
 *
 * Instead create includes/mail-config.local.php, which .gitignore already
 * excludes (`*.local.php`), and return only the keys you want to override:
 *
 *     <?php
 *     return [
 *         'transport' => 'smtp',
 *         'host'      => 'mail.elitepublishing.co',
 *         'port'      => 465,
 *         'secure'    => 'ssl',          // 465 => 'ssl', 587 => 'tls'
 *         'username'  => 'info@elitepublishing.co',
 *         'password'  => 'the mailbox password from Site Tools',
 *     ];
 *
 * That file is loaded on top of this one, so anything it does not mention
 * keeps the default below.
 */

return [
    /** 'mail' | 'smtp' */
    'transport' => 'mail',

    /**
     * Only read when 'transport' is 'smtp'. Ignored entirely on 'mail'.
     *
     * The host to use depends on who runs the MAILBOX, which is not necessarily
     * who runs the website:
     *
     *   SiteGround         mail.<domain>, port 465 with 'ssl' — this is the
     *                      one in use. Site Tools -> Email -> Accounts ->
     *                      Mail Configuration shows the exact values.
     *   Google Workspace   smtp.gmail.com with an app password
     *   Microsoft 365      smtp.office365.com, port 587 with 'tls'
     *
     * mail.elitepublishing.co is the default because that is where the domain
     * resolves, and it resolves to the same server the site runs on. Change it
     * in mail-config.local.php rather than here, alongside the username and
     * password it goes with.
     */
    'host' => 'mail.elitepublishing.co',

    /**
     * 465 with 'ssl' (implicit TLS) is what SiteGround documents first and is
     * the most reliable through outbound firewalls. 587 with 'tls' (STARTTLS)
     * is the standard alternative if 465 is blocked.
     */
    'port'   => 465,
    'secure' => 'ssl',            // 'ssl' | 'tls' | '' (none, not recommended)

    /**
     * The full email address of a mailbox that exists on the domain, and its
     * password. Leave both empty here — set them in mail-config.local.php.
     */
    'username' => '',
    'password' => '',

    /**
     * TEST ONLY — send enquiries somewhere other than EP_MAIL_TO.
     *
     * Empty means "deliver to EP_MAIL_TO in includes/config.php", which is what
     * production must run with. Set it in mail-config.local.php to an address
     * you can actually inspect — a Gmail account is ideal, because Gmail shows
     * the full headers and states plainly whether SPF and DKIM passed, which
     * SiteGround's webmail does not:
     *
     *     'recipient' => 'you@gmail.com',
     *
     * This changes ONLY the destination. The From address and the -f envelope
     * sender stay on the site's own domain, because that is what SPF
     * authorises — redirecting the From to a Gmail address is precisely the
     * mistake that puts every enquiry in a spam folder. So this tests the real
     * sending path, not a different one.
     *
     * An unparseable address here is ignored and EP_MAIL_TO is used instead, so
     * a typo loses no leads. mail-selftest.php reports the override in
     * capitals whenever it is active — leaving it set on a live site means
     * every enquiry goes to a personal inbox and the company one stays empty,
     * which looks exactly like the fault you are trying to diagnose.
     */
    'recipient' => '',

    /** Seconds to wait on connect and on each command. */
    'timeout' => 20,

    /**
     * Verify the server's TLS certificate. Leave true. Setting this false to
     * "make it work" disables the only check that the server you handed a
     * password to is the one you meant, and a self-signed certificate on a
     * mail host is a reason to stop and look, not to skip the check.
     */
    'verify_peer' => true,

    /**
     * Write the full composed message to data/mail-outbox/ as a .eml file in
     * addition to sending it. Off in production; the development branch in
     * ep_send_mail() switches it on by itself so a local run can be inspected.
     */
    'keep_copies' => false,
];
