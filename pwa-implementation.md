# Panduan Implementasi PWA & Mobile-First Interface: Famespace

Dokumen ini merupakan panduan teknis lengkap untuk mengimplementasikan **Progressive Web App (PWA)** pada aplikasi **Famespace** (Laravel + Blade + Tailwind CSS), dengan diferensiasi antarmuka penuh antara **Desktop Web** (Dashboard Neobrutalist dengan Sidebar) dan **Mobile App** (Bottom Nav, Top App Bar, FAB, & Bottom Sheet).

---

## 1. Struktur Folder & File Target

Tambahkan dan sesuaikan file-file berikut pada direktori proyek Famespace:

```text
famespace/
├── public/
│   ├── manifest.json                        # [BARU] Konfigurasi identitas PWA & Theme
│   ├── sw.js                                # [BARU] Service Worker (Offline & Cache)
│   └── icons/                               # [BARU] Ikon aplikasi PWA
│       ├── icon-192x192.png
│       ├── icon-512x512.png
│       ├── icon-maskable-512x512.png
│       └── apple-touch-icon.png
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── main.blade.php               # [MODIFIKASI] Layout Master PWA & Shell Switcher
│       │
│       ├── dashboard/
│       │   └── partials/
│       │       ├── sidebar.blade.php        # [MODIFIKASI] Tambahkan 'hidden md:flex'
│       │       ├── header.blade.php         # [MODIFIKASI] Tambahkan 'hidden md:block'
│       │       ├── bottom-nav.blade.php     # [BARU] Bottom Navigation Bar Khusus Mobile
│       │       ├── mobile-header.blade.php  # [BARU] App Header Khusus Mobile
│       │       ├── fab.blade.php            # [BARU] Floating Action Button "+"
│       │       ├── pwa-banner.blade.php     # [BARU] Banner Install PWA
│       │       └── scripts.blade.php        # [MODIFIKASI] Registrasi Service Worker
│       │
│       └── errors/
│           └── offline.blade.php            # [BARU] Tampilan Graceful Saat Offline
│
└── pwa-implementation.md                    # File dokumentasi ini
```

---

## 2. Implementasi File Fondasi PWA

### 2.1 `public/manifest.json`
Buat file `public/manifest.json`:

```json
{
  "name": "Famespace - Ruang Harmoni Keluarga",
  "short_name": "Famespace",
  "description": "Kelola tugas rumah tangga, kas keluarga, jadwal bersama, dan momen indah keluarga.",
  "start_url": "/dashboard",
  "scope": "/",
  "display": "standalone",
  "background_color": "#FFFDF7",
  "theme_color": "#FFE500",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-maskable-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable"
    },
    {
      "src": "/icons/apple-touch-icon.png",
      "sizes": "180x180",
      "type": "image/png"
    }
  ]
}
```

### 2.2 `public/sw.js` (Service Worker)
Buat file `public/sw.js`:

```javascript
const CACHE_NAME = 'famespace-v1.0';
const OFFLINE_URL = '/offline';

const PRECACHE_ASSETS = [
  '/',
  '/dashboard',
  '/manifest.json',
  OFFLINE_URL,
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_ASSETS);
    })
  );
  self.skipWaiting();
});

// Activate Event (Cleanup Old Cache)
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

// Fetch Event (Network First, Fallback to Cache/Offline)
self.addEventListener('fetch', (event) => {
  // Hanya proses GET requests
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        // Cache hasil respon jika valid
        if (networkResponse && networkResponse.status === 200) {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
        }
        return networkResponse;
      })
      .catch(async () => {
        const cachedResponse = await caches.match(event.request);
        if (cachedResponse) {
          return cachedResponse;
        }
        // Jika request berupa navigasi halaman HTML, berikan halaman offline
        if (event.request.headers.get('accept')?.includes('text/html')) {
          return caches.match(OFFLINE_URL);
        }
      })
  );
});
```

---

## 3. Komponen UI Mobile Baru

### 3.1 `resources/views/dashboard/partials/bottom-nav.blade.php`
Bottom Navigation Bar bergaya Neobrutalist yang menempel di bawah layar HP:

