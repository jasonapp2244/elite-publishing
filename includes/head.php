<?php
declare(strict_types=1);

/**
 * Document head + opening body. Include this at the very top of every page,
 * after setting:
 *   $pageKey         string  matches a nav key, e.g. 'home', 'service:ghostwriting'
 *   $pageTitle       string  without the brand suffix
 *   $pageDescription string  meta description, ~150 chars
 *   $pageCss         array   extra stylesheet paths relative to assets/, optional
 *   $ogImage         string  path relative to assets/, optional
 *   $bodyClass       string  optional
 *   $pageChrome      string  'site' (default) | 'lp'
 *
 * $pageChrome picks which header/footer pair the page gets. The campaign
 * landing pages (lp1-lp4) draw no navigation at all, so they use lp-header.php
 * and lp-footer.php instead. Pages set it once, here, rather than skipping
 * head.php — everything above the <body> tag (canonical, OG, preloads, the
 * session that ep_csrf_field() needs) is identical either way, and a second
 * copy of it would be the thing that silently drifts.
 */

require_once __DIR__ . '/config.php';

$pageKey         = $pageKey         ?? '';
$pageTitle       = $pageTitle       ?? EP_NAME;
$pageDescription = $pageDescription ?? EP_TAGLINE;
$pageCss         = $pageCss         ?? [];
$bodyClass       = $bodyClass       ?? '';
$ogImage         = $ogImage         ?? 'img/og-default.jpg';
$pageChrome      = $pageChrome      ?? 'site';

/**
 * Start the session here, before a single byte of output.
 *
 * The consultation wizard sits near the bottom of almost every page and calls
 * ep_csrf_field(); by that point headers are long gone and session_start()
 * fails with a warning, leaving the form with no CSRF token. Opening it at the
 * top of the document is the only place that works for every page.
 */
ep_session_start();

$fullTitle  = $pageKey === 'home' ? $pageTitle : $pageTitle . ' | ' . EP_NAME;
/**
 * Canonical URL.
 *
 * Derived from the page, not from REQUEST_URI: five of the content pages
 * resolve at both `/pricing` and `/pricing.php`, and echoing the requested URI
 * would publish whichever one the visitor happened to arrive on. Pages with a
 * URL that is not derivable from their filename (service.php) set
 * $pageCanonical themselves.
 */
if (!isset($pageCanonical)) {
    $script        = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'), '.php');
    $pageCanonical = ep_page_url($script);
}
$canonical = EP_ORIGIN . url($pageCanonical);
?>
<!doctype html>
<html lang="en">
<head>
<script>document.documentElement.className+=" js"</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($fullTitle) ?></title>
<meta name="description" content="<?= esc($pageDescription) ?>">
<?php /* No canonical on an error page — it is noise for crawlers and would
         point them at a URL that legitimately returns 404. */ ?>
<?php if (($pageNoIndex ?? false) === false): ?>
<link rel="canonical" href="<?= esc($canonical) ?>">
<?php else: ?>
<meta name="robots" content="noindex, follow">
<?php endif; ?>
<meta name="theme-color" content="#60C489">

<!-- Open Graph / Twitter -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= esc(EP_NAME) ?>">
<meta property="og:title" content="<?= esc($fullTitle) ?>">
<meta property="og:description" content="<?= esc($pageDescription) ?>">
<meta property="og:url" content="<?= esc($canonical) ?>">
<meta property="og:image" content="<?= esc(EP_ORIGIN . asset($ogImage)) ?>">
<meta name="twitter:card" content="summary_large_image">

<!-- Fonts: preload the two weights used above the fold.
     No ?v= cache-buster here: tokens.css resolves its @font-face src relative
     to itself, giving an unversioned URL. If the preload href does not match
     that URL byte-for-byte the browser treats them as two resources, warns
     "preloaded but not used", and downloads each font twice. -->
<link rel="preload" href="<?= esc(url('assets/fonts/urbanist-600.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= esc(url('assets/fonts/urbanist-400.woff2')) ?>" as="font" type="font/woff2" crossorigin>

<!-- Bootstrap was removed in Phase 4: a grep of all 17 pages found zero uses of
     its grid or utilities (no row/col-*/g-*/d-flex/container), so it was 227KB
     of render-blocking CSS for nothing. Everything here is CSS Grid/Flexbox. -->
<link rel="stylesheet" href="<?= esc(asset('css/tokens.css')) ?>">
<link rel="stylesheet" href="<?= esc(asset('css/main.css')) ?>">
<?php foreach ($pageCss as $css): ?>
<link rel="stylesheet" href="<?= esc(asset($css)) ?>">
<?php endforeach; ?>

<link rel="icon" href="<?= esc(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= esc(asset('img/apple-touch-icon.png')) ?>">

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'Organization',
    'name'     => EP_NAME,
    'url'      => EP_ORIGIN . EP_BASE,
    'logo'     => EP_ORIGIN . asset('img/logo.png'),
    'description' => EP_TAGLINE,
    'contactPoint' => [
        '@type'       => 'ContactPoint',
        'contactType' => 'customer service',
        'email'       => EP_EMAIL,
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
</head>
<body<?= $bodyClass !== '' ? ' class="' . esc($bodyClass) . '"' : '' ?>>
<a class="skip-link" href="#main">Skip to main content</a>
<?php require __DIR__ . ($pageChrome === 'lp' ? '/lp-header.php' : '/header.php'); ?>
<?php /* tabindex="-1" so the skip link moves focus, not just the scroll
         position — without it a screen-reader cursor stays in the header. */ ?>
<main id="main" tabindex="-1">
