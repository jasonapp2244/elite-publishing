<?php
declare(strict_types=1);

/**
 * "Let's Bring Your Book To Life" + the 4-step consultation wizard.
 * SPEC §B.11 form 1, §B.13 "4-step wizard", §D.2 CTA + wizard.
 *
 * Two-column block on the page background — copy and contact lines left, the
 * white wizard card right. Not a coloured band; that variant is Contact-page
 * only (SPEC §B.11 form 2) and lives in contact.php.
 *
 * The wizard is progressive-enhancement first: with JavaScript off it degrades
 * to one long form with every step visible and a single submit, which still
 * posts to the same handler. main.js hides the inactive steps and wires the
 * Continue/back buttons.
 *
 * No inputs.
 */

/**
 * Optional input:
 *   $wizardBanner  bool  false when the host page already renders the
 *                        submission flash itself (contact.php does), so the
 *                        result is not reported twice on one page.
 */
$cta   = ep_data_get('shared', 'cta');
$steps = ep_data_get('shared', 'wizard');
if (empty($cta) && empty($steps)) {
    return;
}
?>
<section class="section cta-block" aria-labelledby="cta-head">
  <div class="container-ep cta-block__grid">

    <div class="cta-block__copy">
      <h2 id="cta-head"><?= ep_lines($cta['heading'] ?? '') ?></h2>
      <?php if (!empty($cta['intro'])): ?>
        <p class="lead text-muted-ep"><?= esc($cta['intro']) ?></p>
      <?php endif; ?>

      <ul class="cta-block__contacts list-plain">
        <?php if (!empty($cta['site'])): ?>
          <li>
            <span class="cta-block__ico"><?= ep_icon('globe', ['size' => 18]) ?></span>
            <a href="https://<?= esc($cta['site']) ?>" rel="noopener"><?= esc($cta['site']) ?></a>
          </li>
        <?php endif; ?>
        <?php if (!empty($cta['email'])): ?>
          <li>
            <span class="cta-block__ico"><?= ep_icon('mail', ['size' => 18]) ?></span>
            <a href="mailto:<?= esc($cta['email']) ?>"><?= esc($cta['email']) ?></a>
          </li>
        <?php endif; ?>
      </ul>

      <p class="cta-block__actions">
        <?php foreach (($cta['buttons'] ?? []) as $btn): ?>
          <a class="ep-btn ep-btn--<?= esc($btn['variant'] ?? 'primary') ?>"
             href="<?= esc(url($btn['href'] ?? 'contact.php')) ?>">
            <?= esc($btn['label'] ?? '') ?>
            <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
          </a>
        <?php endforeach; ?>
      </p>
    </div>

    <?php if (!empty($steps)): ?>
      <div class="wizard-card" id="wizard">
        <?php
        /* The handler is POST-redirect-GET, so an outcome arrives in the flash.
           Rendering it here means every page carrying the wizard reports the
           result — not just contact.php. */
        $flash = ep_form_flash();
        if (ep_form_is('wizard') && ($wizardBanner ?? true)):
        ?>
          <?php /* id="form-result" is the fragment the handler redirects to, and
                   tabindex="-1" lets main.js move focus here. Without both, a
                   rejected submission scrolls nowhere and says nothing — the
                   banner sits thousands of pixels below the fold. Only one of
                   this and the hero form's alert renders per request (they test
                   ep_form_is()), so the id stays unique. */ ?>
          <p class="ep-alert ep-alert--<?= $flash['status'] === 'ok' ? 'ok' : 'err' ?>"
             id="form-result" tabindex="-1"
             role="<?= $flash['status'] === 'ok' ? 'status' : 'alert' ?>">
            <?= esc($flash['message']) ?>
          </p>
        <?php endif; ?>

        <form class="wizard" method="post" action="<?= esc(url('forms/contact-handler.php')) ?>"
              data-wizard novalidate>
          <?= ep_csrf_field() ?>
          <input type="hidden" name="_form" value="wizard">
          <div class="hp-field" aria-hidden="true">
            <label for="wz-website">Leave this field empty</label>
            <input type="text" id="wz-website" name="website" tabindex="-1" autocomplete="off">
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
                    $fid  = 'wz-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($g['name'] ?? ''));
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
                    <?php $optId = 'wz-' . esc($step['name'] ?? 'q' . $i) . '-' . $j; ?>
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
      </div>
    <?php endif; ?>

  </div>
</section>
