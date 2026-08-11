<?php
declare(strict_types=1);

/**
 * Pricing — /pricing (SPEC §C.4).
 *
 * Navbar · page hero · Plans · FAQ · CTA + wizard · footer. Everything below
 * the hero is a shared component, so the hero is the only markup here.
 * Copy is SPEC §D.6, transcribed verbatim.
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'pricing';
$pageTitle       = 'Pricing';
$pageDescription = 'Publishing packages for every author — Basic, Standard and Premium. '
                 . 'Flexible pricing whether you are starting from an idea or ready to publish.';
$pageCss         = ['css/p-pricing.css'];
$bodyClass       = 'page-pricing';

require __DIR__ . '/includes/head.php';
?>

<!-- 2 · Page hero — left-aligned h1 + paragraph over the mint wash -->
<?php /* Deliberately not .page-hero: p-core.css defines that for the other
         page heroes and every p-*.css is concatenated into one bundle at build
         time, so the two would collide. See docs/reports/dev2.md. */ ?>
<section class="pricing-hero hero-wash" aria-labelledby="pricing-hero-h">
  <div class="container-ep">
    <h1 id="pricing-hero-h"><?= ep_lines("Choose The Right Package\nFor Your Book Journey") ?></h1>
    <p class="lead text-body-ep pricing-hero__text">
      Whether you're starting from an idea or ready to publish, we have flexible packages
      designed to fit your needs.
    </p>
  </div>
</section>

<!-- 3-5 · Shared sections -->
<?php require __DIR__ . '/includes/components/plans.php'; ?>
<?php require __DIR__ . '/includes/components/faq.php'; ?>
<?php require __DIR__ . '/includes/components/cta-wizard.php'; ?>

<!-- 6 · Footer + book strip -->
<?php require __DIR__ . '/includes/footer.php'; ?>
