<?php
declare(strict_types=1);

/**
 * Landing-page assets (lp1-lp4).
 *
 *   F:\xampp\php\php.exe tools\assets-lp.php
 *
 * Re-runnable from scratch: every output is overwritten, nothing is appended.
 *
 * ---------------------------------------------------------------------------
 * logo-ink — the lockup for the landing-page footer
 * ---------------------------------------------------------------------------
 * The four landing designs put the footer on SOLID brand green, and draw the
 * logo there as a single ink silhouette. Neither existing asset works on that
 * band:
 *
 *   logo.png        "PUBLISHING" is brand green — green on green, invisible.
 *   logo-light.png   white knockout; legible, but not what is drawn.
 *
 * The recolour is a flat "every opaque pixel becomes ink", preserving alpha.
 *
 * That is worth stating because the obvious guess is wrong. The mark looks like
 * a green book with navy E/3 letterforms cut into it, which suggests the navy
 * should become transparent — and doing that eats the whole right-hand side of
 * the mark. The mark is an isometric book: the top and LEFT faces are green,
 * the RIGHT face is navy, and the E/3 slits between them are ALREADY
 * transparent in logo.png. Every coloured pixel is therefore ink, and the
 * negative space needs no work at all.
 *
 * Source is assets/img/logo.png rather than the Figma export: it is already
 * keyed and trimmed by tools/build-assets.php, and re-keying a second time
 * would only add fringing.
 */

require __DIR__ . '/imglib.php';

const LP_INK = [0x2B, 0x2A, 0x28];   // --ep-ink

echo "Landing-page assets\n";
echo "  footer lockup, ink-on-green (logo-ink.png)\n";

$src = OUT . '/logo.png';
if (!is_file($src)) {
    fwrite(STDERR, "  ! assets/img/logo.png is missing — run build-assets.php first\n");
    exit(1);
}

$logo = imagecreatefrompng($src);
if ($logo === false) {
    fwrite(STDERR, "  ! could not read $src\n");
    exit(1);
}

$w = imagesx($logo);
$h = imagesy($logo);

$out = imagecreatetruecolor($w, $h);
imagealphablending($out, false);
imagesavealpha($out, true);
imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));

$inked = 0;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $alpha = (imagecolorat($logo, $x, $y) >> 24) & 0x7F;

        if ($alpha === 127) {
            continue;                       // negative space — leave it alone
        }

        $inked++;
        imagesetpixel(
            $out,
            $x,
            $y,
            imagecolorallocatealpha($out, LP_INK[0], LP_INK[1], LP_INK[2], $alpha)
        );
    }
}

printf("    %d px inked\n", $inked);

saveSet($out, 'logo-ink', ['png', 'webp']);
printf("    -> %dx%d\n", $w, $h);

echo "\nDone.\n";
