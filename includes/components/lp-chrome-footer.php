<?php
declare(strict_types=1);

/**
 * Landing-page footer — logo, three legal links, one copyright line.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS IS NOT components/site-footer.php
 * ---------------------------------------------------------------------------
 * The landing pages used to render the main site's footer: a mailto heading at
 * display size, social chips, five columns of links and a full-bleed band of
 * tilted book covers. Same reasoning as the header — that footer is a site map,
 * and a site map at the bottom of a campaign page is a list of exits.
 *
 * What a landing page still owes the visitor is the legal minimum: how we treat
 * their data, the terms, a way to reach us, and who owns the page. That is
 * exactly what is here.
 *
 * NOTHING HERE IS SHARED WITH THE MAIN SITE. site-footer.php is untouched and
 * still renders every main-site page, book band and all. This file is reached
 * only through includes/lp-footer.php, which only lp1..lp4 include.
 *
 * See lp-chrome-header.php for why the class names are still `ep-*` — LP3's
 * stylesheet contains `:not(.ep-footer)` guards written against this exact name
 * and renaming it re-breaks the footer LP3 already had to be fixed for.
 *
 * ---------------------------------------------------------------------------
 * THE LINKS
 * ---------------------------------------------------------------------------
 * All three resolve through ep_page_url(), so they point at the real pages and
 * cannot rot the way the pages' original footers did — LP2 linked to a
 * /pricing-request/ that does not exist and LP4 to a /terms-of-service that
 * returned 404. The labels are the ones the approved design draws.
 */

if (!defined('EP_ROOT')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * The bare domain, for the copyright line.
 *
 * Derived from EP_EMAIL rather than typed, so it stays true to the one place
 * the address is defined. The design draws it lower-case and without a scheme.
 */
$lpDomain = strtolower((string) substr(strrchr(EP_EMAIL, '@') ?: '@elitepublishing.co', 1));
?>
<footer class="ep-footer ep-footer--lp">
  <div class="container-ep ep-footer__inner">

    <a class="ep-footer__logo" href="<?= esc(url()) ?>" aria-label="<?= esc(EP_NAME) ?> home">
      <?php /* logo-ink is the single-colour mark. The full-colour logo.png used
               in the header has a green in it that sits almost on top of this
               footer's green field and disappears against it. */ ?>
      <img src="<?= esc(asset('img/logo-ink.png')) ?>" alt="<?= esc(EP_NAME) ?>"
           width="452" height="360" loading="lazy" decoding="async">
    </a>

    <?php /* The Privacy Policy / Terms of Service / Contact row was removed on
             request. The markup is kept here, commented, because the pages it
             pointed at still exist and ad networks routinely require a privacy
             link on a paid landing page — restoring it is uncommenting three
             lines, and the grid in lp-chrome.css still has a column for it.

      <nav class="ep-footer__nav" aria-label="Legal and contact">
        <a href="<?= esc(url(ep_page_url('privacy-policy'))) ?>">Privacy Policy</a>
        <a href="<?= esc(url(ep_page_url('terms-conditions'))) ?>">Terms of Service</a>
        <a href="<?= esc(url(ep_page_url('contact'))) ?>">Contact</a>
      </nav>
    */ ?>

    <p class="ep-footer__copy">
      &copy; <?= esc(date('Y')) ?> <?= esc($lpDomain) ?>
      <span aria-hidden="true">&bull;</span> All rights reserved.
    </p>

  </div>
</footer>
