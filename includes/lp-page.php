<?php
declare(strict_types=1);

/**
 * Campaign landing page template — renders lp1, lp2, lp3 and lp4.
 *
 * The four designs are one layout with four sets of copy, so this is one
 * template and data/landing.php holds everything that differs. The page files
 * in the root set $lpKey and require this.
 *
 * Section order, from the designs:
 *   1  hero        — copy left, lead-capture form right, on a deep mint wash
 *   2  book band   — the tilted cover strip, full bleed (shared component)
 *   3  stats       — four claims on black, full bleed
 *   4  services    — heading + two CTAs, then three cards
 *   5  CTA panel   — rounded green block, two buttons
 *   (the footer is lp-footer.php, on solid green)
 *
 * WHERE THE BUTTONS GO. The designs specify no destinations for any of the
 * eleven CTAs on a page. Every one of them targets #lp-form, the hero form,
 * because that is the whole point of a landing page — a visitor who arrives
 * from an ad should convert here, not be handed off to the main site and
 * counted as a bounce. The one button this fits badly is lp4's "Listen to
 * Narrator Voice Samples", which implies audio that does not exist anywhere on
 * the site; it is raised in docs/CLIENT-QUESTIONS.md §48.
 *
 * Required input:
 *   $lpKey  string  'children' | 'christian' | 'marketing' | 'audiobook'
 */

$lp     = ep_data_get('landing', $lpKey);
$shared = ep_data_get('landing', 'shared');

if ($lp === [] || $lp === null) {
    /* A typo in $lpKey would otherwise render a page-shaped hole. Fail loudly
       in development and 404 in production rather than publish an empty page. */
    if (EP_DEBUG) {
        trigger_error("lp-page.php: no data/landing.php entry for '{$lpKey}'", E_USER_WARNING);
    }
    http_response_code(404);
    require EP_ROOT . '/404.php';
    exit;
}

/* Read and consume the submission flash from forms/contact-handler.php, which
   redirects back here (POST-redirect-GET). Same five-minute staleness rule as
   the contact page: anything older is a leftover from a page that never
   rendered it. */
$flash = ep_form_flash();
$flash = ($flash['status'] ?? '') !== '' ? $flash : null;
if (!is_array($flash) || (time() - (int) ($flash['time'] ?? 0)) > 300) {
    $flash = null;
}
$formErrors = is_array($flash['errors'] ?? null) ? $flash['errors'] : [];
$old = static fn(string $f): string => (string) ($flash['old'][$f] ?? '');
?>

<!-- 1 · Hero — copy left, lead-capture form right -->
<section class="lp-hero" aria-labelledby="lp-hero-h">
  <div class="container-ep lp-hero__grid">

    <div class="lp-hero__copy">
      <?php /* No <br> in the h1. The design breaks it across three or four
               lines at 1920, but those are the natural wrap points for this
               measure — hard breaks would survive down to a 360px phone and
               strand single words on their own line. */ ?>
      <h1 id="lp-hero-h"><?= esc($lp['hero']['h1'] ?? '') ?></h1>

      <?php foreach (($lp['hero']['paras'] ?? []) as $para): ?>
        <p class="lp-hero__text"><?= esc($para) ?></p>
      <?php endforeach; ?>

      <p class="lp-hero__actions">
        <a class="ep-btn ep-btn--green" href="#lp-form">
          Publish Your Book <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
        <a class="ep-btn ep-btn--outline" href="#lp-form">
          Free Consultation <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>

    <div class="lp-form-card" id="lp-form">
      <h2 class="h4 lp-form-card__title" id="lp-form-h"><?= esc($shared['form']['title'] ?? '') ?></h2>

      <?php if ($flash !== null): ?>
        <div class="ep-alert lp-form__flash <?= $flash['status'] === 'ok' ? 'ep-alert--ok' : 'ep-alert--err' ?>"
             id="form-result" tabindex="-1"
             role="<?= $flash['status'] === 'ok' ? 'status' : 'alert' ?>">
          <p class="m-0"><?= esc($flash['message'] ?? '') ?></p>
          <?php if ($formErrors !== []): ?>
            <ul class="lp-form__flash-list">
              <?php foreach ($formErrors as $error): ?>
                <li><?= esc($error) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form class="lp-form" method="post" aria-labelledby="lp-form-h"
            action="<?= esc(url('forms/contact-handler.php')) ?>">
        <?= ep_csrf_field() ?>
        <?php /* Not `_form=wizard`, so the handler treats this as a contact
                 form and requires a message — which is what the design's
                 four-field card is. `campaign` tells whoever reads the inbox
                 which ad landed the lead. */ ?>
        <input type="hidden" name="_form" value="lp-contact">
        <input type="hidden" name="campaign" value="<?= esc($lp['slug'] ?? '') ?>">

        <div class="hp-field" aria-hidden="true">
          <label for="lp-website">Leave this field empty</label>
          <input type="text" id="lp-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <?php /* The design shows placeholders only, so every label is visually
                 hidden rather than dropped — a placeholder is not a label. */ ?>
        <div class="ep-field">
          <label class="visually-hidden" for="lp-name">Full Name</label>
          <input class="ep-input" type="text" id="lp-name" name="full_name"
                 placeholder="Full Name" autocomplete="name" required
                 value="<?= esc($old('full_name')) ?>"
                 <?= isset($formErrors['full_name']) ? 'aria-invalid="true"' : '' ?>>
        </div>

        <div class="ep-field">
          <label class="visually-hidden" for="lp-email">Email Address</label>
          <input class="ep-input" type="email" id="lp-email" name="email"
                 placeholder="Email Address" autocomplete="email" required
                 value="<?= esc($old('email')) ?>"
                 <?= isset($formErrors['email']) ? 'aria-invalid="true"' : '' ?>>
        </div>

        <div class="ep-field">
          <label class="visually-hidden" for="lp-phone">Phone Number</label>
          <input class="ep-input" type="tel" id="lp-phone" name="phone"
                 placeholder="Phone Number" autocomplete="tel"
                 value="<?= esc($old('phone')) ?>"
                 <?= isset($formErrors['phone']) ? 'aria-invalid="true"' : '' ?>>
        </div>

        <?php /* `required` matters here. The handler rejects an empty message
                 for every non-wizard form, but without this attribute the
                 browser lets the visitor press Send and they only learn that
                 after a full round-trip — on a landing page, where the form is
                 the entire point, that is a conversion thrown away. The
                 attribute makes the browser say so inline instead; the server
                 rule is unchanged and is still the one that decides. */ ?>
        <div class="ep-field">
          <label class="visually-hidden" for="lp-message">Your message</label>
          <textarea class="ep-textarea lp-form__message" id="lp-message" name="message"
                    rows="4" placeholder="Write your message here..." required
                    <?= isset($formErrors['message']) ? 'aria-invalid="true"' : '' ?>><?= esc($old('message')) ?></textarea>
        </div>

        <button class="ep-btn ep-btn--primary lp-form__submit" type="submit">
          <?= esc($shared['form']['submit'] ?? 'Send Message') ?>
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </button>
      </form>
    </div>

  </div>
