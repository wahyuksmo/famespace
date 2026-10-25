# Implementation Plan: Super Administrator & QRIS Static Payment System

Dokumen perencanaan teknis pembuatan portal **Super Administrator**, integrasi **QRIS Statis** untuk pembayaran Pro & Lifetime, serta sistem audit transaksi & revert otomatis ke free tier jika terjadi manipulasi data pembayaran.

---

## 1. Ringkasan Kebutuhan & Alur Bisnis

1. **Autentikasi Super Administrator**:
   - Akun Super Admin: `wahyuganteng@admin.com`
   - Password: `@Admin1234!`
   - Role: `super_admin` (tanpa keterikatan `family_id`).
   - Akses rute khusus: `/admin/*` terlindungi middleware `EnsureSuperAdmin`.

2. **Flow Pembayaran QRIS Statis (Optimistic Pro Activation)**:
   - Pengguna memilih paket (Pro Bulanan / Lifetime).
   - Tampilkan QRIS Statis FamSpace beserta total tagihan dan instruksi transfer.
   - Pengguna klik konfirmasi pembayaran (opsional: input jam transfer & unggah struk mutasi).
   - Sistem langsung mengaktifkan status **PRO / Lifetime** untuk keluarga tersebut seketika (*optimistic instant upgrade*).

3. **Verifikasi & Revert di Portal Super Admin**:
   - Super Admin membuka menu **Daftar Transaksi Pembayaran**.
   - Admin mencocokkan jam transaksi & nominal dengan mutasi rekening bank/QRIS manual.
   - **Jika Valid**: Klik tombol **Konfirmasi / Verified**.
   - **Jika Tidak Masuk / Fiktif**: Klik tombol **Revert ke Free Tier**. Sistem otomatis menurunkan status keluarga kembali ke `free` tier via `revertToFree()`, membatalkan invoice (`reverted`), dan broadcast event pembaruan tier.

4. **Fitur Ekstra Super Admin**:
   - **Dashboard**: Statistik total anggota, total keluarga, rasio Free vs Pro/Lifetime, estimasi omzet.
   - **Manajemen Produk (Plans)**: Konfigurasi harga, durasi, dan fitur paket `m_plans`.
   - **Manajemen Anggota & Keluarga**: List seluruh user & keluarga terdaftar beserta kontrol manual subscription.

---

## 2. Struktur File & Modul

```text
app/
├── Http/
│   └── Middleware/
│       └── EnsureSuperAdmin.php          # [BARU] Proteksi route grup /admin
├── Models/
│   ├── Plan.php                         # [EXISTING] Master paket
│   ├── Subscription.php                 # [EXISTING] Relasi langganan keluarga
│   ├── SubscriptionPayment.php          # [MODIFIKASI] Tambah field bukti, jam bayar, status rejected/reverted
│   └── User.php                         # [MODIFIKASI] Helper isSuperAdmin()
├── Modules/
│   ├── Admin/                           # [BARU] Modul Super Administrator
│   │   ├── Controllers/
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── AdminPaymentController.php
│   │   │   ├── AdminPlanController.php
│   │   │   └── AdminUserController.php
│   │   ├── Repositories/
│   │   │   ├── AdminDashboardRepository.php
│   │   │   └── AdminPaymentRepository.php
│   │   └── Services/
│   │       ├── AdminPaymentService.php
│   │       └── AdminPlanService.php
│   ├── Auth/
│   │   └── Controllers/
│   │       └── AuthController.php       # [MODIFIKASI] Redirect super admin bypass family_id check
│   └── Subscription/
│       ├── Controllers/
│       │   └── SubscriptionController.php # [MODIFIKASI] Handle konfirmasi QRIS statis
│       └── Services/
│           └── SubscriptionService.php  # [MODIFIKASI] Catat info QRIS statis & optimistik pro
database/
├── migrations/
│   └── 2026_10_25_000001_add_admin_and_qris_fields_to_payments_table.php # [BARU]
└── seeders/
    └── SuperAdminSeeder.php             # [BARU] Seeder akun wahyuganteng@admin.com
resources/
├── views/
│   ├── layouts/
│   │   └── admin.blade.php              # [BARU] Master Layout Neobrutalist Super Admin
│   ├── modules/
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php      # [BARU] Ringkasan metrik & analytics
│   │   │   ├── payments/
│   │   │   │   └── index.blade.php      # [BARU] List transaksi, filter status, aksi verif & revert
│   │   │   ├── plans/
│   │   │   │   └── index.blade.php      # [BARU] List & form edit harga paket
│   │   │   └── users/
│   │   │       └── index.blade.php      # [BARU] List user, keluarga & status tier
│   │   └── subscription/
│   │       └── modal.blade.php          # [MODIFIKASI] Tampilan QRIS statis & konfirmasi bayar
routes/
└── web.php                              # [MODIFIKASI] Daftarkan route group /admin
```

