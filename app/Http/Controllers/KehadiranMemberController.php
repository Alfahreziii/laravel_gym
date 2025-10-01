<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KehadiranMember;
use App\Models\Anggota;

class KehadiranMemberController extends Controller
{
    /**
     * Menampilkan daftar kehadiran
     */
    public function index()
    {
        // Ambil kehadiran beserta data anggota
        $kehadiranmembers = KehadiranMember::with('anggota')->latest()->get();
        return view('pages.kehadiranmember.index', compact('kehadiranmembers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rfid'   => 'required|string',
            'status' => 'required|string|max:20',
        ]);

        // cek apakah kartu ada di tabel anggotas
        $anggota = Anggota::where('id_kartu', $request->rfid)->first();

        if (!$anggota) {
            // lempar alert danger ke index
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Kartu dengan RFID' . e($request->rfid) . ' tidak ditemukan!');
        }

        try {
            KehadiranMember::create([
                'rfid'   => $request->rfid,
                'status' => $request->status,
            ]);

            return redirect()->route('kehadiranmember.index')
                ->with('success', 'Absensi untuk anggota' . e($anggota->name) . 'berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }


    /**
     * Hapus data kehadiran
     */
    public function destroy(KehadiranMember $kehadiranmember)
    {
        try {
            $kehadiranmember->delete();
            return redirect()->route('kehadiranmember.index')->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('kehadiranmember.index')->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
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
