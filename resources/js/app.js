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

/**
 * One dropdown in the top menu.
 *
 * Opens on hover and on click, because both are things people do to a menu with
 * an arrow on it — and closes on Escape, on a click elsewhere and on the pointer
 * leaving. Separate from nsNav so the mobile panel and the dropdown cannot fight
 * over the same `open`.
 */
Alpine.data('nsNavGroup', () => ({
    open: false,
    hovered: false,

    enter() {
        this.hovered = true;
        this.open = true;
    },

    leave() {
        this.hovered = false;
        this.open = false;
    },

    /*
     * A click while the pointer is already inside means hover opened it a moment
     * ago — closing on that click would undo the gesture that opened it, which
     * reads as the menu refusing to stay open. Anywhere else, it toggles.
     */
    press() {
        this.open = this.hovered ? true : !this.open;
    },

    close() {
        this.open = false;
        this.hovered = false;
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
/**
 * The offer popup on the home page.
 *
 * The decision to open is made here rather than on the server, so it survives
 * page caching and never flashes before the answer is known. The key carries the
 * popup's version, so an edit in the dashboard brings it back for everybody and
 * an untouched popup stays closed.
 *
 * A browser with localStorage switched off (private windows on older Safari)
 * throws on read. It should still see the popup, so a failure opens it rather
 * than swallowing it.
 */
Alpine.data('nsOfferPopup', (key) => ({
    open: false,

    init() {
        let dismissed = false;

        try {
            dismissed = window.localStorage.getItem(key) === '1';
        } catch (e) {
            dismissed = false;
        }

        // A moment's delay: opening on the same frame as the page lands feels
        // like a fault, and hides the thing it interrupted.
        if (!dismissed) {
            setTimeout(() => {
                this.open = true;
            }, 900);
        }
    },

    close() {
        this.open = false;

        try {
            window.localStorage.setItem(key, '1');
        } catch (e) {
            // Nothing to do — they will see it again, which is the safe way round.
        }
    },
}));

/**
 * The scholarship eligibility check, counting itself as it is filled in.
 *
 * Reads the radios rather than keeping its own copy of the answers, so a page
 * restored with answers already saved starts at the right number instead of
 * zero.
 */
Alpine.data('nsEligibility', (total) => ({
    total,
    answered: 0,
    missing: [],
    showMissing: false,

    init() {
        /*
         * The form is held, not looked up each time. Inside a handler bound with
         * @change, Alpine points $el at the element that fired the event — the
         * radio — so querying $el for the questions returned nothing, and the
         * counter sat on 0/5 however many were answered.
         */
        this.form = this.$root;
        this.recount();
    },

    recount() {
        const sets = [...this.form.querySelectorAll('fieldset[data-question]')];

        this.missing = sets
            .filter((set) => !set.querySelector('input[type="radio"]:checked'))
            .map((set) => set.dataset.question);

        this.answered = sets.length - this.missing.length;

        // Flagged only once they are part-way through: a page that opens with
        // every question marked is telling somebody off for not having started.
        this.showMissing = this.answered > 0 && this.answered < sets.length;
    },
}));

/*
 * Every Alpine.data() above has to be registered before this line. Registering
 * one after it leaves the component undefined, and Alpine then resolves the
 * expression against the window — so `x-show="open"` finds window.open, which is
 * a function and therefore truthy. The panel appears, nothing can close it, and
 * the only clue is a warning in the console.
 */
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
