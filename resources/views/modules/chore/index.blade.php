@extends('layouts.main')

@section('title', 'Tugas & Chores — ' . ($family->name ?? 'FamKoji') . ' — FamKoji')

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
  <div class="bg-white brutal-card p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2">
        <span id="header-tagline" class="px-2.5 py-0.5 bg-accent brutal-border font-mono text-[10px] sm:text-xs font-black uppercase shadow-[1.5px_1.5px_0px_#121614]">
          DISTRIBUSI PEKERJAAN RUMAH
        </span>
      </div>
      <h2 id="header-title" class="text-xl sm:text-2xl font-black font-display text-ink mt-1 tracking-tight">Manajemen Tugas & Chores</h2>
      <p id="header-subtitle" class="text-xs font-medium text-ink/70">Bagi tugas secara adil antar anggota keluarga. Anak-anak kumpulkan poin reward.</p>
    </div>
  </div>

  <!-- SECTION: CHORES TAB -->
  <div id="section-chores-tab" class="space-y-5">

  <!-- REWARD POIN BANNER FOR KIDS: INTERACTIVE AVATAR CHIPS -->
  @php
    $kids = $members->filter(fn($m) => in_array(strtolower($m->role), ['anak', 'member']))->values();
  @endphp
  <div id="chores-kids-banner" class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
    <div class="px-2.5 py-1.5 bg-ink text-white brutal-border font-mono text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 flex-shrink-0 shadow-[2px_2px_0px_#121614]">
      <span>⭐ Poin Anak</span>
    </div>
    @forelse($kids as $idx => $kid)
      @php
        $kidDoneCount = ($allChores ?? $chores)->where('assigned_to_user_id', $kid->id)->where('status', 'completed')->count();
      @endphp
      <button 
        type="button" 
        onclick="filterByAssignee('{{ addslashes($kid->name) }}')"
        class="kid-point-chip flex items-center gap-2 px-3 py-1.5 bg-white hover:bg-brand-soft brutal-border transition-all shadow-[2px_2px_0px_#121614] hover:-translate-y-0.5 active:translate-y-0 flex-shrink-0 group cursor-pointer"
        title="Klik untuk filter tugas {{ $kid->name }} ({{ $kidDoneCount }} tuntas)"
      >
        <div class="w-6 h-6 {{ $idx % 2 === 0 ? 'bg-sky text-ink' : 'bg-coral text-white' }} brutal-border rounded-full flex items-center justify-center text-xs font-black flex-shrink-0">
          {{ $idx % 2 === 0 ? '👦' : '👧' }}
        </div>
        <span class="font-bold text-xs text-ink group-hover:text-brand">{{ $kid->name }}</span>
        <span class="px-1.5 py-0.2 bg-accent text-ink font-mono font-black text-[11px] brutal-border">
          ⭐ <span class="kid-points-val" data-user-id="{{ $kid->id }}">{{ $kid->points ?? 0 }}</span>
        </span>
      </button>
    @empty
      <div class="text-xs font-mono text-ink/60 bg-cream brutal-border px-3 py-1.5 shadow-[2px_2px_0px_#121614]">
        💡 Tambahkan anggota anak untuk mengaktifkan celengan poin.
      </div>
    @endforelse
  </div>

  @php
    $completedCount = ($allChores ?? $chores)->where('status', 'completed')->count();
    $totalCount = max(($allChores ?? $chores)->count(), 1);
    $progressPercent = ($allChores ?? $chores)->count() > 0 ? round(($completedCount / $totalCount) * 100) : 0;
  @endphp

  <!-- CHORES LIST BY SECTION WITH SERVERSIDE DATATABLES -->
  <div class="bg-white brutal-card p-4 sm:p-6 space-y-4">
    <!-- TOP BAR: Title & Interactive Progress Bar -->
    <div class="space-y-2.5 pb-3 border-b-2 border-ink">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2">
          <span class="inline-block w-2.5 h-2.5 rounded-full bg-accent brutal-border animate-pulse"></span>
          <h3 class="font-black font-display text-base text-ink">Checklist Tugas Aktif</h3>
        </div>
        <div class="flex items-center gap-2 font-mono text-xs font-bold text-ink/80">
          <span>Progres:</span>
          <span id="chores-completion-stat" class="px-2 py-0.5 bg-brand-soft text-brand brutal-border font-black text-[11px]">
            {{ $completedCount }}/{{ ($allChores ?? $chores)->count() }} Selesai (<span id="chores-progress-percent">{{ $progressPercent }}%</span>)
          </span>
        </div>
      </div>
      <!-- Progress Bar Visual Neobrutalist -->
      <div class="w-full bg-cream brutal-border h-3 p-0.5 overflow-hidden">
        <div 
          id="chores-progress-bar" 
          class="bg-accent h-full transition-all duration-500 ease-out" 
          style="width: {{ $progressPercent }}%"
        ></div>
      </div>
    </div>

    <!-- SEGMENTED TABS UNTUK DESKTOP -->
    <div class="hidden md:flex items-center justify-between gap-2">
      <div class="flex items-center gap-1.5 p-1 bg-cream brutal-border text-xs font-mono font-bold">
        <button 
          type="button" 
          class="btn-segment-chore px-4 py-1.5 bg-ink text-white brutal-border text-xs font-black uppercase transition-all shadow-brutal-sm flex items-center gap-1.5 cursor-pointer" 
          data-status="pending"
        >
          <span>📋 Perlu Dikerjakan</span>
          <span id="badge-pending-count" class="px-1.5 py-0.2 bg-accent text-ink text-[10px] font-mono font-black rounded-sm">{{ ($allChores ?? $chores)->where('status', 'pending')->count() }}</span>
        </button>
        <button 
          type="button" 
          class="btn-segment-chore px-4 py-1.5 bg-white text-ink text-xs font-bold uppercase transition-all hover:bg-white/80 flex items-center gap-1.5 cursor-pointer" 
          data-status="completed"
        >
          <span>✓ Selesai</span>
          <span id="badge-completed-count" class="px-1.5 py-0.2 bg-ink/10 text-ink text-[10px] font-mono font-bold rounded-sm">{{ ($allChores ?? $chores)->where('status', 'completed')->count() }}</span>
        </button>
      </div>
      <div class="text-[11px] font-mono text-ink/50">
        💡 Hover kartu untuk aksi cepat
      </div>
    </div>

    @php
      $pendingChores = ($allChores ?? $chores)->where('status', 'pending')->values();
      $pendingCount = count($pendingChores);
      $totalChores = ($allChores ?? $chores)->count();
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
      
      <!-- MOBILE QUICK SEARCH & SEGMENTED TABS -->
      <div class="space-y-2.5">
        <div class="relative">
          <input 
            type="text" 
            id="chores-mobile-search" 
            placeholder="Cari tugas atau nama anggota..." 
            class="w-full pl-9 pr-3 py-2 bg-white brutal-border font-sans text-xs font-bold text-ink placeholder:text-ink/40 focus:outline-none focus:bg-brand-soft shadow-brutal-sm transition-colors"
          >
          <span class="absolute left-3 top-2.5 text-xs text-ink/50">🔍</span>
        </div>
        
        <!-- Mobile Segmented Tabs -->
        <div class="grid grid-cols-2 gap-1.5 p-1 bg-cream brutal-border text-xs font-mono font-bold">
          <button 
            type="button" 
            class="btn-msegment-chore py-2 px-2 bg-ink text-white brutal-border text-[11px] font-black uppercase transition-all shadow-brutal-sm flex items-center justify-center gap-1.5 cursor-pointer" 
            data-status="pending"
          >
            <span>📋 Perlu Dikerjakan</span>
            <span id="mbadge-pending-count" class="px-1.5 py-0.2 bg-accent text-ink text-[10px] font-mono font-black rounded-sm">{{ $pendingCount }}</span>
          </button>
          <button 
            type="button" 
            class="btn-msegment-chore py-2 px-2 bg-white text-ink text-[11px] font-bold uppercase transition-all hover:bg-white/80 flex items-center justify-center gap-1.5 cursor-pointer" 
            data-status="completed"
          >
            <span>✓ Selesai</span>
            <span id="mbadge-completed-count" class="px-1.5 py-0.2 bg-ink/10 text-ink text-[10px] font-mono font-bold rounded-sm">{{ ($allChores ?? $chores)->where('status', 'completed')->count() }}</span>
          </button>
        </div>
      </div>

      <!-- CARD FEED CONTAINER -->
      <div id="chores-mobile-card-list" class="space-y-3">
        @if($pendingCount > 0)
          @foreach($pendingChores as $chore)
            @php
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
              $dueLabel = $chore->description ? $chore->description : ($chore->due_date ? 'Batas: '.\Carbon\Carbon::parse($chore->due_date)->locale('id')->isoFormat('D MMM Y') : null);
            @endphp
            <div class="chore-card group relative p-3.5 bg-white brutal-border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-brutal shadow-brutal-sm flex flex-col justify-between gap-3 mobile-card-item" data-id="{{ $chore->id }}">
              <div class="flex items-start gap-3 min-w-0">
                <input 
                  type="checkbox" 
                  onchange="toggleTask(this, {{ $chore->id }})" 
                  class="chore-checkbox w-5 h-5 mt-0.5 brutal-border accent-brand cursor-pointer transition-transform active:scale-90 flex-shrink-0"
                >
                <div class="min-w-0 flex-1">
                  <div class="flex items-start justify-between gap-2">
                    <span class="chore-title text-xs sm:text-sm font-black text-ink leading-snug break-words transition-all">
                      {{ $chore->title }}
                    </span>
                    <span class="chore-points-badge px-2 py-0.5 bg-accent text-ink brutal-border font-mono font-black text-[10px] shadow-[1px_1px_0px_#121614] inline-flex items-center gap-0.5 flex-shrink-0">
                      ⭐ +{{ $chore->points }} Poin
                    </span>
                  </div>

                  <div class="flex items-center gap-1.5 mt-2 flex-wrap text-[10px] sm:text-[11px]">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-soft border border-ink/20 font-bold text-ink">
                      👤 {{ $chore->assignee ? $chore->assignee->name : 'Semua Anggota' }}
                    </span>
                    @if($dueLabel)
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-cream border border-ink/20 font-mono text-coral font-bold">
                        📅 {{ $dueLabel }}
                      </span>
                    @endif
                  </div>
                </div>
              </div>

              <div class="pt-2 border-t border-ink/10 flex items-center justify-end gap-1.5">
                @if($attachmentUrl)
                  <button 
                    type="button" 
                    onclick="showImagePreview('{{ $attachmentUrl }}', 'Foto Tugas: {{ addslashes($chore->title) }}')" 
                    class="w-7 h-7 flex items-center justify-center bg-white hover:bg-sky brutal-border text-xs shadow-[1px_1px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                    title="Foto Panduan"
                  >
                    📷
                  </button>
                @endif
                <button 
                  type="button" 
                  onclick='openEditChoreModal({!! $chorePayload !!})' 
                  class="w-7 h-7 flex items-center justify-center bg-white hover:bg-accent brutal-border text-xs shadow-[1px_1px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                  title="Edit Tugas"
                >
                  ✏️
                </button>
                <button 
                  type="button" 
                  onclick="confirmDelete('{{ route('chores.destroy', $chore->id) }}', 'tugas {{ addslashes($chore->title) }}')" 
                  class="w-7 h-7 flex items-center justify-center bg-white hover:bg-coral hover:text-white brutal-border text-xs shadow-[1px_1px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                  title="Hapus Tugas"
                >
                  🗑️
                </button>
              </div>
            </div>
          @endforeach
        @else
          <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60 shadow-[2px_2px_0px_#121614]">
            🎉 Semua tugas selesai! Tidak ada tugas yang perlu dikerjakan saat ini.
          </div>
        @endif
      </div>

      <!-- MOBILE PAGINATION ("MUAT LEBIH BANYAK") -->
      <div id="chores-mobile-pagination-box" class="pt-1 text-center space-y-2">
        <div id="chores-mobile-counter" class="text-[11px] font-mono font-bold text-ink/70">
          Menampilkan <span id="chores-mobile-count">{{ $pendingCount }}</span> dari <span id="chores-mobile-total">{{ $pendingCount }}</span> tugas aktif
        </div>
        <button 
          type="button" 
          id="chores-mobile-load-more" 
          onclick="loadMoreChoresMobile()" 
          class="hidden w-full py-2.5 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-2 active:scale-[0.99] transition-all"
        >
          <span>⬇ Muat Lebih Banyak</span>
        </button>
        <div id="chores-mobile-all-loaded" class="py-2.5 bg-cream brutal-border font-mono text-[11px] font-black text-ink/60">
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
  let choresFilterStatus = 'pending';

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
          d.status = choresFilterStatus;
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
            const dueLabel = row.description ? escapeHtml(row.description) : (row.formatted_due ? 'Batas: ' + row.formatted_due : null);
            const photoBtn = row.attachment_url ? `
              <button 
                type="button" 
                onclick="showImagePreview('${row.attachment_url}', 'Foto Tugas: ${escapeHtml(row.title)}')" 
                class="w-8 h-8 flex items-center justify-center bg-white hover:bg-sky brutal-border text-xs shadow-[1.5px_1.5px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                title="Lihat Foto Bukti"
              >
                📷
              </button>
            ` : '';

            return `
              <div class="chore-card group relative p-4 bg-white brutal-border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-brutal ${isDone ? 'is-completed bg-[#FAFAF8] opacity-75' : 'shadow-brutal-sm'} flex items-center justify-between gap-4" data-id="${row.id}">
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                  <input 
                    type="checkbox" 
                    ${isDone ? 'checked' : ''} 
                    onchange="toggleTask(this, ${row.id})" 
                    class="chore-checkbox w-5 h-5 brutal-border accent-brand cursor-pointer transition-transform active:scale-90 hover:scale-105 flex-shrink-0"
                  >
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="chore-title text-sm font-black text-ink leading-snug break-words transition-all ${isDone ? 'line-through text-ink/40' : ''}">
                        ${escapeHtml(row.title)}
                      </span>
                      <span class="chore-points-badge px-2 py-0.5 bg-accent text-ink brutal-border font-mono font-black text-[11px] shadow-[1px_1px_0px_#121614] inline-flex items-center gap-1 transition-transform group-hover:scale-105 flex-shrink-0">
                        ⭐ +${row.points} Poin
                      </span>
                    </div>

                    <div class="flex items-center gap-2 mt-1.5 flex-wrap text-xs">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-soft border border-ink/20 font-bold text-[11px] text-ink">
                        <span>👤</span> ${escapeHtml(row.assignee_name || 'Semua Anggota')}
                      </span>
                      ${dueLabel ? `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-cream border border-ink/20 font-mono text-[11px] ${isDone ? 'text-ink/50' : 'text-coral font-bold'}">
                          <span>📅</span> ${dueLabel}
                        </span>
                      ` : ''}
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-1.5 opacity-90 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-150 flex-shrink-0">
                  ${photoBtn}
                  <button 
                    type="button" 
                    onclick='openEditChoreModal(${payload})' 
                    class="w-8 h-8 flex items-center justify-center bg-white hover:bg-accent brutal-border text-xs shadow-[1.5px_1.5px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                    title="Edit Tugas"
                  >
                    ✏️
                  </button>
                  <button 
                    type="button" 
                    onclick="confirmDelete('${row.destroy_url}', 'tugas ${escapeHtml(row.title)}')" 
                    class="w-8 h-8 flex items-center justify-center bg-white hover:bg-coral hover:text-white brutal-border text-xs shadow-[1.5px_1.5px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
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
  let choresMobileStart = {{ $pendingCount }};
  const choresMobileLength = 5;
  let choresMobileTotal = {{ $pendingCount }};
  let choresMobileFilterStatus = 'pending';
  let choresMobileSearch = '';
  let choresMobileLoading = false;

  function renderMobileChoreCard(row) {
    const isDone = row.status === 'completed';
    const payload = JSON.stringify(row).replace(/"/g, '&quot;');
    const dueLabel = row.description ? escapeHtml(row.description) : (row.formatted_due ? 'Batas: ' + row.formatted_due : null);
    const photoBtn = row.attachment_url ? `
      <button 
        type="button" 
        onclick="showImagePreview('${row.attachment_url}', 'Foto Tugas: ${escapeHtml(row.title)}')" 
        class="w-7 h-7 flex items-center justify-center bg-white hover:bg-sky brutal-border text-xs shadow-[1px_1px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
        title="Lihat Foto Bukti"
      >
        📷
      </button>
    ` : '';

    return `
      <div class="chore-card group relative p-3.5 bg-white brutal-border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-brutal ${isDone ? 'is-completed bg-[#FAFAF8] opacity-75' : 'shadow-brutal-sm'} flex flex-col justify-between gap-3 mobile-card-item" data-id="${row.id}">
        <div class="flex items-start gap-3 min-w-0">
          <input 
            type="checkbox" 
            ${isDone ? 'checked' : ''} 
            onchange="toggleTask(this, ${row.id})" 
            class="chore-checkbox w-5 h-5 mt-0.5 brutal-border accent-brand cursor-pointer transition-transform active:scale-90 flex-shrink-0"
          >
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
              <span class="chore-title text-xs sm:text-sm font-black text-ink leading-snug break-words transition-all ${isDone ? 'line-through text-ink/40' : ''}">
                ${escapeHtml(row.title)}
              </span>
              <span class="chore-points-badge px-2 py-0.5 bg-accent text-ink brutal-border font-mono font-black text-[10px] shadow-[1px_1px_0px_#121614] inline-flex items-center gap-0.5 flex-shrink-0">
                ⭐ +${row.points} Poin
              </span>
            </div>

            <div class="flex items-center gap-1.5 mt-2 flex-wrap text-[10px] sm:text-[11px]">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-soft border border-ink/20 font-bold text-ink">
                👤 ${escapeHtml(row.assignee_name || 'Semua Anggota')}
              </span>
              ${dueLabel ? `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-cream border border-ink/20 font-mono ${isDone ? 'text-ink/50' : 'text-coral font-bold'}">
                  📅 ${dueLabel}
                </span>
              ` : ''}
            </div>
          </div>
        </div>

        <div class="pt-2 border-t border-ink/10 flex items-center justify-end gap-1.5">
          ${photoBtn}
          <button 
            type="button" 
            onclick='openEditChoreModal(${payload})' 
            class="w-7 h-7 flex items-center justify-center bg-white hover:bg-accent brutal-border text-xs shadow-[1px_1px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
            title="Edit Tugas"
          >
            ✏️
          </button>
          <button 
            type="button" 
            onclick="confirmDelete('${row.destroy_url}', 'tugas ${escapeHtml(row.title)}')" 
            class="w-7 h-7 flex items-center justify-center bg-white hover:bg-coral hover:text-white brutal-border text-xs shadow-[1px_1px_0px_#121614] active:translate-x-0.5 active:translate-y-0.5 transition-all"
            title="Hapus Tugas"
          >
            🗑️
          </button>
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
    $(document).on('click', '.btn-segment-chore, .btn-msegment-chore', function() {
      const status = $(this).data('status') || 'pending';
      choresFilterStatus = status;
      choresMobileFilterStatus = status;

      $('.btn-segment-chore').each(function() {
        if ($(this).data('status') === status) {
          $(this).removeClass('bg-white text-ink hover:bg-white/80').addClass('bg-ink text-white brutal-border shadow-brutal-sm');
          $(this).find('span:last-child').removeClass('bg-ink/10 text-ink').addClass('bg-accent text-ink font-black');
        } else {
          $(this).removeClass('bg-ink text-white brutal-border shadow-brutal-sm').addClass('bg-white text-ink hover:bg-white/80');
          $(this).find('span:last-child').removeClass('bg-accent text-ink font-black').addClass('bg-ink/10 text-ink font-bold');
        }
      });

      $('.btn-msegment-chore').each(function() {
        if ($(this).data('status') === status) {
          $(this).removeClass('bg-white text-ink hover:bg-white/80').addClass('bg-ink text-white brutal-border shadow-brutal-sm');
          $(this).find('span:last-child').removeClass('bg-ink/10 text-ink').addClass('bg-accent text-ink font-black');
        } else {
          $(this).removeClass('bg-ink text-white brutal-border shadow-brutal-sm').addClass('bg-white text-ink hover:bg-white/80');
          $(this).find('span:last-child').removeClass('bg-accent text-ink font-black').addClass('bg-ink/10 text-ink font-bold');
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

  let totalChoresCount = {{ max(($allChores ?? $chores)->count(), 0) }};
  let completedChoresCount = {{ ($allChores ?? $chores)->where('status', 'completed')->count() }};
  let pendingChoresCount = {{ ($allChores ?? $chores)->where('status', 'pending')->count() }};
  let activeAssigneeFilter = null;

  function filterByAssignee(name) {
    if (activeAssigneeFilter === name) {
      activeAssigneeFilter = null;
      $('.kid-point-chip').removeClass('ring-2 ring-brand bg-brand-soft');
      if (choresDataTable) choresDataTable.search('').draw();
      $('#chores-mobile-search').val('');
      choresMobileSearch = '';
      if (typeof refreshChoresMobile === 'function') refreshChoresMobile();
      if (typeof showToast === 'function') showToast('Menampilkan semua tugas');
    } else {
      activeAssigneeFilter = name;
      $('.kid-point-chip').removeClass('ring-2 ring-brand bg-brand-soft');
      $(`.kid-point-chip[title*="${name}"]`).addClass('ring-2 ring-brand bg-brand-soft');
      if (choresDataTable) choresDataTable.search(name).draw();
      $('#chores-mobile-search').val(name);
      choresMobileSearch = name;
      if (typeof refreshChoresMobile === 'function') refreshChoresMobile();
      if (typeof showToast === 'function') showToast(`Menyaring tugas: ${name}`);
    }
  }

  function updateCompletionStatsOptimistic(isCompleted) {
    if (isCompleted) {
      completedChoresCount = Math.min(totalChoresCount, completedChoresCount + 1);
      pendingChoresCount = Math.max(0, pendingChoresCount - 1);
    } else {
      completedChoresCount = Math.max(0, completedChoresCount - 1);
      pendingChoresCount = Math.min(totalChoresCount, pendingChoresCount + 1);
    }
    const percent = totalChoresCount > 0 ? Math.round((completedChoresCount / totalChoresCount) * 100) : 0;
    $('#chores-progress-bar').css('width', percent + '%');
    $('#chores-progress-percent').text(percent + '%');
    $('#chores-completion-stat').html(`${completedChoresCount}/${totalChoresCount} Selesai (<span id="chores-progress-percent">${percent}%</span>)`);

    // Update segmented tab counters
    $('#badge-pending-count, #mbadge-pending-count').text(pendingChoresCount);
    $('#badge-completed-count, #mbadge-completed-count').text(completedChoresCount);
    $('#tab-btn-chores span:last-child').text(pendingChoresCount);
  }

  function toggleTask(checkbox, choreId) {
    const $checkbox = $(checkbox);
    const $card = $checkbox.closest('.chore-card, [data-id]');
    const $text = $card.find('.chore-title');
    const $badge = $card.find('.chore-points-badge');

    const $choreCount = $('#sidebar-chore-count');
    const prevCount = parseInt($choreCount.text(), 10) || 0;
    const isChecked = checkbox.checked;

    // Optimistic UI updates
    if (isChecked) {
      $text.addClass('line-through text-ink/40');
      $card.addClass('is-completed bg-[#FAFAF8] opacity-75').removeClass('shadow-brutal-sm');
      $choreCount.text(Math.max(0, prevCount - 1));
      
      // Micro-bounce animations
      $badge.addClass('scale-110 bg-brand text-white transition-transform');
      setTimeout(() => $badge.removeClass('scale-110 bg-brand text-white'), 300);
      $checkbox.addClass('scale-125 transition-transform');
      setTimeout(() => $checkbox.removeClass('scale-125'), 200);

      const rect = checkbox.getBoundingClientRect();
      if (typeof fireConfetti === 'function') {
        fireConfetti(rect.left + rect.width / 2, rect.top + rect.height / 2);
      }

      // Transisi halus keluar 800ms jika sedang di tab "Perlu Dikerjakan"
      if (choresFilterStatus === 'pending') {
        setTimeout(function() {
          $card.slideUp(300, function() {
            $(this).remove();
            if ($('#chores-mobile-card-list .chore-card').length === 0) {
              $('#chores-mobile-card-list').html(`
                <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60 shadow-[2px_2px_0px_#121614]">
                  🎉 Semua tugas tuntas! Tidak ada tugas yang perlu dikerjakan saat ini.
                </div>
              `);
            }
          });
        }, 800);
      }
    } else {
      $text.removeClass('line-through text-ink/40');
      $card.removeClass('is-completed bg-[#FAFAF8] opacity-75').addClass('shadow-brutal-sm');
      $choreCount.text(prevCount + 1);

      // Transisi halus keluar 800ms jika sedang di tab "Selesai"
      if (choresFilterStatus === 'completed') {
        setTimeout(function() {
          $card.slideUp(300, function() {
            $(this).remove();
            if ($('#chores-mobile-card-list .chore-card').length === 0) {
              $('#chores-mobile-card-list').html(`
                <div class="p-6 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60 shadow-[2px_2px_0px_#121614]">
                  Belum ada tugas yang selesai.
                </div>
              `);
            }
          });
        }, 800);
      }
    }

    updateCompletionStatsOptimistic(isChecked);

    if (choreId) {
      $checkbox.prop('disabled', true);

      $.ajax({
        url: '/chores/' + choreId + '/toggle',
        type: 'PATCH',
        dataType: 'json',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          if (data.success) {
            const pts = data.points_awarded || 0;
            if (pts > 0) {
              if (typeof showToast === 'function') showToast(`Tugas selesai! +${pts} Poin ⭐`);
            } else {
              if (typeof showToast === 'function') showToast('Status tugas dikembalikan');
            }
            if (data.user_points !== undefined && typeof window.updateUserPoints === 'function') {
              window.updateUserPoints(data.user_points);
            }
            if (data.user_id) {
              $(`.kid-points-val[data-user-id="${data.user_id}"]`).text(data.user_points);
            }
            if (typeof window.reloadCurrentModuleData === 'function') {
              window.reloadCurrentModuleData({ data: data });
            }
          }
        },
        error: function(err) {
          if (typeof showToast === 'function') showToast('Gagal memperbarui status tugas.', 'error');
          checkbox.checked = !isChecked;
          if (checkbox.checked) {
            $text.addClass('line-through text-ink/40');
            $card.addClass('is-completed bg-[#FAFAF8] opacity-75');
            updateCompletionStatsOptimistic(true);
          } else {
            $text.removeClass('line-through text-ink/40');
            $card.removeClass('is-completed bg-[#FAFAF8] opacity-75');
            updateCompletionStatsOptimistic(false);
          }
          $choreCount.text(prevCount);
        },
        complete: function() {
          $checkbox.prop('disabled', false);
        }
      });
    }
  }

  window.reloadCurrentModuleData = function(event) {
    if (choresDataTable) choresDataTable.ajax.reload(null, false);
    if (typeof refreshChoresMobile === 'function') refreshChoresMobile();
  };

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
    const cameraInput = document.getElementById('minput-camera');
    if (cameraInput) cameraInput.value = '';
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
    const cameraInput = document.getElementById('minput-camera');
    if (cameraInput && cameraInput.files && cameraInput.files[0] && (!formData.get('attachment') || formData.get('attachment').size === 0)) {
      formData.set('attachment', cameraInput.files[0]);
    }

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

    if (tab === 'chores') {
      $btnChores.removeClass('bg-white text-ink hover:bg-cream').addClass('bg-ink text-white');
      $btnRewards.removeClass('bg-ink text-white').addClass('bg-white text-ink hover:bg-cream');
      
      $('#header-tagline').text('DISTRIBUSI PEKERJAAN RUMAH');
      $('#header-title').text('Manajemen Tugas & Chores');
      $('#header-subtitle').text('Bagi tugas secara adil antar anggota keluarga. Anak-anak kumpulkan poin reward.');

      $secChores.removeClass('hidden');
      $secRewards.addClass('hidden');

      // Adaptif bottom nav button ke Tambah Tugas
      $('#bottom-nav-center-btn').attr('onclick', "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openChoreModal === 'function') openChoreModal();").attr('title', 'Tambah Tugas').attr('aria-label', 'Tambah Tugas');

      if (history.replaceState) {
        history.replaceState(null, null, '#chores');
      }
    } else {
      $btnRewards.removeClass('bg-white text-ink hover:bg-cream').addClass('bg-ink text-white');
      $btnChores.removeClass('bg-ink text-white').addClass('bg-white text-ink hover:bg-cream');
      
      $('#header-tagline').text('KATALOG & PENUKARAN REWARD');
      $('#header-title').text('Katalog Hadiah & Klaim Reward');
      $('#header-subtitle').text('Tukarkan poin hasil tugas rumah tangga dengan berbagai hadiah menarik pilihan orang tua.');

      $secChores.addClass('hidden');
      $secRewards.removeClass('hidden');

      // Adaptif bottom nav button ke Tambah Hadiah (Orang Tua)
      const isParentUser = {{ ($isParent ?? (auth()->user()?->isParent() ?? false)) ? 'true' : 'false' }};
      if (isParentUser) {
        $('#bottom-nav-center-btn').attr('onclick', "if(window.vibrateDevice) window.vibrateDevice(12); if(typeof openRewardModal === 'function') openRewardModal();").attr('title', 'Tambah Hadiah').attr('aria-label', 'Tambah Hadiah');
      }

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
