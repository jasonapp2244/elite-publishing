<?php
declare(strict_types=1);

/**
 * Landing page header.
 *
 * Renders components/lp-chrome-header.php — the landing pages' own chrome:
 * the logo and a single "Submit Your Book" button, matching the approved
 * design. It is deliberately NOT the main site's header.
 *
 * WHAT CHANGED, AND WHY THE MAIN SITE IS SAFE
 * ---------------------------------------------------------------------------
 * This file used to render components/site-header.php, the same component
 * includes/header.php renders on every main-site page. It no longer does. The
 * two headers are now separate components with separate stylesheets:
 *
 *   main site   includes/header.php -> components/site-header.php, styled by
 *               assets/css/main.css. UNTOUCHED by this change.
 *   landing     this file           -> components/lp-chrome-header.php, styled
 *               by assets/css/lp-chrome.css, which no main-site page loads.
 *
 * So the nav, the Services dropdown, the burger and the drawer are all still
 * exactly where they were on the main site. They are simply not on a landing
 * page any more, which is the point: a campaign page that offers ten ways to
 * navigate away offers ten ways to lose the click that was paid for.
 *
 * Consequences worth knowing:
 *
 *   No JavaScript is needed. The old header's burger and dropdown were driven
 *   by assets/js/main.js, which lp-footer.php loaded for that reason alone.
 *   With no nav to operate there is nothing left for it to do, so it is no
 *   longer loaded — see the note in lp-footer.php.
 *
 *   The CTA target differs per page. It comes from ep_lp_cta() in
 *   lp-bootstrap.php, which reads $lp. Set $lp before including this file, as
 *   every landing page already does.
 *
 * Usage, immediately after <body> and before the LP's own content:
 *     <?php require __DIR__ . '/../includes/lp-header.php'; ?>
 *
 * The matching stylesheet links belong in the LP's <head> — see
 * ep_lp_chrome_styles() in includes/lp-bootstrap.php.
 */

if (!defined('EP_ROOT')) {
    require_once __DIR__ . '/config.php';
}

require __DIR__ . '/components/lp-chrome-header.php';
