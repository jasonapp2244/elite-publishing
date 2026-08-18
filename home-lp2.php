<?php
declare(strict_types=1);

/**
 * THE LP2 LAYOUT, kept as a page of its own at /home-lp2.
 *
 * This briefly was index.php. The sixteen-section layout is the home page
 * again, because it is the one that includes the services carousel, the
 * press-logo marquee and the book strip — none of which this page ever
 * included, which is why its slider and logo row appeared to be missing.
 *
 * Preserved unchanged so it can still be viewed and compared. It is noindex:
 * two near-identical pages competing for the same searches is worse than one.
 * It is also absent from the navigation, the footer and sitemap.php —
 * reachable by URL, not advertised.
 *
 * TO SWITCH BACK TO IT: copy this file over index.php, change $pageKey to
 * 'home' and delete the $pageNoIndex line below.
 *
 * ---------------------------------------------------------------------------
 * WHAT THIS REPLACED
 * ---------------------------------------------------------------------------
 * The sixteen-section home page, which is index.php again. It used to be kept
 * alongside it as home-classic.php; that copy is gone, because once index.php
 * became the same layout again the two files were the same page at two URLs —
 * exactly the duplicate the paragraph above is about. It is in the history if
 * it is ever wanted: `git show 1f4a95a:home-classic.php`.
 *
 * ---------------------------------------------------------------------------
 * SECTION ORDER (from lp/index.html)
 * ---------------------------------------------------------------------------
 *   1  hero      photograph, scrim, headline, lead-capture card floating right
 *   2  serve     who we serve — copy left, four icon rows right
 *   3  why       photo left, six-point list right, on a tinted band
 *   4  services  centred intro, then ten expandable cards
 *   5  control   "Stay In Control", three tiles
 *   6  band      dark photo strip, one line, one button
 *   7  promote   copy and three questions left, photo right
 *   8  close     "Let's Talk About Your Book!" — the closing CTA panel
 *      footer
 *
 * That is the source's section list exactly, in the source's order. Nothing is
 * added to it: no testimonial marquee, no FAQ, no consultation wizard. See the
 * note above the footer include.
 *
 * ---------------------------------------------------------------------------
 * WHAT IS REUSED RATHER THAN REBUILT
 * ---------------------------------------------------------------------------
 * Header, footer, book band, publish modal, testimonials, FAQ and the
 * consultation wizard are the site's own components, included as-is. The hero
 * form posts to forms/contact-handler.php exactly as the campaign landing pages
 * do — same CSRF field, same honeypot, same validation, same rate limit, same
 * POST-redirect-GET. Buttons, inputs, alerts, containers, cards and type all
 * come from main.css. The only new stylesheet is p-home-lp2.css, and every
 * selector in it is namespaced `.hl-` so it cannot reach another page.
 *
 * The service cards are generated from EP_SERVICES and data/services.php — the
 * ten real service pages, their real summaries, their real images and their
 * real routes. No service copy is restated here.
 */

require_once __DIR__ . '/includes/config.php';

/* NOT 'home'. That key marks the Home item in the header and footer as the
   current page, and the current page is index.php. A key nothing in ep_nav()
   matches leaves every nav item unhighlighted, which is correct for a page
   that is not in the navigation. */
$pageKey         = 'home-lp2';
$pageTitle       = 'LP2 Home Page';
$pageDescription = 'Complete book publishing services for independent authors. '
                 . 'From line editing and cover design to global distribution and audiobooks.';
$pageCss         = ['css/p-home-lp2.css'];
$bodyClass       = 'page-home-lp';
/* Suppresses the canonical tag and emits `noindex, follow` — see the note at
   the top of this file. */
$pageNoIndex     = true;

$home = ep_data('home');
$svc  = ep_data('services');

/* The submission flash, on the same five-minute staleness rule the contact page
   and the landing pages use. The hero card renders it, so a rejected submission
   reports itself where the visitor was typing rather than in the wizard two
   thousand pixels below. */
