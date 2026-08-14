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
$pageDescription = 'A selection of titles across the genres we work in — fiction, '
                 . 'non-fiction, children\'s books and more.';
$pageCss         = ['css/p-core.css'];

require __DIR__ . '/includes/head.php';

$books = ep_data('books');
?>

<section class="page-hero hero-wash" aria-labelledby="books-hero-h">
  <div class="container-ep">
    <?php /* Was "Stories We've Helped Bring To Life" over a grid of real
             commercial titles published by other houses — a claim of
             involvement the project cannot support. The page now presents the
             grid as the genres we work in, which is what it can honestly say
             while the covers remain. */ ?>
    <h1 id="books-hero-h"><?= ep_lines("Genres We\nWork In") ?></h1>
    <p class="lead page-hero__intro">
      We work across fiction, non-fiction, children's books and more. Whatever the genre, the
      process covers writing, editing, design, publishing and distribution.
    </p>
  </div>
</section>

<section class="section" aria-labelledby="collection-h" data-scroller>
  <div class="container-ep">

    <?php /* Was "Our Published Books Collection" over "books we've helped
             authors turn from ideas into professionally published titles". The
             covers below are real commercial titles from other houses — see
             data/books.php, every row is 'placeholder' => true — so both lines
             claimed work this company did not do. The section now presents them
             as what they are while the placeholder catalogue stands: examples of
             the kinds of book the service covers. Replacing data/books.php with
             the real catalogue is what lets this go back to naming them as ours;
             it is flagged in the handover report. */ ?>
    <div class="books-head">
      <h2 id="collection-h"><?= ep_lines("The Kinds Of Book\nWe Work On") ?></h2>
      <p class="books-head__aside">
        Examples of the formats and genres our editing, design, publishing and distribution
        services cover, from children's picture books to full-length fiction.
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
