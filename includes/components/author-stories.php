<?php
declare(strict_types=1);

/**
 * "What Our Authors Say" — SPEC §C.1 §11, §B.8a.
 *
 * Pale rounded full-bleed band. Two-column header (h2 left, intro + button
 * right-aligned), then three video story cards with a play button and an
 * overlaid avatar + name caption.
 *
 * IMPORTANT — why there is no visible play button or caption here:
 * the thumbnails are cropped from a flattened Figma export, so the designed
 * play button, avatar and "Clara Wen / Everything Remembered" plate are baked
 * into the pixels. Drawing our own on top would double them. Instead the whole
 * card is the control and its accessible name is supplied by aria-label, so
 * keyboard and screen-reader users are not left behind by the fact that the
 * label is pixels.
 *
 * This is a workaround, not the intended end state: text in an image cannot be
 * translated, resized or selected. When the client supplies clean thumbnails
 * (see docs/CLIENT-QUESTIONS.md) restore the real overlay markup — the CSS for
 * it is still in main.css.
 *
 * No inputs.
 */

$stories = ep_data_get('shared', 'stories');
if (empty($stories['cards'])) {
    return;
}
?>
<section class="section band-rounded bg-tint-band stories" aria-labelledby="stories-head">
  <div class="container-ep">

    <div class="section-head section-head--split">
      <div>
        <p class="eyebrow"><?= esc($stories['eyebrow'] ?? '') ?></p>
        <h2 id="stories-head"><?= ep_lines($stories['heading'] ?? '') ?></h2>
      </div>
      <div class="stories__aside">
        <?php if (!empty($stories['intro'])): ?>
          <p class="text-muted-ep stories__intro"><?= esc($stories['intro']) ?></p>
        <?php endif; ?>
        <?php if (!empty($stories['cta'])): ?>
          <a class="ep-btn ep-btn--primary" href="<?= esc(url(ep_page_url('our-books'))) ?>">
            <?= esc($stories['cta']) ?>
            <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <ul class="stories__grid list-plain">
      <?php foreach ($stories['cards'] as $card): ?>
        <?php
        /* A card is only a control if there is something to play. With no video
           the card renders as a plain figure: three focusable buttons that do
           nothing are worse than none, and for a screen-reader user they were
           three identically-named dead ends. Add a 'video' URL to a card in
           data/shared.php and it becomes a real link to that video. */
        $video = $card['video'] ?? '';
        $label = sprintf(
            'Play the author story of %s — %s',
            $card['name'] ?? 'this author',
            $card['title'] ?? ''
        );
        $media = ep_srcset(
            $card['img'] ?? 'img/story-1',
            [480, 960],
            '',
            960,
            544,
            ['class' => 'story-card__img', 'sizes' => '(max-width: 767px) 100vw, 33vw']
        );
        ?>
        <li>
          <?php if ($video !== ''): ?>
            <a class="story-card" href="<?= esc($video) ?>"
               target="_blank" rel="noopener noreferrer"
               aria-label="<?= esc($label) ?>"><?= $media ?></a>
          <?php else: ?>
            <figure class="story-card story-card--static"><?= $media ?></figure>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
