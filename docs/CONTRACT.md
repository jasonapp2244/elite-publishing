# Build contract — Phase 3

Five agents work in parallel. This file is the interface between them: the exact
shape of every data array, the exact signature of every shared component, and
the exact filename of every image. **Nobody invents a key, a partial name or an
image path that is not in this document.** If you need one that is missing, say
so in your report — do not add it yourself.

Read order: `docs/DEV-GUIDE.md` → `docs/SPEC.md` (your sections) → this file.

---

## 1. Ownership map — who writes what

| Agent | Owns (exclusive write access) |
|---|---|
| **Lead** | `includes/**`, `assets/css/main.css`, `assets/css/tokens.css`, `assets/js/main.js`, `.htaccess`, `tools/imglib.php`, `tools/build-assets.php` |
| **PM** | `data/shared.php`, `data/services.php`, `data/books.php`, `data/pricing.php`, `docs/CLIENT-QUESTIONS.md` |
| **Assets** | `tools/assets-photos.php`, everything it writes under `assets/img/` |
| **Dev 1** | `index.php`, `assets/css/p-home.css` |
| **Dev 2** | `service.php`, `pricing.php`, `assets/css/p-service.css`, `assets/css/p-pricing.css` |
| **Dev 3** | `about.php`, `our-books.php`, `contact.php`, `privacy-policy.php`, `terms-conditions.php`, `forms/contact-handler.php`, `assets/css/p-core.css`, `assets/css/p-contact.css` |
| **QA** | `docs/QA-REPORT.md` only — QA reports, it does not patch |

Touching a file you do not own is a merge conflict, not a contribution.

---

## 2. Data files — exact shapes

All four files `return` a plain array. No side effects, no output, no `require`
of anything but nothing. Every file starts:

```php
<?php
declare(strict_types=1);
return [ /* ... */ ];
```

Load them with `ep_data('shared')` (helper added by the Lead — it caches and
resolves the path). Never `require 'data/foo.php'` directly.

### 2.1 `data/shared.php`

Content that appears on many pages. Copy is transcribed **verbatim** from
`docs/SPEC.md §D.2`. Anything not present in the design carries
`'draft' => true` (see `docs/DECISIONS.md` §3–5).

