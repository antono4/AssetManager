// Service Worker - AssetManager PWA
const CACHE_NAME = 'assetmanager-v1';
const ASSETS = [
    '/dashboard',
    '/assets',
    '/login',
];

self.addEventListener('install', function(e) {
    e.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll(ASSETS).catch(function() {});
        })
    );
    self.skipWaiting();
});

self.addEventListener('fetch', function(e) {
    // Only cache GET requests, skip API & uploads
    if (e.request.method !== 'GET' || e.request.url.includes('/api/') || e.request.url.includes('/uploads/')) {
        return;
    }
    e.respondWith(
        caches.match(e.request).then(function(cached) {
            return cached || fetch(e.request).then(function(resp) {
                // Don't cache non-OK or POST responses
                if (!resp || resp.status !== 200 || resp.type !== 'basic') {
                    return resp;
                }
                const clone = resp.clone();
                caches.open(CACHE_NAME).then(function(cache) {
                    cache.put(e.request, clone);
                });
                return resp;
            }).catch(function() {
                return cached;
            });
        })
    );
});

self.addEventListener('activate', function(e) {
    e.waitUntil(
        caches.keys().then(function(names) {
            return Promise.all(
                names.filter(n => n !== CACHE_NAME).map(n => caches.delete(n))
            );
        })
    );
    self.clients.claim();
});
