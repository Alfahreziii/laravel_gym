<?php

namespace App\Http\Controllers;

use App\Models\AlatGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Controllers\Concerns\ExportsExcel;

class AlatGymController extends Controller
{
    use ExportsExcel;

    /**
     * Export data alat gym ke PDF
     */
    public function exportPdf()
    {
        try {
            $alatGyms = AlatGym::orderBy('nama_alat_gym', 'asc')->get();

            // Hitung statistik
            $totalAlat = $alatGyms->count();
            $totalJumlah = $alatGyms->sum('jumlah');
            $totalNilai = $alatGyms->sum(function($item) {
                return $item->harga * $item->jumlah;
            });

            // Statistik kondisi
            $kondisiBaik = $alatGyms->where('kondisi_alat', 'Baik')->count();
            $kondisiRusak = $alatGyms->where('kondisi_alat', 'Rusak')->count();
            $kondisiPerluPerbaikan = $alatGyms->where('kondisi_alat', 'Perlu Perbaikan')->count();

            $pdf = Pdf::loadView('pages.admin.alat-gym.pdf', compact(
                'alatGyms',
                'totalAlat',
                'totalJumlah',
                'totalNilai',
                'kondisiBaik',
                'kondisiRusak',
                'kondisiPerluPerbaikan'
            ));

            $pdf->setPaper('a4', 'landscape');
            
            $filename = 'Laporan_Alat_Gym_' . date('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Gagal export PDF alat gym', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export data alat gym ke Excel (.xls) — kolom sama seperti Laporan PDF
     * (plus kolom Keterangan karena di Excel tidak perlu halaman detail terpisah).
     */
    public function exportExcel()
    {
        try {
            $alatGyms = AlatGym::orderBy('nama_alat_gym', 'asc')->get();

            $totalAlat   = $alatGyms->count();
            $totalJumlah = $alatGyms->sum('jumlah');
            $totalNilai  = $alatGyms->sum(fn($item) => $item->harga * $item->jumlah);

            $kondisiBaik           = $alatGyms->where('kondisi_alat', 'Baik')->count();
            $kondisiRusak          = $alatGyms->where('kondisi_alat', 'Rusak')->count();
            $kondisiPerluPerbaikan = $alatGyms->where('kondisi_alat', 'Perlu Perbaikan')->count();

            $rows = '';
            foreach ($alatGyms as $i => $item) {
                $nilaiTotal = $item->harga * $item->jumlah;
                $tglBeli    = $item->tgl_pembelian ? Carbon::parse($item->tgl_pembelian)->format('d/m/Y') : '-';

                $rows .= '<tr>'
                    . '<td class="center">' . ($i + 1) . '</td>'
                    . '<td>' . $this->exEsc($item->barcode) . '</td>'
                    . '<td>' . $this->exEsc($item->nama_alat_gym) . '</td>'
                    . '<td class="center">' . $item->jumlah . '</td>'
                    . '<td class="num">Rp ' . $this->exNum($item->harga) . '</td>'
                    . '<td class="num">Rp ' . $this->exNum($nilaiTotal) . '</td>'
                    . '<td class="center">' . $tglBeli . '</td>'
                    . '<td>' . $this->exEsc($item->lokasi_alat ?? '-') . '</td>'
                    . '<td class="center">' . $this->exEsc($item->kondisi_alat ?? '-') . '</td>'
                    . '<td>' . $this->exEsc($item->vendor ?? '-') . '</td>'
                    . '<td>' . $this->exEsc($item->keterangan ?? '-') . '</td>'
                    . '</tr>';
            }

            $html = '<table>';
            $html .= '<tr><td colspan="11" class="title">Laporan Inventaris Alat Gym</td></tr>';
            $html .= '<tr><td colspan="11" class="subtitle">Dicetak: ' . now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') . ' WIB</td></tr>';
            $html .= '<tr><td colspan="11"></td></tr>';
            $html .= '<tr>'
                . '<td colspan="3" class="summary-label">Total Jenis Alat</td><td colspan="2" class="summary-val">' . $totalAlat . '</td>'
                . '<td colspan="3" class="summary-label">Total Unit</td><td colspan="3" class="summary-val">' . $this->exNum($totalJumlah) . '</td>'
                . '</tr>';
            $html .= '<tr>'
                . '<td colspan="3" class="summary-label">Total Nilai Aset</td><td colspan="2" class="summary-val">Rp ' . $this->exNum($totalNilai) . '</td>'
                . '<td colspan="3" class="summary-label">Baik / Perlu Perbaikan / Rusak</td><td colspan="3" class="summary-val">' . $kondisiBaik . ' / ' . $kondisiPerluPerbaikan . ' / ' . $kondisiRusak . '</td>'
                . '</tr>';
            $html .= '<tr><td colspan="11"></td></tr>';
            $html .= '<tr>'
                . '<th>No</th><th>Barcode</th><th>Nama Alat</th><th>Qty</th><th>Harga/Unit</th><th>Total Nilai</th>'
                . '<th>Tgl Beli</th><th>Lokasi</th><th>Kondisi</th><th>Vendor</th><th>Keterangan</th>'
                . '</tr>';
            $html .= $rows;
            $html .= '<tr class="grand-row">'
                . '<td colspan="3" class="center">TOTAL</td>'
                . '<td class="center">' . $this->exNum($totalJumlah) . '</td>'
                . '<td></td>'
                . '<td class="num">Rp ' . $this->exNum($totalNilai) . '</td>'
                . '<td colspan="5"></td>'
                . '</tr>';
            $html .= '</table>';

            $filename = 'Laporan_Alat_Gym_' . date('Y-m-d_His') . '.xls';

            return $this->excelDownload($html, 'Laporan Inventaris Alat Gym', $filename);
        } catch (\Exception $e) {
            Log::error('Gagal export Excel alat gym', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan semua data alat gym
     */
    public function index()
    {
        $alatGyms = AlatGym::latest()->get();
        return view('pages.admin.alat-gym.index', compact('alatGyms'));
    }

    /**
     * Form tambah data
     */
    public function create()
    {
        return view('pages.admin.alat-gym.create');
    }

    /**
     * Simpan data baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:50|unique:alat_gyms,barcode',
            'nama_alat_gym' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'tgl_pembelian' => 'nullable|date',
            'lokasi_alat' => 'nullable|string|max:100',
            'kondisi_alat' => 'nullable|string|max:50',
            'vendor' => 'nullable|string|max:100',
            'kontak' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        try {
            AlatGym::create($validated);
            return redirect()->route('alat_gym.index')->with('success', 'Data alat gym berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menambahkan data alat gym.');
        }
    }

    /**
     * Form edit data
     */
    public function edit(AlatGym $alatgym)
    {
        return view('pages.admin.alat-gym.edit', compact('alatgym'));
    }

    /**
     * Update data
     */
    public function update(Request $request, AlatGym $alatgym)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:50|unique:alat_gyms,barcode,' . $alatgym->id,
            'nama_alat_gym' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'tgl_pembelian' => 'nullable|date',
            'lokasi_alat' => 'nullable|string|max:100',
            'kondisi_alat' => 'nullable|string|max:50',
            'vendor' => 'nullable|string|max:100',
            'kontak' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $alatgym->update($validated);
            return redirect()->route('alat_gym.index')->with('success', 'Data alat gym berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('danger', 'Terjadi kesalahan saat memperbarui data alat gym.');
        }
    }

    /**
     * Hapus data
     */
    public function destroy(AlatGym $alatgym)
    {
        try {
            $alatgym->delete();
            return redirect()->route('alat_gym.index')->with('success', 'Data alat gym berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('alat_gym.index')->with('danger', 'Terjadi kesalahan saat menghapus data alat gym.');
        }
    }
}