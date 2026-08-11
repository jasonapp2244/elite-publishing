# Elite Publishing — Website Build Plan

**Source of truth:** Figma `Elite Publishing` → page **Final**
`https://www.figma.com/design/xCK2rrka4ihZEpVZJ9crYz/Elite-Publishing?node-id=119-12684`
**Design exports:** `Elite Publishing -fgma-images/` (43 PNGs, 1920px wide)
**Reference slices:** `_figma-ref/` (auto-generated, readable strips — safe to delete at the end)

**Stack:** HTML5 · CSS3 · Bootstrap 5.3 (local, purged) · Vanilla JS (no jQuery) · PHP 8.2
**Local env:** XAMPP — PHP 8.2.12, Apache running, GD enabled
**Test URL:** `http://localhost/Elite%20Publishing/`

---

## 1. Scope — 17 pages

### Core (7)
| Page | File | Figma export | Height |
|---|---|---|---|
| Home | `index.php` | Homepage.png | 9448px |
| Our Books | `our-books.php` | Our Books.png | 4300px |
| About Our Company | `about.php` | About Our Company.png | 5510px |
| Pricing | `pricing.php` | Pricing.png | 3774px |
| Contact Us | `contact.php` | Conatct Us.png | 2632px |
| Privacy Policy | `privacy-policy.php` | Privacy policy.png | 2593px |
| Terms & Conditions | `terms-conditions.php` | Terms & Conditions.png | 2593px |

### Services (10) — all exports are **identically 1920×8776**
This confirms one shared template with swapped content. Build **one** `services/_template` +
10 content data files, not 10 hand-built pages.

`ghostwriting` · `book-editing` · `books-publishing` · `book-cover-design` · `book-marketing`
`book-illustration` · `proofreading` · `creative-content-writing` · `blog-article-writing` · `audio-book-production`

### Shared components (from Component 95–159 exports)
Navbar (+ Services dropdown) · Footer · Service card · Book card · Pricing card
Testimonial · Stats strip · FAQ accordion · CTA band · Logo/press strip · Form controls

---

## 2. Architecture

```
Elite Publishing/
├── index.php                  # Home
├── about.php  our-books.php  pricing.php  contact.php
├── privacy-policy.php  terms-conditions.php
├── service.php                # single controller for all 10 service pages
├── includes/
│   ├── config.php             # site constants, base URL, nav model
│   ├── head.php               # <head>: meta, OG, JSON-LD, critical CSS inline
│   ├── header.php             # navbar
│   ├── footer.php             # footer + deferred JS
│   ├── functions.php          # esc(), asset(), url(), img() srcset helper
│   └── components/            # card.php, faq.php, cta.php, testimonial.php …
├── data/
│   ├── services.php           # 10 service definitions (copy, icons, FAQs)
│   ├── books.php              # book catalogue
│   └── pricing.php            # plan tiers
├── forms/
│   └── contact-handler.php    # CSRF + validation + honeypot + rate-limit + mail
├── assets/
│   ├── css/  tokens.css  bootstrap.min.css (purged)  main.css
│   ├── js/   main.js (defer)
│   ├── img/  *.webp + *.avif + fallbacks, responsive sizes
│   └── fonts/ *.woff2 (self-hosted, preloaded)
└── docs/  SPEC.md  QA-REPORT.md  PERF-REPORT.md
```

**Routing:** `.htaccess` rewrites `/services/ghostwriting` → `service.php?s=ghostwriting`
so URLs stay clean and SEO-friendly.

---

## 3. Team & assignments

| Role | Agent | Owns |
|---|---|---|
| **PM** | `pm` | Reads all 43 exports → writes `docs/SPEC.md`: design tokens, full copy, per-page section breakdown, component inventory, asset manifest. Single source of truth. Then reviews dev output against Figma. |
| **Lead (me)** | — | Foundation: folder skeleton, tokens.css, purged Bootstrap, PHP partials, image pipeline, helper functions. Built **before** devs start so all three build on one base. |
| **Dev 1** | `dev-core` | `index.php` (Home — the largest, most component-dense page) + `our-books.php` + `about.php` |
| **Dev 2** | `dev-services` | `service.php` template + `data/services.php` (all 10 services) + `pricing.php` |
| **Dev 3** | `dev-forms` | `contact.php` + `forms/contact-handler.php` + `privacy-policy.php` + `terms-conditions.php` + global `main.js` (nav, carousels, accordions) + responsive pass |
| **QA** | `qa` | Figma-vs-build visual diff on all 17 pages, 4 breakpoints, link/form/a11y audit → `docs/QA-REPORT.md` |

