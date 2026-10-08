<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\UnitPs;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Daftar unit PS (grid).
     */
    public function index()
    {
        $units = UnitPs::orderBy('kode_unit')->get();

        return view('admin.units.index', ['units' => $units]);
    }

    public function create()
    {
        return view('admin.units.create');
    }

    /**
     * Simpan unit baru (grid otomatis menyesuaikan jumlah unit).
     */
    public function store(UnitRequest $request)
    {
        UnitPs::create($request->validated());

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit PS berhasil ditambahkan.');
    }

    public function edit(UnitPs $unit)
    {
        return view('admin.units.edit', ['unit' => $unit]);
    }

    public function update(UnitRequest $request, UnitPs $unit)
    {
        $unit->update($request->validated());

        return redirect()->route('admin.units.index')
            ->with('success', "Unit {$unit->kode_unit} berhasil diperbarui.");
    }

    /**
     * Hapus unit — ditolak bila masih punya booking.
     */
    public function destroy(Request $request, UnitPs $unit)
    {
        if ($unit->bookings()->exists()) {
            return back()->with('error', 'Unit tidak bisa dihapus karena masih memiliki data booking.');
        }

        $unit->delete();

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit PS berhasil dihapus.');
    }

    /**
     * Ubah status cepat (Tersedia / Maintenance / Digunakan).
     */
    public function ubahStatus(Request $request, UnitPs $unit)
    {
        $data = $request->validate([
            'status' => ['required', 'in:TERSEDIA,MAINTENANCE,DIGUNAKAN'],
        ]);

        $unit->update($data);

        return back()->with('success', "Status {$unit->kode_unit} diubah menjadi {$data['status']}.");
    }
}
