<?php
declare(strict_types=1);

/**
 * Elite Publishing — shared content.
 *
 * Copy is transcribed verbatim from docs/SPEC.md §D. The design's copy bugs are
 * reproduced deliberately (docs/DECISIONS.md §11) — do not "fix" them here.
 * Designed line breaks are stored as "\n" and rendered with ep_lines().
 * Anything carrying 'draft' => true is copy this build wrote because the design
 * has none; every one of those strings is listed in docs/CLIENT-QUESTIONS.md.
 */

return [

    // ---------------------------------------------------------------- services
    // SPEC §D.2 "Our Services carousel" — 8 cards. Ghostwriting and Proofreading
    // have no card in the design (DECISIONS §9).
    'services_carousel' => [
        'eyebrow' => 'OUR SERVICES',
        'heading' => 'Featured Self-Publishing Solutions',
        'intro'   => 'Whether you need standalone book editing or end-to-end book publishing services, we provide tailored solutions for every stage of your author journey:',
        'cards'   => [
            [
                'slug'  => 'books-publishing',
                'title' => "Books\nPublishing",
                'icon'  => 'search',
                'text'  => 'Transform your raw manuscript into a bookstore-ready paperback, hardcover, or eBook. Our publishing service covers formatting, ISBN registration, and distribution to major retailers.',
                'draft' => true,
            ],
            [
                'slug'  => 'book-editing',
                'title' => "Book\nEditing",
                'icon'  => 'quill',
                'text'  => 'Refine your narrative before hitting market shelves. Our editorial team offers tailored tiers including structural, line, and copy editing, so your manuscript reads exactly the way you intended.',
                'draft' => true,
            ],
            [
                'slug'  => 'book-cover-design',
                'title' => "Book Cover\nDesign",
                'icon'  => 'layers',
                'text'  => 'Readers judge books by their covers, and so do retail algorithms. Our cover artists design modern, high-impact covers that hold up at full size and as a store thumbnail.',
                'draft' => true,
            ],
            [
                'slug'  => 'book-illustration',
                'title' => "Book\nIllustration",
                'icon'  => 'book-open',
                'text'  => 'Bring your characters to life with custom artwork. Perfect for children\'s literature, graphic novels, and any book that tells part of its story in pictures.',
                'draft' => true,
            ],
            [
                'slug'  => 'audio-book-production',
                'title' => "Audio Book\nProduction",
                'icon'  => 'mic',
                'text'  => 'Bring your book to listeners as well as readers. We manage the entire audiobook pipeline, including narrator casting, studio recording, mastering, and store distribution.',
                'draft' => true,
            ],
            [
                'slug'  => 'book-marketing',
                'title' => "Book\nMarketing",
                'icon'  => 'megaphone',
                /* Was "…the campaigns, and the reviews that put your title in
                   front of buyers" — reviews are not ours to produce, and
                   reaching buyers was stated as a result rather than an aim. */
                'text'  => 'Publishing your book is only the first step; reaching readers is where the next stage of work begins. Our marketing team builds the launch plan, runs the campaigns, and handles the review outreach agreed for your title.',
                'draft' => true,
            ],
            [
                'slug'  => 'blog-article-writing',
                'title' => "Blog Article\nWriting",
                'icon'  => 'browser',
                'text'  => 'Give your author website something to publish between books. Our writers research and write blog articles around the subjects your readers search for, structured and optimized for search.',
                'draft' => true,
            ],
            [
                'slug'  => 'creative-content-writing',
                'title' => "Creative Content\nWriting",
                'icon'  => 'hand-pen',
                'text'  => 'Need text beyond your manuscript? We write web copy, author bios, book descriptions, and launch emails in the same voice as your book.',
                'draft' => true,
            ],
        ],
    ],

    // ----------------------------------------------------------------- journey
    // SPEC §D.2 "Your Publishing Journey" — 4 steps, all copy is in the design.
    'journey' => [
        'heading' => 'Your Publishing Journey',
        'intro'   => 'From your first idea to a finished, published book, we guide you through every step of the process.',
        'steps'   => [
            [
                'title' => 'Share Your Idea',
                /* "the perfect strategy" promised an outcome nobody can
                   promise; the step is a briefing conversation, so it now says
                   that. Same reasoning for the three below. */
                'text'  => 'Tell us your goals for the book and your readers, and we agree the scope and schedule together.',
                'icon'  => 'lightbulb',
            ],
            [
                'title' => 'Writing & Development',
                'text'  => 'Our writers work your outline up into a complete manuscript, sent to you in stages so you can steer it.',
                'icon'  => 'hand-pen',
            ],
            [
                'title' => 'Design & Formatting',
                'text'  => 'Cover artwork and interior layouts designed together, for print, eBook and store thumbnails.',
                'icon'  => 'palette',
            ],
            [
                'title' => 'Publishing & Launch',
                'text'  => 'We set up the retail listings, distribute to the major platforms, and run the launch activity agreed for your book.',
                'icon'  => 'paper-plane',
            ],
        ],
    ],

    // ----------------------------------------------------------------- process
    // SPEC §D.3 "Our Publishing Process" — 7 tabs. Only step 01 is rendered in
    // the export; steps 02–07 are drafted here (DECISIONS §4).
    'process' => [
        'eyebrow' => 'OUR PUBLISHING PROCESS',
        'heading' => 'From Idea To Published Book In 7 Simple Steps',
        'steps'   => [
            [
                'tab'   => 'Consultation',
                'title' => 'Consultation',
                'text'  => 'We take each book through preparation, publication, and promotion, with a set schedule at every stage.',
                'check' => [
                    '30-min video or phone call',
                    'Custom roadmap preview',
                    'Zero commitment required',
                    'Genre & audience analysis',
                ],
                'draft' => false,
            ],
            [
                'tab'   => 'Planning',
                'title' => 'Planning',
                'text'  => 'We map your book from the first chapter to launch day, so every stage has an owner, a deadline, and a clear deliverable.',
                'check' => [
                    'Chapter-by-chapter outline',
                    'Agreed delivery schedule',
                    'Dedicated project manager',
                    'Target reader profile',
                ],
                'draft' => true,
            ],
            [
                'tab'   => 'Writing',
                'title' => 'Writing',
                'text'  => 'Your writer drafts the manuscript in your voice and sends it to you in stages, so you can steer the book while it is being written.',
                'check' => [
                    'Drafts delivered in batches',
                    'Weekly progress updates',
                    'Voice and tone matching',
                    'Two rounds of revisions',
                ],
                'draft' => true,
            ],
            [
                'tab'   => 'Editing',
                'title' => 'Editing',
                'text'  => 'Two editors work on every manuscript: one for structure and pacing, one for grammar and line-level detail. Nothing moves to design until the text is clean.',
                'check' => [
                    'Structural edit',
                    'Line and copy edit',
                    'Consistency and fact check',
                    'Final proofread',
                ],
                'draft' => true,
            ],
            [
                'tab'   => 'Design',
                'title' => 'Design',
                'text'  => 'Your cover and your interior are designed together, so the book reads as one piece of work in print, on screen, and as a store thumbnail.',
                'check' => [
                    'Three cover concepts',
                    'Print and eBook interiors',
                    'Typography and layout',
                    'Retailer-ready files',
                ],
                'draft' => true,
            ],
            [
                'tab'   => 'Publishing',
                'title' => 'Publishing',
                'text'  => 'We handle the accounts, the metadata, and the uploads, then check every live listing before we call your book published.',
                'check' => [
                    'ISBN and copyright registration',
                    'Amazon KDP and IngramSpark setup',
                    'Metadata and category selection',
                    'Proof copy approval',
                ],
                'draft' => true,
            ],
            [
                'tab'   => 'Marketing',
                'title' => 'Marketing',
                'text'  => 'Publication day is the start, not the finish. We develop the promotional materials and run the campaign activity agreed for your book, audience and publishing goals.',
                'check' => [
                    'Launch campaign plan',
                    'Author platform setup',
                    'Reader review outreach',
                    'Paid advertising management',
                ],
                'draft' => true,
            ],
        ],
    ],

    // ----------------------------------------------------------------- stories
    // SPEC §D.2 "Author Stories". All three cards carry the same name/title in
    // the design (DECISIONS §11 bug 12).
    'stories' => [
        'eyebrow' => 'AUTHOR STORIES',
        'heading' => "What Our\nAuthors Say",
        /* Was "Discover the inspiring journeys of authors who placed their
           trust in us … Each story is a testament to the power of
           collaboration". Two problems: it described results for authors the
           site cannot name, and it promised stories the section does not
           contain — the cards are static images, and the CTA said "Watch More
           Stories" with nothing to watch (see components/author-stories.php).
           It now describes the working relationship, which is what the section
           and the reviews under it actually show. */
        'intro'   => 'Authors work directly with the editors, designers and project managers assigned to their book, and approve the work at each stage. The reviews below are from authors who have been through that process with us.',
        'cta'     => 'See the Genres We Work In',
        'cards'   => [
            ['name' => 'Clara Wen', 'title' => 'Everything Remembered', 'img' => 'img/story-1.jpg', 'placeholder' => true],
            ['name' => 'Clara Wen', 'title' => 'Everything Remembered', 'img' => 'img/story-2.jpg', 'placeholder' => true],
            ['name' => 'Clara Wen', 'title' => 'Everything Remembered', 'img' => 'img/story-3.jpg', 'placeholder' => true],
        ],
    ],

    // ------------------------------------------------------------ testimonials
    /**
     * SPEC §D.2 "Testimonial marquee". Trailing commas on the names are in the
     * design (DECISIONS §11).
     *
     * The design supplied TWO quotes across four cards. Card 4 repeated card 1
     * word for word under the same name, which is only a repetition — but card
     * 3 repeated card 2 word for word under a DIFFERENT name and role, which
     * credits one person's review to another. That is the one thing in here
     * that was not merely redundant, and writing a third review to replace it
     * would have been inventing a customer.
     *
     * So the two real quotes now alternate A-B-A-B, each with its own name,
     * role, platform badge and avatar. Nothing was added, nothing attributed to
     * anyone who did not say it, and the track still carries four cards — the
     * marquee duplicates it again for the loop, and a shorter track would leave
     * a visible gap at the seam on a wide screen.
     *
     * Replace these with the real review set when it arrives; the count is free
     * to grow, only the emptiness of the array matters to the component.
     */
    'testimonials' => [
        [
            'quote' => '"If you want to hire a book publisher that actually respects your creative vision, look no further. The audiobook narration they arranged was Broadway-quality, and my royalties go straight to me."',
            'name'  => 'Marcus Vance,',
            'role'  => 'Author of Shadows Over Orion',
            'badge' => 'trustpilot',
            'img'   => 'img/avatar-1.jpg',
        ],
        [
            'quote' => '"As a first-time writer looking for reliable book publishing services, I was terrified of making costly mistakes. Elite Publishing guided me step-by-step through manuscript formatting, editing, and launch marketing."',
            'name'  => 'Elena Rostova,',
            'role'  => 'Author of The Botanical Table',
            'badge' => 'google',
            'img'   => 'img/avatar-2.jpg',
        ],
        [
            'quote' => '"If you want to hire a book publisher that actually respects your creative vision, look no further. The audiobook narration they arranged was Broadway-quality, and my royalties go straight to me."',
            'name'  => 'Marcus Vance,',
            'role'  => 'Author of Shadows Over Orion',
            'badge' => 'trustpilot',
            'img'   => 'img/avatar-1.jpg',
        ],
        [
            'quote' => '"As a first-time writer looking for reliable book publishing services, I was terrified of making costly mistakes. Elite Publishing guided me step-by-step through manuscript formatting, editing, and launch marketing."',
            'name'  => 'Elena Rostova,',
            'role'  => 'Author of The Botanical Table',
            'badge' => 'google',
            'img'   => 'img/avatar-2.jpg',
        ],
    ],

    // --------------------------------------------------------------- platforms
    /**
     * SPEC §D.3 "Review platforms" — 4 cards.
     *
     * 'icon' is a path under assets/img/ holding the platform's own mark. A card
     * without one falls back to the first letter of the name on a dark tile,
     * which is what all four used to show.
     *
     * 'icon_fill' says the artwork IS the badge rather than a mark to sit
     * inside one. Trustpilot's file is a filled green rounded tile — 97% of its
     * pixels are opaque — so it fills the 28px badge and the white plate is
     * dropped. The other three are small marks on transparency and need the
     * plate behind them.
     */
    /* EMPTIED IN THE CONTENT PASS — the four cards published a rating and a
       review count for each platform: 4.9/5 from 1,247 Trustpilot reviews,
       4.8/5 from 892 on Google, and two more. None of it came from the
       platforms. Every "View on …" link pointed at '#', so a visitor could not
       check a single figure, and nothing in the project measures them.
       Published review scores are the most checkable claim on a site and the
       most damaging one to get wrong, so they are out.

       index.php renders this section only when the array is non-empty, so the
       block disappears cleanly and comes back the moment there is something
       true to put in it: fill in each card's real 'score' and 'reviews', and
       point 'href' at the actual profile page rather than '#'. */
    'platforms' => [],

    // --------------------------------------------------------------------- FAQ
    // SPEC §D.2 "FAQ". Answer 1 is verbatim, spaces before the commas included
    // (DECISIONS §11 bug 1). Answers 2–6 are drafted (DECISIONS §3).
    'faq' => [
        'eyebrow' => 'FAQ',
        'heading' => 'Frequently Asked Questions.',
        'items'   => [
            [
                'q'     => 'What genres do you work with?',
                /* The stray spaces before the commas in "romance , christian ,"
                   and the ampersand mid-sentence were transcribed from the
                   design (DECISIONS §11 bug 1) and reproduced deliberately. The
                   content pass asks for correct grammar, so they are corrected
                   here — the wording and the genre list are untouched. */
                'a'     => 'Fiction, nonfiction, romance, Christian, self-help, children\'s, poetry, and academic. We match your project with a writer who specializes in your genre.',
                'draft' => false,
            ],
            [
                'q'     => 'How long does the process take?',
                'a'     => 'Most projects run three to six months from kickoff to launch, depending on length and the services you choose. Editing-only work moves faster; a full ghostwritten manuscript takes longer. You receive a dated schedule at the end of your consultation.',
                'draft' => true,
            ],
            [
                'q'     => 'Who owns the copyright to my book?',
                'a'     => 'You do. Copyright stays in your name from the first draft onward, and you keep 100% of the royalties on every format we publish. We never take a share of your rights or your earnings.',
                'draft' => true,
            ],
            [
                'q'     => 'How involved will I be?',
                'a'     => 'As involved as you want to be. Some authors approve every chapter as it is written, others check in once a month and leave the rest to us. You set the pace at the start and we work to it.',
                'draft' => true,
            ],
            [
                'q'     => 'What\'s your refund policy?',
                'a'     => 'If work has not started, your payment is returned in full. Once your team is booked and writing has begun, deposits are non-refundable, but every package includes a set number of revisions so the work meets your expectations.',
                'draft' => true,
            ],
            [
                'q'     => 'Will my book remain 100% confidential?',
                'a'     => 'Yes. Every project is covered by a confidentiality agreement, and your manuscript is only seen by the team assigned to it. We never share your name, your material, or your involvement without your written permission.',
                'draft' => true,
            ],
        ],
    ],

    // --------------------------------------------------------------------- CTA
    // SPEC §D.2 "CTA + wizard".
    'cta' => [
        'heading' => "Let's Bring Your\nBook To Life",
        'intro'   => 'Ready to self-publish or have questions about our services? Get in touch with our editorial team today for a free manuscript evaluation and consultation.',
        'site'    => 'ElitePublishing.co',
        'email'   => 'info@elitepublishing.co',
        'buttons' => [
            ['label' => 'Publish Your Book', 'href' => 'contact.php', 'variant' => 'primary'],
            ['label' => 'Free Consultation', 'href' => 'contact.php', 'variant' => 'outline'],
        ],
    ],

    // ------------------------------------------------------------------ wizard
    // SPEC §D.2 wizard, 4 steps. Step 3 option 4 is '$20,000+' (DECISIONS §7) —
    // the upper bound is clipped in the export.
    'wizard' => [
        [
            'eyebrow'  => 'STEP 1 OF 4',
            'question' => 'What Genre Is Your Book?',
            'name'     => 'genre',
            'cols'     => 2,
            'cta'      => 'Continue',
            'options'  => [
                ['emoji' => '📖', 'label' => 'Fiction',      'selected' => true],
                ['emoji' => '📚', 'label' => 'Non-Fiction',  'selected' => false],
                ['emoji' => '💌', 'label' => 'Romance',      'selected' => false],
                ['emoji' => '📕', 'label' => 'Christian',    'selected' => false],
                ['emoji' => '🌱', 'label' => 'Self-Help',    'selected' => false],
                ['emoji' => '🎨', 'label' => 'Children\'s',  'selected' => false],
            ],
        ],
        [
            'eyebrow'  => 'STEP 2 OF 4',
            'question' => 'Where Are You In The Journey?',
            'name'     => 'stage',
            'cols'     => 1,
            'cta'      => 'Continue',
            'options'  => [
                ['emoji' => '💡', 'label' => 'Just an idea, I need help from scratch', 'selected' => false],
                ['emoji' => '📝', 'label' => 'I have an outline or partial draft',      'selected' => true],
                ['emoji' => '📄', 'label' => 'My manuscript is complete',               'selected' => false],
                ['emoji' => '🚀', 'label' => 'Ready to publish and launch',             'selected' => false],
            ],
        ],
        [
            'eyebrow'  => 'STEP 3 OF 4',
            'question' => 'What\'s Your Budget Range?',
            'name'     => 'budget',
            'cols'     => 1,
            'cta'      => 'Continue',
            'options'  => [
                ['emoji' => null, 'label' => '$2,500 to $5,000',    'selected' => false],
                ['emoji' => null, 'label' => '$5,000 to $10,000',   'selected' => false],
                ['emoji' => null, 'label' => '$10,000 to $20,000',  'selected' => true],
                ['emoji' => null, 'label' => '$20,000+',           'selected' => false],
                ['emoji' => null, 'label' => 'Not sure, advise me', 'selected' => false],
            ],
        ],
        [
            'eyebrow'  => 'STEP 4 OF 4',
            'question' => 'Almost There!',
            'name'     => 'details',
            'cols'     => 2,
            'cta'      => 'Get My Free Consultation',
            'type'     => 'fields',
            'fields'   => [
                ['name' => 'full_name', 'type' => 'text',     'placeholder' => 'Full Name',      'width' => 'full'],
                ['name' => 'email',     'type' => 'email',    'placeholder' => 'Email Address',  'width' => 'half'],
                ['name' => 'phone',     'type' => 'tel',      'placeholder' => 'Phone No',       'width' => 'half'],
                ['name' => 'message',   'type' => 'textarea', 'placeholder' => 'Message ...',    'width' => 'full'],
            ],
        ],
    ],

    // ------------------------------------------------------------------- press
    /**
     * SPEC §D.1 press band. Order matters.
     *
     * These were styled TEXT mastheads until the client supplied artwork —
     * tracing a masthead by hand is a trademark problem and a blurry raster, so
     * the band waited for real files (DECISIONS §13). Each entry now carries the
     * name and the file; 'file' is a path under assets/img/ and 'name' is what a
     * screen reader announces, so both have to stay filled in.
     *
     * The New York Times is NOT here. It was in the text list, but no artwork
     * for it arrived with the other six, and the band cannot mix one text
     * masthead in among six logos without looking broken. Add the file to
     * assets/img/press/ and a row here to restore it.
     *
     * Drop an entry and the marquee adapts — components/press-band.php sizes the
     * track from the count.
     */
    'press' => [
        /* Was "RECOGNIZED BY AUTHORS ACROSS THE GLOBE" — a recognition claim
           with nothing behind it, sitting directly above a row of news
           mastheads, which made it read as press coverage.

           Changing the wording only goes so far: the logos themselves still
           imply a relationship with those publications. Unless Elite Publishing
           can point to actual coverage, the band should come out entirely —
           raised in the handover report. The heading no longer makes the claim
           either way. */
        'eyebrow' => 'PUBLISHING NEWS AND RESOURCES',
        'logos'   => [
            ['name' => 'SNN',                   'file' => 'img/press/snn.png'],
            ['name' => 'TechCrunch',            'file' => 'img/press/techcrunch.png'],
            ['name' => 'Techopedia',            'file' => 'img/press/techopedia.png'],
            ['name' => 'Tech Times',            'file' => 'img/press/tech-times.png'],
            ['name' => 'Wapakoneta Daily News', 'file' => 'img/press/wapakoneta-daily-news.png'],
            ['name' => 'Yahoo News',            'file' => 'img/press/yahoo-news.png'],
        ],
    ],

    // ------------------------------------------------------------- service_why
    // SPEC §D.10 "Why Us band" — identical on all ten service pages.
    'service_why' => [
        'eyebrow' => 'WHY US',
        'heading' => 'Why Authors Trust Us',
        /* Was "…a professionally written, market-ready book that creates real
           impact." The last clause promised a result; the rest of the sentence
           already says what is delivered, so it ends there. */
        'text'    => 'We help you build more than just a book. We help shape your author identity. Every project is handled with care, clarity, and full confidentiality while staying true to your voice. Our focus is a professionally written book, prepared to the standard the retailers and print partners require.',
        'chips'   => [
            '100% Confidential Process',
            'Your Voice, Your Style',
            'Professional Writing Team',
            'Publishing-Ready Quality',
            'Collaborative Approach',
        ],
        'cta'     => ['label' => 'Learn Our Story', 'href' => 'about.php'],
    ],
];
