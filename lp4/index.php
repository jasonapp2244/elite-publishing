<?php
/**
 * LP4 — standalone campaign landing page.
 *
 * Was index.html. It is PHP now so the manuscript form can carry a per-session
 * CSRF token; nothing else about the page has changed and all of its assets
 * still live inside this folder.
 */
$lp = 'lp4';
require __DIR__ . '/../includes/lp-bootstrap.php';
$lpFlash = ep_lp_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Audiobook Publishing Services | Elite Publishing</title>
<meta name="description" content="Elite Publishing turns your manuscript into a professional audiobook — expert narration, full audio production, global distribution and marketing. Publish your audiobook with a team that treats your story like its own.">
<?php /* This page, not the home page — see the note in lp1/index.php. */ ?>
<link rel="canonical" href="https://elitepublishing.co/lp4/">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="theme-color" content="#031351">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Elite Publishing">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="Professional Audiobook Publishing Services">
<meta property="og:description" content="From narration to distribution, Elite Publishing handles every step of bringing your story to life in audio.">
<meta property="og:url" content="https://elitepublishing.co/lp4/">
<?php /* These named /assets/img/og-cover.jpg. That path is the MAIN SITE's asset
         folder, which holds og-default.jpg and no og-cover.jpg — the file this
         page ships is at /lp4/assets/img/og-cover.jpg. So the URL 404'd and every
         share of this page fell back to a plain text card. The declared 1200x630
         matches the real file; only the directory was missing. */ ?>
<meta property="og:image" content="https://elitepublishing.co/lp4/assets/img/og-cover.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Elite Publishing audiobook publishing services">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Professional Audiobook Publishing Services">
<meta name="twitter:description" content="From narration to distribution, Elite Publishing handles every step of bringing your story to life in audio.">
<meta name="twitter:image" content="https://elitepublishing.co/lp4/assets/img/og-cover.jpg">
<meta name="twitter:image:alt" content="Elite Publishing audiobook publishing services">

<?= ep_favicon_tags() ?>

<link rel="preload" href="assets/fonts/poppins-latin-500-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="assets/fonts/inter-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>

<?php /* ep_lp_asset() appends the file's mtime, so an edited stylesheet reaches
         every browser on the next request instead of waiting out a cache
         lifetime. See the note on the function. */ ?>
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/bootstrap-reboot.min.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/bootstrap-grid.min.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/variables.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/base.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/components.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/sections.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/animations.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/responsive.css')) ?>">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ProfessionalService",
  "name": "Elite Publishing",
  "url": "https://elitepublishing.co/",
  "description": "Audiobook publishing services covering narration, audio production, distribution and marketing.",
  "aggregateRating": { "@type": "AggregateRating", "ratingValue": "4.9", "bestRating": "5", "ratingCount": "137" },
  "offers": [
    { "@type": "Offer", "name": "Basic Package", "price": "700", "priceCurrency": "USD" },
    { "@type": "Offer", "name": "Standard Package", "price": "1000", "priceCurrency": "USD" },
    { "@type": "Offer", "name": "Premium Package", "price": "1500", "priceCurrency": "USD" }
  ]
}
</script>
<?= ep_lp_chrome_styles() ?>
</head>

<body>

<a class="skip-link" href="#main">Skip to content</a>

<?php /* The landing-page header, from includes/components/lp-chrome-header.php —
         logo and one "Submit Your Book" button, the same chrome all four landing
         pages render. It replaces this page's own two-item header bar (logo +
         "Get Started"); the button scrolls to #start, the CTA and form section.
         The page's own .site-header CSS is left in lp4/css/ untouched: nothing
         references it any more, and deleting rules is a separate decision from
         swapping the markup. */ ?>
<?php require __DIR__ . '/../includes/lp-header.php'; ?>

<main id="main">

<!-- ============================ 1 · HERO ============================ -->
<section class="hero" id="top">
    <div class="hero__media">
        <img src="assets/img/hero-shelves.webp" alt="" width="1920" height="1150" fetchpriority="high" decoding="async">
    </div>
    <div class="hero__scrim" aria-hidden="true"></div>

    <div class="container-lp">
        <div class="hero__grid">
            <div class="hero__copy">
                <h1 class="hero__title">Professional Audiobook <br class="br-lg"> Publishing Services To <br class="br-lg"> Bring Your Story To Life</h1>
                <p class="hero__text lead">At Elite Publishing, we specialize in audiobook publishing services that transform your written work into high-quality audio. Our expert team handles everything from narration to distribution, ensuring your audiobook reaches a global audience. Whether you want to publish an audiobook for the first time or hire the best audiobook narrators, we&rsquo;ve got you covered.</p>
                <div class="hero__actions">
                    <a class="btn-lp btn-lp--light" href="#start">Publish Audiobook</a>
                </div>
            </div>

            <div class="hero__books">
                <img class="book-cluster" src="assets/img/hero-books.webp" alt="Audiobook editions of Theo of Golden, Warriors: The New Prophecy and Dear Debbie" width="790" height="514" fetchpriority="high" decoding="async">
            </div>
        </div>
    </div>
