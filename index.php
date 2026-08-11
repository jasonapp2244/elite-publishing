<?php
declare(strict_types=1);

/**
 * Home page — SPEC §C.1, copy from §D.3.
 *
 * Sixteen sections. Eight of them are shared partials that pull their own copy
 * from data/ (CONTRACT §3) and are included, never re-authored:
 *   3 book strip · 4 press band · 5 services · 11 stories · 12 testimonials
 *   13 plans · 14 FAQ · 15 CTA + wizard
 * This file authors the six that are unique to the home page:
 *   2 hero · 6 why us · 7 publishing process · 8 genres · 9 portfolio
 *   10 review platforms
 *
 * Copy is transcribed verbatim from the design, including its bugs — the h1
 * really does read "Elite Publishing , The" with a space before the comma
 * (DECISIONS §11, bug 2). Do not correct it.
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'home';
$pageTitle       = 'Turn Your Manuscript Into A Bestseller';
$pageDescription = 'Premium, complete book publishing services for independent authors. '
                 . 'From line editing and cover design to global distribution and audiobooks.';
$pageCss         = ['css/p-home.css'];
$pageJs          = ['js/p-home.js'];
$bodyClass       = 'page-home';

/** Genre filter tabs (SPEC §D.3) mapped to data/books.php 'genre' keys. */
$genreTabs = [
    'fiction'     => 'Fiction',
    'non-fiction' => 'Non-Fiction',
    'romance'     => 'Romance',
    'christian'   => 'Christian',
    'childrens'   => "Children's",
];
$genreActive = 'fiction';

$books   = ep_data('books');
$process = ep_data_get('shared', 'process');
$plats   = ep_data_get('shared', 'platforms');

/** One book cover cell, shared by the Genres and Portfolio tracks. */
$epBookCell = static function (array $book): string {
    $title  = (string) ($book['title'] ?? '');
    $author = (string) ($book['author'] ?? '');

    return '<li class="book-cell" data-genre="' . esc((string) ($book['genre'] ?? '')) . '">'
        . '<figure class="book-card">'
        . ep_srcset(
            (string) ($book['img'] ?? ''),
            [420, 840],
            'Cover of ' . $title,
            840,
            1160,
            ['class' => 'book-card__img', 'sizes' => '(max-width: 575px) 46vw, (max-width: 991px) 30vw, 240px']
        )
        . '<figcaption class="book-card__cap">'
        . '<span class="book-card__author">' . esc($author) . '</span>'
        . '<span class="book-card__title">' . esc($title) . '</span>'
        . '</figcaption></figure></li>';
};

require __DIR__ . '/includes/head.php';
?>

<!-- 2 — Hero (SPEC §C.1 row 2, copy §D.3) -->
<section class="home-hero" aria-labelledby="hero-title">
  <div class="container-ep home-hero__inner">
    <h1 id="hero-title" class="home-hero__title">
      <?= ep_lines("Turn Your Manuscript Into A\nBestseller With Elite Publishing , The\nBest Manuscript ") ?><mark class="home-hero__hl">Publisher!</mark>
    </h1>

    <p class="lead text-muted-ep home-hero__text">
      Premium, complete book publishing services for independent authors. From line editing
      and cover design to global distribution and audiobooks, we help you publish a book with
      professional impact.
    </p>

    <p class="home-hero__actions">
      <a class="ep-btn ep-btn--primary" href="<?= esc(url(ep_page_url('contact'))) ?>">
        Submit Manuscript <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
      <a class="ep-btn ep-btn--outline" href="<?= esc(url('services/books-publishing')) ?>">
        Explore Our Services <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </p>

    <p class="home-hero__rating">
      <span class="home-hero__stars" aria-hidden="true">
        <?= str_repeat(ep_icon('star', ['size' => 18]), 5) ?>
      </span>
      Trusted By 10,000+ Authors Worldwide
    </p>
  </div>
</section>

<?php
// 3 — Book strip. This is the page's LCP element and its only eager image.
$bandVariant = 'hero';
$bandEager   = true;
require __DIR__ . '/includes/components/book-band.php';

// 4 — Press band.
require __DIR__ . '/includes/components/press-band.php';

// 5 — Our Services carousel.
require __DIR__ . '/includes/components/services-carousel.php';
?>

