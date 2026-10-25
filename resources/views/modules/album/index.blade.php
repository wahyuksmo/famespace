@extends('layouts.main')

@section('title', 'Album Keluarga — ' . ($family->name ?? 'FamKoji'))

@section('content')
<div class="space-y-6">

  <!-- TOP HEADER / BANNER -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-6 bg-white brutal-card">
    <div class="space-y-1">
      <div class="flex items-center gap-2">
        <span class="text-2xl sm:text-3xl">📸</span>
        <h1 class="text-xl sm:text-2xl font-black font-display tracking-tight text-ink">Album Keluarga</h1>
      </div>
      <p class="text-xs sm:text-sm font-mono text-ink/70">
        Simpan dan abadikan momen memori indah bersama keluarga dalam satu tempat.
      </p>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-auto">
      <button type="button" 
              onclick="openAlbumModal()" 
              class="px-4 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider flex items-center gap-2 shadow-[2px_2px_0px_#121614] active:scale-95 transition-all">
        <span>+</span>
        <span>Buat Album Baru</span>
      </button>
    </div>
  </div>

  <!-- QUOTA TIER CARD (PWA FRIENDLY) -->
  <div class="p-4 sm:p-5 {{ $quota['is_pro'] ? 'bg-brand-soft border-brand' : 'bg-cream' }} brutal-card">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      
      <!-- KUOTA INFO -->
      <div class="space-y-2 flex-1">
        <div class="flex items-center gap-2">
          <span class="text-xs font-mono font-black uppercase tracking-wider text-ink/60">Status Kuota Media:</span>
          @if($quota['is_pro'])
            <span class="px-2 py-0.5 bg-accent text-ink brutal-border text-[10px] font-mono font-black">
              PRO 👑 UNLIMITED
            </span>
          @else
            <span class="px-2 py-0.5 bg-coral text-white brutal-border text-[10px] font-mono font-black">
              FREE TIER
            </span>
          @endif
        </div>

        <!-- PROGRESS METERS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
          <!-- FOTO METER -->
          <div class="p-2.5 bg-white brutal-border space-y-1.5 shadow-[1.5px_1.5px_0px_#121614]">
            <div class="flex items-center justify-between text-xs font-mono font-bold">
              <span class="flex items-center gap-1.5">
                <span>🖼️</span>
                <span>Foto Keluarga</span>
              </span>
              <span class="font-black {{ (!$quota['is_pro'] && $quota['photos_used'] >= 10) ? 'text-coral' : 'text-ink' }}">
                {{ $quota['photos_used'] }} / {{ $quota['is_pro'] ? '∞' : $quota['photos_limit'] }}
              </span>
            </div>
            <div class="w-full bg-cream h-2.5 brutal-border overflow-hidden">
              <div class="h-full {{ (!$quota['is_pro'] && $quota['photos_used'] >= 10) ? 'bg-coral' : 'bg-brand' }} transition-all duration-300" 
                   style="width: {{ $quota['is_pro'] ? '100%' : min(100, ($quota['photos_used'] / 10) * 100) }}%"></div>
            </div>
            <div class="text-[9px] font-mono text-ink/60 flex justify-between">
              <span>{{ $quota['is_pro'] ? 'Tanpa batas upload' : 'Maksimal 10 foto (JPG/PNG/WEBP)' }}</span>
              @if(!$quota['is_pro'] && $quota['photos_used'] >= 10)
                <span class="font-black text-coral">Penuh</span>
              @endif
            </div>
          </div>

          <!-- VIDEO METER -->
          <div class="p-2.5 bg-white brutal-border space-y-1.5 shadow-[1.5px_1.5px_0px_#121614]">
            <div class="flex items-center justify-between text-xs font-mono font-bold">
              <span class="flex items-center gap-1.5">
                <span>🎥</span>
                <span>Video Keluarga</span>
              </span>
              <span class="font-black {{ (!$quota['is_pro'] && $quota['videos_used'] >= 1) ? 'text-coral' : 'text-ink' }}">
                {{ $quota['videos_used'] }} / {{ $quota['is_pro'] ? '∞' : $quota['videos_limit'] }}
              </span>
            </div>
            <div class="w-full bg-cream h-2.5 brutal-border overflow-hidden">
              <div class="h-full {{ (!$quota['is_pro'] && $quota['videos_used'] >= 1) ? 'bg-coral' : 'bg-brand' }} transition-all duration-300" 
                   style="width: {{ $quota['is_pro'] ? '100%' : min(100, ($quota['videos_used'] / 1) * 100) }}%"></div>
            </div>
            <div class="text-[9px] font-mono text-ink/60 flex justify-between">
              <span>{{ $quota['is_pro'] ? 'Tanpa batas video' : 'Maksimal 1 video (MP4/MOV)' }}</span>
              @if(!$quota['is_pro'] && $quota['videos_used'] >= 1)
                <span class="font-black text-coral">Penuh</span>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- UPGRADE CTA IF FREE -->
      @if(!$quota['is_pro'])
        <div class="flex flex-col items-start md:items-end justify-center gap-2 border-t md:border-t-0 md:border-l-2 border-ink pt-3 md:pt-0 md:pl-5 shrink-0">
          <div class="text-xs font-mono font-bold text-ink">
            Ingin simpan album tanpa batas?
          </div>
          <button type="button" 
                  onclick="openPaywallModal('album')" 
                  class="px-3.5 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider flex items-center gap-1.5 shadow-[2px_2px_0px_#121614] active:scale-95 transition-all">
            <span>👑</span>
            <span>Upgrade ke PRO</span>
          </button>
        </div>
      @endif

    </div>
  </div>

  <!-- ALBUM GRID -->
  @if($albums->isEmpty())
    <div class="p-8 sm:p-12 bg-white brutal-card text-center space-y-4">
      <div class="w-16 h-16 mx-auto bg-brand-soft brutal-border flex items-center justify-center text-3xl shadow-[3px_3px_0px_#121614]">
        📁
      </div>
      <div class="space-y-1">
        <h3 class="text-base sm:text-lg font-mono font-black text-ink">Belum Ada Album Keluarga</h3>
        <p class="text-xs sm:text-sm font-mono text-ink/60 max-w-md mx-auto">
          Mulai kumpulkan memori keluarga dengan membuat album pertama, misalnya: "Liburan Lebaran", "Ulang Tahun Adik", atau "Keseharian Rumah".
        </p>
      </div>
      <button type="button" 
              onclick="openAlbumModal()" 
              class="px-4 py-2.5 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider shadow-[2px_2px_0px_#121614]">
        + Buat Album Sekarang
      </button>
    </div>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
      @foreach($albums as $album)
        <div class="bg-white brutal-card flex flex-col group overflow-hidden hover:-translate-y-0.5 hover:shadow-[4px_4px_0px_#121614] transition-all">
          
          <!-- COVER IMAGE / PREVIEW -->
          <a href="{{ route('albums.show', $album) }}" class="relative aspect-video sm:aspect-4/3 bg-cream overflow-hidden block border-b-2 border-ink">
            @if($album->cover_url)
              <img src="{{ $album->cover_url }}" 
                   alt="{{ $album->title }}" 
                   loading="lazy"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            @else
              <div class="w-full h-full flex flex-col items-center justify-center text-ink/40 gap-1.5 p-4 bg-brand-soft/40">
                <span class="text-3xl">🖼️</span>
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider">Belum Ada Foto</span>
              </div>
            @endif

            <!-- BADGE MEDIA COUNT -->
            <div class="absolute bottom-2 left-2 flex items-center gap-1.5 z-10">
              <span class="px-2 py-0.5 bg-ink text-white brutal-border text-[9px] font-mono font-black shadow-[1px_1px_0px_#FFFFFF] flex items-center gap-1">
                <span>📸</span>
                <span>{{ $album->photos_count }}</span>
              </span>
              <span class="px-2 py-0.5 bg-accent text-ink brutal-border text-[9px] font-mono font-black shadow-[1px_1px_0px_#121614] flex items-center gap-1">
                <span>🎥</span>
                <span>{{ $album->videos_count }}</span>
              </span>
            </div>
          </a>

          <!-- CARD BODY -->
          <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
            <div class="space-y-1">
              <a href="{{ route('albums.show', $album) }}" class="block group-hover:text-brand transition-colors">
                <h2 class="text-sm sm:text-base font-black font-display text-ink truncate" title="{{ $album->title }}">
                  {{ $album->title }}
                </h2>
              </a>
              @if($album->description)
                <p class="text-[11px] font-mono text-ink/70 line-clamp-2 leading-relaxed">
                  {{ $album->description }}
                </p>
              @endif
            </div>

            <!-- CARD FOOTER / META & ACTIONS -->
            <div class="pt-2 border-t border-ink/15 flex items-center justify-between text-[10px] font-mono text-ink/60">
              <div class="truncate flex items-center gap-1">
                <span>👤</span>
                <span class="truncate font-bold">{{ $album->user->name ?? 'Anggota' }}</span>
              </div>

              <div class="flex items-center gap-1.5 shrink-0">
                @if($user->isParent() || $album->user_id === $user->id)
                  <button type="button" 
                          onclick="openEditAlbumModal({{ $album->id }}, @js($album->title), @js($album->description))" 
                          class="p-1 hover:bg-cream brutal-border text-ink text-[10px] leading-none transition-colors" 
                          title="Edit Album">
                    ✏️
                  </button>
                  <form action="{{ route('albums.destroy', $album) }}" 
                        method="POST" 
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus album ini beserta seluruh isinya?');" 
                        class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="p-1 hover:bg-coral hover:text-white brutal-border text-ink text-[10px] leading-none transition-colors" 
                            title="Hapus Album">
                      🗑️
                    </button>
                  </form>
                @endif
                <a href="{{ route('albums.show', $album) }}" 
                   class="px-2 py-0.5 bg-accent hover:bg-brand hover:text-white text-ink brutal-btn font-mono font-black text-[9px] uppercase">
                  Buka ➔
                </a>
              </div>
            </div>

          </div>

        </div>
      @endforeach
    </div>

    <!-- PAGINATION -->
    <div class="pt-4">
      {{ $albums->links() }}
    </div>
  @endif

