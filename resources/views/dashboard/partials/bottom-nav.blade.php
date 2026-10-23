@php
  $currentRoute = request()->route() ? request()->route()->getName() : '';
  $user = auth()->user();
  $family = $family ?? ($user ? $user->family : null);
  $isPro = $isPro ?? ($family ? $family->isPro() : false);

  if (isset($chores) && is_object($chores) && method_exists($chores, 'where')) {
    $pendingCount = $chores->where('status', 'pending')->count();
  } elseif ($user && $user->family_id) {
    $pendingCount = \App\Models\Chore::where('family_id', $user->family_id)->where('status', 'pending')->count();
  } else {
    $pendingCount = 0;
  }

  $isDashboard = request()->routeIs('dashboard') || $currentRoute === '';
  $isChores = request()->routeIs('chores.*');
  $isFinances = request()->routeIs('finances.*');
  $isEvents = request()->routeIs('events.*');
  $isTimeline = request()->routeIs('timeline.*');
  $isMembers = request()->routeIs('members.*');
  $isMenu = $isEvents || $isTimeline || $isMembers;

  if ($isChores) {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openChoreModal === 'function') { openChoreModal(); }";
    $centerLabel = "Tambah";
  } elseif ($isFinances) {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openFinanceModal === 'function') { openFinanceModal(); }";
    $centerLabel = "Catat";
  } elseif ($isEvents) {
    $centerAction = $isPro
      ? "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openCalendarModal === 'function') { openCalendarModal(); }"
      : "if(window.vibrateDevice) window.vibrateDevice(20); if(typeof openPaywallModal === 'function') { openPaywallModal('calendar'); } else { alert('Fitur Agenda khusus Famespace PRO!'); }";
    $centerLabel = "Agenda";
  } elseif ($isTimeline) {
    $centerAction = $isPro
      ? "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openTimelineModal === 'function') { openTimelineModal(); }"
      : "if(window.vibrateDevice) window.vibrateDevice(20); if(typeof openPaywallModal === 'function') { openPaywallModal('timeline'); } else { alert('Fitur Linimasa khusus Famespace PRO!'); }";
    $centerLabel = "Momen";
  } else {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); toggleMobileFabMenu();";
    $centerLabel = "Tambah";
  }
@endphp

<!-- BOTTOM NAVIGATION BAR (KHUSUS MOBILE / PWA) -->
<nav aria-label="Navigasi Bawah" class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-white/95 backdrop-blur-md brutal-border border-x-0 border-b-0 pb-safe select-none shadow-[0_-4px_20px_rgba(18,22,20,0.08)]">
  <div class="h-16 flex items-center justify-around px-1 max-w-lg mx-auto relative">
    
    <!-- TAB 1: BERANDA -->
    <a href="{{ route('dashboard') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(8);"
       class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isDashboard ? 'text-ink' : 'text-ink/60 hover:text-ink' }}">
      <div class="relative w-8 h-7 flex items-center justify-center rounded-lg transition-all duration-150 {{ $isDashboard ? 'bg-accent brutal-border border-2 shadow-[1.5px_1.5px_0px_#121614]' : 'border-2 border-transparent' }}">
        <span class="text-base leading-none">📊</span>
      </div>
      <span class="text-[10px] font-mono tracking-tight mt-1 truncate transition-all {{ $isDashboard ? 'font-black text-ink' : 'font-bold' }}">Beranda</span>
      @if($isDashboard)
        <span class="w-4 h-0.5 rounded-full bg-brand absolute bottom-1"></span>
      @endif
    </a>

    <!-- TAB 2: TUGAS -->
    <a href="{{ route('chores.index') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(8);"
       class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isChores ? 'text-ink' : 'text-ink/60 hover:text-ink' }}">
      <div class="relative w-8 h-7 flex items-center justify-center rounded-lg transition-all duration-150 {{ $isChores ? 'bg-accent brutal-border border-2 shadow-[1.5px_1.5px_0px_#121614]' : 'border-2 border-transparent' }}">
        <span class="text-base leading-none">🧹</span>
        <span id="mobile-bottom-nav-chore-count" 
              class="{{ ($pendingCount && $pendingCount > 0) ? '' : 'hidden' }} absolute -top-1.5 -right-2 min-w-[16px] h-[16px] px-1 bg-coral text-white text-[9px] font-mono font-black rounded-full brutal-border flex items-center justify-center shadow-[1px_1px_0px_#121614]">
          {{ $pendingCount ?? 0 }}
        </span>
      </div>
      <span class="text-[10px] font-mono tracking-tight mt-1 truncate transition-all {{ $isChores ? 'font-black text-ink' : 'font-bold' }}">Tugas</span>
      @if($isChores)
        <span class="w-4 h-0.5 rounded-full bg-brand absolute bottom-1"></span>
      @endif
    </a>

    <!-- TAB 3: TOMBOL TAMBAH ADAPTIF (HERO DI TENGAH) -->
    <div class="flex-1 min-w-0 h-full flex flex-col items-center justify-center relative">
      <button type="button" 
              onclick="{{ $centerAction }}"
              class="group -mt-5 flex flex-col items-center select-none active:scale-95 transition-transform duration-150 touch-press focus:outline-none"
              aria-label="Tambah Data">
        <div class="w-12 h-12 rounded-2xl brutal-border flex items-center justify-center transition-all duration-200 bg-brand text-white shadow-[2.5px_2.5px_0px_#121614] hover:bg-brand-light active:shadow-none">
          <svg id="bottom-nav-plus-icon" class="w-6 h-6 stroke-[3] transition-transform duration-250 ease-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
        </div>
        <span class="text-[10px] font-mono tracking-tight mt-0.5 font-black text-brand truncate">
          {{ $centerLabel }}
        </span>
      </button>
    </div>

    <!-- TAB 4: KAS -->
    <a href="{{ route('finances.index') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(8);"
       class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isFinances ? 'text-ink' : 'text-ink/60 hover:text-ink' }}">
      <div class="relative w-8 h-7 flex items-center justify-center rounded-lg transition-all duration-150 {{ $isFinances ? 'bg-accent brutal-border border-2 shadow-[1.5px_1.5px_0px_#121614]' : 'border-2 border-transparent' }}">
        <span class="text-base leading-none">💰</span>
      </div>
      <span class="text-[10px] font-mono tracking-tight mt-1 truncate transition-all {{ $isFinances ? 'font-black text-ink' : 'font-bold' }}">Kas</span>
      @if($isFinances)
        <span class="w-4 h-0.5 rounded-full bg-brand absolute bottom-1"></span>
      @endif
    </a>

    <!-- TAB 5: MENU -->
    <button type="button" 
            onclick="if(window.vibrateDevice) window.vibrateDevice(8); toggleMobileSidebar();" 
            class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isMenu ? 'text-ink' : 'text-ink/60 hover:text-ink' }}"
            aria-label="Buka Menu">
      <div class="relative w-8 h-7 flex items-center justify-center rounded-lg transition-all duration-150 {{ $isMenu ? 'bg-accent brutal-border border-2 shadow-[1.5px_1.5px_0px_#121614]' : 'border-2 border-transparent' }}">
        <svg class="w-5 h-5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
        </svg>
      </div>
      <span class="text-[10px] font-mono tracking-tight mt-1 truncate transition-all {{ $isMenu ? 'font-black text-ink' : 'font-bold' }}">Menu</span>
      @if($isMenu)
        <span class="w-4 h-0.5 rounded-full bg-brand absolute bottom-1"></span>
      @endif
    </button>

  </div>
</nav>
