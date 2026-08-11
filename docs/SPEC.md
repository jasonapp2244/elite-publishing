# Elite Publishing — Build Specification

**Single source of truth for the build.** Everything here was read off the Figma PNG exports in
`_figma-ref/` (1920px artboards, sliced to 1280px-wide strips → **scale factor 1.5** from slice px to
artboard px). Colours were sampled programmatically from the JPEGs (flat fills are exact; text
colours are inferred from core glyph pixels and are marked where uncertain).

**How to read this doc**
- All px values are **artboard px at 1920 width** unless stated.
- `[UNREADABLE]` = genuinely not legible in the export. Do not invent replacements.
- `[NOT IN EXPORT]` = the state exists in the design but is not rendered in a static export
  (e.g. accordion items 2–6 closed, carousel slides 2+).
- ⚠️ = a discrepancy, copy bug, or open question the Lead/PM must resolve.

---

## 0. Reconciliation with `assets/css/tokens.css`

`tokens.css` already exists. My independent measurements agree with it almost everywhere. These are
the deltas — **the Lead has Figma access and should adjudicate**; where Figma disagrees with me,
Figma wins.

| Token | tokens.css | Measured here | Note |
|---|---|---|---|
| `--ep-green` | `#60C489` | `#60C488` | Sampled as a 100%-flat fill over a 120×20 region. Effectively identical; keep `#60C489`. |
| `--ep-bg` | `#F9FAF9` | `#FAFAFA` | Measured **100% flat** `#FAFAFA` over a 60×60 region on three separate pages. Recommend changing to `#FAFAFA`. |
| `--ep-green-tint` | `#EBF5EF` | Footer `#EAF5EF`, pale section band `#E8F3ED` | ⚠️ There are **two** distinct tints, not one. See §A.1. |
| `--fs-h1` / `--lh-h1` | `80 / 90` | **`76 / 88`** | ⚠️ Cap-height of hero "T" = 35 slice px = 52.5 artboard px. With the cap ratio validated against the Figma-confirmed h2 (0.70 em), that gives 75px, and the measured baseline pitch is 88.5px. 80/90 would need a 56px cap. **Please re-check in Figma.** |
| `--fs-h2` / `--lh-h2` | `60 / 70` | `60 / 70` | ✅ Confirmed — my method reproduces the Figma-confirmed value exactly, which validates the rest of the scale. |
| `--fs-h3` | `40 / 48` | `40` | ✅ |
| `--fs-h4` | `24 / 32` | **two sizes: `28` and `24`** | Service/book card titles measure 28px; pricing package names and journey step titles measure 24px. Suggest adding `--fs-h4: 28px` and `--fs-h5: 24px`. |
| `--lh-lg` | `30` | **`27`** | Hero paragraph baseline pitch measured 18 slice px = 27px. |
| `--lh-base` | `26` | **`24`** | Card description baseline pitch measured 16 slice px = 24px. |
| `--ep-gutter` | `24px` | `24px` for 3-up, **`~18–20px` for 4-up** | 3-up: 3×459 + 2×23 = 1423 ✓. 4-up: 4×342 + 3×18 = 1422 ✓. See §A.6. |
| `--ep-ink` | `#2B2A28` | `#2B2A28` | ✅ Confirmed independently — the "Most Popular" pill is a flat fill that samples to `#2B2A28`/`#2B2B2B`. |
| `--ep-container` | `1420px` | `1420px` | ✅ Content spans artboard x 250 → 1670. |
| — | (absent) | **navbar container ≈ 1700px** | ⚠️ The navbar is wider than the content container. See §A.6. |
| `--ep-peach` | `#FDF4EF` | not found | I found no peach/warm block in any of the 17 pages. Possibly unused. |

---

# A. Design tokens

## A.1 Colour

### Brand green
| Token | Hex | Where |
|---|---|---|
| `green` | **`#60C488`** | "Why Us" panel fill, filled buttons, FAQ open-item fill, pricing card borders + Standard CTA, journey step icon tiles, carousel arrow buttons, checkmark icons, genre tab track, hero highlight behind "Publisher!", service-card hover fill, footer top rule |
| `green-on-green` | **`#68C78F`** | Inner "glass" cards inside the Why Us panel. This is `#60C488` + `rgba(255,255,255,0.12)` — implement as the overlay, not as a flat colour |
| `green-tint-footer` | **`#EAF5EF`** | Footer band background (all 17 pages) |
| `green-tint-band` | **`#E8F3ED`** | Full-bleed rounded section bands: "Our Publishing Process", "Author Stories", "Why Authors Choose Us" (About page) |
| `green-tint-soft` | **`#EFFAF4`** | Inner panel inside the Process card; unselected option chips in the 4-step wizard |
| `green-tint-hairline` | **`#DFF3E7`** *(derived)* | 1px borders on the pale pricing cards and outline buttons |
| `green-hover` | **`#4FB177`** *(derived — not sampled)* | ⚠️ No hover state of a green button appears in the exports. Derived as green darkened ~10%. |

The hero highlight behind **"Publisher!"** is a solid `#60C488` rectangle (not a rounded pill), with
the black word sitting on top.

### Neutrals
| Token | Hex | Where |
|---|---|---|
| `black` | **`#000000`** | Press-logo band background (sampled 100% flat over 200×10), primary filled buttons, nav "Publish Your Book", "Continue", "Get My Free Consultation", process step-tab active pill, prev/next buttons in the Process card |
| `ink` | **`#2B2A28`** | Headings, nav links, card titles, "Most Popular" pill fill. *(JPEG ringing makes large heading strokes sample as low as `#000000`; `#2B2A28` is the trustworthy value, taken from the flat "Most Popular" pill.)* |
| `body` | **`#3D3D3D`** *(measured floor `#3F3F3F`)* | Long-form body paragraphs (policy pages, intro sections) |
| `muted` | **`#5F5E5C`** *(measured floor `#474747`)* | Card descriptions, journey step descriptions, testimonial roles, "Based on N reviews" |
| `white` | **`#FFFFFF`** | Cards, pricing cards, FAQ closed items, hero form card, process card |
| `page-bg` | **`#FAFAFA`** | Page background on every page |
| `surface-input` | **`#FBFBFB`** | Form input fill |
| `icon-tile` | **`#F2F2F2`** | 68px rounded square behind service-card icons |
| `border` | **`#E6E8E7`** *(derived; too thin to sample cleanly)* | Neutral hairlines, input borders, process step-tab outlines |

### Accents (not brand — third-party / decorative)
| Token | Hex | Where |
|---|---|---|
| `star-orange` | **`#F5620A`** (sampled `#F86300`/`#F05C00`) | 5-star rating glyphs on review + testimonial cards |
| `trustpilot-green` | `#00B67A` *(brand colour, assumed)* | Trustpilot badge tile and its 5-square star bar |
| `carousel-red` | **`#C41230`** | ⚠️ **Our Books page only** — the book-carousel prev/next arrows are crimson there, while the identical control is `#60C488` everywhere else. Flag to design: almost certainly an oversight, but build it as drawn unless told otherwise. |

### Hero background (all non-service pages)
A soft mint radial/linear wash, not a flat colour. Sampled: `#D8EEE2` at the top-centre → `#F2F7F3`
at the top corners → `#FAFAFA` by y≈500. Implement as a large soft radial gradient centred near the
top of the viewport.

## A.2 Type scale

**Family:** one geometric sans is used for *both* headings and body — double-storey `a`, circular
bowls, horizontal `e` bar, straight-tailed `y`, angle-cut `t`. This is **not** Poppins (Poppins has a
single-storey `a`). The Lead has already self-hosted **Urbanist** as the substitute; that is a
reasonable match. ⚠️ **Open item:** confirm the real family name from the Figma properties panel
before the visual QA pass — if it's Gilroy / Sofia Pro / Circular, note the substitution in the QA
report rather than chasing pixel parity on glyph widths.

All values are artboard px @1920. Weights are read from stroke density and are ±100.

| Role | Size / line-height | Weight | Tracking | Where |
|---|---|---|---|---|
| **h1 / display** | `76 / 88` ⚠️ (tokens says 80/90) | 600 | -2.5% | Hero headlines, page titles ("About Our Company", "Privacy Policy") |
| **h2** | `60 / 70` ✅ | 600 | -2.5% | Section headlines ("Featured Self-Publishing Solutions", "Frequently Asked Questions.", "Let's Bring Your Book To Life") |
| **h3** | `40 / 48` | 600 | -2.5% | "Start Your Book Today" (hero form card), "Our Published Books Collection", "What Genre Is Your Book?" |
| **h4** | `28 / 34` | 500 | 0 | Service card titles, book card titles, policy-page section headings, FAQ questions (28/–), Why-Us glass card titles |
| **h5** | `24 / 32` | 500 | 0 | Pricing package names, journey step titles, "Consultation" step title, Core Values card titles |
| **body-lg** | `20 / 27` | 400 | 0 | Hero sub-paragraphs, section intro paragraphs, FAQ question text, footer links |
| **body** | `16 / 24` | 400 | 0 | Card descriptions, pricing feature rows, journey step descriptions, policy body copy, form labels |
| **small** | `14 / 20` | 400 | 0 | Testimonial role line, "Based on 1,247 reviews", book card author line, copyright line |
| **eyebrow** | `16 / 20` | 600 | **+0.09em (≈1.5px)**, `text-transform: uppercase` | "OUR SERVICES", "WHY US", "PLANS", "FAQ", "PORTFOLIO", "GENRES", "AUTHOR STORIES", "OUR PUBLISHING PROCESS", "STEP 1 OF 4", "STEP 01", "RECOGNIZED BY AUTHORS ACROSS THE GLOBE" |
| **nav link** | `18 / 24` | 500 | 0 | Header nav |
| **price figure** | `62 / 62` | 600 | -2.5% | `700` / `1000` / `1500` |
| **price currency** | `24` | 500 | 0 | The `$`, baseline-aligned to the *top* of the figure |
| **price period** | `20` | 400 | 0 | `/ month`, muted, baseline-aligned to the *bottom* |
| **footer brand** | `72 / 80` | 500 | -2.5% | `Contact@Elitepublishing.Co` |