</section>

<!-- ============================ 2 · FORMATS ============================ -->
<section class="section" id="formats">
    <div class="container-lp">
        <div class="section-head" data-animation="fade-up">
            <h2 class="section-head__title">Publishing Formats We <br class="br-lg"> <span class="accent">Prepare For You</span></h2>
            <p class="section-head__sub">Every package includes professional formatting and setup for eBook, paperback, and hardcover &mdash; ready for distribution on major retail platforms.</p>
        </div>

        <div class="formats-grid row g-4" data-stagger>
            <div class="col-12 col-md-6 col-lg-4" data-animation="fade-up">
                <article class="format-card">
                    <div class="format-card__icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 5.5A1.5 1.5 0 0 1 5.5 4H14l6 6v9.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19.5Z"/>
                            <path d="M14 4v6h6"/>
                            <path d="m8.6 15.4.7-1.9.7 1.9 1.9.7-1.9.7-.7 1.9-.7-1.9-1.9-.7Z"/>
                        </svg>
                    </div>
                    <h3 class="format-card__title">eBook</h3>
                    <p class="format-card__text">EPUB and Kindle-ready files formatted for Amazon KDP, Google Books, Kobo, and other digital retailers.</p>
                </article>
            </div>

            <div class="col-12 col-md-6 col-lg-4" data-animation="fade-up">
                <article class="format-card">
                    <div class="format-card__icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 3.5h9A2.5 2.5 0 0 1 17.5 6v14.5H6A2.5 2.5 0 0 1 3.5 18V6A2.5 2.5 0 0 1 6 3.5Z"/>
                            <path d="M17.5 6H20v14.5H6"/>
                            <path d="M7 8h6M7 11.5h6"/>
                        </svg>
                    </div>
                    <h3 class="format-card__title">Paperback</h3>
                    <p class="format-card__text">Print-ready interior and cover files sized for on-demand printing through Barnes &amp; Noble, Amazon, and Ingram.</p>
                </article>
            </div>

            <div class="col-12 col-md-6 col-lg-4" data-animation="fade-up">
                <article class="format-card">
                    <div class="format-card__icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 6.5C10.4 5.2 8.5 4.5 6 4.5H3.5v13H6c2.5 0 4.4.7 6 2 1.6-1.3 3.5-2 6-2h2.5v-13H18c-2.5 0-4.4.7-6 2Z"/>
                            <path d="M12 6.5v14"/>
                        </svg>
                    </div>
                    <h3 class="format-card__title">Hardcover</h3>
                    <p class="format-card__text">Premium hardcover layout with dust-jacket-ready cover design for authors who want a lasting physical edition.</p>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- ============================ 3 · PACKAGES ============================ -->
