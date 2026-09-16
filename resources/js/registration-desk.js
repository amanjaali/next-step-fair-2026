/**
 * Registration desk.
 *
 * Browse the pre-registered list, create a walk-in (auto checked-in for today
 * server-side), edit any record, and — only for the volunteer who created it,
 * only within an hour — delete it.
 */
const shell = document.querySelector('.rg-shell');
if (shell) {
    const urls = {
        search: shell.dataset.searchUrl,
        base: shell.dataset.storeUrl,
    };
    const csrf = shell.dataset.csrf;
    const cities = (shell.dataset.cities || '').split(',').filter(Boolean);
    const phoneCountries = (shell.dataset.phoneCountries || '').split(',').filter(Boolean);
    const defaultPhoneCountry = shell.dataset.defaultPhoneCountry || phoneCountries[0] || '';

    const t = (key, fallback) => shell.dataset[`label${key.replace(/(^|_)([a-z])/g, (m, sep, c) => c.toUpperCase())}`] || fallback;

    const el = {
        searchInput: shell.querySelector('[data-search-input]'),
        tabs: shell.querySelector('[data-filter-tabs]'),
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
        citySelect: shell.querySelector('[data-field="city"]'),
        phoneCountrySelect: shell.querySelector('[data-field="phone_country"]'),
        typeSelect: shell.querySelector('[data-field="type"]'),
        localeSelect: shell.querySelector('[data-field="locale"]'),
        schoolWrap: shell.querySelector('[data-field-wrap="school_name"]'),
        relationshipWrap: shell.querySelector('[data-field-wrap="relationship"]'),
        daysField: shell.querySelector('[data-days-field]'),
    };

    cities.forEach((city) => {
        const opt = document.createElement('option');
        opt.value = city;
        opt.textContent = city;
        el.citySelect.appendChild(opt);
    });
    phoneCountries.forEach((code) => {
        const opt = document.createElement('option');
        opt.value = code;
        opt.textContent = code;
        el.phoneCountrySelect.appendChild(opt);
    });
    el.phoneCountrySelect.value = defaultPhoneCountry;

    let currentId = null;
    let filterType = 'all';
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
                    <span class="rg-hit__detail">${[r.type ? escapeHtml(r.type.toUpperCase()) : '', escapeHtml(r.city || ''), escapeHtml(r.phone || '')].filter(Boolean).join(' · ')}</span>
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
        if (filterType !== 'all') params.set('type', filterType);

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
        if (filterType !== 'all') params.set('type', filterType);
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

    el.tabs?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-filter]');
        if (!button) return;
        filterType = button.dataset.filter;
        el.tabs.querySelectorAll('.rg-tab').forEach((tab) => tab.classList.toggle('rg-tab--active', tab === button));
        runSearch();
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

    function toggleTypeFields() {
        const isStudent = el.typeSelect.value === 'student';
        el.schoolWrap.hidden = !isStudent;
        el.relationshipWrap.hidden = isStudent;
        // Clear whichever field just went out of view so a stale value from the
        // other type can't ride along in the next save.
        if (!isStudent) el.schoolWrap.querySelector('[data-field]').value = '';
        if (isStudent) el.relationshipWrap.querySelector('[data-field]').value = '';
    }
    el.typeSelect?.addEventListener('change', toggleTypeFields);

    // Clear a field's red outline as soon as the volunteer fixes it.
    el.panel.querySelector('[data-form]')?.addEventListener('input', (event) => {
        const field = event.target.closest('[data-required]');
        if (field && field.value.trim() !== '') field.classList.remove('rg-input--error');
    });
    el.panel.querySelector('[data-form]')?.addEventListener('change', (event) => {
        const field = event.target.closest('[data-required]');
        if (field && field.value.trim() !== '') field.classList.remove('rg-input--error');
    });
    shell.querySelectorAll('[data-day]').forEach((box) => {
        box.addEventListener('change', () => {
            if (shell.querySelectorAll('[data-day]:checked').length > 0) {
                el.daysField.classList.remove('rg-field--error');
            }
        });
    });

    function resetForm() {
        const form = el.panel.querySelector('[data-form]');
        fieldsOf(form).forEach((input) => { input.value = ''; });
        el.phoneCountrySelect.value = defaultPhoneCountry;
        el.typeSelect.value = 'student';
        el.localeSelect.value = 'ku';
        toggleTypeFields();
        shell.querySelectorAll('[data-day]').forEach((box) => { box.checked = false; });
        clearFieldErrors();
        el.del.hidden = true;
        el.deleteHint.textContent = '';
        el.history.hidden = true;
        el.historyList.innerHTML = '';
    }

    function fillForm(record) {
        const form = el.panel.querySelector('[data-form]');
        fieldsOf(form).forEach((input) => {
            const key = input.dataset.field;
            if (key in record && record[key] !== null) input.value = record[key];
        });
        if (record.phone_country) el.phoneCountrySelect.value = record.phone_country;
        toggleTypeFields();
        shell.querySelectorAll('[data-day]').forEach((box) => {
            box.checked = (record.days || []).includes(parseInt(box.value, 10));
        });
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
        fillForm(record);

        el.del.hidden = false;
        el.deleteHint.textContent = record.can_delete
            ? t('delete_window_hint', 'You can delete a walk-in you created within one hour of creating it.')
            : t('delete_window_expired_hint', 'The 1-hour self-service window for this record has passed.');
        el.del.disabled = !record.can_delete;

        if (record.audits && record.audits.length) {
            el.history.hidden = false;
            el.historyList.innerHTML = record.audits
                .map((a) => `<p class="rg-history__row">${escapeHtml(JSON.stringify(a.changes))} — ${escapeHtml(a.user || '')}</p>`)
                .join('');
        }

        openPanel();
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : value;
        return div.innerHTML;
    }

    function collectPayload() {
        const form = el.panel.querySelector('[data-form]');
        const payload = {};
        fieldsOf(form).forEach((input) => {
            const key = input.dataset.field;
            if (input.value !== '') payload[key] = input.value;
        });
        payload.days = Array.from(shell.querySelectorAll('[data-day]:checked')).map((box) => parseInt(box.value, 10));
        return payload;
    }

    /* -------------------------------------------------------- validation -- */

    function clearFieldErrors() {
        const form = el.panel.querySelector('[data-form]');
        fieldsOf(form).forEach((input) => input.classList.remove('rg-input--error'));
        el.daysField.classList.remove('rg-field--error');
    }

    // Every field is required except e-mail and notes. School/relationship only
    // count while their type-dependent section is actually showing.
    function validateForm() {
        const form = el.panel.querySelector('[data-form]');
        const invalid = [];

        fieldsOf(form).forEach((input) => {
            if (!input.hasAttribute('data-required')) return;
            const wrap = input.closest('[data-field-wrap]');
            if (wrap && wrap.hidden) return;

            const missing = input.value.trim() === '';
            input.classList.toggle('rg-input--error', missing);
            if (missing) invalid.push(input);
        });

        const anyDayChecked = shell.querySelectorAll('[data-day]:checked').length > 0;
        el.daysField.classList.toggle('rg-field--error', !anyDayChecked);
        if (!anyDayChecked) invalid.push(el.daysField);

        return invalid;
    }

    async function save() {
        const invalid = validateForm();
        if (invalid.length) {
            toast(t('fill_required', 'Please fill in the required fields.'), 'error');
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

            toast(currentId ? t('saved', 'Saved.') : t('created_walk_in', 'Walk-in created and checked in for today.'));
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

    toggleTypeFields();
    runSearch();
}
