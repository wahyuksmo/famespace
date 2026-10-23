<!-- NOTIFICATIONS FLASH -->
<div id="ajax-toast" class="hidden fixed top-5 right-5 z-[100] px-4 py-2.5 brutal-card text-xs font-mono font-black flex items-center gap-2 shadow-brutal transition-all">
  <span id="ajax-toast-icon">✨</span>
  <span id="ajax-toast-text"></span>
</div>

<!-- GLOBAL AJAX LOADING INDICATORS -->
<div id="global-ajax-progress" class="hidden fixed top-0 left-0 right-0 h-1.5 bg-accent z-[150] overflow-hidden">
  <div class="h-full bg-brand w-full animate-pulse"></div>
</div>

<div id="global-ajax-loader" class="hidden fixed bottom-6 right-6 z-[140] pointer-events-none transition-all duration-200">
  <div class="px-4 py-2.5 bg-ink text-white brutal-card flex items-center gap-3 shadow-[4px_4px_0px_#34D399] border-2 border-ink">
    <svg class="animate-spin h-4 w-4 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
    </svg>
    <span id="global-ajax-loader-text" class="text-xs font-mono font-black uppercase tracking-wider text-accent">Memproses data...</span>
  </div>
</div>

@if (session('success'))
  <div class="bg-accent border-b-2 border-ink px-4 py-2 text-center text-xs font-mono font-black text-ink">
    ✨ {{ session('success') }}
  </div>
@endif
@if (session('warning'))
  <div class="bg-coral text-white border-b-2 border-ink px-4 py-2 text-center text-xs font-mono font-black">
    ⚠️ {{ session('warning') }}
  </div>
@endif
@if ($errors->any())
  <div class="bg-coral text-white border-b-2 border-ink px-4 py-2 text-center text-xs font-mono font-black">
    ⚠️ {{ $errors->first() }}
  </div>
@endif
