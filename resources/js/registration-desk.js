/**
 * Registration desk.
 *
 * Browse the list, issue an account (name, type and phone), edit any record,
 * and — only for the volunteer who created it, only within an hour —
 * delete it. Check-in still happens at the gate, not here.
 */
const shell = document.querySelector('.rg-shell');
if (shell) {
    const urls = {
        search: shell.dataset.searchUrl,
        base: shell.dataset.storeUrl,
    };
    const csrf = shell.dataset.csrf;
    const phoneCountries = (shell.dataset.phoneCountries || '').split(',').filter(Boolean);
    const defaultPhoneCountry = shell.dataset.defaultPhoneCountry || phoneCountries[0] || '';

    const t = (key, fallback) => shell.dataset[`label${key.replace(/(^|_)([a-z])/g, (m, sep, c) => c.toUpperCase())}`] || fallback;

    const el = {
        searchInput: shell.querySelector('[data-search-input]'),
        results: shell.querySelector('[data-results]'),
        resultsCount: shell.querySelector('[data-results-count]'),
        loadMore: shell.querySelector('[data-load-more]'),
        openNew: shell.querySelector('[data-open-new]'),
        panel: shell.querySelector('[data-panel]'),
        panelTitle: shell.querySelector('[data-panel-title]'),
        panelClose: shell.querySelector('[data-panel-close]'),
        save: shell.querySelector('[data-save]'),
        del: shell.querySelector('[data-delete]'),
        deleteHint: shell.querySelector('[data-delete-hint]'),
        history: shell.querySelector('[data-history]'),
        historyList: shell.querySelector('[data-history-list]'),
        phoneCountrySelect: shell.querySelector('[data-field="phone_country"]'),
        typeSelect: shell.querySelector('[data-field="type"]'),
        phoneInput: shell.querySelector('[data-field="phone"]'),
        membersFeature: shell.querySelector('[data-members-feature]'),
        membersToggle: shell.querySelector('[data-add-members-toggle]'),
        membersContainer: shell.querySelector('[data-members]'),
        memberRows: shell.querySelector('[data-member-rows]'),
        addMemberBtn: shell.querySelector('[data-add-member]'),
    };

    const TYPE_OPTIONS = [
        ['visitor', t('type_visitor', 'Visitor')],
        ['parent', t('type_parent', 'Parent')],
        ['student', t('type_student', 'Student')],
    ];

    phoneCountries.forEach((code) => {
        const opt = document.createElement('option');
        opt.value = code;
        opt.textContent = code;
        el.phoneCountrySelect.appendChild(opt);
    });
    el.phoneCountrySelect.value = defaultPhoneCountry;

    // Digits only, and never more than an 11-digit Iraqi number (a leading 0
    // plus the 10-digit mobile) can hold — pasted spaces/dashes get stripped
    // too, so "7XX XXX XXXX" and "07XX XXX XXXX" both come out clean.
    el.phoneInput?.addEventListener('input', () => {
        const digitsOnly = el.phoneInput.value.replace(/\D/g, '').slice(0, 11);
        if (digitsOnly !== el.phoneInput.value) el.phoneInput.value = digitsOnly;
    });

    /* ------------------------------------------------------ family members -- */

    function createMemberRow() {
        const row = document.createElement('div');
        row.className = 'rg-member';
        row.setAttribute('data-member-row', '');

        const typeOptionsHtml = TYPE_OPTIONS
            .map(([value, label]) => `<option value="${value}">${escapeHtml(label)}</option>`)
            .join('');

        row.innerHTML = `
            <label class="rg-field">
                <span class="rg-label">${escapeHtml(t('field_name', 'Full name'))}</span>
                <input type="text" class="rg-input" data-member-field="full_name" data-required maxlength="120"
                       placeholder="${escapeHtml(t('member_name_placeholder', 'Full name'))}">
            </label>
            <label class="rg-field">
                <span class="rg-label">${escapeHtml(t('field_type', 'Type'))}</span>
                <select class="rg-select" data-member-field="type" data-required>${typeOptionsHtml}</select>
            </label>
            <button type="button" class="rg-member__remove" data-remove-member>${escapeHtml(t('remove_member', 'Remove'))}</button>
        `;

        return row;
    }

    function addMemberRow() {
        el.memberRows?.appendChild(createMemberRow());
    }

    function clearMemberRows() {
        if (el.memberRows) el.memberRows.innerHTML = '';
    }

    function collectMembers() {
        if (!el.membersContainer || el.membersContainer.hidden) return [];
        return Array.from(el.memberRows.querySelectorAll('[data-member-row]')).map((row) => ({
            full_name: row.querySelector('[data-member-field="full_name"]').value.trim(),
            type: row.querySelector('[data-member-field="type"]').value,
        }));
    }

    el.membersToggle?.addEventListener('change', () => {
        const on = el.membersToggle.checked;
        el.membersContainer.hidden = !on;
        if (on && !el.memberRows.querySelector('[data-member-row]')) addMemberRow();
        if (!on) clearMemberRows();
    });

    el.addMemberBtn?.addEventListener('click', addMemberRow);

    el.memberRows?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-member]');
        if (!button) return;
        button.closest('[data-member-row]')?.remove();
    });

    let currentId = null;
    let currentPage = 1;
    let hasMorePages = false;

    /* --------------------------------------------------------------- toast -- */

    function toast(message, tone = 'success') {
        let node = shell.querySelector('.rg-toast');
        if (!node) {
            node = document.createElement('div');
            node.className = 'rg-toast';
            shell.appendChild(node);
        }
        node.textContent = message;
        node.dataset.tone = tone;
        node.hidden = false;
        clearTimeout(node._timer);
        node._timer = setTimeout(() => { node.hidden = true; }, 3200);
    }

    /* -------------------------------------------------------------- search -- */

    function hitMarkup(r) {
        return `
                <button type="button" class="rg-hit" data-id="${r.id}">
                    <span class="rg-hit__top">
                        <span class="rg-hit__name">${escapeHtml(r.full_name)}</span>
                        ${r.is_walk_in ? `<span class="rg-hit__badge rg-hit__badge--walkin">${t('walk_in', 'Walk-in')}</span>` : ''}
                        ${r.checked_in_today ? `<span class="rg-hit__badge rg-hit__badge--checked">${t('checked_in_today', 'Checked in today')}</span>` : ''}
                    </span>
                    <span class="rg-hit__detail">${[r.type ? escapeHtml(r.type.toUpperCase()) : '', escapeHtml(r.phone || '')].filter(Boolean).join(' · ')}</span>
                    <span class="rg-hit__ticket">${escapeHtml(r.ticket || '')}</span>
                </button>
            `;
    }

    function renderResults(items) {
        el.results.innerHTML = items.length
            ? items.map(hitMarkup).join('')
            : `<p class="rg-empty">${t('no_results', 'No registration matches that.')}</p>`;
    }

    function appendResults(items) {
        el.results.insertAdjacentHTML('beforeend', items.map(hitMarkup).join(''));
    }

    function updatePagingUi(payload) {
        currentPage = payload.current_page || 1;
        hasMorePages = payload.current_page < payload.last_page;
        el.loadMore.hidden = !hasMorePages;

        const shown = ((payload.current_page - 1) * payload.per_page) + (payload.data?.length || 0);
        el.resultsCount.textContent = payload.total
            ? (t('showing_count', 'Showing :shown of :total').replace(':shown', shown).replace(':total', payload.total))
            : '';
    }

    async function runSearch() {
        const q = el.searchInput.value.trim();
        const params = new URLSearchParams();
        if (q) params.set('q', q);

        try {
            const response = await fetch(`${urls.search}?${params.toString()}`, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            renderResults(payload.data || []);
            updatePagingUi(payload);
        } catch (e) {
            toast('Could not reach the server.', 'error');
        }
    }

    async function loadMore() {
        if (!hasMorePages) return;
        const q = el.searchInput.value.trim();
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        params.set('page', currentPage + 1);

        el.loadMore.disabled = true;
        try {
            const response = await fetch(`${urls.search}?${params.toString()}`, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            appendResults(payload.data || []);
            updatePagingUi(payload);
        } catch (e) {
            toast('Could not reach the server.', 'error');
        } finally {
            el.loadMore.disabled = false;
        }
    }
    el.loadMore?.addEventListener('click', loadMore);

    let searchTimer;
    el.searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 250);
    });

    el.results?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-id]');
        if (!button) return;
        openEdit(button.dataset.id);
    });

    /* --------------------------------------------------------------- panel -- */

    function fieldsOf(form) {
        return Array.from(form.querySelectorAll('[data-field]'));
    }

    // Clear a field's red outline as soon as the volunteer fixes it.
    el.panel.querySelector('[data-form]')?.addEventListener('input', (event) => {
        const field = event.target.closest('[data-required]');
        if (field && field.value.trim() !== '') field.classList.remove('rg-input--error');
    });
    el.panel.querySelector('[data-form]')?.addEventListener('change', (event) => {
        const field = event.target.closest('[data-required]');
        if (field && field.value.trim() !== '') field.classList.remove('rg-input--error');
    });

    function resetForm() {
        const form = el.panel.querySelector('[data-form]');
        fieldsOf(form).forEach((input) => { input.value = ''; });
        el.phoneCountrySelect.value = defaultPhoneCountry;
        el.typeSelect.value = 'visitor';
        clearFieldErrors();
        el.del.hidden = true;
        el.deleteHint.textContent = '';
        el.history.hidden = true;
        el.historyList.innerHTML = '';
        if (el.membersToggle) el.membersToggle.checked = false;
        if (el.membersContainer) el.membersContainer.hidden = true;
        clearMemberRows();
    }

    function fillForm(record) {
        const form = el.panel.querySelector('[data-form]');
        fieldsOf(form).forEach((input) => {
            const key = input.dataset.field;
            if (key in record && record[key] !== null) input.value = record[key];
        });
        if (record.phone_country) el.phoneCountrySelect.value = record.phone_country;
    }

    function openPanel() {
        el.panel.hidden = false;
    }
    function closePanel() {
        el.panel.hidden = true;
    }

    el.openNew?.addEventListener('click', () => {
        currentId = null;
        el.panelTitle.textContent = t('form_title_new', 'New walk-in');
        resetForm();
        if (el.membersFeature) el.membersFeature.hidden = false;
        openPanel();
    });

    el.panelClose?.addEventListener('click', closePanel);

    async function openEdit(id) {
        let response;
        try {
            response = await fetch(`${urls.base}/${id}`, { headers: { Accept: 'application/json' } });
        } catch (e) {
            toast('Could not reach the server.', 'error');
            return;
        }
        if (!response.ok) {
            toast(response.status === 401 ? 'Your session expired — sign in again.' : 'Could not load that record.', 'error');
            return;
        }
        const record = await response.json();

        currentId = id;
        el.panelTitle.textContent = t('form_title_edit', 'Edit registration');
        resetForm();
        // A family sharing one phone is something the desk creates once, at
        // walk-in — not something to graft onto an existing record.
        if (el.membersFeature) el.membersFeature.hidden = true;
        fillForm(record);

        el.del.hidden = false;
        el.deleteHint.textContent = record.can_delete
            ? t('delete_window_hint', 'You can delete a walk-in you created within one hour of creating it.')
            : t('delete_window_expired_hint', 'The 1-hour self-service window for this record has passed.');
        el.del.disabled = !record.can_delete;

        if (record.audits && record.audits.length) {
            el.history.hidden = false;
            el.historyList.innerHTML = record.audits
                .flatMap((a) => Object.entries(a.changes || {}).map(([field, diff]) => historyLine(a, field, diff)))
                .join('');
        }

        openPanel();
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : value;
        return div.innerHTML;
    }

    const FIELD_LABEL_KEYS = {
        full_name: 'field_name',
        type: 'field_type',
        phone_country: 'field_phone_country',
        phone: 'field_phone',
    };

    function historyLine(audit, field, diff) {
        const fieldLabel = t(FIELD_LABEL_KEYS[field] || field, field);
        const line = t('history_by', ':user changed :field from ":old" to ":new"')
            .replace(':user', escapeHtml(audit.user || ''))
            .replace(':field', escapeHtml(fieldLabel))
            .replace(':old', escapeHtml(diff?.old ?? ''))
            .replace(':new', escapeHtml(diff?.new ?? ''));

        return `<p class="rg-history__row">${line}</p>`;
    }

    function collectPayload() {
        const form = el.panel.querySelector('[data-form]');
        const payload = {};
        fieldsOf(form).forEach((input) => {
            const key = input.dataset.field;
            if (input.value !== '') payload[key] = input.value;
        });

        const members = collectMembers();
        if (members.length) payload.members = members;

        return payload;
    }

    /* -------------------------------------------------------- validation -- */

    function clearFieldErrors() {
        const form = el.panel.querySelector('[data-form]');
        fieldsOf(form).forEach((input) => input.classList.remove('rg-input--error'));
        form.querySelectorAll('[data-member-field]').forEach((input) => input.classList.remove('rg-input--error'));
    }

    // Iraqi mobiles: ten digits starting with 7, an optional leading 0 — same
    // shape the server enforces. Every other country code keeps the old,
    // looser check; this desk has no business policing a UK or Turkish number.
    function phoneMatchesShape(value, countryCode) {
        const pattern = countryCode === '+964' ? /^0?7[0-9]{9}$/ : /^0?[0-9]{9,12}$/;
        return pattern.test(value.trim());
    }

    function validateForm() {
        const form = el.panel.querySelector('[data-form]');
        const invalid = [];
        let phoneInvalid = false;

        fieldsOf(form).forEach((input) => {
            if (!input.hasAttribute('data-required')) return;

            const missing = input.value.trim() === '';
            input.classList.toggle('rg-input--error', missing);
            if (missing) invalid.push(input);
        });

        if (el.membersContainer && !el.membersContainer.hidden) {
            form.querySelectorAll('[data-member-field="full_name"]').forEach((input) => {
                const missing = input.value.trim() === '';
                input.classList.toggle('rg-input--error', missing);
                if (missing) invalid.push(input);
            });
        }

        if (el.phoneInput && !invalid.includes(el.phoneInput) && el.phoneInput.value.trim() !== ''
            && !phoneMatchesShape(el.phoneInput.value, el.phoneCountrySelect.value)) {
            el.phoneInput.classList.add('rg-input--error');
            invalid.push(el.phoneInput);
            phoneInvalid = true;
        }

        return { invalid, phoneInvalid };
    }

    async function save() {
        const { invalid, phoneInvalid } = validateForm();
        if (invalid.length) {
            toast(
                phoneInvalid ? t('phone_invalid', 'Enter a valid phone number, e.g. 7XX XXX XXXX.') : t('fill_required', 'Please fill in the required fields.'),
                'error',
            );
            invalid[0].scrollIntoView?.({ block: 'center' });
            invalid[0].focus?.();
            return;
        }

        const payload = collectPayload();
        const url = currentId ? `${urls.base}/${currentId}` : urls.base;
        const method = currentId ? 'PUT' : 'POST';

        el.save.disabled = true;
        el.save.textContent = t('saving', 'Saving…');

        try {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const body = await response.json().catch(() => ({}));
                toast(body.message || 'Could not save.', 'error');
                return;
            }

            // A new walk-in with family members comes back as one registration
            // per person — everything else still returns (and expects) one.
            const created = currentId ? null : await response.json().catch(() => null);
            const createdCount = Array.isArray(created) ? created.length : 1;
            const createdMessage = createdCount > 1
                ? t('created_walk_ins', 'Accounts created. They still check in at the gate.')
                : t('created_walk_in', 'Account created. They still check in at the gate.');

            toast(currentId ? t('saved', 'Saved.') : createdMessage);
            closePanel();
            runSearch();
        } catch (e) {
            toast('Could not reach the server.', 'error');
        } finally {
            el.save.disabled = false;
            el.save.textContent = t('save', 'Save');
        }
    }
    el.save?.addEventListener('click', save);

    el.del?.addEventListener('click', async () => {
        if (!currentId || el.del.disabled) return;
        if (!window.confirm(t('delete_confirm', 'Delete this registration?'))) return;

        try {
            const response = await fetch(`${urls.base}/${currentId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            });

            if (!response.ok) {
                const body = await response.json().catch(() => ({}));
                toast(body.message || 'Could not delete.', 'error');
                return;
            }

            toast(t('deleted', 'Registration deleted.'));
            closePanel();
            runSearch();
        } catch (e) {
            toast('Could not reach the server.', 'error');
        }
    });

    runSearch();
}
