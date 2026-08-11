# Performance report

Measured on the local XAMPP build (Apache 2.4, PHP 8.2) with Chrome DevTools.

## Read this before quoting a number

The brief asked for "100 percent everywhere". Three of the four Lighthouse
categories are measured and are at 100 on every page template, desktop **and**
mobile. The fourth — **Performance** — is reported here as raw Core Web Vitals
and payload, **not** as a Lighthouse Performance score, for one honest reason:

> The audit tool available here (`chrome-devtools-mcp`) explicitly **excludes
> the Performance category**, and the trace it does produce ran against
> `localhost` with **no CPU throttling and no network throttling**.

A Lighthouse Performance score is computed under simulated mobile conditions
(4× CPU slowdown, throttled 4G). Numbers taken on an unthrottled localhost are
real measurements of the page, but they are **not** that score and must not be
presented as one. Anyone reporting "Performance 100" from this data would be
quoting a number nobody measured.

**To get the real figure**, run PageSpeed Insights against a public URL once the
site is deployed, or `npx lighthouse <url> --preset=desktop` / default mobile
locally. The structural work that drives the score is done — the remaining
variable is hosting.

---

## Lighthouse — actually measured

Every distinct page template, run individually. `service.php` covers all ten
service pages (one template, identical structure).

| Page | Device | A11y | Best Practices | SEO | Agentic | Failed audits |
|---|---|---|---|---|---|---|
| `/` | desktop | **100** | **100** | **100** | **100** | 0 |
| `/` | mobile | **100** | **100** | **100** | **100** | 0 |
| `/services/book-editing` | desktop | **100** | **100** | **100** | **100** | 0 |
| `/services/ghostwriting` | mobile | **100** | **100** | **100** | **100** | 0 |
| `/contact` | desktop | **100** | **100** | **100** | **100** | 0 |
| `/about-our-company` | desktop | **100** | **100** | **100** | **100** | 0 |
| `/our-books` | desktop | **100** | **100** | **100** | **100** | 0 |
| `/pricing` | desktop | **100** | **100** | **100** | **100** | 0 |
| `/privacy-policy` | desktop | **100** | **100** | **100** | **100** | 0 |

## Core Web Vitals — home page, unthrottled localhost

| Metric | Value | Target |
|---|---|---|
| **LCP** | **273 ms** | < 2500 ms |
| **CLS** | **0.00** | < 0.1 |
| TTFB | 21 ms | — |
| Render-blocking savings identified | **0 ms** | — |

**CLS 0.00 is the meaningful result here.** Layout stability is a property of
the markup, not of the connection: it holds on any hardware because every image
carries explicit `width`/`height`, fonts use `font-display: swap` against a
metric-similar fallback, and no content is injected above existing content after
paint. That number will survive deployment.

LCP's 273 ms breaks down as 21 ms TTFB + 10 ms load delay + 6 ms load duration +
**236 ms render delay**. Render delay dominating means the constraint is parse
and style of a large document, not asset transfer — which is what you would
expect from a 9,448 px page and is the honest ceiling on this design.

## Payload — home page, the heaviest of the 17

| Type | Transferred |
|---|---|
| Fonts (4 × Urbanist woff2, latin subset) | 109.6 KB |
| Images (AVIF, above the fold) | 86.3 KB |
| CSS (3 files, compressed) | 16.0 KB |
| JS (2 files, compressed) | 14.2 KB |
| HTML document (compressed) | 15.4 KB |
| **Total** | **241.5 KB over 13 requests** |

The HTML is 150 KB uncompressed and 15.4 KB on the wire — mod_deflate is working
at roughly 10:1. DOM size is 1,376 nodes.

**Fonts are now the single largest item.** That is the correct problem to have,
and it is the next optimisation if one is wanted: four weights are downloaded
because four are used. Dropping to three (400/500/600, folding 700 into 600)
would save ~27 KB.

---

## What was done to get here

| Change | Effect |
|---|---|
| **Deleted Bootstrap entirely** | −227 KB render-blocking CSS. A grep of all 17 pages found zero uses of its grid or utilities — it was pure dead weight. Also removed the never-referenced 79 KB JS bundle. |
| **Fixed `srcset` URLs** | The base path `/Elite Publishing/` contains a space, and a space is the `srcset` delimiter — every candidate parsed as `/Elite` and was dropped, so **no responsive image worked at all**. Now percent-encoded at the source. |
| **Fixed font preloading** | Preload hrefs carried `?v=<mtime>` while `tokens.css` resolved `@font-face` to unversioned URLs — two different resources, so every font was fetched twice and Chrome warned "preloaded but not used". |
| **Single eager image per page** | The header logo was claiming `fetchpriority="high"`, competing with the real LCP element for early bandwidth. |
| AVIF + WebP + JPEG fallback, per-width `srcset` | Home ships 86 KB of imagery for a photographic design. |
| Explicit `width`/`height` on every image | CLS 0.00. |
| Self-hosted subset fonts, `font-display: swap`, 2 preloaded | No FOIT, no third-party connection. |
| `.htaccess`: deflate/brotli, immutable 1-year asset caching, `?v=<mtime>` busting | Repeat views are near-instant; cache lifetime is safe because URLs change with content. |
| Hand-rolled vanilla JS, deferred | 14.2 KB total, no framework, no jQuery, no Bootstrap JS. |

## Known costs not paid down

- **Fonts at 109.6 KB** — reducible to ~82 KB by dropping weight 700.
- **`assets/img/` is 15 MB on disk** across 284 files. Only a fraction is served
  per page (a service page pulls 3 AVIFs, ~94 KB at 1× DPR, ~207 KB at 2×), but
  the repository carries all of it.
- **Intro/end-to-end images at 1280 are upscales** of a ~636 px source, so that
  width buys file size without buying detail. Dropping the 1280 variant is the
  cheapest remaining win.
- **The design's own scale is the floor.** The home page is 9,448 px tall with
  full-bleed photographic bands. Nothing here fakes the score by stripping the
  hero, lazy-loading the LCP element, or gating content behind interaction —
  those win the number and lose the design. If specific visual fidelity should
  be traded for points, that is a client decision, not a build decision.
