# Dev 1 — home page report

**Owns:** `index.php`, `assets/css/p-home.css` (+ `assets/js/p-home.js`, new, granted by the brief).
**Verified at:** `http://localhost/Elite%20Publishing/` — 0 PHP notices/warnings, 0 console
messages, no horizontal page scroll at 1920 / 1440 / 1024 / 390.

---

## 1. What was built

| SPEC §C.1 | Section | How |
|---|---|---|
| 2 | Hero | authored — centred, ~1220px block, mint wash, `<mark>` highlight on `Publisher!` |
| 3 | Book strip | `book-band.php`, `$bandVariant='hero'; $bandEager=true;` — the page's LCP and its only eager image |
| 4 | Press band | `press-band.php` |
| 5 | Our Services | `services-carousel.php` |
| 6 | Why Us | authored — `.panel-green` + three staggered `.tile-glass` |
| 7 | Publishing Process | authored — pale `band-rounded` → white card → 7-tab `role="tablist"` + 2×2 checklist |
| 8 | Genres | authored — 5 filter pills + 6-up rail, client-side filter |
| 9 | Portfolio | authored — `View All` + 6-up rail |
| 10 | Review platforms | authored — 4-up cards from `ep_data_get('shared','platforms')` |
| 11–15 | Stories / testimonials / plans / FAQ / CTA | the five partials, unmodified |
| 16 | Footer + strip | `footer.php` |

Copy is transcribed from SPEC §D.3 character-for-character. The h1 keeps
`Elite Publishing , The` (space before the comma, DECISIONS §11 bug 2) and is built with
`ep_lines()` over `\n`, not hardcoded `<br>`.

**Behaviour** (`assets/js/p-home.js`): process tabs with roving tabindex, Left/Right/Up/Down/
Home/End, automatic activation and wrap-around prev/next; after a prev/next press focus moves
to the same control in the panel that replaced it (the controls live inside the panel, as drawn,
so otherwise focus would be destroyed). Genre filtering is CSS keyed off `data-active` on the
track and gated on `.js`, so there is no wrong-books flash at first paint and no-JS visitors see
all ten covers; the JS only moves the flag and re-fires `resize` so `main.js` re-syncs the
scroller arrows. Rails use `.ep-scroller` + `[data-scroller]` — no carousel JS was written.

**A11y checked in the DOM:** one `<h1>`; h1 → h2 → h3 with no skipped level anywhere on the
page; all 27 images carry `width`, `height`, `alt`; no unlabelled buttons or links; tab row fully
keyboard-operable. No `data-bs-*`. No hardcoded hex, font size or radius except the one noted in
§3.6.

---

## 2. Things I could not match to the design

1. **Genres shows 4 covers, not 6.** The design draws the `Fiction` tab active *and* all ten
   covers — i.e. the export's tab row is not actually filtering. With a real filter and the genre
   spread in `data/books.php` (fiction 4, romance 2, childrens 2, non-fiction 1, christian 1),
   Fiction renders 4 covers and Christian renders 1. **PM decision needed:** either re-spread the
   ten books so no tab is thinner than ~6, or add an `All` tab (not in the design), or accept it.
2. **Process step title size.** SPEC §A.2 files "Consultation" under h5 (24/32), but in
   `homepage__03` the word measures ~36px. Built as `<h3>` at the h3 scale (40px @1920). If §A.2
   is authoritative I will drop it to `.h5`.
3. **Review-platform badges.** Only Trustpilot's mark is reproducible from the icon set (green
   tile + white star, as drawn). Google Reviews / Reviews.io / Sitejabber currently render as
   their initial letter in an ink tile — see §4.
4. **Inactive genre pills are white on `--ep-green`** (~2:1), which is what SPEC §B.13 draws but
   fails the WCAG AA rule in DEV-GUIDE §7. Built as drawn per DECISIONS §11; the same conflict
   already exists in the Lead's `.tile-glass p`. **Needs one ruling for both.**
