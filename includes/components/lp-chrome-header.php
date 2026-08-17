<?php
declare(strict_types=1);

/**
 * Landing-page header — logo, and one CTA. Nothing else.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS IS NOT components/site-header.php
 * ---------------------------------------------------------------------------
 * The landing pages used to render the main site's header: logo, six nav items,
 * a ten-item Services dropdown, a burger and a drawer. This replaces it with the
 * two controls the approved design shows.
 *
 * That is not a downgrade, it is what a landing page is for. Every one of these
 * pages exists to take a single action — send us a manuscript. A nav bar on it
 * offers ten ways to leave before doing that, and on paid traffic those are ten
 * ways to spend the click and get nothing. The main site keeps its full header;
 * it is a different job.
 *
 * NOTHING HERE IS SHARED WITH THE MAIN SITE. site-header.php is untouched and
 * still renders every main-site page. This file is reached only through
 * includes/lp-header.php, which only lp1..lp4 include. A change here cannot
 * reach the main site, and a change to the main site's header cannot reach here.
 *
 * ---------------------------------------------------------------------------
 * WHY THE CLASS NAMES ARE STILL .ep-header / .ep-btn
 * ---------------------------------------------------------------------------
 * They look like main-site names and they are, deliberately. The appearance is
 * carried entirely by assets/css/lp-chrome.css, which ONLY the landing pages
 * load, so these selectors mean something different here than on the main site
 * and cannot collide with it.
 *
 * Renaming them would break something real: lp3/assets/css/landing.css guards
 * its own abandoned chrome with `:not(.ep-footer)` selectors at id specificity.
 * Those guards are written against these exact names. Rename the classes and
 * LP3's dark-footer rules capture this chrome instead — near-black text on a
 * near-black ground, with its grid collapsed to a flex row. Keeping the names
 * keeps that guard working.
 */

if (!defined('EP_ROOT')) {
    require_once __DIR__ . '/../config.php';
}

/* Set by the landing page before it includes lp-header.php. Falls back to LP1's
   behaviour rather than to nothing, so a new page that forgets to declare one
   still gets a CTA that goes somewhere sensible. */
$lpCta = ep_lp_cta($lp ?? '');
?>
<header class="ep-header ep-header--lp">
  <div class="container-ep ep-header__inner">

    <a class="ep-logo" href="<?= esc(url()) ?>" aria-label="<?= esc(EP_NAME) ?> — home">
      <?php /* Eager and synchronous: it is the first thing drawn and a header
               that pops in after the hero has painted reads as a broken page.
               No fetchpriority — that belongs to the hero image, which is the
               LCP element on all four pages. */ ?>
      <img src="<?= esc(asset('img/logo.png')) ?>" alt="<?= esc(EP_NAME) ?>"
           width="452" height="360" loading="eager" decoding="sync">
    </a>

    <?php /* An <a>, not a <button>, on purpose. LP1's CTA opens the page's own
             enquiry modal and its main.js calls preventDefault() on the click —
             but with JavaScript off that modal is `hidden` and unreachable, so
             the href carries the visitor to the contact page instead of leaving
             the only conversion control on the page inert. LP2, LP3 and LP4
             point at their own in-page form, which needs no JavaScript at all. */ ?>
    <a class="ep-btn ep-btn--cta ep-header__cta"
       href="<?= esc($lpCta['href']) ?>"<?= $lpCta['attrs'] ?>>
      Submit Your Book
    </a>

  </div>
</header>
