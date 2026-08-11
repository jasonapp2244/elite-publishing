<?php
declare(strict_types=1);

/**
 * Elite Publishing — shared helpers.
 * Every string that reaches the page goes through esc(). Every URL goes
 * through url() or asset() so the site works from any sub-directory.
 */

// --- Output -----------------------------------------------------------------

/** Escape for HTML text/attribute context. */
function esc(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build a site URL from a root-relative path. */
function url(string $path = ''): string
{
    return EP_BASE . ltrim($path, '/');
}

/**
 * Build an asset URL, appending the file's mtime so long cache lifetimes are
 * safe. Falls back to the plain path if the file is missing.
 */
function asset(string $path): string
{
    $rel  = 'assets/' . ltrim($path, '/');
    $full = EP_ROOT . '/' . $rel;
    $url  = url($rel);

    return is_file($full) ? $url . '?v=' . filemtime($full) : $url;
}

// --- Navigation state -------------------------------------------------------

/** True when $key identifies the page currently being rendered. */
function is_active(string $key): bool
{
    global $pageKey;
    return isset($pageKey) && $pageKey === $key;
}

/** True when any child of a dropdown is the current page. */
function has_active_child(array $item): bool
{
    global $pageKey;
    if (empty($item['children']) || !isset($pageKey)) {
        return false;
    }
    foreach ($item['children'] as $child) {
        if ($child['key'] === $pageKey) {
            return true;
        }
    }
    return false;
}

// --- Images -----------------------------------------------------------------

/**
 * Render a <picture> with AVIF + WebP sources and a raster fallback.
 *
 * Expects sibling files next to $webPath, e.g. for 'img/hero.jpg' it will use
 * 'img/hero.avif' and 'img/hero.webp' when those exist. Width and height are
 * required — they are what keeps Cumulative Layout Shift at zero.
 */
function ep_picture(string $webPath, string $alt, int $w, int $h, array $opts = []): string
{
    $class    = $opts['class']    ?? '';
    $sizes    = $opts['sizes']    ?? null;
    $eager    = $opts['eager']    ?? false;         // true for the LCP image only
    $priority = $opts['priority'] ?? $eager;

    $base    = preg_replace('/\.(jpg|jpeg|png|webp|avif)$/i', '', $webPath);
    $sources = '';

    foreach (['avif' => 'image/avif', 'webp' => 'image/webp'] as $ext => $type) {
        $candidate = $base . '.' . $ext;
        if (is_file(EP_ROOT . '/assets/' . ltrim($candidate, '/'))) {
            $sources .= sprintf(
                '<source type="%s" srcset="%s"%s>',
                $type,
                esc(asset($candidate)),
                $sizes ? ' sizes="' . esc($sizes) . '"' : ''
            );
        }
    }

    return sprintf(
        '<picture>%s<img src="%s" alt="%s" width="%d" height="%d"%s loading="%s" decoding="%s"%s></picture>',
        $sources,
        esc(asset($webPath)),
        esc($alt),
        $w,
        $h,
        $class !== '' ? ' class="' . esc($class) . '"' : '',
        $eager ? 'eager' : 'lazy',
        $eager ? 'sync' : 'async',
        $priority ? ' fetchpriority="high"' : ''
    );
}

// --- Icons ------------------------------------------------------------------

/**
 * Inline SVG icons. Inlining avoids extra requests and keeps icons crisp at
 * any size — both matter for the performance and quality targets.
 * All are 24x24, currentColor, stroke-based unless noted.
 */
function ep_icon(string $name, array $attrs = []): string
{
    $paths = [
        'arrow-up-right' => '<path d="M7 17 17 7M7 7h10v10"/>',
        'arrow-right'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left'     => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'chevron-down'   => '<path d="m6 9 6 6 6-6"/>',
        'plus'           => '<path d="M12 5v14M5 12h14"/>',
        'check'          => '<path d="m20 6-11 11-5-5"/>',
        'menu'           => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'          => '<path d="M6 6l12 12M18 6 6 18"/>',
        'search'         => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'pen'            => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'layers'         => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'book'           => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5Z"/><path d="M4 17.5h16"/>',
        'book-open'      => '<path d="M2 4h6a4 4 0 0 1 4 4v12a3 3 0 0 0-3-3H2Z"/><path d="M22 4h-6a4 4 0 0 0-4 4v12a3 3 0 0 1 3-3h7Z"/>',
        'mic'            => '<rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v4"/>',
        'megaphone'      => '<path d="M3 11v2a1 1 0 0 0 1 1h3l8 5V5L7 10H4a1 1 0 0 0-1 1Z"/><path d="M18 9a3 3 0 0 1 0 6"/>',
        'palette'        => '<path d="M12 21a9 9 0 1 1 9-9c0 2-1.5 3-3 3h-2a2 2 0 0 0-1 3.7A2 2 0 0 1 12 21Z"/><circle cx="7.5" cy="11.5" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16.5" cy="11.5" r="1"/>',
        'file-text'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
        'edit'           => '<path d="M11 4H4v16h16v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>',
        'star'           => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1Z" fill="currentColor" stroke="none"/>',
        'quote'          => '<path d="M10 11H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v8a4 4 0 0 1-4 4"/><path d="M20 11h-4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v8a4 4 0 0 1-4 4"/>',
        'mail'           => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>',
        'phone'          => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.6 2.8a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.8.6a2 2 0 0 1 1.7 2Z"/>',
        'map-pin'        => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'globe'          => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20Z"/>',
        'shield'         => '<path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5Z"/><path d="m9 12 2 2 4-4"/>',
        'users'          => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/>',

        // --- Phase 3 additions (SPEC §E.2) ----------------------------------
        'minus'          => '<path d="M5 12h14"/>',
        'chevron-left'   => '<path d="m15 6-6 6 6 6"/>',
        'chevron-right'  => '<path d="m9 6 6 6-6 6"/>',
        'lightbulb'      => '<path d="M9 18h6M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2Z"/>',
        'paper-plane'    => '<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/>',
        'crown'          => '<path d="m3 7 4.5 4L12 4l4.5 7L21 7l-1.8 12H4.8Z"/>',
        'badge-check'    => '<path d="M12 2l2.5 2.6L18 4l.6 3.5L22 9l-1.7 3L22 15l-3.4 1.5L18 20l-3.5-.6L12 22l-2.5-2.6L6 20l-.6-3.5L2 15l1.7-3L2 9l3.4-1.5L6 4l3.5.6Z"/><path d="m9 12 2 2 4-4"/>',
        'eye'            => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'browser'        => '<rect x="2.5" y="4" width="19" height="16" rx="2"/><path d="M2.5 9h19M6 6.5h.01M8.5 6.5h.01"/>',
        'quill'          => '<path d="M4 20c6-1 9-4 11-8s3-7 3-9c-3 .5-6 1.5-9 4S5 14 4 20Z"/><path d="M4 20l5.5-5.5"/>',
        'hand-pen'       => '<path d="M12 19H5a2 2 0 0 1-2-2v-4"/><path d="m8.5 13.5 8.5-8.5a2.1 2.1 0 0 1 3 3l-8.5 8.5-4 1Z"/>',
        'magnifier'      => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m21 21-5.6-5.6"/>',

        // Solid glyphs — registered in $filled below.
        'play'           => '<path d="M8 5.2v13.6a.6.6 0 0 0 .93.5l10.4-6.8a.6.6 0 0 0 0-1L8.93 4.7a.6.6 0 0 0-.93.5Z" fill="currentColor" stroke="none"/>',
        'sparkle'        => '<path d="M12 2c.5 5 2.5 7.5 8 8-5.5.5-7.5 3-8 8-.5-5-2.5-7.5-8-8 5.5-.5 7.5-3 8-8Z" fill="currentColor" stroke="none"/>',
        'dot'            => '<circle cx="12" cy="12" r="5" fill="currentColor" stroke="none"/>',
        'caret-down'     => '<path d="m6 9.5 6 6 6-6Z" fill="currentColor" stroke="none"/>',

        // Social marks are solid glyphs, not strokes — see $filled below.
        'facebook'  => '<path d="M14 8.5V7c0-.8.2-1.2 1.3-1.2H17V3h-2.6C11.6 3 10.6 4.4 10.6 6.8v1.7H9V11h1.6v10H14V11h2.3l.4-2.5H14Z" fill="currentColor" stroke="none"/>',
        'linkedin'  => '<path d="M6.9 21H3.5V9h3.4v12ZM5.2 7.5A2 2 0 1 1 5.2 3.5a2 2 0 0 1 0 4ZM21 21h-3.4v-5.8c0-1.4 0-3.2-2-3.2s-2.3 1.5-2.3 3.1V21H9.9V9h3.3v1.6h.1a3.6 3.6 0 0 1 3.2-1.8c3.5 0 4.1 2.3 4.1 5.2V21Z" fill="currentColor" stroke="none"/>',
        'instagram' => '<path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2 0 1.8.3 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c0 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2 0-1.8-.2-2.2-.4a3.9 3.9 0 0 1-1.4-.9 3.9 3.9 0 0 1-.9-1.4c-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c0-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2Zm0 3.8a6 6 0 1 0 0 12 6 6 0 0 0 0-12Zm0 9.9a3.9 3.9 0 1 1 0-7.8 3.9 3.9 0 0 1 0 7.8Zm7.6-10.1a1.4 1.4 0 1 1-2.8 0 1.4 1.4 0 0 1 2.8 0Z" fill="currentColor" stroke="none"/>',
    ];

    if (!isset($paths[$name])) {
        return '';
    }

    $class  = $attrs['class'] ?? '';
    $size   = $attrs['size']  ?? null;
    $filled = in_array(
        $name,
        ['star', 'facebook', 'linkedin', 'instagram', 'play', 'sparkle', 'dot', 'caret-down'],
        true
    );

    return sprintf(
        '<svg viewBox="0 0 24 24"%s%s fill="none" stroke="currentColor" stroke-width="%s" '
        . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
        $class !== '' ? ' class="' . esc($class) . '"' : '',
        $size ? ' width="' . (int) $size . '" height="' . (int) $size . '"' : '',
        $filled ? '0' : '1.8',
        $paths[$name]
    );
}

// --- Content data -----------------------------------------------------------

/**
 * Load a file from data/ once and cache it for the rest of the request.
 *
 * Every shared component pulls its own content through this, so a page never
 * passes copy into a partial. Returns [] rather than fatalling if the file is
 * missing — a half-built section is easier to spot and fix than a white screen.
 */
function ep_data(string $name): array
{
    static $cache = [];

    if (!array_key_exists($name, $cache)) {
        $path = EP_ROOT . '/data/' . basename($name) . '.php';
        $data = is_file($path) ? require $path : null;

        if (!is_array($data)) {
            if (EP_DEBUG) {
                trigger_error("ep_data(): data/{$name}.php is missing or does not return an array", E_USER_WARNING);
            }
            $data = [];
        }
        $cache[$name] = $data;
    }

    return $cache[$name];
}

/** Fetch one key out of a data file, with a fallback. */
function ep_data_get(string $name, string $key, mixed $default = []): mixed
{
    return ep_data($name)[$key] ?? $default;
}

// --- Text -------------------------------------------------------------------

/**
 * Render a string that carries the design's deliberate line breaks.
 *
 * Headlines in the exports break at specific words; that break is content, so
 * data files store it as "\n" and this turns it into <br>. The text itself is
 * escaped — only the break is markup.
 */
function ep_lines(?string $text): string
{
    return str_replace("\n", '<br>', esc($text));
}

// --- Images -----------------------------------------------------------------

/**
 * Responsive <picture> across several widths.
 *
 * Pass the path with no extension and no width: 'img/svc/ghostwriting-hero'
 * plus [1280, 1920] resolves to ghostwriting-hero-1280.avif and -1920.avif for
 * each of avif/webp/jpg. A format is only emitted if its files are on disk, so
 * a missing AVIF degrades instead of shipping a broken <source>.
 *
 * $w/$h are the intrinsic dimensions of the LARGEST width — required, because
 * they are what holds layout while the image loads.
 */
function ep_srcset(string $base, array $widths, string $alt, int $w, int $h, array $opts = []): string
{
    sort($widths);

    // The path is meant to carry neither extension nor width, but a trailing
    // extension is the obvious mistake to make and produces the baffling
    // "story-1.jpg-960.jpg". Normalise instead of 404ing.
    $base = preg_replace('/\.(jpe?g|png|webp|avif)$/i', '', $base);

    $class = $opts['class'] ?? '';
    $sizes = $opts['sizes'] ?? '100vw';
    $eager = $opts['eager'] ?? false;
    $fetch = $opts['priority'] ?? $eager;
    $largest = (int) end($widths);

    $set = static function (string $ext) use ($base, $widths): string {
        $parts = [];
        foreach ($widths as $width) {
            $file = "{$base}-{$width}.{$ext}";
            if (is_file(EP_ROOT . '/assets/' . ltrim($file, '/'))) {
                $parts[] = asset($file) . ' ' . $width . 'w';
            }
        }
        return implode(', ', $parts);
    };

    $sources = '';
    foreach (['avif' => 'image/avif', 'webp' => 'image/webp'] as $ext => $type) {
        $srcset = $set($ext);
        if ($srcset !== '') {
            $sources .= sprintf(
                '<source type="%s" srcset="%s" sizes="%s">',
                $type,
                esc($srcset),
                esc($sizes)
            );
        }
    }

    $jpg = $set('jpg');

    return sprintf(
        '<picture>%s<img src="%s"%s sizes="%s" alt="%s" width="%d" height="%d"%s '
        . 'loading="%s" decoding="%s"%s></picture>',
        $sources,
        esc(asset("{$base}-{$largest}.jpg")),
        $jpg !== '' ? ' srcset="' . esc($jpg) . '"' : '',
        esc($sizes),
        esc($alt),
        $w,
        $h,
        $class !== '' ? ' class="' . esc($class) . '"' : '',
        $eager ? 'eager' : 'lazy',
        $eager ? 'sync' : 'async',
        $fetch ? ' fetchpriority="high"' : ''
    );
}

// --- Security ---------------------------------------------------------------

function ep_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }
}

