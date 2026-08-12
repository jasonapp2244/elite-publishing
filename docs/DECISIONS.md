# Decisions — resolving SPEC §G

The spec left 13 open questions. Waiting on client answers would block Phase 3
entirely, so each one is decided here. **These decisions are binding for the
build.** Anything marked `CLIENT` is a real question that still needs a real
answer before launch — it is recorded in `docs/CLIENT-QUESTIONS.md` and the
build ships a defensible default in the meantime.

Guiding rule, from the brief: **"as per Figma."** Where the design and good
practice disagree, the design wins and the discrepancy is logged rather than
silently corrected.

---

## 1. `--fs-h1` — 76px or 80px?

**Decided: 76 / 88.** The PM's cap-height method reproduces the Figma-confirmed
`h2 = 60/70` exactly, which validates the method; the same method yields 76/88
for h1. `tokens.css` already carries 76/88. No change needed.

## 2. Font family

**Decided: Urbanist**, self-hosted, weights 400/500/600/700, already in
`assets/fonts/`. It is a geometric sans with a double-storey `a` and matches the
export's skeleton closely. The true family name is not recoverable from a raster
export.
`CLIENT` — confirm the licensed family. If it is Gilroy / Sofia Pro / Circular,
the substitution is recorded in the QA report and swapping it is a one-file
change (`tokens.css` `@font-face` + `--ep-font`).

## 3. FAQ answers 2–6

**Decided: write them.** An accordion with five empty panels is a broken page,
and item 1 is the only one rendered open in the export so the others were never
designed. New copy is written in the brand's voice, kept to 2–3 sentences to
match item 1's length, and every drafted answer is listed verbatim in
`docs/CLIENT-QUESTIONS.md` for sign-off.
Drafted copy lives in `data/shared.php` under `'faq'` with `'draft' => true`.

## 4. Publishing Process steps 02–07

**Decided: write them.** Same reasoning — six of seven tabs would otherwise be
dead. Titles come from the tab labels, which *are* in the design (`Planning`,
`Writing`, `Editing`, `Design`, `Publishing`, `Marketing`). Body + 4-item
checklist drafted per step, flagged `'draft' => true`, listed for sign-off.

## 5. Service card descriptions are truncated with `…`

**Decided: supply full sentences, clamp to 3 lines in CSS.** The truncation is a
*design* feature (`-webkit-line-clamp: 3`), so the card looks identical to the
export while the full sentence is in the DOM for SEO and for screen readers. The
visible fragment is preserved exactly as drawn; only the continuation is new.
Continuations flagged `'draft' => true`.

## 6. Book catalogue metadata

**Decided: real titles, placeholder authors are wrong to ship.** The design
repeats `Elena Hartwell / The Last Cartographer` on every card, which is
obviously placeholder. The SPEC (§E.1) already identified the nine real covers.
`data/books.php` carries the identified title + author per cover so the page
reads coherently, and every row is marked `'placeholder' => true` so the client
can swap the catalogue in one file.
`CLIENT` — supply the real catalogue, and see decision 13 on cover rights.

## 7. Wizard step 3, option 4 — `$20,000 — $[UNREADABLE]`

**Decided: `$20,000+`.** The three bands below it are closed ranges that tile
without gaps; the fourth is the top band, and an open-ended top band is the
conventional and safest reading of a clipped export.
`CLIENT` — confirm.

## 8. Footer `Portfolio` link has no page

**Decided: → `/our-books`.** It is the only page that is a portfolio of work.
Recorded in `ep_footer_nav()`.

## 9. Ghostwriting + Proofreading missing from the Services carousel and footer

**Decided: build as drawn — 8 cards, 7 footer links.** Both services keep their
dropdown entries and their full pages, so nothing is unreachable. Inventing two
extra cards would mean inventing two icons and two descriptions that no one
designed.
`CLIENT` — almost certainly an oversight; two cards can be added in `data/shared.php`
the moment copy exists.

## 10. Red carousel arrows (`#C41230`) on Our Books only

**Decided: build as drawn.** `--ep-carousel-red` already exists in `tokens.css`
and is scoped to that one page. It is a one-line revert if it was a mistake.
`CLIENT` — intentional?

## 11. Copy bugs in the design

**Decided: reproduce verbatim, log every one.** "As per Figma" is the explicit
instruction, and silently rewriting client copy is how wrong text ships
unnoticed. Each is reproduced exactly and listed in `docs/CLIENT-QUESTIONS.md`
with the corrected text pre-written, so accepting a fix is a copy-paste:

