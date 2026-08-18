/* ==========================================================================
   Elite Publishing — landing page behaviour

   Timings mirror the Figma prototype:
     buttons / FAQ .................. 300ms
     book carousel .................. 600ms EASE_OUT
     testimonials ................... 1000ms EASE_OUT
     logo strip ..................... 2000ms step (rendered as a marquee)
     modal .......................... 300ms dissolve
   ========================================================================== */

(function () {
    "use strict";

    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    function prefersReduced() {
        return reduceMotion.matches;
    }

    /* ----------------------------------------------------------------------
       Scroll reveals
       ---------------------------------------------------------------------- */
    function initReveals() {
        var singles = document.querySelectorAll("[data-animation]");
        var groups = document.querySelectorAll("[data-stagger]");

        // Index the children once so the stagger delay is pure CSS.
        groups.forEach(function (group) {
            Array.prototype.forEach.call(group.children, function (child, i) {
                child.style.setProperty("--i", i);
            });
        });

        if (prefersReduced() || !("IntersectionObserver" in window)) {
            singles.forEach(function (el) {
                el.classList.add("is-visible", "is-settled");
            });
            groups.forEach(function (el) {
                el.classList.add("is-visible");
            });
            return;
        }

        var pending = new Set();

        function reveal(el) {
            if (!pending.has(el)) return;
            pending.delete(el);
            observer.unobserve(el);
            el.classList.add("is-visible");
            // Drop the will-change hint once the transition has run.
            window.setTimeout(function () {
                el.classList.add("is-settled");
            }, 900);
        }

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) reveal(entry.target);
                });
            },
            { rootMargin: "0px 0px -12% 0px", threshold: 0.12 }
        );

        // Safety net. A reveal is decoration; content must never be stranded
        // invisible because an observer callback was coalesced during a fast
        // scroll, a deep link, or a print. Sweep anything already at or above
        // the fold and reveal it outright.
        function sweep() {
            var limit = window.innerHeight * 1.15;
            pending.forEach(function (el) {
                if (el.getBoundingClientRect().top < limit) reveal(el);
            });
        }

        function revealAll() {
            pending.forEach(function (el) {
                el.classList.add("is-visible", "is-settled");
            });
            pending.clear();
            observer.disconnect();
        }

        singles.forEach(function (el) {
            pending.add(el);
            observer.observe(el);
        });
        groups.forEach(function (el) {
            pending.add(el);
            observer.observe(el);
        });

        window.addEventListener("load", sweep);
        window.addEventListener("hashchange", sweep);
        window.addEventListener("beforeprint", revealAll);
        sweep();
    }

    /* ----------------------------------------------------------------------
       Header elevation once the page has scrolled
       ---------------------------------------------------------------------- */
    function initHeader() {
        var header = document.querySelector(".site-header");
        if (!header) return;

        var sentinel = document.createElement("div");
        sentinel.setAttribute("aria-hidden", "true");
        sentinel.style.cssText = "position:absolute;top:0;height:1px;width:1px;";
        document.body.prepend(sentinel);

        if (!("IntersectionObserver" in window)) return;

        new IntersectionObserver(
            function (entries) {
                header.classList.toggle("is-stuck", !entries[0].isIntersecting);
            },
            { threshold: 0 }
        ).observe(sentinel);
    }

    /* ----------------------------------------------------------------------
       Book carousel — looping, autoplay, arrows, swipe and keyboard
       ---------------------------------------------------------------------- */
    function initCarousel(root) {
        var track = root.querySelector("[data-carousel-track]");
        var prev = root.querySelector("[data-carousel-prev]");
        var next = root.querySelector("[data-carousel-next]");
        if (!track || track.children.length < 2) return;

        var delay = parseInt(root.getAttribute("data-autoplay"), 10) || 0;
        var timer = null;
        var busy = false;

        function step() {
            var first = track.children[0];
            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap) || 0;
            return first.getBoundingClientRect().width + gap;
        }

        function settle(cb) {
            track.addEventListener("transitionend", function handler(e) {
                if (e.target !== track || e.propertyName !== "transform") return;
                track.removeEventListener("transitionend", handler);
                cb();
            });
        }

        function goNext() {
            if (busy) return;
            busy = true;
            track.style.transform = "translateX(" + -step() + "px)";
            settle(function () {
                track.style.transition = "none";
                track.appendChild(track.children[0]);
                track.style.transform = "translateX(0)";
                void track.offsetWidth; // flush the reset before restoring
                track.style.transition = "";
                busy = false;
            });
        }

        function goPrev() {
            if (busy) return;
            busy = true;
            track.style.transition = "none";
            track.prepend(track.children[track.children.length - 1]);
            track.style.transform = "translateX(" + -step() + "px)";
            void track.offsetWidth;
            track.style.transition = "";
            track.style.transform = "translateX(0)";
            settle(function () {
                busy = false;
            });
        }

        function play() {
            if (!delay || prefersReduced()) return;
            stop();
            timer = window.setInterval(goNext, delay);
        }

        function stop() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        if (next) next.addEventListener("click", function () { goNext(); play(); });
        if (prev) prev.addEventListener("click", function () { goPrev(); play(); });

        root.addEventListener("mouseenter", stop);
        root.addEventListener("mouseleave", play);
        root.addEventListener("focusin", stop);
        root.addEventListener("focusout", play);

        // Keyboard
        root.setAttribute("tabindex", "0");
        root.addEventListener("keydown", function (e) {
            if (e.key === "ArrowRight") {
                e.preventDefault();
                goNext();
                play();
            } else if (e.key === "ArrowLeft") {
                e.preventDefault();
                goPrev();
                play();
            }
        });

        // Touch / pointer swipe
        var startX = null;
        root.addEventListener(
            "pointerdown",
            function (e) {
                startX = e.clientX;
                stop();
            },
            { passive: true }
        );
        root.addEventListener(
            "pointerup",
            function (e) {
                if (startX === null) return;
                var dx = e.clientX - startX;
                startX = null;
                if (Math.abs(dx) > 45) {
                    if (dx < 0) goNext();
                    else goPrev();
                }
                play();
            },
            { passive: true }
        );

        // Pause while the tab is hidden — no work off-screen.
        document.addEventListener("visibilitychange", function () {
            if (document.hidden) stop();
            else play();
        });

        play();
    }

    /* ----------------------------------------------------------------------
       Testimonial carousel — fade between slides, dots, autoplay
       ---------------------------------------------------------------------- */
    function initQuotes(root) {
        var slides = Array.prototype.slice.call(
            root.querySelectorAll("[data-quote-slide]")
        );
        var dotsHost = root.querySelector("[data-quote-dots]");
        if (slides.length < 2 || !dotsHost) return;

        var delay = parseInt(root.getAttribute("data-autoplay"), 10) || 6000;
        var index = 0;
        var timer = null;

        var dots = slides.map(function (_, i) {
            var dot = document.createElement("button");
            dot.type = "button";
            dot.setAttribute("role", "tab");
            dot.setAttribute("aria-label", "Testimonial " + (i + 1));
            dot.addEventListener("click", function () {
                show(i);
                play();
            });
            dotsHost.appendChild(dot);
            return dot;
        });

        function show(next) {
            if (next === index) return;
            var current = slides[index];
            var incoming = slides[next];

            if (prefersReduced()) {
                current.hidden = true;
                incoming.hidden = false;
            } else {
                current.classList.add("is-leaving");
                window.setTimeout(function () {
                    current.hidden = true;
                    current.classList.remove("is-leaving");

                    incoming.hidden = false;
                    incoming.classList.add("is-entering");
                    void incoming.offsetWidth;
                    incoming.classList.remove("is-entering");
                }, 320);
            }

            index = next;
            sync();
        }

        function sync() {
            dots.forEach(function (dot, i) {
                dot.setAttribute("aria-current", i === index ? "true" : "false");
            });
        }

        function play() {
            stop();
            if (prefersReduced()) return;
            timer = window.setInterval(function () {
                show((index + 1) % slides.length);
            }, delay);
        }

        function stop() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        root.addEventListener("mouseenter", stop);
        root.addEventListener("mouseleave", play);
        root.addEventListener("focusin", stop);
        root.addEventListener("focusout", play);
        document.addEventListener("visibilitychange", function () {
            if (document.hidden) stop();
            else play();
        });

        sync();
        play();
    }

    /* ----------------------------------------------------------------------
       Logo marquee — duplicate the set so the loop has no seam
       ---------------------------------------------------------------------- */
    function initMarquee() {
        var track = document.querySelector("[data-marquee]");
        if (!track) return;

        var originals = Array.prototype.slice.call(track.children);
        originals.forEach(function (node) {
            var clone = node.cloneNode(true);
            clone.setAttribute("aria-hidden", "true");
            clone.setAttribute("alt", "");
            track.appendChild(clone);
        });
    }

    /* ----------------------------------------------------------------------
       FAQ accordion — each row toggles independently, as in the prototype
       ---------------------------------------------------------------------- */
    function initFaq() {
        document
            .querySelectorAll(".faq__trigger")
            .forEach(function (trigger) {
                trigger.addEventListener("click", function () {
                    var item = trigger.closest(".faq__item");
                    var open = trigger.getAttribute("aria-expanded") === "true";
                    trigger.setAttribute("aria-expanded", String(!open));
                    item.classList.toggle("is-open", !open);
                });
            });
    }

    /* ----------------------------------------------------------------------
       Modal — dissolve in, focus trap, ESC, backdrop click, scroll lock
       ---------------------------------------------------------------------- */
    var FOCUSABLE =
        'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function initModal() {
        var modal = document.getElementById("contact-modal");
        if (!modal) return;

        var dialog = modal.querySelector("[data-modal-dialog]");
        var lastFocused = null;

        function open() {
            lastFocused = document.activeElement;
            modal.hidden = false;
            void modal.offsetWidth;
            modal.classList.add("is-open");

            document.body.style.overflow = "hidden";
            document.addEventListener("keydown", onKeydown);

            // The dialog is still `visibility: hidden` on this frame, and a
            // hidden element cannot take focus — wait for the class to paint.
            window.requestAnimationFrame(function () {
                var first = dialog.querySelector(FOCUSABLE);
                if (first) first.focus();
            });
        }

        function close() {
            modal.classList.remove("is-open");
            document.body.style.overflow = "";
            document.removeEventListener("keydown", onKeydown);

            var done = function () {
                modal.hidden = true;
                modal.removeEventListener("transitionend", done);
            };
            if (prefersReduced()) done();
            else modal.addEventListener("transitionend", done);

            if (lastFocused && lastFocused.focus) lastFocused.focus();
        }

        function onKeydown(e) {
            if (e.key === "Escape") {
                e.preventDefault();
                close();
                return;
            }
            if (e.key !== "Tab") return;

            var items = Array.prototype.filter.call(
                dialog.querySelectorAll(FOCUSABLE),
                function (el) {
                    return el.offsetParent !== null;
                }
            );
            if (!items.length) return;

            var first = items[0];
            var last = items[items.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }

        document
            .querySelectorAll('[data-modal-open="contact-modal"]')
            .forEach(function (trigger) {
                trigger.addEventListener("click", function (e) {
                    e.preventDefault();
                    open();
                });
            });

        modal.querySelectorAll("[data-modal-close]").forEach(function (btn) {
            btn.addEventListener("click", close);
        });

        // Backdrop click, but never a click that started inside the dialog.
        modal.addEventListener("mousedown", function (e) {
            if (e.target === modal) close();
        });

        // In-modal links that point back at the page.
        modal.querySelectorAll("[data-scroll-to]").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var target = document.querySelector(
                    btn.getAttribute("data-scroll-to")
                );
                close();
                if (target) {
                    target.scrollIntoView({
                        behavior: prefersReduced() ? "auto" : "smooth",
                        block: "start",
                    });
                }
            });
        });
    }

    /* ----------------------------------------------------------------------
       Contact form
       Validation, loading, success and error states, and the real submission.

       This used to resolve a timer and claim success without sending anything.
       It now posts the form to forms/lp-handler.php, which validates server
       side and mails the enquiry, and it reports success only when the handler
       says the mail was accepted. The form element carries the method, action
       and enctype, so the whole thing still submits normally if this script
       fails to load — the handler answers a non-JavaScript post with a
       redirect instead of JSON.

       FormData is used rather than JSON because the manuscript field is a file
       and a file cannot travel in a JSON body.
       ---------------------------------------------------------------------- */
    function submitEnquiry(form) {
        var data = new FormData(form);

        // Tell the handler to answer with JSON. The field defaults to "0" in
        // the markup so a no-JavaScript post still gets the redirect flow.
        data.set("_ajax", "1");

        return fetch(form.getAttribute("action"), {
            method: "POST",
            body: data,
            credentials: "same-origin",
            headers: { Accept: "application/json" }
        })
            .then(function (response) {
                return response
                    .json()
                    .catch(function () {
                        // A non-JSON body means something upstream of the
                        // handler answered — a PHP fatal, or a proxy error
                        // page. Treat it as a failure with a usable message
                        // rather than letting a parse error surface.
                        throw new Error(
                            "We could not send that just now. Please try again, or email info@elitepublishing.co."
                        );
                    })
                    .then(function (body) {
                        if (!response.ok || !body || body.ok !== true) {
                            var error = new Error(
                                (body && body.message) ||
                                    "We could not send that just now. Please try again."
                            );
                            error.fields = (body && body.errors) || {};
                            throw error;
                        }
                        return body;
                    });
            });
    }

    var VALIDATORS = {
        name: function (v) {
            return v.trim().length >= 2 ? "" : "Please enter your full name.";
        },
        email: function (v) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())
                ? ""
                : "Please enter a valid email address.";
        },
        phone: function (v) {
            var digits = v.replace(/\D/g, "");
            return digits.length >= 7
                ? ""
                : "Please enter a valid phone number.";
        },
        message: function (v) {
            return v.trim().length >= 10
                ? ""
                : "Please tell us a little more (10 characters or more).";
        },
    };

    function initForm() {
        var form = document.querySelector("[data-contact-form]");
        if (!form) return;

        var status = form.querySelector("[data-form-status]");
        var submit = form.querySelector("[data-submit]");

        function validateField(input) {
            var check = VALIDATORS[input.name];
            if (!check) return true;

            var message = check(input.value);
            var field = input.closest(".field");
            var error = field.querySelector(".field__error");

            field.classList.toggle("is-invalid", Boolean(message));
            input.setAttribute("aria-invalid", message ? "true" : "false");
            error.textContent = message;
            return !message;
        }

        form.querySelectorAll("input, textarea").forEach(function (input) {
            input.addEventListener("blur", function () {
                validateField(input);
            });
            input.addEventListener("input", function () {
                if (input.closest(".field").classList.contains("is-invalid")) {
                    validateField(input);
                }
            });
        });

        form.addEventListener("submit", function (e) {
            e.preventDefault();

            var inputs = Array.prototype.slice.call(
                form.querySelectorAll("input, textarea")
            );
            var valid = inputs.map(validateField).every(Boolean);

            status.textContent = "";
            status.removeAttribute("data-state");

            if (!valid) {
                status.textContent = "Please correct the highlighted fields.";
                status.setAttribute("data-state", "error");
                var firstBad = form.querySelector(".field.is-invalid input, .field.is-invalid textarea");
                if (firstBad) firstBad.focus();
                return;
            }

            /* The manuscript is optional, so an empty input is fine. A file
               that is present and oversized is caught here as well as on the
               server — uploading 40 MB only to be told it was too big wastes
               the visitor's time and connection. */
            var fileInput = form.querySelector('input[type="file"]');
            var chosen = fileInput && fileInput.files && fileInput.files[0];
            if (chosen && chosen.size > 25 * 1024 * 1024) {
                var fileField = fileInput.closest(".field");
                if (fileField) {
                    fileField.classList.add("is-invalid");
                    var slot = fileField.querySelector(".field__error");
                    if (slot) slot.textContent = "That file is over the 25 MB limit.";
                }
                status.textContent = "Please choose a smaller file, or email it to info@elitepublishing.co.";
                status.setAttribute("data-state", "error");
                fileInput.focus();
                return;
            }

            submit.classList.add("is-loading");
            submit.disabled = true;             // also stops a double send

            submitEnquiry(form)
                .then(function (body) {
                    form.reset();
                    /* The handler answers a successful send with where to go next, so the
                       visitor lands on the thank-you page instead of reading a confirmation
                       on the form they just left. If that key is ever missing, fall back to
                       the inline message rather than stranding them here. */
                    if (body && body.redirect) { window.location.assign(body.redirect); return; }
                    status.textContent = body.message;
                    status.setAttribute("data-state", "success");
                })
                .catch(function (error) {
                    // Field-level errors from the server are shown against the
                    // fields they belong to, so a rejected email address is
                    // marked where the visitor can see it rather than only in
                    // the summary line.
                    var fields = (error && error.fields) || {};
                    Object.keys(fields).forEach(function (name) {
                        var input = form.querySelector('[name="' + name + '"]');
                        var field = input && input.closest(".field");
                        if (!field) return;
                        var slot = field.querySelector(".field__error");
                        field.classList.add("is-invalid");
                        if (slot) slot.textContent = fields[name];
                    });

                    status.textContent =
                        (error && error.message) ||
                        "Something went wrong sending your message. Please try again, or email info@elitepublishing.co.";
                    status.setAttribute("data-state", "error");
                })
                .finally(function () {
                    submit.classList.remove("is-loading");
                    submit.disabled = false;
                });
        });
    }

    /* ----------------------------------------------------------------------
       Intro video
       The frame keeps the Figma poster + play badge until the visitor asks for
       the film; only then does the file load and the native controls appear.
       ---------------------------------------------------------------------- */
    function initVideo(root) {
        var video = root.querySelector("[data-video-el]");
        var trigger = root.querySelector("[data-video-play]");
        if (!video || !trigger) return;

        trigger.addEventListener("click", function () {
            root.classList.add("is-playing");
            video.controls = true;

            var started = video.play();
            // Autoplay policy allows this (it is a user gesture), but a codec
            // or network failure still rejects — put the poster back.
            if (started && started.catch) {
                started.catch(function () {
                    root.classList.remove("is-playing");
                    video.controls = false;
                });
            }

            // Keyboard users land on the controls they just asked for.
            video.focus();
        });

        // Once the film has run its course, offer the badge again.
        video.addEventListener("ended", function () {
            root.classList.remove("is-playing");
            video.controls = false;
            video.load(); // restores the poster frame
        });
    }

    /* ----------------------------------------------------------------------
       Boot
       ---------------------------------------------------------------------- */
    function init() {
        initReveals();
        initHeader();
        initMarquee();
        initFaq();
        initModal();
        initForm();

        document.querySelectorAll("[data-video]").forEach(initVideo);

        document.querySelectorAll("[data-carousel]").forEach(initCarousel);
        document.querySelectorAll("[data-quotes]").forEach(initQuotes);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
