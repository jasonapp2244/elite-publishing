<?php
declare(strict_types=1);

/**
 * The 4-step consultation form, on its own so two of them can exist.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS IS A PARTIAL
 * ---------------------------------------------------------------------------
 * This markup used to live inside components/cta-wizard.php. The "Publish Your
 * Book" popup needs the same form — same steps, same field names, same handler
 * — and a page can carry both at once: the home page draws the CTA wizard near
 * the bottom AND the popup behind the header button.
 *
 * Copying the markup into the modal was the obvious move and the wrong one. The
 * form is ~130 lines of stepping, validation wiring and error re-population; two
 * copies drift, and the first thing to drift is the bit nobody looks at, which
 * here is the honeypot and the CSRF field. One partial, rendered twice.
 *
 * ---------------------------------------------------------------------------
 * IDS
 * ---------------------------------------------------------------------------
 * Rendering the same form twice on one page means every `id` in it has to be
 * unique per copy — not cosmetic: a <label for> that resolves to the first copy
 * makes the second copy's chips unclickable, and duplicate ids on radio inputs
 * silently merge the two forms' radio groups. $wizardPrefix namespaces every id
 * this file emits. The CTA block keeps 'wz' so nothing about it changes; the
 * popup passes 'pm'.
 *
 * Optional inputs:
 *   $wizardPrefix  string  id prefix, default 'wz'. Must be unique per page.
 *   $wizardSteps   array   step definitions, default the shared wizard data.
 */

$p     = $wizardPrefix ?? 'wz';
$steps = $wizardSteps ?? ep_data_get('shared', 'wizard');

