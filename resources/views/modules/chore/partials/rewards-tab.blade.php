@php
  $user = auth()->user() ?? ($user ?? null);
  $isParent = $user && method_exists($user, 'isParent') ? $user->isParent() : false;
  $claims = $claims ?? collect();
  $rewards = $rewards ?? collect();
  $pendingClaims = $claims->where('status', 'pending');
  $historyClaims = $claims->where('status', '!=', 'pending');
@endphp

<div class="space-y-6">

  <!-- REWARD HERO & SUMMARY -->
  <div class="bg-brand-soft brutal-card p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
      <div class="w-14 h-14 bg-accent text-ink brutal-border flex items-center justify-center text-3xl shadow-[3px_3px_0px_#121614] flex-shrink-0">
        ⭐
      </div>
      <div>
        <div class="text-[10px] font-mono font-black uppercase tracking-wider text-brand">Celengan Poin Kamu</div>
        <h3 class="text-2xl font-black font-display text-ink flex items-center gap-2">
          <span id="rewards-user-points-display">{{ number_format($user->points ?? 0) }}</span>
          <span class="text-sm font-mono font-bold text-ink/70">Poin Tersedia</span>
        </h3>
        <p class="text-xs text-ink/70 mt-0.5">Tukarkan poin hasil tugas rumah tanggamu dengan berbagai hadiah seru!</p>
      </div>
    </div>

    @if($isParent)
      <button 
        type="button" 
        onclick="openRewardModal()" 
        class="w-full sm:w-auto px-4 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2"
      >
        <span>+ Tambah Hadiah Baru</span>
      </button>
    @endif
  </div>

  <!-- PENDING CLAIMS REVIEW SECTION -->
  @if($pendingClaims->count() > 0)
    <div class="bg-white brutal-card p-4 sm:p-6 border-l-8 border-l-accent space-y-3">
      <div class="flex items-center justify-between pb-2.5 border-b-2 border-ink">
        <div class="flex items-center gap-2">
          <span class="text-lg animate-bounce">⏳</span>
          <h4 class="font-black font-display text-base text-ink">Permintaan Klaim Menunggu Konfirmasi</h4>
        </div>
        <span class="px-2.5 py-0.5 bg-accent brutal-border font-mono font-black text-xs">
          {{ $pendingClaims->count() }} Permintaan
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
        @foreach($pendingClaims as $claim)
          <div class="bg-cream brutal-border p-3.5 flex flex-col justify-between gap-3 shadow-[2px_2px_0px_#121614]" id="claim-card-{{ $claim->id }}">
            <div class="flex items-start justify-between gap-2">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-10 h-10 bg-white brutal-border flex items-center justify-center text-xl flex-shrink-0">
                  {{ $claim->reward->icon ?? '🎁' }}
                </div>
                <div class="min-w-0">
                  <div class="font-black text-xs text-ink truncate">{{ $claim->reward->title ?? 'Hadiah' }}</div>
                  <div class="text-[11px] font-mono text-ink/70 flex items-center gap-1.5">
                    <span>👤 {{ $claim->user->name ?? 'Anggota' }}</span>
                    <span>&bull;</span>
                    <span class="font-bold text-coral">⭐ {{ number_format($claim->points_spent) }} Poin</span>
                  </div>
                  <div class="text-[10px] font-mono text-ink/50 mt-0.5">
                    Diklaim {{ $claim->created_at->diffForHumans() }}
                  </div>
                </div>
              </div>

              <span class="px-2 py-0.5 bg-white brutal-border font-mono font-bold text-[10px] text-coral whitespace-nowrap">
                Pending
              </span>
            </div>

            @if($isParent)
              <div class="pt-2 border-t border-ink/20 flex items-center justify-end gap-2">
                <button 
                  type="button" 
                  onclick="openRejectModal({{ $claim->id }})" 
                  class="px-2.5 py-1 bg-white hover:bg-coral hover:text-white brutal-border text-[11px] font-mono font-black uppercase transition-colors"
                  title="Tolak klaim dan kembalikan poin"
                >
                  ✕ Tolak
                </button>
                <button 
                  type="button" 
                  onclick="openApproveModal({{ $claim->id }}, '{{ addslashes($claim->reward->title ?? 'Hadiah') }}', '{{ addslashes($claim->user->name ?? 'Anggota') }}', {{ (int)$claim->points_spent }})" 
                  class="px-3 py-1 bg-brand text-white hover:bg-brand-dark brutal-border text-[11px] font-mono font-black uppercase transition-all shadow-[1px_1px_0px_#121614] flex items-center gap-1"
                  title="Serahkan hadiah dan setujui klaim"
                >
                  <span>✓ Serahkan Hadiah</span>
                </button>
              </div>
            @else
              <div class="text-[10px] font-mono font-bold text-ink/60 bg-white brutal-border p-1.5 text-center">
                Menunggu Orang Tua menyerahkan hadiahmu 😊
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endif

  <!-- REWARD CATALOG GRID WITH SERVERSIDE DATATABLES -->
  <div class="space-y-4">
    <div class="flex items-center justify-between pb-2 border-b-2 border-ink">
      <div>
        <h3 class="font-black font-display text-lg text-ink">Katalog Hadiah Tersedia</h3>
        <p class="text-xs text-ink/70">Pilih hadiah yang ingin kamu tukarkan dengan poin tugasmu.</p>
      </div>
      @if($isParent)
        <button 
          type="button" 
          onclick="openRewardModal()" 
          class="hidden sm:inline-flex px-3 py-1 bg-brand text-white brutal-btn text-xs font-mono font-black uppercase tracking-wider items-center gap-1.5"
        >
          <span>+ Tambah Hadiah</span>
        </button>
      @endif
    </div>

    <!-- SERVER-SIDE REWARDS DATATABLES WRAPPER -->
    <div class="card-datatable rewards-datatable-wrapper">
      <table id="rewards-datatable" class="w-full">
        <thead>
          <tr>
            <th>Hadiah</th>
          </tr>
        </thead>
        <tbody>
          <!-- Populated by DataTables Server-Side -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- CLAIM HISTORY SECTION -->
  @if($historyClaims->count() > 0)
    <div class="bg-white brutal-card p-4 sm:p-6 space-y-3">
      <div class="flex items-center justify-between pb-2.5 border-b-2 border-ink">
        <h4 class="font-black font-display text-base text-ink">Riwayat Penukaran Hadiah</h4>
        <span class="text-xs font-mono font-bold text-ink/70">
          {{ $historyClaims->count() }} Riwayat Terakhir
        </span>
      </div>

      <div class="divide-y divide-ink/10">
        @foreach($historyClaims as $history)
          <div class="py-3 flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3 min-w-0">
              <span class="text-xl flex-shrink-0">{{ $history->reward->icon ?? '🎁' }}</span>
              <div class="min-w-0">
                <div class="font-bold text-ink truncate">{{ $history->reward->title ?? 'Hadiah' }}</div>
                <div class="text-[11px] font-mono text-ink/60">
                  Oleh <strong>{{ $history->user->name ?? 'Anggota' }}</strong> &bull; ⭐ {{ number_format($history->points_spent) }} Poin &bull; {{ $history->created_at->format('d M Y, H:i') }}
                </div>
                @if($history->status === 'rejected' && $history->notes)
                  <div class="text-[11px] text-coral font-medium mt-0.5">
                    Alasan: "{{ $history->notes }}"
                  </div>
                @endif
              </div>
            </div>

            <div class="flex-shrink-0">
              @if($history->status === 'approved')
                <span class="px-2.5 py-1 bg-brand text-white brutal-border font-mono font-black text-[10px] uppercase">
                  ✓ Diserahkan
                </span>
              @elseif($history->status === 'rejected')
                <span class="px-2.5 py-1 bg-coral text-white brutal-border font-mono font-black text-[10px] uppercase">
                  ✕ Ditolak
                </span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

</div>
