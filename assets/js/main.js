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

    // Everything behind the drawer. Made inert while it is open so Tab cannot
    // walk out of the menu into a page the visitor cannot see or scroll to.
    var behind = [document.getElementById('main'), document.querySelector('.ep-footer')];

    function setOpen(open) {
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      nav.classList.toggle('is-open', open);
      if (scrim) {
        scrim.classList.toggle('is-open', open);
        scrim.hidden = !open;
      }
      document.body.style.overflow = open ? 'hidden' : '';

      behind.forEach(function (el) {
        if (!el) return;
        if (open) {
          el.setAttribute('inert', '');
        } else {
          el.removeAttribute('inert');
        }
      });
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

      // Escape closes it and returns focus to the trigger — the expected
      // behaviour for any expandable, and previously only wired to the burger.
      item.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (!item.classList.contains('is-open')) return;
        e.stopPropagation();
        open(false);
        btn.focus();
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

  /* --- Consultation wizard ----------------------------------------------- */
  /* Progressive enhancement: the markup ships every step visible with one
     working submit. CSS hides the inactive steps only once `.js` is on <html>,
     and this wires the stepping. With JS off the form still posts. */
  function initWizards() {
    var forms = document.querySelectorAll('[data-wizard]');

    Array.prototype.forEach.call(forms, function (form) {
      var steps = form.querySelectorAll('.wizard__step');
      var segs  = form.querySelectorAll('.wizard__seg');
      if (steps.length < 2) return;

      var index = 0;

      function show(next, moveFocus) {
        index = Math.max(0, Math.min(steps.length - 1, next));

        Array.prototype.forEach.call(steps, function (step, i) {
          step.classList.toggle('is-active', i === index);
        });
        Array.prototype.forEach.call(segs, function (seg, i) {
          seg.classList.toggle('is-done', i <= index);
        });

        // Move focus to the new question so keyboard and screen-reader users
        // land where the visual change happened. Only on a user-driven step
        // change — doing it during setup would yank focus (and scroll) to a
        // form halfway down a 9,000px page on every load.
        if (!moveFocus) return;
        var heading = steps[index].querySelector('.wizard__q');
        if (heading) {
          heading.setAttribute('tabindex', '-1');
          heading.focus({ preventScroll: true });
        }
      }

      /* A step with radios needs one chosen before Continue does anything. */
      function valid(step) {
        var radios = step.querySelectorAll('input[type="radio"]');
        if (!radios.length) return true;

        var chosen = Array.prototype.some.call(radios, function (r) { return r.checked; });
        if (!chosen) {
          var first = step.querySelector('.wizard__chip');
          if (first) {
            first.classList.add('is-shake');
            setTimeout(function () { first.classList.remove('is-shake'); }, 400);
          }
          radios[0].focus();
        }
        return chosen;
      }

      form.addEventListener('click', function (e) {
        var next = e.target.closest('[data-wizard-next]');
        var back = e.target.closest('[data-wizard-back]');

        if (next) {
          if (valid(steps[index])) show(index + 1, true);
        } else if (back) {
          show(index - 1, true);
        }
      });

      // Enter inside a field should advance, not submit early.
      form.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' || e.target.tagName === 'TEXTAREA') return;
        if (index < steps.length - 1) {
          e.preventDefault();
          if (valid(steps[index])) show(index + 1, true);
        }
      });

      /* If the server rejected this submission, open the step that owns the
         first invalid field rather than resetting to step 1 — otherwise the
         flagged inputs sit two steps away behind display:none and the visitor
         sees no reason why nothing happened. */
      var firstBad = form.querySelector('[aria-invalid="true"]');
      var badStep  = firstBad && firstBad.closest('.wizard__step');
      var startAt  = 0;

      if (badStep) {
        startAt = Array.prototype.indexOf.call(steps, badStep);
        if (startAt < 0) startAt = 0;
      }

      show(startAt, false);

      if (badStep) {
        // Put the banner in front of the visitor, then focus it so screen
        // readers announce the failure too.
        var banner = form.parentNode.querySelector('.ep-alert');
        var anchor = banner || badStep;
        anchor.scrollIntoView({ block: 'center' });
        if (banner) banner.focus({ preventScroll: true });
        if (firstBad) firstBad.setAttribute('data-first-error', 'true');
      }
    });
  }

  /* --- Marquee pause (WCAG 2.2.2) ---------------------------------------- */
  function initMarquees() {
    var toggles = document.querySelectorAll('[data-marquee-toggle]');

    Array.prototype.forEach.call(toggles, function (btn) {
      var marquee = btn.closest('.marquee');
      if (!marquee) return;

      btn.addEventListener('click', function () {
        var paused = marquee.classList.toggle('is-paused');
        btn.setAttribute('aria-pressed', String(paused));
      });
    });
  }

  /* --- Keyboard-reachable carousels (WCAG 2.1.1) ------------------------- */
  /* Below 768px the prev/next buttons are hidden and the cover rails hold no
     focusable children, so there was no keyboard path past the first slide.
     Making a scrollable region focusable lets it be scrolled with the arrow
     keys — but only when it actually overflows, so we don't add pointless tab
     stops on desktop. */
  function initScrollerA11y() {
    var rails = document.querySelectorAll('.ep-scroller, [data-rail]');

    function sync() {
      Array.prototype.forEach.call(rails, function (rail) {
        var overflows = rail.scrollWidth > rail.clientWidth + 1;
        var reachable = rail.querySelector('a, button, [tabindex]:not([tabindex="-1"])');

        if (overflows && !reachable) {
          // tabindex + a label is all a scrollable region needs. Do NOT set
          // role="group" here: these rails are <ul>s, and overriding the
          // implicit list role orphans every <li> inside them.
          rail.setAttribute('tabindex', '0');
          if (!rail.hasAttribute('aria-label')) {
            rail.setAttribute('aria-label', 'Scrollable list — use the arrow keys to move through it');
          }
        } else {
          rail.removeAttribute('tabindex');
        }
      });
    }

    sync();
    window.addEventListener('resize', sync, { passive: true });
  }

  /* --- Scroll reveal ----------------------------------------------------- */
  /* Blocks fade and rise as they enter the viewport. Deliberately conservative:
     - nothing above the fold is touched, so the LCP element paints immediately
       and the hero never animates in late;
     - carousels and the marquee are excluded — they already move, and hiding a
       horizontally-scrolled child fights the scroll;
     - only opacity/transform, so layout never shifts (CLS stays 0);
     - the hiding CSS is gated on a class this function adds, so if any of the
       preconditions fail the page simply renders normally. */
  function initReveal() {
    if (!('IntersectionObserver' in window)) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var SELECTOR = [
      '.section-head',
      '.svc-card-cell',
      '.journey__step',
      '.stories__grid > li',
      '.plans__grid > li',
      '.ep-faq__item',
      '.wizard-card',
      '.cta-block__copy',
      '.panel-green',
      '.press-band__row',
      '.plat-card',
      '.value-card',
      '.why-grid > *',
      '.doc__block'
    ].join(',');

    var EXCLUDE = '.ep-scroller, .marquee, .svc-hero, .ep-header';
    var fold    = window.innerHeight * 0.9;

    var targets = Array.prototype.filter.call(
      document.querySelectorAll(SELECTOR),
      function (el) {
        if (el.closest(EXCLUDE)) return false;
        return el.getBoundingClientRect().top > fold;   // below the fold only
      }
    );
    if (!targets.length) return;

    targets.forEach(function (el) {
      el.setAttribute('data-reveal', '');

      // Stagger siblings so a grid arrives as a wave rather than a slab.
      var sibs = el.parentNode
        ? Array.prototype.indexOf.call(el.parentNode.children, el)
        : 0;
      el.style.setProperty('--reveal-delay', Math.min(sibs, 5) * 70 + 'ms');
    });

    document.documentElement.classList.add('has-reveal');

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-revealed');
        io.unobserve(entry.target);      // reveal once; never re-hide on scroll up
      });
      // Positive bottom margin extends the root *past* the fold, so an element
      // starts its entrance just before it is actually on screen. A negative
      // margin (waiting until it is 8% inside) leaves a band at the bottom of
      // the viewport where content is visible but still transparent — which
      // looks like a bug if the visitor happens to stop scrolling there.
    }, { rootMargin: '0px 0px 8% 0px', threshold: 0 });

    targets.forEach(function (el) { io.observe(el); });

    // If anything is asked to print, or the tab is restored from bfcache
    // mid-scroll, make sure nothing is left invisible.
    window.addEventListener('beforeprint', function () {
      targets.forEach(function (el) { el.classList.add('is-revealed'); });
    });
  }

  /* --- Boot -------------------------------------------------------------- */
  function boot() {
    initNav();
    initDropdowns();
    initStickyHeader();
    initAccordions();
    initScrollers();
    initWizards();
    initMarquees();
    initScrollerA11y();
    initReveal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
