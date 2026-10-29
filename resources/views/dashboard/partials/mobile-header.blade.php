@php
  $user = $user ?? auth()->user();
  $family = $family ?? ($user ? $user->family : null);
  $isPro = $isPro ?? ($family ? $family->isPro() : false);
  $userInitial = strtoupper(substr($user->name ?? 'U', 0, 1));
  $roleBg = match($user->role ?? '') {
    'Ayah' => 'bg-brand text-white',
    'Ibu' => 'bg-accent text-ink',
    'Anak' => 'bg-sky text-ink',
    default => 'bg-cream text-ink'
  };

  // Label konteks halaman saat ini
  $pageTitle = 'Dashboard';
  if (request()->routeIs('chores.*')) {
    $pageTitle = 'Tugas & Chores';
  } elseif (request()->routeIs('finances.*')) {
    $pageTitle = 'Kas & Tabungan';
  } elseif (request()->routeIs('albums.*')) {
    $pageTitle = 'Album Keluarga';
  } elseif (request()->routeIs('events.*')) {
    $pageTitle = 'Kalender & Agenda';
  } elseif (request()->routeIs('timeline.*')) {
    $pageTitle = 'Linimasa & Trip';
  } elseif (request()->routeIs('pantry.*')) {
    $pageTitle = 'Stok & Pantry';
  } elseif (request()->routeIs('notes.*')) {
    $pageTitle = 'Catatan Keluarga';
  } elseif (request()->routeIs('members.*')) {
    $pageTitle = 'Anggota Keluarga';
  }
@endphp

