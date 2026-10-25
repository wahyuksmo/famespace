<!-- MODAL: GOAL / CELENGAN (BUAT & EDIT TARGET TABUNGAN) -->
<div id="goal-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[60] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-lg w-full max-h-[92vh] overflow-y-auto p-4 sm:p-6 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div id="goal-modal-icon-badge" class="w-8 h-8 bg-brand text-white brutal-border flex items-center justify-center font-black text-sm">
          🎯
        </div>
        <div>
          <h3 id="goal-modal-title" class="font-black font-display text-lg sm:text-xl text-ink">Buat Target Tabungan</h3>
          <p class="text-[10px] font-mono text-ink/60">Tentukan mimpi atau rencana bersama keluarga</p>
        </div>
      </div>
      <button type="button" onclick="closeGoalModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="goal-modal-form" method="POST" action="{{ route('finances.goals.store') }}" class="space-y-3.5">
      @csrf
      <input type="hidden" name="_method" id="goal-modal-method" value="POST">
      <input type="hidden" id="goal-input-id" value="">

      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Nama Target / Celengan:
        </label>
        <input 
          type="text" 
          id="goal-input-title" 
          name="title" 
          required 
          placeholder="misal: Liburan ke Bali, Dana Darurat, Beli Kulkas Baru"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Target Nominal (Rp):
          </label>
          <input 
            type="text" 
            id="goal-input-target-amount" 
            name="target_amount" 
            required 
            placeholder="misal: Rp 10.000.000"
            oninput="handleCurrencyInput(this)"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
            autocomplete="off"
          >
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Saldo Awal Terkumpul (Rp):
          </label>
          <input 
            type="text" 
            id="goal-input-current-amount" 
            name="current_amount" 
            placeholder="misal: Rp 0"
            oninput="handleCurrencyInput(this)"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
            autocomplete="off"
          >
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Emoji / Ikon:
          </label>
          <input 
            type="text" 
            id="goal-input-icon" 
            name="icon" 
            value="🎯"
            placeholder="🎯"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
          >
          <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
            <button type="button" onclick="$('#goal-input-icon').val('🏖️')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🏖️</button>
            <button type="button" onclick="$('#goal-input-icon').val('🛡️')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🛡️</button>
            <button type="button" onclick="$('#goal-input-icon').val('🚗')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🚗</button>
            <button type="button" onclick="$('#goal-input-icon').val('🏡')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🏡</button>
            <button type="button" onclick="$('#goal-input-icon').val('🎓')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🎓</button>
            <button type="button" onclick="$('#goal-input-icon').val('💻')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">💻</button>
          </div>
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Target Tanggal (Opsional):
          </label>
          <div class="relative">
            <input 
              type="text" 
              id="goal-input-date" 
              name="target_date" 
              placeholder="Pilih target waktu..." 
              readonly 
              class="w-full px-3 py-2 pr-8 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none cursor-pointer focus:bg-white datepicker-input"
            >
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-xs">📅</span>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Catatan / Rencana Alokasi:
        </label>
        <input 
          type="text" 
          id="goal-input-note" 
          name="note" 
          placeholder="misal: Sisihkan Rp 500rb per bulan dari gaji ayah"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <div class="pt-2 flex items-center gap-3">
        <button type="button" onclick="closeGoalModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="goal-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan Target &rarr;
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: SETOR TABUNGAN KE CELENGAN -->
<div id="goal-deposit-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-md w-full p-4 sm:p-6 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-brand text-white brutal-border flex items-center justify-center font-black text-sm">
          💰
        </div>
        <div>
          <h3 class="font-black font-display text-lg text-ink">Setor ke Celengan</h3>
          <p id="deposit-goal-subtitle" class="text-[11px] font-mono font-bold text-brand truncate max-w-[220px]">Target Tabungan</p>
        </div>
      </div>
      <button type="button" onclick="closeGoalDepositModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="goal-deposit-form" method="POST" action="" class="space-y-3.5">
      @csrf
      <input type="hidden" id="deposit-goal-id" value="">

      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Nominal Setoran (Rp):
        </label>
        <input 
          type="text" 
          id="deposit-input-amount" 
          name="amount" 
          required 
          placeholder="misal: Rp 500.000"
          oninput="handleCurrencyInput(this)"
          class="w-full px-3 py-2 bg-cream brutal-border text-sm font-bold font-mono text-ink outline-none focus:bg-white"
          autocomplete="off"
        >
        <p class="text-[10px] text-ink/60 font-mono mt-1">Uang yang disetor mengurangi saldo kas keluarga sebagai tabungan (bukan pengeluaran).</p>
      </div>

      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Catatan Setoran (Opsional):
        </label>
        <input 
          type="text" 
          id="deposit-input-note" 
          name="note" 
          placeholder="misal: Alokasi gaji bulan ini"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <div class="pt-2 flex items-center gap-3">
        <button type="button" onclick="closeGoalDepositModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="deposit-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Konfirmasi Setor &rarr;
        </button>
      </div>
    </form>
  </div>
</div>
