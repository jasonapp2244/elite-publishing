# Dev 3 report — content pages, contact handler, 404

Scope: `about.php`, `our-books.php`, `contact.php`, `privacy-policy.php`,
`terms-conditions.php`, `404.php`, `forms/contact-handler.php`,
`assets/css/p-core.css`, `assets/css/p-contact.css`. Nothing outside that list
was edited.

---

## 1. What was built

| File | Contents |
|---|---|
| `our-books.php` | Page hero (§D.4) → "Our Published Books Collection": h2 left, right-aligned paragraph, 5-up cover carousel with overhanging **red** arrows (`--ep-carousel-red`, DECISIONS §10) and captions on a dark scrim → `services-carousel.php` → `faq.php` → `cta-wizard.php` → footer. Covers come from `ep_data('books')`. |
| `about.php` | Page hero (§D.5) → "Championing Independent Voices Worldwide" (2-col, image left) → "Our Core Values" (centred h2, 3 cards, 48px pale-green icon tiles, `badge-check` / `shield` / `eye`) → `journey.php` → "Why Authors Choose Us" (pale rounded band, split header, image left + numbered `01`–`05` list) → `faq.php` → `cta-wizard.php` → footer. |
| `contact.php` | Page hero (§D.7) → submission banner (see §3) → `cta-wizard.php` → the green CTA band unique to this page (§B.11 form 2) → footer. |
| `privacy-policy.php` | h1 + 9 h2/paragraph/list blocks (§D.8), full 1420px measure, plain `--ep-bg`, **no mint wash**, no hero image, no CTA. |
| `terms-conditions.php` | Same shape, 10 blocks (§D.9). |
| `404.php` | Not in the design. Navbar → `404` label, h1, one-paragraph apology, "Back to Home" + "Explore Our Services" → footer. Sends a real `404` status with `http_response_code()` before any output. |
| `forms/contact-handler.php` | CSRF, honeypot, server-side validation, session + IP rate limit, `mail()` in production / `data/submissions.log` in development, POST-redirect-GET. |
| `assets/css/p-core.css` | `.page-hero`, Our Books collection + carousel, About sections, `.doc` policy styles, `.page-404`. |
| `assets/css/p-contact.css` | `.cta-green` band, `.contact-flash`. |

Copy is transcribed character-for-character from SPEC §D.4/§D.5/§D.7/§D.8/§D.9.
The three copy bugs I was told to preserve are preserved and verified in the
rendered DOM:

* About hero paragraph ends mid-sentence on `…to design and publishing,`
* About body P1 reads `Our Story Elite Publishing was founded on a simple principle:`
* T&C "Intellectual Property" bullet 2 keeps its **leading space**. HTML would
  normally collapse it, so `.doc__list li` is `white-space: pre-wrap` and every
  `<li>` is emitted on one source line. Measured in the browser: the second
  bullet's first glyph starts 4.7px right of the first bullet's — the space
  renders, exactly as in `terms-conditions__01.jpg`.

Designed line breaks go through `ep_lines()`; there is no hardcoded `<br>`, no
raw hex/size/radius, and no `data-bs-*` anywhere in my files.

---

## 2. Form tests — verbatim results

All four cases run against `http://localhost/Elite%20Publishing/forms/contact-handler.php`
with `Invoke-WebRequest -Method POST`, a real session cookie, and a `Referer` of
`contact.php`. "alert" is what `contact.php` then rendered.

```
=== TEST 1: valid wizard submission ===
[valid]    HTTP 303  Location: /Elite%20Publishing/contact.php?form=ok#form-result
           alert: ep-alert--ok  id="form-result" role="status"
           text : Thank you — your message has been sent. Our editorial team will be in touch shortly.

=== TEST 2: invalid input (blank name, bad email, bad phone, 2-char message) ===
[invalid]  HTTP 303  Location: /Elite%20Publishing/contact.php?form=error#form-result
           alert: ep-alert--err  id="form-result" role="alert"
           text : We could not send that just yet — please check the highlighted fields.
                  Please tell us your name.
                  That email address does not look right.
                  That phone number does not look right.
                  Please write a little more so we can help properly.

=== TEST 3: honeypot filled (website=http://spam.example) ===
[honeypot] HTTP 303  Location: /Elite%20Publishing/contact.php?form=ok#form-result
           alert: ep-alert--ok  id="form-result" role="status"
           text : Thank you — your message has been sent. We will be in touch shortly.
           (nothing delivered; the only trace is a development-only log line
            {"outcome":"honeypot"} with no submitted content)

=== TEST 4: bad CSRF token (64 hex chars, not the session token) ===
[bad-csrf] HTTP 303  Location: /Elite%20Publishing/contact.php?form=error#form-result
           alert: ep-alert--err  id="form-result" role="alert"
           text : Your session expired before the form was sent. Please try again.
           (rejected before any field is read and before the rate limit is spent)
```

