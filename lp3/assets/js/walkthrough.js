/* Walkthrough Setup Technology (WST) — step-by-step guided panel (PROTOTYPE, session 56)
 *
 * Non-modal companion panel. Stays open while the user works in the real app,
 * survives hash navigation, and shows one labeled step at a time with the
 * screenshot of what that step looks like.
 *
 * Loaded as a classic script before the bundle, same as tour.js.
 * Self-contained: markup, styles and state all live here.
 *
 * Each step carries `view` and `selector` — not used for targeting yet, but
 * they are the staleness manifest. When a view changes, wtAffected('social')
 * lists every slide shot there so we know what to recapture.
 */
(function () {
  'use strict';

  // ---------------------------------------------------------------- catalog
  // Every setup-able tool in the app, whether or not its steps are written yet.
  // `status: 'ready'` means WALKTHROUGHS has steps for it. Everything else is
  // the build queue — the picker shows it so the roadmap is visible during the
  // prototype. In production, hide anything not ready.

  const CATALOG = [
    { view:'dashboard',        title:'Portal quick start',          group:'Start here',       status:'ready'   },
    { view:'books',            title:'Setting up your book',        group:'Start here',       status:'ready'   },
    { view:'connections',      title:'Connecting your platforms',   group:'Start here',       status:'ready'   },
    { view:'account',          title:'Your account and plan',       group:'Start here',       status:'ready' },

    { view:'social',           title:'Your first social post',      group:'Get the word out', status:'ready'   },
    { view:'email',            title:'Email campaigns',             group:'Get the word out', status:'ready'   },
    { view:'contacts',         title:'Contacts and lists',          group:'Get the word out', status:'ready'   },
    { view:'press',            title:'Press releases',              group:'Get the word out', status:'ready'   },
    { view:'events',           title:'Events and signings',         group:'Get the word out', status:'ready'   },
    { view:'ads',              title:'Running ads',                 group:'Get the word out', status:'ready' },

    { view:'cover-letter',     title:'Cover letters',               group:'Written material', status:'ready'   },
    { view:'sell-sheet',       title:'Sell sheets',                 group:'Written material', status:'ready'   },
    { view:'author-bio',       title:'Author Central bio',          group:'Written material', status:'ready'   },

    { view:'videos',           title:'Graphics and video',          group:'Graphics & video', status:'ready' },
    { view:'gv-cover',         title:'Book cover concepts',         group:'Graphics & video', status:'ready' },
    { view:'gv-social',        title:'Social media graphics',       group:'Graphics & video', status:'ready' },
    { view:'gv-quote',         title:'Quote cards',                 group:'Graphics & video', status:'ready' },
    { view:'gv-event',         title:'Event and signing flyers',    group:'Graphics & video', status:'ready' },
    { view:'gv-trailer',       title:'Book trailer scripts',        group:'Graphics & video', status:'ready' },
    { view:'gv-trailer-video', title:'Book trailer videos',         group:'Graphics & video', status:'ready' },
    { view:'gv-slideshow',     title:'Slideshow videos',            group:'Graphics & video', status:'ready' },

    { view:'kdp',             title:'Amazon KDP tools',            group:'Amazon',           status:'ready' },
    { view:'kdp-keywords',    title:'Keywords and categories',     group:'Amazon',           status:'ready' },
    { view:'kdp-aplus',       title:'A+ content modules',          group:'Amazon',           status:'ready' },
    { view:'kdp-promos',      title:'KDP Select promo planner',    group:'Amazon',           status:'ready' },
    { view:'rank-logger',     title:'Sales rank logger',           group:'Amazon',           status:'ready' },

    { view:'ebook-convert',   title:'Making your eBook',           group:'Publishing',       status:'ready' },  // built, awaiting live verification
    { view:'sales',           title:'Sales channels',              group:'Publishing',       status:'ready' },
    { view:'print-quote',     title:'Book printing quote',         group:'Publishing',       status:'ready' },

    { view:'website',         title:'Your author website',         group:'Website',          status:'ready' },
    { view:'wordpress',       title:'WordPress for authors',       group:'Website',          status:'ready' }

  ];

  // ---------------------------------------------------------------- content

  // Step definitions live in /walkthroughs.json — the same file tools/wt_capture.js
  // reads to shoot the screenshots. One source of truth: a selector fixed for the
  // capture is automatically the selector the panel uses, and they cannot drift.
  let WALKTHROUGHS = {};

  let WT_REV = '';

  function loadWalkthroughs() {
    // The spec itself is fetched no-cache, so a fresh rev always arrives; the
    // images then ride on ?v=<rev> and update the moment they're recaptured.
    return fetch('/walkthroughs.json', { cache: 'no-cache' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        WALKTHROUGHS = (d && d.walkthroughs) || {};
        WT_REV = (d && d.rev) ? String(d.rev) : '';
        window.WALKTHROUGHS = WALKTHROUGHS;
      })
      .catch(function () { WALKTHROUGHS = {}; });
  }

  function shotUrl(img) {
    if (!img) return '';
    return WT_REV ? img + (img.indexOf('?') === -1 ? '?v=' : '&v=') + WT_REV : img;
  }

  // ------------------------------------------------------------------ state

  const KEY = 'wt_state';
  let wt = null;   // { id, step, minimized }

  function save() {
    try {
      wt ? sessionStorage.setItem(KEY, JSON.stringify(wt))
         : sessionStorage.removeItem(KEY);
    } catch (e) { /* private mode — panel still works, just won't survive reload */ }
  }

  function restore() {
    try {
      const raw = sessionStorage.getItem(KEY);
      if (!raw) return;
      const s = JSON.parse(raw);
      if (s && WALKTHROUGHS[s.id]) wt = s;
    } catch (e) { wt = null; }
  }

  function isNarrow() { return window.innerWidth < 620; }

  // The layout was decided once at start and never revisited, so resizing an
  // open panel could never reach the phone layout. Follow the window instead,
  // unless the user has expressed a preference by toggling it themselves.
  function syncCompact() {
    if (!wt || wt.minimized || wt.compactManual) { dockSheet(); return; }
    const want = isNarrow();
    if (wt.compact !== want) { wt.compact = want; save(); render(); }
    else dockSheet();   // rotation changes the sheet's height without changing layout
  }

  let resizeTimer = null;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(syncCompact, 150);
  });

  // Belt and braces: matchMedia fires on the breakpoint itself, and reliably
  // in contexts where a plain resize event doesn't (device emulation, some
  // embedded views). Either path lands in the same place.
  if (window.matchMedia) {
    const mq = window.matchMedia('(max-width: 620px)');
    const onMq = function () { syncCompact(); };
    mq.addEventListener ? mq.addEventListener('change', onMq) : mq.addListener(onMq);
  }

  // ------------------------------------------------------------------ style

  const CSS = `
  /* #chat-fab is 110x110 at bottom:24, so it occupies up to 134px from the
     bottom. Sitting at 96px put the Next button underneath it. 150px clears
     it with a gap. z stays below Sophie's 9000/9001 so chat wins the corner. */
  #wt-panel {
    position: fixed; right: 24px; bottom: 150px; width: 320px; max-width: calc(100vw - 32px);
    /* Tinted and translucent so it reads as a guide layered OVER the app
       rather than part of it, and so you can see what it is covering. The
       tint is on the panel background only — the text stays fully opaque,
       because the one thing that must be readable is the instruction. */
    background: rgba(243, 247, 253, 0.85);
    /* Light blur only. A heavy frost looks smart but defeats the point —
       Bob wants to SEE what the panel is covering, and blur destroys the
       detail underneath no matter how transparent the tint is. */
    -webkit-backdrop-filter: blur(3px) saturate(1.1);
    backdrop-filter: blur(3px) saturate(1.1);
    border: 1px solid #b9cbe4; border-radius: 14px;
    box-shadow: 0 12px 40px rgba(16,24,40,.22);
    /* Below the app's modals (z 1000) but above ordinary page content. At 8900
       the panel sat on top of every dialog — the AI demo preview came up
       partly hidden behind it. Modals are blocking and transient; the panel
       should get out of their way, not compete. Sophie (9000/9001) still wins
       the corner regardless. */
    z-index: 950; font-size: 13px; line-height: 1.45;
    display: flex; flex-direction: column; overflow: hidden;
    /* bottom:150px + a 40px breathing gap at the top. Without subtracting the
       bottom offset the panel could still run off the top of a short window. */
    max-height: calc(100vh - 190px);
  }
  #wt-body { overflow-y: auto; overscroll-behavior: contain; }
  #wt-panel.wt-min { width: auto; }
  #wt-head {
    display: flex; align-items: center; gap: 8px; padding: 10px 12px;
    background: rgba(228,238,250,0.88); border-bottom: 1px solid #e6ebf2; cursor: grab; user-select: none;
  }
  #wt-head.wt-drag { cursor: grabbing; }
  /* Spelled out on purpose. A bare grip glyph is a designer's convention and
     means nothing to most people — Bob flagged that he only worked out the
     panel was draggable after being told. */
  #wt-grip {
    display: inline-flex; align-items: center; gap: 4px; flex: none;
    font-size: 11px; font-weight: 600; color: #6b7a8d;
    padding: 3px 7px; border: 1px solid #d8dee7; border-radius: 5px;
    background: #fff; cursor: grab; user-select: none; white-space: nowrap;
  }
  #wt-head:hover #wt-grip { background: #eef4fb; border-color: #bcd3ea; color: #2b6cb0; }
  #wt-head.wt-drag #wt-grip { cursor: grabbing; background: #e0edfa; }
  #wt-count { font-size: 11.5px; font-weight: 600; color: #5b6b82; letter-spacing: .02em; }
  #wt-title {
    font-size: 11.5px; color: #8695a8; margin-left: auto;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;
  }
  .wt-x {
    border: 0; background: transparent; cursor: pointer; padding: 4px 6px;
    font-size: 20px; line-height: 1; color: #7b8798; border-radius: 6px;
  }
  .wt-x:hover { background: #e9eef5; color: #33404f; }
  #wt-body { padding: 12px; }
  #wt-label { font-size: 14px; font-weight: 650; color: #16202c; margin: 0 0 6px; }
  #wt-detail { font-size: 12.5px; color: #4a5768; margin: 0 0 11px; }
  #wt-warn {
    font-size: 12px; background: #fff6e5; border-left: 3px solid #e8a33d;
    color: #6b4a12; padding: 10px 12px; border-radius: 6px; margin: 0 0 14px;
  }
  /* max-width, never width: a crop narrower than the panel must not be
     upscaled — stretching a 280px capture to 368px just blurs it. */
  /* Full-bleed: the screenshot spans the panel edge to edge rather than sitting
     inside #wt-body's 12px padding, buying back ~24px of width. Still max-width
     only — a crop narrower than the panel is centred at native size, never
     upscaled, because stretching a capture past 1:1 just blurs it. */
  #wt-shot {
    max-width: calc(100% + 24px); border: 1px solid #e3e8ef; border-radius: 6px;
    display: block; background: #f2f5f9; cursor: zoom-in;
    margin: 0 -12px;
  }
  #wt-shot.wt-missing { min-height: 90px; }
  .wt-ctx {
    margin-left: 14px; vertical-align: middle;
    border: 1px solid #bcd3ea; background: #eef5fc; color: #2b6cb0;
    font-size: 15px; font-weight: 600; padding: 7px 14px;
    border-radius: 999px; cursor: pointer;
  }
  .wt-ctx:hover { background: #e0edfa; }
  /* Picker. Centred dialog on desktop, full-height sheet on a phone. */
  #wt-help {
    position: fixed; inset: 0; z-index: 8960; background: rgba(12,18,26,.5);
    display: flex; align-items: center; justify-content: center; padding: 24px;
  }
  #wt-help-card {
    background: #fff; border-radius: 14px; width: 520px; max-width: 100%;
    max-height: 100%; display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 20px 60px rgba(16,24,40,.3);
  }
  #wt-help-head {
    display: flex; align-items: center; gap: 12px; padding: 18px 20px 14px;
  }
  #wt-help-head h2 { margin: 0; font-size: 20px; font-weight: 650; color: #16202c; }
  #wt-help-head .wt-x { margin-left: auto; }
  #wt-help-q {
    margin: 0 20px 14px; padding: 12px 14px; font-size: 16px;
    border: 1px solid #d3dae4; border-radius: 9px; outline: none;
  }
  #wt-help-q:focus { border-color: #2b6cb0; box-shadow: 0 0 0 3px rgba(43,108,176,.15); }
  #wt-help-scroll { overflow-y: auto; padding: 0 20px 8px; }
  .wt-grp { margin-bottom: 16px; }
  .wt-grp-h {
    font-size: 13px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    color: #8695a8; margin: 0 0 7px;
  }
  .wt-pick {
    display: flex; align-items: center; gap: 10px; width: 100%; text-align: left;
    padding: 13px 14px; margin-bottom: 6px; font-size: 16px; color: #22303f;
    background: #f7f9fc; border: 1px solid #e6ebf2; border-radius: 9px; cursor: pointer;
  }
  .wt-pick:hover { background: #eef4fb; border-color: #bcd3ea; }
  .wt-pick.ready .wt-pick-t { font-weight: 600; }
  .wt-pick.planned { opacity: .62; }
  .wt-soon {
    margin-left: auto; font-size: 12px; font-weight: 700; letter-spacing: .05em;
    text-transform: uppercase; color: #8695a8; background: #e9eef5;
    padding: 3px 8px; border-radius: 999px;
  }
  #wt-help-foot { padding: 14px 20px; border-top: 1px solid #eef1f6; }
  #wt-help-foot .wt-btn { width: 100%; }
  @media (max-width: 620px) {
    #wt-help { padding: 0; align-items: stretch; }
    #wt-help-card { width: 100%; border-radius: 0; max-height: none; }
  }
  /* The live target on the real page. Same green as the capture ring so the
     screenshot and the page agree. outline (not border) sits outside the box
     and doesn't reflow the layout; pointer-events stay untouched so the user
     can still click the thing we're pointing at. */
  .wt-live-target {
    outline: 3px solid #16a34a !important;
    outline-offset: 2px !important;
    box-shadow: 0 0 0 6px rgba(22,163,74,.16) !important;
    border-radius: 4px;
    animation: wt-pulse 1.6s ease-out 2;
  }
  /* For targets inside a clipping parent (tab strips, pill groups) the ring is
     drawn inside the box, where nothing can crop it. */
  .wt-live-target-inset {
    outline: 3px solid #16a34a !important;
    outline-offset: -3px !important;
    border-radius: 4px;
  }
  @keyframes wt-pulse {
    0%   { box-shadow: 0 0 0 0   rgba(22,163,74,.45); }
    70%  { box-shadow: 0 0 0 14px rgba(22,163,74,0); }
    100% { box-shadow: 0 0 0 6px rgba(22,163,74,.16); }
  }
  @media (prefers-reduced-motion: reduce) {
    .wt-live-target { animation: none; }
  }
  #wt-panel.wt-left { right: auto; left: 24px; }
  .wt-more {
    border: 0; background: transparent; color: #2b6cb0; cursor: pointer;
    font-size: 12.5px; font-weight: 600; padding: 5px 0 0; text-decoration: underline;
  }
  #wt-foot {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 12px; border-top: 1px solid #eef1f6; background: rgba(236,243,251,0.84);
  }
  .wt-btn {
    font-size: 12.5px; font-weight: 600; padding: 7px 13px; border-radius: 8px;
    border: 1px solid #d3dae4; background: #fff; color: #33404f; cursor: pointer;
  }
  .wt-btn:hover:not(:disabled) { background: #f3f6fa; }
  .wt-btn:disabled { opacity: .4; cursor: default; }
  .wt-btn.wt-primary { background: #2b6cb0; border-color: #2b6cb0; color: #fff; margin-left: auto; }
  .wt-btn.wt-primary:hover { background: #245a94; }
  #wt-track { height: 3px; background: #e6ebf2; }
  #wt-fill { height: 100%; background: #2b6cb0; transition: width .18s ease; }
  #wt-pill {
    position: fixed; right: 24px; bottom: 150px; z-index: 950;
    display: flex; align-items: center; gap: 9px;
    background: #2b6cb0; color: #fff; border: 0; cursor: pointer;
    font-size: 15px; font-weight: 600; padding: 12px 18px; border-radius: 999px;
    box-shadow: 0 8px 24px rgba(43,108,176,.4);
  }
  #wt-lightbox {
    position: fixed; inset: 0; z-index: 8950; background: rgba(12,18,26,.85);
    display: flex; align-items: center; justify-content: center; padding: 40px; cursor: zoom-out;
  }
  #wt-lightbox img { max-width: 100%; max-height: 100%; border-radius: 8px; }
  @media (max-width: 620px) {
    /* A floating box on a phone just covers the thing you're being told to
       click — there is no "beside the target" on a 390px screen. So the panel
       DOCKS to the bottom edge as a sheet: app on top, guide underneath, no
       overlap. body.wt-docked pads #main by the sheet's real height so the
       page can still scroll everything clear of it. */
    body.wt-docked #wt-panel {
      left: 0; right: 0; bottom: 0; top: auto; width: auto;
      max-width: none;            /* the desktop calc(100vw - 32px) left a gap */
      border-radius: 14px 14px 0 0;
      max-height: 46vh;
      border-top: 3px solid #2b6cb0;
      box-shadow: 0 -10px 30px rgba(16,24,40,.30);
    }
    body.wt-docked #wt-body { overflow-y: auto; -webkit-overflow-scrolling: touch; }
    #wt-pill { right: 10px; bottom: 10px; }
    body.wt-docked #wt-head::before {
      content: ''; position: absolute; top: 5px; left: 50%; transform: translateX(-50%);
      width: 34px; height: 3px; border-radius: 2px; background: #c8d2de;
    }
    body.wt-docked #wt-head { position: relative; padding-top: 14px; }
    /* Sophie's launcher sits exactly where the sheet now lives. */
    body.wt-docked #chat-fab { display: none !important; }
  }
  /* Follows the class, not the width: a panel the user has dragged is their
     own placement and must not be re-docked or padded around. */
  body.wt-docked #main { padding-bottom: var(--wt-dock-h, 0px) !important; }
  #wt-head { touch-action: none; }
  body.wt-docked #wt-grip { display: none; }
  /* While it is being dragged, drop it right back so the user can see where
     they are putting it. Restored the instant they let go. */
  #wt-panel.wt-dragging { opacity: .45; transition: opacity .1s linear; }
  #wt-navnote {
    margin: 6px 0 0; font-size: 12px; line-height: 1.45; color: #6b7a8d;
    background: #f4f7fb; border-left: 3px solid #2b6cb0;
    padding: 6px 9px; border-radius: 0 6px 6px 0;
  }`;

  function injectCSS() {
    if (document.getElementById('wt-css')) return;
    const s = document.createElement('style');
    s.id = 'wt-css';
    s.textContent = CSS;
    document.head.appendChild(s);
  }

  // ------------------------------------------------------------------- view

  function el() { return document.getElementById('wt-root'); }

  function root() {
    let r = el();
    if (!r) {
      r = document.createElement('div');
      r.id = 'wt-root';
      document.body.appendChild(r);   // outside the view divs — survives hash routing
    }
    return r;
  }

  function esc(s) {
    return String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  }

  function render() {
    const r = root();

    if (!wt) { r.innerHTML = ''; dockSheet(); return; }

    const pack = WALKTHROUGHS[wt.id];
    const step = pack.steps[wt.step];
    const n = pack.steps.length;
    const last = wt.step === n - 1;

    if (wt.minimized) {
      r.innerHTML = `<button id="wt-pill" type="button">
        <span>Resume walkthrough</span>
        <span style="opacity:.8">${wt.step + 1}/${n}</span>
      </button>`;
      r.querySelector('#wt-pill').onclick = () => { wt.minimized = false; save(); render(); };
      dockSheet();
      return;
    }

    r.innerHTML = `
      <div id="wt-panel" role="complementary" aria-label="Walkthrough">
        <div id="wt-head" title="Drag to move">
          <span id="wt-grip"><span aria-hidden="true">⠿</span> Move</span>
          <span id="wt-count">Step ${wt.step + 1} of ${n}</span>
          <span id="wt-title">${esc(pack.title)}</span>
          <button class="wt-x" id="wt-close" type="button" title="Minimize" aria-label="Minimize">&times;</button>
        </div>
        <div id="wt-track"><div id="wt-fill" style="width:${((wt.step + 1) / n) * 100}%"></div></div>
        <div id="wt-body">
          <p id="wt-label">${esc(step.label)}</p>
          ${mobileNavNote(step)}
          ${wt.compact ? '' : `
            <p id="wt-detail">${esc(step.detail)}</p>
            ${step.warn ? `<p id="wt-warn">${esc(step.warn)}</p>` : ''}
            <img id="wt-shot" src="${esc(shotUrl(step.img))}" alt="${esc(step.label)}">`}
          <button class="wt-more" id="wt-more" type="button">
            ${wt.compact ? 'Show me what it looks like' : 'Hide the details'}
          </button>
        </div>
        <div id="wt-foot">
          <button class="wt-btn" id="wt-prev" type="button" ${wt.step === 0 ? 'disabled' : ''}>Back</button>
          <button class="wt-btn wt-primary" id="wt-next" type="button">${last ? 'Done' : 'Next'}</button>
        </div>
      </div>`;

    r.querySelector('#wt-close').onclick = () => { clearLive(); wt.minimized = true; save(); render(); };
    r.querySelector('#wt-more').onclick = () => {
      wt.compact = !wt.compact;
      wt.compactManual = true;
      save();
      render();
    };
    r.querySelector('#wt-prev').onclick = () => { if (wt.step > 0) { wt.step--; save(); render(); } };
    r.querySelector('#wt-next').onclick = () => {
      if (last) { finish(); } else { wt.step++; save(); render(); }
    };

    const shot = r.querySelector('#wt-shot');
    if (shot) {
    shot.onerror = () => {
      shot.classList.add('wt-missing');
      shot.replaceWith(Object.assign(document.createElement('div'), {
        id: 'wt-shot',
        className: 'wt-missing',
        style: 'padding:14px;border:1px dashed #ccd4e0;border-radius:8px;color:#8695a8;font-size:14px;text-align:center',
        textContent: 'Screenshot not captured yet'
      }));
    };
    shot.onclick = () => lightbox(shotUrl(step.img), step.label);
    }

    dragify(r.querySelector('#wt-panel'), r.querySelector('#wt-head'));
    restorePos(r.querySelector('#wt-panel'));   // before dockSheet: docking clears it
    dockSheet();
    setTimeout(syncLive, 0);   // after layout, so measurements are real
  }

  // The step copy says "in the sidebar" because that is what it is on a
  // desktop. On a phone there is no sidebar to point at, so say where it went
  // rather than rewording 31 steps that are correct everywhere else.
  function mobileNavNote(step) {
    if (!isNarrow()) return '';
    let el = null;
    try { el = document.querySelector(step.liveSelector || step.selector || ''); } catch (e) {}
    if (!inSidebar(el)) return '';
    return '<p id="wt-navnote">On a phone the sidebar lives behind the ' +
           '<strong>\u2630</strong> menu \u2014 opened for you.</p>';
  }

  // Re-apply the user's own placement after a re-render. Clamped, because the
  // window may have been resized or rotated since — an off-screen panel would
  // be unrecoverable.
  function restorePos(panel) {
    if (!panel || !wt || !wt.moved || !wt.pos || isNarrow()) return;
    const w = panel.offsetWidth, h = panel.offsetHeight;
    const x = Math.min(Math.max(0, wt.pos.left), Math.max(0, window.innerWidth  - w));
    const y = Math.min(Math.max(0, wt.pos.top),  Math.max(0, window.innerHeight - h));
    panel.style.right = 'auto'; panel.style.bottom = 'auto';
    panel.style.left = x + 'px'; panel.style.top = y + 'px';
  }

  function lightbox(src, alt) {
    const b = document.createElement('div');
    b.id = 'wt-lightbox';
    b.innerHTML = `<img src="${esc(src)}" alt="${esc(alt)}">`;
    b.onclick = () => b.remove();
    document.body.appendChild(b);
  }

  // Drag by the header. Switches to top/left so it detaches from the corner.
  // Drag state lives at module scope and the window listeners are attached
  // ONCE. They used to be attached inside dragify(), which render() calls on
  // every step change — so each render leaked another mousemove handler bound
  // to a stale, detached panel. After a few steps every mouse movement ran a
  // pile of dead handlers and the page ground to a halt.
  const drag = { live: false, panel: null, handle: null, sx: 0, sy: 0, ox: 0, oy: 0 };

  // Pointer events, not mouse events. A touchscreen fires neither mousedown nor
  // mousemove, so the panel was undraggable on every tablet — the Move handle
  // was there, it just did nothing. Pointer events cover mouse, touch and pen
  // in one path. Still attached ONCE at module scope: attaching inside
  // dragify() (which render() calls per step) leaked a handler per render and
  // froze the page.
  window.addEventListener('pointermove', function (e) {
    if (!drag.live || !drag.panel) return;
    const w = drag.panel.offsetWidth, h = drag.panel.offsetHeight;
    const x = Math.min(Math.max(0, drag.ox + e.clientX - drag.sx), window.innerWidth - w);
    const y = Math.min(Math.max(0, drag.oy + e.clientY - drag.sy), window.innerHeight - h);
    drag.panel.style.left = x + 'px';
    drag.panel.style.top  = y + 'px';
  });

  function endDrag() {
    if (!drag.live) return;
    drag.live = false;
    if (drag.handle) drag.handle.classList.remove('wt-drag');
    if (drag.panel) drag.panel.classList.remove('wt-dragging');
    // render() rebuilds the panel every step, so the inline left/top died with
    // the old element and it snapped back to the CSS corner on each Next.
    // wt.moved recorded THAT it was moved but never WHERE.
    if (drag.panel && wt) {
      const b = drag.panel.getBoundingClientRect();
      wt.pos = { left: Math.round(b.left), top: Math.round(b.top) };
      save();
    }
  }
  window.addEventListener('pointerup', endDrag);
  window.addEventListener('pointercancel', endDrag);

  // Per-render: only wire the handle's own pointerdown. The element is new each
  // time, so this listener dies with it — nothing accumulates.
  function dragify(panel, handle) {
    if (!panel || !handle) return;
    handle.addEventListener('pointerdown', function (e) {
      if (e.target.closest('button')) return;
      // A docked sheet spans the screen; there is nowhere to drag it to, and a
      // stray touch-drag would fight the page. Phones stay put by design.
      if (document.body.classList.contains('wt-docked')) return;
      const box = panel.getBoundingClientRect();
      drag.live = true;
      drag.panel = panel;
      drag.handle = handle;
      drag.sx = e.clientX; drag.sy = e.clientY;
      drag.ox = box.left;  drag.oy = box.top;
      if (wt) { wt.moved = true; save(); }
      panel.classList.add('wt-dragging');
      panel.classList.remove('wt-left');
      panel.style.right = 'auto'; panel.style.bottom = 'auto';
      panel.style.left = box.left + 'px'; panel.style.top = box.top + 'px';
      handle.classList.add('wt-drag');
      e.preventDefault();
    });
  }

  // ------------------------------------------------- live page highlighting
  // Ring the real control on the real page, not just in the screenshot, so
  // nobody has to translate a picture into a location on their own screen.

  let liveEl = null;      // primary target (drives panel avoidance)
  let liveAll = [];       // every element ringed for this step
  // Applying the highlight mutates a class, and the view watcher observes class
  // changes — without this guard each highlight retriggers the watcher, which
  // reapplies the highlight, and the renderer locks up. Found the hard way.
  let suppressWatch = false;

  function clearLive() {
    if (!liveAll.length && !liveEl) return;
    suppressWatch = true;
    liveAll.forEach(function (e) {
      e.classList.remove('wt-live-target');
      e.classList.remove('wt-live-target-inset');
    });
    if (liveEl) {
      liveEl.classList.remove('wt-live-target');
      liveEl.classList.remove('wt-live-target-inset');
    }
    // Sweep any overflow markers left behind by the earlier un-clip approach,
    // which could strand the app's scroll container in overflow:visible.
    [].slice.call(document.querySelectorAll('[data-wt-prev-ovf]')).forEach(function (p) {
      p.style.overflow = p.getAttribute('data-wt-prev-ovf');
      p.removeAttribute('data-wt-prev-ovf');
    });
    liveAll = [];
    liveEl = null;
    suppressWatch = false;
  }

  function currentStep() {
    if (!wt) return null;
    const pack = WALKTHROUGHS[wt.id];
    return pack ? pack.steps[wt.step] : null;
  }

  function applyLive() {
    clearLive();
    if (!wt || wt.minimized) return null;
    const step = currentStep();
    if (!step || !step.selector || step.liveHighlight === false) return null;

    // A step can ring several things at once — `liveSelector` may match many,
    // which is how "fill in everything marked *" lights up all eleven required
    // fields together instead of pointing at one and leaving ten unexplained.
    const sel = step.liveSelector || step.selector;
    let matches;
    try { matches = [].slice.call(document.querySelectorAll(sel)); } catch (e) { return null; }

    // On a phone #sidebar is width:0/overflow:hidden until .open, so a sidebar
    // link measures 0x0 and the filter below drops it — no ring at all, on the
    // first step of all 31 walkthroughs. Open the drawer first so the target is
    // real. suppressWatch because the class change would otherwise wake the
    // view observer and re-enter render().
    if (isNarrow() && matches.length && inSidebar(matches[0])) {
      suppressWatch = true;
      openDrawer();
      suppressWatch = false;
    }

    // Only highlight what's actually on screen — every view exists in the DOM,
    // so a visibility check is what stops us ringing controls on another page.
    const visible = matches.filter(function (m) {
      const r = m.getBoundingClientRect();
      return r.width > 0 && r.height > 0;
    });
    if (!visible.length) return null;

    suppressWatch = true;
    visible.forEach(function (m) {
      // Ring inside the element when a parent clips (tab strips, pill groups).
      // The previous version un-clipped the ancestors, which also released the
      // app's scroll container and stopped the page scrolling — never mutate
      // anything but the target itself.
      let clipped = false, p = m.parentElement, depth = 0;
      while (p && depth < 4) {
        if (getComputedStyle(p).overflow !== 'visible') { clipped = true; break; }
        p = p.parentElement; depth++;
      }
      m.classList.add(clipped ? 'wt-live-target-inset' : 'wt-live-target');
    });
    suppressWatch = false;
    liveAll = visible;

    // Panel avoidance and scrolling follow the step's own crop target when it
    // is among the matches, otherwise the first one.
    let el = null;
    try { el = document.querySelector(step.selector); } catch (e) {}
    if (!el || visible.indexOf(el) === -1) el = visible[0];
    liveEl = el;
    const b = el.getBoundingClientRect();

    // Bring it into view if it's off-screen, but never yank the page around
    // when it's already visible. The scroll is async, so re-run the avoidance
    // once it lands — measuring straight away gives the OLD position, which is
    // how the panel ended up sitting on top of a target it had just scrolled
    // to (the New campaign button, 2000px down the email page).
    // 'instant', not 'smooth'. The app scrolls #main rather than the window,
    // and a smooth scroll on that container silently ends back at 0 — the
    // target stayed off-screen while the panel sat on top of where it landed.
    // Instant also means the rect below is correct immediately, so the panel
    // avoidance has something real to measure.
    // The usable viewport stops where the docked sheet starts.
    const floor = window.innerHeight - dockHeight();
    if (b.top < 0 || b.bottom > floor) {
      el.scrollIntoView({ block: 'center', behavior: 'instant' });
      // scrollIntoView centres on the FULL viewport, which on a phone lands the
      // target behind the sheet. Nudge it up into the visible half.
      const after = el.getBoundingClientRect();
      const over = after.bottom - (floor - 12);
      if (over > 0) {
        const sc = document.getElementById('main') || document.scrollingElement;
        if (sc) sc.scrollTop += over;
      }
    }
    return el;
  }

  // On a phone the sidebar is a drawer: #sidebar is width:0/overflow:hidden
  // until .open. Every walkthrough's first step points into it, so without
  // this the ring draws on a zero-width box — invisible — and the step reads
  // "click X in the sidebar" next to a screen that has no sidebar. Open it for
  // them; the app closes it again on navigate().
  function inSidebar(el) { return !!(el && el.closest && el.closest('#sidebar')); }

  function openDrawer() {
    const sb = document.getElementById('sidebar');
    if (!sb || sb.classList.contains('open')) return false;
    // Set the class directly rather than calling the app's toggleSidebar():
    // a toggle would CLOSE it if anything else opened it first, and this runs
    // on every re-render.
    sb.classList.add('open');
    const bd = document.getElementById('sidebar-backdrop');
    if (bd) bd.classList.add('visible');
    return true;
  }

  // On phones the panel is a docked bottom sheet. Reserve its height at the
  // foot of the app's scroll container so no page content is trapped
  // underneath it — without this the last card on a view is unreachable.
  function dockSheet() {
    const panel = document.getElementById('wt-panel');
    // NOT gated on wt.moved. A dragged position is meaningful on a desktop,
    // where the panel is a 320px box with room beside it; on a phone the sheet
    // spans the screen and there is nowhere else for it to be. wt.moved is
    // persisted, so honouring it here meant one desktop drag left every later
    // phone session un-docked — panel still bottom-aligned by CSS, but no
    // class, so Sophie stayed on top of the Next button and #main lost its
    // padding. Appearance and state must come from the same switch.
    const on = !!(panel && wt && !wt.minimized && isNarrow());
    if (on && panel.style.left) { panel.style.left = ''; panel.style.top = ''; }
    document.body.classList.toggle('wt-docked', on);
    document.documentElement.style.setProperty(
      '--wt-dock-h', on ? panel.offsetHeight + 'px' : '0px');
  }

  function dockHeight() {
    if (!document.body.classList.contains('wt-docked')) return 0;
    const p = document.getElementById('wt-panel');
    return p ? p.offsetHeight : 0;
  }

  // Keep the panel off whatever we just highlighted. Skipped once the user has
  // dragged it themselves — their placement beats our guess.
  function avoidTarget(el) {
    const panel = document.getElementById('wt-panel');
    if (!panel || !wt || wt.moved) return;
    // Docked = full width; there is no left/right to flip to.
    if (document.body.classList.contains('wt-docked')) { panel.classList.remove('wt-left'); return; }
    suppressWatch = true;
    panel.classList.remove('wt-left');
    if (!el) { suppressWatch = false; return; }

    const t = el.getBoundingClientRect();
    const p = panel.getBoundingClientRect();
    // Treat a near miss as a hit. A target sitting a few pixels from the panel
    // edge still reads as "covered" to the person looking at it, and different
    // zoom levels shift things enough that strict overlap is too literal.
    const M = 28;
    const overlaps = !(t.right < p.left - M || t.left > p.right + M ||
                       t.bottom < p.top - M || t.top > p.bottom + M);
    if (!overlaps) { suppressWatch = false; return; }

    // Flip to whichever side has more clear room beside the target.
    const roomLeft  = t.left;
    const roomRight = window.innerWidth - t.right;
    if (roomLeft > roomRight && roomLeft > p.width + 40) panel.classList.add('wt-left');
    suppressWatch = false;
  }

  function syncLive() {
    const el = applyLive();
    avoidTarget(el);
  }

  // ------------------------------------------------------------------- API

  function startWalkthrough(id) {
    if (!WALKTHROUGHS[id]) { console.warn('[wt] no walkthrough:', id); return; }
    injectCSS();
    // Phones start compact: the label alone is what you act on, and the
    // screenshot would otherwise cover half the screen you're trying to use.
    // compactManual records a deliberate toggle so a later resize doesn't
    // undo the user's own choice.
    wt = { id: id, step: 0, minimized: false, compact: isNarrow(), compactManual: false };
    save();
    render();
  }

  function finish() {
    const lesson = WALKTHROUGHS[wt.id].lesson;
    clearLive();
    wt = null; save(); render();
    // Reuse the lesson completion the education system already tracks.
    try {
      const done = JSON.parse(localStorage.getItem('edu_completed') || '[]');
      if (lesson && !done.includes(lesson)) {
        done.push(lesson);
        localStorage.setItem('edu_completed', JSON.stringify(done));
      }
    } catch (e) { /* non-fatal */ }
  }

  function stopWalkthrough() { clearLive(); wt = null; save(); render(); }

  // Staleness manifest: which slides were shot in a given view.
  function wtAffected(view) {
    const out = [];
    Object.keys(WALKTHROUGHS).forEach(id => {
      WALKTHROUGHS[id].steps.forEach((s, i) => {
        if (s.view === view) out.push({ walkthrough: id, step: i + 1, label: s.label, img: s.img, selector: s.selector });
      });
    });
    return out;
  }

  window.startWalkthrough = startWalkthrough;
  window.openSetupHelp = openSetupHelp;
  window.WT_CATALOG = CATALOG;
  window.stopWalkthrough = stopWalkthrough;
  window.wtAffected = wtAffected;
  window.WALKTHROUGHS = WALKTHROUGHS;

  // ------------------------------------------------------- lesson hook-in
  // Wrap openLesson() so a lesson with a matching walkthrough grows a
  // "Show me, step by step" button. Keeps the prototype out of the bundle.

  function addLessonButton(lessonId) {
    const pack = Object.keys(WALKTHROUGHS).find(k => WALKTHROUGHS[k].lesson === lessonId);
    const host = document.getElementById('lesson-body');
    if (!pack || !host || document.getElementById('wt-launch')) return;

    const b = document.createElement('button');
    b.id = 'wt-launch';
    b.type = 'button';
    b.textContent = 'Show me, step by step';
    b.style.cssText = 'display:block;width:100%;margin:0 0 20px;padding:14px 18px;font-size:17px;' +
      'font-weight:650;color:#fff;background:#2b6cb0;border:0;border-radius:10px;cursor:pointer';
    b.onclick = () => startWalkthrough(pack);
    host.insertBefore(b, host.firstChild);
  }

  // ------------------------------------------------- contextual entry point
  // The offer belongs on the screen where someone is stuck, not only in Learn.
  // Any view named as an entryView grows a "Show me how" link in its header,
  // so adding a walkthrough automatically adds its own way in.

  function wtForView(view) {
    return Object.keys(WALKTHROUGHS).find(k => WALKTHROUGHS[k].entryView === view) || null;
  }

  function activeView() {
    const v = document.querySelector('.view.active') ||
              [...document.querySelectorAll('.view')].find(e => e.offsetParent !== null);
    return v ? v.id.replace(/^view-/, '') : null;
  }

  // Every page gets the same button. The user never has to work out that help
  // exists or that Sophie is the way to reach it — they press one thing.
  function decorateView() {
    const view = activeView();
    if (!view) return;
    const host = document.getElementById('view-' + view);
    if (!host || host.querySelector('.wt-ctx')) return;

    const b = document.createElement('button');
    b.className = 'wt-ctx';
    b.type = 'button';
    b.textContent = 'Need help with setup?';
    b.onclick = () => openSetupHelp(view);

    // campaign-detail, send and composer have no .page-header — they are
    // drill-downs, not top-level pages. Appending to the header only meant
    // those three views silently had no way in.
    const header = host.querySelector('.page-header');
    if (header) { header.appendChild(b); return; }
    b.classList.add('wt-ctx-solo');
    host.insertBefore(b, host.firstChild);
  }

  // -------------------------------------------------------------- the picker
  // Opens with this page's walkthrough first, because that is the likeliest
  // intent — but never assumes it. The whole catalog is right underneath, so
  // someone on the Dashboard, or on the wrong page, is one tap from the rest.

  function openSetupHelp(currentView) {
    injectCSS();
    const here = CATALOG.find(c => c.view === currentView);
    const rest = CATALOG.filter(c => c !== here);

    const groups = [];
    rest.forEach(c => {
      let g = groups.find(x => x.name === c.group);
      if (!g) { g = { name: c.group, items: [] }; groups.push(g); }
      g.items.push(c);
    });

    const row = c => `
      <button class="wt-pick ${c.status}" data-view="${esc(c.view)}" type="button">
        <span class="wt-pick-t">${esc(c.title)}</span>
        ${c.status === 'ready' ? '' : '<span class="wt-soon">soon</span>'}
      </button>`;

    const sheet = document.createElement('div');
    sheet.id = 'wt-help';
    sheet.innerHTML = `
      <div id="wt-help-card" role="dialog" aria-label="Setup help">
        <div id="wt-help-head">
          <h2>What would you like help setting up?</h2>
          <button class="wt-x" id="wt-help-x" type="button" aria-label="Close">&times;</button>
        </div>
        <input id="wt-help-q" type="search" placeholder="Search, or describe what you're trying to do…" autocomplete="off">
        <div id="wt-help-scroll">
          ${here ? `<div class="wt-grp"><div class="wt-grp-h">On this page</div>${row(here)}</div>` : ''}
          ${groups.map(g => `
            <div class="wt-grp">
              <div class="wt-grp-h">${esc(g.name)}</div>
              ${g.items.map(row).join('')}
            </div>`).join('')}
        </div>
        <div id="wt-help-foot">
          <button class="wt-btn" id="wt-help-sophie" type="button">Ask Sophie instead</button>
        </div>
      </div>`;

    document.body.appendChild(sheet);

    const close = () => sheet.remove();
    sheet.onclick = e => { if (e.target === sheet) close(); };
    sheet.querySelector('#wt-help-x').onclick = close;

    // Free text is the escape hatch for "I can't find my thing in this list".
    sheet.querySelector('#wt-help-sophie').onclick = () => {
      const q = sheet.querySelector('#wt-help-q').value.trim();
      close();
      askSophie(q);
    };

    sheet.querySelectorAll('.wt-pick').forEach(btn => {
      btn.onclick = () => {
        const v = btn.dataset.view;
        const pack = wtForView(v);
        close();
        if (pack) startWalkthrough(pack);
        else askSophie('Help me set up ' + (CATALOG.find(c => c.view === v) || {}).title);
      };
    });

    const q = sheet.querySelector('#wt-help-q');
    q.oninput = () => {
      const term = q.value.trim().toLowerCase();
      sheet.querySelectorAll('.wt-pick').forEach(b => {
        b.style.display = !term || b.textContent.toLowerCase().includes(term) ? '' : 'none';
      });
      sheet.querySelectorAll('.wt-grp').forEach(g => {
        const any = [...g.querySelectorAll('.wt-pick')].some(b => b.style.display !== 'none');
        g.style.display = any ? '' : 'none';
      });
    };
    if (window.innerWidth >= 620) q.focus();
  }

  // Hand off to the chat. Falls back to a message if the bundle renames things.
  function askSophie(text) {
    // toggleChat() is a toggle — calling it on an already-open chat would
    // close it. Only fire when the panel is actually hidden.
    const panel = document.getElementById('chat-panel');
    const isOpen = panel && panel.offsetParent !== null;
    if (!isOpen) {
      if (typeof window.toggleChat === 'function') window.toggleChat();
      else { const fab = document.getElementById('chat-fab'); if (fab) fab.click(); }
    }

    const input = document.getElementById('chat-input');
    if (input && text) {
      input.value = text;
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }
  }

  // When the user actually does the thing a step told them to do — navigating
  // to the view the NEXT step is about — move with them. Deliberately narrow:
  // only fires when the next step lives in a different view, and only when the
  // user lands on exactly that view. It never fires inside a view, so it can't
  // race ahead of someone still working through steps on one screen.
  function maybeAdvance() {
    if (!wt || wt.minimized) return;
    const pack = WALKTHROUGHS[wt.id];
    if (!pack) return;
    const cur  = pack.steps[wt.step];
    const next = pack.steps[wt.step + 1];
    if (!cur || !next) return;
    if (next.view === cur.view) return;
    if (activeView() !== next.view) return;
    wt.step++;
    save();
    render();
  }

  function onViewChanged() {
    if (suppressWatch) return;
    decorateView();
    maybeAdvance();
    if (wt && !wt.minimized) setTimeout(syncLive, 120);
  }

  function watchViews() {
    window.addEventListener('hashchange', () => setTimeout(onViewChanged, 0));
    // Watch only the view containers, not every element on the page. Narrower
    // scope means far fewer callbacks and no chance of our own highlight or
    // panel-side class feeding back in.
    const obs = new MutationObserver(() => onViewChanged());
    document.querySelectorAll('.view').forEach(function (v) {
      obs.observe(v, { attributes: true, attributeFilter: ['class', 'style'] });
    });
    onViewChanged();
  }

  function hookLessons() {
    const orig = window.openLesson;
    if (typeof orig !== 'function') return;
    window.openLesson = function (lessonId) {
      const out = orig.apply(this, arguments);
      setTimeout(() => addLessonButton(lessonId), 0);  // after the body renders
      return out;
    };
  }

  // Bring the panel back after a reload mid-walkthrough.
  // ?wt=<id> force-starts one — testing hatch, works without logging in.
  // ── Prototype gate ────────────────────────────────────────────────────
  // Off for everyone unless explicitly switched on. ?wt=on sets a localStorage
  // flag so it survives navigation (the app is hash-routed, and Bob needs to
  // move around the real app with the panel following him); ?wt=off clears it.
  // Without the flag this file injects nothing at all — no header button, no
  // lesson button, no panel. Delete this block when the feature ships for real.
  const WT_FLAG = 'wt_enabled';

  function gateOpen() {
    // SHIPPED 2026-07-19: on by default for everyone. ?wt=off remains an
    // opt-out (persisted per browser) — the capture tool relies on it so the
    // panel never appears in its own screenshots. ?wt=on undoes an opt-out.
    const q = new URLSearchParams(location.search).get('wt');
    try {
      if (q === 'off') { localStorage.setItem(WT_FLAG, '0'); return false; }
      if (q !== null)  { localStorage.setItem(WT_FLAG, '1'); return true; }
      return localStorage.getItem(WT_FLAG) !== '0';
    } catch (e) {
      return q !== 'off';
    }
  }


  function boot() {
    if (!gateOpen()) return;
    injectCSS();
    hookLessons();
    watchViews();
    // restore() and the ?wt= hatch both need the step definitions, so they
    // wait on the fetch. The header button does not — it opens the picker,
    // which is driven by CATALOG and works even if the JSON never arrives.
    loadWalkthroughs().then(function () {
      restore();
      const forced = new URLSearchParams(location.search).get('wt');
      if (forced === 'help') { openSetupHelp(activeView()); return; }
      if (forced && WALKTHROUGHS[forced]) { startWalkthrough(forced); return; }
      if (wt) render();
    });
  }
  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', boot)
    : boot();
})();
