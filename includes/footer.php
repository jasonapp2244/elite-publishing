<?php
declare(strict_types=1);

/**
 * Main-site page ending: footer, modals, deferred scripts, closing document.
 * Include at the very bottom of every main-site page.
 *
 * The footer's own markup moved to components/site-footer.php when the landing
 * pages were standardised on the same chrome. What stays here is everything
 * that ends a MAIN-SITE document specifically — the closing </main>, the two
 * modals, main.js and any per-page scripts. A landing page ends differently and
 * uses includes/lp-footer.php, which renders the same footer component without
 * this apparatus.
 */
?>
</main>

<?php require __DIR__ . '/components/site-footer.php'; ?>

<?php /* The "Publish Your Book" popup. Last thing in the body, after every
         section that could claim the submission banner — see the component. */ ?>
<?php require __DIR__ . '/components/publish-modal.php'; ?>

<?php /* The book-cover lightbox. An empty shell — initCoverZoom() fills it from
         whichever cover was clicked. Included on every page for the same reason
         as the popup above; it binds nothing on a page with no covers. */ ?>
<?php require __DIR__ . '/components/cover-modal.php'; ?>

<script src="<?= esc(asset('js/main.js')) ?>" defer></script>
<?php foreach (($pageJs ?? []) as $js): ?>
<script src="<?= esc(asset($js)) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
