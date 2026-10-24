<!-- MODAL: PANTRY (CATAT / EDIT BARANG INVENTARIS) -->
<div id="pantry-modal" class="fixed inset-0 bg-ink/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-xl w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div id="pantry-modal-icon" class="w-7 h-7 bg-brand text-white brutal-border flex items-center justify-center font-black text-xs">
          🥫
        </div>
        <h3 id="pantry-modal-title" class="font-black font-display text-xl text-ink">Catat Barang ke Pantry</h3>
      </div>
      <button type="button" onclick="closePantryModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="pantry-form" action="{{ route('pantry.store') }}" method="POST" class="space-y-3.5">
      @csrf
      <input type="hidden" name="_method" id="form-method" value="POST">

      <!-- NAMA BARANG -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Nama Barang / Produk *
        </label>
        <input 
          type="text" 
          name="name" 
          id="pantry-name" 
          required 
          placeholder="Contoh: Beras Ramos, Susu UHT Cokelat, Paracetamol" 
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <!-- KATEGORI COMBOBOX (SEARCHABLE DROPDOWN STANDAR FAMESPACE) -->
      <div class="relative" id="combobox-category-root">
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Kategori / Lokasi Penyimpanan *
        </label>
        <input type="hidden" id="minput-category" name="category" value="dapur">
        <button 
          type="button" 
          id="btn-category" 
          onclick="toggleCombobox('category')" 
          class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
        >
          <span id="label-category" class="truncate">🍳 Dapur & Sembako Pokok</span>
          <span class="text-[10px] ml-1.5 text-ink/70">▼</span>
        </button>

        <!-- POPOVER DROPDOWN -->
        <div 
          id="popover-category" 
          class="hidden absolute left-0 right-0 top-full mt-1 bg-cream brutal-border shadow-brutal z-50 p-2 space-y-1.5"
        >
          <div class="relative">
            <input 
              type="text" 
              id="search-category" 
              placeholder="Ketik cari kategori..." 
              oninput="filterCombobox('category', this.value)" 
              class="w-full px-2.5 py-1.5 bg-white brutal-border text-xs font-bold text-ink outline-none"
              autocomplete="off"
            >
            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs pointer-events-none">🔍</span>
          </div>
          <div id="list-category" class="max-h-36 overflow-y-auto space-y-1 pr-0.5">
            <!-- Dynamically populated via comboboxData.category -->
          </div>
        </div>
      </div>

      <!-- KUANTITAS & SATUAN -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Jumlah / Stok *
          </label>
          <input 
            type="number" 
            name="quantity" 
            id="pantry-quantity" 
            step="0.1" 
            min="0" 
            required 
            value="1" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
          >
        </div>
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Satuan *
          </label>
          <input 
            type="text" 
            name="unit" 
            id="pantry-unit" 
            required 
            placeholder="pcs, kg, liter, botol, strip" 
            value="pcs" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
          >
        </div>
      </div>

      <!-- BATAS MINIMUM & EXPIRY (FLATPICKR STANDAR FAMESPACE) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Batas Stok Minimum *
          </label>
          <input 
            type="number" 
            name="min_quantity" 
            id="pantry-min-quantity" 
            step="0.1" 
            min="0" 
            required 
            value="1" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white" 
            title="Jika stok mencapai atau di bawah angka ini, status berubah jadi Stok Menipis"
          >
        </div>
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Tanggal Kedaluwarsa (Expired):
          </label>
          <div class="relative">
            <input 
              type="text" 
              name="expired_at" 
              id="pantry-expired-at" 
              placeholder="Pilih tanggal kedaluwarsa..." 
              readonly 
              class="w-full px-3 py-2 pr-9 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none cursor-pointer focus:bg-white datepicker-input"
            >
            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-xs">📅</span>
          </div>
        </div>
      </div>

      <!-- CATATAN TAMBAHAN -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Catatan Tambahan (Opsional):
        </label>
        <textarea 
          name="notes" 
          id="pantry-notes" 
          rows="2" 
          placeholder="Contoh: Disimpan di rak bawah dapur, merk favorit keluarga" 
          class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink outline-none focus:bg-white"
        ></textarea>
      </div>

      <!-- BUTTONS -->
      <div class="flex items-center justify-end gap-2 pt-3 border-t-2 border-ink">
        <button type="button" onclick="closePantryModal()" class="px-4 py-2 bg-cream hover:bg-ink/10 brutal-border font-mono font-bold text-xs text-ink transition-colors">
          Batal
        </button>
        <button type="submit" id="pantry-btn-submit" class="px-4 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider shadow-[2px_2px_0px_#121614] active:scale-95 transition-all">
          Simpan Barang &rarr;
        </button>
      </div>
    </form>

  </div>
</div>
