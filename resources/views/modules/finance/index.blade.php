@extends('layouts.main')

@section('title', 'Kas & Tabungan — famespace')

@section('content')
<!-- TAB 3: KAS & TABUNGAN KELUARGA -->
<div id="view-finance" class="space-y-6">
  
  <!-- HEADER KAS -->
  <div class="bg-white brutal-card p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <span class="px-2.5 py-0.5 bg-brand text-white brutal-border font-mono text-[10px] sm:text-xs font-black uppercase tracking-wider">
        TRANSPARANSI FINANSIAL RUMAH TANGGA
      </span>
      <div class="flex items-center gap-2 flex-wrap">
        <h2 class="text-xl sm:text-2xl font-black font-display text-ink mt-1">Kas & Tabungan Bersama</h2>
        @if(!($canViewAllFinances ?? true))
          <span class="px-2 py-0.5 bg-accent text-ink brutal-border text-[10px] font-black font-mono">
            🔒 Mode Terbatas: Data Pribadi
          </span>
        @endif
      </div>
      <p class="text-xs font-medium text-ink/70">Catat pemasukan, pengeluaran dapur, dan pantau celengan impian keluarga.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <button type="button" onclick="openCategoryManageModal()" class="flex-1 sm:flex-none inline-flex items-center justify-center px-3.5 py-2.5 bg-cream hover:bg-accent text-ink brutal-border text-xs font-black uppercase tracking-wider shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">
        🏷️ Kategori & Anggaran
      </button>
      <button type="button" onclick="openFinanceModal()" class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">
        + Catat Kas
      </button>
    </div>
  </div>

  <!-- 3 SUMMARY STATS -->
  @php
    $family = $family ?? (auth()->user() ? auth()->user()->family : null);
    $familyName = $family ? $family->name : 'Keluarga';
    $balance = (float) ($finance['balance'] ?? 0);
    $income = (float) ($finance['income'] ?? 0);
    $expense = (float) ($finance['expense'] ?? 0);
    $budget = (float) ($family ? $family->monthly_budget : 0);
    $budgetPct = $budget > 0 ? round(($expense / $budget) * 100, 1) : 0;
    $currentMonthName = now()->locale('id')->isoFormat('MMM');
    $categoryBudgets = $categoryBudgets ?? collect();
    $goals = $goals ?? collect();
  @endphp
  <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4">
    <div class="bg-brand-soft brutal-card p-4 sm:p-5">
      <div class="text-[11px] sm:text-xs font-mono font-bold text-ink/70 uppercase">Total Saldo Kas Tersedia</div>
      <div class="text-2xl sm:text-3xl font-black font-mono text-brand mt-1 truncate" title="Rp {{ number_format($balance, 0, ',', '.') }}">
        Rp {{ number_format($balance, 0, ',', '.') }}
      </div>
      <div class="text-xs font-bold text-ink/70 mt-1">
        {{ ($canViewAllFinances ?? true) ? 'Saldo aktif kas ruang keluarga ' . $familyName : 'Saldo kas pribadi ' . auth()->user()->name }}
      </div>
    </div>

    <div class="bg-white brutal-card p-4 sm:p-5">
      <div class="text-[11px] sm:text-xs font-mono font-bold text-ink/70 uppercase">Pemasukan Bulan Ini ({{ $currentMonthName }})</div>
      <div class="text-2xl sm:text-3xl font-black font-mono text-brand mt-1 truncate" title="+ Rp {{ number_format($income, 0, ',', '.') }}">
        + Rp {{ number_format($income, 0, ',', '.') }}
      </div>
      <div class="text-xs font-bold text-brand mt-1">Total uang masuk tercatat</div>
    </div>

    <div class="bg-white brutal-card p-4 sm:p-5">
      <div class="text-[11px] sm:text-xs font-mono font-bold text-ink/70 uppercase">Pengeluaran Bulan Ini ({{ $currentMonthName }})</div>
      <div class="text-2xl sm:text-3xl font-black font-mono text-coral mt-1 truncate" title="- Rp {{ number_format($expense, 0, ',', '.') }}">
        - Rp {{ number_format($expense, 0, ',', '.') }}
      </div>
      <div class="text-xs font-bold text-ink/70 mt-1">{{ $budgetPct }}% dari target anggaran bulanan</div>
    </div>
  </div>

  <!-- BATAS ANGGARAN & KUOTA PER KATEGORI -->
  <div class="bg-white brutal-card p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b-2 border-ink mb-4 gap-3">
      <div>
        <div class="flex items-center gap-2 flex-wrap">
          <h3 class="font-black font-display text-base text-ink">Batas & Riwayat Anggaran Bulanan</h3>
          <span id="budget-period-badge" class="px-2 py-0.5 bg-accent text-ink brutal-border text-[10px] font-black font-mono {{ ($selectedPeriod ?? now()->format('Y-m')) !== now()->format('Y-m') ? '' : 'hidden' }}">
            🗓️ Mode Riwayat: <span id="budget-period-label">{{ $selectedPeriodLabel ?? $selectedPeriod }}</span>
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

        <button type="button" onclick="openPeriodBudgetModal()" class="px-3 py-1.5 bg-accent hover:bg-white text-ink brutal-border text-xs font-bold flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">
          <span>📝 Anggaran Periode</span>
        </button>

        <button type="button" onclick="openCategoryManageModal()" class="px-3 py-1.5 bg-cream hover:bg-white text-ink brutal-border text-xs font-bold flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">
          <span>⚙️ Kelola Kategori</span>
        </button>
      </div>
    </div>

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
          @if(isset($categoryBudgets) && count($categoryBudgets) > 0)
            @foreach($categoryBudgets as $cb)
              @php
                $hasBudget = $cb['monthly_budget'] > 0;
                $isOver = $cb['is_over_budget'];
                $pct = $cb['pct'];
                $barColor = $isOver ? 'bg-coral' : ($pct > 80 ? 'bg-amber-500' : 'bg-brand');
              @endphp
              <tr>
                <td class="p-2.5">
                  <div class="flex items-center gap-2">
                    <span class="text-base flex-shrink-0">{{ $cb['icon'] ?: '🏷️' }}</span>
                    <div>
                      <div class="font-black text-ink flex items-center gap-1.5 flex-wrap">
                        <span>{{ $cb['name'] }}</span>
                        @if($cb['has_custom_period_budget'])
                          <span class="px-1.5 py-0.5 bg-accent text-ink brutal-border text-[9px] font-black font-mono">⭐ Khusus</span>
                        @endif
                      </div>
                      @if(!empty($cb['notes']))
                        <div class="text-[10px] text-brand font-mono font-bold">📝 {{ $cb['notes'] }}</div>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="p-2.5 font-mono">
                  <span class="font-bold {{ $hasBudget ? 'text-ink' : 'text-ink/40' }}">
                    {{ $hasBudget ? 'Rp ' . number_format($cb['monthly_budget'], 0, ',', '.') : '—' }}
                  </span>
                </td>
                <td class="p-2.5 font-mono">
                  <span class="font-bold {{ $isOver ? 'text-coral' : 'text-ink' }}">
                    Rp {{ number_format($cb['spent'], 0, ',', '.') }}
                  </span>
                </td>
                <td class="p-2.5">
                  @if($hasBudget)
                    <div class="space-y-1">
                      <div class="flex items-center justify-between text-[11px] font-mono">
                        @if($isOver)
                          <span class="px-1.5 py-0.2 bg-coral text-white brutal-border text-[9px] font-black font-mono">🚨 OVER</span>
                          <span class="text-coral font-bold">+Rp {{ number_format($cb['spent'] - $cb['monthly_budget'], 0, ',', '.') }}</span>
                        @else
                          <span class="px-1.5 py-0.2 bg-brand-soft text-brand brutal-border text-[9px] font-black font-mono">{{ $pct }}%</span>
                          <span class="text-brand font-bold">Sisa: Rp {{ number_format($cb['remaining'], 0, ',', '.') }}</span>
                        @endif
                      </div>
                      <div class="w-full bg-cream h-2 brutal-border overflow-hidden">
                        <div class="{{ $barColor }} h-full" style="width: {{ min(100, $pct) }}%"></div>
                      </div>
                    </div>
                  @else
                    <span class="px-1.5 py-0.5 bg-cream text-ink/60 brutal-border text-[10px] font-mono">Tanpa Anggaran</span>
                  @endif
                </td>
                <td class="p-2.5 text-center">
                  <button 
                    type="button" 
                    onclick='openPeriodBudgetModal({{ $cb['id'] }}, @json($cb['name']), {{ (int) $cb['monthly_budget'] }}, @json($cb['notes'] ?? ""))' 
                    class="px-2.5 py-1 bg-accent hover:bg-white text-ink brutal-border text-[10px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                  >
                    ✏️ Atur
                  </button>
                </td>
              </tr>
            @endforeach
          @endif
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

              <div class="pt-2 border-t border-ink/15">
                <button 
                  type="button" 
                  onclick='openPeriodBudgetModal({{ $cb['id'] }}, @json($cb['name']), {{ (int) $cb['monthly_budget'] }}, @json($cb['notes'] ?? ""))' 
                  class="w-full py-2 bg-accent hover:bg-white text-ink brutal-border text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                >
                  <span>✏️ Atur Anggaran Bulan Ini</span>
                </button>
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

  <!-- CELENGAN & TARGET TABUNGAN BERSAMA -->
  <div class="bg-white brutal-card p-4 sm:p-6">
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
            $badgeBg = $isComplete ? 'bg-brand text-white' : ($goal['pct'] > 50 ? 'bg-accent text-ink' : 'bg-sage text-ink');
            $goalJson = json_encode($goal);
          @endphp
          <div class="p-3.5 sm:p-4 bg-cream brutal-border space-y-2 flex flex-col justify-between">
            <div class="space-y-2">
              <div class="flex justify-between items-start gap-2">
                <span class="text-2xl flex-shrink-0">{{ $goal['icon'] ?? '🎯' }}</span>
                <div class="flex items-center gap-1">
                  @if($isComplete)
                    <span class="px-2 py-0.5 bg-brand text-white brutal-border text-[10px] font-black font-mono">✓ TERCAPAI</span>
                  @else
                    <span class="px-2 py-0.5 {{ $badgeBg }} brutal-border text-[10px] font-black font-mono">{{ $goal['pct'] }}% TERCAPAI</span>
                  @endif
                </div>
              </div>

              <div>
                <div class="font-black text-sm text-ink leading-snug">{{ $goal['title'] }}</div>
                @if(!empty($goal['target_date']))
                  <div class="text-[10px] font-mono text-ink/60 mt-0.5">🎯 Target: {{ $goal['formatted_target_date'] }}</div>
                @endif
              </div>

              <div class="text-xs font-mono font-bold text-brand">
                Rp {{ number_format($goal['current_amount'], 0, ',', '.') }} / Rp {{ number_format($goal['target_amount'], 0, ',', '.') }}
              </div>

              <div class="w-full bg-white h-3 brutal-border overflow-hidden">
                <div class="bg-brand h-full transition-all duration-300" style="width: {{ $goal['pct'] }}%"></div>
              </div>

              @if(!empty($goal['note']))
                <div class="text-[10px] text-ink/70 font-medium">{{ $goal['note'] }}</div>
              @endif
            </div>

            <!-- ACTION BUTTONS UNTUK GOAL -->
            <div class="pt-3 border-t border-ink/15 flex items-center justify-between gap-1.5 mt-2">
              <button 
                type="button" 
                onclick="openGoalDepositModal({{ $goal['id'] }}, @json($goal['title']))" 
                class="px-2.5 py-1.5 bg-brand hover:bg-brand-dark text-white brutal-border text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all flex-1 justify-center"
              >
                <span>💰 Setor</span>
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
                onclick="confirmDelete('{{ route('finances.goals.destroy', $goal['id']) }}', 'target tabungan {{ addslashes($goal['title']) }}')" 
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

  <!-- RIWAYAT TRANSAKSI TABEL SERVERSIDE DATATABLES -->
  <div class="bg-white brutal-card p-4 sm:p-6">
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div>
        <h3 class="font-black font-display text-base text-ink">Buku Kas: Catatan Transaksi</h3>
        <p class="text-[10px] font-mono text-ink/60 hidden sm:block">Mutasi uang masuk dan keluar rumah tangga</p>
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
            placeholder="Cari transaksi kas keluarga..." 
            class="w-full pl-9 pr-3 py-2 bg-white brutal-border font-sans text-xs font-bold text-ink placeholder:text-ink/40 focus:outline-none focus:bg-brand-soft shadow-brutal-sm transition-colors"
          >
          <span class="absolute left-3 top-2.5 text-xs text-ink/50">🔍</span>
        </div>
        
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-mono font-bold no-scrollbar">
          <button type="button" class="btn-mfilter-type px-3 py-1 bg-ink text-white brutal-border text-[11px] font-black uppercase transition-all" data-type="all">Semua</button>
          <button type="button" class="btn-mfilter-type px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-type="expense">Pengeluaran</button>
          <button type="button" class="btn-mfilter-type px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-type="income">Pemasukan</button>
        </div>
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
                <span class="font-mono text-[11px] font-bold text-ink/70 flex items-center gap-1 flex-shrink-0">
                  <span>🗓️</span>
                  <span>{{ \Carbon\Carbon::parse($trx->transaction_date)->locale('id')->isoFormat('D MMM Y') }}</span>
                </span>
              </div>

              <div class="py-3 flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                  <h4 class="text-sm font-black text-ink leading-snug break-words">{{ $trx->description }}</h4>
                  <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-ink/70">
                      <span class="text-ink/50">👤</span> {{ $trx->user ? $trx->user->name : 'Keluarga' }}
                    </span>
                    @if($receiptUrl)
                      <button type="button" onclick="showImagePreview('{{ $receiptUrl }}', 'Struk: {{ addslashes($trx->description) }}')" class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-soft hover:bg-brand hover:text-white brutal-border text-[10px] font-mono font-bold transition-colors">
                        📎 Struk
                      </button>
                    @endif
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <span class="text-[10px] font-mono font-black block text-ink/50 uppercase">{{ $isIncome ? 'Masuk' : 'Keluar' }}</span>
                  <span class="font-mono text-base font-black {{ $isIncome ? 'text-brand' : 'text-coral' }} leading-tight block">
                    {{ $isIncome ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                  </span>
                </div>
              </div>

              <div class="pt-2.5 border-t border-ink/15 flex items-center justify-end gap-2">
                <button 
                  type="button" 
                  onclick='openEditFinanceModal({!! $trxPayload !!})' 
                  class="px-3 py-1.5 bg-accent hover:bg-white text-ink brutal-border text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                >
                  ✏️ Edit
                </button>
                <button 
                  type="button" 
                  onclick="confirmDelete('{{ route('finances.destroy', $trx->id) }}', 'transaksi {{ addslashes($trx->description) }}')" 
                  class="px-3 py-1.5 bg-coral hover:bg-ink text-white brutal-border text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                >
                  🗑️ Hapus
                </button>
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
@endsection

@push('modals')
  @include('modules.finance.modal')
  @include('modules.finance.category-modal')
  @include('modules.finance.goal-modal')
  @include('modules.finance.period-budget-modal')
@endpush

@push('scripts')
<script>
  let financeDataTable = null;
  let budgetDataTable = null;
  let categoryDataTable = null;
  let financeDatePicker = null;

  $(document).ready(function() {
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
    financeDataTable = $('#finance-datatable').DataTable({
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
    budgetDataTable = $('#budget-datatable').DataTable({
      processing: true,
      serverSide: true,
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
            const notesSafe = (row.notes || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const nameSafe = (row.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            return `
              <button 
                type="button" 
                onclick='openPeriodBudgetModal(${row.id}, "${nameSafe}", ${row.monthly_budget}, "${notesSafe}")' 
                class="px-2.5 py-1 bg-accent hover:bg-white text-ink brutal-border text-[10px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
              >
                ✏️ Atur
              </button>
            `;
          }
        }
      ]
    });

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
        <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-ink/15">
          <span class="px-2.5 py-0.5 ${badgeClass} brutal-border text-[10px] font-black uppercase tracking-wider truncate inline-flex items-center gap-1">
            <span>${row.category_icon || (isIncome ? '💰' : '📦')}</span>
            <span>${escapeHtml(categoryLabel)}</span>
          </span>
          <span class="font-mono text-[11px] font-bold text-ink/70 flex items-center gap-1 flex-shrink-0">
            <span>🗓️</span>
            <span>${escapeHtml(row.formatted_date || row.transaction_date)}</span>
          </span>
        </div>

        <div class="py-3 flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <h4 class="text-sm font-black text-ink leading-snug break-words">${escapeHtml(row.description)}</h4>
            <div class="mt-1.5 flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-ink/70">
                <span class="text-ink/50">👤</span> ${escapeHtml(row.user || 'Keluarga')}
              </span>
              ${receiptBtn}
            </div>
          </div>
          <div class="text-right flex-shrink-0">
            <span class="text-[10px] font-mono font-black block text-ink/50 uppercase">${isIncome ? 'Masuk' : 'Keluar'}</span>
            <span class="font-mono text-base font-black ${amountClass} leading-tight block">
              ${amountSign} Rp ${Number(row.amount).toLocaleString('id-ID')}
            </span>
          </div>
        </div>

        <div class="pt-2.5 border-t border-ink/15 flex items-center justify-end gap-2">
          <button 
            type="button" 
            onclick='openEditFinanceModal(${payload})' 
            class="px-3 py-1.5 bg-accent hover:bg-white text-ink brutal-border text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
          >
            ✏️ Edit
          </button>
          <button 
            type="button" 
            onclick="confirmDelete('${row.destroy_url}', 'transaksi ${escapeHtml(row.description)}')" 
            class="px-3 py-1.5 bg-coral hover:bg-ink text-white brutal-border text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
          >
            🗑️ Hapus
          </button>
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
  let budgetMobileStart = {{ count($categoryBudgets) }};
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

        <div class="pt-2 border-t border-ink/15">
          <button 
            type="button" 
            onclick='openPeriodBudgetModal(${row.id}, "${nameSafe}", ${row.monthly_budget}, "${notesSafe}")' 
            class="w-full py-2 bg-accent hover:bg-white text-ink brutal-border text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
          >
            <span>✏️ Atur Anggaran Bulan Ini</span>
          </button>
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
    const deleteBtn = row.is_custom ? `
      <button 
        type="button" 
        onclick="confirmDelete('${row.destroy_url}', 'kategori ${nameEsc}')" 
        class="px-3 py-1.5 bg-coral hover:bg-ink text-white brutal-border text-xs font-bold text-center shadow-brutal-sm"
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
        <div class="pt-2 border-t border-ink/15 flex items-center justify-end gap-2">
          <button 
            type="button" 
            onclick='openEditCategoryForm(${catJson})' 
            class="flex-1 py-1.5 bg-accent hover:bg-white text-ink brutal-border text-xs font-bold text-center shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
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
    if (type === 'income') {
      $('#btn-ftype-income').removeClass('bg-cream text-ink').addClass('bg-brand text-white');
      $('#btn-ftype-expense').removeClass('bg-coral text-white').addClass('bg-cream text-ink');
      $('#mlabel-title').text('Keterangan Pemasukan:');
      $('#finance-input-description').attr('placeholder', 'misal: Gaji bulanan atau hasil jualan online');
      $('#mlabel-extra').text('Nominal Pemasukan (Rp):');
      $('#mlabel-upload').text('Foto Bukti Transfer / Slip (Opsional):');
    } else {
      $('#btn-ftype-expense').removeClass('bg-cream text-ink').addClass('bg-coral text-white');
      $('#btn-ftype-income').removeClass('bg-brand text-white').addClass('bg-cream text-ink');
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
    $('#finance-modal-form')[0].reset();
    $('#finance-modal-method').val('POST');
    $('#finance-modal-form').attr('action', "{{ route('finances.store') }}");
    $('#finance-modal-title').text('Catat Kas & Pengeluaran');
    $('#finance-modal-icon').text('💸');
    $('#finance-btn-submit').html('Simpan Transaksi &rarr;');
    
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
    $('#finance-modal-form')[0].reset();
    $('#finance-modal-method').val('PUT');
    $('#finance-modal-form').attr('action', trx.update_url);
    $('#finance-modal-title').text('Edit Transaksi Kas');
    $('#finance-modal-icon').text('✏️');
    $('#finance-btn-submit').html('Perbarui Transaksi &rarr;');
    
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
        if (financeDataTable) {
          financeDataTable.ajax.reload(null, false);
        }
        if (typeof refreshFinanceMobile === 'function') {
          refreshFinanceMobile();
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
  function openCategoryManageModal() {
    switchCategoryTab('list');
    $('#category-manage-modal').removeClass('hidden');
    setTimeout(() => {
      initCategoryDataTable();
      refreshCategoryMobile();
    }, 50);
  }

  function initCategoryDataTable() {
    if (categoryDataTable) {
      categoryDataTable.columns.adjust().draw();
      categoryDataTable.ajax.reload(null, false);
      return;
    }

    categoryDataTable = $('#category-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('finances.categories.paginate') }}",
        type: 'GET'
      },
      pageLength: 5,
      lengthMenu: [5, 10, 25],
      language: {
        search: "Cari Kategori:",
        lengthMenu: "Tampil _MENU_ data",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ kategori",
        infoEmpty: "Menampilkan 0 kategori",
        infoFiltered: "(disaring dari _MAX_ total data)",
        emptyTable: "Belum ada kategori yang terdaftar.",
        processing: "Memuat master kategori...",
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
                <span class="text-base flex-shrink-0">${row.icon || '🏷️'}</span>
                <span class="font-black text-ink">${escapeHtml(data)}</span>
              </div>
            `;
          }
        },
        {
          data: 'type_label',
          name: 'type',
          className: 'p-2.5',
          render: function(data, type, row) {
            const isExpense = row.type === 'expense';
            const badgeClass = isExpense ? 'bg-coral text-white' : 'bg-brand text-white';
            return `<span class="px-1.5 py-0.5 ${badgeClass} brutal-border text-[9px] font-bold uppercase font-mono">${escapeHtml(data)}</span>`;
          }
        },
        {
          data: 'formatted_budget',
          name: 'monthly_budget',
          className: 'p-2.5 font-mono',
          render: function(data, type, row) {
            const colorClass = row.monthly_budget > 0 ? 'text-brand' : 'text-ink/50';
            return `<span class="font-bold ${colorClass}">${data}</span>`;
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
                  class="px-2 py-0.5 bg-accent hover:bg-white text-ink brutal-border text-[10px] font-bold shadow-brutal-sm"
                  title="Edit Kategori & Anggaran"
                >
                  ✏️ Edit
                </button>
            `;
            if (row.is_custom) {
              const nameEsc = escapeHtml(row.name);
              html += `
                <button 
                  type="button" 
                  onclick="confirmDelete('${row.destroy_url}', 'kategori ${nameEsc}')" 
                  class="px-2 py-0.5 bg-coral hover:bg-ink text-white brutal-border text-[10px] font-bold shadow-brutal-sm"
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
  }

  function closeCategoryManageModal() {
    $('#category-manage-modal').addClass('hidden');
  }

  function switchCategoryTab(tab) {
    if (tab === 'list') {
      $('#tab-cat-list').removeClass('hidden');
      $('#tab-cat-form').addClass('hidden');
      $('#tab-btn-cat-list').removeClass('bg-cream text-ink').addClass('bg-ink text-white');
      $('#tab-btn-cat-form').removeClass('bg-ink text-white').addClass('bg-cream text-ink');
    } else {
      $('#tab-cat-list').addClass('hidden');
      $('#tab-cat-form').removeClass('hidden');
      $('#tab-btn-cat-form').removeClass('bg-cream text-ink').addClass('bg-ink text-white');
      $('#tab-btn-cat-list').removeClass('bg-ink text-white').addClass('bg-cream text-ink');
      if (!$('#category-input-id').val()) {
        resetCategoryForm();
      }
    }
  }

  function resetCategoryForm() {
    $('#category-modal-form')[0].reset();
    $('#category-input-id').val('');
    $('#category-modal-method').val('POST');
    $('#category-modal-form').attr('action', "{{ route('finances.categories.store') }}");
    $('#category-btn-submit').html('Simpan Kategori &rarr;');
    $('#category-input-icon').val('🏷️');
    $('#category-input-type').val('expense');
  }

  function openEditCategoryForm(cat) {
    resetCategoryForm();
    $('#category-input-id').val(cat.id);
    $('#category-modal-method').val('PUT');
    $('#category-modal-form').attr('action', `/finances/categories/${cat.id}`);
    $('#category-input-name').val(cat.name);
    $('#category-input-type').val(cat.type || 'expense');
    $('#category-input-icon').val(cat.icon || '🏷️');
    if (cat.monthly_budget && Number(cat.monthly_budget) > 0) {
      $('#category-input-budget').val(formatRupiah(cat.monthly_budget));
    } else {
      $('#category-input-budget').val('');
    }
    $('#category-btn-submit').html('Perbarui Kategori &rarr;');
    switchCategoryTab('form');
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
        resetCategoryForm();
        switchCategoryTab('list');
        if (categoryDataTable) {
          categoryDataTable.ajax.reload(null, false);
        }
        refreshCategoryMobile();
        if (budgetDataTable) {
          budgetDataTable.ajax.reload(null, false);
        }
        refreshBudgetMobile();
        refreshBudgetMobile();
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
        setTimeout(() => window.location.reload(), 350);
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
  function openGoalDepositModal(goalId, title) {
    $('#goal-deposit-form')[0].reset();
    $('#deposit-goal-id').val(goalId);
    $('#deposit-goal-subtitle').text(title);
    $('#goal-deposit-form').attr('action', `/finances/goals/${goalId}/deposit`);
    $('#goal-deposit-modal').removeClass('hidden');
  }

  function closeGoalDepositModal() {
    $('#goal-deposit-modal').addClass('hidden');
  }

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
        setTimeout(() => window.location.reload(), 400);
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
  function openPeriodBudgetModal(catId = null, catName = null, currentAmount = null, notes = null) {
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
    $('#period-budget-modal').removeClass('hidden');
  }

  function closePeriodBudgetModal() {
    $('#period-budget-modal').addClass('hidden');
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
        if (budgetDataTable) {
          budgetDataTable.ajax.reload(null, false);
        }
        refreshBudgetMobile();
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
