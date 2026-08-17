<?php
/**
 * LP1 — standalone campaign landing page.
 *
 * Was index.html. It is PHP now for one reason: the enquiry form needs a CSRF
 * token minted per session, and a static file cannot mint one. Everything else
 * on the page — markup, CSS, JavaScript, images — is unchanged and still lives
 * entirely inside this folder.
 */
$lp = 'lp1';
require __DIR__ . '/../includes/lp-bootstrap.php';
$lpFlash = ep_lp_flash();
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="theme-color" content="#005eda" />

        <title>
            Elite Publishing — Award-Winning Children's Book Publishing Company
        </title>
        <meta
            name="description"
            content="Elite Publishing is an award-winning, independent children's book publisher. End-to-end self-publishing with 17K+ stories launched on 50+ platforms, zero royalties and no rejections."
        />
        <?php /* Points at this page, not the home page. It said
                 "https://elitepublishing.co/", which tells a search engine that
                 this landing page IS the home page — the usual outcome is the
                 LP being dropped from the index and its ad traffic landing on a
                 page that no longer ranks for anything it was written for. */ ?>
        <link rel="canonical" href="https://elitepublishing.co/lp1/" />
        <?php /* max-image-preview:large brought into line with LP2 and LP4, which
                 already declared it. Without it Google is limited to a 128px
                 thumbnail for a page whose subject is book covers. */ ?>
        <meta name="robots" content="index, follow, max-image-preview:large" />

        <!-- Open Graph -->
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="Elite Publishing" />
        <meta
            property="og:title"
            content="Elite Publishing — Award-Winning Children's Book Publishing Company"
        />
        <meta
            property="og:description"
            content="End-to-end children's book publishing. 17K+ stories launched on 50+ platforms, 100% royalty-free, no rejections."
        />
        <meta property="og:url" content="https://elitepublishing.co/lp1/" />
        <?php /* These pointed at /images/hero-bg.jpg. This page's images live in
                 /lp1/images/, so the URL 404'd and every share of this landing
                 page rendered as a bare text card. The declared 1920x754 was
                 right — it is the real size of the file — only the path was
                 missing its directory. */ ?>
        <meta
            property="og:image"
            content="https://elitepublishing.co/lp1/images/hero-bg.jpg"
        />
        <meta property="og:image:width" content="1920" />
        <meta property="og:image:height" content="754" />
        <meta
            property="og:image:alt"
            content="Illustrated sunrise landscape from the Elite Publishing hero"
        />
        <meta property="og:locale" content="en_US" />

        <!-- X / Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta
            name="twitter:title"
            content="Elite Publishing — Award-Winning Children's Book Publishing Company"
        />
        <meta
            name="twitter:description"
            content="End-to-end children's book publishing. 17K+ stories launched on 50+ platforms, 100% royalty-free, no rejections."
        />
        <meta
            name="twitter:image"
            content="https://elitepublishing.co/lp1/images/hero-bg.jpg"
        />
        <meta
            name="twitter:image:alt"
            content="Illustrated sunrise landscape from the Elite Publishing hero"
        />

        <?= ep_favicon_tags() ?>

        <!-- Fonts: preload only the two faces used above the fold. -->
        <link
            rel="preload"
            href="fonts/urbanist-600.woff2"
            as="font"
            type="font/woff2"
            crossorigin
        />
        <link
            rel="preload"
            href="fonts/satoshi-500.woff2"
            as="font"
            type="font/woff2"
            crossorigin
        />
        <link rel="preload" href="images/hero-bg.webp" as="image" />

        <?php /* ep_lp_asset() appends the file's mtime, so an edited stylesheet
                 reaches every browser on the next request instead of waiting out
                 a cache lifetime. See the note on the function. */ ?>
        <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/bootstrap.min.css')) ?>" />
        <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/variables.css')) ?>" />
        <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/base.css')) ?>" />
        <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/components.css')) ?>" />
        <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/sections.css')) ?>" />
        <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/animations.css')) ?>" />
        <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'css/responsive.css')) ?>" />

        <?php
        /* Built from includes/config.php rather than typed out, so the address
           and the mailbox cannot drift away from the ones the rest of the site
           publishes.

           Three corrections to what was here:
             - logo pointed at /images/svg/logo.svg, which 404s; this page's
               images are under /lp1/. It now names the site logo the main site's
               Organization schema already publishes, so both agree.
             - "Award-winning" is dropped. It is a claim about ourselves with
               nothing behind it, and as structured data it is asserted to search
               engines as fact rather than as marketing copy.
             - telephone is dropped rather than corrected. It read
               "+1-000-123-4567" and EP_PHONE is "+1 (800) 000-0000" — both are
               placeholders, and publishing a number that does not ring is worse
               than publishing none. Restore it here and in the modal below once
               there is a real one. */
        $lp1Schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => EP_NAME,
            'url'         => 'https://elitepublishing.co/',
            /* Literal, like the canonical and og:url above it. asset() would
               prefix EP_BASE, which is "/Elite%20Publishing/" on the dev box and
               would emit a broken absolute URL from a local render. */
            'logo'        => 'https://elitepublishing.co/assets/img/logo.png',
            'description' => "Independent children's book publishing company offering end-to-end self-publishing services.",
            'contactPoint' => [
                '@type'       => 'ContactPoint',
                'contactType' => 'customer service',
                'email'       => EP_EMAIL,
            ],
        ];
        if (EP_ADDRESS !== '') {
            $lp1Schema['address'] = ['@type' => 'PostalAddress'] + EP_ADDRESS_PARTS;
        }
        ?>
        <script type="application/ld+json">
