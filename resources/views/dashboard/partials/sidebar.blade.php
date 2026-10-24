<!-- SIDEBAR KIRI TETAP (CHUNKY NEOBRUTALIST) -->
<aside id="sidebar-aside" class="w-72 max-w-[85vw] bg-white brutal-border border-l-0 border-y-0 h-[100dvh] max-h-[100dvh] fixed lg:sticky top-0 left-0 -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-out flex flex-col p-4 flex-shrink-0 z-50 shadow-2xl lg:shadow-none overflow-y-auto overscroll-y-contain custom-scrollbar touch-pan-y">
  
  <div class="space-y-4">
    <!-- LOGO & FAMILY BADGE -->
    <div class="pb-4 border-b-2 border-ink flex-shrink-0 space-y-3">
      <div class="flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
          <div class="w-9 h-9 bg-brand text-white brutal-border flex items-center justify-center font-black text-xl font-display group-hover:rotate-6 transition-transform shadow-[2px_2px_0px_#121614]">
            f
          </div>
          <div class="leading-none">
            <span class="font-display font-black text-xl tracking-tight text-ink">famespace<span class="text-brand">.</span></span>
            <span class="block text-[9px] font-mono font-bold text-ink/50 uppercase tracking-widest mt-0.5">Family OS</span>
          </div>
        </a>
        <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden w-8 h-8 bg-cream hover:bg-accent brutal-border text-ink flex items-center justify-center text-xs font-bold transition-colors" aria-label="Tutup Menu">
          ✕
        </button>
      </div>

      <!-- CURRENT FAMILY SPACE CARD -->
      <div class="p-3 bg-brand-soft brutal-border space-y-2.5">
        <div class="flex items-center justify-between text-[10px] font-mono font-black text-brand uppercase tracking-wider">
          <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-brand inline-block animate-pulse"></span>
            Ruang Aktif
          </span>
          <button type="button" 
                  onclick="copyInviteCode('{{ $family->invite_code }}')" 
                  class="px-2 py-0.5 bg-white hover:bg-accent brutal-border text-[9px] tracking-normal font-mono font-black text-ink cursor-pointer active:scale-95 transition-all flex items-center gap-1 shadow-[1px_1px_0px_#121614]" 
                  title="Klik untuk Salin Kode Undangan">
            <span>{{ $family->invite_code }}</span>
            <span id="sidebar-copy-icon">📋</span>
          </button>
        </div>
        <div>
          <div class="font-black text-xs text-ink truncate flex items-center gap-1.5">
            <span>🏡</span>
            <span class="truncate">{{ $family->name }}</span>
          </div>
          <div class="text-[10px] font-mono font-bold text-ink/60 mt-1.5 flex items-center justify-between">
            <div class="flex items-center gap-1.5 truncate">
              <span>👥 {{ $members->count() }} Anggota</span>
              <span>&bull;</span>
              <span class="{{ $isPro ? 'text-brand font-black' : 'text-ink/60' }}">{{ $isPro ? 'PRO 👑' : 'Free' }}</span>
            </div>
            <!-- POIN PENGGUNA -->
            <div class="px-2 py-0.5 bg-cream brutal-border font-mono font-black text-[10px] text-ink flex items-center gap-1 shrink-0 shadow-[1px_1px_0px_#121614]">
              <span>⭐</span>
              <span id="sidebar-user-points-val">{{ number_format($user->points ?? 0) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- NAVIGATION MENU -->
    <nav class="space-y-4" id="sidebar-nav">
      <!-- SECTION: UTAMA -->
      <div>
        <div class="text-[9px] font-mono font-black uppercase tracking-wider text-ink/50 px-2 pb-1.5 flex items-center gap-1">
          <span>●</span>
          <span>Aktivitas Harian</span>
        </div>
        <div class="space-y-1">
          <a href="{{ route('dashboard') }}" id="nav-overview" class="w-full text-left px-2.5 py-2 rounded-none {{ request()->routeIs('dashboard') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">📊</span>
              <span class="font-bold">Ringkasan</span>
            </div>
          </a>

          <a href="{{ route('chores.index') }}" id="nav-chores" class="w-full text-left px-2.5 py-2 rounded-none {{ request()->routeIs('chores.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">🧹</span>
              <span class="font-bold">Tugas & Chores</span>
            </div>
            <span id="sidebar-chore-count" class="px-1.5 py-0.5 bg-coral text-white font-mono text-[10px] brutal-border font-bold">
              {{ $chores->where('status', 'pending')->count() }}
            </span>
          </a>

          <a href="{{ route('finances.index') }}" id="nav-finance" class="w-full text-left px-2.5 py-2 rounded-none {{ request()->routeIs('finances.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">💰</span>
              <span class="font-bold">Kas & Tabungan</span>
            </div>
          </a>
        </div>
      </div>

      <!-- SECTION: PERENCANAAN -->
      <div>
        <div class="text-[9px] font-mono font-black uppercase tracking-wider text-ink/50 px-2 pb-1.5 flex items-center gap-1">
          <span>●</span>
          <span>Perencanaan & Agenda</span>
        </div>
        <div class="space-y-1">
          @if($isPro)
            <a href="{{ route('timeline.index') }}" id="nav-timeline" class="w-full text-left px-2.5 py-2 rounded-none {{ request()->routeIs('timeline.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">🗺️</span>
                <span class="font-bold">Linimasa & Trip</span>
              </div>
              <span id="badge-nav-timeline" class="px-1.5 py-0.5 bg-brand text-white font-mono text-[10px] brutal-border font-bold">
                PRO ✓
              </span>
            </a>
          @else
            <button type="button" onclick="openPaywallModal('timeline')" id="nav-timeline" class="w-full text-left px-2.5 py-2 rounded-none nav-item flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">🗺️</span>
                <span class="font-bold">Linimasa & Trip</span>
              </div>
              <span id="badge-nav-timeline" class="px-1.5 py-0.5 bg-coral text-white font-mono text-[10px] brutal-border font-bold">
                🔒 PRO
              </span>
            </button>
          @endif

          @if($isPro)
            <a href="{{ route('events.index') }}" id="nav-calendar" class="w-full text-left px-2.5 py-2 rounded-none {{ request()->routeIs('events.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">📅</span>
                <span class="font-bold">Kalender & Agenda</span>
              </div>
              <span id="badge-nav-calendar" class="px-1.5 py-0.5 bg-brand text-white font-mono text-[10px] brutal-border font-bold">
                PRO ✓
              </span>
            </a>
          @else
            <button type="button" onclick="openPaywallModal('calendar')" id="nav-calendar" class="w-full text-left px-2.5 py-2 rounded-none nav-item flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">📅</span>
                <span class="font-bold">Kalender & Agenda</span>
              </div>
              <span id="badge-nav-calendar" class="px-1.5 py-0.5 bg-coral text-white font-mono text-[10px] brutal-border font-bold">
                🔒 PRO
              </span>
            </button>
          @endif
        </div>
      </div>

      <!-- SECTION: KELUARGA -->
      <div>
        <div class="text-[9px] font-mono font-black uppercase tracking-wider text-ink/50 px-2 pb-1.5 flex items-center gap-1">
          <span>●</span>
          <span>Ruang Komunitas</span>
        </div>
        <div class="space-y-1">
          <a href="{{ route('members.index') }}" id="nav-members" class="w-full text-left px-2.5 py-2 rounded-none {{ request()->routeIs('members.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">👨‍👩‍👧‍👦</span>
              <span class="font-bold">Anggota Keluarga</span>
            </div>
            <span class="px-1.5 py-0.5 bg-cream text-ink font-mono text-[10px] brutal-border font-bold">
              {{ $members->count() }}
            </span>
          </a>

          <a href="{{ route('albums.index') }}" id="nav-albums" class="w-full text-left px-2.5 py-2 rounded-none {{ request()->routeIs('albums.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">📸</span>
              <span class="font-bold">Album Keluarga</span>
            </div>
            <span class="px-1.5 py-0.5 bg-brand-soft text-brand font-mono text-[10px] brutal-border font-bold">
              {{ $family->albums()->count() }}
            </span>
          </a>

          <a href="{{ route('notes.index') }}" id="nav-notes" class="w-full text-left px-2.5 py-2 rounded-none {{ request()->routeIs('notes.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <span class="w-6 h-6 flex items-center justify-center text-sm flex-shrink-0">📌</span>
              <span class="font-bold">Catatan & Kulkas</span>
            </div>
            <span class="px-1.5 py-0.5 bg-accent text-ink font-mono text-[10px] brutal-border font-bold">
              {{ $family->notes()->count() }}
            </span>
          </a>
        </div>
      </div>
    </nav>
  </div>

  <!-- BOTTOM PROFILE CARD & LOGOUT -->
  <div class="mt-auto pt-3 border-t-2 border-ink space-y-2.5 flex-shrink-0">
    <!-- SUBSCRIPTION STATUS -->
    <div class="p-2.5 bg-cream brutal-border space-y-1.5">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-mono font-black uppercase text-ink/70">Paket Akun</span>
        <span id="plan-badge-sidebar" class="px-1.5 py-0.5 text-[9px] font-black font-mono brutal-border {{ $isPro ? 'bg-accent text-ink' : 'bg-coral text-white' }}">
          {{ $isPro ? (($subscription && $subscription->plan && $subscription->plan->isLifetime()) ? 'LIFETIME 👑' : 'PRO 👑') : 'FREE' }}
        </span>
      </div>
      <div class="flex items-center justify-between text-[10px] font-bold">
        <span id="plan-desc-sidebar" class="text-ink/80 font-mono">
          @if($isPro)
            {{ ($subscription && $subscription->plan && $subscription->plan->isLifetime()) ? 'Aktif (Lifetime)' : 'Aktif (Family Pro)' }}
          @else
            Free Starter
          @endif
        </span>
        @if(!$isPro)
          <button type="button" onclick="openPaywallModal('Fitur Pro Famespace')" class="px-2 py-0.5 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-[10px]">
            Upgrade
          </button>
        @endif
      </div>
    </div>

    <!-- PROFILE CARD -->
    <div class="flex items-center justify-between p-2 bg-cream brutal-border">
      <div onclick="openProfileModal()" class="flex items-center gap-2.5 min-w-0 cursor-pointer flex-1 group" title="Klik untuk edit profil & foto">
        <div id="sidebar-user-avatar" class="w-8 h-8 bg-brand text-white brutal-border font-black text-xs flex items-center justify-center flex-shrink-0 overflow-hidden group-hover:ring-2 group-hover:ring-brand transition-all">
          @if($user->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
          @else
            <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
          @endif
        </div>
        <div class="min-w-0">
          <div id="sidebar-user-name" class="font-black text-xs text-ink leading-tight truncate group-hover:text-brand transition-colors" title="{{ $user->name }}">{{ $user->name }}</div>
          <div class="text-[10px] font-mono text-brand font-bold truncate flex items-center gap-1">
            <span>{{ $user->role ?? 'Kepala Keluarga' }}</span>
            <span class="text-[9px] text-ink/40 group-hover:text-brand">✏️</span>
          </div>
        </div>
      </div>
      <form action="{{ route('logout') }}" method="POST" class="inline flex-shrink-0 ml-1">
        @csrf
        <button type="submit" title="Keluar Akun" class="w-7 h-7 bg-white brutal-border hover:bg-coral hover:text-white flex items-center justify-center text-xs font-bold transition-colors">
          🚪
        </button>
      </form>
    </div>

    <!-- FOOTER INFO -->
    <!-- <div class="flex items-center justify-between text-[11px] font-mono font-bold text-ink/60 px-1">
      <a href="{{ route('landing') }}" class="hover:text-brand hover:underline flex items-center gap-1">&larr; Landing Page</a>
      <span class="text-[10px] px-1 bg-white brutal-border">v1.0</span>
    </div> -->
  </div>

</aside>
