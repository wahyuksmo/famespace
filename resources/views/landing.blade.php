<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>FamKoji — Urusan Rumah Tangga Jadi Santai & Kompak</title>

  <!-- PWA & Mobile Web App Meta -->
  <meta name="theme-color" content="#2F5233">
  <link rel="icon" type="image/png" href="{{ asset('icons/favicon-32x32.png') }}">
  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="FamKoji">

  <script src="{{ asset('vendor/tailwindcss/tailwind.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              DEFAULT: '#2F5233',
              dark: '#1E3821',
              light: '#3F6B44',
              soft: '#EAF0EB',
              accent: '#34D399'
            },
            cream: '#FBF8EF',
            ink: '#121614',
            accent: '#34D399',
            coral: '#E07A5F',
            sage: '#87A987',
            sky: '#81B0C4'
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
            display: ['"Space Grotesk"', 'sans-serif'],
            mono: ['"JetBrains Mono"', 'monospace']
          },
          boxShadow: {
            brutal: '4px 4px 0px 0px #121614',
            'brutal-lg': '6px 6px 0px 0px #121614',
            'brutal-sm': '2px 2px 0px 0px #121614'
          }
        }
      }
    }
  </script>
  <style>
    body {
      background-color: #FBF8EF;
      color: #121614;
      font-family: 'Plus Jakarta Sans', sans-serif;
      overflow-x: hidden;
      padding-top: env(safe-area-inset-top, 0px);
      padding-bottom: env(safe-area-inset-bottom, 0px);
    }
    nav, button, a {
      -webkit-tap-highlight-color: transparent;
    }
    .brutal-border {
      border: 2.5px solid #121614;
    }
    .brutal-btn {
      border: 2.5px solid #121614;
      box-shadow: 4px 4px 0px 0px #121614;
      transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .brutal-btn:hover {
      transform: translate(-2px, -2px);
      box-shadow: 6px 6px 0px 0px #121614;
    }
    .brutal-btn:active {
      transform: translate(2px, 2px);
      box-shadow: 2px 2px 0px 0px #121614;
    }
    .brutal-card {
      border: 2.5px solid #121614;
      box-shadow: 5px 5px 0px 0px #121614;
      transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .brutal-card:hover {
      transform: translate(-3px, -3px);
      box-shadow: 8px 8px 0px 0px #121614;
    }

    /* PWA Standalone Mode adjustments */
    @media all and (display-mode: standalone), (display-mode: fullscreen), (display-mode: minimal-ui) {
      #pwa-nav-install-btn,
      #pwa-hero-install-btn,
      #pwa-guide-modal {
        display: none !important;
      }
      .pwa-mobile-register {
        display: inline-flex !important;
      }
    }
    .is-pwa #pwa-nav-install-btn,
    .is-pwa #pwa-hero-install-btn,
    .is-pwa #pwa-guide-modal {
      display: none !important;
    }
    .is-pwa .pwa-mobile-register {
      display: inline-flex !important;
    }

    /* Keyframes & Motion */
    @keyframes float-badge {
      0%, 100% { transform: translateY(0px) rotate(6deg); }
      50% { transform: translateY(-7px) rotate(3deg); }
    }
    @keyframes float-slow {
      0%, 100% { transform: translateY(0px) rotate(-3deg); }
      50% { transform: translateY(-6px) rotate(-1deg); }
    }
    @keyframes marquee {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
    @keyframes popIn {
      0% { transform: scale(0.95); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }
    .animate-float-badge {
      animation: float-badge 3.5s ease-in-out infinite;
    }
    .animate-float-slow {
      animation: float-slow 4s ease-in-out infinite;
    }
    .marquee-track {
      display: flex;
      width: 200%;
      animation: marquee 22s linear infinite;
    }
    .marquee-track:hover {
      animation-play-state: paused;
    }

    /* Scroll reveal */
    .reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.6s ease-out, transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .revealed {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>
<body class="bg-cream text-ink antialiased selection:bg-accent selection:text-ink">

  <!-- TOP ANNOUNCEMENT BANNER -->
  <div class="bg-accent brutal-border border-t-0 border-x-0 py-2 px-4 text-center text-xs md:text-sm font-bold tracking-wide">
    🌿 Gak ada lagi drama lupa beli odol atau bon belanja hilang. Satu ruang kompak buat sekeluarga. 
    <a href="{{ route('register') }}" class="underline decoration-2 font-black ml-1 hover:text-brand-dark">Bikin Ruang Rumah Sekarang &rarr;</a>
  </div>

  <!-- NAVBAR -->
  <nav class="sticky top-0 z-50 bg-cream/95 backdrop-blur-md brutal-border border-t-0 border-x-0">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2">
      <!-- LOGO -->
      <a href="{{ route('landing') }}" class="flex items-center gap-2 sm:gap-3 group shrink-0 min-w-0">
        <div class="w-10 h-10 sm:w-11 sm:h-11 bg-brand-soft brutal-border rounded-xl p-1 flex items-center justify-center shadow-[2px_2px_0px_#121614] group-hover:rotate-6 transition-transform shrink-0">
          <img src="{{ asset('icons/new_logo.png') }}" alt="FamKoji Logo" class="w-full h-full object-contain">
        </div>
        <div class="min-w-0">
          <span class="font-display font-black text-xl sm:text-2xl tracking-tight text-ink">FamKoji<span class="text-brand">.</span></span>
          <span class="hidden lg:inline-block ml-2 px-2 py-0.5 text-[10px] font-black uppercase bg-accent brutal-border text-ink">Aplikasi Keluarga</span>
        </div>
      </a>

      <!-- NAV LINKS -->
      <div class="hidden md:flex items-center gap-8 font-bold text-sm">
        <a href="#fitur" class="hover:text-brand transition-colors">Fitur Rumah</a>
        <a href="#keunggulan" class="hover:text-brand transition-colors">Kenapa FamKoji?</a>
        <a href="#cara-kerja" class="hover:text-brand transition-colors">Cara Mulai</a>
        <a href="#harga" class="hover:text-brand transition-colors">Pilihan Paket</a>
      </div>

      <!-- CTA BUTTONS -->
      <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
        <button id="pwa-nav-install-btn" type="button" class="inline-flex px-2 sm:px-3.5 py-1.5 sm:py-2 font-black text-xs sm:text-sm bg-accent text-ink brutal-btn rounded-none items-center gap-1 cursor-pointer shrink-0">
          <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          <span class="hidden sm:inline">Install App</span>
          <span class="sm:hidden text-[11px]">Install</span>
        </button>
        <a href="{{ route('login') }}" class="px-2.5 sm:px-4 py-1.5 sm:py-2 font-bold text-xs sm:text-sm bg-white hover:bg-cream brutal-btn rounded-none shrink-0">
          Masuk
        </a>
        <a href="{{ route('register') }}" class="hidden sm:inline-flex pwa-mobile-register px-3 sm:px-5 py-1.5 sm:py-2 font-black text-xs sm:text-sm bg-brand text-white hover:bg-brand-light brutal-btn rounded-none shrink-0">
          <span class="hidden sm:inline">Mulai Gratis &rarr;</span>
          <span class="sm:hidden">Daftar &rarr;</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      
      <!-- HERO TEXT -->
      <div class="lg:col-span-7 space-y-6">
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-brand-soft text-brand-dark brutal-border font-mono text-xs font-bold uppercase tracking-wider">
          <span class="w-2.5 h-2.5 bg-brand rounded-full inline-block"></span>
          Beresin Urusan Rumah Bareng-Bareng
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black font-display tracking-tight text-ink leading-[1.08]">
          Biar Rumah Gak Berantakan, <span class="bg-accent px-2 py-0.5 brutal-border inline-block rotate-[-1deg]">Tugas</span> Jelas, Dompet Aman.
        </h1>

        <p class="text-lg sm:text-xl font-medium text-ink/80 max-w-2xl leading-relaxed">
          Capek tiap hari nanya "siapa yang belum buang sampah?" atau bon belanjaan tercecer entah ke mana? 
          <strong class="text-ink font-bold">FamKoji</strong> bikin urusan sehari-hari Ayah, Ibu, dan Anak tercatat rapi dalam satu aplikasi. Semua saling tahu, tanpa curiga.
        </p>

        <!-- CTA & TRUST -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2">
          <a href="{{ route('register') }}" class="px-7 py-4 bg-brand text-white font-black text-base text-center brutal-btn">
            👨‍👩‍👧‍👦 Bikin Ruang Keluarga Sekarang
          </a>
          <button id="pwa-hero-install-btn" type="button" class="flex px-6 py-4 bg-accent text-ink font-black text-base text-center brutal-btn items-center justify-center gap-2 cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span>📲 Simpan ke Layar HP</span>
          </button>
          <a href="{{ route('login') }}" class="px-6 py-4 bg-white text-ink font-bold text-base text-center brutal-btn flex items-center justify-center gap-2">
            <span>Masuk ke Akun &rarr;</span>
          </a>
        </div>

        <div class="flex flex-wrap items-center gap-5 pt-3 text-xs font-bold text-ink/70">
          <div class="flex items-center gap-2">
            <span class="text-brand text-base">✓</span> Fitur Dasar Gratis Dipakai Kapan Aja
          </div>
          <div class="flex items-center gap-2">
            <span class="text-brand text-base">✓</span> Fitur Pro Cuma Rp 20rb/bln Sekeluarga
          </div>
          <div class="flex items-center gap-2">
            <span class="text-brand text-base">✓</span> Kirim Undangan Langsung via WhatsApp
          </div>
        </div>
      </div>

      <!-- HERO VISUAL (NEOBRUTALIST MOCKUP CARD) -->
      <div class="lg:col-span-5">
        <div class="bg-white brutal-card p-4 sm:p-6 relative group">
          <!-- FLOATING STICKER KIRI ATAS -->
          <div class="absolute -top-3.5 -left-3.5 bg-coral text-white brutal-border px-3 py-1 font-display font-black text-[11px] uppercase tracking-wider -rotate-6 shadow-brutal animate-float-slow hidden sm:flex items-center gap-1.5 z-10">
            <span>⚡</span>
            <span>LIVE INTERAKTIF</span>
          </div>

          <!-- TOP WINDOW BAR -->
          <div class="flex items-center justify-between pb-4 border-b-2 border-ink mb-4">
            <div class="flex items-center gap-2">
              <div class="w-3.5 h-3.5 rounded-full bg-coral brutal-border"></div>
              <div class="w-3.5 h-3.5 rounded-full bg-accent brutal-border"></div>
              <div class="w-3.5 h-3.5 rounded-full bg-sage brutal-border"></div>
              <span class="font-mono text-xs font-bold text-ink/70 ml-2">ruang-keluarga-harmonis</span>
            </div>
            <span class="px-2 py-0.5 bg-brand text-white font-mono text-[11px] font-bold uppercase brutal-border flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-accent animate-ping inline-block"></span>
              Online
            </span>
          </div>

          <!-- MINI WIDGET 1: CHORES (INTERAKTIF) -->
          <div class="bg-cream brutal-border p-3 mb-3">
            <div class="flex items-center justify-between mb-2">
              <span id="hero-task-status-title" class="font-display font-bold text-xs uppercase tracking-wider text-ink/80">Tugas Hari Ini (3/4)</span>
              <span id="hero-task-pct" class="text-xs font-mono font-bold text-brand transition-all">75% Selesai</span>
            </div>
            <div class="space-y-2 text-xs font-semibold">
              <div class="flex items-center justify-between p-2 bg-white brutal-border">
                <div class="flex items-center gap-2">
                  <span class="text-brand font-bold">☑</span>
                  <span class="line-through text-ink/50">Cuci piring & bersihkan dapur</span>
                </div>
                <span class="px-1.5 py-0.5 bg-accent brutal-border text-[10px] font-bold">Ibu</span>
              </div>
              <div class="flex items-center justify-between p-2 bg-white brutal-border">
                <div class="flex items-center gap-2">
                  <span class="text-brand font-bold">☑</span>
                  <span class="line-through text-ink/50">Beli galon & gas elpiji</span>
                </div>
                <span class="px-1.5 py-0.5 bg-sage brutal-border text-[10px] font-bold">Ayah</span>
              </div>
              <div id="hero-task-item" onclick="toggleHeroTask(this, event)" title="Klik untuk selesaikan tugas!" class="flex items-center justify-between p-2 bg-white brutal-border cursor-pointer hover:bg-accent/20 transition-all active:scale-[0.98] select-none">
                <div class="flex items-center gap-2">
                  <span id="hero-task-check" class="text-coral font-bold text-sm">☐</span>
                  <span id="hero-task-text">Beri makan kucing & siram tanaman</span>
                </div>
                <div class="flex items-center gap-1.5">
                  <span class="text-[9px] font-mono text-brand font-bold uppercase underline hidden sm:inline">coba klik!</span>
                  <span class="px-1.5 py-0.5 bg-coral text-white brutal-border text-[10px] font-bold">Budi (Anak)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- MINI WIDGET 2: KAS & TABUNGAN -->
          <div class="grid grid-cols-2 gap-3 mb-3">
            <div class="p-3 bg-brand-soft brutal-border hover:bg-white transition-colors">
              <div class="text-[11px] font-mono font-bold text-ink/70">KAS BULANAN</div>
              <div class="text-lg font-black font-mono text-brand">Rp 8.450.000</div>
              <div class="text-[10px] font-bold text-ink/60 mt-1">Sisa aman hingga tgl 30</div>
            </div>
            <div class="p-3 bg-accent/25 brutal-border hover:bg-accent/35 transition-colors">
              <div class="text-[11px] font-mono font-bold text-ink/70">TARGET LIBURAN BALI</div>
              <div class="text-lg font-black font-mono text-ink">Rp 12.000.000</div>
              <div class="w-full bg-white h-2.5 brutal-border mt-1.5 overflow-hidden">
                <div id="hero-progress-bar" class="bg-brand h-full w-[70%] transition-all duration-1000 ease-out"></div>
              </div>
            </div>
          </div>

          <!-- MINI WIDGET 3: LINIMASA & TRIP -->
          <div class="p-3 bg-sky/20 brutal-border flex items-center justify-between hover:bg-sky/30 transition-colors">
            <div>
              <span class="text-[10px] font-mono font-bold px-1 bg-white brutal-border">LINIMASA BARU</span>
              <div class="font-bold text-xs mt-1">Trip Glamping Bandung (2 Hari)</div>
              <div class="text-[11px] font-mono text-ink/70">Pengeluaran: Rp 1.650.000 • 14 Foto</div>
            </div>
            <a href="{{ route('dashboard') }}" class="px-2.5 py-1.5 bg-white brutal-btn text-xs font-black">
              Buka &rarr;
            </a>
          </div>

          <!-- FLOATING STAMP -->
          <div onclick="fireConfetti(event.clientX, event.clientY)" title="Klik untuk konfeti!" class="absolute -bottom-4 -right-3 bg-accent brutal-border px-3 py-1 font-display font-black text-xs uppercase tracking-widest rotate-6 shadow-brutal animate-float-badge cursor-pointer select-none hover:scale-105 active:scale-95 transition-transform">
            ⭐ 100% SINKRON
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- VALUE PILLARS BAR (REAL PRODUCTION VALUE, NO FAKE STATS) -->
  <section class="bg-ink text-white brutal-border border-x-0 py-6 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
      <div class="p-2">
        <div class="text-2xl sm:text-3xl font-black font-display text-accent">100% Privat</div>
        <div class="text-xs sm:text-sm font-bold text-cream/80 uppercase tracking-wider mt-1">Hanya Orang Rumah</div>
      </div>
      <div class="p-2">
        <div class="text-2xl sm:text-3xl font-black font-display text-sage">Otomatis</div>
        <div class="text-xs sm:text-sm font-bold text-cream/80 uppercase tracking-wider mt-1">HP Ayah & Ibu Sinkron</div>
      </div>
      <div class="p-2">
        <div class="text-2xl sm:text-3xl font-black font-display text-coral">Bebas Iklan</div>
        <div class="text-xs sm:text-sm font-bold text-cream/80 uppercase tracking-wider mt-1">Fokus Urusan Rumah</div>
      </div>
      <div class="p-2">
        <div class="text-2xl sm:text-3xl font-black font-display text-sky">Super Enteng</div>
        <div class="text-xs sm:text-sm font-bold text-cream/80 uppercase tracking-wider mt-1">Lancar di HP Apa Aja</div>
      </div>
    </div>
  </section>

  <!-- MARQUEE RUNNING TICKER -->
  <div class="bg-accent brutal-border border-x-0 border-t-0 py-2.5 overflow-hidden font-mono font-black text-xs uppercase tracking-widest text-ink select-none">
    <div class="flex whitespace-nowrap marquee-track">
      <span class="mx-4">✦ UANG KAS TRANSPARAN</span>
      <span class="mx-4">✦ GILIRAN CUCI PIRING JELAS</span>
      <span class="mx-4">✦ NABUNG LIBURAN BARENG</span>
      <span class="mx-4">✦ JADWAL KELUARGA GAK LUPA</span>
      <span class="mx-4">✦ AYAH IBU ANAK KOMPAK</span>
      <span class="mx-4">✦ RUMAH TENANG TANPA DEBAT</span>
      <span class="mx-4">✦ UANG KAS TRANSPARAN</span>
      <span class="mx-4">✦ GILIRAN CUCI PIRING JELAS</span>
      <span class="mx-4">✦ NABUNG LIBURAN BARENG</span>
      <span class="mx-4">✦ JADWAL KELUARGA GAK LUPA</span>
      <span class="mx-4">✦ AYAH IBU ANAK KOMPAK</span>
      <span class="mx-4">✦ RUMAH TENANG TANPA DEBAT</span>
    </div>
  </div>

  <!-- 4 PILAR FITUR -->
  <section id="fitur" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 reveal">
    <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
      <span class="px-3 py-1 bg-brand text-white brutal-border font-mono text-xs font-bold uppercase tracking-wider">
        Fitur Kompak FamKoji
      </span>
      <h2 class="text-3xl sm:text-5xl font-black font-display text-ink tracking-tight">
        Satu Aplikasi Buat Beresin 4 Urusan Rumah yang Sering Bikin Pusing.
      </h2>
      <p class="text-base sm:text-lg text-ink/80 font-medium">
        Gak perlu lagi catatan tercecer di kulkas, grup WA keluarga penuh chat tenggelam, atau bon belanjaan hilang entah ke mana.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      
      <!-- PILAR 1: TUGAS & CHORES -->
      <div class="bg-white brutal-card p-6 sm:p-8 hover:-translate-y-1.5 transition-transform">
        <div class="flex items-center justify-between mb-4">
          <div class="w-14 h-14 bg-accent brutal-border flex items-center justify-center text-2xl shadow-brutal-sm">
            🧹
          </div>
          <span class="px-2 py-0.5 bg-cream brutal-border font-mono text-[10px] font-black uppercase text-ink">Fitur Gratis</span>
        </div>
        <span class="font-mono text-xs font-bold uppercase tracking-wider text-brand">Bagian 01 — Adil & Jelas</span>
        <h3 class="text-2xl font-black font-display text-ink mt-1 mb-3">Bagi Tugas Tanpa Saling Tunjuk</h3>
        <p class="text-ink/80 text-sm leading-relaxed mb-6 font-medium">
          Malam ini giliran siapa cuci piring? Siapa yang beli beras pas habis? Buat daftar tugas harian bareng-bareng. Anak-anak juga bisa kumpulin poin hadiah tiap selesai bantu orang tua!
        </p>
        <div class="bg-cream brutal-border p-4 space-y-2 text-xs font-bold">
          <div class="flex items-center justify-between">
            <span>✓ Bersihkan Kamar Mandi (Ayah)</span>
            <span class="text-brand">Selesai (+10 poin)</span>
          </div>
          <div class="flex items-center justify-between text-ink/70">
            <span>☐ Buang Sampah Depan (Budi)</span>
            <span class="text-coral">Batas: 20:00</span>
          </div>
        </div>
      </div>

      <!-- PILAR 2: KAS & TABUNGAN -->
      <div class="bg-white brutal-card p-6 sm:p-8 hover:-translate-y-1.5 transition-transform">
        <div class="flex items-center justify-between mb-4">
          <div class="w-14 h-14 bg-brand text-white brutal-border flex items-center justify-center text-2xl shadow-brutal-sm">
            💰
          </div>
          <span class="px-2 py-0.5 bg-cream brutal-border font-mono text-[10px] font-black uppercase text-ink">Fitur Gratis</span>
        </div>
        <span class="font-mono text-xs font-bold uppercase tracking-wider text-brand">Bagian 02 — Dompet Tenang</span>
        <h3 class="text-2xl font-black font-display text-ink mt-1 mb-3">Uang Kas Terbuka, Nabung Impian Tercapai</h3>
        <p class="text-ink/80 text-sm leading-relaxed mb-6 font-medium">
          Urusan belanja pasar, tagihan listrik, sampai SPP anak tercatat terbuka. Pasutri juga bisa bikin celengan bersama buat liburan atau dana darurat dengan progress bar yang nyata.
        </p>
        <div class="bg-cream brutal-border p-4 space-y-2 text-xs font-bold">
          <div class="flex justify-between items-center">
            <span>🏖️ Nabung Liburan Bali Akhir Tahun</span>
            <span class="font-mono text-brand">Rp 12.000.000 / 15.000.000</span>
          </div>
          <div class="w-full bg-white h-3 brutal-border overflow-hidden">
            <div class="bg-brand h-full w-[80%]"></div>
          </div>
        </div>
      </div>

      <!-- PILAR 3: LINIMASA & TRIP -->
      <div class="bg-white brutal-card p-6 sm:p-8 hover:-translate-y-1.5 transition-transform relative">
        <div class="flex items-center justify-between mb-4">
          <div class="w-14 h-14 bg-coral text-white brutal-border flex items-center justify-center text-2xl shadow-brutal-sm">
            🗺️
          </div>
          <span class="px-2 py-0.5 bg-coral text-white brutal-border font-mono text-[10px] font-black uppercase">🔒 Pro &bull; Rp 20rb/bln</span>
        </div>
        <span class="font-mono text-xs font-bold uppercase tracking-wider text-brand">Bagian 03 — Cerita Perjalanan</span>
        <h3 class="text-2xl font-black font-display text-ink mt-1 mb-3">Catat Liburan & Rincian Pengeluaran</h3>
        <p class="text-ink/80 text-sm leading-relaxed mb-6 font-medium">
          Tahun ini udah mudik atau jalan-jalan ke mana aja? Simpan foto kenangan favorit lengkap dengan rincian biaya bensin, makan enak, dan tiket wisata biar keuangan liburan tetap sehat.
        </p>
        <div class="bg-cream brutal-border p-4 text-xs font-bold space-y-1">
          <div class="flex justify-between">
            <span>🚗 Roadtrip Jogja - Solo</span>
            <span class="font-mono text-coral font-black">Rp 3.850.000</span>
          </div>
          <p class="text-ink/60 text-[11px] font-normal">3 Hari 2 Malam • 24 Foto Momen • 8 Tempat Makan</p>
        </div>
      </div>

      <!-- PILAR 4: KALENDER & AGENDA -->
      <div class="bg-white brutal-card p-6 sm:p-8 hover:-translate-y-1.5 transition-transform relative">
        <div class="flex items-center justify-between mb-4">
          <div class="w-14 h-14 bg-sky text-ink brutal-border flex items-center justify-center text-2xl shadow-brutal-sm">
            📅
          </div>
          <span class="px-2 py-0.5 bg-coral text-white brutal-border font-mono text-[10px] font-black uppercase">🔒 Pro &bull; Rp 20rb/bln</span>
        </div>
        <span class="font-mono text-xs font-bold uppercase tracking-wider text-brand">Bagian 04 — Jadwal Kompak</span>
        <h3 class="text-2xl font-black font-display text-ink mt-1 mb-3">Kalender Bersama & Pengingat Acara</h3>
        <p class="text-ink/80 text-sm leading-relaxed mb-6 font-medium">
          Gak ada lagi cerita Ayah kelupaan jemput anak atau Ibu lupa jadwal imunisasi. Kalender keluarga otomatis sinkron dan kirim pengingat ke HP seluruh anggota keluarga.
        </p>
        <div class="bg-cream brutal-border p-4 space-y-2 text-xs font-bold">
          <div class="flex items-center justify-between text-ink">
            <span>🗓️ 28 Oktober: Kontrol Dokter Gigi (Adik)</span>
            <span class="px-2 py-0.5 bg-accent brutal-border text-[10px]">10:00 WIB</span>
          </div>
          <div class="flex items-center justify-between text-ink/70">
            <span>🗓️ 05 November: Ambil Rapor Semester</span>
            <span class="px-2 py-0.5 bg-white brutal-border text-[10px]">Semua Hadir</span>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- CARA KERJA (3 LANGKAH) -->
  <section id="cara-kerja" class="bg-brand text-white brutal-border border-x-0 py-20 reveal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
        <span class="px-3 py-1 bg-accent text-ink brutal-border font-mono text-xs font-black uppercase tracking-wider">
          Gampang Banget
        </span>
        <h2 class="text-3xl sm:text-5xl font-black font-display tracking-tight text-white">
          Cuma Butuh 3 Menit Buat Mulai.
        </h2>
        <p class="text-cream/90 text-sm sm:text-base">
          Tampilannya simpel dan bersih, kakek-nenek atau anak-anak pun langsung bisa pakai.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- LANGKAH 1 -->
        <div class="bg-white text-ink brutal-border p-6 shadow-brutal hover:-translate-y-1.5 transition-transform relative">
          <div class="text-4xl font-black font-display text-brand mb-4">01.</div>
          <h4 class="text-xl font-black font-display mb-2">Bikin Ruang Rumah</h4>
          <p class="text-xs sm:text-sm text-ink/80 leading-relaxed font-medium">
            Daftar akun sebagai Ayah atau Ibu, beri nama keluarga (misal: "Keluarga Bramantyo"), selesai dalam hitungan detik.
          </p>
        </div>

        <!-- LANGKAH 2 -->
        <div class="bg-accent text-ink brutal-border p-6 shadow-brutal hover:-translate-y-1.5 transition-transform relative">
          <div class="text-4xl font-black font-display text-ink mb-4">02.</div>
          <h4 class="text-xl font-black font-display mb-2">Kirim Link ke WhatsApp</h4>
          <p class="text-xs sm:text-sm text-ink/80 leading-relaxed font-medium">
            Tinggal salin link undangan atau share kode unik ke grup WA keluarga. Pasangan dan anak langsung gabung tanpa ribet.
          </p>
        </div>

        <!-- LANGKAH 3 -->
        <div class="bg-white text-ink brutal-border p-6 shadow-brutal hover:-translate-y-1.5 transition-transform relative">
          <div class="text-4xl font-black font-display text-brand mb-4">03.</div>
          <h4 class="text-xl font-black font-display mb-2">Rasakan Tenangnya Rumah</h4>
          <p class="text-xs sm:text-sm text-ink/80 leading-relaxed font-medium">
            Mulai bagi tugas harian, catat uang dapur bareng, dan rencanakan liburan. Rumah tangga jadi adem dan kompak!
          </p>
        </div>
      </div>

      <div class="mt-12 text-center">
        <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-accent text-ink font-black text-base brutal-btn">
          Mulai Bikin Ruang Sekarang &rarr;
        </a>
      </div>
    </div>
  </section>

  <!-- TABEL HARGA / PRICING -->
  <section id="harga" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 reveal">
    <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
      <span class="px-3 py-1 bg-accent text-ink brutal-border font-mono text-xs font-black uppercase tracking-wider">
        Jujur & Ramah Kantong
      </span>
      <h2 class="text-3xl sm:text-5xl font-black font-display text-ink tracking-tight">
        Satu Biaya Kecil Buat Serumah.
      </h2>
      <p class="text-base sm:text-lg text-ink/80 font-medium">
        Bisa pakai gratis selamanya untuk to-do harian dan kas dasar. Mau fitur lengkap? Cukup Rp 20.000/bulan atau bayar sekali Rp 150.000 seumur hidup.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl mx-auto items-stretch">
      <!-- PAKET FREE -->
      <div class="bg-white brutal-card p-6 sm:p-8 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="px-2.5 py-1 bg-cream brutal-border font-mono text-xs font-bold text-ink uppercase">Paket Dasar</span>
            <span class="text-xs font-mono font-bold text-ink/60">Aktif Selamanya</span>
          </div>
          <h3 class="text-2xl font-black font-display text-ink">Free Starter</h3>
          <div class="mt-4 mb-6 flex items-baseline gap-1">
            <span class="text-4xl font-black font-display text-ink">Rp 0</span>
            <span class="text-xs font-mono text-ink/70">/ bulan</span>
          </div>
          <p class="text-xs text-ink/80 font-medium mb-6 leading-relaxed">
            Pas buat pasutri yang baru mau membiasakan catat tugas harian dan uang kas tanpa beban biaya.
          </p>

          <div class="space-y-3 text-xs font-bold border-t-2 border-ink pt-6 mb-6">
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Daftar tugas & giliran rumah tangga interaktif</span>
            </div>
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Catatan kas belanja dapur & celengan impian</span>
            </div>
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Undang semua anggota keluarga tanpa batasan</span>
            </div>
            <div class="flex items-center gap-2.5 text-ink/40">
              <span>✕</span>
              <span class="line-through">Linimasa & Pengeluaran Trip Liburan</span>
            </div>
            <div class="flex items-center gap-2.5 text-ink/40">
              <span>✕</span>
              <span class="line-through">Kalender Bersama & Pengingat Agenda</span>
            </div>
          </div>
        </div>

        <a href="{{ route('register') }}" class="w-full py-3.5 bg-white hover:bg-cream text-ink text-center font-black text-xs uppercase tracking-wider brutal-btn">
          Mulai Gratis Sekarang &rarr;
        </a>
      </div>

      <!-- PAKET PRO -->
      <div class="bg-brand-soft brutal-card p-6 sm:p-8 flex flex-col justify-between relative">
        <div class="absolute -top-3.5 right-6">
          <span class="px-3 py-1 bg-accent text-ink brutal-border font-mono text-[10px] font-black uppercase tracking-wider shadow-brutal-sm">
            👑 Paling Kompak
          </span>
        </div>

        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="px-2.5 py-1 bg-brand text-white brutal-border font-mono text-xs font-black uppercase">FamKoji Pro</span>
            <span class="text-xs font-mono font-bold text-brand">1 Akun Buat Serumah</span>
          </div>
          <h3 class="text-2xl font-black font-display text-ink">Family Pro</h3>
          <div class="mt-4 mb-6 flex items-baseline gap-1">
            <span class="text-4xl font-black font-display text-ink">Rp 20.000</span>
            <span class="text-xs font-mono text-ink/70">/ bulan</span>
          </div>
          <p class="text-xs text-ink/80 font-medium mb-6 leading-relaxed">
            Semua fitur terbuka tanpa batas. Cukup satu yang bayar, seluruh anggota keluarga otomatis aktif.
          </p>

          <div class="space-y-3 text-xs font-bold border-t-2 border-ink pt-6 mb-6">
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Semua fitur di Paket Free Starter</span>
            </div>
            <div class="flex items-center gap-2.5 text-ink">
              <span class="text-brand font-black text-sm">★</span>
              <span class="font-black text-brand-dark">Linimasa & Riwayat Biaya Perjalanan Liburan</span>
            </div>
            <div class="flex items-center gap-2.5 text-ink">
              <span class="text-brand font-black text-sm">★</span>
              <span class="font-black text-brand-dark">Kalender Bersama & Alarm Acara Keluarga</span>
            </div>
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Upload bukti foto struk & album kenangan</span>
            </div>
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Bisa berhenti berlangganan kapan saja</span>
            </div>
          </div>
        </div>

        <a href="{{ route('register') }}" class="w-full py-3.5 bg-brand text-white hover:bg-accent hover:text-ink text-center font-black text-xs uppercase tracking-wider brutal-btn">
          Pilih Paket Family Pro &rarr;
        </a>
      </div>

      <!-- PAKET LIFETIME -->
      <div class="bg-white brutal-card p-6 sm:p-8 flex flex-col justify-between relative">
        <div class="absolute -top-3.5 right-6">
          <span class="px-3 py-1 bg-coral text-white brutal-border font-mono text-[10px] font-black uppercase tracking-wider shadow-brutal-sm">
            ⚡ Sekali Bayar
          </span>
        </div>

        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="px-2.5 py-1 bg-coral text-white brutal-border font-mono text-xs font-black uppercase">FamKoji Lifetime</span>
            <span class="text-xs font-mono font-bold text-coral">Hemat Selamanya</span>
          </div>
          <h3 class="text-2xl font-black font-display text-ink">Family Lifetime</h3>
          <div class="mt-4 mb-6 flex items-baseline gap-1">
            <span class="text-4xl font-black font-display text-ink">Rp 150.000</span>
            <span class="text-xs font-mono text-ink/70">/ selamanya</span>
          </div>
          <p class="text-xs text-ink/80 font-medium mb-6 leading-relaxed">
            Investasi sekali bayar buat seumur hidup. Seluruh fitur Pro aktif selamanya tanpa kepikiran tagihan bulanan lagi.
          </p>

          <div class="space-y-3 text-xs font-bold border-t-2 border-ink pt-6 mb-6">
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Semua fitur lengkap Paket Family Pro</span>
            </div>
            <div class="flex items-center gap-2.5 text-ink">
              <span class="text-coral font-black text-sm">★</span>
              <span class="font-black text-ink">Bayar 1x aktif seumur hidup tanpa tagihan</span>
            </div>
            <div class="flex items-center gap-2.5 text-ink">
              <span class="text-coral font-black text-sm">★</span>
              <span class="font-black text-ink">Dapat seluruh pembaruan fitur di masa depan</span>
            </div>
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Seluruh anggota keluarga otomatis aktif</span>
            </div>
            <div class="flex items-center gap-2.5">
              <span class="text-brand text-sm">✓</span>
              <span>Bantuan prioritas untuk keluarga Anda</span>
            </div>
          </div>
        </div>

        <a href="{{ route('register') }}" class="w-full py-3.5 bg-accent hover:bg-brand hover:text-white text-ink text-center font-black text-xs uppercase tracking-wider brutal-btn">
          Pilih Paket Lifetime &rarr;
        </a>
      </div>
    </div>
  </section>

  <!-- KEUNGGULAN UTAMA (PRODUKSI NYATA, TANPA TESTIMONI / DATA PALSU) -->
  <section id="keunggulan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 reveal">
    <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
      <span class="px-3 py-1 bg-brand-soft text-brand-dark brutal-border font-mono text-xs font-bold uppercase tracking-wider">
        Kenapa FamKoji?
      </span>
      <h2 class="text-3xl sm:text-4xl font-black font-display text-ink tracking-tight">
        Alasan Pasangan & Keluarga Memilih FamKoji.
      </h2>
      <p class="text-ink/80 text-sm sm:text-base font-medium">
        Lahir dari obrolan nyata di meja makan keluarga Indonesia. Praktis, transparan, dan bikin rumah lebih adem.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- KEUNGGULAN 1 -->
      <div class="bg-white brutal-card p-6 flex flex-col justify-between hover:-translate-y-1.5 transition-transform">
        <div class="space-y-3">
          <div class="w-12 h-12 bg-accent brutal-border flex items-center justify-center text-xl shadow-brutal-sm font-black">
            🤝
          </div>
          <h3 class="text-xl font-black font-display text-ink">Buka-Bukaan Tanpa Curiga</h3>
          <p class="text-xs sm:text-sm font-semibold text-ink/80 leading-relaxed">
            Semua catatan belanja dapur, cicilan, dan celengan bersama bisa dicek bareng pasangan kapan aja. Pikiran tenang, gak ada lagi salah paham masalah duit.
          </p>
        </div>
        <div class="pt-4 border-t-2 border-ink mt-6 font-mono text-[11px] font-bold text-brand uppercase tracking-wider">
          ✦ Dompet Harmonis
        </div>
      </div>

      <!-- KEUNGGULAN 2 -->
      <div class="bg-white brutal-card p-6 flex flex-col justify-between hover:-translate-y-1.5 transition-transform">
        <div class="space-y-3">
          <div class="w-12 h-12 bg-coral text-white brutal-border flex items-center justify-center text-xl shadow-brutal-sm font-black">
            🎯
          </div>
          <h3 class="text-xl font-black font-display text-ink">Anak Makin Mandiri</h3>
          <p class="text-xs sm:text-sm font-semibold text-ink/80 leading-relaxed">
            Beresin kasur atau buang sampah gak perlu disuruh berkali-kali. Sistem checklist dan poin reward bikin anak semangat ngebantu orang tua di rumah.
          </p>
        </div>
        <div class="pt-4 border-t-2 border-ink mt-6 font-mono text-[11px] font-bold text-coral uppercase tracking-wider">
          ✦ Edukasi Nyata
        </div>
      </div>

      <!-- KEUNGGULAN 3 -->
      <div class="bg-white brutal-card p-6 flex flex-col justify-between hover:-translate-y-1.5 transition-transform">
        <div class="space-y-3">
          <div class="w-12 h-12 bg-brand text-white brutal-border flex items-center justify-center text-xl shadow-brutal-sm font-black">
            🔒
          </div>
          <h3 class="text-xl font-black font-display text-ink">100% Privat & Aman</h3>
          <p class="text-xs sm:text-sm font-semibold text-ink/80 leading-relaxed">
            Catatan keluarga Anda tertutup rapat. Cuma yang punya link undangan atau kode keluarga yang bisa masuk, tanpa takut data keluarga bocor ke mana-mana.
          </p>
        </div>
        <div class="pt-4 border-t-2 border-ink mt-6 font-mono text-[11px] font-bold text-brand uppercase tracking-wider">
          ✦ Ruang Khusus Rumah
        </div>
      </div>
    </div>
  </section>

  <!-- BOTTOM CTA CALLOUT -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 reveal">
    <div class="bg-accent brutal-card p-8 sm:p-14 text-center relative overflow-hidden">
      <div class="max-w-3xl mx-auto space-y-6 relative z-10">
        <span class="px-3 py-1 bg-white brutal-border font-mono text-xs font-black uppercase tracking-wider inline-block">
          GRATIS DIPAKAI &bull; PRO CUMA RP 20RB/BULAN
        </span>
        <h2 class="text-3xl sm:text-5xl font-black font-display text-ink tracking-tight">
          Yuk, Bikin Rumah Tangga Lebih Rapi & Kompak Hari Ini!
        </h2>
        <p class="text-base sm:text-lg font-bold text-ink/85 max-w-xl mx-auto">
          Cukup 1 orang yang bikin akun. Anggota keluarga yang lain tinggal klik link undangan dari WhatsApp.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
          <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-brand text-white font-black text-base brutal-btn">
            Bikin Ruang Rumah Sekarang &rarr;
          </a>
          <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-white text-ink font-bold text-base brutal-btn">
            Masuk ke Akun Keluarga &rarr;
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-white brutal-border border-x-0 border-b-0 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6 text-xs font-bold text-ink/80">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-brand-soft brutal-border rounded-lg p-0.5 flex items-center justify-center shadow-[1.5px_1.5px_0px_#121614] shrink-0">
          <img src="{{ asset('icons/new_logo.png') }}" alt="FamKoji Logo" class="w-full h-full object-contain">
        </div>
        <span class="font-display font-black text-lg text-ink">FamKoji<span class="text-brand">.</span></span>
        <span>— Aplikasi Kompak Rumah Tangga Indonesia</span>
      </div>

      <div class="flex items-center gap-6">
        <a href="{{ route('landing') }}" class="hover:text-brand">Beranda</a>
        <a href="#fitur" class="hover:text-brand">Fitur Rumah</a>
        <a href="#keunggulan" class="hover:text-brand">Keunggulan</a>
        <a href="#harga" class="hover:text-brand">Paket</a>
        <a href="{{ route('login') }}" class="hover:text-brand">Masuk</a>
        <a href="{{ route('register') }}" class="hover:text-brand">Daftar</a>
      </div>

      <div class="font-mono text-[11px] text-ink/60">
        &copy; {{ date('Y') }} FamKoji. Seluruh hak cipta dilindungi.
      </div>
    </div>
  </footer>

  <!-- LIGHTWEIGHT NATIVE JAVASCRIPT ANIMATIONS -->
  <script>
    // ponytail: native canvas confetti, minimal allocations
    function fireConfetti(x, y) {
      const canvas = document.createElement('canvas');
      canvas.style.position = 'fixed';
      canvas.style.inset = '0';
      canvas.style.width = '100vw';
      canvas.style.height = '100vh';
      canvas.style.pointerEvents = 'none';
      canvas.style.zIndex = '9999';
      document.body.appendChild(canvas);
      const ctx = canvas.getContext('2d');
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;

      const colors = ['#34D399', '#2F5233', '#E07A5F', '#81B0C4', '#121614', '#87A987'];
      const cx = x || window.innerWidth / 2;
      const cy = y || window.innerHeight / 2;

      const pieces = Array.from({ length: 45 }, () => ({
        x: cx,
        y: cy,
        vx: (Math.random() - 0.5) * 14,
        vy: (Math.random() - 0.8) * 15,
        size: Math.random() * 8 + 4,
        color: colors[Math.floor(Math.random() * colors.length)],
        rot: Math.random() * 360,
        vrot: (Math.random() - 0.5) * 12
      }));

      let frame = 0;
      function loop() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pieces.forEach(p => {
          p.x += p.vx;
          p.y += p.vy;
          p.vy += 0.45;
          p.rot += p.vrot;
          ctx.save();
          ctx.translate(p.x, p.y);
          ctx.rotate((p.rot * Math.PI) / 180);
          ctx.fillStyle = p.color;
          ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
          ctx.restore();
        });
        frame++;
        if (frame < 60) requestAnimationFrame(loop);
        else canvas.remove();
      }
      requestAnimationFrame(loop);
    }

    // Interactive hero demo task checkbox
    let heroTaskDone = false;
    function toggleHeroTask(el, event) {
      heroTaskDone = !heroTaskDone;
      const check = document.getElementById('hero-task-check');
      const text = document.getElementById('hero-task-text');
      const title = document.getElementById('hero-task-status-title');
      const pct = document.getElementById('hero-task-pct');

      if (heroTaskDone) {
        check.innerText = '☑';
        check.className = 'text-brand font-bold text-sm';
        text.className = 'line-through text-ink/50';
        title.innerText = 'Tugas Hari Ini (4/4) 🎉';
        pct.innerText = '100% Selesai';
        pct.className = 'text-xs font-mono font-bold text-white bg-brand px-1 brutal-border';
        fireConfetti(event ? event.clientX : window.innerWidth * 0.7, event ? event.clientY : window.innerHeight * 0.4);
      } else {
        check.innerText = '☐';
        check.className = 'text-coral font-bold text-sm';
        text.className = '';
        title.innerText = 'Tugas Hari Ini (3/4)';
        pct.innerText = '75% Selesai';
        pct.className = 'text-xs font-mono font-bold text-brand';
      }
    }

    // ponytail: simple IntersectionObserver for scroll reveal and number counting
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          if (entry.target.classList.contains('count-up') && !entry.target.dataset.counted) {
            entry.target.dataset.counted = 'true';
            animateCounter(entry.target);
          }
        }
      });
    }, { threshold: 0.12 });

    document.querySelectorAll('.reveal, .count-up').forEach(el => observer.observe(el));

    function animateCounter(el) {
      const target = parseFloat(el.dataset.target);
      const suffix = el.dataset.suffix || '';
      const prefix = el.dataset.prefix || '';
      const isFloat = el.dataset.float === 'true';
      const duration = 1200;
      const start = performance.now();

      function step(now) {
        const progress = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        const current = target * ease;
        el.textContent = prefix + (isFloat ? current.toFixed(1) : Math.floor(current).toLocaleString('id-ID')) + suffix;
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = prefix + (isFloat ? target.toFixed(1) : target.toLocaleString('id-ID')) + suffix;
      }
      requestAnimationFrame(step);
    }

    // PWA Service Worker & Desktop Install Prompt
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
      });
    }

    // PWA Universal Install Handler
    let pwaInstallPrompt = null;
    const heroInstallBtn = document.getElementById('pwa-hero-install-btn');
    const navInstallBtn = document.getElementById('pwa-nav-install-btn');
    const guideModal = document.getElementById('pwa-guide-modal');
    const guideIos = document.getElementById('pwa-guide-ios');
    const guideOther = document.getElementById('pwa-guide-other');
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
      || window.matchMedia('(display-mode: fullscreen)').matches
      || window.matchMedia('(display-mode: minimal-ui)').matches
      || window.navigator.standalone === true;
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

    // Sembunyikan jika sudah terpasang & dibuka di mode PWA (standalone)
    if (isStandalone) {
      document.documentElement.classList.add('is-pwa');
      if (heroInstallBtn) heroInstallBtn.style.display = 'none';
      if (navInstallBtn) navInstallBtn.style.display = 'none';
    }

    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      pwaInstallPrompt = e;
    });

    window.openPwaGuideModal = function() {
      if (!guideModal) return;
      if (isIos) {
        if (guideIos) guideIos.classList.remove('hidden');
        if (guideOther) guideOther.classList.add('hidden');
      } else {
        if (guideIos) guideIos.classList.add('hidden');
        if (guideOther) guideOther.classList.remove('hidden');
      }
      guideModal.classList.remove('hidden');
    };

    window.closePwaGuideModal = function() {
      if (guideModal) guideModal.classList.add('hidden');
    };

    async function triggerInstall() {
      if (pwaInstallPrompt) {
        pwaInstallPrompt.prompt();
        const choice = await pwaInstallPrompt.userChoice;
        if (choice && choice.outcome === 'accepted') {
          pwaInstallPrompt = null;
          if (heroInstallBtn) heroInstallBtn.style.display = 'none';
          if (navInstallBtn) navInstallBtn.style.display = 'none';
        }
      } else {
        window.openPwaGuideModal();
      }
    }

    if (heroInstallBtn) heroInstallBtn.addEventListener('click', triggerInstall);
    if (navInstallBtn) navInstallBtn.addEventListener('click', triggerInstall);

    window.addEventListener('appinstalled', () => {
      pwaInstallPrompt = null;
      if (heroInstallBtn) heroInstallBtn.style.display = 'none';
      if (navInstallBtn) navInstallBtn.style.display = 'none';
      window.closePwaGuideModal();
    });
  </script>

  <!-- MODAL PANDUAN INSTALL PWA -->
  <div id="pwa-guide-modal" class="fixed inset-0 z-50 hidden bg-ink/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white brutal-border p-6 max-w-sm sm:max-w-md w-full shadow-brutal space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-2">
          <span class="text-2xl">📲</span>
          <h3 class="font-display font-black text-lg text-ink">Pasang FamKoji</h3>
        </div>
        <button type="button" onclick="closePwaGuideModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border text-ink font-bold flex items-center justify-center cursor-pointer">✕</button>
      </div>

      <!-- PANDUAN IOS SAFARI -->
      <div id="pwa-guide-ios" class="hidden space-y-3 text-sm text-ink">
        <p class="font-bold text-brand">Cara Pasang di iPhone / iPad (Safari):</p>
        <ol class="list-decimal list-inside space-y-2 text-xs sm:text-sm font-mono bg-cream p-3 brutal-border">
          <li>Ketuk tombol <strong class="bg-white px-1.5 py-0.5 brutal-border">Share (⎋)</strong> di bilah bawah Safari.</li>
          <li>Geser ke bawah lalu pilih <strong class="bg-accent px-1.5 py-0.5 brutal-border text-ink">Tambah ke Layar Utama</strong> (+).</li>
          <li>Ketuk <strong class="underline">Tambah</strong> di pojok kanan atas.</li>
        </ol>
      </div>

      <!-- PANDUAN CHROME / ANDROID / DESKTOP -->
      <div id="pwa-guide-other" class="space-y-3 text-sm text-ink">
        <p class="font-bold text-brand">Cara Pasang di Browser HP / Laptop:</p>
        <ol class="list-decimal list-inside space-y-2 text-xs sm:text-sm font-mono bg-cream p-3 brutal-border">
          <li>Ketuk menu <strong class="bg-white px-1.5 py-0.5 brutal-border">titik tiga (⋮)</strong> di browser Anda.</li>
          <li>Pilih menu <strong class="bg-accent px-1.5 py-0.5 brutal-border text-ink">Install Aplikasi</strong> atau <strong class="bg-accent px-1.5 py-0.5 brutal-border text-ink">Tambahkan ke Layar Utama</strong>.</li>
        </ol>
      </div>

      <button type="button" onclick="closePwaGuideModal()" class="w-full py-2.5 bg-brand text-white font-black text-xs sm:text-sm brutal-btn cursor-pointer">
        Mengerti
      </button>
    </div>
  </div>

</body>
</html>
