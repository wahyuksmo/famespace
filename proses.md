# Dokumentasi Proses Bisnis & Alur Operasional: FameSpace

Dokumen ini mendefinisikan seluruh alur operasional (*business flows*), *user journey*, mekanisme state machine, serta SOP sistem pada aplikasi FameSpace.

---

## 1. Alur 1: Pendaftaran & Onboarding Ruang Keluarga (Wizard 3 Langkah)

Alur registrasi dirancang memandu pengguna baru membangun fondasi ruang keluarga secara bertahap tanpa kebingungan.

```mermaid
sequenceDiagram
    autonumber
    actor User as Calon Pengguna
    participant Web as Browser (Register Page)
    participant RegCtrl as RegisterController
    participant DB as PostgreSQL (m_families, m_users, m_plans, t_subscriptions)

    User->>Web: Akses /register
    Note over Web: Langkah 1: Profil Ruang Keluarga
    User->>Web: Isi Nama Keluarga & Target Anggaran Bulanan
    Web->>Web: Validasi client-side & Next ke Langkah 2

    Note over Web: Langkah 2: Akun Admin Utama
    User->>Web: Isi Nama, Email, Password, & Pilih Peran (Ayah/Ibu)
    Web->>Web: Validasi input akun & Next ke Langkah 3

    Note over Web: Langkah 3: Konfirmasi & Undangan
    User->>Web: Klik "Selesaikan & Buka Ruang Keluarga"
    Web->>RegCtrl: POST /register (payload step 1 & 2)

    activate RegCtrl
    RegCtrl->>RegCtrl: Generate Invite Code acak (FAM-XXXX)
    RegCtrl->>DB: INSERT INTO m_families
    RegCtrl->>DB: INSERT INTO m_users (Role Admin/Kepala)
    RegCtrl->>DB: INSERT INTO t_subscriptions (Plan: Free)
    RegCtrl->>RegCtrl: Auth::login(user)
    RegCtrl-->>Web: Redirect ke /dashboard dengan status sukses
    deactivate RegCtrl

    Web-->>User: Tampilkan Dashboard Keluarga Pertama Kali
```

### Detail Langkah Onboarding:
1. **Langkah 1 (Keluarga)**:
   - Input: `family_name` (wajib, max 100 karakter).
   - Input: `budget_monthly` (opsional, format mata uang rupiah).
   - Input: `currency` (default: IDR).
2. **Langkah 2 (Admin)**:
   - Input: `role` (radio button: Ayah, Ibu, Anak, dll).
   - Input: `name`, `email` (harus unik di `m_users`), `password` (min 8 karakter).
3. **Langkah 3 (Undangan)**:
   - Sistem menerbitkan `invite_code` unik 6 digit (contoh: `FAM-7729`).
   - Tautan pendaftaran anggota instan: `https://famespace.id/join/FAM-7729`.

---

## 2. Alur 2: Autentikasi & Login Cepat (Demo 1-Klik)

FameSpace menyediakan form login standar email/password serta shortcut simulasi 1-klik untuk pengujian multi-peran (Ayah, Ibu, Anak) secara instan.

```mermaid
flowchart TD
    Start["User Membuka /login"] --> Choice{"Pilih Metode Masuk"}
    
    Choice -- "Login Manual" --> Form["Input Email & Password"]
    Form --> Submit["POST /login"]
    Submit --> Verify{"Verifikasi Kredensial DB"}
    Verify -- "Cocok" --> Session["Buat Sesi & Set Cookie 'Ingat Saya'"]
    Verify -- "Gagal" --> ErrorMsg["Kembalikan Error ke Form"]
    
    Choice -- "Demo 1-Klik (Ayah / Ibu / Anak)" --> DemoClick["Klik Tombol Avatar Demo"]
    DemoClick --> DemoReq["POST /demo-login (role_target)"]
    DemoReq --> FetchDemo["Ambil User Demo Berdasarkan Email Preset"]
    FetchDemo --> Session
    
    Session --> RedirectDash["Redirect ke /dashboard"]
```

---

## 3. Alur 3: Manajemen Tugas Harian & Gamifikasi Poin (Chores)

Membagi tanggung jawab rumah tangga dan memberikan insentif psikologis berupa reward poin untuk setiap tugas yang diselesaikan anggota.

```mermaid
sequenceDiagram
    autonumber
    actor Anggota as Anggota Keluarga (e.g. Anak)
    actor Admin as Orang Tua (Ayah / Ibu)
    participant Ctrl as ChoreController
    participant DB as PostgreSQL (t_chores, m_users)

    Admin->>Ctrl: POST /chores (Title: "Cuci Piring", Assignee: Budi, Points: 20, DueDate)
    Ctrl->>DB: INSERT INTO t_chores (status: 'pending')
    Ctrl-->>Admin: Tugas Muncul di Tab Chores & Overview

    Anggota->>Ctrl: PATCH /chores/{id}/toggle
    activate Ctrl
    Ctrl->>DB: SELECT * FROM t_chores WHERE id = {id}
    alt Tugas berubah dari pending -> completed
        Ctrl->>DB: UPDATE t_chores SET status = 'completed', completed_at = NOW()
        Ctrl->>DB: UPDATE m_users SET points = points + 20 WHERE id = assigned_to_user_id
    else Tugas di-uncheck (kembali pending)
        Ctrl->>DB: UPDATE t_chores SET status = 'pending', completed_at = NULL
        Ctrl->>DB: UPDATE m_users SET points = points - 20 WHERE id = assigned_to_user_id
    end
    Ctrl-->>Anggota: JSON / Redirect respons update skor
    deactivate Ctrl
```

---

## 4. Alur 4: Pengelolaan Arus Kas Keluarga & Bukti Struk (Finance)

