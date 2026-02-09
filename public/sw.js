// Service Worker minimal
self.addEventListener('install', e => {
  console.log('ALMA Suite installé');
});

self.addEventListener('fetch', e => {
  e.respondWith(fetch(e.request));
});
