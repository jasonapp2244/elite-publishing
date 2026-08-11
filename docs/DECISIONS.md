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
