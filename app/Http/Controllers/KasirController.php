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
     * Simpan transaksi dengan status 'hold'
     */
    public function hold(Request $request)
    {
        $cart = $request->input('cart', []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart kosong.'], 400);
        }

        $transactionCode = 'TRX-' . strtoupper(Str::random(6));

        // Hitung total amount
        $total = collect($cart)->sum(function ($item) {
            return $item['qty'] * $item['price'];
        });

        // Buat transaksi utama
        $transaction = Transaction::create([
            'transaction_code' => $transactionCode,
            'total_amount' => $total,
            'status' => 'hold',
        ]);

        // Simpan item-item transaksi
        foreach ($cart as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['qty'] * $item['price'],
                'kategori' => $item['kategori']['name'] ?? null,
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