| # | Location | As drawn |
|---|---|---|
| 1 | FAQ answer 1 | `romance , christian ,` — space *before* the comma, twice |
| 2 | Home h1 | `Elite Publishing , The` — space before the comma |
| 3 | About hero paragraph | ends mid-sentence on a comma |
| 4 | About intro P1 | `Our Story Elite Publishing was founded…` — missing heading break |
| 5 | T&C, Intellectual Property | leading space on `  We reserve the right…` |
| 6 | Book Cover Design | "What We Offer" list is Books Publishing's list |
| 7 | Book Illustration | "End-To-End" paragraph is about **websites** |
| 8 | Creative Content Writing | whole page is about **script writing** |
| 9 | Blog Article Writing | whole page is about **script writing** |
| 10 | Testimonial names | trailing comma — `Marcus Vance,` |
| 11 | Testimonial card 4 | duplicates card 1 |
| 12 | Author-story cards | all three say `Clara Wen / Everything Remembered` |

**Exception — one bug is not reproduced:** the design's misspelled *frame* name
`Conatct Us`. Frame names are Figma-internal and never render. The page is
`contact.php` at `/contact`, per DEV-GUIDE §6.

## 12. Navbar sticky behaviour, hover states, CTA target

- **Sticky: yes.** A static export cannot express scroll behaviour, so this is a
  product decision, not a design contradiction. On a 9,448px page a nav that
  scrolls away is a usability failure. `.ep-header` is already
  `position: sticky` with an `.is-stuck` shadow. Deviation logged.
- **Hover states:** as proposed in SPEC §B.3 — filled buttons darken ~6%,
  outline buttons fill with their border colour and flip the label to white, all
  lift 1px. Already implemented in `main.css`.
- **`Publish Your Book` → `/contact`.** It is the only conversion destination on
  the site.

## 13. Rights clearance — real book covers and press mastheads

**Decided: build with them, flag hard.** They are in the design, they are what
the client asked to be built, and this is a local development build. Shipping
*Project Hail Mary* and a New York Times masthead to production without a
licence is a legal exposure the client has to clear — it is not a decision the
build can make.
`CLIENT` — **blocking for production launch, not for this build.** Raised at the
top of `docs/CLIENT-QUESTIONS.md`.

---

## 14. Text colour on brand green — added in Phase 4, after measurement

Both Dev 1 and Dev 2 flagged this and correctly refused to diverge on their own.
Measured with Lighthouse/axe, brand green fails contrast in **two** directions:

| Case | Measured | WCAG AA needs |
|---|---|---|
| `#60C489` **as text** on white — `Learn More`, `STEP 01`, the `01`–`05` markers | **2.15:1** | 4.5:1 |
| White **as text** on `#60C489` — Why Us panel, open FAQ item, plan CTAs, genre tabs, Contact's green band | **2.15:1** | 4.5:1 (and it fails the 3:1 large-text bar too) |

So the design's white-on-green could not ship as drawn at any text size.

**Two ways out, and why the second was chosen:**

1. *Darken the green surface* until white passes — that requires roughly
   `#2F7D52`. It keeps white text but replaces the brand colour with a
   noticeably darker green across the site's most recognisable panels.
2. *Keep the green exactly, darken the text* — brand colour is untouched;
   text that the design draws in white becomes ink.

**Decided: option 2.** The mint green is the brand; the text colour is not. Two
tokens were added in `tokens.css`:

- `--ep-green-text: #2C7A50` — green text on light surfaces (**5.2:1**)
- `--ep-on-green: #2B2A28` — text on green surfaces (**7.4:1**, AAA)

This is **the one place in the build where the rendered design deliberately
differs from the Figma**, and it is what took Accessibility from 93 to 100 on
every page.

**Reverting is one line.** Set `--ep-on-green: #FFFFFF` in `tokens.css` and the
drawn appearance returns, at the cost of the accessibility score and WCAG AA
compliance. `CLIENT` — this is a real trade-off and yours to make. It is listed
in `docs/CLIENT-QUESTIONS.md`.

### 14a. Reversed — white on green, by the design owner's decision

The trade-off above was put to the design owner, who chose fidelity to the
Figma. `--ep-on-green` is now `#FFFFFF`. **Only that token changed**; the green
surface is still exactly `#60C489`, so the brand colour was never in question.

