<!-- MODAL: ANGGARAN BULANAN HISTORIS -->
<div id="period-budget-modal" class="fixed inset-0 bg-ink/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-lg w-full max-h-[92vh] overflow-y-auto p-4 sm:p-6 relative modal-box">
    
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-accent text-ink brutal-border flex items-center justify-center font-black text-sm">
          📅
        </div>
        <div>
          <h3 class="font-black font-display text-lg sm:text-xl text-ink">Anggaran Bulanan Khusus</h3>
          <p class="text-[10px] font-mono text-ink/60">Tentukan nominal anggaran khusus untuk bulan kalender tertentu</p>
        </div>
      </div>
      <button type="button" onclick="closePeriodBudgetModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <form id="period-budget-form" method="POST" action="{{ route('finances.period_budgets.store') }}" class="space-y-3.5">
      @csrf

      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Kategori Pengeluaran:
        </label>
        <select 
          id="pbudget-input-category" 
          name="category_id" 
          required
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
          <option value="">Pilih Kategori...</option>
          @if(isset($categories))
            @foreach($categories->where('type', 'expense') as $cat)
              <option value="{{ $cat->id }}">{{ $cat->icon ?: '🏷️' }} {{ $cat->name }}</option>
            @endforeach
          @endif
        </select>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Periode Bulan:
          </label>
          <select 
            id="pbudget-input-period" 
            name="period" 
            required
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
          >
            @if(isset($availablePeriods))
              @foreach($availablePeriods as $ap)
                <option value="{{ $ap['period'] }}" {{ ($selectedPeriod ?? '') === $ap['period'] ? 'selected' : '' }}>
                  {{ $ap['label'] }} {{ $ap['is_current'] ? '(Bulan Ini)' : '' }}
                </option>
              @endforeach
            @else
              <option value="{{ now()->format('Y-m') }}">{{ now()->locale('id')->isoFormat('MMMM Y') }}</option>
            @endif
          </select>
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Nominal Anggaran Khusus (Rp):
          </label>
          <input 
            type="text" 
            id="pbudget-input-amount" 
            name="budget_amount" 
            required 
            placeholder="misal: Rp 3.000.000"
            oninput="handleCurrencyInput(this)"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
            autocomplete="off"
          >
        </div>
      </div>

      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Catatan / Alasan Khusus (Opsional):
        </label>
        <input 
          type="text" 
          id="pbudget-input-notes" 
          name="notes" 
          placeholder="misal: Tambahan belanja Ramadhan, Liburan sekolah anak"
          class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
        >
        <p class="text-[10px] text-ink/60 font-mono mt-1">
          Anggaran ini akan berlaku khusus untuk periode bulan yang dipilih tanpa mengubah anggaran standar kategori.
        </p>
      </div>

      <div class="pt-2 flex items-center gap-3">
        <button type="button" onclick="closePeriodBudgetModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="pbudget-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan Anggaran Periode &rarr;
        </button>
      </div>
    </form>
  </div>
</div>