```php
return [
  // SPEC §D.2 "Our Services carousel" — 8 cards. Not 10. See DECISIONS §9.
  'services_carousel' => [
    'eyebrow' => 'OUR SERVICES',
    'heading' => 'Featured Self-Publishing Solutions',
    'intro'   => 'Whether you need standalone book editing or …',
    'cards'   => [
      [
        'slug'  => 'books-publishing',      // must match a key of EP_SERVICES
        'title' => "Books\nPublishing",     // \n = the deliberate line break (SPEC §B.5)
        'icon'  => 'search',                // an ep_icon() name, see §4
        'text'  => 'Transform your raw manuscript into …',  // FULL sentence
        'draft' => true,                    // true when the continuation is ours
      ],
      // …8 total, in SPEC §D.2 order
    ],
  ],

  // SPEC §D.2 "Your Publishing Journey" — 4 steps
  'journey' => [
    'heading' => 'Your Publishing Journey',
    'intro'   => 'From your first idea to a bestselling book, …',
    'steps'   => [
      ['title' => 'Share Your Idea', 'text' => '…', 'icon' => 'lightbulb'],
      // …4 total
    ],
  ],

  // SPEC §D.3 "Our Publishing Process" — 7 tabs. Steps 2-7 are drafted.
  'process' => [
    'eyebrow' => 'OUR PUBLISHING PROCESS',
    'heading' => 'From Idea To Published Book In 7 Simple Steps',
    'steps'   => [
      [
        'tab'   => 'Consultation',
        'title' => 'Consultation',
        'text'  => 'Our streamlined publishing process ensures …',
        'check' => ['30-min video or phone call', 'Custom roadmap preview',
                    'Zero commitment required', 'Genre & audience analysis'],
        'draft' => false,   // step 1 only
      ],
      // …7 total; tabs 2-7 exactly: Planning, Writing, Editing, Design,
      //   Publishing, Marketing — each with 'draft' => true
    ],
  ],

  // SPEC §D.2 "Author Stories" — 3 video cards
  'stories' => [
    'eyebrow' => 'AUTHOR STORIES',
    'heading' => "What Our\nAuthors Say",     // \n = designed line break
    'intro'   => 'Discover the inspiring journeys of authors …',
    'cta'     => 'Watch More Stories',
    'cards'   => [
      ['name' => 'Clara Wen', 'title' => 'Everything Remembered',
       'img'  => 'img/story-1', 'placeholder' => true],
      // …3 total, img => story-1 / story-2 / story-3.
      // NO extension and NO width — ep_srcset() appends both. See §5.
    ],
  ],

  // SPEC §D.2 "Testimonial marquee" — 4 cards, card 4 duplicates card 1
  'testimonials' => [
    ['quote' => '"If you want to hire a book publisher …"',
     'name'  => 'Marcus Vance,',            // trailing comma is in the design
     'role'  => 'Author of Shadows Over Orion',
     'badge' => 'trustpilot',               // trustpilot | google | mark
     'img'   => 'img/avatar-1.jpg'],
    // …4 total
  ],

  // SPEC §D.3 "Review platforms" — 4 cards
  'platforms' => [
    ['name' => 'Trustpilot', 'score' => '4.9', 'reviews' => 'Based on 1,247 reviews',
     'link' => 'View on Trustpilot', 'href' => '#', 'style' => 'squares'],
    // …4 total; 'style' => 'squares' for Trustpilot, 'stars' for the rest
  ],

  // SPEC §D.2 "FAQ" — 6 items, answers 2-6 drafted
  'faq' => [
    'eyebrow'  => 'FAQ',
    'heading'  => 'Frequently Asked Questions.',   // trailing full stop is designed
    'items'    => [
      ['q' => 'What genres do you work with?',
       'a' => 'Fiction, non-fiction, romance , christian , self-help, …',  // verbatim, spaces before commas kept
       'draft' => false],
      // …6 total, items 2-6 'draft' => true
    ],
  ],

  // SPEC §D.2 "CTA + wizard"
  'cta' => [
    'heading' => "Let's Bring Your\nBook To Life",
    'intro'   => 'Ready to self-publish or have questions …',
    'site'    => 'ElitePublishing.co',
    'email'   => 'contact@elitepublishing.co',
    'buttons' => [
      ['label' => 'Publish Your Book', 'href' => 'contact.php', 'variant' => 'primary'],
      ['label' => 'Free Consultation', 'href' => 'contact.php', 'variant' => 'outline'],
    ],
  ],

  // SPEC §D.2 wizard, 4 steps. Step 3 option 4 = '$20,000+' per DECISIONS §7.
  'wizard' => [
    ['eyebrow' => 'STEP 1 OF 4', 'question' => 'What Genre Is Your Book?',
     'name' => 'genre', 'cols' => 2, 'cta' => 'Continue',
     'options' => [
       ['emoji' => '📖', 'label' => 'Fiction', 'selected' => true],
       // …6 total
     ]],
    // steps 2,3 as SPEC; step 4 is 'type' => 'fields' with the four inputs
  ],

  // SPEC §D.1 press band — 7 logos, order matters
  'press' => [
    'eyebrow' => 'RECOGNIZED BY AUTHORS ACROSS THE GLOBE',
    'logos'   => ['SNN', 'TechCrunch', 'techopedia', 'TECH TIMES',
                  'The New York Times', 'WAPAKONETA DAILY NEWS', 'yahoo! news'],
  ],
];
```

### 2.2 `data/services.php`

Keyed by slug — the ten keys of `EP_SERVICES`, same order. Drives the three
sections that differ between service pages (SPEC §C.8: hero, intro, end-to-end).
Everything else on those pages is shared.

