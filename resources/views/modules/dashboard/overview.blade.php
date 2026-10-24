<!-- TAB 1: OVERVIEW / RINGKASAN UTAMA (MENDUKUNG PAKET FREE & PRO DENGAN OVERLAY UPGRADE) -->
<div id="view-overview" class="space-y-4 sm:space-y-5">

  <!-- SAPAAN HANGAT WAKTU (NEOBRUTALISM WELCOME CARD) -->
  @php
    $hour = (int) now()->format('H');
    if ($hour >= 4 && $hour < 11) {
      $greetingTime = 'Selamat Pagi';
      $greetingEmoji = '🌅';
      $warmQuote = 'Awali hari penuh berkah dan kehangatan bersama keluarga tercinta.';
      $timeBadgeColor = 'bg-accent text-ink';
    } elseif ($hour >= 11 && $hour < 15) {
      $greetingTime = 'Selamat Siang';
      $greetingEmoji = '☀️';
      $warmQuote = 'Tetap semangat beraktivitas hari ini, jangan lupa jaga kesehatan dan rehat sejenak.';
      $timeBadgeColor = 'bg-[#F9D949] text-ink';
    } elseif ($hour >= 15 && $hour < 18) {
      $greetingTime = 'Selamat Sore';
      $greetingEmoji = '🌇';
      $warmQuote = 'Hari perlahan senja, saatnya bersantai dan berkumpul kembali bersama orang tersayang.';
      $timeBadgeColor = 'bg-coral text-white';
    } else {
      $greetingTime = 'Selamat Malam';
      $greetingEmoji = '🌙';
      $warmQuote = 'Selamat beristirahat dengan damai. Terima kasih atas cinta dan kerja kerasmu hari ini.';
      $timeBadgeColor = 'bg-ink text-white';
    }
  @endphp

  <div class="bg-white brutal-card brutal-tilt p-3.5 sm:p-4.5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-all">
    
    <!-- SISI KIRI: ICON ANIMASI & TEKS SAPAAN -->
    <div class="flex items-start sm:items-center gap-3.5 min-w-0">
      <!-- ICON CUACA / WAKTU HIDUP -->
      <div class="w-12 h-12 sm:w-14 sm:h-14 bg-cream brutal-border p-2 flex items-center justify-center shrink-0 shadow-brutal-sm">
        @if($hour >= 4 && $hour < 15)
          <!-- SVG Matahari Berputar -->
          <svg class="w-full h-full" viewBox="0 0 36 36" fill="none">
            <g class="animate-sun-spin">
              <path d="M18 2V5M18 31V34M2 18H5M31 18H34M6.7 6.7L9 9M27 27L29.3 29.3M6.7 29.3L9 27M27 9L29.3 6.7" stroke="#121614" stroke-width="2.5" stroke-linecap="round"/>
            </g>
            <circle cx="18" cy="18" r="8" fill="#F9D949" stroke="#121614" stroke-width="2.5" />
            <circle cx="15.5" cy="16.5" r="1.2" fill="#121614" />
            <circle cx="20.5" cy="16.5" r="1.2" fill="#121614" />
            <path d="M15.5 19.5C16.2 20.8 19.8 20.8 20.5 19.5" stroke="#121614" stroke-width="1.5" stroke-linecap="round" />
          </svg>
        @elseif($hour >= 15 && $hour < 18)
          <!-- SVG Matahari Senja & Awan -->
          <svg class="w-full h-full" viewBox="0 0 36 36" fill="none">
            <circle cx="18" cy="17" r="8" fill="#E07A5F" stroke="#121614" stroke-width="2.5" />
            <path d="M4 27H32" stroke="#121614" stroke-width="2.5" stroke-linecap="round"/>
            <path d="M8 30.5H28" stroke="#121614" stroke-width="2" stroke-linecap="round"/>
            <g class="animate-cloud-drift">
              <path d="M13 20C13 18.5 14.5 17.5 16 18C16.8 16.5 19 16.5 20 18C21.5 18 22.5 19 22.5 20.5C22.5 22 21 22.5 19.5 22.5H14C13 22.5 13 21.5 13 20Z" fill="#FBF8EF" stroke="#121614" stroke-width="2" />
            </g>
          </svg>
        @else
          <!-- SVG Bulan Sabit & Bintang Berkedip -->
          <svg class="w-full h-full" viewBox="0 0 36 36" fill="none">
            <path class="animate-star-twinkle" d="M7 11L8 13L10 14L8 15L7 17L6 15L4 14L6 13L7 11Z" fill="#34D399" stroke="#121614" stroke-width="1.2" />
            <path class="animate-star-twinkle" style="animation-delay: 1.1s;" d="M28 8L28.8 9.5L30.5 10.2L28.8 11L28 12.5L27.2 11L25.5 10.2L27.2 9.5L28 8Z" fill="#F9D949" stroke="#121614" stroke-width="1.2" />
            <path class="animate-star-twinkle" style="animation-delay: 0.5s;" d="M29 25L29.6 26.2L31 26.8L29.6 27.4L29 28.6L28.4 27.4L27 26.8L28.4 26.2L29 25Z" fill="#81B0C4" stroke="#121614" stroke-width="1.2" />
            <g class="pulse-gentle">
              <path d="M20 9C15 9 11 13 11 18C11 23 15 27 20 27C22.5 27 24.5 26 26 24.5C21.5 24 18 20.5 18 16C18 12.8 19.8 10.2 22.5 9.4C21.7 9.1 20.8 9 20 9Z" fill="#F9D949" stroke="#121614" stroke-width="2.5" stroke-linejoin="round"/>
              <circle cx="16" cy="17" r="1" fill="#121614"/>
            </g>
          </svg>
        @endif
      </div>

      <div class="min-w-0 space-y-1">
        <div class="flex items-center gap-2 flex-wrap">
          <h1 class="text-base sm:text-xl font-black font-display text-ink leading-tight">
            {{ $greetingTime }}, <span class="text-brand">{{ $user->name }}</span>!
          </h1>
          <span class="px-2 py-0.5 {{ $timeBadgeColor }} brutal-border text-[9px] sm:text-[10px] font-mono font-black uppercase shadow-[1.5px_1.5px_0px_#121614] shrink-0 sticker-wiggle cursor-default">
            {{ $greetingTime }}
          </span>
        </div>
        <p class="text-xs sm:text-sm font-mono text-ink/80 leading-relaxed">
          {{ $warmQuote }}
        </p>
      </div>
    </div>

    <!-- SISI KANAN: RETRO DIGITAL CLOCK + BADGE KELUARGA & TANGGAL -->
    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 self-start lg:self-center shrink-0 pt-2 lg:pt-0 border-t border-ink/10 lg:border-t-0 w-full lg:w-auto justify-between lg:justify-end">
      
      <!-- RETRO LCD DIGITAL CLOCK -->
      <div class="bg-ink text-accent brutal-border px-3 py-1.5 flex items-center gap-2 shadow-[2px_2px_0px_#121614] shrink-0 font-mono select-none" title="Waktu Nyata Lokal (WIB)">
        <span class="w-2 h-2 rounded-full bg-accent animate-ping"></span>
        <div class="flex items-baseline text-xs sm:text-sm font-black tracking-widest text-[#34D399]">
          <span id="greetings-clock-hours">--</span>
          <span class="animate-colon-blink px-0.5 text-white">:</span>
          <span id="greetings-clock-minutes">--</span>
          <span class="animate-colon-blink px-0.5 text-white">:</span>
          <span id="greetings-clock-seconds" class="text-white text-[11px] sm:text-xs">--</span>
          <span class="text-[9px] text-accent/80 ml-1.5 font-bold uppercase">WIB</span>
        </div>
      </div>

      <!-- RUANG KELUARGA & TANGGAL -->
      <div class="flex items-center gap-1.5">
        <div class="px-2.5 py-1.5 bg-cream brutal-border text-[11px] font-mono font-bold text-ink flex items-center gap-1.5 shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] transition-transform">
          <span>🏡</span>
          <span class="font-black">{{ $family->name }}</span>
        </div>
        <div class="px-2.5 py-1.5 bg-white brutal-border text-[11px] font-mono font-bold text-ink/80 flex items-center gap-1.5 shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] transition-transform">
          <span>📅</span>
          <span>{{ now()->locale('id')->isoFormat('D MMM Y') }}</span>
        </div>
      </div>

    </div>
  </div>

  <!-- BANNER FOTO BERANDA DARI ALBUM (BINGKAI NEOBRUTALISM) -->
  @php
    $bannerMedia = $family->bannerMedia;
  @endphp

  @if($bannerMedia && $bannerMedia->isPhoto())
    <div class="relative bg-white brutal-card brutal-tilt p-2 sm:p-2.5 overflow-hidden group">
      <!-- BINGKAI FOTO NEOBRUTALISM (INTERAKTIF: KLIK BUKA LIGHTBOX) -->
      <div onclick="showImagePreview('{{ $bannerMedia->file_url }}', '{{ addslashes($bannerMedia->caption ?? 'Banner Keluarga ' . $family->name) }}')"
           class="relative w-full h-48 sm:h-60 md:h-72 lg:h-80 brutal-border overflow-hidden bg-ink/5 cursor-zoom-in"
           title="Klik untuk melihat foto layar penuh">
        <img src="{{ $bannerMedia->file_url }}" 
             alt="{{ $bannerMedia->caption ?? 'Banner Beranda Keluarga ' . $family->name }}" 
             class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500 ease-out">

        <!-- ZOOM HINT PILL (MUNCUL SAAT HOVER) -->
        <div class="absolute inset-0 bg-ink/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center pointer-events-none z-10">
          <span class="px-3 py-1.5 bg-white text-ink brutal-border font-mono font-black text-xs uppercase tracking-wider shadow-brutal flex items-center gap-2 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-200">
            <span>🔍</span>
            <span>Lihat Foto Penuh</span>
          </span>
        </div>

        <!-- DEKORATIF: STAMP / BADGE NEOBRUTALIS DI SUDUT ATAS -->
        <div class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3 flex items-center gap-1.5 flex-wrap z-20" onclick="event.stopPropagation()">
          <span class="px-2.5 py-1 bg-accent text-ink brutal-border text-[10px] sm:text-xs font-mono font-black uppercase tracking-wider shadow-brutal-sm flex items-center gap-1.5 sticker-wiggle cursor-pointer"
                onclick="showImagePreview('{{ $bannerMedia->file_url }}', '{{ addslashes($bannerMedia->caption ?? 'Banner Keluarga ' . $family->name) }}')"
                title="Buka pratinjau foto">
            <span>🖼️</span>
            <span>Momen Keluarga</span>
          </span>
          @if($bannerMedia->album)
            <a href="{{ route('albums.show', $bannerMedia->album) }}" 
               class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 bg-white text-ink brutal-border text-[10px] font-mono font-bold hover:bg-cream shadow-brutal-sm transition-all hover:translate-x-[-1px] hover:translate-y-[-1px] active:scale-95"
               title="Lihat Album Foto">
              <span>📁</span>
              <span class="max-w-[150px] truncate">{{ $bannerMedia->album->title }}</span>
            </a>
          @endif
        </div>

        <!-- ACTION BUTTONS: KANAN ATAS -->
        <div class="absolute top-2.5 right-2.5 sm:top-3 sm:right-3 flex items-center gap-1.5 z-20" onclick="event.stopPropagation()">
          <a href="{{ route('albums.index') }}" 
             class="px-2.5 py-1 bg-white hover:bg-cream text-ink brutal-border text-[10px] sm:text-xs font-mono font-black uppercase tracking-wider shadow-brutal-sm flex items-center gap-1 transition-all hover:translate-x-[-1px] hover:translate-y-[-1px] active:scale-95"
             title="Pilih foto lain dari Album">
            <span>🔄</span>
            <span class="hidden sm:inline">Ganti Banner</span>
          </a>
          <form action="{{ route('albums.banner.remove') }}" method="POST" class="inline" onsubmit="return confirm('Lepas banner ini dari beranda?');">
            @csrf
            <button type="submit" 
                    class="p-1 sm:px-2 sm:py-1 bg-white hover:bg-coral hover:text-white text-ink brutal-border text-[10px] sm:text-xs font-mono font-black shadow-brutal-sm transition-all hover:translate-x-[-1px] hover:translate-y-[-1px] active:scale-95"
                    title="Lepas banner beranda">
              ✕
            </button>
          </form>
        </div>

        <!-- CAPTION STRIP NEOBRUTALIS (GRADIENT & OVERLAY) -->
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/90 via-ink/65 to-transparent pt-10 pb-3 sm:pb-3.5 px-3.5 sm:px-5 text-white flex flex-col sm:flex-row sm:items-end justify-between gap-1 z-20 pointer-events-none">
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span class="px-1.5 py-0.2 bg-white/20 text-white brutal-border border-white text-[9px] font-mono font-black uppercase tracking-wider">
                BERANDA
              </span>
              <h2 class="text-base sm:text-lg md:text-xl font-black font-display text-white tracking-wide drop-shadow-sm truncate">
                Rumah Tangga {{ $family->name }}
              </h2>
            </div>
            @if($bannerMedia->caption)
              <p class="text-[11px] sm:text-xs font-mono text-white/95 line-clamp-1 drop-shadow-sm mt-0.5">
                "{{ $bannerMedia->caption }}"
              </p>
            @endif
          </div>
          <div class="text-[10px] font-mono text-white/80 shrink-0 self-start sm:self-auto flex items-center gap-1.5">
            <span>Foto oleh <strong class="text-white">{{ $bannerMedia->user->name ?? 'Keluarga' }}</strong></span>
            <span>&bull;</span>
            <span>{{ $bannerMedia->created_at->translatedFormat('d M Y') }}</span>
          </div>
        </div>
      </div>
    </div>
  @else
    <!-- PLACEHOLDER BANNER BERANDA JIKA BELUM ADA FOTO DIPILIH -->
    <div class="bg-white brutal-card brutal-tilt p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 border-dashed">
      <div class="flex items-center gap-3.5 text-center sm:text-left">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-accent/40 brutal-border flex items-center justify-center text-2xl sm:text-3xl shrink-0 shadow-brutal-sm pulse-gentle">
          🖼️
        </div>
        <div class="space-y-0.5">
          <div class="flex items-center gap-2 justify-center sm:justify-start">
            <span class="px-2 py-0.5 bg-brand-soft text-brand brutal-border font-mono text-[9px] font-black uppercase">
              Momen Keluarga
            </span>
            <span class="text-xs font-mono text-ink/60">Belum ada banner aktif</span>
          </div>
          <h2 class="text-sm sm:text-base font-black font-display text-ink">
            Pajang Foto Favorit Keluarga di Sini
          </h2>
          <p class="text-xs font-mono text-ink/70 max-w-xl">
            Buka album foto keluarga, pilih foto terbaik, dan klik "Jadikan Banner Beranda" agar beranda semakin hangat.
          </p>
        </div>
      </div>

      <a href="{{ route('albums.index') }}" 
         class="px-4 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider shrink-0 flex items-center gap-2 shadow-brutal-sm active:scale-95 transition-all">
        <span>📸 Buka Album Foto</span>
        <span>&rarr;</span>
      </a>
    </div>
  @endif

  <!-- BANNER STATUS PAKET BERLANGGANAN -->
  @if($isPro)
    <div class="p-2.5 sm:p-3 bg-accent/25 brutal-border flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="text-lg sm:text-xl">👑</span>
        <div class="text-xs font-mono">
          <span class="font-black text-ink">Paket FamKoji Pro Aktif</span>
          <span class="text-ink/70"> &bull; Fitur Kas, Tugas, Kalender, & Linimasa terbuka penuh</span>
        </div>
      </div>
      <span class="px-2 py-0.5 bg-brand text-white brutal-border font-mono text-[9px] sm:text-[10px] font-black uppercase self-start sm:self-auto">
        Akses Pro Lengkap
      </span>
    </div>
  @else
    <div class="p-2.5 sm:p-3 bg-cream brutal-border flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="text-lg sm:text-xl">⭐</span>
        <div class="text-xs font-mono">
          <span class="font-black text-ink">Paket Free</span>
          <span class="text-ink/70"> &bull; Kas & Tugas aktif tanpa batas. Kalender & Linimasa tersedia di Pro.</span>
        </div>
      </div>
      <button type="button" onclick="openPaywallModal('dashboard-banner')" class="px-2.5 py-1 bg-accent hover:bg-brand hover:text-white brutal-btn font-mono font-black text-xs uppercase tracking-wider self-start sm:self-auto">
        👑 Upgrade ke Pro (Rp 20rb) &rarr;
      </button>
    </div>
  @endif

  <!-- BARIS 1: 4 KARTU ANGKA UTAMA -->
  <div id="overview-stat-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
    
    <!-- KARTU 1: SALDO KAS -->
    <div class="bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between hover:translate-x-[-2px] hover:translate-y-[-2px] transition-transform">
      <div class="flex items-center justify-between text-xs font-mono font-bold text-ink/70">
        <span>SISA KAS RUMAH</span>
        <span class="text-xl sm:text-2xl">💰</span>
      </div>
      <div class="text-2xl sm:text-3xl font-black font-mono {{ $netBalance >= 0 ? 'text-brand' : 'text-coral' }} truncate my-1.5" title="Rp {{ number_format($netBalance, 0, ',', '.') }}">
        Rp {{ number_format($netBalance, 0, ',', '.') }}
      </div>
      <div class="text-[11px] font-mono text-ink/70 pt-1.5 border-t border-ink/10 flex items-center justify-between">
        <span>Total Masuk:</span>
        <span class="text-brand font-black truncate max-w-[120px] text-right" title="Rp {{ number_format($totalIncome, 0, ',', '.') }}">+Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
      </div>
    </div>

    <!-- KARTU 2: TUGAS HARI INI -->
    <div id="overview-chore-progress" class="bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between hover:translate-x-[-2px] hover:translate-y-[-2px] transition-transform">
      <div class="flex items-center justify-between text-xs font-mono font-bold text-ink/70">
        <span>TUGAS RUMAH</span>
        <span class="text-xl sm:text-2xl">🧹</span>
      </div>
      <div class="text-2xl sm:text-3xl font-black font-mono text-ink my-1.5 flex items-baseline gap-2">
        <span>{{ $choresCompleted }}/{{ $choresTotal }}</span>
        <span class="text-xs sm:text-sm font-sans font-bold text-ink/60">Beres ({{ $choreCompletionRate }}%)</span>
      </div>
      <div class="pt-1.5 border-t border-ink/10">
        <div class="w-full bg-cream h-2 brutal-border overflow-hidden">
          <div class="bg-brand h-full transition-all duration-300" style="width: {{ $choreCompletionRate }}%"></div>
        </div>
      </div>
    </div>

    <!-- KARTU 3: POIN KELUARGA -->
    <div class="bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between hover:translate-x-[-2px] hover:translate-y-[-2px] transition-transform">
      <div class="flex items-center justify-between text-xs font-mono font-bold text-ink/70">
        <span>POIN KELUARGA</span>
        <span class="text-xl sm:text-2xl">⭐</span>
      </div>
      <div class="text-2xl sm:text-3xl font-black font-mono text-ink my-1.5 flex items-baseline gap-1.5 truncate">
        <span>{{ number_format($members->sum('points')) }}</span>
        <span class="text-xs font-mono font-bold text-ink/60 uppercase">Poin</span>
      </div>
      <div class="text-[11px] font-mono text-ink/70 pt-1.5 border-t border-ink/10 truncate flex items-center justify-between">
        <span class="flex-shrink-0">Paling rajin:</span>
        <span class="font-black text-ink truncate ml-1">{{ $members->first() ? $members->first()->name : '-' }}</span>
      </div>
    </div>

    <!-- KARTU 4: AGENDA TERDEKAT -->
    <div class="bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between relative overflow-hidden {{ $isPro ? 'bg-accent/25' : '' }}">
      @if($isPro)
        <div class="flex items-center justify-between text-xs font-mono font-black text-ink">
          <span>AGENDA TERDEKAT</span>
          <div class="flex items-center gap-1">
            <span class="px-1.5 py-0.2 bg-white brutal-border font-mono text-[9px] font-black">👑 PRO</span>
            <span class="text-xl sm:text-2xl">⏰</span>
          </div>
        </div>
        @if($upcomingEvents->count() > 0)
          @php $nextEv = $upcomingEvents->first(); @endphp
          <div class="text-base sm:text-lg font-black font-display text-ink truncate my-1.5 leading-snug" title="{{ $nextEv->title }}">
            {{ $nextEv->title }}
          </div>
          <div class="text-[11px] font-mono font-bold text-ink/80 pt-1.5 border-t border-ink/20 truncate">
            🗓️ {{ \Carbon\Carbon::parse($nextEv->start_time)->locale('id')->isoFormat('D MMM, HH:mm') }} &bull; {{ $nextEv->location ?? 'Rumah' }}
          </div>
        @else
          <div class="text-base sm:text-lg font-black font-display text-ink/70 my-1.5">
            Belum ada jadwal
          </div>
          <div class="text-[11px] font-mono text-ink/60 pt-1.5 border-t border-ink/20">
            Semua kegiatan tuntas
          </div>
        @endif
      @else
        <!-- FREE TIER: OPACITY RENDAH + OVERLAY UPGRADE -->
        <div class="opacity-30 select-none pointer-events-none">
          <div class="flex items-center justify-between text-xs font-mono font-bold text-ink/70">
            <span>AGENDA TERDEKAT</span>
            <span class="text-xl sm:text-2xl">⏰</span>
          </div>
          <div class="text-base sm:text-lg font-black font-display text-ink my-1.5">
            Kontrol Dokter Anak
          </div>
          <div class="text-[11px] font-mono text-ink/60 pt-1.5 border-t border-ink/20">
            🗓️ Besok, 10:00 &bull; RS Hermina
          </div>
        </div>

        <!-- OVERLAY UPGRADE -->
        <div class="absolute inset-0 bg-cream/80 backdrop-blur-[1px] flex flex-col items-center justify-center p-3 text-center z-10">
          <span class="text-lg mb-0.5">🔒</span>
          <span class="text-xs font-black font-display text-ink leading-tight">Agenda Kalender</span>
          <span class="text-[10px] font-mono text-ink/70 mb-1.5">Terkunci di Paket Free</span>
          <button type="button" onclick="openPaywallModal('calendar')" class="px-2.5 py-1 bg-accent hover:bg-brand hover:text-white brutal-btn text-[10px] font-mono font-black uppercase tracking-wider">
            👑 Upgrade Pro &rarr;
          </button>
        </div>
      @endif
    </div>

  </div>

  <!-- BARIS 2: 3 GRAFIK UTAMA SEJAJAR -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-4">
    
    <!-- GRAFIK 1: ARUS KAS (6 KOLOM) -->
    <div class="lg:col-span-6 bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between pb-2 border-b-2 border-ink">
        <div class="flex items-center gap-2">
          <span class="text-base sm:text-lg">📈</span>
          <h3 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">
            Uang Masuk & Keluar
          </h3>
          <span class="text-[10px] font-mono text-ink/50 hidden sm:inline">(Aktivitas Terakhir)</span>
        </div>
        <div class="flex items-center gap-1 font-mono text-xs font-bold">
          <button type="button" onclick="setCashflowChartType('bar')" id="btn-chart-bar" class="px-2 py-0.5 bg-accent brutal-border text-ink hover:bg-accent text-xs">Bar</button>
          <button type="button" onclick="setCashflowChartType('line')" id="btn-chart-line" class="px-2 py-0.5 bg-white brutal-border text-ink hover:bg-cream text-xs">Garis</button>
        </div>
      </div>
      <div class="relative h-44 sm:h-48 w-full mt-2">
        <canvas id="cashflowChart"></canvas>
      </div>
    </div>

    <!-- GRAFIK 2: KATEGORI PENGELUARAN (3 KOLOM) -->
    <div class="lg:col-span-3 bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between pb-2 border-b-2 border-ink">
        <div class="flex items-center gap-1.5">
          <span class="text-base sm:text-lg">🍩</span>
          <h3 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">
            Pos Biaya
          </h3>
        </div>
        <span class="text-[10px] font-mono font-bold text-ink/60">Top Pengeluaran</span>
      </div>
      <div class="relative h-44 sm:h-48 w-full flex items-center justify-center mt-2">
        @if($expensesByCategory->count() > 0)
          <canvas id="categoryDonutChart"></canvas>
        @else
          <div class="p-3 text-center text-xs font-mono text-ink/60 bg-cream brutal-border w-full">
            Belum ada catatan biaya.
          </div>
        @endif
      </div>
    </div>

    <!-- GRAFIK 3: POIN & TUGAS ANGGOTA (3 KOLOM) -->
    <div class="lg:col-span-3 bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between pb-2 border-b-2 border-ink">
        <div class="flex items-center gap-1.5">
          <span class="text-base sm:text-lg">🏆</span>
          <h3 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">
            Poin Anggota
          </h3>
        </div>
        <div class="flex items-center gap-1 font-mono text-[10px] font-bold text-ink/60">
          <span>{{ $members->count() > 5 ? 'Top 5' : $members->count() . ' orang' }}</span>
          @if($members->count() > 5)
            <a href="{{ route('members.index') }}" class="text-brand font-black hover:underline" title="Lihat semua anggota">&rarr;</a>
          @endif
        </div>
      </div>
      <div class="relative h-44 sm:h-48 w-full mt-2">
        <canvas id="memberPointsChart"></canvas>
      </div>
    </div>

  </div>

  <!-- BARIS 3: RINGKASAN DATA RIIL (KOMPAK DENGAN INNER SCROLL) -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5 sm:gap-4">
    
    <!-- KOLOM 1: TUGAS HARI INI + DONUT RING -->
    <div id="overview-chore-widget" class="bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between h-full">
      <div class="flex items-center justify-between pb-2 border-b-2 border-ink mb-2.5">
        <div class="flex items-center gap-1.5">
          <span class="text-base">📋</span>
          <h3 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">Tugas Rumah</h3>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-[11px] font-mono font-bold text-ink/70">{{ $choresTotal }} tugas</span>
          <a href="{{ route('chores.index') }}" class="text-[10px] font-mono font-black text-brand hover:underline">Semua &rarr;</a>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <!-- RING DONUT -->
        <div class="relative w-16 h-16 sm:w-20 sm:h-20 flex-shrink-0 flex items-center justify-center">
          <canvas id="choreRingChart"></canvas>
          <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <span class="text-[11px] sm:text-xs font-mono font-black text-ink">{{ $choreCompletionRate }}%</span>
          </div>
        </div>

        <!-- LIST TUGAS COMPACT DENGAN SCROLL CONTAINMENT -->
        <div class="flex-1 min-w-0 space-y-1.5 max-h-[145px] overflow-y-auto custom-scrollbar pr-1">
          @forelse($allChores->take(5) as $chore)
            <div class="p-1.5 sm:p-2 {{ $chore->status === 'completed' ? 'bg-cream' : 'bg-white shadow-brutal-sm' }} brutal-border flex items-center justify-between text-xs gap-2">
              <div class="truncate text-xs font-bold text-ink {{ $chore->status === 'completed' ? 'line-through text-ink/50' : '' }}" title="{{ $chore->title }}">
                {{ $chore->title }}
              </div>
              <span class="px-1.5 py-0.2 {{ $chore->status === 'completed' ? 'bg-brand text-white' : 'bg-accent text-ink' }} brutal-border text-[9px] font-mono font-black flex-shrink-0 uppercase">
                {{ $chore->status === 'completed' ? 'Beres' : 'Belum' }}
              </span>
            </div>
          @empty
            <div class="text-xs font-mono text-ink/50 py-2">Belum ada tugas</div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- KOLOM 2: CATATAN KAS TERBARU -->
    <div id="overview-finance-widget" class="bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between h-full">
      <div class="flex items-center justify-between pb-2 border-b-2 border-ink mb-2.5">
        <div class="flex items-center gap-1.5">
          <span class="text-base">🧾</span>
          <h3 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">Catatan Kas</h3>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-[11px] font-mono font-bold text-ink/70">Terbaru</span>
          <a href="{{ route('finances.index') }}" class="text-[10px] font-mono font-black text-brand hover:underline">Semua &rarr;</a>
        </div>
      </div>

      <div class="space-y-1.5 max-h-[145px] overflow-y-auto custom-scrollbar pr-1">
        @php $recentTrx = $finance['recent_transactions'] ?? (isset($finance['transactions']) ? $finance['transactions']->take(4) : collect()); @endphp
        @forelse($recentTrx->take(4) as $trx)
          <div class="flex items-center justify-between p-1.5 sm:p-2 bg-cream brutal-border text-xs gap-2">
            <div class="min-w-0 truncate">
              <div class="text-xs font-bold text-ink truncate" title="{{ $trx->description }}">{{ $trx->description }}</div>
              <div class="text-[10px] font-mono text-ink/60 truncate">{{ $trx->category ? $trx->category->name : 'Umum' }} &bull; {{ $trx->user ? $trx->user->name : 'Keluarga' }}</div>
            </div>
            <span class="font-mono {{ $trx->type === 'income' ? 'text-brand' : 'text-coral' }} font-black text-xs flex-shrink-0">
              {{ $trx->type === 'income' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
            </span>
          </div>
        @empty
          <div class="text-xs font-mono text-ink/50 py-2">Belum ada transaksi</div>
        @endforelse
      </div>
    </div>

    <!-- KOLOM 3: JADWAL & MOMEN TERAKHIR -->
    <div id="overview-calendar-widget" class="bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between relative overflow-hidden h-full">
      <div class="flex items-center justify-between pb-2 border-b-2 border-ink mb-2.5">
        <div class="flex items-center gap-1.5">
          <span class="text-base">🗓️</span>
          <h3 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">Jadwal & Momen</h3>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="px-1.5 py-0.2 {{ $isPro ? 'bg-accent text-ink' : 'bg-coral text-white' }} brutal-border font-mono text-[9px] font-black uppercase">
            {{ $isPro ? '👑 Pro' : '🔒 Pro' }}
          </span>
          @if($isPro)
            <a href="{{ route('events.index') }}" class="text-[10px] font-mono font-black text-brand hover:underline">Semua &rarr;</a>
          @endif
        </div>
      </div>

      @if($isPro)
        <div class="space-y-1.5 max-h-[145px] overflow-y-auto custom-scrollbar pr-1">
          @if($upcomingEvents->count() > 0)
            @php $ev = $upcomingEvents->first(); @endphp
            <div class="p-1.5 sm:p-2 bg-accent/25 brutal-border flex items-center justify-between text-xs gap-2">
              <div class="min-w-0 truncate">
                <div class="text-xs font-bold text-ink truncate">{{ $ev->title }}</div>
                <div class="text-[10px] font-mono text-ink/70">📍 {{ $ev->location ?? 'Rumah' }}</div>
              </div>
              <span class="px-1.5 py-0.5 bg-white brutal-border font-mono text-[9px] sm:text-[10px] font-bold flex-shrink-0">
                {{ \Carbon\Carbon::parse($ev->start_time)->locale('id')->isoFormat('D MMM') }}
              </span>
            </div>
          @endif

          @if($latestMoment)
            <div class="p-1.5 sm:p-2 bg-cream brutal-border flex items-center gap-2 text-xs">
              @if($latestMoment->image_path)
                <img src="{{ asset('storage/' . $latestMoment->image_path) }}" alt="{{ $latestMoment->title }}" class="w-8 h-8 sm:w-9 sm:h-9 object-cover brutal-border flex-shrink-0">
              @else
                <span class="text-base">📸</span>
              @endif
              <div class="min-w-0 truncate">
                <div class="text-xs font-bold text-ink truncate">{{ $latestMoment->title }}</div>
                <div class="text-[10px] font-mono text-ink/60 truncate">{{ \Carbon\Carbon::parse($latestMoment->event_date)->locale('id')->isoFormat('D MMMM Y') }}</div>
              </div>
            </div>
          @else
            <div class="text-xs font-mono text-ink/50 py-1">Belum ada momen foto</div>
          @endif
        </div>
      @else
        <!-- FREE TIER: OPACITY RENDAH DENGAN OVERLAY UPGRADE -->
        <div class="opacity-25 select-none pointer-events-none space-y-1.5">
          <div class="p-2 bg-cream brutal-border flex items-center justify-between text-xs">
            <div>
              <div class="font-bold text-ink">Liburan Pantai Bersama</div>
              <div class="text-[9px] font-mono text-ink/60">Anyer &bull; 4 Foto</div>
            </div>
            <span class="px-1.5 py-0.5 bg-white brutal-border text-[9px] font-mono font-bold">15 Okt</span>
          </div>
          <div class="p-2 bg-cream brutal-border flex items-center justify-between text-xs">
            <div>
              <div class="font-bold text-ink">Jadwal Imunisasi Anak</div>
              <div class="text-[9px] font-mono text-ink/60">Puskesmas</div>
            </div>
            <span class="px-1.5 py-0.5 bg-white brutal-border text-[9px] font-mono font-bold">28 Okt</span>
          </div>
        </div>

        <!-- OVERLAY UPGRADE BUTTON -->
        <div class="absolute inset-0 bg-cream/80 backdrop-blur-[2px] flex flex-col items-center justify-center p-3 text-center z-10">
          <span class="text-xl mb-0.5">🔒</span>
          <span class="px-1.5 py-0.2 bg-coral text-white brutal-border font-mono text-[9px] font-black uppercase mb-0.5">Fitur Pro</span>
          <h4 class="font-black font-display text-xs sm:text-sm text-ink mb-0.5">Kalender & Linimasa</h4>
          <p class="text-[10px] text-ink/70 font-medium mb-2 max-w-xs leading-tight">
            Foto liburan & jadwal agenda otomatis aktif di Pro.
          </p>
          <button type="button" onclick="openPaywallModal('timeline')" class="px-2.5 py-1 bg-accent hover:bg-brand hover:text-white brutal-btn text-[10px] font-mono font-black uppercase tracking-wider shadow-brutal-sm">
            👑 Upgrade Pro (Rp 20rb) &rarr;
          </button>
        </div>
      @endif
    </div>

  </div>

  <!-- BARIS 4: PAPAN KULKAS & CATATAN TETAP (STICKY NOTES KELUARGA) -->
  <div class="bg-white brutal-card p-3.5 sm:p-4 space-y-3">
    <div class="flex items-center justify-between pb-2 border-b-2 border-ink">
      <div class="flex items-center gap-2">
        <span class="text-base sm:text-lg">📌</span>
        <h3 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">
          Papan Kulkas Keluarga
        </h3>
        <span class="text-[10px] font-mono text-ink/50 hidden sm:inline">(Catatan Disematkan)</span>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('notes.index') }}" class="text-[11px] font-mono font-black text-brand hover:underline flex items-center gap-1">
          <span>Buka Semua Papan</span>
          <span>&rarr;</span>
        </a>
      </div>
    </div>

    @if(isset($pinnedNotes) && $pinnedNotes->count() > 0)
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach($pinnedNotes as $pNote)
          @php
            $pBg = match($pNote->color) {
              'mint' => 'bg-[#D1FAE5]',
              'blue' => 'bg-[#BAE6FD]',
              'pink' => 'bg-[#FCE7F3]',
              'orange' => 'bg-[#FED7AA]',
              'lavender' => 'bg-[#E9D5FF]',
              default => 'bg-[#FFF9A6]',
            };
          @endphp
          <a href="{{ route('notes.index') }}" class="p-3 brutal-border {{ $pBg }} block hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_#121614] transition-all">
            <div class="flex items-center justify-between text-[10px] font-mono font-black text-ink mb-1">
              <span class="truncate">{{ $pNote->user->name ?? 'Keluarga' }}</span>
              <span>📌</span>
            </div>
            <div class="font-display font-black text-xs text-ink truncate mb-1">
              {{ $pNote->title }}
            </div>
            <div class="font-mono text-[11px] text-ink/80 line-clamp-2 leading-tight">
              {{ $pNote->content }}
            </div>
          </a>
        @endforeach
      </div>
    @else
      <div class="p-3 bg-cream brutal-border flex flex-col sm:flex-row items-center justify-between gap-2 text-xs font-mono text-ink/70">
        <div class="flex items-center gap-2">
          <span>🧲</span>
          <span>Belum ada catatan kulkas yang disematkan tetap di atas.</span>
        </div>
        <a href="{{ route('notes.index') }}?action=create" class="px-2.5 py-1 bg-accent hover:bg-brand hover:text-white brutal-btn text-[10px] font-mono font-black uppercase tracking-wider shrink-0">
          + Tulis Catatan
        </a>
      </div>
    @endif
  </div>

  <!-- PANTRY & INVENTORY ALERTS (JIKA ADA STOK MENIPIS / EXPIRED) -->
  @if(isset($pantryAlerts) && $pantryAlerts->count() > 0)
    <div class="p-4 bg-white brutal-card space-y-3 border-coral">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="text-base sm:text-lg">🥫</span>
          <h3 class="font-display font-black text-sm sm:text-base text-ink">
            Peringatan Stok & Pantry
          </h3>
          <span class="px-2 py-0.5 bg-coral text-white brutal-border text-[9px] font-mono font-black uppercase">
            {{ $pantryAlerts->count() }} Perlu Perhatian
          </span>
        </div>
        <a href="{{ route('pantry.index') }}" class="text-[11px] font-mono font-black text-brand hover:underline flex items-center gap-1">
          <span>Buka Inventaris</span>
          <span>&rarr;</span>
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($pantryAlerts as $alertItem)
          @php
            $isLow = $alertItem->isLowStock();
            $isExp = $alertItem->isExpired();
            $isSoon = $alertItem->isExpiringSoon();
          @endphp
          <a href="{{ route('pantry.index') }}" class="p-3 brutal-border bg-cream hover:bg-accent/40 block transition-all">
            <div class="flex items-center justify-between text-[10px] font-mono font-bold mb-1">
              <span class="uppercase text-ink/60">{{ $alertItem->category }}</span>
              @if($isExp)
                <span class="px-1.5 py-0.5 bg-coral text-white brutal-border text-[8px] font-black uppercase">Expired</span>
              @elseif($isSoon)
                <span class="px-1.5 py-0.5 bg-accent text-ink brutal-border text-[8px] font-black uppercase">Exp Segera</span>
              @elseif($isLow)
                <span class="px-1.5 py-0.5 bg-amber-300 text-ink brutal-border text-[8px] font-black uppercase">Stok Tipis</span>
              @endif
            </div>
            <div class="font-display font-black text-xs text-ink truncate">
              {{ $alertItem->name }}
            </div>
            <div class="font-mono text-[11px] text-ink/80 mt-1 flex items-center justify-between">
              <span>Sisa: <strong>{{ rtrim(rtrim(number_format((float) $alertItem->quantity, 2, '.', ''), '0'), '.') }} {{ $alertItem->unit }}</strong></span>
              @if($alertItem->expired_at)
                <span class="text-[10px] text-ink/60">📅 {{ \Carbon\Carbon::parse($alertItem->expired_at)->isoFormat('D MMM') }}</span>
              @endif
            </div>
          </a>
        @endforeach
      </div>
    </div>
  @endif

