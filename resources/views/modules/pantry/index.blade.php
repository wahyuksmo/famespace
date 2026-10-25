@extends('layouts.main')

@section('title', 'Stok & Pantry — ' . ($family->name ?? 'FamKoji'))

@section('content')
<div class="space-y-6">

  <!-- TOP HEADER / BANNER -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-6 bg-white brutal-card">
    <div class="space-y-1">
      <div class="flex items-center gap-2">
        <span class="text-2xl sm:text-3xl">🥫</span>
        <h1 class="text-xl sm:text-2xl font-black font-display tracking-tight text-ink">Inventaris Stok & Pantry</h1>
      </div>
      <p class="text-xs sm:text-sm font-mono text-ink/70">
        Pantau persediaan dapur, kulkas, dan obat-obatan. Cegah barang habis mendadak dan mubazir kedaluwarsa.
      </p>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-auto">
      <button type="button" 
              onclick="openPantryModal()" 
              class="px-4 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider flex items-center gap-2 shadow-[2px_2px_0px_#121614] active:scale-95 transition-all">
        <span>+</span>
        <span>Catat Barang</span>
      </button>
    </div>
  </div>

  <!-- STATS OVERVIEW -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <button type="button" onclick="setPantryFilter('all')" class="p-3 sm:p-4 bg-white brutal-card hover:bg-cream transition-all flex flex-col justify-between text-left cursor-pointer">
      <span class="text-[10px] sm:text-xs font-mono font-black uppercase text-ink/60">Total Barang</span>
      <div class="flex items-baseline justify-between mt-2">
        <span class="text-xl sm:text-2xl font-display font-black text-ink" id="stat-total">{{ $summary['total'] }}</span>
        <span class="text-lg">📦</span>
      </div>
    </button>

    <button type="button" onclick="setPantryFilter('low_stock')" class="p-3 sm:p-4 {{ $summary['low_stock'] > 0 ? 'bg-coral text-white shadow-[3px_3px_0px_#121614]' : 'bg-white text-ink' }} brutal-card hover:opacity-95 transition-all flex flex-col justify-between text-left cursor-pointer" id="card-stat-low">
      <span class="text-[10px] sm:text-xs font-mono font-black uppercase {{ $summary['low_stock'] > 0 ? 'text-white/80' : 'text-ink/60' }}">Stok Menipis</span>
      <div class="flex items-baseline justify-between mt-2">
        <span class="text-xl sm:text-2xl font-display font-black" id="stat-low-stock">{{ $summary['low_stock'] }}</span>
        <span class="text-lg">⚠️</span>
      </div>
    </button>

    <button type="button" onclick="setPantryFilter('expiring_soon')" class="p-3 sm:p-4 {{ $summary['expiring_soon'] > 0 ? 'bg-accent text-ink shadow-[3px_3px_0px_#121614]' : 'bg-white text-ink' }} brutal-card hover:opacity-95 transition-all flex flex-col justify-between text-left cursor-pointer" id="card-stat-soon">
      <span class="text-[10px] sm:text-xs font-mono font-black uppercase text-ink/70">Segera Expired (H-7)</span>
      <div class="flex items-baseline justify-between mt-2">
        <span class="text-xl sm:text-2xl font-display font-black" id="stat-expiring-soon">{{ $summary['expiring_soon'] }}</span>
        <span class="text-lg">⏳</span>
      </div>
    </button>

    <button type="button" onclick="setPantryFilter('expired')" class="p-3 sm:p-4 {{ $summary['expired'] > 0 ? 'bg-ink text-white shadow-[3px_3px_0px_#FF6B6B]' : 'bg-white text-ink' }} brutal-card hover:opacity-95 transition-all flex flex-col justify-between text-left cursor-pointer" id="card-stat-exp">
      <span class="text-[10px] sm:text-xs font-mono font-black uppercase {{ $summary['expired'] > 0 ? 'text-coral' : 'text-ink/60' }}">Sudah Expired</span>
      <div class="flex items-baseline justify-between mt-2">
        <span class="text-xl sm:text-2xl font-display font-black" id="stat-expired">{{ $summary['expired'] }}</span>
        <span class="text-lg">❌</span>
      </div>
    </button>
  </div>

  <!-- FILTER PILLS & CATEGORY -->
  <div class="p-3 sm:p-4 bg-white brutal-card flex flex-wrap items-center justify-between gap-3">
    <!-- STATUS PILLS (MODUL TUGAS STYLE) -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-mono font-bold no-scrollbar">
      <button type="button" class="btn-filter-pantry px-3 py-1.5 bg-ink text-white brutal-border text-[11px] font-black uppercase transition-all" data-status="all">Semua</button>
      <button type="button" class="btn-filter-pantry px-3 py-1.5 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="low_stock">⚠️ Stok Menipis</button>
      <button type="button" class="btn-filter-pantry px-3 py-1.5 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="expiring_soon">⏳ Segera Expired</button>
      <button type="button" class="btn-filter-pantry px-3 py-1.5 bg-white text-ink brutal-border text-[11px] font-bold uppercase transition-all" data-status="expired">❌ Sudah Expired</button>
    </div>

    <!-- CATEGORY FILTER & RESET -->
    <div class="flex items-center gap-2">
      <select id="pantry-filter-category" class="px-3 py-1.5 bg-cream brutal-border text-xs font-mono font-bold focus:bg-white focus:outline-none">
        <option value="">Semua Lokasi / Kategori</option>
        <option value="dapur">🍳 Dapur & Sembako</option>
        <option value="kulkas">❄️ Kulkas & Freezer</option>
        <option value="obat">💊 Obat & P3K</option>
        <option value="kebersihan">🧹 Kebersihan Rumah</option>
        <option value="lainnya">📦 Lainnya</option>
      </select>

      <button type="button" id="pantry-filter-reset" class="px-2.5 py-1.5 bg-cream hover:bg-coral hover:text-white brutal-border text-xs font-mono font-black transition-colors" title="Reset Filter">
        Reset ✕
      </button>
    </div>
  </div>

  <!-- DATATABLES SERVERSIDE TABLE -->
  <div class="bg-white brutal-card p-3 sm:p-5 overflow-x-auto">
    <table id="pantry-datatable" class="w-full text-left border-collapse text-xs">
      <thead class="bg-cream border-b-2 border-ink uppercase font-mono text-[11px] text-ink font-black">
        <tr>
          <th class="p-3">Nama Barang</th>
          <th class="p-3">Kategori</th>
          <th class="p-3 text-center">Stok</th>
          <th class="p-3 text-center">Min</th>
          <th class="p-3">Kedaluwarsa</th>
          <th class="p-3">Status</th>
          <th class="p-3">Pencatat</th>
          <th class="p-3 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-ink/10 font-mono">
        <!-- Populated by DataTables Server-Side -->
      </tbody>
    </table>
  </div>

