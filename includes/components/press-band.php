<?php
declare(strict_types=1);

/**
 * Full-bleed black press band — SPEC §B.12.
 * 296px tall at 1920, centred white eyebrow, then the mastheads in one row.
 *
 * The mastheads are the client's supplied artwork, in assets/img/press/. They
 * were styled TEXT until that artwork arrived — tracing a masthead by hand is a
 * trademark liability and a blurry raster, so the band held text rather than
 * guess at logo shapes (DECISIONS §13). data/shared.php holds the file and the
 * name for each; see the note there about the one masthead with no file.
 *
 * The files are white-on-transparent PNGs on a uniform 155x31 canvas, which is
 * why nothing here has to normalise them: they all occupy the same box, so the
 * row spaces itself evenly without per-logo tuning. A future logo that is not
 * that size will need its own width, and this is where that would go.
 *
 * The row scrolls continuously. It reuses the .marquee machinery built for the
 * testimonials rather than growing a second implementation: the same CSS
 * animation and the same pause-on-hover and :focus-within. The Pause/Play
 * button that machinery used to carry was removed at the client's request; see
 * the note below the eyebrow.
 *
 * The track holds SIX copies of the list, not the two the testimonial marquee
 * uses, and that number is load-bearing. The animation translates the track by
 * -50%, so the second half has to be wide enough to cover the viewport on its
 * own or a bare gap scrolls through. The testimonial cards are wide enough that
 * one copy already exceeds any screen; six mastheads are not. At 1920 a copy is
 * six 155px logos plus six 84px gaps — 1434px — so three copies per half give
 * 4302px against a 2560px widest supported viewport. Comfortable, and it stays
 * clear if a seventh logo is added rather than removed.
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

  <?php /* The Pause/Play control was removed at the client's request — see the
           same note in components/testimonials.php for what that costs. */ ?>
  <div class="marquee__viewport">
    <ul class="marquee__track press-band__row list-plain">
      <?php for ($copy = 0; $copy < EP_PRESS_COPIES; $copy++): ?>
        <?php foreach ($press['logos'] as $logo): ?>
          <?php
          /* Only the first copy is real to assistive tech. The duplicates carry
             aria-hidden AND an empty alt — either alone leaves the masthead
             announced six times, because an aria-hidden subtree still exposes
             nothing while a named image inside it can still be reached by some
             older screen readers walking the DOM. */
          $isDupe = $copy > 0;
          ?>
          <?php /* NOT loading="lazy". It was, and 19 of the 36 images never
                   loaded: lazy loading decides from the intersection of an
                   element's LAYOUT position with the viewport, and a marquee
                   moves its track with a CSS transform, which does not change
                   that position. The copies parked off to the right are
                   therefore never "scrolled into view" as far as the loader is
                   concerned, so they stay blank and travel across the band as
                   holes. Eager costs nothing here: six unique files totalling
                   ~9KB, and the 36 tags share them from cache. */ ?>
          <li class="press-band__logo"<?= $isDupe ? ' aria-hidden="true"' : '' ?>>
            <img src="<?= esc(asset($logo['file'] ?? '')) ?>"
                 alt="<?= $isDupe ? '' : esc($logo['name'] ?? '') ?>"
                 width="155" height="31" decoding="async">
          </li>
        <?php endforeach; ?>
      <?php endfor; ?>
    </ul>
  </div>
</section>
