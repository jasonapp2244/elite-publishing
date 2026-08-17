<?php
declare(strict_types=1);

/**
 * Landing page bootstrap — the only thing LP1..LP4 share with the main site.
 *
 * Each landing page was built as a standalone project: its own CSS, its own
 * JavaScript, its own markup conventions. That separation is deliberate and the
 * brief asks for it to be kept, so this file is intentionally small. It gives a
 * landing page four things and nothing else:
 *
 *   1. A CSRF token, so its form can post to forms/lp-handler.php.
 *   2. The site's real contact details, from includes/config.php, so an address
 *      or a phone number is never maintained in five places.
 *   3. The hidden fields every LP form needs, written once.
 *   4. The error state for a submission made with JavaScript disabled.
 *
 * It does NOT pull in the main site's header, footer, CSS or components. A
 * landing page that suddenly grew the site nav would stop being a landing page.
 *
 * Usage, at the very top of an LP's index.php:
 *
 *     <?php $lp = 'lp1'; require __DIR__ . '/../includes/lp-bootstrap.php'; ?>
 */

if (!defined('EP_ROOT')) {
    require_once __DIR__ . '/config.php';
}

/** Which landing page is rendering. Set by the page before including this. */
$lp = isset($lp) && is_string($lp) ? $lp : '';
if (!preg_match('/^lp[1-4]$/', $lp)) {
    $lp = 'lp1';
}

ep_session_start();

/**
 * The failure flash left by forms/lp-handler.php for a no-JavaScript post.
 *
 * Read once and consumed here, so a reload shows a clean form rather than a
 * stale error. Anything older than five minutes is a leftover from a page that
 * never rendered it and is dropped silently.
 */
function ep_lp_flash(): array
{
    static $flash = null;

    if ($flash === null) {
        $raw = $_SESSION['ep_lp_form'] ?? [];
        unset($_SESSION['ep_lp_form']);

        $stale = (time() - (int) ($raw['time'] ?? 0)) > 300;

        $flash = (!is_array($raw) || $raw === [] || $stale)
            ? ['status' => '', 'message' => '', 'errors' => [], 'old' => []]
            : [
                'status'  => (string) ($raw['status'] ?? ''),
                'message' => (string) ($raw['message'] ?? ''),
                'errors'  => (array) ($raw['errors'] ?? []),
                'old'     => (array) ($raw['old'] ?? []),
            ];
    }

    return $flash;
}

/** The value the visitor previously typed into $field, for repopulating. */
function ep_lp_old(string $field): string
{
    return (string) (ep_lp_flash()['old'][$field] ?? '');
}

/** The validation error for $field after a no-JavaScript post, or ''. */
function ep_lp_error(string $field): string
{
    return (string) (ep_lp_flash()['errors'][$field] ?? '');
}

/**
 * The hidden inputs every landing page form needs.
 *
 *   _lp      which page this is, so the handler can label the lead
 *   _token   CSRF
 *   _ajax    set to 1 by JavaScript before posting; left at 0 it makes the
 *            handler answer with a redirect instead of JSON, which is exactly
 *            what a browser with no JavaScript needs
 *   website  the honeypot. Hidden from people, irresistible to bots. It is
 *            positioned off-screen rather than display:none because some bots
 *            skip fields they can tell are not rendered.
 */
function ep_lp_hidden_fields(string $lp): string
{
    return implode("\n", [
        '<input type="hidden" name="_lp" value="' . esc($lp) . '">',
        '<input type="hidden" name="_ajax" value="0" data-ajax-flag>',
        ep_csrf_field(),
        '<div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden">',
        '  <label>Do not fill this in<input type="text" name="website" tabindex="-1" autocomplete="off"></label>',
        '</div>',
    ]);
}

/** Where an LP form posts. Absolute from the site root, so /lp1/ resolves it. */
function ep_lp_action(): string
{
    return url('forms/lp-handler.php');
}

