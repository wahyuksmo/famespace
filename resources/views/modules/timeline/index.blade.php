@extends('layouts.main')

@section('title', 'Linimasa & Trip — famespace')

@push('styles')
<!-- LEAFLET CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
  /* Custom Neobrutalist Leaflet Styling */
  .leaflet-popup-content-wrapper {
    background: #FFFAF0 !important;
    border: 2px solid #111111 !important;
    border-radius: 0 !important;
    box-shadow: 4px 4px 0 #111111 !important;
    padding: 0 !important;
  }
  .leaflet-popup-content {
    margin: 10px 12px !important;
    line-height: 1.4 !important;
  }
  .leaflet-popup-tip-container {
    display: none !important;
  }
  .leaflet-container {
    font-family: 'Plus Jakarta Sans', sans-serif !important;
  }
  .custom-brutal-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #34D399;
    border: 2px solid #111111;
    box-shadow: 2px 2px 0 #111111;
    font-size: 14px;
    font-weight: 900;
    width: 32px;
    height: 32px;
    border-radius: 9999px;
    cursor: pointer;
    transition: transform 0.15s ease, background 0.15s ease;
  }
  .custom-brutal-marker:hover {
    transform: scale(1.18);
    background: #FF5A36;
    color: #ffffff;
  }
</style>
@endpush