One rule had to change with it. The outlined-white button on a green surface
inverts on hover — fill and label swap. It took both colours from
`--ep-on-green`, which was safe while that token was ink but paints a white
label on a white fill once the token is white, erasing the text. The hover now
names `--ep-white` / `--ep-ink` literally.

**What this costs, measured after the change:**

| | Result |
|---|---|
| White on `#60C489` (panel, FAQ open, genre chips, CTA band) | **2.15:1** — AA needs 4.5:1, large text 3:1 |
| White on the glass tiles' composited `#73CB97` | **1.95:1** |
| Lighthouse Accessibility, everything visible | **97**, 17 failing nodes, all `color-contrast` |

Note the navigation-mode Lighthouse run still reports **100**, and that number
is not evidence. axe skips elements at `opacity: 0`, and the scroll-reveal
leaves most of the page hidden while the audit runs, so the green sections are
never examined. The 97 above comes from a snapshot audit taken after scrolling
the whole page to reveal it. Anyone re-checking this should do the same, or
they will measure the animation rather than the contrast.

### 14b. Reversed the other direction too — brand green as text

The design owner then asked for the drawn green on light surfaces as well, so
`--ep-green-text` went from `#2C7A50` (5.2:1) to `#60C489` — the brand green
itself. That covers `Learn More`, `STEP 01`, `STEP n OF 4`, the wizard chips,
the plan outline buttons, the numbered markers and the 404 code.

All eleven of its uses were checked first for a green-on-green collision; there
is none — every one sits on white or on the pale `--ep-green-tint-soft`.

| | Result |
|---|---|
| Brand green on white | **2.15:1** |
| Brand green on the pale `#EFFAF4` chips | **2.01:1** |
| Failing nodes, both decisions together | **39** (27 at 2.15, 6 at 2.01, 6 at 1.95) |
| Lighthouse Accessibility, everything visible | **97** — still only `color-contrast` |

The score does not fall below 97 as more nodes fail, because `color-contrast` is
a single binary audit: it is already failed. Do not read a steady 97 as "no
further harm" — the node count is the honest measure, and it went 17 → 39.

The token is kept distinct from `--ep-green` even though the two values are now
identical. That is deliberate: it marks every place where green is carrying
*text*, so legibility can be restored by editing one line without touching a
single fill. Set it back to `#2C7A50` to do that.

---

## 15. Service hero photography — replaced, not cropped

**The problem.** The ten `<slug>-hero-*` images were cut from the `y 0–866` band
of each service page export. That band is the *composited design*: the navbar,
the `<h1>`, the body paragraph, the two buttons and the opaque "Start Your Book
Today" form card are all flattened into the pixels. The real HTML hero then
renders on top, giving a doubled headline and a ghost form on all ten pages. A
photograph cannot be separated from text flattened over it, and no clean hero
source exists anywhere in the export set — every Frame and Component file was
checked.

**Decided (client, Phase 4): rebuild each hero from that service's clean
end-to-end photograph.** Design fidelity on *which photograph appears* is traded
for a hero that is not visibly broken.

**Consequences, stated plainly:**

- Each service page now shows **the same photograph twice** — once full-bleed in
  the hero, once in section 7. Known and accepted.
- SPEC §E.1's ten hero-background descriptions ("woman reading on a tartan
  blanket", etc.) **no longer describe the site**. They describe the discarded
  crops. §E.1's *end-to-end* descriptions are the accurate ones.
