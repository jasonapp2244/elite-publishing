<?php
declare(strict_types=1);

/**
 * Thank-you page — where forms/contact-handler.php sends a visitor after a
 * message has been accepted for delivery.
 *
 * Reachable at /thankyou through the extensionless rewrite in .htaccess.
 *
 * It is deliberately a plain page with no state. It takes no query string: the
 * handler has nothing to tell it that is worth putting in a URL, and a URL is
 * the last place you want a name or an email address to end up — they get
 * pasted into support tickets, logged by analytics, and shared over the
 * shoulder.
 *
 * That does mean the page can be opened directly by anyone, which is fine. It
 * confirms nothing it has not been told; it thanks whoever is reading it.
 *
 * noindex, because a thank-you page in search results is a page that ranks for
 * your brand and tells a searcher their message was sent when it was not.
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'thankyou';
$pageTitle       = 'Thank You';
$pageDescription = 'Your message has been sent to the Elite Publishing team.';
$pageCss         = ['css/p-core.css'];
$pageNoIndex     = true;

require __DIR__ . '/includes/head.php';
?>

<section class="section thanks" aria-labelledby="thanks-h">
  <div class="container-ep thanks__inner">

    <span class="thanks__mark" aria-hidden="true">
      <?= ep_icon('check', ['size' => 34]) ?>
    </span>

    <h1 id="thanks-h">Thank you. Your message is on its way</h1>

    <p class="lead thanks__lead">
      Our editorial team reads every inquiry and will get back to you shortly,
      usually within one working day.
    </p>

    <p class="thanks__meta">
      Nothing to do now. If it is urgent, email us directly at
      <a href="mailto:<?= esc(EP_EMAIL) ?>"><?= esc(EP_EMAIL) ?></a>.
    </p>

    <p class="thanks__actions">
      <a class="ep-btn ep-btn--primary" href="<?= esc(url()) ?>">
        Back to Home <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
      <a class="ep-btn ep-btn--outline" href="<?= esc(url(ep_page_url('our-books'))) ?>">
        Browse Our Books <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </p>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
