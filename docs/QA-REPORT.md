# QA report — Elite Publishing

> ## Resolution — all findings addressed
>
> Every item below has been fixed and re-verified. The audit text is preserved
> unedited as the record of what was found.
>
> | § | Finding | Fix, and how it was verified |
> |---|---|---|
> | 1.1 | `/about-our-company` 404s | Explicit rewrite added, plus `EP_PAGE_URLS` so nav, footer, sitemap and canonicals all publish extensionless URLs. All 17 URLs return 200; `.php` variants now canonicalise to the pretty form. |
> | 1.2 | Failed wizard submission invisible | Wizard banner gained `id="form-result"` + `tabindex="-1"`; `initWizards()` opens the step owning the first error and focuses the banner. Re-measured: banner **in viewport and focused**, active step **3** (was 0), scrollY **7692** (was 0). |
> | 1.3 | Closed drawer in the tab order | `visibility: hidden` when closed. Verified at 390: nav and its links compute `hidden` closed, `visible` open. The visibility transition is delayed on close and instant on open — a duration would have flipped it at 50%, leaving the drawer unfocusable for 100ms. |
> | 1.4 | Open drawer leaks focus | `inert` applied to `<main>` and `.ep-footer` while open, removed on close. Verified both toggle correctly. |
> | 1.5 | Three dead play buttons | Cards render as `<figure>` unless a card carries a `video` URL, in which case they become real links. Verified: 3 figures, **0** focusable dead controls. |
> | 1.6 | Carousels keyboard-unreachable ≤767px | `initScrollerA11y()` makes any overflowing rail with no focusable children `tabindex="0"` with a label, so arrow keys scroll it. Deliberately sets no `role` — see the regression note below. |
> | 2.1 | Footer brand green, not mint | Now `--ep-green-tint` (`#EAF5EF`) with the spec'd 3px green top rule. Verified computed `rgb(234,245,239)`. |
> | 2.2 | Services dropdown order | New `EP_SERVICES_DROPDOWN` constant in the design's row-major order, kept separate from `EP_SERVICES` so the footer, sitemap and 404 list are unaffected. |
> | 2.3 | No header CTA below 1200px | `Publish Your Book` added inside the drawer, `display:none` on desktop so only one copy is ever in the accessibility tree. Verified `display: flex` at 390. |
> | 2.4 | About header inverts at 768/1024 | `align-items: start` below 1200px. Delta was 103px/145px; now **0 at both**. |
> | 2.5 | Pricing loses symmetry at 1024 | Equal heights between 992–1199px. All three cards now `top 664 / height 526` (was 700/454, 667/521, 664/526). |
> | 2.6 | Surviving white-on-green | `.ep-btn--green-outline:hover` moved to `--ep-on-green`. |
> | 3.1 | JS-off loses 5 of 6 FAQ answers | `hidden` removed from markup; a `.js`-gated CSS rule collapses panels only when JS can reopen them. |
> | 3.2 | Six dead buttons in the no-JS wizard | Stepping controls are `display:none` until `.js` is present. |
> | 3.3 | Marquee cannot be paused | Real Pause/Play toggle with `aria-pressed`. Verified running → paused → running. |
> | 3.4 | Escape doesn't close the dropdown | Wired, returning focus to the trigger. |
> | 3.5 | Wizard ships pre-answered | `selected` no longer applied — steps 1–3 ship blank, so the guard is reachable and no lead carries a fabricated budget. Verified **0** pre-checked radios. |
> | 3.6 | No `scroll-padding-top` | Added, breakpoint-aware, plus `tabindex="-1"` on `<main>` so the skip link moves focus. |
> | 4 | Footer tap targets · 404 canonical · CSRF discards typing · inflated `scrollWidth` | Links padded to 32px; 404 emits `noindex` instead of a canonical; CSRF failure now hands back what was typed; `overflow-x: clip` on `body` unifies the reported width. |
>
> **One regression I introduced and caught:** the first version of the carousel
> fix set `role="group"` on the rails, which are `<ul>`s — that overrode the
> implicit list role and orphaned every `<li>`. Lighthouse dropped to 97. The
> role was removed; `tabindex` and a label are all a scroll region needs.
>
> **Re-verified after all fixes:** Lighthouse **100 / 100 / 100 / 100** on home
> (desktop) and a service page (mobile), zero failed audits; 17/17 URLs 200 with
> no PHP errors; `data/` and `docs/` still 403; unknown paths still 404.
>
> Still outstanding and **not** QA's to fix: mail delivery is untested (no MTA),
> service hero photography is a 3× upscale of a 635px source (DECISIONS §15),
> and book-cover/press-masthead rights are unresolved (CLIENT-QUESTIONS).


**Scope:** all 17 URLs plus a 404 check, at 1920 / 1440 / 1024 / 768 / 390, in Chrome via
Chrome DevTools MCP and a Playwright-driven Chromium (device-metrics emulation, so 1920 and 390
are true viewports, not window sizes).

**Method:** scripted sweeps across all 18 URLs (HTTP status, link and asset resolution, console,
heading order, overflow, font sizes, tap targets, accessible names, contrast) plus hand-driven
interaction, keyboard and form testing. Rendered text of all 17 pages was diffed against SPEC §D
line by line.

**Not re-reported:** the 12 deliberate copy bugs (DECISIONS §11), the drafted FAQ/process/card copy,
and everything already logged in `docs/reports/*.md`. Where I confirm or contradict a builder's
note I say so explicitly.

---

## 1. Broken

### 1.1 `/about-our-company` returns HTTP 404
**Repro:** `GET http://localhost/Elite%20Publishing/about-our-company` → **404**, rendering the
"This Page Has Not Been Written Yet" page. Any viewport.

SPEC §F.3 assigns `about.php` the URL `/about-our-company`. `.htaccess` only rewrites an
extensionless path to a **same-named** `.php` file, and there is no `about-our-company.php`, so the
one URL in the spec whose path differs from its filename is dead. One of the 17 pages I was asked
to check does not exist at its specified address.

