/*
 * Collector PWA service worker.
 *
 * Scope: /collector only (registered from the collector shell). The Admin and
 * Borrower surfaces are intentionally NOT offline-enabled.
 *
 * Strategy:
 *   - Vite-built assets (/build/*) are content-hashed and immutable, so they
 *     are served cache-first. A new deploy ships new filenames, which miss the
 *     cache and are fetched + cached transparently — no manual cache clearing.
 *   - Collector page navigations are network-first with a cache fallback, so an
 *     online open always renders the freshest server HTML (and refreshes the
 *     cached copy), while an offline open falls back to the last cached shell.
 *   - The route JSON API is network-first with cache fallback as a backstop;
 *     the authoritative offline copy lives in IndexedDB (see collector JS).
 *   - POSTs (payment sync) are never intercepted/cached — the app-level queue
 *     owns offline payment durability.
 *
 * Bump CACHE_VERSION to force-evict old caches on the next activation.
 */
const CACHE_VERSION = 'v1';
const STATIC_CACHE = `collector-static-${CACHE_VERSION}`;
const PAGE_CACHE = `collector-pages-${CACHE_VERSION}`;
const DATA_CACHE = `collector-data-${CACHE_VERSION}`;

const PRECACHE_URLS = [
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    const keep = new Set([STATIC_CACHE, PAGE_CACHE, DATA_CACHE]);
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k.startsWith('collector-') && !keep.has(k))
                    .map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

function isAsset(url) {
    return url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/icons/')
        || url.pathname === '/manifest.webmanifest';
}

function isRouteApi(url) {
    return url.pathname === '/collector/api/route';
}

function isCollectorNavigation(request, url) {
    return request.mode === 'navigate' && url.pathname.startsWith('/collector');
}

async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    if (cached) {
        return cached;
    }
    const response = await fetch(request);
    if (response && response.ok) {
        cache.put(request, response.clone());
    }
    return response;
}

async function networkFirst(request, cacheName, fallbackUrl) {
    const cache = await caches.open(cacheName);
    try {
        const response = await fetch(request);
        if (response && response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }
        if (fallbackUrl) {
            const fallback = await cache.match(fallbackUrl);
            if (fallback) {
                return fallback;
            }
        }
        throw err;
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return; // payment POSTs and other writes bypass the SW entirely
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    if (isAsset(url)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    if (isRouteApi(url)) {
        event.respondWith(networkFirst(request, DATA_CACHE));
        return;
    }

    if (isCollectorNavigation(request, url)) {
        event.respondWith(networkFirst(request, PAGE_CACHE, '/collector/route'));
        return;
    }
});

// Allow the page to trigger an immediate activation after an update.
self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
