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
  $isAlbums = request()->routeIs('albums.*');
  $isNotes = request()->routeIs('notes.*');

  if ($isChores) {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openChoreModal === 'function') { openChoreModal(); }";
    $centerLabel = "Tambah Tugas";
  } elseif ($isNotes) {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openNoteModal === 'function') { openNoteModal(); }";
    $centerLabel = "Tempel Catatan";
  } elseif ($isFinances) {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openFinanceModal === 'function') { openFinanceModal(); }";
    $centerLabel = "Catat Kas";
  } elseif ($isAlbums) {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof window.triggerAlbumAction === 'function') { window.triggerAlbumAction(); }";
    $centerLabel = "Aksi Album";
  } elseif ($isEvents) {
    $centerAction = $isPro
      ? "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openCalendarModal === 'function') { openCalendarModal(); }"
      : "if(window.vibrateDevice) window.vibrateDevice(20); if(typeof openPaywallModal === 'function') { openPaywallModal('calendar'); } else { alert('Fitur Agenda khusus Famespace PRO!'); }";
    $centerLabel = "Agenda Baru";
  } elseif ($isTimeline) {
    $centerAction = $isPro
      ? "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openTimelineModal === 'function') { openTimelineModal(); }"
      : "if(window.vibrateDevice) window.vibrateDevice(20); if(typeof openPaywallModal === 'function') { openPaywallModal('timeline'); } else { alert('Fitur Linimasa khusus Famespace PRO!'); }";
    $centerLabel = "Momen Baru";
  } else {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); toggleMobileFabMenu();";
    $centerLabel = "Tambah Aksi";
  }
@endphp

<!-- BOTTOM NAVIGATION BAR (KHUSUS MOBILE / PWA) -->
<nav aria-label="Navigasi Bawah" class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-white/95 backdrop-blur-md brutal-border border-x-0 border-b-0 pb-safe select-none shadow-[0_-4px_20px_rgba(18,22,20,0.08)]">
  <div class="h-16 flex items-center justify-around px-2 max-w-lg mx-auto relative">
    
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

    <!-- TAB 3: TOMBOL TAMBAH ADAPTIF (FLOATING RAISED NOTCH DI TENGAH) -->
    <div class="flex-1 min-w-0 h-full flex items-center justify-center relative">
      <button type="button" 
              onclick="{{ $centerAction }}"
              class="-mt-6 group relative flex items-center justify-center p-1 bg-white brutal-border rounded-2xl shadow-[3px_3px_0px_#121614] hover:shadow-[4px_4px_0px_#121614] active:translate-x-[1.5px] active:translate-y-[1.5px] active:shadow-none transition-all duration-150 touch-press focus:outline-none z-10"
              title="{{ $centerLabel }}"
              aria-label="{{ $centerLabel }}">
        <div class="w-12 h-12 bg-brand rounded-xl flex items-center justify-center text-white brutal-border group-hover:bg-brand-light transition-all duration-250">
          <svg id="bottom-nav-plus-icon" class="w-7 h-7 stroke-[3] transition-transform duration-250 ease-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
        </div>
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

    <!-- TAB 5: ALBUM -->
    <a href="{{ route('albums.index') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(8);"
       class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isAlbums ? 'text-ink' : 'text-ink/60 hover:text-ink' }}">
      <div class="relative w-8 h-7 flex items-center justify-center rounded-lg transition-all duration-150 {{ $isAlbums ? 'bg-accent brutal-border border-2 shadow-[1.5px_1.5px_0px_#121614]' : 'border-2 border-transparent' }}">
        <span class="text-base leading-none">📸</span>
      </div>
      <span class="text-[10px] font-mono tracking-tight mt-1 truncate transition-all {{ $isAlbums ? 'font-black text-ink' : 'font-bold' }}">Album</span>
      @if($isAlbums)
        <span class="w-4 h-0.5 rounded-full bg-brand absolute bottom-1"></span>
      @endif
    </a>

  </div>
</nav>
