@extends('layouts.main')

@section('title', $album->title . ' — Album Keluarga — ' . ($family->name ?? 'Famespace'))

@section('content')
<div class="space-y-6">

  <!-- TOP ALBUM BAR & ACTIONS -->
  <div class="p-4 sm:p-6 bg-white brutal-card space-y-4">
    
    <!-- NAVIGATION & ACTION BUTTONS -->
    <div class="flex flex-wrap items-center justify-between gap-3">
      <a href="{{ route('albums.index') }}" 
         class="px-3 py-1.5 bg-cream hover:bg-accent text-ink brutal-border font-mono font-bold text-xs flex items-center gap-1.5 shadow-[1.5px_1.5px_0px_#121614] active:scale-95 transition-all">
        <span>←</span>
        <span>Daftar Album</span>
      </a>

      <div class="flex items-center gap-2">
        @if($user->isParent() || $album->user_id === $user->id)
          <button type="button" 
                  onclick="openEditAlbumModal({{ $album->id }}, @js($album->title), @js($album->description))" 
                  class="px-3 py-1.5 bg-white hover:bg-cream text-ink brutal-border font-mono font-bold text-xs flex items-center gap-1">
            <span>✏️</span>
            <span class="hidden sm:inline">Edit Album</span>
          </button>
        @endif

        <button type="button" 
                onclick="handleUploadClick()" 
                class="px-4 py-1.5 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider flex items-center gap-1.5 shadow-[2px_2px_0px_#121614] active:scale-95 transition-all">
          <span>+</span>
          <span>Unggah Media</span>
        </button>
      </div>
    </div>

    <!-- ALBUM INFO & SUMMARY -->
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between border-t-2 border-ink pt-4">
      <div class="space-y-1.5 max-w-2xl">
        <div class="flex items-center gap-2 flex-wrap">
          <h1 class="text-xl sm:text-2xl font-black font-display text-ink leading-tight">
            {{ $album->title }}
          </h1>
          <span class="px-2 py-0.5 bg-brand-soft text-brand brutal-border font-mono font-black text-[10px]">
            {{ $album->media->count() }} Berkas
          </span>
        </div>
        @if($album->description)
          <p class="text-xs sm:text-sm font-mono text-ink/75 leading-relaxed">
            {{ $album->description }}
          </p>
        @endif
        <div class="text-[10px] font-mono text-ink/60 flex items-center gap-2 pt-0.5">
          <span>Dibuat oleh <strong class="text-ink">{{ $album->user->name ?? 'Anggota' }}</strong></span>
          <span>&bull;</span>
          <span>{{ $album->created_at->translatedFormat('d M Y') }}</span>
        </div>
      </div>

      <!-- KUOTA STATUS PILL -->
      <div class="p-3 bg-cream brutal-border space-y-1 self-stretch md:self-auto min-w-[220px]">
        <div class="flex items-center justify-between text-[10px] font-mono font-black uppercase text-ink/70">
          <span>Status Kuota Keluarga</span>
          <span class="{{ $quota['is_pro'] ? 'text-brand' : 'text-coral' }}">
            {{ $quota['is_pro'] ? 'PRO 👑' : 'FREE' }}
          </span>
        </div>
        <div class="flex items-center justify-between text-xs font-mono font-bold pt-0.5">
          <span class="flex items-center gap-1">
            <span>🖼️ Foto:</span>
            <span class="{{ (!$quota['is_pro'] && $quota['photos_used'] >= 10) ? 'text-coral font-black' : '' }}">
              {{ $quota['photos_used'] }}/{{ $quota['is_pro'] ? '∞' : $quota['photos_limit'] }}
            </span>
          </span>
          <span class="text-ink/30">|</span>
          <span class="flex items-center gap-1">
            <span>🎥 Video:</span>
            <span class="{{ (!$quota['is_pro'] && $quota['videos_used'] >= 1) ? 'text-coral font-black' : '' }}">
              {{ $quota['videos_used'] }}/{{ $quota['is_pro'] ? '∞' : $quota['videos_limit'] }}
            </span>
          </span>
        </div>
      </div>
    </div>

    <!-- FILTER TABS -->
    <div class="flex items-center gap-2 border-t-2 border-ink pt-3 overflow-x-auto custom-scrollbar">
      <a href="{{ route('albums.show', [$album, 'type' => 'all']) }}" 
         class="px-3 py-1 text-xs font-mono font-black uppercase tracking-wider {{ $typeFilter === 'all' ? 'bg-ink text-white brutal-border' : 'bg-cream text-ink hover:bg-accent brutal-border' }} transition-colors">
        Semua ({{ $album->media->count() }})
      </a>
      <a href="{{ route('albums.show', [$album, 'type' => 'photo']) }}" 
         class="px-3 py-1 text-xs font-mono font-black uppercase tracking-wider {{ $typeFilter === 'photo' ? 'bg-ink text-white brutal-border' : 'bg-cream text-ink hover:bg-accent brutal-border' }} transition-colors">
        🖼️ Foto ({{ $album->photos->count() }})
      </a>
      <a href="{{ route('albums.show', [$album, 'type' => 'video']) }}" 
         class="px-3 py-1 text-xs font-mono font-black uppercase tracking-wider {{ $typeFilter === 'video' ? 'bg-ink text-white brutal-border' : 'bg-cream text-ink hover:bg-accent brutal-border' }} transition-colors">
        🎥 Video ({{ $album->videos->count() }})
      </a>
    </div>

  </div>

  <!-- MEDIA GALLERY GRID -->
  @if($media->isEmpty())
    <div class="p-8 sm:p-12 bg-white brutal-card text-center space-y-4">
      <div class="w-16 h-16 mx-auto bg-brand-soft brutal-border flex items-center justify-center text-3xl shadow-[3px_3px_0px_#121614]">
        📷
      </div>
      <div class="space-y-1">
        <h3 class="text-base sm:text-lg font-mono font-black text-ink">Album Masih Kosong</h3>
        <p class="text-xs sm:text-sm font-mono text-ink/60 max-w-sm mx-auto">
          Belum ada media yang diunggah ke album ini. Unggah foto atau video kenangan Anda sekarang.
        </p>
      </div>
      <button type="button" 
              onclick="handleUploadClick()" 
              class="px-4 py-2.5 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider shadow-[2px_2px_0px_#121614]">
        + Unggah Foto / Video
      </button>
    </div>
  @else
    <!-- RESPONSIVE GRID (PWA OPTIMIZED: 2 COLS ON MOBILE, 3-4 ON DESKTOP) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
      @foreach($media as $item)
        <div class="bg-white brutal-card overflow-hidden flex flex-col group relative">
          
          <!-- MEDIA THUMBNAIL / PREVIEW -->
          <div class="relative aspect-square bg-cream overflow-hidden cursor-pointer select-none border-b-2 border-ink" 
               onclick="openLightbox({{ $loop->index }})">
            @if($item->isPhoto())
              <img src="{{ $item->file_url }}" 
                   alt="{{ $item->caption ?? $item->file_name }}" 
                   loading="lazy" 
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
            @else
              <video src="{{ $item->file_url }}#t=0.5" 
                     preload="metadata" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"></video>
              <!-- VIDEO PLAY OVERLAY -->
              <div class="absolute inset-0 bg-ink/30 flex items-center justify-center group-hover:bg-ink/40 transition-colors">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-accent text-ink brutal-border flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_#121614] group-hover:scale-110 transition-transform">
                  ▶
                </div>
              </div>
            @endif

            <!-- TYPE BADGE -->
            <div class="absolute top-2 left-2 z-10">
              <span class="px-1.5 py-0.5 {{ $item->isPhoto() ? 'bg-ink text-white' : 'bg-coral text-white' }} brutal-border text-[9px] font-mono font-black shadow-[1px_1px_0px_#121614]">
                {{ $item->isPhoto() ? 'FOTO' : 'VIDEO' }}
              </span>
            </div>

            <!-- FILE SIZE BADGE -->
            <div class="absolute bottom-2 right-2 z-10">
              <span class="px-1.5 py-0.2 bg-white/90 text-ink brutal-border text-[8px] font-mono font-bold shadow-[1px_1px_0px_#121614]">
                {{ $item->formatted_size }}
              </span>
            </div>
          </div>

          <!-- MEDIA CAPTION & ACTIONS -->
          <div class="p-2 sm:p-2.5 flex-1 flex flex-col justify-between space-y-1.5">
            <div>
              @if($item->caption)
                <p class="text-[11px] font-mono text-ink line-clamp-2 leading-tight" title="{{ $item->caption }}">
                  {{ $item->caption }}
                </p>
              @else
                <p class="text-[10px] font-mono text-ink/50 truncate italic">
                  {{ $item->file_name }}
                </p>
              @endif
            </div>

            <div class="flex items-center justify-between text-[9px] font-mono text-ink/60 pt-1 border-t border-ink/10">
              <span class="truncate">{{ $item->user->name ?? 'Anggota' }}</span>

              @if($user->isParent() || $item->user_id === $user->id || $album->user_id === $user->id)
                <form action="{{ route('albums.media.destroy', $item) }}" 
                      method="POST" 
                      onsubmit="return confirm('Hapus media ini dari album?');" 
                      class="inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" 
                          class="p-1 hover:bg-coral hover:text-white brutal-border text-ink text-[9px] leading-none transition-colors" 
                          title="Hapus Media">
                    🗑️
                  </button>
                </form>
              @endif
            </div>
          </div>

        </div>
      @endforeach
    </div>

    <!-- PAGINATION -->
    <div class="pt-4">
      {{ $media->links() }}
    </div>
  @endif