$flash = ep_form_flash();
$flash = ($flash['status'] ?? '') !== '' && !ep_form_is('wizard') ? $flash : null;
if (!is_array($flash) || (time() - (int) ($flash['time'] ?? 0)) > 300) {
    $flash = null;
}
$formErrors = is_array($flash['errors'] ?? null) ? $flash['errors'] : [];
$old = static fn(string $f): string => (string) ($flash['old'][$f] ?? '');

require __DIR__ . '/includes/head.php';
?>

<!-- 1 · Hero -->
<section class="hl-hero" aria-labelledby="hl-hero-h">
  <?php /* The background is a real <img>, not a CSS background: it is the LCP
           element, and only a real element can be given fetchpriority and a
           srcset. The scrim over it is a pseudo-element in CSS. */ ?>
  <div class="hl-hero__media" aria-hidden="true">
    <?= ep_srcset(
          $home['hero']['image'] ?? '',
          [1280, 1920],
          (string) ($home['hero']['alt'] ?? ''),
          1920,
          1080,
          ['class' => 'hl-hero__img', 'sizes' => '100vw', 'eager' => true]
        ) ?>
  </div>

  <div class="container-ep hl-hero__grid">
    <div class="hl-hero__copy">
      <h1 id="hl-hero-h" class="hl-hero__title"><?= ep_lines($home['hero']['h1'] ?? '') ?></h1>
      <p class="hl-hero__text"><?= esc($home['hero']['text'] ?? '') ?></p>
    </div>

    <div class="hl-form-card" id="hl-form">
      <h2 class="hl-form-card__title" id="hl-form-h">
        <?= esc($home['hero']['form']['title'] ?? '') ?>
      </h2>

      <?php if ($flash !== null): ?>
        <div class="ep-alert hl-form__flash <?= $flash['status'] === 'ok' ? 'ep-alert--ok' : 'ep-alert--err' ?>"
             id="form-result" tabindex="-1"
             role="<?= $flash['status'] === 'ok' ? 'status' : 'alert' ?>">
          <p class="m-0"><?= esc($flash['message'] ?? '') ?></p>
          <?php if ($formErrors !== []): ?>
            <ul class="hl-form__flash-list">
              <?php foreach ($formErrors as $error): ?>
                <li><?= esc($error) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php /* `lp-contact` so the handler requires a message, plus a
               `campaign` value so
               whoever reads the inbox can tell a home-page enquiry from an ad
               landing. No new back-end and no new field names — adding an
               "Organization" box, which the source form has, would have meant
               editing the handler and the mail body for a value nothing else
               on the site collects. */ ?>
      <form class="hl-form" method="post" aria-labelledby="hl-form-h"
            action="<?= esc(url('forms/contact-handler.php')) ?>">
        <?= ep_csrf_field() ?>
        <input type="hidden" name="_form" value="lp-contact">
        <input type="hidden" name="campaign" value="home">

        <div class="hp-field" aria-hidden="true">
          <label for="hl-website">Leave this field empty</label>
          <input type="text" id="hl-hp" name="ep_hp" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore data-form-type="other" data-bwignore>
        </div>

        <div class="hl-form__row">
          <div class="ep-field">
            <label class="visually-hidden" for="hl-name">Full Name</label>
            <input class="ep-input" type="text" id="hl-name" name="full_name"
                   placeholder="Full Name" autocomplete="name" required
                   value="<?= esc($old('full_name')) ?>"
                   <?= isset($formErrors['full_name']) ? 'aria-invalid="true"' : '' ?>>
          </div>
          <div class="ep-field">
            <label class="visually-hidden" for="hl-phone">Phone Number</label>
            <input class="ep-input" type="tel" id="hl-phone" name="phone"
                   placeholder="Phone Number" autocomplete="tel"
                   value="<?= esc($old('phone')) ?>"
                   <?= isset($formErrors['phone']) ? 'aria-invalid="true"' : '' ?>>
          </div>
        </div>

        <div class="ep-field">
          <label class="visually-hidden" for="hl-email">Email Address</label>
          <input class="ep-input" type="email" id="hl-email" name="email"
                 placeholder="Email Address" autocomplete="email" required
                 value="<?= esc($old('email')) ?>"
                 <?= isset($formErrors['email']) ? 'aria-invalid="true"' : '' ?>>
        </div>

        <div class="ep-field">
          <label class="visually-hidden" for="hl-message">Your message</label>
          <textarea class="ep-textarea hl-form__message" id="hl-message" name="message"
                    rows="4" placeholder="Tell us about your book..." required
                    <?= isset($formErrors['message']) ? 'aria-invalid="true"' : '' ?>><?= esc($old('message')) ?></textarea>
        </div>

        <button class="ep-btn ep-btn--primary hl-form__submit" type="submit">
          <?= esc($home['hero']['form']['submit'] ?? 'Inquire') ?>
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </button>
      </form>
    </div>
  </div>
