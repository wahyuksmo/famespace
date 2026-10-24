<!-- MODAL: EDIT PROFIL & FOTO (NEO-BRUTALIST) -->
<div id="profile-modal" class="fixed inset-0 bg-ink/80 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closeProfileModal()">
  <div class="bg-cream brutal-card max-w-md w-full p-5 sm:p-6 relative space-y-4 my-auto max-h-[92vh] overflow-y-auto">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
      <div class="flex items-center gap-2">
        <span class="text-2xl">👤</span>
        <div>
          <h3 class="font-black font-display text-lg text-ink">Edit Profil Saya</h3>
          <p class="text-[11px] font-mono text-ink/70">Ubah foto profil dan nama akun</p>
        </div>
      </div>
      <button type="button" onclick="closeProfileModal()" class="w-8 h-8 bg-white hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- FORM -->
    <form id="profile-form" onsubmit="handleProfileSubmit(event)" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <input type="hidden" name="remove_avatar" id="profile-remove-avatar-input" value="0">
      <input type="file" id="profile-avatar-input" name="avatar" accept="image/*" class="hidden" onchange="previewProfileAvatar(this)">

      <!-- AVATAR SECTION -->
      <div class="p-4 bg-white brutal-border space-y-3">
        <label class="block text-xs font-mono font-black uppercase text-ink">Foto Profil</label>
        
        <div class="flex items-center gap-4">
          <!-- AVATAR PREVIEW BOX -->
          <div class="relative group shrink-0">
            <div id="profile-modal-avatar-preview-box" class="w-20 h-20 bg-brand-soft brutal-border overflow-hidden flex items-center justify-center text-3xl font-black">
              <img id="profile-modal-avatar-img" 
                   src="{{ auth()->user()?->avatar_url ?? '' }}" 
                   alt="Foto Profil" 
                   class="w-full h-full object-cover {{ auth()->user()?->avatar_url ? '' : 'hidden' }}">
              <span id="profile-modal-avatar-placeholder" class="{{ auth()->user()?->avatar_url ? 'hidden' : '' }}">
                {{ match(auth()->user()?->role ?? '') {
                  'Ayah' => '👨',
                  'Ibu' => '👩',
                  'Anak' => '👦',
                  default => strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1))
                } }}
              </span>
            </div>
          </div>

          <!-- AVATAR CONTROLS -->
          <div class="space-y-2 flex-1">
            <div class="flex flex-wrap gap-2">
              <button type="button" 
                      onclick="document.getElementById('profile-avatar-input').click()" 
                      class="px-3 py-1.5 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-black uppercase tracking-wider flex items-center gap-1.5">
                <span>📸 Pilih / Kamera</span>
              </button>
              
              <button type="button" 
                      id="profile-remove-avatar-btn"
                      onclick="resetProfileAvatar()" 
                      class="px-2.5 py-1.5 bg-white text-ink hover:bg-coral hover:text-white brutal-btn text-xs font-bold transition-colors flex items-center gap-1 {{ auth()->user()?->avatar_url ? '' : 'hidden' }}">
                <span>🗑️ Hapus</span>
              </button>
            </div>
            <p class="text-[10px] font-mono text-ink/60 leading-tight">
              Kamera HP atau file gambar (JPG, PNG, WEBP, maks 2MB).
            </p>
          </div>
        </div>
      </div>

      <!-- NAMA LENGKAP -->
      <div class="space-y-1.5">
        <label for="profile-name-input" class="block text-xs font-mono font-black uppercase text-ink">
          Nama Lengkap <span class="text-coral">*</span>
        </label>
        <input type="text" 
               id="profile-name-input" 
               name="name" 
               value="{{ auth()->user()?->name ?? '' }}" 
               required 
               maxlength="255"
               class="w-full px-3.5 py-2.5 bg-white brutal-border font-bold text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand shadow-brutal-sm"
               placeholder="Nama Anda">
      </div>

      <!-- READ-ONLY INFO (ROLE, EMAIL, KELUARGA) -->
      <div class="grid grid-cols-2 gap-2 text-xs font-mono">
        <div class="p-2.5 bg-cream-soft brutal-border space-y-0.5">
          <span class="text-[9px] uppercase font-bold text-ink/60 block">Peran</span>
          <span class="font-black text-ink">{{ auth()->user()?->role ?? 'Anggota' }}</span>
        </div>
        <div class="p-2.5 bg-cream-soft brutal-border space-y-0.5">
          <span class="text-[9px] uppercase font-bold text-ink/60 block">Ruang Keluarga</span>
          <span class="font-black text-ink truncate block">{{ auth()->user()?->family?->name ?? 'Famespace' }}</span>
        </div>
        <div class="col-span-2 p-2.5 bg-cream-soft brutal-border space-y-0.5">
          <span class="text-[9px] uppercase font-bold text-ink/60 block">Email Terdaftar</span>
          <span class="font-bold text-ink/80 truncate block">{{ auth()->user()?->email ?? '-' }}</span>
        </div>
      </div>

      <!-- FOOTER BUTTONS -->
      <div class="flex items-center justify-end gap-2 pt-2 border-t-2 border-ink">
        <button type="button" 
                onclick="closeProfileModal()" 
                class="px-4 py-2 bg-white hover:bg-cream brutal-btn text-xs font-bold text-ink">
          Batal
        </button>
        <button type="submit" 
                id="profile-submit-btn" 
                class="px-5 py-2 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-black uppercase tracking-wider flex items-center gap-1.5 shadow-brutal-sm">
          <span id="profile-submit-spinner" class="hidden animate-spin">⏳</span>
          <span id="profile-submit-text">💾 Simpan</span>
        </button>
      </div>
    </form>

  </div>
</div>
