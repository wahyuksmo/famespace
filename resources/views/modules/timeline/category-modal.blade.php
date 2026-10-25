<!-- MODAL: KELOLA KATEGORI LINIMASA (SERUPA MODUL FINANCE) -->
<div id="timeline-category-manage-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-2xl w-full max-h-[92vh] flex flex-col p-4 sm:p-6 relative modal-box">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4 flex-shrink-0">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-accent text-ink brutal-border flex items-center justify-center font-black text-sm">
          🏷️
        </div>
        <div>
          <h3 class="font-black font-display text-lg sm:text-xl text-ink">Daftar Kategori Linimasa</h3>
          <p class="text-[10px] font-mono text-ink/60">Kelola kategori momen petualangan, liburan & trip keluarga</p>
        </div>
      </div>
      <button type="button" onclick="closeTimelineCategoryManageModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- TABS -->
    <div class="grid grid-cols-2 gap-2 mb-4 flex-shrink-0 font-mono text-xs font-black">
      <button type="button" id="tab-btn-timeline-cat-list" onclick="switchTimelineCategoryTab('list')" class="py-2 bg-ink text-white brutal-border transition-all">
        📋 Daftar Kategori
      </button>
      <button type="button" id="tab-btn-timeline-cat-form" onclick="switchTimelineCategoryTab('form')" class="py-2 bg-cream text-ink brutal-border transition-all">
        + Tambah Kategori
      </button>
    </div>

    <!-- TAB 1: DAFTAR KATEGORI (DATATABLES + MOBILE CARD STACK) -->
    <div id="tab-timeline-cat-list" class="overflow-y-auto flex-1 pr-1 space-y-3">
      <!-- 1. DESKTOP VIEW (MD+): TABEL SERVERSIDE DATATABLES -->
      <div class="hidden md:block w-full overflow-x-auto">
        <table id="timeline-category-datatable" class="w-full text-left text-xs font-semibold">
          <thead class="bg-cream brutal-border uppercase font-mono text-[11px] text-ink">
            <tr>
              <th class="p-2.5">Kategori</th>
              <th class="p-2.5">Tipe</th>
              <th class="p-2.5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y-2 divide-ink/10">
            @if(isset($categories) && count($categories) > 0)
              @foreach($categories as $cat)
                @php
                  $catJson = json_encode([
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'type' => $cat->type,
                    'type_label' => 'Liburan & Trip',
                    'icon' => $cat->icon ?: '📸',
                    'color' => $cat->color,
                    'is_custom' => ! is_null($cat->family_id),
                    'destroy_url' => route('timeline.categories.destroy', $cat->id),
                  ]);
                @endphp
                <tr>
                  <td class="p-2.5">
                    <div class="flex items-center gap-2">
                      <span class="text-base flex-shrink-0">{{ $cat->icon ?: '📸' }}</span>
                      <span class="font-black text-ink">{{ $cat->name }}</span>
                    </div>
                  </td>
                  <td class="p-2.5">
                    <span class="px-1.5 py-0.5 bg-brand text-white brutal-border text-[9px] font-bold uppercase font-mono">
                      Liburan & Trip
                    </span>
                  </td>
                  <td class="p-2.5 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                      @if(! is_null($cat->family_id))
                        <button 
                          type="button" 
                          onclick='openEditTimelineCategoryForm({!! $catJson !!})' 
                          class="px-2 py-0.5 bg-accent hover:bg-white text-ink brutal-border text-[10px] font-bold shadow-brutal-sm"
                          title="Edit Kategori"
                        >
                          ✏️ Edit
                        </button>
                        <button 
                          type="button" 
                          onclick="confirmDelete('{{ route('timeline.categories.destroy', $cat->id) }}', 'kategori {{ addslashes($cat->name) }}')" 
                          class="px-2 py-0.5 bg-coral hover:bg-ink text-white brutal-border text-[10px] font-bold shadow-brutal-sm"
                          title="Hapus Kategori"
                        >
                          🗑️
                        </button>
                      @else
                        <span class="text-[10px] font-mono text-ink/50 italic">Bawaan Sistem</span>
                      @endif
                    </div>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>

      <!-- 2. MOBILE VIEW (<MD): CARD STACK LIST -->
      <div class="block md:hidden space-y-2">
        <div id="timeline-category-mobile-card-list" class="space-y-2">
          @if(isset($categories) && count($categories) > 0)
            @foreach($categories->take(5) as $cat)
              @php
                $catJson = json_encode([
                  'id' => $cat->id,
                  'name' => $cat->name,
                  'type' => $cat->type,
                  'type_label' => 'Liburan & Trip',
                  'icon' => $cat->icon ?: '📸',
                  'color' => $cat->color,
                  'is_custom' => ! is_null($cat->family_id),
                  'destroy_url' => route('timeline.categories.destroy', $cat->id),
                ]);
              @endphp
              <div class="p-3 bg-cream brutal-border space-y-2 mobile-category-card" data-id="{{ $cat->id }}">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xl flex-shrink-0">{{ $cat->icon ?: '📸' }}</span>
                    <span class="font-black text-xs sm:text-sm text-ink truncate">{{ $cat->name }}</span>
                  </div>
                  <span class="px-2 py-0.5 bg-brand text-white brutal-border text-[9px] font-bold uppercase font-mono flex-shrink-0">
                    Liburan & Trip
                  </span>
                </div>
                <div class="pt-2 border-t border-ink/15 flex items-center justify-end gap-2">
                  @if(! is_null($cat->family_id))
                    <button 
                      type="button" 
                      onclick='openEditTimelineCategoryForm({!! $catJson !!})' 
                      class="flex-1 py-1.5 bg-accent hover:bg-white text-ink brutal-border text-xs font-bold text-center shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                    >
                      ✏️ Edit
                    </button>
                    <button 
                      type="button" 
                      onclick="confirmDelete('{{ route('timeline.categories.destroy', $cat->id) }}', 'kategori {{ addslashes($cat->name) }}')" 
                      class="px-3 py-1.5 bg-coral hover:bg-ink text-white brutal-border text-xs font-bold text-center shadow-brutal-sm"
                    >
                      🗑️ Hapus
                    </button>
                  @else
                    <span class="text-[10px] font-mono text-ink/50 italic py-1">Kategori bawaan sistem</span>
                  @endif
                </div>
              </div>
            @endforeach
          @else
            <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
              Belum ada kategori yang terdaftar.
            </div>
          @endif
        </div>

        <!-- MOBILE PAGINATION -->
        @php
          $catTotal = isset($categories) ? count($categories) : 0;
          $catInit = min(5, $catTotal);
        @endphp
        <div id="timeline-category-mobile-pagination-box" class="pt-1 text-center space-y-2">
          <div class="text-[11px] font-mono font-bold text-ink/70">
            Menampilkan <span id="timeline-category-mobile-count">{{ $catInit }}</span> dari <span id="timeline-category-mobile-total">{{ $catTotal }}</span> kategori
          </div>
          <button 
            type="button" 
            id="timeline-category-mobile-load-more" 
            onclick="loadMoreTimelineCategoryMobile()" 
            class="{{ $catInit >= $catTotal ? 'hidden' : '' }} w-full py-2.5 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-2 active:scale-[0.99] transition-all"
          >
            <span>⬇ Muat Lebih Banyak</span>
          </button>
          <div id="timeline-category-mobile-all-loaded" class="{{ $catInit < $catTotal ? 'hidden' : '' }} py-2 bg-cream brutal-border font-mono text-[11px] font-black text-ink/60">
            ✓ Semua kategori telah ditampilkan
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: FORM TAMBAH / EDIT KATEGORI -->
    <div id="tab-timeline-cat-form" class="hidden overflow-y-auto flex-1 pr-1">
      <form id="timeline-category-modal-form" method="POST" action="{{ route('timeline.categories.store') }}" class="space-y-3.5">
        @csrf
        <input type="hidden" name="_method" id="timeline-category-modal-method" value="POST">
        <input type="hidden" id="timeline-category-input-id" value="">

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Nama Kategori:
          </label>
          <input 
            type="text" 
            id="timeline-category-input-name" 
            name="name" 
            required 
            placeholder="misal: Snorkeling, Staycation, Gowes, Pantai"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
          >
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Emoji / Ikon:
          </label>
          <input 
            type="text" 
            id="timeline-category-input-icon" 
            name="icon" 
            placeholder="📸 atau pilih emoji di bawah"
            value="📸"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
          >
          <div class="flex items-center gap-1.5 mt-2 flex-wrap">
            @foreach(['📸', '🌲', '🏖️', '🏕️', '🍜', '🚗', '✈️', '🎡', '🏨', '⛰️', '🏛️', '🎉', '🧗', '⛵', '🚴', '🎣'] as $em)
              <button type="button" onclick="$('#timeline-category-input-icon').val('{{ $em }}')" class="px-2 py-1 bg-white hover:bg-accent brutal-border text-sm transition-transform active:scale-95">{{ $em }}</button>
            @endforeach
          </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="pt-4 flex items-center gap-3">
          <button type="button" onclick="switchTimelineCategoryTab('list')" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
            Batal
          </button>
          <button type="submit" id="timeline-category-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
            Simpan Kategori &rarr;
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
