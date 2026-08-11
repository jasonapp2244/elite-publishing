<?php
declare(strict_types=1);

/**
 * Our Books — SPEC §C.2, copy §D.4.
 *
 * Hero -> "Our Published Books Collection" (5-up cover carousel with the
 * page-specific red arrows, DECISIONS §10) -> shared services carousel ->
 * shared FAQ -> shared CTA + wizard -> footer.
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'books';
$pageTitle       = 'Our Books';
$pageDescription = 'A collection of books we have helped authors turn from ideas into '
                 . 'professionally published titles, across fiction, non-fiction and more.';
$pageCss         = ['css/p-core.css'];

require __DIR__ . '/includes/head.php';

$books = ep_data('books');
?>

<section class="page-hero hero-wash" aria-labelledby="books-hero-h">
  <div class="container-ep">
    <h1 id="books-hero-h"><?= ep_lines("Stories We've\nHelped Bring To Life") ?></h1>
    <p class="lead page-hero__intro">
      We take pride in transforming ideas into powerful, professionally written books. Each
      project reflects creativity, dedication, and a commitment to quality storytelling.
    </p>
  </div>
</section>

<section class="section" aria-labelledby="collection-h" data-scroller>
  <div class="container-ep">

    <div class="books-head">
      <h2 id="collection-h"><?= ep_lines("Our Published Books\nCollection") ?></h2>
      <p class="books-head__aside">
        A collection of books we've helped authors turn from ideas into professionally
        published titles.
      </p>
    </div>

    <div class="books-carousel">
      <button class="books-nav books-nav--prev" type="button" data-scroll-prev
              aria-label="Previous books">
        <?= ep_icon('arrow-left', ['size' => 20]) ?>
      </button>

      <ul class="ep-scroller books-track list-plain">
        <?php foreach ($books as $book): ?>
          <li>
            <article class="book-card">
              <?= ep_srcset(
                    $book['img'],
                    [420, 840],
                    'Cover of ' . ($book['title'] ?? '') . ' by ' . ($book['author'] ?? ''),
                    840,
                    1191,
                    ['sizes' => '(max-width: 767px) 45vw, (max-width: 1199px) 30vw, 20vw']
                  ) ?>
              <p class="book-card__cap">
                <span class="book-card__author"><?= esc($book['author'] ?? '') ?></span>
                <span class="book-card__title"><?= esc($book['title'] ?? '') ?></span>
              </p>
            </article>
          </li>
        <?php endforeach; ?>
      </ul>

      <button class="books-nav books-nav--next" type="button" data-scroll-next
              aria-label="Next books">
        <?= ep_icon('arrow-right', ['size' => 20]) ?>
      </button>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/components/services-carousel.php'; ?>
<?php require __DIR__ . '/includes/components/faq.php'; ?>
<?php require __DIR__ . '/includes/components/cta-wizard.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