if (empty($steps)) {
    return;
}
?>
<form class="wizard" method="post" action="<?= esc(url('forms/contact-handler.php')) ?>"
      data-wizard novalidate>
  <?= ep_csrf_field() ?>
  <input type="hidden" name="_form" value="wizard">
  <div class="hp-field" aria-hidden="true">
    <label for="<?= esc($p) ?>-website">Leave this field empty</label>
    <input type="text" id="<?= esc($p) ?>-hp" name="ep_hp" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore data-form-type="other" data-bwignore>
  </div>

  <ol class="wizard__progress list-plain" aria-hidden="true">
    <?php foreach ($steps as $i => $_): ?>
      <li class="wizard__seg<?= $i === 0 ? ' is-done' : '' ?>"></li>
    <?php endforeach; ?>
  </ol>

  <?php foreach ($steps as $i => $step): ?>
    <fieldset class="wizard__step<?= $i === 0 ? ' is-active' : '' ?>" data-step="<?= (int) $i ?>">
      <legend class="visually-hidden"><?= esc($step['question'] ?? 'Step ' . ($i + 1)) ?></legend>

      <p class="eyebrow eyebrow--green wizard__eyebrow"><?= esc($step['eyebrow'] ?? '') ?></p>
      <h3 class="wizard__q"><?= esc($step['question'] ?? '') ?></h3>

      <?php if (($step['type'] ?? 'options') === 'fields'): ?>

        <?php
        /* The design shows placeholders only (SPEC §B.13). Placeholders
           are not labels, so every control gets a visually-hidden one.
           Consecutive half-width fields pair into a row. */
        $fields = $step['fields'] ?? [];
        $auto   = ['full_name' => 'name', 'email' => 'email', 'phone' => 'tel'];
        $needed = ['full_name', 'email'];

        for ($f = 0; $f < count($fields); $f++):
            $field = $fields[$f];
            $pair  = ($field['width'] ?? 'full') === 'half'
                  && ($fields[$f + 1]['width'] ?? '') === 'half';
            $group = $pair ? [$field, $fields[$f + 1]] : [$field];
            if ($pair) { $f++; }
        ?>
          <?php if ($pair): ?><div class="wizard__row"><?php endif; ?>
          <?php foreach ($group as $g): ?>
            <?php
            $fid  = $p . '-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($g['name'] ?? ''));
            $req  = in_array($g['name'] ?? '', $needed, true);
            $ph   = $g['placeholder'] ?? '';
            ?>
            <?php
            $err = ep_field_error($g['name'] ?? '', 'wizard');
            $val = ep_old($g['name'] ?? '', 'wizard');
            ?>
            <div class="ep-field">
              <label class="visually-hidden" for="<?= esc($fid) ?>"><?= esc(rtrim($ph, ' .')) ?></label>
              <?php if (($g['type'] ?? 'text') === 'textarea'): ?>
                <textarea class="ep-textarea" id="<?= esc($fid) ?>"
                          name="<?= esc($g['name'] ?? '') ?>" rows="4"
                          placeholder="<?= esc($ph) ?>"
                          <?= $err !== '' ? 'aria-invalid="true" aria-describedby="' . esc($fid) . '-err"' : '' ?>><?= esc($val) ?></textarea>
              <?php else: ?>
                <input class="ep-input" type="<?= esc($g['type'] ?? 'text') ?>"
                       id="<?= esc($fid) ?>" name="<?= esc($g['name'] ?? '') ?>"
                       placeholder="<?= esc($ph) ?>" value="<?= esc($val) ?>"
                       <?= isset($auto[$g['name'] ?? '']) ? 'autocomplete="' . esc($auto[$g['name']]) . '"' : '' ?>
                       <?= $err !== '' ? 'aria-invalid="true" aria-describedby="' . esc($fid) . '-err"' : '' ?>
                       <?= $req ? 'required' : '' ?>>
              <?php endif; ?>
              <?php if ($err !== ''): ?>
                <span class="ep-error" id="<?= esc($fid) ?>-err"><?= esc($err) ?></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <?php if ($pair): ?></div><?php endif; ?>
        <?php endfor; ?>

      <?php else: ?>

        <ul class="wizard__options list-plain<?= (int) ($step['cols'] ?? 1) === 2 ? ' wizard__options--2' : '' ?>">
          <?php foreach (($step['options'] ?? []) as $j => $opt): ?>
            <?php $optId = $p . '-' . esc($step['name'] ?? 'q' . $i) . '-' . $j; ?>
            <?php /* $opt['selected'] is NOT applied. The design draws a
                     chosen chip on each step to show the selected state,
                     but shipping it pre-answered means a visitor who
                     clicks Continue three times submits a genre, a stage
                     and a "$10,000 — $20,000" budget they never picked —
                     and sales cannot tell a real answer from a default.
                     It also made main.js's "choose something" guard
                     unreachable. Steps 1-3 ship unanswered. */ ?>
            <li>
              <input class="wizard__radio visually-hidden" type="radio"
                     id="<?= esc($optId) ?>"
                     name="<?= esc($step['name'] ?? 'q' . $i) ?>"
                     value="<?= esc($opt['label'] ?? '') ?>">
              <label class="wizard__chip" for="<?= esc($optId) ?>">
                <?php if (!empty($opt['emoji'])): ?>
                  <span class="wizard__emoji" aria-hidden="true"><?= esc($opt['emoji']) ?></span>
                <?php endif; ?>
                <?= esc($opt['label'] ?? '') ?>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>

      <?php endif; ?>

      <?php /* The stepping controls only work with JS, so they are
               hidden until `.js` proves it is there. Otherwise the
               degraded form showed seven buttons of which one worked. */ ?>
      <div class="wizard__foot">
        <?php if ($i > 0): ?>
          <button class="wizard__back wizard__step-ctrl" type="button"
                  data-wizard-back aria-label="Previous step">
            <?= ep_icon('arrow-left', ['size' => 18]) ?>
          </button>
        <?php endif; ?>

        <?php if ($i < count($steps) - 1): ?>
          <button class="ep-btn ep-btn--primary wizard__next wizard__step-ctrl"
                  type="button" data-wizard-next>
            <?= esc($step['cta'] ?? 'Continue') ?>
            <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
          </button>
        <?php else: ?>
          <button class="ep-btn ep-btn--primary wizard__next" type="submit">
            <?= esc($step['cta'] ?? 'Get My Free Consultation') ?>
            <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
          </button>
        <?php endif; ?>
      </div>
    </fieldset>
  <?php endforeach; ?>
</form>
<?php
/* Both are per-render inputs, not page state. Left set, the popup would inherit
   the CTA block's prefix on any page that draws both. */
$wizardPrefix = null;
$wizardSteps  = null;
