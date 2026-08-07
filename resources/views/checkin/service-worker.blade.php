/* Next Step check-in — offline shell.
   Caches the scanner UI and its assets so gate staff can keep scanning when the
   venue Wi-Fi drops. Scan results are queued in localStorage by the page itself. */
const CACHE = 'ns-checkin-v1';
const SHELL = [
    '{{ route('checkin.index') }}',
    '{{ Vite::asset('resources/css/checkin.css') }}',
    '{{ Vite::asset('resources/js/checkin.js') }}',
    '{{ asset('assets/brand/nextstep-white-sm.png') }}',
];

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
                if (response.ok && request.url.startsWith(self.location.origin)) {
                    const copy = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(request, copy));
                }
                return response;
            })
            .catch(() => caches.match(request).then((hit) => hit || caches.match('{{ route('checkin.index') }}')))
    );
});