<!-- MOBILE TOP APP BAR (MODERN CLEAN NEOBRUTALISM) -->
<header id="mobile-top-bar" class="lg:hidden sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b-2 border-ink px-3.5 py-2 pt-safe select-none transition-shadow duration-200">
  <div class="flex items-center justify-between gap-2 max-w-lg mx-auto">
    
    <!-- KIRI: HAMBURGER DRAWER BUTTON & BRAND LOGO -->
    <div class="flex items-center gap-2 min-w-0">
      <button type="button" 
              onclick="if(window.vibrateDevice) window.vibrateDevice(8); toggleMobileSidebar();" 
              class="w-9 h-9 bg-cream hover:bg-accent border-2 border-ink flex items-center justify-center text-ink shrink-0 shadow-[2px_2px_0px_#121614] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all rounded-lg" 
              aria-label="Buka Menu" 
              title="Menu Navigasi">
        <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
        </svg>
      </button>

      <a href="{{ route('dashboard') }}" 
         onclick="if(window.vibrateDevice) window.vibrateDevice(6);"
         class="w-9 h-9 bg-brand-soft border-2 border-ink p-1 rounded-lg flex items-center justify-center shadow-[2px_2px_0px_#121614] shrink-0 active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all" 
         title="Beranda FamKoji"
         aria-label="Beranda">
        <img src="{{ asset('icons/new_logo.png') }}" alt="FamKoji" class="w-full h-full object-contain">
      </a>
    </div>

    <!-- TENGAH: NAMA KELUARGA & KONTEKS HALAMAN AKTIF -->
    <div class="flex-1 min-w-0 text-center px-1">
      <div class="flex items-center justify-center gap-1.5 leading-tight">
        <span class="w-2 h-2 rounded-full bg-brand inline-block animate-pulse shrink-0"></span>
        <span class="font-display font-black text-xs sm:text-sm text-ink truncate">
          {{ $family->name ?? 'FamKoji' }}
        </span>
        @if($isPro)
          <span class="px-1 py-0.5 bg-accent border border-ink text-[8px] font-mono font-black shadow-[1px_1px_0px_#121614] rounded shrink-0">
            PRO
          </span>
        @endif
      </div>
      <p class="text-[10px] font-mono font-semibold text-ink/60 truncate leading-none mt-0.5">
        {{ $pageTitle }}
      </p>
    </div>

    <!-- KANAN: OFFLINE STATUS & AVATAR PROFILE DROPDOWN -->
    <div class="flex items-center gap-1.5 shrink-0">
      <!-- Offline status badge (PWA) -->
      <div id="mobile-offline-badge" class="hidden items-center gap-1 px-1.5 py-0.5 bg-coral text-white border-2 border-ink text-[9px] font-mono font-black rounded shadow-[1px_1px_0px_#121614]">
        <svg class="w-3 h-3 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        <span>Offline</span>
      </div>

      <!-- AVATAR PROFILE CONTAINER -->
      <div class="relative" id="mobile-profile-container">
        <button type="button" 
                id="mobile-profile-btn"
                onclick="if(window.vibrateDevice) window.vibrateDevice(6); toggleMobileProfileDropdown();" 
                class="w-9 h-9 {{ $roleBg }} border-2 border-ink rounded-lg shadow-[2px_2px_0px_#121614] font-black text-xs flex items-center justify-center active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all overflow-hidden" 
                aria-expanded="false" 
                aria-haspopup="true"
                title="Profil, Poin & Keluar">
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
             class="hidden absolute right-0 mt-2.5 w-64 bg-white border-2 border-ink rounded-xl shadow-[4px_4px_0px_#121614] p-3 z-50 flex-col gap-2.5">
          
          <!-- HEADER PROFIL -->
          <div class="flex items-center gap-2.5 pb-2.5 border-b-2 border-ink">
            <div id="mobile-dropdown-avatar" class="w-10 h-10 {{ $roleBg }} border-2 border-ink rounded-lg font-black text-sm flex items-center justify-center shrink-0 shadow-[1.5px_1.5px_0px_#121614] overflow-hidden">
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
                  class="w-full py-1.5 px-2 bg-brand-soft hover:bg-brand text-brand hover:text-white border-2 border-ink rounded-lg font-mono font-black text-xs flex items-center justify-center gap-2 transition-colors">
            <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
            </svg>
            <span>Edit Profil & Foto</span>
          </button>

          <!-- POIN PENGGUNA -->
          <div class="flex items-center justify-between p-2 bg-cream border-2 border-ink rounded-lg">
            <div class="flex items-center gap-1.5 text-xs font-mono font-black text-ink">
              <svg class="w-4 h-4 text-brand fill-brand" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              <span>Poin Kamu</span>
            </div>
            <span id="mobile-header-points-val" class="font-mono font-black text-sm text-brand">
              {{ number_format($user->points ?? 0) }}
            </span>
          </div>

          @if($family && isset($family->invite_code))
            <!-- KODE UNDANGAN -->
            <div class="flex items-center justify-between px-2.5 py-1.5 bg-brand-soft border-2 border-ink rounded-lg text-[10px] font-mono">
              <span class="font-black text-brand uppercase tracking-wider">Undangan:</span>
              <button type="button" 
                      id="mobile-btn-copy-code"
                      onclick="copyInviteCodeMobile('{{ $family->invite_code }}')" 
                      class="px-2 py-0.5 bg-white hover:bg-accent border-2 border-ink rounded font-black text-ink flex items-center gap-1.5 active:scale-95 transition-transform" 
                      title="Salin Kode Undangan">
                <span>{{ $family->invite_code }}</span>
                <span id="mobile-copy-icon">
                  <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                  </svg>
                </span>
              </button>
            </div>
          @endif

          <!-- TOMBOL LOGOUT -->
          <form action="{{ route('logout') }}" method="POST" class="w-full pt-1">
            @csrf
            <button type="submit" 
                    class="w-full py-2 px-3 bg-coral text-white border-2 border-ink rounded-lg font-mono font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-[2px_2px_0px_#121614] hover:bg-red-600 active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer">
              <span>Keluar Akun</span>
              <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
              </svg>
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>
</header>