---

## 3. Tahapan Rencana Kerja (Action Plan)

### Fase 1: Fondasi Database & Autentikasi Super Admin
1. **Migration**:
   - Tambah kolom di `t_subscription_payments`:
     - `proof_image` (string, nullable)
     - `transfer_time` (string, nullable)
     - `admin_notes` (text, nullable)
     - `verified_at` (timestamp, nullable)
     - `verified_by` (unsignedBigInteger, nullable)
2. **Seeder Super Admin**:
   - Buat `SuperAdminSeeder` membuat user `wahyuganteng@admin.com` dengan password `@Admin1234!` dan `role = 'super_admin'`.
3. **Bypass Login Check & Middleware**:
   - Update `AuthController@login`: jika user punya `role === 'super_admin'`, lewati validasi `family_id` dan arahkan langsung ke `route('admin.dashboard')`.
   - Buat middleware `EnsureSuperAdmin` untuk melindungi seluruh rute `/admin/*`.

### Fase 2: Front-Facing QRIS Statis & Optimistic Instant Pro
1. **Pembaruan Modal Langganan (`modal.blade.php`)**:
   - Tampilkan kartu QRIS Statis resmi FamSpace.
   - Field opsional: Jam transfer (misal: "14:35 WIB") & unggah bukti transfer.
   - Tombol "Konfirmasi & Aktifkan Paket".
2. **Optimistic Upgrade**:
   - Controller memproses pembuatan invoice bertipe QRIS Statis, langsung mengaktifkan langganan keluarga via `SubscriptionRepository::activatePro`.
   - Anggota keluarga langsung menikmati fitur Pro tanpa menunggu proses verifikasi admin.

### Fase 3: Back-Office Super Admin Portal
1. **Layout Admin Neobrutalist (`layouts/admin.blade.php`)**:
   - Desain terpadu bernuansa neobrutalisme FamSpace dengan sidebar admin terpisah, badge `SUPER ADMIN`, quick switcher, dan profil admin.
2. **Dashboard Analytics (`AdminDashboardController`)**:
   - Total anggota & keluarga terdaftar.
   - Rasio paket aktif (Free vs Pro Bulanan vs Lifetime).
   - Total nominal pembayaran terverifikasi vs pending.
3. **Audit Transaksi & Revert Engine (`AdminPaymentController`)**:
   - List transaksi dengan DataTables Server-Side.
   - Modal detail bukti pembayaran & jam transaksi.
   - Tombol **Verifikasi**: tandai status invoice menjadi `verified`.
   - Tombol **Revert ke Free**:
     - Panggil `SubscriptionRepository::revertToFree($familyId)`.
     - Update invoice status menjadi `reverted`.
     - Broadcast `SubscriptionEvent` untuk notifikasi realtime jika keluarga sedang membuka aplikasi.
4. **Manajemen Produk (`AdminPlanController`)**:
   - Melihat dan memperbarui harga `price` serta status aktif `is_active` paket Free, Pro Bulanan, dan Lifetime.
5. **Manajemen Anggota & Keluarga (`AdminUserController`)**:
   - DataTables daftar semua keluarga & user.
   - Kemampuan override paket keluarga secara manual (Emergency Override).

### Fase 4: Testing & Verifikasi
1. **Feature Test `SuperAdminAuthTest`**:
   - Uji login `wahyuganteng@admin.com` sukses dan diarahkan ke `/admin/dashboard`.
   - Uji pencegahan user biasa mengakses rute `/admin/*`.
2. **Feature Test `AdminPaymentRevertTest`**:
   - Uji checkout QRIS statis -> langganan keluarga menjadi Pro.
   - Uji Super Admin mengeksekusi aksi revert -> langganan keluarga kembali menjadi Free tier.

---

## 4. Keamanan & Edge Cases

| Skenario | Solusi |
|---|---|
| User biasa mencoba akses URL `/admin` | Diblokir oleh middleware `EnsureSuperAdmin` dengan error 403 / redirect login |
| User mengklaim bayar fiktif berulang kali | Super Admin memiliki log riwayat invoice dan tombol instant Revert |
| Revert saat user sedang membuka aplikasi | Broadcast event WebSocket `SubscriptionEvent` memperbarui state antarmuka user ke Free |
| Admin salah tekan revert | Tersedia modal konfirmasi dengan input alasan penolakan |