/** Issue (or reuse) the CSRF token for this session. */
function ep_csrf_token(): string
{
    ep_session_start();
    if (empty($_SESSION['ep_csrf'])) {
        $_SESSION['ep_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['ep_csrf'];
}

/** Constant-time CSRF check. */
function ep_csrf_valid(?string $token): bool
{
    ep_session_start();
    return !empty($_SESSION['ep_csrf'])
        && is_string($token)
        && hash_equals($_SESSION['ep_csrf'], $token);
}

// --- Form flash -------------------------------------------------------------

/**
 * Read (and consume) the flash that forms/contact-handler.php left behind.
 *
 * The handler is POST-redirect-GET, so the outcome, the field errors and the
 * user's own input all travel in the session. This is read once per request and
 * cached, so a page can render a banner AND the form can repopulate itself from
 * the same data — whichever calls first does not starve the other.
 *
 * @return array{status:string,message:string,errors:array<string,string>,old:array<string,string>}
 */
function ep_form_flash(): array
{
    static $flash = null;

    if ($flash === null) {
        ep_session_start();
        $raw = $_SESSION['ep_form'] ?? [];
        unset($_SESSION['ep_form']);

        $flash = [
            'status'  => (string) ($raw['status'] ?? ''),
            'message' => (string) ($raw['message'] ?? ''),
            'errors'  => (array) ($raw['errors'] ?? []),
            'old'     => (array) ($raw['old'] ?? []),
            'time'    => (int) ($raw['time'] ?? 0),
            'form'    => (string) ($raw['form'] ?? 'contact'),
        ];
    }

    return $flash;
}

/**
 * True when the flash belongs to $form ('wizard' | 'contact').
 *
 * A service page renders both forms, so each one asks this before showing a
 * banner or repopulating — otherwise a failed wizard submission reports itself
 * inside the hero contact form.
 */
function ep_form_is(string $form): bool
{
    $flash = ep_form_flash();
    return $flash['status'] !== '' && $flash['form'] === $form;
}

/** The value the user previously typed into $field, for repopulating a form. */
function ep_old(string $field, string $form = '', string $default = ''): string
{
    if ($form !== '' && !ep_form_is($form)) {
        return $default;
    }
    return (string) (ep_form_flash()['old'][$field] ?? $default);
}

/** The validation error for $field, or '' if it passed (or is another form's). */
function ep_field_error(string $field, string $form = ''): string
{
    if ($form !== '' && !ep_form_is($form)) {
        return '';
    }
    return (string) (ep_form_flash()['errors'][$field] ?? '');
}

// --- Security ---------------------------------------------------------------

/** Hidden CSRF input, ready to drop into a form. */
function ep_csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . esc(ep_csrf_token()) . '">';
}
