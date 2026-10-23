<!-- BANNER AJAKAN INSTALL PWA (KHUSUS MOBILE BROWSER) -->
<div id="pwa-install-prompt" class="fixed inset-x-3 bottom-20 z-50 lg:hidden hidden bg-white brutal-border p-3.5 shadow-brutal select-none animate-slide-up-mobile rounded-xl">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <div class="w-11 h-11 bg-brand text-white brutal-border flex items-center justify-center font-black text-lg font-display shadow-[2px_2px_0px_#121614] rounded-lg shrink-0">
        f
      </div>
      <div class="min-w-0">
        <h4 class="text-xs font-black uppercase tracking-tight text-ink flex items-center gap-1.5">
          <span>Pasang Famespace</span>
          <span class="px-1 py-0.2 bg-accent text-[8px] font-mono brutal-border">PWA</span>
        </h4>
        <p id="pwa-prompt-desc" class="text-[11px] font-medium text-ink/70 leading-snug mt-0.5">
          Akses cepat langsung dari Home Screen HP kamu tanpa browser.
        </p>
      </div>
    </div>
    <button type="button" 
            onclick="dismissPwaPrompt()" 
            class="w-7 h-7 bg-cream hover:bg-coral hover:text-white brutal-border text-ink flex items-center justify-center text-xs font-bold shrink-0 touch-press transition-colors" 
            aria-label="Tutup Banner">
      ✕
    </button>
  </div>
  
  <!-- ANDROID / CHROME INSTALL BUTTONS -->
  <div id="pwa-android-actions" class="mt-2.5 flex items-center gap-2">
    <button type="button" 
            id="pwa-install-button" 
            onclick="if(window.vibrateDevice) window.vibrateDevice(15);"
            class="flex-1 py-2 px-3 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider text-center touch-press">
      📲 Install Sekarang
    </button>
    <button type="button" 
            onclick="dismissPwaPrompt()" 
            class="py-2 px-3 bg-cream brutal-btn text-xs font-bold text-ink/80 touch-press">
      Nanti Saja
    </button>
  </div>

  <!-- IOS SAFARI INSTALL INSTRUCTIONS -->
  <div id="pwa-ios-instructions" class="hidden mt-2.5 p-2 bg-cream brutal-border text-[11px] font-mono text-ink/80 space-y-1">
    <div class="font-black text-ink flex items-center gap-1">
      <span>📱 Cara Pasang di iPhone / iPad:</span>
    </div>
    <div class="flex items-center gap-1.5 text-xs font-bold text-brand">
      <span>1. Ketuk tombol Share</span>
      <span class="px-1.5 py-0.5 bg-white brutal-border text-sm">⎋</span>
      <span>di bilah bawah Safari</span>
    </div>
    <div>2. Geser lalu pilih <strong class="text-ink underline">"Tambah ke Layar Utama" (Add to Home Screen)</strong> ➕</div>
  </div>
</div>

<script>
  (function() {
    let deferredPrompt;
    const promptEl = document.getElementById('pwa-install-prompt');
    const installBtn = document.getElementById('pwa-install-button');
    const iosBox = document.getElementById('pwa-ios-instructions');
    const androidActions = document.getElementById('pwa-android-actions');
    const descEl = document.getElementById('pwa-prompt-desc');

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isStandalone) return;

    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;

      const dismissedAt = localStorage.getItem('fs_pwa_dismissed');
      const threeDays = 3 * 24 * 60 * 60 * 1000;
      if (!dismissedAt || (Date.now() - parseInt(dismissedAt, 10) > threeDays)) {
        if (promptEl) {
          promptEl.classList.remove('hidden');
        }
      }
    });

    // Check for iOS Safari display if not dismissed recently
    if (isIos && !isStandalone) {
      const dismissedAt = localStorage.getItem('fs_pwa_dismissed');
      const threeDays = 3 * 24 * 60 * 60 * 1000;
      if (!dismissedAt || (Date.now() - parseInt(dismissedAt, 10) > threeDays)) {
        if (promptEl) {
          if (androidActions) androidActions.classList.add('hidden');
          if (iosBox) iosBox.classList.remove('hidden');
          if (descEl) descEl.innerText = 'Pasang aplikasi di iPhone untuk pengalaman layar penuh.';
          promptEl.classList.remove('hidden');
        }
      }
    }

    if (installBtn) {
      installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        deferredPrompt = null;
        dismissPwaPrompt();
      });
    }

    window.dismissPwaPrompt = function() {
      if (!promptEl) return;
      promptEl.classList.add('transition-all', 'duration-200', 'translate-y-8', 'opacity-0');
      setTimeout(() => {
        promptEl.classList.add('hidden');
      }, 200);
      localStorage.setItem('fs_pwa_dismissed', Date.now().toString());
    };
  })();
</script>
