<!-- TOP HEADER (DESKTOP) -->
<header class="hidden lg:flex bg-white brutal-border border-t-0 border-x-0 sticky top-0 z-20 px-4 sm:px-6 lg:px-8 py-3 sm:py-3.5 items-center justify-between gap-3">
  
  <!-- LEFT: MOBILE TOGGLE & GREETING -->
  <div class="flex items-center gap-3 min-w-0">
    <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden w-9 h-9 flex-shrink-0 bg-cream hover:bg-accent brutal-border text-ink flex items-center justify-center transition-all hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] shadow-brutal-sm" aria-label="Buka Menu">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    
    <div class="min-w-0">
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-base sm:text-xl lg:text-2xl font-black font-display text-ink truncate">
          Halo, {{ $user->name }}! 🌿
        </h1>
        <span class="hidden sm:inline-flex items-center gap-1.5 px-2 py-0.5 bg-accent brutal-border text-[10px] font-black uppercase font-mono shadow-[1.5px_1.5px_0px_#121614] flex-shrink-0">
          <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse"></span>
          <span>Hari ini harmonis</span>
        </span>
      </div>
      <div class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-ink/70 truncate mt-0.5">
        <span class="flex items-center gap-1 flex-shrink-0">
          <span>📅</span>
          <span>{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
        </span>
        <span class="text-ink/30 font-bold">&bull;</span>
        <span class="flex items-center gap-1 font-bold text-ink truncate">
          <span>🏡</span>
          <span class="truncate">Rumah Tangga {{ $family->name }}</span>
        </span>
      </div>
    </div>
  </div>

  <!-- RIGHT: FAMILY AVATARS, POINTS & QUICK STATS -->
  <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
    
    <!-- USER POINTS PILL -->
    <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1.5 bg-cream brutal-border shadow-brutal-sm cursor-default" title="Total Poin Anda: {{ $user->points ?? 0 }} Poin">
      <span class="text-sm">⭐</span>
      <div class="flex items-baseline gap-1">
        <span class="font-mono font-black text-xs text-ink">{{ number_format($user->points ?? 0) }}</span>
        <span class="text-[9px] font-mono font-bold text-ink/60 uppercase">Poin</span>
      </div>
    </div>

    <!-- FAMILY INVITE CODE QUICK COPY -->
    <button type="button" onclick="navigator.clipboard.writeText('{{ $family->invite_code }}'); if(typeof showToast==='function') showToast('Kode undangan {{ $family->invite_code }} berhasil disalin!');" title="Klik untuk salin kode undangan keluarga" class="hidden lg:flex items-center gap-1.5 px-2.5 py-1.5 bg-white hover:bg-cream brutal-border shadow-brutal-sm transition-all hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px]">
      <span class="text-[10px] font-mono font-black uppercase text-ink/50 tracking-wider">Kode:</span>
      <span class="text-xs font-mono font-black text-ink">{{ $family->invite_code }}</span>
      <svg class="w-3.5 h-3.5 text-ink/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
    </button>

    @if($isPro)
      <!-- PRO BADGE -->
      <span class="hidden sm:inline-flex items-center gap-1 px-2 py-1 bg-accent text-ink brutal-border shadow-brutal-sm font-mono font-black text-[10px] uppercase tracking-wider" title="Langganan Famespace Pro Aktif">
        <span>👑</span>
        <span>PRO</span>
      </span>
    @endif

    <!-- FAMILY MEMBERS GROUP (CLICKABLE TO MEMBERS PAGE) -->
    <a href="{{ route('members.index') }}" title="Kelola Anggota Keluarga ({{ $members->count() }} orang)" class="flex items-center gap-2 p-1 sm:p-1.5 bg-cream hover:bg-accent/30 brutal-border transition-all hover:translate-x-[-1px] hover:translate-y-[-1px] shadow-brutal-sm group">
      <div class="flex items-center -space-x-2 pl-0.5">
        @foreach($members->take(4) as $m)
          @php
            $bg = match($m->role) {
              'Ayah' => 'bg-brand text-white',
              'Ibu' => 'bg-accent text-ink',
              'Anak' => 'bg-sky text-ink',
              default => 'bg-coral text-white'
            };
            $emoji = match($m->role) {
              'Ayah' => '👨',
              'Ibu' => '👩',
              'Anak' => '👦',
              default => '👧'
            };
          @endphp
          <div title="{{ $m->name }} ({{ $m->role }})" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $bg }} brutal-border flex items-center justify-center text-xs font-black ring-1 ring-white">
            {{ $emoji }}
          </div>
        @endforeach

        @if($members->count() > 4)
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-ink text-white brutal-border flex items-center justify-center text-[10px] font-mono font-bold">
            +{{ $members->count() - 4 }}
          </div>
        @endif
      </div>

      <div class="hidden sm:flex flex-col text-left pr-1.5 leading-tight">
        <span class="text-[10px] font-mono font-black text-ink uppercase tracking-wider">{{ $members->count() }} Anggota</span>
        <span class="text-[9px] font-bold text-ink/60 group-hover:text-ink">Kelola &rarr;</span>
      </div>
    </a>

  </div>
</header>
