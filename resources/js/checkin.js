import { Html5Qrcode } from 'html5-qrcode';

/**
 * Gate scanner.
 *
 * Online it posts each scan and shows the server's verdict. Offline it validates
 * against the cached ticket list, shows the same verdict, and queues the scan in
 * localStorage until the connection returns.
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
    const day = parseInt(shell.dataset.day, 10) || 1;

    const QUEUE_KEY = 'ns.checkin.queue';
    const TICKETS_KEY = 'ns.checkin.tickets';
    const DEVICE_KEY = 'ns.checkin.device';

    const el = {
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

    let paused = false;

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
    }

    function clearResult() {
        paused = false;
        el.result.hidden = true;
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
        queue.push({ ticket: token, day, scanned_at: new Date().toISOString(), device_id: deviceId });
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
                body: JSON.stringify({ scans: queue }),
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
        if (paused) return;

        if (!navigator.onLine) {
            queueScan(token);
            show(offlineVerdict(token));
            return;
        }

        try {
            const response = await fetch(urls.scan, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ ticket: token, day, device_id: deviceId }),
            });
            show(await response.json());
        } catch (e) {
            queueScan(token);
            show(offlineVerdict(token));
        }
    }

    const reader = new Html5Qrcode('ck-reader', { verbose: false });

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

    shell.querySelector('[data-rescan]')?.addEventListener('click', clearResult);
    el.result?.addEventListener('click', clearResult);

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
                                    <span class="ck-hit__name">${r.name}</span>
                                    <span class="ck-hit__detail">${r.detail}</span>
                                    <span class="ck-hit__ticket">${r.ticket}</span>
                                  </button>`
                      )
                      .join('')
                : `<p class="ck-hit__empty">${shell.dataset.labelNoResults || ''}</p>`;
        }, 250);
    });

    el.searchResults?.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-id]');
        if (!button) return;

        const response = await fetch(`${urls.manual}/${button.dataset.id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify({ day }),
        });
        show(await response.json());
        el.search.hidden = true;
        el.searchInput.value = '';
        el.searchResults.innerHTML = '';
    });

    /* ------------------------------------------------------------ lifecycle -- */

    window.addEventListener('online', () => {
        flushQueue();
        refreshTickets();
    });
    window.addEventListener('offline', updateSyncLabel);

    updateSyncLabel();
    refreshTickets();
    flushQueue();
    setInterval(flushQueue, 30000);
}