</section>

<!-- 2 · Who We Serve -->
<section class="section hl-serve" aria-labelledby="hl-serve-h">
  <div class="container-ep hl-serve__grid">
    <div class="hl-serve__copy">
      <h2 id="hl-serve-h"><?= esc($home['serve']['heading'] ?? '') ?></h2>
      <p class="hl-serve__text"><?= esc($home['serve']['text'] ?? '') ?></p>
      <p class="hl-serve__actions">
        <a class="ep-btn ep-btn--primary"
           href="<?= esc(url(ep_page_url($home['serve']['cta']['href'] ?? 'contact'))) ?>">
          <?= esc($home['serve']['cta']['label'] ?? '') ?>
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>

    <ul class="hl-serve__list list-plain">
      <?php foreach (($home['serve']['items'] ?? []) as $item): ?>
        <li class="hl-serve__item">
          <span class="hl-serve__ico" aria-hidden="true"><?= ep_icon($item['icon'] ?? 'book', ['size' => 22]) ?></span>
          <span><?= esc($item['text'] ?? '') ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- 3 · Why authors choose us -->
<section class="hl-why" aria-labelledby="hl-why-h">
  <div class="container-ep hl-why__grid">
    <div class="hl-why__media">
      <?= ep_srcset(
            $home['why']['image'] ?? '',
            [640, 1280],
            (string) ($home['why']['alt'] ?? ''),
            1280,
            960,
            ['class' => 'hl-why__img', 'sizes' => '(max-width: 991px) 92vw, 44vw']
          ) ?>
    </div>

    <div class="hl-why__copy">
      <h2 id="hl-why-h"><?= esc($home['why']['heading'] ?? '') ?></h2>
      <p class="hl-why__text"><?= esc($home['why']['text'] ?? '') ?></p>

      <ul class="hl-checks list-plain">
        <?php foreach (($home['why']['points'] ?? []) as $point): ?>
          <li class="hl-check">
            <span class="hl-check__mark" aria-hidden="true"><?= ep_icon('check', ['size' => 16]) ?></span>
            <span><?= esc($point) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<!-- 4 · Our author services -->
