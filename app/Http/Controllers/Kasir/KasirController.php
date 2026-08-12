<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\KategoriProduct;
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
                'tanggal' => to_tenant_tz($transaction->transaction_date ?? $transaction->created_at)
                    ->locale('id')
                    ->isoFormat('dddd, D MMMM YYYY HH:mm'),
                'kasir' => Auth::user()->name ?? 'Kasir', // 🟢 Pakai user yang sedang login
                'tenant' => app('tenant'),
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

            // Cek jumlah transaksi sebelum proses — cegah memory exhausted
            $countQuery = Transaction::where('status', 'completed');
            if ($filterType === 'range') {
                $countQuery->whereBetween('created_at', [
                    Carbon::parse($request->tanggal_mulai)->startOfDay(),
                    Carbon::parse($request->tanggal_selesai)->endOfDay(),
                ]);
            }
            $jumlahTransaksi = $countQuery->count();

            if ($jumlahTransaksi > 300) {
                return redirect()->back()->with('danger',
                    "Data terlalu banyak untuk di-export ke PDF ({$jumlahTransaksi} transaksi). " .
                    "Maksimal 300 transaksi per export PDF. " .
                    "Gunakan filter range tanggal yang lebih pendek, atau gunakan tombol Export Excel untuk data sebanyak ini."
                );
            }

            // Load semua HPP produk sekaligus — hindari N+1 query
            $productHppMap = Product::pluck('hpp', 'id')
                ->map(fn($hpp) => (float) ($hpp ?? 0));

            // Hitung statistik dari SEMUA data (tidak terfilter)
            $allTransactions = Transaction::with('items')
                ->where('status', 'completed')
                ->get();

            $totalTransaksi    = $allTransactions->count();
            $totalPendapatan   = $allTransactions->sum('total_amount');
            $totalDiskonBarang = $allTransactions->sum('diskon_barang');
            $totalDiskonManual = $allTransactions->sum('diskon');
            $totalSebelumDiskon = $allTransactions->sum('harga_sebelum_diskon');

            $totalHPP = 0;
            foreach ($allTransactions as $t) {
                foreach ($t->items as $item) {
                    $totalHPP += $productHppMap->get($item->product_id, 0) * $item->qty;
                }
            }

            // Query untuk data yang akan ditampilkan
            $query = Transaction::with('items')->where('status', 'completed');

            if ($filterType === 'range') {
                $tanggalMulai   = Carbon::parse($request->tanggal_mulai)->startOfDay();
                $tanggalSelesai = Carbon::parse($request->tanggal_selesai)->endOfDay();
                $query->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai]);
            }

            $allFiltered = $query->orderBy('created_at', 'asc')->get();

            // Jika filter range dan datanya sedikit (≤150 transaksi), export satu PDF langsung
            if ($filterType === 'range' && $allFiltered->count() <= 150) {
                return $this->generateSinglePdf(
                    $allFiltered, $productHppMap,
                    $totalTransaksi, $totalPendapatan, $totalDiskonBarang,
                    $totalDiskonManual, $totalSebelumDiskon, $totalHPP,
                    $tanggalMulai->locale('id')->isoFormat('D MMMM YYYY') . ' - ' .
                    $tanggalSelesai->locale('id')->isoFormat('D MMMM YYYY'),
                    $filterType
                );
            }

            // Untuk "semua tanggal" atau range besar: pecah per bulan → ZIP
            $grouped = $allFiltered->groupBy(fn($t) => to_tenant_tz($t->created_at)->format('Y-m'));

            $zipPath = storage_path('app/temp_export_' . time() . '.zip');
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Tidak bisa membuat file ZIP.');
            }

            foreach ($grouped as $yearMonth => $monthTransactions) {
                $bulan      = Carbon::createFromFormat('Y-m', $yearMonth)->locale('id')->isoFormat('MMMM YYYY');
                $filterInfo = $bulan;

                $filteredTotalTransaksi    = $monthTransactions->count();
                $filteredTotalPendapatan   = $monthTransactions->sum('total_amount');
                $filteredTotalDiskonBarang = $monthTransactions->sum('diskon_barang');
                $filteredTotalDiskonManual = $monthTransactions->sum('diskon');
                $filteredTotalSebelumDiskon = $monthTransactions->sum('harga_sebelum_diskon');
                $filteredTotalHPP = 0;

                foreach ($monthTransactions as $transaction) {
                    foreach ($transaction->items as $item) {
                        $hpp = $productHppMap->get($item->product_id, 0);
                        $item->hpp       = $hpp;
                        $item->total_hpp = $hpp * $item->qty;
                        $filteredTotalHPP += $item->total_hpp;
                    }
                    $transaction->total_hpp_transaction = $transaction->items->sum('total_hpp');
                }

                $transactions = $monthTransactions->sortByDesc('created_at')->values();
                $title        = 'Laporan Penjualan - ' . $bulan;
                $filterType   = 'range';

                $pdf = Pdf::loadView('pages.kasir.pdf', compact(
                    'transactions', 'title', 'filterInfo', 'filterType',
                    'totalTransaksi', 'totalPendapatan', 'totalDiskonBarang',
                    'totalDiskonManual', 'totalSebelumDiskon', 'totalHPP',
                    'filteredTotalTransaksi', 'filteredTotalPendapatan',
                    'filteredTotalDiskonBarang', 'filteredTotalDiskonManual',
                    'filteredTotalSebelumDiskon', 'filteredTotalHPP'
                ))->setPaper('a4', 'landscape');

                $zip->addFromString(
                    'Laporan_Penjualan_' . $yearMonth . '.pdf',
                    $pdf->output()
                );

                // Bebaskan memory setelah tiap bulan
                unset($pdf);
                gc_collect_cycles();
            }

            $zip->close();

            return response()->download($zipPath, 'Laporan_Penjualan_' . date('Y-m-d') . '.zip')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Gagal export PDF penjualan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate single PDF (untuk range kecil ≤150 transaksi)
     */
    private function generateSinglePdf(
        $transactions, $productHppMap,
        $totalTransaksi, $totalPendapatan, $totalDiskonBarang,
        $totalDiskonManual, $totalSebelumDiskon, $totalHPP,
        $filterInfo, $filterType
    ) {
        $filteredTotalTransaksi    = $transactions->count();
        $filteredTotalPendapatan   = $transactions->sum('total_amount');
        $filteredTotalDiskonBarang = $transactions->sum('diskon_barang');
        $filteredTotalDiskonManual = $transactions->sum('diskon');
        $filteredTotalSebelumDiskon = $transactions->sum('harga_sebelum_diskon');
        $filteredTotalHPP = 0;

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $hpp = $productHppMap->get($item->product_id, 0);
                $item->hpp       = $hpp;
                $item->total_hpp = $hpp * $item->qty;
                $filteredTotalHPP += $item->total_hpp;
            }
            $transaction->total_hpp_transaction = $transaction->items->sum('total_hpp');
        }

        $transactions = $transactions->sortByDesc('created_at')->values();
        $title        = 'Laporan Riwayat Penjualan - ' . $filterInfo;

        $pdf = Pdf::loadView('pages.kasir.pdf', compact(
            'transactions', 'title', 'filterInfo', 'filterType',
            'totalTransaksi', 'totalPendapatan', 'totalDiskonBarang',
            'totalDiskonManual', 'totalSebelumDiskon', 'totalHPP',
            'filteredTotalTransaksi', 'filteredTotalPendapatan',
            'filteredTotalDiskonBarang', 'filteredTotalDiskonManual',
            'filteredTotalSebelumDiskon', 'filteredTotalHPP'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Penjualan_' . date('Y-m-d_His') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $request->validate([
            'filter_type'     => 'required|in:all,range',
            'tanggal_mulai'   => 'nullable|required_if:filter_type,range|date',
            'tanggal_selesai' => 'nullable|required_if:filter_type,range|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            $productHppMap = Product::pluck('hpp', 'id')
                ->map(fn($hpp) => (float) ($hpp ?? 0));

            $query = Transaction::with('items')->where('status', 'completed');

            $filterLabel = 'Semua Periode';
            $filterSlug  = 'Semua_Periode';
            if ($request->filter_type === 'range') {
                $mulai   = Carbon::parse($request->tanggal_mulai)->startOfDay();
                $selesai = Carbon::parse($request->tanggal_selesai)->endOfDay();
                $query->whereBetween('created_at', [$mulai, $selesai]);
                $filterLabel = $mulai->locale('id')->isoFormat('D MMMM YYYY') . ' - ' . $selesai->locale('id')->isoFormat('D MMMM YYYY');
                $filterSlug  = $mulai->format('Y-m-d') . '_sd_' . $selesai->format('Y-m-d');
            }

            $transactions = $query->orderBy('created_at', 'asc')->get();

            // Hitung total keseluruhan
            $grandTotalHPP = 0;
            foreach ($transactions as $trx) {
                foreach ($trx->items as $item) {
                    $grandTotalHPP += $productHppMap->get($item->product_id, 0) * $item->qty;
                }
            }
            $grandTotal        = $transactions->sum('total_amount');
            $grandDiskonBarang = $transactions->sum('diskon_barang');
            $grandDiskonManual = $transactions->sum('diskon');
            $grandDibayarkan   = $transactions->sum('dibayarkan');
            $grandKembalian    = $transactions->sum('kembalian');
            $grandSblDiskon    = $transactions->sum('harga_sebelum_diskon');
            $grandLaba         = $grandTotal - $grandTotalHPP;

            $filename = 'Laporan_Penjualan_' . $filterSlug . '.xls';

            $headers = [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $fmt = fn($n) => number_format((float)$n, 0, ',', '.');

            $callback = function () use (
                $transactions, $productHppMap, $filterLabel,
                $grandTotal, $grandTotalHPP, $grandLaba,
                $grandDiskonBarang, $grandDiskonManual,
                $grandDibayarkan, $grandKembalian, $grandSblDiskon, $fmt
            ) {
                echo chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM UTF-8
                ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head><meta charset="UTF-8">
<style>
  body { font-family: Arial; font-size: 10pt; }
  table { border-collapse: collapse; width: 100%; }
  th {
    background-color: #2c3e50; color: #ffffff;
    border: 1px solid #999; padding: 6px 8px;
    text-align: center; font-size: 10pt;
    white-space: nowrap;
  }
  td {
    border: 1px solid #ccc; padding: 5px 8px;
    font-size: 10pt; vertical-align: middle;
  }
  tr:nth-child(even) td { background-color: #f2f2f2; }
  .num { text-align: right; mso-number-format:'\#\,\#\#0'; }
  .center { text-align: center; }
  .title { font-size: 14pt; font-weight: bold; }
  .subtitle { font-size: 10pt; color: #555; }
  .summary-label { font-weight: bold; background-color: #ecf0f1; }
  .summary-val { text-align: right; font-weight: bold; color: #27ae60; }
  .grand-row td { background-color: #2c3e50; color: #fff; font-weight: bold; }
  .grand-row .num { text-align: right; }
</style>
</head>
<body>
<table>
  <tr><td colspan="13" class="title">Laporan Riwayat Penjualan</td></tr>
  <tr><td colspan="13" class="subtitle">Periode: <?= htmlspecialchars($filterLabel) ?> &nbsp;|&nbsp; Dicetak: <?= tenant_now()->locale('id')->isoFormat('D MMMM YYYY HH:mm') ?> <?= tz_label() ?></td></tr>
  <tr><td colspan="13"></td></tr>

  <!-- Ringkasan -->
  <tr>
    <td colspan="3" class="summary-label">Total Transaksi</td>
    <td colspan="3" class="summary-val"><?= $transactions->count() ?> transaksi</td>
    <td colspan="3" class="summary-label">Total Pendapatan Bersih</td>
    <td colspan="5" class="summary-val">Rp <?= $fmt($grandTotal) ?></td>
  </tr>
  <tr>
    <td colspan="3" class="summary-label">Total HPP</td>
    <td colspan="3" class="summary-val">Rp <?= $fmt($grandTotalHPP) ?></td>
    <td colspan="3" class="summary-label">Laba Kotor</td>
    <td colspan="5" class="summary-val">Rp <?= $fmt($grandLaba) ?></td>
  </tr>
  <tr><td colspan="13"></td></tr>

  <!-- Header tabel -->
  <tr>
    <th>No</th>
    <th>Kode Transaksi</th>
    <th>Nama Pelanggan</th>
    <th>Tanggal</th>
    <th>Metode</th>
    <th>Harga Sbl Diskon</th>
    <th>Total Diskon</th>
    <th>Total Tagihan</th>
    <th>Dibayarkan</th>
    <th>Kembalian</th>
    <th>Total HPP</th>
    <th>Laba Kotor</th>
    <th>Detail Item</th>
  </tr>

<?php
                $no = 1;
                foreach ($transactions as $trx) {
                    $hppTrx = 0;
                    foreach ($trx->items as $item) {
                        $hppTrx += $productHppMap->get($item->product_id, 0) * $item->qty;
                    }
                    $totalDiskon = $trx->diskon_barang + $trx->diskon;
                    $labaKotor   = $trx->total_amount - $hppTrx;
                    $detailItem  = $trx->items->map(function ($it) {
                        $subtotal = ($it->price * $it->qty) - ($it->diskon ?? 0);
                        return $it->product_name . ' x' . $it->qty . ' (Rp' . number_format($subtotal, 0, ',', '.') . ')';
                    })->implode(', ');
                    echo '<tr>';
                    echo '<td class="center">' . $no++ . '</td>';
                    echo '<td>' . htmlspecialchars($trx->transaction_code) . '</td>';
                    echo '<td>' . htmlspecialchars($trx->customer_name ?? '-') . '</td>';
                    echo '<td class="center">' . to_tenant_tz($trx->created_at)->format('d/m/Y H:i') . '</td>';
                    echo '<td class="center">' . htmlspecialchars($trx->metode_pembayaran ?? '-') . '</td>';
                    echo '<td class="num">Rp ' . $fmt($trx->harga_sebelum_diskon) . '</td>';
                    echo '<td class="num">Rp ' . $fmt($totalDiskon) . '</td>';
                    echo '<td class="num">Rp ' . $fmt($trx->total_amount) . '</td>';
                    echo '<td class="num">Rp ' . $fmt($trx->dibayarkan) . '</td>';
                    echo '<td class="num">Rp ' . $fmt($trx->kembalian) . '</td>';
                    echo '<td class="num">Rp ' . $fmt($hppTrx) . '</td>';
                    echo '<td class="num">Rp ' . $fmt($labaKotor) . '</td>';
                    echo '<td>' . (htmlspecialchars($detailItem) ?: '-') . '</td>';
                    echo '</tr>';
                }
?>

  <!-- Grand total row -->
  <tr class="grand-row">
    <td colspan="5" style="text-align:center;">GRAND TOTAL</td>
    <td class="num">Rp <?= $fmt($grandSblDiskon) ?></td>
    <td class="num">Rp <?= $fmt($grandDiskonBarang + $grandDiskonManual) ?></td>
    <td class="num">Rp <?= $fmt($grandTotal) ?></td>
    <td class="num">Rp <?= $fmt($grandDibayarkan) ?></td>
    <td class="num">Rp <?= $fmt($grandKembalian) ?></td>
    <td class="num">Rp <?= $fmt($grandTotalHPP) ?></td>
    <td class="num">Rp <?= $fmt($grandLaba) ?></td>
    <td></td>
  </tr>
</table>
</body></html>
<?php
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Gagal export Excel penjualan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export Excel: ' . $e->getMessage());
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

    public function datatableRiwayat(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $productHppMap = Product::pluck('hpp', 'id')->map(fn($v) => (float) ($v ?? 0));

        $query = Transaction::with('items')->where('status', 'completed')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('metode_pembayaran', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage, $productHppMap) {
                $totalHPP = $item->items->sum(fn($t) => ($productHppMap[$t->product_id] ?? 0) * $t->qty);
                return [
                    'no'                   => (($page - 1) * $perPage) + $index + 1,
                    'id'                   => $item->id,
                    'kode_transaksi'       => $item->transaction_code,
                    'customer_name'        => $item->customer_name ?? '-',
                    'tanggal'              => to_tenant_tz($item->created_at)->format('d M Y'),
                    'total_amount'         => 'Rp ' . number_format($item->total_amount, 0, ',', '.'),
                    'dibayarkan'           => 'Rp ' . number_format($item->dibayarkan, 0, ',', '.'),
                    'kembalian'            => 'Rp ' . number_format($item->kembalian, 0, ',', '.'),
                    'metode_pembayaran'    => $item->metode_pembayaran ?? '-',
                    'harga_sebelum_diskon' => 'Rp ' . number_format($item->harga_sebelum_diskon, 0, ',', '.'),
                    'diskon_barang'        => 'Rp ' . number_format($item->diskon_barang, 0, ',', '.'),
                    'diskon'               => 'Rp ' . number_format($item->diskon, 0, ',', '.'),
                    'total_hpp'            => 'Rp ' . number_format($totalHPP, 0, ',', '.'),
                    'items_json'           => $item->items->map(fn($t) => [
                        'product_name' => $t->product_name,
                        'keterangan'   => $t->keterangan,
                        'qty'          => $t->qty,
                        'price'        => (float) $t->price,
                        'hpp'          => $productHppMap[$t->product_id] ?? 0,
                        'diskon'       => (float) ($t->diskon ?? 0),
                    ])->values()->toArray(),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function index()
    {
        $kategoris = KategoriProduct::orderBy('name')->get();
        return view('pages.kasir.index', compact('kategoris'));
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
        $customer_name = $request->input('customer_name');

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
                'customer_name' => $customer_name ?: 'Customer',
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
                    'keterangan' => $item['keterangan'] ?? null,
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
        $cart             = $request->input('cart', []);
        $diskon           = floatval($request->input('diskon', 0));
        $diskon_barang    = floatval($request->input('diskon_barang', 0));
        $oldTransactionId = $request->input('old_transaction_id');
        $customer_name = $request->input('customer_name');

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart kosong.'], 400);
        }

        DB::beginTransaction();
        try {
            // Ambil kode lama SEBELUM dihapus
            $transactionCode = null;

            if ($oldTransactionId) {
                $oldTransaction = Transaction::where('id', $oldTransactionId)
                    ->where('status', 'hold')
                    ->first();

                if ($oldTransaction) {
                    $transactionCode = $oldTransaction->transaction_code; // ← simpan kode lama
                    $oldTransaction->items()->delete();
                    $oldTransaction->delete();
                }
            }

            // Jika tidak ada hold lama, buat kode baru
            if (!$transactionCode) {
                $transactionCode = 'TRX-' . strtoupper(Str::random(6));
            }

            $total        = collect($cart)->sum(fn($item) => $item['qty'] * $item['price']);
            $totalDiskon  = $diskon_barang + $diskon;
            $totalTagihan = max($total - $totalDiskon, 0);

            $transaction = Transaction::create([
                'transaction_code'     => $transactionCode, // ← pakai kode yang dipertahankan
                'customer_name' => $customer_name ?: 'Customer',
                'total_amount'         => $totalTagihan,
                'harga_sebelum_diskon' => $total,
                'diskon'               => $diskon,
                'diskon_barang'        => $diskon_barang,
                'status'               => 'hold',
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
                    'image'          => $item['image'] ?? null,
                    'product_id'     => $item['id'],
                    'product_name'   => $item['name'],
                    'qty'            => $item['qty'],
                    'price'          => $item['price'],
                    'kategori'       => $item['kategori']['name'] ?? null,
                    'keterangan' => $item['keterangan'] ?? null,
                    'diskon'         => $discount,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'          => true,
                'transaction_code' => $transactionCode,
                'transaction_id'   => $transaction->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan transaksi hold', ['error' => $e->getMessage()]);

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

        $tanggal = tenant_today_date();
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
            'metode_pembayaran' => $metodePembayaran
        ]);
    }

    /**
     * AJAX endpoint untuk grid produk kasir (pagination + filter)
     */
    public function productsGrid(Request $request)
    {
        $search   = $request->get('search', '');
        $kategori = $request->get('kategori', '');
        $perPage  = 24;
        $page     = max(1, (int) $request->get('page', 1));

        $query = Product::with('kategori')->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($kategori) {
            $query->where('kategori_product_id', $kategori);
        }

        $total    = (clone $query)->count();
        $products = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $products->map(fn($p) => [
                'id'                  => $p->id,
                'name'                => $p->name,
                'price'               => (float) $p->price,
                'discount'            => (float) ($p->discount ?? 0),
                'discount_type'       => $p->discount_type ?? '',
                'image'               => $p->image,
                'kategori_product_id' => $p->kategori_product_id,
                'kategori_name'       => $p->kategori->name ?? '',
                'quantity'            => (int) $p->quantity,
                'is_active'           => (bool) $p->is_active,
            ]),
            'page'     => $page,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
            'total'    => $total,
            'perPage'  => $perPage,
        ]);
    }

    /**
     * Datatable AJAX untuk transaksi yang di-hold (Hold Items modal)
     */
    public function holdItemsDatatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = Transaction::with('items')
            ->where('status', 'hold')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($transaction, $index) use ($page, $perPage) {
                $totalQty      = $transaction->items->sum('qty');
                $hasKeterangan = $transaction->items->contains(fn($it) => !empty($it->keterangan));

                return [
                    'no'                   => (($page - 1) * $perPage) + $index + 1,
                    'id'                   => $transaction->id,
                    'kode_transaksi'       => $transaction->transaction_code,
                    'customer_name'        => $transaction->customer_name ?? '-',
                    'customer_name_raw'    => $transaction->customer_name ?? '',
                    'qty'                  => $totalQty,
                    'harga_sebelum_diskon' => $transaction->harga_sebelum_diskon,
                    'total_amount'         => $transaction->total_amount,
                    'diskon'               => $transaction->diskon,
                    'diskon_barang'        => $transaction->diskon_barang,
                    'keterangan_flag'      => $hasKeterangan,
                    'created_at'           => to_tenant_tz($transaction->created_at)->format('d M Y H:i'),
                    'items_json'           => $transaction->items->map(fn($it) => [
                        'product_id'   => $it->product_id,
                        'product_name' => $it->product_name,
                        'qty'          => $it->qty,
                        'price'        => (float) $it->price,
                        'diskon'       => (float) ($it->diskon ?? 0),
                        'keterangan'   => $it->keterangan,
                        'kategori'     => $it->kategori,
                        'image'        => $it->image,
                    ])->values()->toArray(),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Hapus transaksi hold berdasarkan ID
     */
    public function deleteHold($id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::where('id', $id)
                ->where('status', 'hold')
                ->firstOrFail();

            $transaction->items()->delete();
            $transaction->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi hold berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus transaksi hold', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
