# Elite Publishing

Marketing site for a book publishing company — 17 pages built to a Figma design.

**Stack:** PHP 8.2 · HTML5 · CSS3 (Grid/Flexbox, no framework) · vanilla JS
**No build step.** Clone it, point Apache at it, and it runs.

---

## Running it locally

Requires XAMPP (or any Apache + PHP 8.2 with the GD extension).

1. Place the folder in your web root, e.g. `F:\xampp\htdocs\Elite Publishing`.
2. Start Apache.
3. Open <http://localhost/Elite%20Publishing/>.

`mod_rewrite`, `mod_deflate`, `mod_expires` and `mod_headers` should be enabled —
`.htaccess` degrades gracefully without them, but you lose pretty URLs,
compression and cache headers.

No database. No `composer install`. No `npm install`.

---

## Layout

```
index.php                  Home
about.php  our-books.php  pricing.php  contact.php
privacy-policy.php  terms-conditions.php  404.php
service.php                one template -> all 10 /services/<slug> pages
sitemap.php                generated XML, served at /sitemap.xml

includes/
  config.php               site constants, nav model, service registry
  head.php  header.php  footer.php
  functions.php            esc() url() asset() ep_icon() ep_data() ep_srcset() …
  components/              8 shared page sections — see below
data/                      all copy and content, as plain PHP arrays
forms/contact-handler.php  CSRF + honeypot + validation + rate limit + mail
assets/  css/ js/ img/ fonts/
tools/                     CLI image builders (not web-facing)
docs/                      spec, decisions, contract, QA and perf reports
```

### The eight shared sections

Nearly every page is assembled from these rather than hand-written. They read
their own copy from `data/` and take no content parameters:

`press-band` · `services-carousel` · `journey` · `author-stories` ·
`testimonials` · `plans` · `faq` · `cta-wizard` · (+ `book-band`)

They cover 8 of the home page's 16 sections and 9 of each service page's 15. If
you are about to write markup for one of these, don't — include the partial.

```php
<?php require __DIR__ . '/includes/components/faq.php'; ?>
```

### Content lives in `data/`, not in pages

`data/shared.php`, `services.php`, `books.php`, `pricing.php`. Editing copy means
editing a data file, not hunting through templates. Anything marked
`'draft' => true` is copy the build wrote because the design did not contain it —
`grep "'draft' => true" data/` finds all of it.

---

## Documentation

Read in this order:

| File | What it is |
|---|---|
| `docs/SPEC.md` | The design spec: tokens, per-page breakdown, all copy, asset manifest |
| `docs/DECISIONS.md` | The 14 judgement calls and why — **read before changing anything that looks wrong** |
| `docs/CONTRACT.md` | Data shapes, component signatures, image filenames, file ownership |
| `docs/DEV-GUIDE.md` | How to build a page here |
| `docs/QA-REPORT.md` | Audit findings |
| `docs/PERF-REPORT.md` | Lighthouse results and payload, with honest caveats |
| `docs/CLIENT-QUESTIONS.md` | **Open items needing client answers before launch** |

### Motion

Blocks fade and rise as they scroll into view, plus small hover touches (the ↗
glyph leans into its direction, book covers lift). All of it is deliberately
defensive:

- **Nothing above the fold is animated**, so the LCP element paints immediately.
- **Only `opacity` and `transform`** are animated, so motion can never move
  layout — CLS stays at 0.00, measured.
- **The hiding CSS is gated on a class that JavaScript adds**, and only after it
  has confirmed `IntersectionObserver` support and checked the motion
  preference. A stylesheet that loads without its script cannot blank the page.
- **`prefers-reduced-motion: reduce` disables it entirely** — the script returns
  before tagging anything.
- **Nothing that is not currently rendered is tagged.** A `display:none`
  element — an inactive process tab, a closed panel — never intersects, so it
  would never be un-hidden, and would then appear at opacity 0 *permanently*
  the moment its tab was opened.
- **Anything already scrolled past is revealed immediately**, via a very large
  top `rootMargin`. Without it a fast scroll (scrollbar drag, `End`,
  find-in-page) can jump a section between two frames; the observer never sees
  it intersect and it stays transparent. An element is only hidden while it is
  still *below* the viewport.
- **Only the outermost block in any nest animates.** A child fading in inside a
  fading parent reads as a flicker and stacks the two delays.
- **Rails animate as one block; their cells do not** — hiding a
  horizontally-scrolled child fights the scroll. The testimonial marquee is
  excluded outright.
