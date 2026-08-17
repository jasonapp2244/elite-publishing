<?php
/**
 * LP3 — standalone campaign landing page.
 *
 * Was index.html. It is PHP now so the enquiry form can carry a per-session
 * CSRF token; the page's own markup, CSS and JavaScript are otherwise
 * untouched and all still live inside this folder.
 */
$lp = 'lp3';
require __DIR__ . '/../includes/lp-bootstrap.php';
$lpFlash = ep_lp_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Elite Publishing — Your Book Marketing Command Center</title>
<meta name="description" content="One portal to create, publish, and track every marketing effort for your book. Social posts, email campaigns, press releases, ads, distribution, and more.">

<!-- Google tag (gtag.js).
     WARNING: G-RF8BKX7YYZ is the measurement ID this page arrived with, and it
     belongs to the GA4 property of the company the page was built for — not to
     Elite Publishing. Every visit to this landing page is therefore reported
     into someone else's analytics, and Elite Publishing sees none of it.
     It is left connected rather than deleted so tracking is not silently
     broken, but it should be swapped for Elite Publishing's own measurement ID
     (Google Analytics -> Admin -> Data Streams) or removed. Flagged in the
     handover notes. -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RF8BKX7YYZ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-RF8BKX7YYZ');
</script>

<!-- Canonical + crawl -->
<?php /* This page arrived with a canonical pointing at a different company's
         domain. A canonical aimed off-site tells search engines the page is a
         copy of one over there: the usual result is that it never ranks and
         its ad landings look like cross-domain duplicates. It is served from
         elitepublishing.co, so that is what it now claims. */ ?>
<link rel="canonical" href="https://elitepublishing.co/lp3/">
<?php /* This page carried no robots directive at all, so it ran on the implicit
         default while LP2 and LP4 both stated one. Stated here too, so the crawl
         policy for all four landing pages is visible in the markup rather than
         inferred. */ ?>
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="theme-color" content="#60C489">

<!-- Open Graph (social share previews) -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Elite Publishing">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="Elite Publishing — Marketing tools built for authors">
<meta property="og:description" content="A complete promotion toolkit for authors: book descriptions, press releases, social posts, trailers, email campaigns, and event tools — without becoming a full-time marketer.">
<meta property="og:url" content="https://elitepublishing.co/lp3/">
<?php /* Was the 452x360 site logo under a summary_large_image declaration — the
         same mismatch LP2 had. og-default.jpg is the site's purpose-built share
         card at 1200x628, which is the ratio both X and Facebook crop to. */ ?>
<meta property="og:image" content="https://elitepublishing.co/assets/img/og-default.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="628">
<meta property="og:image:alt" content="Elite Publishing — marketing tools built for authors">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Elite Publishing — Marketing tools built for authors">
<meta name="twitter:description" content="A complete promotion toolkit for authors: book descriptions, press releases, social posts, trailers, email campaigns, and event tools — without becoming a full-time marketer.">
<meta name="twitter:image" content="https://elitepublishing.co/assets/img/og-default.jpg">
<meta name="twitter:image:alt" content="Elite Publishing — marketing tools built for authors">

<!-- Structured data: Organization + SoftwareApplication (helps Google rich results and AI assistants describe/recommend the product) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "https://elitepublishing.co/#org",
      <?php /* legalName was the previous owner's registered company name. It is
               dropped rather than renamed: a legal entity name is a matter of
               record, and inventing "Elite Publishing LLC" would publish a
               company type as structured data with nothing behind it. */ ?>
      "name": "Elite Publishing",
      "url": "https://elitepublishing.co/",
      "logo": "https://elitepublishing.co/assets/img/logo.png",
      "description": "AI-assisted promotional materials, campaign tools, and publishing helpers for indie authors."
    },
    {
      "@type": "SoftwareApplication",
      "@id": "https://elitepublishing.co/#app",
      "name": "Elite Publishing",
      "applicationCategory": "BusinessApplication",
      "applicationSubCategory": "Book Marketing Software",
      "operatingSystem": "Web browser",
      "url": "https://elitepublishing.co/",
      "publisher": { "@id": "https://elitepublishing.co/#org" },
      "description": "A complete promotion toolkit for indie authors: AI-drafted book descriptions, press releases, sell sheets, social posts, book trailers, email campaigns, ad copy, keyword and BISAC suggestions, and event tools — each reading your book's metadata to stay on-brand.",
      "offers": [
        {
          "@type": "Offer",
          "name": "Starter",
          "price": "14.95",
          "priceCurrency": "USD",
          "description": "Starter plan, billed monthly. Annual billing available at a discount."
        },
        {
          "@type": "Offer",
          "name": "Pro",
          "price": "24.00",
          "priceCurrency": "USD",
          "description": "Pro plan, billed monthly. Annual billing available at a discount."
        },
        {
          "@type": "Offer",
          "name": "Studio",
          "price": "99.00",
          "priceCurrency": "USD",
          "description": "Studio plan, billed monthly. Annual billing available at a discount."
        }
      ]
    }
  ]
}
</script>

<?php /* Removed: three http-equiv cache directives left over from developing the
         mock app against a live reload.

         They were doing nothing useful and one thing harmful. Browsers ignore
         http-equiv Cache-Control and Expires entirely — only a real response
         header counts, and .htaccess already sets "no-cache, must-revalidate"
         for .php. Pragma is an HTTP/1.0 REQUEST header that has no meaning in a
         response at all. Where a proxy does honour them, they say no-store for
         the document, which is the opposite of what a landing page carrying paid
         traffic wants. The .htaccess policy is the single place this is decided. */ ?>
<?= ep_favicon_tags() ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&amp;family=Lora:ital,wght@0,400;0,500;0,600;1,400;1,600&amp;display=swap">
<?php /* ep_lp_asset() appends the file's mtime, so an edited stylesheet reaches
         every browser on the next request instead of waiting out a cache
         lifetime. See the note on the function. */ ?>
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'assets/css/base.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'assets/css/landing.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'assets/css/app.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'assets/css/components.css')) ?>">
<link rel="stylesheet" href="<?= esc(ep_lp_asset($lp, 'assets/css/overrides.css')) ?>">
<!-- marked.js for live markdown preview in the WordPress composer (Session 7B) -->
<script src="<?= esc(ep_lp_asset($lp, 'assets/js/vendor/marked.min.js')) ?>" defer></script>
<?= ep_lp_chrome_styles() ?>
</head>
<body>

<!-- ════════════════════════════════════════
     LANDING PAGE
════════════════════════════════════════ -->
<div id="landing">

  <!-- Nav -->
  <?php /* The landing-page header, from
           includes/components/lp-chrome-header.php — logo and one
           "Submit Your Book" button, the same chrome all four LPs render. It
           replaces this page's own landing nav (logo, hamburger, in-page links
           and a demo login link). The button scrolls to #enquiry-panel, the
           manuscript form beside the hero.

           Inside #landing, matching the footer below: the mock app in
           #app-screen keeps its own <header id="topbar">, which is product
           chrome rather than site chrome. */ ?>
  <?php require __DIR__ . '/../includes/lp-header.php'; ?>

  <!-- Hero -->
  <div class="hero">
    <!-- Decorative floating elements -->
    <div class="hero-decor book gold" aria-hidden="true"></div>
    <div class="hero-decor book green" aria-hidden="true"></div>
    <div class="hero-decor book coral" aria-hidden="true"></div>
    <svg class="hero-decor sparkle sparkle-1" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0l1.8 5.2L15 7l-5.2 1.8L8 14l-1.8-5.2L1 7l5.2-1.8z"></path></svg>
    <svg class="hero-decor sparkle sparkle-2" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0l1.8 5.2L15 7l-5.2 1.8L8 14l-1.8-5.2L1 7l5.2-1.8z"></path></svg>
    <svg class="hero-decor sparkle sparkle-3" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0l1.8 5.2L15 7l-5.2 1.8L8 14l-1.8-5.2L1 7l5.2-1.8z"></path></svg>
    <svg class="hero-decor sparkle sparkle-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0l1.8 5.2L15 7l-5.2 1.8L8 14l-1.8-5.2L1 7l5.2-1.8z"></path></svg>
    <div class="hero-decor dot dot-1" aria-hidden="true"></div>
    <div class="hero-decor dot dot-2" aria-hidden="true"></div>
    <div class="hero-decor dot dot-3" aria-hidden="true"></div>

    <div class="hero-left">
      <span class="hero-eyebrow">Built for authors</span>
      <h1>Sell more books.<br>Spend less time on <em>marketing.</em></h1>
      <p>Elite Publishing is a complete promotion toolkit — book descriptions, press releases, social posts, trailers, email campaigns, and event tools — without becoming a full-time marketer.</p>
      <div class="hero-ctas">
        <button class="btn btn-green btn-lg" onclick="openSiteTour()">✨ See what your author website can look like</button>
        <button class="btn btn-outline btn-lg" onclick="document.getElementById('tour').scrollIntoView({behavior:'smooth'})">Take the tour</button>
        <button class="btn btn-outline btn-lg" onclick="document.getElementById('pricing').scrollIntoView({behavior:'smooth'})">See pricing</button>
      </div>
      <p class="hero-note">For traditionally published, hybrid, and independent authors</p>
    </div>

    <!-- Manuscript Panel — replaces the former sign-in / sign-up panel.
         Visitors send us their manuscript and we get back to them; there
         are no accounts and no checkout on this site. Handled by
         submitEnquiry() in assets/js/enquiry-form.js. -->
    <div class="auth-panel" id="enquiry-panel">

      <div class="auth-panel-title">Start Your Book Today</div>
      <div class="auth-panel-sub">Send us your manuscript and we'll be in touch.</div>
      <div class="enquiry-note">Upload your draft and tell us what you'd like help with — we'll reply with the right plan and next steps for your launch.</div>

      <form id="enquiry-form" novalidate
            method="post"
            action="<?= esc(ep_lp_action()) ?>"
            enctype="multipart/form-data">
        <?= ep_lp_hidden_fields($lp) ?>

        <div class="auth-error" id="enquiry-error"
             <?= $lpFlash['status'] === 'error' ? 'style="display:block"' : '' ?>><?php
          /* Populated by enquiry-form.js in the normal case. It is filled
             server-side here only for a post made with JavaScript disabled. */
          echo $lpFlash['status'] === 'error' ? esc($lpFlash['message']) : '';
        ?></div>

        <!-- Set by enquireAbout() from the pricing cards, so the tier still
             travels with the submission without a visible dropdown. -->
        <input type="hidden" id="enq-plan" name="plan" value="">

        <div class="field">
          <label for="enq-name">Full name</label>
          <input type="text" id="enq-name" name="name" placeholder="Full Name" autocomplete="name" required value="<?= esc(ep_lp_old('name')) ?>">
        </div>

        <div class="field">
          <label for="enq-email">Email address</label>
          <input type="email" id="enq-email" name="email" placeholder="Email Address" autocomplete="email" required value="<?= esc(ep_lp_old('email')) ?>">
        </div>

        <div class="field">
          <label for="enq-phone">Phone number</label>
          <input type="tel" id="enq-phone" name="phone" placeholder="Phone Number" autocomplete="tel" required value="<?= esc(ep_lp_old('phone')) ?>">
        </div>

        <div class="field">
          <label for="enq-file">Upload manuscript <span class="optional">(optional)</span></label>
          <!-- The native input is visually hidden; the button and filename
               below are the visible control, wired up in enquiry-form.js.

               `required` was removed deliberately: the form has to submit with
               or without a manuscript, and with the attribute in place a
               visitor who only wanted to ask a question could not send the
               form at all. enquiry-form.js no longer demands one either. -->
          <input type="file" id="enq-file" name="manuscript" class="file-input"
                 accept=".doc,.docx,.pdf,.rtf,.txt,.odt,.epub">
          <div class="file-control" id="enq-file-control">
            <button type="button" class="file-btn" id="enq-file-btn">Upload Manuscript</button>
            <span class="file-name" id="enq-file-name">No file chosen</span>
          </div>
          <div class="file-hint">Accepted formats: DOC, DOCX, PDF, RTF, TXT, EPUB. Maximum 25&nbsp;MB.</div>
        </div>

        <div class="field">
          <label for="enq-message">Additional notes <span class="optional">(optional)</span></label>
          <textarea id="enq-message" name="message" placeholder="Additional Notes..."><?= esc(ep_lp_old('message')) ?></textarea>
        </div>

        <button type="submit" class="auth-btn" id="btn-enquiry">Submit Manuscript</button>
      </form>

      <!-- Swapped in by submitEnquiry() once the form validates. -->
      <div class="enquiry-success" id="enquiry-success" style="display:none">
        <h3>Thanks<span id="enquiry-success-name"></span> — manuscript received</h3>
        <p>We've got your details and will reply within one business day. In the meantime, the free demo below tours every feature.</p>
      </div>

      <div class="auth-demo-cta">
        Want to look around first?
        <a href="api/demo_login.php">✨ Try the demo →</a>
      </div>
    </div>
  </div>

  <!-- Feature strip -->
  <div class="feature-strip">
    <div class="feature-strip-item"><span class="strip-dot"></span>Multi-platform social posting</div>
    <div class="feature-strip-item"><span class="strip-dot"></span>AI-drafted copy &amp; book trailers</div>
    <div class="feature-strip-item"><span class="strip-dot"></span>Email campaigns &amp; reminders</div>
    <div class="feature-strip-item"><span class="strip-dot"></span>Press releases &amp; sell sheets</div>
    <div class="feature-strip-item"><span class="strip-dot"></span>Print partner integration</div>
    <div class="feature-strip-item"><span class="strip-dot"></span>Turn your book into a sellable eBook</div>
  </div>

  <!-- NEW: eBook Maker announcement band -->
  <div style="margin:40px auto 8px;max-width:1100px;padding:0 18px">
    <div style="position:relative;overflow:hidden;background:linear-gradient(135deg,#336699 0%,#295680 55%,#1F4463 100%);border-radius:16px;box-shadow:0 14px 36px rgba(41,86,128,0.30);padding:clamp(28px,4vw,44px);color:#fff">
      <div aria-hidden="true" style="position:absolute;top:-40px;right:-30px;width:220px;height:220px;background:radial-gradient(circle,rgba(255,255,255,0.12) 0%,transparent 70%);pointer-events:none"></div>
      <div aria-hidden="true" style="position:absolute;bottom:-60px;left:-40px;width:200px;height:200px;background:radial-gradient(circle,rgba(245,215,117,0.14) 0%,transparent 70%);pointer-events:none"></div>

      <div style="position:relative">
        <div style="display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#F5D775,#D8A72E);color:#3a2c00;font-weight:800;font-size:13px;letter-spacing:1.4px;text-transform:uppercase;padding:6px 15px;border-radius:999px;margin-bottom:20px;box-shadow:0 3px 12px rgba(0,0,0,0.20)">✨ New</div>

        <h2 style="font-family:var(--font-serif);font-size:clamp(26px,4.5vw,36px);line-height:1.18;font-weight:700;margin:0 0 16px;max-width:820px">Now turn your book into an eBook — <em style="color:#F5D775;font-style:italic">right here, in minutes.</em></h2>

        <p style="font-size:clamp(16px,2vw,18.5px);line-height:1.6;margin:0 0 28px;max-width:770px;color:rgba(255,255,255,0.93)">Every online store wants your book as an EPUB file — and making one used to mean buying special software or paying a formatter. Our new <strong>eBook Maker</strong> builds a clean, store-ready eBook from the file you already have. No extra software, no headaches. Then sell it anywhere you like.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-bottom:30px">
          <div style="background:rgba(255,255,255,0.10);border:1px solid rgba(255,255,255,0.20);border-radius:12px;padding:22px 24px">
            <div style="font-size:28px;margin-bottom:10px">📖</div>
            <div style="font-family:var(--font-serif);font-size:20px;font-weight:600;margin-bottom:7px">Novels &amp; text books</div>
            <div style="font-size:15.5px;line-height:1.55;color:rgba(255,255,255,0.86)">Upload a Word file and we structure your chapters, build a clickable table of contents, tidy stray formatting, and add your cover — automatically.</div>
          </div>
          <div style="background:rgba(255,255,255,0.10);border:1px solid rgba(255,255,255,0.20);border-radius:12px;padding:22px 24px">
            <div style="font-size:28px;margin-bottom:10px">🎨</div>
            <div style="font-family:var(--font-serif);font-size:20px;font-weight:600;margin-bottom:7px">Picture &amp; children's books</div>
            <div style="font-size:15.5px;line-height:1.55;color:rgba(255,255,255,0.86)">Upload your print-ready PDF and every page stays exactly as you designed it — artwork and words locked together in a true fixed-layout eBook. <strong>Something most tools can't do.</strong></div>
          </div>
        </div>

        <div style="font-size:12.5px;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,0.72);margin-bottom:11px">Ready to sell on</div>
        <div style="display:flex;flex-wrap:wrap;gap:9px;margin-bottom:30px">
          <span style="background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;padding:6px 15px;font-size:14px;font-weight:600">Amazon KDP</span>
          <span style="background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;padding:6px 15px;font-size:14px;font-weight:600">Apple Books</span>
          <span style="background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;padding:6px 15px;font-size:14px;font-weight:600">Kobo</span>
          <span style="background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;padding:6px 15px;font-size:14px;font-weight:600">Google Play</span>
          <span style="background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;padding:6px 15px;font-size:14px;font-weight:600">Barnes &amp; Noble</span>
          <span style="background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;padding:6px 15px;font-size:14px;font-weight:600">Your own website</span>
        </div>

        <button class="btn btn-white btn-lg" onclick="scrollToEnquiry()">Start creating your eBook</button>
      </div>
    </div>
  </div>

  <!-- Creator bio — credibility marker on the landing page -->
  <div style="background:var(--accent);color:#fff;padding:28px 24px;margin:0 auto;max-width:1100px;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.10);margin-top:32px;margin-bottom:8px">
    <div style="display:flex;align-items:center;gap:22px;flex-wrap:wrap;justify-content:center;text-align:left">
      <div style="flex-shrink:0;width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;font-size:30px;border:2px solid rgba(255,255,255,0.35)">📚</div>
      <div style="flex:1;min-width:260px;max-width:760px">
        <div style="font-size:12px;letter-spacing:1.3px;text-transform:uppercase;opacity:0.85;margin-bottom:6px">Built by someone who's been in the trenches</div>
        <div style="font-family:var(--font-serif);font-size:20px;line-height:1.45;font-weight:600;margin-bottom:8px">Three decades helping authors print, publish, and market their books.</div>
        <div style="font-size:14.5px;line-height:1.6;opacity:0.95">Elite Publishing is built by <strong>Bob Sims</strong>, founder of <strong>Zip Print &amp; Copy</strong> and <strong>Biblio Publishing</strong> in central Ohio — businesses that have served indie authors and small publishers for thirty years. This portal is the toolkit those authors kept asking for: every feature shaped by what authors actually struggle with, not what looks good in a pitch deck.</div>
      </div>
    </div>
  </div>

  <!-- The Pain -->
  <div class="problem-section">
    <div class="problem-inner">
      <div class="section-eyebrow">The reality</div>
      <div class="section-title">Writing the book was the <em>hard</em> part. Right?</div>
      <div class="problem-body">
        <p>For most authors, the truth lands sometime around publication day: <strong>the writing was the easy part.</strong> Now you're supposed to launch a press campaign, build a newsletter, post across half a dozen social platforms, pitch reviewers and bookstores, run events — all while writing the next one.</p>
        <p class="pull">Most of us never trained for any of it. So we either learn on the fly, hire a publicist we can't afford, or — most often — just don't do it. And the book quietly disappears.</p>
        <p>Elite Publishing is built for the in-between. It gives you the tools a marketing team would use, designed for the way authors actually work. <strong>Whether you're traditionally published, hybrid, or fully independent</strong> — if you're the one doing the marketing, this is your toolkit.</p>
      </div>
    </div>
  </div>

  <!-- 3 steps -->
  <div class="section" id="how-it-works">
    <div class="section-eyebrow">How it works</div>
    <div class="section-title">From a finished manuscript to a real campaign — in <em>three steps.</em></div>
    <div class="section-sub">No new vocabulary, no marketing degree, no unanswered questions about which platform matters this month.</div>

    <div class="steps">
      <div class="step">
        <div class="step-num">01</div>
        <h3>Connect</h3>
        <p>Link your social platforms and your email service in a few clicks. Tokens are encrypted and only you can post from your account.</p>
        <div class="step-features">
          <div class="step-feature">TikTok, Facebook, Instagram</div>
          <div class="step-feature">Bluesky, LinkedIn</div>
          <div class="step-feature">Mailgun for email campaigns</div>
        </div>
      </div>
      <div class="step">
        <div class="step-num">02</div>
        <h3>Generate</h3>
        <p>Drafts that sound like you, tuned to your book and your audience. Polish what you want; everything else is already done.</p>
        <div class="step-features">
          <div class="step-feature">AI-drafted, genre-aware</div>
          <div class="step-feature">Press releases, sell sheets, trailers</div>
          <div class="step-feature">Always editable before sending</div>
        </div>
      </div>
      <div class="step">
        <div class="step-num">03</div>
        <h3>Reach readers</h3>
        <p>Send to the right platform at the right time. Track what works. Repeat for the next book — without starting from scratch.</p>
        <div class="step-features">
          <div class="step-feature">Schedule and crosspost</div>
          <div class="step-feature">Live previews per platform</div>
          <div class="step-feature">History of every send</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Features -->
  <div class="features-section" id="features">
    <div class="features-inner">
      <div class="section-eyebrow">Everything in one place</div>
      <div class="section-title">For launch day — <em>and every month after</em></div>
      <div class="section-sub">Promotion is a campaign, not an event. This is the toolkit you'll keep using long after launch week.</div>

      <!-- Two-path author-control callout -->
      <div class="dual-path">
        <div class="dual-path-intro">
          <strong>Two ways to use it. You're always the author.</strong>
        </div>
        <div class="dual-path-cards">
          <div class="dual-card author-side">
            <div class="dual-card-ico">✍️</div>
            <h4>Your words, your art</h4>
            <p>Already wrote your blurb, taglines, or social copy? Made your own cover graphics or video? Use the toolkit to publish, schedule, and track across every platform — the AI never touches your work.</p>
          </div>
          <div class="dual-card ai-side">
            <div class="dual-card-ico">✨</div>
            <h4>AI drafts, you edit</h4>
            <p>Stuck on a blurb or press release? Generate a first draft tuned to your book, then make it your own. AI suggests; you decide what publishes. Mix and match per piece.</p>
          </div>
        </div>
        <div class="dual-path-coda">Nothing posts without your approval. Ever.</div>
      </div>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M3 5h14M3 10h14M3 15h9"></path></svg>
          </div>
          <h3>Book descriptions</h3>
          <p>Polished, hook-first descriptions tuned to your genre. The kind retailers list and readers actually click.</p>
          <span class="feature-tag">AI-assisted</span>
        </div>
        <div class="feature-card">
          <div class="feature-icon gold">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><rect x="3" y="3" width="14" height="14"></rect><path d="M6 7h8 M6 10h8 M6 13h5"></path></svg>
          </div>
          <h3>Press releases</h3>
          <p>AP-style press releases drafted to be sendable — proper dateline, lede, body, quote, boilerplate. Ready for the local paper or a niche blog.</p>
          <span class="feature-tag gold">AI-assisted</span>
        </div>
        <div class="feature-card">
          <div class="feature-icon coral">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 2h9l3 3v13H4z M13 2v3h3"></path></svg>
          </div>
          <h3>Sell sheets</h3>
          <p>One-page PDFs for retailers, librarians, and reviewers. Cover, hook, comp titles, ISBN, contact. Looks like you have a publicist.</p>
          <span class="feature-tag coral">AI-assisted</span>
        </div>
        <div class="feature-card">
          <div class="feature-icon blue">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M2 6l8 5 8-5 M2 6v10h16V6z"></path></svg>
          </div>
          <h3>Cover letters &amp; pitches</h3>
          <p>Drafted outreach for ARC programs, bookstore appearances, podcasts, and reviewers — tailored each time, never generic.</p>
          <span class="feature-tag">AI-assisted</span>
        </div>
        <div class="feature-card">
          <div class="feature-icon teal">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="15" cy="5" r="2.5"></circle><circle cx="5" cy="10" r="2.5"></circle><circle cx="15" cy="15" r="2.5"></circle><path d="M7.5 9l5-2.5M7.5 11l5 2.5"></path></svg>
          </div>
          <h3>Multi-platform social posting</h3>
          <p>Compose once, publish to TikTok, Facebook, Instagram, Bluesky, and LinkedIn. Each post tuned to that platform.</p>
          <span class="feature-tag">AI-assisted</span>
        </div>
        <div class="feature-card">
          <div class="feature-icon coral">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="10" cy="10" r="8"></circle><polygon points="8,6 14,10 8,14" fill="currentColor" stroke="none"></polygon></svg>
          </div>
          <h3>Book trailers</h3>
          <p>Render a polished video trailer from your cover, blurb, and a few choices. AI backdrops, narration, music. Perfect for BookTok and Reels.</p>
          <span class="feature-tag coral">AI-assisted</span>
        </div>
        <div class="feature-card">
          <div class="feature-icon blue">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><rect x="2" y="4" width="16" height="12" rx="1.5"></rect><path d="M2 7l8 5 8-5"></path></svg>
          </div>
          <h3>Email campaigns</h3>
          <p>Send newsletters to your subscriber list with a clean editor and your own Mailgun account. No Mailchimp tax, no friction.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon gold">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><rect x="3" y="4" width="14" height="13" rx="1"></rect><path d="M3 8h14 M7 2v3 M13 2v3"></path></svg>
          </div>
          <h3>Author events &amp; reminders</h3>
          <p>Track signings, readings, podcasts, library talks. Get email reminders before each one. One-click promote to social or press.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon teal">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M3 17V4a1 1 0 0 1 1-1h9l4 4v10a1 1 0 0 1-1 1z M13 3v4h4"></path></svg>
          </div>
          <h3>Education library</h3>
          <p>Plain-English lessons on book trailers, BookTok, ARC strategy, press outreach, hashtag strategy by genre. Practical, not aspirational.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon coral">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><rect x="3" y="4" width="14" height="11" rx="1"></rect><path d="M3 9h14 M7 15v3h6v-3"></path></svg>
          </div>
          <h3>Print on demand</h3>
          <p>Get instant quotes for short-run paperbacks through our local print partner. Order proofs and event stock without minimums.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon gold">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="7" cy="16" r="2"></circle><circle cx="15" cy="16" r="2"></circle><path d="M2 2h2.5l2.5 9h8l2.5-6H6"></path></svg>
          </div>
          <h3>Sales channels</h3>
          <p>Connect Amazon KDP, Shopify, Google Merchant, eBay, and Ingram Spark — track listings and routes from one dashboard.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon blue">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M3 17l5-12 5 12 M5 12h6 M14 4l3 13 M14 4l-3 1"></path></svg>
          </div>
          <h3>Genre-specific guidance</h3>
          <p>Cozy mystery hashtags, romance landing pages, thriller covers, literary press lists — advice that knows what you write.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Tour -->
  <div class="section" id="tour">
    <div class="section-eyebrow">Take the tour</div>
    <div class="section-title">Here's what it actually <em>looks like.</em></div>
    <div class="section-sub">A quick walk through the rooms you'll spend the most time in.</div>

    <div class="tour-rows">

      <div class="tour-row">
        <div class="tour-text">
          <h3>Your books, your AI context</h3>
          <p>Every AI feature reads your book's metadata — title, genre, blurb, themes — to keep drafts on-brand and on-tone. The more you fill in, the better every output gets. The description tool already knows about the book it's writing for.</p>
          <ul class="tour-list">
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>One book record powers every tool</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Add a new field, every output improves</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Manage multiple books from one dashboard</li>
          </ul>
        </div>
        <div class="tour-visual">
          <div class="header-bar" data-cover-target="tour-cover-img">
            <span class="active" role="button" tabindex="0" aria-label="Show cover 1" onclick="swapTourCover(this, 'assets/demo/cover-lighthouse-1.jpg')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();swapTourCover(this,'assets/demo/cover-lighthouse-1.jpg')}"></span>
            <span role="button" tabindex="0" aria-label="Show cover 2" onclick="swapTourCover(this, 'assets/demo/cover-lighthouse-2.jpg')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();swapTourCover(this,'assets/demo/cover-lighthouse-2.jpg')}"></span>
            <span role="button" tabindex="0" aria-label="Show cover 3" onclick="swapTourCover(this, 'assets/demo/cover-lighthouse-3.jpg')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();swapTourCover(this,'assets/demo/cover-lighthouse-3.jpg')}"></span>
          </div>
          <div class="mock-row with-book">
            <img id="tour-cover-img" class="book-cover-mini" src="assets/demo/cover-lighthouse-1.jpg" alt="The Lighthouse Letters — cover">
            <div class="book-meta"><strong>The Lighthouse Letters</strong><span class="genre">Cozy mystery · Margaret Hayes</span></div>
          </div>
          <div class="mock-block">"A late-summer murder draws an unlikely amateur sleuth onto the rocks of a quiet harbor town…"</div>
        </div>
      </div>

      <div class="tour-row reverse">
        <div class="tour-text">
          <h3>Compose once, post everywhere</h3>
          <p>Write your post once. The composer renders previews for each platform you've connected, with platform-specific tone variations and the right image dimensions. Schedule it, post it now, or save a draft.</p>
          <ul class="tour-list">
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Live previews per platform</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Auto-resized images and videos</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Skip platforms with one click</li>
          </ul>
        </div>
        <div class="tour-visual">
          <div class="header-bar"><span></span><span></span><span></span></div>
          <div class="mock-block" style="background:#1F3810;color:#FAFAF9;font-style:normal;">A locked-room mystery in the off-season. Cozy meets clever, with a cup of something hot. Available now.</div>
          <div class="mock-row"><span class="pdot"></span>TikTok &nbsp;·&nbsp; <strong>Will publish</strong></div>
          <div class="mock-row"><span class="pdot"></span>Instagram &nbsp;·&nbsp; <strong>Will publish</strong></div>
          <div class="mock-row"><span class="pdot" style="background:#0085FF"></span>Bluesky &nbsp;·&nbsp; <strong>Will publish</strong></div>
          <div class="mock-row"><span class="pdot" style="background:var(--ink-soft)"></span>LinkedIn &nbsp;·&nbsp; Skipped</div>
          <span class="mock-btn">Send all</span>
        </div>
      </div>

      <div class="tour-row">
        <div class="tour-text">
          <h3>Trailers your readers will share</h3>
          <p>From your cover, your blurb, and a few choices, the trailer renderer assembles a polished 30–60 second video — with AI backdrops, optional narration, music, and your tagline. Built especially for vertical formats that perform on BookTok and Reels.</p>
          <ul class="tour-list">
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Vertical, square, and widescreen formats</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>AI-generated backdrops tuned to genre</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Re-render variations until it's right</li>
          </ul>
        </div>
        <div class="tour-visual" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1F4670,#336699);border:none;">
          <!-- Trailer mock: plays /assets/demo/examples/trailer-video.mp4 when the
               tour section scrolls into view (IntersectionObserver below —
               no autoplay attribute, since autoplay forces the browser to
               download the whole MP4 on page load even with preload="none").
               Clicking anywhere on the frame also plays/pauses manually; the
               play badge auto-fades once playback starts. -->
          <div class="trailer-mock" onclick="toggleDemoTrailer()" style="position:relative;width:135px;height:240px;background:#0B1A2E;border-radius:8px;overflow:hidden;box-shadow:0 12px 32px rgba(0,0,0,0.3);cursor:pointer;">
            <video id="demo-trailer" loop="" muted="" playsinline="" preload="none" poster="assets/demo/cover-lighthouse-1.jpg" onplaying="this.parentElement.classList.add('is-playing')" onpause="this.parentElement.classList.remove('is-playing')" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;pointer-events:none;">
              <source src="assets/demo/examples/trailer-video.mp4" type="video/mp4">
            </video>
            <span class="trailer-play-badge" aria-hidden="true" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:56px;height:56px;border-radius:50%;background:rgba(11,26,46,0.55);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(2px);transition:opacity 0.4s ease;pointer-events:none;">
              <svg width="22" height="22" viewBox="0 0 16 16" fill="#A8C5E0"><polygon points="6,4 12,8 6,12"></polygon></svg>
            </span>
            <div style="position:absolute;bottom:10px;left:0;right:0;text-align:center;color:#FFFFFF;font-family:Lora,serif;font-style:italic;font-size:13px;text-shadow:0 1px 4px rgba(0,0,0,0.6);pointer-events:none;">Your trailer →</div>
          </div>
        </div>
      </div>

      <div class="tour-row reverse">
        <div class="tour-text">
          <h3>Events with built-in promotion</h3>
          <p>Add a signing, podcast, or library talk. Get email reminders before the event so nothing falls off your calendar. Generate a social post or press release for the event with one click — proper details, proper tone, proper hashtags.</p>
          <ul class="tour-list">
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>24-hour and 1-hour email reminders</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Promote any event in seconds</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Upcoming and past events tracked</li>
          </ul>
        </div>
        <div class="tour-visual">
          <div class="header-bar"><span></span><span></span><span></span></div>
          <div class="mock-row"><span class="pdot"></span>📚 Book signing · <strong>Sat, May 16 · 2:00 PM</strong> 🔔</div>
          <div class="mock-row"><span class="pdot" style="background:#B8965A"></span>🎙 Podcast · <strong>Tue, May 19 · 7:00 PM</strong> 🔔</div>
          <div class="mock-row"><span class="pdot" style="background:#9FC87A"></span>🎉 Launch party · <strong>Sat, Jun 1 · 6:30 PM</strong></div>
          <div style="display:flex;gap:6px;margin-top:14px;">
            <span class="mock-btn">Generate social post</span>
            <span class="mock-btn" style="background:#57534E;">Generate press release</span>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Pricing -->
  <div class="features-section" id="pricing">
    <div class="features-inner">
      <div class="section-eyebrow">Pricing</div>
      <div class="section-title">Plans that match how much <em>you write.</em></div>
      <div class="section-sub">Whether you publish a book a year or a book a month — there's a tier that fits.</div>

      <div class="price-grid">

        <div class="price-card">
          <h3>Starter</h3>
          <p class="pitch">For authors testing the waters or planning their first launch.</p>
          <div class="price-row"><span class="price">$14.95</span><span class="per">/ month</span></div>
          <div class="annual-note">$135 / year — save 25%</div>
          <ul class="price-list">
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>1 book project</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>50 AI images / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>5 book-trailer renders / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>1,000 marketing emails / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Every tool, plus generous AI writing &amp; chat</li>
          </ul>
          <button class="btn btn-outline" onclick="enquireAbout('Starter')">Enquire about Starter</button>
        </div>

        <div class="price-card featured">
          <span class="tier-badge">Most popular</span>
          <h3>Pro</h3>
          <p class="pitch">For active authors running real launches and ongoing campaigns.</p>
          <div class="price-row"><span class="price">$24</span><span class="per">/ month</span></div>
          <div class="annual-note">Save 20% with annual billing</div>
          <ul class="price-list">
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>5 book projects</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>100 AI images / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>10 book-trailer renders / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>2,500 marketing emails / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Every tool, plus generous AI writing &amp; chat</li>
          </ul>
          <button class="btn btn-green" onclick="enquireAbout('Pro')">Enquire about Pro</button>
        </div>

        <div class="price-card">
          <h3>Studio</h3>
          <p class="pitch">For prolific authors, hybrid publishers, and anyone running multiple campaigns at once.</p>
          <div class="price-row"><span class="price">$99</span><span class="per">/ month</span></div>
          <div class="annual-note">Save 20% with annual billing</div>
          <ul class="price-list">
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Unlimited book projects</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>300 AI images / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>30 book-trailer renders / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>10,000 marketing emails / month</li>
            <li><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 8l3 3 7-7"></path></svg>Every tool, plus unlimited AI writing &amp; chat</li>
          </ul>
          <button class="btn btn-outline" onclick="enquireAbout('Studio')">Enquire about Studio</button>
        </div>

      </div>

      <p class="pricing-foot" style="text-align:center"><strong>Image, trailer, and email allowances are per&nbsp;month</strong> and reset each billing period. Need more email? Add 5,000 sends for $10 — one-time, and they never expire.</p>

      <div style="max-width:1080px;margin:34px auto 0;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px 32px">
        <h3 style="font-size:21px;margin:0 0 6px;text-align:center">Everything in the app — included on every plan</h3>
        <p style="color:var(--ink-soft,#64748b);margin:0 auto 22px;font-size:15px;text-align:center;max-width:700px">Plans differ only in the monthly allowances and book count above. Every tool below is on <strong>every</strong> tier.</p>
        <div id="plan-catalog">
          <div>
            <h4>✍️ AI Writing &amp; Copy</h4>
            <ul><li>Book descriptions &amp; blurbs</li><li>Press releases</li><li>Sell sheets</li><li>Query &amp; cover letters</li><li>Author bio</li><li>Amazon Author Central bio</li><li>Taglines &amp; loglines</li><li>Ad &amp; social copy</li><li>Email subjects, preheaders &amp; body</li><li>Media kit</li></ul>
          </div>
          <div>
            <h4>🎨 Graphics &amp; Design</h4>
            <ul><li>AI-generated book cover designs</li><li>Social media graphics</li><li>Quote cards</li><li>Event &amp; signing flyers</li><li>YouTube thumbnail text</li><li>Ad visuals</li></ul>
          </div>
          <div>
            <h4>🎬 Book Trailers</h4>
            <ul><li>Trailer scripts with scene cues</li><li>30-second vertical trailer videos</li><li>Motion, music &amp; text overlays</li></ul>
          </div>
          <div>
            <h4>📣 Social — 15 Platforms</h4>
            <ul><li>Facebook, Instagram, X/Twitter</li><li>Threads, TikTok, LinkedIn</li><li>Bluesky, Pinterest, Reddit</li><li>YouTube, Goodreads, BookBub</li><li>Substack, Medium, Discord</li><li>AutoPost + guided handoff</li><li>Content calendar &amp; scheduling</li></ul>
          </div>
          <div>
            <h4>🌐 Author Website</h4>
            <p style="margin:0 0 6px;font-size:12.5px;color:var(--ink-mid);line-height:1.5">Your social accounts are rented land — a website is the one home base online that's fully yours.</p>
            <ul><li>One-click full site build (theme, pages &amp; store)</li><li>Genre-matched style packs</li><li>Automatic book publishing from the app</li><li>Built-in store for selling print books</li><li>Blog post publishing</li><li>Reviews preserved on every sync</li><li>Guided setup wizard &amp; host picks</li><li>Automatic plugin updates</li></ul>
          </div>
          <div>
            <h4>📧 Email Marketing</h4>
            <ul><li>Campaigns &amp; newsletters</li><li>Contacts &amp; lists</li><li>Automated sequences</li><li>Open, click &amp; bounce analytics</li></ul>
          </div>
          <div>
            <h4>📅 Events</h4>
            <ul><li>Signings, launches &amp; readings</li><li>Podcasts &amp; book-club visits</li><li>Library talks &amp; festivals</li><li>Virtual events</li><li>Auto 24h &amp; 1h email reminders</li></ul>
          </div>
          <div>
            <h4>📈 Amazon &amp; KDP</h4>
            <ul><li>Keyword research</li><li>BISAC categories</li><li>A+ Content modules</li><li>KDP promotions</li><li>Sales-rank (BSR) tracking</li></ul>
          </div>
          <div>
            <h4>🛒 Sell &amp; Distribute</h4>
            <ul><li>Print quotes &amp; book printing</li><li>Distribution links (IngramSpark)</li><li>Shopify sync</li></ul>
          </div>
          <div>
            <h4>🧭 Plan &amp; Learn</h4>
            <ul><li>AI marketing game plan with tasks</li><li>AI help assistant</li><li>Education library of guides</li><li>Analytics dashboard</li><li>Manage multiple books</li><li>Cancel anytime</li></ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="cta-section" id="get-started">
    <div class="cta-inner">
      <h2>Your book deserves more than <em>wishful thinking.</em></h2>
      <p>The tools to promote it are right here. Start using them today.</p>
      <button class="btn btn-white btn-lg" onclick="scrollToEnquiry()">Start your book today</button>
    </div>
  </div>

  <!-- Footer -->
  <?php /* The landing-page footer, from
           includes/components/lp-chrome-footer.php. It replaces this page's own
           footer, which carried the previous product's wordmark and linked to
           terms.html and privacy.html — two files that do not exist in this
           folder, so both were 404s. The replacement's legal links are built
           from ep_page_url() and resolve to the real pages.

           NOTE for anyone restyling it: assets/css/landing.css guards this
           page's abandoned dark footer with `#landing footer:not(.ep-footer)`
           at id specificity. That guard is written against the .ep-footer class
           this component still carries. Rename the class and those rules
           capture the new footer — near-black on near-black, grid collapsed to
           a flex row. See the block at the FOOTER heading in landing.css.

           Inside #landing on purpose: the app view below is a separate screen
           that JavaScript swaps to, and the public footer should not follow the
           visitor into it. */ ?>
  <?php require __DIR__ . '/../includes/lp-footer.php'; ?>

</div><!-- /landing -->

<!-- ════════════════════════════════════════
     APP SHELL
════════════════════════════════════════ -->
<div id="app-screen">

  <!-- Demo-mode banner — only shown when currentUser.is_demo === 1 -->
  <div id="demo-banner">
    🎬 <strong>Demo mode</strong> — explore freely. AI generations are illustrative; nothing actually posts to social or sends email.
    <a href="#" onclick="contactUs(); return false;">Enquire to start posting →</a>
    <a href="#" onclick="doLogout(); return false;" style="opacity:0.85">Exit demo</a>
  </div>

  <header id="topbar">
    <button id="hamburger-btn" onclick="toggleSidebar()" title="Menu">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M2 4h12M2 8h12M2 12h12"></path></svg>
    </button>
    <div class="topbar-logo"><img src="<?= esc(asset("img/logo.png")) ?>" alt="Elite Publishing"></div>
    <select class="book-selector" id="bookSelector" onchange="spSyncLinkFromBook()">
      <option>Select a book…</option>
    </select>
    <div class="topbar-right">
      <span class="topbar-name" id="topbar-name"></span>
      <button class="icon-btn" title="Notifications">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M8 1a5 5 0 015 5v3l1.5 2h-13L3 9V6a5 5 0 015-5z"></path><path d="M6.5 13.5a1.5 1.5 0 003 0"></path></svg>
      </button>
      <button class="logout-btn" onclick="doLogout()">Sign out</button>
    </div>
  </header>

  <!-- The trial / subscription banner was removed with the billing flow:
       it existed only to push the visitor toward checkout. -->

  <div id="shell">
    <div id="sidebar-backdrop" onclick="closeSidebar()"></div>
    <nav id="sidebar">
      <div class="nav-section">
        <div class="nav-item active" data-view="dashboard">
          <svg class="nav-icon" viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1.5"></rect><rect x="9" y="1" width="6" height="6" rx="1.5"></rect><rect x="1" y="9" width="6" height="6" rx="1.5"></rect><rect x="9" y="9" width="6" height="6" rx="1.5"></rect></svg>Dashboard
        </div>
        <div class="nav-item" data-view="books">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M3 2h8a1 1 0 011 1v11l-4-2-4 2V3a1 1 0 011-1z"></path></svg>My Books
        </div>
      </div>
      <div class="nav-section">
        <div class="nav-label">Campaigns</div>
		  
		  <div class="nav-item" data-view="website">
  <svg class="nav-icon" viewBox="0 0 16 16">
    <circle cx="8" cy="8" r="6" fill="none" stroke="currentColor" stroke-width="1.2"></circle>
    <path d="M2 8h12M8 2c2 2 2 10 0 12M8 2c-2 2-2 10 0 12" fill="none" stroke="currentColor" stroke-width="1.2"></path>
  </svg>Website
  <span class="platform-status off" id="nav-website-status"></span>
</div>
		  
        <div class="nav-item" data-view="social">
          <svg class="nav-icon" viewBox="0 0 16 16"><circle cx="12" cy="4" r="2"></circle><circle cx="4" cy="8" r="2"></circle><circle cx="12" cy="12" r="2"></circle><path d="M6 7.5l4-2M6 8.5l4 2"></path></svg>Social Posts
          <span class="platform-status off" id="nav-social-status"></span>
        </div>
        <div class="nav-item" data-view="email">
          <svg class="nav-icon" viewBox="0 0 16 16"><rect x="1" y="3" width="14" height="10" rx="1"></rect><path d="M1 4l7 5 7-5"></path></svg>Email Campaigns
        </div>
        <div class="nav-item" data-view="promo">
          <svg class="nav-icon" viewBox="0 0 16 16"><rect x="2" y="2" width="12" height="12" rx="1"></rect><path d="M5 6h6M5 8.5h4M5 11h5"></path></svg>Promo Materials
        </div>
        <div class="nav-item" data-view="videos">
          <svg class="nav-icon" viewBox="0 0 16 16"><rect x="1" y="2" width="9" height="7" rx="1"></rect><path d="M10 5l4-2v6l-4-2z"></path><rect x="1" y="11" width="14" height="3" rx="1"></rect></svg>Graphics &amp; Video
        </div>
        <div class="nav-item" data-view="press">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M2 2h12v12H2z"></path><path d="M5 5h6M5 7.5h6M5 10h4"></path></svg>Press Releases
        </div>
        <div class="nav-item" data-view="events">
          <svg class="nav-icon" viewBox="0 0 16 16"><rect x="1" y="3" width="14" height="12" rx="1"></rect><path d="M1 7h14M5 1v4M11 1v4"></path></svg>Events
        </div>
      </div>
      <div class="nav-section">
        <div class="nav-label">Publishing</div>
        <div class="nav-item" data-view="ebook-convert">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M3 1.5h6.5l3.5 3.5v9.5H3z"></path><path d="M9.5 1.5V5H13"></path><path d="M5.5 8.5h5M5.5 11h3.5" stroke="currentColor" stroke-width="1.3"></path></svg>eBook Maker
        </div>
        <div class="nav-item" data-view="kdp">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M1.5 1.5h7l6 6-7 7-6-6z"></path><circle cx="5" cy="5" r="1" fill="#fff"></circle></svg>Amazon KDP Tools
        </div>
        <div class="nav-item" data-view="print-quote">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M3 5h10v6H3z"></path><path d="M4 1h8v4H4zM4 11v4h8v-4"></path><circle cx="11" cy="7.5" r="0.8" fill="#fff"></circle></svg>Book Printing
        </div>
      </div>
      <div class="nav-section">
        <div class="nav-label">Commerce</div>
        <div class="nav-item" data-view="sales">
          <svg class="nav-icon" viewBox="0 0 16 16"><circle cx="6" cy="13" r="1.5"></circle><circle cx="12" cy="13" r="1.5"></circle><path d="M1 1h2l2 8h7l2-5H5"></path></svg>Sales Channels
        </div>
        <div class="nav-item" data-view="distribution">
          <svg class="nav-icon" viewBox="0 0 16 16"><rect x="2" y="10" width="4" height="4" rx="1"></rect><rect x="10" y="10" width="4" height="4" rx="1"></rect><rect x="6" y="2" width="4" height="4" rx="1"></rect><path d="M8 6v3M4 10V9h8v1"></path></svg>Distribution
        </div>
        <div class="nav-item" data-view="production">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M3 2h7l3 3v9H3z"></path><path d="M10 2v3h3M6 7h4M6 9.5h4"></path></svg>Production / Print
        </div>
      </div>
      <div class="nav-section">
        <div class="nav-label">Tools</div>
        <div class="nav-item" data-view="kdp-keywords">
          <svg class="nav-icon" viewBox="0 0 16 16"><circle cx="5" cy="11" r="3"></circle><path d="M7.5 9.5l5-5M11 7l1.5 1.5M9 8l1.2 1.2" fill="none" stroke="currentColor" stroke-width="1.4"></path></svg>Keywords + Categories
        </div>
        <div class="nav-item" data-view="ads">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M2 14V7l3-5h6l3 5v7z"></path><path d="M5.5 7h5M8 7v5"></path></svg>Ads
        </div>
        <div class="nav-item" data-view="contacts">
          <svg class="nav-icon" viewBox="0 0 16 16"><circle cx="8" cy="5" r="3"></circle><path d="M2 14c0-3 2.7-5 6-5s6 2 6 5"></path></svg>Contacts / Lists
        </div>
        <div class="nav-item" data-view="analytics">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M1 14h14M4 10v4M8 6v8M12 2v12"></path></svg>Analytics
        </div>
        <div class="nav-item" data-view="education">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M2 4h12v7H2z"></path><path d="M5 11v3M11 11v3M3 14h10M8 4V1M5 1h6"></path></svg>Learn
        </div>
        <div class="nav-item" onclick="window.open('https://elitepublishing.co/','_blank')">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M2 2h12v9H6l-3 3v-3H2z"></path><path d="M5 5h6M5 7.5h4"></path></svg>Elite Publishing Blog
        </div>
        <div class="nav-item" data-view="wordpress">
          <svg class="nav-icon" viewBox="0 0 16 16"><circle cx="8" cy="8" r="7"></circle><path d="M8 1v14M1 8h14"></path><path d="M3 4c1.5 1 3.5 1.5 5 1s3.5-.5 5 .5"></path><path d="M3 12c1.5-1 3.5-1.5 5-1s3.5.5 5-.5"></path></svg>WordPress
        </div>
        <div class="nav-item" data-view="connections">
          <svg class="nav-icon" viewBox="0 0 16 16"><circle cx="8" cy="8" r="7"></circle><path d="M5 8h6M8 5v6"></path></svg>Connections
        </div>
        <div class="nav-item" data-view="account">
          <svg class="nav-icon" viewBox="0 0 16 16"><circle cx="8" cy="8" r="7"></circle><circle cx="8" cy="8" r="2.5"></circle></svg>Account
        </div>
      </div>
      <div class="nav-section" id="admin-nav-section" style="display:none">
        <div class="nav-label">Admin</div>
        <div class="nav-item" data-view="admin-users">
          <svg class="nav-icon" viewBox="0 0 16 16"><circle cx="6" cy="5" r="3"></circle><path d="M1 14c0-3 2-5 5-5s5 2 5 5"></path><circle cx="12" cy="5" r="2"></circle><path d="M12 10c1.5 0 3 1 3 4"></path></svg>Users
        </div>
        <div class="nav-item" data-view="admin-usage">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M1 14h14M4 10v4M8 6v8M12 2v12"></path></svg>AI Usage
        </div>
        <div class="nav-item" data-view="admin-groups">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M2 13c0-2.2 1.8-4 4-4s4 1.8 4 4" fill="none" stroke="currentColor" stroke-width="1.4"></path><circle cx="6" cy="5" r="2.4" fill="none" stroke="currentColor" stroke-width="1.4"></circle><path d="M11 9.5c1.7 0 3 1.3 3 3.5" fill="none" stroke="currentColor" stroke-width="1.4"></path><circle cx="11.5" cy="5.5" r="1.8" fill="none" stroke="currentColor" stroke-width="1.4"></circle></svg>Writers Groups
        </div>
        <div class="nav-item" data-view="admin-chatlog">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M2 2h12v9H6l-3 3v-3H2z" fill="none" stroke="currentColor" stroke-width="1.4"></path><path d="M5 5h6M5 7.5h4" fill="none" stroke="currentColor" stroke-width="1.2"></path></svg>Chat Log
        </div>
        <div class="nav-item" data-view="admin-overrides">
          <svg class="nav-icon" viewBox="0 0 16 16"><path d="M2 4h12M2 8h12M2 12h8" fill="none" stroke="currentColor" stroke-width="1.4"></path><circle cx="13" cy="12" r="2" fill="currentColor"></circle></svg>Chat Overrides
        </div>
      </div>
      <div class="quota-widget" id="quota-widget" style="display:none">
        <div class="quota-widget-label">
          <span>AI usage</span>
          <span id="quota-pct-label">0%</span>
        </div>
        <div class="quota-bar-track">
          <div class="quota-bar-fill" id="quota-bar-fill" style="width:0%"></div>
        </div>
        <div class="quota-upgrade" id="quota-upgrade-nudge">
          Running low — <a href="#" onclick="navigate('account');return false" style="color:inherit;text-decoration:underline">upgrade your plan</a>
        </div>
      </div>
      <button id="sidebar-feedback-btn" onclick="openFeedbackModal()" title="Report an issue or request a feature">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:7px"><path d="M14 3H2v9h3v2.5L8.5 12H14z"></path></svg>
        Feedback
      </button>
    </nav>

    <main id="main">

      <!-- DASHBOARD -->
      <div class="view active" id="view-dashboard">
        <div class="page-header">
          <h1>Dashboard</h1>
          <p id="dash-welcome">Welcome back — here's how your books are performing</p>
        <button class="wt-ctx" type="button">Need help with setup?</button></div>

        <!-- ── Author's Game Plan ─────────────────────────────── -->
        <div class="game-plan" id="game-plan-panel" style="display:none">
          <div class="game-plan-header">
            <h2 class="game-plan-title" id="game-plan-title">Your game plan for growing book sales</h2>
            <span class="game-plan-sub" id="game-plan-progress"></span>
          </div>
          <p class="game-plan-sub" id="game-plan-intro">Here's what to tackle next — one click takes you straight there.</p>
          <div id="game-plan-demo-note" class="game-plan-demo-note" style="display:none">
            This is the game plan we build for every author — sign up and yours will pick up where this demo leaves off.
          </div>
          <div class="game-plan-list" id="game-plan-list"></div>
          <div class="game-plan-footer">
            <span style="margin-left:auto">Updates as you complete each step.</span>
          </div>
        </div>

        <div class="stat-grid">
          <div class="stat-card"><div class="label">Total sales</div><div class="value">—</div><div class="sub">Connect sales channels</div></div>
          <div class="stat-card"><div class="label">Email subscribers</div><div class="value">—</div><div class="sub">Set up email campaigns</div></div>
          <div class="stat-card"><div class="label">Posts sent</div><div class="value" id="dash-posts">0</div><div class="sub up">via Elite Publishing</div></div>
          <div class="stat-card"><div class="label">Social reach</div><div class="value">—</div><div class="sub">Connect platforms</div></div>
        </div>
        <div id="connect-prompt" class="connect-banner" style="display:none">
          <span>Connect your social accounts to start posting from the portal.</span>
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('connections')">Connect now</button>
        </div>

        <!-- ── Plan usage (this billing period) ─────────────────── -->
        <div class="card" id="plan-usage-card" style="display:none;margin-bottom:18px">
          <div style="display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:8px;margin-bottom:14px">
            <div class="card-title" style="margin:0">Your plan usage</div>
            <span style="font-size:12px;color:var(--ink-soft)" id="plan-usage-meta"></span>
          </div>
          <div id="plan-usage-rows" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px"></div>
          <div id="plan-usage-upgrade" style="display:none;margin-top:14px;font-size:13px">
            Approaching a limit? <a href="#" onclick="contactUs();return false" style="color:var(--accent);font-weight:600">Get in touch →</a>
          </div>
        </div>

        <!-- ── Marketing Progress Grid ─────────────────────────── -->
        <div id="progress-grid-section" style="margin-bottom:18px">
          <div class="progress-intro">
            <strong>Your marketing progress.</strong> Below is every marketing area in Elite Publishing. You don't need to do them all at once — most authors start with a few. As more green checks fill in, more of your marketing is working for you. Click any item to jump straight to that section.
            <div class="progress-summary">
              <div class="progress-bar"><div class="progress-bar-fill" id="progress-bar-fill" style="width:0%"></div></div>
              <div class="progress-summary-label" id="progress-summary-label"><strong>0</strong> of <strong>0</strong> complete</div>
            </div>
          </div>
          <div id="progress-grid"></div>
        </div>

        <div class="two-col">
          <div class="card">
            <div class="card-title">Quick actions</div>
            <div class="actions" style="flex-direction:column;align-items:stretch">
              <button class="app-btn app-btn-green" onclick="navigate('social')">+ New social post</button>
              <button class="app-btn app-btn-outline" onclick="navigate('email')">Create email campaign</button>
              <button class="app-btn app-btn-outline" onclick="navigate('press')">Write press release</button>
              <button class="app-btn app-btn-outline" onclick="navigate('events')">Add event</button>
            </div>
          </div>
          <div class="card">
            <div class="card-title">Platform connections</div>
            <div class="row"><div class="row-left"><span class="pdot" style="background:#1877F2"></span>Facebook</div><span class="badge badge-gray" id="conn-facebook">Not connected</span></div>
            <div class="row"><div class="row-left"><span class="pdot" style="background:#E1306C"></span>Instagram</div><span class="badge badge-gray" id="conn-instagram">Not connected</span></div>
            <div class="row"><div class="row-left"><span class="pdot" style="background:#0085ff"></span>Bluesky</div><span class="badge badge-gray" id="conn-bluesky">Not connected</span></div>
            <div class="row"><div class="row-left"><span class="pdot" style="background:#0A66C2"></span>LinkedIn</div><span class="badge badge-gray" id="conn-linkedin">Not connected</span></div>
            <div class="row" style="display:none"><div class="row-left"><span class="pdot" style="background:#010101"></span>TikTok</div><span class="badge badge-gray" id="conn-tiktok">Not connected</span></div>
            <div class="row"><div class="row-left"><span class="pdot" style="background:#E60023"></span>Pinterest</div><span class="badge badge-gray" id="conn-pinterest">Not connected</span></div>
            <div style="margin-top:14px;text-align:right"><button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('connections')">Manage connections →</button></div>
          </div>
        </div>
      </div>

      <!-- MY BOOKS -->
      <div class="view" id="view-books">

        <!-- Book list -->
        <div id="books-list-view">
          <div class="page-header">
            <h1>My Books</h1>
            <p>Manage your titles, covers, and metadata</p>
          </div>
          <div class="actions">
            <button class="app-btn app-btn-green" onclick="showBookForm()">+ Add book</button>
          </div>
          <div id="books-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px">
            <div class="empty" style="grid-column:1/-1">No books added yet — click above to add your first title</div>
          </div>
        </div>

        <!-- Add / Edit book form -->
        <div id="books-form-view" style="display:none">
          <div class="page-header" style="display:flex;align-items:center;gap:16px">
            <button class="lesson-back" onclick="hideBookForm()" style="margin:0">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M9 2L4 7l5 5"></path></svg>
              Back to books
            </button>
            <h1 id="book-form-title">Add a book</h1>
            <button class="app-btn-help" onclick="showSetupHelp('book-setup')" title="Step-by-step setup instructions" style="margin-left:auto"><span class="help-q">?</span>Setup help</button>
          </div>

          <div class="two-col" style="align-items:start">
            <!-- Cover upload -->
            <div>
              <div class="card" style="text-align:center;padding:24px">
                <div class="card-title">Book cover</div>
                <div id="cover-preview" style="margin-bottom:16px">
                  <div id="cover-placeholder" style="width:140px;height:200px;background:var(--ink-faint);border-radius:var(--radius);margin:0 auto;display:flex;align-items:center;justify-content:center;color:var(--ink-soft);font-size:12px">No cover</div>
                  <img id="cover-img" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Book cover" style="display:none;width:140px;height:200px;object-fit:cover;border-radius:var(--radius);margin:0 auto">
                </div>
                <input type="file" id="cover-file" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif,application/pdf" style="display:none" onchange="handleCoverUpload(this)">
                <button class="app-btn app-btn-outline" onclick="document.getElementById('cover-file').click()" style="width:100%;margin-bottom:8px">
                  Upload cover (image or PDF)
                </button>
                <div id="cover-upload-status" style="font-size:11px;color:var(--ink-soft)">JPG, PNG or WebP — max 5MB</div>
                <input type="hidden" id="book-cover-url">
              </div>

              <div class="card" style="margin-top:0">
                <div class="card-title">Formats available</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                  <label style="display:flex;align-items:center;gap:8px;font-size:13.5px;cursor:pointer"><input type="checkbox" value="paperback" class="format-check" style="width:auto;accent-color:var(--accent)"> Paperback</label>
                  <label style="display:flex;align-items:center;gap:8px;font-size:13.5px;cursor:pointer"><input type="checkbox" value="hardcover" class="format-check" style="width:auto;accent-color:var(--accent)"> Hardcover</label>
                  <label style="display:flex;align-items:center;gap:8px;font-size:13.5px;cursor:pointer"><input type="checkbox" value="ebook" class="format-check" style="width:auto;accent-color:var(--accent)"> eBook</label>
                  <label style="display:flex;align-items:center;gap:8px;font-size:13.5px;cursor:pointer"><input type="checkbox" value="audiobook" class="format-check" style="width:auto;accent-color:var(--accent)"> Audiobook</label>
                </div>
              </div>

              <!-- Author photo: stored once per author, reused across books +
                   press releases / sell sheets / bio. Auto-fills on later books. -->
              <div class="card" style="margin-top:0;text-align:center;padding:24px">
                <div class="card-title">Author photo</div>
                <div style="font-size:11.5px;color:var(--ink-soft);margin-bottom:12px">Used on press releases and your author bio. Set it once — it carries to every book.</div>
                <div id="author-photo-preview" style="margin-bottom:14px">
                  <div id="author-photo-placeholder" style="width:120px;height:120px;background:var(--ink-faint);border-radius:50%;margin:0 auto;display:flex;align-items:center;justify-content:center;color:var(--ink-soft);font-size:12px">No photo</div>
                  <img id="author-photo-img" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Author photo" style="display:none;width:120px;height:120px;object-fit:cover;border-radius:50%;margin:0 auto">
                </div>
                <input type="file" id="author-photo-file" accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none" onchange="handleAuthorPhotoUpload(this)">
                <button class="app-btn app-btn-outline" onclick="document.getElementById('author-photo-file').click()" style="width:100%;margin-bottom:8px">Upload author photo</button>
                <div id="author-photo-status" style="font-size:11px;color:var(--ink-soft)">JPG, PNG or WebP — a clear headshot works best</div>
                <input type="hidden" id="author-photo-url">
              </div>
            </div>

            <!-- Book details -->
            <div>
              <div class="card">
                <div class="card-title">Book details</div>
                <input type="hidden" id="book-id">
                <div class="field-group"><label class="field-label">Title <span style="color:var(--danger)">*</span></label><input type="text" id="book-title" placeholder="The name of your book"></div>
                <div class="field-group"><label class="field-label">Author</label><input type="text" id="book-author" placeholder="Author name as it appears on the cover"></div>
                <div class="field-group"><label class="field-label">Subtitle</label><input type="text" id="book-subtitle" placeholder="Optional subtitle"></div>
                <div style="background:#fefce8;border:1px solid #fde68a;border-radius:var(--radius);padding:10px 14px;font-size:12px;color:#92400e;margin-bottom:4px">
                  <strong>Tip:</strong> The more you fill in, the better your AI results. A full description, author bio, genre, audience, and comp titles make a significant difference in every piece of content the AI generates.
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                  <div class="field-group"><label class="field-label">ISBN</label><input type="text" id="book-isbn" placeholder="978-0-000-00000-0"></div>
                  <div class="field-group"><label class="field-label">Page count</label><input type="text" id="book-pages" placeholder="e.g. 320"></div>
                  <div class="field-group"><label class="field-label">Trim size</label><input type="text" id="book-page-size" placeholder="e.g. 6×9, 5.5×8.5"></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px">
                  <div class="field-group"><label class="field-label">Amazon ASIN</label><input type="text" id="book-amazon-asin" placeholder="B0XXXXXXX" maxlength="20"></div>
                  <div class="field-group"><label class="field-label">Amazon listing URL</label><input type="text" id="book-amazon-url" placeholder="https://www.amazon.com/dp/..."></div>
                </div>
                <div class="lesson-tip" style="margin:-4px 0 14px 0;font-size:12.5px">
                  <strong>Why fill these in?</strong> When you publish on KDP, paste your book's ASIN and Amazon URL here. Generated social posts, sell sheets, and email campaigns will auto-include the real buy link instead of <code>[link]</code> placeholders.
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                  <div class="field-group"><label class="field-label">Genre</label><input type="text" id="book-genre" placeholder="e.g. Mystery, Fantasy"></div>
                  <div class="field-group"><label class="field-label">Publication date</label><input type="date" id="book-pubdate"></div>
                </div>
                <div class="field-group"><label class="field-label">Publisher</label><input type="text" id="book-publisher" placeholder="Publisher name or Self-published"></div>
                <div class="field-group">
                  <label class="field-label">Status</label>
                  <select id="book-status">
                    <option value="published">Published</option>
                    <option value="draft">Draft / Coming soon</option>
                    <option value="out_of_print">Out of print</option>
                  </select>
                </div>
                <div class="field-group">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <label class="field-label" style="margin:0">Description / Back cover blurb</label>
                    <button class="ai-btn" type="button" onclick="aiDraftDescription()">
                      <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                      AI draft
                    </button>
                  </div>
                  <textarea id="book-description" rows="5" placeholder="Write your description here, or fill in the fields above and let AI draft it for you…"></textarea>
                </div>
                <div class="field-group">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <label class="field-label" style="margin:0">Author bio</label>
                    <a href="#" onclick="navigate('education'); openLesson('author-bios'); return false" style="font-size:12px;color:var(--accent);text-decoration:none">See example bios →</a>
                  </div>
                  <textarea id="book-author-bio" rows="3" placeholder="2–3 sentences about the author — used in press releases, cover letters, and sell sheets"></textarea>
                </div>
                <div class="field-group">
                  <label class="field-label">Target audience</label>
                  <textarea id="book-audience" rows="2" placeholder="e.g. Women 35–55 who enjoy cozy mysteries and small-town settings"></textarea>
                </div>
                <div class="field-group">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <label class="field-label" style="margin:0">Comparable titles</label>
                    <button class="ai-btn" type="button" onclick="aiBookComps(this)">
                      <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                      Suggest titles
                    </button>
                  </div>
                  <textarea id="book-comps" rows="3" placeholder="e.g. Still Life by Louise Penny, Maisie Dobbs by Jacqueline Winspear — or click Suggest titles"></textarea>
                </div>
                <div class="field-group">
                  <label class="field-label">Keywords</label>
                  <textarea id="book-keywords" rows="2" placeholder="e.g. small-town romance, found family, cozy mystery — for search and AI context"></textarea>
                </div>
                <div class="field-group">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <label class="field-label" style="margin:0">Tagline</label>
                    <button class="ai-btn" type="button" onclick="aiTagline('tagline')">
                      <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                      Generate ideas
                    </button>
                  </div>
                  <input type="text" id="book-tagline" placeholder="One punchy sentence that sells the book">
                  <div id="tagline-results" style="display:none;margin-top:8px;border:1px solid var(--ink-faint);border-radius:var(--radius);overflow:hidden"></div>
                </div>
                <div class="field-group">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <label class="field-label" style="margin:0">Logline</label>
                    <button class="ai-btn" type="button" onclick="aiTagline('logline')">
                      <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                      Generate ideas
                    </button>
                  </div>
                  <textarea id="book-logline" rows="2" placeholder="One or two sentences: hook, protagonist, stakes"></textarea>
                  <div id="logline-results" style="display:none;margin-top:8px;border:1px solid var(--ink-faint);border-radius:var(--radius);overflow:hidden"></div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                  <button class="app-btn app-btn-green" onclick="saveBook()">Save book</button>
                  <button class="app-btn app-btn-outline" onclick="hideBookForm()">Cancel</button>
                  <button class="app-btn app-btn-outline" id="book-shopify-btn" onclick="syncCurrentBookToShopify()" style="display:none;border-color:#96BF48;color:#5a7a2c"><span style="display:inline-block;width:8px;height:8px;background:#96BF48;border-radius:50%;margin-right:6px;vertical-align:middle"></span><span id="book-shopify-btn-label">Sync to Shopify</span></button>
                  <a id="book-shopify-link" href="#" target="_blank" rel="noopener" style="display:none;font-size:12.5px;color:#5a7a2c;text-decoration:none">View in Shopify ↗</a>
                  <button class="app-btn app-btn-outline" id="book-woo-btn" onclick="syncCurrentBookToWoo()" style="display:none;border-color:#7F54B3;color:#5d3d8a"><span style="display:inline-block;width:8px;height:8px;background:#7F54B3;border-radius:50%;margin-right:6px;vertical-align:middle"></span><span id="book-woo-btn-label">Sync to WooCommerce</span></button>
                  <a id="book-woo-link" href="#" target="_blank" rel="noopener" style="display:none;font-size:12.5px;color:#5d3d8a;text-decoration:none">View in WooCommerce ↗</a>
                  <button class="app-btn app-btn-outline" id="book-website-btn" onclick="publishCurrentBookToWebsite()" style="display:none;border-color:#2271b1;color:#1d5a8a"><span style="display:inline-block;width:8px;height:8px;background:#2271b1;border-radius:50%;margin-right:6px;vertical-align:middle"></span><span id="book-website-btn-label">Publish to my website</span></button>
                  <a id="book-website-link" href="#" target="_blank" rel="noopener" style="display:none;font-size:12.5px;color:#1d5a8a;text-decoration:none">View page ↗</a>
                  <button class="app-btn app-btn-outline" id="book-delete-btn" onclick="deleteBook()" style="display:none;margin-left:auto;color:var(--danger);border-color:#FECACA">Delete book</button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- SOCIAL POSTS -->
      <div class="view" id="view-social">
        <div class="page-header"><h1>Social Posts</h1><p>Compose once, publish everywhere</p></div>

        <div style="background:var(--paper-soft);border-left:3px solid var(--accent);padding:14px 18px;margin-bottom:20px;border-radius:6px;font-size:14px;line-height:1.65;color:var(--ink)">
          <p style="margin:0"><strong>Why post to social?</strong> Social media is how indie authors stay visible between launches. The trick is consistency, not virality — a steady drumbeat of posts about your book, your process, and your reading life builds an audience that actually buys when the next launch comes. This is the workspace for that drumbeat: write once, pick your platforms, and publish to Facebook, Instagram, Bluesky, LinkedIn, and TikTok in a single click. Connect new platforms under <a href="#" onclick="navigate('connections');return false;">Connections</a> and check the <a href="#" onclick="navigate('education'); openLesson('social-media-authors'); return false;">Social media for authors</a> lesson for what to post and how often.</p>
        </div>

        <div class="card">
          <div class="card-title">Compose post</div>
          <div id="social-connect-warn" class="connect-banner" style="display:none">
            No social platforms connected yet. <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('connections')">Connect platforms</button>
          </div>
          <div class="field-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <label class="field-label" style="margin:0">Post content</label>
              <button class="ai-btn" onclick="aiDraftPost()">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                AI draft
              </button>
            </div>
            <textarea id="post-content" rows="5" autocomplete="off" placeholder="Write your post here, or type a direction and let AI draft it for you…"></textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;flex-wrap:wrap;gap:8px">
              <div style="display:flex;gap:6px;flex-wrap:wrap">
                <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spToggleHashtagPanel()">#&nbsp;Hashtags</button>
                <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spInsertLineBreak()" title="Insert a blank line at the cursor">↵&nbsp;Line break</button>
              </div>
              <div style="font-size:11px;color:var(--ink-soft)" id="char-count">0 characters</div>
            </div>
            <div id="sp-hashtag-panel" style="display:none;margin-top:8px;padding:10px;background:var(--bg-soft,#f8f7fc);border:1px solid var(--border-soft,#e5e5ea);border-radius:6px">
              <div style="font-size:11px;color:var(--ink-soft);margin-bottom:8px">Click a hashtag to add it to your post, or type your own. We skip duplicates.</div>
              <div style="display:flex;gap:6px;margin-bottom:10px">
                <input type="text" id="sp-hashtag-custom" placeholder="Type a custom tag, e.g. vinyldialogues" style="flex:1;font-size:12px;padding:5px 8px" onkeydown="if(event.key==='Enter'){event.preventDefault();spInsertCustomHashtag();}">
                <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spInsertCustomHashtag()">Add</button>
              </div>
              <div id="sp-hashtag-chips" style="display:flex;gap:6px;flex-wrap:wrap"></div>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Image or video (optional)</label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" id="post-image" placeholder="https://yoursite.com/image.jpg — or upload an image or MP4" style="flex:1" oninput="onPostImageChanged()">
              <button class="app-btn app-btn-outline app-btn-sm" type="button" id="post-image-upload-btn" onclick="document.getElementById('post-image-file').click()" style="white-space:nowrap">Upload</button>
              <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spFillBookCover()" style="white-space:nowrap">Use book cover</button>
              <input type="file" id="post-image-file" accept="image/png,image/jpeg,image/webp,image/gif,video/mp4,video/quicktime,.mp4,.mov" style="display:none" onchange="spUploadPostImage(this)">
            </div>
            <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Upload a file from your computer, or paste a URL. Images are auto-resized to fit every platform. <strong>Videos (MP4/MOV)</strong> post by hand: <strong>Post now</strong> opens a tab per platform with the caption and click-by-click steps — same as posting a trailer.</div>
          </div>
          <div class="field-group">
            <label class="field-label">Link to include <span style="font-weight:400;color:var(--ink-soft)">— added to the end of your post on a new line</span></label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" id="post-link" placeholder="https://yoursite.com/book" style="flex:1">
              <button class="app-btn app-btn-outline app-btn-sm" onclick="spFillLink()" style="white-space:nowrap">Use book link</button>
            </div>
            <div id="post-link-instagram-note" style="display:none;font-size:11px;color:var(--ink-soft);margin-top:4px">Note: Instagram doesn't make caption links clickable — consider "link in bio" instead.</div>
          </div>
          <div class="field-group">
            <label class="field-label">Final post preview</label>
            <div id="post-preview" style="font-size:13px;background:var(--bg-soft,#f8f7fc);border:1px solid var(--border-soft,#e5e5ea);border-radius:6px;padding:10px 12px;min-height:42px;white-space:pre-wrap;line-height:1.5;color:var(--ink)">
              <span style="color:var(--ink-soft);font-style:italic">Type a post above to see how it'll look with the link.</span>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Publish to <span style="font-weight:400;color:var(--ink-soft);font-size:12px">— only your set-up platforms appear, filtered to what fits this post</span></label>
            <div id="post-platform-grid" class="platform-grid">
              <div class="empty" style="grid-column:1/-1;font-size:12.5px;color:var(--ink-soft);padding:8px">Loading platforms…</div>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Schedule for later <span style="font-weight:400;color:var(--ink-soft);font-size:12px">— leave blank to post now; only AutoPost platforms can be scheduled</span></label>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
              <input type="datetime-local" id="post-schedule" style="max-width:260px">
              <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spSetSchedule('plus-1h')">+1 hour</button>
              <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spSetSchedule('tonight-6pm')">Tonight 6pm</button>
              <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spSetSchedule('tomorrow-9am')">Tomorrow 9am</button>
              <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spSetSchedule('tomorrow-6pm')">Tomorrow 6pm</button>
              <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="document.getElementById('post-schedule').value=''">Clear</button>
            </div>
          </div>
          <div class="actions">
            <button class="app-btn app-btn-green" id="btn-post-now" onclick="submitPost('post_now')">Post now</button>
            <button class="app-btn app-btn-outline" onclick="submitPost('schedule')">Schedule</button>
            <button class="app-btn app-btn-outline" onclick="submitPost('draft')">Save draft</button>
          </div>
        </div>
        <div class="card">
          <div class="card-title">Recent posts</div>
          <div id="post-queue"><div class="empty">No posts yet</div></div>
        </div>
      </div>

      <!-- EMAIL -->
      <div class="view" id="view-email">
        <div class="page-header"><h1>Email Campaigns</h1><p>Build an email list you own, and send campaigns that sell books</p></div>

        <!-- ZONE 0: List hygiene (prominent — protects deliverability & your sending account) -->
        <div class="card" style="margin-bottom:16px;border-left:4px solid var(--accent)">
          <div class="card-title">Clean your list before you send — protect your delivery and your sending account</div>
          <div style="font-size:14px;line-height:1.6">
            <p style="margin:0 0 12px">Sending to an old or unverified list is the fastest way to get into trouble. When too many messages bounce or get marked as spam, mailbox providers like Gmail and Outlook start routing your mail to the spam folder — and your sending service (Mailgun) can throttle or even <strong>suspend your account</strong> until the list is cleaned up. Getting reinstated is slow and painful. A validated list is the single biggest lever on whether your campaigns reach real readers.</p>
            <p style="margin:0 0 12px"><strong>What this app already does automatically:</strong> before every send it screens for bad or parked domains, common typos, and disposable/throwaway addresses, and it auto-suppresses any address that hard-bounces or files a spam complaint so you never email it twice. What it <em>can't</em> do is confirm a specific mailbox is still live — that needs a mail-server check our host blocks. That's the gap a validation service fills, and it's worth running <strong>before you import a large, old, purchased, or event-collected list</strong> you haven't emailed recently.</p>
            <p style="margin:0 0 8px"><strong>Services we recommend</strong> — a few dollars per thousand addresses, and you only do it occasionally:</p>
            <ul style="margin:0 0 12px;padding-left:20px">
              <li style="margin-bottom:4px"><a href="https://neverbounce.com/" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline">NeverBounce</a> — inexpensive, pay-as-you-go, and accurate. The one we'd start with.</li>
              <li style="margin-bottom:4px"><a href="https://www.zerobounce.net/" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline">ZeroBounce</a> — adds activity and abuse scoring; a bit pricier.</li>
              <li style="margin-bottom:4px"><a href="https://kickbox.com/" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline">Kickbox</a> — clean interface with free trial credits to test.</li>
              <li style="margin-bottom:4px"><a href="https://www.millionverifier.com/" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline">MillionVerifier</a> — the budget option for big lists.</li>
            </ul>
            <p style="margin:0"><strong>How:</strong> On the <strong>Contacts / Lists</strong> page, use <strong>Export CSV</strong>, run the file through one of the services above, then re-import only the addresses it marks deliverable.</p>
          </div>
        </div>

        <!-- ZONE 1: Education article -->
        <div class="card" style="margin-bottom:16px">
          <div id="email-primer-content"></div>
        </div>

        <!-- ZONE 2: Your email system -->
        <div class="card" style="margin-bottom:16px">
          <div class="card-title">Your email system</div>
          <div id="email-system-status">
            <div class="empty">Loading…</div>
          </div>
        </div>

        <!-- ZONE 3: Campaigns -->
        <div class="card" id="campaigns-card">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px">
            <div class="card-title" style="margin:0">Your campaigns</div>
            <button class="app-btn app-btn-green" id="new-campaign-btn" onclick="openComposer(null)">+ New campaign</button>
          </div>
          <div id="campaigns-wrap">
            <div class="empty">Loading…</div>
          </div>
        </div>
      </div>

      <!-- CAMPAIGN DETAIL (stats & analytics for a sent/sending campaign) -->
      <div class="view" id="view-campaign-detail">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:18px">
          <div>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="closeCampaignDetail()">← Back to campaigns</button>
          </div>
          <div id="detail-refresh-note" style="font-size:12px;color:var(--ink-soft)">Stats update in real time as Mailgun reports events.</div>
        </div>

        <div class="card" style="margin-bottom:14px">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap">
            <div style="flex:1;min-width:240px">
              <div id="detail-campaign-name" style="font-family:var(--font-serif);font-size:22px;margin-bottom:4px"></div>
              <div id="detail-campaign-subject" style="font-size:14px;color:var(--ink-soft)"></div>
              <div id="detail-campaign-meta" style="font-size:12px;color:var(--ink-soft);margin-top:8px"></div>
            </div>
            <div id="detail-status-badge"></div>
          </div>
        </div>

        <!-- Stat cards grid -->
        <div id="detail-stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));gap:10px;margin-bottom:14px"></div>

        <!-- Recipients table -->
        <div class="card" style="margin-bottom:14px">
          <div class="card-title">Recipients</div>
          <div id="detail-recipients-wrap" style="margin-top:10px">
            <div class="empty">Loading…</div>
          </div>
        </div>

        <!-- Events timeline -->
        <div class="card">
          <div class="card-title">Recent activity</div>
          <div id="detail-events-wrap" style="margin-top:10px">
            <div class="empty">Loading…</div>
          </div>
        </div>
      </div>

      <!-- CAMPAIGN SEND CONTROLS (recipients + test + schedule + send now) -->
      <div class="view" id="view-send">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:18px">
          <div style="display:flex;gap:8px">
            <button class="app-btn app-btn-outline app-btn-sm" onclick="backToComposer()">← Back to editor</button>
          </div>
          <div id="send-campaign-name" style="font-family:var(--font-serif);font-size:18px"></div>
        </div>

        <!-- Review card: subject + preheader preview -->
        <div class="card" style="margin-bottom:14px">
          <div class="card-title">Review</div>
          <div style="padding:8px 0">
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px">From</div>
            <div id="send-from" style="font-family:var(--font-serif)"></div>
          </div>
          <div style="padding:8px 0;border-top:1px solid var(--ink-faint)">
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px">Subject</div>
            <div id="send-subject" style="font-family:var(--font-serif);font-size:15px;font-weight:500"></div>
          </div>
          <div style="padding:8px 0;border-top:1px solid var(--ink-faint)">
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px">Preview text</div>
            <div id="send-preheader" style="color:var(--ink-soft);font-size:13px">—</div>
          </div>
        </div>

        <!-- Recipients card -->
        <div class="card" style="margin-bottom:14px">
          <div class="card-title">Who will receive this?</div>
          <div style="padding:8px 0;font-size:13px;color:var(--ink-soft)">Pick one or more lists. Unsubscribed contacts and any on the suppression list will be skipped automatically.</div>
          <div id="send-lists-wrap" style="margin-top:10px">
            <div class="empty">Loading your lists…</div>
          </div>
          <div id="send-recipient-summary" style="margin-top:12px;padding:10px 12px;background:#F0F7E8;border-left:3px solid var(--accent);border-radius:4px;font-size:13px;display:none"></div>
        </div>

        <!-- Test send card -->
        <div class="card" style="margin-bottom:14px">
          <div class="card-title">Send yourself a test first</div>
          <div style="padding:8px 0;font-size:13px;color:var(--ink-soft)">See what your campaign looks like before you send it to everyone.</div>
          <div style="display:flex;gap:8px;align-items:flex-start;margin-top:10px;flex-wrap:wrap">
            <input type="email" id="send-test-email" placeholder="your@email.com" style="flex:1;min-width:200px">
            <button class="app-btn app-btn-outline" id="send-test-btn" onclick="sendTestCampaign()">Send test</button>
          </div>
          <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Test emails are prefixed with "[TEST]" in the subject line.</div>
        </div>

        <!-- Send controls card -->
        <div class="card" style="margin-bottom:14px">
          <div class="card-title">When should this go out?</div>

          <div style="display:flex;gap:10px;align-items:center;margin:10px 0;flex-wrap:wrap">
            <label style="display:flex;gap:6px;align-items:center;cursor:pointer">
              <input type="radio" name="send-when" value="now" checked onchange="updateSendControls()">
              <span>Send now</span>
            </label>
            <label style="display:flex;gap:6px;align-items:center;cursor:pointer">
              <input type="radio" name="send-when" value="schedule" onchange="updateSendControls()">
              <span>Schedule for later</span>
            </label>
          </div>

          <div id="send-schedule-controls" style="display:none;padding:10px 0">
            <div class="field-group">
              <label class="field-label">Date &amp; time</label>
              <input type="datetime-local" id="send-schedule-when">
              <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Must be at least a few minutes in the future. Scheduled campaigns send within 15 minutes of the chosen time.</div>
            </div>
          </div>

          <div id="send-cta" style="margin-top:16px;display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap">
            <button class="app-btn app-btn-outline" onclick="backToComposer()">Back to editor</button>
            <button class="app-btn app-btn-green" id="send-cta-btn" onclick="executeSend()">Send now</button>
          </div>

          <div id="send-error" style="color:#B94141;font-size:13px;margin-top:10px"></div>
        </div>
      </div>
      <div class="view" id="view-composer">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:18px">
          <div>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="closeComposer()">← Back to campaigns</button>
          </div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <span id="composer-save-status" style="font-size:12px;color:var(--ink-soft)"></span>
            <button class="app-btn app-btn-outline" onclick="composerSaveNow()">Save draft</button>
            <button class="app-btn app-btn-green" id="composer-next-btn" onclick="composerGoToSend()" style="display:none">Next: choose recipients →</button>
          </div>
        </div>

        <div class="card">
          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Campaign name (internal, readers won't see this)</label>
              <input type="text" id="composer-name" placeholder="e.g. April 2026 newsletter" maxlength="200" oninput="composerOnChange()">
            </div>
            <div class="field-group">
              <label class="field-label">Book (for AI context)</label>
              <select id="composer-book-id">
                <option value="0">No specific book</option>
              </select>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Subject line *</label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" id="composer-subject" placeholder="Write your own, or click AI to generate ideas" maxlength="500" oninput="composerOnChange()" style="flex:1">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" id="composer-subject-ai-btn" onclick="aiEmailSubject()">✨ AI idea</button>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:4px">
              <span style="font-size:12px;color:var(--ink-soft)">Keep it short — 50 characters or less tends to get the best open rates.</span>
              <span id="composer-subject-counter" style="font-size:12px;color:var(--ink-soft);display:none"></span>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Preview text (optional)</label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" id="composer-preheader" placeholder="Short preview that appears next to the subject in some email clients" maxlength="500" oninput="composerOnChange()" style="flex:1">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" id="composer-preheader-ai-btn" onclick="aiEmailPreheader()">✨ AI</button>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Message *</label>
            <div id="composer-toolbar" style="display:flex;gap:6px;padding:6px 8px;background:#FAFAF7;border:1px solid var(--ink-faint);border-bottom:none;border-radius:4px 4px 0 0;flex-wrap:wrap;align-items:center">
              <button type="button" class="composer-tb-btn" onclick="composerFormat('bold')" title="Bold (Ctrl/Cmd+B)"><strong>B</strong></button>
              <button type="button" class="composer-tb-btn" onclick="composerFormat('italic')" title="Italic (Ctrl/Cmd+I)"><em>I</em></button>
              <button type="button" class="composer-tb-btn" onclick="composerInsertLink()" title="Insert link">🔗 Link</button>
              <span style="width:1px;height:18px;background:var(--ink-faint);margin:0 4px"></span>
              <select id="composer-merge-select" onchange="composerInsertMergeTag(this.value); this.selectedIndex=0" style="padding:4px 6px;font-size:12px;border:1px solid var(--ink-faint);border-radius:3px;background:#fff">
                <option value="">Insert merge tag…</option>
                <option value="{{first_name}}">First name</option>
                <option value="{{first_name|there}}">First name (or “there” if blank)</option>
                <option value="{{last_name}}">Last name</option>
                <option value="{{full_name}}">Full name</option>
                <option value="{{email}}">Email address</option>
              </select>
              <span style="width:1px;height:18px;background:var(--ink-faint);margin:0 4px"></span>
              <button type="button" class="composer-tb-btn" id="composer-body-ai-btn" onclick="aiEmailBody()" style="color:var(--accent);font-weight:500">✨ AI Draft</button>
              <span style="flex:1"></span>
              <button type="button" class="composer-tb-btn" id="composer-html-toggle" onclick="composerToggleHtml()" title="Switch between the visual editor and raw HTML source — paste HTML email code in HTML view">&lt;/&gt; HTML</button>
              <span style="font-size:11px;color:var(--ink-soft)">Plain-text-voice — sound like a writer, not a marketer</span>
            </div>
            <div id="composer-body" contenteditable="true" oninput="composerOnChange()" style="min-height:300px;padding:14px 16px;border:1px solid var(--ink-faint);border-radius:0 0 4px 4px;font-family:var(--font-serif);font-size:15px;line-height:1.6;background:#fff" data-placeholder="Start writing your email… or type a prompt and click AI Draft"></div>
            <textarea id="composer-body-source" oninput="composerOnChange()" spellcheck="false" style="display:none;width:100%;min-height:300px;padding:14px 16px;border:1px solid var(--ink-faint);border-radius:0 0 4px 4px;font-family:Menlo,Consolas,monospace;font-size:12.5px;line-height:1.5;background:#fbfaf7;box-sizing:border-box" placeholder="Paste or edit the raw HTML for this email. Click &lt;/&gt; HTML again to see it rendered."></textarea>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">
              An unsubscribe link and your physical address will be added automatically to every email (required by CAN-SPAM).
            </div>
          </div>
        </div>
      </div>

      <!-- AMAZON KDP HUB -->
      <div class="view" id="view-kdp">
        <div class="page-header">
          <h1>Amazon KDP</h1>
          <p>Tools for optimizing your KDP listings, planning Amazon promos, and tracking sales rank</p>
        </div>
        <div class="card" style="background:#FFF8EC;border-left:3px solid #FF9900;margin-bottom:20px">
          <div style="display:flex;align-items:flex-start;gap:12px">
            <div style="font-size:22px">🛈</div>
            <div style="font-size:13.5px;line-height:1.55">
              KDP doesn't offer a public API, so Elite Publishing can't post to Amazon directly. Instead, these tools generate copy-paste-ready content tuned to your book, and a planner so you don't lose track of what's running where. Use the <strong>Open KDP ↗</strong> buttons inside each tool to jump to the matching screen on KDP.
            </div>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px">
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('kdp-keywords')">
            <div style="font-size:28px;margin-bottom:8px">🔑</div>
            <div style="font-weight:500">Keywords + Categories</div>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Keywords + BISAC categories for KDP, IngramSpark, and other platforms</div>
          </div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('kdp-aplus')">
            <div style="font-size:28px;margin-bottom:8px">📐</div>
            <div style="font-weight:500">A+ Content</div>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">3 modules of rich product-page content</div>
          </div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('author-bio')">
            <div style="font-size:28px;margin-bottom:8px">👤</div>
            <div style="font-weight:500">Author Central Bio</div>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Cross-book bio for your Amazon Author Page</div>
          </div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('kdp-promos')">
            <div style="font-size:28px;margin-bottom:8px">📅</div>
            <div style="font-weight:500">Promo Planner</div>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Plan Free Days &amp; Countdown Deals for KDP Select</div>
          </div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('rank-logger')">
            <div style="font-size:28px;margin-bottom:8px">📈</div>
            <div style="font-weight:500">Sales Rank Logger</div>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Track your Amazon BSR over time on a chart</div>
          </div>
        </div>
        <div style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://kdp.amazon.com','_blank')">Open KDP ↗</button>
          <button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://authorcentral.amazon.com','_blank')">Open Author Central ↗</button>
          <button class="app-btn-help" onclick="showSetupHelp('amazon-kdp')" title="KDP setup walk-through"><span class="help-q">?</span>KDP setup help</button>
        </div>
      </div>

      <!-- PROMO -->
      <div class="view" id="view-promo">
        <div class="page-header"><h1>Promo Materials</h1><p>Create flyers, sell sheets, cover letters, KDP listings, and more</p></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px">
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('email')"><div style="font-size:28px;margin-bottom:8px">✉️</div><div style="font-weight:500">Promo email</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">Email campaign</div></div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('cover-letter')"><div style="font-size:28px;margin-bottom:8px">📄</div><div style="font-weight:500">Cover letter</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">For submissions</div></div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('gv-event')"><div style="font-size:28px;margin-bottom:8px">🗂️</div><div style="font-weight:500">Book flyer</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">Print-ready (8.5×11)</div></div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('sell-sheet')"><div style="font-size:28px;margin-bottom:8px">📊</div><div style="font-weight:500">Sell sheet</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">For buyers</div></div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('kdp-keywords')"><div style="font-size:28px;margin-bottom:8px">🔑</div><div style="font-weight:500">Keywords + Categories</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">KDP, IngramSpark, and more</div></div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('kdp-aplus')"><div style="font-size:28px;margin-bottom:8px">📐</div><div style="font-weight:500">A+ Content</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">For KDP product page</div></div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('author-bio')"><div style="font-size:28px;margin-bottom:8px">👤</div><div style="font-weight:500">Author Central bio</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">Cross-book Amazon bio</div></div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('kdp-promos')"><div style="font-size:28px;margin-bottom:8px">📅</div><div style="font-weight:500">KDP Promos</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">Free Days &amp; Countdown Deals</div></div>
          <div class="card" style="text-align:center;cursor:pointer;padding:24px 16px" onclick="navigate('rank-logger')"><div style="font-size:28px;margin-bottom:8px">📈</div><div style="font-weight:500">Sales rank logger</div><div style="font-size:12px;color:var(--ink-soft);margin-top:3px">Track Amazon BSR over time</div></div>
        </div>
      </div>

      <!-- COVER LETTER -->
      <div class="view" id="view-cover-letter">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('promo')">← Promo Materials</button>
        </div>
        <div class="page-header"><h1>Cover Letter</h1><p>AI-drafted letters for agents, publishers, podcasts, and media</p></div>

        <!-- Guide callout -->
        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">Before you generate</div>
          <p style="margin:0 0 10px 0">Cover letters change shape by recipient — an agent query is formal and manuscript-focused, a podcast pitch is conversational and centered on what you can talk about. Pick the right shape for the right reader.</p>
          <p style="margin:0">New to query letters, or unsure how a podcast pitch should look? <a href="#" onclick="navigate('education'); openLesson('cover-letters'); return false;">Read the full guide →</a></p>
        </div>

        <div class="card" id="cl-form-card">
          <div class="card-title">Generate a cover letter</div>

          <div id="cl-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:14px;font-weight:500"></div>

          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Book (optional)</label>
              <select id="cl-book-id" onchange="renderBookBanner('cl-book-id','cl-book-status')">
                <option value="0">No specific book</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Recipient type</label>
              <select id="cl-recipient-type" onchange="updateClPurposeOptions()">
                <option value="agent">Literary agent</option>
                <option value="publisher">Publisher or editor</option>
                <option value="podcast">Podcast host</option>
                <option value="media">Journalist or media contact</option>
                <option value="bookclub">Book club or reading group</option>
                <option value="other">Other professional contact</option>
              </select>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Recipient name or outlet (optional)</label>
            <input type="text" id="cl-recipient-name" placeholder="e.g. Jane Smith at Folio Literary, The Indie Author Podcast…">
          </div>

          <div class="field-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <label class="field-label" style="margin:0">Purpose</label>
              <button class="ai-btn" type="button" onclick="aiSuggestClPurpose()">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                AI suggest
              </button>
            </div>
            <select id="cl-purpose-select" onchange="toggleClPurposeOther()">
              <option value="">— choose a purpose —</option>
            </select>
            <textarea id="cl-purpose-other" rows="2" placeholder="Describe the purpose of this letter…" style="display:none;margin-top:6px"></textarea>
            <input type="hidden" id="cl-purpose">
          </div>

          <div class="field-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <label class="field-label" style="margin:0">Author credentials &amp; platform (optional)</label>
              <button class="ai-btn" type="button" onclick="aiDraftClCredits()">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                AI draft
              </button>
            </div>
            <textarea id="cl-author-credits" rows="2" placeholder="e.g. Previously published in The Sun magazine. 2,400 newsletter subscribers. Winner of the 2023 Ohioana Award…"></textarea>
          </div>

          <div class="actions">
            <button class="app-btn app-btn-green" id="cl-generate-btn" onclick="generateCoverLetter()">
              <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
              Generate cover letter
            </button>
          </div>
        </div>

        <div class="card" id="cl-output-card" style="display:none">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div class="card-title" style="margin:0">Your cover letter</div>
            <div style="display:flex;gap:8px">
              <button class="app-btn app-btn-outline app-btn-sm" onclick="copyCoverLetter()">Copy</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocPdf('cl-output', docTitle('cl-book-id','Cover Letter'))">Download PDF</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocWord('cl-output', docTitle('cl-book-id','Cover Letter'))">Download Word</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="generateCoverLetter()">Regenerate</button>
            </div>
          </div>
          <pre id="cl-output" style="white-space:pre-wrap;font-family:var(--font-serif);font-size:14px;line-height:1.7;margin:0;padding:0;border:none;background:none"></pre>
          <div id="cl-quota-note" style="margin-top:12px;font-size:12px;color:var(--ink-soft)"></div>
        </div>
      </div>

      <!-- SELL SHEET -->
      <div class="view" id="view-sell-sheet">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('promo')">← Promo Materials</button>
        </div>
        <div class="page-header"><h1>Sell Sheet</h1><p>One-page AI-drafted copy for booksellers, librarians, and reviewers</p></div>

        <!-- Guide callout -->
        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">Before you generate</div>
          <p style="margin:0 0 10px 0">A sell sheet is a one-page reference document buyers scan in seconds. The metadata block (ISBN, format, distribution, returnability) is what makes a stocking decision possible — fill in the book setup form completely before generating.</p>
          <p style="margin:0">Never seen a sell sheet before? <a href="#" onclick="navigate('education'); openLesson('sell-sheets'); return false;">Read the full guide and see a complete example →</a></p>
        </div>

        <div class="card" id="sell-sheet-form-card">
          <div class="card-title">Generate a sell sheet</div>

          <div id="ss-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:14px;font-weight:500"></div>

          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Book (optional — adds book details to the AI context)</label>
              <select id="ss-book-id" onchange="ssBookHint()">
                <option value="0">No specific book</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Target audience</label>
              <select id="ss-audience">
                <option value="booksellers">Independent booksellers &amp; retail buyers</option>
                <option value="librarians">Librarians &amp; library acquisitions</option>
                <option value="reviewers">Book reviewers &amp; bloggers</option>
                <option value="media">Journalists &amp; media contacts</option>
              </select>
            </div>
          </div>

          <div class="field-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <label class="field-label" style="margin:0">Key selling points — what makes this book stand out?</label>
              <button class="ai-btn" type="button" onclick="aiDraftKeyPoints()">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                AI suggest
              </button>
            </div>
            <textarea id="ss-key-points" rows="4" placeholder="e.g. First book in a 3-part series, strong regional interest in Ohio, author has 600-person email list, 47 five-star reviews on Amazon, featured in The Columbus Dispatch…"></textarea>
          </div>

          <div class="field-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <label class="field-label" style="margin:0">Comparable titles (optional)</label>
              <button class="ai-btn" type="button" onclick="aiDraftCompTitles()">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                AI suggest
              </button>
            </div>
            <input type="text" id="ss-comp-titles" placeholder="e.g. Lessons in Chemistry, The Midnight Library — or let AI suggest">
          </div>

          <div class="field-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <label class="field-label" style="margin:0">About the author</label>
              <button class="ai-btn" type="button" onclick="aiDraftSsAuthorBio()">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                AI draft
              </button>
            </div>
            <textarea id="ss-author-bio" rows="3" placeholder="Write a short author bio here, or let AI draft one from your profile…"></textarea>
          </div>

          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Contact name</label>
              <input type="text" id="ss-contact-name" placeholder="Jane Smith">
            </div>
            <div class="field-group">
              <label class="field-label">Contact email</label>
              <input type="email" id="ss-contact-email" placeholder="jane@example.com">
            </div>
          </div>

          <div class="actions">
            <button class="app-btn app-btn-green" id="ss-generate-btn" onclick="generateSellSheet()">
              <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
              Generate sell sheet
            </button>
          </div>
        </div>

        <div class="card" id="ss-output-card" style="display:none">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div class="card-title" style="margin:0">Your sell sheet</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <button class="app-btn app-btn-outline app-btn-sm" onclick="copySellSheet()">Copy</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocPdf('ss-output', ssDocTitle())">Download PDF</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocWord('ss-output', ssDocTitle())">Download Word</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="generateSellSheet()">Regenerate</button>
            </div>
          </div>
          <pre id="ss-output" style="white-space:pre-wrap;font-family:var(--font-serif);font-size:14px;line-height:1.7;margin:0;padding:0;border:none;background:none"></pre>
          <div id="ss-quota-note" style="margin-top:12px;font-size:12px;color:var(--ink-soft)"></div>
        </div>
      </div>

      <!-- KDP KEYWORDS + CATEGORIES -->
      <div class="view" id="view-kdp-keywords">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('promo')">← Promo Materials</button>
        </div>
        <div class="page-header"><h1>Book Keywords &amp; Categories</h1><p>AI-suggested keywords + BISAC categories for KDP, Amazon Seller, IngramSpark, B&amp;N Press, Kobo, Apple Books, and your own store</p></div>

        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">Why this matters</div>
          <p style="margin:0">Almost every place you list a book — KDP, Amazon Seller, IngramSpark, Barnes &amp; Noble Press, Kobo Writing Life, Apple Books, even your own Shopify or WordPress store — asks for keywords and one or two BISAC categories. Pick the wrong ones and your book is invisible; pick generic ones and you're drowning in competition. The AI below generates <strong>12 keyword candidates and 4 BISAC categories</strong>, ranked strongest-first, plus a quick guide for which subset to use on each platform (KDP wants 7+2, IngramSpark 5–7+2–3, etc.). <strong>Fill in your book's description, genre, audience, and comparable titles</strong> for AI suggestions that actually match the book.</p>
        </div>

        <div class="card" id="kw-form-card">
          <div class="card-title">Generate suggestions</div>
          <div id="kw-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:14px;font-weight:500"></div>
          <div class="field-group">
            <label class="field-label">Book <span style="color:var(--danger)">*</span></label>
            <select id="kw-book-id" onchange="renderBookBanner('kw-book-id','kw-book-status')">
              <option value="0">— pick a book —</option>
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Extra guidance (optional)</label>
            <textarea id="kw-extra-hint" rows="2" placeholder="e.g. Lean toward female-protagonist tropes. Or: focus on the small-town setting more than the mystery angle."></textarea>
          </div>
          <div class="actions">
            <button class="app-btn app-btn-green" id="kw-generate-btn" onclick="generateKdpKeywords()">
              <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
              Generate keywords + categories
            </button>
          </div>
        </div>

        <div class="card" id="kw-output-card" style="display:none">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div class="card-title" style="margin:0">Suggestions</div>
            <div style="display:flex;gap:8px">
              <button class="app-btn app-btn-outline app-btn-sm" onclick="copyKdpKeywords()">Copy</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocPdf('kw-output', docTitle('kw-book-id','KDP Keywords &amp; Categories'))">Download PDF</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocWord('kw-output', docTitle('kw-book-id','KDP Keywords &amp; Categories'))">Download Word</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="generateKdpKeywords()">Regenerate</button>
            </div>
          </div>
          <pre id="kw-output" style="white-space:pre-wrap;font-family:var(--font-body);font-size:14px;line-height:1.7;margin:0;padding:0;border:none;background:none"></pre>
          <div id="kw-quota-note" style="margin-top:12px;font-size:12px;color:var(--ink-soft)"></div>
        </div>
      </div>

      <!-- eBOOK CONVERTER -->
      <div class="view" id="view-ebook-convert">
        <!-- ⚠ STANDALONE ONLY (elitepublishing.co/ebook-maker/). Hidden inside
             the app, where the visitor already knows who we are. -->
        <div class="ah-only-standalone" id="eb-standalone-hero">
          <div class="card" style="background:linear-gradient(135deg,#336699,#1F4463);color:#fff;border:0">
            <div style="font-size:13px;letter-spacing:.09em;text-transform:uppercase;opacity:.85;margin-bottom:9px">
              No account needed
            </div>
            <h1 style="margin:0 0 12px;font-size:clamp(26px,4.4vw,38px);line-height:1.18;color:#fff">
              Turn your book into an eBook —<br><em style="color:#F5C96B">even if all you have is a scan</em>
            </h1>
            <p style="margin:0 0 14px;font-size:17px;line-height:1.65;opacity:.95;max-width:44em">
              Amazon, Apple Books and Kobo won't accept a PDF. Upload what you've got —
              a Word file, the print PDF, or a scan of the printed pages — and get back
              the EPUB the stores ask for.
            </p>
            <p style="margin:0;font-size:16px;line-height:1.65;opacity:.95">
              <strong>You see the whole finished book before you pay a penny.</strong>
              One payment, no subscription, nothing to cancel.
            </p>
          </div>
        </div>

        <div class="page-header"><h1>eBook Maker</h1><p>Turn your manuscript or picture book into a clean, store-ready EPUB — the file every online store needs. Make it here in minutes, then sell it on Amazon, Apple Books, Kobo, and anywhere else.</p></div>

        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">How this works</div>
          <div id="eb-howto-fxl" style="display:none">
            <p style="margin:0">A picture book can’t be converted like a novel. The words are part of the artwork, so if the text reflows, the book falls apart. Instead we build a <strong>fixed-layout EPUB</strong> — the same format Amazon, Apple and Kobo use for children’s books. Upload the <strong>print-ready interior PDF</strong> you already sent to your printer, and we lock every page exactly as you designed it: artwork and words together, nothing moved, nothing reflowed. Facing pages are paired so tablets show real two-page spreads.</p>
            <p style="margin:12px 0 0 0;font-size:14px;color:var(--ink-soft);line-height:1.6"><strong>One thing to know:</strong> your PDF needs the text <strong>already placed on the pages</strong> — that’s how a finished picture book comes off the press, so if it’s print-ready, you’re set. A fixed-layout book keeps its print proportions, which means it reads best on a tablet or phone rather than a small e-ink Kindle.</p>
          </div>
          <div id="eb-howto-pdftext" style="display:none">
            <p style="margin:0">Plenty of authors no longer have the Word file their book was written in — all that’s left is a PDF. That PDF is useless to the stores: <strong>Amazon, Apple Books and Kobo won’t accept a PDF as an eBook.</strong> So we read the words back out of it — rejoining lines that were broken to fit the page, dropping repeating headers and page numbers, and finding your chapters.</p>
            <p style="margin:12px 0 0 0;font-size:14px;color:var(--ink-soft);line-height:1.6"><strong>You get to check it first.</strong> Recovering text from a printed page isn’t perfect, so we show you the whole book and highlight every spot we weren’t sure about. Fix what you like, then export — <strong>nothing is charged until you do</strong>. Poetry, plays and books with tables are the tricky ones: they usually still work, but they’re worth a careful look. It only takes a few minutes to find out.</p>
            <p style="margin:12px 0 0 0;font-size:14px;color:var(--ink-soft);line-height:1.6">This works best with a PDF that has real text in it — the kind exported from Word or InDesign. If your book was <em>scanned</em> from paper, the pages are pictures rather than words, and we read those a different way. You don’t have to know which one you have: we check the file and tell you.</p>
          </div>
          <div id="eb-howto-ocr" style="display:none">
            <p style="margin:0">If the only copy of your book left is a <strong>scan of the printed pages</strong>, you have almost certainly been told it can’t be done. It can. (A real scan, from a scanner — phone photographs of pages don’t work, and won’t give you a book you would want to sell.) We read the words off the pages themselves, then rebuild them into a real eBook — rejoining lines that were broken to fit the page, dropping repeating headers and page numbers, and finding your chapters.</p>
            <p style="margin:12px 0 0 0;font-size:14px;color:var(--ink-soft);line-height:1.6"><strong>You get to check it first.</strong> Reading words off a scanned page is very good now, but it isn’t perfect — so we show you the whole book and mark every word we weren’t certain of, ready for you to correct. <strong>Nothing is charged until you export.</strong></p>
            <p style="margin:12px 0 0 0;font-size:14px;color:var(--ink-soft);line-height:1.6">Two things worth knowing: reading a scan takes about half a second a page — roughly a minute for a 130-page book, and you’ll see the pages counted as they go — and <strong>italics and bold can’t be recovered</strong> from a picture of a page — those you add back in your Word file. If it turns out your PDF <em>does</em> have text in it after all, we’ll spot that and use the faster, more accurate route instead.</p>
          </div>
          <div id="eb-howto-reflow">
          <p style="margin:0">Most authors don’t know how to turn a Word document into the eBook files that stores require — and a messy document gets rejected. Here’s the fix: upload your manuscript and we <strong>check its structure first</strong> (headings, chapter breaks, stray formatting) and tell you in plain English what to fix before you publish. Then we build a clean <strong>EPUB</strong> you can list anywhere. <strong>We never change a word of your writing</strong> — only the file format. Best results come from a Word (.docx), OpenDocument (.odt), rich-text (.rtf), or plain-text (.txt) file.</p>
          <p style="margin:12px 0 0 0;font-size:13.5px;color:var(--ink-soft);line-height:1.6"><strong>A note on results:</strong> the converter automatically structures your chapters, tidies formatting, and builds a linked table of contents — but it can’t rescue every file. Manuscripts with heavy manual formatting, or with no chapter styling at all, may convert with some chapters missing or need a cleanup first. If a file doesn’t come out right, re-saving it as a fresh <strong>.docx</strong> and trying again usually fixes it. Still stuck? Contact us and we’ll take a direct look.</p>
          </div>
        </div>

        <div class="card" id="eb-upload-card">
          <div class="card-title" id="eb-upload-title">1 · Upload your manuscript</div>
          <div class="field-group">
            <label class="field-label">What kind of book is this? <span style="color:var(--danger)">*</span></label>
            <div class="eb-modes">
              <button type="button" class="eb-mode active" id="eb-mode-reflow" onclick="ebookSetKind('reflow')">
                <span class="eb-price-badge">One-time<strong>$9.95</strong></span>
                <span class="eb-mode-t">Novel or text book — Word file</span>
                <span class="eb-mode-d">Fiction, non-fiction, memoir, poetry — writing that should reflow and resize on any device. Upload a Word file.</span>
              </button>
              <button type="button" class="eb-mode" id="eb-mode-fxl" onclick="ebookSetKind('fxl')">
                <span class="eb-price-badge">One-time<strong>$9.95</strong></span>
                <span class="eb-mode-t">Picture book or children’s book</span>
                <span class="eb-mode-d">Artwork with the words already on the page. Every page stays exactly as you designed it. Upload your print-ready PDF.</span>
              </button>
              <button type="button" class="eb-mode" id="eb-mode-pdftext" onclick="ebookSetKind('pdftext')">
                <span class="eb-price-badge">One-time<strong>$9.95</strong></span>
                <span class="eb-mode-t">Novel or text book — PDF only</span>
                <span class="eb-mode-d">Same kind of book, but the Word file is long gone. Upload the PDF instead — whatever was exported from Word or InDesign, or sent to your printer — and we’ll recover the text, then let you fix anything odd before exporting.</span>
              </button>
              <button type="button" class="eb-mode" id="eb-mode-ocr" onclick="ebookSetKind('ocr')">
                <span class="eb-price-badge">One-time<strong>$14.95</strong></span>
                <span class="eb-mode-t">Scanned book — no digital file at all</span>
                <span class="eb-mode-d">Your book only exists on paper and was scanned to a PDF, so the pages are pictures rather than text. We read the words off the pages and rebuild the book. <strong>You also get your whole book back as an editable Word file</strong> — for revisions, or to take to a printer. Use a proper flatbed or sheet-feed scan — <strong>photos taken with a phone don’t work</strong>, the pages come out curved and unevenly lit. Not sure which you have? Pick either PDF option — we check the file and use the right one.</span>
              </button>
            </div>
          </div>
          <div class="field-group" id="eb-book-field">
            <label class="field-label">Link to a book (optional)</label>
            <select id="eb-book-id">
              <option value="0">— not linked to a book (optional) —</option>
            </select>
          </div>
          <!-- ⚠ ASK, DO NOT INFER. The converter used to name the book after the
               uploaded FILE — a real conversion shipped as "SwingInterior4 4
               23Trimmed" while "The Ghost in the Swing" sat on page one. The
               author knows their own title; no heuristic beats being told.
               Left blank, we still read it off the title page. -->
          <div class="field-group">
            <label class="field-label" for="eb-title">Book title (optional)</label>
            <input type="text" id="eb-title" placeholder="The Ghost in the Swing" autocomplete="off" maxlength="180">
            <p style="margin:6px 0 0 0;font-size:14px;color:var(--ink-soft);line-height:1.65">
              This becomes the eBook's title in every store and on every reader's shelf.
              Leave it blank and we'll take it from your title page.
            </p>
          </div>
          <div class="field-group">
            <label class="field-label" for="eb-author">Author name (optional)</label>
            <input type="text" id="eb-author" placeholder="Janet Patton Smith" autocomplete="off" maxlength="180">
          </div>
          <div class="field-group">
            <label class="field-label" for="eb-isbn">eBook ISBN (optional)</label>
            <input type="text" id="eb-isbn" placeholder="978-0-306-40615-7" autocomplete="off" spellcheck="false">
            <p style="margin:6px 0 0 0;font-size:14px;color:var(--ink-soft);line-height:1.65">
              If you’ve registered an ISBN <strong>for the eBook edition</strong>, put it here and we’ll
              stamp it into the file as the book’s identity. Leave it blank if you haven’t —
              plenty of authors don’t need one (Amazon issues its own ASIN, and Draft2Digital
              hands out a free ISBN).
              <br>
              <strong>Don’t use your paperback’s ISBN.</strong> That one identifies the print edition;
              putting it in an eBook tells every store the two are the same edition, which causes
              more trouble than having no ISBN at all. ISBN-10 is fine — we convert it.
            </p>
          </div>
          <div class="field-group">
            <label class="field-label" id="eb-file-label">Manuscript file <span style="color:var(--danger)">*</span></label>
            <div class="eb-drop">
              <input type="file" id="eb-file" accept=".docx,.doc,.odt,.rtf,.txt" onchange="ebookFilePicked()">
              <div id="eb-file-name" style="margin-top:8px;font-size:13px;color:var(--ink-soft)"></div>
              <div id="eb-file-hint" style="margin-top:6px;font-size:13px;color:var(--ink-soft)">Accepted: .docx · .doc · .odt · .rtf · .txt — up to 25 MB</div>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Cover image (optional)</label>
            <input type="file" id="eb-cover" accept="image/jpeg,image/png,image/webp,application/pdf,.pdf">
            <div id="eb-cover-hint" style="margin-top:6px;font-size:13px;color:var(--ink-soft)">If you linked a book above, we use its cover automatically — so the eBook opens on the cover. Upload one here to override it, or if this manuscript isn't linked to a book. JPG, PNG, WebP or PDF, up to 100 MB — a full print cover (back, spine and front on one wide page) is fine, we take the front from it.</div>
          </div>
          <div id="eb-cost-line" style="margin:2px 0 12px;font-size:15px;color:var(--ink)"></div>
          <div class="actions">
            <button class="app-btn app-btn-green" id="eb-upload-btn" onclick="ebookUpload()" disabled>Check my manuscript</button>
          </div>
        </div>

        <!-- ⚠ TOP LEVEL, not inside the results card. It lived in there and so
             could never appear: that card is display:none until there IS a
             result, which is precisely when the progress bar is no longer
             wanted. -->
        <div class="card" id="eb-progress-card" style="display:none">
          <div id="eb-progress" style="background:var(--accent-lt);border-left:3px solid var(--accent);
               border-radius:0 10px 10px 0;padding:14px 16px;margin-bottom:14px">
            <div id="eb-progress-text" style="font-size:15.5px;font-weight:600;margin-bottom:9px">Preparing your pages…</div>
            <div style="height:10px;background:rgba(0,0,0,.09);border-radius:6px;overflow:hidden">
              <div id="eb-progress-fill" style="height:100%;width:0%;background:var(--accent);
                   border-radius:6px;transition:width .5s ease"></div>
            </div>
            <p style="margin:9px 0 0 0;font-size:13.5px;color:var(--ink-soft)">
              Reading a scan takes about half a second a page — roughly a minute for a 130-page book.
              You can leave this tab open and come back to it.
            </p>
          </div>

        </div>

        <!-- Shown when a file uploaded as a text PDF turns out to be a scan. The
             author is told what they actually have and offered the reader that
             suits it, rather than being handed a dead end. -->
        <div class="card" id="eb-ocr-offer-card" style="display:none">
          <div class="card-title">2 · This one is a scan</div>
          <p id="eb-ocr-offer-msg" style="margin:0 0 12px 0;font-size:15.5px;line-height:1.7"></p>
          <p style="margin:0 0 14px 0;font-size:14px;color:var(--ink-soft);line-height:1.7">
            We can read the words straight off the scanned pages and rebuild your book from
            them — the same review step follows, and <strong>nothing is charged until you
            export</strong>. It takes about half a second a page — roughly a minute for a 130-page book — and
            <strong>italics and bold can’t be recovered</strong> from a picture of a page.
          </p>
          <div class="actions">
            <button class="app-btn app-btn-green" id="eb-ocr-run-btn" onclick="ebookRunOcr()">Read my scanned book</button>
          </div>
        </div>

        <div class="card" id="eb-preflight-card" style="display:none">
          <div class="card-title">2 · Pre-flight check</div>
          <div id="eb-digest-chips" style="margin-bottom:14px"></div>
          <pre id="eb-preflight-report"></pre>
          <p id="eb-preflight-unavailable" style="display:none;margin:0;color:var(--ink-soft);font-size:14px">The manuscript is uploaded and ready to convert. (The AI pre-flight check wasn’t available just now — you can still convert below.)</p>
          <div class="actions" style="margin-top:18px">
            <button class="app-btn app-btn-green" id="eb-convert-btn" onclick="ebookConvert()">Convert to EPUB</button>
          </div>
          <div id="eb-convert-note" style="margin-top:8px;font-size:12.5px;color:var(--ink-soft)"></div>
        </div>

        <!-- ============================================================
             THE EDIT STAGE (print-PDF path)
             Recovering text from a printed page is never perfect, so rather
             than hide that, every uncertain spot is flagged and the author
             fixes it here — before anything is exported or paid for. This is
             the preview AND the correction tool; the finished file is the
             paid step.
             ============================================================ -->
        <div class="card" id="eb-editor-card" style="display:none">
          <div class="card-title">2 · Read it through and fix anything odd</div>
          <p id="eb-ed-intro" style="margin:0 0 12px 0;font-size:15px;line-height:1.65;color:var(--ink-soft)">
            Here is your whole book, read back out of the PDF. Everything we
            weren’t sure about is highlighted — usually a word that ran together
            where the printed line broke.
          </p>
          <ol style="margin:0 0 16px 0;padding-left:22px;font-size:15px;line-height:1.85;color:var(--ink)">
            <li>Use <strong>Next</strong> to jump to each highlighted spot — the word is selected, so you can type straight over it.</li>
            <li>Read through the rest if you like. Click any paragraph to edit it, or change a heading with the menu beside it.</li>
            <li>Nothing is kept until you press <strong>Save changes</strong> — the ↺ button puts a paragraph back.</li>
            <li>Read and correct as much as you like. <strong>Copying the text is part of the file you unlock</strong> — once it's yours, it's yours.</li>
            <li>When it looks right, press <strong>Build my EPUB</strong>. <strong>Nothing is charged until you export.</strong></li>
          </ol>

          <div id="eb-ed-stats" style="margin-bottom:12px"></div>
          <div id="eb-ed-warnings" style="margin-bottom:14px"></div>

          <div id="eb-ed-flagbar" style="display:none">
            <div>
              <strong id="eb-ed-flagcount">0 spots</strong>
              <span style="color:var(--ink-soft)"> to check</span>
            </div>
            <div>
              <button type="button" class="app-btn app-btn-sm" onclick="ebookJumpFlag(-1)">‹ Previous</button>
              <button type="button" class="app-btn app-btn-sm" onclick="ebookJumpFlag(1)">Next ›</button>
            </div>
          </div>

          <!-- ⚠ These live ABOVE the book and STICK there. They used to sit at the
               very bottom, which meant scrolling an entire book to find the
               button that finishes it — and a reader who stops halfway had no
               way to act without scrolling past everything they had not read.
               Sticky beats simply moving them: after correcting something on
               page 200 the buttons are still right there. -->
          <div id="eb-ed-bar">
            <div class="eb-ed-bar-actions">
              <button class="app-btn" id="eb-ed-save" onclick="ebookSaveDoc()" disabled>Save changes</button>
              <button class="app-btn app-btn-green" id="eb-ed-build" onclick="ebookBuildDoc()">Build my EPUB</button>
              <button type="button" class="app-btn app-btn-sm" id="eb-ed-fullscreen" onclick="ebookToggleReading()">Read full screen</button>
            </div>
            <div id="eb-ed-note" style="font-size:13px;color:var(--ink-soft)"></div>
          </div>

          <div id="eb-ed-blocks"></div>

          <div id="eb-ed-pager" style="display:none">
            <button type="button" class="app-btn app-btn-sm" id="eb-ed-next" onclick="ebookDocPage(1)">Next pages ›</button>
            <button type="button" class="app-btn app-btn-sm" id="eb-ed-prev" onclick="ebookDocPage(-1)">‹ Previous pages</button>
            <span id="eb-ed-range" style="font-size:14px;color:var(--ink-soft)"></span>
          </div>

        </div>

        <div class="card" id="eb-result-card" style="display:none">
          <div class="card-title">3 · Your eBook files</div>
          <div id="eb-converting" style="display:none;color:var(--ink-soft);font-size:15px">Formatting and converting your manuscript… this usually takes under a minute.</div>
          <div id="eb-error" style="display:none;background:#FEF2F2;border-left:3px solid var(--danger);border-radius:6px;padding:14px 16px;font-size:14.5px;line-height:1.6;color:#7f1d1d"></div>
          <div id="eb-format-note" style="display:none;background:var(--accent-lt);border-left:3px solid var(--accent);border-radius:6px;padding:12px 14px;margin-bottom:16px;font-size:14px;line-height:1.55"></div>
          <div id="eb-partial" style="display:none;background:#FFFBEB;border-left:3px solid #F59E0B;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13.5px;line-height:1.55;color:#78350f"></div>
          <div id="eb-downloads" style="display:none">
          <!-- ⚠ THE HINGE OF THE WHOLE MODEL.
               The author reads the ACTUAL finished file here — real pages, real
               styling, real cover — and only then decides to pay. Seeing the
               recovered text is not the same promise: text says the words are
               right, this says the book is a book. Nobody buys blind, so nobody
               asks for their money back. -->
          <div id="eb-preview-block" style="margin:4px 0 16px">
            <div style="font-size:16.5px;font-weight:700;margin-bottom:8px">
              Check your eBook before you buy it
            </div>
            <p style="margin:0 0 10px;font-size:15px;line-height:1.65;color:var(--ink-soft)">
              This is the finished file — the real pages, your cover, your chapters,
              exactly as a reader will see it. Read as much of it as you like.
            </p>
            <button type="button" class="app-btn app-btn-green" id="eb-preview-btn" onclick="ebookOpenPreview()">Read your eBook</button>
            <div class="card" id="eb-preview-card" style="display:none;margin-top:12px;padding:14px">
              <div class="card-title" style="margin-bottom:10px">Your finished eBook</div>
              <!-- ⚠ THE UNLOCK CTA LIVES WITH THE READER, NOT UNDER IT.
                   The reader is a 70vh frame that scrolls internally, so an
                   author reading their book could never see the paywall below
                   it — the moment they decide they want the file is somewhere
                   in the middle of reading, not at the end. Sticky, so it
                   follows them. Hidden the instant they are entitled. -->
              <div id="eb-preview-body"></div>
              <p style="margin:10px 0 0;font-size:13.5px;color:var(--ink-soft)">
                This is the real file, exactly as a reader will see it. Downloading it is
                the paid step.
              </p>
            </div>
          </div>

          <!-- The paywall. The book is finished and fully reviewable above; this
               is the step that hands over the file. Subscribers never see it. -->
            <div style="display:inline-flex;align-items:center;gap:7px;background:#EAF7EE;border:1px solid #BFE3CB;color:#1E7A3A;border-radius:999px;padding:5px 14px;font-size:13.5px;font-weight:700;margin-bottom:12px">✓ Store-ready — passes EPUBCheck validation</div>
            <p style="margin:0 0 14px 0;font-size:14px;color:var(--ink-soft)">Ready to download and list anywhere. Your text is unchanged — only the format.</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
              <button type="button" class="app-btn app-btn-green eb-dl" id="eb-dl-epub" onclick="ebookDownload('epub')" style="display:none;align-items:center">Download EPUB</button>
              <button type="button" class="app-btn app-btn-outline eb-dl" id="eb-dl-word" onclick="ebookDownload('docx')" style="display:none;align-items:center">Download Word file</button>
              <button type="button" class="app-btn app-btn-outline eb-dl" id="eb-dl-pdf" onclick="ebookDownload('pdf')" style="display:none;align-items:center">Download PDF</button>
            </div>

<!-- ⚠ AFTER payment only, and only for a buyer with no account.
               Somebody who has just bought a book is the best moment to
               mention the subscription; before they pay it is just noise. -->

            <div id="eb-toc-block" style="margin-top:22px;padding-top:18px;border-top:1px solid var(--border);display:none">
              <div class="card-title" style="font-size:16px;margin-bottom:6px">Table of contents</div>
              <p style="margin:0 0 12px 0;font-size:14px;color:var(--ink-soft);line-height:1.6">We built this from your book automatically. It's usually spot-on — but if anything looks off, you can fix it here: <strong>uncheck</strong> entries that shouldn't be listed, <strong>rename</strong> any entry, then save. Your book's text isn't touched — only the contents list.</p>
              <button class="app-btn app-btn-outline" id="eb-toc-toggle" onclick="ebookTocOpen()">✏️ Edit table of contents</button>
              <div id="eb-toc-editor" style="display:none;margin-top:14px">
                <div id="eb-toc-list" style="max-height:360px;overflow-y:auto;padding-right:6px;margin-bottom:12px"></div>
                <p style="margin:0 0 12px 0;font-size:13.5px;color:var(--ink-soft);line-height:1.55">Make your changes above, then click <strong>Save &amp; update</strong> — your updated EPUB will be ready to download right here (no need to convert again).</p>
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                  <button class="app-btn app-btn-green" id="eb-toc-rebuild-btn" onclick="ebookTocRebuild()">Save &amp; update table of contents</button>
                  <button class="app-btn app-btn-outline app-btn-sm" onclick="ebookTocReload()" title="Discard changes and reload the current table of contents">Reset</button>
                  <span id="eb-toc-status" style="font-size:13.5px;line-height:1.5"></span>
                </div>
                <div id="eb-toc-done" style="display:none;margin-top:14px;padding:13px 15px;background:var(--accent-lt);border-left:3px solid var(--accent);border-radius:6px">
                  <p id="eb-toc-done-msg" style="margin:0 0 11px 0;font-size:14px;line-height:1.5"></p>
                  <a class="app-btn app-btn-green" id="eb-toc-dl" href="#" download="" style="display:inline-flex;align-items:center">⬇ Download updated EPUB</a>
                </div>
              </div>
            </div>

            <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--border)">
              <div class="card-title" style="font-size:16px;margin-bottom:6px">Preview your eBook before you publish</div>
              <p style="margin:0 0 12px 0;font-size:14px;color:var(--ink-soft);line-height:1.6">Open your EPUB in a free reader to see it exactly the way your readers will. Any of these work well:</p>
              <ul style="margin:0 0 14px 0;padding-left:0;list-style:none;font-size:14px;line-height:1.6">
                <li style="margin-bottom:9px"><a href="https://thorium.edrlab.org/" target="_blank" rel="noopener" style="font-weight:700">Thorium Reader</a> <span style="color:var(--ink-soft)">— free, for Windows, Mac and Linux. Our top pick: it shows your file the most accurately, including the table of contents.</span></li>
                <li style="margin-bottom:9px"><a href="https://calibre-ebook.com/" target="_blank" rel="noopener" style="font-weight:700">Calibre</a> <span style="color:var(--ink-soft)">— free; a full library manager with a built-in viewer.</span></li>
                <li style="margin-bottom:9px"><strong>Apple Books</strong> <span style="color:var(--ink-soft)">— already on your Mac, iPhone and iPad. Drag your EPUB onto it to open.</span></li>
                <li style="margin-bottom:9px"><a href="https://www.adobe.com/solutions/ebook/digital-editions/download.html" target="_blank" rel="noopener" style="font-weight:700">Adobe Digital Editions</a> <span style="color:var(--ink-soft)">— free, but an older app we no longer recommend for checking your work. See the note below.</span></li>
              </ul>
              <p style="margin:0 0 12px 0;background:#fff8e6;border-left:3px solid #e0a800;border-radius:6px;padding:10px 14px;font-size:13.5px;line-height:1.55"><strong>A word on Adobe Digital Editions.</strong> It hasn’t kept up with the current eBook standard, and it often fails to show things that are correctly in your file — chapter page breaks, cover images, and the table of contents. If something looks wrong there, <strong>check it in Thorium or Apple Books before assuming your book has a problem</strong>. Nine times out of ten the file is fine and the reader isn’t showing it.</p>
              <p style="margin:0;background:var(--accent-lt);border-left:3px solid var(--accent);border-radius:6px;padding:10px 14px;font-size:13.5px;line-height:1.55"><strong>Using Apple Books?</strong> Your table of contents is there — tap the <strong>Contents</strong> button at the top of the reader to see it. Unlike some apps, Books doesn’t show it in a side panel, so it can look missing when it isn’t.</p>
            </div>

            <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--border)">
              <div class="card-title" style="font-size:16px;margin-bottom:6px">Where to sell your eBook</div>
              <p style="margin:0 0 16px 0;font-size:14px;color:var(--ink-soft);line-height:1.6">Your EPUB is yours to sell anywhere — you’re not locked into one store. Most authors start with one or two of these and add more over time.</p>

              <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--ink-soft);margin:2px 0 8px">Major stores — list it yourself</div>
              <ul style="margin:0 0 16px 0;padding-left:0;list-style:none;font-size:14px;line-height:1.6">
                <li style="margin-bottom:9px"><a href="https://kdp.amazon.com/" target="_blank" rel="noopener" style="font-weight:700">Amazon KDP</a> <span style="color:var(--ink-soft)">— the largest ebook marketplace and the widest reach. Up to 70% royalties in many markets. (KDP Select’s higher rate asks for temporary Amazon exclusivity.)</span></li>
                <li style="margin-bottom:9px"><a href="https://authors.apple.com/" target="_blank" rel="noopener" style="font-weight:700">Apple Books</a> <span style="color:var(--ink-soft)">— an excellent worldwide audience of premium readers, straight to every iPhone and iPad.</span></li>
                <li style="margin-bottom:9px"><a href="https://www.kobowritinglife.com/" target="_blank" rel="noopener" style="font-weight:700">Kobo Writing Life</a> <span style="color:var(--ink-soft)">— strong international sales, especially in Canada, Europe, and libraries.</span></li>
                <li style="margin-bottom:9px"><a href="https://press.barnesandnoble.com/" target="_blank" rel="noopener" style="font-weight:700">Barnes &amp; Noble Press</a> <span style="color:var(--ink-soft)">— a solid U.S. alternative to Amazon.</span></li>
                <li style="margin-bottom:9px"><a href="https://play.google.com/books/publish" target="_blank" rel="noopener" style="font-weight:700">Google Play Books</a> <span style="color:var(--ink-soft)">— huge global reach across Android devices.</span></li>
              </ul>

              <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--ink-soft);margin:2px 0 8px">Distributors — upload once, reach many stores</div>
              <ul style="margin:0 0 16px 0;padding-left:0;list-style:none;font-size:14px;line-height:1.6">
                <li style="margin-bottom:9px"><a href="https://www.draft2digital.com/" target="_blank" rel="noopener" style="font-weight:700">Draft2Digital</a> <span style="color:var(--ink-soft)">— upload your EPUB once and it distributes to Apple, Kobo, Barnes &amp; Noble, libraries, and more. The simplest way to be everywhere at once.</span></li>
                <li style="margin-bottom:9px"><a href="https://www.smashwords.com/" target="_blank" rel="noopener" style="font-weight:700">Smashwords</a> <span style="color:var(--ink-soft)">— a large indie-focused store and distributor (now part of Draft2Digital).</span></li>
              </ul>

              <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--ink-soft);margin:2px 0 8px">Sell direct from your own website</div>
              <ul style="margin:0 0 16px 0;padding-left:0;list-style:none;font-size:14px;line-height:1.6">
                <li style="margin-bottom:9px"><a href="https://payhip.com/" target="_blank" rel="noopener" style="font-weight:700">Payhip</a> <span style="color:var(--ink-soft)">— sell ebooks straight from your own site with instant delivery, and keep the largest share of each sale.</span></li>
                <li style="margin-bottom:9px"><a href="https://gumroad.com/" target="_blank" rel="noopener" style="font-weight:700">Gumroad</a> <span style="color:var(--ink-soft)">— great if you already have an audience to sell to directly.</span></li>
                <li style="margin-bottom:9px"><a href="https://apps.shopify.com/digital-downloads" target="_blank" rel="noopener" style="font-weight:700">Shopify (Digital Downloads)</a> <span style="color:var(--ink-soft)">— run your own bookstore with full control over branding, pricing, and your customer list.</span></li>
              </ul>

              <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--ink-soft);margin:2px 0 8px">Reader delivery &amp; promotion</div>
              <ul style="margin:0 0 6px 0;padding-left:0;list-style:none;font-size:14px;line-height:1.6">
                <li style="margin-bottom:9px"><a href="https://bookfunnel.com/" target="_blank" rel="noopener" style="font-weight:700">BookFunnel</a> <span style="color:var(--ink-soft)">— handles ebook delivery and reader tech support; often paired with Payhip or Shopify.</span></li>
                <li style="margin-bottom:9px"><a href="https://storyoriginapp.com/" target="_blank" rel="noopener" style="font-weight:700">StoryOrigin</a> <span style="color:var(--ink-soft)">— newsletter swaps, review copies, and direct sales.</span></li>
              </ul>

              <p style="margin:14px 0 0 0;background:var(--accent-lt);border-left:3px solid var(--accent);border-radius:6px;padding:10px 14px;font-size:13.5px;line-height:1.55"><strong>Don’t overlook your own website.</strong> Selling direct — through Payhip, Shopify, or Gumroad — keeps the largest share of every sale and, just as valuable, builds your own list of readers you can reach again for the next book.</p>
            </div>
          </div>

          
          <!-- ⚠ The moment somebody decides NOT to buy is the most useful thing
               this tool can tell us, and it is the one moment we currently
               learn nothing from. Deliberately placed beside the paywall, not
               hidden in a footer. -->
          <div id="eb-feedback" style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(0,0,0,.09)">
            <button type="button" class="app-btn app-btn-sm" id="eb-feedback-open" onclick="ebookFeedbackOpen()">Not what you expected? Tell us</button>
            <div id="eb-feedback-form" style="display:none;margin-top:11px">
              <p style="margin:0 0 8px;font-size:14.5px;line-height:1.6;color:var(--ink-soft)">
                What's wrong with it? This goes straight to us and we read every one —
                it's the only way we find out what the converter is getting wrong.
              </p>
              <textarea id="eb-feedback-text" rows="4" placeholder="e.g. the chapters are in the wrong order, or half the pictures are missing" style="width:100%;font-size:15px;padding:10px;border-radius:8px;
                               border:1px solid rgba(0,0,0,.18);font-family:inherit"></textarea>
              <div class="actions" style="margin-top:9px">
                <button type="button" class="app-btn app-btn-green" id="eb-feedback-send" onclick="ebookFeedbackSend()">Send</button>
                <button type="button" class="app-btn app-btn-sm" onclick="ebookFeedbackClose()">Cancel</button>
              </div>
              <div id="eb-feedback-done" style="display:none;margin-top:9px;font-size:14.5px;color:#177245">
                Thank you — that's genuinely useful.
              </div>
            </div>
          </div>

        <!-- ⚠ STANDALONE ONLY. Shown after they have their book, never before —
             nobody wants a pitch for the bigger product while they are still
             waiting to see whether this one worked. -->
        <div class="ah-only-standalone" id="eb-standalone-upsell">
          <div class="card">
            <div class="card-title">Liked this? It's one tool out of a dozen.</div>
            <p style="margin:0 0 12px;font-size:15.5px;line-height:1.7">
              The eBook Maker is part of <strong>Elite Publishing</strong> — a complete
              promotion toolkit built for indie and hybrid authors. Everything below is
              included in a subscription, and <strong>the eBook Maker comes with it</strong>,
              so you'd never pay per conversion again.
            </p>
            <ul style="margin:0 0 14px;padding-left:22px;font-size:15.5px;line-height:1.85">
              <li><strong>Book descriptions, blurbs and press releases</strong> written from your book's own details.</li>
              <li><strong>Social posts and ad copy</strong> for every platform, with the images sized to fit each one.</li>
              <li><strong>Email campaigns</strong> to your reader list, with sending built in.</li>
              <li><strong>An author website</strong> you can put up without touching code.</li>
              <li><strong>Keywords, categories and KDP tools</strong> for getting found on Amazon.</li>
            </ul>
            <div class="actions" style="margin:0">
              <a class="app-btn app-btn-green" href="#pricing">See what's included</a>
              <a class="app-btn app-btn-outline" href="https://elitepublishing.co/">Take a look around first</a>
            </div>
          </div>
        </div>
        </div>

        <div class="card" id="eb-recent-card">
          <div class="card-title">Recent conversions</div>
          <div id="eb-recent-list"><p style="color:var(--ink-soft);margin:0;font-size:14px">No conversions yet.</p></div>
        </div>
      </div>

      <!-- KDP A+ CONTENT -->
      <div class="view" id="view-kdp-aplus">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('promo')">← Promo Materials</button>
        </div>
        <div class="page-header"><h1>A+ Content Modules</h1><p>The rich product description blocks below the basic blurb on Amazon — most authors leave these blank</p></div>

        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">What is A+ Content?</div>
          <p style="margin:0 0 8px 0">When a reader scrolls down on your Amazon book page, after the basic description, they hit the "From the Publisher" section. That's A+ Content — image-and-text blocks that read as full marketing real estate. KDP gives you up to 7 modules per book; <strong>most indie authors use zero</strong> because the editor is intimidating.</p>
          <p style="margin:0">This generates 3 ready-to-use modules. Paste each into KDP → Marketing → A+ Content → Create A+ Content → Modules. You'll add your own images on KDP's side.</p>
        </div>

        <div class="card" id="aplus-form-card">
          <div class="card-title">Generate A+ Content</div>
          <div id="aplus-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:14px;font-weight:500"></div>
          <div class="field-group">
            <label class="field-label">Book <span style="color:var(--danger)">*</span></label>
            <select id="aplus-book-id" onchange="renderBookBanner('aplus-book-id','aplus-book-status')">
              <option value="0">— pick a book —</option>
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Extra guidance (optional)</label>
            <textarea id="aplus-extra-hint" rows="2" placeholder="e.g. Lean into the small-town setting. Or: emphasize the slow-burn romance subplot."></textarea>
          </div>
          <div class="actions">
            <button class="app-btn app-btn-green" id="aplus-generate-btn" onclick="generateKdpAplus()">
              <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
              Generate 3 modules
            </button>
          </div>
        </div>

        <div class="card" id="aplus-output-card" style="display:none">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div class="card-title" style="margin:0">Your A+ Content modules</div>
            <div style="display:flex;gap:8px">
              <button class="app-btn app-btn-outline app-btn-sm" onclick="copyKdpAplus()">Copy</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocPdf('aplus-output', docTitle('aplus-book-id','KDP A+ Content'))">Download PDF</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocWord('aplus-output', docTitle('aplus-book-id','KDP A+ Content'))">Download Word</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="generateKdpAplus()">Regenerate</button>
            </div>
          </div>
          <pre id="aplus-output" style="white-space:pre-wrap;font-family:var(--font-serif);font-size:14px;line-height:1.7;margin:0;padding:0;border:none;background:none"></pre>
          <div id="aplus-quota-note" style="margin-top:12px;font-size:12px;color:var(--ink-soft)"></div>
        </div>
      </div>

      <!-- AUTHOR CENTRAL BIO -->
      <div class="view" id="view-author-bio">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('promo')">← Promo Materials</button>
        </div>
        <div class="page-header"><h1>Author Central Bio</h1><p>The cross-book bio that appears on your Amazon Author Page and every book product page</p></div>

        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">Where this bio lives</div>
          <p style="margin:0 0 8px 0">This bio is different from the per-book "About the author" you wrote on each book page. It lives on your <strong>Amazon Author Central</strong> profile and appears on <em>every</em> one of your books. It establishes who you are as an author — the kinds of books you write, your voice, and where readers can follow you.</p>
          <p style="margin:0">Need to set up Author Central first? <a href="#" onclick="navigate('sales'); showSetupHelp('amazon-author-central'); return false;">See setup instructions →</a></p>
        </div>

        <div class="card" id="abio-form-card">
          <div class="card-title">Generate bio</div>
          <div id="abio-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:14px;font-weight:500"></div>
          <div class="field-group">
            <label class="field-label">Representative book <span style="color:var(--danger)">*</span></label>
            <select id="abio-book-id" onchange="renderBookBanner('abio-book-id','abio-book-status')">
              <option value="0">— pick a book —</option>
            </select>
            <div style="font-size:11.5px;color:var(--ink-soft);margin-top:5px">Pick any of your books — the AI uses it for genre, audience, and voice context. The bio itself is cross-book and won't pitch this specific title.</div>
          </div>
          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Voice</label>
              <select id="abio-voice">
                <option value="third">Third-person ("She writes…")</option>
                <option value="first">First-person ("I write…")</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Follow links (optional)</label>
              <input type="text" id="abio-follow-links" placeholder="e.g. newsletter at jane.com/news, IG @janewrites">
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Extra guidance (optional)</label>
            <textarea id="abio-extra-hint" rows="2" placeholder="e.g. Mention I'm a former librarian and live in Maine. Or: don't include the Iowa Writers' Workshop credential — it makes me sound stuffy."></textarea>
          </div>
          <div class="actions">
            <button class="app-btn app-btn-green" id="abio-generate-btn" onclick="generateAuthorBio()">
              <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
              Generate bio
            </button>
          </div>
        </div>

        <div class="card" id="abio-output-card" style="display:none">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div class="card-title" style="margin:0">Your Author Central bio</div>
            <div style="display:flex;gap:8px">
              <button class="app-btn app-btn-outline app-btn-sm" onclick="copyAuthorBio()">Copy</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocPdf('abio-output', docTitle('abio-book-id','Author Bio'))">Download PDF</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="downloadDocWord('abio-output', docTitle('abio-book-id','Author Bio'))">Download Word</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="generateAuthorBio()">Regenerate</button>
            </div>
          </div>
          <pre id="abio-output" style="white-space:pre-wrap;font-family:var(--font-serif);font-size:14px;line-height:1.7;margin:0;padding:0;border:none;background:none"></pre>
          <div id="abio-quota-note" style="margin-top:12px;font-size:12px;color:var(--ink-soft)"></div>
        </div>
      </div>

      <!-- KDP PROMOS -->
      <div class="view" id="view-kdp-promos">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('promo')">← Promo Materials</button>
        </div>
        <div class="page-header"><h1>KDP Select Promo Planner</h1><p>Plan your Free Promo Days and Countdown Deals — then run them on KDP</p></div>

        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">How this works</div>
          <p style="margin:0 0 10px 0">If your book is enrolled in <strong>KDP Select</strong>, you get two promo types per 90-day enrollment cycle: <strong>Free Promo Days</strong> (up to 5 days where the eBook is free) or <strong>Countdown Deals</strong> (up to 7 days at a discounted price, US/UK only). They don't stack — you pick one per cycle.</p>
          <p style="margin:0 0 8px 0"><strong>This is a planning tool, not a KDP integration.</strong> Two steps:</p>
          <ol style="margin:0;padding-left:22px">
            <li style="margin-bottom:6px"><strong>Plan here.</strong> Pick the book, type, and dates. Save. Nothing is sent to Amazon — the plan lives in your app so you can keep track of what you've committed to.</li>
            <li><strong>Run on KDP.</strong> Sign in to <a href="https://kdp.amazon.com/en_US/bookshelf" target="_blank" rel="noopener">kdp.amazon.com</a> → Bookshelf → click the <strong>···</strong> menu next to your book → <strong>Promote and Advertise</strong>. Pick <em>Free Book Promotion</em> (for Free Promo Days) or <em>Kindle Countdown Deals</em>. Enter the same dates you planned here. Submit. KDP usually approves within a few hours.</li>
          </ol>
        </div>

        <!-- Add / Edit form (hidden by default) -->
        <div class="card" id="kp-form-card" style="display:none">
          <div class="card-title" id="kp-form-title">Plan a promo</div>
          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Book <span style="color:var(--danger)">*</span></label>
              <select id="kp-book-id">
                <option value="0">— pick a book —</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Promo type <span style="color:var(--danger)">*</span></label>
              <select id="kp-promo-type" onchange="kpUpdateTypeHint()">
                <option value="free">Free Promo Days (max 5 days)</option>
                <option value="countdown">Countdown Deal (max 7 days)</option>
              </select>
            </div>
          </div>
          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Start date <span style="color:var(--danger)">*</span></label>
              <input type="date" id="kp-start-date">
            </div>
            <div class="field-group">
              <label class="field-label">End date <span style="color:var(--danger)">*</span></label>
              <input type="date" id="kp-end-date">
            </div>
          </div>
          <div id="kp-type-hint" style="font-size:12px;color:var(--ink-soft);margin:-6px 0 14px 0"></div>
          <div class="field-group">
            <label class="field-label">Notes (optional)</label>
            <textarea id="kp-notes" rows="2" placeholder="e.g. Coordinated with Goodreads giveaway. Or: testing Bookbub Featured Deal pricing."></textarea>
          </div>
          <input type="hidden" id="kp-edit-id">
          <div class="lesson-warning" style="margin:0 0 14px 0;font-size:12.5px">
            <strong>After you save here, run it on KDP:</strong> sign in to <a href="https://kdp.amazon.com/en_US/bookshelf" target="_blank" rel="noopener">kdp.amazon.com</a> → Bookshelf → click the <strong>···</strong> menu next to your book → <strong>Promote and Advertise</strong> → pick <em>Free Book Promotion</em> (for Free Promo Days) or <em>Kindle Countdown Deals</em> → enter the same dates you set here → submit. Saving here does not send anything to Amazon.
          </div>
          <div class="actions" style="display:flex;gap:8px">
            <button class="app-btn app-btn-green" onclick="savePromo()">Save promo</button>
            <button class="app-btn app-btn-outline" onclick="kpHideForm()">Cancel</button>
            <button class="app-btn app-btn-outline" id="kp-delete-btn" onclick="deletePromo()" style="display:none;margin-left:auto;color:var(--danger);border-color:#FECACA">Delete</button>
          </div>
        </div>

        <!-- Add button -->
        <div class="actions" id="kp-add-btn-row">
          <button class="app-btn app-btn-green" onclick="kpShowForm()">+ Plan a promo</button>
        </div>

        <!-- Upcoming -->
        <div class="card">
          <div class="card-title" style="margin-bottom:8px">Upcoming &amp; active</div>
          <div id="kp-upcoming"><div class="empty">Loading…</div></div>
        </div>

        <!-- Past -->
        <div class="card">
          <div class="card-title" style="margin-bottom:8px;color:var(--ink-soft)">Past</div>
          <div id="kp-past"><div class="empty">Loading…</div></div>
        </div>
      </div>

      <!-- SALES RANK LOGGER -->
      <div class="view" id="view-rank-logger">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('promo')">← Promo Materials</button>
        </div>
        <div class="page-header"><h1>Sales Rank Logger</h1><p>Track your book's Amazon Best Seller Rank over time</p></div>

        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">How this works</div>
          <p style="margin:0 0 8px 0">Amazon shows a <strong>Best Seller Rank</strong> (BSR) on every Kindle and paperback product page — buried in the "Product details" section. Lower rank = better. A book at rank 5,000 is selling more often than one at rank 200,000.</p>
          <p style="margin:0 0 8px 0">Amazon doesn't expose BSR via an API, so this is a manual log: visit your Amazon product page, copy the BSR number, paste it here with today's date. Weekly is plenty. Over time, you'll see trends — what a promo did to your rank, how new releases moved the needle, whether a slow period is normal seasonality or something to worry about.</p>
          <p style="margin:0;font-size:12.5px;color:var(--ink-soft)">Find the BSR on your book's Amazon page → scroll to "Product details" → look for "Best Sellers Rank: #X in Kindle Store" (the overall number, not the category numbers).</p>
        </div>

        <div class="card">
          <div class="card-title" style="margin-bottom:10px">Pick a book</div>
          <select id="rl-book-id" onchange="loadRankEntries()">
            <option value="0">— pick a book —</option>
          </select>
        </div>

        <!-- Chart -->
        <div class="card" id="rl-chart-card" style="display:none">
          <div class="card-title" style="margin-bottom:10px">Rank over time</div>
          <div id="rl-chart-container" style="position:relative;width:100%;min-height:240px"></div>
          <div style="margin-top:8px;font-size:11.5px;color:var(--ink-soft)">Y-axis is inverted: higher on the chart = better rank (lower BSR number).</div>
        </div>

        <!-- Add / Edit entry form -->
        <div class="card" id="rl-form-card" style="display:none">
          <div class="card-title" id="rl-form-title">Log a rank</div>
          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Date <span style="color:var(--danger)">*</span></label>
              <input type="date" id="rl-observed-at">
            </div>
            <div class="field-group">
              <label class="field-label">BSR <span style="color:var(--danger)">*</span></label>
              <input type="text" inputmode="numeric" id="rl-rank-value" placeholder="e.g. 47832 (commas OK)">
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Notes (optional)</label>
            <textarea id="rl-notes" rows="2" placeholder="e.g. Free promo ended yesterday. Or: new BookBub Featured Deal ran this week."></textarea>
          </div>
          <input type="hidden" id="rl-edit-id">
          <div class="actions" style="display:flex;gap:8px">
            <button class="app-btn app-btn-green" onclick="saveRankEntry()">Save entry</button>
            <button class="app-btn app-btn-outline" onclick="rlHideForm()">Cancel</button>
            <button class="app-btn app-btn-outline" id="rl-delete-btn" onclick="deleteRankEntry()" style="display:none;margin-left:auto;color:var(--danger);border-color:#FECACA">Delete</button>
          </div>
        </div>

        <!-- Add button -->
        <div class="actions" id="rl-add-btn-row" style="display:none">
          <button class="app-btn app-btn-green" onclick="rlShowForm()">+ Log a rank</button>
        </div>

        <!-- Entries list -->
        <div class="card" id="rl-entries-card" style="display:none">
          <div class="card-title" style="margin-bottom:8px">All entries</div>
          <div id="rl-entries"><div class="empty">Loading…</div></div>
        </div>
      </div>

      <!-- VIDEOS -->
      <!-- GRAPHICS & VIDEO — ENTRY PAGE (tile grid) -->
      <div class="view" id="view-videos">
        <div class="page-header"><h1>Graphics &amp; Video</h1><p>AI-generated images and copy for every promotional asset</p></div>

        <!-- How it works -->
        <div class="card" style="margin-bottom:14px;background:var(--accent-faint,#f0edff);border:1px solid var(--accent-light,#c8bfff)">
          <div style="font-size:13px;font-weight:600;margin-bottom:8px">How it works</div>
          <ol style="margin:0 0 10px 16px;padding:0;font-size:13px;line-height:1.8">
            <li><strong>Pick the asset you want to make</strong> — each tile below opens a focused workspace.</li>
            <li><strong>Select your book on that page</strong> — the brief auto-fills with genre-matched style suggestions.</li>
            <li><strong>Customize and generate</strong> — adjust style, mood, colors, then click Generate.</li>
            <li><strong>Download, then add text</strong> — title, author, and any copy should be added after download using Canva or another design tool. AI-generated text in images is unreliable.</li>
          </ol>
          <div style="font-size:12px;color:var(--ink-soft);border-top:1px solid var(--accent-light,#c8bfff);padding-top:8px;margin-top:2px">
            <strong>Tips:</strong>
            &nbsp;·&nbsp; "No text" and "no borders" are enforced automatically — you don't need to add those.
            &nbsp;·&nbsp; Use <em>Include in image</em> for specific objects, people, or places (a lighthouse, a woman in red, a Paris street) or extra style notes (wide open sky, shallow depth of field).
            &nbsp;·&nbsp; DALL-E cannot reproduce your actual book cover — it can only work from descriptions. To post using the real cover, use <em>Use book cover</em> in the Social Posts area.
            &nbsp;·&nbsp; If the first result isn't right, tweak one or two brief fields and regenerate.
          </div>
        </div>

        <div class="gv-image-quota" style="font-size:12px;color:var(--ink-soft);margin:0 0 14px 4px"></div>

        <!-- Tile grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px">

          <div class="card gv-tile" onclick="navigate('gv-cover')" style="cursor:pointer;display:flex;flex-direction:column">
            <div style="font-size:32px;margin-bottom:8px">📚</div>
            <div class="card-title" style="margin-bottom:6px">Book Cover Concept</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;flex:1">AI portrait-format concept image to inspire your cover design. Download as a starting point.</p>
            <button class="app-btn app-btn-outline app-btn-sm" style="align-self:flex-start" onclick="event.stopPropagation();navigate('gv-cover')">Open →</button>
          </div>

          <div class="card gv-tile" onclick="navigate('gv-social')" style="cursor:pointer;display:flex;flex-direction:column">
            <div style="font-size:32px;margin-bottom:8px">📷</div>
            <div class="card-title" style="margin-bottom:6px">Social Media Graphic</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;flex:1">Text overlay copy + AI background image for Instagram, Facebook, or Pinterest.</p>
            <button class="app-btn app-btn-outline app-btn-sm" style="align-self:flex-start" onclick="event.stopPropagation();navigate('gv-social')">Open →</button>
          </div>

          <div class="card gv-tile" onclick="navigate('gv-quote')" style="cursor:pointer;display:flex;flex-direction:column">
            <div style="font-size:32px;margin-bottom:8px">💬</div>
            <div class="card-title" style="margin-bottom:6px">Quote Card</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;flex:1">AI quote text + background image for a shareable graphic.</p>
            <button class="app-btn app-btn-outline app-btn-sm" style="align-self:flex-start" onclick="event.stopPropagation();navigate('gv-quote')">Open →</button>
          </div>

          <div class="card gv-tile" onclick="navigate('gv-event')" style="cursor:pointer;display:flex;flex-direction:column">
            <div style="font-size:32px;margin-bottom:8px">📅</div>
            <div class="card-title" style="margin-bottom:6px">Event &amp; Signing Flyer</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;flex:1">Promo copy for book signings, readings, and author events.</p>
            <button class="app-btn app-btn-outline app-btn-sm" style="align-self:flex-start" onclick="event.stopPropagation();navigate('gv-event')">Open →</button>
          </div>

          <div class="card gv-tile" onclick="navigate('gv-youtube')" style="cursor:pointer;display:flex;flex-direction:column">
            <div style="font-size:32px;margin-bottom:8px">🎬</div>
            <div class="card-title" style="margin-bottom:6px">YouTube Thumbnail Text</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;flex:1">Bold, short thumbnail text options that get clicks.</p>
            <button class="app-btn app-btn-outline app-btn-sm" style="align-self:flex-start" onclick="event.stopPropagation();navigate('gv-youtube')">Open →</button>
          </div>

          <div class="card gv-tile" onclick="navigate('gv-trailer')" style="cursor:pointer;display:flex;flex-direction:column">
            <div style="font-size:32px;margin-bottom:8px">🎙️</div>
            <div class="card-title" style="margin-bottom:6px">Book Trailer Script</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;flex:1">30–60 second narration script with scene cues — ready for a videographer or video tool.</p>
            <button class="app-btn app-btn-outline app-btn-sm" style="align-self:flex-start" onclick="event.stopPropagation();navigate('gv-trailer')">Open →</button>
          </div>

          <div class="card gv-tile" onclick="navigate('gv-trailer-video')" style="cursor:pointer;display:flex;flex-direction:column">
            <div style="font-size:32px;margin-bottom:8px">🎞️</div>
            <div class="card-title" style="margin-bottom:6px">Book Trailer Video</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;flex:1">30-second vertical book trailer (9:16) — your cover with Ken Burns motion, title and tagline overlays, mood-matched music. Renders in 1–2 minutes.</p>
            <button class="app-btn app-btn-outline app-btn-sm" style="align-self:flex-start" onclick="event.stopPropagation();navigate('gv-trailer-video')">Open →</button>
          </div>

          <div class="card gv-tile" onclick="navigate('gv-slideshow')" style="cursor:pointer;display:flex;flex-direction:column">
            <div style="font-size:32px;margin-bottom:8px">🎬</div>
            <div class="card-title" style="margin-bottom:6px">Slideshow Video</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;flex:1">Turn 2–12 of your images into a video — slides in sequence with crossfades, music, and optional narration. One render fits feeds; another fits Reels/TikTok.</p>
            <button class="app-btn app-btn-outline app-btn-sm" style="align-self:flex-start" onclick="event.stopPropagation();navigate('gv-slideshow')">Open →</button>
          </div>

        </div>
      </div>

      <!-- GRAPHICS & VIDEO — sub-pages (one per asset type) -->

      <div class="view" id="view-gv-cover">
        <div style="margin-bottom:10px"><a href="#" onclick="navigate('videos');return false" style="font-size:13px;color:var(--accent);text-decoration:none">← Back to Graphics &amp; Video</a></div>
        <div class="page-header"><h1>Book Cover Concept</h1><p>AI portrait-format concept image to inspire your cover design</p></div>

        <div class="card" style="margin-bottom:14px">
          <div class="field-group" style="margin:0">
            <label class="field-label">Book <span style="color:var(--accent);font-weight:600">— required for best AI results</span></label>
            <select id="gv-cover-book-id" class="gv-book-selector" onchange="gvUpdateBriefs()"><option value="0">— Select a book —</option></select>
          </div>
          <div class="gv-image-quota" style="font-size:12px;color:var(--ink-soft);margin-top:10px"></div>
        </div>

        <div class="card" style="max-width:760px">
          <div class="card-title" style="margin-bottom:8px">Book Cover Concept</div>
          <div class="gv-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:10px;font-weight:500"></div>
          <p style="font-size:13px;color:var(--ink-soft);margin-bottom:10px">AI-generated portrait concept image to inspire your cover design. Download and use as a starting point.</p>
          <div class="gv-brief-panel">
            <div style="font-size:11px;font-weight:600;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:9px">Image Brief</div>
            <div class="gv-brief-row"><span class="gv-brief-label">Print size:</span>
              <select id="gv-cover-size" class="gv-brief-select">
                <option value="6&quot; × 9&quot; trade paperback">6" × 9" (Trade paperback)</option>
                <option value="5½&quot; × 8½&quot; compact paperback">5½" × 8½" (Compact paperback)</option>
                <option value="8½&quot; × 11&quot; large format">8½" × 11" (Large format)</option>
                <option value="Square format">Square (web / social)</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Style:</span>
              <select id="gv-cover-style" class="gv-brief-select">
                <option>Cinematic</option><option>Illustrated</option><option>Painterly</option>
                <option>Photorealistic</option><option>Minimalist</option><option>Watercolor</option><option>Digital art</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Mood:</span>
              <select id="gv-cover-mood" class="gv-brief-select">
                <option>Dark &amp; mysterious</option><option>Warm &amp; inviting</option><option>Tense &amp; dramatic</option>
                <option>Romantic</option><option>Whimsical</option><option>Gritty &amp; raw</option>
                <option>Peaceful &amp; serene</option><option>Eerie &amp; unsettling</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Color palette:</span>
              <select id="gv-cover-palette" class="gv-brief-select">
                <option>Dark &amp; rich</option><option>Warm earth tones</option><option>Cool blues &amp; grays</option>
                <option>Bright &amp; vibrant</option><option>Muted &amp; subtle</option><option>Black &amp; white</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Lighting:</span>
              <select id="gv-cover-lighting" class="gv-brief-select">
                <option>Dark &amp; moody</option><option>Soft &amp; natural</option><option>Bright &amp; airy</option>
                <option>Golden hour</option><option>Dramatic spotlight</option><option>Neon &amp; artificial</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Complexity:</span>
              <select id="gv-cover-complexity" class="gv-brief-select">
                <option value="Clean, minimal composition">Simple</option>
                <option value="Balanced composition, moderate detail" selected>Medium</option>
                <option value="Richly detailed, intricate, highly complex composition">Very complex</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Colors to include:</span>
              <input type="text" id="gv-cover-colors" class="gv-brief-input" placeholder="e.g. deep burgundy, gold accents, midnight blue…">
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Include in image:</span>
              <input type="text" id="gv-cover-include" class="gv-brief-input" placeholder="e.g. lone figure, ancient map, broken clock…">
            </div>
            <p style="font-size:11px;color:var(--ink-soft);margin:9px 0 0">Add title &amp; author text after download — AI text rendering is unreliable.</p>
          </div>
          <div class="field-group" id="gv-cover-font-picker" data-value="Roboto" style="margin-bottom:10px"></div>
          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerateImage('cover', this)">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
            Generate Image
          </button>
          <div id="gv-img-cover-err" style="display:none;font-size:13px;color:var(--error);margin-bottom:8px"></div>
          <div id="gv-img-cover" style="display:none">
            <img id="gv-img-cover-el" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Generated cover concept" style="width:100%;border-radius:6px;margin-bottom:8px;display:block">
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <button class="app-btn app-btn-green app-btn-sm" onclick="gvPostGraphic('cover')">Post this cover concept →</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="gvDownloadImage('cover', 'cover-concept.png')">Download</button>
            </div>
          </div>
        </div>
      </div>

      <div class="view" id="view-gv-social">
        <div style="margin-bottom:10px"><a href="#" onclick="navigate('videos');return false" style="font-size:13px;color:var(--accent);text-decoration:none">← Back to Graphics &amp; Video</a></div>
        <div class="page-header"><h1>Social Media Graphic</h1><p>Text overlay copy + AI background image for Instagram, Facebook, or Pinterest</p></div>
        <div class="demo-platform-strip" id="demo-strip-social" style="display:none"></div>

        <div class="card" style="margin-bottom:14px">
          <div class="field-group" style="margin:0">
            <label class="field-label">Book <span style="color:var(--accent);font-weight:600">— required for best AI results</span></label>
            <select id="gv-social-book-id" class="gv-book-selector" onchange="gvUpdateBriefs()"><option value="0">— Select a book —</option></select>
          </div>
          <div class="gv-image-quota" style="font-size:12px;color:var(--ink-soft);margin-top:10px"></div>
        </div>

        <div class="card" style="max-width:760px">
          <div class="gv-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:10px;font-weight:500"></div>
          <div class="field-group" style="margin-bottom:10px">
            <label class="field-label">Platform</label>
            <select id="gv-social-platform">
              <option value="instagram">Instagram post (1:1)</option>
              <option value="facebook">Facebook post</option>
              <option value="pinterest">Pinterest pin</option>
            </select>
          </div>

          <div class="field-group" style="margin:0 0 10px">
            <label class="field-label">Your copy <span style="font-weight:400;color:var(--ink-soft)">— write your own, or click "Generate copy" below</span></label>
            <textarea id="gv-social-copy-text" rows="4" style="width:100%;font-size:13px;margin-top:6px;resize:vertical" placeholder="Type your post copy here, or click Generate copy for AI suggestions…"></textarea>
          </div>
          <div class="field-group" style="margin:0 0 10px">
            <label class="field-label">Link to include <span style="font-weight:400;color:var(--ink-soft)">— added to the end on a new line when you copy or send</span></label>
            <input type="text" id="gv-social-link" placeholder="https://yoursite.com/book" style="font-size:13px">
            <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Note: Instagram doesn't make caption links clickable — consider "link in bio" for Instagram posts.</div>
          </div>
          <div class="field-group" style="margin:0 0 12px">
            <label class="field-label">Final post preview</label>
            <div id="gv-social-preview" style="font-size:13px;background:var(--bg-soft,#f8f7fc);border:1px solid var(--border-soft,#e5e5ea);border-radius:6px;padding:10px 12px;min-height:42px;white-space:pre-wrap;line-height:1.5;color:var(--ink)">
              <span style="color:var(--ink-soft);font-style:italic">Type a post above to see how it'll look with the link.</span>
            </div>
          </div>

          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerate('social')">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
            Generate copy
          </button>
          <div id="gv-out-social" class="gv-output" style="display:none"></div>

          <div class="gv-brief-panel">
            <div style="font-size:11px;font-weight:600;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:9px">Image Brief</div>
            <div class="gv-brief-row"><span class="gv-brief-label">Style:</span>
              <select id="gv-social-style" class="gv-brief-select">
                <option>Bold &amp; graphic</option><option>Photographic</option><option>Illustrated</option>
                <option>Minimalist</option><option>Abstract</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Mood:</span>
              <select id="gv-social-mood" class="gv-brief-select">
                <option>Exciting</option><option>Mysterious</option><option>Warm &amp; inviting</option>
                <option>Dramatic</option><option>Playful</option><option>Elegant</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Color palette:</span>
              <select id="gv-social-palette" class="gv-brief-select">
                <option>High contrast</option><option>Warm tones</option><option>Cool tones</option>
                <option>Vibrant</option><option>Dark</option><option>Pastel</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Complexity:</span>
              <select id="gv-social-complexity" class="gv-brief-select">
                <option value="Clean, minimal composition">Simple</option>
                <option value="Balanced composition, moderate detail" selected>Medium</option>
                <option value="Richly detailed, intricate, highly complex composition">Very complex</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Colors to include:</span>
              <input type="text" id="gv-social-colors" class="gv-brief-input" placeholder="e.g. teal, warm orange, cream…">
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Include in image:</span>
              <input type="text" id="gv-social-include" class="gv-brief-input" placeholder="e.g. open book, coffee cup, autumn leaves…">
            </div>
            <p style="font-size:11px;color:var(--ink-soft);margin:9px 0 0">Your copy is composited onto the image automatically when you generate — typed text overlays in the left panel, the book cover composites on the right.</p>
          </div>
          <div class="field-group" id="gv-social-font-picker" data-value="Roboto" style="margin-bottom:10px"></div>

          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerateImage('social', this)">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="1" width="14" height="14" rx="2"></rect><circle cx="5.5" cy="5.5" r="1.5"></circle><path d="M1 11l4-4 3 3 2-2 5 5"></path></svg>
            Generate Image
          </button>
          <div id="gv-img-social-err" style="display:none;font-size:13px;color:var(--error);margin-top:6px"></div>
          <div id="gv-img-social" style="display:none;margin-top:8px">
            <img id="gv-img-social-el" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Generated social background" style="width:100%;border-radius:6px;margin-bottom:8px;display:block">
            <button class="app-btn app-btn-outline app-btn-sm" onclick="gvDownloadImage('social', 'social-background.png')">Download</button>
          </div>

          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;padding-top:12px;border-top:1px solid var(--border-soft,#e5e5ea)">
            <button class="app-btn app-btn-green app-btn-sm" onclick="gvPostGraphic('social')">Post this graphic →</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="gvCopySocialText(this)">Copy to clipboard</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="gvSendToSocialPosts()">Send to Social Posts ›</button>
          </div>
        </div>
      </div>

      <div class="view" id="view-gv-quote">
        <div style="margin-bottom:10px"><a href="#" onclick="navigate('videos');return false" style="font-size:13px;color:var(--accent);text-decoration:none">← Back to Graphics &amp; Video</a></div>
        <div class="page-header"><h1>Quote Card</h1><p>AI quote text + background image for a shareable graphic</p></div>
        <div class="demo-platform-strip" id="demo-strip-quote" style="display:none"></div>

        <div class="card" style="margin-bottom:14px">
          <div class="field-group" style="margin:0">
            <label class="field-label">Book <span style="color:var(--accent);font-weight:600">— required for best AI results</span></label>
            <select id="gv-quote-book-id" class="gv-book-selector" onchange="gvUpdateBriefs()"><option value="0">— Select a book —</option></select>
          </div>
          <div class="gv-image-quota" style="font-size:12px;color:var(--ink-soft);margin-top:10px"></div>
        </div>

        <div class="card" style="max-width:760px">
          <div class="gv-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:10px;font-weight:500"></div>
          <p style="font-size:13px;color:var(--ink-soft);margin-bottom:12px">AI quote text + background image for a shareable graphic.</p>
          <div class="field-group" style="margin-bottom:10px">
            <label class="field-label">Quote source (optional)</label>
            <input type="text" id="gv-quote-source" placeholder="e.g. a 5-star review, chapter 3, your tagline…">
          </div>

          <div class="field-group" style="margin:0 0 10px">
            <label class="field-label">Your quote <span style="font-weight:400;color:var(--ink-soft)">— write your own, or click "Generate quote" below</span></label>
            <textarea id="gv-quote-copy-text" rows="3" style="width:100%;font-size:13px;margin-top:6px;resize:vertical" placeholder="Type a quote here, or click Generate quote for AI suggestions…"></textarea>
          </div>

          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerate('quote')">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
            Generate quote
          </button>
          <div id="gv-out-quote" class="gv-output" style="display:none"></div>

          <div class="gv-brief-panel">
            <div style="font-size:11px;font-weight:600;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:9px">Image Brief</div>
            <div class="gv-brief-row"><span class="gv-brief-label">Atmosphere:</span>
              <select id="gv-quote-atmosphere" class="gv-brief-select">
                <option>Ethereal</option><option>Cozy &amp; warm</option><option>Dark &amp; moody</option>
                <option>Romantic</option><option>Nature-inspired</option><option>Abstract</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Background:</span>
              <select id="gv-quote-bg" class="gv-brief-select">
                <option>Soft bokeh</option><option>Stone &amp; rock</option><option>Wood &amp; organic</option>
                <option>Water &amp; mist</option><option>Fabric &amp; textile</option><option>Abstract gradient</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Color tones:</span>
              <select id="gv-quote-palette" class="gv-brief-select">
                <option>Dark (for white text)</option><option>Light (for dark text)</option>
                <option>Warm neutrals</option><option>Cool tones</option><option>Colorful</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Complexity:</span>
              <select id="gv-quote-complexity" class="gv-brief-select">
                <option value="Clean, minimal composition">Simple</option>
                <option value="Balanced composition, moderate detail" selected>Medium</option>
                <option value="Richly detailed, intricate, highly complex composition">Very complex</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Colors to include:</span>
              <input type="text" id="gv-quote-colors" class="gv-brief-input" placeholder="e.g. dusty rose, charcoal, ivory…">
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Include in image:</span>
              <input type="text" id="gv-quote-include" class="gv-brief-input" placeholder="e.g. candle, rain on glass, forest path… (optional)">
            </div>
            <p style="font-size:11px;color:var(--ink-soft);margin:9px 0 0">Your quote is composited onto the image automatically when you generate the background — no Canva step needed.</p>
          </div>
          <div class="field-group" id="gv-quote-font-picker" data-value="ACaslonPro" style="margin-bottom:10px"></div>

          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerateImage('quote', this)">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="1" width="14" height="14" rx="2"></rect><circle cx="5.5" cy="5.5" r="1.5"></circle><path d="M1 11l4-4 3 3 2-2 5 5"></path></svg>
            Generate Background
          </button>
          <div id="gv-img-quote-err" style="display:none;font-size:13px;color:var(--error);margin-top:6px"></div>
          <div id="gv-img-quote" style="display:none;margin-top:8px">
            <img id="gv-img-quote-el" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Generated quote background" style="width:100%;border-radius:6px;margin-bottom:8px;display:block">
            <button class="app-btn app-btn-outline app-btn-sm" onclick="gvDownloadImage('quote', 'quote-background.png')">Download</button>
          </div>

          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;padding-top:12px;border-top:1px solid var(--border-soft,#e5e5ea)">
            <button class="app-btn app-btn-green app-btn-sm" onclick="gvPostGraphic('quote')">Post this card →</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="gvCopyQuoteText(this)">Copy quote</button>
          </div>
        </div>
      </div>

      <div class="view" id="view-gv-event">
        <div style="margin-bottom:10px"><a href="#" onclick="navigate('videos');return false" style="font-size:13px;color:var(--accent);text-decoration:none">← Back to Graphics &amp; Video</a></div>
        <div class="page-header"><h1>Event &amp; Signing Flyer</h1><p>Promo copy + AI background image for book signings, readings, and author events</p></div>

        <div class="card" style="margin-bottom:14px">
          <div class="field-group" style="margin:0">
            <label class="field-label">Book <span style="color:var(--accent);font-weight:600">— required for best AI results</span></label>
            <select id="gv-event-book-id" class="gv-book-selector" onchange="gvUpdateBriefs()"><option value="0">— Select a book —</option></select>
          </div>
          <div class="gv-image-quota" style="font-size:12px;color:var(--ink-soft);margin-top:10px"></div>
        </div>

        <div class="card" style="max-width:760px">
          <div class="gv-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:10px;font-weight:500"></div>

          <div class="field-group" style="margin-bottom:10px">
            <label class="field-label">Format</label>
            <select id="gv-event-format">
              <option value="flyer">Flyer (8.5×11 portrait)</option>
              <option value="poster">Poster (11×17 portrait)</option>
              <option value="square">Social event card (1:1 square)</option>
            </select>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
            <div class="field-group" style="margin:0">
              <label class="field-label">Event type</label>
              <select id="gv-event-type">
                <option>Book signing</option>
                <option>Reading</option>
                <option>Launch party</option>
                <option>Author Q&amp;A</option>
                <option>Panel discussion</option>
                <option>Workshop</option>
                <option>Book club appearance</option>
                <option>Conference / festival</option>
              </select>
            </div>
            <div class="field-group" style="margin:0">
              <label class="field-label">Date</label>
              <input type="date" id="gv-event-date">
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
            <div class="field-group" style="margin:0">
              <label class="field-label">Time</label>
              <input type="text" id="gv-event-time" placeholder="e.g. 2:00 pm">
            </div>
            <div class="field-group" style="margin:0">
              <label class="field-label">Venue</label>
              <input type="text" id="gv-event-venue" placeholder="e.g. Barnes &amp; Noble">
            </div>
          </div>

          <div class="field-group" style="margin-bottom:10px">
            <label class="field-label">Street address</label>
            <input type="text" id="gv-event-address" placeholder="e.g. 1234 Main St">
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
            <div class="field-group" style="margin:0">
              <label class="field-label">City &amp; state</label>
              <input type="text" id="gv-event-city" placeholder="e.g. Columbus, OH">
            </div>
            <div class="field-group" style="margin:0">
              <label class="field-label">Phone</label>
              <input type="text" id="gv-event-phone" placeholder="e.g. (614) 555-1212">
            </div>
          </div>

          <div class="field-group" style="margin:0 0 10px">
            <label class="field-label">Your copy <span style="font-weight:400;color:var(--ink-soft)">— write your own, or click "Generate copy" below</span></label>
            <textarea id="gv-event-copy-text" rows="4" style="width:100%;font-size:13px;margin-top:6px;resize:vertical" placeholder="Type the announcement copy here, or click Generate copy for AI suggestions…"></textarea>
          </div>
          <div class="field-group" style="margin:0 0 10px">
            <label class="field-label">RSVP / event link <span style="font-weight:400;color:var(--ink-soft)">— added to the end on a new line when you copy or send</span></label>
            <input type="text" id="gv-event-link" placeholder="https://yoursite.com/events" style="font-size:13px">
          </div>
          <div class="field-group" style="margin:0 0 12px">
            <label class="field-label">Final post preview</label>
            <div id="gv-event-preview" style="font-size:13px;background:var(--bg-soft,#f8f7fc);border:1px solid var(--border-soft,#e5e5ea);border-radius:6px;padding:10px 12px;min-height:42px;white-space:pre-wrap;line-height:1.5;color:var(--ink)">
              <span style="color:var(--ink-soft);font-style:italic">Type a post above to see how it'll look with the link.</span>
            </div>
          </div>

          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerate('event')">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
            Generate copy
          </button>
          <div id="gv-out-event" class="gv-output" style="display:none"></div>

          <div class="gv-brief-panel">
            <div style="font-size:11px;font-weight:600;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:9px">Image Brief</div>
            <div class="gv-brief-row"><span class="gv-brief-label">Style:</span>
              <select id="gv-event-style" class="gv-brief-select">
                <option>Bold &amp; graphic</option><option>Photographic</option><option>Illustrated</option>
                <option>Minimalist</option><option>Painterly</option><option>Vintage poster</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Mood:</span>
              <select id="gv-event-mood" class="gv-brief-select">
                <option>Festive &amp; inviting</option><option>Intimate &amp; warm</option>
                <option>Professional</option><option>Exciting</option><option>Elegant</option><option>Literary</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Color palette:</span>
              <select id="gv-event-palette" class="gv-brief-select">
                <option>Warm earth tones</option><option>Cool tones</option><option>High contrast</option>
                <option>Vibrant</option><option>Muted &amp; classic</option><option>Black &amp; cream</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Complexity:</span>
              <select id="gv-event-complexity" class="gv-brief-select">
                <option value="Clean, minimal composition" selected>Simple</option>
                <option value="Balanced composition, moderate detail">Medium</option>
                <option value="Richly detailed, intricate, highly complex composition">Very complex</option>
              </select>
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Colors to include:</span>
              <input type="text" id="gv-event-colors" class="gv-brief-input" placeholder="e.g. forest green, cream, warm gold…">
            </div>
            <div class="gv-brief-row"><span class="gv-brief-label">Include in image:</span>
              <input type="text" id="gv-event-include" class="gv-brief-input" placeholder="e.g. open book, bookstore aisle, audience chairs…">
            </div>
            <p style="font-size:11px;color:var(--ink-soft);margin:9px 0 0">Your headline copy lands on the top, the structured details (date, time, venue, address, phone, RSVP) land on the bottom, and your book cover composites in the middle. All overlays are pixel-exact — use the font picker below to swap typeface without leaving the app.</p>
          </div>
          <div class="field-group" id="gv-event-font-picker" data-value="Roboto" style="margin-bottom:10px"></div>

          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerateImage('event', this)">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="1" width="14" height="14" rx="2"></rect><circle cx="5.5" cy="5.5" r="1.5"></circle><path d="M1 11l4-4 3 3 2-2 5 5"></path></svg>
            Generate Image
          </button>
          <div id="gv-img-event-err" style="display:none;font-size:13px;color:var(--error);margin-top:6px"></div>
          <div id="gv-img-event" style="display:none;margin-top:8px">
            <img id="gv-img-event-el" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Generated event flyer background" style="width:100%;border-radius:6px;margin-bottom:8px;display:block">
            <button class="app-btn app-btn-outline app-btn-sm" onclick="gvDownloadImage('event', 'event-flyer-background.png')">Download</button>
          </div>

          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;padding-top:12px;border-top:1px solid var(--border-soft,#e5e5ea)">
            <button class="app-btn app-btn-green app-btn-sm" onclick="gvPostGraphic('event')">Post this flyer →</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="gvCopyEventText(this)">Copy to clipboard</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="gvSendEventToSocialPosts()">Send to Social Posts ›</button>
            <a class="app-btn app-btn-outline app-btn-sm" href="https://www.canva.com/create/flyers/" target="_blank" rel="noopener" style="display:inline-flex">Open Canva Flyer →</a>
            <a class="app-btn app-btn-outline app-btn-sm" href="https://www.canva.com/create/posters/" target="_blank" rel="noopener" style="display:inline-flex">Open Canva Poster →</a>
          </div>
        </div>
      </div>

      <div class="view" id="view-gv-youtube">
        <div style="margin-bottom:10px"><a href="#" onclick="navigate('videos');return false" style="font-size:13px;color:var(--accent);text-decoration:none">← Back to Graphics &amp; Video</a></div>
        <div class="page-header"><h1>YouTube Thumbnail Text</h1><p>Bold, short text for a thumbnail that gets clicks</p></div>

        <div class="card" style="margin-bottom:14px">
          <div class="field-group" style="margin:0">
            <label class="field-label">Book <span style="color:var(--accent);font-weight:600">— required for best AI results</span></label>
            <select id="gv-yt-book-id" class="gv-book-selector" onchange="gvUpdateBriefs()"><option value="0">— Select a book —</option></select>
          </div>
        </div>

        <div class="card" style="max-width:760px">
          <p style="font-size:13px;color:var(--ink-soft);margin-bottom:8px">Bold, short text for a thumbnail that gets clicks.</p>
          <div class="field-group" style="margin-bottom:10px">
            <label class="field-label">Video topic</label>
            <input type="text" id="gv-yt-topic" placeholder="e.g. My publishing journey, Book review, Author Q&amp;A…">
          </div>
          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerate('youtube')">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
            Generate options
          </button>
          <div id="gv-out-youtube" class="gv-output" style="display:none"></div>
          <a class="app-btn app-btn-outline app-btn-sm" href="https://www.canva.com/create/youtube-thumbnails/" target="_blank" rel="noopener" style="margin-top:10px;display:inline-flex">Open Canva →</a>
        </div>
      </div>

      <div class="view" id="view-gv-trailer">
        <div style="margin-bottom:10px"><a href="#" onclick="navigate('videos');return false" style="font-size:13px;color:var(--accent);text-decoration:none">← Back to Graphics &amp; Video</a></div>
        <div class="page-header"><h1>Book Trailer Script</h1><p>30–60 second narration script with scene cues</p></div>

        <div class="card" style="margin-bottom:14px">
          <div class="field-group" style="margin:0">
            <label class="field-label">Book <span style="color:var(--accent);font-weight:600">— required for best AI results</span></label>
            <select id="gv-trailer-book-id" class="gv-book-selector" onchange="gvUpdateBriefs()"><option value="0">— Select a book —</option></select>
          </div>
        </div>

        <div class="card" style="max-width:760px">
          <p style="font-size:13px;color:var(--ink-soft);margin-bottom:12px">A 30–60 second narration script with scene cues — ready to hand to a videographer or use in a video tool.</p>
          <button class="ai-btn" style="margin-bottom:10px" onclick="gvGenerate('trailer')">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
            Generate script
          </button>
          <div id="gv-out-trailer" class="gv-output" style="display:none"></div>
        </div>
      </div>

      <!-- BOOK TRAILER VIDEO (Shotstack-rendered MP4) -->
      <div class="view" id="view-gv-trailer-video">
        <div style="margin-bottom:10px"><a href="#" onclick="navigate('videos');return false" style="font-size:13px;color:var(--accent);text-decoration:none">← Back to Graphics &amp; Video</a></div>
        <div class="page-header"><h1>Book Trailer Video</h1><p>30-second vertical book trailer (9:16) — your cover with motion, text overlays, and music</p></div>
        <div class="demo-platform-strip" id="demo-strip-trailer" style="display:none"></div>

        <div class="card" style="margin-bottom:14px">
          <div class="field-group" style="margin:0">
            <label class="field-label">Book — auto-fills title, tagline, and cover</label>
            <select id="tv-book-id" class="gv-book-selector" onchange="tvFillFromBook()"><option value="0">— Select a book —</option></select>
          </div>
        </div>

        <!-- ── Campaign mode & templates (v113) ───────────────────── -->
        <div class="card" style="margin-bottom:14px;max-width:760px;background:var(--accent-lt);border-left:3px solid var(--accent)">
          <div class="card-title" style="margin-bottom:4px">Campaign setup</div>
          <p style="font-size:12px;color:var(--ink-mid);margin:0 0 12px">Start from a campaign style, or reuse your own saved look. Both just preset the styling controls below — you can still change anything before you render.</p>
          <div class="two-col">
            <div class="field-group" style="margin-bottom:6px">
              <label class="field-label">Campaign mode</label>
              <select id="tv-campaign-mode" onchange="tvApplyCampaignMode(this.value)">
                <option value="">— Custom (no preset) —</option>
                <option value="emotional">Emotional — big headline, slow, soft</option>
                <option value="educational">Educational — clear, readable, steady</option>
                <option value="product">Product demo — bigger dashboard, punchy</option>
                <option value="testimonial">Testimonial — centered quote, gentle</option>
                <option value="feature">Feature highlight — bold, fast, energetic</option>
              </select>
              <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Optimized defaults for a campaign style.</div>
            </div>
            <div class="field-group" style="margin-bottom:6px">
              <label class="field-label">My templates</label>
              <div style="display:flex;gap:6px">
                <select id="tv-template-select" style="flex:1"><option value="">— Saved templates —</option></select>
                <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="tvLoadTemplate()" title="Load the selected template">Load</button>
                <button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="tvDeleteTemplate()" title="Delete the selected template" style="color:#c44">✕</button>
              </div>
              <button class="app-btn app-btn-green" type="button" onclick="tvSaveTemplate()" title="Save the current styling as a reusable template" style="width:100%;margin-top:8px">
                <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:6px;vertical-align:-2px"><path d="M3 2h8l3 3v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z"></path><path d="M5 2v4h5V2M5 15v-5h6v5"></path></svg>
                Save current settings as a template
              </button>
              <div style="font-size:11px;color:var(--ink-soft);margin-top:5px">Saves the look (fonts, colors, positions, logo, motion) so you can reload it next time and just swap the image and narration.</div>
            </div>
          </div>
        </div>


        <div class="card" style="max-width:760px" id="tv-wizard">
          <div id="tv-stepper" style="display:flex;gap:6px;margin-bottom:18px;flex-wrap:wrap">
            <button type="button" class="tv-pill" id="tv-pill-1" onclick="tvGoStep(1)">1 · Image &amp; tagline</button>
            <button type="button" class="tv-pill" id="tv-pill-2" onclick="tvGoStep(2)">2 · Voice &amp; closing</button>
            <button type="button" class="tv-pill" id="tv-pill-3" onclick="tvGoStep(3)">3 · Style &amp; branding</button>
            <button type="button" class="tv-pill" id="tv-pill-4" onclick="tvGoStep(4)">4 · Generate</button>
          </div>

          <!-- ───────── STEP 1 — IMAGE & TAGLINE ───────── -->
          <div class="tv-step" data-step="1">
            <div class="card-title" style="margin-bottom:4px">Step 1 — Image &amp; tagline</div>
            <p style="font-size:12px;color:var(--ink-soft);margin:0 0 14px">What your trailer is built from — the cover image and the headline tagline.</p>
            <div class="gv-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:10px;font-weight:500"></div>

          <div class="field-group">
            <label class="field-label">Cover image — auto-filled from your book, or upload your own</label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" id="tv-cover-url" placeholder="https://..." style="flex:1">
              <button class="app-btn app-btn-outline app-btn-sm" type="button" id="tv-cover-upload-btn" onclick="document.getElementById('tv-cover-file').click()" style="white-space:nowrap">Upload</button>
            </div>
            <input type="file" id="tv-cover-file" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none" onchange="tvUploadCover(this)">
            <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Auto-filled from your book's cover. Click <strong>Upload</strong> to replace it with your own image file — it'll be hosted automatically so Shotstack can fetch it.</div>
            <div id="tv-cover-note" style="display:none;font-size:12px;color:var(--ink-mid);background:#f8f6f1;border-left:3px solid var(--accent);border-radius:4px;padding:9px 11px;margin-top:8px;line-height:1.5">Your image will be used as the trailer's cover — the trailer adds cinematic motion, a backdrop, and your tagline on top, and may trim the edges (perfect for a book cover). If you've uploaded a <strong>finished graphic</strong> you want shown whole and uncropped, turn on <strong>Show full-frame</strong> below.</div>
          </div>

          <div class="field-group">
            <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:13px;color:var(--ink-mid)">
              <input type="checkbox" id="tv-fullframe" style="margin-top:2px">
              <span><strong>Show my image full-frame</strong> — no crop or AI backdrop. For finished graphics, not book covers. <span style="color:var(--ink-soft)">(Pads to the chosen ratio with a blurred border, like your social posts; the AI backdrop and cover thumbnail are skipped.)</span></span>
            </label>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Check this box if you uploaded your own finished file instead of using your book cover — it shows your image whole, with nothing cropped or added.</div>
            <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:13px;color:var(--ink-mid);margin-top:10px;padding-left:16px">
              <input type="checkbox" id="tv-fullframe-overlays" style="margin-top:2px">
              <span><strong>Add my text &amp; logo on top</strong> — keep the full-frame image but still stamp the tagline, closing card, and logo over it. <span style="color:var(--ink-soft)">Use this for images composed with empty “branding space” — position the text into that space with the controls below.</span></span>
            </label>
          </div>

          <div class="field-group">
            <label class="field-label">Tagline (held 1.5–23s, lower third of frame)</label>
            <input type="text" id="tv-tagline" placeholder="One punchy sentence that hooks the reader" maxlength="100">
            <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Keep it short — 5 to 9 words reads best at this size.</div>
            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13px;color:var(--ink-mid);margin-top:8px">
              <input type="checkbox" id="tv-show-tagline" checked>
              <span><strong>Show tagline</strong> — uncheck to let the image breathe with no tagline text (the closing card still shows).</span>
            </label>
          </div>

          <div class="field-group">
            <label class="field-label">Output format</label>
            <select id="tv-format">
              <option value="9x16" selected>Vertical 9:16 — TikTok &amp; YouTube Shorts (1080×1920)</option>
              <option value="1x1">Square 1:1 — Instagram, Reels &amp; Facebook (1080×1080)</option>
              <option value="16x9">Horizontal 16:9 — YouTube &amp; websites (1920×1080)</option>
            </select>
            <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Pick the format for where you're posting. <strong>Instagram &amp; Reels use Square (1:1)</strong> — Instagram crops vertical videos to square by default, so squaring it up front keeps your whole image visible with nothing to adjust. The layout auto-adjusts to the format you choose.</div>
          </div>

            <div style="display:flex;justify-content:flex-end;margin-top:18px;border-top:1px solid var(--border,#eee);padding-top:14px">
              <button type="button" class="app-btn app-btn-green app-btn-sm" onclick="tvNext()">Next: Voice &amp; closing →</button>
            </div>
          </div><!-- /step 1 -->

          <!-- ───────── STEP 2 — VOICE & CLOSING ───────── -->
          <div class="tv-step" data-step="2" style="display:none">
            <div class="card-title" style="margin-bottom:4px">Step 2 — Voiceover &amp; closing card</div>
            <p style="font-size:12px;color:var(--ink-soft);margin:0 0 14px">An optional AI voiceover, and the text on the final card.</p>

            <div class="field-group">
              <label class="field-label">Closing card text</label>
              <input type="text" id="tv-cta" value="Available Now" maxlength="30">
              <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Shown on the final card — e.g. “Available Now”, “On Amazon”, your release date.</div>
            </div>

          <div class="field-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <label class="field-label" style="margin:0">AI narration (optional)</label>
              <button class="ai-btn" type="button" onclick="aiSuggestNarration()">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                AI suggest 3
              </button>
            </div>
            <textarea id="tv-narration" rows="3" maxlength="300" placeholder="A line or two for an AI voiceover. Leave blank for music-only. Reads over the tagline window — keep it under ~50 words."></textarea>
            <div id="tv-narration-suggestions" style="display:none;margin-top:10px"></div>
            <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">When narration is on, music ducks so the voice stays clear. Counts the same as a regular render against your monthly quota.</div>
          </div>

          <div class="field-group" id="tv-voice-group" style="display:none">
            <label class="field-label">Narration voice</label>
            <select id="tv-voice">
              <option value="Joanna" selected>Joanna — US English, warm female</option>
              <option value="Matthew">Matthew — US English, conversational male</option>
              <option value="Amy">Amy — British English, female</option>
              <option value="Brian">Brian — British English, male</option>
              <option value="Olivia">Olivia — Australian English, female</option>
            </select>
          </div>

            <div style="display:flex;justify-content:space-between;margin-top:18px;border-top:1px solid var(--border,#eee);padding-top:14px">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="tvBack()">← Back</button>
              <button type="button" class="app-btn app-btn-green app-btn-sm" onclick="tvNext()">Next: Style &amp; branding →</button>
            </div>
          </div><!-- /step 2 -->

          <!-- ───────── STEP 3 — STYLE & BRANDING ───────── -->
          <div class="tv-step" data-step="3" style="display:none">
            <div class="card-title" style="margin-bottom:4px">Step 3 — Style &amp; branding</div>
            <p style="font-size:12px;color:var(--ink-soft);margin:0 0 14px">Set the music and the look. Tip: apply a <strong>Campaign mode</strong> or load a saved <strong>template</strong> from the top of the page to preset everything at once.</p>

            <div class="field-group">
              <label class="field-label">Music mood (selects soundtrack)</label>
              <select id="tv-mood">
                <option value="warm" selected>Warm — gentle, hopeful (cozy mystery, contemporary)</option>
                <option value="lighthouse">Lighthouse — calm, reflective (memoir, literary, cozy)</option>
                <option value="mysterious">Mysterious — quiet tension (thriller, suspense, paranormal)</option>
                <option value="dramatic">Dramatic — orchestral swell (historical, literary, epic)</option>
                <option value="upbeat">Upbeat — light, driving (romance, YA, comedy)</option>
                <option value="dark">Dark — cinematic, heavy (horror, dark fantasy, noir)</option>
                <option value="silent">Silent — no music</option>
              </select>
            </div>

            <div class="two-col">
              <div class="field-group">
                <label class="field-label">Colors to include (optional)</label>
                <input type="text" id="tv-colors" placeholder="e.g. deep blue, silver, white">
              </div>
              <div class="field-group">
                <label class="field-label">Include in backdrop (optional)</label>
                <input type="text" id="tv-include" placeholder="e.g. lighthouse, ancient map, harbor">
              </div>
            </div>
            <div style="font-size:11px;color:var(--ink-soft);margin-top:-8px;margin-bottom:14px">Override the genre-default backdrop with specific colors or elements. Applied to all backdrop scenes.</div>

          <!-- ── Styling & layout (v112) ───────────────────────────── -->
          <details id="tv-style-section" open style="margin-bottom:14px;border:1px solid var(--border,#e4ded2);border-radius:6px;padding:0">
            <summary style="cursor:pointer;padding:11px 13px;font-weight:600;font-size:13px;color:var(--ink-mid);list-style:none">⚙ Styling &amp; layout <span style="font-weight:400;color:var(--ink-soft)">— position, zoom, colors, fonts, logo, blur (click to collapse)</span></summary>
            <div style="padding:4px 13px 14px">

              <!-- Layout row -->
              <div class="two-col">
                <div class="field-group">
                  <label class="field-label">Text position</label>
                  <select id="tv-text-position">
                    <option value="bottom" selected>Bottom (default)</option>
                    <option value="center">Center</option>
                    <option value="top">Top</option>
                    <option value="left">Left</option>
                    <option value="right">Right</option>
                  </select>
                  <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Where the tagline, closing card, and credit sit in the frame.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Image zoom — <span id="tv-zoom-val">100%</span></label>
                  <input type="range" id="tv-zoom" min="60" max="120" step="10" value="100" style="width:100%" oninput="document.getElementById('tv-zoom-val').textContent=this.value+'%'">
                  <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Scales your cover/dashboard image. 100% is the standard size.</div>
                </div>
              </div>

              <div class="two-col">
                <div class="field-group">
                  <label class="field-label">Logo overlay</label>
                  <select id="tv-logo-position">
                    <option value="none" selected>None</option>
                    <option value="bottomright">Bottom right</option>
                    <option value="bottomleft">Bottom left</option>
                    <option value="topright">Top right</option>
                  </select>
                  <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Adds Elite Publishing logo as a corner watermark.</div>
                </div>
                <div class="field-group">
                  <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:13px;color:var(--ink-mid);margin-bottom:8px">
                    <input type="checkbox" id="tv-safe-area" style="margin-top:2px">
                    <span><strong>Social safe area</strong> — keep text clear of the edges where Reels/Shorts/Instagram overlay buttons and captions.</span>
                  </label>
                  <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:13px;color:var(--ink-mid)">
                    <input type="checkbox" id="tv-bg-blur" style="margin-top:2px">
                    <span><strong>Blur background</strong> — softens the atmospheric backdrop while your cover stays sharp. A premium look. <span style="color:var(--ink-soft)">(Composed layout only — not full-frame.)</span></span>
                  </label>
                </div>
              </div>

              <div class="two-col">
                <div class="field-group">
                  <label class="field-label">Motion</label>
                  <select id="tv-motion">
                    <option value="slow" selected>Slow (cinematic — default)</option>
                    <option value="normal">Normal</option>
                    <option value="static">Static (no movement)</option>
                  </select>
                  <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Speed of the slow zoom on the image and backdrop.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Text fade</label>
                  <select id="tv-fade">
                    <option value="normal" selected>Normal</option>
                    <option value="slow">Slow (longer, softer)</option>
                    <option value="fast">Fast (snappier)</option>
                  </select>
                  <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">How the tagline, closing card, and credit fade in and out.</div>
                </div>
              </div>

              <!-- Per-area text styling -->
              <div style="font-size:12px;font-weight:600;color:var(--ink-mid);margin:6px 0 8px">Text styling — each area independently</div>
              <div style="font-size:11px;color:var(--ink-soft);margin:-4px 0 12px">Tip: if white text disappears over a light backdrop, pick a darker color here. A subtle shadow is always applied for legibility.</div>

              <!-- Tagline -->
              <div style="border-top:1px solid var(--border,#eee);padding-top:10px;margin-bottom:8px">
                <div style="font-size:12px;font-weight:600;color:var(--ink-mid);margin-bottom:6px">Tagline</div>
                <div id="tv-font-tagline" class="field-group" data-value="Roboto" style="margin-bottom:8px"></div>
                <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;font-size:13px;color:var(--ink-mid)">
                  <label style="display:flex;align-items:center;gap:5px">Size <input type="number" id="tv-size-tagline" min="18" max="120" value="48" style="width:64px"></label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer"><input type="checkbox" id="tv-bold-tagline"> Bold</label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer"><input type="checkbox" id="tv-italic-tagline"> Italic</label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer">Color <input type="color" id="tv-color-tagline" value="#ffffff" style="width:34px;height:26px;padding:0;border:none;background:none;cursor:pointer"></label>
                </div>
              </div>

              <!-- Closing card (CTA) -->
              <div style="border-top:1px solid var(--border,#eee);padding-top:10px;margin-bottom:8px">
                <div style="font-size:12px;font-weight:600;color:var(--ink-mid);margin-bottom:6px">Closing card</div>
                <div id="tv-font-cta" class="field-group" data-value="Roboto" style="margin-bottom:8px"></div>
                <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;font-size:13px;color:var(--ink-mid)">
                  <label style="display:flex;align-items:center;gap:5px">Size <input type="number" id="tv-size-cta" min="18" max="120" value="48" style="width:64px"></label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer"><input type="checkbox" id="tv-bold-cta" checked> Bold</label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer"><input type="checkbox" id="tv-italic-cta"> Italic</label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer">Color <input type="color" id="tv-color-cta" value="#ffffff" style="width:34px;height:26px;padding:0;border:none;background:none;cursor:pointer"></label>
                </div>
              </div>

              <!-- Credit line -->
              <div style="border-top:1px solid var(--border,#eee);padding-top:10px">
                <div style="font-size:12px;font-weight:600;color:var(--ink-mid);margin-bottom:6px">Credit line <span style="font-weight:400;color:var(--ink-soft)">(“Made with Elite Publishing”)</span></div>
                <div id="tv-font-credit" class="field-group" data-value="Roboto" style="margin-bottom:8px"></div>
                <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;font-size:13px;color:var(--ink-mid)">
                  <label style="display:flex;align-items:center;gap:5px">Size <input type="number" id="tv-size-credit" min="18" max="120" value="52" style="width:64px"></label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer"><input type="checkbox" id="tv-bold-credit"> Bold</label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer"><input type="checkbox" id="tv-italic-credit"> Italic</label>
                  <label style="display:flex;align-items:center;gap:5px;cursor:pointer">Color <input type="color" id="tv-color-credit" value="#ffffff" style="width:34px;height:26px;padding:0;border:none;background:none;cursor:pointer"></label>
                </div>
              </div>

            </div>
          </details>

            <div style="display:flex;justify-content:flex-start;margin-top:18px;border-top:1px solid var(--border,#eee);padding-top:14px">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="tvBack()">← Back</button>
            </div>
          </div><!-- /step 3 -->

          <!-- ───────── STEP 4 — REVIEW & GENERATE ───────── -->
          <div class="tv-step" data-step="4" style="display:none">
            <div class="card-title" style="margin-bottom:4px">Step 4 — Review &amp; generate</div>
            <p style="font-size:12px;color:var(--ink-soft);margin:0 0 12px">A quick recap before you render. Each render counts against your monthly quota.</p>
            <div id="tv-review-summary" style="font-size:13px;color:var(--ink-mid);background:#f8f6f1;border-radius:6px;padding:12px 14px;margin-bottom:14px;line-height:1.7"></div>

            <div class="actions" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="tvBack()">← Back</button>
              <button class="app-btn app-btn-green" id="tv-generate-btn" onclick="tvGenerate()">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
                Generate trailer
              </button>
              <div id="tv-quota-pill" style="font-size:12px;color:var(--ink-soft)"></div>
            </div>
          </div><!-- /step 4 -->
        </div>

        <!-- Status / progress card -->
        <div class="card" id="tv-status-card" style="display:none;margin-top:14px;max-width:760px">
          <div class="card-title" style="margin-bottom:8px">Rendering</div>
          <div id="tv-status-text" style="font-size:14px;color:var(--ink-soft);margin-bottom:8px">Submitting…</div>
          <div style="background:#eee;border-radius:3px;height:6px;overflow:hidden">
            <div id="tv-status-bar" style="height:100%;width:0%;background:var(--accent);transition:width 1s linear"></div>
          </div>
          <div id="tv-status-elapsed" style="font-size:12px;color:var(--ink-soft);margin-top:6px"></div>
        </div>

        <!-- Output / video player -->
        <div class="card" id="tv-output-card" style="display:none;margin-top:14px;max-width:760px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div class="card-title" style="margin:0">Your trailer</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <button class="app-btn app-btn-green app-btn-sm" onclick="tvPostTrailer()">Post this trailer →</button>
              <a id="tv-download-btn" class="app-btn app-btn-outline app-btn-sm" href="#" onclick="tvDownloadVideo(event)">Download MP4</a>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="tvGenerate()">Regenerate</button>
            </div>
          </div>
          <video id="tv-video" controls="" playsinline="" style="width:100%;max-width:360px;display:block;margin:0 auto;background:#000;border-radius:6px"></video>
          <div style="font-size:12px;color:var(--ink-soft);margin-top:10px;text-align:center">Sized for the format you picked — TikTok, Reels, Shorts, YouTube, or Instagram feed</div>
          <div style="margin-top:14px;padding:10px 12px;background:var(--accent-lt);border-left:3px solid var(--accent);border-radius:4px;font-size:13px;line-height:1.5">
            <strong>Posting your trailer:</strong> different platforms favor different aspect ratios, captions, and hashtag styles. <a href="#" onclick="navigate('education'); openLesson('posting-trailer'); return false">Read the posting guide →</a> for format-by-platform specs, sample captions, hashtag sets per genre, and which platforms actually drive book sales.
          </div>
        </div>

        <!-- Error card -->
        <div class="card" id="tv-error-card" style="display:none;margin-top:14px;max-width:760px;border-left:3px solid #c44">
          <div class="card-title" id="tv-error-title" style="margin-bottom:6px;color:#c44">Render failed</div>
          <div id="tv-error-text" style="font-size:13px"></div>
        </div>
      </div>


      <!-- SLIDESHOW VIDEO (Shotstack-rendered MP4 from uploaded slides) -->
      <div class="view" id="view-gv-slideshow">
        <div style="margin-bottom:10px"><a href="#" onclick="navigate('videos');return false" style="font-size:13px;color:var(--accent);text-decoration:none">← Back to Graphics &amp; Video</a></div>
        <div class="page-header"><h1>Slideshow Video</h1><p>Turn your images into a video — slides in sequence with music and optional narration</p></div>


        <!-- ── How it works ── -->
        <div class="card" style="margin-bottom:14px;max-width:760px;background:var(--accent-lt);border-left:3px solid var(--accent)">
          <div class="card-title" style="margin-bottom:6px">How it works</div>
          <p style="font-size:13px;color:var(--ink-mid);margin:0 0 8px;line-height:1.55">A slideshow video is a set of images played in sequence — with music, optional spoken narration, and text on the slides. It's the fastest way to turn a marketing idea into a video post for Facebook, Instagram, Reels, or TikTok.</p>
          <p style="font-size:13px;color:var(--ink-mid);margin:0 0 8px;line-height:1.55"><strong>Two ways to get your slides:</strong> let the AI plan and draw everything in <strong>step 1</strong> (you type the idea, it writes every slide and creates the images) — or skip straight to <strong>step 2</strong> and upload images you already have: quote cards, social graphics, event flyers, your cover. Mixing both works too.</p>
          <p style="font-size:13px;color:var(--ink-mid);margin:0;line-height:1.55">Work through the numbered steps, check the recap in <strong>step 5</strong>, then generate. Rendering takes about 1–2 minutes, each render counts against the monthly video quota you share with trailers, and when it's done you can post it to your platforms straight from this page.</p>
        </div>

        <!-- ── The 5-step wizard (v133 — same architecture as the Book Trailer page) ── -->
        <div class="card" style="max-width:760px" id="sv-wizard">
          <div id="sv-stepper" style="display:flex;gap:6px;margin-bottom:18px;flex-wrap:wrap">
            <button type="button" class="sv-pill" id="sv-pill-1" onclick="svGoStep(1)">1 · Plan with AI</button>
            <button type="button" class="sv-pill" id="sv-pill-2" onclick="svGoStep(2)">2 · Your slides</button>
            <button type="button" class="sv-pill" id="sv-pill-3" onclick="svGoStep(3)">3 · Look &amp; sound</button>
            <button type="button" class="sv-pill" id="sv-pill-4" onclick="svGoStep(4)">4 · Narration</button>
            <button type="button" class="sv-pill" id="sv-pill-5" onclick="svGoStep(5)">5 · Generate</button>
          </div>

          <!-- ───────── STEP 1 — PLAN WITH AI ───────── -->
          <div class="sv-step" data-step="1">
            <div class="card-title" style="margin-bottom:4px">Step 1 — Plan with AI <span style="font-weight:400;color:var(--ink-soft)">(optional)</span></div>
          <p style="font-size:13px;color:var(--ink-mid);margin:0 0 12px;line-height:1.55">Describe the marketing idea and the AI plans the whole slideshow — the text on each slide plus a narration line spoken while that slide is up, all matched. Review and edit every word before anything is created. When your storyboard reads right, click <strong>Next</strong> — the buttons that turn it into slide images are in step 2, right above the slide strip, so you can watch each image land as it's made. <strong>Using your own images instead?</strong> Skip this step — click <strong>Next</strong> and add them in step 2.</p>
          <div class="field-group" style="margin-bottom:10px">
            <label class="field-label">Book</label>
            <select id="sv-sb-book" class="gv-book-selector"><option value="0">— Select a book —</option></select>
          </div>
          <div class="field-group" style="margin-bottom:10px">
            <label class="field-label">Marketing idea</label>
            <input type="text" id="sv-sb-theme" maxlength="500" placeholder="e.g. Launch announcement · A holiday gift for dads who love classic rock · Meet the author behind the memoir">
          </div>
          <div class="field-group" style="max-width:220px;margin-bottom:12px">
            <label class="field-label">Slides</label>
            <select id="sv-sb-count">
              <option value="3">3</option><option value="4">4</option><option value="5">5</option>
              <option value="6" selected>6</option>
              <option value="7">7</option>
              <option value="8">8</option>
            </select>
          </div>
          <div class="field-group" style="margin-bottom:12px">
            <label class="field-label">Style reference <span style="font-weight:400;color:var(--ink-soft)">(optional — up to 6 images whose look the AI images should match; saved to your account)</span></label>
            <input type="file" id="sv-styleref-input" accept="image/jpeg,image/png,image/webp" multiple style="display:none" onchange="svUploadStyleRefs(this)">
            <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="document.getElementById('sv-styleref-input').click()">Upload style images</button>
            <span id="sv-styleref-label" style="font-size:12px;color:var(--accent);margin-left:8px"></span>
            <a href="#" id="sv-styleref-clear" onclick="svClearStyleRefs();return false" style="display:none;font-size:12px;color:var(--ink-soft);margin-left:8px">clear</a>
          </div>
          <button class="ai-btn" id="sv-sb-btn" onclick="ssGenerateStoryboard()">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
            Generate storyboard
          </button>
          <a href="#" onclick="svTogglePaste();return false" style="font-size:12px;color:var(--accent);text-decoration:none;margin-left:12px">or paste a storyboard you already have</a>
          <div id="sv-sb-paste-wrap" style="display:none;margin-top:12px">
            <div class="field-group" style="margin-bottom:8px">
              <label class="field-label">Paste your storyboard</label>
              <textarea id="sv-sb-paste" rows="8" placeholder="One slide per block, blank line between slides:

Headline for slide 1
On-slide subtext (optional)
Spoken narration for this slide (optional)

Headline for slide 2
..."></textarea>
            </div>
            <button type="button" class="app-btn app-btn-green app-btn-sm" onclick="svParseStoryboard()">Use this storyboard</button>
          </div>
          <div id="sv-sb-editor" style="display:none;margin-top:14px"></div>

            <div style="display:flex;justify-content:flex-end;margin-top:18px;border-top:1px solid var(--border,#eee);padding-top:14px">
              <button type="button" class="app-btn app-btn-green app-btn-sm" onclick="svNext()">Next: Your slides →</button>
            </div>
          </div><!-- /step 1 -->

          <!-- ───────── STEP 2 — YOUR SLIDES ───────── -->
          <div class="sv-step" data-step="2" style="display:none">
            <div class="card-title" style="margin-bottom:4px">Step 2 — Your slides</div>
            <p style="font-size:13px;color:var(--ink-mid);margin:0 0 10px;line-height:1.55">These are the images your video will play, in order. If the AI created them in step 1 they're already here — drag to reorder, ✕ to remove, or add more of your own alongside them. Add 2–12 images total; anything you've made works: quote cards, social graphics, event flyers, your cover. Images that don't match the video shape get a soft blurred background automatically, so nothing gets cropped.</p>
            <p style="font-size:13px;color:var(--ink-mid);margin:0 0 10px;line-height:1.55"><strong>Text overlays:</strong> turn on the checkbox below and a caption box appears under each slide — whatever you type is stamped onto that slide in your brand style. With a storyboard, the captions prefill themselves from the slide text.</p>
          <div id="sv-sb-actions" style="display:none;margin:0 0 12px;align-items:center;gap:12px;flex-wrap:wrap;padding:10px 12px;background:var(--accent-lt);border-radius:6px">
            <button class="app-btn app-btn-green app-btn-sm" id="sv-sb-ai" onclick="ssGenerateAiImages()">Create AI images →</button>
            <button class="app-btn app-btn-outline app-btn-sm" id="sv-sb-compose" onclick="ssComposeSlides()">Create text slides</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="svCopyImagePrompt()">Copy ChatGPT image prompt</button>
            <a href="https://chatgpt.com/" target="_blank" rel="noopener" style="font-size:12px;color:var(--accent);text-decoration:none">Open ChatGPT ↗</a>
            <span style="font-size:12px;color:var(--ink-soft)">AI images: a unique, professional scene for every slide, appearing in the strip below as each one finishes (about 1–2 minutes per image). Narration and captions are ready either way.</span>
          </div>
          <input type="file" id="sv-file-input" accept="image/jpeg,image/png,image/webp,image/gif" multiple style="display:none" onchange="ssAddImages(this)">
          <button type="button" class="app-btn app-btn-outline app-btn-sm" id="sv-add-btn" onclick="document.getElementById('sv-file-input').click()">+ Add images</button>
          <label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;margin-left:16px;cursor:pointer;vertical-align:middle">
            <input type="checkbox" id="sv-overlay-toggle" onchange="svOverlayToggled()">
            Add text overlays — type a caption under any slide
          </label>
          <div class="sv-strip" id="sv-strip"></div>

            <div style="display:flex;justify-content:space-between;margin-top:18px;border-top:1px solid var(--border,#eee);padding-top:14px">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="svBack()">← Back</button>
              <button type="button" class="app-btn app-btn-green app-btn-sm" onclick="svNext()">Next: Look &amp; sound →</button>
            </div>
          </div><!-- /step 2 -->

          <!-- ───────── STEP 3 — LOOK & SOUND ───────── -->
          <div class="sv-step" data-step="3" style="display:none">
            <div class="card-title" style="margin-bottom:4px">Step 3 — Look &amp; sound</div>
            <p style="font-size:13px;color:var(--ink-mid);margin:0 0 12px;line-height:1.55">Pick the video shape for where you'll post it — <strong>Feed 4:5</strong> is the safe choice for Facebook, Instagram, and LinkedIn; <strong>Vertical 9:16</strong> is for Reels and TikTok. You can render the same slideshow again in another shape afterward — your slides and settings stay put. Then set the pacing and the soundtrack.</p>
          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Video shape</label>
              <select id="sv-format">
                <option value="4x5" selected>Feed (4:5) — Facebook, Instagram, LinkedIn</option>
                <option value="9x16">Vertical (9:16) — Reels, TikTok</option>
                <option value="1x1">Square (1:1)</option>
                <option value="16x9">Wide (16:9) — website, email</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Seconds per slide</label>
              <select id="sv-per-slide">
                <option value="4">4 — quick</option>
                <option value="5">5</option>
                <option value="6.5" selected>6.5 — comfortable read</option>
                <option value="8">8</option>
                <option value="10">10 — slow</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Between slides</label>
              <select id="sv-transition">
                <option value="fade" selected>Crossfade</option>
                <option value="none">Hard cut</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Music</label>
              <select id="sv-mood" onchange="svMoodChanged()">
                <option value="silent">No music</option>
                <option value="warm" selected>Warm</option>
                <option value="lighthouse">Lighthouse — calm, reflective</option>
                <option value="mysterious">Mysterious</option>
                <option value="dramatic">Dramatic</option>
                <option value="upbeat">Upbeat</option>
                <option value="dark">Dark</option>
                <option value="custom">My own music — upload an MP3</option>
              </select>
              <div id="sv-music-upload" style="display:none;margin-top:8px">
                <input type="file" id="sv-music-input" accept="audio/mpeg,.mp3" style="display:none" onchange="svUploadMusic(this)">
                <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="document.getElementById('sv-music-input').click()">Upload music MP3</button>
                <span id="sv-music-file-label" style="font-size:12px;color:var(--accent);margin-left:8px"></span>
                <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Use music you have the rights to (your own, or royalty-free).</div>
              </div>
            </div>
          </div>
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-top:4px;cursor:pointer">
            <input type="checkbox" id="sv-end-card" checked>
            Add a “Made with Elite Publishing” closing card
          </label>

            <div style="display:flex;justify-content:space-between;margin-top:18px;border-top:1px solid var(--border,#eee);padding-top:14px">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="svBack()">← Back</button>
              <button type="button" class="app-btn app-btn-green app-btn-sm" onclick="svNext()">Next: Narration →</button>
            </div>
          </div><!-- /step 3 -->

          <!-- ───────── STEP 4 — NARRATION ───────── -->
          <div class="sv-step" data-step="4" style="display:none">
            <div class="card-title" style="margin-bottom:4px">Step 4 — Narration <span style="font-weight:400;color:var(--ink-soft)">(optional)</span></div>
            <p style="font-size:13px;color:var(--ink-mid);margin:0 0 12px;line-height:1.55">A voice speaking over the slides makes the video feel finished — and the music automatically ducks under it so the words stay clear. <strong>Storyboard narration</strong> (available when step 1 planned your slides) speaks each slide's own line while that slide is up, and slides extend automatically so the voice never gets cut off. Or type one script for the AI voice to read, or upload a recording of your own voice. <strong>Music only is fine too</strong> — plenty of scrollers watch with the sound off.</p>
          <div style="display:flex;gap:18px;flex-wrap:wrap;font-size:13px;margin-bottom:10px">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer"><input type="radio" name="sv-narration-mode" value="none" checked onchange="ssNarrationMode()"> None — music only</label>
            <label id="sv-mode-storyboard" style="display:none;align-items:center;gap:6px;cursor:pointer"><input type="radio" name="sv-narration-mode" value="storyboard" onchange="ssNarrationMode()"> Storyboard narration — one line per slide</label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer"><input type="radio" name="sv-narration-mode" value="tts" onchange="ssNarrationMode()"> AI voice reads my script</label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer"><input type="radio" name="sv-narration-mode" value="upload" onchange="ssNarrationMode()"> My own recording (MP3)</label>
          </div>
          <div id="sv-narration-tts" style="display:none">
            <div class="field-group" style="margin-bottom:10px">
              <label class="field-label">Narration script</label>
              <textarea id="sv-narration-text" rows="4" maxlength="900" placeholder="What the voice should say while the slides play. Rough guide: 20 words ≈ 8 seconds — about one sentence per slide."></textarea>
            </div>
          </div>
          <div id="sv-voice-wrap" style="display:none">
            <div class="field-group" style="max-width:280px">
              <label class="field-label">Voice</label>
              <select id="sv-voice">
                <option value="Joanna" selected>Joanna — US, female</option>
                <option value="Matthew">Matthew — US, male</option>
                <option value="Amy">Amy — UK, female</option>
                <option value="Brian">Brian — UK, male</option>
                <option value="Olivia">Olivia — AU, female</option>
              </select>
            </div>
          </div>
          <div id="sv-narration-upload" style="display:none">
            <p style="font-size:12px;color:var(--ink-mid);margin:0 0 8px">Record on your phone or computer, save as MP3, upload here. Music ducks automatically under your voice.</p>
            <input type="file" id="sv-voice-input" accept="audio/mpeg,.mp3" style="display:none" onchange="ssUploadVoice(this)">
            <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="document.getElementById('sv-voice-input').click()">Upload voiceover MP3</button>
            <span id="sv-voice-file-label" style="font-size:12px;color:var(--accent);margin-left:8px"></span>
          </div>

            <div style="display:flex;justify-content:space-between;margin-top:18px;border-top:1px solid var(--border,#eee);padding-top:14px">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="svBack()">← Back</button>
              <button type="button" class="app-btn app-btn-green app-btn-sm" onclick="svNext()">Next: Review &amp; generate →</button>
            </div>
          </div><!-- /step 4 -->

          <!-- ───────── STEP 5 — REVIEW & GENERATE ───────── -->
          <div class="sv-step" data-step="5" style="display:none">
            <div class="card-title" style="margin-bottom:4px">Step 5 — Review &amp; generate</div>
            <p style="font-size:13px;color:var(--ink-mid);margin:0 0 12px;line-height:1.55">A quick recap before you render. Click any step pill above to jump back and change something — nothing is lost. Each render counts against your monthly video quota (shared with trailers).</p>
            <div id="sv-review-summary" style="font-size:13px;color:var(--ink-mid);background:#f8f6f1;border-radius:6px;padding:12px 14px;margin-bottom:14px;line-height:1.7"></div>

            <div class="actions" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
              <button type="button" class="app-btn app-btn-outline app-btn-sm" onclick="svBack()">← Back</button>
              <button class="app-btn app-btn-green" id="sv-generate-btn" onclick="ssSubmit()">Generate slideshow video</button>
              <span id="sv-quota-pill" style="font-size:12px;color:var(--ink-soft)"></span>
            </div>
          </div><!-- /step 5 -->
        </div><!-- /wizard -->

        <!-- ── Status ── -->
        <div class="card" id="sv-status-card" style="display:none;max-width:760px">
          <div class="card-title" style="margin-bottom:4px">Rendering…</div>
          <div class="sv-bar-track"><div class="sv-bar" id="sv-status-bar"></div></div>
          <div id="sv-status-label" style="font-size:13px;color:var(--ink-mid)">Starting…</div>
          <div id="sv-status-elapsed" style="font-size:12px;color:var(--ink-soft);margin-top:4px"></div>
        </div>

        <!-- ── Output ── -->
        <div class="card" id="sv-output-card" style="display:none;max-width:760px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div class="card-title" style="margin:0">Your slideshow video</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <button class="app-btn app-btn-green app-btn-sm" onclick="svPostVideo()">Post this video →</button>
              <a class="app-btn app-btn-outline app-btn-sm" id="sv-download-btn" href="#" download="" target="_blank" rel="noopener">Download MP4</a>
            </div>
          </div>
          <video id="sv-video" controls="" playsinline="" style="width:100%;max-width:420px;border-radius:8px;background:#000;display:block;margin-bottom:12px"></video>
          <p style="font-size:13px;color:var(--ink-soft);margin:10px 0 0;line-height:1.55">Need another shape (e.g. vertical for Reels/TikTok)? Change the video shape in step 3 and generate again — your slides and settings stay put. Download links expire after 24 hours, so save the file now.</p>
          <div style="margin-top:14px;padding:10px 12px;background:var(--accent-lt);border-left:3px solid var(--accent);border-radius:4px;font-size:13px;line-height:1.5">
            <strong>Posting your video:</strong> click <strong>Post this video →</strong> above and a tab opens for every platform you've set up — with an editable caption, hashtag suggestions per platform, and click-by-click posting steps. New to posting video, or wondering which platforms actually sell books? <a href="#" onclick="navigate('education'); openLesson('posting-trailer'); return false">Read the posting guide →</a> for format-by-platform specs, sample captions, and hashtag sets per genre.
          </div>
        </div>

        <!-- ── Error ── -->
        <div class="card" id="sv-error-card" style="display:none;max-width:760px;border-left:3px solid var(--danger,#c44)">
          <div class="card-title" style="margin-bottom:4px;color:var(--danger,#c44)">Something went wrong</div>
          <div id="sv-error-text" style="font-size:13px"></div>
        </div>
      </div>


      <!-- PRESS -->
      <div class="view" id="view-press">
        <div class="page-header"><h1>Press Releases</h1><p>AI-drafted press releases in AP style, ready for media contacts</p></div>

        <!-- Guide callout -->
        <div class="card" style="background: var(--accent-lt); border-left: 3px solid var(--accent);">
          <div class="card-title" style="margin-bottom:8px">Before you generate</div>
          <p style="margin:0 0 10px 0">A press release works best when there's real news — a launch with a local angle, an award, a milestone, or an event. Routine "new book available" announcements rarely get coverage.</p>
          <p style="margin:0">New to press releases, or wondering where to send one? <a href="#" onclick="navigate('education'); openLesson('press-releases'); return false;">Read the full guide →</a></p>
        </div>

        <!-- Generator form -->
        <div class="card" id="press-form-card">
          <div class="card-title">Generate a press release</div>

          <div id="press-book-status" style="font-size:12px;padding:8px 10px;border-radius:5px;margin-bottom:14px;font-weight:500"></div>

          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Book (optional — adds book details to the AI context)</label>
              <select id="press-book-id" onchange="renderBookBanner('press-book-id','press-book-status')">
                <option value="0">No specific book</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Announcement type</label>
              <select id="press-announcement-type">
                <option value="launch">Book launch</option>
                <option value="award">Award or recognition</option>
                <option value="review">Notable review or endorsement</option>
                <option value="event">Author event or appearance</option>
                <option value="milestone">Sales or publishing milestone</option>
              </select>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Key details — what is this announcement about?</label>
            <textarea id="press-key-details" rows="4" placeholder="e.g. The paperback edition of &quot;Summer in Ashford&quot; releases May 15. The book was featured in The Columbus Dispatch and has sold 2,000 copies since its January launch."></textarea>
          </div>

          <div class="two-col">
            <div class="field-group">
              <label class="field-label">City, State (for dateline)</label>
              <input type="text" id="press-city-state" placeholder="Columbus, Ohio">
            </div>
            <div class="field-group">
              <label class="field-label">Embargo date (leave blank for immediate release)</label>
              <input type="date" id="press-embargo-date">
            </div>
          </div>

          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Media contact name</label>
              <input type="text" id="press-contact-name" placeholder="Jane Smith">
            </div>
            <div class="field-group">
              <label class="field-label">Media contact email</label>
              <input type="email" id="press-contact-email" placeholder="jane@example.com">
            </div>
          </div>

          <div class="actions">
            <button class="app-btn app-btn-green" id="press-generate-btn" onclick="generatePressRelease()">
              <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"></path></svg>
              Generate press release
            </button>
          </div>
        </div>

        <!-- Output panel — hidden until a generation completes -->
        <div class="card" id="press-output-card" style="display:none">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div class="card-title" style="margin:0">Your press release</div>
            <div style="display:flex;gap:8px">
              <button class="app-btn app-btn-outline app-btn-sm" onclick="copyPressRelease()">Copy</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="pressDownloadPdf()">Download PDF</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="pressDownloadWord()">Download Word</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="generatePressRelease()">Regenerate</button>
            </div>
          </div>
          <pre id="press-output" style="white-space:pre-wrap;font-family:var(--font-serif);font-size:14px;line-height:1.7;margin:0;padding:0;border:none;background:none"></pre>
          <div id="press-quota-note" style="margin-top:12px;font-size:12px;color:var(--ink-soft)"></div>
        </div>
      </div>

      <!-- EVENTS -->
      <div class="view" id="view-events">
        <div class="page-header"><h1>Events</h1><p>Readings, signings, podcasts, and appearances</p></div>

        <!-- Add/Edit form — hidden by default, shown when adding or editing -->
        <div class="card" id="ev-form-card" style="display:none">
          <div class="card-title" id="ev-form-title">Add event</div>
          <input type="hidden" id="ev-form-id" value="">

          <div class="two-col">
            <div class="field-group">
              <label class="field-label">Title</label>
              <input type="text" id="ev-form-title-input" placeholder="e.g. Launch reading at The Book Loft" maxlength="200">
            </div>
            <div class="field-group">
              <label class="field-label">Event type</label>
              <select id="ev-form-type">
                <option value="signing">Book signing</option>
                <option value="launchparty">Launch party</option>
                <option value="reading">Reading</option>
                <option value="podcast">Podcast appearance</option>
                <option value="bookclub">Book club visit</option>
                <option value="librarytalk">Library talk</option>
                <option value="festival">Book festival / fair</option>
                <option value="virtual">Virtual event</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Start date &amp; time</label>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
              <input type="date" id="ev-form-start-date" style="flex:1;min-width:160px;max-width:220px">
              <select id="ev-form-start-hour" style="width:auto;min-width:70px">
                <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option>
                <option value="5">5</option><option value="6">6</option>
                <option value="7" selected>7</option>
                <option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option>
              </select>
              <span style="font-weight:600">:</span>
              <select id="ev-form-start-min" style="width:auto;min-width:70px">
                <option value="00" selected>00</option><option value="15">15</option><option value="30">30</option><option value="45">45</option>
              </select>
              <select id="ev-form-start-ampm" style="width:auto;min-width:70px">
                <option value="AM">AM</option>
                <option value="PM" selected>PM</option>
              </select>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">End date &amp; time (optional)</label>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
              <input type="date" id="ev-form-end-date" style="flex:1;min-width:160px;max-width:220px">
              <select id="ev-form-end-hour" style="width:auto;min-width:70px">
                <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option>
                <option value="5">5</option><option value="6">6</option><option value="7">7</option>
                <option value="8" selected>8</option>
                <option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option>
              </select>
              <span style="font-weight:600">:</span>
              <select id="ev-form-end-min" style="width:auto;min-width:70px">
                <option value="00" selected>00</option><option value="15">15</option><option value="30">30</option><option value="45">45</option>
              </select>
              <select id="ev-form-end-ampm" style="width:auto;min-width:70px">
                <option value="AM">AM</option>
                <option value="PM" selected>PM</option>
              </select>
            </div>
            <div style="font-size:11px;color:var(--ink-soft);margin-top:4px">Leave date blank if you don't want to set an end time.</div>
          </div>

          <div class="field-group">
            <label class="field-label">Related book (optional)</label>
            <select id="ev-form-book"><option value="0">— None —</option></select>
          </div>

          <div class="field-group">
            <label class="field-label">Location</label>
            <input type="text" id="ev-form-location" placeholder="e.g. The Book Loft of German Village, Columbus OH (or Zoom URL for virtual)" maxlength="300">
            <label style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:12px;color:var(--ink-soft);cursor:pointer">
              <input type="checkbox" id="ev-form-virtual"> This is a virtual event
            </label>
          </div>

          <div class="field-group">
            <label class="field-label">Event page or ticket URL (optional)</label>
            <input type="url" id="ev-form-url" placeholder="https://..." maxlength="500">
          </div>

          <div class="field-group">
            <label class="field-label">Description (shown publicly when promoting)</label>
            <textarea id="ev-form-description" rows="3" placeholder="What attendees will hear, see, or get. Used by future 'promote this event' features."></textarea>
          </div>

          <div class="field-group">
            <label class="field-label">Private notes (for you only)</label>
            <textarea id="ev-form-notes" rows="2" placeholder="Contact name, parking, dress code, prep notes — anything you want to remember"></textarea>
          </div>

          <div class="field-group">
            <label class="field-label">Email reminders (sent to your account email)</label>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:13px">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" id="ev-form-remind-24h"> Email me 24 hours before
              </label>
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" id="ev-form-remind-1h"> Email me 1 hour before
              </label>
            </div>
            <div style="font-size:11px;color:var(--ink-soft);margin-top:6px">SMS and in-app reminders are coming soon.</div>
          </div>

          <div class="actions" style="display:flex;gap:8px">
            <button class="app-btn app-btn-green" onclick="evSave()">Save event</button>
            <button class="app-btn app-btn-outline" onclick="evHideForm()">Cancel</button>
          </div>
        </div>

        <!-- Add button -->
        <div class="actions" id="ev-add-row">
          <button class="app-btn app-btn-green" onclick="evShowForm()">+ Add event</button>
        </div>

        <!-- Upcoming events -->
        <div id="ev-upcoming-section" style="display:none;margin-top:14px">
          <div class="card-title" style="margin-bottom:8px">Upcoming</div>
          <div id="ev-upcoming-list"></div>
        </div>

        <!-- Past events -->
        <div id="ev-past-section" style="display:none;margin-top:20px">
          <div class="card-title" style="margin-bottom:8px;color:var(--ink-soft)">Past</div>
          <div id="ev-past-list"></div>
        </div>

        <!-- Empty state -->
        <div class="card" id="ev-empty" style="display:none">
          <div class="empty">No events added yet. Click <strong>+ Add event</strong> above to track your first signing, reading, or appearance.</div>
        </div>
      </div>

      <!-- SALES -->
      <div class="view" id="view-sales">
        <div class="page-header"><h1>Sales Channels</h1><p>Manage your retail and marketplace listings</p></div>
        <div class="card">
          <div class="card-title">Channels</div>
          <div class="row"><div class="row-left"><span class="pdot" style="background:#FF9900"></span><div><div>Amazon KDP</div><div class="row-meta">Print + eBook</div></div></div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('amazon-kdp')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://kdp.amazon.com','_blank')">Open KDP ↗</button></div></div>
          <div class="row"><div class="row-left"><span class="pdot" style="background:#FF9900"></span><div><div>Amazon Author Central</div><div class="row-meta">Author bio, photo, and Follow button on every book page</div></div></div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('amazon-author-central')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://authorcentral.amazon.com','_blank')">Open Author Central ↗</button></div></div>
          <div class="row"><div class="row-left"><span class="pdot" style="background:#FF9900"></span><div><div>Amazon Series page</div><div class="row-meta">Consolidated landing page for multi-book series — readers see all books in order</div></div></div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('amazon-series-page')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://www.amazon.com','_blank')">Search Amazon ↗</button></div></div>
          <div class="row" id="shopify-row"><div class="row-left"><span class="pdot" style="background:#96BF48"></span><div><div>Shopify</div><div class="row-meta" id="shopify-row-meta">Direct store</div></div></div><div id="shopify-row-actions" style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('shopify')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><span class="badge badge-gray" id="shopify-row-badge">Not connected</span><button class="app-btn app-btn-outline app-btn-sm" onclick="showShopifyConnect()">Connect</button></div></div>
          <div class="row" id="woo-row"><div class="row-left"><span class="pdot" style="background:#7F54B3"></span><div><div>WooCommerce</div><div class="row-meta" id="woo-row-meta">Self-hosted WordPress store</div></div></div><div id="woo-row-actions" style="display:flex;gap:8px;align-items:center"><span class="badge badge-gray" id="woo-row-badge">Not connected</span><button class="app-btn app-btn-outline app-btn-sm" onclick="showWooConnect()">Connect</button></div></div>
          <div class="row"><div class="row-left"><span class="pdot" style="background:#4285F4"></span><div><div>Google Merchant</div><div class="row-meta">Product listings</div></div></div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('google-merchant')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://merchants.google.com','_blank')">Open Google Merchant ↗</button></div></div>
        </div>
      </div>

      <!-- DISTRIBUTION -->
      <div class="view" id="view-distribution">
        <div class="page-header"><h1>Distribution</h1><p>Ingram Spark and fulfillment</p></div>
        <div class="card">
          <div class="card-title" style="display:flex;align-items:center;justify-content:space-between;gap:8px">Ingram Spark<div style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('ingramspark')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://www.ingramspark.com','_blank')">Manage ↗</button></div></div>
          <p style="margin:8px 0 0;font-size:13px;color:var(--ink-soft)">Global print-on-demand distribution to 40,000+ bookstores, libraries, and online retailers worldwide. Setup help walks you through the 10-step process; Manage opens your IngramSpark dashboard.</p>
        </div>
      </div>

      <!-- PRODUCTION -->
      <div class="view" id="view-production">
        <div class="page-header"><h1>Production / Print</h1><p>Manage book files and print orders</p></div>
        <div class="card" style="border-left:3px solid var(--accent)">
        <div class="card-title">Which one should I use?</div>
        <p style="margin:0 0 8px;font-size:14px;line-height:1.7;color:var(--ink)">
          <strong>Amazon KDP author copies</strong> — order copies of your own book through KDP and have them shipped to you. Convenient for small numbers when you are not in a hurry.
        </p>
        <p style="margin:0 0 8px;font-size:14px;line-height:1.7;color:var(--ink)">
          <strong>IngramSpark</strong> — print on demand aimed at <em>distribution</em>, so bookshops and libraries can order your book. Use it to be stocked, not to get copies for yourself.
        </p>
        <p style="margin:0 0 8px;font-size:14px;line-height:1.7;color:var(--ink)">
          <strong>Zip Print &amp; Copy</strong> — a short to medium-run (20–5,000 books) printer for when you want books quickly: a signing, a fair, review copies, or stock for projects, personal sales, local shops. Better materials and cheaper per copy than print-on-demand in most cases.
        </p>
        <p style="margin:10px 0 0;font-size:13.5px;color:var(--ink-mid);line-height:1.6">
          Amazon and IngramSpark are managed on their own sites — those buttons take you there to sign in. Zip Print is quoted here in the app.
        </p>
      </div>

      <div class="card">
          <div class="card-title">Print partners</div>
          <div class="row"><div class="row-left"><div><div>Zip Print &amp; Copy</div><div class="row-meta">Local print partner — Columbus, OH</div></div></div><button class="app-btn app-btn-green app-btn-sm" onclick="navigate('print-quote')">Get a quote →</button></div>
          <div class="row"><div class="row-left"><div><div>Amazon KDP author copies</div><div class="row-meta">Your own copies at printing cost</div></div></div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('amazon-kdp')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://kdp.amazon.com','_blank')">Manage ↗</button></div></div><div class="row"><div class="row-left"><div><div>Ingram Spark POD</div><div class="row-meta">Print on demand</div></div></div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('ingramspark')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://www.ingramspark.com','_blank')">Manage ↗</button></div></div>
        </div>
      </div>

      <!-- PRINT QUOTE -->
      <div class="view" id="view-print-quote">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate('production')">← Production / Print</button>
        </div>
        <div class="page-header">
          <h1>Book Printing Quote and Order Form</h1>
          <p>Get an instant estimate and submit your order to Zip Print &amp; Copy — Columbus, OH</p>
        </div>

        <div style="background:var(--paper-soft);border-left:3px solid var(--accent);padding:14px 18px;margin-bottom:18px;border-radius:6px;font-size:14px;line-height:1.65;color:var(--ink)">
          <p style="margin:0 0 8px"><strong>What this is for.</strong> When you need a small or medium print run for an author event, book signing, special edition, or supplemental inventory beyond what KDP and IngramSpark provide, fill in the specs below. You'll get an instant estimate on the right as you make selections — then submit the form to place the order. Most jobs ship in one to two weeks.</p>
          <p style="margin:0"><strong>About Zip Print &amp; Copy.</strong> Zip Print &amp; Copy (Grandview Heights, OH) is one of the largest book producers in Central Ohio — a family-owned shop that has printed books and short-run materials for authors, publishers, and small businesses for over 20 years. They offer digital printing with a full range of bindings: <strong>perfect bound, saddle stitch, coil, and comb</strong>. Unlike print-on-demand services that print one book at a time, Zip Print specializes in short and medium runs — typically 25 to 500 copies — where you can talk to a real person about paper choice, binding quality, and turnaround. Ideal for launch events, signed editions, and any time you want hand-controlled inventory rather than POD economics.</p>
        </div>

        <div class="pq-outer-grid">

          <!-- FORM COLUMN -->
          <div class="card">
            <div style="padding:24px">

              <div class="field-group">
                <label class="field-label">Project name</label>
                <input type="text" id="pq-project-name" placeholder="e.g. Morsch Memoir — First Run" maxlength="200">
              </div>

              <div style="border-top:1px solid var(--ink-faint);margin:20px 0"></div>
              <div style="font-weight:600;margin-bottom:16px;color:var(--ink)">Contact Information <span style="font-weight:400;font-size:13px;color:var(--ink-soft)">* required</span></div>

              <div class="two-col">
                <div class="field-group">
                  <label class="field-label">Full name *</label>
                  <input type="text" id="pq-name" placeholder="Your name">
                  <div class="field-error" id="pq-nameError">Please enter your full name.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Company / Organization</label>
                  <input type="text" id="pq-company" placeholder="Optional">
                </div>
                <div class="field-group">
                  <label class="field-label">Email *</label>
                  <input type="email" id="pq-email" placeholder="you@example.com">
                  <div class="field-error" id="pq-emailError">Please enter a valid email.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Phone *</label>
                  <input type="text" id="pq-phone" placeholder="614-555-0000">
                  <div class="field-error" id="pq-phoneError">Please enter a phone number.</div>
                </div>
              </div>

              <div style="border-top:1px solid var(--ink-faint);margin:20px 0"></div>
              <div style="font-weight:600;margin-bottom:16px;color:var(--ink)">Book Specifications</div>

              <div class="pq-specs-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field-group">
                  <label class="field-label">Page size *</label>
                  <select id="pq-trimSize" onchange="pqScheduleEstimate()">
                    <option value="">Choose trim size</option>
                    <option value="5.5x8.5">5½″ × 8½″</option>
                    <option value="6x9">6″ × 9″</option>
                    <option value="8.5x8.5">8½″ × 8½″</option>
                    <option value="9x9">9″ × 9″</option>
                    <option value="8.5x11">8½″ × 11″</option>
                    <option value="11x8.5">11″ × 8½″ (Landscape)</option>
                  </select>
                  <div class="field-error" id="pq-trimSizeError">Please choose a page size.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Number of copies *</label>
                  <input type="number" id="pq-quantity" min="1" placeholder="e.g. 50" oninput="pqScheduleEstimate();pqEnforceRules()">
                  <div class="field-error" id="pq-quantityError">Please enter the number of copies.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Free proof *</label>
                  <select id="pq-freeProof">
                    <option value="">Choose</option>
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                  </select>
                  <div class="field-error" id="pq-freeProofError">Please choose a free proof option.</div>
                  <span id="pq-freeProofHint" style="font-size:12px;color:var(--ink-soft);margin-top:4px;display:block">Available for orders of 10+ copies.</span>
                </div>
                <div class="field-group">
                  <label class="field-label">Binding type *</label>
                  <select id="pq-binding" onchange="pqScheduleEstimate()">
                    <option value="">Choose binding</option>
                    <option value="3hole">3 Hole Punch</option>
                    <option value="comb">Comb</option>
                    <option value="coil">Coil</option>
                    <option value="perfect">Perfect Bound (Paperback)</option>
                    <option value="saddle">Saddle Stitch</option>
                  </select>
                  <div class="field-error" id="pq-bindingError">Please choose a binding type.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Interior B&amp;W pages *</label>
                  <input type="number" id="pq-bwPages" min="0" placeholder="0" oninput="pqScheduleEstimate()">
                  <div class="field-error" id="pq-pageCountsError">Enter pages in at least one field.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Interior color pages *</label>
                  <input type="number" id="pq-colorPages" min="0" placeholder="0" oninput="pqScheduleEstimate()">
                </div>
                <div class="field-group">
                  <label class="field-label">Printing sides *</label>
                  <select id="pq-printingSide" onchange="pqScheduleEstimate()">
                    <option value="">Choose</option>
                    <option value="double">Double sided</option>
                    <option value="single">Single sided</option>
                  </select>
                  <div class="field-error" id="pq-printingSideError">Please choose single or double sided.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Interior paper *</label>
                  <select id="pq-interiorPaper" onchange="pqScheduleEstimate()">
                    <option value="">Choose paper</option>
                    <option value="24matteivory">24# Matte Ivory</option>
                    <option value="24_60matte">24/60# Matte</option>
                    <option value="80coatedmatte">80# Coated Matte</option>
                    <option value="80coatedgloss">80# Coated Gloss</option>
                    <option value="100coatedgloss">100# Coated Gloss</option>
                  </select>
                  <div class="field-error" id="pq-interiorPaperError">Please choose interior paper.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Cover stock *</label>
                  <select id="pq-coverStock" onchange="pqScheduleEstimate()">
                    <option value="">Choose cover stock</option>
                    <option value="67matte">67# Matte Card Stock</option>
                    <option value="80coatedgloss">80# Coated Gloss</option>
                    <option value="100matte">100# Matte Card Stock</option>
                    <option value="124coated">124# Coated Card Stock</option>
                    <option value="145coated">145# Coated Card Stock</option>
                  </select>
                  <div class="field-error" id="pq-coverStockError">Please choose cover stock.</div>
                </div>
                <div class="field-group">
                  <label class="field-label">Cover print</label>
                  <select id="pq-coverPrint">
                    <option value="">Choose</option>
                    <option value="color">Color cover</option>
                    <option value="bw">B&amp;W cover</option>
                  </select>
                </div>
                <div class="field-group">
                  <label class="field-label">UV gloss coating</label>
                  <select id="pq-uvGloss" onchange="pqScheduleEstimate()">
                    <option value="">Choose</option>
                    <option value="no">No</option>
                    <option value="yes">Yes (+$0.30/copy, 50+ only)</option>
                  </select>
                </div>
                <div class="field-group">
                  <label class="field-label">Turnaround</label>
                  <select id="pq-turnaround" onchange="pqScheduleEstimate()">
                    <option value="">Choose</option>
                    <option value="standard">Standard</option>
                    <option value="rush">Rush (+10%)</option>
                  </select>
                </div>
                <div class="field-group">
                  <label class="field-label">Delivery</label>
                  <select id="pq-shipping">
                    <option value="">Choose</option>
                    <option value="pickup">Pickup — Grandview Heights</option>
                    <option value="ups">UPS / USPS shipping</option>
                  </select>
                </div>
              </div>

              <div style="border-top:1px solid var(--ink-faint);margin:20px 0"></div>
              <div style="font-weight:600;margin-bottom:16px;color:var(--ink)">File Upload</div>
              <div class="field-group">
                <label class="field-label">Upload your file(s)</label>
                <div id="pq-file-inputs">
                  <input type="file" id="pq-file-0" class="pq-file-input" style="margin-bottom:8px">
                </div>
                <span style="font-size:12px;color:var(--ink-soft);display:block;margin-top:6px">PDF, DOC, DOCX, InDesign, AI, EPS, PSD, TIFF, JPG, PNG, ZIP. Max 150 MB per file. After choosing a file, another field appears automatically.<br>Large files can also be sent via <a href="https://zipprintcopy.com/Send/" target="_blank">Zip Cloud</a> — send us an email after uploading.</span>
              </div>

              <div style="border-top:1px solid var(--ink-faint);margin:20px 0"></div>
              <div class="field-group">
                <label class="field-label">Project notes</label>
                <textarea id="pq-notes" placeholder="Deadlines, special instructions, shipping details, or anything else we should know…" style="min-height:100px;resize:vertical"></textarea>
              </div>

              <!-- honeypot -->
              <div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">
                <input type="text" id="pq-website" tabindex="-1" autocomplete="off">
              </div>
            </div>
          </div>

          <!-- ESTIMATE COLUMN -->
          <div class="pq-estimate-sticky" style="position:sticky;top:20px">
            <div class="card">
              <div style="background:var(--surface-alt,#FAFAF7);border-bottom:1px solid var(--ink-faint);padding:16px 20px;border-radius:var(--radius,8px) var(--radius,8px) 0 0">
                <div class="card-title" style="margin:0">Instant Estimate</div>
              </div>
              <div style="padding:20px">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed var(--ink-faint);font-size:14px"><span>Total copies</span><strong id="pq-sumQty">0</strong></div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed var(--ink-faint);font-size:14px"><span>Binding</span><strong id="pq-sumBinding">Not selected</strong></div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed var(--ink-faint);font-size:14px"><span>B&amp;W pages</span><strong id="pq-sumBW">0</strong></div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed var(--ink-faint);font-size:14px"><span>Color pages</span><strong id="pq-sumColor">0</strong></div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed var(--ink-faint);font-size:14px"><span>Print cost per copy</span><strong id="pq-unitPrice">$0.00</strong></div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed var(--ink-faint);font-size:14px"><span>Adjusted per copy</span><strong id="pq-adjustedUnitPrice" style="color:var(--accent-green,#2D6A4F)">$0.00</strong></div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed var(--ink-faint);font-size:14px"><span>Full order cost</span><strong id="pq-basePrice">$0.00</strong></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:15px;font-weight:700"><span>Adjusted total</span><strong id="pq-totalPrice" style="color:var(--accent-green,#2D6A4F)">$0.00</strong></div>

                <div style="margin-top:14px;padding:12px;background:#F8F8F5;border:1px solid var(--ink-faint);border-radius:4px;font-size:12px;color:var(--ink-soft)">
                  Cover choice, shipping, and file review may affect final pricing. Orders of 1–9 copies add a flat $5.00.
                </div>

                <div style="margin-top:20px;font-size:13px;color:var(--ink-soft);line-height:1.7">
                  <strong style="display:block;margin-bottom:6px;color:var(--ink)">Zip Print &amp; Copy</strong>
                  1091 West 1st Ave<br>
                  Grandview Heights, Ohio 43212<br>
                  <a href="tel:+16144850721">614-485-0721</a><br>
                  <a href="mailto:info@zipprintcopy.com">info@zipprintcopy.com</a><br>
                  <a href="https://zipprintcopy.com/" target="_blank">zipprintcopy.com</a>
                </div>
              </div>
            </div>
          </div>

        </div><!-- end grid -->

        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
          <button type="button" class="app-btn app-btn-green" id="pq-submit-btn" onclick="pqSubmit()">Submit quote request</button>
          <button type="button" class="app-btn app-btn-outline" onclick="pqReset()">Reset form</button>
        </div>
        <div id="pq-status" style="margin-top:10px;font-size:14px;font-weight:500"></div>
      </div>

      <!-- ADS -->
      <div class="view" id="view-ads">
        <!-- ⚠ This header is visible to EVERY user. The posting agent below it
             is admin-only (hidden here, and ads.php 403s non-admins), so this
             copy must describe only what a subscriber actually gets: the
             platform links. Do not mention the agent here. -->
        <div class="page-header"><h1>Ads</h1><p>Manage advertising across all platforms</p></div>

        <!-- Agent status + kill switch. Admin-only; hidden for everyone else. -->
        <div id="ads-admin" style="display:none">

          <div class="card">
            <div class="card-title">Posting agent</div>
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:6px">
              <label class="ads-check" style="font-weight:600">
                <input type="checkbox" id="ads-agent-enabled" onchange="adsToggleAgent(this.checked)">
                Agent enabled
              </label>
              <span id="ads-agent-note" style="font-size:14px;color:var(--ink-soft)"></span>
            </div>
            <p style="font-size:14px;line-height:1.6;color:var(--ink-soft);margin:6px 0 0">
              Turn this off and nothing posts, anywhere, immediately. Pacing is enforced on the
              server — the agent asks for work and is told no. It never decides for itself how
              often to post.
            </p>
          </div>

          <div class="card">
            <div class="card-title">Destinations &amp; pacing</div>
            <!-- ⚠ This panel had no explanation and the on/off here looks like
                 the Activate button on an ad. Bob switched TikTok off believing
                 it would stop a NEW ad from going there — it silenced a running
                 one instead, and the new ad had never been scheduled to TikTok
                 at all. Say plainly what this controls. -->
            <p style="font-size:14px;line-height:1.6;color:var(--ink-soft);margin:0 0 14px">
              <strong>Where posting is allowed</strong> — one row per account.
              Switching one off stops <strong>every</strong> ad to that account.
              It does not change which ads exist, and it is not how you stop a
              single ad: for that, use <strong>Pause</strong> in the Ad library below.
              An ad only ever posts where you scheduled it.
            </p>
            <div id="ads-platform-rows" style="font-size:15px">Loading…</div>
            <p style="font-size:13.5px;line-height:1.6;color:var(--ink-soft);margin:12px 0 0">
              Two posts a day sits at roughly 13% of the tightest published ceiling (TikTok's
              ~15/day). Volume is not what gets accounts flagged — mechanically regular timing is,
              so every scheduled post is jittered. A platform that reports a checkpoint or
              action-block is switched off here and stays off until you clear it.
            </p>
          </div>

          <div class="card">
            <div class="card-title" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
              <span>Ad library</span>
              <button class="app-btn app-btn-green app-btn-sm" onclick="adsNewAsset()">New ad</button>
            </div>
            <div id="ads-asset-rows" style="font-size:15px">Loading…</div>
          </div>

          <!-- Detail panel for the selected ad -->
          <div class="card" id="ads-detail" style="display:none">
            <div class="card-title" id="ads-detail-title">Ad</div>

            <!-- One cut per shape. Deliberately two slots rather than one file
                 machine-converted: padding a 9:16 out to 16:9 gives a narrow
                 strip between wide bars. A properly framed cut always wins. -->
            <div style="margin-bottom:18px">
              <div style="font-size:16px;font-weight:600;margin-bottom:2px">Video cuts</div>
              <p style="font-size:13.5px;color:var(--ink-soft);margin:0 0 12px">
                Upload the shape each platform wants. If one is missing the agent falls back to
                the other and pads it, which works but looks worse.
              </p>

              <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:10px">
                <span style="min-width:190px;font-size:14px"><strong>Vertical 9:16</strong><br>
                  <span style="color:var(--ink-soft);font-size:13px">TikTok, Reels, Instagram, Threads, Facebook</span></span>
                <input type="file" id="ads-master-vertical" accept="video/mp4,video/quicktime" style="font-size:14px">
                <button class="app-btn app-btn-outline app-btn-sm" onclick="adsUploadMaster('vertical')">Upload</button>
                <span id="ads-note-vertical" style="font-size:13.5px;color:var(--ink-soft)"></span>
              </div>

              <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                <span style="min-width:190px;font-size:14px"><strong>Landscape 16:9</strong><br>
                  <span style="color:var(--ink-soft);font-size:13px">X, LinkedIn</span></span>
                <input type="file" id="ads-master-landscape" accept="video/mp4,video/quicktime" style="font-size:14px">
                <button class="app-btn app-btn-outline app-btn-sm" onclick="adsUploadMaster('landscape')">Upload</button>
                <span id="ads-note-landscape" style="font-size:13.5px;color:var(--ink-soft)"></span>
              </div>
            </div>

            <div style="border-top:1px solid var(--rule);padding-top:16px">
              <div style="font-size:16px;font-weight:600;margin-bottom:8px">Caption pool</div>
              <p style="font-size:14px;line-height:1.6;color:var(--ink-soft);margin:0 0 12px">
                Generate a batch, then edit, delete and tick the ones you want. <strong>Nothing
                posts until it is approved.</strong> The card line is burned onto the opening
                frame of the video, so keep it to a few words that land in under a second.
              </p>
              <!-- The angle is the highest-leverage field on this page. Given one
                   line, the generator falls back on the most generic pitch it can
                   infer from the ad's name; given a real brief, it stays on subject. -->
              <label for="ads-direction" style="display:block;font-size:14px;font-weight:600;margin-bottom:4px">
                The angle — who is this for, and what problem do they have?
              </label>
              <textarea id="ads-direction" rows="3" placeholder="e.g. Most indie authors have never made an ebook version of their book at all — they don't know how, or assume it's expensive and technical, so they've just never done it. This does it for them. Write to authors who have a print book and no ebook." style="width:100%;font-size:14px;line-height:1.5;margin-bottom:10px"></textarea>
              <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
                <select id="ads-gen-count" style="font-size:14px;width:auto">
                  <option value="10">10</option>
                  <option value="15" selected>15</option>
                  <option value="20">20</option>
                </select>
                <button class="app-btn app-btn-green app-btn-sm" id="ads-gen-btn" onclick="adsGenerate()">Generate captions</button>
              </div>
              <div id="ads-variant-rows" style="font-size:15px"></div>

              <!-- Hashtags belong to the AD, not to each caption — varying them
                   per post fragments the reach they exist to earn. Generated
                   from the same angle above, so the tags describe the subject
                   rather than the product category. -->
              <div style="border-top:1px solid var(--rule);margin-top:16px;padding-top:14px">
                <label for="ads-hashtags" style="display:block;font-size:14px;font-weight:600;margin-bottom:4px">
                  Hashtags — added to the end of every caption for this ad
                </label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                  <input type="text" id="ads-hashtags" placeholder="#IndieAuthor #SelfPublishing …" style="flex:1;min-width:280px;font-size:14px">
                  <button class="app-btn app-btn-outline app-btn-sm" id="ads-tags-btn" onclick="adsGenerateHashtags()">Generate from the angle</button>
                </div>
                <p style="font-size:13px;color:var(--ink-soft);margin:6px 0 0">
                  Saved with the button below. LinkedIn and Reddit dislike hashtags — leave this
                  empty if you add those platforms.
                </p>
              </div>
              <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
                <button class="app-btn app-btn-green app-btn-sm" onclick="adsSaveVariants()">Save changes</button>
                <button class="app-btn app-btn-outline app-btn-sm" onclick="adsAddManual()">Add my own line</button>
              </div>
            </div>

            <div style="border-top:1px solid var(--rule);padding-top:16px;margin-top:18px">
              <div style="font-size:16px;font-weight:600;margin-bottom:8px">Schedule</div>
              <div id="ads-schedule-platforms" style="font-size:15px;margin-bottom:12px"></div>
              <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                <label style="font-size:14px">Days
                  <select id="ads-schedule-days" style="font-size:14px;width:auto">
                    <option value="3">3</option>
                    <option value="7" selected>7</option>
                    <option value="14">14</option>
                    <option value="30">30</option>
                  </select>
                </label>
                <button class="app-btn app-btn-green app-btn-sm" onclick="adsSchedule()">Add to queue</button>
                <span id="ads-schedule-note" style="font-size:14px;color:var(--ink-soft)"></span>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-title" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
              <span>Queue</span>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="adsClearPending()">Clear all pending</button>
            </div>
            <div id="ads-queue-rows" style="font-size:15px">Loading…</div>
            <div style="margin-top:16px;border-top:1px solid var(--rule);padding-top:14px">
              <div style="font-size:15px;font-weight:600;margin-bottom:8px">Attempts that didn't post</div>
              <div id="ads-queue-history" style="font-size:15px"></div>
            </div>
          </div>

          <div class="card">
            <div class="card-title">Recent posts</div>
            <div id="ads-recent-rows" style="font-size:15px">Loading…</div>
            <p style="font-size:13.5px;line-height:1.6;color:var(--ink-soft);margin:12px 0 0">
              Clicks come from the tracked link each caption carries, with the platforms' own
              preview bots filtered out. A platform whose clicks-per-post falls off a cliff while
              posts keep succeeding is being throttled — that is the signal to back off.
            </p>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Ad platforms</div>
          <div class="row"><div class="row-left"><span class="pdot" style="background:#4285F4"></span>Google Ads</div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://ads.google.com','_blank')">Open Google Ads ↗</button></div></div>
          <div class="row"><div class="row-left"><span class="pdot" style="background:#1877F2"></span>Facebook Ads</div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn-help" onclick="showSetupHelp('facebook-ads')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://www.facebook.com/adsmanager','_blank')">Open Meta Ads Manager ↗</button></div></div>
          <div class="row"><div class="row-left"><span class="pdot" style="background:#FF9900"></span>Amazon Ads</div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://advertising.amazon.com','_blank')">Open Amazon Ads ↗</button></div></div>
          <div class="row"><div class="row-left"><span class="pdot" style="background:#888"></span>Ingram Advertising</div><div style="display:flex;gap:8px;align-items:center"><button class="app-btn app-btn-outline app-btn-sm" onclick="window.open('https://www.ingramspark.com','_blank')">Set up ↗</button></div></div>
        </div>
      </div>

      <!-- WEBSITE -->
      <div class="view" id="view-website">
        <div class="page-header"><h1>Website</h1><p>Connect your WordPress site and publish posts straight from here</p></div>

        <div style="background:var(--paper-soft);border-left:3px solid var(--accent);padding:14px 18px;margin-bottom:20px;border-radius:6px;font-size:14px;line-height:1.65;color:var(--ink)">
          <p style="margin:0"><strong>Your website is the one place online you own.</strong> Social platforms are rented land — algorithms shift, accounts get suspended, audiences disappear overnight. A WordPress site is yours: it hosts your buy links, your blog, your newsletter signup, and your About page, all under your domain. Connect your existing WordPress site below and you can draft blog posts inside this portal and publish straight to it — no copy-paste, no logging in to two places. Don't have a site yet? Start in the <a href="#" onclick="navigate('wordpress');return false;">WordPress for Authors</a> section for a step-by-step setup with recommended hosts.</p>
        </div>

        <!-- Plugin download — always visible (needed before connecting; handy for re-installs) -->
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;background:var(--white);border:1px solid var(--accent);border-left:4px solid var(--accent);border-radius:8px;padding:14px 18px;margin-bottom:20px">
          <div style="font-size:13px;line-height:1.55;color:var(--ink-mid);max-width:560px"><strong style="color:var(--ink)">Need the site plugin?</strong> Install Elite Publishing plugin on your WordPress site to build your author site and let this app publish to it. New to this? The <a href="#" onclick="navigate('wordpress');return false;">WordPress for Authors</a> page walks you through every step.</div>
          <a class="app-btn app-btn-green" href="api/wp_plugin.php?action=download" download="" style="text-decoration:none;white-space:nowrap">↓ Download plugin</a>
        </div>

        <div class="card" id="wp-plugin-explainer" style="border-left:3px solid var(--accent)">
        <div class="card-title">What Elite Publishing plugin does</div>
        <p style="margin:0 0 10px;font-size:14px;line-height:1.7;color:var(--ink)">
          It turns a blank WordPress into a finished author website. Installing it builds your pages —
          home, books, blog, about, contact — adds a simple control panel inside WordPress, and applies
          one of ten genre styles so the site looks like it belongs to your book rather than to a
          template. It also keeps your titles in step with this app: add or edit a book here and it
          appears on the site, no retyping.
        </p>
        <div style="font-size:14px;line-height:1.8;color:var(--ink)">
          <strong>Installing it takes about two minutes:</strong>
          <ol style="margin:6px 0 0;padding-left:20px">
            <li>Click <strong>Download plugin</strong> above — you get a <code>.zip</code> file. Don’t unzip it.</li>
            <li>In your WordPress admin, go to <strong>Plugins → Add New Plugin → Upload Plugin</strong>.</li>
            <li>Choose the zip, click <strong>Install Now</strong>, then <strong>Activate</strong>.</li>
            <li>Come back here and connect your site using either method below.</li>
          </ol>
        </div>
        <p style="margin:10px 0 0;font-size:13.5px;color:var(--ink-mid);line-height:1.6">
          You only install it once. After that it updates itself when you visit your site’s
          Manage My Site page, so you never have to download it again.
          Needs WordPress 6.0 or newer.
        </p>
      </div>

      <!-- LOADING STATE -->
        <div class="card" id="wp-loading" style="display: none;">
          <div class="card-title">Loading…</div>
          <p>Checking your WordPress connection.</p>
        </div>

        <!-- NOT CONNECTED STATE: education + connect form -->
        <div class="card" id="wp-not-connected" style="">
          <div class="card-title">Connect your WordPress site</div>

          <div id="wp-oneclick" style="margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid var(--line,#e5e0d8)">
            <p style="margin:0 0 10px"><strong>The easy way — connect in one click.</strong> Enter your website address and we'll take you to your site to approve, then bring you right back. You never type or paste a password.</p>
            <div class="field-group">
              <label class="field-label">Your website address</label>
              <input type="url" id="wp-oneclick-url" placeholder="https://yourname.com">
            </div>
            <button class="app-btn" id="wp-oneclick-btn" style="margin-top:8px">Connect my website</button>
            <div id="wp-oneclick-msg" style="display:none;margin-top:10px;padding:10px;border-radius:4px"></div>
          </div>

          <div style="font-weight:600;color:var(--ink-soft);margin-bottom:12px">Or connect by hand</div>

          <details open style="margin-bottom:14px">
            <summary style="cursor:pointer;font-weight:600">First time? Start here — what you'll need</summary>
            <div style="padding:10px 0 4px 0">
              <p>To connect your site, we need three things:</p>
              <ol>
                <li><strong>Your site URL</strong> — the home page of your site, e.g. <code>https://yourname.com</code></li>
                <li><strong>Your WordPress username</strong> — the one you log in with</li>
                <li>An <strong>Application Password</strong> — a special, revokable password we'll walk you through creating below</li>
              </ol>
              <p><strong>Heads-up:</strong> your site must be <em>self-hosted WordPress</em> (wordpress.org) running version 5.6 or later. That covers nearly every modern WordPress site. WordPress.com's free tier doesn't support this kind of connection.</p>
            </div>
          </details>

          <details style="margin-bottom:14px">
            <summary style="cursor:pointer;font-weight:600">What is an Application Password, and why not my regular password?</summary>
            <div style="padding:10px 0 4px 0">
              <p>Application Passwords are WordPress's built-in way to let other apps (like this one) talk to your site safely. They differ from your login password in three important ways:</p>
              <ul>
                <li><strong>Revokable.</strong> Delete the Application Password any time and this app loses access instantly — without changing your main password or logging you out anywhere else.</li>
                <li><strong>Limited to apps.</strong> Application Passwords can't be used to log into your WordPress admin dashboard. Even if one leaks, nobody can use it to sign in as you.</li>
                <li><strong>Named per app.</strong> You'll see "Elite Publishing" listed in your WP admin under Users → Profile, so you always know what's connected.</li>
              </ul>
              <p>We store the password encrypted on our server using AES-256. We never log it, and it's only decrypted at the moment we send a request to your site on your behalf.</p>
            </div>
          </details>

          <details style="margin-bottom:14px">
            <summary style="cursor:pointer;font-weight:600">Step-by-step: create an Application Password</summary>
            <div style="padding:10px 0 4px 0">
              <ol>
                <li>Log in to your WordPress admin dashboard (usually <code>yoursite.com/wp-admin</code>).</li>
                <li>In the left sidebar, click <strong>Users → Profile</strong>.</li>
                <li>Scroll all the way to the bottom. You'll see a section labeled <strong>Application Passwords</strong>.</li>
                <li>In the "New Application Password Name" field, type something memorable like <code>Elite Publishing</code>.</li>
                <li>Click <strong>Add New Application Password</strong>.</li>
                <li>WordPress will show you a password that looks like <code>xxxx xxxx xxxx xxxx xxxx xxxx</code>. <strong>Copy it now</strong> — you can't see it again. The spaces are part of the password; keep them.</li>
                <li>Paste it into the form below, along with your site URL and username.</li>
              </ol>
              <p><strong>Don't see the Application Passwords section?</strong></p>
              <ul>
                <li>Your WordPress is older than 5.6 — update it.</li>
                <li>A security plugin (iThemes Security, Wordfence, etc.) has disabled the feature — check that plugin's settings.</li>
                <li>Your site is on WordPress.com's free tier — you'll need the Business plan or higher, or move to self-hosted.</li>
              </ul>
            </div>
          </details>

          <details style="margin-bottom:14px">
            <summary style="cursor:pointer;font-weight:600">Troubleshooting: "Authentication failed" errors</summary>
            <div style="padding:10px 0 4px 0">
              <p>If the test fails with a 401 error even though your password is correct, your web host may be stripping the <code>Authorization</code> header before it reaches WordPress. This is a known issue on <strong>GoDaddy Managed WordPress</strong> and some shared hosts.</p>
              <p><strong>The fix:</strong> add these lines to the very top of your site's <code>.htaccess</code> file:</p>
<pre style="background:#f4f4f4;padding:10px;border-radius:4px;font-size:12px;overflow:auto">&lt;IfModule mod_rewrite.c&gt;
  RewriteEngine on
  RewriteCond %{HTTP:Authorization} ^(.*)
  RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
&lt;/IfModule&gt;</pre>
              <p>Not comfortable editing <code>.htaccess</code>? Contact your host's support and ask them to "allow Authorization headers to reach WordPress." Most hosts will do it on request.</p>
            </div>
          </details>

          <div class="field-group">
            <label class="field-label">Your site URL</label>
            <input type="url" id="wp-input-url" placeholder="https://yourname.com">
            <div style="font-size:11px;color:var(--ink-soft);margin-top:3px">The home page of your WordPress site — not the admin URL.</div>
          </div>

          <div class="field-group">
            <label class="field-label">WordPress username</label>
            <input type="text" id="wp-input-user" autocomplete="off">
          </div>

          <div class="field-group">
            <label class="field-label">Application Password</label>
            <input type="text" id="wp-input-pass" autocomplete="off" placeholder="xxxx xxxx xxxx xxxx xxxx xxxx">
            <div style="font-size:11px;color:var(--ink-soft);margin-top:3px">Keep the spaces — they're part of the password.</div>
          </div>

          <div style="display:flex;gap:8px;margin-top:10px">
            <button class="app-btn app-btn-outline" id="wp-test-btn">Test connection</button>
            <button class="app-btn" id="wp-save-btn">Save &amp; connect</button>
          </div>
          <div id="wp-connect-msg" style="display: block; margin-top: 10px; padding: 10px; border-radius: 4px; background: rgb(253, 234, 234); color: rgb(180, 35, 24);">Could not load connection info: Not logged in</div>
        </div>

        <!-- CONNECTED STATE -->
        <div id="wp-connected" style="display:none">
          <div class="card">
            <div class="card-title">Connected site</div>
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
              <div>
                <div style="font-size:18px;font-weight:600" id="wp-site-name">—</div>
                <div style="margin:4px 0"><a id="wp-site-url" href="#" target="_blank" rel="noopener">—</a></div>
                <div style="font-size:13px;color:var(--ink-soft)">
                  <div><strong>Username:</strong> <span id="wp-username">—</span></div>
                  <div><strong>WP version:</strong> <span id="wp-version">—</span></div>
                  <div><strong>Last verified:</strong> <span id="wp-last-verified">—</span></div>
                </div>
              </div>
              <div style="display:flex;gap:8px;flex-shrink:0">
                <button class="app-btn app-btn-outline app-btn-sm" id="wp-reverify-btn">Verify</button>
                <button class="app-btn app-btn-outline app-btn-sm" id="wp-disconnect-btn">Disconnect</button>
              </div>
            </div>
            <div id="wp-connected-msg" style="display:none;margin-top:10px;padding:10px;border-radius:4px"></div>
          </div>

          <div class="card">
            <div class="card-title">Compose a post</div>

            <details style="margin-bottom:14px;background:#f8f8f8;border-radius:var(--radius);padding:8px 12px">
              <summary style="cursor:pointer;font-weight:600;font-size:13px">How image alignment &amp; text wrapping work</summary>
              <div style="padding:10px 0 4px 0;font-size:13px;line-height:1.55">
                <p style="margin:0 0 8px 0"><strong>Two things together control how an image looks: alignment and position.</strong></p>

                <p style="margin:0 0 4px 0;font-weight:600">Alignment (where the image sits in the column):</p>
                <ul style="margin:0 0 10px 18px;padding:0">
                  <li><strong>None</strong> — image takes its own line, full width, no text beside it.</li>
                  <li><strong>Left</strong> — image floats to the left edge; body text wraps along the image's right side.</li>
                  <li><strong>Right</strong> — image floats to the right edge; body text wraps along the image's left side.</li>
                  <li><strong>Center</strong> — image is centered on its own line, no text beside it.</li>
                </ul>

                <p style="margin:0 0 4px 0;font-weight:600">Position (where the image lands in your post):</p>
                <ul style="margin:0 0 10px 18px;padding:0">
                  <li><strong>At top</strong> — beginning of the post, before all body text. Best for Left/Right wrap.</li>
                  <li><strong>At cursor</strong> — wherever your cursor was last in the body. Click in the body first to choose the spot.</li>
                  <li><strong>At bottom</strong> — end of the post, after all body text. Use this only with None or Center alignment, since wrapping needs text <em>after</em> the image to wrap around.</li>
                </ul>

                <p style="margin:0 0 4px 0;font-weight:600">Size:</p>
                <ul style="margin:0 0 10px 18px;padding:0">
                  <li><strong>Small</strong> ~150px — small thumbnail, good for icons or sign-offs.</li>
                  <li><strong>Medium</strong> ~300px — fits comfortably alongside text.</li>
                  <li><strong>Large</strong> — fills most of the column (or ~60% if floated for wrap).</li>
                  <li><strong>Full</strong> — full column width, never wraps.</li>
                </ul>

                <p style="margin:0;color:var(--ink-soft);font-size:12px"><strong>Tip:</strong> for the classic blog look — image on the right with body copy wrapping around it — pick <em>Right</em>, <em>Medium</em> or <em>Large</em>, and <em>At top</em>. Then write your body underneath as usual.</p>
              </div>
            </details>

            <div class="field-group">
              <label class="field-label">Title</label>
              <input type="text" id="wp-compose-title" placeholder="Your post title">
            </div>

            <!-- Featured image -->
            <div class="field-group">
              <label class="field-label">Featured image <span style="font-weight:400;color:var(--ink-soft);font-size:11px">(optional)</span></label>
              <div id="wp-featured-empty">
                <button class="app-btn app-btn-outline app-btn-sm" id="wp-featured-pick-btn" type="button">Choose image…</button>
                <span style="font-size:11px;color:var(--ink-soft);margin-left:8px">JPEG, PNG, GIF, or WebP. Large photos are resized to 2000px before upload.</span>
              </div>
              <div id="wp-featured-set" style="display:none;align-items:center;gap:12px">
                <img id="wp-featured-thumb" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--ink-faint)">
                <div style="flex:1;min-width:0">
                  <div id="wp-featured-name" style="font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">—</div>
                  <div style="font-size:11px;color:var(--ink-soft);margin-top:2px">Will be set as the post's featured image.</div>
                </div>
                <button class="app-btn app-btn-outline app-btn-sm" id="wp-featured-remove-btn" type="button">Remove</button>
              </div>
              <input type="file" id="wp-featured-file-input" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none">
            </div>

            <div class="field-group">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                <label class="field-label" style="margin:0">Body (Markdown)</label>
                <button class="app-btn app-btn-outline app-btn-sm" id="wp-insert-image-btn" type="button" style="padding:3px 10px;font-size:11px">+ Insert image</button>
              </div>
              <input type="file" id="wp-inline-file-input" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none">

              <!-- Insert-image panel: alignment + size + upload -->
              <div id="wp-insert-panel" style="display:none;border:1px solid var(--ink-faint);border-radius:var(--radius);padding:12px;margin-bottom:8px;background:#f8f8f8">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                  <div style="font-size:13px;font-weight:600">Insert image</div>
                  <button class="app-btn app-btn-outline app-btn-sm" id="wp-insert-cancel-btn" type="button" style="padding:2px 8px;font-size:11px">Cancel</button>
                </div>

                <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;margin-bottom:10px">
                  <div>
                    <div style="font-size:11px;color:var(--ink-soft);margin-bottom:4px">Alignment</div>
                    <div role="group" id="wp-align-group" style="display:inline-flex;gap:0;border:1px solid var(--ink-faint);border-radius:var(--radius);overflow:hidden">
                      <button type="button" class="wp-align-btn" data-align="alignnone" style="padding:4px 10px;font-size:12px;border:0;background:#fff;cursor:pointer">None</button>
                      <button type="button" class="wp-align-btn" data-align="alignleft" style="padding:4px 10px;font-size:12px;border:0;border-left:1px solid var(--ink-faint);background:#fff;cursor:pointer">Left</button>
                      <button type="button" class="wp-align-btn" data-align="alignright" style="padding:4px 10px;font-size:12px;border:0;border-left:1px solid var(--ink-faint);background:#fff;cursor:pointer">Right</button>
                      <button type="button" class="wp-align-btn" data-align="aligncenter" style="padding:4px 10px;font-size:12px;border:0;border-left:1px solid var(--ink-faint);background:#fff;cursor:pointer">Center</button>
                    </div>
                  </div>
                  <div>
                    <div style="font-size:11px;color:var(--ink-soft);margin-bottom:4px">Size</div>
                    <div role="group" id="wp-size-group" style="display:inline-flex;gap:0;border:1px solid var(--ink-faint);border-radius:var(--radius);overflow:hidden">
                      <button type="button" class="wp-size-btn" data-size="size-thumbnail" style="padding:4px 10px;font-size:12px;border:0;background:#fff;cursor:pointer">Small</button>
                      <button type="button" class="wp-size-btn" data-size="size-medium" style="padding:4px 10px;font-size:12px;border:0;border-left:1px solid var(--ink-faint);background:#fff;cursor:pointer">Medium</button>
                      <button type="button" class="wp-size-btn" data-size="size-large" style="padding:4px 10px;font-size:12px;border:0;border-left:1px solid var(--ink-faint);background:#fff;cursor:pointer">Large</button>
                      <button type="button" class="wp-size-btn" data-size="size-full" style="padding:4px 10px;font-size:12px;border:0;border-left:1px solid var(--ink-faint);background:#fff;cursor:pointer">Full</button>
                    </div>
                  </div>
                </div>

                <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;margin-bottom:10px">
                  <div>
                    <div style="font-size:11px;color:var(--ink-soft);margin-bottom:4px">Position</div>
                    <div role="group" id="wp-pos-group" style="display:inline-flex;gap:0;border:1px solid var(--ink-faint);border-radius:var(--radius);overflow:hidden">
                      <button type="button" class="wp-pos-btn" data-pos="cursor" style="padding:4px 10px;font-size:12px;border:0;background:#fff;cursor:pointer">At cursor</button>
                      <button type="button" class="wp-pos-btn" data-pos="top" style="padding:4px 10px;font-size:12px;border:0;border-left:1px solid var(--ink-faint);background:#fff;cursor:pointer">At top</button>
                      <button type="button" class="wp-pos-btn" data-pos="bottom" style="padding:4px 10px;font-size:12px;border:0;border-left:1px solid var(--ink-faint);background:#fff;cursor:pointer">At bottom</button>
                    </div>
                  </div>
                </div>

                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                  <button class="app-btn app-btn-green app-btn-sm" id="wp-insert-pick-btn" type="button">Choose image…</button>
                  <span id="wp-insert-help" style="font-size:11px;color:var(--ink-soft)">For wrapping to work, the image must be placed <em>before</em> the body text it wraps around. Click in the body where you want the image, then choose your file.</span>
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:stretch">
                <textarea id="wp-compose-md" rows="14" placeholder="Write your post in Markdown.

## Heading
**bold** and *italic*

- bullet list
- another item

![alt text](image url)
[a link](https://example.com)" style="resize:vertical;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13px;line-height:1.5;padding:10px;border:1px solid var(--ink-faint);border-radius:var(--radius);width:100%"></textarea>
                <div id="wp-compose-preview" class="wp-preview" style="border:1px solid var(--ink-faint);border-radius:var(--radius);padding:10px 14px;background:#fafafa;font-size:14px;line-height:1.55;overflow:auto;min-height:240px"><em style="color:var(--ink-soft)">Preview appears here as you type.</em></div>
              </div>
              <div style="font-size:11px;color:var(--ink-soft);margin-top:6px">
                Supports headings, <strong>**bold**</strong>, <em>*italic*</em>, <code>`code`</code>, links, images, lists (including nested), tables, blockquotes, code fences, and ~~strikethrough~~.
              </div>
            </div>

            <!-- Categories + Tags row -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:4px">
              <div class="field-group" style="margin-bottom:0">
                <label class="field-label">Categories</label>
                <div id="wp-categories-loading" style="font-size:12px;color:var(--ink-soft)">Loading…</div>
                <div id="wp-categories-empty" style="font-size:12px;color:var(--ink-soft);display:none">No categories on your site yet. Create them in WP admin → Posts → Categories.</div>
                <div id="wp-categories-list" style="display:none;max-height:120px;overflow:auto;border:1px solid var(--ink-faint);border-radius:var(--radius);padding:8px"></div>
              </div>
              <div class="field-group" style="margin-bottom:0">
                <label class="field-label">Tags</label>
                <input type="text" id="wp-compose-tags" placeholder="fiction, writing tips, reviews">
                <div style="font-size:11px;color:var(--ink-soft);margin-top:3px">Comma-separated. New tags will be created on your site.</div>
              </div>
            </div>

            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:14px">
              <label class="field-label" style="margin:0">Status</label>
              <select id="wp-compose-status" style="padding:6px 8px">
                <option value="publish">Publish immediately</option>
                <option value="draft">Save as draft</option>
                <option value="pending">Pending review</option>
              </select>
              <div style="flex:1"></div>
              <button class="app-btn app-btn-outline" id="wp-compose-clear-btn">Clear</button>
              <button class="app-btn app-btn-green" id="wp-compose-publish-btn">Publish to WordPress</button>
            </div>
            <div id="wp-compose-msg" style="display:none;margin-top:10px;padding:10px;border-radius:4px"></div>
          </div>

          <div class="card">
            <div class="card-title">Recent posts <span style="font-size:12px;font-weight:400;color:var(--ink-soft)">(published from here)</span></div>
            <div id="wp-history-empty" style="font-size:13px;color:var(--ink-soft)">No posts yet. Publish your first one above.</div>
            <div id="wp-history-list" style="display:none"></div>
          </div>
        </div>
      </div>
		
      <!-- CONTACTS -->
      <div class="view" id="view-contacts">
        <div class="page-header"><h1>Contacts / Lists</h1><p>Mailing lists, media contacts, ARC readers, and booksellers</p></div>

        <!-- List hygiene (prominent — protects deliverability & your sending account) -->
        <div class="card" style="margin-bottom:16px;border-left:4px solid var(--accent)">
          <div class="card-title">Keep your list clean — protect your delivery and your sending account</div>
          <div style="font-size:14px;line-height:1.6">
            <p style="margin:0 0 12px">A clean list is the single biggest lever on whether your campaigns reach real readers. When too many messages bounce or get marked as spam, mailbox providers like Gmail and Outlook start routing your mail to the spam folder — and your sending service (Mailgun) can throttle or even <strong>suspend your account</strong> until the list is cleaned up. Getting reinstated is slow and painful, so it's worth keeping the list healthy from the start.</p>
            <p style="margin:0 0 12px"><strong>What this app already does automatically:</strong> it screens for bad or parked domains, common typos, and disposable/throwaway addresses, and any address that hard-bounces or files a spam complaint is added to your Suppression list below so you never email it twice. What it <em>can't</em> do is confirm a specific mailbox is still live — that needs a mail-server check our host blocks. That's the gap a validation service fills, and it's worth running <strong>before you import a large, old, purchased, or event-collected list</strong> you haven't emailed recently.</p>
            <p style="margin:0 0 8px"><strong>Services we recommend</strong> — a few dollars per thousand addresses, and you only do it occasionally:</p>
            <ul style="margin:0 0 12px;padding-left:20px">
              <li style="margin-bottom:4px"><a href="https://neverbounce.com/" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline">NeverBounce</a> — inexpensive, pay-as-you-go, and accurate. The one we'd start with.</li>
              <li style="margin-bottom:4px"><a href="https://www.zerobounce.net/" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline">ZeroBounce</a> — adds activity and abuse scoring; a bit pricier.</li>
              <li style="margin-bottom:4px"><a href="https://kickbox.com/" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline">Kickbox</a> — clean interface with free trial credits to test.</li>
              <li style="margin-bottom:4px"><a href="https://www.millionverifier.com/" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline">MillionVerifier</a> — the budget option for big lists.</li>
            </ul>
            <p style="margin:0"><strong>How:</strong> Use the <strong>Export CSV</strong> button below, run the file through one of the services above, then re-import only the addresses it marks deliverable.</p>
          </div>
        </div>

        <div class="actions">
          <button class="app-btn app-btn-green" onclick="openContactModal()">+ Add contact</button>
          <button class="app-btn app-btn-outline" onclick="openListModal()">+ New list</button>
          <button class="app-btn app-btn-outline" onclick="openImportModal()">Import CSV</button>
          <button class="app-btn app-btn-outline" onclick="exportContactsCsv()">Export CSV</button>
        </div>

        <div class="stat-grid" id="contact-stats" style="margin-bottom:16px">
          <div class="stat-card"><div class="label">Total contacts</div><div class="value" id="stat-total">—</div><div class="sub">All contacts</div></div>
          <div class="stat-card"><div class="label">Opted in</div><div class="value" id="stat-optedin">—</div><div class="sub">Can receive email</div></div>
          <div class="stat-card"><div class="label">Unsubscribed</div><div class="value" id="stat-unsub">—</div><div class="sub">Will not receive email</div></div>
          <div class="stat-card"><div class="label">Bounced</div><div class="value" id="stat-bounced">—</div><div class="sub">Bad email addresses</div></div>
        </div>

        <div class="card" style="margin-bottom:16px">
          <div class="card-title">Filters</div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="text" id="contact-search" placeholder="Search by email or name…" oninput="debouncedLoadContacts()" style="flex:1;min-width:180px">
            <select id="contact-type-filter" onchange="loadContacts()">
              <option value="">All types</option>
              <option value="reader">Readers</option>
              <option value="media">Media</option>
              <option value="bookseller">Booksellers</option>
              <option value="arc_reader">ARC readers</option>
              <option value="other">Other</option>
            </select>
            <select id="contact-status-filter" onchange="loadContacts()">
              <option value="">All statuses</option>
              <option value="opted_in">Opted in</option>
              <option value="unconfirmed">Unconfirmed</option>
              <option value="unsubscribed">Unsubscribed</option>
              <option value="bounced">Bounced</option>
              <option value="complained">Complained</option>
            </select>
            <select id="contact-list-filter" onchange="loadContacts()">
              <option value="">All lists</option>
            </select>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="listBulkOptIn()" title="Mark every contact in the selected list as Opted in (whole list, not just this page)">Opt in entire list</button>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Contacts</div>

          <div id="bulk-bar" style="display:none;padding:12px;margin-bottom:10px;background:#F5ECCC;border-radius:6px;flex-direction:column;gap:10px">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
              <strong id="bulk-count">0 selected</strong>
              <div style="display:flex;gap:8px">
                <button class="app-btn app-btn-red app-btn-sm" onclick="bulkDelete()">Delete selected</button>
                <button class="app-btn app-btn-outline app-btn-sm" onclick="clearSelection()">Clear selection</button>
              </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding-top:8px;border-top:1px solid rgba(0,0,0,0.08)">
              <span style="font-size:12px;color:var(--ink-soft);min-width:60px">Add to:</span>
              <select id="bulk-list-select" style="flex:1;min-width:140px">
                <option value="">Pick a list…</option>
              </select>
              <button class="app-btn app-btn-green app-btn-sm" onclick="bulkAddToList()">Add to list</button>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <span style="font-size:12px;color:var(--ink-soft);min-width:60px">Status:</span>
              <select id="bulk-status-select" style="flex:1;min-width:140px">
                <option value="">Pick a status…</option>
                <option value="opted_in">Opted in</option>
                <option value="unconfirmed">Unconfirmed</option>
                <option value="unsubscribed">Unsubscribed</option>
              </select>
              <button class="app-btn app-btn-green app-btn-sm" onclick="bulkChangeStatus()">Change status</button>
            </div>
          </div>

          <div id="select-all-row" style="display:none;padding:6px 10px;font-size:12px;color:var(--ink-soft)">
            <label style="cursor:pointer"><input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll()"> Select all on this page</label>
          </div>

          <div id="list-filter-banner" style="display:none;padding:10px 12px;margin-bottom:10px;background:#E8F0E5;border-left:3px solid var(--accent);border-radius:4px;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
            <div>
              <span style="font-size:12px;color:var(--ink-soft)">Viewing list:</span>
              <strong id="list-filter-banner-name" style="margin-left:4px"></strong>
            </div>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="clearListFilter()">Show all contacts</button>
          </div>

          <div id="contacts-table-wrap">
            <div class="empty">Loading…</div>
          </div>
        </div>

        <div class="card" style="margin-top:16px">
          <div class="card-title">Your lists</div>
          <div id="lists-wrap">
            <div class="empty">No lists yet — create one to group contacts</div>
          </div>
        </div>

        <div class="card" style="margin-top:16px">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;cursor:pointer" onclick="toggleSuppressions()">
            <div>
              <div class="card-title" style="margin:0">Suppression list</div>
              <div style="font-size:12px;color:var(--ink-soft);margin-top:2px">Emails that won't receive any campaigns — bounces, spam complaints, and unsubscribes</div>
            </div>
            <div style="display:flex;gap:10px;align-items:center">
              <span id="suppression-count" style="font-size:12px;color:var(--ink-soft)"></span>
              <span id="suppression-toggle-icon" style="font-size:14px;color:var(--ink-soft)">▸</span>
            </div>
          </div>
          <div id="suppressions-wrap" style="display:none;margin-top:12px">
            <div class="empty">Loading…</div>
          </div>
        </div>
      </div>

      <!-- MAILGUN WIZARD MODAL -->
      <div class="modal-backdrop" id="mailgun-wizard" style="display:none">
        <div class="modal" style="max-width:720px">
          <div class="modal-header">
            <h3>Set up email sending</h3>
            <button class="modal-close" onclick="closeWizardModal()">×</button>
          </div>

          <div style="padding:14px 22px 0;display:flex;gap:4px;justify-content:center">
            <div class="wiz-dot" id="wiz-dot-1">1</div>
            <div class="wiz-line"></div>
            <div class="wiz-dot" id="wiz-dot-2">2</div>
            <div class="wiz-line"></div>
            <div class="wiz-dot" id="wiz-dot-3">3</div>
            <div class="wiz-line"></div>
            <div class="wiz-dot" id="wiz-dot-4">4</div>
            <div class="wiz-line"></div>
            <div class="wiz-dot" id="wiz-dot-5">5</div>
          </div>

          <div class="modal-body">

            <!-- STEP 1: Signup -->
            <div id="wiz-step-1" style="display:none">
              <h3 style="font-family:var(--font-serif);margin:0 0 10px">Create your Mailgun account</h3>
              <p>Mailgun is the service that actually sends your emails. You'll pay them directly for their sending infrastructure (free to start, ~$15/month once you grow). This app then uses your Mailgun account to compose and track campaigns.</p>
              <p><strong>Here's what you'll do:</strong></p>
              <ol style="padding-left:22px">
                <li>Click the Mailgun signup link below. It opens in a new tab.</li>
                <li>Create a Mailgun account using your business email.</li>
                <li>When Mailgun asks for a credit card, it's required even for the free plan — they don't charge unless you exceed the free tier.</li>
                <li>Once you're logged into Mailgun, come back here.</li>
              </ol>
              <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap">
                <a href="https://signup.mailgun.com/" target="_blank" rel="noopener" class="app-btn app-btn-green" style="text-decoration:none">Sign up for Mailgun ↗</a>
                <button class="app-btn app-btn-outline" onclick="showWizardStep(2)">I already have an account</button>
              </div>
            </div>

            <!-- STEP 2: API Key -->
            <div id="wiz-step-2" style="display:none">
              <h3 style="font-family:var(--font-serif);margin:0 0 10px">Paste your Mailgun API key</h3>
              <p>You'll find your API key in Mailgun's dashboard.</p>
              <ol style="padding-left:22px">
                <li>In Mailgun, click your account menu in the top-right corner.</li>
                <li>Select <strong>API Security</strong> (or <strong>API Keys</strong> in some versions).</li>
                <li>Find <strong>Private API key</strong> and click <strong>Reveal</strong>.</li>
                <li>Copy the entire key and paste it below.</li>
              </ol>
              <p>We'll test the key before saving, so you'll know right away if there's a typo.</p>
              <div class="field-group">
                <label class="field-label">Mailgun private API key</label>
                <input type="text" id="wiz-api-key" placeholder="Paste the full API key here" style="font-family:monospace;font-size:12px">
              </div>
              <div style="padding:10px;background:#F0F7E8;border-left:3px solid var(--accent);border-radius:4px;font-size:12px;color:var(--ink-soft)">
                <strong>Security note:</strong> This key is encrypted before it's stored. Only your account can use it. You can disconnect anytime.
              </div>
              <div id="wiz-step2-error" style="color:#B94141;font-size:13px;margin-top:10px"></div>
              <div style="display:flex;gap:10px;margin-top:16px;justify-content:space-between">
                <button class="app-btn app-btn-outline" onclick="showWizardStep(1)">← Back</button>
                <button class="app-btn app-btn-green" id="wiz-step2-save" onclick="wizardValidateAndSaveKey()">Test and save</button>
              </div>
            </div>

            <!-- STEP 3: Domain -->
            <div id="wiz-step-3" style="display:none">
              <h3 style="font-family:var(--font-serif);margin:0 0 10px">What domain will you send from?</h3>
              <p>Your emails will come from <em>your</em> domain (like <code>jane@janedoeauthor.com</code>), not ours. This is more professional and protects your sender reputation.</p>
              <p><strong>If you own a domain already</strong> (for your author website, for example), enter just the root — like <code>janedoeauthor.com</code>. We'll automatically use <code>mail.janedoeauthor.com</code> as the sending subdomain, so your regular website email keeps working.</p>
              <p><strong>If you don't own a domain yet</strong>, you'll need one before continuing. Domains cost about $12/year. Good places to register: Namecheap (cleanest interface), Cloudflare (cheapest), or GoDaddy. Pick something that represents you — your author name or pen name. Register, come back, continue.</p>

              <div style="padding:12px 14px;margin:14px 0;background:#FFF5E0;border-left:3px solid #D4A017;border-radius:4px">
                <strong style="color:#7A6417">Heads up about existing email:</strong>
                <p style="margin:6px 0 4px;font-size:13px">If you're currently receiving email at this domain (for example via Google Workspace, Zoho, or another provider), Mailgun's signup process may have already added <code>mxa.mailgun.org</code> and <code>mxb.mailgun.org</code> MX records to your domain. Those records would redirect your incoming mail to Mailgun and break your existing email setup.</p>
                <p style="margin:4px 0 0;font-size:13px">After this wizard finishes, check your domain's DNS for any <code>mailgun.org</code> MX records and delete them. Your real email providers' MX records should remain untouched.</p>
              </div>

              <div class="field-group">
                <label class="field-label">Your domain</label>
                <input type="text" id="wiz-domain" placeholder="janedoeauthor.com">
                <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">No http://, no www. Just the root domain.</div>
              </div>
              <div id="wiz-step3-error" style="color:#B94141;font-size:13px;margin-top:10px"></div>
              <div style="display:flex;gap:10px;margin-top:16px;justify-content:space-between">
                <button class="app-btn app-btn-outline" onclick="showWizardStep(2)">← Back</button>
                <button class="app-btn app-btn-green" id="wiz-step3-save" onclick="wizardAddDomain()">Add domain</button>
              </div>
            </div>

            <!-- STEP 4: DNS records -->
            <div id="wiz-step-4" style="display:none">
              <h3 style="font-family:var(--font-serif);margin:0 0 10px">Add these DNS records</h3>
              <p>Now add these records to your domain's DNS, at whichever registrar you used (GoDaddy, Namecheap, Cloudflare, etc.).</p>
              <p style="font-size:13px;color:var(--ink-soft)">Sending domain: <strong id="wiz-step4-domain" style="font-family:monospace"></strong></p>

              <div id="wiz-dns-records" style="margin:14px 0"></div>

              <div id="wiz-dns-notice" style="padding:10px;background:#FAFAF7;border-radius:6px;margin:10px 0;font-size:13px"></div>

              <div id="wiz-poll-paused" style="display:none;padding:10px;background:#FFF5E0;border-left:3px solid #D4A017;border-radius:4px;font-size:13px;margin:10px 0">
                Automatic checking paused. DNS is taking longer than 10 minutes — that's normal, some registrars are slow.
                <button class="app-btn app-btn-outline app-btn-sm" onclick="wizardResumePolling()" style="margin-top:6px">Resume checking</button>
              </div>

              <div style="padding:12px;background:#FAFAF7;border-radius:6px;margin-top:14px">
                <div style="font-size:13px;font-weight:500;margin-bottom:6px">Need help adding DNS records?</div>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                  <button class="app-btn app-btn-outline app-btn-sm" onclick="wizardShowRegistrarHelp('godaddy')">GoDaddy</button>
                  <button class="app-btn app-btn-outline app-btn-sm" onclick="wizardShowRegistrarHelp('namecheap')">Namecheap</button>
                  <button class="app-btn app-btn-outline app-btn-sm" onclick="wizardShowRegistrarHelp('cloudflare')">Cloudflare</button>
                  <button class="app-btn app-btn-outline app-btn-sm" onclick="wizardShowRegistrarHelp('other')">Other registrar</button>
                </div>
                <div id="wiz-registrar-help" style="display:none;margin-top:12px;padding:12px;background:#fff;border-radius:4px;font-size:13px;line-height:1.5"></div>
              </div>

              <div style="display:flex;gap:10px;margin-top:16px;justify-content:space-between;flex-wrap:wrap">
                <button class="app-btn app-btn-outline" onclick="showWizardStep(3)">← Back</button>
                <div style="display:flex;gap:8px">
                  <button class="app-btn app-btn-outline" id="wiz-manual-check-btn" onclick="wizardManualCheck()">Check now</button>
                  <button class="app-btn app-btn-outline" onclick="closeWizardModal()">I'll come back later</button>
                  <button class="app-btn app-btn-green" id="wiz-step4-continue" onclick="showWizardStep(5)" style="display:none">Continue →</button>
                </div>
              </div>
            </div>

            <!-- STEP 5: Sender profile -->
            <div id="wiz-step-5" style="display:none">
              <h3 style="font-family:var(--font-serif);margin:0 0 10px">Who are your emails from?</h3>
              <p>This is the last step. These details appear in every email you send, and some are legally required.</p>

              <div class="field-group">
                <label class="field-label">From name *</label>
                <input type="text" id="wiz-from-name" placeholder="Jane Doe">
                <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">The name readers see in their inbox. Usually your author name or pen name.</div>
              </div>
              <div class="field-group">
                <label class="field-label">From email *</label>
                <input type="email" id="wiz-from-email" placeholder="hello@janedoeauthor.com">
                <div style="font-size:12px;color:var(--ink-soft);margin-top:4px" id="wiz-step5-domain-hint">Must be at your verified domain</div>
              </div>
              <div class="field-group">
                <label class="field-label">Reply-to email (optional)</label>
                <input type="email" id="wiz-reply-to" placeholder="Leave blank to use From email">
                <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Where readers replying to your emails will be directed.</div>
              </div>
              <div class="field-group">
                <label class="field-label">Physical mailing address *</label>
                <textarea id="wiz-address" rows="2" placeholder="PO Box 123, Anytown, State 12345"></textarea>
                <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Required by US law (CAN-SPAM) for every marketing email. A PO Box is fine.</div>
              </div>

              <div id="wiz-step5-error" style="color:#B94141;font-size:13px;margin-top:10px"></div>

              <div style="display:flex;gap:10px;margin-top:16px;justify-content:space-between;flex-wrap:wrap">
                <div id="wiz-disconnect-wrap" style="display:none">
                  <button class="app-btn app-btn-red app-btn-sm" onclick="disconnectMailgun()">Disconnect Mailgun</button>
                </div>
                <button class="app-btn app-btn-green" id="wiz-step5-save" onclick="wizardSaveProfile()">Save and finish</button>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- CONTACT MODAL -->
      <div class="modal-backdrop" id="contact-modal" style="display:none">
        <div class="modal">
          <div class="modal-header">
            <h3 id="contact-modal-title">Add contact</h3>
            <button class="modal-close" onclick="closeContactModal()">×</button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="c-id">
            <div class="field-group">
              <label class="field-label">Email *</label>
              <input type="email" id="c-email" placeholder="reader@example.com">
            </div>
            <div style="display:flex;gap:10px">
              <div class="field-group" style="flex:1">
                <label class="field-label">First name</label>
                <input type="text" id="c-first">
              </div>
              <div class="field-group" style="flex:1">
                <label class="field-label">Last name</label>
                <input type="text" id="c-last">
              </div>
            </div>
            <div style="display:flex;gap:10px">
              <div class="field-group" style="flex:1">
                <label class="field-label">Type</label>
                <select id="c-type">
                  <option value="reader">Reader</option>
                  <option value="media">Media contact</option>
                  <option value="bookseller">Bookseller</option>
                  <option value="arc_reader">ARC reader</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="field-group" style="flex:1">
                <label class="field-label">Consent status</label>
                <select id="c-status">
                  <option value="opted_in">Opted in</option>
                  <option value="unconfirmed">Unconfirmed</option>
                  <option value="unsubscribed">Unsubscribed</option>
                </select>
              </div>
            </div>
            <div class="field-group">
              <label class="field-label">Notes</label>
              <textarea id="c-notes" rows="3" placeholder="How you know them, interests, etc."></textarea>
            </div>
            <div class="field-group" id="c-lists-wrap" style="display:none">
              <label class="field-label">Lists this contact belongs to</label>
              <div id="c-lists-checkboxes" style="display:flex;flex-direction:column;gap:6px;max-height:150px;overflow-y:auto;padding:8px;border:1px solid var(--ink-faint);border-radius:6px">
                <div style="font-size:12px;color:var(--ink-soft)">No lists yet — create a list first</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="app-btn app-btn-outline" onclick="closeContactModal()">Cancel</button>
            <button class="app-btn app-btn-red" id="c-delete-btn" onclick="deleteContact()" style="display:none">Delete</button>
            <button class="app-btn app-btn-green" onclick="saveContact()">Save</button>
          </div>
        </div>
      </div>

      <!-- LIST MODAL -->
      <div class="modal-backdrop" id="list-modal" style="display:none">
        <div class="modal">
          <div class="modal-header">
            <h3>New list</h3>
            <button class="modal-close" onclick="closeListModal()">×</button>
          </div>
          <div class="modal-body">
            <div class="field-group">
              <label class="field-label">List name *</label>
              <input type="text" id="l-name" placeholder="Street Team, Newsletter, Media contacts…">
            </div>
            <div class="field-group">
              <label class="field-label">Description</label>
              <textarea id="l-desc" rows="2"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="app-btn app-btn-outline" onclick="closeListModal()">Cancel</button>
            <button class="app-btn app-btn-green" onclick="saveList()">Create list</button>
          </div>
        </div>
      </div>

      <!-- IMPORT CSV MODAL -->
      <div class="modal-backdrop" id="import-modal" style="display:none">
        <div class="modal" style="max-width:640px">
          <div class="modal-header">
            <h3>Import contacts from CSV</h3>
            <button class="modal-close" onclick="closeImportModal()">×</button>
          </div>
          <div class="modal-body">
            <div id="import-step-1">
              <div class="field-group">
                <label class="field-label">Choose CSV file (max 1 MB, ~5,000 contacts)</label>
                <input type="file" id="import-file" accept=".csv" onchange="onImportFileSelected()">
                <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">
                  Export from MailChimp, Substack, ConvertKit, or any tool — or create your own. Must include at least an email column.
                </div>
              </div>
            </div>

            <div id="import-step-2" style="display:none">
              <div class="field-group">
                <label class="field-label" style="display:flex;align-items:center;gap:8px">
                  <input type="checkbox" id="import-skip-header" checked onchange="refreshImportPreview()" style="margin:0">
                  First row contains column headers
                </label>
              </div>

              <div class="field-group">
                <label class="field-label">Preview (first 3 data rows)</label>
                <div id="import-preview" style="font-family:monospace;font-size:11px;border:1px solid var(--ink-faint);border-radius:6px;padding:8px;background:#FAFAF7;overflow-x:auto;white-space:nowrap"></div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="field-group">
                  <label class="field-label">Email column *</label>
                  <select id="import-email-col"></select>
                </div>
                <div class="field-group">
                  <label class="field-label">First name column</label>
                  <select id="import-first-col"><option value="">— none —</option></select>
                </div>
                <div class="field-group">
                  <label class="field-label">Last name column</label>
                  <select id="import-last-col"><option value="">— none —</option></select>
                </div>
                <div class="field-group">
                  <label class="field-label">Notes column</label>
                  <select id="import-notes-col"><option value="">— none —</option></select>
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="field-group">
                  <label class="field-label">Contact type for all</label>
                  <select id="import-type">
                    <option value="reader">Reader</option>
                    <option value="media">Media contact</option>
                    <option value="bookseller">Bookseller</option>
                    <option value="arc_reader">ARC reader</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="field-group">
                  <label class="field-label">Add to list (optional)</label>
                  <select id="import-list"><option value="">— no list —</option></select>
                </div>
              </div>

              <div class="field-group">
                <label class="field-label">Consent status for all</label>
                <select id="import-consent">
                  <option value="opted_in">Opted in — they have explicitly signed up to receive your emails</option>
                  <option value="unconfirmed" selected>Unconfirmed — I will send a confirmation email before marketing to them</option>
                  <option value="unsubscribed">Unsubscribed — import for reference only, do not email</option>
                </select>
              </div>

              <div class="field-group">
                <label class="field-label">Source description</label>
                <input type="text" id="import-source" placeholder="e.g. MailChimp export 2026-04, Website signup form, Book signing at Powell's">
                <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Helps you remember where these contacts came from — important for compliance records.</div>
              </div>

              <div style="padding:12px;background:#FFF5E0;border-left:3px solid #D4A017;border-radius:4px;margin-top:8px">
                <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:13px;line-height:1.5">
                  <input type="checkbox" id="import-confirm-consent" style="margin-top:3px">
                  <span><strong>I confirm I have the legal right to email these contacts.</strong> I understand I am responsible for compliance with CAN-SPAM, GDPR, and other applicable laws. Importing contacts who have not explicitly opted in may violate these laws and damage my sender reputation.</span>
                </label>
              </div>
            </div>

            <div id="import-step-3" style="display:none">
              <div id="import-results"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="app-btn app-btn-outline" onclick="closeImportModal()" id="import-cancel-btn">Cancel</button>
            <button class="app-btn app-btn-green" id="import-submit-btn" onclick="submitImport()" style="display:none">Import contacts</button>
            <button class="app-btn app-btn-green" id="import-done-btn" onclick="closeImportModal()" style="display:none">Done</button>
          </div>
        </div>
      </div>

      <!-- ANALYTICS -->
      <div class="view" id="view-analytics">
        <div class="page-header"><h1>Analytics</h1><p>Performance across all your campaigns</p></div>
        <div class="stat-grid">
          <div class="stat-card"><div class="label">Email open rate</div><div class="value">—</div><div class="sub">No campaigns yet</div></div>
          <div class="stat-card"><div class="label">Click-through</div><div class="value">—</div><div class="sub">No campaigns yet</div></div>
          <div class="stat-card"><div class="label">Ad ROI</div><div class="value">—</div><div class="sub">No ads running</div></div>
          <div class="stat-card"><div class="label">Social followers</div><div class="value">—</div><div class="sub">Connect platforms</div></div>
        </div>
        <div class="card"><div class="empty">Connect platforms to see analytics</div></div>
      </div>

      <!-- EDUCATION -->
      <div class="view" id="view-education">
        <div id="edu-library">
          <div class="page-header"><h1>Learn</h1><p>Master the portal and grow your book's audience</p></div>
          <div class="edu-hero">
            <div class="edu-hero-left">
              <h2>Your marketing education</h2>
              <p>Short, practical guides on using the portal and promoting your books. No fluff — just what works for independent authors.</p>
            </div>
            <div class="edu-progress-wrap">
              <div class="big-num" id="edu-completed-count">0 / 13</div>
              <div class="big-label">lessons completed</div>
              <div class="edu-progress-track"><div class="edu-progress-fill" id="edu-progress-bar" style="width:0%"></div></div>
            </div>
          </div>
          <div class="edu-section-label">Getting started — using the portal</div>
          <div class="edu-grid" id="edu-grid-beginner"></div>
          <div class="edu-section-label">Book marketing — grow your audience</div>
          <div class="edu-grid" id="edu-grid-intermediate"></div>
          <div class="edu-section-label">Advanced — ads, sales, and distribution</div>
          <div class="edu-grid" id="edu-grid-advanced"></div>
        </div>
        <div id="edu-lesson" class="lesson-viewer">
          <button class="lesson-back" onclick="closeLesson()">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M9 2L4 7l5 5"></path></svg>
            Back to lessons
          </button>
          <div class="lesson-header">
            <div class="lesson-meta">
              <span class="edu-pill" id="lesson-pill">Beginner</span>
              <span class="edu-time"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="6" cy="6" r="5"></circle><path d="M6 3v3l2 1.5"></path></svg><span id="lesson-time"></span></span>
            </div>
            <h1 id="lesson-title"></h1>
            <p class="lesson-desc" id="lesson-desc"></p>
          </div>
          <div class="lesson-body" id="lesson-body"></div>
          <div class="lesson-action-bar">
            <div class="lesson-nav-btns">
              <button class="app-btn app-btn-outline" id="lesson-prev-btn" onclick="prevLesson()">← Previous</button>
              <button class="app-btn app-btn-outline" id="lesson-next-btn" onclick="nextLesson()">Next →</button>
            </div>
            <button class="app-btn app-btn-green" id="lesson-complete-btn" onclick="markComplete()">Mark as complete ✓</button>
          </div>
        </div>
      </div>

      <!-- WORDPRESS -->
      <div class="view" id="view-wordpress">
        <div class="page-header">
          <h1>WordPress for Authors</h1>
          <p>Build your author website — the hub of your entire online presence</p>
        </div>

        <div style="background:var(--paper-soft);border-left:3px solid var(--accent);padding:14px 18px;margin-bottom:20px;border-radius:6px;font-size:14px;line-height:1.65;color:var(--ink)">
          <p style="margin:0"><strong>Why WordPress?</strong> A WordPress site is the easiest, most flexible way for an author to claim a piece of the internet that's actually <em>yours</em> — no coding required. Modern themes do the design work, and one-click plugins handle the rest: sell books directly through WooCommerce, collect newsletter signups, run a blog, list upcoming events, even take pre-orders. You can start with just an About page and a buy link, then grow into a full author business as you go. And because WordPress runs roughly 40% of the web, finding hosts, plugins, designers, and step-by-step tutorials is never the bottleneck.</p>
        </div>

        <!-- Plugin download callout -->
        <div style="background:var(--white);border:1px solid var(--accent);border-left:4px solid var(--accent);border-radius:8px;padding:16px 20px;margin-bottom:20px">
          <div style="font-family:var(--font-serif);font-size:16px;font-weight:700;color:var(--ink);margin-bottom:6px">Set up or update your author website</div>
          <p style="margin:0 0 12px;font-size:13.5px;line-height:1.6;color:var(--ink-mid)"><strong>Download Elite Publishing plugin and install it on your WordPress site.</strong> On activation it builds your whole author site for you — the Author Writer theme, all your pages, the control panel, a style, and your store — then keeps itself updated automatically. Step-by-step install instructions are in the <strong>Setup wizard</strong> tab below.</p>
          <a class="app-btn app-btn-green" href="api/wp_plugin.php?action=download" download="" style="text-decoration:none">↓ Download plugin</a>
        </div>

        <div class="card" id="wp-plugin-explainer-2" style="border-left:3px solid var(--accent)">
        <div class="card-title">What Elite Publishing plugin does</div>
        <p style="margin:0 0 10px;font-size:14px;line-height:1.7;color:var(--ink)">
          It turns a blank WordPress into a finished author website. Installing it builds your pages —
          home, books, blog, about, contact — adds a simple control panel inside WordPress, and applies
          one of ten genre styles so the site looks like it belongs to your book rather than to a
          template. It also keeps your titles in step with this app: add or edit a book here and it
          appears on the site, no retyping.
        </p>
        <div style="font-size:14px;line-height:1.8;color:var(--ink)">
          <strong>Installing it takes about two minutes:</strong>
          <ol style="margin:6px 0 0;padding-left:20px">
            <li>Click <strong>Download plugin</strong> above — you get a <code>.zip</code> file. Don’t unzip it.</li>
            <li>In your WordPress admin, go to <strong>Plugins → Add New Plugin → Upload Plugin</strong>.</li>
            <li>Choose the zip, click <strong>Install Now</strong>, then <strong>Activate</strong>.</li>
            <li>Come back here and connect your site using either method below.</li>
          </ol>
        </div>
        <p style="margin:10px 0 0;font-size:13.5px;color:var(--ink-mid);line-height:1.6">
          You only install it once. After that it updates itself when you visit your site’s
          Manage My Site page, so you never have to download it again.
          Needs WordPress 6.0 or newer.
        </p>
      </div>

      <!-- Tab switcher -->
        <div style="display:flex;gap:0;border:1px solid var(--ink-faint);border-radius:var(--radius);overflow:hidden;margin-bottom:24px;width:fit-content">
          <button class="wp-tab active" onclick="showWpTab('learn')" id="wp-tab-learn" style="padding:8px 20px;font-size:13px;font-family:var(--font-body);font-weight:500;border:none;cursor:pointer;background:var(--accent);color:var(--white);transition:all 0.12s">Learn</button>
          <button class="wp-tab" onclick="showWpTab('wizard')" id="wp-tab-wizard" style="padding:8px 20px;font-size:13px;font-family:var(--font-body);font-weight:600;border:none;cursor:pointer;background:#E8F0F7;color:var(--accent);transition:all 0.12s">Setup wizard</button>
          <button class="wp-tab" onclick="showWpTab('tour')" id="wp-tab-tour" style="padding:8px 20px;font-size:13px;font-family:var(--font-body);font-weight:600;border:none;cursor:pointer;background:#E8F0F7;color:var(--accent);transition:all 0.12s">See your site</button>
        </div>

        <!-- LEARN TAB -->
        <div id="wp-learn" class="wp-panel">
          <div class="card" style="background:var(--accent);border:none;margin-bottom:20px">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:20px">
              <div>
                <div style="font-family:var(--font-serif);font-size:18px;font-weight:600;color:var(--white);margin-bottom:6px">Why every author needs a website</div>
                <div style="font-size:13px;color:rgba(255,255,255,0.75);line-height:1.6;max-width:500px">Your social media accounts are rented land. Your website is the one place online that you own completely — no algorithm can take it away.</div>
              </div>
            </div>
          </div>

          <div class="edu-grid" id="wp-lesson-grid"></div>
        </div>

        <!-- WIZARD TAB -->
        <div id="wp-wizard" class="wp-panel" style="display:none">
          <div class="card" style="margin-bottom:16px;border-left:3px solid var(--accent)">
            <div class="card-title">Your own author website — optional, but powerful</div>
            <p style="margin:0;font-size:13.5px;color:var(--ink-mid);line-height:1.6">Elite Publishing works great on its own. A WordPress site is an <strong>optional bonus</strong> — a home base you fully own, where the app publishes your books for you and can even sell print copies in one click. There's no rush and no big commitment: start month-to-month and follow these five steps whenever you're ready.</p>
          </div>
          <div class="card" style="margin-bottom:16px">
            <div class="card-title">Your WordPress setup progress</div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
              <div style="flex:1;height:8px;background:var(--ink-faint);border-radius:4px;overflow:hidden">
                <div id="wp-wizard-bar" style="height:100%;background:var(--accent);border-radius:4px;transition:width 0.4s ease;width:0%"></div>
              </div>
              <span id="wp-wizard-pct" style="font-size:13px;font-weight:500;color:var(--accent);min-width:36px">0%</span>
            </div>
            <div id="wp-steps-list"></div>
          </div>
          <div class="card">
            <div class="card-title">Recommended WordPress hosts</div>
            <p style="font-size:13px;color:var(--ink-mid);margin-bottom:16px;line-height:1.6">Pick a host, install WordPress, and you're ready to connect. We lead with DreamHost because it bills month-to-month and backs it with a 97-day money-back guarantee — so you can start without a big upfront commitment and change your mind if it's not for you.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
              <div style="border:1.5px solid var(--accent);border-radius:var(--radius);padding:16px;position:relative">
                <div style="font-size:10px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:var(--accent);margin-bottom:6px">Recommended</div>
                <div style="font-weight:500;font-size:14px;margin-bottom:4px">DreamHost</div>
                <div style="font-size:12px;color:var(--ink-mid);margin-bottom:12px;line-height:1.5">Beginner-friendly, billed month-to-month, with a 97-day money-back guarantee — the easiest way to start without locking yourself in.</div>
                <a href="https://www.dreamhost.com/hosting/wordpress/" target="_blank" rel="noopener" style="display:inline-block;padding:6px 14px;background:var(--accent);color:var(--white);border-radius:var(--radius);font-size:12px;text-decoration:none;font-family:var(--font-body)">Get started ↗</a>
              </div>
              <div style="border:1px solid var(--ink-faint);border-radius:var(--radius);padding:16px">
                <div style="font-weight:500;font-size:14px;margin-bottom:4px">Hostinger</div>
                <div style="font-size:12px;color:var(--ink-mid);margin-bottom:12px;line-height:1.5">Lowest starting price and a simple setup. Good if cost is your main concern — just note the cheapest rate usually means paying for a longer term up front.</div>
                <a href="https://www.hostinger.com/wordpress-hosting" target="_blank" rel="noopener" style="display:inline-block;padding:6px 14px;background:var(--accent);color:var(--white);border-radius:var(--radius);font-size:12px;text-decoration:none;font-family:var(--font-body)">Get started ↗</a>
              </div>
              <div style="border:1px solid var(--ink-faint);border-radius:var(--radius);padding:16px">
                <div style="font-weight:500;font-size:14px;margin-bottom:4px">Bluehost</div>
                <div style="font-size:12px;color:var(--ink-mid);margin-bottom:12px;line-height:1.5">One of the oldest, most widely used WordPress hosts — so there's a tutorial for everything. A solid, familiar choice.</div>
                <a href="https://www.bluehost.com/wordpress/wordpress-hosting" target="_blank" rel="noopener" style="display:inline-block;padding:6px 14px;background:var(--accent);color:var(--white);border-radius:var(--radius);font-size:12px;text-decoration:none;font-family:var(--font-body)">Get started ↗</a>
              </div>
            </div>
            <p style="font-size:12px;color:var(--ink-mid);margin-top:14px;line-height:1.6">Heads-up: most hosts show a low introductory rate that rises at renewal — check the renewal price before you commit. When you sign up, choose the <strong>basic WordPress plan</strong>, not the pricier eCommerce/WooCommerce tier — Elite Publishing plugin adds your store for you.</p>
          </div>
        </div>

        <!-- SEE YOUR SITE (TOUR) TAB -->
        <div id="wp-tour" class="wp-panel" style="display:none"></div>
      </div>

      <!-- CONNECTIONS -->
      <div class="view" id="view-connections">
        <div class="page-header"><h1>Connections</h1><p>Where you'll be showing up to promote your book</p></div>

        <div class="card" style="border-left:3px solid var(--accent)">
          <div class="card-title">A note on focus</div>
          <p style="margin:0;font-size:14px;line-height:1.6;color:var(--ink)">
            Don't try to be everywhere. Most successful indie authors build a real audience on <strong>two to five</strong> platforms — the ones where their readers already are. Pick a handful below, focus your energy there, and let the rest sit. You can always add more later.
          </p>
          <p style="margin:10px 0 0;font-size:13.5px;color:var(--ink-mid);line-height:1.6">
            Platforms are listed in <strong>rough order of value for indie book promotion</strong>. Where we already support one-click posting, you'll see an <strong>AutoPost</strong> badge — the rest, you'll compose your post here and copy or download it to the platform.
          </p>
        </div>

        <div class="card" style="border-left:3px solid #16a34a">
        <div class="card-title">How to connect a platform</div>
        <ol style="margin:0;padding-left:20px;font-size:14px;line-height:1.75;color:var(--ink)">
          <li><strong>Start with accounts you already have.</strong> If you want a new platform, create the account there first, then come back.</li>
          <li><strong>Open that platform in another tab</strong> and sign in if you need to.</li>
          <li><strong>Go to your own page or profile</strong> and copy the whole web address from the bar at the top of your browser.</li>
          <li><strong>Paste it into that platform's box below and click Save.</strong> A “Saved” confirmation appears when it's stored.</li>
        </ol>
        <p style="margin:10px 0 0;font-size:13.5px;color:var(--ink-mid);line-height:1.6">
          Platforms marked <strong>AutoPost</strong> can also post for you directly — click <strong>Connect API</strong> on the card to set that up. Bluesky asks for an app password, which you create in Bluesky's own settings.
        </p>
      </div>

      <div id="conn-list" class="card">
          <div class="empty">Loading platforms…</div>
        </div>

        <!-- Bluesky inline app-password form (revealed by the Bluesky card's Connect button) -->
        <div id="bsky-form" class="card" style="display:none;border-left:3px solid #0085FF">
          <div class="card-title" style="margin-bottom:8px">Connect to Bluesky</div>
          <p style="margin:0 0 12px;font-size:13px;color:var(--ink-mid);line-height:1.5">Bluesky uses an <strong>App Password</strong>, not your account password. Create one at <a href="https://bsky.app/settings/app-passwords" target="_blank" rel="noopener">bsky.app/settings/app-passwords</a>, then paste it here.</p>
          <div class="field-group"><label class="field-label">Handle</label><input type="text" id="bsky-handle" placeholder="yourname.bsky.social" autocomplete="off"></div>
          <div class="field-group"><label class="field-label">App password</label><input type="password" id="bsky-pw" placeholder="xxxx-xxxx-xxxx-xxxx" autocomplete="off"></div>
          <div style="display:flex;gap:8px">
            <button class="app-btn app-btn-green app-btn-sm" id="bsky-save-btn" onclick="connectBluesky()">Connect</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="toggleBlueskyForm()">Cancel</button>
          </div>
        </div>

        <div class="card">
          <div class="card-title">How connections work</div>
          <p style="font-size:13px;color:var(--ink-mid);line-height:1.7">For platforms with one-click posting (the AutoPost badge), click Connect API — you'll be taken to the platform to approve access. Tokens are stored securely and tied to your account only; other authors cannot access your connections. You can disconnect at any time.</p>
          <p style="font-size:13px;color:var(--ink-mid);line-height:1.7;margin-top:8px">For everything else, paste your profile URL so we can show "Open my [platform]" links on generated posts. When you generate social graphics, quote cards, or trailers, we'll only show the platforms you've set up here — filtered to those that fit the kind of content (graphics, video, or text).</p>
        </div>
      </div>

      <!-- ACCOUNT -->
      <div class="view" id="view-account">
        <div class="page-header"><h1>Account</h1><p>Manage your profile and settings</p></div>
        <div class="two-col">
          <div class="card">
            <div class="card-title">Profile</div>
            <div class="field-group"><label class="field-label">Full name</label><input type="text" id="acc-name"></div>
            <div class="field-group"><label class="field-label">Pen name</label><input type="text" id="acc-pen" placeholder="Optional"></div>
            <div class="field-group"><label class="field-label">Email</label><input type="email" id="acc-email" disabled style="opacity:0.6"></div>
            <div class="field-group"><label class="field-label">Website</label><input type="text" id="acc-website" placeholder="https://yoursite.com"></div>
            <button class="app-btn app-btn-green" onclick="saveProfile()">Save changes</button>
          </div>
			<div class="card">
  <div class="card-title">Change password</div>
  <div class="field-group"><label class="field-label">Current password</label><div class="pw-field"><input type="password" id="pwd-current" placeholder="••••••••"><a href="#" class="pw-toggle" onclick="togglePasswordField('pwd-current', this);return false">Show</a></div></div>
  <div class="field-group"><label class="field-label">New password</label><div class="pw-field"><input type="password" id="pwd-new" placeholder="At least 8 characters"><a href="#" class="pw-toggle" onclick="togglePasswordField('pwd-new', this);return false">Show</a></div></div>
  <div class="field-group"><label class="field-label">Confirm new password</label><div class="pw-field"><input type="password" id="pwd-confirm" placeholder="••••••••"><a href="#" class="pw-toggle" onclick="togglePasswordField('pwd-confirm', this);return false">Show</a></div></div>
  <button class="app-btn app-btn-green" onclick="changePassword()">Update password</button>
</div>
          <div class="card">
            <div class="card-title">Subscription</div>
            <div class="row"><div>Plan</div><strong id="acc-plan-name">—</strong></div>
            <div class="row"><div>Status</div><span id="acc-plan-status">—</span></div>
            <div class="row" id="acc-plan-date-row" style="display:none"><div id="acc-plan-date-label">Next billing</div><span id="acc-plan-date">—</span></div>
            <div class="row"><div>Member since</div><span id="acc-since">—</span></div>
            <!-- Billing controls (view plans, Stripe portal, cancel,
                 reactivate) were removed with the checkout flow. Plan
                 changes are arranged by enquiry. -->
            <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap">
              <button class="app-btn app-btn-green" onclick="contactUs()">Ask about your plan</button>
            </div>
          </div>
          <div class="card">
            <div class="card-title">Email preferences</div>
            <p style="margin:0 0 12px;font-size:13.5px;color:var(--ink-mid);line-height:1.5">
              Elite Publishing sends biweekly progress nudges (suggested next setup steps) and short congratulations when you complete new items on your Marketing Progress Grid. Operational emails — quota warnings, billing notices — always send.
            </p>
            <label style="display:flex;align-items:center;gap:10px;font-size:14px;cursor:pointer">
              <input type="checkbox" id="acc-email-optout" onchange="saveEmailPrefs()" style="width:auto;accent-color:var(--accent)">
              <span>Don't send me progress nudges or achievement emails</span>
            </label>
          </div>
        </div>
      </div>

      <!-- ADMIN: AI USAGE -->
      <div class="view" id="view-admin-users">
        <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
          <div><h1>Users</h1><p>Accounts and subscription status</p></div>
          <button class="app-btn app-btn-primary" onclick="toggleCreateUserForm()" style="flex-shrink:0">+ Create user</button>
        </div>

        <!-- Create-user form (hidden by default; toggled by the button above).
             Bypasses Stripe — for admin onboarding of beta testers or anyone
             else who needs an account outside the public pay-at-signup flow. -->
        <div class="card" id="admin-create-user-card" style="display:none;margin-bottom:14px;border-left:3px solid var(--accent)">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <strong style="font-size:15px">Create a beta-tester account</strong>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="toggleCreateUserForm()" type="button">Cancel</button>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">
            <div class="field-group" style="margin:0">
              <label class="field-label">Full name *</label>
              <input type="text" id="cu-full-name" placeholder="Margaret Hayes">
            </div>
            <div class="field-group" style="margin:0">
              <label class="field-label">Pen name (optional)</label>
              <input type="text" id="cu-pen-name" placeholder="">
            </div>
            <div class="field-group" style="margin:0">
              <label class="field-label">Email *</label>
              <input type="email" id="cu-email" placeholder="tester@example.com">
            </div>
            <div class="field-group" style="margin:0">
              <label class="field-label">Temporary password *</label>
              <input type="text" id="cu-password" placeholder="8+ characters">
            </div>
            <div class="field-group" style="margin:0">
              <label class="field-label">Plan</label>
              <select id="cu-plan">
                <option value="pro" selected>Pro</option>
                <option value="starter">Starter</option>
                <option value="unlimited">Studio</option>
              </select>
            </div>
            <div class="field-group" style="margin:0">
              <label class="field-label">Comp days</label>
              <input type="number" id="cu-comp-days" value="30" min="1" max="365">
            </div>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:14px;flex-wrap:wrap">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink-mid)">
              <input type="checkbox" id="cu-send-email" checked>
              Email login details to the tester
            </label>
            <button class="app-btn app-btn-primary" onclick="createBetaUser()" type="button">Create account</button>
          </div>
        </div>

        <!-- Summary cards -->
        <div id="au-user-summary" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;margin-bottom:14px"></div>

        <!-- Filters -->
        <div class="card" style="margin-bottom:14px">
          <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div class="field-group" style="margin:0;min-width:160px">
              <label class="field-label">Status</label>
              <select id="auu-status" onchange="loadAdminUsers()">
                <option value="">All</option>
                <option value="trialing">Trialing</option>
                <option value="active">Active</option>
                <option value="past_due">Past due</option>
                <option value="trial_expired">Trial expired</option>
                <option value="canceled">Canceled</option>
                <option value="none">No subscription</option>
              </select>
            </div>
            <div class="field-group" style="margin:0;flex:1;min-width:200px">
              <label class="field-label">Search</label>
              <input type="text" id="auu-search" placeholder="Name or email…" oninput="loadAdminUsers()">
            </div>
          </div>
        </div>

        <!-- User table -->
        <div class="card">
          <div id="auu-table-wrap" style="overflow-x:auto">
            <div class="empty">Loading…</div>
          </div>
        </div>
      </div>

      <!-- ADMIN: WRITERS GROUPS (partner campaigns) -->
      <div class="view" id="view-admin-groups">
        <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
          <div>
            <h1>Writers Groups</h1>
            <p>Partner campaigns. Add a group, send its leader the outreach letter, then watch the funnel.</p>
          </div>
          <div style="display:flex;gap:8px">
            <button class="app-btn app-btn-outline" onclick="agOpenTemplates()">Edit templates</button>
            <button class="app-btn app-btn-green" onclick="agNewGroup()">+ New group</button>
          </div>
        </div>

        <!-- Editable templates: reword once, applies to every group -->
        <div class="card" id="ag-templates" style="display:none;padding:22px;margin-bottom:22px">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:6px">
            <h2 style="margin:0;font-size:18px">Outreach templates</h2>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="agBackToGroups()" title="Back to Writers Groups">← Back to groups</button>
          </div>
          <p style="font-size:13px;color:var(--ink-soft);margin:0 0 16px">
            Edit the wording once and every group's letter uses it. Placeholders get filled per group:
            <code>{{group_name}}</code> <code>{{leader_first}}</code> <code>{{leader_trial_days}}</code>
            <code>{{trial_days}}</code> <code>{{link}}</code> <code>{{setup_link}}</code> <code>{{year}}</code>
            <code>{{leader_salutation}}</code> <code>{{ebook_link}}</code>
            <br><span style="font-size:12px">{{leader_salutation}} renders as “For you Marcia, ” — or as nothing at all when the group has no leader name, so the subject falls back to the plain line. It supplies its own trailing space; don’t add one after it. {{ebook_link}} is a tracked link to the free guide — clicks land in this group’s numbers, so don’t replace it with a direct PDF link or you lose the only signal these letters produce.</span>
          </p>

          <label class="field-label">Leader letter — subject</label>
          <input type="text" id="ag-tpl-letter-subject">
          <label class="field-label" style="margin-top:12px">Leader letter — body</label>
          <textarea id="ag-tpl-letter-body" rows="18" style="font-family:ui-monospace,Menlo,monospace;font-size:12.5px"></textarea>
          <div style="margin:8px 0 22px"><button class="app-btn app-btn-green app-btn-sm" onclick="agSaveTemplate('leader_letter')">Save letter template</button></div>

          <label class="field-label">Alternative leader letter (“free talk” angle) — subject</label>
          <input type="text" id="ag-tpl-talk-subject">
          <label class="field-label" style="margin-top:10px">Alternative leader letter — body</label>
          <textarea id="ag-tpl-talk-body" rows="18" style="font-family:ui-monospace,Menlo,monospace;font-size:12.5px"></textarea>
          <div style="margin:8px 0 22px"><button class="app-btn app-btn-green app-btn-sm" onclick="agSaveTemplate('leader_letter_talk')">Save alternative template</button></div>

          <label class="field-label">Follow-up letter — subject</label>
          <input type="text" id="ag-tpl-followup-subject">
          <label class="field-label" style="margin-top:12px">Follow-up letter — body</label>
          <textarea id="ag-tpl-followup-body" rows="12" style="font-family:ui-monospace,Menlo,monospace;font-size:12.5px"></textarea>
          <div style="margin:8px 0 22px"><button class="app-btn app-btn-green app-btn-sm" onclick="agSaveTemplate('leader_followup')">Save follow-up template</button></div>

          <label class="field-label">Member blurb — body</label>
          <textarea id="ag-tpl-blurb-body" rows="10" style="font-family:ui-monospace,Menlo,monospace;font-size:12.5px"></textarea>
          <div style="margin-top:8px"><button class="app-btn app-btn-green app-btn-sm" onclick="agSaveTemplate('member_blurb')">Save blurb template</button></div>

          <!-- Bottom exit — four templates makes this panel several screens tall. -->
          <div style="margin-top:22px;padding-top:16px;border-top:1px solid var(--line,#e5e5e5)">
            <button class="app-btn app-btn-outline app-btn-sm" onclick="agBackToGroups()" title="Back to Writers Groups">← Back to groups</button>
          </div>
        </div>

        <!-- Add / edit form -->
        <div class="card" id="ag-form" style="display:none;padding:22px;margin-bottom:22px">
          <h2 style="margin:0 0 16px;font-size:18px" id="ag-form-title">New group</h2>
          <input type="hidden" id="ag-id">
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px">
            <div><label class="field-label">Group name</label><input type="text" id="ag-name" placeholder="Columbus Writers Guild"></div>
            <div><label class="field-label">Leader name</label><input type="text" id="ag-leader-name" placeholder="Marge Wilson"></div>
            <div><label class="field-label">Leader email</label><input type="email" id="ag-leader-email" placeholder="marge@example.com"></div>
            <div><label class="field-label">City</label><input type="text" id="ag-city" placeholder="Columbus"></div>
            <div><label class="field-label">State</label><input type="text" id="ag-state" placeholder="Ohio"></div>
            <div><label class="field-label">Trial days</label><input type="number" id="ag-trial-days" value="14" min="1" max="60"></div>
            <div><label class="field-label">Status</label>
              <select id="ag-status">
                <option value="draft">Draft</option>
                <option value="contacted">Contacted</option>
                <option value="active">Active</option>
                <option value="declined">Declined</option>
                <option value="paused">Paused</option>
              </select>
            </div>
          </div>
          <div style="margin-top:14px"><label class="field-label">Notes</label><textarea id="ag-notes" rows="2" placeholder="How you know them, when you last spoke…"></textarea></div>
          <div style="margin-top:16px;display:flex;gap:10px">
            <button class="app-btn app-btn-green" onclick="agSaveGroup()">Save group</button>
            <button class="app-btn app-btn-outline" onclick="document.getElementById('ag-form').style.display='none'">Cancel</button>
          </div>
          <div id="ag-form-err" style="color:#c44;font-size:13px;margin-top:10px"></div>
        </div>

        <!-- Outreach: generated letter + member blurb -->
        <div class="card" id="ag-outreach" style="display:none;padding:22px;margin-bottom:22px">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:6px">
            <h2 style="margin:0;font-size:18px" id="ag-outreach-title">Outreach</h2>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="agBackToGroups()" title="Back to Writers Groups">← Back to groups</button>
          </div>
          <p style="font-size:13px;color:var(--ink-soft);margin:0 0 16px">
            Send these from your own mail app — they are cold emails and must not go through the campaign sender.
            The leader forwards the member blurb to their group; you never email members directly.
          </p>

          <label class="field-label">1. First letter to the leader — send now</label>
          <textarea id="ag-letter" rows="16" style="font-family:ui-monospace,Menlo,monospace;font-size:12.5px"></textarea>
          <div style="display:flex;gap:10px;margin:8px 0 20px;flex-wrap:wrap">
            <button class="app-btn app-btn-primary app-btn-sm" onclick="agOpenEmail('ag-letter')">Open in email</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="agCopy('ag-letter', this)">Copy letter</button>
            <button class="app-btn app-btn-green app-btn-sm" onclick="agMarkContacted()">Mark as contacted</button>
          </div>

          <div id="ag-talk-block" style="display:none">
            <label class="field-label">1b. Alternative first letter — leads with the free guide</label>
            <p style="font-size:13px;color:var(--ink-soft);margin:0 0 8px">
              Send this <em>instead of</em> letter 1, not as well. It leads with the free
              “Google Ads for Authors” guide so the leader gets something concrete to hand their
              members with no signup, and the tool arrives as a follow-on rather than the ask —
              aimed at role inboxes (info@, thurberhouse@) where a pitch-shaped email struggles.
              The guide link is tracked per group, so clicks show up in this group’s numbers;
              since these go out by hand as plain text, that click is the only engagement signal
              available — there is no way to track opens.
            </p>
            <textarea id="ag-letter-talk" rows="16" style="font-family:ui-monospace,Menlo,monospace;font-size:12.5px"></textarea>
            <div style="display:flex;gap:10px;margin:8px 0 20px;flex-wrap:wrap">
              <button class="app-btn app-btn-primary app-btn-sm" onclick="agOpenEmail('ag-letter-talk')">Open in email</button>
              <button class="app-btn app-btn-outline app-btn-sm" onclick="agCopy('ag-letter-talk', this)">Copy alternative</button>
            </div>
          </div>

          <label class="field-label">2. Follow-up letter — send ~a week later if they’ve gone quiet</label>
          <textarea id="ag-followup" rows="12" style="font-family:ui-monospace,Menlo,monospace;font-size:12.5px"></textarea>
          <div style="display:flex;gap:10px;margin:8px 0 20px;flex-wrap:wrap">
            <button class="app-btn app-btn-primary app-btn-sm" onclick="agOpenEmail('ag-followup')">Open in email</button>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="agCopy('ag-followup', this)">Copy follow-up</button>
          </div>

          <label class="field-label">3. Blurb for the leader to forward to members — send once they say yes</label>
          <textarea id="ag-blurb" rows="10" style="font-family:ui-monospace,Menlo,monospace;font-size:12.5px"></textarea>
          <div style="margin-top:8px"><button class="app-btn app-btn-outline app-btn-sm" onclick="agCopy('ag-blurb', this)">Copy blurb</button></div>

          <!-- Second exit at the bottom: this panel is several screens tall, so
               after working through the letters the top button is well out of reach. -->
          <div style="margin-top:22px;padding-top:16px;border-top:1px solid var(--line,#e5e5e5)">
            <button class="app-btn app-btn-outline app-btn-sm" onclick="agBackToGroups()" title="Back to Writers Groups">← Back to groups</button>
          </div>
        </div>

        <div id="ag-list"></div>
      </div>

      <div class="view" id="view-admin-usage">
        <div class="page-header"><h1>AI Usage</h1><p>Cost and activity across all users</p></div>

        <!-- Filters -->
        <div class="card" style="margin-bottom:14px">
          <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div class="field-group" style="margin:0;min-width:120px">
              <label class="field-label">Period</label>
              <select id="au-days" onchange="loadAdminUsage()">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="all">All time</option>
              </select>
            </div>
            <div class="field-group" style="margin:0;min-width:160px">
              <label class="field-label">Feature</label>
              <select id="au-feature" onchange="loadAdminUsage()">
                <option value="">All features</option>
              </select>
            </div>
            <div class="field-group" style="margin:0;min-width:180px">
              <label class="field-label">User</label>
              <select id="au-user" onchange="loadAdminUsage()">
                <option value="">All users</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Summary stats -->
        <div id="au-summary-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:14px"></div>

        <!-- By-feature breakdown -->
        <div class="card" style="margin-bottom:14px">
          <div class="card-title">By feature</div>
          <div id="au-by-feature"><div class="empty">Loading…</div></div>
        </div>

        <!-- Posting activity (manual handoffs by source/platform + AutoPost total) -->
        <div class="card" style="margin-bottom:14px">
          <div class="card-title">Posting activity</div>
          <div id="au-posting"><div class="empty">Loading…</div></div>
        </div>

        <!-- Full log -->
        <div class="card">
          <div class="card-title">Recent calls (up to 200)</div>
          <div id="au-table-wrap" style="overflow-x:auto;margin-top:10px">
            <div class="empty">Loading…</div>
          </div>
        </div>
      </div>

      <!-- ADMIN CHAT LOG -->
      <div class="view" id="view-admin-chatlog">
        <div class="page-header"><h1>Chat Log</h1><p>Every conversation between authors and the in-app help bot. Flag wrong answers to teach the bot.</p></div>
        <div class="card" style="margin-bottom:14px">
          <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div class="field-group" style="margin:0;min-width:200px">
              <label class="field-label">Filter</label>
              <select id="acl-filter" onchange="loadAdminChatlog()">
                <option value="all">All conversations</option>
                <option value="unhelpful">Marked unhelpful only</option>
              </select>
            </div>
            <button class="app-btn app-btn-outline app-btn-sm" onclick="loadAdminChatlog()">Refresh</button>
          </div>
        </div>
        <div class="card">
          <div class="card-title">Recent conversations (most recent first)</div>
          <div id="acl-list" style="margin-top:10px"><div class="empty">Loading…</div></div>
        </div>
      </div>

      <!-- ADMIN CHAT OVERRIDES -->
      <div class="view" id="view-admin-overrides">
        <div class="page-header"><h1>Chat Overrides</h1><p>Corrections you've taught the bot. Each active override is injected into every chat as authoritative truth.</p></div>
        <div class="card" style="margin-bottom:14px">
          <button class="app-btn app-btn-green app-btn-sm" onclick="openOverrideModal(null)">+ New override</button>
          <button class="app-btn app-btn-outline app-btn-sm" onclick="loadAdminOverrides()" style="margin-left:8px">Refresh</button>
        </div>
        <div class="card">
          <div class="card-title">Active &amp; inactive overrides</div>
          <div id="ovr-list" style="margin-top:10px"><div class="empty">Loading…</div></div>
        </div>
      </div>

      <footer style="margin-top:48px;padding:18px 0 24px;text-align:center;font-size:12px;color:var(--ink-soft);border-top:1px solid var(--ink-faint)">
        © <span class="copy-year">2026</span> Elite Publishing &nbsp;·&nbsp; All Rights Reserved &nbsp;·&nbsp;
        <a href="terms.html" style="color:var(--ink-soft);text-decoration:underline">Terms of Service</a> &nbsp;·&nbsp;
        <a href="privacy.html" style="color:var(--ink-soft);text-decoration:underline">Privacy Policy</a>
      </footer>
    </main>
  </div>
</div>

<div id="toast"></div>

<!-- Setup-help modal — driven by showSetupHelp(topic). Per-section how-to with checkable steps. -->
<div class="modal-backdrop" id="setup-help-backdrop" style="display:none" onclick="if(event.target===this) closeSetupHelp()">
  <div class="modal setup-help-modal">
    <div class="modal-header">
      <h3 id="setup-help-title">Setup instructions</h3>
      <button class="modal-close" onclick="closeSetupHelp()">×</button>
    </div>
    <div class="modal-body" id="setup-help-body"></div>
    <div class="modal-footer">
      <span id="setup-help-progress" class="setup-help-progress" style="margin-right:auto"></span>
      <button class="app-btn app-btn-outline app-btn-sm" onclick="closeSetupHelp()">Close</button>
    </div>
  </div>
</div>

<!-- Handoff modal (v52) — opened by submitPost() when manual platforms are selected.
     One tab per chosen manual platform; each tab gives the author the shortest path
     to actually posting: copy caption, copy/download image, open the platform composer. -->
<div class="modal-backdrop" id="handoff-modal-backdrop" style="display:none" onclick="if(event.target===this) closeHandoffModal()">
  <div class="modal handoff-modal">
    <div class="modal-header">
      <h3 id="handoff-modal-title">Post to your platforms</h3>
      <button class="modal-close" onclick="closeHandoffModal()" aria-label="Close">×</button>
    </div>
    <div id="handoff-autopost-banner" style="display:none"></div>
    <div class="handoff-tab-strip" id="handoff-tab-strip" role="tablist"></div>
    <div class="modal-body" id="handoff-tab-body"></div>
    <div class="modal-footer">
      <button class="app-btn app-btn-outline app-btn-sm" onclick="closeHandoffModal()">Done</button>
    </div>
  </div>
</div>

<!-- Shopify Connect modal — driven by showShopifyConnect() from Sales Channels view. -->
<div class="modal-backdrop" id="shopify-connect-backdrop" style="display:none" onclick="if(event.target===this) closeShopifyConnect()">
  <div class="modal" style="max-width:560px">
    <div class="modal-header">
      <h3>Connect Shopify store</h3>
      <button class="modal-close" onclick="closeShopifyConnect()">×</button>
    </div>
    <div class="modal-body">
      <p style="margin-top:0;font-size:13.5px">Enter your <code>.myshopify.com</code> store domain. You'll be sent to Shopify to approve the connection, then redirected back here.</p>
      <div class="field-group" style="margin-top:14px">
        <label class="field-label">Store domain</label>
        <input type="text" id="shopify-shop-domain" placeholder="yourstore.myshopify.com" autocomplete="off">
        <div style="font-size:11.5px;color:var(--ink-soft);margin-top:4px">Use the <code>.myshopify.com</code> form — even if you have a custom domain on top, the API authenticates against this one. Find it in Shopify Settings → Domains.</div>
      </div>
      <div id="shopify-connect-error" style="display:none;color:var(--danger);font-size:13px;margin-top:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="app-btn app-btn-outline app-btn-sm" onclick="closeShopifyConnect()">Cancel</button>
      <button class="app-btn app-btn-green app-btn-sm" id="shopify-connect-submit" onclick="startShopifyAuthorize()">Continue to Shopify ↗</button>
    </div>
  </div>
</div>

<!-- WooCommerce Connect modal — driven by showWooConnect() from Sales Channels view. -->
<div class="modal-backdrop" id="woo-connect-backdrop" style="display:none" onclick="if(event.target===this) closeWooConnect()">
  <div class="modal" style="max-width:600px">
    <div class="modal-header">
      <h3>Connect WooCommerce store</h3>
      <button class="modal-close" onclick="closeWooConnect()">×</button>
    </div>
    <div class="modal-body">
      <p style="margin-top:0;font-size:13.5px">Connect a self-hosted WordPress site running WooCommerce. You'll generate a Consumer Key / Secret pair in WP Admin and paste them here.</p>
      <details style="margin:10px 0 14px;font-size:13px;background:var(--paper-soft);padding:10px 12px;border-radius:6px">
        <summary style="cursor:pointer;font-weight:500;color:var(--ink)">How to generate the key pair (one minute)</summary>
        <ol style="margin:8px 0 0;padding-left:22px;line-height:1.7">
          <li>In WordPress admin, go to <strong>WooCommerce → Settings → Advanced → REST API</strong>.</li>
          <li>Click <strong>Add key</strong>.</li>
          <li>Description: "Elite Publishing" (any name works).</li>
          <li>User: pick your admin account.</li>
          <li>Permissions: <strong>Read/Write</strong>.</li>
          <li>Click <strong>Generate API key</strong>.</li>
          <li>Copy the Consumer Key (<code>ck_…</code>) <em>and</em> the Consumer Secret (<code>cs_…</code>) into the fields below — the secret is shown only once.</li>
        </ol>
      </details>
      <div class="field-group" style="margin-top:8px">
        <label class="field-label">Store URL</label>
        <input type="text" id="woo-store-url" placeholder="https://yourstore.com" autocomplete="off">
        <div style="font-size:11.5px;color:var(--ink-soft);margin-top:4px">Your WordPress site root — the URL where the WooCommerce REST API lives at <code>/wp-json/wc/v3</code>. Must be HTTPS.</div>
      </div>
      <div class="field-group" style="margin-top:12px">
        <label class="field-label">Consumer Key</label>
        <input type="text" id="woo-consumer-key" placeholder="ck_…" autocomplete="off" spellcheck="false">
      </div>
      <div class="field-group" style="margin-top:12px">
        <label class="field-label">Consumer Secret</label>
        <input type="text" id="woo-consumer-secret" placeholder="cs_…" autocomplete="off" spellcheck="false">
      </div>
      <div id="woo-connect-error" style="display:none;color:var(--danger);font-size:13px;margin-top:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="app-btn app-btn-outline app-btn-sm" onclick="closeWooConnect()">Cancel</button>
      <button class="app-btn app-btn-green app-btn-sm" id="woo-connect-submit" onclick="connectWoo()">Connect</button>
    </div>
  </div>
</div>

<!-- First-time welcome modal — driven by maybeShowWelcomeForNewUser() in onLogin. -->
<div class="modal-backdrop" id="welcome-backdrop" style="display:none" onclick="if(event.target===this) closeWelcome()">
  <div class="modal" style="max-width:560px">
    <div class="modal-header">
      <h3>Welcome to Elite Publishing</h3>
      <button class="modal-close" onclick="closeWelcome()">×</button>
    </div>
    <div class="modal-body">
      <p id="welcome-greeting" style="margin-top:0"></p>
      <p>The <strong>left sidebar</strong> is your navigation. The five places you'll spend the most time:</p>
      <ul style="margin:6px 0 14px;padding-left:20px;line-height:1.55">
        <li><strong>My Books</strong> — title, blurb, genre, cover. Every AI feature reads from here, so this is where to start.</li>
        <li><strong>Social Posts</strong> — compose once, post everywhere. LinkedIn, Bluesky &amp; Pinterest post with one click; for Instagram, Facebook, TikTok and 10+ more, we prep the caption, hashtags, and image so posting is copy-paste-done.</li>
        <li><strong>Email Campaigns</strong> — newsletter sends to your reader list.</li>
        <li><strong>Promo Materials</strong> — AI-drafted press releases, sell sheets, cover letters, and more.</li>
        <li><strong>Amazon KDP Tools</strong> — keywords + categories, A+ Content, Author Central bio, promo planner, sales rank tracker.</li>
      </ul>
      <p>Every tool has a <strong>? Setup help</strong> button for a walk-through. Snoop around freely — and when you're ready to publish, head to <strong>Connections</strong> to link your platforms.</p>

      <div id="welcome-assessment" style="margin-top:16px;padding:14px 16px;background:var(--accent-lt);border-radius:var(--radius);border:1px solid rgba(45,80,22,0.18)">
        <div style="font-size:13.5px;line-height:1.5;margin-bottom:10px">
          <strong>Quick — which of these do you already have?</strong>
          <span style="color:var(--ink-soft)">Optional. Helps us tailor your game plan.</span>
        </div>
        <div id="welcome-assessment-chips" style="display:flex;flex-wrap:wrap;gap:8px">
          <button type="button" class="assess-chip" data-chip="social" onclick="toggleAssessChip(this)">I'm active on social media</button>
          <button type="button" class="assess-chip" data-chip="website" onclick="toggleAssessChip(this)">I have an author website</button>
          <button type="button" class="assess-chip" data-chip="kdp" onclick="toggleAssessChip(this)">I sell on Amazon KDP</button>
          <button type="button" class="assess-chip" data-chip="events" onclick="toggleAssessChip(this)">I do live events or signings</button>
        </div>
      </div>

      <p id="welcome-show-counter" style="margin:14px 0 0;font-size:12px;color:var(--ink-soft)"></p>
    </div>
    <div class="modal-footer" style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
      <button class="app-btn app-btn-outline" onclick="closeWelcome(true)">Don't show this again</button>
      <button class="app-btn app-btn-green" onclick="closeWelcome(false)">Got it — let's go</button>
    </div>
  </div>
</div>

<!-- Demo-mode intercept modal — populated by JS in demo mode only -->
<div id="site-tour-overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(20,18,15,.6);align-items:flex-start;justify-content:center;overflow:auto;padding:24px 16px" onclick="if(event.target===this) closeSiteTour()">
  <div style="background:var(--white);border-radius:12px;max-width:760px;width:100%;margin:auto;padding:22px 22px 26px;position:relative;box-shadow:0 18px 60px rgba(0,0,0,.35)">
    <button onclick="closeSiteTour()" aria-label="Close" style="position:absolute;top:10px;right:14px;border:none;background:none;font-size:26px;line-height:1;color:var(--ink-mid);cursor:pointer">×</button>
    <div style="font-family:var(--font-serif);font-size:20px;font-weight:600;color:var(--ink);margin:0 30px 14px 0">See what your book's website looks like</div>
    <div id="site-tour-body"></div>
  </div>
</div>

<div id="demo-modal-overlay" onclick="if(event.target===this) closeDemoModal()">
  <div id="demo-modal" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title">
    <div class="demo-modal-head">
      <div id="demo-modal-icon" class="demo-modal-icon">✨</div>
      <div class="demo-modal-titles">
        <div id="demo-modal-eyebrow" class="demo-modal-eyebrow">Demo preview</div>
        <div id="demo-modal-title" class="demo-modal-title">AI draft</div>
      </div>
      <button class="demo-modal-close" onclick="closeDemoModal()" aria-label="Close">×</button>
    </div>
    <div id="demo-modal-body" class="demo-modal-body">
          
        </div>
        <figure id="demo-modal-example-wrap" style="display:none;margin:0;padding:0 26px 4px"><video id="demo-modal-example-video" playsinline="" controls="" preload="none" style="display:none;width:100%;max-height:300px;border:1px solid var(--ink-faint);border-radius:8px;background:#000"></video>
          <img id="demo-modal-example" alt="" style="display:block;width:100%;max-height:240px;object-fit:contain;border:1px solid var(--ink-faint);border-radius:8px;background:var(--paper-soft)"><figcaption id="demo-modal-example-cap" style="margin-top:6px;font-size:12px;color:var(--ink-soft);text-align:center;line-height:1.45"></figcaption></figure>
        
    <div class="demo-modal-foot">
      <span id="demo-modal-foot-note" class="demo-foot-note">This is a demo — get in touch to generate your own.</span>
      <div class="demo-foot-actions">
        <button class="demo-cta secondary" onclick="closeDemoModal()">Close</button>
        <button class="demo-cta" onclick="closeDemoModal(); contactUs();">Get in touch</button>
      </div>
    </div>
  </div>
</div>
	
	<!--
  Session 7A — Snippet 3 FINAL (replaces all earlier versions)

  This is the complete JS module for the Website view, pre-adjusted
  to match the conventions of your index.html:
    - uses style.display instead of the hidden attribute
    - uses your actual input IDs (wp-input-url / -user / -pass)
    - binds the save button as a click, not a form submit
    - updates nav-website-status using the same pattern as
      nav-social-status (class = 'platform-status on|off')

  WHERE TO PASTE:
  Anywhere inside a <script> tag in index.html. The end of the file
  (just before </body>) is fine. If you previously pasted a partial
  version of snippet 3, delete that first.

  AFTER PASTING:
  Upload index.html, click the Website nav item, confirm you see
  the education hub + connection form rendered in your app's styling.
-->

<?php /* The hand-maintained ?v=236 and ?v=2 below were the right instinct — they
         just had to be bumped by hand, and were not. ep_lp_asset() derives the
         same thing from the file's mtime, so it can never be forgotten. */ ?>
<script src="<?= esc(ep_lp_asset($lp, 'assets/js/tour.js')) ?>" defer></script>
<script src="<?= esc(ep_lp_asset($lp, 'assets/js/app.js')) ?>" defer></script>
<!-- Walkthrough prototype (session 56). Loads after the bundle so it can wrap
     openLesson(). Remove this line + /walkthrough.js to pull the feature. -->
<script src="<?= esc(ep_lp_asset($lp, 'assets/js/walkthrough.js')) ?>" defer></script>

<!-- ════════════════════════════════════════════════════════════
     ENQUIRY FORM
     Replaces the retired sign-in / sign-up panel and the Stripe
     checkout buttons. There are no accounts and no payments on this
     site: every "get started" path now ends at this one form.

     To post enquiries somewhere real, set ENQUIRY_ENDPOINT below to
     your handler URL. Left empty, the form validates and shows the
     thank-you state without any network call, which is what a static
     page can honestly do on its own.
════════════════════════════════════════════════════════════ -->
<script src="<?= esc(ep_lp_asset($lp, 'assets/js/enquiry-form.js')) ?>"></script>

<!-- Feedback modal — report an issue or request a feature. -->
<div class="modal-backdrop" id="feedback-backdrop" style="display:none" onclick="if(event.target===this) closeFeedbackModal()">
  <div class="modal">
    <div class="modal-header">
      <h3>Send feedback</h3>
      <button class="modal-close" onclick="closeFeedbackModal()" type="button">×</button>
    </div>
    <div class="modal-body">
      <p style="margin:0 0 14px;font-size:13.5px;color:var(--ink-mid)">Found a bug or have an idea? Tell us — it goes straight to the team, with the page you're on attached automatically.</p>
      <div class="fb-type-row">
        <div class="fb-type active" data-type="bug" onclick="fbSelectType(this)">🐞 Something's broken</div>
        <div class="fb-type" data-type="feature" onclick="fbSelectType(this)">💡 Feature idea</div>
        <div class="fb-type" data-type="other" onclick="fbSelectType(this)">💬 Question / other</div>
      </div>
      <div class="field-group" style="margin:0">
        <label class="field-label">Your message</label>
        <textarea id="feedback-message" rows="5" placeholder="What happened, or what would you like to see?"></textarea>
      </div>
      <div id="feedback-error" style="display:none;color:var(--danger);font-size:13px;margin-top:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="app-btn app-btn-outline" onclick="closeFeedbackModal()" type="button">Cancel</button>
      <button class="app-btn app-btn-green" id="feedback-send-btn" onclick="submitFeedback()" type="button">Send feedback</button>
    </div>
  </div>
</div>

<!-- Chat override modal — admin clicks "Wrong answer" or "+ New override" to teach the bot. -->
<div class="modal-backdrop" id="ovr-backdrop" style="display:none" onclick="if(event.target===this) closeOverrideModal()">
  <div class="modal" style="max-width:620px">
    <div class="modal-header">
      <h3 id="ovr-modal-title">Teach the bot</h3>
      <button class="modal-close" onclick="closeOverrideModal()">×</button>
    </div>
    <div class="modal-body">
      <p style="margin-top:0;font-size:13.5px;color:var(--ink-mid)">Describe the kind of question this applies to, and the correct answer. The bot will use this on every future chat where it's relevant.</p>
      <div id="ovr-source-context" style="display:none;background:var(--paper-soft);border-left:3px solid var(--ink-faint);padding:10px 12px;border-radius:6px;margin-bottom:12px;font-size:12.5px;color:var(--ink-soft)"></div>
      <div class="field-group">
        <label class="field-label">When the user asks something like…</label>
        <textarea id="ovr-scenario" rows="3" placeholder="e.g. When authors ask whether they need their own ISBN for KDP and IngramSpark."></textarea>
      </div>
      <div class="field-group">
        <label class="field-label">…the correct answer is</label>
        <textarea id="ovr-answer" rows="5" placeholder="e.g. Each format on each platform needs its own ISBN — so a paperback on both KDP and IS means two paperback ISBNs. They cannot share. Free Ingram ISBNs lock you into IS as the printer for that edition."></textarea>
      </div>
      <input type="hidden" id="ovr-source-conv-id" value="">
      <div id="ovr-error" style="display:none;color:var(--danger);font-size:13px;margin-top:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="app-btn app-btn-outline app-btn-sm" onclick="closeOverrideModal()">Cancel</button>
      <button class="app-btn app-btn-green app-btn-sm" id="ovr-save-btn" onclick="saveOverride()">Save override</button>
    </div>
  </div>
</div>

<!-- Help chatbot widget — driven by /api/chat.php. Hidden until login (see showChatFab in onLogin). -->
<div id="chat-tooltip" onclick="toggleChat()" role="button" aria-label="Open assistant">
  <button id="chat-tooltip-close" onclick="event.stopPropagation(); dismissChatTooltip(true)" aria-label="Dismiss">×</button>
  <div class="chat-tooltip-body">
    <span class="chat-tooltip-avatar" aria-hidden="true">👋</span>
    <div class="chat-tooltip-text">
      <strong>Hi! I'm Sophie.</strong>
      Need help with your book, a post, or finding something in the app? Click here to chat.
    </div>
  </div>
</div>
<button id="chat-fab" onclick="toggleChat()" aria-label="Open Sophie, your assistant" title="Ask Sophie — your in-app assistant">
  <img src="assets/Avatar2.png" alt="Sophie" onerror="this.style.display='none';var s=this.nextElementSibling;if(s)s.style.display='block';">
  <svg class="chat-fab-fallback" viewBox="0 0 40 40" aria-hidden="true" style="display:none;width:100%;height:100%">
    <defs><clippath id="cfab-clip"><circle cx="20" cy="20" r="20"></circle></clippath></defs>
    <g clip-path="url(#cfab-clip)">
      <rect width="40" height="40" fill="#f3efe6"></rect>
      <ellipse cx="20" cy="40" rx="16" ry="11" fill="#ffffff"></ellipse>
      <circle cx="20" cy="17" r="9" fill="#fcd9b6"></circle>
      <path d="M11.5 14.5 C12 8.5 28 8 28.5 14.8 L28 18 C27 14 26 13 23 13 C20 13 17 13.5 14 14 C12.5 14.2 12 15 12 16 Z" fill="#4a3526"></path>
      <circle cx="17" cy="17" r="0.9" fill="#2a1f15"></circle>
      <circle cx="23" cy="17" r="0.9" fill="#2a1f15"></circle>
      <path d="M16.5 20.5 Q20 23 23.5 20.5" stroke="#2a1f15" stroke-width="1.1" fill="none" stroke-linecap="round"></path>
    </g>
  </svg>
</button>
<div id="chat-panel" role="dialog" aria-label="Sophie — your in-app assistant">
  <div id="chat-header">
    <div id="chat-header-avatar" aria-hidden="true">
      <img src="assets/Avatar2.png" alt="Sophie" onerror="this.style.display='none';var s=this.nextElementSibling;if(s)s.style.display='block';">
      <svg viewBox="0 0 40 40" style="display:none">
        <defs>
          <clippath id="cpanel-clip"><circle cx="20" cy="20" r="20"></circle></clippath>
        </defs>
        <g clip-path="url(#cpanel-clip)">
          <rect width="40" height="40" fill="#f3efe6"></rect>
          <ellipse cx="20" cy="40" rx="16" ry="11" fill="#ffffff"></ellipse>
          <circle cx="20" cy="17" r="9" fill="#fcd9b6"></circle>
          <path d="M11.5 14.5 C12 8.5 28 8 28.5 14.8 L28 18 C27 14 26 13 23 13 C20 13 17 13.5 14 14 C12.5 14.2 12 15 12 16 Z" fill="#4a3526"></path>
          <circle cx="17" cy="17" r="0.9" fill="#2a1f15"></circle>
          <circle cx="23" cy="17" r="0.9" fill="#2a1f15"></circle>
          <path d="M16.5 20.5 Q20 23 23.5 20.5" stroke="#2a1f15" stroke-width="1.1" fill="none" stroke-linecap="round"></path>
        </g>
      </svg>
    </div>
    <div id="chat-header-text">
      <h4>Sophie <span class="ai-badge">AI</span></h4>
      <div class="chat-sub">Your in-app assistant</div>
    </div>
    <div id="chat-header-actions">
      <button id="chat-download" type="button" onclick="downloadChatConversation()" aria-label="Download conversation" title="Download this conversation as a text file">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
      </button>
      <button id="chat-close" onclick="toggleChat()" aria-label="Close assistant">×</button>
    </div>
  </div>
  <div id="chat-messages"></div>
  <div id="chat-input-area">
    <textarea id="chat-input" rows="1" placeholder="Ask Sophie anything…"></textarea>
    <button id="chat-send" onclick="sendChat()">Send</button>
  </div>
</div>


</body>
</html>