</div>
@endsection

@push('modals')
<!-- MODAL: BUAT / EDIT ALBUM -->
<div id="album-modal" class="fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-4 bg-ink/75 backdrop-blur-sm hidden">
  <div class="w-full max-w-md bg-white brutal-card p-5 sm:p-6 space-y-4 max-h-[90dvh] overflow-y-auto">
    
    <div class="flex items-center justify-between border-b-2 border-ink pb-3">
      <div class="flex items-center gap-2">
        <span class="text-xl">📁</span>
        <h3 id="album-modal-title" class="text-base font-black font-display text-ink">Buat Album Baru</h3>
      </div>
      <button type="button" 
              onclick="closeAlbumModal()" 
              class="w-7 h-7 bg-cream hover:bg-coral hover:text-white brutal-border text-ink flex items-center justify-center text-xs font-black transition-colors">
        ✕
      </button>
    </div>

    <form id="album-modal-form" action="{{ route('albums.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <input type="hidden" name="_method" id="album-modal-method" value="POST">

      <div class="space-y-1">
        <label for="album_title" class="text-xs font-mono font-black uppercase tracking-wider text-ink block">
          Judul Album <span class="text-coral">*</span>
        </label>
        <input type="text" 
               id="album_title" 
               name="title" 
               required 
               maxlength="150" 
               placeholder="Contoh: Liburan ke Pantai 2026" 
               class="w-full p-2.5 bg-cream brutal-border font-mono text-xs text-ink focus:bg-white focus:outline-none">
      </div>

      <div class="space-y-1">
        <label for="album_description" class="text-xs font-mono font-black uppercase tracking-wider text-ink block">
          Deskripsi / Catatan (Opsional)
        </label>
        <textarea id="album_description" 
                  name="description" 
                  rows="3" 
                  maxlength="1000" 
                  placeholder="Kisah atau catatan kecil tentang album ini..." 
                  class="w-full p-2.5 bg-cream brutal-border font-mono text-xs text-ink focus:bg-white focus:outline-none"></textarea>
      </div>

      <div class="space-y-1.5">
        <label for="album_cover" class="text-xs font-mono font-black uppercase tracking-wider text-ink block">
          Sampul Album (Opsional)
        </label>
        <div class="flex items-center gap-2">
          <input type="file" 
                 id="album_cover" 
                 name="cover_image" 
                 accept="image/jpeg,image/png,image/webp" 
                 class="w-full p-2 bg-cream brutal-border font-mono text-[11px] text-ink file:mr-3 file:py-1 file:px-2.5 file:brutal-border file:bg-white file:font-mono file:font-bold file:text-xs">
          <input type="file" 
                 id="album_cover_camera" 
                 accept="image/*" 
                 capture="environment" 
                 class="hidden" 
                 onchange="handleAlbumCoverCamera(this, 'album_cover')">
          <button type="button" 
                  onclick="document.getElementById('album_cover_camera').click()" 
                  class="px-2.5 py-2 bg-accent hover:bg-accent/80 text-ink brutal-border text-xs font-black shadow-brutal-sm shrink-0 flex items-center gap-1 cursor-pointer" 
                  title="Ambil foto sampul dengan kamera">
            <span>📸</span>
            <span class="hidden sm:inline">Kamera</span>
          </button>
        </div>
        <p class="text-[10px] font-mono text-ink/60">Foto JPG/PNG/WEBP.</p>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t-2 border-ink">
        <button type="button" 
                onclick="closeAlbumModal()" 
                class="px-4 py-2 bg-cream hover:bg-ink/10 text-ink brutal-border font-mono font-bold text-xs uppercase">
          Batal
        </button>
        <button type="submit" 
                id="album-modal-submit-btn" 
                class="px-4 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider shadow-[2px_2px_0px_#121614]">
          Simpan Album
        </button>
      </div>
    </form>

  </div>
