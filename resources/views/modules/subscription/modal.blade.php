<!-- MODAL: SUBSCRIPTION / PAYWALL (FAMKOJI PRO) -->
<div id="paywall-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-cream brutal-card max-w-lg w-full max-h-[92vh] overflow-y-auto p-5 sm:p-7 relative modal-box">
    
    <!-- HEADER -->
    <div class="flex items-start justify-between pb-3 border-b-2 border-ink mb-4">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 bg-accent text-ink brutal-border flex items-center justify-center font-black text-sm">
          👑
        </div>
        <div>
          <span class="text-[10px] font-black font-mono uppercase text-brand tracking-widest block">Langganan FamKoji</span>
          <h3 class="font-black font-display text-xl text-ink leading-tight">Upgrade ke Paket Pro</h3>
        </div>
      </div>
      <button type="button" onclick="closePaywallModal()" class="w-8 h-8 bg-white hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- PLAN SELECTION -->
    <div class="space-y-2 mb-4">
      <label class="block text-xs font-black font-mono uppercase text-ink">
        Pilih Durasi Paket:
      </label>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <!-- PLAN 1: MONTHLY -->
        <button type="button" onclick="selectPlan('pro_monthly')" id="plan-card-pro_monthly" class="p-3 bg-brand text-white brutal-border text-left relative transition-all cursor-pointer">
          <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-accent">Bulanan</span>
            <span class="px-1.5 py-0.5 bg-accent text-ink font-mono text-[9px] font-black brutal-border">Fleksibel</span>
          </div>
          <div class="flex items-baseline gap-1">
            <span class="text-xl font-black font-display">Rp 20.000</span>
            <span class="text-[10px] font-mono opacity-80">/ bln</span>
          </div>
          <div class="text-[10px] font-medium opacity-80 mt-1">Perpanjang tiap bulan</div>
        </button>

        <!-- PLAN 2: LIFETIME -->
        <button type="button" onclick="selectPlan('lifetime')" id="plan-card-lifetime" class="p-3 bg-white text-ink brutal-border text-left relative transition-all cursor-pointer">
          <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-coral">Lifetime</span>
            <span class="px-1.5 py-0.5 bg-coral text-white font-mono text-[9px] font-black brutal-border">Sekali Bayar 👑</span>
          </div>
          <div class="flex items-baseline gap-1">
            <span class="text-xl font-black font-display">Rp 150.000</span>
            <span class="text-[10px] font-mono opacity-80">/ selamanya</span>
          </div>
          <div class="text-[10px] font-medium opacity-80 mt-1">Akses seumur hidup</div>
        </button>
      </div>
    </div>

    <!-- UNLOCKED PERKS -->
    <div class="space-y-2 mb-4">
      <div class="text-xs font-mono font-black uppercase text-ink/70">Fitur yang Terbuka:</div>
      
      <div class="p-2.5 bg-white brutal-border flex items-start gap-2.5">
        <span class="text-base">🗺️</span>
        <div>
          <div class="text-xs font-black text-ink">Linimasa & Pengeluaran Trip</div>
          <div class="text-[11px] text-ink/70 font-medium leading-snug">Dokumentasi kenangan liburan, upload foto momen, dan rekap detail pengeluaran jalan-jalan.</div>
        </div>
      </div>

      <div class="p-2.5 bg-white brutal-border flex items-start gap-2.5">
        <span class="text-base">📅</span>
        <div>
          <div class="text-xs font-black text-ink">Kalender & Agenda Pengingat</div>
          <div class="text-[11px] text-ink/70 font-medium leading-snug">Kalender interaktif keluarga, reminder arisan, jatuh tempo tagihan, dan jadwal dokter anak.</div>
        </div>
      </div>

      <div class="p-2.5 bg-white brutal-border flex items-start gap-2.5">
        <span class="text-base">📸</span>
        <div>
          <div class="text-xs font-black text-ink">Album Keluarga Unlimited</div>
          <div class="text-[11px] text-ink/70 font-medium leading-snug">Unggah foto & video keluarga tanpa batas kuota (Free dibatasi 10 foto & 1 video).</div>
        </div>
      </div>

      <div class="p-2.5 bg-white brutal-border flex items-start gap-2.5">
        <span class="text-base">👨‍👩‍👧‍👦</span>
        <div>
          <div class="text-xs font-black text-ink">Semua Anggota Otomatis Aktif</div>
          <div class="text-[11px] text-ink/70 font-medium leading-snug">Cukup bayar sekali oleh Ayah, seluruh anggota keluarga langsung dapat akses Pro.</div>
        </div>
      </div>
    </div>

    <!-- PAYMENT METHOD SELECTION -->
    <div class="space-y-2 mb-5">
      <label class="block text-xs font-black font-mono uppercase text-ink">
        Pilih Metode Pembayaran:
      </label>
      <div class="grid grid-cols-2 gap-2 text-xs font-bold">
        <button type="button" onclick="selectPayMethod('qris')" id="pay-qris" class="p-2.5 bg-accent brutal-border text-left flex items-center justify-between">
          <span>⚡ QRIS Instant</span>
          <span class="text-[10px] font-mono">Bebas Biaya</span>
        </button>
        <button type="button" onclick="selectPayMethod('gopay')" id="pay-gopay" class="p-2.5 bg-white brutal-border text-left flex items-center justify-between">
          <span>🟢 GoPay / OVO</span>
          <span class="text-[10px] font-mono">E-Wallet</span>
        </button>
        <button type="button" onclick="selectPayMethod('bca')" id="pay-bca" class="p-2.5 bg-white brutal-border text-left flex items-center justify-between">
          <span>🏦 BCA VA</span>
          <span class="text-[10px] font-mono">Transfer</span>
        </button>
        <button type="button" onclick="selectPayMethod('mandiri')" id="pay-mandiri" class="p-2.5 bg-white brutal-border text-left flex items-center justify-between">
          <span>💳 Mandiri VA</span>
          <span class="text-[10px] font-mono">Transfer</span>
        </button>
      </div>
    </div>

    <!-- PAY BUTTON -->
    <button type="button" id="btn-pay-submit" onclick="simulatePayment()" class="w-full py-3.5 bg-brand text-white hover:bg-accent hover:text-ink font-black text-sm uppercase tracking-wider brutal-btn flex items-center justify-center gap-2">
      <span id="btn-pay-text">Bayar Rp 20.000 Sekarang</span>
      <span>&rarr;</span>
    </button>

    <p class="text-center text-[10px] font-mono font-medium text-ink/60 mt-3">
      Pembayaran instan & terverifikasi otomatis &bull; Langganan aktif untuk seluruh anggota keluarga
    </p>

  </div>
</div>

<!-- HIDDEN PAYMENT FORM -->
<form id="form-dev-toggle" action="{{ route('subscription.dev_toggle') }}" method="POST" class="hidden">
  @csrf
  <input type="hidden" name="plan_code" id="dev-toggle-plan-code" value="pro_monthly">
</form>
