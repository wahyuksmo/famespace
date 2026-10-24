@extends('layouts.main')

@section('title', 'Tugas & Chores — famespace')

@section('content')
<!-- TAB 2: TUGAS & CHORES -->
<div id="view-chores" class="space-y-6">
  
  <!-- TAB SWITCHER: TUGAS vs REWARDS -->
  <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
    <button 
      type="button" 
      id="tab-btn-chores" 
      onclick="switchChoreTab('chores')" 
      class="px-4 py-2 bg-ink text-white brutal-border font-mono text-xs font-black uppercase tracking-wider flex items-center gap-2 transition-all shadow-[2px_2px_0px_#121614] active:scale-95"
    >
      <span>📋 Checklist Tugas</span>
      <span class="px-1.5 py-0.2 bg-accent text-ink text-[10px] font-mono font-black rounded-sm">{{ ($allChores ?? $chores)->where('status', 'pending')->count() }}</span>
    </button>
    <button 
      type="button" 
      id="tab-btn-rewards" 
      onclick="switchChoreTab('rewards')" 
      class="px-4 py-2 bg-white text-ink brutal-border font-mono text-xs font-black uppercase tracking-wider flex items-center gap-2 hover:bg-cream transition-all shadow-[2px_2px_0px_#121614] active:scale-95"
    >
      <span>🎁 Katalog Hadiah & Klaim</span>
      <span id="tab-rewards-badge" class="{{ ($pendingClaimsCount ?? 0) > 0 ? '' : 'hidden' }} px-1.5 py-0.2 bg-coral text-white text-[10px] font-mono font-black rounded-sm animate-pulse">
        {{ $pendingClaimsCount ?? 0 }} Baru
      </span>
    </button>
  </div>

  <!-- HEADER CHORES & REWARDS -->
  <div class="bg-white brutal-card p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2">
        <span id="header-tagline" class="px-2.5 py-0.5 bg-accent brutal-border font-mono text-[10px] sm:text-xs font-black uppercase">
          DISTRIBUSI PEKERJAAN RUMAH
        </span>
      </div>
      <h2 id="header-title" class="text-xl sm:text-2xl font-black font-display text-ink mt-1">Manajemen Tugas & Chores</h2>
      <p id="header-subtitle" class="text-xs font-medium text-ink/70">Bagi tugas secara adil antar anggota keluarga. Anak-anak kumpulkan poin reward.</p>
    </div>
    <div class="flex items-center gap-2">
      <button type="button" id="header-btn-add-chore" onclick="openChoreModal()" class="hidden md:inline-flex items-center justify-center px-4 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider text-center">
        + Tambah Tugas Baru
      </button>
      @if(isset($user) && method_exists($user, 'isParent') && $user->isParent())
        <button type="button" id="header-btn-add-reward" onclick="openRewardModal()" class="hidden items-center justify-center px-4 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider text-center">
          + Tambah Hadiah Baru
        </button>
      @endif
    </div>
  </div>

  <!-- SECTION: CHORES TAB -->
  <div id="section-chores-tab" class="space-y-6">

  <!-- REWARD POIN BANNER FOR KIDS -->
  <div id="chores-kids-banner" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @php
      $kids = $members->filter(fn($m) => in_array(strtolower($m->role), ['anak', 'member']))->values();
    @endphp
    @forelse($kids as $idx => $kid)
      <div class="bg-brand-soft brutal-border p-4 flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 {{ $idx % 2 === 0 ? 'bg-sky text-ink' : 'bg-coral text-white' }} brutal-border flex items-center justify-center font-black text-xl flex-shrink-0">
            {{ $idx % 2 === 0 ? '👦' : '👧' }}
          </div>
          <div class="min-w-0">
            <div class="font-black text-xs text-ink truncate">Celengan Poin {{ $kid->name }}</div>
            <div class="text-[11px] text-ink/70 truncate">
              {{ ($allChores ?? $chores)->where('assigned_to_user_id', $kid->id)->where('status', 'completed')->count() }} tugas tuntas
            </div>
          </div>
        </div>
        <span class="px-3 py-1 bg-accent brutal-border font-mono font-black text-sm flex-shrink-0">⭐ {{ $kid->points ?? 0 }} Poin</span>
      </div>
    @empty
      <div class="col-span-full bg-cream brutal-border p-3.5 flex items-center justify-between text-xs font-mono text-ink/70">
        <span>💡 Tambahkan anggota keluarga berstatus Anak untuk mengaktifkan celengan poin tugas rumah tangga.</span>
      </div>
    @endforelse
  </div>

  <!-- CHORES LIST BY SECTION WITH SERVERSIDE DATATABLES -->
  <div class="bg-white brutal-card p-4 sm:p-6 space-y-3 sm:space-y-4">
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
      <h3 class="font-black font-display text-base">Checklist Tugas Aktif</h3>
      <span id="chores-completion-stat" class="text-xs font-mono font-bold text-ink/70">
        {{ ($allChores ?? $chores)->where('status', 'completed')->count() }}/{{ max(($allChores ?? $chores)->count(), 1) }} Selesai
      </span>
    </div>

    <!-- FILTER PILLS UNTUK DESKTOP -->
    <div class="hidden md:flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-mono font-bold no-scrollbar">
      <button type="button" class="btn-filter-chore px-3 py-1 bg-ink text-white brutal-border text-[11px] font-black uppercase transition-all" data-status="all">Semua</button>
      <button type="button" class="btn-filter-chore px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="pending">Perlu Dikerjakan</button>
      <button type="button" class="btn-filter-chore px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="completed">Selesai</button>
    </div>

    @php
      $choreCount = isset($chores) ? count($chores) : 0;
      $totalChores = isset($chores) && method_exists($chores, 'total') ? $chores->total() : $choreCount;
    @endphp

    <!-- 1. DESKTOP VIEW (MD+): DATATABLES CARD WRAPPER -->
    <div class="hidden md:block card-datatable">
      <table id="chores-datatable" class="w-full">
        <thead>
          <tr>
            <th>Tugas</th>
          </tr>
        </thead>
        <tbody class="space-y-3">
          <!-- Populated by DataTables Server-Side -->
        </tbody>
      </table>
    </div>

    <!-- 2. MOBILE VIEW (< MD): NEOBRUTALIST CARD STACK & LOAD MORE -->
    <div class="block md:hidden space-y-3.5" id="chores-mobile-container">
      
      <!-- MOBILE QUICK SEARCH & FILTER PILLS -->
      <div class="space-y-2">
        <div class="relative">
          <input 
            type="text" 
            id="chores-mobile-search" 
            placeholder="Cari tugas rumah tangga..." 
            class="w-full pl-9 pr-3 py-2 bg-white brutal-border font-sans text-xs font-bold text-ink placeholder:text-ink/40 focus:outline-none focus:bg-brand-soft shadow-brutal-sm transition-colors"
          >
          <span class="absolute left-3 top-2.5 text-xs text-ink/50">🔍</span>
        </div>
        
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-mono font-bold no-scrollbar">
          <button type="button" class="btn-mfilter-chore px-3 py-1 bg-ink text-white brutal-border text-[11px] font-black uppercase transition-all" data-status="all">Semua</button>
          <button type="button" class="btn-mfilter-chore px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="pending">Perlu Dikerjakan</button>
          <button type="button" class="btn-mfilter-chore px-3 py-1 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="completed">Selesai</button>
        </div>
      </div>

      <!-- CARD FEED CONTAINER -->
      <div id="chores-mobile-card-list" class="space-y-3">
        @if(isset($chores) && count($chores) > 0)
          @foreach($chores as $chore)
            @php
              $isDone = $chore->status === 'completed';
              $attachmentUrl = $chore->attachment_path ? (str_starts_with($chore->attachment_path, 'http') ? $chore->attachment_path : asset('storage/'.$chore->attachment_path)) : null;
              $chorePayload = json_encode([
                'id' => $chore->id,
                'title' => $chore->title,
                'description' => $chore->description,
                'points' => (int) $chore->points,
                'status' => $chore->status,
                'due_date' => $chore->due_date,
                'assigned_to_user_id' => $chore->assigned_to_user_id,
                'attachment_url' => $attachmentUrl,
                'update_url' => route('chores.update', $chore->id),
                'destroy_url' => route('chores.destroy', $chore->id),
              ]);
              $dueLabel = $chore->description ? $chore->description : ($chore->due_date ? 'Batas: '.\Carbon\Carbon::parse($chore->due_date)->locale('id')->isoFormat('D MMM Y') : 'Tugas Rumah');
            @endphp
            <div class="p-3.5 {{ $isDone ? 'bg-cream' : 'bg-white shadow-brutal-sm' }} brutal-border mobile-card-item transition-all" data-id="{{ $chore->id }}">
              <div class="flex items-start justify-between gap-2.5">
                <label class="flex items-start gap-3 cursor-pointer min-w-0 flex-1">
                  <input type="checkbox" {{ $isDone ? 'checked' : '' }} onchange="toggleTask(this, {{ $chore->id }})" class="w-5 h-5 mt-0.5 brutal-border accent-brand flex-shrink-0">
                  <div class="min-w-0">
                    <div class="text-xs {{ $isDone ? 'line-through text-ink/50' : 'text-ink font-black' }} break-words leading-snug">
                      {{ $chore->title }}
                    </div>
                    <div class="text-[10px] mt-0.5 {{ $isDone ? 'text-ink/60' : 'text-coral font-bold' }}">
                      {{ $dueLabel }}
                    </div>
                  </div>
                </label>
                <span class="px-2 py-0.5 bg-brand text-white brutal-border text-[10px] font-black font-mono flex-shrink-0">
                  +{{ $chore->points }} Poin
                </span>
              </div>

              <div class="pt-2.5 mt-2.5 border-t border-ink/15 flex items-center justify-between gap-2 flex-wrap">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="px-2 py-0.5 bg-accent brutal-border text-[10px] font-black uppercase">
                    👤 {{ $chore->assignee ? $chore->assignee->name : 'Semua Anggota' }}
                  </span>
                  @if($attachmentUrl)
                    <button 
                      type="button" 
                      onclick="showImagePreview('{{ $attachmentUrl }}', 'Foto Tugas: {{ addslashes($chore->title) }}')" 
                      class="px-2 py-0.5 bg-brand-soft hover:bg-brand hover:text-white brutal-border text-[10px] font-bold flex items-center gap-1 transition-colors"
                    >
                      📸 Foto
                    </button>
                  @endif
                </div>
                <div class="flex items-center gap-1.5 ml-auto">
                  <button 
                    type="button" 
                    onclick='openEditChoreModal({!! $chorePayload !!})' 
                    class="px-2.5 py-1 bg-white hover:bg-accent brutal-border text-[11px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                  >
                    ✏️ Edit
                  </button>
                  <button 
                    type="button" 
                    onclick="confirmDelete('{{ route('chores.destroy', $chore->id) }}', 'tugas {{ addslashes($chore->title) }}')" 
                    class="px-2.5 py-1 bg-coral text-white hover:bg-ink brutal-border text-[11px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        @else
          <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
            Belum ada tugas rumah tangga.
          </div>
        @endif
      </div>

      <!-- MOBILE PAGINATION ("MUAT LEBIH BANYAK") -->
      <div id="chores-mobile-pagination-box" class="pt-1 text-center space-y-2">
        <div id="chores-mobile-counter" class="text-[11px] font-mono font-bold text-ink/70">
          Menampilkan <span id="chores-mobile-count">{{ $choreCount }}</span> dari <span id="chores-mobile-total">{{ $totalChores }}</span> tugas
        </div>
        <button 
          type="button" 
          id="chores-mobile-load-more" 
          onclick="loadMoreChoresMobile()" 
          class="{{ $choreCount >= $totalChores ? 'hidden' : '' }} w-full py-3 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-2 active:scale-[0.99] transition-all"
        >
          <span>⬇ Muat Lebih Banyak</span>
        </button>
        <div id="chores-mobile-all-loaded" class="{{ $choreCount < $totalChores ? 'hidden' : '' }} py-2.5 bg-cream brutal-border font-mono text-[11px] font-black text-ink/60">
          ✓ Semua tugas telah ditampilkan
        </div>
      </div>
    </div>
  </div>
  </div><!-- /section-chores-tab -->

  <!-- SECTION: REWARDS TAB -->
  <div id="section-rewards-tab" class="hidden space-y-6">
    @include('modules.chore.partials.rewards-tab')
  </div>

