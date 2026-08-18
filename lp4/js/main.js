/* ==========================================================================
   Elite Publishing — LP 4 behaviour
   Vanilla ES2020. Every module is a no-op when its markup is absent.
   ========================================================================== */
(() => {
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    /* ---- Scroll reveal --------------------------------------------------- */
    function initReveal() {
        const targets = document.querySelectorAll('[data-animation]');
        if (!targets.length) return;

        if (reduceMotion.matches || !('IntersectionObserver' in window)) {
            targets.forEach((el) => el.classList.add('is-visible', 'is-settled'));
            return;
        }

        // Groups marked [data-stagger] get incremental delays so their children
        // cascade instead of landing together.
        document.querySelectorAll('[data-stagger]').forEach((group) => {
            const step = parseFloat(getComputedStyle(group).getPropertyValue('--reveal-step')) || 90;
            [...group.children].forEach((child, i) => {
                const el = child.matches('[data-animation]') ? child : child.querySelector('[data-animation]');
                if (el) el.style.setProperty('--reveal-delay', `${i * step}ms`);
            });
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                el.classList.add('is-visible');
                el.addEventListener('transitionend', () => el.classList.add('is-settled'), { once: true });
                observer.unobserve(el);
            });
        }, { rootMargin: '0px 0px -12% 0px', threshold: 0.12 });

        targets.forEach((el) => observer.observe(el));
    }

    /* ---- Sticky header --------------------------------------------------- */
    function initHeader() {
        const header = document.getElementById('siteHeader');
        if (!header) return;

        // A sentinel keeps this off the scroll event loop entirely. It is taken
        // out of flow so it cannot push the hero down, and it is as tall as the
        // scroll distance after which the bar should solidify.
        const sentinel = document.createElement('div');
        sentinel.setAttribute('aria-hidden', 'true');
        sentinel.style.cssText =
            'position:absolute;top:0;left:0;width:1px;height:120px;pointer-events:none;';
        header.parentNode.insertBefore(sentinel, header);

        new IntersectionObserver(
            ([entry]) => header.classList.toggle('is-stuck', !entry.isIntersecting)
        ).observe(sentinel);
    }

    /* ---- Stat counters --------------------------------------------------- */
    function initCounters() {
        const counters = document.querySelectorAll('[data-count-to]');
        if (!counters.length) return;

        if (reduceMotion.matches || !('IntersectionObserver' in window)) return;

        const run = (el) => {
            const target = Number(el.dataset.countTo);
            if (!Number.isFinite(target)) return;
            const duration = 1400;
            let start = null;

            const tick = (now) => {
                if (start === null) start = now;
                const p = Math.min((now - start) / duration, 1);
                // easeOutCubic
                el.textContent = String(Math.round(target * (1 - Math.pow(1 - p, 3))));
                if (p < 1) requestAnimationFrame(tick);
            };

            el.textContent = '0';
            requestAnimationFrame(tick);
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                run(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.6 });

        counters.forEach((el) => observer.observe(el));
    }

    /* ---- Testimonial carousel -------------------------------------------- */
    function initCarousel() {
        const root = document.getElementById('testimonialCarousel');
        if (!root) return;

        const track = root.querySelector('.carousel__track');
        const prev = root.querySelector('[data-carousel-prev]');
        const next = root.querySelector('[data-carousel-next]');
        if (!track || track.children.length < 2) return;

        // The frame shows three cards with the middle one in focus, so the deck
        // rotates rather than scrolling to an end — otherwise, with only three
        // testimonials, the focused card would slide off centre and stop.
        let animating = false;

        const perView = () => {
            const pct = parseFloat(getComputedStyle(track.firstElementChild).flexBasis);
            return pct > 0 ? Math.max(1, Math.round(100 / pct)) : 1;
        };

        const markActive = () => {
            const view = perView();
            const focus = Math.floor((view - 1) / 2);
            [...track.children].forEach((slide, i) => {
                slide.classList.toggle('is-active', i === focus);
            });
        };

        const settle = (fn) => {
            let ran = false;
            const done = (e) => {
                if (e && e.target !== track) return;
                if (ran) return;                        // transitionend + fallback both fire
                ran = true;
                track.removeEventListener('transitionend', done);
                fn();
                animating = false;
            };
            if (reduceMotion.matches) {
                done();
            } else {
                track.addEventListener('transitionend', done);
                // Guard against a dropped transitionend (e.g. background tab).
                setTimeout(done, 900);
            }
        };

        const rotate = (dir) => {
            if (animating) return;
            animating = true;
            const step = 100 / perView();

            if (dir > 0) {
                track.style.transform = `translate3d(${-step}%, 0, 0)`;
                settle(() => {
                    track.appendChild(track.firstElementChild);
                    track.style.transition = 'none';
                    track.style.transform = 'translate3d(0, 0, 0)';
                    void track.offsetWidth;                 // flush before restoring
                    track.style.transition = '';
                    markActive();
                });
            } else {
                track.insertBefore(track.lastElementChild, track.firstElementChild);
                track.style.transition = 'none';
                track.style.transform = `translate3d(${-step}%, 0, 0)`;
                void track.offsetWidth;
                track.style.transition = '';
                track.style.transform = 'translate3d(0, 0, 0)';
                settle(markActive);
            }
        };

        prev?.addEventListener('click', () => rotate(-1));
        next?.addEventListener('click', () => rotate(1));

        root.tabIndex = 0;
        root.setAttribute('role', 'group');
        root.setAttribute('aria-roledescription', 'carousel');
        root.setAttribute('aria-label', 'Author testimonials');

        root.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') { e.preventDefault(); rotate(-1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); rotate(1); }
        });

        // Touch / pointer swipe
        let startX = 0;
        let dragging = false;

        root.addEventListener('pointerdown', (e) => {
            if (e.pointerType === 'mouse' && e.button !== 0) return;
            dragging = true;
            startX = e.clientX;
        });

        root.addEventListener('pointerup', (e) => {
            if (!dragging) return;
            dragging = false;
            const dx = e.clientX - startX;
            if (Math.abs(dx) > 45) rotate(dx < 0 ? 1 : -1);
        });

        root.addEventListener('pointercancel', () => { dragging = false; });

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(markActive, 150);
        });

        markActive();
    }

    /* ---- FAQ accordion ---------------------------------------------------- */
    function initAccordion() {
        const items = document.querySelectorAll('.faq-item');
        if (!items.length) return;

        const close = (trigger, panel) => {
            panel.style.height = `${panel.scrollHeight}px`;
            requestAnimationFrame(() => { panel.style.height = '0px'; });
            trigger.setAttribute('aria-expanded', 'false');
        };

        const open = (trigger, panel) => {
            panel.style.height = `${panel.scrollHeight}px`;
            trigger.setAttribute('aria-expanded', 'true');
            // Release to auto once the transition lands so reflowed text still fits.
            panel.addEventListener('transitionend', function done(e) {
                if (e.propertyName !== 'height') return;
                if (trigger.getAttribute('aria-expanded') === 'true') panel.style.height = 'auto';
                panel.removeEventListener('transitionend', done);
            });
        };

        items.forEach((item) => {
            const trigger = item.querySelector('.faq-item__trigger');
            const panel = item.querySelector('.faq-item__panel');
            if (!trigger || !panel) return;

            // Rows open independently, as they do in the prototype.
            trigger.addEventListener('click', () => {
                const isOpen = trigger.getAttribute('aria-expanded') === 'true';
                isOpen ? close(trigger, panel) : open(trigger, panel);
            });
        });
    }

    /* ---- Manuscript form -------------------------------------------------- */
    function initForm() {
        const form = document.getElementById('manuscriptForm');
        if (!form) return;

        const submit = document.getElementById('manuscriptSubmit');
        const status = document.getElementById('formStatus');
        const fileInput = document.getElementById('manuscript');
        const fileName = form.querySelector('[data-file-name]');
        const MAX_BYTES = 25 * 1024 * 1024;

        const rules = {
            fullName: (v) => (v.trim().length >= 2 ? '' : 'Please enter your full name.'),
            email: (v) => (/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) ? '' : 'Please enter a valid email address.'),
            phone: (v) => (v.replace(/[^\d]/g, '').length >= 7 ? '' : 'Please enter a contact number.'),
            manuscript: () => {
                const file = fileInput?.files?.[0];
                if (!file) return '';                       // optional
                return file.size <= MAX_BYTES ? '' : 'That file is over the 25 MB limit.';
            },
        };

        const setError = (name, message) => {
            const field = form.querySelector(`[name="${name}"]`)?.closest('.field');
            const slot = form.querySelector(`[data-error-for="${name}"]`);
            if (!field || !slot) return;
            field.classList.toggle('is-invalid', Boolean(message));
            slot.textContent = message;
            const control = form.querySelector(`[name="${name}"]`);
            if (control) control.setAttribute('aria-invalid', message ? 'true' : 'false');
        };

        const validate = (name) => {
            const control = form.querySelector(`[name="${name}"]`);
            const message = rules[name] ? rules[name](control ? control.value : '') : '';
            setError(name, message);
            return !message;
        };

        Object.keys(rules).forEach((name) => {
            const control = form.querySelector(`[name="${name}"]`);
            if (!control) return;
            control.addEventListener('blur', () => validate(name));
            control.addEventListener('input', () => {
                if (control.closest('.field')?.classList.contains('is-invalid')) validate(name);
            });
        });

        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (fileName) fileName.textContent = file ? file.name : 'Upload Manuscript';
            validate('manuscript');
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (status) { status.className = 'form-status'; status.textContent = ''; }

            const ok = Object.keys(rules).map(validate).every(Boolean);
            if (!ok) {
                form.querySelector('.field.is-invalid .field__control')?.focus();
                return;
            }

            submit?.classList.add('is-loading');
            submit?.setAttribute('aria-busy', 'true');
            if (submit) submit.disabled = true;             // no double send

            /* Posts to forms/lp-handler.php, which validates server side and
               mails the enquiry with the manuscript attached when there is one.
               This replaced a setTimeout that reported success without sending
               anything. Success is now only ever shown when the handler
               confirms the message was accepted.

               FormData carries the file; the form also has method, action and
               enctype, so it still submits without this script. */
            const payload = new FormData(form);
            payload.set('_ajax', '1');

            // Kept so anything else on the page can still observe or intercept
            // a submission, as the original integration point allowed.
            const event = new CustomEvent('manuscript:submit', { detail: { payload }, cancelable: true });
            const handled = !form.dispatchEvent(event);

            try {
                if (!handled) {
                    const response = await fetch(form.getAttribute('action'), {
                        method: 'POST',
                        body: payload,
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json' },
                    });

                    let body = null;
                    try {
                        body = await response.json();
                    } catch {
                        // A non-JSON reply means something upstream of the
                        // handler answered — a PHP fatal or a proxy error page.
                        throw new Error('We could not send that just now. Please try again, or email info@elitepublishing.co.');
                    }

                    if (!response.ok || !body || body.ok !== true) {
                        /* Show field-level errors where they belong, so a
                           rejected address is marked on the field itself. The
                           handler serves four landing pages and answers with
                           its own field names, which is why `name` and
                           `message` are translated to this page's `fullName`
                           and `notes` rather than silently missing. */
                        const FIELD_NAMES = { name: 'fullName', message: 'notes' };
                        Object.entries(body?.errors || {}).forEach(([field, text]) =>
                            setError(FIELD_NAMES[field] || field, String(text))
                        );
                        throw new Error(body?.message || 'We could not send that just now. Please try again.');
                    }

                    /* The handler answers a successful send with where to go next, so the
                       visitor lands on the thank-you page rather than reading a confirmation
                       on the form they just left. The inline status is the fallback for a
                       response that carries no redirect. */
                    if (body && body.redirect) { window.location.assign(body.redirect); return; }


                    if (status) {
                        status.className = 'form-status is-success';
                        status.textContent = body.message;
                    }
                } else if (status) {
                    status.className = 'form-status is-success';
                    status.textContent = 'Thank you — your manuscript is with us. We will be in touch within one business day.';
                }

                form.reset();
                if (fileName) fileName.textContent = 'Upload Manuscript';
            } catch (error) {
                if (status) {
                    status.className = 'form-status is-error';
                    status.textContent = error?.message
                        || 'Something went wrong sending your details. Please try again, or email info@elitepublishing.co.';
                }
            } finally {
                submit?.classList.remove('is-loading');
                submit?.removeAttribute('aria-busy');
                if (submit) submit.disabled = false;
            }
        });
    }

    /* ---- Back to top ------------------------------------------------------ */
    function initToTop() {
        const btn = document.getElementById('toTop');
        if (!btn) return;

        const sentinel = document.createElement('div');
        sentinel.setAttribute('aria-hidden', 'true');
        document.body.prepend(sentinel);

        new IntersectionObserver(
            ([entry]) => btn.classList.toggle('is-visible', !entry.isIntersecting),
            { rootMargin: '600px 0px 0px 0px' }
        ).observe(sentinel);

        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: reduceMotion.matches ? 'auto' : 'smooth',
            });
        });
    }

    /* ---- Boot -------------------------------------------------------------- */
    const boot = () => {
        initReveal();
        initHeader();
        initCounters();
        initCarousel();
        initAccordion();
        initForm();
        initToTop();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