</div>

</div>

<!-- CHART.JS LOGIC -->
@push('scripts')
<script>
  let cashflowChartInstance = null;
  let categoryDonutInstance = null;
  let memberPointsInstance = null;
  let choreRingInstance = null;

  Chart.defaults.font.family = '"JetBrains Mono", monospace';
  Chart.defaults.color = '#121614';
  Chart.defaults.borderColor = 'rgba(18, 22, 20, 0.08)';

  // 1. CASHFLOW CHART
  const cashflowLabels = @json($cashflowLabels);
  const cashflowIncome = @json($cashflowIncome);
  const cashflowExpense = @json($cashflowExpense);

  function initCashflowChart(chartType = 'bar') {
    const ctx = document.getElementById('cashflowChart');
    if (!ctx) return;

    if (cashflowChartInstance) {
      cashflowChartInstance.destroy();
    }

    cashflowChartInstance = new Chart(ctx, {
      type: chartType,
      data: {
        labels: cashflowLabels,
        datasets: [
          {
            label: 'Masuk',
            data: cashflowIncome,
            backgroundColor: '#2F5233',
            borderColor: '#121614',
            borderWidth: 2.5,
            borderRadius: 0,
            maxBarThickness: 28,
            fill: chartType === 'line' ? false : undefined,
            tension: 0.1
          },
          {
            label: 'Keluar',
            data: cashflowExpense,
            backgroundColor: '#E07A5F',
            borderColor: '#121614',
            borderWidth: 2.5,
            borderRadius: 0,
            maxBarThickness: 28,
            fill: chartType === 'line' ? false : undefined,
            tension: 0.1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              boxWidth: 10,
              boxHeight: 10,
              font: { weight: 'bold', size: 10 },
              padding: 6
            }
          },
          tooltip: {
            backgroundColor: '#FBF8EF',
            titleColor: '#121614',
            bodyColor: '#121614',
            borderColor: '#121614',
            borderWidth: 2,
            padding: 8,
            cornerRadius: 0,
            callbacks: {
              label: function(context) {
                const val = context.parsed.y !== undefined ? context.parsed.y : context.parsed;
                return context.dataset.label + ': Rp ' + Number(val).toLocaleString('id-ID');
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              font: { weight: 'bold', size: 9 },
              maxTicksLimit: 7,
              autoSkip: true
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(val) {
                if (val >= 1000000) return (val / 1000000) + 'jt';
                if (val >= 1000) return (val / 1000) + 'rb';
                return val;
              },
              font: { weight: 'bold', size: 9 }
            }
          }
        }
      }
    });
  }

  function setCashflowChartType(type) {
    $('#btn-chart-bar').toggleClass('bg-accent', type === 'bar').toggleClass('bg-white', type !== 'bar');
    $('#btn-chart-line').toggleClass('bg-accent', type === 'line').toggleClass('bg-white', type !== 'line');
    initCashflowChart(type);
  }

  // 2. CATEGORY EXPENSES DONUT CHART
  @php
    $catNames = $expensesByCategory->pluck('name')->toArray();
    $catAmounts = $expensesByCategory->pluck('amount')->toArray();
  @endphp
  const categoryLabels = @json($catNames);
  const categoryAmounts = @json($catAmounts);

  function initCategoryDonutChart() {
    const ctx = document.getElementById('categoryDonutChart');
    if (!ctx || categoryLabels.length === 0) return;

    if (categoryDonutInstance) {
      categoryDonutInstance.destroy();
    }

    const palette = ['#2F5233', '#34D399', '#E07A5F', '#81B0C4', '#87A987', '#121614', '#D4A373'];

    categoryDonutInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: categoryLabels,
        datasets: [{
          data: categoryAmounts,
          backgroundColor: palette.slice(0, categoryLabels.length),
          borderColor: '#121614',
          borderWidth: 2.5,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 8,
              boxHeight: 8,
              font: { weight: 'bold', size: 9 },
              padding: 5
            }
          },
          tooltip: {
            backgroundColor: '#FBF8EF',
            titleColor: '#121614',
            bodyColor: '#121614',
            borderColor: '#121614',
            borderWidth: 2,
            padding: 8,
            cornerRadius: 0,
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const val = context.raw;
                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                return `${context.label}: Rp ${Number(val).toLocaleString('id-ID')} (${pct}%)`;
              }
            }
          }
        },
        cutout: '62%'
      }
    });
  }

  // 3. MEMBER POINTS & PRODUCTIVITY BAR CHART (TOP 5 LEADERBOARD)
  @php
    $topMemberStats = $memberStats->take(5);
    $memberNames = $topMemberStats->pluck('name')->toArray();
    $memberPoints = $topMemberStats->pluck('points')->toArray();
  @endphp
  const memberNames = @json($memberNames);
  const memberPoints = @json($memberPoints);

  function initMemberPointsChart() {
    const ctx = document.getElementById('memberPointsChart');
    if (!ctx) return;

    if (memberPointsInstance) {
      memberPointsInstance.destroy();
    }

    memberPointsInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: memberNames,
        datasets: [
          {
            label: 'Poin',
            data: memberPoints,
            backgroundColor: '#34D399',
            borderColor: '#121614',
            borderWidth: 2.5,
            borderRadius: 0,
            maxBarThickness: 16
          }
        ]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#FBF8EF',
            titleColor: '#121614',
            bodyColor: '#121614',
            borderColor: '#121614',
            borderWidth: 2,
            padding: 8,
            cornerRadius: 0
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: { font: { weight: 'bold', size: 9 } }
          },
          y: {
            ticks: { font: { weight: 'bold', size: 9 } }
          }
        }
      }
    });
  }

  // 4. CHORES COMPLETION RING CHART
  const choresCompletedCount = {{ $choresCompleted }};
  const choresPendingCount = {{ $choresPending }};

  function initChoreRingChart() {
    const ctx = document.getElementById('choreRingChart');
    if (!ctx) return;

    if (choreRingInstance) {
      choreRingInstance.destroy();
    }

    const hasData = (choresCompletedCount + choresPendingCount) > 0;
    const ringData = hasData ? [choresCompletedCount, choresPendingCount] : [0, 1];
    const ringColors = hasData ? ['#2F5233', '#E07A5F'] : ['#EAF0EB', '#EAF0EB'];

    choreRingInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Beres', 'Belum'],
        datasets: [{
          data: ringData,
          backgroundColor: ringColors,
          borderColor: '#121614',
          borderWidth: 2,
          hoverOffset: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            enabled: hasData,
            backgroundColor: '#FBF8EF',
            titleColor: '#121614',
            bodyColor: '#121614',
            borderColor: '#121614',
            borderWidth: 2,
            padding: 6,
            cornerRadius: 0
          }
        },
        cutout: '70%'
      }
    });
  }

  $(document).ready(function() {
    initCashflowChart('bar');
    initCategoryDonutChart();
    initMemberPointsChart();
    initChoreRingChart();
  });
</script>
@endpush
