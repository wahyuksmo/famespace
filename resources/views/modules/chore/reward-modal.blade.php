<!-- MODAL: REWARD (TAMBAH / EDIT HADIAH) -->
<div id="reward-manage-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[60] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-lg w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div id="reward-manage-modal-icon" class="w-8 h-8 bg-brand text-white brutal-border flex items-center justify-center font-black text-sm">
          🎁
        </div>
        <h3 id="reward-manage-modal-title" class="font-black font-display text-xl text-ink">Tambah Hadiah Baru</h3>
      </div>
      <button type="button" onclick="closeRewardModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="reward-manage-modal-form" method="POST" action="{{ route('rewards.store') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="_method" id="reward-manage-modal-method" value="POST">
      <input type="hidden" name="id" id="reward-manage-modal-id" value="">

      <!-- PILIHAN IKON EMOJI -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1.5">
          Ikon Hadiah:
        </label>
        <input type="hidden" name="icon" id="reward-input-icon" value="🎁">
        <div class="flex flex-wrap gap-2" id="reward-emoji-picker">
          @foreach(['🎁', '🍦', '🎮', '🍕', '🎬', '🚴', '💵', '⚽', '🧸', '🎨', '📚', '🏖️', '🍩', '🎟️', '⭐'] as $emoji)
            <button 
              type="button" 
              onclick="selectRewardEmoji('{{ $emoji }}')" 
              data-emoji="{{ $emoji }}"
              class="emoji-select-btn w-9 h-9 bg-cream brutal-border flex items-center justify-center text-lg hover:bg-brand-soft active:scale-95 transition-all {{ $emoji === '🎁' ? 'bg-accent ring-2 ring-ink' : '' }}"
            >
              {{ $emoji }}
            </button>
          @endforeach
        </div>
      </div>

      <!-- JUDUL HADIAH -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Nama Hadiah:
        </label>
        <input 
          type="text" 
          id="reward-input-title" 
          name="title" 
          required 
          placeholder="misal: Main Game 1 Jam, Beli Es Krim, dll."
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- POIN YANG DIBUTUHKAN -->
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Biaya Poin:
          </label>
          <div class="relative">
            <span class="absolute left-3 top-2 text-xs">⭐</span>
            <input 
              type="number" 
              id="reward-input-points" 
              name="points" 
              required 
              min="1" 
              value="20"
              placeholder="20"
              class="w-full pl-8 pr-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-mono font-black text-ink outline-none focus:bg-white"
            >
          </div>
        </div>

        <!-- STOK / KUOTA (OPSIONAL) -->
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Stok / Kuota <span class="text-ink/50 normal-case font-normal">(opsional)</span>:
          </label>
          <input 
            type="number" 
            id="reward-input-stock" 
            name="stock" 
            min="0" 
            placeholder="Kosongkan jika tak terbatas"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-mono font-bold text-ink outline-none focus:bg-white"
          >
        </div>
      </div>

      <!-- DESKRIPSI -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Keterangan / Aturan Main:
        </label>
        <textarea 
          id="reward-input-description" 
          name="description" 
          rows="2" 
          placeholder="misal: Hanya boleh ditukar saat akhir pekan setelah PR selesai."
          class="w-full px-3 py-2 bg-cream brutal-border text-xs font-medium text-ink outline-none focus:bg-white resize-none"
        ></textarea>
      </div>

      <!-- TOMBOL AKSI -->
      <div class="pt-3 border-t-2 border-ink flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeRewardModal()" 
          class="px-4 py-2 bg-cream hover:bg-coral hover:text-white brutal-border text-xs font-black uppercase transition-colors"
        >
          Batal
        </button>
        <button 
          type="submit" 
          id="btn-submit-reward" 
          class="px-5 py-2 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider"
        >
          Simpan Hadiah
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: KONFIRMASI KLAIM REWARD -->
<div id="reward-claim-confirm-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-md w-full p-5 sm:p-6 relative modal-box text-center">
    <div id="claim-confirm-icon" class="w-16 h-16 mx-auto bg-brand-soft brutal-border flex items-center justify-center text-3xl shadow-[3px_3px_0px_#121614] mb-3">
      🎁
    </div>
    
    <h3 class="font-black font-display text-xl text-ink">Klaim Hadiah Ini?</h3>
    <p id="claim-confirm-title" class="font-bold text-sm text-ink mt-1">Nama Hadiah</p>
    <p id="claim-confirm-desc" class="text-xs text-ink/70 mt-1 mb-4">Deskripsi</p>

    <!-- POIN SUMMARY CARD -->
    <div class="bg-cream brutal-border p-3.5 mb-5 space-y-2 text-xs font-mono font-bold text-left">
      <div class="flex justify-between items-center text-ink/70">
        <span>⭐ Poin Kamu Saat Ini:</span>
        <span id="claim-current-points">{{ number_format(auth()->user()->points ?? 0) }} Poin</span>
      </div>
      <div class="flex justify-between items-center text-coral font-black">
        <span>🏷️ Biaya Hadiah:</span>
        <span id="claim-cost-points">- 0 Poin</span>
      </div>
      <div class="border-t border-ink/20 pt-2 flex justify-between items-center text-brand font-black text-sm">
        <span>✨ Sisa Poin Nanti:</span>
        <span id="claim-remaining-points">0 Poin</span>
      </div>
    </div>

    <div class="flex items-center justify-center gap-3">
      <button 
        type="button" 
        onclick="closeClaimConfirmModal()" 
        class="px-4 py-2 bg-cream hover:bg-ink hover:text-white brutal-border text-xs font-black uppercase transition-colors"
      >
        Batal
      </button>
      <button 
        type="button" 
        id="btn-execute-claim" 
        onclick="executeClaim()" 
        class="px-5 py-2 bg-accent text-ink brutal-btn text-xs font-black uppercase tracking-wider"
      >
        Ya, Tukar Poin! 🚀
      </button>
    </div>
  </div>
</div>

<!-- MODAL: TOLAK KLAIM DENGAN ALASAN (UNTUK ORANG TUA) -->
<div id="reward-reject-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-md w-full p-5 sm:p-6 relative modal-box">
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-3">
      <div class="flex items-center gap-2">
        <span class="text-xl">⚠️</span>
        <h3 class="font-black font-display text-lg text-ink">Tolak Klaim Hadiah</h3>
      </div>
      <button type="button" onclick="closeRejectModal()" class="w-7 h-7 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center text-xs">
        ✕
      </button>
    </div>

    <p class="text-xs text-ink/70 mb-3 font-medium">
      Jika ditolak, poin yang terpotong dan kuota stok hadiah akan <strong>otomatis dikembalikan</strong> ke anggota keluarga.
    </p>

    <div class="space-y-3">
      <input type="hidden" id="reject-claim-id" value="">
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Alasan / Catatan Penolakan (Opsional):
        </label>
        <textarea 
          id="reject-claim-notes" 
          rows="2" 
          placeholder="misal: PR sekolah belum selesai, rapikan kamar dulu ya."
          class="w-full px-3 py-2 bg-cream brutal-border text-xs font-medium text-ink outline-none focus:bg-white resize-none"
        ></textarea>
      </div>

      <div class="pt-2 flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeRejectModal()" 
          class="px-3.5 py-1.5 bg-cream hover:bg-ink hover:text-white brutal-border text-xs font-black uppercase"
        >
          Batal
        </button>
        <button 
          type="button" 
          id="btn-confirm-reject" 
          onclick="executeRejectClaim()" 
          class="px-4 py-1.5 bg-coral text-white brutal-btn text-xs font-black uppercase tracking-wider"
        >
          Tolak & Kembalikan Poin
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: KONFIRMASI PERSETUJUAN KLAIM HADIAH (ORANG TUA) -->
<div id="reward-approve-confirm-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closeApproveModal()">
  <div class="bg-white brutal-card max-w-md w-full p-5 sm:p-6 relative modal-box text-center">
    <div class="w-14 h-14 mx-auto bg-brand-soft brutal-border flex items-center justify-center text-3xl shadow-[3px_3px_0px_#121614] mb-3">
      🎉
    </div>
    
    <h3 class="font-black font-display text-xl text-ink">Serahkan Hadiah?</h3>
    <p class="text-xs text-ink/70 mt-1 mb-3">
      Pastikan Anda sudah menyiapkan atau menyerahkan hadiah fisik kepada anggota keluarga.
    </p>

    <!-- DETAIL KLAIM -->
    <div class="bg-cream brutal-border p-3 mb-4 space-y-1.5 text-xs font-mono text-left">
      <div class="flex justify-between items-center text-ink">
        <span class="text-ink/60">🎁 Hadiah:</span>
        <span id="approve-confirm-reward-title" class="font-black text-right truncate ml-2">Hadiah</span>
      </div>
      <div class="flex justify-between items-center text-ink">
        <span class="text-ink/60">👤 Penerima:</span>
        <span id="approve-confirm-user-name" class="font-bold">Anggota</span>
      </div>
      <div class="flex justify-between items-center text-brand font-black">
        <span class="text-ink/60">⭐ Poin Ditukar:</span>
        <span id="approve-confirm-points">0 Poin</span>
      </div>
    </div>

    <input type="hidden" id="approve-claim-id" value="">

    <div class="flex items-center justify-center gap-2.5">
      <button 
        type="button" 
        onclick="closeApproveModal()" 
        class="w-1/2 py-2 bg-cream hover:bg-ink hover:text-white brutal-border text-xs font-mono font-black uppercase transition-colors"
      >
        Batal
      </button>
      <button 
        type="button" 
        id="btn-confirm-approve" 
        onclick="executeApproveClaim()" 
        class="w-1/2 py-2 bg-brand text-white brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-1.5"
      >
        <span>✓ Ya, Serahkan</span>
      </button>
    </div>
  </div>
</div>