<!-- 6 — Why Us (SPEC §C.1 row 6, copy §D.3) -->
<section class="section home-why" aria-labelledby="why-title">
  <div class="container-ep">
    <div class="panel-green home-why__panel">
      <div class="home-why__copy">
        <p class="eyebrow">WHY US</p>
        <h2 id="why-title">Why Choose Elite Publishing?</h2>
        <p class="home-why__text">
          Navigating the world of self-publishing companies shouldn't be overwhelming. As a
          hybrid publishing platform, Elite Publishing bridges the gap between full author
          control and traditional editorial perfection.
        </p>
        <p class="home-why__actions">
          <a class="ep-btn ep-btn--white" href="<?= esc(url(ep_page_url('contact'))) ?>">
            I want to Publish my Book! Start Now! <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
          </a>
          <a class="ep-btn ep-btn--ghost-white" href="<?= esc(url(ep_page_url('contact'))) ?>">
            Schedule a Call <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
          </a>
        </p>
      </div>

      <ul class="home-why__tiles list-plain">
        <li class="tile-glass">
          <h3 class="h4">100% Royalty &amp; Ownership</h3>
          <p>You keep full rights and maximum royalties on every print, eBook, and audiobook sold.</p>
        </li>
        <li class="tile-glass">
          <h3 class="h4">Industry-Grade Quality</h3>
          <p>Work with veteran editors, award-winning illustrators, and top-tier designers.</p>
        </li>
        <li class="tile-glass">
          <h3 class="h4">Global Print &amp; Digital Reach</h3>
          <p>Distribute directly to Amazon KDP, IngramSpark, Barnes &amp; Noble, and international retailers.</p>
        </li>
      </ul>
    </div>
  </div>
</section>