- **The heroes are soft.** The e2e photo's true extent is `x 1035..1669,
  y 2753..3346` — **635×594 on every one of the ten exports**. The layout slot
  *is* the photo's full extent, so there are no extra native pixels to recover.
  1920 is a **3.02× enlargement**; 1280 is 2.02×. Structural edges (jawlines,
  glasses rims, shelf lines) survive and read as shallow depth of field; fine
  texture does not — skin goes waxy and background lettering becomes mush.
  Soft, but far better than a headline printed into the image.
- File sizes improved anyway: 1920 AVIF is now **44.7–78.1 KB** (was up to
  110.8 KB), well under the 180 KB budget. Quality was deliberately *not* raised
  — at 3× upscale a higher setting just encodes interpolation more faithfully;
  the bytes went into a pre-upscale sharpen instead, which buys real apparent
  detail.

`CLIENT` — **the real fix is bare image fills exported from Figma.** That yields
native-resolution heroes and restores the intended photographs. It needs either
a Figma export from you (~13 assets) or a Figma seat that isn't rate-limited.
Until then this is the best available result.

---

## Additional standing decisions

**Missing FAQ/process/description copy is drafted, never invented silently.**
Every drafted string carries `'draft' => true` in its data file and is listed in
`docs/CLIENT-QUESTIONS.md`. A single grep for `'draft' => true` finds all of it.

**No design fidelity is traded for a Lighthouse point.** The hero band, the
full-bleed photography and the 8–9k-pixel pages stay. Performance is won with
AVIF, correct `width`/`height`, critical CSS, deferred everything and cache
headers — not by deleting the design. Real per-page numbers are reported in
`docs/PERF-REPORT.md`; see `PROJECT-PLAN.md` §5 on why mobile 100 is not
promised.

**Contact form:** built complete and correct — CSRF, honeypot, server-side
validation, rate limit. XAMPP has no mailer, so delivery is unverifiable
locally; the handler logs to `data/submissions.log` in development and calls
`mail()` in production. `CLIENT` — SMTP credentials needed before launch.

---

## 16. Campaign landing pages (lp1–lp4) — added after the Figma parity pass

Four designs arrived in the project root as PNGs. They are a different animal
from the seventeen pages that came before: no navigation, a lead-capture form in
the hero, and a single conversion goal. The decisions that shaped the build:

**16a. They are one template, not four pages.** The four designs are identical
in structure — only copy, three card icons and one button label change. So there
is one `includes/lp-page.php`, one `assets/css/p-lp.css`, and `data/landing.php`
holds everything that varies. `lp1.php`–`lp4.php` are 27 lines each and contain
no markup. A layout fix applies to all four or to none, which is the point.

**16b. The names are the client's.** `lp1`–`lp4` was specified, so the URLs are
`/lp1`…`/lp4` rather than something descriptive like
`/childrens-book-publishing`. Worth revisiting if these are ever meant to rank —
the slug is a ranking signal and `lp3` is not one. Raised as question 46.

**16c. No navigation, deliberately.** The designs draw a logo and one CTA, and
nothing else. That is what a landing page is for: the only ways forward are the
form and the buttons. `includes/lp-header.php` is a separate file from
`includes/header.php` rather than a variant of it, because "the site header
minus almost all of it" is not a variant.

**16d. The landing footer and the CTA panel disagree about text on green, and
both are right.** This is the one thing here most likely to be "fixed" by
mistake. §14 established `--ep-on-green: #FFFFFF` because the design draws white
on the green panels. The landing footer is also solid `#60C489` — but the design
draws *ink* on it, and sampling the exports confirms near-black glyphs and a
black logo lockup. So `.lp-footer` uses `--ep-ink` and does not read the token.

Measured: ink on `#60C489` is **7.4:1** and passes AA. The footer is the only
green surface on the site that does. Pointing it at `--ep-on-green` to make the
two "consistent" would take a passing surface and break it, to match a drawing
it does not match.

**16e. The stats band is evenly spaced; the design's is not.** The drawn columns
are 391px apart with the row starting 45px to the LEFT of the container, which
puts its right edge 99px past the container's — it only looks contained because
the fourth label is short. Reproducing that would mean deliberately overflowing
the container, which is the exact bug class the responsive pass spent a session
removing. The columns are equal and fill the container instead. The dividers
land within 63px of the drawn positions; the band reads identically. Noted in
`QA-REPORT.md`.

**16f. The landing h1 is 70px, not the site's 76px.** Not a guess: setting the
three drawn lines of lp1's headline in Urbanist 600 at −2.5% tracking reproduces
their measured widths (661 / 722 / 733px) to within 0.5% at 70px, and runs 8%
wide at 76px. The 6px matters — at 76px the headline takes four lines instead of
the drawn three. Leading is 75px (1.07), also measured, against the site's 88.

**16g. No hardcoded `<br>` in the landing h1.** The rest of the site stores
designed line breaks as `\n` and renders them with `ep_lines()`. Here the copy
column is 760px wide and breaks the headline at the drawn points on its own, so
the breaks are left to the browser — a hard `<br>` would survive down to a 360px
phone and strand single words. The CTA headings, which are short and centred,
do keep their drawn breaks.

