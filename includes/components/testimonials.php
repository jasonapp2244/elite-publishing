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

/**
 * Which platform each 'badge' value stands for, and the mark to draw for it.
 *
 * The badges were flat coloured squares — green, red, black — standing in for
 * the platforms the reviews came from. These are the same files the review
 * platform cards further up the page use, so a Trustpilot review is marked with
 * the Trustpilot logo in both places instead of a green square in one and a
 * logo in the other.
 *
 * 'fill' matches the platform cards: Trustpilot's artwork is a filled tile and
 * fills the badge, the rest are marks that need a plate behind them.
 *
 * A badge value that is not listed here keeps its plain coloured tile. 'mark' is
 * deliberately absent — it names no platform, and picking one for it would be
 * inventing where a review was left.
 */
const EP_REVIEW_BADGES = [
    'trustpilot' => ['file' => 'img/platforms/trustpilot.png', 'fill' => true],
    'google'     => ['file' => 'img/platforms/google.png',     'fill' => false],
    'reviews-io' => ['file' => 'img/platforms/reviews-io.png', 'fill' => false],
    'sitejabber' => ['file' => 'img/platforms/sitejabber.png', 'fill' => false],
];

/** One card. Rendered twice — once real, once as the aria-hidden loop copy. */
$renderCard = static function (array $c, bool $clone): void {
    $badge = (string) ($c['badge'] ?? 'mark');
    $mark  = EP_REVIEW_BADGES[$badge] ?? null;
    ?>
    <li class="review-card"<?= $clone ? ' aria-hidden="true"' : '' ?>>
      <div class="review-card__top">
        <span class="review-card__stars" role="img" aria-label="Rated 5 out of 5">
          <?= str_repeat(ep_icon('star', ['size' => 16]), 5) ?>
        </span>
        <span class="review-card__badge review-card__badge--<?= esc($badge) ?><?= $mark ? ($mark['fill'] ? ' review-card__badge--fill' : ' review-card__badge--img') : '' ?>"
              aria-hidden="true"><?php if ($mark): ?><img
                src="<?= esc(asset($mark['file'])) ?>" alt=""
                width="<?= $mark['fill'] ? 28 : 18 ?>" height="<?= $mark['fill'] ? 28 : 18 ?>"
                decoding="async"><?php endif; ?></span>
      </div>
      <blockquote class="review-card__quote"><?= esc($c['quote'] ?? '') ?></blockquote>
      <figcaption class="review-card__by">
        <?php /* Decorative: the reviewer's name sits right next to it as real
                 text, so an alt would just be read twice. Explicit width and
                 height keep the 32px circle from shifting anything while it
                 loads.

                 eager, and deliberately so. These sit in a marquee track that
                 is far wider than the viewport, and lazy loading decides from
                 an element's LAYOUT position — which a CSS transform does not
                 change. The cards parked off to the right were therefore never
                 "scrolled into view" as far as the loader was concerned, so two
                 of the eight avatars never loaded and travelled past as empty
                 circles. Same trap as the press band; see that component.

                 priority => false keeps fetchpriority="high" off them: eager is
                 about loading at all, and eight 32px avatars must not compete
                 with the hero for early bandwidth. */ ?>
        <?= ep_srcset(
              $c['img'] ?? 'img/avatar-1',
              [96],
              '',
              32,
              32,
              ['class' => 'review-card__avatar', 'sizes' => '32px',
               'eager' => true, 'priority' => false]
            ) ?>
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
  <?php /* The Pause/Play control was removed at the client's request.
           What still stops this row: hover (mouse), :focus-within, and
           prefers-reduced-motion, which drops the animation entirely. What no
           longer stops it: a keyboard or touch user deliberately choosing to —
           they have no control, which is the WCAG 2.2.2 failure the button
           existed to answer. Restoring it means putting this block back and
           re-adding initMarquees() to assets/js/main.js. */ ?>
  <div class="marquee__viewport">
    <ul class="marquee__track list-plain">
      <?php foreach ($cards as $c) { $renderCard($c, false); } ?>
      <?php foreach ($cards as $c) { $renderCard($c, true); } ?>
    </ul>
  </div>
</section>