<section class="section" id="packages">
    <div class="container-lp container-lp--narrow">
        <div class="section-head" data-animation="fade-up">
            <h2 class="section-head__title">Publishing Packages For <br class="br-lg"> Every Author</h2>
        </div>

        <div class="pricing-grid" data-stagger>
            <!-- Basic -->
            <article class="price-card" data-animation="fade-up">
                <div class="price-card__inner">
                    <div class="price-card__top">
                        <div class="price-card__icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 6.5C10.4 5.2 8.5 4.5 6 4.5H3.5v13H6c2.5 0 4.4.7 6 2 1.6-1.3 3.5-2 6-2h2.5v-13H18c-2.5 0-4.4.7-6 2Z"/>
                                <path d="M12 6.5v14"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="price-card__name">Basic Package</h3>
                    <p class="price-card__price">
                        <span class="price-card__currency">$</span>
                        <span class="price-card__amount">700</span>
                        <span class="price-card__period">/ month</span>
                    </p>
                    <ul class="price-list">
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Professional Editing</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Basic Book Formatting</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Simple Cover Design</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Publishing Assistance</li>
                    </ul>
                    <a class="btn-lp btn-lp--outline btn-lp--block" href="#start">Get Started</a>
                </div>
            </article>

            <!-- Standard -->
            <article class="price-card price-card--featured" data-animation="fade-up">
                <div class="price-card__inner">
                    <div class="price-card__top">
                        <div class="price-card__icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.6 13.7 8l5.4 1.7-5.4 1.8L12 17l-1.7-5.5L4.9 9.7 10.3 8Z"/>
                                <path d="M18.4 14.6l.8 2.4 2.4.8-2.4.8-.8 2.4-.8-2.4-2.4-.8 2.4-.8Z" opacity=".75"/>
                            </svg>
                        </div>
                        <span class="price-badge">Most Popular</span>
                    </div>
                    <h3 class="price-card__name">Standard Package</h3>
                    <p class="price-card__price">
                        <span class="price-card__currency">$</span>
                        <span class="price-card__amount">1000</span>
                        <span class="price-card__period">/ month</span>
                    </p>
                    <ul class="price-list">
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Ghostwriting Support</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Editing &amp; Proofreading</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Custom Book Cover Design</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Publishing &amp; Distribution</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Interior Formatting</li>
                    </ul>
                    <a class="btn-lp btn-lp--block" href="#start">Get Started</a>
                </div>
            </article>

            <!-- Premium -->
            <article class="price-card" data-animation="fade-up">
                <div class="price-card__inner">
                    <div class="price-card__top">
                        <div class="price-card__icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 7.5 7 11l5-6.5 5 6.5 4-3.5-1.6 9.5H4.6Z"/>
                                <rect x="4.6" y="18" width="14.8" height="2.2" rx="1.1"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="price-card__name">Premium Package</h3>
                    <p class="price-card__price">
                        <span class="price-card__currency">$</span>
                        <span class="price-card__amount">1500</span>
                        <span class="price-card__period">/ month</span>
                    </p>
                    <ul class="price-list">
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Full Ghostwriting Service</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Advanced Editing &amp; Proofreading</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Premium Cover &amp; Layout Design</li>
                        <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0Zm4.7 7.6-5.3 5.8a1 1 0 0 1-1.5 0L5.3 10.7a1 1 0 1 1 1.5-1.3l1.9 2.1 4.5-5a1 1 0 0 1 1.5 1.1Z"/></svg>Global Publishing &amp; Distribution</li>
                    </ul>
                    <a class="btn-lp btn-lp--outline btn-lp--block" href="#start">Get Started</a>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- ============================ 4 · CTA + FORM ============================ -->
