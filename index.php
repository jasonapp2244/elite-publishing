<?php
declare(strict_types=1);

/**
 * Home page.
 * NOTE: currently a foundation smoke-test only — Dev 1 replaces the body of
 * this file with the real sections from the Figma design.
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'home';
$pageTitle       = 'Turn Your Manuscript Into A Bestseller';
$pageDescription = 'Premium, complete book publishing services for independent authors. '
                 . 'From line editing and cover design to global distribution and audiobooks.';

require __DIR__ . '/includes/head.php';
?>

<section class="section" style="text-align:center">
  <div class="container-ep">
    <span class="eyebrow">Foundation check</span>
    <h1>Turn Your Manuscript Into A Bestseller With Elite Publishing, The Best Manuscript
      <mark style="background:var(--ep-green);padding:0 .1em;border-radius:6px">Publisher!</mark>
    </h1>
    <p class="lead text-muted-ep" style="max-width:66ch;margin-inline:auto">
      Premium, complete book publishing services for independent authors. From line editing
      and cover design to global distribution and audiobooks, we help you publish a book with
      professional impact.
    </p>
    <p style="margin-top:28px;display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a class="ep-btn ep-btn--primary" href="<?= esc(url('contact.php')) ?>">
        Submit Manuscript <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
      <a class="ep-btn ep-btn--outline" href="<?= esc(url('services/books-publishing')) ?>">
        Explore Our Services <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </p>
  </div>
</section>

<?php
$bandVariant = 'hero';
$bandEager   = true;
require __DIR__ . '/includes/components/book-band.php';
?>

<section class="section bg-tint">
  <div class="container-ep">
    <span class="eyebrow">Our Services</span>
    <h2>Featured Self-Publishing Solutions</h2>
    <div class="row g-4 mt-2">
      <?php foreach (array_slice(EP_SERVICES, 0, 4, true) as $slug => $label): ?>
        <div class="col-12 col-sm-6 col-lg-3">
          <article class="ep-card">
            <div class="ep-card__icon"><?= ep_icon('book-open') ?></div>
            <h3 class="ep-card__title h4"><?= esc($label) ?></h3>
            <p class="ep-card__text">Placeholder copy — replaced with the real description from the design.</p>
            <a class="link-arrow" href="<?= esc(url('services/' . $slug)) ?>">
              Learn More <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
            </a>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
