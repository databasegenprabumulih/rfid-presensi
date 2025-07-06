const CACHE_NAME = 'presensi-cache-v1';
const urlsToCache = [
  '/',
  '/form_presensi.php',
  '/simpan_presensi.php',
  '/style.css',
  '/index.php',
  // Tambahkan file lain yang penting untuk offline di sini
];

// Saat service worker diinstall, cache file-file ini
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(urlsToCache);
    })
  );
});

// Saat service worker aktif, hapus cache lama jika ada
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      )
    )
  );
});

// Intercept request dan gunakan cache jika ada, fallback ke network jika tidak
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request);
    })
  );
});