<section class="section section--grey" id="start">
    <div class="container-lp">
        <div class="cta-split">
            <div data-animation="fade-right">
                <h2 class="cta-split__title">Ready To Publish Your <br class="br-lg"> Audiobook?</h2>
                <p class="cta-split__text">Submit your manuscript today, and let our team turn it into a high-quality audiobook. From narration to marketing, we handle every step of the process. Fill out the form to get started, and we&rsquo;ll guide you through the publishing journey.</p>

                <h3 class="expect-title">What To Expect?</h3>
                <ul class="expect-list">
                    <li>30-minute private strategy session to validate your book idea and eliminate confusion with expert clarity</li>
                    <li>In-depth discussion of your project brief with a step-by-step roadmap from concept to execution</li>
                    <li>Tailored publishing strategy aligned with your goals, including scope, timeline, and transparent pricing</li>
                    <li>This is a results-focused consultation designed for serious authors ready to invest in bringing their vision to life</li>
                </ul>

                <div class="cta-split__actions">
                    <a class="btn-lp" href="#manuscriptForm">Get Started</a>
                </div>
            </div>

            <div data-animation="fade-left">
                <div class="form-card">
                    <h3 class="form-card__title">Start Your Book Today</h3>

                    <!-- No backend is wired up: point `action` at your endpoint, or
                         handle the `manuscript:submit` event dispatched in main.js. -->
                    <form id="manuscriptForm" novalidate
                          method="post"
                          action="<?= esc(ep_lp_action()) ?>"
                          enctype="multipart/form-data">
                        <?= ep_lp_hidden_fields($lp) ?>

                        <?php if ($lpFlash['status'] === 'error'): ?>
                          <?php /* Shown only when the post was made with JavaScript
                                   off. With it on, the same text arrives as JSON and
                                   is written into #formStatus without a reload. */ ?>
                          <p class="form-status is-error" role="alert"><?= esc($lpFlash['message']) ?></p>
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <div class="field">
                                    <label class="visually-hidden" for="fullName">Full name</label>
                                    <input class="field__control" type="text" id="fullName" name="fullName" placeholder="Full Name" autocomplete="name" required value="<?= esc(ep_lp_old('name')) ?>">
                                    <span class="field__error" data-error-for="fullName"></span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="field">
                                    <label class="visually-hidden" for="email">Email address</label>
                                    <input class="field__control" type="email" id="email" name="email" placeholder="Email Address" autocomplete="email" required value="<?= esc(ep_lp_old('email')) ?>">
                                    <span class="field__error" data-error-for="email"></span>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="visually-hidden" for="phone">Phone number</label>
                            <input class="field__control" type="tel" id="phone" name="phone" placeholder="Phone Number" autocomplete="tel" required value="<?= esc(ep_lp_old('phone')) ?>">
                            <span class="field__error" data-error-for="phone"></span>
                        </div>

                        <div class="field field--file">
                            <label class="visually-hidden" for="manuscript">Upload manuscript</label>
                            <span class="field__control" id="manuscriptLabel" aria-hidden="true">
                                <span data-file-name>Upload Manuscript</span>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 15.5V4m0 0L8.5 7.5M12 4l3.5 3.5"/>
                                    <path d="M4.5 15v3.5A1.5 1.5 0 0 0 6 20h12a1.5 1.5 0 0 0 1.5-1.5V15"/>
                                </svg>
                            </span>
                            <input type="file" id="manuscript" name="manuscript" accept=".doc,.docx,.pdf,.rtf,.odt,.epub" aria-describedby="manuscriptHint">
                            <span class="visually-hidden" id="manuscriptHint">Accepted formats: DOC, DOCX, PDF, RTF, ODT, EPUB. Maximum 25 MB.</span>
                            <span class="field__error" data-error-for="manuscript"></span>
                        </div>

                        <div class="field">
                            <label class="visually-hidden" for="notes">Additional notes</label>
                            <textarea class="field__control" id="notes" name="notes" placeholder="Additional Notes..." rows="4"><?= esc(ep_lp_old('message')) ?></textarea>
                        </div>

                        <button class="btn-lp" type="submit" id="manuscriptSubmit">
                            <span class="btn-lp__spinner" aria-hidden="true"></span>
                            <span class="btn-lp__label">Submit Manuscript</span>
                        </button>

                        <p class="form-status" id="formStatus" role="status" aria-live="polite"></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================ 5 · STATS ============================ -->
<section class="section section--black" id="results">
    <h2 class="visually-hidden">Results our authors see</h2>
    <div class="container-lp">
        <div class="stats-grid" data-stagger>
            <div class="stat" data-animation="fade-up">
                <p class="eyebrow stat__label">Proven Success Stories</p>
                <p class="stat__value"><span data-count-to="137">137</span>+<br>Best Sellers</p>
                <p class="stat__text">Our programs have empowered authors to reach bestseller status, with countless success stories spanning multiple genres</p>
            </div>

            <div class="stat stat--card" data-animation="fade-up">
                <p class="eyebrow stat__label">Growth Revenue In</p>
                <p class="stat__value">Millions</p>
                <p class="stat__text">Our authors have generated millions in royalties, transforming their books into highly profitable ventures</p>
            </div>

            <div class="stat" data-animation="fade-up">
                <p class="eyebrow stat__label">Google Rating</p>
                <p class="stat__value">4.9+</p>
                <p class="stat__text">Our courses consistently earn top ratings, boasting an average Google score of 4.9</p>
            </div>

            <img class="stats-art stats-art--arrow" src="assets/img/art-bestseller-arrow.webp" alt="" width="545" height="533" loading="lazy" decoding="async" data-animation="zoom-in">

            <div class="stat" data-animation="fade-up">
                <p class="eyebrow stat__label">Done For You</p>
                <p class="stat__value">Publishing Services</p>
                <p class="stat__text">Tap into the expertise of a skilled team of coaches, ghostwriters, and editors committed to your success.</p>
            </div>

            <div class="stat stat--card" data-animation="fade-up">
                <p class="eyebrow stat__label">Dynamic Community</p>
                <p class="stat__value"><span data-count-to="70">70</span>+</p>
                <p class="stat__text">Join a vibrant community of over 70,000 members, all supporting and inspiring one another every step of the way</p>
            </div>

            <img class="stats-art stats-art--book" src="assets/img/art-black-book.webp" alt="" width="361" height="385" loading="lazy" decoding="async" data-animation="zoom-in">

            <div class="stat stat--card" data-animation="fade-up">
                <p class="eyebrow stat__label">Countries Published</p>
                <p class="stat__value"><span data-count-to="50">50</span>+</p>
                <p class="stat__text">Expand your reach globally with our proven publishing methods and expert services</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================ 6 · WHY CHOOSE ============================ -->
