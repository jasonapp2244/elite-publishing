<?php
declare(strict_types=1);

/**
 * The one template behind all ten service pages.
 *
 * URLs are /services/<slug>, rewritten to service.php?s=<slug> by .htaccess.
 * SPEC §C.8: of the 15 sections only 2 (hero), 5 (intro) and 7 (end-to-end)
 * differ between services — those three read data/services.php; sections 3-4
 * and 8-15 are the shared partials and take no arguments.
 */

require_once __DIR__ . '/includes/config.php';

// --- Route ------------------------------------------------------------------
// An unvalidated ?s= is how you get a half-rendered page, so the slug has to be
// a key of EP_SERVICES *and* carry copy before anything is emitted.
$slug     = is_string($_GET['s'] ?? null) ? strtolower(trim($_GET['s'])) : '';
$services = ep_data('services');

if (!array_key_exists($slug, EP_SERVICES) || empty($services[$slug])) {
    http_response_code(404);

    $notFound = EP_ROOT . '/404.php';
    if (is_file($notFound)) {
        require $notFound;
        exit;
    }

    // No 404.php on disk yet (see docs/reports/dev2.md) — render a correct,
    // on-brand miss rather than a blank 404 body.
    $pageKey         = 'services';
    $pageTitle       = 'Service not found';
    $pageDescription = 'That service page does not exist. Browse the ten publishing services we offer.';
    $pageCss         = ['css/p-service.css'];

    require __DIR__ . '/includes/head.php';
    ?>
    <section class="section svc-404">
      <div class="container-ep">
        <h1>We couldn&rsquo;t find that service</h1>
        <p class="lead text-muted-ep svc-404__text">
          The page you asked for isn&rsquo;t one of our services. Here is everything we do.
        </p>
        <ul class="svc-404__list list-plain">
          <?php foreach (EP_SERVICES as $key => $label): ?>
            <li>
              <a class="link-arrow" href="<?= esc(url('services/' . $key)) ?>">
                <?= esc($label) ?> <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// --- Content ----------------------------------------------------------------
$svc   = $services[$slug];
$hero  = $svc['hero']  ?? [];
$intro = $svc['intro'] ?? [];
$e2e   = $svc['e2e']   ?? [];
$why   = ep_data_get('shared', 'service_why');

$pageKey         = 'service:' . $slug;                 // highlights the nav dropdown item
$pageTitle       = $svc['title'] ?? EP_SERVICES[$slug];
$pageDescription = $svc['meta_desc'] ?? EP_TAGLINE;
$pageCss         = ['css/p-service.css'];
$pageCanonical   = 'services/' . $slug;                // not derivable from the filename
$ogImage         = ($hero['image'] ?? 'img/og-default') . '-1280.jpg';

// Drives the dark navbar variant (SPEC §B.1) from CSS — see p-service.css.
$bodyClass = 'page-service';

require __DIR__ . '/includes/head.php';

/* Read the submission flash left by forms/contact-handler.php.
   This page has TWO posters — the hero card here and the wizard in
   cta-wizard.php — and they share one flash slot. Reading the session directly
   consumed it before the wizard rendered, so a failed wizard submission showed
   its errors inside the hero form instead. ep_form_flash() caches for the
   request and records which form posted, so each one only claims its own.
   Anything older than five minutes is a leftover that never got rendered. */
$flash = ep_form_is('contact') ? ep_form_flash() : null;
if ($flash !== null && (time() - (int) ($flash['time'] ?? 0)) > 300) {
    $flash = null;
}
$formErrors = (array) ($flash['errors'] ?? []);
$formOld    = (array) ($flash['old'] ?? []);

/** Value to put back in a field after a failed submission. */
$old = static fn (string $key): string => (string) ($formOld[$key] ?? '');
?>

<!-- 2 · Hero — full-bleed photo + scrim, 55/45, contact card right (SPEC §C.8) -->
<section class="svc-hero" aria-labelledby="svc-hero-h">
  <div class="svc-hero__media">
    <?= ep_srcset(
        $hero['image'] ?? 'img/svc/' . $slug . '-hero',
        [1280, 1920],
        '',
        1920,
        866,
        ['eager' => true, 'sizes' => '100vw']
    ) ?>
  </div>

  <div class="container-ep svc-hero__inner">

    <div class="svc-hero__copy">
      <h1 id="svc-hero-h"><?= ep_lines($hero['h1'] ?? ($svc['title'] ?? '')) ?></h1>

      <?php if (!empty($hero['text'])): ?>
        <p class="svc-hero__text"><?= esc($hero['text']) ?></p>
      <?php endif; ?>

      <p class="svc-hero__actions">
        <a class="ep-btn ep-btn--white" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Publish Your Book <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
        <a class="ep-btn ep-btn--ghost-white" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Free Consultation <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>

    <?php /* SPEC §B.13 "Hero contact form". Posts to Dev 3's shared handler. */ ?>
    <div class="svc-form-card">
      <h2 class="h4 svc-form-card__title" id="svc-form-h">Start Your Book Today</h2>

      <?php if ($flash !== null): ?>
        <div class="ep-alert svc-form__flash <?= $flash['status'] === 'ok' ? 'ep-alert--ok' : 'ep-alert--err' ?>"
             id="form-result" tabindex="-1"
             role="<?= $flash['status'] === 'ok' ? 'status' : 'alert' ?>">
          <p class="m-0"><?= esc($flash['message'] ?? '') ?></p>
          <?php if ($formErrors !== []): ?>
            <ul class="svc-form__flash-list">
              <?php foreach ($formErrors as $error): ?>
                <li><?= esc($error) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form class="svc-form" method="post" aria-labelledby="svc-form-h"
            action="<?= esc(url('forms/contact-handler.php')) ?>">
        <?= ep_csrf_field() ?>
        <input type="hidden" name="_form" value="hero-contact">
        <input type="hidden" name="service" value="<?= esc($svc['title'] ?? '') ?>">

        <div class="hp-field" aria-hidden="true">
          <label for="svc-website">Leave this field empty</label>
          <input type="text" id="svc-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <?php /* The design shows placeholders only, so every label is visually hidden. */ ?>
        <div class="ep-field">
          <label class="visually-hidden" for="svc-name">Full Name</label>
          <input class="ep-input" type="text" id="svc-name" name="full_name"
                 placeholder="Full Name" autocomplete="name" required
                 value="<?= esc($old('full_name')) ?>"
                 <?= isset($formErrors['full_name']) ? 'aria-invalid="true"' : '' ?>>
        </div>

        <div class="ep-field">
          <label class="visually-hidden" for="svc-email">Email Address</label>
          <input class="ep-input" type="email" id="svc-email" name="email"
                 placeholder="Email Address" autocomplete="email" required
                 value="<?= esc($old('email')) ?>"
                 <?= isset($formErrors['email']) ? 'aria-invalid="true"' : '' ?>>
        </div>

        <div class="ep-field">
          <label class="visually-hidden" for="svc-phone">Phone Number</label>
          <input class="ep-input" type="tel" id="svc-phone" name="phone"
                 placeholder="Phone Number" autocomplete="tel"
                 value="<?= esc($old('phone')) ?>"
                 <?= isset($formErrors['phone']) ? 'aria-invalid="true"' : '' ?>>
        </div>

        <?php /* Same as the landing pages: the handler requires a message on
                 this form, so the markup has to say so or the visitor finds out
                 only after a round-trip. See includes/lp-page.php. */ ?>
        <div class="ep-field">
          <label class="visually-hidden" for="svc-message">Your message</label>
          <textarea class="ep-textarea svc-form__message" id="svc-message" name="message"
                    rows="3" placeholder="Write your message here..." required
                    <?= isset($formErrors['message']) ? 'aria-invalid="true"' : '' ?>><?= esc($old('message')) ?></textarea>
        </div>

        <button class="ep-btn ep-btn--primary svc-form__submit" type="submit">
          Send Message <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </button>
      </form>
    </div>

  </div>
</section>

<!-- 3 · Book-cover strip -->
<?php $bandVariant = 'hero'; require __DIR__ . '/includes/components/book-band.php'; ?>

<!-- 4 · Press band -->
<?php require __DIR__ . '/includes/components/press-band.php'; ?>

<!-- 5 · Intro — image left, copy right -->
<?php if (!empty($intro)): ?>
<section class="svc-intro" aria-labelledby="svc-intro-h">
  <div class="container-ep svc-intro__grid">

    <div class="svc-intro__media">
      <?= ep_srcset(
          $intro['image'] ?? 'img/svc/' . $slug . '-intro',
          [640, 1280],
          '',
          1280,
          1195,
          ['sizes' => '(min-width: 992px) 42vw, 92vw']
      ) ?>
    </div>

    <div class="svc-intro__copy">
      <h2 id="svc-intro-h"><?= ep_lines($intro['h2'] ?? '') ?></h2>

      <div class="svc-prose">
        <?php foreach (($intro['paras'] ?? []) as $para): ?>
          <p><?= esc($para) ?></p>
        <?php endforeach; ?>
      </div>

      <p class="svc-actions">
        <a class="ep-btn ep-btn--green" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Get Started <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
        <a class="ep-btn ep-btn--green-outline" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Book a Free Consultation <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>

  </div>
</section>
<?php endif; ?>

<!-- 6 · Why Us — centred green panel, identical on all ten -->
<?php if (!empty($why)): ?>
<section class="svc-why" aria-labelledby="svc-why-h">
  <div class="container-ep">
    <div class="panel-green svc-why__panel">

      <?php if (!empty($why['eyebrow'])): ?>
        <p class="eyebrow svc-why__eyebrow"><?= esc($why['eyebrow']) ?></p>
      <?php endif; ?>

      <h2 id="svc-why-h"><?= ep_lines($why['heading'] ?? '') ?></h2>

      <?php if (!empty($why['text'])): ?>
        <p class="svc-why__text"><?= esc($why['text']) ?></p>
      <?php endif; ?>

      <?php if (!empty($why['chips'])): ?>
        <ul class="svc-why__chips list-plain">
          <?php foreach ($why['chips'] as $chip): ?>
            <li class="svc-why__chip">
              <span class="svc-why__dot" aria-hidden="true"></span><?= esc($chip) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php if (!empty($why['cta']['label'])): ?>
        <p class="svc-why__cta">
          <a class="ep-btn ep-btn--white" href="<?= esc(url($why['cta']['href'] ?? 'about.php')) ?>">
            <?= esc($why['cta']['label']) ?> <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
          </a>
        </p>
      <?php endif; ?>

    </div>
  </div>
</section>
<?php endif; ?>

<!-- 7 · End-to-end — copy left, image right -->
<?php if (!empty($e2e)): ?>
<section class="svc-e2e" aria-labelledby="svc-e2e-h">
  <div class="container-ep svc-e2e__grid">

    <div class="svc-e2e__copy">
      <h2 id="svc-e2e-h"><?= ep_lines($e2e['h2'] ?? '') ?></h2>

      <?php if (!empty($e2e['text'])): ?>
        <p class="svc-e2e__text"><?= esc($e2e['text']) ?></p>
      <?php endif; ?>

      <?php
      /* 'label' is null on blog-article-writing and audio-book-production —
         there the list sits straight under the paragraph (SPEC §D.10). */
      $offerLabel = $e2e['label'] ?? null;
      ?>
      <?php if ($offerLabel !== null && $offerLabel !== ''): ?>
        <p class="svc-offers__label" id="svc-offers-h"><?= esc($offerLabel) ?></p>
      <?php endif; ?>

      <?php if (!empty($e2e['offers'])): ?>
        <ul class="svc-offers list-plain"
            <?= ($offerLabel !== null && $offerLabel !== '') ? 'aria-labelledby="svc-offers-h"' : '' ?>>
          <?php foreach ($e2e['offers'] as $offer): ?>
            <li class="svc-offers__item">
              <span class="svc-offers__dot" aria-hidden="true"></span><?= esc($offer) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <p class="svc-actions">
        <a class="ep-btn ep-btn--green" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Get Started <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
        <a class="ep-btn ep-btn--green-outline" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Book a Free Consultation <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>

    <div class="svc-e2e__media">
      <?= ep_srcset(
          $e2e['image'] ?? 'img/svc/' . $slug . '-e2e',
          [640, 1280],
          '',
          1280,
          1195,
          ['sizes' => '(min-width: 992px) 42vw, 92vw']
      ) ?>
    </div>

  </div>
</section>
<?php endif; ?>

<!-- 8-14 · Shared sections, byte-identical on all ten pages -->
<?php require __DIR__ . '/includes/components/services-carousel.php'; ?>
<?php require __DIR__ . '/includes/components/journey.php'; ?>
<?php require __DIR__ . '/includes/components/author-stories.php'; ?>
<?php require __DIR__ . '/includes/components/testimonials.php'; ?>
<?php require __DIR__ . '/includes/components/plans.php'; ?>
<?php require __DIR__ . '/includes/components/faq.php'; ?>
<?php require __DIR__ . '/includes/components/cta-wizard.php'; ?>

<!-- 15 · Footer + book strip -->
<?php require __DIR__ . '/includes/footer.php'; ?>
