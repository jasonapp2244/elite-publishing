<?php
declare(strict_types=1);

/**
 * 404 — not part of the Figma design, so it is assembled from components that
 * are: navbar, a short centred apology, two buttons, footer.
 *
 * .htaccess routes any unmatched path here with mod_rewrite, which serves this
 * file at the original URL — so the 404 status has to be set in PHP, before a
 * byte of output, or the miss would be served as a 200.
 */

require_once __DIR__ . '/includes/config.php';

http_response_code(404);

$pageKey         = '';
$pageTitle       = 'Page Not Found';
$pageDescription = 'The page you were looking for could not be found. Browse our publishing '
                 . 'services or head back to the home page.';
$pageCss         = ['css/p-core.css'];

require __DIR__ . '/includes/head.php';
?>

<section class="page-404" aria-labelledby="nf-h">
  <div class="container-ep">
    <p class="page-404__code">404</p>
    <h1 id="nf-h">This Page Has Not Been Written Yet</h1>
    <p class="lead page-404__intro">
      Sorry — the page you were looking for does not exist, or it has moved. The link may be
      out of date, or the address may have a typo in it.
    </p>
    <p class="page-404__actions">
      <a class="ep-btn ep-btn--primary" href="<?= esc(url('')) ?>">
        Back to Home
        <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
      <a class="ep-btn ep-btn--outline" href="<?= esc(url('services/books-publishing')) ?>">
        Explore Our Services
        <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
