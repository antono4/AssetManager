// Service Worker - AssetManager PWA
const CACHE_NAME = 'assetmanager-v2';
const ASSETS_TO_CACHE = [
    '/dashboard',
    '/assets',
];

// Jangan pernah intercept halaman ini (biarkan langsung ke network)
const EXCLUDE_PATHS = ['/login', '/logout', '/setup', '/api/', '/language/', '/dark-mode', '/search', '/assets/export', '/assets/csv-template', '/assets/import', '/assets/trash', '/notifications', '/audit', '/api-tokens', '/borrowings'];

self.addEventListener('install', function(e) {
    e.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll(ASSETS_TO_CACHE).catch(function() {});
        })
    );
    self.skipWaiting();
});

self.addEventListener('fetch', function(e) {
    // Only cache GET requests
    if (e.request.method !== 'GET') {
        return;
    }
    var url = new URL(e.request.url);
    var path = url.pathname;

    // Skip excluded paths, uploads, dan non-HTML (CSS/JS/API di-serve langsung)
    for (var i = 0; i < EXCLUDE_PATHS.length; i++) {
        if (path.indexOf(EXCLUDE_PATHS[i]) === 0) return;
    }
    if (path.indexOf('/uploads/') === 0) return;
    // Hanya cache navigasi HTML (document), bukan CSS/JS/image (sudah CDN)
    if (e.request.mode !== 'navigate') return;

    e.respondWith(
        fetch(e.request).then(function(resp) {
            if (!resp || resp.status !== 200 || resp.type !== 'basic') {
                return resp;
            }
            var clone = resp.clone();
            caches.open(CACHE_NAME).then(function(cache) {
                cache.put(e.request, clone);
            });
            return resp;
        }).catch(function() {
            return caches.match(e.request).then(function(cached) {
                return cached || new Response('Offline', { status: 503 });
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
