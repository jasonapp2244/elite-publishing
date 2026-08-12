<?php
declare(strict_types=1);

/**
 * Full-bleed band of tilted book covers.
 *
 * Built from the ten individual covers in data/books.php rather than from one
 * pre-composed strip. It was a single flat image until the client asked for the
 * covers to lift on hover — and a baked bitmap has no per-cover element to
 * hover, so there was nothing to attach the effect to.
 *
 * The trade-off, stated plainly: a CSS reconstruction is not pixel-identical to
 * the artwork it replaces. The tilts and offsets below were tuned against
 * assets/img/hero-band-1920.jpg until the two read the same at 1920. What the
 * rebuild buys, besides the hover: the covers now scale with the viewport
 * instead of being resampled from a fixed raster, and the band costs ten small
 * shared images that every other cover rail on the site already loads, instead
 * of a dedicated 1920/2560 strip in three formats.
 *
 * It stays DECORATIVE. The band repeats covers already listed on Our Books and
 * in the home rails, so the container is aria-hidden and every alt is empty —
 * exactly the semantics the flat image had. The hover lift is a visual flourish
 * and carries no information.
 *
 * Two variants remain because the band sits on different grounds and is drawn
 * at different heights: 'hero' on the pale wash, 'footer' on the green field.
 *
 *   $bandVariant  'hero' | 'footer'   (default 'footer')
 *   $bandEager    bool                true only for the home page hero, which
 *                                     is that page's LCP element
 */

$bandVariant = $bandVariant ?? 'footer';
$bandEager   = $bandEager   ?? false;

$bandBooks = ep_data('books');
if ($bandBooks === []) {
    return;
}

/**
 * Per-cover tilt in degrees and vertical drop in band-height units.
 *
 * Read off the original strip: the covers are not evenly staggered, and an
 * even stagger reads as a graphic pattern rather than a pile of books. Index
 * into this by position, wrapping, so a catalogue of any length still works.
 */
$bandTilt = [-3.4, 2.6, -2.0, 1.4, -3.0, 2.2, -1.2, 3.0, -2.6, 1.8];
$bandDrop = [0.09, 0.15, 0.06, 0.18, 0.03, 0.13, 0.08, 0.16, 0.05, 0.12];
?>
<div class="book-band book-band--<?= esc($bandVariant) ?>" aria-hidden="true">
  <ul class="book-band__row list-plain">
    <?php foreach (array_values($bandBooks) as $i => $book): ?>
      <li class="book-band__item"
          style="--tilt: <?= $bandTilt[$i % count($bandTilt)] ?>deg; --drop: <?= $bandDrop[$i % count($bandDrop)] ?>;">
        <?= ep_srcset(
            $book['img'] ?? '',
            [420],
            '',
            420,
            630,
            [
                'class'  => 'book-band__cover',
                'sizes'  => '(min-width: 1200px) 12vw, 22vw',
                'eager'  => $bandEager,
            ]
        ) ?>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php
// Reset so a second include on the same page doesn't inherit these.
$bandVariant = 'footer';
$bandEager   = false;
