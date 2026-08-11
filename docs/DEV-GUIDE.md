# Dev guide — Elite Publishing

Read this before writing a line of code. The foundation is already built and
verified against the design; your job is to build pages **on top of it**, not
to reinvent it.

Local URL: `http://localhost/Elite%20Publishing/`
Design reference slices: `_figma-ref/<page-slug>__NN.jpg` (read in NN order, top to bottom)
Original exports: `Elite Publishing -fgma-images/*.png` (1920px wide artboards)

---

## 1. Files you own vs files you must not touch

**Do not edit** (owned by the lead — request changes in your report instead):
```
includes/config.php      includes/functions.php   includes/head.php
includes/header.php      includes/footer.php      includes/components/book-band.php
assets/css/tokens.css    assets/css/main.css      assets/js/main.js
tools/build-assets.php   .htaccess
```

**You own** only the files listed in your brief, plus your own page stylesheet
`assets/css/p-<yourarea>.css`. Everything page-specific goes there. At build
time all `p-*.css` files are concatenated and minified into one bundle.

If two of you need the same new component, say so in your report — the lead
will add it to `main.css` once, rather than it being written twice.

---

## 2. Page skeleton — copy this exactly

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'about';                       // nav key, see ep_nav()
$pageTitle       = 'About Our Company';           // no brand suffix, head.php adds it
$pageDescription = 'One sentence, ~150 characters, unique to this page.';
$pageCss         = ['css/p-core.css'];            // your page stylesheet
// $pageJs       = ['js/p-something.js'];         // only if you need page JS

require __DIR__ . '/includes/head.php';
?>

<section class="section">
  <div class="container-ep">
    ...
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
```

`config.php` **must** come first — `head.php` needs it, and so does any
constant you use above the include.

`head.php` opens `<main>`; `footer.php` closes it and renders the footer plus
the book-cover band. Never write your own `<header>`, `<footer>`, `<main>`,
`<html>` or `<body>`.

---

## 3. Design tokens — sampled from the exports, do not guess

| Token | Value | Use |
|---|---|---|
| `--ep-green` | `#60C489` | brand primary, buttons, panels, footer field |
| `--ep-green-tint` | `#EBF5EF` | light green section background |
| `--ep-green-tint-2` | `#F3FBF6` | lighter wash |
| `--ep-ink` | `#2B2A28` | all headings and body text |
| `--ep-black` | `#000000` | dark bands, primary button fill |
| `--ep-bg` | `#F9FAF9` | page background |
| `--ep-white` | `#FFFFFF` | cards |
| `--ep-peach` | `#FDF4EF` | warm accent block |

**Font: Urbanist**, self-hosted, weights 400/500/600/700. Confirmed from the
Figma properties panel, along with `h2 = 60px / 70px line-height / -2.5%
letter-spacing / weight 600`. Headings are weight **600**, never 700, unless a
slice clearly shows otherwise.

Container is `1420px` (`.container-ep`) — measured off the artboards, wider
than any Bootstrap container. Use `.container-ep`, never `.container`.

**Never hardcode a hex, font-size, radius or spacing value.** Use the CSS
custom properties from `tokens.css`. If you need a value that does not exist,
say so in your report rather than inventing one.

---

## 4. Components already built — use these, don't rewrite them

From `main.css`:

- `.container-ep`, `.section`, `.section-sm`
- `.bg-tint`, `.bg-tint-2`, `.bg-dark-ep`, `.bg-peach`
- `.eyebrow` — the ◉ + ALL-CAPS label above most section headings
- `.ep-btn` + `--primary` `--outline` `--green` `--white` `--ghost-white`
- `.link-arrow` — the green "Learn More ↗" inline link
- `.ep-card`, `.ep-card__icon`, `.ep-card__title`, `.ep-card__text`
- `.panel-green`, `.tile-glass` — the green feature panel and its inner tiles
- `.ep-faq__item / __btn / __icon / __panel` — accordion (JS is already wired;
  wrap the group in `[data-accordion]` for single-open behaviour)
