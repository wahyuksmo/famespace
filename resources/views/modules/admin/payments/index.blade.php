@extends('layouts.admin')

@section('title', 'Manajemen Transaksi & QRIS — FamSpace Super Admin')

@section('content')
<div class="space-y-6">

  <!-- HEADER -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b-3 border-ink">
    <div>
      <span class="text-xs font-mono font-black uppercase text-brand tracking-wider">Verifikasi Mutasi & Audit</span>
      <h1 class="text-2xl sm:text-3xl font-black font-display text-ink">Daftar Transaksi QRIS</h1>
    </div>
  </div>

  <!-- STATUS TABS & SEARCH BAR -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    
    <!-- TABS -->
    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('admin.payments.index', ['status' => 'all', 'search' => $search]) }}" class="px-3 py-1.5 font-mono text-xs font-black uppercase brutal-border transition-all {{ ($status === 'all' || !$status) ? 'bg-ink text-white' : 'bg-white text-ink hover:bg-cream' }}">
        Semua ({{ $stats['all'] }})
      </a>
      <a href="{{ route('admin.payments.index', ['status' => 'paid', 'search' => $search]) }}" class="px-3 py-1.5 font-mono text-xs font-black uppercase brutal-border transition-all {{ $status === 'paid' ? 'bg-accent text-ink' : 'bg-white text-ink hover:bg-cream' }}">
        ⚡ Perlu Audit ({{ $stats['paid'] }})
      </a>
      <a href="{{ route('admin.payments.index', ['status' => 'verified', 'search' => $search]) }}" class="px-3 py-1.5 font-mono text-xs font-black uppercase brutal-border transition-all {{ $status === 'verified' ? 'bg-brand text-white' : 'bg-white text-ink hover:bg-cream' }}">
        ✅ Terverifikasi ({{ $stats['verified'] }})
      </a>
      <a href="{{ route('admin.payments.index', ['status' => 'reverted', 'search' => $search]) }}" class="px-3 py-1.5 font-mono text-xs font-black uppercase brutal-border transition-all {{ $status === 'reverted' ? 'bg-coral text-white' : 'bg-white text-ink hover:bg-cream' }}">
        ⛔ Di-Revert ({{ $stats['reverted'] }})
      </a>
    </div>

    <!-- SEARCH FORM -->
    <form action="{{ route('admin.payments.index') }}" method="GET" class="flex items-center gap-2">
      <input type="hidden" name="status" value="{{ $status }}">
      <input type="text" name="search" value="{{ $search }}" placeholder="Cari invoice / user / keluarga..." class="p-2 text-xs font-mono font-bold bg-white brutal-border focus:bg-cream outline-none w-56 sm:w-64">
      <button type="submit" class="px-3 py-2 bg-ink text-white brutal-border font-mono font-black text-xs uppercase hover:bg-accent hover:text-ink transition-colors">
        Cari
      </button>
      @if($search)
        <a href="{{ route('admin.payments.index', ['status' => $status]) }}" class="p-2 bg-white brutal-border font-mono font-black text-xs hover:bg-coral hover:text-white">✕</a>
      @endif
    </form>

  </div>

  <!-- PAYMENTS TABLE -->
  <div class="bg-white brutal-card overflow-hidden">
    @if($payments->isEmpty())
      <div class="py-12 text-center font-mono text-xs text-ink/60">
        Tidak ada transaksi yang cocok dengan kriteria filter.
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-cream border-b-2 border-ink text-ink font-black uppercase text-[11px]">
            <tr>
              <th class="p-3">Invoice & Waktu</th>
              <th class="p-3">User & Keluarga</th>
              <th class="p-3">Paket</th>
              <th class="p-3">Nominal</th>
              <th class="p-3">Bukti / Struk</th>
              <th class="p-3">Status</th>
              <th class="p-3">Catatan Admin</th>
              <th class="p-3 text-right">Aksi Audit</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-ink/20">
            @foreach($payments as $p)
              <tr class="hover:bg-cream/40 transition-colors">
                
                <!-- INVOICE & WAKTU -->
                <td class="p-3">
                  <div class="font-black text-ink">{{ $p->invoice_number }}</div>
                  <div class="text-[10px] text-ink/70 mt-0.5">
                    <b>Jam Klaim:</b> {{ $p->transfer_time ?: '—' }}
                  </div>
                  <div class="text-[10px] text-ink/50">
                    {{ $p->created_at->format('d M Y, H:i') }}
                  </div>
                </td>

                <!-- USER & KELUARGA -->
                <td class="p-3">
                  <div class="font-bold text-ink">{{ $p->user?->name ?? 'User Terhapus' }}</div>
                  <div class="text-[10px] text-ink/70">{{ $p->user?->email ?? '—' }}</div>
                  <div class="mt-1">
                    <span class="px-1.5 py-0.5 bg-cream brutal-border text-[9px] font-bold text-ink">
                      🏡 {{ $p->family?->name ?? 'Tanpa Keluarga' }}
                    </span>
                  </div>
                </td>

                <!-- PAKET -->
                <td class="p-3">
                  <span class="px-2 py-1 bg-white brutal-border font-black text-[10px]">
                    {{ $p->plan?->name ?? 'Pro Plan' }}
                  </span>
                </td>

                <!-- NOMINAL -->
                <td class="p-3 font-black text-ink text-sm">
                  Rp {{ number_format($p->amount, 0, ',', '.') }}
                </td>

                <!-- BUKTI / STRUK -->
                <td class="p-3">
                  @if($p->proof_image)
                    <button type="button" onclick="openProofModal('{{ asset('storage/' . $p->proof_image) }}', '{{ $p->invoice_number }}')" class="px-2 py-1 bg-accent brutal-border text-[10px] font-black flex items-center gap-1 hover:bg-brand hover:text-white transition-colors">
                      <span>🖼️</span> Lihat Struk
                    </button>
                  @else
                    <span class="text-[10px] text-ink/50 italic">Tidak ada struk</span>
                  @endif
                </td>

                <!-- STATUS -->
                <td class="p-3">
                  @if($p->status === 'verified')
                    <span class="px-2 py-0.5 bg-brand text-white font-black text-[10px] brutal-border">
                      ✅ VERIFIED
                    </span>
                    <div class="text-[9px] text-ink/60 mt-1">
                      Oleh: {{ $p->verifier?->name ?? 'Admin' }}
                    </div>
                  @elseif($p->status === 'reverted')
                    <span class="px-2 py-0.5 bg-coral text-white font-black text-[10px] brutal-border">
                      ⛔ REVERTED (FREE)
                    </span>
                    <div class="text-[9px] text-ink/60 mt-1">
                      Dibatalkan manual
                    </div>
                  @else
                    <span class="px-2 py-0.5 bg-accent text-ink font-black text-[10px] brutal-border">
                      ⚡ PAID (PERLU AUDIT)
                    </span>
                    <div class="text-[9px] text-ink/60 mt-1">
                      Akun aktif Pro
                    </div>
                  @endif
                </td>

                <!-- CATATAN ADMIN -->
                <td class="p-3 max-w-[200px]">
                  <span class="text-[11px] text-ink/80 block break-words">
                    {{ $p->admin_notes ?: '—' }}
                  </span>
                </td>

                <!-- AKSI -->
                <td class="p-3 text-right space-y-1">
                  @if($p->status !== 'verified')
                    <form action="{{ route('admin.payments.verify', $p->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Konfirmasi bahwa pembayaran Rp {{ number_format($p->amount, 0, ',', '.') }} sudah masuk ke rekening?')">
                      @csrf
                      <button type="submit" class="px-2.5 py-1 bg-brand hover:bg-ink text-white font-black text-[10px] uppercase brutal-border transition-colors">
                        ✅ Verifikasi
                      </button>
                    </form>
                  @endif

                  @if($p->status !== 'reverted')
                    <button type="button" onclick="openRevertModal('{{ $p->id }}', '{{ $p->invoice_number }}', '{{ $p->family?->name }}')" class="px-2.5 py-1 bg-coral hover:bg-ink text-white font-black text-[10px] uppercase brutal-border transition-colors">
                      ⛔ Revert ke Free
                    </button>
                  @endif
                </td>

              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div class="p-4 border-t-2 border-ink bg-cream/30">
        {{ $payments->links() }}
      </div>
    @endif
  </div>

