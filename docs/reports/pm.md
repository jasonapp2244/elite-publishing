# PM report — Phase 3 data files

## Files written

| File | Contents | Parses |
|---|---|---|
| `data/shared.php` | 11 top-level keys: `services_carousel`, `journey`, `process`, `stories`, `testimonials`, `platforms`, `faq`, `cta`, `wizard`, `press`, `service_why` | ✅ |
| `data/services.php` | 10 slugs, `array_keys() === array_keys(EP_SERVICES)` confirmed | ✅ |
| `data/books.php` | 10 rows, all `'placeholder' => true` | ✅ |
| `data/pricing.php` | `eyebrow`, `heading`, 3 tiers | ✅ |
| `docs/CLIENT-QUESTIONS.md` | 44 numbered questions | — |

Verified with `php -l` on all four, plus a shape check confirming: carousel 8
cards / journey 4 / process 7 / stories 3 / testimonials 4 / platforms 4 / faq 6
/ wizard 4 / press 7 logos / why-us 5 chips; every carousel `slug` is a real
`EP_SERVICES` key; all 10 services carry `hero{h1,text,image}`,
`intro{h2,paras,image}`, `e2e{h2,text,label,offers,image}`; each `title` matches
`EP_SERVICES`; 10 unique `meta_desc` of 142–155 chars; all five genres populated;
no literal `<br>` anywhere. No files outside my ownership list were touched.

## Copy bugs reproduced deliberately (DECISIONS §11)

All twelve are in the data or belong to a page (marked ▸) and are listed in
`CLIENT-QUESTIONS.md` §C with pre-written corrections:

1. FAQ answer 1 `romance , christian ,` — in `shared.php`
2. ▸ Home h1 `Elite Publishing , The` — Dev 1's page copy, flagged in the doc
3. ▸ About hero paragraph ends on a comma — Dev 3
4. ▸ About `Our Story Elite Publishing was founded…` — Dev 3
5. ▸ T&C leading space on the IP bullet — Dev 3
6. Book Cover Design `offers` = Books Publishing's list — in `services.php`
7. Book Illustration `e2e.text` about websites — in `services.php`
8. Creative Content Writing page is about script writing — in `services.php`
9. Blog Article Writing page is about script writing — in `services.php`
10. Testimonial names with trailing commas — in `shared.php`
11. Testimonial card 4 = card 1 — in `shared.php`
12. All three author-story cards say Clara Wen / Everything Remembered — in `shared.php`

Bugs 2–5 sit in page-level copy owned by Dev 1 and Dev 3. **They are not in my
data files** — those devs must transcribe them from SPEC §D.3/§D.5/§D.9 exactly,
including the stray spaces and the unfinished sentence. Worth a QA check.

## Drafted copy — 19 strings

