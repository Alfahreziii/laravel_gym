<?php

namespace App\Http\Controllers;

use App\Models\TransaksiKeuangan;
use App\Models\AkunKeuangan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TransaksiKeuanganController extends Controller
{
    public function index()
    {
        $akuns = AkunKeuangan::with('kategori')->orderBy('kode')->get();
        return view('pages.keuangan.transaksi.index', compact('akuns'));
    }

    public function datatable(Request $request)
    {
        try {
            $search     = $request->get('search', '');
            $perPage    = (int) $request->get('perPage', 15);
            $page       = (int) $request->get('page', 1);
            $akunId     = $request->get('akun_id', '');
            $referensi  = $request->get('referensi', '');
            $tglMulai   = $request->get('tgl_mulai', '');
            $tglSelesai = $request->get('tgl_selesai', '');

            // Query utama untuk data tabel (dengan eager load)
            $query = TransaksiKeuangan::with(['akun.kategori'])
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc');

            // Query aggregate TERPISAH — tanpa with() agar sum() bersih
            $aggQuery = TransaksiKeuangan::query();

            // Apply filter ke kedua query
            $this->applyFilters($query, $akunId, $referensi, $tglMulai, $tglSelesai, $search);
            $this->applyFilters($aggQuery, $akunId, $referensi, $tglMulai, $tglSelesai, $search);

            $total       = $aggQuery->count();
            $totalDebit  = (clone $aggQuery)->sum('debit');
            $totalKredit = (clone $aggQuery)->sum('kredit');

            $data = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'data'         => $data->map(function ($item, $index) use ($page, $perPage) {
                    return [
                        'no'              => (($page - 1) * $perPage) + $index + 1,
                        'id'              => $item->id,
                        'tanggal'         => $item->tanggal
                            ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y')
                            : '-',
                        'kode_akun'       => $item->akun?->kode ?? '-',
                        'nama_akun'       => $item->akun?->nama ?? '-',
                        'kategori_akun'   => optional(optional($item->akun)->kategori)->nama ?? '-',
                        'deskripsi'       => $item->deskripsi ?? '-',
                        'referensi_tabel' => $item->referensi_tabel ?? '-',
                        'referensi_id'    => $item->referensi_id ?? '-',
                        'debit'           => (float) ($item->debit ?? 0),
                        'kredit'          => (float) ($item->kredit ?? 0),
                    ];
                }),
                'total'        => $total,
                'perPage'      => $perPage,
                'page'         => $page,
                'lastPage'     => max(1, ceil($total / $perPage)),
                'total_debit'  => (float) $totalDebit,
                'total_kredit' => (float) $totalKredit,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => basename($e->getFile()),
            ], 500);
        }
    }

    /**
     * Apply filter conditions ke query builder
     */
    private function applyFilters($query, $akunId, $referensi, $tglMulai, $tglSelesai, $search): void
    {
        if ($akunId)    $query->where('akun_id', $akunId);
        if ($referensi) $query->where('referensi_tabel', $referensi);
        if ($tglMulai)  $query->whereDate('tanggal', '>=', $tglMulai);
        if ($tglSelesai) $query->whereDate('tanggal', '<=', $tglSelesai);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('akun', fn($q2) => $q2
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%"));
            });
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $akunId     = $request->get('akun_id', '');
            $referensi  = $request->get('referensi', '');
            $tglMulai   = $request->get('tgl_mulai', '');
            $tglSelesai = $request->get('tgl_selesai', '');

            $query = TransaksiKeuangan::with(['akun.kategori'])
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc');

            $this->applyFilters($query, $akunId, $referensi, $tglMulai, $tglSelesai, '');

            $transaksis  = $query->get();
            $totalDebit  = $transaksis->sum('debit');
            $totalKredit = $transaksis->sum('kredit');

            $filterLabel = [];
            if ($tglMulai || $tglSelesai) {
                $filterLabel[] = 'Periode: ' . ($tglMulai ?: '...') . ' s/d ' . ($tglSelesai ?: '...');
            }
            if ($akunId) {
                $akun = AkunKeuangan::find($akunId);
                if ($akun) $filterLabel[] = 'Akun: ' . $akun->kode . ' - ' . $akun->nama;
            }
            if ($referensi) {
                $filterLabel[] = 'Sumber: ' . ucfirst(str_replace('_', ' ', $referensi));
            }

            $pdf = Pdf::loadView('pages.keuangan.transaksi.pdf', compact(
                'transaksis',
                'totalDebit',
                'totalKredit',
                'filterLabel'
            ));
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('Laporan_Transaksi_Keuangan_' . date('Y-m-d_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export PDF transaksi keuangan', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
}
