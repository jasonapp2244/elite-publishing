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
            'h1'    => "Turn Your Book Into A\nGlobally Published\nSuccess",
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
            'h2'     => "End-To-End Book Publishing\nFor Your Success",
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
            'h1'    => "Turn Your Manuscript\nInto A Perfectly\nPolished Book",
            'text'  => 'Professional editing and proofreading that refines your writing, improves clarity, and prepares your book for publication.',
            'image' => 'img/svc/book-editing-hero',
        ],
        'intro' => [
            'h2'    => "Professional Book Editing &\nProofreading That Perfects\nYour Manuscript",
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
            'text'  => 'We design eye-catching, professional book covers that instantly attract readers and reflect your book\'s true message. From concept to final design, we create covers that stand out in both digital and print markets.',
            'image' => 'img/svc/book-cover-design-hero',
        ],
        'intro' => [
            'h2'    => "Creative Book Cover Design\nThat Sells Your Story",
            'paras' => [
                'We design professional and visually compelling book covers that capture attention and represent your book\'s genre, tone, and message. Every design is crafted to make your book stand out in a competitive market.',
                'Our team focuses on creating covers that not only look beautiful but also help increase visibility and reader interest.',
                'The result is a powerful, market-ready book cover that connects with your audience instantly.',
            ],
            'image' => 'img/svc/book-cover-design-intro',
        ],
        'e2e' => [
            'h2'    => "End-To-End Book Cover\nDesign For Your Success",
            'text'  => 'We provide complete cover design solutions to visually represent your book in the most powerful way. From concept creation to final artwork, we design covers that match your story and attract the right audience.',
            'label' => 'What We Offer:',
            // DECISIONS §11 bug 6 — this is Books Publishing's list, as drawn.
            'offers' => ['Publishing', 'Platform Setup', 'Uploading', 'Distribution', 'ISBN', 'Global Reach'],
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
                'Our focus is to turn your ideas into visually stunning illustrations that make your book more engaging and memorable.',
                'The result is powerful artwork that elevates your entire book experience.',
            ],
            'image' => 'img/svc/book-illustration-intro',
        ],
        'e2e' => [
            'h2' => "End-To-End Custom\nIllustration Services",
            // DECISIONS §11 bug 7 — this paragraph is about websites, as drawn.
            'text'   => 'We provide complete website solutions for authors to build their online presence. From design to launch, we ensure your website is fully functional, professional, and reader-focused.',
            'label'  => 'What We Offer:',
            'offers' => ['Character Design', 'Scene Illustration', 'Cover Illustration', 'Concept Art', 'Book Artwork', 'Visual Storytelling'],
            'image'  => 'img/svc/book-illustration-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    'audio-book-production' => [
        'title'     => 'Audio Book Production',
        'meta_desc' => 'Audio book production from first concept to final recording. We structure and produce audio-ready narration that keeps listeners engaged start to finish.',
        'hero' => [
            'h1'    => "Transform Your\nConcepts Into Audio\nScripts.",
            'text'  => 'We write audio scripts that hold attention, convey clear messages, and suit the format you are producing for.',
            'image' => 'img/svc/audio-book-production-hero',
        ],
        'intro' => [
            'h2'    => "Engaging Audiobook Scripts\nThat Captivate Listeners",
            'paras' => [
                'We create structured and captivating scripts specifically designed for audiobooks, ensuring clarity and a smooth narrative flow. Our goal is to turn your concepts into engaging scripts that resonate with listeners, making your message clear and impactful. The outcome is a dynamic script that keeps your audience hooked from start to finish.',
                'At our company, we specialize in crafting engaging scripts tailored for audiobooks. Our focus is on clarity and seamless storytelling, transforming your ideas into scripts that truly connect with listeners. The result is a captivating narrative that captivates your audience from beginning to end.',
            ],
            'image' => 'img/svc/audio-book-production-intro',
        ],
        'e2e' => [
            'h2'   => "Comprehensive Audio Book\nProduction Services",
            'text' => 'We offer full audio book production services, guiding you from the initial idea to the final recording. Our team ensures your content is captivating, well-organized, and resonates with your audience, delivering a strong and engaging narrative.',
            // No label on this page (SPEC §D.10).
            'label'  => null,
            'offers' => [
                'Audio Book Script Development',
                'Narrative Structuring for Audio Books',
                'Character Dialogue Creation for Audio Books',
                'Audio Content Strategy',
                'Audio Editing and Enhancement',
                'Innovative Audio Development',
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
            'text'  => 'We promote your book with powerful marketing strategies that increase visibility, attract the right readers, and boost sales across global platforms. From launch to ongoing promotion, we handle everything to grow your author brand.',
            'image' => 'img/svc/book-marketing-hero',
        ],
        'intro' => [
            'h2'    => "Strategic Book Marketing\nThat Drives Real Results",
            'paras' => [
                'We build a marketing plan around your book, your audience, and your publishing goals. From author branding to promotional campaigns, we develop the materials and run the activity behind your launch.',
                'Our team ensures your book is not only published but also actively promoted to increase engagement, reach, and sales.',
                'The result is a strong author brand with a book that gets real attention.',
            ],
            'image' => 'img/svc/book-marketing-intro',
        ],
        'e2e' => [
            'h2'     => "End-To-End Book Marketing\nFor Your Success",
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
            'h1'    => "Perfect Your Book\nWith Professional\nProofreading",
            'text'  => 'We provide detailed proofreading services to remove errors and improve the overall quality of your manuscript. Your book becomes clean, polished, and ready for professional publishing.',
            'image' => 'img/svc/proofreading-hero',
        ],
        'intro' => [
            'h2'    => "Professional Proofreading\nThat Perfects Every Word",
            'paras' => [
                'We carefully review your manuscript to eliminate grammar, spelling, punctuation, and formatting errors. Our goal is to make your writing clear, smooth, and professional while preserving your original meaning and tone.',
                'Every detail is checked to ensure your book meets high publishing standards and delivers a flawless reading experience.',
                'The result is a clean, error-free manuscript ready for publication.',
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
    // DECISIONS §11 bug 8 — the page is titled Creative Content Writing but all
    // of its body copy is about script writing. Transcribed as drawn.
    'creative-content-writing' => [
        'title'     => 'Creative Content Writing',
        'meta_desc' => 'Creative content and script writing that delivers your message clearly. Structured, engaging copy written for books, video, podcasts and storytelling.',
        'hero' => [
            'h1'    => "Turn Your Ideas Into\nPowerful, Engaging\nScripts",
            'text'  => 'We create professionally written scripts that capture attention, deliver clear messages, and connect with your audience across different formats.',
            'image' => 'img/svc/creative-content-writing-hero',
        ],
        'intro' => [
            'h2'    => "Professional Script Writing\nThat Brings Ideas To Life",
            'paras' => [
                'We craft well-structured and engaging scripts tailored to your purpose, whether for books, videos, podcasts, or storytelling projects. Our focus is on clarity, flow, and impactful communication.',
                'We transform your ideas into compelling scripts that are easy to follow, engaging, and professionally written.',
                'The result is a powerful script that delivers your message effectively and keeps your audience engaged.',
            ],
            'image' => 'img/svc/creative-content-writing-intro',
        ],
        'e2e' => [
            'h2'     => "End-To-End Script Writing\nServices",
            'text'   => 'We provide complete script writing solutions from initial concept to final draft, ensuring your content is engaging, well-structured, and impactful while effectively connecting with your target audience and delivering a clear, compelling message.',
            'label'  => 'What We Offer:',
            'offers' => ['Script Writing', 'Story Structuring', 'Dialogue Writing', 'Content Planning', 'Editing & Refinement', 'Creative Development'],
            'image'  => 'img/svc/creative-content-writing-e2e',
        ],
    ],

    // ------------------------------------------------------------------------
    // DECISIONS §11 bug 9 — the page is titled Blog Article Writing but all of
    // its body copy is about script writing. Transcribed as drawn.
    'blog-article-writing' => [
        'title'     => 'Blog Article Writing',
        'meta_desc' => 'Blog article and script writing that turns your concepts into engaging, well-structured content your audience will actually read and remember.',
        'hero' => [
            'h1'    => "Transform Your\nConcepts Into\nEngaging Scripts",
            'text'  => 'We write scripts that hold attention, convey clear messages, and suit the format and audience you are writing for.',
            'image' => 'img/svc/blog-article-writing-hero',
        ],
        'intro' => [
            'h2'    => "Crafting Scripts That Bring\nYour Vision To Life",
            'paras' => [
                'Crafting scripts that truly reflect your vision is our passion. Whether it\'s for a book, video, podcast, or an engaging story, we focus on creating clear, compelling narratives. Our team ensures smooth transitions and powerful messaging, transforming your ideas into scripts that captivate and resonate with your audience.',
                'At our core, we are dedicated to bringing your ideas to life through carefully written scripts. Be it for a novel, a video, a podcast, or a captivating tale, we prioritize clarity and engagement in our storytelling. Our skilled team delivers seamless transitions and impactful messaging, so your concepts are transformed into scripts that truly connect with your audience.',
            ],
            'image' => 'img/svc/blog-article-writing-intro',
        ],
        'e2e' => [
            // Does not use the "End-To-End" pattern in the design (SPEC §D.10.1).
            'h2'   => "Comprehensive Script\nWriting Services",
            'text' => 'We offer a full range of script writing services, guiding you from the initial idea to the polished final draft. Our focus is on creating engaging, well-organized content that resonates with your audience and conveys a strong, clear message.',
            // No label on this page (SPEC §D.10).
            'label'  => null,
            'offers' => ['Script Creation', 'Narrative Structuring', 'Dialogue Crafting', 'Content Strategy', 'Editing and Enhancement', 'Innovative Development'],
            'image'  => 'img/svc/blog-article-writing-e2e',
        ],
    ],
];
