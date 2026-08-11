<?php
declare(strict_types=1);

/**
 * FAQ accordion — SPEC §B.10, §D.2 FAQ.
 *
 * Centred header, then a 1084px-wide column of discrete cards separated by a
 * 13px gap (not a bordered list). Exactly one item open at a time; item 1 is
 * open by default on every page. The open item fills green with white text.
 *
 * Single-open behaviour comes from main.js via [data-accordion] — no JS here.
 *
 * Answers 2-6 do not exist in the design and are drafted; see DECISIONS §3.
 * No inputs.
 */

$faq = ep_data_get('shared', 'faq');
if (empty($faq['items'])) {
    return;
}

/** Unique per include so two accordions on one page cannot collide on id. */
$GLOBALS['epFaqInstance'] = ($GLOBALS['epFaqInstance'] ?? 0) + 1;
$idBase = 'faq' . $GLOBALS['epFaqInstance'];
?>
<section class="section faq" aria-labelledby="<?= esc($idBase) ?>-head">
  <div class="container-ep">

    <div class="section-head section-head--center">
      <p class="eyebrow"><?= esc($faq['eyebrow'] ?? '') ?></p>
      <h2 id="<?= esc($idBase) ?>-head"><?= ep_lines($faq['heading'] ?? '') ?></h2>
    </div>

    <div class="ep-faq" data-accordion>
      <?php foreach ($faq['items'] as $i => $item): ?>
        <?php
        $open    = $i === 0;
        $btnId   = "{$idBase}-b{$i}";
        $panelId = "{$idBase}-p{$i}";
        ?>
        <div class="ep-faq__item">
          <h3 class="ep-faq__q">
            <button class="ep-faq__btn" type="button" id="<?= esc($btnId) ?>"
                    aria-expanded="<?= $open ? 'true' : 'false' ?>"
                    aria-controls="<?= esc($panelId) ?>">
              <span><?= esc($item['q'] ?? '') ?></span>
              <span class="ep-faq__icon" aria-hidden="true">
                <?= ep_icon('plus', ['size' => 20]) ?>
              </span>
            </button>
          </h3>
          <div class="ep-faq__panel" id="<?= esc($panelId) ?>" role="region"
               aria-labelledby="<?= esc($btnId) ?>"<?= $open ? '' : ' hidden' ?>>
            <p><?= esc($item['a'] ?? '') ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
