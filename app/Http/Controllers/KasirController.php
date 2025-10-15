<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;

class KasirController extends Controller
{
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
        $transaction_id = $request->input('transaction_id'); // dari data hold

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart kosong.'], 400);
        }

        // Hitung total
        $total = collect($cart)->sum(fn($item) => $item['qty'] * $item['price']);
        $totalDiskon = $diskon_barang + $diskon;
        $totalTagihan = max($total - $totalDiskon, 0);

        // 🟢 Jika ada transaction_id (dari hold), hapus dan buat baru
        if ($transaction_id) {
            $transaction = Transaction::find($transaction_id);

            if ($transaction) {
                $transaction->items()->delete();
                $transaction->delete();
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
        } else {
            // 🟢 Jika belum pernah di-hold, buat transaksi baru
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
        }

        // Simpan ulang item transaksi baru
        foreach ($cart as $item) {
            $discount = 0;
            if (!empty($item['discount']) && $item['discount'] > 0) {
                if (($item['discount_type'] ?? '') === 'percent') {
                    $discount = ($item['price'] * $item['discount'] / 100);
                } else {
                    $discount = $item['discount'];
                }
            }

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
        }

        return response()->json([
            'success' => true,
            'message' => $transaction_id ? 'Transaksi hold dihapus dan dibuat baru (completed).' : 'Transaksi baru berhasil dibayar.',
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
