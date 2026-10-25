<!-- MODAL: TIMELINE (TAMBAH / EDIT MOMEN & TRIP KELUARGA) -->
<div id="timeline-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[60] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-xl w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div id="timeline-modal-icon" class="w-7 h-7 bg-brand text-white brutal-border flex items-center justify-center font-black text-xs">
          🗺️
        </div>
        <h3 id="timeline-modal-title" class="font-black font-display text-xl text-ink">Abadikan Momen & Trip</h3>
      </div>
      <button type="button" onclick="closeTimelineModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="timeline-modal-form" method="POST" action="{{ route('timeline.store') }}" enctype="multipart/form-data" class="space-y-3.5">
      @csrf
      <input type="hidden" name="_method" id="timeline-modal-method" value="POST">

      <!-- JUDUL MOMEN -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-title">
          Judul Momen / Liburan:
        </label>
        <input 
          type="text" 
          id="timeline-input-title" 
          name="title" 
          required 
          placeholder="misal: Piknik Akhir Pekan Kebun Raya Bogor"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <!-- SEARCHABLE DROPDOWNS: PENGUNGGAH & KATEGORI TRIP -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- SEARCHABLE 1: PENGUNGGAH -->
        <div class="relative" id="combobox-assignee-root">
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Diabadikan Oleh:
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

        <!-- SEARCHABLE 2: KATEGORI TRIP -->
        <div class="relative" id="combobox-category-root">
          <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-black font-mono uppercase text-ink" id="mlabel-category">
              Kategori Liburan / Trip:
            </label>
            <button 
              type="button" 
              onclick="openTimelineCategoryManageModal()" 
              class="text-[10px] font-black text-brand hover:underline font-mono"
            >
              🏷️ Kelola Kategori
            </button>
          </div>
          <input type="hidden" id="minput-category" name="category_id" value="">
          <button 
            type="button" 
            id="btn-category" 
            onclick="toggleCombobox('category')" 
            class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white"
          >
            <span id="label-category" class="truncate">Pilih Kategori...</span>
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
                placeholder="Ketik cari / buat kategori..." 
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

      <!-- TANGGAL MOMEN -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Tanggal Kegiatan:
        </label>
        <div class="relative">
          <input 
            type="text" 
            id="timeline-input-date" 
            name="event_date" 
            value="{{ now()->format('Y-m-d') }}"
            placeholder="Pilih tanggal..." 
            readonly 
            class="w-full px-3 py-2 pr-9 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none cursor-pointer focus:bg-white datepicker-input"
          >
          <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-xs">📅</span>
        </div>
      </div>

      <!-- CERITA / CATATAN TRIP -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Cerita / Catatan Momen:
        </label>
        <textarea 
          id="timeline-input-story" 
          name="story" 
          rows="3" 
          placeholder="Tulis kenangan atau catatan belanja seru liburan di sini..."
          class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink outline-none focus:bg-white"
        ></textarea>
      </div>

      <!-- LOKASI & TITIK PETA (LEAFLET) -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Nama Lokasi / Tempat (Opsional):
        </label>
        <input 
          type="text" 
          id="timeline-input-location" 
          name="location_name" 
          placeholder="misal: Pantai Anyer, Ciwidey, atau Taman Safari Bogor"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
      </div>

      <!-- TITIK PETA LEAFLET (PICKER) -->
      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="block text-xs font-black font-mono uppercase text-ink">
            Titik Koordinat Peta (Leaflet):
          </label>
          <span id="map-picker-coords-text" class="text-[10px] font-mono text-ink/70">
            Belum ada titik dipilih
          </span>
        </div>

        <!-- SEARCH TEMPAT DI PETA PICKER -->
        <div class="relative mb-2">
          <div class="flex items-center gap-1.5">
            <div class="relative flex-1">
              <input 
                type="text" 
                id="picker-search-query" 
                placeholder="Cari tempat di peta (cth: Candi Borobudur, Kuta, Malioboro)..." 
                class="w-full pl-8 pr-7 py-1.5 bg-cream brutal-border text-xs font-bold text-ink outline-none focus:bg-white placeholder:text-ink/50"
                onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchPickerLocation(); }"
                autocomplete="off"
              >
              <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs pointer-events-none">🔍</span>
              <button 
                type="button" 
                id="btn-clear-picker-search" 
                onclick="clearPickerSearchInput()" 
                class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-xs font-black text-ink/50 hover:text-coral transition-colors"
                title="Hapus"
              >✕</button>
            </div>
            <button 
              type="button" 
              id="btn-search-picker" 
              onclick="searchPickerLocation()" 
              class="px-3 py-1.5 bg-accent hover:bg-brand hover:text-white brutal-border text-xs font-bold transition-colors whitespace-nowrap"
            >
              Cari di Peta
            </button>
          </div>
          <!-- HASIL SEARCH DROPDOWN -->
          <div id="picker-search-results" class="hidden absolute left-0 right-0 top-full mt-1 bg-white brutal-border shadow-brutal z-50 max-h-40 overflow-y-auto p-1 space-y-1"></div>
        </div>
        
        <div id="timeline-picker-map" class="w-full h-36 sm:h-44 brutal-border bg-stone-200 relative z-0"></div>
        <input type="hidden" id="timeline-input-lat" name="latitude" value="">
        <input type="hidden" id="timeline-input-lng" name="longitude" value="">

        <div class="flex flex-wrap items-center justify-between gap-1.5 mt-1.5 text-[11px]">
          <span class="text-[10px] text-ink/60 font-mono">💡 Klik peta untuk menaruh pin</span>
          <div class="flex flex-wrap items-center gap-1">
            <button type="button" onclick="setPickerLocation(-6.2088, 106.8456, 'Jakarta')" class="px-1.5 py-0.5 bg-cream brutal-border text-[10px] font-bold hover:bg-accent">Jakarta</button>
            <button type="button" onclick="setPickerLocation(-6.9175, 107.6191, 'Bandung')" class="px-1.5 py-0.5 bg-cream brutal-border text-[10px] font-bold hover:bg-accent">Bandung</button>
            <button type="button" onclick="setPickerLocation(-6.5971, 106.8060, 'Bogor')" class="px-1.5 py-0.5 bg-cream brutal-border text-[10px] font-bold hover:bg-accent">Bogor</button>
            <button type="button" onclick="setPickerLocation(-8.4095, 115.1889, 'Bali')" class="px-1.5 py-0.5 bg-cream brutal-border text-[10px] font-bold hover:bg-accent">Bali</button>
            <button type="button" onclick="clearPickerMarker()" class="px-1.5 py-0.5 bg-coral text-white brutal-border text-[10px] font-bold hover:bg-ink">✕ Hapus Pin</button>
          </div>
        </div>
      </div>

      <!-- FOTO KENANGAN (DROPZONE & PREVIEW) -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1" id="mlabel-upload">
          Foto Kenangan (Opsional):
        </label>
        <!-- Input File untuk Galeri/Dokumen -->
        <input type="file" id="minput-file" name="image" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
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
              <span>Buka Kamera</span>
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
          <p class="text-[10px] text-ink/60 font-mono font-medium">Atau seret foto ke sini (JPG, PNG, WebP)</p>
        </div>

        <!-- PREVIEW CARD -->
        <div id="upload-preview-card" class="hidden p-2.5 bg-cream brutal-border flex items-center justify-between gap-3">
          <div class="flex items-center gap-2.5 min-w-0">
            <img id="upload-preview-img" src="" alt="Preview" class="w-12 h-12 object-cover brutal-border flex-shrink-0 bg-white">
            <div class="min-w-0 text-left">
              <p id="upload-file-name" class="text-xs font-black text-ink truncate max-w-[200px] sm:max-w-[280px]">momen-liburan.jpg</p>
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
        <button type="button" onclick="closeTimelineModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="timeline-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan Kenangan &rarr;
        </button>
      </div>
    </form>

  </div>
</div>
