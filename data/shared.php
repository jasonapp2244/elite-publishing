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
                'text'  => 'Transform your raw manuscript into a bookstore-ready paperback, hardcover, or eBook. Our comprehensive publishing service covers formatting, ISBN registration, and distribution to every major retailer.',
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
                'text'  => 'Readers judge books by their covers—and so do retail algorithms. Our cover artists design modern, high-impact covers that hold up at full size and as a store thumbnail.',
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
                'text'  => 'Tap into today\'s fastest-growing literary format. We manage the entire audiobook pipeline, including narrator casting, studio recording, mastering, and store distribution.',
                'draft' => true,
            ],
            [
                'slug'  => 'book-marketing',
                'title' => "Book\nMarketing",
                'icon'  => 'megaphone',
                'text'  => 'Publishing your book is only the first step; reaching readers is where the real work begins. Our marketing specialists build the launch plan, the campaigns, and the reviews that put your title in front of buyers.',
                'draft' => true,
            ],
            [
                'slug'  => 'blog-article-writing',
                'title' => "Blog Article\nWriting",
                'icon'  => 'browser',
                'text'  => 'Build long-term organic traffic to your author website. Our content strategists write SEO-optimized blog articles that answer the questions your readers are already searching for.',
                'draft' => true,
            ],
            [
                'slug'  => 'creative-content-writing',
                'title' => "Creative Content\nWriting",
                'icon'  => 'hand-pen',
                'text'  => 'Need compelling text beyond your manuscript? We write high-converting web copy, author bios, book descriptions, and launch emails in the same voice as your book.',
                'draft' => true,
            ],
        ],
    ],

    // ----------------------------------------------------------------- journey
    // SPEC §D.2 "Your Publishing Journey" — 4 steps, all copy is in the design.
    'journey' => [
        'heading' => 'Your Publishing Journey',
        'intro'   => 'From your first idea to a bestselling book, we guide you through every step of the process with ease and expertise.',
        'steps'   => [
            [
                'title' => 'Share Your Idea',
                'text'  => 'Tell us your vision and goals, so we can plan the perfect strategy for your book.',
                'icon'  => 'lightbulb',
            ],
            [
                'title' => 'Writing & Development',
                'text'  => 'Our expert writers craft your manuscript into a compelling, polished story.',
                'icon'  => 'hand-pen',
            ],
            [
                'title' => 'Design & Formatting',
                'text'  => 'Stunning cover designs and reader-friendly layouts that make your book stand out.',
                'icon'  => 'palette',
            ],
            [
                'title' => 'Publishing & Launch',
                'text'  => 'We handle distribution on major platforms and help you promote your book worldwide.',
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
                'text'  => 'Our streamlined publishing process ensures every book is professionally prepared, published, and promoted for maximum impact.',
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
                'text'  => 'Publication day is the start, not the finish. We run the campaign that puts your book in front of the readers most likely to buy it, and keep it there.',
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
        'intro'   => 'Discover the inspiring journeys of authors who placed their trust in us, transforming their manuscripts into bestselling masterpieces. Each story is a testament to the power of collaboration, creativity, and dedication.',
        'cta'     => 'Watch More Stories',
        'cards'   => [
            ['name' => 'Clara Wen', 'title' => 'Everything Remembered', 'img' => 'img/story-1.jpg', 'placeholder' => true],
            ['name' => 'Clara Wen', 'title' => 'Everything Remembered', 'img' => 'img/story-2.jpg', 'placeholder' => true],
            ['name' => 'Clara Wen', 'title' => 'Everything Remembered', 'img' => 'img/story-3.jpg', 'placeholder' => true],
        ],
    ],

    // ------------------------------------------------------------ testimonials
    // SPEC §D.2 "Testimonial marquee". Trailing commas on the names and the
    // card 4 = card 1 duplication are both in the design (DECISIONS §11, 10/11).
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
            'quote' => '"As a first-time writer looking for reliable book publishing services, I was terrified of making costly mistakes. Elite Publishing guided me step-by-step through manuscript formatting, editing, and launch marketing."',
            'name'  => 'David K.,',
            'role'  => 'Children\'s Book Author',
            'badge' => 'mark',
            'img'   => 'img/avatar-3.jpg',
        ],
        [
            'quote' => '"If you want to hire a book publisher that actually respects your creative vision, look no further. The audiobook narration they arranged was Broadway-quality, and my royalties go straight to me."',
            'name'  => 'Marcus Vance,',
            'role'  => 'Author of Shadows Over Orion',
            'badge' => 'trustpilot',
            'img'   => 'img/avatar-4.jpg',
        ],
    ],

    // --------------------------------------------------------------- platforms
    // SPEC §D.3 "Review platforms" — 4 cards.
    'platforms' => [
        [
            'name'    => 'Trustpilot',
            'score'   => '4.9',
            'reviews' => 'Based on 1,247 reviews',
            'link'    => 'View on Trustpilot',
            'href'    => '#',
            'style'   => 'squares',
        ],
        [
            'name'    => 'Google Reviews',
            'score'   => '4.8',
            'reviews' => 'Based on 892 reviews',
            'link'    => 'View on Google',
            'href'    => '#',
            'style'   => 'stars',
        ],
        [
            'name'    => 'Reviews.io',
            'score'   => '4.8',
            'reviews' => 'Based on 634 reviews',
            'link'    => 'View on Reviews.io',
            'href'    => '#',
            'style'   => 'stars',
        ],
        [
            'name'    => 'Sitejabber',
            'score'   => '4.9',
            'reviews' => 'Based on 634 reviews',
            'link'    => 'View on Sitejabber',
            'href'    => '#',
            'style'   => 'stars',
        ],
    ],

    // --------------------------------------------------------------------- FAQ
    // SPEC §D.2 "FAQ". Answer 1 is verbatim, spaces before the commas included
    // (DECISIONS §11 bug 1). Answers 2–6 are drafted (DECISIONS §3).
    'faq' => [
        'eyebrow' => 'FAQ',
        'heading' => 'Frequently Asked Questions.',
        'items'   => [
            [
                'q'     => 'What genres do you work with?',
                'a'     => 'Fiction, non-fiction, romance , christian , self-help, children\'s, poetry, & academic — we match your project with a writer who specializes in your genre.',
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
                ['emoji' => '💡', 'label' => 'Just an idea — I need help from scratch', 'selected' => false],
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
                ['emoji' => null, 'label' => '$2,500 — $5,000',    'selected' => false],
                ['emoji' => null, 'label' => '$5,000 — $10,000',   'selected' => false],
                ['emoji' => null, 'label' => '$10,000 — $20,000',  'selected' => true],
                ['emoji' => null, 'label' => '$20,000+',           'selected' => false],
                ['emoji' => null, 'label' => 'Not sure — advise me', 'selected' => false],
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
    // SPEC §D.1 press band — 7 logos, order matters.
    'press' => [
        'eyebrow' => 'RECOGNIZED BY AUTHORS ACROSS THE GLOBE',
        'logos'   => [
            'SNN',
            'TechCrunch',
            'techopedia',
            'TECH TIMES',
            'The New York Times',
            'WAPAKONETA DAILY NEWS',
            'yahoo! news',
        ],
    ],

    // ------------------------------------------------------------- service_why
    // SPEC §D.10 "Why Us band" — identical on all ten service pages.
    'service_why' => [
        'eyebrow' => 'WHY US',
        'heading' => 'Why Authors Trust Us',
        'text'    => 'We help you build more than just a book—we help shape your author identity. Every project is crafted with care, clarity, and full confidentiality while staying true to your voice. Our focus is to deliver a professionally written, market-ready book that creates real impact.',
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