Related, same root cause: the site serves and links its six content pages at `.php` URLs —
`/our-books.php`, `/about.php`, `/pricing.php`, `/contact.php`, `/privacy-policy.php`,
`/terms-conditions.php` — while the ten service pages use the pretty `/services/<slug>` form.
`ep_nav()`, `ep_footer_nav()`, `sitemap.php` and every `<link rel="canonical">` publish the `.php`
form. The extensionless variants happen to resolve for the five pages whose filename matches, so
each of those pages is reachable at two URLs. **Why it matters:** a spec'd URL 404s, the URL scheme
is half-migrated, and the canonical/sitemap pair bakes the `.php` form into search results.

### 1.2 A failed wizard submission is completely invisible
**Repro:** `/services/ghostwriting` at any viewport. Put `nope` in the wizard's *Email Address*
field, submit. Also reproduces on `/`, `/our-books.php`, `/about.php`, `/pricing.php` and all ten
service pages. **Does not** reproduce on `/contact.php`, which is the only page that gets this right.

Measured after the redirect to `…/services/ghostwriting?form=error#form-result`:

| | |
|---|---|
| `window.scrollY` | **0** |
| `document.getElementById('form-result')` | **null** — the anchor does not exist |
| wizard error banner, document y | **7089** (page height 8293) |
| banner in viewport | **false** |
| active wizard step | **0** (reset to "What Genre Is Your Book?") |
| `display` of the step holding the 4 `aria-invalid` fields | **`none`** |

Three faults compound. `forms/contact-handler.php:209` appends `#form-result` for wizard posts, but
`includes/components/cta-wizard.php:76` renders the wizard banner with **no `id`** — the only
`id="form-result"` on a service page belongs to the *hero* form's alert, which is correctly
suppressed when the flash belongs to the wizard. So the fragment resolves to nothing and the page
stays at scroll 0. Meanwhile `main.js` re-initialises the wizard at step 1, so the four fields that
were flagged are two steps away behind `display:none`.

**Why it matters:** the visitor presses "Get My Free Consultation", the page appears to reload
unchanged, and there is no signal at all that the enquiry was rejected. This is the site's primary
lead form on 15 of 17 pages.

### 1.3 The off-canvas mobile menu stays in the tab order while closed
**Repro:** any page at **390** (also 768 and 1024). Focus the burger, press Tab.

Focus goes to `Home` → `Services` → `Our Books` → `Company` → `Pricing` → `Contact`, all at
`x = 400` in a 390px viewport, i.e. entirely off-screen, with the drawer closed. Sixteen focusable
elements in total. `main.css:406` parks `.ep-nav` at `position: fixed; transform: translateX(100%)`
with no `visibility: hidden`, no `inert`, no `aria-hidden` — only a transform, which does not remove
anything from the accessibility tree or the tab order.

**Why it matters:** every keyboard user on a phone or tablet hits six invisible controls before
reaching page content, and a screen reader announces a navigation menu that is not on screen.
Automated audits pass this because nothing is technically hidden.

### 1.4 The open mobile drawer does not contain focus
**Repro:** `/` at **390**. Tap the burger, then press Tab seven times.

After `Contact` the focus leaves the drawer and lands on `Submit Manuscript` (y=525) in the page
behind the scrim, then continues through the whole page — `Learn More` ×8 at y=1729,
`I want to Publish my Book!` at y=2189, and so on. The page behind is not `inert`, and because
`main.js:27` sets `document.body.style.overflow = 'hidden'` while the drawer is open, those focused
controls cannot even be scrolled into view.

Escape *does* close the drawer and return focus to the burger — that half is correct.

### 1.5 Three "Play the author story" buttons do nothing
**Repro:** `/` (and all ten service pages), Author Stories band. Click, or Tab to a card and press
Enter. Nothing happens.

`includes/components/author-stories.php:56` renders
`<button class="story-card" aria-label="Play the author story of Clara Wen — Everything Remembered">`
for each of the three cards. There is no `story-card` handler in `main.js` or `p-home.js`, and no
dialog/modal implementation anywhere in the codebase. SPEC §C.1 §11 specifies a video modal.

**Why it matters:** three controls that announce themselves as play buttons and are inert. For a
screen-reader user they are three identically-named dead ends. (The "modal returns focus" check in
my brief has no subject — there is no modal to test.)

### 1.6 At ≤767px the book carousels cannot be reached without a mouse or touch
**Repro:** `/` at **390** (Genres rail and Portfolio rail) and `/our-books.php` at **390**
(the collection rail).

Measured: the prev/next buttons are `display:none` below 768px; the tracks contain **zero** focusable
children (the covers are plain images, not links) and carry no `tabindex`. There is therefore no
keyboard path to slide 2 onward — a keyboard user on a phone sees the first 1–2 covers of each rail
and nothing else. WCAG 2.1.1.

The Our Services carousel is unaffected — its cards contain `Learn More` links, so tabbing scrolls
the track.

---

## 2. Differs from the design

### 2.1 The footer band is brand green, not the mint tint — all 17 pages
`main.css:441` sets `.ep-footer { background: var(--ep-green) }`, rendering **`rgb(96,196,137)`**.

SPEC §B.2 specifies band fill **`#EAF5EF`**, and goes out of its way to warn against exactly this:
"⚠️ `footer__01.jpg` shows the footer on a saturated green background. That is the Figma page canvas
showing through a transparent component frame — **every page render uses `#EAF5EF`**. Build the mint
version." The `--ep-green-tint: #EAF5EF` token exists and is used for other bands but not here.

Not an accessibility problem — the footer text is ink at 6.66:1 — but it is the largest single
colour deviation on the site and it repeats on every page. **Repro:** scroll to the footer on any URL.

### 2.2 Services dropdown items are in the wrong order — all 17 pages
The panel is `display: grid; grid-template-columns: 1fr 1fr` (`main.css:372`) with default row-major
flow, and the items come from `EP_SERVICES` order in `includes/config.php:68`. Every label is present
and correctly spelled; the sequence is not the design's.