<section class="section section--navy" id="why-choose">
    <div class="container-lp">
        <div class="split split--media-left">
            <div class="split__media" data-animation="fade-right">
                <img class="book-cluster" src="assets/img/books-why-choose.webp" alt="Audiobook editions of It&rsquo;s Not Easy Being a Bunny, Project Hail Mary and The Correspondent" width="814" height="502" loading="lazy" decoding="async">
            </div>

            <div data-animation="fade-left">
                <h2 class="split__title">Why Choose Our Audiobook <br class="br-lg"> Publishing Services?</h2>
                <p class="split__text">Hiring professional audiobook publishing services ensures your audiobook is produced with the highest standards. From expert narration to flawless audio editing and global distribution, our services save you time and effort while guaranteeing a quality product that resonates with listeners and helps you build a loyal audience.</p>
                <div class="split__actions">
                    <a class="btn-lp btn-lp--light" href="#start">Get Started</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================ 7 · SERVICES ============================ -->
<section class="section" id="services">
    <div class="container-lp">
        <div class="section-head" data-animation="fade-up">
            <h2 class="section-head__title">Our Audiobook Publishing Services</h2>
        </div>

        <div class="services-grid" data-stagger>
            <article class="service-card" data-animation="fade-up">
                <div class="service-card__icon" aria-hidden="true">
                    <img src="assets/img/icon-narration.webp" alt="" width="72" height="73" loading="lazy">
                </div>
                <h3 class="service-card__title">Audiobook Narration</h3>
                <p class="service-card__text">We connect you with the best audiobook narrators who bring your story to life with professional voice acting, ensuring your audiobook sounds dynamic and engaging, perfect for capturing the attention of listeners.</p>
            </article>

            <article class="service-card" data-animation="fade-up">
                <div class="service-card__icon" aria-hidden="true">
                    <img src="assets/img/icon-production.webp" alt="" width="72" height="73" loading="lazy">
                </div>
                <h3 class="service-card__title">Audio Production</h3>
                <p class="service-card__text">Our audiobook services include full audio production, from high-quality recording to mastering, ensuring crisp, clear sound and professional editing. We handle every technical detail for a seamless listening experience.</p>
            </article>

            <article class="service-card" data-animation="fade-up">
                <div class="service-card__icon" aria-hidden="true">
                    <img src="assets/img/icon-distribution.webp" alt="" width="72" height="73" loading="lazy">
                </div>
                <h3 class="service-card__title">Distribution</h3>
                <p class="service-card__text">We help you distribute your audiobook to major platforms like Audible, iTunes, and Google Play. Our audiobook publishing companies ensure that your audiobook reaches listeners across the world with ease.</p>
            </article>

            <article class="service-card" data-animation="fade-up">
                <div class="service-card__icon" aria-hidden="true">
                    <img src="assets/img/icon-marketing.webp" alt="" width="72" height="73" loading="lazy">
                </div>
                <h3 class="service-card__title">Marketing and Promotion</h3>
                <p class="service-card__text">Our audiobook publishing services also include comprehensive marketing strategies to promote your audiobook. We utilize digital marketing, social media campaigns, and influencer outreach to boost your book&rsquo;s visibility and sales.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============================ 8 · PROCESS ============================ -->
