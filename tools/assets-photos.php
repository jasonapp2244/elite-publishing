<?php
declare(strict_types=1);

/**
 * Assets agent — every photograph the build needs, cut from the 1920px Figma
 * page exports and written as AVIF + WebP + JPG at the widths CONTRACT §5 lists.
 *
 *   F:\xampp\php\php.exe tools\assets-photos.php            # everything
 *   F:\xampp\php\php.exe tools\assets-photos.php books      # one group
 *   F:\xampp\php\php.exe tools\assets-photos.php svc:ghostwriting
 *
 * Groups: books, stories, avatars, about, svc, icons
 *
 * Re-runnable from scratch: every output is overwritten, nothing is appended.
 *
 * ---------------------------------------------------------------------------
 * COORDINATE NOTE (see docs/reports/assets.md)
 * ---------------------------------------------------------------------------
 * SPEC §E.1 gives positions as "<slice> y ~a-b". Those y values are already in
 * ARTBOARD pixels, but measured from the top of that _figma-ref slice, not from
 * the top of the artboard. The slices are 1280px-wide, 933px-tall strips, so
 * slice K begins at artboard y = (K-1) * 933 * 1.5 = (K-1) * 1399.5.
 * Every box below was then tightened against the real 1920px export by scanning
 * for the photo's edges, so these are measured, not inferred.
 */

require_once __DIR__ . '/imglib.php';

const PHOTO_Q   = 82;                    // CONTRACT: JPEG-source content
const HERO_Q    = 62;                    // heroes are 1920px wide; keep AVIF < 180KB
const UPSCALE_Q = 70;                    // see emit()
const FMT       = ['avif', 'webp', 'jpg'];

@mkdir(OUT . '/books', 0777, true);
@mkdir(OUT . '/svc', 0777, true);

$only = $argv[1] ?? '';
$want = static fn(string $group): bool => $only === '' || str_starts_with($only, $group);

/** Resize to an exact WxH (resizeTo() only takes a width). */
function resizeExact(GdImage $src, int $w, int $h): GdImage
{
    $dst = imagecreatetruecolor($w, $h);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
    return $dst;
}

/**
 * Crop a box and write it at each width.
 *
 * Several of the contracted widths are larger than the source region — the
 * intro/e2e photos are only ~636px wide in the artboard but CONTRACT §5 asks
 * for 1280 as well. Those variants are pure interpolation: there is no
 * high-frequency detail left to preserve, so encoding them at q82 just spends
 * bytes on resampling noise. Drop to UPSCALE_Q whenever the target exceeds the
 * source, which roughly halves them for no visible difference.
 */
function emit(GdImage $src, array $box, string $name, array $widths, int $q = PHOTO_Q): void
{
    $piece = crop($src, $box[0], $box[1], $box[2], $box[3]);
    foreach ($widths as $w) {
        $scaled = resizeTo($piece, $w);
        saveSet($scaled, $name . '-' . $w, FMT, $w > $box[2] ? min($q, UPSCALE_Q) : $q);
        if ($scaled !== $piece) {
            imagedestroy($scaled);
        }
    }
    printf("    (source %dx%d)\n", $box[2], $box[3]);
    imagedestroy($piece);
}

echo "Assets agent — photography\n\n";

// ---------------------------------------------------------------------------
// 1. Book covers 01-09 — the dedicated Component exports (one cover each)
// ---------------------------------------------------------------------------
// Every "Component 1xx.png" is 1188x3636: the same cover rendered twice on a
// black field inside a dashed Figma selection frame. The first copy sits at a
// constant (80, 80, 1028, 1448) — verified by scanning for the non-black box.
// A 12px inset removes the black that bleeds into the cover's rounded corners.
const BOOK_BOX = [80 + 12, 80 + 12, 1028 - 24, 1448 - 24];
const BOOK_COMPONENTS = [
    'book-01' => 'Component 104.png',   // It's Not Easy Being a Bunny — Marilyn Sadler
    'book-02' => 'Component 105.png',   // Judge Stone — Viola Davis / James Patterson
    'book-03' => 'Component 106.png',   // Project Hail Mary — Andy Weir
    'book-04' => 'Component 107.png',   // The Correspondent — Virginia Evans
    'book-05' => 'Component 108.png',   // Game On — Navessa Allen
    'book-06' => 'Component 109.png',   // Dear Debbie — Freida McFadden
    'book-07' => 'Component 110.png',   // Theo of Golden — Allen Levi
    'book-08' => 'Component 111.png',   // Warriors — Erin Hunter
    'book-09' => 'Component 112.png',   // The Divorce — Freida McFadden
];

