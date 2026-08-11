<?php
declare(strict_types=1);

/**
 * "Publishing Packages For Every Author" — SPEC §B.7, §D.2 Plans.
 *
 * Centred header + a 3-up row. The Standard tier is the featured one: 2px
 * green border plus an offset outer halo, ~30px taller than its siblings, a
 * "Most Popular" pill, a filled-green icon circle and a filled-green CTA.
 *
 * Identical on home, pricing and all ten service pages, so it takes no inputs
 * and reads data/pricing.php directly.
 */

$plans = ep_data('pricing');
if (empty($plans['tiers'])) {
    return;
}
?>
<section class="section plans" aria-labelledby="plans-head">
  <div class="container-ep">

    <div class="section-head section-head--center">
      <p class="eyebrow"><?= esc($plans['eyebrow'] ?? '') ?></p>
      <h2 id="plans-head"><?= ep_lines($plans['heading'] ?? '') ?></h2>
    </div>

    <ul class="plans__grid list-plain">
      <?php foreach ($plans['tiers'] as $tier): ?>
        <?php $featured = !empty($tier['featured']); ?>
        <li>
          <article class="plan<?= $featured ? ' plan--featured' : '' ?>">

            <div class="plan__top">
              <span class="plan__icon" aria-hidden="true">
                <?= ep_icon($tier['icon'] ?? 'book-open') ?>
              </span>
              <?php if (!empty($tier['badge'])): ?>
                <span class="plan__badge"><?= esc($tier['badge']) ?></span>
              <?php endif; ?>
            </div>

            <h3 class="h5 plan__name"><?= esc($tier['name'] ?? '') ?></h3>

            <p class="plan__price">
              <span class="plan__currency">$</span><!--
           --><span class="plan__figure"><?= esc($tier['price'] ?? '') ?></span><!--
           --><span class="plan__period"><?= esc($tier['period'] ?? '') ?></span>
            </p>

            <ul class="plan__features list-plain">
              <?php foreach (($tier['features'] ?? []) as $feature): ?>
                <li>
                  <span class="plan__check" aria-hidden="true"><?= ep_icon('check', ['size' => 12]) ?></span>
                  <?= esc($feature) ?>
                </li>
              <?php endforeach; ?>
            </ul>

            <a class="ep-btn plan__cta ep-btn--<?= $featured ? 'green' : 'green-outline' ?>"
               href="<?= esc(url('contact.php')) ?>">
              <?= esc($tier['cta'] ?? 'Get Started') ?>
              <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
              <span class="visually-hidden">with the <?= esc($tier['name'] ?? '') ?></span>
            </a>

          </article>
        </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
