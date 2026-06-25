const CACHE = 'qrmap-v1';
const OFFLINE_URL = '/offline';

const PRECACHE = [
    '/',
    '/offline',
    'https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js',
];

// Installation — pré-cache
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE).then(cache => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

// Activation — nettoyage des anciens caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Fetch — stratégie Network-first pour les scans, Cache-first pour les assets
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Pages de scan : network first, fallback cache
    if (url.pathname.startsWith('/scan/')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    const clone = response.clone();
                    caches.open(CACHE).then(cache => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request).then(r => r || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // Assets statiques (JS, CSS, fonts) : cache first
    if (url.pathname.match(/\.(js|css|png|jpg|svg|woff2?)$/) || url.hostname !== location.hostname) {
        event.respondWith(
            caches.match(event.request).then(cached => cached || fetch(event.request).then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE).then(cache => cache.put(event.request, clone));
                }
                return response;
            }))
        );
        return;
    }

    // Autres : network first
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});

// Notification push (optionnel)
self.addEventListener('push', event => {
    const data = event.data?.json() ?? { title: 'QRMap', body: 'Nouveau scan détecté' };
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-192.png',
            tag: 'qrmap-scan',
        })
    );
});