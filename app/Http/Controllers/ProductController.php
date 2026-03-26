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

class ProductController extends Controller
{
    public function exportPdf()
    {
        try {
            $products = Product::with('kategori')->get();

            $totalProduk    = $products->count();
            $totalNilaiStok = $products->sum(fn($p) => $p->hpp * $p->quantity);
            $totalNilaiJual = $products->sum(function ($p) {
                $harga = $p->price;
                if ($p->discount > 0) {
                    $harga = $p->discount_type === 'percent'
                        ? $p->price - ($p->price * $p->discount / 100)
                        : $p->price - $p->discount;
                }
                return $harga * $p->quantity;
            });
            $produkAktif    = $products->where('is_active', 1)->count();
            $produkNonaktif = $products->where('is_active', 0)->count();
            $totalStok      = $products->sum('quantity');
            $title          = 'Laporan Data Produk';

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

            return $pdf->download('Laporan_Produk_' . date('Y-m-d_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export PDF produk', ['error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $products = Product::with('kategori')->latest()->get();
        return view('pages.products.index', compact('products'));
    }

    public function create()
    {
        $categories = KategoriProduct::all();
        return view('pages.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name'                => 'required|string|max:255',
                'barcode'             => 'required|string|max:100|unique:products',
                'description'         => 'nullable|string',
                'image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'price'               => 'required|numeric|min:0',
                'hpp'                 => 'required|numeric|min:0',
                'discount'            => 'nullable|numeric|min:0',
                'discount_type'       => 'nullable|in:percent,nominal',
                'quantity'            => 'integer|min:0',
                'reorder'             => 'integer|min:0',
                'is_active'           => 'boolean',
                'kategori_product_id' => 'required|exists:kategori_products,id',
            ]);

            $data = $validated;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);

            if ($product->quantity > 0) {
                ProductQuantityLog::create([
                    'product_id'       => $product->id,
                    'type'             => 'in',
                    'quantity'         => $product->quantity,
                    'current_quantity' => $product->quantity,
                    'description'      => 'Stok awal produk',
                ]);

                /*
                 * JURNAL: Pembelian stok awal
                 * Debit  AST004 (Persediaan Barang Dagang) → aset bertambah
                 * Kredit AST001 (Kas)                      → kas berkurang
                 */
                $this->jurnalPembelianStok(
                    $product,
                    $product->quantity,
                    'Pembelian stok awal: ' . $product->name
                );
            }

            DB::commit();
            return back()->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('danger', 'Gagal menambahkan produk: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Product $product)
    {
        $categories = KategoriProduct::all();
        return view('pages.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name'                => 'required|string|max:255',
                'barcode'             => 'required|string|max:100|unique:products,barcode,' . $product->id,
                'description'         => 'nullable|string',
                'image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'price'               => 'required|numeric|min:0',
                'hpp'                 => 'required|numeric|min:0',
                'discount'            => 'nullable|numeric|min:0',
                'discount_type'       => 'nullable|in:percent,nominal',
                'reorder'             => 'integer|min:0',
                'is_active'           => 'boolean',
                'kategori_product_id' => 'required|exists:kategori_products,id',
            ]);

            $data = $validated;

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            return back()->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('danger', 'Gagal memperbarui produk: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            /*
             * JURNAL: Produk dihapus — balik nilai persediaan yang masih ada
             * Debit  AST001 (Kas)                      → kas bertambah kembali
             * Kredit AST004 (Persediaan Barang Dagang) → aset berkurang
             *
             * Logika: produk dihapus = kita asumsikan persediaan dikembalikan / dibalik
             */
            if ($product->quantity > 0) {
                $this->jurnalHapusProduk(
                    $product,
                    $product->quantity,
                    'Produk dihapus (sisa stok ' . $product->quantity . '): ' . $product->name
                );
            }

            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('danger', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function adjustQuantity(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'type'        => 'required|in:in,out',
                'quantity'    => 'required|integer|min:1',
                'description' => 'nullable|string|max:255',
            ]);

            $change      = $validated['type'] === 'in' ? $validated['quantity'] : -$validated['quantity'];
            $newQuantity = $product->quantity + $change;

            if ($newQuantity < 0) {
                return back()->with('danger', 'Stok tidak boleh kurang dari 0.');
            }

            ProductQuantityLog::create([
                'product_id'       => $product->id,
                'type'             => $validated['type'],
                'quantity'         => $validated['quantity'],
                'current_quantity' => $newQuantity,
                'description'      => $validated['description'] ??
                    ($validated['type'] === 'in' ? 'Barang masuk' : 'Barang keluar'),
            ]);

            $product->update(['quantity' => $newQuantity]);

            if ($validated['type'] === 'in') {
                /*
                 * JURNAL: Tambah stok (barang masuk / restock)
                 * Debit  AST004 (Persediaan Barang Dagang) → aset bertambah
                 * Kredit AST001 (Kas)                      → kas berkurang
                 */
                $this->jurnalPembelianStok(
                    $product,
                    $validated['quantity'],
                    $validated['description'] ?? 'Restock barang masuk: ' . $product->name
                );
            } else {
                /*
                 * JURNAL: Kurangi stok manual (bukan dari penjualan — rusak/hilang/susut)
                 * Debit  BEB002 (Beban Kerugian Persediaan) → beban bertambah
                 * Kredit AST004 (Persediaan Barang Dagang)  → aset berkurang
                 *
                 * BUKAN BEB001 (HPP) karena HPP hanya diakui saat terjadi penjualan ke customer
                 */
                $this->jurnalKerugianPersediaan(
                    $product,
                    $validated['quantity'],
                    $validated['description'] ?? 'Penyesuaian stok keluar: ' . $product->name
                );
            }

            DB::commit();
            return back()->with('success', 'Stok produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('danger', 'Gagal memperbarui stok: ' . $e->getMessage());
        }
    }

    public function logs($product)
    {
        $products = Product::findOrFail($product);
        $logs     = $products->quantityLogs()->latest()->get();
        return view('pages.products.logs', compact('products', 'logs'));
    }

    // =========================================================
    // JURNAL HELPERS
    // =========================================================

    /**
     * Pembelian stok / barang masuk / stok awal
     *
     * Debit  : AST004 (Persediaan Barang Dagang) → aset naik
     * Kredit : AST001 (Kas)                      → kas turun
     */
    protected function jurnalPembelianStok(Product $product, int $qty, string $deskripsi): void
    {
        $persediaan = AkunKeuangan::where('kode', 'AST004')->first();
        $kas        = AkunKeuangan::where('kode', 'AST001')->first();

        if (!$persediaan || !$kas) {
            Log::warning('jurnalPembelianStok: AST004 atau AST001 tidak ditemukan');
            return;
        }

        $nilai   = $product->hpp * $qty;
        $tanggal = now()->format('Y-m-d');
        $ref     = ['referensi_id' => $product->id, 'referensi_tabel' => 'products'];

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $persediaan->id,
            'deskripsi' => $deskripsi,
            'debit'     => $nilai,
            'kredit'    => 0,
            'tanggal'   => $tanggal,
        ]));

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $kas->id,
            'deskripsi' => $deskripsi,
            'debit'     => 0,
            'kredit'    => $nilai,
            'tanggal'   => $tanggal,
        ]));
    }

    /**
     * Pengurangan stok manual (rusak / hilang / susut) — BUKAN dari penjualan
     *
     * Debit  : BEB002 (Beban Kerugian Persediaan) → beban naik
     * Kredit : AST004 (Persediaan Barang Dagang)  → aset turun
     */
    protected function jurnalKerugianPersediaan(Product $product, int $qty, string $deskripsi): void
    {
        $persediaan = AkunKeuangan::where('kode', 'AST004')->first();
        $beban      = AkunKeuangan::where('kode', 'BEB002')->first();

        if (!$persediaan || !$beban) {
            Log::warning('jurnalKerugianPersediaan: AST004 atau BEB002 tidak ditemukan');
            return;
        }

        $nilai   = $product->hpp * $qty;
        $tanggal = now()->format('Y-m-d');
        $ref     = ['referensi_id' => $product->id, 'referensi_tabel' => 'products'];

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $beban->id,
            'deskripsi' => $deskripsi,
            'debit'     => $nilai,
            'kredit'    => 0,
            'tanggal'   => $tanggal,
        ]));

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $persediaan->id,
            'deskripsi' => $deskripsi,
            'debit'     => 0,
            'kredit'    => $nilai,
            'tanggal'   => $tanggal,
        ]));
    }

    /**
     * Produk dihapus dari sistem — balik nilai persediaan
     *
     * Debit  : AST001 (Kas)                      → kas naik (balik)
     * Kredit : AST004 (Persediaan Barang Dagang) → aset turun
     */
    protected function jurnalHapusProduk(Product $product, int $qty, string $deskripsi): void
    {
        $persediaan = AkunKeuangan::where('kode', 'AST004')->first();
        $kas        = AkunKeuangan::where('kode', 'AST001')->first();

        if (!$persediaan || !$kas) {
            Log::warning('jurnalHapusProduk: AST004 atau AST001 tidak ditemukan');
            return;
        }

        $nilai   = $product->hpp * $qty;
        $tanggal = now()->format('Y-m-d');
        $ref     = ['referensi_id' => $product->id, 'referensi_tabel' => 'products'];

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $kas->id,
            'deskripsi' => $deskripsi,
            'debit'     => $nilai,
            'kredit'    => 0,
            'tanggal'   => $tanggal,
        ]));

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $persediaan->id,
            'deskripsi' => $deskripsi,
            'debit'     => 0,
            'kredit'    => $nilai,
            'tanggal'   => $tanggal,
        ]));
    }
}