**Measurement method (for anyone re-checking):** cap-height in slice px × 1.5 ÷ 0.70. This
reproduces the Figma-confirmed h2 = 60px exactly, so the ratio is sound.

## A.3 Radii

| Token | Value | Applies to |
|---|---|---|
| `radius-btn` | **12px** | All buttons (measured: nav "Publish Your Book" corner arc ≈ 10 slice px). Buttons are **rounded rectangles, not pills** — a 56px-tall button with a 12px radius. |
| `radius-card` | **16px** | Service cards, review/trust cards, testimonial cards, book cards, Core Values cards, hero form card, pricing cards |
| `radius-panel` | **12–16px** | Why Us green panel (measured ~11px — noticeably *less* rounded than the cards), pale full-bleed section bands (~16px) |
| `radius-input` | **10px** | Form inputs, wizard option chips |
| `radius-icon-tile` | **12px** | 68px icon tiles on service cards; 48px journey step tiles |
| `radius-pill` | **999px** | Process step tabs ("Consultation", "Planning"…), genre tabs, "Most Popular" badge, social icon circles, numbered circles on the About page |

## A.4 Shadows

Shadows in the design are very soft and low-contrast — barely visible against `#FAFAFA`.

```
shadow-card:        0 1px 2px rgba(43,42,40,.04), 0 8px 24px rgba(43,42,40,.06)
shadow-card-hover:  0 2px 4px rgba(43,42,40,.06), 0 16px 40px rgba(43,42,40,.10)
shadow-float:       0 8px 32px rgba(43,42,40,.08)   /* hero form card, process card */
```
The tokens.css values are already correct. The **Standard pricing card** additionally has a visible
outer halo — a second `#60C488` ring offset ~4px outside the 2px border (see §B.10).

## A.5 Vertical rhythm

Measured full-page background-band map (artboard px). These are exact section boundaries.

**Homepage (9448px):**
| From | To | Height | Band |
|---|---|---|---|
| 0 | 864 | 864 | Hero (mint gradient, transparent navbar over it) |
| 864 | 1112 | 248 | Book-cover strip (full-bleed) |
| 1112 | 1407 | **296** | Press-logo band (`#000000`) |
| 1407 | 2823 | 1416 | Our Services + Why Us (page bg) |
| 2823 | 3495 | 672 | Our Publishing Process (pale band `#E8F3ED`) |
| 3495 | 5326 | 1831 | Genres + Portfolio + Review strip (page bg) |
| 5326 | 5970 | 644 | Author Stories (pale band `#E8F3ED`) |
| 5970 | 8661 | 2691 | Testimonial marquee + Plans + FAQ + CTA (page bg) |
| 8661 | 8672 | ~3px rule | Footer top rule (`#60C488`) |
| 8672 | 9164 | 492 | Footer (`#EAF5EF`) |
| 9164 | 9448 | 284 | Book-cover strip under the footer |

**Service pages (8776px, identical for all 10):** hero 0→866 · book strip 866→1112 · press band
1112→1407 (296px, identical) · content 1407→4656 · Author Stories band 4656→5326 · content
5326→7990 · green rule → footer 8001→8492 · book strip to 8776.

**Derived spacing rules:**
- **Standard section gap: 100px** (measured 96–110px between adjacent sections).
- Section padding *inside* a full-bleed pale band: **~70px top**, ~0–20px bottom (content nearly
  touches the lower edge).
- Gap between a section's intro block and its card grid: **~72px**.
- Gap between an h2 and the paragraph under it: **~24px**; eyebrow → h2: **~16px**.

## A.6 Grid & containers

| Container | Width | Side margin @1920 |
|---|---|---|
| **Content container** | **1420px** | 250px |
| **Navbar container** | **≈1700px** ⚠️ | 108px left / 110px right |
| Full-bleed | 1920px | 0 — book strips, press band, pale section bands (bands are inset by the content margin and rounded), testimonial marquee |

**Measured grids (artboard px):**
| Grid | Card width | Gutter | Formula |
|---|---|---|---|
| 4-up (service cards, review/trust strip) | **342** | **18** | 4×342 + 3×18 = 1422 ✓ |
| 3-up (pricing, Core Values) | **459** | **23** | 3×459 + 2×23 = 1423 ✓ |
| Testimonial marquee (full-bleed) | **447** | **22** | overflows both edges |
| Video cards (Author Stories) | ~460 | 24 | 3-up inside the content container |

⚠️ The 4-up gutter is genuinely tighter than the 3-up gutter. Bootstrap's `g-4` (24px) is right for
the 3-up rows; the 4-up rows need a custom ~20px gap. Card internal padding is **~28px**.

**Footer link columns** start at artboard x = 250, 525, 828, 1140, 1440 — i.e. five left-aligned
columns of unequal width, not a 5×equal grid. Match the x positions.

---

# B. Component inventory

## B.1 Navbar
Two variants exist (`frame-2147239914`):
1. **Light** — transparent over the mint hero. Logo in colour (navy serif `ELITE` + green
   `PUBLISHING` + green stacked-books mark). Nav links `#2B2A28`. CTA = black filled button.
2. **Dark** — over a photographic hero (all 10 service pages). Logo reversed to white. Nav links
   white. CTA = **white filled** button with black label.

- Height ≈ **106px**; logo block spans y 30→127 (so it overhangs the nav's own height slightly).
- Links: `Home · Services ▾ · Our Books · Company · Pricing · Contact` — 18px/500, ~44px apart.
- `Services` has a small solid triangle caret to its right.
- CTA button: **56px tall**, label `Publish Your Book` + ↗, right edge at artboard x 1810.
- Not sticky in any export; ⚠️ sticky-on-scroll behaviour is **not specified by the design** — treat
  as a product decision, not a spec item.

### Services dropdown (`frame-2147239963`)
White rounded panel (radius ~16px), soft shadow, **two columns × five rows**, bulleted (`•`) list,
items ~28px, `#2B2A28`. Order, read **row by row**:

| Left column | Right column |
|---|---|
| Books Publishing | Book Illustration |
| Book Editing | Audio Book Production |
| Book Cover Design | Book Marketing |
| Blog Article Writing | Creative Content Writing |
| Ghostwriting | Proofreading |

## B.2 Footer
- Top edge: **3px `#60C488`** full-bleed rule. Band fill `#EAF5EF`, height **492px**.
- Row 1: green stacked-books logo mark + `Contact@Elitepublishing.Co` at 72px/500 (title-case as
  written). Right-aligned: three **40px circles**, fill `#E4EAE6`-ish grey, dark glyphs — Facebook
  `f`, LinkedIn `in`, Instagram camera.
- Row 2: five link columns (copy in §F.2), 20px/400, ~26px row spacing.
- Hairline divider, then Row 3: left `© Copyright 2026 All Rights Reserved.` · right
  `Terms & Conditions` `|` `Privacy Policy`.
- Immediately below the footer band, the **book-cover strip repeats** (full-bleed, ~284px tall).
- ⚠️ `footer__01.jpg` shows the footer on a saturated green background. That is the Figma page
  canvas showing through a transparent component frame — **every page render uses `#EAF5EF`**. Build
  the mint version.

## B.3 Buttons
All buttons: **56px tall**, radius **12px**, horizontal padding ~28px, label 18px/500, and a
**↗ north-east arrow glyph** ~14px, separated from the label by a **~14px gap**. The arrow is a
stroked (not filled) arrow with a ~2px stroke — hand-author it as SVG so it inherits `currentColor`.

| Variant | Fill | Border | Label | Component | Used for |
|---|---|---|---|---|---|
| **Primary (black)** | `#000000` | none | white | `component-126` | `Publish Your Book`, `Submit Manuscript`, `Watch More Stories`, `View All`, `Send Message`, `Continue`, `Get My Free Consultation` |
| **Primary (white)** | `#FFFFFF` | none | `#2B2A28` | `component-128` | Nav CTA on dark heroes, `Learn Our Story` on the green panel, `I want to Publish my Book! Start Now!` |
| **Primary (green)** | `#60C488` | none | white | `component-127`, `141` | `Get Started` (intro/end-to-end sections), Standard-plan `Get Started` |
| **Secondary (dark outline)** | transparent/white | 1px `#2B2A28` | `#2B2A28` | — | `Explore Our Services`, `Free Consultation` |
| **Secondary (green outline)** | transparent | 1px `#60C488` | `#60C488` | `component-129`, `142` | `Book a Free Consultation`, Basic/Premium `Get Started` |
| **Secondary (white outline)** | transparent | 1px `#FFFFFF` | white | `component-130` | `Free Consultation` on dark/green backgrounds, `Schedule a Call` |
| **Ghost / text link** | none | none | `#60C488` | — | `Learn More ↗`, `View on Trustpilot ↗` — 16px, arrow inline |

⚠️ Each button component exports as **two visually identical instances** (default + hover). No
hover delta is discernible in the raster. Specify hover as: primary → 6% darken; outline → fill with
its own border colour, label flips to white; all → `transform: translateY(-1px)` + `shadow-card`.

## B.4 Eyebrow label
`◉ ALL-CAPS TEXT`. The glyph is a **thick black ring** — a solid octagonal/circular donut with a
white hole in the centre, ~14px square, `#2B2A28` (white on green backgrounds). Gap to text ~15px.
Text: 16px / 600 / uppercase / +0.09em. Total width of "◉ OUR SERVICES" = 180px.
Colour flips to white inside the green Why-Us panel.

## B.5 Service card
- 342 × ~300px, white, radius 16px, padding ~28px, `shadow-card`.
- **68px** rounded-square icon tile, fill `#F2F2F2`, radius 12px, containing a **~28px solid black
  glyph**.
- Title 28px/500, `#2B2A28`, **forced to two lines** ("Books / Publishing", "Book / Editing") — the
  design breaks these deliberately; reproduce the line break.
- Description 16px/24, `muted`, **clamped to 3 lines with a trailing `…`** — the truncation is in the
  design. Supply the full copy in the markup and clamp with CSS (`-webkit-line-clamp: 3`).
- `Learn More ↗` ghost link, `#60C488`.
- **Hover (`component-159` slides 2–3): the whole card fills `#60C488`**, the icon tile becomes
  white/translucent with a white glyph, title + description + link all turn white.

## B.6 Book card
- ~210 × 290px cover image, radius 12px, `object-fit: cover`.
- Two layouts:
  - **Homepage "Recently Published Titles" / Genres carousel** — caption *below* the cover:
    author 14px muted (`Elena Hartwell`), title 16px/500 ink (`The Last Cartographer`).
  - **Our Books page** — caption *overlaid* on the bottom of the cover on a dark scrim, white text,
    same two lines.
- ⚠️ Every single book card in the design uses the placeholder pair `Elena Hartwell` /
  `The Last Cartographer` while showing a different real cover image. Real per-book metadata is
  **not in the design** — the build needs it from the client, or `data/books.php` placeholders.

## B.7 Pricing card
- 459 × ~620px, white, radius 16px.
- **Basic / Premium:** 1px `#DFF3E7` border.
- **Standard:** 2px `#60C488` border **plus** an offset outer ring ~4px beyond it (a double-stroke
  halo), and it is ~30px taller than its siblings (it extends above and below their bounds).
- Top row: a **56px circle**. Basic/Premium: white fill, 1px green border, green glyph (open book /
  crown). Standard: solid `#60C488` fill, white glyph (4-point sparkle). Standard also carries a
  right-aligned **`Most Popular`** pill — fill `#2B2A28`, white 16px label, radius 999px.
- Package name 24px/500 · price block (see §A.2) · feature rows: 20px green filled circle with a
  white check + 16px label, ~27px apart.
- CTA: full-width `Get Started ↗`. Basic/Premium = green outline; Standard = green filled.
- **Hover (`component-140` slide 3): the Standard card fills solid `#60C488`** — name/price/features
  all white, and the sparkle circle inverts to solid black with a white glyph.

## B.8 Testimonial (two distinct components)

**a) Video story card** (Author Stories band) — ~460 × 260px, radius 16px, photo fill, centred
translucent white **play button** (~48px circle, dark triangle). Bottom-left overlay: ~32px circular
avatar + `Clara Wen` (16px/600 white) + `Everything Remembered` (14px white 80%).
⚠️ All three cards use the same placeholder name/title.

