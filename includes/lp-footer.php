<?php
declare(strict_types=1);

/**
 * Landing-page footer (lp1–lp4).
 *
 * Deliberately NOT includes/footer.php. The site footer is a five-column link
 * directory on a PALE mint band (#EAF5EF); these designs draw a single row —
 * logo, three links, copyright — on the SOLID brand green, and the text on it
 * is ink, not white.
 *
 * That last point is easy to get wrong. --ep-on-green is white (DECISIONS §14),
 * but it describes the green CTA panel, where the design does draw white. Here
 * the design draws dark, and sampling the export confirms it: near-black glyphs
 * and the black logo lockup on #60C489. Ink on brand green measures 7.4:1, so
 * this footer passes AA where the CTA panel above it does not — do not
 * "harmonise" the two by pointing this at --ep-on-green.
 *
 * Measured at 1920: band 209px tall, logo 112x89, everything centred on one
 * row, 60px of padding above and below.
 */

$shared = ep_data_get('landing', 'shared');
?>
</main>

<footer class="lp-footer">
  <div class="container-ep lp-footer__inner">

    <a class="lp-footer__logo" href="<?= esc(url()) ?>" aria-label="<?= esc(EP_NAME) ?> — home">
      <img src="<?= esc(asset('img/logo-ink.png')) ?>" alt="<?= esc(EP_NAME) ?>"
           width="452" height="360" loading="lazy" decoding="async">
    </a>

    <nav class="lp-footer__nav" aria-label="Legal">
      <?php foreach (($shared['footer_links'] ?? []) as $link): ?>
        <a href="<?= esc(url(ep_page_url($link['page']))) ?>"><?= esc($link['label']) ?></a>
      <?php endforeach; ?>
    </nav>

    <p class="lp-footer__copy">
      &copy; <?= esc(date('Y')) ?> elitepublishing.co &bull; All rights reserved.
    </p>

  </div>
</footer>

<script src="<?= esc(asset('js/main.js')) ?>" defer></script>
</body>
</html>
