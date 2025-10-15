<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\KategoriProduct;
use App\Models\ProductQuantityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Tampilkan semua produk
     */
    public function index()
    {
        $products = Product::with('kategori')->latest()->get();
        return view('pages.products.index', compact('products'));
    }

    /**
     * Form tambah produk
     */
    public function create()
    {
        $categories = KategoriProduct::all();
        return view('pages.products.create', compact('categories'));
    }

    /**
     * Simpan produk baru
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'barcode' => 'required|string|max:100|unique:products',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percent,nominal',
                'quantity' => 'integer|min:0',
                'is_active' => 'boolean',
                'kategori_product_id' => 'required|exists:kategori_products,id',
            ]);

            $data = $validated;

            // Upload gambar jika ada
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $data['image'] = $path;
            }

            // Simpan produk
            $product = Product::create($data);

            // Tambahkan log stok awal (barang masuk)
            if ($product->quantity > 0) {
                ProductQuantityLog::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $product->quantity,
                    'current_quantity' => $product->quantity,
                    'description' => 'Stok awal produk',
                ]);
            }

            return back()->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('danger', 'Gagal menambahkan produk: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Form edit produk
     */
    public function edit(Product $product)
    {
        $categories = KategoriProduct::all();
        return view('pages.products.edit', compact('product', 'categories'));
    }

    /**
     * Update data produk
     */
    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'barcode' => 'required|string|max:100|unique:products,barcode,' . $product->id,
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percent,nominal',
                'is_active' => 'boolean',
                'kategori_product_id' => 'required|exists:kategori_products,id',
            ]);

            $data = $validated;

            // Handle update gambar
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                // Upload gambar baru
                $path = $request->file('image')->store('products', 'public');
                $data['image'] = $path;
            }

            $product->update($data);

            // Kembali ke halaman form dengan alert sukses
            return back()->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            // Kembali ke halaman form dengan pesan error
            return back()->with('danger', 'Gagal memperbarui produk: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Hapus produk
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('danger', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    /**
     * Update quantity produk (barang masuk/keluar)
     */
    public function adjustQuantity(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out', // in = tambah stok, out = kurangi stok
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        // Hitung stok baru
        $change = $validated['type'] === 'in' 
            ? $validated['quantity'] 
            : -$validated['quantity'];

        $newQuantity = $product->quantity + $change;

        if ($newQuantity < 0) {
            return back()->with('danger', 'Stok tidak boleh kurang dari 0.');
        }

        // Simpan log
        ProductQuantityLog::create([
            'product_id' => $product->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'current_quantity' => $newQuantity,
            'description' => $validated['description'] ?? 
                ($validated['type'] === 'in' ? 'Barang masuk' : 'Barang keluar'),
        ]);

        // Update stok di tabel products
        $product->update(['quantity' => $newQuantity]);

        return back()->with('success', 'Stok produk berhasil diperbarui.');
    }


    /**
     * Lihat log perubahan stok produk
     */
    public function logs($product)
    {
        $products = Product::findOrFail($product);
        $logs = $products->quantityLogs()->latest()->get();

        return view('pages.products.logs', compact('products', 'logs'));
    }

}