@section('content')
<!-- TAB 4: LINIMASA & TRIP (JEJAK & PENGELUARAN) -->
<div id="view-timeline" class="space-y-6">
  
  <!-- SUB-NAVIGATION: KALENDER & LINIMASA -->
  <div class="flex items-center gap-1.5 p-1 bg-white brutal-border w-full sm:w-fit rounded-lg shadow-[2px_2px_0px_#121614] select-none">
    <a href="{{ route('events.index') }}" 
       class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 px-3 py-1.5 rounded border border-transparent font-mono text-xs font-bold text-ink/70 hover:text-ink hover:bg-cream transition-colors">
      <span>📅</span>
      <span>Agenda Kalender</span>
    </a>
    <a href="{{ route('timeline.index') }}" 
       class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 px-3 py-1.5 rounded brutal-border font-mono text-xs font-black bg-coral text-white shadow-[1.5px_1.5px_0px_#121614]">
      <span>🗺️</span>
      <span>Linimasa & Kenangan</span>
    </a>
  </div>
  
  <!-- HEADER LINIMASA -->
  <div class="bg-white brutal-card p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <span class="px-2.5 py-0.5 bg-coral text-white brutal-border font-mono text-[10px] sm:text-xs font-black uppercase">
        JEJAK & KENANGAN AKTIVITAS KELUARGA
      </span>
      <h2 class="text-xl sm:text-2xl font-black font-display text-ink mt-1">Linimasa & Pengeluaran Trip</h2>
      <p class="text-xs font-medium text-ink/70">Kemana aja, ngapain aja, dan berapa pengeluarannya — semua terdokumentasi rapi.</p>
    </div>
    <button type="button" onclick="openTimelineModal()" class="hidden md:inline-flex items-center justify-center px-4 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider text-center">
      + Tambah Momen / Trip Baru
    </button>
  </div>

  <!-- PETA JEJAK LIBURAN & MOMEN KELUARGA (LEAFLET) -->
  <div class="bg-white brutal-card p-4 sm:p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b-2 border-ink gap-2">
      <div class="flex items-center gap-3">
        <span class="w-10 h-10 bg-accent text-ink brutal-border flex items-center justify-center text-xl flex-shrink-0">
          🗺️
        </span>
        <div>
          <h3 class="text-lg font-black font-display text-ink">Peta Jejak Petualangan & Liburan</h3>
          <p class="text-xs font-medium text-ink/70">Pin lokasi kenangan keluarga yang terekam di linimasa.</p>
        </div>
      </div>
      <div class="flex items-center gap-2 self-start sm:self-center">
        <span id="map-counter-badge" class="px-2.5 py-1 bg-cream brutal-border font-mono text-xs font-bold text-ink">
          Memuat lokasi...
        </span>
        <button type="button" onclick="fitTimelineMapBounds()" class="px-3 py-1 bg-accent hover:bg-white brutal-border text-xs font-bold transition-colors" title="Tampilkan seluruh titik di peta">
          🔍 Reset Tampilan
        </button>
      </div>
    </div>

    <!-- LEAFLET MAP CONTAINER -->
    <div id="timeline-main-map" class="w-full h-64 sm:h-80 md:h-96 brutal-border z-0 bg-[#e5e3df] relative"></div>
  </div>

  <!-- TIMELINE FEED WITH SERVERSIDE DATATABLES -->
  <div class="card-datatable">
    <table id="timeline-datatable" class="w-full">
      <thead>
        <tr>
          <th>Momen & Liburan</th>
        </tr>
      </thead>
      <tbody class="space-y-6">
        <!-- Populated by DataTables Server-Side -->
      </tbody>
    </table>
  </div>

</div>
@endsection

@push('modals')
  @include('modules.timeline.modal')
@endpush

@push('scripts')
<!-- LEAFLET JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
  let timelineDataTable = null;
  let timelineDatePicker = null;
  let mainTimelineMap = null;
  let mainMapMarkers = [];
  let pickerMap = null;
  let pickerMarker = null;

  // Custom neobrutalist marker icon
  const brutalMarkerIcon = L.divIcon({
    className: 'leaflet-none',
    html: '<div class="custom-brutal-marker">📍</div>',
    iconSize: [32, 32],
    iconAnchor: [16, 30],
    popupAnchor: [0, -28]
  });

  const pickerMarkerIcon = L.divIcon({
    className: 'leaflet-none',
    html: '<div class="custom-brutal-marker" style="background:#FF5A36;color:#ffffff;">📌</div>',
    iconSize: [32, 32],
    iconAnchor: [16, 30]
  });

  $(document).ready(function() {
    // 1. Inisialisasi Flatpickr Tanggal Momen
    if (typeof flatpickr !== 'undefined') {
      timelineDatePicker = flatpickr("#timeline-input-date", {
        locale: "id",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j F Y",
        defaultDate: new Date()
      });
    }

    // 2. Inisialisasi Peta Utama Linimasa
    initMainTimelineMap();

    // 3. Inisialisasi Peta Picker di Modal
    initPickerMap();

    // 4. Inisialisasi Server-Side jQuery DataTables untuk Linimasa & Trip (Card View)
    timelineDataTable = $('#timeline-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('timeline.paginate') }}",
        type: 'GET'
      },
      pageLength: 4,
      lengthMenu: [4, 8, 16],
      language: {
        search: "Cari Momen/Trip:",
        lengthMenu: "Tampil _MENU_",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ momen",
        infoEmpty: "0 momen",
        infoFiltered: "(disaring dari _MAX_ data)",
        emptyTable: "Belum Ada Momen & Liburan Keluarga. Abadikan kenangan seru sekarang!",
        processing: "Memuat momen...",
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
            const storyHtml = row.story ? `
              <p class="text-xs font-semibold text-ink leading-relaxed">
                "${escapeHtml(row.story)}"
              </p>
            ` : '';

            const imageHtml = row.image_url ? `
              <div class="pt-2 w-full max-w-full overflow-hidden">
                <div 
                  class="relative group cursor-pointer overflow-hidden brutal-border bg-ink/5" 
                  onclick="showImagePreview('${row.image_url}', '${escapeHtml(row.title)}')"
                  title="Klik untuk melihat foto ukuran penuh"
                >
                  <img 
                    src="${row.image_url}" 
                    alt="${escapeHtml(row.title)}" 
                    loading="lazy" 
                    class="w-full h-48 sm:h-64 md:h-80 object-cover object-center block max-w-full transition-transform duration-300 group-hover:scale-[1.02]"
                  >
                  <div class="absolute bottom-2 right-2 px-2 py-1 bg-ink/85 text-white font-mono text-[10px] font-bold brutal-border backdrop-blur-sm pointer-events-none flex items-center gap-1 shadow-sm">
                    <span>🔍 Perbesar</span>
                  </div>
                </div>
              </div>
            ` : '';

            const locationBadge = row.location_name ? `
              <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-cream brutal-border text-[11px] font-bold text-ink">
                📍 ${escapeHtml(row.location_name)}
              </span>
            ` : '';

            const mapJumpBtn = (row.latitude && row.longitude) ? `
              <button 
                type="button" 
                onclick="focusOnMap(${row.latitude}, ${row.longitude}, '${escapeHtml(row.title)}', ${row.id})"
                class="inline-flex items-center gap-1 px-2 py-0.5 bg-accent hover:bg-brand hover:text-white brutal-border text-[11px] font-bold transition-colors"
                title="Pusatkan di Peta"
              >
                🗺️ Lihat di Peta
              </button>
            ` : '';

            const locationRow = (locationBadge || mapJumpBtn) ? `
              <div class="flex flex-wrap items-center gap-2 pt-0.5">
                ${locationBadge}
                ${mapJumpBtn}
              </div>
            ` : '';

            return `
              <div class="bg-white brutal-card p-3.5 sm:p-6 space-y-3 sm:space-y-4 max-w-full overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-3 border-b-2 border-ink gap-3">
                  <div class="flex items-center gap-3 min-w-0 flex-1">
                    <span class="w-10 h-10 bg-brand text-white brutal-border flex items-center justify-center text-xl flex-shrink-0">📸</span>
                    <div class="min-w-0 flex-1">
                      <h3 class="text-base sm:text-lg font-black font-display text-ink truncate">${escapeHtml(row.title)}</h3>
                      <div class="text-[11px] sm:text-xs font-medium text-ink/70">
                        ${row.formatted_date} &bull; Oleh ${escapeHtml(row.user_name)}
                      </div>
                      ${locationRow}
                    </div>
                  </div>
                  <div class="flex items-center gap-1.5 self-start sm:self-center flex-shrink-0">
                    <button 
                      type="button" 
                      onclick='openEditTimelineModal(${payload})'
                      class="px-2.5 py-1 bg-accent hover:bg-white brutal-border text-xs font-bold transition-colors"
                      title="Edit Momen"
                    >
                      ✏️ Edit
                    </button>
                    <button 
                      type="button" 
                      onclick="confirmDelete('${row.destroy_url}', 'momen ${escapeHtml(row.title)}')"
                      class="px-2.5 py-1 bg-coral text-white hover:bg-ink brutal-border text-xs font-bold transition-colors"
                      title="Hapus Momen"
                    >
                      🗑️ Hapus
                    </button>
                  </div>
                </div>

                ${storyHtml}
                ${imageHtml}
              </div>
            `;
          }
        }
      ]
    });
  });

  // Inisialisasi Peta Utama (Semua Titik Jejak Keluarga)
  function initMainTimelineMap() {
    const defaultCenter = [-6.9175, 107.6191]; // Bandung / Jawa Barat
    mainTimelineMap = L.map('timeline-main-map', {
      scrollWheelZoom: false
    }).setView(defaultCenter, 9);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mainTimelineMap);

    loadMainMapPoints();
  }

  // Muat titik-titik koordinat dari database via AJAX
  function loadMainMapPoints() {
    if (!mainTimelineMap) return;

    $.ajax({
      url: "{{ route('timeline.map-points') }}",
      type: 'GET',
      dataType: 'json',
      success: function(res) {
        // Hapus marker lama
        mainMapMarkers.forEach(m => mainTimelineMap.removeLayer(m.marker));
        mainMapMarkers = [];

        const points = (res && res.data) ? res.data : [];
        const bounds = [];

        points.forEach(point => {
          if (point.latitude && point.longitude) {
            const lat = parseFloat(point.latitude);
            const lng = parseFloat(point.longitude);
            bounds.push([lat, lng]);

            const popupHtml = `
              <div class="p-1 space-y-1.5 max-w-[220px]">
                ${point.image_url ? `<img src="${point.image_url}" class="w-full h-24 object-cover brutal-border mb-1.5 cursor-pointer hover:opacity-90 transition-opacity" onclick="showImagePreview('${point.image_url}', '${escapeHtml(point.title)}')" title="Ketuk untuk perbesar">` : ''}
                <div class="font-black text-xs text-ink uppercase">${escapeHtml(point.title)}</div>
                ${point.location_name ? `<div class="text-[11px] font-bold text-coral flex items-center gap-1">📍 ${escapeHtml(point.location_name)}</div>` : ''}
                <div class="text-[10px] text-ink/70 font-mono">${point.formatted_date} &bull; ${escapeHtml(point.user_name)}</div>
                ${point.story ? `<p class="text-[11px] text-ink/90 font-medium italic line-clamp-2">"${escapeHtml(point.story)}"</p>` : ''}
              </div>
            `;

            const marker = L.marker([lat, lng], { icon: brutalMarkerIcon })
              .addTo(mainTimelineMap)
              .bindPopup(popupHtml);

            mainMapMarkers.push({
              id: point.id,
              lat: lat,
              lng: lng,
              marker: marker
            });
          }
        });

        // Update badge counter
        $('#map-counter-badge').text(`${bounds.length} Lokasi Tersemat`);

        // Fit map bounds jika ada titik
        if (bounds.length > 0) {
          mainTimelineMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
        }
      }
    });
  }

  // Pusatkan peta ke semua titik lokasi
  function fitTimelineMapBounds() {
    if (!mainTimelineMap || mainMapMarkers.length === 0) return;
    const bounds = mainMapMarkers.map(m => [m.lat, m.lng]);
    mainTimelineMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
  }

  // Fokuskan peta ke momen tertentu dan buka popup
  function focusOnMap(lat, lng, title, momentId) {
    if (!mainTimelineMap) return;

    // Scroll mulus ke elemen peta
    document.getElementById('timeline-main-map').scrollIntoView({ behavior: 'smooth', block: 'center' });

    mainTimelineMap.flyTo([lat, lng], 14, { duration: 1 });

    const targetMarker = mainMapMarkers.find(m => m.id === momentId);
    if (targetMarker) {
      setTimeout(() => {
        targetMarker.marker.openPopup();
      }, 1000);
    }
  }

  // Inisialisasi Peta Picker di Modal Tambah/Edit Momen
  function initPickerMap() {
    const defaultCenter = [-6.9175, 107.6191];
    pickerMap = L.map('timeline-picker-map', {
      scrollWheelZoom: true
    }).setView(defaultCenter, 10);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(pickerMap);

    // Event klik pada peta untuk menaruh atau memindahkan pin
    pickerMap.on('click', function(e) {
      setPickerCoords(e.latlng.lat, e.latlng.lng);
    });
  }

  // Atur koordinat pada picker
  function setPickerCoords(lat, lng) {
    const roundedLat = parseFloat(Number(lat).toFixed(6));
    const roundedLng = parseFloat(Number(lng).toFixed(6));

    $('#timeline-input-lat').val(roundedLat);
    $('#timeline-input-lng').val(roundedLng);
    $('#map-picker-coords-text').html(`📍 <strong>${roundedLat}, ${roundedLng}</strong>`);

    if (pickerMarker) {
      pickerMarker.setLatLng([roundedLat, roundedLng]);
    } else {
      pickerMarker = L.marker([roundedLat, roundedLng], {
        draggable: true,
        icon: pickerMarkerIcon
      }).addTo(pickerMap);

      pickerMarker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        setPickerCoords(pos.lat, pos.lng);
      });
    }

    if (pickerMap) {
      pickerMap.panTo([roundedLat, roundedLng]);
    }
  }

  // Tombol pintasan lokasi cepat
  function setPickerLocation(lat, lng, name) {
    setPickerCoords(lat, lng);
    const $loc = $('#timeline-input-location');
    if (!$loc.val()) {
      $loc.val(name);
    }
  }

  // Hapus pin titik lokasi
  function clearPickerMarker() {
    if (pickerMarker && pickerMap) {
      pickerMap.removeLayer(pickerMarker);
      pickerMarker = null;
    }
    $('#timeline-input-lat').val('');
    $('#timeline-input-lng').val('');
    $('#map-picker-coords-text').text('Belum ada titik dipilih');
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function openTimelineModal() {
    currentModalType = 'trip';
    $('#timeline-modal-form')[0].reset();
    $('#timeline-modal-method').val('POST');
    $('#timeline-modal-form').attr('action', "{{ route('timeline.store') }}");
    $('#timeline-modal-title').text('Abadikan Momen & Trip');
    $('#timeline-modal-icon').text('🗺️');
    $('#timeline-btn-submit').html('Simpan Kenangan &rarr;');
    if (timelineDatePicker) timelineDatePicker.setDate(new Date());

    selectComboboxOption('assignee', "{{ Auth::id() }}", "👨 {{ Auth::user()->name }}");
    selectComboboxOption('category', 'Wisata Alam', '🌲 Wisata Alam & Pantai');

    // Reset lokasi & Leaflet picker
    $('#timeline-input-location').val('');
    clearPickerMarker();

    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
    $('#timeline-modal').removeClass('hidden');

    // Refresh ukuran peta Leaflet setelah modal tampil
    setTimeout(() => {
      if (pickerMap) {
        pickerMap.invalidateSize();
        pickerMap.setView([-6.9175, 107.6191], 10);
      }
    }, 200);
  }

  function closeTimelineModal() {
    $('#timeline-modal').addClass('hidden');
    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
  }

  function openEditTimelineModal(moment) {
    currentModalType = 'trip';
    $('#timeline-modal-form')[0].reset();
    $('#timeline-modal-method').val('PUT');
    $('#timeline-modal-form').attr('action', moment.update_url);
    $('#timeline-modal-title').text('Edit Momen: ' + moment.title);
    $('#timeline-modal-icon').text('✏️');
    $('#timeline-btn-submit').html('Perbarui Kenangan &rarr;');

    $('#timeline-input-title').val(moment.title);
    $('#timeline-input-story').val(moment.story || '');
    $('#timeline-input-location').val(moment.location_name || '');

    if (moment.latitude && moment.longitude) {
      setPickerCoords(moment.latitude, moment.longitude);
    } else {
      clearPickerMarker();
    }

    if (moment.event_date && timelineDatePicker) {
      timelineDatePicker.setDate(moment.event_date);
    }

    selectComboboxOption('assignee', moment.user_id || "{{ Auth::id() }}", moment.user_name ? `👨 ${moment.user_name}` : "👨 {{ Auth::user()->name }}");
    selectComboboxOption('category', 'Wisata Alam', '🌲 Wisata Alam & Pantai');

    const fileInput = document.getElementById('minput-file');
    if (fileInput) fileInput.value = '';

    if (moment.image_url) {
      document.getElementById('upload-preview-img').src = moment.image_url;
      document.getElementById('upload-file-name').innerText = 'Foto Kenangan Tersimpan';
      document.getElementById('upload-file-size').innerText = 'Tersimpan';
      document.getElementById('upload-dropzone').classList.add('hidden');
      document.getElementById('upload-preview-card').classList.remove('hidden');
    } else {
      if (typeof removeUploadedFile === 'function') removeUploadedFile();
    }

    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
    $('#timeline-modal').removeClass('hidden');

    // Refresh ukuran peta Leaflet setelah modal tampil
    setTimeout(() => {
      if (pickerMap) {
        pickerMap.invalidateSize();
        if (moment.latitude && moment.longitude) {
          pickerMap.setView([moment.latitude, moment.longitude], 12);
        } else {
          pickerMap.setView([-6.9175, 107.6191], 10);
        }
      }
    }, 200);
  }

  // Handle Form Submit via AJAX
  $('#timeline-modal-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#timeline-btn-submit');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    const formData = new FormData($form[0]);
    $btn.prop('disabled', true).html('Menyimpan...');
    showLoading('Menyimpan momen liburan...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        closeTimelineModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Momen liburan berhasil disimpan!');
        if (timelineDataTable) {
          timelineDataTable.ajax.reload(null, false);
        }
        loadMainMapPoints();
      },
      error: function(xhr) {
        let msg = 'Gagal menyimpan momen.';
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
    if (type === 'trip') openTimelineModal();
    else if (window.openFinanceModal) window.openFinanceModal();
    else if (window.openChoreModal) window.openChoreModal();
    else if (window.openCalendarModal) window.openCalendarModal();
  };

  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_create') === '1') {
      setTimeout(openTimelineModal, 200);
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  });
</script>
@endpush