</div>
@endsection

@push('modals')
  @include('modules.chore.modal')
  @include('modules.chore.reward-modal')
@endpush

@push('scripts')
<script>
  let choresDataTable = null;
  let rewardsDataTable = null;
  let choreDatePicker = null;
  let choresFilterStatus = 'all';

  $(document).ready(function() {
    // Inisialisasi Flatpickr
    if (typeof flatpickr !== 'undefined') {
      choreDatePicker = flatpickr("#chore-input-date", {
        locale: "id",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j F Y"
      });
    }

    // Inisialisasi Server-Side jQuery DataTables untuk Tugas (Custom Card Renderer)
    window.choresDataTable = choresDataTable = $('#chores-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('chores.paginate') }}",
        type: 'GET',
        data: function(d) {
          d.status = (choresFilterStatus === 'all') ? '' : choresFilterStatus;
        }
      },
      pageLength: 5,
      lengthMenu: [5, 10, 25],
      language: {
        search: "Cari Tugas:",
        lengthMenu: "Tampil _MENU_ tugas",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ tugas",
        infoEmpty: "Menampilkan 0 tugas",
        infoFiltered: "(disaring dari _MAX_ total data)",
        emptyTable: "Belum ada tugas rumah tangga. Silakan buat tugas baru!",
        processing: "Memuat tugas...",
        paginate: {
          first: "&laquo;",
          previous: "&larr; Prev",
          next: "Next &rarr;",
          last: "&raquo;"
        }
      },
      columns: [
        {
          data: null,
          orderable: false,
          render: function(data, type, row) {
            const isDone = row.status === 'completed';
            const payload = JSON.stringify(row).replace(/"/g, '&quot;');
            const dueLabel = row.description ? escapeHtml(row.description) : (row.formatted_due ? 'Batas: ' + row.formatted_due : 'Tugas Rumah');

            return `
              <div class="p-3 ${isDone ? 'bg-cream' : 'bg-white shadow-brutal-sm'} brutal-border flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <label class="flex items-center gap-3 cursor-pointer min-w-0">
                  <input type="checkbox" ${isDone ? 'checked' : ''} onchange="toggleTask(this, ${row.id})" class="w-5 h-5 brutal-border accent-brand flex-shrink-0">
                  <div class="min-w-0">
                    <div class="text-xs font-bold ${isDone ? 'line-through text-ink/50' : 'text-ink font-black'} truncate">
                      ${escapeHtml(row.title)}
                    </div>
                    <div class="text-[10px] ${isDone ? 'text-ink/60' : 'text-coral font-bold'}">
                      ${dueLabel}
                    </div>
                  </div>
                </label>
                <div class="flex items-center gap-1.5 flex-wrap self-end sm:self-center">
                  ${row.attachment_url ? `
                    <button 
                      type="button" 
                      onclick="showImagePreview('${row.attachment_url}', 'Foto Tugas: ${escapeHtml(row.title)}')" 
                      class="px-2 py-0.5 bg-brand-soft hover:bg-brand hover:text-white brutal-border text-[10px] font-bold flex items-center gap-1 transition-colors"
                      title="Lihat Foto Panduan / Bukti"
                    >
                      📸 Foto
                    </button>
                  ` : ''}
                  <span class="px-2 py-0.5 bg-accent brutal-border text-xs font-bold">
                    ${escapeHtml(row.assignee_name)}
                  </span>
                  <span class="px-2 py-0.5 bg-brand text-white brutal-border text-[10px] font-black font-mono">
                    +${row.points} Poin
                  </span>
                  <button 
                    type="button" 
                    onclick='openEditChoreModal(${payload})' 
                    class="px-2 py-0.5 bg-white hover:bg-accent brutal-border text-[10px] font-bold transition-colors"
                    title="Edit Tugas"
                  >
                    ✏️ Edit
                  </button>
                  <button 
                    type="button" 
                    onclick="confirmDelete('${row.destroy_url}', 'tugas ${escapeHtml(row.title)}')" 
                    class="px-2 py-0.5 bg-coral text-white hover:bg-ink brutal-border text-[10px] font-bold transition-colors"
                    title="Hapus Tugas"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            `;
          }
        }
      ]
    });

    const isUserParent = {{ ($isParent ?? (auth()->user()?->isParent() ?? false)) ? 'true' : 'false' }};

    // Inisialisasi Server-Side jQuery DataTables untuk Hadiah (Katalog Card Grid)
    window.rewardsDataTable = rewardsDataTable = $('#rewards-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('rewards.paginate') }}",
        type: 'GET'
      },
      pageLength: 6,
      lengthMenu: [6, 12, 24],
      language: {
        search: "Cari Hadiah:",
        lengthMenu: "Tampil _MENU_ hadiah",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ hadiah",
        infoEmpty: "Menampilkan 0 hadiah",
        infoFiltered: "(disaring dari _MAX_ total data)",
        emptyTable: "Belum ada hadiah di katalog.",
        processing: "Memuat katalog hadiah...",
        paginate: {
          first: "&laquo;",
          previous: "&larr; Prev",
          next: "Next &rarr;",
          last: "&raquo;"
        }
      },
      columns: [
        {
          data: null,
          orderable: false,
          render: function(data, type, row) {
            const payload = JSON.stringify(row).replace(/"/g, '&quot;');
            const stockBadgeClass = row.has_stock ? 'bg-cream text-ink' : 'bg-coral text-white';

            let actionBtns = '';
            if (isUserParent) {
              actionBtns = `
                <button 
                  type="button" 
                  onclick='openEditRewardModal(${payload})' 
                  class="w-7 h-7 bg-white hover:bg-accent brutal-border font-bold text-xs flex items-center justify-center transition-colors"
                  title="Edit Hadiah"
                >
                  ✏️
                </button>
                <button 
                  type="button" 
                  onclick="confirmDeleteReward(${row.id}, '${escapeHtml(row.title)}')" 
                  class="w-7 h-7 bg-white hover:bg-coral hover:text-white brutal-border font-bold text-xs flex items-center justify-center transition-colors"
                  title="Hapus Hadiah"
                >
                  🗑️
                </button>
              `;
            }

            let bottomBtn = '';
            if (!row.has_stock) {
              bottomBtn = `
                <button 
                  type="button" 
                  disabled 
                  class="w-full py-2 bg-ink/10 text-ink/40 brutal-border text-xs font-mono font-black uppercase cursor-not-allowed text-center"
                >
                  Stok Habis
                </button>
              `;
            } else if (row.can_afford) {
              bottomBtn = `
                <button 
                  type="button" 
                  onclick='openClaimConfirmModal(${payload})' 
                  class="w-full py-2 bg-accent hover:bg-accent/80 text-ink brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all"
                >
                  <span>Tukar Hadiah 🚀</span>
                </button>
              `;
            } else {
              bottomBtn = `
                <button 
                  type="button" 
                  disabled 
                  class="w-full py-2 bg-cream text-coral brutal-border text-xs font-mono font-black uppercase cursor-not-allowed text-center"
                  title="Poin kamu belum mencukupi"
                >
                  Kurang ${Number(row.points_needed).toLocaleString('id-ID')} Poin
                </button>
              `;
            }

            const descHtml = row.description ? `
              <p class="text-xs text-ink/70 mt-2 font-medium line-clamp-2">
                ${escapeHtml(row.description)}
              </p>
            ` : '';

            return `
              <div class="bg-white brutal-card p-4 sm:p-5 flex flex-col justify-between hover:translate-x-[-2px] hover:translate-y-[-2px] transition-transform space-y-4 h-full">
                <div>
                  <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="w-12 h-12 bg-accent brutal-border flex items-center justify-center text-2xl shadow-[2px_2px_0px_#121614] flex-shrink-0">
                      ${escapeHtml(row.icon || '🎁')}
                    </div>
                    <div class="flex items-center gap-1.5">
                      <span class="px-2 py-0.5 ${stockBadgeClass} brutal-border font-mono font-bold text-[10px]">
                        📦 ${escapeHtml(row.stock_text)}
                      </span>
                      ${actionBtns}
                    </div>
                  </div>

                  <h4 class="font-black font-display text-base text-ink line-clamp-1" title="${escapeHtml(row.title)}">
                    ${escapeHtml(row.title)}
                  </h4>
                  <div class="mt-1 flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-brand-soft text-brand brutal-border font-mono font-black text-xs">
                      ⭐ ${Number(row.points).toLocaleString('id-ID')} Poin
                    </span>
                  </div>
                  ${descHtml}
                </div>

                <div class="pt-3 border-t border-ink/20">
                  ${bottomBtn}
                </div>
              </div>
            `;
          }
        }
      ]
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

  // MOBILE CHORES STATE & LOAD MORE
  let choresMobileStart = {{ $choreCount }};
  const choresMobileLength = 5;
  let choresMobileTotal = {{ $totalChores }};
  let choresMobileFilterStatus = 'all';
  let choresMobileSearch = '';
  let choresMobileLoading = false;

  function renderMobileChoreCard(row) {
    const isDone = row.status === 'completed';
    const payload = JSON.stringify(row).replace(/"/g, '&quot;');
    const dueLabel = row.description ? escapeHtml(row.description) : (row.formatted_due ? 'Batas: ' + row.formatted_due : 'Tugas Rumah');
    const photoBtn = row.attachment_url ? `
      <button 
        type="button" 
        onclick="showImagePreview('${row.attachment_url}', 'Foto Tugas: ${escapeHtml(row.title)}')" 
        class="px-2 py-0.5 bg-brand-soft hover:bg-brand hover:text-white brutal-border text-[10px] font-bold flex items-center gap-1 transition-colors"
      >
        📸 Foto
      </button>
    ` : '';

    return `
      <div class="p-3.5 ${isDone ? 'bg-cream' : 'bg-white shadow-brutal-sm'} brutal-border mobile-card-item transition-all" data-id="${row.id}">
        <div class="flex items-start justify-between gap-2.5">
          <label class="flex items-start gap-3 cursor-pointer min-w-0 flex-1">
            <input type="checkbox" ${isDone ? 'checked' : ''} onchange="toggleTask(this, ${row.id})" class="w-5 h-5 mt-0.5 brutal-border accent-brand flex-shrink-0">
            <div class="min-w-0">
              <div class="text-xs ${isDone ? 'line-through text-ink/50' : 'text-ink font-black'} break-words leading-snug">
                ${escapeHtml(row.title)}
              </div>
              <div class="text-[10px] mt-0.5 ${isDone ? 'text-ink/60' : 'text-coral font-bold'}">
                ${dueLabel}
              </div>
            </div>
          </label>
          <span class="px-2 py-0.5 bg-brand text-white brutal-border text-[10px] font-black font-mono flex-shrink-0">
            +${row.points} Poin
          </span>
        </div>

        <div class="pt-2.5 mt-2.5 border-t border-ink/15 flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-1.5 flex-wrap">
            <span class="px-2 py-0.5 bg-accent brutal-border text-[10px] font-black uppercase">
              👤 ${escapeHtml(row.assignee_name || 'Semua Anggota')}
            </span>
            ${photoBtn}
          </div>
          <div class="flex items-center gap-1.5 ml-auto">
            <button 
              type="button" 
              onclick='openEditChoreModal(${payload})' 
              class="px-2.5 py-1 bg-white hover:bg-accent brutal-border text-[11px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
            >
              ✏️ Edit
            </button>
            <button 
              type="button" 
              onclick="confirmDelete('${row.destroy_url}', 'tugas ${escapeHtml(row.title)}')" 
              class="px-2.5 py-1 bg-coral text-white hover:bg-ink brutal-border text-[11px] font-bold shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] transition-all"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>
    `;
  }

  function fetchChoresMobileData(append = false) {
    if (choresMobileLoading) return;
    choresMobileLoading = true;

    const $btn = $('#chores-mobile-load-more');
    const origBtnHtml = $btn.html();
    $btn.prop('disabled', true).html('<span>⏳ Memuat tugas...</span>');

    const fetchStart = append ? choresMobileStart : 0;
    const filterStatusParam = (choresMobileFilterStatus === 'all') ? '' : choresMobileFilterStatus;

    $.ajax({
      url: "{{ route('chores.paginate') }}",
      type: 'GET',
      data: {
        draw: 1,
        start: fetchStart,
        length: choresMobileLength,
        search: choresMobileSearch,
        status: filterStatusParam
      },
      dataType: 'json',
      success: function(res) {
        const rows = res.data || [];
        choresMobileTotal = res.recordsFiltered !== undefined ? res.recordsFiltered : (res.total || 0);

        if (!append) {
          $('#chores-mobile-card-list').empty();
          choresMobileStart = 0;
        }

        if (rows.length === 0 && !append) {
          $('#chores-mobile-card-list').html(`
            <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
              Tidak ada tugas yang sesuai filter.
            </div>
          `);
        } else {
          rows.forEach(function(row) {
            $('#chores-mobile-card-list').append(renderMobileChoreCard(row));
          });
          choresMobileStart += rows.length;
        }

        // Update progress counter
        $('#chores-mobile-count').text(choresMobileStart);
        $('#chores-mobile-total').text(choresMobileTotal);

        if (choresMobileStart >= choresMobileTotal || rows.length === 0) {
          $('#chores-mobile-load-more').addClass('hidden');
          $('#chores-mobile-all-loaded').removeClass('hidden');
        } else {
          $('#chores-mobile-load-more').removeClass('hidden');
          $('#chores-mobile-all-loaded').addClass('hidden');
        }
      },
      error: function(err) {
        console.error('Gagal mengambil data tugas mobile:', err);
      },
      complete: function() {
        choresMobileLoading = false;
        $btn.prop('disabled', false).html(origBtnHtml);
      }
    });
  }

  function loadMoreChoresMobile() {
    fetchChoresMobileData(true);
  }

  function refreshChoresMobile() {
    choresMobileStart = 0;
    fetchChoresMobileData(false);
  }

  $(document).ready(function() {
    $(document).on('click', '.btn-filter-chore, .btn-mfilter-chore', function() {
      const status = $(this).data('status') || 'all';
      choresFilterStatus = status;
      choresMobileFilterStatus = status;
      $('.btn-filter-chore, .btn-mfilter-chore').each(function() {
        if ($(this).data('status') === status) {
          $(this).removeClass('bg-white text-ink font-bold').addClass('bg-ink text-white font-black');
        } else {
          $(this).removeClass('bg-ink text-white font-black').addClass('bg-white text-ink font-bold');
        }
      });
      if (choresDataTable) choresDataTable.ajax.reload();
      if (typeof refreshChoresMobile === 'function') refreshChoresMobile();
    });

    let choreSearchTimer = null;
    $(document).on('input', '#chores-mobile-search', function() {
      clearTimeout(choreSearchTimer);
      const query = $(this).val();
      choreSearchTimer = setTimeout(function() {
        choresMobileSearch = query;
        refreshChoresMobile();
      }, 350);
    });
  });

  function toggleTask(checkbox, choreId) {
    const $checkbox = $(checkbox);
    const $parent = $checkbox.closest('div');
    const $text = $parent.find('label > div > div').first();

    const $choreCount = $('#sidebar-chore-count');
    const prevCount = parseInt($choreCount.text(), 10) || 0;

    if (checkbox.checked) {
      $text.addClass('line-through text-ink/50');
      $choreCount.text(Math.max(0, prevCount - 1));
      const rect = checkbox.getBoundingClientRect();
      fireConfetti(rect.left + 8, rect.top + 8);
    } else {
      $text.removeClass('line-through text-ink/50');
      $choreCount.text(prevCount + 1);
    }

    if (choreId) {
      $checkbox.prop('disabled', true);
      showLoading('Menyinkronkan status tugas...');

      $.ajax({
        url: '/chores/' + choreId + '/toggle',
        type: 'PATCH',
        dataType: 'json',
        success: function(data) {
          if (data.success) {
            showToast(`Status tugas diperbarui (${data.points_awarded > 0 ? '+' : ''}${data.points_awarded} Poin)`);
            if (data.user_points !== undefined && typeof window.updateUserPoints === 'function') {
              window.updateUserPoints(data.user_points);
            }
            if (typeof window.reloadCurrentModuleData === 'function') {
              window.reloadCurrentModuleData({ data: data });
            }
          }
        },
        error: function(err) {
          showToast('Gagal memperbarui status tugas.', 'error');
          checkbox.checked = !checkbox.checked;
          $choreCount.text(prevCount);
        },
        complete: function() {
          $checkbox.prop('disabled', false);
          hideLoading();
        }
      });
    }
  }

  function openChoreModal() {
    currentModalType = 'task';
    $('#chore-modal-form')[0].reset();
    $('#chore-modal-method').val('POST');
    $('#chore-modal-form').attr('action', "{{ route('chores.store') }}");
    $('#chore-modal-title').text('Tambah Tugas Baru');
    $('#chore-modal-icon').text('🧹');
    $('#chore-btn-submit').html('Simpan Tugas &rarr;');
    if (choreDatePicker) choreDatePicker.clear();

    selectComboboxOption('assignee', '', '👥 Semua Anggota');
    selectComboboxOption('category', 'Dapur', '🍳 Dapur');

    $('#chore-input-remove-attachment').val('0');
    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
    $('#chore-modal').removeClass('hidden');
  }

  function closeChoreModal() {
    $('#chore-modal').addClass('hidden');
    $('#chore-input-remove-attachment').val('0');
    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
  }

  function openEditChoreModal(chore) {
    currentModalType = 'task';
    $('#chore-modal-form')[0].reset();
    $('#chore-modal-method').val('PUT');
    $('#chore-modal-form').attr('action', chore.update_url);
    $('#chore-modal-title').text('Edit Tugas: ' + chore.title);
    $('#chore-modal-icon').text('✏️');
    $('#chore-btn-submit').html('Perbarui Tugas &rarr;');

    $('#chore-input-title').val(chore.title);
    $('#chore-input-points').val(chore.points || 10);
    $('#chore-input-desc').val(chore.description || '');
    if (chore.due_date && choreDatePicker) {
      choreDatePicker.setDate(chore.due_date);
    }

    // Set Assignee
    if (chore.assigned_to_user_id) {
      const member = (comboboxData && comboboxData.assignee) 
        ? comboboxData.assignee.find(m => String(m.id !== undefined ? m.id : m.value) === String(chore.assigned_to_user_id)) 
        : null;
      if (member) {
        selectComboboxOption('assignee', member.id !== undefined ? member.id : member.value, member.text);
      } else {
        selectComboboxOption('assignee', chore.assigned_to_user_id, chore.assignee_name ? `👤 ${chore.assignee_name}` : 'Anggota Terpilih');
      }
    } else {
      selectComboboxOption('assignee', '', '👥 Semua Anggota');
    }

    // Set Category
    selectComboboxOption('category', 'Dapur', '🍳 Dapur');

    const fileInput = document.getElementById('minput-file');
    if (fileInput) fileInput.value = '';
    $('#chore-input-remove-attachment').val('0');

    if (chore.attachment_url) {
      document.getElementById('upload-preview-img').src = chore.attachment_url;
      document.getElementById('upload-file-name').innerText = 'Foto Panduan / Bukti Tersimpan';
      document.getElementById('upload-file-size').innerText = 'Tersimpan';
      document.getElementById('upload-dropzone').classList.add('hidden');
      document.getElementById('upload-preview-card').classList.remove('hidden');
    } else {
      if (typeof removeUploadedFile === 'function') removeUploadedFile();
    }

    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
    $('#chore-modal').removeClass('hidden');
  }

  // Handle Form Submit via AJAX
  $('#chore-modal-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#chore-btn-submit');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    const formData = new FormData($form[0]);

    $btn.prop('disabled', true).html('Menyimpan...');
    showLoading('Menyimpan tugas...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        closeChoreModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Tugas berhasil disimpan!');
        if (typeof window.reloadCurrentModuleData === 'function') {
          window.reloadCurrentModuleData({ data: { pending_count: res.pending_count } });
        }
      },
      error: function(xhr) {
        let msg = 'Gagal menyimpan tugas.';
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
    if (type === 'task') openChoreModal();
    else if (window.openFinanceModal) window.openFinanceModal();
    else if (window.openCalendarModal) window.openCalendarModal();
    else if (window.openTimelineModal) window.openTimelineModal();
  };

  // Compatibility helpers
  window.moduleFormHandlers = window.moduleFormHandlers || {};
  window.moduleFormHandlers['task'] = function(ctx) {
    openChoreModal();
  };

  function fetchChoresPage(page = 1) {
    if (choresDataTable) {
      choresDataTable.page(page - 1).draw('page');
    }
  }

  // REWARD & TAB SYSTEM SCRIPTS
  let currentActiveTab = 'chores';
  let targetClaimReward = null;

  function switchChoreTab(tab) {
    currentActiveTab = tab;
    const $btnChores = $('#tab-btn-chores');
    const $btnRewards = $('#tab-btn-rewards');
    const $secChores = $('#section-chores-tab');
    const $secRewards = $('#section-rewards-tab');
    const $btnAddChore = $('#header-btn-add-chore');
    const $btnAddReward = $('#header-btn-add-reward');

    if (tab === 'chores') {
      $btnChores.removeClass('bg-white text-ink hover:bg-cream').addClass('bg-ink text-white');
      $btnRewards.removeClass('bg-ink text-white').addClass('bg-white text-ink hover:bg-cream');
      
      $('#header-tagline').text('DISTRIBUSI PEKERJAAN RUMAH');
      $('#header-title').text('Manajemen Tugas & Chores');
      $('#header-subtitle').text('Bagi tugas secara adil antar anggota keluarga. Anak-anak kumpulkan poin reward.');
      
      $btnAddChore.removeClass('hidden').addClass('hidden md:inline-flex');
      $btnAddReward.addClass('hidden').removeClass('inline-flex');

      $secChores.removeClass('hidden');
      $secRewards.addClass('hidden');

      if (history.replaceState) {
        history.replaceState(null, null, '#chores');
      }
    } else {
      $btnRewards.removeClass('bg-white text-ink hover:bg-cream').addClass('bg-ink text-white');
      $btnChores.removeClass('bg-ink text-white').addClass('bg-white text-ink hover:bg-cream');
      
      $('#header-tagline').text('KATALOG & PENUKARAN REWARD');
      $('#header-title').text('Katalog Hadiah & Klaim Reward');
      $('#header-subtitle').text('Tukarkan poin hasil tugas rumah tangga dengan berbagai hadiah menarik pilihan orang tua.');
      
      $btnAddChore.addClass('hidden').removeClass('hidden md:inline-flex');
      $btnAddReward.removeClass('hidden').addClass('inline-flex');

      $secChores.addClass('hidden');
      $secRewards.removeClass('hidden');

      if (rewardsDataTable) {
        setTimeout(function() {
          rewardsDataTable.columns.adjust().draw(false);
        }, 50);
      }

      if (history.replaceState) {
        history.replaceState(null, null, '#rewards');
      }
    }
  }

  function openRewardModal() {
    $('#reward-manage-modal-form')[0].reset();
    $('#reward-manage-modal-method').val('POST');
    $('#reward-manage-modal-form').attr('action', "{{ route('rewards.store') }}");
    $('#reward-manage-modal-title').text('Tambah Hadiah Baru');
    $('#reward-manage-modal-icon').text('🎁');
    $('#btn-submit-reward').html('Simpan Hadiah &rarr;');
    selectRewardEmoji('🎁');
    $('#reward-manage-modal').removeClass('hidden');
  }

  function closeRewardModal() {
    $('#reward-manage-modal').addClass('hidden');
  }

  function selectRewardEmoji(emoji) {
    $('#reward-input-icon').val(emoji);
    $('#reward-manage-modal-icon').text(emoji);
    $('.emoji-select-btn').removeClass('bg-accent ring-2 ring-ink').addClass('bg-cream');
    $(`.emoji-select-btn[data-emoji="${emoji}"]`).removeClass('bg-cream').addClass('bg-accent ring-2 ring-ink');
  }

  function openEditRewardModal(reward) {
    $('#reward-manage-modal-form')[0].reset();
    $('#reward-manage-modal-method').val('PUT');
    $('#reward-manage-modal-form').attr('action', '/rewards/' + reward.id);
    $('#reward-manage-modal-title').text('Edit Hadiah: ' + reward.title);
    $('#btn-submit-reward').html('Perbarui Hadiah &rarr;');

    $('#reward-input-title').val(reward.title);
    $('#reward-input-points').val(reward.points);
    $('#reward-input-stock').val(reward.stock !== null ? reward.stock : '');
    $('#reward-input-description').val(reward.description || '');

    selectRewardEmoji(reward.icon || '🎁');
    $('#reward-manage-modal').removeClass('hidden');
  }

  function confirmDeleteReward(id, title) {
    if (typeof confirmDelete === 'function') {
      confirmDelete('/rewards/' + id, 'Hadiah "' + title + '"');
    } else {
      if (confirm('Yakin ingin menghapus hadiah "' + title + '"?')) {
        $.ajax({
          url: '/rewards/' + id,
          type: 'DELETE',
          data: { _token: '{{ csrf_token() }}' },
          success: function(res) {
            showToast(res.message || 'Hadiah berhasil dihapus.');
            if (rewardsDataTable) {
              rewardsDataTable.ajax.reload(null, false);
            } else {
              location.reload();
            }
          },
          error: function(err) {
            showToast(err.responseJSON ? err.responseJSON.message : 'Gagal menghapus hadiah.', 'error');
          }
        });
      }
    }
  }

  // Handle Reward Create/Edit AJAX
  $('#reward-manage-modal-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#btn-submit-reward');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    $btn.prop('disabled', true).html('Menyimpan...');
    showLoading('Menyimpan hadiah...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function(res) {
        closeRewardModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Hadiah berhasil disimpan!');
        if (rewardsDataTable) {
          rewardsDataTable.ajax.reload(null, false);
        } else {
          setTimeout(function() {
            window.location.hash = 'rewards';
            location.reload();
          }, 500);
        }
      },
      error: function(xhr) {
        let msg = 'Gagal menyimpan hadiah.';
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

  // CLAIM EXECUTION MODALS & ACTIONS
  function openClaimConfirmModal(reward) {
    targetClaimReward = reward;
    const userPoints = parseInt($('#sidebar-user-points-val').text().replace(/[^0-9]/g, ''), 10) || {{ (int)($user->points ?? 0) }};
    const cost = parseInt(reward.points, 10);
    const remaining = Math.max(0, userPoints - cost);

    $('#claim-confirm-icon').text(reward.icon || '🎁');
    $('#claim-confirm-title').text(reward.title);
    $('#claim-confirm-desc').text(reward.description || 'Tukarkan ' + cost.toLocaleString('id-ID') + ' poin untuk hadiah ini.');
    $('#claim-current-points').text(userPoints.toLocaleString('id-ID') + ' Poin');
    $('#claim-cost-points').text('- ' + cost.toLocaleString('id-ID') + ' Poin');
    $('#claim-remaining-points').text(remaining.toLocaleString('id-ID') + ' Poin');

    $('#reward-claim-confirm-modal').removeClass('hidden');
  }

  function closeClaimConfirmModal() {
    $('#reward-claim-confirm-modal').addClass('hidden');
    targetClaimReward = null;
  }

  function executeClaim() {
    if (!targetClaimReward) return;
    const $btn = $('#btn-execute-claim');
    const origText = $btn.html();
    $btn.prop('disabled', true).html('Mengklaim...');
    showLoading('Memproses penukaran poin...');

    $.ajax({
      url: '/rewards/' + targetClaimReward.id + '/claim',
      type: 'POST',
      data: { _token: '{{ csrf_token() }}' },
      dataType: 'json',
      success: function(res) {
        closeClaimConfirmModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Hadiah berhasil diklaim!');
        if (res.user_points !== undefined && typeof window.updateUserPoints === 'function') {
          window.updateUserPoints(res.user_points);
        }
        setTimeout(function() {
          window.location.hash = 'rewards';
          location.reload();
        }, 600);
      },
      error: function(xhr) {
        let msg = 'Gagal menukarkan hadiah.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
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
  }

  function openApproveModal(claimId, rewardTitle = 'Hadiah', userName = 'Anggota', points = 0) {
    $('#approve-claim-id').val(claimId);
    $('#approve-confirm-reward-title').text(rewardTitle);
    $('#approve-confirm-user-name').text(userName);
    $('#approve-confirm-points').text(points ? Number(points).toLocaleString('id-ID') + ' Poin' : '- Poin');
    $('#reward-approve-confirm-modal').removeClass('hidden');
  }

  function closeApproveModal() {
    $('#reward-approve-confirm-modal').addClass('hidden');
  }

  function approveClaim(claimId, rewardTitle = 'Hadiah', userName = 'Anggota', points = 0) {
    openApproveModal(claimId, rewardTitle, userName, points);
  }

  function executeApproveClaim() {
    const claimId = $('#approve-claim-id').val();
    if (!claimId) return;

    const $btn = $('#btn-confirm-approve');
    const origText = $btn.html();
    $btn.prop('disabled', true).html('Menyetujui...');
    showLoading('Menyetujui klaim & menyerahkan hadiah...');

    $.ajax({
      url: '/rewards/claims/' + claimId + '/approve',
      type: 'PATCH',
      data: { _token: '{{ csrf_token() }}' },
      dataType: 'json',
      success: function(res) {
        closeApproveModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Klaim berhasil disetujui!');
        setTimeout(function() {
          window.location.hash = 'rewards';
          location.reload();
        }, 500);
      },
      error: function(xhr) {
        let msg = 'Gagal menyetujui klaim.';
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        showToast(msg, 'error');
      },
      complete: function() {
        $btn.prop('disabled', false).html(origText);
        hideLoading();
      }
    });
  }

  function openRejectModal(claimId) {
    $('#reject-claim-id').val(claimId);
    $('#reject-claim-notes').val('');
    $('#reward-reject-modal').removeClass('hidden');
  }

  function closeRejectModal() {
    $('#reward-reject-modal').addClass('hidden');
  }

  function executeRejectClaim() {
    const claimId = $('#reject-claim-id').val();
    const notes = $('#reject-claim-notes').val();
    const $btn = $('#btn-confirm-reject');
    const origText = $btn.html();

    $btn.prop('disabled', true).html('Menolak...');
    showLoading('Menolak klaim & mengembalikan poin...');

    $.ajax({
      url: '/rewards/claims/' + claimId + '/reject',
      type: 'PATCH',
      data: {
        _token: '{{ csrf_token() }}',
        notes: notes
      },
      dataType: 'json',
      success: function(res) {
        closeRejectModal();
        if (typeof showToast === 'function') showToast(res.message || 'Klaim ditolak. Poin dikembalikan.');
        setTimeout(function() {
          window.location.hash = 'rewards';
          location.reload();
        }, 500);
      },
      error: function(xhr) {
        let msg = 'Gagal menolak klaim.';
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        showToast(msg, 'error');
      },
      complete: function() {
        $btn.prop('disabled', false).html(origText);
        hideLoading();
      }
    });
  }

  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_create') === '1') {
      setTimeout(openChoreModal, 200);
      window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Check URL Hash for tab selection
    if (window.location.hash === '#rewards') {
      switchChoreTab('rewards');
    }
  });
</script>
@endpush
