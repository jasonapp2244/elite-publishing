<?php
declare(strict_types=1);

/**
 * "Featured Self-Publishing Solutions" — SPEC §C.1 §5, §B.5.
 *
 * Header block left (eyebrow / h2 / intro), carousel arrows right, then a
 * horizontal 4-up track of service cards with the fifth peeking. Eight cards,
 * not ten: Ghostwriting and Proofreading have no card in the design
 * (DECISIONS §9).
 *
 * Card titles carry a deliberate two-line break and descriptions are clamped
 * to three lines in CSS — both are design features, see SPEC §B.5.
 *
 * Optional input:
 *   $carouselHeading  string  overrides the h2 (unused so far; the section is
 *                             identical on every page that shows it)
 */

$svc = ep_data_get('shared', 'services_carousel');
if (empty($svc['cards'])) {
    return;
}

$heading = $carouselHeading ?? ($svc['heading'] ?? '');
$headId  = 'services-' . substr(md5($heading), 0, 6);
?>
<?php /* data-autoplay is the interval in ms between advances, and this one
         number is the whole speed control — raise it to slow the rail down,
         lower it to speed it up, remove the attribute to make the rail manual
         again. See initAutoplay() in assets/js/main.js.

         2000, taken down from 4500 in two steps at the client's request. This
         is the floor for a stepped rail and should not go lower: each advance
         is a smooth scroll of roughly 210ms, so at 2000ms a card is only still
         for about 1.8s — already less than it takes to read a title plus three
         lines of description. Below this the next move starts while the eye is
         still on the last card, which reads as jitter rather than speed.

         data-loop turns the end of the rail into a seam instead of a wall.
         Without it the rail reached the last card and scrolled all the way back
         to the first; with it the cards are duplicated (see below) and the rail
         steps into the copy, then silently rewinds by exactly one copy. The
         motion never reverses. */ ?>
<section class="section" aria-labelledby="<?= esc($headId) ?>"
         data-scroller data-autoplay="2000" data-loop>
  <div class="container-ep">

    <div class="section-head">
      <div class="section-head__text">
        <p class="eyebrow"><?= esc($svc['eyebrow'] ?? '') ?></p>
        <h2 id="<?= esc($headId) ?>"><?= ep_lines($heading) ?></h2>
        <?php if (!empty($svc['intro'])): ?>
          <p class="lead text-muted-ep section-head__intro"><?= esc($svc['intro']) ?></p>
        <?php endif; ?>
      </div>

      <?php /* Not aria-hidden: these are real, labelled controls and a keyboard
               user needs them to reach cards 5-8. Hiding the wrapper while
               leaving the buttons focusable is an axe violation
               (aria-hidden-focus) and strands anyone not using a mouse. */ ?>
      <div class="ep-scroller-nav">
        <button type="button" data-scroll-prev aria-label="Previous services">
          <?= ep_icon('arrow-left', ['size' => 20]) ?>
        </button>
        <button type="button" data-scroll-next aria-label="Next services">
          <?= ep_icon('arrow-right', ['size' => 20]) ?>
        </button>
      </div>
    </div>
  </div>

  <?php /* The track deliberately sits OUTSIDE .container-ep. The design runs it
           past the container's right edge so a fifth card is half-visible, and
           that peek is the only thing telling a visitor the row scrolls. Doing
           it with a negative margin instead makes the whole document
           horizontally scrollable, so the track is full-width and pads its own
           left edge back into line with the heading. */ ?>
  <div class="svc-rail-bleed">
    <?php /* The card list is rendered TWICE, and that is what makes the loop
             seamless. The rail used to run to the last card and then scroll all
             the way back to the first — a long backwards sweep that reads as the
             carousel breaking rather than repeating.

             With a second identical copy behind the first, initAutoplay() can
             subtract one copy's width the moment the rail crosses into the
             duplicate. The content either side of that seam is identical, so the
             jump is invisible and the row appears to advance forever.

             The duplicate is aria-hidden and its links are tabindex="-1": the
             eight services must be announced once and reachable once, not twice.
             See initAutoplay() in assets/js/main.js for the other half. */ ?>
    <ul class="ep-scroller ep-scroller--4up list-plain">
      <?php for ($copy = 0; $copy < 2; $copy++): ?>
        <?php $dupe = $copy > 0; ?>
        <?php foreach ($svc['cards'] as $card): ?>
          <li class="svc-card-cell"<?= $dupe ? ' aria-hidden="true"' : '' ?>>
            <article class="ep-card svc-card">
              <div class="ep-card__icon ep-card__icon--light">
                <?= ep_icon($card['icon'] ?? 'book-open') ?>
              </div>
              <h3 class="ep-card__title h4"><?= ep_lines($card['title'] ?? '') ?></h3>
              <p class="ep-card__text svc-card__text"><?= esc($card['text'] ?? '') ?></p>
              <a class="link-arrow" href="<?= esc(url('services/' . ($card['slug'] ?? ''))) ?>"
                 <?= $dupe ? 'tabindex="-1"' : '' ?>>
                Learn More <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
                <span class="visually-hidden">about <?= esc(str_replace("\n", ' ', $card['title'] ?? '')) ?></span>
              </a>
            </article>
          </li>
        <?php endforeach; ?>
      <?php endfor; ?>
    </ul>
  </div>
</section>
<?php
$carouselHeading = null;