<section class="section section--black" id="process">
    <div class="container-lp">
        <div class="section-head" data-animation="fade-up">
            <h2 class="section-head__title">Our 4-Step Audiobook Publishing <br class="br-lg"> Process</h2>
        </div>

        <div class="process-grid" data-stagger>
            <article class="process-card" data-animation="fade-up">
                <div class="process-card__head">
                    <p class="process-card__num">01</p>
                    <h3 class="process-card__title">Manuscript Review</h3>
                </div>
                <p class="process-card__body">We begin by thoroughly reviewing your manuscript to understand the tone, style, and narrative voice. This allows us to select the ideal narrator and establish a clear production timeline. We ensure that every detail aligns with your vision before moving on to the next stage.</p>
            </article>

            <article class="process-card" data-animation="fade-up">
                <div class="process-card__head">
                    <p class="process-card__num">02</p>
                    <h3 class="process-card__title">Audiobook Narration</h3>
                </div>
                <p class="process-card__body">Our talented audiobook narrators begin recording, capturing your story&rsquo;s true essence with professional vocal skills. We ensure consistency throughout, from tone to pacing. You&rsquo;ll have the opportunity to provide feedback after each recording session, ensuring that your audiobook sounds exactly as you envisioned and resonates with your audience.</p>
            </article>

            <article class="process-card" data-animation="fade-up">
                <div class="process-card__head">
                    <p class="process-card__num">03</p>
                    <h3 class="process-card__title">Editing and Mastering</h3>
                </div>
                <p class="process-card__body">After the narration, we meticulously edit and master the audio to ensure top-notch quality. This includes eliminating background noise, adjusting sound levels, and removing unnecessary pauses or mistakes. We then fine-tune the recording, making sure it&rsquo;s polished and ready for seamless distribution to various audiobook platforms.</p>
            </article>

            <article class="process-card" data-animation="fade-up">
                <div class="process-card__head">
                    <p class="process-card__num">04</p>
                    <h3 class="process-card__title">Distribution and Promotion</h3>
                </div>
                <p class="process-card__body">We distribute your audiobook to major platforms such as Audible, iTunes, and Google Play, maximizing its reach. Our promotion team then uses targeted marketing strategies to boost visibility and drive sales, ensuring your audiobook gains the exposure it deserves and reaches a wide, engaged audience, increasing listener retention.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============================ 9 · RIGHT CHOICE ============================ -->
<section class="section" id="right-choice">
    <div class="container-lp">
        <div class="split split--media-left">
            <div class="split__media" data-animation="fade-right">
                <img class="book-cluster" src="assets/img/books-right-choice.webp" alt="Audiobook editions of Judge Stone, Game On and The Correspondent" width="814" height="471" loading="lazy" decoding="async">
            </div>

            <div data-animation="fade-left">
                <h2 class="split__title">Why Elite Publishing Is The <br class="br-lg"> Right Choice <span class="accent">For Audiobook <br class="br-lg"> Publishing</span></h2>
                <p class="split__text">Elite Publishing is your trusted partner for audiobook publishing. We connect you with the best audiobook narrators and handle the technical aspects of production, ensuring a professional, polished final product. Our audiobook services are designed to save you time while giving you the quality and reach your work deserves.</p>
                <p class="split__text">Whether you&rsquo;re a first-time author or an experienced writer, we provide end-to-end services, from narration to distribution and marketing. Our team understands the unique demands of audiobook publishing and will help you navigate every step with ease.</p>
                <div class="split__actions">
                    <a class="btn-lp" href="#start">Publish today</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================ 10 · GO PRO ============================ -->
<section class="section section--black" id="go-pro">
    <div class="container-lp">
        <div class="section-head" data-animation="fade-up">
            <h2 class="section-head__title">Go Pro With Us</h2>
        </div>

        <div class="pro-list" data-stagger>
            <div class="pro-row" data-animation="fade-up">
                <div>
                    <p class="pro-row__num">01</p>
                    <h3 class="pro-row__title">Top-Tier Quality</h3>
                </div>
                <p class="pro-row__text">We don&rsquo;t do good enough. We do great. Your book deserves to look its best, and we do it with compromise.</p>
            </div>

            <div class="pro-row" data-animation="fade-up">
                <div>
                    <p class="pro-row__num">02</p>
                    <h3 class="pro-row__title">Your Ideas Matter</h3>
                </div>
                <p class="pro-row__text">This is your book, your way. We don&rsquo;t take over. Everything we do is to help your story or project be the best it can be while keeping it 100% yours.</p>
            </div>

            <div class="pro-row" data-animation="fade-up">
                <div>
                    <p class="pro-row__num">03</p>
                    <h3 class="pro-row__title">All-In-One Support</h3>
                </div>
                <p class="pro-row__text">We&rsquo;ve got everything covered. You don&rsquo;t need to juggle a bunch of services. We keep it all in one place, so it&rsquo;s easy for you.</p>
            </div>

            <div class="pro-row" data-animation="fade-up">
                <div>
                    <p class="pro-row__num">04</p>
                    <h3 class="pro-row__title">We&rsquo;re On Your Team</h3>
                </div>
                <p class="pro-row__text">We care about your book as much as you do and want to see it succeed. You&rsquo;ll always feel supported and never like you&rsquo;re figuring things out alone.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================ 11 · TESTIMONIALS ============================ -->
