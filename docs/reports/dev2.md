# Dev 2 — service template + pricing

**Owns:** `service.php`, `pricing.php`, `assets/css/p-service.css`, `assets/css/p-pricing.css`.
**Covers:** 11 of the 17 pages — the ten `/services/<slug>` URLs and `/pricing`.
**Verified at:** `http://localhost/Elite%20Publishing/` — all eleven return 200 with
**0 PHP notices/warnings/fatals**, one `<h1>` each, every `<img>` carrying `width`/`height`/`alt`,
no `data-bs-*`, and every referenced asset resolving to a file on disk.

---

## 1. What was built

### `service.php` — one template, ten pages

Driven entirely by `data/services.php`, keyed by the `?s=` slug that `.htaccess` rewrites out of
`/services/<slug>`. Of the 15 sections in SPEC §C.8, only **2 (hero), 5 (intro) and 7 (end-to-end)**
carry per-service copy; sections 3–4 and 8–15 are the shared partials, included and not re-authored.

| SPEC §C.8 | What this file does |
|---|---|
| 1 Navbar (dark) | body class + CSS, see §2 |
| **2 Hero** | full-bleed photo + scrim, 55/45, h1 (`ep_lines()`) + paragraph + 2 buttons left, `Start Your Book Today` card right |
| 3 Book strip | `book-band.php`, `$bandVariant = 'hero'` |
| 4 Press band | `press-band.php` |
| **5 Intro** | image left / h2 + 2–4 paragraphs + 2 buttons right |
| **6 Why Us** | centred green panel, eyebrow + h2 + capped paragraph + 5 radio-dot chips over 2 rows + white button, from `ep_data_get('shared','service_why')` |
| **7 End-to-End** | copy left / image right, h2 + paragraph + optional `What We Offer:` + 2-column dotted list + 2 buttons |
| 8–14 | the seven shared partials, in order |
| 15 | `footer.php` |

**Routing and safety.** The slug must be a key of `EP_SERVICES` *and* have copy in
`data/services.php` before anything is emitted; anything else sends `404` and delegates to the
site's `404.php` (with a self-contained fallback listing the ten services if that file ever goes
away — it did not exist when I started). `$pageKey = 'service:' . $slug` so the nav dropdown marks
the right item; `$pageTitle`/`$pageDescription` come from the data's `title`/`meta_desc`;
`$ogImage` is the service's own hero at 1280.

**`e2e.label === null`** on `blog-article-writing` and `audio-book-production` renders the list
straight under the paragraph with no label and no `aria-labelledby` — verified on the page, not
just in the data.

**Copy** is rendered exactly as `data/services.php` supplies it. Book Cover Design's offer list is
Books Publishing's list, and Book Illustration's end-to-end paragraph is about websites
(DECISIONS §11 bugs 6 and 7). Nothing was "fixed".

### The hero contact form

Posts to `forms/contact-handler.php` with `ep_csrf_field()`, the same honeypot field name the
wizard uses (`website`), and the field names the handler documents: `full_name`, `email`, `phone`,
`message`. It sends `_form=hero-contact` — anything that is not `wizard` takes the handler's
contact path — plus a hidden `service` field carrying the service title so a submission is
attributable to the page it came from. **Dev 3: that extra field is currently ignored; one line in
the mail body / log line would make every service enquiry traceable.**

The page also **renders the handler's flash**, using the same shape `contact.php` does
(`$_SESSION['ep_form']`, consumed and 5-minute-stale-dropped): an `.ep-alert` inside the card with
the message and the error list, plus `value=""` repopulation and `aria-invalid="true"` on the
fields that failed. It carries `id="form-result"` so the wizard's `#form-result` redirect fragment
lands on the one alert on the page. Round-tripped end to end against the real handler — invalid
post → 303 back to `/services/ghostwriting?form=error` → alert and repopulated fields render.

All four controls have a `<label>` (visually hidden — the design shows placeholders only, SPEC §B.13).

### `pricing.php`

Navbar → page hero → `plans.php` → `faq.php` → `cta-wizard.php` → footer. The hero is the only
markup: h1 + paragraph over `.hero-wash`, copy verbatim from SPEC §D.6.

---

## 2. The dark navbar — what I did and what I would rather have

`includes/header.php` renders only the light variant and is Lead-owned, so the dark variant
(SPEC §B.1) is applied from `p-service.css` through the `page-service` body class:

- `.page-service > main { margin-top: calc(-1 * var(--svc-nav-h)) }` pulls the page up so the hero
  photo runs behind the bar; the hero adds the same value back as `padding-top`.
  `--svc-nav-h` is `96px`, dropping to `76px` under 1200px — it duplicates `.ep-header__inner`'s
  `min-height` from `main.css` and **will silently desynchronise if that number ever changes**.
