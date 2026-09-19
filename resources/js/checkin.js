import { Html5Qrcode } from 'html5-qrcode';

/**
 * Gate scanner.
 *
 * Day and gate are chosen once on this device, kept in localStorage, and only
 * changed from Settings. Online scans post to the server; offline scans queue
 * locally against a cached ticket list until the connection returns.
 */
const shell = document.querySelector('.ck-shell');
if (shell) {
    const urls = {
        scan: shell.dataset.scanUrl,
        search: shell.dataset.searchUrl,
        sync: shell.dataset.syncUrl,
        manifest: shell.dataset.manifestUrl,
        manual: shell.dataset.manualUrl,
    };
    const csrf = shell.dataset.csrf;

    const QUEUE_KEY = 'ns.checkin.queue';
    const TICKETS_KEY = 'ns.checkin.tickets';
    const DEVICE_KEY = 'ns.checkin.device';
    const SETTINGS_KEY = 'ns.checkin.settings';

    const el = {
        setup: shell.querySelector('[data-setup]'),
        workspace: shell.querySelector('[data-workspace]'),
        settings: shell.querySelector('[data-settings]'),
        setupDay: shell.querySelector('[data-setup-day]'),
        setupGate: shell.querySelector('[data-setup-gate]'),
        settingsDay: shell.querySelector('[data-settings-day]'),
        settingsGate: shell.querySelector('[data-settings-gate]'),
        gateLabel: shell.querySelector('[data-gate-label]'),
        result: shell.querySelector('[data-result]'),
        status: shell.querySelector('[data-result-status]'),
        title: shell.querySelector('[data-result-title]'),
        detail: shell.querySelector('[data-result-detail]'),
        sync: shell.querySelector('[data-sync-state]'),
        count: shell.querySelector('[data-count]'),
        hint: shell.querySelector('[data-camera-hint]'),
        search: shell.querySelector('[data-search]'),
        searchInput: shell.querySelector('[data-search-input]'),
        searchResults: shell.querySelector('[data-search-results]'),
    };

    const deviceId = (() => {
        let id = localStorage.getItem(DEVICE_KEY);
        if (!id) {
            id = 'dev-' + Math.random().toString(36).slice(2, 10);
            localStorage.setItem(DEVICE_KEY, id);
        }
        return id;
    })();

    const readJson = (key, fallback) => {
        try {
            return JSON.parse(localStorage.getItem(key)) ?? fallback;
        } catch (e) {
            return fallback;
        }
    };
    const writeJson = (key, value) => {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {}
    };

    const suggestedDay = parseInt(shell.dataset.suggestedDay, 10) || 1;
    const suggestedGate = shell.dataset.suggestedGate || 'A';

    let day = suggestedDay;
    let gate = suggestedGate;
    let cameraStarted = false;
    let paused = false;
    let reader = null;
    let clearTimer = null;
    let lastToken = null;
    let lastTokenAt = 0;

    /* ------------------------------------------------------------ settings -- */

    function readSettings() {
        const stored = readJson(SETTINGS_KEY, null);
        if (!stored || !stored.day || !stored.gate) return null;
        return {
            day: parseInt(stored.day, 10) || suggestedDay,
            gate: String(stored.gate),
        };
    }

    function writeSettings(next) {
        day = next.day;
        gate = next.gate;
        writeJson(SETTINGS_KEY, { day, gate });
        updateGateLabel();
    }

    function updateGateLabel() {
        if (!el.gateLabel) return;
        const template = shell.dataset.labelGate || 'Gate :gate · Day :day';
        el.gateLabel.textContent = template.replace(':gate', gate).replace(':day', String(day));
    }

    function fillSelects(daySelect, gateSelect) {
        if (daySelect) daySelect.value = String(day);
        if (gateSelect) gateSelect.value = gate;
    }

    function showSetup() {
        el.setup.hidden = false;
        el.workspace.hidden = true;
        el.settings.hidden = true;
        fillSelects(el.setupDay, el.setupGate);
    }

    function showWorkspace() {
        el.setup.hidden = true;
        el.workspace.hidden = false;
        el.settings.hidden = true;
        updateGateLabel();
        startCamera();
        updateSyncLabel();
        refreshTickets();
        flushQueue();
    }

    function openSettings() {
        fillSelects(el.settingsDay, el.settingsGate);
        el.settings.hidden = false;
        paused = true;
    }

    function closeSettings() {
        el.settings.hidden = true;
        paused = false;
    }

    /* ------------------------------------------------------------ display -- */

    const STATE_LABEL = {
        valid: shell.dataset.labelValid,
        valid_conference: shell.dataset.labelValidConference,
        already: shell.dataset.labelAlready,
        invalid: shell.dataset.labelInvalid,
        cancelled: shell.dataset.labelCancelled,
        wrong_day: shell.dataset.labelWrongDay,
        pending: shell.dataset.labelPending,
    };

    function show(result) {
        paused = true;
        el.result.hidden = false;
        el.result.dataset.state = result.state;
        el.status.textContent = result.status || STATE_LABEL[result.state] || result.state;
        el.title.textContent = result.title || '';
        el.detail.textContent = result.detail || '';

        if (navigator.vibrate) {
            navigator.vibrate(result.state.startsWith('valid') ? 60 : [40, 60, 40]);
        }

        if (result.state.startsWith('valid') && el.count) {
            el.count.textContent = String(parseInt(el.count.textContent, 10) + 1);
        }

        // Auto-resume so the next badge can be scanned without a tap.
        // Valid: short flash. Amber/red: a bit longer so staff can read it.
        const delay = result.state.startsWith('valid') ? 1400 : 2200;
        clearTimeout(clearTimer);
        clearTimer = setTimeout(clearResult, delay);
    }

    function clearResult() {
        if (!el.settings.hidden) return;
        clearTimeout(clearTimer);
        paused = false;
        el.result.hidden = true;
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : value;
        return div.innerHTML;
    }

    /* ------------------------------------------------------------- offline -- */

    function offlineVerdict(token) {
        const tickets = readJson(TICKETS_KEY, { tickets: [] }).tickets || [];
        const id = token.includes('/verify/') ? token.split('/verify/')[1].split('?')[0] : token;
        const hit = tickets.find((t) => t.t === id);

        if (!hit) {
            return { state: 'invalid', title: '—', detail: shell.dataset.labelInvalidDetail || '' };
        }

        if (hit.d && hit.d.length && !hit.d.includes(day)) {
            return { state: 'wrong_day', title: hit.n, detail: hit.o };
        }

        return { state: 'valid', title: hit.n, detail: [hit.y, hit.o].filter(Boolean).join(' · ') };
    }

    function queueScan(token) {
        const queue = readJson(QUEUE_KEY, []);
        queue.push({
            ticket: token,
            day,
            gate,
            scanned_at: new Date().toISOString(),
            device_id: deviceId,
        });
        writeJson(QUEUE_KEY, queue);
        updateSyncLabel();
    }

    function updateSyncLabel() {
        const queue = readJson(QUEUE_KEY, []);
        if (!el.sync) return;
        el.sync.textContent = queue.length
            ? (shell.dataset.labelOffline || 'Offline').replace(':count', queue.length)
            : shell.dataset.labelSynced || 'Synced';
        el.sync.dataset.pending = queue.length ? 'true' : 'false';
    }

    async function flushQueue() {
        const queue = readJson(QUEUE_KEY, []);
        if (!queue.length || !navigator.onLine) return;

        try {
            const response = await fetch(urls.sync, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ scans: queue, gate }),
            });
            if (response.ok) {
                writeJson(QUEUE_KEY, []);
                updateSyncLabel();
            }
        } catch (e) {
            /* still offline — keep the queue */
        }
    }

    async function refreshTickets() {
        if (!navigator.onLine) return;
        try {
            const response = await fetch(urls.manifest, { headers: { Accept: 'application/json' } });
            if (response.ok) writeJson(TICKETS_KEY, await response.json());
        } catch (e) {}
    }

    /* ---------------------------------------------------------------- scan -- */

    async function handleToken(token) {
        if (paused || el.workspace.hidden) return;

        // Same QR still in frame after auto-resume — ignore briefly.
        const now = Date.now();
        if (token === lastToken && now - lastTokenAt < 3500) return;
        lastToken = token;
        lastTokenAt = now;

        if (!navigator.onLine) {
            queueScan(token);
            show(offlineVerdict(token));
            return;
        }

        try {
            const response = await fetch(urls.scan, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ ticket: token, day, gate, device_id: deviceId }),
            });
            show(await response.json());
        } catch (e) {
            queueScan(token);
            show(offlineVerdict(token));
        }
    }

    function startCamera() {
        if (cameraStarted) return;
        cameraStarted = true;

        reader = new Html5Qrcode('ck-reader', { verbose: false });
        reader
            .start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                (decoded) => handleToken(decoded),
                () => {}
            )
            .then(() => {
                if (el.hint) el.hint.hidden = true;
            })
            .catch(() => {
                if (el.hint) el.hint.textContent = shell.dataset.labelCameraDenied || '';
            });
    }

    // Tap the result to dismiss early; otherwise it clears on its own.
    el.result?.addEventListener('click', clearResult);

    shell.querySelector('[data-setup-save]')?.addEventListener('click', () => {
        writeSettings({
            day: parseInt(el.setupDay.value, 10) || suggestedDay,
            gate: el.setupGate.value || suggestedGate,
        });
        showWorkspace();
    });

    shell.querySelector('[data-open-settings]')?.addEventListener('click', openSettings);

    shell.querySelector('[data-settings-cancel]')?.addEventListener('click', closeSettings);

    shell.querySelector('[data-settings-save]')?.addEventListener('click', () => {
        writeSettings({
            day: parseInt(el.settingsDay.value, 10) || day,
            gate: el.settingsGate.value || gate,
        });
        closeSettings();
    });

    /* -------------------------------------------------------------- search -- */

    shell.querySelector('[data-open-search]')?.addEventListener('click', () => {
        el.search.hidden = !el.search.hidden;
        if (!el.search.hidden) el.searchInput.focus();
    });

    let searchTimer;
    el.searchInput?.addEventListener('input', (event) => {
        clearTimeout(searchTimer);
        const term = event.target.value;
        searchTimer = setTimeout(async () => {
            if (term.length < 3) {
                el.searchResults.innerHTML = '';
                return;
            }
            const response = await fetch(`${urls.search}?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json' },
            });
            const { results } = await response.json();

            el.searchResults.innerHTML = results.length
                ? results
                      .map(
                          (r) => `<button type="button" class="ck-hit" data-id="${r.id}">
                                    <span class="ck-hit__info">
                                        <span class="ck-hit__name">${escapeHtml(r.name)}</span>
                                        <span class="ck-hit__detail">${escapeHtml(r.detail)}</span>
                                        <span class="ck-hit__ticket">${escapeHtml(r.ticket)}</span>
                                    </span>
                                    <span class="ck-hit__action">${shell.dataset.labelCheckIn || 'Check in'}</span>
                                  </button>`
                      )
                      .join('')
                : `<p class="ck-hit__empty">${shell.dataset.labelNoResults || ''}</p>`;
        }, 250);
    });

    el.searchResults?.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-id]');
        if (!button || button.disabled) return;

        // A family shares one phone number, so a single search can hold several
        // people. Closing it after the first would make the volunteer type that
        // number again for every other member.
        const family = el.searchResults.querySelectorAll('[data-id]').length > 1;

        button.disabled = true;

        let result;
        try {
            const response = await fetch(`${urls.manual}/${button.dataset.id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ day, gate }),
            });
            result = await response.json();
        } catch (error) {
            // A dropped connection must not leave a row that can never be tapped again.
            button.disabled = false;
            return;
        }

        show(result);

        if (!family) {
            el.search.hidden = true;
            el.searchInput.value = '';
            el.searchResults.innerHTML = '';
            return;
        }

        // Whoever just went through stays on the list, greyed out: the next
        // member is one tap away, and nobody gets checked in twice.
        if (result.state.startsWith('valid')) {
            button.classList.add('ck-hit--done');
            const action = button.querySelector('.ck-hit__action');
            if (action) action.textContent = STATE_LABEL.already;
        } else {
            button.disabled = false;
        }
    });

    /* ------------------------------------------------------------ lifecycle -- */

    window.addEventListener('online', () => {
        flushQueue();
        refreshTickets();
    });
    window.addEventListener('offline', updateSyncLabel);

    const stored = readSettings();
    if (stored) {
        day = stored.day;
        gate = stored.gate;
        showWorkspace();
    } else {
        if (el.setupDay) el.setupDay.value = String(suggestedDay);
        if (el.setupGate) el.setupGate.value = suggestedGate;
        showSetup();
    }

    setInterval(flushQueue, 30000);
}