/**
 * A landing page's own CSS/JS URL, with the file's mtime appended.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS EXISTS
 * ---------------------------------------------------------------------------
 * The landing pages arrived as standalone projects and linked their own assets
 * with bare relative paths — "css/sections.css", no version. The URL therefore
 * never changes when the file does, and .htaccess serves it with a cache
 * lifetime. A fix to a landing page's stylesheet was consequently invisible to
 * anyone who had already visited, including whoever was reviewing the fix, for
 * as long as their copy stayed fresh.
 *
 * That is not hypothetical. It hid a footer fix during earlier testing, which is
 * why .htaccess drops LP assets to max-age=3600 instead of the year the
 * main site's assets get — a mitigation, not a cure. An hour is still an hour of
 * being told a bug is unfixed, and the shorter lifetime costs every visitor a
 * revalidation the main site does not pay.
 *
 * With the mtime in the URL the cure is complete: edit a file and its URL
 * changes, so every browser fetches it on the next request and never asks about
 * the old one again. That also makes the 3600 in .htaccess redundant — see the
 * note there.
 *
 * The returned path stays RELATIVE, exactly as the pages already wrote it. An
 * absolute one would have to know the site's base path, and these pages work
 * unchanged at a domain root and in a sub-directory precisely because they do
 * not. A missing file returns the plain path rather than throwing, so a typo
 * shows up as a 404 in the network tab and not a 500 on the page.
 *
 * Usage, inside an LP that has already set $lp:
 *     <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/base.css')) ?>">
 */
function ep_lp_asset(string $lp, string $path): string
{
    $rel  = ltrim($path, '/');
    $full = EP_ROOT . '/' . $lp . '/' . $rel;

    return is_file($full) ? $rel . '?v=' . filemtime($full) : $rel;
}

/**
 * Where the header's "Submit Your Book" button sends the visitor.
 *
 * One button, four pages, four different answers — because the four pages put
 * their form in four different places, and a CTA that scrolls to nothing is
 * worse than no CTA. Kept here rather than in the header component so the
 * component stays presentation and this file stays the one thing that knows how
 * the landing pages differ.
 *
 *   lp1  the form lives in a modal, opened by main.js on [data-modal-open].
 *        That script calls preventDefault(), so the href is only ever followed
 *        when JavaScript is off — and then the modal is `hidden` and the form
 *        genuinely is unreachable, which is why the fallback is the contact
 *        page rather than a fragment on this one.
 *   lp2  #ContactForm, the card beside the hero
 *   lp3  #enquiry-panel, the manuscript panel beside the hero
 *   lp4  #start, the CTA + form section
 *
 * The last three are plain fragments and need no JavaScript at all.
 *
 * @return array{href: string, attrs: string}
 */
function ep_lp_cta(string $lp): array
{
    switch ($lp) {
        case 'lp2':
            return ['href' => '#ContactForm', 'attrs' => ''];
        case 'lp3':
            return ['href' => '#enquiry-panel', 'attrs' => ''];
        case 'lp4':
            return ['href' => '#start', 'attrs' => ''];
        case 'lp1':
        default:
            return [
                'href'  => url(ep_page_url('contact')),
                'attrs' => ' data-modal-open="contact-modal"',
            ];
    }
}

/**
 * The stylesheets the shared header and footer need. Goes in the LP's <head>.
 *
 * Two files, and deliberately NOT main.css:
 *
 *   tokens.css     @font-face rules and :root custom properties, nothing else.
 *                  Custom properties style nothing on their own — they only
 *                  resolve where a rule asks for one — and no landing page
 *                  declares an --ep-* name, so this cannot affect LP layout.
 *
 *   lp-chrome.css  the header/footer rules themselves, every one of them scoped
 *                  to .ep-header / .ep-footer / .book-band / .container-ep.
 *
 * main.css is excluded on purpose. It opens with global `*`, `html` and `body`
 * rules that would re-typeset the whole landing page; two of the LPs never
 * restate `body`, so they would silently take the main site's font and colour.
 * lp-chrome.css carries the same chrome appearance without touching anything
 * outside it.
 *
 * Emit this LAST in the head, after the LP's own stylesheets: the chrome
 * selectors are namespaced so order rarely matters, but where specificity ties
 * the chrome should win inside its own markup.
 */
function ep_lp_chrome_styles(): string
{
    /* lp-chrome.css only. tokens.css used to be emitted alongside it and must
       not be: it is the main site's token file and it also defines --fs-h1,
       --fs-h2, --fs-h3, --fs-h4 and their line heights, which LP1 and LP4
       declare too. Emitted here it lands AFTER each page's own stylesheets and
       wins on source order, so LP4's responsive
       `clamp(2rem, 0.5rem + 2.81vw, 3.875rem)` heading became the main site's
       flat 76px at every width, phones included. lp-chrome.css now declares the
       fifteen tokens and four @font-face rules it actually needs. */
    return '<link rel="stylesheet" href="' . esc(asset('css/lp-chrome.css')) . '">';
}