<section class="section testimonials" id="testimonials">
    <div class="container-lp">
        <div class="section-head" data-animation="fade-up">
            <h2 class="section-head__title">What Authors Are Saying About <br class="br-lg"> Our Audiobook Services</h2>
            <p class="section-head__sub">Real feedback from authors who trusted us with their audiobooks</p>
        </div>

        <div class="carousel carousel--focus" id="testimonialCarousel" data-animation="fade-up">
            <div class="carousel__viewport">
                <ul class="carousel__track" id="testimonialTrack" aria-live="polite">
                    <li class="carousel__slide">
                        <article class="testimonial testimonial--rating-green">
                            <div class="testimonial__head">
                                <div class="testimonial__person">
                                    <img class="testimonial__avatar" src="assets/img/author-marcus-vance.webp" alt="" width="44" height="44" decoding="async">
                                    <div>
                                        <p class="testimonial__name">Marcus Vance,</p>
                                        <p class="testimonial__role">Author of Shadows Over Orion</p>
                                    </div>
                                </div>
                                <p class="testimonial__date"><time datetime="2026-04-14">Apr 14, 2026</time></p>
                            </div>
                            <p class="testimonial__stars" aria-label="Rated 5 out of 5">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                            </p>
                            <blockquote class="testimonial__quote">&ldquo;Working with Elite Publishing made the entire self-publishing process effortless. Their editors were thorough, the cover design blew me away, and my book hit the top new releases in its category within a week.&rdquo;</blockquote>
                            <p class="testimonial__source"><img src="assets/img/mark-trustpilot.svg" alt="Reviewed on Trustpilot" width="26" height="26"></p>
                        </article>
                    </li>

                    <li class="carousel__slide">
                        <article class="testimonial">
                            <div class="testimonial__head">
                                <div class="testimonial__person">
                                    <img class="testimonial__avatar" src="assets/img/author-elena-rostova.webp" alt="" width="44" height="44" decoding="async">
                                    <div>
                                        <p class="testimonial__name">Elena Rostova,</p>
                                        <p class="testimonial__role">Author of The Botanical Table</p>
                                    </div>
                                </div>
                                <p class="testimonial__date"><time datetime="2026-04-08">Apr 08, 2026</time></p>
                            </div>
                            <p class="testimonial__stars" aria-label="Rated 5 out of 5">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                            </p>
                            <blockquote class="testimonial__quote">&ldquo;If you want to hire a book publisher that actually respects your creative vision, look no further. The audiobook narration they arranged was Broadway-quality, and my royalties go straight to me.&rdquo;</blockquote>
                            <p class="testimonial__source"><img src="assets/img/mark-google.svg" alt="Reviewed on Google" width="26" height="26"></p>
                        </article>
                    </li>

                    <li class="carousel__slide">
                        <article class="testimonial testimonial--rating-green">
                            <div class="testimonial__head">
                                <div class="testimonial__person">
                                    <img class="testimonial__avatar" src="assets/img/author-david-k.webp" alt="" width="44" height="44" decoding="async">
                                    <div>
                                        <p class="testimonial__name">David K.,</p>
                                        <p class="testimonial__role">Children&rsquo;s Book Author</p>
                                    </div>
                                </div>
                                <p class="testimonial__date"><time datetime="2026-03-20">Mar 20, 2026</time></p>
                            </div>
                            <p class="testimonial__stars" aria-label="Rated 5 out of 5">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1 2.6 5.6 6 .8-4.4 4.2 1.1 6L10 14.8 4.7 17.6l1.1-6L1.4 7.4l6-.8Z"/></svg>
                            </p>
                            <blockquote class="testimonial__quote">&ldquo;As a first-time writer looking for reliable book publishing services, I was terrified of making costly mistakes. Elite Publishing guided me step-by-step through manuscript formatting, editing, and launch marketing.&rdquo;</blockquote>
                            <p class="testimonial__source"><img src="assets/img/mark-trustpilot.svg" alt="Reviewed on Trustpilot" width="26" height="26"></p>
                        </article>
                    </li>
                </ul>
            </div>

            <div class="carousel__controls">
                <button class="carousel__btn carousel__btn--prev" type="button" data-carousel-prev aria-controls="testimonialTrack" aria-label="Previous testimonial">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m0 0 6-6m-6 6 6 6"/></svg>
                </button>
                <button class="carousel__btn" type="button" data-carousel-next aria-controls="testimonialTrack" aria-label="Next testimonial">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- ============================ 12 · FAQ ============================ -->