| Row | Rendered left | Rendered right | SPEC §B.1/§F.1 left | SPEC right |
|---|---|---|---|---|
| 1 | Books Publishing | Book Editing | Books Publishing | Book Illustration |
| 2 | Book Cover Design | Book Illustration | Book Editing | Audio Book Production |
| 3 | Audio Book Production | Ghostwriting | Book Cover Design | Book Marketing |
| 4 | Book Marketing | Proofreading | Blog Article Writing | Creative Content Writing |
| 5 | Creative Content Writing | Blog Article Writing | Ghostwriting | Proofreading |

Not covered by DECISIONS. Fix is a reorder in `EP_SERVICES` (or a dedicated dropdown order array) —
but note `EP_SERVICES` also drives the footer columns and the 404 fallback list, so reorder
deliberately.

### 2.3 The `Publish Your Book` header CTA vanishes below 1200px and is not moved into the drawer
Measured `.ep-header__cta` visibility: **1920 ✔ · 1400 ✔ · 1199 ✘ · 1024 ✘ · 768 ✘ · 390 ✘**, and
`#ep-nav` contains no `.ep-btn` at any width. `main.css:427` hides it under 1200px and the drawer
(`includes/header.php:25–54`) only ever contains the six nav items.

**Why it matters:** the site's single primary conversion control is absent from the header on every
tablet and phone. The design has no mobile artboard, so this is a build decision rather than a
contradiction — but it is the wrong one, and it is invisible in the desktop QA everyone else ran.

### 2.4 "Why Authors Choose Us" split header inverts at 768 and 1024 — `/about.php`
Measured document-y of the `<h2>` versus its own paragraph:

| Viewport | h2 y | paragraph y | verdict |
|---|---|---|---|
| 1920 | 2813 | 2817 | aligned ✔ |
| 1440 | 2492 | 2485 | aligned ✔ |
| **1024** | **2178** | **2075** | h2 sits **103px below** its paragraph |
| **768** | **3225** | **3080** | h2 sits **145px below** its paragraph |
| 390 | 3441 | 3540 | stacked correctly ✔ |

At 768 the on-screen reading order is paragraph → `View Services` → `Book a Free Consultation` →
heading, with a large empty area in the top-left of the band. The two-column header keeps its
columns but the left cell bottom-aligns instead of collapsing.

### 2.5 Pricing cards lose their symmetry at 1024
**Repro:** `/pricing` at **1024** (also the Plans block on `/` and all ten service pages).

Measured card boxes (document coordinates):

| Viewport | Basic top / height | Standard top / height | Premium top / height |
|---|---|---|---|
| 1920 | 1070 / 483 | 1036 / 550 | 1070 / 483 ✔ |
| 1440 | 908 / 467 | 875 / 534 | 908 / 467 ✔ |
| **1024** | **700 / 454** | **667 / 521** | **664 / 526** |

At 1024 the Premium card is **5px taller than the "Most Popular" Standard card** and starts **36px
above Basic**, so the outer pair is visibly staggered and the featured card no longer reads as the
raised one — the whole point of the Standard treatment. Cause: Premium's feature list wraps to two
lines in a 294px column and the row is centre-aligned rather than top-aligned/stretched.
768 and 390 stack cleanly.

### 2.6 One surviving white-on-green
`main.css:578` — `.ep-btn--green-outline:hover { background: var(--ep-green); color: var(--ep-white) }`
= white on `#60C489`, **2.15:1**. Applies to `Book a Free Consultation` on `/about.php` and all ten
service pages, and to the Basic/Premium `Get Started` buttons in the Plans block. Every other green
surface was correctly moved to `--ep-on-green` (`#2B2A28`); this hover state was missed.

Lower priority, same family: `.plan--featured .plan__icon` (white sparkle on green) and
`.journey__tile--green` (white glyph on green) render non-text graphics at 2.15:1 where WCAG 1.4.11
wants 3:1.

---

## 3. Progressive enhancement and interaction gaps

### 3.1 With JavaScript off, five of six FAQ answers are unreachable
`includes/components/faq.php:52` ships panels 2–6 with the `hidden` attribute in the markup, and the
buttons are inert without JS. Verified in a `javaScriptEnabled: false` browser context on
`/pricing`. The wizard was deliberately built progressive-enhancement-first and degrades correctly
(see 3.2); the FAQ was not, so a third of the page's content silently disappears.

### 3.2 The no-JS wizard works, but shows six dead buttons
Verified with JS off on `/contact.php`: all four `.wizard__step` fieldsets are `display: block`, the
form posts, and the real success banner renders — the progressive-enhancement claim holds. However
the three `Continue` buttons and three back buttons are `type="button"` and stay visible, so the
degraded form presents seven buttons of which only the last does anything. `hidden` on the stepping
controls plus a `.js`-scoped unhide would fix it.

### 3.3 The testimonial marquee cannot be paused
`/` and all ten service pages. `.marquee__track` runs `marquee 64s linear infinite`.
`.marquee:hover` and `.marquee:focus-within` pause it, but the track contains **zero focusable
elements**, so `:focus-within` can never fire — a keyboard-only or touch user has no way to stop it.
WCAG 2.2.2 (Pause, Stop, Hide). `prefers-reduced-motion: reduce` *is* honoured correctly, which
mitigates it for users who set that preference. The duplicated second half of the track is correctly
`aria-hidden="true"`.

### 3.4 Escape does not close the Services dropdown on desktop
`main.js:36` binds Escape only to the mobile burger. **Repro:** `/` at 1440, Tab to `Services`, press
Enter (opens, `aria-expanded="true"`), press Escape — it stays open. Focus-out does close it, and
both the mouse-hover and keyboard paths into the ten items work.

### 3.5 The wizard arrives pre-answered, so leads carry answers nobody gave
Measured on load: `genre=Fiction`, `stage=I have an outline or partial draft`,
`budget=$10,000 — $20,000` are all `checked`. The design draws those as selected states, so the
markup is faithful — but as a live form it has two consequences. First, `main.js`'s `valid()` guard
can never fail: I could only exercise the "cannot skip a required choice" path by clearing the
radios from the console (it then correctly refuses to advance and shakes the first chip). Second, a
visitor who clicks Continue three times submits a fabricated $10k–$20k budget, and there is no way
for sales to tell a real answer from a default. Recommend shipping steps 1–3 unanswered.

