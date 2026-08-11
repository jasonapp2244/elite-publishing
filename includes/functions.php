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
    $filled = in_array($name, ['star', 'facebook', 'linkedin', 'instagram'], true);

    return sprintf(
        '<svg viewBox="0 0 24 24"%s%s fill="none" stroke="currentColor" stroke-width="%s" '
        . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
        $class !== '' ? ' class="' . esc($class) . '"' : '',
        $size ? ' width="' . (int) $size . '" height="' . (int) $size . '"' : '',
        $filled ? '0' : '1.8',
        $paths[$name]
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

/** Hidden CSRF input, ready to drop into a form. */
function ep_csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . esc(ep_csrf_token()) . '">';
}