**16h. The cover strip is cropped in CSS, not re-cut.** The designs use the same
artwork as the home page hero band but 50px shorter (1920×235 against 1920×285),
cropped off the top. That is `aspect-ratio` plus `object-fit: cover` on the
landing pages, rather than a second near-identical 1920px image in three formats
— nine more files to keep in step for 50px.

**16i. `logo-ink` is a new asset, built not cropped.** The landing footer needs
the lockup as a single ink silhouette; `logo.png` has a brand-green "PUBLISHING"
that vanishes on the green band, and `logo-light.png` is a white knockout. It is
generated by `tools/assets-lp.php` from `logo.png` by inking every opaque pixel.
The obvious guess — that the mark's navy E/3 shapes should become transparent —
is wrong and eats half the mark: it is an isometric book whose right FACE is
navy, and the E/3 slits are already transparent.

**16j. `.cta-green` moved from `p-contact.css` to `main.css`.** The landing pages
close with the same green panel as the contact page. It was never really
page-scoped — `main.css` already styled its ghost button and `main.js` already
listed it as a reveal target — and a landing page loading `p-contact.css` to
borrow a shared component would have been misleading.

---

## 17. Motion and hover states requested from screenshots

Six changes asked for by the client as annotated screenshots. Four were CSS;
two changed structure.

**17a. The press band is now a slider, and it reuses the marquee.** The
mastheads were a static wrapping row. Rather than write a second scroller, the
band adopts `.marquee` — same keyframes, same pause-on-hover and
`:focus-within`, same `[data-marquee-toggle]` handler, which already scoped
itself with `btn.closest('.marquee')` and so supports any number per page.

The track holds **six** copies of the list, not the marquee's usual two, and
that number is load-bearing. The animation translates by −50%, so the second
half must cover the viewport alone or a bare gap scrolls through. One copy of
seven text mastheads measures 1017px at 1920, against a 2560px widest supported
viewport; three copies per half clears it. The count must stay even or the seam
lands mid-copy.

**17b. The services carousel advances itself, and stops for good on the first
real input.** Opt-in per rail with `data-autoplay` on the `[data-scroller]`
wrapper, so the book rails stay manual. It pauses off-screen, on a hidden tab
and on hover, and never starts under `prefers-reduced-motion`.

WCAG 2.2.2 wants a mechanism to pause anything moving for more than five
seconds. **The arrows are that mechanism**: a click on either ends the motion
for the session, as does a wheel, drag, key or focus landing on a card link.
That is a deliberate reading of the criterion rather than a strict one — the
strict answer is a visible pause button, which the two marquees carry but which
this section's design does not draw. If an audit demands it, the control already
exists and is one include away.

**17c. Hovered cards take a green edge — and the featured plan keeps its
halo.** `.plat-card` and `.plan:not(.plan--featured)` colour their 1px border
green and paint a 1px ring outside it with `box-shadow`, which reads as a 2px
edge without growing the box; going to `border-width: 2px` would pull the
contents in by a pixel and jitter the row.

`:not(.plan--featured)` is the point of the rule, not a detail. The featured
card's border-plus-halo is what marks it "Most Popular"; if hovering either
neighbour reproduced that treatment, the one card meant to stand out would stop
standing out.

**17d. The book band was rebuilt from individual covers.** This is the
structural one. Both bands were a single pre-composed bitmap, and a bitmap has
no per-cover element to hover — there was nothing to attach the requested lift
to. They are now ten `<li>`s built from the same `data/books.php` covers every
other rail on the site uses, tilted and overlapped in CSS.

Stated plainly: **this is not pixel-identical to the artwork it replaces.** The
tilts, drops and a 202px cover pitch were measured off `hero-band-1920.jpg` and
tuned side by side until the two read the same at 1920, but a CSS
reconstruction of hand-placed art never matches exactly. The client accepted
that trade before the work started.

What it buys besides the hover: the covers scale with the viewport instead of
being resampled from a fixed raster, the band fills the screen at 2560 where a
capped image would not, and pages that already show a cover rail get the band
for free from cache.

It stays **decorative** — `aria-hidden` on the container, empty `alt` on every
cover, exactly the semantics the flat image had. The band repeats titles already
listed on Our Books; announcing them a third time would be noise, and the lift
carries no information.

`--cover-w` is `max(104px, 12.2vw)` with **no upper cap** on purpose. A clamp
ceiling would stop the covers growing past ~1920 and leave a bare strip down the
right of a 2560 monitor — the one thing a full-bleed band must never do.