### 3.6 No `scroll-padding-top` for the 96px sticky header
`html` computes `scroll-padding-top: auto` and `main` has `scroll-margin-top: 0`. **Repro:** `/`,
press Tab then Enter on "Skip to main content" — the page scrolls to y=96, which places the top of
`<main>` exactly under the sticky header. The same will apply to any in-page anchor added later.
Separately, `<main id="main">` has no `tabindex="-1"`: the skip link moves the scroll position and
Chrome's sequential-focus starting point (the next Tab does land on `Submit Manuscript`), but it does
not move focus or a screen reader's cursor, and the behaviour is historically unreliable outside
Chrome.

---

## 4. Cosmetic

- **Footer column links are 17px tall at 390** — `Home` 37×17, `Services` 52×17. WCAG 2.5.8 AA wants
  24×24 or equivalent spacing; the row gap partly compensates but they are tight to tap.
- **`404.php` emits `<link rel="canonical" href=".../404.php">`** alongside a real 404 status. A
  canonical on an error page is noise for crawlers.
- **`documentElement.scrollWidth` reads 2783px at a 1905px viewport** on every page. There is **no**
  real horizontal scroll — I verified `window.scrollTo(2000,0)` leaves `scrollLeft` at `0` on all 18
  URLs at all five viewports — but the inflated number will trip any automated overflow check. It
  comes from the off-canvas `.ep-nav` (§1.3) and the `.hp-field` honeypot at `left:-9999px`. Dev 2
  §3.3 reported a *draggable* scrollbar; I could not reproduce that, on any page or viewport. Dev 2's
  `overflow-x: clip` guard on `body.page-service`/`body.page-pricing` means those 11 pages report a
  clean `scrollWidth` while the other 6 do not — worth unifying.
- **Bad CSRF discards the user's typing.** `contact-handler.php:215` passes `old: []` on a CSRF
  failure, so a genuinely expired session shows "Your session expired before the form was sent.
  Please try again." above an empty form.
- **Genre filter leaves the rail nearly empty** — already logged by Dev 1 §2.1; noting only that at
  1920 the `Christian` and `Non-Fiction` tabs render a single cover in a 6-up rail with both arrows
  disabled, which looks broken rather than filtered.
- **Service hero photography.** As instructed, one line: the ten hero images now render as clean
  photography with no baked-in navbar, headline or form card (checked `/services/ghostwriting` at
  1920 and 390) — the known blocker appears resolved. One caveat worth a Figma check: ghostwriting's
  hero shows a woman writing in a café with a plant behind her, which is SPEC §E.1's *end-to-end*
  photo for that page, not its "woman on a tartan blanket" hero.

---

## 5. Checked and clean

One line each, so the coverage is legible.

- **Copy fidelity, all 17 pages against SPEC §D.** Clean apart from §2.2. Every one of the ten
  service pages carries its own hero h1 (with the designed line breaks), hero paragraph, intro h2,
  intro paragraph *count and text*, end-to-end h2, end-to-end paragraph and offer list — no page
  borrowed a neighbour's copy. The shared region from `OUR SERVICES` to the end of document hashes
  identically across all ten. Nav, footer columns and legal row, press band and its seven logos,
  services carousel, FAQ, Plans, CTA + wizard, testimonials, journey, and every page-specific block
  on home / our-books / about / pricing / contact / privacy / terms are verbatim. `What We Offer:` is
  present on exactly the eight correct pages.
- **Console.** Zero errors and zero warnings on all 17 pages at all five viewports. The only console
  entry anywhere is the expected `404 (Not Found)` on `404.php` itself.
- **Links and assets.** 253 distinct assets and 17 distinct internal link targets fetched: **zero**
  non-200 responses. `favicon.svg`, `apple-touch-icon.png` and the JSON-LD publisher logo all resolve
  (Dev 3 §3.4 and Assets §7 are fixed). Every `<img>` on every page has `alt`, `width` and `height`.
- **Forms.** All four paths behave correctly, and the two forms on a service page are properly
  isolated. A failed **wizard** post renders its banner only inside `.wizard-card`, repopulates the
  wizard's four fields with `aria-invalid="true"` and per-field `.ep-error` messages, and leaves the
  hero form untouched; a failed **hero** post does the mirror image. Valid submissions return a
  `role="status"` banner; the honeypot returns a fake success and delivers nothing; a bad CSRF token
  is rejected before any field is read. On `/contact.php` the success banner is focused and scrolled
  into view (scrollY 553). The only defects are §1.2 and the CSRF repopulation note in §4.
- **FAQ accordion.** Item 1 open on load, exactly one open at a time, clicking the open item closes
  it, `aria-expanded` and panel `hidden` stay in sync. Correct with JS on.
- **7-tab process panel.** Seven tabs, `role="tab"`/`aria-selected` correct, roving tabindex
  (`0`/`-1`), clicking a tab swaps exactly one visible `role="tabpanel"`.
- **Genre filter.** Five pills with `aria-pressed`, filtering to 4 / 1 / 2 / 1 / 2 covers, pressed
  state moves correctly.
- **Wizard stepping (JS on).** Forward and back work, inactive steps are `display: none` so their
  controls leave the tab order, the progress segments track the index in both directions, focus moves
  to the new question heading on each step change, and the back button is labelled "Previous step".
- **Services dropdown.** Opens on hover and on Enter, all ten items are tabbable and in the tab order
  while open, closes on focus-out and on outside click. Only Escape is missing (§3.4).
- **Mobile burger.** 44×44, `aria-expanded`/`aria-label` toggle correctly, scrim closes it, Escape
  closes it and returns focus. Its failings are containment, not wiring (§1.3, §1.4).
- **Focus visibility.** A global `:focus-visible { outline: 3px solid #60C489; outline-offset: 2px }`
  applies to every control I tested including the carousel arrows and the story cards; text inputs
  swap to a green border plus a 3px ring; the visually-hidden wizard radios correctly project their
  ring onto the chip label. Nothing focusable is invisible when focused.