</section>

<!-- 2 · Book-cover strip -->
<?php $bandVariant = 'hero'; require __DIR__ . '/components/book-band.php'; ?>

<!-- 3 · Stats on black -->
<section class="lp-stats" aria-label="Elite Publishing by the numbers">
  <div class="container-ep">
    <ul class="lp-stats__grid list-plain">
      <?php foreach (($shared['stats'] ?? []) as $stat): ?>
        <li class="lp-stats__item">
          <p class="lp-stats__value"><?= esc($stat['value']) ?></p>
          <p class="lp-stats__label"><?= esc($stat['label']) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- 4 · Services — heading and CTAs, then three cards -->
<section class="section lp-services" aria-labelledby="lp-services-h">
  <div class="container-ep">

    <div class="lp-services__head">
      <h2 id="lp-services-h"><?= esc($lp['services']['heading'] ?? '') ?></h2>

      <p class="lp-services__actions">
        <a class="ep-btn ep-btn--green" href="#lp-form">
          Publish Your Book <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
        <a class="ep-btn ep-btn--green-outline" href="#lp-form">
          Free Consultation <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>

    <ul class="lp-cards list-plain">
      <?php foreach (($lp['services']['cards'] ?? []) as $card): ?>
        <li>
          <article class="ep-card lp-card">
            <div class="ep-card__icon ep-card__icon--mint">
              <?= ep_icon($card['icon']) ?>
            </div>
            <h3 class="ep-card__title h4 lp-card__title"><?= ep_lines($card['title']) ?></h3>
            <p class="ep-card__text lp-card__text"><?= esc($card['text']) ?></p>
            <?php /* The hairline under the copy is decoration, not a divider
                     between two things, so it is a pseudo-element in CSS and
                     not an <hr> here. */ ?>
          </article>
        </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>

<!-- 5 · Closing CTA -->
<section class="section lp-cta-section" aria-labelledby="lp-cta-h">
  <div class="container-ep">
    <div class="cta-green lp-cta">
      <h2 id="lp-cta-h"><?= ep_lines($lp['cta']['heading'] ?? '') ?></h2>
      <p class="cta-green__intro"><?= esc($lp['cta']['text'] ?? '') ?></p>
      <p class="cta-green__actions">
        <a class="ep-btn ep-btn--white" href="#lp-form">
          <?= esc($lp['cta']['primary'] ?? '') ?>
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
        <a class="ep-btn ep-btn--ghost-white" href="#lp-form">
          Publish Your Book <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/lp-footer.php'; ?>