<section class="section hl-services" aria-labelledby="hl-services-h">
  <div class="container-ep">

    <div class="hl-services__head">
      <p class="eyebrow"><?= esc($home['services']['eyebrow'] ?? '') ?></p>
      <h2 id="hl-services-h"><?= esc($home['services']['heading'] ?? '') ?></h2>
      <p class="hl-services__lead"><?= esc($home['services']['lead'] ?? '') ?></p>
      <?php foreach (($home['services']['paras'] ?? []) as $para): ?>
        <p class="hl-services__para"><?= esc($para) ?></p>
      <?php endforeach; ?>
    </div>

    <ul class="hl-cards list-plain">
      <?php foreach (EP_SERVICES as $slug => $label): ?>
        <?php
        $entry = $svc[$slug] ?? [];
        if ($entry === []) {
            continue;   // a slug with no copy would render an empty card
        }
        $summary = (string) ($entry['hero']['text'] ?? '');
        $detail  = (string) ($entry['e2e']['text'] ?? '');
        $image   = (string) ($entry['intro']['image'] ?? '');
        ?>
        <li>
          <article class="hl-card">
            <?php if ($image !== ''): ?>
              <div class="hl-card__media">
                <?= ep_srcset(
                      $image,
                      [640, 1280],
                      '',
                      1280,
                      853,
                      ['class' => 'hl-card__img', 'sizes' => '(max-width: 575px) 92vw, (max-width: 991px) 46vw, 30vw']
                    ) ?>
              </div>
            <?php endif; ?>

            <div class="hl-card__body">
              <h3 class="hl-card__title h4">
                <a class="hl-card__link" href="<?= esc(url('services/' . $slug)) ?>"><?= esc($label) ?></a>
              </h3>
              <p class="hl-card__text"><?= esc($summary) ?></p>

              <?php /* A native <details> rather than a scripted accordion: the
                       source page uses a jQuery toggle, the site already ships
                       one JS bundle, and this needs neither. It opens with
                       JavaScript off, it is keyboard-operable and it is
                       announced correctly, for no bytes at all. */ ?>
              <?php if ($detail !== ''): ?>
                <details class="hl-card__more">
                  <summary class="hl-card__summary">
                    <span class="hl-card__more-label" data-more="<?= esc($home['services']['more'] ?? 'Read More') ?>"
                          data-less="<?= esc($home['services']['less'] ?? 'Read Less') ?>"></span>
                    <?= ep_icon('chevron-down', ['size' => 16, 'class' => 'hl-card__chev']) ?>
                  </summary>
                  <p class="hl-card__detail"><?= esc($detail) ?></p>
                </details>
              <?php endif; ?>
            </div>
          </article>
        </li>
      <?php endforeach; ?>
    </ul>

    <p class="hl-services__foot">
      <a class="ep-btn ep-btn--primary"
         href="<?= esc(url(ep_page_url($home['services']['cta']['href'] ?? 'pricing'))) ?>">
        <?= esc($home['services']['cta']['label'] ?? '') ?>
        <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </p>

  </div>
</section>

<!-- 5 · Stay in control -->
<section class="hl-control" aria-labelledby="hl-control-h">
  <div class="container-ep">
    <div class="hl-control__head">
      <h2 id="hl-control-h"><?= ep_lines($home['control']['heading'] ?? '') ?></h2>
      <p class="hl-control__text"><?= esc($home['control']['text'] ?? '') ?></p>
    </div>

    <ul class="hl-tiles list-plain">
      <?php foreach (($home['control']['tiles'] ?? []) as $tile): ?>
        <li class="hl-tile">
          <span class="hl-tile__ico" aria-hidden="true"><?= ep_icon($tile['icon'] ?? 'check', ['size' => 24]) ?></span>
          <h3 class="hl-tile__title h4"><?= esc($tile['title'] ?? '') ?></h3>
          <p class="hl-tile__text"><?= esc($tile['text'] ?? '') ?></p>
        </li>
      <?php endforeach; ?>
    </ul>

    <p class="hl-control__foot">
      <a class="ep-btn ep-btn--primary"
         href="<?= esc(url(ep_page_url($home['control']['cta']['href'] ?? 'contact'))) ?>"
         data-modal-open="publish-modal">
        <?= esc($home['control']['cta']['label'] ?? '') ?>
        <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </p>
  </div>
</section>

<!-- 6 · Dark photo band -->
<section class="hl-band" aria-labelledby="hl-band-h">
  <div class="hl-band__media" aria-hidden="true">
    <?= ep_srcset(
          $home['band']['image'] ?? '',
          [1280, 1920],
          '',
          1920,
          1080,
          ['class' => 'hl-band__img', 'sizes' => '100vw']
        ) ?>
  </div>
  <div class="container-ep hl-band__inner">
    <h2 id="hl-band-h" class="hl-band__title"><?= esc($home['band']['heading'] ?? '') ?></h2>
    <p class="hl-band__actions">
      <a class="ep-btn ep-btn--ghost-white"
         href="<?= esc(url(ep_page_url($home['band']['cta']['href'] ?? 'contact'))) ?>">
        <?= esc($home['band']['cta']['label'] ?? '') ?>
        <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </p>
  </div>
