<?php
declare(strict_types=1);

/**
 * Landing page footer.
 *
 * Renders components/lp-chrome-footer.php — the landing pages' own chrome: a
 * green band carrying the logo, the three legal/contact links and the
 * copyright line, matching the approved design. It is deliberately NOT the
 * main site's footer.
 *
 * WHAT CHANGED, AND WHY THE MAIN SITE IS SAFE
 * ---------------------------------------------------------------------------
 * This file used to render components/site-footer.php, the same component
 * includes/footer.php renders on every main-site page. It no longer does:
 *
 *   main site   includes/footer.php -> components/site-footer.php, styled by
 *               assets/css/main.css. UNTOUCHED — it keeps its mailto heading,
 *               social chips, five link columns and the tilted book band.
 *   landing     this file           -> components/lp-chrome-footer.php, styled
 *               by assets/css/lp-chrome.css, which no main-site page loads.
 *
 * assets/js/main.js is no longer loaded here.
 * ---------------------------------------------------------------------------
 * It was loaded for one reason: the old shared header's burger, drawer and
 * Services dropdown were its handlers. The landing pages no longer have any of
 * those controls, so every init in that file would now find its markup absent
 * and return immediately. Loading a script to have it do nothing costs a
 * request and a parse on the page whose speed matters most.
 *
 * Nothing else depended on it. Each landing page loads its OWN script for its
 * own carousels, forms and reveals — lp1/js/main.js, lp2/assets/js/main.js,
 * lp3/assets/js/app.js and enquiry-form.js, lp4/js/main.js — and those are
 * still loaded by the pages themselves, exactly as before.
 *
 * Deliberately NOT rendered here, as before: the publish and cover modals. Both
 * are styled by main.css, which an LP does not load, so including them would
 * put unstyled dialogs in the markup.
 *
 * Usage, at the end of the LP's content and before its own scripts:
 *     <?php require __DIR__ . '/../includes/lp-footer.php'; ?>
 */

if (!defined('EP_ROOT')) {
    require_once __DIR__ . '/config.php';
}

require __DIR__ . '/components/lp-chrome-footer.php';
