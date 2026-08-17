<?php
declare(strict_types=1);

/**
 * The site footer — green field, mailto heading, social chips, five link
 * columns, legal row, and the full-bleed band of tilted book covers.
 *
 * SINGLE SOURCE OF TRUTH. This is the only place the footer's markup exists.
 * It is rendered by:
 *   includes/footer.php      every main-site page
 *   includes/lp-footer.php   every landing page
 *
 * Only the <footer> element lives here. Deliberately NOT included: closing
 * </main>/</body>/</html>, the modals, and the script tags. Those belong to
 * whatever is ending the document, and the two callers end it differently — a
 * landing page brings its own scripts and its own closing markup. Putting them
 * here would make this file impossible to reuse, which is the thing it is for.
 *
 * Link columns come from ep_footer_nav() and the address from EP_EMAIL, both in
 * includes/config.php, so the content is shared as well as the shape.
 */

$footerCols = ep_footer_nav();
?>
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
      <p class="m-0 ep-footer__legal-copy">&copy; Copyright <?= esc(date('Y')) ?> All Rights Reserved.</p>
      <p class="m-0 ep-footer__legal-links">
        <a href="<?= esc(url(ep_page_url('terms-conditions'))) ?>">Terms &amp; Conditions</a>
        <span aria-hidden="true">|</span>
        <a href="<?= esc(url(ep_page_url('privacy-policy'))) ?>">Privacy Policy</a>
      </p>
    </div>

  </div>

  <?php require __DIR__ . '/book-band.php'; ?>
</footer>