- A 4-second failsafe reveals anything still hidden inside the viewport, and
  printing forces everything visible. Neither should ever have work to do.

None of the measurement happens during boot: reading `getBoundingClientRect`
there forces the browser to compute the whole page's first layout
synchronously, on the main thread, before anything is on screen. Deferring the
reveal, the rail measurements and the carousel button state past the first
paint removed **114 ms** of forced reflow and took home-page LCP from 308 ms to
214 ms. Deferring is only safe because no reveal target is above the fold.

To change the feel, `--dur-reveal` and `--ease-out` are in `tokens.css`. To turn
it off completely, delete the `initReveal()` call in `assets/js/main.js`.

### Responsive

Breakpoints are at 480 / 576 / 768 / 992 / 1200 / 1520. Content sits in a
1420px measure (1700px for the navbar); below that everything is fluid, and
type scales with `clamp()` rather than stepping at breakpoints.

Verified with geometry probes — not screenshots — at 320, 360, 414, 479, 576,
768, 834, 991, 1024, 1199, 1440, 1920 and 2560, plus landscape phone
(844 × 390), across every page template. At each size the checks were: does the
page pan sideways, does any element escape the viewport or its own parent, do
any two siblings overlap, is any control under 24 × 24, is any image distorted,
is any text under 12px.

Three things that look wrong in a measurement but are not:

- **`documentElement.scrollWidth` reads ~2050px on a 390px phone.** That is the
  off-canvas nav drawer, which is `visibility: hidden` when closed. `body`
  stays at viewport width, the page cannot pan sideways, and the drawer's 16
  links are not focusable while closed — all verified.
- **Carousel arrows overhang their rail by 20px.** They are absolutely
  positioned and centred on the rail's edge by design, and stay inside the
  viewport.
- **Service card descriptions are cut off.** A deliberate
  `-webkit-line-clamp: 3` so cards in a carousel share a height; the full text
  is on the service page.

**Hover is gated on `@media (hover: hover)`.** All 38 hover rules sit inside
that guard. Without it, a touchscreen tap leaves the hover state stuck — most
visibly on the service cards, where the whole card inverts to green and would
stay green until the visitor tapped somewhere else. Touch devices report
`hover: none`, so none of the 33 guards apply there; on a mouse pointer all 33
apply and the styling is unchanged. If you add a hover style, put it inside a
guard.

### Two things that look like bugs but are not

1. **The design's copy bugs are reproduced on purpose** — `romance , christian ,`
   with spaces before the commas, `Elite Publishing , The`, the About paragraph
   that ends mid-sentence on a comma. All twelve are listed in `DECISIONS.md`
   §11 with pre-written corrections. Do not "fix" them casually.
2. **Text on green panels is white, as drawn, and it fails contrast.** White on
   `#60C489` measures 2.15:1 where WCAG AA needs 4.5:1. This is not an
   oversight: the build shipped dark ink (7.4:1) until the design owner chose
   fidelity to the Figma instead. `DECISIONS.md` §14a has the measurements, and
   `--ep-on-green` in `tokens.css` reverses it in one line.

---

## Status

- 17/17 pages render with zero PHP notices and zero console errors
- Lighthouse **100** for Best Practices, SEO and Agentic Browsing on every page
  template, desktop and mobile
- Lighthouse **Accessibility 97** — the only failing audit is `color-contrast`,
  entirely from the white-on-green decision above (17 nodes at 2.15:1 and
  1.95:1). Everything else passes.
  A navigation-mode audit still prints 100, and **that number is misleading**:
  axe skips elements at `opacity: 0`, and the scroll-reveal leaves the green
  sections hidden while the audit runs. The 97 is a snapshot audit taken after
  scrolling the page to reveal everything — measure it that way, or you are
  measuring the animation.
- CLS **0.00**; home page ships ~241 KB over 13 requests

**Before production launch** — see `docs/CLIENT-QUESTIONS.md`:

- **Rights clearance** for the real book covers and press mastheads used in the
  design. This is a legal decision, not a build one.
- **SMTP credentials.** The contact handler is complete and header-injection
  safe, but XAMPP has no mailer, so delivery is untested. In development it
  appends to `data/submissions.log`; in production it calls `mail()`.
- Placeholder content: the book catalogue, author-story names and reviewer
  avatars are all placeholders in the design itself.

`data/submissions.log` and `data/rate-limit.json` hold submitted personal data
and are gitignored. `.htaccess` also blocks `data/`, `includes/`, `docs/` and
`_figma-ref/` over HTTP — verified returning 403.
