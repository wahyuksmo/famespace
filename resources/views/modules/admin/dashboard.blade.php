@extends('layouts.admin')

@section('title', 'Dashboard Super Admin — FamSpace')

@section('content')
<div class="space-y-6">

  <!-- TOP TITLE -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b-3 border-ink">
    <div>
      <span class="text-xs font-mono font-black uppercase text-brand tracking-wider">Pusat Kendali Utama</span>
      <h1 class="text-2xl sm:text-3xl font-black font-display text-ink">Dashboard Super Administrator</h1>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.payments.index', ['status' => 'paid']) }}" class="px-4 py-2 bg-accent text-ink brutal-btn font-mono font-black text-xs uppercase flex items-center gap-1.5">
        <span>⚡</span> Perlu Verifikasi ({{ $pendingAuditCount }})
      </a>
    </div>
  </div>

  <!-- STAT METRICS CARDS -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- CARD 1: TOTAL ANGGOTA & KELUARGA -->
    <div class="bg-white brutal-card p-4">
      <div class="flex items-center justify-between text-ink/70 mb-2">
        <span class="text-[11px] font-mono font-black uppercase">Total Anggota</span>
        <span class="text-lg">👨‍👩‍👧‍👦</span>
      </div>
      <div class="text-3xl font-black font-display text-ink">{{ number_format($totalUsers) }}</div>
      <div class="text-[11px] font-mono font-bold text-ink/60 mt-1">
        Tersebar di <span class="text-ink font-black">{{ $totalFamilies }}</span> Keluarga
      </div>
    </div>

    <!-- CARD 2: PRO / LIFETIME RATIO -->
    <div class="bg-white brutal-card p-4">
      <div class="flex items-center justify-between text-ink/70 mb-2">
        <span class="text-[11px] font-mono font-black uppercase">Pelanggan Aktif</span>
        <span class="text-lg">👑</span>
      </div>
      <div class="text-3xl font-black font-display text-brand">{{ number_format($totalPro + $totalLifetime) }}</div>
      <div class="text-[11px] font-mono font-bold text-ink/60 mt-1 flex items-center gap-2">
        <span>Pro: <b>{{ $totalPro }}</b></span> &bull; 
        <span>Lifetime: <b>{{ $totalLifetime }}</b></span> &bull; 
        <span>Free: <b>{{ $totalFree }}</b></span>
      </div>
    </div>

    <!-- CARD 3: TOTAL OMZET TERVERIFIKASI -->
    <div class="bg-white brutal-card p-4">
      <div class="flex items-center justify-between text-ink/70 mb-2">
        <span class="text-[11px] font-mono font-black uppercase">Omzet Terverifikasi</span>
        <span class="text-lg">💰</span>
      </div>
      <div class="text-2xl sm:text-3xl font-black font-display text-ink">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
      <div class="text-[11px] font-mono font-bold text-brand mt-1">
        Dari mutasi & pembayaran QRIS
      </div>
    </div>

    <!-- CARD 4: PENDING AUDIT -->
    <div class="bg-white brutal-card p-4 {{ $pendingAuditCount > 0 ? 'bg-amber-100' : '' }}">
      <div class="flex items-center justify-between text-ink/70 mb-2">
        <span class="text-[11px] font-mono font-black uppercase">Menunggu Audit Mutasi</span>
        <span class="text-lg">🔍</span>
      </div>
      <div class="text-3xl font-black font-display {{ $pendingAuditCount > 0 ? 'text-coral' : 'text-ink' }}">
        {{ number_format($pendingAuditCount) }}
      </div>
      <div class="text-[11px] font-mono font-bold text-ink/60 mt-1">
        Transaksi baru yang belum dicek
      </div>
    </div>

  </div>

  <!-- MAIN SECTIONS (2 COLUMNS) -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- RECENT TRANSACTIONS (2 COLS) -->
    <div class="lg:col-span-2 bg-white brutal-card p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
        <div class="flex items-center gap-2">
          <span class="text-base">💳</span>
          <h2 class="font-black font-display text-lg text-ink">Transaksi QRIS Terbaru</h2>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="text-xs font-mono font-bold text-brand hover:underline">
          Lihat Semua &rarr;
        </a>
      </div>

      @if($recentPayments->isEmpty())
        <div class="py-8 text-center text-ink/60 font-mono text-xs">
          Belum ada transaksi pembayaran.
        </div>
      @else
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs font-mono">
            <thead>
              <tr class="border-b-2 border-ink text-[11px] font-black uppercase text-ink/70">
                <th class="py-2">Invoice / Waktu</th>
                <th class="py-2">Keluarga & User</th>
                <th class="py-2">Paket</th>
                <th class="py-2">Nominal</th>
                <th class="py-2">Status</th>
                <th class="py-2 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-ink/20">
              @foreach($recentPayments as $payment)
                <tr>
                  <td class="py-3">
                    <span class="font-black text-ink block">{{ $payment->invoice_number }}</span>
                    <span class="text-[10px] text-ink/60 block">{{ $payment->transfer_time ?: $payment->created_at->format('d/m H:i') }}</span>
                  </td>
                  <td class="py-3">
                    <span class="font-bold text-ink block">{{ $payment->family?->name ?? '—' }}</span>
                    <span class="text-[10px] text-ink/60 block">{{ $payment->user?->name ?? '—' }}</span>
                  </td>
                  <td class="py-3">
                    <span class="px-2 py-0.5 bg-cream brutal-border text-[10px] font-black">
                      {{ $payment->plan?->name ?? 'Pro' }}
                    </span>
                  </td>
                  <td class="py-3 font-black text-ink">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                  </td>
                  <td class="py-3">
                    @if($payment->status === 'verified')
                      <span class="px-2 py-0.5 bg-brand text-white font-black text-[9px] brutal-border">VERIFIED</span>
                    @elseif($payment->status === 'reverted')
                      <span class="px-2 py-0.5 bg-coral text-white font-black text-[9px] brutal-border">REVERTED</span>
                    @else
                      <span class="px-2 py-0.5 bg-accent text-ink font-black text-[9px] brutal-border">PAID / AUDIT</span>
                    @endif
                  </td>
                  <td class="py-3 text-right">
                    <a href="{{ route('admin.payments.index', ['search' => $payment->invoice_number]) }}" class="px-2 py-1 bg-cream hover:bg-ink hover:text-white brutal-border font-black text-[10px] transition-colors">
                      Detail
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <!-- RECENT REGISTERED FAMILIES (1 COL) -->
    <div class="bg-white brutal-card p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
        <div class="flex items-center gap-2">
          <span class="text-base">🏡</span>
          <h2 class="font-black font-display text-lg text-ink">Keluarga Baru</h2>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-xs font-mono font-bold text-brand hover:underline">
          Semua &rarr;
        </a>
      </div>

      <div class="divide-y divide-ink/20">
        @forelse($recentFamilies as $f)
          <div class="py-3 flex items-center justify-between">
            <div>
              <span class="font-bold text-xs text-ink block">{{ $f->name }}</span>
              <span class="text-[10px] font-mono text-ink/60">{{ $f->users_count }} Anggota &bull; Kode: {{ $f->invite_code }}</span>
            </div>
            <div>
              @php
                $isPro = $f->subscription && $f->subscription->plan && $f->subscription->plan->isPro() && $f->subscription->expires_at->isFuture();
              @endphp
              @if($isPro)
                <span class="px-1.5 py-0.5 bg-accent text-ink text-[9px] font-mono font-black brutal-border">PRO</span>
              @else
                <span class="px-1.5 py-0.5 bg-cream text-ink text-[9px] font-mono font-black brutal-border">FREE</span>
              @endif
            </div>
          </div>
        @empty
          <div class="py-6 text-center text-ink/60 font-mono text-xs">
            Belum ada keluarga terdaftar.
          </div>
        @endforelse
      </div>
    </div>

  </div>

</div>
@endsection
