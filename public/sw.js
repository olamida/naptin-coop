const CACHE_NAME = 'naptin-coop-v5';
const STATIC_CACHE = 'naptin-coop-static-v5';

const PRECACHE_URLS = [
    'offline.html',
];

const STATIC_EXTENSIONS = [
    '.css', '.js', '.woff', '.woff2', '.ttf', '.png', '.svg', '.ico', '.jpg', '.jpeg', '.webp',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_URLS);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME && name !== STATIC_CACHE)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Cache static assets on first fetch
    if (STATIC_EXTENSIONS.some((ext) => url.pathname.endsWith(ext))) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) return cached;
                return fetch(event.request).then((response) => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(STATIC_CACHE).then((cache) => cache.put(event.request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // API and document requests: network-first with offline fallback
    event.respondWith(
        fetch(event.request).then((response) => {
            if (response && response.status === 200) {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
            }
            return response;
        }).catch(() => {
            return caches.match(event.request).then((cached) => {
                if (cached) return cached;
                if (event.request.destination === 'document') {
                    return caches.match('offline.html');
                }
                return new Response('', { status: 503, statusText: 'Service Unavailable' });
            });
        })
    );
});

// Listen for skip-waiting message from the client
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
