<?php

namespace App\Http\Controllers;

use App\Models\AlatGym;
use Illuminate\Http\Request;

class AlatGymController extends Controller
{
    /**
     * Tampilkan semua data alat gym
     */
    public function index()
    {
        $alatGyms = AlatGym::latest()->paginate(10);
        return view('alatgym.index', compact('alatGyms'));
    }

    /**
     * Form tambah data
     */
    public function create()
    {
        return view('alatgym.create');
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

        AlatGym::create($validated);

        return redirect()->route('alatgym.index')->with('success', 'Data alat gym berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail data
     */
    public function show(AlatGym $alatgym)
    {
        return view('alatgym.show', compact('alatgym'));
    }

    /**
     * Form edit data
     */
    public function edit(AlatGym $alatgym)
    {
        return view('alatgym.edit', compact('alatgym'));
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

        $alatgym->update($validated);

        return redirect()->route('alatgym.index')->with('success', 'Data alat gym berhasil diperbarui.');
    }

    /**
     * Hapus data
     */
    public function destroy(AlatGym $alatgym)
    {
        $alatgym->delete();
        return redirect()->route('alatgym.index')->with('success', 'Data alat gym berhasil dihapus.');
    }
}
