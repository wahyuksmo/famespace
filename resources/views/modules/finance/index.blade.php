@extends('layouts.main')

@section('title', 'Kas & Tabungan — ' . ($family->name ?? 'FamKoji') . ' — FamKoji')

@section('content')
<!-- TAB 3: KAS & TABUNGAN KELUARGA -->
<div id="view-finance" class="space-y-6">
  
  @php
    $family = $family ?? (auth()->user() ? auth()->user()->family : null);
    $familyName = $family ? $family->name : 'Keluarga';
    $balance = (float) ($finance['balance'] ?? 0);
    $income = (float) ($finance['income'] ?? 0);
    $expense = (float) ($finance['expense'] ?? 0);
    $saving = (float) ($finance['saving'] ?? 0);
    $budget = (float) ($family ? $family->monthly_budget : 0);
    $budgetPct = $budget > 0 ? round(($expense / $budget) * 100, 1) : 0;
    $currentMonthName = now()->locale('id')->isoFormat('MMMM Y');
    $categoryBudgets = $categoryBudgets ?? collect();
    $goals = $goals ?? collect();
  @endphp

  @if($canViewAllFinances ?? true)
  <!-- HERO BALANCE CARD NEOBRUTALIS INTERAKTIF -->
  <div class="bg-gradient-to-br from-brand-soft to-cream brutal-card p-5 sm:p-7 relative overflow-hidden transition-all duration-300 hover:shadow-brutal-lg">
    <div class="absolute -right-6 -bottom-6 text-brand/10 text-9xl font-black select-none pointer-events-none font-mono">
      Rp
    </div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      <!-- Balance Details -->
      <div class="space-y-1.5">
        <div class="flex items-center gap-2.5 flex-wrap">
          <span class="px-2.5 py-0.5 bg-brand text-white brutal-border font-mono text-[10px] font-black uppercase tracking-wider">
            🏛️ Total Saldo Kas Tersedia
          </span>
          <button 
            type="button" 
            id="btn-toggle-eye" 
            onclick="toggleBalancePrivacy()" 
            class="px-2 py-0.5 bg-white hover:bg-accent text-ink brutal-border text-xs flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all cursor-pointer"
            title="Sembunyikan/Tampilkan nominal saldo"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span class="text-[10px] font-mono font-bold">Privasi</span>
          </button>
        </div>

        <div class="flex items-baseline gap-2">
          <div 
            id="stat-total-balance" 
            data-real-balance="Rp {{ number_format($balance, 0, ',', '.') }}" 
            class="text-3xl sm:text-4xl font-black font-mono text-ink tracking-tight select-all transition-all duration-300"
            title="Rp {{ number_format($balance, 0, ',', '.') }}"
          >
            Rp {{ number_format($balance, 0, ',', '.') }}
          </div>
        </div>

        <p class="text-xs font-medium text-ink/70">
          Saldo riil siap alokasi per <span class="font-bold text-ink">{{ $currentMonthName }}</span>
        </p>
      </div>

      <!-- Desktop-only Quick Action (Mobile menggunakan tombol (+) tengah di bottom navigation) -->
      <div class="hidden lg:flex items-center gap-2 flex-shrink-0">
        <button 
          type="button" 
          onclick="openFinanceModal()" 
          class="px-4 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-brutal-sm hover:bg-brand-dark transition-all active:translate-x-[1px] active:translate-y-[1px]"
        >
          <span>✍️ + Catat Kas</span>
        </button>
      </div>
    </div>

    <!-- 3 SLIM METRICS PILLS (INTERACTIVE HOVER) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 sm:pt-5 mt-4 sm:mt-5 border-t-2 border-ink/15">
      <div class="bg-white/90 p-3 brutal-border flex items-center justify-between gap-2 shadow-brutal-sm hover:-translate-y-0.5 transition-all">
        <div class="min-w-0">
          <div class="text-[10px] font-mono font-black uppercase text-brand flex items-center gap-1">
            <span>↗</span> Pemasukan Bulan Ini
          </div>
          <div id="stat-total-income" class="text-base sm:text-lg font-black font-mono text-brand truncate" title="+ Rp {{ number_format($income, 0, ',', '.') }}">
            + Rp {{ number_format($income, 0, ',', '.') }}
          </div>
        </div>
        <span class="text-xl flex-shrink-0">💰</span>
      </div>

      <div class="bg-white/90 p-3 brutal-border flex items-center justify-between gap-2 shadow-brutal-sm hover:-translate-y-0.5 transition-all">
        <div class="min-w-0">
          <div class="text-[10px] font-mono font-black uppercase text-coral flex items-center gap-1">
            <span>↘</span> Pengeluaran Bulan Ini
          </div>
          <div id="stat-total-expense" class="text-base sm:text-lg font-black font-mono text-coral truncate" title="- Rp {{ number_format($expense, 0, ',', '.') }}">
            - Rp {{ number_format($expense, 0, ',', '.') }}
          </div>
          <div class="text-[9px] font-mono font-bold text-ink/60 truncate">
            {{ $budgetPct }}% target bulanan
          </div>
        </div>
        <span class="text-xl flex-shrink-0">💸</span>
      </div>

      <div class="bg-white/90 p-3 brutal-border flex items-center justify-between gap-2 shadow-brutal-sm hover:-translate-y-0.5 transition-all">
        <div class="min-w-0">
          <div class="text-[10px] font-mono font-black uppercase text-emerald-800 flex items-center gap-1">
            <span>🛡️</span> Tabungan Tersimpan
          </div>
          <div id="stat-total-saving" class="text-base sm:text-lg font-black font-mono text-emerald-700 truncate" title="Rp {{ number_format($saving, 0, ',', '.') }}">
            Rp {{ number_format($saving, 0, ',', '.') }}
          </div>
          <div class="text-[9px] font-mono font-bold text-emerald-800 truncate">
            {{ count($goals) }} Celengan Impian Aktif
          </div>
        </div>
        <span class="text-xl flex-shrink-0">🐷</span>
      </div>
    </div>
  </div>
  @else
  <!-- Hero Card Mode Terbatas -->
  <div class="bg-white brutal-card p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <span class="px-2 py-0.5 bg-accent text-ink brutal-border text-[10px] font-black font-mono">
        🔒 Mode Terbatas: Data Pribadi
      </span>
      <h2 class="text-xl font-black font-display text-ink mt-1">Catatan Pengeluaran Pribadi</h2>
      <p class="text-xs font-medium text-ink/70">Catat dan pantau transaksi pengeluaran yang Anda kelola.</p>
    </div>
    <button type="button" onclick="openFinanceModal()" class="px-4 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider shadow-brutal-sm">
      + Catat Pengeluaran
    </button>
  </div>
  @endif

  <!-- SUB-TABS SEGMENTED NAVIGASI BERSIH -->
  <div class="flex items-center gap-2 border-b-2 border-ink pb-2 overflow-x-auto no-scrollbar">
    <button 
      type="button" 
      onclick="switchFinanceTab('trx')" 
      id="fsubtab-trx" 
      class="fsubtab-btn px-4 py-2 bg-ink text-white brutal-border font-display font-black text-xs sm:text-sm uppercase tracking-wider transition-all shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] flex items-center gap-1.5 flex-shrink-0"
    >
      <span>💳</span>
      <span>Transaksi & Mutasi</span>
    </button>

    @if($canViewAllFinances ?? true)
    <button 
      type="button" 
      onclick="switchFinanceTab('budget')" 
      id="fsubtab-budget" 
      class="fsubtab-btn px-4 py-2 bg-cream hover:bg-accent text-ink brutal-border font-display font-black text-xs sm:text-sm uppercase tracking-wider transition-all shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] flex items-center gap-1.5 flex-shrink-0"
    >
      <span>📊</span>
      <span>Kategori & Anggaran</span>
    </button>

    <button 
      type="button" 
      onclick="switchFinanceTab('goals')" 
      id="fsubtab-goals" 
      class="fsubtab-btn px-4 py-2 bg-cream hover:bg-accent text-ink brutal-border font-display font-black text-xs sm:text-sm uppercase tracking-wider transition-all shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] flex items-center gap-1.5 flex-shrink-0"
    >
      <span>🎯</span>
      <span>Celengan Impian</span>
      @if(count($goals) > 0)
        <span class="px-1.5 py-0.2 bg-brand text-white text-[10px] font-mono rounded-full">{{ count($goals) }}</span>
      @endif
    </button>

    <button 
      type="button" 
      onclick="switchFinanceTab('rekap')" 
      id="fsubtab-rekap" 
      class="fsubtab-btn px-4 py-2 bg-cream hover:bg-accent text-ink brutal-border font-display font-black text-xs sm:text-sm uppercase tracking-wider transition-all shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] flex items-center gap-1.5 flex-shrink-0"
    >
      <span>📈</span>
      <span>Rekap & Laporan</span>
    </button>
    @endif
  </div>

  <div id="section-finance-book" class="hidden"></div>

  @if($canViewAllFinances ?? true)
  <!-- TAB 2: ANGGARAN BULANAN PER KATEGORI -->
  <div id="section-finance-budget" class="finance-tab-section space-y-6 hidden">
  <div id="finance-budgets-section" class="bg-white brutal-card p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b-2 border-ink mb-4 gap-3">
      <div>
        <div class="flex items-center gap-2 flex-wrap">
          <h3 class="font-black font-display text-base text-ink">Batas & Riwayat Anggaran Bulanan</h3>
          <span id="budget-period-badge" class="px-2 py-0.5 bg-accent text-ink brutal-border text-[10px] font-black font-mono {{ ($selectedPeriod ?? now()->format('Y-m')) !== now()->format('Y-m') ? '' : 'hidden' }}">
            🗓️ Mode Riwayat: <span id="budget-period-label">{{ $selectedPeriodLabel ?? ($selectedPeriod ?? '') }}</span>
          </span>
        </div>
        <p class="text-[10px] font-mono text-ink/60">Batas anggaran bulanan dan realisasi belanja per periode kalender</p>
      </div>

      <!-- PERIOD SELECTOR & ACTIONS -->
      <div class="flex items-center gap-2 flex-wrap">
        <div class="flex items-center gap-1.5 bg-cream brutal-border px-2.5 py-1 text-xs">
          <span class="text-xs">📅</span>
          <select 
            id="budget-period-select"
            class="bg-transparent font-mono font-bold text-ink outline-none cursor-pointer text-xs"
          >
            @if(isset($availablePeriods))
              @foreach($availablePeriods as $ap)
                <option value="{{ $ap['period'] }}" data-label="{{ $ap['label'] }}" {{ $ap['is_selected'] ? 'selected' : '' }}>
                  {{ $ap['label'] }} {{ $ap['is_current'] ? '(Bulan Ini)' : '' }}
                </option>
              @endforeach
            @endif
          </select>
        </div>

        <button type="button" onclick="openCreateCategoryModal()" class="px-3.5 py-1.5 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-black uppercase tracking-wider flex items-center gap-1.5 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">
          <span>+ Kategori Baru</span>
        </button>
      </div>
    </div>

    <!-- SUB-FILTER TABS: PENGELUARAN VS PEMASUKAN -->
    <div class="flex items-center gap-2 mb-4 border-b-2 border-ink pb-3">
      <button type="button" id="subtab-cat-expense" onclick="switchCategorySection('expense')" class="px-3.5 py-1.5 bg-ink text-white brutal-border font-mono text-xs font-black uppercase transition-all shadow-brutal-sm">
        💸 Pengeluaran & Anggaran
      </button>
      <button type="button" id="subtab-cat-income" onclick="switchCategorySection('income')" class="px-3.5 py-1.5 bg-cream hover:bg-white text-ink brutal-border font-mono text-xs font-black uppercase transition-all shadow-brutal-sm">
        💰 Kategori Pemasukan
      </button>
    </div>

    <!-- 1. KATEGORI PENGELUARAN & ANGGARAN SECTION -->
    <div id="category-section-expense" class="space-y-4">
    <!-- 1. DESKTOP VIEW (MD+): TABEL SERVERSIDE DATATABLES -->
    <div class="hidden md:block w-full overflow-x-auto">
      <table id="budget-datatable" class="w-full text-left text-xs font-semibold">
        <thead class="bg-cream brutal-border uppercase font-mono text-[11px] text-ink">
          <tr>
            <th class="p-2.5">Kategori</th>
            <th class="p-2.5">Batas Anggaran</th>
            <th class="p-2.5">Realisasi Belanja</th>
            <th class="p-2.5">Status & Penggunaan</th>
            <th class="p-2.5 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y-2 divide-ink/10">
        </tbody>
      </table>
    </div>

    <!-- 2. MOBILE VIEW (< MD): TOUCH-FRIENDLY CARD STACK (PWA COMPLIANT) -->
    <div class="block md:hidden space-y-3.5" id="budget-mobile-container">
      <!-- QUICK SEARCH & FILTER PILLS -->
      <div class="space-y-2">
        <div class="relative">
          <input 
            type="text" 
            id="budget-mobile-search" 
            placeholder="Cari anggaran kategori..." 
            class="w-full pl-9 pr-3 py-2 bg-white brutal-border font-sans text-xs font-bold text-ink placeholder:text-ink/40 focus:outline-none focus:bg-brand-soft shadow-brutal-sm transition-colors"
          >
          <span class="absolute left-3 top-2.5 text-xs text-ink/50">🔍</span>
        </div>
        
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-mono font-bold no-scrollbar">
          <button type="button" class="btn-bfilter-status px-3 py-1 bg-ink text-white brutal-border text-[11px] font-black uppercase transition-all" data-status="all">Semua</button>
          <button type="button" class="btn-bfilter-status px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="over">🚨 Over</button>
          <button type="button" class="btn-bfilter-status px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="on_track">✓ Sesuai</button>
          <button type="button" class="btn-bfilter-status px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="no_budget">Tanpa Batas</button>
        </div>
      </div>

      <!-- CARD FEED CONTAINER -->
      <div id="budget-mobile-card-list" class="space-y-3">
        @if(isset($categoryBudgets) && count($categoryBudgets) > 0)
          @foreach($categoryBudgets->take(5) as $cb)
            @php
              $hasBudget = $cb['monthly_budget'] > 0;
              $isOver = $cb['is_over_budget'];
              $pct = $cb['pct'];
              $barColor = $isOver ? 'bg-coral' : ($pct > 80 ? 'bg-amber-500' : 'bg-brand');
            @endphp
            <div class="bg-white brutal-card p-3.5 border-2 border-ink shadow-brutal-sm mobile-budget-card space-y-2.5 transition-all {{ $isOver ? 'ring-2 ring-coral' : '' }}" data-id="{{ $cb['id'] }}">
              <div class="flex items-start justify-between gap-2 pb-2 border-b border-ink/15">
                <div class="flex items-center gap-2 min-w-0">
                  <span class="text-xl flex-shrink-0">{{ $cb['icon'] ?: '🏷️' }}</span>
                  <div class="min-w-0">
                    <h4 class="font-black text-sm text-ink truncate">{{ $cb['name'] }}</h4>
                    @if($cb['has_custom_period_budget'])
                      <span class="px-1.5 py-0.2 bg-accent text-ink brutal-border text-[9px] font-black font-mono">⭐ Anggaran Khusus</span>
                    @endif
                  </div>
                </div>
                <div class="flex-shrink-0">
                  @if($hasBudget)
                    @if($isOver)
                      <span class="px-2 py-0.5 bg-coral text-white brutal-border text-[9px] font-black font-mono animate-pulse">🚨 OVER</span>
                    @else
                      <span class="px-2 py-0.5 bg-brand-soft text-brand brutal-border text-[9px] font-black font-mono">{{ $pct }}%</span>
                    @endif
                  @else
                    <span class="px-2 py-0.5 bg-white text-ink/60 brutal-border text-[9px] font-bold font-mono">Tanpa Batas</span>
                  @endif
                </div>
              </div>

              <div class="space-y-1">
                <div class="flex justify-between items-baseline text-xs font-mono font-bold">
                  <span class="{{ $isOver ? 'text-coral' : 'text-ink' }}">Terpakai: Rp {{ number_format($cb['spent'], 0, ',', '.') }}</span>
                  <span class="text-ink/60 text-[11px]">Anggaran: {{ $hasBudget ? 'Rp ' . number_format($cb['monthly_budget'], 0, ',', '.') : '—' }}</span>
                </div>
                @if($hasBudget)
                  <div class="w-full bg-cream h-2.5 brutal-border overflow-hidden">
                    <div class="{{ $barColor }} h-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
                  </div>
                  <div class="flex justify-between items-center text-[10px] font-mono">
                    @if($isOver)
                      <span class="text-coral font-bold">Kelebihan: +Rp {{ number_format($cb['spent'] - $cb['monthly_budget'], 0, ',', '.') }}</span>
                    @else
                      <span class="text-brand font-bold">Sisa: Rp {{ number_format($cb['remaining'], 0, ',', '.') }}</span>
                    @endif
                  </div>
                @endif
                @if(!empty($cb['notes']))
                  <div class="text-[10px] text-brand font-mono font-bold pt-0.5 truncate">📝 Catatan: {{ $cb['notes'] }}</div>
                @endif
              </div>

              @php
                $cbPayload = json_encode([
                  'id' => $cb['id'],
                  'name' => $cb['name'],
                  'type' => 'expense',
                  'icon' => $cb['icon'] ?: '🏷️',
                  'monthly_budget' => (float) ($cb['monthly_budget'] ?? 0),
                  'is_custom' => $cb['is_custom'] ?? false,
                ]);
              @endphp
              <div class="pt-2 border-t border-ink/15 flex items-center gap-2">
                <button 
                  type="button" 
                  onclick='openEditCategoryForm({!! $cbPayload !!})' 
                  class="flex-1 py-2 bg-accent hover:bg-white text-ink brutal-border text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                >
                  <span>✏️ Edit Kategori & Anggaran</span>
                </button>
                @if(!empty($cb['is_custom']) && strtolower(trim($cb['name'])) !== 'lain-lain')
                  <button 
                    type="button" 
                    onclick="confirmDelete('{{ route('finances.categories.destroy', $cb['id']) }}', 'kategori {{ addslashes($cb['name']) }} (transaksi dialihkan ke Lain-lain)')" 
                    class="px-3 py-2 bg-coral hover:bg-ink text-white brutal-border text-xs font-black uppercase shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                    title="Hapus Kategori"
                  >
                    <span>🗑️</span>
                  </button>
                @endif
              </div>
            </div>
          @endforeach
        @else
          <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
            Belum ada kategori pengeluaran terdaftar.
          </div>
        @endif
      </div>

      <!-- MOBILE PAGINATION ("MUAT LEBIH BANYAK") -->
      @php
        $bgTotal = isset($categoryBudgets) ? count($categoryBudgets) : 0;
        $bgInit = min(5, $bgTotal);
      @endphp
      <div id="budget-mobile-pagination-box" class="pt-1 text-center space-y-2">
        <div class="text-[11px] font-mono font-bold text-ink/70">
          Menampilkan <span id="budget-mobile-count">{{ $bgInit }}</span> dari <span id="budget-mobile-total">{{ $bgTotal }}</span> anggaran
        </div>
        <button 
          type="button" 
          id="budget-mobile-load-more" 
          onclick="loadMoreBudgetMobile()" 
          class="{{ $bgInit >= $bgTotal ? 'hidden' : '' }} w-full py-3 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-2 active:scale-[0.99] transition-all"
        >
          <span>⬇ Muat Lebih Banyak</span>
        </button>
        <div id="budget-mobile-all-loaded" class="{{ $bgInit < $bgTotal ? 'hidden' : '' }} py-2.5 bg-cream brutal-border font-mono text-[11px] font-black text-ink/60">
          ✓ Semua anggaran telah ditampilkan
        </div>
      </div>

    </div>
    </div>
    <!-- END CATEGORY SECTION EXPENSE -->

    <!-- 2. KATEGORI PEMASUKAN SECTION -->
    <div id="category-section-income" class="space-y-4 hidden">
      <!-- 1. DESKTOP VIEW (MD+): TABEL SERVERSIDE PEMASUKAN -->
      <div class="hidden md:block w-full overflow-x-auto">
        <table id="income-category-datatable" class="w-full text-left text-xs font-semibold">
          <thead class="bg-cream brutal-border uppercase font-mono text-[11px] text-ink">
            <tr>
              <th class="p-2.5">Kategori Pemasukan</th>
              <th class="p-2.5">Jenis</th>
              <th class="p-2.5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y-2 divide-ink/10">
          </tbody>
        </table>
      </div>

      <!-- 2. MOBILE VIEW (< MD): TOUCH-FRIENDLY CARD STACK -->
      <div class="block md:hidden space-y-3" id="income-category-mobile-container">
        <div id="income-category-mobile-list" class="space-y-2.5">
          <!-- Populated by JS -->
        </div>
      </div>
    </div>
  </div>
  </div>

  <!-- TAB 3: CELENGAN & TARGET TABUNGAN BERSAMA -->
  <div id="section-finance-goals" class="finance-tab-section space-y-6 hidden">
  <div id="finance-goals-section" class="bg-white brutal-card p-4 sm:p-6">
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4 gap-2 flex-wrap">
      <div>
        <h3 class="font-black font-display text-base text-ink">Celengan & Target Tabungan Bersama</h3>
        <p class="text-[10px] font-mono text-ink/60">Wujudkan impian keluarga dengan tabungan terencana</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs font-mono font-bold text-brand hidden sm:inline">{{ count($goals) }} Target Aktif</span>
        <button type="button" onclick="openGoalModal()" class="px-3 py-1.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-brutal-sm">
          <span>+ Target Baru</span>
        </button>
      </div>
    </div>

    @if(count($goals) > 0)
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4">
        @foreach($goals as $goal)
          @php
            $isComplete = $goal['pct'] >= 100 || $goal['status'] === 'completed';
            $badgeBg = $isComplete ? 'bg-emerald-500 text-white' : ($goal['pct'] > 50 ? 'bg-accent text-ink' : 'bg-sage text-ink');
            $remaining = max(0, $goal['target_amount'] - $goal['current_amount']);
            $goalJson = json_encode($goal);
          @endphp
          <div class="p-4 sm:p-5 bg-white brutal-border shadow-brutal hover:shadow-brutal-lg hover:-translate-y-0.5 space-y-3 flex flex-col justify-between transition-all duration-300">
            <div class="space-y-3">
              <div class="flex justify-between items-start gap-2">
                <div class="w-11 h-11 bg-cream brutal-border flex items-center justify-center text-2xl flex-shrink-0 shadow-brutal-sm">
                  {{ $goal['icon'] ?? '🎯' }}
                </div>
                <div class="flex items-center gap-1">
                  @if($isComplete)
                    <span class="px-2 py-0.5 bg-emerald-500 text-white brutal-border text-[10px] font-black font-mono">✓ TERCAPAI</span>
                  @else
                    <span class="px-2 py-0.5 {{ $badgeBg }} brutal-border text-[10px] font-black font-mono">{{ $goal['pct'] }}% TERCAPAI</span>
                  @endif
                </div>
              </div>

              <div>
                <h4 class="font-black font-display text-sm sm:text-base text-ink leading-snug">{{ $goal['title'] }}</h4>
                @if(!empty($goal['target_date']))
                  <div class="text-[10px] font-mono text-ink/60 mt-0.5 flex items-center gap-1">
                    <span>🎯 Target: {{ $goal['formatted_target_date'] }}</span>
                  </div>
                @endif
              </div>

              <div class="bg-cream/60 p-2 brutal-border space-y-1">
                <div class="flex justify-between items-baseline text-xs font-mono font-bold">
                  <span class="text-ink/70 text-[11px]">Terkumpul:</span>
                  <span class="text-brand font-black">Rp {{ number_format($goal['current_amount'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-baseline text-[10px] font-mono text-ink/60">
                  <span>Target:</span>
                  <span>Rp {{ number_format($goal['target_amount'], 0, ',', '.') }}</span>
                </div>
              </div>

              <div class="space-y-1">
                <div class="w-full bg-cream h-3.5 brutal-border overflow-hidden p-0.5">
                  <div class="{{ $isComplete ? 'bg-emerald-500' : 'bg-brand' }} h-full transition-all duration-700 ease-out" style="width: {{ min(100, $goal['pct']) }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] font-mono">
                  @if($isComplete)
                    <span class="text-emerald-700 font-bold">🎉 Target tercapai penuh!</span>
                  @else
                    <span class="text-ink/60">Sisa:</span>
                    <span class="text-coral font-bold">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                  @endif
                </div>
              </div>

              @if(!empty($goal['note']))
                <div class="text-[10px] text-ink/70 font-medium italic bg-cream/40 p-1.5 brutal-border">
                  💬 {{ $goal['note'] }}
                </div>
              @endif
            </div>

            <!-- ACTION BUTTONS UNTUK GOAL -->
            <div class="pt-3 border-t border-ink/15 flex items-center justify-between gap-1.5 mt-2">
              <button 
                type="button" 
                data-goal-id="{{ $goal['id'] }}"
                data-goal-title="{{ $goal['title'] }}"
                onclick="openGoalDepositModal(this.dataset.goalId, this.dataset.goalTitle)" 
                class="px-2.5 py-1.5 bg-brand hover:bg-brand-dark text-white brutal-btn text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all flex-1 justify-center"
              >
                <span>💰 + Setor</span>
              </button>
              <button 
                type="button" 
                onclick='openEditGoalModal({!! $goalJson !!})' 
                class="px-2 py-1.5 bg-accent hover:bg-white text-ink brutal-border text-xs font-bold transition-all shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                title="Edit Target"
              >
                ✏️
              </button>
              <button 
                type="button" 
                onclick="confirmDelete('{{ route('finances.goals.destroy', $goal['id']) }}', 'target tabungan {{ addslashes($goal['title']) }} (saldo yang sudah disetor akan dikembalikan ke kas)')" 
                class="px-2 py-1.5 bg-coral hover:bg-ink text-white brutal-border text-xs font-bold transition-all shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                title="Hapus Target"
              >
                🗑️
              </button>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="p-6 text-center bg-cream brutal-border space-y-3">
        <span class="text-3xl block">🏺</span>
        <div class="font-mono text-xs font-bold text-ink/70">
          Belum ada target tabungan atau celengan bersama.
        </div>
        <button type="button" onclick="openGoalModal()" class="px-4 py-2 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          + Buat Target Pertama
        </button>
      </div>
    @endif
  </div>
  </div>
  @endif

  <!-- TAB 1: TRANSAKSI & MUTASI (HYBRID CERDAS) -->
  <div id="section-finance-trx" class="finance-tab-section space-y-4">
    <!-- QUICK FILTER CHIPS TRANSAKSI -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
      <span class="text-xs font-mono font-black uppercase text-ink/70 flex-shrink-0">Filter Cepat:</span>
      <button type="button" onclick="filterTrxQuick('all', this)" class="chip-trx-filter px-3 py-1 bg-ink text-white brutal-border text-xs font-mono font-black uppercase tracking-wider shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all flex-shrink-0">Semua</button>
      <button type="button" onclick="filterTrxQuick('expense', this)" class="chip-trx-filter px-3 py-1 bg-white text-ink brutal-border text-xs font-mono font-bold uppercase tracking-wider shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all flex-shrink-0 flex items-center gap-1"><span>💸</span> Pengeluaran</button>
      <button type="button" onclick="filterTrxQuick('income', this)" class="chip-trx-filter px-3 py-1 bg-white text-ink brutal-border text-xs font-mono font-bold uppercase tracking-wider shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all flex-shrink-0 flex items-center gap-1"><span>💰</span> Pemasukan</button>
      <button type="button" onclick="filterTrxQuick('saving', this)" class="chip-trx-filter px-3 py-1 bg-white text-ink brutal-border text-xs font-mono font-bold uppercase tracking-wider shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all flex-shrink-0 flex items-center gap-1"><span>🐷</span> Tabungan</button>
    </div>

  <!-- RIWAYAT TRANSAKSI TABEL SERVERSIDE DATATABLES -->
  <div class="bg-white brutal-card p-4 sm:p-6">
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div>
        <h3 class="font-black font-display text-base text-ink">
          {{ ($canViewAllFinances ?? true) ? 'Buku Kas: Catatan Transaksi' : 'Buku Kas: Catatan Pengeluaran Saya' }}
        </h3>
        <p class="text-[10px] font-mono text-ink/60 hidden sm:block">
          {{ ($canViewAllFinances ?? true) ? 'Mutasi uang masuk dan keluar rumah tangga' : 'Catatan pengeluaran pribadi yang telah Anda input' }}
        </p>
      </div>
      <button type="button" onclick="window.print()" class="px-2.5 py-1 bg-cream brutal-border text-xs font-bold hover:bg-accent flex items-center gap-1.5 shadow-brutal-sm">
        <span>🖨️</span>
        <span class="hidden sm:inline">Cetak / Export</span>
      </button>
    </div>

    @php
      $transactionsList = $finance['transactions'] ?? [];
      $totalTrx = isset($transactionsList) && method_exists($transactionsList, 'total') 
        ? $transactionsList->total() 
        : (isset($transactionsList) ? count($transactionsList) : 0);
      $initialCount = isset($transactionsList) ? count($transactionsList) : 0;
    @endphp

    <!-- 1. DESKTOP VIEW (MD+): TABEL LENGKAP NEOBRUTALISME -->
    <div class="hidden md:block w-full overflow-x-auto">
      <table id="finance-datatable" class="w-full text-left text-xs font-semibold">
        <thead class="bg-cream brutal-border uppercase font-mono text-[11px] text-ink">
          <tr>
            <th class="p-2.5">Tanggal</th>
            <th class="p-2.5">Kategori</th>
            <th class="p-2.5">Keterangan</th>
            <th class="p-2.5">Pencatat</th>
            <th class="p-2.5 text-right">Nominal</th>
            <th class="p-2.5 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y-2 divide-ink/10">
          @if(isset($finance['transactions']) && count($finance['transactions']) > 0)
            @foreach($finance['transactions'] as $trx)
              <tr class="finance-card-row">
                <td class="p-2.5 font-mono text-ink col-date">
                  <span class="inline-flex items-center gap-1">
                    <span>🗓️</span>
                    <span>{{ \Carbon\Carbon::parse($trx->transaction_date)->locale('id')->isoFormat('D MMM Y') }}</span>
                  </span>
                </td>
                <td class="p-2.5 col-cat">
                  <span class="px-2 py-0.5 {{ $trx->type === 'income' ? 'bg-brand text-white' : 'bg-sage text-ink' }} brutal-border text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1">
                    <span>{{ $trx->category ? $trx->category->icon : ($trx->type === 'income' ? '💰' : '📦') }}</span>
                    <span>{{ $trx->category ? $trx->category->name : ($trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran') }}</span>
                  </span>
                </td>
                <td class="p-2.5 font-bold text-ink col-desc">
                  <div class="text-xs sm:text-sm font-black text-ink leading-tight">{{ $trx->description }}</div>
                  @if($trx->receipt_path)
                    @php
                      $receiptUrl = str_starts_with($trx->receipt_path, 'http') ? $trx->receipt_path : asset('storage/'.$trx->receipt_path);
                    @endphp
                    <button type="button" onclick="showImagePreview('{{ $receiptUrl }}', 'Struk: {{ addslashes($trx->description) }}')" class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 bg-brand-soft hover:bg-brand hover:text-white brutal-border text-[10px] font-mono font-bold transition-colors">
                      📎 Struk
                    </button>
                  @endif
                </td>
                <td class="p-2.5 text-ink col-user">
                  <span class="inline-flex items-center gap-1 text-xs">
                    <span class="font-bold text-ink">{{ $trx->user ? $trx->user->name : 'Keluarga' }}</span>
                  </span>
                </td>
                <td class="p-2.5 text-right font-mono font-bold col-amount">
                  <div class="inline-flex items-center gap-1 text-xs sm:text-sm font-black {{ $trx->type === 'income' ? 'text-brand' : 'text-coral' }}">
                    <span>{{ $trx->type === 'income' ? '+ ' : '- ' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                  </div>
                </td>
                <td class="p-2.5 text-center col-actions">
                  <div class="flex items-center justify-center gap-1.5">
                    <button 
                      type="button" 
                      onclick='openEditFinanceModal({{ json_encode([
                        'id' => $trx->id,
                        'update_url' => route('finances.update', $trx->id),
                        'type' => $trx->type,
                        'amount' => (int) $trx->amount,
                        'description' => $trx->description,
                        'category_id' => $trx->category_id,
                        'category' => $trx->category ? $trx->category->name : ($trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran'),
                        'user_id' => $trx->user_id,
                        'user' => $trx->user ? $trx->user->name : 'Keluarga',
                        'receipt_url' => $trx->receipt_path ? (str_starts_with($trx->receipt_path, 'http') ? $trx->receipt_path : asset('storage/'.$trx->receipt_path)) : null,
                        'transaction_date' => $trx->transaction_date,
                      ]) }})' 
                      class="px-2.5 py-1 bg-accent hover:bg-white brutal-border text-[10px] font-bold transition-colors shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                      title="Edit Transaksi"
                    >
                      ✏️ Edit
                    </button>
                    <button 
                      type="button" 
                      onclick="confirmDelete('{{ route('finances.destroy', $trx->id) }}', 'transaksi {{ addslashes($trx->description) }}')" 
                      class="px-2.5 py-1 bg-coral text-white hover:bg-ink brutal-border text-[10px] font-bold transition-colors shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                      title="Hapus Transaksi"
                    >
                      🗑️ Hapus
                    </button>
                  </div>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>

    <!-- 2. MOBILE VIEW (< MD): NEOBRUTALIST CARD STACK & PAGINATION "LOAD MORE" -->
    <div class="block md:hidden space-y-3.5" id="finance-mobile-container">
      
      <!-- MOBILE QUICK SEARCH & FILTER PILLS -->
      <div class="space-y-2">
        <div class="relative">
          <input 
            type="text" 
            id="finance-mobile-search" 
            placeholder="{{ ($canViewAllFinances ?? true) ? 'Cari transaksi kas keluarga...' : 'Cari pengeluaran saya...' }}" 
            class="w-full pl-9 pr-3 py-2 bg-white brutal-border font-sans text-xs font-bold text-ink placeholder:text-ink/40 focus:outline-none focus:bg-brand-soft shadow-brutal-sm transition-colors"
          >
          <span class="absolute left-3 top-2.5 text-xs text-ink/50">🔍</span>
        </div>
        
        @if($canViewAllFinances ?? true)
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-mono font-bold no-scrollbar">
          <button type="button" class="btn-mfilter-type px-3 py-1 bg-ink text-white brutal-border text-[11px] font-black uppercase transition-all" data-type="all">Semua</button>
          <button type="button" class="btn-mfilter-type px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-type="expense">Pengeluaran</button>
          <button type="button" class="btn-mfilter-type px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-type="income">Pemasukan</button>
        </div>
        @endif
      </div>

      <!-- CARD FEED CONTAINER -->
      <div id="finance-mobile-card-list" class="space-y-3">
        @if(isset($finance['transactions']) && count($finance['transactions']) > 0)
          @foreach($finance['transactions'] as $trx)
            @php
              $isIncome = $trx->type === 'income';
              $receiptUrl = $trx->receipt_path ? (str_starts_with($trx->receipt_path, 'http') ? $trx->receipt_path : asset('storage/'.$trx->receipt_path)) : null;
              $trxPayload = json_encode([
                'id' => $trx->id,
                'update_url' => route('finances.update', $trx->id),
                'destroy_url' => route('finances.destroy', $trx->id),
                'type' => $trx->type,
                'amount' => (int) $trx->amount,
                'formatted_amount' => ($isIncome ? '+ ' : '- ').'Rp '.number_format($trx->amount, 0, ',', '.'),
                'description' => $trx->description,
                'category_id' => $trx->category_id,
                'category' => $trx->category ? $trx->category->name : ($isIncome ? 'Pemasukan' : 'Pengeluaran'),
                'user_id' => $trx->user_id,
                'user' => $trx->user ? $trx->user->name : 'Keluarga',
                'receipt_url' => $receiptUrl,
                'transaction_date' => $trx->transaction_date,
                'formatted_date' => \Carbon\Carbon::parse($trx->transaction_date)->locale('id')->isoFormat('D MMM Y'),
              ]);
            @endphp
            <div class="bg-white brutal-card p-3.5 border-2 border-ink shadow-brutal-sm mobile-card-item transition-all" data-id="{{ $trx->id }}">
              <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-ink/15">
                <span class="px-2.5 py-0.5 {{ $isIncome ? 'bg-brand text-white' : 'bg-sage text-ink' }} brutal-border text-[10px] font-black uppercase tracking-wider truncate inline-flex items-center gap-1">
                  <span>{{ $trx->category ? $trx->category->icon : ($isIncome ? '💰' : '📦') }}</span>
                  <span>{{ $trx->category ? $trx->category->name : ($isIncome ? 'Pemasukan' : 'Pengeluaran') }}</span>
                </span>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                  <span class="font-mono text-[10px] font-bold text-ink/70 flex items-center gap-0.5">
                    <span>🗓️</span>
                    <span>{{ \Carbon\Carbon::parse($trx->transaction_date)->locale('id')->isoFormat('D MMM Y') }}</span>
                  </span>
                  <div class="flex items-center gap-1 ml-1">
                    <button 
                      type="button" 
                      onclick='openEditFinanceModal({!! $trxPayload !!})' 
                      class="w-6 h-6 bg-cream hover:bg-accent text-ink brutal-border flex items-center justify-center text-[10px] shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                      title="Edit Transaksi"
                    >
                      ✏️
                    </button>
                    <button 
                      type="button" 
                      onclick="confirmDelete('{{ route('finances.destroy', $trx->id) }}', 'transaksi {{ addslashes($trx->description) }}')" 
                      class="w-6 h-6 bg-cream hover:bg-coral hover:text-white text-ink brutal-border flex items-center justify-center text-[10px] shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                      title="Hapus Transaksi"
                    >
                      🗑️
                    </button>
                  </div>
                </div>
              </div>

              <div class="pt-2.5 flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                  <h4 class="text-sm font-black text-ink leading-snug break-words">{{ $trx->description }}</h4>
                  <div class="mt-1 flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-ink/70">
                      <span class="text-ink/50">👤</span> {{ $trx->user ? $trx->user->name : 'Keluarga' }}
                    </span>
                    @if($receiptUrl)
                      <button type="button" onclick="showImagePreview('{{ $receiptUrl }}', 'Struk: {{ addslashes($trx->description) }}')" class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-brand-soft hover:bg-brand hover:text-white brutal-border text-[9px] font-mono font-bold transition-colors">
                        📎 Struk
                      </button>
                    @endif
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <span class="text-[9px] font-mono font-black block text-ink/50 uppercase">{{ $isIncome ? 'Masuk' : 'Keluar' }}</span>
                  <span class="font-mono text-sm sm:text-base font-black {{ $isIncome ? 'text-brand' : 'text-coral' }} leading-tight block">
                    {{ $isIncome ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                  </span>
                </div>
              </div>
            </div>
          @endforeach
        @else
          <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
            Belum ada catatan transaksi kas.
          </div>
        @endif
      </div>

      <!-- MOBILE PAGINATION ("MUAT LEBIH BANYAK") -->
      <div id="finance-mobile-pagination-box" class="pt-1 text-center space-y-2">
        <div id="finance-mobile-counter" class="text-[11px] font-mono font-bold text-ink/70">
          Menampilkan <span id="finance-mobile-count">{{ $initialCount }}</span> dari <span id="finance-mobile-total">{{ $totalTrx }}</span> transaksi
        </div>
        <button 
          type="button" 
          id="finance-mobile-load-more" 
          data-page="2" 
          onclick="loadMoreFinanceMobile()" 
          class="{{ $initialCount >= $totalTrx ? 'hidden' : '' }} w-full py-3 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-2 active:scale-[0.99] transition-all"
        >
          <span>⬇ Muat Lebih Banyak</span>
        </button>
        <div id="finance-mobile-all-loaded" class="{{ $initialCount < $totalTrx ? 'hidden' : '' }} py-2.5 bg-cream brutal-border font-mono text-[11px] font-black text-ink/60">
          ✓ Semua data telah ditampilkan
        </div>
      </div>

    </div>
  </div>
  </div>

  <!-- TAB 4: REKAP LAPORAN KEUANGAN & DIAGRAM ANALISIS -->
  <div id="section-finance-rekap" class="finance-tab-section space-y-6 hidden">
    <!-- FILTER BAR REKAP -->
    <div class="bg-white brutal-card p-4 sm:p-6 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b-2 border-ink">
        <div>
          <h3 class="font-black font-display text-lg text-ink">Rekap & Analisis Keuangan Keluarga</h3>
          <p class="text-xs font-medium text-ink/70">Diagram persentase pengeluaran tiap kategori dan tabungan dari seluruh uang masuk yang dikelola.</p>
        </div>
        <div class="flex items-center gap-1.5 bg-cream p-1 brutal-border text-xs font-mono font-bold">
          <button type="button" onclick="setReportMode('monthly')" id="btn-rmode-monthly" class="px-3 py-1.5 bg-ink text-white brutal-border transition-all">
            Bulanan
          </button>
          <button type="button" onclick="setReportMode('yearly')" id="btn-rmode-yearly" class="px-3 py-1.5 bg-transparent text-ink hover:bg-white brutal-border transition-all">
            Tahunan
          </button>
          <button type="button" onclick="setReportMode('range')" id="btn-rmode-range" class="px-3 py-1.5 bg-transparent text-ink hover:bg-white brutal-border transition-all">
            Rentang Bulan
          </button>
        </div>
      </div>

      <!-- FILTER INPUTS -->
      <form id="report-filter-form" onsubmit="applyReportFilter(event)" class="flex flex-wrap items-center gap-3">
        <!-- MONTH PICKER -->
        <div id="filter-wrapper-monthly" class="flex items-center gap-2">
          <label class="text-xs font-black font-mono uppercase text-ink">Bulan:</label>
          <input 
            type="month" 
            id="report-input-month" 
            value="{{ $selectedPeriod ?? now()->format('Y-m') }}" 
            class="px-3 py-1.5 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none focus:bg-white"
          >
        </div>

        <!-- YEAR PICKER -->
        <div id="filter-wrapper-yearly" class="items-center gap-2 hidden">
          <label class="text-xs font-black font-mono uppercase text-ink">Tahun:</label>
          <select 
            id="report-input-year" 
            class="px-3 py-1.5 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none focus:bg-white"
          >
            @php $thisYear = (int) now()->format('Y'); @endphp
            @for($y = $thisYear; $y >= $thisYear - 4; $y--)
              <option value="{{ $y }}" {{ $y === $thisYear ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>
        </div>

        <!-- RANGE PICKER -->
        <div id="filter-wrapper-range" class="items-center gap-2 hidden">
          <label class="text-xs font-black font-mono uppercase text-ink">Dari:</label>
          <input 
            type="month" 
            id="report-input-start-month" 
            value="{{ now()->startOfYear()->format('Y-m') }}" 
            class="px-3 py-1.5 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none focus:bg-white"
          >
          <label class="text-xs font-black font-mono uppercase text-ink">Sampai:</label>
          <input 
            type="month" 
            id="report-input-end-month" 
            value="{{ now()->format('Y-m') }}" 
            class="px-3 py-1.5 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none focus:bg-white"
          >
        </div>

        <button type="submit" class="px-4 py-1.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider shadow-brutal-sm">
          🔍 Tampilkan Rekap
        </button>

        <span id="report-active-label" class="text-xs font-mono font-bold text-brand ml-auto">
          Periode: <span id="report-period-text">{{ $report['period_label'] ?? now()->locale('id')->isoFormat('MMMM Y') }}</span>
        </span>
      </form>
    </div>

    <!-- METRICS 4 CARDS REKAP -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
      <div class="bg-brand-soft brutal-card p-4 sm:p-5">
        <div class="text-[10px] sm:text-xs font-mono font-bold text-ink/70 uppercase">Total Uang Masuk</div>
        <div id="rekap-total-income" class="text-xl sm:text-2xl font-black font-mono text-brand mt-1 truncate">
          {{ $report['formatted_total_income'] ?? 'Rp 0' }}
        </div>
        <div class="text-[11px] font-bold text-ink/70 mt-1">100% dana yang dikelola</div>
      </div>

      <div class="bg-emerald-50 brutal-card p-4 sm:p-5">
        <div class="text-[10px] sm:text-xs font-mono font-bold text-emerald-800 uppercase">Total Ditabung</div>
        <div id="rekap-total-saving" class="text-xl sm:text-2xl font-black font-mono text-emerald-700 mt-1 truncate">
          {{ $report['formatted_total_saving'] ?? 'Rp 0' }}
        </div>
        <div class="text-[11px] font-black text-emerald-800 mt-1">
          <span id="rekap-pct-saving">{{ $report['pct_saving_of_income'] ?? 0 }}</span>% dari uang masuk
        </div>
      </div>

      <div class="bg-coral/10 brutal-card p-4 sm:p-5">
        <div class="text-[10px] sm:text-xs font-mono font-bold text-coral uppercase">Total Pengeluaran</div>
        <div id="rekap-total-expense" class="text-xl sm:text-2xl font-black font-mono text-coral mt-1 truncate">
          {{ $report['formatted_total_expense'] ?? 'Rp 0' }}
        </div>
        <div class="text-[11px] font-black text-coral mt-1">
          <span id="rekap-pct-expense">{{ $report['pct_expense_of_income'] ?? 0 }}</span>% dari uang masuk
        </div>
      </div>

      <div class="bg-white brutal-card p-4 sm:p-5">
        <div class="text-[10px] sm:text-xs font-mono font-bold text-ink/70 uppercase">Sisa Kas Tersedia</div>
        <div id="rekap-net-balance" class="text-xl sm:text-2xl font-black font-mono text-ink mt-1 truncate">
          {{ $report['formatted_net_balance'] ?? 'Rp 0' }}
        </div>
        <div class="text-[11px] font-bold text-ink/70 mt-1">
          <span id="rekap-pct-remaining">{{ $report['pct_remaining_of_income'] ?? 0 }}</span>% cadangan kas
        </div>
      </div>
    </div>

    <!-- DIAGRAM VISUALISASI REKAP -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- DONUT CHART: ALOKASI UANG MASUK -->
      <div class="lg:col-span-5 bg-white brutal-card p-4 sm:p-6 flex flex-col justify-between">
        <div class="pb-3 border-b-2 border-ink mb-4">
          <h4 class="font-black font-display text-base text-ink">Diagram Alokasi Uang Masuk (%)</h4>
          <p class="text-[10px] font-mono text-ink/60">Persentase belanja per kategori & tabungan dari total pemasukan</p>
        </div>
        <div class="relative w-full aspect-square max-h-[320px] mx-auto flex items-center justify-center">
          <canvas id="rekapDonutChart"></canvas>
        </div>
        <div id="donut-empty-state" class="hidden text-center py-8 font-mono text-xs font-bold text-ink/60">
          Belum ada catatan pemasukan/pengeluaran pada periode ini.
        </div>
      </div>

      <!-- BAR CHART: NOMINAL PENGELUARAN PER KATEGORI -->
      <div class="lg:col-span-7 bg-white brutal-card p-4 sm:p-6 flex flex-col justify-between">
        <div class="pb-3 border-b-2 border-ink mb-4">
          <h4 class="font-black font-display text-base text-ink">Nominal Belanja per Kategori (Rp)</h4>
          <p class="text-[10px] font-mono text-ink/60">Perbandingan nominal rupiah yang dikeluarkan per pos belanja</p>
        </div>
        <div class="relative w-full min-h-[300px] max-h-[320px]">
          <canvas id="rekapBarChart"></canvas>
        </div>
        <div id="bar-empty-state" class="hidden text-center py-8 font-mono text-xs font-bold text-ink/60">
          Belum ada pengeluaran belanja di periode ini.
        </div>
      </div>
    </div>

    <!-- KARTU ANALISIS KESIMPULAN BOROS DIMANA -->
    <div class="bg-accent/40 brutal-card p-4 sm:p-6 space-y-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-coral text-white brutal-border flex items-center justify-center text-xl font-black">
          🚨
        </div>
        <div>
          <h4 class="font-black font-display text-base text-ink">Analisis & Evaluasi Boros Pengeluaran</h4>
          <p class="text-xs font-medium text-ink/80">Identifikasi pos pengeluaran keluarga yang menyerap anggaran paling besar.</p>
        </div>
      </div>

      <div id="rekap-top-waste-box" class="p-3.5 bg-white brutal-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <div class="text-[10px] font-mono uppercase font-black text-coral">Kategori Paling Boros di Periode Ini:</div>
          <div class="text-lg font-black text-ink flex items-center gap-2 mt-0.5" id="rekap-top-waste-title">
            <span>{{ $report['top_category']['icon'] ?? '🏷️' }}</span>
            <span>{{ $report['top_category']['name'] ?? 'Belum ada data' }}</span>
          </div>
        </div>
        <div class="text-left sm:text-right">
          <div class="text-base font-black font-mono text-coral" id="rekap-top-waste-amount">
            {{ $report['top_category']['formatted_amount'] ?? 'Rp 0' }}
          </div>
          <div class="text-[10px] font-mono font-bold text-ink/70" id="rekap-top-waste-pct">
            {{ $report['top_category']['pct_of_expense'] ?? 0 }}% dari total belanja ({{ $report['top_category']['pct_of_income'] ?? 0 }}% dari uang masuk)
          </div>
        </div>
      </div>

      <!-- TABEL RINCIAN PENGELUARAN PER KATEGORI -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse brutal-border bg-white text-xs">
          <thead>
            <tr class="bg-ink text-white font-mono text-[10px] uppercase">
              <th class="p-2.5">Kategori</th>
              <th class="p-2.5 text-right">Total Belanja (Rp)</th>
              <th class="p-2.5 text-center">% dari Uang Masuk</th>
              <th class="p-2.5 text-center">% dari Total Belanja</th>
              <th class="p-2.5 text-center">Jumlah Transaksi</th>
            </tr>
          </thead>
          <tbody id="rekap-categories-table-body">
            @if(isset($report['categories']) && count($report['categories']) > 0)
              @foreach($report['categories'] as $cat)
                <tr class="border-b border-ink/20 hover:bg-cream">
                  <td class="p-2.5 font-bold text-ink flex items-center gap-1.5">
                    <span>{{ $cat['icon'] }}</span>
                    <span>{{ $cat['name'] }}</span>
                  </td>
                  <td class="p-2.5 text-right font-mono font-bold text-coral">{{ $cat['formatted_amount'] }}</td>
                  <td class="p-2.5 text-center font-mono font-bold">{{ $cat['pct_of_income'] }}%</td>
                  <td class="p-2.5 text-center font-mono font-bold text-brand">{{ $cat['pct_of_expense'] }}%</td>
                  <td class="p-2.5 text-center font-mono">{{ $cat['transaction_count'] }} kali</td>
                </tr>
              @endforeach
            @else
              <tr>
                <td colspan="5" class="p-4 text-center font-mono text-ink/60">Tidak ada pengeluaran pada periode ini.</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('modals')
  @include('modules.finance.modal')
  @if($canViewAllFinances ?? true)
    @include('modules.finance.category-modal')
    @include('modules.finance.goal-modal')
    @include('modules.finance.period-budget-modal')
  @endif
@endpush

@push('scripts')
<script>
  window.formatRupiah = function(val) {
    if (!val && val !== 0) return '';
    const clean = val.toString().replace(/[^0-9]/g, '');
    if (!clean) return '';
    const num = parseInt(clean, 10);
    if (isNaN(num)) return '';
    return 'Rp ' + num.toLocaleString('id-ID');
  };

  window.handleCurrencyInput = function(input) {
    const clean = input.value.replace(/[^0-9]/g, '');
    if (!clean) {
      input.value = '';
      return;
    }
    input.value = 'Rp ' + parseInt(clean, 10).toLocaleString('id-ID');
  };

  window.addQuickAmount = function(val, targetId = 'finance-input-amount') {
    const input = document.getElementById(targetId);
    if (!input) return;
    const current = parseInt((input.value || '').replace(/[^0-9]/g, ''), 10) || 0;
    const next = current + val;
    input.value = 'Rp ' + next.toLocaleString('id-ID');
    $(input).trigger('input');
  };

  window.clearQuickAmount = function(targetId = 'finance-input-amount') {
    const input = document.getElementById(targetId);
    if (!input) return;
    input.value = '';
    $(input).trigger('input');
  };

  window.toggleBalancePrivacy = function() {
    const isHidden = localStorage.getItem('famkoji_finance_hide_balance') === 'true';
    localStorage.setItem('famkoji_finance_hide_balance', !isHidden);
    applyBalancePrivacy();
  };

  window.applyBalancePrivacy = function() {
    const isHidden = localStorage.getItem('famkoji_finance_hide_balance') === 'true';
    const $statBalance = $('#stat-total-balance');
    if (!$statBalance.length) return;
    
    const realBalance = $statBalance.attr('data-real-balance') || $statBalance.attr('title') || $statBalance.text().trim();
    if (!$statBalance.attr('data-real-balance')) {
      $statBalance.attr('data-real-balance', realBalance);
    }
    
    if (isHidden) {
      $statBalance.text('Rp ••••••••');
      $('#btn-toggle-eye').html(`
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        </svg>
        <span class="text-[10px] font-mono font-bold">Tampilkan</span>
      `);
    } else {
      $statBalance.text($statBalance.attr('data-real-balance'));
      $('#btn-toggle-eye').html(`
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <span class="text-[10px] font-mono font-bold">Privasi</span>
      `);
    }
  };

  let financeDataTable = null;
  let budgetDataTable = null;
  let categoryDataTable = null;
  let financeDatePicker = null;
  let donutChartInstance = null;
  let barChartInstance = null;
  let currentReportData = @json($report ?? null);
  let currentReportMode = 'monthly';

  window.switchFinanceTab = function(tab) {
    $('.finance-tab-section').addClass('hidden');
    $('.fsubtab-btn').removeClass('bg-ink text-white').addClass('bg-cream text-ink hover:bg-accent');

    if (tab === 'rekap') {
      $('#section-finance-rekap').removeClass('hidden');
      $('#fsubtab-rekap').removeClass('bg-cream text-ink hover:bg-accent').addClass('bg-ink text-white');
      setTimeout(() => {
        if (typeof initOrUpdateReportCharts === 'function') initOrUpdateReportCharts();
      }, 50);
    } else if (tab === 'budget') {
      $('#section-finance-budget').removeClass('hidden');
      $('#fsubtab-budget').removeClass('bg-cream text-ink hover:bg-accent').addClass('bg-ink text-white');
      if (window.budgetDataTable) {
        setTimeout(() => window.budgetDataTable.columns.adjust().draw(false), 50);
      }
    } else if (tab === 'goals') {
      $('#section-finance-goals').removeClass('hidden');
      $('#fsubtab-goals').removeClass('bg-cream text-ink hover:bg-accent').addClass('bg-ink text-white');
    } else {
      $('#section-finance-trx').removeClass('hidden');
      $('#fsubtab-trx').removeClass('bg-cream text-ink hover:bg-accent').addClass('bg-ink text-white');
      if (window.financeDataTable) {
        setTimeout(() => window.financeDataTable.columns.adjust().draw(false), 50);
      }
    }

    try {
      sessionStorage.setItem('famkoji_finance_active_tab', tab);
    } catch(e) {}
  };

  window.switchFinanceMainTab = function(tab) {
    if (tab === 'rekap') {
      switchFinanceTab('rekap');
    } else {
      switchFinanceTab('trx');
    }
  };

  window.quickOpenDepositModal = function() {
    @if(isset($goals) && count($goals) > 0)
      @php $firstGoal = $goals->first(); @endphp
      openGoalDepositModal({{ $firstGoal['id'] }}, @json($firstGoal['title']));
    @else
      openGoalModal();
    @endif
  };

  window.filterTrxQuick = function(type, el) {
    $('.chip-trx-filter').removeClass('bg-ink text-white font-black').addClass('bg-white text-ink font-bold');
    $(el).removeClass('bg-white text-ink font-bold').addClass('bg-ink text-white font-black');

    if (window.financeDataTable) {
      if (type === 'all') {
        window.financeDataTable.search('').draw();
      } else if (type === 'expense') {
        window.financeDataTable.search('Pengeluaran').draw();
      } else if (type === 'income') {
        window.financeDataTable.search('Pemasukan').draw();
      } else if (type === 'saving') {
        window.financeDataTable.search('Tabungan').draw();
      }
    }

    financeMobileFilterType = type;
    if (typeof refreshFinanceMobile === 'function') {
      refreshFinanceMobile();
    }
  };

  window.setReportMode = function(mode) {
    currentReportMode = mode;
    ['monthly', 'yearly', 'range'].forEach(m => {
      const $btn = $(`#btn-rmode-${m}`);
      const $wrapper = $(`#filter-wrapper-${m}`);
      if (m === mode) {
        $btn.removeClass('bg-transparent text-ink hover:bg-white').addClass('bg-ink text-white');
        $wrapper.removeClass('hidden').addClass('flex');
      } else {
        $btn.removeClass('bg-ink text-white').addClass('bg-transparent text-ink hover:bg-white');
        $wrapper.addClass('hidden').removeClass('flex');
      }
    });
  };

  window.applyReportFilter = function(e) {
    if (e) e.preventDefault();
    fetchReportData();
  };

  window.fetchReportData = function() {
    const params = { mode: currentReportMode };
    if (currentReportMode === 'monthly') {
      params.period = $('#report-input-month').val() || '';
    } else if (currentReportMode === 'yearly') {
      params.year = $('#report-input-year').val() || '';
    } else if (currentReportMode === 'range') {
      params.start_month = $('#report-input-start-month').val() || '';
      params.end_month = $('#report-input-end-month').val() || '';
    }

    showLoading('Memuat laporan keuangan...');
    $.ajax({
      url: "{{ route('finances.report_data') }}",
      type: 'GET',
      data: params,
      dataType: 'json',
      success: function(res) {
        if (res && res.success && res.report) {
          currentReportData = res.report;
          renderReportUI(res.report);
          initOrUpdateReportCharts();
        }
      },
      error: function(err) {
        console.error('Gagal mengambil data laporan:', err);
        if (typeof showToast === 'function') showToast('Gagal memuat data laporan.', 'error');
      },
      complete: function() {
        hideLoading();
      }
    });
  };

  function renderReportUI(report) {
    $('#report-period-text').text(report.period_label || '');
    $('#rekap-total-income').text(report.formatted_total_income || 'Rp 0');
    $('#rekap-total-saving').text(report.formatted_total_saving || 'Rp 0');
    $('#rekap-pct-saving').text(report.pct_saving_of_income ?? 0);
    $('#rekap-total-expense').text(report.formatted_total_expense || 'Rp 0');
    $('#rekap-pct-expense').text(report.pct_expense_of_income ?? 0);
    $('#rekap-net-balance').text(report.formatted_net_balance || 'Rp 0');
    $('#rekap-pct-remaining').text(report.pct_remaining_of_income ?? 0);

    if (report.top_category && report.top_category.amount > 0) {
      $('#rekap-top-waste-box').removeClass('hidden');
      $('#rekap-top-waste-title').html(`<span>${report.top_category.icon || '🏷️'}</span><span>${escapeHtml(report.top_category.name)}</span>`);
      $('#rekap-top-waste-amount').text(report.top_category.formatted_amount);
      $('#rekap-top-waste-pct').text(`${report.top_category.pct_of_expense}% dari total belanja (${report.top_category.pct_of_income}% dari uang masuk)`);
    } else {
      $('#rekap-top-waste-title').html(`<span>👍</span><span>Belum ada pengeluaran boros</span>`);
      $('#rekap-top-waste-amount').text('Rp 0');
      $('#rekap-top-waste-pct').text('0% pengeluaran');
    }

    const $tbody = $('#rekap-categories-table-body');
    $tbody.empty();
    if (report.categories && report.categories.length > 0) {
      report.categories.forEach(cat => {
        $tbody.append(`
          <tr class="border-b border-ink/20 hover:bg-cream">
            <td class="p-2.5 font-bold text-ink flex items-center gap-1.5">
              <span>${cat.icon || '🏷️'}</span>
              <span>${escapeHtml(cat.name)}</span>
            </td>
            <td class="p-2.5 text-right font-mono font-bold text-coral">${cat.formatted_amount}</td>
            <td class="p-2.5 text-center font-mono font-bold">${cat.pct_of_income}%</td>
            <td class="p-2.5 text-center font-mono font-bold text-brand">${cat.pct_of_expense}%</td>
            <td class="p-2.5 text-center font-mono">${cat.transaction_count} kali</td>
          </tr>
        `);
      });
    } else {
      $tbody.append(`
        <tr>
          <td colspan="5" class="p-4 text-center font-mono text-ink/60">Tidak ada pengeluaran pada periode ini.</td>
        </tr>
      `);
    }
  }

  function initOrUpdateReportCharts() {
    if (typeof Chart === 'undefined') return;
    const report = currentReportData;
    if (!report) return;

    const palette = ['#E1523D', '#E2B842', '#2F5233', '#4A7C59', '#3B82F6', '#8B5CF6', '#EC4899', '#F97316', '#14B8A6', '#6366F1'];

    // 1. DATA DONUT CHART
    const donutLabels = [];
    const donutData = [];
    const donutColors = [];
    const donutRawAmounts = [];

    if (report.categories && report.categories.length > 0) {
      report.categories.forEach((c, idx) => {
        donutLabels.push(`${c.icon || '🏷️'} ${c.name}`);
        donutData.push(c.pct_of_income);
        donutColors.push(c.color || palette[idx % palette.length]);
        donutRawAmounts.push(c.formatted_amount);
      });
    }

    if (report.total_saving > 0) {
      donutLabels.push('🐷 Tabungan (Celengan)');
      donutData.push(report.pct_saving_of_income);
      donutColors.push('#10B981');
      donutRawAmounts.push(report.formatted_total_saving);
    }

    if (report.net_balance > 0 && report.total_income > 0) {
      donutLabels.push('💵 Sisa Kas Tersedia');
      donutData.push(report.pct_remaining_of_income);
      donutColors.push('#E5E7EB');
      donutRawAmounts.push(report.formatted_net_balance);
    }

    const donutCanvas = document.getElementById('rekapDonutChart');
    if (donutCanvas) {
      if (donutData.length === 0 || report.total_income <= 0) {
        $('#rekapDonutChart').addClass('hidden');
        $('#donut-empty-state').removeClass('hidden');
        if (donutChartInstance) {
          donutChartInstance.destroy();
          donutChartInstance = null;
        }
      } else {
        $('#rekapDonutChart').removeClass('hidden');
        $('#donut-empty-state').addClass('hidden');

        if (donutChartInstance) {
          donutChartInstance.data.labels = donutLabels;
          donutChartInstance.data.datasets[0].data = donutData;
          donutChartInstance.data.datasets[0].backgroundColor = donutColors;
          donutChartInstance.update();
        } else {
          donutChartInstance = new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
              labels: donutLabels,
              datasets: [{
                data: donutData,
                backgroundColor: donutColors,
                borderWidth: 2,
                borderColor: '#111827'
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  position: 'bottom',
                  labels: {
                    boxWidth: 12,
                    font: { family: 'ui-monospace, monospace', size: 10, weight: 'bold' },
                    color: '#111827'
                  }
                },
                tooltip: {
                  callbacks: {
                    label: function(ctx) {
                      const raw = donutRawAmounts[ctx.dataIndex] || '';
                      return ` ${ctx.label}: ${ctx.raw}% (${raw})`;
                    }
                  }
                }
              },
              cutout: '60%'
            }
          });
        }
      }
    }

    // 2. DATA BAR CHART
    const barLabels = [];
    const barData = [];
    const barColors = [];

    if (report.categories && report.categories.length > 0) {
      report.categories.forEach((c, idx) => {
        barLabels.push(`${c.icon || '🏷️'} ${c.name}`);
        barData.push(c.amount);
        barColors.push(c.color || palette[idx % palette.length]);
      });
    }

    const barCanvas = document.getElementById('rekapBarChart');
    if (barCanvas) {
      if (barData.length === 0) {
        $('#rekapBarChart').addClass('hidden');
        $('#bar-empty-state').removeClass('hidden');
        if (barChartInstance) {
          barChartInstance.destroy();
          barChartInstance = null;
        }
      } else {
        $('#rekapBarChart').removeClass('hidden');
        $('#bar-empty-state').addClass('hidden');

        if (barChartInstance) {
          barChartInstance.data.labels = barLabels;
          barChartInstance.data.datasets[0].data = barData;
          barChartInstance.data.datasets[0].backgroundColor = barColors;
          barChartInstance.update();
        } else {
          barChartInstance = new Chart(barCanvas, {
            type: 'bar',
            data: {
              labels: barLabels,
              datasets: [{
                label: 'Nominal Belanja',
                data: barData,
                backgroundColor: barColors,
                borderWidth: 2,
                borderColor: '#111827'
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { display: false },
                tooltip: {
                  callbacks: {
                    label: function(ctx) {
                      return ' Belanja: Rp ' + Number(ctx.raw).toLocaleString('id-ID');
                    }
                  }
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  ticks: {
                    font: { family: 'ui-monospace, monospace', size: 9 },
                    color: '#111827',
                    callback: function(val) {
                      if (val >= 1000000) return (val / 1000000).toFixed(1) + ' jt';
                      if (val >= 1000) return (val / 1000) + ' rb';
                      return val;
                    }
                  },
                  grid: { color: 'rgba(0,0,0,0.06)' }
                },
                x: {
                  ticks: {
                    font: { family: 'ui-monospace, monospace', size: 10, weight: 'bold' },
                    color: '#111827'
                  },
                  grid: { display: false }
                }
              }
            }
          });
        }
      }
    }
  }

  window.refreshFinanceData = function() {
    if (financeDataTable) {
      financeDataTable.ajax.reload(null, false);
    }
    if (typeof refreshFinanceMobile === 'function') {
      refreshFinanceMobile();
    }
    if (budgetDataTable) {
      budgetDataTable.ajax.reload(null, false);
    }
    if (typeof refreshBudgetMobile === 'function') {
      refreshBudgetMobile();
    }
    if (categoryDataTable) {
      categoryDataTable.ajax.reload(null, false);
    }
    if (typeof refreshCategoryMobile === 'function') {
      refreshCategoryMobile();
    }

    const period = $('#budget-period-select').val() || '';
    $.get(`/finances/summary?period=${encodeURIComponent(period)}`, function(res) {
      if (!res || !res.success) return;

      $('#stat-total-balance').attr('data-real-balance', res.formatted_balance).attr('title', res.formatted_balance);
      applyBalancePrivacy();
      $('#stat-total-income').text('+ ' + res.formatted_income).attr('title', '+ ' + res.formatted_income);
      $('#stat-total-expense').text('- ' + res.formatted_expense).attr('title', '- ' + res.formatted_expense);
      $('#stat-total-saving').text(res.formatted_saving).attr('title', res.formatted_saving);

      if (res.categories && Array.isArray(res.categories)) {
        res.categories.forEach(cat => {
          if (typeof window.updateCategoryInCombobox === 'function') {
            window.updateCategoryInCombobox(cat);
          }
        });
      }

      const $goalsSection = $('#finance-goals-section');
      if ($goalsSection.length) {
        $.get(window.location.href, function(html) {
          const freshGoals = $(html).find('#finance-goals-section').html();
          if (freshGoals) $goalsSection.html(freshGoals);
        });
      }


      fetchReportData();
    });
  };
  window.reloadCurrentModuleData = window.refreshFinanceData;

  $(document).ready(function() {
    // Pulihkan tab aktif dari sessionStorage
    const savedFinanceTab = sessionStorage.getItem('famkoji_finance_active_tab');
    if (savedFinanceTab && ['trx', 'budget', 'goals', 'rekap'].includes(savedFinanceTab)) {
      switchFinanceTab(savedFinanceTab);
    }
    // Terapkan preferensi privasi saldo
    applyBalancePrivacy();

    // Inisialisasi Flatpickr Tanggal Transaksi
    if (typeof flatpickr !== 'undefined') {
      financeDatePicker = flatpickr("#finance-input-date", {
        locale: "id",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j F Y",
        defaultDate: new Date()
      });

      if (document.getElementById('goal-input-date')) {
        goalDatePicker = flatpickr("#goal-input-date", {
          locale: "id",
          dateFormat: "Y-m-d",
          altInput: true,
          altFormat: "j F Y"
        });
      }
    }

    // Inisialisasi Drag & Drop File Upload
    if (typeof initDragAndDrop === 'function') {
      initDragAndDrop();
    }

    // Inisialisasi Server-Side jQuery DataTables dengan Tema Neobrutalisme
    window.financeDataTable = financeDataTable = $('#finance-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('finances.paginate') }}",
        type: 'GET'
      },
      pageLength: 5,
      lengthMenu: [5, 10, 25, 50],
      language: {
        search: "Cari Transaksi:",
        lengthMenu: "Tampil _MENU_ data",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ transaksi",
        infoEmpty: "Menampilkan 0 transaksi",
        infoFiltered: "(disaring dari _MAX_ total data)",
        emptyTable: "Belum ada catatan transaksi kas.",
        processing: "Memuat data kas...",
        paginate: {
          first: "&laquo;",
          previous: "&larr; Prev",
          next: "Next &rarr;",
          last: "&raquo;"
        }
      },
      columns: [
        {
          data: 'formatted_date',
          name: 'transaction_date',
          className: 'col-date p-2.5 font-mono text-ink',
          render: function(data, type, row) {
            return `<span class="inline-flex items-center gap-1"><span class="md:hidden text-ink/60">🗓️</span><span>${escapeHtml(data)}</span></span>`;
          }
        },
        {
          data: 'category',
          name: 'category_id',
          className: 'col-cat p-2.5',
          render: function(data, type, row) {
            const badgeClass = row.type === 'income' ? 'bg-brand text-white' : 'bg-sage text-ink';
            const icon = row.category_icon || (row.type === 'income' ? '💰' : '📦');
            return `<span class="px-2 py-0.5 ${badgeClass} brutal-border text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1"><span>${icon}</span><span>${escapeHtml(data)}</span></span>`;
          }
        },
        {
          data: 'description',
          name: 'description',
          className: 'col-desc p-2.5 font-bold text-ink',
          render: function(data, type, row) {
            let html = `<div class="text-xs sm:text-sm font-black text-ink leading-tight">${escapeHtml(data)}</div>`;
            if (row.receipt_url) {
              html += `
                <button type="button" onclick="showImagePreview('${row.receipt_url}', 'Struk: ${escapeHtml(row.description)}')" class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 bg-brand-soft hover:bg-brand hover:text-white brutal-border text-[10px] font-mono font-bold transition-colors">
                  📎 Struk
                </button>
              `;
            }
            return html;
          }
        },
        {
          data: 'user',
          name: 'user_id',
          className: 'col-user p-2.5 text-ink',
          render: function(data, type, row) {
            return `<span class="inline-flex items-center gap-1 text-xs"><span class="md:hidden text-ink/50 text-[10px] font-mono">Oleh:</span><span class="font-bold text-ink">${escapeHtml(data)}</span></span>`;
          }
        },
        {
          data: 'formatted_amount',
          name: 'amount',
          className: 'col-amount p-2.5 text-right font-mono font-bold',
          render: function(data, type, row) {
            const colorClass = row.type === 'income' ? 'text-brand' : 'text-coral';
            const icon = row.type === 'income' ? '↙' : '↗';
            return `<div class="inline-flex items-center gap-1 text-xs sm:text-sm font-black ${colorClass}"><span class="text-[10px] md:hidden">${icon}</span><span>${data}</span></div>`;
          }
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'col-actions p-2.5 text-center',
          render: function(data, type, row) {
            const payload = JSON.stringify(row).replace(/"/g, '&quot;');
            return `
              <div class="flex items-center justify-end md:justify-center gap-1.5">
                <button 
                  type="button" 
                  onclick='openEditFinanceModal(${payload})' 
                  class="px-2 py-0.5 sm:px-2.5 sm:py-1 bg-accent hover:bg-white brutal-border text-[10px] font-bold transition-colors shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                  title="Edit Transaksi"
                >
                  ✏️ Edit
                </button>
                <button 
                  type="button" 
                  onclick="confirmDelete('${row.destroy_url}', 'transaksi ${escapeHtml(row.description)}')" 
                  class="px-2 py-0.5 sm:px-2.5 sm:py-1 bg-coral text-white hover:bg-ink brutal-border text-[10px] font-bold transition-colors shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                  title="Hapus Transaksi"
                >
                  🗑️ Hapus
                </button>
              </div>
            `;
          }
        }
      ]
    });

    // Inisialisasi Server-Side jQuery DataTables untuk Batas & Riwayat Anggaran Bulanan
    if ($('#budget-datatable').length) {
      window.budgetDataTable = budgetDataTable = $('#budget-datatable').DataTable({
      processing: true,
      serverSide: true,
      autoWidth: false,
      order: [[0, 'asc']],
      ajax: {
        url: "{{ route('finances.budgets.paginate') }}",
        type: 'GET',
        data: function(d) {
          d.period = $('#budget-period-select').val();
        }
      },
      pageLength: 5,
      lengthMenu: [5, 10, 25],
      language: {
        search: "Cari Anggaran:",
        lengthMenu: "Tampil _MENU_ data",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ anggaran",
        infoEmpty: "Menampilkan 0 anggaran",
        infoFiltered: "(disaring dari _MAX_ total data)",
        emptyTable: "Belum ada kategori pengeluaran.",
        processing: "Memuat anggaran bulanan...",
        paginate: {
          first: "&laquo;",
          previous: "&larr; Prev",
          next: "Next &rarr;",
          last: "&raquo;"
        }
      },
      columns: [
        {
          data: 'name',
          name: 'name',
          className: 'p-2.5',
          render: function(data, type, row) {
            const icon = row.icon || '🏷️';
            const specialBadge = row.has_custom_period_budget 
              ? `<span class="px-1.5 py-0.5 bg-accent text-ink brutal-border text-[9px] font-black font-mono">⭐ Khusus</span>` 
              : '';
            const notes = row.notes 
              ? `<div class="text-[10px] text-brand font-mono font-bold">📝 ${escapeHtml(row.notes)}</div>` 
              : '';
            return `
              <div class="flex items-center gap-2">
                <span class="text-base flex-shrink-0">${icon}</span>
                <div>
                  <div class="font-black text-ink flex items-center gap-1.5 flex-wrap">
                    <span>${escapeHtml(data)}</span>
                    ${specialBadge}
                  </div>
                  ${notes}
                </div>
              </div>
            `;
          }
        },
        {
          data: 'formatted_budget',
          name: 'monthly_budget',
          className: 'p-2.5 font-mono',
          render: function(data, type, row) {
            const cls = row.has_budget ? 'text-ink font-bold' : 'text-ink/40 font-bold';
            return `<span class="${cls}">${data}</span>`;
          }
        },
        {
          data: 'formatted_spent',
          name: 'spent',
          className: 'p-2.5 font-mono',
          render: function(data, type, row) {
            const cls = row.is_over_budget ? 'text-coral font-bold' : 'text-ink font-bold';
            return `<span class="${cls}">${data}</span>`;
          }
        },
        {
          data: 'pct',
          name: 'pct',
          className: 'p-2.5',
          render: function(data, type, row) {
            if (!row.has_budget) {
              return `<span class="px-1.5 py-0.5 bg-cream text-ink/60 brutal-border text-[10px] font-mono">Tanpa Anggaran</span>`;
            }
            const barColor = row.is_over_budget ? 'bg-coral' : (row.pct > 80 ? 'bg-amber-500' : 'bg-brand');
            const statusLabel = row.is_over_budget
              ? `<span class="px-1.5 py-0.2 bg-coral text-white brutal-border text-[9px] font-black font-mono">🚨 OVER</span><span class="text-coral font-bold">+${row.formatted_over}</span>`
              : `<span class="px-1.5 py-0.2 bg-brand-soft text-brand brutal-border text-[9px] font-black font-mono">${row.pct}%</span><span class="text-brand font-bold">Sisa: ${row.formatted_remaining}</span>`;

            return `
              <div class="space-y-1">
                <div class="flex items-center justify-between text-[11px] font-mono">
                  ${statusLabel}
                </div>
                <div class="w-full bg-cream h-2 brutal-border overflow-hidden">
                  <div class="${barColor} h-full" style="width: ${Math.min(100, row.pct)}%"></div>
                </div>
              </div>
            `;
          }
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'p-2.5 text-center',
          render: function(data, type, row) {
            const catPayload = JSON.stringify({
              id: row.id,
              name: row.name,
              type: 'expense',
              icon: row.icon || '🏷️',
              monthly_budget: row.monthly_budget,
              is_custom: row.is_custom
            }).replace(/"/g, '&quot;');
            const nameSafe = escapeHtml(row.name || '');

            let actions = `
              <div class="flex items-center justify-center gap-1.5">
                <button 
                  type="button" 
                  onclick='openEditCategoryForm(${catPayload})' 
                  class="px-2.5 py-1 bg-accent hover:bg-white text-ink brutal-border text-[10px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                  title="Edit Kategori & Anggaran"
                >
                  ✏️ Edit
                </button>
            `;
            if (row.is_custom && (row.name || '').toLowerCase().trim() !== 'lain-lain') {
              actions += `
                <button 
                  type="button" 
                  onclick="confirmDelete('/finances/categories/${row.id}', 'kategori ${nameSafe} (transaksi dialihkan ke Lain-lain)')" 
                  class="px-2 py-1 bg-coral hover:bg-ink text-white brutal-border text-[10px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                  title="Hapus Kategori"
                >
                  🗑️
                </button>
              `;
            }
            actions += `</div>`;
            return actions;
          }
        }
      ]
    });
    }

    // Event filter period select untuk DataTable Anggaran
    $('#budget-period-select').on('change', function() {
      const selected = $(this).val();
      const label = $(this).find('option:selected').data('label') || selected;
      const currentPeriod = "{{ now()->format('Y-m') }}";

      if (selected !== currentPeriod) {
        $('#budget-period-badge').removeClass('hidden');
        $('#budget-period-label').text(label);
      } else {
        $('#budget-period-badge').addClass('hidden');
      }

      if (budgetDataTable) {
        budgetDataTable.ajax.reload();
      }
      refreshBudgetMobile();
    });
  });

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // MOBILE CARD FEED & PAGINATION (LOAD MORE) STATE
  let financeMobileStart = {{ $initialCount }};
  const financeMobileLength = 5;
  let financeMobileTotal = {{ $totalTrx }};
  let financeMobileFilterType = 'all';
  let financeMobileSearch = '';
  let financeMobileLoading = false;

  function renderMobileFinanceCard(row) {
    const isIncome = row.type === 'income';
    const badgeClass = isIncome ? 'bg-brand text-white' : 'bg-sage text-ink';
    const categoryLabel = row.category || (isIncome ? 'Pemasukan' : 'Pengeluaran');
    const amountClass = isIncome ? 'text-brand' : 'text-coral';
    const amountSign = isIncome ? '+' : '-';
    const payload = JSON.stringify(row).replace(/"/g, '&quot;');
    const receiptBtn = row.receipt_url ? `
      <button type="button" onclick="showImagePreview('${row.receipt_url}', 'Struk: ${escapeHtml(row.description)}')" class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-soft hover:bg-brand hover:text-white brutal-border text-[10px] font-mono font-bold transition-colors">
        📎 Struk
      </button>
    ` : '';

    return `
      <div class="bg-white brutal-card p-3.5 border-2 border-ink shadow-brutal-sm mobile-card-item transition-all" data-id="${row.id}">
        <div class="flex items-center justify-between gap-2 pb-2 border-b border-ink/15">
          <span class="px-2.5 py-0.5 ${badgeClass} brutal-border text-[10px] font-black uppercase tracking-wider truncate inline-flex items-center gap-1">
            <span>${row.category_icon || (isIncome ? '💰' : '📦')}</span>
            <span>${escapeHtml(categoryLabel)}</span>
          </span>
          <div class="flex items-center gap-1.5 flex-shrink-0">
            <span class="font-mono text-[10px] font-bold text-ink/70 flex items-center gap-0.5">
              <span>🗓️</span>
              <span>${escapeHtml(row.formatted_date || row.transaction_date)}</span>
            </span>
            <div class="flex items-center gap-1 ml-1">
              <button 
                type="button" 
                onclick='openEditFinanceModal(${payload})' 
                class="w-6 h-6 bg-cream hover:bg-accent text-ink brutal-border flex items-center justify-center text-[10px] shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                title="Edit Transaksi"
              >
                ✏️
              </button>
              <button 
                type="button" 
                onclick="confirmDelete('${row.destroy_url}', 'transaksi ${escapeHtml(row.description)}')" 
                class="w-6 h-6 bg-cream hover:bg-coral hover:text-white text-ink brutal-border flex items-center justify-center text-[10px] shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                title="Hapus Transaksi"
              >
                🗑️
              </button>
            </div>
          </div>
        </div>

        <div class="pt-2.5 flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <h4 class="text-sm font-black text-ink leading-snug break-words">${escapeHtml(row.description)}</h4>
            <div class="mt-1 flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-ink/70">
                <span class="text-ink/50">👤</span> ${escapeHtml(row.user || 'Keluarga')}
              </span>
              ${receiptBtn}
            </div>
          </div>
          <div class="text-right flex-shrink-0">
            <span class="text-[9px] font-mono font-black block text-ink/50 uppercase">${isIncome ? 'Masuk' : 'Keluar'}</span>
            <span class="font-mono text-sm sm:text-base font-black ${amountClass} leading-tight block">
              ${amountSign} Rp ${Number(row.amount).toLocaleString('id-ID')}
            </span>
          </div>
        </div>
      </div>
    `;
  }

  function fetchFinanceMobileData(append = false) {
    if (financeMobileLoading) return;
    financeMobileLoading = true;

    const $btn = $('#finance-mobile-load-more');
    const origBtnHtml = $btn.html();
    $btn.prop('disabled', true).html('<span>⏳ Memuat data kas...</span>');

    const fetchStart = append ? financeMobileStart : 0;
    const filterTypeParam = (financeMobileFilterType === 'all') ? '' : financeMobileFilterType;

    $.ajax({
      url: "{{ route('finances.paginate') }}",
      type: 'GET',
      data: {
        draw: 1,
        start: fetchStart,
        length: financeMobileLength,
        search: financeMobileSearch,
        type: filterTypeParam
      },
      dataType: 'json',
      success: function(res) {
        const rows = res.data || [];
        financeMobileTotal = res.recordsFiltered !== undefined ? res.recordsFiltered : (res.total || 0);

        if (!append) {
          $('#finance-mobile-card-list').empty();
          financeMobileStart = 0;
        }

        if (rows.length === 0 && !append) {
          $('#finance-mobile-card-list').html(`
            <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
              Tidak ada catatan transaksi yang sesuai filter.
            </div>
          `);
        } else {
          rows.forEach(function(row) {
            $('#finance-mobile-card-list').append(renderMobileFinanceCard(row));
          });
          financeMobileStart += rows.length;
        }

        // Update progress counter
        $('#finance-mobile-count').text(financeMobileStart);
        $('#finance-mobile-total').text(financeMobileTotal);

        if (financeMobileStart >= financeMobileTotal || rows.length === 0) {
          $('#finance-mobile-load-more').addClass('hidden');
          $('#finance-mobile-all-loaded').removeClass('hidden');
        } else {
          $('#finance-mobile-load-more').removeClass('hidden');
          $('#finance-mobile-all-loaded').addClass('hidden');
        }
      },
      error: function(err) {
        console.error('Gagal mengambil data transaksi kas mobile:', err);
      },
      complete: function() {
        financeMobileLoading = false;
        $btn.prop('disabled', false).html(origBtnHtml);
      }
    });
  }

  function loadMoreFinanceMobile() {
    fetchFinanceMobileData(true);
  }

  function refreshFinanceMobile() {
    financeMobileStart = 0;
    fetchFinanceMobileData(false);
  }

  $(document).ready(function() {
    // Quick filter pills
    $(document).on('click', '.btn-mfilter-type', function() {
      $('.btn-mfilter-type').removeClass('bg-ink text-white font-black').addClass('bg-white text-ink font-bold');
      $(this).removeClass('bg-white text-ink font-bold').addClass('bg-ink text-white font-black');
      financeMobileFilterType = $(this).data('type') || 'all';
      refreshFinanceMobile();
    });

    // Quick search with debounce
    let mobileSearchTimer = null;
    $(document).on('input', '#finance-mobile-search', function() {
      clearTimeout(mobileSearchTimer);
      const query = $(this).val();
      mobileSearchTimer = setTimeout(function() {
        financeMobileSearch = query;
        refreshFinanceMobile();
      }, 350);
    });

    // Mobile Budget filter pills
    $(document).on('click', '.btn-bfilter-status', function() {
      $('.btn-bfilter-status').removeClass('bg-ink text-white font-black').addClass('bg-white text-ink font-bold');
      $(this).removeClass('bg-white text-ink font-bold').addClass('bg-ink text-white font-black');
      budgetMobileFilterStatus = $(this).data('status') || 'all';
      refreshBudgetMobile();
    });

    // Mobile Budget search debounce
    let budgetSearchTimer = null;
    $(document).on('input', '#budget-mobile-search', function() {
      clearTimeout(budgetSearchTimer);
      const query = $(this).val();
      budgetSearchTimer = setTimeout(function() {
        budgetMobileSearch = query;
        refreshBudgetMobile();
      }, 350);
    });

    // Mobile Category filter pills
    $(document).on('click', '.btn-cat-filter-type', function() {
      $('.btn-cat-filter-type').removeClass('bg-ink text-white font-black').addClass('bg-white text-ink font-bold');
      $(this).removeClass('bg-white text-ink font-bold').addClass('bg-ink text-white font-black');
      categoryMobileFilterType = $(this).data('type') || 'all';
      refreshCategoryMobile();
    });

    // Mobile Category search debounce
    let catSearchTimer = null;
    $(document).on('input', '#category-mobile-search', function() {
      clearTimeout(catSearchTimer);
      const query = $(this).val();
      catSearchTimer = setTimeout(function() {
        categoryMobileSearch = query;
        refreshCategoryMobile();
      }, 350);
    });
  });

  // ================= MOBILE BUDGET CARDS (PWA) =================
  let budgetMobileStart = {{ min(5, count($categoryBudgets)) }};
  const budgetMobileLength = 5;
  let budgetMobileTotal = {{ count($categoryBudgets) }};
  let budgetMobileFilterStatus = 'all';
  let budgetMobileSearch = '';
  let budgetMobileLoading = false;

  function renderMobileBudgetCard(row) {
    const hasBudget = row.has_budget;
    const isOver = row.is_over_budget;
    const barColor = isOver ? 'bg-coral' : (row.pct > 80 ? 'bg-amber-500' : 'bg-brand');
    const badgeHtml = hasBudget
      ? (isOver
        ? `<span class="px-2 py-0.5 bg-coral text-white brutal-border text-[9px] font-black font-mono animate-pulse">🚨 OVER</span>`
        : `<span class="px-2 py-0.5 bg-brand-soft text-brand brutal-border text-[9px] font-black font-mono">${row.pct}%</span>`)
      : `<span class="px-2 py-0.5 bg-white text-ink/60 brutal-border text-[9px] font-bold font-mono">Tanpa Batas</span>`;

    const customBadge = row.has_custom_period_budget
      ? `<span class="px-1.5 py-0.2 bg-accent text-ink brutal-border text-[9px] font-black font-mono">⭐ Anggaran Khusus</span>`
      : '';

    const notesHtml = row.notes
      ? `<div class="text-[10px] text-brand font-mono font-bold pt-0.5 truncate">📝 Catatan: ${escapeHtml(row.notes)}</div>`
      : '';

    const budgetBar = hasBudget ? `
      <div class="w-full bg-cream h-2.5 brutal-border overflow-hidden">
        <div class="${barColor} h-full transition-all duration-300" style="width: ${Math.min(100, row.pct)}%"></div>
      </div>
      <div class="flex justify-between items-center text-[10px] font-mono">
        ${isOver
          ? `<span class="text-coral font-bold">Kelebihan: +${row.formatted_over}</span>`
          : `<span class="text-brand font-bold">Sisa: ${row.formatted_remaining}</span>`}
      </div>
    ` : '';

    const notesSafe = (row.notes || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    const nameSafe = (row.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');

    return `
      <div class="bg-white brutal-card p-3.5 border-2 border-ink shadow-brutal-sm mobile-budget-card space-y-2.5 transition-all ${isOver ? 'ring-2 ring-coral' : ''}" data-id="${row.id}">
        <div class="flex items-start justify-between gap-2 pb-2 border-b border-ink/15">
          <div class="flex items-center gap-2 min-w-0">
            <span class="text-xl flex-shrink-0">${row.icon || '🏷️'}</span>
            <div class="min-w-0">
              <h4 class="font-black text-sm text-ink truncate">${escapeHtml(row.name)}</h4>
              ${customBadge}
            </div>
          </div>
          <div class="flex-shrink-0">
            ${badgeHtml}
          </div>
        </div>

        <div class="space-y-1">
          <div class="flex justify-between items-baseline text-xs font-mono font-bold">
            <span class="${isOver ? 'text-coral' : 'text-ink'}">Terpakai: ${row.formatted_spent}</span>
            <span class="text-ink/60 text-[11px]">Anggaran: ${row.formatted_budget}</span>
          </div>
          ${budgetBar}
          ${notesHtml}
        </div>

        <div class="pt-2 border-t border-ink/15 flex items-center gap-2">
          <button 
            type="button" 
            onclick='openEditCategoryForm({ id: ${row.id}, name: "${nameSafe}", type: "expense", icon: "${row.icon || '🏷️'}", monthly_budget: ${row.monthly_budget || 0}, is_custom: ${row.is_custom ? 'true' : 'false'} })' 
            class="flex-1 py-2 bg-accent hover:bg-white text-ink brutal-border text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
          >
            <span>✏️ Edit Kategori & Anggaran</span>
          </button>
          ${(row.is_custom && (row.name || '').toLowerCase().trim() !== 'lain-lain') ? `
            <button 
              type="button" 
              onclick="confirmDelete('/finances/categories/${row.id}', 'kategori ${nameSafe} (transaksi dialihkan ke Lain-lain)')" 
              class="px-3 py-2 bg-coral hover:bg-ink text-white brutal-border text-xs font-black uppercase shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
              title="Hapus Kategori"
            >
              <span>🗑️</span>
            </button>
          ` : ''}
        </div>
      </div>
    `;
  }

  function fetchBudgetMobileData(append = false) {
    if (budgetMobileLoading) return;
    budgetMobileLoading = true;

    const $btn = $('#budget-mobile-load-more');
    const origHtml = $btn.html();
    $btn.prop('disabled', true).html('<span>⏳ Memuat anggaran...</span>');

    const fetchStart = append ? budgetMobileStart : 0;
    const filterStatusParam = (budgetMobileFilterStatus === 'all') ? '' : budgetMobileFilterStatus;
    const periodVal = $('#budget-period-select').val();

    $.ajax({
      url: "{{ route('finances.budgets.paginate') }}",
      type: 'GET',
      data: {
        draw: 1,
        start: fetchStart,
        length: budgetMobileLength,
        search: budgetMobileSearch,
        status: filterStatusParam,
        period: periodVal
      },
      dataType: 'json',
      success: function(res) {
        const rows = res.data || [];
        budgetMobileTotal = res.recordsFiltered !== undefined ? res.recordsFiltered : (res.recordsTotal || 0);

        if (!append) {
          $('#budget-mobile-card-list').empty();
          budgetMobileStart = 0;
        }

        if (rows.length === 0 && !append) {
          $('#budget-mobile-card-list').html(`
            <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
              Tidak ada data anggaran yang sesuai filter.
            </div>
          `);
        } else {
          rows.forEach(function(row) {
            $('#budget-mobile-card-list').append(renderMobileBudgetCard(row));
          });
          budgetMobileStart += rows.length;
        }

        $('#budget-mobile-count').text(budgetMobileStart);
        $('#budget-mobile-total').text(budgetMobileTotal);

        if (budgetMobileStart >= budgetMobileTotal || rows.length === 0) {
          $('#budget-mobile-load-more').addClass('hidden');
          $('#budget-mobile-all-loaded').removeClass('hidden');
        } else {
          $('#budget-mobile-load-more').removeClass('hidden');
          $('#budget-mobile-all-loaded').addClass('hidden');
        }
      },
      error: function(err) {
        console.error('Gagal memuat anggaran mobile:', err);
      },
      complete: function() {
        budgetMobileLoading = false;
        $btn.prop('disabled', false).html(origHtml);
      }
    });
  }

  function loadMoreBudgetMobile() {
    fetchBudgetMobileData(true);
  }

  function refreshBudgetMobile() {
    budgetMobileStart = 0;
    fetchBudgetMobileData(false);
  }

  // ================= MOBILE MASTER KATEGORI CARDS (PWA) =================
  let categoryMobileStart = {{ isset($categories) ? min(5, count($categories)) : 0 }};
  const categoryMobileLength = 5;
  let categoryMobileTotal = {{ isset($categories) ? count($categories) : 0 }};
  let categoryMobileFilterType = 'all';
  let categoryMobileSearch = '';
  let categoryMobileLoading = false;

  function renderMobileCategoryCard(row) {
    const isExpense = row.type === 'expense';
    const badgeClass = isExpense ? 'bg-coral text-white' : 'bg-brand text-white';
    const catJson = JSON.stringify(row).replace(/"/g, '&quot;');
    const nameEsc = escapeHtml(row.name);
    const isLainLain = (row.name || '').toLowerCase().trim() === 'lain-lain';
    const deleteBtn = (row.is_custom && !isLainLain) ? `
      <button 
        type="button" 
        onclick="confirmDelete('${row.destroy_url}', 'kategori ${nameEsc} (transaksi & anggarannya dialihkan ke Lain-lain)')" 
        class="px-2.5 py-1 bg-coral hover:bg-ink text-white brutal-border text-[11px] font-bold text-center shadow-brutal-sm"
      >
        🗑️ Hapus
      </button>
    ` : '';

    return `
      <div class="p-3 bg-cream brutal-border space-y-2 mobile-category-card" data-id="${row.id}">
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-2 min-w-0">
            <span class="text-xl flex-shrink-0">${row.icon || '🏷️'}</span>
            <span class="font-black text-xs sm:text-sm text-ink truncate">${nameEsc}</span>
          </div>
          <span class="px-2 py-0.5 ${badgeClass} brutal-border text-[9px] font-bold uppercase font-mono flex-shrink-0">
            ${escapeHtml(row.type_label)}
          </span>
        </div>
        <div class="text-[11px] font-mono text-ink/70">
          Anggaran: <span class="font-bold ${row.monthly_budget > 0 ? 'text-brand' : 'text-ink/50'}">${row.formatted_budget}</span>
        </div>
        <div class="pt-2 border-t border-ink/15 flex items-center justify-end gap-1.5">
          <button 
            type="button" 
            onclick='openEditCategoryForm(${catJson})' 
            class="px-2.5 py-1 bg-accent hover:bg-white text-ink brutal-border text-[11px] font-bold text-center shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
          >
            ✏️ Edit
          </button>
          ${deleteBtn}
        </div>
      </div>
    `;
  }

  function fetchCategoryMobileData(append = false) {
    if (categoryMobileLoading) return;
    categoryMobileLoading = true;

    const $btn = $('#category-mobile-load-more');
    const origHtml = $btn.html();
    $btn.prop('disabled', true).html('<span>⏳ Memuat kategori...</span>');

    const fetchStart = append ? categoryMobileStart : 0;
    const filterTypeParam = (categoryMobileFilterType === 'all') ? '' : categoryMobileFilterType;

    $.ajax({
      url: "{{ route('finances.categories.paginate') }}",
      type: 'GET',
      data: {
        draw: 1,
        start: fetchStart,
        length: categoryMobileLength,
        search: categoryMobileSearch,
        type: filterTypeParam
      },
      dataType: 'json',
      success: function(res) {
        const rows = res.data || [];
        categoryMobileTotal = res.recordsFiltered !== undefined ? res.recordsFiltered : (res.recordsTotal || 0);

        if (!append) {
          $('#category-mobile-card-list').empty();
          categoryMobileStart = 0;
        }

        if (rows.length === 0 && !append) {
          $('#category-mobile-card-list').html(`
            <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
              Tidak ada kategori yang sesuai filter.
            </div>
          `);
        } else {
          rows.forEach(function(row) {
            $('#category-mobile-card-list').append(renderMobileCategoryCard(row));
          });
          categoryMobileStart += rows.length;
        }

        $('#category-mobile-count').text(categoryMobileStart);
        $('#category-mobile-total').text(categoryMobileTotal);

        if (categoryMobileStart >= categoryMobileTotal || rows.length === 0) {
          $('#category-mobile-load-more').addClass('hidden');
          $('#category-mobile-all-loaded').removeClass('hidden');
        } else {
          $('#category-mobile-load-more').removeClass('hidden');
          $('#category-mobile-all-loaded').addClass('hidden');
        }
      },
      error: function(err) {
        console.error('Gagal memuat kategori mobile:', err);
      },
      complete: function() {
        categoryMobileLoading = false;
        $btn.prop('disabled', false).html(origHtml);
      }
    });
  }

  function loadMoreCategoryMobile() {
    fetchCategoryMobileData(true);
  }

  function refreshCategoryMobile() {
    categoryMobileStart = 0;
    fetchCategoryMobileData(false);
  }

  function setFinanceType(type) {
    currentModalType = type;
    $('#finance-input-type').val(type);
    $('#btn-ftype-income').removeClass('bg-brand text-white').addClass('bg-cream text-ink');
    $('#btn-ftype-expense').removeClass('bg-coral text-white').addClass('bg-cream text-ink');
    $('#btn-ftype-saving').removeClass('bg-emerald-600 text-white').addClass('bg-cream text-ink');

    if (type === 'income') {
      $('#btn-ftype-income').removeClass('bg-cream text-ink').addClass('bg-brand text-white');
      $('#mlabel-title').text('Keterangan Pemasukan:');
      $('#finance-input-description').attr('placeholder', 'misal: Gaji bulanan atau hasil jualan online');
      $('#mlabel-extra').text('Nominal Pemasukan (Rp):');
      $('#mlabel-upload').text('Foto Bukti Transfer / Slip (Opsional):');
    } else if (type === 'saving') {
      $('#btn-ftype-saving').removeClass('bg-cream text-ink').addClass('bg-emerald-600 text-white');
      $('#mlabel-title').text('Keterangan Tabungan:');
      $('#finance-input-description').attr('placeholder', 'misal: Setor tabungan darurat atau celengan');
      $('#mlabel-extra').text('Nominal Tabungan (Rp):');
      $('#mlabel-upload').text('Foto Bukti Setor / Transfer (Opsional):');
    } else {
      $('#btn-ftype-expense').removeClass('bg-cream text-ink').addClass('bg-coral text-white');
      $('#mlabel-title').text('Keterangan Pengeluaran:');
      $('#finance-input-description').attr('placeholder', 'misal: Belanja sayur mingguan pasar');
      $('#mlabel-extra').text('Nominal Pengeluaran (Rp):');
      $('#mlabel-upload').text('Foto Struk / Bukti Kas (Opsional):');
    }

    const activeCats = typeof getActiveCategoryList === 'function' ? getActiveCategoryList() : [];
    const currentVal = $('#minput-category').val();
    const isCurrentInActive = activeCats.some(c => String(c.id !== undefined ? c.id : c.value) === String(currentVal));
    if (!isCurrentInActive) {
      if (activeCats.length > 0) {
        selectComboboxOption('category', activeCats[0].id !== undefined ? activeCats[0].id : activeCats[0].value, activeCats[0].text);
      } else {
        selectComboboxOption('category', '', 'Pilih kategori...');
      }
    }
  }

  function openFinanceModal() {
    const canViewAll = @json($canViewAllFinances ?? true);
    $('#finance-modal-form')[0].reset();
    $('#finance-modal-method').val('POST');
    $('#finance-modal-form').attr('action', "{{ route('finances.store') }}");
    $('#finance-modal-title').text(canViewAll ? 'Catat Kas & Pengeluaran' : 'Catat Pengeluaran');
    $('#finance-modal-icon').text('💸');
    $('#finance-btn-submit').html(canViewAll ? 'Simpan Transaksi &rarr;' : 'Simpan Pengeluaran &rarr;');
    
    // Reset file preview
    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#finance-input-remove-receipt').val('0');

    // Default type
    setFinanceType('expense');

    // Default assignee
    @php
      $currentUserRole = Auth::user()->role ?? 'Anggota';
      $currentUserEmoji = match($currentUserRole) {
        'Ayah' => '👨',
        'Ibu' => '👩',
        'Anak' => '👦',
        default => '👧'
      };
    @endphp
    selectComboboxOption('assignee', "{{ Auth::id() }}", "{{ $currentUserEmoji }} {{ Auth::user()->name }} ({{ $currentUserRole }})");

    // Default category
    const activeCats = typeof getActiveCategoryList === 'function' ? getActiveCategoryList() : [];
    if (activeCats && activeCats.length > 0) {
      selectComboboxOption('category', activeCats[0].id !== undefined ? activeCats[0].id : activeCats[0].value, activeCats[0].text);
    } else {
      selectComboboxOption('category', '', 'Pilih kategori...');
    }

    if (financeDatePicker) financeDatePicker.setDate(new Date());

    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
    $('#finance-modal').removeClass('hidden');
  }

  function closeFinanceModal() {
    $('#finance-modal').addClass('hidden');
    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
  }

  function openEditFinanceModal(trx) {
    const canViewAll = @json($canViewAllFinances ?? true);
    $('#finance-modal-form')[0].reset();
    $('#finance-modal-method').val('PUT');
    $('#finance-modal-form').attr('action', trx.update_url);
    $('#finance-modal-title').text(canViewAll ? 'Edit Transaksi Kas' : 'Edit Pengeluaran');
    $('#finance-modal-icon').text('✏️');
    $('#finance-btn-submit').html(canViewAll ? 'Perbarui Transaksi &rarr;' : 'Perbarui Pengeluaran &rarr;');
    
    // Set type
    setFinanceType(trx.type);
    
    // Set description & amount
    $('#finance-input-description').val(trx.description);
    $('#finance-input-amount').val(formatRupiah(trx.amount));
    
    // Set assignee
    const member = (comboboxData && comboboxData.assignee) ? comboboxData.assignee.find(m => String(m.id !== undefined ? m.id : m.value) === String(trx.user_id)) : null;
    if (member) {
      selectComboboxOption('assignee', member.id !== undefined ? member.id : member.value, member.text);
    } else if (trx.user) {
      selectComboboxOption('assignee', trx.user_id || "{{ Auth::id() }}", `👨 ${trx.user}`);
    } else {
      selectComboboxOption('assignee', "{{ Auth::id() }}", "{{ $currentUserEmoji }} {{ Auth::user()->name }} ({{ $currentUserRole }})");
    }

    // Set category
    const catList = typeof getActiveCategoryList === 'function' ? getActiveCategoryList() : [];
    let selectedCat = catList.find(c => String(c.id !== undefined ? c.id : c.value) === String(trx.category_id));
    if (!selectedCat && trx.category) {
      selectedCat = catList.find(c => c.text.includes(trx.category) || c.value === trx.category);
    }
    if (selectedCat) {
      selectComboboxOption('category', selectedCat.id !== undefined ? selectedCat.id : selectedCat.value, selectedCat.text);
    } else if (trx.category_id) {
      selectComboboxOption('category', trx.category_id, trx.category ? `🏷️ ${trx.category}` : 'Kategori Terpilih');
    } else if (trx.category) {
      selectComboboxOption('category', trx.category, `🏷️ ${trx.category}`);
    } else {
      selectComboboxOption('category', '', 'Pilih kategori...');
    }

    if (trx.transaction_date && financeDatePicker) {
      financeDatePicker.setDate(trx.transaction_date);
    }

    // Receipt preview
    const fileInput = document.getElementById('minput-file');
    if (fileInput) fileInput.value = '';
    const cameraInput = document.getElementById('minput-camera');
    if (cameraInput) cameraInput.value = '';
    $('#finance-input-remove-receipt').val('0');

    if (trx.receipt_url) {
      document.getElementById('upload-preview-img').src = trx.receipt_url;
      document.getElementById('upload-file-name').innerText = 'Foto Struk / Bukti Tersimpan';
      document.getElementById('upload-file-size').innerText = 'Tersimpan';
      document.getElementById('upload-dropzone').classList.add('hidden');
      document.getElementById('upload-preview-card').classList.remove('hidden');
    } else {
      if (typeof removeUploadedFile === 'function') removeUploadedFile();
    }

    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
    $('#finance-modal').removeClass('hidden');
  }

  // Handle Form Submit via AJAX
  $('#finance-modal-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#finance-btn-submit');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    const rawAmount = $('#finance-input-amount').val().replace(/[^0-9]/g, '');
    const formData = new FormData($form[0]);
    formData.set('amount', rawAmount || '0');
    const cameraInput = document.getElementById('minput-camera');
    if (cameraInput && cameraInput.files && cameraInput.files[0] && (!formData.get('receipt') || formData.get('receipt').size === 0)) {
      formData.set('receipt', cameraInput.files[0]);
    }

    $btn.prop('disabled', true).html('Menyimpan...');
    showLoading('Menyimpan data kas...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        closeFinanceModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Transaksi berhasil disimpan!');
        if (typeof window.refreshFinanceData === 'function') {
          window.refreshFinanceData();
        } else if (financeDataTable) {
          financeDataTable.ajax.reload(null, false);
        }
      },
      error: function(xhr) {
        let msg = 'Gagal menyimpan transaksi.';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
          if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
        }
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else {
          alert(msg);
        }
      },
      complete: function() {
        $btn.prop('disabled', false).html(origText);
        hideLoading();
      }
    });
  });

  // Global helper fallback
  window.openModal = function(type) {
    if (type === 'expense') openFinanceModal();
    else if (window.openChoreModal) window.openChoreModal();
    else if (window.openCalendarModal) window.openCalendarModal();
    else if (window.openTimelineModal) window.openTimelineModal();
  };

  // Compatibility helpers
  window.moduleFormHandlers = window.moduleFormHandlers || {};
  window.moduleFormHandlers['expense'] = function(ctx) {
    openFinanceModal();
  };

  function fetchFinancePage(page = 1) {
    if (financeDataTable) {
      financeDataTable.page(page - 1).draw('page');
    }
  }

  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_create') === '1') {
      setTimeout(openFinanceModal, 200);
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  });

  // ================= CATEGORY & BUDGET JS =================
  let incomeCategoryDataTable = null;

  function switchCategorySection(section) {
    if (section === 'income') {
      $('#subtab-cat-income').removeClass('bg-cream text-ink').addClass('bg-ink text-white');
      $('#subtab-cat-expense').removeClass('bg-ink text-white').addClass('bg-cream text-ink');
      $('#category-section-expense').addClass('hidden');
      $('#category-section-income').removeClass('hidden');
      initIncomeCategoryDataTable();
    } else {
      $('#subtab-cat-expense').removeClass('bg-cream text-ink').addClass('bg-ink text-white');
      $('#subtab-cat-income').removeClass('bg-ink text-white').addClass('bg-cream text-ink');
      $('#category-section-income').addClass('hidden');
      $('#category-section-expense').removeClass('hidden');
      if (budgetDataTable) {
        budgetDataTable.columns.adjust().draw();
      }
    }
  }

  function initIncomeCategoryDataTable() {
    if (incomeCategoryDataTable) {
      incomeCategoryDataTable.columns.adjust().draw();
      incomeCategoryDataTable.ajax.reload(null, false);
      loadIncomeCategoryMobile();
      return;
    }

    incomeCategoryDataTable = $('#income-category-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('finances.categories.paginate') }}",
        type: 'GET',
        data: function(d) {
          d.type = 'income';
        }
      },
      pageLength: 5,
      lengthMenu: [5, 10, 25],
      language: {
        search: "Cari Kategori:",
        lengthMenu: "Tampil _MENU_ data",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ kategori",
        infoEmpty: "Menampilkan 0 kategori",
        infoFiltered: "(disaring dari _MAX_ total data)",
        emptyTable: "Belum ada kategori pemasukan.",
        processing: "Memuat master pemasukan...",
        paginate: {
          first: "&laquo;",
          previous: "&larr; Prev",
          next: "Next &rarr;",
          last: "&raquo;"
        }
      },
      columns: [
        {
          data: 'name',
          name: 'name',
          className: 'p-2.5',
          render: function(data, type, row) {
            return `
              <div class="flex items-center gap-2">
                <span class="text-base flex-shrink-0">${row.icon || '💰'}</span>
                <span class="font-black text-ink">${escapeHtml(data)}</span>
              </div>
            `;
          }
        },
        {
          data: 'type_label',
          name: 'type',
          className: 'p-2.5',
          render: function() {
            return `<span class="px-1.5 py-0.5 bg-brand text-white brutal-border text-[9px] font-bold uppercase font-mono">Pemasukan</span>`;
          }
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'p-2.5 text-center',
          render: function(data, type, row) {
            const catJson = JSON.stringify(row).replace(/"/g, '&quot;');
            let html = `
              <div class="flex items-center justify-center gap-1.5">
                <button 
                  type="button" 
                  onclick='openEditCategoryForm(${catJson})' 
                  class="px-2.5 py-1 bg-accent hover:bg-white text-ink brutal-border text-[10px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                  title="Edit Kategori"
                >
                  ✏️ Edit
                </button>
            `;
            const isLainLain = (row.name || '').toLowerCase().trim() === 'lain-lain';
            if (row.is_custom && !isLainLain) {
              const nameEsc = escapeHtml(row.name);
              html += `
                <button 
                  type="button" 
                  onclick="confirmDelete('${row.destroy_url}', 'kategori ${nameEsc} (transaksi dialihkan ke Lain-lain)')" 
                  class="px-2 py-1 bg-coral hover:bg-ink text-white brutal-border text-[10px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                  title="Hapus Kategori"
                >
                  🗑️
                </button>
              `;
            }
            html += `</div>`;
            return html;
          }
        }
      ]
    });

    loadIncomeCategoryMobile();
  }

  function loadIncomeCategoryMobile() {
    const $container = $('#income-category-mobile-list');
    if (!$container.length) return;
    $container.html('<div class="p-4 text-center font-mono text-xs text-ink/60">Memuat kategori pemasukan...</div>');

    $.ajax({
      url: "{{ route('finances.categories.paginate') }}",
      type: 'GET',
      data: { type: 'income', length: 50 },
      success: function(res) {
        if (!res.data || !res.data.length) {
          $container.html('<div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">Belum ada kategori pemasukan terdaftar.</div>');
          return;
        }

        let html = '';
        res.data.forEach(function(cat) {
          const catJson = JSON.stringify(cat).replace(/"/g, '&quot;');
          const nameSafe = escapeHtml(cat.name);
          const isLainLain = (cat.name || '').toLowerCase().trim() === 'lain-lain';
          html += `
            <div class="p-3 bg-white brutal-border shadow-brutal-sm flex items-center justify-between gap-2">
              <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl flex-shrink-0">${cat.icon || '💰'}</span>
                <div class="min-w-0">
                  <div class="font-black text-xs sm:text-sm text-ink truncate">${nameSafe}</div>
                  <span class="px-1.5 py-0.2 bg-brand text-white brutal-border text-[9px] font-bold uppercase font-mono">Pemasukan</span>
                </div>
              </div>
              <div class="flex items-center gap-1.5 flex-shrink-0">
                <button 
                  type="button" 
                  onclick='openEditCategoryForm(${catJson})' 
                  class="px-2.5 py-1 bg-accent hover:bg-white text-ink brutal-border text-[11px] font-bold shadow-brutal-sm"
                >
                  ✏️ Edit
                </button>
                ${cat.is_custom && !isLainLain ? `
                  <button 
                    type="button" 
                    onclick="confirmDelete('${cat.destroy_url}', 'kategori ${nameSafe}')" 
                    class="px-2.5 py-1 bg-coral hover:bg-ink text-white brutal-border text-[11px] font-bold shadow-brutal-sm"
                  >
                    🗑️
                  </button>
                ` : ''}
              </div>
            </div>
          `;
        });
        $container.html(html);
      },
      error: function() {
        $container.html('<div class="p-4 text-center text-coral font-mono text-xs">Gagal memuat kategori pemasukan.</div>');
      }
    });
  }

  function openCreateCategoryModal(defaultType = 'expense') {
    resetCategoryForm();
    $('#category-modal-title').text('Tambah Kategori Baru');
    $('#category-modal-subtitle').text('Atur kategori transaksi kas dan batas belanja bulanan');
    $('#category-modal-icon-badge').text(defaultType === 'income' ? '💰' : '🏷️');
    $('#category-input-type').val(defaultType);
    $('#category-input-icon').val(defaultType === 'income' ? '💰' : '🏷️');
    handleCategoryTypeChange(defaultType);
    $('#category-btn-submit').html('Simpan Kategori &rarr;');
    $('#category-manage-modal').removeClass('hidden');
  }

  function openCategoryManageModal() {
    openCreateCategoryModal();
  }

  function closeCategoryManageModal() {
    $('#category-manage-modal').addClass('hidden');
  }

  function handleCategoryTypeChange(type) {
    if (type === 'income') {
      $('#category-budget-wrapper').addClass('hidden');
      $('#category-input-budget').val('');
      if ($('#category-input-icon').val() === '🏷️') {
        selectCategoryIcon('💰');
      }
    } else {
      $('#category-budget-wrapper').removeClass('hidden');
      if ($('#category-input-icon').val() === '💰') {
        selectCategoryIcon('🏷️');
      }
    }
  }

  function selectCategoryIcon(icon) {
    $('#category-input-icon').val(icon);
    $('#category-modal-icon-badge').text(icon);
  }

  function resetCategoryForm() {
    $('#category-modal-form')[0].reset();
    $('#category-input-id').val('');
    $('#category-modal-method').val('POST');
    $('#category-modal-form').attr('action', "{{ route('finances.categories.store') }}");
    $('#category-btn-submit').html('Simpan Kategori &rarr;');
    $('#category-input-icon').val('🏷️');
    $('#category-input-type').val('expense');
    $('#category-budget-wrapper').removeClass('hidden');
  }

  function openEditCategoryForm(cat) {
    resetCategoryForm();
    $('#category-input-id').val(cat.id);
    $('#category-modal-method').val('PUT');
    $('#category-modal-form').attr('action', `/finances/categories/${cat.id}`);
    $('#category-modal-title').text('Edit Kategori & Anggaran');
    $('#category-modal-subtitle').text('Ubah nama, ikon, atau batas belanja bulanan');
    $('#category-input-name').val(cat.name);
    const type = cat.type || 'expense';
    $('#category-input-type').val(type);
    const icon = cat.icon || (type === 'income' ? '💰' : '🏷️');
    $('#category-input-icon').val(icon);
    $('#category-modal-icon-badge').text(icon);
    if (cat.monthly_budget && Number(cat.monthly_budget) > 0) {
      $('#category-input-budget').val(formatRupiah(cat.monthly_budget));
    } else {
      $('#category-input-budget').val('');
    }
    handleCategoryTypeChange(type);
    $('#category-btn-submit').html('Perbarui Kategori &rarr;');
    $('#category-manage-modal').removeClass('hidden');
  }

  $('#category-modal-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#category-btn-submit');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    const rawBudget = $('#category-input-budget').val().replace(/[^0-9]/g, '');
    const formData = new FormData($form[0]);
    formData.set('monthly_budget', rawBudget || '0');

    $btn.prop('disabled', true).html('Menyimpan...');
    showLoading('Menyimpan kategori...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        if (typeof showToast === 'function') showToast(res.message || 'Kategori berhasil disimpan!');
        if (res.category && typeof window.updateCategoryInCombobox === 'function') {
          window.updateCategoryInCombobox(res.category);
        }
        closeCategoryManageModal();
        if (budgetDataTable) {
          budgetDataTable.ajax.reload(null, false);
        }
        refreshBudgetMobile();
        if (incomeCategoryDataTable) {
          incomeCategoryDataTable.ajax.reload(null, false);
        }
        loadIncomeCategoryMobile();
        if (typeof window.refreshFinanceData === 'function') {
          window.refreshFinanceData();
        }
      },
      error: function(xhr) {
        let msg = 'Gagal menyimpan kategori.';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
          if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
        }
        if (typeof showToast === 'function') showToast(msg, 'error');
        else alert(msg);
      },
      complete: function() {
        $btn.prop('disabled', false).html(origText);
        hideLoading();
      }
    });
  });

  // ================= GOAL & CELENGAN JS =================
  let goalDatePicker = null;

  function openGoalModal() {
    $('#goal-modal-form')[0].reset();
    $('#goal-modal-method').val('POST');
    $('#goal-input-id').val('');
    $('#goal-modal-form').attr('action', "{{ route('finances.goals.store') }}");
    $('#goal-modal-title').text('Buat Target Tabungan');
    $('#goal-modal-icon-badge').text('🎯');
    $('#goal-input-icon').val('🎯');
    $('#goal-btn-submit').html('Simpan Target &rarr;');
    if (goalDatePicker) goalDatePicker.clear();
    $('#goal-modal').removeClass('hidden');
  }

  function closeGoalModal() {
    $('#goal-modal').addClass('hidden');
  }

  function openEditGoalModal(goal) {
    $('#goal-modal-form')[0].reset();
    $('#goal-input-id').val(goal.id);
    $('#goal-modal-method').val('PUT');
    $('#goal-modal-form').attr('action', `/finances/goals/${goal.id}`);
    $('#goal-modal-title').text('Edit Target Tabungan');
    $('#goal-modal-icon-badge').text(goal.icon || '🎯');
    $('#goal-input-title').val(goal.title);
    $('#goal-input-target-amount').val(formatRupiah(goal.target_amount));
    $('#goal-input-current-amount').val(formatRupiah(goal.current_amount || 0));
    $('#goal-input-icon').val(goal.icon || '🎯');
    $('#goal-input-note').val(goal.note || '');
    if (goal.target_date && goalDatePicker) {
      goalDatePicker.setDate(goal.target_date);
    } else if (goalDatePicker) {
      goalDatePicker.clear();
    }
    $('#goal-btn-submit').html('Perbarui Target &rarr;');
    $('#goal-modal').removeClass('hidden');
  }

  $('#goal-modal-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#goal-btn-submit');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    const rawTarget = $('#goal-input-target-amount').val().replace(/[^0-9]/g, '');
    const rawCurrent = $('#goal-input-current-amount').val().replace(/[^0-9]/g, '');
    const formData = new FormData($form[0]);
    formData.set('target_amount', rawTarget || '0');
    formData.set('current_amount', rawCurrent || '0');

    $btn.prop('disabled', true).html('Menyimpan...');
    showLoading('Menyimpan target tabungan...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        closeGoalModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Target berhasil disimpan!');
        if (typeof window.refreshFinanceData === 'function') {
          window.refreshFinanceData();
        } else {
          const $goals = $('#finance-goals-section');
          if ($goals.length) {
            $.get(window.location.href, function(html) {
              const fresh = $(html).find('#finance-goals-section').html();
              if (fresh) $goals.html(fresh);
            });
          }
        }
      },
      error: function(xhr) {
        let msg = 'Gagal menyimpan target tabungan.';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
          if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
        }
        if (typeof showToast === 'function') showToast(msg, 'error');
        else alert(msg);
      },
      complete: function() {
        $btn.prop('disabled', false).html(origText);
        hideLoading();
      }
    });
  });

  // ================= GOAL DEPOSIT JS =================
  window.openGoalDepositModal = function(goalId, title) {
    $('#goal-deposit-form')[0].reset();
    $('#deposit-goal-id').val(goalId);
    $('#deposit-goal-subtitle').text(title);
    $('#goal-deposit-form').attr('action', `/finances/goals/${goalId}/deposit`);
    $('#goal-deposit-modal').removeClass('hidden');
  };

  window.closeGoalDepositModal = function() {
    $('#goal-deposit-modal').addClass('hidden');
  };

  $('#goal-deposit-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#deposit-btn-submit');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    const rawAmount = $('#deposit-input-amount').val().replace(/[^0-9]/g, '');
    const formData = new FormData($form[0]);
    formData.set('amount', rawAmount || '0');

    $btn.prop('disabled', true).html('Memproses Setoran...');
    showLoading('Mencatat setoran celengan di kas...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        closeGoalDepositModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Setoran tabungan berhasil dicatat!');
        if (typeof window.refreshFinanceData === 'function') {
          window.refreshFinanceData();
        } else {
          if (financeDataTable) {
            financeDataTable.ajax.reload(null, false);
          }
          const $goals = $('#finance-goals-section');
          if ($goals.length) {
            $.get(window.location.href, function(html) {
              const fresh = $(html).find('#finance-goals-section').html();
              if (fresh) $goals.html(fresh);
            });
          }
        }
      },
      error: function(xhr) {
        let msg = 'Gagal mencatat setoran tabungan.';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
          if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
        }
        if (typeof showToast === 'function') showToast(msg, 'error');
        else alert(msg);
      },
      complete: function() {
        $btn.prop('disabled', false).html(origText);
        hideLoading();
      }
    });
  });
  // ================= PERIOD BUDGET JS =================
  function openPeriodBudgetModal(catId = null, catName = null, currentAmount = null, notes = null, hasBudget = false) {
    $('#period-budget-form')[0].reset();
    if (catId) {
      $('#pbudget-input-category').val(catId);
    }
    if (currentAmount && Number(currentAmount) > 0) {
      $('#pbudget-input-amount').val(formatRupiah(currentAmount));
    }
    if (notes) {
      $('#pbudget-input-notes').val(notes);
    }
    const currentPeriod = $('#budget-period-select').val() || "{{ $selectedPeriod ?? now()->format('Y-m') }}";
    $('#pbudget-input-period').val(currentPeriod);

    if (catId && (hasBudget || (currentAmount && Number(currentAmount) > 0))) {
      $('#pbudget-btn-delete').removeClass('hidden');
    } else {
      $('#pbudget-btn-delete').addClass('hidden');
    }

    $('#period-budget-modal').removeClass('hidden');
  }

  function closePeriodBudgetModal() {
    $('#period-budget-modal').addClass('hidden');
  }

  function deleteCurrentPeriodBudget() {
    const catId = $('#pbudget-input-category').val();
    const period = $('#pbudget-input-period').val() || $('#budget-period-select').val() || "{{ now()->format('Y-m') }}";
    const catName = $('#pbudget-input-category option:selected').text().trim() || 'kategori ini';

    if (!catId) return;

    closePeriodBudgetModal();
    const deleteUrl = `{{ url('/finances/period-budgets') }}/${catId}?period=${encodeURIComponent(period)}`;
    if (typeof confirmDelete === 'function') {
      confirmDelete(deleteUrl, `anggaran ${catName} (nominal dialihkan ke Lain-lain)`);
    } else if (confirm(`Hapus batasan anggaran untuk ${catName}? Nominal akan dialihkan ke Lain-lain.`)) {
      showLoading('Menghapus anggaran...');
      $.ajax({
        url: deleteUrl,
        type: 'DELETE',
        dataType: 'json',
        success: function(res) {
          showToast(res.message || 'Anggaran berhasil dihapus!');
          if (typeof window.refreshFinanceData === 'function') {
            window.refreshFinanceData();
          } else {
            if (budgetDataTable) budgetDataTable.ajax.reload(null, false);
            refreshBudgetMobile();
          }
        },
        error: function(xhr) {
          let msg = 'Gagal menghapus anggaran.';
          if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
          showToast(msg, 'error');
        },
        complete: function() {
          hideLoading();
        }
      });
    }
  }

  $('#period-budget-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#pbudget-btn-submit');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    const rawAmount = $('#pbudget-input-amount').val().replace(/[^0-9]/g, '');
    const formData = new FormData($form[0]);
    formData.set('budget_amount', rawAmount || '0');

    $btn.prop('disabled', true).html('Menyimpan...');
    showLoading('Menyimpan anggaran periode...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        closePeriodBudgetModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Anggaran periode berhasil disimpan!');
        if (typeof window.refreshFinanceData === 'function') {
          window.refreshFinanceData();
        } else {
          if (budgetDataTable) {
            budgetDataTable.ajax.reload(null, false);
          }
          refreshBudgetMobile();
        }
      },
      error: function(xhr) {
        let msg = 'Gagal menyimpan anggaran periode.';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
          if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
        }
        if (typeof showToast === 'function') showToast(msg, 'error');
        else alert(msg);
      },
      complete: function() {
        $btn.prop('disabled', false).html(origText);
        hideLoading();
      }
    });
  });
</script>
@endpush