```php
return [
  'ghostwriting' => [
    'title'     => 'Ghostwriting',                  // matches EP_SERVICES
    'meta_desc' => '…150 chars, unique per service…',
    'hero' => [
      'h1'    => "Turn Your Idea Into A\nPowerful, Professionally\nWritten Book",  // \n = designed breaks
      'text'  => 'We transform your ideas into compelling …',
      'image' => 'img/svc/ghostwriting-hero',       // NO extension — see §3
    ],
    'intro' => [
      'h2'    => "Professional Ghostwriting\nThat Brings Your Story To Life",
      'paras' => ['…', '…', '…', '…'],              // 2-4, exactly as SPEC
      'image' => 'img/svc/ghostwriting-intro',
    ],
    'e2e' => [
      'h2'      => "End-To-End Ghostwriting For\nYour Book",
      'text'    => 'We provide professional ghostwriting services …',
      'label'   => 'What We Offer:',                // null on blog-article-writing
                                                    //   and audio-book-production (SPEC §D.10)
      'offers'  => ['Writing', 'Editing', 'Structuring',
                    'Ghostwriting', 'Storytelling', 'Collaboration'],  // 6, rendered 2 cols x 3
      'image'   => 'img/svc/ghostwriting-e2e',
    ],
    // SPEC §D.10 "Why Us band" is identical on all ten — it lives in
    // shared.php['service_why'], NOT here.
  ],
  // …10 total
];
```

Plus, at the top level of `shared.php`, the identical-on-all-ten Why Us band:

```php
'service_why' => [
  'eyebrow' => 'WHY US',
  'heading' => 'Why Authors Trust Us',
  'text'    => 'We help you build more than just a book—…',
  'chips'   => ['100% Confidential Process', 'Your Voice, Your Style',
                'Professional Writing Team', 'Publishing-Ready Quality',
                'Collaborative Approach'],
  'cta'     => ['label' => 'Learn Our Story', 'href' => 'about.php'],
],
```

### 2.3 `data/books.php`

Ten covers. Titles/authors identified in SPEC §E.1; every row is
`'placeholder' => true` per DECISIONS §6.

```php
return [
  ['img' => 'img/books/book-01', 'title' => "It's Not Easy Being a Bunny",
   'author' => 'Marilyn Sadler', 'genre' => 'childrens', 'placeholder' => true],
  // …10 total. 'genre' is one of: fiction, non-fiction, romance, christian, childrens
];
```

`genre` drives the Genres tab filter on the home page (SPEC §C.1 §8). Spread the
ten across the five genres so no tab is empty — at least one per genre.

### 2.4 `data/pricing.php`

Three tiers, SPEC §D.2 "Plans". Identical on home, pricing and all ten service
pages.

```php
return [
  'eyebrow' => 'PLANS',
  'heading' => "Publishing Packages For\nEvery Author",
  'tiers'   => [
    ['name' => 'Basic Package', 'price' => '700', 'period' => '/ month',
     'icon' => 'book-open', 'featured' => false, 'badge' => null,
     'features' => ['Professional Editing', 'Basic Book Formatting',
                    'Simple Cover Design', 'Publishing Assistance'],
     'cta' => 'Get Started', 'variant' => 'green-outline'],
    ['name' => 'Standard Package', 'price' => '1000', 'period' => '/ month',
     'icon' => 'sparkle', 'featured' => true, 'badge' => 'Most Popular',
     'features' => [/* 5 */], 'cta' => 'Get Started', 'variant' => 'green'],
    ['name' => 'Premium Package', 'price' => '1500', /* … */],
  ],
];
```

---

## 3. Shared components — exact signatures

Built by the Lead into `includes/components/`. Each is a `require` that reads
named variables set immediately before it. **Set every variable the component
documents, then require it.** Components reset their own inputs afterwards, so
two includes on one page do not leak state.

```php
<?php $sectionBg = 'band'; require __DIR__ . '/includes/components/journey.php'; ?>
```

| Partial | Inputs | Renders |
|---|---|---|
| `press-band.php` | — | Full-bleed black band, eyebrow + 7 logos (SPEC §B.12) |
| `services-carousel.php` | `$carouselHeading` (optional override) | Header + 8-card 4-up carousel (SPEC §C.1 §5) |
| `journey.php` | — | "Your Publishing Journey", 4 steps + dashed connectors |
| `author-stories.php` | — | Pale band, 2-col header, 3 video cards |
| `testimonials.php` | — | Full-bleed marquee, 4 review cards |
| `plans.php` | — | Centred header + 3-up pricing (SPEC §B.7) |
| `faq.php` | — | Centred header + 1084px accordion, item 1 open |
| `cta-wizard.php` | — | 2-col: CTA copy left, 4-step wizard right |
| `book-band.php` | `$bandVariant`, `$bandEager` | *(exists)* full-bleed cover strip |

