<?php
declare(strict_types=1);

/**
 * Full-bleed band of tilted book covers.
 *
 * Two variants exist because the covers are cut out against different
 * backdrops in the design and their tops are jagged: 'hero' sits on the pale
 * hero wash, 'footer' sits on the green footer field. They are not
 * interchangeable — using the wrong one leaves a coloured fringe.
 *
 *   $bandVariant  'hero' | 'footer'   (default 'footer')
 *   $bandEager    bool                true only for the home page hero, which
 *                                     is that page's LCP element
 */
$bandVariant = $bandVariant ?? 'footer';
$bandEager   = $bandEager   ?? false;

$widths = $bandVariant === 'hero' ? [1280, 1920] : [1280, 1920, 2560];
$height = $bandVariant === 'hero' ? 285 : 316;

$srcset = static function (string $ext) use ($bandVariant, $widths): string {
    $parts = [];
    foreach ($widths as $w) {
        $parts[] = asset("img/{$bandVariant}-band-{$w}.{$ext}") . " {$w}w";
    }
    return implode(', ', $parts);
};
?>
<div class="book-band book-band--<?= esc($bandVariant) ?>" aria-hidden="true">
  <picture>
    <source type="image/avif" srcset="<?= esc($srcset('avif')) ?>" sizes="100vw">
    <source type="image/webp" srcset="<?= esc($srcset('webp')) ?>" sizes="100vw">
    <img src="<?= esc(asset("img/{$bandVariant}-band-1920.jpg")) ?>"
         srcset="<?= esc($srcset('jpg')) ?>" sizes="100vw"
         alt="" width="1920" height="<?= (int) $height ?>"
         loading="<?= $bandEager ? 'eager' : 'lazy' ?>"
         decoding="<?= $bandEager ? 'sync' : 'async' ?>"
         <?= $bandEager ? 'fetchpriority="high"' : '' ?>>
  </picture>
</div>
<?php
// Reset so a second include on the same page doesn't inherit these.
$bandVariant = 'footer';
$bandEager   = false;
