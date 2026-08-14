<?php
declare(strict_types=1);

/**
 * Home page copy — the LP2 layout.
 *
 * ---------------------------------------------------------------------------
 * WHERE THIS CAME FROM, AND WHY THE WORDS ARE NOT THE SOURCE'S
 * ---------------------------------------------------------------------------
 * The brief supplied a landing page in lp/ and asked for it to become the home
 * page. lp/index.html is not a design export — it is a complete saved copy of
 * twinepublishing.com, a different and real company: its logo files, its phone
 * number, its info@ address, its photography and its copyright line are all in
 * that folder.
 *
 * The LAYOUT is what was taken: section order, the hero with a lead form
 * floating over a photo, "Who We Serve", the six-point why-us list, the
 * expandable service cards, the three-tile control band, the dark photo CTA
 * strip, the copy-plus-photo block. That structure is generic marketing
 * furniture and is what the brief was actually asking for.
 *
 * The WORDS and the IMAGES are Elite Publishing's own — taken from
 * data/services.php, data/shared.php and assets/img/, which the site already
 * owns. Republishing a competitor's marketing prose and photographs on a live
 * production site is not something the brief can authorise, and none of it
 * would have been true of this company anyway: the source page sells Christian
 * publishing, claims access to Inspire Media Group's email lists, and quotes
 * that company's terms.
 *
 * If Elite Publishing does hold rights to the Twine content, every string on
 * the home page is in this one file and swapping it in touches nothing else.
 *
 * ---------------------------------------------------------------------------
 * CLAIMS
 * ---------------------------------------------------------------------------
 * Same rule as the rest of data/: nothing here asserts a ranking, an award, a
 * client count, a partnership or a result. "100% royalty and ownership" and the
 * distribution list are the company's own stated policy and are already
 * published in the FAQ and on the service pages; everything else describes what
 * the service does rather than how well it does it.
 */

