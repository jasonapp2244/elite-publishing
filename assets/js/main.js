/* ==========================================================================
   Elite Publishing — global behaviour
   Vanilla JS, no dependencies, loaded with `defer`. Bootstrap's JS bundle is
   deliberately NOT loaded: everything interactive here is hand-rolled so the
   page ships ~80KB less script.
   ========================================================================== */
(function () {
  'use strict';

  var mqDesktop = window.matchMedia('(min-width: 1200px)');

  /* --- Mobile nav -------------------------------------------------------- */
  function initNav() {
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav    = document.getElementById('ep-nav');
    var scrim  = document.querySelector('[data-nav-scrim]');
    if (!toggle || !nav) return;

    function setOpen(open) {
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      nav.classList.toggle('is-open', open);
      if (scrim) {
        scrim.classList.toggle('is-open', open);
        scrim.hidden = !open;
      }
      document.body.style.overflow = open ? 'hidden' : '';
    }

    toggle.addEventListener('click', function () {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    if (scrim) scrim.addEventListener('click', function () { setOpen(false); });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setOpen(false);
        toggle.focus();
      }
    });

    // Reset state when crossing the desktop breakpoint.
    mqDesktop.addEventListener('change', function () { setOpen(false); });
  }

  /* --- Services dropdown ------------------------------------------------- */
  function initDropdowns() {
    var items = document.querySelectorAll('[data-dropdown]');

    Array.prototype.forEach.call(items, function (item) {
      var btn = item.querySelector('.ep-nav__link');
      if (!btn) return;
      var closeTimer;

      function open(state) {
        item.classList.toggle('is-open', state);
        btn.setAttribute('aria-expanded', String(state));
      }

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        open(!item.classList.contains('is-open'));
      });

      // Hover only applies on pointer devices at desktop width.
      item.addEventListener('mouseenter', function () {
        if (!mqDesktop.matches) return;
        clearTimeout(closeTimer);
        open(true);
      });
      item.addEventListener('mouseleave', function () {
        if (!mqDesktop.matches) return;
        closeTimer = setTimeout(function () { open(false); }, 140);
      });

      // Close when focus leaves the whole item (keyboard users).
      item.addEventListener('focusout', function (e) {
        if (!item.contains(e.relatedTarget)) open(false);
      });

      document.addEventListener('click', function (e) {
        if (!item.contains(e.target)) open(false);
      });
    });
  }

  /* --- Sticky header shadow ---------------------------------------------- */
  function initStickyHeader() {
    var header = document.getElementById('ep-header');
    if (!header) return;

    var sentinel = document.createElement('div');
    sentinel.setAttribute('aria-hidden', 'true');
    header.parentNode.insertBefore(sentinel, header);

    new IntersectionObserver(function (entries) {
      header.classList.toggle('is-stuck', !entries[0].isIntersecting);
    }, { threshold: 1 }).observe(sentinel);
  }

  /* --- FAQ accordion ----------------------------------------------------- */
  function initAccordions() {
    var buttons = document.querySelectorAll('.ep-faq__btn');

    Array.prototype.forEach.call(buttons, function (btn) {
      var panel = document.getElementById(btn.getAttribute('aria-controls'));
      if (!panel) return;

      // Start collapsed unless the markup opted this one open.
      var startOpen = btn.getAttribute('aria-expanded') === 'true';
      panel.hidden = !startOpen;

      btn.addEventListener('click', function () {
        var isOpen = btn.getAttribute('aria-expanded') === 'true';
        var group  = btn.closest('[data-accordion]');

        // Single-open behaviour within a group.
        if (group && !isOpen) {
          Array.prototype.forEach.call(group.querySelectorAll('.ep-faq__btn'), function (other) {
            if (other === btn) return;
            var otherPanel = document.getElementById(other.getAttribute('aria-controls'));
            other.setAttribute('aria-expanded', 'false');
            if (otherPanel) otherPanel.hidden = true;
          });
        }

        btn.setAttribute('aria-expanded', String(!isOpen));
        panel.hidden = isOpen;
      });
    });
  }

  /* --- Horizontal scrollers ---------------------------------------------- */
  function initScrollers() {
    var scrollers = document.querySelectorAll('[data-scroller]');

    Array.prototype.forEach.call(scrollers, function (wrap) {
      var track = wrap.querySelector('.ep-scroller');
      var prev  = wrap.querySelector('[data-scroll-prev]');
      var next  = wrap.querySelector('[data-scroll-next]');
      if (!track) return;

      function step() {
        var first = track.firstElementChild;
        if (!first) return track.clientWidth;
        var gap = parseFloat(getComputedStyle(track).columnGap || '24') || 24;
        return first.getBoundingClientRect().width + gap;
      }

      function sync() {
        var max = track.scrollWidth - track.clientWidth - 1;
        if (prev) prev.disabled = track.scrollLeft <= 0;
        if (next) next.disabled = track.scrollLeft >= max;
      }

      if (prev) prev.addEventListener('click', function () {
        track.scrollBy({ left: -step(), behavior: 'smooth' });
      });
      if (next) next.addEventListener('click', function () {
        track.scrollBy({ left: step(), behavior: 'smooth' });
      });

      track.addEventListener('scroll', sync, { passive: true });
      window.addEventListener('resize', sync, { passive: true });
      sync();
    });
  }

  /* --- Boot -------------------------------------------------------------- */
  function boot() {
    initNav();
    initDropdowns();
    initStickyHeader();
    initAccordions();
    initScrollers();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
