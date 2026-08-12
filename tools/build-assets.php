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

/* 1b was a white logo-light for a dark header variant that was never built, and
   nothing on the site has ever referenced it. Dropped with the files. */

$home = load('Homepage.png');

// --- 2. Footer logo mark ----------------------------------------------------
// The FOOTER export is 4x, so this one is genuinely high resolution.
echo "\n  footer logo mark (logo-mark.png)\n";
$footer = load('FOOTER.png');
$mark = trimAlpha(keyBackground(crop($footer, 940, 300, 320, 360), 60.0), 2);
saveSet($mark, 'logo-mark', ['png', 'webp']);
printf("    -> %dx%d\n", imagesx($mark), imagesy($mark));

/* 3 and 4 built the hero and footer cover strips as flat images. The band is
   now ten individual covers positioned in CSS so each one can lift on hover
   (DECISIONS §17d), so those 15 files were dead weight and are gone. The crops
   are in git history if the flat strip is ever wanted back:
     git show HEAD~1 -- tools/build-assets.php */

// --- 3. Open Graph card -----------------------------------------------------
echo "\n  open graph card\n";
saveSet(resizeTo(crop($home, 0, 0, 1920, 1005), 1200), 'og-default', ['jpg'], 82);

echo "\nDone.\n";

