/**
 * Manuscript submission form — validation, upload, submit and thank-you state.
 *
 * Set ENQUIRY_ENDPOINT to a handler URL to POST submissions. With a file
 * attached the request is multipart/form-data (field name `manuscript`);
 * without one it is JSON. Left empty, the form validates and confirms
 * locally with no network call — nothing is uploaded.
 */
(function () {
  'use strict';

  /* The live handler. This was an empty string, which made the form confirm
     locally without sending anything — a visitor saw "thank you" and no
     enquiry existed. It is read from the form's own action attribute so the
     URL is written once, in the markup, and still works if the site is ever
     served from a sub-directory. */
  var ENQUIRY_ENDPOINT =
    (document.getElementById('enquiry-form') || {}).action || '/forms/lp-handler.php';

  var SUBMIT_LABEL  = 'Submit Manuscript';
  var CONTACT_EMAIL = 'info@elitepublishing.co';
  var MAX_BYTES    = 25 * 1024 * 1024;                            // 25 MB
  var ALLOWED_EXT  = ['doc', 'docx', 'pdf', 'rtf', 'txt', 'epub'];

  function $(id) { return document.getElementById(id); }

  function showError(msg) {
    var box = $('enquiry-error');
    if (!box) return;
    box.textContent = msg;
    box.style.display = 'block';
  }

  function clearError() {
    var box = $('enquiry-error');
    if (box) box.style.display = 'none';
  }

  // Deliberately permissive — this only catches obvious typos. Real
  // address validation happens when we reply to it.
  function looksLikeEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
  }

  // Digits only, so +1 (555) 000-1234 and 555.000.1234 both pass.
  function looksLikePhone(value) {
    return (value.replace(/\D/g, '')).length >= 7;
  }

  function extensionOf(filename) {
    var dot = filename.lastIndexOf('.');
    return dot === -1 ? '' : filename.slice(dot + 1).toLowerCase();
  }

  function formatSize(bytes) {
    return bytes >= 1024 * 1024
      ? (bytes / (1024 * 1024)).toFixed(1) + ' MB'
      : Math.max(1, Math.round(bytes / 1024)) + ' KB';
  }

  // Returns an error string, or '' when the file is acceptable.
  function fileProblem(file) {
    if (ALLOWED_EXT.indexOf(extensionOf(file.name)) === -1) {
      return 'That file type isn’t supported. Please upload a DOC, DOCX, PDF, RTF, TXT or EPUB file.';
    }
    if (file.size > MAX_BYTES) {
      return 'That file is ' + formatSize(file.size) + ' — the maximum is 25 MB.';
    }
    if (file.size === 0) {
      return 'That file looks empty. Please choose another.';
    }
    return '';
  }

  function selectedFile() {
    var input = $('enq-file');
    return (input && input.files && input.files[0]) || null;
  }

  // Mirror the native input's state into the visible control.
  function renderFileName() {
    var label = $('enq-file-name');
    if (!label) return;
    var file = selectedFile();
    if (file) {
      label.textContent = file.name + ' (' + formatSize(file.size) + ')';
      label.classList.add('has-file');
    } else {
      label.textContent = 'No file chosen';
      label.classList.remove('has-file');
    }
  }

  function onFileChange() {
    clearError();
    var file = selectedFile();
    if (file) {
      var problem = fileProblem(file);
      if (problem) {
        $('enq-file').value = '';                 // reject it before it reaches submit
        renderFileName();
        showError(problem);
        return;
      }
    }
    renderFileName();
  }

  // Scroll the panel into view and flash it, so a click on a CTA
  // several screens down visibly lands somewhere.
  function scrollToEnquiry() {
    var panel = $('enquiry-panel');
    if (!panel) return;
    panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
    panel.classList.remove('flash');
    void panel.offsetWidth;            // force reflow to restart the animation
    panel.classList.add('flash');
    // Put the cursor in the first empty field once the scroll has settled.
    setTimeout(function () {
      var name = $('enq-name');
      if (name && !name.value) name.focus({ preventScroll: true });
    }, 450);
  }

  // Pricing-card buttons: record the tier on the hidden field, then send
  // them up to the form.
  function enquireAbout(plan) {
    var field = $('enq-plan');
    if (field && plan) field.value = plan;
    scrollToEnquiry();
  }

  function submitEnquiry(e) {
    if (e) e.preventDefault();
    clearError();

    var name    = ($('enq-name').value    || '').trim();
    var email   = ($('enq-email').value   || '').trim();
    var phone   = ($('enq-phone').value   || '').trim();
    var message = ($('enq-message').value || '').trim();
    var file    = selectedFile();

    if (!name)                  { showError('Please enter your full name.');              $('enq-name').focus();  return; }
    if (!email)                 { showError('Please enter your email address.');          $('enq-email').focus(); return; }
    if (!looksLikeEmail(email)) { showError('That email address doesn’t look right.'); $('enq-email').focus(); return; }
    if (!phone)                 { showError('Please enter your phone number.');           $('enq-phone').focus(); return; }
    if (!looksLikePhone(phone)) { showError('That phone number doesn’t look right.'); $('enq-phone').focus(); return; }

    /* The manuscript is optional. This used to refuse to submit without one,
       which turned a lead who wanted to ask a question first into no lead at
       all. A file that IS chosen still has to be a sane type and size. */
    if (file) {
      var problem = fileProblem(file);
      if (problem) { showError(problem); $('enq-file-btn').focus(); return; }
    }

    var planField = $('enq-plan');
    var payload = {
      name:    name,
      email:   email,
      phone:   phone,
      plan:    (planField && planField.value) || '',
      message: message
    };

    var btn = $('btn-enquiry');
    btn.disabled = true;                            // also stops a double send
    btn.textContent = 'Sending…';

    /* Always multipart, whether or not a file is attached: one code path is
       one thing to get wrong, and the handler reads both the same way. */
    var body = new FormData($('enquiry-form'));
    body.set('_ajax', '1');
    if (!file) {
      // An empty file input still serialises as a zero-byte part named
      // "manuscript"; removing it lets the handler see a clean "no file".
      body.delete('manuscript');
    }

    fetch(ENQUIRY_ENDPOINT, {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      headers: { Accept: 'application/json' }
    })
      .then(function (res) {
        return res.json()
          .catch(function () {
            throw new Error('Sorry — we couldn’t send that. Please try again, or email ' + CONTACT_EMAIL + '.');
          })
          .then(function (data) {
            if (!res.ok || !data || data.ok !== true) {
              throw new Error((data && data.message) || 'Sorry — we couldn’t send that. Please try again.');
            }
            return data;
          });
      })
      .then(function () {
        showSuccess(payload);
      })
      .catch(function (err) {
        btn.disabled = false;
        btn.textContent = SUBMIT_LABEL;
        showError(err.message || 'Sorry — we couldn’t send that. Please try again.');
      });
  }

  function showSuccess(payload) {
    var form = $('enquiry-form');
    var ok   = $('enquiry-success');
    var who  = $('enquiry-success-name');
    // First name only — "Thanks, Jane" reads better than the full name.
    if (who && payload.name) who.textContent = ', ' + payload.name.split(/\s+/)[0];
    if (form) form.style.display = 'none';
    if (ok)   ok.style.display   = 'block';
    var panel = $('enquiry-panel');
    if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  // Exposed for the inline onclick handlers in the nav, hero and pricing cards.
  window.enquireAbout    = enquireAbout;
  window.scrollToEnquiry = scrollToEnquiry;

  function init() {
    var form = $('enquiry-form');
    if (form) form.addEventListener('submit', submitEnquiry);

    var input   = $('enq-file');
    var button  = $('enq-file-btn');
    var control = $('enq-file-control');

    if (button && input) {
      button.addEventListener('click', function () { input.click(); });
    }
    if (input) {
      input.addEventListener('change', onFileChange);
      // The input is visually hidden, so mirror its focus onto the control
      // for browsers without :focus-within.
      if (control) {
        input.addEventListener('focus', function () { control.classList.add('focus'); });
        input.addEventListener('blur',  function () { control.classList.remove('focus'); });
      }
      renderFileName();      // a browser-restored selection shows on reload
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
