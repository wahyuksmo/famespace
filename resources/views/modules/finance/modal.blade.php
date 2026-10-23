<!-- MODAL: FINANCE (TAMBAH / EDIT KAS & PENGELUARAN) -->
<div id="finance-modal" class="fixed inset-0 bg-ink/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-xl w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div id="finance-modal-icon" class="w-7 h-7 bg-brand text-white brutal-border flex items-center justify-center font-black text-xs">
          💸
        </div>
        <h3 id="finance-modal-title" class="font-black font-display text-xl text-ink">Catat Kas & Pengeluaran</h3>
      </div>
      <button type="button" onclick="closeFinanceModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- TYPE TOGGLE (PENGELUARAN VS PEMASUKAN) -->
    <div class="grid grid-cols-2 gap-2 mb-4 font-black text-xs">
      <button type="button" onclick="setFinanceType('expense')" id="btn-ftype-expense" class="py-2 bg-coral text-white brutal-border transition-all">
        💸 Pengeluaran
      </button>
      <button type="button" onclick="setFinanceType('income')" id="btn-ftype-income" class="py-2 bg-cream text-ink brutal-border transition-all">
        💰 Pemasukan
      </button>
    </div>

    <form id="finance-modal-form" method="POST" action="{{ route('finances.store') }}" enctype="multipart/form-data" class="space-y-3.5">
      @csrf
      <input type="hidden" name="_method" id="finance-modal-method" value="POST">
      <input type="hidden" name="type" id="finance-input-type" value="expense">

      <!-- KETERANGAN TRANSAKSI -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-title">
          Keterangan Pengeluaran:
        </label>
        <input 
          type="text" 
          id="finance-input-description" 
          name="description" 
          required 
          placeholder="misal: Belanja sayur mingguan pasar"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <!-- SEARCHABLE DROPDOWNS: ANGGOTA & KATEGORI -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- SEARCHABLE 1: ANGGOTA -->
        <div class="relative" id="combobox-assignee-root">
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Ditugaskan / Penginput:
          </label>
          <input type="hidden" id="minput-assignee" name="user_id" value="{{ Auth::id() }}">
          <button 
            type="button" 
            id="btn-assignee" 
            onclick="toggleCombobox('assignee')" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
          >
            <span id="label-assignee" class="truncate">👨 {{ Auth::user()->name }}</span>
            <span class="text-[10px] ml-1.5 text-ink/70">▼</span>
          </button>

          <!-- POPOVER DROPDOWN -->
          <div 
            id="popover-assignee" 
            class="hidden absolute left-0 right-0 top-full mt-1 bg-cream brutal-border shadow-brutal z-50 p-2 space-y-1.5"
          >
            <div class="relative">
              <input 
                type="text" 
                id="search-assignee" 
                placeholder="Ketik cari anggota..." 
                oninput="filterCombobox('assignee', this.value)" 
                class="w-full px-2.5 py-1.5 bg-white brutal-border text-xs font-bold text-ink outline-none"
                autocomplete="off"
              >
              <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs pointer-events-none">🔍</span>
            </div>
            <div id="list-assignee" class="max-h-36 overflow-y-auto space-y-1 pr-0.5">
              <!-- Dynamically populated -->
            </div>
          </div>
        </div>

        <!-- SEARCHABLE 2: KATEGORI -->
        <div class="relative" id="combobox-category-root">
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-category">
            Kategori / Area:
          </label>
          <input type="hidden" id="minput-category" name="category_id" value="">
          <button 
            type="button" 
            id="btn-category" 
            onclick="toggleCombobox('category')" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
          >
            <span id="label-category" class="truncate">Pilih kategori...</span>
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
              <!-- Dynamically populated -->
            </div>
          </div>
        </div>
      </div>

      <!-- NOMINAL/POIN & CUSTOM DATEPICKER -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-extra">
            Nominal Pengeluaran (Rp):
          </label>
          <input 
            type="text" 
            id="finance-input-amount" 
            name="amount" 
            required
            placeholder="misal: Rp 150.000"
            oninput="handleCurrencyInput(this)"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
            autocomplete="off"
          >
        </div>
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-date">
            Tanggal Transaksi:
          </label>
          <div class="relative">
            <input 
              type="text" 
              id="finance-input-date" 
              name="transaction_date" 
              value="{{ now()->format('Y-m-d') }}"
              placeholder="Pilih tanggal..." 
              readonly 
              class="w-full px-3 py-2 pr-9 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none cursor-pointer focus:bg-white datepicker-input"
            >
            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-xs">📅</span>
          </div>
        </div>
      </div>

      <!-- FILE UPLOAD WITH PREVIEW -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-upload">
          Foto Struk / Bukti Kas (Opsional):
        </label>
        <input type="file" id="minput-file" name="receipt" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
        <input type="hidden" name="remove_receipt" id="finance-input-remove-receipt" value="0">
        
        <!-- DROPZONE -->
        <label 
          for="minput-file"
          id="upload-dropzone" 
          class="block border-2 border-dashed border-ink bg-cream/70 hover:bg-accent/20 p-3 sm:p-4 text-center cursor-pointer transition-colors duration-150 group"
        >
          <div class="flex items-center justify-center gap-2.5 pointer-events-none">
            <span class="text-2xl group-hover:scale-110 transition-transform">📸</span>
            <div class="text-left">
              <p class="text-xs font-bold text-ink">
                <span class="underline decoration-2 decoration-brand">Pilih file</span> atau seret foto ke sini
              </p>
              <p class="text-[10px] text-ink/60 font-mono font-medium">JPG, PNG, WebP (Maks. 5 MB)</p>
            </div>
          </div>
        </label>

        <!-- PREVIEW CARD -->
        <div id="upload-preview-card" class="hidden p-2.5 bg-cream brutal-border flex items-center justify-between gap-3">
          <div class="flex items-center gap-2.5 min-w-0">
            <img id="upload-preview-img" src="" alt="Preview" onclick="if(this.src) showImagePreview(this.src, 'Pratinjau Struk')" class="w-12 h-12 object-cover brutal-border flex-shrink-0 bg-white cursor-pointer hover:opacity-80" title="Klik untuk memperbesar">
            <div class="min-w-0 text-left">
              <p id="upload-file-name" class="text-xs font-black text-ink truncate max-w-[200px] sm:max-w-[280px]">struk-belanja.jpg</p>
              <p id="upload-file-size" class="text-[10px] font-mono font-bold text-ink/60">0 KB</p>
            </div>
          </div>
          <button 
            type="button" 
            onclick="removeUploadedFile(event)" 
            class="px-2.5 py-1 bg-coral text-white brutal-border text-[11px] font-black hover:bg-ink transition-colors flex-shrink-0"
          >
            ✕ Hapus
          </button>
        </div>
      </div>

      <!-- ACTION BUTTONS -->
      <div class="pt-2 flex items-center gap-3">
        <button type="button" onclick="closeFinanceModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="finance-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan Transaksi &rarr;
        </button>
      </div>
    </form>

  </div>
</div>
