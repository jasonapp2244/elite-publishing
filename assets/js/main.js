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
      /* A looping rail renders its children twice (see
         components/services-carousel.php). One "copy" is therefore half the
         scrollable content, and stepping past that point can be rewound by
         exactly that much without anything appearing to move: the pixels either
         side of the seam are identical. */
      var loops = wrap.hasAttribute('data-loop');

      /* The distance from the first card to its duplicate — the DOM's own
         measurement of one copy.

         Two other ways to get this number were wrong. scrollWidth / 2 includes
         the track's padding-inline and overshoots by ~39px. step() * (cells / 2)
         looks exact but multiplies a fractional card width eight times: it came
         out at 2699.2 where the rail actually rests at 2696, so `scrollLeft >=
         copyWidth` was never true and the rail simply stopped at the seam.
         Subtracting two offsetLefts cannot drift, whatever the card width. */
      function copyWidth() {
        var cells = track.children;
        var half  = Math.floor(cells.length / 2);
        if (!half) return 0;
        return cells[half].offsetLeft - cells[0].offsetLeft;
      }

      /* The seam is crossed AFTER the rail comes to rest, never before a move.

         The obvious version — rewind, then immediately animate one step — does
         not work in Chrome. An instant scrollLeft assignment followed in the
         same frame (or the next, or 300ms later) by a smooth scrollBy leaves two
         scrolls resolving at once, and with `scroll-snap-type: x mandatory` the
         browser keeps the instant one and silently discards the animation: the
         rail rewound to 0 and then sat there, one card short, on every lap.
         Yielding a frame did not help, and neither did a timeout.

         Doing it this way round means only ONE animated scroll is ever in
         flight. Steps are always plain forward scrollBy calls that cannot be
         dropped; the rewind runs 250ms after the last scroll event, when the
         rail is still and nothing can conflict with it. The two copies are
         identical, so landing one copy back is invisible.

         There is room to overshoot before rewinding: two copies make the
         scrollable range nearly twice a copy, so a step past the seam always
         lands inside real content. */
      var seamTimer = null;

      function watchSeam() {
        if (!loops) return;
        window.clearTimeout(seamTimer);
        seamTimer = window.setTimeout(function () {
          var w = copyWidth();
          /* -1 so a rail resting exactly on the seam is not left there by a
             sub-pixel: scrollLeft is fractional and the comparison must not be
             the thing that decides whether the loop continues. */
          if (w > 0 && track.scrollLeft >= w - 1) {
            track.scrollLeft -= w;
            sync();
          }
        }, 250);
      }

      function stepForward() {
        track.scrollBy({ left: step(), behavior: 'smooth' });
        sync();
      }

      function stepBack() {
        /* Wrapping backwards off the start is the one move that must be
           instant. Animating it would mean an assignment plus an animation
           again, which is exactly what gets dropped — and the visitor is going
           backwards through identical content either way, so there is nothing
           to see. Land one card short of the seam: the same card that sits one
           step behind the first, one copy along. */
        if (loops && track.scrollLeft < step() / 2) {
          track.scrollLeft = copyWidth() - step();
          sync();
          return;
        }
        track.scrollBy({ left: -step(), behavior: 'smooth' });
        sync();
      }

      var queued = false;

      function sync() {
        if (queued) return;
        queued = true;
        window.requestAnimationFrame(function () {
          queued = false;
          /* A looping rail has no ends, so its arrows must never disable —
             doing so would grey out Next exactly when the loop is working. */
          if (loops) {
            if (prev) prev.disabled = false;
            if (next) next.disabled = false;
            return;
          }
          var max  = track.scrollWidth - track.clientWidth - 1;
          var left = track.scrollLeft;                 // all reads first
          if (prev) prev.disabled = left <= 0;         // then all writes
          if (next) next.disabled = left >= max;
        });
      }

      if (prev) prev.addEventListener('click', stepBack);
      if (next) next.addEventListener('click', stepForward);

      track.addEventListener('scroll', sync, { passive: true });
      track.addEventListener('scroll', watchSeam, { passive: true });
      window.addEventListener('resize', sync, { passive: true });
      sync();

      initAutoplay(wrap, track, step, sync, loops ? stepForward : null);
    });
  }

  /* --- Carousel autoplay -------------------------------------------------- */
  /* Opt-in per rail with data-autoplay on the [data-scroller] wrapper, so the
     book rails stay manual and only the marked carousel advances itself.

     It stops rather than merely pauses once the visitor takes control of the
     RAIL. Content that keeps moving under someone trying to read it is worse
     than no autoplay at all.

     What counts as taking control is the whole difficulty, and the first
     version got it wrong in a way that made the carousel look broken: it bound
     pointerdown/wheel/touchstart/keydown/click to the section wrapper and
     stopped on the first of them. The wrapper is a full-width band, so one
     notch of the mouse wheel while scrolling PAST the section killed the
     autoplay for the rest of the visit — and on a phone, touchstart fires the
     instant a finger lands on a card to scroll the page vertically, so the
     carousel almost never ran on mobile at all. Both are the visitor moving the
     PAGE, not the rail, and neither should have counted.

     So control is now judged by the outcome instead of the input: after every
     advance we remember the offset we asked for, and if the rail settles
     anywhere else, something other than us moved it — a swipe, a horizontal
     trackpad gesture, an arrow key, a click on the nav buttons. That is true
     control, and it stops the motion. Vertical page scrolling never changes
     scrollLeft, so it no longer registers.

     WCAG 2.2.2 wants a mechanism to pause anything that moves for more than
     five seconds. The arrows are that mechanism here — using either one ends the
     motion for the session — and initScrollerA11y() makes the rail itself
     focusable, so a keyboard user can stop it by tabbing to it. The alternative
     is a visible pause button, which the marquees carry but which this section's
     design does not draw. See docs/QA-REPORT.md. */
  function initAutoplay(wrap, track, step, sync, loopStep) {
    if (!wrap.hasAttribute('data-autoplay')) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var DELAY = parseInt(wrap.getAttribute('data-autoplay'), 10) || 4500;
    var timer = null;
    var stopped = false;
    var visible = true;
    var hovered = false;

    function canRun() {
      return !stopped && visible && !hovered && !document.hidden
        && track.scrollWidth > track.clientWidth + 1;
    }

    function advance() {
      if (!canRun()) return;

      /* A looping rail only ever moves forward — stepForward() owns the seam,
         rewinding by exactly one duplicated copy onto identical pixels while
         the rail is still, so the visitor never sees the jump. */
      if (loopStep) {
        loopStep();
        return;
      }

      var max = track.scrollWidth - track.clientWidth - 2;
      if (track.scrollLeft >= max) {
        track.scrollTo({ left: 0, behavior: 'smooth' });
      } else {
        track.scrollBy({ left: step(), behavior: 'smooth' });
      }
      sync();
    }

    function start() { if (timer === null) timer = window.setInterval(advance, DELAY); }
    function pause() { if (timer !== null) { window.clearInterval(timer); timer = null; } }

    function stop() {
      stopped = true;
      pause();
    }

    /* --- What counts as the visitor taking control -----------------------
       Each listener below is a gesture that can only mean "I am driving this
       rail". Everything else — scrolling the page past it, tapping a card,
       resting the cursor on it — leaves the autoplay alone.

       Watching the scroll position instead was tried twice and abandoned. Our
       own advances are indistinguishable from a swipe once they have finished:
       Chrome's smooth scrollBy eases out short of the target and scroll-snap
       nudges the rail again some unbounded time later, so any "did it land
       where we asked?" test either fires on our own motion or needs a timing
       window long enough to miss real swipes. Reading the input is exact. */

    // The arrows. These are also the WCAG 2.2.2 pause mechanism.
    Array.prototype.forEach.call(
      wrap.querySelectorAll('[data-scroll-prev], [data-scroll-next]'),
      function (btn) { btn.addEventListener('click', stop); }
    );

    // A sideways trackpad or shift-wheel gesture over the rail. A plain
    // vertical wheel is the visitor scrolling the PAGE and is ignored — binding
    // that was the bug: one notch while scrolling past the section killed the
    // carousel for the rest of the visit.
    track.addEventListener('wheel', function (e) {
      if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) stop();
    }, { passive: true });

    // A drag or swipe across the rail. Judged on horizontal movement, not on
    // the touch itself: on a phone the first touch of a vertical page scroll
    // lands on a card, and treating that as control meant the carousel never
    // ran on mobile at all. 12px is past the tap slop and well short of a
    // deliberate swipe.
    var dragFrom = null;
    track.addEventListener('pointerdown', function (e) { dragFrom = e.clientX; }, { passive: true });
    track.addEventListener('pointermove', function (e) {
      if (dragFrom !== null && Math.abs(e.clientX - dragFrom) > 12) {
        dragFrom = null;
        stop();
      }
    }, { passive: true });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (evt) {
      track.addEventListener(evt, function () { dragFrom = null; }, { passive: true });
    });

    // Keyboard: the arrow/paging keys scroll a focused rail.
    track.addEventListener('keydown', function (e) {
      if (/^(Arrow|Page|Home|End)/.test(e.key)) stop();
    });

    /* Focus landing inside the rail is deliberate on its own — a keyboard user
       has arrived and is about to read or drive it. */
    track.addEventListener('focusin', stop);

    /* Do not animate a rail nobody is looking at — it burns battery and, worse,
       a visitor who scrolls back up finds the carousel somewhere they did not
       leave it. */
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        visible = entries[0].isIntersecting;
        if (canRun()) { start(); } else { pause(); }
      }, { threshold: 0.25 }).observe(wrap);
    } else {
      start();
    }

    document.addEventListener('visibilitychange', function () {
      if (canRun()) { start(); } else { pause(); }
    });

    wrap.addEventListener('mouseenter', function () { hovered = true; pause(); });
    wrap.addEventListener('mouseleave', function () {
      hovered = false;
      if (canRun()) start();
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

  /* The marquee Pause/Play handler stood here. Removed with the buttons at the
     client's request — see the note in includes/components/testimonials.php.
     The marquees now pause on hover and :focus-within from CSS alone, and
     prefers-reduced-motion stops them outright. */

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

  /* --- Modal dialogs ------------------------------------------------------ */
  /* Opens the "Publish Your Book" popup (includes/components/publish-modal.php)
     from any [data-modal-open="<id>"] control.

     Built on <dialog>.showModal(), which supplies the focus trap, the Escape
     key, the inert background and the return of focus to the opening button —
     all of which a hand-rolled modal has to reimplement and usually gets subtly
     wrong. Baseline across browsers since March 2022.

     The openers are real links with real hrefs. If this script never runs, or
     the browser has no <dialog>, or the target id is missing, preventDefault()
     is never reached and the click navigates exactly as it did before. That is
     the whole fallback, and it is why the buttons were left as <a>. */
  function initModals() {
    var openers = document.querySelectorAll('[data-modal-open]');
    if (!openers.length) return;

    var supported = typeof HTMLDialogElement === 'function' &&
                    typeof document.createElement('dialog').showModal === 'function';
    if (!supported) return;

    function lock(on) {
      /* showModal() makes the page inert but does NOT stop it scrolling behind
         the dialog on iOS Safari and older Chrome — the visitor drags the form
         and the page moves underneath. A class on <html> is the smallest fix
         that does not fight the dialog's own scroll container. */
      document.documentElement.classList.toggle('has-modal', on);
    }

    function open(dialog, opener) {
      if (dialog.open) return;
      dialog.showModal();
      lock(true);

      /* Land on the dialog's own heading rather than the close button. Without
         this, showModal() focuses the first tabbable element — the close
         button — and a screen reader announces "close" as the first thing about
         a form the visitor just asked to see. */
      var head = dialog.querySelector('.ep-modal__title');
      if (head) {
        head.setAttribute('tabindex', '-1');
        head.focus({ preventScroll: true });
      }
      if (opener) dialog.__opener = opener;
    }

    function close(dialog) {
      if (dialog.open) dialog.close();
    }

    Array.prototype.forEach.call(openers, function (btn) {
      var id = btn.getAttribute('data-modal-open');
      var dialog = document.getElementById(id);
      if (!dialog || typeof dialog.showModal !== 'function') return;

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        open(dialog, btn);
      });
    });

    Array.prototype.forEach.call(document.querySelectorAll('dialog.ep-modal'), function (dialog) {
      dialog.addEventListener('close', function () { lock(false); });

      Array.prototype.forEach.call(dialog.querySelectorAll('[data-modal-close]'), function (btn) {
        btn.addEventListener('click', function () { close(dialog); });
      });

      /* Click outside to dismiss. The dialog element fills the viewport and the
         panel inside it is what you see, so a click whose target IS the dialog
         landed on the backdrop and nowhere else. Comparing against the panel's
         bounding box instead breaks the moment the panel scrolls. */
      dialog.addEventListener('click', function (e) {
        if (e.target === dialog) close(dialog);
      });

      /* The server rejected a submission that came from this dialog and the
         page has nowhere else to show the error — reopen so the visitor reads
         it, with their answers still in the fields. */
      if (dialog.hasAttribute('data-modal-autoopen')) open(dialog, null);
    });
  }

  /* --- Book-cover lightbox ------------------------------------------------ */
  /* Clicking a cover shows it enlarged and centred, in the empty dialog from
     includes/components/cover-modal.php.

     Self-contained rather than routed through initModals(). That function
     returns early on a page with no [data-modal-open] control, and its close
     and backdrop handlers sit AFTER that return — so a page with covers but no
     popup would get a lightbox that opens and cannot be closed with anything
     but Escape. The handlers below are bound whether or not initModals ran.
     Binding the same close twice is harmless: dialog.close() is guarded on
     .open, so the second call is a no-op.

     The cover is CLONED from the card that was clicked, not re-fetched. Each
     cover is a <picture> — AVIF and WebP <source> children with a JPG <img>
     fallback — so cloning the whole element is what keeps the format
     negotiation intact. Copying src/srcset off the inner <img> instead would
     silently serve everyone the JPG, because the format the browser actually
     chose is on a sibling <source>, not on the <img>.

     Only `sizes` is rewritten after the clone: the dialog shows the cover at
     roughly 380px rather than the rail's 240px, and sizes has to say so on
     every <source> as well as the <img> or the browser picks for the old slot.
     Nothing new is downloaded when that source is already cached from the rail. */
  function initCoverZoom() {
    var dialog = document.getElementById('cover-modal');
    var zooms  = document.querySelectorAll('[data-cover-zoom]');
    if (!dialog || !zooms.length) return;
    if (typeof dialog.showModal !== 'function') return;   // no <dialog>: covers stay static

    var media  = dialog.querySelector('.cover-modal__media');
    var title  = dialog.querySelector('.cover-modal__title');
    var author = dialog.querySelector('.cover-modal__author');
    if (!media) return;

    var SIZES = '(max-width: 600px) 78vw, 380px';

    function open(btn) {
      if (dialog.open) return;

      /* The <picture> when there is one, the bare <img> when there is not —
         our-books.php and any future rail may use either. */
      var source = btn.querySelector('picture') || btn.querySelector('img');
      if (!source) return;

      var clone = source.cloneNode(true);
      var cimg  = clone.tagName === 'IMG' ? clone : clone.querySelector('img');
      if (!cimg) return;

      /* Restate the slot width on the <img> and on every <source>. Also drop
         the rail's lazy-loading: this image is the only thing the visitor is
         looking at, so deferring it is exactly wrong. */
      cimg.setAttribute('sizes', SIZES);
      cimg.setAttribute('loading', 'eager');
      cimg.removeAttribute('class');
      Array.prototype.forEach.call(clone.querySelectorAll ? clone.querySelectorAll('source') : [],
        function (s) { s.setAttribute('sizes', SIZES); });

      media.textContent = '';
      media.appendChild(clone);

      /* The caption is the card's own, so the dialog cannot drift from the rail.
         Read from the figure the button sits in, not from the button. */
      var fig = btn.closest('.book-card');
      var t   = fig && fig.querySelector('.book-card__title');
      var a   = fig && fig.querySelector('.book-card__author');
      if (title)  title.textContent  = t ? t.textContent : '';
      if (author) author.textContent = a ? a.textContent : '';

      dialog.showModal();
      document.documentElement.classList.add('has-modal');

      /* Land on the title, not the close button — same reasoning as the popup:
         a screen reader should say which cover this is before it says "close". */
      if (title) {
        title.setAttribute('tabindex', '-1');
        title.focus({ preventScroll: true });
      }
      dialog.__opener = btn;
    }

    Array.prototype.forEach.call(zooms, function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        open(btn);
      });
    });

    dialog.addEventListener('close', function () {
      document.documentElement.classList.remove('has-modal');
      /* Return focus to the cover that opened it. showModal() restores focus on
         its own in current browsers, but not when the opener was inside a rail
         that has since been scrolled by autoplay. */
      if (dialog.__opener && document.contains(dialog.__opener)) {
        dialog.__opener.focus({ preventScroll: true });
      }
      /* Drop the clone so a re-open cannot flash the previous cover before the
         new one decodes. */
      media.textContent = '';
    });

    Array.prototype.forEach.call(dialog.querySelectorAll('[data-modal-close]'), function (btn) {
      btn.addEventListener('click', function () { if (dialog.open) dialog.close(); });
    });

    /* Click the backdrop to dismiss — the dialog fills the viewport and the
       panel is what you see, so a click whose target IS the dialog missed it. */
    dialog.addEventListener('click', function (e) {
      if (e.target === dialog && dialog.open) dialog.close();
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
    initModals();
    initCoverZoom();
    initScrollerA11y();
    initReveal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
