const CACHE_NAME = 'famespace-cache-v3';
const PRECACHE_ASSETS = [
  '/manifest.json',
  '/offline',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png',
  '/icons/icon-maskable-512x512.png',
  '/icons/apple-touch-icon.png',
  '/vendor/tailwindcss/tailwind.js',
  '/vendor/fonts/fonts.css',
  '/vendor/jquery/jquery-3.7.1.min.js'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_ASSETS))
  );
  self.skipWaiting();
});

// Activate Event
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

// Fetch Event
self.addEventListener('fetch', (event) => {
  // Hanya proses request GET
  if (event.request.method !== 'GET') return;

  // Abaikan skema selain http / https (misal chrome-extension://)
  if (!event.request.url.startsWith('http://') && !event.request.url.startsWith('https://')) return;

  const url = new URL(event.request.url);

  // Jika request ke assets static (icons, vendor, build, manifest)
  if (url.pathname.startsWith('/icons/') || url.pathname.startsWith('/vendor/') || url.pathname.startsWith('/build/') || url.pathname === '/manifest.json') {
    event.respondWith(
      caches.match(event.request).then((cached) => cached || fetch(event.request))
    );
    return;
  }

  // Network-first untuk halaman web dinamis
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        if (response && response.status === 200) {
          const cloned = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            // Simpan cache halaman yang berhasil dimuat
            cache.put(event.request, cloned);
          });
        }
        return response;
      })
      .catch(async () => {
        const cached = await caches.match(event.request);
        if (cached) return cached;

        // Fallback HTML jika offline
        if (event.request.headers.get('accept')?.includes('text/html')) {
          const offlinePage = await caches.match('/offline');
          if (offlinePage) return offlinePage;

          return new Response(
            `<!DOCTYPE html>
            <html lang="id">
            <head>
              <meta charset="UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <title>Offline — Famespace</title>
              <style>
                body {
                  margin: 0; padding: 2rem 1.5rem; background: #FBF8EF; color: #121614;
                  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                  display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 85vh; text-align: center;
                }
                .card {
                  background: white; border: 2.5px solid #121614; box-shadow: 4px 4px 0px #121614;
                  border-radius: 16px; padding: 2rem 1.5rem; max-width: 400px; width: 100%;
                }
                .emoji { font-size: 3.5rem; margin-bottom: 1rem; }
                h1 { margin: 0 0 0.5rem; font-size: 1.35rem; font-weight: 900; }
                p { margin: 0 0 1.5rem; color: #555; font-size: 0.95rem; line-height: 1.4; }
                button {
                  background: #2F5233; color: white; border: 2.5px solid #121614; box-shadow: 2.5px 2.5px 0px #121614;
                  padding: 0.75rem 1.5rem; font-size: 0.95rem; font-weight: 800; border-radius: 10px; cursor: pointer;
                }
                button:active { transform: translate(1px, 1px); box-shadow: 1px 1px 0px #121614; }
              </style>
            </head>
            <body>
              <div class="card">
                <div class="emoji">📡🌿</div>
                <h1>Koneksi Sedang Terputus</h1>
                <p>Aplikasi Famespace tidak dapat terhubung ke internet. Coba periksa koneksi Wi-Fi atau data seluler kamu.</p>
                <button onclick="window.location.reload()">Coba Muat Ulang</button>
              </div>
            </body>
            </html>`,
            {
              headers: { 'Content-Type': 'text/html; charset=utf-8' }
            }
          );
        }
      })
  );
});
