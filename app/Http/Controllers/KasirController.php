<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\HeldTransaction;
use Illuminate\Http\Request;
use App\Models\Product;

class KasirController extends Controller
{
    public function index()
    {
        $products = Product::with('kategori')->get();
        return view('pages.kasir.index', compact('products'));
    }

    public function hold(Request $request)
    {
        $cart = $request->input('cart'); // array of items

        $transactionCode = 'HOLD-' . strtoupper(Str::random(6));

        $held = HeldTransaction::create([
            'transaction_code' => $transactionCode,
            'cart_data' => json_encode($cart),
        ]);

        return response()->json([
            'success' => true,
            'transaction_code' => $transactionCode,
            'held_id' => $held->id,
        ]);
    }

    public function resumeHold($id)
    {
        $hold = HeldTransaction::findOrFail($id);
        $cart = json_decode($hold->cart_data, true);

        session()->put('cart', $cart);
        $hold->delete(); // hapus dari hold setelah di-load

        return back()->with('success', 'Transaksi berhasil dilanjutkan');
    }

    public function getHeldTransactions()
    {
        $heldTransactions = HeldTransaction::latest()->get();
        return response()->json($heldTransactions);
    }
}
