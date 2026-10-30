<!-- MODAL: KELOLA KATEGORI & ANGGARAN (CLEAN & SIMPLE) -->
<div id="category-manage-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-lg w-full max-h-[92vh] overflow-y-auto p-5 sm:p-6 relative modal-box space-y-4">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
      <div class="flex items-center gap-2">
        <div id="category-modal-icon-badge" class="w-8 h-8 bg-accent text-ink brutal-border flex items-center justify-center font-black text-sm">
          🏷️
        </div>
        <div>
          <h3 id="category-modal-title" class="font-black font-display text-lg sm:text-xl text-ink">Tambah Kategori Baru</h3>
          <p id="category-modal-subtitle" class="text-[10px] font-mono text-ink/60">Atur kategori transaksi kas dan batas belanja bulanan</p>
        </div>
      </div>
      <button type="button" onclick="closeCategoryManageModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- FORM -->
    <form id="category-modal-form" method="POST" action="{{ route('finances.categories.store') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="_method" id="category-modal-method" value="POST">
      <input type="hidden" id="category-input-id" value="">

      <!-- NAMA KATEGORI -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Nama Kategori:
        </label>
        <input 
          type="text" 
          id="category-input-name" 
          name="name" 
          required 
          placeholder="misal: Belanja Dapur, Bensin Motor, Skincare"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <!-- JENIS KATEGORI & IKON -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- SEARCHABLE COMBOBOX: JENIS KATEGORI -->
        <div class="relative" id="combobox-cattype-root">
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Jenis Kategori:
          </label>
          <input type="hidden" id="category-input-type" name="type" value="expense">
          <button 
            type="button" 
            id="btn-cattype" 
            onclick="toggleCatTypeCombobox()" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
          >
            <span id="label-cattype" class="truncate">💸 Pengeluaran</span>
            <span class="text-[10px] ml-1.5 text-ink/70">▼</span>
          </button>

          <!-- POPOVER DROPDOWN -->
          <div 
            id="popover-cattype" 
            class="hidden absolute left-0 right-0 top-full mt-1 bg-cream brutal-border shadow-brutal z-50 p-2 space-y-1"
          >
            <button type="button" onclick="selectCatTypeOption('expense', '💸 Pengeluaran')" class="w-full text-left px-2.5 py-1.5 bg-accent text-ink brutal-border text-xs font-bold flex items-center justify-between transition-colors">
              <span>💸 Pengeluaran</span>
              <span class="text-xs cattype-check" data-type="expense">✓</span>
            </button>
            <button type="button" onclick="selectCatTypeOption('income', '💰 Pemasukan')" class="w-full text-left px-2.5 py-1.5 bg-white hover:bg-cream text-ink brutal-border text-xs font-bold flex items-center justify-between transition-colors">
              <span>💰 Pemasukan</span>
              <span class="text-xs cattype-check hidden" data-type="income">✓</span>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Emoji / Ikon:
          </label>
          <input 
            type="text" 
            id="category-input-icon" 
            name="icon" 
            placeholder="🏷️"
            value="🏷️"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
          >
        </div>
      </div>

      <!-- PRESET EMOJI CHIPS -->
      <div>
        <label class="block text-[10px] font-mono font-bold text-ink/60 uppercase mb-1">Pilihan Cepat Ikon:</label>
        <div class="flex items-center gap-1.5 flex-wrap">
          @foreach(['🛍️', '🛒', '⚡', '🚗', '🍜', '💊', '🎓', '🏫', '🐷', '🧾', '🎁', '💰', '🏷️', '🏖️', '☕', '👶'] as $ico)
            <button type="button" onclick="selectCategoryIcon('{{ $ico }}')" class="w-8 h-8 bg-cream hover:bg-accent brutal-border text-sm flex items-center justify-center active:translate-x-[1px] active:translate-y-[1px] transition-all">
              {{ $ico }}
            </button>
          @endforeach
        </div>
      </div>

      <!-- BATAS ANGGARAN BULANAN (KHUSUS PENGELUARAN) -->
      <div id="category-budget-wrapper">
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Batas Anggaran Bulanan (Rp):
        </label>
        <input 
          type="text" 
          id="category-input-budget" 
          name="monthly_budget" 
          placeholder="misal: Rp 2.500.000 (0 jika tanpa batas)"
          oninput="handleCurrencyInput(this)"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
          autocomplete="off"
        >
        <p class="text-[10px] text-ink/60 font-mono mt-1">Kosongkan atau isi 0 jika kategori ini tidak dibatasi kuota belanja bulanan.</p>
      </div>

      <!-- TOMBOL AKSI -->
      <div class="pt-2 flex items-center gap-3">
        <button type="button" onclick="closeCategoryManageModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="category-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan Kategori &rarr;
        </button>
      </div>
    </form>

  </div>
</div>
