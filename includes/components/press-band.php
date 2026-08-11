<?php
declare(strict_types=1);

/**
 * Full-bleed black press band — SPEC §B.12.
 * 296px tall at 1920, centred white eyebrow, then seven mastheads in one row.
 *
 * The mastheads are rendered as styled text rather than traced logo art.
 * Tracing a New York Times masthead produces a trademark liability and a
 * blurry raster; real SVGs need a licence the client has to clear
 * (DECISIONS §13). Swapping text for licensed SVGs later is a change to this
 * file alone.
 *
 * No inputs.
 */

$press = ep_data_get('shared', 'press');
if (empty($press['logos'])) {
    return;
}
?>
<section class="press-band" aria-label="Press coverage">
  <div class="container-ep">
    <p class="eyebrow press-band__eyebrow"><?= esc($press['eyebrow'] ?? '') ?></p>
    <ul class="press-band__row list-plain">
      <?php foreach ($press['logos'] as $logo): ?>
        <li class="press-band__logo"><?= esc($logo) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
