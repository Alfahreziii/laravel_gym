<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduct;
use Illuminate\Http\Request;

class KategoriProductController extends Controller
{
    /**
     * Tampilkan semua kategori produk (menampilkan daftar + modal add/edit)
     */
    public function index()
    {
        $kategori_products = KategoriProduct::latest()->get();
        return view('pages.kategoriproduct.index', compact('kategori_products'));
    }

    /**
     * Simpan kategori baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        KategoriProduct::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('kategori_products.index')
                         ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Update kategori (dari modal edit)
     */
    public function update(Request $request, KategoriProduct $kategori_product)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $kategori_product->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('kategori_products.index')
                         ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori
     */
    public function destroy(KategoriProduct $kategori_product)
    {
        $kategori_product->delete();

        return redirect()->route('kategori_products.index')
                         ->with('success', 'Kategori berhasil dihapus.');
    }
}
