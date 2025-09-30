<?php

namespace App\Http\Controllers;

use App\Models\Specialisasi;
use Illuminate\Http\Request;
use Exception;

class SpecialisasiController extends Controller
{
    /**
     * Tampilkan semua data specialisasi
     */
    public function index()
    {
        $specialisasis = Specialisasi::all();
        return view('pages.specialisasi.index', compact('specialisasis'));
    }

    /**
     * Simpan specialisasi baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_specialisasi' => 'required|string|max:255',
        ]);

        try {
            Specialisasi::create([
                'nama_specialisasi' => $request->nama_specialisasi,
            ]);

            return redirect()->route('specialisasi.index')
                            ->with('success', 'Specialisasi berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Gagal menambahkan specialisasi: ' . $e->getMessage());
        }
    }

    /**
     * Update specialisasi
     */
    public function update(Request $request, Specialisasi $specialisasi)
    {
        $request->validate([
            'nama_specialisasi' => 'required|string|max:255',
        ]);

        try {
            $specialisasi->update([
                'nama_specialisasi' => $request->nama_specialisasi,
            ]);

            return redirect()->route('specialisasi.index')
                            ->with('success', 'Specialisasi berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Gagal memperbarui specialisasi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus specialisasi
     */
    public function destroy(Specialisasi $specialisasi)
    {
        try {
            $specialisasi->delete();

            return redirect()->route('specialisasi.index')
                            ->with('success', 'Specialisasi berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()
                            ->with('error', 'Gagal menghapus specialisasi: ' . $e->getMessage());
        }
    }
}