- **Heading structure.** Exactly one `<h1>` per page, no skipped levels on any of the 17.
- **Horizontal scroll.** None, anywhere — verified by attempting to scroll, not by reading
  `scrollWidth` (see §4).
- **Type sizes.** No rendered text below 12px on any page at any of the five viewports.
- **Tap targets.** No control under 24px except the footer column links at 390 (§4).
- **Contrast.** No text/background pair below AA on any page, at any viewport, other than §2.6. The
  service-page hero holds white text over a dark scrim and passes; the earlier ink-on-green decision
  is applied consistently everywhere else, including the footer (6.66:1) and the open FAQ panel.
- **404 handling.** A bogus path returns a real `404` status with the site's 404 page, both directly
  and through the rewrite.
- **PHP.** No notices, warnings or fatals in the output of any of the 18 responses.

---

## 6. Verdict

**Not shippable as-is, but close.** The design work is genuinely good and the copy is clean — an
entire page-by-page diff against SPEC §D turned up exactly one ordering error. Nothing is broken in
the build sense: no console errors, no dead links, no missing assets, no PHP notices, no horizontal
scroll, correct heading structure, real focus rings, and a contact handler whose CSRF, honeypot,
validation, rate-limit and two-form flash isolation all do exactly what they claim.

What stops it is a small number of things that only show up when you actually use the site rather
than look at it. In order:

1. **§1.2 — the invisible wizard failure.** Fix first, no argument. The primary lead form on 15 of
   17 pages can reject a visitor with zero feedback. Give the wizard's banner `id="form-result"` and
   `tabindex="-1"`, and have `initWizards()` open the step that owns the first error instead of
   step 1.
2. **§1.1 — `/about-our-company` 404s.** One rewrite rule, or one decision to standardise the URL
   scheme. A spec'd page that does not exist is not a QA nitpick.
3. **§1.3 + §1.4 — the mobile drawer.** `inert` (or `visibility: hidden`) on `.ep-nav` when closed,
   and `inert` on `<main>`/`<footer>` while it is open. Two attributes; removes six invisible tab
   stops and a focus leak from every page on every phone.
4. **§1.5 — the three dead play buttons.** Either build the modal or stop rendering controls that
   promise something they cannot do.
5. **§2.1 — the green footer.** One CSS value, on every page, against an explicit written warning in
   the spec. Cheapest visible win on the list.

After those five, §1.6 (keyboard carousels on mobile), §2.3 (the missing mobile CTA), §2.5 (pricing
at 1024) and §3.5 (the pre-answered wizard) are the ones I would not let go to a client.

---

## Addendum — responsive sweep

Re-checked at 320, 360, 414, 479, 576, 768, 834, 991, 1024, 1199, 1440, 1920
and 2560, plus landscape phone (844 × 390), on every page template. Checks per
size: sideways panning, elements escaping the viewport or their parent,
overlapping siblings, controls under 24 × 24, distorted images, text under 12px.

**One real defect found and fixed: ungated `:hover`.** All 38 hover rules
applied on touch devices, which report `hover: none`. A tap leaves the hover
state applied until the visitor taps elsewhere, so tapping a service card left
it inverted to brand green. Every hover rule is now inside
`@media (hover: hover)`; verified as 33/33 guards active on a mouse pointer and
0/33 on touch, with the desktop appearance measured unchanged
(`.svc-card:hover` still resolves to `#60C489` with `--ep-on-green` text).

Everything else came back clean. Three results that look like defects and are
not — the off-canvas drawer inflating `documentElement.scrollWidth`, carousel
arrows overhanging their rail by design, and the deliberate three-line clamp on
service card text — are documented in the README's Responsive section so the
next audit does not re-open them.

Confirmed working rather than assumed: the landscape drawer scrolls and its
last link is reachable and unobstructed; the skip link lands `#main` clear of
the sticky header (`scroll-padding-top` is set); all four text inputs are 16px,
so iOS does not zoom on focus; and no content is hover-only, so touch users
lose nothing when hover is gated.

---

## Addendum — Figma parity pass

Compared the build against the 151 Figma reference exports in `_figma-ref/`,
section by section, measuring the build in the browser rather than eyeballing.

**Two differences found and fixed:**

1. **Reviewer avatars were empty circles.** The testimonial cards rendered a
   blank pale-green disc where the design shows a photo. The four images had
   been built (`avatar-1..4-96` in AVIF/WebP/JPEG) and `data/shared.php` already
   pointed at them — only the markup was missing. Now wired through
   `ep_srcset()` at 96px into a 32px box, so they stay crisp to 3× density.
2. **The services carousel showed no fifth card.** The design runs that track
   past the container's right edge and lets the artboard cut it, leaving a card
   roughly two-thirds visible — the only cue that the row scrolls. The build
   clipped at the container, so four cards sat flush and read as a finished
   static grid. The track now spans the full width and pads its left edge back
   into line with the heading: 227px of peek against the design's 236px, with
   `scroll-padding-inline` so snapping does not drag the first card back to the
   edge. Doing this with `margin-right: calc((100% - 100vw)/2)` instead looks
   identical but makes the document horizontally scrollable, so it was rejected.

**Verified as already matching** (measured, not assumed): container widths
1420/1700; hero copy including the deliberate `Publishing , The`; 7 press logos;
service card 341px against 345px drawn; the Why Us glass cards staggered with
the second indented; all 7 process tabs with their connecting hairlines, the
green STEP label, the 2x2 checklist in its `#EFFAF4` panel and the 45/55 split;
genre and portfolio rails at 240px covers with 5 visible and a 6th peeking;
green rail arrows vs black process arrows; 4 platform cards; 3 story cards;
plans 3-up with the middle raised; FAQ measure exactly 1084px; wizard emoji
chips and 4-segment progress bar; 5-column footer.

**Two differences deliberately not "fixed":**

- **Text on green is ink, not the white the design draws.** White on `#60C489`
  is 2.15:1 and fails AA at every size. See `DECISIONS.md` §14 — it is a
  one-token revert if the client prefers the drawn appearance over the
  accessibility score.
