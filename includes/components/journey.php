<?php
declare(strict_types=1);

/**
 * "Your Publishing Journey" — SPEC §D.2, §C.3 §5, §C.8 §9.
 *
 * Centred heading + intro, then four steps in a row joined by dashed green
 * connectors. The connectors are drawn in CSS between cells rather than as
 * elements, so they disappear cleanly when the row wraps at narrow widths.
 *
 * Optional input:
 *   $journeyTiles  'green' | 'pale'   icon tile treatment. Service pages use
 *                                     56px green tiles, About uses pale.
 *                                     Default 'green'.
 */

$journey = ep_data_get('shared', 'journey');
if (empty($journey['steps'])) {
    return;
}

$tiles = in_array($journeyTiles ?? 'green', ['green', 'pale'], true) ? ($journeyTiles ?? 'green') : 'green';
?>
<section class="section" aria-labelledby="journey-head">
  <div class="container-ep">

    <div class="section-head section-head--center">
      <h2 id="journey-head"><?= ep_lines($journey['heading'] ?? '') ?></h2>
      <?php if (!empty($journey['intro'])): ?>
        <p class="lead text-muted-ep"><?= esc($journey['intro']) ?></p>
      <?php endif; ?>
    </div>

    <ol class="journey list-plain">
      <?php foreach ($journey['steps'] as $i => $step): ?>
        <li class="journey__step">
          <div class="journey__tile journey__tile--<?= esc($tiles) ?>">
            <?= ep_icon($step['icon'] ?? 'dot') ?>
          </div>
          <h3 class="h5 journey__title"><?= esc($step['title'] ?? '') ?></h3>
          <p class="journey__text text-muted-ep"><?= esc($step['text'] ?? '') ?></p>
        </li>
      <?php endforeach; ?>
    </ol>

  </div>
</section>
<?php
$journeyTiles = null;