```blade
@php
  $currentRoute = request()->route()->getName();
@endphp

<nav class="fixed bottom-0 inset-x-0 z-40 md:hidden bg-white/95 backdrop-blur-md border-t-2 border-black flex justify-around items-center h-16 pb-safe shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
  
  <!-- Tab 1: Dashboard / Beranda -->
  <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ $currentRoute === 'dashboard' ? 'text-black font-black' : 'text-slate-500 font-semibold' }}">
    <div class="p-1 rounded-xl {{ $currentRoute === 'dashboard' ? 'bg-accent border-2 border-black -translate-y-1 shadow-[2px_2px_0px_#000]' : '' }} transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
      </svg>
    </div>
    <span class="text-[10px] mt-0.5">Beranda</span>
  </a>

  <!-- Tab 2: Chores / Tugas -->
  <a href="{{ route('chores.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ str_contains($currentRoute, 'chores') ? 'text-black font-black' : 'text-slate-500 font-semibold' }}">
    <div class="p-1 rounded-xl {{ str_contains($currentRoute, 'chores') ? 'bg-accent border-2 border-black -translate-y-1 shadow-[2px_2px_0px_#000]' : '' }} transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </div>
    <span class="text-[10px] mt-0.5">Tugas</span>
  </a>

  <!-- Tab 3: Finance / Kas -->
  <a href="{{ route('finances.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ str_contains($currentRoute, 'finances') ? 'text-black font-black' : 'text-slate-500 font-semibold' }}">
    <div class="p-1 rounded-xl {{ str_contains($currentRoute, 'finances') ? 'bg-accent border-2 border-black -translate-y-1 shadow-[2px_2px_0px_#000]' : '' }} transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </div>
    <span class="text-[10px] mt-0.5">Kas</span>
  </a>

  <!-- Tab 4: Kalender / Jadwal -->
  <a href="{{ route('events.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ str_contains($currentRoute, 'events') ? 'text-black font-black' : 'text-slate-500 font-semibold' }}">
    <div class="p-1 rounded-xl {{ str_contains($currentRoute, 'events') ? 'bg-accent border-2 border-black -translate-y-1 shadow-[2px_2px_0px_#000]' : '' }} transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
      </svg>
    </div>
    <span class="text-[10px] mt-0.5">Jadwal</span>
  </a>

  <!-- Tab 5: Anggota / Profil -->
  <a href="{{ route('members.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ str_contains($currentRoute, 'members') ? 'text-black font-black' : 'text-slate-500 font-semibold' }}">
    <div class="p-1 rounded-xl {{ str_contains($currentRoute, 'members') ? 'bg-accent border-2 border-black -translate-y-1 shadow-[2px_2px_0px_#000]' : '' }} transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
      </svg>
    </div>
    <span class="text-[10px] mt-0.5">Keluarga</span>
  </a>

</nav>
```

### 3.2 `resources/views/dashboard/partials/mobile-header.blade.php`
Header ringkas aplikasi mobile:

```blade
<header class="block md:hidden sticky top-0 z-30 bg-surface/90 backdrop-blur border-b-2 border-black px-4 py-3 pt-safe">
  <div class="flex items-center justify-between">
    <!-- Brand / Modul -->
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-lg bg-accent border-2 border-black flex items-center justify-center font-black text-xs shadow-[2px_2px_0px_#000]">
        FS
      </div>
      <div>
        <h1 class="text-sm font-black tracking-tight leading-none text-ink">
          {{ $family->name ?? 'Famespace' }}
        </h1>
        <p class="text-[10px] font-bold text-slate-500 mt-0.5">
          {{ auth()->user()->name }}
        </p>
      </div>
    </div>

    <!-- Quick Badges -->
    <div class="flex items-center gap-2">
      <div class="flex items-center gap-1 px-2.5 py-1 bg-amber-100 border border-black rounded-full text-xs font-black shadow-[1.5px_1.5px_0px_#000]">
        <span>⭐</span>
        <span>{{ auth()->user()->points ?? 0 }}</span>
      </div>
    </div>
  </div>
</header>
```

### 3.3 `resources/views/dashboard/partials/fab.blade.php`
Tombol tambah cepat (FAB) kontekstual sesuai halaman aktif:

```blade
@php
  $currentRoute = request()->route()->getName();
@endphp

@if(str_contains($currentRoute, 'chores'))
  <!-- FAB Tambah Tugas -->
  <button onclick="openChoreModal()" class="fixed right-4 bottom-20 z-30 md:hidden w-14 h-14 bg-accent rounded-full border-2 border-black shadow-[3px_3px_0px_#000] active:translate-y-1 active:shadow-none flex items-center justify-center transition">
    <svg class="w-7 h-7 text-black stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
  </button>
@elseif(str_contains($currentRoute, 'finances'))
  <!-- FAB Tambah Kas -->
  <button onclick="openFinanceModal()" class="fixed right-4 bottom-20 z-30 md:hidden w-14 h-14 bg-emerald-400 rounded-full border-2 border-black shadow-[3px_3px_0px_#000] active:translate-y-1 active:shadow-none flex items-center justify-center transition">
    <svg class="w-7 h-7 text-black stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
  </button>
@elseif(str_contains($currentRoute, 'events'))
  <!-- FAB Tambah Event -->
  <button onclick="openEventModal()" class="fixed right-4 bottom-20 z-30 md:hidden w-14 h-14 bg-sky-400 rounded-full border-2 border-black shadow-[3px_3px_0px_#000] active:translate-y-1 active:shadow-none flex items-center justify-center transition">
    <svg class="w-7 h-7 text-black stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
  </button>
@endif
```

### 3.4 `resources/views/dashboard/partials/pwa-banner.blade.php`
Banner drawer ajakan install PWA (otomatis muncul jika dibuka di browser biasa):

```blade
<div id="pwa-install-prompt" class="fixed inset-x-3 bottom-20 z-50 md:hidden hidden bg-surface border-2 border-black p-4 rounded-2xl shadow-[4px_4px_0px_#000] animate-bounce-short">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-accent border-2 border-black flex items-center justify-center font-black text-lg shadow-[2px_2px_0px_#000] shrink-0">
        FS
      </div>
      <div>
        <h4 class="text-sm font-black text-ink">Pasang Aplikasi Famespace</h4>
        <p class="text-[11px] font-bold text-slate-600 mt-0.5 leading-snug">
          Buka lebih cepat & mudah dari layar utama HP kamu!
        </p>
      </div>
    </div>
    <button onclick="dismissPwaPrompt()" class="text-slate-400 hover:text-black font-bold p-1">&times;</button>
  </div>
  <div class="mt-3 flex gap-2">
    <button id="pwa-install-button" class="flex-1 py-2 bg-ink text-surface text-xs font-black rounded-xl border border-black shadow-[2px_2px_0px_#FFE500]">
      Install Sekarang
    </button>
    <button onclick="dismissPwaPrompt()" class="px-3 py-2 bg-slate-100 text-xs font-bold rounded-xl border border-black">
      Nanti
    </button>
  </div>
</div>

<script>
  let deferredPrompt;
  const pwaPrompt = document.getElementById('pwa-install-prompt');
  const installBtn = document.getElementById('pwa-install-button');

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    // Cek jika belum di-dismiss sebelumnya
    if (!localStorage.getItem('pwa_dismissed')) {
      pwaPrompt?.classList.remove('hidden');
    }
  });

  installBtn?.addEventListener('click', async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    console.log(`User response: ${outcome}`);
    deferredPrompt = null;
    pwaPrompt?.classList.add('hidden');
  });

  function dismissPwaPrompt() {
    pwaPrompt?.classList.add('hidden');
    localStorage.setItem('pwa_dismissed', 'true');
  }
</script>
```

---

## 4. Modifikasi Layout Utama ([layouts/main.blade.php](file:///d:/Samudrakreasi/famespace/resources/views/layouts/main.blade.php))

Update file `resources/views/layouts/main.blade.php` agar menggabungkan Shell Desktop dan Shell Mobile:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>@yield('title', 'Dashboard Keluarga — ' . ($family->name ?? 'Famespace'))</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- PWA & Mobile Meta Tags -->
  <meta name="theme-color" content="#FFE500">
  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Famespace">

  <!-- TAILWIND & FONTS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
  
  @include('dashboard.partials.styles')
  @stack('styles')