- **The Genres row is under-filled** on three tabs because the placeholder
  catalogue has one book in some genres. Padding it out would mean tagging books
  with genres they do not belong to. Logged as client question 45.

---

## Addendum — Figma parity, remaining pages

Home was covered in the previous addendum. This pass covered Our Books,
Pricing, About, Contact, the service template (one template, ten pages),
Privacy/Terms and the shared footer, measuring the build in the browser against
the reference exports.

**Two differences found and fixed:**

1. **The featured pricing card had no hover state.** `component-140` slide 3
   shows the Standard card filling solid brand green with every accent
   inverting to black — the sparkle circle, the five feature check circles and
   the CTA. Nothing was implemented; hovering did nothing. Now built as drawn.
   Note the text on that hovered green card stays **ink**, not `--ep-on-green`:
   the design draws those labels the same dark as the labels on the white cards
   beside it. White belongs to the flat green panels, not here.
2. **Footer social icons were the wrong way round.** They rendered as ink
   circles with green glyphs; SPEC §B.2 and every page export show pale grey
   circles with dark glyphs. Now `--ep-social-tile: #E4EAE6` with ink glyphs,
   measuring 11.75:1.

**Verified as already matching** (measured, not eyeballed): Our Books — red
`#C41230` arrows, 270×416 covers against 270×418 drawn, five visible, white
overlay captions on a gradient, right-aligned section intro. Pricing — 458px
cards against 459 drawn, the Standard card's double-stroke halo, 56px icon
circles filled/outlined correctly, the `Most Popular` pill in `#2B2A28`,
full-width CTAs, middle card raised. About — image left at 16px radius, and
both button pairs correct (`Get Started` on the Championing section,
`View Services` on Why Authors Choose Us — they differ in the design and the
build has each right), including the paragraph that ends on a comma. Contact —
icon-led contact links, two-column emoji chips, four-segment progress with only
the first green, black full-width Continue. Service template — the header CTA
inverting to a white pill on the photo hero, white nav links, the enquiry card,
intro image left with the right button pair, and the centred green panel with
white dot-chips and a white `Learn Our Story` button. Privacy/Terms — no hero
wash, 76px h1, 28px section headings, disc bullets, full-container measure.

**A trap worth recording.** `footer__01.jpg` shows the footer on saturated
brand green, which looks like a clear miss against the build's pale mint. It is
not: that green is the Figma page canvas showing through a transparent
component frame, `homepage__07.jpg` shows the footer in page context as
`#EAF5EF`, and SPEC §B.2 carries an explicit warning about exactly this. The
build is correct. Do not "fix" it.

---

## Addendum — campaign landing pages (lp1–lp4)

Built from the four design PNGs added to the project root on 11 Aug 2026, and
tested to the same standard as the rest of the site.

### Parity against the designs

Measured at the 1920 artboard, build against export. The build was measured in a
1905px viewport (1920 less the scrollbar), so a ~0.8% horizontal difference is
expected and is not counted as a deviation.

| | design | build | |
|---|---|---|---|
| content column | 1420 | 1420 | ✓ |
| header band | 158 | 157 | ✓ |
| hero form card | 594 × 546 | 594 × 553 | ✓ |
| h1 | 70 / 75, 3 lines | 70 / 75, 3 lines | ✓ |
| hero body | 20 / 27 | 20 / 27 | ✓ |
| cover strip | 1920 × 235 | cropped to 235 | ✓ |
| stats band | 237 tall | 224 | ✓ |
| service card | 459 × 358 | 458 × 356 | ✓ |
| card copy | 16 / 24 | 16 / 24 | ✓ |
| card hairline | 2px `#60C489` | 2px `#60C489` | ✓ |
| h2 | 60 / 70, 2 lines | 60 / 70, 2 lines | ✓ |
| CTA panel | 1420 × 412 | 1420 × 406 | ✓ |
| footer band | 209, `#60C489`, ink | 209, `#60C489`, ink | ✓ |
| **page height** | **2833** | **2796** | **−1.3%** |

Every band lands within 36px of its drawn position across a 2833px page.

Three things were wrong on the first build and were fixed: the h1 was set at the
site's 76px and took four lines instead of three; the mint wash started below
the header instead of behind it; and the services and CTA sections both carried
`.section`, stacking their padding to 200px in a gap the design draws at 101.

### One deliberate deviation

**The stats band columns are evenly spaced; the design's are not.** The drawn
columns are 391px apart starting 45px left of the container, which puts the
row's right edge 99px *past* the container — invisible only because the fourth
label is short. Matching it would mean deliberate horizontal overflow. Equal
columns put the dividers within 63px of the drawn positions. See DECISIONS §16e.

### Responsive

Geometry probes, not screenshots — 4 pages × 12 widths (320, 360, 414, 480, 576,
768, 834, 992, 1024, 1200, 1440, 1920). At each: does the page pan sideways, does
any element escape the viewport, is any control under 24×24, is any text under
12px. **48/48 clean.**

One real defect was found and fixed: at 320px the logo and the header CTA came to
355px against a 320px viewport and the page panned. The CTA now shrinks below
420px, and the header row can wrap as a backstop.

The honeypot `<input>` reports as a 177×21 "small tap target" in a naive probe.
It is not one — `.hp-field` is a 1×1 `overflow:hidden`, `opacity:0`,
`pointer-events:none` wrapper. Same false positive as on the service pages.

### Lighthouse

| | A11y | BP | SEO | Agentic |
|---|---|---|---|---|
| lp1 desktop, navigation | 96 | 100 | 100 | 100 |
| lp1 desktop, snapshot (all revealed) | 96 | 100 | 100 | 100 |
| lp2 desktop | 96 | 100 | 100 | 100 |
| lp3 **mobile** | 96 | 100 | 100 | 100 |
| lp4 desktop | 96 | 100 | 100 | 100 |

The single failing audit on every page is `color-contrast`. The snapshot audit —
taken after scrolling the page so the reveal animation cannot hide anything from
axe, per the caveat in §Verdict — reports **5 nodes on lp1, all of them white on
`#60C489` at 2.15:1**. Every one traces to the `--ep-on-green` decision in
DECISIONS §14/§14a. No new failure class was introduced, and the landing footer,
which is ink on green at 7.4:1, passes.

