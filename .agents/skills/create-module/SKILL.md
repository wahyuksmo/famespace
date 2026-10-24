---
name: create-module
description: "Panduan dan alur standar pembuatan modul baru di aplikasi FameSpace berbasis arsitektur modul Tugas & Chores (Chore). Gunakan skill ini setiap kali membuat modul fitur baru yang melibatkan Migration, Model, Repository, Service, Controller, Event Broadcast, Route, Blade View Neobrutalist, Table Server-Side (DataTables), komponen input form (Flatpickr, Combobox Searchable, Kamera/Upload), dan Feature Test."
---

# FameSpace Module Creation Skill (Based on Tugas & Chores)

Dokumen ini adalah standar resmi pembuatan modul baru di FameSpace dengan mengacu pada implementasi modul **Tugas & Chores (`app/Modules/Chore`)** dan **Pantry (`app/Modules/Pantry`)**.

---

## 1. Arsitektur & Prinsip Dasar

Setiap modul di FameSpace mengikuti pola **Controller -> Service -> Repository -> Model** dengan isolasi data tingkat keluarga (`family_id`).

| Komponen | Lokasi | Peran & Tanggung Jawab |
|---|---|---|
| **Model** | `app/Models/{Entity}.php` | Representasi tabel, relasi Eloquent (`belongsTo(Family::class)`), casts. |
| **Migration** | `database/migrations/*_create_t_{entities}_table.php` | Skema database dengan relasi `family_id` ke `m_families`. |
| **Repository** | `app/Modules/{Module}/Repositories/{Entity}Repository.php` | Query data spesifik keluarga (`family_id`), pagination, DataTables Server-Side. |
| **Service** | `app/Modules/{Module}/Services/{Entity}Service.php` | Logika bisnis, `DB::transaction`, upload file/lampiran, validasi aturan bisnis. |
| **Event** | `app/Events/{Entity}Event.php` | Realtime broadcasting via PrivateChannel `family.{familyId}`. |
| **Controller** | `app/Modules/{Module}/Controllers/{Entity}Controller.php` | Request handling, routing response (Blade / JSON), dispatch event realtime. |
| **Views** | `resources/views/modules/{module}/` | Tampilan neobrutalist (`index.blade.php`, `modal.blade.php`, partials). |
| **Routes** | `routes/web.php` | Rute grup auth middleware. |
| **Tests** | `tests/Feature/Family{Entity}Test.php` | Feature test dengan `DatabaseTransactions` menguji otorisasi multi-tenant keluarga. |

---

## 2. Standar Penamaan & Konvensi

1. **Tabel Database**:
   - Master data: prefix `m_` (contoh: `m_families`, `m_users`).
   - Transaksi / fitur: prefix `t_` (contoh: `t_chores`, `t_notes`, `t_pantry_items`).
   - Kolom tenant wajib: `foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete()`.
   - Pembuat record: `foreignId('created_by')->nullable()->constrained('m_users')->nullOnDelete()`.

2. **Direktori Modul**:
   - Diletakkan di `app/Modules/{ModuleName}/`.
   - Subfolder: `Controllers/`, `Repositories/`, `Services/`.

3. **Blade Views**:
   - Diletakkan di `resources/views/modules/{module_slug}/`.
   - Wajib mewarisi `@extends('layouts.main')`.
   - Mengikuti styling Neo-Brutalist: `brutal-card`, `brutal-border`, `brutal-btn`, warna `bg-ink`, `bg-brand`, `bg-accent`, `bg-coral`, `bg-cream`.

---

## 3. Detail DataTables Server-Side (Backend & Frontend)

### Backend: Repository Query

Metode `getDataTables*` di Repository wajib:
1. Hitung `recordsTotal` hanya dengan scope `family_id`.
2. Filter dinamis: status, kategori, atau parameter kustom lainnya.
3. Search term: memeriksa kolom judul, deskripsi, atau relasi relasional.
4. Hitung `recordsFiltered`.
5. Paginasi dengan `skip($start)->take($length)->get()`.

```php
public function getDataTablesItems(string $familyId, int $start = 0, int $length = 10, ?string $search = null, ?string $status = null): array
{
    $query = Item::with('assignee')->where('family_id', $familyId);
    $recordsTotal = (clone $query)->count();

    if (!empty($status) && in_array($status, ['pending', 'completed'])) {
        $query->where('status', $status);
    }

    if ($search !== null && trim($search) !== '') {
        $term = trim($search);
        $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhereHas('assignee', function ($sub) use ($term) {
                  $sub->where('name', 'like', "%{$term}%");
              });
        });
    }

    $recordsFiltered = (clone $query)->count();

    $data = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
        ->orderBy('due_date', 'asc')
        ->skip($start)
        ->take($length)
        ->get();

    return [
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data,
    ];
}
```

### Backend: Controller Endpoint Paginate

Controller membaca parameter DataTables dan mengembalikan JSON dengan format resmi DataTables:

```php
public function paginate(Request $request)
{
    $familyId = Auth::user()->family_id;

    $draw = (int) $request->input('draw', 1);
    $start = (int) $request->input('start', 0);
    $length = (int) $request->input('length', 10);
    $searchParam = $request->input('search');
    $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');
    $status = (string) $request->input('status', '');

    $dt = $this->itemRepo->getDataTablesItems($familyId, $start, $length, $search, $status);

    $formatted = $dt['data']->map(function ($item) {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'status' => $item->status,
            'due_date' => $item->due_date ? Carbon::parse($item->due_date)->locale('id')->isoFormat('D MMM Y') : null,
            'raw_due_date' => $item->due_date?->format('Y-m-d'),
            'attachment_url' => $item->attachment_path ? asset('storage/' . $item->attachment_path) : null,
            'update_url' => route('items.update', $item->id),
            'destroy_url' => route('items.destroy', $item->id),
        ];
    });

    return response()->json([
        'draw' => $draw,
        'recordsTotal' => $dt['recordsTotal'],
        'recordsFiltered' => $dt['recordsFiltered'],
        'data' => $formatted,
    ]);
}
```

### Frontend: Inisialisasi DataTables (Card List vs Tabel Baris)

Ada 2 tipe visual DataTables yang dipakai di FameSpace:
1. **Custom Card Renderer** (Contoh: [Chore](file:///d:/Samudrakreasi/famespace/app/Modules/Chore)): 1 kolom dengan `data: null` dan fungsi `render` mengembalikan kartu neobrutalist responsif.
2. **Multi-Column Table** (Contoh: [Pantry](file:///d:/Samudrakreasi/famespace/app/Modules/Pantry)): Kolom standar `th/td` neobrutalist.

Contoh Inisialisasi JS Card Renderer:
```javascript
let itemTable = $('#item-datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "{{ route('items.paginate') }}",
        type: 'GET',
        data: function(d) {
            d.status = currentStatusFilter;
        }
    },
    pageLength: 5,
    lengthMenu: [5, 10, 25],
    language: {
        search: "Cari:",
        lengthMenu: "Tampil _MENU_ data",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ data",
        emptyTable: "Belum ada data tersedia.",
        paginate: { previous: "&larr; Prev", next: "Next &rarr;" }
    },
    columns: [
        {
            data: null,
            orderable: false,
            render: function(data, type, row) {
                const payload = JSON.stringify(row).replace(/"/g, '&quot;');
                return `
                    <div class="p-3 bg-white brutal-border shadow-brutal-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-2">
                        <div>
                            <div class="font-black text-xs sm:text-sm text-ink">${escapeHtml(row.title)}</div>
                            <div class="text-[10px] text-ink/70">${row.description || '-'}</div>
                        </div>
                        <div class="flex items-center gap-1.5 self-end sm:self-center">
                            <button type="button" onclick='openEditModal(${payload})' class="px-2 py-0.5 bg-accent brutal-border text-[10px] font-bold">✏️ Edit</button>
                            <button type="button" onclick="confirmDelete('${row.destroy_url}', '${escapeHtml(row.title)}')" class="px-2 py-0.5 bg-coral text-white brutal-border text-[10px] font-bold">🗑️</button>
                        </div>
                    </div>
                `;
            }
        }
    ]
});

function reloadTable() {
    itemTable.ajax.reload(null, false);
}
```

---

## 4. Detail Komponen Input Form (Neobrutalist)

Setiap input di FameSpace mengikuti styling chunky neobrutalist:
- Base input style: `px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white`
- Label style: `block text-xs font-black font-mono uppercase text-ink mb-1`

### A. Input Teks & Textarea
```html
<div>
  <label class="block text-xs font-black font-mono uppercase text-ink mb-1">Judul:</label>
  <input type="text" name="title" id="item-title" required class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white" placeholder="Masukkan judul...">
</div>

<div>
  <label class="block text-xs font-black font-mono uppercase text-ink mb-1">Keterangan (Opsional):</label>
  <textarea name="description" id="item-desc" rows="2" class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink outline-none focus:bg-white" placeholder="Catatan tambahan..."></textarea>
</div>
```

### B. Input Angka (Poin / Jumlah Stok / Budget)
```html
<div>
  <label class="block text-xs font-black font-mono uppercase text-ink mb-1">Jumlah Poin:</label>
  <input type="number" name="points" id="item-points" value="10" min="1" max="500" class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold font-mono text-ink outline-none focus:bg-white">
</div>
```

### C. Input Tanggal (Flatpickr Kalender Neobrutalist)
Gunakan plugin Flatpickr bawaan `layouts.main` dengan format tanggal Indonesia:
```html
<div>
  <label class="block text-xs font-black font-mono uppercase text-ink mb-1">Batas Waktu (Due Date):</label>
  <div class="relative">
    <input type="text" name="due_date" id="item-due-date" readonly placeholder="Pilih tanggal..." class="w-full px-3 py-2 pr-9 bg-cream brutal-border text-xs font-bold font-mono text-ink outline-none cursor-pointer focus:bg-white">
    <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-xs">📅</span>
  </div>
</div>
```
Inisialisasi JS:
```javascript
let datePicker = flatpickr("#item-due-date", {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "j F Y",
    locale: "id"
});
```

### D. Searchable Dropdown Combobox (Kategori / Anggota)
Combobox khusus FameSpace dengan filter live search di popover:
```html
<div class="relative" id="combobox-category-root">
  <label class="block text-xs font-black font-mono uppercase text-ink mb-1">Kategori:</label>
  <input type="hidden" id="minput-category" name="category" value="Dapur">
  <button type="button" id="btn-category" onclick="toggleCombobox('category')" class="w-full px-3 py-2 bg-cream brutal-border text-xs font-bold text-ink flex items-center justify-between text-left hover:bg-white focus:bg-white">
    <span id="label-category" class="truncate">🍳 Dapur</span>
    <span class="text-[10px] ml-1.5 text-ink/70">▼</span>
  </button>
  <div id="popover-category" class="hidden absolute left-0 right-0 top-full mt-1 bg-cream brutal-border shadow-brutal z-50 p-2 space-y-1.5">
    <input type="text" placeholder="Ketik cari..." oninput="filterCombobox('category', this.value)" class="w-full px-2.5 py-1.5 bg-white brutal-border text-xs font-bold text-ink outline-none">
    <div id="list-category" class="max-h-36 overflow-y-auto space-y-1"></div>
  </div>
</div>
```

### E. Input Lampiran & Kamera Langsung (Dual-Source Mobile Camera)
Mendukung upload dari galeri atau kamera langsung perangkat mobile:
```html
<div>
  <label class="block text-xs font-black font-mono uppercase text-ink mb-1">Foto Bukti / Lampiran:</label>
  <!-- Galeri / File picker -->
  <input type="file" id="item-file-gallery" name="attachment" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
  <!-- Kamera langsung Mobile PWA -->
  <input type="file" id="item-file-camera" accept="image/*" capture="environment" class="hidden" onchange="handleFileSelect(event)">
  <input type="hidden" name="remove_attachment" id="item-remove-attachment" value="0">

  <div id="upload-dropzone" class="border-2 border-dashed border-ink bg-cream/70 p-3 sm:p-4 text-center">
    <div class="flex items-center justify-center gap-2 mb-2">
      <button type="button" onclick="document.getElementById('item-file-camera').click()" class="px-3 py-1.5 bg-accent text-ink brutal-border text-xs font-black shadow-brutal-sm">
        📸 Kamera
      </button>
      <button type="button" onclick="document.getElementById('item-file-gallery').click()" class="px-3 py-1.5 bg-white text-ink brutal-border text-xs font-black shadow-brutal-sm">
        🖼️ Galeri
      </button>
    </div>
    <p class="text-[10px] text-ink/60 font-mono">Format: JPG, PNG, WebP (Maks 5MB)</p>
  </div>

  <!-- Preview Container -->
  <div id="attachment-preview" class="hidden mt-2 p-2 bg-white brutal-border flex items-center justify-between">
    <img id="preview-img" class="w-12 h-12 object-cover brutal-border">
    <button type="button" onclick="removeAttachment()" class="text-xs text-coral font-bold hover:underline">Hapus Foto</button>
  </div>
</div>
```

### F. AJAX Form Submission
Form di-submit melalui AJAX `FormData` agar tabel server-side dapat direload instan tanpa me-refresh halaman:

```javascript
$('#item-form').on('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');

    submitBtn.prop('disabled', true).addClass('opacity-50');

    $.ajax({
        url: form.action,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(res) {
            closeModal();
            itemTable.ajax.reload(null, false);
            // Tampilkan toast berhasil
        },
        error: function(err) {
            const msg = err.responseJSON?.message || 'Terjadi kesalahan pada data input.';
            alert(msg);
        },
        complete: function() {
            submitBtn.prop('disabled', false).removeClass('opacity-50');
        }
    });
});
```

---

## 5. Ringkasan Checklist Modul Baru

1. [ ] **Migration & Model**: Tabel `t_<entities>`, UUID `family_id` foreign key ke `m_families`.
2. [ ] **Repository**: Method `getDataTables*` dengan filter `family_id`, search term, dan pagination slice.
3. [ ] **Service**: Transaksi DB `create*`, `update*`, `delete*`, serta upload file storage.
4. [ ] **Event Broadcast**: `ShouldBroadcastNow` ke private channel `family.{familyId}`.
5. [ ] **Controller**: Action `index`, `paginate` (JSON format DataTables), `store`, `update`, `destroy`.
6. [ ] **Routes**: Rute didaftarkan di `routes/web.php` dalam grup middleware `auth`.
7. [ ] **View & Modal**: UI Neobrutalist, DataTables server-side, Flatpickr, Combobox, dual-source camera input.
8. [ ] **Sidebar & Bottom Nav**: Link menu dengan status active route dan counter badge.
9. [ ] **Feature Test**: Menjamin multi-tenant keluarga tidak tembus antar family_id.
10. [ ] **Pint Formatter**: Jalankan `vendor/bin/pint --dirty --format agent`.