</div>
@endsection

@push('modals')
  @include('modules.pantry.modal')
@endpush

@push('scripts')
<script>
  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // FAMESPACE STANDAR COMBOBOX DATA UNTUK PANTRY
  window.comboboxData = {
    category: [
      { value: 'dapur', text: '🍳 Dapur & Sembako Pokok' },
      { value: 'kulkas', text: '❄️ Kulkas & Makanan Dingin' },
      { value: 'obat', text: '💊 Obat & Kotak P3K' },
      { value: 'kebersihan', text: '🧹 Alat & Bahan Kebersihan' },
      { value: 'lainnya', text: '📦 Lainnya' }
    ]
  };

  let pantryDataTable = null;
  let pantryFilterStatus = 'all';
  let pantryDatePicker = null;

  $(document).ready(function() {
    // Inisialisasi Flatpickr Tanggal Kedaluwarsa (Standar Format Famespace)
    if (typeof flatpickr !== 'undefined') {
      pantryDatePicker = flatpickr("#pantry-expired-at", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j F Y",
        locale: "id"
      });
    }

    window.pantryDataTable = pantryDataTable = $('#pantry-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('pantry.paginate') }}",
        type: 'GET',
        data: function(d) {
          d.filter = (pantryFilterStatus === 'all') ? '' : pantryFilterStatus;
          d.category = $('#pantry-filter-category').val() || '';
        },
        dataSrc: function(json) {
          if (json.summary) {
            updateSummaryCards(json.summary);
          }
          return json.data;
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50],
      language: {
        search: "Cari Barang:",
        lengthMenu: "Tampil _MENU_ data",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ barang",
        infoEmpty: "Menampilkan 0 barang",
        infoFiltered: "(disaring dari _MAX_ total data)",
        emptyTable: "Belum ada barang di inventaris pantry.",
        processing: "Memuat data...",
        paginate: {
          first: "&laquo;",
          previous: "&larr; Prev",
          next: "Next &rarr;",
          last: "&raquo;"
        }
      },
      columns: [
        {
          data: 'name',
          name: 'name',
          className: 'p-3',
          render: function(data, type, row) {
            let notes = row.notes ? `<div class="text-[11px] text-ink/60 line-clamp-1 mt-0.5">${escapeHtml(row.notes)}</div>` : '';
            return `<div><div class="font-display font-black text-sm text-ink leading-tight">${escapeHtml(data)}</div>${notes}</div>`;
          }
        },
        {
          data: 'category',
          name: 'category',
          className: 'p-3 whitespace-nowrap',
          render: function(data, type, row) {
            const catIcons = { dapur: '🍳', kulkas: '❄️', obat: '💊', kebersihan: '🧹', lainnya: '📦' };
            const catNames = { dapur: 'Dapur & Sembako', kulkas: 'Kulkas', obat: 'Obat & P3K', kebersihan: 'Kebersihan', lainnya: 'Lainnya' };
            const icon = catIcons[data] || '📦';
            const label = catNames[data] || (data ? data.charAt(0).toUpperCase() + data.slice(1) : '');
            return `<span class="px-2 py-0.5 bg-cream brutal-border text-[10px] font-bold text-ink inline-flex items-center gap-1"><span>${icon}</span><span>${escapeHtml(label)}</span></span>`;
          }
        },
        {
          data: 'quantity',
          name: 'quantity',
          className: 'p-3 whitespace-nowrap text-center',
          render: function(data, type, row) {
            return `
              <div class="inline-flex items-center gap-2 bg-cream/70 px-2 py-1 brutal-border">
                <button type="button" onclick="adjustPantryStock(${row.id}, -1)" class="w-6 h-6 bg-white hover:bg-coral hover:text-white brutal-border font-mono font-black text-xs flex items-center justify-center text-ink transition-colors" title="Kurang 1 ${escapeHtml(row.unit)}">-</button>
                <span class="font-black text-sm text-ink min-w-[3rem] text-center">
                  ${row.formatted_quantity} <span class="text-[10px] font-normal text-ink/70">${escapeHtml(row.unit)}</span>
                </span>
                <button type="button" onclick="adjustPantryStock(${row.id}, 1)" class="w-6 h-6 bg-white hover:bg-brand hover:text-white brutal-border font-mono font-black text-xs flex items-center justify-center text-ink transition-colors" title="Tambah 1 ${escapeHtml(row.unit)}">+</button>
              </div>
            `;
          }
        },
        {
          data: 'min_quantity',
          name: 'min_quantity',
          className: 'p-3 whitespace-nowrap text-center font-bold text-ink/70',
          render: function(data, type, row) {
            return `${row.formatted_min_quantity} ${escapeHtml(row.unit)}`;
          }
        },
        {
          data: 'expired_at',
          name: 'expired_at',
          className: 'p-3 whitespace-nowrap',
          render: function(data, type, row) {
            if (!row.formatted_expired_at) return `<span class="text-ink/40">-</span>`;
            const color = row.is_expired ? 'text-coral' : (row.is_expiring_soon ? 'text-amber-700' : 'text-ink/80');
            return `<span class="font-bold ${color}">${escapeHtml(row.formatted_expired_at)}</span>`;
          }
        },
        {
          data: null,
          orderable: false,
          className: 'p-3 whitespace-nowrap',
          render: function(data, type, row) {
            if (row.is_expired) {
              return `<span class="px-2 py-0.5 bg-coral text-white brutal-border text-[9px] font-black uppercase">Kedaluwarsa</span>`;
            }
            if (row.is_expiring_soon) {
              return `<span class="px-2 py-0.5 bg-accent text-ink brutal-border text-[9px] font-black uppercase">H-${row.days_until_expired}</span>`;
            }
            if (row.is_low_stock) {
              return `<span class="px-2 py-0.5 bg-amber-200 text-ink brutal-border text-[9px] font-black uppercase">Stok Tipis</span>`;
            }
            return `<span class="px-2 py-0.5 bg-sage text-ink brutal-border text-[9px] font-black uppercase">Aman</span>`;
          }
        },
        {
          data: 'creator_name',
          orderable: false,
          className: 'p-3 whitespace-nowrap text-ink/70 text-[11px]',
          render: function(data, type, row) {
            return escapeHtml(data);
          }
        },
        {
          data: null,
          orderable: false,
          className: 'p-3 whitespace-nowrap text-right',
          render: function(data, type, row) {
            const itemJson = JSON.stringify(row.raw_item).replace(/"/g, '&quot;');
            let deleteBtn = '';
            if (row.can_delete) {
              deleteBtn = `
                <button type="button" onclick="deletePantryItem(${row.id}, '${escapeHtml(row.name)}')" class="w-7 h-7 bg-white hover:bg-coral hover:text-white brutal-border text-xs flex items-center justify-center text-ink transition-colors" title="Hapus Barang">
                  🗑️
                </button>
              `;
            }
            return `
              <div class="inline-flex items-center gap-1 justify-end">
                <button type="button" onclick='editPantryItem(${itemJson})' class="w-7 h-7 bg-cream hover:bg-accent brutal-border text-xs flex items-center justify-center text-ink transition-colors" title="Edit Barang">
                  ✏️
                </button>
                ${deleteBtn}
              </div>
            `;
          }
        }
      ]
    });

    // Filter status pills click listener
    $(document).on('click', '.btn-filter-pantry', function() {
      const status = $(this).data('status') || 'all';
      setPantryFilter(status);
    });

    // Category select listener
    $('#pantry-filter-category').on('change', function() {
      pantryDataTable.ajax.reload();
    });

    // Reset button listener
    $('#pantry-filter-reset').on('click', function() {
      $('#pantry-filter-category').val('');
      setPantryFilter('all');
    });

    // AJAX Form submission
    $('#pantry-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const submitBtn = form.find('button[type="submit"]');
      submitBtn.prop('disabled', true).addClass('opacity-50');

      $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(res) {
          closePantryModal();
          pantryDataTable.ajax.reload(null, false);
        },
        error: function(err) {
          const msg = err.responseJSON?.message || (err.responseJSON?.errors ? Object.values(err.responseJSON.errors)[0][0] : 'Terjadi kesalahan.');
          alert(msg);
        },
        complete: function() {
          submitBtn.prop('disabled', false).removeClass('opacity-50');
        }
      });
    });
  });

  function setPantryFilter(status) {
    pantryFilterStatus = status;
    $('.btn-filter-pantry').each(function() {
      if ($(this).data('status') === status) {
        $(this).removeClass('bg-white text-ink font-bold').addClass('bg-ink text-white font-black');
      } else {
        $(this).removeClass('bg-ink text-white font-black').addClass('bg-white text-ink font-bold');
      }
    });
    if (pantryDataTable) {
      pantryDataTable.ajax.reload();
    }
  }

  function updateSummaryCards(summary) {
    if (!summary) return;
    $('#stat-total').text(summary.total ?? 0);
    $('#stat-low-stock').text(summary.low_stock ?? 0);
    $('#stat-expiring-soon').text(summary.expiring_soon ?? 0);
    $('#stat-expired').text(summary.expired ?? 0);
  }

  function adjustPantryStock(id, delta) {
    $.ajax({
      url: `/pantry/${id}/adjust`,
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        _method: 'PATCH',
        delta: delta
      },
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(res) {
        if (pantryDataTable) {
          pantryDataTable.ajax.reload(null, false);
        }
      },
      error: function(err) {
        alert(err.responseJSON?.message || 'Gagal mengubah stok.');
      }
    });
  }

  function deletePantryItem(id, name) {
    if (!confirm(`Hapus barang "${name}" dari inventaris pantry?`)) return;

    $.ajax({
      url: `/pantry/${id}`,
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        _method: 'DELETE'
      },
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(res) {
        if (pantryDataTable) {
          pantryDataTable.ajax.reload(null, false);
        }
      },
      error: function(err) {
        alert(err.responseJSON?.message || 'Gagal menghapus barang.');
      }
    });
  }

  function openPantryModal() {
    $('#pantry-modal-title').text('Catat Barang ke Pantry');
    $('#pantry-modal-icon').text('🥫');
    $('#pantry-btn-submit').html('Simpan Barang &rarr;');
    $('#pantry-form').attr('action', "{{ route('pantry.store') }}");
    $('#form-method').val('POST');
    $('#pantry-name').val('');
    $('#pantry-quantity').val('1');
    $('#pantry-unit').val('pcs');
    $('#pantry-min-quantity').val('1');
    $('#pantry-notes').val('');

    selectComboboxOption('category', 'dapur', '🍳 Dapur & Sembako Pokok');
    if (pantryDatePicker) pantryDatePicker.clear();

    $('#popover-category').addClass('hidden');
    $('#pantry-modal').removeClass('hidden');
  }

  function editPantryItem(item) {
    $('#pantry-modal-title').text('Ubah Data Barang');
    $('#pantry-modal-icon').text('✏️');
    $('#pantry-btn-submit').html('Simpan Perubahan &rarr;');
    $('#pantry-form').attr('action', `/pantry/${item.id}`);
    $('#form-method').val('PUT');
    $('#pantry-name').val(item.name || '');
    $('#pantry-quantity').val(parseFloat(item.quantity) || 0);
    $('#pantry-unit').val(item.unit || 'pcs');
    $('#pantry-min-quantity').val(parseFloat(item.min_quantity) || 1);
    $('#pantry-notes').val(item.notes || '');

    const catObj = comboboxData.category ? comboboxData.category.find(c => c.value === item.category) : null;
    selectComboboxOption('category', item.category || 'dapur', catObj ? catObj.text : (item.category || '🍳 Dapur & Sembako Pokok'));

    if (pantryDatePicker) {
      if (item.expired_at) {
        pantryDatePicker.setDate(item.expired_at.substring(0, 10));
      } else {
        pantryDatePicker.clear();
      }
    }

    $('#popover-category').addClass('hidden');
    $('#pantry-modal').removeClass('hidden');
  }

  function closePantryModal() {
    $('#pantry-modal').addClass('hidden');
    $('#popover-category').addClass('hidden');
  }
</script>
@endpush