- `.ep-header { background: transparent }`, and `background: var(--ep-black)` once `main.js` adds
  `.is-stuck`, so the bar keeps AA contrast after it leaves the photo. The design cannot specify
  this (DECISIONS §12) — a solid black bar is the reading that keeps the white logo/links/CTA
  legible without a mid-hero colour flip.
- The logo is reversed with `filter: brightness(0) invert(1)`.
- Nav links go white **only at ≥1200px** — below that the nav is a white drawer and its links must
  stay ink.
- `.ep-header__cta` is overridden to a white fill with a black label, hover included.

**What I would rather have:** a `$navVariant = 'dark'|'light'` input on `includes/header.php` that
(a) swaps `img/logo.png` for the `img/logo-light.png` that already exists in `assets/img/`, (b) puts
a `ep-header--dark` class on the element, and (c) exposes the header height as a token
(`--ep-nav-h`) instead of every page re-deriving it. Three pages already need the transparent-over-
hero behaviour (the ten service pages, pricing, and — from `contact.php` — the other mint heroes),
so this is not a one-page special case.

---

## 3. Bugs and gaps in files I do not own

### 3.1 ⚠ Blocking — the ten hero photos are flattened page exports, not photographs

`assets/img/svc/<slug>-hero-*` is the whole hero **frame**: the navbar, the headline, both buttons
and the white contact card are baked into the JPEG. Rendering the real markup on top produces a
visible double image on every one of the ten pages — the design's own text sitting behind mine,
offset because the export was laid out on a true 1420px content measure (see §3.2).

The Assets agent has already logged this (`docs/reports/assets.md` §4.1) and cannot recover the
photograph from a flattened raster. Recording it here because it is what stops these ten pages
looking finished: **the markup is correct and needs no change** — it needs the ten bare image fills
exported from Figma to the same filenames and widths. `-intro` and `-e2e` are clean photography and
render correctly.

### 3.2 `.container-ep` is 96px narrower than SPEC §A.6

`main.css` has `max-width: 1420px` **plus** `padding-inline: clamp(20px, 4vw, 48px)` under a global
`border-box`, so at 1920 the content measure is **1324px, not 1420px** (SPEC §A.6: 1420 wide,
250px margins; we render 290px margins). It affects every page and every shared component equally,
so nothing looks misaligned — but it is a systematic 7% squeeze, and it has one visible
consequence on my pages: the end-to-end h2 needs ~741px for `End-To-End Book Editing For` and gets
696px, so the designed line break gains an extra wrap (`…Editing` / `For` / `Your Manuscript`).
Fix is one line in `main.css` — `max-width: calc(var(--ep-container) + 2 * <padding>)`, or
`width: min(var(--ep-container), 100% - 2 * <gutter>)` with no padding — and I have deliberately
not compensated for it locally, because a local fix would break alignment with the shared sections.

### 3.3 Every page has a real horizontal scrollbar (off-canvas nav)

`.ep-nav` in `main.css` parks the mobile drawer at `translateX(100%)` with no clipping ancestor, so
`documentElement.scrollWidth` runs to ~2670px at a 1905px viewport. This is **not** only an
inflated number — `http://localhost/Elite%20Publishing/tools/_smoke.php` (no page CSS at all)
renders a draggable horizontal scrollbar at 1024px, 1200px and 1400px. `index.php` does too.

I added a scoped guard in my own stylesheets — `body.page-service`, `body.page-pricing
{ overflow-x: clip }` — and verified the scrollbar is gone on my eleven pages and still present on
`_smoke.php`. `clip` rather than `hidden` deliberately: `hidden` would make `body` a scroll
container and break the sticky header. **This belongs on `body` in `main.css` once**, at which
point my two rules become no-ops and should be deleted.

### 3.4 `.page-hero` is being defined twice, and the p-*.css files are concatenated

`contact.php` (Dev 3) uses `.page-hero`/`.page-hero__intro` styled in `p-core.css`; I needed the
same component for `/pricing`. DEV-GUIDE §1 says all `p-*.css` are concatenated into one bundle at
build time, so two unscoped `.page-hero` blocks **will** cross-contaminate in production even
though they never collide on `localhost`. I renamed mine to `.pricing-hero` to stay out of the way.
**Suggested:** one `.page-hero` in `main.css` (padding rhythm + `__intro` measure), used by
pricing, about, our-books, contact, privacy and terms — six pages currently duplicating it.

### 3.5 Two `fetchpriority="high"` images per page

`includes/header.php` gives the logo `loading="eager" fetchpriority="high"`, and the service hero
(the actual LCP element) needs the same. Two high-priority images compete for the first
connection; CONTRACT §6.3 asks for one. The logo is small and could drop to plain `eager` with no
`fetchpriority`.

### 3.6 `ep_return_url()` drops the query string