Devs run **in parallel** on non-overlapping files. Shared files (`includes/`, `assets/css/main.css`)
are owned by me to avoid write collisions — devs request additions via their report.

---

## 4. Phases

**Phase 0 — Recon** ✅ done
Design mapped, 17 pages identified, exports sliced, env verified.

**Phase 1 — Spec** (PM)
`docs/SPEC.md` with exact tokens and full copy extracted from every export.

**Phase 2 — Foundation** (me)
Skeleton, tokens, purged Bootstrap, partials, image pipeline (PNG → WebP/AVIF + srcset),
self-hosted fonts. Homepage hero built as the reference implementation devs pattern-match.

**Phase 3 — Build** (3 devs, parallel)
All 17 pages to pixel-parity with Figma at 1920px, then 1440 / 1024 / 768 / 390.

**Phase 4 — Performance** (me)
Target Lighthouse 100. Inline critical CSS, defer everything else, AVIF/WebP with correct
`width`/`height` to kill CLS, `fetchpriority=high` on LCP image, preload fonts with
`font-display: swap`, purge unused Bootstrap, gzip/brotli + far-future cache headers via `.htaccess`.

**Phase 5 — QA** (QA agent)
Full audit → `docs/QA-REPORT.md`. Devs fix. Re-audit until clean.

**Phase 6 — Chrome self-testing** (me)
Drive Chrome DevTools MCP against `localhost`: run Lighthouse per page, capture real
screenshots, compare against the Figma export side by side, check console errors and
network waterfall. Fix and re-run until green.

---

## 5. Definition of done

- [ ] All 17 pages built, matching Figma at 1920px
- [ ] Responsive and correct at 1440 / 1024 / 768 / 390
- [ ] Contact form validates server-side, blocks spam, sends mail
- [ ] Zero console errors, zero broken links, zero 404 assets
- [ ] Semantic HTML, keyboard-navigable, WCAG AA contrast, all images have alt text
- [ ] SEO: unique title/description per page, OG tags, JSON-LD, sitemap.xml, robots.txt
- [ ] Lighthouse run on every page and recorded in `docs/PERF-REPORT.md`

### On the "100% PageSpeed" target — read this

I'm targeting 100 and will engineer for it. Realistically:

- **Accessibility / Best Practices / SEO → 100 is achievable and I'll hold the build to it.**
- **Performance → 100 on desktop is very achievable. On mobile it is genuinely hard**
  for a design like this: the hero is a full-bleed photographic band and the pages are
  8–9k pixels tall with heavy imagery. Expect **95–100 mobile**; I'll report the real
  numbers per page rather than claim a round 100.

Anything that would fake the score — stripping the hero imagery, lazy-loading the LCP
element, gating content behind interaction — I won't do, because it wins the number and
loses the design. If you'd rather trade specific visual fidelity for points, that's your
call and I'll implement whatever you choose.

---

## 6. Open items

1. **Font identity** — the Figma type is a geometric sans (not Poppins; double-storey `a`).
   PM to confirm the exact family from the Figma properties panel. If it's a paid font
   (Gilroy / Sofia Pro / Cera), I'll self-host the closest free match (Figtree / Outfit /
   Plus Jakarta Sans) and flag the substitution.
2. **Contact form delivery** — XAMPP has no local mailer. Form will be built complete and
   correct; a real SMTP host/credentials are needed before mail actually sends in production.
3. **Book catalogue** — currently hardcoded in `data/books.php`. Say the word if you want
   MySQL-backed books/blog with an admin panel; that's a separate, larger phase.
