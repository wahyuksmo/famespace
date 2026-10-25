<!-- MODAL: PREVIEW GAMBAR / STRUK / LAMPIRAN (NEO-BRUTALIST) -->
<div id="image-preview-modal" class="fixed inset-0 bg-ink/85 backdrop-blur-md z-[80] flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closeImagePreviewModal()">
  <div class="bg-cream brutal-card max-w-2xl w-full p-4 sm:p-5 relative space-y-3 my-auto max-h-[92vh] flex flex-col">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between pb-2 border-b-2 border-ink flex-shrink-0">
      <div class="flex items-center gap-2 min-w-0 pr-2">
        <span class="text-xl flex-shrink-0">📸</span>
        <h3 id="image-preview-title" class="font-black font-display text-sm sm:text-base text-ink truncate">Lampiran File</h3>
      </div>
      <div class="flex items-center gap-2 flex-shrink-0">
        <a id="image-preview-link" href="#" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 bg-white hover:bg-accent brutal-border font-mono text-[11px] font-bold text-ink transition-colors flex items-center gap-1">
          ↗ Buka Asli
        </a>
        <button type="button" onclick="closeImagePreviewModal()" class="w-7 h-7 bg-white hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
          ✕
        </button>
      </div>
    </div>

    <!-- IMAGE DISPLAY -->
    <div class="bg-white brutal-border p-2 flex items-center justify-center flex-1 min-h-0 overflow-auto">
      <img id="image-preview-src" src="" alt="Pratinjau File" class="max-h-[68vh] sm:max-h-[72vh] w-auto max-w-full object-contain mx-auto block">
    </div>

  </div>
</div>