5. **Carousel arrows read as disabled** when a track cannot scroll (`main.js` sets `disabled`,
   `.ep-scroller-nav`/`.rail-nav` fade to .4). Under the Fiction filter both arrows are faded,
   whereas the design shows solid green. Left as-is — it is honest feedback, not a fidelity call
   I wanted to make silently.
6. **Portfolio order** is rotated (books 5–10, then 1–4) because the design shows a different
   sequence there than in Genres. The catalogue is placeholder anyway (DECISIONS §6).

---

## 3. Requests — things that do not exist and I did not invent quietly

1. **A hero-wash hook.** `homepage__01` runs the mint wash behind a transparent navbar, but
   `.ep-header` is painted `--ep-bg` in `main.css`, which would cut a 96px flat strip across the
   top of the wash. I set `$bodyClass = 'page-home'` and, scoped to that class only, paint the
   wash on `body` and make the header transparent until `.is-stuck`. **Lead:** if other pages need
   this, it belongs in `main.css` as e.g. `.ep-header--over-hero`, not in three page stylesheets.
2. **Three brand marks** for §B.9: `brand-google`, `brand-reviewsio`, `brand-sitejabber`. (Also a
   rights question — see DECISIONS §13.)
3. **The Process h2's designed line break.** `data/shared.php` → `process.heading` has no `\n`,
   but the design breaks it `From Idea To Published / Book In 7 Simple Steps`. CONTRACT §6.2 says
   designed breaks are data, so the `\n` should go in the data file; until then I hold the h2 to
   `max-width: 660px`, which reproduces the break at desktop only. **PM:** please add the `\n`.
4. **`.tile-glass h4` in `main.css` only matches a literal `<h4>`.** The glass tiles sit under an
   `<h2>`, so the correct heading level is `<h3 class="h4">`, which the selector misses; I
   re-declared the margin in `p-home.css`. Suggest `.tile-glass :is(h3, h4, .h4)`.
5. **A very small radius token.** Trustpilot's five rating squares are near-square (~3px). There
   is no token below `--radius-input: 10px`, so that one value is raw with a comment. Proposal:
   `--radius-xs: 3px` (`main.css` already hardcodes `8px` for `.review-card__badge`, which could
   use it too).
6. **Book cover intrinsic size.** The brief specifies `ep_srcset(..., 840, 1160, ...)` and I used
   it, but the generated files are 840×1191 and SPEC §B.6 says 210×290 (= 840×1160). I set
   `aspect-ratio: 210/290` + `object-fit: cover`, so ~2.6% of each cover's height is cropped.
   Assets: either produce 840×1160 or tell me to switch the attributes to 1191.

---

## 4. `[VERIFY]` copy

None from my sections — every string in 2, 6, 8, 9, 10 is verbatim SPEC §D.3, and section 7's
step-1 copy is verbatim. Two things worth a client eye anyway:

- **`STEP 01`–`STEP 07`.** The design only ever shows `STEP 01`; the other six numbers are
  generated from the step index. Steps 02–07's body and checklists are the PM's drafted copy
  (`'draft' => true`, DECISIONS §4).
- **Hero rating stars are ink, not `--ep-star` orange.** That is what `homepage__01` shows; the
  orange stars only appear on review/testimonial cards (§B.9).

---

## 5. Bugs / notes in files I do not own

1. **`includes/header.php`** ships the logo with `fetchpriority="high"` + `decoding="sync"`. With
   the hero book-band also eager (correctly — it is the LCP), the page requests two high-priority
   images and they compete. Suggest dropping the logo to default priority, at least on pages that
   declare an LCP image.
2. **`.hp-field`** (`main.css`, used by `cta-wizard.php`) is parked at `left: -9999px`. It does
   not create a real horizontal scroll (`maxScrollX` is 0), but it inflates
   `documentElement.scrollWidth` to ~2670px at a 1905px viewport, which will look like an overflow
   bug to any automated check QA runs. `clip-path: inset(50%)` + `width: 1px` avoids that.
3. Not a bug, but worth knowing: `data/shared.php` → `platforms` rows carry no badge/logo key, so
   §B.9's platform mark has to be inferred from `style`/`name`. If the brand SVGs land, a
   `'badge' => 'google'` key would be cleaner than inferring.
