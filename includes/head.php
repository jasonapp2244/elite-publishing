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
 */

require_once __DIR__ . '/config.php';

$pageKey         = $pageKey         ?? '';
$pageTitle       = $pageTitle       ?? EP_NAME;
$pageDescription = $pageDescription ?? EP_TAGLINE;
$pageCss         = $pageCss         ?? [];
$bodyClass       = $bodyClass       ?? '';
$ogImage         = $ogImage         ?? 'img/og-default.jpg';

$fullTitle  = $pageKey === 'home' ? $pageTitle : $pageTitle . ' | ' . EP_NAME;
$canonical  = EP_ORIGIN . strtok((string) ($_SERVER['REQUEST_URI'] ?? EP_BASE), '?');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($fullTitle) ?></title>
<meta name="description" content="<?= esc($pageDescription) ?>">
<link rel="canonical" href="<?= esc($canonical) ?>">
<meta name="theme-color" content="#60C489">

<!-- Open Graph / Twitter -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= esc(EP_NAME) ?>">
<meta property="og:title" content="<?= esc($fullTitle) ?>">
<meta property="og:description" content="<?= esc($pageDescription) ?>">
<meta property="og:url" content="<?= esc($canonical) ?>">
<meta property="og:image" content="<?= esc(EP_ORIGIN . asset($ogImage)) ?>">
<meta name="twitter:card" content="summary_large_image">

<!-- Fonts: preload the two weights used above the fold -->
<link rel="preload" href="<?= esc(asset('fonts/urbanist-600.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= esc(asset('fonts/urbanist-400.woff2')) ?>" as="font" type="font/woff2" crossorigin>

<link rel="stylesheet" href="<?= esc(asset('css/tokens.css')) ?>">
<link rel="stylesheet" href="<?= esc(asset('css/bootstrap.min.css')) ?>">
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
    'logo'     => EP_ORIGIN . asset('img/logo.svg'),
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
<?php require __DIR__ . '/header.php'; ?>
<main id="main">