<section class="section section--grey" id="faqs">
    <div class="container-lp">
        <div class="section-head" data-animation="fade-up">
            <h2 class="section-head__title">FAQs</h2>
        </div>

        <div class="faq-list" data-stagger>
            <div class="faq-item" data-animation="fade-up">
                <h3>
                    <button class="faq-item__trigger" type="button" id="faq1-t" aria-expanded="false" aria-controls="faq1-p">
                        <span>What Authors Are Saying About Our Audiobook Services</span>
                        <span class="faq-item__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></span>
                    </button>
                </h3>
                <div class="faq-item__panel" id="faq1-p" role="region" aria-labelledby="faq1-t">
                    <p class="faq-item__answer">Audiobook publishing is the process of converting written material into audio format, including narration, recording, editing, and distribution.</p>
                </div>
            </div>

            <div class="faq-item" data-animation="fade-up">
                <h3>
                    <button class="faq-item__trigger" type="button" id="faq2-t" aria-expanded="false" aria-controls="faq2-p">
                        <span>How Long Does It Take To Publish An Audiobook?</span>
                        <span class="faq-item__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></span>
                    </button>
                </h3>
                <div class="faq-item__panel" id="faq2-p" role="region" aria-labelledby="faq2-t">
                    <p class="faq-item__answer">The timeline varies, but typically takes between 6-8 weeks from start to finish, depending on the complexity and length of your book.</p>
                </div>
            </div>

            <div class="faq-item" data-animation="fade-up">
                <h3>
                    <button class="faq-item__trigger" type="button" id="faq3-t" aria-expanded="false" aria-controls="faq3-p">
                        <span>Can I Hire A Ghostwriter For My Audiobook?</span>
                        <span class="faq-item__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></span>
                    </button>
                </h3>
                <div class="faq-item__panel" id="faq3-p" role="region" aria-labelledby="faq3-t">
                    <p class="faq-item__answer">Yes, we offer ghostwriting services for authors who want to have their story told but don&rsquo;t have the time or expertise to write the manuscript.</p>
                </div>
            </div>

            <div class="faq-item" data-animation="fade-up">
                <h3>
                    <button class="faq-item__trigger" type="button" id="faq4-t" aria-expanded="false" aria-controls="faq4-p">
                        <span>Do You Help With Audiobook Marketing?</span>
                        <span class="faq-item__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></span>
                    </button>
                </h3>
                <div class="faq-item__panel" id="faq4-p" role="region" aria-labelledby="faq4-t">
                    <p class="faq-item__answer">Yes, we provide comprehensive audiobook promotion services to ensure your book reaches a wide audience.</p>
                </div>
            </div>

            <div class="faq-item" data-animation="fade-up">
                <h3>
                    <button class="faq-item__trigger" type="button" id="faq5-t" aria-expanded="false" aria-controls="faq5-p">
                        <span>How Do I Get Started With Publishing My Audiobook?</span>
                        <span class="faq-item__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></span>
                    </button>
                </h3>
                <div class="faq-item__panel" id="faq5-p" role="region" aria-labelledby="faq5-t">
                    <p class="faq-item__answer">Simply contact us with your manuscript, and we will guide you through every step of the process, from narration to distribution.</p>
                </div>
            </div>

            <div class="faq-item" data-animation="fade-up">
                <h3>
                    <button class="faq-item__trigger" type="button" id="faq6-t" aria-expanded="false" aria-controls="faq6-p">
                        <span>How Do I Know If My Book Is A Good Fit For Audiobook Publishing?</span>
                        <span class="faq-item__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></span>
                    </button>
                </h3>
                <div class="faq-item__panel" id="faq6-p" role="region" aria-labelledby="faq6-t">
                    <p class="faq-item__answer">We can review your book&rsquo;s content and discuss whether audiobook format is right for your target audience and goals.</p>
                </div>
            </div>
        </div>
    </div>
</section>

</main>

<?php /* The landing-page footer, from includes/components/lp-chrome-footer.php.
         It replaces this page's own footer, which linked to /terms-of-service —
         a path that does not exist on this site and returned a 404. The
         replacement's legal links are built from ep_page_url(), so they resolve
         to the real pages and cannot go stale. */ ?>
<?php require __DIR__ . '/../includes/lp-footer.php'; ?>

<button class="to-top" type="button" id="toTop" aria-label="Back to top">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 15 6-6 6 6"/></svg>
</button>

<script src="<?= esc(ep_lp_asset($lp, 'js/main.js')) ?>" defer></script>
</body>
</html>
