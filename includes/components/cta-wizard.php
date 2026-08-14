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
 * The form markup itself is components/wizard-form.php, because the "Publish
 * Your Book" popup renders the same form. See that file.
 *
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
        <?php /* Reads EP_EMAIL, not the copy in data/shared.php. Two spellings of
                 the same address is how one of them ends up wrong — and one of
                 them was: the footer said Info@Elitepublishing.Co while this said
                 info@elitepublishing.co. One constant now feeds both. */ ?>
        <li>
          <span class="cta-block__ico"><?= ep_icon('mail', ['size' => 18]) ?></span>
          <a href="mailto:<?= esc(EP_EMAIL) ?>"><?= esc(EP_EMAIL) ?></a>
        </li>
        <?php /* The address is a third row the design does not draw — it shows
                 only the site and the email. It is here because the client
                 supplied it to be published, and a contact block is where a
                 visitor looks for it. Plain text, not a link: a maps URL would
                 be a destination nobody asked for. Reads EP_ADDRESS rather than
                 $cta so there is one copy of it (see includes/config.php). */ ?>
        <?php if (EP_ADDRESS !== ''): ?>
          <li>
            <span class="cta-block__ico"><?= ep_icon('map-pin', ['size' => 18]) ?></span>
            <span class="cta-block__address"><?= esc(EP_ADDRESS) ?></span>
          </li>
        <?php endif; ?>
      </ul>

      <p class="cta-block__actions">
        <?php foreach (($cta['buttons'] ?? []) as $btn): ?>
          <?php /* "Publish Your Book" opens the popup instead of walking the
                   visitor to another page. The href stays as the fallback: with
                   JS off, or if the dialog element is missing, the click is a
                   plain link to the contact page exactly as before. */ ?>
          <a class="ep-btn ep-btn--<?= esc($btn['variant'] ?? 'primary') ?>"
             href="<?= esc(url($btn['href'] ?? 'contact.php')) ?>"
             <?= ($btn['label'] ?? '') === 'Publish Your Book' ? 'data-modal-open="publish-modal"' : '' ?>>
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
            /* The popup carries the same form and would render the same banner
               under the same id. Whichever renders first claims it; this block
               is above the footer on every page that has both, so it wins and
               components/publish-modal.php stands down. */
            $GLOBALS['ep_wizard_flash_shown'] = true;
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

        <?php $wizardPrefix = 'wz'; $wizardSteps = $steps; ?>
        <?php require __DIR__ . '/wizard-form.php'; ?>
      </div>
    <?php endif; ?>

  </div>
</section>
