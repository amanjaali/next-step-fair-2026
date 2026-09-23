import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

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

/**
 * The requirements box on the scholarship application form.
 *
 * University and department used to be two independent selects with every
 * department from every university listed at once — a student picked their
 * university, then had to scroll a hundred unrelated departments to find the
 * handful that were actually theirs. The department list is now filtered to
 * the chosen university as soon as it is picked, and cleared back to empty
 * if the university changes to one it does not belong to, rather than
 * silently keeping a stranger's seat filled in.
 *
 * Changing either select clears that slot's acknowledgement: the box shown
 * changed, so the tick has to happen again.
 */
Alpine.data('nsScholarshipChoice', () => ({
    /* Only the first choice is required. Up to five, in order. */
    slotOrder: ['first', 'second', 'third', 'fourth', 'fifth'],

    slots: Object.fromEntries(['first', 'second', 'third', 'fourth', 'fifth'].map((slot) => [slot, {
        requirements: '', hasRequirements: false, ack: false, requiresForm: false,
        requiresExternalForm: false, externalFormUrl: '', externalFormName: '', externalFormAck: false,
    }])),

    /* Two shown by default; a saved application may already have more filled
       in, so those are revealed too rather than hiding data the student
       already entered. */
    visibleSlots: ['first', 'second'],

    init() {
        this.slotOrder.forEach((slot) => {
            const uniSelect = this.$root.querySelector(`select[name="${slot}_choice_university"]`);

            if (uniSelect && uniSelect.value !== '' && !this.visibleSlots.includes(slot)) {
                this.visibleSlots.push(slot);
            }
        });

        this.slotOrder.forEach((slot) => {
            this.filterDepartments(slot);
            this.sync(slot);
        });
    },

    addChoice() {
        const next = this.slotOrder.find((slot) => !this.visibleSlots.includes(slot));

        if (next) {
            this.visibleSlots.push(next);
        }
    },

    /* Clears the slot's own selects before hiding it, so a removed choice
       does not silently resubmit whatever was picked before it was hidden. */
    removeChoice(slot) {
        this.visibleSlots = this.visibleSlots.filter((s) => s !== slot);

        const uniSelect = this.$root.querySelector(`select[name="${slot}_choice_university"]`);
        const deptSelect = this.$root.querySelector(`select[name="${slot}_choice_department"]`);

        if (uniSelect) {
            uniSelect.value = '';
        }
        if (deptSelect) {
            deptSelect.value = '';
        }

        this.filterDepartments(slot);
        this.sync(slot);
    },

    /**
     * Shows only the departments that belong to the chosen university,
     * hiding the rest instead of leaving every university's departments in
     * one long list. A department already selected under a different
     * university is cleared, since it no longer applies to this choice.
     */
    filterDepartments(slot) {
        const uniSelect = this.$root.querySelector(`select[name="${slot}_choice_university"]`);
        const deptSelect = this.$root.querySelector(`select[name="${slot}_choice_department"]`);

        if (!uniSelect || !deptSelect) {
            return;
        }

        const chosen = uniSelect.value;
        let selectedStillBelongs = false;

        deptSelect.querySelectorAll('optgroup').forEach((group) => {
            group.hidden = chosen !== '' && group.label !== chosen;
        });

        deptSelect.querySelectorAll('option[data-university]').forEach((option) => {
            const belongs = chosen === '' || option.dataset.university === chosen;
            option.hidden = !belongs;
            option.disabled = !belongs;

            if (option.selected && option.dataset.university === chosen) {
                selectedStillBelongs = true;
            }
        });

        deptSelect.disabled = chosen === '';

        if (chosen !== '' && deptSelect.value !== '' && !selectedStillBelongs) {
            deptSelect.value = '';
        }
    },

    sync(slot) {
        const uniSelect = this.$root.querySelector(`select[name="${slot}_choice_university"]`);
        const deptSelect = this.$root.querySelector(`select[name="${slot}_choice_department"]`);

        if (!uniSelect || !deptSelect) {
            return;
        }

        const uniOption = uniSelect.selectedOptions[0];
        const deptOption = deptSelect.selectedOptions[0];

        // The university's own text is dashboard-authored rich text, already
        // sanitized server-side before it ever reached this attribute — safe
        // to render as-is. The department's is a plain textarea, so it is
        // escaped here rather than trusted, same reasoning as the "about"
        // text this mirrors on the universities page.
        const uniHtml = (uniOption && uniOption.value) ? (uniOption.dataset.requirements || '') : '';
        const deptBelongsToUni = deptOption && uniOption && deptOption.dataset.university === uniOption.value;
        const deptText = deptBelongsToUni ? (deptOption.dataset.requirements || '') : '';
        const deptHtml = deptText ? `<p>${this.escapeHtml(deptText)}</p>` : '';

        this.slots[slot].requirements = [uniHtml, deptHtml].filter(Boolean).join('<hr class="my-3 border-0 border-t border-[rgba(5,7,8,0.14)]">');
        this.slots[slot].hasRequirements = this.slots[slot].requirements.length > 0;
        this.slots[slot].requiresForm = Boolean(deptBelongsToUni && deptOption.dataset.requiresForm);
        this.slots[slot].ack = false;

        // Whole-university, so this reads off the university option only —
        // it does not matter which department under it was picked.
        this.slots[slot].requiresExternalForm = Boolean(uniOption && uniOption.value && uniOption.dataset.requiresExternalForm);
        this.slots[slot].externalFormUrl = this.slots[slot].requiresExternalForm ? (uniOption.dataset.externalFormUrl || '') : '';
        this.slots[slot].externalFormName = this.slots[slot].requiresExternalForm ? uniOption.value : '';
        this.slots[slot].externalFormAck = false;
    },

    /** Whether an unchecked box in any visible slot is still holding the form shut. */
    blocked() {
        return this.visibleSlots.some((slot) => (this.slots[slot].hasRequirements && !this.slots[slot].ack)
            || (this.slots[slot].requiresExternalForm && !this.slots[slot].externalFormAck));
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;

        return div.innerHTML;
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

