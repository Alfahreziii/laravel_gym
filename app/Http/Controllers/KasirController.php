<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ProductQuantityLog;

class KasirController extends Controller
{
    public function riwayat()
    {
        $transactions = Transaction::with('items')
            ->where('status', 'completed')
            ->latest()
            ->get();
        return view('pages.kasir.riwayat', compact('transactions'));
    }

    public function index()
    {
        $products = Product::with('kategori')->get();
        return view('pages.kasir.index', compact('products'));
    }

    /**
     * Simpan transaksi dengan status 'completed'
     */
    public function bayar(Request $request)
    {
        $cart = $request->input('cart', []);
        $diskon = floatval($request->input('diskon', 0));
        $diskon_barang = floatval($request->input('diskon_barang', 0));
        $metode_pembayaran = $request->input('metode_pembayaran');
        $dibayarkan = floatval($request->input('dibayarkan', 0));
        $kembalian = floatval($request->input('kembalian', 0));
        $transaction_id = $request->input('transaction_id');

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart kosong.'], 400);
        }

        $total = collect($cart)->sum(fn($item) => $item['qty'] * $item['price']);
        $totalDiskon = $diskon_barang + $diskon;
        $totalTagihan = max($total - $totalDiskon, 0);

        if ($transaction_id) {
            $transaction = Transaction::find($transaction_id);
            if ($transaction) {
                $transaction->items()->delete();
                $transaction->delete();
            }
        }

        $transactionCode = 'TRX-' . strtoupper(Str::random(6));
        $transaction = Transaction::create([
            'transaction_code' => $transactionCode,
            'total_amount' => $totalTagihan,
            'harga_sebelum_diskon' => $total,
            'diskon' => $diskon,
            'diskon_barang' => $diskon_barang,
            'status' => 'completed',
            'dibayarkan' => $dibayarkan,
            'kembalian' => $kembalian,
            'metode_pembayaran' => $metode_pembayaran,
        ]);

        foreach ($cart as $item) {
            $discount = 0;
            if (!empty($item['discount']) && $item['discount'] > 0) {
                $discount = ($item['discount_type'] ?? '') === 'percent'
                    ? ($item['price'] * $item['discount'] / 100)
                    : $item['discount'];
            }

            // 🟢 Simpan item transaksi
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'image' => $item['image'] ?? null,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'kategori' => $item['kategori']['name'] ?? null,
                'diskon' => $discount,
            ]);

            // 🟠 Kurangi stok produk
            $product = Product::find($item['id']);
            if ($product) {
                $newQuantity = $product->quantity - $item['qty'];

                // Cegah stok minus
                if ($newQuantity < 0) $newQuantity = 0;

                // Simpan log stok keluar
                ProductQuantityLog::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item['qty'],
                    'current_quantity' => $newQuantity,
                    'description' => 'Transaksi ' . $transactionCode,
                ]);

                // Update stok produk
                $product->update(['quantity' => $newQuantity]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dan stok produk diperbarui.',
            'transaction_id' => $transaction->id,
        ]);
    }


    /**
     * Simpan transaksi dengan status 'hold'
     */
    public function hold(Request $request)
    {
        $cart = $request->input('cart', []);
        $diskon = floatval($request->input('diskon', 0));
        $diskon_barang = floatval($request->input('diskon_barang', 0));

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart kosong.'], 400);
        }

        $transactionCode = 'TRX-' . strtoupper(Str::random(6));

        // Hitung total amount
        $total = collect($cart)->sum(fn($item) => $item['qty'] * $item['price']);

        $totalDiskon = $diskon_barang + $diskon;
        $totalTagihan = max($total - $totalDiskon, 0);

        // Buat transaksi utama
        $transaction = Transaction::create([
            'transaction_code' => $transactionCode,
            'total_amount' => $totalTagihan,
            'harga_sebelum_diskon' => $total, // 🟢 simpan harga sebelum diskon
            'diskon' => $diskon, // 🟢 simpan diskon
            'diskon_barang' => $diskon_barang, // 🟢 simpan diskon barang
            'status' => 'hold',
        ]);

        foreach ($cart as $item) {
            // Hitung diskon nominal per item
            $discount = 0;
            if (!empty($item['discount']) && $item['discount'] > 0) {
                if (($item['discount_type'] ?? '') === 'percent') {
                    // Konversi persen ke nominal
                    $discount = ($item['price'] * $item['discount'] / 100);
                } else {
                    // Jika sudah nominal, langsung kalikan dengan qty
                    $discount = $item['discount'];
                }
            }

            // Simpan ke database
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'image' => $item['image'] ?? null,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'kategori' => $item['kategori']['name'] ?? null,
                'diskon' => $discount, // 🟢 simpan nilai diskon dalam bentuk nominal rupiah
            ]);
        }

        return response()->json([
            'success' => true,
            'transaction_code' => $transactionCode,
            'transaction_id' => $transaction->id,
        ]);
    }


    /**
     * Ambil semua transaksi yang di-hold
     */
    public function getHeldTransactions()
    {
        $heldTransactions = Transaction::with('items')
            ->where('status', 'hold')
            ->latest()
            ->get();

        return response()->json($heldTransactions);
    }
}
