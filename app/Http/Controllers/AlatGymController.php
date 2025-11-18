<?php

namespace App\Http\Controllers;

use App\Models\AlatGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AlatGymController extends Controller
{
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

            $pdf = Pdf::loadView('pages.alatgym.pdf', compact(
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
     * Tampilkan semua data alat gym
     */
    public function index()
    {
        $alatGyms = AlatGym::latest()->get();
        return view('pages.alatgym.index', compact('alatGyms'));
    }

    /**
     * Form tambah data
     */
    public function create()
    {
        return view('pages.alatgym.create');
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
        return view('pages.alatgym.edit', compact('alatgym'));
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