</head>
<body class="h-screen overflow-hidden flex flex-col antialiased selection:bg-accent selection:text-ink">

  <!-- 1. MOBILE TOP APP BAR (Hanya di layar HP) -->
  @include('dashboard.partials.mobile-header')

  <!-- 2. WRAPPER UTAMA -->
  <div class="flex-1 flex overflow-hidden">

    <!-- 3. SIDEBAR KHUSUS DESKTOP (Tampil hanya di md ke atas) -->
    <div class="hidden md:flex">
      @include('dashboard.partials.sidebar')
    </div>

    <!-- 4. CANVAS KONTEN UTAMA -->
    <div id="main-content" class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
      
      <!-- Topbar Header Desktop -->
      <div class="hidden md:block">
        @include('dashboard.partials.header')
      </div>

      <!-- Notifikasi Flash -->
      @include('dashboard.partials.notifications')

      <!-- Konten Modul (Berikan padding bawah pb-24 untuk mobile agar tidak tertutup Bottom Nav) -->
      <main class="p-4 sm:p-6 pb-24 md:pb-6 max-w-7xl w-full mx-auto space-y-6">
        @yield('content')
      </main>

    </div>
  </div>

  <!-- 5. FLOATING ACTION BUTTON (Mobile) -->
  @include('dashboard.partials.fab')

  <!-- 6. BOTTOM NAVIGATION BAR (Mobile) -->
  @include('dashboard.partials.bottom-nav')

  <!-- 7. PWA INSTALL BANNER PROMPT (Mobile) -->
  @include('dashboard.partials.pwa-banner')

  <!-- SCRIPTS & SERVICE WORKER -->
  @include('dashboard.partials.scripts')
  @stack('scripts')
</body>
</html>
```

---

## 5. Touch & iOS Tuning di [dashboard/partials/styles.blade.php](file:///d:/Samudrakreasi/famespace/resources/views/dashboard/partials/styles.blade.php)

Tambahkan kode ini ke dalam file CSS/style:

```css
/* Matikan seleksi teks pada navigasi & tombol mobile */
nav, button, .interactive-btn {
  -webkit-tap-highlight-color: transparent;
  user-select: none;
  -webkit-user-select: none;
}

/* Dukungan Notch dan Safe Area iPhone */
.pt-safe {
  padding-top: max(env(safe-area-inset-top, 0px), 0.75rem);
}
.pb-safe {
  padding-bottom: max(env(safe-area-inset-bottom, 0px), 0.5rem);
}

/* Modifikasi Standalone PWA Mode */
@media all and (display-mode: standalone) {
  /* Hilangkan banner prompt install jika aplikasi sudah dibuka dari Home Screen */
  #pwa-install-prompt {
    display: none !important;
  }
}

/* Transformasi Tabel Web ke Card App di Tampilan Mobile PWA */
@media (max-width: 767px) {
  #finance-datatable thead {
    display: none !important;
  }
  #finance-datatable tbody tr {
    display: grid !important;
    grid-template-columns: 1fr auto !important;
    grid-template-areas:
      "desc amount"
      "cat date"
      "user actions" !important;
    /* Kartu transaksi native mobile app neobrutalisme */
  }
}
```

---

## 6. Verifikasi & Pengujian

1. **Uji di Browser Komputer (Chrome/Edge)**:
   * Tekan `F12` -> Buka tab **Lighthouse**.
   * Pilih Device: **Mobile** -> Centang kategori **Progressive Web App** -> Klik **Analyze page load**.
   * Buka tab **Application** -> Klik menu **Manifest** & **Service Workers** untuk memastikan statusnya *Active and running*.
2. **Uji di Smartphone Nyata**:
   * Jalankan server lokal: `php artisan serve --host=0.0.0.0 --port=8000`.
   * Buka browser di HP melalui alamat IP lokal (misal: `http://192.168.1.5:8000`).
   * Tekan menu browser (*titik tiga* di Chrome Android / *Share button* di Safari iPhone) -> Tekan **Tambahkan ke Layar Utama (Add to Home Screen)**.
   * Buka ikon Famespace di layar HP: Aplikasi akan terbuka dalam mode layar penuh (tanpa search bar browser) lengkap dengan Bottom Navigation Bar.