Two extra checks:

```
GET on the handler        -> HTTP 303  Location: /Elite%20Publishing/contact.php
                             (nobody is ever left on a bare handler URL)

Rate limit (5 / 10 min)   -> 6th post in the window:
                             "That is a lot of messages in a short time. Please wait a
                              few minutes and try again."
                             Counted per session AND per IP; the IP file stores
                             sha256(ip), never the address itself.
```

Log lines written in development (`data/submissions.log`, verbatim, one JSON object per line):

```
{"time":"2026-08-11T19:07:01+02:00","outcome":"accepted","form":"wizard","name":"Ada Lovelace","email":"ada@example.com","phone":"+1 555 0100","genre":"Fiction","stage":"I have an outline or partial draft","budget":"$20,000+","message":"I have a finished 80,000 word manuscript and need help publishing it.","page":"/Elite%20Publishing/contact.php","ip":"::1","ua":"…"}
{"time":"2026-08-11T19:07:21+02:00","outcome":"honeypot","form":"wizard","ip":"::1"}
```

Web-exposure check — `.htaccess` really does return 403:

```
/data/submissions.log  -> HTTP 403
/data/rate-limit.json  -> HTTP 403
/data/shared.php       -> HTTP 403
/docs/SPEC.md          -> HTTP 403
```

`data/submissions.log` still holds the six lines my tests wrote; delete it
before launch. `data/rate-limit.json` was removed after testing so QA is not
blocked — it regenerates on the next submission. **Both are runtime artifacts,
not content: please add them to `.gitignore`** (Lead — `.gitignore` is not mine).

### What could not be verified locally

**Mail delivery. XAMPP has no MTA**, and `EP_ENV` is `development`, so
`mail()` is never reached in this environment. The production branch is written
(`From: Elite Publishing <no-reply@<host>>`, `Reply-To:` the visitor,
`text/plain; charset=UTF-8`, all header values passed through
`ep_header_safe()` which strips CR/LF/TAB, so header injection is not possible)
but it is **untested and I am not claiming it sends**. It needs an SMTP account
and a real send on the production host before launch — this is already recorded
as a `CLIENT` item in DECISIONS ("Contact form"). If the client wants delivery
guarantees, PHPMailer over authenticated SMTP is the right fix and `mail()`
should be swapped out then.

---

## 3. Changes I need in files I do not own

### 3.1 `includes/components/cta-wizard.php` — errors do not survive the redirect (Lead)

The handler stores everything the form needs to re-render itself:

```php
$_SESSION['ep_form'] = [
  'status'  => 'ok' | 'error',
  'message' => 'human sentence',
  'errors'  => ['full_name' => '…', 'email' => '…', 'phone' => '…', 'message' => '…'],
  'old'     => ['full_name'=>…, 'email'=>…, 'phone'=>…, 'message'=>…, 'genre'=>…, 'stage'=>…, 'budget'=>…],
  'time'    => 1786467000,   // ignore anything older than 300s
];
```

`contact.php` reads and consumes it and renders `.ep-alert--ok` / `--err` with
the per-field list. **But the wizard partial writes no `value=` attributes and
no `aria-invalid`**, so after an error the user's typing is lost — and on the
ten service pages nothing renders the flash at all. Three small edits are
needed in the partial (yours, not mine):

1. read `$old = $_SESSION['ep_form']['old'] ?? []` at the top;
2. `value="<?= esc($old[$g['name']] ?? '') ?>"` on the inputs, the same string as
   the textarea's content, and `checked` on the radio whose label matches
   `$old['genre'|'stage'|'budget']`;
3. `aria-invalid="true"` plus `aria-describedby` on any field present in
   `['errors']`, and render the banner at the top of the card so it also works
   on the service pages.

Until that lands, error re-population works on the copy of the banner in
`contact.php` only. **This is the one Definition-of-Done item I could not
finish inside my own file boundary.**

### 3.2 `.container-ep` is 96px narrower than the design (Lead)

