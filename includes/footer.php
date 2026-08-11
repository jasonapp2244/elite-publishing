<?php
declare(strict_types=1);

/**
 * Site footer + deferred scripts. Include at the very bottom of every page.
 * Matches the Figma FOOTER export: green field, oversized mailto heading,
 * three social chips, five unlabelled link columns, legal row, and a
 * full-bleed band of tilted book covers.
 */

$footerCols = ep_footer_nav();
?>
</main>

<footer class="ep-footer">
  <div class="container-ep">

    <div class="ep-footer__top">
      <a class="ep-footer__brand" href="mailto:<?= esc(EP_EMAIL) ?>">
        <img src="<?= esc(asset('img/logo-mark.png')) ?>" alt=""
             width="230" height="308" loading="lazy" decoding="async">
        <span><?= esc(EP_EMAIL) ?></span>
      </a>

      <ul class="ep-social list-plain">
        <?php foreach (EP_SOCIAL as $network => $href): ?>
          <?php if ($href === '') { continue; } ?>
          <li>
            <a href="<?= esc($href) ?>" aria-label="<?= esc(EP_NAME . ' on ' . ucfirst($network)) ?>"
               rel="noopener noreferrer" target="_blank">
              <?= ep_icon($network, ['size' => 18]) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <nav class="ep-footer__links" aria-label="Footer">
      <?php foreach ($footerCols as $i => $col): ?>
        <ul class="list-plain">
          <?php foreach ($col as $link): ?>
            <li><a href="<?= esc(url($link['href'])) ?>"><?= esc($link['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </nav>

    <div class="ep-footer__legal">
      <p class="m-0">&copy; Copyright <?= esc(date('Y')) ?> All Rights Reserved.</p>
      <p class="m-0 ep-footer__legal-links">
        <a href="<?= esc(url(ep_page_url('terms-conditions'))) ?>">Terms &amp; Conditions</a>
        <span aria-hidden="true">|</span>
        <a href="<?= esc(url(ep_page_url('privacy-policy'))) ?>">Privacy Policy</a>
      </p>
    </div>

  </div>

  <?php require __DIR__ . '/components/book-band.php'; ?>
</footer>

<script src="<?= esc(asset('js/main.js')) ?>" defer></script>
<?php foreach (($pageJs ?? []) as $js): ?>
<script src="<?= esc(asset($js)) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
