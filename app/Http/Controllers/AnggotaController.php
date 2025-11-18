<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AnggotaController extends Controller
{
    /**
     * Tampilkan semua data anggota
     */
    public function index()
    {
        $anggotas = Anggota::all(); // bisa pakai paginate jika banyak
        return view('pages.anggota.index', compact('anggotas'));
    }

    /**
     * Tampilkan form untuk membuat anggota baru
     */
    public function create()
    {
        return view('pages.anggota.create');
    }

    /**
     * Simpan anggota baru ke database
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_kartu' => 'required|string|max:30|unique:anggotas,id_kartu',
                'name' => 'required|string|max:255',
                'no_telp' => 'required|string|max:50',
                'alamat' => 'required|string',
                'gol_darah' => 'required|string|max:2',
                'tinggi' => 'required|integer',
                'berat' => 'required|integer',
                'tempat_lahir' => 'required|string',
                'tgl_lahir' => 'required|date',
                'tgl_daftar' => 'required|date',
                'jenis_kelamin' => 'required|string|max:20',
                'riwayat_kesehatan' => 'nullable|string',
                'photo' => 'nullable|image|max:2048',
            ]);

            $data = $request->except('photo'); // ambil semua kecuali photo

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('anggota', 'public'); 
                $data['photo'] = $path;
            }

            Anggota::create($data);

            return back()->with('success', 'Data anggota berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal menambahkan anggota: ' . $e->getMessage())
                            ->withInput();
        }
    }


    /**
     * Tampilkan form untuk edit anggota
     */
    public function edit(Anggota $anggota)
    {
        return view('pages.anggota.edit', compact('anggota'));
    }

    /**
     * Update data anggota
     */
    public function update(Request $request, Anggota $anggota)
    {
        try {
            $request->validate([
                'id_kartu' => 'required|string|max:30|unique:anggotas,id_kartu,' . $anggota->id,
                'name' => 'required|string|max:255',
                'no_telp' => 'required|string|max:50',
                'alamat' => 'required|string',
                'gol_darah' => 'required|string|max:2',
                'tinggi' => 'required|integer',
                'berat' => 'required|integer',
                'tempat_lahir' => 'required|string',
                'tgl_lahir' => 'required|date',
                'tgl_daftar' => 'required|date',
                'jenis_kelamin' => 'required|string|max:20',
                'riwayat_kesehatan' => 'nullable|string',
                'photo' => 'nullable|image|max:2048',
            ]);

            $data = $request->except('photo');

            if ($request->hasFile('photo')) {
                // hapus photo lama
                if ($anggota->photo && \Storage::disk('public')->exists($anggota->photo)) {
                    \Storage::disk('public')->delete($anggota->photo);
                }
                $path = $request->file('photo')->store('anggota', 'public');
                $data['photo'] = $path;
            }

            $anggota->update($data);

            return back()->with('success', 'Data anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal memperbarui data anggota: ' . $e->getMessage())
                            ->withInput();
        }
    }

    public function destroy(Anggota $anggota)
    {
        try {
            // hapus photo dari storage jika ada
            if ($anggota->photo && \Storage::disk('public')->exists($anggota->photo)) {
                \Storage::disk('public')->delete($anggota->photo);
            }

            $anggota->delete();

            return redirect()->route('anggota.index')
                            ->with('success', 'Anggota berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal menghapus anggota: ' . $e->getMessage());
        }
    }

    /**
     * Export PDF dengan filter status
     */
    public function exportPdf(Request $request)
    {
        try {
            $statusFilter = $request->input('status_filter', 'all');
            
            // Ambil semua anggota dengan relasi untuk statistik keseluruhan
            $allAnggotas = Anggota::with(['anggotaMemberships' => function($query) {
                $query->latest('tgl_selesai');
            }])->get();
            
            // Hitung statistik dari SEMUA data (tidak terfilter)
            $totalAnggota = $allAnggotas->count();
            $totalAktif = $allAnggotas->filter(function($anggota) {
                return $anggota->status_keanggotaan === true;
            })->count();
            $totalTidakAktif = $allAnggotas->filter(function($anggota) {
                return $anggota->status_keanggotaan === false;
            })->count();
            
            // Filter data untuk ditampilkan di tabel
            if ($statusFilter === 'aktif') {
                $anggotas = $allAnggotas->filter(function($anggota) {
                    return $anggota->status_keanggotaan === true;
                });
                $title = 'Data Anggota Aktif';
            } elseif ($statusFilter === 'tidak_aktif') {
                $anggotas = $allAnggotas->filter(function($anggota) {
                    return $anggota->status_keanggotaan === false;
                });
                $title = 'Data Anggota Tidak Aktif';
            } else {
                $anggotas = $allAnggotas;
                $title = 'Data Semua Anggota';
            }
            
            // Generate PDF
            $pdf = Pdf::loadView('pages.anggota.pdf', [
                'anggotas' => $anggotas,
                'title' => $title,
                'statusFilter' => $statusFilter,
                'totalAnggota' => $totalAnggota,
                'totalAktif' => $totalAktif,
                'totalTidakAktif' => $totalTidakAktif,
            ]);
            
            // Set paper dan orientasi
            $pdf->setPaper('a4', 'landscape');
            
            // Generate filename
            $filename = 'Anggota_' . ucfirst($statusFilter) . '_' . date('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal mengekspor PDF: ' . $e->getMessage());
        }
    }
}