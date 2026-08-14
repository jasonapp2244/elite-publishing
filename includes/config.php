<?php
declare(strict_types=1);

/**
 * Elite Publishing — site configuration.
 * Single place for site-wide constants, the nav model and the service registry.
 */

// --- Environment ------------------------------------------------------------
/**
 * 'development' | 'production' — detected from the host, not hardcoded.
 *
 * This used to be a literal 'development', which is a deployment landmine: on
 * SiteGround it would have kept quietly appending to data/submissions.log
 * instead of sending mail, and every enquiry would have been lost with no error
 * anywhere. Anything that is not obviously a local machine is production.
 *
 * To force it either way — a staging domain that should not send real mail, or
 * testing delivery locally — define EP_ENV before including this file, or drop
 * one line in a *.local.php (already gitignored).
 */
if (!defined('EP_ENV')) {
    $epHostRaw = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $epHostRaw = preg_replace('/:\d+$/', '', $epHostRaw);
    $epIsLocal = $epHostRaw === ''
        || $epHostRaw === 'localhost'
        || $epHostRaw === '127.0.0.1'
        || $epHostRaw === '::1'
        || str_ends_with($epHostRaw, '.local')
        || str_ends_with($epHostRaw, '.test')
        || str_ends_with($epHostRaw, '.localhost')
        || PHP_SAPI === 'cli';

    define('EP_ENV', $epIsLocal ? 'development' : 'production');
}
define('EP_DEBUG', EP_ENV === 'development');