</section>

<!-- 7 · Publishing and promotion -->
<section class="section hl-promote" aria-labelledby="hl-promote-h">
  <div class="container-ep hl-promote__grid">
    <div class="hl-promote__copy">
      <h2 id="hl-promote-h"><?= ep_lines($home['promote']['heading'] ?? '') ?></h2>
      <p class="hl-promote__intro"><?= esc($home['promote']['intro'] ?? '') ?></p>

      <ul class="hl-checks list-plain">
        <?php foreach (($home['promote']['questions'] ?? []) as $question): ?>
          <li class="hl-check">
            <span class="hl-check__mark" aria-hidden="true"><?= ep_icon('check', ['size' => 16]) ?></span>
            <span><?= esc($question) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <h3 class="hl-promote__sub h4"><?= esc($home['promote']['sub'] ?? '') ?></h3>
      <?php foreach (($home['promote']['paras'] ?? []) as $para): ?>
        <p class="hl-promote__para"><?= esc($para) ?></p>
      <?php endforeach; ?>

      <p class="hl-promote__actions">
        <a class="ep-btn ep-btn--primary"
           href="<?= esc(url(ep_page_url($home['promote']['cta']['href'] ?? 'contact'))) ?>"
           data-modal-open="publish-modal">
          <?= esc($home['promote']['cta']['label'] ?? '') ?>
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>

    <div class="hl-promote__media">
      <?= ep_srcset(
            $home['promote']['image'] ?? '',
            [640, 1280],
            (string) ($home['promote']['alt'] ?? ''),
            1280,
            960,
            ['class' => 'hl-promote__img', 'sizes' => '(max-width: 991px) 92vw, 42vw']
          ) ?>
    </div>
  </div>
</section>

<!-- 8 · Closing band -->
<section class="section hl-close" aria-labelledby="hl-close-h">
  <div class="container-ep">
    <div class="cta-green hl-close__panel">
      <h2 id="hl-close-h"><?= esc($home['close']['heading'] ?? '') ?></h2>
      <p class="cta-green__actions">
        <a class="ep-btn ep-btn--white"
           href="<?= esc(url(ep_page_url($home['close']['primary']['href'] ?? 'contact'))) ?>">
          <?= esc($home['close']['primary']['label'] ?? '') ?>
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
        <?php /* The wizard no longer sits on this page, so this is the route to
                 it — the popup is rendered by footer.php on every page and
                 carries the same four-step form the CTA block used to. */ ?>
        <a class="ep-btn ep-btn--ghost-white"
           href="<?= esc(url(ep_page_url($home['close']['secondary']['href'] ?? 'contact'))) ?>"
           data-modal-open="publish-modal">
          <?= esc($home['close']['secondary']['label'] ?? '') ?>
          <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
        </a>
      </p>
    </div>
  </div>
</section>

<?php
/* Footer, book band and the publish modal.
 *
 * WHAT IS DELIBERATELY NOT HERE. An earlier pass added the site's testimonial
 * marquee, FAQ and consultation-wizard blocks after section 7. The source
 * layout has none of the three, and the instruction is that the home page is
 * LP2 and only LP2 — so they are gone and the page is now the source's eight
 * sections in the source's order.
 *
 * Nothing was lost from the site with them: components/faq.php still runs on
 * about, our-books, pricing and all ten service pages; components/testimonials
 * .php on all ten service pages; components/cta-wizard.php on about, contact,
 * our-books, pricing and the service pages. The wizard is still one click from
 * here through the "Publish Your Book" popup, which footer.php renders below.
 */
require __DIR__ . '/includes/footer.php';
