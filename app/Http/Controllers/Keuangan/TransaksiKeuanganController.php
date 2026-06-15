<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;

use App\Models\TransaksiKeuangan;
use App\Models\AkunKeuangan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\ExportsExcel;

class TransaksiKeuanganController extends Controller
{
    use ExportsExcel;

    public function index()
    {
        $akuns = AkunKeuangan::with('kategori')->orderBy('kode')->get();
        return view('pages.admin.keuangan.transaksi.index', compact('akuns'));
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

            $query = TransaksiKeuangan::with(['akun.kategori'])
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc');

            $aggQuery = TransaksiKeuangan::query();

            $this->applyFilters($query, $akunId, $referensi, $tglMulai, $tglSelesai, $search);
            $this->applyFilters($aggQuery, $akunId, $referensi, $tglMulai, $tglSelesai, $search);

            $total       = $aggQuery->count();
            $totalDebit  = (clone $aggQuery)->sum('debit');
            $totalKredit = (clone $aggQuery)->sum('kredit');

            $data = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                    $ref = $this->resolveReferensi($item->referensi_tabel ?? '', $item->referensi_id ?? '');

                    // Enrich deskripsi: ganti fallback "dari Member" dengan nama anggota sebenarnya
                    $deskripsi = $item->deskripsi ?? '-';
                    if ($ref['nama_anggota'] && str_contains($deskripsi, ' dari Member')) {
                        $deskripsi = str_replace(' dari Member', ' dari ' . $ref['nama_anggota'], $deskripsi);
                    }

                    return [
                        'no'              => (($page - 1) * $perPage) + $index + 1,
                        'id'              => $item->id,
                        'tanggal'         => $item->tanggal
                            ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y')
                            : '-',
                        'kode_akun'       => $item->akun?->kode ?? '-',
                        'nama_akun'       => $item->akun?->nama ?? '-',
                        'kategori_akun'   => optional(optional($item->akun)->kategori)->nama ?? '-',
                        'deskripsi'       => $deskripsi,
                        'referensi_tabel' => $item->referensi_tabel ?? '-',
                        'referensi_id'    => $item->referensi_id ?? '-',
                        'kode_transaksi'  => $ref['kode_transaksi'],
                        'edit_url'        => $ref['edit_url'],
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
     * Resolve kode_transaksi dan URL edit berdasarkan referensi_tabel dan referensi_id.
     */
    private function resolveReferensi(string $tabel, $id): array
    {
        $empty = ['kode_transaksi' => null, 'edit_url' => null, 'nama_anggota' => null];

        if (!$tabel || !$id) {
            return $empty;
        }

        try {
            switch ($tabel) {
                case 'anggota_memberships':
                    $record = \App\Models\AnggotaMembership::with('anggota')->find($id);
                    if ($record) {
                        return [
                            'kode_transaksi' => $record->kode_transaksi,
                            'edit_url'       => route('anggota_membership.edit', $record->id),
                            'nama_anggota'   => $record->anggota->name ?? null,
                        ];
                    }
                    break;

                case 'pembayaran_memberships':
                    $record = \App\Models\PembayaranMembership::with('anggotaMembership.anggota')->find($id);
                    if ($record && $record->anggotaMembership) {
                        return [
                            'kode_transaksi' => $record->anggotaMembership->kode_transaksi,
                            'edit_url'       => route('anggota_membership.edit', $record->anggotaMembership->id),
                            'nama_anggota'   => $record->anggotaMembership->anggota->name ?? null,
                        ];
                    }
                    break;

                case 'member_trainers':
                    $record = \App\Models\MemberTrainer::with('anggota')->find($id);
                    if ($record) {
                        return [
                            'kode_transaksi' => $record->kode_transaksi,
                            'edit_url'       => route('membertrainer.edit', $record->id),
                            'nama_anggota'   => $record->anggota->name ?? null,
                        ];
                    }
                    break;

                case 'pembayaran_member_trainers':
                    $record = \App\Models\PembayaranMemberTrainer::with('memberTrainer.anggota')->find($id);
                    if ($record && $record->memberTrainer) {
                        return [
                            'kode_transaksi' => $record->memberTrainer->kode_transaksi,
                            'edit_url'       => route('membertrainer.edit', $record->memberTrainer->id),
                            'nama_anggota'   => $record->memberTrainer->anggota->name ?? null,
                        ];
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::warning('resolveReferensi gagal', [
                'tabel' => $tabel,
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
        }

        return $empty;
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

            $pdf = Pdf::loadView('pages.admin.keuangan.transaksi.pdf', compact(
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

    public function exportExcel(Request $request)
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
            $selisih     = $totalDebit - $totalKredit;

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

            $referensiMap = [
                'anggota_memberships' => 'Membership',
                'products' => 'Produk',
                'transaksi_spas' => 'Spa',
                'personal_trainers' => 'PT',
            ];

            $rows = '';
            foreach ($transaksis as $index => $t) {
                $kategoriNama = $t->akun?->kategori?->nama ?? '-';
                $sumber = ($referensiMap[$t->referensi_tabel] ?? $t->referensi_tabel) . ($t->referensi_id ? ' #' . $t->referensi_id : '');

                $rows .= '<tr>'
                    . '<td class="center">' . ($index + 1) . '</td>'
                    . '<td class="center">' . ($t->tanggal ? \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y') : '-') . '</td>'
                    . '<td class="center">' . $this->exEsc($t->akun?->kode ?? '-') . '</td>'
                    . '<td>' . $this->exEsc($t->akun?->nama ?? '-') . '</td>'
                    . '<td class="center">' . $this->exEsc($kategoriNama) . '</td>'
                    . '<td>' . $this->exEsc($t->deskripsi ?? '-') . '</td>'
                    . '<td>' . $this->exEsc($sumber ?: '-') . '</td>'
                    . '<td class="num">' . ($t->debit > 0 ? $this->exNum($t->debit) : '-') . '</td>'
                    . '<td class="num">' . ($t->kredit > 0 ? $this->exNum($t->kredit) : '-') . '</td>'
                    . '</tr>';
            }

            if ($transaksis->isEmpty()) {
                $rows = '<tr><td colspan="9" class="center">Tidak ada data transaksi</td></tr>';
            }

            $title = 'Laporan Transaksi Keuangan';

            $html = '<table>';
            $html .= '<tr><td colspan="9" class="title">' . $this->exEsc($title) . '</td></tr>';
            $subtitle = 'Dicetak: ' . now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') . ' WIB';
            if (count($filterLabel) > 0) {
                $subtitle .= ' | Filter: ' . implode(' | ', $filterLabel);
            }
            $html .= '<tr><td colspan="9" class="subtitle">' . $this->exEsc($subtitle) . '</td></tr>';
            $html .= '<tr><td colspan="9"></td></tr>';
            $html .= '<tr>'
                . '<td colspan="2" class="summary-label">Total Debit</td><td colspan="1" class="summary-val">Rp ' . $this->exNum($totalDebit) . '</td>'
                . '<td colspan="2" class="summary-label">Total Kredit</td><td colspan="1" class="summary-val">Rp ' . $this->exNum($totalKredit) . '</td>'
                . '<td colspan="2" class="summary-label">Selisih</td><td colspan="1" class="summary-val">Rp ' . $this->exNum(abs($selisih)) . '</td>'
                . '</tr>';
            $html .= '<tr><td colspan="9"></td></tr>';
            $html .= '<tr>'
                . '<th>No</th><th>Tanggal</th><th>Kode Akun</th><th>Nama Akun</th><th>Kategori</th>'
                . '<th>Deskripsi</th><th>Sumber</th><th>Debit (Rp)</th><th>Kredit (Rp)</th>'
                . '</tr>';
            $html .= $rows;
            if ($transaksis->count() > 0) {
                $html .= '<tr class="grand-row">'
                    . '<td colspan="7" class="center">TOTAL (' . $transaksis->count() . ' transaksi)</td>'
                    . '<td class="num">Rp ' . $this->exNum($totalDebit) . '</td>'
                    . '<td class="num">Rp ' . $this->exNum($totalKredit) . '</td>'
                    . '</tr>';
            }
            $html .= '</table>';

            $filename = 'Laporan_Transaksi_Keuangan_' . date('Y-m-d_His') . '.xls';

            return $this->excelDownload($html, $title, $filename);
        } catch (\Exception $e) {
            Log::error('Gagal export Excel transaksi keuangan', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }
}