ini_set('display_errors', EP_DEBUG ? '1' : '0');
error_reporting(EP_DEBUG ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// --- Paths ------------------------------------------------------------------
define('EP_ROOT', dirname(__DIR__));

/**
 * Base URL path the site is served from, derived at runtime so the same code
 * works at http://localhost/Elite%20Publishing/ and at a domain root.
 */
$epDocRoot = str_replace('\\', '/', rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\'));
$epRootFs  = str_replace('\\', '/', EP_ROOT);
$epBase    = ($epDocRoot !== '' && str_starts_with($epRootFs, $epDocRoot))
    ? substr($epRootFs, strlen($epDocRoot))
    : '';

/**
 * Percent-encode each path segment.
 *
 * The development folder is literally named "Elite Publishing", so the base
 * path contains a space. In an href a raw space is merely sloppy, but in a
 * `srcset` the space IS the delimiter between a URL and its width descriptor —
 * "/Elite Publishing/x-960.avif 960w" parses as the candidate "/Elite" with an
 * unknown descriptor, and the browser drops every candidate. That silently
 * breaks every responsive image on the site. Encode once, here, so url() and
 * asset() are safe in every context.
 */
$epBase = implode('/', array_map('rawurlencode', explode('/', trim($epBase, '/'))));
define('EP_BASE', '/' . ($epBase !== '' ? $epBase . '/' : ''));

$epScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('EP_ORIGIN', $epScheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

// --- Brand ------------------------------------------------------------------
define('EP_NAME', 'Elite Publishing');
/**
 * Feeds the default meta description AND the schema.org Organization
 * description in includes/head.php, so a claim left here is published twice —
 * once to readers and once to search engines. It said "Premium book publishing
 * services", which is a quality claim about ourselves that nothing supports.
 */
define('EP_TAGLINE', 'Book publishing services for independent authors');
/**
 * Shown in the footer, the CTA block and the thank-you page.
 *
 * The design draws "Contact@Elitepublishing.Co" in title case, and this
 * followed it. Title case is wrong for an address that is also a mailto: link
 * and a thing people retype from a screen — it reads as two different addresses
 * from EP_MAIL_TO below, and a visitor who copies it by hand is being shown
 * capitals that are not in the mailbox name. One lower-case spelling now, used
 * everywhere: display, mailto, mail headers.
 */
define('EP_EMAIL', 'info@elitepublishing.co');
define('EP_PHONE', '+1 (800) 000-0000');
/**
 * Street address. Shown in the contact block on the home page, the contact page
 * and all ten service pages, and published as schema.org PostalAddress.
 *
 * This is the single source of truth — it is NOT duplicated into
 * data/shared.php with the rest of that block's copy, because an address is a
 * business fact rather than page copy, and two copies of it is how one of them
 * goes stale. Set EP_ADDRESS to '' to hide the row everywhere; the component and
 * the structured data both check it.
 *
 * EP_ADDRESS_PARTS carries the same address split for schema.org. Keep the two
 * in step — a search engine reading a postal code that disagrees with the one on
 * the page is worse than publishing no structured address at all.
 */
define('EP_ADDRESS', '261 Madison Ave., New York, NY 10016');

const EP_ADDRESS_PARTS = [
    'streetAddress'   => '261 Madison Ave.',
    'addressLocality' => 'New York',
    'addressRegion'   => 'NY',
    'postalCode'      => '10016',
    'addressCountry'  => 'US',
];

// Social profiles shown in the footer. Empty string hides the icon.
const EP_SOCIAL = [
    'facebook'  => '#',
    'linkedin'  => '#',
    'instagram' => '#',
];

/**
 * Where contact-form submissions are DELIVERED.
 *
 * Kept separate from EP_EMAIL even though both now point at info@, because they
 * answer different questions:
 *
 *   EP_EMAIL    the address SHOWN to visitors — in the footer, in the CTA block
 *               and on the thank-you page. Carries the design's title-case
 *               styling. Display only; never used to route anything.
 *   EP_MAIL_TO  the inbox the site actually posts enquiries to. Lower-case,
 *               because this one goes into a mail header.
 *
 * The two matching means a visitor who writes in by hand lands in the same inbox
 * as a form submission, which is usually what you want. Nothing depends on it:
 * to route enquiries elsewhere later, change this line alone.
 *
 * The mailbox must exist on the domain, or mail to it bounces into nothing.
 */
define('EP_MAIL_TO', 'info@elitepublishing.co');

// --- Services ---------------------------------------------------------------
/**
 * The 10 service pages. All share one template (service.php); only the content
 * differs. Copy for each lives in data/services.php, keyed by slug.
 */
const EP_SERVICES = [
    'books-publishing'         => 'Books Publishing',
    'book-editing'             => 'Book Editing',
    'book-cover-design'        => 'Book Cover Design',
    'book-illustration'        => 'Book Illustration',
    'audio-book-production'    => 'Audio Book Production',
    'ghostwriting'             => 'Ghostwriting',
    'book-marketing'           => 'Book Marketing',
    'proofreading'             => 'Proofreading',
    'creative-content-writing' => 'Creative Content Writing',
    'blog-article-writing'     => 'Blog Article Writing',
];

// --- Primary navigation -----------------------------------------------------
/**
 * 'children' turns an item into a dropdown. 'key' is matched against the
 * $pageKey each page sets, to mark the active item.
 */
/**
 * Order the Services dropdown is drawn in — SPEC §B.1 / §F.1, two columns of
 * five read row by row.
 *
 * Deliberately separate from EP_SERVICES: that array also drives the footer
 * columns, the sitemap and the 404 fallback list, so reordering it to satisfy
 * the dropdown would silently reorder three other things.
 */
const EP_SERVICES_DROPDOWN = [
    'books-publishing',  'book-illustration',
    'book-editing',      'audio-book-production',
    'book-cover-design', 'book-marketing',
    'blog-article-writing', 'creative-content-writing',
    'ghostwriting',      'proofreading',
];

/**
 * Canonical, extensionless URL for each content page.
 *
 * The ten service pages already used pretty URLs while these six were published
 * as `.php`, which left the scheme half-migrated and baked `.php` into every
 * canonical tag and sitemap entry. `about-our-company` is the one path that is
 * not derivable from its filename (SPEC §F.3) and has an explicit .htaccess
 * rule; the rest resolve through the generic extensionless rewrite.
 */
const EP_PAGE_URLS = [
    'index'             => '',
    'our-books'         => 'our-books',
    'about'             => 'about-our-company',
    'pricing'           => 'pricing',
    'contact'           => 'contact',
    'privacy-policy'    => 'privacy-policy',
    'terms-conditions'  => 'terms-conditions',
];

/** Canonical path for a page, by its file basename. */
function ep_page_url(string $page): string
{
    return EP_PAGE_URLS[$page] ?? $page;
}

function ep_nav(): array
{
    $serviceChildren = [];
    foreach (EP_SERVICES_DROPDOWN as $slug) {
        if (!isset(EP_SERVICES[$slug])) {
            continue;   // guards against a typo silently dropping an item
        }
        $serviceChildren[] = [
            'key'   => 'service:' . $slug,
            'label' => EP_SERVICES[$slug],
            'href'  => 'services/' . $slug,
        ];
    }

    return [
        ['key' => 'home',     'label' => 'Home',      'href' => ''],
        ['key' => 'services', 'label' => 'Services',  'href' => null, 'children' => $serviceChildren],
        ['key' => 'books',    'label' => 'Our Books', 'href' => ep_page_url('our-books')],
        ['key' => 'about',    'label' => 'Company',   'href' => ep_page_url('about')],
        ['key' => 'pricing',  'label' => 'Pricing',   'href' => ep_page_url('pricing')],
        ['key' => 'contact',  'label' => 'Contact',   'href' => ep_page_url('contact')],
    ];
}

// --- Footer navigation ------------------------------------------------------
/**
 * Five unlabelled link columns, matching the Figma footer exactly.
 * The design shows no column headings — just the links.
 */
function ep_footer_nav(): array
{
    return [
        [
            ['label' => 'Home',     'href' => ''],
            ['label' => 'Services', 'href' => 'services/books-publishing'],
            ['label' => 'Company',  'href' => ep_page_url('about')],
        ],
        [
            ['label' => 'Portfolio', 'href' => ep_page_url('our-books')],
            ['label' => 'Pricing',   'href' => ep_page_url('pricing')],
            ['label' => 'Contact',   'href' => ep_page_url('contact')],
        ],
        [
            ['label' => 'Books Publishing',  'href' => 'services/books-publishing'],
            ['label' => 'Book Editing',      'href' => 'services/book-editing'],
            ['label' => 'Book Cover Design', 'href' => 'services/book-cover-design'],
        ],
        [
            ['label' => 'Book Illustration',     'href' => 'services/book-illustration'],
            ['label' => 'Audio Book Production', 'href' => 'services/audio-book-production'],
            ['label' => 'Book Marketing',        'href' => 'services/book-marketing'],
        ],
        [
            ['label' => 'Blog Article Writing',     'href' => 'services/blog-article-writing'],
            ['label' => 'Creative Content Writing', 'href' => 'services/creative-content-writing'],
        ],
    ];
}

require_once __DIR__ . '/functions.php';
