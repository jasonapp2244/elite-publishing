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
