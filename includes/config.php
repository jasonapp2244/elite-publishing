<?php
declare(strict_types=1);

/**
 * Elite Publishing — site configuration.
 * Single place for site-wide constants, the nav model and the service registry.
 */

// --- Environment ------------------------------------------------------------
define('EP_ENV', 'development');            // 'development' | 'production'
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
define('EP_TAGLINE', 'Premium book publishing services for independent authors');
define('EP_EMAIL', 'Contact@Elitepublishing.Co');   // shown verbatim in the footer
define('EP_PHONE', '+1 (800) 000-0000');
define('EP_ADDRESS', '');

// Social profiles shown in the footer. Empty string hides the icon.
const EP_SOCIAL = [
    'facebook'  => '#',
    'linkedin'  => '#',
    'instagram' => '#',
];

// Where contact-form submissions are delivered.
define('EP_MAIL_TO', EP_EMAIL);

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
