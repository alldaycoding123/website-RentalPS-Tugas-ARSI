<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Api\ChartController;
use App\Http\Controllers\Api\UnitApiController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\RiwayatController;
use App\Http\Controllers\Customer\UnitController as CustomerUnitController;
use App\Http\Controllers\Kasir;
use App\Http\Controllers\Manajer;
use App\Http\Controllers\Staff\AuthController as StaffAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik: pilih akses
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('pilih-akses'))->name('beranda');

/*
|--------------------------------------------------------------------------
| Autentikasi STAFF (Kasir, Admin, Manajer) — throttle login
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/staff/login', [StaffAuthController::class, 'showLogin'])->name('staff.login');
    Route::post('/staff/login', [StaffAuthController::class, 'login'])->name('staff.login.proses');
});

Route::post('/staff/logout', [StaffAuthController::class, 'logout'])
    ->middleware('auth:staff')
    ->name('staff.logout');

/*
|--------------------------------------------------------------------------
| Endpoint JSON (session-based, tanpa token API)
|--------------------------------------------------------------------------
*/
Route::get('/api/units/status', [UnitApiController::class, 'status'])->name('api.units.status');
Route::get('/api/units/{unit}/slots', [UnitApiController::class, 'slots'])->name('api.units.slots');
Route::get('/api/chart/pendapatan', [ChartController::class, 'pendapatan'])
    ->middleware('auth:staff')
    ->name('api.chart.pendapatan');
Route::get('/api/chart/booking-status', [ChartController::class, 'statusBooking'])
    ->middleware('auth:staff')
    ->name('api.chart.booking-status');

/*
|--------------------------------------------------------------------------
| PELANGGAN (session No. HP / QR)
|--------------------------------------------------------------------------
*/
Route::prefix('pelanggan')->name('customer.')->group(function () {
    // Login (throttled)
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.proses');
        Route::post('/demo', [CustomerAuthController::class, 'demo'])->name('demo');
    });

    Route::middleware('customer.auth')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

        // Pilih unit & jadwal
        Route::get('/unit', [CustomerUnitController::class, 'index'])->name('units');
        Route::get('/unit/{unit}/jadwal', [CustomerUnitController::class, 'jadwal'])->name('units.jadwal');
        Route::post('/unit/{unit}/jadwal', [CustomerUnitController::class, 'simpanPilihan'])->name('units.jadwal.simpan');

        // Pembayaran & konfirmasi booking
        Route::get('/pembayaran', [CustomerBookingController::class, 'pembayaran'])->name('pembayaran');
        Route::post('/pembayaran', [CustomerBookingController::class, 'store'])->name('pembayaran.store');
        Route::get('/booking/{kode}/sukses', [CustomerBookingController::class, 'sukses'])->name('booking.sukses');

        // Riwayat booking saya
        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');
        Route::get('/riwayat/{booking}', [RiwayatController::class, 'show'])->name('riwayat.show');
        Route::post('/riwayat/{booking}/batal', [RiwayatController::class, 'batal'])->name('riwayat.batal');
    });
});

/*
|--------------------------------------------------------------------------
| KASIR (prefix /kasir, role KASIR)
|--------------------------------------------------------------------------
*/
Route::prefix('kasir')->name('kasir.')
    ->middleware(['auth:staff', 'role:KASIR'])
    ->group(function () {
        Route::get('/dashboard', [Kasir\DashboardController::class, 'index'])->name('dashboard');

        // Check-in (kode booking / scan QR / No. HP)
        Route::get('/checkin', [Kasir\CheckinController::class, 'index'])->name('checkin');
        Route::post('/checkin/{booking}', [Kasir\CheckinController::class, 'proses'])->name('checkin.proses');

        // Check-out
        Route::get('/checkout', [Kasir\CheckinController::class, 'checkoutIndex'])->name('checkout');
        Route::post('/checkout/{booking}', [Kasir\CheckinController::class, 'checkout'])->name('checkout.proses');

        // Proses pembayaran
        Route::get('/pembayaran', [Kasir\PembayaranController::class, 'index'])->name('pembayaran');
        Route::post('/pembayaran/{booking}', [Kasir\PembayaranController::class, 'proses'])->name('pembayaran.proses');
    });

/*
|--------------------------------------------------------------------------
| ADMIN (prefix /admin, role ADMIN)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')
    ->middleware(['auth:staff', 'role:ADMIN'])
    ->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // CRUD Data PS
        Route::resource('units', Admin\UnitController::class)->except(['show']);
        Route::patch('/units/{unit}/status', [Admin\UnitController::class, 'ubahStatus'])->name('units.status');

        // Jadwal (matriks unit × jam)
        Route::get('/jadwal', [Admin\JadwalController::class, 'index'])->name('jadwal.index');

        // Booking (+ walk-in manual)
        Route::get('/booking', [Admin\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/booking/create', [Admin\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/booking', [Admin\BookingController::class, 'store'])->name('bookings.store');
        Route::get('/booking/{booking}/edit', [Admin\BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/booking/{booking}', [Admin\BookingController::class, 'update'])->name('bookings.update');
        Route::delete('/booking/{booking}', [Admin\BookingController::class, 'destroy'])->name('bookings.destroy');

        // CRUD Pelanggan
        Route::resource('pelanggans', Admin\PelangganController::class)->except(['show']);

        // Pembayaran
        Route::get('/pembayaran', [Admin\PembayaranController::class, 'index'])->name('pembayarans.index');

        // Laporan transaksi + ekspor CSV
        Route::get('/laporan', [Admin\LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/csv', [Admin\LaporanController::class, 'csv'])->name('laporan.csv');
    });

/*
|--------------------------------------------------------------------------
| MANAJER (prefix /manajer, role MANAJER) — hanya laporan & analitik
|--------------------------------------------------------------------------
*/
Route::prefix('manajer')->name('manajer.')
    ->middleware(['auth:staff', 'role:MANAJER'])
    ->group(function () {
        Route::get('/dashboard', [Manajer\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/pendapatan', [Manajer\DashboardController::class, 'apiPendapatan'])->name('api.pendapatan');

        Route::get('/laporan/pendapatan', [Manajer\LaporanController::class, 'pendapatan'])->name('laporan.pendapatan');
        Route::get('/laporan/performa-unit', [Manajer\LaporanController::class, 'performaUnit'])->name('laporan.performa');
        Route::get('/laporan/tren', [Manajer\LaporanController::class, 'tren'])->name('laporan.tren');
    });