The old strips (`assets/img/{hero,footer}-band-*`, 15 files, 1.3MB) are now
unreferenced. They are left on disk because `tools/build-assets.php` still
produces them; drop both together when convenient.

---

## 18. Street address — added, not replaced

The client supplied **261 Madison Ave., New York, NY 10016** as an address to
replace. There was no address to replace: `EP_ADDRESS` existed in
`includes/config.php` but was `''` and unused, the design draws only the website
and the email in its contact block, and a search of the whole build for street
or ZIP patterns found nothing. So it is new content, and it went in where an
address is useful rather than nowhere.

**One source of truth, in `config.php`, not `data/shared.php`.** The rest of that
contact block's copy lives in the data file, and putting the address there would
have been the consistent-looking choice. It is a business fact, not page copy,
and duplicating it across a data file and a schema block is exactly how the two
drift apart — a page showing one postal code while the structured data publishes
another is worse than publishing no address at all. `EP_ADDRESS` is the display
string; `EP_ADDRESS_PARTS` is the same address split for schema.org.

**Where it shows:** a third row in the contact block, under the website and the
email, with the `map-pin` glyph that was already in the icon set. That block
appears on the home page, the contact page and all ten service pages. It is
plain text, not a link — a maps URL is a destination nobody asked for, and it
would send a visitor off-site from the one block whose job is to keep them.

**The landing pages do not show it.** They draw their own minimal footer and no
contact block, and adding one would work against what a landing page is for.
They still publish it in structured data, like every other page.

**It is drawn as a third row the design does not have.** The design's block has
two. An address the client supplied and nobody can see is not worth having, so
the row was added rather than the address filed away.

Setting `EP_ADDRESS` back to `''` removes the row and the `PostalAddress` from
the markup together — both check it — so there is no way to leave half an
address behind.

---

## 19. Mail split out, thank-you page, and environment detection

Asked for: check the forms, add an `email.php` that sends, redirect to a
`thankyou.php`, deploying to SiteGround.

**19a. There are three forms, and they all post to one handler.** The audit:

| Form | Where | Extra fields |
|---|---|---|
| `hero-contact` | the ten service pages | `service` |
| `wizard` | home, contact, every service page | `genre` `stage` `budget` |
| `lp-contact` | lp1–lp4 | `campaign` |

All three carry a CSRF token and a honeypot and share
`forms/contact-handler.php`. No fourth form exists anywhere in the build.

**19b. `email.php` sends; it is not the form action.** The obvious reading of
the request was to point the forms at `email.php`. That would have skipped the
CSRF check, the honeypot, the validation and the rate limit in one move, and the
address would be harvested within days. So the split is by concern, not by
request path: the handler decides whether a submission deserves to be sent,
`email.php` owns the message, the headers and the transport. Changing how mail
goes out — SMTP, a different From, a new body — is now one file, with no risk of
disturbing a line of validation.

`ep_header_safe()` and `ep_log_submission()` moved with it, so there is one copy
of each. A second copy in the handler would have been a fatal redeclaration
anyway, which is a good property for this kind of split to have.

**19c. Success goes to `/thankyou`; failure goes back to the form.** Both are
303s, so a refresh can never resend. A dedicated success URL is what an
analytics or ad platform can count as a conversion — a green banner on the page
you were already on is invisible to both. Failure still returns to the form with
the values the visitor typed, because sending someone to a thank-you page for a
message that was refused is a lie, and making them retype a long enquiry is how
a real lead is lost.

The page carries **no query string**. It needs no state, and a URL is the last
place a name or an email address should end up — they get pasted into support
tickets and logged by analytics. It follows that the page can be opened
directly, which is fine: it confirms nothing it was not told.

It is `noindex`. **Lighthouse scores it SEO 63 for exactly that reason** — the
single failing audit is `is-crawlable`, and it is correct that it fails. A
thank-you page in search results tells a stranger their message was sent when it
was not. Do not "fix" this.

**19d. `EP_ENV` is detected, not hardcoded — and this was a live landmine.** It
was the literal string `'development'`. Deployed to SiteGround unchanged, every
enquiry would have been quietly appended to `data/submissions.log` and no mail
would ever have been sent, with nothing in any log to say so. It now resolves to
production for anything that is not plainly a local host, and a `*.local.php`
or a pre-definition can force it either way.