if ($want('books')) {
    echo "-- book covers 01-09 (Component exports, full resolution)\n";
    foreach (BOOK_COMPONENTS as $name => $file) {
        echo "  $name  <- $file\n";
        $src = load($file);
        emit($src, BOOK_BOX, "books/$name", [420, 840]);
        imagedestroy($src);
    }

    // book-10 only exists on the Our Books page, inside a card whose bottom
    // ~70px carry a baked-in caption plate. Crop stops above it, so this one is
    // 268x345 (aspect 0.78) where 01-09 are 0.71 — and 268px must be upscaled
    // to reach 840. Flagged in the report; needs an original from the client.
    echo "  book-10 <- Our Books.png\n";
    $ob = load('Our Books.png');
    emit($ob, [826, 955, 268, 345], 'books/book-10', [420, 840]);
    imagedestroy($ob);
    echo "\n";
}

// ---------------------------------------------------------------------------
// 2 + 3. Author-story thumbnails and reviewer avatars — Homepage.png
// ---------------------------------------------------------------------------
if ($want('stories') || $want('avatars')) {
    $home = load('Homepage.png');

    if ($want('stories')) {
        // SPEC: "homepage__05 y 0-380" -> slice 5 starts at 4 * 1399.5 = 5598,
        // so artboard y 5598-5978. Measured edges: y 5603..5982, x 250/730/1210,
        // each card 460x380.
        echo "-- author-story thumbnails (Homepage.png)\n";
        foreach ([250, 730, 1210] as $i => $x) {
            emit($home, [$x, 5603, 460, 380], 'story-' . ($i + 1), [480, 960]);
        }
        echo "\n";
    }

    if ($want('avatars')) {
        // SPEC: "homepage__05 y ~690-720" -> artboard 6288-6318. Measured
        // 6283..6327. Each avatar is a 45x45 circle: 96px output is a 2.1x
        // upscale and will be soft. Originals required from the client.
        echo "-- reviewer avatars (Homepage.png) — 45px source, upscaled\n";
        foreach ([50, 527, 1004, 1481] as $i => $x) {
            emit($home, [$x, 6283, 45, 45], 'avatar-' . ($i + 1), [96]);
        }
        echo "\n";
    }

    imagedestroy($home);
}

// ---------------------------------------------------------------------------
// 4. About Our Company
// ---------------------------------------------------------------------------
if ($want('about')) {
    echo "-- about page photos (About Our Company.png)\n";
    $about = load('About Our Company.png');
    // A1: auburn-haired woman on a stack of books, library shelves behind.
    // SPEC "__01 y ~755-1345" — correct.
    emit($about, [250, 755, 636, 594], 'about-story', [640, 1280]);
    // A2: woman holding "Curse of Stolen Flame" over her face.
    // SPEC "__02 y ~1330 -> __03 y ~410" = artboard 2729-3209; measured 2705-3214.
    emit($about, [250, 2705, 665, 510], 'about-why', [640, 1280]);
    imagedestroy($about);
    echo "\n";
}

// ---------------------------------------------------------------------------
// 5. The ten service pages — hero, intro, end-to-end
// ---------------------------------------------------------------------------
// All ten exports are 1920x8776 with an identical layout, so one set of boxes
// serves all of them. Note the irregular capitalisation in the filenames.
const SERVICE_SOURCES = [
    'books-publishing'         => 'Books Publishing.png',
    'book-editing'             => 'book Editing.png',        // lowercase b
    'book-cover-design'        => 'Book Cover Design.png',
    'book-illustration'        => 'Book Illustration.png',
    'audio-book-production'    => 'Audio Book Production.png',
    'ghostwriting'             => 'Ghostwriting.png',
    'book-marketing'           => 'Book Marketing.png',
    'proofreading'             => 'Proofreading.png',
    'creative-content-writing' => 'Creative Content Writing.png',
    'blog-article-writing'     => 'Blog Article Writing.png',
];

// Intro: SPEC "__02 y ~110-700" -> artboard 1509-2099. Measured 1508 + 594.
const SVC_INTRO = [250, 1508, 636, 594];
// End-to-end: SPEC "__03 y ~0-545" is wrong — the photo starts 45px ABOVE the
// slice-3 boundary (i.e. __02 y ~1354) and is 594 tall, not 817.
const SVC_E2E   = [1034, 2753, 636, 594];

