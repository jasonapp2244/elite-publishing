<?php
declare(strict_types=1);

/**
 * Elite Publishing — per-service copy.
 *
 * Keyed by slug, in EP_SERVICES order. Drives only the three sections that
 * differ between the ten service pages (SPEC §C.8: hero, intro, end-to-end).
 * Every string is transcribed verbatim from SPEC §D.10.1 — including the
 * design's copy bugs (DECISIONS §11): Book Cover Design's offer list is Books
 * Publishing's list, Book Illustration's end-to-end paragraph is about
 * websites, and Creative Content Writing / Blog Article Writing are both about
 * script writing.
 *
 * 'meta_desc' is written by the build. Meta descriptions are never present in a
 * Figma export, so they are not marked 'draft'.
 *
 * 'e2e.label' is 'What We Offer:' on 8 pages and null on blog-article-writing
 * and audio-book-production, where the design shows no label (SPEC §D.10).
 *
 * The Why Us band is identical on all ten pages and lives in
 * data/shared.php['service_why'], not here.
 */

return [

    // ------------------------------------------------------------------------
    'books-publishing' => [
        'title'     => 'Books Publishing',
        'meta_desc' => 'Publish your book worldwide with Elite Publishing. We handle formatting, ISBN, platform setup and global distribution so readers can find your title.',
        'hero' => [
            /* Was "…Globally Published Success". The service is publication and
               distribution; success is the reader's verdict, not a deliverable. */
            'h1'    => "Publish Your Book\nAnd Reach Readers\nWorldwide",
            'text'  => 'We publish your book on major retail platforms and make it available to readers worldwide. From setup to final launch, we handle formatting, publishing setup, metadata, and distribution.',
            'image' => 'img/svc/books-publishing-hero',
        ],
        'intro' => [
            'h2'    => "Global Book Publishing Made\nEasy",
            'paras' => [
                'We handle the technical and platform requirements of publishing. From formatting to final upload, we prepare your files to each store\'s specification and complete the listing details readers search on.',
                'Our team manages everything so you can focus on your writing while we take care of publishing and distribution.',
                'The result is a professionally published book ready for global readers.',
            ],
            'image' => 'img/svc/books-publishing-intro',
        ],
        'e2e' => [
            'h2'     => "End-To-End Book Publishing\nFrom Setup To Launch",
            'text'   => 'We provide complete publishing solutions to turn your manuscript into a professionally published book. From setup to final launch, we handle every step to ensure your book is properly published, formatted, and ready for global readers.',
            'label'  => 'What We Offer:',
            'offers' => ['Publishing', 'Platform Setup', 'Uploading', 'Distribution', 'ISBN', 'Global Reach'],
            'image'  => 'img/svc/books-publishing-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    'book-editing' => [
        'title'     => 'Book Editing',
        'meta_desc' => 'Professional book editing and proofreading that sharpens clarity, corrects grammar and structure, and prepares your manuscript for publication.',
        'hero' => [
            'h1'    => "Turn Your Manuscript\nInto A Polished,\nFinished Book",
            'text'  => 'Professional editing and proofreading that refines your writing, improves clarity, and prepares your book for publication.',
            'image' => 'img/svc/book-editing-hero',
        ],
        'intro' => [
            'h2'    => "Professional Book Editing &\nProofreading That Sharpens\nYour Manuscript",
            'paras' => [
                'We refine your manuscript to make it clear, polished, and professionally structured. Our editing process focuses on improving readability, fixing grammar, and enhancing flow while preserving your original voice and message.',
                'We carefully review every detail to ensure your book is smooth, engaging, and ready for professional publishing standards.',
            ],
            'image' => 'img/svc/book-editing-intro',
        ],
        'e2e' => [
            'h2'     => "End-To-End Book Editing For\nYour Manuscript",
            'text'   => 'We provide complete editing and proofreading solutions to turn your draft into a polished final version. From grammar correction to structure improvement, we refine every part of your book.',
            'label'  => 'What We Offer:',
            'offers' => ['Editing', 'Grammar', 'Structure', 'Proofreading', 'Clarity', 'Formatting'],
            'image'  => 'img/svc/book-editing-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    'book-cover-design' => [
        'title'     => 'Book Cover Design',
        'meta_desc' => 'Custom book cover design that fits your genre and sells your story. Concept through to print and eBook-ready artwork built to stand out in any store.',
        'hero' => [
            'h1'    => "Give Your Book A\nVisually Stunning\nCover",
            'text'  => 'We design professional book covers that reflect your book\'s genre, tone and message. From first concept to final artwork, we produce covers built to work in both digital storefronts and print.',
            'image' => 'img/svc/book-cover-design-hero',
        ],
        'intro' => [
            'h2'    => "Creative Book Cover Design\nThat Suits Your Story",
            'paras' => [
                'We design professional and visually considered book covers that represent your book\'s genre, tone, and message. Every design is developed with the shelf, the storefront and the reader it has to reach in mind.',
                /* Was "help increase visibility and reader interest" and "connects
                   with your audience instantly" — both stated results the design
                   process cannot promise. What it can promise is a cover that
                   still works at thumbnail size, which is a real constraint and
                   a real deliverable. */
                'Our team designs covers to be legible and recognizable at every size they are shown at, from a full-size print jacket down to a 100-pixel search result.',
                'You see concepts before any one of them is developed, and the finished cover is delivered as print-ready and eBook-ready artwork.',
            ],
            'image' => 'img/svc/book-cover-design-intro',
        ],
        'e2e' => [
            'h2'    => "End-To-End Book Cover\nDesign, Start To Finish",
            'text'  => 'We provide complete cover design solutions, from the first concept through to the print-ready and eBook artwork. Every cover is designed around your story, your genre and the shelf it has to sit on.',
            'label' => 'What We Offer:',
            /* Was Books Publishing's list — Publishing, Platform Setup,
               Uploading, Distribution, ISBN, Global Reach — under a cover
               design heading (DECISIONS §11 bug 6). Transcribing the design's
               mistake was right while the brief was fidelity; the content pass
               asks for every heading to be supported by content that belongs to
               it, so the list is now the cover design deliverables. */
            'offers' => ['Concept Development', 'Cover Design', 'Typography', 'Print Layout', 'eBook Artwork', 'Print-Ready Files'],
            'image'  => 'img/svc/book-cover-design-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    'book-illustration' => [
        'title'     => 'Book Illustration',
        'meta_desc' => 'Custom book illustration for novels, children\'s books and graphic novels. Character design, scene artwork and concept art created around your story.',
        'hero' => [
            'h1'    => "Bring Your Story To Life\nWith Custom\nIllustrations",
            'text'  => 'We create unique, high-quality illustrations that visually represent your story, characters, and ideas. Our artwork helps transform your book into a more engaging and visually powerful experience for readers.',
            'image' => 'img/svc/book-illustration-hero',
        ],
        'intro' => [
            'h2'    => "Creative Custom Illustration\nThat Enhances Your Story",
            'paras' => [
                'We design original illustrations tailored to your book\'s theme, genre, and vision. Every artwork is crafted to bring depth, emotion, and creativity to your storytelling.',
                'Our focus is turning your ideas into finished illustrations that carry part of the story themselves, rather than decorating it.',
                'Artwork is delivered at print resolution and in the formats your interior layout and eBook edition both need.',
            ],
            'image' => 'img/svc/book-illustration-intro',
        ],
        'e2e' => [
            'h2' => "End-To-End Custom\nIllustration Services",
            /* Was a paragraph about building author WEBSITES, sitting under an
               illustration heading and above a list of illustration
               deliverables (DECISIONS §11 bug 7). It described a service this
               page does not offer, so it is replaced with one about the work
               the heading and the list are both already about. */
            'text'   => 'We provide complete illustration solutions, from first sketches through to the finished artwork files. Characters, scenes and cover art are developed with you and delivered at the resolution your printer and your eBook edition need.',
            'label'  => 'What We Offer:',
            'offers' => ['Character Design', 'Scene Illustration', 'Cover Illustration', 'Concept Art', 'Book Artwork', 'Visual Storytelling'],
            'image'  => 'img/svc/book-illustration-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    /**
     * The whole page described SCRIPT WRITING — "Transform Your Concepts Into
     * Audio Scripts", two near-identical paragraphs about crafting scripts, and
     * an offer list of script development and dialogue creation. The service
     * this page sells, as stated on its own card in data/shared.php and in the
     * pricing packages, is audiobook production: casting, recording, mastering
     * and store distribution. The copy now describes that.
     *
     * The two intro paragraphs were also the same paragraph twice, reworded —
     * "keeps your audience hooked from start to finish" and "captivates your
     * audience from beginning to end". Neither said anything the other did not,
     * so they are replaced by two that carry different information.
     */
    'audio-book-production' => [
        'title'     => 'Audio Book Production',
        'meta_desc' => 'Audio book production from manuscript to store-ready master. Narrator casting, studio recording, editing, mastering and audiobook distribution.',
        'hero' => [
            'h1'    => "Bring Your Book To\nListeners With A\nProduced Audiobook",
            'text'  => 'We manage audiobook production end to end — narrator casting, recording, editing and mastering — and deliver files to the specification each audiobook store requires.',
            'image' => 'img/svc/audio-book-production-hero',
        ],
        'intro' => [
            'h2'    => "Audiobook Production From\nCasting To Distribution",
            'paras' => [
                'An audiobook is a separate edition, not a reading of the print file. We prepare your manuscript for narration, cast a narrator whose voice suits the book, and record to the audio standards the retailers set.',
                'Editing, proofing against the manuscript and mastering follow, and then we prepare and upload the files store by store. You approve the narrator and a sample chapter before full recording begins.',
            ],
            'image' => 'img/svc/audio-book-production-intro',
        ],
        'e2e' => [
            'h2'   => "Comprehensive Audio Book\nProduction Services",
            'text' => 'We offer full audio book production services, from preparing the manuscript for narration through to the finished, store-ready master. Your book is cast, recorded, edited and proofed against the text at every stage.',
            // No label on this page (SPEC §D.10).
            'label'  => null,
            'offers' => [
                'Manuscript Preparation for Narration',
                'Narrator Casting and Sample Approval',
                'Studio Recording',
                'Audio Editing and Mastering',
                'Proofing Against the Manuscript',
                'Audiobook Store Distribution',
            ],
            'image' => 'img/svc/audio-book-production-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    'ghostwriting' => [
        'title'     => 'Ghostwriting',
        'meta_desc' => 'Professional ghostwriting that turns your idea into a publish-ready book. We write in your voice, from the first concept to the final manuscript.',
        'hero' => [
            'h1'    => "Turn Your Idea Into A\nPowerful, Professionally\nWritten Book",
            'text'  => 'We turn your ideas into a finished book that reflects your voice, vision, and message. From concept to final manuscript, our ghostwriters handle the writing so you can focus on the story you want told.',
            'image' => 'img/svc/ghostwriting-hero',
        ],
        'intro' => [
            'h2'    => "Professional Ghostwriting\nThat Brings Your Story To Life",
            'paras' => [
                'Every great book starts with an idea—but not every idea becomes a book. Turning thoughts and experiences into a well-written manuscript requires structure, clarity, and storytelling expertise.',
                'Our ghostwriting service helps authors, entrepreneurs, and professionals transform raw ideas into engaging, publish-ready books. Whether you have a rough concept or a full outline, we turn it into a clear and compelling narrative.',
                'We work closely with you to capture your voice, tone, and message so your book feels authentic while meeting professional publishing standards.',
                'The result is a book that sounds like you—only clearer, stronger, and more impactful.',
            ],
            'image' => 'img/svc/ghostwriting-intro',
        ],
        'e2e' => [
            'h2'     => "End-To-End Ghostwriting For\nYour Book",
            'text'   => 'We provide professional ghostwriting services designed to turn your ideas into a fully developed, publish-ready book. From the first concept to the final manuscript, we ensure your story is structured, engaging, and aligned with your voice and vision.',
            'label'  => 'What We Offer:',
            'offers' => ['Writing', 'Editing', 'Structuring', 'Ghostwriting', 'Storytelling', 'Collaboration'],
            'image'  => 'img/svc/ghostwriting-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    'book-marketing' => [
        'title'     => 'Book Marketing',
        'meta_desc' => 'Book marketing that builds visibility and sales. Launch strategy, author branding, audience targeting and promotion that put your book in front of readers.',
        'hero' => [
            'h1'    => "Put Your Book In Front\nOf More Readers",
            /* Was "…boost sales across global platforms" and "we handle
               everything to grow your author brand". Sales and growth are
               outcomes; what is actually sold is the campaign work, so that is
               what the copy now describes. Same edit through the section below. */
            'text'  => 'We promote your book with a marketing plan built around your genre, your readers and your launch date. From the announcement through to ongoing promotion, we produce the materials and run the campaigns.',
            'image' => 'img/svc/book-marketing-hero',
        ],
        'intro' => [
            'h2'    => "Strategic Book Marketing\nBuilt Around Your Book",
            'paras' => [
                'We build a marketing plan around your book, your audience, and your publishing goals. From author branding to promotional campaigns, we develop the materials and run the activity behind your launch.',
                'That covers the retailer listing and its keywords, your author profiles, the launch announcement, review outreach, and any paid advertising you choose to run.',
                'You see the plan before it starts and a report on what ran and how it performed after it does.',
            ],
            'image' => 'img/svc/book-marketing-intro',
        ],
        'e2e' => [
            'h2'     => "End-To-End Book Marketing\nFrom Launch Onwards",
            'text'   => 'We provide complete marketing solutions to promote your book and build your author presence. From planning to execution, we ensure your book reaches the right audience effectively.',
            'label'  => 'What We Offer:',
            'offers' => ['Marketing', 'Branding', 'Audience Targeting', 'Promotion', 'Launch Strategy', 'Visibility'],
            'image'  => 'img/svc/book-marketing-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    'proofreading' => [
        'title'     => 'Proofreading',
        'meta_desc' => 'Detailed proofreading that removes grammar, spelling, punctuation and formatting errors, leaving your manuscript clean, consistent and ready to publish.',
        'hero' => [
            'h1'    => "Polish Your Book\nWith Professional\nProofreading",
            'text'  => 'We provide detailed proofreading services to remove errors and improve the overall quality of your manuscript. Your book becomes clean, polished, and ready for professional publishing.',
            'image' => 'img/svc/proofreading-hero',
        ],
        'intro' => [
            'h2'    => "Professional Proofreading\nThat Checks Every Word",
            'paras' => [
                'We carefully review your manuscript for grammar, spelling, punctuation, and formatting errors. Our goal is to make your writing clear, smooth, and professional while preserving your original meaning and tone.',
                /* "flawless" and "error-free" were guarantees of an outcome no
                   proofread can promise. The checks themselves are the offer. */
                'Names, dates, hyphenation, capitalization and heading styles are checked for consistency across the whole manuscript, not only for correctness line by line.',
                'The result is a clean, carefully checked manuscript ready for publication.',
            ],
            'image' => 'img/svc/proofreading-intro',
        ],
        'e2e' => [
            'h2'     => "End-To-End Proofreading For\nYour Manuscript",
            'text'   => 'We provide complete proofreading solutions to ensure your book is polished, professional, and fully refined, improving clarity, readability, and overall quality so it is completely ready for publication and readers worldwide.',
            'label'  => 'What We Offer:',
            'offers' => ['Grammar Correction', 'Spelling Check', 'Punctuation Fixes', 'Sentence Clarity', 'Formatting Review', 'Consistency Check'],
            'image'  => 'img/svc/proofreading-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    /**
     * Every word on this page was about SCRIPT writing — for videos, podcasts
     * and storytelling projects — under the title Creative Content Writing
     * (DECISIONS §11 bug 8). The service card in data/shared.php describes the
     * real offer: "web copy, author bios, book descriptions, and launch emails
     * in the same voice as your book". The page now says the same thing, so the
     * card, the nav item and the page finally agree.
     */
    'creative-content-writing' => [
        'title'     => 'Creative Content Writing',
        'meta_desc' => 'Creative content writing for authors — book descriptions, author bios, website copy and launch emails, written in the same voice as your book.',
        'hero' => [
            /* Three lines, and each one has to fit the hero column at 76px —
               about 20 characters. "Creative Content Written" was 24 and wrapped
               to a fourth line. */
            'h1'    => "Content Written For\nYour Book And Your\nAuthor Platform",
            'text'  => 'We write the copy that sits around your manuscript — book descriptions, author bios, website pages and launch emails — in the same voice as the book itself.',
            'image' => 'img/svc/creative-content-writing-hero',
        ],
        'intro' => [
            'h2'    => "Professional Content Writing\nFor Authors",
            'paras' => [
                'Your book is not the only thing a reader meets. The retailer description, your author bio, your website and the emails you send at launch are usually what they read first.',
                'We write those pieces to match the voice of your manuscript, so a reader arriving from a search result or a newsletter meets the same author they will meet on page one.',
                'Every piece is drafted from your brief, reviewed with you, and delivered in the length and format the platform it is written for requires.',
            ],
            'image' => 'img/svc/creative-content-writing-intro',
        ],
        'e2e' => [
            'h2'     => "End-To-End Content Writing\nServices",
            'text'   => 'We provide complete content writing solutions, from the first brief through to the final draft. Every piece is written for the place it will appear and the reader who will find it there.',
            'label'  => 'What We Offer:',
            'offers' => ['Book Descriptions', 'Author Bios', 'Website Copy', 'Launch Emails', 'Content Planning', 'Editing & Refinement'],
            'image'  => 'img/svc/creative-content-writing-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    /**
     * As with Creative Content Writing above, every word on this page was about
     * SCRIPT writing under the title Blog Article Writing (DECISIONS §11 bug 9),
     * and its two intro paragraphs were the same paragraph twice — "captivate
     * and resonate with your audience" and "truly connect with your audience".
     * The page now describes article writing, which is what its own service card
     * and the nav item both say it is.
     */
    'blog-article-writing' => [
        'title'     => 'Blog Article Writing',
        'meta_desc' => 'Blog article writing for author websites. Researched, well-structured articles on your subject and genre, written in your voice and ready to publish.',
        'hero' => [
            'h1'    => "Blog Articles That\nKeep Your Author\nSite Active",
            'text'  => 'We research and write articles for your author website — on your subject, your genre, and the questions your readers are already asking.',
            'image' => 'img/svc/blog-article-writing-hero',
        ],
        'intro' => [
            'h2'    => "Blog Writing For Authors\nAnd Their Readers",
            'paras' => [
                'A website with nothing new on it gives a reader no reason to come back. Regular articles give your site something to publish between books, and give search engines something to index.',
                'We agree a schedule and a set of subjects with you, research each piece, and write it in your voice. Every article is structured with the headings, internal links and metadata that search engines read, and is delivered ready to publish.',
            ],
            'image' => 'img/svc/blog-article-writing-intro',
        ],
        'e2e' => [
            // Does not use the "End-To-End" pattern in the design (SPEC §D.10.1).
            'h2'   => "Comprehensive Blog\nWriting Services",
            'text' => 'We offer a full range of article writing services, from topic research and planning through to the finished, publish-ready draft. Each piece is written for a subject your readers search for and edited before it reaches you.',
            // No label on this page (SPEC §D.10).
            'label'  => null,
            'offers' => ['Topic Research', 'Article Writing', 'Search-Friendly Structure', 'Content Planning', 'Editing and Enhancement', 'Publishing Support'],
            'image'  => 'img/svc/blog-article-writing-e2e',
        ],
    ],
];
