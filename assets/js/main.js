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

      /* sync() reads layout (scrollWidth/clientWidth) and then writes to the
         DOM (button.disabled). Called straight from a scroll listener, once
         per scroller, that interleaving forces a synchronous reflow on every
         event — measured at 154ms across the page's rails. Coalescing into one
         animation frame collapses a burst of scroll events into a single
         read-then-write, and the frame callback runs at a point where layout
         is already being computed. */
      var queued = false;

      function sync() {
        if (queued) return;
        queued = true;
        window.requestAnimationFrame(function () {
          queued = false;
          var max  = track.scrollWidth - track.clientWidth - 1;
          var left = track.scrollLeft;                 // all reads first
          if (prev) prev.disabled = left <= 0;         // then all writes
          if (next) next.disabled = left >= max;
        });
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
      /* Measure every rail before touching any of them. Reading scrollWidth
         and then writing an attribute inside the same loop invalidates layout
         for the next rail's read, so an N-rail page pays N reflows. */
      var measured = Array.prototype.map.call(rails, function (rail) {
        return {
          rail: rail,
          overflows: rail.scrollWidth > rail.clientWidth + 1,
          reachable: rail.querySelector('a, button, [tabindex]:not([tabindex="-1"])')
        };
      });

      measured.forEach(function (m) {
        var rail = m.rail, overflows = m.overflows, reachable = m.reachable;

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

    /* Deferred past the first paint on purpose. Reading scrollWidth during
       boot forces the browser to compute the whole page's first layout
       synchronously, on the main thread, before anything is on screen —
       measured at 132ms here. Two frames later that layout already exists,
       so the same reads are nearly free. Nothing visual depends on this; it
       only adds a keyboard affordance to rails that overflow. */
    if (window.requestAnimationFrame) {
      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(sync);
      });
    } else {
      sync();
    }
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
    if (!window.requestAnimationFrame) return;   // no way to defer; skip motion

    /* Wait for the first paint before measuring. Every getBoundingClientRect
       below would otherwise force the browser to compute the entire page's
       layout synchronously during boot — 114ms on this page, all of it on the
       main thread, all of it before the visitor sees anything. Two frames
       later that layout is already done and the same reads cost nothing.
       Deferring is safe here precisely because no reveal target is above the
       fold: for those two frames the only elements not yet hidden are ones
       nobody can see. */
    window.requestAnimationFrame(function () {
      window.requestAnimationFrame(startReveal);
    });
  }

  function startReveal() {
    var SELECTOR = [
      /* headings and shared sections */
      '.section-head',
      '.books-head',
      '.journey__step',
      '.stories__grid > li',
      '.plans__grid > li',
      '.ep-faq__item',
      '.wizard-card',
      '.cta-block__copy',
      '.cta-green',
      '.panel-green',
      '.press-band__row',
      '.plat-card',
      /* home */
      '.book-band',
      '.proc-card',
      '.book-rail',
      /* service pages */
      '.svc-intro',
      '.svc-e2e',
      '.svc-why',
      /* about */
      '.value-card',
      '.why-grid > *',
      '.why-list__item',
      /* our books */
      '.books-carousel',
      /* landing pages — the hero and its form are above the fold and are
         filtered out by the fold test, not listed here */
      '.lp-stats__grid',
      '.lp-services__head',
      '.lp-cards > li',
      /* the services rail animates as one block, its cards do not */
      '.ep-scroller--4up',
      /* policy pages */
      '.doc__block'
    ].join(',');

    /* Horizontal rails and the marquee move on their own; the hero and header
       must never be hidden. Note the test below is `parentElement.closest`,
       not `el.closest` — an element that *is* a rail can animate as one block,
       but its cells cannot, because hiding a horizontally-scrolled child
       fights the scroll. */
    var EXCLUDE = '.ep-scroller, .marquee, .svc-hero, .ep-header';
    var fold    = window.innerHeight * 0.9;

    var targets = Array.prototype.filter.call(
      document.querySelectorAll(SELECTOR),
      function (el) {
        if (el.parentElement && el.parentElement.closest(EXCLUDE)) return false;

        /* Skip anything not currently rendered — inactive tab panels, closed
           accordions, hidden slides. A display:none element never intersects,
           so it would never receive .is-revealed, and would then appear at
           opacity 0 *permanently* the moment its tab was opened. */
        if (!el.getClientRects().length) return false;

        return el.getBoundingClientRect().top > fold;   // below the fold only
      }
    );
    if (!targets.length) return;

    /* Drop any element that sits inside another target: a child fading in
       while its parent is also fading in reads as a flicker, and the child's
       delay stacks on top of the parent's. Outermost block wins. */
    targets = targets.filter(function (el) {
      for (var p = el.parentElement; p; p = p.parentElement) {
        if (targets.indexOf(p) !== -1) return false;
      }
      return true;
    });

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
      // Bottom: a positive margin extends the root *past* the fold, so an
      // element starts its entrance just before it is actually on screen. A
      // negative margin (waiting until it is 8% inside) leaves a band at the
      // bottom of the viewport where content is visible but still transparent,
      // which looks like a bug if the visitor stops scrolling there.
      //
      // Top: a very large margin extends the root far *above* the viewport, so
      // anything already scrolled past counts as intersecting and is revealed
      // at once. Without this, a fast scroll — dragging the scrollbar, End,
      // find-in-page — can jump over a section between two frames; the observer
      // never sees it intersect and it stays transparent until the visitor
      // happens to scroll back up to it. An element is only ever hidden while
      // it is still below the viewport.
    }, { rootMargin: '10000px 0px 8% 0px', threshold: 0 });

    targets.forEach(function (el) { io.observe(el); });

    function revealAll() {
      targets.forEach(function (el) { el.classList.add('is-revealed'); });
    }

    // If anything is asked to print, make sure nothing is left invisible.
    window.addEventListener('beforeprint', revealAll);

    // Last-resort failsafe. Everything above is best-effort, but a block that
    // stays transparent is worse than no animation at all, so if the observer
    // has not accounted for every target a few seconds after load, show them.
    // In normal use this fires with nothing left to do.
    window.setTimeout(function () {
      var stuck = targets.filter(function (el) {
        return !el.classList.contains('is-revealed') &&
               el.getBoundingClientRect().top < window.innerHeight;
      });
      stuck.forEach(function (el) { el.classList.add('is-revealed'); });
    }, 4000);
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
