<!-- TAB 1: OVERVIEW / RINGKASAN UTAMA (MENDUKUNG PAKET FREE & PRO DENGAN OVERLAY UPGRADE) -->
<div id="view-overview" class="space-y-4 sm:space-y-5">

  <!-- HERO ALL-IN-ONE (GREETING + BANNER FOTO + STATUS PRO/FREE) -->
  @php
    $hour = (int) now(config('app.timezone', 'Asia/Jakarta'))->format('H');
    if ($hour >= 4 && $hour < 11) {
      $greetingTime = 'Selamat Pagi';
      $greetingEmoji = '🌅';
      $warmQuote = 'Awali hari penuh berkah dan kehangatan bersama keluarga.';
    } elseif ($hour >= 11 && $hour < 15) {
      $greetingTime = 'Selamat Siang';
      $greetingEmoji = '☀️';
      $warmQuote = 'Tetap semangat beraktivitas, jangan lupa rehat sejenak.';
    } elseif ($hour >= 15 && $hour < 18) {
      $greetingTime = 'Selamat Sore';
      $greetingEmoji = '🌇';
      $warmQuote = 'Hari perlahan senja, saatnya bersantai bersama orang tersayang.';
    } else {
      $greetingTime = 'Selamat Malam';
      $greetingEmoji = '🌙';
      $warmQuote = 'Selamat beristirahat. Terima kasih atas cinta dan kerja kerasmu hari ini.';
    }

    $bannerMedia = $family->bannerMedia;
    $bannerPos = $family->banner_position ?? '50% 50%';
    $bannerFit = $family->banner_fit ?? 'cover';
    $initY = 50;
    if (preg_match('/(\d+)%$/', $bannerPos, $m)) {
        $initY = (int) $m[1];
    } elseif ($bannerPos === 'top') {
        $initY = 15;
    } elseif ($bannerPos === 'bottom') {
        $initY = 85;
    }
  @endphp

  <div id="dashboard-hero-card" class="relative bg-white brutal-card p-1.5 sm:p-2 overflow-hidden shadow-brutal transition-all">
    
    <!-- CONTROLS TOOLBAR UNTUK PENYESUAIAN POSISI BANNER -->
    <div id="banner-adjust-toolbar" class="hidden mb-2 p-2.5 bg-cream brutal-border shadow-brutal-sm space-y-2 select-none">
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-ink/10 pb-2">
        <div class="flex items-center gap-2">
          <span class="text-sm">📐</span>
          <span class="font-mono font-black text-xs uppercase text-ink">Penyesuaian Posisi & Tampilan Banner</span>
          <span class="text-[10px] font-mono text-ink/60 hidden sm:inline">(Geser foto atau pakai slider)</span>
        </div>
        <div class="flex items-center gap-1.5">
          <button type="button" 
                  onclick="cancelBannerAdjust()" 
                  class="px-2.5 py-1 bg-white hover:bg-gray-100 text-ink brutal-border font-mono font-black text-[11px] shadow-brutal-sm active:scale-95">
            Batal
          </button>
          <button type="button" 
                  id="btn-save-banner-pos"
                  onclick="saveBannerAdjust()" 
                  class="px-3 py-1 bg-brand text-white hover:bg-accent hover:text-ink brutal-border font-mono font-black text-[11px] shadow-brutal-sm flex items-center gap-1 active:scale-95">
            <span>✓</span>
            <span>Simpan Posisi</span>
          </button>
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
        <!-- MODE TAMPILAN (FIT) -->
        <div class="flex items-center gap-1.5">
          <span class="text-ink/70 font-bold text-[11px]">Tampilan:</span>
          <div class="inline-flex brutal-border bg-white p-0.5 shadow-[1px_1px_0px_#121614]">
            <button type="button" 
                    id="btn-fit-cover"
                    onclick="setBannerFitMode('cover')"
                    class="px-2 py-0.5 text-[10px] font-black uppercase transition-colors {{ $bannerFit === 'cover' ? 'bg-ink text-white' : 'text-ink hover:bg-cream' }}">
              Penuh (Cover)
            </button>
            <button type="button" 
                    id="btn-fit-contain"
                    onclick="setBannerFitMode('contain')"
                    class="px-2 py-0.5 text-[10px] font-black uppercase transition-colors {{ $bannerFit === 'contain' ? 'bg-ink text-white' : 'text-ink hover:bg-cream' }}">
              Utuh (Contain)
            </button>
          </div>
        </div>

        <!-- KONTROL VERTICAL POSITION -->
        <div id="banner-pos-controls" class="flex items-center gap-2 flex-wrap {{ $bannerFit === 'contain' ? 'opacity-40 pointer-events-none' : '' }}">
          <span class="text-ink/70 font-bold text-[11px]">Posisi:</span>
          <div class="flex items-center gap-1">
            <button type="button" onclick="setBannerPreset(15)" class="px-2 py-0.5 bg-white hover:bg-accent text-ink brutal-border text-[10px] font-bold">Atas</button>
            <button type="button" onclick="setBannerPreset(50)" class="px-2 py-0.5 bg-white hover:bg-accent text-ink brutal-border text-[10px] font-bold">Tengah</button>
            <button type="button" onclick="setBannerPreset(85)" class="px-2 py-0.5 bg-white hover:bg-accent text-ink brutal-border text-[10px] font-bold">Bawah</button>
          </div>
          <div class="flex items-center gap-1.5 pl-1">
            <input type="range" 
                   id="banner-pos-slider" 
                   min="0" 
                   max="100" 
                   value="{{ $initY }}" 
                   oninput="handleBannerSlider(this.value)" 
                   class="w-24 sm:w-32 accent-brand cursor-pointer">
            <span id="banner-pos-label" class="text-[10px] font-mono font-black text-ink w-8 text-right">{{ $initY }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- PANGGUNG HERO (FOTO ATAU GEOMETRIC PATTERN NEOBRUTALIS) -->
    <div id="banner-container-frame"
         onclick="handleBannerFrameClick(event)"
         class="relative w-full h-44 sm:h-52 md:h-60 lg:h-72 brutal-border overflow-hidden bg-ink/5 {{ $bannerMedia && $bannerMedia->isPhoto() ? 'cursor-zoom-in' : '' }} select-none group"
         title="{{ $bannerMedia && $bannerMedia->isPhoto() ? 'Klik untuk melihat foto layar penuh' : 'Ruang Keluarga ' . $family->name }}">

      @if($bannerMedia && $bannerMedia->isPhoto())
        <!-- LATAR BLUR JIKA MODE CONTAIN -->
        <div id="banner-contain-backdrop" 
             class="absolute inset-0 bg-cover bg-center filter blur-md opacity-35 scale-110 pointer-events-none {{ $bannerFit === 'contain' ? '' : 'hidden' }}" 
             style="background-image: url('{{ $bannerMedia->file_url }}')"></div>

        <!-- FOTO UTAMA BANNER -->
        <img id="dashboard-banner-img"
             src="{{ $bannerMedia->file_url }}" 
             alt="{{ $bannerMedia->caption ?? 'Banner Beranda Keluarga ' . $family->name }}" 
             draggable="false"
             class="relative z-0 w-full h-full {{ $bannerFit === 'contain' ? 'object-contain' : 'object-cover' }} transition-all duration-150 ease-out"
             style="object-position: {{ $bannerPos }};">

        <!-- DRAG HINT BADGE (SAAT ADJUST MODE AKTIF) -->
        <div id="banner-drag-guide" class="absolute inset-0 z-20 pointer-events-none items-center justify-center hidden">
          <span class="px-3.5 py-1.5 bg-ink text-white brutal-border font-mono font-black text-xs uppercase tracking-wider shadow-brutal flex items-center gap-2">
            <span>↕️</span>
            <span>Tahan & Geser Foto ke Atas / Bawah</span>
          </span>
        </div>

        <!-- ZOOM HINT PILL (MUNCUL SAAT HOVER DESKTOP) -->
        <div id="banner-zoom-hint" class="absolute inset-0 bg-ink/20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center pointer-events-none z-10 hidden sm:flex">
          <span class="px-3 py-1.5 bg-white text-ink brutal-border font-mono font-black text-xs uppercase tracking-wider shadow-brutal flex items-center gap-2 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-200">
            <span>🔍</span>
            <span>Lihat Foto Penuh</span>
          </span>
        </div>
      @else
        <!-- BACKGROUND PATTERN NEOBRUTALIS JIKA BELUM ADA BANNER -->
        <div class="absolute inset-0 bg-[#FBF8EF] select-none pointer-events-none"
             style="background-image: radial-gradient(#121614 1px, transparent 1px); background-size: 16px 16px;">
          <!-- ACCENT SHAPES -->
          <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-accent/30 filter blur-xl"></div>
          <div class="absolute -left-10 -bottom-10 w-44 h-44 rounded-full bg-brand/15 filter blur-xl"></div>
        </div>

        <!-- CALLOUT AJAKAN TAMBAH FOTO BERANDA -->
        <div class="absolute inset-0 flex items-center justify-center p-4 z-10 pointer-events-auto">
          <a href="{{ route('albums.index') }}" 
             class="px-3 py-1.5 sm:px-4 sm:py-2 bg-white hover:bg-accent text-ink brutal-border shadow-brutal hover:shadow-brutal-sm flex items-center gap-2 font-mono font-black text-[11px] sm:text-xs uppercase tracking-wider transition-all hover:translate-x-[-1px] hover:translate-y-[-1px] active:scale-95">
            <span>📸</span>
            <span>Pasang Foto Favorit Keluarga</span>
            <span>&rarr;</span>
          </a>
        </div>
      @endif

      <!-- HERO OVERLAYS (INFO & KONTROL MINIMALIS BERSIH UNTUK PWA & DESKTOP) -->
      <div id="banner-hero-overlays" class="absolute inset-0 flex flex-col justify-between p-2.5 sm:p-3.5 z-10 pointer-events-none transition-opacity duration-300">
        
        <!-- TOP BAR: KELUARGA (KIRI) & RETRO CLOCK / LIVE WEATHER / MENU (KANAN) -->
        <div class="flex items-center justify-between gap-2">
          <!-- KIRI: NAMA KELUARGA & TANGGAL -->
          <div class="flex items-center gap-1.5 pointer-events-auto">
            <div class="px-2 sm:px-2.5 py-1 bg-white/95 backdrop-blur-md brutal-border text-[10px] sm:text-[11px] font-mono font-bold text-ink flex items-center gap-1.5 shadow-[1.5px_1.5px_0px_#121614]">
              <span>🏡</span>
              <span class="font-black truncate max-w-[90px] sm:max-w-none">{{ $family->name }}</span>
              <span class="text-ink/30 hidden xs:inline">•</span>
              <span class="hidden xs:inline">{{ now()->locale('id')->isoFormat('D MMM') }}</span>
              <span class="sr-only">Momen Keluarga</span>
            </div>
            @if($bannerMedia && $bannerMedia->isPhoto())
              <span class="hidden sm:inline-flex items-center gap-1 px-2 py-1 bg-accent/90 backdrop-blur-md text-ink brutal-border text-[10px] font-mono font-black uppercase shadow-[1.5px_1.5px_0px_#121614] sticker-wiggle cursor-default">
                <span>🖼️</span>
                <span>Momen Keluarga</span>
              </span>
            @endif
            @if($bannerMedia && $bannerMedia->album)
              <a href="{{ route('albums.show', $bannerMedia->album) }}" 
                 onclick="event.stopPropagation()"
                 class="hidden md:inline-flex items-center gap-1 px-2 py-1 bg-cream/95 backdrop-blur-md text-ink brutal-border text-[10px] font-mono font-bold hover:bg-white shadow-[1.5px_1.5px_0px_#121614] transition-all"
                 title="Buka Album">
                <span>📁</span>
                <span class="max-w-[120px] truncate">{{ $bannerMedia->album->title }}</span>
              </a>
            @endif
          </div>

          <!-- KANAN: RETRO LCD CLOCK + LIVE WEATHER + MENU KEBAB -->
          <div class="flex items-center gap-1.5 pointer-events-auto">
            
            <!-- CHIP WAKTU & IKON CUACA HIDUP -->
            <div class="bg-ink/90 backdrop-blur-md text-accent brutal-border px-2 sm:px-2.5 py-1 flex items-center gap-1.5 shadow-[1.5px_1.5px_0px_#121614] select-none" 
                 title="Waktu Nyata Lokal (WIB)">
              <!-- IKON CUACA MIKRO-ANIMASI -->
              <div class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 flex items-center justify-center">
                @if($hour >= 4 && $hour < 15)
                  <svg class="w-full h-full" viewBox="0 0 36 36" fill="none">
                    <g class="animate-sun-spin">
                      <path d="M18 2V5M18 31V34M2 18H5M31 18H34M6.7 6.7L9 9M27 27L29.3 29.3M6.7 29.3L9 27M27 9L29.3 6.7" stroke="#34D399" stroke-width="2.8" stroke-linecap="round"/>
                    </g>
                    <circle cx="18" cy="18" r="7.5" fill="#F9D949" stroke="#121614" stroke-width="2" />
                  </svg>
                @elseif($hour >= 15 && $hour < 18)
                  <svg class="w-full h-full" viewBox="0 0 36 36" fill="none">
                    <circle cx="18" cy="17" r="7.5" fill="#E07A5F" stroke="#121614" stroke-width="2" />
                    <path d="M5 28H31" stroke="#34D399" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M9 31.5H27" stroke="#34D399" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                @else
                  <svg class="w-full h-full" viewBox="0 0 36 36" fill="none">
                    <path class="animate-star-twinkle" d="M7 11L8 13L10 14L8 15L7 17L6 15L4 14L6 13L7 11Z" fill="#34D399" stroke="#121614" stroke-width="1.2" />
                    <path d="M20 9C15 9 11 13 11 18C11 23 15 27 20 27C22.5 27 24.5 26 26 24.5C21.5 24 18 20.5 18 16C18 12.8 19.8 10.2 22.5 9.4C21.7 9.1 20.8 9 20 9Z" fill="#F9D949" stroke="#121614" stroke-width="2" stroke-linejoin="round"/>
                  </svg>
                @endif
              </div>

              <!-- TEKS JAM DIGITAL RETRO -->
              <div class="flex items-baseline text-xs sm:text-xs font-black tracking-wider text-[#34D399] font-mono">
                <span id="greetings-clock-hours">--</span>
                <span class="animate-colon-blink px-0.5 text-white">:</span>
                <span id="greetings-clock-minutes">--</span>
                <span class="hidden sm:inline">
                  <span class="animate-colon-blink px-0.5 text-white">:</span>
                  <span id="greetings-clock-seconds" class="text-white text-[10px]">--</span>
                </span>
                <span class="text-[8px] sm:text-[8.5px] text-accent/80 ml-1 font-bold uppercase">WIB</span>
              </div>
            </div>

            <!-- KEBAB MENU BUTTON & DROPDOWN -->
            <div class="relative inline-block text-left" onclick="event.stopPropagation()">
              <button type="button" 
                      id="btn-banner-kebab"
                      onclick="toggleBannerKebabMenu(event)"
                      class="w-7 h-7 sm:w-7.5 sm:h-7.5 bg-white/95 hover:bg-cream text-ink brutal-border shadow-[1.5px_1.5px_0px_#121614] flex items-center justify-center font-black text-sm active:scale-95 transition-all"
                      title="Menu Banner">
                <span>⋮</span>
              </button>

              <!-- POPOVER DROPDOWN -->
              <div id="banner-kebab-menu" class="hidden absolute right-0 mt-1.5 w-44 bg-white brutal-border shadow-brutal p-1 z-20 space-y-0.5 font-mono text-xs select-none">
                @if($bannerMedia && $bannerMedia->isPhoto())
                  <button type="button" 
                          id="btn-open-adjust-banner"
                          onclick="toggleBannerAdjustMode(); closeBannerKebabMenu();"
                          class="w-full text-left px-2.5 py-1.5 hover:bg-cream brutal-border border-transparent hover:border-ink font-bold flex items-center gap-2 text-ink">
                    <span>📐</span>
                    <span>Atur Posisi Foto</span>
                  </button>
                  <a href="{{ route('albums.index') }}"
                     class="w-full text-left px-2.5 py-1.5 hover:bg-cream brutal-border border-transparent hover:border-ink font-bold flex items-center gap-2 text-ink">
                    <span>🔄</span>
                    <span>Ganti Foto</span>
                  </a>
                  <button type="button"
                          onclick="showImagePreview('{{ $bannerMedia->file_url }}', '{{ addslashes($bannerMedia->caption ?? 'Banner Keluarga ' . $family->name) }}'); closeBannerKebabMenu();"
                          class="w-full text-left px-2.5 py-1.5 hover:bg-cream brutal-border border-transparent hover:border-ink font-bold flex items-center gap-2 text-ink">
                    <span>🔍</span>
                    <span>Lihat Layar Penuh</span>
                  </button>
                  <div class="border-t border-ink/15 my-0.5"></div>
                  <form action="{{ route('albums.banner.remove') }}" method="POST" onsubmit="return confirm('Lepas banner ini dari beranda?');">
                    @csrf
                    <button type="submit" 
                            class="w-full text-left px-2.5 py-1.5 hover:bg-coral hover:text-white brutal-border border-transparent hover:border-ink font-bold flex items-center gap-2 text-coral">
                      <span>🗑️</span>
                      <span>Lepas Banner</span>
                    </button>
                  </form>
                @else
                  <a href="{{ route('albums.index') }}"
                     class="w-full text-left px-2.5 py-1.5 hover:bg-accent brutal-border border-transparent hover:border-ink font-bold flex items-center gap-2 text-ink">
                    <span>📸</span>
                    <span>Pasang Foto Keluarga</span>
                  </a>
                @endif
              </div>
            </div>

          </div>
        </div>

        <!-- BOTTOM BAR: SAPAAN HANGAT + BADGE STATUS PRO + KUTIPAN -->
        <div class="space-y-1">
          <!-- BARIS 1: SAPAAN + NAMA USER + CHIP PRO/FREE -->
          <div class="flex items-center justify-between gap-2 flex-wrap pointer-events-auto">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
              <h1 class="text-sm sm:text-lg md:text-xl font-black font-display text-white tracking-wide drop-shadow-md flex items-center gap-1.5">
                <span>{{ $greetingEmoji }}</span>
                <span>{{ $greetingTime }},</span>
                <span class="text-[#F9D949]">{{ $user->name }}</span>!
              </h1>

              <!-- BADGE AKUN PRO / FREE -->
              @if($isPro)
                <span class="px-1.5 sm:px-2 py-0.5 bg-brand text-white brutal-border border-white text-[8.5px] sm:text-[9.5px] font-mono font-black uppercase shadow-[1px_1px_0px_#fff] flex items-center gap-1 cursor-default" title="Paket FamKoji Pro Aktif">
                  <span>👑</span>
                  <span>Paket FamKoji Pro Aktif</span>
                </span>
              @else
                <button type="button" 
                        onclick="openPaywallModal('dashboard-hero')" 
                        class="px-1.5 sm:px-2 py-0.5 bg-accent hover:bg-[#F9D949] text-ink brutal-border border-white text-[8.5px] sm:text-[9.5px] font-mono font-black uppercase shadow-[1px_1px_0px_#fff] flex items-center gap-1 transition-transform active:scale-95" 
                        title="Buka akses fitur lengkap FamSpace">
                  <span>⭐</span>
                  <span>Paket Free</span>
                  <span class="text-ink/40">&bull;</span>
                  <span>Upgrade ke Pro</span>
                </button>
              @endif
            </div>

            @if($bannerMedia && $bannerMedia->user)
              <span class="text-[9px] sm:text-[10px] font-mono text-white/70 hidden md:inline-block drop-shadow-sm">
                Foto: <strong class="text-white">{{ $bannerMedia->user->name }}</strong>
              </span>
            @endif
          </div>

          <!-- BARIS 2: WARM QUOTE 1 BARIS ELEGAN -->
          <div class="flex items-center justify-between gap-2 text-white/95 font-mono text-[10.5px] sm:text-xs pointer-events-auto">
            <p class="truncate leading-tight drop-shadow-md flex items-center gap-1.5">
              <span class="text-[#F9D949] text-[11px]">💬</span>
              <span class="italic text-white/90">{{ $warmQuote }}</span>
            </p>
            @if($bannerMedia && $bannerMedia->caption)
              <span class="text-[9.5px] sm:text-[10px] text-[#F9D949] font-bold shrink-0 hidden sm:inline truncate max-w-[180px] drop-shadow-sm" title="{{ $bannerMedia->caption }}">
                "{{ $bannerMedia->caption }}"
              </span>
            @endif
          </div>
        </div>

      </div>

      <!-- GRADIENT SHADE OVERLAY UNTUK KONTRAST TEKS DI BAWAH -->
      <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-ink/90 via-ink/50 to-transparent pointer-events-none z-10"></div>
      <!-- GRADIENT SHADE TIPIS DI ATAS UNTUK KONTRAST PILL ATAS -->
      <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-ink/60 via-ink/20 to-transparent pointer-events-none z-10"></div>

    </div>
  </div>

  <!-- UNIFIED DASHBOARD: 4 TOMBOL STICKER NEOBRUTALIS BERWARNA & BERANIMASI -->
  <div class="space-y-3">

    <!-- BARIS 4 TOMBOL STICKER BERWARNA -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 select-none">
      
      <!-- TOMBOL 1: KULKAS (KUNING PASTEL) -->
      <button type="button"
              id="btn-unified-kulkas"
              onclick="toggleUnifiedPanel('kulkas')"
              class="unified-tab-btn group relative px-3 py-2.5 sm:py-3 bg-[#FFF9A6] hover:bg-[#FFF37A] text-ink brutal-border shadow-brutal hover:shadow-brutal-sm hover:-translate-y-0.5 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none flex items-center justify-center gap-2 font-mono font-black text-xs uppercase tracking-wider transition-all">
        <span class="text-base sm:text-lg group-hover:rotate-12 group-hover:scale-110 transition-transform duration-200 inline-block">📌</span>
        <span>Memo Kulkas</span>
        <span class="active-dot hidden absolute -top-1 -right-1 w-3 h-3 bg-brand rounded-full brutal-border"></span>
      </button>

      <!-- TOMBOL 2: MOMEN (MINT PASTEL) -->
      <button type="button"
              id="btn-unified-momen"
              onclick="toggleUnifiedPanel('momen')"
              class="unified-tab-btn group relative px-3 py-2.5 sm:py-3 bg-[#D1FAE5] hover:bg-[#A7F3D0] text-ink brutal-border shadow-brutal hover:shadow-brutal-sm hover:-translate-y-0.5 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none flex items-center justify-center gap-2 font-mono font-black text-xs uppercase tracking-wider transition-all">
        <span class="text-base sm:text-lg group-hover:scale-125 transition-transform duration-200 inline-block">📷</span>
        <span>Momen</span>
        <span class="active-dot hidden absolute -top-1 -right-1 w-3 h-3 bg-brand rounded-full brutal-border"></span>
      </button>

      <!-- TOMBOL 3: TUGAS (LAVENDER PASTEL) -->
      <button type="button"
              id="btn-unified-tugas"
              onclick="toggleUnifiedPanel('tugas')"
              class="unified-tab-btn group relative px-3 py-2.5 sm:py-3 bg-[#E9D5FF] hover:bg-[#D8B4FE] text-ink brutal-border shadow-brutal hover:shadow-brutal-sm hover:-translate-y-0.5 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none flex items-center justify-center gap-2 font-mono font-black text-xs uppercase tracking-wider transition-all">
        <span class="text-base sm:text-lg group-hover:-rotate-12 group-hover:scale-110 transition-transform duration-200 inline-block">🧹</span>
        <span>Tugas & Agenda</span>
        <span class="active-dot hidden absolute -top-1 -right-1 w-3 h-3 bg-brand rounded-full brutal-border"></span>
      </button>

      <!-- TOMBOL 4: STATISTIK (ORANGE/CORAL PASTEL) -->
      <button type="button"
              id="btn-unified-statistik"
              onclick="toggleUnifiedPanel('statistik')"
              class="unified-tab-btn group relative px-3 py-2.5 sm:py-3 bg-[#FED7AA] hover:bg-[#FDBA74] text-ink brutal-border shadow-brutal hover:shadow-brutal-sm hover:-translate-y-0.5 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none flex items-center justify-center gap-2 font-mono font-black text-xs uppercase tracking-wider transition-all">
        <span class="text-base sm:text-lg group-hover:scale-110 group-hover:rotate-6 transition-transform duration-200 inline-block">📊</span>
        <span>Kas & Statistik</span>
        <span class="active-dot hidden absolute -top-1 -right-1 w-3 h-3 bg-brand rounded-full brutal-border"></span>
      </button>
    </div>

    <!-- INLINE PANEL CARD (MEKAR DENGAN EFEK SPRING POP, DEFAULT TERSEMBUNYI) -->
    <div id="unified-panel-container" class="hidden bg-white brutal-card p-3.5 sm:p-5 shadow-brutal-lg border-2 border-ink transition-all">
      
      <!-- PANEL HEADER & TOMBOL TUTUP -->
      <div class="flex items-center justify-between pb-2.5 border-b-2 border-ink mb-4 select-none">
        <div class="flex items-center gap-2">
          <span id="unified-panel-icon" class="text-base sm:text-xl p-1 bg-cream brutal-border shadow-[1px_1px_0px_#121614]">📌</span>
          <h3 id="unified-panel-title" class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">
            Panel Detail
          </h3>
        </div>
        <button type="button" 
                onclick="closeUnifiedPanel()" 
                class="px-2.5 py-1 bg-cream hover:bg-coral hover:text-white brutal-border font-mono font-black text-[11px] flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all"
                title="Tutup Panel">
          <span>✕</span>
          <span>Tutup</span>
        </button>
      </div>

      <!-- PANEL 1: MEMO KULKAS KELUARGA (PLAYFUL ROTATING STICKY NOTES DENGAN SELOTIP) -->
      <div id="panel-view-kulkas" class="unified-panel-view hidden space-y-4">
        <div class="flex items-center justify-between text-xs font-mono font-bold text-ink/70">
          <h4 class="font-display font-black text-xs sm:text-sm text-ink uppercase tracking-wide flex items-center gap-1.5">
            <span>Papan Kulkas Keluarga</span>
            <span class="text-[10px] font-mono text-ink/50 normal-case hidden sm:inline">(Memo Disematkan)</span>
          </h4>
          <a href="{{ route('notes.index') }}" class="text-brand font-black hover:underline flex items-center gap-1">
            <span>Buka Semua Papan</span>
            <span>&rarr;</span>
          </a>
        </div>

        @if(isset($pinnedNotes) && $pinnedNotes->count() > 0)
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
            @php
              $rotations = ['rotate-[-1.5deg]', 'rotate-[1.2deg]', 'rotate-[-0.8deg]', 'rotate-[1.8deg]'];
            @endphp
            @foreach($pinnedNotes->take(4) as $idx => $pNote)
              @php
                $pBg = match($pNote->color) {
                  'mint' => 'bg-[#D1FAE5]',
                  'blue' => 'bg-[#BAE6FD]',
                  'pink' => 'bg-[#FCE7F3]',
                  'orange' => 'bg-[#FED7AA]',
                  'lavender' => 'bg-[#E9D5FF]',
                  default => 'bg-[#FFF9A6]',
                };
                $rotClass = $rotations[$idx % 4];
              @endphp
              <div class="relative group {{ $rotClass }} hover:rotate-0 hover:-translate-y-1.5 hover:z-10 transition-all duration-200">
                <!-- SELOTIP RETRO / TAPE STRIP -->
                <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-12 h-3.5 bg-ink/15 backdrop-blur-[1px] border border-ink/30 rotate-[-1deg] shadow-sm pointer-events-none z-10"></div>
                
                <a href="{{ route('notes.index') }}" class="p-3 brutal-border {{ $pBg }} block shadow-brutal group-hover:shadow-brutal-lg transition-shadow">
                  <div class="flex items-center justify-between text-[10px] font-mono font-black text-ink mb-1.5">
                    <span class="truncate font-black">{{ $pNote->user->name ?? 'Keluarga' }}</span>
                    <span class="text-xs group-hover:rotate-12 transition-transform">📌</span>
                  </div>
                  <div class="font-display font-black text-xs text-ink truncate mb-1">
                    {{ $pNote->title }}
                  </div>
                  <div class="font-mono text-[11px] text-ink/80 line-clamp-3 leading-tight">
                    {{ $pNote->content }}
                  </div>
                </a>
              </div>
            @endforeach
          </div>
        @else
          <div class="p-6 bg-cream brutal-border flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-mono text-ink/70">
            <div class="flex items-center gap-2.5">
              <span class="text-2xl animate-pulse">✨</span>
              <span>Kulkas keluarga masih bersih. Belum ada memo yang disematkan.</span>
            </div>
            <a href="{{ route('notes.index') }}" class="px-3 py-1.5 bg-accent hover:bg-brand hover:text-white brutal-border font-black text-xs uppercase shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">
              + Tempel Memo Baru
            </a>
          </div>
        @endif
      </div>

      <!-- PANEL 2: MOMEN KENANGAN KELUARGA (POLAROID TILT INTERAKTIF) -->
      <div id="panel-view-momen" class="unified-panel-view hidden space-y-4">
        <div class="flex items-center justify-between text-xs font-mono font-bold text-ink/70">
          <span>Kapsul Kenangan & Kilas Balik Bersama</span>
          @if($isPro)
            <a href="{{ route('timeline.index') }}" class="text-brand font-black hover:underline flex items-center gap-1">
              <span>Buka Linimasa Penuh</span>
              <span>&rarr;</span>
            </a>
          @else
            <span class="px-1.5 py-0.2 bg-coral text-white brutal-border font-mono text-[9px] font-black uppercase">Fitur Pro</span>
          @endif
        </div>

        @if($isPro)
          @if($latestMoment)
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center bg-cream brutal-border p-4 hover:shadow-brutal transition-shadow">
              <!-- POLAROID FRAME -->
              <div class="md:col-span-5 bg-white p-2.5 pb-3.5 brutal-border shadow-brutal hover:scale-[1.02] hover:-rotate-1 transition-all duration-300 group">
                <div class="relative h-44 sm:h-52 overflow-hidden brutal-border bg-ink/10">
                  @if($latestMoment->image_path)
                    <img src="{{ asset('storage/' . $latestMoment->image_path) }}" 
                         alt="{{ $latestMoment->title }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                  @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-ink/40">
                      <span class="text-4xl animate-bounce">📸</span>
                      <span class="text-[10px] font-mono mt-1">Momen Terabadikan</span>
                    </div>
                  @endif
                  <div class="absolute bottom-2 right-2 px-2 py-0.5 bg-ink text-white font-mono text-[10px] font-bold brutal-border">
                    {{ \Carbon\Carbon::parse($latestMoment->event_date)->locale('id')->isoFormat('D MMMM Y') }}
                  </div>
                </div>
                <div class="mt-2 text-center text-[10px] font-mono font-bold text-ink/60 truncate">
                  {{ $latestMoment->title }}
                </div>
              </div>

              <!-- CAPTION DETAIL -->
              <div class="md:col-span-7 space-y-2.5">
                <div class="inline-block px-2 py-0.5 bg-accent brutal-border text-[9px] font-mono font-black uppercase shadow-[1px_1px_0px_#121614]">
                  Kenangan Terakhir
                </div>
                <h4 class="font-display font-black text-sm sm:text-base text-ink">
                  {{ $latestMoment->title }}
                </h4>
                @if($latestMoment->description)
                  <p class="font-mono text-xs text-ink/80 leading-relaxed bg-white p-2.5 brutal-border shadow-brutal-sm">
                    "{{ $latestMoment->description }}"
                  </p>
                @endif
                <div class="pt-1">
                  <a href="{{ route('timeline.index') }}" class="px-3.5 py-1.5 bg-brand text-white hover:bg-accent hover:text-ink brutal-border font-mono font-black text-xs inline-flex items-center gap-1.5 shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] transition-all">
                    <span>Jelajahi Linimasa Foto</span>
                    <span>&rarr;</span>
                  </a>
                </div>
              </div>
            </div>
          @else
            <div class="p-6 bg-cream brutal-border text-center space-y-2">
              <span class="text-3xl block">🖼️</span>
              <div class="font-display font-black text-sm text-ink">Belum Ada Momen Tersimpan</div>
              <p class="font-mono text-xs text-ink/60 max-w-sm mx-auto">Abadikan foto-foto hangat dan perjalanan bersama keluarga di Linimasa.</p>
              <a href="{{ route('timeline.index') }}" class="inline-block px-3 py-1.5 bg-brand text-white brutal-border font-mono font-black text-xs uppercase shadow-brutal-sm">
                + Tambah Momen Baru
              </a>
            </div>
          @endif
        @else
          <div class="bg-cream brutal-border p-6 text-center space-y-2.5 relative overflow-hidden">
            <span class="text-3xl block animate-bounce">📸</span>
            <div class="font-display font-black text-sm text-ink">Kapsul Kenangan Keluarga</div>
            <p class="font-mono text-xs text-ink/70 max-w-md mx-auto">
              Simpan foto kenangan indah, momen liburan, dan perjalanan keluarga di satu linimasa bersama tanpa batas.
            </p>
            <button type="button" onclick="openPaywallModal('timeline')" class="px-3.5 py-1.5 bg-accent hover:bg-brand hover:text-white brutal-btn text-xs font-mono font-black uppercase tracking-wider shadow-brutal-sm">
              👑 Aktifkan Linimasa Pro &rarr;
            </button>
          </div>
        @endif
      </div>

      <!-- PANEL 3: TUGAS RUMAH & AGENDA KELUARGA (INTERACTIVE HOVER CHECKLIST & EVENT PILLS) -->
      <div id="panel-view-tugas" class="unified-panel-view hidden space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5 sm:gap-4 items-stretch">
          
          <!-- KIRI: TUGAS RUMAH HARI INI -->
          <div class="p-3.5 sm:p-4 bg-cream brutal-border flex flex-col justify-between">
            <div class="flex items-center justify-between pb-2 border-b-2 border-ink mb-2.5">
              <div class="flex items-center gap-1.5">
                <span class="text-base">🧹</span>
                <h4 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">
                  Tugas Hari Ini
                </h4>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[11px] font-mono font-bold text-ink/70">{{ $choresCompleted }}/{{ $choresTotal }} beres</span>
                <a href="{{ route('chores.index') }}" class="text-[10px] font-mono font-black text-brand hover:underline">Semua &rarr;</a>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <!-- COMPACT DONUT RING -->
              <div class="relative w-14 h-14 sm:w-16 sm:h-16 flex-shrink-0 flex items-center justify-center">
                <canvas id="choreRingChart"></canvas>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                  <span class="text-[10px] sm:text-[11px] font-mono font-black text-ink">{{ $choreCompletionRate }}%</span>
                </div>
              </div>

              <!-- CHECKLIST TUGAS INTERAKTIF -->
              <div class="flex-1 min-w-0 space-y-1.5 max-h-[145px] overflow-y-auto custom-scrollbar pr-1">
                @forelse($allChores->take(5) as $chore)
                  <div class="p-1.5 sm:p-2 {{ $chore->status === 'completed' ? 'bg-white opacity-85' : 'bg-white shadow-brutal-sm' }} brutal-border flex items-center justify-between text-xs gap-2 hover:-translate-x-0.5 transition-transform">
                    <div class="truncate text-xs font-bold text-ink {{ $chore->status === 'completed' ? 'line-through text-ink/50' : '' }}" title="{{ $chore->title }}">
                      {{ $chore->title }}
                    </div>
                    <span class="px-1.5 py-0.2 {{ $chore->status === 'completed' ? 'bg-brand text-white' : 'bg-accent text-ink' }} brutal-border text-[9px] font-mono font-black flex-shrink-0 uppercase shadow-[1px_1px_0px_#121614]">
                      {{ $chore->status === 'completed' ? '✓ Beres' : 'Belum' }}
                    </span>
                  </div>
                @empty
                  <div class="text-xs font-mono text-ink/50 py-3 text-center bg-white brutal-border">
                    Belum ada tugas rumah hari ini. Rumah tenang!
                  </div>
                @endforelse
              </div>
            </div>
          </div>

          <!-- KANAN: AGENDA KELUARGA MENDATANG -->
          <div class="p-3.5 sm:p-4 bg-cream brutal-border flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between pb-2 border-b-2 border-ink mb-2.5">
              <div class="flex items-center gap-1.5">
                <span class="text-base">📅</span>
                <h4 class="font-black font-display text-xs sm:text-sm text-ink uppercase tracking-wide">
                  Agenda Mendatang
                </h4>
              </div>
              @if($isPro)
                <a href="{{ route('events.index') }}" class="text-[10px] font-mono font-black text-brand hover:underline">Kalender &rarr;</a>
              @else
                <span class="px-1.5 py-0.2 bg-coral text-white brutal-border font-mono text-[9px] font-black uppercase">Fitur Pro</span>
              @endif
            </div>

            @if($isPro)
              <div class="space-y-1.5 max-h-[145px] overflow-y-auto custom-scrollbar pr-1">
                @forelse($upcomingEvents->take(4) as $ev)
                  <div class="p-1.5 sm:p-2 bg-white brutal-border flex items-center justify-between text-xs gap-2 hover:-translate-x-0.5 transition-transform">
                    <div class="min-w-0 truncate">
                      <div class="text-xs font-bold text-ink truncate">{{ $ev->title }}</div>
                      <div class="text-[10px] font-mono text-ink/70">📍 {{ $ev->location ?? 'Rumah' }}</div>
                    </div>
                    <span class="px-1.5 py-0.5 bg-cream brutal-border font-mono text-[9px] sm:text-[10px] font-bold flex-shrink-0 shadow-[1px_1px_0px_#121614]">
                      {{ \Carbon\Carbon::parse($ev->start_time)->locale('id')->isoFormat('D MMM, HH:mm') }}
                    </span>
                  </div>
                @empty
                  <div class="text-xs font-mono text-ink/50 py-3 text-center bg-white brutal-border">
                    Tidak ada agenda mendesak.
                  </div>
                @endforelse
              </div>
            @else
              <div class="opacity-25 select-none pointer-events-none space-y-1.5">
                <div class="p-2 bg-white brutal-border flex items-center justify-between text-xs">
                  <div class="font-bold text-ink">Liburan Akhir Pekan</div>
                  <span class="px-1.5 py-0.5 bg-cream brutal-border text-[9px] font-mono font-bold">Minggu</span>
                </div>
              </div>
              <div class="absolute inset-0 bg-cream/80 backdrop-blur-[2px] flex flex-col items-center justify-center p-3 text-center z-10">
                <span class="text-xl mb-0.5">🗓️</span>
                <h5 class="font-black font-display text-xs text-ink mb-1">Kalender Keluarga Pro</h5>
                <button type="button" onclick="openPaywallModal('calendar')" class="px-2.5 py-1 bg-accent hover:bg-brand hover:text-white brutal-btn text-[10px] font-mono font-black uppercase tracking-wider shadow-brutal-sm">
                  👑 Aktifkan Kalender Pro &rarr;
                </button>
              </div>
            @endif
          </div>

        </div>

        <!-- PANTRY ALERTS (JIKA ADA STOK MENIPIS) -->
        @if(isset($pantryAlerts) && $pantryAlerts->count() > 0)
          <div class="p-3 bg-white brutal-border border-coral space-y-2">
            <div class="flex items-center justify-between text-xs font-mono font-bold text-ink">
              <span class="flex items-center gap-1.5 text-coral">
                <span>🥫</span>
                <span>Peringatan Stok Dapur & Pantry ({{ $pantryAlerts->count() }})</span>
              </span>
              <a href="{{ route('pantry.index') }}" class="text-brand font-black hover:underline text-[11px]">Buka Inventaris &rarr;</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              @foreach($pantryAlerts->take(3) as $alertItem)
                @php
                  $isLow = $alertItem->isLowStock();
                  $isExp = $alertItem->isExpired();
                  $isSoon = $alertItem->isExpiringSoon();
                @endphp
                <div class="p-2 bg-cream brutal-border flex items-center justify-between text-xs gap-2 hover:-translate-y-0.5 transition-transform">
                  <div class="min-w-0 truncate">
                    <div class="font-bold text-ink truncate">{{ $alertItem->name }}</div>
                    <div class="text-[10px] font-mono text-ink/60">Sisa: {{ rtrim(rtrim(number_format((float) $alertItem->quantity, 2, '.', ''), '0'), '.') }} {{ $alertItem->unit }}</div>
                  </div>
                  @if($isExp)
                    <span class="px-1.5 py-0.5 bg-coral text-white brutal-border text-[8px] font-black uppercase flex-shrink-0">Expired</span>
                  @elseif($isSoon)
                    <span class="px-1.5 py-0.5 bg-accent text-ink brutal-border text-[8px] font-black uppercase flex-shrink-0">Exp Segera</span>
                  @elseif($isLow)
                    <span class="px-1.5 py-0.5 bg-amber-300 text-ink brutal-border text-[8px] font-black uppercase flex-shrink-0">Stok Tipis</span>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      <!-- PANEL 4: KAS, POIN & STATISTIK (DENGAN MICRO COUNT-UP ANIMATION) -->
      <div id="panel-view-statistik" class="unified-panel-view hidden space-y-4">
        
        <!-- 4 KARTU NOMINAL UTAMA DENGAN COUNT-UP -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
          <!-- SISA KAS -->
          <div class="p-3 bg-cream brutal-border hover:shadow-brutal-sm hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between text-[10px] font-mono font-bold text-ink/70">
              <span>SISA KAS</span>
              <span>💰</span>
            </div>
            <div class="text-lg sm:text-2xl font-black font-mono {{ $netBalance >= 0 ? 'text-brand' : 'text-coral' }} truncate my-1" title="Rp {{ number_format($netBalance, 0, ',', '.') }}">
              <span class="countup-val" data-target="{{ (int)$netBalance }}" data-is-rp="1">Rp {{ number_format($netBalance, 0, ',', '.') }}</span>
            </div>
            <div class="text-[10px] font-mono text-ink/60">Saldo berjalan</div>
          </div>

          <!-- PEMASUKAN -->
          <div class="p-3 bg-[#D1FAE5] brutal-border hover:shadow-brutal-sm hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between text-[10px] font-mono font-bold text-ink/70">
              <span>MASUK</span>
              <span>📈</span>
            </div>
            <div class="text-base sm:text-xl font-black font-mono text-brand truncate my-1">
              <span class="countup-val" data-target="{{ (int)$totalIncome }}" data-is-rp="1" data-prefix="+">+Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </div>
            <div class="text-[10px] font-mono text-ink/60">Bulan ini</div>
          </div>

          <!-- PENGELUARAN -->
          <div class="p-3 bg-[#FCE7F3] brutal-border hover:shadow-brutal-sm hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between text-[10px] font-mono font-bold text-ink/70">
              <span>KELUAR</span>
              <span>📉</span>
            </div>
            <div class="text-base sm:text-xl font-black font-mono text-coral truncate my-1">
              <span class="countup-val" data-target="{{ (int)$totalExpense }}" data-is-rp="1" data-prefix="-">-Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
            </div>
            <div class="text-[10px] font-mono text-ink/60">Bulan ini</div>
          </div>

          <!-- POIN KELUARGA -->
          <div class="p-3 bg-white brutal-border hover:shadow-brutal-sm hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between text-[10px] font-mono font-bold text-ink/70">
              <span>POIN KELUARGA</span>
              <span>⭐</span>
            </div>
            <div class="text-base sm:text-xl font-black font-mono text-ink truncate my-1">
              <span class="countup-val" data-target="{{ (int)$members->sum('points') }}">{{ number_format($members->sum('points')) }}</span> Pts
            </div>
            <div class="text-[10px] font-mono text-ink/60 truncate">
              Rajin: <strong>{{ $members->first() ? $members->first()->name : '-' }}</strong>
            </div>
          </div>
        </div>

        <!-- 3 GRAFIK UTAMA -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 items-stretch">
          
          <!-- GRAFIK 1: ARUS KAS (6 KOLOM) -->
          <div class="lg:col-span-6 p-3 sm:p-3.5 bg-white brutal-card flex flex-col justify-between hover:shadow-brutal transition-shadow">
            <div class="flex items-center justify-between pb-1.5 border-b border-ink/20 mb-2">
              <div class="flex items-center gap-1.5">
                <span class="text-sm">📈</span>
                <span class="font-display font-black text-xs text-ink uppercase">Uang Masuk & Keluar</span>
              </div>
              <div class="flex items-center gap-1 font-mono text-[10px] font-bold">
                <button type="button" onclick="setCashflowChartType('bar')" id="btn-chart-bar" class="px-2 py-0.5 bg-accent brutal-border text-ink hover:bg-accent transition-colors">Bar</button>
                <button type="button" onclick="setCashflowChartType('line')" id="btn-chart-line" class="px-2 py-0.5 bg-white brutal-border text-ink hover:bg-cream transition-colors">Garis</button>
              </div>
            </div>
            <div class="relative h-44 w-full">
              <canvas id="cashflowChart"></canvas>
            </div>
          </div>

          <!-- GRAFIK 2: KATEGORI PENGELUARAN (3 KOLOM) -->
          <div class="lg:col-span-3 p-3 sm:p-3.5 bg-white brutal-card flex flex-col justify-between hover:shadow-brutal transition-shadow">
            <div class="flex items-center justify-between pb-1.5 border-b border-ink/20 mb-2">
              <div class="flex items-center gap-1.5">
                <span class="text-sm">🥧</span>
                <span class="font-display font-black text-xs text-ink uppercase">Pos Biaya</span>
              </div>
            </div>
            <div class="relative h-44 w-full flex items-center justify-center">
              @if($expensesByCategory->count() > 0)
                <canvas id="categoryDonutChart"></canvas>
              @else
                <div class="p-3 text-center text-xs font-mono text-ink/60 bg-cream brutal-border w-full">
                  Belum ada catatan biaya.
                </div>
              @endif
            </div>
          </div>

          <!-- GRAFIK 3: POIN ANGGOTA (3 KOLOM) -->
          <div class="lg:col-span-3 p-3 sm:p-3.5 bg-white brutal-card flex flex-col justify-between hover:shadow-brutal transition-shadow">
            <div class="flex items-center justify-between pb-1.5 border-b border-ink/20 mb-2">
              <div class="flex items-center gap-1.5">
                <span class="text-sm">🏆</span>
                <span class="font-display font-black text-xs text-ink uppercase">Klasemen Poin</span>
              </div>
              <a href="{{ route('members.index') }}" class="text-brand font-black hover:underline text-[10px]">&rarr;</a>
            </div>
            <div class="relative h-44 w-full">
              <canvas id="memberPointsChart"></canvas>
            </div>
          </div>

        </div>

        <!-- TRANSAKSI TERAKHIR -->
        <div class="p-3 bg-cream brutal-border space-y-2">
          <div class="flex items-center justify-between text-xs font-mono font-bold text-ink">
            <span>Catatan Transaksi Terakhir</span>
            <a href="{{ route('finances.index') }}" class="text-brand font-black hover:underline text-[11px]">Buka Catatan Kas &rarr;</a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
            @php $recentTrx = $finance['recent_transactions'] ?? (isset($finance['transactions']) ? $finance['transactions']->take(4) : collect()); @endphp
            @forelse($recentTrx->take(4) as $trx)
              <div class="p-2 bg-white brutal-border text-xs flex items-center justify-between gap-2 hover:-translate-x-0.5 transition-transform">
                <div class="min-w-0 truncate">
                  <div class="text-xs font-bold text-ink truncate" title="{{ $trx->description }}">{{ $trx->description }}</div>
                  <div class="text-[10px] font-mono text-ink/60 truncate">{{ $trx->category ? $trx->category->name : 'Umum' }}</div>
                </div>
                <span class="font-mono {{ $trx->type === 'income' ? 'text-brand' : 'text-coral' }} font-black text-xs flex-shrink-0">
                  {{ $trx->type === 'income' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                </span>
              </div>
            @empty
              <div class="text-xs font-mono text-ink/50 py-2 col-span-full text-center">Belum ada transaksi kas</div>
            @endforelse
          </div>
        </div>

      </div>

    </div>

    <!-- FAMILY WELCOME FEED: PAPAN CUPLIKAN HANGAT (DEFAULT TAMPIL DI LAYAR AWAL) -->
    <div id="family-welcome-feed" class="space-y-3 sm:space-y-4 animate-spring-pop">
      
      <!-- GRID 3 KOLOM ASIMETRIS HANGAT -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3.5 sm:gap-4 items-stretch">

        <!-- KOLOM 1 (4 KOLOM DESKTOP): 2 MEMO KULKAS DENGAN SELOTIP -->
        <div class="lg:col-span-4 bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between">
          <div class="flex items-center justify-between pb-2 border-b-2 border-ink mb-3 select-none">
            <div class="flex items-center gap-1.5">
              <span class="text-base">📌</span>
              <h4 class="font-display font-black text-xs sm:text-sm text-ink uppercase tracking-wide">
                Papan Kulkas Keluarga
              </h4>
            </div>
            <button type="button" onclick="toggleUnifiedPanel('kulkas')" class="text-[11px] font-mono font-black text-brand hover:underline flex items-center gap-0.5">
              <span>Buka</span>
              <span>&rarr;</span>
            </button>
          </div>

          @if(isset($pinnedNotes) && $pinnedNotes->count() > 0)
            <div class="space-y-3 flex-1 flex flex-col justify-center pt-1">
              @foreach($pinnedNotes->take(2) as $idx => $pNote)
                @php
                  $pBg = match($pNote->color) {
                    'mint' => 'bg-[#D1FAE5]',
                    'blue' => 'bg-[#BAE6FD]',
                    'pink' => 'bg-[#FCE7F3]',
                    'orange' => 'bg-[#FED7AA]',
                    'lavender' => 'bg-[#E9D5FF]',
                    default => 'bg-[#FFF9A6]',
                  };
                  $rot = $idx === 0 ? 'rotate-[-1.5deg]' : 'rotate-[1.2deg]';
                @endphp
                <div class="relative group {{ $rot }} hover:rotate-0 hover:-translate-y-1 transition-all duration-200">
                  <!-- SELOTIP -->
                  <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-10 h-3 bg-ink/15 border border-ink/30 rotate-[-1deg] pointer-events-none z-10"></div>
                  <div onclick="toggleUnifiedPanel('kulkas')" class="p-2.5 brutal-border {{ $pBg }} block shadow-brutal cursor-pointer group-hover:shadow-brutal-sm">
                    <div class="flex items-center justify-between text-[10px] font-mono font-black text-ink mb-1">
                      <span class="truncate font-black">{{ $pNote->user->name ?? 'Keluarga' }}</span>
                      <span>📌</span>
                    </div>
                    <div class="font-display font-black text-xs text-ink truncate mb-0.5">
                      {{ $pNote->title }}
                    </div>
                    <div class="font-mono text-[11px] text-ink/80 line-clamp-2 leading-tight">
                      {{ $pNote->content }}
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="p-4 bg-cream brutal-border text-center my-auto space-y-1">
              <span class="text-2xl block animate-pulse">✨</span>
              <div class="font-display font-black text-xs text-ink">Kulkas Masih Bersih</div>
              <p class="font-mono text-[11px] text-ink/60">Belum ada catatan yang disematkan.</p>
              <a href="{{ route('notes.index') }}" class="inline-block mt-1 text-brand font-black hover:underline text-[11px]">
                + Tempel Memo Baru &rarr;
              </a>
            </div>
          @endif
        </div>

        <!-- KOLOM 2 (4 KOLOM DESKTOP): POLAROID MOMEN KENANGAN -->
        <div class="lg:col-span-4 bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between">
          <div class="flex items-center justify-between pb-2 border-b-2 border-ink mb-3 select-none">
            <div class="flex items-center gap-1.5">
              <span class="text-base">📷</span>
              <h4 class="font-display font-black text-xs sm:text-sm text-ink uppercase tracking-wide">
                Kilas Balik Momen
              </h4>
            </div>
            <button type="button" onclick="toggleUnifiedPanel('momen')" class="text-[11px] font-mono font-black text-brand hover:underline flex items-center gap-0.5">
              <span>Buka</span>
              <span>&rarr;</span>
            </button>
          </div>

          @if($latestMoment)
            <div onclick="toggleUnifiedPanel('momen')" class="bg-cream brutal-border p-2 cursor-pointer hover:shadow-brutal hover:-translate-y-0.5 transition-all group my-auto">
              <div class="relative h-32 sm:h-36 overflow-hidden brutal-border bg-ink/10 mb-2">
                @if($latestMoment->image_path)
                  <img src="{{ asset('storage/' . $latestMoment->image_path) }}" 
                       alt="{{ $latestMoment->title }}" 
                       class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                  <div class="w-full h-full flex flex-col items-center justify-center text-ink/40">
                    <span class="text-2xl">📸</span>
                    <span class="text-[10px] font-mono mt-1">Momen Terabadikan</span>
                  </div>
                @endif
                <div class="absolute bottom-1.5 right-1.5 px-2 py-0.5 bg-ink text-white font-mono text-[9px] font-bold brutal-border">
                  {{ \Carbon\Carbon::parse($latestMoment->event_date)->locale('id')->isoFormat('D MMM Y') }}
                </div>
              </div>
              <div class="font-display font-black text-xs text-ink truncate group-hover:text-brand transition-colors">
                {{ $latestMoment->title }}
              </div>
              @if($latestMoment->description)
                <p class="font-mono text-[10.5px] text-ink/70 line-clamp-1 mt-0.5">
                  "{{ $latestMoment->description }}"
                </p>
              @endif
            </div>
          @else
            <div class="p-4 bg-cream brutal-border text-center my-auto space-y-1">
              <span class="text-2xl block">🖼️</span>
              <div class="font-display font-black text-xs text-ink">Abadikan Kenangan</div>
              <p class="font-mono text-[11px] text-ink/60">Simpan momen indah kebersamaan keluarga.</p>
              <a href="{{ route('timeline.index') }}" class="inline-block mt-1 text-brand font-black hover:underline text-[11px]">
                Buka Linimasa &rarr;
              </a>
            </div>
          @endif
        </div>

        <!-- KOLOM 3 (4 KOLOM DESKTOP): AGENDA HARI INI & APRESIASI KELUARGA -->
        <div class="lg:col-span-4 bg-white brutal-card p-3.5 sm:p-4 flex flex-col justify-between">
          <div class="flex items-center justify-between pb-2 border-b-2 border-ink mb-3 select-none">
            <div class="flex items-center gap-1.5">
              <span class="text-base">📅</span>
              <h4 class="font-display font-black text-xs sm:text-sm text-ink uppercase tracking-wide">
                Agenda Santai
              </h4>
            </div>
            <button type="button" onclick="toggleUnifiedPanel('tugas')" class="text-[11px] font-mono font-black text-brand hover:underline flex items-center gap-0.5">
              <span>Buka</span>
              <span>&rarr;</span>
            </button>
          </div>

          <div class="space-y-2.5 flex-1 flex flex-col justify-between">
            @php $firstEv = $upcomingEvents->first(); @endphp
            @if($firstEv)
              <div onclick="toggleUnifiedPanel('tugas')" class="p-2.5 bg-cream brutal-border cursor-pointer hover:-translate-x-0.5 transition-transform">
                <div class="flex items-center justify-between text-[10px] font-mono font-bold text-ink/60 mb-0.5">
                  <span>AGENDA BERIKUTNYA</span>
                  <span class="font-bold text-ink">{{ \Carbon\Carbon::parse($firstEv->start_time)->locale('id')->isoFormat('D MMM') }}</span>
                </div>
                <div class="font-display font-black text-xs text-ink truncate">
                  {{ $firstEv->title }}
                </div>
                <div class="text-[10px] font-mono text-ink/70 mt-0.5">
                  📍 {{ $firstEv->location ?? 'Rumah' }}
                </div>
              </div>
            @else
              <div class="p-2.5 bg-cream brutal-border text-center">
                <div class="text-xs font-mono text-ink/60">Tidak ada agenda padat. Nikmati waktu santai bersama!</div>
              </div>
            @endif

            <!-- KUTIPAN KELUARGA / DAILY WARMTH -->
            <div class="p-2.5 bg-[#FFF9A6] brutal-border flex items-start gap-2">
              <span class="text-base shrink-0">🌿</span>
              <div class="min-w-0">
                <div class="text-[9.5px] font-mono font-black text-ink uppercase">Catatan Kasih Hari Ini</div>
                <p class="font-mono text-[10.5px] text-ink/80 italic line-clamp-2 leading-tight mt-0.5">
                  {{ $warmQuote }}
                </p>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

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

  // BANNER REPOSITION & FIT MANAGEMENT
  let isBannerAdjustMode = false;
  let bannerCurrentY = {{ $initY ?? 50 }};
  let bannerCurrentFit = '{{ $bannerFit ?? "cover" }}';
  let bannerInitialY = {{ $initY ?? 50 }};
  let bannerInitialFit = '{{ $bannerFit ?? "cover" }}';
  const bannerMediaUrl = '{{ $bannerMedia ? $bannerMedia->file_url : "" }}';
  const bannerCaption = '{{ addslashes($bannerMedia ? ($bannerMedia->caption ?? "Banner Keluarga " . $family->name) : "") }}';

  function toggleBannerKebabMenu(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    const menu = document.getElementById('banner-kebab-menu');
    if (menu) menu.classList.toggle('hidden');
  }

  function closeBannerKebabMenu() {
    const menu = document.getElementById('banner-kebab-menu');
    if (menu) menu.classList.add('hidden');
  }

  document.addEventListener('click', function(e) {
    const menu = document.getElementById('banner-kebab-menu');
    const btn = document.getElementById('btn-banner-kebab');
    if (menu && !menu.classList.contains('hidden')) {
      if (btn && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.add('hidden');
      }
    }
  });

  function handleBannerFrameClick(e) {
    if (isBannerAdjustMode) return;
    if (typeof showImagePreview === 'function' && bannerMediaUrl) {
      showImagePreview(bannerMediaUrl, bannerCaption);
    }
  }

  function toggleBannerAdjustMode() {
    isBannerAdjustMode = !isBannerAdjustMode;
    const toolbar = document.getElementById('banner-adjust-toolbar');
    const frame = document.getElementById('banner-container-frame');
    const dragGuide = document.getElementById('banner-drag-guide');
    const zoomHint = document.getElementById('banner-zoom-hint');
    const openBtn = document.getElementById('btn-open-adjust-banner');
    const overlays = document.getElementById('banner-hero-overlays');

    if (isBannerAdjustMode) {
      if (toolbar) toolbar.classList.remove('hidden');
      if (overlays) overlays.classList.add('opacity-0', 'pointer-events-none');
      if (frame) {
        frame.classList.remove('cursor-zoom-in');
        frame.classList.add('cursor-grab');
      }
      if (dragGuide && bannerCurrentFit === 'cover') {
        dragGuide.classList.remove('hidden');
        dragGuide.classList.add('flex');
        setTimeout(() => {
          dragGuide.classList.add('opacity-0', 'transition-opacity', 'duration-500');
          setTimeout(() => {
            dragGuide.classList.add('hidden');
            dragGuide.classList.remove('flex', 'opacity-0');
          }, 500);
        }, 1800);
      }
      if (zoomHint) zoomHint.classList.add('hidden');
      if (openBtn) openBtn.classList.add('bg-accent');
      initBannerDrag();
    } else {
      cancelBannerAdjust();
    }
  }

  function handleBannerSlider(val) {
    bannerCurrentY = parseInt(val, 10);
    applyBannerStyle();
  }

  function setBannerPreset(val) {
    bannerCurrentY = val;
    applyBannerStyle();
  }

  function setBannerFitMode(mode) {
    bannerCurrentFit = mode;
    const btnCover = document.getElementById('btn-fit-cover');
    const btnContain = document.getElementById('btn-fit-contain');
    const posControls = document.getElementById('banner-pos-controls');
    const backdrop = document.getElementById('banner-contain-backdrop');

    if (mode === 'cover') {
      if (btnCover) {
        btnCover.className = 'px-2 py-0.5 text-[10px] font-black uppercase transition-colors bg-ink text-white';
      }
      if (btnContain) {
        btnContain.className = 'px-2 py-0.5 text-[10px] font-black uppercase transition-colors text-ink hover:bg-cream';
      }
      if (posControls) {
        posControls.classList.remove('opacity-40', 'pointer-events-none');
      }
      if (backdrop) backdrop.classList.add('hidden');
    } else {
      if (btnCover) {
        btnCover.className = 'px-2 py-0.5 text-[10px] font-black uppercase transition-colors text-ink hover:bg-cream';
      }
      if (btnContain) {
        btnContain.className = 'px-2 py-0.5 text-[10px] font-black uppercase transition-colors bg-ink text-white';
      }
      if (posControls) {
        posControls.classList.add('opacity-40', 'pointer-events-none');
      }
      if (backdrop) backdrop.classList.remove('hidden');
    }
    applyBannerStyle();
  }

  function applyBannerStyle() {
    const img = document.getElementById('dashboard-banner-img');
    const label = document.getElementById('banner-pos-label');
    const slider = document.getElementById('banner-pos-slider');

    if (slider) slider.value = bannerCurrentY;
    if (label) label.textContent = bannerCurrentY + '%';

    if (img) {
      img.className = 'relative z-10 w-full h-full ' + (bannerCurrentFit === 'contain' ? 'object-contain' : 'object-cover') + ' transition-all duration-150 ease-out';
      img.style.objectPosition = '50% ' + bannerCurrentY + '%';
    }
  }

  function cancelBannerAdjust() {
    bannerCurrentY = bannerInitialY;
    setBannerFitMode(bannerInitialFit);
    applyBannerStyle();
    closeBannerAdjustUI();
  }

  function closeBannerAdjustUI() {
    isBannerAdjustMode = false;
    const toolbar = document.getElementById('banner-adjust-toolbar');
    const frame = document.getElementById('banner-container-frame');
    const zoomHint = document.getElementById('banner-zoom-hint');
    const openBtn = document.getElementById('btn-open-adjust-banner');
    const overlays = document.getElementById('banner-hero-overlays');

    if (toolbar) toolbar.classList.add('hidden');
    if (overlays) overlays.classList.remove('opacity-0', 'pointer-events-none');
    if (frame) {
      frame.classList.remove('cursor-grab', 'cursor-grabbing');
      frame.classList.add('cursor-zoom-in');
    }
    if (zoomHint) zoomHint.classList.remove('hidden');
    if (openBtn) openBtn.classList.remove('bg-accent');
  }

  async function saveBannerAdjust() {
    const btn = document.getElementById('btn-save-banner-pos');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span>⏳</span> <span>Menyimpan...</span>';
    }

    try {
      const res = await fetch('{{ route('albums.banner.position') }}', {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          position: `50% ${bannerCurrentY}%`,
          fit: bannerCurrentFit,
        }),
      });

      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        throw new Error(data.message || 'Gagal menyimpan posisi banner.');
      }

      if (typeof showToast === 'function') {
        showToast(data.message || 'Posisi banner berhasil disimpan!');
      }

      closeBannerAdjustUI();
      bannerInitialY = bannerCurrentY;
      bannerInitialFit = bannerCurrentFit;
    } catch (err) {
      if (typeof showToast === 'function') {
        showToast(err.message, 'error');
      } else {
        alert(err.message);
      }
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<span>✓</span> <span>Simpan Posisi</span>';
      }
    }
  }

  let isDraggingBanner = false;
  let dragStartY = 0;
  let dragStartVal = 50;

  function initBannerDrag() {
    const frame = document.getElementById('banner-container-frame');
    if (!frame || frame._dragInitialized) return;
    frame._dragInitialized = true;

    function onPointerDown(e) {
      if (!isBannerAdjustMode || bannerCurrentFit === 'contain') return;
      isDraggingBanner = true;
      dragStartY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
      dragStartVal = bannerCurrentY;
      frame.classList.remove('cursor-grab');
      frame.classList.add('cursor-grabbing');
      e.preventDefault();
    }

    function onPointerMove(e) {
      if (!isDraggingBanner) return;
      const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
      const deltaY = clientY - dragStartY;
      const frameHeight = frame.offsetHeight || 250;
      const deltaPercent = (deltaY / frameHeight) * 100;
      let newY = Math.round(dragStartVal - deltaPercent);
      if (newY < 0) newY = 0;
      if (newY > 100) newY = 100;
      bannerCurrentY = newY;
      applyBannerStyle();
    }

    function onPointerUp() {
      if (!isDraggingBanner) return;
      isDraggingBanner = false;
      frame.classList.remove('cursor-grabbing');
      frame.classList.add('cursor-grab');
    }

    frame.addEventListener('mousedown', onPointerDown);
    window.addEventListener('mousemove', onPointerMove);
    window.addEventListener('mouseup', onPointerUp);

    frame.addEventListener('touchstart', onPointerDown, { passive: false });
    window.addEventListener('touchmove', onPointerMove, { passive: true });
    window.addEventListener('touchend', onPointerUp);
  }

  // KONTROL 4 TOMBOL TERPADU (UNIFIED DASHBOARD PANELS DENGAN ANIMASI)
  let activeUnifiedPanel = null;

  function runCountUpAnimations() {
    $('.countup-val').each(function() {
      const el = $(this);
      const target = parseInt(el.attr('data-target') || '0', 10);
      const isRp = el.attr('data-is-rp') === '1';
      const prefix = el.attr('data-prefix') || '';
      const isNegative = target < 0;
      const absTarget = Math.abs(target);
      const duration = 450;
      const startTime = performance.now();

      function updateNumber(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeOutQuad = 1 - (1 - progress) * (1 - progress);
        const current = Math.floor(easeOutQuad * absTarget);
        const formattedNumber = current.toLocaleString('id-ID');
        const sign = isNegative ? '-' : '';
        const display = isRp ? sign + (prefix ? prefix : '') + 'Rp ' + formattedNumber : (prefix ? prefix : '') + sign + formattedNumber;
        el.text(display);
        if (progress < 1) {
          requestAnimationFrame(updateNumber);
        }
      }
      requestAnimationFrame(updateNumber);
    });
  }

  function toggleUnifiedPanel(key) {
    const container = $('#unified-panel-container');
    const welcomeFeed = $('#family-welcome-feed');
    const title = $('#unified-panel-title');
    const icon = $('#unified-panel-icon');

    // Jika mengklik tombol yang sedang aktif -> tutup panel
    if (activeUnifiedPanel === key) {
      closeUnifiedPanel();
      return;
    }

    activeUnifiedPanel = key;
    if (window.vibrateDevice) window.vibrateDevice(6);

    // Reset tombol ke keadaan normal
    $('.unified-tab-btn')
      .removeClass('translate-x-[2px] translate-y-[2px] shadow-none ring-2 ring-ink')
      .addClass('shadow-brutal');
    $('.active-dot').addClass('hidden');

    // Beri gaya aktif (tekan ke bawah) pada tombol yang dipilih
    const activeBtn = $('#btn-unified-' + key);
    activeBtn
      .removeClass('shadow-brutal')
      .addClass('translate-x-[2px] translate-y-[2px] shadow-none ring-2 ring-ink');
    activeBtn.find('.active-dot').removeClass('hidden');

    // Sembunyikan welcome feed saat panel detail aktif
    welcomeFeed.addClass('hidden');

    // Ganti panel tampilan
    $('.unified-panel-view').addClass('hidden');
    $('#panel-view-' + key).removeClass('hidden');

    const meta = {
      kulkas: { title: 'Papan Kulkas Keluarga (Memo Disematkan)', icon: '📌' },
      momen: { title: 'Kilas Balik Momen Kenangan', icon: '📷' },
      tugas: { title: 'Tugas Rumah & Agenda Mendatang', icon: '🧹' },
      statistik: { title: 'Ringkasan Finansial, Poin & Grafik', icon: '📊' },
    };

    if (meta[key]) {
      title.text(meta[key].title);
      icon.text(meta[key].icon);
    }

    // Tampilkan panel dengan animasi spring pop
    container.removeClass('hidden animate-spring-pop');
    void container[0].offsetWidth; // trigger reflow
    container.addClass('animate-spring-pop');

    // Eksekusi animasi khusus per panel
    if (key === 'statistik') {
      runCountUpAnimations();
      setTimeout(function() {
        if (cashflowChartInstance) cashflowChartInstance.resize();
        if (categoryDonutInstance) categoryDonutInstance.resize();
        if (memberPointsInstance) memberPointsInstance.resize();
        window.dispatchEvent(new Event('resize'));
      }, 50);
    } else if (key === 'tugas') {
      setTimeout(function() {
        if (choreRingInstance) choreRingInstance.resize();
        window.dispatchEvent(new Event('resize'));
      }, 50);
    }
  }

  function closeUnifiedPanel() {
    activeUnifiedPanel = null;
    $('#unified-panel-container').addClass('hidden').removeClass('animate-spring-pop');
    $('.unified-panel-view').addClass('hidden');
    $('.unified-tab-btn')
      .removeClass('translate-x-[2px] translate-y-[2px] shadow-none ring-2 ring-ink')
      .addClass('shadow-brutal');
    $('.active-dot').addClass('hidden');

    // Kembalikan welcome feed dengan animasi halus
    const welcomeFeed = $('#family-welcome-feed');
    welcomeFeed.removeClass('hidden animate-spring-pop');
    void welcomeFeed[0].offsetWidth;
    welcomeFeed.addClass('animate-spring-pop');
  }

  $(document).ready(function() {
    initCashflowChart('bar');
    initCategoryDonutChart();
    initMemberPointsChart();
    initChoreRingChart();  });
</script>
@endpush
