<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PelangganRequest;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    /**
     * Daftar pelanggan + pencarian.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $pelanggans = Pelanggan::withCount('bookings')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_hp', 'like', "%{$q}%"));
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pelanggans.index', [
            'pelanggans' => $pelanggans,
            'q' => $q,
        ]);
    }

    public function create()
    {
        return view('admin.pelanggans.create');
    }

    public function store(PelangganRequest $request)
    {
        Pelanggan::create($request->validated());

        return redirect()->route('admin.pelanggans.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('admin.pelanggans.edit', ['pelanggan' => $pelanggan]);
    }

    public function update(PelangganRequest $request, Pelanggan $pelanggan)
    {
        $pelanggan->update($request->validated());

        return redirect()->route('admin.pelanggans.index')
            ->with('success', "Data pelanggan {$pelanggan->nama} diperbarui.");
    }

    /**
     * Hapus pelanggan — ditolak bila masih punya booking.
     */
    public function destroy(Request $request, Pelanggan $pelanggan)
    {
        if ($pelanggan->bookings()->exists()) {
            return back()->with('error', 'Pelanggan tidak bisa dihapus karena masih memiliki riwayat booking.');
        }

        $pelanggan->delete();

        return redirect()->route('admin.pelanggans.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
