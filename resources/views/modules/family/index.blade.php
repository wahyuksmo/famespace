@extends('layouts.main')

@section('title', 'Anggota Keluarga — ' . ($family->name ?? 'FamKoji') . ' — FamKoji')

@section('content')
<!-- TAB 6: ANGGOTA KELUARGA & KODE UNDANGAN -->
<div id="view-members" class="space-y-6">
  
  <!-- HEADER ANGGOTA -->
  <div class="bg-white brutal-card p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <span class="px-2.5 py-0.5 bg-accent brutal-border font-mono text-[10px] sm:text-xs font-black uppercase">
        STRUKTUR KELUARGA {{ strtoupper($family->name) }}
      </span>
      <h2 class="text-xl sm:text-2xl font-black font-display text-ink mt-1">Anggota & Peran Keluarga</h2>
      <p class="text-xs font-medium text-ink/70">Atur hak akses pengelolaan kas, checklist tugas, dan undang anggota baru.</p>
    </div>
    <button type="button" onclick="copyDashboardInvite()" id="btn-copy-dash" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider flex items-center gap-1.5">
      <span>🔗 Salin Link Undangan</span>
    </button>
  </div>

  <!-- SHARE KODE BOX -->
  <div class="p-4 bg-accent/25 brutal-card flex flex-col sm:flex-row items-center justify-between gap-4">
    <div>
      <div class="text-xs font-mono font-bold uppercase text-ink">Kode Rahasia Ruang Keluarga:</div>
      <div class="text-2xl font-black font-mono tracking-widest text-brand mt-0.5" id="display-invite-code">{{ $family->invite_code }}</div>
      <div class="text-xs font-semibold text-ink/70">Bagikan kode ini ke keluarga yang ingin join via halaman Registrasi.</div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="https://wa.me/?text=Halo!%20Ayo%20gabung%20ke%20ruang%20keluarga%20kita%20di%20FamKoji:%20{{ url('/join/' . $family->invite_code) }}" id="btn-wa-invite" target="_blank" class="px-3.5 py-2 bg-brand text-white brutal-btn text-xs font-bold">
        💬 Kirim via WhatsApp
      </a>
      @if($user->isParent())
        <button type="button" onclick="regenerateInviteCode()" id="btn-regen-invite" class="px-3.5 py-2 bg-white text-ink hover:bg-cream brutal-btn text-xs font-bold flex items-center gap-1.5" title="Acak ulang kode jika ada yang mencurigakan mencoba menyusup">
          <span>🔄 Acak Ulang Kode</span>
        </button>
      @endif
    </div>
  </div>

  <!-- MEMBER CARDS GRID -->
  <div id="members-list-section" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @if($members->count() > 0)
      @foreach($members as $m)
        @php
          $bg = match($m->role) {
            'Ayah' => 'bg-brand text-white',
            'Ibu' => 'bg-accent text-ink',
            'Anak' => 'bg-sky text-ink',
            default => 'bg-coral text-white'
          };
          $emoji = match($m->role) {
            'Ayah' => '👨',
            'Ibu' => '👩',
            'Anak' => '👦',
            default => '👧'
          };
          $badgeText = match($m->role) {
            'Ayah' => 'ADMIN UTAMA',
            'Ibu' => 'CO-ADMIN',
            default => 'ANGGOTA'
          };
        @endphp
        <div class="bg-white brutal-card p-5 space-y-3 transition-all duration-300" id="member-card-{{ $m->id }}">
          <div class="flex items-center justify-between">
            <div class="w-12 h-12 {{ $bg }} brutal-border flex items-center justify-center text-2xl font-black overflow-hidden" id="member-avatar-{{ $m->id }}">
              @if($m->avatar_url)
                <img src="{{ $m->avatar_url }}" alt="{{ $m->name }}" class="w-full h-full object-cover">
              @else
                <span>{{ $emoji }}</span>
              @endif
            </div>
            <div class="flex items-center gap-1.5">
              @if($m->id === $user->id)
                <button type="button" 
                        onclick="openProfileModal()" 
                        class="px-2 py-0.5 bg-brand-soft hover:bg-brand text-brand hover:text-white brutal-border font-mono text-[10px] font-black transition-colors"
                        title="Edit Profil & Foto Saya">
                  ✏️ Edit
                </button>
              @endif
              <span id="member-badge-{{ $m->id }}" class="px-2 py-0.5 {{ $m->role === 'Ayah' ? 'bg-brand text-white' : ($m->role === 'Ibu' ? 'bg-accent text-ink' : 'bg-white text-ink') }} brutal-border font-mono text-[10px] font-bold">
                {{ $badgeText }}
              </span>
            </div>
          </div>
          <div>
            <div class="font-black text-base text-ink" id="member-name-{{ $m->id }}">{{ $m->name }}</div>
            <div class="text-xs font-mono font-bold text-brand" id="member-role-{{ $m->id }}">{{ $m->role ?? 'Anggota' }}</div>
            <div class="text-[11px] text-ink/60 mt-1 truncate">{{ $m->email }}</div>
          </div>
          <div class="pt-2 border-t-2 border-ink flex items-center justify-between text-xs font-black">
            <span>Poin Chores:</span>
            <span class="text-brand font-mono">{{ $m->points }} Poin</span>
          </div>

          @if($m->isParent())
            <div class="pt-2 border-t-2 border-ink flex items-center justify-between text-xs font-black">
              <span>Data Keuangan:</span>
              <span class="px-2 py-0.5 bg-brand text-white brutal-border font-mono text-[9px] uppercase font-bold">Penuh (Orang Tua)</span>
            </div>
          @elseif($user->isParent())
            <div class="pt-2 border-t-2 border-ink flex items-center justify-between text-xs font-black">
              <div class="flex flex-col pr-2">
                <span class="text-ink">Lihat Seluruh Kas:</span>
                <span class="text-[10px] font-mono font-medium text-ink/70" id="label-finance-{{ $m->id }}">
                  {{ $m->can_view_all_finances ? 'Penuh (Seluruh Keluarga)' : 'Terbatas (Milik Sendiri)' }}
                </span>
              </div>
              <label class="relative inline-flex items-center cursor-pointer shrink-0">
                <input type="checkbox" 
                       onchange="toggleFinanceAccess({{ $m->id }}, this)" 
                       class="sr-only peer" 
                       {{ $m->can_view_all_finances ? 'checked' : '' }}>
                <div class="w-10 h-6 bg-ink/20 peer-focus:outline-none brutal-border peer peer-checked:after:translate-x-4 peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-ink after:border after:h-4 after:w-4 after:transition-all peer-checked:bg-brand"></div>
              </label>
            </div>
          @else
            <div class="pt-2 border-t-2 border-ink flex items-center justify-between text-xs font-black">
              <span>Data Keuangan:</span>
              <span class="px-2 py-0.5 {{ $m->can_view_all_finances ? 'bg-brand/20 text-brand' : 'bg-ink/10 text-ink/70' }} brutal-border font-mono text-[9px] font-bold">
                {{ $m->can_view_all_finances ? 'Penuh' : 'Terbatas' }}
              </span>
            </div>
          @endif

          @if($user->isParent() || $m->id === $user->id)
            <div class="pt-2 border-t-2 border-ink space-y-1.5">
              <div class="grid {{ ($user->isParent() && !$m->isParent()) ? 'grid-cols-2' : 'grid-cols-1' }} gap-1.5">
                <button type="button" 
                        onclick="openPasswordModal({{ $m->id }}, '{{ addslashes($m->name) }}')"
                        class="py-1.5 px-2 bg-cream hover:bg-accent text-ink brutal-border font-mono text-[11px] font-bold flex items-center justify-center gap-1 transition-all shadow-[2px_2px_0px_#121614] active:translate-x-[1px] active:translate-y-[1px]">
                  <span>🔑</span>
                  <span>Ubah Password</span>
                </button>
                @if($user->isParent() && !$m->isParent())
                  <button type="button" 
                          onclick="openRoleModal({{ $m->id }}, '{{ addslashes($m->name) }}', '{{ addslashes($m->role) }}')"
                          class="py-1.5 px-2 bg-cream hover:bg-mustard text-ink brutal-border font-mono text-[11px] font-bold flex items-center justify-center gap-1 transition-all shadow-[2px_2px_0px_#121614] active:translate-x-[1px] active:translate-y-[1px]">
                    <span>🎭</span>
                    <span>Ubah Peran</span>
                  </button>
                @endif
              </div>

              @if($user->isParent() && $m->id !== $user->id && !$m->isParent())
                <button type="button" 
                        onclick="openKickModal({{ $m->id }}, '{{ addslashes($m->name) }}', '{{ addslashes($m->role) }}')" 
                        class="w-full py-1.5 px-2 bg-red-50 hover:bg-coral text-coral hover:text-white brutal-border font-mono text-[11px] font-bold uppercase tracking-wider flex items-center justify-center gap-1 transition-all shadow-[2px_2px_0px_#121614] active:translate-x-[1px] active:translate-y-[1px]">
                  <span>🚫</span>
                  <span>Keluarkan dari Keluarga</span>
                </button>
              @endif
            </div>
          @endif
        </div>
      @endforeach
    @else
      <div class="col-span-full bg-cream brutal-card p-8 text-center space-y-2">
        <div class="text-3xl">👨‍👩‍👧‍👦</div>
        <h3 class="font-display font-black text-base text-ink">Belum Ada Anggota Keluarga Lainnya</h3>
        <p class="text-xs font-medium text-ink/70 max-w-sm mx-auto">
          Bagikan tautan atau kode undangan <strong class="font-mono text-brand">{{ $family->invite_code }}</strong> untuk mengajak pasangan atau anak bergabung ke ruang keluarga ini.
        </p>
      </div>
    @endif
  </div>

