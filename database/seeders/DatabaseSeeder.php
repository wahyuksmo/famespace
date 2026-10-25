<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Chore;
use App\Models\Event;
use App\Models\Family;
use App\Models\Finance;
use App\Models\FinanceGoal;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\TimelineMoment;
use App\Models\User;
use App\Modules\Calendar\Services\WkcalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Master Plans
        $freePlan = Plan::firstOrCreate(
            ['code' => 'free'],
            [
                'name' => 'Famespace Free',
                'price' => 0.00,
                'duration_days' => 3650,
                'features' => ['overview', 'chores', 'finance', 'members'],
                'is_active' => true,
            ]
        );

        $proPlan = Plan::firstOrCreate(
            ['code' => 'pro_monthly'],
            [
                'name' => 'Famespace Pro Bulanan',
                'price' => 20000.00,
                'duration_days' => 30,
                'features' => ['overview', 'chores', 'finance', 'members', 'timeline', 'calendar', 'unlimited_members'],
                'is_active' => true,
            ]
        );

        $lifetimePlan = Plan::firstOrCreate(
            ['code' => 'lifetime'],
            [
                'name' => 'Famespace Pro Lifetime',
                'price' => 150000.00,
                'duration_days' => 36500,
                'features' => ['overview', 'chores', 'finance', 'members', 'timeline', 'calendar', 'unlimited_members'],
                'is_active' => true,
            ]
        );

        // 2. Ruang Keluarga Demo
        $family = Family::create([
            'name' => 'Keluarga Santoso Pratama',
            'invite_code' => 'FAM-7729',
            'monthly_budget' => 15000000.00,
            'currency' => 'IDR',
        ]);

        // 3. Status Langganan Pro Aktif
        $sub = Subscription::create([
            'family_id' => $family->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addYear(),
            'auto_renew' => true,
        ]);

        // 4. Pengguna Demo (Ayah, Ibu, Anak 1, Anak 2)
        $ayah = User::create([
            'family_id' => $family->id,
            'name' => 'Ayah Dimas',
            'email' => 'ayah@keluarga.id',
            'password' => Hash::make('rahasia123'),
            'role' => 'Ayah',
            'points' => 125,
            'avatar_url' => null,
        ]);

        $ibu = User::create([
            'family_id' => $family->id,
            'name' => 'Ibu Maya',
            'email' => 'ibu@keluarga.id',
            'password' => Hash::make('rahasia123'),
            'role' => 'Ibu',
            'points' => 160,
            'avatar_url' => null,
        ]);

        $budi = User::create([
            'family_id' => $family->id,
            'name' => 'Budi Pratama',
            'email' => 'budi@keluarga.id',
            'password' => Hash::make('rahasia123'),
            'role' => 'Anak',
            'points' => 240,
            'avatar_url' => null,
        ]);

        $sinta = User::create([
            'family_id' => $family->id,
            'name' => 'Sinta Pratama',
            'email' => 'sinta@keluarga.id',
            'password' => Hash::make('rahasia123'),
            'role' => 'Anak',
            'points' => 180,
            'avatar_url' => null,
        ]);

        // 5. Catatan Pembayaran Langganan Pro
        SubscriptionPayment::create([
            'invoice_number' => 'INV-FAM-'.now()->format('Ym').'-001',
            'subscription_id' => $sub->id,
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'plan_id' => $proPlan->id,
            'amount' => 20000.00,
            'payment_method' => 'QRIS GoPay',
            'status' => 'paid',
            'paid_at' => now()->subDays(10),
            'reference_code' => 'QRIS-PRO-'.strtoupper(Str::random(8)),
        ]);

        // 6. Kategori Kas
        $catGaji = Category::create([
            'family_id' => $family->id,
            'name' => 'Gaji & Pendapatan Tetap',
            'type' => 'income',
            'icon' => '💰',
            'color' => '#2F5233',
        ]);

        $catUsaha = Category::create([
            'family_id' => $family->id,
            'name' => 'Usaha Toko Kue Ibu',
            'type' => 'income',
            'icon' => '🛍️',
            'color' => '#F2C94C',
        ]);

        $catBelanja = Category::create([
            'family_id' => $family->id,
            'name' => 'Belanja Dapur & Pokok',
            'type' => 'expense',
            'icon' => '🛒',
            'color' => '#E07A5F',
            'monthly_budget' => 3500000.00,
        ]);

        $catTagihan = Category::create([
            'family_id' => $family->id,
            'name' => 'Listrik, Air & WiFi',
            'type' => 'expense',
            'icon' => '⚡',
            'color' => '#F2C94C',
            'monthly_budget' => 1500000.00,
        ]);

        $catPendidikan = Category::create([
            'family_id' => $family->id,
            'name' => 'Pendidikan & Les Anak',
            'type' => 'expense',
            'icon' => '🎓',
            'color' => '#81B0C4',
            'monthly_budget' => 2500000.00,
        ]);

        $catKesehatan = Category::create([
            'family_id' => $family->id,
            'name' => 'Kesehatan & Apotek',
            'type' => 'expense',
            'icon' => '💊',
            'color' => '#E07A5F',
            'monthly_budget' => 1000000.00,
        ]);

        $catBBM = Category::create([
            'family_id' => $family->id,
            'name' => 'BBM & Servis Mobil',
            'type' => 'expense',
            'icon' => '🚗',
            'color' => '#121614',
            'monthly_budget' => 1200000.00,
        ]);

        $catKuliner = Category::create([
            'family_id' => $family->id,
            'name' => 'Kuliner & Liburan',
            'type' => 'expense',
            'icon' => '🎬',
            'color' => '#2F5233',
            'monthly_budget' => 1500000.00,
        ]);

        // Goals Celengan Bersama
        FinanceGoal::create([
            'family_id' => $family->id,
            'title' => 'Liburan & Rekreasi Keluarga',
            'target_amount' => 12000000.00,
            'current_amount' => 4500000.00,
            'icon' => '🏖️',
            'color' => '#E07A5F',
            'note' => 'Target tabungan liburan akhir tahun',
            'target_date' => now()->addMonths(6),
            'status' => 'active',
        ]);

        FinanceGoal::create([
            'family_id' => $family->id,
            'title' => 'Dana Darurat Rumah Tangga',
            'target_amount' => 30000000.00,
            'current_amount' => 15000000.00,
            'icon' => '🛡️',
            'color' => '#2F5233',
            'note' => 'Alokasi cadangan 3 bulan operasional',
            'target_date' => now()->addMonths(12),
            'status' => 'active',
        ]);

        FinanceGoal::create([
            'family_id' => $family->id,
            'title' => 'Target Impian & Tabungan Bersama',
            'target_amount' => 8000000.00,
            'current_amount' => 2000000.00,
            'icon' => '🎯',
            'color' => '#81B0C4',
            'note' => 'Tabungan renovasi & alat dapur baru',
            'target_date' => now()->addMonths(4),
            'status' => 'active',
        ]);

        // 7. Transaksi Keuangan
        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'category_id' => $catGaji->id,
            'type' => 'income',
            'amount' => 17500000.00,
            'description' => 'Gaji Pokok & Tunjangan Ayah Dimas',
            'transaction_date' => now()->startOfMonth(),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ibu->id,
            'category_id' => $catUsaha->id,
            'type' => 'income',
            'amount' => 4500000.00,
            'description' => 'Pendapatan Pesanan Bolu & Catering Ibu Maya',
            'transaction_date' => now()->subDays(6),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ibu->id,
            'category_id' => $catBelanja->id,
            'type' => 'expense',
            'amount' => 1450000.00,
            'description' => 'Belanja Bahan Dapur & Daging Supermarket',
            'transaction_date' => now()->subDays(7),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'category_id' => $catTagihan->id,
            'type' => 'expense',
            'amount' => 520000.00,
            'description' => 'Token Listrik PLN 3500VA',
            'transaction_date' => now()->subDays(5),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'category_id' => $catTagihan->id,
            'type' => 'expense',
            'amount' => 450000.00,
            'description' => 'Langganan WiFi Fiber 100Mbps',
            'transaction_date' => now()->subDays(4),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ibu->id,
            'category_id' => $catPendidikan->id,
            'type' => 'expense',
            'amount' => 1250000.00,
            'description' => 'SPP & Kursus Robotik Budi',
            'transaction_date' => now()->subDays(3),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ibu->id,
            'category_id' => $catPendidikan->id,
            'type' => 'expense',
            'amount' => 650000.00,
            'description' => 'Biaya Kursus Menggambar Sinta',
            'transaction_date' => now()->subDays(3),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ibu->id,
            'category_id' => $catKesehatan->id,
            'type' => 'expense',
            'amount' => 280000.00,
            'description' => 'Multivitamin Keluarga & Suplemen Kalsium',
            'transaction_date' => now()->subDays(2),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'category_id' => $catBBM->id,
            'type' => 'expense',
            'amount' => 450000.00,
            'description' => 'BBM Pertamax Mobil Avanza Keluarga',
            'transaction_date' => now()->subDays(1),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'category_id' => $catKuliner->id,
            'type' => 'expense',
            'amount' => 580000.00,
            'description' => 'Makan Malam Seafood Akhir Pekan',
            'transaction_date' => now()->subDays(1),
        ]);

        Finance::create([
            'family_id' => $family->id,
            'user_id' => $ibu->id,
            'category_id' => $catBelanja->id,
            'type' => 'expense',
            'amount' => 320000.00,
            'description' => 'Buah-Buahan & Sayur Organik',
            'transaction_date' => now(),
        ]);

        // 8. Tugas Rumah Tangga (Chores)
        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $budi->id,
            'title' => 'Cuci Piring Makan Malam',
            'description' => 'Rapikan wastafel dan bersihkan sisa makanan',
            'points' => 20,
            'due_date' => now()->toDateString(),
            'status' => 'completed',
            'completed_at' => now()->subHours(2),
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $budi->id,
            'title' => 'Buang Sampah ke Depan Komplek',
            'description' => 'Ikat kantong sampah plastik sebelum truk sampah lewat',
            'points' => 15,
            'due_date' => now()->toDateString(),
            'status' => 'completed',
            'completed_at' => now()->subHours(4),
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $sinta->id,
            'title' => 'Rapikan Meja Belajar & Kamar Tidur',
            'description' => 'Susun buku gambar dan simpan pensil warna di tempatnya',
            'points' => 15,
            'due_date' => now()->toDateString(),
            'status' => 'completed',
            'completed_at' => now()->subHours(5),
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $sinta->id,
            'title' => 'Siram Tanaman Teras Depan',
            'description' => 'Pakai selang air kecil dan jangan sampai air becek di lantai',
            'points' => 15,
            'due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $budi->id,
            'title' => 'Beri Makan & Ganti Air Kucing',
            'description' => 'Satu takar dry food pagi dan sore hari',
            'points' => 15,
            'due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $ayah->id,
            'title' => 'Beli Galon Aqua & Gas Elpiji 3kg',
            'description' => 'Toko kelontong depan gerbang perumahan',
            'points' => 25,
            'due_date' => now()->addDay()->toDateString(),
            'status' => 'pending',
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $ibu->id,
            'title' => 'Belanja Bahan Segar Pasar Tradisional',
            'description' => 'Stok sayur bayam, wortel, dan ikan salmon',
            'points' => 25,
            'due_date' => now()->subDays(2)->toDateString(),
            'status' => 'completed',
            'completed_at' => now()->subDays(2),
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $budi->id,
            'title' => 'Lipat Baju Kering Jemuran',
            'description' => 'Kumpulkan pakaian anak dan masukkan ke lemari kamar',
            'points' => 20,
            'due_date' => now()->addDay()->toDateString(),
            'status' => 'pending',
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $sinta->id,
            'title' => 'Rapikan Sepatu di Rak Teras',
            'description' => 'Susun sepatu sekolah dan sandal harian agar tertata rapi',
            'points' => 10,
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'pending',
        ]);

        Chore::create([
            'family_id' => $family->id,
            'assigned_to_user_id' => $ayah->id,
            'title' => 'Cuci Mobil & Motor Bersama Budi',
            'description' => 'Sabun bodi mobil dan bilas bersih saat pagi hari',
            'points' => 35,
            'due_date' => now()->subDays(4)->toDateString(),
            'status' => 'completed',
            'completed_at' => now()->subDays(4),
        ]);

        // 9. Agenda Kalender (Fitur Pro Aktif)
        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ibu->id,
            'title' => 'Belanja Bulanan & Stok Dapur',
            'description' => 'Belanja persediaan bahan pokok dan bumbu dapur bulanan',
            'start_time' => now()->subDays(18)->setHour(9)->setMinute(0),
            'end_time' => now()->subDays(18)->setHour(11)->setMinute(30),
            'location' => 'Superindo Dago Bandung',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ayah->id,
            'title' => 'Latihan Renang & Futsal Budi',
            'description' => 'Kegiatan ekstrakurikuler olahraga akhir pekan',
            'start_time' => now()->subDays(11)->setHour(15)->setMinute(0),
            'end_time' => now()->subDays(11)->setHour(17)->setMinute(0),
            'location' => 'Kolam Renang Karang Setra',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ayah->id,
            'title' => 'Silaturahmi & Makan Bersama Kakek',
            'description' => 'Kunjungan keluarga besar dan makan siang bersama',
            'start_time' => now()->subDays(5)->setHour(11)->setMinute(0),
            'end_time' => now()->subDays(5)->setHour(15)->setMinute(0),
            'location' => 'Rumah Kakek Subang',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ayah->id,
            'title' => 'Servis Mobil Berkala & Cek Rem',
            'description' => 'Perawatan 20.000 KM di Bengkel Resmi Auto 2000',
            'start_time' => now()->setHour(10)->setMinute(0),
            'end_time' => now()->setHour(12)->setMinute(0),
            'location' => 'Auto 2000 Pasteur',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ibu->id,
            'title' => 'Kontrol Behel Gigi Budi',
            'description' => 'Pemeriksaan rutin kawat gigi bulanan',
            'start_time' => now()->addDay()->setHour(14)->setMinute(0),
            'end_time' => now()->addDay()->setHour(15)->setMinute(30),
            'location' => 'Klinik Dental Care Sehat',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ayah->id,
            'title' => 'Arisan RT & Silaturahmi Blok C',
            'description' => 'Pertemuan warga bulanan membahas keamanan lingkungan',
            'start_time' => now()->addDays(3)->setHour(19)->setMinute(30),
            'end_time' => now()->addDays(3)->setHour(21)->setMinute(30),
            'location' => 'Balai Warga RT 04',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ibu->id,
            'title' => 'Pertemuan Guru & Orang Tua Sinta',
            'description' => 'Konsultasi perkembangan belajar dan sosialisasi anak di sekolah',
            'start_time' => now()->addDays(6)->setHour(9)->setMinute(0),
            'end_time' => now()->addDays(6)->setHour(11)->setMinute(0),
            'location' => 'TK Islam Al-Azhar',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ibu->id,
            'title' => 'Pembagian Rapor Semester Budi',
            'description' => 'Mengambil buku hasil belajar semester ganjil',
            'start_time' => now()->addDays(10)->setHour(8)->setMinute(30),
            'end_time' => now()->addDays(10)->setHour(11)->setMinute(0),
            'location' => 'SD Harapan Bangsa',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ayah->id,
            'title' => 'Ulang Tahun Ibu Maya & Makan Malam',
            'description' => 'Makan malam keluarga merayakan ultah Ibu tercinta',
            'start_time' => now()->addDays(14)->setHour(18)->setMinute(30),
            'end_time' => now()->addDays(14)->setHour(21)->setMinute(0),
            'location' => 'Restoran Kampung Daun',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ibu->id,
            'title' => 'Jadwal Imunisasi Booster Adik',
            'description' => 'Vaksinasi lanjutan anak di faskes puskesmas',
            'start_time' => now()->addDays(18)->setHour(8)->setMinute(30),
            'end_time' => now()->addDays(18)->setHour(10)->setMinute(30),
            'location' => 'Puskesmas Sukajadi',
        ]);

        Event::create([
            'family_id' => $family->id,
            'created_by_user_id' => $ayah->id,
            'title' => 'Weekend Liburan ke Floating Market',
            'description' => 'Ajak anak-anak bermain perahu air dan kuliner kelinci',
            'start_time' => now()->addDays(22)->setHour(9)->setMinute(0),
            'end_time' => now()->addDays(22)->setHour(16)->setMinute(0),
            'location' => 'Floating Market Lembang',
        ]);

        // 10. Linimasa Momen & Catatan Liburan (Fitur Pro Aktif)
        TimelineMoment::create([
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'title' => 'Weekend Ceria di Pantai Anyer',
            'story' => 'Menikmati matahari terbenam sambil anak-anak main pasir dan naik banana boat bersama!',
            'location_name' => 'Pantai Anyer, Banten',
            'latitude' => -6.05600000,
            'longitude' => 105.89060000,
            'image_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
            'event_date' => now()->subDays(6),
        ]);

        TimelineMoment::create([
            'family_id' => $family->id,
            'user_id' => $ibu->id,
            'title' => 'Glamping Sejuk di Kebun Teh Ciwidey',
            'story' => 'Petik buah strawberry segar di perkebunan teh, udara sejuk dan anak-anak senang bakar sosis di depan tenda.',
            'location_name' => 'Ciwidey, Bandung, Jawa Barat',
            'latitude' => -7.14750000,
            'longitude' => 107.39450000,
            'image_path' => 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=800&q=80',
            'event_date' => now()->subDays(14),
        ]);

        TimelineMoment::create([
            'family_id' => $family->id,
            'user_id' => $budi->id,
            'title' => 'Budi Juara 1 Lomba Sains & Robotik',
            'story' => 'Bangga sekali karya perakitan mobil tenaga surya Budi berhasil meraih piala juara 1 tingkat kota!',
            'location_name' => 'SD Harapan Bangsa, Bandung',
            'latitude' => -6.90380000,
            'longitude' => 107.59910000,
            'image_path' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80',
            'event_date' => now()->subDays(22),
        ]);

        TimelineMoment::create([
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'title' => 'Wisata Edukasi Candi Prambanan',
            'story' => 'Mengenalkan sejarah nusantara dan arsitektur warisan dunia saat liburan mudik ke Yogyakarta.',
            'location_name' => 'Candi Prambanan, DI Yogyakarta',
            'latitude' => -7.75200000,
            'longitude' => 110.49140000,
            'image_path' => 'https://images.unsplash.com/photo-1566127444979-b3d2b654e3d7?auto=format&fit=crop&w=800&q=80',
            'event_date' => now()->subDays(35),
        ]);

        TimelineMoment::create([
            'family_id' => $family->id,
            'user_id' => $ayah->id,
            'title' => 'Liburan Seru di Pantai Pandawa',
            'story' => 'Piknik pasir putih dan belajar main kano bersama di pantai tebing kapur Bali.',
            'location_name' => 'Pantai Pandawa, Kuta Selatan, Bali',
            'latitude' => -8.84510000,
            'longitude' => 115.18710000,
            'image_path' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?auto=format&fit=crop&w=800&q=80',
            'event_date' => now()->subDays(50),
        ]);

        TimelineMoment::create([
            'family_id' => $family->id,
            'user_id' => $ibu->id,
            'title' => 'Piknik Santai di Kebun Raya Bogor',
            'story' => 'Gelar tikar di bawah pohon rindang, makan bekal buatan Ibu Maya sembari mengamati rusa tutul.',
            'location_name' => 'Kebun Raya Bogor, Jawa Barat',
            'latitude' => -6.59760000,
            'longitude' => 106.79960000,
            'image_path' => 'https://images.unsplash.com/photo-1533227268428-f9ed0900fb3b?auto=format&fit=crop&w=800&q=80',
            'event_date' => now()->subDays(65),
        ]);

        // 7. Master Kalender Kerja (m_wkcal) Tahun 2026
        (new WkcalService)->generateYear(2026, 'DatabaseSeeder');

        // 8. Super Administrator Account
        $this->call(SuperAdminSeeder::class);
    }
}