Every one pulls its own content from `ep_data()`. **Devs pass no content.** If a
page needs different copy in a shared section, that is a spec question, not a
parameter — report it.

Sections 8–15 of a service page and 5, 9–15 of the home page are these
components. A dev writing markup that duplicates one of them is doing it wrong.

---

## 4. New helpers the Lead is adding to `includes/functions.php`

```php
ep_data('shared')                  // cached require of data/<name>.php
ep_srcset('img/x', [640,1280])     // multi-width <picture>, returns HTML
ep_lines("A\nB")                   // "A<br>B", escaped — for designed line breaks
```

New `ep_icon()` names available for Phase 3 (SPEC §E.2):
`lightbulb, paper-plane, sparkle, crown, badge-check, eye, play, minus,
chevron-left, chevron-right, dot, browser, hand-pen, quill, magnifier`

Need another glyph? Report it. Do not inline a random SVG (DEV-GUIDE §4).

---

## 5. Image filenames — Assets agent produces exactly these

Every entry is written as `.avif` + `.webp` + `.jpg` at each listed width.
Reference them **without extension or width** and let `ep_srcset()` assemble it.

| Path pattern | Widths | Count | Source (SPEC §E.1) |
|---|---|---|---|
| `img/books/book-01` … `book-10` | 420, 840 | 10 | `component-104`…`112` + `our-books__01` |
| `img/story-1` … `story-3` | 480, 960 | 3 | `homepage__05` y 0–380 |
| `img/avatar-1` … `avatar-4` | 96 | 4 | `homepage__05` y ~690–720 |
| `img/about-story` | 640, 1280 | 1 | `about-our-company__01` y ~755–1345 |
| `img/about-why` | 640, 1280 | 1 | `about-our-company__02`→`__03` |
| `img/svc/<slug>-hero` | 1280, 1920 | 10 | **not** y 0–866 — see note below |
| `img/svc/<slug>-intro` | 640, 1280 | 10 | each service `__02` y ~110–700 |
| `img/svc/<slug>-e2e` | 640, 1280 | 10 | each service `__03` y ~0–545 |

`<slug>` is exactly an `EP_SERVICES` key. 49 images × 3 formats × 2 widths.

> **Hero source changed in Phase 4 — DECISIONS §15.** The `y 0–866` band is the
> *composited design*: navbar, `<h1>`, buttons and the form card are flattened
> into it, so a hero built from it renders under a picture of itself. Heroes are
> now cut from the same source as `<slug>-e2e` (`x 1035..1669, y 2753..3346`,
> 635×594) as a 2.217:1 band. Filenames, widths, formats and counts are
> unchanged — no code is affected.

Also required, and **not** yet built: `img/favicon.svg`, `img/apple-touch-icon.png`
(both referenced by `head.php` and currently 404).

---

## 6. Rules that apply to everyone

1. **Copy is transcribed, never paraphrased** (DEV-GUIDE §6). Reproduce the
   design's copy bugs exactly — DECISIONS §11 lists all twelve.
2. **Designed line breaks are data**, stored as `\n` and rendered with
   `ep_lines()`. Do not hardcode `<br>` in a data file.
3. **Every `<img>` carries `width`, `height`, `alt`.** One eager
   `fetchpriority="high"` image per page, the LCP element, and nothing else.
4. **No hardcoded hex, size, radius or spacing.** Tokens only.
5. **No `data-bs-*`.** Bootstrap's JS is not loaded — grid and utilities only.
6. **Escape everything** with `esc()`.
7. Finish by loading your pages at `http://localhost/Elite%20Publishing/` with
   zero PHP notices and zero console errors, then write
   `docs/reports/<your-name>.md`.

---

## 7. Campaign landing pages (lp1–lp4) — added after Phase 4

### 7.1 File ownership

| File | Role |
|---|---|
| `lp1.php` … `lp4.php` | 27 lines each, **no markup**. Set `$lpKey` and page meta. |
| `includes/lp-page.php` | The whole layout. Renders all four. |
| `includes/lp-header.php` | Logo + one CTA. Not a variant of `header.php`. |
| `includes/lp-footer.php` | Logo, three links, copyright. Closes the document. |
| `data/landing.php` | All copy, for all four, plus the shared bits. |
| `assets/css/p-lp.css` | Landing-only styles. |
| `tools/assets-lp.php` | Builds `logo-ink.png` / `.webp`. |