Memastikan seluruh pengeluaran dan pemasukan tercatat rapi secara kolektif dengan kalkulasi saldo otomatis.

1. **Pencatatan**:
   - Anggota membuka form modal catat kas.
   - Mengisi jenis (`pemasukan` atau `pengeluaran`), nominal (Rp), kategori (dari `m_categories`), tanggal, dan deskripsi.
   - Opsional: Mengunggah foto struk belanja / bukti transfer (`.jpg`, `.png`, max 2MB).
2. **Penyimpanan**:
   - File struk divalidasi dan disimpan di storage disk lokal (`storage/app/public/receipts/`).
   - Data transaksi dicatat ke tabel `t_finances`.
3. **Agregasi Dashboard**:
   - Total Pemasukan = `SUM(amount)` where `type = 'income'`.
   - Total Pengeluaran = `SUM(amount)` where `type = 'expense'`.
   - Saldo Kas Berjalan = `Total Pemasukan - Total Pengeluaran`.
   - Indikator Target Bulanan: Membandingkan total pengeluaran terhadap `monthly_budget` pada tabel `m_families`.

---

## 5. Alur 5: Siklus Langganan Pro, Invoice, & Penanganan Expired (Hybrid)

Menjelaskan perjalanan transaksi upgrade Pro (Rp 20.000/bulan) per keluarga, pembuatan nomor invoice unik, aktivasi masa berlaku, dan pencegahan akses saat expired.

```mermaid
stateDiagram-v2
    [*] --> FreePlan : Registrasi Baru
    FreePlan --> PendingPayment : Klik "Upgrade Pro" (Pilih Paket)
    
    state PendingPayment {
        [*] --> GenerateInvoice : Terbitkan INV-YYYYMMDD-XXXX
        GenerateInvoice --> AwaitingSimulation : Menunggu Pembayaran (Status: PENDING)
    }

    AwaitingSimulation --> ActivePro : Konfirmasi Pembayaran (Status: PAID)
    AwaitingSimulation --> ExpiredInvoice : Lewat 24 Jam Tanpa Bayar (Status: EXPIRED)
    
    state ActivePro {
        [*] --> InGracePeriod : Masa Aktif 30 Hari
        InGracePeriod --> ExpiredCheck : Setiap Request & Cron Harian
    }

    ActivePro --> FreePlan : Masa Berlaku Habis (Status: EXPIRED)
    ExpiredInvoice --> [*]
```

### 5.1 Alur Penerbitan Invoice & Pembayaran
1. Pengguna (Ayah/Ibu) mengklik tombol *"Upgrade ke Paket Pro"* dari Paywall Modal.
2. Endpoint `POST /subscriptions/checkout`:
   - Membuat rekaman baru di `t_subscription_payments` dengan status `pending`.
   - Menghasilkan nomor invoice berformat `INV-{YYYYMMDD}-{URUTAN}`.
   - Menghasilkan rincian pembayaran: Total Rp 20.000.
3. Simulasi Pembayaran Instan / Konfirmasi:
   - Endpoint `POST /subscriptions/simulate-pay/{invoice_number}`.
   - Mengubah status pembayaran menjadi `paid` dan mencatat `paid_at = now()`.
   - Mengupdate atau membuat record di `t_subscriptions`:
     - `status = 'active'`
     - `starts_at = now()`
     - `expires_at = (existing_expires_at > now() ? existing_expires_at : now()) + 30 days`
   - Mengirim notifikasi / toast sukses ke tampilan browser.

### 5.2 Mekanisme Deteksi & Penguncian Expired (Hybrid)
Sistem menerapkan perlindungan ganda (real-time dan batch cron):

1. **Gatekeeper Middleware (Real-time)**:
   - Diterapkan pada route fitur Pro (`/timeline/*`, `/events/*`, dan API terkait).
   - Middleware `EnsureProPlan` memeriksa:
     ```php
     $subscription = $family->activeSubscription;
     if (!$subscription || now()->greaterThan($subscription->expires_at)) {
         return redirect()->route('dashboard', ['tab' => 'overview', 'paywall' => 'true']);
     }
     ```
   - Di tampilan UI Dashboard, jika keluarga dalam status `Free` atau `Expired`:
     - Tombol tab Kalender dan Linimasa menampilkan badge `PRO 🔒`.
     - Ketika diklik, modal paywall otomatis terbuka menampilkan rincian paket Rp 20.000/bln.
2. **Command Otomatis Terjadwal (Batch Cron)**:
   - Command Artisan: `php artisan subscription:check-expired`.
   - Dijalankan setiap hari pukul 00:01 WIB via Laravel Scheduler.
   - Logika:
     ```sql
     UPDATE t_subscriptions 
     SET status = 'expired', updated_at = NOW() 
     WHERE status = 'active' AND expires_at < NOW();
     ```
   - Invoice pending yang tidak diselesaikan dalam 24 jam ditandai sebagai `expired`.

---

## 6. Alur 6: Kalender Bersama & Linimasa Momen (Fitur Pro)

### 6.1 Agenda & Kalender Keluarga (`t_events`)
- Integrasi library Flatpickr (Brutalist style) untuk pemilihan tanggal & rentang jam.
- Pengguna mencatat agenda: Ulang tahun, janji temu dokter, jadwal imunisasi anak, liburan.
- Agenda ditampilkan dalam kalender visual dan rekap list 7 hari ke depan pada tab Overview.

### 6.2 Linimasa & Rekap Jejak Liburan (`t_timeline_moments`)
- Mengunggah foto dokumentasi kegiatan keluarga (file path disimpan di storage publik).
- Menambahkan judul momen, tanggal kejadian, dan cerita ringkas.
- Disajikan dalam format feed polaroid Neobrutalism berurutan dari tanggal terbaru ke terlama.
