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
      : "if(window.vibrateDevice) window.vibrateDevice(20); if(typeof openPaywallModal === 'function') { openPaywallModal('calendar'); } else { alert('Fitur Agenda khusus FamKoji PRO!'); }";
    $centerLabel = "Agenda Baru";
  } elseif ($isTimeline) {
    $centerAction = $isPro
      ? "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openTimelineModal === 'function') { openTimelineModal(); }"
      : "if(window.vibrateDevice) window.vibrateDevice(20); if(typeof openPaywallModal === 'function') { openPaywallModal('timeline'); } else { alert('Fitur Linimasa khusus FamKoji PRO!'); }";
    $centerLabel = "Momen Baru";
  } else {
    $centerAction = "if(window.vibrateDevice) window.vibrateDevice(12); toggleMobileFabMenu();";
    $centerLabel = "Tambah Aksi";
  }
@endphp

<!-- BOTTOM NAVIGATION BAR (MODERN CLEAN NEOBRUTALISM) -->
<nav aria-label="Navigasi Bawah" class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-white/95 backdrop-blur-md border-t-2 border-ink pb-safe select-none shadow-[0_-3px_12px_rgba(18,22,20,0.06)]">
  <div class="h-14 flex items-center justify-around px-2 max-w-md mx-auto relative">
    
    <!-- TAB 1: BERANDA -->
    <a href="{{ route('dashboard') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(8);"
       class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isDashboard ? 'text-brand' : 'text-ink/60 hover:text-ink' }}">
      <div class="relative w-8 h-6 flex items-center justify-center rounded-md transition-all duration-150 {{ $isDashboard ? 'bg-accent/40 text-brand' : '' }}">
        <svg class="w-5 h-5 {{ $isDashboard ? 'stroke-[2.5] text-brand' : 'stroke-[2]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
      </div>
      <span class="text-[9.5px] font-mono tracking-tight mt-0.5 truncate transition-all {{ $isDashboard ? 'font-black text-brand' : 'font-bold' }}">Beranda</span>
      @if($isDashboard)
        <span class="w-3.5 h-0.5 rounded-full bg-brand absolute bottom-0.5"></span>
      @endif
    </a>

    <!-- TAB 2: TUGAS -->
    <a href="{{ route('chores.index') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(8);"
       class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isChores ? 'text-brand' : 'text-ink/60 hover:text-ink' }}">
      <div class="relative w-8 h-6 flex items-center justify-center rounded-md transition-all duration-150 {{ $isChores ? 'bg-accent/40 text-brand' : '' }}">
        <svg class="w-5 h-5 {{ $isChores ? 'stroke-[2.5] text-brand' : 'stroke-[2]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="mobile-bottom-nav-chore-count" 
              class="{{ ($pendingCount && $pendingCount > 0) ? '' : 'hidden' }} absolute -top-1 -right-2 min-w-[15px] h-[15px] px-0.5 bg-coral text-white text-[8px] font-mono font-black rounded-full border border-ink flex items-center justify-center shadow-[1px_1px_0px_#121614]">
          {{ $pendingCount ?? 0 }}
        </span>
      </div>
      <span class="text-[9.5px] font-mono tracking-tight mt-0.5 truncate transition-all {{ $isChores ? 'font-black text-brand' : 'font-bold' }}">Tugas</span>
      @if($isChores)
        <span class="w-3.5 h-0.5 rounded-full bg-brand absolute bottom-0.5"></span>
      @endif
    </a>

    <!-- TAB 3: TOMBOL TAMBAH ADAPTIF (SLIM NEOBRUTALIST CENTER BUTTON) -->
    <div class="flex-1 min-w-0 h-full flex items-center justify-center relative">
      <button type="button" 
              id="bottom-nav-center-btn"
              onclick="{{ $centerAction }}"
              class="-mt-4 group relative flex items-center justify-center w-11 h-11 bg-brand text-white border-2 border-ink rounded-xl shadow-[2.5px_2.5px_0px_#121614] hover:shadow-[3px_3px_0px_#121614] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all duration-150 touch-press focus:outline-none z-10"
              title="{{ $centerLabel }}"
              aria-label="{{ $centerLabel }}">
        <svg id="bottom-nav-plus-icon" class="w-5 h-5 stroke-[2.8] transition-transform duration-200 ease-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
      </button>
    </div>

    <!-- TAB 4: KAS -->
    <a href="{{ route('finances.index') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(8);"
       class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isFinances ? 'text-brand' : 'text-ink/60 hover:text-ink' }}">
      <div class="relative w-8 h-6 flex items-center justify-center rounded-md transition-all duration-150 {{ $isFinances ? 'bg-accent/40 text-brand' : '' }}">
        <svg class="w-5 h-5 {{ $isFinances ? 'stroke-[2.5] text-brand' : 'stroke-[2]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0v1.5c0 .414.336.75.75.75h.75m0 0H21m-18 0v7.5c0 .414.336.75.75.75H3.75m0 0V18m0-7.5h16.5m0 0v7.5c0 .414-.336.75-.75.75H20.25m0 0H3.75m16.5-7.5V6a.75.75 0 00-.75-.75h-.75m0 0V4.5m0 0a60.07 60.07 0 00-15.797 2.101C2.973 6.8 2.25 7.34 2.25 8.094" />
        </svg>
      </div>
      <span class="text-[9.5px] font-mono tracking-tight mt-0.5 truncate transition-all {{ $isFinances ? 'font-black text-brand' : 'font-bold' }}">Kas</span>
      @if($isFinances)
        <span class="w-3.5 h-0.5 rounded-full bg-brand absolute bottom-0.5"></span>
      @endif
    </a>

    <!-- TAB 5: ALBUM -->
    <a href="{{ route('albums.index') }}" 
       onclick="if(window.vibrateDevice) window.vibrateDevice(8);"
       class="flex flex-col items-center justify-center flex-1 min-w-0 h-full py-1 relative select-none active:scale-95 transition-transform duration-100 {{ $isAlbums ? 'text-brand' : 'text-ink/60 hover:text-ink' }}">
      <div class="relative w-8 h-6 flex items-center justify-center rounded-md transition-all duration-150 {{ $isAlbums ? 'bg-accent/40 text-brand' : '' }}">
        <svg class="w-5 h-5 {{ $isAlbums ? 'stroke-[2.5] text-brand' : 'stroke-[2]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
        </svg>
      </div>
      <span class="text-[9.5px] font-mono tracking-tight mt-0.5 truncate transition-all {{ $isAlbums ? 'font-black text-brand' : 'font-bold' }}">Album</span>
      @if($isAlbums)
        <span class="w-3.5 h-0.5 rounded-full bg-brand absolute bottom-0.5"></span>
      @endif
    </a>

  </div>
</nav>
