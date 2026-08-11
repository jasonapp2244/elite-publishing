<?php
declare(strict_types=1);

/**
 * Elite Publishing — book catalogue.
 *
 * Ten covers. The design captions every single card with the same placeholder
 * pair (Elena Hartwell / The Last Cartographer), so real per-book metadata does
 * not exist in the export. Titles and authors here are the covers identified in
 * SPEC §E.1; 'genre' is assigned by the build to populate the home page Genres
 * filter (SPEC §C.1 §8) and is not from the design.
 *
 * Every row is 'placeholder' => true (DECISIONS §6) — the client replaces this
 * whole file with the real catalogue in one edit.
 *
 * 'img' has no extension or width; ep_srcset() assembles those (CONTRACT §5).
 */

return [
    [
        'img'         => 'img/books/book-01',
        'title'       => "It's Not Easy Being a Bunny",
        'author'      => 'Marilyn Sadler',
        'genre'       => 'childrens',
        'placeholder' => true,
    ],
    [
        'img'         => 'img/books/book-02',
        'title'       => 'Judge Stone',
        'author'      => 'Viola Davis & James Patterson',
        'genre'       => 'fiction',
        'placeholder' => true,
    ],
    [
        'img'         => 'img/books/book-03',
        'title'       => 'Project Hail Mary',
        'author'      => 'Andy Weir',
        'genre'       => 'fiction',
        'placeholder' => true,
    ],
    [
        'img'         => 'img/books/book-04',
        'title'       => 'The Correspondent',
        'author'      => 'Virginia Evans',
        'genre'       => 'fiction',
        'placeholder' => true,
    ],
    [
        'img'         => 'img/books/book-05',
        'title'       => 'Game On',
        'author'      => 'Navessa Allen',
        'genre'       => 'romance',
        'placeholder' => true,
    ],
    [
        'img'         => 'img/books/book-06',
        'title'       => 'Dear Debbie',
        'author'      => 'Freida McFadden',
        'genre'       => 'non-fiction',
        'placeholder' => true,
    ],
    [
        'img'         => 'img/books/book-07',
        'title'       => 'Theo of Golden',
        'author'      => 'Allen Levi',
        'genre'       => 'christian',
        'placeholder' => true,
    ],
    [
        'img'         => 'img/books/book-08',
        'title'       => 'Warriors',
        'author'      => 'Erin Hunter',
        'genre'       => 'childrens',
        'placeholder' => true,
    ],
    [
        // SPEC §E.1 identifies this cover only as "Freida McFadden (pink)" —
        // the title is not legible in the export. Title is a best guess.
        'img'         => 'img/books/book-09',
        'title'       => 'The Boyfriend',
        'author'      => 'Freida McFadden',
        'genre'       => 'romance',
        'placeholder' => true,
    ],
    [
        // SPEC §E.1 records this title only as "My Husband's…" — the rest of
        // the title is cropped in the export.
        'img'         => 'img/books/book-10',
        'title'       => "My Husband's…",
        'author'      => 'Alice Feeney',
        'genre'       => 'fiction',
        'placeholder' => true,
    ],
];
