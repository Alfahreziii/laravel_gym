<?php

namespace App\Http\Controllers;

use App\Models\AlatGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlatGymController extends Controller
{
    /**
     * Tampilkan semua data alat gym
     */
    public function index()
    {
        $alatGyms = AlatGym::latest()->paginate(10);
        return view('pages.alatgym.index', compact('alatGyms'));
    }

    /**
     * Form tambah data
     */
    public function create()
    {
        return view('pages.alatgym.create');
    }

    /**
     * Simpan data baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:50|unique:alat_gyms,barcode',
            'nama_alat_gym' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'tgl_pembelian' => 'nullable|date',
            'lokasi_alat' => 'nullable|string|max:100',
            'kondisi_alat' => 'nullable|string|max:50',
            'vendor' => 'nullable|string|max:100',
            'kontak' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        try {
            AlatGym::create($validated);
            return redirect()->route('alat_gym.index')->with('success', 'Data alat gym berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menambahkan data alat gym.');
        }
    }

    /**
     * Form edit data
     */
    public function edit(AlatGym $alatgym)
    {
        return view('pages.alatgym.edit', compact('alatgym'));
    }

    /**
     * Update data
     */
    public function update(Request $request, AlatGym $alatgym)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:50|unique:alat_gyms,barcode,' . $alatgym->id,
            'nama_alat_gym' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'tgl_pembelian' => 'nullable|date',
            'lokasi_alat' => 'nullable|string|max:100',
            'kondisi_alat' => 'nullable|string|max:50',
            'vendor' => 'nullable|string|max:100',
            'kontak' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $alatgym->update($validated);
            return redirect()->route('alat_gym.index')->with('success', 'Data alat gym berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('danger', 'Terjadi kesalahan saat memperbarui data alat gym.');
        }
    }

    /**
     * Hapus data
     */
    public function destroy(AlatGym $alatgym)
    {
        try {
            $alatgym->delete();
            return redirect()->route('alat_gym.index')->with('success', 'Data alat gym berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('alat_gym.index')->with('danger', 'Terjadi kesalahan saat menghapus data alat gym.');
        }
    }
}