`.container-ep` is `max-width: 1420px` with `padding-inline: clamp(20px,4vw,48px)`
*inside* that width, so at 1920 the content measure is **1324px, not 1420px**.
The artboards put content at x=250…1670 — exactly 1420 (confirmed independently
by the Assets agent's crop boxes, which start at x=250). Every page is ~7% too
narrow. Fix is one line — `max-width: calc(var(--ep-container) + 2 * <the same
padding>)`, or `box-sizing: content-box` on the container.

Consequence in my pages: the About h2 `Championing Independent\nVoices Worldwide`
no longer fit its designed break in the measured 636/700 column, so
`.about-story` uses `424fr / 500fr` instead of the measured `424fr / 467fr`.
Revert that one line to `467fr` when the container is fixed.

### 3.3 Two components worth promoting into `main.css` (Lead)

* **`.page-hero`** — left-aligned h1 + paragraph on the mint wash, identical on
  Our Books, About, Contact **and Dev 2's Pricing page** (SPEC §C.4 §2). I built
  it in `p-core.css`; Dev 2 will otherwise write it a second time. Values:
  `padding-block: clamp(44px, 9.9vw, 189px) clamp(40px, 6.1vw, 117px)`,
  intro `max-width: 46ch`, colour `--ep-body`.
* **`.ep-btn__badge`** — the small white rounded tile around the ↗ inside filled
  green buttons ("Get Started", "View Services" on About). It is a button
  variant, not a page style.

Neither is urgent; both are duplication risks.

### 3.4 Smaller notes

* `img/favicon.svg` and `img/apple-touch-icon.png` still 404 on every page
  (already CONTRACT §5) — the only network errors my pages produce.
* `--ep-carousel-red` has no hover token, so the red arrows darken with
  `filter: brightness(.9)` rather than inventing a second red. Add
  `--ep-carousel-red-hover` if you would rather it were a colour.
* For **Dev 2**: the service-page hero form must post `_token` (`ep_csrf_field()`),
  an empty `website` honeypot input, and `full_name` / `email` / `phone` /
  `message`. `name` is accepted as an alias for `full_name`. Omit `_form` (or
  send anything other than `wizard`) and the handler treats `message` as
  **required**, which is right for a "write your message here" card. `email` is
  always required and validated; `message` is bounded at 10–4000 characters.

---

## 4. Deviations from the design, and why

1. **Book captions.** The export captions every cover `Elena Hartwell / The Last
   Cartographer`. Per DECISIONS §6 the page renders the real per-book metadata
   from `data/books.php` instead. Intentional, one-file revert.
2. **Cover crop.** The design's card is 180×278 (0.647); the extracted covers
   are 1004×1424 (0.705). `object-fit: cover` on the design aspect trims ~4% off
   each side of a cover. The alternative — matching the asset ratio — would make
   the row shorter than the export. I matched the export.
3. **White body copy on green** in the contact band, and white captions on the
   cover scrim. Design draws them white; 16px white on `#60C489` is ~1.9:1 and
   **fails WCAG AA**, contradicting DEV-GUIDE §7. Built as drawn ("as per Figma",
   and `main.css` already sets white body text on the open FAQ panel), logged
   here. `--ep-ink` would pass if the client accepts the change.
4. **Green band fill.** The export's band looks very slightly graded left to
   right; built as a flat `--ep-green` since flat fills are the sampled truth.
5. **Policy heading/body sizes.** The design's policy h2 measures ~30px and its
   body ~20px at 1920 — no token is exactly 30px, so `.doc__h` clamps up to
   `--fs-h4` (28px) and `.doc__p` up to `--fs-lg` (20px), body colour
   `--ep-body`. No new token invented; say the word if you want `--fs-h4-doc`.
6. **404 copy** is mine — the page is not in the design. Headline: "This Page Has
   Not Been Written Yet".

Nothing else in `_figma-ref/our-books__NN`, `about-our-company__NN`,
`conatct-us__NN`, `privacy-policy__NN` or `terms-conditions__NN` is unmatched.

---

## 5. Verification

* All six pages return 200 (404.php correctly returns **404**, both directly and
  via the `.htaccess` rewrite of an unknown path) with **zero** PHP
  notices/warnings in the output and **zero** console errors/warnings in Chrome.
* No horizontal overflow on any of the six pages at **1440 / 1024 / 768 / 390**
  (measured `documentElement.scrollWidth` vs `clientWidth`, plus a per-element
  bounds sweep excluding the deliberate horizontal scrollers). 1920 checked
  against the slices by screenshot.
* Per page: exactly one `<h1>`, no skipped heading levels, every `<img>` has
  `width`/`height`/`alt`, every form control has a label, every icon-only button
  has an `aria-label`, exactly one `fetchpriority="high"` image.
* Carousel arrows are `<button aria-label="Previous books|Next books">` and hide
  below 768px in favour of swiping, matching the shared carousel behaviour.