</div>

<!-- LIGHTBOX / FULLSCREEN VIEWER (PWA READY) -->
<div id="lightbox-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/90 backdrop-blur-sm hidden p-2 sm:p-4 select-none">
  <!-- CLOSE BUTTON -->
  <button type="button" 
          onclick="closeLightbox()" 
          class="absolute top-4 right-4 z-50 w-9 h-9 bg-white hover:bg-coral hover:text-white brutal-border text-ink flex items-center justify-center font-black text-sm shadow-[2px_2px_0px_#121614] transition-colors"
          aria-label="Tutup Preview">
    ✕
  </button>

  <!-- PREV / NEXT NAV BUTTONS -->
  <button type="button" 
          id="lightbox-btn-prev" 
          onclick="navigateLightbox(-1)" 
          class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 z-50 w-9 h-9 sm:w-11 sm:h-11 bg-white hover:bg-accent brutal-border text-ink flex items-center justify-center font-black text-lg shadow-[2px_2px_0px_#121614] active:scale-95 transition-all"
          aria-label="Sebelumnya">
    ‹
  </button>
  <button type="button" 
          id="lightbox-btn-next" 
          onclick="navigateLightbox(1)" 
          class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 z-50 w-9 h-9 sm:w-11 sm:h-11 bg-white hover:bg-accent brutal-border text-ink flex items-center justify-center font-black text-lg shadow-[2px_2px_0px_#121614] active:scale-95 transition-all"
          aria-label="Selanjutnya">
    ›
  </button>

  <!-- LIGHTBOX CONTENT CARD -->
  <div class="w-full max-w-4xl bg-white brutal-card overflow-hidden flex flex-col max-h-[92dvh] shadow-[6px_6px_0px_#121614]">
    
    <!-- MEDIA DISPLAY AREA -->
    <div class="relative bg-black flex items-center justify-center min-h-[240px] max-h-[65dvh] sm:max-h-[70dvh] overflow-hidden">
      <img id="lightbox-image" src="" alt="" class="max-w-full max-h-[65dvh] sm:max-h-[70dvh] object-contain hidden">
      <video id="lightbox-video" src="" controls playsinline class="max-w-full max-h-[65dvh] sm:max-h-[70dvh] hidden"></video>
    </div>

    <!-- CAPTION & DETAILS BAR -->
    <div class="p-3 sm:p-4 bg-white border-t-2 border-ink space-y-1">
      <div class="flex items-center justify-between gap-2">
        <span id="lightbox-counter" class="px-2 py-0.5 bg-accent text-ink brutal-border text-[10px] font-mono font-black">
          1 / 1
        </span>
        <span id="lightbox-meta" class="text-[10px] font-mono text-ink/60"></span>
      </div>
      <p id="lightbox-caption" class="text-xs sm:text-sm font-mono text-ink leading-relaxed"></p>
    </div>

  </div>
</div>

<!-- MODAL: UNGGAH MEDIA BARU -->
<div id="upload-media-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-ink/60 backdrop-blur-[2px] hidden">
  <div class="w-full max-w-md bg-white brutal-card p-5 sm:p-6 space-y-4 max-h-[90dvh] overflow-y-auto">
    
    <div class="flex items-center justify-between border-b-2 border-ink pb-3">
      <div class="flex items-center gap-2">
        <span class="text-xl">📤</span>
        <h3 class="text-base font-black font-display text-ink">Unggah ke Album</h3>
      </div>
      <button type="button" 
              onclick="closeUploadModal()" 
              class="w-7 h-7 bg-cream hover:bg-coral hover:text-white brutal-border text-ink flex items-center justify-center text-xs font-black transition-colors">
        ✕
      </button>
    </div>

    <!-- FORM UPLOAD -->
    <form id="upload-media-form" action="{{ route('albums.media.upload', $album) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf

      <!-- FILE PICKER & DROPZONE -->
      <div class="space-y-1">
        <label for="media_file_input" class="text-xs font-mono font-black uppercase tracking-wider text-ink block">
          Pilih Foto atau Video <span class="text-coral">*</span>
        </label>
        <div id="file-dropzone" class="p-4 bg-cream brutal-border border-dashed border-2 text-center transition-colors">
          <input type="file" 
                 id="media_file_input" 
                 name="media_file" 
                 required 
                 accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" 
                 onchange="handleMediaFileSelected(this)"
                 class="hidden">
          <input type="file" 
                 id="media_camera_input" 
                 accept="image/*" 
                 capture="environment" 
                 onchange="handleCameraSelected(this)"
                 class="hidden">
          
          <!-- DEFAULT DROPZONE STATE -->
          <div id="dropzone-empty" class="space-y-2.5 py-3">
            <span class="text-3xl block">📁</span>
            <div class="text-xs font-mono font-bold text-ink">Pilih sumber foto atau video:</div>
            <div class="flex items-center justify-center gap-2 flex-wrap">
              <button 
                type="button" 
                onclick="document.getElementById('media_camera_input').click()" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent hover:bg-accent/80 text-ink brutal-border text-xs font-black shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] cursor-pointer transition-all"
              >
                <span>📸</span>
                <span>Buka Kamera</span>
              </button>
              <button 
                type="button" 
                onclick="document.getElementById('media_file_input').click()" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-cream text-ink brutal-border text-xs font-black shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] cursor-pointer transition-all"
              >
                <span>📁</span>
                <span>Pilih Berkas / Galeri</span>
              </button>
            </div>
            <div class="text-[10px] font-mono text-ink/60">Foto JPG/PNG/WEBP (maks 5MB) atau Video MP4/MOV (maks 25MB)</div>
          </div>

          <!-- PREVIEW SELECTED FILE -->
          <div id="dropzone-preview" class="hidden space-y-2">
            <div class="w-32 h-32 mx-auto bg-black brutal-border overflow-hidden flex items-center justify-center">
              <img id="preview-image" src="" alt="Preview" class="w-full h-full object-cover hidden">
              <video id="preview-video" src="" class="w-full h-full object-cover hidden"></video>
            </div>
            <div id="preview-filename" class="text-[11px] font-mono font-black text-ink truncate max-w-xs mx-auto"></div>
            <div id="preview-filesize" class="text-[10px] font-mono text-ink/60"></div>
            <button type="button" onclick="clearSelectedFile()" class="px-2 py-0.5 bg-coral text-white brutal-border text-[9px] font-mono font-bold">
              Ganti Berkas
            </button>
          </div>
        </div>
      </div>

      <!-- CAPTION -->
      <div class="space-y-1">
        <label for="media_caption" class="text-xs font-mono font-black uppercase tracking-wider text-ink block">
          Keterangan / Cerita Momen (Opsional)
        </label>
        <textarea id="media_caption" 
                  name="caption" 
                  rows="3" 
                  maxlength="500" 
                  placeholder="Ceritakan momen di balik foto atau video ini..." 
                  class="w-full p-2.5 bg-cream brutal-border font-mono text-xs text-ink focus:bg-white focus:outline-none"></textarea>
      </div>

      <!-- ERROR ALERT BANNER -->
      <div id="upload-error-alert" class="p-2.5 bg-coral text-white brutal-border text-xs font-mono font-bold hidden space-y-1">
        <div id="upload-error-text"></div>
        <button type="button" 
                id="btn-upgrade-from-upload" 
                onclick="closeUploadModal(); openPaywallModal('album');" 
                class="px-2.5 py-1 bg-white text-ink brutal-border font-mono font-black text-[10px] uppercase hidden">
          👑 Upgrade ke PRO Sekarang
        </button>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t-2 border-ink">
        <button type="button" 
                onclick="closeUploadModal()" 
                class="px-4 py-2 bg-cream hover:bg-ink/10 text-ink brutal-border font-mono font-bold text-xs uppercase">
          Batal
        </button>
        <button type="submit" 
                id="upload-submit-btn" 
                class="px-4 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider shadow-[2px_2px_0px_#121614]">
          Mulai Unggah
        </button>
      </div>
    </form>

  </div>
