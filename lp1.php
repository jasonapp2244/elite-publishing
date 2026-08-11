<?php
declare(strict_types=1);

/**
 * lp1 — Children's book publishing campaign landing page.
 *
 * Design: "Children's Book Publishing.png" (project root).
 *
 * There is no page markup here on purpose. All four landing pages are the same
 * layout, so the sections live in includes/lp-page.php and the copy lives in
 * data/landing.php under the 'children' key. Editing this page means editing
 * one of those two files.
 */

require_once __DIR__ . '/includes/config.php';

$lpKey = 'children';
$lp    = ep_data_get('landing', $lpKey);

$pageKey         = 'lp:children';
$pageTitle       = $lp['title'] ?? '';
$pageDescription = $lp['meta']  ?? '';
$pageCss         = ['css/p-lp.css'];
$pageChrome      = 'lp';
$bodyClass       = 'lp';   // carries the mint wash, which starts behind the header

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/lp-page.php';