<?= json_encode($lp1Schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>
        <?= ep_lp_chrome_styles() ?>
    </head>

    <body>
        <a class="skip-link" href="#main">Skip to content</a>

        <?php /* The landing-page header, from
                 includes/components/lp-chrome-header.php: the Elite Publishing
                 logo and one "Submit Your Book" button, shared by all four LPs.

                 Its CTA carries data-modal-open="contact-modal", so it opens
                 this page's own enquiry modal exactly as the page's original
                 header button did. The href is the contact page, which is only
                 ever followed with JavaScript off — initModal() calls
                 preventDefault() — and is there because the modal is `hidden`
                 without JavaScript and would otherwise be unreachable.

                 The nine other [data-modal-open] controls in the page body are
                 untouched and still open the same modal. */ ?>
        <?php require __DIR__ . '/../includes/lp-header.php'; ?>

        <main id="main" class="page">
            <!-- ============================ HERO ============================ -->
            <section class="hero" aria-labelledby="hero-title">
                <div class="hero__bg">
                    <div class="container hero__content">
                        <div class="hero__text">
                            <h1
                                class="h1 hero__title"
                                id="hero-title"
                                data-animation="fade-up"
                            >
                                An Award-Winning, Independent Children's Book
                                Publishing Company
                            </h1>
                            <p
                                class="lead muted hero__sub"
                                data-animation="fade-up"
                            >
                                Children Publishers specializes in end-to-end
                                children's book publishing and has a proven
                                track record of launching bestsellers of 17K+
                                stories on 50+ platforms—all without rejections.
                            </p>
                        </div>

                        <button
                            class="btn btn--gradient"
                            type="button"
                            data-modal-open="contact-modal"
                            data-animation="fade-up"
                        >
                            <span
                                class="btn__label"
                                data-text="Publish Children's Book Today"
                                >Publish Children's Book Today</span
                            >
                        </button>
                    </div>
                </div>

                <!-- Nine covers, rotated exactly as grouped in the Figma file. -->
                <div class="hero__books" data-stagger>
                    <a class="cover hero__book" href="#books" style="left: 63px; top: 204.5px; rotate: -10deg">
                        <img src="images/book-bunny.webp" alt="It's Not Easy Being a Bunny by Marilyn Sadler" width="257" height="362" loading="eager" decoding="async" />
                    </a>
                    <a class="cover hero__book" href="#books" style="left: 294px; top: 204.5px; rotate: -5deg">
                        <img src="images/book-judge-stone.webp" alt="Judge Stone by James Patterson" width="257" height="362" loading="eager" decoding="async" />
                    </a>
                    <a class="cover hero__book" href="#books" style="left: 525px; top: 204.5px; rotate: -5deg">
                        <img src="images/book-hail-mary.webp" alt="Project Hail Mary by Andy Weir" width="257" height="362" loading="eager" decoding="async" />
                    </a>
                    <a class="cover hero__book" href="#books" style="left: 756px; top: 204.5px; rotate: -5deg">
                        <img src="images/book-correspondent.webp" alt="The Correspondent by Virginia Evans" width="257" height="362" loading="eager" decoding="async" />
                    </a>
                    <a class="cover hero__book" href="#books" style="left: 986.5px; top: 204.5px; rotate: -2deg">
                        <img src="images/book-game-on.webp" alt="Game On by Navessa Allen" width="257" height="362" loading="eager" decoding="async" />
                    </a>
                    <a class="cover hero__book" href="#books" style="left: 1217px; top: 204.5px; rotate: 3deg">
                        <img src="images/book-dear-debbie.webp" alt="Dear Debbie by Freida McFadden" width="257" height="362" loading="lazy" decoding="async" />
                    </a>
                    <a class="cover hero__book" href="#books" style="left: 1448px; top: 204px; rotate: -9deg">
                        <img src="images/book-theo-of-golden.webp" alt="Theo of Golden by Allen Levi" width="257" height="362" loading="lazy" decoding="async" />
                    </a>
                    <a class="cover hero__book" href="#books" style="left: 1643.5px; top: 190.5px; rotate: -2deg">
                        <img src="images/book-warriors.webp" alt="Warriors: The New Prophecy by Erin Hunter" width="257" height="362" loading="lazy" decoding="async" />
                    </a>
                    <a class="cover hero__book" href="#books" style="left: 1868.5px; top: 193.5px; rotate: 6deg">
                        <img src="images/book-the-divorce.webp" alt="The Divorce by Freida McFadden" width="257" height="362" loading="lazy" decoding="async" />
                    </a>
                </div>

                <div class="hero__wave" aria-hidden="true"></div>
            </section>

            <!-- ======================= VISIBILITY / VIDEO ======================= -->
            <section class="container split" aria-labelledby="visibility-title">
                <div class="split__copy">
                    <p class="eyebrow" data-animation="fade-right">
                        Boost Visibility Worldwide
                    </p>
                    <h2
                        class="h2"
                        id="visibility-title"
                        data-animation="fade-right"
                    >
                        Get your children's book released globally with
                        America's top publishers and gain 5x more visibility!
                    </h2>
                    <div data-animation="fade-right">
                        <button
                            class="btn btn--accent"
                            type="button"
                            data-modal-open="contact-modal"
                        >
                            <span
                                class="btn__label"
                                data-text="Share Your Manuscript"
                                >Share Your Manuscript</span
                            >
                        </button>
                    </div>
                </div>

                <div
                    class="split__media split__media--video"
                    data-animation="fade-left"
                    data-video
                >
                    <!-- `preload="none"` keeps the 50MB file off the initial
                         load; the poster is the still the design calls for. -->
                    <video
                        data-video-el
                        poster="images/video-thumb.webp"
                        preload="none"
                        playsinline
                        width="694"
                        height="488"
                    >
                        <source src="videos/intro.mp4" type="video/mp4" />
                        Your browser cannot play this video.
                    </video>
                    <button
                        class="video-play"
                        type="button"
                        data-video-play
                        aria-label="Play the Elite Publishing introduction video"
                    >
                        <img
                            src="images/svg/icon-play.svg"
                            alt=""
                            width="90"
                            height="90"
                        />
                    </button>
                </div>
            </section>

            <!-- ============================ PROCEDURE ============================ -->
            <section
                class="container stack"
                aria-labelledby="procedure-title"
            >
                <div class="section-head section-head--narrow" data-animation="fade-up">
                    <h2 class="h2" id="procedure-title">
                        Our Modest Procedure to become a bestselling Author
                    </h2>
                    <p class="lead muted">
                        We've moved beyond the traditional process and
                        streamlined it for a smoother, more enjoyable
                        experience.
                    </p>
                </div>

                <div class="tile-row" data-stagger>
                    <article class="tile">
                        <img
                            class="tile__art"
                            src="images/step-1-manuscript.png"
                            alt=""
                            width="189"
                            height="104"
                            loading="lazy"
                            decoding="async"
                        />
                        <div class="tile__body">
                            <h3 class="h4">Share Your Manuscript</h3>
                            <p class="muted">
                                Submit your story along with the platform you'd
                                want to see them on.
                            </p>
                        </div>
                    </article>

                    <article class="tile">
                        <img
                            class="tile__art"
                            src="images/step-2-format.png"
                            alt=""
                            width="189"
                            height="104"
                            loading="lazy"
                            decoding="async"
                        />
                        <div class="tile__body">
                            <h3 class="h4">Format &amp; Convert</h3>
                            <p class="muted">
                                The team formats and convert your book as per
                                your desired platform.
                            </p>
                        </div>
                    </article>

                    <article class="tile">
                        <img
                            class="tile__art"
                            src="images/step-3-publish.png"
                            alt=""
                            width="189"
                            height="104"
                            loading="lazy"
                            decoding="async"
                        />
                        <div class="tile__body">
                            <h3 class="h4">Publishing And More</h3>
                            <p class="muted">
                                We publish your book within days and market your
                                book if you say so!
                            </p>
                        </div>
                    </article>
                </div>

                <div data-animation="fade-up">
                    <button
                        class="btn btn--accent"
                        type="button"
                        data-modal-open="contact-modal"
                    >
                        <span
                            class="btn__label"
                            data-text="Get Your Kid's Book Released Today"
                            >Get Your Kid's Book Released Today</span
                        >
                    </button>
                </div>
            </section>

            <!-- =================== PACKAGES + BOOK CAROUSEL =================== -->
            <div class="bluewrap">
                <section class="panel-blue" aria-labelledby="packages-title">
                    <img
                        class="scallop scallop--top"
                        src="images/svg/wave-top-blue.svg"
                        alt=""
                        width="1920"
                        height="84"
                        aria-hidden="true"
                    />

                    <div class="panel-blue__body">
                    <img
                        class="doodle doodle--swirl"
                        src="images/doodle-swirl.png"
                        alt=""
                        aria-hidden="true"
                        width="534"
                        height="406"
                        loading="lazy"
                    />
                    <img
                        class="doodle doodle--dashes"
                        src="images/doodle-dashes.png"
                        alt=""
                        aria-hidden="true"
                        width="700"
                        height="500"
                        loading="lazy"
                    />

                    <div class="container panel-blue__inner">
                        <div class="section-head section-head--narrow" data-animation="fade-up">
                            <h2
                                class="h2 text-white"
                                id="packages-title"
                            >
                                Complete Self-Publishing Solutions for
                                Children's Books
                            </h2>
                            <p class="lead muted text-white">
                                Our children's book publishing packages cover
                                everything from editing and illustration to
                                marketing without breaking the bank. Choose the
                                package that fits your needs.
                            </p>
                        </div>

                        <div class="pricing-row" data-stagger>
                            <article class="pricing-card">
                                <img
                                    class="pricing-card__icon"
                                    src="images/pkg-basic.png"
                                    alt=""
                                    width="76"
                                    height="76"
                                    loading="lazy"
                                />
                                <h3 class="pricing-card__title">
                                    Basic Book Publishing Package
                                </h3>
                                <ul class="pricing-card__list">
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Editing &amp; Formatting
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Book Cover Design
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Publishing on 5+ Platforms
                                    </li>
                                    <li data-spacer aria-hidden="true">
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Publishing on 5+ Platforms
                                    </li>
                                    <li data-spacer aria-hidden="true">
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Publishing on 5+ Platforms
                                    </li>
                                </ul>
                                <button
                                    class="btn btn--accent btn--block"
                                    type="button"
                                    data-modal-open="contact-modal"
                                >
                                    <span class="btn__label" data-text="Book A Call">Book A Call</span>
                                </button>
                            </article>

                            <article class="pricing-card">
                                <img
                                    class="badge-best-value"
                                    src="images/svg/badge-best-value.svg"
                                    alt="Best value"
                                    width="114"
                                    height="31"
                                    loading="lazy"
                                />
                                <img
                                    class="pricing-card__icon"
                                    src="images/pkg-premium.png"
                                    alt=""
                                    width="76"
                                    height="76"
                                    loading="lazy"
                                />
                                <h3 class="pricing-card__title">
                                    Premium Publishing &amp; Design Package
                                </h3>
                                <ul class="pricing-card__list">
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Editing &amp; Formatting
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Book Cover Design
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Illustration/Animation Design
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Publishing on 50+ Platforms
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Marketing &amp; Distribution Support
                                    </li>
                                </ul>
                                <button
                                    class="btn btn--accent btn--block"
                                    type="button"
                                    data-modal-open="contact-modal"
                                >
                                    <span class="btn__label" data-text="Book A Call">Book A Call</span>
                                </button>
                            </article>

                            <article class="pricing-card">
                                <img
                                    class="pricing-card__icon"
                                    src="images/pkg-standard.png"
                                    alt=""
                                    width="76"
                                    height="76"
                                    loading="lazy"
                                />
                                <h3 class="pricing-card__title">
                                    Standard Publishing &amp; Marketing Package
                                </h3>
                                <ul class="pricing-card__list">
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Comprehensive Editing
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Advanced Formatting
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Custom Cover &amp; Illustration Design
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Publishing on 10+ Platforms
                                    </li>
                                    <li>
                                        <img src="images/svg/icon-tick.svg" alt="" width="24" height="24" />
                                        Animation &amp; Interactive Design
                                    </li>
                                </ul>
                                <button
                                    class="btn btn--accent btn--block"
                                    type="button"
                                    data-modal-open="contact-modal"
                                >
                                    <span class="btn__label" data-text="Book A Call">Book A Call</span>
                                </button>
                            </article>
                        </div>
                    </div>
                    </div>
                </section>

                <section
                    class="band-tint"
                    id="books"
                    aria-labelledby="launched-title"
                >
                    <div class="band-tint__inner">
                        <h2
                            class="h2 text-navy text-center"
                            id="launched-title"
                            data-animation="fade-up"
                        >
                            Our publishing house has launched books that are
                            kid's all-time favorite
                        </h2>

                        <div
                            class="carousel book-carousel"
                            data-carousel
                            data-autoplay="4000"
                            data-step="269"
                            aria-roledescription="carousel"
                            aria-label="Books we have launched"
                        >
                            <button
                                class="carousel__btn"
                                type="button"
                                data-carousel-prev
                                aria-label="Previous books"
                            >
                                <img src="images/svg/icon-arrow-left.svg" alt="" width="24" height="24" />
                            </button>

                            <div class="carousel__viewport">
                                <ul class="carousel__track" data-carousel-track>
                                    <li class="carousel__slide">
                                        <span class="cover">
                                            <img src="images/book-game-on.webp" alt="Game On by Navessa Allen" width="239" height="334" loading="lazy" decoding="async" />
                                        </span>
                                    </li>
                                    <li class="carousel__slide">
                                        <span class="cover">
                                            <img src="images/book-judge-stone.webp" alt="Judge Stone by James Patterson" width="239" height="334" loading="lazy" decoding="async" />
                                        </span>
                                    </li>
                                    <li class="carousel__slide">
                                        <span class="cover">
                                            <img src="images/book-correspondent.webp" alt="The Correspondent by Virginia Evans" width="239" height="334" loading="lazy" decoding="async" />
                                        </span>
                                    </li>
                                    <li class="carousel__slide">
                                        <span class="cover">
                                            <img src="images/book-dear-debbie.webp" alt="Dear Debbie by Freida McFadden" width="239" height="334" loading="lazy" decoding="async" />
                                        </span>
                                    </li>
                                    <li class="carousel__slide">
                                        <span class="cover">
                                            <img src="images/book-bunny.webp" alt="It's Not Easy Being a Bunny by Marilyn Sadler" width="239" height="334" loading="lazy" decoding="async" />
                                        </span>
                                    </li>
                                    <li class="carousel__slide">
                                        <span class="cover">
                                            <img src="images/book-hail-mary.webp" alt="Project Hail Mary by Andy Weir" width="239" height="334" loading="lazy" decoding="async" />
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <button
                                class="carousel__btn"
                                type="button"
                                data-carousel-next
                                aria-label="Next books"
                            >
                                <img src="images/svg/icon-arrow-right.svg" alt="" width="24" height="24" />
                            </button>
                        </div>
                    </div>

                    <div class="wave wave--abs wave--bottom" aria-hidden="true"></div>
                </section>
            </div>

            <!-- ============================ FEATURES ============================ -->
            <section class="container stack" aria-labelledby="features-title">
                <div class="section-head section-head--wide" data-animation="fade-up">
                    <h2 class="h2" id="features-title">
                        Top Features That Makes Our Kids Publishing Agency #1 in
                        the United States and Beyond
                    </h2>
                    <p class="lead muted">
                        When it comes to features, there's no other children's
                        book publishing company that offers such value. Here's
                        what you can earn with just one request of 'Publish my
                        child's book':
                    </p>
                </div>

                <div class="tile-row" data-stagger>
                    <article class="tile">
                        <span class="tile__chip" style="background-color: #0071e2">
                            <img src="images/svg/icon-book.svg" alt="" width="32" height="32" loading="lazy" />
                        </span>
                        <div class="tile__body">
                            <h3 class="h4">Rejection-proof launch</h3>
                            <p class="muted">
                                We guarantee rejection-free and smooth
                                publication process to our clients.
                            </p>
                        </div>
                    </article>

                    <article class="tile">
                        <span class="tile__chip" style="background-color: #efb624">
                            <img src="images/svg/icon-royalty.svg" alt="" width="32" height="32" loading="lazy" />
                        </span>
                        <div class="tile__body">
                            <h3 class="h4">100% royalty-free</h3>
                            <p class="muted">
                                Keep every single penny of your earnings, as we
                                never take any profit cuts.
                            </p>
                        </div>
                    </article>

                    <article class="tile">
                        <span class="tile__chip" style="background-color: #8790dc">
                            <img src="images/svg/icon-globe.svg" alt="" width="32" height="32" loading="lazy" />
                        </span>
                        <div class="tile__body">
                            <h3 class="h4">Worldwide readership</h3>
                            <p class="muted">
                                Share mesmerizing stories with the world and
                                capture the untapped audience.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="tile-row" data-stagger>
                    <article class="tile">
                        <span class="tile__chip" style="background-color: #013870">
                            <img src="images/svg/icon-shield.svg" alt="" width="32" height="32" loading="lazy" />
                        </span>
                        <div class="tile__body">
                            <h3 class="h4">100% of your ownership</h3>
                            <p class="muted">
                                Enjoy complete rights authority of the published
                                book since it's solely yours.
                            </p>
                        </div>
                    </article>

                    <article class="tile">
                        <span class="tile__chip" style="background-color: #ff6b5e">
                            <img src="images/svg/icon-money-off.svg" alt="" width="32" height="32" loading="lazy" />
                        </span>
                        <div class="tile__body">
                            <h3 class="h4">No hidden charges</h3>
                            <p class="muted">
                                There are no surprise charges. Pay once and get
                                services without any extra payment.
                            </p>
                        </div>
                    </article>

                    <article class="tile">
                        <span class="tile__chip" style="background-color: #27224e">
                            <img src="images/svg/icon-support.svg" alt="" width="32" height="32" loading="lazy" />
                        </span>
                        <div class="tile__body">
                            <h3 class="h4">24/7 client support</h3>
                            <p class="muted">
                                Whether day or night, our team is at your
                                service for prompt assistance.
                            </p>
                        </div>
                    </article>
                </div>
            </section>

            <!-- ============================ GENRES ============================ -->
            <section class="panel-blue" aria-labelledby="genres-title">
                <img
                    class="scallop scallop--top"
                    src="images/svg/wave-top-blue.svg"
                    alt=""
                    width="1920"
                    height="84"
                    aria-hidden="true"
                />

                <div class="panel-blue__body">
                <img
                    class="doodle doodle--swirl"
                    src="images/doodle-swirl.png"
                    alt=""
                    aria-hidden="true"
                    width="534"
                    height="406"
                    loading="lazy"
                />
                <img
                    class="doodle doodle--starburst"
                    src="images/doodle-starburst.png"
                    alt=""
                    aria-hidden="true"
                    width="141"
                    height="273"
                    loading="lazy"
                />

                <div class="container panel-blue__inner">
                    <div class="section-head" data-animation="fade-up">
                        <h2 class="h2 text-white" id="genres-title">
                            USA's Premier Publishers are entertaining Every
                            Genre that Falls under Children's Book Category
                        </h2>
                    </div>

                    <ul class="genre-rail" data-stagger>
                        <li class="genre">
                            <span class="cover">
                                <img src="images/book-game-on.webp" alt="Game On by Navessa Allen" width="212" height="295" loading="lazy" decoding="async" />
                            </span>
                            <span class="genre__caption">Picture Books</span>
                        </li>
                        <li class="genre">
                            <span class="cover">
                                <img src="images/book-judge-stone.webp" alt="Judge Stone by James Patterson" width="212" height="295" loading="lazy" decoding="async" />
                            </span>
                            <span class="genre__caption">Board Books</span>
                        </li>
                        <li class="genre">
                            <span class="cover">
                                <img src="images/book-correspondent.webp" alt="The Correspondent by Virginia Evans" width="212" height="295" loading="lazy" decoding="async" />
                            </span>
                            <span class="genre__caption">Early Reader Books</span>
                        </li>
                        <li class="genre">
                            <span class="cover">
                                <img src="images/book-dear-debbie.webp" alt="Dear Debbie by Freida McFadden" width="212" height="295" loading="lazy" decoding="async" />
                            </span>
                            <span class="genre__caption">Chapter Books</span>
                        </li>
                        <li class="genre">
                            <span class="cover">
                                <img src="images/book-bunny.webp" alt="It's Not Easy Being a Bunny by Marilyn Sadler" width="212" height="295" loading="lazy" decoding="async" />
                            </span>
                            <span class="genre__caption">Middle-Grade Novels</span>
                        </li>
                        <li class="genre">
                            <span class="cover">
                                <img src="images/book-hail-mary.webp" alt="Project Hail Mary by Andy Weir" width="212" height="295" loading="lazy" decoding="async" />
                            </span>
                            <span class="genre__caption">Young Adult Novels</span>
                        </li>
                    </ul>

                    <div class="genre-actions" data-animation="fade-up">
                        <button
                            class="btn btn--accent"
                            type="button"
                            data-modal-open="contact-modal"
                        >
                            <span class="btn__label" data-text="Publish My Kid's Book">Publish My Kid's Book</span>
                        </button>

                        <button
                            class="btn btn--ghost"
                            type="button"
                            data-modal-open="contact-modal"
                        >
                            <img class="icon-chat-dot" src="images/svg/icon-chat.svg" alt="" width="24" height="24" />
                            <span class="btn__label" data-text="Consult Our Expert">Consult Our Expert</span>
                        </button>
                    </div>
                </div>
                </div>

                <img
                    class="scallop scallop--bottom"
                    src="images/svg/wave-bottom-blue.svg"
                    alt=""
                    width="1920"
                    height="84"
                    aria-hidden="true"
                />
            </section>

            <!-- ============================ PLATFORMS ============================ -->
            <section class="container split" aria-labelledby="platforms-title">
                <div class="split__copy">
                    <p class="eyebrow" data-animation="fade-right">
                        Reach Young Readers Everywhere
                    </p>
                    <h2 class="h2" id="platforms-title" data-animation="fade-right">
                        Self-Publish Your Children's Book On 50+ Acclaimed
                        Platforms, Seamlessly!
                    </h2>
                    <div class="split__body lead muted" data-animation="fade-right">
                        <p>
                            Want to make your story accessible everywhere
                            online? Our children's publishers are accepting
                            submissions from all over the world. You just name
                            the publishing platforms, and we'll do the rest.
                        </p>
                        <p>
                            With our strong distribution network and incredible
                            strategies, we make your book available on every
                            popular platform, including Amazon KDP, Lulu, Apple
                            Books, Kobo, IngramSpark, and more.
                        </p>
                    </div>
                </div>

                <div class="split__media split__media--short" data-animation="fade-left">
                    <img
                        src="images/platforms.webp"
                        alt="A parent and children reading together beside a stack of books"
                        width="694"
                        height="481"
                        loading="lazy"
                        decoding="async"
                    />
                </div>
            </section>

            <!-- =========================== TESTIMONIALS =========================== -->
            <section class="quote-band" aria-labelledby="quote-title">
                <h2 class="visually-hidden" id="quote-title">
                    What our authors say
                </h2>

                <div class="wave wave--abs wave--top wave--flip" aria-hidden="true"></div>

                <div
                    class="quote-band__inner"
                    data-quotes
                    data-autoplay="6000"
                    aria-roledescription="carousel"
                    aria-label="Author testimonials"
                >
                    <figure class="quote-slide" data-quote-slide>
                        <blockquote class="quote__text">
                            "Working with Elite Publishing made the entire
                            self-publishing process effortless. Their editors
                            were thorough, the cover design blew me away, and my
                            book hit the top new releases in its category within
                            a week."
                        </blockquote>
                        <figcaption>
                            <span class="quote__name">Marcus Vance,</span>
                            <span class="quote__role">Author of Shadows Over Orion</span>
                        </figcaption>
                    </figure>

                    <figure class="quote-slide" data-quote-slide hidden>
                        <blockquote class="quote__text">
                            "If you want to hire a book publisher that actually
                            respects your creative vision, look no further. The
                            audiobook narration they arranged was
                            Broadway-quality, and my royalties go straight to
                            me."
                        </blockquote>
                        <figcaption>
                            <span class="quote__name">Elena Rostova,</span>
                            <span class="quote__role">Author of The Botanical Table</span>
                        </figcaption>
                    </figure>

                    <figure class="quote-slide" data-quote-slide hidden>
                        <blockquote class="quote__text">
                            "As a first-time writer looking for reliable book
                            publishing services, I was terrified of making
                            costly mistakes. Elite Publishing guided me
                            step-by-step through manuscript formatting, editing,
                            and launch marketing."
                        </blockquote>
                        <figcaption>
                            <span class="quote__name">David K.,</span>
                            <span class="quote__role">Children's Book Author</span>
                        </figcaption>
                    </figure>

                    <div class="dots" data-quote-dots role="tablist" aria-label="Choose a testimonial"></div>
                </div>

                <div class="wave wave--abs wave--bottom" aria-hidden="true"></div>
            </section>

            <!-- =========================== BESTSELLER =========================== -->
            <section class="container split" aria-labelledby="bestseller-title">
                <div class="split__media split__media--short" data-animation="fade-right">
                    <img
                        src="images/bestseller.webp"
                        alt="A young girl smiling while reading a red hardback book"
                        width="694"
                        height="481"
                        loading="lazy"
                        decoding="async"
                    />
                </div>

                <div class="split__copy">
                    <p class="eyebrow" data-animation="fade-left">
                        15K+ Literary Masterpiece Launched
                    </p>
                    <h2 class="h2" id="bestseller-title" data-animation="fade-left">
                        Children's Book Publishers Turning Your Title into a New
                        York Bestseller
                    </h2>
                    <div class="split__body lead muted" data-animation="fade-left">
                        <p>
                            Do you want to start your journey as a bestselling
                            book? We're just a 'best children's book publishers
                            near me' search away, ready to transform your book
                            and take it to young readers.
                        </p>
                        <p>
                            With our kid's book publishing services, we cover
                            everything from design to launch. That's how we make
                            your book a literary masterpiece and connect it with
                            the next generation of readers!
                        </p>
                    </div>
                </div>
            </section>

            <!-- ========================== CLIENT LOGOS ========================== -->
            <section class="container" aria-label="Trusted by">
                <div class="marquee">
                    <div class="marquee__track" data-marquee>
                        <img src="images/logo-discovery-education.png" alt="Discovery Education" width="122" height="50" loading="lazy" />
                        <img src="images/logo-netflix.png" alt="Netflix" width="118" height="49" loading="lazy" />
                        <img src="images/logo-chronicle-books.png" alt="Chronicle Books" width="154" height="49" loading="lazy" />
                        <img src="images/logo-alaska-airlines.png" alt="Alaska Airlines" width="98" height="49" loading="lazy" />
                        <img src="images/logo-prime-video.png" alt="Prime Video" width="111" height="49" loading="lazy" />
                        <img src="images/logo-atmos-energy.png" alt="Atmos Energy" width="113" height="50" loading="lazy" />
                        <img src="images/logo-joy-rx.png" alt="Joy Rx, Children's Cancer Association" width="108" height="50" loading="lazy" />
                    </div>
                </div>
            </section>

            <!-- ==================== FAQ + FINAL CTA + FOOTER ==================== -->
            <div class="closing">
                <section class="faq-section" aria-labelledby="faq-title">
                    <div class="faq-section__inner">
                        <h2 class="h2 text-navy" id="faq-title" data-animation="fade-up">
                            Frequently asked questions.
                        </h2>

                        <div class="faq" data-stagger>
                            <div class="faq__item">
                                <h3>
                                    <button class="faq__trigger" type="button" aria-expanded="false" aria-controls="faq-panel-1">
                                        <span>Where can I get a children's book published in the first attempt?</span>
                                        <span class="faq__icon">
                                            <img src="images/svg/icon-chevron.svg" alt="" width="33" height="33" />
                                        </span>
                                    </button>
                                </h3>
                                <div class="faq__panel" id="faq-panel-1" role="region">
                                    <div>
                                        <p class="faq__answer">
                                            Publishing on the first attempt without any professional assistance is rare. While other children's publishing companies claim to publish right, only we guarantee a seamless launch of your book. With our solid strategy, we publish effortlessly.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq__item">
                                <h3>
                                    <button class="faq__trigger" type="button" aria-expanded="false" aria-controls="faq-panel-2">
                                        <span>How much money can you make publishing a children's book?</span>
                                        <span class="faq__icon">
                                            <img src="images/svg/icon-chevron.svg" alt="" width="33" height="33" />
                                        </span>
                                    </button>
                                </h3>
                                <div class="faq__panel" id="faq-panel-2" role="region">
                                    <div>
                                        <p class="faq__answer">
                                            If you decide to publish a book from our company, you can make a lot of money. That is because we have a zero royalty policy. You never have to share your revenue, not even a certain percentage. We only charge a minimum one-time fee.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq__item">
                                <h3>
                                    <button class="faq__trigger" type="button" aria-expanded="false" aria-controls="faq-panel-3">
                                        <span>What is the best children's book publisher in the USA and worldwide?</span>
                                        <span class="faq__icon">
                                            <img src="images/svg/icon-chevron.svg" alt="" width="33" height="33" />
                                        </span>
                                    </button>
                                </h3>
                                <div class="faq__panel" id="faq-panel-3" role="region">
                                    <div>
                                        <p class="faq__answer">
                                            With many children's book publishing houses and independent agents in the USA, our agency stands amongst the top publications everywhere, including Florida, New York, Texas, Utah, etc. With no royalties and a quick launch, we're the best.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq__item">
                                <h3>
                                    <button class="faq__trigger" type="button" aria-expanded="false" aria-controls="faq-panel-4">
                                        <span>How many pages should a children's book have to be published?</span>
                                        <span class="faq__icon">
                                            <img src="images/svg/icon-chevron.svg" alt="" width="33" height="33" />
                                        </span>
                                    </button>
                                </h3>
                                <div class="faq__panel" id="faq-panel-4" role="region">
                                    <div>
                                        <p class="faq__answer">
                                            A children's book typically has around 20 to 50 pages on average. However, the ideal length differs from the type of book. Board and picture books go from 8 to 35, while the early readers and chapter books are between 30 and 80 pages.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq__item">
                                <h3>
                                    <button class="faq__trigger" type="button" aria-expanded="false" aria-controls="faq-panel-5">
                                        <span>How much does it cost to publish a children's book on Amazon?</span>
                                        <span class="faq__icon">
                                            <img src="images/svg/icon-chevron.svg" alt="" width="33" height="33" />
                                        </span>
                                    </button>
                                </h3>
                                <div class="faq__panel" id="faq-panel-5" role="region">
                                    <div>
                                        <p class="faq__answer">
                                            Publishing a children's book can cost you thousands of dollars. However, with our children's book publishing agency, the prices start as low as $499 only. The prices may differ depending on the platform you choose and other services you pick.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-section__edge" aria-hidden="true"></div>
                </section>

                <section class="final-cta" aria-labelledby="final-cta-title">
                    <img
                        class="final-cta__icon"
                        src="images/cta-icon.png"
                        alt=""
                        width="109"
                        height="109"
                        aria-hidden="true"
                        loading="lazy"
                    />

                    <div class="final-cta__card" data-animation="zoom">
                        <div class="final-cta__copy">
                            <h2 class="h3" id="final-cta-title">
                                Begin Your Journey to Captivate Young Readers
                            </h2>
                            <p class="lead muted">
                                It's time you get started on this exciting
                                journey to make your book found in every digital
                                library, lighting up the hearts of children
                                forever.
                            </p>
                        </div>

                        <div class="final-cta__action">
                            <img
                                class="final-cta__underline"
                                src="images/cta-underline.png"
                                alt=""
                                width="318"
                                height="28"
                                aria-hidden="true"
                                loading="lazy"
                            />
                            <button
                                class="btn btn--orange btn--block"
                                type="button"
                                data-modal-open="contact-modal"
                            >
                                <span class="btn__label" data-text="Share Your Manuscript">Share Your Manuscript</span>
                                <img src="images/svg/icon-play-small.svg" alt="" width="18" height="20" />
                            </button>
                        </div>
                    </div>
                </section>

            </div>
        </main>

        <?php /* The landing-page footer, from
                 includes/components/lp-chrome-footer.php. Placed OUTSIDE </main>
                 on purpose: this page's own footer sat inside <main> and inside
                 a width-constrained wrapper div, and the green field is
                 full-bleed, so leaving it in that wrapper would box it to the
                 container width.

                 Its legal links replace #privacy and #terms, which were bare
                 fragments pointing at nothing on this page. */ ?>
        <?php require __DIR__ . '/../includes/lp-footer.php'; ?>

        <!-- ============================ MODAL ============================ -->
        <div
            class="modal"
            id="contact-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
            hidden
        >
            <div class="modal__dialog" data-modal-dialog>
                <button class="modal__close" type="button" data-modal-close aria-label="Close dialog">
                    <img src="images/svg/icon-close.svg" alt="" width="24" height="24" />
                </button>

                <div class="modal__grid">
                    <div class="modal__intro">
                        <h2 class="h2 text-navy" id="modal-title">
                            Let's Bring Your Book to Life
                        </h2>
                        <p class="muted">
                            Ready to self-publish or have questions about our
                            services? Get in touch with our editorial team today
                            for a free manuscript evaluation and consultation.
                        </p>

                        <ul class="contact-list">
                            <li>
                                <img src="images/svg/icon-phone.svg" alt="" width="24" height="24" />
                                <a href="tel:+10001234567">+1 (000) 123-4567</a>
                            </li>
                            <li>
                                <img src="images/svg/icon-web.svg" alt="" width="24" height="24" />
                                <a href="https://elitepublishing.co">ElitePublishing.co</a>
                            </li>
                            <li>
                                <img src="images/svg/icon-email.svg" alt="" width="24" height="24" />
                                <a href="mailto:info@elitepublishing.co">info@elitepublishing.co</a>
                            </li>
                            <li>
                                <img src="images/svg/icon-location.svg" alt="" width="24" height="24" />
                                <span>261 Madison Ave., New York, NY 10016</span>
                            </li>
                        </ul>

                        <button class="btn btn--gradient align-self-start" type="button" data-scroll-to="#books">
                            <span class="btn__label" data-text="Publish Children's Book Today">Publish Children's Book Today</span>
                        </button>
                    </div>

                    <div class="modal__card">
                        <h3 class="h3">Start Your Book Today</h3>

                        <form class="form" id="lp-form" data-contact-form novalidate
                              method="post"
                              action="<?= esc(ep_lp_action()) ?>"
                              enctype="multipart/form-data">
                            <?= ep_lp_hidden_fields($lp) ?>

                            <?php if ($lpFlash['status'] === 'error'): ?>
                              <?php /* Only ever rendered for a submission made with
                                       JavaScript off — with JS on, the same message
                                       arrives as JSON and is written into
                                       [data-form-status] without a reload. */ ?>
                              <p class="form__status" data-state="error" role="alert">
                                <?= esc($lpFlash['message']) ?>
                              </p>
                            <?php endif; ?>

                            <div class="form__fields">
                                <p class="field">
                                    <label class="visually-hidden" for="cf-name">Full Name</label>
                                    <input id="cf-name" name="name" type="text" placeholder="Full Name" autocomplete="name" required aria-describedby="cf-name-error" value="<?= esc(ep_lp_old('name')) ?>" />
                                    <span class="field__error" id="cf-name-error" role="alert"></span>
                                </p>

                                <p class="field">
                                    <label class="visually-hidden" for="cf-email">Email Address</label>
                                    <input id="cf-email" name="email" type="email" placeholder="Email Address" autocomplete="email" required aria-describedby="cf-email-error" value="<?= esc(ep_lp_old('email')) ?>" />
                                    <span class="field__error" id="cf-email-error" role="alert"></span>
                                </p>

                                <p class="field">
                                    <label class="visually-hidden" for="cf-phone">Phone Number</label>
                                    <input id="cf-phone" name="phone" type="tel" placeholder="Phone Number" autocomplete="tel" required aria-describedby="cf-phone-error" value="<?= esc(ep_lp_old('phone')) ?>" />
                                    <span class="field__error" id="cf-phone-error" role="alert"></span>
                                </p>

                                <p class="field">
                                    <label class="visually-hidden" for="cf-message">Your message</label>
                                    <textarea id="cf-message" name="message" placeholder="Write your message here..." required aria-describedby="cf-message-error"><?= esc(ep_lp_old('message')) ?></textarea>
                                    <span class="field__error" id="cf-message-error" role="alert"></span>
                                </p>

                                <?php /* Optional throughout: the form submits with or
                                         without a manuscript, and nothing here is marked
                                         required. When a file is attached it rides along
                                         with the enquiry as a mail attachment. */ ?>
                                <p class="field field--file">
                                    <label class="field__file-label" for="cf-manuscript">
                                        Manuscript <span class="field__optional">(optional)</span>
                                    </label>
                                    <input id="cf-manuscript" name="manuscript" type="file"
                                           accept=".doc,.docx,.pdf,.rtf,.odt,.epub"
                                           aria-describedby="cf-manuscript-error cf-manuscript-hint" />
                                    <span class="field__hint" id="cf-manuscript-hint">
                                        DOC, DOCX, PDF, RTF, ODT or EPUB. Up to 25&nbsp;MB.
                                    </span>
                                    <span class="field__error" id="cf-manuscript-error" role="alert"></span>
                                </p>
                            </div>

                            <button class="btn btn--accent align-self-start" type="submit" data-submit>
                                <span class="btn__label" data-text="Send Message">Send Message</span>
                            </button>

                            <p class="form__status" data-form-status role="status" aria-live="polite"></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="<?= esc(ep_lp_asset($lp, 'js/bootstrap.bundle.min.js')) ?>" defer></script>
        <script src="<?= esc(ep_lp_asset($lp, 'js/main.js')) ?>" defer></script>
    </body>
</html>
