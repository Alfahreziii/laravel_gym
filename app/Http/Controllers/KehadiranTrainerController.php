<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KehadiranTrainer;
use App\Models\Trainer;

class KehadiranTrainerController extends Controller
{
    /**
     * Menampilkan daftar kehadiran
     */
    public function index()
    {
        // Ambil kehadiran beserta data anggota
        $kehadirantrainers = KehadiranTrainer::with('trainer')->latest()->get();
        return view('pages.kehadirantrainer.index', compact('kehadirantrainers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rfid'   => 'required|string',
            'status' => 'required|string|max:20',
        ]);

        // cek apakah kartu ada di tabel trainers
        $trainer = Trainer::where('rfid', $request->rfid)->first();

        if (!$trainer) {
            // lempar alert danger ke index
            return redirect()->route('kehadirantrainer.index')
                ->with('danger', 'Kartu dengan RFID' . e($request->rfid) . ' tidak ditemukan!');
        }

        try {
            KehadiranTrainer::create([
                'rfid'   => $request->rfid,
                'status' => $request->status,
            ]);

            return redirect()->route('kehadirantrainer.index')
                ->with('success', 'Absensi untuk trainer' . e($trainer->name) . 'berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->route('kehadirantrainer.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }


    /**
     * Hapus data kehadiran
     */
    public function destroy(KehadiranTrainer $kehadirantrainer)
    {
        try {
            $kehadirantrainer->delete();
            return redirect()->route('kehadirantrainer.index')->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('kehadirantrainer.index')->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }
    // public function store(Request $request)
    // {
    //     $rfid = $request->input('rfid'); // hasil dari tap kartu

    //     // cek apakah kartu ada di tabel anggota
    //     $anggota = Anggota::where('id_kartu', $rfid)->first();

    //     if (!$anggota) {
    //         return response()->json(['error' => 'Kartu tidak terdaftar'], 404);
    //     }

    //     // simpan kehadiran
    //     $kehadiran = Kehadiran::create([
    //         'rfid'   => $rfid,
    //         'status' => 'Hadir'
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Absensi berhasil dicatat',
    //         'data'    => $kehadiran
    //     ]);
    // }
}