`forms/contact-handler.php` strips the query when it redirects back. That is right for the pretty
URLs (`/services/<slug>` carries none) but means a form posted from a bare
`service.php?s=ghostwriting` returns to `service.php` with no slug — which my validation correctly
turns into a 404. Only reachable by hand-typing the internal URL, so it is a note, not a defect;
a hidden `return` field validated against `EP_SERVICES` would close it if anyone cares.

---

## 4. Tokens and components I needed and did not find

1. **Scrim/overlay alphas.** `tokens.css` has no black-with-alpha token, and the hero needs four
   stops. They are declared locally in `p-service.css` as `color-mix(in srgb, var(--ep-black) N%,
   transparent)` with an `rgba()` line above each as the fallback, so no raw hex is hardcoded — but
   `--ep-scrim-strong` / `--ep-scrim-soft` in `tokens.css` would be the honest home for them.
2. **`--ep-nav-h`.** See §2 — three stylesheets now hardcode 96/76.
3. **`.page-hero`.** See §3.4.
4. No new icons were needed; `arrow-up-right` covers every button on these pages.

---

## 5. Decisions worth knowing about

- **Vertical rhythm on sections 5/6/7 is not `.section`.** The design puts ~96–100px *in total*
  between the intro, Why Us and end-to-end blocks, not 100px from each neighbour. Each of the three
  owns only its top gap (`padding-block: clamp(...) 0`) and the end-to-end block hands its bottom
  gap to the shared services carousel, whose own `.section` padding supplies it. Measured against
  the export: press→intro 96px (design 96), intro→panel 96 (96), panel→e2e 100 (100),
  e2e→carousel 104 (112).
- **The five Why-Us chips are capped to the paragraph's 762px measure.** That cap is what makes
  them break 3 + 2 as drawn; at the full container width all five fit on one line.
- **The offer list is `grid-auto-flow: column` over three rows with auto-width tracks packed
  left**, so short labels pair tightly like the export and Audio Book Production's much longer
  labels still fit. It collapses to one column under 576px.
- **The three photographs carry `alt=""`.** They are decorative — the hero is a background and the
  intro/e2e photos add nothing the adjacent h2 does not already say. Inventing descriptive alt text
  would have meant inventing copy.
- **Pricing hero copy is inlined in `pricing.php`**, transcribed from SPEC §D.6, because no data
  file owns per-page hero copy. Same pattern `contact.php` uses. If page heroes ever move into
  `data/shared.php` this is one of the strings to move.

---

## 6. Known deviation: white on `--ep-green` fails AA

The Why Us panel is white text on `#60C489` — 2.1:1, which fails AA even at display sizes, and
DEV-GUIDE §7 explicitly says to use `--ep-ink` for body copy on green. I built it **as designed and
as `.panel-green` already implements it in `main.css`** (the Lead's own component sets
`color: var(--ep-white)`), because switching only my page would leave the site inconsistent with
the home page's green panel and with the export. This is a site-wide call, not a service-page one:
either `.panel-green` moves its body copy and chips to `--ep-ink`, or the deviation is logged for
the client. Flagging rather than silently diverging.

The navbar over the hero photo *does* meet AA: the top scrim holds
`color-mix(in srgb, var(--ep-black) 55%, transparent)` at full strength for the whole `--svc-nav-h`
band before releasing, which keeps white ≥4.7:1 even against the brightest of the ten photographs
(`proofreading`, which is near-white at the top left).

---

## 7. Verification

| Check | Result |
|---|---|
| 10 × `/services/<slug>` + `/pricing` | 200, 0 PHP notices/warnings/fatals |
| `/services/<anything-else>` | 404 + the site's 404 page |
| `<h1>` per page | exactly 1 |
| `<img>` without `alt` / `width` | 0 / 0 |
| `data-bs-*` | 0 |
| Referenced assets that 404 | 0 (61 per service page, 19 on pricing) |
| Hero form round trip | CSRF ok, validation errors flash + repopulate, 303 back to the pretty URL |
| `e2e.label === null` pages | no `What We Offer:` rendered on `blog-article-writing`, `audio-book-production` |
| Horizontal scroll on my pages | none (see §3.3) |
| Layout checked at | 1920, 1440, 1024, 768, ~500 |

Geometry against the export at 1920 (viewport 1905): hero 864px tall (design 866), h1 box top 288
(design ~290), hero buttons top 664 (675), inputs 48px, textarea 128px (129), Why-Us paragraph
762px (762), chips 3 + 2, offer list two columns of three. Horizontal positions are all inset by
the 48px in §3.2.

**Note on 390px.** Chrome on Windows will not open a window narrower than ~500px, and the shared
MCP browser is being driven by several agents at once, so the narrowest layout I could capture
reliably is ~500px — which exercises the same `<576px` rules (full-width buttons, single-column
offer list, stacked hero). A true 390px pass wants device emulation; nothing in the CSS between
500px and 390px changes behaviour, but I am not claiming it as verified.
