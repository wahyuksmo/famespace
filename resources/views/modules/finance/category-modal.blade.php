<!-- MODAL: KELOLA KATEGORI & ANGGARAN -->
<div id="category-manage-modal" class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white brutal-card max-w-2xl w-full max-h-[92vh] flex flex-col p-4 sm:p-6 relative modal-box">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4 flex-shrink-0">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-accent text-ink brutal-border flex items-center justify-center font-black text-sm">
          🏷️
        </div>
        <div>
          <h3 class="font-black font-display text-lg sm:text-xl text-ink">Daftar Kategori & Batas Anggaran</h3>
          <p class="text-[10px] font-mono text-ink/60">Kelola Daftar Kategori kas dan batas belanja bulanan keluarga</p>
        </div>
      </div>
      <button type="button" onclick="closeCategoryManageModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- TABS -->
    <div class="grid grid-cols-2 gap-2 mb-4 flex-shrink-0 font-mono text-xs font-black">
      <button type="button" id="tab-btn-cat-list" onclick="switchCategoryTab('list')" class="py-2 bg-ink text-white brutal-border transition-all">
        📋 Daftar Kategori
      </button>
      <button type="button" id="tab-btn-cat-form" onclick="switchCategoryTab('form')" class="py-2 bg-cream text-ink brutal-border transition-all">
        + Tambah Kategori
      </button>
    </div>

    <!-- TAB 1: DAFTAR KATEGORI (SERVERSIDE DATATABLES + MOBILE CARD STACK) -->
    <div id="tab-cat-list" class="overflow-y-auto flex-1 pr-1 space-y-3">
      <!-- 1. DESKTOP VIEW (MD+): TABEL SERVERSIDE DATATABLES -->
      <div class="hidden md:block w-full overflow-x-auto">
        <table id="category-datatable" class="w-full text-left text-xs font-semibold">
          <thead class="bg-cream brutal-border uppercase font-mono text-[11px] text-ink">
            <tr>
              <th class="p-2.5">Kategori</th>
              <th class="p-2.5">Jenis</th>
              <th class="p-2.5">Anggaran Standar</th>
              <th class="p-2.5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y-2 divide-ink/10">
            @if(isset($categories) && count($categories) > 0)
              @foreach($categories as $cat)
                @php
                  $isExpense = $cat->type === 'expense';
                  $budgetVal = (float) ($cat->monthly_budget ?? 0);
                  $catJson = json_encode([
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'type' => $cat->type,
                    'type_label' => $isExpense ? 'Pengeluaran' : 'Pemasukan',
                    'icon' => $cat->icon ?: '🏷️',
                    'color' => $cat->color,
                    'monthly_budget' => $budgetVal,
                    'formatted_budget' => $budgetVal > 0 ? 'Rp ' . number_format($budgetVal, 0, ',', '.') . ' / bln' : '—',
                    'is_custom' => ! is_null($cat->family_id),
                    'destroy_url' => route('finances.categories.destroy', $cat->id),
                  ]);
                @endphp
                <tr>
                  <td class="p-2.5">
                    <div class="flex items-center gap-2">
                      <span class="text-base flex-shrink-0">{{ $cat->icon ?: '🏷️' }}</span>
                      <span class="font-black text-ink">{{ $cat->name }}</span>
                    </div>
                  </td>
                  <td class="p-2.5">
                    <span class="px-1.5 py-0.5 {{ $isExpense ? 'bg-coral text-white' : 'bg-brand text-white' }} brutal-border text-[9px] font-bold uppercase font-mono">
                      {{ $isExpense ? 'Pengeluaran' : 'Pemasukan' }}
                    </span>
                  </td>
                  <td class="p-2.5 font-mono">
                    <span class="font-bold {{ $budgetVal > 0 ? 'text-brand' : 'text-ink/50' }}">
                      {{ $budgetVal > 0 ? 'Rp ' . number_format($budgetVal, 0, ',', '.') . ' / bln' : '—' }}
                    </span>
                  </td>
                  <td class="p-2.5 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                      <button 
                        type="button" 
                        onclick='openEditCategoryForm({!! $catJson !!})' 
                        class="px-2 py-0.5 bg-accent hover:bg-white text-ink brutal-border text-[10px] font-bold shadow-brutal-sm"
                        title="Edit Kategori & Anggaran"
                      >
                        ✏️ Edit
                      </button>
                      @if(! is_null($cat->family_id) && strtolower(trim($cat->name)) !== 'lain-lain')
                        <button 
                          type="button" 
                          onclick="confirmDelete('{{ route('finances.categories.destroy', $cat->id) }}', 'kategori {{ addslashes($cat->name) }} (transaksi & anggarannya dialihkan ke Lain-lain)')" 
                          class="px-2 py-0.5 bg-coral hover:bg-ink text-white brutal-border text-[10px] font-bold shadow-brutal-sm"
                          title="Hapus Kategori"
                        >
                          🗑️
                        </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>

      <!-- 2. MOBILE VIEW (< MD): TOUCH-FRIENDLY CARD STACK (PWA COMPLIANT) -->
      <div class="block md:hidden space-y-3" id="category-mobile-container">
        <!-- QUICK SEARCH & FILTER PILLS -->
        <div class="space-y-2">
          <div class="relative">
            <input 
              type="text" 
              id="category-mobile-search" 
              placeholder="Cari master kategori..." 
              class="w-full pl-9 pr-3 py-2 bg-white brutal-border font-sans text-xs font-bold text-ink placeholder:text-ink/40 focus:outline-none focus:bg-brand-soft shadow-brutal-sm transition-colors"
            >
            <span class="absolute left-3 top-2.5 text-xs text-ink/50">🔍</span>
          </div>
          
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-mono font-bold no-scrollbar">
            <button type="button" class="btn-cat-filter-type px-3 py-1 bg-ink text-white brutal-border text-[11px] font-black uppercase transition-all" data-type="all">Semua</button>
            <button type="button" class="btn-cat-filter-type px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-type="expense">Pengeluaran</button>
            <button type="button" class="btn-cat-filter-type px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-type="income">Pemasukan</button>
          </div>
        </div>

        <!-- CARD FEED CONTAINER -->
        <div id="category-mobile-card-list" class="space-y-2.5">
          @if(isset($categories) && count($categories) > 0)
            @foreach($categories->take(5) as $cat)
              @php
                $isExpense = $cat->type === 'expense';
                $budgetVal = (float) ($cat->monthly_budget ?? 0);
                $catJson = json_encode([
                  'id' => $cat->id,
                  'name' => $cat->name,
                  'type' => $cat->type,
                  'type_label' => $isExpense ? 'Pengeluaran' : 'Pemasukan',
                  'icon' => $cat->icon ?: '🏷️',
                  'color' => $cat->color,
                  'monthly_budget' => $budgetVal,
                  'formatted_budget' => $budgetVal > 0 ? 'Rp ' . number_format($budgetVal, 0, ',', '.') . ' / bln' : '—',
                  'is_custom' => ! is_null($cat->family_id),
                  'destroy_url' => route('finances.categories.destroy', $cat->id),
                ]);
              @endphp
              <div class="p-3 bg-cream brutal-border space-y-2 mobile-category-card" data-id="{{ $cat->id }}">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xl flex-shrink-0">{{ $cat->icon ?: '🏷️' }}</span>
                    <span class="font-black text-xs sm:text-sm text-ink truncate">{{ $cat->name }}</span>
                  </div>
                  <span class="px-2 py-0.5 {{ $isExpense ? 'bg-coral text-white' : 'bg-brand text-white' }} brutal-border text-[9px] font-bold uppercase font-mono flex-shrink-0">
                    {{ $isExpense ? 'Pengeluaran' : 'Pemasukan' }}
                  </span>
                </div>
                <div class="text-[11px] font-mono text-ink/70">
                  Anggaran: <span class="font-bold {{ $budgetVal > 0 ? 'text-brand' : 'text-ink/50' }}">{{ $budgetVal > 0 ? 'Rp ' . number_format($budgetVal, 0, ',', '.') . ' / bln' : 'Belum diatur' }}</span>
                </div>
                <div class="pt-2 border-t border-ink/15 flex items-center justify-end gap-2">
                  <button 
                    type="button" 
                    onclick='openEditCategoryForm({!! $catJson !!})' 
                    class="flex-1 py-1.5 bg-accent hover:bg-white text-ink brutal-border text-xs font-bold text-center shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px]"
                  >
                    ✏️ Edit
                  </button>
                  @if(! is_null($cat->family_id) && strtolower(trim($cat->name)) !== 'lain-lain')
                    <button 
                      type="button" 
                      onclick="confirmDelete('{{ route('finances.categories.destroy', $cat->id) }}', 'kategori {{ addslashes($cat->name) }} (transaksi & anggarannya dialihkan ke Lain-lain)')" 
                      class="px-3 py-1.5 bg-coral hover:bg-ink text-white brutal-border text-xs font-bold text-center shadow-brutal-sm"
                    >
                      🗑️ Hapus
                    </button>
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

        <!-- MOBILE PAGINATION ("MUAT LEBIH BANYAK") -->
        @php
          $catTotal = isset($categories) ? count($categories) : 0;
          $catInit = min(5, $catTotal);
        @endphp
        <div id="category-mobile-pagination-box" class="pt-1 text-center space-y-2">
          <div class="text-[11px] font-mono font-bold text-ink/70">
            Menampilkan <span id="category-mobile-count">{{ $catInit }}</span> dari <span id="category-mobile-total">{{ $catTotal }}</span> kategori
          </div>
          <button 
            type="button" 
            id="category-mobile-load-more" 
            onclick="loadMoreCategoryMobile()" 
            class="{{ $catInit >= $catTotal ? 'hidden' : '' }} w-full py-2.5 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-2 active:scale-[0.99] transition-all"
          >
            <span>⬇ Muat Lebih Banyak</span>
          </button>
          <div id="category-mobile-all-loaded" class="{{ $catInit < $catTotal ? 'hidden' : '' }} py-2 bg-cream brutal-border font-mono text-[11px] font-black text-ink/60">
            ✓ Semua kategori telah ditampilkan
          </div>
        </div>

      </div>
    </div>

    <!-- TAB 2: FORM TAMBAH / EDIT KATEGORI -->
    <div id="tab-cat-form" class="hidden overflow-y-auto flex-1 pr-1">
      <form id="category-modal-form" method="POST" action="{{ route('finances.categories.store') }}" class="space-y-3.5">
        @csrf
        <input type="hidden" name="_method" id="category-modal-method" value="POST">
        <input type="hidden" id="category-input-id" value="">

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Nama Kategori:
          </label>
          <input 
            type="text" 
            id="category-input-name" 
            name="name" 
            required 
            placeholder="misal: Belanja Dapur, Bensin Motor, Skincare"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
          >
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
              Jenis Kategori:
            </label>
            <select 
              id="category-input-type" 
              name="type" 
              required
              class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink outline-none focus:bg-white"
            >
              <option value="expense">💸 Pengeluaran</option>
              <option value="income">💰 Pemasukan</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
              Emoji / Ikon:
            </label>
            <input 
              type="text" 
              id="category-input-icon" 
              name="icon" 
              placeholder="🏷️ atau ketik emoji"
              value="🏷️"
              class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white"
            >
            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
              <button type="button" onclick="$('#category-input-icon').val('💰')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">💰</button>
              <button type="button" onclick="$('#category-input-icon').val('🛍️')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🛍️</button>
              <button type="button" onclick="$('#category-input-icon').val('🛒')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🛒</button>
              <button type="button" onclick="$('#category-input-icon').val('⚡')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">⚡</button>
              <button type="button" onclick="$('#category-input-icon').val('🚗')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🚗</button>
              <button type="button" onclick="$('#category-input-icon').val('🍜')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🍜</button>
              <button type="button" onclick="$('#category-input-icon').val('💊')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">💊</button>
              <button type="button" onclick="$('#category-input-icon').val('🎓')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🎓</button>
              <button type="button" onclick="$('#category-input-icon').val('🏫')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🏫</button>
              <button type="button" onclick="$('#category-input-icon').val('🐷')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🐷</button>
              <button type="button" onclick="$('#category-input-icon').val('🧾')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🧾</button>
              <button type="button" onclick="$('#category-input-icon').val('🎁')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🎁</button>
              <button type="button" onclick="$('#category-input-icon').val('🏷️')" class="px-1.5 py-0.5 bg-white brutal-border text-xs">🏷️</button>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
            Batas Anggaran Bulanan (Rp):
          </label>
          <input 
            type="text" 
            id="category-input-budget" 
            name="monthly_budget" 
            placeholder="misal: Rp 2.500.000 (0 jika tanpa batas)"
            oninput="handleCurrencyInput(this)"
            class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white"
            autocomplete="off"
          >
          <p class="text-[10px] text-ink/60 font-mono mt-1">Kosongkan atau isi 0 jika tidak membatasi kuota bulanan.</p>
        </div>

        <div class="pt-2 flex items-center gap-3">
          <button type="button" onclick="switchCategoryTab('list')" class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
            Kembali
          </button>
          <button type="submit" id="category-btn-submit" class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
            Simpan Kategori &rarr;
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