**19e. SiteGround specifics that are not optional.** `From:` is a mailbox on the
site's own domain and the envelope sender (`-f`) matches it. Sending "as" the
visitor's Gmail address is the single most common reason these mails land in
spam: the domain's SPF record does not authorise SiteGround to send as
gmail.com, so the message fails SPF and usually DMARC too. The visitor goes in
`Reply-To`, where hitting Reply still reaches them.

The mailbox in `EP_MAIL_FROM` has to exist in Site Tools → Email → Accounts, or
bounces go nowhere. If deliverability is still poor after SPF and DKIM are
published, the answer is authenticated SMTP, which `mail()` cannot do — swap the
one `@mail()` call in `ep_send_mail()` for PHPMailer and nothing else changes.

**19f. A copy of every accepted submission is still written to disk**, even when
mail succeeds. A lead is never lost to a mail problem. That file holds personal
data: it is gitignored and `.htaccess` denies `data/` over HTTP. Both need
checking after the deploy — `.htaccess` rules are the thing that quietly does
not survive a move between hosts.

---

## 20. Enquiries are delivered to info@, not to the address on the page

`EP_MAIL_TO` was `EP_EMAIL`. They are now separate, and deliberately different:

| | value | what it is |
|---|---|---|
| `EP_EMAIL` | `Contact@Elitepublishing.Co` | the address SHOWN — footer, contact block, thank-you page. Drawn in the design, reproduced verbatim (DECISIONS §11). |
| `EP_MAIL_TO` | `info@elitepublishing.co` | the inbox form submissions are POSTED to. |

So the site invites people to write to contact@ while the forms arrive at info@.
That is a perfectly normal arrangement and nothing in the build depends on the
two matching — but it looks like an inconsistency, and the next person to read
it will be tempted to "fix" one to match the other. They are separate on
purpose: one is copy from the design, the other is operational routing.

**Both mailboxes have to exist**, since the page invites mail to contact@.

`EP_MAIL_FROM` is unchanged and still resolves to `no-reply@elitepublishing.co`.
That is the sending identity, a third distinct thing from the other two, and it
must exist on the domain or bounces disappear. `email.php` carries a documented
one-line switch to send as `EP_MAIL_TO` instead, for anyone who would rather not
create a second mailbox — with the trade written out rather than presented as a
shortcut.

---

## 21. Pre-deployment cleanup — what was deleted and what deliberately was not

Asked to remove unnecessary files before deploying. The answer split three ways,
because "unnecessary on the server" and "unnecessary to the project" are not the
same thing.

**21a. Deleted: 18 files, 1.3 MB, unreferenced by anything the site serves.**
Found by matching every file under `assets/` against the runtime source only —
root `*.php`, `includes/`, `forms/`, `data/`, `assets/css`, `assets/js`.
Deliberately excluding `tools/` and `*.md` from that scan is what made the audit
useful: a first pass that included them reported one dead file, because the
build script and the README still named the band images. A file mentioned only
by a build script is still dead weight on a server.

| Files | Why dead |
|---|---|
| `hero-band-*` `footer-band-*` (15) | the cover band became ten CSS-positioned covers in §17d |
| `logo-light.png/.webp` (2) | built for a dark header variant that was never made; nothing has ever referenced it |
| `assets/fonts/_google.css` (1) | left over from before the fonts were self-hosted |

The jobs that produced them were removed from `tools/build-assets.php` at the
same time, so the tool and the assets agree. Leaving a generator for files
nobody wants is how they come back.

**21b. Moved, not deleted: the four landing-page design PNGs.** They sat in the
web root and were served at **200** — 5.7 MB of the client's design comps,
downloadable by anyone who guessed the filename. They are now in
`_figma-ref/landing-pages/`, which `.htaccess` denies. The `Design:` comment at
the top of each `lp*.php` was updated to match.

**21c. Also exposed and now blocked: `Elite Publishing -fgma-images/`.** 107 MB
of raw Figma exports, also served at 200. `.htaccess` now denies it and
`tools/`, which was reachable too — every file in it is CLI-guarded, so it was
not exploitable, but it listed the build pipeline to anyone who looked.

**21d. NOT deleted: `docs/`, `tools/`, `_figma-ref/`, the `*.md` files.** These
are 266 MB of the project and none of it belongs on the server — but deleting
them would destroy the design source of truth, the record of every decision, and
the scripts that regenerate every image. "Do not upload" and "delete" are
different instructions, and only one of them is reversible.

The README now carries a **What not to upload** list instead. The site is
**15.7 MB** of a 282 MB project.