**b) Written review card** (full-bleed marquee) — 447 × ~215px, white, radius 16px, padding 24px.
Top row: five `#F5620A` stars (left) + a **28px platform badge** (right). Body: quote in curly
double quotes, 16px/24, ink. Footer: 32px circular avatar + name (16px/600, with a trailing comma —
`Marcus Vance,`) over role (14px muted).
Badges cycle: green Trustpilot star · Google `G` · black circular mark · green Trustpilot star.
⚠️ Only **two** unique quotes and **three** unique attributions exist; cards 3 and 4 repeat 1 and 2.

## B.9 Stats / review-platform strip
4-up grid of white cards, 342px wide, radius 16px, padding 24px.
Row 1: 28px rounded platform badge + platform name (20px/500).
Row 2: score as `4.9` at 40px/600 + `/5` at 20px muted, baseline-aligned.
Row 3: `Based on N reviews`, 14px muted.
Row 4: rating graphic — Trustpilot uses five **filled green squares** with white stars; the other
three use five `#F5620A` star glyphs.
Row 5: `View on <Platform> ↗` ghost link, `#60C488`.

## B.10 FAQ accordion
- Width **1084px**, centred (narrower than the 1420px container).
- **Closed item:** white, radius 12px, height ~62px, padding 24px, question at 20px/500 ink, a `+`
  glyph (2px stroke, ~20px) on the right. Items are separated by a **13px gap** (they are discrete
  cards, not a bordered list).
- **Open item:** fill `#60C488`, question white, icon becomes `−`, answer revealed underneath at
  16px/24 in white ~90%. Item grows to ~90px.
- Exactly one item open at a time; item 1 is open by default on every page.

## B.11 CTA band
Two forms:
1. **Homepage / service / policy pages** — a 2-column block on the page background, not a coloured
   band: left = h2 + paragraph + contact lines + two buttons; right = the 4-step wizard card (§B.13).
2. **Contact page only** — a true full-width green band, `#60C488`, radius 16px, centred white
   content: h2 `Your Story Deserves To Be Published.`, paragraph, `Publish Your Book` (white filled)
   + `Free Consultation` (white outline).

## B.12 Press-logo strip
Full-bleed `#000000` band, **296px** tall. Centred eyebrow `RECOGNIZED BY AUTHORS ACROSS THE GLOBE`
in white, then a single row of **7** greyscale logos at ~55% opacity white, evenly distributed
across the 1420px container. Identical on every page that has it.

## B.13 Form controls

**Inputs** — height 48px, fill `#FBFBFB`, 1px `#E6E8E7` border, radius 10px, 16px placeholder in
`muted`, padding-x 16px. Textarea ~110px. No visible labels anywhere in the design — **placeholders
only**. (Build with visually-hidden `<label>`s for a11y.)

**Hero contact form** (service pages) — white card, radius 16px, padding 32px, `shadow-float`.
Title `Start Your Book Today` (h3). Fields stacked full-width: `Full Name`, `Email Address`,
`Phone Number`, `Write your message here...`. Submit: black `Send Message ↗`, auto width, left-aligned.

**4-step wizard** (`component-95`) — white card, radius 16px, ~500px wide.
- Progress: 4 equal segments, 4px tall, radius 2px, gap 8px; completed = `#60C488`, pending = `#E6E8E7`.
- `STEP n OF 4` eyebrow in `#60C488`, then an h3 question.
- **Option chips:** full-width (or 2-up), height 42px, radius 10px, fill `#EFFAF4`, label 16px in a
  muted green; leading emoji. **Selected** = white fill + 1px `#60C488` border + green label.
- Footer: steps 2–4 have a **48px square "back" button** (white, 1px border, radius 10px, green ←)
  to the left of the primary button. Step 1 has none.
- Primary is full-width black: `Continue ↗` on steps 1–3, `Get My Free Consultation ↗` on step 4.

**Process step tabs** — 999px-radius pills, ~40px tall, 1px `#E6E8E7` border on white; the active
tab is solid `#000000` with a white label. Connected by 1px `#E6E8E7` hairlines.

**Genre tabs** — a `#60C488` pill *track* containing five pills; the active one is white with ink
text, the inactive ones are transparent with white text.

## B.14 Carousel controls
Two styles, both 40px squares with a 12px radius:
- **Green** (`#60C488`, white arrow) — Our Services, Genres, Portfolio.
- **Black** (`#000000`, white arrow) — Our Publishing Process (inside the white card).
- ⚠️ **Red `#C41230`** — Our Books page only.
Placement: top-right of the section header for Our Services; **vertically centred, overhanging the
left and right edges** of the track for Genres / Portfolio / Our Books.

## B.15 Book-cover strip
Full-bleed band of **9 real book covers**, each rotated a few degrees (alternating direction),
overlapping, cropped by the band, radius ~12px. Appears **twice per page**: directly under the hero
and directly under the footer. Height 248px (hero) / 284px (footer).

---

# C. Per-page section breakdown

## C.1 Home — `index.php` — `/` (9448px)

| # | Section | Layout | Behaviour |
|---|---|---|---|
| 1 | Navbar (light) | Logo left · links centred · CTA right, ~1700px container | Services dropdown on hover/click |
| 2 | Hero | Centred, max ~1000px: h1 → paragraph → 2 buttons → rating line | Mint radial gradient bg |
| 3 | Book strip | Full-bleed, 9 tilted covers | Static (or slow marquee) |
| 4 | Press band | Full-bleed black; eyebrow + 7 logos in one row | — |
| 5 | Our Services | Header block left (eyebrow/h2/paragraph) + arrows right; **4-up card track** | Horizontal carousel, 8 cards, 5th peeks |
| 6 | Why Us | Green panel, 2-col 45/55: text+buttons left, 3 offset "glass" cards right | Cards are staggered horizontally (2nd indented right) |
| 7 | Our Publishing Process | Pale band → white card: 7-tab pill row on top, then 2-col 45/55 (step copy left, 2×2 checklist right) | Tab carousel; black prev/next |
| 8 | Genres | Header left + 5 tab pills right; **6-up cover track** | Tab filter + carousel, arrows overhang |
| 9 | Portfolio | Header left + `View All` right; **6-up cover track** | Carousel, arrows overhang |
| 10 | Review platforms | 4-up card grid | Static |
| 11 | Author Stories | Pale band; 2-col header (h2 left, paragraph+button right); **3-up video grid** | Video modal |
| 12 | Testimonial marquee | Full-bleed 4-up row, cards overflow both edges | Infinite marquee |
| 13 | Plans | Centred header; 3-up pricing | Middle card raised |
| 14 | FAQ | Centred header; 1084px accordion | Single-open accordion |
| 15 | CTA | 2-col 40/60: text left, wizard right | 4-step wizard |
| 16 | Footer + book strip | 5-col links | — |

## C.2 Our Books — `our-books.php` — `/our-books` (4300px)
1. Navbar (light) · 2. **Page hero** — left-aligned h1 + paragraph, no buttons, no image ·
3. **Our Published Books Collection** — h2 left, paragraph **right-aligned**, then a 5-up cover
carousel with overhanging **red** arrows and overlay captions · 4. Our Services carousel (identical
to home §5) · 5. FAQ · 6. CTA + wizard · 7. Footer + book strip.

## C.3 About Our Company — `about.php` — `/about-our-company` (5510px)
1. Navbar · 2. Page hero (left-aligned h1 + paragraph) · 3. **Championing Independent Voices
Worldwide** — 2-col 35/65, image left, 2 paragraphs + 2 buttons right · 4. **Our Core Values** —
centred h2, 3-up cards, each with a 48px pale-green rounded icon tile, centred title + description ·
5. **Your Publishing Journey** — centred h2 + paragraph, 4 steps in a row joined by dashed green
connectors · 6. **Why Authors Choose Us** — pale band, header row (h2 left; paragraph + 2 buttons
right), then 2-col: image left, numbered list `01`–`05` right (each: green outlined circle + 24px
title + 16px description) · 7. FAQ · 8. CTA + wizard · 9. Footer.

