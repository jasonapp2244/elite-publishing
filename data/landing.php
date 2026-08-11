<?php
declare(strict_types=1);

/**
 * Elite Publishing — campaign landing pages (lp1–lp4).
 *
 * Copy transcribed from the four design PNGs in the project root:
 *   Children's Book Publishing.png · Christian Book.png
 *   Marketing.png                  · Audiobook Production.png
 *
 * All four pages are the same template with different copy, so everything that
 * varies lives here and everything that does not lives in 'shared' below.
 *
 * Unlike the rest of data/, none of this is marked 'draft': every string is
 * drawn in the design. The open questions are about DESTINATIONS, not copy —
 * see docs/CLIENT-QUESTIONS.md §46–48.
 *
 * Keys map to files: 'children' => lp1.php, 'christian' => lp2.php,
 * 'marketing' => lp3.php, 'audiobook' => lp4.php.
 */

return [

    /* ------------------------------------------------------------------
       Identical on all four pages — the header CTA, the hero form, the
       stats band and the footer are one design, drawn four times.
       ------------------------------------------------------------------ */
    'shared' => [
        'header_cta' => 'Publish Your Book',

        'form' => [
            'title'  => 'Start Your Book Today',
            'submit' => 'Send Message',
        ],

        /* Black band under the book-cover strip. Four claims, no source given
           in the design — flagged for the client (CLIENT-QUESTIONS §47). */
        'stats' => [
            ['value' => '2,500+', 'label' => 'Authors Published'],
            ['value' => '500+',   'label' => 'Top Rankings Achieved'],
            ['value' => '15+',    'label' => 'Years of Experience'],
            ['value' => '98%',    'label' => 'Client Satisfaction'],
        ],

        /* The pair of buttons at the top right of the services section. */
        'services_actions' => [
            ['label' => 'Publish Your Book',  'style' => 'green'],
            ['label' => 'Free Consultation',  'style' => 'green-outline'],
        ],

        'footer_links' => [
            /* The design labels this "Terms of Service"; the page it points at
               is titled "Terms & Conditions". The drawn label wins, per
               DECISIONS §11 — the target is the same document either way. */
            ['label' => 'Privacy Policy',   'page' => 'privacy-policy'],
            ['label' => 'Terms of Service', 'page' => 'terms-conditions'],
            ['label' => 'Contact',          'page' => 'contact'],
        ],
    ],

    /* ------------------------------------------------------------------ lp1 */
    'children' => [
        'slug'  => 'lp1',
        'title' => "Children's Book Publishing Services",
        'meta'  => "End-to-end children's book publishing services for independent "
                 . 'authors — custom illustration, print-ready layout and Amazon '
                 . 'metadata, from manuscript to finished hardcover.',

        'hero' => [
            'h1'    => "End-To-End Children's Book Publishing Services For Independent Authors",
            'paras' => [
                "Turning a children's manuscript into a beloved storybook takes more than "
                . 'just text—it requires captivating artwork, precise formatting, and expert '
                . 'market positioning. At elitepublishing.co, our children\'s book publishing '
                . 'services guide authors step-by-step from raw ideas to finished hardcover '
                . 'and paperback editions.',
            ],
        ],

        'services' => [
            'heading' => "Complete Children's Book Publishing Services Tailored To Your Vision",
            'cards'   => [
                [
                    'icon'  => 'palette-solid',
                    'title' => "Custom Character\n& Page Illustration",
                    'text'  => "As part of our children's book publishing services, we pair you "
                             . 'with veteran artists to create vibrant, high-resolution '
                             . 'illustrations for board books, picture books, and middle-grade '
                             . 'chapter books.',
                ],
                [
                    'icon'  => 'printer',
                    'title' => "Industry-Standard\nLayout & Printing",
                    'text'  => "Our specialized children's book publishing services handle exact "
                             . '24, 32, and 48-page print formatting for full-bleed '
                             . 'print-on-demand networks like KDP and IngramSpark.',
                ],
                [
                    'icon'  => 'search-sparkle',
                    'title' => "Targeted Metadata\n& SEO Setup",
                    'text'  => "Every package within our children's book publishing services "
                             . 'includes Amazon keyword research to place your book directly in '
                             . 'front of browsing parents and educators.',
                ],
            ],
        ],

        'cta' => [
            'heading' => "Bring Your Children's Story To\nLife Today",
            'text'    => "Don't let your story stay hidden in a draft. Discover how "
                       . 'elitepublishing.co uses premium children\'s book publishing services '
                       . 'to help authors publish picture books that inspire young minds '
                       . 'worldwide.',
            'primary' => "Schedule a Free Children's Book Consultation",
        ],
    ],

    /* ------------------------------------------------------------------ lp2 */
    'christian' => [
        'slug'  => 'lp2',
        'title' => 'Christian Book Publishing Services',
        'meta'  => 'Full-service Christian book publishing services for pastors, ministry '
                 . 'leaders and independent writers — theologically sound editing, '
                 . 'faith-centered design and global distribution.',

        'hero' => [
            'h1'    => 'Professional Christian Book Publishing Services For Faithful Authors',
            'paras' => [
                'At elitepublishing.co, we provide full-service Christian book publishing '
                . 'services tailored to pastors, ministry leaders, and independent writers who '
                . "want to share God's word. Bringing a faith-filled manuscript to market "
                . 'requires precision, spiritual alignment, and professional craftsmanship.',

                'Our Christian book publishing services bridge the gap between your divine '
                . 'inspiration and a beautifully printed book on store shelves.',
            ],
        ],

        'services' => [
            'heading' => 'Why Authors Choose Our Christian Book Publishing Services',
            'cards'   => [
                [
                    'icon'  => 'leaf',
                    'title' => "Theologically Sound\nEditing",
                    'text'  => 'Beyond standard grammar checks, our team delivers Christian book '
                             . 'publishing services that respect biblical integrity, helping you '
                             . 'refine devotionals, theology guides, and Christian living '
                             . 'manuscripts for maximum impact.',
                ],
                [
                    'icon'  => 'book-ribbon',
                    'title' => "Custom Faith-\nCentered Design",
                    'text'  => 'Every book published through our Christian book publishing '
                             . 'services receives bespoke cover art and interior formatting built '
                             . 'for physical bookstores and major online platforms alike.',
                ],
                [
                    'icon'  => 'search-solid',
                    'title' => "Global Print &\nDigital Distribution",
                    'text'  => 'From Amazon KDP to global Christian retail networks, our Christian '
                             . 'book publishing services connect your message with readers across '
                             . 'the world.',
                ],
            ],
        ],

        'cta' => [
            'heading' => "Ready To Share Your God-\nGiven Story?",
            'text'    => 'Work with a team that values your vision. Partner with '
                       . 'elitepublishing.co for comprehensive Christian book publishing services '
                       . 'that honor your calling and preserve 100% of your copyright and '
                       . 'royalties.',
            'primary' => 'Submit Your Manuscript Today',
        ],
    ],

    /* ------------------------------------------------------------------ lp3 */
    'marketing' => [
        'slug'  => 'lp3',
        'title' => 'Book Marketing Services',
        'meta'  => 'Data-driven book marketing services for self-published and hybrid '
                 . 'authors — Amazon and Meta ads, KDP metadata optimization, and media '
                 . 'and PR outreach.',

        'hero' => [
            'h1'    => 'Bestseller-Driven Book Marketing Services For Authors Across All Genres',
            'paras' => [
                'Writing a great book is only half the battle; getting it into the hands of '
                . 'real readers is where success happens. At elitepublishing.co, our '
                . 'data-driven book marketing services help self-published and hybrid authors '
                . 'build author brands, increase sales ranks, and maximize long-term royalty '
                . 'earnings.',
            ],
        ],

        'services' => [
            'heading' => 'Strategic Book Marketing Services That Drive Verified Reader Sales',
            'cards'   => [
                [
                    'icon'  => 'megaphone-solid',
                    'title' => "Amazon & Meta Ad\nManagement",
                    'text'  => 'Our high-converting book marketing services leverage targeted ad '
                             . 'campaigns to showcase your title directly to active buyers in '
                             . 'your specific genre.',
                ],
                [
                    'icon'  => 'search-solid',
                    'title' => "KDP Metadata\nOptimization",
                    'text'  => 'We optimize your keywords, categories, and sales copy as a '
                             . 'foundational part of our book marketing services, boosting your '
                             . 'organic discoverability on store search engines.',
                ],
                [
                    'icon'  => 'calendar-check',
                    'title' => "Media & PR\nOutreach",
                    'text'  => 'Expand your reach through book marketing services that secure '
                             . 'podcast bookings, editorial reviews, and literary feature '
                             . 'placements.',
                ],
            ],
        ],

        'cta' => [
            'heading' => "Scale Your Reader Base With\nElitepublishing.Co",
            'text'    => 'Stop writing in the dark. Invest in professional book marketing '
                       . 'services designed to turn casual browsers into dedicated lifelong fans '
                       . 'of your work.',
            'primary' => 'Request Your Custom Marketing Plan',
        ],
    ],

    /* ------------------------------------------------------------------ lp4 */
    'audiobook' => [
        'slug'  => 'lp4',
        'title' => 'Audiobook Production Services',
        'meta'  => 'Turnkey audiobook production services for modern writers — professional '
                 . 'voice casting, ACX-compliant mastering and global distribution to '
                 . 'Audible, Apple Books and Spotify.',

        'hero' => [
            'h1'    => 'Studio-Quality Audiobook Production Services For Modern Writers',
            'paras' => [
                'Audiobooks are the fastest-growing sector in publishing. At '
                . 'elitepublishing.co, our turnkey audiobook production services transform '
                . 'written manuscripts into immersive, studio-grade audio experiences ready for '
                . 'retail platforms like Audible, Apple Books, and Spotify.',
            ],
        ],

        'services' => [
            'heading' => 'Turnkey Audiobook Production Services From Casting To Distribution',
            'cards'   => [
                [
                    'icon'  => 'mic-solid',
                    'title' => "Professional Voice\nCasting",
                    'text'  => 'Our audiobook production services connect you with auditioned '
                             . "voice actors matched specifically to your book's tone, dialect, "
                             . 'and character dynamics.',
                ],
                [
                    'icon'  => 'audio-track',
                    'title' => "Retail-Ready Audio\nEngineering",
                    'text'  => 'Every package in our audiobook production services includes full '
                             . 'proofing, mastering, and ACX-compliant noise floor engineering '
                             . 'for seamless store approval.',
                ],
                [
                    'icon'  => 'book-open-solid',
                    'title' => "Global Audiobook\nDistribution",
                    'text'  => 'Through our audiobook production services, you retain 100% '
                             . 'ownership of your audio files while gaining access to global '
                             . 'retail and library audio networks.',
                ],
            ],
        ],

        'cta' => [
            'heading' => "Turn Your Manuscript Into An\nAudio Experience",
            'text'    => 'Expand your audience to thousands of daily listeners. Partner with '
                       . 'elitepublishing.co for reliable audiobook production services that '
                       . 'bring your story to life.',
            /* No narrator samples exist anywhere on the site; this points at the
               contact page until the client supplies them — CLIENT-QUESTIONS §48. */
            'primary' => 'Listen to Narrator Voice Samples',
        ],
    ],
];
