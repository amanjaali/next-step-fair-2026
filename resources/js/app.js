import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import shareCard from './share-card';

Alpine.plugin(collapse);

/**
 * Multi-step registration wizard.
 *
 * Partial state is mirrored into localStorage on every change so a refresh, a
 * dropped mobile connection or an accidental back gesture does not wipe the form.
 * The key is per-track-and-type, so a parent form never restores a student's answers.
 *
 * Every DOM lookup below goes through `$root`, never `$el`. Alpine rebinds `$el`
 * to whichever element the expression is running on, so a method reached from
 * `@click` on a button sees the button — which silently breaks `querySelector`
 * for the step panels and makes `submit()` undefined.
 */
Alpine.data('nsWizard', (config) => ({
    step: 1,
    steps: config.steps || 4,
    storageKey: config.storageKey,
    values: {},
    errors: {},
    submitting: false,

    init() {
        this.values = { ...(config.values || {}) };
        this.restore();

        // Persist on any change inside the form, debounced to a frame.
        this.$root.addEventListener('input', () => queueMicrotask(() => this.persist()));
        this.$root.addEventListener('change', () => queueMicrotask(() => this.persist()));

        if (config.startStep) {
            this.step = Math.min(config.startStep, this.steps);
        }
    },

    restore() {
        try {
            const raw = window.localStorage.getItem(this.storageKey);
            if (!raw) return;
            const saved = JSON.parse(raw);
            if (!saved || typeof saved !== 'object') return;
            if (saved.__savedAt && Date.now() - saved.__savedAt > 1000 * 60 * 60 * 24 * 14) {
                window.localStorage.removeItem(this.storageKey);
                return;
            }
            delete saved.__savedAt;
            Object.entries(saved).forEach(([name, value]) => {
                this.values[name] = value;
                const field = this.$root.querySelector(`[name="${CSS.escape(name)}"]`);
                if (field && field.type !== 'file' && field.tagName !== 'BUTTON') {
                    field.value = value;
                }
            });
        } catch (e) {
            /* corrupted payload — start clean rather than block the form */
        }
    },

    persist() {
        try {
            const data = { ...this.values, __savedAt: Date.now() };
            this.$root.querySelectorAll('input, select, textarea').forEach((field) => {
                if (!field.name || field.type === 'file' || field.type === 'hidden') return;
                if (field.type === 'checkbox' || field.type === 'radio') return;
                if (field.dataset.noPersist !== undefined) return;
                data[field.name] = field.value;
            });
            window.localStorage.setItem(this.storageKey, JSON.stringify(data));
        } catch (e) {
            /* storage full or blocked (private mode) — the form still works */
        }
    },

    clear() {
        try {
            window.localStorage.removeItem(this.storageKey);
        } catch (e) {}
    },

    toggle(name, value) {
        const list = Array.isArray(this.values[name]) ? [...this.values[name]] : [];
        const i = list.indexOf(value);
        if (i > -1) list.splice(i, 1);
        else list.push(value);
        this.values[name] = list;
        this.persist();
    },

    isOn(name, value) {
        return Array.isArray(this.values[name]) && this.values[name].includes(value);
    },

    set(name, value) {
        this.values[name] = value;
        this.persist();
    },

    is(name, value) {
        return this.values[name] === value;
    },

    /** Validate only the fields on the current step, on the client, before advancing. */
    validateStep() {
        this.errors = {};
        const panel = this.$root.querySelector(`[data-step="${this.step}"]`);
        if (!panel) return true;

        panel.querySelectorAll('[data-required]').forEach((field) => {
            const name = field.dataset.required;
            const controls = [...panel.querySelectorAll(
                `[name="${CSS.escape(name)}"], [name="${CSS.escape(name + '[]')}"]`
            )];

            // Anything the user ticks — checkbox group, radio group, single consent
            // box — is satisfied by a checked control, never by a value. Reading
            // `.value` off a radio group is what let an unanswered question through:
            // an unchecked radio still reports the value in its markup, so the step
            // advanced and the server rejected the submission instead.
            const isChoice = controls.some((el) => el.type === 'radio' || el.type === 'checkbox');
            let ok;

            if (field.dataset.requiredKind === 'dom' || isChoice) {
                ok = controls.some((el) => el.checked);
            } else {
                const el = field.matches('input, select, textarea') ? field : controls[0];
                const val = el ? String(el.value || '').trim() : '';
                ok = val.length > 0;
                if (ok && el && el.dataset.pattern) {
                    ok = new RegExp(el.dataset.pattern).test(val);
                }
            }

            if (!ok) this.errors[name] = field.dataset.message || 'This field is required.';
        });

        this.errors = { ...this.errors };

        if (Object.keys(this.errors).length) {
            const first = panel.querySelector(`[data-error-for="${Object.keys(this.errors)[0]}"]`);
            if (first) first.scrollIntoView({ block: 'center', behavior: 'smooth' });
            return false;
        }
        return true;
    },

    next() {
        if (!this.validateStep()) return;
        if (this.step < this.steps) {
            this.step += 1;
            this.persist();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            this.submitting = true;
            this.clear();
            this.$root.submit();
        }
    },

    back() {
        if (this.step > 1) {
            this.step -= 1;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },
}));

/** Header navigation for narrow viewports. */
Alpine.data('nsNav', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
        document.body.style.overflow = this.open ? 'hidden' : '';
    },
    close() {
        this.open = false;
        document.body.style.overflow = '';
    },
}));

/** Photo album lightbox with keyboard navigation. */
Alpine.data('nsLightbox', (items) => ({
    items: items || [],
    index: 0,
    open: false,
    show(i) {
        this.index = i;
        this.open = true;
    },
    close() {
        this.open = false;
    },
    next() {
        this.index = (this.index + 1) % this.items.length;
    },
    prev() {
        this.index = (this.index - 1 + this.items.length) % this.items.length;
    },
    get current() {
        return this.items[this.index] || {};
    },
}));

/** OTP entry: 6 digits, auto-advance, paste-friendly, with a resend cooldown. */
Alpine.data('nsOtp', (cooldown = 60) => ({
    cooldown: 0,
    init() {
        this.cooldown = cooldown;
        this.tick();
    },
    tick() {
        if (this.cooldown <= 0) return;
        setTimeout(() => {
            this.cooldown -= 1;
            this.tick();
        }, 1000);
    },
    onInput(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
    },
}));

/** Days-until counter on the home page, so the number is never stale in cache. */
Alpine.data('nsCountdown', (targetIso) => ({
    days: 0,
    init() {
        const target = new Date(targetIso + 'T00:00:00+03:00');
        const diff = target.getTime() - Date.now();
        this.days = Math.max(0, Math.ceil(diff / 86400000));
    },
}));

/** Copy-to-clipboard for share links and ticket ids. */
Alpine.data('nsCopy', () => ({
    copied: false,
    async copy(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => (this.copied = false), 2000);
        } catch (e) {}
    },
}));

window.Alpine = Alpine;
Alpine.start();

/* Fade-and-rise on scroll: 300ms, no parallax, disabled under reduced motion. */
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const risers = document.querySelectorAll('.ns-rise');

if (reduced || !('IntersectionObserver' in window)) {
    risers.forEach((el) => el.classList.add('is-visible'));
} else {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.05 }
    );
    risers.forEach((el) => observer.observe(el));
}

/* Puts the person's name on their share card, where there is one on the page. */
shareCard();