Every one carries `'draft' => true`. `grep -c "'draft' => true" data/shared.php`
returns 20 (19 real + 1 in the file's doc comment). All 19 are printed verbatim
in `CLIENT-QUESTIONS.md` §B for sign-off.

**FAQ answers 2–6** (5 strings, `shared.php['faq']['items'][1..5]['a']`) —
2–3 sentences each, matched to answer 1's ~28-word length. Two of them make
factual claims the client must confirm: the refund answer had to be reconciled
with the Terms & Conditions ("non-refundable once the project has started"), and
the confidentiality answer asserts a written NDA exists.

**Process steps 02–07** (6 steps, `shared.php['process']['steps'][1..6]`) — a
title (taken from the tab label, which *is* in the design), a 1–2 sentence body
and a 4-item checklist each, shaped like step 01. Six of these checklists make
operational claims I could not verify from any document: two editors per
manuscript, two revision rounds, three cover concepts, ISBN + copyright
registration on the author's behalf, KDP and IngramSpark specifically, and paid
advertising included rather than billed separately. Each is called out as a
sub-question in `CLIENT-QUESTIONS.md` §B.2.

**Service card descriptions** (8 strings,
`shared.php['services_carousel']['cards'][*]['text']`) — the visible fragment is
byte-identical to the design, including the mid-word cut (`Our compre`,
`including struct`, `modern, high-`, `includin`, `Our marketing s`,
`SEO-optimized bl`) and the trailing comma after `author bios,`. Only the
continuation is new. The ellipsis is dropped from the data; CSS restores it via
`-webkit-line-clamp: 3`, so the card looks identical.

Nothing else was drafted. `meta_desc` (10 strings in `services.php`) is written
by the build and deliberately **not** marked draft — meta descriptions never
exist in a Figma export.

## Ambiguities and contradictions found while encoding

**1. Testimonial quote marks.** SPEC §B.8 says the quote sits "in curly double
quotes"; SPEC §D.2 transcribes them with straight `"`. I used straight quotes, as
in §D.2 (the copy section), matching CONTRACT §2.1's own example. If the design
really uses `"` `"`, that is a one-line change in `shared.php` — or better, do it
in CSS so the data stays plain.

**2. Books Publishing's carousel icon.** SPEC §E.2 calls it "a magnifying glass
over a page". CONTRACT §4 adds a new glyph named `magnifier`, and CONTRACT §2.1's
own worked example uses the existing `search`. I used **`search`** to match the
contract's example. If the Lead builds `magnifier` as the page-plus-lens variant,
switching one string is trivial — flagging so we don't ship both glyphs.

**3. Wizard step 3 has no emoji.** Steps 1 and 2 have a leading emoji per option;
step 3's budget options are drawn without one. I set `'emoji' => null` on all
five rather than omitting the key, so the renderer can test one shape.

**4. Two book covers are not fully identified.** SPEC §E.1 lists cover 9 as only
"*Freida McFadden* (pink)" — an author, not a title — and cover 10 as
"*My Husband's…*" (Alice Feeney), truncated. I entered `The Boyfriend` for cover
9 as a best guess and kept `My Husband's…` verbatim for cover 10. Both rows are
`placeholder => true` and both are raised as question 35. If the client answers
question 34 this becomes moot.

**5. Genre is not in the design at all.** No book card, tab or caption in the
export associates a book with a genre — but the home page has five genre filter
tabs that must not be empty. I assigned genres myself: fiction 4, romance 2,
childrens 2, non-fiction 1, christian 1. This is build-invented data, flagged as
question 36. The genre strings are lowercase slugs (`non-fiction`, `childrens`),
not the tab labels (`Non-Fiction`, `Children's`) — Dev 1 needs to map them.

**6. Book Cover Design is doubly affected.** Its `offers` list is Books
Publishing's (bug 6), and I also wrote its `meta_desc` about cover design rather
than publishing. So the page's meta description and its own on-page list now
disagree. That is the right call — the meta description should describe the
service — but QA will notice it, and it resolves the moment bug 6 is fixed.

**7. Creative Content Writing and Blog Article Writing meta descriptions.** Both
pages' bodies are entirely about script writing (bugs 8 and 9). I wrote each
`meta_desc` to straddle both, mentioning the page's real title and the script
content, rather than describing a service the page does not discuss. They will
read oddly until the client picks option (a) rename or (b) rewrite in question
29/30.

## Icons

Every glyph I needed exists in either CONTRACT §4's new list or the current
`$paths` array in `includes/functions.php`. **Nothing is missing.**

| Where | Icons used | Source |
|---|---|---|
| Carousel cards 1–8 | `search`, `quill`, `layers`, `book-open`, `mic`, `megaphone`, `browser`, `hand-pen` | `search`/`layers`/`book-open`/`mic`/`megaphone` exist; `quill`/`browser`/`hand-pen` are CONTRACT §4 |
| Journey steps 1–4 | `lightbulb`, `hand-pen`, `palette`, `paper-plane` | `palette` exists; the rest are CONTRACT §4 |
| Pricing tiers | `book-open`, `sparkle`, `crown` | `book-open` exists; `sparkle`/`crown` are CONTRACT §4 |

Two notes for the Lead:

- `hand-pen` is used twice with different meanings: SPEC §E.2 describes the
  Creative Content Writing card as "a hand holding a pen" and journey step 2 as
  "hand writing with a pen". They may be the same glyph or two; I pointed both at
  `hand-pen`. If they differ, the second one needs a name.
- SPEC §E.2 flags the **Book Marketing** icon as ambiguous at export resolution
  ("a lightbulb/balloon-like teardrop with a stem", low confidence). I used
  `megaphone`, which is what §D.2's icon column names. Worth a look in Figma.

## Data keys CONTRACT does not define

One gap. CONTRACT §2.1 specifies wizard step 4 as `'type' => 'fields'` "with the
four inputs" but does not give the shape of a field. I used:

```php
['name' => 'full_name', 'type' => 'text', 'placeholder' => 'Full Name', 'width' => 'full']
```

`width` is `full` or `half` — step 4 is drawn as Full Name full-width, Email
Address + Phone No side by side, then the textarea full-width. `name`, `type`,
`placeholder`, `width` are my keys, not the contract's. If the Lead's
`cta-wizard.php` expects something else, say so and I will change it; nothing
else reads them.

Two smaller notes, both within the contract's shape:

- Steps 1–3 carry `options`, step 4 carries `type` + `fields`. A renderer should
  branch on `isset($step['fields'])`, not on an index.
- I did **not** add a `back` key. The back button exists on steps 2–4 only,
  which is derivable from the index.

## What QA should know is not real

- **19 drafted strings** — 5 FAQ answers, 6 process steps (body + checklist), 8
  card description continuations. `grep "'draft' => true" data/shared.php`.
- **10 book rows** — real cover identities, invented genres, one guessed title.
  `grep "'placeholder' => true" data/books.php`.
- **3 author-story cards + 4 testimonial avatars** — `placeholder => true` on the
  stories; the testimonial images are placeholder photography too, though the
  quotes and attributions are the design's.
- **10 meta descriptions** — written by the build, not client-approved, but
  correct by construction (they are never in a design).
- **Everything else in these four files is transcribed character-for-character
  from SPEC §D**, copy bugs included.
