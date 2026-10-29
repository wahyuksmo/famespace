<!-- SIDEBAR KIRI TETAP (MODERN CLEAN NEOBRUTALISM) -->
<aside id="sidebar-aside" class="w-72 max-w-[85vw] bg-white border-r-2 border-y-0 border-l-0 border-ink h-[100dvh] max-h-[100dvh] fixed lg:sticky top-0 left-0 -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-out flex flex-col p-3.5 flex-shrink-0 z-50 shadow-2xl lg:shadow-none overflow-y-auto overscroll-y-contain custom-scrollbar touch-pan-y">
  
  <div class="space-y-3.5">
    <!-- LOGO & FAMILY BADGE -->
    <div class="pb-3 border-b-2 border-ink flex-shrink-0 space-y-2.5">
      <div class="flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
          <div class="w-9 h-9 bg-brand-soft border-2 border-ink rounded-lg p-1 flex items-center justify-center shadow-[2px_2px_0px_#121614] group-hover:rotate-6 transition-transform shrink-0">
            <img src="{{ asset('icons/new_logo.png') }}" alt="FamKoji" class="w-full h-full object-contain">
          </div>
          <div class="leading-none">
            <span class="font-display font-black text-lg tracking-tight text-ink">FamKoji<span class="text-brand">.</span></span>
            <span class="block text-[8px] font-mono font-bold text-ink/50 uppercase tracking-widest mt-0.5">Family OS</span>
          </div>
        </a>
        <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden w-8 h-8 bg-cream hover:bg-accent border-2 border-ink rounded-lg text-ink flex items-center justify-center text-xs font-bold transition-all shadow-[1.5px_1.5px_0px_#121614] active:scale-95" aria-label="Tutup Menu">
          ✕
        </button>
      </div>

      <!-- CURRENT FAMILY SPACE CARD -->
      <div class="p-2.5 bg-brand-soft border-2 border-ink rounded-xl space-y-2 shadow-[2px_2px_0px_#121614]">
        <div class="flex items-center justify-between text-[10px] font-mono font-black text-brand uppercase tracking-wider">
          <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-brand inline-block animate-pulse"></span>
            Ruang Aktif
          </span>
          <button type="button" 
                  onclick="copyInviteCode('{{ $family->invite_code }}')" 
                  class="px-2 py-0.5 bg-white hover:bg-accent border-2 border-ink rounded text-[9px] tracking-normal font-mono font-black text-ink cursor-pointer active:scale-95 transition-all flex items-center gap-1 shadow-[1px_1px_0px_#121614]" 
                  title="Klik untuk Salin Kode Undangan">
            <span>{{ $family->invite_code }}</span>
            <span id="sidebar-copy-icon">
              <svg class="w-3 h-3 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
              </svg>
            </span>
          </button>
        </div>
        <div>
          <div class="font-black text-xs text-ink truncate flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-brand stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
            </svg>
            <span class="truncate">{{ $family->name }}</span>
          </div>
          <div class="text-[10px] font-mono font-bold text-ink/60 mt-1.5 flex items-center justify-between">
            <div class="flex items-center gap-1.5 truncate">
              <span id="sidebar-members-summary">{{ $members->count() }} Anggota</span>
              <span>&bull;</span>
              <span class="{{ $isPro ? 'text-brand font-black' : 'text-ink/60' }}">{{ $isPro ? 'PRO 👑' : 'Free' }}</span>
            </div>
            <!-- POIN PENGGUNA -->
            <div class="px-2 py-0.5 bg-cream border border-ink rounded font-mono font-black text-[10px] text-ink flex items-center gap-1 shrink-0 shadow-[1px_1px_0px_#121614]">
              <svg class="w-3 h-3 text-brand fill-brand" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              <span id="sidebar-user-points-val">{{ number_format($user->points ?? 0) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- NAVIGATION MENU -->
    <nav class="space-y-3" id="sidebar-nav">
      <!-- SECTION 1: UTAMA -->
      <div>
        <div class="text-[9px] font-mono font-black uppercase tracking-wider text-ink/50 px-2 pb-1 flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-ink/40"></span>
          <span>Aktivitas Utama</span>
        </div>
        <div class="space-y-1">
          <!-- RINGKASAN -->
          <a href="{{ route('dashboard') }}" id="nav-overview" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/>
              </svg>
              <span class="font-bold">Ringkasan</span>
            </div>
          </a>

          <!-- TUGAS & CHORES -->
          <a href="{{ route('chores.index') }}" id="nav-chores" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('chores.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="font-bold">Tugas & Chores</span>
            </div>
            <span id="sidebar-chore-count" class="px-1.5 py-0.5 bg-coral text-white font-mono text-[9px] border border-ink rounded font-black shadow-[1px_1px_0px_#121614]">
              {{ $chores->where('status', 'pending')->count() }}
            </span>
          </a>

          <!-- KAS & TABUNGAN -->
          <a href="{{ route('finances.index') }}" id="nav-finance" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('finances.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0v1.5c0 .414.336.75.75.75h.75m0 0H21m-18 0v7.5c0 .414.336.75.75.75H3.75m0 0V18m0-7.5h16.5m0 0v7.5c0 .414-.336.75-.75.75H20.25m0 0H3.75m16.5-7.5V6a.75.75 0 00-.75-.75h-.75m0 0V4.5m0 0a60.07 60.07 0 00-15.797 2.101C2.973 6.8 2.25 7.34 2.25 8.094"/>
              </svg>
              <span class="font-bold">Kas & Tabungan</span>
            </div>
          </a>
        </div>
      </div>

      <!-- SECTION 2: RUMAH TANGGA & PRO -->
      <div>
        <div class="text-[9px] font-mono font-black uppercase tracking-wider text-ink/50 px-2 pb-1 flex items-center justify-between">
          <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
            <span>Rumah Tangga & Pro</span>
          </div>
          <span class="text-[8px] font-mono font-black text-brand bg-brand-soft px-1 border border-ink rounded">PRO</span>
        </div>
        <div class="space-y-1">
          <!-- STOK & PANTRY (PRO) -->
          @if($isPro)
            <a href="{{ route('pantry.index') }}" id="nav-pantry" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('pantry.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
                <span class="font-bold">Stok & Pantry</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="px-1.5 py-0.5 bg-brand text-white font-mono text-[9px] border border-ink rounded font-bold">PRO</span>
                <span class="px-1.5 py-0.5 bg-amber-200 text-ink font-mono text-[9px] border border-ink rounded font-bold">
                  {{ $family->pantryItems()->count() }}
                </span>
              </div>
            </a>
          @else
            <button type="button" onclick="openPaywallModal('pantry')" id="nav-pantry" class="w-full text-left px-2.5 py-2 rounded-lg nav-item flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
                <span class="font-bold">Stok & Pantry</span>
              </div>
              <span id="badge-nav-pantry" class="px-1.5 py-0.5 bg-coral text-white font-mono text-[9px] border border-ink rounded font-black shadow-[1px_1px_0px_#121614]">
                PRO
              </span>
            </button>
          @endif

          <!-- LINIMASA & TRIP (PRO) -->
          @if($isPro)
            <a href="{{ route('timeline.index') }}" id="nav-timeline" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('timeline.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934a1.12 1.12 0 01-1.006 0L9.503 3.313a1.12 1.12 0 00-1.006 0L3.623 5.75c-.382.191-.623.58-.623 1.006v11.723c0 .836.88 1.38 1.628 1.006l3.869-1.934a1.12 1.12 0 011.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                </svg>
                <span class="font-bold">Linimasa & Trip</span>
              </div>
              <span id="badge-nav-timeline" class="px-1.5 py-0.5 bg-brand text-white font-mono text-[9px] border border-ink rounded font-bold">
                PRO
              </span>
            </a>
          @else
            <button type="button" onclick="openPaywallModal('timeline')" id="nav-timeline" class="w-full text-left px-2.5 py-2 rounded-lg nav-item flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934a1.12 1.12 0 01-1.006 0L9.503 3.313a1.12 1.12 0 00-1.006 0L3.623 5.75c-.382.191-.623.58-.623 1.006v11.723c0 .836.88 1.38 1.628 1.006l3.869-1.934a1.12 1.12 0 011.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                </svg>
                <span class="font-bold">Linimasa & Trip</span>
              </div>
              <span id="badge-nav-timeline" class="px-1.5 py-0.5 bg-coral text-white font-mono text-[9px] border border-ink rounded font-black shadow-[1px_1px_0px_#121614]">
                PRO
              </span>
            </button>
          @endif

          <!-- KALENDER & AGENDA (PRO) -->
          @if($isPro)
            <a href="{{ route('events.index') }}" id="nav-calendar" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('events.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <span class="font-bold">Kalender & Agenda</span>
              </div>
              <span id="badge-nav-calendar" class="px-1.5 py-0.5 bg-brand text-white font-mono text-[9px] border border-ink rounded font-bold">
                PRO
              </span>
            </a>
          @else
            <button type="button" onclick="openPaywallModal('calendar')" id="nav-calendar" class="w-full text-left px-2.5 py-2 rounded-lg nav-item flex items-center justify-between text-xs uppercase tracking-wider">
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <span class="font-bold">Kalender & Agenda</span>
              </div>
              <span id="badge-nav-calendar" class="px-1.5 py-0.5 bg-coral text-white font-mono text-[9px] border border-ink rounded font-black shadow-[1px_1px_0px_#121614]">
                PRO
              </span>
            </button>
          @endif
        </div>
      </div>

      <!-- SECTION 3: RUANG KELUARGA -->
      <div>
        <div class="text-[9px] font-mono font-black uppercase tracking-wider text-ink/50 px-2 pb-1 flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-ink/40"></span>
          <span>Ruang Keluarga</span>
        </div>
        <div class="space-y-1">
          <!-- ANGGOTA KELUARGA -->
          <a href="{{ route('members.index') }}" id="nav-members" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('members.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
              </svg>
              <span class="font-bold">Anggota Keluarga</span>
            </div>
            <span id="sidebar-members-count" class="px-1.5 py-0.5 bg-cream text-ink font-mono text-[9px] border border-ink rounded font-black shadow-[1px_1px_0px_#121614]">
              {{ $members->count() }}
            </span>
          </a>

          <!-- ALBUM KELUARGA -->
          <a href="{{ route('albums.index') }}" id="nav-albums" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('albums.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
              </svg>
              <span class="font-bold">Album Keluarga</span>
            </div>
            <span class="px-1.5 py-0.5 bg-brand-soft text-brand font-mono text-[9px] border border-ink rounded font-black shadow-[1px_1px_0px_#121614]">
              {{ $family->albums()->count() }}
            </span>
          </a>

          <!-- CATATAN -->
          <a href="{{ route('notes.index') }}" id="nav-notes" class="w-full text-left px-2.5 py-2 rounded-lg {{ request()->routeIs('notes.*') ? 'nav-item-active' : 'nav-item' }} flex items-center justify-between text-xs uppercase tracking-wider">
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 stroke-[2] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
              </svg>
              <span class="font-bold">Catatan</span>
            </div>
            <span class="px-1.5 py-0.5 bg-accent text-ink font-mono text-[9px] border border-ink rounded font-black shadow-[1px_1px_0px_#121614]">
              {{ $family->notes()->count() }}
            </span>
          </a>
        </div>
      </div>
    </nav>
  </div>

  <!-- BOTTOM PROFILE CARD & LOGOUT -->
  <div class="mt-auto pt-3 border-t-2 border-ink space-y-2 flex-shrink-0">
    <!-- SUBSCRIPTION STATUS -->
    <div class="p-2 bg-cream border-2 border-ink rounded-xl space-y-1.5 shadow-[1.5px_1.5px_0px_#121614]">
      <div class="flex items-center justify-between">
        <span class="text-[9px] font-mono font-black uppercase text-ink/70">Paket Akun</span>
        <span id="plan-badge-sidebar" class="px-1.5 py-0.5 text-[8px] font-black font-mono border border-ink rounded shadow-[1px_1px_0px_#121614] {{ $isPro ? 'bg-accent text-ink' : 'bg-coral text-white' }}">
          {{ $isPro ? (($subscription && $subscription->plan && $subscription->plan->isLifetime()) ? 'LIFETIME 👑' : 'PRO 👑') : 'FREE' }}
        </span>
      </div>
      <div class="flex items-center justify-between text-[10px] font-bold">
        <span id="plan-desc-sidebar" class="text-ink/80 font-mono text-[9px]">
          @if($isPro)
            {{ ($subscription && $subscription->plan && $subscription->plan->isLifetime()) ? 'Aktif (Lifetime)' : 'Aktif (Family Pro)' }}
          @else
            Free Starter
          @endif
        </span>
        @if(!$isPro)
          <button type="button" onclick="openPaywallModal('Fitur Pro FamKoji')" class="px-2 py-0.5 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-[9px] rounded">
            Upgrade
          </button>
        @endif
      </div>
    </div>

    <!-- PROFILE CARD -->
    <div class="flex items-center justify-between p-2 bg-cream border-2 border-ink rounded-xl shadow-[1.5px_1.5px_0px_#121614]">
      <div onclick="openProfileModal()" class="flex items-center gap-2.5 min-w-0 cursor-pointer flex-1 group" title="Klik untuk edit profil & foto">
        <div id="sidebar-user-avatar" class="w-8 h-8 bg-brand text-white border-2 border-ink rounded-lg font-black text-xs flex items-center justify-center flex-shrink-0 overflow-hidden group-hover:ring-2 group-hover:ring-brand transition-all shadow-[1px_1px_0px_#121614]">
          @if($user->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
          @else
            <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
          @endif
        </div>
        <div class="min-w-0">
          <div id="sidebar-user-name" class="font-black text-xs text-ink leading-tight truncate group-hover:text-brand transition-colors" title="{{ $user->name }}">{{ $user->name }}</div>
          <div class="text-[9px] font-mono text-brand font-bold truncate flex items-center gap-1">
            <span>{{ $user->role ?? 'Kepala Keluarga' }}</span>
            <span class="text-[8px] text-ink/40 group-hover:text-brand">
              <svg class="w-2.5 h-2.5 inline stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
              </svg>
            </span>
          </div>
        </div>
      </div>
      <form action="{{ route('logout') }}" method="POST" class="inline flex-shrink-0 ml-1">
        @csrf
        <button type="submit" title="Keluar Akun" class="w-8 h-8 bg-white border-2 border-ink rounded-lg hover:bg-coral hover:text-white flex items-center justify-center text-xs font-bold transition-all shadow-[1px_1px_0px_#121614] active:scale-95">
          <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
          </svg>
        </button>
      </form>
    </div>
  </div>

</aside>
