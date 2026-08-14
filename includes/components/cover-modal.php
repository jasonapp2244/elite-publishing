<?php
declare(strict_types=1);

/**
 * Book-cover lightbox — the empty shell the covers open into.
 *
 * ---------------------------------------------------------------------------
 * WHAT IT IS
 * ---------------------------------------------------------------------------
 * Clicking any book cover shows that cover enlarged and centred. This file is
 * only the container; initCoverZoom() in assets/js/main.js copies the clicked
 * cover's image and caption into it and calls showModal().
 *
 * It is deliberately EMPTY in the markup. Rendering every cover a second time
 * at full size would put a dozen more <img> tags on the page for something
 * most visitors never open, and the covers are already on the page — copying
 * the one that was clicked costs nothing and cannot fall out of sync with it.
 *
 * ---------------------------------------------------------------------------
 * WHY IT REUSES .ep-modal
 * ---------------------------------------------------------------------------
 * Same <dialog> foundation as the "Publish Your Book" popup: showModal() gives
 * the focus trap, the Escape key, the inert background and the ::backdrop for
 * free, all of which a hand-rolled lightbox has to reimplement and usually
 * gets subtly wrong. The .ep-modal class carries the shared panel, backdrop
 * and close-button styling; .cover-modal only changes what a picture needs
 * that a form does not — no padding, and a panel that shrinks to the image.
 *
 * ---------------------------------------------------------------------------
 * WHERE IT IS INCLUDED
 * ---------------------------------------------------------------------------
 * Once per page from includes/footer.php, beside publish-modal.php, and for
 * the same reason: a dialog belongs at the end of the body. It is cheap enough
 * to include everywhere — an empty dialog is a handful of bytes and no
 * requests — and initCoverZoom() binds nothing on a page with no covers.
 */

?>
<?php /* aria-labelledby points at the caption title, which the JS fills in.
         Empty at rest is correct: the dialog is not reachable until a cover
         has been clicked, and by then the title is there. */ ?>
<dialog class="ep-modal cover-modal" id="cover-modal" aria-labelledby="cover-modal-title">
  <div class="ep-modal__panel cover-modal__panel">

    <button class="ep-modal__close" type="button" data-modal-close
            aria-label="Close this cover">
      <?= ep_icon('close', ['size' => 20]) ?>
    </button>

    <figure class="cover-modal__figure">
      <?php /* An empty container, not an <img>. ep_srcset() renders each cover
               as a <picture> with AVIF and WebP <source> children and a JPG
               <img> fallback, so the format the browser actually chose lives on
               a SIBLING of the <img>, not on the <img> itself. Copying the
               img's own src and srcset would therefore throw AVIF and WebP away
               and serve everyone the JPG.

               initCoverZoom() clones the whole <picture> in here instead, which
               keeps the format negotiation intact and cannot drift from how the
               cover is marked up in the rail. */ ?>
      <div class="cover-modal__media"></div>

      <figcaption class="cover-modal__cap">
        <span class="cover-modal__title" id="cover-modal-title"></span>
        <span class="cover-modal__author"></span>
      </figcaption>
    </figure>

  </div>
</dialog>
