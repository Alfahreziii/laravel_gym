<?php

namespace App\Http\Controllers;

use App\Models\KategoriPaketMembership;
use Illuminate\Http\Request;

class KategoriPaketController extends Controller
{
    /**
     * Tampilkan semua kategori paket membership
     */
    public function index()
    {
        $kategori_paket_memberships = KategoriPaketMembership::all();
        return view('pages.kategoripaketmember.index', compact('kategori_paket_memberships'));
    }

    /**
     * Simpan kategori baru ke database
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_kategori' => 'required|string|max:255|unique:kategori_paket_memberships,nama_kategori',
            ]);

            KategoriPaketMembership::create([
                'nama_kategori' => $request->nama_kategori,
            ]);

            return redirect()->route('kategori_paket_membership.index')->with('success', 'Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal menambahkan kategori: ' . $e->getMessage())
                            ->withInput();
        }
    }
    /**
     * Update kategori
     */
    public function update(Request $request, KategoriPaketMembership $kategori_paket_membership)
    {
        try {
            $request->validate([
                'nama_kategori' => 'required|string|max:255|unique:kategori_paket_memberships,nama_kategori,' . $kategori_paket_membership->id,
            ]);

            $kategori_paket_membership->update([
                'nama_kategori' => $request->nama_kategori,
            ]);

            return redirect()->route('kategori_paket_membership.index')->with('success', 'Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal memperbarui kategori: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Hapus kategori
     */
    public function destroy(KategoriPaketMembership $kategori_paket_membership)
    {
        try {
            $kategori_paket_membership->delete();

            return redirect()->route('kategori_paket_membership.index')
                            ->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }
}
