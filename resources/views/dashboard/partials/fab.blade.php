<!-- SPEED DIAL OVERLAY BACKDROP -->
<div id="fab-backdrop" 
     onclick="toggleMobileFabMenu(false)" 
     class="fixed inset-0 bg-ink/60 backdrop-blur-[3px] z-40 hidden transition-opacity duration-200 lg:hidden">
</div>

<!-- BOTTOM SHEET (SHORTCUT INPUT DATA CEPAT - DILENGKAPI GESTURE SWIPE/PULL DOWN) -->
<div id="fab-speed-dial-menu" 
     class="fixed inset-x-0 bottom-0 z-50 flex flex-col items-center hidden lg:hidden select-none pb-24 pt-2 px-3 bg-cream brutal-border border-x-0 border-b-0 shadow-[0_-8px_30px_rgba(18,22,20,0.25)] rounded-t-2xl transform transition-transform duration-200 ease-out touch-pan-y"
     style="will-change: transform;">
  
  <!-- DRAG / PULL HANDLE INDICATOR -->
  <div id="fab-sheet-handle" class="w-full flex flex-col items-center py-2 cursor-grab active:cursor-grabbing touch-none select-none">
    <div class="w-14 h-1.5 bg-ink/30 rounded-full"></div>
    <span class="text-[9px] font-mono font-bold text-ink/40 tracking-wider uppercase mt-1">Tarik ke bawah untuk menutup</span>
  </div>

  <!-- SHEET HEADER CHIP & CLOSE BUTTON -->
  <div class="w-full max-w-sm flex items-center justify-between px-1 mb-2">
    <div class="px-2.5 py-1 bg-ink text-white brutal-border text-[10px] font-mono font-black uppercase tracking-widest shadow-[2px_2px_0px_#34D399] flex items-center gap-1.5">
      <span class="text-xs">⚡</span>
      <span>Pilih Aksi Cepat</span>
    </div>
    <button type="button" 
            onclick="toggleMobileFabMenu(false)" 
            class="w-6 h-6 bg-white brutal-border text-ink flex items-center justify-center text-xs font-black hover:bg-coral hover:text-white transition-colors"
            aria-label="Tutup Menu">
      ✕
    </button>
  </div>

  <!-- ACTION ITEMS CONTAINER -->
  <div class="w-full max-w-sm flex flex-col gap-2">
    <!-- Action 1: Catat Tugas -->
    <button type="button" 
            onclick="triggerQuickAction('chore')" 
            class="w-full flex items-center justify-between p-2.5 bg-white brutal-card group touch-press active:scale-[0.98] transition-transform">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 bg-brand text-white brutal-border rounded-xl flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
          🧹
        </div>
        <div class="text-left min-w-0">
          <div class="text-xs font-mono font-black text-ink">Tugas Baru</div>
          <div class="text-[10px] font-mono font-semibold text-ink/60 truncate">Tugaskan anggota keluarga</div>
        </div>
      </div>
      <span class="text-xs font-black font-mono text-ink">➔</span>
    </button>

    <!-- Action 2: Catat Kas -->
    <button type="button" 
            onclick="triggerQuickAction('finance')" 
            class="w-full flex items-center justify-between p-2.5 bg-white brutal-card group touch-press active:scale-[0.98] transition-transform" 
            style="animation-delay: 40ms;">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 bg-accent text-ink brutal-border rounded-xl flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
          💰
        </div>
        <div class="text-left min-w-0">
          <div class="text-xs font-mono font-black text-ink">Catat Kas</div>
          <div class="text-[10px] font-mono font-semibold text-ink/60 truncate">Pemasukan & pengeluaran</div>
        </div>
      </div>
      <span class="text-xs font-black font-mono text-ink">➔</span>
    </button>

    <!-- Action 3: Agenda Jadwal -->
    <button type="button" 
            onclick="triggerQuickAction('event')" 
            class="w-full flex items-center justify-between p-2.5 bg-white brutal-card group touch-press active:scale-[0.98] transition-transform" 
            style="animation-delay: 80ms;">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 {{ $isPro ? 'bg-sky text-ink' : 'bg-cream text-ink' }} brutal-border rounded-xl flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
          📅
        </div>
        <div class="text-left min-w-0">
          <div class="text-xs font-mono font-black text-ink flex items-center gap-1">
            <span>Agenda Jadwal</span>
            @if(!$isPro)
              <span class="text-[8px] px-1 bg-accent brutal-border font-mono font-black">PRO 👑</span>
            @endif
          </div>
          <div class="text-[10px] font-mono font-semibold text-ink/60 truncate">Acara & kegiatan bersama</div>
        </div>
      </div>
      <span class="text-xs font-black font-mono text-ink">➔</span>
    </button>

    <!-- Action 4: Momen / Trip (Linimasa) -->
    <button type="button" 
            onclick="triggerQuickAction('timeline')" 
            class="w-full flex items-center justify-between p-2.5 bg-white brutal-card group touch-press active:scale-[0.98] transition-transform" 
            style="animation-delay: 120ms;">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 {{ $isPro ? 'bg-coral text-white' : 'bg-cream text-ink' }} brutal-border rounded-xl flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
          🗺️
        </div>
        <div class="text-left min-w-0">
          <div class="text-xs font-mono font-black text-ink flex items-center gap-1">
            <span>Momen & Trip</span>
            @if(!$isPro)
              <span class="text-[8px] px-1 bg-accent brutal-border font-mono font-black">PRO 👑</span>
            @endif
          </div>
          <div class="text-[10px] font-mono font-semibold text-ink/60 truncate">Foto kenangan & perjalanan</div>
        </div>
      </div>
      <span class="text-xs font-black font-mono text-ink">➔</span>
    </button>

    <!-- Action 5: Album Keluarga -->
    <a href="{{ route('albums.index') }}" 
       class="w-full flex items-center justify-between p-2.5 bg-white brutal-card group touch-press active:scale-[0.98] transition-transform">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 bg-brand-soft text-ink brutal-border rounded-xl flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
          📸
        </div>
        <div class="text-left min-w-0">
          <div class="text-xs font-mono font-black text-ink">Album Keluarga</div>
          <div class="text-[10px] font-mono font-semibold text-ink/60 truncate">Koleksi foto & video memori</div>
        </div>
      </div>
      <span class="text-xs font-black font-mono text-ink">➔</span>
    </a>

    <!-- Action 6: Catatan Kulkas -->
    <a href="{{ route('notes.index') }}?action=create" 
       class="w-full flex items-center justify-between p-2.5 bg-white brutal-card group touch-press active:scale-[0.98] transition-transform">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 bg-[#FFF9A6] text-ink brutal-border rounded-xl flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
          📌
        </div>
        <div class="text-left min-w-0">
          <div class="text-xs font-mono font-black text-ink">Catatan Kulkas</div>
          <div class="text-[10px] font-mono font-semibold text-ink/60 truncate">Pesan & info penting keluarga</div>
        </div>
      </div>
      <span class="text-xs font-black font-mono text-ink">➔</span>
    </a>
  </div>

</div>
