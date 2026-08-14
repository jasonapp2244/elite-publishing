<?php
declare(strict_types=1);

/**
 * Contact Us — SPEC §C.5, copy §D.7.
 *
 * Hero -> shared CTA + wizard -> the green CTA band that is unique to this
 * page (SPEC §B.11 form 2) -> footer. The Figma frame is misspelled
 * "Conatct Us"; frame names never render, so the page is contact.php
 * (DECISIONS §11).
 *
 * The banner above the wizard reports the result of a submission handled by
 * forms/contact-handler.php, which redirects back here (POST-redirect-GET).
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'contact';
$pageTitle       = 'Contact Us';
$pageDescription = 'Get in touch with the Elite Publishing team for questions, project '
                 . 'details or guidance on publishing your book.';
$pageCss         = ['css/p-core.css', 'css/p-contact.css'];

require __DIR__ . '/includes/head.php';

/* Read and consume the submission flash. Anything older than five minutes is
   a stale leftover from a page that never rendered it — drop it silently. */
/* Read through the shared helper, not the session directly: it caches for the
   request so this banner and the wizard's own repopulation both see the flash
   instead of whichever ran first consuming it. */
$flash = ep_form_flash();
$flash = $flash['status'] !== '' ? $flash : null;
if (!is_array($flash) || (time() - (int) ($flash['time'] ?? 0)) > 300) {
    $flash = null;
}
?>

<section class="page-hero hero-wash" aria-labelledby="contact-hero-h">
  <div class="container-ep">
    <h1 id="contact-hero-h"><?= ep_lines("Get In Touch With\nOur Team") ?></h1>
    <p class="lead page-hero__intro">
      We're here to help you with your publishing journey. Reach out to us for any questions,
      project details, or guidance on where to start.
    </p>
  </div>
</section>

<?php if ($flash !== null): ?>
  <div class="container-ep">
    <div class="ep-alert contact-flash <?= $flash['status'] === 'ok' ? 'ep-alert--ok' : 'ep-alert--err' ?>"
         id="form-result" tabindex="-1"
         role="<?= $flash['status'] === 'ok' ? 'status' : 'alert' ?>">
      <p class="m-0"><?= esc($flash['message'] ?? '') ?></p>
      <?php if (!empty($flash['errors'])): ?>
        <ul class="contact-flash__list">
          <?php foreach ($flash['errors'] as $error): ?>
            <li><?= esc($error) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php
// This page renders its own flash banner above, so suppress the wizard's.
$wizardBanner = false;
require __DIR__ . '/includes/components/cta-wizard.php';
?>

<section class="section" aria-labelledby="contact-cta-h">
  <div class="container-ep">
    <div class="cta-green">
      <h2 id="contact-cta-h"><?= ep_lines("Your Story Deserves To\nBe Published.") ?></h2>
      <p class="cta-green__intro">
        Take the next step in your author journey. Our team is ready to turn your idea into a
        professionally published success.
      </p>
      <p class="cta-green__actions">
        <a class="ep-btn ep-btn--white" href="<?= esc(url(ep_page_url('contact'))) ?>"
           data-modal-open="publish-modal">
          Publish Your Book
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
        <a class="ep-btn ep-btn--ghost-white" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Free Consultation
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
