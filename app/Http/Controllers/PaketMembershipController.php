<?php

namespace App\Http\Controllers;

use App\Models\PaketMembership;
use App\Models\KategoriPaketMembership;
use Illuminate\Http\Request;

class PaketMembershipController extends Controller
{
    /**
     * Tampilkan semua paket membership
     */
    public function index()
    {
        // ambil semua paket beserta kategori
        $paketMemberships = PaketMembership::with('kategori')->get();
        return view('pages.paketmembership.index', compact('paketMemberships'));
    }

    /**
     * Tampilkan form untuk membuat paket baru
     */
    public function create()
    {
        $kategoriPaket = KategoriPaketMembership::all();
        return view('pages.paketmembership.create', compact('kategoriPaket'));
    }

    /**
     * Simpan paket baru ke database
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_kategori' => 'required|exists:kategori_paket_memberships,id',
                'nama_paket' => 'required|string|max:255',
                'durasi' => 'required|integer',
                'periode' => 'required|string|max:50',
                'harga' => 'required|numeric',
                'keterangan' => 'nullable|string',
            ]);

            PaketMembership::create($request->all());

            return redirect()->route('paket_membership.index')
                            ->with('success', 'Paket membership berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal menambahkan paket: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Tampilkan form edit paket
     */
    public function edit(PaketMembership $paket_membership)
    {
        $kategoriPaket = KategoriPaketMembership::all();
        return view('pages.paketmembership.edit', compact('paketmembership', 'kategoriPaket'));
    }

    /**
     * Update data paket
     */
    public function update(Request $request, PaketMembership $paket_membership)
    {
        try {
            $request->validate([
                'id_kategori' => 'required|exists:kategori_paket_memberships,id',
                'nama_paket' => 'required|string|max:255',
                'durasi' => 'required|integer',
                'periode' => 'required|string|max:50',
                'harga' => 'required|numeric',
                'keterangan' => 'nullable|string',
            ]);

            $paket_membership->update($request->all());

            return redirect()->route('paket_membership.index')
                            ->with('success', 'Paket membership berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal memperbarui paket: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Hapus paket
     */
    public function destroy(PaketMembership $paket_membership)
    {
        try {
            $paket_membership->delete();

            return redirect()->route('paket_membership.index')
                            ->with('success', 'Paket membership berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }
}
