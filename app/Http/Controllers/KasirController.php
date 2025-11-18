<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ProductQuantityLog;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class KasirController extends Controller
{
    public function printNota($transactionId)
    {
        try {
            // 🟢 Hapus 'user' dari with()
            $transaction = Transaction::with(['items'])
                ->findOrFail($transactionId);

            // Data untuk PDF
            $data = [
                'transaction' => $transaction,
                'items' => $transaction->items,
                'tanggal' => Carbon::parse($transaction->transaction_date ?? $transaction->created_at)
                    ->locale('id')
                    ->isoFormat('dddd, D MMMM YYYY HH:mm'),
                'kasir' => Auth::user()->name ?? 'Kasir', // 🟢 Pakai user yang sedang login
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pages.kasir.nota-pdf', $data);
            
            // Ukuran kertas thermal 80mm x custom height
            $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait');

            $filename = 'Nota_' . $transaction->transaction_code . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Gagal generate nota PDF', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate nota: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:all,range',
            'tanggal_mulai' => 'nullable|required_if:filter_type,range|date',
            'tanggal_selesai' => 'nullable|required_if:filter_type,range|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            $filterType = $request->filter_type;
            
            // Hitung statistik dari SEMUA data (tidak terfilter)
            $allTransactions = Transaction::with('items')
                ->where('status', 'completed')
                ->get();
            
            $totalTransaksi = $allTransactions->count();
            $totalPendapatan = $allTransactions->sum('total_amount');
            $totalDiskonBarang = $allTransactions->sum('diskon_barang');
            $totalDiskonManual = $allTransactions->sum('diskon');
            $totalSebelumDiskon = $allTransactions->sum('harga_sebelum_diskon');
            
            // Hitung total HPP dari semua transaksi
            $totalHPP = 0;
            foreach ($allTransactions as $transaction) {
                foreach ($transaction->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $totalHPP += ($product->hpp * $item->qty);
                    }
                }
            }
            
            // Query untuk data yang akan ditampilkan
            $query = Transaction::with('items')
                ->where('status', 'completed');
            
            // Filter berdasarkan tanggal
            $filterInfo = '';
            if ($filterType === 'range') {
                $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
                $tanggalSelesai = Carbon::parse($request->tanggal_selesai)->endOfDay();
                
                $query->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai]);
                
                $filterInfo = $tanggalMulai->locale('id')->isoFormat('D MMMM YYYY') . ' - ' . 
                            $tanggalSelesai->locale('id')->isoFormat('D MMMM YYYY');
            } else {
                $filterInfo = 'Semua Periode';
            }
            
            $transactions = $query->orderBy('created_at', 'desc')->get();
            
            // Hitung statistik data yang terfilter
            $filteredTotalTransaksi = $transactions->count();
            $filteredTotalPendapatan = $transactions->sum('total_amount');
            $filteredTotalDiskonBarang = $transactions->sum('diskon_barang');
            $filteredTotalDiskonManual = $transactions->sum('diskon');
            $filteredTotalSebelumDiskon = $transactions->sum('harga_sebelum_diskon');
            
            // Hitung total HPP untuk data terfilter
            $filteredTotalHPP = 0;
            foreach ($transactions as $transaction) {
                foreach ($transaction->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $filteredTotalHPP += ($product->hpp * $item->qty);
                    }
                }
            }
            
            // Tambahkan HPP ke setiap transaction item untuk ditampilkan di PDF
            foreach ($transactions as $transaction) {
                foreach ($transaction->items as $item) {
                    $product = Product::find($item->product_id);
                    $item->hpp = $product ? $product->hpp : 0;
                    $item->total_hpp = $item->hpp * $item->qty;
                }
                
                // Hitung total HPP per transaksi
                $transaction->total_hpp_transaction = $transaction->items->sum('total_hpp');
            }
            
            // Buat title dinamis
            $title = 'Laporan Riwayat Penjualan';
            if ($filterType !== 'all') {
                $title .= ' - ' . $filterInfo;
            }

            $pdf = Pdf::loadView('pages.kasir.pdf', compact(
                'transactions',
                'totalTransaksi',
                'totalPendapatan',
                'totalDiskonBarang',
                'totalDiskonManual',
                'totalSebelumDiskon',
                'totalHPP',
                'filteredTotalTransaksi',
                'filteredTotalPendapatan',
                'filteredTotalDiskonBarang',
                'filteredTotalDiskonManual',
                'filteredTotalSebelumDiskon',
                'filteredTotalHPP',
                'title',
                'filterInfo',
                'filterType'
            ));

            $pdf->setPaper('a4', 'landscape');
            
            $filename = 'Laporan_Penjualan_' . date('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Gagal export PDF penjualan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

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

        DB::beginTransaction();
        try {
            $total = collect($cart)->sum(fn($item) => $item['qty'] * $item['price']);
            $totalDiskon = $diskon_barang + $diskon;
            $totalTagihan = max($total - $totalDiskon, 0);

            // Hapus transaksi hold jika ada
            if ($transaction_id) {
                $transaction = Transaction::find($transaction_id);
                if ($transaction) {
                    $transaction->items()->delete();
                    $transaction->delete();
                }
            }

            $transactionCode = 'TRX-' . strtoupper(Str::random(6));

            // Buat transaksi utama
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

            $totalHPP = 0;

            foreach ($cart as $item) {
                $discount = 0;
                if (!empty($item['discount']) && $item['discount'] > 0) {
                    $discount = ($item['discount_type'] ?? '') === 'percent'
                        ? ($item['price'] * $item['discount'] / 100)
                        : $item['discount'];
                }

                // Simpan item transaksi
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

                // Kurangi stok & akumulasi HPP
                $product = Product::find($item['id']);
                if ($product) {
                    $newQuantity = max($product->quantity - $item['qty'], 0);

                    ProductQuantityLog::create([
                        'product_id' => $product->id,
                        'type' => 'out',
                        'quantity' => $item['qty'],
                        'current_quantity' => $newQuantity,
                        'description' => 'Transaksi ' . $transactionCode,
                    ]);

                    $totalHPP += ($product->hpp * $item['qty']);

                    $product->update(['quantity' => $newQuantity]);
                }
            }

            // Nilai penerimaan bersih (yang masuk kas)
            $netBayar = max($dibayarkan - $kembalian, 0);

            // Catat transaksi keuangan dengan netBayar
            $this->catatTransaksiKeuanganPenjualan(
                $transaction,
                $netBayar,
                $totalHPP,
                $metode_pembayaran
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil, stok dan keuangan diperbarui.',
                'transaction_id' => $transaction->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan transaksi kasir', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
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

        DB::beginTransaction();
        try {
            $transactionCode = 'TRX-' . strtoupper(Str::random(6));
            $total = collect($cart)->sum(fn($item) => $item['qty'] * $item['price']);
            $totalDiskon = $diskon_barang + $diskon;
            $totalTagihan = max($total - $totalDiskon, 0);

            $transaction = Transaction::create([
                'transaction_code' => $transactionCode,
                'total_amount' => $totalTagihan,
                'harga_sebelum_diskon' => $total,
                'diskon' => $diskon,
                'diskon_barang' => $diskon_barang,
                'status' => 'hold',
            ]);

            foreach ($cart as $item) {
                $discount = 0;
                if (!empty($item['discount']) && $item['discount'] > 0) {
                    $discount = ($item['discount_type'] ?? '') === 'percent'
                        ? ($item['price'] * $item['discount'] / 100)
                        : $item['discount'];
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

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction_code' => $transactionCode,
                'transaction_id' => $transaction->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan transaksi hold', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi hold: ' . $e->getMessage()
            ], 500);
        }
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

    // =====================================================
    // FUNGSI HELPER UNTUK TRANSAKSI KEUANGAN KASIR
    // =====================================================

    /**
     * Catat transaksi keuangan untuk penjualan produk (berdasarkan uang diterima)
     *
     * Jurnal:
     * 1) PENERIMAAN KAS
     *    - Debit: Kas (AST001) = netBayar
     *    - Kredit: Pendapatan Penjualan (MOD005) = netBayar
     *
     * 2) HPP
     *    - Debit: Beban HPP (BEB001) = totalHPP
     *    - Kredit: Persediaan/Perlengkapan (AST004) = totalHPP
     */
    protected function catatTransaksiKeuanganPenjualan($transaction, float $netBayar, float $totalHPP, ?string $metodePembayaran)
    {
        $akunKas        = AkunKeuangan::where('kode', 'AST001')->first(); // Kas
        $akunPersediaan = AkunKeuangan::where('kode', 'AST004')->first(); // Persediaan/Perlengkapan
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD005')->first(); // Pendapatan Penjualan
        $akunHPP        = AkunKeuangan::where('kode', 'BEB001')->first(); // Beban HPP

        if (!$akunKas)        throw new \Exception('Akun Kas (AST001) tidak ditemukan.');
        if (!$akunPersediaan) throw new \Exception('Akun Persediaan (AST004) tidak ditemukan.');
        if (!$akunPendapatan) Log::warning('Akun Pendapatan Penjualan (MOD005) tidak ditemukan—baris kredit pendapatan dilewati.');
        if (!$akunHPP)        Log::warning('Akun Beban HPP (BEB001) tidak ditemukan—baris debit HPP dilewati.');

        $tanggal = now()->format('Y-m-d');
        $trxCode = $transaction->transaction_code;

        // 1) PENERIMAAN KAS (netBayar)
        if ($netBayar > 0) {
            // Debit Kas
            TransaksiKeuangan::create([
                'akun_id'         => $akunKas->id,
                'deskripsi'       => "Penjualan {$trxCode} via {$metodePembayaran}",
                'debit'           => $netBayar,
                'kredit'          => 0,
                'tanggal'         => $tanggal,
                'referensi_id'    => $transaction->id,
                'referensi_tabel' => 'transactions',
            ]);

            // Kredit Pendapatan
            if ($akunPendapatan) {
                TransaksiKeuangan::create([
                    'akun_id'         => $akunPendapatan->id,
                    'deskripsi'       => "Pendapatan penjualan {$trxCode}",
                    'debit'           => 0,
                    'kredit'          => $netBayar,
                    'tanggal'         => $tanggal,
                    'referensi_id'    => $transaction->id,
                    'referensi_tabel' => 'transactions',
                ]);
            }
        }

        // 2) HPP
        if ($totalHPP > 0) {
            if ($akunHPP) {
                TransaksiKeuangan::create([
                    'akun_id'         => $akunHPP->id,
                    'deskripsi'       => "HPP penjualan {$trxCode}",
                    'debit'           => $totalHPP,
                    'kredit'          => 0,
                    'tanggal'         => $tanggal,
                    'referensi_id'    => $transaction->id,
                    'referensi_tabel' => 'transactions',
                ]);
            }

            TransaksiKeuangan::create([
                'akun_id'         => $akunPersediaan->id,
                'deskripsi'       => "Pengurangan persediaan {$trxCode}",
                'debit'           => 0,
                'kredit'          => $totalHPP,
                'tanggal'         => $tanggal,
                'referensi_id'    => $transaction->id,
                'referensi_tabel' => 'transactions',
            ]);
        }

        Log::info('Transaksi keuangan penjualan tercatat', [
            'transaction_id'   => $transaction->id,
            'transaction_code' => $trxCode,
            'net_bayar'        => $netBayar,
            'total_hpp'        => $totalHPP,
            'metode_pembayaran'=> $metodePembayaran
        ]);
    }
}