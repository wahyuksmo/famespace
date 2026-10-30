<!-- MODAL: ANGGARAN BULANAN HISTORIS -->
<div id="period-budget-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[60] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-lg w-full max-h-[92vh] overflow-y-auto p-4 sm:p-6 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-accent text-ink brutal-border flex items-center justify-center font-black text-sm">
          📅
        </div>
        <div>
          <h3 class="font-black font-display text-lg sm:text-xl text-ink">Anggaran Bulanan Khusus</h3>
          <p class="text-[10px] font-mono text-ink/60">Tentukan nominal anggaran khusus untuk bulan kalender tertentu</p>
        </div>
      </div>
      <button type="button" onclick="closePeriodBudgetModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="period-budget-form" method="POST" action="{{ route('finances.period_budgets.store') }}" class="space-y-3.5">
      @csrf

      <!-- SEARCHABLE COMBOBOX 1: KATEGORI PENGELUARAN -->
      <div class="relative" id="combobox-pbudget-cat-root">
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Kategori Pengeluaran:
        </label>
        <input type="hidden" id="pbudget-input-category" name="category_id" value="" required>
        <button 
          type="button" 
          id="btn-pbudget-cat" 
          onclick="togglePbudgetCatCombobox()" 
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
        >
          <span id="label-pbudget-cat" class="truncate">Pilih Kategori...</span>
          <span class="text-[10px] ml-1.5 text-ink/70">▼</span>
        </button>

        <!-- POPOVER DROPDOWN -->
        <div 
          id="popover-pbudget-cat" 
          class="hidden absolute left-0 right-0 top-full mt-1 bg-cream brutal-border shadow-brutal z-50 p-2 space-y-1.5"
        >
          <div class="relative">
            <input 
              type="text" 
              id="search-pbudget-cat" 
              placeholder="Cari kategori..." 
              oninput="filterPbudgetCat(this.value)" 
              class="w-full px-2.5 py-1.5 bg-white brutal-border text-xs font-bold text-ink outline-none"
              autocomplete="off"
            >
            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs pointer-events-none">🔍</span>
          </div>
          <div id="list-pbudget-cat" class="max-h-40 overflow-y-auto space-y-1 pr-0.5">
            <!-- Dynamically populated -->
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- SEARCHABLE COMBOBOX 2: PERIODE BULAN -->
        <div class="relative" id="combobox-pbudget-period-root">
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Periode Bulan:
          </label>
          <input type="hidden" id="pbudget-input-period" name="period" value="{{ $selectedPeriod ?? now()->format('Y-m') }}" required>
          <button 
            type="button" 
            id="btn-pbudget-period" 
            onclick="togglePbudgetPeriodCombobox()" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
          >
            <span id="label-pbudget-period" class="truncate font-mono">{{ $selectedPeriodLabel ?? now()->locale('id')->isoFormat('MMMM Y') }}</span>
            <span class="text-[10px] ml-1.5 text-ink/70">▼</span>
          </button>

          <!-- POPOVER DROPDOWN -->
          <div 
            id="popover-pbudget-period" 
            class="hidden absolute left-0 right-0 top-full mt-1 bg-cream brutal-border shadow-brutal z-50 p-2 space-y-1.5"
          >
            <div class="relative">
              <input 
                type="text" 
                id="search-pbudget-period" 
                placeholder="Cari periode..." 
                oninput="filterPbudgetPeriod(this.value)" 
                class="w-full px-2.5 py-1.5 bg-white brutal-border text-xs font-bold text-ink outline-none"
                autocomplete="off"
              >
              <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs pointer-events-none">🔍</span>
            </div>
            <div id="list-pbudget-period" class="max-h-40 overflow-y-auto space-y-1 pr-0.5">
              <!-- Dynamically populated -->
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Nominal Anggaran Khusus (Rp):
          </label>
          <input 
            type="text" 
            id="pbudget-input-amount" 
            name="budget_amount" 
            required 
            placeholder="misal: Rp 3.000.000"
            oninput="handleCurrencyInput(this)"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
            autocomplete="off"
          >
        </div>
      </div>

      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Catatan / Alasan Khusus (Opsional):
        </label>
        <input 
          type="text" 
          id="pbudget-input-notes" 
          name="notes" 
          placeholder="misal: Tambahan belanja Ramadhan, Liburan sekolah anak"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
        <p class="text-[10px] text-ink/60 font-mono mt-1">
          Anggaran ini akan berlaku khusus untuk periode bulan yang dipilih tanpa mengubah anggaran standar kategori.
        </p>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <button type="button" onclick="closePeriodBudgetModal()" class="flex-1 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button 
          type="button" 
          id="pbudget-btn-delete" 
          onclick="deleteCurrentPeriodBudget()" 
          class="hidden px-3.5 py-2.5 bg-coral hover:bg-ink text-white brutal-btn text-xs font-black uppercase transition-colors shadow-brutal-sm"
          title="Hapus batasan anggaran untuk kategori ini"
        >
          🗑️ Hapus
        </button>
        <button type="submit" id="pbudget-btn-submit" class="flex-1 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan &rarr;
        </button>
      </div>
    </form>
  </div>
</div>
