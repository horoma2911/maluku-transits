const CACHE_NAME = 'maluku-logistics-v1';
const urlsToCache = [
  '/css/main.css',
  '/js/theme.js',
  '/js/utils.js',
  '/js/layout.js',
  '/js/charts.js',
  '/js/app.js',
  '/js/data.js',
  '/pages/login.html',
  '/pages/dashboard.html',
  '/pwa/offline.html'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(urlsToCache))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => response || fetch(event.request).catch(() => caches.match('/pwa/offline.html')))
  );
});