<?php if (!empty($process['steps'])): ?>
<!-- 7 — Our Publishing Process (SPEC §C.1 row 7, copy §D.3) -->
<section class="home-process band-rounded bg-tint-band" aria-labelledby="process-title">
  <div class="container-ep">

    <div class="section-head">
      <p class="eyebrow"><?= esc($process['eyebrow'] ?? '') ?></p>
      <h2 id="process-title"><?= ep_lines($process['heading'] ?? '') ?></h2>
    </div>

    <div class="proc-card">
      <div class="proc-tabs" role="tablist" aria-label="Publishing process steps" data-proc-tabs>
        <?php foreach ($process['steps'] as $i => $step): ?>
          <?php $on = $i === 0; ?>
          <button class="proc-tab<?= $on ? ' is-active' : '' ?>" type="button"
                  role="tab" id="proc-tab-<?= (int) $i ?>"
                  aria-controls="proc-panel-<?= (int) $i ?>"
                  aria-selected="<?= $on ? 'true' : 'false' ?>"
                  tabindex="<?= $on ? '0' : '-1' ?>">
            <?= esc($step['tab'] ?? '') ?>
          </button>
        <?php endforeach; ?>
      </div>

      <?php foreach ($process['steps'] as $i => $step): ?>
        <div class="proc-panel" role="tabpanel" id="proc-panel-<?= (int) $i ?>"
             aria-labelledby="proc-tab-<?= (int) $i ?>" tabindex="0"
             <?= $i === 0 ? '' : 'hidden' ?>>
          <div class="proc-panel__copy">
            <p class="proc-panel__step">Step <?= esc(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></p>
            <h3 class="proc-panel__title"><?= esc($step['title'] ?? '') ?></h3>
            <p class="text-muted-ep proc-panel__text"><?= esc($step['text'] ?? '') ?></p>

            <div class="proc-nav">
              <button class="proc-nav__btn" type="button" data-proc-prev aria-label="Previous step">
                <?= ep_icon('arrow-left', ['size' => 18]) ?>
              </button>
              <button class="proc-nav__btn" type="button" data-proc-next aria-label="Next step">
                <?= ep_icon('arrow-right', ['size' => 18]) ?>
              </button>
            </div>
          </div>

          <?php if (!empty($step['check'])): ?>
            <ul class="proc-checks list-plain">
              <?php foreach ($step['check'] as $check): ?>
                <li class="proc-check">
                  <span class="proc-check__mark"><?= ep_icon('check', ['size' => 16]) ?></span>
                  <?= esc($check) ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endif; ?>

<!-- 8 — Genres (SPEC §C.1 row 8, copy §D.3) -->
<section class="home-genres" aria-labelledby="genres-title">
  <div class="container-ep">

    <div class="section-head home-rail__head">
      <div class="section-head__text">
        <p class="eyebrow">GENRES</p>
        <h2 id="genres-title">Books We Help Publish</h2>
      </div>

      <div class="genre-tabs" role="group" aria-label="Filter books by genre" data-genre-tabs>
        <?php foreach ($genreTabs as $key => $label): ?>
          <button class="genre-tab<?= $key === $genreActive ? ' is-active' : '' ?>" type="button"
                  data-genre-filter="<?= esc($key) ?>"
                  aria-pressed="<?= $key === $genreActive ? 'true' : 'false' ?>">
            <?= esc($label) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="book-rail" data-scroller>
      <ul class="ep-scroller book-track list-plain"
          data-genre-track data-active="<?= esc($genreActive) ?>">
        <?php foreach ($books as $book): ?>
          <?= $epBookCell($book) ?>
        <?php endforeach; ?>
      </ul>

      <button class="rail-nav rail-nav--prev" type="button" data-scroll-prev
              aria-label="Previous books">
        <?= ep_icon('arrow-left', ['size' => 18]) ?>
      </button>
      <button class="rail-nav rail-nav--next" type="button" data-scroll-next
              aria-label="Next books">
        <?= ep_icon('arrow-right', ['size' => 18]) ?>
      </button>
    </div>

  </div>
</section>

<!-- 9 — Portfolio (SPEC §C.1 row 9, copy §D.3) -->
<section class="home-portfolio" aria-labelledby="portfolio-title">
  <div class="container-ep">

    <div class="section-head home-rail__head">
      <div class="section-head__text">
        <p class="eyebrow">PORTFOLIO</p>
        <h2 id="portfolio-title">Recently Published Titles</h2>
      </div>

      <a class="ep-btn ep-btn--primary" href="<?= esc(url(ep_page_url('our-books'))) ?>">
        View All <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </div>

    <div class="book-rail" data-scroller>
      <ul class="ep-scroller book-track list-plain">
        <?php foreach (array_slice($books, 4) as $book): ?>
          <?= $epBookCell($book) ?>
        <?php endforeach; ?>
        <?php foreach (array_slice($books, 0, 4) as $book): ?>
          <?= $epBookCell($book) ?>
        <?php endforeach; ?>
      </ul>

      <button class="rail-nav rail-nav--prev" type="button" data-scroll-prev
              aria-label="Previous titles">
        <?= ep_icon('arrow-left', ['size' => 18]) ?>
      </button>
      <button class="rail-nav rail-nav--next" type="button" data-scroll-next
              aria-label="Next titles">
        <?= ep_icon('arrow-right', ['size' => 18]) ?>
      </button>
    </div>

  </div>
</section>

<?php if (!empty($plats)): ?>
<!-- 10 — Review platforms (SPEC §B.9, copy §D.3) -->
<section class="home-platforms" aria-label="Review platform ratings">
  <div class="container-ep">
    <ul class="plat-grid list-plain">
      <?php foreach ($plats as $plat): ?>
        <?php $squares = ($plat['style'] ?? 'stars') === 'squares'; ?>
        <li class="plat-card">
          <p class="plat-card__head">
            <span class="plat-badge<?= $squares ? ' plat-badge--trustpilot' : '' ?>" aria-hidden="true">
              <?= $squares
                    ? ep_icon('star', ['size' => 16])
                    : esc(mb_substr((string) ($plat['name'] ?? '?'), 0, 1)) ?>
            </span>
            <span class="plat-card__name"><?= esc($plat['name'] ?? '') ?></span>
          </p>

          <p class="plat-card__score">
            <span class="plat-card__figure"><?= esc($plat['score'] ?? '') ?></span><span class="plat-card__outof">/5</span>
          </p>

          <p class="plat-card__reviews"><?= esc($plat['reviews'] ?? '') ?></p>

          <p class="plat-card__rating<?= $squares ? ' plat-card__rating--squares' : '' ?>" aria-hidden="true">
            <?php for ($s = 0; $s < 5; $s++): ?>
              <span class="plat-card__star"><?= ep_icon('star', ['size' => $squares ? 12 : 16]) ?></span>
            <?php endfor; ?>
          </p>

          <a class="link-arrow" href="<?= esc($plat['href'] ?? '#') ?>">
            <?= esc($plat['link'] ?? '') ?> <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php
// 11 — Author Stories.
require __DIR__ . '/includes/components/author-stories.php';

// 12 — Testimonial marquee.
require __DIR__ . '/includes/components/testimonials.php';

// 13 — Plans.
require __DIR__ . '/includes/components/plans.php';

// 14 — FAQ.
require __DIR__ . '/includes/components/faq.php';

// 15 — CTA + consultation wizard.
require __DIR__ . '/includes/components/cta-wizard.php';

// 16 — Footer + book strip.
require __DIR__ . '/includes/footer.php';
