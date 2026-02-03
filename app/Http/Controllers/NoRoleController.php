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
     * Menampilkan halaman absensi member
     */
    public function index()
    {
        $kehadiranmembers = KehadiranMember::with('anggota.user')->latest()->get();
        return view('pages.norole.kehadiranmember', compact('kehadiranmembers'));
    }

    /**
     * Menyimpan data kehadiran member (dengan foto)
     */
    public function store(Request $request)
    {
        $request->validate([
            'rfid' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // NORMALISASI RFID: Ubah ke uppercase untuk konsistensi
        $rfid = strtoupper(trim($request->rfid, '0'));

        // Cek apakah kartu terdaftar (case-insensitive)
        $anggota = Anggota::whereRaw('UPPER(id_kartu) = ?', [$rfid])->first();

        if (!$anggota) {
            return redirect()->route('absen.index')
                ->with('danger', 'Kartu dengan RFID ' . e($rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        // Cari kehadiran terakhir dengan case-insensitive
        $lastAttendance = KehadiranMember::whereRaw('UPPER(rfid) = ?', [$rfid])
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
                'rfid'   => $anggota->id_kartu, // Gunakan ID kartu asli dari database
                'status' => $status,
                'foto'   => $fotoPath,
            ]);

            return redirect()->route('absen.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk ' . e($anggota->name) . ' berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->route('absen.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data kehadiran member
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
     * Menampilkan halaman absensi trainer
     */
    public function indextrainer()
    {
        $kehadirantrainers = KehadiranTrainer::with('trainer')->latest()->get();
        return view('pages.norole.kehadirantrainer', compact('kehadirantrainers'));
    }

    /**
     * Menyimpan data kehadiran trainer (dengan foto)
     */
    public function storetrainer(Request $request)
    {
        $request->validate([
            'rfid' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // NORMALISASI RFID untuk trainer juga
        $rfid = strtoupper(trim($request->rfid));

        // Cek apakah kartu ada di tabel trainers (case-insensitive)
        $trainer = Trainer::whereRaw('UPPER(rfid) = ?', [$rfid])->first();

        if (!$trainer) {
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Kartu dengan RFID ' . e($rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        $lastAttendance = KehadiranTrainer::whereRaw('UPPER(rfid) = ?', [$rfid])
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
                'rfid'   => $trainer->rfid, // Gunakan RFID asli dari database
                'status' => $status,
                'foto'   => $fotoPath,
            ]);

            return redirect()->route('absentrainer.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk ' . e($trainer->name) . ' berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data kehadiran trainer
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
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }
}