</div>

<!-- MODAL: EDIT ALBUM DETAIL -->
<div id="album-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-ink/60 backdrop-blur-[2px] hidden">
  <div class="w-full max-w-md bg-white brutal-card p-5 sm:p-6 space-y-4 max-h-[90dvh] overflow-y-auto">
    <div class="flex items-center justify-between border-b-2 border-ink pb-3">
      <div class="flex items-center gap-2">
        <span class="text-xl">✏️</span>
        <h3 class="text-base font-black font-display text-ink">Edit Album</h3>
      </div>
      <button type="button" onclick="closeEditModal()" class="w-7 h-7 bg-cream hover:bg-coral hover:text-white brutal-border text-ink flex items-center justify-center text-xs font-black transition-colors">
        ✕
      </button>
    </div>

    <form id="edit-album-form" action="{{ route('albums.update', $album) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      @method('PUT')

      <div class="space-y-1">
        <label for="edit_album_title" class="text-xs font-mono font-black uppercase tracking-wider text-ink block">
          Judul Album <span class="text-coral">*</span>
        </label>
        <input type="text" 
               id="edit_album_title" 
               name="title" 
               required 
               maxlength="150" 
               value="{{ $album->title }}" 
               class="w-full p-2.5 bg-cream brutal-border font-mono text-xs text-ink focus:bg-white focus:outline-none">
      </div>

      <div class="space-y-1">
        <label for="edit_album_description" class="text-xs font-mono font-black uppercase tracking-wider text-ink block">
          Deskripsi / Catatan (Opsional)
        </label>
        <textarea id="edit_album_description" 
                  name="description" 
                  rows="3" 
                  maxlength="1000" 
                  class="w-full p-2.5 bg-cream brutal-border font-mono text-xs text-ink focus:bg-white focus:outline-none">{{ $album->description }}</textarea>
      </div>

      <div class="space-y-1.5">
        <label for="edit_album_cover" class="text-xs font-mono font-black uppercase tracking-wider text-ink block">
          Ganti Sampul Album (Opsional)
        </label>
        <div class="flex items-center gap-2">
          <input type="file" 
                 id="edit_album_cover" 
                 name="cover_image" 
                 accept="image/jpeg,image/png,image/webp" 
                 class="w-full p-2 bg-cream brutal-border font-mono text-[11px] text-ink file:mr-3 file:py-1 file:px-2.5 file:brutal-border file:bg-white file:font-mono file:font-bold file:text-xs">
          <input type="file" 
                 id="edit_album_cover_camera" 
                 accept="image/*" 
                 capture="environment" 
                 class="hidden" 
                 onchange="handleAlbumCoverCamera(this, 'edit_album_cover')">
          <button type="button" 
                  onclick="document.getElementById('edit_album_cover_camera').click()" 
                  class="px-2.5 py-2 bg-accent hover:bg-accent/80 text-ink brutal-border text-xs font-black shadow-brutal-sm shrink-0 flex items-center gap-1 cursor-pointer" 
                  title="Ambil foto sampul dengan kamera">
            <span>📸</span>
            <span class="hidden sm:inline">Kamera</span>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t-2 border-ink">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-cream hover:bg-ink/10 text-ink brutal-border font-mono font-bold text-xs uppercase">
          Batal
        </button>
        <button type="submit" class="px-4 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider shadow-[2px_2px_0px_#121614]">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const quotaData = @json($quota);
  const mediaItems = @json($mediaItems ?? []);

  let currentLightboxIdx = 0;

  // PWA Bottom-nav FAB trigger hook
  window.triggerAlbumAction = function() {
    handleUploadClick();
  };

  function handleUploadClick() {
    // If Free and both photo and video quotas are maxed out, open paywall modal immediately
    if (!quotaData.is_pro && !quotaData.can_upload_photo && !quotaData.can_upload_video) {
      if (typeof openPaywallModal === 'function') {
        openPaywallModal('album');
      } else {
        alert('Kuota Free tier (10 Foto & 1 Video) sudah penuh! Upgrade ke Pro untuk upload tanpa batas.');
      }
      return;
    }
    openUploadModal();
  }

  function openUploadModal() {
    clearSelectedFile();
    document.getElementById('upload-error-alert').classList.add('hidden');
    document.getElementById('upload-media-modal').classList.remove('hidden');
  }

  function closeUploadModal() {
    document.getElementById('upload-media-modal').classList.add('hidden');
  }

  // Click on dropzone triggers hidden file input
  document.getElementById('file-dropzone').addEventListener('click', function(e) {
    if (e.target.tagName !== 'BUTTON' && !e.target.closest('button') && e.target.id !== 'media_file_input' && e.target.id !== 'media_camera_input') {
      document.getElementById('media_file_input').click();
    }
  });

  function handleMediaFileSelected(input) {
    const cameraInput = document.getElementById('media_camera_input');
    if (cameraInput) {
      cameraInput.value = '';
      cameraInput.name = '';
    }
    input.name = 'media_file';
    handleFileSelected(input);
  }

  function handleCameraSelected(input) {
    const file = input.files && input.files[0];
    if (!file) return;
    const mediaInput = document.getElementById('media_file_input');
    if (mediaInput) {
      mediaInput.removeAttribute('required');
      mediaInput.name = '';
      mediaInput.value = '';
    }
    input.name = 'media_file';
    handleFileSelected(input);
  }

  function handleFileSelected(input) {
    const file = input.files && input.files[0];
    if (!file) return;

    const errorAlert = document.getElementById('upload-error-alert');
    const errorText = document.getElementById('upload-error-text');
    const btnUpgrade = document.getElementById('btn-upgrade-from-upload');
    const submitBtn = document.getElementById('upload-submit-btn');

    errorAlert.classList.add('hidden');
    btnUpgrade.classList.add('hidden');
    submitBtn.disabled = false;

    const isImage = file.type.startsWith('image/');
    const isVideo = file.type.startsWith('video/');

    if (!isImage && !isVideo) {
      errorText.innerText = 'Format berkas tidak didukung. Harap pilih gambar (JPG/PNG/WEBP) atau video (MP4/MOV).';
      errorAlert.classList.remove('hidden');
      submitBtn.disabled = true;
      return;
    }

    // Size check
    if (isImage && file.size > 5 * 1024 * 1024) {
      errorText.innerText = 'Ukuran foto melebihi batas 5MB (' + (file.size / 1048576).toFixed(1) + ' MB).';
      errorAlert.classList.remove('hidden');
      submitBtn.disabled = true;
      return;
    }

    if (isVideo && file.size > 25 * 1024 * 1024) {
      errorText.innerText = 'Ukuran video melebihi batas 25MB (' + (file.size / 1048576).toFixed(1) + ' MB).';
      errorAlert.classList.remove('hidden');
      submitBtn.disabled = true;
      return;
    }

    // Quota check
    if (!quotaData.is_pro) {
      if (isImage && !quotaData.can_upload_photo) {
        errorText.innerText = 'Batas Free tier tercapai: Maksimal 10 foto keluarga. Upgrade ke Pro untuk bebas simpan ratusan foto!';
        errorAlert.classList.remove('hidden');
        btnUpgrade.classList.remove('hidden');
        submitBtn.disabled = true;
        return;
      }

      if (isVideo && !quotaData.can_upload_video) {
        errorText.innerText = 'Batas Free tier tercapai: Maksimal 1 video keluarga. Upgrade ke Pro untuk upload video tanpa batas!';
        errorAlert.classList.remove('hidden');
        btnUpgrade.classList.remove('hidden');
        submitBtn.disabled = true;
        return;
      }
    }

    // Render Preview
    document.getElementById('dropzone-empty').classList.add('hidden');
    document.getElementById('dropzone-preview').classList.remove('hidden');
    document.getElementById('preview-filename').innerText = file.name;
    document.getElementById('preview-filesize').innerText = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

    const imgPreview = document.getElementById('preview-image');
    const videoPreview = document.getElementById('preview-video');

    if (isImage) {
      imgPreview.src = URL.createObjectURL(file);
      imgPreview.classList.remove('hidden');
      videoPreview.classList.add('hidden');
    } else {
      videoPreview.src = URL.createObjectURL(file);
      videoPreview.classList.remove('hidden');
      imgPreview.classList.add('hidden');
    }
  }

  function clearSelectedFile() {
    const input = document.getElementById('media_file_input');
    input.value = "";
    input.name = "media_file";
    input.required = true;
    const cameraInput = document.getElementById('media_camera_input');
    if (cameraInput) {
      cameraInput.value = "";
      cameraInput.name = "";
    }
    document.getElementById('dropzone-empty').classList.remove('hidden');
    document.getElementById('dropzone-preview').classList.add('hidden');
    document.getElementById('preview-image').classList.add('hidden');
    document.getElementById('preview-video').classList.add('hidden');
    document.getElementById('upload-error-alert').classList.add('hidden');
    document.getElementById('upload-submit-btn').disabled = false;
  }

  // LIGHTBOX LOGIC
  function openLightbox(index) {
    if (!mediaItems || !mediaItems[index]) return;
    currentLightboxIdx = index;
    renderLightboxItem();
    document.getElementById('lightbox-modal').classList.remove('hidden');
  }

  function renderLightboxItem() {
    const item = mediaItems[currentLightboxIdx];
    if (!item) return;

    const img = document.getElementById('lightbox-image');
    const vid = document.getElementById('lightbox-video');

    vid.pause();

    if (item.is_photo) {
      img.src = item.url;
      img.classList.remove('hidden');
      vid.classList.add('hidden');
    } else {
      vid.src = item.url;
      vid.classList.remove('hidden');
      img.classList.add('hidden');
    }

    document.getElementById('lightbox-counter').innerText = `${currentLightboxIdx + 1} / ${mediaItems.length}`;
    document.getElementById('lightbox-meta').innerText = `${item.uploader} • ${item.date} (${item.size})`;
    document.getElementById('lightbox-caption').innerText = item.caption || item.file_name;

    // Prev / Next button states
    document.getElementById('lightbox-btn-prev').style.visibility = currentLightboxIdx > 0 ? 'visible' : 'hidden';
    document.getElementById('lightbox-btn-next').style.visibility = currentLightboxIdx < mediaItems.length - 1 ? 'visible' : 'hidden';
  }

  function navigateLightbox(direction) {
    const nextIdx = currentLightboxIdx + direction;
    if (nextIdx >= 0 && nextIdx < mediaItems.length) {
      currentLightboxIdx = nextIdx;
      renderLightboxItem();
    }
  }

  function closeLightbox() {
    const vid = document.getElementById('lightbox-video');
    vid.pause();
    document.getElementById('lightbox-modal').classList.add('hidden');
  }

  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    const lightboxModal = document.getElementById('lightbox-modal');
    if (!lightboxModal.classList.contains('hidden')) {
      if (e.key === 'ArrowLeft') navigateLightbox(-1);
      if (e.key === 'ArrowRight') navigateLightbox(1);
      if (e.key === 'Escape') closeLightbox();
    }

    if (e.key === 'Escape') {
      closeUploadModal();
      closeEditModal();
    }
  });

  function handleAlbumCoverCamera(cameraInput, targetInputId) {
    const file = cameraInput.files && cameraInput.files[0];
    if (!file) return;
    const targetInput = document.getElementById(targetInputId);
    if (targetInput && window.DataTransfer) {
      try {
        const dt = new DataTransfer();
        dt.items.add(file);
        targetInput.files = dt.files;
      } catch (e) {}
    }
  }

  function openEditAlbumModal(id, titleText, descText) {
    document.getElementById('edit_album_title').value = titleText || '';
    document.getElementById('edit_album_description').value = descText || '';
    const editCover = document.getElementById('edit_album_cover');
    if (editCover) editCover.value = '';
    const camCover = document.getElementById('edit_album_cover_camera');
    if (camCover) camCover.value = '';
    document.getElementById('album-modal').classList.remove('hidden');
  }

  function closeEditModal() {
    document.getElementById('album-modal').classList.add('hidden');
  }

  @if(session('quota_error'))
    if (typeof openPaywallModal === 'function') {
      openPaywallModal('album');
    }
  @endif
</script>
@endpush