return [

    /* ------------------------------------------------------------------ hero
       Full-bleed photograph, dark scrim, headline bottom-left, lead-capture
       card floating right — the source layout's one genuinely distinctive
       idea. The headline is the one already agreed for this page in the
       content pass; it is not re-litigated here. */
    'hero' => [
        /* Chosen for where its subject sits, not for what it depicts: the
           headline lands bottom-left and the form card floats top-right, so
           this frame's open shelving on the left and its single figure on the
           right are the ones that do not fight either. books-publishing-hero,
           the obvious pick, is a face in close-up directly behind the h1. */
        'image' => 'img/svc/creative-content-writing-hero',
        'alt'   => '',
        'h1'    => "Book Publishing That Brings\nYour Manuscript To Life",
        'text'  => 'From line editing and cover design to global distribution and audiobooks, '
                 . 'we take your manuscript through every stage of publication.',
        'form'  => [
            'title'  => 'Schedule a Call to Get Your Publishing Questions Answered',
            'submit' => 'Inquire',
        ],
    ],

    /* ------------------------------------------------------------- who we serve
       Four author situations, not four claims about audience size. Icons are
       ep_icon() names — see includes/functions.php. */
    'serve' => [
        'heading' => 'Who We Serve',
        'text'    => 'If you have a manuscript, an outline, or a book already in print, '
                   . 'there is a route through publication that fits where you are.',
        'cta'     => ['label' => 'Connect With Us', 'href' => 'contact'],
        'items'   => [
            ['icon' => 'quill',       'text' => 'First-time authors with a finished or part-finished manuscript'],
            ['icon' => 'book-open',   'text' => 'Published authors relaunching, refreshing or expanding an existing title'],
            ['icon' => 'users',       'text' => 'Business owners and speakers publishing a book alongside their work'],
            ['icon' => 'layers',      'text' => 'Authors who want one team across editing, design, publishing and marketing'],
        ],
    ],

    /* ----------------------------------------------------------------- why us
       Six points, each one a thing the company does or a term it offers.
       The first two are its stated policy and appear in the FAQ as well; the
       rest describe the service. Nothing here is a superlative. */
    'why' => [
        'heading' => 'Why Authors Choose Elite Publishing',
        'text'    => 'You keep ownership of your work and you choose the services you need. '
                   . 'We provide the team, the process and the distribution to take it from '
                   . 'manuscript to finished book.',
        'image'   => 'img/about-why',
        'alt'     => 'An author working on a manuscript',
        'points'  => [
            'You keep 100% of the royalties on every print, eBook and audiobook sold',
            'Copyright stays in your name, on every format we produce',
            'Fixed package pricing, published on the pricing page before you commit',
            'Editors, illustrators, designers and narrators on one production team',
            'Distribution to Amazon KDP, IngramSpark, Barnes & Noble and international retailers',
            'One point of contact from the first edit through to launch',
        ],
    ],

    /* --------------------------------------------------------------- services
       Rendered from EP_SERVICES and data/services.php rather than restated
       here — ten cards whose titles, summaries, expanded text, images and
       links all come from the service pages themselves. Two copies of a
       service description is how one of them goes stale.

       Only the section furniture lives here. */
    'services' => [
        'eyebrow' => 'OUR AUTHOR SERVICES',
        'heading' => 'Everything Your Book Needs, In One Place',
        'lead'    => 'Author ownership comes first.',
        'paras'   => [
            'Elite Publishing does not take ownership of your manuscript. You hold the '
            . 'copyright throughout, and the files and artwork we produce are yours to '
            . 'keep and to use wherever you publish next.',
            'Services are modular. Take the whole route from manuscript to marketplace, or '
            . 'the single stage your book is missing — scope, timeline and deliverables are '
            . 'agreed with you before any work starts.',
        ],
        'cta'     => ['label' => 'View Pricing Guide', 'href' => 'pricing'],
        'more'    => 'Read More',
        'less'    => 'Read Less',
    ],

    /* ---------------------------------------------------------------- control
       The "Stay In Control" band. Three tiles, each a term of engagement. */
    'control' => [
        'heading' => "Stay In Control.\nWe Work Alongside You.",
        'text'    => 'You decide what your book needs and how far you want to take it. '
                   . 'We bring the editorial, design and distribution work, and keep you in '
                   . 'the decision on every one of them.',
        'tiles'   => [
            ['icon' => 'shield',      'title' => 'Clear terms',    'text' => 'Fixed scope and fixed price, agreed in writing before work begins.'],
            ['icon' => 'badge-check', 'title' => 'Built to fit',   'text' => 'Matched to your goals, your timeline and your budget.'],
            ['icon' => 'lightbulb',   'title' => 'Author-led',     'text' => 'Your voice, your decisions, at every stage of production.'],
        ],
        'cta'     => ['label' => 'Begin the Journey', 'href' => 'contact'],
    ],

    /* ------------------------------------------------------------------- band
       Dark full-bleed photo strip with one line and one button. */
    'band' => [
        /* Faceless by choice — the heading is centred over the middle of the
           frame, which is where every other hero image puts a person's face. */
        'image'   => 'img/svc/book-illustration-hero',
        'heading' => 'Ready To Talk About Your Book?',
        'cta'     => ['label' => 'Schedule a Discussion', 'href' => 'contact'],
    ],

    /* ---------------------------------------------------------------- promote
       Copy left, photo right. The three questions are the source layout's
       device for qualifying a visitor, kept because it works; the questions
       themselves are about this company's services. */
    'promote' => [
        'heading' => "Publishing And Promotion Services\nFor Independent Authors",
        'intro'   => 'Elite Publishing helps independent authors take a manuscript through '
                   . 'editing, design, production and distribution, and then put it in front '
                   . 'of readers.',
        'questions' => [
            'Do you have a manuscript you are ready to publish for the first time?',
            'Are you looking to produce an eBook, a paperback, or an audiobook edition?',
            'Do you have a book in print that needs a new cover, a new edition, or a relaunch?',
        ],
        'sub'     => 'Full-service publishing, made straightforward',
        'paras'   => [
            'Publishing a book does not have to be overwhelming. From manuscript to '
            . 'marketplace, our team handles the editing, the design, the production files '
            . 'and the retail setup, and tells you what is happening at each stage.',
            'Whether this is your first book, your first eBook, or a new edition of a title '
            . 'you published years ago, the process is the same: agree the scope, do the '
            . 'work, publish it properly.',
        ],
        'image'   => 'img/about-story',
        'alt'     => 'A member of the Elite Publishing team at work',
        'cta'     => ['label' => 'Publish Your Book', 'href' => 'contact'],
    ],

    /* ------------------------------------------------------------------ close
       The closing band — the last section of the source layout.

       The source draws it on a warm gold wash. This uses the site's own green
       CTA panel instead, which is the same object in this design system and is
       already the closing block on the contact page and all four campaign
       pages. Matching the source's gold would have introduced a second accent
       colour that appears nowhere else on the site. */
    'close' => [
        'heading' => "Let's Talk About Your Book!",
        'primary' => ['label' => 'Schedule a Conversation', 'href' => 'contact'],
        'secondary' => ['label' => 'Publish Your Book',     'href' => 'contact'],
    ],
];
