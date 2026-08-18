/**
 * Elite Publishing landing page.
 *
 * Progressive enhancement only — every section renders and every link works
 * with this file removed. Each behaviour is an isolated init function that
 * no-ops when its markup is absent.
 */
(function () {
  "use strict";

  /**
   * Where the contact form posts. Point this at your form handler (Formspree,
   * Netlify Forms, a serverless function, …). While it is empty the form
   * validates and reports success without sending anything.
   */
  /* The live handler, read from the form's own action attribute so the URL is
     written once, in the markup. This was an empty string, and the branch
     below that checked for it reported success after a 400ms timer without
     sending anything — every enquiry through this page was discarded while the
     visitor was thanked for it. */
  var FORM_ENDPOINT =
    (document.querySelector("[data-contact-form]") || {}).action || "/forms/lp-handler.php";

  var STICKY_OFFSET = 100;

  /* Manuscript upload limits — keep in sync with the hint text and the
     `accept` attribute in index.html. */
  var UPLOAD_EXTENSIONS = ["doc", "docx", "pdf", "rtf", "txt", "epub"];
  var UPLOAD_MAX_BYTES = 25 * 1024 * 1024;
  var NO_FILE_LABEL = "No file chosen";

  /* ---------------------------------------------------------------------
     Header — swap to the solid state once the page is scrolled.
     --------------------------------------------------------------------- */

  function initStickyHeader() {
    var header = document.querySelector("[data-sticky-header]");
    if (!header) return;

    var ticking = false;

    function update() {
      header.classList.toggle("is-stuck", window.scrollY >= STICKY_OFFSET);
      ticking = false;
    }

    window.addEventListener(
      "scroll",
      function () {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(update);
      },
      { passive: true }
    );

    update();
  }

  /* ---------------------------------------------------------------------
     Mobile navigation.
     --------------------------------------------------------------------- */

  function initMobileNav() {
    var toggle = document.querySelector("[data-nav-toggle]");
    var menu = document.querySelector("[data-nav-menu]");
    if (!toggle || !menu) return;

    function setOpen(open) {
      toggle.setAttribute("aria-expanded", String(open));
      menu.classList.toggle("is-open", open);
    }

    toggle.addEventListener("click", function () {
      setOpen(toggle.getAttribute("aria-expanded") !== "true");
    });

    // Close after following an in-page link, or on Escape.
    menu.addEventListener("click", function (event) {
      if (event.target.closest("a")) setOpen(false);
    });

    document.addEventListener("keydown", function (event) {
      if (event.key !== "Escape") return;
      if (toggle.getAttribute("aria-expanded") !== "true") return;
      setOpen(false);
      toggle.focus();
    });

    document.addEventListener("click", function (event) {
      if (toggle.getAttribute("aria-expanded") !== "true") return;
      if (menu.contains(event.target) || toggle.contains(event.target)) return;
      setOpen(false);
    });
  }

  /* ---------------------------------------------------------------------
     Scroll reveal — CSS handles the animation, this only flips the class.
     --------------------------------------------------------------------- */

  function initScrollReveal() {
    var targets = document.querySelectorAll(".reveal");
    if (!targets.length) return;

    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (reduced || !("IntersectionObserver" in window)) {
      targets.forEach(function (el) {
        el.classList.add("is-visible");
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: "0px 0px -10% 0px", threshold: 0.1 }
    );

    targets.forEach(function (el) {
      observer.observe(el);
    });
  }

  /* ---------------------------------------------------------------------
     Contact form — inline validation plus an async submit.
     --------------------------------------------------------------------- */

  function initContactForm() {
    var form = document.querySelector("[data-contact-form]");
    if (!form) return;

    var status = form.querySelector("[data-form-status]");
    var submit = form.querySelector("[data-form-submit]");
    var honeypot = form.querySelector("[data-honeypot]");
    var fileInput = form.querySelector("[data-file-input]");
    var fileName = form.querySelector("[data-file-name]");

    function setError(field, message) {
      var slot = form.querySelector('[data-error-for="' + field.name + '"]');
      field.setAttribute("aria-invalid", message ? "true" : "false");
      if (slot) slot.textContent = message;
    }

    function validateField(field) {
      var message = "";
      if (!field.validity.valid) {
        message = fieldMessage(field);
      } else if (field.type === "file") {
        message = uploadMessage(field);
      }
      setError(field, message);
      return !message;
    }

    function fieldMessage(field) {
      if (field.validity.valueMissing) return "This field is required.";
      if (field.validity.typeMismatch && field.type === "email") {
        return "Enter a valid email address.";
      }
      return "Please check this field.";
    }

    /* The manuscript is optional, but when one is attached it has to be a
       format we can open and small enough to send. */
    function uploadMessage(field) {
      var file = field.files && field.files[0];
      if (!file) return "";

      var parts = file.name.split(".");
      var extension = parts.length > 1 ? parts.pop().toLowerCase() : "";

      if (UPLOAD_EXTENSIONS.indexOf(extension) === -1) {
        return "Upload a DOC, DOCX, PDF, RTF, TXT or EPUB file.";
      }
      if (file.size > UPLOAD_MAX_BYTES) {
        return "That file is over 25 MB. Please send a smaller version.";
      }
      return "";
    }

    if (fileInput && fileName) {
      fileInput.addEventListener("change", function () {
        var file = fileInput.files && fileInput.files[0];
        fileName.textContent = file ? file.name : NO_FILE_LABEL;
      });
    }

    function setStatus(message, kind) {
      if (!status) return;
      status.textContent = message;
      status.className = "form__status" + (kind ? " form__status--" + kind : "");
    }

    form.querySelectorAll("input, textarea").forEach(function (field) {
      if (field === honeypot) return;
      field.addEventListener("blur", function () {
        validateField(field);
      });
      field.addEventListener("input", function () {
        if (field.getAttribute("aria-invalid") === "true") validateField(field);
      });
    });

    form.addEventListener("submit", function (event) {
      event.preventDefault();

      // Silently accept and discard bot submissions.
      if (honeypot && honeypot.value) return;

      var fields = Array.prototype.filter.call(
        form.querySelectorAll("input, textarea"),
        function (field) {
          return field !== honeypot;
        }
      );

      var firstInvalid = null;
      fields.forEach(function (field) {
        if (!validateField(field) && !firstInvalid) firstInvalid = field;
      });

      if (firstInvalid) {
        setStatus("Please correct the highlighted fields.", "error");
        firstInvalid.focus();
        return;
      }

      submitForm(new FormData(form));
    });

    function submitForm(data) {
      if (submit) submit.dataset.loading = "true";
      setStatus("Sending…", "");

      // Ask the handler for JSON. The field defaults to "0" in the markup, so
      // a post made with JavaScript disabled still gets the redirect flow.
      data.set("_ajax", "1");

      fetch(FORM_ENDPOINT, {
        method: "POST",
        body: data,
        credentials: "same-origin",
        headers: { Accept: "application/json" },
      })
        .then(function (response) {
          return response
            .json()
            .catch(function () {
              // A non-JSON body means something upstream of the handler
              // answered — a PHP fatal, or a proxy error page.
              throw new Error(
                "We could not send that just now. Please try again, or email info@elitepublishing.co."
              );
            })
            .then(function (body) {
              if (!response.ok || !body || body.ok !== true) {
                var error = new Error(
                  (body && body.message) || "We could not send that just now. Please try again."
                );
                error.fields = (body && body.errors) || {};
                throw error;
              }
              return body;
            });
        })
        .then(function (body) {
          /* Go to the thank-you page on success; the inline confirmation is only
             the fallback if the handler did not say where to go. */
          if (body && body.redirect) { window.location.assign(body.redirect); return; }
          onSuccess(body.message);
        })
        .catch(function (error) {
          if (submit) delete submit.dataset.loading;

          /* Field-level errors from the server, shown against the fields they
             belong to. The handler answers with its own field names because it
             serves four landing pages, so `name` and `message` are translated
             to this page's `full_name` and `notes`. */
          var NAMES = { name: "full_name", message: "notes" };
          var fields = (error && error.fields) || {};
          Object.keys(fields).forEach(function (key) {
            var slot = form.querySelector(
              '[data-error-for="' + (NAMES[key] || key) + '"]'
            );
            if (slot) slot.textContent = fields[key];
          });

          setStatus(
            (error && error.message) ||
              "Something went wrong. Please email info@elitepublishing.co.",
            "error"
          );
        });
    }

    function onSuccess(message) {
      if (submit) delete submit.dataset.loading;
      form.reset();
      if (fileName) fileName.textContent = NO_FILE_LABEL;
      setStatus(
        message || "Thank you — we've received your manuscript and will be in touch shortly.",
        "success"
      );
    }
  }

  /* ---------------------------------------------------------------------
     Footer copyright year.
     --------------------------------------------------------------------- */

  function initCurrentYear() {
    document.querySelectorAll("[data-current-year]").forEach(function (el) {
      el.textContent = String(new Date().getFullYear());
    });
  }

  /* ------------------------------------------------------------------- */

  function init() {
    initStickyHeader();
    initMobileNav();
    initScrollReveal();
    initContactForm();
    initCurrentYear();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
