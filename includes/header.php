<?php
declare(strict_types=1);

/** Site header: logo, primary nav (with Services dropdown), CTA, mobile burger. */

$nav = ep_nav();
?>
<header class="ep-header" id="ep-header">
  <div class="container-ep ep-header__inner">

    <a class="ep-logo" href="<?= esc(url()) ?>" aria-label="<?= esc(EP_NAME) ?> — home">
      <img src="<?= esc(asset('img/logo.png')) ?>" alt="<?= esc(EP_NAME) ?>"
           width="452" height="360" fetchpriority="high" decoding="sync">
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
    </nav>

    <a class="ep-btn ep-btn--primary ep-header__cta" href="<?= esc(url('contact.php')) ?>">
      Publish Your Book
      <?= ep_icon('arrow-up-right', ['class' => 'arrow']) ?>
    </a>

  </div>
</header>
<div class="nav-scrim" data-nav-scrim hidden></div>