### Also verified

- 21/21 pages return 200 with zero PHP notices, warnings or fatals
- zero console errors or warnings on all four landing pages
- `includes/lp-*.php`, `data/landing.php` and `tools/assets-lp.php` all return
  **403** over HTTP; `/lp9` still 404s
- form end-to-end: valid submit → 303 → `?form=ok#form-result` on the same
  landing page with the success banner; invalid submit → 303 → `?form=error`
  with all three field errors listed, `aria-invalid` set on the right inputs and
  the typed values handed back. Test data written to `data/submissions.log` and
  `data/rate-limit.json` was deleted afterwards.
- scroll reveal: 6 targets per page, all reach `opacity: 1` with no residual
  transform after a normal scroll; nothing in the hero is tagged, so the LCP
  element paints immediately
- the contact page's own `.cta-green` panel is byte-identical after the rule
  moved to `main.css` — same fill, radius, padding, alignment and ghost button

---

## Addendum — site-wide responsive re-sweep (all 21 pages, landing pages included)

Geometry probes across every page template at 320, 360, 414, 576, 768, 992,
1200 and 1920, plus 300, 1600 and 2560 on a representative subset and landscape
phone (844 × 390) on five. At each combination: does the page pan sideways, does
any element escape the viewport, does any child escape its parent's box, is any
control under 24 × 24, is any text under 12px, is any image squashed, is any
form control under 16px (which makes iOS zoom the page on focus).

**Result: clean, after three fixes.**

### 1. The footer email broke mid-address on every phone — all 17 site pages

`.ep-footer__brand` has a 26px floor, which below 414px is wider than the space
left beside the logo mark. `word-break: break-word` then split the address at
its last dot, so the footer's most prominent line read
**"Contact@Elitepublishing."** over a stranded **"Co"**. Measured at 2 lines at
320, 360 and 390; 1 line only from 414 up.

The address is a single token, so nothing but scaling keeps it whole. Below
414px the type now continues down the same curve — `clamp(17px, 8.7vw - 9.4px,
26px)` — which holds it to one line all the way to 320. Verified: 1 line at 320,
360, 390, 414, 480, 768 and 1920, and unchanged at 414 and above.

This was pre-existing, not introduced by the landing pages.

### 2. The landing service cards orphaned their third card on tablet

`grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr))` fitted
two columns at 768–834 and left card three alone in a half-width slot with an
empty half beside it; at 991 it squeezed all three to 284px.

`auto-fit` is the wrong tool for exactly three items. Replaced with the same
breakpoints `.plans__grid` — the site's other three-card row — already uses: one
centred column capped at 520px below 992, three equal columns above. Measured
520px × 3 stacked at 768/834/991 and 458px × 3 in a row at 1920.

### 3. Four inline links sat exactly on the 24px tap-target minimum

`Terms & Conditions` and `Privacy Policy` in the footer, and the two
`.cta-block__contacts` links, measured **24.00px** tall — their line box and
nothing more. That is WCAG 2.2 SC 2.5.8 to the pixel, so they crossed below it
on sub-pixel rounding; a probe caught one at 23.99.

Grown to 34px with an absolutely positioned `::after` overlay. Padding was tried
first and rejected: `.ep-footer__legal-links` is an `inline-flex` row and
padding on a flex item grows the container's cross size, which moved the footer
legal line 10px. The overlay is out of flow and shifts nothing — confirmed by
re-measuring the parents at 24px and 60px, unchanged.

Verified by hit-testing rather than by measurement: a point 4px above and 4px
below the text now resolves to the link, and 7px above correctly falls through
to the parent. Lighthouse's `target-size` audit passes.

### Two probe artifacts, not defects

Both were flagged by a first-pass probe and are wrong:

- **Rail cells "escape the viewport"** — `.svc-card-cell` on the home page and
  the Our Books carousel cells, at every width. They sit inside an
  `overflow-x: auto` scroller, where extending past the viewport edge is the
  entire mechanism. Only the scroll container has to stay inside, and it does.
  The probe now walks for a scrolling ancestor before reporting.
- **A 177 × 21 "small tap target"** on every page with a form — the honeypot
  `<input>`, inside a 1 × 1 `overflow:hidden`, `opacity:0`, `pointer-events:none`
  wrapper.

### Re-verified after the fixes

21/21 pages return 200 with zero PHP diagnostics. Lighthouse on contact, mobile:
**96 / 100 / 100 / 100**, `target-size` passing, and `color-contrast` the only
failing audit — 7 nodes, all the `--ep-on-green` decision from DECISIONS §14.
Landscape phone (844 × 390): the sticky header is 76px, 19% of the screen; the
landing header is not sticky.

---

## Addendum — client-requested motion and hover states

Six changes from annotated screenshots. What each one found, and what it left.

| Asked for | State before | Now |
|---|---|---|
| Services carousel auto-advances | no autoplay anywhere in the build | `data-autoplay="4500"`, stops on first input |
| Press band moves | static wrapping `<ul>` | `.marquee`, six copies, no seam |
| Review cards: green border on hover | **`.plat-card` had zero hover rules** | 2px green edge, no layout shift |
| Basic/Premium: green border on hover | only `.plan--featured:hover` existed | 2px green edge, halo left to the featured card |
| Hero covers lift on hover | **one flat bitmap — nothing to hover** | rebuilt from 10 individual covers |
| Footer covers lift on hover | same bitmap | same rebuild, `--footer` variant |

### Verified

- **Press band loop**: half-track ≥ viewport at 360 / 768 / 1440 / 1920 / 2560,
  so no gap scrolls through at any width. 7 mastheads exposed to the
  accessibility tree, 35 duplicates `aria-hidden`. No page pan.
