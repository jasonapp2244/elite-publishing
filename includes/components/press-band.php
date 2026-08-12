<?php
declare(strict_types=1);

/**
 * Full-bleed black press band — SPEC §B.12.
 * 296px tall at 1920, centred white eyebrow, then the mastheads in one row.
 *
 * The mastheads are rendered as styled text rather than traced logo art.
 * Tracing a New York Times masthead produces a trademark liability and a
 * blurry raster; real SVGs need a licence the client has to clear
 * (DECISIONS §13). Swapping text for licensed SVGs later is a change to this
 * file alone.
 *
 * The row scrolls continuously. It reuses the .marquee machinery built for the
 * testimonials rather than growing a second implementation: the same CSS
 * animation, the same pause-on-hover and :focus-within, and the same
 * [data-marquee-toggle] handler in main.js, which finds its own .marquee
 * ancestor and so works for any number of them on a page.
 *
 * The track holds SIX copies of the list, not the two the testimonial marquee
 * uses, and that number is load-bearing. The animation translates the track by
 * -50%, so the second half has to be wide enough to cover the viewport on its
 * own or a bare gap scrolls through. The testimonial cards are wide enough that
 * one copy already exceeds any screen; seven text mastheads are not — measured
 * at 1017px per copy at 1920, against a 2560px widest supported viewport. Three
 * copies per half (3 x 1017 = 3051px) clears it.
 *
 * Only the first copy is exposed; the rest are aria-hidden, so a screen reader
 * and the accessibility audit see each masthead exactly once.
 *
 * No inputs.
 */

$press = ep_data_get('shared', 'press');
if (empty($press['logos'])) {
    return;
}

/** Copies of the list in the track. Must stay EVEN — the -50% translate splits
 *  the track in half, and an odd count puts the seam mid-copy. */
const EP_PRESS_COPIES = 6;
?>
<section class="press-band marquee" aria-label="Press coverage">
  <div class="container-ep">
    <p class="eyebrow press-band__eyebrow"><?= esc($press['eyebrow'] ?? '') ?></p>
  </div>

  <?php /* WCAG 2.2.2: anything that moves for more than five seconds needs a
           way to stop it. Hover and :focus-within are not enough on their own —
           the track holds no focusable elements, so :focus-within can never
           fire, and a touch user has no hover. */ ?>
  <div class="marquee__bar container-ep press-band__bar">
    <button class="marquee__toggle press-band__toggle" type="button"
            data-marquee-toggle aria-pressed="false">
      <span class="marquee__toggle-on">Pause</span>
      <span class="marquee__toggle-off">Play</span>
      <span class="visually-hidden">the scrolling press logos</span>
    </button>
  </div>

  <div class="marquee__viewport">
    <ul class="marquee__track press-band__row list-plain">
      <?php for ($copy = 0; $copy < EP_PRESS_COPIES; $copy++): ?>
        <?php foreach ($press['logos'] as $logo): ?>
          <li class="press-band__logo"<?= $copy > 0 ? ' aria-hidden="true"' : '' ?>><?= esc($logo) ?></li>
        <?php endforeach; ?>
      <?php endfor; ?>
    </ul>
  </div>
</section>
