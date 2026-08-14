<?php
declare(strict_types=1);

/**
 * THE PREVIOUS HOME PAGE, kept as a page of its own at /home-classic.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS EXISTS
 * ---------------------------------------------------------------------------
 * index.php is now the LP2 layout. This is the sixteen-section home page that
 * was there before it, unchanged, so it can still be viewed, compared against
 * the new one, and switched back to.
 *
 * TO SWITCH BACK: copy this file over index.php, then change $pageKey back to
 * 'home' and delete the $pageNoIndex line below. Nothing else needs touching —
 * assets/css/p-home.css and assets/js/p-home.js are still on disk, untouched,
 * and are still exactly what this page needs.
 *
 * ---------------------------------------------------------------------------
 * WHY IT IS noindex
 * ---------------------------------------------------------------------------
 * It repeats most of the real home page and large parts of the service and
 * pricing pages. Two near-identical pages competing for the same searches is
 * worse than one, so this is served with `noindex, follow` and carries no
 * canonical tag. It is also deliberately absent from the navigation, the
 * footer and sitemap.php — reachable by URL, not advertised.
 *
 * Delete $pageNoIndex to publish it properly, but give it its own title and
 * description first.
 *
 * ---------------------------------------------------------------------------
 * ORIGINAL NOTES
 * ---------------------------------------------------------------------------
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

/* NOT 'home'. That key marks the Home item in the header and footer as the
   current page, and the current page is index.php — the LP2 layout. A key
   nothing in ep_nav() matches leaves every nav item unhighlighted, which is
   correct for a page that is not in the navigation. */
$pageKey         = 'home-classic';
$pageTitle       = 'Previous Home Page';
$pageDescription = 'Complete book publishing services for independent authors. '
                 . 'From line editing and cover design to global distribution and audiobooks.';
/* Both still on disk and still unmodified — this page is the only thing that
   loads either of them now. */
$pageCss         = ['css/p-home.css'];
$pageJs          = ['js/p-home.js'];
$bodyClass       = 'page-home';
/* Suppresses the canonical tag and emits `noindex, follow` — see the note at
   the top of this file. */
$pageNoIndex     = true;

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
      <?php /* Was "…Into A Bestseller With Elite Publishing , The Best Manuscript
               Publisher!". Two unsupported claims in one line: no publisher can
               promise a bestseller, and "the best" is a ranking nobody audits.
               The structure is unchanged — three lines and the highlighted last
               word the design draws — so only the claim went. */ ?>
      <?= ep_lines("Turn Your Manuscript Into A\nProfessionally Published Book\nWith Elite ") ?><mark class="home-hero__hl">Publishing!</mark>
    </h1>

    <p class="lead text-muted-ep home-hero__text">
      Complete book publishing services for independent authors. From line editing and cover
      design to distribution and audiobooks, we take your manuscript through every stage of
      publication.
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
      <?php /* Was "Trusted By 10,000+ Authors Worldwide". An author count nobody
               can source is exactly the kind of claim this line was asked to
               stop making, so it now says what the company does instead of how
               many people it has done it for. The five stars beside it are the
               other half of the same claim — see the note in assets/css and the
               handover report; deleting the .home-hero__stars span removes it. */ ?>
      Publishing Support For Independent Authors Worldwide
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
          <?php /* "maximum royalties" removed — a vague superlative sitting next
                   to a specific figure. The 100% is the company's stated policy
                   and is repeated in the FAQ, so it stays; see the handover note
                   about backing it in the Terms, which currently say nothing
                   about royalties or copyright at all. */ ?>
          <h3 class="h4">100% Royalty &amp; Ownership</h3>
          <p>You keep your rights and your royalties on every print, eBook, and audiobook sold.</p>
        </li>
        <li class="tile-glass">
          <h3 class="h4">A Full Production Team</h3>
          <p>Work with editors, illustrators, and designers across every stage of your book.</p>
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
        <?php /* Was "Recently Published Titles", which claims these books were
                 published by Elite Publishing. The covers in data/books.php are
                 real commercial titles from other houses — Project Hail Mary,
                 Judge Stone — so that claim cannot stand. "Selected Titles"
                 states no authorship. The covers themselves still imply one; see
                 the handover report. */ ?>
        <h2 id="portfolio-title">Selected Titles</h2>
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
        <?php
        $squares = ($plat['style'] ?? 'stars') === 'squares';
        /* The platform's own mark when the client supplied one. Everything
           without a file keeps the initial-on-a-dark-tile fallback all four
           used to show, so a missing icon degrades to what was there before
           rather than to a hole. */
        $icon = (string) ($plat['icon'] ?? '');
        $fill = !empty($plat['icon_fill']);
        $badgeClass = $icon !== ''
            ? ($fill ? ' plat-badge--fill' : ' plat-badge--img')
            : ($squares ? ' plat-badge--trustpilot' : '');
        ?>
        <li class="plat-card">
          <p class="plat-card__head">
            <span class="plat-badge<?= $badgeClass ?>" aria-hidden="true">
              <?php if ($icon !== ''): ?>
                <img src="<?= esc(asset($icon)) ?>" alt=""
                     width="<?= $fill ? 28 : 18 ?>" height="<?= $fill ? 28 : 18 ?>"
                     loading="lazy" decoding="async">
              <?php else: ?>
                <?= $squares
                      ? ep_icon('star', ['size' => 16])
                      : esc(mb_substr((string) ($plat['name'] ?? '?'), 0, 1)) ?>
              <?php endif; ?>
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
