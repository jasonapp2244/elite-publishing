<?php
declare(strict_types=1);

/**
 * Landing-page header (lp1–lp4).
 *
 * Deliberately NOT includes/header.php. The four landing designs draw no
 * navigation at all — just the logo and a single CTA — and that is the point of
 * a campaign landing page: the only ways out are the form and the CTA. Adding
 * the site nav here would be a design change, not a fix.
 *
 * Because there is no nav there is also no burger, no dropdown and no scrim, so
 * none of the header JS in assets/js/main.js applies to these pages.
 *
 * Measured from the designs at 1920: logo 112x89 at x=250, CTA pill 231x55
 * ending at x=1669, band 158px tall.
 */

$lpCta = ep_data_get('landing', 'shared')['header_cta'] ?? 'Publish Your Book';
?>
<header class="lp-header">
  <div class="container-ep lp-header__inner">

    <a class="lp-header__logo" href="<?= esc(url()) ?>" aria-label="<?= esc(EP_NAME) ?> — home">
      <img src="<?= esc(asset('img/logo.png')) ?>" alt="<?= esc(EP_NAME) ?>"
           width="452" height="360" loading="eager" decoding="sync">
    </a>

    <a class="ep-btn ep-btn--primary lp-header__cta" href="#lp-form">
      <?= esc($lpCta) ?>
      <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
    </a>

  </div>
</header>
