# Assets report — photography, brand mark

**Owner:** Assets agent · **Script:** `tools/assets-photos.php` · **Outputs:** `assets/img/**`

Everything in CONTRACT §5 exists. 284 files, no gaps. The script is re-runnable
from scratch — the numbers below come from a run made after deleting every file
it owns.

```
F:\xampp\php\php.exe tools\assets-photos.php              # everything (~9 min, AVIF is slow)
F:\xampp\php\php.exe tools\assets-photos.php books        # one group
F:\xampp\php\php.exe tools\assets-photos.php svc:ghostwriting
```

Groups: `books`, `stories`, `avatars`, `about`, `svc`, `icons`.

---

## 1. Headline numbers

| | |
|---|---|
| Files written | **284** (94 name+width combinations × AVIF/WebP/JPG, + `favicon.svg`, + `apple-touch-icon.png`) |
| **Total weight added to `assets/img/`** | **15.00 MB** (15,358 KB) |
| `assets/img/` after this work (incl. the Lead's logos/bands/OG card) | 306 files, 16.42 MB → **16.51 MB after the §9 hero rebuild** |
| Missing from CONTRACT §5 | none |
| Largest single AVIF | `svc/creative-content-writing-intro-1280.avif`, 138.6 KB |
| Largest hero AVIF at 1920 | ~~`svc/book-illustration-hero-1920.avif`, 110.8 KB~~ → **superseded by §9: `svc/book-marketing-hero-1920.avif`, 78.1 KB** (range 44.7–78.1 KB) |

> **The ten service heroes were rebuilt after this report was first written — see
> §9.** Sections 2, 3, 5 and 8 still describe the original `y 0–866` hero crop;
> §9 is what is on disk.

Per-format share of the total: **AVIF 4.11 MB · WebP 3.81 MB · JPG 7.07 MB.**
The JPGs are more than double the AVIFs and are pure fallback — only ever served
to a browser that supports neither modern format, which in practice is nothing
the site targets. If the performance phase wants the *deployed artefact* smaller
(as opposed to the delivered bytes, which this does not change), the JPG tier is
the obvious thing to prune.

**Delivered weight is the number that matters, and it is much smaller.**
`ep_srcset()` only emits a `<source>` for widths that exist on disk, and the
browser downloads exactly one file per `<picture>`. Measured across the ten
service pages, the three photographs on a page cost:

| | AVIF served | |
|---|---|---|
| 1× DPR (hero-1280 + intro-640 + e2e-640) | 75–118 KB | avg **94 KB** |
| 2× DPR (hero-1920 + intro-1280 + e2e-1280) | 150–262 KB | avg **207 KB** |

Home page adds the 3 story thumbnails (~30–37 KB each at 960) and 4 avatars
(~2 KB each); Our Books adds ten covers (~20–48 KB each at 840).

---

## 2. Coordinate corrections to SPEC §E.1

**SPEC's y values are not what the brief assumed.** The task brief said to
multiply `_figma-ref` slice coordinates by 1.5 and add the slice offset. In fact
SPEC §E.1's y values are *already in artboard pixels*, they are just measured
from the top of their slice rather than the top of the artboard. The slices are
1280px-wide, 933px-tall strips, so:

```
artboard_y = spec_y + (sliceNumber - 1) × 933 × 1.5      // = + (K-1) × 1399.5
```

Applying the brief's ×1.5 on top of that would have put every service intro
photo ~750px too low — a slab of the Why-Us band instead of a portrait. This is
confirmed by three independent checks: SPEC's `__02 y ~110-700` for the intro
resolves to artboard 1509–2099 and the measured photo is 1508–2102; SPEC's
`about-our-company__01 y ~755-1345` is already exact; SPEC's `homepage__05
y 0-380` resolves to the measured story-card bottom at 5978.

Every box below was then tightened against the real 1920px export by scanning
for the photo's edges against the page background, so they are measured, not
derived.

| SPEC §E.1 entry | SPEC says | Actually | Note |
|---|---|---|---|
| Service **end-to-end** photo | `__03 y ~0–545` → artboard 2799–3616 | **artboard 2753–3347** (`[1034, 2753, 636, 594]`) | **Wrong in SPEC.** The photo starts 45px *above* the slice-3 boundary (it is cut by the slice, so it looks like it starts at 0), and it is 594 tall, not 817. SPEC's range would have included ~220px of empty page below the photo. |
| `about-why` (A2) | `__02 y ~1330 → __03 y ~410` → artboard 2729–3209 | **artboard 2705–3215** (`[250, 2705, 665, 510]`) | Start 24px early; end correct. Minor. |
| Service **intro** photo | `__02 y ~110–700` | `[250, 1508, 636, 594]` | Correct. |
| Service **hero** | `y 0–866` | `[0, 0, 1920, 866]` | Correct, and 866 is exactly where the tilted book-cover band begins — a clean cut. |
| `about-story` (A1) | `__01 y ~755–1345` | `[250, 755, 636, 594]` | Correct. |
| Story thumbnails | `homepage__05 y 0–380` | `[250\|730\|1210, 5603, 460, 380]` | Correct. |
| Avatars | `homepage__05 y ~690–720` | `[50\|527\|1004\|1481, 6283, 45, 45]` | Correct. |

Two source-filename traps, both handled: the export is **`book Editing.png`**
(lowercase `b`) and **`Audio Book Production.png`** — note there is also a
different, smaller `Audiobook Production.png` sitting in the project root, which
is *not* the artboard. The misspelled `Conatct Us.png` was not needed.

---

## 3. What I verified visually vs. inferred

The rule I worked to: crop → write → open the JPG → confirm it matches SPEC's
written description of that photo.

**Verified by eye (36 of 49 images):**

- **All 10 service heroes.** Rendered as a 2-up contact sheet and checked
  against SPEC's descriptions one by one — e.g. ghostwriting really is the woman
  on the tartan blanket, creative-content-writing really is the green wingback
  by the fire, book-illustration really is the magazine rack. All ten crop to
  the same bounds with no book-band bleed at the bottom.
- **All 20 service intro + end-to-end photos.** Same method, 4-up contact sheet.
  Every one matches its SPEC row, including the two distinctive ones that make
  good canaries: ghostwriting's intro has *Alte Sorten* legible on the spine, and
  book-illustration's intro is the illustrated artwork rather than a photo, exactly
  as SPEC flags.
- **All 10 book covers**, checked against SPEC's left→right title list. Order is
  as SPEC gives it: Bunny · Judge Stone · Project Hail Mary · The Correspondent ·
  Game On · Dear Debbie · Theo of Golden · Warriors · The Divorce · My Husband's.
- **3 story thumbnails, 4 avatars, both about photos.**
- **`favicon.svg` and `apple-touch-icon.png`**, rendered at 16 / 32 / 64 / 180px.

**Inferred, not individually opened:** the second width of each asset (e.g.
`-1280` where I opened `-640`) and the AVIF/WebP siblings of each JPG. These come
from the same in-memory crop through `resizeTo()`/`saveSet()`, so a correct JPG at
one width means the rest of that set is correct by construction.

I did **not** trust the loop on geometry: ghostwriting was built and verified end
to end first, and only then generalised. All nine remaining pages were then
checked visually anyway, since the contact sheet made it nearly free.

---

## 4. Problems — things that need a decision or a client asset

### 4.1 The service heroes carry baked-in page furniture ⚠ **resolved — see §9**

`svc/<slug>-hero-*` is the hero region of a **flattened page export**, so the
navbar, the wordmark, the `<h1>`, the body paragraph, both buttons and the entire
white "Start Your Book Today" form card are burnt into the pixels. Used as a CSS
background with live text on top, every service page will show doubled headlines
and a ghost form.

I checked for a clean source and there is not one: `Frame 2147239960`,
`Component 140/159/95/132/136` are the service-card grid, pricing cards, press
band and marquees — no hero photograph among them. The photo is not recoverable
from behind a 400×360px opaque form card.

**Needed:** the ten hero photographs exported from Figma as bare image fills
(SPEC §E.1 already asks for this — "source the originals from Figma at 2× for
retina"). The files exist at the contracted paths so nobody 404s and layout work
is unblocked, but they must be replaced before launch.

**Update:** the client resolved this rather than waiting for the Figma originals.
The twenty hero files were rebuilt from each page's clean end-to-end photograph.
The diagnosis above still stands and the ask for bare image fills is still the
right long-term fix; **§9 is what actually shipped.**

### 4.2 Story thumbnails have a baked-in play button and caption

`story-1..3` each carry the design's own ▶ button and a "Clara Wen / Everything
Remembered" plate. `author-stories.php` renders its own play glyph and pulls the
name from `shared.php`, so both will appear twice. Same fix as 4.1 — client
originals. CONTRACT §2.1 already marks these `'placeholder' => true`.

### 4.3 Avatars are a 2.1× upscale and look it

Confirmed as SPEC §E.1 predicted. The source circles are **45×45px** in the
1920 artboard; CONTRACT asks for 96px. They are visibly soft and blocky. Cropped
anyway so the markup is not blocked. **Originals should be requested from the
client** — this is the clearest case of the four.

Note `avatar-4` is a duplicate of `avatar-1` (Marcus Vance). That is not a bug on
my side: SPEC §D.2 / CONTRACT §2.1 say testimonial card 4 duplicates card 1, and
the design export shows the same face. Kept as a distinct file so the data array
can reference four avatars without special-casing.

### 4.4 `book-10` is a weaker asset than `book-01`–`09`

Covers 1–9 come from the dedicated `Component 104…112` exports at **1004×1424**
— genuinely high resolution, downscaled to 840 and 420.

Cover 10 (*My Husband's*, Alice Feeney) has no component export. Its only
appearance is a card on the Our Books page at **268×420**, whose bottom ~70px is
an opaque caption plate. I cropped above the plate, giving 268×345. Two
consequences:

- it is **upscaled** to reach 420 and 840 (1.6× and 3.1×) and is softer than the others;
- its aspect ratio is **0.78** where covers 1–9 are 0.71, so it will not sit flush
  in a fixed-ratio strip unless the CSS uses `object-fit: cover`.

**Needed:** either a `Component 113`-style export of that cover, or accept
`object-fit: cover` in `book-band.php`.

### 4.5 The 1280-wide intro/e2e variants are interpolation, not detail

The intro and end-to-end photos are only ~636px wide in the artboard, but
CONTRACT §5 asks for 640 **and** 1280. The 1280s therefore contain no real extra
detail. I kept them — omitting them would make `ep_srcset()` serve the 640 to
retina screens — but encode them at q70 rather than q82 (see §5), since there is
no high-frequency information to protect. If the performance phase wants the page
lighter, dropping the 1280 variants of `-intro`/`-e2e` is the cheapest win
available and costs almost nothing visually.

### 4.6 Rights

SPEC §E.2 already flags it and it is the Lead's to action, but restating since I
am the one who wrote the files: `books/book-01`–`book-10` are **real, in-copyright
commercial book covers** (Penguin, Tor, HarperCollins et al.) and the story
thumbnails are stock or AI-generated people. Clearing or replacing them is a
client decision.

---

## 5. Encoding choices

All photographs are AVIF + WebP + JPG via
`saveSet($img, $name, ['avif','webp','jpg'], $q)`.

| Content | Quality | Why |
|---|---|---|
| Photographs, native or downscaled | **82** | The brief's figure for JPEG-source content; not exceeded anywhere. |
| Service heroes (1280/1920) | **62** | 1920×866 is the largest thing on the site. The brief asked for AVIF under ~180 KB at 1920 — at q62 the worst hero AVIF is **110.8 KB** and the median is ~75 KB, comfortably inside. A full-bleed background behind a dark scrim is the least detail-critical surface on the page. |
| Any variant wider than its source | **70** | Upscales are interpolated; q82 spends bytes on resampling noise. Affects the `-1280` intro/e2e/about variants, `story-*-960`, `avatar-*-96`, `book-10-*`. |

That last rule is worth a number: it took the total from **18.35 MB to 15.00 MB
(−18%)** and the largest AVIF from 200.2 KB to 138.6 KB. I checked the result by
eye at 1280 — `creative-content-writing-intro-1280.jpg` shows no visible
artefacts, because there is no real detail in an upscale to lose.

Book covers and avatars are cropped tight to their artwork with no page
background around them, as required. The `Component 1xx` exports place each cover
on a black field inside a dashed Figma selection frame; the cover box is a
constant `(80, 80, 1028, 1448)`, and I inset a further 12px so the black that
bleeds into the cover's rounded corners is not carried through.

---

## 6. Brand mark

`assets/img/favicon.svg` — hand-authored, 5 polygons, ~600 bytes. SPEC §E.2's
mark is an isometric stack of books: green (`#60C489`) top slab, green
(`#4CAD74`) left face, dark-navy (`#1B2A4A`) right face.

I rendered three candidate geometries at 16/32/64/180px before choosing. A single
page-gap reads as a plain two-tone cube at 16px; **two** gaps are what make it
read as a *stack of books*, and they survive at 16px. The gaps are a `<mask>`
cut-out rather than white fills, so the mark stays correct on a dark browser tab
bar as well as a light one. Validated as well-formed XML.

`assets/img/apple-touch-icon.png` — 180×180, same geometry on a solid white
field (the brief allowed `#60C489` or white; white was chosen because the mark's
own top slab *is* `#60C489` and would disappear against it). Drawn by the script
at 4× and downsampled, because GD's `imagefilledpolygon()` has no antialiasing.

Both were 404ing from `head.php` on every page. They now resolve.

---

## 7. Not mine, flagged for whoever owns it

- `head.php` line 66 references `img/logo.svg` for the Open Graph / JSON-LD
  publisher logo. That file **does not exist** — the Lead's `build-assets.php`
  writes `logo.png` and `logo.webp`, not `.svg`. It is outside my ownership
  (`tools/build-assets.php` and `includes/**` are the Lead's) and it is not in
  CONTRACT §5, so I have not created it. It is a broken URL in structured data
  on every page.

---

## 8. Full manifest

Widths are as CONTRACT §5 specifies. "Pixels" is the actual encoded size of the
JPG; the AVIF/WebP siblings have identical dimensions. The last two rows are the
brand mark — they have no format variants, so their single file size is listed
in the final column.

| File (base) | Width | Pixels | AVIF | WebP | JPG |
|---|---|---|---|---|---|
| `books/book-01` | 420 | 420×596 | 34.8 KB | 31.3 KB | 45.4 KB |
| `books/book-01` | 840 | 840×1191 | 86.4 KB | 70.8 KB | 113.8 KB |
| `books/book-02` | 420 | 420×596 | 27.0 KB | 28.4 KB | 47.7 KB |
| `books/book-02` | 840 | 840×1191 | 62.0 KB | 62.4 KB | 116.8 KB |
| `books/book-03` | 420 | 420×596 | 24.9 KB | 24.6 KB | 44.4 KB |
| `books/book-03` | 840 | 840×1191 | 53.9 KB | 52.3 KB | 106.1 KB |
| `books/book-04` | 420 | 420×596 | 25.1 KB | 19.3 KB | 35.5 KB |
| `books/book-04` | 840 | 840×1191 | 104.0 KB | 67.7 KB | 132.5 KB |
| `books/book-05` | 420 | 420×596 | 42.2 KB | 35.8 KB | 52.9 KB |
| `books/book-05` | 840 | 840×1191 | 97.4 KB | 79.0 KB | 130.2 KB |
| `books/book-06` | 420 | 420×596 | 30.3 KB | 27.3 KB | 46.7 KB |
| `books/book-06` | 840 | 840×1191 | 70.0 KB | 61.1 KB | 118.6 KB |
| `books/book-07` | 420 | 420×596 | 12.2 KB | 9.4 KB | 21.1 KB |
| `books/book-07` | 840 | 840×1191 | 22.9 KB | 21.9 KB | 55.3 KB |
| `books/book-08` | 420 | 420×596 | 36.7 KB | 31.9 KB | 48.8 KB |
| `books/book-08` | 840 | 840×1191 | 66.3 KB | 69.2 KB | 121.8 KB |
| `books/book-09` | 420 | 420×596 | 20.6 KB | 20.6 KB | 35.0 KB |
| `books/book-09` | 840 | 840×1191 | 48.2 KB | 44.6 KB | 91.9 KB |
| `books/book-10` | 420 | 420×541 | 13.7 KB | 12.6 KB | 22.5 KB |
| `books/book-10` | 840 | 840×1081 | 29.3 KB | 29.5 KB | 61.4 KB |
| `story-1` | 480 | 480×397 | 10.3 KB | 9.5 KB | 18.3 KB |
| `story-1` | 960 | 960×793 | 24.5 KB | 25.1 KB | 50.3 KB |
| `story-2` | 480 | 480×397 | 9.7 KB | 9.0 KB | 16.8 KB |
| `story-2` | 960 | 960×793 | 23.3 KB | 22.8 KB | 45.5 KB |
| `story-3` | 480 | 480×397 | 8.3 KB | 7.3 KB | 14.1 KB |
| `story-3` | 960 | 960×793 | 19.4 KB | 19.1 KB | 39.7 KB |
| `avatar-1` | 96 | 96×96 | 1.8 KB | 1.4 KB | 2.5 KB |
| `avatar-2` | 96 | 96×96 | 1.6 KB | 1.2 KB | 2.2 KB |
| `avatar-3` | 96 | 96×96 | 1.6 KB | 1.2 KB | 2.2 KB |
| `avatar-4` | 96 | 96×96 | 1.8 KB | 1.4 KB | 2.5 KB |
| `about-story` | 640 | 640×598 | 30.5 KB | 26.3 KB | 41.9 KB |
| `about-story` | 1280 | 1280×1195 | 85.1 KB | 72.6 KB | 133.1 KB |
| `about-why` | 640 | 640×491 | 29.4 KB | 23.4 KB | 38.7 KB |
| `about-why` | 1280 | 1280×982 | 52.1 KB | 42.6 KB | 85.3 KB |
| `svc/books-publishing-hero` | 1280 | 1280×577 | 25.2 KB | 25.9 KB | 51.9 KB |
| `svc/books-publishing-hero` | 1920 | 1920×866 | 38.9 KB | 45.4 KB | 100.4 KB |
| `svc/books-publishing-intro` | 640 | 640×598 | 17.8 KB | 15.5 KB | 30.9 KB |
| `svc/books-publishing-intro` | 1280 | 1280×1195 | 49.1 KB | 42.4 KB | 93.1 KB |
| `svc/books-publishing-e2e` | 640 | 640×598 | 32.3 KB | 28.3 KB | 45.9 KB |
| `svc/books-publishing-e2e` | 1280 | 1280×1195 | 79.5 KB | 74.7 KB | 130.6 KB |
| `svc/book-editing-hero` | 1280 | 1280×577 | 30.5 KB | 29.7 KB | 57.2 KB |
| `svc/book-editing-hero` | 1920 | 1920×866 | 57.1 KB | 57.3 KB | 114.9 KB |
| `svc/book-editing-intro` | 640 | 640×598 | 39.5 KB | 37.1 KB | 57.3 KB |
| `svc/book-editing-intro` | 1280 | 1280×1195 | 103.5 KB | 98.0 KB | 173.5 KB |
| `svc/book-editing-e2e` | 640 | 640×598 | 22.8 KB | 20.5 KB | 37.1 KB |
| `svc/book-editing-e2e` | 1280 | 1280×1195 | 64.2 KB | 56.9 KB | 115.8 KB |
| `svc/book-cover-design-hero` | 1280 | 1280×577 | 41.9 KB | 40.8 KB | 71.0 KB |
| `svc/book-cover-design-hero` | 1920 | 1920×866 | 66.1 KB | 72.0 KB | 137.8 KB |
| `svc/book-cover-design-intro` | 640 | 640×598 | 25.2 KB | 21.7 KB | 38.7 KB |
| `svc/book-cover-design-intro` | 1280 | 1280×1195 | 65.7 KB | 57.2 KB | 113.8 KB |
| `svc/book-cover-design-e2e` | 640 | 640×598 | 24.3 KB | 23.0 KB | 38.8 KB |
| `svc/book-cover-design-e2e` | 1280 | 1280×1195 | 63.3 KB | 59.8 KB | 111.8 KB |
| `svc/book-illustration-hero` | 1280 | 1280×577 | 61.3 KB | 55.2 KB | 85.5 KB |
| `svc/book-illustration-hero` | 1920 | 1920×866 | 110.8 KB | 102.8 KB | 171.1 KB |
| `svc/book-illustration-intro` | 640 | 640×598 | 20.0 KB | 18.0 KB | 36.8 KB |
| `svc/book-illustration-intro` | 1280 | 1280×1195 | 46.8 KB | 45.1 KB | 100.7 KB |
| `svc/book-illustration-e2e` | 640 | 640×598 | 19.2 KB | 19.6 KB | 35.2 KB |
| `svc/book-illustration-e2e` | 1280 | 1280×1195 | 43.8 KB | 47.7 KB | 96.6 KB |
| `svc/audio-book-production-hero` | 1280 | 1280×577 | 27.4 KB | 27.1 KB | 53.8 KB |
| `svc/audio-book-production-hero` | 1920 | 1920×866 | 40.7 KB | 47.9 KB | 104.1 KB |
| `svc/audio-book-production-intro` | 640 | 640×598 | 19.3 KB | 17.8 KB | 35.8 KB |
| `svc/audio-book-production-intro` | 1280 | 1280×1195 | 45.5 KB | 43.6 KB | 99.8 KB |
| `svc/audio-book-production-e2e` | 640 | 640×598 | 36.2 KB | 32.3 KB | 49.7 KB |
| `svc/audio-book-production-e2e` | 1280 | 1280×1195 | 96.7 KB | 83.9 KB | 151.0 KB |
| `svc/ghostwriting-hero` | 1280 | 1280×577 | 40.0 KB | 38.1 KB | 65.1 KB |
| `svc/ghostwriting-hero` | 1920 | 1920×866 | 59.7 KB | 65.8 KB | 125.0 KB |
| `svc/ghostwriting-intro` | 640 | 640×598 | 13.8 KB | 13.6 KB | 28.4 KB |
| `svc/ghostwriting-intro` | 1280 | 1280×1195 | 31.3 KB | 32.9 KB | 80.6 KB |
| `svc/ghostwriting-e2e` | 640 | 640×598 | 23.2 KB | 21.4 KB | 39.4 KB |
| `svc/ghostwriting-e2e` | 1280 | 1280×1195 | 59.2 KB | 56.5 KB | 111.5 KB |
| `svc/book-marketing-hero` | 1280 | 1280×577 | 32.9 KB | 32.1 KB | 59.9 KB |
| `svc/book-marketing-hero` | 1920 | 1920×866 | 54.9 KB | 59.1 KB | 118.0 KB |
| `svc/book-marketing-intro` | 640 | 640×598 | 34.8 KB | 29.3 KB | 47.2 KB |
| `svc/book-marketing-intro` | 1280 | 1280×1195 | 86.2 KB | 74.3 KB | 138.2 KB |
| `svc/book-marketing-e2e` | 640 | 640×598 | 33.2 KB | 29.7 KB | 50.7 KB |
| `svc/book-marketing-e2e` | 1280 | 1280×1195 | 80.5 KB | 74.4 KB | 149.7 KB |
| `svc/proofreading-hero` | 1280 | 1280×577 | 28.6 KB | 28.9 KB | 56.0 KB |
| `svc/proofreading-hero` | 1920 | 1920×866 | 44.8 KB | 51.5 KB | 109.6 KB |
| `svc/proofreading-intro` | 640 | 640×598 | 28.3 KB | 25.4 KB | 45.4 KB |
| `svc/proofreading-intro` | 1280 | 1280×1195 | 78.6 KB | 69.8 KB | 138.8 KB |
| `svc/proofreading-e2e` | 640 | 640×598 | 31.2 KB | 26.6 KB | 45.5 KB |
| `svc/proofreading-e2e` | 1280 | 1280×1195 | 88.6 KB | 76.8 KB | 146.5 KB |
| `svc/creative-content-writing-hero` | 1280 | 1280×577 | 30.1 KB | 29.7 KB | 57.1 KB |
| `svc/creative-content-writing-hero` | 1920 | 1920×866 | 46.3 KB | 52.2 KB | 111.2 KB |
| `svc/creative-content-writing-intro` | 640 | 640×598 | 51.5 KB | 43.6 KB | 61.8 KB |
| `svc/creative-content-writing-intro` | 1280 | 1280×1195 | 138.6 KB | 119.0 KB | 195.9 KB |
| `svc/creative-content-writing-e2e` | 640 | 640×598 | 26.3 KB | 25.8 KB | 45.8 KB |
| `svc/creative-content-writing-e2e` | 1280 | 1280×1195 | 66.6 KB | 65.2 KB | 134.7 KB |
| `svc/blog-article-writing-hero` | 1280 | 1280×577 | 29.1 KB | 27.9 KB | 54.9 KB |
| `svc/blog-article-writing-hero` | 1920 | 1920×866 | 45.9 KB | 50.7 KB | 108.1 KB |
| `svc/blog-article-writing-intro` | 640 | 640×598 | 51.8 KB | 45.6 KB | 62.9 KB |
| `svc/blog-article-writing-intro` | 1280 | 1280×1195 | 120.8 KB | 109.9 KB | 174.8 KB |
| `svc/blog-article-writing-e2e` | 640 | 640×598 | 37.3 KB | 32.0 KB | 50.8 KB |
| `svc/blog-article-writing-e2e` | 1280 | 1280×1195 | 95.3 KB | 84.1 KB | 146.7 KB |
| `favicon.svg` | — | 32×32 viewBox | — | — | 0.6 KB |
| `apple-touch-icon.png` | — | 180×180 | — | — | 2.9 KB |

---

## 9. Hero rebuild — the ten service heroes now come from the e2e photograph

**Supersedes the hero rows in §8 and the "needed" note in §4.1.** Everything else
in this report is unchanged and was re-verified by the clean rebuild described in
§9.6.

### 9.1 What was wrong and what was decided

`svc/<slug>-hero-*` was cut from the export's y 0–866 band, exactly as SPEC §E.1
directs. That band is the **composited design**, so the navbar, the wordmark, the
`<h1>`, the body paragraph, both buttons and the opaque "Start Your Book Today"
form card were flattened into the pixels. The live HTML hero renders on top of
them, so all ten service pages showed a doubled headline and a ghost form.

A photograph cannot be separated from text flattened over it, and there is no
bare hero image fill anywhere in the exports (re-checked: `Frame 2147239960` and
`Component 95/132/136/140/159` are the service-card grid, pricing cards, press
band and marquees). The client's decision was to **rebuild each hero from that
page's end-to-end photograph** — the one large text-free photograph on the page.

**Design fidelity on the hero image is deliberately traded for a hero that is not
visibly broken.** The consequence is that the same photograph now appears twice
on every service page — full-bleed in the hero and again in section 7. That is
known and accepted. It should not be "fixed" by reinstating the composited crop.

Note the second consequence: the hero photographs SPEC §E.1 describes (the tartan
blanket for ghostwriting, the green wingback for creative-content-writing, the
magazine rack for book-illustration) are **no longer on the site at all**. If the
client wants them back they have to arrive as bare image fills.

### 9.2 Native resolution — how much of this is real detail

This is the part worth being blunt about. I went back to the 1920px page exports
and scanned for the photograph's true edges against the page background on all
ten, rather than trusting a crop box that was sized to the design's layout slot:

```
e2e photograph, measured on all ten exports:   x 1035..1669   y 2753..3346
                                               = 635 x 594, identical every time
```

The layout slot **is** the photograph's full extent here — it is a rounded-rect
fill (radius ~10px) with page background on all four sides, so there are no
further native pixels hiding outside it. I also checked the rest of each page for
a larger instance of the same photograph: a service export contains exactly three
photographs (hero, intro, e2e), the e2e appears once, and the intro photo is the
same 635 wide. So **635px is the ceiling, on every slug**, and it is not a crop
decision I can improve on.

| | |
|---|---|
| Native source band | **635 × 286** per slug (0.18 MP) |
| Delivered at 1280 | 2.02× enlargement |
| Delivered at 1920 | **3.02× enlargement** (1.66 MP — 9.1× the pixels are invented) |
| Hero aspect | 635/286 = 2.220 vs the design's 1920/866 = 2.217 (0.14% squash, invisible) |

No letterboxing, padding or squashing: the band is the photograph's full width,
cut to the hero aspect, with only the vertical offset chosen.

### 9.3 Per-slug vertical offset, and why

Offset is measured down from the top of the 594px-tall e2e photograph. Ten
photographs, ten different offsets — a single constant would have decapitated
about half of them. All offsets are kept inside [12, 296] so the rounded corners
never pull page background into a full-bleed image.

| Slug | Native | Offset | Why that offset |
|---|---|---|---|
| `books-publishing` | 635×286 | **+24** | Extreme close-up. The eyes sit at y 30–110 and the head is already cut by the source frame, so this is as high as the band can go: eyes along the top, the Cyrillic cover filling the rest. |
| `book-editing` | 635×286 | **+14** | Wanted the whole head. +30 clipped the crown; +14 keeps it with headroom and puts the face at ~40% down. |
| `book-cover-design` | 635×286 | **+125** | Reclining subject, face low-right and the held book high-left. This is the only band that gets **both** fully in frame. |
| `book-illustration` | 635×286 | **+150** | No face in this photograph at all — the head is above the source frame. Centres the stack of Cyrillic-spined hardbacks and the hands, which are the subject. |
| `audio-book-production` | 635×286 | **+14** | Full head with a little headroom. The books are at y 400–560 and cannot share a 286px band with the face; the face won. |
| `ghostwriting` | 635×286 | **+140** | Face at 38% down, plant and café window behind. The notebook is at y 440+ and does not fit — only the pen tip makes it in. |
| `book-marketing` | 635×286 | **+130** | Best of the ten: the bookcase fills the frame at every height, so the face can sit centred and the books are in shot regardless. |
| `proofreading` | 635×286 | **+30** | The face is hidden behind the book in this photograph, so there is no face to protect. Frames the open tan book, the mug and both hands. |
| `creative-content-writing` | 635×286 | **+12** | Back view. +30 sliced the top off the top-knot and looked like an accident; +12 keeps the whole head. |
| `blog-article-writing` | 635×286 | **+30** | Face, gold-rimmed glasses and the open book all fit in one band. |

Each of the ten was rendered and looked at before and after choosing the offset.
Three offsets (`book-editing`, `audio-book-production`, `creative-content-writing`)
were moved after the first pass because the first choice clipped the subject's
head. **No slug had to be abandoned** — all ten carry a 2.22:1 crop without
destroying the subject, though `books-publishing` and `book-illustration` are
tight because their source photographs are already cropped above the head.

As a cross-check that these are the right photographs, all ten crops match SPEC
§E.1's *End-to-End photo* column description word for word.

### 9.4 New file sizes — the twenty regenerated heroes

Encoded at q62 as before. Dimensions verified as exactly 1280×577 and 1920×866,
so the `width`/`height` attributes in `service.php` are unaffected.

| File (base) | 1280 AVIF | 1280 WebP | 1280 JPG | 1920 AVIF | 1920 WebP | 1920 JPG |
|---|---|---|---|---|---|---|
| `svc/books-publishing-hero` | 34.0 KB | 34.0 KB | 56.4 KB | 57.6 KB | 59.9 KB | 109.5 KB |
| `svc/book-editing-hero` | 25.9 KB | 25.6 KB | 47.3 KB | 44.7 KB | 45.3 KB | 93.1 KB |
| `svc/book-cover-design-hero` | 42.8 KB | 41.1 KB | 63.9 KB | 74.0 KB | 73.7 KB | 122.9 KB |
| `svc/book-illustration-hero` | 31.8 KB | 34.0 KB | 57.2 KB | 54.4 KB | 58.7 KB | 110.6 KB |
| `svc/audio-book-production-hero` | 35.4 KB | 34.2 KB | 59.1 KB | 62.8 KB | 59.3 KB | 115.4 KB |
| `svc/ghostwriting-hero` | 35.6 KB | 34.8 KB | 59.5 KB | 63.3 KB | 62.4 KB | 116.3 KB |
| `svc/book-marketing-hero` | 47.0 KB | 45.1 KB | 78.3 KB | **78.1 KB** | 76.6 KB | 151.1 KB |
| `svc/proofreading-hero` | 42.0 KB | 39.9 KB | 67.2 KB | 72.9 KB | 71.5 KB | 134.8 KB |
| `svc/creative-content-writing-hero` | 37.7 KB | 38.1 KB | 69.7 KB | 65.3 KB | 66.1 KB | 140.0 KB |
| `svc/blog-article-writing-hero` | 27.1 KB | 27.7 KB | 52.3 KB | 46.4 KB | 47.4 KB | 101.9 KB |

**Quality budget: comfortably met.** The largest hero AVIF at 1920 is **78.1 KB**
(`book-marketing`); the range is 44.7–78.1 KB and the median ~64 KB — under half
the 180 KB ceiling. The previous composited heroes ranged 38.9–110.8 KB, so the
worst case actually improved by 32 KB while the median rose slightly.

Delivered cost per service page barely moves. The LCP fetch is **+1.2 KB** on
average at 1× DPR (hero-1280 AVIF, avg 34.7 → 35.9 KB) and **+5.5 KB** at 2× DPR
(hero-1920 AVIF, avg 56.5 → 62.0 KB). Across all 60 hero files the set grew
3.58 MB → 3.67 MB, and `assets/img/` as a whole 16.42 MB → 16.51 MB.

**I did not raise the quality setting**, even with 100 KB of headroom. At a 3×
upscale there is no high-frequency detail for extra bits to preserve — a higher q
would encode the interpolation more faithfully and buy nothing visible. Per the
brief, the smaller file wins. The bytes were spent on something that does help
instead (below).

### 9.5 The sharpen, and an honest read on softness

Both hero widths are upscales, so the band is sharpened **before** the
enlargement, at native scale, where a 3×3 kernel still lands on real edges;
applied afterwards it would only sharpen interpolated ones. The kernel is a
gentle unsharp (centre 1.5, each neighbour −0.0625). A stronger centre-2.0
variant was rendered and rejected as over-processed in the hair. It costs ~15 KB
on each 1920 AVIF and is the single most worthwhile thing in this section.

**How soft do they actually look at 1920?** Judged at 1:1 in the delivered files,
not fitted to a screen:

- **Structural edges hold up well.** Jawlines, eyelashes, lips, glasses rims,
  shelf edges and the bookcase's verticals are clean, and read as intentional
  shallow depth of field rather than as a bad enlargement.
- **Fine texture is gone.** Skin has a waxy, faintly plastic quality. Individual
  hair strands are smeared into ribbons. Book-spine lettering in the backgrounds
  of `book-marketing` and `creative-content-writing` — legible in the e2e crop at
  640 — is illegible mush at 1920.
- **No ringing or halos** from the sharpen, and no visible AVIF blocking at q62.

**Verdict: soft, but not broken.** These are the softest photographs on the site
and would be obvious side by side with a native 1920 image. In situ it matters
much less than the numbers suggest: the hero sits behind a scrim that runs heavy
on the left, the left 55% is covered by the `<h1>` and body copy and the right
45% by an opaque form card, so the region actually read as photograph is a band
through the middle. On a 1× display the browser fetches the 1280 (a 2.02×
upscale), which is noticeably better than the 1920. Either way it is a clear
improvement on a hero with a second headline printed into it.

If the client ever supplies the bare Figma image fills, this whole section
reverts to a two-line crop box and the softness disappears. That remains the
right fix.

### 9.6 Reproducibility and file set

`tools/assets-photos.php` was re-run end to end (no group argument) after the
change. Deleting the output tree first was not permitted in this environment, but
no filename changed, so every file the script owns was overwritten in place — and
the run was verified by timestamp instead: **all 284 owned files carry the new
run's mtime, and the only files with older timestamps are the 22 belonging to the
Lead** (`hero-band-*`, `footer-band-*`, `logo*`, `og-default.jpg`). No stale or
orphaned outputs.

| | |
|---|---|
| `assets/img/` total | **306 files, 16.51 MB** (was 306 files, 16.42 MB) |
| Written by this script | 284 — unchanged, nothing added or removed |
| `svc/` | 180 files · `books/` 60 files |
| Missing from CONTRACT §5 | none |

Everything outside the twenty hero files came out **byte-for-byte identical** to
the §8 manifest — spot-checked across `book-10`, `avatar-1`, `about-story`,
`book-illustration-intro` and `creative-content-writing-intro`, all matching to
0.1 KB. `<slug>-e2e-*` and `<slug>-intro-*` were not touched and are still the
files sections 5 and 7 consume.

### 9.7 Stale documentation this creates — not mine to edit

Two documents now describe a hero that no longer exists. Flagging rather than
editing, since neither is in my ownership (CONTRACT §1):

- **`docs/CONTRACT.md` §5**, the `img/svc/<slug>-hero` row, gives the source as
  "each service export, y 0–866". It is now the e2e photograph's band instead.
- **`docs/SPEC.md` §E.1**, the "Hero background (full-bleed, y 0–866)" column of
  the service-pages table, describes ten photographs that are no longer used
  anywhere on the site.

Filenames, widths, formats and counts are all unchanged, so no other agent's code
is affected — only the prose describing where the pixels came from.
