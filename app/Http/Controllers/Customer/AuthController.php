<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginPelangganRequest;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Halaman login pelanggan: scan QR atau input No. HP.
     */
    public function showLogin(Request $request)
    {
        $pelanggan = Pelanggan::find($request->session()->get('pelanggan_id'));

        // QR sesi berisi token sesi + No. HP pelanggan bila sudah login
        $qrPayload = 'PSR:'.session()->getId();

        return view('customer.login', [
            'pelanggan' => $pelanggan,
            'sessionId' => session()->getId(),
            'qrPayload' => $qrPayload,
        ]);
    }

    /**
     * Proses login pelanggan: registrasi otomatis bila No. HP baru.
     */
    public function login(LoginPelangganRequest $request)
    {
        $noHp = $request->validated('no_hp');

        // Scan QR sesi (berisi "PSR:<session id>"): validasi ke session yang sama,
        // lalu masuk sebagai pelanggan demo (perilaku demo untuk QR sesi).
        if (str_starts_with($noHp, 'PSR:')) {
            if (substr($noHp, 4) === session()->getId()) {
                return $this->demo($request);
            }

            return back()->with('error', 'QR tidak dikenali / QR bukan milik sesi ini. Silakan masukkan No. HP Anda.');
        }

        // Cari atau buat pelanggan baru (registrasi otomatis)
        $pelanggan = Pelanggan::firstOrCreate(
            ['no_hp' => $noHp],
            ['nama' => 'Pelanggan Baru']
        );

        $request->session()->regenerate();
        $request->session()->put('pelanggan_id', $pelanggan->id_pelanggan);

        return redirect()->route('customer.units')
            ->with('success', "Selamat datang, {$pelanggan->nama}!");
    }

    /**
     * Login cepat memakai akun demo 081234567890.
     */
    public function demo(Request $request)
    {
        $pelanggan = Pelanggan::firstOrCreate(
            ['no_hp' => '081234567890'],
            ['nama' => 'Pelanggan Demo']
        );

        $request->session()->regenerate();
        $request->session()->put('pelanggan_id', $pelanggan->id_pelanggan);

        return redirect()->route('customer.units')->with('success', 'Login sebagai pelanggan demo berhasil.');
    }

    /**
     * Logout pelanggan: hapus session pelanggan.
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['pelanggan_id', 'booking_draft']);
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('success', 'Anda telah keluar.');
    }
}
