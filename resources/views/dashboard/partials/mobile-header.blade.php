@php
  $user = $user ?? auth()->user();
  $family = $family ?? ($user ? $user->family : null);
  $isPro = $isPro ?? ($family ? $family->isPro() : false);
  $isMore = request()->routeIs('members.*');
@endphp

<!-- MOBILE TOP APP BAR (KHUSUS MOBILE / PWA) -->
<header id="mobile-top-bar" class="lg:hidden sticky top-0 z-30 bg-white/95 backdrop-blur-md brutal-border border-x-0 border-t-0 px-3 py-2 pt-safe flex items-center justify-between gap-2 select-none transition-shadow duration-200">
  
  <!-- KIRI: BRANDING & INFO KELUARGA -->
  <div class="flex items-center gap-2.5 min-w-0">
    <a href="{{ route('dashboard') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(6);"
       class="w-8 h-8 bg-brand text-white brutal-border flex items-center justify-center font-black text-sm font-display shadow-[1.5px_1.5px_0px_#121614] shrink-0 active:scale-95 transition-transform" 
       title="Beranda Famespace"
       aria-label="Beranda">
      f
    </a>
    
    <div class="min-w-0">
      <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-brand inline-block animate-pulse shrink-0" title="Ruang Keluarga Aktif"></span>
        <span class="font-display font-black text-xs sm:text-sm text-ink truncate leading-tight">
          {{ $family->name ?? 'Famespace' }}
        </span>
        @if($isPro)
          <span class="px-1 py-0.2 bg-accent brutal-border text-[8px] font-mono font-black shadow-[1px_1px_0px_#121614] shrink-0">
            PRO 👑
          </span>
        @endif
      </div>
      <p class="text-[9px] sm:text-[10px] font-mono text-ink/60 truncate leading-tight mt-0.5">
        {{ $user->name ?? 'Keluarga' }} &bull; {{ $user->role ?? 'Anggota' }}
      </p>
    </div>
  </div>

  <!-- KANAN: POIN & QUICK ACTIONS -->
  <div class="flex items-center gap-1.5 shrink-0">
    <!-- Offline status badge (PWA) -->
    <div id="mobile-offline-badge" class="hidden items-center gap-1 px-1.5 py-0.5 bg-coral text-white brutal-border text-[9px] font-mono font-black animate-badge-pulse">
      <span>📡</span>
      <span>Offline</span>
    </div>

    <!-- Points Badge (Interactive Tap & Wiggle) -->
    <button type="button" 
            id="mobile-points-badge"
            onclick="animatePointsPill({{ $user->points ?? 0 }})"
            class="flex items-center gap-1 px-1.5 py-1 bg-cream hover:bg-accent brutal-border shadow-[1.5px_1.5px_0px_#121614] text-[10px] sm:text-[11px] font-mono font-black touch-press transition-colors" 
            title="Ketuk untuk melihat poin keluarga">
      <span id="mobile-points-star" class="inline-block transition-transform text-xs">⭐</span>
      <span id="mobile-header-points-val">{{ number_format($user->points ?? 0) }}</span>
    </button>

    @if($family && isset($family->invite_code))
      <!-- Invite Code Button with Instant Visual Feedback -->
      <button type="button" 
              id="mobile-btn-copy-code"
              onclick="copyInviteCodeMobile('{{ $family->invite_code }}')" 
              title="Salin Kode Undangan Keluarga" 
              class="w-7 h-7 bg-white hover:bg-cream brutal-border flex items-center justify-center shadow-[1.5px_1.5px_0px_#121614] active:translate-x-[1px] active:translate-y-[1px] touch-press transition-all">
        <span id="mobile-copy-icon" class="text-xs">📋</span>
      </button>
    @endif
  </div>

</header>