// ---------------------------------------------------------------------------
// The hero is NOT SPEC's y 0-866 band — see docs/reports/assets.md §9
// ---------------------------------------------------------------------------
// SPEC §E.1 says the hero is the export's y 0-866. It is, and that is the
// problem: that band is the *composited* design, so the navbar, the wordmark,
// the <h1>, the body paragraph, both buttons and the opaque "Start Your Book
// Today" form card are all flattened into the pixels. The live HTML hero
// renders on top of it, so all ten pages showed a doubled headline and a ghost
// form. Text cannot be separated from a photograph it was flattened over, and
// there is no bare hero image fill anywhere in the exports (Frame 2147239960
// and Component 95/132/136/140/159 are the service-card grid, pricing cards,
// press band and marquees — no hero photograph among them).
//
// Accepted deviation: each hero is rebuilt from that page's END-TO-END photo,
// the one large text-free photograph on the page, cropped to a full-width
// 2.217:1 band. Design fidelity on the hero image is deliberately traded for a
// hero that is not visibly broken. Consequence: the same photograph now appears
// twice on every service page (hero + section 7). Known and accepted — do not
// "fix" this by reinstating the composited crop.
//
// Resolution: the e2e photo's true extent in the 1920px export is 635x594 —
// measured by scanning the photo's edges against the page background on all ten
// exports, identical every time. The design's layout slot IS the photograph's
// full extent here, so there are no further native pixels to recover, and the
// 1920 hero is a 3.02x enlargement of 635 real pixels. Nothing available does
// better. A mild pre-upscale sharpen (below) buys back some apparent detail.
const SVC_HERO_W = 635;                 // x 1035..1669, measured
const SVC_HERO_H = 286;                 // 635 / (1920/866) = 286.4
// Vertical offset into the 594px-tall e2e photo, chosen per photograph so the
// subject's face (and, where the photo allows it, the books) stay in the band.
// Ten photographs, ten different offsets. Corner radius is ~10px, so every
// offset is kept inside [12, 296] to avoid pulling page background into the
// rounded corners.
const SVC_HERO_Y = [
    'books-publishing'         => 24,   // eyes at the top edge, book cover fills the rest
    'book-editing'             => 14,   // full head with headroom; face at 40% down
    'book-cover-design'        => 125,  // reclining subject — face and the held book together
    'book-illustration'        => 150,  // no face in this photo; centres the stack of books
    'audio-book-production'    => 14,   // full head; books sit below the band, unavoidable
    'ghostwriting'             => 140,  // face at 38% down, plant and window behind
    'book-marketing'           => 130,  // face centred against the full bookcase
    'proofreading'             => 30,   // face is behind the book — frames book, mug, hands
    'creative-content-writing' => 12,   // back view: keeps the whole top-knot in frame
    'blog-article-writing'     => 30,   // face, glasses and the open book all in frame
];

/**
 * Cut the hero band out of the e2e photograph and write it at 1280 and 1920.
 *
 * Both widths are upscales (2.02x and 3.02x), so the band is sharpened *before*
 * the enlargement — a 3x3 convolution applied afterwards only works on
 * interpolated edges, while at native scale it lifts real ones. The kernel is
 * a gentle unsharp (centre 1.5, each neighbour -0.0625); the stronger centre-2.0
 * variant was tried too and reads as over-processed in the hair. Cost of the
 * sharpen is ~15 KB on the 1920 AVIF, which the 180 KB budget absorbs easily.
 *
 * Written at exact dimensions rather than through resizeTo(), because the hero
 * markup declares width=1920 height=866 and 635x286 rounds to 1920x865.
 * The 0.14% vertical stretch that costs is not visible.
 */
function emitHero(GdImage $page, int $offsetY, string $name): void
{
    // +1 on x: the e2e box starts one pixel left of the photograph's real edge,
    // which does not matter in a 636px crop but would put a background column
    // down the side of a full-bleed hero.
    $band = crop($page, SVC_E2E[0] + 1, SVC_E2E[1] + $offsetY, SVC_HERO_W, SVC_HERO_H);
    imageconvolution($band, [[-1, -1, -1], [-1, 24, -1], [-1, -1, -1]], 16, 0);

    foreach ([1280 => 577, 1920 => 866] as $w => $h) {
        $scaled = resizeExact($band, $w, $h);
        saveSet($scaled, $name . '-' . $w, FMT, HERO_Q);
        imagedestroy($scaled);
    }
    printf("    (source %dx%d, offset y+%d, %.2fx upscale)\n",
        SVC_HERO_W, SVC_HERO_H, $offsetY, 1920 / SVC_HERO_W);
    imagedestroy($band);
}

if ($want('svc')) {
    $filter = str_contains($only, ':') ? substr($only, strpos($only, ':') + 1) : '';

    foreach (SERVICE_SOURCES as $slug => $file) {
        if ($filter !== '' && $filter !== $slug) {
            continue;
        }
        echo "-- $slug  <- $file\n";
        // One page in memory at a time: each is ~17 megapixels.
        $page = load($file);
        emitHero($page, SVC_HERO_Y[$slug], "svc/$slug-hero");
        emit($page, SVC_INTRO, "svc/$slug-intro", [640, 1280]);
        emit($page, SVC_E2E,   "svc/$slug-e2e",   [640, 1280]);
        imagedestroy($page);
        echo "\n";
    }
}