</div>
@endpush

@push('scripts')
<script>
  window.triggerAlbumAction = function() {
    openAlbumModal();
  };

  function openAlbumModal() {
    const modal = document.getElementById('album-modal');
    const form = document.getElementById('album-modal-form');
    const title = document.getElementById('album-modal-title');
    const methodInput = document.getElementById('album-modal-method');
    const titleInput = document.getElementById('album_title');
    const descInput = document.getElementById('album_description');

    form.action = "{{ route('albums.store') }}";
    methodInput.value = "POST";
    title.innerText = "Buat Album Baru";
    titleInput.value = "";
    descInput.value = "";
    document.getElementById('album_cover').value = "";
    const camInput = document.getElementById('album_cover_camera');
    if (camInput) camInput.value = "";

    modal.classList.remove('hidden');
    titleInput.focus();
  }

  function openEditAlbumModal(id, titleText, descText) {
    const modal = document.getElementById('album-modal');
    const form = document.getElementById('album-modal-form');
    const title = document.getElementById('album-modal-title');
    const methodInput = document.getElementById('album-modal-method');
    const titleInput = document.getElementById('album_title');
    const descInput = document.getElementById('album_description');

    form.action = `/albums/${id}`;
    methodInput.value = "PUT";
    title.innerText = "Edit Album";
    titleInput.value = titleText || "";
    descInput.value = descText || "";
    document.getElementById('album_cover').value = "";
    const camInput = document.getElementById('album_cover_camera');
    if (camInput) camInput.value = "";

    modal.classList.remove('hidden');
    titleInput.focus();
  }

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

  function closeAlbumModal() {
    document.getElementById('album-modal').classList.add('hidden');
  }

  // Handle ESC key to close modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeAlbumModal();
    }
  });

  @if(session('quota_error'))
    if (typeof openPaywallModal === 'function') {
      openPaywallModal('album');
    }
  @endif
</script>
@endpush
