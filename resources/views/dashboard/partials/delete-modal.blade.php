<!-- MODAL: KONFIRMASI HAPUS (NEO-BRUTALIST) -->
<div id="confirm-delete-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closeDeleteModal()">
  <div class="bg-cream brutal-card max-w-sm sm:max-w-md w-full p-5 sm:p-6 relative modal-box space-y-4">
    
    <!-- HEADER -->
    <div class="flex items-start justify-between pb-3 border-b-2 border-ink">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-coral text-white brutal-border flex items-center justify-center font-black text-lg shadow-[2px_2px_0px_#121614]">
          🗑️
        </div>
        <div>
          <span class="text-[10px] font-black font-mono uppercase text-coral tracking-widest block">Tindakan Permanen</span>
          <h3 class="font-black font-display text-lg sm:text-xl text-ink leading-tight">Konfirmasi Hapus</h3>
        </div>
      </div>
      <button type="button" onclick="closeDeleteModal()" class="w-8 h-8 bg-white hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- CONTENT BODY -->
    <div class="space-y-3">
      <p class="text-xs sm:text-sm text-ink font-medium leading-relaxed">
        Apakah Anda yakin ingin menghapus <span id="confirm-delete-label" class="font-black text-ink bg-accent/60 px-1.5 py-0.5 brutal-border inline-block break-all">data ini</span>?
      </p>

      <div class="p-3 bg-white brutal-border border-l-4 border-l-coral flex items-start gap-2.5">
        <span class="text-base flex-shrink-0">⚠️</span>
        <div class="text-[11px] font-mono font-bold text-ink/80 leading-snug">
          Data yang sudah dihapus tidak dapat dipulihkan kembali.
        </div>
      </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="pt-2 flex items-center gap-3">
      <button type="button" onclick="closeDeleteModal()" class="w-1/2 py-2.5 bg-white hover:bg-cream brutal-btn text-xs font-mono font-black uppercase tracking-wider text-ink transition-all">
        Batal
      </button>
      <button type="button" id="confirm-delete-btn" class="w-1/2 py-2.5 bg-coral hover:bg-coral/90 text-white brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all">
        <span>Ya, Hapus</span>
      </button>
    </div>

  </div>
</div>
