<?php
declare(strict_types=1);

/**
 * Lead's asset jobs: logos, book bands, Open Graph card.
 *
 *   F:\xampp\php\php.exe tools\build-assets.php
 */

require_once __DIR__ . '/imglib.php';

// ---------------------------------------------------------------------------
// Jobs
// ---------------------------------------------------------------------------

echo "Building assets from Figma exports\n\n";

// --- 1. Header logo lockup (mark + ELITE + PUBLISHING) ----------------------
// Taken from the navbar component export rather than the homepage: there the
// logo sits on a flat white field at roughly 4x, so keying is clean and the
// result is sharp on retina. (The homepage crop was 1x on a gradient.)
echo "  header logo (logo.png)\n";
$navbar = load('Frame 2147239914.png');
$logo   = trimAlpha(keyBackground(crop($navbar, 60, 60, 500, 400), 30.0), 2);
saveSet($logo, 'logo', ['png', 'webp']);
printf("    -> %dx%d\n", imagesx($logo), imagesy($logo));

// --- 1b. White logo for the dark header variant -----------------------------
echo "\n  header logo, light-on-dark (logo-light.png)\n";
$logoLight = trimAlpha(keyBackground(crop($navbar, 40, 520, 540, 380), 30.0), 2);
saveSet($logoLight, 'logo-light', ['png', 'webp']);
printf("    -> %dx%d\n", imagesx($logoLight), imagesy($logoLight));

$home = load('Homepage.png');

// --- 2. Footer logo mark ----------------------------------------------------
// The FOOTER export is 4x, so this one is genuinely high resolution.
echo "\n  footer logo mark (logo-mark.png)\n";
$footer = load('FOOTER.png');
$mark = trimAlpha(keyBackground(crop($footer, 940, 300, 320, 360), 60.0), 2);
saveSet($mark, 'logo-mark', ['png', 'webp']);
printf("    -> %dx%d\n", imagesx($mark), imagesy($mark));

// --- 3. Footer book-cover band (4x source) ----------------------------------
echo "\n  footer book band\n";
$fBand = crop($footer, 0, 1932, 7680, 1264);
foreach ([1280, 1920, 2560] as $w) {
    saveSet(resizeTo($fBand, $w), "footer-band-$w", ['avif', 'webp', 'jpg'], 80);
}

// --- 4. Hero book-cover band (1x source; different backdrop to the footer) --
echo "\n  hero book band\n";
$hBand = crop($home, 0, 845, 1920, 285);
foreach ([1280, 1920] as $w) {
    saveSet(resizeTo($hBand, $w), "hero-band-$w", ['avif', 'webp', 'jpg'], 80);
}

// --- 5. Open Graph card -----------------------------------------------------
echo "\n  open graph card\n";
saveSet(resizeTo(crop($home, 0, 0, 1920, 1005), 1200), 'og-default', ['jpg'], 82);

echo "\nDone.\n";

