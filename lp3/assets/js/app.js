// app.v82.js — extracted from index.html (session 55).
// Loaded with <script defer> from index.html. Versioned filename:
// bump v-number on every deploy that changes this file, and update
// the <script src> in index.html to match — the old version stays
// cached forever, the new name forces a fresh download.

// ── Website (WordPress) view module ──
const WP = (() => {
  const $ = (id) => document.getElementById(id);
  const API = '/api/wordpress.php';

  function show(id) { const el = $(id); if (el) el.style.display = ''; }
  function hide(id) { const el = $(id); if (el) el.style.display = 'none'; }

  function setMsg(boxId, text, kind) {
    const box = $(boxId);
    if (!box) return;
    box.textContent = text;
    // Simple inline styling so we don't depend on CSS classes we
    // haven't added yet. Matches the feel of other inline styles
    // used in the Website view.
    const colors = {
      info:    { bg: '#eef4ff', fg: '#1e40af' },
      success: { bg: '#e7f7ec', fg: '#1a7f37' },
      error:   { bg: '#fdeaea', fg: '#b42318' },
    };
    const c = colors[kind] || colors.info;
    box.style.background = c.bg;
    box.style.color      = c.fg;
    box.style.display    = text ? 'block' : 'none';
  }

  // UTC -> browser-local display. Session 3+ convention: UTC in DB,
  // convert on display.
  function fmtLocal(utcStr) {
    if (!utcStr) return '—';
    const d = new Date(utcStr.replace(' ', 'T') + 'Z');
    if (isNaN(d)) return utcStr;
    return d.toLocaleString();
  }

  function setNavDot(on) {
    const dot = $('nav-website-status');
    if (dot) dot.className = 'platform-status ' + (on ? 'on' : 'off');
  }

  async function api(action, body) {
    const headers = body ? {'Content-Type': 'application/json'} : {};
    const token = localStorage.getItem('auth_token');
    if (token) headers['X-Auth-Token'] = token;
    const opts = {
      method: body ? 'POST' : 'GET',
      headers: headers,
      credentials: 'same-origin',
    };
    if (body) opts.body = JSON.stringify(body);
    const res = await fetch(API + '?action=' + action, opts);
    const text = await res.text();
    let envelope;
    try { envelope = JSON.parse(text); }
    catch (e) {
      console.error('WP API non-JSON response:', res.status, text.slice(0, 500));
      throw new Error('Server returned non-JSON (HTTP ' + res.status + '). Check console for details.');
    }
    // Codebase response shape: jsonResponse(success, message, extras) returns
    //   { success, message, ...extras }  (extras flattened to top level).
    // Failure responses use { success:false, message:'...' } and may include
    // a 'hint' key alongside.
    const ok  = (typeof envelope.success === 'boolean') ? envelope.success : res.ok;
    const msg = envelope.message || envelope.error || 'Request failed';
    if (!ok || !res.ok) {
      const err = new Error(msg);
      err.hint = envelope.hint || null;
      throw err;
    }
    return envelope;
  }

  function readForm() {
    return {
      site_url:     $('wp-input-url').value.trim(),
      username:     $('wp-input-user').value.trim(),
      app_password: $('wp-input-pass').value, // preserve spaces
    };
  }

  async function refresh() {
    show('wp-loading'); hide('wp-not-connected'); hide('wp-connected');
    try {
      const data = await api('get_connection');
      hide('wp-loading');
      if (data.connected) {
        $('wp-site-name').textContent = data.site_name || '(unnamed site)';
        const a = $('wp-site-url');
        a.textContent = data.site_url;
        a.href        = data.site_url;
        $('wp-username').textContent      = data.username;
        $('wp-version').textContent       = data.wp_version || 'unknown';
        $('wp-last-verified').textContent = fmtLocal(data.last_verified_at);
        show('wp-connected');
        setNavDot(true);
        loadHistory();
        loadCategories();
      } else {
        show('wp-not-connected');
        setNavDot(false);
      }
    } catch (err) {
      hide('wp-loading');
      show('wp-not-connected');
      setNavDot(false);
      setMsg('wp-connect-msg', 'Could not load connection info: ' + err.message, 'error');
    }
  }

  async function onTest() {
    setMsg('wp-connect-msg', 'Testing…', 'info');
    try {
      const data = await api('test_connection', readForm());
      setMsg('wp-connect-msg',
        '✓ Connected to "' + data.site_name + '". Click Save to finish.',
        'success');
    } catch (err) {
      setMsg('wp-connect-msg',
        err.message + (err.hint ? ' — ' + err.hint : ''),
        'error');
    }
  }

  async function onSave() {
    setMsg('wp-connect-msg', 'Saving…', 'info');
    try {
      await api('save_connection', readForm());
      await refresh();
    } catch (err) {
      setMsg('wp-connect-msg',
        err.message + (err.hint ? ' — ' + err.hint : ''),
        'error');
    }
  }

  // One-click connect: ask the backend to build the WordPress "authorize
  // application" URL, then send the browser there. The author approves on their
  // own site and WP redirects back to /?wpconnect=… (handled in init()).
  async function onConnectOneClick() {
    const el = $('wp-oneclick-url');
    const url = el ? el.value.trim() : '';
    if (!url) { setMsg('wp-oneclick-msg', 'Please enter your website address first.', 'error'); return; }
    setMsg('wp-oneclick-msg', 'Opening your site to connect…', 'info');
    try {
      const data = await api('connect_start', { site_url: url });
      if (data && data.authorize_url) {
        window.location.href = data.authorize_url;
      } else {
        setMsg('wp-oneclick-msg', 'Could not start the connection — try the by-hand option below.', 'error');
      }
    } catch (err) {
      setMsg('wp-oneclick-msg', err.message + (err.hint ? ' — ' + err.hint : ''), 'error');
    }
  }

  // When WordPress sends the author back from the authorize screen, the URL
  // carries ?wpconnect=<status>. Surface it on the Website view and refresh.
  function handleConnectReturn() {
    const wpc = new URLSearchParams(window.location.search).get('wpconnect');
    if (!wpc) return;
    const navEl = document.querySelector('.nav-item[data-view="website"]');
    if (navEl) navEl.click();
    refresh().then(() => {
      if (wpc === 'connected') {
        setMsg('wp-connected-msg', '✓ Your website is connected. You can now publish books to it from the book editor.', 'success');
      } else {
        const m = {
          rejected: 'Connection cancelled — it wasn’t approved on your site. You can try again any time.',
          expired:  'That connection link expired. Please click “Connect my website” again.',
          mismatch: 'The site you approved didn’t match the address you entered. Please try again.',
          failed:   'We reached your site but couldn’t verify the connection. Try again, or use the by-hand option below.',
          error:    'Something went wrong connecting. Please try again.'
        }[wpc] || 'Something went wrong connecting. Please try again.';
        setMsg('wp-oneclick-msg', m, 'error');
      }
    });
    if (window.history && window.history.replaceState) {
      const u = new URL(window.location.href);
      u.searchParams.delete('wpconnect');
      window.history.replaceState({}, document.title, u.pathname + u.search + u.hash);
    }
  }

  // Exposed so onLogin() can call it AFTER the app routes to its default view —
  // otherwise the default navigate('dashboard') overrides our landing.
  window.__wpHandleConnectReturn = handleConnectReturn;

  async function onReverify() {
    setMsg('wp-connected-msg', 'Re-verifying…', 'info');
    try {
      await refresh();
      setMsg('wp-connected-msg', '✓ Connection info refreshed.', 'success');
    } catch (err) {
      setMsg('wp-connected-msg', err.message, 'error');
    }
  }

  async function onDisconnect() {
    if (!confirm('Disconnect this WordPress site? Your posts will stay on your site — this only removes the connection from Elite Publishing.')) return;
    try {
      await api('disconnect', {});
      await refresh();
    } catch (err) {
      setMsg('wp-connected-msg', err.message, 'error');
    }
  }

  // ───── Composer (Session 7B chunk 2) ─────────────────────────
  // State for the composer's structured fields. Body, title, status are
  // read straight from DOM elements; everything else lives here so we can
  // reset cleanly and serialize on publish.
  const composerState = {
    featured: null,        // { media_id, source_url, filename } | null
    categoryIds: new Set(),
    // tags are read from the comma-separated input on publish — no state needed
  };

  function renderPreview() {
    const ta = $('wp-compose-md');
    const pv = $('wp-compose-preview');
    if (!ta || !pv) return;
    const md = ta.value;
    if (!md.trim()) {
      pv.innerHTML = '<em style="color:var(--ink-soft)">Preview appears here as you type.</em>';
      return;
    }
    if (typeof marked !== 'undefined') {
      pv.innerHTML = marked.parse(md, { breaks: true, gfm: true });
    } else {
      const esc = md.replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
      pv.innerHTML = '<pre style="white-space:pre-wrap">' + esc + '</pre>';
    }
  }

  function clearComposer() {
    clearComposerFields();
    setMsg('wp-compose-msg', '', 'info');
  }

  // Like clearComposer but doesn't wipe the success message — used after a
  // successful publish so the "View on your site" link stays visible.
  function clearComposerFields() {
    $('wp-compose-title').value = '';
    $('wp-compose-md').value = '';
    $('wp-compose-status').value = 'publish';
    $('wp-compose-tags').value = '';
    composerState.categoryIds.clear();
    document.querySelectorAll('input[name="wp-cat"]').forEach(cb => cb.checked = false);
    clearFeaturedImage();
    renderPreview();
  }

  function parseTags(raw) {
    return String(raw || '')
      .split(',')
      .map(s => s.trim())
      .filter(Boolean);
  }

  async function onPublish() {
    const title  = $('wp-compose-title').value.trim();
    const md     = $('wp-compose-md').value;
    const status = $('wp-compose-status').value;
    const tags   = parseTags($('wp-compose-tags').value);
    const categories = Array.from(composerState.categoryIds);
    const featured_media = composerState.featured ? composerState.featured.media_id : null;

    if (!title) {
      setMsg('wp-compose-msg', 'Please enter a title.', 'error');
      $('wp-compose-title').focus();
      return;
    }
    if (!md.trim()) {
      setMsg('wp-compose-msg', 'Please write something in the body.', 'error');
      $('wp-compose-md').focus();
      return;
    }

    const btn = $('wp-compose-publish-btn');
    btn.disabled = true;
    const originalLabel = btn.textContent;
    btn.textContent = 'Publishing…';
    setMsg('wp-compose-msg', 'Sending to WordPress…', 'info');

    try {
      const payload = { title, markdown: md, status };
      if (featured_media) payload.featured_media = featured_media;
      if (categories.length) payload.categories = categories;
      if (tags.length)       payload.tags = tags;

      const data = await api('publish_post', payload);
      const verb = status === 'publish' ? 'Published' : (status === 'draft' ? 'Saved as draft' : 'Sent for review');
      const linkHtml = data.wp_post_url
        ? ' <a href="' + data.wp_post_url + '" target="_blank" rel="noopener">View on your site →</a>'
        : '';
      const box = $('wp-compose-msg');
      box.innerHTML = '✓ ' + verb + '.' + linkHtml;
      box.style.background = '#e7f7ec';
      box.style.color      = '#1a7f37';
      box.style.display    = 'block';
      clearComposerFields();
      loadHistory();
    } catch (err) {
      setMsg('wp-compose-msg',
        err.message + (err.hint ? ' — ' + err.hint : ''),
        'error');
    } finally {
      btn.disabled = false;
      btn.textContent = originalLabel;
    }
  }

  // ───── Media upload ──────────────────────────────────────────
  const MAX_UPLOAD_BYTES = 8 * 1024 * 1024;
  const MAX_IMAGE_EDGE   = 2000; // px — longest edge after resize
  const RESIZE_QUALITY   = 0.85; // JPEG quality

  // Resize a File to fit within MAX_IMAGE_EDGE on the longest edge. Returns
  // a Blob (or the original File if no resize needed / not supported).
  // Skipped for GIFs (canvas drops animation) and for images already small.
  // We pass through the result with a name and type so the upload code can
  // continue to read .name, .type, .size.
  async function maybeResizeImage(file) {
    // GIFs: don't resize — canvas would drop animation.
    if (file.type === 'image/gif') return file;
    if (!('createImageBitmap' in window) || typeof OffscreenCanvas === 'undefined' && typeof document.createElement('canvas').getContext !== 'function') {
      return file; // very old browser — skip
    }

    let bitmap;
    try {
      bitmap = await createImageBitmap(file);
    } catch (e) {
      // Decoding failed — let the server surface a clearer error.
      return file;
    }

    const { width, height } = bitmap;
    const longest = Math.max(width, height);
    if (longest <= MAX_IMAGE_EDGE) {
      bitmap.close && bitmap.close();
      return file; // already small enough
    }

    const scale = MAX_IMAGE_EDGE / longest;
    const tw = Math.round(width  * scale);
    const th = Math.round(height * scale);

    const canvas = document.createElement('canvas');
    canvas.width  = tw;
    canvas.height = th;
    const ctx = canvas.getContext('2d');
    // White background for JPEG output (PNG transparency would otherwise go black).
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, tw, th);
    ctx.drawImage(bitmap, 0, 0, tw, th);
    bitmap.close && bitmap.close();

    // Output type: keep webp as webp, otherwise produce JPEG (smaller for photos).
    const outType = file.type === 'image/webp' ? 'image/webp' : 'image/jpeg';
    const blob = await new Promise(res => canvas.toBlob(res, outType, RESIZE_QUALITY));
    if (!blob) return file; // toBlob can return null on weird browsers

    // Synthesize a name with the right extension for the new type.
    const dot = file.name.lastIndexOf('.');
    const stem = dot > 0 ? file.name.slice(0, dot) : file.name;
    const ext = outType === 'image/webp' ? 'webp' : 'jpg';
    blob.name = stem + '.' + ext; // we read this in uploadMedia
    return blob;
  }

  // Upload a File (or Blob from maybeResizeImage) to /api/wordpress.php?action=upload_media
  // as raw binary. Returns { media_id, source_url, filename, mime_type }.
  async function uploadMedia(rawFile) {
    const file = await maybeResizeImage(rawFile);
    if (file.size > MAX_UPLOAD_BYTES) {
      throw new Error('File is too large after resizing. Maximum is 8 MB.');
    }
    const filename = file.name || rawFile.name || 'upload';
    const headers = {
      'Content-Type': file.type || 'application/octet-stream',
      'X-Filename':   filename,
    };
    const token = localStorage.getItem('auth_token');
    if (token) headers['X-Auth-Token'] = token;

    const res = await fetch(API + '?action=upload_media', {
      method: 'POST',
      headers: headers,
      credentials: 'same-origin',
      body: file,
    });
    const text = await res.text();
    let envelope;
    try { envelope = JSON.parse(text); }
    catch (e) {
      throw new Error('Server returned non-JSON (HTTP ' + res.status + ').');
    }
    if (!envelope.success) {
      const err = new Error(envelope.message || 'Upload failed');
      err.hint = envelope.hint || null;
      throw err;
    }
    return envelope;
  }

  // Featured image picker
  function clearFeaturedImage() {
    composerState.featured = null;
    $('wp-featured-empty').style.display = '';
    $('wp-featured-set').style.display   = 'none';
    $('wp-featured-thumb').src = '';
    $('wp-featured-name').textContent = '—';
    $('wp-featured-file-input').value = '';
  }

  function setFeaturedImage(media) {
    composerState.featured = media;
    $('wp-featured-thumb').src = media.source_url;
    $('wp-featured-thumb').alt = media.filename || 'featured image';
    $('wp-featured-name').textContent = media.filename || 'image';
    $('wp-featured-empty').style.display = 'none';
    $('wp-featured-set').style.display   = 'flex';
  }

  async function onFeaturedFileChosen(ev) {
    const file = ev.target.files && ev.target.files[0];
    if (!file) return;
    const btn = $('wp-featured-pick-btn');
    const origLabel = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Uploading…';
    setMsg('wp-compose-msg', 'Uploading featured image…', 'info');
    try {
      const m = await uploadMedia(file);
      setFeaturedImage(m);
      setMsg('wp-compose-msg', '', 'info');
    } catch (err) {
      setMsg('wp-compose-msg',
        'Featured image upload failed: ' + err.message + (err.hint ? ' — ' + err.hint : ''),
        'error');
      $('wp-featured-file-input').value = '';
    } finally {
      btn.disabled = false;
      btn.textContent = origLabel;
    }
  }

  // Insert text at the textarea's cursor. Uses document.execCommand so the
  // operation goes onto the browser's undo stack — unlike a direct
  // textarea.value assignment, which is invisible to undo. execCommand is
  // technically deprecated but universally supported for this exact use
  // case; we fall back to a value assignment if it returns false.
  function insertAtCursor(textarea, text) {
    textarea.focus();
    let inserted = false;
    try {
      // execCommand respects the current selection, so positioning is automatic.
      inserted = document.execCommand('insertText', false, text);
    } catch (e) {
      inserted = false;
    }
    if (!inserted) {
      // Fallback: direct value mutation (won't be undoable, but at least works).
      const start = textarea.selectionStart;
      const end   = textarea.selectionEnd;
      const before = textarea.value.slice(0, start);
      const after  = textarea.value.slice(end);
      textarea.value = before + text + after;
      const cursor = start + text.length;
      textarea.selectionStart = textarea.selectionEnd = cursor;
    }
    renderPreview();
  }

  // ─── Insert-image panel ──────────────────────────────────
  // The "+ Insert image" button toggles a panel above the textarea where
  // the user picks alignment + size, then chooses the file. We emit
  // WordPress-recognized figure HTML so the published post wraps the text
  // around the image. Defaults: alignnone + size-large.
  const insertPanelState = {
    align: 'alignnone',
    size:  'size-large',
    pos:   'top',  // 'cursor' | 'top' | 'bottom' — default 'top' is the
                   // common pattern for wrapping (image precedes the text)
  };

  function syncInsertPanelButtons() {
    document.querySelectorAll('.wp-align-btn').forEach(b => {
      b.classList.toggle('active', b.dataset.align === insertPanelState.align);
    });
    document.querySelectorAll('.wp-size-btn').forEach(b => {
      b.classList.toggle('active', b.dataset.size === insertPanelState.size);
    });
    document.querySelectorAll('.wp-pos-btn').forEach(b => {
      b.classList.toggle('active', b.dataset.pos === insertPanelState.pos);
    });

    // Smart help text. Wrap-around alignment only works when there's body
    // text AFTER the image — so warn when the user picks Left/Right + Bottom,
    // which can never produce a visible wrap.
    const help = $('wp-insert-help');
    if (help) {
      const isFloated = (insertPanelState.align === 'alignleft' || insertPanelState.align === 'alignright');
      if (isFloated && insertPanelState.pos === 'bottom') {
        help.innerHTML = '<strong style="color:#b42318">Heads up:</strong> '
          + 'Left/Right alignment wraps the text that comes <em>after</em> the image. '
          + '"At bottom" means there\'s no text after — pick "At top" or "At cursor" '
          + 'so body text has room to wrap.';
      } else if (isFloated) {
        help.textContent = 'The image will float to the ' +
          (insertPanelState.align === 'alignleft' ? 'left' : 'right') +
          '. Body text after the image will wrap around it.';
      } else {
        help.textContent = 'For wrapping to work, use Left or Right alignment and place the image before the body text.';
      }
    }
  }

  function openInsertPanel() {
    const panel = $('wp-insert-panel');
    if (!panel) return;
    panel.style.display = '';
    syncInsertPanelButtons();
  }

  function closeInsertPanel() {
    const panel = $('wp-insert-panel');
    if (!panel) return;
    panel.style.display = 'none';
  }

  function buildFigureHtml(media, align, size) {
    const classes = ['wp-block-image', align, size].filter(Boolean).join(' ');
    const escAttr = (s) => String(s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
    const alt = (media.filename || 'image').replace(/\.[^.]+$/, '');

    // Inline styles so the wrap works regardless of WordPress theme. Many
    // themes (especially minimal/block-based ones) don't ship the alignleft
    // /alignright float CSS, and we can't audit every author's theme. Inline
    // styles also drive the live preview without needing a separate CSS path.
    //
    // Margins are kept tight: ~0.6em side gutter, ~0.4em bottom gap. Anything
    // larger reads as too airy alongside body copy in narrow blog columns.
    let figStyle = 'padding:0;';
    if (align === 'alignleft') {
      figStyle += 'float:left;margin:0 0.6em 0.4em 0;';
    } else if (align === 'alignright') {
      figStyle += 'float:right;margin:0 0 0.4em 0.6em;';
    } else if (align === 'aligncenter') {
      figStyle += 'display:block;margin:0.4em auto;text-align:center;';
    } else {
      figStyle += 'margin:0.4em 0;';
    }

    // Image width relative to its containing <figure>. The figure itself
    // does the absolute sizing via max-width on size classes; the img always
    // fills 100% of the figure so it scales cleanly. vertical-align:top
    // suppresses the few pixels of baseline gap that <img> picks up from
    // line-height by default.
    let imgStyle = 'display:block;height:auto;max-width:100%;vertical-align:top;margin:0;padding:0;';
    const isFloated = (align === 'alignleft' || align === 'alignright');
    let figSizeStyle = '';
    if (size === 'size-thumbnail') {
      figSizeStyle = 'max-width:150px;';
    } else if (size === 'size-medium') {
      figSizeStyle = 'max-width:300px;';
    } else if (size === 'size-large') {
      // Floated large images cap at ~60% column so there's room for text wrap.
      figSizeStyle = isFloated ? 'max-width:60%;' : 'max-width:100%;';
    } else { // size-full
      figSizeStyle = 'max-width:100%;';
    }
    figStyle += figSizeStyle;

    return '<figure class="' + escAttr(classes) + '" style="' + figStyle + '">'
         + '<img src="' + escAttr(media.source_url) + '" alt="' + escAttr(alt)
         + '" style="' + imgStyle + '" />'
         + '</figure>';
  }

  // Insert text into the textarea ensuring it's separated from surrounding
  // content by a blank line (paragraph break). This matters because Parsedown
  // and marked.js both require a blank line before/after a block-level HTML
  // element to treat it as block. Without that, the figure can end up glued
  // to the previous paragraph and the wrap won't render predictably.
  //
  // Position can be:
  //   'cursor' — insert at current selection (default)
  //   'top'    — insert at very start of the textarea
  //   'bottom' — insert at very end of the textarea
  function insertFigureBlock(textarea, html, position) {
    const value = textarea.value;
    let start, end;
    if (position === 'top') {
      start = 0; end = 0;
    } else if (position === 'bottom') {
      start = value.length; end = value.length;
    } else {
      start = textarea.selectionStart;
      end   = textarea.selectionEnd;
    }

    const before = value.slice(0, start);
    const after  = value.slice(end);

    // Ensure a blank line BEFORE the figure: if the preceding context doesn't
    // already end with two newlines (or we're at the very start), pad.
    let prefix = '';
    if (before.length > 0) {
      if (!/\n\n$/.test(before)) {
        prefix = before.endsWith('\n') ? '\n' : '\n\n';
      }
    }

    // Ensure a blank line AFTER the figure: same logic on the trailing side.
    let suffix = '';
    if (after.length > 0) {
      if (!/^\n\n/.test(after)) {
        suffix = after.startsWith('\n') ? '\n' : '\n\n';
      }
    } else {
      // Always end the document on a newline if we're inserting at end.
      suffix = '\n';
    }

    const insertion = prefix + html + suffix;

    // For 'cursor' position, use execCommand so the operation is undoable.
    // For 'top'/'bottom', we need to programmatically reposition selection
    // first, then execCommand picks it up. Fall back to direct value
    // assignment if execCommand isn't supported (preserves correctness even
    // at the cost of undo history in that edge case).
    textarea.focus();
    if (position !== 'cursor') {
      textarea.selectionStart = start;
      textarea.selectionEnd   = end;
    }
    let inserted = false;
    try {
      inserted = document.execCommand('insertText', false, insertion);
    } catch (e) {
      inserted = false;
    }
    if (!inserted) {
      textarea.value = before + insertion + after;
      const cursor = before.length + insertion.length;
      textarea.selectionStart = textarea.selectionEnd = cursor;
    }
    renderPreview();
  }

  async function onInsertFileChosen(ev) {
    const file = ev.target.files && ev.target.files[0];
    if (!file) return;
    const pickBtn = $('wp-insert-pick-btn');
    const origLabel = pickBtn.textContent;
    pickBtn.disabled = true;
    pickBtn.textContent = 'Uploading…';
    setMsg('wp-compose-msg', 'Uploading image…', 'info');
    try {
      const m = await uploadMedia(file);
      const figureHtml = buildFigureHtml(m, insertPanelState.align, insertPanelState.size);
      insertFigureBlock($('wp-compose-md'), figureHtml, insertPanelState.pos);
      setMsg('wp-compose-msg', '', 'info');
      closeInsertPanel();
    } catch (err) {
      setMsg('wp-compose-msg',
        'Image upload failed: ' + err.message + (err.hint ? ' — ' + err.hint : ''),
        'error');
    } finally {
      pickBtn.disabled = false;
      pickBtn.textContent = origLabel;
      $('wp-inline-file-input').value = '';
    }
  }

  // ───── Categories ────────────────────────────────────────────

  async function loadCategories() {
    const loading = $('wp-categories-loading');
    const empty   = $('wp-categories-empty');
    const list    = $('wp-categories-list');
    if (!loading || !empty || !list) return;

    loading.style.display = '';
    empty.style.display   = 'none';
    list.style.display    = 'none';
    list.innerHTML        = '';

    try {
      const data = await api('list_categories');
      const cats = data.categories || [];
      loading.style.display = 'none';
      if (cats.length === 0) {
        empty.style.display = '';
        return;
      }
      list.style.display = '';
      list.innerHTML = cats.map(c => {
        const id = 'wp-cat-' + c.id;
        const checked = composerState.categoryIds.has(c.id) ? 'checked' : '';
        const count = c.count > 0 ? ' <span style="color:var(--ink-soft);font-size:11px">(' + c.count + ')</span>' : '';
        return '<label for="' + id + '" style="display:flex;align-items:center;gap:8px;padding:3px 0;font-size:13px;cursor:pointer">'
             +   '<input type="checkbox" name="wp-cat" id="' + id + '" value="' + c.id + '" ' + checked + '>'
             +   '<span>' + escapeHtml(c.name) + count + '</span>'
             + '</label>';
      }).join('');
      list.querySelectorAll('input[name="wp-cat"]').forEach(cb => {
        cb.addEventListener('change', () => {
          const id = parseInt(cb.value, 10);
          if (cb.checked) composerState.categoryIds.add(id);
          else            composerState.categoryIds.delete(id);
        });
      });
    } catch (err) {
      loading.style.display = 'none';
      empty.style.display   = '';
      empty.textContent     = 'Could not load categories: ' + err.message;
    }
  }

  // ───── History ────────────────────────────────────────────────

  async function loadHistory() {
    const list  = $('wp-history-list');
    const empty = $('wp-history-empty');
    if (!list || !empty) return;
    try {
      const data  = await api('list_recent_posts');
      const posts = data.posts || [];
      if (posts.length === 0) {
        list.style.display  = 'none';
        empty.style.display = 'block';
        return;
      }
      empty.style.display = 'none';
      list.style.display  = 'block';
      list.innerHTML = posts.map(p => {
        const statusBadge = p.status === 'publish'
          ? '<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#e7f7ec;color:#1a7f37">published</span>'
          : (p.status === 'draft'
              ? '<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#fff7e0;color:#8a6d11">draft</span>'
              : '<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#eef4ff;color:#1e40af">' + p.status + '</span>');
        const link = p.wp_post_url
          ? '<a href="' + p.wp_post_url + '" target="_blank" rel="noopener" style="color:inherit;text-decoration:none">' + escapeHtml(p.title) + '</a>'
          : escapeHtml(p.title);
        return '<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--ink-faint)">'
          +   '<div style="flex:1;min-width:0">'
          +     '<div style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + link + '</div>'
          +     '<div style="font-size:12px;color:var(--ink-soft);margin-top:2px">' + fmtLocal(p.published_at) + '</div>'
          +   '</div>'
          +   '<div>' + statusBadge + '</div>'
          + '</div>';
      }).join('');
    } catch (err) {
      // Soft-fail: history failure shouldn't block the composer.
      list.style.display  = 'none';
      empty.style.display = 'block';
      empty.textContent   = 'Could not load post history: ' + err.message;
    }
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }

  function init() {
    // Guard against double-init if this script runs twice for any reason.
    if (window.__WP_INITED__) return;
    window.__WP_INITED__ = true;

    const oneClickBtn = $('wp-oneclick-btn');
    const testBtn = $('wp-test-btn');
    const saveBtn = $('wp-save-btn');
    const reverBtn = $('wp-reverify-btn');
    const discBtn = $('wp-disconnect-btn');

    if (oneClickBtn) oneClickBtn.addEventListener('click', onConnectOneClick);
    if (testBtn) testBtn.addEventListener('click', onTest);
    if (saveBtn) saveBtn.addEventListener('click', onSave);
    if (reverBtn) reverBtn.addEventListener('click', onReverify);
    if (discBtn) discBtn.addEventListener('click', onDisconnect);

    // Composer wiring (Session 7B)
    const mdArea     = $('wp-compose-md');
    const clearBtn   = $('wp-compose-clear-btn');
    const publishBtn = $('wp-compose-publish-btn');
    if (mdArea)     mdArea.addEventListener('input', renderPreview);
    if (clearBtn)   clearBtn.addEventListener('click', clearComposer);
    if (publishBtn) publishBtn.addEventListener('click', onPublish);

    // Featured image wiring (Session 7B chunk 2)
    const featPickBtn   = $('wp-featured-pick-btn');
    const featRemoveBtn = $('wp-featured-remove-btn');
    const featInput     = $('wp-featured-file-input');
    if (featPickBtn)   featPickBtn.addEventListener('click', () => featInput && featInput.click());
    if (featRemoveBtn) featRemoveBtn.addEventListener('click', clearFeaturedImage);
    if (featInput)     featInput.addEventListener('change', onFeaturedFileChosen);

    // Insert-image panel wiring (Session 7B chunk 2 — image alignment)
    const insertImgBtn = $('wp-insert-image-btn');
    const insertCancel = $('wp-insert-cancel-btn');
    const insertPick   = $('wp-insert-pick-btn');
    const inlineInput  = $('wp-inline-file-input');
    if (insertImgBtn) insertImgBtn.addEventListener('click', openInsertPanel);
    if (insertCancel) insertCancel.addEventListener('click', closeInsertPanel);
    if (insertPick)   insertPick.addEventListener('click', () => inlineInput && inlineInput.click());
    if (inlineInput)  inlineInput.addEventListener('change', onInsertFileChosen);

    // Alignment + size + position button groups
    document.querySelectorAll('.wp-align-btn').forEach(b => {
      b.addEventListener('click', () => {
        insertPanelState.align = b.dataset.align;
        syncInsertPanelButtons();
      });
    });
    document.querySelectorAll('.wp-size-btn').forEach(b => {
      b.addEventListener('click', () => {
        insertPanelState.size = b.dataset.size;
        syncInsertPanelButtons();
      });
    });
    document.querySelectorAll('.wp-pos-btn').forEach(b => {
      b.addEventListener('click', () => {
        insertPanelState.pos = b.dataset.pos;
        syncInsertPanelButtons();
      });
    });

    // Refresh whenever the Website nav item is clicked.
    document.querySelectorAll('.nav-item[data-view="website"]').forEach(el => {
      el.addEventListener('click', () => setTimeout(refresh, 0));
    });

    // Also refresh once on initial load so the nav dot is correct
    // if the user is already connected.
    refresh();
  }

  // Run init once the DOM is ready. If this script is placed at the
  // end of <body> the DOM is already parsed, so we can init immediately.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  return { refresh };
})();

// ── Main application ──
const API = '/api';
let authToken = localStorage.getItem('auth_token') || '';
let currentUser = null;
let platformStatus = { meta: false, tiktok: false, bluesky: false, linkedin: false };

// ── INIT ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // The password-reset (?reset_token=…) and signup password-setup
  // (?setup_token=…) deep links were removed along with the sign-in and
  // sign-up forms — the landing page now carries an enquiry form only.
  if (authToken && !window.AH_STANDALONE) {
    // ⚠ Not on the standalone page — ebookStandaloneBoot() owns that boot.
    // Both running meant loadUser() finished last and navigated to the
    // dashboard, so a returning buyer landed in an app they never joined.
    loadUser();
  }

  // ── STANDALONE eBook Maker (/ebook-maker/) ──
  // The same app, the same editor, the same code — shown as a single-purpose
  // tool for a visitor with no account. Deliberately NOT a second copy of the
  // page: the editor is a thousand lines of markup and behaviour, and two
  // copies would drift apart within a week.
  if (window.AH_STANDALONE) { ebookStandaloneBoot(); }

  document.getElementById('post-content').addEventListener('input', function() {
    spUpdateCharCount();
    updatePostPreview();
  });
  document.getElementById('post-link').addEventListener('input', function() {
    spUpdateCharCount();
    updatePostPreview();
  });
  document.querySelectorAll('.platform-check').forEach(cb => cb.addEventListener('change', spUpdateCharCount));
  document.getElementById('gv-social-copy-text')?.addEventListener('input', gvSocialUpdatePreview);
  document.getElementById('gv-social-link')?.addEventListener('input', gvSocialUpdatePreview);
  document.getElementById('gv-event-copy-text')?.addEventListener('input', gvEventUpdatePreview);
  document.getElementById('gv-event-link')?.addEventListener('input', gvEventUpdatePreview);

});

// ── LANDING HELPERS ───────────────────────────────────────────

// "Get started" / "See pricing" CTAs land the visitor in the pricing
// section, where each tier's button opens the enquiry form (see the
// enquiry script at the end of index.html).
function scrollToPricing() {
  const el = document.getElementById('pricing');
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showApp() {
  document.getElementById('landing').style.display = 'none';
  document.getElementById('app-screen').classList.add('visible');
  document.body.style.overflow = 'hidden';
}

function showLanding() {
  document.getElementById('landing').style.display = 'block';
  document.getElementById('app-screen').classList.remove('visible');
  document.body.style.overflow = '';
  // Ensure demo banner is cleared when no one is logged in
  const banner = document.getElementById('demo-banner');
  if (banner) banner.classList.remove('visible');
  document.body.classList.remove('demo-mode');
}

// Landing tour trailer: plays only while the frame is actually on screen.
// The <video> deliberately has no autoplay attribute — autoplay forces the
// browser to download the full 26 MB MP4 on every page load (it overrides
// preload="none"), which was adding ~18s to login. The observer starts
// playback when the tour section scrolls into view and pauses it when it
// leaves; a hidden landing view never intersects, so logged-in users never
// download the file at all.
(function () {
  function watchDemoTrailer() {
    const v = document.getElementById('demo-trailer');
    if (!v || !('IntersectionObserver' in window)) return;
    new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting && v.paused) {
          const p = v.play();
          if (p && typeof p.catch === 'function') p.catch(() => {});
        } else if (!e.isIntersecting && !v.paused) {
          v.pause();
        }
      });
    }, { threshold: 0.4 }).observe(v);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', watchDemoTrailer);
  } else {
    watchDemoTrailer();
  }
})();

// Manual fallback: click anywhere on the frame to play/pause, in case the
// browser blocks the observer-triggered play (low-power mode etc.).
function toggleDemoTrailer() {
  const v = document.getElementById('demo-trailer');
  if (!v) return;
  if (v.paused) {
    const p = v.play();
    if (p && typeof p.catch === 'function') p.catch(() => {});
  } else {
    v.pause();
  }
}

// Landing tour: the 3 "window-button" dots in the books-mockup also act as
// a cover picker. Clicking a dot swaps the demo cover image — uses real
// covers generated by the app's own cover tool (eats our own dog food).
function swapTourCover(dot, src) {
  const picker = dot.parentElement;
  if (!picker) return;
  const targetId = picker.getAttribute('data-cover-target');
  const img = targetId && document.getElementById(targetId);
  if (img) img.src = src;
  picker.querySelectorAll('span').forEach(s => s.classList.remove('active'));
  dot.classList.add('active');
}

// ── API ───────────────────────────────────────────────────────
// ── DEMO MODE — intercept AI / post / send calls ──────────────
// When currentUser.is_demo === 1, these endpoints are intercepted
// in the frontend so demo visitors don't burn real AI quota or
// send real social/email traffic.

const _demoTaskTitles = {
  book_description: 'AI book description',
  press_release:    'AI press release',
  author_bio:
    "Margaret Hayes spent thirty years teaching English literature at a small college in Ohio " +
    "before retiring to the Maine coast in 2018. She lives in a sea-grey cottage with her husband, " +
    "two indifferent cats, and an alarming number of half-finished crosswords. The Lighthouse Letters " +
    "is her first novel.",

  tagline:
    "A late-summer murder draws an unlikely amateur sleuth onto the rocks of a quiet harbor town.",

  social_post:      'AI social post',
  email_subject:    'AI email subject line',
  email_body:       'AI email body',
  cover_letter:     'AI cover letter / pitch',
  sell_sheet:       'AI sell sheet',
  tagline:          'AI taglines',
  logline:          'AI logline',
  custom:           'AI draft',
};

const _demoExamples = {
  book_description:
    "Cape Hollow, Maine keeps its secrets the way the tide keeps the shore — always pushing something back, never letting anything go for long.\n\n" +
    "Helen Marsh came here for quiet. Retirement quiet. The kind that smells like salt air and old paperbacks and has absolutely nothing to do with dead bodies. But the body beneath the lighthouse didn't consult her plans — and neither did the letter clenched in its cold hand, signed by a woman the whole village buried forty years ago.\n\n" +
    "The locals aren't talking. The lighthouse has been dark for decades. And Helen, who spent thirty years teaching eighth-graders not to lie to her face, knows evasion when she sees it.\n\n" +
    "Armed with a retired schoolteacher's particular brand of relentless patience, a postmistress named Edie who runs entirely on espresso and local gossip, and Mr. Crouch — a one-eyed cat with a deeply suspicious nature and impeccable instincts — Helen begins pulling at a thread that unravels a story of long-buried love, quiet betrayal, and a lighthouse that was never just guiding ships safely home.\n\n" +
    "Cape Hollow wanted to stay forgotten. Helen Marsh didn't move four states away to mind her own business.\n\n" +
    "The Lighthouse Letters is a slow-burn, character-rich debut for readers who love the atmospheric warmth of Louise Penny, the wit of Richard Osman, and the particular satisfaction of a mystery solved over strong tea in a town that really should have known better than to wash its secrets ashore.",

  press_release:
    "EMBARGOED UNTIL MAY 15, 2026\n" +
    "FOR IMMEDIATE RELEASE AFTER EMBARGO\n\n" +
    "DEBUT COZY MYSTERY ‘THE LIGHTHOUSE LETTERS’ EARNS TOP RECOGNITION AS STANDOUT VOICE IN INDEPENDENT FICTION\n\n" +
    "COLUMBUS, Ohio, May 15, 2026 — A retired schoolteacher, a one-eyed cat, and a forty-year-old mystery along the Maine coast have combined to earn Columbus-based debut novelist Margaret Hayes a coveted Silver Falchion Award in the Best Debut Mystery category, announced today by Killer Nashville. The Lighthouse Letters: A Cape Hollow Mystery was cited for its distinctive narrative voice, layered plot construction, and a protagonist judges called “immediately irreplaceable” among the current field of independent cozy mystery writers.\n\n" +
    "The novel follows Helen Marsh, a retired schoolteacher who relocates to the fictional coastal village of Cape Hollow, Maine, seeking quiet retirement — only to find herself at the center of a decades-old cold case when a body surfaces beneath a crumbling lighthouse, clutching a letter signed by a woman long dead. Joined by her high-strung postmistress Edie and the inscrutable Mr. Crouch, Helen unravels a tangle of buried secrets the town has quietly protected for a generation. Killer Nashville presented the Silver Falchion at the 2026 Killer Nashville International Writers’ Conference in Nashville, Tennessee, recognizing Hayes among 38 nominated titles in the category.\n\n" +
    "“I wrote The Lighthouse Letters for the kind of reader who curls up with a book on a rainy afternoon and stays there all weekend,” said Hayes. “To have it recognized this way — by readers and writers who genuinely love the genre — is a quiet, enormous gift.” Comparisons to Louise Penny and Richard Osman have followed the book since its release, a reflection of the novel’s balance of warmth, wit, and genuine suspense that has drawn an enthusiastic readership well beyond the author’s home state. The Lighthouse Letters is available now in paperback, eBook, and audiobook through major retailers and IngramSpark, and at independent booksellers nationwide.\n\n" +
    "ABOUT MARGARET HAYES\n\n" +
    "Margaret Hayes is a Columbus, Ohio-based writer and debut novelist. The Lighthouse Letters: A Cape Hollow Mystery is the first installment in the Cape Hollow Mystery series. A lifelong reader of classic and contemporary mystery fiction, Hayes brings a background in thirty years of teaching English literature at a small Ohio college to character-driven storytelling rooted in place, community, and the quiet persistence of the past. Hayes is currently at work on the second Cape Hollow Mystery.\n\n" +
    "###\n\n" +
    "MEDIA CONTACT\n" +
    "Margaret Hayes\n" +
    "Author, The Lighthouse Letters\n" +
    "info@elitepublishing.co",

  author_bio:
    "Margaret Hayes spent thirty years teaching English literature at a small college in Ohio " +
    "before retiring to the Maine coast in 2018. She lives in a sea-grey cottage with her husband, " +
    "two indifferent cats, and an alarming number of half-finished crosswords. The Lighthouse Letters " +
    "is her first novel.",

  tagline:
    "A late-summer murder draws an unlikely amateur sleuth onto the rocks of a quiet harbor town.",

  social_post:
    "just finished reading my own book for the first time in years and... wow, i forgot how much i made my characters suffer. grab a copy and judge my choices 👉 [link]",

  email_subject:
    "A Body in the Surf, a Letter from the Grave, and Helen Marsh’s Quiet Life About to Unravel",

  email_body:
    "{{first_name}}, there’s a moment in the writing of every book where the story stops feeling like something you’re inventing and starts feeling like something you’ve been quietly remembering. That happened to me somewhere around chapter four, when Helen Marsh — retired schoolteacher, devoted crossword-solver, someone who has very deliberately chosen a small and peaceful life — found a dead man at the foot of a lighthouse with a letter in his hand. A letter written by a woman forty years in the grave. I hadn’t planned for Helen to care. She planned not to care. We were both wrong.\n\n" +
    "The Lighthouse Letters: A Cape Hollow Mystery is the book that came out of that surprise — a story set on the rocky Maine coast, where the fog rolls in fast and the locals carry old silences the way other people carry grudges. Helen doesn’t set out to solve anything. She just starts asking the kinds of questions that a certain type of careful, observant person can’t help but ask, with her postmistress Edie running interference and a one-eyed cat named Mr. Crouch offering moral support of varying quality. What unravels is a tangle of love and betrayal that someone in Cape Hollow has spent four decades keeping tidy — and would very much prefer to keep that way.\n\n" +
    "If you’ve been waiting for something unhurried and atmospheric, with a little salt air and a mystery that earns its ending, I think this might be exactly the book for you. The Lighthouse Letters is available now wherever you like to buy your books — and if you do pick it up, I’d genuinely love to hear what you make of Mr. Crouch. He’s earned some fan mail.",

  cover_letter:
    "Margaret Hayes\nHilliard, Ohio\ninfo@elitepublishing.co\nMay 8, 2026\n\n" +
    "[Agent Name]\n[Agency Name]\n\n" +
    "Dear [Agent Name],\n\n" +
    "The Lighthouse Letters: A Cape Hollow Mystery is a cozy mystery novel of approximately 92,000 words. It opens with retired schoolteacher Helen Marsh arriving in the coastal village of Cape Hollow, Maine, expecting small pleasures — crosswords, sea air, letters to her sister in Ohio. What she gets instead is a body beneath the old lighthouse, a corpse clutching a letter signed by a woman forty years dead. Helen, aided by her perpetually overcaffeinated postmistress Edie and a one-eyed cat named Mr. Crouch, follows that letter into a story of love, betrayal, and a lighthouse that has been keeping secrets far longer than it has kept ships from the rocks. The book will appeal to readers who follow Louise Penny for her atmosphere and moral weight, Richard Osman for his sharp ensemble wit, and who reach instinctively for anything set along the New England coast.\n\n" +
    "The novel turns on a structural conceit — the letters themselves serve as both clue and unreliable narrator — that gives the mystery an additional layer of readerly pleasure beyond the central whodunit. Cape Hollow is built for a series, with the village’s grudging acceptance of Helen and the lighthouse’s history leaving ample ground to cover in subsequent books.\n\n" +
    "The Lighthouse Letters is my first novel. Before turning to fiction, I spent thirty years teaching English literature at a small Ohio college, where I read more cozy mysteries than seems reasonable and developed a certain conviction about what makes them work. I’ve completed the manuscript and am at work on a second Cape Hollow mystery.\n\n" +
    "I would be glad to send the full manuscript, a synopsis, or any portion you prefer at your request. Thank you for your time and consideration.\n\n" +
    "Sincerely,\nMargaret Hayes",

  sell_sheet:
    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
    "THE LIGHTHOUSE LETTERS\n" +
    "A Cape Hollow Mystery\n\n" +
    "A debut cozy mystery  |  Trade Paperback\n" +
    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" +
    "HOOK\n\n" +
    "A dead man. A letter signed by a woman forty years in the grave. And a retired schoolteacher who just wanted a quiet life by the sea.\n\n" +
    "BOOK DESCRIPTION\n\n" +
    "When Helen Marsh retires to the coastal village of Cape Hollow, Maine, she comes for the long walks and the crossword — not for the body that washes ashore beneath the old lighthouse, clutching a yellowed letter signed by a woman who died forty years ago. With the help of her perpetually overcaffeinated postmistress Edie and a one-eyed cat named Mr. Crouch, Helen begins pulling at a thread the locals would rather leave buried in the dunes. What unspools is a story of love, betrayal, and a lighthouse that may have been keeping more than ships from running aground.\n\n" +
    "Sharp, atmospheric, and rooted in the slow-burning charm of coastal New England, The Lighthouse Letters marks the beginning of a mystery series with a sleuth readers will want to follow back to Cape Hollow again and again.\n\n" +
    "KEY SELLING POINTS\n\n" +
    "◆ DISTINCTIVE AMERICAN SETTING — Off-season coastal Maine is bracingly underrepresented in a genre crowded with English villages. Cape Hollow offers something genuinely fresh: salt air, closed-up summer houses, and secrets the tides keep bringing in.\n\n" +
    "◆ A SLEUTH READERS WILL ROOT FOR — Helen Marsh is practical, funny, and stubborn in all the right ways. Her dynamic with postmistress Edie and the irascible Mr. Crouch gives the book its warmth and wit.\n\n" +
    "◆ SERIES POTENTIAL — Cape Hollow is built to sustain a long-running cast and community. Strong candidate for series readers and book clubs.\n\n" +
    "◆ TAPS A PROVEN, GROWING MARKET — Cozy mysteries are seeing a significant revival in physical bookstores, with reader demand for “gentle” mystery outpacing the current supply of quality new series.\n\n" +
    "◆ CLEAN HAND-SELL COMP LINE — Readers who loved Louise Penny’s Three Pines or Richard Osman’s Thursday Murder Club and are hungry for something set on this side of the Atlantic will find a natural next read here.\n\n" +
    "COMPARABLE TITLES\n\n" +
    "Louise Penny — Still Life and the Chief Inspector Gamache series (atmospheric small-community mystery; literary warmth)\n\n" +
    "Richard Osman — The Thursday Murder Club (amateur sleuth charm; ensemble wit; broad commercial appeal)\n\n" +
    "AUTHOR BIO\n\n" +
    "Margaret Hayes spent thirty years teaching English literature at a small Ohio college before retiring to the Maine coast. The Lighthouse Letters is her first novel and the first in the Cape Hollow Mystery series. She lives in a sea-grey cottage on the Maine coast with her husband, two indifferent cats, and an alarming number of half-finished crosswords.\n\n" +
    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
    "ORDERING & CONTACT INFORMATION\n\n" +
    "For review copies, wholesale inquiries, and ordering information, please contact:\n\n" +
    "Margaret Hayes\n" +
    "info@elitepublishing.co\n\n" +
    "ISBN: 978-1-DEMO-0001-0\n" +
    "Price: $16.95\n" +
    "Formats: Paperback, eBook, Audiobook\n" +
    "Page Count: 312\n" +
    "Publisher: Independently published (IngramSpark)\n" +
    "Publication Date: April 15, 2026\n" +
    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",

  tagline:
    "1. A body arrives with a forty-year-old secret, and a retired teacher’s quiet summer becomes an investigation the lighthouse has been waiting to confess.\n\n" +
    "2. When the tide brings murder to Cape Hollow, a schoolteacher discovers that some letters were never meant to be found—or read alone.\n\n" +
    "3. Helen Marsh wanted solitude; instead, she got a corpse, a cryptic note from a ghost, and proof that small towns keep their darkest truths wedged between the rocks.\n\n" +
    "4. A lighthouse has stood silent for decades, but the body at its base finally speaks—and a reluctant sleuth must learn to listen.\n\n" +
    "5. In Cape Hollow, the real mystery isn’t who died forty years ago; it’s why someone wanted Helen to find out now.",

  logline:
    "1. A retired schoolteacher’s quiet Maine retirement unravels when a decades-old corpse surfaces with a letter from beyond the grave, forcing Helen Marsh to excavate secrets the whole village has agreed to forget.\n\n" +
    "2. Helen Marsh traded her classroom for coastal solitude, but the lighthouse keeper’s skeleton that washes ashore comes with a mystery that won’t let her rest until she knows who died—and why everyone pretended to forget.\n\n" +
    "3. When Helen moves to Cape Hollow seeking silence, a body caught in the lighthouse beam drags her into a forty-year-old crime that the townspeople have carefully, deliberately buried in the sand.\n\n" +
    "4. The dead don’t stay buried in Cape Hollow, and Helen Marsh—armed with nothing but curiosity, a postmistress, and a one-eyed cat—is about to learn why the lighthouse’s light was meant to keep more than ships away.\n\n" +
    "5. Helen Marsh wanted her retirement to be marked only by crosswords and correspondence, but the corpse and cryptic letter that surface beneath the old lighthouse promise her a mystery darker than the Maine fog that hides it.",

  custom:
    "(In your real account, this generates a custom draft tuned to your book's metadata. The demo can't run a one-off custom prompt — it would need real AI access. Sign up to use custom prompts.)",
};

// Random sample of platform names for demo-modal copy ("post to platforms
// like X, Y, Z"). Randomized per view so repeat visitors see the breadth of
// the catalog rather than the same three names every time.
function _demoPlatformSample(contentType) {
  const pools = {
    image: ['Instagram', 'Facebook', 'Pinterest', 'TikTok', 'Threads', 'Bluesky', 'LinkedIn', 'Goodreads', 'BookBub'],
    video: ['Instagram Reels', 'TikTok', 'Facebook', 'Threads', 'Bluesky', 'LinkedIn', 'Discord'],
  };
  const pool = (pools[contentType] || pools.image).slice();
  const picks = [];
  while (picks.length < 4 && pool.length) {
    picks.push(pool.splice(Math.floor(Math.random() * pool.length), 1)[0]);
  }
  return picks.join(', ');
}

// Demo-only strip on the creation pages (Social Graphic, Quote Card,
// Trailer): shows a random sample of platforms the finished asset can be
// posted to, with brand-color dots. Hidden for real users — their pages
// show the real platform list at post time via the handoff modal.
const _DEMO_PLATFORM_POOL = {
  image: [
    ['Instagram', '#E4405F'], ['Facebook', '#1877F2'], ['Pinterest', '#E60023'],
    ['TikTok', '#000000'], ['Threads', '#000000'], ['Bluesky', '#0285FF'],
    ['LinkedIn', '#0A66C2'], ['Goodreads', '#553B08'], ['BookBub', '#E70E02'],
  ],
  video: [
    ['Instagram Reels', '#E4405F'], ['TikTok', '#000000'], ['Facebook', '#1877F2'],
    ['Threads', '#000000'], ['Bluesky', '#0285FF'], ['LinkedIn', '#0A66C2'],
    ['Discord', '#5865F2'],
  ],
};

function _renderDemoPlatformStrip(elId, contentType) {
  const el = document.getElementById(elId);
  if (!el) return;
  const isDemo = currentUser && (currentUser.is_demo == 1 || currentUser.is_demo === true);
  if (!isDemo) { el.style.display = 'none'; return; }
  const pool  = (_DEMO_PLATFORM_POOL[contentType] || _DEMO_PLATFORM_POOL.image).slice();
  const picks = [];
  while (picks.length < 5 && pool.length) {
    picks.push(pool.splice(Math.floor(Math.random() * pool.length), 1)[0]);
  }
  el.innerHTML = '<span class="dps-label">Post-ready for:</span>' +
    picks.map(p => '<span class="dps-chip"><span class="pdot" style="background:' + p[1] + '"></span>' + p[0] + '</span>').join('') +
    '<span>+ more</span>';
  el.style.display = 'flex';
}

// ── Sophie in demo mode ───────────────────────────────────────────────────
// Real chat UI, pre-written answers, no model call. chat.php refuses the demo
// account server-side and the demo session is public, so a live model here
// would be an unauthenticated pipe to the Anthropic account. Canned answers
// cost nothing, cannot hallucinate a feature we don't have, and are simply
// marketing copy — edit the text below, it is not generated.
//
// TODO(Bob): replace this copy with your own wording. Two flagged below.
const _DEMO_SOPHIE_QA = [
  {
    q: 'What does it cost?',
    keys: ['cost', 'price', 'pricing', 'how much', 'plan', 'subscription', 'expensive'],
    a: 'Three plans. Starter is $14.95 a month, Pro is $24, and Unlimited is $99. '
     + 'Pro and Unlimited are about 20% cheaper paid annually, and Starter is $135 for the year.\n\n'
     + 'Every plan includes the AI tools — the difference is how much you generate each month.'
  },
  {
    q: 'Can I try it before I buy?',
    keys: ['trial', 'free', 'try it', 'try before', 'demo', 'test', 'money back', 'refund', 'cancel'],
    a: 'You already are — this demo is the free look, with no card and nothing to cancel. '
     + 'Click around as much as you like.\n\n'
     + 'There is no separate trial period. When you are ready you pick a plan and start working on '
     + 'your own book straight away.'
  },
  {
    q: 'Can it write my press release?',
    keys: ['press release', 'press', 'media', 'publicity', 'journalist'],
    a: 'Yes. You pick the book, tell it the angle — a launch, an award, an event — and it drafts a '
     + 'full release in standard format, dateline and boilerplate included.\n\n'
     + 'It reads your book\'s details first, so the draft is about your actual book rather than a template '
     + 'with your title dropped in. You edit before anything goes out.'
  },
  {
    q: 'What about social media posts?',
    keys: ['social', 'post', 'facebook', 'instagram', 'twitter', 'linkedin', 'bluesky', 'tiktok', 'schedule'],
    a: 'You write a post once — or let the AI draft it — and send it to several platforms at once. '
     + 'You can publish immediately, schedule it, or save a draft.\n\n'
     + 'Some platforms connect directly. For the rest, the app prepares your caption and image and hands '
     + 'them off so you paste and post in one step.'
  },
  {
    q: 'Does it work with Amazon KDP?',
    keys: ['kdp', 'amazon', 'keywords', 'categories', 'a+', 'rank', 'bisac'],
    a: 'Yes — there\'s a set of KDP tools. Keyword and category research with BISAC codes, A+ content modules, '
     + 'a KDP Select promo planner, and a sales rank logger to track how you move over time.'
  },
  {
    q: 'Can it make my eBook?',
    keys: ['ebook', 'epub', 'convert', 'manuscript', 'kindle', 'format', 'picture book'],
    a: 'It does. Upload a Word or RTF manuscript and it formats the whole book — chapter breaks, a linked '
     + 'table of contents, clean typography — then converts it to EPUB for Kindle, Apple Books and Kobo.\n\n'
     + 'Picture books take a different path: upload your print-ready PDF and it builds a fixed-layout EPUB '
     + 'that keeps every page exactly as you designed it.'
  },
  {
    q: 'What about book trailers and graphics?',
    keys: ['trailer', 'video', 'graphic', 'image', 'cover', 'quote card', 'flyer', 'slideshow'],
    a: 'There\'s a whole graphics and video section — cover concepts, social graphics, quote cards, event '
     + 'flyers, and book trailers assembled from your cover and blurb with AI backdrops and optional narration.'
  },
  {
    q: 'Do I need to be technical?',
    keys: ['technical', 'hard', 'difficult', 'easy', 'learn', 'beginner', 'help', 'support'],
    a: 'No. It\'s built for authors, not marketers. There\'s a Learn section with short practical lessons, '
     + 'and I\'m here inside the app when you get stuck.'
  },
  {
    q: 'Can I have a website for my book?',
    keys: ['website', 'wordpress', 'site', 'domain', 'author website'],
    a: 'Yes. There\'s a guided setup for a WordPress author site, with genre style packs so it looks like it '
     + 'belongs to your book, and your titles push straight from the app to the site.'
  }
];

function _demoSophieAnswer(msg) {
  const m = (msg || '').toLowerCase();
  let best = null, bestScore = 0;
  _DEMO_SOPHIE_QA.forEach(function (item) {
    let score = 0;
    item.keys.forEach(function (k) { if (m.indexOf(k) !== -1) score += k.length; });
    if (score > bestScore) { bestScore = score; best = item; }
  });
  if (best) return best.a;
  return 'That one I can\'t answer in the demo — in your own account I can look at your actual books and '
       + 'campaigns to give you a real answer.\n\nHere I can tell you about pricing, press releases, social '
       + 'posts, KDP tools, eBook conversion, trailers and graphics, or setting up an author website. '
       + 'Tap one below, or ask about any of those.';
}

// Tappable question chips. Bubbles are rendered with textContent, so these
// live alongside the transcript rather than inside a message.
const _demoSophieAsked = [];

function _demoSophieChips() {
  const host = document.getElementById('chat-messages');
  if (!host) return;
  const old = document.getElementById('demo-sophie-chips');
  if (old) old.remove();

  // Only what they haven't asked yet, capped — nine chips fill the whole panel
  // and push the answer they just received out of view.
  const remaining = _DEMO_SOPHIE_QA.filter(function (i) { return _demoSophieAsked.indexOf(i.q) === -1; });
  if (!remaining.length) return;

  const wrap = document.createElement('div');
  wrap.id = 'demo-sophie-chips';
  wrap.style.cssText = 'display:flex;flex-wrap:wrap;gap:6px;padding:8px 4px 4px';
  remaining.slice(0, 4).forEach(function (item) {
    const b = document.createElement('button');
    b.type = 'button';
    b.textContent = item.q;
    b.style.cssText = 'font-size:13px;padding:7px 12px;border-radius:999px;border:1px solid #bcd3ea;'
                    + 'background:#eef5fc;color:#2b6cb0;cursor:pointer;line-height:1.3;text-align:left';
    b.onclick = function () {
      const input = document.getElementById('chat-input');
      if (!input) return;
      if (_demoSophieAsked.indexOf(item.q) === -1) _demoSophieAsked.push(item.q);
      input.value = item.q;
      if (typeof sendChat === 'function') sendChat();
      else { const s = document.getElementById('chat-send'); if (s) s.click(); }
    };
    wrap.appendChild(b);
  });
  host.appendChild(wrap);
  host.scrollTop = host.scrollHeight;
}

function _demoIntercept(endpoint, options) {
  const url = (endpoint || '').split('?')[0];

  // ── Campaigns: let the demo explore the composer without persisting ──
  // openComposer() creates a draft immediately so it has an id to autosave
  // against. That POST hit the demo lockdown, so clicking "+ New campaign"
  // just produced an error and the composer never opened. Hand back a
  // throwaway id and swallow the autosaves; reads still go to the server, and
  // anything that would actually send still gets the send warning.
  if (url.indexOf('/campaigns.php') === 0) {
    const DEMO_DRAFT_ID = -1;
    const act = ((endpoint.split('action=')[1] || '').split('&')[0]) || '';
    if (act === 'create') return { success: true, id: DEMO_DRAFT_ID };

    // The throwaway draft only exists in the browser, so a real GET for it
    // comes back "Campaign ID required". Answer for it here, using whatever
    // the composer currently holds, so Next: choose recipients still works.
    if (act === 'get' && endpoint.indexOf('id=' + DEMO_DRAFT_ID) > -1) {
      const val = id => (document.getElementById(id) || {}).value || '';
      const body = (document.getElementById('composer-body') || {}).innerHTML || '';
      return { success: true, campaign: {
        id: DEMO_DRAFT_ID,
        name: val('composer-name') || 'Untitled campaign',
        subject: val('composer-subject'),
        preheader: val('composer-preheader'),
        body_html: body,
        status: 'draft',
        recipients_count: 0
      } };
    }
    if (act === 'update' || act === 'set_recipients') return { success: true };
    if (act === 'send_now' || act === 'send_test' || act === 'schedule') {
      showDemoModal({
        type: 'warn',
        eyebrow: 'Demo mode',
        title: 'In your real account, this would send',
        body: 'Your campaign would go to your subscriber list, or to a test address you choose.\n\n'
            + 'The demo never sends — sign up to start emailing your readers.',
        footNote: 'Your real account never sends without your explicit click.',
      });
      return { success: false, message: 'Demo mode — nothing was sent' };
    }
    if (act === 'delete' || act === 'cancel' || act === 'duplicate') {
      showDemoModal({
        type: 'ai',
        eyebrow: 'Demo mode',
        title: 'The demo stays as it is',
        body: 'Every visitor shares this demo, so campaigns here can\u2019t be changed or removed.\n\n'
            + 'In your own account these are yours to edit, duplicate and delete freely.',
        footNote: 'Sign up to run your own campaigns.',
      });
      return { success: false, message: 'Not available in demo mode' };
    }
    return null;   // list, get, stats, preflight — real reads, let them through
  }

  // ── Sophie: canned Q&A, no model call ──
  if (url === '/chat.php') {
    const action = ((endpoint.split('action=')[1] || '').split('&')[0]) || '';
    if (action === 'history')  { setTimeout(_demoSophieChips, 250); return { success: true, turns: [] }; }
    if (action === 'feedback') { return { success: true }; }
    if (action === 'ask') {
      let body = {};
      try { body = options && options.body ? JSON.parse(options.body) : {}; } catch (e) {}
      setTimeout(_demoSophieChips, 80);
      return { success: true, response: _demoSophieAnswer(body.message || ''), conversation_id: 0 };
    }
    return { success: false, message: 'Not available in demo mode' };
  }

  // ── Composer endpoints (sell sheet, cover letter, press release, bio…) ──
  // These don't route through ai_gateway.php, so without this a demo visitor's
  // click reached the backend and came back as a raw demo-lockdown 403 rather
  // than an explanation. Serve the pre-baked Margaret Hayes example where one
  // exists, and explain the rest.
  const _demoComposers = {
    // key = the property THIS endpoint's caller actually reads off the response.
    // They all differ; returning a generic `draft` filled nothing and the demo
    // silently showed an empty result card.
    '/sell_sheet.php':         { title: 'Sell sheet',            key: 'sell_sheet',    example: 'sell_sheet' },
    '/cover_letter.php':       { title: 'Cover letter',          key: 'cover_letter',  example: 'cover_letter' },
    '/press_release.php':      { title: 'Press release',         key: 'press_release', example: 'press_release' },
    '/author_central_bio.php': { title: 'Author Central bio',    key: 'bio',           example: 'author_bio' },
    '/tagline.php':            { title: 'Tagline ideas',         key: 'lines',         example: 'tagline', asArray: true },
    '/kdp_keywords.php':       { title: 'Keywords + categories', key: 'suggestions',   example: null },
    '/kdp_aplus.php':          { title: 'A+ content',            key: 'modules',       example: null },
    '/email_ai.php':           { title: 'Email copy',            key: 'subjects',      example: null }
  };
  if (_demoComposers[url]) {
    const spec = _demoComposers[url];
    const example = spec.example ? (_demoExamples[spec.example] || null) : null;
    showDemoModal({
      type: 'ai',
      eyebrow: 'AI demo preview',
      title: spec.title,
      body: example
        ? 'This is a finished example for the demo book, so you can see the shape of what you get.\n\n'
          + 'In your own account it is written from your book — your title, blurb, genre and audience — and you can edit it, regenerate it, and download it as PDF or Word.'
        : 'In your real account this is written from your book\u2019s details, and you can edit it, regenerate it, and download it as PDF or Word.\n\n'
          + 'The demo skips the actual generation to keep costs bounded.',
      footNote: 'Sign up to create this for your own book.',
    });
    // The modal has already explained it; returning success:false made the app
    // toast a second, blunter message on top ('Demo mode — generation skipped').
    const resp = { success: true, quota: { remaining: 99, limit: 100 } };
    if (example) resp[spec.key] = spec.asArray ? [example] : example;
    return resp;
  }

  // ── AI text generation: return a pre-baked example + show modal ──
  if (url === '/ai_draft.php' || url === '/ai_draft2.php' || url === '/ai_gateway.php') {
    let body = {};
    try { body = options && options.body ? JSON.parse(options.body) : {}; } catch (e) {}
    const task = body.task || 'custom';
    const example = _demoExamples[task] || _demoExamples.custom;

    // The graphics pages have TWO generate buttons — copy first, image second —
    // and the copy one is what people click. Show the finished graphic here too,
    // captioned so it's clear it comes from the NEXT button, not this one.
    // Without this, clicking Generate on a graphics page looked like nothing
    // happened compared with the image button.
    let gfxExample = null;
    if (task === 'graphics_copy') {
      const view = (document.querySelector('.view.active') || {}).id || '';
      const map = {
        'view-gv-social': { img: 'assets/demo/examples/social-graphic.png',
                            cap: 'What you end up with after Generate Image — a real social graphic for The Lighthouse Letters' },
        'view-gv-quote':  { img: 'assets/demo/examples/quote-card.png',
                            cap: 'What you end up with after Generate Background — a real quote card for The Lighthouse Letters' },
        'view-gv-event':  { img: 'assets/demo/examples/event-flyer.png',
                            cap: 'What you end up with after Generate Image — a real event flyer for The Lighthouse Letters' }
      };
      gfxExample = map[view] || null;
    }
    showDemoModal({
      exampleImage: gfxExample && gfxExample.img,
      exampleCaption: gfxExample && gfxExample.cap,
      type: 'ai',
      eyebrow: 'AI demo preview',
      title: _demoTaskTitles[task] || 'AI draft',
      body: example,
      footNote: 'In your real account, the AI tunes drafts to your book’s metadata, genre, and themes.',
    });
    return { success: true, draft: example, quota: { remaining: 99, limit: 100 } };
  }

  // ── AI image generation ──
  // Real generations from each tool, supplied by Bob from his own account.
  // Missing files simply don't render — the modal hides the figure on error —
  // so this is safe to ship before every example exists.
  const _demoImageExamples = {
    // The finished Lighthouse Letters cover. Captioned as the DESTINATION, not
    // as this tool's output — the concept tool makes rough inspiration art, and
    // labelling a type-set cover as its result would oversell it badly.
    cover:  { img: 'assets/demo/lighthouse_letters_cover.png',
              cap: 'The finished cover for The Lighthouse Letters — the concept tool gives you a starting image to work toward a design like this' },
    social: { img: 'assets/demo/examples/social-graphic.png',
              cap: 'An actual social graphic generated for The Lighthouse Letters' },
    quote:  { img: 'assets/demo/examples/quote-card.png',
              cap: 'An actual quote card generated for The Lighthouse Letters' },
    event:  { img: 'assets/demo/examples/event-flyer.png',
              cap: 'An actual event flyer generated for The Lighthouse Letters' }
  };

  if (url === '/image_gen.php') {
    // ?action=status is a plain read — loadImageQuota() calls it just to show
    // the remaining allowance, so opening Graphics & Video fired a "generation
    // skipped" modal before the user had asked for anything. Same trap as
    // sender.php?action=status. Reads fall through; only real generation warns.
    const act = ((endpoint.split('action=')[1] || '').split('&')[0]) || '';
    if (act === 'status') return null;
    let feature = '';
    try { feature = (JSON.parse((options && options.body) || '{}').feature) || ''; } catch (e) {}
    const ex = _demoImageExamples[feature];
    showDemoModal({
      exampleImage: ex && ex.img,
      exampleCaption: ex && ex.cap,
      type: 'ai',
      eyebrow: 'AI demo preview',
      title: 'AI image generation',
      body: 'In your real account, this generates a custom AI image based on your prompt and book — backdrops, social cards, quote graphics, and trailer scenes.\n\n'
          + 'When it’s ready, one click hands it off for posting — caption, hashtags, and download prepped — to platforms like '
          + _demoPlatformSample('image') + ', and more.\n\nThe demo skips the actual generation to keep usage costs bounded.',
      footNote: 'Sign up to start generating images for your book.',
    });
    return { success: false, message: 'Demo mode — image generation skipped' };
  }

  // ── Trailer rendering (Shotstack — costs real money) ──
  if (url === '/video_render.php') {
    showDemoModal({
      exampleVideo: 'assets/demo/examples/trailer-video.mp4',
      examplePoster: 'assets/demo/examples/trailer-poster.jpg',
      exampleCaption: 'A real book trailer made with this tool for The Lighthouse Letters — press play',
      type: 'ai',
      eyebrow: 'AI demo preview',
      title: 'Book trailer rendering',
      body: 'In your real account, the trailer renderer assembles a polished 30-60 second video from your cover, blurb, and a few choices — with AI backdrops, optional narration, music, and your tagline.\n\n'
          + 'Finished trailers post straight to platforms like ' + _demoPlatformSample('video')
          + ', and more — captions and hashtags included.\n\nA real render takes 1-3 minutes. The demo skips it to keep costs bounded.',
      footNote: 'Sign up to render real trailers.',
    });
    // The page prints this message under the wizard, so it must read as an
    // explanation rather than a fault — the modal has already shown a real
    // example. 'Render submission failed' looked like the app was broken.
    return { success: false, message: 'Rendering is switched off in the demo. The example above is a real trailer made with this tool — sign up to render your own.' };
  }

  // ── Slideshow rendering (Shotstack — costs real money) ──
  if (url === '/slideshow_render.php') {
    showDemoModal({
      exampleVideo: 'assets/demo/examples/slideshow-video.mp4',
      examplePoster: 'assets/demo/examples/slideshow-poster.jpg',
      exampleCaption: 'A real slideshow video made with this tool for The Lighthouse Letters — press play',
      type: 'ai',
      eyebrow: 'AI demo preview',
      title: 'Slideshow video rendering',
      body: 'In your real account, this turns your slides into a finished video — crossfades, music (mood library or your own upload), and narration (an AI voice, per-slide storyboard lines, or your own recording).\n\nOne render works everywhere: feed shape for Facebook, Instagram, and LinkedIn, or vertical for Reels and TikTok.\n\nA real render takes about a minute. The demo skips it to keep costs bounded.',
      footNote: 'Sign up to render real slideshow videos.',
    });
    return { success: false, message: 'Rendering is switched off in the demo. The example above is a real video made with this tool — sign up to render your own.' };
  }

  // ── Slideshow storyboard (AI call) ──
  if (url === '/slideshow_storyboard.php') {
    showDemoModal({
      type: 'ai',
      eyebrow: 'AI demo preview',
      title: 'AI slideshow storyboard',
      body: 'In your real account, you describe a marketing idea in one sentence and the AI plans the whole slideshow — the text on every slide plus a narration line spoken while that slide is up, all matched and fully editable.\n\nThe demo skips the generation to keep costs bounded.',
      footNote: 'Sign up to plan real slideshow videos.',
    });
    return { success: false, message: 'Demo mode — storyboard skipped' };
  }

  // ── Slideshow AI scene images (costs real money) ──
  if (url === '/slideshow_images.php') {
    showDemoModal({
      type: 'ai',
      eyebrow: 'AI demo preview',
      title: 'AI slide images',
      body: 'In your real account, the app creates a unique, professional scene image for every slide of your storyboard — one continuous visual story matched to your theme, optionally matching the style of reference images you provide, with your slide text overlaid crisply on top.\n\nThe demo skips the generation to keep costs bounded.',
      footNote: 'Sign up to create real slide images.',
    });
    return { success: false, message: 'Demo mode — image generation skipped' };
  }

  // ── Social posting (would actually publish to TikTok/Meta/etc.) ──
  if (url === '/post.php') {
    // Read actions are silent pass-throughs with safe demo defaults.
    // Only intercept actual write actions (publish/schedule/etc.) with the modal.
    const m = (endpoint || '').match(/[?&]action=([^&]+)/);
    const action = m ? decodeURIComponent(m[1]) : '';
    if (action === 'status') {
      return { success: true, connected: { meta: false, bluesky: false, linkedin: false, tiktok: false } };
    }
    if (action === 'queue') {
      return { success: true, queue: [] };
    }
    let body = {};
    try { body = options && options.body ? JSON.parse(options.body) : {}; } catch (e) {}
    const platforms = (body.platforms && body.platforms.length) ? body.platforms.join(', ') : 'connected platforms';
    showDemoModal({
      type: 'warn',
      eyebrow: 'Demo mode',
      title: 'In your real account, this would publish',
      body: 'Your post would go live on: ' + platforms + '.\n\nThe demo never publishes to real platforms — sign up to start posting from the toolkit.',
      footNote: 'Your real account never posts without your explicit click.',
    });
    return { success: false, message: 'Demo mode — nothing was published' };
  }

  // ── Email send (would send real campaigns to real subscribers) ──
  // sender.php is the Mailgun CONFIGURATION endpoint, not a send endpoint, and
  // ?action=status is a plain read the Email Campaigns page makes on load. It
  // was caught in this list wholesale, so merely opening that page fired a
  // "this would send" warning at the user. Reads fall through; setup actions
  // get their own explanation; only the send endpoints get the send warning.
  if (url === '/sender.php') {
    const act = ((endpoint.split('action=')[1] || '').split('&')[0]) || '';
    if (act === 'status' || act === '') return null;   // read — let it through
    showDemoModal({
      type: 'ai',
      eyebrow: 'Demo mode',
      title: 'Email setup',
      body: 'Connecting a real sending service is part of your own account — the demo shares one '
          + 'set of settings with every visitor, so it can\u2019t be reconfigured here.\n\n'
          + 'In your account you connect Mailgun once, verify your domain, and then campaigns send from your own address.',
      footNote: 'Sign up to connect your own sending address.',
    });
    return { success: false, message: 'Not available in demo mode' };
  }

  if (url === '/send_campaigns.php' || url === '/send_campaigns2.php' || url === '/send_engine.php' || url === '/send_engine2.php') {
    showDemoModal({
      type: 'warn',
      eyebrow: 'Demo mode',
      title: 'In your real account, this would send',
      body: 'Your email campaign would go to your real subscriber list (or a test address you specify).\n\nThe demo never sends — sign up to start emailing your readers.',
      footNote: 'Your real account never sends without your explicit click.',
    });
    return { success: false, message: 'Demo mode — nothing was sent' };
  }

  // ── Platform chooser (post composer + "Post this →" buttons) ──
  // The demo account has no saved profile URLs or API tokens, so the real
  // endpoint returns an empty list and the composer looks broken. Serve a
  // canned set of popular platforms instead — manual-handoff only, so the
  // post.php / manual_post.php intercepts keep anything from publishing.
  if (url === '/connections.php' && (endpoint || '').includes('enabled_only=1')) {
    const P = (id, slug, name, color, img, vid, txt) => ({
      id, slug, name, brand_color: color, description: '',
      url_label: '', url_placeholder: '',
      supports_image: img, supports_video: vid, supports_text: txt,
      intent_url_template: null, api_slug: null, quality_rank: id,
      profile_url: _platformHomeUrl(slug), autoposts: false,
    });
    const all = [
      P(1, 'instagram', 'Instagram',       '#E4405F', 1, 1, 0),
      P(2, 'reels',     'Instagram Reels', '#E4405F', 0, 1, 0),
      P(3, 'tiktok',    'TikTok',          '#000000', 1, 1, 0),
      P(4, 'facebook',  'Facebook',        '#1877F2', 1, 1, 1),
      P(5, 'threads',   'Threads',         '#000000', 1, 1, 1),
      P(6, 'bluesky',   'Bluesky',         '#0285FF', 1, 1, 1),
      P(7, 'pinterest', 'Pinterest',       '#E60023', 1, 0, 0),
      P(8, 'linkedin',  'LinkedIn',        '#0A66C2', 1, 1, 1),
    ];
    const m   = (endpoint || '').match(/[?&]for=(text|image|video)/);
    const key = m ? { text: 'supports_text', image: 'supports_image', video: 'supports_video' }[m[1]] : null;
    return { success: true, platforms: key ? all.filter(p => p[key] === 1) : all };
  }

  // ── Mark-as-posted (would write a manual_posts row for the demo user) ──
  if (url === '/manual_post.php') {
    return { success: true, id: 0, message: 'Demo mode — not recorded' };
  }

  return null;  // not intercepted; let it through
}

function showDemoModal(opts) {
  // Callers override the primary button (e.g. the book-limit modal swaps it for
  // "Upgrade to Pro"). Those overrides are set directly on the shared DOM node,
  // so without resetting here the last override would persist into every later
  // modal. Restore both buttons to their defaults on each open.
  (function resetDemoModalButtons() {
    const primary   = document.querySelector('#demo-modal-overlay .demo-cta:not(.secondary)');
    const secondary = document.querySelector('#demo-modal-overlay .demo-cta.secondary');
    if (primary) {
      primary.textContent = 'Get in touch';
      primary.onclick = () => { closeDemoModal(); contactUs(); };
    }
    if (secondary) {
      secondary.textContent = 'Close';
      secondary.onclick = () => { closeDemoModal(); };
    }
  })();

  const overlay = document.getElementById('demo-modal-overlay');
  const icon    = document.getElementById('demo-modal-icon');
  const eyebrow = document.getElementById('demo-modal-eyebrow');
  const title   = document.getElementById('demo-modal-title');
  const bodyEl  = document.getElementById('demo-modal-body');
  const footN   = document.getElementById('demo-modal-foot-note');
  if (!overlay) return;

  icon.className = 'demo-modal-icon ' + (opts.type || '');
  icon.textContent = opts.type === 'warn' ? '🛡' : (opts.type === 'ai' ? '✨' : '🎬');
  eyebrow.textContent = opts.eyebrow || 'Demo preview';
  title.textContent   = opts.title   || 'Demo';
  bodyEl.textContent  = opts.body    || '';
  bodyEl.className    = 'demo-modal-body' + (opts.serif ? ' serif' : '');
  footN.textContent   = opts.footNote || 'This is a demo — sign up to use this feature for real.';

  // Optional example image beneath the explanation. Used by the image tools,
  // where showing output beats describing it. The caption must say what the
  // image actually IS — a finished cover is the destination, not this tool's
  // output, and mislabelling it would oversell what the app produces.
  var exWrap = document.getElementById('demo-modal-example-wrap');
  var exImg  = document.getElementById('demo-modal-example');
  var exVid  = document.getElementById('demo-modal-example-video');
  var exCap  = document.getElementById('demo-modal-example-cap');
  var hasExampleVideo = false;

  // Video example. preload="none" plus a poster means nothing downloads until
  // the visitor presses play — the source trailer is 26MB, so loading it for
  // everyone who clicks Generate would be rude.
  if (exVid) {
    if (opts.exampleVideo) {
      exVid.src = opts.exampleVideo;
      if (opts.examplePoster) exVid.poster = opts.examplePoster;
      exVid.style.display = '';
      if (exImg) { exImg.style.display = 'none'; exImg.removeAttribute('src'); }
      if (exWrap) exWrap.style.display = '';
      if (exCap) exCap.textContent = opts.exampleCaption || '';
      // Deliberately NOT returning here. overlay.classList.add('visible') is
      // further down this function — an early return populated the modal
      // perfectly and then never showed it, so Generate looked like it did
      // nothing at all.
      hasExampleVideo = true;
    } else {
      // Only tear the video down when this modal ISN'T showing one. This used
      // to sit after an early `return`; removing the return left it running
      // unconditionally, so the video was set and then immediately cleared.
      exVid.pause && exVid.pause();
      exVid.style.display = 'none';
      exVid.removeAttribute('src');
      if (exImg) exImg.style.display = '';
    }
  }

  if (!hasExampleVideo && exWrap && exImg) {
    if (opts.exampleImage) {
      exImg.onerror = function () { exWrap.style.display = 'none'; };
      exImg.onload  = function () { exWrap.style.display = ''; };
      exImg.src = opts.exampleImage;
      exImg.alt = opts.exampleCaption || 'Example';
      if (exCap) exCap.textContent = opts.exampleCaption || '';
    } else {
      exWrap.style.display = 'none';
      exImg.removeAttribute('src');
      if (exCap) exCap.textContent = '';
    }
  }


  overlay.classList.add('visible');
  document.body.style.overflow = 'hidden';
}
function closeDemoModal() {
  const overlay = document.getElementById('demo-modal-overlay');
  if (overlay) overlay.classList.remove('visible');
  document.body.style.overflow = '';
}

// Helper to toggle the demo banner based on currentUser
function applyDemoMode() {
  const banner = document.getElementById('demo-banner');
  if (!banner) return;
  const isDemo = !!(currentUser && (currentUser.is_demo == 1 || currentUser.is_demo === true));
  banner.classList.toggle('visible', isDemo);
  document.body.classList.toggle('demo-mode', isDemo);
}

async function api(endpoint, options = {}) {
  // Demo-mode interception (frontend gate; backend isn't aware)
  if (currentUser && (currentUser.is_demo == 1 || currentUser.is_demo === true)) {
    const mock = _demoIntercept(endpoint, options);
    if (mock !== null && mock !== undefined) return mock;
  }

  const headers = { 'Content-Type': 'application/json' };
  if (authToken) headers['X-Auth-Token'] = authToken;
  const res = await fetch(API + endpoint, { ...options, headers });
  const data = await res.json();
  // A 'subscription_required' refusal used to bounce the user to the billing
  // page. There is no billing page now — the caller's own toast is the whole
  // response, and enquiries are handled on the landing page.
  return data;
}

// ── SESSION ───────────────────────────────────────────────────
// The sign-in / sign-up / forgot-password / reset-password flows were
// removed along with the landing-page auth panel. What remains restores
// an existing session for the app screen and the standalone eBook tool.
async function loadUser() {
  const data = await api('/auth.php?action=me');
  if (data.success) {
    // ⚠ A GUEST IS NOT A USER OF THIS APP. An eBook buyer has bought one file;
    // they have no plan, no books and never signed up. Letting their token in
    // here put a stranger on the dashboard of a product they had not joined —
    // and stranded Bob as "Guest" after a test purchase. Also cleans up tokens
    // already stored under the old shared key.
    if (data.user && data.user.is_guest && !window.AH_STANDALONE) {
      authToken = '';
      localStorage.removeItem('auth_token');
      return;
    }
    currentUser = data.user;
    platformStatus = {
      meta:     !!(data.connected && data.connected.meta),
      tiktok:   !!(data.connected && data.connected.tiktok),
      bluesky:  !!(data.connected && data.connected.bluesky),
      linkedin: !!(data.connected && data.connected.linkedin),
    };
    onLogin();
  } else {
    authToken = '';
    localStorage.removeItem('auth_token');
  }
}

async function doLogout() {
  await api('/auth.php?action=logout', { method: 'POST' });
  authToken = '';
  localStorage.removeItem('auth_token');
  // Drop Sophie's client-side session pointers before reload (server history
  // is already per-user; this is just the local mirror).
  try {
    localStorage.removeItem('chat_session_id');
    localStorage.removeItem('chat_session_last_active');
    // Walkthrough progress lives in sessionStorage (walkthrough.js, key
    // 'wt_state'), which the reload below does NOT clear — sessionStorage
    // survives a same-tab reload. Without this, the next account signed in on
    // this tab inherits the previous user's half-finished walkthrough and its
    // "Resume walkthrough" pill. Same cross-account leak the reload guards
    // against; it just wasn't in localStorage, so it slipped through.
    sessionStorage.removeItem('wt_state');
  } catch (e) {}
  // Logout is a security boundary. This is a single-page app, so without a
  // reload every view's DOM and in-memory state (generated graphics, book
  // forms, post drafts, email composer, admin tables, Sophie's transcript)
  // would persist into the NEXT account signed in on this browser — a real
  // cross-account data leak (admin's generated graphic showed up in a fresh
  // tester account, 2026-06-12). A hard reload to a clean URL wipes ALL of
  // it at once and can never miss a newly-added view. Caching keeps the
  // landing page fast; this is a once-per-logout cost.
  window.location.replace('/');
}

// ── ON LOGIN ──────────────────────────────────────────────────
function onLogin() {
  showApp();

  // ⚠ A GUEST IS NOT A MEMBER — stop here.
  // Everything below is the app: it navigates to the dashboard and fans out to
  // books, quota, game plan, Shopify, Woo, the post queue and progress. A guest
  // has none of those, the server now refuses them all, and the navigate() is
  // what put a returning eBook buyer on a dashboard they never signed up for.
  // ebookStandaloneBoot() takes it from here.
  if (currentUser && currentUser.is_guest) { return; }

  // Force a known starting view. Without this, whatever view was active when
  // the previous user signed out (e.g. an admin's Users view) stays active —
  // the new account would land in that view with the previous user's rendered
  // content still in the DOM.
  navigate('dashboard');
  // If we just returned from the WordPress one-click connect, land on the
  // Website view with the result — this runs after the default route so it wins.
  if (window.__wpHandleConnectReturn) window.__wpHandleConnectReturn();
  const name = currentUser.pen_name || currentUser.full_name || 'Author';
  document.getElementById('topbar-name').textContent = name;
  document.getElementById('dash-welcome').textContent =
    'Welcome back, ' + name.split(' ')[0] + ' — here\'s how your books are performing';
  document.getElementById('acc-name').value  = currentUser.full_name || '';
  document.getElementById('acc-pen').value   = currentUser.pen_name  || '';
  document.getElementById('acc-email').value = currentUser.email     || '';
  const optOutCb = document.getElementById('acc-email-optout');
  if (optOutCb) optOutCb.checked = !!(currentUser.system_email_opt_out == 1 || currentUser.system_email_opt_out === true);
  document.getElementById('acc-since').textContent = currentUser.created_at
    ? new Date(currentUser.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : '—';
  updateConnectionUI();
  loadPostQueue();
  loadShopifyStatus();
  loadWooStatus();
  showChatFab();
  loadBooks().then(maybeShowWelcomeForNewUser);
  loadQuotaMeter();
  loadProgressGrid();
  loadGamePlan();
  const adminNav = document.getElementById('admin-nav-section');
  if (adminNav) adminNav.style.display = currentUser.is_admin ? 'block' : 'none';
  updateSubscriptionUI();
  applyDemoMode();

  const urlParams = new URLSearchParams(window.location.search);

  // Handle return from Shopify OAuth callback
  if (urlParams.get('shopify') === 'connected') {
    toast('Shopify store connected.');
    window.history.replaceState({}, '', window.location.pathname + window.location.hash);
    // Sales view's hash will already land them there; refresh the status row.
    setTimeout(loadShopifyStatus, 200);
  }
}

// ── SUBSCRIPTION UI ───────────────────────────────────────────

// Read-only plan summary on the Account page. All billing controls — the
// checkout buttons, the Stripe portal, cancel/reactivate — were removed
// along with the pricing view; what a plan currently IS still shows, but
// nothing here changes it. Plan changes go through an enquiry.
function updateSubscriptionUI() {
  const u = currentUser;
  if (!u) return;

  const planName    = document.getElementById('acc-plan-name');
  const planStatus  = document.getElementById('acc-plan-status');
  const planDateRow = document.getElementById('acc-plan-date-row');
  const planDate    = document.getElementById('acc-plan-date');
  const planDateLbl = document.getElementById('acc-plan-date-label');

  if (u.is_admin) {
    if (planName)   planName.textContent   = 'Admin';
    if (planStatus) planStatus.textContent = 'Unlimited access';
    return;
  }

  const status    = u.subscription_status || '';
  const trialEnd  = u.trial_ends_at      ? new Date(u.trial_ends_at)      : null;
  const periodEnd = u.current_period_end ? new Date(u.current_period_end) : null;
  const planLabel = u.plan ? (u.plan.charAt(0).toUpperCase() + u.plan.slice(1)) : 'Free';
  const scheduled = !!(+u.cancel_at_period_end);

  if (planName) planName.textContent = planLabel;

  const fmt = d => d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const showDate = (label, d) => {
    if (!d || !planDateRow) return;
    planDateRow.style.display = 'flex';
    planDateLbl.textContent = label;
    planDate.textContent = fmt(d);
  };

  if (status === 'trialing') {
    const daysLeft = trialEnd ? Math.max(0, Math.ceil((trialEnd - Date.now()) / 86400000)) : null;
    // A trial ending months out is really deferred billing on an annual plan,
    // not a trial — a paying customer must never see trial wording.
    if (daysLeft === null || daysLeft > 60) {
      if (planStatus) planStatus.textContent = scheduled ? 'Active — ending soon' : 'Active';
      showDate(scheduled ? 'Access ends' : 'Renews', trialEnd || periodEnd);
    } else {
      if (planStatus) planStatus.textContent = 'Trial — ' + daysLeft + ' day' + (daysLeft !== 1 ? 's' : '') + ' left';
      showDate('Trial ends', trialEnd);
    }

  } else if (status === 'active') {
    if (planStatus) planStatus.textContent = scheduled ? 'Active — ending soon' : 'Active';
    showDate(scheduled ? 'Access ends' : 'Renews', periodEnd);

  } else if (status === 'past_due') {
    if (planStatus) planStatus.textContent = 'Payment overdue';

  } else if (status === 'canceled') {
    if (planStatus) planStatus.textContent = 'Cancelled';

  } else {
    if (planStatus) planStatus.textContent = 'No active subscription';
  }
}

// ── ENQUIRIES ─────────────────────────────────────────────────

// Every CTA that used to open checkout or the pricing view now lands here:
// back on the landing page, at the enquiry form. scrollToEnquiry() is
// defined by the enquiry script in index.html.
function contactUs() {
  showLanding();
  if (typeof scrollToEnquiry === 'function') {
    setTimeout(scrollToEnquiry, 60);
  }
}

// ── CONNECTIONS ───────────────────────────────────────────────
// Slugs the user has saved a profile URL for (manual-handoff setup).
// Populated by refreshStatus; consumed by updateConnectionUI so the
// dashboard panel gives credit for handoff setups, not just API tokens —
// previously a user could paste four URLs in Connections and the dashboard
// still said "Not connected" everywhere.
let _handoffSetupSlugs = {};

async function refreshStatus() {
  const data = await api('/post.php?action=status');
  if (data.success) { platformStatus = data.connected; }
  try {
    const c = await api('/connections.php?action=list');
    _handoffSetupSlugs = {};
    if (c && c.success) {
      (c.platforms || []).forEach(p => { if (p.profile_url) _handoffSetupSlugs[p.slug] = true; });
    }
  } catch (e) { /* panel falls back to API-only state */ }
  if (data.success) updateConnectionUI();
}

function updateConnectionUI() {
  const { meta, tiktok, bluesky, linkedin, pinterest } = platformStatus;
  const u = _handoffSetupSlugs;
  const anyApi = meta || tiktok || bluesky || linkedin || pinterest;
  const any    = anyApi || Object.keys(u).length > 0;
  setBadge('conn-facebook',  meta,      u.facebook);
  setBadge('conn-instagram', meta,      u.instagram || u.reels);
  setBadge('conn-bluesky',   bluesky,   u.bluesky);
  setBadge('conn-linkedin',  linkedin,  u.linkedin);
  setBadge('conn-tiktok',    tiktok,    u.tiktok);
  setBadge('conn-pinterest', pinterest, u.pinterest);
  setConnBadge('meta', meta);
  setConnBadge('bluesky', bluesky);
  setConnBadge('linkedin', linkedin);
  setConnBadge('tiktok', tiktok);
  setConnBadge('pinterest', pinterest);
  document.getElementById('nav-social-status').className = 'platform-status ' + (any ? 'on' : 'off');
  document.getElementById('connect-prompt').style.display = any ? 'none' : 'flex';
  document.getElementById('social-connect-warn').style.display = any ? 'none' : 'flex';
}

// Three states: API-connected (green "AutoPost on"), profile URL saved
// (blue "Linked"), nothing (gray "Not connected").
function setBadge(id, connected, linked) {
  const el = document.getElementById(id);
  if (!el) return;
  if (connected)   { el.textContent = 'AutoPost on';   el.className = 'badge badge-green'; }
  else if (linked) { el.textContent = 'Linked';        el.className = 'badge badge-blue';  }
  else             { el.textContent = 'Not connected'; el.className = 'badge badge-gray';  }
}

function setConnBadge(platform, connected) {
  const badge = document.getElementById('conn-badge-' + platform);
  const btn   = document.getElementById('conn-btn-'   + platform);
  if (!badge || !btn) return;
  badge.textContent = connected ? 'Connected' : 'Not connected';
  badge.className = 'badge ' + (connected ? 'badge-green' : 'badge-gray');
  btn.textContent = connected ? 'Reconnect' : 'Connect';
}

function connectPlatform(platform) {
  if (platform === 'bluesky') { toggleBlueskyForm(); return; }
  var token = encodeURIComponent(authToken);
  window.location.href = API + '/callback_' + platform + '.php?action=connect&token=' + token;
}

// Bluesky uses an app-password form, not OAuth. The form lives directly under
// the Connections row and toggles open on Connect/Reconnect.
function toggleBlueskyForm() {
  var f = document.getElementById('bsky-form');
  if (!f) return;
  var open = f.style.display !== 'none';
  f.style.display = open ? 'none' : 'block';
  if (!open) {
    var h = document.getElementById('bsky-handle');
    if (h && !h.value) h.focus();
  }
}

async function connectBluesky() {
  var handle = (document.getElementById('bsky-handle').value || '').trim();
  var pw     = (document.getElementById('bsky-pw').value     || '').trim();
  if (!handle || !pw) { toast('Handle and app password are required', true); return; }

  var btn = document.getElementById('bsky-save-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Connecting…'; }

  try {
    var res = await api('/connect_bluesky.php', {
      method: 'POST',
      body: JSON.stringify({ handle: handle, app_password: pw }),
    });
    if (res.success) {
      toast('Connected to Bluesky as @' + (res.handle || handle));
      document.getElementById('bsky-pw').value = '';
      document.getElementById('bsky-form').style.display = 'none';
      refreshStatus();
    } else {
      toast(res.message || 'Failed to connect to Bluesky', true);
    }
  } catch (e) {
    toast('Request failed — check your connection', true);
  }

  if (btn) { btn.disabled = false; btn.textContent = 'Connect'; }
}

// ── CONNECTIONS HUB (v51) ─────────────────────────────────────
// Reads the platforms catalog + the user's saved profile URLs from
// /api/connections.php and renders the ranked card list. The legacy
// refreshStatus() still runs alongside to keep platformStatus populated
// for the social-posting composer.
async function loadConnectionsHub() {
  const list = document.getElementById('conn-list');
  if (!list) return;
  list.innerHTML = '<div class="empty">Loading platforms…</div>';
  try {
    const data = await api('/connections.php?action=list');
    if (!data || !data.success) {
      list.innerHTML = '<div class="empty">Couldn\'t load platforms. Try refreshing.</div>';
      return;
    }
    renderConnectionsHub(data.platforms || []);
  } catch (e) {
    list.innerHTML = '<div class="empty">Couldn\'t load platforms. Try refreshing.</div>';
  }
}

function renderConnectionsHub(platforms) {
  const list = document.getElementById('conn-list');
  if (!list) return;
  if (!platforms.length) { list.innerHTML = '<div class="empty">No platforms configured.</div>'; return; }

  list.innerHTML = platforms.map(p => {
    const safeColor = escapeHtml(p.brand_color || '#888');
    const safeName  = escapeHtml(p.name);
    const safeDesc  = escapeHtml(p.description || '');
    const safeUrl   = escapeHtml(p.profile_url || '');
    const safeLabel = escapeHtml(p.url_label || (p.name + ' profile URL'));
    const safePh    = escapeHtml(p.url_placeholder || '');

    const autopostBadge = p.autoposts
      ? '<span class="badge badge-green" title="Posts directly via API">AutoPost</span>'
      : (p.api_slug ? '<span class="badge badge-gray" title="One-click posting available — click Connect API">AutoPost available</span>' : '');

    let connectBtn = '';
    if (p.api_slug) {
      // Only show Setup help when a topic actually exists in setupHelpTopics.
      if (typeof setupHelpTopics !== 'undefined' && setupHelpTopics[p.api_slug]) {
        connectBtn += '<button class="app-btn-help" onclick="showSetupHelp(\'' + p.api_slug + '\')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button>';
      }
      if (!p.autoposts) {
        const onclick = p.api_slug === 'bluesky' ? 'toggleBlueskyForm()' : "connectPlatform('" + p.api_slug + "')";
        connectBtn += '<button class="app-btn app-btn-outline app-btn-sm" onclick="' + onclick + '">Connect API</button>';
      }
    }

    return '' +
      '<div class="conn-row" data-platform-id="' + p.id + '">' +
        '<div class="conn-row-head">' +
          '<span class="pdot" style="background:' + safeColor + '"></span>' +
          '<div class="conn-row-name">' + safeName + '</div>' +
          autopostBadge +
        '</div>' +
        '<p class="conn-row-desc">' + safeDesc + '</p>' +
        '<div class="conn-row-url">' +
          '<label class="field-label">' + safeLabel + '</label>' +
          '<div class="conn-row-url-input-wrap">' +
            '<input type="url" id="conn-url-' + p.id + '" placeholder="' + safePh + '" value="' + safeUrl + '"' +
              ' onchange="saveConnUrl(' + p.id + ')">' +
            '<button class="app-btn app-btn-outline app-btn-sm" onclick="saveConnUrl(' + p.id + ')">Save</button>' +
            connectBtn +
          '</div>' +
        '</div>' +
      '</div>';
  }).join('');
}

async function saveConnUrl(platformId) {
  const input = document.getElementById('conn-url-' + platformId);
  if (!input) return;
  const url = (input.value || '').trim();
  try {
    const data = await api('/connections.php?action=save_url', {
      method: 'POST',
      body: JSON.stringify({ platform_id: platformId, profile_url: url }),
    });
    if (data && data.success) toast('Saved');
    else toast((data && data.message) || 'Could not save', true);
  } catch (e) {
    toast('Request failed', true);
  }
}

// ── DYNAMIC PLATFORM CHOOSER for the post composer (v52) ──────
// Drives the "Publish to" grid from the user's enabled platforms,
// filtered to those that fit the current content type (text-only
// vs image). API-direct platforms get a small "Auto" badge so the
// author knows they post without a copy/paste step. The cached
// _postPlatforms list is what submitPost() reads to split AutoPost
// vs manual handoff.
let _postPlatforms = [];

async function loadPostComposerPlatforms() {
  const grid = document.getElementById('post-platform-grid');
  if (!grid) return;

  const imageUrl    = (document.getElementById('post-image')?.value || '').trim();
  const contentType = imageUrl ? (_spIsVideo(imageUrl) ? 'video' : 'image') : 'text';

  grid.innerHTML = '<div class="empty" style="grid-column:1/-1;font-size:12.5px;color:var(--ink-soft);padding:8px">Loading platforms…</div>';

  try {
    const data = await api('/connections.php?action=list&for=' + contentType + '&enabled_only=1');
    if (!data || !data.success) {
      grid.innerHTML = '<div class="empty" style="grid-column:1/-1;font-size:12.5px;color:var(--ink-soft);padding:8px">Couldn\'t load platforms.</div>';
      return;
    }
    _postPlatforms = data.platforms || [];
    renderPostPlatforms(_postPlatforms);
  } catch (e) {
    grid.innerHTML = '<div class="empty" style="grid-column:1/-1;font-size:12.5px;color:var(--ink-soft);padding:8px">Couldn\'t load platforms.</div>';
  }
}

function renderPostPlatforms(platforms) {
  const grid = document.getElementById('post-platform-grid');
  if (!grid) return;
  if (!platforms.length) {
    grid.innerHTML =
      '<div class="empty" style="grid-column:1/-1;font-size:12.5px;color:var(--ink-soft);padding:10px;line-height:1.5">' +
        'No platforms set up yet for this kind of post. Open <a href="#" onclick="navigate(\'connections\');return false" style="color:var(--accent)">Connections</a> and paste your profile URLs.' +
      '</div>';
    return;
  }
  grid.innerHTML = platforms.map(p => {
    const color = escapeHtml(p.brand_color || '#888');
    const name  = escapeHtml(p.name);
    const slug  = escapeHtml(p.slug);
    const badge = p.autoposts
      ? ' <span class="badge badge-green" style="font-size:10px;padding:1px 6px;margin-left:auto" title="Posts automatically via API">Auto</span>'
      : '';
    // Instagram preserves its caption-link-not-clickable note toggle.
    const onchange = p.slug === 'instagram'
      ? ' onchange="document.getElementById(\'post-link-instagram-note\').style.display=this.checked?\'block\':\'none\'"'
      : '';
    return '<label class="platform-btn"' +
             ' data-platform-id="' + p.id + '"' +
             ' data-autopost="' + (p.autoposts ? '1' : '0') + '">' +
             '<input type="checkbox" value="' + slug + '" class="platform-check"' + onchange + '>' +
             '<span class="pdot" style="background:' + color + '"></span>' + name + badge +
           '</label>';
  }).join('');
}

// Called from the post-image input's oninput. Reloads the chooser
// because text→image (or image→text) may change which platforms apply.
function onPostImageChanged() {
  loadPostComposerPlatforms();
}

// ── HANDOFF MODAL (v52) ───────────────────────────────────────
// In-app modal with one tab per chosen manual platform. Each tab
// carries the rendered post (caption + image) and the shortest path
// to actually publishing it: copy caption, copy image to clipboard,
// download image with a platform-named filename, open the platform's
// composer (intent URL when one exists). API-direct platforms aren't
// shown here — they post immediately via the existing OAuth flow.
let _handoff = null;   // { caption, linkUrl, imageUrl, platforms, activeIdx, posted, autopostSummary }

function openHandoffModal(opts) {
  // Auto-detect the currently-selected book's genre so hashtag chips can
  // include genre-specific tags. Reads the topbar #bookSelector since the
  // book-selector sync (v53) keeps it aligned with the per-page selectors.
  let bookGenre = opts.bookGenre || '';
  if (!bookGenre) {
    const bookId = parseInt(document.getElementById('bookSelector')?.value || '0');
    if (bookId) {
      const book = (window._books || []).find(b => b.id == bookId);
      bookGenre = book?.genre || '';
    }
  }

  _handoff = {
    caption:           opts.caption || '',         // original caption (the seed)
    captionByPlatform: {},                          // slug → user-edited caption (lazy; defaults to caption)
    linkUrl:           opts.linkUrl || '',
    imageUrl:          opts.imageUrl || '',
    previewSrc:        opts.previewSrc || '',       // already-loaded src for instant preview (falls back to imageUrl)
    videoUrl:          opts.videoUrl || '',
    platforms:         opts.platforms || [],
    activeIdx:         0,
    posted:            new Set(),
    postedIds:         {},                          // slug → manual_posts.id, populated as the user marks each platform
    source:            opts.source || '',
    bookGenre:         bookGenre,
    autopostSummary:   opts.autopostSummary || '',
    autopostFailed:    !!opts.autopostFailed,
    fitModeByPlatform: {},                           // slug → 'blur'|'solid'|'crop' (default blur)
    fittedByPlatform:  {},                           // slug → { mode: url|null } cache of image_fit.php results
    videoAspect:       opts.videoAspect || null,     // 'vertical'|'square'|'landscape' — for the trailer fit warning
  };
  if (!_handoff.platforms.length) return;
  const n = _handoff.platforms.length;
  document.getElementById('handoff-modal-title').textContent =
    'Post to ' + n + ' ' + (n === 1 ? 'platform' : 'platforms');

  // AutoPost banner: visible only when mixed mode actually ran an AutoPost.
  // Green on success, amber on failure.
  const banner = document.getElementById('handoff-autopost-banner');
  if (banner) {
    if (_handoff.autopostSummary) {
      const color   = _handoff.autopostFailed ? 'var(--gold)'    : 'var(--accent)';
      const bgColor = _handoff.autopostFailed ? 'var(--gold-lt)' : 'var(--accent-lt)';
      const mark    = _handoff.autopostFailed ? '!' : '✓';
      banner.style.cssText = 'display:block;background:' + bgColor + ';color:' + color + ';font-weight:600;padding:11px 22px;font-size:13.5px;border-bottom:1px solid ' + color + ';';
      banner.textContent = mark + '  ' + _handoff.autopostSummary;
    } else {
      banner.style.display = 'none';
      banner.textContent = '';
    }
  }

  _renderHandoffTabs();
  _renderHandoffTabBody();
  document.getElementById('handoff-modal-backdrop').style.display = 'flex';
}

function closeHandoffModal() {
  const bd = document.getElementById('handoff-modal-backdrop');
  if (bd) bd.style.display = 'none';
  _handoff = null;
}

function _renderHandoffTabs() {
  const strip = document.getElementById('handoff-tab-strip');
  if (!strip || !_handoff) return;
  strip.innerHTML = _handoff.platforms.map((p, i) => {
    const cls = 'handoff-tab' +
                (i === _handoff.activeIdx ? ' active' : '') +
                (_handoff.posted.has(p.slug) ? ' posted' : '');
    return '<button class="' + cls + '" role="tab" type="button" onclick="_handoffSwitchTab(' + i + ')">' +
             '<span class="pdot" style="background:' + escapeHtml(p.brand_color || '#888') + '"></span>' +
             escapeHtml(p.name) +
           '</button>';
  }).join('');
}

// Per-platform "what to do once you're at the platform" instructions, by
// content type. Each string is plain HTML — <strong>/<em> are styled by
// .handoff-step-detail. Keep them short and concrete: which buttons to click.
const _POST_STEPS = {
  video: {
    instagram: 'Click <strong>+ Create</strong> → drag the MP4 from your Downloads folder into the upload area → paste the caption (Cmd-V / Ctrl-V) → click <strong>Share</strong>. You\'ll get a Reel.',
    reels:     'Reels are posted through <strong>Instagram</strong> — there\'s no separate Reels site, so this opens instagram.com. Click <strong>+ Create</strong> → drag the MP4 from your Downloads folder into the upload area → paste the caption (Cmd-V / Ctrl-V) → click <strong>Share</strong>. Vertical video posts as a Reel automatically.',
    tiktok:    'Click <strong>Upload</strong> → drag the MP4 into the upload area → paste the caption → click <strong>Post</strong>.',
    facebook:  'On your author Page: click <strong>Create post</strong> → click the <strong>Photo/Video</strong> button → pick the MP4 from your Downloads folder → paste the caption → click <strong>Post</strong>.',
    x:         'The caption is already filled in. Click the <strong>image/video icon</strong>, pick the MP4 from Downloads, then click <strong>Post</strong>.',
    threads:   'The caption is already filled in. Click the <strong>attachment icon</strong>, pick the MP4 from Downloads, then click <strong>Post</strong>.',
    linkedin:  'Click <strong>Start a post</strong> → <strong>Add a video</strong> → pick the MP4 → paste the caption → click <strong>Post</strong>.',
    bluesky:   'The caption is already filled in. Click the <strong>image icon</strong>, pick the MP4 (max 50 MB), then click <strong>Post</strong>.',
    reddit:    'Choose the <strong>Image &amp; Video</strong> tab → upload the MP4 → pick a subreddit → click <strong>Post</strong>.',
    discord:   'Pick the server and channel → drag the MP4 into the message box → type or paste your caption → press Enter.',
  },
  image: {
    instagram: 'Click <strong>+ Create</strong> → drag the image into the upload area → paste the caption → click <strong>Share</strong>.',
    facebook:  'On your author Page: click <strong>Create post</strong> → click the <strong>Photo/Video</strong> button → pick the image from Downloads → paste the caption → click <strong>Post</strong>.',
    pinterest: 'Pinterest opens with the image, description, and link already pre-filled. Pick a board, then click <strong>Save</strong>.',
    x:         'The caption is already filled in. Click the <strong>image icon</strong>, pick the image from Downloads, then click <strong>Post</strong>.',
    threads:   'The caption is already filled in. Click the <strong>attachment icon</strong>, pick the image, then click <strong>Post</strong>.',
    linkedin:  'Click <strong>Start a post</strong> → the <strong>camera icon</strong> → pick the image → paste the caption → click <strong>Post</strong>.',
    bluesky:   'The caption is already filled in. Click the <strong>image icon</strong>, pick the image, then click <strong>Post</strong>.',
    reddit:    'Choose the <strong>Image &amp; Video</strong> tab → upload the image → pick a subreddit → click <strong>Post</strong>.',
    discord:   'Pick the server and channel → drag the image into the message box → type or paste your caption → press Enter.',
    tiktok:    'TikTok photo posts work best on the mobile app. AirDrop / iCloud / email the image to your phone, open the TikTok app, tap <strong>+</strong>, choose Photo, then upload.',
  },
  text: {
    facebook:  'On your author Page: click <strong>Create post</strong> → paste the caption → click <strong>Post</strong>.',
    x:         'The caption is already filled in. Just click <strong>Post</strong>.',
    threads:   'The caption is already filled in. Just click <strong>Post</strong>.',
    linkedin:  'Click <strong>Start a post</strong> → paste the caption → click <strong>Post</strong>.',
    bluesky:   'The caption is already filled in. Just click <strong>Post</strong>.',
    reddit:    'The title is already filled in. Pick a subreddit, add any body text you want, then click <strong>Post</strong>.',
    goodreads: 'Sign in to Goodreads → your Author Dashboard → <strong>New Update</strong> → paste the caption → click <strong>Post Update</strong>.',
    bookbub:   'Sign in to BookBub → your Author Profile → <strong>New Author Update</strong> → paste the caption → click <strong>Post</strong>.',
    storygraph:'Sign in to StoryGraph → your profile → start a new update → paste the caption → click <strong>Post</strong>.',
    substack:  'In Substack: click <strong>Notes</strong> → paste the caption → click <strong>Post</strong>. Or use it as a seed for a longer newsletter post.',
    discord:   'Pick the server and channel → type or paste your caption → press Enter.',
    royalroad: 'Sign in to Royal Road → your profile → <strong>Write a Post</strong> → paste the caption → click <strong>Submit</strong>.',
  },
};

// Platform-tuned starter hashtags. Kept short — chips are suggestions, not
// a wall. LinkedIn and Reddit dislike heavy hashtag use, so they get none.
// Book-community-only platforms (Goodreads, BookBub, etc.) skip hashtags too.
const _HASHTAGS_BY_PLATFORM = {
  // #BookTok and #Bookstagram are cross-platform book-community tags —
  // they show on every visual platform because authors tag both for
  // discoverability even when posting outside the originating app.
  tiktok:    ['#BookTok', '#Bookstagram', '#BookTokFinds', '#IndieAuthor', '#AuthorLife', '#NewBook'],
  instagram: ['#Bookstagram', '#BookTok', '#BooksOfInstagram', '#IndieAuthor', '#AmReading', '#NewRelease'],
  reels:     ['#BookTok', '#Bookstagram', '#BookReels', '#IndieAuthor', '#NewBook'],
  facebook:  ['#Bookstagram', '#BookTok', '#IndieAuthor', '#NewBook', '#NowAvailable', '#BookLovers'],
  threads:   ['#Bookstagram', '#BookTok', '#IndieAuthor', '#AmReading'],
  pinterest: ['#BookCover', '#Bookstagram', '#IndieAuthor', '#BookRecommendations'],
  // Text-first platforms — Writing-community tags do more work than book ones.
  x:         ['#WritingCommunity', '#AmWriting', '#BookTwitter', '#IndieAuthor', '#NewRelease'],
  bluesky:   ['#WritingCommunity', '#IndieAuthor', '#NewBook'],
  // Hashtag-light or hashtag-hostile platforms.
  discord:    [],
  linkedin:   [],
  reddit:     [],
  goodreads:  [],
  storygraph: [],
  bookbub:    [],
  substack:   [],
  royalroad:  [],
};

// Genre-specific hashtags layered on top of the platform set. Keys are
// matched against the book's genre field with case-insensitive substring
// matching (handles "Cozy Mystery", "cozy mystery", "Mystery / Thriller",
// etc. with the same entry).
const _HASHTAGS_BY_GENRE = [
  { match: ['cozy mystery'],                     tags: ['#CozyMystery', '#Whodunit', '#SmallTownMystery'] },
  { match: ['mystery', 'thriller', 'suspense'],  tags: ['#MysteryBooks', '#ThrillerBooks', '#CrimeFiction'] },
  { match: ['romance', 'rom-com', 'romcom'],     tags: ['#Romance', '#RomanceNovel', '#BookishRomance'] },
  { match: ['fantasy', 'litrpg'],                tags: ['#FantasyBooks', '#EpicFantasy', '#BookishFantasy'] },
  { match: ['sci-fi', 'scifi', 'science fiction'], tags: ['#SciFiBooks', '#SciFiReads', '#SFFCommunity'] },
  { match: ['ya', 'young adult', 'teen'],        tags: ['#YABooks', '#YALit', '#YoungAdultBooks'] },
  { match: ['historical'],                       tags: ['#HistoricalFiction', '#HistFicReads'] },
  { match: ['literary'],                         tags: ['#LiteraryFiction', '#ContemporaryLit'] },
  { match: ['memoir'],                           tags: ['#Memoir', '#MemoirReads', '#TrueStories'] },
  { match: ['nonfiction', 'non-fiction'],        tags: ['#NonFictionReads', '#NonFictionBooks'] },
  { match: ['self help', 'self-help', 'business', 'productivity'], tags: ['#SelfHelpBooks', '#PersonalGrowth'] },
  { match: ['horror'],                           tags: ['#HorrorBooks', '#HorrorReads'] },
  { match: ['poetry'],                           tags: ['#PoetryCommunity', '#PoetsOfInstagram'] },
];

// Returns the combined platform + genre hashtag list for a platform, with
// duplicates removed (case-insensitive). Empty array is a legitimate result
// for platforms that don't favor hashtags (LinkedIn, Reddit, Goodreads, etc.).
function _handoffHashtagsFor(slug, genre) {
  const platformTags = _HASHTAGS_BY_PLATFORM[slug] || [];
  let genreTags = [];
  if (genre) {
    const g = String(genre).toLowerCase();
    for (const entry of _HASHTAGS_BY_GENRE) {
      if (entry.match.some(m => g.indexOf(m) !== -1)) {
        genreTags = entry.tags;
        break;
      }
    }
  }
  const seen = new Set();
  const out = [];
  for (const t of [...genreTags, ...platformTags]) {     // genre first, more specific
    const k = t.toLowerCase();
    if (seen.has(k)) continue;
    seen.add(k);
    out.push(t);
  }
  return out;
}

// Append a hashtag to the current tab's caption. Adds a space before if the
// caption doesn't already end with whitespace, so chips don't smash into the
// last word of the seed text.
function _handoffInsertHashtag(slug, tag) {
  if (!_handoff) return;
  const cur = _handoffCaptionFor(slug);
  const sep = (cur && !/\s$/.test(cur)) ? ' ' : '';
  _handoff.captionByPlatform[slug] = cur + sep + tag;
  _renderHandoffTabBody();
}

// Returns the caption currently associated with this platform tab.
// Falls back to the modal's original caption (stripped of HTML) when
// the user hasn't edited this platform's textarea yet.
function _handoffCaptionFor(slug) {
  if (!_handoff) return '';
  if (Object.prototype.hasOwnProperty.call(_handoff.captionByPlatform, slug)) {
    return _handoff.captionByPlatform[slug];
  }
  return _handoffPlainText(_handoff.caption);
}

// oninput handler for the caption textarea — stores the user's edit.
function _handoffEditCaption(slug, value) {
  if (!_handoff) return;
  _handoff.captionByPlatform[slug] = value;
}

// Called by the small "Reset" link beneath the textarea.
function _handoffResetCaption(slug) {
  if (!_handoff) return;
  delete _handoff.captionByPlatform[slug];
  _renderHandoffTabBody();
}

function _handoffStepBlock(num, title, detail, actionHtml) {
  return '<div class="handoff-step">' +
           '<div class="handoff-step-num">' + num + '</div>' +
           '<div class="handoff-step-body">' +
             '<div class="handoff-step-title">' + title + '</div>' +
             (actionHtml ? '<div class="handoff-step-action">' + actionHtml + '</div>' : '') +
             '<div class="handoff-step-detail">' + detail + '</div>' +
           '</div>' +
         '</div>';
}

// ── Per-platform image fit (handoff modal) ──────────────────────
// Each manual platform can fit the post image to its ideal pixel
// size (platforms.image_w/image_h) so it isn't cropped on the
// platform. Fitted versions come from image_fit.php and are cached
// per (slug, mode). Default mode is 'blur' (blurred-pad — nothing
// lost). A cached value of null means "fit failed, use original".
function _handoffFitMode(slug) {
  return (_handoff && _handoff.fitModeByPlatform[slug]) || 'blur';
}
function _handoffPlatform(slug) {
  return _handoff ? _handoff.platforms.find(p => p.slug === slug) : null;
}
function _handoffCanFit(p) {
  return !!(_handoff && _handoff.imageUrl && p && p.image_w > 0 && p.image_h > 0);
}
// The image URL to actually use for a platform: fitted if ready,
// else the original — so download / preview / record never break.
function _handoffImageFor(slug) {
  if (!_handoff) return '';
  const store = _handoff.fittedByPlatform[slug];
  const mode  = _handoffFitMode(slug);
  if (store && Object.prototype.hasOwnProperty.call(store, mode) && store[mode]) return store[mode];
  return _handoff.imageUrl;
}
// Fit (slug, current mode) via image_fit.php if not already done /
// in flight, then re-render the tab so the fitted image swaps in.
async function _handoffEnsureFit(slug) {
  if (!_handoff) return;
  const p = _handoffPlatform(slug);
  if (!_handoffCanFit(p)) return;
  const mode  = _handoffFitMode(slug);
  const store = _handoff.fittedByPlatform[slug] = _handoff.fittedByPlatform[slug] || {};
  if (Object.prototype.hasOwnProperty.call(store, mode) || store['_loading_' + mode]) return;
  store['_loading_' + mode] = true;
  try {
    const data = await api('/image_fit.php', {
      method: 'POST',
      body: JSON.stringify({ source: _handoff.imageUrl, platform: slug, mode: mode }),
    });
    store[mode] = (data && data.success && data.url) ? data.url : null;
  } catch (e) {
    store[mode] = null;
  }
  delete store['_loading_' + mode];
  const cur = _handoff.platforms[_handoff.activeIdx];
  if (cur && cur.slug === slug) _renderHandoffTabBody();
}
function _handoffSetFitMode(slug, mode) {
  if (!_handoff) return;
  _handoff.fitModeByPlatform[slug] = mode;
  _renderHandoffTabBody();   // triggers a fit for the new mode if needed
}

// ── Trailer (video) fit warning ─────────────────────────────────
// Video can't be reshaped on this host (no ffmpeg) — its ratio is
// fixed at render time. So at the post step we just WARN when the
// trailer's orientation will be cropped/boxed on the platform, and
// point back to the Format dropdown. Each platform's best
// orientation; 'flexible' never hard-warns.
const _VIDEO_ORIENT = {
  tiktok: 'vertical', threads: 'vertical',
  reels: 'square', instagram: 'square',          // Instagram crops vertical to 1:1 by default — square is the safe target
  x: 'landscape', linkedin: 'landscape',
  facebook: 'flexible', bluesky: 'flexible',
};
// Reads the real video dimensions once metadata loads and re-renders
// so the note reflects the actual file (not just the Format guess).
function _handoffVideoMeta(v) {
  if (!_handoff || !v || !v.videoWidth || !v.videoHeight) return;
  const r = v.videoWidth / v.videoHeight;
  // 4:5 (0.8) counts as square-ish: platforms that crop to square show a
  // 4:5 feed video essentially uncut, so a crop warning would be noise.
  const cat = r < 0.75 ? 'vertical' : (r > 1.2 ? 'landscape' : 'square');
  if (_handoff.videoAspect !== cat) { _handoff.videoAspect = cat; _renderHandoffTabBody(); }
}
function _handoffVideoNote(slug, aspect) {
  if (!aspect) return '';
  const human = { vertical: 'vertical (9:16)', landscape: 'landscape (16:9)', square: 'square (1:1)' };
  const want  = _VIDEO_ORIENT[slug] || 'flexible';
  const p     = _handoffPlatform(slug);
  const name  = p ? escapeHtml(p.name) : 'this platform';
  // Will this trailer's shape get cropped/reshaped on this platform?
  let targetFmt = '';
  if      (want === 'square'    && aspect !== 'square')    targetFmt = 'Square 1:1';
  else if (want === 'vertical'  && aspect === 'landscape') targetFmt = 'Vertical 9:16';
  else if (want === 'landscape' && aspect === 'vertical')  targetFmt = 'Horizontal 16:9';
  const isSlideshow = _handoff && _handoff.source === 'slideshow';
  const kind        = isSlideshow ? 'slideshow' : 'trailer';
  if (targetFmt) {
    const why = (want === 'square')
      ? name + ' crops video to square (1:1) by default'
      : name + ' shows ' + want + ' video best';
    const fix = isSlideshow
      ? 'On the <strong>Slideshow Video</strong> page set <strong>Video shape → ' + targetFmt + '</strong> and generate again (your slides and settings stay put)'
      : 'On the <strong>Book Trailer</strong> page set <strong>Format → ' + targetFmt + '</strong> and re-generate';
    return '<div style="font-size:12.5px;color:var(--gold);background:var(--gold-lt);border-radius:6px;padding:9px 11px;margin-top:8px;line-height:1.45">'
         + '⚠ Your ' + kind + ' is <strong>' + human[aspect] + '</strong> — ' + why + ', so it would be cropped here. '
         + fix + ' for ' + name + '.'
         + '</div>';
  }
  return '<div style="font-size:12px;color:var(--ink-soft);margin-top:6px">' + (kind === 'slideshow' ? 'Slideshow' : 'Trailer') + ' is ' + human[aspect] + ' — fine for ' + name + '.</div>';
}

function _renderHandoffTabBody() {
  const body = document.getElementById('handoff-tab-body');
  if (!body || !_handoff) return;
  const p = _handoff.platforms[_handoff.activeIdx];
  if (!p) { body.innerHTML = ''; return; }

  const platformCaption = _handoffCaptionFor(p.slug);
  const seedCaption     = _handoffPlainText(_handoff.caption);
  const isEdited        = Object.prototype.hasOwnProperty.call(_handoff.captionByPlatform, p.slug)
                          && platformCaption !== seedCaption;
  const contentType     = _handoff.videoUrl ? 'video' : (_handoff.imageUrl ? 'image' : 'text');
  const platformInstructions = (_POST_STEPS[contentType] && _POST_STEPS[contentType][p.slug])
    || 'Open the platform\'s composer, paste the caption, attach the file if there is one, and post.';

  // Build the Open-platform URL — intent template if available, else profile / homepage.
  // Uses the current tab's caption so per-platform edits ride along into the intent URL.
  let openHref;
  if (p.intent_url_template) {
    openHref = p.intent_url_template
      .replaceAll('{text}',      encodeURIComponent(platformCaption))
      .replaceAll('{url}',       encodeURIComponent(_handoff.linkUrl || ''))
      .replaceAll('{image_url}', encodeURIComponent(_handoff.imageUrl || ''));
  } else {
    openHref = p.profile_url || _platformHomeUrl(p.slug);
  }

  let html = '';

  // Editable caption — per-platform. Edits stay live while the modal is open.
  html += '<div class="field-label" style="margin-bottom:4px">Caption for ' + escapeHtml(p.name) + ' — edit before you copy or open</div>';
  html += '<textarea class="handoff-caption-textarea"' +
            ' oninput="_handoffEditCaption(\'' + escapeHtml(p.slug) + '\', this.value)"' +
            ' placeholder="Type a caption for ' + escapeHtml(p.name) + '…">' +
            escapeHtml(platformCaption) +
          '</textarea>';
  html += '<div class="handoff-caption-meta">';
  html += '<span>Edit hashtags or tone for ' + escapeHtml(p.name) + ' here — this version is what gets copied and opened.</span>';
  if (isEdited) {
    html += '<button type="button" onclick="_handoffResetCaption(\'' + escapeHtml(p.slug) + '\')">Reset to original</button>';
  }
  html += '</div>';

  // Hashtag suggestion chips — tuned per platform and per book genre.
  // Clicking a chip appends the tag to the textarea. Empty for platforms
  // that don't favor hashtags (LinkedIn, Reddit, Goodreads, BookBub, etc.).
  const hashtags = _handoffHashtagsFor(p.slug, _handoff.bookGenre);
  if (hashtags.length) {
    html += '<div class="handoff-hashtag-row">';
    html += '<span class="handoff-hashtag-label">Add a tag:</span>';
    hashtags.forEach(t => {
      html += '<button type="button" class="handoff-hashtag-chip" onclick="_handoffInsertHashtag(\'' + escapeHtml(p.slug) + '\', \'' + escapeHtml(t) + '\')">' + escapeHtml(t) + '</button>';
    });
    html += '</div>';
  }

  // Media preview
  if (_handoff.videoUrl) {
    html += '<div class="field-label" style="margin-bottom:4px">Video</div>';
    html += '<video class="handoff-image-preview" controls playsinline onloadedmetadata="_handoffVideoMeta(this)" src="' + escapeHtml(_handoff.videoUrl) + '"></video>';
    html += _handoffVideoNote(p.slug, _handoff.videoAspect);
  } else if (_handoff.imageUrl) {
    if (_handoffCanFit(p)) {
      const mode  = _handoffFitMode(p.slug);
      const store = _handoff.fittedByPlatform[p.slug] || {};
      const ready = Object.prototype.hasOwnProperty.call(store, mode);
      html += '<div class="field-label" style="margin-bottom:4px">Image — fitted for ' + escapeHtml(p.name) + ' (' + p.image_w + '×' + p.image_h + ')</div>';
      if (!ready) {
        _handoffEnsureFit(p.slug);   // async; re-renders this tab when done
        html += '<div class="handoff-image-preview" style="display:flex;align-items:center;justify-content:center;min-height:140px;color:var(--ink-soft);font-size:13px">Fitting to ' + escapeHtml(p.name) + '…</div>';
      } else {
        const fitted = store[mode];
        html += '<img class="handoff-image-preview" src="' + escapeHtml(fitted || _handoff.imageUrl) + '" alt="Post image fitted for ' + escapeHtml(p.name) + '">';
        if (!fitted) {
          html += '<div style="font-size:12px;color:var(--gold);margin-top:3px">Couldn\'t auto-fit this image — it must be one uploaded here. Showing the original.</div>';
        }
      }
      // Fit-mode toggle (per platform)
      html += '<div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;align-items:center">';
      html += '<span style="font-size:12px;color:var(--ink-soft)">Fit:</span>';
      [['blur', 'Blurred fill'], ['solid', 'Solid'], ['crop', 'Crop']].forEach(o => {
        const on = mode === o[0];
        html += '<button type="button" class="app-btn ' + (on ? 'app-btn-green' : 'app-btn-outline') + ' app-btn-sm" onclick="_handoffSetFitMode(\'' + escapeHtml(p.slug) + '\', \'' + o[0] + '\')">' + o[1] + '</button>';
      });
      html += '</div>';
    } else {
      html += '<div class="field-label" style="margin-bottom:4px">Image</div>';
      html += '<img class="handoff-image-preview" src="' + escapeHtml(_handoff.previewSrc || _handoff.imageUrl) + '" alt="Post image">';
    }
  }

  // ── Numbered steps ──
  const totalSteps = (_handoff.videoUrl || _handoff.imageUrl) ? 3 : 2;
  html += '<div class="handoff-steps-header">Post in ' + totalSteps + ' steps</div>';

  let stepN = 1;

  // Step: download (only when there's media)
  if (_handoff.videoUrl) {
    html += _handoffStepBlock(stepN++,
      'Download the video to your computer',
      'It will save to your Downloads folder as <em>' + escapeHtml(p.slug) + '_trailer.mp4</em>. You\'ll drag it into ' + escapeHtml(p.name) + ' in step 3.',
      '<button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="_handoffDownloadVideo(\'' + escapeHtml(p.slug) + '\')">Download video</button>'
    );
  } else if (_handoff.imageUrl) {
    html += _handoffStepBlock(stepN++,
      'Download the image to your computer',
      'It will save to your Downloads folder as <em>' + escapeHtml(p.slug) + '_post.' + (_handoff.imageUrl.match(/\.(png|jpe?g|gif|webp)(\?|$)/i)?.[1]?.toLowerCase() || 'png') + '</em>. You\'ll drag it into ' + escapeHtml(p.name) + ' in step 3.',
      '<button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="_handoffDownloadImage(\'' + escapeHtml(p.slug) + '\')">Download image</button>'
    );
  }

  // Step: copy caption. When there's media, explain WHY the caption matters —
  // authors reasonably ask "the text is already in the image, why paste it
  // again?" The answer: image text isn't clickable or searchable.
  const copyDetail = (_handoff.videoUrl || _handoff.imageUrl)
    ? 'You\'ll paste it into ' + escapeHtml(p.name) + '\'s composer in the next step. Even if the text appears in the ' +
      (_handoff.videoUrl ? 'video' : 'image') + ', the caption is what makes your post searchable and carries the clickable buy link — text inside media can\'t be clicked or found in search.'
    : 'You\'ll paste it into ' + escapeHtml(p.name) + '\'s composer in the next step.';
  html += _handoffStepBlock(stepN++,
    'Copy the caption to your clipboard',
    copyDetail,
    '<button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="_handoffCopyCaption()">Copy caption</button>'
  );

  // Step: open platform + per-platform instructions
  html += _handoffStepBlock(stepN++,
    'Open ' + escapeHtml(p.name) + ' and post',
    platformInstructions,
    '<a class="app-btn app-btn-green app-btn-sm" href="' + escapeHtml(openHref) + '" target="_blank" rel="noopener">Open ' + escapeHtml(p.name) + ' →</a>'
  );

  // Mark-as-posted
  const posted = _handoff.posted.has(p.slug);
  html += '<label class="handoff-marker">';
  html += '<input type="checkbox" ' + (posted ? 'checked ' : '') + 'onchange="_handoffTogglePosted(\'' + escapeHtml(p.slug) + '\', this.checked)">';
  html += 'Mark as posted to ' + escapeHtml(p.name);
  html += '</label>';

  // Link to the lesson for users who want the long version (video flow only).
  if (_handoff.videoUrl) {
    html += '<div class="handoff-more-help">';
    html += 'Need more detail? <a href="#" onclick="closeHandoffModal();navigate(\'education\');openLesson(\'posting-trailer\');return false">Read the full Posting Your Trailer guide →</a>';
    html += '</div>';
  }

  body.innerHTML = html;
}

function _handoffSwitchTab(idx) {
  if (!_handoff) return;
  _handoff.activeIdx = idx;
  _renderHandoffTabs();
  _renderHandoffTabBody();
}

async function _handoffTogglePosted(slug, checked) {
  if (!_handoff) return;
  // Optimistic UI: flip the local set immediately so the tab gets its ✓.
  if (checked) _handoff.posted.add(slug);
  else _handoff.posted.delete(slug);
  _renderHandoffTabs();

  // Persist the mark to /api/manual_post.php. The returned id is stored so
  // an unmark can DELETE the same row. Failures revert the local set.
  try {
    if (checked) {
      // Persist the platform's CURRENT caption (the user may have edited it),
      // not the original seed. Strip any HTML so the stored content is clean.
      const cleanCaption = _handoffPlainText(_handoffCaptionFor(slug));
      const data = await api('/manual_post.php?action=record', {
        method: 'POST',
        body: JSON.stringify({
          platform_slug: slug,
          content:       cleanCaption,
          image_url:     _handoffImageFor(slug) || '',
          video_url:     _handoff.videoUrl || '',
          source:        _handoff.source || '',
        }),
      });
      if (data && data.success && data.id) {
        _handoff.postedIds[slug] = data.id;
      } else {
        throw new Error(data?.message || 'Save failed');
      }
    } else {
      const id = _handoff.postedIds[slug];
      if (!id) return;   // never recorded, nothing to undo
      await api('/manual_post.php?action=undo', {
        method: 'POST',
        body: JSON.stringify({ id: id }),
      });
      delete _handoff.postedIds[slug];
    }
    // Refresh the Recent Posts feed when it's visible so the new manual
    // record appears (or disappears) immediately.
    if (typeof loadPostQueue === 'function') loadPostQueue();
  } catch (e) {
    // Revert the optimistic flip.
    if (checked) _handoff.posted.delete(slug);
    else _handoff.posted.add(slug);
    _renderHandoffTabs();
    _renderHandoffTabBody();
    toast('Couldn\'t save the mark — try again', true);
  }
}

function _handoffCopyCaption() {
  if (!_handoff) return;
  // Use the current tab's edited caption (falls back to the seed) and strip
  // any HTML so we never paste markup into a social composer.
  const p = _handoff.platforms[_handoff.activeIdx];
  const text = _handoffPlainText(p ? _handoffCaptionFor(p.slug) : _handoff.caption);
  if (!navigator.clipboard) { toast('Copy not supported — select the caption text manually', true); return; }
  navigator.clipboard.writeText(text).then(
    () => toast('Caption copied'),
    () => toast('Couldn\'t copy — select and copy manually', true)
  );
}

async function _handoffCopyImage() {
  if (!_handoff || !_handoff.imageUrl) return;
  if (!navigator.clipboard || !navigator.clipboard.write || typeof ClipboardItem === 'undefined') {
    toast('Copy image isn\'t supported in this browser — use Download', true);
    return;
  }
  try {
    const res  = await fetch(_handoff.imageUrl);
    const blob = await res.blob();
    await navigator.clipboard.write([new ClipboardItem({ [blob.type]: blob })]);
    toast('Image copied to clipboard');
  } catch (e) {
    toast('Couldn\'t copy image — use Download instead', true);
  }
}

async function _handoffDownloadImage(slug) {
  if (!_handoff || !_handoff.imageUrl) return;
  const url = _handoffImageFor(slug);   // fitted version when ready, else original
  const m   = url.match(/\.(png|jpe?g|gif|webp)(\?|$)/i);
  const ext = m ? m[1].toLowerCase() : 'jpg';
  await _handoffForceDownload(url, slug + '_post.' + ext, 'image');
}

async function _handoffDownloadVideo(slug) {
  if (!_handoff || !_handoff.videoUrl) return;
  const m   = _handoff.videoUrl.match(/\.(mp4|mov|webm|m4v)(\?|$)/i);
  const ext = m ? m[1].toLowerCase() : 'mp4';
  await _handoffForceDownload(_handoff.videoUrl, slug + '_trailer.' + ext, 'video');
}

// Cross-origin files don't honor the <a download> attribute — the browser
// just opens them in a new tab. Workaround: fetch the file as a blob, then
// download from a blob: URL which IS same-origin. If the CDN blocks CORS,
// we open in a new tab and prompt the user to right-click → Save As.
async function _handoffForceDownload(url, filename, label) {
  toast('Downloading ' + label + '…');
  try {
    const res = await fetch(url, { mode: 'cors', credentials: 'omit' });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const blob = await res.blob();
    const blobUrl = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(blobUrl), 5000);
    toast(label.charAt(0).toUpperCase() + label.slice(1) + ' saved to your Downloads folder');
  } catch (e) {
    toast('Auto-save blocked by the file host — opening in a new tab; right-click and Save As', true);
    window.open(url, '_blank', 'noopener');
  }
}

// Strip any HTML markup that may have come in from upstream AI generators
// (gv-social → post-content sometimes carries <p>/<br>/etc.). Preserves the
// text content; the modal preview and clipboard both go through this.
function _handoffPlainText(s) {
  if (!s) return '';
  if (s.indexOf('<') === -1 && s.indexOf('&') === -1) return s;  // fast path
  const div = document.createElement('div');
  div.innerHTML = s;
  return (div.textContent || '').trim();
}

// Per-slug fallback homepage URL when the catalog has no intent_url_template
// AND the user hasn't pasted a profile URL. Last-resort, but better than blank.
function _platformHomeUrl(slug) {
  const map = {
    facebook:   'https://facebook.com/',
    instagram:  'https://instagram.com/',
    reels:      'https://instagram.com/',
    tiktok:     'https://tiktok.com/',
    x:          'https://x.com/',
    threads:    'https://threads.net/',
    bluesky:    'https://bsky.app/',
    linkedin:   'https://linkedin.com/',
    pinterest:  'https://pinterest.com/',
    reddit:     'https://reddit.com/',
    goodreads:  'https://goodreads.com/',
    storygraph: 'https://app.thestorygraph.com/',
    bookbub:    'https://bookbub.com/',
    substack:   'https://substack.com/',
    discord:    'https://discord.com/',
    royalroad:  'https://royalroad.com/',
  };
  return map[slug] || ('https://google.com/search?q=' + encodeURIComponent(slug));
}

// ── SOCIAL POSTING ────────────────────────────────────────────

const SP_PLATFORM_LIMITS = {
  bluesky:   300,
  linkedin:  3000,
  instagram: 2200,
  tiktok:    2200,
  reddit:    40000,
  facebook:  63206,
  pinterest: 500,
};
const SP_PLATFORM_NAMES = {
  bluesky: 'Bluesky', linkedin: 'LinkedIn', instagram: 'Instagram', tiktok: 'TikTok', reddit: 'Reddit', facebook: 'Facebook', pinterest: 'Pinterest',
};
const SP_HASHTAGS = [
  '#indieauthor', '#amwriting', '#amreading', '#booklover', '#bookstagram',
  '#newrelease', '#bookpromo', '#mustread', '#newbook', '#authorlife',
  '#writingcommunity', '#booklaunch', '#bookrecommendations', '#tbr',
  '#supportindieauthors',
];

// Platform-aware character counter. Counts the final post (content + link if any),
// shows the tightest selected platform's limit, and turns red when exceeded.
function spUpdateCharCount() {
  const counter = document.getElementById('char-count');
  if (!counter) return;
  const text = document.getElementById('post-content')?.value || '';
  const link = document.getElementById('post-link')?.value?.trim() || '';
  const finalText = link ? text + '\n\n' + link : text;
  const len = finalText.length;
  const checked = Array.from(document.querySelectorAll('.platform-check:checked')).map(c => c.value);

  let tightest = null;
  for (const p of checked) {
    const limit = SP_PLATFORM_LIMITS[p];
    if (limit && (tightest === null || limit < tightest.limit)) {
      tightest = { name: SP_PLATFORM_NAMES[p] || p, limit };
    }
  }

  if (tightest) {
    const over = len > tightest.limit;
    counter.textContent = len + ' / ' + tightest.limit + ' (' + tightest.name + ')' + (over ? ' — over limit' : '');
    counter.style.color = over ? 'var(--error)' : '';
    counter.style.fontWeight = over ? '600' : '';
  } else {
    counter.textContent = len + ' characters';
    counter.style.color = '';
    counter.style.fontWeight = '';
  }
}

function spInsertLineBreak() {
  const ta = document.getElementById('post-content');
  if (!ta) return;
  const start = ta.selectionStart || ta.value.length;
  const end   = ta.selectionEnd   || ta.value.length;
  ta.value = ta.value.substring(0, start) + '\n\n' + ta.value.substring(end);
  ta.selectionStart = ta.selectionEnd = start + 2;
  ta.focus();
  spUpdateCharCount();
  updatePostPreview();
}

function spToggleHashtagPanel() {
  const panel = document.getElementById('sp-hashtag-panel');
  if (!panel) return;
  if (panel.style.display === 'none' || !panel.style.display) {
    spRenderHashtagChips();
    panel.style.display = 'block';
  } else {
    panel.style.display = 'none';
  }
}

function spRenderHashtagChips() {
  const wrap = document.getElementById('sp-hashtag-chips');
  if (!wrap) return;
  wrap.innerHTML = SP_HASHTAGS.map(t =>
    '<button class="app-btn app-btn-outline app-btn-sm" type="button" onclick="spInsertHashtag(\'' +
      t + '\')" style="font-size:11px;padding:3px 8px">' + escapeHtml(t) + '</button>'
  ).join('');
}

function spInsertHashtag(tag) {
  const ta = document.getElementById('post-content');
  if (!ta) return;
  const cur = ta.value;
  // Skip if already in the post (case-insensitive, word-boundary check).
  const re = new RegExp('(?:^|\\s)' + tag.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(?:\\s|$)', 'i');
  if (re.test(cur)) return;
  const prefix = (cur.length === 0 || /[\s\n]$/.test(cur)) ? '' : ' ';
  ta.value = cur + prefix + tag;
  ta.focus();
  spUpdateCharCount();
  updatePostPreview();
}

function spInsertCustomHashtag() {
  const input = document.getElementById('sp-hashtag-custom');
  if (!input) return;
  let raw = (input.value || '').trim();
  if (!raw) return;
  // Strip any internal whitespace and stray leading hashes; require alnum/_ chars.
  raw = raw.replace(/^#+/, '').replace(/\s+/g, '');
  if (!raw) return;
  if (!/^[A-Za-z0-9_]+$/.test(raw)) {
    toast('Hashtags can only contain letters, numbers, and underscores', true);
    return;
  }
  spInsertHashtag('#' + raw);
  input.value = '';
  input.focus();
}

async function submitPost(action) {
  const content    = document.getElementById('post-content').value.trim();
  const imageUrl   = document.getElementById('post-image').value.trim();
  const link       = document.getElementById('post-link')?.value?.trim() || '';
  const scheduleAt = document.getElementById('post-schedule').value.trim();
  const slugs      = Array.from(document.querySelectorAll('.platform-check:checked')).map(cb => cb.value);

  if (!content)         return toast('Please write some content first', true);
  if (!slugs.length)    return toast('Select at least one platform', true);
  if (action === 'schedule' && !scheduleAt) return toast('Enter a date and time to schedule', true);

  const finalContent = link ? content + '\n\n' + link : content;

  // Video attachment: no platform API accepts our video files, so drafts
  // and scheduling don't apply — every selected platform goes through the
  // manual handoff modal, same model as trailers and slideshows.
  if (_spIsVideo(imageUrl)) {
    if (action !== 'post_now') {
      return toast('Video posts go out by hand per platform — use Post now. (Drafts and scheduling are for text and image posts.)', true);
    }
    const chosen = slugs.map(slug => (_postPlatforms || []).find(pp => pp.slug === slug)).filter(Boolean);
    if (!chosen.length) return toast('Select at least one platform', true);
    openHandoffModal({
      caption:   finalContent,
      linkUrl:   link,
      videoUrl:  imageUrl,
      platforms: chosen,
      source:    'post_composer',
    });
    return;
  }

  // Split selection into AutoPost (api token live) vs manual handoff using
  // the cached catalog the chooser populated. Manual platforms can't be
  // dispatched via /api/post.php — they open the handoff modal instead.
  const auto = [], manual = [];
  slugs.forEach(slug => {
    const p = (_postPlatforms || []).find(pp => pp.slug === slug);
    if (!p) return;
    if (p.api_slug && p.autoposts) auto.push(p); else manual.push(p);
  });

  // Schedule-with-manual would silently fail at fire time, so block it up front.
  if (action === 'schedule' && manual.length) {
    return toast('Scheduling works only for AutoPost platforms. Uncheck the manual ones or remove the schedule and use Post now.', true);
  }

  const btn = document.getElementById('btn-post-now');
  const origBtnText = btn ? btn.textContent : 'Post now';
  if (btn) { btn.disabled = true; btn.textContent = 'Posting…'; }

  // Helper to clear the composer once everything that's going to happen
  // has happened. Called from both the AutoPost-only and post-modal paths.
  function clearForm() {
    document.getElementById('post-content').value = '';
    document.getElementById('post-image').value = '';
    const postLink = document.getElementById('post-link');
    if (postLink) postLink.value = '';
    document.getElementById('post-schedule').value = '';
    document.querySelectorAll('.platform-check').forEach(cb => cb.checked = false);
    document.getElementById('post-link-instagram-note').style.display = 'none';
    spUpdateCharCount();
    updatePostPreview();
    loadPostQueue();
    // Reload the chooser too — Auto badges may have flipped if a token expired.
    loadPostComposerPlatforms();
  }

  let autopostSummary = '';
  let autopostFailed  = false;

  try {
    // ── Draft / Schedule: save all checked platforms as-is via /api/post.php.
    //    (Manual-in-draft is harmless; manual-in-schedule was blocked above.)
    if (action === 'draft' || action === 'schedule') {
      const data = await api('/post.php?action=' + action, {
        method: 'POST',
        body: JSON.stringify({ content: finalContent, image_url: imageUrl, platforms: slugs, schedule_at: scheduleAt, action }),
      });
      if (data.success) { toast(data.message || 'Done!'); clearForm(); }
      else              { toast(data.message || 'Something went wrong', true); }
      return;
    }

    // ── Post now: AutoPost first (if any), then open modal for manual (if any).
    if (auto.length) {
      const apiSlugs = auto.map(p => p.api_slug);
      // Auto-fit the image so AutoPost platforms (Bluesky, LinkedIn, Pinterest)
      // don't crop it. One shared 4:5 blurred-pad fit — pad never crops, so it's
      // safe for all three; falls back to the original on any failure so posting
      // never breaks.
      let autoImageUrl = imageUrl;
      if (imageUrl) {
        try {
          const fit = await api('/image_fit.php', {
            method: 'POST',
            body: JSON.stringify({ source: imageUrl, w: 1080, h: 1350, mode: 'blur' }),
          });
          if (fit && fit.success && fit.url) autoImageUrl = fit.url;
        } catch (e) { /* keep original */ }
      }
      const data = await api('/post.php?action=post_now', {
        method: 'POST',
        body: JSON.stringify({ content: finalContent, image_url: autoImageUrl, platforms: apiSlugs, schedule_at: '', action: 'post_now' }),
      });
      // Inspect per-platform results so failures cascade into the modal
      // as manual tabs instead of leaving the author stuck on a vague error.
      const succeeded = [], failedAuto = [];
      if (data && data.results) {
        auto.forEach(p => {
          const r = data.results[p.api_slug];
          if (r && r.success) succeeded.push(p);
          else                 failedAuto.push(p);
        });
      } else if (data && data.success) {
        succeeded.push(...auto);
      } else {
        failedAuto.push(...auto);
      }
      if (succeeded.length) {
        autopostSummary = 'Auto-posted to ' + succeeded.map(p => p.name).join(', ');
        toast(autopostSummary);
      }
      if (failedAuto.length) {
        failedAuto.forEach(p => manual.push(p));
        autopostFailed = true;
        const failNames = failedAuto.map(p => p.name).join(', ');
        const sep = autopostSummary ? '. ' : '';
        autopostSummary = (autopostSummary || '') + sep + 'AutoPost failed for ' + failNames + ' — added to the tabs below so you can post by hand.';
        toast('AutoPost failed for ' + failNames + ' — falling back to manual', true);
      }
    }

    if (manual.length) {
      // Hand off the manual platforms to the in-app modal. We leave the
      // composer fields populated so the author can reference them while
      // they're in the modal copying / opening platforms.
      openHandoffModal({
        caption:        finalContent,
        linkUrl:        link,
        imageUrl:       imageUrl,
        platforms:      manual,
        source:         'post_composer',
        autopostSummary: autopostSummary,
        autopostFailed:  autopostFailed,
      });
      // Refresh the queue (in case auto created a record) but keep the form.
      loadPostQueue();
    } else {
      // AutoPost-only: clear the form like the pre-v52 flow did.
      clearForm();
    }
  } catch(e) {
    toast('Network error — check connection and try again', true);
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = origBtnText; }
  }
}

function updatePostPreview() {
  const preview = document.getElementById('post-preview');
  if (!preview) return;
  const content = document.getElementById('post-content')?.value || '';
  const link    = document.getElementById('post-link')?.value?.trim() || '';
  if (!content.trim() && !link) {
    preview.innerHTML = '<span style="color:var(--ink-soft);font-style:italic">Type a post above to see how it\'ll look with the link.</span>';
    return;
  }
  let html = escapeHtml(content);
  if (link) html += '\n\n<span style="color:var(--accent);word-break:break-all">' + escapeHtml(link) + '</span>';
  preview.innerHTML = html;
}

function gvSocialUpdatePreview() {
  const preview = document.getElementById('gv-social-preview');
  if (!preview) return;
  const content = document.getElementById('gv-social-copy-text')?.value || '';
  const link    = document.getElementById('gv-social-link')?.value?.trim() || '';
  if (!content.trim() && !link) {
    preview.innerHTML = '<span style="color:var(--ink-soft);font-style:italic">Type a post above to see how it\'ll look with the link.</span>';
    return;
  }
  let html = escapeHtml(content);
  if (link) html += '\n\n<span style="color:var(--accent);word-break:break-all">' + escapeHtml(link) + '</span>';
  preview.innerHTML = html;
}

// Authors usually paste Amazon URLs straight from search results — hundreds of
// characters of ref/crid/dib tracking junk. When the URL contains an ASIN,
// reduce it to the canonical short form for captions and composers. The
// stored amazon_url on the book record is left untouched.
function cleanAmazonUrl(url) {
  if (!url || !/amazon\./i.test(url)) return url || '';
  const m = url.match(/\/(?:dp|gp\/product)\/([A-Z0-9]{10})/i);
  return m ? 'https://www.amazon.com/dp/' + m[1] : url;
}

function spSyncLinkFromBook() {
  const bookId = parseInt(document.getElementById('bookSelector')?.value || '0');
  if (!bookId) return;
  const book = (window._books || []).find(b => b.id == bookId);
  if (!book) return;
  // On My Books, the topbar selector doubles as a quick book switcher: if a book
  // is open in the edit form, switch the form to the chosen book instead of
  // forcing the user to go Back to Books and pick from the grid.
  const formView = document.getElementById('books-form-view');
  if (formView && formView.style.display !== 'none' && typeof editBook === 'function') {
    editBook(bookId);
  }
  const link = cleanAmazonUrl(book.amazon_url || '');
  const postLink = document.getElementById('post-link');
  if (postLink && !postLink.value) { postLink.value = link; updatePostPreview(); }
}

function spFillLink() {
  const bookId = parseInt(document.getElementById('bookSelector')?.value || '0');
  const book = bookId ? (window._books || []).find(b => b.id == bookId) : null;
  const link = cleanAmazonUrl(book?.amazon_url || '') || currentUser?.website || '';
  const postLink = document.getElementById('post-link');
  if (postLink) { postLink.value = link; updatePostPreview(); }
}

function spFillBookCover() {
  const bookId = parseInt(document.getElementById('bookSelector')?.value || '0');
  const book = bookId ? (window._books || []).find(b => b.id == bookId) : null;
  const coverUrl = book?.cover_url || '';
  if (!coverUrl) return toast('No cover URL saved for this book', true);
  const postImage = document.getElementById('post-image');
  if (postImage) { postImage.value = coverUrl; onPostImageChanged(); }
}

// Upload a local image file (e.g. one saved from ChatGPT/Downloads) and
// drop the resulting public URL into the Image URL field. The server
// (upload_post_image.php) downscales/recompresses to fit Bluesky's
// ~976KB blob limit, so big AI-generated PNGs just work.
async function spUploadPostImage(input) {
  const file = input.files && input.files[0];
  if (!file) return;
  // MP4/MOV goes through the video endpoint (stored as-is, posted via
  // manual handoff like trailers); everything else is an image.
  const isVideo = /^video\//.test(file.type) || /\.(mp4|mov)$/i.test(file.name);
  const btn  = document.getElementById('post-image-upload-btn');
  const orig = btn ? btn.textContent : '';
  if (btn) { btn.disabled = true; btn.textContent = 'Uploading…'; }
  try {
    const fd = new FormData();
    fd.append(isVideo ? 'video' : 'image', file);
    const headers = {};   // no Content-Type — let the browser set the multipart boundary
    const token = localStorage.getItem('auth_token');
    if (token) headers['X-Auth-Token'] = token;
    const res  = await fetch(isVideo ? '/api/upload_post_video.php' : '/api/upload_post_image.php', {
      method: 'POST', headers: headers, credentials: 'same-origin', body: fd,
    });
    const data = await res.json();
    if (!data.success || !data.url) throw new Error(data.message || 'Upload failed');
    const postImage = document.getElementById('post-image');
    if (postImage) { postImage.value = data.url; onPostImageChanged(); }
    toast(isVideo
      ? 'Video uploaded — video posts go out by hand per platform, so Post now opens the posting steps'
      : 'Image uploaded');
  } catch (e) {
    toast(e.message || 'Upload failed — please try again', true);
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = orig; }
    input.value = '';   // reset so the same file can be re-picked
  }
}

// The composer's media field holds either an image or a video URL —
// video changes the platform list, disables scheduling, and routes
// everything through the manual handoff modal.
function _spIsVideo(url) {
  return /\.(mp4|mov)(\?|#|$)/i.test(url || '');
}

// Trailer: upload a local image to use as the cover in the trailer, replacing
// the book's default cover. Mirrors spUploadPostImage — uploads via
// upload_post_image.php (which downscales/hosts it publicly so Shotstack can
// fetch it) and drops the resulting URL into the tv-cover-url field.
async function tvUploadCover(input) {
  const file = input.files && input.files[0];
  if (!file) return;
  const btn  = document.getElementById('tv-cover-upload-btn');
  const orig = btn ? btn.textContent : '';
  if (btn) { btn.disabled = true; btn.textContent = 'Uploading…'; }
  try {
    const fd = new FormData();
    fd.append('image', file);
    const headers = {};   // no Content-Type — let the browser set the multipart boundary
    const token = localStorage.getItem('auth_token');
    if (token) headers['X-Auth-Token'] = token;
    const res  = await fetch('/api/upload_post_image.php', {
      method: 'POST', headers: headers, credentials: 'same-origin', body: fd,
    });
    const data = await res.json();
    if (!data.success || !data.url) throw new Error(data.message || 'Upload failed');
    const cover = document.getElementById('tv-cover-url');
    if (cover) cover.value = data.url;
    // Reveal the contextual note so the author knows the default (composed)
    // treatment vs the full-frame option.
    const note = document.getElementById('tv-cover-note');
    if (note) note.style.display = 'block';
    toast('Cover image uploaded');
  } catch (e) {
    toast(e.message || 'Upload failed — please try again', true);
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = orig; }
    input.value = '';   // reset so the same file can be re-picked
  }
}

// One-click presets for the Schedule input. The native datetime-local
// picker is functional but clumsy, especially the time portion; these
// cover ~90% of common scheduling needs.
function spSetSchedule(preset) {
  const now = new Date();
  let target;
  if (preset === 'plus-1h') {
    target = new Date(now.getTime() + 60 * 60 * 1000);
  } else if (preset === 'tonight-6pm') {
    target = new Date(now);
    target.setHours(18, 0, 0, 0);
    // If it's already past 6pm today, bump to tomorrow's 6pm.
    if (target <= now) target.setDate(target.getDate() + 1);
  } else if (preset === 'tomorrow-9am') {
    target = new Date(now);
    target.setDate(target.getDate() + 1);
    target.setHours(9, 0, 0, 0);
  } else if (preset === 'tomorrow-6pm') {
    target = new Date(now);
    target.setDate(target.getDate() + 1);
    target.setHours(18, 0, 0, 0);
  }
  if (!target) return;
  // datetime-local expects YYYY-MM-DDTHH:MM in the user's local time.
  const pad = n => String(n).padStart(2, '0');
  const v = target.getFullYear() + '-' + pad(target.getMonth() + 1) + '-' + pad(target.getDate())
         + 'T' + pad(target.getHours()) + ':' + pad(target.getMinutes());
  const el = document.getElementById('post-schedule');
  if (el) el.value = v;
}

async function loadPostQueue() {
  const data = await api('/post.php?action=queue');
  const el = document.getElementById('post-queue');
  const dashPosts = document.getElementById('dash-posts');
  if (!data.success || !data.posts || !data.posts.length) {
    el.innerHTML = '<div class="empty">No posts yet</div>';
    if (dashPosts) dashPosts.textContent = '0';
    return;
  }
  if (dashPosts) dashPosts.textContent = data.posts.length;
  el.innerHTML = data.posts.map(p => {
    const isManual = p.status === 'manual';
    const badge = isManual                  ? 'badge-green'
                : p.status === 'sent'       ? 'badge-green'
                : p.status === 'failed'     ? 'badge-red'
                : p.status === 'scheduled'  ? 'badge-amber'
                                            : 'badge-amber';
    const label = isManual                  ? 'Posted manually'
                : p.status === 'sent'       ? 'Sent'
                : p.status === 'failed'     ? 'Failed'
                : p.status === 'scheduled'  ? 'Scheduled'
                                            : 'Pending';
    const preview = (p.content || '').substring(0, 80) + ((p.content || '').length > 80 ? '…' : '');
    const date = p.sent_at ? new Date(p.sent_at).toLocaleDateString() : (p.scheduled_at ? 'Scheduled: ' + new Date(p.scheduled_at).toLocaleDateString() : '');
    return '<div class="row"><div><div>' + preview + '</div><div class="row-meta">' + (p.platforms || '') + (date ? ' · ' + date : '') + '</div></div><span class="badge ' + badge + '">' + label + '</span></div>';
  }).join('');
}

async function aiDraftPost() {
  const btn = document.querySelector('.ai-btn');
  btn.textContent = 'Drafting…'; btn.disabled = true;
  try {
    const bookId = parseInt(document.getElementById('bookSelector').value, 10) || 0;
    const seed = document.getElementById('post-content').value.trim();
    const prompt = seed
      ? 'Using the following as a direction or starting point, write a polished social media post: ' + seed
      : '';
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'social_post', book_id: bookId, prompt, max_tokens: 400 }),
    });
    if (data.success && data.draft) {
      document.getElementById('post-content').value = data.draft;
      spUpdateCharCount();
      updatePostPreview();
      if (data.quota) updateQuotaMeter(data.quota);
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'AI draft unavailable — write manually', true);
    }
  } catch(e) { toast('AI draft unavailable — write manually', true); }
  btn.textContent = '✦ AI draft'; btn.disabled = false;
}

// ── COVER LETTER ──────────────────────────────────────────────

const CL_PURPOSES = {
  agent:     ['Query for literary representation',
              'Submit a proposal for a nonfiction book',
              'Follow up on a previous query submission',
              'Request a referral to another agent',
              'other'],
  publisher: ['Submit manuscript for publication consideration',
              'Pitch a book series or multi-title deal',
              'Request a meeting at a publishing conference',
              'other'],
  podcast:   ['Request a guest appearance to discuss my book',
              'Pitch an episode on self-publishing or the writing process',
              'Offer to discuss my area of expertise or research',
              'other'],
  media:     ['Request a book review',
              'Pitch a feature story or author interview',
              'Pitch an op-ed or guest article',
              'Announce a new book release',
              'Suggest a local author spotlight',
              'other'],
  bookclub:  ['Offer to join a book club discussion of my book',
              'Offer a virtual Q&A for your reading group',
              'Request your book club consider my book',
              'other'],
  other:     ['Request a speaking or event appearance',
              'Introduce myself and my work',
              'Follow up on a previous conversation',
              'other'],
};

function updateClPurposeOptions() {
  const type    = document.getElementById('cl-recipient-type').value;
  const sel     = document.getElementById('cl-purpose-select');
  const current = sel.value;
  const options = CL_PURPOSES[type] || CL_PURPOSES.other;

  sel.innerHTML = '<option value="">— choose a purpose —</option>' +
    options.map(p => p === 'other'
      ? '<option value="other">Other — describe below</option>'
      : '<option value="' + p + '"' + (p === current ? ' selected' : '') + '>' + p + '</option>'
    ).join('');

  // Re-evaluate whether the other textarea should show
  toggleClPurposeOther();
}

function initCoverLetterView() {
  const sel = document.getElementById('cl-book-id');
  if (!sel) return;
  const current = sel.value;
  sel.innerHTML = '<option value="0">No specific book</option>' +
    (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
  if (current) sel.value = current;
  updateClPurposeOptions();
  renderBookBanner('cl-book-id', 'cl-book-status');
}

async function generateCoverLetter() {
  const recipientType = document.getElementById('cl-recipient-type').value;
  const recipientName = document.getElementById('cl-recipient-name').value.trim();
  const purposeSelect = document.getElementById('cl-purpose-select').value;
  const purpose = purposeSelect === 'other'
    ? document.getElementById('cl-purpose-other').value.trim()
    : purposeSelect;
  const authorCredits = document.getElementById('cl-author-credits').value.trim();
  const bookId        = parseInt(document.getElementById('cl-book-id').value, 10) || 0;

  if (!purpose) { toast('Please select or describe the purpose of the letter', true); return; }

  const btn = document.getElementById('cl-generate-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generating…';
  document.getElementById('cl-output-card').style.display = 'none';

  try {
    const data = await api('/cover_letter.php', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId, recipient_type: recipientType,
        recipient_name: recipientName || null, purpose,
        author_credits: authorCredits || null }),
    });

    if (data.success) {
      document.getElementById('cl-output').textContent = data.cover_letter;
      document.getElementById('cl-output-card').style.display = 'block';
      document.getElementById('main').scrollTop =
        document.getElementById('cl-output-card').offsetTop - 20;
      if (data.quota) {
        updateQuotaMeter(data.quota);
        const pct = Math.min(100, Math.round((data.quota.used_tenths_cent / data.quota.cap_tenths_cent) * 100));
        document.getElementById('cl-quota-note').textContent =
          'AI usage this period: ' + pct + '% of monthly allowance';
      }
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'Generation failed — please try again', true);
    }
  } catch(e) { toast('Request failed — check your connection and try again', true); }

  btn.disabled = false;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generate cover letter';
}

function copyCoverLetter() {
  const text = document.getElementById('cl-output').textContent;
  if (!text) return;
  navigator.clipboard.writeText(text).then(
    () => toast('Copied to clipboard'),
    () => toast('Copy failed — select and copy manually', true)
  );
}

function toggleClPurposeOther() {
  const sel   = document.getElementById('cl-purpose-select').value;
  const other = document.getElementById('cl-purpose-other');
  other.style.display = sel === 'other' ? 'block' : 'none';
}

async function aiSuggestClPurpose() {
  const btn          = event.target.closest('button');
  btn.textContent    = 'Thinking…'; btn.disabled = true;

  const recipientType = document.getElementById('cl-recipient-type').value;
  const bookId        = parseInt(document.getElementById('cl-book-id').value, 10) || 0;
  const selectedBook  = bookId && booksList ? booksList.find(b => b.id == bookId) : null;

  const typeLabels = {
    agent: 'literary agent', publisher: 'publisher or editor',
    podcast: 'podcast host', media: 'journalist or media contact',
    bookclub: 'book club', other: 'professional contact'
  };

  let prompt = 'Write one sentence describing the purpose of a cover letter '
    + 'from an author to a ' + (typeLabels[recipientType] || 'contact') + '. ';
  if (selectedBook) prompt += 'The book is called "' + selectedBook.title + '". ';
  prompt += 'Be specific and direct. Output only the single sentence.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: bookId, prompt, max_tokens: 80 }),
    });
    if (data.success && data.draft) {
      // Put the result into "other" so it's fully editable
      document.getElementById('cl-purpose-select').value = 'other';
      toggleClPurposeOther();
      document.getElementById('cl-purpose-other').value = data.draft;
      if (data.quota) updateQuotaMeter(data.quota);
    } else {
      toast(data.message || 'Could not suggest a purpose', true);
    }
  } catch(e) { toast('Request failed — try again', true); }

  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg> AI suggest';
  btn.disabled = false;
}

async function aiDraftClCredits() {
  const btn = event.target.closest('button');
  btn.textContent = 'Drafting…'; btn.disabled = true;

  const bookId   = parseInt(document.getElementById('cl-book-id').value, 10) || 0;
  const existing = document.getElementById('cl-author-credits').value.trim();
  const name     = (currentUser && (currentUser.pen_name || currentUser.full_name)) || '';

  let prompt = existing
    ? 'Polish these author credentials for use in a cover letter (2-3 sentences, specific and concrete): ' + existing
    : 'Write a brief author credentials statement for use in a cover letter (1-2 sentences). '
        + 'Focus on platform, prior publications, awards, or relevant experience.';
  if (name) prompt += ' Author name: ' + name + '.';
  prompt += ' Output only the credentials statement.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: bookId, prompt, max_tokens: 150 }),
    });
    if (data.success && data.draft) {
      document.getElementById('cl-author-credits').value = data.draft;
      if (data.quota) updateQuotaMeter(data.quota);
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached', true);
    } else {
      toast(data.message || 'Could not generate credentials', true);
    }
  } catch(e) { toast('Request failed — try again', true); }

  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg> AI draft';
  btn.disabled = false;
}

// ── PRINT QUOTE ───────────────────────────────────────────────

let pqEstimateTimer = null;

function pqEl(id) { return document.getElementById(id); }

function pqFmtCurrency(num) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(num) || 0);
}

function pqBindingLabel(val) {
  const map = { '3hole':'3 Hole Punch','comb':'Comb','coil':'Coil',
                'perfect':'Perfect Bound (Paperback)','saddle':'Saddle Stitch' };
  return map[val] || 'Not selected';
}

function pqEnforceRules() {
  const qty = parseInt(pqEl('pq-quantity').value, 10) || 0;

  const uvOpt = pqEl('pq-uvGloss').querySelector('option[value="yes"]');
  if (uvOpt) {
    uvOpt.disabled = qty < 50;
    if (qty < 50 && pqEl('pq-uvGloss').value === 'yes') pqEl('pq-uvGloss').value = '';
  }

  const fpOpt = pqEl('pq-freeProof').querySelector('option[value="yes"]');
  const fpHint = pqEl('pq-freeProofHint');
  if (fpOpt) {
    fpOpt.disabled = qty < 10;
    if (qty < 10 && pqEl('pq-freeProof').value === 'yes') pqEl('pq-freeProof').value = '';
    if (fpHint) fpHint.textContent = qty >= 10
      ? 'You qualify for a free proof with this order size.'
      : 'Available for orders of 10+ copies.';
  }
}

function pqResetEstimate() {
  ['pq-sumQty','pq-sumBW','pq-sumColor'].forEach(id => { if(pqEl(id)) pqEl(id).textContent = '0'; });
  if(pqEl('pq-sumBinding')) pqEl('pq-sumBinding').textContent = 'Not selected';
  ['pq-unitPrice','pq-adjustedUnitPrice','pq-basePrice','pq-totalPrice']
    .forEach(id => { if(pqEl(id)) pqEl(id).textContent = '$0.00'; });
}

function pqScheduleEstimate() {
  if (pqEstimateTimer) clearTimeout(pqEstimateTimer);
  pqEstimateTimer = setTimeout(pqRunEstimate, 250);
}

async function pqRunEstimate() {
  const qty       = parseInt(pqEl('pq-quantity').value, 10) || 0;
  const trimSize  = pqEl('pq-trimSize').value;
  const binding   = pqEl('pq-binding').value;
  const bwPages   = parseInt(pqEl('pq-bwPages').value, 10) || 0;
  const colorPages= parseInt(pqEl('pq-colorPages').value, 10) || 0;
  const side      = pqEl('pq-printingSide').value;
  const interior  = pqEl('pq-interiorPaper').value;
  const cover     = pqEl('pq-coverStock').value;
  const uvGloss   = pqEl('pq-uvGloss').value;
  const turnaround= pqEl('pq-turnaround').value;

  // Always update the display labels regardless
  if(pqEl('pq-sumQty'))      pqEl('pq-sumQty').textContent      = qty || 0;
  if(pqEl('pq-sumBinding'))  pqEl('pq-sumBinding').textContent  = pqBindingLabel(binding);
  if(pqEl('pq-sumBW'))       pqEl('pq-sumBW').textContent       = bwPages;
  if(pqEl('pq-sumColor'))    pqEl('pq-sumColor').textContent    = colorPages;

  if (!trimSize || !binding || !side || !interior || !cover || qty < 1
      || (bwPages + colorPages) <= 0) {
    return;
  }

  const fd = new FormData();
  fd.append('action', 'estimate');
  fd.append('trimSize', trimSize);
  fd.append('quantity', qty);
  fd.append('binding', binding);
  fd.append('bwPages', bwPages);
  fd.append('colorPages', colorPages);
  fd.append('printingSide', side);
  fd.append('interiorPaper', interior);
  fd.append('coverStock', cover);
  fd.append('uvGloss', uvGloss);
  fd.append('turnaround', turnaround);

  try {
    const headers = {};
    if (authToken) headers['X-Auth-Token'] = authToken;
    const res  = await fetch(API + '/print_quote.php', { method: 'POST', body: fd, headers });
    const data = await res.json();
    if (!data.success) return;
    if(pqEl('pq-unitPrice'))         pqEl('pq-unitPrice').textContent         = pqFmtCurrency(data.unitPrice);
    if(pqEl('pq-adjustedUnitPrice')) pqEl('pq-adjustedUnitPrice').textContent = pqFmtCurrency(data.adjustedUnitPrice);
    if(pqEl('pq-basePrice'))         pqEl('pq-basePrice').textContent         = pqFmtCurrency(data.baseEstimatedPrice);
    if(pqEl('pq-totalPrice'))        pqEl('pq-totalPrice').textContent        = pqFmtCurrency(data.estimateTotal);
  } catch(e) { /* silent — estimate is best-effort */ }
}

function pqSetError(id, show, msg) {
  const errEl = pqEl(id);
  if (!errEl) return;
  if (show) { errEl.style.display = 'block'; if (msg) errEl.textContent = msg; }
  else       { errEl.style.display = 'none'; }
  // Highlight the associated field — error id is fieldId + 'Error'
  const fieldId = id.replace(/Error$/, '');
  const field = pqEl(fieldId);
  if (field) {
    if (show) field.classList.add('pq-invalid');
    else      field.classList.remove('pq-invalid');
  }
}

function pqValidate() {
  // Clear all previous error state before re-validating
  document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.pq-invalid').forEach(el => el.classList.remove('pq-invalid'));

  let ok = true;
  const qty = parseInt(pqEl('pq-quantity').value, 10) || 0;

  const checks = [
    ['pq-nameError',       pqEl('pq-name').value.trim() !== '',          'Please enter your full name.'],
    ['pq-emailError',      /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(pqEl('pq-email').value.trim()), 'Please enter a valid email.'],
    ['pq-phoneError',      pqEl('pq-phone').value.trim() !== '',         'Please enter a phone number.'],
    ['pq-trimSizeError',   pqEl('pq-trimSize').value !== '',             'Please choose a page size.'],
    ['pq-quantityError',   qty > 0,                                      'Please enter the number of copies.'],
    ['pq-bindingError',    pqEl('pq-binding').value !== '',              'Please choose a binding type.'],
    ['pq-printingSideError',pqEl('pq-printingSide').value !== '',        'Please choose single or double sided.'],
    ['pq-interiorPaperError',pqEl('pq-interiorPaper').value !== '',      'Please choose interior paper.'],
    ['pq-coverStockError', pqEl('pq-coverStock').value !== '',           'Please choose cover stock.'],
    ['pq-freeProofError',  pqEl('pq-freeProof').value !== '',            'Please choose a free proof option.'],
  ];

  checks.forEach(([errId, valid, msg]) => {
    pqSetError(errId, !valid, msg);
    if (!valid) ok = false;
  });

  const bw    = parseInt(pqEl('pq-bwPages').value, 10) || 0;
  const color = parseInt(pqEl('pq-colorPages').value, 10) || 0;
  const pagesOk = (bw + color) > 0;
  pqSetError('pq-pageCountsError', !pagesOk, 'Enter page count in B&W, Color, or both.');
  // pageCountsError covers two fields
  if (pqEl('pq-colorPages')) {
    if (!pagesOk) pqEl('pq-colorPages').classList.add('pq-invalid');
    else          pqEl('pq-colorPages').classList.remove('pq-invalid');
  }
  if (!pagesOk) ok = false;

  if (pqEl('pq-freeProof').value === 'yes' && qty < 10) {
    pqSetError('pq-freeProofError', true, 'Free proof requires 10+ copies.');
    ok = false;
  }

  return ok;
}

function pqGetSelectedFiles() {
  return Array.from(document.querySelectorAll('.pq-file-input'))
    .map(i => i.files && i.files[0] ? i.files[0] : null)
    .filter(Boolean);
}

async function pqSubmit() {
  const btn    = pqEl('pq-submit-btn');
  const status = pqEl('pq-status');

  status.textContent = '';
  status.style.color = '';

  if (pqEl('pq-website') && pqEl('pq-website').value.trim() !== '') return;

  if (!pqValidate()) {
    status.textContent = 'Please complete the highlighted fields.';
    status.style.color = 'var(--danger, #b91c1c)';
    return;
  }

  // Normalize page counts
  const bwEl    = pqEl('pq-bwPages');
  const colorEl = pqEl('pq-colorPages');
  if (bwEl.value.trim() === '' && colorEl.value.trim() !== '') bwEl.value = '0';
  if (colorEl.value.trim() === '' && bwEl.value.trim() !== '') colorEl.value = '0';

  btn.disabled = true;
  status.textContent = 'Submitting and uploading file(s)…';

  const fd = new FormData();
  fd.append('action', 'submit');
  fd.append('projectName',   pqEl('pq-project-name').value.trim());
  fd.append('name',          pqEl('pq-name').value.trim());
  fd.append('company',       pqEl('pq-company').value.trim());
  fd.append('email',         pqEl('pq-email').value.trim());
  fd.append('phone',         pqEl('pq-phone').value.trim());
  fd.append('trimSize',      pqEl('pq-trimSize').value);
  fd.append('quantity',      pqEl('pq-quantity').value);
  fd.append('binding',       pqEl('pq-binding').value);
  fd.append('bwPages',       bwEl.value);
  fd.append('colorPages',    colorEl.value);
  fd.append('printingSide',  pqEl('pq-printingSide').value);
  fd.append('interiorPaper', pqEl('pq-interiorPaper').value);
  fd.append('coverStock',    pqEl('pq-coverStock').value);
  fd.append('coverPrint',    pqEl('pq-coverPrint').value);
  fd.append('freeProof',     pqEl('pq-freeProof').value);
  fd.append('uvGloss',       pqEl('pq-uvGloss').value);
  fd.append('turnaround',    pqEl('pq-turnaround').value);
  fd.append('shipping',      pqEl('pq-shipping').value);
  fd.append('notes',         pqEl('pq-notes').value.trim());
  fd.append('website',       pqEl('pq-website') ? pqEl('pq-website').value : '');

  pqGetSelectedFiles().forEach(f => fd.append('projectFiles[]', f));

  try {
    const headers = {};
    if (authToken) headers['X-Auth-Token'] = authToken;
    const res  = await fetch(API + '/print_quote.php', { method: 'POST', body: fd, headers });
    const data = await res.json();

    btn.disabled = false;

    if (data.success) {
      const msg = data.fileCount > 0
        ? 'Quote submitted — ' + data.fileCount + ' file(s) uploaded. We\'ll be in touch soon.'
        : 'Quote submitted successfully. We\'ll be in touch soon.';
      status.textContent = msg;
      status.style.color = 'var(--success, #0f766e)';
      pqReset();
    } else {
      status.textContent = data.message || 'Submission failed. Please try again.';
      status.style.color = 'var(--danger, #b91c1c)';
    }
  } catch(e) {
    btn.disabled = false;
    status.textContent = 'Network error — please try again.';
    status.style.color = 'var(--danger, #b91c1c)';
  }
}

function pqReset() {
  ['pq-project-name','pq-name','pq-company','pq-email','pq-phone','pq-notes']
    .forEach(id => { if(pqEl(id)) pqEl(id).value = ''; });
  ['pq-trimSize','pq-binding','pq-printingSide','pq-interiorPaper','pq-coverStock',
   'pq-coverPrint','pq-freeProof','pq-uvGloss','pq-turnaround','pq-shipping']
    .forEach(id => { if(pqEl(id)) pqEl(id).value = ''; });
  ['pq-quantity','pq-bwPages','pq-colorPages']
    .forEach(id => { if(pqEl(id)) pqEl(id).value = ''; });
  document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.pq-invalid').forEach(el => el.classList.remove('pq-invalid'));
  pqResetEstimate();
  pqResetFileInputs();
  pqEnforceRules();
}

function pqResetFileInputs() {
  const container = pqEl('pq-file-inputs');
  if (!container) return;
  container.innerHTML = '<input type="file" id="pq-file-0" class="pq-file-input" style="margin-bottom:8px">';
  pqBindFileInput(container.querySelector('.pq-file-input'));
}

function pqBindFileInput(input) {
  if (!input) return;
  input.addEventListener('change', function() {
    const container = pqEl('pq-file-inputs');
    const inputs    = container.querySelectorAll('.pq-file-input');
    const last      = inputs[inputs.length - 1];
    if (last && last.files && last.files.length > 0) {
      const next    = document.createElement('input');
      next.type     = 'file';
      next.className= 'pq-file-input';
      next.style.marginBottom = '8px';
      container.appendChild(next);
      pqBindFileInput(next);
    }
  });
}

function initPrintQuoteView() {
  pqEnforceRules();
  // Bind file input if not already done
  const first = document.querySelector('.pq-file-input');
  if (first && !first._pqBound) {
    pqBindFileInput(first);
    first._pqBound = true;
  }
  // Pre-fill contact from logged-in user
  const nameEl  = pqEl('pq-name');
  const emailEl = pqEl('pq-email');
  if (nameEl && !nameEl.value && currentUser) {
    nameEl.value  = currentUser.full_name || currentUser.pen_name || '';
  }
  if (emailEl && !emailEl.value && currentUser) {
    emailEl.value = currentUser.email || '';
  }
}

// ── GRAPHICS & VIDEO ─────────────────────────────────────────

// Each sub-page has its own book selector (class .gv-book-selector); we read
// from whichever selector lives in the currently-active view.
function _gvCurrentBookId() {
  const activeView = document.querySelector('.view.active');
  const sel = activeView?.querySelector('.gv-book-selector');
  return parseInt(sel?.value || '0');
}

function gvFocusBookSelector(el) {
  const sel = el.closest('.view')?.querySelector('.gv-book-selector');
  if (!sel) return;
  sel.scrollIntoView({block:'center', behavior:'smooth'});
  setTimeout(() => sel.focus(), 400);
}

// ── Font picker (v54) ──────────────────────────────────────────
// Drives the font dropdown across graphics + trailer pages. Fetches the
// canonical registry from /api/fonts_registry.php once per page load,
// injects @font-face rules for the fonts that are actually uploaded so
// the dropdown can preview each option in its own font, and exposes
// renderFontPicker(containerEl, currentValue) for the views to call.
let _fontRegistry = null;  // { fonts: [...], default: 'Inter_18pt' }

async function initFontRegistry() {
  if (_fontRegistry) return _fontRegistry;
  try {
    const res  = await fetch(API + '/fonts_registry.php?action=list');
    const data = await res.json();
    if (!data || !data.success) return null;
    _fontRegistry = data;
    // Inject @font-face rules so the dropdown can preview-render each font.
    // Skips fonts whose preview_url is null (no .ttf uploaded yet).
    let css = '';
    (_fontRegistry.fonts || []).forEach(f => {
      if (!f.preview_url) return;
      css += '@font-face{font-family:"' + f.family + '";src:url("' + f.preview_url + '") format("truetype");font-display:swap;}\n';
    });
    if (css) {
      const styleEl = document.createElement('style');
      styleEl.id = 'font-registry-faces';
      styleEl.textContent = css;
      document.head.appendChild(styleEl);
    }
    return _fontRegistry;
  } catch (e) {
    console.warn('Font registry load failed', e);
    return null;
  }
}

// Resolves the chosen font's preview-friendly CSS family name. Fallback to
// system sans if the registry hasn't loaded yet or the family is unknown.
function _fontFamilyCss(family) {
  if (!family) return 'inherit';
  return '"' + family + '", -apple-system, system-ui, sans-serif';
}

// Renders a font picker inside `containerEl`. The picker writes the
// currently-chosen family slug to `containerEl.dataset.value` so the
// generation code can read it via container.dataset.value at submit time.
//
// Defaults to the registry's default family when no current value passed.
// Disabled options (font files not yet uploaded) are dimmed and marked so
// users see what's available without uploads.
async function renderFontPicker(containerEl, currentValue, label) {
  if (!containerEl) return;
  await initFontRegistry();
  if (!_fontRegistry) {
    containerEl.innerHTML = '<div style="font-size:12px;color:var(--ink-soft)">Font picker unavailable</div>';
    return;
  }
  const fonts   = _fontRegistry.fonts || [];
  const fallback= _fontRegistry.default || 'Inter_18pt';
  const chosen  = currentValue || containerEl.dataset.value || fallback;
  containerEl.dataset.value = chosen;

  const labelHtml = label ? '<label class="field-label">' + escapeHtml(label) + '</label>' : '';

  // Build options grouped by category, with disabled state for missing files.
  const grouped = {};
  fonts.forEach(f => {
    const cat = f.category || 'other';
    (grouped[cat] = grouped[cat] || []).push(f);
  });
  const categoryOrder = ['serif', 'sans', 'display', 'script', 'other'];
  const categoryLabels = { serif: 'Serif', sans: 'Sans-serif', display: 'Display', script: 'Script / handwritten', other: 'Other' };

  let optionsHtml = '';
  categoryOrder.forEach(cat => {
    const list = grouped[cat];
    if (!list || !list.length) return;
    optionsHtml += '<optgroup label="' + escapeHtml(categoryLabels[cat] || cat) + '">';
    list.forEach(f => {
      const anyInstalled = f.installed && Object.values(f.installed).some(Boolean);
      const disabled = anyInstalled ? '' : ' disabled';
      const suffix   = anyInstalled ? '' : ' (not uploaded)';
      const selected = (f.family === chosen) ? ' selected' : '';
      optionsHtml += '<option value="' + escapeHtml(f.family) + '" data-use="' + escapeHtml(f.use_case || '') + '"' + disabled + selected + '>' +
                      escapeHtml(f.label) + suffix +
                     '</option>';
    });
    optionsHtml += '</optgroup>';
  });

  const id = containerEl.id || ('font-picker-' + Math.floor(Math.random() * 1e6));
  containerEl.innerHTML =
    labelHtml +
    '<div class="font-picker-wrap">' +
      '<div class="font-picker-preview" id="' + id + '-preview" style="font-family:' + _fontFamilyCss(chosen) + '">' + escapeHtml(_fontPickerLabelFor(chosen)) + '</div>' +
      '<select class="font-picker-select" id="' + id + '-select" onchange="_handleFontPickerChange(\'' + id + '\')">' +
        optionsHtml +
      '</select>' +
      '<div id="' + id + '-use" style="font-size:11.5px;color:var(--ink-soft);margin-top:2px">' + escapeHtml(_fontPickerUseCaseFor(chosen)) + '</div>' +
    '</div>';
}

// Returns the human label for a family slug from the cached registry.
function _fontPickerLabelFor(family) {
  if (!_fontRegistry) return family;
  const f = (_fontRegistry.fonts || []).find(x => x.family === family);
  return f ? f.label : family;
}
function _fontPickerUseCaseFor(family) {
  if (!_fontRegistry) return '';
  const f = (_fontRegistry.fonts || []).find(x => x.family === family);
  return f ? (f.use_case || '') : '';
}

// onchange handler for the select. Updates the preview div + writes the
// chosen family to the container's dataset so the generation code reads it
// via container.dataset.value at submit.
function _handleFontPickerChange(pickerId) {
  const select  = document.getElementById(pickerId + '-select');
  const preview = document.getElementById(pickerId + '-preview');
  const useDiv  = document.getElementById(pickerId + '-use');
  if (!select) return;
  const family = select.value;
  // The container is whichever element wraps the select.
  const container = select.closest('[data-value]') || select.parentElement?.parentElement;
  if (container) container.dataset.value = family;
  if (preview) {
    preview.style.fontFamily = _fontFamilyCss(family);
    preview.textContent      = _fontPickerLabelFor(family);
    preview.classList.remove('empty');
  }
  if (useDiv) useDiv.textContent = _fontPickerUseCaseFor(family);
}

// Reads the current font choice out of a picker container. Returns null when
// the container isn't a picker or no font is selected.
function getFontPickerValue(containerEl) {
  if (!containerEl) return null;
  return containerEl.dataset.value || null;
}

function initGraphicsView() {
  const opts = '<option value="0">No specific book</option>' +
    (window._books || []).map(b => '<option value="' + b.id + '">' + escapeHtml(b.title) + '</option>').join('');
  // Use the topbar's current book as the fallback when a per-page selector
  // doesn't have its own prior selection — keeps the two in lockstep on
  // first visit. After that, the change listener below holds them together.
  const topbarRaw = document.getElementById('bookSelector')?.value || '';
  const fallback  = topbarRaw === '' ? '0' : topbarRaw;
  document.querySelectorAll('.gv-book-selector').forEach(sel => {
    const prev = sel.value;
    sel.innerHTML = opts;
    sel.value = (prev && prev !== '0') ? prev : fallback;
  });
  loadImageQuota();
  gvUpdateBriefs();
  // Font pickers (v54). Render once per page; each container's data-value
  // attribute holds the default font for that feature (set in the markup).
  ['gv-cover', 'gv-social', 'gv-quote', 'gv-event'].forEach(prefix => {
    const c = document.getElementById(prefix + '-font-picker');
    if (c && !c.dataset.rendered) {
      renderFontPicker(c, c.dataset.value || '', 'Font');
      c.dataset.rendered = '1';
    }
  });
}

// ── Book-selector sync (v53) ──────────────────────────────────
// The topbar #bookSelector and the per-page .gv-book-selector elements
// could be set to different books, so the image generator followed one
// while the post-composer link followed the other. This handler keeps
// them in lockstep: change one, all others follow, and each one's inline
// onchange fires so existing logic (spSyncLinkFromBook on the topbar,
// gvUpdateBriefs / tvFillFromBook on the per-page selectors) keeps working.
// A flag guards against re-entry — without it, each dispatched change
// would trigger the listener again.
let _bookSelectorSyncing = false;

document.addEventListener('change', function (ev) {
  if (_bookSelectorSyncing) return;
  const target = ev.target;
  if (!target || target.tagName !== 'SELECT') return;
  const isTopbar = target.id === 'bookSelector';
  const isGv     = target.classList && target.classList.contains('gv-book-selector');
  if (!isTopbar && !isGv) return;

  const raw = target.value || '';
  _bookSelectorSyncing = true;
  try {
    if (!isTopbar) {
      const top = document.getElementById('bookSelector');
      // Topbar uses '' to mean "no book"; gv selectors use '0'.
      const wantTop = raw === '0' ? '' : raw;
      if (top && top.value !== wantTop) {
        top.value = wantTop;
        top.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }
    document.querySelectorAll('.gv-book-selector').forEach(sel => {
      if (sel === target) return;
      const wantGv = raw === '' ? '0' : raw;
      if (sel.value !== wantGv) {
        sel.value = wantGv;
        sel.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  } finally {
    _bookSelectorSyncing = false;
  }
});

const _gvPrompts = {
  cover: () => {
    const bookCtx = _gvBookLine();
    return 'Write a visual design brief for a book cover.' + bookCtx +
      '\n\nInclude: mood/tone, color palette suggestion, imagery concept, typography feel, and one sentence of art direction. Keep it under 120 words.';
  },
  social: () => {
    const platform = document.getElementById('gv-social-platform').value;
    const bookCtx = _gvBookLine();
    const angles = ['curiosity','urgency','emotion','intrigue','aspiration','community','mystery','excitement'];
    const angle = angles[Math.floor(Math.random() * angles.length)];
    return 'Write text overlay copy for a ' + platform + ' graphic promoting a book.' + bookCtx +
      '\n\nProvide 3 options, each taking a distinctly different creative angle. Lead with a ' + angle + '-driven approach for at least one option. Make them feel fresh and varied — avoid generic marketing phrases.' +
      '\n\nEach option: a short headline (5–8 words) and a subline (10–15 words). Format as:\nOption 1:\nHeadline: ...\nSubline: ...';
  },
  quote: () => {
    const source = document.getElementById('gv-quote-source').value.trim();
    const bookCtx = _gvBookLine();
    // Random register per generation — same variety pattern as social/event,
    // so regenerating doesn't return the same 3 quotes every time.
    const registers = ['defiant','tender','wry','nostalgic','provocative','contemplative','urgent','bittersweet'];
    const register = registers[Math.floor(Math.random() * registers.length)];
    return 'Write 3 compelling pull-quote options for a shareable graphic.' + bookCtx +
      (source ? '\nQuote source hint: ' + source : '') +
      '\n\nEach option should take a distinctly different emotional angle — make at least one ' + register + ' in tone. ' +
      'Avoid the most obvious lines; surprise a reader who has seen a hundred book quote cards.' +
      '\n\nEach quote: 10–25 words, emotionally resonant, works as a standalone image. Output one per line, no numbering.';
  },
  event: () => {
    const bookCtx = _gvBookLine();
    const type    = document.getElementById('gv-event-type')?.value?.trim()    || '';
    const date    = document.getElementById('gv-event-date')?.value?.trim()    || '';
    const time    = document.getElementById('gv-event-time')?.value?.trim()    || '';
    const venue   = document.getElementById('gv-event-venue')?.value?.trim()   || '';
    const address = document.getElementById('gv-event-address')?.value?.trim() || '';
    const city    = document.getElementById('gv-event-city')?.value?.trim()    || '';
    const phone   = document.getElementById('gv-event-phone')?.value?.trim()   || '';
    const dateFmt = date ? new Date(date + 'T00:00').toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'}) : '';
    const parts = [];
    if (type)    parts.push('Event type: ' + type);
    if (dateFmt) parts.push('Date: '       + dateFmt);
    if (time)    parts.push('Time: '       + time);
    if (venue)   parts.push('Venue: '      + venue);
    if (address) parts.push('Address: '    + address);
    if (city)    parts.push('Location: '   + city);
    if (phone)   parts.push('Phone: '      + phone);
    const detailsBlock = parts.length ? '\n\nEvent details:\n' + parts.join('\n') : '';
    const angles = ['warm invitation','community gathering','intimate access','can\'t-miss moment','behind-the-book curiosity'];
    const angle  = angles[Math.floor(Math.random() * angles.length)];
    return 'Write 3 promotional copy options for an author event announcement that can be posted to social media or used as flyer body copy.' + bookCtx + detailsBlock +
      '\n\nLead at least one option with a ' + angle + ' angle. Make them feel fresh and personal — avoid stock event-marketing phrases.' +
      '\n\nEach option: a short headline (5–9 words) and a subline (15–25 words) with the date/venue/CTA woven in naturally. Format as:\nOption 1:\nHeadline: ...\nSubline: ...';
  },
  youtube: () => {
    const topic = document.getElementById('gv-yt-topic').value.trim();
    const bookCtx = _gvBookLine();
    return 'Write 5 YouTube thumbnail text options for an author video.' + bookCtx +
      (topic ? '\nVideo topic: ' + topic : '') +
      '\n\nEach option: 3–6 bold words that create curiosity or urgency. Output one per line, no numbering.';
  },
  trailer: () => {
    const bookCtx = _gvBookLine();
    return 'Write a 30–60 second book trailer narration script.' + bookCtx +
      '\n\nStructure: open with a hook (tension/question), build intrigue, reveal just enough, end with title + call to action. ' +
      'Format with [SCENE] cues and NARRATOR lines. Aim for ~90 words of spoken text.';
  },
};

function _gvBookLine() {
  const bookId = _gvCurrentBookId();
  if (!bookId || !window._books) return '';
  const book = window._books.find(b => b.id == bookId);
  if (!book) return '';
  let ctx = '\n\nBook: ' + book.title;
  if (book.author)      ctx += ' | Author: ' + book.author;
  if (book.genre)       ctx += ' | Genre: ' + book.genre;
  if (book.description) ctx += '\nDescription: ' + book.description.substring(0, 400);
  return ctx;
}

async function gvGenerate(type) {
  const outEl = document.getElementById('gv-out-' + type);
  if (!outEl) return;

  const promptFn = _gvPrompts[type];
  if (!promptFn) return;

  outEl.style.display = 'block';
  outEl.textContent = 'Generating…';

  const bookId = _gvCurrentBookId();

  try {
    // graphics_copy routes to the fast model class — these are short overlay
    // copy tasks. Trailer scripts are longer-form, so they stay on 'custom'.
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({
        task: type === 'trailer' ? 'custom' : 'graphics_copy',
        prompt: promptFn(),
        book_id: bookId,
      }),
    });
    if (data.success && data.draft) {
      if (type === 'social') {
        gvRenderSocialCopy(data.draft, outEl);
      } else if (type === 'quote') {
        gvRenderQuoteCopy(data.draft, outEl);
      } else if (type === 'event') {
        gvRenderEventCopy(data.draft, outEl);
      } else {
        outEl.innerHTML = '<button class="gv-copy-btn" onclick="gvCopy(this)">Copy</button>' +
          '<span class="gv-text">' + escapeHtml(data.draft) + '</span>';
      }
      if (data.quota) updateQuotaMeter(data.quota);
    } else {
      outEl.textContent = data.message || 'Generation failed — try again';
    }
  } catch(e) {
    outEl.textContent = 'Request failed — check your connection';
  }
}

function gvParseSocialOptions(text) {
  const options = [];
  const blocks = text.split(/Option\s*\d+\s*:/i).filter(b => b.trim());
  for (const block of blocks) {
    const headlineMatch = block.match(/Headline\s*:\s*(.+)/i);
    const sublineMatch  = block.match(/Subline\s*:\s*(.+)/i);
    if (headlineMatch || sublineMatch) {
      options.push({
        headline: (headlineMatch?.[1] || '').trim(),
        subline:  (sublineMatch?.[1]  || '').trim(),
      });
    }
  }
  return options.length ? options : [{ headline: text.trim(), subline: '' }];
}

function gvRenderSocialCopy(text, outEl) {
  const options = gvParseSocialOptions(text);
  outEl.style.display = 'block';
  outEl.innerHTML = '<div style="font-size:11px;color:var(--ink-soft);margin-bottom:6px">Click an option to select it, then edit as needed below.</div>' +
    options.map((opt, i) =>
      '<div class="gv-copy-option" onclick="gvSelectSocialOption(this)"' +
        ' data-headline="' + escapeHtml(opt.headline) + '"' +
        ' data-subline="'  + escapeHtml(opt.subline)  + '">' +
        '<div class="gv-copy-option-headline">' + escapeHtml(opt.headline) + '</div>' +
        (opt.subline ? '<div class="gv-copy-option-subline">' + escapeHtml(opt.subline) + '</div>' : '') +
      '</div>'
    ).join('');
}

function gvSelectSocialOption(el) {
  el.closest('.gv-output').querySelectorAll('.gv-copy-option')
    .forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  const headline = el.getAttribute('data-headline');
  const subline  = el.getAttribute('data-subline');
  const ta = document.getElementById('gv-social-copy-text');
  if (ta) {
    ta.value = subline ? headline + '\n\n' + subline : headline;
    gvSocialUpdatePreview();
  }
}

function gvRenderEventCopy(text, outEl) {
  const options = gvParseSocialOptions(text);
  outEl.style.display = 'block';
  outEl.innerHTML = '<div style="font-size:11px;color:var(--ink-soft);margin-bottom:6px">Click an option to select it, then edit as needed below.</div>' +
    options.map(opt =>
      '<div class="gv-copy-option" onclick="gvSelectEventOption(this)"' +
        ' data-headline="' + escapeHtml(opt.headline) + '"' +
        ' data-subline="'  + escapeHtml(opt.subline)  + '">' +
        '<div class="gv-copy-option-headline">' + escapeHtml(opt.headline) + '</div>' +
        (opt.subline ? '<div class="gv-copy-option-subline">' + escapeHtml(opt.subline) + '</div>' : '') +
      '</div>'
    ).join('');
}

function gvSelectEventOption(el) {
  el.closest('.gv-output').querySelectorAll('.gv-copy-option')
    .forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  const headline = el.getAttribute('data-headline');
  const subline  = el.getAttribute('data-subline');
  const ta = document.getElementById('gv-event-copy-text');
  if (ta) {
    ta.value = subline ? headline + '\n\n' + subline : headline;
    gvEventUpdatePreview();
  }
}

// Assemble the structured event fields into the multi-line text block that
// the server overlays on the BOTTOM panel of an event flyer. Uppercase lines
// get the gold accent color (date, time); mixed-case lines get warm-white
// body treatment (venue, address). The first line is rendered LARGEST per
// overlayTextOnImage's heuristic — putting the date first makes it the focal
// fact, which is what people most need to see on a flyer.
function gvBuildEventFacts() {
  const date    = document.getElementById('gv-event-date')?.value?.trim()    || '';
  const time    = document.getElementById('gv-event-time')?.value?.trim()    || '';
  const venue   = document.getElementById('gv-event-venue')?.value?.trim()   || '';
  const address = document.getElementById('gv-event-address')?.value?.trim() || '';
  const city    = document.getElementById('gv-event-city')?.value?.trim()    || '';
  const phone   = document.getElementById('gv-event-phone')?.value?.trim()   || '';
  const link    = document.getElementById('gv-event-link')?.value?.trim()    || '';

  const dateFmt = date
    ? new Date(date + 'T00:00').toLocaleDateString('en-US',
        { weekday:'long', month:'long', day:'numeric' }).toUpperCase()
    : '';

  const lines = [];
  if (dateFmt)         lines.push(dateFmt);                          // largest, gold accent
  if (time)            lines.push(time.toUpperCase());               // gold accent
  if (venue)           lines.push(venue);                            // body white
  if (address)         lines.push(address);                          // body white
  if (city)            lines.push(city);                             // body white
  if (phone)           lines.push(phone);                            // body white
  if (link)            lines.push(link);                             // body white
  return lines.join('\n');
}

function gvEventUpdatePreview() {
  const preview = document.getElementById('gv-event-preview');
  if (!preview) return;
  const content = document.getElementById('gv-event-copy-text')?.value || '';
  const link    = document.getElementById('gv-event-link')?.value?.trim() || '';
  if (!content.trim() && !link) {
    preview.innerHTML = '<span style="color:var(--ink-soft);font-style:italic">Type a post above to see how it\'ll look with the link.</span>';
    return;
  }
  let html = escapeHtml(content);
  if (link) html += '\n\n<span style="color:var(--accent);word-break:break-all">' + escapeHtml(link) + '</span>';
  preview.innerHTML = html;
}

function gvCopyEventText(btn) {
  const text = document.getElementById('gv-event-copy-text')?.value?.trim() || '';
  if (!text) return;
  const link = document.getElementById('gv-event-link')?.value?.trim() || '';
  const final = link ? text + '\n\n' + link : text;
  navigator.clipboard.writeText(final).then(() => {
    const orig = btn.textContent;
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = orig, 1500);
  });
}

function gvSendEventToSocialPosts() {
  const text   = document.getElementById('gv-event-copy-text')?.value?.trim() || '';
  const link   = document.getElementById('gv-event-link')?.value?.trim() || '';
  const imgUrl = document.getElementById('gv-img-event-el')?.getAttribute('data-public-url') || '';

  const postContent = document.getElementById('post-content');
  const postImage   = document.getElementById('post-image');
  const postLink    = document.getElementById('post-link');

  if (postContent) postContent.value = text;
  if (postImage && imgUrl) postImage.value = imgUrl;
  if (postLink) postLink.value = link;
  spUpdateCharCount();
  updatePostPreview();

  navigate('social');
  toast('Copy and image sent to Social Posts');
}

function gvRenderQuoteCopy(text, outEl) {
  const quotes = text.split(/\n+/).map(s => s.replace(/^[-•*\d.\s"]+/, '').replace(/["]+$/, '').trim()).filter(s => s.length > 5);
  outEl.style.display = 'block';
  outEl.innerHTML = '<div style="font-size:11px;color:var(--ink-soft);margin-bottom:6px">Click a quote to use it, then edit as needed above.</div>' +
    quotes.map(q =>
      '<div class="gv-copy-option" onclick="gvSelectQuoteOption(this)" data-quote="' + escapeHtml(q) + '">' +
        '<div class="gv-copy-option-headline">' + escapeHtml(q) + '</div>' +
      '</div>'
    ).join('');
}

function gvSelectQuoteOption(el) {
  el.closest('.gv-output').querySelectorAll('.gv-copy-option')
    .forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  const ta = document.getElementById('gv-quote-copy-text');
  if (ta) ta.value = el.getAttribute('data-quote') || '';
}

function gvCopyQuoteText(btn) {
  const text = document.getElementById('gv-quote-copy-text')?.value?.trim() || '';
  if (!text) return;
  navigator.clipboard.writeText(text).then(() => {
    const orig = btn.textContent;
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = orig, 1500);
  });
}

function gvCopySocialText(btn) {
  const text = document.getElementById('gv-social-copy-text')?.value?.trim() || '';
  if (!text) return;
  const link = document.getElementById('gv-social-link')?.value?.trim() || '';
  const final = link ? text + '\n\n' + link : text;
  navigator.clipboard.writeText(final).then(() => {
    const orig = btn.textContent;
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = orig, 1500);
  });
}

function gvSendToSocialPosts() {
  const text = document.getElementById('gv-social-copy-text')?.value?.trim() || '';
  const link = document.getElementById('gv-social-link')?.value?.trim() || '';
  const imgUrl = document.getElementById('gv-img-social-el')?.getAttribute('data-public-url') || '';

  const postContent = document.getElementById('post-content');
  const postImage   = document.getElementById('post-image');
  const postLink    = document.getElementById('post-link');

  if (postContent) postContent.value = text;
  if (postImage && imgUrl) postImage.value = imgUrl;
  if (postLink) postLink.value = link;
  spUpdateCharCount();
  updatePostPreview();

  navigate('social');
  toast('Copy and image sent to Social Posts');
}

function gvCopy(btn) {
  const text = btn.parentElement.querySelector('.gv-text')?.textContent || '';
  navigator.clipboard.writeText(text).then(() => {
    const orig = btn.textContent;
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = orig, 1500);
  });
}

async function gvGenerateImage(type, btn) {
  const imgWrap = document.getElementById('gv-img-' + type);
  const imgEl   = document.getElementById('gv-img-' + type + '-el');
  const errEl   = document.getElementById('gv-img-' + type + '-err');
  if (!imgWrap || !imgEl) return;

  // Soft check: warn (don't block) when no book is selected. AI results are
  // dramatically better with a book — title, author, description, cover all flow in.
  const bookId = _gvCurrentBookId();
  if (bookId === 0) {
    const proceed = confirm(
      'No book is selected.\n\n' +
      'AI image results will be much weaker without book context — no cover composite, ' +
      'no title/author overlay, no description tagline.\n\n' +
      'Generate anyway?'
    );
    if (!proceed) return;
  }

  const origHTML = btn.innerHTML;
  btn.disabled = true;
  btn.textContent = 'Generating…';
  imgWrap.style.display = 'none';
  errEl.style.display = 'none';
  let extra = '';
  if (type === 'social') {
    extra = document.getElementById('gv-social-platform')?.value || 'instagram';
  } else if (type === 'event') {
    // Event: only the 'square' format value matters server-side (it switches the
    // prompt's text-overlay zones from top/bottom thirds to portrait layout).
    // Flyer/poster both portrait, so anything else falls through to portrait.
    extra = (document.getElementById('gv-event-format')?.value === 'square') ? 'square' : '';
  }
  const styleBrief = gvAssembleBrief(type);

  // Pull the "Your copy" text (event details, CTAs, promo content the user
  // wrote) into the image-generation brief. Style direction is supporting,
  // user-written content is primary — so it leads.
  let userCopy = '';
  if (type === 'social' || type === 'quote') {
    userCopy = document.getElementById('gv-' + type + '-copy-text')?.value?.trim() || '';
  } else if (type === 'event') {
    // Event userCopy = headline only (the user's "Your copy") — this is what
    // goes through haiku for the TOP text panel overlay. Image-gen prompt also
    // sees light event-context (event type / venue / city) for visual mood —
    // signing in a bookstore vs. launch party reads differently.
    userCopy = document.getElementById('gv-event-copy-text')?.value?.trim() || '';
  }
  const brief = userCopy
    ? (styleBrief ? userCopy + '. ' + styleBrief : userCopy)
    : styleBrief;

  // Event-only: build the structured-facts block for the bottom text panel
  // overlay. Pre-formatted multi-line; uppercase lines get the gold accent
  // color server-side, mixed-case lines get warm-white body treatment.
  const eventFacts = (type === 'event') ? gvBuildEventFacts() : '';

  // Font choice from this page's picker (v54). Falls through to empty string
  // when the picker isn't on this page or hasn't loaded — image_gen.php then
  // uses its own default (Inter for overlays, Playfair for quote).
  const fontPicker = document.getElementById('gv-' + type + '-font-picker');
  const fontFamily = fontPicker ? (fontPicker.dataset.value || '') : '';

  try {
    const data = await api('/image_gen.php', {
      method: 'POST',
      body: JSON.stringify({
        feature: type,
        book_id: bookId,
        extra,
        brief,                   // legacy combined brief (cover uses it directly)
        text_content: userCopy,  // user prose → goes through Haiku for text overlay
        style_brief: styleBrief, // brief-panel directives → DALL-E visual direction
        event_facts: eventFacts, // event-only: bottom-panel structured facts
        font_family: fontFamily, // v54 — user's font pick from the page's font picker
        size: gvDalleSize(type),
      }),
    });
    if (data.success && data.image_url) {
      imgEl.src = data.image_url;
      imgEl.onload = () => { imgWrap.style.display = 'block'; };
      imgEl.setAttribute('data-url', data.image_url);
      if (data.image_public_url) imgEl.setAttribute('data-public-url', data.image_public_url);
      else imgEl.removeAttribute('data-public-url');
      if (data.image_count !== undefined) {
        updateImageQuotaDisplay(data.image_count, data.image_limit);
      }
      gvStartCooldown(btn, origHTML, 15);
    } else {
      btn.disabled = false;
      btn.innerHTML = origHTML;
      if (data.error_code === 'image_quota_exceeded') {
        errEl.innerHTML = escapeHtml(data.message || 'Image limit reached') +
          (currentUser?.plan === 'starter'
            ? ' <a href="#" onclick="navigate(\'pricing\');return false" style="color:var(--accent)">Upgrade →</a>'
            : '');
      } else {
        errEl.textContent = data.message || 'Generation failed — try again';
      }
      errEl.style.display = 'block';
    }
  } catch(e) {
    btn.disabled = false;
    btn.innerHTML = origHTML;
    errEl.textContent = 'Request failed — check your connection';
    errEl.style.display = 'block';
  }
}

function gvStartCooldown(btn, origHTML, seconds) {
  btn.disabled = true;
  let remaining = seconds;
  btn.textContent = 'Wait ' + remaining + 's…';
  const interval = setInterval(() => {
    remaining--;
    if (remaining <= 0) {
      clearInterval(interval);
      btn.disabled = false;
      btn.innerHTML = origHTML;
    } else {
      btn.textContent = 'Wait ' + remaining + 's…';
    }
  }, 1000);
}

async function gvDownloadImage(type, filename) {
  const imgEl = document.getElementById('gv-img-' + type + '-el');
  // Prefer the server-saved copy (JPEG, ~300 KB — same file that gets posted)
  // over the in-page display copy (PNG data URL, ~2.5 MB). Fall back to the
  // display copy if the server save failed. Filename extension follows the
  // actual file so a .jpg never downloads labeled .png.
  const pub   = imgEl?.getAttribute('data-public-url') || '';
  const url   = pub || imgEl?.getAttribute('data-url') || imgEl?.src || '';
  if (!url) return;
  const extM = pub.match(/\.(png|jpe?g|gif|webp)(\?|$)/i);
  if (extM && filename) filename = filename.replace(/\.[a-z0-9]+$/i, '.' + extM[1].toLowerCase());
  try {
    const res  = await fetch(url);
    const blob = await res.blob();
    const blobUrl = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = filename || 'image.png';
    a.click();
    URL.revokeObjectURL(blobUrl);
  } catch(e) {
    window.open(url, '_blank');
  }
}

async function loadImageQuota() {
  try {
    const data = await api('/image_gen.php?action=status');
    if (data.success) updateImageQuotaDisplay(data.count, data.limit);
  } catch(e) {}
}

function updateImageQuotaDisplay(count, limit) {
  const els = document.querySelectorAll('.gv-image-quota');
  if (!els.length) return;
  let html = '';
  if (limit && limit < 999) {
    const remaining = Math.max(0, limit - count);
    html = 'Images this month: <strong>' + count + ' of ' + limit + '</strong>' +
      (remaining === 0
        ? ' — <a href="#" onclick="navigate(\'pricing\');return false" style="color:var(--accent)">Upgrade for more</a>'
        : ' (' + remaining + ' remaining)');
  }
  els.forEach(el => el.innerHTML = html);
}

const _gvGenreDefaults = {
  'thriller':         { style:'Cinematic',     mood:'Tense & dramatic',   palette:'Dark & rich',        lighting:'Dark & moody',       socialMood:'Dramatic',       quoteAtm:'Dark & moody',  quoteBg:'Stone & rock',       eventStyle:'Bold & graphic',  eventMood:'Exciting',          eventPalette:'High contrast' },
  'mystery':          { style:'Cinematic',     mood:'Dark & mysterious',  palette:'Cool blues & grays', lighting:'Dark & moody',       socialMood:'Mysterious',     quoteAtm:'Dark & moody',  quoteBg:'Water & mist',       eventStyle:'Photographic',    eventMood:'Literary',          eventPalette:'Cool tones' },
  'horror':           { style:'Photorealistic',mood:'Eerie & unsettling', palette:'Dark & rich',        lighting:'Dark & moody',       socialMood:'Dramatic',       quoteAtm:'Dark & moody',  quoteBg:'Stone & rock',       eventStyle:'Bold & graphic',  eventMood:'Exciting',          eventPalette:'High contrast' },
  'romance':          { style:'Painterly',     mood:'Romantic',           palette:'Warm earth tones',   lighting:'Soft & natural',     socialMood:'Warm & inviting',quoteAtm:'Romantic',      quoteBg:'Fabric & textile',   eventStyle:'Painterly',       eventMood:'Intimate & warm',   eventPalette:'Warm earth tones' },
  'fantasy':          { style:'Digital art',   mood:'Whimsical',          palette:'Bright & vibrant',   lighting:'Dramatic spotlight', socialMood:'Exciting',       quoteAtm:'Ethereal',      quoteBg:'Abstract gradient',  eventStyle:'Illustrated',     eventMood:'Festive & inviting',eventPalette:'Vibrant' },
  'science fiction':  { style:'Digital art',   mood:'Tense & dramatic',   palette:'Cool blues & grays', lighting:'Neon & artificial',  socialMood:'Exciting',       quoteAtm:'Abstract',      quoteBg:'Abstract gradient',  eventStyle:'Bold & graphic',  eventMood:'Exciting',          eventPalette:'Cool tones' },
  'sci-fi':           { style:'Digital art',   mood:'Tense & dramatic',   palette:'Cool blues & grays', lighting:'Neon & artificial',  socialMood:'Exciting',       quoteAtm:'Abstract',      quoteBg:'Abstract gradient',  eventStyle:'Bold & graphic',  eventMood:'Exciting',          eventPalette:'Cool tones' },
  'literary fiction': { style:'Painterly',     mood:'Warm & inviting',    palette:'Muted & subtle',     lighting:'Soft & natural',     socialMood:'Elegant',        quoteAtm:'Cozy & warm',   quoteBg:'Wood & organic',     eventStyle:'Painterly',       eventMood:'Literary',          eventPalette:'Muted & classic' },
  'biography':        { style:'Photorealistic',mood:'Warm & inviting',    palette:'Warm earth tones',   lighting:'Golden hour',        socialMood:'Warm & inviting',quoteAtm:'Cozy & warm',   quoteBg:'Fabric & textile',   eventStyle:'Photographic',    eventMood:'Professional',      eventPalette:'Warm earth tones' },
  'non-fiction':      { style:'Minimalist',    mood:'Peaceful & serene',  palette:'Muted & subtle',     lighting:'Bright & airy',      socialMood:'Elegant',        quoteAtm:'Nature-inspired',quoteBg:'Wood & organic',    eventStyle:'Minimalist',      eventMood:'Professional',      eventPalette:'Muted & classic' },
  "children's":       { style:'Illustrated',   mood:'Whimsical',          palette:'Bright & vibrant',   lighting:'Bright & airy',      socialMood:'Playful',        quoteAtm:'Ethereal',      quoteBg:'Soft bokeh',         eventStyle:'Illustrated',     eventMood:'Festive & inviting',eventPalette:'Vibrant' },
  'young adult':      { style:'Digital art',   mood:'Tense & dramatic',   palette:'Bright & vibrant',   lighting:'Dramatic spotlight', socialMood:'Exciting',       quoteAtm:'Ethereal',      quoteBg:'Abstract gradient',  eventStyle:'Bold & graphic',  eventMood:'Exciting',          eventPalette:'Vibrant' },
};

function _gvSelectValue(id, value) {
  const el = document.getElementById(id);
  if (!el || !value) return;
  const match = Array.from(el.options).find(o => o.text === value || o.value === value);
  if (match) el.value = match.value;
}

// Update the per-card "Working with: <book>" / "No book selected" indicators.
// Called from gvUpdateBriefs() on every book-selection change (and initial load).
function gvUpdateBookStatus() {
  // Each sub-page has its own selector; update each banner using its sibling selector.
  document.querySelectorAll('.gv-book-status').forEach(el => {
    const sel = el.closest('.view')?.querySelector('.gv-book-selector');
    const bookId = parseInt(sel?.value || '0');
    if (bookId > 0) {
      const bookText = sel.options[sel.selectedIndex]?.text || '';
      el.style.background = 'var(--accent-faint)';
      el.style.color = 'var(--accent)';
      el.style.border = '1px solid transparent';
      el.innerHTML = '<span style="margin-right:5px">📖</span>Working with: <strong>' + escapeHtml(bookText) + '</strong>';
    } else {
      el.style.background = '#fff8e1';
      el.style.color = '#7a5300';
      el.style.border = '1px solid #f0d97a';
      el.innerHTML = '<span style="margin-right:5px">⚠️</span><strong>No book selected.</strong> ' +
        '<a href="#" onclick="gvFocusBookSelector(this);return false" style="color:#7a5300;text-decoration:underline">Choose one above ↑</a> for the AI to work with this book\'s details and cover.';
    }
  });
}

function gvUpdateBriefs() {
  const bookId = _gvCurrentBookId();
  const book   = bookId ? (window._books || []).find(b => b.id == bookId) : null;
  const genre  = (book?.genre || '').toLowerCase().trim();
  const d      = _gvGenreDefaults[genre] || {};

  gvUpdateBookStatus();

  _gvSelectValue('gv-cover-style',    d.style   || 'Cinematic');
  _gvSelectValue('gv-cover-mood',     d.mood    || 'Dark & mysterious');
  _gvSelectValue('gv-cover-palette',  d.palette || 'Dark & rich');
  _gvSelectValue('gv-cover-lighting', d.lighting|| 'Dark & moody');
  _gvSelectValue('gv-social-style',   'Photographic');
  _gvSelectValue('gv-social-mood',    d.socialMood || 'Exciting');
  _gvSelectValue('gv-social-palette', 'Vibrant');
  _gvSelectValue('gv-quote-atmosphere', d.quoteAtm || 'Ethereal');
  _gvSelectValue('gv-quote-bg',         d.quoteBg  || 'Soft bokeh');
  _gvSelectValue('gv-quote-palette',    'Dark (for white text)');
  _gvSelectValue('gv-event-style',      d.eventStyle  || 'Photographic');
  _gvSelectValue('gv-event-mood',       d.eventMood   || 'Festive & inviting');
  _gvSelectValue('gv-event-palette',    d.eventPalette|| 'Warm earth tones');

  const linkEl = document.getElementById('gv-social-link');
  if (linkEl && !linkEl.value) {
    linkEl.value = cleanAmazonUrl(book?.amazon_url || '') || currentUser?.website || '';
  }
  const evLinkEl = document.getElementById('gv-event-link');
  if (evLinkEl && !evLinkEl.value) {
    evLinkEl.value = currentUser?.website || cleanAmazonUrl(book?.amazon_url || '');
  }
}

function gvAssembleBrief(type) {
  const fieldSets = {
    cover: [
      { id:'gv-cover-size',       label:'Format' },
      { id:'gv-cover-style',      label:'Style' },
      { id:'gv-cover-mood',       label:'Mood' },
      { id:'gv-cover-palette',    label:'Color palette' },
      { id:'gv-cover-lighting',   label:'Lighting' },
      { id:'gv-cover-complexity', label:'Composition' },
      { id:'gv-cover-colors',     label:'Colors to include' },
      { id:'gv-cover-include',    label:'Include in image' },
    ],
    social: [
      { id:'gv-social-style',      label:'Style' },
      { id:'gv-social-mood',       label:'Mood' },
      { id:'gv-social-palette',    label:'Color palette' },
      { id:'gv-social-complexity', label:'Composition' },
      { id:'gv-social-colors',     label:'Colors to include' },
      { id:'gv-social-include',    label:'Include in image' },
    ],
    quote: [
      { id:'gv-quote-atmosphere',  label:'Atmosphere' },
      { id:'gv-quote-bg',          label:'Background' },
      { id:'gv-quote-palette',     label:'Color tones' },
      { id:'gv-quote-complexity',  label:'Composition' },
      { id:'gv-quote-colors',      label:'Colors to include' },
      { id:'gv-quote-include',     label:'Include in image' },
    ],
    event: [
      { id:'gv-event-style',      label:'Style' },
      { id:'gv-event-mood',       label:'Mood' },
      { id:'gv-event-palette',    label:'Color palette' },
      { id:'gv-event-complexity', label:'Composition' },
      { id:'gv-event-colors',     label:'Colors to include' },
      { id:'gv-event-include',    label:'Include in image' },
    ],
  };
  return (fieldSets[type] || [])
    .map(f => { const v = document.getElementById(f.id)?.value?.trim(); return v ? f.label + ': ' + v : ''; })
    .filter(Boolean).join('. ');
}

function gvDalleSize(type) {
  if (type === 'cover') {
    const sizeVal = document.getElementById('gv-cover-size')?.value || '';
    return sizeVal === 'Square format' ? '1024x1024' : '1024x1792';
  }
  if (type === 'event') {
    const fmt = document.getElementById('gv-event-format')?.value || 'flyer';
    return fmt === 'square' ? '1024x1024' : '1024x1792';
  }
  return '1024x1024';
}

// ── BOOK TRAILER VIDEO (Shotstack) ────────────────────────────
// Submit a render → poll status every 5s → embed MP4 when done.
// Stateless: render_id lives only in the browser (window._tvRenderId).

let _tvPollHandle = null;
let _tvElapsedHandle = null;
let _tvRenderId = null;
let _tvStartedAt = 0;

function tvResetView() {
  if (_tvPollHandle) { clearTimeout(_tvPollHandle); _tvPollHandle = null; }
  if (_tvElapsedHandle) { clearInterval(_tvElapsedHandle); _tvElapsedHandle = null; }
  _tvRenderId = null;
  _tvStartedAt = 0;
  document.getElementById('tv-status-card').style.display = 'none';
  document.getElementById('tv-output-card').style.display = 'none';
  document.getElementById('tv-error-card').style.display = 'none';
  const btn = document.getElementById('tv-generate-btn');
  if (btn) btn.disabled = false;
  tvLoadQuota();
  // Per-area font pickers (v112). Render once each; picks persist for
  // subsequent regenerates on this page until the user navigates away.
  ['tagline', 'cta', 'credit'].forEach(area => {
    const fp = document.getElementById('tv-font-' + area);
    if (fp && !fp.dataset.rendered) {
      renderFontPicker(fp, fp.dataset.value || '', 'Font');
      fp.dataset.rendered = '1';
    }
  });
  // Populate saved campaign templates once per session (cheap GET).
  if (typeof tvRefreshTemplates === 'function' && !window._tvTemplatesLoaded) {
    window._tvTemplatesLoaded = true;
    tvRefreshTemplates();
  }
}

// Per-kind configuration for the direct "Post this →" button on each graphics
// page. The JS handler (gvPostGraphic) reads this to find the image element,
// caption textarea, optional link, and the book selector that drives the
// fallback link. Keep this in sync if a new graphics page is added.
const _GV_POST_CONFIG = {
  cover:  { label: 'cover concept', imgEl: 'gv-img-cover-el',  textEl: null,                  linkEl: null,             bookEl: 'gv-cover-book-id',  genBtn: 'Generate Image'      },
  social: { label: 'graphic',       imgEl: 'gv-img-social-el', textEl: 'gv-social-copy-text', linkEl: 'gv-social-link', bookEl: 'gv-social-book-id', genBtn: 'Generate Image'      },
  quote:  { label: 'quote card',    imgEl: 'gv-img-quote-el',  textEl: 'gv-quote-copy-text',  linkEl: null,             bookEl: 'gv-quote-book-id',  genBtn: 'Generate Background' },
  event:  { label: 'flyer',         imgEl: 'gv-img-event-el',  textEl: 'gv-event-copy-text',  linkEl: 'gv-event-link',  bookEl: 'gv-event-book-id',  genBtn: 'Generate Image'      },
};

// Direct-to-modal pattern for image-generating pages. Loads every image-
// friendly enabled platform and opens the handoff modal — the author drives
// what happens per tab, no AutoPost fires without explicit consent. Users
// who want one-click image AutoPost go through the Send to Social Posts ›
// route on the social/event pages, which routes into the post composer
// where the checkbox UI captures opt-in. (Trailer's tvPostTrailer follows
// the same explicit-consent model since video AutoPost isn't supported.)
async function gvPostGraphic(kind) {
  const cfg = _GV_POST_CONFIG[kind];
  if (!cfg) return;

  const img = document.getElementById(cfg.imgEl);
  const imageUrl = (img && (img.getAttribute('data-public-url') || img.src)) || '';
  if (!imageUrl || imageUrl === window.location.href || imageUrl === window.location.href + '#') {
    return toast('No image yet — click "' + cfg.genBtn + '" above first.', true);
  }

  const captionRaw = cfg.textEl ? (document.getElementById(cfg.textEl)?.value?.trim() || '') : '';
  let linkUrl     = cfg.linkEl ? (document.getElementById(cfg.linkEl)?.value?.trim() || '') : '';

  // Resolve the selected book once — used both for the link fallback and
  // (for quote cards) the promotional caption.
  const bookId = cfg.bookEl ? parseInt(document.getElementById(cfg.bookEl)?.value || '0') : 0;
  const book   = bookId ? (window._books || []).find(b => b.id == bookId) : null;
  if (!linkUrl) linkUrl = cleanAmazonUrl(book?.amazon_url || '');

  // For Quote Cards the quote is already burned into the image, so reusing
  // the quote text as the post caption would just duplicate visible content.
  // Default to a promo caption: "A line from \"<title>\" by <author>" +
  // book link. The author can still edit per-platform in the modal.
  let caption;
  if (kind === 'quote') {
    const parts = [];
    if (book && book.title) {
      const t = '"' + book.title + '"';
      parts.push(book.author ? 'A line from ' + t + ' by ' + book.author + '.' : 'A line from ' + t + '.');
    }
    if (linkUrl) parts.push(linkUrl);
    caption = parts.join('\n\n');
  } else {
    caption = linkUrl ? (captionRaw ? captionRaw + '\n\n' + linkUrl : linkUrl) : captionRaw;
  }

  try {
    const data = await api('/connections.php?action=list&for=image&enabled_only=1');
    if (!data || !data.success) return toast('Couldn\'t load platforms', true);
    const platforms = data.platforms || [];
    if (!platforms.length) {
      toast('No image platforms set up yet. Open Connections and paste profile URLs for Instagram, Facebook, Pinterest, etc.', true);
      return;
    }
    openHandoffModal({
      caption:    caption,
      linkUrl:    linkUrl,
      imageUrl:   imageUrl,
      // The on-page <img> is already loaded (often a base64 data URL) — reuse
      // it for the modal preview so it shows instantly instead of re-fetching
      // the full-size PNG from the server. Download still uses imageUrl.
      previewSrc: (img && img.src) || '',
      platforms:  platforms,
      source:     'gv_' + kind,
    });
  } catch (e) {
    toast('Couldn\'t load platforms', true);
  }
}

// Wire the rendered trailer into the same handoff modal the post composer
// uses. Loads only video-friendly platforms the author has set up, then
// opens the modal with one tab each. Trailers are always manual handoff —
// no API path supports posting our generated video files yet.
async function tvPostTrailer() {
  const dl = document.getElementById('tv-download-btn');
  const videoUrl = dl ? dl.getAttribute('href') : '';
  if (!videoUrl || videoUrl === '#') {
    return toast('Generate a trailer first', true);
  }
  const tagline = document.getElementById('tv-tagline')?.value?.trim() || '';

  // Book link, if the author has a destination URL on file.
  const bookId = parseInt(document.getElementById('tv-book-id')?.value || '0');
  const book   = bookId ? (window._books || []).find(b => b.id == bookId) : null;
  const linkUrl = cleanAmazonUrl(book?.amazon_url || '');

  try {
    const data = await api('/connections.php?action=list&for=video&enabled_only=1');
    if (!data || !data.success) return toast('Couldn\'t load platforms', true);
    const platforms = data.platforms || [];
    if (!platforms.length) {
      toast('No video platforms set up yet. Open Connections and add Instagram, TikTok, Facebook, etc.', true);
      return;
    }
    const tvFmt = document.getElementById('tv-format')?.value || '9x16';
    const tvAspect = tvFmt === '16x9' ? 'landscape' : (tvFmt === '1x1' ? 'square' : 'vertical');
    openHandoffModal({
      caption:  tagline,
      linkUrl:  linkUrl,
      videoUrl: videoUrl,
      platforms: platforms,
      source:   'trailer',
      videoAspect: tvAspect,
    });
  } catch (e) {
    toast('Couldn\'t load platforms', true);
  }
}

async function tvLoadQuota() {
  const pill = document.getElementById('tv-quota-pill');
  const btn  = document.getElementById('tv-generate-btn');
  if (!pill) return;
  try {
    const data = await api('/video_quota.php');
    if (!data?.success) { pill.textContent = ''; return; }
    if (data.is_admin) {
      pill.innerHTML = '<span style="color:var(--accent);font-weight:500">Admin — unlimited renders</span>';
      return;
    }
    if (data.cap <= 0) {
      pill.innerHTML = 'Trailers not in your plan — <a href="#" onclick="navigate(\'pricing\');return false">upgrade</a>';
      if (btn) btn.disabled = true;
      return;
    }
    const remaining = data.remaining;
    const used      = data.used;
    const cap       = data.cap;
    if (remaining <= 0) {
      pill.innerHTML = '<span style="color:#c44">' + used + ' / ' + cap + ' renders used this month</span> — '
        + '<a href="#" onclick="navigate(\'pricing\');return false">upgrade for more</a>';
      if (btn) btn.disabled = true;
    } else {
      pill.textContent = remaining + ' of ' + cap + ' renders left this month';
      if (btn) btn.disabled = false;
    }
  } catch (e) {
    pill.textContent = '';
  }
}

function tvFillFromBook() {
  const id   = parseInt(document.getElementById('tv-book-id').value || '0');
  const book = id ? (window._books || []).find(b => b.id == id) : null;
  if (book) {
    const tagEl   = document.getElementById('tv-tagline');
    const coverEl = document.getElementById('tv-cover-url');
    // Replace the previous book's auto-filled values when the user switches
    // books — but never clobber text the user typed themselves. "Auto-filled"
    // = empty, or exactly equal to the previously selected book's value.
    const prevId   = parseInt(tagEl.dataset.autoBookId || '0');
    const prevBook = prevId ? (window._books || []).find(b => b.id == prevId) : null;
    const tagIsAuto   = !tagEl.value   || (prevBook && tagEl.value   === (prevBook.tagline   || ''));
    const coverIsAuto = !coverEl.value || (prevBook && coverEl.value === (prevBook.cover_url || ''));
    if (tagIsAuto)   tagEl.value   = book.tagline   || '';
    if (coverIsAuto) coverEl.value = book.cover_url || '';
    tagEl.dataset.autoBookId = String(book.id);
  }
  // Refresh the shared "Working with: X" / "No book selected" status banner.
  if (typeof gvUpdateBookStatus === 'function') gvUpdateBookStatus();
}

// Download the rendered MP4 via the blob-download helper (cross-origin
// Shotstack URLs ignore the <a download> attribute and open in a tab).
// Filename comes from the selected book's title.
function tvDownloadVideo(ev) {
  if (ev) ev.preventDefault();
  const a = document.getElementById('tv-download-btn');
  const url = a ? a.href : '';
  if (!url || url.endsWith('#')) return;
  const id   = parseInt(document.getElementById('tv-book-id')?.value || '0');
  const book = id ? (window._books || []).find(b => b.id == id) : null;
  const base = ((book?.title || 'book-trailer').toLowerCase()
                  .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')) || 'book-trailer';
  _handoffForceDownload(url, base + '-trailer.mp4', 'video');
}

// Show the voice selector only when there's narration text to read.
function tvToggleVoiceVisible() {
  const txt = document.getElementById('tv-narration')?.value.trim() || '';
  const grp = document.getElementById('tv-voice-group');
  if (grp) grp.style.display = txt ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', () => {
  const t = document.getElementById('tv-narration');
  if (t) t.addEventListener('input', tvToggleVoiceVisible);
});

// AI suggest 3 narration options. Pipes through ai_draft.php (custom task)
// using the existing AI gateway — same quota path as other AI features.
window._tvLastNarrationOptions = [];

async function aiSuggestNarration() {
  const btn = event.target.closest('button');
  const original = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = 'Thinking…';

  const bookId  = parseInt(document.getElementById('tv-book-id').value || '0');
  const tagline = document.getElementById('tv-tagline').value.trim();

  // Variation seed forces fresh output per click — without it Claude tends
  // to converge on similar phrasings across regenerations even at default
  // temperature. Random integer in the prompt + explicit "fresh variations"
  // instruction breaks the convergence.
  const variationSeed = Math.floor(Math.random() * 1000000);

  let prompt = 'Write 3 different short voiceover scripts for a 30-second book trailer. '
    + 'These must be FRESH variations distinct from typical book trailer phrasings — '
    + 'avoid common openers and well-worn structural patterns. '
    + 'Each option must take a distinctly different angle: '
    + 'Option 1 atmospheric/mood-setting (sets the scene, hints at the world); '
    + 'Option 2 premise-focused (hooks with the central conflict or question); '
    + 'Option 3 emotional (speaks to what the reader will feel). '
    + 'Each option must be 35-50 words — that is 12-18 seconds spoken at default narration pace. '
    + 'Avoid AI-sounding phrases like "dive into", "unforgettable journey", "in a world where", "embark on". '
    + 'Output exactly 3 options separated by a line containing only "---". '
    + 'No numbering, no labels, no preamble — just the three scripts.';
  if (tagline) prompt += '\n\nTagline (use as inspiration, do not repeat verbatim): ' + tagline;
  prompt += '\n\n(Variation seed: ' + variationSeed + ' — produce different angles than any prior generation)';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: bookId, prompt, max_tokens: 600 }),
    });
    if (data.success && data.draft) {
      const options = data.draft.split(/\n*---+\n*/).map(s => s.trim()).filter(s => s.length > 10);
      if (options.length >= 1) {
        tvShowNarrationOptions(options.slice(0, 3));
      } else {
        toast('Could not parse suggestions — try again', true);
      }
    } else {
      toast(data.message || 'Could not suggest narration', true);
    }
  } catch (e) {
    toast('Request failed — try again', true);
  } finally {
    btn.disabled = false;
    btn.innerHTML = original;
  }
}

function tvShowNarrationOptions(options) {
  window._tvLastNarrationOptions = options.slice();
  const container = document.getElementById('tv-narration-suggestions');
  if (!container) return;
  if (!options || options.length === 0) {
    container.style.display = 'none';
    container.innerHTML = '';
    return;
  }
  container.innerHTML = '<div style="font-size:11px;color:var(--ink-soft);margin-bottom:6px">Click an option to use it:</div>'
    + options.map((text, i) => {
      const wordCount = text.split(/\s+/).length;
      const seconds   = Math.round(wordCount * 0.4);
      return '<div onclick="tvUseNarration(' + i + ')" '
        + 'style="border:1px solid #d4cfc4;border-radius:4px;padding:10px;margin-top:6px;cursor:pointer;background:#fafaf7" '
        + 'onmouseover="this.style.background=\'#f0ebe0\'" '
        + 'onmouseout="this.style.background=\'#fafaf7\'">'
        + '<div style="font-size:11px;color:var(--ink-soft);margin-bottom:4px">Option ' + (i+1) + ' · ' + wordCount + ' words · ~' + seconds + 's spoken</div>'
        + '<div style="font-size:13px;line-height:1.5">' + escapeHtml(text) + '</div>'
        + '</div>';
    }).join('');
  container.style.display = 'block';
}

function tvUseNarration(index) {
  const text = window._tvLastNarrationOptions[index];
  if (!text) return;
  document.getElementById('tv-narration').value = text;
  const container = document.getElementById('tv-narration-suggestions');
  if (container) { container.style.display = 'none'; container.innerHTML = ''; }
  tvToggleVoiceVisible();
}

function tvShowError(msg) {
  
  // The demo's "rendering is switched off" case already has a modal explaining
  // it AND showing a real example trailer. Printing a second notice under the
  // wizard is redundant, and it sits in a card styled for genuine failures.
  // Real errors — a bad cover URL, a quota cap — still show normally.
  if (typeof msg === 'string' && msg.indexOf('switched off in the demo') > -1) {
    var _c = document.getElementById('tv-error-card');
    if (_c) _c.style.display = 'none';
    var _s = document.getElementById('tv-status-card');
    if (_s) _s.style.display = 'none';
    var _b = document.getElementById('tv-generate-btn');
    if (_b) _b.disabled = false;
    return;
  }
  document.getElementById('tv-status-card').style.display = 'none';
  document.getElementById('tv-error-text').textContent = msg;
  
  // In the demo nothing actually failed — rendering is switched off on purpose.
  // A red "Render failed" heading over an explanatory message reads as a broken
  // app, which is the opposite of what the demo should convey.
  var _isDemo = (typeof currentUser !== 'undefined' && currentUser &&
                 (currentUser.is_demo == 1 || currentUser.is_demo === true));
  var _tvErrTitle = document.getElementById('tv-error-title');
  var _tvErrCard  = document.getElementById('tv-error-card');
  if (_tvErrTitle) {
    _tvErrTitle.textContent = _isDemo ? 'Demo mode' : 'Render failed';
    _tvErrTitle.style.color = _isDemo ? 'var(--ink-mid)' : '#c44';
  }
  if (_tvErrCard) _tvErrCard.style.borderLeftColor = _isDemo ? 'var(--accent)' : '#c44';
  document.getElementById('tv-error-card').style.display = 'block';
  const btn = document.getElementById('tv-generate-btn');
  if (btn) btn.disabled = false;
  if (_tvPollHandle)    { clearTimeout(_tvPollHandle); _tvPollHandle = null; }
  if (_tvElapsedHandle) { clearInterval(_tvElapsedHandle); _tvElapsedHandle = null; }
  // Failed renders don't count against quota — refresh the pill so the user
  // sees their slot returned (UPDATE to status='failed' happens server-side).
  tvLoadQuota();
}

function tvUpdateStatus(text, percent) {
  document.getElementById('tv-status-text').textContent = text;
  document.getElementById('tv-status-bar').style.width  = Math.min(100, percent) + '%';
}

// ════════════════════════════════════════════════════════════
//  Trailer wizard navigation (v114)
//  The Book Trailer page is a 4-step wizard: 1 Image & tagline,
//  2 Voice & closing, 3 Style & branding, 4 Review & generate.
// ════════════════════════════════════════════════════════════
let _tvStep = 1;
function tvGoStep(n) {
  n = Math.max(1, Math.min(4, n));
  _tvStep = n;
  document.querySelectorAll('#view-gv-trailer-video .tv-step').forEach(el => {
    el.style.display = (parseInt(el.dataset.step) === n) ? 'block' : 'none';
  });
  for (let i = 1; i <= 4; i++) {
    const p = document.getElementById('tv-pill-' + i);
    if (!p) continue;
    p.classList.toggle('active', i === n);
    p.classList.toggle('done', i < n);
  }
  if (n === 4) tvBuildReview();
  const w = document.getElementById('tv-wizard');
  if (w && w.scrollIntoView) w.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function tvNext() {
  // Light validation as the user advances (mirrors the final submit checks).
  if (_tvStep === 1) {
    const cover = document.getElementById('tv-cover-url')?.value.trim();
    if (!cover) { alert('Add or upload a cover image first.'); return; }
    const ff    = !!document.getElementById('tv-fullframe')?.checked;
    const showT = document.getElementById('tv-show-tagline')?.checked !== false;
    const tag   = document.getElementById('tv-tagline')?.value.trim();
    if (!ff && showT && !tag) { alert('Add a tagline — or uncheck “Show tagline” to skip it.'); return; }
  }
  tvGoStep(_tvStep + 1);
}
function tvBack() { tvGoStep(_tvStep - 1); }

// Build the Step-4 recap from the current control values.
function tvBuildReview() {
  const el = document.getElementById('tv-review-summary');
  if (!el) return;
  const g = (id) => document.getElementById(id);
  const fmt   = ({ '9x16': 'Vertical 9:16', '1x1': 'Square 1:1', '16x9': 'Horizontal 16:9' })[g('tv-format')?.value] || '—';
  const ff    = !!g('tv-fullframe')?.checked;
  const tagOn = g('tv-show-tagline')?.checked !== false && !ff;
  const tag   = g('tv-tagline')?.value.trim();
  const mood  = g('tv-mood')?.value;
  const narr  = g('tv-narration')?.value.trim();
  const logo  = g('tv-logo-position')?.value;
  const modeEl = g('tv-campaign-mode');
  const mode  = (modeEl && modeEl.value) ? modeEl.options[modeEl.selectedIndex].text : 'Custom (no preset)';
  const rows = [
    ['Format',        fmt + (ff ? ' · full-frame image' : '')],
    ['Tagline',       tagOn ? (tag || '—') : 'Hidden'],
    ['Closing card',  g('tv-cta')?.value.trim() || '—'],
    ['Voiceover',     narr ? 'On' : 'Music only'],
    ['Music',         mood === 'silent' ? 'Silent' : (mood || '—')],
    ['Logo',          (!logo || logo === 'none') ? 'None' : logo],
    ['Campaign style', mode],
  ];
  el.innerHTML = rows.map(r => '<div><strong>' + r[0] + ':</strong> ' + escapeHtml(String(r[1])) + '</div>').join('');
}

// ════════════════════════════════════════════════════════════
//  Campaign modes & templates (v113)
//  A "settings bundle" captures the LOOK of a trailer (positions,
//  fonts, colors, motion, logo, safe margins, …) — never the
//  content (tagline text, cover, narration). Templates save/load
//  a bundle to the server; campaign modes are curated built-in
//  bundles. Both funnel through tvApplySettings().
// ════════════════════════════════════════════════════════════
function tvCollectSettings() {
  const areaStyle = (area) => ({
    font:   document.getElementById('tv-font-' + area)?.dataset.value || '',
    size:   parseInt(document.getElementById('tv-size-' + area)?.value || '0') || null,
    bold:   !!document.getElementById('tv-bold-' + area)?.checked,
    italic: !!document.getElementById('tv-italic-' + area)?.checked,
    color:  document.getElementById('tv-color-' + area)?.value || null,
  });
  return {
    v: 113,
    format:             document.getElementById('tv-format')?.value,
    mood:               document.getElementById('tv-mood')?.value,
    cta:                document.getElementById('tv-cta')?.value,
    show_tagline:       document.getElementById('tv-show-tagline')?.checked !== false,
    text_position:      document.getElementById('tv-text-position')?.value,
    safe_area:          !!document.getElementById('tv-safe-area')?.checked,
    bg_blur:            !!document.getElementById('tv-bg-blur')?.checked,
    logo_position:      document.getElementById('tv-logo-position')?.value,
    zoom:               parseInt(document.getElementById('tv-zoom')?.value || '100') || 100,
    motion:             document.getElementById('tv-motion')?.value,
    fade:               document.getElementById('tv-fade')?.value,
    fullframe:          !!document.getElementById('tv-fullframe')?.checked,
    fullframe_overlays: !!document.getElementById('tv-fullframe-overlays')?.checked,
    styles: { tagline: areaStyle('tagline'), cta: areaStyle('cta'), credit: areaStyle('credit') },
  };
}

function _tvSet(id, val)   { if (val === undefined || val === null) return; const el = document.getElementById(id); if (el) el.value = val; }
function _tvCheck(id, val) { if (val === undefined || val === null) return; const el = document.getElementById(id); if (el) el.checked = !!val; }
function tvSetFontArea(area, family) {
  if (!family) return;
  const c = document.getElementById('tv-font-' + area);
  if (!c) return;
  c.dataset.value = family;
  const sel = document.getElementById('tv-font-' + area + '-select');
  if (sel) { sel.value = family; if (typeof _handleFontPickerChange === 'function') _handleFontPickerChange('tv-font-' + area); }
}

// Apply a (possibly partial) settings bundle. Missing keys are left untouched
// so campaign modes can set just a few fields.
function tvApplySettings(s) {
  if (!s || typeof s !== 'object') return;
  _tvSet('tv-format', s.format);
  _tvSet('tv-mood', s.mood);
  _tvSet('tv-cta', s.cta);
  _tvCheck('tv-show-tagline', s.show_tagline);
  _tvSet('tv-text-position', s.text_position);
  _tvCheck('tv-safe-area', s.safe_area);
  _tvCheck('tv-bg-blur', s.bg_blur);
  _tvSet('tv-logo-position', s.logo_position);
  _tvCheck('tv-fullframe', s.fullframe);
  _tvCheck('tv-fullframe-overlays', s.fullframe_overlays);
  _tvSet('tv-motion', s.motion);
  _tvSet('tv-fade', s.fade);
  if (s.zoom) {
    const z = document.getElementById('tv-zoom');
    if (z) { z.value = s.zoom; const lbl = document.getElementById('tv-zoom-val'); if (lbl) lbl.textContent = s.zoom + '%'; }
  }
  const st = s.styles || {};
  ['tagline', 'cta', 'credit'].forEach(area => {
    const a = st[area]; if (!a) return;
    if (a.font) tvSetFontArea(area, a.font);
    _tvSet('tv-size-' + area, a.size);
    _tvCheck('tv-bold-' + area, a.bold);
    _tvCheck('tv-italic-' + area, a.italic);
    _tvSet('tv-color-' + area, a.color);
  });
}

// Built-in campaign modes — curated partial bundles of optimized defaults.
const TV_CAMPAIGN_MODES = {
  emotional:   { text_position: 'bottom', motion: 'slow',   fade: 'slow',   mood: 'warm',   bg_blur: true,  zoom: 110, show_tagline: true,
                 styles: { tagline: { size: 60, bold: false }, cta: { size: 46, bold: true }, credit: { size: 50 } } },
  educational: { text_position: 'bottom', motion: 'normal', fade: 'normal', mood: 'warm',   bg_blur: false, safe_area: true, zoom: 100,
                 styles: { tagline: { size: 44, bold: false }, cta: { size: 44, bold: true }, credit: { size: 48 } } },
  product:     { text_position: 'bottom', motion: 'normal', fade: 'normal', mood: 'upbeat', bg_blur: false, zoom: 120,
                 styles: { tagline: { size: 42, bold: true }, cta: { size: 46, bold: true }, credit: { size: 48 } } },
  testimonial: { text_position: 'center', motion: 'slow',   fade: 'slow',   mood: 'warm',   bg_blur: true,  zoom: 100,
                 styles: { tagline: { size: 50, bold: false, italic: true }, cta: { size: 44, bold: true }, credit: { size: 46 } } },
  feature:     { text_position: 'bottom', motion: 'normal', fade: 'fast',   mood: 'upbeat', bg_blur: false, zoom: 105,
                 styles: { tagline: { size: 48, bold: true }, cta: { size: 48, bold: true }, credit: { size: 50 } } },
};
function tvApplyCampaignMode(mode) {
  if (!mode || !TV_CAMPAIGN_MODES[mode]) return;
  tvApplySettings(TV_CAMPAIGN_MODES[mode]);
  const sec = document.getElementById('tv-style-section');
  if (sec && !sec.open) sec.open = true;
  if (typeof toast === 'function') toast('Applied the “' + mode + '” campaign defaults — tweak anything before rendering.');
}

// ── Saved templates (server-backed) ─────────────────────────
async function tvRefreshTemplates(selectId) {
  const sel = document.getElementById('tv-template-select');
  if (!sel) return;
  try {
    const res = await api('/campaign_templates.php?action=list', { method: 'GET' });
    const list = (res && res.templates) || [];
    sel.innerHTML = '<option value="">— Saved templates —</option>' +
      list.map(t => '<option value="' + t.id + '">' + escapeHtml(t.name) + '</option>').join('');
    if (selectId) sel.value = String(selectId);
  } catch (e) { /* leave the list as-is */ }
}
async function tvSaveTemplate() {
  const name = (prompt('Save these settings as a reusable campaign template.\n\nName it:') || '').trim();
  if (!name) return;
  try {
    const res = await api('/campaign_templates.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'save', name: name, settings: tvCollectSettings() }),
    });
    if (res && res.success) { await tvRefreshTemplates(res.id); if (typeof toast === 'function') toast('Template “' + name + '” saved.'); }
    else alert((res && res.message) || 'Could not save the template.');
  } catch (e) { alert('Could not save the template: ' + (e?.message || e)); }
}
async function tvLoadTemplate() {
  const sel = document.getElementById('tv-template-select');
  const id  = sel ? sel.value : '';
  if (!id) { alert('Pick a saved template first.'); return; }
  try {
    const res = await api('/campaign_templates.php?action=get&id=' + encodeURIComponent(id), { method: 'GET' });
    if (res && res.success && res.template) {
      tvApplySettings(res.template.settings);
      const mode = document.getElementById('tv-campaign-mode'); if (mode) mode.value = '';
      const sec  = document.getElementById('tv-style-section'); if (sec && !sec.open) sec.open = true;
      if (typeof toast === 'function') toast('Loaded “' + res.template.name + '”.');
    } else alert((res && res.message) || 'Could not load the template.');
  } catch (e) { alert('Could not load the template: ' + (e?.message || e)); }
}
async function tvDeleteTemplate() {
  const sel = document.getElementById('tv-template-select');
  const id  = sel ? sel.value : '';
  if (!id) { alert('Pick a saved template to delete.'); return; }
  const name = sel.options[sel.selectedIndex]?.text || 'this template';
  if (!confirm('Delete “' + name + '”? This cannot be undone.')) return;
  try {
    const res = await api('/campaign_templates.php', { method: 'POST', body: JSON.stringify({ action: 'delete', id: parseInt(id) }) });
    if (res && res.success) { await tvRefreshTemplates(); if (typeof toast === 'function') toast('Template deleted.'); }
    else alert((res && res.message) || 'Could not delete the template.');
  } catch (e) { alert('Could not delete the template: ' + (e?.message || e)); }
}

async function tvGenerate() {
  const tagline    = document.getElementById('tv-tagline').value.trim();
  const coverUrl   = document.getElementById('tv-cover-url').value.trim();
  const mood       = document.getElementById('tv-mood').value;
  const cta        = document.getElementById('tv-cta').value.trim() || 'Available Now';
  const narration  = document.getElementById('tv-narration').value.trim();
  const voice      = document.getElementById('tv-voice').value;
  const format     = document.getElementById('tv-format').value;
  const colors     = document.getElementById('tv-colors').value.trim();
  const include    = document.getElementById('tv-include').value.trim();
  const fullFrame  = !!document.getElementById('tv-fullframe')?.checked;

  // ── v112 styling & layout controls ──
  const tvAreaStyle = (area) => {
    const fp = document.getElementById('tv-font-' + area);
    const sz = parseInt(document.getElementById('tv-size-' + area)?.value || '0');
    return {
      font_family: fp ? (fp.dataset.value || '') : '',
      size:        sz > 0 ? sz : undefined,
      bold:        !!document.getElementById('tv-bold-' + area)?.checked,
      italic:      !!document.getElementById('tv-italic-' + area)?.checked,
      color:       document.getElementById('tv-color-' + area)?.value || undefined,
    };
  };
  const textStyles   = { tagline: tvAreaStyle('tagline'), cta: tvAreaStyle('cta'), credit: tvAreaStyle('credit') };
  const textPosition = document.getElementById('tv-text-position')?.value || 'bottom';
  const safeArea     = !!document.getElementById('tv-safe-area')?.checked;
  const bgBlur       = !!document.getElementById('tv-bg-blur')?.checked;
  const logoPosition = document.getElementById('tv-logo-position')?.value || 'none';
  const coverZoom    = (parseInt(document.getElementById('tv-zoom')?.value || '100') || 100) / 100;
  const showTagline  = document.getElementById('tv-show-tagline')?.checked !== false;
  // v113
  const fullFrameOverlays = !!document.getElementById('tv-fullframe-overlays')?.checked;
  const motion       = document.getElementById('tv-motion')?.value || 'slow';
  const fade         = document.getElementById('tv-fade')?.value || 'normal';
  // Page-level fallback family for the backend (per-area unset → falls back here).
  const fontFamily   = textStyles.tagline.font_family || '';

  if (!coverUrl) { alert('Cover image URL is required.'); return; }
  if (!fullFrame && showTagline && !tagline) { alert('Tagline is required — or uncheck “Show tagline” to skip it.'); return; }
  if (!/^https?:\/\//i.test(coverUrl)) {
    alert('Cover image URL must start with http:// or https:// — Shotstack must be able to fetch it.');
    return;
  }

  tvResetView();
  document.getElementById('tv-generate-btn').disabled = true;
  document.getElementById('tv-status-card').style.display = 'block';
  tvUpdateStatus('Submitting render…', 5);

  // Full-frame mode: blurred-pad-fit the finished image to the chosen ratio so
  // it fills the frame whole (no crop). Falls back to the original on failure.
  let renderCoverUrl = coverUrl;
  if (fullFrame) {
    const dims = { '9x16': [1080, 1920], '1x1': [1080, 1080], '16x9': [1920, 1080] }[format] || [1080, 1920];
    try {
      const fit = await api('/image_fit.php', {
        method: 'POST',
        body: JSON.stringify({ source: coverUrl, w: dims[0], h: dims[1], mode: 'blur' }),
      });
      if (fit && fit.success && fit.url) renderCoverUrl = fit.url;
    } catch (e) { /* keep original */ }
  }

  let data;
  try {
    data = await api('/video_render.php', {
      method: 'POST',
      body: JSON.stringify({
        tagline:     tagline,
        cover_url:   renderCoverUrl,
        full_frame:  fullFrame,
        mood:        mood,
        cta:         cta,
        narration:   narration,
        voice:       voice,
        format:      format,
        colors:      colors,
        include:     include,
        book_id:     parseInt(document.getElementById('tv-book-id').value || '0'),
        font_family: fontFamily,    // v54 — page-level fallback font
        // v112/v113 styling & layout
        text_styles:        textStyles,
        show_tagline:       showTagline,
        text_position:      textPosition,
        safe_area:          safeArea,
        bg_blur:            bgBlur,
        logo_position:      logoPosition,
        cover_zoom:         coverZoom,
        fullframe_overlays: fullFrameOverlays,
        motion:             motion,
        fade:               fade,
      }),
    });
  } catch (e) {
    tvShowError('Submission error: ' + (e?.message || e));
    return;
  }

  if (data?.error_code === 'quota_exceeded') {
    tvShowError(data.message || 'Monthly trailer render limit reached.');
    tvLoadQuota();  // refresh the pill so it shows the cap-reached state
    return;
  }

  if (!data?.success || !data?.render_id) {
    tvShowError(data?.message || 'Render submission failed.');
    return;
  }

  // Successful submit consumes one from quota — refresh the pill.
  tvLoadQuota();

  _tvRenderId  = data.render_id;
  _tvStartedAt = Date.now();
  tvUpdateStatus('Queued — Shotstack is preparing assets…', 10);

  // Elapsed-time ticker (1s)
  _tvElapsedHandle = setInterval(() => {
    const sec = Math.round((Date.now() - _tvStartedAt) / 1000);
    document.getElementById('tv-status-elapsed').textContent =
      'Elapsed: ' + sec + 's' + (sec > 90 ? ' — typical render is 60–120s, hang tight' : '');
  }, 1000);

  // First poll in 4s, then every 5s.
  _tvPollHandle = setTimeout(tvPoll, 4000);
}

async function tvPoll() {
  if (!_tvRenderId) return;
  let data;
  try {
    data = await api('/video_status.php?render_id=' + encodeURIComponent(_tvRenderId));
  } catch (e) {
    // Transient error — try again in 5s rather than failing the whole render.
    _tvPollHandle = setTimeout(tvPoll, 5000);
    return;
  }

  if (!data?.success) {
    tvShowError(data?.message || 'Status check failed.');
    return;
  }

  // Map Shotstack states → user-readable + progress %.
  const stateMap = {
    queued:    { label: 'Queued',                       pct: 15 },
    fetching:  { label: 'Fetching cover and music…',    pct: 30 },
    rendering: { label: 'Rendering frames…',            pct: 60 },
    saving:    { label: 'Encoding final video…',        pct: 85 },
    done:      { label: 'Done',                         pct: 100 },
    failed:    { label: 'Failed',                       pct: 100 },
  };
  const s = stateMap[data.status] || { label: data.status || 'Working…', pct: 50 };
  tvUpdateStatus(s.label + '…', s.pct);

  if (data.status === 'done' && data.url) {
    if (_tvPollHandle)    { clearTimeout(_tvPollHandle); _tvPollHandle = null; }
    if (_tvElapsedHandle) { clearInterval(_tvElapsedHandle); _tvElapsedHandle = null; }
    document.getElementById('tv-status-card').style.display = 'none';
    document.getElementById('tv-video').src = data.url;
    document.getElementById('tv-download-btn').href = data.url;
    document.getElementById('tv-output-card').style.display = 'block';
    document.getElementById('tv-generate-btn').disabled = false;
    return;
  }

  if (data.status === 'failed') {
    tvShowError('Render failed: ' + (data.error || 'no detail returned'));
    return;
  }

  // Keep polling.
  _tvPollHandle = setTimeout(tvPoll, 5000);
}

// ── ADMIN: AI USAGE ───────────────────────────────────────────

// ── ADMIN USERS ───────────────────────────────────────────────

let _auuDebounce = null;
function loadAdminUsers() {
  clearTimeout(_auuDebounce);
  _auuDebounce = setTimeout(_doLoadAdminUsers, 250);
}

async function _doLoadAdminUsers() {
  const status = document.getElementById('auu-status').value;
  const search = document.getElementById('auu-search').value.trim();
  const qs = (status ? '?status=' + encodeURIComponent(status) : '?')
           + (search ? '&search=' + encodeURIComponent(search) : '');

  const data = await api('/admin_users.php' + qs);
  if (!data.success) { toast(data.message || 'Failed to load users', true); return; }

  _renderUserSummary(data.summary);
  _renderUserTable(data.users);
}

function _renderUserSummary(s) {
  const grid = document.getElementById('au-user-summary');
  const stat = (label, value, sub) =>
    '<div class="card" style="padding:14px 16px">' +
    '<div style="font-size:11px;color:var(--ink-soft);margin-bottom:4px">' + label + '</div>' +
    '<div style="font-size:22px;font-weight:600;font-family:var(--font-serif)">' + value + '</div>' +
    (sub ? '<div style="font-size:11px;color:var(--ink-soft);margin-top:2px">' + sub + '</div>' : '') +
    '</div>';
  grid.innerHTML =
    stat('Total users',    s.total) +
    stat('Trialing',       s.trialing,      s.trial_expired ? s.trial_expired + ' expired' : '') +
    stat('Active',         s.active) +
    stat('Past due',       s.past_due) +
    stat('Canceled',       s.canceled) +
    stat('No subscription',s.no_sub);
}

function _renderUserTable(users) {
  const wrap = document.getElementById('auu-table-wrap');
  if (!users.length) { wrap.innerHTML = '<div class="empty">No users match</div>'; return; }

  const statusBadge = s => {
    const colors = {
      trialing:     'background:#e8f4fd;color:#1a6fa8',
      active:       'background:#e8f8ee;color:#1a7a3c',
      past_due:     'background:#fff3e0;color:#b45309',
      trial_expired:'background:#fde8e8;color:#b91c1c',
      canceled:     'background:#f3f4f6;color:#6b7280',
      none:         'background:#f3f4f6;color:#9ca3af',
    };
    const labels = {
      trialing:'Trialing', active:'Active', past_due:'Past due',
      trial_expired:'Trial ended', canceled:'Canceled', none:'None',
    };
    const style = colors[s] || colors.none;
    return '<span style="' + style + ';padding:2px 8px;border-radius:10px;font-size:11px;font-weight:500">'
         + (labels[s] || s) + '</span>';
  };

  const fmt = d => d ? new Date(d).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' }) : '—';

  wrap.innerHTML = '<table style="width:100%;border-collapse:collapse;font-size:13px;min-width:880px">' +
    '<thead><tr style="border-bottom:1px solid var(--ink-faint)">' +
    ['Name / Email','Plan','Status','Trial ends','Member since','AI (period)','AI (lifetime)','Actions'].map(h =>
      '<th style="text-align:left;padding:6px 8px;font-weight:500;color:var(--ink-soft);white-space:nowrap">' + h + '</th>'
    ).join('') +
    '</tr></thead><tbody>' +
    users.map(u => {
      const safeName  = escapeHtml(u.name).replace(/'/g, '&#39;');
      const adminBadge = u.is_admin
        ? ' <span style="background:#ede9fe;color:#5b21b6;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:600;vertical-align:middle;margin-left:4px">ADMIN</span>'
        : '';
      const actions = u.is_admin
        ? '<span style="color:var(--ink-soft);font-size:11px">—</span>'
        : '<button class="app-btn app-btn-outline app-btn-sm" '
            + 'onclick="grantComp(' + u.id + ', \'' + safeName + '\')" '
            + 'title="Grant trial-access comp to this user">+ Comp days</button>'
          + '<button class="app-btn app-btn-outline app-btn-sm" '
            + 'style="color:var(--danger);border-color:#FECACA;margin-left:6px" '
            + 'onclick="deleteUser(' + u.id + ', \'' + safeName + '\')" '
            + 'title="Permanently delete this user and all their data">Delete</button>';
      return '<tr style="border-bottom:1px solid var(--ink-faint)">' +
        '<td style="padding:6px 8px"><div style="font-weight:500">' + escapeHtml(u.name) + adminBadge + '</div>' +
          '<div style="font-size:11px;color:var(--ink-soft)">' + escapeHtml(u.email) + '</div></td>' +
        '<td style="padding:6px 8px;text-transform:capitalize">' + escapeHtml(u.plan) +
          (u.billing_interval
            ? ' <span style="font-size:10px;font-weight:600;padding:1px 6px;border-radius:8px;vertical-align:middle;text-transform:none;'
                + (u.billing_interval === 'year' ? 'background:#DCFCE7;color:#166534' : 'background:#F1F5F9;color:#475569') + '">'
                + (u.billing_interval === 'year' ? 'Annual' : 'Monthly') + '</span>'
            : '') +
        '</td>' +
        '<td style="padding:6px 8px">' + statusBadge(u.subscription_status) + '</td>' +
        '<td style="padding:6px 8px;white-space:nowrap">' + fmt(u.trial_ends_at) + '</td>' +
        '<td style="padding:6px 8px;white-space:nowrap">' + fmt(u.created_at) + '</td>' +
        '<td style="padding:6px 8px;font-variant-numeric:tabular-nums">' + escapeHtml(u.ai_used_display) + '</td>' +
        '<td style="padding:6px 8px;font-variant-numeric:tabular-nums">' + escapeHtml(u.ai_lifetime_display || '—') + '</td>' +
        '<td style="padding:6px 8px;white-space:nowrap">' + actions + '</td>' +
      '</tr>';
    }).join('') +
    '</tbody></table>';
}

async function deleteUser(userId, name) {
  if (!confirm('Permanently delete ' + name + '?\n\nThis removes the user and all their books, posts, AI usage history, and other data. If the user has an active Stripe subscription, it will be canceled automatically so no further charges occur.\n\nThis cannot be undone.')) return;

  const data = await api('/admin_users.php?action=delete_user', {
    method: 'POST',
    body: JSON.stringify({ user_id: userId }),
  });

  if (data.success) {
    let msg = data.message || ('Deleted ' + name);
    // Surface the Stripe result so admin knows whether the cancel ran.
    if (data.stripe_result && data.stripe_result !== 'no-subscription') {
      msg += ' — Stripe: ' + data.stripe_result;
    }
    toast(msg);
    loadAdminUsers();
  } else {
    toast(data.message || 'Could not delete user', true);
  }
}

// Toggle the inline "Create user" form in the admin Users view. Form is
// hidden by default to keep the page focused on the user list; admin opens
// it only when onboarding a beta tester (or anyone else outside the public
// pay-at-signup flow).
function toggleCreateUserForm() {
  const card = document.getElementById('admin-create-user-card');
  if (!card) return;
  const open = card.style.display !== 'none';
  card.style.display = open ? 'none' : 'block';
  if (!open) {
    setTimeout(() => { const f = document.getElementById('cu-full-name'); if (f) f.focus(); }, 50);
  }
}

// Admin → create a beta-tester account. POSTs to /admin_users.php with the
// six form fields plus the "send email" checkbox. On success, clears the
// form, hides it, and refreshes the user table so the new account is visible.
async function createBetaUser() {
  const fullName  = document.getElementById('cu-full-name').value.trim();
  const penName   = document.getElementById('cu-pen-name').value.trim();
  const email     = document.getElementById('cu-email').value.trim();
  const password  = document.getElementById('cu-password').value;
  const plan      = document.getElementById('cu-plan').value;
  const compDays  = parseInt(document.getElementById('cu-comp-days').value, 10);
  const sendEmail = document.getElementById('cu-send-email').checked;

  if (!fullName)      { toast('Full name is required', true); return; }
  if (!email)         { toast('Email is required', true); return; }
  if (!password || password.length < 8) { toast('Password must be at least 8 characters', true); return; }
  if (!compDays || compDays < 1 || compDays > 365) { toast('Comp days must be 1–365', true); return; }

  const data = await api('/admin_users.php?action=create_user', {
    method: 'POST',
    body: JSON.stringify({
      full_name:  fullName,
      pen_name:   penName,
      email:      email,
      password:   password,
      plan:       plan,
      comp_days:  compDays,
      send_email: sendEmail,
    }),
  });

  if (!data.success) {
    toast(data.message || 'Could not create user', true);
    return;
  }

  // Clear, hide, refresh.
  ['cu-full-name','cu-pen-name','cu-email','cu-password'].forEach(id => {
    const el = document.getElementById(id); if (el) el.value = '';
  });
  document.getElementById('cu-comp-days').value = '30';
  document.getElementById('cu-plan').value = 'pro';
  document.getElementById('cu-send-email').checked = true;
  toggleCreateUserForm();

  const tail = data.email_sent ? ' — welcome email sent.'
             : (sendEmail ? ' — but the welcome email failed to send. Share credentials manually.' : '');
  toast((data.message || 'Account created') + tail);
  loadAdminUsers();
}

async function grantComp(userId, name) {
  const daysStr = prompt('How many days of comp access for ' + name + '?', '30');
  if (daysStr === null) return;
  const days = parseInt(daysStr, 10);
  if (!days || days < 1 || days > 365) {
    toast('Days must be a number between 1 and 365', true);
    return;
  }
  if (!confirm('Grant ' + days + '-day comp access to ' + name + '?\n\nThis sets their trial_ends_at to ' + days + ' days from now and lets them use the app like a paying user.')) return;

  const data = await api('/admin_users.php?action=grant_comp', {
    method: 'POST',
    body: JSON.stringify({ user_id: userId, days: days }),
  });

  if (data.success) {
    toast(data.message || ('Granted ' + days + '-day comp'));
    loadAdminUsers();  // refresh the table
  } else {
    toast(data.message || 'Could not grant comp', true);
  }
}

async function loadAdminUsage() {
  const days    = document.getElementById('au-days').value;
  const feature = document.getElementById('au-feature').value;
  const userId  = document.getElementById('au-user').value;

  const qs = '?days=' + days
    + (feature ? '&feature=' + encodeURIComponent(feature) : '')
    + (userId  ? '&user_id=' + userId : '');

  const data = await api('/admin_usage.php' + qs);
  if (!data.success) { toast(data.message || 'Failed to load usage data', true); return; }

  renderAdminSummary(data.summary);
  renderAdminByFeature(data.by_feature);
  renderAdminPosting(data.posting);
  renderAdminTable(data.rows);
  populateAdminFilters(data.features, data.users);
}

// Posting activity: manual handoffs by source and platform, plus AutoPost
// total. `source` values come from manual_posts (post_composer, trailer,
// gv_social, gv_quote, gv_event, gv_cover) — the analytics handle for "which
// creative surface drives actual posting."
function renderAdminPosting(p) {
  const el = document.getElementById('au-posting');
  if (!el) return;
  if (!p || (!p.manual_total && !p.autopost_total)) {
    el.innerHTML = '<div class="empty">No posts in this period</div>';
    return;
  }
  const labels = {
    post_composer: 'Post composer', trailer: 'Trailer page', gv_social: 'Social graphic',
    gv_quote: 'Quote card', gv_event: 'Event flyer', gv_cover: 'Cover concept',
  };
  const miniTable = (title, rows, keyField) => {
    if (!rows || !rows.length) return '';
    let h = '<div style="flex:1;min-width:200px"><div style="font-size:11px;color:var(--ink-soft);margin-bottom:6px">' + title + '</div>' +
      '<table style="width:100%;border-collapse:collapse;font-size:13px">';
    rows.forEach(r => {
      const raw = r[keyField] || '—';
      h += '<tr style="border-bottom:1px solid var(--ink-faint)">' +
        '<td style="padding:5px 8px">' + escapeHtml(labels[raw] || raw) + '</td>' +
        '<td style="padding:5px 8px;text-align:right;font-weight:600">' + (+r.posts).toLocaleString() + '</td></tr>';
    });
    return h + '</table></div>';
  };
  el.innerHTML =
    '<div style="display:flex;gap:18px;margin-bottom:12px;flex-wrap:wrap">' +
      '<div><span style="font-size:20px;font-weight:600;font-family:var(--font-serif)">' + (+p.manual_total).toLocaleString() + '</span>' +
      ' <span style="font-size:12px;color:var(--ink-soft)">manual handoffs</span></div>' +
      '<div><span style="font-size:20px;font-weight:600;font-family:var(--font-serif)">' + (+p.autopost_total).toLocaleString() + '</span>' +
      ' <span style="font-size:12px;color:var(--ink-soft)">AutoPosts</span></div>' +
    '</div>' +
    '<div style="display:flex;gap:24px;flex-wrap:wrap">' +
      miniTable('Manual posts by source', p.by_source, 'source') +
      miniTable('Manual posts by platform', p.by_platform, 'platform_slug') +
    '</div>';
}

function populateAdminFilters(features, users) {
  const fSel = document.getElementById('au-feature');
  const uSel = document.getElementById('au-user');
  const fVal = fSel.value;
  const uVal = uSel.value;

  fSel.innerHTML = '<option value="">All features</option>' +
    features.map(f => '<option value="' + f + '"' + (f === fVal ? ' selected' : '') + '>' + f + '</option>').join('');

  uSel.innerHTML = '<option value="">All users</option>' +
    users.map(u => {
      const label = (u.pen_name || u.full_name || u.email) + ' (' + u.email + ')';
      return '<option value="' + u.id + '"' + (u.id == uVal ? ' selected' : '') + '>' + label + '</option>';
    }).join('');
}

function renderAdminSummary(s) {
  const grid = document.getElementById('au-summary-grid');
  const stat = (label, value) =>
    '<div class="card" style="padding:14px 16px">' +
    '<div style="font-size:11px;color:var(--ink-soft);margin-bottom:4px">' + label + '</div>' +
    '<div style="font-size:20px;font-weight:600;font-family:var(--font-serif)">' + value + '</div>' +
    '</div>';
  grid.innerHTML =
    stat('Total calls', s.total_calls.toLocaleString()) +
    stat('Successful', s.successful_calls.toLocaleString()) +
    stat('Total cost', s.total_cost_display) +
    stat('Input tokens', s.total_input_tokens.toLocaleString()) +
    stat('Output tokens', s.total_output_tokens.toLocaleString());
}

function renderAdminByFeature(rows) {
  const el = document.getElementById('au-by-feature');
  if (!rows.length) { el.innerHTML = '<div class="empty">No data</div>'; return; }
  el.innerHTML = '<table style="width:100%;border-collapse:collapse;font-size:13px">' +
    '<thead><tr style="border-bottom:1px solid var(--ink-faint)">' +
    '<th style="text-align:left;padding:6px 8px;font-weight:500;color:var(--ink-soft)">Feature</th>' +
    '<th style="text-align:right;padding:6px 8px;font-weight:500;color:var(--ink-soft)">Calls</th>' +
    '<th style="text-align:right;padding:6px 8px;font-weight:500;color:var(--ink-soft)">Cost</th>' +
    '</tr></thead><tbody>' +
    rows.map(r =>
      '<tr style="border-bottom:1px solid var(--ink-faint)">' +
      '<td style="padding:6px 8px">' + r.feature + '</td>' +
      '<td style="padding:6px 8px;text-align:right">' + r.calls + '</td>' +
      '<td style="padding:6px 8px;text-align:right;font-variant-numeric:tabular-nums">' + r.cost_display + '</td>' +
      '</tr>'
    ).join('') +
    '</tbody></table>';
}

function renderAdminTable(rows) {
  const wrap = document.getElementById('au-table-wrap');
  if (!rows.length) { wrap.innerHTML = '<div class="empty">No calls in this period</div>'; return; }
  wrap.innerHTML = '<table style="width:100%;border-collapse:collapse;font-size:12px;min-width:700px">' +
    '<thead><tr style="border-bottom:1px solid var(--ink-faint)">' +
    ['Time','User','Plan','Feature','Model','In','Out','Cost','OK'].map(h =>
      '<th style="text-align:left;padding:5px 8px;font-weight:500;color:var(--ink-soft);white-space:nowrap">' + h + '</th>'
    ).join('') +
    '</tr></thead><tbody>' +
    rows.map(r => {
      const name  = r.pen_name || r.full_name || r.email;
      const time  = new Date(r.created_at).toLocaleString('en-US', { month:'short', day:'numeric', hour:'numeric', minute:'2-digit' });
      const ok    = r.success == 1
        ? '<span style="color:var(--accent)">✓</span>'
        : '<span style="color:var(--danger)" title="' + (r.error_message || '') + '">✗</span>';
      return '<tr style="border-bottom:1px solid var(--ink-faint)">' +
        '<td style="padding:5px 8px;white-space:nowrap;color:var(--ink-soft)">' + time + '</td>' +
        '<td style="padding:5px 8px;white-space:nowrap">' + name + '</td>' +
        '<td style="padding:5px 8px">' + r.plan + '</td>' +
        '<td style="padding:5px 8px">' + r.feature + '</td>' +
        '<td style="padding:5px 8px;color:var(--ink-soft)">' + r.model_class + '</td>' +
        '<td style="padding:5px 8px;text-align:right">' + Number(r.input_tokens).toLocaleString() + '</td>' +
        '<td style="padding:5px 8px;text-align:right">' + Number(r.output_tokens).toLocaleString() + '</td>' +
        '<td style="padding:5px 8px;text-align:right;font-variant-numeric:tabular-nums">' + r.cost_display + '</td>' +
        '<td style="padding:5px 8px;text-align:center">' + ok + '</td>' +
        '</tr>';
    }).join('') +
    '</tbody></table>';
}

// ── QUOTA METER ───────────────────────────────────────────────

async function loadQuotaMeter() {
  const data = await api('/quota.php');
  if (data.success && data.quota) updateQuotaMeter(data.quota);
}

function updateQuotaMeter(quota) {
  if (!quota || !quota.cap_tenths_cent) return;
  const widget = document.getElementById('quota-widget');
  if (!widget) return;

  const pct  = Math.min(100, Math.round((quota.used_tenths_cent / quota.cap_tenths_cent) * 100));
  const fill = document.getElementById('quota-bar-fill');
  const label= document.getElementById('quota-pct-label');
  const nudge= document.getElementById('quota-upgrade-nudge');

  fill.style.width = pct + '%';
  fill.classList.remove('warn', 'danger');
  if (pct >= 80) fill.classList.add('danger');
  else if (pct >= 60) fill.classList.add('warn');

  label.textContent = pct + '%';
  nudge.style.display = pct >= 80 ? 'block' : 'none';

  widget.style.display = 'block';
}

// ── PRESS RELEASE ─────────────────────────────────────────────

function initPressView() {
  const sel = document.getElementById('press-book-id');
  if (!sel) return;
  const current = sel.value;
  sel.innerHTML = '<option value="0">No specific book</option>' +
    (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
  if (current) sel.value = current;
  renderBookBanner('press-book-id', 'press-book-status');
}

async function generatePressRelease() {
  const announcementType = document.getElementById('press-announcement-type').value;
  const keyDetails       = document.getElementById('press-key-details').value.trim();
  const cityState        = document.getElementById('press-city-state').value.trim();
  const contactName      = document.getElementById('press-contact-name').value.trim();
  const contactEmail     = document.getElementById('press-contact-email').value.trim();
  const embargoDate      = document.getElementById('press-embargo-date').value;
  const bookId           = parseInt(document.getElementById('press-book-id').value, 10) || 0;

  if (!keyDetails) { toast('Please describe what this announcement is about', true); return; }
  if (!cityState)  { toast('Please enter a city and state for the dateline', true); return; }
  if (!contactName || !contactEmail) { toast('Please enter media contact name and email', true); return; }
  if (!isValidEmail(contactEmail)) { toast('That contact email doesn\'t look right — check the format', true); return; }

  const btn = document.getElementById('press-generate-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generating…';

  document.getElementById('press-output-card').style.display = 'none';

  try {
    const data = await api('/press_release.php', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId, announcement_type: announcementType,
        key_details: keyDetails, city_state: cityState,
        contact_name: contactName, contact_email: contactEmail,
        embargo_date: embargoDate || null }),
    });

    if (data.success) {
      document.getElementById('press-output').textContent = data.press_release;
      document.getElementById('press-output-card').style.display = 'block';
      document.getElementById('main').scrollTop =
        document.getElementById('press-output-card').offsetTop - 20;

      if (data.quota) {
        updateQuotaMeter(data.quota);
        const pct = Math.min(100, Math.round((data.quota.used_tenths_cent / data.quota.cap_tenths_cent) * 100));
        document.getElementById('press-quota-note').textContent =
          'AI usage this period: ' + pct + '% of monthly allowance';
      }
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'Generation failed — please try again', true);
    }
  } catch(e) {
    toast('Request failed — check your connection and try again', true);
  }

  btn.disabled = false;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generate press release';
}

function copyPressRelease() {
  const text = document.getElementById('press-output').textContent;
  if (!text) return;
  navigator.clipboard.writeText(text).then(
    () => toast('Copied to clipboard'),
    () => toast('Copy failed — select and copy manually', true)
  );
}

// Build the formatted press-release document HTML (shared by PDF + Word).
// Book cover sits in the header up top; the author photo sits at the bottom
// in an "About the Author" block, next to the author's name — the layout a
// journalist expects. Both images are optional: the document degrades to
// clean text-only when a cover or photo isn't set.
function _pressBuildDocHtml(forWord) {
  const text  = document.getElementById('press-output')?.textContent || '';
  const id    = parseInt(document.getElementById('press-book-id')?.value, 10) || 0;
  const book  = id && booksList ? booksList.find(b => b.id == id) : null;
  const cover = book && book.cover_url ? book.cover_url : '';
  const photo = (currentUser && currentUser.author_photo_url) || '';
  const author = (book && book.author) || (currentUser && (currentUser.pen_name || currentUser.full_name)) || '';
  const title = (book && book.title) ? book.title + ' — Press Release' : 'Press Release';

  const serif = 'font-family:Georgia,\'Times New Roman\',serif;';
  let h = '';
  // Header — book cover as the media letterhead.
  if (cover) {
    h += '<div style="text-align:center;margin:0 0 22pt"><img src="' + escapeHtml(cover) +
         '" alt="Book cover" style="max-height:200px;max-width:150px;border:1px solid #ccc"></div>';
  }
  // Body — the press release text, preserving line breaks.
  h += '<div style="' + serif + 'font-size:12pt;line-height:1.55;color:#1a1a1a;white-space:pre-wrap">' +
       escapeHtml(text) + '</div>';
  // Footer — author photo beside name. The text already carries the bio
  // paragraph; this is the visual headshot a journalist pairs with it.
  if (photo) {
    h += '<div style="margin-top:26pt;padding-top:14pt;border-top:1px solid #ccc">' +
         '<table style="border-collapse:collapse"><tr>' +
         '<td style="vertical-align:top;padding-right:14pt;width:96px">' +
           '<img src="' + escapeHtml(photo) + '" alt="Author photo" style="width:90px;height:90px;object-fit:cover;border-radius:50%">' +
         '</td>' +
         '<td style="vertical-align:top;' + serif + 'font-size:11pt;color:#444">' +
           '<div style="font-weight:bold;font-size:12pt;color:#1a1a1a;margin-bottom:3pt">About the Author</div>' +
           (author ? '<div>' + escapeHtml(author) + '</div>' : '') +
           '<div style="color:#888;font-size:10pt;margin-top:4pt">See the author bio above.</div>' +
         '</td></tr></table></div>';
  }
  return { html: h, title: title };
}

function pressDownloadPdf() {
  const src = document.getElementById('press-output');
  if (!src || !src.textContent.trim()) { toast('Generate a press release first', true); return; }
  const built = _pressBuildDocHtml(false);
  const w = window.open('', '_blank');
  if (!w) { toast('Allow pop-ups to download, or use Copy', true); return; }
  w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>' + escapeHtml(built.title) +
    '</title><style>@page{margin:1in;}body{margin:0;}</style></head><body>' + built.html + '</body></html>');
  w.document.close();
  w.onload = function () { w.focus(); w.print(); };
  toast('Opening print dialog — choose "Save as PDF"');
}

function pressDownloadWord() {
  const src = document.getElementById('press-output');
  if (!src || !src.textContent.trim()) { toast('Generate a press release first', true); return; }
  const built = _pressBuildDocHtml(true);
  const html = '<!doctype html><html xmlns:o="urn:schemas-microsoft-com:office:office" ' +
    'xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">' +
    '<head><meta charset="utf-8"><title>' + escapeHtml(built.title) + '</title></head><body>' +
    built.html + '</body></html>';
  const blob = new Blob(['﻿', html], { type: 'application/msword' });
  const url  = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = _safeFileName(built.title) + '.doc';
  document.body.appendChild(a); a.click(); document.body.removeChild(a);
  URL.revokeObjectURL(url);
  toast('Downloading Word document');
}

// ── EVENTS ────────────────────────────────────────────────────

const _evTypeLabels = {
  signing: 'Book signing',
  launchparty: 'Launch party',
  reading: 'Reading',
  podcast: 'Podcast',
  bookclub: 'Book club',
  librarytalk: 'Library talk',
  festival: 'Festival',
  virtual: 'Virtual event',
  other: 'Event',
};
const _evTypeIcons = {
  signing: '✍️', launchparty: '🎉', reading: '📖', podcast: '🎙️', bookclub: '☕',
  librarytalk: '📚', festival: '🎪', virtual: '💻', other: '📅',
};

let _evEvents = [];

async function initEventsView() {
  // Populate the book selector from cached books.
  const sel = document.getElementById('ev-form-book');
  if (sel) {
    const opts = '<option value="0">— None —</option>' +
      (window._books || []).map(b => '<option value="' + b.id + '">' + escapeHtml(b.title) + '</option>').join('');
    sel.innerHTML = opts;
  }
  evHideForm();
  await evLoadList();
}

async function evLoadList() {
  try {
    const data = await api('/events.php?action=list');
    _evEvents = (data && data.success && data.events) ? data.events : [];
    evRender();
  } catch (e) {
    _evEvents = [];
    evRender();
  }
}

function evRender() {
  const upWrap   = document.getElementById('ev-upcoming-list');
  const pastWrap = document.getElementById('ev-past-list');
  const upSec    = document.getElementById('ev-upcoming-section');
  const pastSec  = document.getElementById('ev-past-section');
  const empty    = document.getElementById('ev-empty');

  if (!_evEvents.length) {
    upSec.style.display   = 'none';
    pastSec.style.display = 'none';
    empty.style.display   = 'block';
    return;
  }
  empty.style.display = 'none';

  const now = new Date();
  const upcoming = [];
  const past     = [];
  _evEvents.forEach(e => {
    const startMs = new Date(String(e.start_at).replace(' ', 'T')).getTime();
    if (startMs >= now.getTime()) upcoming.push(e);
    else past.push(e);
  });
  // Upcoming: nearest first. Past: most recent first.
  upcoming.sort((a, b) => new Date(a.start_at) - new Date(b.start_at));
  past.sort((a, b) => new Date(b.start_at) - new Date(a.start_at));

  upWrap.innerHTML   = upcoming.length ? upcoming.map(evCardHtml).join('') : '<div class="card"><div class="empty">No upcoming events.</div></div>';
  pastWrap.innerHTML = past.length     ? past.map(evCardHtml).join('')     : '';
  upSec.style.display   = 'block';
  pastSec.style.display = past.length ? 'block' : 'none';
}

function evCardHtml(e) {
  const label = _evTypeLabels[e.event_type] || _evTypeLabels.other;
  const icon  = _evTypeIcons[e.event_type]  || _evTypeIcons.other;
  const date  = evFormatDate(e.start_at);
  const loc   = e.is_virtual
    ? '<span style="color:var(--accent)">Virtual</span>' + (e.location ? ' · ' + escapeHtml(e.location) : '')
    : (e.location ? escapeHtml(e.location) : '<span style="color:var(--ink-soft)">No location set</span>');
  const bookLine = e.book_title
    ? '<div style="font-size:12px;color:var(--ink-soft);margin-top:4px">📕 ' + escapeHtml(e.book_title) + '</div>'
    : '';
  const urlLine = e.url
    ? '<div style="font-size:12px;margin-top:4px"><a href="' + escapeHtml(e.url) + '" target="_blank" rel="noopener">Event page →</a></div>'
    : '';
  const desc = e.description
    ? '<div style="font-size:13px;color:var(--ink-soft);margin-top:8px;white-space:pre-wrap">' + escapeHtml(e.description) + '</div>'
    : '';
  // Show reminder pill if any reminders are set on this event.
  const hasReminders = !!parseInt(e.remind_24h || 0) || !!parseInt(e.remind_1h || 0);
  const reminderPill = hasReminders
    ? '<span style="display:inline-block;font-size:11px;color:var(--accent);background:var(--accent-faint);padding:1px 6px;border-radius:10px;margin-left:6px">🔔 reminder set</span>'
    : '';
  return '<div class="card" style="margin-bottom:10px" id="ev-card-' + e.id + '">'
    + '<div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap">'
    +   '<div style="flex:1;min-width:0">'
    +     '<div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px">' + icon + '&nbsp; ' + escapeHtml(label) + ' · ' + escapeHtml(date) + reminderPill + '</div>'
    +     '<div style="font-weight:600;font-size:15px">' + escapeHtml(e.title) + '</div>'
    +     '<div style="font-size:13px;margin-top:4px">' + loc + '</div>'
    +     bookLine
    +     urlLine
    +     desc
    +   '</div>'
    +   '<div style="display:flex;gap:6px;flex-shrink:0">'
    +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="evShowForm(' + e.id + ')">Edit</button>'
    +     '<button class="app-btn app-btn-outline app-btn-sm" style="color:#c44;border-color:#fecaca" onclick="evDelete(' + e.id + ')">Delete</button>'
    +   '</div>'
    + '</div>'
    + '<div style="margin-top:12px">'
    +   '<div style="font-size:12px;font-weight:600;color:var(--accent);margin-bottom:6px">📣 Promote this event</div>'
    +   '<div style="display:flex;gap:6px;flex-wrap:wrap">'
    +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="evPromoteSocial(' + e.id + ')">'
    +       '<svg width="11" height="11" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:4px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>'
    +       'Generate social post</button>'
    +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="evPromotePress(' + e.id + ')">'
    +       '<svg width="11" height="11" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:4px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>'
    +       'Generate press release</button>'
    +   '</div>'
    + '</div>'
    + '<div id="ev-promote-out-' + e.id + '" style="display:none;margin-top:10px;background:#fafaf7;border:1px solid #d4cfc4;border-radius:4px;padding:12px"></div>'
    + '</div>';
}

async function evPromoteSocial(id) {
  const e = _evEvents.find(x => x.id == id);
  if (!e) return;
  const out = document.getElementById('ev-promote-out-' + id);
  // Put the clicked button into the app-standard disabled "Generating…" state.
  const btn = (typeof event !== 'undefined' && event && event.target) ? event.target.closest('button') : null;
  const btnOrig = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.innerHTML = 'Generating…'; }
  out.style.display = 'block';
  // First time: show a loading block (output is empty). Regenerate: keep the
  // existing draft visible — the button's "Generating…" state signals progress.
  if (!_evPromoteDrafts[id]) out.innerHTML = _evLoadingHtml('Writing your social post…');

  const date = evFormatDate(e.start_at);
  const eventTypeLabel = (_evTypeLabels[e.event_type] || 'event').toLowerCase();
  let prompt = 'Write a short social media post promoting an upcoming author ' + eventTypeLabel + '. ';
  prompt += 'The post should sound personal and warm, not like an ad. Keep it under 220 characters. ';
  prompt += 'Include a clear call-to-action (RSVP, attend, listen). Avoid AI-sounding phrases like "dive into" or "join us as we".\n\n';
  prompt += 'Event details:\n';
  prompt += '- Title: ' + e.title + '\n';
  prompt += '- Type: ' + (_evTypeLabels[e.event_type] || 'Event') + '\n';
  prompt += '- When: ' + date + '\n';
  if (e.location) prompt += '- Where: ' + e.location + (parseInt(e.is_virtual) ? ' (virtual)' : '') + '\n';
  if (e.url)         prompt += '- Link: ' + e.url + '\n';
  if (e.description) prompt += '- Description: ' + e.description + '\n';
  prompt += '\nOutput ONLY the post text. No preamble, no hashtags suggestion list — just the post.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: e.book_id || 0, prompt: prompt, max_tokens: 250 }),
    });
    if (btn) { btn.disabled = false; btn.innerHTML = btnOrig; }
    if (data && data.success && data.draft) {
      _evPromoteDrafts[id] = data.draft;
      out.innerHTML = '<div style="font-size:11px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Social post — ready to use</div>'
        + '<div style="white-space:pre-wrap;font-size:14px;line-height:1.5;margin-bottom:8px">' + escapeHtml(data.draft) + '</div>'
        + '<div style="display:flex;gap:6px;flex-wrap:wrap">'
        + '<button class="app-btn app-btn-green app-btn-sm" onclick="evSendSocialToPosts(' + id + ')">Send to Social Posts →</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="evCopyPromoteText(' + id + ', _evPromoteDrafts[' + id + '])">Copy</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="evPromoteSocial(' + id + ')">Regenerate</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="evHidePromote(' + id + ')">Close</button></div>';
    } else {
      out.innerHTML = '<div style="color:#c44;font-size:13px">Couldn\'t generate post: ' + escapeHtml(data?.message || 'unknown error') + '</div>';
    }
  } catch (err) {
    if (btn) { btn.disabled = false; btn.innerHTML = btnOrig; }
    out.innerHTML = '<div style="color:#c44;font-size:13px">Network error — try again.</div>';
  }
}

async function evPromotePress(id) {
  const e = _evEvents.find(x => x.id == id);
  if (!e) return;
  const out = document.getElementById('ev-promote-out-' + id);
  const btn = (typeof event !== 'undefined' && event && event.target) ? event.target.closest('button') : null;
  const btnOrig = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.innerHTML = 'Generating…'; }
  out.style.display = 'block';
  if (!_evPromoteDrafts[id]) out.innerHTML = _evLoadingHtml('Writing your press release… this can take 20–30 seconds.');

  const date = evFormatDate(e.start_at);
  let prompt = 'Write a press release announcing an upcoming author event in AP style. ';
  prompt += '400-500 words. Include FOR IMMEDIATE RELEASE header, a newsworthy headline (lead with a local angle if applicable), dateline, lead paragraph answering who/what/where/when/why, body with details, a quote from the author, a short About the Author boilerplate, and Contact placeholder. End with ### centered.\n\n';
  prompt += 'Event details:\n';
  prompt += '- Title: ' + e.title + '\n';
  prompt += '- Type: ' + (_evTypeLabels[e.event_type] || 'Event') + '\n';
  prompt += '- When: ' + date + '\n';
  if (e.location) prompt += '- Where: ' + e.location + (parseInt(e.is_virtual) ? ' (virtual)' : '') + '\n';
  if (e.url)         prompt += '- Event page: ' + e.url + '\n';
  if (e.description) prompt += '- About the event: ' + e.description + '\n';
  prompt += '\nOutput only the press release text.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: e.book_id || 0, prompt: prompt, max_tokens: 1200 }),
    });
    if (btn) { btn.disabled = false; btn.innerHTML = btnOrig; }
    if (data && data.success && data.draft) {
      _evPromoteDrafts[id] = data.draft;
      out.innerHTML = '<div style="font-size:11px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Press release — ready to use</div>'
        + '<div id="ev-press-text-' + id + '" style="white-space:pre-wrap;font-family:var(--font-serif);font-size:13px;line-height:1.6;margin-bottom:8px;max-height:400px;overflow-y:auto;padding:12px;background:#fff;border:1px solid #d4cfc4;border-radius:4px">' + escapeHtml(data.draft) + '</div>'
        + '<div style="display:flex;gap:6px;flex-wrap:wrap"><button class="app-btn app-btn-outline app-btn-sm" onclick="evCopyPromoteText(' + id + ', _evPromoteDrafts[' + id + '])">Copy</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="evDownloadPress(' + id + ', \'pdf\')">Download PDF</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="evDownloadPress(' + id + ', \'word\')">Download Word</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="evPromotePress(' + id + ')">Regenerate</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="evHidePromote(' + id + ')">Close</button></div>';
    } else {
      out.innerHTML = '<div style="color:#c44;font-size:13px">Couldn\'t generate release: ' + escapeHtml(data?.message || 'unknown error') + '</div>';
    }
  } catch (err) {
    if (btn) { btn.disabled = false; btn.innerHTML = btnOrig; }
    out.innerHTML = '<div style="color:#c44;font-size:13px">Network error — try again.</div>';
  }
}

// Latest generated promote text per event id (social/press share the slot —
// only one is shown at a time). Keyed so the action buttons reference it
// cleanly instead of escaping the whole draft through onclick attributes.
let _evPromoteDrafts = {};

// Prominent, honest loading block for event promote generation. The press
// release takes ~20s, and the old small-gray "this takes a few seconds" text
// read as "nothing's happening." Spinner + accent color + accurate timing.
function _evLoadingHtml(label) {
  return '<div style="display:flex;align-items:center;font-size:14px;color:var(--accent);font-weight:600;padding:8px 0">' +
         '<span class="ev-spinner"></span>' + escapeHtml(label) + '</div>';
}

function evCopyPromoteText(id, text) {
  navigator.clipboard.writeText(text).then(
    () => toast('Copied to clipboard'),
    () => toast('Copy failed', true)
  );
}

// Download the event press release as PDF or Word. Wrapper so the title
// (which contains an em-dash and the event name) isn't embedded as a string
// inside the onclick attribute — doing that broke the button markup.
function evDownloadPress(id, fmt) {
  const e = _evEvents.find(x => x.id == id);
  const title = (e && e.title) ? e.title + ' — Press Release' : 'Event Press Release';
  const elId  = 'ev-press-text-' + id;
  if (fmt === 'word') downloadDocWord(elId, title);
  else                downloadDocPdf(elId, title);
}

// Load a generated event social post into the Social Posts composer and jump
// there — so the author can actually post it (handoff modal / AutoPost),
// instead of the inline text being a copy-only dead end. Mirrors
// gvSendToSocialPosts. Carries the event's book link + cover if present.
function evSendSocialToPosts(id) {
  const text = _evPromoteDrafts[id] || '';
  const e = _evEvents.find(x => x.id == id);
  const book = (e && e.book_id && booksList) ? booksList.find(b => b.id == e.book_id) : null;

  const postContent = document.getElementById('post-content');
  const postLink    = document.getElementById('post-link');
  const postImage   = document.getElementById('post-image');
  if (postContent) postContent.value = text;
  // Prefer the event's own URL as the post link; fall back to the book's Amazon link.
  const link = (e && e.url) ? e.url : (book ? cleanAmazonUrl(book.amazon_url || '') : '');
  if (postLink) postLink.value = link;
  if (postImage && book && book.cover_url) postImage.value = book.cover_url;

  if (typeof spUpdateCharCount === 'function') spUpdateCharCount();
  if (typeof updatePostPreview === 'function') updatePostPreview();
  navigate('social');
  toast('Event post sent to Social Posts');
}

function evHidePromote(id) {
  const out = document.getElementById('ev-promote-out-' + id);
  if (out) { out.style.display = 'none'; out.innerHTML = ''; }
}

function evFormatDate(dtStr) {
  if (!dtStr) return '';
  // dtStr is "YYYY-MM-DD HH:MM:SS" from MySQL.
  const d = new Date(String(dtStr).replace(' ', 'T'));
  if (isNaN(d.getTime())) return dtStr;
  return d.toLocaleDateString('en-US', { weekday:'short', month:'short', day:'numeric', year:'numeric' })
    + ' at ' + d.toLocaleTimeString('en-US', { hour:'numeric', minute:'2-digit' });
}

// Split a "YYYY-MM-DD HH:MM:SS" (or ISO) string into form-friendly parts.
function evSplitDateTime(dtStr) {
  if (!dtStr) return { date: '', hour12: '7', min: '00', ampm: 'PM' };
  const s = String(dtStr).replace(' ', 'T');
  const date = s.substring(0, 10);
  const hh24 = parseInt(s.substring(11, 13), 10);
  const mmRaw = parseInt(s.substring(14, 16), 10);
  // Snap to nearest 15 minutes; 60 wraps to 00 (loses up to 7 min, acceptable).
  const snapped = Math.round(mmRaw / 15) * 15;
  const min = (snapped === 60 ? 0 : snapped).toString().padStart(2, '0');
  let h12 = hh24 % 12;
  if (h12 === 0) h12 = 12;
  const ampm = hh24 >= 12 ? 'PM' : 'AM';
  return { date: date, hour12: String(h12), min: min, ampm: ampm };
}

// Combine the four dropdowns into "YYYY-MM-DDTHH:MM:00".
function evCombineDateTime(date, hour12, min, ampm) {
  if (!date) return '';
  let h = parseInt(hour12, 10);
  if (ampm === 'PM' && h !== 12) h += 12;
  if (ampm === 'AM' && h === 12) h = 0;
  return date + 'T' + String(h).padStart(2, '0') + ':' + String(min).padStart(2, '0') + ':00';
}

function evShowForm(id) {
  const card = document.getElementById('ev-form-card');
  const addRow = document.getElementById('ev-add-row');
  const titleEl = document.getElementById('ev-form-title');

  if (id) {
    const e = _evEvents.find(x => x.id == id);
    if (!e) return;
    titleEl.textContent = 'Edit event';
    document.getElementById('ev-form-id').value             = e.id;
    document.getElementById('ev-form-title-input').value    = e.title || '';
    document.getElementById('ev-form-type').value           = e.event_type || 'other';

    const s = evSplitDateTime(e.start_at);
    document.getElementById('ev-form-start-date').value = s.date;
    document.getElementById('ev-form-start-hour').value = s.hour12;
    document.getElementById('ev-form-start-min').value  = s.min;
    document.getElementById('ev-form-start-ampm').value = s.ampm;

    const en = evSplitDateTime(e.end_at);
    document.getElementById('ev-form-end-date').value = en.date;
    document.getElementById('ev-form-end-hour').value = en.hour12;
    document.getElementById('ev-form-end-min').value  = en.min;
    document.getElementById('ev-form-end-ampm').value = en.ampm;

    document.getElementById('ev-form-book').value           = e.book_id || '0';
    document.getElementById('ev-form-location').value       = e.location || '';
    document.getElementById('ev-form-virtual').checked      = !!parseInt(e.is_virtual);
    document.getElementById('ev-form-url').value            = e.url || '';
    document.getElementById('ev-form-description').value    = e.description || '';
    document.getElementById('ev-form-notes').value          = e.notes || '';
    document.getElementById('ev-form-remind-24h').checked   = !!parseInt(e.remind_24h || 0);
    document.getElementById('ev-form-remind-1h').checked    = !!parseInt(e.remind_1h || 0);
  } else {
    titleEl.textContent = 'Add event';
    document.getElementById('ev-form-id').value             = '';
    document.getElementById('ev-form-title-input').value    = '';
    document.getElementById('ev-form-type').value           = 'signing';
    document.getElementById('ev-form-start-date').value     = '';
    document.getElementById('ev-form-start-hour').value     = '7';
    document.getElementById('ev-form-start-min').value      = '00';
    document.getElementById('ev-form-start-ampm').value     = 'PM';
    document.getElementById('ev-form-end-date').value       = '';
    document.getElementById('ev-form-end-hour').value       = '8';
    document.getElementById('ev-form-end-min').value        = '00';
    document.getElementById('ev-form-end-ampm').value       = 'PM';
    document.getElementById('ev-form-book').value           = '0';
    document.getElementById('ev-form-location').value       = '';
    document.getElementById('ev-form-virtual').checked      = false;
    document.getElementById('ev-form-url').value            = '';
    document.getElementById('ev-form-description').value    = '';
    document.getElementById('ev-form-notes').value          = '';
    document.getElementById('ev-form-remind-24h').checked   = true;   // default: yes for new events
    document.getElementById('ev-form-remind-1h').checked    = true;   // default: yes for new events
  }
  card.style.display = 'block';
  addRow.style.display = 'none';
  card.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function evHideForm() {
  document.getElementById('ev-form-card').style.display = 'none';
  document.getElementById('ev-add-row').style.display = 'block';
}

async function evSave() {
  const id    = parseInt(document.getElementById('ev-form-id').value || '0');
  const title = document.getElementById('ev-form-title-input').value.trim();

  const startDate = document.getElementById('ev-form-start-date').value;
  const start = evCombineDateTime(
    startDate,
    document.getElementById('ev-form-start-hour').value,
    document.getElementById('ev-form-start-min').value,
    document.getElementById('ev-form-start-ampm').value
  );

  const endDate = document.getElementById('ev-form-end-date').value;
  const end = endDate ? evCombineDateTime(
    endDate,
    document.getElementById('ev-form-end-hour').value,
    document.getElementById('ev-form-end-min').value,
    document.getElementById('ev-form-end-ampm').value
  ) : '';

  if (!title)     { alert('Title is required.'); return; }
  if (!startDate) { alert('Start date is required.'); return; }

  const payload = {
    id:          id || undefined,
    title:       title,
    event_type:  document.getElementById('ev-form-type').value,
    start_at:    start,
    end_at:      end,
    book_id:     parseInt(document.getElementById('ev-form-book').value || '0'),
    location:    document.getElementById('ev-form-location').value.trim(),
    is_virtual:  document.getElementById('ev-form-virtual').checked ? 1 : 0,
    url:         document.getElementById('ev-form-url').value.trim(),
    description: document.getElementById('ev-form-description').value.trim(),
    notes:       document.getElementById('ev-form-notes').value.trim(),
    remind_24h:  document.getElementById('ev-form-remind-24h').checked ? 1 : 0,
    remind_1h:   document.getElementById('ev-form-remind-1h').checked  ? 1 : 0,
  };

  const action = id ? 'update' : 'create';
  try {
    const data = await api('/events.php?action=' + action, {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    if (data && data.success) {
      toast(id ? 'Event updated' : 'Event created');
      evHideForm();
      await evLoadList();
    } else {
      alert(data?.message || 'Save failed');
    }
  } catch (e) {
    alert('Save failed — try again');
  }
}

async function evDelete(id) {
  const e = _evEvents.find(x => x.id == id);
  if (!e) return;
  if (!confirm('Delete "' + e.title + '"? This cannot be undone.')) return;

  try {
    const data = await api('/events.php?action=delete', {
      method: 'POST',
      body: JSON.stringify({ id: id }),
    });
    if (data && data.success) {
      toast('Event deleted');
      await evLoadList();
    } else {
      alert(data?.message || 'Delete failed');
    }
  } catch (e2) {
    alert('Delete failed — try again');
  }
}

// ── BOOK DESCRIPTION ──────────────────────────────────────────

// Suggest comparable titles from the LIVE book-form fields. Like the tagline
// and description generators, it sends what's on screen (title, subtitle,
// genre, description) inline — never just book_id — so it works on an unsaved
// book and reflects edits that haven't been saved yet. Any text already in the
// comps field is treated as a starting point to refine/expand.
async function aiBookComps(btn) {
  const title       = document.getElementById('book-title').value.trim();
  const subtitle    = document.getElementById('book-subtitle').value.trim();
  const genre       = document.getElementById('book-genre').value.trim();
  const description = document.getElementById('book-description').value.trim();
  const bookId      = parseInt(document.getElementById('book-id').value, 10) || 0;
  const seed        = document.getElementById('book-comps').value.trim();

  if (!title) { toast('Add a book title first — the AI matches comps to your book', true); return; }

  const label = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.textContent = 'Thinking…'; }

  let prompt = seed
    ? 'Refine and expand this list of comparable titles: ' + seed + '. '
    : 'Suggest comparable titles for this book. ';
  prompt += 'Give 5-6 well-known books with a similar genre, tone, or readership, '
          + 'the kind an author would cite to position their book. '
          + 'For "' + title + '"';
  if (subtitle)    prompt += ': ' + subtitle;
  if (genre)       prompt += '. Genre: ' + genre;
  if (description) prompt += '. Description: ' + description.slice(0, 1200);
  prompt += '. Output only the titles (with authors), comma-separated, no commentary.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: bookId, prompt, max_tokens: 200 }),
    });
    if (data.success && data.draft) {
      document.getElementById('book-comps').value = data.draft.trim();
      if (data.quota) updateQuotaMeter(data.quota);
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'Could not suggest titles — try again', true);
    }
  } catch (e) { toast('Request failed — check your connection', true); }

  if (btn) { btn.disabled = false; btn.innerHTML = label; }
}

async function aiDraftDescription() {
  const btn = document.querySelector('#books-form-view .ai-btn');
  if (!btn) return;
  btn.textContent = 'Drafting…'; btn.disabled = true;

  const title    = document.getElementById('book-title').value.trim();
  const subtitle = document.getElementById('book-subtitle').value.trim();
  const genre    = document.getElementById('book-genre').value.trim();
  const bookId   = parseInt(document.getElementById('book-id').value, 10) || 0;
  const seed     = document.getElementById('book-description').value.trim();

  if (!title) { toast('Please enter a book title first', true); btn.textContent = '✦ AI draft'; btn.disabled = false; return; }

  let prompt = seed
    ? 'Using the following as a starting point, write a polished book description: ' + seed
    : 'Write a compelling book description';
  if (title)    prompt += ' for "' + title + '"';
  if (subtitle) prompt += ': ' + subtitle;
  if (genre)    prompt += '. Genre: ' + genre;
  prompt += '. 150-250 words. Lead with a hook, build tension, end with a reason to buy. Output only the description.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'book_description', book_id: bookId, prompt, max_tokens: 500 }),
    });
    if (data.success && data.draft) {
      document.getElementById('book-description').value = data.draft;
      if (data.quota) updateQuotaMeter(data.quota);
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'AI draft unavailable — try again', true);
    }
  } catch(e) { toast('AI draft unavailable — try again', true); }

  btn.textContent = '✦ AI draft'; btn.disabled = false;
}

// ── TAGLINE / LOGLINE ─────────────────────────────────────────

async function aiTagline(type) {
  const fieldId   = type === 'tagline' ? 'book-tagline'     : 'book-logline';
  const resultsId = type === 'tagline' ? 'tagline-results'  : 'logline-results';
  const btnEl = event.currentTarget;

  // Send the live form fields, not just book_id. This is what makes generation
  // match what's on screen even before the book is saved — mirrors the AI
  // description drafter. Without it, an unsaved book had no grounding and the
  // model invented an unrelated book.
  const title       = document.getElementById('book-title').value.trim();
  const subtitle    = document.getElementById('book-subtitle').value.trim();
  const genre       = document.getElementById('book-genre').value.trim();
  const description = document.getElementById('book-description').value.trim();
  const bookId = parseInt(document.getElementById('book-id').value, 10) || 0;
  const seed   = document.getElementById(fieldId).value.trim();

  if (!title) {
    toast('Add a book title first — the AI writes from your book’s details', true);
    return;
  }

  btnEl.disabled = true;
  btnEl.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg> Generating…';

  const resultsEl = document.getElementById(resultsId);
  resultsEl.style.display = 'none';
  resultsEl.innerHTML = '';

  try {
    const data = await api('/tagline.php', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId, type, seed, title, subtitle, genre, description }),
    });

    if (data.success && data.lines && data.lines.length) {
      if (data.quota) updateQuotaMeter(data.quota);
      resultsEl.innerHTML = data.lines.map((line, i) =>
        '<div style="padding:9px 12px;cursor:pointer;font-size:13px;' +
        (i > 0 ? 'border-top:1px solid var(--ink-faint);' : '') +
        'transition:background 0.1s" ' +
        'onmouseover="this.style.background=\'var(--paper)\'" ' +
        'onmouseout="this.style.background=\'\'" ' +
        'onclick="document.getElementById(\'' + fieldId + '\').value=' + JSON.stringify(line) + ';' +
        'document.getElementById(\'' + resultsId + '\').style.display=\'none\'">' +
        escHtml(line) + '</div>'
      ).join('');
      resultsEl.style.display = 'block';
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade to generate more', true);
    } else {
      toast(data.message || 'Generation failed — try again', true);
    }
  } catch(e) {
    toast('Request failed — check your connection', true);
  }

  btnEl.disabled = false;
  btnEl.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg> Generate ideas';
}

// ── SELL SHEET ────────────────────────────────────────────────

function initSellSheetView() {
  const sel = document.getElementById('ss-book-id');
  if (!sel) return;
  const current = sel.value;
  sel.innerHTML = '<option value="0">No specific book</option>' +
    (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
  if (current) sel.value = current;

  // Pre-fill author bio from profile if the field is empty
  const bioField = document.getElementById('ss-author-bio');
  if (bioField && !bioField.value && currentUser && currentUser.bio) {
    bioField.value = currentUser.bio;
  }

  ssBookHint();
}

async function generateSellSheet() {
  const audience    = document.getElementById('ss-audience').value;
  const keyPoints   = document.getElementById('ss-key-points').value.trim();
  const compTitles  = document.getElementById('ss-comp-titles').value.trim();
  const contactName = document.getElementById('ss-contact-name').value.trim();
  const contactEmail= document.getElementById('ss-contact-email').value.trim();
  const bookId      = parseInt(document.getElementById('ss-book-id').value, 10) || 0;

  if (!keyPoints)    { toast('Please enter the key selling points', true); return; }
  if (!contactName || !contactEmail) { toast('Please enter contact name and email', true); return; }
  if (!isValidEmail(contactEmail)) { toast('That contact email doesn\'t look right — check the format', true); return; }

  const btn = document.getElementById('ss-generate-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generating…';

  document.getElementById('ss-output-card').style.display = 'none';

  try {
    const data = await api('/sell_sheet.php', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId, audience, key_points: keyPoints,
        comp_titles: compTitles || null, author_bio: document.getElementById('ss-author-bio').value.trim() || null,
        contact_name: contactName, contact_email: contactEmail }),
    });

    if (data.success) {
      document.getElementById('ss-output').textContent = data.sell_sheet;
      document.getElementById('ss-output-card').style.display = 'block';
      document.getElementById('main').scrollTop =
        document.getElementById('ss-output-card').offsetTop - 20;
      if (data.quota) {
        updateQuotaMeter(data.quota);
        const pct = Math.min(100, Math.round((data.quota.used_tenths_cent / data.quota.cap_tenths_cent) * 100));
        document.getElementById('ss-quota-note').textContent =
          'AI usage this period: ' + pct + '% of monthly allowance';
      }
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'Generation failed — please try again', true);
    }
  } catch(e) { toast('Request failed — check your connection and try again', true); }

  btn.disabled = false;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generate sell sheet';
}

function copySellSheet() {
  const text = document.getElementById('ss-output').textContent;
  if (!text) return;
  navigator.clipboard.writeText(text).then(
    () => toast('Copied to clipboard'),
    () => toast('Copy failed — select and copy manually', true)
  );
}

// Two-state book banner for any document tool, matching the graphics pages'
// gvUpdateBookStatus: yellow ⚠️ when no book is selected, green 📖 when one is.
// Shows immediately on view load so the user knows a book improves the output
// before generating. selectorId = the <select>, bannerId = the banner <div>.
function renderBookBanner(selectorId, bannerId) {
  const sel = document.getElementById(selectorId);
  const el  = document.getElementById(bannerId);
  if (!sel || !el) return;
  const bookId = parseInt(sel.value, 10) || 0;
  if (bookId > 0) {
    const bookText = sel.options[sel.selectedIndex]?.text || '';
    el.style.background = 'var(--accent-faint)';
    el.style.color = 'var(--accent)';
    el.style.border = '1px solid transparent';
    el.innerHTML = '<span style="margin-right:5px">📖</span>Working with: <strong>' + escapeHtml(bookText) + '</strong>';
  } else {
    el.style.background = '#fff8e1';
    el.style.color = '#7a5300';
    el.style.border = '1px solid #f0d97a';
    el.innerHTML = '<span style="margin-right:5px">⚠️</span><strong>No book selected.</strong> ' +
      'Pick one above for the AI to tailor the output to your book\'s title, genre, and description. Without one, you\'ll get a more generic result.';
  }
}

function ssBookHint() { renderBookBanner('ss-book-id', 'ss-book-status'); }

// Filename stem for downloaded sell sheets — uses the selected book's title.
function ssDocTitle() { return docTitle('ss-book-id', 'Sell Sheet'); }

// Book-aware document title for downloads: "<Book Title> — <suffix>" when a
// book is selected, else just "<suffix>". Shared by every document tool's
// Download PDF/Word buttons.
function docTitle(bookSelectorId, suffix) {
  const id = parseInt(document.getElementById(bookSelectorId)?.value, 10) || 0;
  const book = id && booksList ? booksList.find(b => b.id == id) : null;
  return ((book && book.title) ? book.title + ' — ' + suffix : suffix);
}

// AI-suggest key selling points (mirrors aiDraftCompTitles). Pulls from the
// selected book's metadata; refines existing text if the author already typed
// some. Walkthrough 2026-06-12: authors wanted help here, not a blank box.
async function aiDraftKeyPoints() {
  const btn = event.target.closest('button');
  btn.textContent = 'Thinking…'; btn.disabled = true;

  const bookId = parseInt(document.getElementById('ss-book-id').value, 10) || 0;
  const seed   = document.getElementById('ss-key-points').value.trim();
  const prompt = seed
    ? 'Refine and expand these key selling points for a book sell sheet, aimed at booksellers and reviewers: ' + seed
        + '. Return 4-6 punchy bullet-style points, one per line, no numbering, no preamble.'
    : 'Suggest 4-6 key selling points for this book\'s sell sheet — the things that would make a bookseller or reviewer take notice '
        + '(series potential, comparable audiences, regional or topical hooks, format/availability strengths). '
        + 'Base them on the book\'s actual genre, themes, and description. One per line, no numbering, no preamble.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: bookId, prompt, max_tokens: 220 }),
    });
    if (data.success && data.draft) {
      document.getElementById('ss-key-points').value = data.draft.trim();
      if (data.quota) updateQuotaMeter(data.quota);
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached', true);
    } else {
      toast(data.message || 'Could not generate suggestions', true);
    }
  } catch (e) { toast('Request failed — try again', true); }

  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg> AI suggest';
  btn.disabled = false;
}

// ── Document export (reusable across all text-output AI tools) ────
// Zero-dependency PDF + Word download for any element's plain text.
// PDF: opens a print window with clean typography; the user picks
// "Save as PDF" in the browser print dialog (no JS PDF library — keeps
// the no-new-dependencies rule). Word: a Blob with a minimal Word-readable
// HTML wrapper saved as .doc (opens in Word, Pages, and Google Docs).
function _safeFileName(title) {
  return (String(title || 'document').toLowerCase()
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')) || 'document';
}

function downloadDocPdf(elId, title) {
  const src = document.getElementById(elId);
  if (!src || !src.textContent.trim()) { toast('Nothing to export yet', true); return; }
  const safeTitle = escapeHtml(title || 'Document');
  const body = escapeHtml(src.textContent);
  const w = window.open('', '_blank');
  if (!w) { toast('Allow pop-ups to download, or use Copy', true); return; }
  w.document.write(
    '<!doctype html><html><head><meta charset="utf-8"><title>' + safeTitle + '</title>' +
    '<style>@page{margin:1in;}body{font-family:Georgia,"Times New Roman",serif;font-size:12pt;' +
    'line-height:1.55;color:#1a1a1a;white-space:pre-wrap;}h1{font-size:15pt;margin:0 0 16pt;}</style>' +
    '</head><body><h1>' + safeTitle + '</h1>' + body + '</body></html>'
  );
  w.document.close();
  // Give the new window a tick to render before invoking print.
  w.onload = function () { w.focus(); w.print(); };
  toast('Opening print dialog — choose "Save as PDF"');
}

function downloadDocWord(elId, title) {
  const src = document.getElementById(elId);
  if (!src || !src.textContent.trim()) { toast('Nothing to export yet', true); return; }
  const safeTitle = escapeHtml(title || 'Document');
  const body = escapeHtml(src.textContent);
  const html = '<!doctype html><html xmlns:o="urn:schemas-microsoft-com:office:office" ' +
    'xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">' +
    '<head><meta charset="utf-8"><title>' + safeTitle + '</title></head>' +
    '<body style="font-family:Georgia,serif;font-size:12pt;line-height:1.55;white-space:pre-wrap">' +
    '<h1 style="font-size:15pt">' + safeTitle + '</h1>' + body + '</body></html>';
  const blob = new Blob(['﻿', html], { type: 'application/msword' });
  const url  = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = _safeFileName(title) + '.doc';
  document.body.appendChild(a); a.click();
  document.body.removeChild(a); URL.revokeObjectURL(url);
  toast('Downloading Word document');
}

// ── KDP KEYWORDS + CATEGORIES ─────────────────────────────────

function initKdpKeywordsView() {
  const sel = document.getElementById('kw-book-id');
  if (!sel) return;
  const current = sel.value;
  sel.innerHTML = '<option value="0">— pick a book —</option>' +
    (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
  if (current) sel.value = current;
  renderBookBanner('kw-book-id', 'kw-book-status');
}

async function generateKdpKeywords() {
  const bookId    = parseInt(document.getElementById('kw-book-id').value, 10) || 0;
  const extraHint = document.getElementById('kw-extra-hint').value.trim();
  if (!bookId) { toast('Please pick a book first', true); return; }

  const btn = document.getElementById('kw-generate-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generating…';
  document.getElementById('kw-output-card').style.display = 'none';

  try {
    const data = await api('/kdp_keywords.php', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId, extra_hint: extraHint || null }),
    });
    if (data.success) {
      document.getElementById('kw-output').textContent = data.suggestions;
      document.getElementById('kw-output-card').style.display = 'block';
      document.getElementById('main').scrollTop =
        document.getElementById('kw-output-card').offsetTop - 20;
      if (data.quota) {
        updateQuotaMeter(data.quota);
        const pct = Math.min(100, Math.round((data.quota.used_tenths_cent / data.quota.cap_tenths_cent) * 100));
        document.getElementById('kw-quota-note').textContent =
          'AI usage this period: ' + pct + '% of monthly allowance';
      }
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'Generation failed — please try again', true);
    }
  } catch(e) { toast('Request failed — check your connection and try again', true); }

  btn.disabled = false;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generate keywords + categories';
}

function copyKdpKeywords() {
  const text = document.getElementById('kw-output').textContent;
  if (!text) return;
  navigator.clipboard.writeText(text).then(
    () => toast('Copied to clipboard'),
    () => toast('Copy failed — select and copy manually', true)
  );
}

// ── eBOOK CONVERTER ───────────────────────────────────────────
// Upload a manuscript → AI pre-flight (diagnose, never rewrite) → CloudConvert
// makes a clean EPUB → download. Backend: api/ebook_convert.php.
let ebookCurrentConversion = 0;
let ebookPollTimer = null;
// Which reader produced the document on screen ('ocr' or 'pdftext'), and how
// many pages the scan has. The author is told which one ran — for a lot of them
// reading a scan is the whole reason they came, so it should not be invisible.
let ebookDocSource = '';
let ebookOcrPages = 0;
let ebookProgToken = '';
let ebookProgTimer = null;

// Which pipeline the tool is pointed at: 'reflow' (novel/text → Word file) or
// 'fxl' (picture book → print-ready PDF, every page locked as designed).
let ebookKind = 'reflow';

function ebookSetKind(kind) {
  if (kind !== 'fxl' && kind !== 'pdftext' && kind !== 'ocr') kind = 'reflow';
  ebookKind = kind;
  const fxl  = (kind === 'fxl');
  const pdft = (kind === 'pdftext');
  const ocr  = (kind === 'ocr');
  const anyPdf = fxl || pdft || ocr;

  document.getElementById('eb-mode-reflow').classList.toggle('active', kind === 'reflow');
  document.getElementById('eb-mode-fxl').classList.toggle('active', fxl);
  document.getElementById('eb-mode-pdftext').classList.toggle('active', pdft);
  document.getElementById('eb-mode-ocr').classList.toggle('active', ocr);
  document.getElementById('eb-howto-reflow').style.display  = (kind === 'reflow') ? 'block' : 'none';
  document.getElementById('eb-howto-fxl').style.display     = fxl  ? 'block' : 'none';
  document.getElementById('eb-howto-pdftext').style.display = pdft ? 'block' : 'none';
  document.getElementById('eb-howto-ocr').style.display     = ocr  ? 'block' : 'none';
  const offer = document.getElementById('eb-ocr-offer-card');
  if (offer) offer.style.display = 'none';

  const file = document.getElementById('eb-file');
  file.accept = anyPdf ? '.pdf' : '.docx,.doc,.odt,.rtf,.txt';
  file.value = '';
  document.getElementById('eb-file-name').textContent = '';
  document.getElementById('eb-upload-btn').disabled = true;

  const fileLabel = fxl ? 'Print-ready interior PDF'
                        : (pdft ? 'The PDF of your book'
                                : (ocr ? 'The scanned PDF of your book' : 'Manuscript file'));
  document.getElementById('eb-file-label').innerHTML = fileLabel +
    ' <span style="color:var(--danger)">*</span>';
  document.getElementById('eb-file-hint').textContent = fxl
    ? 'Accepted: .pdf — up to 100 MB. The same file you send to your printer.'
    : (pdft
      ? 'Accepted: .pdf — up to 60 MB. Best with real text in it; if it turns out to be a scan we\u2019ll say so and offer to read it.'
      : (ocr
        ? 'Accepted: .pdf — up to 60 MB. The scan of your printed pages, however it was made.'
        : 'Accepted: .docx · .doc · .odt · .rtf · .txt — up to 25 MB'));
  document.getElementById('eb-cover-hint').textContent = fxl
    ? 'Only needed if your PDF is interior-only. If page 1 of your PDF is already the front cover, skip this — we use it. JPG, PNG, WebP or PDF, up to 100 MB.'
    : 'If you linked a book above, we use its cover automatically — so the eBook opens on the cover. '
      + 'Upload one here to override it, or if this manuscript isn\'t linked to a book. '
      + 'JPG, PNG, WebP or PDF, up to 100 MB — a full print cover (back, spine and front on one wide '
      + 'page) is fine, we take the front from it.';
  document.getElementById('eb-upload-btn').textContent = fxl
    ? 'Check my picture book'
    : (pdft ? 'Process my PDF' : (ocr ? 'Read my scanned book' : 'Check my manuscript'));
  document.getElementById('eb-upload-title').textContent = fxl
    ? '1 · Upload your picture book'
    : (pdft ? '1 · Upload your PDF'
            : (ocr ? '1 · Upload your scanned book' : '1 · Upload your manuscript'));

  // What this choice costs, said plainly where they are about to act.
  // ⚠ Worded as what it USUALLY costs, not a promise. The price follows what we
  // actually had to do, not the box they ticked: a "printed PDF" that turns out
  // to be a scan costs $14.95, and a "scan" that turns out to have real text
  // costs $9.95. Both corrections are announced when they happen, and nobody
  // pays until they have seen the finished book — but the wording here must not
  // set up a number we might not honour.
  const cost = document.getElementById('eb-cost-line');
  if (cost) {
    cost.innerHTML = ocr
      ? 'Reading a scanned book is <strong>$14.95</strong>, one time — pages are read '
        + 'individually, which costs us per page. Nothing to pay until you have seen it.'
      : 'This conversion is <strong>$9.95</strong>, one time. Nothing to pay until you have '
        + 'seen the finished book.'
        + (pdft ? ' (If your PDF turns out to be a scan, it becomes $14.95 — we\'ll tell you first.)' : '');
  }

  // Switching kind invalidates any in-progress conversion on screen.
  ebookCurrentConversion = 0;
  if (ebookPollTimer) { clearTimeout(ebookPollTimer); ebookPollTimer = null; }
  ebookDocReset();
  document.getElementById('eb-preflight-card').style.display = 'none';
  document.getElementById('eb-editor-card').style.display = 'none';
  document.getElementById('eb-result-card').style.display = 'none';
}

// ============================================================
//  THE EDIT STAGE
//
//  Recovering text from a printed page cannot be perfect, so the converter
//  says which spots it was unsure of and the author fixes them here — before
//  anything is exported or paid for. Two rules shape this code:
//    1. the author sees the WHOLE book, not a sample; a preview you pay to
//       finish is only fair if it isn't a teaser.
//    2. only CHANGED blocks are sent back, keyed by id, so an edit on page 3
//       never has to re-upload a 300-page novel — and two edits in different
//       chapters can't overwrite each other.
// ============================================================
let ebookDoc      = null;   // {total, offset, limit, stats, warnings}
let ebookDirty    = {};     // block id -> {text?, kind?, level?}
let ebookFlagIdx  = -1;

// Full-screen reading: the same document, given the whole window. Proofreading
// your own prose in a 60vh box inside a dashboard is the wrong shape for the job.
function ebookToggleReading() {
  const card = document.getElementById('eb-editor-card');
  const on = card.classList.toggle('reading');
  document.getElementById('eb-ed-fullscreen').textContent =
    on ? 'Exit full screen' : 'Read full screen';
  document.body.style.overflow = on ? 'hidden' : '';
  // Widths changed, so every paragraph needs to re-measure its own height.
  document.querySelectorAll('#eb-ed-blocks .eb-blk-txt').forEach(ebookAutoGrow);
  if (!on) { card.scrollIntoView({ block: 'start' }); }
}

document.addEventListener('keydown', e => {
  if (e.key !== 'Escape') return;
  const card = document.getElementById('eb-editor-card');
  if (card && card.classList.contains('reading')) ebookToggleReading();
});

function ebookDocReset() {
  const card = document.getElementById('eb-editor-card');
  if (card && card.classList.contains('reading')) { ebookToggleReading(); }
  ebookDoc = null;
  ebookDirty = {};
  ebookFlagIdx = -1;
  const b = document.getElementById('eb-ed-blocks');
  if (b) b.innerHTML = '';
}

function ebookDirtyCount() { return Object.keys(ebookDirty).length; }

// patch === null means the block is back to its original text, so the pending
// change is dropped entirely rather than saved as a no-op.
function ebookMarkDirty(id, patch) {
  if (patch === null) { delete ebookDirty[id]; }
  else { ebookDirty[id] = Object.assign(ebookDirty[id] || { id: id }, patch); }

  const n = ebookDirtyCount();
  const save = document.getElementById('eb-ed-save');
  save.disabled = (n === 0);
  save.textContent = n ? ('Save ' + n + ' change' + (n === 1 ? '' : 's')) : 'Save changes';
  document.getElementById('eb-ed-note').textContent = n
    ? (n + ' unsaved change' + (n === 1 ? '' : 's') + ' — nothing is committed until you save.')
    : '';
}

// Textareas grow to their content: an author proofreading prose should never
// have to scroll inside a single paragraph to see the end of a sentence.
// Paint the backdrop that sits behind the transparent textarea, wrapping each
// flagged word in <mark>. The text must be escaped — it is the author's prose
// going into innerHTML — and the trailing newline keeps the backdrop's height
// in step with the textarea when the text ends on a line break.
function ebookPaintBackdrop(back, text, words) {
  let html = escapeHtml(text) + '\n';
  if (words && words.length) {
    words.forEach(w => {
      if (!w) return;
      // Match against the ESCAPED form: a word containing an apostrophe or
      // ampersand no longer looks like itself once the prose is escaped.
      const esc = escapeHtml(w).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      // ⚠ WHOLE WORDS ONLY. A bare substring match meant a one-letter flag —
      // and OCR produces those constantly from line art — highlighted that
      // letter inside every ordinary word: a flagged "d" lit up ma[d]e,
      // modifie[d], calle[d], stu[d]y, [d]one and [d]ivide right across a
      // clean paragraph. The leading boundary is captured rather than looked
      // behind so this works in every browser.
      const re = new RegExp('([^\\p{L}\\p{N}]|^)(' + esc + ')(?=[^\\p{L}\\p{N}]|$)', 'gu');
      html = html.replace(re, '$1<mark>$2</mark>');
    });
  }
  back.innerHTML = html;
}

function ebookAutoGrow(el) {
  el.style.height = 'auto';
  // scrollHeight is 0 for anything not laid out yet; keep the default rows
  // rather than collapsing the paragraph to nothing.
  if (el.scrollHeight > 0) el.style.height = (el.scrollHeight + 2) + 'px';
}

function ebookRenderEditor(data) {
  ebookDoc = {
    total: data.total_blocks || 0,
    offset: data.offset || 0,
    limit: data.limit || 300,
    stats: data.stats || {},
    warnings: data.warnings || [],
  };
  const s = ebookDoc.stats;
  const chip = (label, val) => '<span class="eb-chip"><strong>' + val + '</strong> ' + label + '</span>';
  let chips = '';

  // Say which reader ran. An author whose book only exists as a scan came here
  // specifically for this, and a silent success would hide the one thing that
  // makes it worth paying for. It also sets the right expectation about the
  // two limits of reading a scanned page: no italics, and OCR's own uncertainty.
  const wasOcr = (ebookDocSource === 'ocr') || !!s.pages_ocred;
  const intro = document.getElementById('eb-ed-intro');
  if (intro) {
    intro.innerHTML = wasOcr
      ? 'Here is your whole book, read <strong>off the scanned pages</strong>. Every word we '
        + 'were not certain of is highlighted, ready for you to correct. Italics and bold '
        + 'could not be recovered from a scanned page — add those in your Word file.'
      : 'Here is your whole book, read back out of the PDF. Everything we weren\u2019t sure '
        + 'about is highlighted — usually a word that ran together where the printed line broke.';
  }
  if (s.truncated) {
    chips += '<span class="eb-chip" style="background:#fff8e1;border-color:#e8c88a">'
           + '<strong>Long book</strong> — only the first part was read</span>';
  }
  if (s.pages)      chips += chip(wasOcr ? 'pages scanned' : 'pages read', Number(s.pages).toLocaleString());
  if (s.word_count) chips += chip('words', Number(s.word_count).toLocaleString());
  if (s.headings)   chips += chip('chapters found', s.headings);
  if (s.images)     chips += chip('pictures kept', s.images);
  if (s.toc_linked) chips += chip('contents entries linked', s.toc_linked);
  if (s.dropped && s.dropped.noise)
    chips += chip('specks of artwork ignored', Number(s.dropped.noise).toLocaleString());
  if (s.dropped && s.dropped.running_head)
    chips += chip('headers &amp; page numbers removed',
      Number(s.dropped.running_head + (s.dropped.page_number || 0)).toLocaleString());
  document.getElementById('eb-ed-stats').innerHTML = chips;

  document.getElementById('eb-ed-warnings').innerHTML = (ebookDoc.warnings || [])
    .map(w => '<div class="eb-warn">' + escapeHtml(w.text) + '</div>').join('');

  // Show the card BEFORE rendering the blocks. Each paragraph sizes itself
  // from scrollHeight, which is 0 while the element is display:none — render
  // into a hidden card and every paragraph collapses to a sliver.
  const card = document.getElementById('eb-editor-card');
  card.style.display = 'block';
  ebookRenderBlocks(data.blocks || []);
  document.getElementById('main').scrollTop = card.offsetTop - 20;
}

function ebookRenderBlocks(blocks) {
  const wrap = document.getElementById('eb-ed-blocks');
  wrap.innerHTML = '';
  blocks.forEach(b => {
    // An extracted illustration. Rendered as a picture with a Remove button —
    // detection is geometric, so an occasional crop will be a stray rule or a
    // smudge and the author needs a one-click way to say so.
    if (b.kind === 'img') {
      const row = document.createElement('div');
      row.className = 'eb-blk is-img';
      row.id = 'eb-blk-' + b.id;
      row.innerHTML = '<div class="eb-img-wrap"><img class="eb-img" alt="Illustration from your book">'
        + '<button type="button" class="app-btn app-btn-sm eb-img-del">Remove picture</button></div>';
      row.querySelector('.eb-img-del').onclick = () => {
        ebookMarkDirty(b.id, { delete: true });
        row.style.opacity = '.35';
        row.querySelector('.eb-img-del').textContent = 'Will be removed on save';
      };
      // Fetched with the auth header and shown as a blob: these sit outside the
      // docroot, so there is no URL that would load them in an <img> directly.
      ebookLoadImage(b.id, row.querySelector('.eb-img'));
      wrap.appendChild(row);
      return;
    }
    const isH = (b.kind === 'h');
    const lvl = isH ? (b.level || 1) : 0;
    const row = document.createElement('div');
    row.className = 'eb-blk' + (isH ? ' is-h lvl' + lvl : '') + (b.flag ? ' flagged' : '');
    row.id = 'eb-blk-' + b.id;
    if (b.flag) row.dataset.flag = '1';

    const tag = document.createElement('div');
    tag.className = 'eb-blk-tag';
    tag.textContent = isH ? ('H' + lvl) : '';
    row.appendChild(tag);

    // Contents entries: show whether this one will actually jump somewhere.
    // Without this the author has no way to tell a working table of contents
    // from a list of dead text — the links only exist in the built file.
    if (b.link || b.toc_entry) {
      const flag = document.createElement('div');
      flag.className = 'eb-toc-flag' + (b.link ? ' is-linked' : '');
      flag.textContent = b.link ? '↪ links to this chapter' : 'no matching chapter found';
      flag.title = b.link
        ? 'This contents entry will jump to its chapter in the finished eBook.'
        : 'We could not find a chapter with this exact title, so this entry will be plain text. '
          + 'Editing it to match the chapter heading exactly will link it.';
      row.appendChild(flag);
    }

    // Backdrop + transparent textarea: the only way to show a highlight on the
    // exact word while keeping a normal, editable text field.
    const fieldWrap = document.createElement('div');
    fieldWrap.className = 'eb-blk-wrap';

    const back = document.createElement('div');
    back.className = 'eb-blk-back';
    back.setAttribute('aria-hidden', 'true');

    const ta = document.createElement('textarea');
    ta.className = 'eb-blk-txt';
    ta.rows = 1;
    ta.value = b.text || '';
    ta.spellcheck = true;
    row.dataset.orig = b.text || '';
    ta.addEventListener('input', () => {
      ebookAutoGrow(ta);
      const changed = (ta.value !== row.dataset.orig);
      ebookMarkDirty(b.id, changed ? { text: ta.value } : null);
      // An edit is PENDING, not committed. The flag stays put so the author can
      // still see which spot this was, compare against the original, and change
      // their mind — the count only drops when they save. Clearing it on the
      // first keystroke made a typed character feel like a saved decision.
      row.classList.toggle('edited', changed);
      ebookPaintBackdrop(back, ta.value, row.dataset.words
        ? row.dataset.words.split('\u001F') : null);
    });
    if (b.flag && b.flag.length) row.dataset.words = b.flag.join('\u001F');
    ebookPaintBackdrop(back, ta.value, b.flag || null);

    fieldWrap.appendChild(back);
    fieldWrap.appendChild(ta);
    row.appendChild(fieldWrap);

    const ctl = document.createElement('div');
    ctl.className = 'eb-blk-ctl';
    const sel = document.createElement('select');
    sel.title = 'Is this a chapter heading?';
    [['p', 'Text'], ['1', 'Heading 1'], ['2', 'Heading 2'], ['3', 'Heading 3']].forEach(o => {
      const opt = document.createElement('option');
      opt.value = o[0]; opt.textContent = o[1];
      sel.appendChild(opt);
    });
    sel.value = isH ? String(lvl) : 'p';
    const origKind = isH ? String(lvl) : 'p';
    sel.addEventListener('change', () => {
      const v = sel.value;
      // Rebuild only the heading classes — assigning className wholesale would
      // wipe the flag and pending-edit state sitting on the same row.
      row.classList.remove('is-h', 'lvl1', 'lvl2', 'lvl3');
      if (v === 'p') {
        tag.textContent = '';
        ebookMarkDirty(b.id, (v === origKind) ? null : { kind: 'p' });
      } else {
        row.classList.add('is-h', 'lvl' + v);
        tag.textContent = 'H' + v;
        ebookMarkDirty(b.id, (v === origKind) ? null
          : { kind: 'h', level: parseInt(v, 10) });
      }
      row.classList.toggle('edited',
        (v !== origKind) || (ta.value !== row.dataset.orig));
      ebookAutoGrow(ta);
    });
    ctl.appendChild(sel);

    // Undo: "make a change and look at it" only works if it can be taken back.
    const undo = document.createElement('button');
    undo.type = 'button';
    undo.className = 'eb-blk-undo';
    undo.title = 'Put this paragraph back the way it was';
    undo.textContent = '\u21BA';
    undo.addEventListener('click', () => {
      ta.value = row.dataset.orig;
      sel.value = origKind;
      row.classList.remove('is-h', 'lvl1', 'lvl2', 'lvl3');
      if (origKind !== 'p') { row.classList.add('is-h', 'lvl' + origKind); }
      tag.textContent = origKind === 'p' ? '' : 'H' + origKind;
      row.classList.remove('edited');
      ebookMarkDirty(b.id, null);
      ebookPaintBackdrop(back, ta.value,
        row.dataset.words ? row.dataset.words.split('\u001F') : null);
      ebookAutoGrow(ta);
    });
    ctl.appendChild(undo);
    row.appendChild(ctl);

    wrap.appendChild(row);
    ebookAutoGrow(ta);
  });

  ebookFlagIdx = -1;
  ebookUpdateFlagBar();
  ebookUpdatePager();
}

function ebookFlaggedRows() {
  return Array.from(document.querySelectorAll('#eb-ed-blocks .eb-blk[data-flag]'));
}

function ebookUpdateFlagBar() {
  const rows = ebookFlaggedRows();
  const bar = document.getElementById('eb-ed-flagbar');
  if (!rows.length) { bar.style.display = 'none'; return; }
  bar.style.display = 'flex';
  document.getElementById('eb-ed-flagcount').textContent =
    rows.length + ' spot' + (rows.length === 1 ? '' : 's');
}

function ebookJumpFlag(dir) {
  const rows = ebookFlaggedRows();
  if (!rows.length) return;
  ebookFlagIdx = (ebookFlagIdx + dir + rows.length) % rows.length;
  const row = rows[ebookFlagIdx];
  row.scrollIntoView({ block: 'center', behavior: 'smooth' });
  const ta = row.querySelector('.eb-blk-txt');
  if (!ta) return;
  ta.focus();
  // Select the flagged word itself, not just the paragraph: the author can
  // retype over it immediately, and the native selection makes it unmissable.
  const w = (row.dataset.words || '').split('\u001F')[0];
  const at = w ? ta.value.indexOf(w) : -1;
  if (at >= 0) ta.setSelectionRange(at, at + w.length);
  else ta.setSelectionRange(0, 0);
}

function ebookUpdatePager() {
  if (!ebookDoc) return;
  const pager = document.getElementById('eb-ed-pager');
  if (ebookDoc.total <= ebookDoc.limit) { pager.style.display = 'none'; return; }
  pager.style.display = 'flex';
  const from = ebookDoc.offset + 1;
  const to = Math.min(ebookDoc.offset + ebookDoc.limit, ebookDoc.total);
  document.getElementById('eb-ed-range').textContent =
    'Showing ' + from.toLocaleString() + '–' + to.toLocaleString() +
    ' of ' + ebookDoc.total.toLocaleString();
  document.getElementById('eb-ed-prev').disabled = (ebookDoc.offset <= 0);
  document.getElementById('eb-ed-next').disabled = (to >= ebookDoc.total);
}

async function ebookDocPage(dir) {
  if (!ebookDoc) return;
  // Never let paging silently discard edits.
  if (ebookDirtyCount() && !await ebookSaveDoc(true)) return;
  const next = Math.max(0, ebookDoc.offset + dir * ebookDoc.limit);
  if (next >= ebookDoc.total) return;
  ebookLoadDoc(next);
}

// Pull one illustration through the authenticated endpoint and show it.
async function ebookLoadImage(blockId, imgEl) {
  try {
    const res = await fetch(API + '/ebook_convert.php', {
      method: 'POST',
      headers: Object.assign({ 'Content-Type': 'application/json' },
        authToken ? { 'X-Auth-Token': authToken } : {}),
      body: JSON.stringify({
        action: 'doc_image', conversion_id: ebookCurrentConversion, block_id: blockId,
      }),
    });
    if (!res.ok) return;
    const blob = await res.blob();
    if (!blob || blob.type.indexOf('image') !== 0) return;
    imgEl.src = URL.createObjectURL(blob);
  } catch (e) { /* a missing picture must never break the editor */ }
}

async function ebookLoadDoc(offset) {
  if (!ebookCurrentConversion) return;
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({
        action: 'doc_get', conversion_id: ebookCurrentConversion,
        offset: offset || 0, limit: 300,
      }),
    });
    if (!data.success) { toast(data.message || 'Could not open your book', true); return; }
    ebookRenderEditor(data);
  } catch (e) { toast('Could not open your book — check your connection', true); }
}

async function ebookSaveDoc(quiet) {
  if (!ebookCurrentConversion) return false;
  const changes = Object.values(ebookDirty);
  if (!changes.length) return true;
  const btn = document.getElementById('eb-ed-save');
  btn.disabled = true; btn.textContent = 'Saving…';
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({
        action: 'doc_save', conversion_id: ebookCurrentConversion, blocks: changes,
      }),
    });
    if (!data.success) {
      toast(data.message || 'Could not save your changes', true);
      btn.disabled = false; btn.textContent = 'Save changes';
      return false;
    }
    ebookDirty = {};
    if (ebookDoc && data.stats) ebookDoc.stats = data.stats;
    // Saving is what commits an edit — only now does a touched block stop being
    // an unchecked spot, and only now does the count drop.
    document.querySelectorAll('#eb-ed-blocks .eb-blk.edited').forEach(r => {
      r.classList.remove('edited');
      const t = r.querySelector('.eb-blk-txt');
      r.dataset.orig = t ? t.value : r.dataset.orig;
      if (r.dataset.flag) {
        delete r.dataset.flag;
        delete r.dataset.words;
        r.classList.remove('flagged');
        ebookPaintBackdrop(r.querySelector('.eb-blk-back'), t ? t.value : '', null);
      }
    });
    ebookFlagIdx = -1;
    ebookUpdateFlagBar();
    btn.disabled = true; btn.textContent = 'Save changes';
    document.getElementById('eb-ed-note').textContent =
      'Saved. ' + (data.changed || 0) + ' change' + ((data.changed === 1) ? '' : 's') + ' kept.';
    if (!quiet) toast('Changes saved');
    return true;
  } catch (e) {
    toast('Could not save — check your connection', true);
    btn.disabled = false; btn.textContent = 'Save changes';
    return false;
  }
}

async function ebookBuildDoc() {
  if (!ebookCurrentConversion) return;
  if (ebookDirtyCount() && !await ebookSaveDoc(true)) return;
  const btn = document.getElementById('eb-ed-build');
  btn.disabled = true; btn.textContent = 'Preparing…';
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'doc_build', conversion_id: ebookCurrentConversion }),
    });
    if (!data.success) {
      toast(data.message || 'Could not prepare your book', true);
      btn.disabled = false; btn.textContent = 'Build my EPUB';
      return;
    }
    if (data.still_flagged > 0) {
      document.getElementById('eb-ed-note').textContent =
        data.still_flagged + ' highlighted spot' + (data.still_flagged === 1 ? ' was' : 's were') +
        ' left unchanged — that’s fine if you checked them.';
    }
    btn.textContent = 'Build my EPUB';
    btn.disabled = false;
    ebookConvert();          // hands off to the existing pipeline
  } catch (e) {
    toast('Could not prepare your book — check your connection', true);
    btn.disabled = false; btn.textContent = 'Build my EPUB';
  }
}

function initEbookConvertView() {
  ebookCurrentConversion = 0;
  if (ebookPollTimer) { clearTimeout(ebookPollTimer); ebookPollTimer = null; }
  ebookSetKind('reflow');
  const sel = document.getElementById('eb-book-id');
  if (sel) {
    const current = sel.value;
    sel.innerHTML = '<option value="0">— not linked to a book (optional) —</option>' +
      (booksList || []).map(b => '<option value="' + b.id + '">' + escapeHtml(b.title) + '</option>').join('');
    if (current) sel.value = current;
  }
  const fileInput = document.getElementById('eb-file');
  if (fileInput) fileInput.value = '';
  const coverInput = document.getElementById('eb-cover');
  if (coverInput) coverInput.value = '';
  const nameEl = document.getElementById('eb-file-name');
  if (nameEl) nameEl.textContent = '';
  const pf = document.getElementById('eb-preflight-card'); if (pf) pf.style.display = 'none';
  const rc = document.getElementById('eb-result-card');    if (rc) rc.style.display = 'none';
  const ub = document.getElementById('eb-upload-btn');      if (ub) ub.disabled = true;
  ebookLoadRecent();
}

function ebookFilePicked() {
  const f = document.getElementById('eb-file').files[0];
  const nameEl = document.getElementById('eb-file-name');
  const btn = document.getElementById('eb-upload-btn');
  if (!f) { nameEl.textContent = ''; btn.disabled = true; return; }
  nameEl.textContent = f.name + ' · ' + Math.max(1, Math.round(f.size / 1024)) + ' KB';
  btn.disabled = false;
}

async function ebookUpload() {
  // Demo mode: ebook_convert.php refuses the demo account server-side, and the
  // upload posts via raw fetch() rather than api(), so _demoIntercept never
  // sees it. Without this the UI looks fully functional and then fails with a
  // bare error string. Explain it up front instead, like the other demo tools.
  if (currentUser && (currentUser.is_demo == 1 || currentUser.is_demo === true)) {
    showDemoModal({
      type: 'ai',
      eyebrow: 'Demo preview',
      title: 'eBook Maker',
      body: 'In your real account, this turns a finished manuscript into a proper EPUB.\n\n'
          + 'Upload a Word or RTF file and it formats the whole book — chapter breaks, a linked table of contents, clean typography — then converts it to EPUB ready for Kindle, Apple Books, and Kobo.\n\n'
          + 'Picture books take a different path: upload your print-ready PDF and it builds a fixed-layout EPUB that keeps every page exactly as designed.\n\n'
          + 'The demo skips the conversion, which needs a real manuscript file to work on.',
      footNote: 'Sign up to convert your own book.',
    });
    return;
  }

  const fxl = (ebookKind === 'fxl');
  const f = document.getElementById('eb-file').files[0];
  if (!f) { toast(fxl ? 'Choose your print-ready PDF first' : 'Choose a manuscript file first', true); return; }
  const bookId = parseInt(document.getElementById('eb-book-id').value, 10) || 0;
  const btn = document.getElementById('eb-upload-btn');
  btn.disabled = true;
  btn.textContent = fxl ? 'Reading your pages…'
                        : (ebookKind === 'pdftext' ? 'Processing your file…'
                        : (ebookKind === 'ocr' ? 'Reading your pages…'
                                               : 'Checking your manuscript…'));
  const pf = document.getElementById('eb-preflight-card'); pf.style.display = 'none';
  const rc = document.getElementById('eb-result-card');    rc.style.display = 'none';
  document.getElementById('eb-ocr-offer-card').style.display = 'none';

  if (ebookKind === 'ocr') { ebookStartProgress(); }

  const fd = new FormData();
  fd.append('action', 'upload');
  fd.append('kind', ebookKind);
  if (ebookProgToken) fd.append('progress_token', ebookProgToken);
  fd.append('manuscript', f);
  fd.append('book_id', bookId);
  // Optional. Sent as typed — the server validates the check digit and converts
  // an ISBN-10, so there is one implementation of "is this a real ISBN".
  const isbnEl = document.getElementById('eb-isbn');
  if (isbnEl && isbnEl.value.trim()) fd.append('ebook_isbn', isbnEl.value.trim());
  // What the author types always beats what we can work out from the page.
  const titleEl  = document.getElementById('eb-title');
  const authorEl = document.getElementById('eb-author');
  if (titleEl  && titleEl.value.trim())  fd.append('title',  titleEl.value.trim());
  if (authorEl && authorEl.value.trim()) fd.append('author', authorEl.value.trim());
  const coverInput = document.getElementById('eb-cover');
  if (coverInput && coverInput.files[0]) fd.append('cover', coverInput.files[0]);
  try {
    // Multipart: don't set Content-Type — the browser adds the boundary. So we
    // hit fetch directly rather than the JSON api() helper.
    const res = await fetch(API + '/ebook_convert.php', {
      method: 'POST',
      headers: authToken ? { 'X-Auth-Token': authToken } : {},
      body: fd,
    });
    const data = await res.json();
    if (data.success) {
      ebookCurrentConversion = data.conversion_id;
      if (ebookKind === 'pdftext' || ebookKind === 'ocr') {
        // The recovered text IS the pre-flight here — there is nothing useful
        // to report about a book the author is about to read in full.
        ebookDocSource = data.source || '';
        if (data.note) toast(data.note);
        if (data.cover_note) toast(data.cover_note);
        ebookLoadDoc(0);
      } else {
        ebookRenderPreflight(data);
      }
      // A cover that could not be used used to vanish without a word.
      if (data.cover_note) toast(data.cover_note);
      if (data.quota) updateQuotaMeter(data.quota);
      ebookLoadRecent();
    } else {
      // Not a failure: the file is a scan, which is a thing we can read. Say
      // what it is and offer the reader that suits it. The upload is kept
      // server-side, so accepting costs the author nothing but a click.
      if (data.error_code === 'needs_ocr') {
        ebookCurrentConversion = data.conversion_id || 0;
        ebookOcrPages = data.pages || 0;
        // Set a real expectation about the wait: reading a scan is per-page
        // work, so a long book is minutes, not seconds.
        let msg = data.message || '';
        if (ebookOcrPages > 0) {
          // ~0.5s a page, measured on a real 128-page book (65s end to end).
          const secs = Math.max(10, Math.round(ebookOcrPages * 0.55));
          msg += ' Your book is ' + ebookOcrPages.toLocaleString() + ' pages, so reading it takes '
               + (secs < 90 ? 'about ' + secs + ' seconds'
                            : 'roughly ' + Math.round(secs / 60) + ' minutes') + '.';
        }
        // Say the price CHANGE here, at the moment it changes — not at checkout.
        msg += ' Reading a scan is $14.95 rather than $9.95, and as always you see the '
             + 'whole book before you decide.';
        document.getElementById('eb-ocr-offer-msg').textContent = msg;
        const oc = document.getElementById('eb-ocr-offer-card');
        oc.style.display = 'block';
        document.getElementById('main').scrollTop = oc.offsetTop - 20;
      } else if (data.error_code === 'bad_isbn') {
        const el = document.getElementById('eb-isbn');
        if (el) { el.focus(); el.select(); el.scrollIntoView({ block: 'center' }); }
        toast(data.message || 'That ISBN does not look right', true);
      } else if (data.error_code === 'no_text_layer') {
        toast(data.message || 'That PDF has no text in it — the pages are images.', true);
      } else {
        toast(data.message || 'Upload failed — please try again', true);
      }
    }
  } catch (e) { toast('Upload failed — check your connection', true); }
  ebookStopProgress();
  btn.disabled = false;
  btn.textContent = fxl ? 'Check my picture book'
                        : (ebookKind === 'pdftext' ? 'Process my PDF'
                        : (ebookKind === 'ocr' ? 'Read my scanned book' : 'Check my manuscript'));
}

// The author accepted the offer to read their scan. The file is already on the
// server from the upload that detected it, so this only starts the reading.
async function ebookRunOcr() {
  const btn = document.getElementById('eb-ocr-run-btn');
  btn.disabled = true;
  btn.textContent = 'Reading your pages…';
  ebookStartProgress();
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({
        action: 'ocr_run', conversion_id: ebookCurrentConversion,
        progress_token: ebookProgToken,
      }),
    });
    if (data.success) {
      document.getElementById('eb-ocr-offer-card').style.display = 'none';
      ebookDocSource = data.source || 'ocr';
      ebookLoadDoc(0);
      ebookLoadRecent();
    } else {
      toast(data.message || 'That scan could not be read — please try again', true);
    }
  } catch (e) { toast('Reading failed — check your connection', true); }
  ebookStopProgress();
  btn.disabled = false;
  btn.textContent = 'Read my scanned book';
}

function ebookRenderPreflight(data) {
  const card = document.getElementById('eb-preflight-card');
  const d = data.digest || {};
  const chip = (label, val) => '<span class="eb-chip"><strong>' + val + '</strong> ' + label + '</span>';
  let chips = '';
  // A picture book has no manuscript structure to report on — what the author
  // wants confirmed is that we read the right number of pages, at the right size.
  if (data.kind === 'fxl') {
    const pi = data.page_info || {};
    chips = chip('pages', pi.pages || 0) +
            chip('', escapeHtml((pi.w_in || 0) + ' × ' + (pi.h_in || 0) + ' in')) +
            (pi.shape ? '<span class="eb-chip">' + escapeHtml(pi.shape) + '</span>' : '');
  } else if (d.analyzed === false) {
    // For formats we don't inspect at upload (.doc/.odt/.rtf), the numeric chips
    // would all read zero and look "empty" — skip them; the report explains it.
    chips = '<span class="eb-chip">structure analyzed &amp; cleaned during conversion</span>';
  } else {
    if (d.word_count != null)         chips += chip('words', Number(d.word_count).toLocaleString());
    if (d.heading_styles != null)     chips += chip('heading styles', d.heading_styles);
    if (d.image_count != null)        chips += chip('images', d.image_count);
    if (d.manual_page_breaks != null) chips += chip('manual page breaks', d.manual_page_breaks);
    if (d.front_matter && d.front_matter.length)
      chips += '<span class="eb-chip">front matter: ' + escapeHtml(d.front_matter.join(', ')) + '</span>';
  }
  document.getElementById('eb-digest-chips').innerHTML = chips;

  const reportEl = document.getElementById('eb-preflight-report');
  const unavail  = document.getElementById('eb-preflight-unavailable');
  if (data.preflight_available && data.preflight_report) {
    reportEl.textContent = data.preflight_report;
    reportEl.style.display = 'block';
    unavail.style.display = 'none';
  } else {
    reportEl.style.display = 'none';
    unavail.style.display = 'block';
  }

  const convBtn  = document.getElementById('eb-convert-btn');
  const convNote = document.getElementById('eb-convert-note');
  if (data.convert_ready === false) {
    convBtn.disabled = true;
    convNote.textContent = 'The conversion engine isn’t connected yet — the pre-flight above still applies.';
  } else {
    convBtn.disabled = false;
    convBtn.textContent = 'Convert to EPUB';
    convNote.textContent = '';
  }
  card.style.display = 'block';
  document.getElementById('main').scrollTop = card.offsetTop - 20;
}

async function ebookConvert() {
  if (!ebookCurrentConversion) return;
  const btn = document.getElementById('eb-convert-btn');
  btn.disabled = true; btn.textContent = 'Converting…';
  ebookShowConverting();
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'convert', conversion_id: ebookCurrentConversion }),
    });
    if (data.success) {
      ebookPoll();
    } else {
      document.getElementById('eb-result-card').style.display = 'none';
      toast(data.message || 'Could not start conversion', true);
      btn.disabled = false; btn.textContent = 'Convert to EPUB';
    }
  } catch (e) {
    document.getElementById('eb-result-card').style.display = 'none';
    toast('Request failed — please try again', true);
    btn.disabled = false; btn.textContent = 'Convert to EPUB';
  }
}

async function ebookPoll() {
  if (!ebookCurrentConversion) return;
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'status', conversion_id: ebookCurrentConversion }),
    });
    const btn = document.getElementById('eb-convert-btn');
    if (data.success && data.status === 'done') {
      ebookRenderResult(data);
      ebookLoadRecent();
      btn.disabled = false; btn.textContent = 'Convert again';
      return;
    }
    if (data.success && data.status === 'error') {
      ebookShowError(data.error_message);
      btn.disabled = false; btn.textContent = 'Try again';
      return;
    }
  } catch (e) { /* transient — keep polling */ }
  ebookPollTimer = setTimeout(ebookPoll, 3000);
}

function ebookShowError(msg) {
  document.getElementById('eb-converting').style.display = 'none';
  document.getElementById('eb-downloads').style.display = 'none';
  const note = document.getElementById('eb-format-note'); if (note) note.style.display = 'none';
  const partial = document.getElementById('eb-partial'); if (partial) partial.style.display = 'none';
  const err = document.getElementById('eb-error');
  err.textContent = msg || (ebookKind === 'fxl'
    ? 'We couldn’t convert this PDF. Make sure it’s the print-ready interior PDF, not a scan or a protected file.'
    : 'We couldn’t convert this file. Try re-saving it as a fresh .docx and uploading again.');
  err.style.display = 'block';
  const card = document.getElementById('eb-result-card');
  card.style.display = 'block';
  document.getElementById('main').scrollTop = card.offsetTop - 20;
}

function ebookShowConverting() {
  const el = document.getElementById('eb-converting');
  el.textContent = (ebookKind === 'fxl')
    ? 'Rendering every page of your book and locking the layout… this takes a few seconds.'
    : 'Formatting and converting your manuscript… this usually takes under a minute.';
  document.getElementById('eb-downloads').style.display = 'none';
  el.style.display = 'block';
  document.getElementById('eb-result-card').style.display = 'block';
}

function ebookRenderResult(data) {
  // Trust message: what the formatting stage structured before converting.
  const note = document.getElementById('eb-format-note');
  const fsx  = data.format_summary || null;

  // Picture book: the promise is fidelity, not restructuring. Say exactly that.
  if (note && fsx && fsx.fxl) {
    const pages = fsx.pages || 0;
    let msg = '✓ We locked <strong>' + pages + '</strong> page' + (pages === 1 ? '' : 's') +
      ' at <strong>' + escapeHtml(fsx.trim || '') + '</strong> — every page exactly as you designed it, artwork and words together.';
    if (fsx.cover_from === 'upload') {
      msg += ' Your uploaded cover is in front.';
    } else {
      msg += ' Page 1 is your cover.';
    }
    note.innerHTML = msg;
    note.style.display = 'block';
    ebookFinishResult(data);
    return;
  }

  if (note) {
    const fs = data.format_summary || null;
    const c  = (fs && fs.cleaned) || {};
    const bits = [];
    const N = (x) => Number(x).toLocaleString();
    if (fs && fs.chapters > 0)
      bits.push('built a table of contents from <strong>' + fs.chapters + '</strong> chapter' + (fs.chapters === 1 ? '' : 's'));
    if (c.font_overrides > 0)
      bits.push('cleared <strong>' + N(c.font_overrides) + '</strong> font overrides that block text resizing');
    if (c.quotes > 0)
      bits.push('curled <strong>' + N(c.quotes) + '</strong> straight quotes');
    if (c.tabs > 0)
      bits.push('removed <strong>' + N(c.tabs) + '</strong> fake-indent tabs');
    if (c.page_breaks > 0)
      bits.push('removed <strong>' + N(c.page_breaks) + '</strong> stray page breaks');
    if (c.blank_lines > 0)
      bits.push('tidied <strong>' + N(c.blank_lines) + '</strong> blank-line gaps');
    const fm = (fs && fs.front_matter) || {};
    if (fm.title_page || fm.copyright) {
      const fp = [];
      if (fm.title_page) fp.push('title');
      if (fm.copyright)  fp.push('copyright');
      bits.push('added a <strong>' + fp.join(' and ') + '</strong> page');
    }
    if (bits.length) {
      note.innerHTML = '✓ We cleaned and structured your manuscript — ' + bits.join(', ') +
        '. It will read cleanly and resize on any device.';
      note.style.display = 'block';
    } else {
      note.style.display = 'none';
    }
  }
  ebookFinishResult(data);
}

// ── Watching a scan being read ──
// The reading happens inside one long upload request the browser cannot see
// into, so the run leaves a breadcrumb on the server and we poll it. That gives
// a REAL bar driven by pages actually read, not a spinner pretending to know.
function ebookStartProgress() {
  const box  = document.getElementById('eb-progress');
  const card = document.getElementById('eb-progress-card');
  if (!box || !card) return;
  card.style.display = 'block';
  ebookProgToken = (Math.random().toString(16).slice(2) + Math.random().toString(16).slice(2)).slice(0, 24);
  box.style.display = 'block';
  document.getElementById('eb-progress-fill').style.width = '0%';
  document.getElementById('eb-progress-text').textContent = 'Preparing your pages…';
  const started = Date.now();

  const tick = async () => {
    try {
      const d = await api('/ebook_convert.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'ocr_progress', progress_token: ebookProgToken }),
      });
      const secs = Math.round((Date.now() - started) / 1000);
      if (d.success && d.known && d.total > 0) {
        const pct = Math.min(99, Math.round((d.done / d.total) * 100));
        document.getElementById('eb-progress-fill').style.width = pct + '%';
        document.getElementById('eb-progress-text').textContent =
          d.stage === 'building'
            ? 'Putting your book together…'
            : 'Reading page ' + Math.min(d.done + 1, d.total) + ' of ' + d.total + ' — ' + pct + '%';
      } else {
        document.getElementById('eb-progress-text').textContent =
          'Preparing your pages… (' + secs + 's)';
      }
    } catch (e) { /* a failed poll must never disturb the upload */ }
  };
  tick();
  ebookProgTimer = setInterval(tick, 2000);
}

function ebookStopProgress() {
  if (ebookProgTimer) { clearInterval(ebookProgTimer); ebookProgTimer = null; }
  const card = document.getElementById('eb-progress-card');
  if (card) card.style.display = 'none';
  ebookProgToken = '';
}

// ── Standalone mode ──
// A visitor arrives with no account and should not be asked for one. We mint a
// passwordless guest session on their behalf, hide everything that belongs to
// the wider app, and leave them alone with the tool.
// ⚠ A GUEST SESSION IS KEPT UNDER ITS OWN KEY.
// It used to share 'auth_token' with the app, which meant buying an eBook
// signed you into Elite Publishing — a stranger with no account landed on
// the dashboard of a product they had not bought. The standalone page keeps its
// own key, so a guest identity cannot leak into the app at all.
const EBM_TOKEN_KEY = 'ebm_token';

async function ebookStandaloneBoot() {
  document.body.classList.add('ah-standalone');
  try {
    // A genuine logged-in author visiting this page stays themselves.
    if (!authToken) { authToken = localStorage.getItem(EBM_TOKEN_KEY) || ''; }

    if (!authToken) {
      const res = await fetch(API + '/ebook_public.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'guest_start' }),
      });
      const d = await res.json();
      if (!d.success || !d.token) {
        toast(d.message || 'Could not start — please refresh and try again', true);
        return;
      }
      authToken = d.token;
      localStorage.setItem(EBM_TOKEN_KEY, authToken);   // never 'auth_token'
    }
    await loadUser();

    // Move a guest token that predates the split off the shared key, so the
    // buyer keeps their book here AND stops being signed into the app.
    if (currentUser && currentUser.is_guest
        && localStorage.getItem('auth_token') === authToken) {
      localStorage.setItem(EBM_TOKEN_KEY, authToken);
      localStorage.removeItem('auth_token');
    }
  } catch (e) {
    toast('Could not start — please check your connection', true);
    return;
  }
  showApp();
  // ⚠ navigate(), not showView() — there is no showView(). The first version
  // called one and threw inside an async function, which surfaces as an
  // unhandled rejection: no console error, no visible failure, and the page
  // simply sat on the dashboard. Views are switched by an "active" class here,
  // not by inline display.
  navigate('ebook-convert');
  // Nothing else in the app belongs on this page.
  document.querySelectorAll('#demo-banner, #topbar-notif').forEach(el => {
    if (el) el.style.display = 'none';
  });
  // A guest has no books to link and no history to list.
  const recent = document.getElementById('eb-recent-card');
  if (recent) recent.style.display = 'none';
  // Nothing to sign out of, no notifications, no menu to open.
  document.querySelectorAll('#topbar-signout, #notif-bell, #hamburger, #menu-btn')
    .forEach(el => { if (el) el.style.display = 'none'; });
  document.querySelectorAll('#topbar button, #topbar a').forEach(el => {
    const t = (el.textContent || '').trim().toLowerCase();
    if (t === 'sign out' || t === 'sign in') el.style.display = 'none';
  });
  // The plan banner is shown by script as well as styled by CSS, so it has to
  // be told to stay down.
  const tb = document.getElementById('trial-banner');
  if (tb) { tb.classList.remove('visible'); tb.style.display = 'none'; }

  // eBook checkout removed — nothing to restore from a payment redirect.
}

// The copy guard that blocked selecting and copying the review text lived
// entirely to protect an unpaid preview. With no paywall and no download
// gate there is nothing left to protect, so the author's own words are
// freely selectable throughout.

// ── READ THE FINISHED EPUB, WITHOUT BEING ABLE TO DOWNLOAD IT ──
// The commercial model rests on this: the author sees the ACTUAL file — real
// pages, real styling, real cover — and only then decides to pay. Nobody buys
// blind, so nobody asks for a refund because it was not what they pictured.
//
// It is assembled here rather than handed over as a file: each part is fetched
// through the authenticated endpoint, the book's own CSS is inlined, images
// become blob URLs, and the result is rendered inside a sandboxed iframe so the
// book's stylesheet cannot leak into the app. At no point does an .epub exist
// in the browser.
let ebookPreviewOpen = false;

function ebookAssetUrlDir(entry) {
  const i = entry.lastIndexOf('/');
  return i === -1 ? '' : entry.slice(0, i);
}
// Resolve "../images/x.jpg" against the part that referenced it.
function ebookResolveEntry(dir, href) {
  const clean = String(href).split('#')[0].split('?')[0];
  if (!clean) return '';
  const parts = (dir ? dir.split('/') : []).concat(clean.split('/'));
  const out = [];
  parts.forEach(seg => {
    if (!seg || seg === '.') return;
    if (seg === '..') { out.pop(); return; }
    out.push(seg);
  });
  return out.join('/');
}

async function ebookFetchAsset(entry, asText) {
  const res = await fetch(API + '/ebook_convert.php', {
    method: 'POST',
    headers: Object.assign({ 'Content-Type': 'application/json' },
      authToken ? { 'X-Auth-Token': authToken } : {}),
    body: JSON.stringify({
      action: 'epub_asset', conversion_id: ebookCurrentConversion, entry: entry,
    }),
  });
  if (!res.ok) return null;
  return asText ? res.text() : res.blob();
}

async function ebookOpenPreview() {
  const card = document.getElementById('eb-preview-card');
  const body = document.getElementById('eb-preview-body');
  const btn  = document.getElementById('eb-preview-btn');
  if (!card) return;
  btn.disabled = true; btn.textContent = 'Opening your book…';
  card.style.display = 'block';
  body.innerHTML = '<p style="color:var(--ink-soft)">Assembling your book…</p>';

  try {
    const info = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'epub_preview', conversion_id: ebookCurrentConversion }),
    });
    if (!info.success) { body.innerHTML = ''; toast(info.message || 'Could not open the preview', true); return; }

    let css = '';
    let html = '';
    const seenCss = new Set();

    for (const entry of info.pages) {
      const xhtml = await ebookFetchAsset(entry, true);
      if (!xhtml) continue;
      const dir = ebookAssetUrlDir(entry);
      const doc = new DOMParser().parseFromString(xhtml, 'application/xhtml+xml');
      const alt = doc.querySelector('parsererror')
        ? new DOMParser().parseFromString(xhtml, 'text/html') : doc;

      // the book's own stylesheets, once each
      for (const link of alt.querySelectorAll('link[rel~="stylesheet"], link[type="text/css"]')) {
        const target = ebookResolveEntry(dir, link.getAttribute('href') || '');
        if (!target || seenCss.has(target)) continue;
        seenCss.add(target);
        const sheet = await ebookFetchAsset(target, true);
        if (sheet) css += '\n' + sheet;
      }
      // images, as blobs — there is no URL a browser could follow on its own
      for (const img of alt.querySelectorAll('img, image')) {
        const attr = img.hasAttribute('src') ? 'src' : 'xlink:href';
        const target = ebookResolveEntry(dir, img.getAttribute(attr) || '');
        if (!target) continue;
        const blob = await ebookFetchAsset(target, false);
        if (blob) img.setAttribute(attr, URL.createObjectURL(blob));
      }
      const b = alt.querySelector('body');
      html += '<div class="ah-part">' + (b ? b.innerHTML : '') + '</div>';
    }

    // Sandboxed: the book's CSS styles the book and nothing else.
    const frame = document.createElement('iframe');
    frame.setAttribute('sandbox', 'allow-same-origin');
    frame.style.cssText = 'width:100%;height:70vh;border:1px solid rgba(0,0,0,.14);border-radius:10px;background:#fff';
    body.innerHTML = '';
    body.appendChild(frame);
    frame.srcdoc = '<!doctype html><meta charset="utf-8">'
      + '<style>' + css + '\nbody{margin:0;padding:22px 20px;}'
      + 'img{max-width:100%;height:auto}.ah-part{margin-bottom:26px}</style>'
      + '<body>' + html + '</body>';
    ebookPreviewOpen = true;
  } catch (e) {
    body.innerHTML = '';
    toast('Could not open the preview — please try again', true);
  }
  btn.disabled = false;
  btn.textContent = 'Read your eBook';
}

// ── "Not what you expected?" ──
// Captured beside the paywall, because that is where somebody decides not to
// buy — and a conversion that fails to sell is worth more to us as a sentence
// of explanation than as a silent abandonment.
function ebookFeedbackOpen() {
  document.getElementById('eb-feedback-form').style.display = 'block';
  document.getElementById('eb-feedback-open').style.display = 'none';
  document.getElementById('eb-feedback-text').focus();
}
function ebookFeedbackClose() {
  document.getElementById('eb-feedback-form').style.display = 'none';
  document.getElementById('eb-feedback-open').style.display = '';
}
async function ebookFeedbackSend() {
  const box = document.getElementById('eb-feedback-text');
  const msg = (box.value || '').trim();
  if (msg.length < 4) { toast('Tell us a little more so we can act on it', true); return; }
  const btn = document.getElementById('eb-feedback-send');
  btn.disabled = true; btn.textContent = 'Sending…';
  try {
    // The conversion id travels with it — without that we would know something
    // was wrong but not which book, and could never look at the file.
    const data = await api('/feedback.php', {
      method: 'POST',
      body: JSON.stringify({
        type: 'ebook',
        message: 'Conversion #' + (ebookCurrentConversion || '?') + ' — ' + msg,
        page: 'ebook-maker',
      }),
    });
    if (data.success) {
      document.getElementById('eb-feedback-done').style.display = 'block';
      box.value = '';
      btn.style.display = 'none';
    } else {
      toast(data.message || 'Could not send that — please try again', true);
    }
  } catch (e) { toast('Could not send that — check your connection', true); }
  btn.disabled = false; btn.textContent = 'Send';
}

// Fetch a finished file and save it. The download gate is gone: a finished
// book downloads for anyone who can see it, with no entitlement check, no
// price label and no lock state on the buttons.
async function ebookDownload(format) {
  if (!ebookCurrentConversion) return;
  try {
    const res = await fetch(API + '/ebook_convert.php', {
      method: 'POST',
      headers: Object.assign({ 'Content-Type': 'application/json' },
        authToken ? { 'X-Auth-Token': authToken } : {}),
      body: JSON.stringify({
        action: 'download', conversion_id: ebookCurrentConversion, format: format,
      }),
    });
    if (!res.ok) { toast('That file could not be downloaded — please try again', true); return; }
    const blob = await res.blob();
    const cd = res.headers.get('Content-Disposition') || '';
    const m = /filename="([^"]+)"/.exec(cd);
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = m ? m[1] : ('book.' + format);
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(a.href), 30000);
  } catch (e) { toast('Download failed — check your connection', true); }
}

// Shared tail for both pipelines: wire up the download buttons and reveal the card.
function ebookFinishResult(data) {
  const errEl = document.getElementById('eb-error'); if (errEl) errEl.style.display = 'none';
  const epub = document.getElementById('eb-dl-epub');
  const word = document.getElementById('eb-dl-word');
  const pdf  = document.getElementById('eb-dl-pdf');
  // These are BUTTONS, not links: the file is fetched through the API and
  // saved from a blob rather than pointed at by a URL.
  epub.style.display = data.epub_ready ? 'inline-flex' : 'none';
  word.style.display = data.docx_ready ? 'inline-flex' : 'none';
  pdf.style.display  = data.pdf_ready  ? 'inline-flex' : 'none';
  // EPUB is the deliverable; if a PDF happens to be present (legacy) show it too,
  // but we don't flag a "missing PDF" — the converter is EPUB-only by design.
  const partial = document.getElementById('eb-partial');
  if (partial) partial.style.display = 'none';

  // TOC editor — reflowable EPUBs only (a picture book's "TOC" is its pages). Reset
  // it fresh for this result; it lazy-loads when the author opens it.
  const tocBlock = document.getElementById('eb-toc-block');
  if (tocBlock) {
    const isFxl = !!(data.format_summary && data.format_summary.fxl);
    tocBlock.style.display = (data.epub_ready && !isFxl) ? 'block' : 'none';
    ebookTocLoaded = false;
    document.getElementById('eb-toc-editor').style.display = 'none';
    const tg = document.getElementById('eb-toc-toggle');
    if (tg) { tg.style.display = ''; tg.disabled = false; tg.textContent = '✏️ Edit table of contents'; }
    document.getElementById('eb-toc-status').textContent = '';
    document.getElementById('eb-toc-done').style.display = 'none';
  }

  document.getElementById('eb-converting').style.display = 'none';
  document.getElementById('eb-downloads').style.display = 'block';
  // Standalone only: now that they have a book, it is fair to mention the
  // bigger product. Pitching before it worked would be obnoxious.
  document.body.classList.add('ah-has-result');
  const pvCard = document.getElementById('eb-preview-card');
  const pvBody = document.getElementById('eb-preview-body');
  if (pvCard) pvCard.style.display = 'none';
  if (pvBody) pvBody.innerHTML = '';
  ebookPreviewOpen = false;
  const fbOpen = document.getElementById('eb-feedback-open');
  const fbForm = document.getElementById('eb-feedback-form');
  const fbDone = document.getElementById('eb-feedback-done');
  const fbSend = document.getElementById('eb-feedback-send');
  if (fbOpen) fbOpen.style.display = '';
  if (fbForm) fbForm.style.display = 'none';
  if (fbDone) fbDone.style.display = 'none';
  if (fbSend) fbSend.style.display = '';
  const card = document.getElementById('eb-result-card');
  card.style.display = 'block';
  document.getElementById('main').scrollTop = card.offsetTop - 20;
}

// ── Manual table-of-contents editor ───────────────────────────────────────────
let ebookTocLoaded = false;

async function ebookTocOpen() {
  const editor = document.getElementById('eb-toc-editor');
  const toggle = document.getElementById('eb-toc-toggle');
  if (ebookTocLoaded) { editor.style.display = editor.style.display === 'none' ? 'block' : 'none'; return; }
  toggle.disabled = true; toggle.textContent = 'Loading…';
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'toc_get', conversion_id: ebookCurrentConversion }),
    });
    if (data.success && data.entries && data.entries.length) {
      ebookTocRenderList(data.entries);
      editor.style.display = 'block';
      toggle.style.display = 'none';
      ebookTocLoaded = true;
    } else {
      toast('No editable table of contents was found for this book', true);
      toggle.disabled = false; toggle.textContent = '✏️ Edit table of contents';
    }
  } catch (e) {
    toast('Could not load the table of contents — please try again', true);
    toggle.disabled = false; toggle.textContent = '✏️ Edit table of contents';
  }
}

function ebookTocReload() {
  ebookTocLoaded = false;
  document.getElementById('eb-toc-editor').style.display = 'none';
  const tg = document.getElementById('eb-toc-toggle');
  tg.style.display = ''; tg.disabled = false; tg.textContent = '✏️ Edit table of contents';
  document.getElementById('eb-toc-status').textContent = '';
  document.getElementById('eb-toc-done').style.display = 'none';
  ebookTocOpen();
}

function ebookTocRenderList(entries) {
  const wrap = document.getElementById('eb-toc-list');
  wrap.innerHTML = '';
  entries.forEach(e => {
    const row = document.createElement('div');
    row.className = 'eb-toc-row';
    row.style.cssText = 'display:flex;align-items:center;gap:10px;margin-bottom:8px';
    const cb = document.createElement('input');
    cb.type = 'checkbox'; cb.checked = true; cb.className = 'eb-toc-keep';
    cb.style.cssText = 'width:auto;flex:0 0 auto;accent-color:var(--accent)';
    cb.dataset.src = e.src || '';
    const tx = document.createElement('input');
    tx.type = 'text'; tx.className = 'eb-toc-label'; tx.value = e.label || '';
    tx.style.cssText = 'flex:1;min-width:0;padding:7px 10px;border:1px solid var(--border);border-radius:6px;font-size:14px;font-family:var(--font-body)';
    // dim the label when its row is unchecked, so removals read at a glance
    cb.addEventListener('change', () => { tx.style.opacity = cb.checked ? '1' : '0.4'; });
    row.appendChild(cb); row.appendChild(tx);
    wrap.appendChild(row);
  });
}

function ebookTocCollect() {
  const out = [];
  document.querySelectorAll('#eb-toc-list .eb-toc-row').forEach(row => {
    const cb = row.querySelector('.eb-toc-keep');
    const tx = row.querySelector('.eb-toc-label');
    if (cb && cb.checked && tx && tx.value.trim()) {
      out.push({ src: cb.dataset.src, label: tx.value.trim() });
    }
  });
  return out;
}

async function ebookTocRebuild() {
  const entries = ebookTocCollect();
  if (!entries.length) { toast('Keep at least one table-of-contents entry', true); return; }
  const btn = document.getElementById('eb-toc-rebuild-btn');
  const status = document.getElementById('eb-toc-status');
  btn.disabled = true; btn.textContent = 'Saving…'; status.textContent = '';
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'toc_update', conversion_id: ebookCurrentConversion, entries }),
    });
    if (data.success) {
      // The file was rewritten in place — cache-bust the links so the author gets the
      // new version, not a stale cached copy. Surface a download button right here so
      // there's no scrolling back up (and no risk of hitting "Convert again").
      const bust = '';   // downloads are fetched, not linked — nothing to cache-bust
      const top = document.getElementById('eb-dl-epub');
      const dl = document.getElementById('eb-toc-dl');
      if (data.download_name) {
        if (top) top.download = data.download_name;
        if (dl)  dl.download  = data.download_name;
      }
      document.getElementById('eb-toc-done-msg').innerHTML =
        '<strong>✓ Table of contents updated</strong> (' + (data.count || entries.length) + ' entries). Download your new EPUB below — you don’t need to convert again.';
      document.getElementById('eb-toc-done').style.display = 'block';
      status.textContent = '';
    } else {
      toast(data.message || 'Could not update the table of contents', true);
    }
  } catch (e) {
    toast('Request failed — please try again', true);
  }
  btn.disabled = false; btn.textContent = 'Save & update table of contents';
}

async function ebookLoadRecent() {
  const wrap = document.getElementById('eb-recent-list');
  if (!wrap) return;
  try {
    const data = await api('/ebook_convert.php', {
      method: 'POST', body: JSON.stringify({ action: 'list' }),
    });
    if (!data.success || !data.conversions || !data.conversions.length) {
      wrap.innerHTML = '<p style="color:var(--ink-soft);margin:0;font-size:14px">No conversions yet.</p>';
      return;
    }
    wrap.innerHTML = data.conversions.map(c => {
      let right;
      if (c.status === 'done') {
        const dn = escapeHtml(c.download_name || '');
        right = (c.epub_url ? '<a class="app-btn app-btn-outline app-btn-sm" href="' + c.epub_url + '" download="' + dn + '">EPUB</a> ' : '') +
                (c.pdf_url  ? '<a class="app-btn app-btn-outline app-btn-sm" href="' + c.pdf_url  + '" download="' + dn.replace(/\.epub$/i, '.pdf') + '">PDF</a>'  : '');
      } else if (c.status === 'error') {
        right = '<span style="color:var(--danger);font-size:12.5px">failed</span>';
      } else if (c.status === 'needs_ocr') {
        // Uploaded, detected as a scan, waiting on the author to say go — not
        // a stage in progress, so it must not read like one.
        right = '<span style="color:var(--ink-soft);font-size:12.5px">scan — not started</span>';
      } else {
        right = '<span style="color:var(--ink-soft);font-size:12.5px">' + escapeHtml(c.status) + '…</span>';
      }
      const meta = (c.kind === 'fxl')
        ? ((c.page_count ? c.page_count + ' pages' : 'PDF') + ' · picture book')
        : (c.word_count ? Number(c.word_count).toLocaleString() + ' words' : (c.source_format || ''));
      return '<div class="row"><div class="row-left">' + escapeHtml(c.source_filename || 'manuscript') +
        ' <span style="color:var(--ink-soft);font-size:12px">· ' + escapeHtml(meta) + '</span></div>' +
        '<div style="display:flex;gap:6px;align-items:center">' + right + '</div></div>';
    }).join('');
  } catch (e) { /* leave as-is on transient error */ }
}

// ── KDP A+ CONTENT MODULES ────────────────────────────────────

function initKdpAplusView() {
  const sel = document.getElementById('aplus-book-id');
  if (!sel) return;
  const current = sel.value;
  sel.innerHTML = '<option value="0">— pick a book —</option>' +
    (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
  if (current) sel.value = current;
  renderBookBanner('aplus-book-id', 'aplus-book-status');
}

async function generateKdpAplus() {
  const bookId    = parseInt(document.getElementById('aplus-book-id').value, 10) || 0;
  const extraHint = document.getElementById('aplus-extra-hint').value.trim();
  if (!bookId) { toast('Please pick a book first', true); return; }

  const btn = document.getElementById('aplus-generate-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generating…';
  document.getElementById('aplus-output-card').style.display = 'none';

  try {
    const data = await api('/kdp_aplus.php', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId, extra_hint: extraHint || null }),
    });
    if (data.success) {
      document.getElementById('aplus-output').textContent = data.modules;
      document.getElementById('aplus-output-card').style.display = 'block';
      document.getElementById('main').scrollTop =
        document.getElementById('aplus-output-card').offsetTop - 20;
      if (data.quota) {
        updateQuotaMeter(data.quota);
        const pct = Math.min(100, Math.round((data.quota.used_tenths_cent / data.quota.cap_tenths_cent) * 100));
        document.getElementById('aplus-quota-note').textContent =
          'AI usage this period: ' + pct + '% of monthly allowance';
      }
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'Generation failed — please try again', true);
    }
  } catch(e) { toast('Request failed — check your connection and try again', true); }

  btn.disabled = false;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generate 3 modules';
}

function copyKdpAplus() {
  const text = document.getElementById('aplus-output').textContent;
  if (!text) return;
  navigator.clipboard.writeText(text).then(
    () => toast('Copied to clipboard'),
    () => toast('Copy failed — select and copy manually', true)
  );
}

// ── AUTHOR CENTRAL BIO ────────────────────────────────────────

function initAuthorBioView() {
  const sel = document.getElementById('abio-book-id');
  if (!sel) return;
  const current = sel.value;
  sel.innerHTML = '<option value="0">— pick a book —</option>' +
    (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
  if (current) sel.value = current;
  renderBookBanner('abio-book-id', 'abio-book-status');
}

async function generateAuthorBio() {
  const bookId      = parseInt(document.getElementById('abio-book-id').value, 10) || 0;
  const voice       = document.getElementById('abio-voice').value;
  const followLinks = document.getElementById('abio-follow-links').value.trim();
  const extraHint   = document.getElementById('abio-extra-hint').value.trim();
  if (!bookId) { toast('Pick a representative book — the AI uses it for genre and voice context', true); return; }

  const btn = document.getElementById('abio-generate-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generating…';
  document.getElementById('abio-output-card').style.display = 'none';

  try {
    const data = await api('/author_central_bio.php', {
      method: 'POST',
      body: JSON.stringify({
        book_id:      bookId,
        voice,
        follow_links: followLinks || null,
        extra_hint:   extraHint   || null,
      }),
    });
    if (data.success) {
      document.getElementById('abio-output').textContent = data.bio;
      document.getElementById('abio-output-card').style.display = 'block';
      document.getElementById('main').scrollTop =
        document.getElementById('abio-output-card').offsetTop - 20;
      if (data.quota) {
        updateQuotaMeter(data.quota);
        const pct = Math.min(100, Math.round((data.quota.used_tenths_cent / data.quota.cap_tenths_cent) * 100));
        document.getElementById('abio-quota-note').textContent =
          'AI usage this period: ' + pct + '% of monthly allowance';
      }
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached — upgrade your plan to generate more', true);
    } else {
      toast(data.message || 'Generation failed — please try again', true);
    }
  } catch(e) { toast('Request failed — check your connection and try again', true); }

  btn.disabled = false;
  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-right:5px"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg>Generate bio';
}

function copyAuthorBio() {
  const text = document.getElementById('abio-output').textContent;
  if (!text) return;
  navigator.clipboard.writeText(text).then(
    () => toast('Copied to clipboard'),
    () => toast('Copy failed — select and copy manually', true)
  );
}

// ── KDP PROMOS (Free Promo Days + Countdown Deals) ────────────

let _kpPromos = [];

function initKdpPromosView() {
  const sel = document.getElementById('kp-book-id');
  if (sel) {
    const current = sel.value;
    sel.innerHTML = '<option value="0">— pick a book —</option>' +
      (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
    if (current) sel.value = current;
  }
  kpHideForm();
  loadKdpPromos();
}

async function loadKdpPromos() {
  const upcomingEl = document.getElementById('kp-upcoming');
  const pastEl     = document.getElementById('kp-past');
  if (!upcomingEl || !pastEl) return;

  try {
    const data = await api('/kdp_promos.php?action=list');
    if (!data.success) {
      upcomingEl.innerHTML = '<div class="empty">Could not load promos</div>';
      pastEl.innerHTML = '';
      return;
    }
    _kpPromos = data.promos || [];
    renderKdpPromos();
  } catch (e) {
    upcomingEl.innerHTML = '<div class="empty">Network error — try again</div>';
    pastEl.innerHTML = '';
  }
}

function renderKdpPromos() {
  const todayStr = new Date().toISOString().slice(0, 10);
  const upcoming = _kpPromos.filter(p => p.end_date >= todayStr);
  const past     = _kpPromos.filter(p => p.end_date <  todayStr);

  // Server returns DESC by start_date — for upcoming we want ASC (soonest first).
  upcoming.sort((a, b) => a.start_date.localeCompare(b.start_date));

  document.getElementById('kp-upcoming').innerHTML = upcoming.length
    ? upcoming.map(renderKpRow).join('')
    : '<div class="empty">No upcoming promos. Click "Schedule a promo" to add one.</div>';

  document.getElementById('kp-past').innerHTML = past.length
    ? past.map(renderKpRow).join('')
    : '<div class="empty">No past promos yet.</div>';
}

function renderKpRow(p) {
  const typeLabel = p.promo_type === 'free' ? 'Free Promo Days' : 'Countdown Deal';
  const typeBadge = p.promo_type === 'free' ? 'badge-amber' : 'badge-green';
  const dateRange = p.start_date === p.end_date
    ? formatPromoDate(p.start_date)
    : formatPromoDate(p.start_date) + ' – ' + formatPromoDate(p.end_date);
  const days = Math.round((new Date(p.end_date) - new Date(p.start_date)) / 86400000) + 1;
  const title = p.book_title || '(book deleted)';
  const notes = p.notes
    ? '<div style="font-size:12px;color:var(--ink-soft);margin-top:4px">' + escHtml(p.notes) + '</div>'
    : '';
  return '<div class="row" style="cursor:pointer" onclick="kpEditPromo(' + p.id + ')">'
    +   '<div class="row-left">'
    +     '<div>'
    +       '<div><strong>' + escHtml(title) + '</strong> <span class="badge ' + typeBadge + '" style="margin-left:6px">' + typeLabel + '</span></div>'
    +       '<div class="row-meta">' + dateRange + ' · ' + days + ' day' + (days === 1 ? '' : 's') + '</div>'
    +       notes
    +     '</div>'
    +   '</div>'
    +   '<div style="display:flex;gap:6px">'
    +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="event.stopPropagation(); window.open(\'https://kdp.amazon.com/en_US/bookshelf\',\'_blank\')" title="Open KDP Bookshelf to run this promo">Open in KDP ↗</button>'
    +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="event.stopPropagation(); kpEditPromo(' + p.id + ')">Edit</button>'
    +   '</div>'
    + '</div>';
}

function formatPromoDate(s) {
  // s like "2026-06-15" — render as "Jun 15, 2026" without timezone surprises
  const [y, m, d] = s.split('-').map(Number);
  const dt = new Date(y, m - 1, d);
  return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function kpShowForm() {
  document.getElementById('kp-edit-id').value = '';
  document.getElementById('kp-form-title').textContent = 'Plan a promo';
  document.getElementById('kp-book-id').value    = '0';
  document.getElementById('kp-promo-type').value = 'free';
  document.getElementById('kp-start-date').value = '';
  document.getElementById('kp-end-date').value   = '';
  document.getElementById('kp-notes').value      = '';
  document.getElementById('kp-delete-btn').style.display = 'none';
  document.getElementById('kp-form-card').style.display = 'block';
  document.getElementById('kp-add-btn-row').style.display = 'none';
  kpUpdateTypeHint();
}

function kpHideForm() {
  document.getElementById('kp-form-card').style.display = 'none';
  document.getElementById('kp-add-btn-row').style.display = '';
}

function kpEditPromo(id) {
  const p = _kpPromos.find(x => x.id == id);
  if (!p) return;
  document.getElementById('kp-edit-id').value    = p.id;
  document.getElementById('kp-form-title').textContent = 'Edit promo';
  document.getElementById('kp-book-id').value    = p.book_id;
  document.getElementById('kp-promo-type').value = p.promo_type;
  document.getElementById('kp-start-date').value = p.start_date;
  document.getElementById('kp-end-date').value   = p.end_date;
  document.getElementById('kp-notes').value      = p.notes || '';
  document.getElementById('kp-delete-btn').style.display = 'inline-flex';
  const card = document.getElementById('kp-form-card');
  card.style.display = 'block';
  document.getElementById('kp-add-btn-row').style.display = 'none';
  kpUpdateTypeHint();
  card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  // Brief highlight so the user clearly sees the form is the new focus
  card.classList.remove('kp-flash');
  void card.offsetWidth; // force reflow so the animation can restart
  card.classList.add('kp-flash');
}

function kpUpdateTypeHint() {
  const type = document.getElementById('kp-promo-type').value;
  const el   = document.getElementById('kp-type-hint');
  if (!el) return;
  el.textContent = type === 'free'
    ? 'KDP Select Free Promos: max 5 days per 90-day enrollment cycle. Book is free during this window.'
    : 'KDP Select Countdown Deals: max 7 days, US and UK stores only. Discounted price with a visible countdown timer.';
}

async function savePromo() {
  const id        = document.getElementById('kp-edit-id').value;
  const bookId    = parseInt(document.getElementById('kp-book-id').value, 10) || 0;
  const promoType = document.getElementById('kp-promo-type').value;
  const startDate = document.getElementById('kp-start-date').value;
  const endDate   = document.getElementById('kp-end-date').value;
  const notes     = document.getElementById('kp-notes').value.trim();

  if (!bookId)            { toast('Pick a book', true); return; }
  if (!startDate)         { toast('Start date is required', true); return; }
  if (!endDate)           { toast('End date is required', true); return; }
  if (endDate < startDate){ toast('End date must be on or after start date', true); return; }

  const action = id ? 'update' : 'create';
  const payload = { book_id: bookId, promo_type: promoType, start_date: startDate, end_date: endDate, notes };
  if (id) payload.id = parseInt(id, 10);

  try {
    const data = await api('/kdp_promos.php?action=' + action, {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    if (data.success) {
      toast(id ? 'Promo updated' : 'Promo scheduled');
      kpHideForm();
      loadKdpPromos();
    } else {
      toast(data.message || 'Save failed', true);
    }
  } catch (e) {
    toast('Network error — try again', true);
  }
}

async function deletePromo() {
  const id = parseInt(document.getElementById('kp-edit-id').value, 10) || 0;
  if (!id) return;
  if (!confirm('Delete this promo? This cannot be undone.')) return;

  try {
    const data = await api('/kdp_promos.php?action=delete', {
      method: 'POST',
      body: JSON.stringify({ id }),
    });
    if (data.success) {
      toast('Promo deleted');
      kpHideForm();
      loadKdpPromos();
    } else {
      toast(data.message || 'Delete failed', true);
    }
  } catch (e) {
    toast('Network error — try again', true);
  }
}

// ── SALES RANK LOGGER ─────────────────────────────────────────

let _rlEntries = [];

function initRankLoggerView() {
  const sel = document.getElementById('rl-book-id');
  if (sel) {
    const current = sel.value;
    sel.innerHTML = '<option value="0">— pick a book —</option>' +
      (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
    if (current) sel.value = current;
  }
  rlHideForm();
  // If a book was previously selected, reload its data; otherwise show nothing
  if (sel && sel.value && sel.value !== '0') {
    loadRankEntries();
  } else {
    document.getElementById('rl-chart-card').style.display = 'none';
    document.getElementById('rl-entries-card').style.display = 'none';
    document.getElementById('rl-add-btn-row').style.display = 'none';
  }
}

async function loadRankEntries() {
  const bookId = parseInt(document.getElementById('rl-book-id').value, 10) || 0;
  if (!bookId) {
    document.getElementById('rl-chart-card').style.display = 'none';
    document.getElementById('rl-entries-card').style.display = 'none';
    document.getElementById('rl-add-btn-row').style.display = 'none';
    return;
  }

  document.getElementById('rl-add-btn-row').style.display = '';
  document.getElementById('rl-entries-card').style.display = 'block';
  document.getElementById('rl-entries').innerHTML = '<div class="empty">Loading…</div>';

  try {
    const data = await api('/kdp_sales_ranks.php?action=list&book_id=' + bookId);
    if (!data.success) {
      document.getElementById('rl-entries').innerHTML = '<div class="empty">Could not load entries</div>';
      _rlEntries = [];
      return;
    }
    _rlEntries = data.entries || [];
    renderRankEntries();
    renderRankChart();
  } catch (e) {
    document.getElementById('rl-entries').innerHTML = '<div class="empty">Network error — try again</div>';
    _rlEntries = [];
  }
}

function renderRankEntries() {
  const el = document.getElementById('rl-entries');
  if (!_rlEntries.length) {
    el.innerHTML = '<div class="empty">No entries yet. Click "Log a rank" to add your first one.</div>';
    return;
  }
  // Render newest first in the list (server returns oldest first for the chart)
  const sorted = _rlEntries.slice().sort((a, b) => b.observed_at.localeCompare(a.observed_at));
  el.innerHTML = sorted.map(e => {
    const notes = e.notes
      ? '<div style="font-size:12px;color:var(--ink-soft);margin-top:4px">' + escHtml(e.notes) + '</div>'
      : '';
    return '<div class="row" style="cursor:pointer" onclick="rlEditEntry(' + e.id + ')">'
      +   '<div class="row-left">'
      +     '<div>'
      +       '<div><strong>BSR ' + Number(e.rank_value).toLocaleString() + '</strong> <span class="row-meta" style="margin-left:8px">' + formatPromoDate(e.observed_at) + '</span></div>'
      +       notes
      +     '</div>'
      +   '</div>'
      +   '<button class="app-btn app-btn-outline app-btn-sm" onclick="event.stopPropagation(); rlEditEntry(' + e.id + ')">Edit</button>'
      + '</div>';
  }).join('');
}

function renderRankChart() {
  const container = document.getElementById('rl-chart-container');
  const card = document.getElementById('rl-chart-card');
  if (!_rlEntries.length) {
    card.style.display = 'none';
    return;
  }
  card.style.display = 'block';

  // Geometry
  const W = container.clientWidth || 600;
  const H = 240;
  const PAD_L = 56, PAD_R = 12, PAD_T = 14, PAD_B = 32;
  const plotW = W - PAD_L - PAD_R;
  const plotH = H - PAD_T - PAD_B;

  // Data range
  const ranks = _rlEntries.map(e => e.rank_value);
  const dates = _rlEntries.map(e => new Date(e.observed_at + 'T00:00:00').getTime());
  const rMin = Math.min.apply(null, ranks);
  const rMax = Math.max.apply(null, ranks);
  const tMin = Math.min.apply(null, dates);
  const tMax = Math.max.apply(null, dates);

  // Pad the rank range a bit so points don't sit on the edges
  const rRange = Math.max(1, rMax - rMin);
  const rLo    = Math.max(1, rMin - rRange * 0.1);
  const rHi    = rMax + rRange * 0.1;

  // Inverted Y: smaller rank = higher on chart
  function xFor(ts) {
    if (tMax === tMin) return PAD_L + plotW / 2;
    return PAD_L + ((ts - tMin) / (tMax - tMin)) * plotW;
  }
  function yFor(r) {
    if (rHi === rLo) return PAD_T + plotH / 2;
    return PAD_T + ((r - rLo) / (rHi - rLo)) * plotH;
  }

  // Build path
  const pts = _rlEntries.map((e, i) => {
    const x = xFor(new Date(e.observed_at + 'T00:00:00').getTime());
    const y = yFor(e.rank_value);
    return { x: x, y: y, e: e };
  });
  const pathD = pts.map((p, i) => (i === 0 ? 'M' : 'L') + p.x.toFixed(1) + ' ' + p.y.toFixed(1)).join(' ');

  // Y-axis labels: 3 ticks (best, middle, worst)
  const yTicks = [
    { v: Math.round(rLo), y: PAD_T },
    { v: Math.round((rLo + rHi) / 2), y: PAD_T + plotH / 2 },
    { v: Math.round(rHi), y: PAD_T + plotH },
  ];

  // X-axis labels: first and last
  const xLabels = [
    { v: formatPromoDate(_rlEntries[0].observed_at), x: PAD_L },
    { v: formatPromoDate(_rlEntries[_rlEntries.length - 1].observed_at), x: PAD_L + plotW },
  ];

  let svg = '<svg viewBox="0 0 ' + W + ' ' + H + '" width="100%" height="' + H + '" style="display:block;font-family:var(--font-body);font-size:11px">';
  // Plot area background
  svg += '<rect x="' + PAD_L + '" y="' + PAD_T + '" width="' + plotW + '" height="' + plotH + '" fill="#FAFAF7" stroke="var(--ink-faint)"/>';
  // Y gridlines + labels
  yTicks.forEach(t => {
    svg += '<line x1="' + PAD_L + '" y1="' + t.y + '" x2="' + (PAD_L + plotW) + '" y2="' + t.y + '" stroke="var(--ink-faint)" stroke-dasharray="2,3"/>';
    svg += '<text x="' + (PAD_L - 6) + '" y="' + (t.y + 3) + '" text-anchor="end" fill="var(--ink-soft)">' + t.v.toLocaleString() + '</text>';
  });
  // X labels
  xLabels.forEach((l, i) => {
    svg += '<text x="' + l.x + '" y="' + (H - 10) + '" text-anchor="' + (i === 0 ? 'start' : 'end') + '" fill="var(--ink-soft)">' + l.v + '</text>';
  });
  // Line path
  if (pts.length > 1) {
    svg += '<path d="' + pathD + '" fill="none" stroke="var(--accent)" stroke-width="2"/>';
  }
  // Points
  pts.forEach(p => {
    svg += '<circle cx="' + p.x.toFixed(1) + '" cy="' + p.y.toFixed(1) + '" r="3.5" fill="var(--accent)"><title>'
        + formatPromoDate(p.e.observed_at) + ' — BSR ' + Number(p.e.rank_value).toLocaleString()
        + (p.e.notes ? '\n' + p.e.notes : '')
        + '</title></circle>';
  });
  svg += '</svg>';
  container.innerHTML = svg;
}

function rlShowForm() {
  document.getElementById('rl-edit-id').value = '';
  document.getElementById('rl-form-title').textContent = 'Log a rank';
  const today = new Date().toISOString().slice(0, 10);
  document.getElementById('rl-observed-at').value = today;
  document.getElementById('rl-rank-value').value = '';
  document.getElementById('rl-notes').value = '';
  document.getElementById('rl-delete-btn').style.display = 'none';
  const card = document.getElementById('rl-form-card');
  card.style.display = 'block';
  document.getElementById('rl-add-btn-row').style.display = 'none';
  card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  card.classList.remove('kp-flash'); void card.offsetWidth; card.classList.add('kp-flash');
}

function rlHideForm() {
  document.getElementById('rl-form-card').style.display = 'none';
  // Only show the Add button if a book is selected
  const bookId = parseInt(document.getElementById('rl-book-id').value, 10) || 0;
  document.getElementById('rl-add-btn-row').style.display = bookId ? '' : 'none';
}

function rlEditEntry(id) {
  const e = _rlEntries.find(x => x.id == id);
  if (!e) return;
  document.getElementById('rl-edit-id').value    = e.id;
  document.getElementById('rl-form-title').textContent = 'Edit rank entry';
  document.getElementById('rl-observed-at').value = e.observed_at;
  document.getElementById('rl-rank-value').value  = e.rank_value;
  document.getElementById('rl-notes').value       = e.notes || '';
  document.getElementById('rl-delete-btn').style.display = 'inline-flex';
  const card = document.getElementById('rl-form-card');
  card.style.display = 'block';
  document.getElementById('rl-add-btn-row').style.display = 'none';
  card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  card.classList.remove('kp-flash'); void card.offsetWidth; card.classList.add('kp-flash');
}

async function saveRankEntry() {
  const id         = document.getElementById('rl-edit-id').value;
  const bookId     = parseInt(document.getElementById('rl-book-id').value, 10) || 0;
  const observedAt = document.getElementById('rl-observed-at').value;
  // Strip commas, spaces, anything non-digit — Amazon displays BSR as "1,717,761"
  // and users naturally paste it in that form.
  const rawRank    = (document.getElementById('rl-rank-value').value || '').replace(/[^\d]/g, '');
  const rankValue  = parseInt(rawRank, 10) || 0;
  const notes      = document.getElementById('rl-notes').value.trim();

  if (!bookId)      { toast('Pick a book first', true); return; }
  if (!observedAt)  { toast('Date is required', true); return; }
  if (rankValue <= 0){ toast('Enter a positive rank number', true); return; }

  const action = id ? 'update' : 'create';
  const payload = { book_id: bookId, observed_at: observedAt, rank_value: rankValue, notes };
  if (id) payload.id = parseInt(id, 10);

  try {
    const data = await api('/kdp_sales_ranks.php?action=' + action, {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    if (data.success) {
      toast(id ? 'Entry updated' : 'Rank logged');
      rlHideForm();
      loadRankEntries();
    } else {
      toast(data.message || 'Save failed', true);
    }
  } catch (e) {
    toast('Network error — try again', true);
  }
}

async function deleteRankEntry() {
  const id = parseInt(document.getElementById('rl-edit-id').value, 10) || 0;
  if (!id) return;
  if (!confirm('Delete this rank entry? This cannot be undone.')) return;

  try {
    const data = await api('/kdp_sales_ranks.php?action=delete', {
      method: 'POST',
      body: JSON.stringify({ id }),
    });
    if (data.success) {
      toast('Entry deleted');
      rlHideForm();
      loadRankEntries();
    } else {
      toast(data.message || 'Delete failed', true);
    }
  } catch (e) {
    toast('Network error — try again', true);
  }
}

async function aiDraftCompTitles() {
  const btn = event.target.closest('button');
  btn.textContent = 'Thinking…'; btn.disabled = true;

  const bookId = parseInt(document.getElementById('ss-book-id').value, 10) || 0;
  const seed   = document.getElementById('ss-comp-titles').value.trim();
  const prompt = seed
    ? 'Refine or expand this list of comparable titles for a book: ' + seed
        + '. Suggest 4-6 total, comma-separated, no explanation.'
    : 'Suggest 4-6 comparable titles for this book. '
        + 'Pick well-known titles with similar genre, tone, or readership. '
        + 'Output only the titles, comma-separated, no explanation.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: bookId, prompt, max_tokens: 150 }),
    });
    if (data.success && data.draft) {
      document.getElementById('ss-comp-titles').value = data.draft;
      if (data.quota) updateQuotaMeter(data.quota);
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached', true);
    } else {
      toast(data.message || 'Could not generate suggestions', true);
    }
  } catch(e) { toast('Request failed — try again', true); }

  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg> AI suggest';
  btn.disabled = false;
}

async function aiDraftSsAuthorBio() {
  const btn = event.target.closest('button');
  btn.textContent = 'Drafting…'; btn.disabled = true;

  const bookId   = parseInt(document.getElementById('ss-book-id').value, 10) || 0;
  const existing = document.getElementById('ss-author-bio').value.trim();

  // Use the selected book's author field first; fall back to the logged-in user's name
  const selectedBook = bookId && booksList ? booksList.find(b => b.id == bookId) : null;
  const name    = (selectedBook && selectedBook.author)
                    || (currentUser && (currentUser.pen_name || currentUser.full_name))
                    || '';
  const website = (currentUser && currentUser.website) || '';

  let prompt = existing
    ? 'Polish this author bio for use on a sell sheet (2-3 sentences, marketing-focused): ' + existing
    : 'Write a short marketing-focused author bio for a sell sheet (2-3 sentences).';
  if (name)    prompt += ' Author name: ' + name + '.';
  if (website) prompt += ' Website: ' + website + '.';
  prompt += ' Output only the bio.';

  try {
    const data = await api('/ai_draft.php', {
      method: 'POST',
      body: JSON.stringify({ task: 'custom', book_id: bookId, prompt, max_tokens: 200 }),
    });
    if (data.success && data.draft) {
      document.getElementById('ss-author-bio').value = data.draft;
      if (data.quota) updateQuotaMeter(data.quota);
    } else if (data.error_code === 'quota_exceeded') {
      toast('Monthly AI limit reached', true);
    } else {
      toast(data.message || 'Could not generate bio', true);
    }
  } catch(e) { toast('Request failed — try again', true); }

  btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 1l1.8 5.2H15l-4.4 3.2 1.7 5.2L8 11.4l-4.3 3.2 1.7-5.2L1 6.2h5.2z"/></svg> AI draft';
  btn.disabled = false;
}

// ── CAMPAIGN DETAIL VIEW (S5 Part B) ─────────────────────────
// Analytics: open/click/bounce rates, per-recipient table, event timeline

let detailState = {
  campaignId: null,
  pollTimer: null,   // auto-refresh while viewing a sending campaign
};

async function openCampaignDetail(campaignId) {
  detailState.campaignId = campaignId;

  // Switch views
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.getElementById('view-campaign-detail').classList.add('active');

  // Clear previous state
  document.getElementById('detail-campaign-name').textContent = '';
  document.getElementById('detail-campaign-subject').textContent = '';
  document.getElementById('detail-campaign-meta').textContent = '';
  document.getElementById('detail-status-badge').innerHTML = '';
  document.getElementById('detail-stats-grid').innerHTML = '';
  document.getElementById('detail-recipients-wrap').innerHTML = '<div class="empty">Loading…</div>';
  document.getElementById('detail-events-wrap').innerHTML = '<div class="empty">Loading…</div>';

  await loadCampaignDetail();

  // If the campaign is still sending, auto-refresh every 15 seconds
  startDetailPolling();
}

function closeCampaignDetail() {
  stopDetailPolling();
  detailState.campaignId = null;
  navigate('email');
  loadCampaigns();
}

function startDetailPolling() {
  if (detailState.pollTimer) return;
  detailState.pollTimer = setInterval(async () => {
    // Only keep polling if we're still on the detail view
    if (!document.getElementById('view-campaign-detail').classList.contains('active')) {
      stopDetailPolling();
      return;
    }
    await loadCampaignDetail();
  }, 15000);
}

function stopDetailPolling() {
  if (detailState.pollTimer) {
    clearInterval(detailState.pollTimer);
    detailState.pollTimer = null;
  }
}

async function loadCampaignDetail() {
  const id = detailState.campaignId;
  if (!id) return;

  const data = await api('/campaigns.php?action=stats&id=' + id);
  if (!data.success) {
    document.getElementById('detail-recipients-wrap').innerHTML =
      '<div class="empty">' + escapeHtml(data.message || 'Failed to load stats') + '</div>';
    return;
  }

  renderCampaignDetail(data);

  // If the campaign has finished sending, stop polling
  if (data.campaign && data.campaign.status !== 'sending') {
    stopDetailPolling();
  }
}

function renderCampaignDetail(data) {
  const c = data.campaign;
  const counts = data.counts;

  // Header
  document.getElementById('detail-campaign-name').textContent = c.name || '(untitled)';
  document.getElementById('detail-campaign-subject').textContent = c.subject || '(no subject)';

  const metaBits = [];
  if (c.sent_at) metaBits.push('Sent ' + formatDateTime(c.sent_at));
  else if (c.status === 'sending') metaBits.push('Sending…');
  if (counts.recipients > 0) metaBits.push(counts.recipients + ' recipient' + (counts.recipients === 1 ? '' : 's'));
  document.getElementById('detail-campaign-meta').textContent = metaBits.join(' · ');

  // Status badge
  const statusBadgeMap = {
    sending: 'badge-amber',
    sent:    'badge-green',
    failed:  'badge-red',
    draft:   'badge-gray',
  };
  const badgeClass = statusBadgeMap[c.status] || 'badge-gray';
  document.getElementById('detail-status-badge').innerHTML =
    '<span class="badge ' + badgeClass + '">' + escapeHtml(c.status) + '</span>';

  // Stats grid
  renderStatsGrid(counts);

  // Recipients list
  renderRecipientsTable(data.recipients || []);

  // Events timeline
  renderEventsTimeline(data.events || []);
}

function renderStatsGrid(counts) {
  const grid = document.getElementById('detail-stats-grid');
  const total = counts.recipients || 0;

  const pct = (n) => total > 0 ? ' <span class="stat-tile-pct">' + Math.round((n / total) * 100) + '%</span>' : '';

  const tiles = [
    { label: 'Recipients',   value: counts.recipients,   cls: '' },
    { label: 'Delivered',    value: counts.delivered,    cls: 'good', pct: pct(counts.delivered) },
    { label: 'Opened',       value: counts.opened,       cls: 'good', pct: pct(counts.opened) },
    { label: 'Clicked',      value: counts.clicked,      cls: 'good', pct: pct(counts.clicked) },
  ];
  if (counts.bounced > 0)      tiles.push({ label: 'Bounced',      value: counts.bounced,      cls: 'bad',  pct: pct(counts.bounced) });
  if (counts.complained > 0)   tiles.push({ label: 'Spam reports', value: counts.complained,   cls: 'bad',  pct: pct(counts.complained) });
  if (counts.unsubscribed > 0) tiles.push({ label: 'Unsubscribed', value: counts.unsubscribed, cls: 'warn', pct: pct(counts.unsubscribed) });

  let html = '';
  for (const t of tiles) {
    html += '<div class="stat-tile ' + t.cls + '">';
    html += '<div class="stat-tile-label">' + escapeHtml(t.label) + '</div>';
    html += '<div class="stat-tile-value">' + (t.value || 0) + (t.pct || '') + '</div>';
    html += '</div>';
  }
  grid.innerHTML = html;
}

function renderRecipientsTable(recipients) {
  const wrap = document.getElementById('detail-recipients-wrap');
  if (recipients.length === 0) {
    wrap.innerHTML = '<div class="empty">No recipients yet — if this campaign is still being queued, check back shortly.</div>';
    return;
  }

  const statusLabels = {
    queued:       { label: 'Queued',       badge: 'badge-gray'  },
    sending:      { label: 'Sending',      badge: 'badge-amber' },
    sent:         { label: 'Sent',         badge: 'badge-green' },
    delivered:    { label: 'Delivered',    badge: 'badge-green' },
    opened:       { label: 'Opened',       badge: 'badge-green' },
    clicked:      { label: 'Clicked',      badge: 'badge-green' },
    bounced:      { label: 'Bounced',      badge: 'badge-red'   },
    complained:   { label: 'Spam report',  badge: 'badge-red'   },
    unsubscribed: { label: 'Unsubscribed', badge: 'badge-amber' },
    failed:       { label: 'Failed',       badge: 'badge-red'   },
  };

  let html = '';
  for (const r of recipients) {
    const s = statusLabels[r.status] || { label: r.status, badge: 'badge-gray' };
    const name = [r.first_name, r.last_name].filter(Boolean).join(' ');
    let timeline = '';
    if (r.opened_at)    timeline = 'Opened ' + formatDateTime(r.opened_at);
    else if (r.delivered_at) timeline = 'Delivered ' + formatDateTime(r.delivered_at);
    else if (r.sent_at) timeline = 'Sent ' + formatDateTime(r.sent_at);
    if (r.clicked_at)   timeline += ' · Clicked ' + formatDateTime(r.clicked_at);

    html += '<div class="recipient-row">';
    html += '<span class="badge ' + s.badge + '">' + escapeHtml(s.label) + '</span>';
    html += '<div class="recipient-row-email">' + escapeHtml(r.email) + '</div>';
    if (name)     html += '<div class="recipient-row-name">' + escapeHtml(name) + '</div>';
    if (timeline) html += '<div style="font-size:11px;color:var(--ink-soft);font-family:monospace">' + timeline + '</div>';
    if (r.error_message) html += '<div style="font-size:11px;color:#B94141;flex-basis:100%">Error: ' + escapeHtml(r.error_message) + '</div>';
    html += '</div>';
  }
  wrap.innerHTML = html;
}

function renderEventsTimeline(events) {
  const wrap = document.getElementById('detail-events-wrap');
  if (events.length === 0) {
    wrap.innerHTML = '<div class="empty">No events recorded yet. Events appear here as recipients open and interact with your email.</div>';
    return;
  }

  const eventLabels = {
    delivered:       'delivered to',
    opened:          'opened by',
    clicked:         'clicked a link in',
    permanent_fail:  'permanently failed for',
    temporary_fail:  'temporarily failed for',
    complained:      'marked as spam by',
    unsubscribed:    'unsubscribed by',
    failed:          'failed for',
  };

  let html = '';
  for (const e of events) {
    const label = eventLabels[e.event_type] || e.event_type;
    const name = [e.first_name, e.last_name].filter(Boolean).join(' ');
    const who = name ? name + ' (' + e.email + ')' : e.email;
    html += '<div class="event-row">';
    html += '<div class="event-row-time">' + (e.occurred_at ? formatDateTime(e.occurred_at) : '') + '</div>';
    html += '<div><strong>' + escapeHtml(label) + '</strong></div>';
    html += '<div class="event-row-who">' + escapeHtml(who) + '</div>';
    html += '</div>';
  }
  wrap.innerHTML = html;
}

// ── CAMPAIGN SEND FLOW (B2) ──────────────────────────────────
// Recipients picker + test send + schedule + send now

let sendState = {
  campaignId: null,
  campaign:   null,    // the loaded campaign data
  lists:      [],      // user's contact lists
  senderInfo: null,    // from_email etc
  selectedListIds: [], // which lists are checked
  eligibleCount: 0,    // computed from selected lists minus unsubscribed
};

async function composerGoToSend() {
  // Validate minimum fields before leaving composer
  const subject = document.getElementById('composer-subject').value.trim();
  const body    = document.getElementById('composer-body').innerText.trim();
  if (!subject) {
    toast('Add a subject line before continuing', true);
    document.getElementById('composer-subject').focus();
    return;
  }
  if (!body) {
    toast('Write something in the message body before continuing', true);
    document.getElementById('composer-body').focus();
    return;
  }

  // Flush any pending save so the send view shows the latest content
  if (composerState.dirty) {
    await composerSaveNow();
  }

  await openSendView(composerState.id);
}

async function openSendView(campaignId) {
  sendState.campaignId = campaignId;
  sendState.selectedListIds = [];

  // Reset UI
  document.getElementById('send-error').textContent = '';
  document.getElementById('send-recipient-summary').style.display = 'none';
  const testEmailEl = document.getElementById('send-test-email');
  if (testEmailEl) testEmailEl.value = '';

  // Default to "Send now"
  const nowRadio = document.querySelector('input[name="send-when"][value="now"]');
  if (nowRadio) nowRadio.checked = true;
  document.getElementById('send-schedule-controls').style.display = 'none';
  document.getElementById('send-cta-btn').textContent = 'Send now';

  // Switch views
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.getElementById('view-send').classList.add('active');

  // Load the campaign + sender info + lists in parallel
  const [campaignRes, senderRes] = await Promise.all([
    api('/campaigns.php?action=get&id=' + campaignId),
    api('/sender.php?action=status'),
  ]);

  if (!campaignRes.success) {
    toast(campaignRes.message || 'Failed to load campaign', true);
    backToComposer();
    return;
  }
  sendState.campaign = campaignRes.campaign;

  if (!senderRes.success || senderRes.state !== 'verified') {
    toast('Sender is not verified — finish email setup first', true);
    navigate('email');
    return;
  }
  sendState.senderInfo = senderRes.sender;

  // Populate review card
  document.getElementById('send-campaign-name').textContent = sendState.campaign.name || '(untitled)';
  document.getElementById('send-from').textContent =
    (sendState.senderInfo.from_name || '') + ' <' + (sendState.senderInfo.from_email || '') + '>';
  document.getElementById('send-subject').textContent = sendState.campaign.subject || '(no subject)';
  const preheaderEl = document.getElementById('send-preheader');
  if (sendState.campaign.preheader) {
    preheaderEl.textContent = sendState.campaign.preheader;
    preheaderEl.style.color = 'var(--ink)';
  } else {
    preheaderEl.textContent = '— (none set)';
    preheaderEl.style.color = 'var(--ink-soft)';
  }

  // Prefill test email with the sender's own email
  if (testEmailEl && sendState.senderInfo.from_email) {
    testEmailEl.value = sendState.senderInfo.from_email;
  }

  // Load lists and pre-check any already configured on the campaign
  await loadSendLists(campaignRes.recipients || []);
}

async function loadSendLists(existingRecipients) {
  // Fetch user's lists via the contacts endpoint
  const data = await api('/contacts.php?action=lists');
  const wrap = document.getElementById('send-lists-wrap');

  if (!data.success) {
    wrap.innerHTML = '<div class="empty">Failed to load your lists</div>';
    return;
  }

  const lists = data.lists || [];
  sendState.lists = lists;

  if (lists.length === 0) {
    wrap.innerHTML = '<div class="empty">You don\'t have any contact lists yet. <button class="app-btn app-btn-outline app-btn-sm" onclick="navigate(\'contacts\')">Create a list</button></div>';
    updateSendCTAEnabled();
    return;
  }

  // Pre-check any lists already on the campaign
  const existingListIds = new Set();
  for (const r of existingRecipients) {
    if (r.list_id) existingListIds.add(parseInt(r.list_id, 10));
  }
  sendState.selectedListIds = Array.from(existingListIds);

  let html = '<div style="display:grid;gap:8px">';
  for (const l of lists) {
    const checked = existingListIds.has(parseInt(l.id, 10));
    html += '<label style="display:flex;gap:10px;align-items:center;padding:8px 10px;border:1px solid var(--ink-faint);border-radius:4px;cursor:pointer;background:' + (checked ? '#F0F7E8' : '#fff') + '">'
      + '<input type="checkbox" value="' + l.id + '" ' + (checked ? 'checked' : '') + ' onchange="toggleSendList(' + l.id + ', this.checked)">'
      + '<div style="flex:1">'
      + '<div style="font-weight:500">' + escapeHtml(l.name) + '</div>'
      + '<div style="font-size:12px;color:var(--ink-soft)">' + l.member_count + ' contact(s)' + (l.description ? ' · ' + escapeHtml(l.description) : '') + '</div>'
      + '</div>'
      + '</label>';
  }
  html += '</div>';
  wrap.innerHTML = html;

  // Compute eligible count if any lists pre-selected
  if (sendState.selectedListIds.length > 0) {
    await refreshRecipientSummary();
  } else {
    updateSendCTAEnabled();
  }
}

async function toggleSendList(listId, checked) {
  listId = parseInt(listId, 10);
  const set = new Set(sendState.selectedListIds);
  if (checked) set.add(listId);
  else         set.delete(listId);
  sendState.selectedListIds = Array.from(set);

  // Update the visual highlight on the parent label
  // (quick and dirty: re-query checkboxes)
  document.querySelectorAll('#send-lists-wrap label').forEach(lbl => {
    const cb = lbl.querySelector('input[type="checkbox"]');
    if (cb) lbl.style.background = cb.checked ? '#F0F7E8' : '#fff';
  });

  await refreshRecipientSummary();
}

async function refreshRecipientSummary() {
  const summary = document.getElementById('send-recipient-summary');
  if (sendState.selectedListIds.length === 0) {
    summary.style.display = 'none';
    sendState.eligibleCount = 0;
    updateSendCTAEnabled();
    return;
  }

  // Save the selection to the server AND compute eligible count
  // (campaigns.php set_recipients handles this)
  const saveRes = await api('/campaigns.php?action=set_recipients', {
    method: 'POST',
    body: JSON.stringify({
      id: sendState.campaignId,
      list_ids: sendState.selectedListIds,
      contact_ids: [],
    }),
  });

  if (!saveRes.success) {
    summary.textContent = saveRes.message || 'Could not save recipient selection';
    summary.style.background = '#FFF5E0';
    summary.style.borderLeftColor = '#D4A017';
    summary.style.display = 'block';
    updateSendCTAEnabled();
    return;
  }

  // Compute an estimated unique count from list memberships.
  // This is an upper bound — actual send will further filter by consent_status and suppression list.
  let unique = new Set();
  for (const listId of sendState.selectedListIds) {
    const list = sendState.lists.find(l => parseInt(l.id, 10) === parseInt(listId, 10));
    if (list) {
      // member_count is per-list; we don't have dedup info here, so we estimate by summing
      // For a more accurate count we'd need a server endpoint — deferred.
    }
  }

  const listCount = sendState.selectedListIds.length;
  const sumMembers = sendState.selectedListIds.reduce((total, id) => {
    const list = sendState.lists.find(l => parseInt(l.id, 10) === parseInt(id, 10));
    return total + (list ? parseInt(list.member_count, 10) : 0);
  }, 0);

  sendState.eligibleCount = sumMembers;

  summary.innerHTML = '<strong>Estimated ' + sumMembers + ' recipient(s)</strong> across ' + listCount + ' list(s). '
    + '<span style="color:var(--ink-soft)">Unsubscribed contacts and any on the suppression list will be skipped.</span>';
  summary.style.background = '#F0F7E8';
  summary.style.borderLeftColor = 'var(--accent)';
  summary.style.display = 'block';

  updateSendCTAEnabled();
}

function updateSendControls() {
  const schedule = document.querySelector('input[name="send-when"][value="schedule"]').checked;
  document.getElementById('send-schedule-controls').style.display = schedule ? 'block' : 'none';
  document.getElementById('send-cta-btn').textContent = schedule ? 'Schedule campaign' : 'Send now';
  // If switching to schedule, prefill datetime with +1 hour from now
  if (schedule) {
    const when = document.getElementById('send-schedule-when');
    if (!when.value) {
      const dt = new Date(Date.now() + 60 * 60 * 1000);
      // Format for datetime-local: YYYY-MM-DDTHH:MM
      const pad = n => String(n).padStart(2, '0');
      when.value = dt.getFullYear() + '-' + pad(dt.getMonth()+1) + '-' + pad(dt.getDate())
                 + 'T' + pad(dt.getHours()) + ':' + pad(dt.getMinutes());
    }
  }
  updateSendCTAEnabled();
}

function updateSendCTAEnabled() {
  const btn = document.getElementById('send-cta-btn');
  if (!btn) return;
  const enabled = sendState.selectedListIds.length > 0 && sendState.eligibleCount > 0;
  btn.disabled = !enabled;
  btn.style.opacity = enabled ? '1' : '0.5';
  btn.style.cursor = enabled ? 'pointer' : 'not-allowed';
}

async function sendTestCampaign() {
  const testEmail = document.getElementById('send-test-email').value.trim();
  const errEl = document.getElementById('send-error');
  errEl.textContent = '';

  if (!testEmail) {
    errEl.textContent = 'Enter an email address to send the test to';
    return;
  }

  const btn = document.getElementById('send-test-btn');
  btn.disabled = true;
  btn.textContent = 'Sending…';

  const data = await api('/campaigns.php?action=send_test', {
    method: 'POST',
    body: JSON.stringify({ id: sendState.campaignId, test_email: testEmail }),
  });

  btn.disabled = false;
  btn.textContent = 'Send test';

  if (data.success) {
    toast(data.message || 'Test sent');
  } else {
    errEl.textContent = data.message || 'Test send failed';
  }
}

async function executeSend() {
  const errEl = document.getElementById('send-error');
  errEl.textContent = '';

  if (sendState.selectedListIds.length === 0) {
    errEl.textContent = 'Pick at least one list';
    return;
  }

  // Pre-send safety check — make sure a test was sent and reviewed, and warn on
  // unrecognized merge tags or a missing postal address. (The server enforces
  // the test gate too; this surfaces it nicely instead of a raw rejection.)
  let pf = null;
  try {
    pf = await api('/campaigns.php?action=preflight&id=' + encodeURIComponent(sendState.campaignId));
  } catch (e) { pf = null; }
  if (pf && pf.success) {
    if (!pf.test_sent_at) {
      errEl.textContent = 'Send yourself a test first and review it — use the “Send test” box above, then come back.';
      return;
    }
    if (Array.isArray(pf.unknown_tags) && pf.unknown_tags.length) {
      const ok = confirm('Heads up — these look like personalization tags but will be sent as literal text:\n\n'
        + pf.unknown_tags.join('    ')
        + '\n\nValid tags: {{first_name}}, {{last_name}}, {{full_name}}, {{email}}.\n\nSend anyway?');
      if (!ok) return;
    }
    if (!pf.has_physical_address) {
      const ok = confirm('No physical mailing address is set in your Sender profile.\n\n'
        + 'A postal address is legally required in marketing email (CAN-SPAM) and helps deliverability.\n\nSend without it anyway?');
      if (!ok) return;
    }
  }
  const missingNote = (pf && pf.missing_first_name)
    ? ' Note: ' + pf.missing_first_name + ' recipient(s) have no first name on file.'
    : '';

  const schedule = document.querySelector('input[name="send-when"][value="schedule"]').checked;
  const btn = document.getElementById('send-cta-btn');

  if (schedule) {
    const when = document.getElementById('send-schedule-when').value;
    if (!when) {
      errEl.textContent = 'Pick a date and time for the scheduled send';
      return;
    }
    btn.disabled = true;
    btn.textContent = 'Scheduling…';
    // Convert datetime-local (author's browser timezone) to UTC for server storage.
    // datetime-local value like "2026-04-22T14:30" is interpreted as author's local time
    // by `new Date()` constructor. We format as UTC SQL string and send.
    const localDate = new Date(when);
    if (isNaN(localDate.getTime())) {
      errEl.textContent = 'Invalid date format';
      btn.disabled = false;
      btn.textContent = 'Schedule campaign';
      return;
    }
    const pad = n => String(n).padStart(2, '0');
    const utcSql = localDate.getUTCFullYear() + '-'
                 + pad(localDate.getUTCMonth() + 1) + '-'
                 + pad(localDate.getUTCDate()) + ' '
                 + pad(localDate.getUTCHours()) + ':'
                 + pad(localDate.getUTCMinutes()) + ':00';
    const data = await api('/campaigns.php?action=schedule', {
      method: 'POST',
      body: JSON.stringify({ id: sendState.campaignId, scheduled_at: utcSql }),
    });
    btn.disabled = false;
    btn.textContent = 'Schedule campaign';
    if (data.success) {
      // Show the scheduled time in the author's local timezone
      const localTimeStr = data.scheduled_at ? formatDateTime(data.scheduled_at) : '';
      toast(localTimeStr ? 'Campaign scheduled for ' + localTimeStr : (data.message || 'Scheduled'));
      navigate('email');
    } else {
      errEl.textContent = data.message || 'Scheduling failed';
    }
    return;
  }

  // Send now — confirm first
  const sendCount = (pf && typeof pf.recipients_count === 'number') ? pf.recipients_count : sendState.eligibleCount;
  const confirmMsg = 'Send this campaign now to approximately ' + sendCount
    + ' recipient(s)?' + missingNote + ' This cannot be undone.';
  if (!confirm(confirmMsg)) return;

  btn.disabled = true;
  btn.textContent = 'Sending…';

  const data = await api('/campaigns.php?action=send_now', {
    method: 'POST',
    body: JSON.stringify({ id: sendState.campaignId }),
  });

  btn.disabled = false;
  btn.textContent = 'Send now';

  if (data.success) {
    const remaining = (data.remaining || 0);
    let msg = 'Sending started. ' + (data.sent || 0) + ' already sent';
    if (data.failed) msg += ', ' + data.failed + ' failed';
    if (remaining > 0) {
      // Sending is paced to stay under the email provider's hourly limit
      // (~80/hour), so large campaigns finish over hours, not minutes. Give a
      // real estimate — otherwise a 300-recipient send looks broken when only
      // the first batch goes out.
      const hours = Math.ceil(remaining / 80);
      const eta = hours <= 1 ? 'within the hour' : 'over about ' + hours + ' hours';
      msg += ', ' + remaining + ' still to go. The rest send automatically '
           + eta + ' — delivery is paced so your email provider doesn\'t'
           + ' rate-limit the campaign. You can close this page.';
    }
    toast(msg);
    navigate('email');
  } else {
    errEl.textContent = data.message || 'Send failed';
  }
}

function backToComposer() {
  // Re-open the composer for this campaign
  openComposer(sendState.campaignId);
}

// ── CAMPAIGNS LIST + COMPOSER ────────────────────────────────
// Session 4 Part B1: campaign list + draft editor. B2 will add
// the recipients picker, test send, and schedule/send actions.

let campaignsCache = [];
let composerState = {
  id: null,             // campaign id (set after first create)
  dirty: false,         // has unsaved changes
  saveTimer: null,      // debounce timer for auto-save
  lastSavedAt: null,    // for the "saved at" indicator
  suppressChange: false, // when we're programmatically filling fields
  htmlMode: false,      // body editor showing raw HTML source (v137)
};

// ── Visual ⇄ HTML source toggle (v137) ────────────────────────
// The body editor is a contenteditable (visual) — pasting HTML *code*
// into it gets escaped to literal text. The </> HTML button swaps in a
// textarea holding the raw source, so pasted HTML emails land intact.
// The contenteditable div stays the source of truth for saves; the
// textarea syncs into it on every change.
function composerToggleHtml() {
  const body   = document.getElementById('composer-body');
  const source = document.getElementById('composer-body-source');
  const btn    = document.getElementById('composer-html-toggle');
  if (!body || !source) return;
  if (!composerState.htmlMode) {
    source.value = body.innerHTML;
    body.style.display   = 'none';
    source.style.display = 'block';
    composerState.htmlMode = true;
    if (btn) { btn.style.background = 'var(--accent)'; btn.style.color = '#fff'; }
  } else {
    body.innerHTML = source.value;
    source.style.display = 'none';
    body.style.display   = 'block';
    composerState.htmlMode = false;
    if (btn) { btn.style.background = ''; btn.style.color = ''; }
    composerOnChange();   // rendered content may differ — queue a save
  }
}

// Keep the hidden contenteditable current while typing in source view,
// so saves, validation, and previews always read real content.
function composerSyncFromSource() {
  if (!composerState.htmlMode) return;
  const body   = document.getElementById('composer-body');
  const source = document.getElementById('composer-body-source');
  if (body && source) body.innerHTML = source.value;
}

async function loadCampaigns() {
  const wrap = document.getElementById('campaigns-wrap');
  if (!wrap) return;

  const data = await api('/campaigns.php?action=list');
  if (!data.success) {
    wrap.innerHTML = '<div class="empty">Failed to load campaigns</div>';
    return;
  }

  campaignsCache = data.campaigns || [];
  renderCampaignsList();
}

function renderCampaignsList() {
  const wrap = document.getElementById('campaigns-wrap');
  if (!wrap) return;

  // The "Buy 5,000 emails ($10)" top-up card was removed with the rest of
  // the checkout flow. Extra send allowance is arranged by enquiry now.
  if (campaignsCache.length === 0) {
    wrap.innerHTML = '<div class="empty">No campaigns yet. Click "+ New campaign" to start writing your first email.</div>';
    return;
  }

  let html = '';
  for (const c of campaignsCache) {
    html += renderCampaignRow(c);
  }
  wrap.innerHTML = html;
}

function renderCampaignRow(c) {
  const statusLabels = {
    draft:     { label: 'Draft',     badge: 'badge-gray' },
    scheduled: { label: 'Scheduled', badge: 'badge-blue' },
    sending:   { label: 'Sending',   badge: 'badge-amber' },
    sent:      { label: 'Sent',      badge: 'badge-green' },
    failed:    { label: 'Failed',    badge: 'badge-red' },
    cancelled: { label: 'Cancelled', badge: 'badge-gray' },
  };
  const st = statusLabels[c.status] || { label: c.status, badge: 'badge-gray' };

  const meta = [];
  // Lead with the date Bob asked for: when it was used (sent), otherwise when it was created.
  if (c.sent_at) {
    meta.push('Used ' + formatDate(c.sent_at));
  } else {
    meta.push('Created ' + formatDate(c.created_at));
  }
  if (c.status === 'scheduled' && c.scheduled_at) {
    meta.push('Scheduled for ' + formatDateTime(c.scheduled_at));
  }
  if (c.status === 'sent' && c.sent_at) {
    meta.push((c.sends_count || 0) + ' recipient' + ((c.sends_count || 0) === 1 ? '' : 's'));
  }
  if (c.status === 'sending') {
    meta.push('Sending to ' + (c.recipients_count || 0) + ' recipient' + ((c.recipients_count || 0) === 1 ? '' : 's'));
    meta.push((c.sends_count || 0) + ' sent so far');
  }

  const dupBtn = '<button class="app-btn app-btn-outline app-btn-sm" onclick="duplicateCampaign(' + c.id + ')">Duplicate</button>';
  let actions = '';
  if (c.status === 'draft') {
    actions = '<button class="app-btn app-btn-outline app-btn-sm" onclick="openComposer(' + c.id + ')">Edit</button>'
            + dupBtn
            + '<button class="app-btn app-btn-outline app-btn-sm" onclick="deleteCampaign(' + c.id + ',\'' + escapeHtml(c.name || '').replace(/'/g, "\\'") + '\')">Delete</button>';
  } else if (c.status === 'scheduled') {
    actions = '<button class="app-btn app-btn-outline app-btn-sm" onclick="cancelCampaign(' + c.id + ')">Cancel &amp; edit</button>'
            + dupBtn;
  } else if (c.status === 'sending' || c.status === 'sent') {
    actions = '<button class="app-btn app-btn-outline app-btn-sm" onclick="openCampaignDetail(' + c.id + ')">See stats</button>'
            + '<button class="app-btn app-btn-outline app-btn-sm" onclick="openComposer(' + c.id + ')">View content</button>'
            + dupBtn;
  } else if (c.status === 'failed' || c.status === 'cancelled') {
    actions = '<button class="app-btn app-btn-outline app-btn-sm" onclick="openComposer(' + c.id + ')">View</button>'
            + dupBtn;
  }

  // Sent/sending campaigns: clicking the row goes to stats. Others go to composer.
  const rowClickHandler = (c.status === 'sent' || c.status === 'sending')
    ? 'openCampaignDetail(' + c.id + ')'
    : 'openComposer(' + c.id + ')';

  return '<div class="campaign-row">'
    + '<div class="campaign-row-main" onclick="' + rowClickHandler + '">'
    + '<div class="campaign-row-name">' + escapeHtml(c.name || '(untitled)') + '</div>'
    + '<div class="campaign-row-meta">'
    + '<span class="badge ' + st.badge + '">' + st.label + '</span>'
    + (meta.length ? ' · ' + meta.map(escapeHtml).join(' · ') : '')
    + '</div>'
    + '</div>'
    + '<div class="campaign-row-actions">' + actions + '</div>'
    + '</div>';
}

function formatDate(sqlDt) {
  if (!sqlDt) return '';
  // Date-only label (e.g. "Jun 12, 2026"). Same UTC->local parsing as formatDateTime.
  const d = new Date(sqlDt.replace(' ', 'T') + 'Z');
  if (isNaN(d.getTime())) return sqlDt;
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDateTime(sqlDt) {
  if (!sqlDt) return '';
  // MySQL datetime "2026-04-21 08:15:00" is now stored in UTC.
  // Append 'Z' so JavaScript parses it as UTC; toLocaleString then displays in browser local time.
  const d = new Date(sqlDt.replace(' ', 'T') + 'Z');
  if (isNaN(d.getTime())) return sqlDt;
  return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

async function deleteCampaign(id, name) {
  if (!confirm('Delete campaign "' + name + '"? This cannot be undone.')) return;
  const data = await api('/campaigns.php?action=delete', {
    method: 'POST',
    body: JSON.stringify({ id }),
  });
  if (data.success) {
    toast(data.message || 'Deleted');
    loadCampaigns();
  } else {
    toast(data.message || 'Delete failed', true);
  }
}

async function cancelCampaign(id) {
  if (!confirm('Cancel this scheduled campaign? It will return to draft status — it will not be sent.')) return;
  const data = await api('/campaigns.php?action=cancel', {
    method: 'POST',
    body: JSON.stringify({ id }),
  });
  if (data.success) {
    toast(data.message || 'Returned to draft');
    loadCampaigns();
  } else {
    toast(data.message || 'Cancel failed', true);
  }
}

// Clone an existing campaign's content into a fresh draft and open it, so the
// same email can be reused with a different list. The copy has no recipients and
// hasn't been test-sent, so the user picks a list and tests before it can send.
async function duplicateCampaign(id) {
  const data = await api('/campaigns.php?action=duplicate', {
    method: 'POST',
    body: JSON.stringify({ id }),
  });
  if (data.success && data.id) {
    toast('Copied to a new draft — choose a list and send a test before sending');
    openComposer(data.id);
  } else {
    toast(data.message || 'Duplicate failed', true);
  }
}

// ── Composer ─────────────────────────────────────────────────

async function openComposer(campaignId) {
  // Hide all views, show composer view
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.getElementById('view-composer').classList.add('active');

  composerState.id = campaignId;
  composerState.dirty = false;
  composerState.lastSavedAt = null;
  composerState.suppressChange = true;

  // Always open in visual mode — loaded body_html renders in the editor.
  if (composerState.htmlMode) composerToggleHtml();
  const srcTa = document.getElementById('composer-body-source');
  if (srcTa) srcTa.value = '';

  // Clear the form
  document.getElementById('composer-name').value = '';
  document.getElementById('composer-subject').value = '';
  document.getElementById('composer-preheader').value = '';
  document.getElementById('composer-body').innerHTML = '';
  document.getElementById('composer-save-status').textContent = '';
  _emailSubjectPool = [];
  _emailSubjectIdx  = 0;
  const counter = document.getElementById('composer-subject-counter');
  if (counter) counter.style.display = 'none';
  composerPopulateBookPicker();

  // Default: hide the Next button. We'll show it once we know the campaign is in an editable state.
  const nextBtn = document.getElementById('composer-next-btn');
  if (nextBtn) nextBtn.style.display = 'none';

  if (campaignId === null) {
    // Create a new draft immediately so we have an id to auto-save against
    const data = await api('/campaigns.php?action=create', {
      method: 'POST',
      body: JSON.stringify({ name: '' }),
    });
    if (!data.success) {
      toast(data.message || 'Failed to create campaign', true);
      closeComposer();
      return;
    }
    composerState.id = data.id;
    // New draft — show Next button
    if (nextBtn) nextBtn.style.display = '';
  } else {
    // Load existing campaign
    const data = await api('/campaigns.php?action=get&id=' + campaignId);
    if (!data.success) {
      toast(data.message || 'Failed to load campaign', true);
      closeComposer();
      return;
    }
    const c = data.campaign;
    document.getElementById('composer-name').value = c.name || '';
    document.getElementById('composer-subject').value = c.subject || '';
    document.getElementById('composer-preheader').value = c.preheader || '';
    // Prefer HTML, fall back to text converted to paragraphs
    if (c.body_html) {
      document.getElementById('composer-body').innerHTML = c.body_html;
    } else if (c.body_text) {
      document.getElementById('composer-body').innerHTML = textToSimpleHtml(c.body_text);
    }
    // If not a draft, make the editor read-only
    const isEditable = (c.status === 'draft' || c.status === 'scheduled');
    setComposerEditable(isEditable);
    // Show Next button for editable campaigns
    if (nextBtn && isEditable) nextBtn.style.display = '';
  }

  composerState.suppressChange = false;
  // Focus subject if empty, else body
  const subj = document.getElementById('composer-subject');
  if (!subj.value) {
    subj.focus();
  }
}

function setComposerEditable(editable) {
  const ids = ['composer-name', 'composer-subject', 'composer-preheader'];
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.readOnly = !editable;
  });
  const body = document.getElementById('composer-body');
  if (body) body.contentEditable = editable ? 'true' : 'false';
  const srcTa = document.getElementById('composer-body-source');
  if (srcTa) srcTa.readOnly = !editable;
  // Hide the toolbar entirely if not editable
  const tb = document.getElementById('composer-toolbar');
  if (tb) tb.style.display = editable ? '' : 'none';
  // Disable AI buttons on read-only campaigns
  ['composer-subject-ai-btn', 'composer-preheader-ai-btn'].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) btn.disabled = !editable;
  });
}

function textToSimpleHtml(text) {
  // Convert double-newlines to paragraph breaks, single newlines to <br>
  const escaped = (text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  const parts = escaped.split(/\n\s*\n/);
  return parts.map(p => '<p>' + p.replace(/\n/g, '<br>') + '</p>').join('');
}

function closeComposer() {
  // Flush any pending auto-save before closing
  if (composerState.dirty) {
    composerSaveNow();
  }
  // Re-show the email hub view via normal navigation
  navigate('email');
  // Refresh the campaigns list so changes are visible
  loadCampaigns();
}

function composerOnChange() {
  if (composerState.suppressChange) return;
  composerSyncFromSource();   // no-op in visual mode
  composerState.dirty = true;
  document.getElementById('composer-save-status').textContent = 'Unsaved changes…';

  // Debounce auto-save
  if (composerState.saveTimer) clearTimeout(composerState.saveTimer);
  composerState.saveTimer = setTimeout(() => { composerSaveNow(); }, 1500);
}

async function composerSaveNow() {
  if (composerState.id === null) return;
  if (composerState.saveTimer) {
    clearTimeout(composerState.saveTimer);
    composerState.saveTimer = null;
  }

  composerSyncFromSource();   // in HTML view the textarea is authoritative
  const name      = document.getElementById('composer-name').value.trim();
  const subject   = document.getElementById('composer-subject').value.trim();
  const preheader = document.getElementById('composer-preheader').value.trim();
  const bodyHtml  = document.getElementById('composer-body').innerHTML;
  const bodyText  = document.getElementById('composer-body').innerText || '';

  const payload = {
    id:        composerState.id,
    name:      name || 'Untitled draft',
    subject,
    preheader,
    body_html: bodyHtml,
    body_text: bodyText,
  };

  document.getElementById('composer-save-status').textContent = 'Saving…';

  const data = await api('/campaigns.php?action=update', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  if (data.success) {
    composerState.dirty = false;
    composerState.lastSavedAt = new Date();
    document.getElementById('composer-save-status').textContent = 'Saved';
    setTimeout(() => {
      if (!composerState.dirty) {
        document.getElementById('composer-save-status').textContent = '';
      }
    }, 2000);
  } else {
    document.getElementById('composer-save-status').textContent = 'Save failed';
    toast(data.message || 'Save failed', true);
  }
}

// Toolbar formatting
function composerFormat(command) {
  document.getElementById('composer-body').focus();
  document.execCommand(command, false, null);
  composerOnChange();
}

function composerInsertLink() {
  const body = document.getElementById('composer-body');
  body.focus();
  const url = prompt('Enter URL:', 'https://');
  if (!url) return;
  // If text is selected, turn it into a link; else insert "link text" with the URL
  const selection = window.getSelection();
  if (selection && selection.toString().trim().length > 0) {
    document.execCommand('createLink', false, url);
  } else {
    const linkText = prompt('Link text:', url);
    if (!linkText) return;
    const a = '<a href="' + url.replace(/"/g, '&quot;') + '">' + escapeHtml(linkText) + '</a>';
    document.execCommand('insertHTML', false, a);
  }
  composerOnChange();
}

function composerInsertMergeTag(tag) {
  if (!tag) return;
  const body = document.getElementById('composer-body');
  body.focus();
  document.execCommand('insertText', false, tag);
  composerOnChange();
}

function composerPopulateBookPicker() {
  const sel = document.getElementById('composer-book-id');
  if (!sel) return;
  const current = sel.value;
  sel.innerHTML = '<option value="0">No specific book</option>' +
    (booksList || []).map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
  if (current && current !== '0') {
    sel.value = current;
  } else if (booksList && booksList.length === 1) {
    sel.value = booksList[0].id;
  }
}

let _emailSubjectPool = [];
let _emailSubjectIdx  = 0;

async function aiEmailSubject() {
  const btn     = document.getElementById('composer-subject-ai-btn');
  const counter = document.getElementById('composer-subject-counter');
  const bookId  = parseInt(document.getElementById('composer-book-id').value, 10) || 0;

  // If we still have unused options in the pool, just cycle to the next one
  if (_emailSubjectPool.length > 0 && _emailSubjectIdx < _emailSubjectPool.length) {
    document.getElementById('composer-subject').value = _emailSubjectPool[_emailSubjectIdx];
    counter.textContent = 'idea ' + (_emailSubjectIdx + 1) + ' of ' + _emailSubjectPool.length;
    counter.style.display = '';
    _emailSubjectIdx++;
    composerOnChange();
    return;
  }

  // Pool exhausted — fetch a fresh batch
  const seed = document.getElementById('composer-subject').value.trim();
  btn.disabled = true;
  btn.textContent = '…';
  counter.style.display = 'none';

  const data = await api('/email_ai.php', {
    method: 'POST',
    body: JSON.stringify({ action: 'subject', book_id: bookId, seed }),
  });

  btn.disabled = false;
  btn.textContent = '✨ AI idea';

  if (!data.success) {
    toast(data.message || 'AI subject generation failed', true);
    return;
  }

  _emailSubjectPool = data.subjects || [];
  _emailSubjectIdx  = 0;

  if (!_emailSubjectPool.length) { toast('No subjects returned', true); return; }

  // Show first idea immediately
  document.getElementById('composer-subject').value = _emailSubjectPool[0];
  _emailSubjectIdx = 1;
  counter.textContent = 'idea 1 of ' + _emailSubjectPool.length + ' — click again for next';
  counter.style.display = '';
  composerOnChange();

  if (data.quota) updateQuotaMeter(data.quota);
}

async function aiEmailPreheader() {
  const btn = document.getElementById('composer-preheader-ai-btn');
  const subject = document.getElementById('composer-subject').value.trim();
  const seed = document.getElementById('composer-preheader').value.trim();
  const bookId = parseInt(document.getElementById('composer-book-id').value, 10) || 0;

  if (!subject && !seed) {
    toast('Add a subject line first so AI has context', true);
    return;
  }

  btn.disabled = true;
  btn.textContent = '…';

  const data = await api('/email_ai.php', {
    method: 'POST',
    body: JSON.stringify({ action: 'preheader', book_id: bookId, subject, seed }),
  });

  btn.disabled = false;
  btn.textContent = '✨ AI';

  if (!data.success) {
    toast(data.message || 'AI preview text generation failed', true);
    return;
  }

  document.getElementById('composer-preheader').value = data.preheader || '';
  composerOnChange();

  if (data.quota) updateQuotaMeter(data.quota);
}

async function aiEmailBody() {
  const btn = document.getElementById('composer-body-ai-btn');
  const subject = document.getElementById('composer-subject').value.trim();
  const bodyEl = document.getElementById('composer-body');
  const seed = bodyEl.innerText.trim();
  const bookId = parseInt(document.getElementById('composer-book-id').value, 10) || 0;

  if (!subject && !seed) {
    toast('Add a subject line or type a prompt in the body first', true);
    return;
  }

  btn.disabled = true;
  btn.textContent = '…';

  const data = await api('/email_ai.php', {
    method: 'POST',
    body: JSON.stringify({ action: 'body', book_id: bookId, subject, seed }),
  });

  btn.disabled = false;
  btn.textContent = '✨ AI Draft';

  if (!data.success) {
    toast(data.message || 'AI body draft failed', true);
    return;
  }

  bodyEl.innerHTML = textToSimpleHtml(data.body || '');
  composerOnChange();

  if (data.quota) updateQuotaMeter(data.quota);
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Note: composerGoToSend() is defined in the SEND FLOW module below.

// Keyboard shortcuts for the composer
document.addEventListener('keydown', function(e) {
  const composerOpen = document.getElementById('view-composer').classList.contains('active');
  if (!composerOpen) return;
  const inBody = document.activeElement && document.activeElement.id === 'composer-body';
  if (!inBody) return;
  if ((e.ctrlKey || e.metaKey) && !e.shiftKey && !e.altKey) {
    if (e.key === 'b' || e.key === 'B') { e.preventDefault(); composerFormat('bold'); }
    if (e.key === 'i' || e.key === 'I') { e.preventDefault(); composerFormat('italic'); }
    if (e.key === 's' || e.key === 'S') { e.preventDefault(); composerSaveNow(); }
  }
});

// ── EMAIL CAMPAIGNS ───────────────────────────────────────────
// Session 3 Part B1: hub page with education + status display.
// Part B2 will add the interactive Mailgun setup wizard.

// Education: "Email marketing for authors" — shown in Zone 1.
// Editable: change the HTML here to rewrite in your own voice.
const EMAIL_PRIMER_HTML = `
<h2 style="font-family:var(--font-serif);font-size:22px;margin:0 0 12px">Email marketing for authors</h2>

<h3 style="font-family:var(--font-serif);font-size:16px;margin:18px 0 8px">Why email still matters</h3>
<p>Your email list is the one marketing channel you fully own. Social media algorithms change every few months, and a single platform change can cut your reach overnight. Your email list can't be taken away from you. Every person who opens your email chose to be there, and that intent makes email dramatically more effective than social posts.</p>
<p>For independent authors, email typically converts 5 to 10 times better than social media for book sales. A 500-person newsletter launched correctly can move more books than a 5,000-follower Instagram account.</p>

<h3 style="font-family:var(--font-serif);font-size:16px;margin:18px 0 8px">What email marketing actually is</h3>
<p>At its simplest, it's sending messages to people who asked to hear from you. For authors, that usually looks like:</p>
<ul style="padding-left:22px;margin:8px 0">
  <li><strong>Reader newsletter</strong> — a regular update to your readers (monthly works well) with news, excerpts, recommendations, or behind-the-scenes notes</li>
  <li><strong>New release announcements</strong> — letting subscribers know when a book is launching</li>
  <li><strong>ARC outreach</strong> — sending advance review copies to a smaller list of dedicated readers</li>
  <li><strong>Review requests</strong> — following up with readers a few weeks after a launch</li>
  <li><strong>Series or backlist reminders</strong> — gentle nudges about earlier books</li>
</ul>
<p>Readers who signed up via your website, a signup form in your book, or a giveaway are your best audience. Never buy lists — it's illegal in most countries, it trashes your sender reputation, and it doesn't work.</p>

<h3 style="font-family:var(--font-serif);font-size:16px;margin:18px 0 8px">What this will cost</h3>
<p><strong>While your list is small (under about 500 contacts):</strong> free. Most email providers offer a free tier at this volume.</p>
<p><strong>Once your list grows:</strong> about $15/month with Mailgun, which is what we support directly. This pays for the sending infrastructure, not our app.</p>
<p><strong>Optional purchases along the way:</strong> a custom domain (~$12/year, from a registrar like Namecheap or GoDaddy) makes your emails look professional. You'd want this even if you weren't emailing — it's the foundation for a real author presence online.</p>

<h3 style="font-family:var(--font-serif);font-size:16px;margin:18px 0 8px">The four rules</h3>
<p>These apply in every country, with slight variations. Break them and your emails go to spam, your domain reputation gets wrecked, and in some jurisdictions you can be fined.</p>
<ol style="padding-left:22px;margin:8px 0">
  <li><strong>Only email people who asked to hear from you.</strong> A reader who bought your book has not asked to hear from you. A reader who signed up through a form has.</li>
  <li><strong>Every email must include a clear way to unsubscribe.</strong> Our app handles this automatically.</li>
  <li><strong>Every email must include your physical mailing address.</strong> This is US law (CAN-SPAM) and our app collects it from you during setup.</li>
  <li><strong>Honor unsubscribes promptly.</strong> Someone who unsubscribes should never hear from you again unless they re-subscribe.</li>
</ol>

<h3 style="font-family:var(--font-serif);font-size:16px;margin:18px 0 8px">What sending well looks like</h3>
<p>Good author newsletters are conversational, not promotional. Share what you're reading, what you're working on, what you learned this month. Book sales follow naturally when readers feel connected to you as a person.</p>
<p>Consistency matters more than frequency. A monthly newsletter you actually send beats a weekly newsletter you give up on after three weeks.</p>
<p>Keep a plain-text voice. You're a writer — sound like one. Avoid heavy design, stock photos, and corporate marketing language. Readers subscribed to hear from <em>you</em>.</p>
`;

// Alternatives content — shown when user expands "Prefer a different service?"
const EMAIL_ALTERNATIVES_HTML = `
<p style="margin-top:0">Not every author wants to run their own sending through Mailgun. Here are the common alternatives.</p>

<p><strong>How this app works if you don't use Mailgun:</strong> You can still use the <strong>Contacts</strong> section as your master list — add, organize, tag, and segment contacts here, then export to CSV and import into whichever service you pick. You just won't be able to compose and send campaigns from inside this app.</p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:14px 0">
  <div style="padding:12px;background:#FAFAF7;border-radius:6px">
    <strong>Mailchimp</strong>
    <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Visual drag-and-drop builder. Free up to 500 contacts. Gets expensive fast as you grow.</div>
  </div>
  <div style="padding:12px;background:#FAFAF7;border-radius:6px">
    <strong>ConvertKit (now Kit)</strong>
    <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Creator-focused. Free up to 10,000 subscribers for basic broadcasts. Good if you want automations.</div>
  </div>
  <div style="padding:12px;background:#FAFAF7;border-radius:6px">
    <strong>Substack</strong>
    <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Free for free newsletters. Takes 10% of paid subscriptions. Readers belong to Substack, not you.</div>
  </div>
  <div style="padding:12px;background:#FAFAF7;border-radius:6px">
    <strong>Buttondown</strong>
    <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Simple, writer-focused. Free up to 100 subscribers. Fewer automation features.</div>
  </div>
  <div style="padding:12px;background:#FAFAF7;border-radius:6px">
    <strong>Gmail / Outlook Bcc</strong>
    <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Works for very small lists (under 50). Free. Deliverability suffers fast and there's no tracking.</div>
  </div>
  <div style="padding:12px;background:#F0F7E8;border-left:3px solid var(--accent);border-radius:6px">
    <strong>Mailgun (what we support)</strong>
    <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Free to start, ~$15/mo once you grow. Send from inside this app with full tracking. Requires 30min DNS setup.</div>
  </div>
</div>

<p style="font-size:13px;color:var(--ink-soft)">Still undecided? That's fine. Your contact list works either way. Set up Mailgun when you're ready.</p>
`;

async function loadEmailView() {
  // Render the primer (idempotent — safe to call multiple times)
  const primerEl = document.getElementById('email-primer-content');
  if (primerEl && primerEl.innerHTML.trim().length < 100) {
    primerEl.innerHTML = EMAIL_PRIMER_HTML;
  }

  // Fetch sender status and render the "Your email system" zone
  await renderEmailSystemStatus();

  // Also load campaigns list
  await loadCampaigns();
}

async function renderEmailSystemStatus() {
  const wrap = document.getElementById('email-system-status');
  if (!wrap) return;

  wrap.innerHTML = '<div class="empty">Checking your setup…</div>';

  let data;
  try {
    data = await api('/sender.php?action=status');
  } catch (err) {
    wrap.innerHTML = '<div class="empty">Could not reach the server. Reload the page?</div>';
    return;
  }

  if (!data.success) {
    wrap.innerHTML = '<div class="empty">' + escapeHtml(data.message || 'Failed to load status') + '</div>';
    return;
  }

  const state  = data.state || 'unconfigured';
  const sender = data.sender || null;

  if (state === 'unconfigured') {
    wrap.innerHTML = renderUnconfiguredState();
  } else if (state === 'verified' && sender && sender.from_email) {
    wrap.innerHTML = renderVerifiedState(sender);
  } else {
    wrap.innerHTML = renderInProgressState(state, sender);
  }
}

function renderUnconfiguredState() {
  return '<div style="padding:16px 4px">'
    + '<div style="display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap">'
    + '<div style="flex:1;min-width:240px">'
    + '<div style="font-family:var(--font-serif);font-size:18px;margin-bottom:6px">Ready to set up email sending?</div>'
    + '<div style="font-size:13px;color:var(--ink-soft);line-height:1.55">We use Mailgun for the actual email sending. You\'ll set up a free Mailgun account (paid tier starts ~$15/mo when your list grows), add a few DNS records to your domain, and then you can send campaigns directly from this app.</div>'
    + '</div>'
    + '<div style="display:flex;flex-direction:column;gap:8px;min-width:180px">'
    + '<button class="app-btn app-btn-green" onclick="startMailgunWizard()">Get started</button>'
    + '<button class="app-btn app-btn-outline app-btn-sm" onclick="toggleEmailAlternatives()">Prefer another service?</button>'
    + '</div>'
    + '</div>'
    + '<div id="email-alternatives-expand" style="display:none;margin-top:18px;padding-top:18px;border-top:1px solid var(--ink-faint)">'
    + EMAIL_ALTERNATIVES_HTML
    + '</div>'
    + '</div>';
}

function renderInProgressState(state, sender) {
  const stateLabels = {
    unconfigured:         'Ready to start',
    pending_verification: 'Waiting for DNS verification',
    needs_profile:        'Domain verified — finish your sender profile',
  };
  const nextAction = {
    pending_verification: 'Continue wizard',
    needs_profile:        'Continue wizard',
  };

  const domain   = sender && sender.sending_domain ? sender.sending_domain : '';
  const label    = stateLabels[state] || ('Setup in progress: ' + state);
  const actionLabel = nextAction[state] || 'Continue wizard';

  return '<div style="padding:16px 4px">'
    + '<div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;justify-content:space-between">'
    + '<div>'
    + '<span class="badge badge-amber">In progress</span>'
    + '<div style="font-family:var(--font-serif);font-size:16px;margin-top:8px">' + escapeHtml(label) + '</div>'
    + (domain ? '<div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Domain: ' + escapeHtml(domain) + '</div>' : '')
    + '</div>'
    + '<div style="display:flex;flex-direction:column;gap:8px">'
    + '<button class="app-btn app-btn-green" onclick="startMailgunWizard()">' + escapeHtml(actionLabel) + '</button>'
    + '<button class="app-btn app-btn-outline app-btn-sm" onclick="disconnectMailgun()">Start over</button>'
    + '</div>'
    + '</div>'
    + '</div>';
}

function renderVerifiedState(sender) {
  const fromName  = sender.from_name  || '';
  const fromEmail = sender.from_email || '';
  const domain    = sender.sending_domain || '';

  return '<div style="padding:16px 4px">'
    + '<div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;justify-content:space-between">'
    + '<div>'
    + '<span class="badge badge-green">Verified and ready</span>'
    + '<div style="font-family:var(--font-serif);font-size:16px;margin-top:8px">From: ' + escapeHtml(fromName) + ' &lt;' + escapeHtml(fromEmail) + '&gt;</div>'
    + '<div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Sending domain: ' + escapeHtml(domain) + ' · Mailgun</div>'
    + '</div>'
    + '<div style="display:flex;gap:8px">'
    + '<button class="app-btn app-btn-outline app-btn-sm" onclick="startMailgunWizard()">Manage</button>'
    + '</div>'
    + '</div>'
    + '</div>';
}

function toggleEmailAlternatives() {
  const el = document.getElementById('email-alternatives-expand');
  if (!el) return;
  el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
}

// ── Mailgun setup wizard ─────────────────────────────────────

// Wizard state — populated when opened, referenced by step functions
let wizardState = {
  step: 1,           // 1..5
  apiKey: null,      // never persisted in browser, only during wizard session
  domain: null,      // the sending domain being added
  dnsRecords: null,  // the DNS records returned by Mailgun
  pollTimer: null,   // DNS auto-poll timer handle
  pollStart: null,   // when polling began (for the 10-min cap)
  senderSnapshot: null,  // for manage mode
};

async function startMailgunWizard() {
  // Fetch current state to decide which step to open at
  const data = await api('/sender.php?action=status');
  if (!data.success) {
    toast(data.message || 'Could not load status', true);
    return;
  }

  wizardState.senderSnapshot = data.sender || null;
  const state = data.state || 'unconfigured';

  // Map state to starting step. Auto-resume.
  if (state === 'unconfigured') {
    wizardState.step = 1;
  } else if (state === 'pending_verification') {
    // API key saved + domain added, waiting for DNS verification
    wizardState.step = 4;
    wizardState.domain = data.sender ? data.sender.sending_domain : null;
  } else if (state === 'needs_profile') {
    // DNS verified but sender profile not filled out
    wizardState.step = 5;
    wizardState.domain = data.sender ? data.sender.sending_domain : null;
  } else if (state === 'verified') {
    // Fully done — open in manage mode (step 5 with existing values, plus Disconnect)
    wizardState.step = 5;
    wizardState.domain = data.sender ? data.sender.sending_domain : null;
  } else {
    // If we saved an API key but no domain, step 3
    if (data.sender) wizardState.step = 3;
    else             wizardState.step = 1;
  }

  openWizardModal();
  showWizardStep(wizardState.step);
}

function openWizardModal() {
  document.getElementById('mailgun-wizard').style.display = 'flex';
}

function closeWizardModal() {
  // Always stop any polling when wizard closes
  stopDnsPolling();
  document.getElementById('mailgun-wizard').style.display = 'none';
}

function showWizardStep(step) {
  wizardState.step = step;

  // Hide all steps
  for (let i = 1; i <= 5; i++) {
    const el = document.getElementById('wiz-step-' + i);
    if (el) el.style.display = 'none';
  }
  const active = document.getElementById('wiz-step-' + step);
  if (active) active.style.display = 'block';

  // Update step indicator
  for (let i = 1; i <= 5; i++) {
    const dot = document.getElementById('wiz-dot-' + i);
    if (!dot) continue;
    dot.className = 'wiz-dot' + (i < step ? ' done' : i === step ? ' active' : '');
  }

  // Step-specific render hooks
  if (step === 4) renderStep4();
  if (step === 5) renderStep5();

  // Stop polling unless we're on step 4
  if (step !== 4) stopDnsPolling();
}

// ── Step 2: API key validation + save ────────────────────────

async function wizardValidateAndSaveKey() {
  const key = document.getElementById('wiz-api-key').value.trim();
  const btn = document.getElementById('wiz-step2-save');
  const errEl = document.getElementById('wiz-step2-error');
  errEl.textContent = '';

  if (!key) {
    errEl.textContent = 'Paste your API key';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Testing key…';

  const data = await api('/sender.php?action=save_key', {
    method: 'POST',
    body: JSON.stringify({ api_key: key, region: 'us' }),
  });

  btn.disabled = false;
  btn.textContent = 'Test and save';

  if (!data.success) {
    errEl.textContent = data.message || 'Could not save key';
    return;
  }

  wizardState.apiKey = key;
  showWizardStep(3);
}

// ── Step 3: Add domain ───────────────────────────────────────

async function wizardAddDomain() {
  const raw = document.getElementById('wiz-domain').value.trim();
  const btn = document.getElementById('wiz-step3-save');
  const errEl = document.getElementById('wiz-step3-error');
  errEl.textContent = '';

  if (!raw) {
    errEl.textContent = 'Enter your domain';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Adding domain…';

  const data = await api('/sender.php?action=add_domain', {
    method: 'POST',
    body: JSON.stringify({ domain: raw }),
  });

  btn.disabled = false;
  btn.textContent = 'Add domain';

  if (!data.success) {
    errEl.textContent = data.message || 'Failed to add domain';
    return;
  }

  wizardState.domain = data.sending_domain;
  wizardState.dnsRecords = data.dns_records;
  showWizardStep(4);
}

// ── Step 4: DNS records + verification polling ───────────────

function renderStep4() {
  const wrap = document.getElementById('wiz-dns-records');
  const domainEl = document.getElementById('wiz-step4-domain');

  if (wizardState.domain) {
    domainEl.textContent = wizardState.domain;
  }

  // If we don't have DNS records cached, fetch via a check_verification call
  if (!wizardState.dnsRecords) {
    wrap.innerHTML = '<div class="empty">Loading DNS records…</div>';
    wizardCheckVerification(false).then(() => {
      renderDnsRecordsTable();
    });
  } else {
    renderDnsRecordsTable();
  }

  // Start auto-polling after a short delay (give user time to read first)
  startDnsPolling();
}

function renderDnsRecordsTable() {
  const wrap = document.getElementById('wiz-dns-records');
  if (!wizardState.dnsRecords) {
    wrap.innerHTML = '<div class="empty">No DNS records yet</div>';
    return;
  }

  const sending = wizardState.dnsRecords.sending || [];
  const receiving = wizardState.dnsRecords.receiving || [];
  const all = [];
  sending.forEach(r => all.push(Object.assign({}, r, {_category: 'Sending'})));
  receiving.forEach(r => all.push(Object.assign({}, r, {_category: 'Receiving (optional, skip unless you want inbound email)'})));

  if (all.length === 0) {
    wrap.innerHTML = '<div class="empty">No records returned. Try refreshing.</div>';
    return;
  }

  let html = '';
  let lastCategory = '';
  for (const rec of all) {
    if (rec._category !== lastCategory) {
      html += '<div style="font-size:12px;color:var(--ink-soft);margin:12px 0 4px;font-weight:500">' + escapeHtml(rec._category) + '</div>';
      lastCategory = rec._category;
    }
    const valid = (rec.valid || '').toLowerCase() === 'valid';
    const badge = valid
      ? '<span class="badge badge-green">Verified</span>'
      : '<span class="badge badge-amber">Pending</span>';
    const recType = (rec.record_type || '').toUpperCase();
    const recName = rec.name || '';
    const recVal  = rec.value || '';
    const recId   = 'dns-' + Math.random().toString(36).slice(2, 10);

    html += '<div style="padding:10px;border:1px solid var(--ink-faint);border-radius:6px;margin-bottom:8px;background:' + (valid ? '#F0F7E8' : '#FAFAF7') + '">';
    html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">';
    html += '<div><strong style="font-family:monospace">' + escapeHtml(recType) + '</strong> record</div>';
    html += badge;
    html += '</div>';
    html += '<div style="font-size:11px;color:var(--ink-soft);margin-top:6px">Name / Host:</div>';
    html += '<div style="display:flex;gap:6px;align-items:center;margin-top:2px">';
    html += '<input type="text" readonly value="' + escapeHtml(recName) + '" id="' + recId + '-name" style="flex:1;font-family:monospace;font-size:12px;padding:6px 8px;background:#fff;border:1px solid var(--ink-faint);border-radius:4px">';
    html += '<button class="app-btn app-btn-outline app-btn-sm" onclick="copyText(\'' + recId + '-name\', this)">Copy</button>';
    html += '</div>';
    html += '<div style="font-size:11px;color:var(--ink-soft);margin-top:8px">Value / Content:</div>';
    html += '<div style="display:flex;gap:6px;align-items:flex-start;margin-top:2px">';
    html += '<textarea readonly id="' + recId + '-val" rows="2" style="flex:1;font-family:monospace;font-size:11px;padding:6px 8px;background:#fff;border:1px solid var(--ink-faint);border-radius:4px;resize:vertical">' + escapeHtml(recVal) + '</textarea>';
    html += '<button class="app-btn app-btn-outline app-btn-sm" onclick="copyText(\'' + recId + '-val\', this)">Copy</button>';
    html += '</div>';
    if (rec.priority) {
      html += '<div style="font-size:11px;color:var(--ink-soft);margin-top:6px">Priority: <strong>' + escapeHtml(String(rec.priority)) + '</strong></div>';
    }
    html += '</div>';
  }

  wrap.innerHTML = html;

  // Check if all sending records are verified — if so, auto-advance
  const allValid = sending.length > 0 && sending.every(r => (r.valid || '').toLowerCase() === 'valid');
  const notice = document.getElementById('wiz-dns-notice');
  if (allValid) {
    notice.innerHTML = '<strong style="color:#4A7C59">All records verified!</strong> You can continue to the next step.';
    document.getElementById('wiz-step4-continue').style.display = '';
  } else {
    notice.innerHTML = '<div style="color:var(--ink-soft)">Waiting for DNS to propagate. This takes 10 minutes to a few hours. We\'ll check automatically every 30 seconds.</div>';
    document.getElementById('wiz-step4-continue').style.display = 'none';
  }
}

function copyText(elementId, btnEl) {
  const el = document.getElementById(elementId);
  if (!el) return;
  el.select();
  try {
    const ok = document.execCommand('copy');
    if (ok) {
      const original = btnEl.textContent;
      btnEl.textContent = 'Copied!';
      setTimeout(() => { btnEl.textContent = original; }, 1200);
    }
  } catch (err) {
    toast('Copy failed — select the text manually', true);
  }
  // Blur to deselect
  window.getSelection().removeAllRanges();
}

async function wizardCheckVerification(showUserFeedback) {
  const data = await api('/sender.php?action=check_verification', {
    method: 'POST',
    body: JSON.stringify({}),
  });
  if (!data.success) {
    if (showUserFeedback) toast(data.message || 'Could not check verification', true);
    return false;
  }
  if (data.dns_records) wizardState.dnsRecords = data.dns_records;
  if (wizardState.step === 4) renderDnsRecordsTable();
  if (data.verified) {
    stopDnsPolling();
    if (showUserFeedback) toast('All DNS records verified!');
    return true;
  }
  return false;
}

function startDnsPolling() {
  if (wizardState.pollTimer) return; // already running
  wizardState.pollStart = Date.now();

  const tick = async () => {
    // Hard stop after 10 minutes
    if (Date.now() - wizardState.pollStart > 10 * 60 * 1000) {
      stopDnsPolling();
      const pauseEl = document.getElementById('wiz-poll-paused');
      if (pauseEl) pauseEl.style.display = '';
      return;
    }
    // Only poll while still on step 4
    if (wizardState.step !== 4) {
      stopDnsPolling();
      return;
    }
    await wizardCheckVerification(false);
  };

  // First check immediately, then every 30s
  tick();
  wizardState.pollTimer = setInterval(tick, 30000);
}

function stopDnsPolling() {
  if (wizardState.pollTimer) {
    clearInterval(wizardState.pollTimer);
    wizardState.pollTimer = null;
  }
  wizardState.pollStart = null;
}

function wizardResumePolling() {
  const pauseEl = document.getElementById('wiz-poll-paused');
  if (pauseEl) pauseEl.style.display = 'none';
  startDnsPolling();
}

async function wizardManualCheck() {
  const btn = document.getElementById('wiz-manual-check-btn');
  btn.disabled = true;
  btn.textContent = 'Checking…';
  await wizardCheckVerification(true);
  btn.disabled = false;
  btn.textContent = 'Check now';
}

// ── Step 5: Sender profile ───────────────────────────────────

function renderStep5() {
  // If coming from verified state (manage mode), prefill from the snapshot
  const s = wizardState.senderSnapshot;
  if (s) {
    document.getElementById('wiz-from-name').value  = s.from_name || '';
    document.getElementById('wiz-from-email').value = s.from_email || '';
    document.getElementById('wiz-reply-to').value   = s.reply_to_email || '';
    document.getElementById('wiz-address').value    = s.physical_address || '';
  }
  // Show the disconnect option only if we already have a verified setup
  const isVerified = s && s.status === 'verified';
  document.getElementById('wiz-disconnect-wrap').style.display = isVerified ? '' : 'none';

  // Update helper text with the verified domain
  const domainHint = document.getElementById('wiz-step5-domain-hint');
  if (domainHint && wizardState.domain) {
    // Strip the "mail." prefix — users send from the root domain
    const root = wizardState.domain.replace(/^mail\./, '');
    domainHint.textContent = 'Must be at @' + root;
  }
}

async function wizardSaveProfile() {
  const fromName  = document.getElementById('wiz-from-name').value.trim();
  const fromEmail = document.getElementById('wiz-from-email').value.trim();
  const replyTo   = document.getElementById('wiz-reply-to').value.trim();
  const address   = document.getElementById('wiz-address').value.trim();

  const btn = document.getElementById('wiz-step5-save');
  const errEl = document.getElementById('wiz-step5-error');
  errEl.textContent = '';

  if (!fromName)  { errEl.textContent = 'From name is required'; return; }
  if (!fromEmail) { errEl.textContent = 'From email is required'; return; }
  if (!address)   { errEl.textContent = 'Physical address is required by CAN-SPAM'; return; }

  btn.disabled = true;
  btn.textContent = 'Saving…';

  const data = await api('/sender.php?action=save_profile', {
    method: 'POST',
    body: JSON.stringify({
      from_name: fromName,
      from_email: fromEmail,
      reply_to_email: replyTo,
      physical_address: address,
    }),
  });

  btn.disabled = false;
  btn.textContent = 'Save and finish';

  if (!data.success) {
    errEl.textContent = data.message || 'Failed to save';
    return;
  }

  toast(data.message || 'Sender profile saved');
  closeWizardModal();
  renderEmailSystemStatus();
}

async function disconnectMailgun() {
  if (!confirm('Disconnect Mailgun? Your stored API key will be deleted, and your sending domain will be removed from Mailgun. You can reconnect anytime.')) return;
  const data = await api('/sender.php?action=disconnect', { method: 'POST', body: JSON.stringify({}) });
  if (data.success) {
    toast(data.message);
    closeWizardModal();
    renderEmailSystemStatus();
  } else {
    toast(data.message || 'Disconnect failed', true);
  }
}

function wizardShowRegistrarHelp(registrar) {
  const wrap = document.getElementById('wiz-registrar-help');
  const guides = {
    godaddy: '<strong>Adding DNS records in GoDaddy</strong>'
      + '<ol style="padding-left:22px;margin:6px 0">'
      + '<li>Sign into your GoDaddy account.</li>'
      + '<li>Click your name in the top-right, then <strong>My Products</strong>.</li>'
      + '<li>Find your domain and click <strong>DNS</strong>.</li>'
      + '<li>Click <strong>Add</strong> at the bottom of the records list.</li>'
      + '<li>For each record above: pick the Type (TXT, CNAME, or MX), paste the Name and Value, leave TTL at default, and click Save.</li>'
      + '</ol>',
    namecheap: '<strong>Adding DNS records in Namecheap</strong>'
      + '<ol style="padding-left:22px;margin:6px 0">'
      + '<li>Sign into Namecheap.</li>'
      + '<li>Click <strong>Domain List</strong> in the sidebar, then <strong>Manage</strong> next to your domain.</li>'
      + '<li>Click the <strong>Advanced DNS</strong> tab.</li>'
      + '<li>Click <strong>Add New Record</strong> at the bottom.</li>'
      + '<li>For each record: pick the Type, paste the Host (Name) and Value, leave TTL at Automatic, and click the green checkmark to save.</li>'
      + '</ol>',
    cloudflare: '<strong>Adding DNS records in Cloudflare</strong>'
      + '<ol style="padding-left:22px;margin:6px 0">'
      + '<li>Sign into Cloudflare.</li>'
      + '<li>Click your domain.</li>'
      + '<li>Click <strong>DNS</strong> in the left sidebar.</li>'
      + '<li>Click <strong>Add record</strong>.</li>'
      + '<li>For each record above: pick the Type, paste Name and Content (Value), <strong>set Proxy status to DNS only (gray cloud — critical!)</strong>, and click Save.</li>'
      + '</ol>',
    other: '<strong>Other registrars</strong><br>Look for "DNS Management" or "Advanced DNS" in your registrar\'s dashboard. The general pattern is: add a new record, pick the Type (TXT, CNAME, or MX), paste the Name/Host and Value/Content exactly as shown above, leave TTL at the default, and save. If you get stuck, your registrar\'s support chat can usually help within minutes.',
  };
  wrap.innerHTML = guides[registrar] || '';
  wrap.style.display = '';
}

// ── CONTACTS ──────────────────────────────────────────────────
let contactsList  = [];
let contactsLists = [];
let contactsDebounce = null;
let selectedContactIds = new Set();

function debouncedLoadContacts() {
  clearTimeout(contactsDebounce);
  contactsDebounce = setTimeout(loadContacts, 250);
}

async function loadContactsView() {
  await Promise.all([loadContactStats(), loadLists(), loadContacts(), loadSuppressionCount()]);
}

async function loadContactStats() {
  const data = await api('/contacts.php?action=stats');
  if (!data.success) return;
  const s = data.stats || {};
  document.getElementById('stat-total').textContent    = s.total || 0;
  document.getElementById('stat-optedin').textContent  = s.opted_in || 0;
  document.getElementById('stat-unsub').textContent    = s.unsubscribed || 0;
  document.getElementById('stat-bounced').textContent  = s.bounced || 0;
}

async function loadContacts() {
  const search = (document.getElementById('contact-search').value || '').trim();
  const type   = document.getElementById('contact-type-filter').value;
  const status = document.getElementById('contact-status-filter').value;
  const listId = document.getElementById('contact-list-filter').value;

  // Show/hide the "Viewing list: Name" banner
  const banner = document.getElementById('list-filter-banner');
  if (banner) {
    if (listId) {
      const list = contactsLists.find(l => String(l.id) === String(listId));
      if (list) {
        document.getElementById('list-filter-banner-name').textContent = list.name;
        banner.style.display = 'flex';
      } else {
        banner.style.display = 'none';
      }
    } else {
      banner.style.display = 'none';
    }
  }

  const params = new URLSearchParams({ action: 'list' });
  if (search) params.set('q', search);
  if (type)   params.set('type', type);
  if (status) params.set('status', status);
  if (listId) params.set('list_id', listId);

  const data = await api('/contacts.php?' + params.toString());
  const wrap = document.getElementById('contacts-table-wrap');
  const selectAllRow = document.getElementById('select-all-row');

  if (!data.success) {
    wrap.innerHTML = '<div class="empty">Failed to load contacts</div>';
    if (selectAllRow) selectAllRow.style.display = 'none';
    return;
  }

  contactsList = data.contacts || [];
  if (contactsList.length === 0) {
    wrap.innerHTML = '<div class="empty">No contacts match your filters</div>';
    if (selectAllRow) selectAllRow.style.display = 'none';
    updateBulkBar();
    return;
  }

  if (selectAllRow) selectAllRow.style.display = '';
  const selectAll = document.getElementById('select-all-checkbox');
  if (selectAll) selectAll.checked = false;

  const typeLabel = { reader:'Reader', media:'Media', bookseller:'Bookseller', arc_reader:'ARC', other:'Other' };
  const statusBadge = {
    opted_in:     '<span class="badge badge-green">Opted in</span>',
    unconfirmed:  '<span class="badge badge-amber">Unconfirmed</span>',
    unsubscribed: '<span class="badge badge-gray">Unsubscribed</span>',
    bounced:      '<span class="badge badge-red">Bounced</span>',
    complained:   '<span class="badge badge-red">Complained</span>',
  };

  wrap.innerHTML = contactsList.map(c => {
    const name = [c.first_name, c.last_name].filter(Boolean).join(' ') || '—';
    const checked = selectedContactIds.has(c.id) ? 'checked' : '';
    return '<div class="row" style="display:flex;align-items:center;gap:10px">'
      + '<input type="checkbox" ' + checked + ' onchange="toggleContactSelection(' + c.id + ', this.checked)" onclick="event.stopPropagation()">'
      + '<div style="flex:1;cursor:pointer" onclick="openContactModal(' + c.id + ')">'
      + '<div><strong>' + escapeHtml(c.email) + '</strong></div>'
      + '<div class="row-meta">' + escapeHtml(name) + ' · ' + (typeLabel[c.contact_type] || c.contact_type) + '</div>'
      + '</div>'
      + '<div>' + (statusBadge[c.consent_status] || c.consent_status) + '</div>'
      + '</div>';
  }).join('');

  updateBulkBar();
}

async function loadLists() {
  const data = await api('/contacts.php?action=lists');
  if (!data.success) return;
  contactsLists = data.lists || [];

  const filterSel = document.getElementById('contact-list-filter');
  if (filterSel) {
    const currentVal = filterSel.value;
    filterSel.innerHTML = '<option value="">All lists</option>' +
      contactsLists.map(l => '<option value="' + l.id + '">' + escapeHtml(l.name) + ' (' + l.member_count + ')</option>').join('');
    filterSel.value = currentVal;
  }

  const bulkSel = document.getElementById('bulk-list-select');
  if (bulkSel) {
    bulkSel.innerHTML = '<option value="">Pick a list…</option>' +
      contactsLists.map(l => '<option value="' + l.id + '">' + escapeHtml(l.name) + '</option>').join('');
  }

  const impSel = document.getElementById('import-list');
  if (impSel) {
    const cur = impSel.value;
    impSel.innerHTML = '<option value="">— no list —</option>' +
      contactsLists.map(l => '<option value="' + l.id + '">' + escapeHtml(l.name) + '</option>').join('');
    impSel.value = cur;
  }

  const wrap = document.getElementById('lists-wrap');
  if (contactsLists.length === 0) {
    wrap.innerHTML = '<div class="empty">No lists yet — create one to group contacts</div>';
    return;
  }
  wrap.innerHTML = contactsLists.map(l =>
    '<div class="row" data-list-id="' + l.id + '" data-list-name="' + escapeHtml(l.name) + '" style="align-items:center">'
    + '<div class="list-row-body" style="flex:1;cursor:pointer">'
    + '<div><strong>' + escapeHtml(l.name) + '</strong></div>'
    + '<div class="row-meta">' + l.member_count + ' contact(s)' + (l.description ? ' · ' + escapeHtml(l.description) : '') + '</div>'
    + '</div>'
    + '<div style="display:flex;gap:6px">'
    + '<button type="button" class="app-btn app-btn-outline app-btn-sm list-view-btn">View</button>'
    + '<button type="button" class="app-btn app-btn-outline app-btn-sm list-delete-btn">Delete</button>'
    + '</div>'
    + '</div>'
  ).join('');

  // Attach event listeners — more reliable across browsers than inline onclick
  wrap.querySelectorAll('.list-row-body, .list-view-btn').forEach(el => {
    el.addEventListener('click', function(e) {
      e.stopPropagation();
      const row = el.closest('[data-list-id]');
      if (row) viewListContacts(parseInt(row.dataset.listId, 10));
    });
  });
  wrap.querySelectorAll('.list-delete-btn').forEach(el => {
    el.addEventListener('click', function(e) {
      e.stopPropagation();
      const row = el.closest('[data-list-id]');
      if (row) deleteList(parseInt(row.dataset.listId, 10), row.dataset.listName);
    });
  });
}

// ── Suppressions UI ──────────────────────────────────────────

let suppressionsCache = [];
let suppressionsLoaded = false;

async function loadSuppressionCount() {
  // Load a lightweight version just to get the count
  try {
    const data = await api('/contacts.php?action=suppressions');
    if (data.success) {
      suppressionsCache = data.suppressions || [];
      suppressionsLoaded = true;
      const countEl = document.getElementById('suppression-count');
      if (countEl) {
        const n = suppressionsCache.length;
        countEl.textContent = n === 0 ? '' : (n + ' ' + (n === 1 ? 'email' : 'emails'));
      }
      // If the suppressions panel is already open, refresh it
      const wrap = document.getElementById('suppressions-wrap');
      if (wrap && wrap.style.display !== 'none') {
        renderSuppressions();
      }
    }
  } catch (err) { /* silent */ }
}

function toggleSuppressions() {
  const wrap = document.getElementById('suppressions-wrap');
  const icon = document.getElementById('suppression-toggle-icon');
  if (!wrap) return;
  const isOpen = wrap.style.display !== 'none';
  wrap.style.display = isOpen ? 'none' : 'block';
  if (icon) icon.textContent = isOpen ? '▸' : '▾';
  if (!isOpen) renderSuppressions();
}

function renderSuppressions() {
  const wrap = document.getElementById('suppressions-wrap');
  if (!wrap) return;

  if (suppressionsCache.length === 0) {
    wrap.innerHTML = '<div class="empty">No suppressed emails. When someone unsubscribes, bounces, or reports spam, they\'ll appear here.</div>';
    return;
  }

  const reasonLabels = {
    bounce:      { label: 'Bounced',      badge: 'badge-red'   },
    complaint:   { label: 'Spam report',  badge: 'badge-red'   },
    unsubscribe: { label: 'Unsubscribed', badge: 'badge-amber' },
    manual:      { label: 'Manually added', badge: 'badge-gray'  },
  };

  let html = '';
  html += '<div style="font-size:12px;color:var(--ink-soft);margin-bottom:10px">Removing someone from this list only makes them eligible to be emailed again — they won\'t automatically be re-added to any lists. Use carefully.</div>';
  for (const s of suppressionsCache) {
    const r = reasonLabels[s.reason] || { label: s.reason, badge: 'badge-gray' };
    const name = [s.first_name, s.last_name].filter(Boolean).join(' ');
    html += '<div style="padding:8px 10px;border-bottom:1px solid var(--ink-faint);display:flex;gap:10px;align-items:center;flex-wrap:wrap">';
    html += '<span class="badge ' + r.badge + '">' + escapeHtml(r.label) + '</span>';
    html += '<div style="flex:1;min-width:180px;font-family:monospace;font-size:12px">' + escapeHtml(s.email) + '</div>';
    if (name) html += '<div style="font-size:12px;color:var(--ink-soft)">' + escapeHtml(name) + '</div>';
    html += '<div style="font-size:11px;color:var(--ink-soft);font-family:monospace">' + (s.created_at ? formatDateTime(s.created_at) : '') + '</div>';
    html += '<button class="app-btn app-btn-outline app-btn-sm" onclick="removeSuppression(' + s.id + ',\'' + escapeHtml(s.email).replace(/'/g, "\\'") + '\',\'' + s.reason + '\')">Remove</button>';
    html += '</div>';
  }
  wrap.innerHTML = html;
}

async function removeSuppression(id, email, reason) {
  let prompt;
  if (reason === 'unsubscribe') {
    prompt = 'Remove ' + email + ' from the suppression list?\n\n'
           + 'This allows them to receive campaigns again. Only do this if they specifically asked to be re-subscribed.\n\n'
           + 'Click OK to remove the suppression AND mark the contact as opted-in.\n'
           + 'Click Cancel to keep them suppressed.';
  } else if (reason === 'bounce') {
    prompt = 'Remove ' + email + ' from the suppression list?\n\n'
           + 'They\'re on this list because a previous send to this address bounced. Removing suppression means future sends will try again.\n\n'
           + 'Only do this if you know the email address works now.';
  } else if (reason === 'complaint') {
    prompt = 'Remove ' + email + ' from the suppression list?\n\n'
           + 'They\'re on this list because they marked a previous email as spam. Sending to them again could damage your sender reputation.\n\n'
           + 'Strongly not recommended unless they explicitly asked to be re-added.';
  } else {
    prompt = 'Remove ' + email + ' from the suppression list?';
  }

  const restoreContact = reason === 'unsubscribe';

  if (!confirm(prompt)) return;

  const data = await api('/contacts.php?action=remove_suppression', {
    method: 'POST',
    body: JSON.stringify({ id, restore_contact: restoreContact }),
  });

  if (data.success) {
    toast(data.message || 'Removed');
    await loadContactsView();
    // Keep the suppressions panel open if it was before
    const wrap = document.getElementById('suppressions-wrap');
    if (wrap) { wrap.style.display = 'block'; renderSuppressions(); }
  } else {
    toast(data.message || 'Failed to remove', true);
  }
}

function viewListContacts(listId) {
  // Set the list filter and reload the contacts table, then scroll to it
  const sel = document.getElementById('contact-list-filter');
  sel.value = listId;
  // Clear other filters for clarity
  document.getElementById('contact-search').value = '';
  document.getElementById('contact-type-filter').value = '';
  document.getElementById('contact-status-filter').value = '';
  loadContacts();
  // Scroll the contacts table into view
  document.getElementById('contacts-table-wrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function clearListFilter() {
  document.getElementById('contact-list-filter').value = '';
  loadContacts();
}

function escapeHtml(s) {
  if (s == null) return '';
  return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));
}

// Reusable email-format check for contact fields (sell sheet, cover letter,
// press release, etc.). Pragmatic pattern: non-empty local part, single @,
// a dotted domain. Not RFC-exhaustive — just catches the typos that would
// make a media/buyer contact line useless ("jane@", "jane.example.com").
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || '').trim());
}

// ── Selection / bulk actions ─────────────────────────────────

function toggleContactSelection(id, checked) {
  if (checked) selectedContactIds.add(id);
  else         selectedContactIds.delete(id);
  updateBulkBar();
}

function toggleSelectAll() {
  const checked = document.getElementById('select-all-checkbox').checked;
  if (checked) contactsList.forEach(c => selectedContactIds.add(c.id));
  else         contactsList.forEach(c => selectedContactIds.delete(c.id));
  loadContacts();
}

function clearSelection() {
  selectedContactIds.clear();
  loadContacts();
}

function updateBulkBar() {
  const bar = document.getElementById('bulk-bar');
  if (!bar) return;
  const count = selectedContactIds.size;
  if (count === 0) {
    bar.style.display = 'none';
  } else {
    bar.style.display = 'flex';
    document.getElementById('bulk-count').textContent = count + ' selected';
  }
}

async function bulkDelete() {
  const ids = Array.from(selectedContactIds);
  if (ids.length === 0) return;
  if (!confirm('Delete ' + ids.length + ' contact(s)? This cannot be undone.')) return;

  const data = await api('/contacts.php?action=bulk_delete', {
    method: 'POST',
    body:   JSON.stringify({ ids }),
  });

  if (data.success) {
    toast(data.message);
    selectedContactIds.clear();
    loadContactsView();
  } else {
    toast(data.message || 'Delete failed', true);
  }
}

// One-click: opt in EVERY contact in the list currently chosen in the list
// filter — server-side, so it covers the whole list, not just the visible page.
async function listBulkOptIn() {
  const sel = document.getElementById('contact-list-filter');
  const listId = sel ? sel.value : '';
  if (!listId) { toast('Pick a list in the "All lists" filter first', true); return; }
  const listName = (sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].text) || 'this list';
  if (!confirm('Mark EVERY contact in "' + listName + '" as Opted in?\n\nOnly do this for people who agreed to hear from you — e.g. your own customers.')) return;

  const data = await api('/contacts.php?action=list_set_status', {
    method: 'POST',
    body:   JSON.stringify({ list_id: Number(listId), status: 'opted_in' }),
  });
  if (data.success) {
    toast(data.message || 'List opted in');
    loadContacts();
  } else {
    toast(data.message || 'Update failed', true);
  }
}

async function bulkChangeStatus() {
  const status = document.getElementById('bulk-status-select').value;
  const ids = Array.from(selectedContactIds);
  if (!status) { toast('Select a status', true); return; }
  if (ids.length === 0) return;

  const data = await api('/contacts.php?action=bulk_status', {
    method: 'POST',
    body:   JSON.stringify({ ids, status }),
  });

  if (data.success) {
    toast(data.message);
    selectedContactIds.clear();
    document.getElementById('bulk-status-select').value = '';
    loadContactsView();
  } else {
    toast(data.message || 'Update failed', true);
  }
}

async function bulkAddToList() {
  const listId = document.getElementById('bulk-list-select').value;
  const ids = Array.from(selectedContactIds);
  if (!listId) { toast('Pick a list first', true); return; }
  if (ids.length === 0) return;

  const data = await api('/contacts.php?action=bulk_add_to_list', {
    method: 'POST',
    body:   JSON.stringify({ ids, list_id: parseInt(listId, 10) }),
  });

  if (data.success) {
    toast(data.message);
    document.getElementById('bulk-list-select').value = '';
    selectedContactIds.clear();
    loadContactsView();  // refresh everything so member counts update
  } else {
    toast(data.message || 'Failed', true);
  }
}

// ── Contact modal ────────────────────────────────────────────

async function openContactModal(id) {
  document.getElementById('c-id').value     = '';
  document.getElementById('c-email').value  = '';
  document.getElementById('c-first').value  = '';
  document.getElementById('c-last').value   = '';
  document.getElementById('c-notes').value  = '';
  document.getElementById('c-type').value   = 'reader';
  document.getElementById('c-status').value = 'opted_in';
  document.getElementById('c-delete-btn').style.display = 'none';
  document.getElementById('c-lists-wrap').style.display = 'none';
  document.getElementById('contact-modal-title').textContent = 'Add contact';

  if (id) {
    const c = contactsList.find(x => x.id == id);
    if (c) {
      document.getElementById('c-id').value     = c.id;
      document.getElementById('c-email').value  = c.email || '';
      document.getElementById('c-first').value  = c.first_name || '';
      document.getElementById('c-last').value   = c.last_name || '';
      document.getElementById('c-notes').value  = c.notes || '';
      document.getElementById('c-type').value   = c.contact_type || 'reader';
      document.getElementById('c-status').value = c.consent_status || 'opted_in';
      document.getElementById('c-delete-btn').style.display = '';
      document.getElementById('contact-modal-title').textContent = 'Edit contact';
      document.getElementById('c-lists-wrap').style.display = '';
      renderListMemberships(c.id);
    }
  }
  document.getElementById('contact-modal').style.display = 'flex';
}

async function renderListMemberships(contactId) {
  const wrap = document.getElementById('c-lists-checkboxes');
  wrap.innerHTML = '<div style="font-size:12px;color:var(--ink-soft)">Loading…</div>';

  const data = await api('/contacts.php?action=get_memberships&contact_id=' + contactId);
  if (!data.success) {
    wrap.innerHTML = '<div style="font-size:12px;color:var(--ink-soft)">Failed to load lists</div>';
    return;
  }

  const lists = data.lists || [];
  if (lists.length === 0) {
    wrap.innerHTML = '<div style="font-size:12px;color:var(--ink-soft)">No lists yet — create a list first to assign contacts to it</div>';
    return;
  }

  wrap.innerHTML = lists.map(l =>
    '<label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px">'
    + '<input type="checkbox" class="c-list-check" value="' + l.id + '"' + (l.is_member ? ' checked' : '') + '>'
    + escapeHtml(l.name)
    + '</label>'
  ).join('');
}

function closeContactModal() {
  document.getElementById('contact-modal').style.display = 'none';
}

async function saveContact() {
  const id       = document.getElementById('c-id').value;
  const payload  = {
    email:          document.getElementById('c-email').value.trim(),
    first_name:     document.getElementById('c-first').value.trim(),
    last_name:      document.getElementById('c-last').value.trim(),
    notes:          document.getElementById('c-notes').value.trim(),
    contact_type:   document.getElementById('c-type').value,
    consent_status: document.getElementById('c-status').value,
  };

  if (!payload.email) {
    toast('Email is required', true);
    return;
  }

  const action = id ? 'update' : 'create';
  if (id) payload.id = parseInt(id, 10);

  const data = await api('/contacts.php?action=' + action, {
    method: 'POST',
    body:   JSON.stringify(payload),
  });

  if (!data.success) {
    toast(data.message || 'Save failed', true);
    return;
  }

  if (id) {
    const savedId = parseInt(id, 10);
    const checkedBoxes = document.querySelectorAll('.c-list-check:checked');
    const listIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value, 10));
    await api('/contacts.php?action=update_memberships', {
      method: 'POST',
      body:   JSON.stringify({ contact_id: savedId, list_ids: listIds }),
    });
  }

  toast(id ? 'Contact updated' : 'Contact added');
  closeContactModal();
  loadContactsView();
}

async function deleteContact() {
  const id = document.getElementById('c-id').value;
  if (!id) return;
  if (!confirm('Delete this contact? This cannot be undone.')) return;

  const data = await api('/contacts.php?action=delete', {
    method: 'POST',
    body:   JSON.stringify({ id: parseInt(id, 10) }),
  });

  if (data.success) {
    toast('Contact deleted');
    closeContactModal();
    loadContactsView();
  } else {
    toast(data.message || 'Delete failed', true);
  }
}

// ── List modal ───────────────────────────────────────────────

function openListModal() {
  document.getElementById('l-name').value = '';
  document.getElementById('l-desc').value = '';
  document.getElementById('list-modal').style.display = 'flex';
}

function closeListModal() {
  document.getElementById('list-modal').style.display = 'none';
}

async function saveList() {
  const name = document.getElementById('l-name').value.trim();
  const desc = document.getElementById('l-desc').value.trim();
  if (!name) {
    toast('List name is required', true);
    return;
  }

  const data = await api('/contacts.php?action=create_list', {
    method: 'POST',
    body:   JSON.stringify({ name, description: desc }),
  });

  if (data.success) {
    toast('List created');
    closeListModal();
    loadLists();
  } else {
    toast(data.message || 'Failed to create list', true);
  }
}

async function deleteList(id, name) {
  if (!confirm('Delete the list "' + name + '"? Contacts in the list will remain, but the list itself will be gone.')) return;

  const data = await api('/contacts.php?action=delete_list', {
    method: 'POST',
    body:   JSON.stringify({ id: parseInt(id, 10) }),
  });

  if (data.success) {
    toast('List deleted');
    loadLists();
  } else {
    toast(data.message || 'Delete failed', true);
  }
}

// ── CSV Export ───────────────────────────────────────────────

function exportContactsCsv() {
  const search = (document.getElementById('contact-search').value || '').trim();
  const type   = document.getElementById('contact-type-filter').value;
  const status = document.getElementById('contact-status-filter').value;
  const listId = document.getElementById('contact-list-filter').value;

  const params = new URLSearchParams({ action: 'export' });
  if (search) params.set('q', search);
  if (type)   params.set('type', type);
  if (status) params.set('status', status);
  if (listId) params.set('list_id', listId);
  const token = localStorage.getItem('auth_token');
  if (token) params.set('token', token);

  window.open(API + '/contacts.php?' + params.toString(), '_blank');
}

// ── CSV Import ───────────────────────────────────────────────

let importFileData = null;
let importFileObject = null;

function openImportModal() {
  document.getElementById('import-step-1').style.display = '';
  document.getElementById('import-step-2').style.display = 'none';
  document.getElementById('import-step-3').style.display = 'none';
  document.getElementById('import-file').value = '';
  document.getElementById('import-cancel-btn').style.display = '';
  document.getElementById('import-submit-btn').style.display = 'none';
  document.getElementById('import-done-btn').style.display = 'none';
  document.getElementById('import-confirm-consent').checked = false;
  document.getElementById('import-source').value = '';
  document.getElementById('import-skip-header').checked = true;
  importFileData = null;
  importFileObject = null;
  document.getElementById('import-modal').style.display = 'flex';
}

function closeImportModal() {
  document.getElementById('import-modal').style.display = 'none';
}

function onImportFileSelected() {
  const fileInput = document.getElementById('import-file');
  const file = fileInput.files[0];
  if (!file) return;

  if (file.size > 1048576) {
    toast('File too large (max 1 MB)', true);
    fileInput.value = '';
    return;
  }

  importFileObject = file;

  const reader = new FileReader();
  reader.onload = function(e) {
    const text = e.target.result;
    importFileData = parseCsvText(text);
    if (importFileData.length === 0) {
      toast('File appears to be empty', true);
      return;
    }
    document.getElementById('import-step-1').style.display = 'none';
    document.getElementById('import-step-2').style.display = '';
    document.getElementById('import-submit-btn').style.display = '';
    populateColumnSelectors();
    refreshImportPreview();
  };
  reader.readAsText(file);
}

function parseCsvText(text) {
  const rows = [];
  let row = [];
  let field = '';
  let inQuotes = false;
  const len = text.length;

  for (let i = 0; i < len; i++) {
    const ch = text[i];
    if (inQuotes) {
      if (ch === '"') {
        if (text[i + 1] === '"') { field += '"'; i++; }
        else { inQuotes = false; }
      } else {
        field += ch;
      }
    } else {
      if (ch === '"') {
        inQuotes = true;
      } else if (ch === ',') {
        row.push(field); field = '';
      } else if (ch === '\n') {
        row.push(field); field = '';
        if (row.some(c => c !== '')) rows.push(row);
        row = [];
      } else if (ch === '\r') {
        // ignore — \n will terminate
      } else {
        field += ch;
      }
    }
  }
  if (field !== '' || row.length > 0) {
    row.push(field);
    if (row.some(c => c !== '')) rows.push(row);
  }
  return rows;
}

function populateColumnSelectors() {
  if (!importFileData || importFileData.length === 0) return;
  const skipHeader = document.getElementById('import-skip-header').checked;
  const headerRow = importFileData[0];
  const numCols = headerRow.length;

  const selectors = ['import-email-col', 'import-first-col', 'import-last-col', 'import-notes-col'];
  const optional = { 'import-email-col': false, 'import-first-col': true, 'import-last-col': true, 'import-notes-col': true };

  selectors.forEach(selId => {
    const sel = document.getElementById(selId);
    let html = '';
    if (optional[selId]) html += '<option value="">— none —</option>';
    for (let i = 0; i < numCols; i++) {
      const label = skipHeader && headerRow[i] ? headerRow[i] : ('Column ' + (i + 1));
      html += '<option value="' + i + '">' + escapeHtml(label) + '</option>';
    }
    sel.innerHTML = html;
  });

  if (skipHeader) {
    for (let i = 0; i < numCols; i++) {
      const h = (headerRow[i] || '').toLowerCase().trim();
      if (h === 'email' || h === 'email address' || h === 'email_address') {
        document.getElementById('import-email-col').value = i;
      }
      if (h === 'first name' || h === 'firstname' || h === 'first_name' || h === 'given name') {
        document.getElementById('import-first-col').value = i;
      }
      if (h === 'last name' || h === 'lastname' || h === 'last_name' || h === 'surname' || h === 'family name') {
        document.getElementById('import-last-col').value = i;
      }
      if (h === 'notes' || h === 'note' || h === 'comments') {
        document.getElementById('import-notes-col').value = i;
      }
    }
  }
}

function refreshImportPreview() {
  if (!importFileData || importFileData.length === 0) return;
  populateColumnSelectors();

  const skipHeader = document.getElementById('import-skip-header').checked;
  const previewStart = skipHeader ? 1 : 0;
  const previewRows = importFileData.slice(previewStart, previewStart + 3);

  const wrap = document.getElementById('import-preview');
  if (previewRows.length === 0) {
    wrap.innerHTML = '<div style="color:var(--ink-soft)">No data rows to preview</div>';
    return;
  }
  wrap.innerHTML = previewRows.map(row =>
    row.map(c => escapeHtml(c || '').substring(0, 40)).join(' | ')
  ).join('<br>');
}

async function submitImport() {
  if (!importFileObject) {
    toast('No file selected', true);
    return;
  }
  if (!document.getElementById('import-confirm-consent').checked) {
    toast('You must confirm you have consent before importing', true);
    return;
  }

  const btn = document.getElementById('import-submit-btn');
  btn.disabled = true;
  btn.textContent = 'Importing…';

  const fd = new FormData();
  fd.append('file', importFileObject);
  fd.append('email_col',       document.getElementById('import-email-col').value);
  fd.append('first_name_col',  document.getElementById('import-first-col').value);
  fd.append('last_name_col',   document.getElementById('import-last-col').value);
  fd.append('notes_col',       document.getElementById('import-notes-col').value);
  fd.append('contact_type',    document.getElementById('import-type').value);
  fd.append('consent_status',  document.getElementById('import-consent').value);
  fd.append('consent_source',  document.getElementById('import-source').value.trim() || 'CSV import');
  fd.append('skip_header',     document.getElementById('import-skip-header').checked ? '1' : '0');
  fd.append('list_id',         document.getElementById('import-list').value || '');
  fd.append('confirm_consent', '1');

  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(API + '/contacts.php?action=import', {
      method: 'POST',
      headers: token ? { 'X-Auth-Token': token } : {},
      body: fd,
    });
    const data = await res.json();

    btn.disabled = false;
    btn.textContent = 'Import contacts';

    if (!data.success) {
      toast(data.message || 'Import failed', true);
      return;
    }

    document.getElementById('import-step-2').style.display = 'none';
    document.getElementById('import-step-3').style.display = '';
    document.getElementById('import-submit-btn').style.display = 'none';
    document.getElementById('import-cancel-btn').style.display = 'none';
    document.getElementById('import-done-btn').style.display = '';

    let html = '<div style="padding:12px;background:#F0F7E8;border-left:3px solid #4A7C59;border-radius:4px;margin-bottom:12px">';
    html += '<strong>Import complete</strong><br>';
    html += '<div style="margin-top:6px">';
    html += '<span style="color:#4A7C59"><strong>' + data.imported + '</strong> imported</span>';
    if (data.linked)   html += ' · <span style="color:#4A7C59"><strong>' + data.linked + '</strong> existing added to this list</span>';
    if (data.skipped)  html += ' · <span style="color:var(--ink-soft)"><strong>' + data.skipped + '</strong> already in this list</span>';
    if (data.invalid)  html += ' · <span style="color:#B94141"><strong>' + data.invalid + '</strong> invalid</span>';
    html += '</div></div>';

    if (data.errors && data.errors.length > 0) {
      html += '<div style="font-size:12px;color:var(--ink-soft);margin-bottom:4px">Issues:</div>';
      html += '<ul style="font-size:12px;margin:0;padding-left:20px">';
      data.errors.forEach(e => html += '<li>' + escapeHtml(e) + '</li>');
      html += '</ul>';
    }

    document.getElementById('import-results').innerHTML = html;
    loadContactsView();
  } catch (err) {
    btn.disabled = false;
    btn.textContent = 'Import contacts';
    toast('Import failed: ' + err.message, true);
  }
}

// ── ACCOUNT ───────────────────────────────────────────────────
async function saveProfile() {
  const data = await api('/auth.php?action=update_profile', {
    method: 'POST',
    body: JSON.stringify({
      pen_name: document.getElementById('acc-pen').value.trim(),
      website:  document.getElementById('acc-website').value.trim(),
    }),
  });
  toast(data.success ? 'Profile saved' : data.message, !data.success);
}

// Change password from Account → Profile. The "Update password" button has
// called this since launch, but the function never existed — clicking it threw
// a silent ReferenceError and nothing happened (caught in walkthrough
// 2026-06-12). Backend action 'change_password' was already in place.
async function changePassword() {
  const cur  = document.getElementById('pwd-current');
  const nw   = document.getElementById('pwd-new');
  const conf = document.getElementById('pwd-confirm');
  if (!cur || !nw || !conf) return;

  const oldPassword = cur.value;
  const newPassword = nw.value;

  if (!oldPassword)            { toast('Enter your current password', true); return; }
  if (newPassword.length < 8)  { toast('New password must be at least 8 characters', true); return; }
  if (newPassword !== conf.value) { toast('New passwords don\'t match', true); return; }

  const data = await api('/auth.php?action=change_password', {
    method: 'POST',
    body: JSON.stringify({ old_password: oldPassword, new_password: newPassword }),
  });

  if (data.success) {
    toast('Password updated');
    cur.value = ''; nw.value = ''; conf.value = '';
  } else {
    toast(data.message || 'Could not update password', true);
  }
}

// Reveal/hide toggle for password fields. Wired to a small "show" link next
// to each field (added to the change-password and sign-in forms). Generic so
// it works on any <input type=password> by id.
function togglePasswordField(inputId, linkEl) {
  const el = document.getElementById(inputId);
  if (!el) return;
  const show = el.type === 'password';
  el.type = show ? 'text' : 'password';
  if (linkEl) linkEl.textContent = show ? 'Hide' : 'Show';
}

async function saveEmailPrefs() {
  const cb = document.getElementById('acc-email-optout');
  if (!cb) return;
  const data = await api('/auth.php?action=update_email_prefs', {
    method: 'POST',
    body: JSON.stringify({ system_email_opt_out: cb.checked ? 1 : 0 }),
  });
  if (data.success) {
    if (currentUser) currentUser.system_email_opt_out = cb.checked ? 1 : 0;
    toast(data.message || 'Saved');
  } else {
    cb.checked = !cb.checked;
    toast(data.message || 'Could not save', true);
  }
}

// ── NAVIGATION ────────────────────────────────────────────────
function toggleSidebar() {
  const open = document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebar-backdrop').classList.toggle('visible', open);
}

function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-backdrop').classList.remove('visible');
}

function navigate(viewId) {
  closeSidebar();
  document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  const item = document.querySelector('[data-view="' + viewId + '"]');
  const view = document.getElementById('view-' + viewId);
  if (item) item.classList.add('active');
  if (view) view.classList.add('active');
  document.getElementById('main').scrollTop = 0;
  if (viewId === 'connections') { refreshStatus(); loadConnectionsHub(); }
  if (viewId === 'social')      loadPostComposerPlatforms();
  if (viewId === 'books') loadBooks();
  if (viewId === 'sales') { loadShopifyStatus(); loadWooStatus(); }
  if (viewId === 'contacts') loadContactsView();
  if (viewId === 'email') loadEmailView();
  if (viewId === 'education') {
    renderEducation();
    document.getElementById('edu-library').style.display = 'block';
    document.getElementById('edu-lesson').classList.remove('active');
  }
  if (viewId === 'wordpress') {
    renderWordPress();
    showWpTab('learn');
  }
  if (viewId === 'ads') initAdsView();
  if (viewId === 'press') initPressView();
  if (viewId === 'cover-letter') initCoverLetterView();
  if (viewId === 'sell-sheet') initSellSheetView();
  if (viewId === 'kdp-keywords') initKdpKeywordsView();
  if (viewId === 'ebook-convert') initEbookConvertView();
  if (viewId === 'kdp-aplus') initKdpAplusView();
  if (viewId === 'author-bio') initAuthorBioView();
  if (viewId === 'kdp-promos') initKdpPromosView();
  if (viewId === 'rank-logger') initRankLoggerView();
  if (viewId === 'events') initEventsView();
  if (viewId === 'videos' || viewId === 'gv-cover' || viewId === 'gv-social' ||
      viewId === 'gv-quote' || viewId === 'gv-event' || viewId === 'gv-youtube' ||
      viewId === 'gv-trailer' || viewId === 'gv-trailer-video' || viewId === 'gv-slideshow') {
    // Keep the parent "Graphics & Video" sidebar item highlighted on every sub-page.
    const parentItem = document.querySelector('[data-view="videos"]');
    if (parentItem) parentItem.classList.add('active');
    initGraphicsView();
    if (viewId === 'gv-trailer-video') { tvResetView(); if (typeof tvGoStep === 'function') tvGoStep(1); }
    if (viewId === 'gv-slideshow') initSlideshowView();
    // Demo-only "Post-ready for: …" strips (hidden for real accounts).
    if (viewId === 'gv-social')        _renderDemoPlatformStrip('demo-strip-social',  'image');
    if (viewId === 'gv-quote')         _renderDemoPlatformStrip('demo-strip-quote',   'image');
    if (viewId === 'gv-trailer-video') _renderDemoPlatformStrip('demo-strip-trailer', 'video');
  }
  if (viewId === 'admin-users') loadAdminUsers();
  if (viewId === 'admin-groups') loadAdminGroups();
  if (viewId === 'admin-usage') loadAdminUsage();
  if (viewId === 'admin-chatlog')   loadAdminChatlog();
  if (viewId === 'admin-overrides') loadAdminOverrides();
  if (viewId === 'print-quote') initPrintQuoteView();
  if (viewId === 'dashboard')  { loadProgressGrid(); loadGamePlan(); loadPlanUsage(); }
}

// Dashboard "Your plan usage" card — AI budget, images, and trailers used
// this billing period. Fills the gap a Starter tester flagged (2026-06-12):
// the only usage meters were the sidebar AI bar (hidden until first
// generation) and a graphics-page image counter — nothing consolidated, and
// nothing on the dashboard. Admins see "unlimited" framing instead of bars.
async function loadPlanUsage() {
  const card = document.getElementById('plan-usage-card');
  if (!card) return;
  let data;
  try { data = await api('/usage_status.php'); } catch (e) { return; }
  if (!data || !data.success) return;

  const rows = document.getElementById('plan-usage-rows');
  const meta = document.getElementById('plan-usage-meta');
  const up   = document.getElementById('plan-usage-upgrade');

  meta.textContent = (data.plan_display || data.plan || '') +
    (data.period_resets ? ' · resets ' + _fmtUsageDate(data.period_resets) : '');

  // One metric → one bar (or an "Unlimited" pill for admins / 0-limit = unmetered).
  const metric = (label, used, limit, unit) => {
    let inner;
    if (data.is_admin) {
      inner = '<div class="pu-line"><strong>Unlimited</strong> <span style="color:var(--ink-soft)">(admin)</span></div>';
    } else if (!limit) {
      inner = '<div class="pu-line"><strong>Included</strong></div>';
    } else {
      const pct = Math.min(100, Math.round((used / limit) * 100));
      const cls = pct >= 90 ? ' danger' : (pct >= 70 ? ' warn' : '');
      inner = '<div class="pu-line"><strong>' + used + '</strong> of ' + limit + ' ' + unit + '</div>' +
        '<div class="pu-track"><div class="pu-fill' + cls + '" style="width:' + pct + '%"></div></div>';
    }
    return '<div class="pu-metric"><div class="pu-label">' + label + '</div>' + inner + '</div>';
  };

  // AI budget is tenths-of-a-cent → dollars.
  const aiUsed  = (data.ai.used_tenths_cent / 1000);
  const aiCap   = (data.ai.cap_tenths_cent  / 1000);
  let aiInner;
  if (data.is_admin) {
    aiInner = '<div class="pu-line"><strong>Unlimited</strong> <span style="color:var(--ink-soft)">(admin)</span></div>';
  } else if (!data.ai.cap_tenths_cent) {
    aiInner = '<div class="pu-line"><strong>Included</strong></div>';
  } else {
    const pct = Math.min(100, Math.round((data.ai.used_tenths_cent / data.ai.cap_tenths_cent) * 100));
    const cls = pct >= 90 ? ' danger' : (pct >= 70 ? ' warn' : '');
    aiInner = '<div class="pu-line"><strong>' + pct + '%</strong> used</div>' +
      '<div class="pu-track"><div class="pu-fill' + cls + '" style="width:' + pct + '%"></div></div>';
  }
  const aiMetric = '<div class="pu-metric"><div class="pu-label">AI generations</div>' + aiInner + '</div>';

  rows.innerHTML = aiMetric +
    metric('AI images', data.images.count, data.images.limit, 'this month') +
    metric('Trailer videos', data.trailers.count, data.trailers.limit, 'this month');

  // Upgrade nudge when any metered resource is ≥ 80% used (non-admin only).
  let near = false;
  if (!data.is_admin) {
    if (data.ai.cap_tenths_cent && data.ai.used_tenths_cent / data.ai.cap_tenths_cent >= 0.8) near = true;
    if (data.images.limit && data.images.count / data.images.limit >= 0.8) near = true;
    if (data.trailers.limit && data.trailers.count / data.trailers.limit >= 0.8) near = true;
  }
  up.style.display = near ? 'block' : 'none';

  card.style.display = 'block';
}

function _fmtUsageDate(ymd) {
  // 'YYYY-MM-DD' → 'Mon D' without timezone drift.
  const p = String(ymd).split('-');
  if (p.length !== 3) return ymd;
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return months[parseInt(p[1],10) - 1] + ' ' + parseInt(p[2],10);
}

document.querySelectorAll('.nav-item').forEach(item => {
  // Skip items without data-view (external links handle themselves via inline onclick).
  if (!item.dataset.view) return;
  item.addEventListener('click', () => navigate(item.dataset.view));
});

// ── AUTHOR'S GAME PLAN PANEL ──────────────────────────────────
// Top-of-dashboard recommendation widget. Fetches the user's top
// prioritized open items from /api/game_plan.php and renders them
// with Go / Not-for-me actions. Re-fetches after every skip so the
// list always reflects current state. In demo mode, shows the
// foundation items as if pre-completed and adds a one-line note.
let _gamePlanState = null;

async function loadGamePlan() {
  const panel = document.getElementById('game-plan-panel');
  if (!panel) return;
  try {
    const data = await api('/game_plan.php?limit=5');
    if (!data || !data.success) {
      panel.style.display = 'none';
      return;
    }
    _gamePlanState = data;
    renderGamePlan(data);
  } catch (e) {
    panel.style.display = 'none';
  }
}

function renderGamePlan(data) {
  const panel = document.getElementById('game-plan-panel');
  const list  = document.getElementById('game-plan-list');
  const prog  = document.getElementById('game-plan-progress');
  const demoNote = document.getElementById('game-plan-demo-note');
  if (!panel || !list) return;

  const isDemo = !!(currentUser && (currentUser.is_demo == 1 || currentUser.is_demo === true));
  if (demoNote) demoNote.style.display = isDemo ? '' : 'none';

  if (prog) {
    const done = data.completed_count || 0;
    const tot  = data.total_count || 0;
    prog.textContent = tot > 0 ? (done + ' of ' + tot + ' complete') : '';
  }

  panel.style.display = '';

  const items = Array.isArray(data.items) ? data.items : [];
  if (items.length === 0) {
    list.innerHTML = '<div class="game-plan-empty">Nice work — you\'ve covered everything on the current game plan. Check back as new tools roll in.</div>';
    return;
  }

  let html = '';
  items.forEach(it => {
    const view    = it.view ? escapeHtml(it.view) : '';
    const url     = it.url  ? escapeHtml(it.url)  : '';
    const label   = escapeHtml(it.label || it.key);
    const key     = escapeHtml(it.key);
    const isSelf  = it.source === 'self';
    const goLabel = isSelf ? 'Set one up &rarr;' : 'Go &rarr;';

    let actions = '';
    if (isSelf) {
      actions += '<button type="button" class="gp-have"'
              +    ' data-key="' + key + '"'
              +    ' onclick="onGamePlanHaveOne(this)"'
              +    ' title="Tick this off — you already have an account">I have one</button>';
    }
    actions += '<button type="button" class="gp-go"'
            +    ' data-view="' + view + '"'
            +    ' data-url="' + url + '"'
            +    ' data-source="' + escapeHtml(it.source || 'auto') + '"'
            +    ' onclick="onGamePlanGo(this)">' + goLabel + '</button>';
    actions += '<button type="button" class="gp-skip"'
            +    ' data-key="' + key + '"'
            +    ' onclick="onGamePlanDefer(this)" title="Push this to the bottom of your game plan">Maybe later</button>';

    html += '<div class="game-plan-item">'
         +    '<span class="gp-check" aria-hidden="true"></span>'
         +    '<span class="gp-label">' + label + '</span>'
         +    '<span class="gp-actions">' + actions + '</span>'
         +  '</div>';
  });
  list.innerHTML = html;
}

// Toggle a self-check item as done from the Game Plan panel.
// Hits the existing /api/progress.php self-check endpoint so the
// Marketing Progress Grid below stays in sync automatically.
async function onGamePlanHaveOne(btn) {
  const key = btn.getAttribute('data-key');
  if (!key) return;
  if (currentUser && (currentUser.is_demo == 1 || currentUser.is_demo === true)) {
    toast('Sign up to track your own accounts');
    return;
  }
  const row = btn.closest('.game-plan-item');
  if (row) row.style.opacity = '0.4';
  try {
    const res = await api('/progress.php', {
      method: 'POST',
      body: JSON.stringify({ item_key: key, checked: true }),
    });
    if (!res || !res.success) throw new Error(res && res.message);
    toast('Marked as done');
    loadGamePlan();
    if (typeof loadProgressGrid === 'function') loadProgressGrid();
  } catch (e) {
    if (row) row.style.opacity = '';
    toast('Could not save — try again', true);
  }
}

function onGamePlanGo(btn) {
  const view   = btn.getAttribute('data-view');
  const url    = btn.getAttribute('data-url');
  const source = btn.getAttribute('data-source');
  // Self-check items live outside the portal — open the external
  // signup/login page in a new tab. The author ticks "I have one"
  // when they come back.
  if (source === 'self' && url) {
    window.open(url, '_blank', 'noopener');
    return;
  }
  if (view) { navigate(view); return; }
  if (url)  { window.open(url, '_blank', 'noopener'); }
}

// "Maybe later" — push the item to the bottom of the rotation.
// It still appears, just only after almost everything else is
// done. No explicit restore needed; items resurface naturally.
async function onGamePlanDefer(btn) {
  const key = btn.getAttribute('data-key');
  if (!key) return;
  if (currentUser && (currentUser.is_demo == 1 || currentUser.is_demo === true)) {
    toast('Sign up to customize your own game plan');
    return;
  }
  const row = btn.closest('.game-plan-item');
  if (row) row.style.opacity = '0.4';
  try {
    const res = await api('/game_plan.php', {
      method: 'POST',
      body: JSON.stringify({ action: 'defer', item_key: key }),
    });
    if (!res || !res.success) throw new Error(res && res.message);
    loadGamePlan();
  } catch (e) {
    if (row) row.style.opacity = '';
    toast('Could not save — try again', true);
  }
}

// ── MARKETING PROGRESS GRID ───────────────────────────────────
let _progressState = null;

async function loadProgressGrid() {
  const host = document.getElementById('progress-grid');
  if (!host) return;
  try {
    const data = await api('/progress.php');
    if (!data || !data.success) return;
    _progressState = data;
    renderProgressGrid(data);
  } catch (e) {
    // Silent — dashboard works without the grid if the endpoint isn't reachable yet.
  }
}

function renderProgressGrid(data) {
  const host = document.getElementById('progress-grid');
  if (!host) return;

  const fill  = document.getElementById('progress-bar-fill');
  const label = document.getElementById('progress-summary-label');
  if (fill)  fill.style.width = (data.stats.percent || 0) + '%';
  if (label) label.innerHTML = '<strong>' + data.stats.done + '</strong> of <strong>' + data.stats.total + '</strong> complete';

  const checkSvg = '<svg viewBox="0 0 14 14" aria-hidden="true"><polyline points="2,7 6,11 12,3"/></svg>';

  let html = '';
  data.categories.forEach(cat => {
    let catDone = 0;
    cat.items.forEach(it => { if (it.done) catDone++; });

    html += '<div class="progress-cat">';
    html += '<div class="progress-cat-title">' + escapeHtml(cat.label)
         +  '<span class="progress-cat-count"><strong>' + catDone + '</strong> / ' + cat.items.length + '</span>'
         +  '</div>';
    html += '<div class="progress-items">';
    cat.items.forEach(it => {
      const cls = 'progress-item' + (it.done ? ' done' : '');
      const typeLabel = it.type === 'setup' ? 'Setup' : 'Use';
      const selfBadge = it.source === 'self' ? '<span class="progress-self-badge">Self-check</span>' : '';
      html += '<button type="button" class="' + cls + '"'
           +  ' data-key="' + escapeHtml(it.key) + '"'
           +  ' data-view="' + escapeHtml(it.view || '') + '"'
           +  ' data-url="' + escapeHtml(it.url || '') + '"'
           +  ' data-source="' + escapeHtml(it.source) + '"'
           +  ' data-done="' + (it.done ? '1' : '0') + '"'
           +  ' onclick="onProgressItemClick(this, event)">'
           +  '<span class="progress-check">' + checkSvg + '</span>'
           +  '<span class="progress-label">' + escapeHtml(it.label) + selfBadge + '</span>'
           +  '<span class="progress-type">' + typeLabel + '</span>'
           +  '</button>';
    });
    html += '</div></div>';
  });

  host.innerHTML = html;
}

function onProgressItemClick(btn, evt) {
  const key    = btn.getAttribute('data-key');
  const view   = btn.getAttribute('data-view');
  const url    = btn.getAttribute('data-url');
  const source = btn.getAttribute('data-source');
  const done   = btn.getAttribute('data-done') === '1';

  // Self-check items toggle on click; if it's currently unchecked and has an
  // external URL, also open the URL in a new tab so the user can go set up
  // the account they're about to mark as done.
  if (source === 'self') {
    const next = !done;
    toggleSelfCheck(key, next, btn);
    if (next && url) {
      window.open(url, '_blank', 'noopener');
    }
    return;
  }

  // Auto items: navigate to the relevant in-app view.
  if (view) navigate(view);
}

async function toggleSelfCheck(itemKey, checked, btn) {
  // Optimistic UI flip.
  if (btn) {
    btn.classList.toggle('done', checked);
    btn.setAttribute('data-done', checked ? '1' : '0');
  }
  try {
    const data = await api('/progress.php', {
      method: 'POST',
      body: JSON.stringify({ item_key: itemKey, checked: checked }),
    });
    if (!data.success) {
      // Roll back on failure.
      if (btn) {
        btn.classList.toggle('done', !checked);
        btn.setAttribute('data-done', !checked ? '1' : '0');
      }
      toast(data.message || 'Could not save', true);
      return;
    }
    // Refresh roll-up stats.
    loadProgressGrid();
  } catch (e) {
    if (btn) {
      btn.classList.toggle('done', !checked);
      btn.setAttribute('data-done', !checked ? '1' : '0');
    }
  }
}

// ── TOAST ─────────────────────────────────────────────────────
function toast(msg, isError = false) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'show' + (isError ? ' error' : '');
  setTimeout(() => el.className = '', 3200);
}

// ══════════════════════════════════════
// EDUCATION SYSTEM
// ══════════════════════════════════════
const LESSONS = [
  { id:'portal-quickstart', title:'Portal quick start', desc:'A 5-minute walkthrough of every section and how they connect.', icon:'📖', level:'beginner', time:'5 min', body:`<p>Welcome to Elite Publishing. This guide walks you through every section so you know exactly where to go.</p><h2>The sidebar is your map</h2><ul><li><strong>Your books</strong> — add and manage your titles, covers, and metadata</li><li><strong>Campaigns</strong> — create and publish all your marketing content</li><li><strong>Commerce</strong> — connect sales channels, distribution, and print partners</li><li><strong>Tools</strong> — ads, contacts, analytics, connections, and your account</li></ul><h2>The core workflow</h2><ol><li><strong>Create</strong> — write your content inside the portal, with AI help if you want it</li><li><strong>Publish</strong> — choose your destinations and send</li><li><strong>Track</strong> — watch your results from the analytics dashboard</li></ol><div class="lesson-tip"><strong>Start here:</strong> Add your book under My Books, then connect your social platforms under Connections. Everything else builds on those two steps.</div>` },
  { id:'first-social-post', title:'Your first social post', desc:'Write, schedule, and publish to multiple platforms in under 2 minutes.', icon:'✉️', level:'beginner', time:'3 min', body:`<p>The Social Posts section is where you'll spend the most time.</p><h2>Writing your post</h2><p>Click <strong>Social Posts</strong> in the sidebar. Write your post or click <strong>AI draft</strong> to generate a starting point.</p><div class="lesson-tip"><strong>Character counts:</strong> X caps at 280 characters. The counter below the text box keeps you informed.</div><h2>Post now vs schedule</h2><ul><li><strong>Post now</strong> — publishes immediately to all selected platforms</li><li><strong>Schedule</strong> — type a date and time and the post goes out automatically</li><li><strong>Save draft</strong> — saves without publishing</li></ul><div class="lesson-warning"><strong>Instagram note:</strong> Instagram requires an image. Always include an image URL if posting to Instagram.</div>` },
  { id:'setting-up-book', title:'Setting up your book', desc:'Add your title, cover, ISBN, and connect it to your sales channels.', icon:'📋', level:'beginner', time:'7 min', body:`<p>Before marketing a book, set it up in the portal to connect it to campaigns, sales data, and analytics.</p><h2>Adding your book</h2><p>Go to <strong>My Books</strong> and click <strong>+ Add book</strong>. Fill in your title, subtitle, ISBN, cover image URL, genre, description, and publication date.</p><div class="lesson-tip"><strong>Multiple books:</strong> Add as many books as you like. Use the book selector in the top bar to switch between them.</div>` },
  { id:'make-ebook', title:'Making your eBook', desc:'Turn a manuscript or picture book into a clean, store-ready EPUB — right here, no extra software.', icon:'📗', level:'beginner', time:'6 min', body:`<p>Every online store — Amazon KDP, Apple Books, Kobo, your own shop — wants your book as an <strong>EPUB</strong> file. Making one used to mean buying Mac-only software or paying a formatter. The <strong>eBook Maker</strong> (under <strong>Publishing</strong> in the sidebar) does it for you, right here, from the file you already have. It never changes a word of your writing — only the format.</p><h2>First, pick the right kind of book</h2><p>The tool has two paths, because a novel and a picture book are completely different jobs:</p><ul><li><strong>Novel or text book</strong> — fiction, non-fiction, memoir, poetry. Anything that is mostly words and should <em>reflow</em> to fit any screen and let readers change the font size. Upload a Word file (.docx, .doc), OpenDocument (.odt), rich text (.rtf), or plain text (.txt).</li><li><strong>Picture book or children's book</strong> — artwork with the words already on the page, where every page has to stay exactly as you designed it. Upload your <strong>print-ready interior PDF</strong> — the same file you send to your printer.</li></ul><div class="lesson-tip"><strong>Not sure which?</strong> If a reader would want to make the text bigger, it is a novel or text book. If moving the text would break the artwork, it is a picture book.</div><h2>The novel path — check, then convert</h2><p>Upload your manuscript and the tool <strong>checks its structure first</strong>, then tells you in plain English what it found. When you convert, it does the tedious work for you:</p><ul><li>Finds your chapters and builds a clickable <strong>table of contents</strong></li><li>Cleans up the stray formatting that gets books rejected — odd fonts, extra blank lines, fake indents, straight quotes</li><li>Puts your cover on the front so the book opens on it</li></ul><p>You download a clean EPUB you can list anywhere.</p><h2>The picture-book path — every page locked</h2><p>Upload your print-ready PDF and the tool turns each page into one full-bleed page of a <strong>fixed-layout EPUB</strong> — the same format Amazon, Apple, and Kobo use for children's books. Your artwork and words stay locked together, nothing reflows, and facing pages are paired so tablets show proper two-page spreads. Page 1 becomes your cover, or upload a separate cover if your PDF is interior-only. It takes only a few seconds.</p><div class="lesson-tip"><strong>Why this matters:</strong> run a picture book through an ordinary converter and the text slides right off the art. Fixed-layout is the only format that keeps a picture book looking like a picture book.</div><h2>Preview before you publish</h2><p>Always open your finished EPUB in a free reader before you upload it to a store, so you see exactly what your readers will see. <strong>Thorium Reader</strong> (Windows, Mac, and Linux) is the most accurate; <strong>Apple Books</strong> and <strong>Calibre</strong> work well too.</p><div class="lesson-tip"><strong>Using Apple Books?</strong> Your table of contents is there — tap the <strong>Contents</strong> button at the top of the reader. Books tucks it away instead of showing a side panel, so it can look missing when it is not.</div><h2>Where to sell it</h2><p>The EPUB you download is yours to sell anywhere — you are not locked into one store. There are four kinds of places to put it:</p><ul><li><strong>Major stores you list on yourself</strong> — <a href="https://kdp.amazon.com" target="_blank" rel="noopener">Amazon KDP</a> (the biggest, up to 70% royalties), <a href="https://authors.apple.com" target="_blank" rel="noopener">Apple Books</a>, <a href="https://www.kobowritinglife.com" target="_blank" rel="noopener">Kobo Writing Life</a> (strong in Europe and libraries), <a href="https://press.barnesandnoble.com" target="_blank" rel="noopener">Barnes &amp; Noble Press</a>, and <a href="https://play.google.com/books/publish" target="_blank" rel="noopener">Google Play Books</a>.</li><li><strong>Distributors that put you everywhere at once</strong> — <a href="https://www.draft2digital.com" target="_blank" rel="noopener">Draft2Digital</a> and <a href="https://www.smashwords.com" target="_blank" rel="noopener">Smashwords</a> let you upload once and reach Apple, Kobo, Barnes &amp; Noble, libraries, and more from a single dashboard.</li><li><strong>Your own website</strong> — sell direct with <a href="https://payhip.com" target="_blank" rel="noopener">Payhip</a>, <a href="https://gumroad.com" target="_blank" rel="noopener">Gumroad</a>, or <a href="https://apps.shopify.com/digital-downloads" target="_blank" rel="noopener">Shopify</a>. You keep the largest share of each sale and build your own reader list.</li><li><strong>Delivery and promotion tools</strong> — <a href="https://bookfunnel.com" target="_blank" rel="noopener">BookFunnel</a> and <a href="https://storyoriginapp.com" target="_blank" rel="noopener">StoryOrigin</a> handle reader delivery, review copies, and newsletter swaps.</li></ul><div class="lesson-tip"><strong>Start small.</strong> Most authors begin with Amazon KDP plus one or two others, then expand. When you finish a book in the eBook Maker, the download screen lists all of these with direct links, so you can go straight there.</div>` },
  { id:'connecting-platforms', title:'Connecting your platforms', desc:'Link Facebook, Instagram, and TikTok to start posting from the portal.', icon:'🔗', level:'beginner', time:'10 min', body:`<p>Connecting social platforms is a one-time setup. Once connected, post directly from the portal.</p><h2>How it works</h2><p>Click Connect on any platform and you're taken to that platform's login page to approve access. We never see your social media password.</p><h2>Facebook and Instagram</h2><p>You need a <strong>Facebook Page</strong> (not a personal profile). For Instagram, your account must be a <strong>Business or Creator account</strong> linked to your Facebook Page.</p><div class="lesson-tip"><strong>Tokens expire:</strong> Meta tokens last approximately 60 days. Just click Reconnect when prompted.</div>` },
  { id:'social-media-authors', title:'Social media for authors', desc:'What to post, when to post, and which platforms matter most for books — with real examples.', icon:'📣', level:'intermediate', time:'18 min', body:`<p>Most authors know they should be on social media — but few know what to post. The result is a feed full of "Buy my book!" promos that get ignored, with the occasional sunset photo that has nothing to do with writing. The fix is structure: a small number of post categories you rotate through, each with a clear job.</p><h2>The 80/20 rule</h2><p>80% of posts should provide value or entertainment. 20% can be promotional. Subscribers — and that's what social followers are, even if the platform calls them "followers" — stay because they like reading from you. The buy-my-book post works precisely because it is rare.</p><h2>The five post types you actually need</h2><ul><li><strong>Behind-the-scenes</strong> — writing process, workspace, research detours, edits, the moment you got stuck on a chapter for two weeks</li><li><strong>Character or world insight</strong> — something readers will not find in the book, told as if you are letting them backstage</li><li><strong>Reading recommendation</strong> — one book you read recently, with a sentence about why it landed</li><li><strong>Milestone</strong> — cover reveal, review count, ARC day, launch day, foreign rights deal</li><li><strong>Promo (the rare one)</strong> — your book, a sale, a giveaway. One out of five. Earn the right to it with the others.</li></ul><h2>Examples: five posts in the same author's voice</h2><p>The same indie author from the press release / bio / newsletter lessons, posting across a typical month. Note what stays consistent — voice, specificity, warmth — and what shifts: each category does a different job.</p><h3 style="margin-bottom:8px">1. Behind-the-scenes</h3><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:14px 16px;margin:6px 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Helvetica',sans-serif;font-size:13px;line-height:1.55"><p style="margin:0 0 6px;font-weight:600;font-size:12px;color:#666">[Photo: a writing desk with three opened novels, a coffee mug, and snow visible through the window]</p><p style="margin:0">Week three of book two. Stuck on Chapter 11 — Eleanor needs a reason to go back to the bakery at midnight, and "she just wanted to" is not it. Putting it down for the day. Pulling Hillerman, Penny, and Winspear off the shelf. Sometimes the only way through is to read someone who already solved the same problem you are stuck on.</p></div><div class="lesson-tip"><strong>Why this works:</strong> specific (Chapter 11, three real authors), honest (admits being stuck), and ends with insight rather than a complaint. Readers like the access to the work, not the polish of the work.</div><h3 style="margin-bottom:8px">2. Character or world insight</h3><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:14px 16px;margin:6px 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Helvetica',sans-serif;font-size:13px;line-height:1.55"><p style="margin:0 0 6px;font-weight:600;font-size:12px;color:#666">[Photo: an old vinyl turntable, similar to the one on the cover]</p><p style="margin:0">Eleanor Hartwell collects records that nobody else wants. There is a scene I cut from <em>Smoke and Brick</em> where she pays $1.50 at a German Village garage sale for a 1972 album by a band that played one show, broke up, and was never heard from again. She likes them more for that, not less. It tells you everything about her.</p></div><div class="lesson-tip"><strong>Why this works:</strong> tells readers something they cannot find in the book (a cut scene), reveals character through specifics (1972 album, $1.50), and the last line is the kind of thing only the writer of that character could say. Insider feel without spoilers.</div><h3 style="margin-bottom:8px">3. Reading recommendation</h3><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:14px 16px;margin:6px 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Helvetica',sans-serif;font-size:13px;line-height:1.55"><p style="margin:0 0 6px;font-weight:600;font-size:12px;color:#666">[Photo: the book held against a winter window]</p><p style="margin:0">Anne Hillerman's <em>Lost Birds</em>. I picked it up expecting a quiet read, ate my whole weekend. If you loved her father Tony's Leaphorn and Chee novels, this one earns the family name. Strong sense of place, a generous protagonist, the kind of mystery that lets you breathe between scenes. Ten out of ten if you like cozy with bones.</p></div><div class="lesson-tip"><strong>Why this works:</strong> specific recommendation (one book, one author), the writer's actual experience ("ate my whole weekend"), useful for the reader (what they will get from it), and the last phrase ("cozy with bones") tells anyone in the genre exactly what kind of mystery this is. Recommendations like this build trust — readers come back when your taste matches theirs.</div><h3 style="margin-bottom:8px">4. Milestone</h3><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:14px 16px;margin:6px 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Helvetica',sans-serif;font-size:13px;line-height:1.55"><p style="margin:0 0 6px;font-weight:600;font-size:12px;color:#666">[Photo: a handful of paperbacks stacked on the kitchen counter, addressed envelopes nearby]</p><p style="margin:0">First batch of signed copies going out today. Forty-seven of them. Some are pre-orders, some are reader giveaways, three are going to bookstores who emailed asking if they could carry the book. The third group — bookstores asking — is the one I did not expect, and I keep looking at the envelopes like they cannot quite be real.</p></div><div class="lesson-tip"><strong>Why this works:</strong> a real number (47), a specific surprise (bookstores reaching out), and ends on a moment of feeling rather than a victory lap. Milestone posts go wrong when they read as "look at me" — they go right when they invite the reader into the moment.</div><h3 style="margin-bottom:8px">5. Promo (the one out of five)</h3><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:14px 16px;margin:6px 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Helvetica',sans-serif;font-size:13px;line-height:1.55"><p style="margin:0 0 6px;font-weight:600;font-size:12px;color:#666">[Photo: the book cover on a wooden shelf]</p><p style="margin:0">For Valentine's weekend only — <em>Smoke and Brick</em> is $2.99 on Kindle. Six weeks since launch, 38 reviews, and a small thank-you to readers who have not yet picked it up. It is a debut cozy mystery set in Columbus' German Village, the first in a series. If your weekend reads tend toward Louise Penny or Anne Hillerman, this one might fit.<br><br>Link in bio.</p></div><div class="lesson-tip"><strong>Why this works:</strong> framed as a thank-you (rather than "BUY NOW"), grounded in real proof points (six weeks, 38 reviews), and includes comp authors so the right reader recognizes themselves. The last line tells you it is a sale without sounding like an ad. Promo posts work when they assume the reader is smart, busy, and entitled to a useful pitch.</div><h2>Which platforms to prioritize</h2><ul><li><strong>TikTok (BookTok)</strong> — single biggest discovery engine for indie books. Short video format favors all five post types above except the milestone one (which works better as static).</li><li><strong>Instagram (Bookstagram)</strong> — strong second. Visual-first; posts with the book or workspace in frame outperform pure-text. Reels for video, feed for stills.</li><li><strong>Facebook</strong> — best for readers 35+. Longer text posts work here when they would not on Twitter or Bluesky. Cozy mystery, historical fiction, memoir, and self-help do disproportionately well.</li><li><strong>Bluesky</strong> — smaller audience but growing. Active book community; authentic, conversational tone wins; promo lands hard if overdone.</li><li><strong>YouTube Shorts</strong> — solid for video-first content (BTS, character insight) and gets indexed by Google, so older posts keep finding readers.</li><li><strong>LinkedIn</strong> — only for nonfiction, business, self-help. Fiction posts mostly get ignored here.</li></ul><h2>Cadence — what to actually do</h2><ul><li><strong>3-5 posts per week per primary platform</strong> is the sweet spot. Less and the algorithm forgets you; more and you burn out (or readers do).</li><li><strong>Rotate the five post types</strong> — do not do five behind-the-scenes posts in a row, even if you have the material. Variety holds attention.</li><li><strong>Schedule when creative, post on rhythm.</strong> Use the Social Posts page to write four to seven posts in one sitting and schedule them across the week.</li><li><strong>Reply to comments within 24 hours.</strong> Algorithms reward replies more than likes. The author who answers feels like a real person; the one who does not feels like a brand.</li></ul><h2>What stays out</h2><ul><li><strong>Politics and divisive cultural commentary</strong>, unless your books are explicitly political. Half your audience will mute you for either side. Even a "harmless" hot take loses readers.</li><li><strong>Constant complaints</strong> about the writing life. Honest "stuck on chapter 11" lands; constant "writing is so hard" reads as exhausting.</li><li><strong>Fishing for compliments.</strong> "Should I give up?" posts are visible from space. Readers want to root for you, not save you.</li><li><strong>Anything that breaks the spell of the writer-reader relationship.</strong> Drama with other authors, public callouts, intra-genre infighting — none of it serves the work.</li></ul><div class="lesson-tip"><strong>Use the scheduler:</strong> Write posts when creative, schedule them throughout the week. The Social Posts page in this app handles cross-posting to TikTok, Instagram, Bluesky, LinkedIn, Facebook — write once, post everywhere.</div>` },
  { id:'email-list', title:'Building your email list', desc:'How to grow a reader list from zero, what to send them, and what a good newsletter actually looks like.', icon:'📧', level:'intermediate', time:'14 min', body:`<p>Your email list is your most valuable marketing asset. Unlike social followers, subscribers are yours — no algorithm can take them away. Open rates for author newsletters average 30-40%, compared to a Facebook post reaching about 5% of your followers. The math is not subtle.</p><h2>How to grow your list</h2><ul><li><strong>Free chapter or prequel story</strong> — let readers sample your writing</li><li><strong>Exclusive content</strong> — deleted scenes, character backstories, an annotated chapter</li><li><strong>Early access</strong> — subscribers see covers, pub dates, and launch announcements first</li></ul><div class="lesson-tip"><strong>Add a signup link everywhere:</strong> your website, book's back matter, social media bios, and every email you send.</div><h2>What to send — the structure</h2><p>A monthly newsletter is the cadence most indie authors can sustain. The 80/20 split is the rule of thumb: 80% personal/value content, 20% promo. Subscribers stay because they like reading from you, not because every email asks them to buy something.</p><p>A simple structure that works for fiction and non-fiction alike:</p><ul><li><strong>Personal opener</strong> — one short paragraph in your voice, the way you'd open a letter to a friend</li><li><strong>What you are working on</strong> — a one-paragraph progress note (drafting, editing, between books — be honest)</li><li><strong>Reading recommendation</strong> — one book you read recently and recommend, with a sentence or two on why</li><li><strong>Event or news</strong> — anything coming up: a signing, a podcast appearance, an award shortlist</li><li><strong>One promo item</strong> — your latest release, a sale, a giveaway. Just one. Save the others for next month.</li><li><strong>Close</strong> — a single warm sign-off, no marketing language</li></ul><h2>Example: a complete monthly newsletter</h2><p>The same indie author used in the press release and bio lessons, sending her January newsletter six weeks after launching <em>Smoke and Brick</em>. Read the whole thing as one piece — note that the personal opener sets the tone, the work-in-progress note treats subscribers as insiders, and the promo (only one) is at the bottom where it doesn't dominate.</p><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:0;margin:16px 0;font-family:Georgia,'Times New Roman',serif;font-size:14px;line-height:1.7;color:#1a1a1a;overflow:hidden"><div style="background:#f5f0e6;padding:14px 24px;border-bottom:1px solid #e5dfd2;font-family:-apple-system,BlinkMacSystemFont,sans-serif;font-size:12px;color:#666"><div style="margin-bottom:3px"><strong style="color:#1a1a1a">Subject:</strong> Snow on the cobblestones, and a thank-you</div><div><strong style="color:#1a1a1a">Preheader:</strong> Plus what I'm reading and where to find me in February</div></div><div style="padding:24px 28px"><p style="margin:0 0 14px">Hi friends,</p><p style="margin:0 0 14px">It snowed in Columbus last week — the kind that sticks just long enough to dust the cobblestones in German Village before the salt trucks ruin it. I walked Gable down there at dawn and thought, of course, about Eleanor Hartwell. Six weeks ago I was still nervous about whether anyone would actually read her story. Now there are 247 of you reading this, and 38 reviews on Amazon, and I have heard from three readers who took a German Village walking tour with the book in hand. That last detail — that is what I wanted when I started writing. Thank you.</p><p style="margin:0 0 6px;font-weight:bold">What I am working on</p><p style="margin:0 0 14px">Book two is at 32,000 words. Still in the messy middle, where every cozy mystery writer questions whether she should have just opened a bakery instead. The working title is <em>The Bricklayer's Daughter</em> and Eleanor is spending more time on Mohawk Street this round. No pub date yet — I'll share one when I have a draft I trust.</p><p style="margin:0 0 6px;font-weight:bold">What I am reading</p><p style="margin:0 0 14px">Anne Hillerman's <em>Lost Birds</em>. I picked it up expecting a quiet read and it ate my weekend. If you have ever loved her father Tony's Leaphorn and Chee novels, this one earns the family name. Recommended for anyone who likes a mystery with a strong sense of place.</p><p style="margin:0 0 6px;font-weight:bold">Where to find me in February</p><p style="margin:0 0 14px">Saturday February 14 — reading and signing at The Book Loft of German Village, 2:00 p.m. Bring your copy or pick one up there. They have set aside a stack.</p><p style="margin:0 0 6px;font-weight:bold">One small ask</p><p style="margin:0 0 14px">If you read <em>Smoke and Brick</em> and have not yet left a review on Amazon or Goodreads, this is the part of an indie author's career where reviews matter most — they push the book to readers who would not otherwise find it. Even three sentences is plenty.</p><p style="margin:0 0 14px">Thanks for being here. Walk the cobblestones if you get a chance.</p><p style="margin:0 0 4px">Margaret</p><p style="margin:0;font-size:12px;color:#666;border-top:1px solid #e5dfd2;padding-top:12px;margin-top:18px">Margaret Chen, German Village Mystery series · margaretchenbooks.com<br><span style="color:#999">You are receiving this because you signed up at margaretchenbooks.com. <a href="#" style="color:#999">Unsubscribe</a></span></p></div></div><div class="lesson-tip"><strong>What this newsletter does well:</strong> the subject line is specific and seasonal, not "January Newsletter" or "Updates from Margaret"; the personal opener is a real moment, not generic warmth; the work-in-progress note is honest about where she is in the draft (subscribers love insider candor); the reading recommendation is specific and connects to her audience's tastes; the one promo at the end is a soft ask for reviews, not a buy-now demand; the sign-off is human. The whole thing reads like a letter, not a marketing email.</div><h2>Subject lines that get opened</h2><ul><li><strong>Specific is always better than generic.</strong> "Snow on the cobblestones" beats "January Newsletter" every time.</li><li><strong>A small detail beats a grand promise.</strong> "What I learned in week 3 of edits" outperforms "Big news from the writing desk."</li><li><strong>Curiosity gaps work, but be honest.</strong> If your subject line implies a story, the email had better deliver one. Bait-and-switch subscribers unsubscribe fast.</li><li><strong>Length: 4-7 words is the sweet spot</strong> for mobile inboxes. Anything longer gets truncated.</li></ul><h2>Cadence and timing</h2><ul><li><strong>Monthly is the floor.</strong> Less frequent and subscribers forget you. More frequent (weekly) is fine if you actually have something to say each time — but most authors burn out trying.</li><li><strong>Same day each month if possible.</strong> First Sunday, last Friday — pick one and keep it. Subscribers who like your work will start looking for it.</li><li><strong>Best times for opens (general):</strong> Tuesday-Thursday mornings, 9-11 a.m. local time. But author newsletters often see strong evening opens too — readers who want to wind down with reading.</li><li><strong>Avoid major holidays</strong> for non-promotional emails. Inboxes are full of retail noise.</li></ul><h2>What to track</h2><ul><li><strong>Open rate</strong> — author newsletters typically run 30-40%. Below 20%, your subject lines need work.</li><li><strong>Click-through rate</strong> — 3-5% is good. Higher means your content is genuinely useful.</li><li><strong>Unsubscribe rate</strong> — keep below 0.5% per email. Spikes usually mean you sent too promotional or off-topic.</li><li><strong>Reply rate</strong> — underrated. Even one or two replies per send means you are connecting; reply to every one personally.</li></ul>` },
  { id:'launch-strategy', title:'Book launch strategy', desc:'A week-by-week plan from pre-launch buzz to post-launch momentum.', icon:'🎯', level:'intermediate', time:'20 min', body:`<p>A strong launch doesn't start on launch day — it starts weeks before.</p><h2>8 weeks before</h2><ul><li>Share your cover on social media</li><li>Set up pre-order on Amazon</li><li>Begin recruiting ARC readers</li><li>Draft 4 weeks of social posts and schedule them</li></ul><h2>4 weeks before</h2><ul><li>Send ARCs with a review request</li><li>Reach out to bloggers and podcasters</li><li>Write and distribute your press release</li><li>Set up a Facebook ad campaign</li></ul><div class="lesson-tip"><strong>Pre-orders matter:</strong> They count toward your first-week sales rank on Amazon, which affects discoverability.</div><h2>Launch week</h2><ul><li>Email your list on launch day with a personal message</li><li>Post daily across all platforms</li><li>Respond to every comment and review</li></ul>` },
  { id:'press-releases', title:'Press releases for authors', desc:'When to use one, how to write it, and where to send it — including affordable distribution.', icon:'📰', level:'intermediate', time:'15 min', body:`<p>Press releases get talked about like a magic marketing tool. They aren't. For an indie author, a press release is genuinely useful in specific situations — and a waste of effort the rest of the time. This guide covers when to use one, how to write it well, and where to send it.</p><h2>When a press release actually helps a book</h2><p>Editors and bloggers get hundreds of releases a week. Yours has to compete on news value, not enthusiasm. Send a release when:</p><ul><li><strong>You have a local angle</strong> — your hometown paper covers local authors. This is the single highest-yield use of a press release.</li><li><strong>You won an award or earned recognition</strong> — even a niche genre award is news worth announcing.</li><li><strong>You have a real milestone</strong> — bestseller list, audiobook release, anniversary edition, foreign rights deal.</li><li><strong>Your book speaks to a current event</strong> — a national news story makes your topic suddenly relevant.</li><li><strong>You're holding an event</strong> — a signing, reading, podcast appearance, or library talk.</li></ul><div class="lesson-warning"><strong>Don't send one for:</strong> a routine new release with no other hook, a price drop, or a "now available on Amazon" announcement. These get ignored and can hurt your standing with editors who remember names.</div><h2>The structure</h2><p>The format is rigid for a reason — editors scan dozens of releases a day and want the news at the top.</p><ul><li><strong>FOR IMMEDIATE RELEASE</strong> at the top (or an embargo date if the news isn't public yet)</li><li><strong>Headline</strong> — newsworthy, not promotional. "Local Hilliard Author Releases Debut Mystery Set in Columbus" beats "New Book You'll Love by [Author Name]"</li><li><strong>Dateline</strong> — City, State — Date, leading directly into the lead paragraph</li><li><strong>Lead paragraph</strong> — who, what, where, when, why in two or three sentences. If an editor reads only the lead, they should know the story.</li><li><strong>Body</strong> — supporting details: the book's premise, why it matters now, where it's available, who it's for</li><li><strong>Quote</strong> — one or two sentences from you, in voice, that an editor can drop straight into an article</li><li><strong>Boilerplate</strong> — a 2-3 sentence "About the Author" paragraph at the end</li><li><strong>Contact info</strong> — name, email, phone, website</li><li><strong>### or -30-</strong> centered to mark the end (a journalism convention that signals you know the format)</li></ul><div class="lesson-tip"><strong>One page, 400-500 words.</strong> Anything longer signals an amateur. The generator on this site is tuned to that length.</div><h2>Book-specific angles to weave in</h2><p>A generic press release reads like every other one. These elements give an editor something to actually work with:</p><ul><li><strong>The local hook</strong> — where you live, where your book is set, where you went to school. Local papers thrive on this.</li><li><strong>Comparable titles or authors</strong> — "in the tradition of Louise Penny" tells an editor the genre and audience instantly.</li><li><strong>An offer of review copies</strong> — say so explicitly: "Review copies available in print and digital formats."</li><li><strong>Author availability for interviews</strong> — say so explicitly: "Author available for phone, podcast, or in-person interviews."</li><li><strong>Where it's available</strong> — Amazon, Barnes &amp; Noble, your local indie bookstore, IngramSpark distribution to libraries. Specifics signal a serious release.</li></ul><h2>What a finished press release looks like</h2><p>Here is a complete example, with every element from the structure section in place. Read it as a model — the shape, the rhythm, the level of detail — not as a template to copy.</p><div style="background:#fff;border:1px solid #d4cfc4;border-radius:4px;padding:28px 32px;margin:16px 0;font-family:Georgia,'Times New Roman',serif;font-size:14px;line-height:1.65;color:#1a1a1a"><div style="text-align:center;margin:0 0 22px"><div style="display:inline-flex;align-items:center;justify-content:center;width:110px;height:165px;background:#e8e2d4;border:1px solid #d4cfc4;font-size:10px;color:#999;text-align:center;letter-spacing:1px;border-radius:2px">BOOK<br>COVER</div></div><p style="margin:0 0 18px 0;font-weight:bold;letter-spacing:1px">FOR IMMEDIATE RELEASE</p><p style="margin:0 0 18px 0;font-size:18px;font-weight:bold;line-height:1.3">Local Hilliard Author Releases Debut Mystery Set in Columbus' German Village</p><p style="margin:0 0 14px 0"><em>COLUMBUS, OHIO — May 4, 2026</em> — Hilliard resident Margaret Chen will release her debut mystery novel, <em>Smoke and Brick</em>, on June 15, 2026. Set in Columbus' historic German Village neighborhood, the book follows a retired schoolteacher who uncovers a decades-old secret beneath the cobblestones outside her bakery.</p><p style="margin:0 0 14px 0"><em>Smoke and Brick</em> draws on Chen's twenty years of teaching at Hilliard Davidson High School and her deep familiarity with central Ohio's neighborhoods. The novel will appeal to readers of Louise Penny and Jacqueline Winspear, blending small-community warmth with quietly unsettling discoveries.</p><p style="margin:0 0 14px 0">"I kept walking past beautiful old buildings and wondering what they had seen," Chen said. "German Village has more than a century of stories under its bricks. I just listened for one."</p><p style="margin:0 0 14px 0"><em>Smoke and Brick</em> will be available in paperback and ebook from Amazon, Barnes &amp; Noble, and through IngramSpark distribution to libraries and independent bookstores. A launch event will be held at The Book Loft of German Village on June 21, 2026 at 2:00 p.m.</p><p style="margin:0 0 18px 0">Review copies are available in print and digital formats. Chen is available for phone, podcast, and in-person interviews.</p><p style="margin:0 0 8px 0;font-weight:bold">About the Author</p><table style="border-collapse:collapse;margin:0 0 18px 0"><tr><td style="vertical-align:top;padding-right:14px;width:78px"><div style="width:70px;height:70px;border-radius:50%;background:#e8e2d4;border:1px solid #d4cfc4;display:flex;align-items:center;justify-content:center;font-size:9px;color:#999;text-align:center;letter-spacing:0.5px">AUTHOR<br>PHOTO</div></td><td style="vertical-align:top">Margaret Chen is a retired English teacher and lifelong central Ohioan. She lives in Hilliard with her husband and two rescue dogs. <em>Smoke and Brick</em> is her first novel; a sequel is planned for spring 2027.</td></tr></table><p style="margin:0 0 6px 0;font-weight:bold">Contact</p><p style="margin:0 0 18px 0">Margaret Chen<br>margaret@margaretchenbooks.com<br>(614) 555-0142<br>margaretchenbooks.com</p><p style="margin:0;text-align:center;letter-spacing:2px;font-weight:bold">###</p></div><div class="lesson-tip"><strong>What this example does well:</strong> the headline carries a local hook ("Hilliard," "German Village") and the genre angle ("mystery"); the lead answers the five Ws in two sentences; the comp-author line ("Louise Penny and Jacqueline Winspear") tells an editor the audience instantly; review copies and interview availability are stated explicitly; the boilerplate is short and human. This is a release a books editor at <em>The Columbus Dispatch</em> could act on without follow-up questions. When you download this press release as a PDF or Word file, the app automatically places your <strong>book cover</strong> at the top and your <strong>author photo</strong> beside the bio — exactly as shown above — so what you send already looks like a media kit.</div><h2>Where to distribute it — a tiered approach</h2><p>Wire services aimed at corporate PR charge hundreds to thousands per release. Indie authors should think differently. Use a tiered approach: wide-net distribution for indexing and credibility, plus direct outreach for actual coverage.</p><h2>1. PRLog (recommended for indie authors)</h2><p>PRLog has a free tier and very affordable paid options. Releases get indexed by Google News, picked up by content aggregators, and reach a network of small media outlets and bloggers. It's the best price-to-value option in the indie space — most paid alternatives cost ten times as much for the same audience.</p><p>Visit <a href="https://www.prlog.org" target="_blank" rel="noopener">prlog.org</a>, create a free account, and paste in the press release this app generates for you.</p><div class="lesson-tip"><strong>The honest expectation:</strong> Wire-style distribution rarely produces direct coverage on its own. What it produces is search-engine indexing, the occasional aggregator pickup, and a credibility marker — links you can point to when readers Google your name. That has real value, but it's not the same as press coverage.</div><h2>2. Direct outreach — where coverage actually comes from</h2><p>One personal email to the right editor or podcaster beats a thousand wire blasts. Build a small list of targets:</p><ul><li>Your <strong>local newspaper's books or features editor</strong> — search "[your city] [paper name] books editor" for the name.</li><li>Regional <strong>NPR affiliates</strong> — many run author interview segments, especially for local authors.</li><li><strong>Genre blogs</strong> active in your category — search "[your genre] book blog" and check who's posted in the last 90 days.</li><li><strong>Podcasts</strong> in your space — there are hundreds of book and genre-specific podcasts always looking for guests.</li></ul><p>For each, send a short personal email. Mention why you're contacting them specifically (a book they covered, an episode you liked), then paste the press release below your note. Don't attach files unless asked — many spam filters strip attachments from unknown senders.</p><h2>3. Industry trade publications</h2><p>Bigger lift, longer lead time, but real impact when it lands:</p><ul><li><strong>Library Journal</strong> — for fiction and nonfiction titles libraries might stock</li><li><strong>Publishers Weekly</strong> — paid placement options exist for indie authors</li><li><strong>Kirkus Reviews</strong> — paid review service for indie books, which often leads to coverage</li><li><strong>Genre-specific trade press</strong> — Locus (SF/F), Mystery Scene, Romance Writers Report, and similar</li></ul><h2>What to expect — realistic outcomes</h2><ul><li><strong>Direct sales bump:</strong> usually small. Press releases build credibility and discoverability, not impulse purchases.</li><li><strong>SEO benefit:</strong> wire distribution gives you indexed mentions on dozens of small sites. Useful when readers search your name.</li><li><strong>Coverage:</strong> direct outreach to a relevant editor is far more likely to result in an actual article or interview than any wire service.</li><li><strong>Timing:</strong> measure results two to four weeks out. Wire pickups trickle in, and editors take time to read and respond.</li></ul><h2>Pair the release with a media kit</h2><p>A press release alone is a lonely artifact. A media kit makes it easy for an editor to cover you. The minimum:</p><ul><li>The press release (PDF and plain text)</li><li>High-resolution book cover (300 DPI JPG)</li><li>Author headshot (300 DPI JPG)</li><li>Short bio (50 words)</li><li>Long bio (150 words)</li><li>Two or three suggested interview questions</li></ul><p>Host these in a folder you can share by link — Google Drive or Dropbox works fine. Include the link in every outreach email.</p>` },
  { id:'cover-letters', title:'Cover letters that land an answer', desc:'Querying agents, pitching podcasters, and writing letters editors actually finish reading.', icon:'📄', level:'intermediate', time:'12 min', body:`<p>A cover letter has one job: open a door. Not close a sale, not summarize the whole book, not list every credential. Open a door. The recipient — agent, editor, podcaster, books editor — should finish reading and want the next thing from you.</p><h2>The four parts that always go in</h2><p>Whether you're querying a literary agent or pitching a podcast, the shape is the same:</p><ul><li><strong>The hook</strong> — one or two sentences that earn the reader's attention. Often a personal connection ("I'm querying you because of your work with [Author]"), or a specific reason your project belongs in their inbox today.</li><li><strong>The project</strong> — what the book is, in their language: title, word count, genre, a one-paragraph pitch with comp titles. Not a synopsis.</li><li><strong>Your credentials</strong> — short, specific, and relevant. "Twenty years teaching English" matters; "I love writing" doesn't.</li><li><strong>The close</strong> — what you're asking for and how to follow up. Make the next step easy.</li></ul><h2>What changes by recipient</h2><ul><li><strong>Literary agent</strong> — formal, manuscript-focused. Genre, word count, comp titles, market positioning. Read their agency's submission page first; format details vary.</li><li><strong>Publisher or editor</strong> — similar to an agent query, but more emphasis on the audience and the marketing angle. Editors think about who buys this book.</li><li><strong>Podcast host</strong> — conversational. Reference their show specifically. Pitch yourself by what you can talk about, not just what you wrote.</li><li><strong>Journalist or media contact</strong> — lead with the news angle, not the book. Why does this story belong in their column this week?</li><li><strong>Book club or reading group</strong> — warm and generous. Offer a free virtual visit, discussion questions, or signed copies for a giveaway.</li></ul><h2>Common mistakes that get instant rejection</h2><ul><li><strong>Synopsizing the whole book.</strong> The pitch is one paragraph. Anything longer is a sample chapter in disguise.</li><li><strong>Listing every credential.</strong> Three relevant lines outperform a CV-style paragraph.</li><li><strong>Praising the recipient excessively.</strong> One specific reference to their work earns more goodwill than a paragraph of compliments.</li><li><strong>Hedging.</strong> "I think you might possibly be interested in maybe taking a look" makes a reader skip you. Be direct.</li><li><strong>Form-letter giveaways.</strong> "Dear Agent" or generic praise that could apply to any recipient signals you sent the same letter to twenty people.</li></ul><h2>Example: agent query letter</h2><p>A query for a debut cozy mystery. Note the personal hook in the first line, the tight pitch paragraph with comp titles, and credentials kept to two sentences.</p><div style="background:#fff;border:1px solid #d4cfc4;border-radius:4px;padding:28px 32px;margin:16px 0;font-family:Georgia,'Times New Roman',serif;font-size:14px;line-height:1.65;color:#1a1a1a"><p style="margin:0 0 18px 0">May 4, 2026</p><p style="margin:0 0 18px 0">Jane Smith<br>Folio Literary Management<br>jane.smith@folioliterary.com</p><p style="margin:0 0 14px 0">Dear Ms. Smith,</p><p style="margin:0 0 14px 0">I'm querying you because of your representation of Ellie Alexander, whose cozy mysteries share both setting sensibility and tonal territory with my novel.</p><p style="margin:0 0 14px 0"><em>SMOKE AND BRICK</em> is an 84,000-word cozy mystery set in Columbus' German Village neighborhood. When retired schoolteacher Eleanor Hartwell discovers a 1923 newspaper clipping wedged behind the brick of her new bakery, she begins pulling on a thread her quiet neighborhood would rather she left alone. The novel will appeal to readers of Louise Penny's Three Pines series and Jacqueline Winspear's Maisie Dobbs.</p><p style="margin:0 0 14px 0">I'm a retired English teacher with twenty years at Hilliard Davidson High School. My short fiction has appeared in <em>The Sun</em> and <em>Cincinnati Review</em>, and I host the "Books on Brick" podcast covering Midwestern fiction (3,400 listeners per episode).</p><p style="margin:0 0 14px 0">The full manuscript is available on request. Thank you for your consideration.</p><p style="margin:0 0 4px 0">Sincerely,</p><p style="margin:0">Margaret Chen<br>margaret@margaretchenbooks.com<br>margaretchenbooks.com</p></div><div class="lesson-tip"><strong>What this query does well:</strong> the opening sentence shows the writer has read the agent's list; the project paragraph gives word count, genre, comps, and the central premise without spoiling the ending; credentials are specific and verifiable; the close is direct and easy to act on.</div><h2>Example: podcast pitch</h2><p>The same author, pitching a podcast. Note how different the shape is — shorter, more conversational, and centered on what the author can talk about, not what they wrote.</p><div style="background:#fff;border:1px solid #d4cfc4;border-radius:4px;padding:24px 28px;margin:16px 0;font-family:Georgia,'Times New Roman',serif;font-size:14px;line-height:1.65;color:#1a1a1a"><p style="margin:0 0 6px 0;font-size:13px;color:#666"><strong>Subject:</strong> Pitching for The Indie Author Hour — debut cozy mystery, BookTok angle</p><p style="margin:0 0 14px 0;font-size:13px;color:#666"><strong>To:</strong> sarah@indieauthorhour.com</p><hr style="border:none;border-top:1px solid #e5dfd2;margin:0 0 14px 0"><p style="margin:0 0 14px 0">Hi Sarah,</p><p style="margin:0 0 14px 0">I caught your conversation with Jamie Holland last week about how regional settings drive cozy mystery sales — that framing of "place as character" was exactly the conversation I wish more authors had. Your show is a natural fit for what I'm working on.</p><p style="margin:0 0 14px 0">I'm Margaret Chen, author of the upcoming cozy mystery <em>SMOKE AND BRICK</em> (June 15, 2026), set in Columbus' German Village. The book has been picking up early traction on BookTok thanks to a reader who posted a tour of the real-world locations — that video is at 80,000 views and counting.</p><p style="margin:0 0 8px 0">Three angles I could bring to an episode:</p><ul style="margin:0 0 14px 0;padding-left:24px"><li>How a hyperlocal setting outperforms a generic one in BookTok-driven discovery</li><li>The cozy mystery's quiet evolution toward older protagonists, and why publishers aren't catching up</li><li>What twenty years of teaching English taught me about plot economy</li></ul><p style="margin:0 0 14px 0">I can record on weekday mornings (Eastern), and I'm happy to send a review copy ahead.</p><p style="margin:0 0 4px 0">Thanks for considering it,</p><p style="margin:0">Margaret<br>margaret@margaretchenbooks.com</p></div><div class="lesson-tip"><strong>What this pitch does well:</strong> the host's name and a recent episode are referenced specifically; the pitch leads with traction (the BookTok number) instead of pleasantries; three concrete episode angles let the host see exactly how a conversation would go; recording availability and a review-copy offer make a yes one click away.</div><h2>Length and format</h2><ul><li><strong>Agent and editor queries:</strong> 250-350 words. Shorter is better.</li><li><strong>Podcast and media pitches:</strong> 150-250 words. Almost always sent as the body of an email, not an attachment.</li><li><strong>Single-spaced</strong>, plain text or simple email formatting. No background images, fancy headers, or PDF attachments unless requested.</li><li><strong>Subject lines matter for email pitches:</strong> "Query — SMOKE AND BRICK, cozy mystery, 84,000 words" is better than "Submission" or "A book you'll love."</li></ul><div class="lesson-warning"><strong>One letter, one recipient.</strong> Mass-sending the same query with mail-merged names is a fast way to get blacklisted. Personalize each one — even just the opening sentence and one mid-letter reference.</div>` },
  { id:'sell-sheets', title:'Sell sheets — the one-pager buyers expect', desc:'What goes on it, what stays off, and how to turn one into a stocking decision.', icon:'📊', level:'intermediate', time:'10 min', body:`<p>A sell sheet is the one-page document a bookseller, librarian, or reviewer can glance at and decide whether your book is worth a closer look. It's the most format-rigid document in book marketing — buyers expect specific information in specific places, and missing any of it signals a release that isn't ready for shelves.</p><h2>What it is, and what it isn't</h2><p>A sell sheet <em>is</em> a single-page reference document with all the data a buyer needs to make a stocking, ordering, or review decision: ISBN, price, format, distribution, comp titles, the hook, and the contact. It is not a press release (no quote needed), not a query letter (no narrative pitch), and not a marketing brochure (no excessive design).</p><h2>Who reads it, and what they look for first</h2><ul><li><strong>Booksellers</strong> — first look: ISBN, distribution, returnability, comp titles. They need to know they can order it through a channel they already use, and that the book sells the way comparable books on their shelves sell.</li><li><strong>Librarians</strong> — first look: BISAC categories, audience, reviews from professional outlets, durable format availability. Library budgets favor hardcover and library-binding editions.</li><li><strong>Reviewers and bloggers</strong> — first look: the hook, the comp titles, the publication date, the offer of a review copy.</li><li><strong>Journalists</strong> — first look: the angle. Why does this book belong in a story this month?</li></ul><h2>The standard layout</h2><p>Sell sheets are visual — buyers scan, they don't read. The standard layout has held for decades:</p><ul><li><strong>Top:</strong> book title, subtitle, author name. Big and unmissable.</li><li><strong>Upper left:</strong> the front cover image at high resolution.</li><li><strong>Upper right:</strong> the metadata block — ISBN, format, page count, trim size, retail price, pub date, distribution channel, returnability, BISAC categories, audience.</li><li><strong>Middle:</strong> the hook (one tight paragraph, 60-80 words), then comparable titles.</li><li><strong>Lower middle:</strong> early praise or review pull-quotes (if available), then marketing-plan highlights.</li><li><strong>Bottom:</strong> short author bio and contact line.</li></ul><div class="lesson-tip"><strong>One page. Always one page.</strong> Two-page sell sheets get filed as "review later" and never get reviewed. If you can't fit it on a page, cut.</div><h2>Example: a finished sell sheet</h2><p>This is what a complete sell sheet looks like for the same debut cozy mystery used in the press release lesson. Every block has a job: the metadata makes ordering frictionless, the hook earns the comp-title comparison, and the marketing line shows momentum.</p><div style="background:#fff;border:1px solid #d4cfc4;border-radius:4px;padding:32px;margin:16px 0;font-family:Georgia,'Times New Roman',serif;font-size:13px;line-height:1.55;color:#1a1a1a"><div style="text-align:center;border-bottom:2px solid #1a1a1a;padding-bottom:16px;margin-bottom:20px"><div style="font-size:22px;font-weight:bold;letter-spacing:0.5px">SMOKE AND BRICK</div><div style="font-size:14px;font-style:italic;color:#555;margin-top:2px">A German Village Mystery</div><div style="font-size:14px;margin-top:6px">By Margaret Chen</div></div><div style="display:flex;gap:20px;margin-bottom:20px;flex-wrap:wrap"><div style="flex:0 0 130px;height:195px;background:#e8e2d4;border:1px solid #d4cfc4;display:flex;align-items:center;justify-content:center;font-size:11px;color:#999;text-align:center;letter-spacing:1px">FRONT<br>COVER<br>IMAGE</div><div style="flex:1;min-width:200px;font-size:12px;line-height:1.7"><div><strong>ISBN-13:</strong> 978-1-23456-789-0</div><div><strong>Format:</strong> Trade Paperback</div><div><strong>Pages:</strong> 312</div><div><strong>Trim:</strong> 5.5 × 8.5 in</div><div><strong>Price:</strong> $16.99 USD</div><div><strong>Pub Date:</strong> June 15, 2026</div><div><strong>Distribution:</strong> IngramSpark (returnable)</div><div><strong>BISAC:</strong> FIC022040 — Mystery / Cozy</div><div><strong>Audience:</strong> Adult mystery readers, 35+</div></div></div><div style="margin-bottom:16px"><div style="font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-size:11px;color:#666;margin-bottom:6px">The Hook</div><p style="margin:0">When retired schoolteacher Eleanor Hartwell discovers a 1923 newspaper clipping wedged behind the brick of her new bakery, she begins pulling on a thread her quiet German Village neighborhood would rather she left alone. <em>Smoke and Brick</em> is the first in a planned three-book series set in Columbus' historic neighborhoods.</p></div><div style="margin-bottom:16px"><div style="font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-size:11px;color:#666;margin-bottom:6px">Comparable Titles</div><p style="margin:0">Louise Penny's <em>Still Life</em> · Jacqueline Winspear's <em>Maisie Dobbs</em> · Ellie Alexander's <em>Murder at First Pitch</em></p></div><div style="margin-bottom:16px"><div style="font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-size:11px;color:#666;margin-bottom:6px">Early Praise</div><p style="margin:0 0 6px 0;font-style:italic">"A debut that feels like settling into a familiar chair." — Cincinnati Review</p><p style="margin:0;font-style:italic">"Chen makes German Village a character in its own right." — Midwest Book Review</p></div><div style="margin-bottom:16px"><div style="font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-size:11px;color:#666;margin-bottom:6px">Marketing Plan</div><p style="margin:0">Launch event at The Book Loft of German Village · Goodreads giveaway (100 copies) · regional radio interviews (WOSU, WCBE) · five-week BookTok creator campaign · author website with locations tour.</p></div><div style="margin-bottom:16px"><div style="font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-size:11px;color:#666;margin-bottom:6px">About the Author</div><p style="margin:0">Margaret Chen is a retired English teacher and lifelong central Ohioan. She lives in Hilliard with her husband and two rescue dogs. <em>Smoke and Brick</em> is her first novel.</p></div><div style="border-top:1px solid #d4cfc4;padding-top:12px;font-size:12px;text-align:center;color:#555">Margaret Chen · margaret@margaretchenbooks.com · (614) 555-0142 · margaretchenbooks.com</div></div><div class="lesson-tip"><strong>What this sell sheet does well:</strong> the metadata block is complete (no missing ISBN, no missing returnability) so a buyer can place an order without a follow-up email; the comp titles span both a name brand (Louise Penny) and a peer-level indie (Ellie Alexander), giving the buyer two anchor points at different price tiers; the marketing line shows the author has a real plan, not just hopes.</div><h2>Visual hygiene</h2><ul><li><strong>White space matters.</strong> A crammed sell sheet looks amateur. Generous margins and clear section breaks signal professionalism.</li><li><strong>One typeface, two weights.</strong> Most professional sell sheets use a single serif or sans-serif font in regular and bold. Three or more fonts is a red flag.</li><li><strong>The cover image must be 300 DPI.</strong> A pixelated cover on a sell sheet is the fastest way to get filed as not-ready.</li><li><strong>PDF format only.</strong> Send sell sheets as PDFs, not Word documents. PDFs render the same on every machine and won't reflow on the buyer's screen.</li></ul><h2>How to use it</h2><p>Sell sheets work hardest in three places:</p><ul><li><strong>Pitching local indie bookstores</strong> — attach the PDF when offering a signing or asking about consignment.</li><li><strong>Library acquisitions</strong> — most public library systems have an acquisitions email; the sell sheet is exactly what they want.</li><li><strong>Review queries</strong> — bundled into your media kit, sent alongside the press release.</li></ul>` },
  { id:'author-bios', title:'Writing your author bio', desc:'The two bios you actually need (short and long), what goes in each, and where they live.', icon:'👤', level:'intermediate', time:'8 min', body:`<p>An author bio is the most-reused piece of writing you will ever do. It appears on social profiles, sell sheets, press releases, conference programs, podcast show notes, bookstore staff picks, and the back of every book you publish. Get it right once, refresh it once a year, and stop fighting it.</p><h2>The two bios you actually need</h2><p>Most authors write one bio and try to use it everywhere. That's why most author bios are bad — they're either too long for short slots or too thin for long slots. The fix is to write <strong>two</strong> bios in advance:</p><ul><li><strong>Short bio (~50 words)</strong> — for social media bios, sell sheets, brief intros, podcast guest blurbs, conference name-tag bios, the bottom of a sell sheet. Anywhere you have one paragraph or less.</li><li><strong>Long bio (~150 words)</strong> — for press kits, full author profiles, literary magazine contributor pages, "About the Author" pages on websites, conference panels, school visits.</li></ul><h2>What goes in (and what stays out)</h2><p>Both bios need:</p><ul><li><strong>What you write</strong> — genre or category, current book, series if any</li><li><strong>One thing that makes you specifically you</strong> — your day job (or former day job), where you live, an unusual personal angle that connects to your work</li><li><strong>Forward motion</strong> — if you're working on something next, say so. "At work on the second book in the series" beats silence.</li></ul><p>The long bio adds:</p><ul><li><strong>Credentials that matter to readers</strong> — short fiction credits, awards, podcast or column work, prior books</li><li><strong>One concrete biographical detail</strong> — children, pets, hobbies, where you grew up. Not a list, just one or two warm specifics.</li></ul><div class="lesson-warning"><strong>Stays out of both:</strong> day-job titles unrelated to your writing, every credential you have ever earned, your family tree, why you decided to write, your love of coffee or wine. These signal "amateur indie author" louder than anything else on a sell sheet.</div><h2>Example: short bio (50 words)</h2><p>The short bio for the same indie author used in the press release and sell sheet lessons. Note what fits in 50 words: identity, where, what, what's next.</p><div style="background:#fff;border:1px solid #d4cfc4;border-radius:4px;padding:24px 28px;margin:16px 0;font-family:Georgia,'Times New Roman',serif;font-size:14px;line-height:1.7;color:#1a1a1a"><p style="margin:0">Margaret Chen is a retired English teacher and lifelong central Ohioan. She lives in Hilliard with her husband and two rescue dogs. Her debut cozy mystery, <em>Smoke and Brick</em>, releases June 15, 2026. She is at work on the second book in the German Village Mystery series.</p></div><div class="lesson-tip"><strong>What this short bio does well:</strong> identity in the first eight words ("retired English teacher and lifelong central Ohioan"); two concrete anchors (Hilliard, two rescue dogs) that make her feel real; the book and pub date; forward motion at the end. Nothing wasted.</div><h2>Example: long bio (150 words)</h2><p>Same author, the version you would send a podcast host or a publication asking for "your bio." Notice the structure — paragraph one is identity and origin, paragraph two is the work and credentials, paragraph three is humanizing detail and what's next.</p><div style="background:#fff;border:1px solid #d4cfc4;border-radius:4px;padding:24px 28px;margin:16px 0;font-family:Georgia,'Times New Roman',serif;font-size:14px;line-height:1.7;color:#1a1a1a"><p style="margin:0 0 14px">Margaret Chen is a retired English teacher and lifelong central Ohioan. After twenty years at Hilliard Davidson High School — most of them spent teaching juniors and seniors how to argue from Hemingway and Morrison — she retired in 2023 and turned to the project she had been deferring her entire career: writing the kind of cozy mystery she had always wanted to read.</p><p style="margin:0 0 14px">Her debut novel, <em>Smoke and Brick</em>, releases June 15, 2026, the first in a planned three-book German Village Mystery series set in Columbus, Ohio. Her short fiction has appeared in <em>The Sun</em> and <em>Cincinnati Review</em>. She also hosts the <em>Books on Brick</em> podcast, covering Midwestern fiction.</p><p style="margin:0">Margaret lives in Hilliard with her husband Tom and two rescue dogs, Gable and Grant. She is currently at work on the second book in the series and a stand-alone novel set in northern Ohio.</p></div><div class="lesson-tip"><strong>What this long bio does well:</strong> the opening sentence is the same as the short bio (a deliberate echo so the two bios don't feel like different people); paragraph one earns the reader's investment with the teaching-then-writing arc; paragraph two layers in credentials without name-dropping clutter; paragraph three returns to the human and ends with forward motion. Three paragraphs, each with a clear job.</div><h2>Common mistakes</h2><ul><li><strong>Writing in the third person about yourself, then forgetting halfway through.</strong> Pick third person and stay there for both bios. "Margaret Chen is..." not "I am..." — even though it feels weird at first.</li><li><strong>Listing every credential.</strong> Three relevant credits beat ten weak ones. If a credit doesn't earn its place, cut it.</li><li><strong>Making it about the writing process.</strong> Readers don't care that you "have always loved stories." They care what you actually do and what they can read.</li><li><strong>No update for years.</strong> A bio that still says "her debut releases next year" three years after launch reads as dead. Refresh once a year minimum.</li><li><strong>Generic warmth.</strong> "Loves coffee, books, and her dogs" applies to seventy percent of indie authors. Be specific (the dog's names, your morning routine, the city you live in) or skip it.</li></ul><h2>Where each bio lives</h2><ul><li><strong>Short bio:</strong> social media profile bios (Instagram, TikTok, Bluesky, LinkedIn), the bottom of every sell sheet, podcast guest blurb, conference name-tag bio, brief author intros at events.</li><li><strong>Long bio:</strong> press kit, "About the Author" page on your website, conference program speaker bio, literary magazine contributor pages, full features in publications, the back-flap copy of your book (often condensed slightly).</li></ul><h2>Keep both updated</h2><p>Set a calendar reminder once a year — same week as your tax prep is a useful anchor — to refresh both bios. The forward-motion line ("at work on the second book") is the most likely to drift; keep it current. When something changes (new book deal, new podcast, you move cities, you win an award), update on the day, not the week.</p>` },
  { id:'book-trailers', title:'Book trailers that work', desc:'What makes a great book trailer and how to make one affordably.', icon:'🎬', level:'intermediate', time:'8 min', body:`<p>A good book trailer creates atmosphere. It doesn't need a big budget — it needs to make viewers feel something.</p><h2>What it should do</h2><p>Establish tone in the first 5 seconds, introduce a compelling question, and end with a clear call to action.</p><h2>Length</h2><p>60-90 seconds for a full trailer. 30-45 seconds for TikTok and Instagram Reels.</p><h2>Making one affordably</h2><ul><li><strong>Free:</strong> Canva or CapCut with stock footage, your cover, and royalty-free music from YouTube Audio Library</li><li><strong>Low cost ($50-200):</strong> A Fiverr freelancer who specializes in book trailers</li></ul><div class="lesson-tip"><strong>Music matters most:</strong> The right track sets the entire emotional tone. Find it before anything else.</div>` },
  { id:'hiring-on-fiverr', title:'Hiring help on Fiverr', desc:'When and how to hire freelancers for cover design, formatting, trailers, marketing, and WordPress — without getting burned.', icon:'🤝', level:'intermediate', time:'7 min', body:`<p>Every indie author hits the same wall: there are pieces of a launch you cannot or do not want to do yourself. Cover design, interior formatting, a 60-second trailer, ad-copy polish, a basic WordPress site. <strong><a href="https://www.fiverr.com" target="_blank" rel="noopener">Fiverr</a></strong> is the most common place authors go to hire that out — a marketplace of independent contractors with public ratings, fixed packages, and turnaround times you can plan around.</p><h2>The services authors actually buy</h2><ul><li><strong>Cover design</strong> — typically $50–$300 for ebook + paperback wrap. Look for designers who show genre-specific portfolios; a horror designer is not a romance designer.</li><li><strong>Interior book formatting and design</strong> — $50–$200 for ebook + print interior. Saves hours of fighting Word or InDesign.</li><li><strong>Book trailers</strong> — $50–$200 for a 30–90 second video. See the <em>Book trailers that work</em> lesson for what to ask for.</li><li><strong>Marketing tasks</strong> — short bursts of social-media content, ad-copy variants, sales-page copy, blurb polish. $20–$100.</li><li><strong>WordPress setup</strong> — basic site, theme installation, plugin configuration. $50–$250 depending on scope.</li></ul><h2>How to pick a contractor without getting burned</h2><ul><li><strong>Reviews matter more than price.</strong> Sort by rating, then by reviews count. A $40 designer with 800 five-star reviews beats a $20 designer with 12.</li><li><strong>Look at the portfolio, not the description.</strong> Anyone can write "I will make you a stunning book cover." The portfolio shows whether they actually can.</li><li><strong>Send your blurb, not your manuscript.</strong> Freelancers need to understand the book in one paragraph. If you cannot describe it that way, you are not ready to hire.</li><li><strong>Pay for the revision package.</strong> Two or three rounds of revision is normal. One round almost never works on the first try.</li></ul><div class="lesson-tip"><strong>Use Fiverr for what you cannot or do not want to do yourself.</strong> Use the AI tools inside this app for everything else — drafting press releases, social posts, sell sheets, author bios, KDP keywords. Fiverr is the human option; this portal is the in-house option. Most authors use both.</div>` },
  { id:'posting-trailer', title:'Posting your book trailer', desc:'Save, format, caption, and distribute — which platforms actually move books, and how.', icon:'📤', level:'intermediate', time:'12 min', body:`<p>You generated a trailer. Now what? This guide covers saving and storing the file, picking the right format for each platform, writing captions that convert, choosing hashtags that get found, and — honestly — which platforms actually drive book sales versus which are just busywork.</p><h2>Step 1: from inside the app to actually posted</h2><p>You generated a trailer. Here is the exact click-by-click for getting it onto each platform.</p><h3 style="margin-bottom:6px">From the trailer page</h3><ol><li>On the <strong>Book Trailer Video</strong> page, with a finished trailer showing, click the green <strong>Post this trailer →</strong> button.</li><li>A modal opens with one tab per video-friendly platform you've set up on the <strong>Connections</strong> page (Instagram, TikTok, Facebook, X, Threads, LinkedIn, Reddit, Discord).</li><li>Click the tab for the platform you want to post to first.</li></ol><h3 style="margin-bottom:6px;margin-top:18px">Posting to Instagram Reels — from your phone</h3><p style="margin:0 0 8px">Desktop Instagram now accepts video uploads directly — use the modal's per-platform steps for that path. If you prefer posting from your phone (some authors find it faster once the file is there), here's the route:</p><ol><li>In the Instagram tab of the modal, click <strong>Copy caption</strong>. Your caption is now in your clipboard.</li><li>Click <strong>Download video</strong>. The MP4 saves to your Downloads folder as <em>instagram_trailer.mp4</em>.</li><li>Get the MP4 onto your phone. Use whichever you prefer: <ul><li><strong>Mac → iPhone (fastest)</strong>: Finder → Downloads → right-click the MP4 → Share → AirDrop → pick your phone.</li><li><strong>iCloud Drive / Google Drive / Dropbox</strong>: upload from your desktop, open the same cloud app on your phone, save to your camera roll.</li><li><strong>Email it to yourself</strong>: attach the MP4, open the email on your phone, save the attachment to your camera roll.</li><li><strong>Text yourself the file</strong> (iMessage on Mac → your iPhone works for files under ~100 MB).</li></ul></li><li>On your phone, open the <strong>Instagram app</strong> → tap the <strong>+</strong> → choose <strong>Reel</strong>.</li><li>Tap the <strong>gallery icon</strong> on the camera screen and pick the trailer MP4 from your camera roll.</li><li>Tap <strong>Next</strong> twice to get to the caption screen.</li><li><strong>Long-press the caption box → Paste</strong>. Your caption from step 1 drops in.</li><li>Tap <strong>Share</strong>.</li><li>Back on your desktop, in the Instagram tab of the modal, check <strong>Mark as posted to Instagram</strong>. A ✓ appears next to the Instagram tab so you can tell which platforms are done.</li></ol><h3 style="margin-bottom:6px;margin-top:18px">Posting to TikTok (mobile required)</h3><p style="margin:0 0 8px">Same pattern as Instagram Reels — TikTok's desktop upload is workable but the mobile app is friendlier.</p><ol><li>In the TikTok tab, click <strong>Copy caption</strong>, then <strong>Download video</strong>.</li><li>Transfer the MP4 to your phone (same options as above).</li><li>Open the <strong>TikTok app</strong> → tap the <strong>+</strong> → tap <strong>Upload</strong>.</li><li>Pick the trailer MP4 from your camera roll → tap <strong>Next</strong>.</li><li>Long-press the caption area → <strong>Paste</strong>.</li><li>Tap <strong>Post</strong>.</li></ol><h3 style="margin-bottom:6px;margin-top:18px">Posting to Facebook (desktop works fine)</h3><ol><li>In the Facebook tab of the modal, click <strong>Copy caption</strong>.</li><li>Click <strong>Open Facebook →</strong>. Facebook opens in a new tab.</li><li>On your author Page, click <strong>Create post</strong> (or the "What's on your mind?" composer).</li><li>Click the <strong>Photo/Video</strong> icon and pick the trailer MP4 from your Downloads folder. It will be at the top of the file picker — it's the most recently downloaded file.</li><li>Click into the caption area and paste (Cmd-V / Ctrl-V).</li><li>Click <strong>Post</strong>.</li></ol><h3 style="margin-bottom:6px;margin-top:18px">Posting to X, Bluesky, LinkedIn, Threads</h3><p style="margin:0 0 8px">All four work fine from desktop. The pattern:</p><ol><li>Click <strong>Copy caption</strong> in the tab, then <strong>Download video</strong>.</li><li>Click <strong>Open [Platform] →</strong>. Most of these (X, Threads, Bluesky) open the composer with your caption already filled in — no paste needed.</li><li>Click the video/attachment icon in the composer and pick the MP4 from your Downloads folder.</li><li>Click Post (or whatever the platform calls it).</li></ol><div class="lesson-tip"><strong>Save and back up the source MP4.</strong> Rename it as you save (something like <em>SmokeAndBrick-trailer-9x16.mp4</em>) and copy it to Google Drive, Dropbox, or iCloud. The trailer should outlive any one device, and you'll want it again for the 4-6-week repost cycle described below.</div><h2>Step 2: format-by-platform — what works where</h2><p>Different platforms favor different aspect ratios. The table below covers what to upload and what each platform's behavior looks like in 2026.</p><div style="overflow-x:auto;margin:14px 0"><table style="border-collapse:collapse;width:100%;font-size:13px;line-height:1.5"><thead><tr style="background:#f0ebe0"><th style="border:1px solid #d4cfc4;padding:8px 10px;text-align:left">Platform</th><th style="border:1px solid #d4cfc4;padding:8px 10px;text-align:left">Best format</th><th style="border:1px solid #d4cfc4;padding:8px 10px;text-align:left">Length cap</th><th style="border:1px solid #d4cfc4;padding:8px 10px;text-align:left">Notes</th></tr></thead><tbody><tr><td style="border:1px solid #d4cfc4;padding:8px 10px;font-weight:600">TikTok</td><td style="border:1px solid #d4cfc4;padding:8px 10px">9:16 vertical</td><td style="border:1px solid #d4cfc4;padding:8px 10px">10 min (BookTok ideal: 30-60s)</td><td style="border:1px solid #d4cfc4;padding:8px 10px">BookTok is the single biggest driver of indie book discovery. If you only post one place, post here.</td></tr><tr style="background:#fafaf7"><td style="border:1px solid #d4cfc4;padding:8px 10px;font-weight:600">Instagram Reels</td><td style="border:1px solid #d4cfc4;padding:8px 10px">9:16 vertical, <strong>16:9 increasingly accepted</strong></td><td style="border:1px solid #d4cfc4;padding:8px 10px">90 sec</td><td style="border:1px solid #d4cfc4;padding:8px 10px">Reels traditionally was vertical-only, but Instagram now accepts and surfaces 16:9 widely. Try both — one may outperform for your audience.</td></tr><tr><td style="border:1px solid #d4cfc4;padding:8px 10px;font-weight:600">Instagram Feed</td><td style="border:1px solid #d4cfc4;padding:8px 10px">1:1 square or 4:5 portrait</td><td style="border:1px solid #d4cfc4;padding:8px 10px">60 sec</td><td style="border:1px solid #d4cfc4;padding:8px 10px">Different surface than Reels. Reaches your existing followers more than Reels does.</td></tr><tr style="background:#fafaf7"><td style="border:1px solid #d4cfc4;padding:8px 10px;font-weight:600">YouTube Shorts</td><td style="border:1px solid #d4cfc4;padding:8px 10px">9:16 vertical (mandatory)</td><td style="border:1px solid #d4cfc4;padding:8px 10px">60 sec</td><td style="border:1px solid #d4cfc4;padding:8px 10px">Solid third option. SEO-friendly — Shorts get indexed by Google.</td></tr><tr><td style="border:1px solid #d4cfc4;padding:8px 10px;font-weight:600">YouTube (regular)</td><td style="border:1px solid #d4cfc4;padding:8px 10px">16:9 horizontal</td><td style="border:1px solid #d4cfc4;padding:8px 10px">unlimited</td><td style="border:1px solid #d4cfc4;padding:8px 10px">Embed on your author website. Lasting placement vs algorithmic feeds.</td></tr><tr style="background:#fafaf7"><td style="border:1px solid #d4cfc4;padding:8px 10px;font-weight:600">Facebook</td><td style="border:1px solid #d4cfc4;padding:8px 10px">16:9 or 1:1</td><td style="border:1px solid #d4cfc4;padding:8px 10px">240 min</td><td style="border:1px solid #d4cfc4;padding:8px 10px">Strong for readers 35+. Facebook Reels is also a thing — same vertical format as Instagram Reels.</td></tr><tr><td style="border:1px solid #d4cfc4;padding:8px 10px;font-weight:600">Bluesky</td><td style="border:1px solid #d4cfc4;padding:8px 10px">9:16 or 1:1</td><td style="border:1px solid #d4cfc4;padding:8px 10px">60 sec, 50 MB</td><td style="border:1px solid #d4cfc4;padding:8px 10px">Active book community. Lower volume than Twitter but more engaged.</td></tr><tr style="background:#fafaf7"><td style="border:1px solid #d4cfc4;padding:8px 10px;font-weight:600">LinkedIn</td><td style="border:1px solid #d4cfc4;padding:8px 10px">16:9 or 1:1</td><td style="border:1px solid #d4cfc4;padding:8px 10px">10 min</td><td style="border:1px solid #d4cfc4;padding:8px 10px">Best for nonfiction, business, self-help. Less effective for fiction.</td></tr></tbody></table></div><div class="lesson-tip"><strong>The Reels 16:9 shift is real.</strong> Instagram's algorithm increasingly surfaces horizontal video in the Reels feed. If you generated a 16:9 trailer for YouTube, you can repurpose it on Reels too — saves a render and broadens reach.</div><h2>Step 3: caption templates</h2><p>Tone differs by platform. Here are three captions for the same trailer, each tuned to its surface.</p><h3 style="margin-bottom:8px">TikTok — short, hashtag-heavy</h3><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:14px 16px;margin:6px 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Helvetica',sans-serif;font-size:13px;line-height:1.5"><p style="margin:0 0 10px">She thought retirement would be quiet. She was wrong.</p><p style="margin:0 0 10px">Smoke and Brick — book one of a Columbus mystery series. Out now.</p><p style="margin:0;color:#1f6cc7">#BookTok #CozyMystery #DebutAuthor #BookRecommendations #IndieAuthor #ColumbusOhio #BookTokFinds #SmallTownMystery</p></div><h3 style="margin-bottom:8px">Instagram Reels — narrative, scene-setting</h3><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:14px 16px;margin:6px 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Helvetica',sans-serif;font-size:13px;line-height:1.5"><p style="margin:0 0 10px">Cozy mystery readers — this one is for you.</p><p style="margin:0 0 10px">Twenty years of teaching English, then I retired and finally wrote the book I'd always wanted to read. Smoke and Brick: a 1923 newspaper clipping wedged behind the brick of a German Village bakery starts pulling on threads the neighborhood would rather leave alone.</p><p style="margin:0 0 10px">Out now in paperback and ebook. Link in bio.</p><p style="margin:0;color:#1f6cc7">#Bookstagram #CozyMystery #DebutNovel #IndieAuthor #ColumbusOhio #ReadIndie #BookishCommunity</p></div><h3 style="margin-bottom:8px">Facebook — conversational, engagement-driven</h3><div style="background:#fff;border:1px solid #d4cfc4;border-radius:6px;padding:14px 16px;margin:6px 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Helvetica',sans-serif;font-size:13px;line-height:1.5"><p style="margin:0 0 10px">After two years of writing, my debut novel <em>Smoke and Brick</em> is finally here.</p><p style="margin:0 0 10px">Set in Columbus' German Village. A retired schoolteacher uncovers a 1923 mystery beneath the cobblestones outside her bakery. The first in a three-book series.</p><p style="margin:0">Available in paperback and ebook now. Comment below if you'd like a launch-week signed copy — I'm sending out 50.</p></div><h2>Step 4: hashtag strategy</h2><p>Hashtag rules of thumb across platforms:</p><ul><li><strong>5-10 hashtags max</strong> on most platforms. More than that hurts discoverability — algorithms read it as spam-signal.</li><li><strong>Mix scales</strong> — 2-3 huge hashtags (#BookTok), 3-4 mid-size (#CozyMystery, #IndieAuthor), 2-3 niche (#ColumbusAuthor, #DebutNovelist). Niche tags get you found by readers who actually convert.</li><li><strong>Local angle hashtags are gold for fiction with regional settings</strong> — #ColumbusAuthor, #BostonBooks, #PNWAuthor</li><li><strong>Don't reuse the exact same hashtag set on every post.</strong> Vary 30-40% of your tags between posts to avoid algorithm fatigue.</li></ul><h3 style="margin-top:14px;margin-bottom:6px">Sample hashtag sets by genre</h3><ul><li><strong>Cozy mystery:</strong> #CozyMystery #Whodunit #SmallTownMystery #AmReadingMystery #CozyReads</li><li><strong>Thriller / suspense:</strong> #ThrillerBooks #PsychologicalThriller #CrimeFiction #ThrillerLovers #ReadAtNight</li><li><strong>Romance:</strong> #BookishRomance #RomanceNovel #ContemporaryRomance #RomanceReads #SwoonyReads</li><li><strong>Fantasy:</strong> #FantasyBooks #BookishFantasy #EpicFantasy #FantasyReader #BookishMagic</li><li><strong>Sci-fi:</strong> #ScienceFictionBooks #SciFiReads #SpeculativeFiction #SFFCommunity</li><li><strong>Historical fiction:</strong> #HistoricalFiction #HistoryBuff #PastTales #HistFicReads</li><li><strong>Literary fiction:</strong> #LiteraryFiction #BookishCommunity #ReadIndie #ContemporaryLit</li><li><strong>Memoir / non-fiction:</strong> #MemoirReads #NonFictionReads #TrueStories #BookwormsCorner</li><li><strong>YA:</strong> #YABooks #YALit #YoungAdultBooks #YAReader #BookishTeens</li></ul><h2>Step 5: which platforms actually drive book sales</h2><p>Honest framing — not all platforms are equal:</p><ul><li><strong>TikTok (BookTok)</strong> — single largest discovery engine for indie books in 2026. Weight your effort here. A trailer that catches on can move thousands of copies.</li><li><strong>Instagram Reels</strong> — solid second. Bookstagram is a real ecosystem with reviewers and readers actively looking for new books.</li><li><strong>YouTube Shorts</strong> — good third. Lower discovery but lasting indexability (Google search picks them up).</li><li><strong>Facebook</strong> — works for older readers (35+). Don't ignore if your audience skews older — cozy mystery, historical fiction, memoir.</li><li><strong>Bluesky</strong> — small but engaged book community. Worth crossposting since it's effectively free to do, but don't expect tonnage.</li><li><strong>LinkedIn</strong> — only for nonfiction, business, self-help. Fiction posts there mostly fall flat.</li></ul><div class="lesson-tip"><strong>The 80/20 rule for trailers:</strong> spend 80% of your trailer-promotion effort on TikTok and Instagram Reels. Spend the other 20% crossposting to YouTube Shorts and Facebook. Don't spread thin across platforms with low book-buying audiences.</div><h2>Step 6: posting cadence</h2><ul><li><strong>Don't post the same trailer twice in a week to the same platform.</strong> Algorithms penalize repetition.</li><li><strong>Do repost the same trailer to different platforms.</strong> Each platform's audience is largely separate — same trailer reaches new eyes.</li><li><strong>Repost with variation 4-6 weeks later</strong> — different caption, different hashtag mix, different hook. Algorithm treats it as a new post; new audience sees it.</li><li><strong>Schedule around launch milestones</strong> — pub day, week-1 update, month-1 reflection, anniversary edition. Each is a natural reason to post the trailer again.</li></ul><h2>One more thing: the URL link strategy</h2><p>Most platforms either don't allow links in posts (TikTok, Instagram Reels) or downrank posts with links (most others). The standard workaround:</p><ul><li><strong>Put a single "link in bio" call</strong> in your caption</li><li><strong>Use a link aggregator</strong> — Linktree, Beacons, or your own author website's links page — so one bio link points to all the places to buy or follow</li><li><strong>Update the link aggregator monthly</strong> — keep new releases at the top</li></ul>` },
  { id:'arc-strategy', title:'ARC strategy', desc:'How to use advance reader copies to build reviews before launch.', icon:'📚', level:'intermediate', time:'10 min', body:`<p>ARCs get you reviews on launch day instead of months later.</p><h2>How many to send</h2><p>Expect 50-60% of ARC readers to leave a review. If you want 20 reviews at launch, send 40 ARCs minimum.</p><h2>Where to find ARC readers</h2><ul><li><strong>Your email list</strong> — your best candidates</li><li><strong>NetGalley</strong> — platform specifically for ARC distribution</li><li><strong>BookSirens</strong> — popular with indie authors</li><li><strong>Genre Facebook groups</strong> — many have ARC request threads</li></ul><div class="lesson-tip"><strong>Build your ARC list now:</strong> Add a signup form to your website even before your book is finished.</div><h2>Managing your list</h2><p>Use the Contacts section of the portal to maintain your ARC reader list. Tag them to easily reach out for your next book.</p>` },
  { id:'amazon-ads', title:'Amazon Ads for authors', desc:'Sponsored products, keyword targeting, and managing your budget.', icon:'💰', level:'advanced', time:'25 min', body:`<p>Amazon Ads reach people already in a buying mindset — one of the most effective channels for books.</p><h2>Start with Sponsored Products</h2><p>Your book appears in search results and on product pages. Start here before trying other ad types.</p><h2>Keyword strategy</h2><p>Start with automatic campaigns for 2-3 weeks, then move best-performing keywords to manual campaigns with higher bids.</p><ul><li><strong>Comparable authors</strong> — authors who write books similar to yours</li><li><strong>Comparable titles</strong> — specific books similar to yours</li><li><strong>Genre terms</strong> — "legal thriller," "cozy mystery," "epic fantasy"</li></ul><div class="lesson-tip"><strong>Start small:</strong> $5/day budget, run for at least 2 weeks before drawing conclusions.</div><h2>Key metrics</h2><ul><li><strong>ACoS</strong> — ad spend divided by ad revenue. Under 70% is generally profitable.</li><li><strong>CTR</strong> — under 0.3% means your cover or title needs work.</li></ul>` },
  { id:'ingram-distribution', title:'Ingram and distribution', desc:'How IngramSpark works, what it actually costs, and how to reach independent bookstores.', icon:'🏪', level:'advanced', time:'15 min', body:`<p>IngramSpark connects your book to 40,000+ retailers worldwide — independent bookstores, libraries, Barnes &amp; Noble, and international markets KDP doesn't reach. If KDP is "Amazon plus a few extras," IngramSpark is "everywhere else."</p><h2>What changed in late 2025</h2><p>Two pricing shifts make IngramSpark much friendlier to indie authors than it was even a year ago:</p><ul><li><strong>Account setup is now free.</strong> No per-title setup fee, no monthly subscription. You can list a paperback, hardcover, and eBook at $0 upfront.</li><li><strong>Revisions are free for 60 days</strong> after approval. After that, file changes are $25 each — though IngramSpark has been making more updates free over time.</li></ul><p>What didn't change: Ingram still takes a <strong>1.875% market access fee</strong> on each sale (about $0.38 on a $20 book), and the <strong>wholesale discount</strong> you set (30–55%) is what bookstores keep when they sell your book. Both come out of your royalty — there's no separate bill.</p><h2>KDP vs IngramSpark — use both</h2><p>This is the part most new authors get wrong. KDP and IngramSpark aren't competitors; they're complementary.</p><ul><li><strong>KDP</strong> prints and ships your book when someone buys it on Amazon. It does not list your book in independent bookstores or libraries.</li><li><strong>IngramSpark</strong> lists your book in Ingram's catalog, which is what bookstores, libraries, and international retailers actually order from.</li></ul><p>Many authors use KDP for Amazon and IngramSpark for everywhere else. <strong>Each format needs its own ISBN per platform</strong> — so a paperback on both KDP and IS means two paperback ISBNs, not one. The two systems can't share an ISBN.</p><h2>Setup — the short version</h2><ol><li>Create your free account at ingramspark.com</li><li>Sort out ISBNs first — your own from Bowker, or free from Ingram (free IS-issued ISBNs lock you into IS as the printer)</li><li>Use Ingram's cover template generator for exact spine width and bleed</li><li>Upload print-ready interior PDF and cover PDF</li><li>Set your retail price and wholesale discount</li><li>Choose distribution territories and decide on returns</li></ol><div class="lesson-tip"><strong>File specs are the #1 rejection reason.</strong> Use Ingram's template generator <em>before</em> designing your cover — it calculates exact spine width based on page count and paper choice. Hand the template to your cover designer up front.</div><h2>The wholesale-discount decision</h2><p>You set this number per market, and it matters more than almost any other choice. Bookstores keep this percentage of the retail price; you get the rest, minus print cost.</p><p><strong>The allowed range varies by market.</strong> In the US, IngramSpark lets you choose between <strong>40% and 55%</strong>. Other markets allow lower discounts — some down to 30% — so you can tune the discount per country if you want different reach/margin trade-offs in different regions.</p><ul><li><strong>55%</strong> — what brick-and-mortar bookstores expect. Required to be stocked in most physical stores.</li><li><strong>45–50%</strong> — middle ground; works for online retailers and some specialty stores.</li><li><strong>40% (US floor)</strong> — online-only in the US; some non-US markets allow going lower.</li></ul><p>Example at 55%: $16.99 retail − 55% bookstore cut ($9.34) − $4.20 print cost = $3.45 royalty per book. Drop the same book to 40% and your royalty roughly doubles, but you've taken yourself out of most physical bookstore consideration.</p><h2>Returns — read this twice</h2><p>You can opt your title in or out of returns. Bookstores strongly prefer returnable titles, and many won't stock non-returnable books at all. The downside is real: if a store orders 50 copies and returns 30 unsold, <strong>you pay the print cost on all 30 returned copies</strong>, and the original sale revenue gets clawed back from your account.</p><div class="lesson-tip"><strong>Default to no-returns</strong> if you're selling primarily online. Only enable returns once you have a real plan for getting into physical bookstores — events, signings, local indie relationships.</div><h2>Getting into actual bookstores</h2><p>Being in Ingram's catalog does <em>not</em> mean stores will automatically stock you. Stores order from Ingram, but they only order what they've decided to carry, and that decision happens locally — book by book, store by store.</p><p>What actually works: reach out to independent bookstores in your area, offer to do a signing event, make a personalized pitch, and make sure your book is returnable and discounted at 55%. Libraries follow a similar pattern — pitch to your local branch's collection-development librarian.</p><h2>Skip the proof — order a single retail copy</h2><p>IngramSpark sells official proof copies, but once your files are approved you can just order a single retail copy of your own book. Costs less, arrives faster, and you'll have it well before the book actually starts appearing on retailer sites (which takes 6–8 weeks after approval). Inspect carefully — if anything's wrong, you have that 60-day free-revision window to fix it.</p>` },
  { id:'reading-analytics', title:'Reading your analytics', desc:'What the numbers actually mean and how to improve them.', icon:'📊', level:'advanced', time:'15 min', body:`<p>Numbers without context are noise. Here's what the key metrics actually mean.</p><h2>Email metrics</h2><ul><li><strong>Open rate</strong> — industry average for authors is 25-35%. Below 20%? Your subject lines need work.</li><li><strong>Click-through rate</strong> — good CTR for author emails is 3-5%.</li><li><strong>Unsubscribe rate</strong> — keep below 0.5% per email.</li></ul><div class="lesson-tip"><strong>List hygiene:</strong> Remove subscribers who haven't opened your last 10 emails. A smaller engaged list outperforms a large unresponsive one.</div><h2>Social metrics</h2><ul><li><strong>Reach</strong> — unique accounts who saw your post. More important than follower count.</li><li><strong>Engagement rate</strong> — likes, comments, shares divided by reach. Good rate: 3-6%.</li></ul><h2>Ad metrics</h2><ul><li><strong>CPC</strong> — for books, $0.30-$0.80 is typical.</li><li><strong>ROAS</strong> — revenue per dollar spent. 2× or higher means profitable.</li></ul>` }
];

let currentLessonIndex = 0;
let completedLessons = JSON.parse(localStorage.getItem('edu_completed') || '[]');

function renderEducation() {
  renderLessonGrid('edu-grid-beginner',     LESSONS.filter(l => l.level === 'beginner'),     'pill-green',  'green');
  renderLessonGrid('edu-grid-intermediate', LESSONS.filter(l => l.level === 'intermediate'), 'pill-amber',  'amber');
  renderLessonGrid('edu-grid-advanced',     LESSONS.filter(l => l.level === 'advanced'),     'pill-purple', 'purple');
  updateEduProgress();
}

function renderLessonGrid(gridId, lessons, pillClass, iconClass) {
  const grid = document.getElementById(gridId);
  if (!grid) return;
  grid.innerHTML = lessons.map(lesson => {
    const done = completedLessons.includes(lesson.id);
    const levelLabel = lesson.level.charAt(0).toUpperCase() + lesson.level.slice(1);
    return '<div class="edu-card ' + (done ? 'completed' : '') + '" onclick="openLesson(\'' + lesson.id + '\')">' +
      '<div class="edu-check"><svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 5l2.5 2.5L8 2.5"/></svg></div>' +
      '<div class="edu-card-icon edu-icon-' + iconClass + '">' + lesson.icon + '</div>' +
      '<h3>' + lesson.title + '</h3><p>' + lesson.desc + '</p>' +
      '<div class="edu-card-footer"><span class="edu-pill ' + (done ? 'pill-done' : pillClass) + '">' + (done ? 'Completed' : levelLabel) + '</span>' +
      '<span class="edu-time"><svg width="11" height="11" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="6" cy="6" r="5"/><path d="M6 3v3l2 1.5"/></svg>' + lesson.time + '</span></div></div>';
  }).join('');
}

function updateEduProgress() {
  const total = LESSONS.length, done = completedLessons.length;
  const countEl = document.getElementById('edu-completed-count');
  const barEl   = document.getElementById('edu-progress-bar');
  if (countEl) countEl.textContent = done + ' / ' + total;
  if (barEl)   barEl.style.width   = (total > 0 ? Math.round((done/total)*100) : 0) + '%';
}

function openLesson(lessonId) {
  const idx = LESSONS.findIndex(l => l.id === lessonId);
  if (idx === -1) return;
  currentLessonIndex = idx;
  showLesson(idx);
}

// Demo-mode lesson truncation — returns the first ~18% of the
// lesson HTML (rounded to whole top-level elements) plus a
// lock-out card prompting signup.
function truncateLessonForDemo(html) {
  const tmp = document.createElement('div');
  tmp.innerHTML = html;
  const totalLen = tmp.textContent.length;
  const targetLen = Math.max(400, Math.floor(totalLen * 0.18));
  const out = document.createElement('div');
  let acc = 0;
  for (const child of Array.from(tmp.children)) {
    out.appendChild(child.cloneNode(true));
    acc += child.textContent.length;
    if (acc >= targetLen) break;
  }
  const lock = document.createElement('div');
  lock.className = 'demo-lesson-lock';
  lock.innerHTML =
    '<div class="lock-eyebrow">Demo preview</div>' +
    '<h4>Read the rest with a real account</h4>' +
    '<p>The full education library — lessons on craft, marketing, BookTok strategy, ARC programs, hashtag strategy by genre, and more — is included with every plan.</p>' +
    '<button class="app-btn app-btn-green" onclick="navigate(\'pricing\')">See plans →</button>';
  out.appendChild(lock);
  return out.innerHTML;
}

function showLesson(idx) {
  const lesson = LESSONS[idx];
  if (!lesson) return;
  document.getElementById('edu-library').style.display = 'none';
  document.getElementById('edu-lesson').classList.add('active');
  const levelLabel = lesson.level.charAt(0).toUpperCase() + lesson.level.slice(1);
  const pillClass  = lesson.level === 'beginner' ? 'pill-green' : lesson.level === 'intermediate' ? 'pill-amber' : 'pill-purple';
  const done = completedLessons.includes(lesson.id);
  document.getElementById('lesson-pill').textContent  = levelLabel;
  document.getElementById('lesson-pill').className    = 'edu-pill ' + pillClass;
  document.getElementById('lesson-time').textContent  = lesson.time;
  document.getElementById('lesson-title').textContent = lesson.title;
  document.getElementById('lesson-desc').textContent  = lesson.desc;
  const isDemo = !!(currentUser && (currentUser.is_demo == 1 || currentUser.is_demo === true));
  document.getElementById('lesson-body').innerHTML = isDemo ? truncateLessonForDemo(lesson.body) : lesson.body;
  const cb = document.getElementById('lesson-complete-btn');
  cb.textContent = done ? '✓ Completed' : 'Mark as complete ✓';
  cb.style.opacity = done ? '0.6' : '1';
  document.getElementById('lesson-prev-btn').style.visibility = idx > 0 ? 'visible' : 'hidden';
  document.getElementById('lesson-next-btn').style.visibility = idx < LESSONS.length-1 ? 'visible' : 'hidden';
  document.getElementById('main').scrollTop = 0;
}

function closeLesson() {
  document.getElementById('edu-library').style.display = 'block';
  document.getElementById('edu-lesson').classList.remove('active');
  renderEducation();
  document.getElementById('main').scrollTop = 0;
}

function prevLesson() { if (currentLessonIndex > 0) { currentLessonIndex--; showLesson(currentLessonIndex); } }
function nextLesson() { if (currentLessonIndex < LESSONS.length-1) { currentLessonIndex++; showLesson(currentLessonIndex); } }

function markComplete() {
  const lesson = LESSONS[currentLessonIndex];
  if (!lesson) return;
  if (!completedLessons.includes(lesson.id)) {
    completedLessons.push(lesson.id);
    localStorage.setItem('edu_completed', JSON.stringify(completedLessons));
    toast('Lesson complete! Great work.');
    const cb = document.getElementById('lesson-complete-btn');
    cb.textContent = '✓ Completed'; cb.style.opacity = '0.6';
  }
  if (currentLessonIndex < LESSONS.length-1) setTimeout(() => nextLesson(), 800);
}

// ══════════════════════════════════════
// WORDPRESS SECTION
// ══════════════════════════════════════
const WP_LESSONS = [
  {
    id: 'wp-why-website',
    title: 'Why every author needs a website',
    desc: 'Your social media is rented land. Your website is the one place you truly own.',
    icon: '🏠',
    time: '5 min',
    body: `<p>Social media platforms come and go. Algorithms change. Accounts get suspended. But your website — at your own domain — is permanent real estate on the internet that belongs entirely to you.</p>
    <h2>What your author website does</h2>
    <ul>
      <li><strong>Central hub</strong> — every platform you're on should point back to your website. It's where interested readers go to learn more.</li>
      <li><strong>Email list home</strong> — your signup form lives here, building your most valuable marketing asset</li>
      <li><strong>Book sales</strong> — sell directly to readers with no middleman taking a cut</li>
      <li><strong>Media kit</strong> — press, podcasters, and event organizers need your bio, photo, and book info in one place</li>
      <li><strong>SEO</strong> — your name and book titles become searchable on Google over time</li>
    </ul>
    <h2>What it should have</h2>
    <ul>
      <li>Homepage with your latest book prominently featured</li>
      <li>About page with your author bio and photo</li>
      <li>Books page listing all your titles with buy links</li>
      <li>Blog or news section for updates</li>
      <li>Contact page</li>
      <li>Email signup form on every page</li>
    </ul>
    <div class="lesson-tip"><strong>Your domain name:</strong> Use your author name if possible — FirstnameLastname.com. If that's taken, try FirstnameLastnameAuthor.com or FirstnameLastnameBooks.com. Avoid hyphens.</div>`
  },
  {
    id: 'wp-choosing-host',
    title: 'Choosing WordPress hosting',
    desc: 'Not all hosting is equal. Here\'s what to look for and which hosts work best for authors.',
    icon: '🖥️',
    time: '8 min',
    body: `<p>WordPress hosting is not the same as general web hosting. Managed WordPress hosting handles all the technical maintenance automatically — updates, security patches, daily backups — so you can focus on writing.</p>
    <h2>Types of WordPress hosting</h2>
    <ul>
      <li><strong>Managed WordPress hosting</strong> — the host handles everything technical (updates, security, backups) for you. The most hands-off option; usually the priciest. Examples: WP Engine, Kinsta, SiteGround.</li>
      <li><strong>Shared hosting with WordPress</strong> — cheaper but slower and you manage more yourself. Fine for starting out. Examples: Bluehost, HostGator.</li>
      <li><strong>WordPress.com</strong> — easiest to start but very limited. You don't fully own your site. Avoid for serious author websites.</li>
    </ul>
    <h2>What to look for</h2>
    <ul>
      <li><strong>Automatic backups</strong> — daily at minimum. Essential protection.</li>
      <li><strong>SSL certificate included</strong> — your site needs https://. Most good hosts include this free.</li>
      <li><strong>One-click WordPress install</strong> — getting WordPress running should take minutes, not hours</li>
      <li><strong>Good support</strong> — 24/7 live chat is ideal for when things go wrong</li>
      <li><strong>Speed</strong> — slow sites lose readers. Look for hosts that use modern infrastructure.</li>
    </ul>
    <div class="lesson-tip"><strong>Our recommendation:</strong> Start with DreamHost's WordPress hosting. It's beginner-friendly and affordable, and — unlike most hosts — it bills month-to-month with a 97-day money-back guarantee, so you can get going without a big upfront commitment. Hostinger (lowest starting price) and Bluehost (familiar, tutorial-rich) are solid alternatives. Whichever you pick, choose the <strong>basic WordPress plan</strong> — Elite Publishing plugin adds your store for you.</div>
    <h2>What it costs</h2>
    <p>You don't need an expensive plan. A basic WordPress plan runs just a few dollars a month, and hosts like DreamHost let you pay month-to-month so you're never locked in. One thing to watch: most hosts advertise a low introductory rate that rises at renewal, so check the renewal price before you commit. Start small — you can always upgrade later if your traffic grows.</p>`
  },
  {
    id: 'wp-setup-basics',
    title: 'Setting up WordPress',
    desc: 'From install to live site — the essential first steps every author needs to take.',
    icon: '⚙️',
    time: '12 min',
    body: `<p>Once you have hosting, getting WordPress running is straightforward. Most managed hosts have a one-click installer that has WordPress live in under 5 minutes.</p>
    <h2>The installation steps</h2>
    <ol>
      <li>Log into your hosting control panel</li>
      <li>Find "WordPress" or "One-click install"</li>
      <li>Enter your site name, admin email, and choose a password</li>
      <li>Click install — done in minutes</li>
      <li>Log in at yourdomain.com/wp-admin</li>
    </ol>
    <h2>First things to do after installing</h2>
    <ul>
      <li><strong>Set your permalink structure</strong> — Go to Settings → Permalinks → choose "Post name." This makes your URLs clean and readable.</li>
      <li><strong>Delete the sample content</strong> — Remove the "Hello World" post and sample page that WordPress installs by default</li>
      <li><strong>Set your timezone</strong> — Settings → General → Timezone. Important for scheduled posts.</li>
      <li><strong>Install SSL</strong> — Most hosts do this automatically. Check that your site loads as https:// not http://</li>
    </ul>
    <div class="lesson-tip"><strong>Write down your login details:</strong> yourdomain.com/wp-admin, your username, and password. Store them somewhere safe — you'll need them regularly.</div>
    <h2>Choosing a theme</h2>
    <p>Your theme controls how your site looks. For authors, look for themes that put your book cover front and center. Good free options: Astra, OceanWP, GeneratePress. Premium author-specific themes: Author (Elegant Themes), BookStore (ThemeForest).</p>`
  },
  {
    id: 'wp-essential-plugins',
    title: 'Essential WordPress plugins for authors',
    desc: 'The plugins every author website needs — and the ones to avoid.',
    icon: '🔌',
    time: '10 min',
    body: `<p>Plugins extend what WordPress can do. But too many plugins slow your site down. Here are the essential ones for an author website — install these and only add more when you have a specific need.</p>
    <h2>Must-have plugins</h2>
    <ul>
      <li><strong>Yoast SEO</strong> — helps Google find and rank your site. Guides you through optimizing every page for search. Free version is excellent.</li>
      <li><strong>WooCommerce</strong> — if you want to sell books directly from your site. The most powerful ecommerce plugin for WordPress. Free to install, transaction fees apply.</li>
      <li><strong>Mailchimp for WordPress</strong> (or your email provider's plugin) — adds signup forms to your site that connect directly to your email list.</li>
      <li><strong>Wordfence Security</strong> — protects your site from hackers and malware. The free version is solid.</li>
      <li><strong>WP Rocket</strong> (paid) or <strong>W3 Total Cache</strong> (free) — makes your site load faster by caching pages.</li>
      <li><strong>UpdraftPlus</strong> — automatic backups to Google Drive or Dropbox. Even if your host does backups, having your own copy is smart.</li>
    </ul>
    <div class="lesson-warning"><strong>Plugin warning:</strong> Only install plugins from reputable sources with recent updates and many active installations. Abandoned plugins are a security risk. Delete plugins you're not using.</div>
    <h2>Plugins to skip</h2>
    <p>Avoid page builder plugins (Elementor, Divi) until you're comfortable with WordPress basics — they add complexity. Also skip any plugin that hasn't been updated in over a year.</p>
    <h2>Connecting to Elite Publishing</h2>
    <p>Once your WordPress site is live, add your website URL in your Elite Publishing Account settings. Future features will allow you to publish blog posts from the portal directly to your WordPress site.</p>`
  }
];

// Streamlined, plugin-first setup flow. Elite Publishing companion plugin does
// the heavy lifting on activation (installs the theme, builds the pages + control
// panel, applies a style, wires WooCommerce), so the old DIY checklist — install a
// theme, hand-build five pages, add SEO/security plugins — is gone. The download
// button lives in the install step (rendered from step.action).
const WP_STEPS = [
  { id: 'wp-host',     label: 'Get a WordPress site',
    detail: 'A website is an <strong>optional bonus</strong> — the app works fine without one, so skip this section if you\'re not ready. If you do want your own author site: pick a host below (we suggest <strong>DreamHost</strong> — it bills month-to-month with a 97-day money-back guarantee, so there\'s no big upfront commitment), choose the <strong>basic WordPress plan</strong>, and run its one-click WordPress installer (about five minutes). Two heads-ups: your host will email you to set your WordPress password before you can log in — watch for that email; and jot down two things you\'ll need in the next step — your <strong>site address</strong> and your <strong>WordPress login</strong>. Already have a WordPress site? Skip to the next step.' },
  { id: 'wp-plugin',   label: 'Install Elite Publishing plugin',
    detail: 'Download your plugin (button below). Then open your <strong>WordPress Dashboard</strong> — that\'s your site\'s admin area at <strong>your-site-address/wp-admin</strong>, where you log in with the username and password you just set. Go to <strong>Plugins → Add New → Upload Plugin</strong>, choose the file, click <strong>Install Now</strong>, then <strong>Activate</strong>. On activation it sets up your whole site automatically — theme, pages, control panel, style, and store — so there\'s nothing to build by hand. After that, new versions update themselves.',
    action: '<a class="app-btn app-btn-green" href="/api/wp_plugin.php?action=download" download style="text-decoration:none;margin-top:10px">↓ Download plugin</a>' },
  { id: 'wp-connect',  label: 'Connect your site to Elite Publishing',
    detail: 'Open the <a href="#" onclick="navigate(\'website\');return false;">Website</a> page and connect your site — one click, no password to copy. This is what lets the app publish your books straight to your site.' },
  { id: 'wp-personalize', label: 'Make it yours',
    detail: 'On your site, open <strong>Manage My Site → Site Info</strong> and add your name, photo, and bio. (When you publish a book, the app seeds these for you automatically if they\'re still blank — so this is just for fine-tuning.)' },
  { id: 'wp-publish',  label: 'Publish your first book',
    detail: 'In <a href="#" onclick="navigate(\'books\');return false;">My Books</a>, open a book and click <strong>Update on my website</strong>. It appears on your site right away — cover, description, and buy links.' },
];

let wpCompletedSteps = JSON.parse(localStorage.getItem('wp_steps') || '[]');

function showWpTab(tab) {
  document.getElementById('wp-learn').style.display  = tab === 'learn'  ? 'block' : 'none';
  document.getElementById('wp-wizard').style.display = tab === 'wizard' ? 'block' : 'none';
  const tourPanel = document.getElementById('wp-tour');
  if (tourPanel) tourPanel.style.display = tab === 'tour' ? 'block' : 'none';
  document.getElementById('wp-tab-learn').style.background  = tab === 'learn'  ? 'var(--accent)' : 'var(--white)';
  document.getElementById('wp-tab-learn').style.color       = tab === 'learn'  ? 'var(--white)'  : 'var(--ink-mid)';
  // The Setup wizard holds the plugin download, so it stays highlighted even when
  // inactive (accent-tinted + bold) instead of fading to plain white — easy to spot.
  const wiz = document.getElementById('wp-tab-wizard');
  wiz.style.background = tab === 'wizard' ? 'var(--accent)' : '#E8F0F7';
  wiz.style.color      = tab === 'wizard' ? 'var(--white)'  : 'var(--accent)';
  wiz.style.fontWeight = '600';
  // The tour is the marketing showpiece, so it also stays accent-tinted when inactive.
  const tourTab = document.getElementById('wp-tab-tour');
  if (tourTab) {
    tourTab.style.background = tab === 'tour' ? 'var(--accent)' : '#E8F0F7';
    tourTab.style.color      = tab === 'tour' ? 'var(--white)'  : 'var(--accent)';
    tourTab.style.fontWeight = '600';
  }
  if (tab === 'tour' && typeof renderTour === 'function') { tourState.target = 'wp-tour'; tourState.mode = 'public'; tourState.page = 'home'; renderTour(); }
}

function renderWordPress() {
  renderWpLessons();
  renderWpWizard();
}

function renderWpLessons() {
  const grid = document.getElementById('wp-lesson-grid');
  if (!grid) return;
  const wpCompleted = JSON.parse(localStorage.getItem('wp_lessons') || '[]');
  grid.innerHTML = WP_LESSONS.map(lesson => {
    const done = wpCompleted.includes(lesson.id);
    return '<div class="edu-card ' + (done ? 'completed' : '') + '" onclick="openWpLesson(\'' + lesson.id + '\')">' +
      '<div class="edu-check"><svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 5l2.5 2.5L8 2.5"/></svg></div>' +
      '<div class="edu-card-icon edu-icon-green">' + lesson.icon + '</div>' +
      '<h3>' + lesson.title + '</h3><p>' + lesson.desc + '</p>' +
      '<div class="edu-card-footer"><span class="edu-pill ' + (done ? 'pill-done' : 'pill-green') + '">' + (done ? 'Completed' : 'WordPress') + '</span>' +
      '<span class="edu-time"><svg width="11" height="11" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="6" cy="6" r="5"/><path d="M6 3v3l2 1.5"/></svg>' + lesson.time + '</span></div></div>';
  }).join('');
}

function openWpLesson(lessonId) {
  const lesson = WP_LESSONS.find(l => l.id === lessonId);
  if (!lesson) return;
  const wpCompleted = JSON.parse(localStorage.getItem('wp_lessons') || '[]');
  const done = wpCompleted.includes(lessonId);
  const main = document.getElementById('wp-learn');
  main.innerHTML = '<button class="lesson-back" onclick="renderWordPress();showWpTab(\'learn\')">' +
    '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M9 2L4 7l5 5"/></svg> Back to lessons</button>' +
    '<div class="lesson-header"><div class="lesson-meta"><span class="edu-pill pill-green">WordPress</span>' +
    '<span class="edu-time"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="6" cy="6" r="5"/><path d="M6 3v3l2 1.5"/></svg>' + lesson.time + '</span></div>' +
    '<h1>' + lesson.title + '</h1><p class="lesson-desc">' + lesson.desc + '</p></div>' +
    '<div class="lesson-body">' + lesson.body + '</div>' +
    '<div class="lesson-action-bar"><div></div>' +
    '<button class="app-btn ' + (done ? 'app-btn-outline' : 'app-btn-green') + '" onclick="markWpLessonComplete(\'' + lessonId + '\')" id="wp-complete-btn">' +
    (done ? '✓ Completed' : 'Mark as complete ✓') + '</button></div>';
}

function markWpLessonComplete(lessonId) {
  const wpCompleted = JSON.parse(localStorage.getItem('wp_lessons') || '[]');
  if (!wpCompleted.includes(lessonId)) {
    wpCompleted.push(lessonId);
    localStorage.setItem('wp_lessons', JSON.stringify(wpCompleted));
    toast('Lesson complete! Great work.');
    const btn = document.getElementById('wp-complete-btn');
    if (btn) { btn.textContent = '✓ Completed'; btn.className = 'app-btn app-btn-outline'; }
  }
}

function renderWpWizard() {
  const list = document.getElementById('wp-steps-list');
  if (!list) return;
  wpCompletedSteps = JSON.parse(localStorage.getItem('wp_steps') || '[]');
  list.innerHTML = WP_STEPS.map((step, i) => {
    const done = wpCompletedSteps.includes(step.id);
    return '<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--ink-faint)">' +
      '<div onclick="toggleWpStep(\'' + step.id + '\')" style="width:20px;height:20px;border-radius:50%;border:2px solid ' + (done ? 'var(--accent)' : 'var(--ink-faint)') + ';background:' + (done ? 'var(--accent)' : 'transparent') + ';cursor:pointer;flex-shrink:0;margin-top:1px;display:flex;align-items:center;justify-content:center">' +
      (done ? '<svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 5l2.5 2.5L8 2.5"/></svg>' : '') +
      '</div>' +
      '<div style="flex:1">' +
      '<div style="font-size:13.5px;font-weight:500;color:' + (done ? 'var(--ink-soft)' : 'var(--ink)') + ';text-decoration:' + (done ? 'line-through' : 'none') + '">' + (i+1) + '. ' + step.label + '</div>' +
      '<div style="font-size:12px;color:var(--ink-soft);margin-top:3px;line-height:1.5">' + step.detail + '</div>' +
      (step.action ? '<div>' + step.action + '</div>' : '') +
      '</div></div>';
  }).join('');
  updateWpProgress();
}

function toggleWpStep(stepId) {
  wpCompletedSteps = JSON.parse(localStorage.getItem('wp_steps') || '[]');
  const idx = wpCompletedSteps.indexOf(stepId);
  if (idx === -1) wpCompletedSteps.push(stepId);
  else wpCompletedSteps.splice(idx, 1);
  localStorage.setItem('wp_steps', JSON.stringify(wpCompletedSteps));
  renderWpWizard();
}

function updateWpProgress() {
  const total = WP_STEPS.length;
  const done  = wpCompletedSteps.length;
  const pct   = Math.round((done / total) * 100);
  const bar = document.getElementById('wp-wizard-bar');
  const pctEl = document.getElementById('wp-wizard-pct');
  if (bar)   bar.style.width   = pct + '%';
  if (pctEl) pctEl.textContent = pct + '%';
}

// ══════════════════════════════════════
// SHOPIFY (Sales Channels integration)
// ══════════════════════════════════════
let shopifyAccount = null;

async function loadShopifyStatus() {
  const row  = document.getElementById('shopify-row');
  const meta = document.getElementById('shopify-row-meta');
  const acts = document.getElementById('shopify-row-actions');
  // Don't require shopify-row-badge — that id only exists in the initial HTML
  // and gets removed when acts.innerHTML is first overwritten.
  if (!row || !meta || !acts) return;
  try {
    const data = await api('/shopify.php?action=status');
    if (!data.success) throw new Error(data.message || 'status failed');
    shopifyAccount = data.account || null;
    const state = data.state || 'unconfigured';

    const helpBtn = '<button class="app-btn-help" onclick="showSetupHelp(\'shopify\')" title="Step-by-step setup instructions"><span class="help-q">?</span>Setup help</button>';

    if (state === 'unconfigured' || !shopifyAccount) {
      meta.textContent = 'Direct store';
      badge.className = 'badge badge-gray';
      badge.textContent = 'Not connected';
      badge.style.display = '';
      acts.innerHTML = helpBtn
        + '<span class="badge badge-gray">Not connected</span>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="showShopifyConnect()">Connect</button>';
      return;
    }

    const synced = data.synced_book_count || 0;
    const shop   = shopifyAccount.shop_domain || '';
    const errMsg = shopifyAccount.last_error ? ' · last error: ' + shopifyAccount.last_error : '';
    meta.textContent = shop + ' · ' + synced + ' book' + (synced === 1 ? '' : 's') + ' synced' + (state === 'error' ? errMsg : '');

    const badgeClass = state === 'verified' ? 'badge-green' : (state === 'error' ? 'badge-red' : 'badge-amber');
    const badgeText  = state === 'verified' ? 'Connected' : (state === 'error' ? 'Error' : 'Unverified');

    acts.innerHTML = helpBtn
      + '<span class="badge ' + badgeClass + '">' + badgeText + '</span>'
      + '<button class="app-btn app-btn-outline app-btn-sm" onclick="syncAllBooksToShopify()" title="Push every book to Shopify as a draft product (existing products get updated)">Sync all books</button>'
      + '<button class="app-btn app-btn-outline app-btn-sm" onclick="verifyShopify()">Verify</button>'
      + '<button class="app-btn app-btn-outline app-btn-sm" onclick="disconnectShopify()">Disconnect</button>';
  } catch (e) {
    meta.textContent = 'Direct store';
    acts.innerHTML = '<button class="app-btn-help" onclick="showSetupHelp(\'shopify\')"><span class="help-q">?</span>Setup help</button>'
      + '<span class="badge badge-red">Status failed</span>'
      + '<button class="app-btn app-btn-outline app-btn-sm" onclick="loadShopifyStatus()">Retry</button>';
  }
}

function showShopifyConnect() {
  document.getElementById('shopify-shop-domain').value = '';
  const err = document.getElementById('shopify-connect-error');
  if (err) { err.style.display = 'none'; err.textContent = ''; }
  document.getElementById('shopify-connect-backdrop').style.display = 'flex';
  setTimeout(() => document.getElementById('shopify-shop-domain').focus(), 50);
}

function closeShopifyConnect() {
  document.getElementById('shopify-connect-backdrop').style.display = 'none';
}

async function startShopifyAuthorize() {
  const shop = document.getElementById('shopify-shop-domain').value.trim();
  const err  = document.getElementById('shopify-connect-error');
  const btn  = document.getElementById('shopify-connect-submit');
  if (!shop) {
    err.textContent = 'Store domain is required.';
    err.style.display = 'block';
    return;
  }
  if (!/^[a-z0-9][a-z0-9-]*\.myshopify\.com$/i.test(shop)) {
    err.textContent = 'Use the .myshopify.com form (not your custom domain).';
    err.style.display = 'block';
    return;
  }
  btn.disabled = true; btn.textContent = 'Redirecting…';
  try {
    const data = await api('/shopify_oauth.php?action=authorize', {
      method: 'POST',
      body: JSON.stringify({ shop_domain: shop })
    });
    if (!data.success || !data.authorize_url) {
      err.textContent = data.message || 'Could not start the connect flow.';
      err.style.display = 'block';
      btn.disabled = false; btn.textContent = 'Continue to Shopify ↗';
      return;
    }
    // Full-page navigation to Shopify's consent screen. We come back via
    // /api/callback_shopify.php, which redirects to the app with ?shopify=connected.
    window.location.href = data.authorize_url;
  } catch (e) {
    err.textContent = 'Network error. Try again.';
    err.style.display = 'block';
    btn.disabled = false; btn.textContent = 'Continue to Shopify ↗';
  }
}

async function verifyShopify() {
  const data = await api('/shopify.php?action=verify', { method: 'POST', body: '{}' });
  if (data.success) toast('Verified · ' + (data.shop_name || ''));
  else toast(data.message || 'Verify failed', true);
  loadShopifyStatus();
}

async function disconnectShopify() {
  if (!confirm('Disconnect from Shopify? Synced products stay in Shopify; we just forget the credentials and clear sync pointers on your books.')) return;
  const data = await api('/shopify.php?action=disconnect', { method: 'POST', body: '{}' });
  if (data.success) toast('Disconnected.');
  else toast(data.message || 'Disconnect failed', true);
  loadShopifyStatus();
  if (typeof loadBooks === 'function') loadBooks();
}

async function syncAllBooksToShopify() {
  if (!shopifyAccount) return toast('Connect a Shopify store first', true);
  const bookCount = (booksList || []).length;
  if (bookCount === 0) return toast('Add books before syncing', true);
  const verb = bookCount === 1 ? 'this book' : ('all ' + bookCount + ' books');
  if (!confirm('Sync ' + verb + ' to Shopify? New books are created as drafts; existing synced books get their title/blurb/cover updated (price and Draft/Active status preserved).')) return;

  // Disable the button while the batch runs. The row is re-rendered on each
  // loadShopifyStatus(), so we work on the live element.
  const acts = document.getElementById('shopify-row-actions');
  const btns = acts ? acts.querySelectorAll('button') : [];
  btns.forEach(b => { b.disabled = true; });
  const syncBtn = Array.from(btns).find(b => /sync all/i.test(b.textContent));
  if (syncBtn) syncBtn.textContent = 'Syncing…';

  try {
    const data = await api('/shopify.php?action=sync_all_books', { method: 'POST', body: '{}' });
    if (!data.success) {
      toast(data.message || 'Bulk sync failed', true);
      return;
    }
    toast(data.message || 'Bulk sync complete.');
    if (data.failures && data.failures.length) {
      // Show a concise inline summary of which books failed and why.
      const details = data.failures.map(f => '• ' + (f.title || 'Book #' + f.book_id) + ' — ' + f.error).join('\n');
      setTimeout(() => alert('Some books were skipped:\n\n' + details), 600);
    }
  } catch (e) {
    toast('Network error during bulk sync', true);
  } finally {
    loadShopifyStatus();
    if (typeof loadBooks === 'function') loadBooks();
  }
}

async function syncBookToShopify(bookId) {
  if (!bookId) return;
  if (!shopifyAccount) {
    toast('Connect a Shopify store first (Sales Channels)', true);
    return;
  }
  const data = await api('/shopify.php?action=sync_book', {
    method: 'POST',
    body: JSON.stringify({ book_id: bookId })
  });
  if (!data.success) {
    toast(data.message || 'Sync failed', true);
    return;
  }
  toast(data.message || 'Synced to Shopify.');
  loadBooks();
}

async function syncCurrentBookToShopify() {
  const btn    = document.getElementById('book-shopify-btn');
  const label  = document.getElementById('book-shopify-btn-label');
  const bookId = parseInt(btn?.dataset?.bookId || document.getElementById('book-id').value || '0', 10);
  if (!bookId) return toast('Save the book first before syncing', true);
  if (!shopifyAccount) return toast('Connect a Shopify store first (Sales Channels)', true);
  const originalLabel = label.textContent;
  btn.disabled = true; label.textContent = 'Syncing…';
  try {
    const data = await api('/shopify.php?action=sync_book', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId })
    });
    if (!data.success) {
      toast(data.message || 'Sync failed', true);
      return;
    }
    toast(data.message || 'Synced to Shopify.');
    // Refresh the local book list, then re-render this form to pick up the new URL.
    await loadBooks();
    const fresh = (booksList || []).find(b => b.id == bookId);
    if (fresh) {
      const link = document.getElementById('book-shopify-link');
      if (link && fresh.shopify_product_url) {
        link.href = fresh.shopify_product_url; link.style.display = 'inline';
      }
      label.textContent = 'Re-sync to Shopify';
    } else {
      label.textContent = originalLabel;
    }
  } catch (e) {
    toast('Network error syncing to Shopify', true);
    label.textContent = originalLabel;
  } finally {
    btn.disabled = false;
  }
}

// WOOCOMMERCE (Sales Channels integration)
// ══════════════════════════════════════
let wooAccount = null;

async function loadWooStatus() {
  const row  = document.getElementById('woo-row');
  const meta = document.getElementById('woo-row-meta');
  const acts = document.getElementById('woo-row-actions');
  // Don't require woo-row-badge — that id only exists in the initial HTML
  // and gets removed when acts.innerHTML is first overwritten.
  if (!row || !meta || !acts) return;
  try {
    const data = await api('/woocommerce.php?action=status');
    if (!data.success) throw new Error(data.message || 'status failed');
    wooAccount = data.account || null;
    const state = data.state || 'unconfigured';

    if (state === 'unconfigured' || !wooAccount) {
      meta.textContent = 'Self-hosted WordPress store';
      acts.innerHTML = '<span class="badge badge-gray">Not connected</span>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="showWooConnect()">Connect</button>';
      return;
    }

    const synced  = data.synced_book_count || 0;
    const storeUrl = wooAccount.store_url || '';
    const host     = (() => { try { return new URL(storeUrl).host; } catch (e) { return storeUrl; } })();
    const errMsg  = wooAccount.last_error ? ' · last error: ' + wooAccount.last_error : '';
    meta.textContent = host + ' · ' + synced + ' book' + (synced === 1 ? '' : 's') + ' synced' + (state === 'error' ? errMsg : '');

    const badgeClass = state === 'verified' ? 'badge-green' : (state === 'error' ? 'badge-red' : 'badge-amber');
    const badgeText  = state === 'verified' ? 'Connected' : (state === 'error' ? 'Error' : 'Unverified');

    acts.innerHTML = '<span class="badge ' + badgeClass + '">' + badgeText + '</span>'
      + '<button class="app-btn app-btn-outline app-btn-sm" onclick="syncAllBooksToWoo()" title="Push every book to WooCommerce as a draft product (existing products get updated)">Sync all books</button>'
      + '<button class="app-btn app-btn-outline app-btn-sm" onclick="verifyWoo()">Verify</button>'
      + '<button class="app-btn app-btn-outline app-btn-sm" onclick="disconnectWoo()">Disconnect</button>';
  } catch (e) {
    meta.textContent = 'Self-hosted WordPress store';
    acts.innerHTML = '<span class="badge badge-red">Status failed</span>'
      + '<button class="app-btn app-btn-outline app-btn-sm" onclick="loadWooStatus()">Retry</button>';
  }
}

function showWooConnect() {
  document.getElementById('woo-store-url').value       = '';
  document.getElementById('woo-consumer-key').value    = '';
  document.getElementById('woo-consumer-secret').value = '';
  const err = document.getElementById('woo-connect-error');
  if (err) { err.style.display = 'none'; err.textContent = ''; }
  document.getElementById('woo-connect-backdrop').style.display = 'flex';
  setTimeout(() => document.getElementById('woo-store-url').focus(), 50);
}

function closeWooConnect() {
  document.getElementById('woo-connect-backdrop').style.display = 'none';
}

async function connectWoo() {
  const storeUrl = document.getElementById('woo-store-url').value.trim();
  const key      = document.getElementById('woo-consumer-key').value.trim();
  const secret   = document.getElementById('woo-consumer-secret').value.trim();
  const err      = document.getElementById('woo-connect-error');
  const btn      = document.getElementById('woo-connect-submit');
  err.style.display = 'none'; err.textContent = '';

  if (!storeUrl)              { err.textContent = 'Store URL is required.'; err.style.display = 'block'; return; }
  if (!/^https:\/\//i.test(storeUrl) && !/^[^/]+\.[^/]+/.test(storeUrl)) {
    err.textContent = 'Store URL must look like https://yourstore.com';
    err.style.display = 'block'; return;
  }
  if (!key || !/^ck_[a-f0-9]+$/i.test(key))      { err.textContent = 'Consumer Key should start with "ck_".'; err.style.display = 'block'; return; }
  if (!secret || !/^cs_[a-f0-9]+$/i.test(secret)){ err.textContent = 'Consumer Secret should start with "cs_".'; err.style.display = 'block'; return; }

  btn.disabled = true; btn.textContent = 'Connecting…';
  try {
    const data = await api('/woocommerce.php?action=connect', {
      method: 'POST',
      body: JSON.stringify({ store_url: storeUrl, consumer_key: key, consumer_secret: secret })
    });
    if (!data.success) {
      err.textContent = data.message || 'Could not connect.';
      err.style.display = 'block';
      btn.disabled = false; btn.textContent = 'Connect';
      return;
    }
    closeWooConnect();
    toast('WooCommerce store connected.');
    loadWooStatus();
  } catch (e) {
    err.textContent = 'Network error. Try again.';
    err.style.display = 'block';
    btn.disabled = false; btn.textContent = 'Connect';
  }
}

async function verifyWoo() {
  const data = await api('/woocommerce.php?action=verify', { method: 'POST', body: '{}' });
  if (data.success) toast('Verified · ' + (data.store_url || ''));
  else toast(data.message || 'Verify failed', true);
  loadWooStatus();
}

async function disconnectWoo() {
  if (!confirm('Disconnect from WooCommerce? Synced products stay in WooCommerce; we just forget the credentials and clear sync pointers on your books.')) return;
  const data = await api('/woocommerce.php?action=disconnect', { method: 'POST', body: '{}' });
  if (data.success) toast('Disconnected.');
  else toast(data.message || 'Disconnect failed', true);
  loadWooStatus();
  if (typeof loadBooks === 'function') loadBooks();
}

async function syncAllBooksToWoo() {
  if (!wooAccount) return toast('Connect a WooCommerce store first', true);
  const bookCount = (booksList || []).length;
  if (bookCount === 0) return toast('Add books before syncing', true);
  const verb = bookCount === 1 ? 'this book' : ('all ' + bookCount + ' books');
  if (!confirm('Sync ' + verb + ' to WooCommerce? New books are created as drafts; existing synced books get their title/blurb/cover updated (price and Draft/Publish status preserved).')) return;

  const acts = document.getElementById('woo-row-actions');
  const btns = acts ? acts.querySelectorAll('button') : [];
  btns.forEach(b => { b.disabled = true; });
  const syncBtn = Array.from(btns).find(b => /sync all/i.test(b.textContent));
  if (syncBtn) syncBtn.textContent = 'Syncing…';

  try {
    const data = await api('/woocommerce.php?action=sync_all_books', { method: 'POST', body: '{}' });
    if (!data.success) {
      toast(data.message || 'Bulk sync failed', true);
      return;
    }
    toast(data.message || 'Bulk sync complete.');
    if (data.failures && data.failures.length) {
      const details = data.failures.map(f => '• ' + (f.title || 'Book #' + f.book_id) + ' — ' + f.error).join('\n');
      setTimeout(() => alert('Some books were skipped:\n\n' + details), 600);
    }
  } catch (e) {
    toast('Network error during bulk sync', true);
  } finally {
    loadWooStatus();
    if (typeof loadBooks === 'function') loadBooks();
  }
}

async function syncBookToWoo(bookId) {
  if (!bookId) return;
  if (!wooAccount) {
    toast('Connect a WooCommerce store first (Sales Channels)', true);
    return;
  }
  const data = await api('/woocommerce.php?action=sync_book', {
    method: 'POST',
    body: JSON.stringify({ book_id: bookId })
  });
  if (!data.success) {
    toast(data.message || 'Sync failed', true);
    return;
  }
  toast(data.message || 'Synced to WooCommerce.');
  loadBooks();
}

async function syncCurrentBookToWoo() {
  const btn    = document.getElementById('book-woo-btn');
  const label  = document.getElementById('book-woo-btn-label');
  const bookId = parseInt(btn?.dataset?.bookId || document.getElementById('book-id').value || '0', 10);
  if (!bookId) return toast('Save the book first before syncing', true);
  if (!wooAccount) return toast('Connect a WooCommerce store first (Sales Channels)', true);
  const originalLabel = label.textContent;
  btn.disabled = true; label.textContent = 'Syncing…';
  try {
    const data = await api('/woocommerce.php?action=sync_book', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId })
    });
    if (!data.success) {
      toast(data.message || 'Sync failed', true);
      return;
    }
    toast(data.message || 'Synced to WooCommerce.');
    await loadBooks();
    const fresh = (booksList || []).find(b => b.id == bookId);
    if (fresh) {
      const link = document.getElementById('book-woo-link');
      if (link && fresh.woocommerce_product_url) {
        link.href = fresh.woocommerce_product_url; link.style.display = 'inline';
      }
      label.textContent = 'Re-sync to WooCommerce';
    } else {
      label.textContent = originalLabel;
    }
  } catch (e) {
    toast('Network error syncing to WooCommerce', true);
    label.textContent = originalLabel;
  } finally {
    btn.disabled = false;
  }
}

// Publish the current book to the author's connected WordPress site. Mirrors
// syncCurrentBookToWoo. The app owns book content; the site keeps its own
// price/buy-links/reviews. Re-publishing updates the same product+page.
async function publishCurrentBookToWebsite() {
  const btn    = document.getElementById('book-website-btn');
  const label  = document.getElementById('book-website-btn-label');
  const bookId = parseInt(btn?.dataset?.bookId || document.getElementById('book-id').value || '0', 10);
  if (!bookId) return toast('Save the book first before publishing', true);
  const originalLabel = label.textContent;
  btn.disabled = true; label.textContent = 'Publishing…';
  try {
    const data = await api('/wordpress.php?action=publish_book', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId })
    });
    if (!data.success) {
      toast(data.message || 'Publish failed', true);
      label.textContent = originalLabel;
      return;
    }
    toast(data.message || 'Published to your website.');
    const link = document.getElementById('book-website-link');
    if (link && data.page_url) { link.href = data.page_url; link.style.display = 'inline'; }
    label.textContent = 'Update on my website';
  } catch (e) {
    toast('Network error publishing to your website', true);
    label.textContent = originalLabel;
  } finally {
    btn.disabled = false;
  }
}

// MY BOOKS
// ══════════════════════════════════════
let booksList = [];

async function loadBooks() {
  const data = await api('/books.php?action=list');
  const grid = document.getElementById('books-grid');
  if (data.success && data.books && data.books.length) {
    booksList = data.books;
    window._books = booksList;
    updateBookSelector(booksList);
  } else {
    booksList = [];
    window._books = [];
    updateBookSelector([]);
  }
  if (!grid) return;
  if (!data.success || !data.books || !data.books.length) {
    grid.innerHTML = '<div class="empty" style="grid-column:1/-1">No books added yet — click above to add your first title</div>';
    return;
  }
  grid.innerHTML = booksList.map(b => {
    const cover = b.cover_url
      ? '<img src="' + b.cover_url + '" alt="' + b.title + '" style="width:100%;height:100%;object-fit:cover">'
      : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--ink-soft);font-size:12px">No cover</div>';
    const statusBadge = b.status === 'published' ? 'badge-green' : b.status === 'draft' ? 'badge-amber' : 'badge-gray';
    const statusLabel = b.status === 'published' ? 'Published' : b.status === 'draft' ? 'Coming soon' : 'Out of print';
    const amazonLink = b.amazon_url
      ? '<a href="' + b.amazon_url + '" target="_blank" rel="noopener" onclick="event.stopPropagation()" style="display:block;font-size:11.5px;color:var(--accent);margin-top:8px;text-decoration:none;font-weight:500">Buy on Amazon ↗</a>'
      : '';
    const shopifyLink = b.shopify_product_url
      ? '<a href="' + b.shopify_product_url + '" target="_blank" rel="noopener" onclick="event.stopPropagation()" style="display:block;font-size:11.5px;color:#96BF48;margin-top:4px;text-decoration:none;font-weight:500">Synced to Shopify ↗</a>'
      : '';
    const wooLink = b.woocommerce_product_url
      ? '<a href="' + b.woocommerce_product_url + '" target="_blank" rel="noopener" onclick="event.stopPropagation()" style="display:block;font-size:11.5px;color:#7F54B3;margin-top:4px;text-decoration:none;font-weight:500">Synced to WooCommerce ↗</a>'
      : '';
    return '<div class="book-card" onclick="editBook(' + b.id + ')">' +
      '<div class="book-card-cover">' + cover + '</div>' +
      '<div class="book-card-body">' +
        '<div class="book-card-title">' + b.title + '</div>' +
        '<div class="book-card-meta">' + (b.genre || '') + (b.published_at ? ' · ' + new Date(b.published_at).getFullYear() : '') + '</div>' +
        '<div class="book-card-footer"><span class="badge ' + statusBadge + '">' + statusLabel + '</span>' +
        '<span style="font-size:11px;color:var(--ink-soft)">' + (b.formats || '') + '</span></div>' +
        amazonLink +
        shopifyLink +
        wooLink +
      '</div></div>';
  }).join('');
}

function updateBookSelector(books) {
  const sel = document.getElementById('bookSelector');
  if (!sel) return;
  sel.innerHTML = '<option value="">Select a book…</option>' +
    books.map(b => '<option value="' + b.id + '">' + b.title + '</option>').join('');
}

function showBookForm(book) {
  document.getElementById('books-list-view').style.display = 'none';
  document.getElementById('books-form-view').style.display = 'block';
  document.getElementById('book-form-title').textContent = book ? 'Edit book' : 'Add a book';
  document.getElementById('book-id').value          = book ? book.id : '';
  document.getElementById('book-title').value       = book ? (book.title || '') : '';
  document.getElementById('book-author').value      = book ? (book.author || '') : (currentUser ? (currentUser.pen_name || currentUser.full_name || '') : '');
  document.getElementById('book-subtitle').value    = book ? (book.subtitle || '') : '';
  document.getElementById('book-isbn').value             = book ? (book.isbn || '') : '';
  document.getElementById('book-amazon-asin').value      = book ? (book.amazon_asin || '') : '';
  document.getElementById('book-amazon-url').value       = book ? (book.amazon_url || '') : '';
  document.getElementById('book-pages').value            = book ? (book.page_count || '') : '';
  document.getElementById('book-page-size').value        = book ? (book.page_size || '') : '';
  document.getElementById('book-genre').value            = book ? (book.genre || '') : '';
  document.getElementById('book-publisher').value        = book ? (book.publisher || '') : '';
  document.getElementById('book-pubdate').value          = book ? (book.published_at || '') : '';
  document.getElementById('book-status').value           = book ? (book.status || 'published') : 'published';
  document.getElementById('book-description').value      = book ? (book.description || '') : '';
  document.getElementById('book-author-bio').value       = book ? (book.author_bio || '') : '';
  document.getElementById('book-audience').value         = book ? (book.audience || '') : '';
  document.getElementById('book-comps').value            = book ? (book.comparable_titles || '') : '';
  document.getElementById('book-keywords').value         = book ? (book.keywords || '') : '';
  document.getElementById('book-tagline').value          = book ? (book.tagline || '') : '';
  document.getElementById('book-logline').value          = book ? (book.logline || '') : '';
  document.getElementById('book-cover-url').value   = book ? (book.cover_url || '') : '';
  document.getElementById('book-delete-btn').style.display = book ? 'inline-flex' : 'none';
  // Shopify sync controls — only shown for existing books, and only if connected
  const shopifyBtn   = document.getElementById('book-shopify-btn');
  const shopifyLink  = document.getElementById('book-shopify-link');
  const shopifyLabel = document.getElementById('book-shopify-btn-label');
  if (shopifyBtn && shopifyLink && shopifyLabel) {
    if (book && shopifyAccount) {
      shopifyBtn.style.display = 'inline-flex';
      shopifyBtn.dataset.bookId = book.id;
      shopifyLabel.textContent = book.shopify_product_id ? 'Re-sync to Shopify' : 'Sync to Shopify';
      if (book.shopify_product_url) {
        shopifyLink.style.display = 'inline';
        shopifyLink.href = book.shopify_product_url;
      } else {
        shopifyLink.style.display = 'none';
        shopifyLink.removeAttribute('href');
      }
    } else {
      shopifyBtn.style.display = 'none';
      shopifyLink.style.display = 'none';
    }
  }
  // WooCommerce sync controls — same shape as Shopify
  const wooBtn   = document.getElementById('book-woo-btn');
  const wooLink  = document.getElementById('book-woo-link');
  const wooLabel = document.getElementById('book-woo-btn-label');
  if (wooBtn && wooLink && wooLabel) {
    if (book && wooAccount) {
      wooBtn.style.display = 'inline-flex';
      wooBtn.dataset.bookId = book.id;
      wooLabel.textContent = book.woocommerce_product_id ? 'Re-sync to WooCommerce' : 'Sync to WooCommerce';
      if (book.woocommerce_product_url) {
        wooLink.style.display = 'inline';
        wooLink.href = book.woocommerce_product_url;
      } else {
        wooLink.style.display = 'none';
        wooLink.removeAttribute('href');
      }
    } else {
      wooBtn.style.display = 'none';
      wooLink.style.display = 'none';
    }
  }
  // Publish-to-website (WordPress) — shown for any saved book. If no site is
  // connected yet, the click returns a clear "connect first" message (the
  // connected-state flag isn't reliably available here, so we don't gate on it).
  const webBtn   = document.getElementById('book-website-btn');
  const webLabel = document.getElementById('book-website-btn-label');
  const webLink  = document.getElementById('book-website-link');
  if (webBtn && webLabel) {
    if (book && book.id) {
      webBtn.style.display = 'inline-flex';
      webBtn.dataset.bookId = book.id;
      webLabel.textContent = 'Publish to my website';
    } else {
      webBtn.style.display = 'none';
      if (webLink) webLink.style.display = 'none';
    }
  }
  document.getElementById('tagline-results').style.display = 'none';
  document.getElementById('logline-results').style.display = 'none';
  document.getElementById('cover-upload-status').textContent = 'JPG, PNG or WebP — max 5MB';

  const img = document.getElementById('cover-img');
  const placeholder = document.getElementById('cover-placeholder');
  if (book && book.cover_url) {
    img.src = book.cover_url; img.style.display = 'block';
    placeholder.style.display = 'none';
  } else {
    img.style.display = 'none'; img.src = '';
    placeholder.style.display = 'flex';
  }

  // Set format checkboxes
  const formats = book ? (book.formats || '').split(',').map(f => f.trim()) : [];
  document.querySelectorAll('.format-check').forEach(cb => {
    cb.checked = formats.includes(cb.value);
  });

  // Author photo auto-fills from the user's saved photo (per-user asset).
  populateAuthorPhoto();

  document.getElementById('main').scrollTop = 0;
}

function hideBookForm() {
  document.getElementById('books-form-view').style.display = 'none';
  document.getElementById('books-list-view').style.display = 'block';
  document.getElementById('main').scrollTop = 0;
}

function editBook(bookId) {
  const book = booksList.find(b => b.id == bookId);
  if (book) showBookForm(book);
}

async function handleCoverUpload(input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 10 * 1024 * 1024) { toast('File must be under 10MB', true); return; }

  const isPdf  = file.type === 'application/pdf';
  const isImage = file.type.startsWith('image/');
  if (!isPdf && !isImage) { toast('Please select an image or PDF file', true); return; }

  const status = document.getElementById('cover-upload-status');
  status.textContent = 'Uploading…';

  const formData = new FormData();
  formData.append('cover', file);

  const headers = {};
  if (authToken) headers['X-Auth-Token'] = authToken;

  try {
    const res  = await fetch('/api/upload.php', { method: 'POST', headers, body: formData });
    const data = await res.json();
    if (data.success) {
      document.getElementById('book-cover-url').value = data.url;
      const img = document.getElementById('cover-img');
      const placeholder = document.getElementById('cover-placeholder');
      if (isPdf) {
        img.style.display = 'none';
        placeholder.style.display = 'flex';
        placeholder.innerHTML = '<div style="text-align:center"><div style="font-size:36px;margin-bottom:8px">📄</div><div style="font-size:11px;color:var(--ink-mid)">PDF cover uploaded</div><div style="font-size:10px;color:var(--ink-soft);margin-top:3px">' + file.name + '</div></div>';
        status.textContent = 'PDF cover uploaded successfully';
      } else {
        img.onload = function() { img.style.display = 'block'; placeholder.style.display = 'none'; };
        img.onerror = function() { status.textContent = 'Uploaded — preview unavailable but file is saved'; };
        img.src = data.url + '?t=' + Date.now();
        status.textContent = 'Cover uploaded successfully';
      }
    } else {
      status.textContent = data.message || 'Upload failed';
      toast(data.message || 'Upload failed', true);
    }
  } catch(e) {
    status.textContent = 'Upload failed — try again';
    toast('Upload failed', true);
  }
}

// Author photo upload. The photo is a per-USER asset (not per-book), so it's
// saved straight to the profile on upload — that way it persists immediately
// and auto-fills on every other book form. Reuses upload.php (field "cover").
async function handleAuthorPhotoUpload(input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 10 * 1024 * 1024) { toast('Photo must be under 10MB', true); return; }
  if (!file.type.startsWith('image/')) { toast('Please select an image', true); return; }

  const status = document.getElementById('author-photo-status');
  status.textContent = 'Uploading…';

  const formData = new FormData();
  formData.append('cover', file);
  const headers = {};
  if (authToken) headers['X-Auth-Token'] = authToken;

  try {
    const res  = await fetch('/api/upload.php', { method: 'POST', headers, body: formData });
    const data = await res.json();
    if (!data.success) { status.textContent = data.message || 'Upload failed'; toast(data.message || 'Upload failed', true); return; }

    const url = data.url;
    document.getElementById('author-photo-url').value = url;
    const img = document.getElementById('author-photo-img');
    const ph  = document.getElementById('author-photo-placeholder');
    img.onload = function() { img.style.display = 'block'; ph.style.display = 'none'; };
    img.src = url + '?t=' + Date.now();

    // Persist to the user profile right away.
    const save = await api('/auth.php?action=set_author_photo', {
      method: 'POST',
      body: JSON.stringify({ author_photo_url: url }),
    });
    if (save && save.success) {
      if (currentUser) currentUser.author_photo_url = url;
      status.textContent = 'Author photo saved';
    } else {
      status.textContent = 'Uploaded, but saving to your profile failed — try again';
    }
  } catch (e) {
    status.textContent = 'Upload failed — try again';
    toast('Upload failed', true);
  }
}

// Fill the book form's author-photo preview from the user's saved photo.
// Called when the book form opens — gives the "auto-fills on subsequent
// books" behavior. The user can still replace it (handleAuthorPhotoUpload).
function populateAuthorPhoto() {
  const url = (currentUser && currentUser.author_photo_url) || '';
  const hidden = document.getElementById('author-photo-url');
  const img = document.getElementById('author-photo-img');
  const ph  = document.getElementById('author-photo-placeholder');
  const status = document.getElementById('author-photo-status');
  if (!hidden) return;
  hidden.value = url;
  if (url) {
    img.onload = function() { img.style.display = 'block'; ph.style.display = 'none'; };
    img.src = url + '?t=' + Date.now();
    if (status) status.textContent = 'Using your saved author photo — upload to replace it';
  } else {
    img.style.display = 'none';
    ph.style.display = 'flex';
    if (status) status.textContent = 'JPG, PNG or WebP — a clear headshot works best';
  }
}

// Single source of truth for the book form's field values, shared by the
// explicit Save and the autosave-on-blur below so they can never drift apart.
function collectBookForm() {
  const formats = Array.from(document.querySelectorAll('.format-check:checked')).map(cb => cb.value).join(', ');
  const bookId  = document.getElementById('book-id').value;
  return {
    id:          bookId || null,
    title:       document.getElementById('book-title').value.trim(),
    author:      document.getElementById('book-author').value.trim(),
    subtitle:    document.getElementById('book-subtitle').value.trim(),
    isbn:               document.getElementById('book-isbn').value.trim(),
    amazon_asin:        document.getElementById('book-amazon-asin').value.trim(),
    amazon_url:         document.getElementById('book-amazon-url').value.trim(),
    page_count:         document.getElementById('book-pages').value.trim(),
    page_size:          document.getElementById('book-page-size').value.trim(),
    genre:              document.getElementById('book-genre').value.trim(),
    publisher:          document.getElementById('book-publisher').value.trim(),
    published_at:       document.getElementById('book-pubdate').value,
    status:             document.getElementById('book-status').value,
    description:        document.getElementById('book-description').value.trim(),
    author_bio:         document.getElementById('book-author-bio').value.trim(),
    audience:           document.getElementById('book-audience').value.trim(),
    comparable_titles:  document.getElementById('book-comps').value.trim(),
    keywords:           document.getElementById('book-keywords').value.trim(),
    tagline:            document.getElementById('book-tagline').value.trim(),
    logline:            document.getElementById('book-logline').value.trim(),
    cover_url:          document.getElementById('book-cover-url').value,
    formats:            formats,
  };
}

// Autosave edits as focus leaves a field (the Connections-page pattern). Only
// runs for a book that ALREADY exists — we deliberately do NOT create a book on
// blur, so a half-typed title that gets abandoned never consumes a plan slot or
// leaves a stray record. A brand-new book is still committed by the Save button.
let _bookAutosaveBusy = false, _bookAutosaveQueued = false;
async function autosaveBook() {
  const payload = collectBookForm();
  if (!payload.id || !payload.title) return;   // existing, titled books only
  if (_bookAutosaveBusy) { _bookAutosaveQueued = true; return; }
  _bookAutosaveBusy = true;
  try {
    const data = await api('/books.php?action=update', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    if (!data || !data.success) toast((data && data.message) || 'Couldn’t save that change', true);
  } catch (e) {
    toast('Couldn’t save that change — check your connection', true);
  }
  _bookAutosaveBusy = false;
  if (_bookAutosaveQueued) { _bookAutosaveQueued = false; autosaveBook(); }
}

async function saveBook() {
  const title = document.getElementById('book-title').value.trim();
  if (!title) { toast('Please enter a book title', true); return; }

  const bookId  = document.getElementById('book-id').value;
  const payload = collectBookForm();

  const action = bookId ? 'update' : 'create';
  const data = await api('/books.php?action=' + action, {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  if (data.success) {
    toast(bookId ? 'Book updated' : 'Book added!');
    hideBookForm();
    loadBooks();
  } else if (data.error_code === 'book_limit') {
    // Plan limit reached — show the message WITH a path forward instead of
    // a dead-end toast.
    showDemoModal({
      type: 'warn',
      eyebrow: 'Plan limit',
      title: 'Your plan\'s book limit is reached',
      body: (data.message || 'Your plan\'s book project limit is reached.') +
            '\n\nUpgrading takes effect immediately — your books, posts, and settings all carry over.',
      footNote: 'You can also remove an old book project to make room.',
    });
    // :not(.secondary) — the markup holds TWO .demo-cta buttons (Close, then the
    // primary). A bare querySelector grabbed the Close button, so the primary
    // still read "Sign up". Always target the primary explicitly.
    const cta = document.querySelector('#demo-modal-overlay .demo-cta:not(.secondary)');
    // The 1-book cap is where most people decide they want a bigger plan, so
    // this is the moment to point them at the enquiry form.
    if (cta) {
      cta.textContent = 'Ask about a bigger plan →';
      cta.onclick = () => { closeDemoModal(); contactUs(); };
    }
  } else {
    toast(data.message || 'Something went wrong', true);
  }
}

async function deleteBook() {
  const bookId = document.getElementById('book-id').value;
  if (!bookId) return;
  if (!confirm('Delete this book? This cannot be undone.')) return;
  const data = await api('/books.php?action=delete', {
    method: 'POST',
    body: JSON.stringify({ id: bookId }),
  });
  if (data.success) {
    toast('Book deleted');
    hideBookForm();
    loadBooks();
  } else {
    toast(data.message || 'Delete failed', true);
  }
}

// ── ADMIN: WRITERS GROUPS (partner campaigns) ─────────────────
// Groups live in the writers_groups table so they can be added here rather than
// by editing code. The funnel numbers come from data we already collect:
// go.php logs link clicks to ad_clicks by slug, and signups carry
// users.signup_source = slug.

let _agGroups = [];

async function loadAdminGroups() {
  const box = document.getElementById('ag-list');
  if (!box) return;
  box.innerHTML = '<p style="color:var(--ink-soft)">Loading…</p>';
  const data = await api('/admin_groups.php?action=list');
  if (!data.success) { box.innerHTML = '<p style="color:#c44">' + escHtml(data.message || 'Could not load groups') + '</p>'; return; }

  _agGroups = data.groups || [];
  if (!_agGroups.length) {
    box.innerHTML = '<div class="card" style="padding:24px;color:var(--ink-soft)">'
      + 'No groups yet. Add one, then use <strong>Outreach</strong> to generate the letter for its leader.</div>';
    return;
  }

  const stat = (n, label) => '<div style="text-align:center;min-width:64px">'
    + '<div style="font-size:20px;font-weight:700">' + n + '</div>'
    + '<div style="font-size:11px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.04em">' + label + '</div></div>';

  // The signal that actually matters right now: every number in the funnel below
  // counts MEMBER behaviour, and none of it can start until the leader acts on
  // the letter. An all-zero row means nothing until you know which side of this
  // the group is on.
  const leaderPill = (m) => {
    const on  = !!m.leader_engaged;
    const bg  = on ? '#DCFCE7' : '#FEF3C7';
    const fg  = on ? '#166534' : '#92400E';
    const txt = on
      ? 'Leader signed in ' + escHtml(String(m.leader_first_login || '').slice(0, 10))
      : 'Leader has never signed in';
    return '<span style="font-size:11px;padding:3px 10px;border-radius:10px;background:'
      + bg + ';color:' + fg + ';font-weight:600">' + txt + '</span>';
  };

  const badge = (s) => {
    const colors = { draft:'#F1F5F9;#475569', contacted:'#e8f4fd;#1a6fa8', active:'#DCFCE7;#166534',
                     declined:'#fde8e8;#b91c1c', paused:'#FEF3C7;#92400E' };
    const c = (colors[s] || colors.draft).split(';');
    return '<span style="font-size:11px;padding:2px 9px;border-radius:10px;background:' + c[0] + ';color:' + c[1] + '">' + escHtml(s) + '</span>';
  };

  box.innerHTML = _agGroups.map(g => {
    const m = g.metrics || {};
    return '<div class="card" style="padding:20px;margin-bottom:14px">'
      + '<div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:flex-start">'
        + '<div style="min-width:220px">'
          + '<div style="font-size:17px;font-weight:600;margin-bottom:4px">' + escHtml(g.name) + ' ' + badge(g.status) + '</div>'
          + '<div style="font-size:13px;color:var(--ink-soft)">'
            + (g.leader_name ? escHtml(g.leader_name) : 'No leader on file')
            + (g.leader_email ? ' · ' + escHtml(g.leader_email) : '')
            + ((g.city || g.state) ? ' · ' + escHtml([g.city, g.state].filter(Boolean).join(', ')) : '')
          + '</div>'
          + '<div style="font-size:12px;color:var(--ink-soft);margin-top:6px">'
            + (g.outreach_sent_at ? 'Emailed ' + escHtml(String(g.outreach_sent_at).slice(0,10)) : 'Not emailed yet')
            + ' · ' + g.trial_days + '-day trial</div>'
          + (g.leader_user_id
              ? '<div style="margin-top:8px">' + leaderPill(m) + '</div>'
              : '')
        + '</div>'
        + '<div style="display:flex;gap:14px;flex-wrap:wrap">'
          + stat(m.clicks || 0, 'Clicks') + stat(m.signups || 0, 'Signups')
          + stat(m.trialing || 0, 'Trialing') + stat(m.converted || 0, 'Paid') + stat(m.canceled || 0, 'Lost')
        + '</div>'
      + '</div>'
      + '<div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;align-items:center">'
        + '<button class="app-btn app-btn-green app-btn-sm" onclick="agOutreach(' + g.id + ')">Outreach</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="agEditGroup(' + g.id + ')">Edit</button>'
        + '<button class="app-btn app-btn-outline app-btn-sm" onclick="agCopyText(' + JSON.stringify(g.link) + ', this)">Copy link</button>'
        + (g.leader_user_id
            ? '<span style="font-size:12px;color:var(--ink-soft)">Leader trial ends '
              + escHtml(String(g.leader_trial_ends_at || '').slice(0,10)) + '</span>'
            : '<button class="app-btn app-btn-outline app-btn-sm" onclick="agLeaderTrial(' + g.id + ')">Create leader trial</button>')
      + '</div></div>';
  }).join('');
}

function agNewGroup() {
  const f = document.getElementById('ag-form');
  document.getElementById('ag-form-title').textContent = 'New group';
  ['ag-id','ag-name','ag-leader-name','ag-leader-email','ag-city','ag-state','ag-notes'].forEach(id => {
    const el = document.getElementById(id); if (el) el.value = '';
  });
  document.getElementById('ag-trial-days').value = 14;
  document.getElementById('ag-status').value = 'draft';
  document.getElementById('ag-form-err').textContent = '';
  f.style.display = 'block';
  f.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function agEditGroup(id) {
  const g = _agGroups.find(x => x.id == id);
  if (!g) return;
  document.getElementById('ag-form-title').textContent = 'Edit ' + g.name;
  document.getElementById('ag-id').value           = g.id;
  document.getElementById('ag-name').value         = g.name || '';
  document.getElementById('ag-leader-name').value  = g.leader_name || '';
  document.getElementById('ag-leader-email').value = g.leader_email || '';
  document.getElementById('ag-city').value         = g.city || '';
  document.getElementById('ag-state').value        = g.state || '';
  document.getElementById('ag-trial-days').value   = g.trial_days || 14;
  document.getElementById('ag-status').value       = g.status || 'draft';
  document.getElementById('ag-notes').value        = g.notes || '';
  document.getElementById('ag-form-err').textContent = '';
  const f = document.getElementById('ag-form');
  f.style.display = 'block';
  f.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

async function agSaveGroup() {
  const err = document.getElementById('ag-form-err');
  err.textContent = '';
  const payload = {
    id:           parseInt(document.getElementById('ag-id').value, 10) || 0,
    name:         document.getElementById('ag-name').value.trim(),
    leader_name:  document.getElementById('ag-leader-name').value.trim(),
    leader_email: document.getElementById('ag-leader-email').value.trim(),
    city:         document.getElementById('ag-city').value.trim(),
    state:        document.getElementById('ag-state').value.trim(),
    trial_days:   parseInt(document.getElementById('ag-trial-days').value, 10) || 14,
    status:       document.getElementById('ag-status').value,
    notes:        document.getElementById('ag-notes').value.trim(),
  };
  if (!payload.name) { err.textContent = 'Group name is required'; return; }

  const data = await api('/admin_groups.php?action=save', { method: 'POST', body: JSON.stringify(payload) });
  if (!data.success) { err.textContent = data.message || 'Could not save'; return; }
  document.getElementById('ag-form').style.display = 'none';
  toast('Group saved');
  loadAdminGroups();
}

// Close whichever letter/template panel is open and return to the groups list.
// Both panels sit ABOVE #ag-list and can be tall, so simply hiding them left the
// page scrolled to wherever the letter had been — the list was off-screen and it
// wasn't obvious you'd gone back. This scrolls to the page header too.
function agBackToGroups() {
  ['ag-outreach', 'ag-templates'].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
  const top = document.querySelector('#view-admin-groups .page-header') ||
              document.getElementById('ag-list');
  if (top) top.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

let _agOutreachId = 0;
let _agOutreachGroup = null;   // held for agOpenEmail, which needs leader_email
// quiet = refresh the panel in place without scrolling to it. Used after a
// template save, where jumping the page away from the editor Bob is still
// working in would be worse than the stale text this is fixing.
async function agOutreach(id, quiet) {
  const data = await api('/admin_groups.php?action=outreach&id=' + id);
  if (!data.success) { toast(data.message || 'Could not build the outreach', true); return; }
  _agOutreachId = id;
  _agOutreachGroup = data.group || null;
  document.getElementById('ag-outreach-title').textContent = 'Outreach — ' + (data.group ? data.group.name : '');
  document.getElementById('ag-letter').value      = data.letter      || '';
  document.getElementById('ag-letter-talk').value = data.letter_talk || '';
  document.getElementById('ag-followup').value    = data.followup    || '';
  document.getElementById('ag-blurb').value       = data.blurb       || '';
  // The talk letter is an alternative to letter 1, not an extra thing to send.
  // Hide its block entirely if migration 051 hasn't been applied yet.
  const talkBlock = document.getElementById('ag-talk-block');
  if (talkBlock) talkBlock.style.display = data.letter_talk ? 'block' : 'none';
  const box = document.getElementById('ag-outreach');
  box.style.display = 'block';
  if (!quiet) box.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Split the "Subject: …\n\n…body" shape that ?action=outreach builds. Reads the
// textarea's CURRENT value rather than the fetched copy, so any per-group tweak
// Bob makes before sending is carried into the email.
function agSplitLetter(text) {
  const m = /^[ \t]*Subject:[ \t]*([^\r\n]*)\r?\n\r?\n?([\s\S]*)$/.exec(text || '');
  return m ? { subject: m[1].trim(), body: m[2] } : { subject: '', body: text || '' };
}

// Open one of these letters in Bob's own mail app, prefilled and ready to send.
//
// Deliberately mailto: and NOT the campaign sender. These are cold 1:1 emails —
// a personal note from Bob's own mailbox lands far better than app-sent mail from
// a domain with no sending history, Mailgun is rate-capped and on probation, and
// the panel itself says to send them by hand. Keep the send in his hands.
//
// The encoded URL measures ~2,700 chars for every real group, past the ~2,000 that
// some mail clients historically truncate at. macOS clients handle it, but a cold
// outreach letter silently cut off mid-sentence is a bad way to find out — so the
// body also goes on the clipboard, making a truncated compose one paste from right.
function agOpenEmail(elId) {
  const email = _agOutreachGroup && _agOutreachGroup.leader_email
    ? String(_agOutreachGroup.leader_email).trim() : '';
  if (!email) { toast('Add the leader’s email to this group first', true); return; }
  const el = document.getElementById(elId);
  if (!el) return;

  const parts = agSplitLetter(el.value);
  if (!parts.body.trim()) { toast('Nothing to send yet — open a group first', true); return; }

  const finish = (copied) => {
    // An anchor click hands off to the mail app without navigating the SPA away,
    // which assigning location.href can do in some browsers.
    const a = document.createElement('a');
    a.href = 'mailto:' + email
           + '?subject=' + encodeURIComponent(parts.subject)
           + '&body='    + encodeURIComponent(parts.body);
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    a.remove();
    toast(copied
      ? 'Composed in your mail app. Body also copied — if it looks cut off, select the body and paste.'
      : 'Composed in your mail app. Check the letter is complete before sending.');
  };

  // Clipboard first: the mail app handoff can take focus and cost us the gesture.
  navigator.clipboard.writeText(parts.body).then(() => finish(true)).catch(() => finish(false));
}

async function agMarkContacted() {
  if (!_agOutreachId) return;
  const data = await api('/admin_groups.php?action=mark_contacted', {
    method: 'POST', body: JSON.stringify({ id: _agOutreachId }),
  });
  if (!data.success) { toast(data.message || 'Could not update', true); return; }
  toast('Marked as contacted');
  loadAdminGroups();
}

// Provision the group leader a no-card comped account. They're the channel, not
// a customer being qualified — so no card, and a longer window than members get.
async function agLeaderTrial(id) {
  const g = _agGroups.find(x => x.id == id);
  if (!g) return;
  if (!g.leader_email) { toast('Add the leader’s email to this group first', true); return; }
  const days = g.leader_trial_days || 30;
  if (!confirm('Create a ' + days + '-day free account (no card) for ' + (g.leader_name || g.leader_email) + '?\n\n'
             + 'Their set-up link will appear in the outreach letter.')) return;

  const data = await api('/admin_groups.php?action=create_leader_trial', {
    method: 'POST', body: JSON.stringify({ id: id }),
  });
  if (!data.success) { toast(data.message || 'Could not create the leader trial', true); return; }
  toast(data.message || 'Leader trial created');
  loadAdminGroups();
}

// ── Editable outreach templates ───────────────────────────────
// Reword once here instead of editing every send. The per-group Outreach panel
// still lets you tweak an individual letter before copying.
// tkey -> the editor fields that hold it. Table-driven so adding a template is
// one entry: the old if/else defaulted its LAST branch to member_blurb, so any
// unrecognized key silently saved over the blurb.
// subject:null means this template has no subject field on screen — the save then
// omits the key entirely rather than sending '', which used to wipe a stored
// subject the editor never displayed.
const AG_TPL_FIELDS = {
  leader_letter:      { subject: 'ag-tpl-letter-subject',   body: 'ag-tpl-letter-body'   },
  leader_letter_talk: { subject: 'ag-tpl-talk-subject',     body: 'ag-tpl-talk-body'     },
  leader_followup:    { subject: 'ag-tpl-followup-subject', body: 'ag-tpl-followup-body' },
  member_blurb:       { subject: null,                      body: 'ag-tpl-blurb-body'    },
};

async function agOpenTemplates() {
  const box = document.getElementById('ag-templates');
  const data = await api('/admin_groups.php?action=templates');
  if (!data.success) { toast(data.message || 'Could not load templates', true); return; }
  Object.keys(AG_TPL_FIELDS).forEach((tkey) => {
    const f = AG_TPL_FIELDS[tkey], row = data[tkey] || {};
    const sEl = f.subject && document.getElementById(f.subject);
    const bEl = document.getElementById(f.body);
    if (sEl) sEl.value = row.subject || '';
    if (bEl) bEl.value = row.body || '';
  });
  box.style.display = 'block';
  box.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function agSaveTemplate(which) {
  const f = AG_TPL_FIELDS[which];
  if (!f) { toast('Unknown template: ' + which, true); return; }
  const payload = { tkey: which, body: document.getElementById(f.body).value };
  if (f.subject) payload.subject = document.getElementById(f.subject).value;
  const data = await api('/admin_groups.php?action=save_template', {
    method: 'POST', body: JSON.stringify(payload),
  });
  toast(data.success ? 'Template saved' : (data.message || 'Could not save'), !data.success);
  // The Outreach panel holds a letter rendered from the pre-save template and
  // never refreshed itself, so a saved edit looked like it hadn't applied.
  // Only when it's actually on screen: ?action=outreach mints a fresh leader
  // setup token on every call, so don't re-fetch for a panel nobody is reading.
  const ob = document.getElementById('ag-outreach');
  const obOpen = ob && ob.style.display !== 'none' && ob.offsetParent !== null;
  if (data.success && _agOutreachId && obOpen) agOutreach(_agOutreachId, true);
}

function agCopyText(text, btn) {
  navigator.clipboard.writeText(text).then(() => {
    const was = btn.textContent; btn.textContent = 'Copied';
    setTimeout(() => { btn.textContent = was; }, 1200);
  }).catch(() => toast('Copy failed — select the text manually', true));
}

function agCopy(elId, btn) {
  const el = document.getElementById(elId);
  if (el) agCopyText(el.value, btn);
}

// Wire autosave-on-blur for the book form. One delegated 'change' listener on
// the form container fires when focus leaves any edited field (native 'change'
// = blur-after-edit), matching how the Connections page saves. autosaveBook()
// itself no-ops for unsaved books, so this is safe to attach once at load.
(function wireBookAutosave() {
  const form = document.getElementById('books-form-view');
  if (!form) return;
  const AUTOSAVE_IDS = new Set([
    'book-title', 'book-author', 'book-subtitle', 'book-isbn', 'book-amazon-asin',
    'book-amazon-url', 'book-pages', 'book-page-size', 'book-genre', 'book-publisher',
    'book-pubdate', 'book-status', 'book-description', 'book-author-bio', 'book-audience',
    'book-comps', 'book-keywords', 'book-tagline', 'book-logline',
  ]);
  form.addEventListener('change', function (e) {
    if (e.target && AUTOSAVE_IDS.has(e.target.id)) autosaveBook();
  });
})();

document.querySelectorAll('.copy-year').forEach(el => { el.textContent = new Date().getFullYear(); });

/* ── SETUP HELP — per-section how-to popups with checkable steps ──
   Add a new topic by adding an entry to setupHelpTopics, then call
   showSetupHelp('topic-key') from anywhere (e.g., a button onclick).
   Progress is persisted per-topic in localStorage. */
const setupHelpTopics = {
  meta: {
    title: 'Connecting Facebook & Instagram',
    intro: 'Facebook and Instagram are connected through Meta — one approval covers both. Setup takes about 10 minutes the first time. Once it\'s done, you can publish to your Facebook Page and Instagram Business account from inside this app.',
    prereqs: [
      'A Facebook Page for your author or book brand (a personal profile won\'t work)',
      'An Instagram Business or Creator account, if you want to post to Instagram'
    ],
    steps: [
      {
        title: 'Create or pick the Facebook Page you\'ll post to',
        desc: 'You need a Facebook Page (not a personal profile). If you don\'t have one, create one at <a href="https://www.facebook.com/pages/create" target="_blank" rel="noopener">facebook.com/pages/create</a>. Use your author name or book brand as the Page name.'
      },
      {
        title: 'Switch Instagram to a Business or Creator account',
        desc: 'Open the Instagram app → Settings → Account → Switch to Professional Account. Choose <strong>Creator</strong> (recommended for authors) or Business. Skip this step if you don\'t plan to post to Instagram.'
      },
      {
        title: 'Link your Instagram account to your Facebook Page',
        desc: 'On Facebook, go to your Page → Settings → Linked Accounts → Instagram → Connect. Sign in with the Instagram account from step 2. This is what lets us post to Instagram on your behalf.'
      },
      {
        title: 'Click the green Connect button on this row',
        desc: 'Close this window, then click <strong>Connect</strong> next to Facebook + Instagram. Facebook will open in a new tab and ask you to approve access. Pick the Page you set up in step 1, and leave all permissions checked.'
      },
      {
        title: 'Confirm the badge says Connected',
        desc: 'You\'ll be sent back here. The badge should switch from "Not connected" to "Connected." If it doesn\'t, try once more — sometimes a browser pop-up blocker interrupts Facebook\'s approval flow.'
      }
    ]
  },

  bluesky: {
    title: 'Connecting Bluesky',
    intro: 'Bluesky doesn\'t use the usual "sign in with…" popup. Instead, you generate an <strong>app password</strong> on Bluesky\'s site and paste it here. Setup takes 2–3 minutes.',
    prereqs: [
      'A Bluesky account (free at bsky.app)',
      'Your Bluesky handle, like yourname.bsky.social'
    ],
    steps: [
      {
        title: 'Create a Bluesky account if you don\'t have one',
        desc: 'Sign up at <a href="https://bsky.app" target="_blank" rel="noopener">bsky.app</a>. Pick a handle that matches your author name where possible — handles look like <em>yourname.bsky.social</em>.'
      },
      {
        title: 'Generate an app password',
        desc: 'Go to <a href="https://bsky.app/settings/app-passwords" target="_blank" rel="noopener">bsky.app/settings/app-passwords</a> → click "Add App Password" → name it "Elite Publishing" → click Create. Bluesky will show you a one-time password in the format <code>xxxx-xxxx-xxxx-xxxx</code>.'
      },
      {
        title: 'Copy the app password right away',
        desc: 'Bluesky only shows the app password once. Copy it now — if you close the dialog without copying, you\'ll have to revoke it and create a new one.'
      },
      {
        title: 'Click Connect on this row, then paste',
        desc: 'A small form will slide open with two fields. Paste your handle (e.g., <em>yourname.bsky.social</em>) and the app password. Click Connect to save.'
      },
      {
        title: 'Confirm the badge says Connected',
        desc: 'The Bluesky row badge should switch to "Connected." To revoke access later, go back to <a href="https://bsky.app/settings/app-passwords" target="_blank" rel="noopener">bsky.app/settings/app-passwords</a> and delete the app password you created.'
      }
    ]
  },

  linkedin: {
    title: 'Connecting LinkedIn',
    intro: 'LinkedIn posts go to your personal profile, not a Company Page. Setup is a quick OAuth flow — about 5 minutes.',
    prereqs: [
      'A LinkedIn personal account',
      '(Optional) An author-branded profile photo and headline — LinkedIn posts look more credible when the profile is filled in'
    ],
    steps: [
      {
        title: 'Make sure you\'re signed in to LinkedIn in this browser',
        desc: 'Open <a href="https://www.linkedin.com" target="_blank" rel="noopener">linkedin.com</a> in another tab and sign in to the account you want to post from. If you have multiple LinkedIn accounts, sign out of the others first to avoid picking the wrong one during connect.'
      },
      {
        title: 'Click Connect on this row',
        desc: 'LinkedIn will open in a new tab and ask you to authorize this app. The permission you\'re granting is "post on your behalf" — needed to publish from here.'
      },
      {
        title: 'Confirm the badge says Connected',
        desc: 'You\'ll be sent back here. If LinkedIn shows an error about Company Pages, you tried to connect to a Page instead of a profile — Pages aren\'t supported yet.'
      }
    ]
  },

  tiktok: {
    title: 'Connecting TikTok',
    intro: 'TikTok is the breakout platform for indie authors — BookTok regularly turns midlist titles into bestsellers. Connecting takes about 5 minutes once your TikTok account exists.',
    prereqs: [
      'A TikTok account (free at tiktok.com or in the TikTok app)',
      'Decide whether your default post visibility should be Public, Friends Only, or Private — you can change this later'
    ],
    steps: [
      {
        title: 'Create or sign in to TikTok',
        desc: 'If you don\'t have a TikTok account, sign up at <a href="https://www.tiktok.com/signup" target="_blank" rel="noopener">tiktok.com/signup</a>. Use your author name or book brand as the username where possible.'
      },
      {
        title: 'Make sure your account isn\'t set to Private',
        desc: 'Open TikTok → Profile → Menu → Settings and Privacy → Privacy → make sure "Private account" is OFF if you want your posts to reach new readers. (You can still set individual posts to private later.)'
      },
      {
        title: 'Click Connect on this row',
        desc: 'TikTok will open in a new tab and ask you to authorize this app. Approve the permissions — read your basic profile and publish videos.'
      },
      {
        title: 'Pick your default post visibility',
        desc: 'After approving, TikTok asks how published videos should appear — Public, Friends Only, or Private. For author marketing, choose <strong>Public</strong>. You can override this per-post when you publish.'
      },
      {
        title: 'Confirm the badge says Connected',
        desc: 'You\'ll be sent back here. The TikTok badge should switch to "Connected."'
      }
    ]
  },

  'amazon-kdp': {
    title: 'Setting up Amazon KDP',
    intro: 'Amazon Kindle Direct Publishing is the largest single sales channel for indie books — eBook + paperback, no upfront cost. KDP doesn\'t connect to this app via API, so you publish on KDP\'s site directly. This guide covers what you need to get started.',
    prereqs: [
      'An Amazon account (the one you shop with works fine)',
      'Bank account info and tax ID (SSN for individuals; EIN if you have an LLC)',
      'Your finished manuscript file and a cover image'
    ],
    steps: [
      {
        title: 'Create your KDP account',
        desc: 'Go to <a href="https://kdp.amazon.com" target="_blank" rel="noopener">kdp.amazon.com</a> and sign in. Click "Update" under Tax Information and complete the W-9 (US) or W-8BEN (international). You won\'t be able to publish until tax info is on file.'
      },
      {
        title: 'Add bank info for royalty payments',
        desc: 'Under Account → Getting Paid, add the bank account where Amazon will deposit royalties (paid roughly 60 days after each sale month).'
      },
      {
        title: 'Click "Create" and pick eBook or Paperback',
        desc: 'Most authors publish both. Start with eBook — it\'s faster and has fewer file specs. You can come back and add paperback later as a separate "format" tied to the same book.'
      },
      {
        title: 'Fill out the book details',
        desc: 'Title, subtitle, author name, description, keywords (up to 7), and 2 categories. Generate description, tagline, and metadata in this app first — they paste straight into KDP\'s form.'
      },
      {
        title: 'Upload manuscript and cover',
        desc: 'For eBook: upload an ePub or .docx — Amazon converts it. For paperback: upload an interior PDF (sized to your trim size) and a wrap PDF cover (front + spine + back as one image). Use KDP\'s Cover Creator if you don\'t have a designer.'
      },
      {
        title: 'Set price and rights',
        desc: 'Pick "Worldwide rights" unless you have a publisher in another country. For eBook, $2.99–$9.99 hits the 70% royalty tier. For paperback, KDP shows the minimum price needed to cover printing.'
      },
      {
        title: 'Submit for review',
        desc: 'Amazon reviews most books within 72 hours. Once live, copy the Amazon listing URL into the Sales channels page here so you can track it.'
      }
    ]
  },

  'amazon-author-central': {
    title: 'Setting up Amazon Author Central',
    intro: 'Author Central is the most underused tool in indie publishing. It\'s a separate Amazon site (not part of KDP) that controls your author bio, photo, and Follow button on every book\'s product page — plus gives you access to sales data KDP doesn\'t show. Most authors don\'t know it exists. Setup takes about 30 minutes and pays back forever.',
    prereqs: [
      'Your Amazon login (the same one you use for KDP)',
      'A professional author photo (square, at least 600×600 px)',
      'A finished author bio (you can draft one in this app first)'
    ],
    steps: [
      {
        title: 'Sign in to Author Central',
        desc: 'Go to <a href="https://authorcentral.amazon.com" target="_blank" rel="noopener">authorcentral.amazon.com</a> and sign in with the same Amazon account you use for KDP. Author Central is a separate dashboard from KDP — it has its own login screen and its own UI.'
      },
      {
        title: 'Set up your author profile',
        desc: 'Click "Profile" → fill in your author name (use exactly what appears on your book covers), upload a square author photo, and paste in your bio. The photo and bio appear on every one of your book product pages and on your Author Page. This is the single highest-leverage change you can make on Amazon.'
      },
      {
        title: 'Claim your books',
        desc: 'Click "Books" → "Add more books." Search by title or paste your ASIN. Amazon will verify you as the author within 1–3 business days. You only have to do this once per book — it sticks for the life of that title.'
      },
      {
        title: 'Customize your Author Page URL',
        desc: 'Your Author Page lives at amazon.com/author/&lt;your-name&gt;. Pick a vanity URL while it\'s available — first come, first served. This is the link you\'ll share in social bios, email signatures, and on your website.'
      },
      {
        title: 'Add editorial reviews to each book',
        desc: 'Books → pick a book → "Editorial Reviews." Paste in trade reviews (Kirkus, Publishers Weekly), endorsements from other authors, or pull-quotes from press coverage. These show prominently on the product page above customer reviews and lend instant credibility.'
      },
      {
        title: 'Add the "From the Author" section',
        desc: 'Same edit screen — "From the Author" lets you add a personal note about why you wrote the book. Optional but powerful: it makes your product page feel less corporate and helps readers connect with you, not just the book.'
      },
      {
        title: 'Confirm the Follow button is live',
        desc: 'Author Central automatically adds a Follow button to your Author Page and book pages. When readers click it, Amazon emails them every time you publish a new title — Amazon-driven launch marketing for free, with zero work on your end after setup.'
      },
      {
        title: 'Check your sales reports monthly',
        desc: 'Reports → Sales Info shows weekly BookScan print sales (US) and Kindle data. KDP shows what KDP sold; Author Central shows what bookstores sold through Ingram and other distributors. Worth checking monthly to see your full sales picture.'
      }
    ]
  },

  'amazon-series-page': {
    title: 'Setting up an Amazon Series page',
    intro: 'If you publish more than one book in a series, Amazon will give you a dedicated <strong>Series page</strong> that shows all the books in reading order with one consolidated reviews block — instead of readers hunting them down individually. Series pages dramatically improve series conversion and discovery. Most authors don\'t know they exist or how to claim one.',
    prereqs: [
      'At least 2 books published in the same series',
      'A KDP account with each book already live on Amazon',
      'The series name typed identically on every book in KDP\'s metadata'
    ],
    steps: [
      {
        title: 'Confirm consistent series metadata across every book',
        desc: 'Sign in to KDP → Bookshelf → for each book, click ... → Edit details. Confirm the <strong>Series Name</strong> field is filled in IDENTICALLY across every book in the series — punctuation, capitalization, and spaces all matter. Confirm the <strong>Series number</strong> field is filled in (1, 2, 3…). Save changes for any book missing this. Wait 24–48 hours after editing for Amazon to re-index.'
      },
      {
        title: 'Search Amazon for your series name',
        desc: 'Go to <a href="https://www.amazon.com" target="_blank" rel="noopener">amazon.com</a> and search for the exact series name. If a Series page already exists, it shows up as "(Series)" or "The [Name] Series" in the results. If yes, skip to step 4. If not, continue to step 3.'
      },
      {
        title: 'Request a Series page through Author Central',
        desc: 'Sign in to <a href="https://authorcentral.amazon.com" target="_blank" rel="noopener">authorcentral.amazon.com</a> → Help (top right) → Contact us → Books or Author profile → "Other." Write: "Please create an Amazon Series page for my series titled [SERIES NAME]. The books in order are: [Book 1 ASIN] – [title], [Book 2 ASIN] – [title]…" Amazon usually responds in 3–5 business days.'
      },
      {
        title: 'Verify the new Series page',
        desc: 'Once Amazon creates it, confirm: all books are listed in correct order, each book\'s individual page shows "Book X of N: [Series Name]" near the title, and the consolidated review count and rating display correctly. If anything is wrong, reply to the same Author Central support thread to fix it.'
      },
      {
        title: 'Use the Series page URL in all your series marketing',
        desc: 'Copy the Series page URL (looks like amazon.com/dp/B0XXXXXXX). Use this URL — not individual book URLs — when promoting the series in social posts, ads, and email campaigns. It\'s the single best landing page for series fans because it lets readers see and buy the next book in one click.'
      },
      {
        title: 'Update each book\'s "From the Author" with reading-order pointers',
        desc: 'In Author Central → Books → edit each book → "From the Author" → add: "This is Book X in the [Series Name] series. Continue with [Book X+1: Title]." Explicit reading-order pointers like this dramatically improve series-completion rates.'
      }
    ]
  },

  'ingramspark': {
    title: 'Setting up IngramSpark distribution',
    intro: 'IngramSpark gives your book global distribution to 40,000+ bookstores, libraries, and online retailers — including Barnes & Noble, independent bookstores, Amazon (yes, even though you may already have a KDP listing), and international markets KDP doesn\'t reach. It\'s print-on-demand, so no inventory, no upfront cost. As of late 2025, IngramSpark dropped setup fees to $0. The trade-off is a 1.875% market access fee per sale and a wholesale discount of 30–55% you set yourself — that discount is what bookstores keep when they sell your book.',
    prereqs: [
      'Print-ready interior PDF (with proper trim size + bleed)',
      'Print-ready cover PDF (built using IngramSpark\'s cover template generator — they give you exact dimensions)',
      'ISBN — your own from Bowker, or a free one from IngramSpark (note: free IS-issued ISBNs restrict you to using IS as your printer)',
      'Bank account for royalty deposits',
      'Tax info (W-9 for US authors)'
    ],
    steps: [
      {
        title: 'Create your free IngramSpark account',
        desc: 'Go to <a href="https://www.ingramspark.com" target="_blank" rel="noopener">ingramspark.com</a> → Sign Up. Account creation is free; there\'s no monthly subscription. Choose "Independent Author" or "Small Publisher" depending on how many titles you plan to publish.'
      },
      {
        title: 'Get your ISBNs sorted before you start the title',
        desc: 'Each format (paperback, hardcover, eBook) needs its own ISBN. <strong>Important if you\'re using both KDP and IngramSpark:</strong> you need a separate ISBN for each platform per format — so a paperback on both KDP and IS means <strong>two paperback ISBNs</strong>, not one. The two systems can\'t share an ISBN. If you have your own ISBNs from Bowker, plan accordingly. IngramSpark offers free ISBNs, but a free IS-issued ISBN locks you into IS as the printer for that edition (and can\'t be reused on KDP).'
      },
      {
        title: 'Start a new title and enter metadata',
        desc: 'Title, subtitle, contributors (author / illustrator / editor), BISAC categories (pick the same ones you used on KDP for consistency), keywords, description, age range if applicable, and the publication date. Spend time on the description — it appears on retailer sites worldwide. You can use the description you wrote for KDP, write a fresh one, or have an AI like ChatGPT generate it for you. A prompt that produces good results: drop your manuscript file into the chat and ask: <em>"Read this manuscript and write a 150–250 word back-cover description. Open with a hook that establishes the protagonist and stakes; develop the central conflict in 2–3 sentences; end on a question or tension that compels the reader to buy — without spoiling the ending. Match the tone of the genre."</em> Tighten the result by hand before pasting into IS.'
      },
      {
        title: 'Choose format and trim size',
        desc: 'Paperback (most common: 6×9 or 5.5×8.5), hardcover (case laminate or jacketed), or eBook (EPUB). You can do all three for the same title — each is a separate ISBN. Each format has its own pricing and discount.'
      },
      {
        title: 'Build your cover using the IngramSpark template generator',
        desc: 'Their template tool gives you exact spine width, bleed, and trim dimensions based on page count and paper choice. <strong>Don\'t skip this</strong> — wrong dimensions are the #1 reason files get rejected. Download the template, hand it to your cover designer, then upload the print-ready cover PDF.'
      },
      {
        title: 'Upload your interior PDF',
        desc: 'Must be print-ready (PDF/X-1a is safest), all fonts embedded, correct trim size, with bleed if you have full-page images. IngramSpark\'s preflight will flag problems before submission.'
      },
      {
        title: 'Set your retail price and wholesale discount',
        desc: 'This is the most important pricing decision. List price is what readers pay; wholesale discount (30–55%) is what bookstores keep. <strong>For trade distribution, 55% is the standard expectation</strong> — bookstores rarely stock books at lower discounts. For online-only distribution where bookstore stocking isn\'t a goal, you can go as low as 30%. Use IngramSpark\'s royalty calculator to see your per-copy revenue at different price/discount combinations.'
      },
      {
        title: 'Choose your distribution markets and decide on returns — carefully',
        desc: 'US, UK, EU, AU, and "global" all available. Each market has slightly different print costs and currencies. Most authors choose all markets — there\'s no extra cost to do so. <strong>Returns is the choice that needs real thought.</strong> Enabling returns gets you into more bookstores (they strongly prefer returnable titles), but it carries real downside risk: if a store orders 50 copies of your book and returns 30 unsold, <strong>you pay the print cost for all 30 returned copies</strong> — and the revenue from those original sales gets clawed back from your account. For most indie authors selling primarily online, <strong>no-returns is the safer default</strong>. Only enable returns once you have a real plan for getting into physical bookstores.'
      },
      {
        title: 'Skip the official proof — order a single copy instead',
        desc: 'IngramSpark sells "proof copies" through their proofing system, but here\'s the cheat: as soon as you\'ve approved your files for distribution, just order a single retail copy of your own book. It\'s much cheaper than the proof process, arrives in about a week, and shows up well before your book actually starts appearing on retailer sites (which takes 6–8 weeks). Inspect that copy carefully — if anything\'s wrong, you have a 60-day window to fix and re-upload your files for free.'
      },
      {
        title: 'Approve and go live',
        desc: 'Once you\'ve approved your files, your title goes into Ingram\'s catalog within 1–2 business days and starts populating retailer sites globally over the next 6–8 weeks. From now on, when someone orders your book at any retailer worldwide, Ingram prints and ships it. <strong>Heads up on the 60-day revision window:</strong> you can fix and re-upload your interior or cover files for free within 60 days of approval. After that, file revisions cost $25 each (though IngramSpark has been making more updates free over time). Use that 60-day window for any "I noticed a typo" fixes.'
      }
    ]
  },

  shopify: {
    title: 'Setting up Shopify',
    intro: 'Shopify lets you sell books directly from your own site — keeping more of the revenue (no Amazon cut, no distributor cut) and capturing buyer email addresses for future launches. Worth it if you sell signed copies, special editions, or bundles.',
    prereqs: [
      'A credit card for the Shopify subscription (~$29/mo Basic plan, free trial)',
      'A domain name (Shopify can sell you one, or connect one you own)',
      'A way to fulfill orders — print-on-demand via IngramSpark or Lulu, or you ship from home'
    ],
    steps: [
      {
        title: 'Start a Shopify trial',
        desc: 'Go to <a href="https://www.shopify.com" target="_blank" rel="noopener">shopify.com</a> and click "Start free trial." The free trial is 3 days, then $1/month for 3 months on most plans.'
      },
      {
        title: 'Add your book as a product',
        desc: 'Products → Add product. Use your generated book description and cover image. For physical books, set the weight (matters for shipping). For eBooks, mark it as a digital product (Shopify will deliver the file by email after purchase).'
      },
      {
        title: 'Set up payment processing',
        desc: 'Settings → Payments → activate Shopify Payments (built-in, no extra signup) or connect Stripe / PayPal. Shopify Payments is the simplest path.'
      },
      {
        title: 'Configure shipping (physical books only)',
        desc: 'Settings → Shipping and delivery. Add a shipping zone (US, North America, Worldwide) and pick a flat rate or carrier-calculated rates. For one book, $4–6 media mail in the US is standard.'
      },
      {
        title: 'Connect or buy a domain',
        desc: 'Settings → Domains. Either buy a domain through Shopify (~$15/yr) or connect one you already own. Default is yourstore.myshopify.com — fine for testing, but a custom domain reads more professional.'
      },
      {
        title: 'Pick a theme and publish',
        desc: 'Online Store → Themes. Shopify\'s "Dawn" free theme works well for single-product author stores. Once you\'re happy, click "Launch" to make the store public.'
      }
    ]
  },

  'google-merchant': {
    title: 'Setting up Google Merchant Center',
    intro: 'Google Merchant Center feeds your book listings into Google Shopping — the product results that show up in Google search and on the Shopping tab. Free to list. Useful if you have your own store (Shopify, WooCommerce); skip it if you only sell on Amazon.',
    prereqs: [
      'A Google account',
      'A website with your book for sale (Shopify, WooCommerce, or any e-commerce site)',
      'Verified ownership of that website'
    ],
    steps: [
      {
        title: 'Create a Merchant Center account',
        desc: 'Go to <a href="https://merchants.google.com" target="_blank" rel="noopener">merchants.google.com</a> → click "Get started" → sign in with the Google account you want to use for your business.'
      },
      {
        title: 'Verify your website',
        desc: 'Tools → Business information → Website. Google will give you a meta tag, an HTML file, or a Google Tag option. Most authors use the meta tag — paste it into your site\'s &lt;head&gt; section. Shopify and WooCommerce both have Merchant Center integrations that handle this automatically.'
      },
      {
        title: 'Create a product feed',
        desc: 'Products → Feeds → Add feed. The simplest path is to install the official Google channel app on Shopify (or the Google Listings &amp; Ads plugin on WooCommerce). It auto-builds and updates the feed for you. Manual feeds via spreadsheet work too but are tedious.'
      },
      {
        title: 'Add required product fields',
        desc: 'Each book needs: title, description, image, price, GTIN (your ISBN), brand, condition, and availability. The store integration usually pulls these from your existing product entries.'
      },
      {
        title: 'Submit and wait for approval',
        desc: 'Google reviews each product. First-time approval can take 3–5 business days. After approval, listings start showing on Google Shopping for free; paid Shopping ads run on top of the same feed.'
      }
    ]
  },

  'facebook-ads': {
    title: 'Setting up Facebook Ads',
    intro: 'Facebook (Meta) Ads is the most-used paid channel for indie author promotion. Setup is a one-time chore — Business Manager, Ad Account, payment method — but it\'s the gateway to everything from boosting posts to running targeted campaigns.',
    prereqs: [
      'A Facebook Page for your author or book brand (the same one you connected for Facebook posting)',
      'A credit card or PayPal for ad billing',
      'A budget you\'re comfortable losing while you learn — $5–10/day for the first month is typical'
    ],
    steps: [
      {
        title: 'Open Meta Business Manager',
        desc: 'Go to <a href="https://business.facebook.com" target="_blank" rel="noopener">business.facebook.com</a>. If you\'ve never used it, click "Create Account." Use your author name or business name. Business Manager is free and is the umbrella for all your Pages, ad accounts, and pixels.'
      },
      {
        title: 'Add your Facebook Page to Business Manager',
        desc: 'Business Settings → Accounts → Pages → Add → Add a Page. Pick the Page you set up earlier. This gives Business Manager permission to manage that Page on your behalf.'
      },
      {
        title: 'Create an Ad Account',
        desc: 'Business Settings → Accounts → Ad Accounts → Add → Create a new ad account. Name it something like "<em>Your Name</em> Ads." Pick currency and time zone — you cannot change these later, so pick correctly.'
      },
      {
        title: 'Add a payment method',
        desc: 'In your new Ad Account → Billing → Payment Settings → Add Payment Method. Credit card, debit card, or PayPal all work. Meta charges you when ads run; there\'s no upfront deposit on most accounts.'
      },
      {
        title: '(Optional) Install the Meta Pixel for tracking',
        desc: 'Pixel tracks who clicks your ad and goes to your site — needed for retargeting and conversion campaigns. Business Settings → Data Sources → Pixels → Add. If your store is Shopify, install the official Facebook &amp; Instagram channel app to wire Pixel automatically.'
      },
      {
        title: 'You\'re ready to run your first campaign',
        desc: 'Go to <a href="https://adsmanager.facebook.com" target="_blank" rel="noopener">adsmanager.facebook.com</a> → Create. For a first ad, use the "Boost a post" pattern from your Page — simpler than Ads Manager. As you get comfortable, move into Ads Manager for proper campaigns with audience targeting.'
      }
    ]
  },

  'book-setup': {
    title: 'Setting up your book',
    intro: 'Every AI feature in this app reads your book\'s metadata — title, genre, blurb, themes — to keep drafts on-brand. The more fields you fill in, the better every output gets. Spend 15 minutes here once and every press release, social post, and sell sheet will come out tighter.',
    prereqs: [
      'Your book\'s title, subtitle, author name, and pen name (if applicable)',
      'A finished manuscript (or far enough along to know your themes)',
      'Your cover image at the highest resolution you have'
    ],
    steps: [
      {
        title: 'Upload the highest-resolution cover you have',
        desc: 'Cover image powers every quote card, social graphic, sell sheet, and trailer. If you upload a low-res cover, every downstream graphic looks low-res too. Aim for at least 1600×2400 px for the eBook cover.'
      },
      {
        title: 'Fill in title, subtitle, and series info',
        desc: 'Title and subtitle appear on press releases, sell sheets, and book trailers verbatim. If your book is part of a series, fill in the series name and book number — sell sheets and metadata feeds need these.'
      },
      {
        title: 'Add author name and pen name',
        desc: 'Author name is the legal/credit name; pen name is what appears on the cover if different. The AI uses pen name in cover letters, press releases, and bios automatically.'
      },
      {
        title: 'Pick a genre — be specific',
        desc: '"Fiction" is too vague. "Cozy mystery," "small-town romance," "epic fantasy" all give the AI the tone, audience, and reference points it needs. Use BISAC categories if you know them; otherwise use the term you\'d say to a bookseller.'
      },
      {
        title: 'Write the description / blurb',
        desc: 'The blurb is the single biggest input to AI-drafted descriptions, taglines, and social posts. Spend the most time here. 150–250 words is the sweet spot — long enough to give the AI hooks, short enough to feel like back-cover copy.'
      },
      {
        title: 'Add themes and keywords',
        desc: 'Themes are what your book is "about" beneath the plot — grief, redemption, found family, second chances. Keywords are search terms readers use — "small town," "slow burn," "WW2." Both feed into AI tagline, sell-sheet, and ad-copy generation.'
      },
      {
        title: 'Fill in formats, pricing, and ISBN',
        desc: 'Sell sheets need format (paperback, hardcover, eBook, audiobook), price, page count, and ISBN. Fill these in once and every sell sheet you generate from now on will have a complete metadata block.'
      },
      {
        title: 'Save and test',
        desc: 'Click Save, then go to any AI feature and click "AI Draft." Whatever comes back is now tuned to your book. If the output feels generic, that\'s a sign one of the fields above is empty or vague — come back here and tighten it.'
      }
    ]
  }
};

const SETUP_HELP_LS_KEY = 'setupHelpProgress';
const SETUP_HELP_SEEN_KEY = 'setupHelpSeen';

function _setupHelpRead(key) {
  try { return JSON.parse(localStorage.getItem(key)) || {}; }
  catch (e) { return {}; }
}
function _setupHelpWrite(key, val) {
  try { localStorage.setItem(key, JSON.stringify(val)); } catch (e) {}
}

function showSetupHelp(topic) {
  const t = setupHelpTopics[topic];
  if (!t) { console.warn('Unknown setup help topic:', topic); return; }
  const done = _setupHelpRead(SETUP_HELP_LS_KEY)[topic] || [];

  document.getElementById('setup-help-title').textContent = t.title;

  let html = '<p class="setup-help-intro">' + t.intro + '</p>';
  if (t.prereqs && t.prereqs.length) {
    html += '<div class="setup-help-prereqs"><strong>Before you start, you\'ll need:</strong><ul>';
    t.prereqs.forEach(p => { html += '<li>' + p + '</li>'; });
    html += '</ul></div>';
  }
  t.steps.forEach((s, i) => {
    const isDone = !!done[i];
    html += '<div class="setup-step' + (isDone ? ' done' : '') + '" data-step="' + i + '" '
         +    'onclick="toggleSetupStep(event, \'' + topic + '\', ' + i + ')">'
         +    '<div class="setup-step-check"><svg viewBox="0 0 16 16"><polyline points="3,8 7,11.5 13,4.5"></polyline></svg></div>'
         +    '<div class="setup-step-body">'
         +      '<div class="setup-step-title">' + s.title + '</div>'
         +      '<div class="setup-step-desc">' + s.desc + '</div>'
         +    '</div>'
         +  '</div>';
  });
  document.getElementById('setup-help-body').innerHTML = html;
  _updateSetupHelpProgress(topic);
  document.getElementById('setup-help-backdrop').style.display = 'flex';
  document.getElementById('setup-help-backdrop').dataset.topic = topic;

  // Mark seen so auto-show-once won't re-trigger
  const seen = _setupHelpRead(SETUP_HELP_SEEN_KEY);
  seen[topic] = true;
  _setupHelpWrite(SETUP_HELP_SEEN_KEY, seen);
}

function closeSetupHelp() {
  document.getElementById('setup-help-backdrop').style.display = 'none';
}

function toggleSetupStep(e, topic, idx) {
  // Let users click links inside the description without toggling the step
  if (e && e.target && e.target.tagName === 'A') return;
  const progress = _setupHelpRead(SETUP_HELP_LS_KEY);
  const arr = progress[topic] || [];
  arr[idx] = !arr[idx];
  progress[topic] = arr;
  _setupHelpWrite(SETUP_HELP_LS_KEY, progress);
  const row = document.querySelector('#setup-help-body .setup-step[data-step="' + idx + '"]');
  if (row) row.classList.toggle('done', !!arr[idx]);
  _updateSetupHelpProgress(topic);
}

function _updateSetupHelpProgress(topic) {
  const t = setupHelpTopics[topic];
  if (!t) return;
  const arr = _setupHelpRead(SETUP_HELP_LS_KEY)[topic] || [];
  const done = arr.filter(Boolean).length;
  const total = t.steps.length;
  const el = document.getElementById('setup-help-progress');
  if (el) el.innerHTML = '<strong>' + done + ' of ' + total + '</strong> step' + (total === 1 ? '' : 's') + ' complete';
}

// Optional: call from a view-init to auto-open the help the first time
// a user lands somewhere relevant. After that, the button is the trigger.
function maybeAutoShowSetupHelp(topic) {
  const seen = _setupHelpRead(SETUP_HELP_SEEN_KEY);
  if (!seen[topic]) showSetupHelp(topic);
}

// ── First-time welcome modal ─────────────────────────────────
// Shows up to WELCOME_MAX_SHOWS times so new users who skim it the
// first time get another chance. Each open increments the counter
// (persisted per-browser); auto-stops after the cap, and the user
// can opt out earlier with "Don't show this again." On dismiss, if
// the user has zero books, auto-opens the book-setup help.
const WELCOME_COUNT_KEY = 'welcomeModalCount';
const WELCOME_OPTOUT_KEY = 'welcomeModalOptOut';
const WELCOME_MAX_SHOWS = 5;

function welcomeShowCount() {
  try { return parseInt(localStorage.getItem(WELCOME_COUNT_KEY) || '0', 10) || 0; } catch (e) { return 0; }
}
function welcomeOptedOut() {
  try { return localStorage.getItem(WELCOME_OPTOUT_KEY) === '1'; } catch (e) { return false; }
}

function maybeShowWelcomeForNewUser() {
  if (!currentUser) return;
  if (currentUser.is_admin) return;
  if (currentUser.is_demo == 1 || currentUser.is_demo === true) return;
  // ⚠ Never on the standalone page. It is a tour of an app this visitor has
  // not bought, pointing at a sidebar that is hidden and features they have no
  // access to — and it opened over the top of the tool they came to use.
  if (window.AH_STANDALONE) return;
  if (currentUser.is_guest == 1 || currentUser.is_guest === true) return;
  if (welcomeOptedOut()) return;
  const shown = welcomeShowCount();
  if (shown >= WELCOME_MAX_SHOWS) return;
  const firstName = (currentUser.pen_name || currentUser.full_name || 'there').split(' ')[0];
  const greeting = document.getElementById('welcome-greeting');
  if (greeting) greeting.textContent = 'Welcome, ' + firstName + ' — quick orientation so you know where things live.';
  const counter = document.getElementById('welcome-show-counter');
  if (counter) {
    const remaining = WELCOME_MAX_SHOWS - shown - 1;
    counter.textContent = remaining > 0
      ? 'This intro will show ' + remaining + ' more time' + (remaining === 1 ? '' : 's') + ' so you can reread it. Use "Don’t show this again" to dismiss it for good.'
      : 'This is the last time this intro will show automatically.';
  }
  try { localStorage.setItem(WELCOME_COUNT_KEY, String(shown + 1)); } catch (e) {}
  const bd = document.getElementById('welcome-backdrop');
  if (bd) bd.style.display = 'flex';
  hydrateAssessmentChips();
}

function closeWelcome(optOut) {
  const bd = document.getElementById('welcome-backdrop');
  if (bd) bd.style.display = 'none';
  if (optOut) {
    try { localStorage.setItem(WELCOME_OPTOUT_KEY, '1'); } catch (e) {}
  }
  // Refresh the game plan so any chip-driven ranking changes show
  // up immediately when the user lands back on the dashboard.
  if (typeof loadGamePlan === 'function') loadGamePlan();
  if (Array.isArray(booksList) && booksList.length === 0) {
    setTimeout(() => maybeAutoShowSetupHelp('book-setup'), 150);
  }
}

// ── Welcome assessment chips ─────────────────────────────────
// Tri-state chips: unanswered → yes → no → unanswered. Each
// click POSTs the new value so the game plan ranker picks it up
// on the next fetch. Failures are silent — the chip flips back.
async function toggleAssessChip(btn) {
  const chip   = btn.getAttribute('data-chip');
  const state  = btn.getAttribute('data-state') || '';
  const next   = state === '' ? 'yes' : (state === 'yes' ? 'no' : '');
  const prev   = state;

  btn.setAttribute('data-state', next);

  const value = next === 'yes' ? true : (next === 'no' ? false : null);
  try {
    const payload = {}; payload[chip] = value;
    const res = await api('/setup_assessment.php', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    if (!res || !res.success) throw new Error(res && res.message);
  } catch (e) {
    btn.setAttribute('data-state', prev);
    toast('Could not save — try again', true);
  }
}

// On welcome-modal open, hydrate the chips from the server so
// returning users (who see the modal again before opt-out) keep
// their prior answers.
async function hydrateAssessmentChips() {
  try {
    const res = await api('/setup_assessment.php');
    if (!res || !res.success || !res.assessment) return;
    const chips = document.querySelectorAll('#welcome-assessment-chips .assess-chip');
    chips.forEach(btn => {
      const key = btn.getAttribute('data-chip');
      const v = res.assessment[key];
      if (v === true)       btn.setAttribute('data-state', 'yes');
      else if (v === false) btn.setAttribute('data-state', 'no');
      else                  btn.setAttribute('data-state', '');
    });
  } catch (e) { /* silent */ }
}

// CHATBOT widget — driven by /api/chat.php
// ══════════════════════════════════════════════
// Sophie's session_id rotates after CHAT_SESSION_TTL_MS of inactivity so the
// chatbot feels "fresh" the next time the user opens it — same UX pattern as
// support widgets like Intercom/Drift. The user's prior conversation stays in
// the DB; we just don't reload it for a stale session. A Download button in
// the header lets the user save the visible conversation before it ages out.
const CHAT_SESSION_TTL_MS = 60 * 60 * 1000; // 1 hour
let chatSessionId = null;
let chatLoaded = false;
let chatBusy   = false;

// initializeChatSession() — returns true if a NEW session was minted (i.e.
// first run, or the previous session was past its TTL). Existing users
// upgrading to this code path (have an old session_id but no last_active
// timestamp) get a one-time grace: we keep their session and stamp it now,
// so we don't wipe a still-loaded conversation the moment they refresh.
function initializeChatSession() {
  let id = null, lastActive = 0;
  try {
    id = localStorage.getItem('chat_session_id');
    lastActive = parseInt(localStorage.getItem('chat_session_last_active') || '0', 10) || 0;
  } catch (e) {}
  const now = Date.now();
  let rotated = false;
  if (!id) {
    id = 'cs_' + Math.random().toString(36).substr(2, 10) + '_' + now.toString(36);
    rotated = true;
  } else if (lastActive && (now - lastActive) > CHAT_SESSION_TTL_MS) {
    id = 'cs_' + Math.random().toString(36).substr(2, 10) + '_' + now.toString(36);
    rotated = true;
  }
  try {
    localStorage.setItem('chat_session_id', id);
    localStorage.setItem('chat_session_last_active', String(now));
  } catch (e) {}
  chatSessionId = id;
  return rotated;
}
function bumpChatActivity() {
  try { localStorage.setItem('chat_session_last_active', String(Date.now())); } catch (e) {}
}
initializeChatSession();

// Sophie's tooltip nudge — shows up to TOOLTIP_MAX times per browser,
// then auto-stops. The × button on the tooltip permanently opts out
// (TOOLTIP_OPTOUT_KEY). Auto-dismiss after 5s does NOT count as opting
// out — just hides for this session. Old `chat_tooltip_dismissed` flag
// (40b shape) gets cleaned up on load so existing testers see the new
// tooltip again.
const TOOLTIP_COUNT_KEY  = 'chat_tooltip_count_v2';
const TOOLTIP_OPTOUT_KEY = 'chat_tooltip_optout_v2';
const TOOLTIP_MAX_SHOWS  = 5;
// Wipe legacy keys so existing testers get a fresh count on this version.
try {
  localStorage.removeItem('chat_tooltip_dismissed');
  localStorage.removeItem('chat_tooltip_count');
  localStorage.removeItem('chat_tooltip_optout');
} catch (e) {}

function showChatFab()  {
  const f = document.getElementById('chat-fab'); if (f) f.classList.add('visible');
  let optedOut = false;
  let count = 0;
  try {
    optedOut = localStorage.getItem(TOOLTIP_OPTOUT_KEY) === '1';
    count = parseInt(localStorage.getItem(TOOLTIP_COUNT_KEY) || '0', 10) || 0;
  } catch (e) {}
  if (optedOut) return;
  if (count >= TOOLTIP_MAX_SHOWS) return;
  try { localStorage.setItem(TOOLTIP_COUNT_KEY, String(count + 1)); } catch (e) {}
  setTimeout(showChatTooltip, 1800);
}
function hideChatFab()  {
  const f = document.getElementById('chat-fab'); if (f) f.classList.remove('visible');
  const p = document.getElementById('chat-panel'); if (p) p.classList.remove('open');
  dismissChatTooltip(false);
}

function showChatTooltip() {
  const t = document.getElementById('chat-tooltip');
  const f = document.getElementById('chat-fab');
  if (!t || !f || !f.classList.contains('visible')) return;
  // Don't show on top of an already-open chat panel.
  const panel = document.getElementById('chat-panel');
  if (panel && panel.classList.contains('open')) return;
  t.classList.add('visible');
  // Trigger CSS transition on next frame.
  requestAnimationFrame(() => t.classList.add('shown'));
  f.classList.add('nudge');
  setTimeout(() => f.classList.remove('nudge'), 3500);
  // Auto-dismiss after ~5 seconds so it feels like a friendly nudge,
  // not a sticky overlay. User can still re-trigger by hovering the FAB,
  // and clicking the × persists the dismissal across sessions.
  setTimeout(() => {
    const stillThere = document.getElementById('chat-tooltip');
    if (stillThere && stillThere.classList.contains('visible')) dismissChatTooltip(false);
  }, 5000);
}

// dismissChatTooltip(permanent) — `permanent` true persists to localStorage so
// the tooltip never shows again for this user; false just hides it for now.
function dismissChatTooltip(permanent) {
  const t = document.getElementById('chat-tooltip');
  if (t) {
    t.classList.remove('shown');
    setTimeout(() => t.classList.remove('visible'), 320);
  }
  if (permanent) {
    try { localStorage.setItem(TOOLTIP_OPTOUT_KEY, '1'); } catch (e) {}
  }
}

function toggleChat() {
  if (!currentUser) return;
  const panel = document.getElementById('chat-panel');
  if (!panel) return;
  // Opening or closing the chat ends the tooltip's job.
  dismissChatTooltip(true);
  if (panel.classList.contains('open')) {
    panel.classList.remove('open');
    return;
  }
  // On open, check the inactivity TTL. If we minted a fresh session_id,
  // clear the rendered messages so the user sees a clean window with a
  // fresh greeting (history fetch for the new id will return empty).
  const rotated = initializeChatSession();
  if (rotated) {
    chatLoaded = false;
    const msgs = document.getElementById('chat-messages');
    if (msgs) msgs.innerHTML = '';
  }
  panel.classList.add('open');
  if (!chatLoaded) {
    loadChatHistory();
    chatLoaded = true;
  } else {
    setTimeout(() => {
      const m = document.getElementById('chat-messages');
      if (m) m.scrollTop = m.scrollHeight;
    }, 40);
  }
  bumpChatActivity();
  setTimeout(() => { const i = document.getElementById('chat-input'); if (i) i.focus(); }, 120);
}

async function loadChatHistory() {
  const msgs = document.getElementById('chat-messages');
  if (!msgs) return;
  msgs.innerHTML = '';
  // Fresh-greeting case scrolls to TOP so the user reads the intro from
  // the start; continuing-conversation case scrolls to BOTTOM so the
  // latest turn is in view. addChatMessage() always tails the scroll on
  // append, so this final positioning has to happen after a tick.
  let freshGreeting = false;
  try {
    const data = await api('/chat.php?action=history&session_id=' + encodeURIComponent(chatSessionId));
    if (data.success && Array.isArray(data.turns) && data.turns.length) {
      data.turns.forEach(t => {
        addChatMessage('user', t.user_message);
        addChatMessage('bot',  t.bot_response, { conversationId: t.id, wasHelpful: t.was_helpful });
      });
    } else {
      renderSophieGreeting();
      freshGreeting = true;
    }
  } catch (e) {
    addChatMessage('bot', "Hi — I'm Sophie, your assistant. Ask me anything about Elite Publishing.");
    freshGreeting = true;
  }
  setTimeout(() => {
    msgs.scrollTop = freshGreeting ? 0 : msgs.scrollHeight;
  }, 30);
}

// First-open greeting for Sophie. Friendly intro plus quick-start chips
// that map to common questions. Clicking a chip pre-fills the input and
// sends it — same path as a typed question.
function renderSophieGreeting() {
  const firstName = (currentUser && (currentUser.pen_name || currentUser.full_name) || '').split(' ')[0];
  const hello = firstName ? ('Hi ' + firstName + ' — I\'m Sophie.') : "Hi — I'm Sophie.";
  addChatMessage('bot',
    hello + " I'm your in-app assistant. I can answer questions about " +
    "your books, drafts, and posts, help you find a feature, walk you through setup, " +
    "or pull from the Learn lessons. How can I help today?");
  const suggestions = [
    { text: 'Walk me through setting up a feature', primary: true },
    'How many books do I have?',
    'Help me write a Facebook post',
    'Where do I connect my social platforms?',
    'Show me a lesson on book trailers',
    'Get a book printing quote'
  ];
  renderChatSuggestions(suggestions);
}

function renderChatSuggestions(items) {
  const msgs = document.getElementById('chat-messages');
  if (!msgs) return;
  const wrap = document.createElement('div');
  wrap.className = 'chat-suggestions';
  items.forEach(item => {
    // Items are strings OR { text, primary } objects. Primary chips get
    // a filled-accent style so they stand out — used for the setup-
    // walkthrough chip, which new users need most.
    const text    = (typeof item === 'string') ? item : item.text;
    const primary = (typeof item === 'object') && item.primary;
    const chip = document.createElement('button');
    chip.className = 'chat-suggestion-chip' + (primary ? ' chat-suggestion-chip-primary' : '');
    chip.type = 'button';
    chip.textContent = text;
    chip.onclick = () => {
      // "Get a book printing quote" is a navigation shortcut, not a chat question.
      if (/printing quote/i.test(text)) {
        toggleChat();
        navigate('print-quote');
        return;
      }
      const input = document.getElementById('chat-input');
      if (input) { input.value = text; input.focus(); }
      // Remove the suggestion strip after the user picks one to keep the
      // conversation clean.
      wrap.remove();
      sendChat();
    };
    wrap.appendChild(chip);
  });
  msgs.appendChild(wrap);
  msgs.scrollTop = msgs.scrollHeight;
}

function addChatMessage(role, text, opts) {
  const msgs = document.getElementById('chat-messages');
  if (!msgs) return null;
  const div = document.createElement('div');
  div.className = 'chat-msg ' + role;
  const bubble = document.createElement('div');
  bubble.className = 'chat-msg-bubble';
  bubble.textContent = text;
  div.appendChild(bubble);

  // Thumbs-up/down feedback row, only on real bot replies (not typing
  // indicators) and only when we know the backing conversation id.
  if (role === 'bot' && opts && opts.conversationId) {
    const wrap = document.createElement('div');
    wrap.className = 'chat-feedback';
    const up = document.createElement('button');
    up.className = 'chat-feedback-btn';
    up.title = 'This was helpful';
    up.setAttribute('aria-label', 'Mark helpful');
    up.textContent = '👍';
    up.onclick = () => rateChatMessage(opts.conversationId, 1, up, dn);
    const dn = document.createElement('button');
    dn.className = 'chat-feedback-btn';
    dn.title = 'This was not helpful';
    dn.setAttribute('aria-label', 'Mark not helpful');
    dn.textContent = '👎';
    dn.onclick = () => rateChatMessage(opts.conversationId, 0, up, dn);

    // Reflect prior rating if rendering history.
    if (opts.wasHelpful === 1 || opts.wasHelpful === '1') up.classList.add('rated-up');
    else if (opts.wasHelpful === 0 || opts.wasHelpful === '0') dn.classList.add('rated-down');

    wrap.appendChild(up);
    wrap.appendChild(dn);
    div.appendChild(wrap);
  }

  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
  return div;
}

async function rateChatMessage(conversationId, value, upBtn, dnBtn) {
  // Visual update first so it feels instant.
  upBtn.classList.toggle('rated-up',   value === 1);
  dnBtn.classList.toggle('rated-down', value === 0);
  try {
    await api('/chat.php?action=feedback', {
      method: 'POST',
      body: JSON.stringify({ conversation_id: conversationId, was_helpful: value })
    });
  } catch (e) {
    // Best-effort; don't disturb the user if it fails.
  }
}

function currentViewIdForChat() {
  const active = document.querySelector('.view.active');
  if (!active) return '';
  return (active.id || '').replace(/^view-/, '');
}

// ── FEEDBACK WIDGET ───────────────────────────────────────────
// Report an issue or request a feature. Submits to feedback.php, which
// stores a row and emails info@elitepublishing.co with the page, app
// version, and user context auto-attached.
let _feedbackType = 'bug';

function openFeedbackModal() {
  _feedbackType = 'bug';
  document.querySelectorAll('#feedback-backdrop .fb-type').forEach(el =>
    el.classList.toggle('active', el.dataset.type === 'bug'));
  const msg = document.getElementById('feedback-message');
  const err = document.getElementById('feedback-error');
  if (msg) msg.value = '';
  if (err) { err.style.display = 'none'; err.textContent = ''; }
  const bd = document.getElementById('feedback-backdrop');
  if (bd) bd.style.display = 'flex';
}

function closeFeedbackModal() {
  const bd = document.getElementById('feedback-backdrop');
  if (bd) bd.style.display = 'none';
}

function fbSelectType(el) {
  _feedbackType = el.dataset.type || 'other';
  document.querySelectorAll('#feedback-backdrop .fb-type').forEach(c =>
    c.classList.toggle('active', c === el));
}

// Read the deployed bundle version from the <script src> tag, for context.
function _appVersion() {
  const s = document.querySelector('script[src*="app.v"]');
  const m = s && s.src.match(/app\.v\d+\.js/);
  return m ? m[0] : '';
}

async function submitFeedback() {
  const msgEl = document.getElementById('feedback-message');
  const errEl = document.getElementById('feedback-error');
  const message = (msgEl && msgEl.value.trim()) || '';
  if (!message) {
    if (errEl) { errEl.textContent = 'Please add a short description first.'; errEl.style.display = 'block'; }
    return;
  }
  const btn = document.getElementById('feedback-send-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }

  try {
    const data = await api('/feedback.php', {
      method: 'POST',
      body: JSON.stringify({
        type: _feedbackType,
        message: message,
        page: currentViewIdForChat(),
        app_version: _appVersion(),
      }),
    });
    if (data && data.success) {
      closeFeedbackModal();
      toast(data.message || 'Thanks — feedback sent.');
    } else if (errEl) {
      errEl.textContent = (data && data.message) || 'Could not send — try again.';
      errEl.style.display = 'block';
    }
  } catch (e) {
    if (errEl) { errEl.textContent = 'Network error — try again.'; errEl.style.display = 'block'; }
  }
  if (btn) { btn.disabled = false; btn.textContent = 'Send feedback'; }
}

async function sendChat() {
  if (chatBusy) return;
  const input = document.getElementById('chat-input');
  if (!input) return;
  const text = input.value.trim();
  if (!text) return;
  chatBusy = true;
  input.value = '';
  addChatMessage('user', text);
  const typingNode = addChatMessage('bot', 'Thinking…');
  if (typingNode) typingNode.querySelector('.chat-msg-bubble').classList.add('chat-typing');
  try {
    const data = await api('/chat.php?action=ask', {
      method: 'POST',
      body: JSON.stringify({
        message:      text,
        session_id:   chatSessionId,
        view_context: currentViewIdForChat()
      })
    });
    if (typingNode) typingNode.remove();
    if (data.success && data.response) {
      addChatMessage('bot', data.response, { conversationId: data.conversation_id });
    } else {
      addChatMessage('bot', data.message || "Sorry — I hit a snag answering that. Try again, or rephrase?");
    }
    bumpChatActivity();
  } catch (e) {
    if (typingNode) typingNode.remove();
    addChatMessage('bot', "Network hiccup. Try again?");
  } finally {
    chatBusy = false;
    input.focus();
  }
}

// Download the visible Sophie conversation as a plain-text file. Skips the
// transient "Thinking…" bubble. Always available in the header so the user
// can save a record before the session ages out or they close the panel.
function downloadChatConversation() {
  const msgs = document.getElementById('chat-messages');
  if (!msgs) return;
  const bubbles = msgs.querySelectorAll('.chat-msg');
  const lines = [];
  lines.push("Sophie — Elite Publishing assistant");
  lines.push('Conversation saved ' + new Date().toLocaleString());
  lines.push('────────────────────────────────────────');
  lines.push('');
  let realMessages = 0;
  bubbles.forEach(b => {
    const bubble = b.querySelector('.chat-msg-bubble');
    if (!bubble) return;
    if (bubble.classList.contains('chat-typing')) return;
    const who = b.classList.contains('user') ? 'You' : 'Sophie';
    lines.push(who + ':');
    lines.push(bubble.textContent);
    lines.push('');
    realMessages++;
  });
  if (!realMessages) {
    if (typeof toast === 'function') toast('No conversation to save yet.');
    return;
  }
  const blob = new Blob([lines.join('\n')], { type: 'text/plain;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  const stamp = new Date().toISOString().slice(0,16).replace(/[T:]/g, '-');
  a.download = 'sophie-chat-' + stamp + '.txt';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

// Enter to send, Shift+Enter for newline. Attach once DOM is ready.
(function () {
  function wireChatInput() {
    const input = document.getElementById('chat-input');
    if (!input) return;
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendChat();
      }
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wireChatInput);
  } else {
    wireChatInput();
  }
})();

// CHAT ADMIN — chatlog viewer + override management (Phase 2)
// ════════════════════════════════════════════════════════════
function escapeHtmlSafe(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

async function loadAdminChatlog() {
  const wrap = document.getElementById('acl-list');
  if (!wrap) return;
  wrap.innerHTML = '<div class="empty">Loading…</div>';
  const filter = (document.getElementById('acl-filter')?.value) || 'all';
  const q = filter === 'unhelpful' ? '&unhelpful_only=1' : '';
  try {
    const data = await api('/chat.php?action=admin_conversations&limit=100' + q);
    if (!data.success) throw new Error(data.message || 'failed');
    const convs = data.conversations || [];
    if (!convs.length) {
      wrap.innerHTML = '<div class="empty">No conversations yet.</div>';
      return;
    }
    wrap.innerHTML = convs.map(renderChatlogRow).join('');
  } catch (e) {
    wrap.innerHTML = '<div class="empty">Could not load chat log.</div>';
  }
}

function renderChatlogRow(c) {
  const when = new Date(c.created_at + (c.created_at.endsWith('Z') ? '' : 'Z')).toLocaleString();
  const who  = escapeHtmlSafe(c.user_name || c.user_email || ('user #' + c.user_id));
  const view = c.view_context ? ' · view: <code>' + escapeHtmlSafe(c.view_context) + '</code>' : '';
  let badge = '';
  if (c.was_helpful === 1 || c.was_helpful === '1') badge = ' <span class="badge badge-green">helpful</span>';
  else if (c.was_helpful === 0 || c.was_helpful === '0') badge = ' <span class="badge badge-red">not helpful</span>';
  return ''
    + '<div style="border:1px solid var(--ink-faint);border-radius:6px;padding:12px;margin-bottom:10px">'
    +   '<div style="font-size:12px;color:var(--ink-soft);margin-bottom:8px">'
    +     who + ' · ' + escapeHtmlSafe(when) + view + badge
    +   '</div>'
    +   '<div style="margin-bottom:8px"><strong>User:</strong> ' + escapeHtmlSafe(c.user_message) + '</div>'
    +   '<div style="margin-bottom:8px;padding:8px 10px;background:var(--paper-soft);border-radius:6px"><strong>Bot:</strong> ' + escapeHtmlSafe(c.bot_response) + '</div>'
    +   '<div style="display:flex;gap:8px;justify-content:flex-end">'
    +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="openOverrideModal(' + (c.id|0) + ', ' + JSON.stringify(c.user_message || '').replace(/"/g, '&quot;') + ', ' + JSON.stringify(c.bot_response || '').replace(/"/g, '&quot;') + ')">Wrong answer → Teach the bot</button>'
    +   '</div>'
    + '</div>';
}

async function loadAdminOverrides() {
  const wrap = document.getElementById('ovr-list');
  if (!wrap) return;
  wrap.innerHTML = '<div class="empty">Loading…</div>';
  try {
    const data = await api('/chat.php?action=admin_overrides');
    if (!data.success) throw new Error(data.message || 'failed');
    const list = data.overrides || [];
    if (!list.length) {
      wrap.innerHTML = '<div class="empty">No overrides yet. Catch a wrong answer in the Chat Log and click "Wrong answer" to teach the bot.</div>';
      return;
    }
    wrap.innerHTML = list.map(renderOverrideRow).join('');
  } catch (e) {
    wrap.innerHTML = '<div class="empty">Could not load overrides.</div>';
  }
}

function renderOverrideRow(o) {
  const active = (o.active === 1 || o.active === '1');
  const stateBadge = active
    ? '<span class="badge badge-green">Active</span>'
    : '<span class="badge badge-gray">Disabled</span>';
  const when = new Date(o.created_at + (o.created_at.endsWith('Z') ? '' : 'Z')).toLocaleDateString();
  return ''
    + '<div style="border:1px solid var(--ink-faint);border-radius:6px;padding:12px;margin-bottom:10px">'
    +   '<div style="font-size:12px;color:var(--ink-soft);margin-bottom:8px">'
    +     stateBadge + ' &nbsp;·&nbsp; added ' + escapeHtmlSafe(when)
    +     (o.created_by_email ? ' by ' + escapeHtmlSafe(o.created_by_email) : '')
    +   '</div>'
    +   '<div style="margin-bottom:8px"><strong>When:</strong> ' + escapeHtmlSafe(o.scenario_description) + '</div>'
    +   '<div style="margin-bottom:8px;padding:8px 10px;background:var(--paper-soft);border-radius:6px"><strong>Answer:</strong> ' + escapeHtmlSafe(o.correct_answer) + '</div>'
    +   '<div style="display:flex;gap:8px;justify-content:flex-end">'
    +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="toggleOverride(' + (o.id|0) + ', ' + (active ? 'false' : 'true') + ')">' + (active ? 'Disable' : 'Re-enable') + '</button>'
    +     '<button class="app-btn app-btn-outline app-btn-sm" style="color:var(--danger);border-color:#FECACA" onclick="deleteOverride(' + (o.id|0) + ')">Delete</button>'
    +   '</div>'
    + '</div>';
}

function openOverrideModal(sourceConvId, userQuestion, botAnswer) {
  document.getElementById('ovr-scenario').value = userQuestion ? ('When users ask: "' + userQuestion + '"') : '';
  document.getElementById('ovr-answer').value   = '';
  document.getElementById('ovr-source-conv-id').value = sourceConvId ? String(sourceConvId|0) : '';
  const ctx = document.getElementById('ovr-source-context');
  if (sourceConvId && botAnswer) {
    ctx.innerHTML = '<strong>Bot previously said:</strong> ' + escapeHtmlSafe(botAnswer);
    ctx.style.display = 'block';
  } else {
    ctx.style.display = 'none';
    ctx.innerHTML = '';
  }
  const err = document.getElementById('ovr-error');
  if (err) { err.style.display = 'none'; err.textContent = ''; }
  document.getElementById('ovr-backdrop').style.display = 'flex';
  setTimeout(() => document.getElementById('ovr-scenario').focus(), 50);
}

function closeOverrideModal() {
  document.getElementById('ovr-backdrop').style.display = 'none';
}

async function saveOverride() {
  const scenario = document.getElementById('ovr-scenario').value.trim();
  const answer   = document.getElementById('ovr-answer').value.trim();
  const sourceId = (document.getElementById('ovr-source-conv-id').value || '').trim();
  const err = document.getElementById('ovr-error');
  const btn = document.getElementById('ovr-save-btn');
  err.style.display = 'none'; err.textContent = '';

  if (!scenario) { err.textContent = 'Describe when this applies.'; err.style.display = 'block'; return; }
  if (!answer)   { err.textContent = "What's the correct answer?"; err.style.display = 'block'; return; }

  btn.disabled = true; btn.textContent = 'Saving…';
  try {
    const data = await api('/chat.php?action=admin_create_override', {
      method: 'POST',
      body: JSON.stringify({
        scenario_description: scenario,
        correct_answer:       answer,
        source_conversation_id: sourceId ? parseInt(sourceId, 10) : 0
      })
    });
    if (!data.success) {
      err.textContent = data.message || 'Could not save.';
      err.style.display = 'block';
      btn.disabled = false; btn.textContent = 'Save override';
      return;
    }
    closeOverrideModal();
    toast(data.message || 'Override saved.');
    if (typeof loadAdminOverrides === 'function' && document.getElementById('view-admin-overrides').classList.contains('active')) {
      loadAdminOverrides();
    }
  } catch (e) {
    err.textContent = 'Network error. Try again.';
    err.style.display = 'block';
    btn.disabled = false; btn.textContent = 'Save override';
  }
}

async function toggleOverride(id, active) {
  const data = await api('/chat.php?action=admin_toggle_override', {
    method: 'POST',
    body: JSON.stringify({ id: id, active: active ? 1 : 0 })
  });
  if (data.success) toast(data.message);
  else toast(data.message || 'Failed', true);
  loadAdminOverrides();
}

async function deleteOverride(id) {
  if (!confirm('Delete this override? The bot will lose this correction.')) return;
  const data = await api('/chat.php?action=admin_delete_override', {
    method: 'POST',
    body: JSON.stringify({ id: id })
  });
  if (data.success) toast('Deleted.');
  else toast(data.message || 'Delete failed', true);
  loadAdminOverrides();
}

// ── SLIDESHOW VIDEO (v118) ────────────────────────────────────
// Turns images into an MP4 via slideshow_render.php — sibling of the
// trailer above, sharing video_status.php polling and the
// video_renders_per_month quota pool. Two ways in:
//   AI storyboard: theme → per-slide text + narration (one aiGenerate
//   call, so slide text and spoken line always match), every field
//   editable, then slideshow_compose.php renders the slide images.
//   Manual: upload your own 2-12 images.
// No AI backdrops either way: slides ARE the finished art, so renders
// are faster and cheaper than trailers.

let _ssImages         = [];    // public URLs, in play order
let _ssVoiceUrl       = '';    // uploaded narration MP3 ('' = none)
let _ssStoryboard     = null;  // last generated storyboard (slides array)
let _ssVisualStyle    = '';    // deck-level protagonist/setting/palette sentence
let _ssStyleRefs      = [];    // uploaded style-reference image URLs (max 3)
let _ssNarrationLines = [];    // per-slide narration (storyboard mode)
let _ssCaptions       = [];    // per-slide text overlays (manual captions)
let _ssMusicUrl       = '';    // uploaded soundtrack MP3 ('' = mood library)
let _ssRenderId       = null;
let _ssPollHandle     = null;
let _ssElapsedHandle  = null;
let _ssStartedAt      = 0;

const _ssFormatDims = {
  '4x5':  [1080, 1350],
  '9x16': [1080, 1920],
  '1x1':  [1080, 1080],
  '16x9': [1920, 1080],
};

function initSlideshowView() {
  ssResetProgress();
  ssRenderStrip();
  ssNarrationMode();
  ssLoadQuota();
  svLoadStyleRefs();
  if (typeof svGoStep === 'function') svGoStep(1);
  if (!_ssStoryboard) {
    try {
      const saved = JSON.parse(sessionStorage.getItem('svStoryboard') || 'null');
      if (saved && Array.isArray(saved.slides) && saved.slides.length) {
        svAdoptStoryboard(saved.slides, saved.vs || '');
      }
    } catch (e) { /* nothing saved */ }
  }
}

// Load the author's saved brand style kit (survives reloads; migration 038).
async function svLoadStyleRefs() {
  try {
    const data = await api('/slideshow_style_refs.php');
    if (data?.success && Array.isArray(data.refs) && data.refs.length) {
      _ssStyleRefs = data.refs;
      svStyleRefLabel();
    }
  } catch (e) { /* kit stays empty */ }
}

function svStyleRefLabel() {
  const label = document.getElementById('sv-styleref-label');
  const clear = document.getElementById('sv-styleref-clear');
  if (label) label.textContent = _ssStyleRefs.length
    ? _ssStyleRefs.length + ' style image' + (_ssStyleRefs.length > 1 ? 's' : '') + ' saved ✓'
    : '';
  if (clear) clear.style.display = _ssStyleRefs.length ? 'inline' : 'none';
}

async function svClearStyleRefs() {
  _ssStyleRefs = [];
  svStyleRefLabel();
  try { await api('/slideshow_style_refs.php', { method: 'POST', body: JSON.stringify({ refs: [] }) }); } catch (e) {}
  toast('Style kit cleared');
}

function ssResetProgress() {
  if (_ssPollHandle)    { clearTimeout(_ssPollHandle); _ssPollHandle = null; }
  if (_ssElapsedHandle) { clearInterval(_ssElapsedHandle); _ssElapsedHandle = null; }
  _ssRenderId = null;
  const ids = ['sv-status-card', 'sv-output-card', 'sv-error-card'];
  ids.forEach(id => { const el = document.getElementById(id); if (el) el.style.display = 'none'; });
}

async function ssLoadQuota() {
  const pill = document.getElementById('sv-quota-pill');
  const btn  = document.getElementById('sv-generate-btn');
  if (!pill) return;
  try {
    const data = await api('/video_quota.php');
    if (!data?.success) { pill.textContent = ''; return; }
    if (data.is_admin) {
      pill.innerHTML = '<span style="color:var(--accent);font-weight:500">Admin — unlimited renders</span>';
      return;
    }
    if (data.cap <= 0) {
      pill.innerHTML = 'Video renders not in your plan — <a href="#" onclick="navigate(\'pricing\');return false">upgrade</a>';
      if (btn) btn.disabled = true;
      return;
    }
    if (data.remaining <= 0) {
      pill.innerHTML = '<span style="color:#c44">' + data.used + ' / ' + data.cap + ' renders used this month</span> — '
        + '<a href="#" onclick="navigate(\'pricing\');return false">upgrade for more</a>';
      if (btn) btn.disabled = true;
    } else {
      pill.textContent = data.remaining + ' of ' + data.cap + ' renders left this month (shared with trailers)';
      if (btn) btn.disabled = false;
    }
  } catch (e) {
    pill.textContent = '';
  }
}

// ── AI storyboard ─────────────────────────────────────────────

async function ssGenerateStoryboard() {
  const bookId = parseInt(document.getElementById('sv-sb-book')?.value || '0');
  const theme  = (document.getElementById('sv-sb-theme')?.value || '').trim();
  const count  = parseInt(document.getElementById('sv-sb-count')?.value || '6');
  if (!bookId) { toast('Select a book first — the storyboard is built around it', true); return; }
  if (!theme)  { toast('Describe the marketing idea first', true); return; }

  const btn  = document.getElementById('sv-sb-btn');
  const orig = btn ? btn.textContent : '';
  if (btn) { btn.disabled = true; btn.textContent = 'Planning your slideshow…'; }
  try {
    const data = await api('/slideshow_storyboard.php', {
      method: 'POST',
      body: JSON.stringify({ book_id: bookId, theme: theme, slide_count: count }),
    });
    if (!data?.success || !data?.storyboard?.slides) {
      toast(data?.message || 'Storyboard generation failed — please try again', true);
      return;
    }
    svAdoptStoryboard(data.storyboard.slides, data.storyboard.visual_style || '');
    toast('Storyboard ready — review and edit anything below, then click Next: the image buttons are in step 2');
  } catch (e) {
    toast(e?.message || 'Storyboard generation failed', true);
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = orig; }
  }
}

// Take a storyboard (AI-generated or pasted) and wire the whole page to it:
// editor fields, per-slide narration, TTS prefill, narration mode default.
function svAdoptStoryboard(slides, visualStyle) {
  _ssStoryboard = slides;
  _ssVisualStyle = visualStyle || '';
  try { sessionStorage.setItem('svStoryboard', JSON.stringify({ slides: slides, vs: _ssVisualStyle })); } catch (e) {}
  ssRenderStoryboard();
  svPrefillCaptions();
  ssRenderStrip();
  _ssNarrationLines = _ssStoryboard.map(s => s.narration || '');
  const ttsBox = document.getElementById('sv-narration-text');
  if (ttsBox) ttsBox.value = _ssNarrationLines.filter(Boolean).join(' ');
  const sbLabel = document.getElementById('sv-mode-storyboard');
  if (sbLabel) sbLabel.style.display = 'flex';
  const cur = document.querySelector('input[name="sv-narration-mode"]:checked')?.value || 'none';
  if (cur === 'none') {
    const r = document.querySelector('input[name="sv-narration-mode"][value="storyboard"]');
    if (r) r.checked = true;
  }
  ssNarrationMode();
}

// Manual storyboard paste: blocks separated by blank lines; within a block,
// line 1 = headline, line 2 = subtext, line 3 = spoken narration (2 and 3
// optional). "Headline:" / "Subtext:" / "Narration:" labels also accepted.
function svParseStoryboard() {
  const raw = (document.getElementById('sv-sb-paste')?.value || '').trim();
  if (!raw) { toast('Paste your storyboard text first', true); return; }
  let blocks = raw.split(/\n\s*\n+/).map(b => b.trim()).filter(Boolean);
  if (blocks.length < 2) {
    // No blank lines survived the paste. Plan B: an ALL-CAPS-ish line starts
    // a new slide (headlines are conventionally uppercase). Plan C: chunk in 3s.
    const lines = raw.split(/\n/).map(l => l.trim()).filter(Boolean);
    const isHead = l => {
      const letters = l.replace(/[^A-Za-z]/g, '');
      return letters.length >= 4 && letters === letters.toUpperCase();
    };
    if (lines.filter(isHead).length >= 2) {
      blocks = [];
      let cur = [];
      lines.forEach(l => {
        if (isHead(l) && cur.length) { blocks.push(cur.join('\n')); cur = []; }
        cur.push(l);
      });
      if (cur.length) blocks.push(cur.join('\n'));
    } else if (lines.length >= 6 && lines.length % 3 === 0) {
      blocks = [];
      for (let i = 0; i < lines.length; i += 3) blocks.push(lines.slice(i, i + 3).join('\n'));
    }
  }
  blocks = blocks.slice(0, 12);
  if (blocks.length < 2) { toast('I couldn\'t split that into slides — separate slides with a blank line, or start each slide with an ALL-CAPS headline', true); return; }
  const slides = blocks.map((block, i) => {
    const lines = block.split(/\n/).map(l => l.trim()).filter(Boolean);
    const out = { headline: '', subtext: '', narration: '', image_brief: '' };
    const rest = [];
    lines.forEach(l => {
      const m = l.match(/^(headline|subtext|sub|narration|voice)\s*:\s*(.*)$/i);
      if (m) {
        const key = m[1].toLowerCase();
        if (key === 'headline') out.headline = m[2];
        else if (key === 'narration' || key === 'voice') out.narration = m[2];
        else out.subtext = m[2];
      } else rest.push(l.replace(/^\d+[\.\)]\s*/, ''));
    });
    if (!out.headline)  out.headline  = rest.shift() || '';
    if (!out.subtext)   out.subtext   = rest.shift() || '';
    if (!out.narration) out.narration = rest.shift() || '';
    out.kind = i === 0 ? 'hook' : (i === blocks.length - 1 ? 'cta' : 'content');
    return out;
  });
  if (slides.some(s => !s.headline)) { toast('Every slide needs at least a headline on its first line', true); return; }
  svAdoptStoryboard(slides, '');
  const wrap = document.getElementById('sv-sb-paste-wrap');
  if (wrap) wrap.style.display = 'none';
  toast(slides.length + ' slides loaded — review below, then click Next: create images (or upload your own) in step 2');
}

function svTogglePaste() {
  const wrap = document.getElementById('sv-sb-paste-wrap');
  if (wrap) wrap.style.display = (wrap.style.display === 'none' || !wrap.style.display) ? 'block' : 'none';
}

// Editable storyboard rows: headline, subtext, narration per slide. Typing
// over the text is the fastest fix for a slide you don't love — the fields
// are read back at compose time, so edits always win.
function ssRenderStoryboard() {
  const wrap    = document.getElementById('sv-sb-editor');
  const actions = document.getElementById('sv-sb-actions');
  if (!wrap || !_ssStoryboard) return;
  const kindLabels = { hook: 'Hook', content: 'Content', payoff: 'Payoff', cta: 'Call to action' };
  wrap.innerHTML = _ssStoryboard.map((s, i) =>
    '<div style="border:1px solid var(--border,#e4ded2);border-radius:8px;padding:12px 14px;margin-bottom:10px;background:#fff">'
    + '<div style="font-size:11.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--accent);margin-bottom:8px">'
    +   'Slide ' + (i + 1) + ' — ' + (kindLabels[s.kind] || 'Content') + '</div>'
    + '<div class="field-group" style="margin-bottom:8px"><label class="field-label" style="font-size:11px">On-slide headline</label>'
    +   '<input type="text" class="sv-sb-h" data-i="' + i + '" maxlength="80" value="' + _ssEsc(s.headline) + '"></div>'
    + '<div class="field-group" style="margin-bottom:8px"><label class="field-label" style="font-size:11px">On-slide subtext</label>'
    +   '<input type="text" class="sv-sb-s" data-i="' + i + '" maxlength="140" value="' + _ssEsc(s.subtext) + '"></div>'
    + '<div class="field-group" style="margin-bottom:0"><label class="field-label" style="font-size:11px">Spoken narration for this slide</label>'
    +   '<textarea class="sv-sb-n" data-i="' + i + '" rows="2" maxlength="220">' + _ssEsc(s.narration) + '</textarea></div>'
    + '</div>'
  ).join('');
  wrap.style.display = 'block';
  if (actions) actions.style.display = 'flex';
}

function _ssEsc(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Style reference images: the author uploads 1-3 images whose look the AI
// matches (palette, lighting, setting). Reuses the slide-image uploader.
async function svUploadStyleRefs(input) {
  const files = Array.from(input.files || []).slice(0, 6);
  if (!files.length) return;
  const label = document.getElementById('sv-styleref-label');
  try {
    const fd = new FormData();
    files.forEach(f => fd.append('images[]', f));
    const headers = {};
    const token = localStorage.getItem('auth_token');
    if (token) headers['X-Auth-Token'] = token;
    const res  = await fetch('/api/slideshow_upload.php', {
      method: 'POST', headers: headers, credentials: 'same-origin', body: fd,
    });
    const data = await res.json();
    if (!data.success || !data.urls) throw new Error(data.message || 'Upload failed');
    _ssStyleRefs = data.urls.slice(0, 6);
    svStyleRefLabel();
    try { await api('/slideshow_style_refs.php', { method: 'POST', body: JSON.stringify({ refs: _ssStyleRefs }) }); } catch (e) {}
    toast('Style kit saved — AI images will match this look on every visit');
  } catch (e) {
    _ssStyleRefs = [];
    svStyleRefLabel();
    toast(e.message || 'Style upload failed', true);
  } finally {
    input.value = '';
  }
}

// Generate one AI scene image per storyboard slide, sequentially — live
// progress in the button, per-image billing/quota on the server, and any
// already-created images survive a mid-deck failure. Text-free scenes:
// the caption overlays (auto-enabled, prefilled) carry the slide text.
async function ssGenerateAiImages() {
  if (!_ssStoryboard) { toast('Generate a storyboard first', true); return; }
  const bookId = parseInt(document.getElementById('sv-sb-book')?.value || '0');
  const edited = _ssReadStoryboard() || [];
  const n = _ssStoryboard.length;
  const btnIds = ['sv-sb-ai', 'sv-sb-compose', 'sv-sb-btn'];
  btnIds.forEach(id => { const b = document.getElementById(id); if (b) b.disabled = true; });
  const aiBtn = document.getElementById('sv-sb-ai');
  const orig  = aiBtn ? aiBtn.textContent : '';
  _ssImages = []; _ssCaptions = [];
  ssRenderStrip();
  try {
    for (let i = 0; i < n; i++) {
      const brief = (_ssStoryboard[i].image_brief && _ssStoryboard[i].image_brief.trim())
        ? _ssStoryboard[i].image_brief
        : ('A scene expressing: ' + ((edited[i] && edited[i].headline) || _ssStoryboard[i].headline || ''));
      // One automatic retry per image — the image service sometimes has slow
      // patches (multi-minute) and the occasional timeout; a retry usually lands.
      let data = null;
      for (let attempt = 1; attempt <= 2 && !(data?.success && data?.url); attempt++) {
        if (aiBtn) aiBtn.textContent = 'Creating image ' + (i + 1) + ' of ' + n
          + (attempt > 1 ? ' (retrying)' : '') + '\u2026 can take a few minutes when busy';
        try {
          data = await api('/slideshow_images.php', {
            method: 'POST',
            body: JSON.stringify({
              brief: brief,
              style: _ssVisualStyle,
              style_refs: _ssImages.length ? _ssStyleRefs.concat([_ssImages[0]]) : _ssStyleRefs,
              book_id: bookId,
            }),
          });
        } catch (e) { data = { success: false, message: e?.message || 'network error' }; }
      }
      if (!data?.success || !data?.url) {
        toast((data?.message || 'Image ' + (i + 1) + ' failed') + ' \u2014 keeping the ' + _ssImages.length + ' finished so far', true);
        break;
      }
      _ssImages.push(data.url);
      ssRenderStrip();
    }
    if (_ssImages.length) {
      const tog = document.getElementById('sv-overlay-toggle');
      if (tog) tog.checked = true;   // scenes are text-free — overlays carry the words
      svPrefillCaptions();
      ssRenderStrip();
      toast(_ssImages.length + ' of ' + n + ' images created \u2014 slide text is prefilled below; render when ready');
    }
  } finally {
    btnIds.forEach(id => { const b = document.getElementById(id); if (b) b.disabled = false; });
    if (aiBtn) aiBtn.textContent = orig;
  }
}

// Package the storyboard's per-slide scene briefs as a ready-to-paste
// ChatGPT image prompt: 4:5, strictly text-free, consistent style, and
// one-image-at-a-time (a single ask for N images comes back as a collage).
function svBuildImagePrompt() {
  if (!_ssStoryboard) return null;
  const edited = _ssReadStoryboard() || [];
  const theme  = (document.getElementById('sv-sb-theme')?.value || '').trim();
  const n      = _ssStoryboard.length;
  const lines  = _ssStoryboard.map((s, i) => {
    const brief = (s.image_brief && s.image_brief.trim())
      ? s.image_brief.trim()
      : ('A scene expressing: ' + ((edited[i] && edited[i].headline) || s.headline || ''));
    return 'Slide ' + (i + 1) + ': ' + brief;
  });
  return 'I\'m creating a promotional slideshow video'
    + (theme ? ' about: ' + theme : '') + '.\n'
    + 'I need ' + n + ' separate images, one per slide.\n\n'
    + 'Rules for every image:\n'
    + '- Exactly 1080 x 1350 pixels (portrait 4:5).\n'
    + '- Keep one consistent visual style, color palette, and mood across all ' + n + ' images'
    + (_ssVisualStyle ? ': ' + _ssVisualStyle : '.') + '\n'
    + '- Create ONE image at a time: make Slide 1 first, then wait for me to say \u201cnext\u201d before creating each following slide. Never combine slides into one image.\n\n'
    + 'About the images I am attaching to this chat:\n'
    + '- If I attach a book cover, use that exact cover art wherever the book appears — composite it in as-is, never redraw or reinterpret it.\n'
    + '- If I attach app screenshots, then whenever a slide shows a laptop, phone, or screen, place the attached screenshot that best matches that slide\'s moment onto the screen exactly as provided, unaltered — never invent an interface.\n'
    + '- Use each attachment on the slides where it belongs; tell me if a slide needs an attachment I haven\'t provided.\n\n'
    + 'The slide scenes:\n' + lines.join('\n');
}

async function svCopyImagePrompt() {
  const text = svBuildImagePrompt();
  if (!text) { toast('Generate a storyboard first', true); return; }
  try {
    await navigator.clipboard.writeText(text);
    toast('ChatGPT image prompt copied — paste it into ChatGPT, then upload the images below');
  } catch (e) {
    // Clipboard can be blocked (permissions/http) — fall back to a prompt box.
    window.prompt('Copy the prompt below:', text);
  }
}

// Read the (possibly edited) storyboard back out of the editor fields.
function _ssReadStoryboard() {
  if (!_ssStoryboard) return null;
  return _ssStoryboard.map((s, i) => ({
    kind:      s.kind,
    headline:  (document.querySelector('.sv-sb-h[data-i="' + i + '"]')?.value || '').trim(),
    subtext:   (document.querySelector('.sv-sb-s[data-i="' + i + '"]')?.value || '').trim(),
    narration: (document.querySelector('.sv-sb-n[data-i="' + i + '"]')?.value || '').trim(),
  }));
}

async function ssComposeSlides() {
  const slides = _ssReadStoryboard();
  if (!slides) return;
  if (slides.some(s => !s.headline)) { toast('Every slide needs a headline', true); return; }
  const bookId = parseInt(document.getElementById('sv-sb-book')?.value || '0');

  const btn  = document.getElementById('sv-sb-compose');
  const orig = btn ? btn.textContent : '';
  if (btn) { btn.disabled = true; btn.textContent = 'Creating slides…'; }
  try {
    const data = await api('/slideshow_compose.php', {
      method: 'POST',
      body: JSON.stringify({ slides: slides, book_id: bookId }),
    });
    if (!data?.success || !data?.urls) {
      toast(data?.message || 'Slide creation failed — please try again', true);
      return;
    }
    _ssImages = data.urls;
    _ssNarrationLines = slides.map(s => s.narration);
    // Prefill the single-script box too, so "AI voice reads my script" is
    // never blank after a storyboard — whichever mode the author picks works.
    const ttsBox = document.getElementById('sv-narration-text');
    if (ttsBox) ttsBox.value = _ssNarrationLines.filter(Boolean).join(' ');
    ssRenderStrip();
    // Storyboard narration becomes available and is the obvious default now.
    const sbLabel = document.getElementById('sv-mode-storyboard');
    if (sbLabel) sbLabel.style.display = 'flex';
    const sbRadio = document.querySelector('input[name="sv-narration-mode"][value="storyboard"]');
    if (sbRadio) sbRadio.checked = true;
    ssNarrationMode();
    if (data.low_res_cover) {
      toast('Heads up: your book cover is low-resolution — it may look soft on the slides. Upload a larger cover on the Books page for best results.', true);
    } else {
      toast(data.urls.length + ' slides created — check them below, then generate the video');
    }
  } catch (e) {
    toast(e?.message || 'Slide creation failed', true);
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = orig; }
  }
}

// ── Manual image upload ───────────────────────────────────────

async function ssAddImages(input) {
  const files = Array.from(input.files || []);
  if (!files.length) return;
  if (_ssImages.length + files.length > 12) {
    toast('A slideshow can use at most 12 images', true);
    input.value = '';
    return;
  }
  const btn  = document.getElementById('sv-add-btn');
  const orig = btn ? btn.textContent : '';
  if (btn) { btn.disabled = true; btn.textContent = 'Uploading ' + files.length + '…'; }
  try {
    const fd = new FormData();
    files.forEach(f => fd.append('images[]', f));
    const headers = {};   // no Content-Type — browser sets the multipart boundary
    const token = localStorage.getItem('auth_token');
    if (token) headers['X-Auth-Token'] = token;
    const res  = await fetch('/api/slideshow_upload.php', {
      method: 'POST', headers: headers, credentials: 'same-origin', body: fd,
    });
    const data = await res.json();
    if (!data.success || !data.urls) throw new Error(data.message || 'Upload failed');
    _ssImages = _ssImages.concat(data.urls);
    svPrefillCaptions();
    ssRenderStrip();
    toast(data.urls.length + ' image' + (data.urls.length > 1 ? 's' : '') + ' added');
  } catch (e) {
    toast(e.message || 'Upload failed — please try again', true);
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = orig; }
    input.value = '';
  }
}

function ssRenderStrip() {
  const strip = document.getElementById('sv-strip');
  if (!strip) return;
  if (!_ssImages.length) {
    strip.innerHTML = '<div class="empty" style="padding:14px">No slides yet — use the AI storyboard above, or add 2 to 12 of your own images. '
      + 'Quote cards, social graphics, event flyers, covers: anything you\'ve made works.</div>';
    return;
  }
  const caps = !!document.getElementById('sv-overlay-toggle')?.checked;
  strip.innerHTML = _ssImages.map((url, i) =>
    '<div class="sv-thumb" draggable="true" ondragstart="svDragStart(event,' + i + ')" ondragover="svDragOver(event)" ondrop="svDrop(event,' + i + ')">'
    + '<img src="' + url + '" alt="Slide ' + (i + 1) + '" draggable="false">'
    + '<div class="sv-thumb-n">' + (i + 1) + '</div>'
    + '<div class="sv-thumb-btns">'
    +   '<button type="button" title="Earlier" onclick="ssMove(' + i + ',-1)"' + (i === 0 ? ' disabled' : '') + '>&#9650;</button>'
    +   '<button type="button" title="Later" onclick="ssMove(' + i + ',1)"' + (i === _ssImages.length - 1 ? ' disabled' : '') + '>&#9660;</button>'
    +   '<button type="button" title="Remove" onclick="ssRemove(' + i + ')">&#10005;</button>'
    + '</div>'
    + (caps ? '<input type="text" class="sv-cap" maxlength="120" placeholder="(empty — no text will be added)" value="' + _ssEsc(_ssCaptions[i] || '') + '" oninput="svSetCaption(' + i + ', this.value)">' : '')
    + '</div>'
  ).join('');
}

// Drag-and-drop reorder. Narration lines and captions travel with their
// slide. Arrow buttons stay as the keyboard/touch fallback.
let _svDragFrom = null;
function svDragStart(e, i) { _svDragFrom = i; e.dataTransfer.effectAllowed = 'move'; }
function svDragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; }
function svDrop(e, to) {
  e.preventDefault();
  const from = _svDragFrom;
  _svDragFrom = null;
  if (from === null || from === to) return;
  while (_ssCaptions.length < _ssImages.length) _ssCaptions.push('');
  const mv = (arr) => { const x = arr.splice(from, 1)[0]; arr.splice(to, 0, x); };
  mv(_ssImages);
  if (_ssNarrationLines.length) mv(_ssNarrationLines);
  mv(_ssCaptions);
  ssRenderStrip();
}
function svSetCaption(i, v) { _ssCaptions[i] = v; }

// Prefill empty captions from the storyboard so the author's own images
// pick up the generated slide text without retyping. Runs on toggle AND
// after image uploads — whichever order the author works in.
function svPrefillCaptions() {
  if (!document.getElementById('sv-overlay-toggle')?.checked) return;
  const sb = _ssReadStoryboard() || _ssStoryboard || [];
  for (let i = 0; i < _ssImages.length; i++) {
    if (_ssCaptions[i] && _ssCaptions[i].trim() !== '') continue;
    const s = sb[i];
    if (s) _ssCaptions[i] = (s.subtext && s.subtext.trim()) ? s.subtext : (s.headline || '');
  }
}

function svOverlayToggled() {
  svPrefillCaptions();
  ssRenderStrip();
}

function ssMove(i, dir) {
  const j = i + dir;
  if (j < 0 || j >= _ssImages.length) return;
  const tmp = _ssImages[i]; _ssImages[i] = _ssImages[j]; _ssImages[j] = tmp;
  // Keep per-slide narration glued to its slide when slides are reordered.
  if (_ssNarrationLines.length === _ssImages.length) {
    const tn = _ssNarrationLines[i]; _ssNarrationLines[i] = _ssNarrationLines[j]; _ssNarrationLines[j] = tn;
  }
  while (_ssCaptions.length < _ssImages.length) _ssCaptions.push('');
  const tc = _ssCaptions[i]; _ssCaptions[i] = _ssCaptions[j]; _ssCaptions[j] = tc;
  ssRenderStrip();
}

function ssRemove(i) {
  _ssImages.splice(i, 1);
  if (_ssNarrationLines.length > i) _ssNarrationLines.splice(i, 1);
  if (_ssCaptions.length > i) _ssCaptions.splice(i, 1);
  ssRenderStrip();
}

// ── Narration ─────────────────────────────────────────────────

function ssNarrationMode() {
  const mode  = document.querySelector('input[name="sv-narration-mode"]:checked')?.value || 'none';
  const tts   = document.getElementById('sv-narration-tts');
  const up    = document.getElementById('sv-narration-upload');
  const voice = document.getElementById('sv-voice-wrap');
  if (tts)   tts.style.display   = (mode === 'tts')    ? 'block' : 'none';
  if (up)    up.style.display    = (mode === 'upload') ? 'block' : 'none';
  if (voice) voice.style.display = (mode === 'tts' || mode === 'storyboard') ? 'block' : 'none';
}

async function ssUploadVoice(input) {
  const file = input.files && input.files[0];
  if (!file) return;
  const label = document.getElementById('sv-voice-file-label');
  try {
    const fd = new FormData();
    fd.append('audio', file);
    const headers = {};
    const token = localStorage.getItem('auth_token');
    if (token) headers['X-Auth-Token'] = token;
    const res  = await fetch('/api/slideshow_upload.php', {
      method: 'POST', headers: headers, credentials: 'same-origin', body: fd,
    });
    const data = await res.json();
    if (!data.success || !data.url) throw new Error(data.message || 'Upload failed');
    _ssVoiceUrl = data.url;
    if (label) label.textContent = file.name + ' — uploaded ✓';
    toast('Voiceover uploaded');
  } catch (e) {
    _ssVoiceUrl = '';
    if (label) label.textContent = '';
    toast(e.message || 'Voiceover upload failed', true);
  } finally {
    input.value = '';
  }
}

// ── Music ─────────────────────────────────────────────────────

function svMoodChanged() {
  const custom = document.getElementById('sv-mood')?.value === 'custom';
  const row = document.getElementById('sv-music-upload');
  if (row) row.style.display = custom ? 'block' : 'none';
}

async function svUploadMusic(input) {
  const file = input.files && input.files[0];
  if (!file) return;
  const label = document.getElementById('sv-music-file-label');
  try {
    const fd = new FormData();
    fd.append('audio', file);
    const headers = {};
    const token = localStorage.getItem('auth_token');
    if (token) headers['X-Auth-Token'] = token;
    const res  = await fetch('/api/slideshow_upload.php', {
      method: 'POST', headers: headers, credentials: 'same-origin', body: fd,
    });
    const data = await res.json();
    if (!data.success || !data.url) throw new Error(data.message || 'Upload failed');
    _ssMusicUrl = data.url;
    if (label) label.textContent = file.name + ' — uploaded ✓';
    toast('Music uploaded');
  } catch (e) {
    _ssMusicUrl = '';
    if (label) label.textContent = '';
    toast(e.message || 'Music upload failed', true);
  } finally {
    input.value = '';
  }
}

// ── Render ────────────────────────────────────────────────────

function ssUpdateStatus(label, pct) {
  const l = document.getElementById('sv-status-label');
  const b = document.getElementById('sv-status-bar');
  if (l) l.textContent = label;
  if (b) b.style.width = pct + '%';
}

function ssShowError(msg) {
  ssResetProgress();
  const card = document.getElementById('sv-error-card');
  const text = document.getElementById('sv-error-text');
  if (text) text.textContent = msg;
  if (card) card.style.display = 'block';
  const btn = document.getElementById('sv-generate-btn');
  if (btn) btn.disabled = false;
}

async function ssSubmit() {
  // In the demo the AI planner and slide-image generation are both switched
  // off, so _ssImages is always empty and Generate can never be reached — the
  // author would only ever see a "add at least 2 slides" toast. Show them the
  // finished article instead.
  if (typeof currentUser !== 'undefined' && currentUser &&
      (currentUser.is_demo == 1 || currentUser.is_demo === true) && _ssImages.length < 2) {
    showDemoModal({
      exampleVideo: 'assets/demo/examples/slideshow-video.mp4',
      examplePoster: 'assets/demo/examples/slideshow-poster.jpg',
      exampleCaption: 'A real slideshow video made with this tool for The Lighthouse Letters — press play',
      type: 'ai',
      eyebrow: 'AI demo preview',
      title: 'Slideshow video rendering',
      body: 'In your real account you plan the slides with AI or upload your own, then this assembles '
          + 'them into a finished video — crossfades, music, and optional narration.\n\n'
          + 'The demo cannot build slides, so here is one made earlier.',
      footNote: 'Sign up to make slideshow videos for your own book.',
    });
    return;
  }
  if (_ssImages.length < 2) { toast('Create or add at least 2 slides first', true); return; }

  const format     = document.getElementById('sv-format')?.value || '4x5';
  const perSlide   = parseFloat(document.getElementById('sv-per-slide')?.value || '6.5');
  const transition = document.getElementById('sv-transition')?.value || 'fade';
  const moodSel    = document.getElementById('sv-mood')?.value || 'silent';
  const musicUrl   = (moodSel === 'custom') ? _ssMusicUrl : '';
  const mood       = (moodSel === 'custom') ? 'silent' : moodSel;
  const endCard    = !!document.getElementById('sv-end-card')?.checked;
  const mode       = document.querySelector('input[name="sv-narration-mode"]:checked')?.value || 'none';
  const narration  = (mode === 'tts') ? (document.getElementById('sv-narration-text')?.value || '').trim() : '';
  const voice      = document.getElementById('sv-voice')?.value || 'Joanna';
  const voiceUrl   = (mode === 'upload') ? _ssVoiceUrl : '';
  let lines = [];
  if (mode === 'storyboard') {
    const sb = _ssReadStoryboard();
    lines = (sb && sb.length) ? sb.map(s => s.narration) : _ssNarrationLines;
  }
  if (mode === 'upload' && !voiceUrl) { toast('Upload your voiceover MP3 first (or switch narration off)', true); return; }
  if (mode === 'tts' && narration === '') { toast('Type a narration script first (or pick another narration option)', true); return; }
  if (moodSel === 'custom' && !musicUrl) { toast('Upload your music MP3 first (or pick a mood)', true); return; }
  if (mode === 'storyboard' && !lines.some(l => l && l.trim() !== '')) {
    toast('No storyboard narration available — generate a storyboard first, or pick another narration option', true);
    return;
  }

  ssResetProgress();
  const btn = document.getElementById('sv-generate-btn');
  if (btn) btn.disabled = true;
  document.getElementById('sv-status-card').style.display = 'block';
  ssUpdateStatus('Fitting slides to ' + format.replace('x', ':') + '…', 5);

  // Blurred-pad each slide to the output shape (same treatment as the
  // trailer's full-frame mode) so mixed or mismatched images fill the
  // frame cleanly. Falls back to the original on any fitting error.
  // Text overlays first (manual captions) so the fit step pads final art.
  // Slides with an empty caption pass through untouched.
  let working = _ssImages.slice();
  if (document.getElementById('sv-overlay-toggle')?.checked && _ssCaptions.some(c => c && c.trim() !== '')) {
    ssUpdateStatus('Adding text overlays…', 3);
    let cap = null;
    try {
      cap = await api('/slideshow_caption.php', {
        method: 'POST',
        body: JSON.stringify({ items: working.map((u, i) => ({ url: u, caption: (_ssCaptions[i] || '').trim() })) }),
      });
    } catch (e) { ssShowError('Text overlay failed: ' + (e?.message || e)); return; }
    if (!cap?.success || !cap?.urls) { ssShowError(cap?.message || 'Text overlay failed.'); return; }
    working = cap.urls;
  }

  const dims = _ssFormatDims[format] || [1080, 1350];
  const fitted = [];
  for (const url of working) {
    try {
      const fit = await api('/image_fit.php', {
        method: 'POST',
        body: JSON.stringify({ source: url, w: dims[0], h: dims[1], mode: 'blur' }),
      });
      fitted.push((fit && fit.success && fit.url) ? fit.url : url);
    } catch (e) { fitted.push(url); }
  }

  ssUpdateStatus('Submitting render…', 10);
  let data;
  try {
    data = await api('/slideshow_render.php', {
      method: 'POST',
      body: JSON.stringify({
        images:          fitted,
        per_slide:       perSlide,
        format:          format,
        transition:      transition,
        mood:            mood,
        music_url:       musicUrl,
        narration:       narration,
        narration_lines: lines,
        voice:           voice,
        narration_url:   voiceUrl,
        end_card:        endCard,
        book_id:         parseInt(document.getElementById('sv-sb-book')?.value || '0') || undefined,
      }),
    });
  } catch (e) {
    ssShowError('Submission error: ' + (e?.message || e));
    return;
  }

  if (data?.error_code === 'quota_exceeded') {
    ssShowError(data.message || 'Monthly video render limit reached.');
    ssLoadQuota();
    return;
  }
  if (!data?.success || !data?.render_id) {
    ssShowError(data?.message || 'Render submission failed.');
    return;
  }

  ssLoadQuota();   // successful submit consumes one from the shared pool
  _ssRenderId  = data.render_id;
  _ssStartedAt = Date.now();
  ssUpdateStatus('Queued — Shotstack is preparing assets…', 15);

  _ssElapsedHandle = setInterval(() => {
    const sec = Math.round((Date.now() - _ssStartedAt) / 1000);
    const el  = document.getElementById('sv-status-elapsed');
    if (el) el.textContent = 'Elapsed: ' + sec + 's' + (sec > 90 ? ' — a slideshow typically renders in 45–90s, hang tight' : '');
  }, 1000);

  _ssPollHandle = setTimeout(ssPoll, 4000);
}

// ════════════════════════════════════════════════════════════
//  Slideshow wizard navigation (v133)
//  The Slideshow Video page is a 5-step wizard, same architecture
//  as the trailer's: 1 Plan with AI, 2 Your slides, 3 Look & sound,
//  4 Narration, 5 Review & generate.
// ════════════════════════════════════════════════════════════
let _svStep = 1;
function svGoStep(n) {
  n = Math.max(1, Math.min(5, n));
  _svStep = n;
  document.querySelectorAll('#view-gv-slideshow .sv-step').forEach(el => {
    el.style.display = (parseInt(el.dataset.step) === n) ? 'block' : 'none';
  });
  for (let i = 1; i <= 5; i++) {
    const p = document.getElementById('sv-pill-' + i);
    if (!p) continue;
    p.classList.toggle('active', i === n);
    p.classList.toggle('done', i < n);
  }
  if (n === 5) svBuildReview();
  const w = document.getElementById('sv-wizard');
  if (w && w.scrollIntoView) w.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function svNext() {
  // The only hard gate: you can't style or narrate a slideshow with no slides.
  if (_svStep === 2 && _ssImages.length < 2) {
    alert('Add at least 2 slides first — upload your own images here, or go back to step 1 and let the AI create them.');
    return;
  }
  svGoStep(_svStep + 1);
}
function svBack() { svGoStep(_svStep - 1); }

// Build the step-5 recap from the current control values.
function svBuildReview() {
  const el = document.getElementById('sv-review-summary');
  if (!el) return;
  const g = (id) => document.getElementById(id);
  const fmtNames = { '4x5': 'Feed 4:5 (Facebook, Instagram, LinkedIn)', '9x16': 'Vertical 9:16 (Reels, TikTok)', '1x1': 'Square 1:1', '16x9': 'Wide 16:9 (website, email)' };
  const fmt      = fmtNames[g('sv-format')?.value] || '—';
  const per      = parseFloat(g('sv-per-slide')?.value || '6.5');
  const nSlides  = _ssImages.length;
  const endCard  = !!g('sv-end-card')?.checked;
  const runSecs  = Math.round(nSlides * per + (endCard ? 3 : 0));
  const moodSel  = g('sv-mood')?.value || 'silent';
  const moodTxt  = moodSel === 'silent' ? 'None' : (moodSel === 'custom' ? (_ssMusicUrl ? 'My own MP3 ✓' : 'My own MP3 — not uploaded yet!') : moodSel.charAt(0).toUpperCase() + moodSel.slice(1));
  const mode     = document.querySelector('input[name="sv-narration-mode"]:checked')?.value || 'none';
  const voice    = g('sv-voice')?.value || 'Joanna';
  const narrTxt  = { none: 'None — music only', storyboard: 'Storyboard — one line per slide (' + voice + ')', tts: 'AI voice reads my script (' + voice + ')', upload: (_ssVoiceUrl ? 'My own recording ✓' : 'My own recording — not uploaded yet!') }[mode] || '—';
  const capsOn   = !!g('sv-overlay-toggle')?.checked && _ssCaptions.some(c => c && c.trim() !== '');
  const rows = [
    ['Slides',        nSlides ? (nSlides + (nSlides * per ? ' × ' + per + 's each' : '')) : 'None yet — go back to step 2!'],
    ['Video length',  nSlides ? ('about ' + runSecs + ' seconds' + (mode === 'storyboard' ? ' (slides extend automatically if a narration line runs long)' : '')) : '—'],
    ['Shape',         fmt],
    ['Transition',    g('sv-transition')?.value === 'none' ? 'Hard cut' : 'Crossfade'],
    ['Music',         moodTxt],
    ['Narration',     narrTxt],
    ['Text overlays', capsOn ? 'On — captions stamped onto slides' : 'Off'],
    ['Closing card',  endCard ? '“Made with Elite Publishing”' : 'Off'],
  ];
  el.innerHTML = rows.map(r => '<div><strong>' + r[0] + ':</strong> ' + escapeHtml(String(r[1])) + '</div>').join('');
}

// Wire the rendered slideshow into the same manual-handoff modal the
// trailer uses (tvPostTrailer above). Video posting is always manual
// handoff — no API path supports uploading our video files.
async function svPostVideo() {
  const dl = document.getElementById('sv-download-btn');
  const videoUrl = dl ? dl.getAttribute('href') : '';
  if (!videoUrl || videoUrl === '#') {
    return toast('Generate a slideshow video first', true);
  }

  // Caption seed: the storyboard's hook headline is the natural opener.
  const sb = _ssReadStoryboard() || _ssStoryboard || [];
  const caption = (sb[0] && (sb[0].headline || '').trim()) || '';

  // Book link, if the storyboard's book has a destination URL on file.
  const bookId = parseInt(document.getElementById('sv-sb-book')?.value || '0');
  const book   = bookId ? (window._books || []).find(b => b.id == bookId) : null;
  const linkUrl = cleanAmazonUrl(book?.amazon_url || '');

  try {
    const data = await api('/connections.php?action=list&for=video&enabled_only=1');
    if (!data || !data.success) return toast('Couldn\'t load platforms', true);
    const platforms = data.platforms || [];
    if (!platforms.length) {
      toast('No video platforms set up yet. Open Connections and add Instagram, TikTok, Facebook, etc.', true);
      return;
    }
    const fmt = document.getElementById('sv-format')?.value || '4x5';
    const aspect = fmt === '16x9' ? 'landscape' : (fmt === '9x16' ? 'vertical' : 'square');
    openHandoffModal({
      caption:  caption,
      linkUrl:  linkUrl,
      videoUrl: videoUrl,
      platforms: platforms,
      source:   'slideshow',
      videoAspect: aspect,
    });
  } catch (e) {
    toast('Couldn\'t load platforms', true);
  }
}

async function ssPoll() {
  if (!_ssRenderId) return;
  let data;
  try {
    data = await api('/video_status.php?render_id=' + encodeURIComponent(_ssRenderId));
  } catch (e) {
    _ssPollHandle = setTimeout(ssPoll, 5000);   // transient — retry
    return;
  }
  if (!data?.success) { ssShowError(data?.message || 'Status check failed.'); return; }

  const stateMap = {
    queued:        { label: 'Queued',                     pct: 20 },
    preprocessing: { label: 'Preparing narration…',       pct: 30 },
    fetching:      { label: 'Fetching slides and music…', pct: 40 },
    rendering:     { label: 'Rendering frames…',          pct: 65 },
    saving:        { label: 'Encoding final video…',      pct: 85 },
    done:          { label: 'Done',                       pct: 100 },
    failed:        { label: 'Failed',                     pct: 100 },
  };
  const s = stateMap[data.status] || { label: data.status || 'Working…', pct: 50 };
  ssUpdateStatus(s.label + '…', s.pct);

  if (data.status === 'done' && data.url) {
    if (_ssPollHandle)    { clearTimeout(_ssPollHandle); _ssPollHandle = null; }
    if (_ssElapsedHandle) { clearInterval(_ssElapsedHandle); _ssElapsedHandle = null; }
    document.getElementById('sv-status-card').style.display = 'none';
    document.getElementById('sv-video').src = data.url;
    document.getElementById('sv-download-btn').href = data.url;
    document.getElementById('sv-output-card').style.display = 'block';
    const btn = document.getElementById('sv-generate-btn');
    if (btn) btn.disabled = false;
    return;
  }
  if (data.status === 'failed') {
    ssShowError('Render failed: ' + (data.error || 'no detail returned'));
    return;
  }
  _ssPollHandle = setTimeout(ssPoll, 5000);
}

// ── ADS AGENT (v230) ──────────────────────────────────────────
// Bob-facing control surface for the Mac-side posting agent.
// See docs/ADS_AGENT_PLAN.md. Admin only — api/ads.php enforces that
// server-side too, because a UI-only gate is walked past by driving the
// API directly.
let _ads = { assets: [], platforms: [], recent: [], settings: {},
             assetId: 0, variants: [] };

function initAdsView() {
  const panel = document.getElementById('ads-admin');
  if (!panel) return;
  const isAdmin = currentUser && (currentUser.is_admin == 1 || currentUser.is_admin === true);
  panel.style.display = isAdmin ? 'block' : 'none';
  if (isAdmin) adsLoadOverview();
}

async function adsLoadOverview() {
  const data = await api('/ads.php?action=overview');
  if (!data.success) { toast(data.message || 'Could not load ads', true); return; }
  _ads.assets    = data.assets    || [];
  _ads.platforms = data.platforms || [];
  _ads.recent    = data.recent    || [];
  _ads.settings  = data.settings  || {};
  adsRenderAgent();
  adsRenderPlatforms();
  adsRenderAssets();
  adsRenderRecent();
  adsLoadQueue();
}

function adsRenderAgent() {
  const on = _ads.settings.agent_enabled === '1';
  const box = document.getElementById('ads-agent-enabled');
  if (box) box.checked = on;
  const note = document.getElementById('ads-agent-note');
  if (note) {
    note.textContent = on ? 'Live — due posts will go out.' : 'Off — nothing will post.';
    note.style.color = on ? 'var(--accent)' : 'var(--ink-soft)';
  }
}

async function adsToggleAgent(on) {
  const data = await api('/ads.php?action=set_setting', {
    method: 'POST',
    body: JSON.stringify({ k: 'agent_enabled', v: on ? '1' : '0' }),
  });
  toast(data.message || 'Saved', !data.success);
  _ads.settings.agent_enabled = on ? '1' : '0';
  adsRenderAgent();
}

function adsRenderPlatforms() {
  const el = document.getElementById('ads-platform-rows');
  if (!el) return;
  if (!_ads.platforms.length) { el.textContent = 'No platforms configured — run migration 062.'; return; }

  el.innerHTML = _ads.platforms.map(p => {
    const blocked = !!p.blocked_at;
    const status = blocked
      ? '<span style="color:var(--gold);font-weight:600">Blocked — ' + escapeHtml(p.blocked_reason || 'reported by driver') + '</span>'
      : (p.enabled == 1 ? '<span style="color:var(--accent);font-weight:600">On</span>'
                        : '<span style="color:var(--ink-soft)">Off</span>');
    return '<div class="row" style="align-items:flex-start;gap:10px;flex-wrap:wrap">'
         +   '<div class="row-left" style="min-width:190px">'
         +     '<span class="pdot" style="background:' + escapeHtml(p.brand_color || '#888') + '"></span>'
         +     escapeHtml(p.name)
         +   '</div>'
         +   '<div style="flex:1;min-width:240px;font-size:14px;color:var(--ink-soft)">'
         +     status
         +     ' &middot; ' + p.posted_24h + '/' + p.daily_cap + ' in 24h'
    +     ((p.interval_days > 1) ? ' &middot; every ' + p.interval_days + ' days' : '')
         +     ' &middot; ' + p.min_gap_minutes + ' min apart'
         +     ' &middot; ' + p.window_start_hr + ':00–' + p.window_end_hr + ':00'
         +   '</div>'
         +   '<div style="display:flex;gap:8px">'
         +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="adsSetPlatform(' + p.id + ',' + (p.enabled == 1 ? 0 : 1) + ')">'
         +       (p.enabled == 1 ? 'Turn off' : 'Turn on') + '</button>'
         +     (blocked ? '<button class="app-btn app-btn-outline app-btn-sm" onclick="adsClearBlock(' + p.id + ')">Clear block</button>' : '')
         +   '</div>'
         + '</div>';
  }).join('');
}

async function adsSetPlatform(platformId, enabled) {
  const data = await api('/ads.php?action=set_platform', {
    method: 'POST', body: JSON.stringify({ destination_id: platformId, enabled: enabled }),
  });
  toast(data.message || 'Saved', !data.success);
  adsLoadOverview();
}

// Clearing a block is deliberately explicit — Bob decides the account is
// healthy again. Nothing clears it on a timer.
async function adsClearBlock(platformId) {
  if (!confirm('Clear the block on this platform? Only do this once you have opened the account yourself and confirmed it is healthy.')) return;
  const data = await api('/ads.php?action=set_platform', {
    method: 'POST', body: JSON.stringify({ destination_id: platformId, clear_block: 1, enabled: 1 }),
  });
  toast(data.message || 'Cleared', !data.success);
  adsLoadOverview();
}

function adsRenderAssets() {
  const el = document.getElementById('ads-asset-rows');
  if (!el) return;
  if (!_ads.assets.length) { el.innerHTML = '<p style="font-size:14px;color:var(--ink-soft);margin:0">No ads yet. Create one, upload a video, generate captions.</p>'; return; }

  el.innerHTML = _ads.assets.map(a =>
      '<div class="row" style="gap:10px;flex-wrap:wrap">'
    +   '<div class="row-left" style="min-width:200px">' + escapeHtml(a.title) + '</div>'
    +   '<div style="flex:1;min-width:240px;font-size:14px;color:var(--ink-soft)">'
    +     escapeHtml(a.status)
    +     ' &middot; ' + a.approved_count + '/' + a.variant_count + ' captions approved'
    +     ' &middot; ' + a.pending_count + ' queued'
    +     ' &middot; ' + a.posted_count + ' posted'
    +     (a.master_path ? '' : ' &middot; <span style="color:var(--gold)">no video yet</span>')
    +   '</div>'
    +   '<div style="display:flex;gap:8px">'
    +     '<button class="app-btn app-btn-outline app-btn-sm" onclick="adsOpenAsset(' + a.id + ')">Open</button>'
    +     (a.status === 'active'
            ? '<button class="app-btn app-btn-outline app-btn-sm" onclick="adsSetAssetStatus(' + a.id + ',\'paused\')">Pause</button>'
            : '<button class="app-btn app-btn-green app-btn-sm" onclick="adsSetAssetStatus(' + a.id + ',\'active\')">Activate</button>')
    +   '</div>'
    + '</div>'
  ).join('');
}

// ⚠ An asset is created as 'draft' and the server's claim query only ever
// hands out work for an ACTIVE one. Without this control an ad could be fully
// built, approved and queued, and still never post — with nothing in the UI
// explaining why. Pausing is also the per-ad stop, short of the global switch.
async function adsSetAssetStatus(id, status) {
  const data = await api('/ads.php?action=update_asset', {
    method: 'POST', body: JSON.stringify({ id: id, status: status }),
  });
  toast(data.success ? (status === 'active' ? 'Ad is live' : 'Ad paused') : data.message, !data.success);
  adsLoadOverview();
}

async function adsNewAsset() {
  const title = prompt('What is this ad for? (e.g. "eBook Maker launch 30s")');
  if (!title) return;
  const data = await api('/ads.php?action=create_asset', {
    method: 'POST', body: JSON.stringify({ title: title, dest_key: 'hero' }),
  });
  if (!data.success) { toast(data.message, true); return; }
  await adsLoadOverview();
  adsOpenAsset(data.asset_id);
}

async function adsOpenAsset(id) {
  _ads.assetId = id;
  const asset = _ads.assets.find(a => a.id == id);
  const panel = document.getElementById('ads-detail');
  if (panel) panel.style.display = 'block';
  const title = document.getElementById('ads-detail-title');
  if (title && asset) title.textContent = asset.title;
  const tags = document.getElementById('ads-hashtags');
  if (tags) tags.value = (asset && asset.hashtags) || '';
  // The angle is the brief for BOTH captions and hashtags, so it is stored on
  // the ad rather than retyped each time a batch is generated.
  const ang = document.getElementById('ads-direction');
  if (ang) ang.value = (asset && asset.angle) || '';
  // Report each slot separately so a missing landscape cut is visible rather
  // than hidden behind "a video is uploaded".
  const masters = (asset && asset.masters) || [];
  ['vertical', 'landscape'].forEach(a => {
    const el = document.getElementById('ads-note-' + a);
    if (!el) return;
    const m = masters.find(x => x.aspect === a);
    el.textContent = m ? ('uploaded, ' + Math.round((m.bytes || 0) / 1048576) + ' MB') : 'none yet';
    el.style.color = m ? 'var(--accent)' : 'var(--gold)';
  });

  adsRenderSchedulePlatforms();
  await adsLoadVariants();
  if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function adsUploadMaster(aspect) {
  aspect = aspect || 'vertical';
  const input = document.getElementById('ads-master-' + aspect);
  if (!input || !input.files || !input.files[0]) { toast('Pick a file first', true); return; }
  if (!_ads.assetId) { toast('Open an ad first', true); return; }

  const note = document.getElementById('ads-note-' + aspect);
  if (note) note.textContent = 'Uploading…';

  // Multipart, so this one bypasses api() — that helper forces a JSON
  // content-type, which would corrupt the boundary.
  const fd = new FormData();
  fd.append('id', _ads.assetId);
  fd.append('aspect', aspect);
  fd.append('video', input.files[0]);
  try {
    const res  = await fetch(API + '/ads.php?action=upload_master', {
      method: 'POST', headers: { 'X-Auth-Token': authToken }, body: fd,
    });
    const data = await res.json();
    toast(data.message || 'Uploaded', !data.success);
    if (note) note.textContent = data.success ? 'Video uploaded' : (data.message || 'Upload failed');
    if (data.success) adsLoadOverview();
  } catch (e) {
    toast('Upload failed — check your connection', true);
    if (note) note.textContent = 'Upload failed';
  }
}

async function adsLoadVariants() {
  const data = await api('/ads.php?action=variants&asset_id=' + _ads.assetId);
  _ads.variants = data.success ? (data.variants || []) : [];
  adsRenderVariants();
}

function adsRenderVariants() {
  const el = document.getElementById('ads-variant-rows');
  if (!el) return;
  if (!_ads.variants.length) {
    el.innerHTML = '<p style="font-size:14px;color:var(--ink-soft);margin:0">No captions yet — generate a batch.</p>';
    return;
  }
  el.innerHTML = _ads.variants.map(v =>
      '<div style="border-bottom:1px solid var(--rule);padding:12px 0;display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap">'
    +   '<label class="ads-check" style="align-items:flex-start;padding-top:8px">'
    +     '<input type="checkbox" data-ads-approve="' + v.id + '"' + (v.approved == 1 ? ' checked' : '') + '>'
    +   '</label>'
    +   '<div style="flex:1;min-width:260px">'
    +     '<textarea data-ads-caption="' + v.id + '" rows="2" style="width:100%;font-size:15px;line-height:1.5">'
    +       escapeHtml(v.caption) + '</textarea>'
    +     '<input type="text" data-ads-card="' + v.id + '" placeholder="Opening card line (max 6 words)" '
    +       'value="' + escapeHtml(v.card_text || '') + '" style="width:100%;font-size:14px;margin-top:6px">'
    +   '</div>'
    +   '<div style="min-width:120px;font-size:13.5px;color:var(--ink-soft);padding-top:6px">'
    +     (v.use_count > 0 ? 'used ' + v.use_count + '&times;' : 'unused')
    +     '<br>' + (v.clicks || 0) + ' click' + ((v.clicks || 0) === 1 ? '' : 's')
    +   '</div>'
    +   '<button class="app-btn app-btn-outline app-btn-sm" onclick="adsDeleteVariant(' + v.id + ')">Delete</button>'
    + '</div>'
  ).join('');
}

async function adsGenerate() {
  if (!_ads.assetId) { toast('Open an ad first', true); return; }
  const btn = document.getElementById('ads-gen-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Generating…'; }
  const data = await api('/ads.php?action=generate', {
    method: 'POST',
    body: JSON.stringify({
      asset_id:  _ads.assetId,
      count:     parseInt(document.getElementById('ads-gen-count').value || '15'),
      direction: document.getElementById('ads-direction').value.trim(),
    }),
  });
  if (btn) { btn.disabled = false; btn.textContent = 'Generate captions'; }
  toast(data.message || 'Done', !data.success);
  if (data.success) { await adsLoadVariants(); adsLoadOverview(); }
}

async function adsSaveVariants() {
  const rows = _ads.variants.map(v => ({
    id:        v.id,
    caption:   (document.querySelector('[data-ads-caption="' + v.id + '"]') || {}).value || v.caption,
    card_text: (document.querySelector('[data-ads-card="'    + v.id + '"]') || {}).value || '',
    approved:  !!(document.querySelector('[data-ads-approve="' + v.id + '"]') || {}).checked,
  }));
  const data = await api('/ads.php?action=save_variants', {
    method: 'POST', body: JSON.stringify({ variants: rows }),
  });
  // Hashtags live on the asset, not the variants, but they are edited in the
  // same panel — saving them separately would be a second button nobody presses.
  const tagsEl = document.getElementById('ads-hashtags');
  if (tagsEl && _ads.assetId) {
    const angEl = document.getElementById('ads-direction');
    await api('/ads.php?action=update_asset', {
      method: 'POST',
      body: JSON.stringify({
        id: _ads.assetId,
        hashtags: tagsEl.value.trim(),
        angle: angEl ? angEl.value.trim() : '',
      }),
    });
  }
  toast(data.message || 'Saved', !data.success);
  await adsLoadVariants();
  adsLoadOverview();
}

async function adsAddManual() {
  const caption = prompt('Your caption:');
  if (!caption) return;
  const card = prompt('Opening card line (optional, a few words):') || '';
  const data = await api('/ads.php?action=add_variant', {
    method: 'POST', body: JSON.stringify({ asset_id: _ads.assetId, caption: caption, card_text: card }),
  });
  toast(data.message || 'Added', !data.success);
  await adsLoadVariants();
  adsLoadOverview();
}

async function adsDeleteVariant(id) {
  const data = await api('/ads.php?action=delete_variant', {
    method: 'POST', body: JSON.stringify({ id: id }),
  });
  if (!data.success) { toast(data.message, true); return; }
  await adsLoadVariants();
  adsLoadOverview();
}

function adsRenderSchedulePlatforms() {
  const el = document.getElementById('ads-schedule-platforms');
  if (!el) return;
  // Only destinations that are switched on AND unblocked can take a post.
  // A disabled one has no login or no driver yet, and offering it would let
  // Bob schedule into a hole.
  const usable = _ads.platforms.filter(p => p.enabled == 1 && !p.blocked_at);
  if (!usable.length) {
    el.innerHTML = '<p style="font-size:14px;color:var(--gold);margin:0">No destinations are switched on yet.</p>';
    return;
  }
  el.innerHTML = usable.map(p =>
      '<label class="ads-check" style="margin-right:16px;margin-bottom:8px">'
    +   '<input type="checkbox" data-ads-plat="' + p.id + '"> '
    +   '<span class="pdot" style="background:' + escapeHtml(p.brand_color || '#888') + '"></span>'
    +   escapeHtml(p.name)
    + '</label>'
  ).join('');
}

async function adsSchedule() {
  const ids = Array.from(document.querySelectorAll('[data-ads-plat]'))
                   .filter(b => b.checked).map(b => parseInt(b.dataset.adsPlat));
  if (!ids.length) { toast('Pick at least one platform', true); return; }

  const note = document.getElementById('ads-schedule-note');
  if (note) note.textContent = 'Scheduling…';
  const data = await api('/ads.php?action=schedule', {
    method: 'POST',
    body: JSON.stringify({
      asset_id:     _ads.assetId,
      platform_ids: ids,
      days:         parseInt(document.getElementById('ads-schedule-days').value || '7'),
    }),
  });
  if (note) note.textContent = data.message || '';
  toast(data.message || 'Scheduled', !data.success);
  if (data.success) adsLoadOverview();
}

function adsRenderRecent() {
  const el = document.getElementById('ads-recent-rows');
  if (!el) return;
  if (!_ads.recent.length) {
    el.innerHTML = '<p style="font-size:14px;color:var(--ink-soft);margin:0">Nothing posted yet.</p>';
    return;
  }
  el.innerHTML = _ads.recent.map(r =>
      '<div class="row" style="gap:10px;flex-wrap:wrap;align-items:flex-start">'
    +   '<div class="row-left" style="min-width:170px">'
    +     '<span class="pdot" style="background:' + escapeHtml(r.brand_color || '#888') + '"></span>'
    +     escapeHtml(r.platform_name)
    +   '</div>'
    +   '<div style="flex:1;min-width:260px;font-size:14px">'
    +     escapeHtml(r.caption || '')
    +     '<div style="color:var(--ink-soft);font-size:13.5px;margin-top:4px">'
    +       escapeHtml(r.asset_title) + ' &middot; ' + escapeHtml(r.posted_at)
    +       ' &middot; ' + (r.clicks || 0) + ' click' + ((r.clicks || 0) === 1 ? '' : 's')
    +     '</div>'
    +   '</div>'
    +   (r.permalink
        ? '<a class="app-btn app-btn-outline app-btn-sm" href="' + escapeHtml(r.permalink) + '" target="_blank" rel="noopener">View ↗</a>'
        : '')
    + '</div>'
  ).join('');
}

// ── ADS QUEUE VIEW (v231) ─────────────────────────────────────
// "What is going out and when" — the question you want answered before
// flipping the agent on. Upcoming and history are separate lists because a
// single scheduled_at ordering buries the next post under weeks of records.
function adsRenderQueue(upcoming, history) {
  const up = document.getElementById('ads-queue-rows');
  if (up) {
    up.innerHTML = (upcoming && upcoming.length)
      ? upcoming.map(q => {
          const when = adsWhenLabel(q.scheduled_at);
          const claimed = q.status === 'claimed';
          return '<div class="row" style="gap:10px;flex-wrap:wrap;align-items:flex-start">'
            +   '<div class="row-left" style="min-width:170px">'
            +     '<span class="pdot" style="background:' + escapeHtml(q.brand_color || '#888') + '"></span>'
            +     escapeHtml(q.platform_name)
            +   '</div>'
            +   '<div style="flex:1;min-width:240px;font-size:14px">'
            +     '<strong>' + escapeHtml(when) + '</strong>'
            +     (claimed ? ' <span style="color:var(--gold)">· being posted now</span>' : '')
            +     '<div style="color:var(--ink-soft);font-size:13.5px;margin-top:4px">'
            +       escapeHtml(q.asset_title)
            +       (q.caption ? ' · ' + escapeHtml(q.caption) : ' · caption picked at post time')
            +     '</div>'
            +   '</div>'
            +   (claimed ? ''
                 : '<button class="app-btn app-btn-outline app-btn-sm" onclick="adsCancelQueued(' + q.id + ')">Cancel</button>')
            + '</div>';
        }).join('')
      : '<p style="font-size:14px;color:var(--ink-soft);margin:0">Nothing queued.</p>';
  }

  // Failures and blocks only. A posted row already appears under Recent posts,
  // and showing it twice makes the failure list harder to scan.
  const el = document.getElementById('ads-queue-history');
  if (!el) return;
  const bad = (history || []).filter(h => h.status !== 'posted');
  el.innerHTML = bad.length
    ? bad.map(h =>
        '<div class="row" style="gap:10px;flex-wrap:wrap;align-items:flex-start">'
      +   '<div class="row-left" style="min-width:170px">'
      +     '<span class="pdot" style="background:' + escapeHtml(h.brand_color || '#888') + '"></span>'
      +     escapeHtml(h.platform_name)
      +   '</div>'
      +   '<div style="flex:1;min-width:240px;font-size:14px">'
      +     '<span style="color:' + (h.status === 'blocked' ? 'var(--gold)' : 'var(--ink)') + ';font-weight:600">'
      +       escapeHtml(h.status) + '</span> · ' + escapeHtml(h.scheduled_at)
      +     (h.last_error
           ? '<div style="color:var(--ink-soft);font-size:13.5px;margin-top:4px">' + escapeHtml(h.last_error) + '</div>'
           : '')
      +   '</div>'
      + '</div>'
      ).join('')
    : '<p style="font-size:14px;color:var(--ink-soft);margin:0">Nothing has failed.</p>';
}

// Friendly relative day plus the exact time — the exact minute matters here,
// because the whole scheduling design is that no two posts share a clock slot.
function adsWhenLabel(ts) {
  if (!ts) return '';
  const d = new Date(ts.replace(' ', 'T'));
  if (isNaN(d)) return ts;
  const now = new Date();
  const day = new Date(d.getFullYear(), d.getMonth(), d.getDate());
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const diff = Math.round((day - today) / 86400000);
  const time = d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  if (diff === 0) return 'Today ' + time;
  if (diff === 1) return 'Tomorrow ' + time;
  if (diff > 1 && diff < 7) return d.toLocaleDateString([], { weekday: 'long' }) + ' ' + time;
  return d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + time;
}

async function adsLoadQueue() {
  const data = await api('/ads.php?action=queue');
  if (!data.success) return;
  adsRenderQueue(data.upcoming || [], data.history || []);
}

async function adsCancelQueued(id) {
  const data = await api('/ads.php?action=cancel_queue', {
    method: 'POST', body: JSON.stringify({ id: id }),
  });
  toast(data.message || 'Cancelled', !data.success);
  adsLoadQueue();
  adsLoadOverview();
}

async function adsClearPending() {
  if (!confirm('Cancel every pending post across all ads? Anything already being posted is unaffected.')) return;
  const data = await api('/ads.php?action=clear_pending', {
    method: 'POST', body: JSON.stringify({}),
  });
  toast(data.message || 'Cleared', !data.success);
  adsLoadQueue();
  adsLoadOverview();
}

// Generate hashtags from the ad's stated angle. Deliberately driven by the
// same brief as the captions — tags chosen from the product name alone
// describe the category ("#ebooks") rather than the audience and the problem,
// which is the same drift that made the first caption batch useless.
async function adsGenerateHashtags() {
  if (!_ads.assetId) { toast('Open an ad first', true); return; }
  const angle = (document.getElementById('ads-direction') || {}).value || '';
  if (!angle.trim()) { toast('Write the angle first — tags come from the subject', true); return; }

  const btn = document.getElementById('ads-tags-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Generating…'; }
  const data = await api('/ads.php?action=generate_hashtags', {
    method: 'POST',
    body: JSON.stringify({ asset_id: _ads.assetId, angle: angle.trim(), platform: 'tiktok' }),
  });
  if (btn) { btn.disabled = false; btn.textContent = 'Generate from the angle'; }

  if (data.success && data.hashtags) {
    const el = document.getElementById('ads-hashtags');
    if (el) el.value = data.hashtags;
  }
  toast(data.message || 'Done', !data.success);
}
