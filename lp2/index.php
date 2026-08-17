<?php
/**
 * LP2 — standalone campaign landing page.
 *
 * Was index.html. It is PHP now so the enquiry form can carry a per-session
 * CSRF token; the page's own markup, CSS and JavaScript are otherwise
 * untouched and all still live inside this folder.
 */
$lp = 'lp2';
require __DIR__ . '/../includes/lp-bootstrap.php';
$lpFlash = ep_lp_flash();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Elite Publishing | Christian Book Publishing</title>
    <meta
      name="description"
      content="Christian book publishing services that help authors bring their message to life, from first draft support to professional distribution."
    />
    <meta name="robots" content="index, follow, max-image-preview:large" />
    <link rel="canonical" href="https://elitepublishing.co/lp2/" />
    <meta name="theme-color" content="#60C489" />

    <!-- Open Graph / Twitter -->
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:site_name" content="Elite Publishing" />
    <meta
      property="og:title"
      content="Elite Publishing | Christian Book Publishing"
    />
    <meta
      property="og:description"
      content="Christian book publishing services that help authors bring their message to life, from first draft support to professional distribution."
    />
    <meta property="og:url" content="https://elitepublishing.co/lp2/" />
    <?php /* Was the site logo — a 452x360 PNG. Declaring summary_large_image and
             then handing over a small near-square logo is the combination that
             makes X and LinkedIn either letterbox it into a grey field or fall
             back to no image at all. This page's own hero photograph is
             1600x930, on-topic, and already deployed with the page. */ ?>
    <meta
      property="og:image"
      content="https://elitepublishing.co/lp2/assets/img/Christian-Book-Publishing-That-Brings-Your-Message-to-Life.jpg"
    />
    <meta property="og:image:width" content="1600" />
    <meta property="og:image:height" content="930" />
    <meta
      property="og:image:alt"
      content="Christian book publishing that brings your message to life"
    />
    <?php /* twitter:card was set with no image anywhere for X to use — og:image
             is the documented fallback and it was pointing at the logo. */ ?>
    <meta name="twitter:card" content="summary_large_image" />
    <meta
      name="twitter:image"
      content="https://elitepublishing.co/lp2/assets/img/Christian-Book-Publishing-That-Brings-Your-Message-to-Life.jpg"
    />
    <meta
      name="twitter:image:alt"
      content="Christian book publishing that brings your message to life"
    />

    <!-- Icons -->
    <link rel="icon" href="assets/img/Favicon.png" sizes="any" />
    <link rel="apple-touch-icon" href="assets/img/Favicon.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Source+Sans+3:wght@400;600&display=swap"
    />

    <!-- Styles -->
    <?php /* ep_lp_asset() appends the file's mtime, so an edited stylesheet
             reaches every browser on the next request instead of waiting out a
             cache lifetime. See the note on the function. */ ?>
    <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'assets/css/base.css')) ?>" />
    <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'assets/css/layout.css')) ?>" />
    <link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'assets/css/components.css')) ?>" />

    <script>
      // Lets CSS scope scroll-reveal animations to JS-enabled browsers only.
      document.documentElement.classList.add("js");
    </script>

    <?php /* telephone was "+1 (800) 000-0000" — the placeholder from
             includes/config.php. A number that does not ring is worse published
             than omitted, so it is dropped here and on LP1 until there is a real
             one. logo stays on the site logo: schema.org wants the brand mark
             there, not the share card. */ ?>
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Elite Publishing",
        "url": "https://elitepublishing.co/",
        "logo": "https://elitepublishing.co/assets/img/logo.png",
        "description": "Christian book publishing services that help authors bring their message to life, from first draft support to professional distribution.",
        "email": "info@elitepublishing.co"
      }
    </script>
    <?= ep_lp_chrome_styles() ?>
  </head>

  <body>
    <a class="skip-link" href="#main">Skip to content</a>

    <!-- ====================================================================
         Header
         ==================================================================== -->
    <?php /* The landing-page header, from
             includes/components/lp-chrome-header.php — logo and one
             "Submit Your Book" button, the same chrome all four LPs render. It
             replaces this page's own sticky header, whose nav pointed at in-page
             sections and whose CTA went to a pricing page that does not exist
             here. The button scrolls to #ContactForm, the card in the hero.

             The page's own .site-header CSS stays in assets/css/layout.css:
             nothing references it now, and removing rules is a separate
             decision from swapping markup. */ ?>
    <?php require __DIR__ . '/../includes/lp-header.php'; ?>

    <main id="main">
      <!-- ==================================================================
           Hero + contact form
           ================================================================== -->
      <section class="hero" aria-labelledby="hero-title">
        <div class="container">
          <div class="hero__grid">
            <h1 class="hero__title" id="hero-title">
              Christian Book Publishing That Brings Your Message to Life
            </h1>

            <div class="contact-card" id="ContactForm">
              <h2 class="contact-card__title">Start Your Book Today</h2>

              <form
                class="form"
                id="lp-form"
                novalidate
                method="post"
                action="<?= esc(ep_lp_action()) ?>"
                enctype="multipart/form-data"
                data-contact-form
              >
                <?= ep_lp_hidden_fields($lp) ?>

                <?php if ($lpFlash['status'] === 'error'): ?>
                  <?php /* Rendered only for a post made with JavaScript off.
                           With it on, the same text arrives as JSON and is
                           written into [data-form-status] without a reload. */ ?>
                  <p class="form__status" data-state="error" role="alert">
                    <?= esc($lpFlash['message']) ?>
                  </p>
                <?php endif; ?>

                <!-- This page's own bot trap, kept as it was. The handler also
                     checks the shared `website` honeypot above; two traps cost
                     nothing and catch different bots. -->
                <!-- Bot trap. Never shown, never focusable by a human. -->
                <div class="form__honeypot" aria-hidden="true">
                  <label for="company-website">Leave this field blank</label>
                  <input
                    type="text"
                    id="company-website"
                    name="company_website"
                    tabindex="-1"
                    autocomplete="off"
                    data-honeypot
                  />
                </div>

                <div class="form__grid">
                  <div class="form__field form__field--full">
                    <label class="form__label" for="full-name">Full name</label>
                    <input
                      class="form__control"
                      type="text"
                      id="full-name"
                      name="full_name"
                      placeholder="Full Name"
                      autocomplete="name"
                      required
                    />
                    <span class="form__error" data-error-for="full_name"></span>
                  </div>

                  <div class="form__field">
                    <label class="form__label" for="email">Email address</label>
                    <input
                      class="form__control"
                      type="email"
                      id="email"
                      name="email"
                      placeholder="Email Address"
                      autocomplete="email"
                      required
                    />
                    <span class="form__error" data-error-for="email"></span>
                  </div>

                  <div class="form__field">
                    <label class="form__label" for="phone">Phone number</label>
                    <input
                      class="form__control"
                      type="tel"
                      id="phone"
                      name="phone"
                      placeholder="Phone Number"
                      autocomplete="tel"
                      required
                    />
                    <span class="form__error" data-error-for="phone"></span>
                  </div>

                  <div class="form__field form__field--full">
                    <label class="form__label" for="manuscript"
                      >Upload manuscript</label
                    >
                    <div class="form__file">
                      <input
                        class="form__file-input"
                        type="file"
                        id="manuscript"
                        name="manuscript"
                        accept=".doc,.docx,.pdf,.rtf,.txt,.epub"
                        aria-describedby="manuscript-hint"
                        data-file-input
                      />
                      <label class="form__file-button" for="manuscript"
                        >Upload Manuscript</label
                      >
                      <span class="form__file-name" data-file-name
                        >No file chosen</span
                      >
                    </div>
                    <p class="form__hint" id="manuscript-hint">
                      Accepted formats: DOC, DOCX, PDF, RTF, TXT, EPUB. Maximum
                      25&nbsp;MB.
                    </p>
                    <span class="form__error" data-error-for="manuscript"></span>
                  </div>

                  <div class="form__field form__field--full">
                    <label class="form__label" for="notes"
                      >Additional notes</label
                    >
                    <textarea
                      class="form__control"
                      id="notes"
                      name="notes"
                      placeholder="Additional Notes..."
                      rows="5"
                    ></textarea>
                    <span class="form__error" data-error-for="notes"></span>
                  </div>
                </div>

                <div class="form__footer">
                  <button
                    class="button form__submit"
                    type="submit"
                    data-form-submit
                  >
                    Submit Manuscript
                  </button>
                  <p class="form__status" role="status" data-form-status></p>
                </div>
              </form>
            </div>
          </div>
        </div>

        <svg
          class="hero__divider"
          viewBox="0 0 1280 140"
          preserveAspectRatio="none"
          aria-hidden="true"
          focusable="false"
        >
          <path fill="currentColor" d="M1280 140V0S993.46 140 640 139 0 0 0 0v140z" />
        </svg>
      </section>

      <!-- ==================================================================
           Who We Serve
           ================================================================== -->
      <section
        class="section section--photo section--paper"
        id="WhoWeServe"
        aria-labelledby="who-we-serve-title"
      >
        <div class="container">
          <div class="section__header section__header--start">
            <h2 class="section__title" id="who-we-serve-title">Who We Serve</h2>
            <p class="section__lead">
              If God has placed a message on your heart, Elite Publishing helps
              you steward it well.
            </p>
            <div class="mt-md">
              <a class="button" href="#ContactForm">Connect With Us</a>
            </div>
          </div>

          <ul class="grid grid--4" role="list">
            <li class="icon-card reveal">
              <img
                class="icon-card__icon"
                src="assets/img/First-time-authors.svg"
                width="64"
                height="64"
                alt=""
                loading="lazy"
              />
              <h3 class="icon-card__title">
                First-time authors with original manuscripts
              </h3>
            </li>
            <li class="icon-card reveal">
              <img
                class="icon-card__icon"
                src="assets/img/Experienced-authors.svg"
                width="64"
                height="64"
                alt=""
                loading="lazy"
              />
              <h3 class="icon-card__title">
                Experienced authors relaunching or expanding existing books
              </h3>
            </li>
            <li class="icon-card reveal">
              <img
                class="icon-card__icon"
                src="assets/img/Pastors.svg"
                width="64"
                height="64"
                alt=""
                loading="lazy"
              />
              <h3 class="icon-card__title">
                Pastors, ministry leaders, and Christian organizations who need
                strategic advice
              </h3>
            </li>
            <li class="icon-card reveal">
              <img
                class="icon-card__icon"
                src="assets/img/publishing-support.svg"
                width="64"
                height="64"
                alt=""
                loading="lazy"
              />
              <h3 class="icon-card__title">
                Thought leaders seeking faith-aligned publishing support
              </h3>
            </li>
          </ul>
        </div>
      </section>

      <!-- ==================================================================
           Why Elite
           ================================================================== -->
      <section
        class="section section--cream"
        id="WhyElite"
        aria-labelledby="why-elite-title"
      >
        <div class="container">
          <div class="grid grid--media-text">
            <div class="media-panel media-panel--why" role="img"
              aria-label="A Elite Publishing author reviewing a manuscript"></div>

            <div>
              <h2 class="section__title" id="why-elite-title">
                Why Christian Authors Choose Elite Publishing
              </h2>
              <p class="section__lead">
                Elite Publishing believes authors should retain ownership of
                their work and have clarity throughout their publishing journey.
                You choose the services you need. We provide the expertise,
                guidance, and infrastructure to help you publish confidently and
                effectively.
              </p>

              <ul class="check-list mt-md" role="list">
                <li class="check-list__item">
                  You retain 100% ownership of your content
                </li>
                <li class="check-list__item">
                  Transparent, fee-for-service pricing—no hidden royalties
                </li>
                <li class="check-list__item">
                  End-to-end publishing and distribution support
                </li>
                <li class="check-list__item">
                  Access to Inspire Media Group’s faith-engaged media audiences
                </li>
                <li class="check-list__item">
                  Designed specifically for ministries, leaders, and
                  message-driven authors
                </li>
                <li class="check-list__item">
                  Backed by Inspire Media Group’s media reach and credibility
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================================================================
           Our Author Services
           ================================================================== -->
      <section
        class="section"
        id="AuthorServices"
        aria-labelledby="author-services-title"
      >
        <div class="container">
          <div class="section__header container--narrow">
            <h2 class="section__title" id="author-services-title">
              Our Author Services
            </h2>
            <p class="section__lead"><strong>Author Ownership Comes First</strong></p>
            <p>
              Elite Publishing does not take ownership of your manuscript or
              intellectual property. For the duration of our agreement, we
              request exclusive rights to print and distribute the formats we
              produce to ensure quality control, accurate metadata, consistent
              pricing, and effective distribution.
            </p>
            <p>
              When the agreement concludes, all rights remain with you, and your
              files and assets are available for continued use or transfer at
              your discretion.
            </p>
            <p>
              Services are modular and scoped based on each project’s needs;
              timelines and deliverables are confirmed prior to engagement.
            </p>
          </div>

          <ul class="grid grid--3" role="list">
            <!-- 1 -->
            <li class="service-card reveal">
              <img
                class="service-card__image"
                src="assets/img/Manuscript-Development-Editorial-Support.jpg"
                srcset="
                  assets/img/Manuscript-Development-Editorial-Support-300x162.jpg 300w,
                  assets/img/Manuscript-Development-Editorial-Support.jpg        372w
                "
                sizes="(max-width: 600px) 100vw, 372px"
                width="372"
                height="201"
                alt="Manuscript development and editorial support"
                loading="lazy"
              />
              <div class="service-card__body">
                <h3 class="service-card__title">
                  Manuscript Development &amp; Editorial Support
                </h3>
                <p class="service-card__summary">
                  Professional guidance to refine your manuscript for clarity,
                  structure, and impact—without losing your unique voice.
                </p>
                <details class="disclosure">
                  <summary class="disclosure__trigger">Read More</summary>
                  <div class="disclosure__panel">
                    <div class="disclosure__content">
                      <p>
                        <strong>Elite Publishing</strong> provides professional,
                        faith-aligned guidance to help shape your manuscript for
                        clarity, structure, flow, and reader engagement—without
                        compromising your unique voice or message. Our editorial
                        team works collaboratively with authors to strengthen
                        content, improve organization, and refine language so
                        your manuscript is prepared for professional design,
                        production, and publication. This service is ideal for
                        first-time authors or experienced writers seeking expert
                        feedback before moving into the publishing phase. (Scope
                        of editorial support determined by project; copyediting
                        and proofreading available as add-on services.)
                      </p>
                    </div>
                  </div>
                </details>
              </div>
            </li>

            <!-- 2 -->
            <li class="service-card reveal">
              <img
                class="service-card__image"
                src="assets/img/Professional-Book-Cover-Design.jpg"
                srcset="
                  assets/img/Professional-Book-Cover-Design-300x162.jpg 300w,
                  assets/img/Professional-Book-Cover-Design.jpg        372w
                "
                sizes="(max-width: 600px) 100vw, 372px"
                width="372"
                height="201"
                alt="Professional book and cover design"
                loading="lazy"
              />
              <div class="service-card__body">
                <h3 class="service-card__title">
                  Professional Book &amp; Cover Design
                </h3>
                <p class="service-card__summary">
                  Beautiful, market-ready cover design and expertly formatted
                  interiors that reflect excellence, credibility, and care.
                </p>
                <details class="disclosure">
                  <summary class="disclosure__trigger">Read More</summary>
                  <div class="disclosure__panel">
                    <div class="disclosure__content">
                      <p>
                        <strong>Elite Publishing</strong> provides a complete
                        publishing solution that transforms your completed
                        manuscript into a professionally designed book with a
                        custom cover, back cover copy, formatted interior, and
                        full distribution setup through Amazon and KDP for
                        print-on-demand fulfillment—ideal for authors ready to
                        publish a new title with professional quality and broad
                        marketplace reach. (Author Provides Completed Manuscript
                        – Editing Is Additional – Includes Two Rounds of
                        Revisions)
                      </p>
                    </div>
                  </div>
                </details>
              </div>
            </li>

            <!-- 3 -->
            <li class="service-card reveal">
              <img
                class="service-card__image"
                src="assets/img/Print-on-Demand-Amazon-Publishin.jpg"
                srcset="
                  assets/img/Print-on-Demand-Amazon-Publishin-300x162.jpg 300w,
                  assets/img/Print-on-Demand-Amazon-Publishin.jpg        372w
                "
                sizes="(max-width: 600px) 100vw, 372px"
                width="372"
                height="201"
                alt="Print-on-demand and Amazon publishing"
                loading="lazy"
              />
              <div class="service-card__body">
                <h3 class="service-card__title">
                  Print-on-Demand &amp; Amazon Publishing
                </h3>
                <p class="service-card__summary">
                  No inventory. No hassle. Your book is printed and shipped as
                  orders come in and made available on Amazon and other major
                  platforms.
                </p>
                <details class="disclosure">
                  <summary class="disclosure__trigger">Read More</summary>
                  <div class="disclosure__panel">
                    <div class="disclosure__content">
                      <p>
                        <strong>Elite Publishing</strong> will prepare and upload
                        your final book files for seamless distribution through
                        KDP on Amazon and through Ingram Spark, making your title
                        available for purchase at major retailers like Walmart,
                        Target, Barnes &amp; Noble, and independent
                        bookstores—ideal for authors seeking wide marketplace
                        exposure and distribution without the need for inventory
                        management.
                      </p>
                    </div>
                  </div>
                </details>
              </div>
            </li>

            <!-- 4 -->
            <li class="service-card reveal">
              <img
                class="service-card__image"
                src="assets/img/eBook-Creation-Digital-Distribution.jpg"
                width="372"
                height="201"
                alt="eBook creation and digital distribution"
                loading="lazy"
              />
              <div class="service-card__body">
                <h3 class="service-card__title">
                  eBook Creation &amp; Digital Distribution
                </h3>
                <p class="service-card__summary">
                  Reach digital readers everywhere with professionally formatted
                  Kindle and EPUB editions and global digital access.
                </p>
                <details class="disclosure">
                  <summary class="disclosure__trigger">Read More</summary>
                  <div class="disclosure__panel">
                    <div class="disclosure__content">
                      <p>
                        <strong>Elite Publishing</strong> transforms your
                        print-ready PDF into a professionally formatted e-book
                        for seamless distribution through Amazon Kindle and
                        Ingram Spark’s global e-book platforms—ideal for authors
                        wanting to expand their reach through digital and mobile
                        readership.
                      </p>
                    </div>
                  </div>
                </details>
              </div>
            </li>

            <!-- 5 -->
            <li class="service-card reveal">
              <img
                class="service-card__image"
                src="assets/img/Republishing-Refreshing.jpg"
                width="372"
                height="201"
                alt="Republishing and refreshing an existing book"
                loading="lazy"
              />
              <div class="service-card__body">
                <h3 class="service-card__title">Republishing &amp; Refreshing</h3>
                <p class="service-card__summary">
                  Have a book that needs a second life? We offer cover
                  redesigns, interior updates, and strategic relaunch support.
                </p>
                <details class="disclosure">
                  <summary class="disclosure__trigger">Read More</summary>
                  <div class="disclosure__panel">
                    <div class="disclosure__content">
                      <p>
                        <strong>Book Replication:</strong> Provides a
                        high-quality exact reproduction of your existing book
                        through professional scanning and high-res PDF
                        manuscript preparation for print-on-demand—perfect for
                        authors who want an exact duplicate without updates or
                        revisions. (No Updates – Rights Waiver Required.)
                      </p>
                      <p>
                        <strong>Book Retrofitting:</strong> Provides a
                        high-quality reproduction of your existing book with
                        refreshed cover elements—new ISBNs and barcodes, new
                        logos, and updated copyright pages—while maintaining the
                        original content—ideal for relaunching a book with new,
                        professional-quality updates. (One Revision Included –
                        Rights Waiver Required.)
                      </p>
                      <p>
                        <strong>Book Republishing Basic:</strong> Gives your
                        entire book a fresh look with limited author-directed
                        updates to your manuscript (repagination not required),
                        fully redesigned front and back covers, and a new
                        publishing high-res PDF for print-on-demand—perfect for
                        refining and relaunching an existing work with a
                        modernized look and professional polish. (Includes Two
                        Rounds of Revisions – Author Provides Updated Copy)
                      </p>
                      <p>
                        <strong>Book Republishing Advanced:</strong> Gives your
                        entire book a fresh look with unlimited author-directed
                        updates to your manuscript (repagination required),
                        fully redesigned front and back covers, and a new
                        publishing high-res PDF for print-on-demand—perfect for
                        refining and relaunching an existing work with a
                        modernized look and professional polish. (Includes Two
                        Rounds of Revisions – Author Provides Updated Copy)
                      </p>
                    </div>
                  </div>
                </details>
              </div>
            </li>

            <!-- 6 -->
            <li class="service-card reveal">
              <img
                class="service-card__image"
                src="assets/img/Global-Distribution-Channels.jpg"
                width="372"
                height="201"
                alt="Global distribution channels"
                loading="lazy"
              />
              <div class="service-card__body">
                <h3 class="service-card__title">Global Distribution Channels</h3>
                <p class="service-card__summary">
                  Your book available through Amazon, Barnes &amp; Noble,
                  ChristianBook.com, and more—online and in-store.
                </p>
                <details class="disclosure">
                  <summary class="disclosure__trigger">Read More</summary>
                  <div class="disclosure__panel">
                    <div class="disclosure__content">
                      <p>
                        <strong>Elite Publishing</strong> ensures your book is
                        professionally positioned and widely available through
                        leading online and retail outlets, including Amazon,
                        Barnes &amp; Noble, ChristianBook.com, and other domestic
                        and international distribution partners. Through
                        strategic setup and platform integration, your title is
                        made accessible to both online shoppers and
                        brick-and-mortar retailers—expanding visibility,
                        discoverability, and long-term sales potential. This
                        service is ideal for authors seeking broad marketplace
                        reach without the complexity of managing distribution
                        logistics.
                      </p>
                    </div>
                  </div>
                </details>
              </div>
            </li>

            <!-- 7 -->
            <li class="service-card reveal">
              <img
                class="service-card__image"
                src="assets/img/Author-Marketing-Support.jpg"
                width="372"
                height="201"
                alt="Author marketing support"
                loading="lazy"
              />
              <div class="service-card__body">
                <h3 class="service-card__title">Author Marketing Support</h3>
                <p class="service-card__summary">
                  Leverage Inspire Media Group’s Christian email network and
                  digital platforms to promote your book to a ready audience.
                </p>
                <details class="disclosure">
                  <summary class="disclosure__trigger">Read More</summary>
                  <div class="disclosure__panel">
                    <div class="disclosure__content">
                      <p>
                        <strong>Elite Publishing</strong> helps amplify your
                        book’s visibility through faith-aligned promotional
                        channels and audience engagement strategies. Authors gain
                        access to Inspire Media Group’s established Christian
                        email networks and digital platforms, providing targeted
                        exposure to readers who are already engaged with
                        Christian content. This service is ideal for authors
                        seeking strategic promotional support to build awareness,
                        drive traffic, and generate momentum around a book launch
                        or relaunch. (Marketing services are customized based on
                        audience, platform availability, and campaign goals.)
                      </p>
                    </div>
                  </div>
                </details>
              </div>
            </li>
          </ul>

          <div class="section__actions">
            <a class="button" href="#ContactForm">View Pricing Guide</a>
          </div>
        </div>
      </section>

      <!-- ==================================================================
           Stay In Control
           ================================================================== -->
      <section
        class="section section--cream-soft section--flower"
        aria-labelledby="stay-in-control-title"
      >
        <div class="container">
          <div class="grid grid--text-media grid--center">
            <h2 class="section__title" id="stay-in-control-title">
              Stay In Control.<br />We Walk Beside You.
            </h2>
            <p class="section__lead">
              Elite Publishing believes authors should retain ownership of their
              work and have clarity throughout their publishing journey.
            </p>
          </div>

          <ul class="grid grid--3 mt-xl" role="list">
            <li class="icon-card icon-card--framed reveal">
              <img
                class="icon-card__icon"
                src="assets/img/Flexible-and-Transparent.svg"
                width="64"
                height="64"
                alt=""
                loading="lazy"
              />
              <h3 class="icon-card__title">Flexible and Transparent</h3>
            </li>
            <li class="icon-card icon-card--framed reveal">
              <img
                class="icon-card__icon"
                src="assets/img/Tailored-to-your-goals.svg"
                width="64"
                height="64"
                alt=""
                loading="lazy"
              />
              <h3 class="icon-card__title">
                Tailored to your goals, timeline, and budget
              </h3>
            </li>
            <li class="icon-card icon-card--framed reveal">
              <img
                class="icon-card__icon"
                src="assets/img/Christian-authors.svg"
                width="64"
                height="64"
                alt=""
                loading="lazy"
              />
              <h3 class="icon-card__title">
                Designed to empower independent Christian authors
              </h3>
            </li>
          </ul>

          <div class="section__actions">
            <a class="button" href="#ContactForm">Begin the Journey</a>
          </div>
        </div>
      </section>

      <!-- ==================================================================
           CTA — Ready To Talk About Your Book?
           ================================================================== -->
      <section
        class="section section--photo section--cta-book"
        aria-labelledby="ready-to-talk-title"
      >
        <div class="container">
          <div class="section__header section__header--flush">
            <h2
              class="section__title section__title--light"
              id="ready-to-talk-title"
            >
              Ready To Talk About Your Book?
            </h2>
            <div class="section__actions">
              <a class="button button--light" href="#ContactForm"
                >Schedule a Discussion</a
              >
            </div>
          </div>
        </div>
      </section>

      <!-- ==================================================================
           Publishing and Promotion
           ================================================================== -->
      <section
        class="section"
        id="PublishingPromotion"
        aria-labelledby="publishing-promotion-title"
      >
        <div class="container">
          <div class="grid grid--text-media">
            <div>
              <h2
                class="section__title section__title--alt"
                id="publishing-promotion-title"
              >
                Professional Publishing And Promotion Services For Christian
                Authors &amp; Ministries
              </h2>
              <p class="section__lead">
                Elite Publishing helps Christian authors
                and ministries bring their messages to life—professionally,
                purposefully, and with excellence.
              </p>

              <ul class="check-list my-md" role="list">
                <li class="check-list__item">
                  Do you have an original manuscript you are ready to publish for
                  the first time?
                </li>
                <li class="check-list__item">
                  Are you looking to create a professional eBook, publish a
                  paperback on Amazon or expand your book into print-on-demand or
                  ebook formats?
                </li>
                <li class="check-list__item">
                  Do you have an existing book that needs to be refreshed,
                  relaunched, or repositioned to reach a wider audience?
                </li>
              </ul>

              <p class="section__lead">
                <strong>Full-Service Publishing—Made Simple</strong>
              </p>
              <p>
                Publishing a book does not have to be overwhelming. From
                manuscript to marketplace, our experienced publishing team
                provides <strong>end-to-end author services</strong> designed to
                help you publish with confidence, clarity, and credibility—while
                staying true to your message and mission. Whether you are
                publishing your <strong>first book</strong>, launching an
                <strong>eBook</strong>, or giving new life to an existing title,
                we guide you through every step of the journey.
              </p>
            </div>

            <div
              class="media-panel media-panel--promotion"
              role="img"
              aria-label="Christian authors and ministry leaders collaborating on a book"
            ></div>
          </div>
        </div>
      </section>

      <!-- ==================================================================
           CTA — Let's Talk About Your Book!
           ================================================================== -->
      <section
        class="section section--photo section--cta-talk"
        aria-labelledby="lets-talk-title"
      >
        <div class="container">
          <div class="section__header section__header--flush">
            <h2 class="section__title" id="lets-talk-title">
              Let’s Talk About Your Book!
            </h2>
            <div class="section__actions">
              <a class="button button--light" href="#ContactForm"
                >Schedule a Conversation</a
              >
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- ====================================================================
         Footer
         ==================================================================== -->
    <?php /* The landing-page footer, from
             includes/components/lp-chrome-footer.php. It replaces this page's
             own five-column footer, whose links pointed at /pricing-request/ and
             /privacy-policy/ — one of which does not exist on this site. The
             replacement builds its three links from ep_page_url(), so they
             resolve to real pages.

             The "Manage Cookies Preferences" control went with it: it was a
             .cky-banner-element hook for a Cookieyes script this site does not
             load, so the link did nothing. */ ?>
    <?php require __DIR__ . '/../includes/lp-footer.php'; ?>

    <script src="<?= esc(ep_lp_asset($lp, 'assets/js/main.js')) ?>" defer></script>
  </body>
</html>
