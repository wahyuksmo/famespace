<!-- MODAL: CALENDAR (TAMBAH / EDIT AGENDA & EVENT KELUARGA) -->
<div id="calendar-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[60] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-xl w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div id="calendar-modal-icon" class="w-7 h-7 bg-brand text-white brutal-border flex items-center justify-center font-black text-xs">
          📅
        </div>
        <h3 id="calendar-modal-title" class="font-black font-display text-xl text-ink">Tambah Agenda Baru</h3>
      </div>
      <button type="button" onclick="closeCalendarModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="calendar-modal-form" method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="space-y-3.5">
      @csrf
      <input type="hidden" name="_method" id="calendar-modal-method" value="POST">

      <!-- JUDUL AGENDA -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-title">
          Nama Agenda / Kegiatan:
        </label>
        <input 
          type="text" 
          id="calendar-input-title" 
          name="title" 
          required 
          placeholder="misal: Rapat Wali Murid SD & Posyandu Balita"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <!-- SEARCHABLE DROPDOWNS: PIC & KATEGORI -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- SEARCHABLE 1: PIC / ANGGOTA -->
        <div class="relative" id="combobox-assignee-root">
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            PIC / Yang Terlibat:
          </label>
          <input type="hidden" id="minput-assignee" name="pic" value="{{ Auth::id() }}">
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
            Kategori Agenda:
          </label>
          <input type="hidden" id="minput-category" name="category" value="Keluarga Inti">
          <button 
            type="button" 
            id="btn-category" 
            onclick="toggleCombobox('category')" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
          >
            <span id="label-category" class="truncate">🏠 Keluarga Inti</span>
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

      <!-- TANGGAL & WAKTU + LOKASI -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Waktu Pelaksanaan:
          </label>
          <div class="relative">
            <input 
              type="text" 
              id="calendar-input-time" 
              name="start_time" 
              required
              placeholder="Pilih waktu..." 
              readonly 
              class="w-full px-3 py-2 pr-9 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none cursor-pointer focus:bg-white datetimepicker-input"
            >
            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-xs">⏰</span>
          </div>
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Lokasi Kegiatan:
          </label>
          <input 
            type="text" 
            id="calendar-input-location" 
            name="location" 
            placeholder="misal: Sekolah / Balai RW"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
          >
        </div>
      </div>

      <!-- DESKRIPSI -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Keterangan Tambahan (Opsional):
        </label>
        <textarea 
          id="calendar-input-desc" 
          name="description" 
          rows="2.5" 
          placeholder="misal: Bawa raport lama dan buku vaksin anak."
          class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink outline-none focus:bg-white"
        ></textarea>
      </div>

      <!-- FILE UPLOAD WITH PREVIEW -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-upload">
          Undangan / Dokumen Agenda (Opsional):
        </label>
        <!-- Input File untuk Galeri/Dokumen -->
        <input type="file" id="minput-file" name="attachment" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
        <!-- Input File khusus Kamera Langsung di Mobile -->
        <input type="file" id="minput-camera" accept="image/*" capture="environment" class="hidden" onchange="handleFileSelect(event)">
        
        <!-- DROPZONE / PILIHAN SUMBER FOTO -->
        <div 
          id="upload-dropzone" 
          class="border-2 border-dashed border-ink bg-cream/70 p-3 sm:p-4 text-center transition-colors duration-150"
        >
          <div class="flex items-center justify-center gap-2 mb-2.5">
            <button 
              type="button" 
              onclick="document.getElementById('minput-camera').click()" 
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent hover:bg-accent/80 text-ink brutal-border text-xs font-black shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] cursor-pointer transition-all"
            >
              <span>📸</span>
              <span>Foto Dokumen (Kamera)</span>
            </button>
            <button 
              type="button" 
              onclick="document.getElementById('minput-file').click()" 
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-cream text-ink brutal-border text-xs font-black shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] cursor-pointer transition-all"
            >
              <span>🖼️</span>
              <span>Galeri / File</span>
            </button>
          </div>
          <p class="text-[10px] text-ink/60 font-mono font-medium">Atau seret dokumen ke sini (JPG, PNG, WebP)</p>
        </div>

        <!-- PREVIEW CARD -->
        <div id="upload-preview-card" class="hidden p-2.5 bg-cream brutal-border flex items-center justify-between gap-3">
          <div class="flex items-center gap-2.5 min-w-0">
            <img id="upload-preview-img" src="" alt="Preview" class="w-12 h-12 object-cover brutal-border flex-shrink-0 bg-white">
            <div class="min-w-0 text-left">
              <p id="upload-file-name" class="text-xs font-black text-ink truncate max-w-[200px] sm:max-w-[280px]">undangan.jpg</p>
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
        <button type="button" onclick="closeCalendarModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="calendar-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan Agenda &rarr;
        </button>
      </div>
    </form>

  </div>
</div>
