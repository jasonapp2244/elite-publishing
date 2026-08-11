/* ==========================================================================
   Elite Publishing — home page behaviour (index.php only)
   Two widgets that exist nowhere else on the site:
     1. "Our Publishing Process" — 7-tab panel (SPEC §C.1 §7)
     2. "Genres" — client-side cover filter (SPEC §C.1 §8)
   Everything else on the page is wired by assets/js/main.js.
   Vanilla, deferred, no dependencies.
   ========================================================================== */
(function () {
  'use strict';

  /* --- 7 — Publishing process tabs --------------------------------------- */
  /* Full tab semantics: roving tabindex, arrow/Home/End keys, automatic
     activation. The prev/next buttons live inside each panel, so after a step
     change focus is moved to the same control in the panel that replaced it —
     otherwise it would be lost with the hidden panel. */
  function initProcess() {
    var lists = document.querySelectorAll('[data-proc-tabs]');

    Array.prototype.forEach.call(lists, function (list) {
      var tabs = list.querySelectorAll('[role="tab"]');
      if (tabs.length < 2) return;

      var panels = Array.prototype.map.call(tabs, function (tab) {
        return document.getElementById(tab.getAttribute('aria-controls'));
      });

      var index = 0;

      function show(next, restore) {
        // Wrap around: the design's prev/next are always live (SPEC §B.14).
        index = (next + tabs.length) % tabs.length;

        Array.prototype.forEach.call(tabs, function (tab, i) {
          var on = i === index;
          tab.setAttribute('aria-selected', String(on));
          tab.setAttribute('tabindex', on ? '0' : '-1');
          tab.classList.toggle('is-active', on);
          if (panels[i]) panels[i].hidden = !on;
        });

        if (restore === 'tab') {
          tabs[index].focus();
        } else if (restore && panels[index]) {
          var btn = panels[index].querySelector('[data-proc-' + restore + ']');
          if (btn) btn.focus();
        }
      }

      Array.prototype.forEach.call(tabs, function (tab, i) {
        tab.addEventListener('click', function () { show(i); });
      });

      list.addEventListener('keydown', function (e) {
        var move = null;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') move = index + 1;
        else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') move = index - 1;
        else if (e.key === 'Home') move = 0;
        else if (e.key === 'End') move = tabs.length - 1;
        if (move === null) return;

        e.preventDefault();
        show(move, 'tab');
      });

      // Delegated so it covers the controls inside all seven panels.
      var card = list.parentNode;
      card.addEventListener('click', function (e) {
        if (e.target.closest('[data-proc-next]')) show(index + 1, 'next');
        else if (e.target.closest('[data-proc-prev]')) show(index - 1, 'prev');
      });

      show(0);
    });
  }

  /* --- 8 — Genre filter --------------------------------------------------- */
  /* The filtering itself is CSS, keyed off data-active on the track, so there
     is no flash of the wrong books on first paint. This only moves the flag
     and tells main.js's scroller to re-check its arrow states. */
  function initGenres() {
    var groups = document.querySelectorAll('[data-genre-tabs]');

    Array.prototype.forEach.call(groups, function (group) {
      var section = group.closest('section');
      var track   = section && section.querySelector('[data-genre-track]');
      var buttons = group.querySelectorAll('[data-genre-filter]');
      if (!track || !buttons.length) return;

      Array.prototype.forEach.call(buttons, function (btn) {
        btn.addEventListener('click', function () {
          var genre = btn.getAttribute('data-genre-filter');

          Array.prototype.forEach.call(buttons, function (other) {
            var on = other === btn;
            other.setAttribute('aria-pressed', String(on));
            other.classList.toggle('is-active', on);
          });

          track.setAttribute('data-active', genre);
          track.scrollLeft = 0;
          // The visible width of the track just changed; main.js syncs its
          // prev/next disabled states on resize.
          window.dispatchEvent(new Event('resize'));
        });
      });
    });
  }

  function boot() {
    initProcess();
    initGenres();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
