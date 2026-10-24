@php
  $user = $user ?? auth()->user();
  $family = $family ?? ($user ? $user->family : null);
  $isPro = $isPro ?? ($family ? $family->isPro() : false);
  $isMore = request()->routeIs('members.*');
  $userInitial = strtoupper(substr($user->name ?? 'U', 0, 1));
  $roleBg = match($user->role ?? '') {
    'Ayah' => 'bg-brand text-white',
    'Ibu' => 'bg-accent text-ink',
    'Anak' => 'bg-sky text-ink',
    default => 'bg-cream text-ink'
  };
@endphp

<!-- MOBILE TOP APP BAR (KHUSUS MOBILE / PWA) -->
<header id="mobile-top-bar" class="lg:hidden sticky top-0 z-30 bg-white/95 backdrop-blur-md brutal-border border-x-0 border-t-0 px-3 py-2 pt-safe flex items-center justify-between gap-2 select-none transition-shadow duration-200">
  
  <!-- KIRI: TOMBOL MENU & BRANDING KELUARGA -->
  <div class="flex items-center gap-2 min-w-0">
    <!-- Tombol Menu (Sidebar Drawer) -->
    <button type="button" 
            onclick="if(window.vibrateDevice) window.vibrateDevice(8); toggleMobileSidebar();" 
            class="w-8 h-8 bg-cream hover:bg-accent brutal-border flex items-center justify-center text-ink shrink-0 shadow-[1.5px_1.5px_0px_#121614] active:scale-95 transition-transform" 
            aria-label="Buka Menu" 
            title="Menu Navigasi">
      <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
      </svg>
    </button>

    <a href="{{ route('dashboard') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(6);"
       class="w-8 h-8 bg-brand-soft brutal-border p-0.5 rounded-lg flex items-center justify-center shadow-[1.5px_1.5px_0px_#121614] shrink-0 active:scale-95 transition-transform" 
       title="Beranda FamKoji"
       aria-label="Beranda">
      <img src="{{ asset('icons/new_logo.png') }}" alt="FamKoji Logo" class="w-full h-full object-contain">
    </a>
    
    <div class="min-w-0">
      <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-brand inline-block animate-pulse shrink-0" title="Ruang Keluarga Aktif"></span>
        <span class="font-display font-black text-xs sm:text-sm text-ink truncate leading-tight">
          {{ $family->name ?? 'FamKoji' }}
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

  <!-- KANAN: OFFLINE STATUS & AVATAR PROFILE DROPDOWN DENGAN LOGOUT -->
  <div class="flex items-center gap-1.5 shrink-0">
    <!-- Offline status badge (PWA) -->
    <div id="mobile-offline-badge" class="hidden items-center gap-1 px-1.5 py-0.5 bg-coral text-white brutal-border text-[9px] font-mono font-black animate-badge-pulse">
      <span>📡</span>
      <span>Offline</span>
    </div>

    <!-- AVATAR PROFILE CONTAINER -->
    <div class="relative" id="mobile-profile-container">
      <button type="button" 
              id="mobile-profile-btn"
              onclick="if(window.vibrateDevice) window.vibrateDevice(6); toggleMobileProfileDropdown();" 
              class="w-8 h-8 {{ $roleBg }} brutal-border shadow-[1.5px_1.5px_0px_#121614] font-black text-xs flex items-center justify-center active:scale-95 transition-transform overflow-hidden" 
              aria-expanded="false" 
              aria-haspopup="true"
              title="Profil, Poin & Logout">
        <span id="mobile-profile-btn-content" class="w-full h-full flex items-center justify-center">
          @if($user && $user->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
          @else
            {{ $userInitial }}
          @endif
        </span>
      </button>

      <!-- DROPDOWN POPOVER -->
      <div id="mobile-profile-dropdown" 
           class="hidden absolute right-0 mt-2 w-64 bg-white brutal-border shadow-[4px_4px_0px_#121614] p-3 z-50 flex-col gap-2.5">
        
        <!-- HEADER PROFIL -->
        <div class="flex items-center gap-2.5 pb-2 border-b-2 border-ink">
          <div id="mobile-dropdown-avatar" class="w-9 h-9 {{ $roleBg }} brutal-border font-black text-sm flex items-center justify-center shrink-0 shadow-[1px_1px_0px_#121614] overflow-hidden">
            @if($user && $user->avatar_url)
              <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
            @else
              <span>{{ $userInitial }}</span>
            @endif
          </div>
          <div class="min-w-0 flex-1">
            <div id="mobile-header-user-name" class="font-black text-xs text-ink truncate leading-tight">{{ $user->name ?? 'User' }}</div>
            <div class="text-[10px] font-mono font-bold text-ink/60 truncate flex items-center gap-1 mt-0.5">
              <span>{{ $user->role ?? 'Anggota' }}</span>
              @if($isPro)
                <span class="text-brand font-black">&bull; PRO 👑</span>
              @endif
            </div>
          </div>
        </div>

        <!-- EDIT PROFIL ACTION -->
        <button type="button" 
                onclick="toggleMobileProfileDropdown(); openProfileModal();" 
                class="w-full py-1.5 px-2 bg-brand-soft hover:bg-brand text-brand hover:text-white brutal-border font-mono font-black text-xs flex items-center justify-center gap-1.5 transition-colors">
          <span>✏️ Edit Profil & Foto</span>
        </button>

        <!-- POIN PENGGUNA -->
        <div class="flex items-center justify-between p-2 bg-cream brutal-border">
          <div class="flex items-center gap-1.5 text-xs font-mono font-black text-ink">
            <span>⭐</span>
            <span>Poin Kamu</span>
          </div>
          <span id="mobile-header-points-val" class="font-mono font-black text-sm text-brand">
            {{ number_format($user->points ?? 0) }}
          </span>
        </div>

        @if($family && isset($family->invite_code))
          <!-- KODE UNDANGAN -->
          <div class="flex items-center justify-between px-2 py-1.5 bg-brand-soft brutal-border text-[10px] font-mono">
            <span class="font-black text-brand uppercase">Undangan:</span>
            <button type="button" 
                    id="mobile-btn-copy-code"
                    onclick="copyInviteCodeMobile('{{ $family->invite_code }}')" 
                    class="px-1.5 py-0.5 bg-white hover:bg-accent brutal-border font-black text-ink flex items-center gap-1 active:scale-95 transition-transform" 
                    title="Salin Kode Undangan">
              <span>{{ $family->invite_code }}</span>
              <span id="mobile-copy-icon">📋</span>
            </button>
          </div>
        @endif

        <!-- TOMBOL LOGOUT -->
        <form action="{{ route('logout') }}" method="POST" class="w-full pt-1">
          @csrf
          <button type="submit" 
                  class="w-full py-2 px-3 bg-coral text-white brutal-border font-mono font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-[2px_2px_0px_#121614] hover:bg-red-600 active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer">
            <span>Keluar Akun</span>
            <span>🚪</span>
          </button>
        </form>
      </div>
    </div>
  </div>

</header>
