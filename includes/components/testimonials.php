<?php
declare(strict_types=1);

/**
 * Testimonial marquee — SPEC §C.1 §12, §B.8b.
 *
 * Full-bleed row of four review cards that overflows both edges of the
 * viewport and scrolls infinitely. The track is duplicated once so the loop is
 * seamless; the copy is aria-hidden so screen readers and the accessibility
 * audit see each quote exactly once.
 *
 * Animation is CSS-only and stops under prefers-reduced-motion (tokens.css
 * zeroes --dur and animation-duration globally), leaving a normal horizontal
 * scroll region.
 *
 * No inputs.
 */

$cards = ep_data_get('shared', 'testimonials');
if (empty($cards)) {
    return;
}

/** One card. Rendered twice — once real, once as the aria-hidden loop copy. */
$renderCard = static function (array $c, bool $clone): void {
    ?>
    <li class="review-card"<?= $clone ? ' aria-hidden="true"' : '' ?>>
      <div class="review-card__top">
        <span class="review-card__stars" role="img" aria-label="Rated 5 out of 5">
          <?= str_repeat(ep_icon('star', ['size' => 16]), 5) ?>
        </span>
        <span class="review-card__badge review-card__badge--<?= esc($c['badge'] ?? 'mark') ?>"
              aria-hidden="true"></span>
      </div>
      <blockquote class="review-card__quote"><?= esc($c['quote'] ?? '') ?></blockquote>
      <figcaption class="review-card__by">
        <span class="review-card__avatar" aria-hidden="true"></span>
        <span>
          <strong><?= esc($c['name'] ?? '') ?></strong>
          <span class="review-card__role"><?= esc($c['role'] ?? '') ?></span>
        </span>
      </figcaption>
    </li>
    <?php
};
?>
<section class="section marquee" aria-label="What authors say about us">
  <div class="marquee__viewport">
    <ul class="marquee__track list-plain">
      <?php foreach ($cards as $c) { $renderCard($c, false); } ?>
      <?php foreach ($cards as $c) { $renderCard($c, true); } ?>
    </ul>
  </div>
</section>