- **Autoplay**: measured advancing on a real page (0 → 572px over two ticks);
  the arrow still works after; and 12s after any input the position is unchanged
  — it stays stopped. Confirmed the end-of-rail branch loops back to 0.
  The first attempt to test this in an off-screen iframe reported "never
  advances" — that was the harness, not the feature: an iframe parked at
  `left:-99999px` never intersects, so the IntersectionObserver correctly kept
  it paused.
- **Hover borders**: applied the shipped declarations to a live card and
  measured — `.plat-card` border `#E6E8E7 → #60C489` plus a 1px ring, box size
  unchanged; `.plan:not(.plan--featured)` the same, inside a matching
  `@media (hover: hover)`.
- **Cover lift**: measured a 14px rise at a 124px band (0.11 × band height),
  neighbours unmoved, band height unchanged before and after — the lift is
  `transform` only, so CLS cannot move.
- **Band fills the viewport** at 360 / 768 / 1440 / 1920 / 2560, overflowing
  both edges at each.
- 21/21 pages 200 with zero PHP diagnostics; responsive re-sweep clean over
  9 page templates × 6 widths.
- Home, desktop: navigation audit **100 / 100 / 100 / 100**; snapshot audit
  after revealing everything **97 / 100 / 100 / 100** with `color-contrast` the
  only failure at **39 nodes** — the same count as before this work, so no new
  failure class. `image-alt`, `aria-hidden-focus` and `target-size` all pass.
- **LCP 267 ms, CLS 0.00.** The hero band is the home page's LCP element and is
  now ten images rather than one, so this is the number to watch; it was ~214 ms
  before. Still an order of magnitude inside budget, and measured unthrottled on
  localhost where variance is high.

### Payload

The band's ten covers are the same files the Genres and Portfolio rails already
load, so on the home page and the service pages the rebuild costs roughly
nothing. On pages with no cover rail — the policy pages, contact, the landing
pages — the footer band is **+171 KB** against the single strip it replaced
(268 KB of AVIF covers against 96 KB), lazy-loaded below the fold and shared
across every page after the first. If that matters, a 240px cover variant would
roughly halve it; the band never renders a cover wider than 234px.

Offsetting it, `assets/img/{hero,footer}-band-*` — 15 files, 1.3 MB — are now
unreferenced.

### Not reproduced

One screenshot (`Screenshot 2026-08-12 041016.png`) shows the "Why Us" panel
with its middle glass tile rendering pale mint with dark text, where its two
siblings are the correct translucent white on green.

**This build does not do that**, in any state that could be found:

- All three tiles compute `rgba(255,255,255,.12)` with white text, live.
- Hovering the middle tile — verified with a real pointer, `:hover` matching —
  changes nothing; there is no hover rule matching `.tile-glass` in any
  stylesheet.
- `.tile-glass` has never been modified since the first commit.
- Sampled against the design: `homepage__02.jpg` has all three tiles at
  ~`#68C78F`, which is what the build renders. The screenshot's middle tile is
  `#D0EDDC` — white at ~70% opacity, a value that appears nowhere in the CSS.

The middle tile's offset to the right *is* correct and deliberate
(`.home-why__tiles > li:nth-child(2) { margin-left: 18% }`, drawn that way).
Left open pending a description of what the client is seeing — a browser
extension and a stale cached stylesheet are both consistent with the evidence.

---

## Addendum — form audit, mail transport and the thank-you page

### The forms

Three, all posting to `forms/contact-handler.php`, all carrying a CSRF token and
a honeypot. There is no fourth form anywhere in the build.

| Form | Pages | Required | Extra |
|---|---|---|---|
| `hero-contact` | 10 service pages | name, email, message | `service` |
| `wizard` | home, contact, 10 service pages | name, email | `genre` `stage` `budget` |
| `lp-contact` | lp1–lp4 | name, email, message | `campaign` |

### Tested end to end

Every row below was exercised over HTTP with a real session and a real CSRF
token, not by reading the code.

| Case | Result |
|---|---|
| `hero-contact` valid | 303 → `/thankyou`, logged with `service=Book Editing` |
| `lp-contact` valid | 303 → `/thankyou`, logged with `campaign=lp2` |
| `wizard` valid | 303 → `/thankyou`, logged with `genre=Fiction` |
| invalid (short name, bad email, short message) | 303 → `/lp3?form=error#form-result`, all three errors listed, `aria-invalid` on the right inputs, typed values handed back |
| `email.php` requested directly | **404** — it is a library, not an endpoint |
| `includes/`, `data/` over HTTP | 403, unchanged |
| `/thankyou` in `sitemap.xml` | absent, as intended |

### Mail header injection

`ep_mail_name()` and `ep_header_safe()` were fed hostile input directly:

| Input | Output |
|---|---|
| `Bob\r\nBcc: victim@example.com` | `"Bob  Bcc: victim@example.com"` — inert inside the quoted name |
| `Bob\nCc: victim@example.com` | `"Bob Cc: victim@example.com"` |
| `Bob\tX-Injected: yes` | `"Bob X-Injected: yes"` |
| `Bob"><script>…` | quote stripped, cannot close the quoted string |
| `Ünïcödé Näme` | `=?UTF-8?B?…?=`, correctly MIME-encoded |

No input produced a CR or LF in a header, so no new header can be started.

### Lighthouse

`/thankyou` desktop: **Accessibility 100 · Best Practices 100 · Agentic 100 ·
SEO 63**.

The SEO score is correct and must not be "fixed". The only failing audit is
`is-crawlable`, which fails because the page is deliberately `noindex` — a
thank-you page in search results tells a stranger their message was sent when it
was not. Every other SEO audit passes.

22/22 pages return 200 with zero PHP diagnostics.

### Still not verifiable here

**Delivery.** XAMPP has no MTA, so `EP_ENV` resolves to development locally and
`ep_send_mail()` records to `data/submissions.log` and reports success — which
is what lets the whole flow, redirect included, be tested. Whether SiteGround's
Exim actually accepts and delivers the message can only be confirmed on the
host. The deployment checklist is in README under **Deploying to SiteGround**;
the first item on it, creating the `EP_MAIL_FROM` mailbox, is the one that
silently swallows bounces if skipped.

Test submissions written during this work were deleted, along with
`data/rate-limit.json`.
