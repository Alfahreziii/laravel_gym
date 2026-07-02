<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;

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
        return view('pages.admin.kasir.kategori-product.index', compact('kategori_products'));
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = KategoriProduct::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->latest()->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'          => (($page - 1) * $perPage) + $index + 1,
                    'id'          => $item->id,
                    'name'        => $item->name,
                    'description' => $item->description ?? '',
                    'update_url'  => route('kategori_products.update', $item->id),
                    'delete_url'  => route('kategori_products.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
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
