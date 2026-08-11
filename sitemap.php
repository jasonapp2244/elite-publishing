<?php
declare(strict_types=1);

/**
 * XML sitemap, generated from the nav model so it cannot drift out of sync
 * with the site. Served at /sitemap.xml via the .htaccess rewrite.
 *
 * Priorities reflect commercial intent: the home page and the ten service
 * pages are what the site is for; the policy pages are required, not promoted.
 */

require_once __DIR__ . '/includes/config.php';

/** @var array<string, array{loc: string, priority: string, freq: string}> */
/* Canonical extensionless paths — see EP_PAGE_URLS in includes/config.php. */
$urls = [
    ['loc' => ep_page_url('index'),            'priority' => '1.0', 'freq' => 'weekly'],
    ['loc' => ep_page_url('our-books'),        'priority' => '0.8', 'freq' => 'weekly'],
    ['loc' => ep_page_url('about'),            'priority' => '0.7', 'freq' => 'monthly'],
    ['loc' => ep_page_url('pricing'),          'priority' => '0.9', 'freq' => 'monthly'],
    ['loc' => ep_page_url('contact'),          'priority' => '0.8', 'freq' => 'monthly'],
    ['loc' => ep_page_url('privacy-policy'),   'priority' => '0.2', 'freq' => 'yearly'],
    ['loc' => ep_page_url('terms-conditions'), 'priority' => '0.2', 'freq' => 'yearly'],
];

foreach (array_keys(EP_SERVICES) as $slug) {
    $urls[] = ['loc' => 'services/' . $slug, 'priority' => '0.9', 'freq' => 'monthly'];
}

/* The four campaign landing pages. Listed because their copy is unique and
   indexable, at a lower priority than the service pages they sit alongside.
   If they turn out to be paid-traffic-only, they should be noindex instead of
   demoted — that is a marketing decision, and it is open in
   docs/CLIENT-QUESTIONS.md §46. */
foreach (['lp1', 'lp2', 'lp3', 'lp4'] as $lp) {
    $urls[] = ['loc' => $lp, 'priority' => '0.6', 'freq' => 'monthly'];
}

/** Last modified: the newest page/template mtime, not "now" — a sitemap that
 *  claims everything changed on every crawl is ignored. */
$stamp = 0;
foreach (glob(EP_ROOT . '/*.php') ?: [] as $file) {
    $stamp = max($stamp, (int) filemtime($file));
}
$lastmod = date('Y-m-d', $stamp ?: time());

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?= esc(EP_ORIGIN . url($u['loc'])) ?></loc>
    <lastmod><?= esc($lastmod) ?></lastmod>
    <changefreq><?= esc($u['freq']) ?></changefreq>
    <priority><?= esc($u['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
