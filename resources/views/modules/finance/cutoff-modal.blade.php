<!-- MODAL: PENGATURAN TANGGAL CUT OFF SIKLUS KEUANGAN -->
<div id="cutoff-manage-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-md w-full p-5 sm:p-6 relative modal-box space-y-4">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-brand text-white brutal-border flex items-center justify-center font-black text-sm">
          ⚙️
        </div>
        <div>
          <h3 class="font-black font-display text-lg text-ink">Atur Tanggal Cut Off</h3>
          <p class="text-[10px] font-mono text-ink/60">Siklus keuangan bulanan akun Anda sendiri</p>
        </div>
      </div>
      <button type="button" onclick="closeCutOffModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- EXPLANATION BANNER -->
    <div class="p-3 bg-cream brutal-border space-y-1">
      <div class="text-[11px] font-black text-ink flex items-center gap-1.5">
        <span>💡</span>
        <span>Bagaimana Cut Off Bekerja?</span>
      </div>
      <p class="text-[10px] font-medium text-ink/80 leading-relaxed">
        Siklus bulanan Anda akan dihitung mulai tanggal cut off yang Anda tentukan (misal gajian tgl 25 atau 28). Periode baru otomatis berganti tanpa harus menunggu akhir bulan kalender ditutup terlebih dahulu.
      </p>
    </div>

    <!-- FORM -->
    <form id="cutoff-modal-form" method="POST" action="{{ route('finances.update_cut_off') }}" class="space-y-4">
      @csrf

      <div>
        <label for="cutoff-input-day" class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Tanggal Cut Off Bulanan (1–31):
        </label>
        <div class="flex items-center gap-2">
          <input 
            type="number" 
            id="cutoff-input-day" 
            name="cut_off_day" 
            min="1" 
            max="31" 
            required
            value="{{ $cutOffDay ?? (auth()->user()->cut_off_day ?? 1) }}"
            class="w-28 px-3.5 py-2.5 bg-cream brutal-border text-base font-black font-mono text-ink outline-none focus:bg-white"
          >
          <span class="text-xs font-mono font-bold text-ink/70">setiap bulan</span>
        </div>
      </div>

      <!-- PRESET CHIPS -->
      <div>
        <label class="block text-[10px] font-mono font-bold text-ink/60 uppercase mb-1.5">Pilihan Cepat Tanggal:</label>
        <div class="flex items-center gap-1.5 flex-wrap">
          <button type="button" onclick="selectCutOffPreset(1)" class="px-2.5 py-1 bg-white hover:bg-accent text-ink brutal-border text-xs font-mono font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">Tgl 1 (Standar)</button>
          <button type="button" onclick="selectCutOffPreset(25)" class="px-2.5 py-1 bg-white hover:bg-accent text-ink brutal-border text-xs font-mono font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">Tgl 25</button>
          <button type="button" onclick="selectCutOffPreset(28)" class="px-2.5 py-1 bg-white hover:bg-accent text-ink brutal-border text-xs font-mono font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">Tgl 28</button>
          <button type="button" onclick="selectCutOffPreset(20)" class="px-2.5 py-1 bg-white hover:bg-accent text-ink brutal-border text-xs font-mono font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">Tgl 20</button>
          <button type="button" onclick="selectCutOffPreset(15)" class="px-2.5 py-1 bg-white hover:bg-accent text-ink brutal-border text-xs font-mono font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">Tgl 15</button>
          <button type="button" onclick="selectCutOffPreset(10)" class="px-2.5 py-1 bg-white hover:bg-accent text-ink brutal-border text-xs font-mono font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all">Tgl 10</button>
        </div>
      </div>

      <!-- ACTION BUTTONS -->
      <div class="pt-2 flex items-center gap-3">
        <button type="button" onclick="closeCutOffModal()" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" id="cutoff-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
          Simpan Cut Off &rarr;
        </button>
      </div>
    </form>

  </div>
</div>
