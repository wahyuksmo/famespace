@extends('layouts.main')

@section('title', 'Anggota Keluarga — famespace')

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
      <div class="text-2xl font-black font-mono tracking-widest text-brand mt-0.5">{{ $family->invite_code }}</div>
      <div class="text-xs font-semibold text-ink/70">Bagikan kode ini ke keluarga yang ingin join via halaman Registrasi.</div>
    </div>
    <div class="flex items-center gap-2">
      <a href="https://wa.me/?text=Halo!%20Ayo%20gabung%20ke%20ruang%20keluarga%20kita%20di%20famespace:%20{{ url('/join/' . $family->invite_code) }}" target="_blank" class="px-3.5 py-2 bg-brand text-white brutal-btn text-xs font-bold">
        💬 Kirim via WhatsApp
      </a>
    </div>
  </div>

  <!-- MEMBER CARDS GRID -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
        <div class="bg-white brutal-card p-5 space-y-3">
          <div class="flex items-center justify-between">
            <div class="w-12 h-12 {{ $bg }} brutal-border flex items-center justify-center text-2xl font-black">
              {{ $emoji }}
            </div>
            <span class="px-2 py-0.5 {{ $m->role === 'Ayah' ? 'bg-brand text-white' : ($m->role === 'Ibu' ? 'bg-accent text-ink' : 'bg-white text-ink') }} brutal-border font-mono text-[10px] font-bold">
              {{ $badgeText }}
            </span>
          </div>
          <div>
            <div class="font-black text-base text-ink">{{ $m->name }}</div>
            <div class="text-xs font-mono font-bold text-brand">{{ $m->role ?? 'Anggota' }}</div>
            <div class="text-[11px] text-ink/60 mt-1 truncate">{{ $m->email }}</div>
          </div>
          <div class="pt-2 border-t-2 border-ink flex items-center justify-between text-xs font-black">
            <span>Poin Chores:</span>
            <span class="text-brand font-mono">{{ $m->points }} Poin</span>
          </div>

          @if($m->isAyah())
            <div class="pt-2 border-t-2 border-ink flex items-center justify-between text-xs font-black">
              <span>Data Keuangan:</span>
              <span class="px-2 py-0.5 bg-brand text-white brutal-border font-mono text-[9px] uppercase font-bold">Penuh (Ayah)</span>
            </div>
          @elseif($user->isAyah())
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

@push('scripts')
<script>
  function copyDashboardInvite() {
    const inviteLink = "{{ url('/join/' . $family->invite_code) }}";
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
</script>
@endpush