</div>

<!-- MODAL: PREVIEW STRUK BUKTI TRANSFER -->
<div id="proof-modal" class="fixed inset-0 bg-ink/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-cream brutal-card max-w-md w-full p-4 relative modal-box space-y-3">
    <div class="flex items-center justify-between pb-2 border-b-2 border-ink">
      <span class="text-xs font-black font-mono uppercase text-ink" id="proof-modal-title">Struk Bukti Transfer</span>
      <button type="button" onclick="closeProofModal()" class="w-6 h-6 bg-white brutal-border font-black flex items-center justify-center hover:bg-coral hover:text-white">✕</button>
    </div>
    <div class="text-center p-2 bg-white brutal-border max-h-[70vh] overflow-auto">
      <img id="proof-modal-img" src="" alt="Bukti Transfer" class="max-w-full mx-auto">
    </div>
  </div>
</div>

<!-- MODAL: REVERT KE FREE TIER -->
<div id="revert-modal" class="fixed inset-0 bg-ink/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-cream brutal-card max-w-md w-full p-5 relative modal-box space-y-4">
    <div class="flex items-center justify-between pb-2 border-b-2 border-ink">
      <div class="flex items-center gap-2">
        <span class="text-lg">⚠️</span>
        <h3 class="font-black font-display text-lg text-ink">Revert ke Free Tier</h3>
      </div>
      <button type="button" onclick="closeRevertModal()" class="w-6 h-6 bg-white brutal-border font-black flex items-center justify-center hover:bg-coral hover:text-white">✕</button>
    </div>

    <p class="text-xs font-bold text-ink">
      Apakah transaksi pada invoice <b id="revert-invoice-num" class="font-mono text-coral"></b> oleh keluarga <b id="revert-family-name"></b> tidak ditemukan di mutasi rekening?
    </p>

    <div class="p-2.5 bg-amber-100 brutal-border text-[11px] font-medium text-ink">
      Tindakan ini akan <b>langsung mencabut fitur Pro</b> keluarga tersebut dan mengembalikan status ke Free Tier.
    </div>

    <form id="revert-form" method="POST" action="">
      @csrf
      <div class="space-y-1 mb-4">
        <label class="block text-[11px] font-black font-mono uppercase text-ink">
          Alasan Penolakan / Catatan Admin:
        </label>
        <textarea name="admin_notes" rows="3" class="w-full text-xs font-mono p-2 bg-white brutal-border outline-none" placeholder="Contoh: Mutasi jam 14:35 tidak ada dana masuk sebesar Rp 20.000.">Dana fiktif / tidak ditemukan pada mutasi rekening bank.</textarea>
      </div>

      <div class="flex items-center justify-end gap-2">
        <button type="button" onclick="closeRevertModal()" class="px-3 py-1.5 bg-white brutal-border font-mono font-black text-xs uppercase hover:bg-cream">
          Batal
        </button>
        <button type="submit" class="px-4 py-1.5 bg-coral hover:bg-ink text-white font-mono font-black text-xs uppercase brutal-border">
          ⛔ Eksekusi Revert Sekarang
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  function openProofModal(imgSrc, invoiceNum) {
    $('#proof-modal-img').attr('src', imgSrc);
    $('#proof-modal-title').text('Struk Transfer: ' + invoiceNum);
    $('#proof-modal').removeClass('hidden');
  }

  function closeProofModal() {
    $('#proof-modal').addClass('hidden');
  }

  function openRevertModal(paymentId, invoiceNum, familyName) {
    $('#revert-form').attr('action', '/admin/payments/' + paymentId + '/revert');
    $('#revert-invoice-num').text(invoiceNum);
    $('#revert-family-name').text(familyName || '—');
    $('#revert-modal').removeClass('hidden');
  }

  function closeRevertModal() {
    $('#revert-modal').addClass('hidden');
  }
</script>
@endpush
@endsection
