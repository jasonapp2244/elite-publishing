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
function ep_nav(): array
{
    $serviceChildren = [];
    foreach (EP_SERVICES as $slug => $label) {
        $serviceChildren[] = ['key' => 'service:' . $slug, 'label' => $label, 'href' => 'services/' . $slug];
    }

    return [
        ['key' => 'home',     'label' => 'Home',      'href' => ''],
        ['key' => 'services', 'label' => 'Services',  'href' => null, 'children' => $serviceChildren],
        ['key' => 'books',    'label' => 'Our Books', 'href' => 'our-books.php'],
        ['key' => 'about',    'label' => 'Company',   'href' => 'about.php'],
        ['key' => 'pricing',  'label' => 'Pricing',   'href' => 'pricing.php'],
        ['key' => 'contact',  'label' => 'Contact',   'href' => 'contact.php'],
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
            ['label' => 'Company',  'href' => 'about.php'],
        ],
        [
            ['label' => 'Portfolio', 'href' => 'our-books.php'],
            ['label' => 'Pricing',   'href' => 'pricing.php'],
            ['label' => 'Contact',   'href' => 'contact.php'],
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
