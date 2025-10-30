<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KehadiranTrainer;
use App\Models\Trainer;
use App\Models\KehadiranMember;
use App\Models\Anggota;
use Illuminate\Support\Facades\Storage;

class NoRoleController extends Controller
{
    /**
     * Menampilkan daftar kehadiran
     */
    public function index()
    {
        $kehadiranmembers = KehadiranMember::with('anggota')->latest()->get();
        return view('pages.norole.kehadiranmember', compact('kehadiranmembers'));
    }

    /**
     * Menyimpan data kehadiran (dengan foto)
     */
    public function store(Request $request)
    {
        $request->validate([
            'rfid' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // maksimal 2MB
        ]);

        // Cek apakah kartu terdaftar di tabel anggotas
        $anggota = Anggota::where('id_kartu', $request->rfid)->first();

        if (!$anggota) {
            return redirect()->route('absen.index')
                ->with('danger', 'Kartu dengan RFID ' . e($request->rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        $lastAttendance = KehadiranMember::where('rfid', $request->rfid)
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
            KehadiranMember::create([
                'rfid'   => $request->rfid,
                'status' => $status,
                'foto'   => $fotoPath,
            ]);

            return redirect()->route('absen.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk anggota ' . e($anggota->name) . ' berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->route('absen.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data kehadiran (dan fotonya)
     */
    public function destroy(KehadiranMember $kehadiranmember)
    {
        try {
            // Hapus foto jika ada
            if ($kehadiranmember->foto && Storage::disk('public')->exists($kehadiranmember->foto)) {
                Storage::disk('public')->delete($kehadiranmember->foto);
            }

            $kehadiranmember->delete();

            return redirect()->route('absen.index')->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('absen.index')
                ->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    } 

    /**
     * Menampilkan daftar kehadiran
     */
    public function indextrainer()
    {
        // Ambil kehadiran beserta data anggota
        $kehadirantrainers = KehadiranTrainer::with('trainer')->latest()->get();
        return view('pages.norole.kehadirantrainer', compact('kehadirantrainers'));
    }

    public function storetrainer(Request $request)
    {
        $request->validate([
            'rfid'   => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // cek apakah kartu ada di tabel trainers
        $trainer = Trainer::where('rfid', $request->rfid)->first();

        if (!$trainer) {
            // lempar alert danger ke index
            return redirect()->route('absentrainer.index')
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

            return redirect()->route('absentrainer.index')
                ->with('success', 'Absensi untuk trainer' . e($trainer->name) . 'berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data kehadiran
     */
    public function destroytrainer(KehadiranTrainer $kehadirantrainer)
    {
        try {
            if ($kehadirantrainer->foto && Storage::disk('public')->exists($kehadirantrainer->foto)) {
                Storage::disk('public')->delete($kehadirantrainer->foto);
            }

            $kehadirantrainer->delete();
            return redirect()->route('absentrainer.index')->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('absentrainer.index')->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }
}
