<?php
declare(strict_types=1);

/**
 * "Publish Your Book" popup — the consultation form in a modal dialog.
 *
 * ---------------------------------------------------------------------------
 * WHAT IT IS
 * ---------------------------------------------------------------------------
 * Every "Publish Your Book" button on the site used to be a link to the contact
 * page. That is a page load, a scroll and a fresh decision between the click and
 * the first question, and most of the intent is gone by then. The button now
 * opens this dialog with "What Genre Is Your Book?" already on screen — one
 * click from anywhere on the site to the first answer.
 *
 * The form inside is components/wizard-form.php, the same partial the CTA block
 * renders, posting to the same handler with the same `_form=wizard` value. There
 * is no second endpoint and no second set of validation rules; see that file.
 *
 * ---------------------------------------------------------------------------
 * WHY <dialog>
 * ---------------------------------------------------------------------------
 * showModal() gives the focus trap, the Escape key, the inert background and the
 * ::backdrop for free, and gets them right in ways hand-rolled modals usually do
 * not — notably returning focus to the button that opened it. Baseline in every
 * browser since March 2022.
 *
 * With JS off nothing here is reachable, which is correct: the buttons keep
 * their href and remain plain links to the contact page. Nothing is lost, and
 * the dialog stays closed and out of the accessibility tree.
 *
 * ---------------------------------------------------------------------------
 * WHERE IT IS INCLUDED
 * ---------------------------------------------------------------------------
 * Once per page, from includes/footer.php — after
 * all page content, because a dialog belongs at the end of the body and because
 * the CTA block must get first claim on the flash banner (see below).
 *
 * No inputs.
 */

$steps = ep_data_get('shared', 'wizard');
if (empty($steps)) {
    return;
}

/* If the handler rejected a wizard submission, exactly one banner may carry
   id="form-result". components/cta-wizard.php renders above this and sets the
   flag when it takes it. When the popup is the only wizard on the page — every
   LP page, our-books, pricing — nothing has claimed it, so the popup reports the
   result itself and main.js reopens the dialog on load so the visitor sees it.

   Without this, a popup submission rejected on an LP page would redirect back to
   a page that shows no error at all, and the visitor would have no idea why
   nothing happened. */
$flash     = ep_form_flash();
$ownsFlash = ep_form_is('wizard')
    && ($flash['status'] ?? '') !== ''
    && empty($GLOBALS['ep_wizard_flash_shown']);

if ($ownsFlash) {
    $GLOBALS['ep_wizard_flash_shown'] = true;
}
?>
<dialog class="ep-modal" id="publish-modal" aria-labelledby="publish-modal-h"
        <?= $ownsFlash && ($flash['status'] ?? '') !== 'ok' ? 'data-modal-autoopen' : '' ?>>
  <div class="ep-modal__panel">

    <?php /* type="button" matters: a bare <button> inside a <form> is a submit,
             and this one sits outside the form but inside a dialog — in Chrome a
             default-type button in a dialog with no form is harmless, but the
             attribute costs nothing and removes the question. */ ?>
    <button class="ep-modal__close" type="button" data-modal-close
            aria-label="Close this form">
      <?= ep_icon('close', ['size' => 20]) ?>
    </button>

    <div class="ep-modal__head">
      <p class="eyebrow eyebrow--green">Free Consultation</p>
      <h2 class="h3 ep-modal__title" id="publish-modal-h">Publish Your Book</h2>
      <p class="ep-modal__intro text-muted-ep">
        Answer four quick questions and our editorial team will come back to you
        with a plan and a quote for your book.
      </p>
    </div>

    <?php if ($ownsFlash): ?>
      <p class="ep-alert ep-alert--<?= $flash['status'] === 'ok' ? 'ok' : 'err' ?>"
         id="form-result" tabindex="-1"
         role="<?= $flash['status'] === 'ok' ? 'status' : 'alert' ?>">
        <?= esc($flash['message']) ?>
      </p>
    <?php endif; ?>

    <?php /* 'pm' — the CTA block's copy uses 'wz'. Two forms with the same ids
             on one page would cross-wire their radio groups and their labels. */ ?>
    <?php $wizardPrefix = 'pm'; $wizardSteps = $steps; ?>
    <?php require __DIR__ . '/wizard-form.php'; ?>

  </div>
</dialog>
