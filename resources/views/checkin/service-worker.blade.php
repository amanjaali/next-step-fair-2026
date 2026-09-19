/* Next Step check-in — offline shell.
   Caches the scanner UI and its assets so gate staff can keep scanning when the
   venue Wi-Fi drops. Scan results are queued in localStorage by the page itself. */
const CACHE = 'ns-checkin-v2';
const INDEX = '{{ route('checkin.index') }}';
const INDEX_PATH = new URL(INDEX).pathname;
/*
 * Assets only. The scanner page itself is stored on the way past, by the fetch
 * handler below, which can tell a real scanner from a redirect to the login
 * form — installing it here cannot, and a worker that installs seconds after a
 * session lapses would pin that login form in place of the scanner.
 */
const SHELL = [
    '{{ Vite::asset('resources/css/checkin.css') }}',
    '{{ Vite::asset('resources/js/checkin.js') }}',
    '{{ asset('assets/brand/nextstep-white-sm.png') }}',
];

/*
 * Only the scanner page itself is worth storing.
 *
 * A signed-out request for /checkin answers with a redirect to the login page,
 * and caching that under the scanner's own address is what put staff in front
 * of a login screen — carrying a dead CSRF token, so the password looked wrong
 * — every time the venue Wi-Fi dropped.
 */
function isCacheable(request, response) {
    if (!response.ok || response.redirected || !request.url.startsWith(self.location.origin)) {
        return false;
    }

    return request.mode !== 'navigate' || new URL(request.url).pathname === INDEX_PATH;
}

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Never cache scans or syncs: they must reach the server or be queued.
    if (request.method !== 'GET' || request.url.includes('/checkin/scan') || request.url.includes('/checkin/sync')) {
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                if (isCacheable(request, response)) {
                    const copy = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(request, copy));
                }
                return response;
            })
            .catch(() => caches.match(request).then((hit) => hit || caches.match(INDEX)))
    );
});
