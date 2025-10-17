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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // cek apakah kartu ada di tabel trainers
        $trainer = Trainer::where('rfid', $request->rfid)->first();

        if (!$trainer) {
            // lempar alert danger ke index
            return redirect()->route('kehadirantrainer.index')
                ->with('danger', 'Kartu dengan RFID' . e($request->rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        $lastAttendance = KehadiranTrainer::where('rfid', $request->rfid)
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->first();

        // Tentukan status otomatis (in/out)
        $status = (!$lastAttendance || $lastAttendance->status === 'out') ? 'in' : 'out';

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kehadiran_foto', 'public');
        }

        try {
            KehadiranTrainer::create([
                'rfid'   => $request->rfid,
                'status' => $status,
                'foto'   => $fotoPath,
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
            if ($kehadirantrainer->foto && Storage::disk('public')->exists($kehadirantrainer->foto)) {
                Storage::disk('public')->delete($kehadirantrainer->foto);
            }

            $kehadirantrainer->delete();
            return redirect()->route('kehadirantrainer.index')->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('kehadirantrainer.index')->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }
}
