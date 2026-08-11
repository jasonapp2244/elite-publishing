<?php
declare(strict_types=1);

/**
 * Shared image helpers for the asset builders in this folder.
 *
 * Each dev has their own tools/assets-<name>.php that requires this file, so
 * nobody has to edit a shared builder and risk clobbering someone else's jobs.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

// The footer export alone is ~24 megapixels and several are held at once.
ini_set('memory_limit', '1536M');

const SRC = __DIR__ . '/../Elite Publishing -fgma-images';
const OUT = __DIR__ . '/../assets/img';

@mkdir(OUT, 0777, true);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function load(string $file): GdImage
{
    $path = SRC . '/' . $file;
    if (!is_file($path)) {
        fwrite(STDERR, "  !! missing source: $file\n");
        exit(1);
    }
    $img = imagecreatefrompng($path);
    if (!$img instanceof GdImage) {
        fwrite(STDERR, "  !! could not decode: $file\n");
        exit(1);
    }
    return $img;
}

function crop(GdImage $src, int $x, int $y, int $w, int $h): GdImage
{
    $w = min($w, imagesx($src) - $x);
    $h = min($h, imagesy($src) - $y);

    $dst = imagecreatetruecolor($w, $h);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopy($dst, $src, 0, 0, $x, $y, $w, $h);
    return $dst;
}

function resizeTo(GdImage $src, int $targetW): GdImage
{
    $sw = imagesx($src);
    $sh = imagesy($src);
    if ($sw === $targetW) {
        return $src;
    }
    $th = (int) round($sh * ($targetW / $sw));

    $dst = imagecreatetruecolor($targetW, $th);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $th, $sw, $sh);
    return $dst;
}

/**
 * Knock out a near-uniform background and un-mix the anti-aliased edges, so a
 * logo lifted off a coloured page sits cleanly on any background.
 *
 * The reference colour is sampled from the crop's top-left corner.
 */
function keyBackground(GdImage $img, float $tolerance = 46.0): GdImage
{
    $w = imagesx($img);
    $h = imagesy($img);

    $bg = imagecolorsforindex($img, imagecolorat($img, 0, 0));
    [$br, $bgc, $bb] = [$bg['red'], $bg['green'], $bg['blue']];

    $out = imagecreatetruecolor($w, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $c = imagecolorsforindex($img, imagecolorat($img, $x, $y));
            $dist = sqrt(
                ($c['red'] - $br) ** 2 +
                ($c['green'] - $bgc) ** 2 +
                ($c['blue'] - $bb) ** 2
            );

            $a = min(1.0, $dist / $tolerance);   // 0 = background, 1 = solid
            if ($a <= 0.02) {
                continue;                        // fully transparent
            }

            // Un-premultiply: recover the true foreground colour.
            $r = (int) round(max(0, min(255, ($c['red']   - $br  * (1 - $a)) / $a)));
            $g = (int) round(max(0, min(255, ($c['green'] - $bgc * (1 - $a)) / $a)));
            $b = (int) round(max(0, min(255, ($c['blue']  - $bb  * (1 - $a)) / $a)));

            $alpha = (int) round(127 * (1 - $a));  // GD: 0 opaque .. 127 clear
            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $alpha));
        }
    }
    return $out;
}

/** Shrink to the bounding box of everything that is not fully transparent. */
function trimAlpha(GdImage $img, int $pad = 0): GdImage
{
    $w = imagesx($img);
    $h = imagesy($img);
    $minX = $w; $minY = $h; $maxX = -1; $maxY = -1;

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $alpha = (imagecolorat($img, $x, $y) >> 24) & 0x7F;
            if ($alpha < 120) {
                if ($x < $minX) $minX = $x;
                if ($x > $maxX) $maxX = $x;
                if ($y < $minY) $minY = $y;
                if ($y > $maxY) $maxY = $y;
            }
        }
    }
    if ($maxX < 0) {
        return $img;   // nothing found; leave it alone
    }

    $minX = max(0, $minX - $pad);
    $minY = max(0, $minY - $pad);
    $maxX = min($w - 1, $maxX + $pad);
    $maxY = min($h - 1, $maxY + $pad);

    return crop($img, $minX, $minY, $maxX - $minX + 1, $maxY - $minY + 1);
}

/** Flatten transparency onto a solid colour (needed before JPEG encoding). */
function flatten(GdImage $img, array $rgb): GdImage
{
    $w = imagesx($img);
    $h = imagesy($img);
    $dst = imagecreatetruecolor($w, $h);
    imagefill($dst, 0, 0, imagecolorallocate($dst, $rgb[0], $rgb[1], $rgb[2]));
    imagealphablending($dst, true);
    imagecopy($dst, $img, 0, 0, 0, 0, $w, $h);
    return $dst;
}

function saveSet(GdImage $img, string $name, array $formats, int $quality = 82): void
{
    foreach ($formats as $fmt) {
        $path = OUT . '/' . $name . '.' . $fmt;
        match ($fmt) {
            'png'  => imagepng($img, $path, 9),
            'jpg'  => imagejpeg(flatten($img, [249, 250, 249]), $path, $quality),
            'webp' => imagewebp($img, $path, $quality),
            'avif' => @imageavif($img, $path, $quality),
            default => null,
        };
        if (is_file($path)) {
            printf("    %-34s %6.1f KB\n", $name . '.' . $fmt, filesize($path) / 1024);
        }
    }
}