- `.ep-input`, `.ep-textarea`, `.ep-select`, `.ep-label`, `.ep-field`, `.ep-error`
- `.ep-scroller` + `[data-scroller]`, `[data-scroll-prev]`, `[data-scroll-next]`
  — horizontal carousels, JS already wired
- `.stack-8/16/24`, `.list-plain`, `.visually-hidden`

Bootstrap 5.3 is loaded — use its **grid and utilities** (`row`, `col-*`, `g-4`,
`d-flex`, spacing helpers). Bootstrap's **JavaScript bundle is deliberately not
loaded**, so do not use `data-bs-*` components (modal, collapse, carousel,
dropdown). Everything interactive is hand-rolled in `main.js`.

### Helpers
```php
esc($string)                    // ALWAYS escape output
url('about.php')                // site-relative URL
asset('css/p-core.css')         // asset URL, auto cache-busted
ep_icon('arrow-up-right')       // inline SVG, see functions.php for the full list
ep_picture('img/x.jpg', 'alt', 800, 600, ['eager' => true])
ep_csrf_field()                 // hidden CSRF input for forms
```

Available icons: `arrow-up-right, arrow-right, arrow-left, chevron-down, plus,
check, menu, close, search, pen, layers, book, book-open, mic, megaphone,
palette, file-text, edit, star, quote, mail, phone, map-pin, globe, shield,
users, facebook, linkedin, instagram`. Need another? Ask — don't inline a
random SVG.

---

## 5. Images

Photographic assets must be cut out of the page exports. **Do not do this by
hand and do not hotlink the huge PNGs.** Add a job to `tools/build-assets.php`
via your report, or if your brief says you own extraction, follow the existing
pattern in that file: `crop()` → optional `keyBackground()` → `resizeTo()` →
`saveSet()` producing AVIF + WebP + a fallback.

Every `<img>` **must** have `width`, `height` and `alt`. Missing dimensions
cause layout shift, which costs us the performance score. Decorative images get
`alt=""`. Only the single largest above-the-fold image on a page may use
`loading="eager"` + `fetchpriority="high"`; everything else is `loading="lazy"`.

---

## 6. Copy

Transcribe the **real text from the design slices**, literally. Do not
paraphrase, do not improve it, do not invent filler. If a slice is genuinely
unreadable, put the closest reading you can and flag it in your report as
`[VERIFY]` — do not silently make something up. Wrong copy is a bug that is
expensive to find later.

Note the design contains a typo in the Contact page frame name ("Conatct Us").
Keep real page copy as designed; only fix obvious typos if they appear in
*frame names*, not in body copy.

---

## 7. Accessibility and semantics — non-negotiable

- One `<h1>` per page; heading levels descend without skipping.
- Sections that are landmarks get `<section aria-labelledby="...">` or an
  `aria-label`.
- Interactive things are `<button>` or `<a>`, never a clickable `<div>`.
- Every form control has an associated `<label>` (or `aria-label`).
- Icon-only buttons need an `aria-label`.
- Colour contrast must hit WCAG AA. `--ep-ink` on `--ep-green` passes;
  white on `--ep-green` does **not** pass for small text — use `--ep-ink` for
  body copy on green, white only for large headings.

---

## 8. Responsive

Build at 1920 first (that's the artboard), then verify and fix at
**1440 / 1024 / 768 / 390**. The type scale is already fluid via `clamp()`, so
you usually only need to adjust grid columns and section padding.

---

## 9. When you finish

1. Load every page you built at `http://localhost/Elite%20Publishing/` and
   confirm it renders with **zero** PHP notices/warnings and zero console errors.
2. Compare side by side against your `_figma-ref` slices.
3. Write `docs/reports/<your-name>.md` listing: pages built, anything you could
   not match and why, any `[VERIFY]` copy, and any shared component or token you
   need the lead to add.
