<!-- MODAL: CHORE (TAMBAH / EDIT TUGAS RUMAH TANGGA) -->
<div id="chore-modal" class="fixed inset-0 bg-ink/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-xl w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div id="chore-modal-icon" class="w-7 h-7 bg-brand text-white brutal-border flex items-center justify-center font-black text-xs">
          🧹
        </div>
        <h3 id="chore-modal-title" class="font-black font-display text-xl text-ink">Tambah Tugas Baru</h3>
      </div>
      <button type="button" onclick="closeChoreModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="chore-modal-form" method="POST" action="{{ route('chores.store') }}" enctype="multipart/form-data" class="space-y-3.5">
      @csrf
      <input type="hidden" name="_method" id="chore-modal-method" value="POST">

      <!-- JUDUL TUGAS -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-title">
          Nama Tugas Rumah Tangga:
        </label>
        <input 
          type="text" 
          id="chore-input-title" 
          name="title" 
          required 
          placeholder="misal: Cuci piring & rapikan meja makan"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <!-- SEARCHABLE DROPDOWNS: ANGGOTA & KATEGORI -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- SEARCHABLE 1: ANGGOTA -->
        <div class="relative" id="combobox-assignee-root">
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Ditugaskan Kepada:
          </label>
          <input type="hidden" id="minput-assignee" name="assigned_to_user_id" value="">
          <button 
            type="button" 
            id="btn-assignee" 
            onclick="toggleCombobox('assignee')" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
          >
            <span id="label-assignee" class="truncate">👥 Semua Anggota</span>
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
          <input type="hidden" id="minput-category" name="category" value="Dapur">
          <button 
            type="button" 
            id="btn-category" 
            onclick="toggleCombobox('category')" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
          >
            <span id="label-category" class="truncate">🍳 Dapur</span>
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

      <!-- POIN HADIAH & BATAS WAKTU -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Poin Hadiah (Reward):
          </label>
          <input 
            type="number" 
            id="chore-input-points" 
            name="points" 
            value="10" 
            min="1" 
            max="500"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
          >
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Batas Waktu (Due Date):
          </label>
          <div class="relative">
            <input 
              type="text" 
              id="chore-input-date" 
              name="due_date" 
              placeholder="Pilih tanggal batas waktu..." 
              readonly 
              class="w-full px-3 py-2 pr-9 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none cursor-pointer focus:bg-white datepicker-input"
            >
            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-xs">📅</span>
          </div>
        </div>
      </div>

      <!-- KETERANGAN / CATATAN -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Catatan Tambahan (Opsional):
        </label>
        <textarea 
          id="chore-input-desc" 
          name="description" 
          rows="2" 
          placeholder="misal: Pastikan gunakan spons bersih dan bilas sampai tuntas."
          class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink outline-none focus:bg-white"
        ></textarea>
      </div>

      <!-- FILE UPLOAD WITH PREVIEW -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-upload">
          Foto Panduan / Bukti Tugas (Opsional):
        </label>
        <input type="file" id="minput-file" name="attachment" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
        <input type="hidden" name="remove_attachment" id="chore-input-remove-attachment" value="0">
        
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
            <img id="upload-preview-img" src="" alt="Preview" onclick="if(this.src) showImagePreview(this.src, 'Foto Tugas')" class="w-12 h-12 object-cover brutal-border flex-shrink-0 bg-white cursor-pointer hover:opacity-80" title="Klik untuk memperbesar">
            <div class="min-w-0 text-left">
              <p id="upload-file-name" class="text-xs font-black text-ink truncate max-w-[200px] sm:max-w-[280px]">foto-tugas.jpg</p>
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
        <button type="button" onclick="closeChoreModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="chore-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan Tugas &rarr;
        </button>
      </div>
    </form>

  </div>
</div>
