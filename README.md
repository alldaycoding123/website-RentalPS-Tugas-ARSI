# PSRental — Sistem Informasi Booking Rental PlayStation Online

Aplikasi web **Laravel 11** (kompatibel Laravel 12) untuk mengelola booking rental PlayStation:
unit, jadwal, booking, pelanggan, pembayaran, riwayat, dan laporan — dalam satu aplikasi.

**Frontend tanpa Node.js / Vite / npm**: Tailwind CSS, Alpine.js, Chart.js, qrcode.js, dan
html5-qrcode dimuat lewat CDN dari layout Blade. Cukup `composer install`,
`php artisan migrate --seed`, lalu jalankan.

---

## 1. Persyaratan

| Komponen | Versi |
|---|---|
| PHP | ≥ 8.2 (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`) |
| MySQL / MariaDB | 5.7+ / 10.3+ (charset `utf8mb4`) |
| Composer | bawaan Laragon |
| Browser | Chrome/Edge/Firefox terbaru (kamera QR butuh HTTPS/localhost) |

---

## 2. Panduan Setup di Laragon (Windows)

### a. Buat proyek
Buka **Laragon → Menu → Quick app → Laravel** (nama aplikasi: `psrental`),
atau lewat terminal Laragon:

```bash
cd C:\laragon\www
composer create-project laravel/laravel psrental
```

Pastikan PHP ≥ 8.2 dan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` aktif
(Laragon → PHP → Extensions).

> Bila file proyek ini sudah Anda salin ke `C:\laragon\www\psrental`, langsung lanjut ke poin **b**.

### b. Start layanan & buat database
1. **Start All** (Apache/Nginx + MySQL).
2. Buat database `psrental` lewat **HeidiSQL** / **phpMyAdmin**
   (collation `utf8mb4_unicode_ci`).

### c. Sesuaikan `.env`

```env
APP_NAME=PSRental
APP_URL=http://psrental.test
APP_TIMEZONE=Asia/Makassar
APP_LOCALE=id
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=psrental
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

> `SESSION_DRIVER=file` dan `CACHE_STORE=file` disengaja agar tidak butuh tabel `sessions`.

### d. Install & migrasi

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan optimize:clear
```

### e. Jalankan

- Akses **http://psrental.test** (Auto Virtual Host Laragon), atau
- `php artisan serve` → **http://127.0.0.1:8000**

### f. Akun demo

| Peran | Login | Password |
|---|---|---|
| Kasir | `kasir@psrental.test` | `password123` |
| Admin | `admin@psrental.test` | `password123` |
| Manajer | `manajer@psrental.test` | `password123` |
| Pelanggan | No. HP `081234567890` (tanpa password) | — |

Pelanggan baru cukup memasukkan No. HP — akun otomatis dibuat.

---

## 3. Alur Aplikasi

### Pelanggan (`/`)
1. `/` — pilih akses: **Pelanggan** atau **Staff/Admin**.
2. `/pelanggan/login` — scan QR (kamera) atau input No. HP; tersedia tombol **Akun Demo**.
3. `/pelanggan/unit` — grid unit (filter Semua/PS5/PS4), badge status
   *Tersedia* (hijau), *Sudah Dibooking* (oranye), *Maintenance* (abu).
   Status auto-refresh tiap 15 detik (`GET /api/units/status`).
4. `/pelanggan/unit/{unit}/jadwal` — pilih tanggal + slot 09:00–21:00
   (state: Tersedia / Dipilih / Terpakai-merah & disabled; slot lewat otomatis disabled;
   slot harus **berurutan**, maks 6 jam) — data dari `GET /api/units/{unit}/slots`.
5. `/pelanggan/pembayaran` — ringkasan + metode (Transfer/E-Wallet/Bayar di Tempat).
6. `/pelanggan/booking/{kode}/sukses` — kode booking + QR + rincian.
7. `/pelanggan/riwayat` — booking saya, detail + QR, pembatalan
   (maks. 1 jam sebelum jadwal, hanya PENDING/CONFIRMED).

### Kasir (`/kasir`, role KASIR)
Dashboard · Check-in (kode/QR/No. HP) · Check-out · Proses Pembayaran.

### Admin (`/admin`, role ADMIN)
Dashboard realtime · Data PS (CRUD + status + harga) · Jadwal (matriks unit × jam) ·
Booking (filter + paginasi + ubah status + walk-in) · Pelanggan (CRUD + cari) ·
Pembayaran (filter status/metode) · Laporan (filter tanggal + **Cetak** + **Ekspor CSV**).

### Manajer (`/manajer`, role MANAJER)
Dashboard + grafik pendapatan 7 hari (Chart.js) · Laporan Pendapatan (harian/bulanan) ·
Performa Unit · Tren & Analitik.

---

## 4. Aturan Bisnis Utama

- **Anti double booking**: `App\Services\BookingService::buat()` menjalankan
  `DB::transaction()` + pengecekan overlap dengan `lockForUpdate()`.
  Bila bentrok dilempar `App\Exceptions\SlotBentrokException` dan pengguna
  dikembalikan ke pemilihan jadwal dengan pesan jelas.
- **Total dihitung server-side** = `harga_jam × durasi_jam` (angka browser diabaikan).
- Slot diambil ulang dari server setiap kali (bisa berubah karena booking orang lain).
- Kode booking: `BK-` + 6 karakter `[A-Z0-9]` (dicek unik).
- Setiap booking selalu dibuat bersama satu record pembayaran `BELUM_BAYAR`.
- Booking `PENDING` menjadi `CONFIRMED` setelah pembayaran dikonfirmasi kasir.
- Check-in mengisi `id_staff` + status unit → `DIGUNAKAN`;
  check-out mengembalikan unit ke `TERSEDIA`.
- Hapus unit/pelanggan ditolak bila masih punya booking.
- Otorisasi: middleware kustom `role:KASIR,ADMIN,MANAJER` (alias di `bootstrap/app.php`)
  dan `customer.auth` untuk pelanggan; pelanggan hanya bisa melihat booking miliknya (404).
- Throttle login `throttle:10,1` untuk login staff & pelanggan.

---

## 5. Struktur Proyek

```
psrental/
├── app/
│   ├── Exceptions/SlotBentrokException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/        (Dashboard, Unit, Jadwal, Booking, Pelanggan, Pembayaran, Laporan)
│   │   │   ├── Api/          (UnitApiController, ChartController)
│   │   │   ├── Customer/     (AuthController, UnitController, BookingController, RiwayatController)
│   │   │   ├── Kasir/        (DashboardController, CheckinController, PembayaranController)
│   │   │   ├── Manajer/      (DashboardController, LaporanController)
│   │   │   └── Staff/AuthController.php
│   │   ├── Middleware/       (EnsureRole, EnsureCustomerAuth)
│   │   └── Requests/         (LoginStaff, LoginPelanggan, StoreBooking, Unit, Pelanggan, StoreWalkinBooking)
│   ├── Models/               (Staff, Pelanggan, UnitPs, Booking, Pembayaran)
│   ├── Services/BookingService.php
│   └── helpers.php           (rupiah(), jam(), tanggal_id())
├── bootstrap/app.php         (alias middleware + redirect guest)
├── config/auth.php           (guard & provider `staff`)
├── database/
│   ├── factories/            (Staff, Pelanggan, UnitPs)
│   ├── migrations/           (staff, pelanggan, unit_ps, booking, pembayaran)
│   └── seeders/              (Staff, UnitPs, Pelanggan, Booking, DatabaseSeeder)
├── resources/views/
│   ├── layouts/              (customer, staff, auth)
│   ├── components/           (x-badge, x-card, x-button, x-flash, x-stat)
│   ├── customer/  kasir/  admin/  manajer/  auth/
│   └── pilih-akses.blade.php
├── routes/web.php            (semua route dikelompokkan per peran)
├── scripts/smoke-test.sh     (test alur otomatis)
└── README.md
```

---

## 6. Smoke Test (opsional)

Jalankan aplikasi dulu (`php artisan serve`), lalu:

```bash
bash scripts/smoke-test.sh http://127.0.0.1:8000
```

Script memeriksa: halaman publik, proteksi akses, alur booking pelanggan
(termasuk anti double booking), check-in/out & pembayaran kasir,
CRUD admin + laporan/CSV, halaman manajer, dan isolasi antar peran.

---

## 7. Troubleshooting

| Masalah | Solusi |
|---|---|
| `could not find driver` | Aktifkan ekstensi `pdo_mysql` di `php.ini` lalu restart Apache |
| `No application encryption key` | `php artisan key:generate` |
| Halaman 404 di virtual host | Reload Apache, aktifkan **Auto Virtual Host**, cek `APP_URL=http://psrental.test` |
| Kamera scan QR tidak jalan | Kamera hanya diizinkan di **HTTPS** atau **localhost/127.0.0.1**. Untuk testing lokal gunakan `php artisan serve` → `http://127.0.0.1:8000` |
| Error storage / bootstrap cache | Beri izin tulis pada `storage` & `bootstrap/cache`, lalu `php artisan optimize:clear` |
| Tailwind/CDN tidak tampil | Pastikan komputer terhubung internet (semua library memakai CDN) |
| Ingin data baru lagi | `php artisan migrate:fresh --seed` |
| Composer menolak install karena *security advisory* | `composer.json` sudah mengisi `config.audit` (abaikan advisory laravel/framework v11). Bila tetap diblokir, jalankan `composer install --no-audit` |
| Port 8000 terpakai | `php artisan serve --port=8080` |

---

## 8. Perintah Singkat

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan optimize:clear
php artisan serve
```