</div>
@endsection

@push('modals')
<!-- MODAL: KONFIRMASI KICK ANGGOTA (NEOBRUTALIST) -->
<div id="confirm-kick-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closeKickModal()">
  <div class="bg-cream brutal-card max-w-sm sm:max-w-md w-full p-5 sm:p-6 relative modal-box space-y-4">
    
    <!-- HEADER -->
    <div class="flex items-start justify-between pb-3 border-b-2 border-ink">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-coral text-white brutal-border flex items-center justify-center font-black text-lg shadow-[2px_2px_0px_#121614]">
          🚫
        </div>
        <div>
          <span class="text-[10px] font-black font-mono uppercase text-coral tracking-widest block">Keamanan Keluarga</span>
          <h3 class="font-black font-display text-lg sm:text-xl text-ink leading-tight">Keluarkan Anggota</h3>
        </div>
      </div>
      <button type="button" onclick="closeKickModal()" class="w-8 h-8 bg-white hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- CONTENT BODY -->
    <div class="space-y-3">
      <p class="text-xs sm:text-sm text-ink font-medium leading-relaxed">
        Apakah Anda yakin ingin mengeluarkan <span id="kick-target-name" class="font-black text-ink bg-coral/20 px-1.5 py-0.5 brutal-border inline-block break-all"></span> (<span id="kick-target-role" class="font-mono font-bold text-coral"></span>) dari ruang keluarga?
      </p>

      <div class="p-3 bg-white brutal-border border-l-4 border-l-coral flex items-start gap-2.5">
        <span class="text-base flex-shrink-0">⚠️</span>
        <div class="text-[11px] font-mono font-bold text-ink/80 leading-snug">
          Akses anggota ini ke kas, tugas, agenda, dan momen linimasa keluarga akan langsung dicabut serta sesi login mereka dihentikan.
        </div>
      </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="pt-2 flex items-center gap-3">
      <button type="button" onclick="closeKickModal()" class="w-1/2 py-2.5 bg-white hover:bg-cream brutal-btn text-xs font-mono font-black uppercase tracking-wider text-ink transition-all">
        Batal
      </button>
      <button type="button" id="btn-confirm-kick" onclick="executeKickMember()" class="w-1/2 py-2.5 bg-coral hover:bg-coral/90 text-white brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all">
        <span>Ya, Keluarkan</span>
      </button>
    </div>

  </div>
</div>

<!-- MODAL: UBAH PASSWORD ANGGOTA (NEOBRUTALIST) -->
<div id="modal-change-password" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closePasswordModal()">
  <div class="bg-cream brutal-card max-w-sm sm:max-w-md w-full p-5 sm:p-6 relative modal-box space-y-4">
    <!-- HEADER -->
    <div class="flex items-start justify-between pb-3 border-b-2 border-ink">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-accent text-ink brutal-border flex items-center justify-center font-black text-lg shadow-[2px_2px_0px_#121614]">
          🔑
        </div>
        <div>
          <span class="text-[10px] font-black font-mono uppercase text-brand tracking-widest block">Keamanan Akun</span>
          <h3 class="font-black font-display text-lg sm:text-xl text-ink leading-tight">Ubah Password</h3>
        </div>
      </div>
      <button type="button" onclick="closePasswordModal()" class="w-8 h-8 bg-white hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- FORM BODY -->
    <form id="form-change-password" onsubmit="executeChangePassword(event)" class="space-y-3">
      <div>
        <div class="text-xs font-semibold text-ink/70">Akun Anggota:</div>
        <div id="pwd-target-name" class="font-black text-ink text-sm bg-white p-2 brutal-border mt-1"></div>
      </div>

      <div>
        <label for="pwd-input" class="block text-xs font-black font-mono uppercase text-ink mb-1">Password Baru</label>
        <input type="password" id="pwd-input" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 bg-white brutal-input text-sm font-bold text-ink">
      </div>

      <div>
        <label for="pwd-confirm-input" class="block text-xs font-black font-mono uppercase text-ink mb-1">Konfirmasi Password Baru</label>
        <input type="password" id="pwd-confirm-input" name="password_confirmation" required minlength="6" placeholder="Ulangi password baru" class="w-full px-3 py-2 bg-white brutal-input text-sm font-bold text-ink">
      </div>

      <div class="pt-2 flex items-center gap-3">
        <button type="button" onclick="closePasswordModal()" class="w-1/2 py-2.5 bg-white hover:bg-cream brutal-btn text-xs font-mono font-black uppercase tracking-wider text-ink transition-all">
          Batal
        </button>
        <button type="submit" id="btn-submit-password" class="w-1/2 py-2.5 bg-brand hover:bg-brand-dark text-white brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all">
          <span>Simpan Password</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: UBAH PERAN ANGGOTA (NEOBRUTALIST) -->
<div id="modal-change-role" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closeRoleModal()">
  <div class="bg-cream brutal-card max-w-sm sm:max-w-md w-full p-5 sm:p-6 relative modal-box space-y-4">
    <!-- HEADER -->
    <div class="flex items-start justify-between pb-3 border-b-2 border-ink">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-mustard text-ink brutal-border flex items-center justify-center font-black text-lg shadow-[2px_2px_0px_#121614]">
          🎭
        </div>
        <div>
          <span class="text-[10px] font-black font-mono uppercase text-brand tracking-widest block">Struktur Keluarga</span>
          <h3 class="font-black font-display text-lg sm:text-xl text-ink leading-tight">Ubah Peran Anggota</h3>
        </div>
      </div>
      <button type="button" onclick="closeRoleModal()" class="w-8 h-8 bg-white hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- FORM BODY -->
    <form id="form-change-role" onsubmit="executeChangeRole(event)" class="space-y-3">
      <div>
        <div class="text-xs font-semibold text-ink/70">Akun Anggota:</div>
        <div id="role-target-name" class="font-black text-ink text-sm bg-white p-2 brutal-border mt-1"></div>
      </div>

      <div>
        <label for="role-select" class="block text-xs font-black font-mono uppercase text-ink mb-1">Pilih Peran Baru</label>
        <select id="role-select" name="role" required class="w-full px-3 py-2 bg-white brutal-input text-sm font-bold text-ink">
          <option value="Ibu">Ibu / Istri</option>
          <option value="Anak">Anak</option>
          <option value="Kakek/Nenek">Kakek / Nenek</option>
          <option value="ART">Asisten Rumah Tangga</option>
          <option value="Lainnya">Lainnya</option>
        </select>
      </div>

      <div class="p-2.5 bg-white brutal-border border-l-4 border-l-mustard text-[11px] font-mono text-ink/80">
        💡 Peran menentukan kategori tugas harian dan label anggota di ruang keluarga.
      </div>

      <div class="pt-2 flex items-center gap-3">
        <button type="button" onclick="closeRoleModal()" class="w-1/2 py-2.5 bg-white hover:bg-cream brutal-btn text-xs font-mono font-black uppercase tracking-wider text-ink transition-all">
          Batal
        </button>
        <button type="submit" id="btn-submit-role" class="w-1/2 py-2.5 bg-brand hover:bg-brand-dark text-white brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all">
          <span>Simpan Peran</span>
        </button>
      </div>
    </form>
  </div>
</div>
@endpush

@push('scripts')
<script>
  let currentInviteCode = "{{ $family->invite_code }}";
  let targetKickId = null;
  let targetPasswordId = null;
  let targetRoleId = null;

  function openPasswordModal(id, name) {
    targetPasswordId = id;
    const nameEl = document.getElementById('pwd-target-name');
    const modal = document.getElementById('modal-change-password');
    const form = document.getElementById('form-change-password');
    if (nameEl) nameEl.textContent = name;
    if (form) form.reset();
    if (modal) modal.classList.remove('hidden');
  }

  function closePasswordModal() {
    targetPasswordId = null;
    const modal = document.getElementById('modal-change-password');
    if (modal) modal.classList.add('hidden');
  }

  async function executeChangePassword(e) {
    e.preventDefault();
    if (!targetPasswordId) return;

    const pwd = document.getElementById('pwd-input').value;
    const pwdConfirm = document.getElementById('pwd-confirm-input').value;

    if (pwd !== pwdConfirm) {
      showToast('Konfirmasi password tidak cocok.', 'error');
      return;
    }

    const btn = document.getElementById('btn-submit-password');
    if (btn) {
      btn.disabled = true;
      btn.innerText = 'Menyimpan...';
    }

    try {
      const res = await fetch(`/members/${targetPasswordId}/password`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          password: pwd,
          password_confirmation: pwdConfirm,
        }),
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Gagal memperbarui password.'));
      }

      showToast(data.message, 'success');
      closePasswordModal();
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerText = 'Simpan Password';
      }
    }
  }

  function openRoleModal(id, name, currentRole) {
    targetRoleId = id;
    const nameEl = document.getElementById('role-target-name');
    const roleSelect = document.getElementById('role-select');
    const modal = document.getElementById('modal-change-role');

    if (nameEl) nameEl.textContent = name;
    if (roleSelect) roleSelect.value = currentRole;
    if (modal) modal.classList.remove('hidden');
  }

  function closeRoleModal() {
    targetRoleId = null;
    const modal = document.getElementById('modal-change-role');
    if (modal) modal.classList.add('hidden');
  }

  function applyRoleBadge(id, role) {
    const roleEl = document.getElementById(`member-role-${id}`);
    const badgeEl = document.getElementById(`member-badge-${id}`);
    const avatarEl = document.getElementById(`member-avatar-${id}`);

    if (roleEl) roleEl.textContent = role;

    const badgeText = role === 'Ayah' ? 'ADMIN UTAMA' : (role === 'Ibu' ? 'CO-ADMIN' : 'ANGGOTA');
    if (badgeEl) {
      badgeEl.textContent = badgeText;
      badgeEl.className = 'px-2 py-0.5 ' + (role === 'Ayah' ? 'bg-brand text-white' : (role === 'Ibu' ? 'bg-accent text-ink' : 'bg-white text-ink')) + ' brutal-border font-mono text-[10px] font-bold';
    }

    if (avatarEl && !avatarEl.querySelector('img')) {
      const emoji = role === 'Ayah' ? '👨' : (role === 'Ibu' ? '👩' : (role === 'Anak' ? '👦' : '👧'));
      avatarEl.innerHTML = `<span>${emoji}</span>`;
      const bg = role === 'Ayah' ? 'bg-brand text-white' : (role === 'Ibu' ? 'bg-accent text-ink' : (role === 'Anak' ? 'bg-sky text-ink' : 'bg-coral text-white'));
      avatarEl.className = `w-12 h-12 ${bg} brutal-border flex items-center justify-center text-2xl font-black overflow-hidden`;
    }
  }

  async function executeChangeRole(e) {
    e.preventDefault();
    if (!targetRoleId) return;

    const roleSelect = document.getElementById('role-select');
    const newRole = roleSelect ? roleSelect.value : '';

    const btn = document.getElementById('btn-submit-role');
    if (btn) {
      btn.disabled = true;
      btn.innerText = 'Menyimpan...';
    }

    try {
      const res = await fetch(`/members/${targetRoleId}/role`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ role: newRole }),
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Gagal memperbarui peran.'));
      }

      showToast(data.message, 'success');
      applyRoleBadge(targetRoleId, data.role);
      closeRoleModal();
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerText = 'Simpan Peran';
      }
    }
  }

  function copyDashboardInvite() {
    const inviteLink = "{{ url('/join') }}/" + currentInviteCode;
    navigator.clipboard.writeText(inviteLink);
    showToast('Link undangan berhasil disalin!');
    const btn = document.getElementById('btn-copy-dash');
    if (btn) {
      btn.innerText = '✓ Berhasil Disalin';
      btn.classList.add('bg-accent', 'text-ink');
      btn.classList.remove('bg-brand', 'text-white');
      setTimeout(() => {
        btn.innerText = '🔗 Salin Link Undangan';
        btn.classList.remove('bg-accent', 'text-ink');
        btn.classList.add('bg-brand', 'text-white');
      }, 2000);
    }
  }

  async function regenerateInviteCode() {
    if (!confirm('Apakah Anda yakin ingin mengacak ulang kode undangan keluarga? Kode lama akan hangus seketika dan tidak dapat digunakan lagi oleh penyusup.')) {
      return;
    }

    const btn = document.getElementById('btn-regen-invite');
    if (btn) {
      btn.disabled = true;
      btn.innerText = 'Memperbarui...';
    }

    try {
      const res = await fetch('{{ route("members.regenerate_invite") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Gagal memperbarui kode undangan.');
      }

      currentInviteCode = data.invite_code;
      const codeDisplay = document.getElementById('display-invite-code');
      if (codeDisplay) {
        codeDisplay.textContent = data.invite_code;
      }

      const waBtn = document.getElementById('btn-wa-invite');
      if (waBtn) {
        waBtn.href = `https://wa.me/?text=Halo!%20Ayo%20gabung%20ke%20ruang%20keluarga%20kita%20di%20FamKoji:%20${encodeURIComponent(data.invite_url)}`;
      }

      showToast(data.message, 'success');
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<span>🔄 Acak Ulang Kode</span>';
      }
    }
  }

  function openKickModal(id, name, role) {
    targetKickId = id;
    const nameEl = document.getElementById('kick-target-name');
    const roleEl = document.getElementById('kick-target-role');
    const modal = document.getElementById('confirm-kick-modal');

    if (nameEl) nameEl.textContent = name;
    if (roleEl) roleEl.textContent = role;
    if (modal) modal.classList.remove('hidden');
  }

  function closeKickModal() {
    targetKickId = null;
    const modal = document.getElementById('confirm-kick-modal');
    if (modal) modal.classList.add('hidden');
  }

  async function executeKickMember() {
    if (!targetKickId) return;

    const btn = document.getElementById('btn-confirm-kick');
    if (btn) {
      btn.disabled = true;
      btn.innerText = 'Mengeluarkan...';
    }

    try {
      const res = await fetch(`/members/${targetKickId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Gagal mengeluarkan anggota.');
      }

      showToast(data.message, 'success');
      
      const card = document.getElementById(`member-card-${targetKickId}`);
      if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        setTimeout(() => card.remove(), 300);
      }

      closeKickModal();
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerText = 'Ya, Keluarkan';
      }
    }
  }

  async function toggleFinanceAccess(memberId, el) {
    const isChecked = el.checked;
    const labelEl = document.getElementById(`label-finance-${memberId}`);
    try {
      const res = await fetch(`/members/${memberId}/toggle-finance`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ can_view_all_finances: isChecked }),
      });
      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Gagal mengubah izin');
      }
      if (labelEl) {
        labelEl.textContent = data.can_view_all_finances ? 'Penuh (Seluruh Keluarga)' : 'Terbatas (Milik Sendiri)';
      }
      showToast(data.message);
    } catch (err) {
      el.checked = !isChecked;
      showToast(err.message, 'error');
    }
  }

  // Handle realtime broadcast event for kicked/joined member
  window.addEventListener('DOMContentLoaded', () => {
    const userMeta = document.querySelector('meta[name="user-id"]');
    const currentUserId = userMeta ? Number(userMeta.getAttribute('content')) : null;

    const setupFamilyEcho = () => {
      if (!window.Echo) return;

      const familyMeta = document.querySelector('meta[name="family-id"]');
      const familyId = familyMeta ? familyMeta.getAttribute('content') : null;

      if (familyId) {
        const handleEvent = (e) => {
          if (e.action === 'member_kicked' && e.data) {
            if (currentUserId && Number(e.data.kicked_user_id) === currentUserId) {
              alert('Akun Anda telah dikeluarkan dari ruang keluarga ini oleh Admin.');
              window.location.href = '{{ route("login") }}';
              return;
            }

            const card = document.getElementById(`member-card-${e.data.kicked_user_id}`);
            if (card) {
              card.style.opacity = '0';
              card.style.transform = 'scale(0.9)';
              setTimeout(() => card.remove(), 300);
            }
          }

          if (e.action === 'member_joined') {
            if (typeof window.reloadCurrentModuleData === 'function') {
              window.reloadCurrentModuleData(e);
            }
          }

          if (e.action === 'role_changed' && e.data) {
            applyRoleBadge(e.data.target_user_id, e.data.role);
          }
        };

        window.Echo.private(`family.${familyId}`)
          .listen('FamilyMemberEvent', handleEvent)
          .listen('.FamilyMemberEvent', handleEvent);
      }
    };

    if (window.Echo) {
      setupFamilyEcho();
    } else {
      setTimeout(setupFamilyEcho, 500);
    }
  });
</script>
@endpush