// ---------------------------------------------------------------------------
// 6. Brand mark — favicon.svg (hand-authored) + apple-touch-icon.png
// ---------------------------------------------------------------------------
// SPEC §E.2: an isometric stack of books — a green (#60C489) top slab, a green
// left face, a dark-navy (#1B2A4A) right face. Two white page gaps cut across
// the body: they are what makes it read as a *stack* rather than a plain cube,
// and they survive down to 16px (checked by rendering at 16/32/64).
const MARK_TOP   = [16.0, 1.0,  30.0, 8.8,  16.0, 16.6,  2.0, 8.8];
const MARK_LEFT  = [2.0, 8.8,   16.0, 16.6, 16.0, 31.0,   2.0, 23.2];
const MARK_RIGHT = [30.0, 8.8,  16.0, 16.6, 16.0, 31.0,  30.0, 23.2];
const MARK_GAP1  = [2.0, 10.4,  16.0, 18.2, 30.0, 10.4,  30.0, 12.8, 16.0, 20.6, 2.0, 12.8];
const MARK_GAP2  = [2.0, 16.0,  16.0, 23.8, 30.0, 16.0,  30.0, 18.4, 16.0, 26.2, 2.0, 18.4];

const C_GREEN     = [0x60, 0xC4, 0x89];
const C_GREEN_DIM = [0x4C, 0xAD, 0x74];
const C_NAVY      = [0x1B, 0x2A, 0x4A];

if ($want('icons')) {
    echo "-- brand mark\n";

    $poly = static function (array $p): string {
        $out = [];
        for ($i = 0; $i < count($p); $i += 2) {
            $out[] = $p[$i] . ' ' . $p[$i + 1];
        }
        return implode(' ', $out);
    };

    // The page gaps are cut-outs, not white fills, so the mark stays correct on
    // a dark browser tab bar as well as a light one.
    $svg = <<<SVG
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" role="img" aria-label="Elite Publishing">
      <title>Elite Publishing</title>
      <mask id="ep-pages">
        <rect width="32" height="32" fill="#fff"/>
        <polygon points="{$poly(MARK_GAP1)}" fill="#000"/>
        <polygon points="{$poly(MARK_GAP2)}" fill="#000"/>
      </mask>
      <g mask="url(#ep-pages)">
        <polygon points="{$poly(MARK_LEFT)}" fill="#4CAD74"/>
        <polygon points="{$poly(MARK_RIGHT)}" fill="#1B2A4A"/>
      </g>
      <polygon points="{$poly(MARK_TOP)}" fill="#60C489"/>
    </svg>
    SVG;
    $svg .= "\n";   // heredoc closing-marker indent is already stripped by PHP
    file_put_contents(OUT . '/favicon.svg', $svg);
    printf("    %-34s %6.1f KB\n", 'favicon.svg', strlen($svg) / 1024);

    // apple-touch-icon: same mark, white field, drawn at 4x and downsampled so
    // GD's un-antialiased polygon fill comes out clean.
    $ss  = 4;
    $big = imagecreatetruecolor(180 * $ss, 180 * $ss);
    imagefill($big, 0, 0, imagecolorallocate($big, 255, 255, 255));

    // The 32-unit mark, inset to ~72% of the tile so it breathes like an app icon.
    $scale = (180 * $ss) * 0.72 / 32;
    $off   = ((180 * $ss) - 32 * $scale) / 2;
    $draw  = static function (array $pts, array $rgb) use ($big, $scale, $off): void {
        $xy = [];
        foreach ($pts as $i => $v) {
            $xy[] = (int) round($v * $scale + $off);
        }
        imagefilledpolygon($big, $xy, imagecolorallocate($big, $rgb[0], $rgb[1], $rgb[2]));
    };
    $draw(MARK_LEFT,  C_GREEN_DIM);
    $draw(MARK_RIGHT, C_NAVY);
    $draw(MARK_GAP1,  [255, 255, 255]);
    $draw(MARK_GAP2,  [255, 255, 255]);
    $draw(MARK_TOP,   C_GREEN);

    $icon = imagecreatetruecolor(180, 180);
    imagecopyresampled($icon, $big, 0, 0, 0, 0, 180, 180, 180 * $ss, 180 * $ss);
    imagepng($icon, OUT . '/apple-touch-icon.png', 9);
    printf("    %-34s %6.1f KB\n", 'apple-touch-icon.png', filesize(OUT . '/apple-touch-icon.png') / 1024);
    imagedestroy($big);
    imagedestroy($icon);
    echo "\n";
}

echo "Done.\n";
