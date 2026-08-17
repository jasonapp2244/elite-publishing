<?php
declare(strict_types=1);

/**
 * The site header — logo, primary nav (with Services dropdown), CTA, burger.
 *
 * SINGLE SOURCE OF TRUTH. This is the only place the header's markup exists.
 * It is rendered by:
 *   includes/header.php      every main-site page
 *   includes/lp-header.php   every landing page
 *
 * That split matters: the two callers need different things AROUND the header
 * (the main site's version sits inside a full document opened by head.php; the
 * LP version has to bring its own stylesheet along), but the header itself must
 * be identical on all of them. Changing the logo, a nav item or the CTA here
 * changes it everywhere, which is the entire point.
 *
 * Nav items come from ep_nav() in includes/config.php, so the links are shared
 * too — not just the shape of them.
 *
 * Behaviour (burger, dropdowns, sticky shadow) comes from assets/js/main.js,
 * whose initNav/initDropdowns/initStickyHeader each no-op when their markup is
 * absent, so it is safe to load on a landing page that has nothing else.
 */

$nav = ep_nav();
?>
<header class="ep-header" id="ep-header">
  <div class="container-ep ep-header__inner">

    <a class="ep-logo" href="<?= esc(url()) ?>" aria-label="<?= esc(EP_NAME) ?> — home">
      <?php /* No fetchpriority here: the logo is small and would compete with
               the page's real LCP element (the hero band / hero photo) for
               early bandwidth. Eager + sync is enough to avoid a header pop. */ ?>
      <img src="<?= esc(asset('img/logo.png')) ?>" alt="<?= esc(EP_NAME) ?>"
           width="452" height="360" loading="eager" decoding="sync">
    </a>

    <button class="ep-burger" type="button"
            aria-expanded="false" aria-controls="ep-nav" aria-label="Open menu"
            data-nav-toggle>
      <?= ep_icon('menu', ['size' => 22]) ?>
    </button>

    <nav class="ep-nav" id="ep-nav" aria-label="Primary">
      <?php foreach ($nav as $item): ?>
        <?php $active = is_active($item['key']) || has_active_child($item); ?>

        <?php if (!empty($item['children'])): ?>
          <div class="ep-nav__item<?= $active ? ' is-active' : '' ?>" data-dropdown>
            <button class="ep-nav__link" type="button"
                    aria-expanded="false" aria-controls="dd-<?= esc($item['key']) ?>">
              <?= esc($item['label']) ?>
              <?= ep_icon('chevron-down', ['class' => 'ep-nav__caret']) ?>
            </button>
            <div class="ep-dropdown" id="dd-<?= esc($item['key']) ?>">
              <?php foreach ($item['children'] as $child): ?>
                <a href="<?= esc(url($child['href'])) ?>"
                   <?= is_active($child['key']) ? 'aria-current="page"' : '' ?>>
                  <?= esc($child['label']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <div class="ep-nav__item<?= $active ? ' is-active' : '' ?>">
            <a class="ep-nav__link" href="<?= esc(url($item['href'])) ?>"
               <?= $active ? 'aria-current="page"' : '' ?>>
              <?= esc($item['label']) ?>
            </a>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php /* The header CTA is hidden below 1200px, which left the site's only
               primary conversion control absent on every tablet and phone. This
               copy lives inside the drawer and is display:none on desktop, so
               only one of the two is ever in the accessibility tree. */ ?>
      <a class="ep-btn ep-btn--primary ep-nav__cta" href="<?= esc(url(ep_page_url('contact'))) ?>"
         data-modal-open="publish-modal">
        Publish Your Book
        <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
      </a>
    </nav>

    <a class="ep-btn ep-btn--primary ep-header__cta" href="<?= esc(url(ep_page_url('contact'))) ?>"
       data-modal-open="publish-modal">
      Publish Your Book
      <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
    </a>

  </div>
</header>
<div class="nav-scrim" data-nav-scrim hidden></div>