To change copy, edit `data/landing.php`. To change layout, edit
`includes/lp-page.php` — and it changes on all four pages, which is the point.

### 7.2 `data/landing.php` shape

```php
return [
  'shared' => [
    'header_cta'       => string,
    'form'             => ['title' => string, 'submit' => string],
    'stats'            => [ ['value' => string, 'label' => string], … ],   // 4
    'services_actions' => [ ['label' => string, 'style' => string], … ],   // 2
    'footer_links'     => [ ['label' => string, 'page' => string], … ],    // 3
  ],
  '<key>' => [                        // children | christian | marketing | audiobook
    'slug'  => string,                // 'lp1'… — goes to the inbox as `campaign`
    'title' => string,                // <title>, without the brand suffix
    'meta'  => string,                // meta description
    'hero'  => ['h1' => string, 'paras' => string[]],
    'services' => [
      'heading' => string,
      'cards'   => [ ['icon' => string, 'title' => string, 'text' => string], … ], // 3
    ],
    'cta' => ['heading' => string, 'text' => string, 'primary' => string],
  ],
];
```

`'title'` on a card carries a drawn `\n` and is rendered with `ep_lines()`.
`'heading'` under `'cta'` does too. The hero `h1` does **not** — see
DECISIONS §16g.

`'page'` in `footer_links` is a key for `ep_page_url()`, not a URL.

### 7.3 Adding a fifth landing page

1. Add a key to `data/landing.php`.
2. Copy `lp4.php` to `lp5.php`; change `$lpKey`.
3. Add `'lp5'` to the loop in `sitemap.php`.

No CSS, no template and no routing change — `.htaccess`'s extensionless rewrite
already resolves `/lp5` to `lp5.php`.

### 7.4 Form contract

The hero form posts to the existing `forms/contact-handler.php` with
`full_name` / `email` / `phone` / `message`, plus `_form=lp-contact` and
`campaign=<slug>`. The handler needed **no changes**: any `_form` value that is
not `wizard` is treated as a contact form, which requires a message — and that
is exactly what this four-field card is.

`campaign` is carried so whoever reads the inbox can tell which ad produced the
lead. It is not validated against a list, and it is only ever echoed into a
plain-text mail body through `ep_field()`.

### 7.5 `$pageChrome`

`includes/head.php` takes `$pageChrome`: `'site'` (default) or `'lp'`. It selects
the header partial, and the landing pages require `lp-footer.php` at the end of
`lp-page.php` rather than `footer.php`. Everything above `<body>` — canonical,
OG tags, font preloads, the session `ep_csrf_field()` depends on — is shared.

---

## 8. `book-band` after the rebuild

The component no longer renders one `<picture>`. Its inputs are unchanged —
`$bandVariant` ('hero' | 'footer') and `$bandEager` — but it now emits:

```html
<div class="book-band book-band--hero" aria-hidden="true">
  <ul class="book-band__row list-plain">
    <li class="book-band__item" style="--tilt: -3.4deg; --drop: 0.09;"> <picture>…</picture> </li>
    …ten of them, from data/books.php in file order
  </ul>
</div>
```

Anything styling `.book-band img` directly will still apply, but code that
assumed a single image will not. The one place that did — `p-lp.css`, which
cropped the band with `aspect-ratio` — now sets `--band-h` instead.

Tuning knobs, all on `.book-band`:

| Custom property | Meaning |
|---|---|
| `--band-h` | drawn height of the strip; covers overflow past its bottom |
| `--cover-w` | one cover's width; `max()` with no cap, so it always fills |
| `--overlap` | fraction of `--cover-w` each cover overlaps its neighbours |
| `--tilt` | per-cover, set inline from `$bandTilt` |
| `--drop` | per-cover, in units of `--band-h`, set inline from `$bandDrop` |

`$bandTilt` and `$bandDrop` in the component are indexed by position and wrap,
so a catalogue longer or shorter than ten still renders.

## 9. `data-autoplay`

Put it on a `[data-scroller]` wrapper to make that rail advance itself; the
value is the interval in milliseconds. Only the services carousel carries it.
`initAutoplay()` in `assets/js/main.js` handles the rest, including stopping for
good on the first real user input.
