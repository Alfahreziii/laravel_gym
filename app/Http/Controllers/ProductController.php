<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\KategoriProduct;
use App\Models\ProductQuantityLog;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function exportPdf()
    {
        try {
            // Ambil semua produk dengan kategori
            $products = Product::with('kategori')->get();
            
            // Hitung statistik
            $totalProduk = $products->count();
            
            // Total nilai stok (HPP × Quantity)
            $totalNilaiStok = $products->sum(function($product) {
                return $product->hpp * $product->quantity;
            });
            
            // Total nilai jual (Harga setelah diskon × Quantity)
            $totalNilaiJual = $products->sum(function($product) {
                $hargaSetelahDiskon = $product->price;
                
                if ($product->discount > 0) {
                    if ($product->discount_type == 'percent') {
                        $hargaSetelahDiskon = $product->price - ($product->price * $product->discount / 100);
                    } else {
                        $hargaSetelahDiskon = $product->price - $product->discount;
                    }
                }
                
                return $hargaSetelahDiskon * $product->quantity;
            });
            
            $produkAktif = $products->where('is_active', 1)->count();
            $produkNonaktif = $products->where('is_active', 0)->count();
            $totalStok = $products->sum('quantity');
            
            $title = 'Laporan Data Produk';
            
            $pdf = Pdf::loadView('pages.products.pdf', compact(
                'products',
                'totalProduk',
                'totalNilaiStok',
                'totalNilaiJual',
                'produkAktif',
                'produkNonaktif',
                'totalStok',
                'title'
            ));

            $pdf->setPaper('a4', 'landscape');
            
            $filename = 'Laporan_Produk_' . date('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Gagal export PDF produk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
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
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'barcode' => 'required|string|max:100|unique:products',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'price' => 'required|numeric|min:0',
                'hpp' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percent,nominal',
                'quantity' => 'integer|min:0',
                'reorder' => 'integer|min:0',
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

                // 🟢 CATAT TRANSAKSI KEUANGAN PEMBELIAN STOK AWAL
                $this->catatPembelianProduk(
                    $product,
                    $product->quantity,
                    'Pembelian stok awal produk: ' . $product->name
                );
            }

            DB::commit();
            return back()->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
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
                'hpp' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percent,nominal',
                'reorder' => 'integer|min:0',
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
        DB::beginTransaction();
        try {
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

            // 🟢 CATAT TRANSAKSI KEUANGAN
            if ($validated['type'] === 'in') {
                // Barang masuk = pembelian
                $this->catatPembelianProduk(
                    $product,
                    $validated['quantity'],
                    $validated['description'] ?? 'Pembelian barang masuk: ' . $product->name
                );
            } else {
                // Barang keluar = penyesuaian manual (tidak umum, tapi bisa dicatat)
                $this->catatPenguranganProduk(
                    $product,
                    $validated['quantity'],
                    $validated['description'] ?? 'Penyesuaian barang keluar: ' . $product->name
                );
            }

            DB::commit();
            return back()->with('success', 'Stok produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('danger', 'Gagal memperbarui stok: ' . $e->getMessage());
        }
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

    // =====================================================
    // FUNGSI HELPER UNTUK TRANSAKSI KEUANGAN
    // =====================================================

    /**
     * Catat transaksi keuangan pembelian produk
     * 
     * Jurnal:
     * Debit: Persediaan/Perlengkapan (AST004) = qty × HPP
     * Kredit: Kas (AST001) = qty × HPP
     */
    protected function catatPembelianProduk(Product $product, int $qty, string $deskripsi)
    {
        $akunPersediaan = AkunKeuangan::where('kode', 'AST004')->first(); // Persediaan
        $akunKas = AkunKeuangan::where('kode', 'AST001')->first(); // Kas

        if (!$akunPersediaan) {
            Log::warning('Akun Persediaan (AST004) tidak ditemukan');
            return;
        }
        if (!$akunKas) {
            Log::warning('Akun Kas (AST001) tidak ditemukan');
            return;
        }

        $nilaiPembelian = $product->hpp * $qty;
        $tanggal = now()->format('Y-m-d');

        // Debit Persediaan (aset bertambah)
        TransaksiKeuangan::create([
            'akun_id' => $akunPersediaan->id,
            'deskripsi' => $deskripsi,
            'debit' => $nilaiPembelian,
            'kredit' => 0,
            'tanggal' => $tanggal,
            'referensi_id' => $product->id,
            'referensi_tabel' => 'products',
        ]);

        // Kredit Kas (kas berkurang)
        TransaksiKeuangan::create([
            'akun_id' => $akunKas->id,
            'deskripsi' => $deskripsi,
            'debit' => 0,
            'kredit' => $nilaiPembelian,
            'tanggal' => $tanggal,
            'referensi_id' => $product->id,
            'referensi_tabel' => 'products',
        ]);

        Log::info('Transaksi keuangan pembelian produk tercatat', [
            'product_id' => $product->id,
            'quantity' => $qty,
            'nilai_pembelian' => $nilaiPembelian,
        ]);
    }

    /**
     * Catat pengurangan persediaan (untuk penyesuaian manual)
     * 
     * Jurnal:
     * Debit: Beban Lain-lain (BEB002) = qty × HPP
     * Kredit: Persediaan (AST004) = qty × HPP
     */
    protected function catatPenguranganProduk(Product $product, int $qty, string $deskripsi)
    {
        $akunPersediaan = AkunKeuangan::where('kode', 'AST004')->first();
        $akunBebanLain = AkunKeuangan::where('kode', 'BEB002')->first(); // Beban Lain-lain

        if (!$akunPersediaan || !$akunBebanLain) {
            Log::warning('Akun untuk pengurangan produk tidak ditemukan');
            return;
        }

        $nilaiPengurangan = $product->hpp * $qty;
        $tanggal = now()->format('Y-m-d');

        // Debit Beban Lain-lain
        TransaksiKeuangan::create([
            'akun_id' => $akunBebanLain->id,
            'deskripsi' => $deskripsi,
            'debit' => $nilaiPengurangan,
            'kredit' => 0,
            'tanggal' => $tanggal,
            'referensi_id' => $product->id,
            'referensi_tabel' => 'products',
        ]);

        // Kredit Persediaan
        TransaksiKeuangan::create([
            'akun_id' => $akunPersediaan->id,
            'deskripsi' => $deskripsi,
            'debit' => 0,
            'kredit' => $nilaiPengurangan,
            'tanggal' => $tanggal,
            'referensi_id' => $product->id,
            'referensi_tabel' => 'products',
        ]);
    }
}