## C.4 Pricing — `pricing.php` — `/pricing` (3774px)
1. Navbar · 2. Page hero · 3. Plans (centred header + 3-up) · 4. FAQ · 5. CTA + wizard · 6. Footer.

## C.5 Contact Us — `contact.php` — `/contact` (2632px)
1. Navbar · 2. Page hero · 3. CTA block (2-col: text+contacts+buttons left, wizard right) ·
4. **Green CTA band** — full-width `#60C488`, centred (this section is unique to this page) ·
5. Footer.
⚠️ Note the Figma page is misspelled `Conatct Us`; the file is `conatct-us__NN.jpg`.

## C.6 Privacy Policy — `privacy-policy.php` — `/privacy-policy` (2593px)
1. Navbar (light, on plain `#FAFAFA` — **no mint gradient**) · 2. Long-form document: h1 then
alternating h2 + paragraph/bulleted-list blocks, **left-aligned, full 1420px measure** (not narrowed)
· 3. Footer. No hero image, no CTA.

## C.7 Terms & Conditions — `terms-conditions.php` — `/terms-conditions` (2593px)
Identical structure to C.6.

## C.8–C.17 The ten service pages — `service.php?s=<slug>` (8776px each)
Identical template on all ten:

| # | Section | Layout | Behaviour |
|---|---|---|---|
| 1 | Navbar (**dark** variant) | over the hero photo | dropdown |
| 2 | Hero | Full-bleed photo + dark scrim; **2-col 55/45** — h1 + paragraph + 2 buttons left, white contact-form card right | — |
| 3 | Book strip | full-bleed | — |
| 4 | Press band | full-bleed black | — |
| 5 | Intro | 2-col 35/65, **image left**, h2 + 2–4 paragraphs + 2 buttons right | — |
| 6 | Why Us | **Centred** green panel: eyebrow, h2, paragraph (max ~700px), 5 inline radio-dot chips on 2 rows, white button | (Note: this is *centred*, unlike the homepage's 2-col green panel) |
| 7 | End-to-End | 2-col 55/45, text left (**image right**): h2, paragraph, `What We Offer:` label, 2-col dotted list, 2 buttons | — |
| 8 | Our Services | identical to home §5 | carousel |
| 9 | Your Publishing Journey | Centred h2 + paragraph; 4 steps with 56px green icon tiles joined by dashed green connectors | — |
| 10 | Author Stories | pale band, identical to home §11 | — |
| 11 | Testimonial marquee | identical to home §12 | marquee |
| 12 | Plans | identical | — |
| 13 | FAQ | identical | accordion |
| 14 | CTA + wizard | identical | — |
| 15 | Footer + book strip | identical | — |

**Only sections 2, 5 and 7 differ between the ten services.** Everything else is byte-identical —
build it once in the template, drive 2/5/7 from `data/services.php`.

---

# D. Copy

## D.1 Global chrome

**Nav:** `Home` · `Services` · `Our Books` · `Company` · `Pricing` · `Contact`
**Nav CTA:** `Publish Your Book`

**Services dropdown:** `Books Publishing` · `Book Illustration` · `Book Editing` ·
`Audio Book Production` · `Book Cover Design` · `Book Marketing` · `Blog Article Writing` ·
`Creative Content Writing` · `Ghostwriting` · `Proofreading` *(laid out in 2 columns, 5 rows — see §B.1)*

**Press band eyebrow:** `RECOGNIZED BY AUTHORS ACROSS THE GLOBE`
**Press logos (in order):** `SNN` · `TechCrunch` · `techopedia` · `TECH TIMES` ·
`The New York Times` · `WAPAKONETA DAILY NEWS` · `yahoo! news`

**Footer brand line:** `Contact@Elitepublishing.Co`
**Footer columns:** see §F.2.
**Footer legal:** `© Copyright 2026 All Rights Reserved.` · `Terms & Conditions` · `Privacy Policy`

## D.2 Shared sections (appear on many pages — write once)

### Our Services carousel
- Eyebrow: `OUR SERVICES`
- h2: `Featured Self-Publishing Solutions`
- Paragraph: `Whether you need standalone book editing or end-to-end book publishing services, we provide tailored solutions for every stage of your author journey:`
- Link on every card: `Learn More`

| # | Title (2 lines as drawn) | Description (as rendered — truncated with `…` in the design) | Icon |
|---|---|---|---|
| 1 | `Books`<br>`Publishing` | `Transform your raw manuscript into a bookstore-ready paperback, hardcover, or eBook. Our compre…` | magnifying glass |
| 2 | `Book`<br>`Editing` | `Refine your narrative before hitting market shelves. Our editorial team offers tailored tiers including struct…` | quill / leaf nib |
| 3 | `Book Cover`<br>`Design` | `Readers judge books by their covers—and so do retail algorithms. Our cover artists design modern, high-…` | stacked layers |
| 4 | `Book`<br>`Illustration` | `Bring your characters to life with custom artwork. Perfect for children's literature, graphic novels…` | open book |
| 5 | `Audio Book`<br>`Production` | `Tap into today's fastest-growing literary format. We manage the entire audiobook pipeline, includin…` | microphone |
| 6 | `Book`<br>`Marketing` | `Publishing your book is only the first step; reaching readers is where the real work begins. Our marketing s…` | balloon / megaphone (see §E.2) |
| 7 | `Blog Article`<br>`Writing` | `Build long-term organic traffic to your author website. Our content strategists write SEO-optimized bl…` | browser window / layout |
| 8 | `Creative Content`<br>`Writing` | `Need compelling text beyond your manuscript? We write high-converting web copy, author bios,…` | hand holding a pen |

⚠️ Only **8** cards exist. `Ghostwriting` and `Proofreading` have pages and dropdown entries but **no
service card**. Flag to design; do not invent two extra cards.
⚠️ The full untruncated descriptions are **not in the design**. Either ask the client for them or set
the visible text as the complete copy and drop the ellipsis.

### Your Publishing Journey
- h2: `Your Publishing Journey`
- Paragraph: `From your first idea to a bestselling book, we guide you through every step of the process with ease and expertise.`

| # | Title | Description | Icon |
|---|---|---|---|
| 1 | `Share Your Idea` | `Tell us your vision and goals, so we can plan the perfect strategy for your book.` | lightbulb |
| 2 | `Writing & Development` | `Our expert writers craft your manuscript into a compelling, polished story.` | hand writing with a pen |
| 3 | `Design & Formatting` | `Stunning cover designs and reader-friendly layouts that make your book stand out.` | artist's palette |
| 4 | `Publishing & Launch` | `We handle distribution on major platforms and help you promote your book worldwide.` | paper plane |

### Author Stories
- Eyebrow: `AUTHOR STORIES`
- h2: `What Our`<br>`Authors Say`
- Paragraph (right-aligned): `Discover the inspiring journeys of authors who placed their trust in us, transforming their manuscripts into bestselling masterpieces. Each story is a testament to the power of collaboration, creativity, and dedication.`
- Button: `Watch More Stories`
- Video card captions (all three, identical): `Clara Wen` / `Everything Remembered` ⚠️ placeholder

### Testimonial marquee
Quote A: `"If you want to hire a book publisher that actually respects your creative vision, look no further. The audiobook narration they arranged was Broadway-quality, and my royalties go straight to me."`
Quote B: `"As a first-time writer looking for reliable book publishing services, I was terrified of making costly mistakes. Elite Publishing guided me step-by-step through manuscript formatting, editing, and launch marketing."`

| Card | Quote | Name | Role | Badge |
|---|---|---|---|---|
| 1 | A | `Marcus Vance,` | `Author of Shadows Over Orion` | Trustpilot |
| 2 | B | `Elena Rostova,` | `Author of The Botanical Table` | Google |
| 3 | B | `David K.,` | `Children's Book Author` | black mark |
| 4 | A | `Marcus Vance,` | `Author of Shadows Over Orion` | Trustpilot |

⚠️ Names carry a trailing comma in the design. Card 4 duplicates card 1.

### Plans
- Eyebrow: `PLANS`
- h2: `Publishing Packages For`<br>`Every Author`
- **Identical on every page that shows it** (home, pricing, and all 10 service pages).

| | Basic Package | Standard Package | Premium Package |
|---|---|---|---|
| Price | `$` `700` `/ month` | `$` `1000` `/ month` | `$` `1500` `/ month` |
| Badge | — | `Most Popular` | — |
| Features | `Professional Editing`<br>`Basic Book Formatting`<br>`Simple Cover Design`<br>`Publishing Assistance` | `Ghostwriting Support`<br>`Editing & Proofreading`<br>`Custom Book Cover Design`<br>`Publishing & Distribution`<br>`Interior Formatting` | `Full Ghostwriting Service`<br>`Advanced Editing & Proofreading`<br>`Premium Cover & Layout Design`<br>`Global Publishing & Distribution` |
| CTA | `Get Started` | `Get Started` | `Get Started` |

### FAQ
- Eyebrow: `FAQ`
- h2: `Frequently Asked Questions.` *(the trailing full stop is in the design)*
- **Identical on every page that shows it.**

| # | Question | Answer |
|---|---|---|
| 1 | `What genres do you work with?` | `Fiction, non-fiction, romance , christian , self-help, children's, poetry, & academic — we match your project with a writer who specializes in your genre.` |
| 2 | `How long does the process take?` | `[NOT IN EXPORT]` |
| 3 | `Who owns the copyright to my book?` | `[NOT IN EXPORT]` |
| 4 | `How involved will I be?` | `[NOT IN EXPORT]` |
| 5 | `What's your refund policy?` | `[NOT IN EXPORT]` |
| 6 | `Will my book remain 100% confidential?` | `[NOT IN EXPORT]` |

⚠️ **Copy bug — reproduce or fix deliberately, don't do it by accident:** the answer contains
`romance , christian ,` with a space *before* the comma, twice.
⚠️ Answers 2–6 must come from the client; only item 1 is ever rendered open.

### CTA + wizard
- h2: `Let's Bring Your`<br>`Book To Life`
- Paragraph: `Ready to self-publish or have questions about our services? Get in touch with our editorial team today for a free manuscript evaluation and consultation.`
- Contact lines: 🌐 `ElitePublishing.co` · ✉ `contact@elitepublishing.co`
- Buttons: `Publish Your Book` · `Free Consultation`

**Wizard, step by step:**

| Step | Eyebrow | Question | Options | Buttons |
|---|---|---|---|---|
| 1 | `STEP 1 OF 4` | `What Genre Is Your Book?` | `📖 Fiction` *(selected)* · `📚 Non-Fiction` · `💌 Romance` · `📕 Christian` · `🌱 Self-Help` · `🎨 Children's` — 2-col | `Continue` |
| 2 | `STEP 2 OF 4` | `Where Are You In The Journey?` | `💡 Just an idea — I need help from scratch` · `📝 I have an outline or partial draft` *(selected)* · `📄 My manuscript is complete` · `🚀 Ready to publish and launch` — 1-col | ← + `Continue` |
| 3 | `STEP 3 OF 4` | `What's Your Budget Range?` | `$2,500 — $5,000` · `$5,000 — $10,000` · `$10,000 — $20,000` *(selected)* · `$20,000 — $[UNREADABLE]` · `Not sure — advise me` — 1-col | ← + `Continue` |
| 4 | `STEP 4 OF 4` | `Almost There!` | Inputs: `Full Name` (full width) · `Email Address` + `Phone No` (2-col) · `Message ...` (textarea) | ← + `Get My Free Consultation` |

⚠️ Step 3's fourth option is clipped in the export — the upper bound of the `$20,000 —` band is
`[UNREADABLE]`. Confirm from Figma.

## D.3 Home — page-specific copy

**Hero**
- h1: `Turn Your Manuscript Into A Bestseller With Elite Publishing , The Best Manuscript ` + `Publisher!` *(green highlight on the last word)*
  ⚠️ Note the stray space before the comma in `Publishing , The`. Renders on 3 lines:
  `Turn Your Manuscript Into A` / `Bestseller With Elite Publishing , The` / `Best Manuscript Publisher!`
- Paragraph: `Premium, complete book publishing services for independent authors. From line editing and cover design to global distribution and audiobooks, we help you publish a book with professional impact.`
- Buttons: `Submit Manuscript` · `Explore Our Services`
- Rating line: ★★★★★ `Trusted By 10,000+ Authors Worldwide`

**Why Us panel**
- Eyebrow `WHY US` · h2 `Why Choose Elite Publishing?`
- Paragraph: `Navigating the world of self-publishing companies shouldn't be overwhelming. As a hybrid publishing platform, Elite Publishing bridges the gap between full author control and traditional editorial perfection.`
- Buttons: `I want to Publish my Book! Start Now!` · `Schedule a Call`
- Glass cards:
  1. `100% Royalty & Ownership` — `You keep full rights and maximum royalties on every print, eBook, and audiobook sold.`
  2. `Industry-Grade Quality` — `Work with veteran editors, award-winning illustrators, and top-tier designers.`
  3. `Global Print & Digital Reach` — `Distribute directly to Amazon KDP, IngramSpark, Barnes & Noble, and international retailers.`

**Our Publishing Process**
- Eyebrow `OUR PUBLISHING PROCESS` · h2 `From Idea To Published Book In 7 Simple Steps`
- Tabs: `Consultation` *(active)* · `Planning` · `Writing` · `Editing` · `Design` · `Publishing` · `Marketing`
- Step 1: eyebrow `STEP 01` · title `Consultation` · body `Our streamlined publishing process ensures every book is professionally prepared, published, and promoted for maximum impact.`
- Checklist (2×2): `30-min video or phone call` · `Custom roadmap preview` · `Zero commitment required` · `Genre & audience analysis`
- ⚠️ Steps 02–07 are `[NOT IN EXPORT]`. Copy for six tabs is missing and must come from the client.

**Genres** — eyebrow `GENRES` · h2 `Books We Help Publish` · tabs `Fiction` *(active)* ·
`Non-Fiction` · `Romance` · `Christian` · `Children's`

**Portfolio** — eyebrow `PORTFOLIO` · h2 `Recently Published Titles` · button `View All`

**Review platforms**

| Platform | Score | Reviews | Link |
|---|---|---|---|
| `Trustpilot` | `4.9` `/5` | `Based on 1,247 reviews` | `View on Trustpilot` |
| `Google Reviews` | `4.8` `/5` | `Based on 892 reviews` | `View on Google` |
| `Reviews.io` | `4.8` `/5` | `Based on 634 reviews` | `View on Reviews.io` |
| `Sitejabber` | `4.9` `/5` | `Based on 634 reviews` | `View on Sitejabber` |

## D.4 Our Books
- h1: `Stories We've`<br>`Helped Bring To Life`
- Paragraph: `We take pride in transforming ideas into powerful, professionally written books. Each project reflects creativity, dedication, and a commitment to quality storytelling.`
- h2: `Our Published Books`<br>`Collection`
- Right-aligned paragraph: `A collection of books we've helped authors turn from ideas into professionally published titles.`
- Book captions: `Elena Hartwell` / `The Last Cartographer` (× all cards) ⚠️ placeholder

## D.5 About Our Company
- h1: `About Our`<br>`Company`
- Paragraph: `We are a professional book writing and publishing company helping authors turn their ideas into published books. From writing and editing to design and publishing,` ⚠️ *the sentence ends on a comma — unfinished copy in the design.*
- h2: `Championing Independent`<br>`Voices Worldwide`
  - P1: `Our Story Elite Publishing was founded on a simple principle: every author deserves access to the same high-caliber production quality as traditional publishing houses without giving up their creative freedom or royalties.` ⚠️ `Our Story Elite Publishing` reads like a missing heading break.
  - P2: `We are a premier team of editors, graphic artists, literary marketers, and publishing strategists dedicated to helping writers navigate the modern literary market. Whether you want to publish your book for the first time or scale your existing author brand, our end-to-end self-publishing packages provide the exact support, expertise, and distribution network you need to succeed.`
  - Buttons: `Get Started` · `Book a Free Consultation`
- h2: `Our Core Values`
  | Title | Description | Icon |
  |---|---|---|
  | `Author Empowerment` | `You retain complete ownership of your intellectual property and final creative approval.` | badge with a check |
  | `Uncompromising Standards` | `We treat every manuscript with the rigorous care of a top-tier manuscript publisher.` | shield |
  | `Transparent Publishing` | `Clear timelines, straightforward pricing, and dedicated project management with no hidden fees.` | eye |
- `Your Publishing Journey` — see §D.2
- h2: `Why Authors`<br>`Choose Us`
  - Paragraph: `We provide end-to-end publishing solutions that save time, maximize impact, and help your book succeed globally.`
  - Buttons: `View Services` · `Book a Free Consultation`
  - List: `01 100% Ownership Rights` — `Keep full control and rights of your book at all times.`
    `02 Experienced Publishing Team` — `Work with experts who know the publishing industry inside out.`
    `03 Fast Turnaround Time` — `Get your book from idea to launch without unnecessary delays.`
    `04 End-to-End Solutions` — `From writing, design, publishing, to marketing — everything under one roof.`
    `05 Transparent Process` — `Know exactly what's happening at every stage of your book's journey.`

## D.6 Pricing
- h1: `Choose The Right Package`<br>`For Your Book Journey`
- Paragraph: `Whether you're starting from an idea or ready to publish, we have flexible packages designed to fit your needs.`
- Then the shared Plans, FAQ and CTA blocks verbatim.

## D.7 Contact Us
- h1: `Get In Touch With`<br>`Our Team`
- Paragraph: `We're here to help you with your publishing journey. Reach out to us for any questions, project details, or expert guidance.`
- CTA block: shared copy (§D.2).
- Green band: h2 `Your Story Deserves To`<br>`Be Published.` ·
  paragraph `Take the next step in your author journey. Our team is ready to turn your idea into a professionally published success.` ·
  buttons `Publish Your Book` · `Free Consultation`

## D.8 Privacy Policy
h1 `Privacy Policy`

- **`Your Privacy Matters to Us`** — `We are committed to protecting your personal information and ensuring your experience with our services is safe, secure, and transparent.`
- **`Information We Collect`** — `When you use our website or services, we may collect the following information:`
  - `Name and contact details (email, phone number)`
  - `Project details you share with us`
  - `Billing or payment information (if applicable)`
  - `Communication history with our team`
- **`How We Use Your Information`** — `We use your information to:`
  - `Provide book writing, editing, and publishing services`
  - `Communicate with you about your project`
  - `Process payments and invoices`
  - `Improve our services and customer experience`
- **`Data Protection`** — `We take your privacy seriously and implement strict security measures to protect your personal data from unauthorized access, misuse, or disclosure.`
- **`Information Sharing`** — `We do not sell, rent, or trade your personal information. Your data is only shared with trusted team members involved in your project.`
- **`Third-Party Services`** — `We may use third-party platforms (such as payment processors or publishing platforms) that have their own privacy policies. We are not responsible for their practices.`
- **`Your Rights`** — `You have the right to:`
  - `Request access to your personal data`
  - `Ask for corrections or updates`
  - `Request deletion of your information`
- **`Updates to This Policy`** — `We may update this Privacy Policy from time to time. Any changes will be posted on this page.`
- **`Contact Us`** — `If you have any questions about this Privacy Policy, please contact us through our website.`

## D.9 Terms & Conditions
h1 `Terms & Conditions`

- **`Please Read Carefully Before Using Our Services`** — `By accessing our website or using our services, you agree to the following terms and conditions. If you do not agree, please do not use our services.`
- **`Services`** — `We provide professional book writing, editing, proofreading, design, and publishing services. All services are delivered based on the package or agreement selected by the client.`
- **`Payments`**
  - `All payments must be made according to the agreed pricing plan or invoice.`
  - `Work will begin only after receiving the initial payment (if applicable).`
  - `Payments are non-refundable once the project has started.`
- **`Project Delivery`**
  - `Delivery timelines are estimated and may vary depending on project complexity.`
  - `We aim to deliver high-quality work within the agreed timeframe.`
  - `Delays caused by missing client feedback or information are not our responsibility.`
- **`Revisions`**
  - `We offer a limited number of revisions based on the selected package.`
  - `Additional revisions may incur extra charges.`
  - `Revisions must be requested within the agreed revision period.`
- **`Intellectual Property`**
  - `Upon full payment, the final content belongs to the client.`
  - ` We reserve the right to showcase completed work in our portfolio unless otherwise agreed.` ⚠️ *leading space present in the design*
- **`Confidentiality`** — `We respect your privacy. All client information, manuscripts, and project details are kept strictly confidential and are not shared with third parties.`
- **`Limitation of Liability`** — `We are not responsible for any losses or damages arising from the use of our services or published content.`
- **`Changes to Terms`** — `We may update these Terms & Conditions at any time. Continued use of our services means you accept any updates.`
- **`Contact Us`** — `If you have any questions about these Terms, please contact us through our website.`

## D.10 Service pages — the shared template copy

Everything in §D.2 applies verbatim. In addition, these blocks are **identical on all 10 pages**:

**Hero form card** — title `Start Your Book Today` · placeholders `Full Name`, `Email Address`,
`Phone Number`, `Write your message here...` · submit `Send Message`
**Hero buttons** — `Publish Your Book` · `Free Consultation`
**Intro buttons** and **End-to-End buttons** — `Get Started` · `Book a Free Consultation`

**Why Us band (identical on all 10):**
- Eyebrow `WHY US` · h2 `Why Authors Trust Us`
- Paragraph: `We help you build more than just a book—we help shape your author identity. Every project is crafted with care, clarity, and full confidentiality while staying true to your voice. Our focus is to deliver a professionally written, market-ready book that creates real impact.`
- Chips: `100% Confidential Process` · `Your Voice, Your Style` · `Professional Writing Team` · `Publishing-Ready Quality` · `Collaborative Approach`
- Button: `Learn Our Story`

**Label above the What-We-Offer list:** `What We Offer:` — present on 8 of 10 pages
(⚠️ absent on `blog-article-writing` and `audio-book-production`).

### D.10.1 Per-service deltas

Hero headline line breaks are as drawn. Pricing and FAQ are **identical to §D.2 on all ten pages —
no per-service price or FAQ variation exists.**

---

#### 1. Ghostwriting — `/services/ghostwriting`
- **Hero h1:** `Turn Your Idea Into A` / `Powerful, Professionally` / `Written Book`
- **Hero p:** `We transform your ideas into compelling, high-quality books that reflect your voice, vision, and message. From concept to final manuscript, our expert ghostwriters handle everything so you can focus on your goals while we bring your story to life.`
- **Intro h2:** `Professional Ghostwriting` / `That Brings Your Story To Life`
- **Intro paragraphs (4):**
  1. `Every great book starts with an idea—but not every idea becomes a book. Turning thoughts and experiences into a well-written manuscript requires structure, clarity, and storytelling expertise.`
  2. `Our ghostwriting service helps authors, entrepreneurs, and professionals transform raw ideas into engaging, publish-ready books. Whether you have a rough concept or a full outline, we turn it into a clear and compelling narrative.`
  3. `We work closely with you to capture your voice, tone, and message so your book feels authentic while meeting professional publishing standards.`
  4. `The result is a book that sounds like you—only clearer, stronger, and more impactful.`
- **End-to-End h2:** `End-To-End Ghostwriting For` / `Your Book`
- **End-to-End p:** `We provide professional ghostwriting services designed to turn your ideas into a fully developed, publish-ready book. From the first concept to the final manuscript, we ensure your story is structured, engaging, and aligned with your voice and vision.`
- **What We Offer:** `Writing` · `Editing` · `Structuring` ‖ `Ghostwriting` · `Storytelling` · `Collaboration`

#### 2. Book Editing — `/services/book-editing`
- **Hero h1:** `Turn Your Manuscript` / `Into A Perfectly` / `Polished Book`
- **Hero p:** `Professional editing and proofreading that refines your writing, improves clarity, and prepares your book for publication.`
- **Intro h2:** `Professional Book Editing &` / `Proofreading That Perfects` / `Your Manuscript`
- **Intro paragraphs (2):**
  1. `We refine your manuscript to make it clear, polished, and professionally structured. Our editing process focuses on improving readability, fixing grammar, and enhancing flow while preserving your original voice and message.`
  2. `We carefully review every detail to ensure your book is smooth, engaging, and ready for professional publishing standards.`
- **End-to-End h2:** `End-To-End Book Editing For` / `Your Manuscript`
- **End-to-End p:** `We provide complete editing and proofreading solutions to turn your draft into a polished final version. From grammar correction to structure improvement, we refine every part of your book.`
- **What We Offer:** `Editing` · `Grammar` · `Structure` ‖ `Proofreading` · `Clarity` · `Formatting`

#### 3. Books Publishing — `/services/books-publishing`
- **Hero h1:** `Turn Your Book Into A` / `Globally Published` / `Success`
- **Hero p:** `We publish your book on leading platforms and make it available to readers worldwide. From setup to final launch, we handle everything so your book is professionally published and ready for global audience reach.`
- **Intro h2:** `Global Book Publishing Made` / `Easy`
- **Intro paragraphs (3):**
  1. `We simplify the entire publishing process by handling all technical and platform requirements. From formatting to final upload, we ensure your book is properly published and optimized for maximum visibility.`
  2. `Our team manages everything so you can focus on your writing while we take care of publishing and distribution.`
  3. `The result is a professionally published book ready for global readers.`
- **End-to-End h2:** `End-To-End Book Publishing` / `For Your Success`
- **End-to-End p:** `We provide complete publishing solutions to turn your manuscript into a professionally published book. From setup to final launch, we handle every step to ensure your book is properly published, formatted, and ready for global readers.`
- **What We Offer:** `Publishing` · `Platform Setup` · `Uploading` ‖ `Distribution` · `ISBN` · `Global Reach`

#### 4. Book Cover Design — `/services/book-cover-design`
- **Hero h1:** `Turn Your Book Into A` / `Visually Stunning` / `Bestseller`
- **Hero p:** `We design eye-catching, professional book covers that instantly attract readers and reflect your book's true message. From concept to final design, we create covers that stand out in both digital and print markets.`
- **Intro h2:** `Creative Book Cover Design` / `That Sells Your Story`
- **Intro paragraphs (3):**
  1. `We design professional and visually compelling book covers that capture attention and represent your book's genre, tone, and message. Every design is crafted to make your book stand out in a competitive market.`
  2. `Our team focuses on creating covers that not only look beautiful but also help increase visibility and reader interest.`
  3. `The result is a powerful, market-ready book cover that connects with your audience instantly.`
- **End-to-End h2:** `End-To-End Book Cover` / `Design For Your Success`
- **End-to-End p:** `We provide complete cover design solutions to visually represent your book in the most powerful way. From concept creation to final artwork, we design covers that match your story and attract the right audience.`
- **What We Offer:** `Publishing` · `Platform Setup` · `Uploading` ‖ `Distribution` · `ISBN` · `Global Reach`
  ⚠️ **Copy bug:** this list is copy-pasted from Books Publishing and has nothing to do with cover
  design. Build as drawn, but raise it with the client.

#### 5. Book Marketing — `/services/book-marketing`
- **Hero h1:** `Turn Your Book Into A` / `Bestselling Success`
- **Hero p:** `We promote your book with powerful marketing strategies that increase visibility, attract the right readers, and boost sales across global platforms. From launch to ongoing promotion, we handle everything to grow your author brand.`
- **Intro h2:** `Strategic Book Marketing` / `That Drives Real Results`
- **Intro paragraphs (3):**
  1. `We create targeted marketing strategies that help your book reach the right audience and gain maximum visibility. From author branding to promotional campaigns, we focus on building strong market presence.`
  2. `Our team ensures your book is not only published but also actively promoted to increase engagement, reach, and sales.`
  3. `The result is a strong author brand with a book that gets real attention.`
- **End-to-End h2:** `End-To-End Book Marketing` / `For Your Success`
- **End-to-End p:** `We provide complete marketing solutions to promote your book and build your author presence. From planning to execution, we ensure your book reaches the right audience effectively.`
- **What We Offer:** `Marketing` · `Branding` · `Audience Targeting` ‖ `Promotion` · `Launch Strategy` · `Visibility`

#### 6. Book Illustration — `/services/book-illustration`
- **Hero h1:** `Bring Your Story To Life` / `With Custom` / `Illustrations`
- **Hero p:** `We create unique, high-quality illustrations that visually represent your story, characters, and ideas. Our artwork helps transform your book into a more engaging and visually powerful experience for readers.`
- **Intro h2:** `Creative Custom Illustration` / `That Enhances Your Story`
- **Intro paragraphs (3):**
  1. `We design original illustrations tailored to your book's theme, genre, and vision. Every artwork is crafted to bring depth, emotion, and creativity to your storytelling.`
  2. `Our focus is to turn your ideas into visually stunning illustrations that make your book more engaging and memorable.`
  3. `The result is powerful artwork that elevates your entire book experience.`
- **End-to-End h2:** `End-To-End Custom` / `Illustration Services`
- **End-to-End p:** `We provide complete website solutions for authors to build their online presence. From design to launch, we ensure your website is fully functional, professional, and reader-focused.`
  ⚠️ **Copy bug:** this paragraph is about *websites*, not illustration. Clearly leftover copy.
- **What We Offer:** `Character Design` · `Scene Illustration` · `Cover Illustration` ‖ `Concept Art` · `Book Artwork` · `Visual Storytelling`

#### 7. Proofreading — `/services/proofreading`
- **Hero h1:** `Perfect Your Book` / `With Professional` / `Proofreading`
- **Hero p:** `We provide detailed proofreading services to remove errors and improve the overall quality of your manuscript. Your book becomes clean, polished, and ready for professional publishing.`
- **Intro h2:** `Professional Proofreading` / `That Perfects Every Word`
- **Intro paragraphs (3):**
  1. `We carefully review your manuscript to eliminate grammar, spelling, punctuation, and formatting errors. Our goal is to make your writing clear, smooth, and professional while preserving your original meaning and tone.`
  2. `Every detail is checked to ensure your book meets high publishing standards and delivers a flawless reading experience.`
  3. `The result is a clean, error-free manuscript ready for publication.`
- **End-to-End h2:** `End-To-End Proofreading For` / `Your Manuscript`
- **End-to-End p:** `We provide complete proofreading solutions to ensure your book is polished, professional, and fully refined, improving clarity, readability, and overall quality so it is completely ready for publication and readers worldwide.`
- **What We Offer:** `Grammar Correction` · `Spelling Check` · `Punctuation Fixes` ‖ `Sentence Clarity` · `Formatting Review` · `Consistency Check`

#### 8. Creative Content Writing — `/services/creative-content-writing`
- **Hero h1:** `Turn Your Ideas Into` / `Powerful, Engaging` / `Scripts`
- **Hero p:** `We create professionally written scripts that capture attention, deliver clear messages, and connect with your audience across different formats.`
- **Intro h2:** `Professional Script Writing` / `That Brings Ideas To Life`
- **Intro paragraphs (3):**
  1. `We craft well-structured and engaging scripts tailored to your purpose, whether for books, videos, podcasts, or storytelling projects. Our focus is on clarity, flow, and impactful communication.`
  2. `We transform your ideas into compelling scripts that are easy to follow, engaging, and professionally written.`
  3. `The result is a powerful script that delivers your message effectively and keeps your audience engaged.`
- **End-to-End h2:** `End-To-End Script Writing` / `Services`
- **End-to-End p:** `We provide complete script writing solutions from initial concept to final draft, ensuring your content is engaging, well-structured, and impactful while effectively connecting with your target audience and delivering a clear, compelling message.`
- **What We Offer:** `Script Writing` · `Story Structuring` · `Dialogue Writing` ‖ `Content Planning` · `Editing & Refinement` · `Creative Development`
  ⚠️ This page is titled "Creative Content Writing" but the entire body copy is about **script
  writing**. Raise with the client.

#### 9. Blog Article Writing — `/services/blog-article-writing`
- **Hero h1:** `Transform Your` / `Concepts Into` / `Engaging Scripts`
- **Hero p:** `We craft expertly written scripts that grab attention, convey clear messages, and resonate with your audience in various formats.`
- **Intro h2:** `Crafting Scripts That Bring` / `Your Vision To Life`
- **Intro paragraphs (2):**
  1. `Crafting scripts that truly reflect your vision is our passion. Whether it's for a book, video, podcast, or an engaging story, we focus on creating clear, compelling narratives. Our team ensures smooth transitions and powerful messaging, transforming your ideas into scripts that captivate and resonate with your audience.`
  2. `At our core, we are dedicated to bringing your ideas to life through expertly crafted scripts. Be it for a novel, a video, a podcast, or a captivating tale, we prioritize clarity and engagement in our storytelling. Our skilled team guarantees seamless transitions and impactful messaging, ensuring your concepts are transformed into scripts that truly connect with your audience.`
- **End-to-End h2:** `Comprehensive Script` / `Writing Services` — ⚠️ *does not use the "End-To-End" pattern*
- **End-to-End p:** `We offer a full range of script writing services, guiding you from the initial idea to the polished final draft. Our focus is on creating engaging, well-organized content that resonates with your audience and conveys a strong, clear message.`
- ⚠️ **No `What We Offer:` label** on this page — the list sits directly under the paragraph.
- **List:** `Script Creation` · `Narrative Structuring` · `Dialogue Crafting` ‖ `Content Strategy` · `Editing and Enhancement` · `Innovative Development`
  ⚠️ Page is titled "Blog Article Writing" but the copy is about **script writing**. Raise with the client.

#### 10. Audio Book Production — `/services/audio-book-production`
- **Hero h1:** `Transform Your` / `Concepts Into Audio` / `Scripts.` *(trailing full stop is in the design)*
- **Hero p:** `We craft expertly written audio scripts that grab attention, convey clear messages, and engage your audience in various formats.`
- **Intro h2:** `Engaging Audiobook Scripts` / `That Captivate Listeners`
- **Intro paragraphs (2):**
  1. `We create structured and captivating scripts specifically designed for audiobooks, ensuring clarity and a smooth narrative flow. Our goal is to turn your concepts into engaging scripts that resonate with listeners, making your message clear and impactful. The outcome is a dynamic script that keeps your audience hooked from start to finish.`
  2. `At our company, we specialize in crafting engaging scripts tailored for audiobooks. Our focus is on clarity and seamless storytelling, transforming your ideas into scripts that truly connect with listeners. The result is a captivating narrative that captivates your audience from beginning to end.`
- **End-to-End h2:** `Comprehensive Audio Book` / `Production Services`
- **End-to-End p:** `We offer full audio book production services, guiding you from the initial idea to the final recording. Our team ensures your content is captivating, well-organized, and resonates with your audience, delivering a strong and engaging narrative.`
- ⚠️ **No `What We Offer:` label** on this page.
- **List:** `Audio Book Script Development` · `Narrative Structuring for Audio Books` · `Character Dialogue Creation for Audio Books` ‖ `Audio Content Strategy` · `Audio Editing and Enhancement` · `Innovative Audio Development`

---

# E. Asset manifest

## E.1 Photography to crop from the page exports

**Positions are given as `page — approximate y in the 1920px artboard`.** Every photo has a ~16px
corner radius except the full-bleed hero backgrounds.

### Shared across all pages
| # | Asset | Where to crop | Notes |
|---|---|---|---|
| S1–S9 | **9 book covers** for the strip | `homepage__01` y 864–1112 (best crop), or the dedicated exports `component-104`…`component-112` (one cover each, full resolution) | Titles left→right: *It's Not Easy Being a Bunny* · *Judge Stone* (Viola Davis / James Patterson) · *Project Hail Mary* (Andy Weir) · *The Correspondent* (Virginia Evans) · *Game On* (Navessa Allen) · *Dear Debbie* (Freida McFadden) · *Theo of Golden* · *Warriors* (Erin Hunter) · *Freida McFadden* (pink) |
| S10 | **My Husband's…** (Alice Feeney) | `our-books__01` y ~950–1370 | 10th cover, only on Our Books |
| S11–S13 | **3 author-story video thumbnails** | `homepage__05` y 0–380 (also `ghostwriting__04` y 725–1120) | (a) young man in a pale-blue sweater + beanie, dark room; (b) Black man in a blue jacket against a yellow/blue colour-block wall; (c) smiling short-haired woman in a rust top, tan backdrop |
| S14–S17 | **4 reviewer avatars** (~32px circles) | `homepage__05` y ~690–720 | Very small in the export — request originals from the client rather than upscaling |

### About Our Company
| # | Asset | Where |
|---|---|---|
| A1 | Auburn-haired woman resting her chin on a stack of old books, tall library shelves behind | `about-our-company__01` y ~755–1345 (left column) |
| A2 | Woman holding *Curse of Stolen Flame* over her face, plain grey backdrop | `about-our-company__02` y ~1330 → `__03` y ~410 (left column) |

### Service pages — 3 photos each (30 total)

> ⚠️ **The "Hero background" column below no longer describes the built site.**
> Those crops contain the flattened page design — navbar, `<h1>`, buttons and the
> form card are baked into the pixels, so a hero built from them renders beneath
> a picture of itself. Heroes are now cut from the **End-to-End** photograph of
> the same service (DECISIONS §15). The hero descriptions are kept here as a
> record of what the design draws, and as the shopping list if bare image fills
> are ever exported from Figma. The Intro and End-to-End columns *are* accurate.

| Page | Hero background *(design only — not built, see above)* | Intro photo (left, `__02` y ~110–700) | End-to-End photo (right, `__03` y ~0–545) |
|---|---|---|---|
| ghostwriting | Woman reading a book while lying on a tartan blanket, dark warm interior | Dark-haired woman resting her chin on a stack of books (*Alte Sorten* visible) | Woman in a black blazer writing in a notebook in a café, plant + window behind |
| book-editing | Woman in a beige knit on a sofa reading; mug + notebook foreground | Woman from behind in a wooden rocking chair, floor-to-ceiling library wall | Smiling woman in an ivory sweater, chin on hand, open book, amber dome lamp |
| books-publishing | Hands on an open book at a desk strewn with open books + spiral notebook | Woman on a cream sofa under a black floor lamp, reading | Close-up: blonde woman's eyes above a Cyrillic fiery-art hardcover |
| book-cover-design | Woman from behind browsing a bookshop aisle between packed shelves | Overhead: woman lying on a fur rug beside an open book, glasses on the pages | Woman lying down holding an open orange-artwork hardcover to her cheek |
| book-marketing | Red-haired woman on a mint tufted sofa reading a red hardback, navy panelled wall | Red-haired woman in a pink floral dress kneeling at a white bookcase | Brunette in a white tee at a tall dark-wood bookshelf |
| book-illustration | Over-the-shoulder: woman browsing a magazine rack in a shop | **Illustrated artwork, not a photo** — auburn-haired woman writing at a lamplit desk | Woman in white lace holding a stack of Cyrillic-spined hardbacks |
| proofreading | Dark moody interior, person in a grey knit reclining and reading | Blonde woman with round glasses sitting in a bookshop doorway | Woman in a rust knit holding an open tan book in front of her face |
| creative-content-writing | Woman in animal print in a green velvet wingback by an open fire | Brunette in a dark-green knit reading under a cream blanket by a bookcase | From behind: person in a denim jacket + backpack at a library shelf |
| blog-article-writing | Red-haired woman in a rust knit writing in a notebook, dark blue-green bokeh | Woman in a cream sundress reading under a tree by a lake | Woman in gold-rimmed glasses and an olive cardigan reading by a brass lamp |
| audio-book-production | Dark-haired woman in a cream cardigan reading, lamp glow, steaming cup | Woman reading on a beige sofa under a throw, warm lamp + bookcase | Woman in a lilac cardigan reading on autumn grass, gothic building behind |

⚠️ These are stock/AI-generated photographs. Crop from the **1920px exports in
`Elite Publishing -fgma-images/`**, not from the 1280px `_figma-ref` slices — the slices are
downscaled and will look soft. Where possible, source the originals from Figma at 2× for retina.

## E.2 Icons and logos — hand-author as SVG (do **not** crop)

### Brand
| Asset | Description |
|---|---|
| **Logo mark** | A stack of books drawn as an isometric 3-D block: a green (`#60C488`) top slab and front face, with a dark-navy folded ribbon/bookmark shape (an angular `3`-like form) laid over the front. |
| **Logo wordmark** | `ELITE` in a high-contrast serif (navy `#1B2A4A`-ish), letter-spaced; `PUBLISHING` beneath in green, uppercase, tight sans, roughly half the cap height. **White reversal** on dark heroes. |

### UI glyphs
| Asset | Description | Used in |
|---|---|---|
| ↗ arrow | North-east arrow, 2px stroke, open head | every button and ghost link |
| ← / → | Left/right arrows, 2px stroke | carousel controls, wizard back button |
| ▾ caret | Small **solid filled** triangle | nav "Services" |
| ◉ eyebrow dot | Thick black ring — a solid octagonal donut with a white centre hole, ~14px | every eyebrow label |
| + / − | Plus and minus, 2px stroke, ~20px | FAQ accordion |
| ✓ in circle | White check inside a 20px filled `#60C488` circle | pricing features, process checklist |
| ▶ play | Dark triangle inside a ~48px translucent white circle | video story cards |
| ★ star | Solid five-point star | rating rows |
| • bullet | Small solid dot | Services dropdown; "What We Offer" list uses a **filled `#60C488` circle** |
| ◉ radio | Small filled circle with a ring | Why-Us chips on service pages |
| 🌐 globe / ✉ envelope | ~18px, green | CTA contact lines |

### Service card icons (28px, solid black, in a 68px `#F2F2F2` tile)
1. **Books Publishing** — magnifying glass over a page
2. **Book Editing** — quill nib / leaf-shaped pen tip
3. **Book Cover Design** — three stacked rounded layers
4. **Book Illustration** — open book
5. **Audio Book Production** — microphone
6. **Book Marketing** — a lightbulb/balloon-like teardrop with a stem ⚠️ *ambiguous at export resolution — confirm in Figma*
7. **Blog Article Writing** — browser window / layout blocks
8. **Creative Content Writing** — a hand holding a pen ⚠️ *low confidence*

### Journey step icons (28px white, in a 56px `#60C488` tile)
lightbulb · hand writing with a pen · artist's palette · paper plane

### Core Values icons (24px green, in a 48px pale-green tile)
badge-with-check · shield · eye

### Pricing tier icons
open book (green, outlined circle) · 4-point sparkle (white, on a green circle) · crown (green, outlined circle)

### Third-party logos (source official SVGs, do not trace)
**Press band:** SNN · TechCrunch · Techopedia · Tech Times · The New York Times ·
Wapakoneta Daily News · Yahoo News — all rendered greyscale/white at ~55% opacity.
**Review platforms:** Trustpilot · Google · Reviews.io · Sitejabber.
**Social:** Facebook · LinkedIn · Instagram (in 40px grey circles).

⚠️ **Legal note for the Lead:** the design uses real book covers (Penguin, Tor, etc.) and real press
mastheads. Clearing usage rights is a client decision, not a build decision — flag it, don't silently
ship it.

---

# F. Sitemap and navigation model

## F.1 Header navigation

| Order | Label | Target | Notes |
|---|---|---|---|
| 1 | `Home` | `/` | |
| 2 | `Services` | — | opens the dropdown; not itself a link |
| 3 | `Our Books` | `/our-books` | |
| 4 | `Company` | `/about-our-company` | label ≠ page title |
| 5 | `Pricing` | `/pricing` | |
| 6 | `Contact` | `/contact` | |
| CTA | `Publish Your Book` | `/contact` | assumed target — ⚠️ not specified in the design |

**Services dropdown — exact order (2 columns × 5 rows, read across):**

| Row | Left | Right |
|---|---|---|
| 1 | Books Publishing → `/services/books-publishing` | Book Illustration → `/services/book-illustration` |
| 2 | Book Editing → `/services/book-editing` | Audio Book Production → `/services/audio-book-production` |
| 3 | Book Cover Design → `/services/book-cover-design` | Book Marketing → `/services/book-marketing` |
| 4 | Blog Article Writing → `/services/blog-article-writing` | Creative Content Writing → `/services/creative-content-writing` |
| 5 | Ghostwriting → `/services/ghostwriting` | Proofreading → `/services/proofreading` |

## F.2 Footer link columns (exact labels and order)

| Col 1 | Col 2 | Col 3 | Col 4 | Col 5 |
|---|---|---|---|---|
| `Home` | `Portfolio` | `Books Publishing` | `Book Illustration` | `Blog Article Writing` |
| `Services` | `Pricing` | `Book Editing` | `Audio Book Production` | `Creative Content Writing` |
| `Company` | `Contact` | `Book Cover Design` | `Book Marketing` | |

⚠️ **`Portfolio` has no page.** It most plausibly points at `/our-books`. Confirm with the client.
⚠️ The footer service list omits **Ghostwriting** and **Proofreading** (7 of 10 services listed).
Build as drawn unless told otherwise.

Legal row: `Terms & Conditions` → `/terms-conditions` · `Privacy Policy` → `/privacy-policy`

## F.3 Full URL map

| Page | File | URL | Figma export |
|---|---|---|---|
| Home | `index.php` | `/` | Homepage.png |
| Our Books | `our-books.php` | `/our-books` | Our Books.png |
| About Our Company | `about.php` | `/about-our-company` | About Our Company.png |
| Pricing | `pricing.php` | `/pricing` | Pricing.png |
| Contact Us | `contact.php` | `/contact` | Conatct Us.png |
| Privacy Policy | `privacy-policy.php` | `/privacy-policy` | Privacy policy.png |
| Terms & Conditions | `terms-conditions.php` | `/terms-conditions` | Terms & Conditions.png |
| Ghostwriting | `service.php?s=ghostwriting` | `/services/ghostwriting` | Ghostwriting.png |
| Book Editing | `service.php?s=book-editing` | `/services/book-editing` | Book Editing.png |
| Books Publishing | `service.php?s=books-publishing` | `/services/books-publishing` | Books Publishing.png |
| Book Cover Design | `service.php?s=book-cover-design` | `/services/book-cover-design` | Book Cover Design.png |
| Book Marketing | `service.php?s=book-marketing` | `/services/book-marketing` | Book Marketing.png |
| Book Illustration | `service.php?s=book-illustration` | `/services/book-illustration` | Book Illustration.png |
| Proofreading | `service.php?s=proofreading` | `/services/proofreading` | Proofreading.png |
| Creative Content Writing | `service.php?s=creative-content-writing` | `/services/creative-content-writing` | Creative Content Writing.png |
| Blog Article Writing | `service.php?s=blog-article-writing` | `/services/blog-article-writing` | Blog Article Writing.png |
| Audio Book Production | `service.php?s=audio-book-production` | `/services/audio-book-production` | Audio Book Production.png |

The `/services/…` prefix matches the `.htaccess` rewrite already planned in `PROJECT-PLAN.md`.
`Company` and `Portfolio` are the only two nav labels whose URL is not literally derivable from the
label — handle them explicitly in `includes/config.php`.

---

# G. Open questions — need a decision before or during Phase 3

1. **`--fs-h1`: 76px or 80px?** My measurement says 76/88; `tokens.css` says 80/90. The same method
   reproduces the Figma-confirmed h2 exactly, so I lean 76 — but the Lead should read it off the
   Figma properties panel and settle it. *(Blocks: hero on all 17 pages.)*
2. **Exact font family.** Geometric sans, double-storey `a`. Urbanist is a defensible substitute.
   Confirm the real name; if it's a paid face, record the substitution in the QA report.
3. **FAQ answers 2–6** do not exist in any export. Client copy needed.
4. **Publishing Process steps 02–07** (Planning, Writing, Editing, Design, Publishing, Marketing) —
   title, body and checklist for each are missing. Client copy needed.
5. **Service card descriptions are truncated** in the design with `…`. Need the full sentences, or a
   decision to ship the visible text as-is.
6. **Book catalogue metadata.** Every book card says `Elena Hartwell / The Last Cartographer`.
   Real author/title per cover needed.
7. **Wizard step 3, option 4** — the upper bound of `$20,000 — $…` is clipped. Unreadable.
8. **`Portfolio` footer link** has no page. Point at `/our-books`?
9. **Two services missing from the Services carousel** (Ghostwriting, Proofreading) and from the
   **footer** service columns. Intentional or an oversight?
10. **Red carousel arrows on Our Books** (`#C41230`) vs green everywhere else. Intentional?
11. **Copy bugs to fix or preserve deliberately** — decide once, apply consistently:
    - FAQ answer: `romance , christian ,` (space before comma, ×2)
    - Homepage h1: `Elite Publishing , The` (space before comma)
    - About hero paragraph ends mid-sentence on a comma
    - About: `Our Story Elite Publishing was founded…` (missing heading break)
    - T&C: leading space on `  We reserve the right to showcase…`
    - Book Cover Design "What We Offer" list is Books Publishing's list
    - Book Illustration "End-To-End" paragraph is about **websites**
    - Creative Content Writing and Blog Article Writing pages are entirely about **script writing**
12. **Navbar sticky behaviour, hover states, and the "Publish Your Book" CTA target** are not
    expressed in a static export. Treat §B.3's hover spec as a proposal to be approved.
13. **Rights clearance** for the real book covers and press mastheads (see §E.2).
