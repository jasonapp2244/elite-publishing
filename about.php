<?php
declare(strict_types=1);

/**
 * About Our Company — SPEC §C.3, copy §D.5.
 *
 * Hero -> "Championing Independent Voices Worldwide" -> "Our Core Values" ->
 * shared Publishing Journey -> "Why Authors Choose Us" -> shared FAQ ->
 * shared CTA + wizard -> footer.
 *
 * Two copy bugs from the design were reproduced deliberately (DECISIONS §11):
 * the hero paragraph ended mid-sentence on a comma, and the first body
 * paragraph ran "Our Story Elite Publishing was founded…" with no heading
 * break. The content pass asks for correct grammar, so both are now fixed —
 * the hero sentence is completed and "Our Story" is dropped rather than
 * promoted to a heading, since the section already has one.
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'about';
$pageTitle       = 'About Our Company';
$pageDescription = 'Elite Publishing is a professional book writing and publishing company '
                 . 'helping independent authors turn their ideas into published books.';
$pageCss         = ['css/p-core.css'];

require __DIR__ . '/includes/head.php';

// SPEC §D.5 — "Our Core Values", three cards.
$values = [
    [
        'icon'  => 'badge-check',
        'title' => 'Author Empowerment',
        'text'  => 'You retain complete ownership of your intellectual property and final creative approval.',
    ],
    [
        'icon'  => 'shield',
        /* "Uncompromising" claimed a standard nobody outside the company can
           check. "Consistent" is the thing the sentence beneath it describes
           and the thing an author can actually hold us to. */
        'title' => 'Consistent Standards',
        'text'  => 'We treat every manuscript with the same care at each stage of production, whatever its length or genre.',
    ],
    [
        'icon'  => 'eye',
        'title' => 'Transparent Publishing',
        'text'  => 'Clear timelines, straightforward pricing, and dedicated project management with no hidden fees.',
    ],
];

// SPEC §D.5 — "Why Authors Choose Us", numbered 01-05.
$reasons = [
    ['title' => '100% Ownership Rights',
     'text'  => 'Keep full control and rights of your book at all times.'],
    ['title' => 'Experienced Publishing Team',
     'text'  => 'Work with a team that handles editing, design, publishing and marketing.'],
    /* Was "Fast Turnaround Time" — a speed claim with no figure behind it. The
       schedule the client actually receives is the substance, so it says that. */
    ['title' => 'A Schedule You Can Plan Around',
     'text'  => 'You get a dated stage-by-stage schedule at the start, and progress against it as the work runs.'],
    ['title' => 'End-to-End Solutions',
     'text'  => 'From writing, design, publishing, to marketing — everything under one roof.'],
    ['title' => 'Transparent Process',
     'text'  => "Know exactly what's happening at every stage of your book's journey."],
];
?>

<section class="page-hero hero-wash" aria-labelledby="about-hero-h">
  <div class="container-ep">
    <h1 id="about-hero-h"><?= ep_lines("About Our\nCompany") ?></h1>
    <p class="lead page-hero__intro">
      We are a professional book writing and publishing company helping authors turn their
      ideas into published books. From writing and editing to design, publishing and
      distribution, one team handles every stage.
    </p>
  </div>
</section>

<section class="section" aria-labelledby="story-h">
  <div class="container-ep about-story">

    <div class="about-story__media">
      <?= ep_srcset('img/about-story', [640, 1280],
            'An author resting on a stack of books in front of library shelves',
            1280, 1196, ['sizes' => '(max-width: 991px) 100vw, 45vw']) ?>
    </div>

    <div class="about-story__body">
      <h2 id="story-h"><?= ep_lines("Championing Independent\nVoices Worldwide") ?></h2>
      <p>
        Elite Publishing was founded on a simple principle: an independent author should be
        able to reach a professional standard of production without signing away creative
        control or a share of the royalties.
      </p>
      <p>
        We are a team of editors, graphic artists, literary marketers, and publishing
        strategists dedicated to helping writers navigate the modern literary market. Whether
        you want to publish your book for the first time or scale your existing author brand,
        our end-to-end self-publishing packages cover the editing, design, production and
        distribution your book needs to reach readers.
      </p>
      <p class="about-story__actions">
        <a class="ep-btn ep-btn--green" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Get Started
          <span class="ep-btn__badge"><?= ep_icon('arrow-up-right') ?></span>
        </a>
        <a class="ep-btn ep-btn--green-outline" href="<?= esc(url(ep_page_url('contact'))) ?>">
          Book a Free Consultation
        </a>
      </p>
    </div>

  </div>
</section>

<section class="section" aria-labelledby="values-h">
  <div class="container-ep">

    <div class="section-head section-head--center">
      <h2 id="values-h">Our Core Values</h2>
    </div>

    <div class="values-grid">
      <?php foreach ($values as $value): ?>
        <article class="ep-card value-card">
          <div class="value-card__icon"><?= ep_icon($value['icon']) ?></div>
          <h3 class="value-card__title"><?= esc($value['title']) ?></h3>
          <p class="value-card__text"><?= esc($value['text']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/components/journey.php'; ?>

<section class="section" aria-labelledby="why-h">
  <div class="why-band">
    <div class="container-ep">

      <div class="section-head section-head--split why-head">
        <h2 id="why-h"><?= ep_lines("Why Authors\nChoose Us") ?></h2>
        <div class="why-head__aside">
          <?php /* Was "…save time, maximize impact, and help your book succeed
                   globally" — three promises, none of them measurable. What the
                   service actually removes is the coordination, so it says that. */ ?>
          <p>
            We provide end-to-end publishing solutions, so you brief one team instead of
            coordinating an editor, a designer, a printer and a distributor yourself.
          </p>
          <p class="why-head__actions">
            <a class="ep-btn ep-btn--green" href="<?= esc(url('services/books-publishing')) ?>">
              View Services
              <span class="ep-btn__badge"><?= ep_icon('arrow-up-right') ?></span>
            </a>
            <a class="ep-btn ep-btn--green-outline" href="<?= esc(url(ep_page_url('contact'))) ?>">
              Book a Free Consultation
            </a>
          </p>
        </div>
      </div>

      <div class="why-grid">
        <div class="why-grid__media">
          <?= ep_srcset('img/about-why', [640, 1280],
                'A reader holding an open book in front of her face',
                1280, 982, ['sizes' => '(max-width: 991px) 100vw, 46vw']) ?>
        </div>

        <ol class="why-list list-plain">
          <?php foreach ($reasons as $i => $reason): ?>
            <li class="why-list__item">
              <span class="why-list__num" aria-hidden="true"><?= esc(sprintf('%02d', $i + 1)) ?></span>
              <div>
                <h3 class="why-list__title"><?= esc($reason['title']) ?></h3>
                <p class="why-list__text"><?= esc($reason['text']) ?></p>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>

    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/components/faq.php'; ?>
<?php require __DIR__ . '/includes/components/cta-wizard.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
