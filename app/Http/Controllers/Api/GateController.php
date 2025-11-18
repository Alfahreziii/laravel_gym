<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Anggota;
use App\Models\AnggotaMembership;
use App\Models\KehadiranMember;
use Carbon\Carbon;

class GateController extends Controller
{
    /**
     * ABSENSI MELALUI RFID + FOTO
     */
    public function absen(Request $request)
    {
        // Validasi input
        $request->validate([
            'kartu' => 'required|string',
        ]);

        $rfid = $request->kartu;
        $today = Carbon::today();

        // CARI MEMBER
        $member = Anggota::where('id_kartu', $rfid)->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data Member tidak ditemukan!',
                'gate' => 'tutup'
            ], 404);
        }

        // CARI MEMBERSHIP
        $membership = AnggotaMembership::where('id_anggota', $member->id)
            ->latest()
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'Member ditemukan tapi tidak memiliki paket membership!',
                'gate' => 'tutup'
            ], 403);
        }

        $mulai  = Carbon::parse($membership->tgl_mulai);
        $akhir  = Carbon::parse($membership->tgl_selesai);

        // BELUM AKTIF
        if ($today->lt($mulai)) {
            return response()->json([
                'success' => false,
                'message' => 'Membership belum aktif!',
                'tanggal_mulai' => $mulai->format('d-m-Y'),
                'gate' => 'tutup'
            ], 403);
        }

        // KADALUARSA
        if ($today->gt($akhir)) {
            return response()->json([
                'success' => false,
                'message' => 'Membership sudah kadaluarsa!',
                'tanggal_akhir' => $akhir->format('d-m-Y'),
                'gate' => 'tutup'
            ], 403);
        }

        /**
         * CEK STATUS TERAKHIR DI KEHADIRAN MEMBER
         * Jika masih IN → TOLAK
         */
        $lastPresence = KehadiranMember::where('rfid', $rfid)
            ->whereDate('created_at', $today)
            ->latest()
            ->first();

        if ($lastPresence && strtolower($lastPresence->status) === 'out') {
            return response()->json([
                'success' => false,
                'message' => 'Member masih berada di luar Gym (belum absen masuk)',
                'gate' => 'tutup'
            ], 403);
        }

        /**
         * Jika semua valid → gate dibuka
         * Tidak insert ke database
         * Tidak upload foto
         */

        return response()->json([
            'success' => true,
            'message' => "Selamat Datang, {$member->name}!",
            'gate' => 'buka',
            'waktu' => Carbon::now()->format('d-m-Y H:i:s')
        ], 200);
    }


    /**
     * SIMPAN BASE64 IMAGE (opsional)
     */
    private function saveBase64Image($base64)
    {
        try {
            if (strpos($base64, 'base64,') !== false) {
                [$meta, $data] = explode(';base64,', $base64);
            } else {
                $data = $base64;
            }

            $image = base64_decode($data);
            $filename = 'gate_' . uniqid() . '.png';
            $path = 'kehadiran_foto/' . $filename;

            Storage::disk('public')->put($path, $image);

            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function ping()
    {
        return response()->json([
            'success' => true,
            'message' => 'Gate API is running',
            'timestamp' => Carbon::now()->toDateTimeString()
        ], 200);
    }

    /**
     * CHECK MEMBER
     */
    public function checkMember(Request $request)
    {
        $request->validate(['kartu' => 'required']);

        $member = Anggota::where('id_kartu', $request->kartu)->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan'
            ], 404);
        }

        $membership = AnggotaMembership::where('id_anggota', $member->id)
            ->latest()
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => true,
                'membership_aktif' => false,
                'reason' => 'no_membership',
                'member' => $member
            ], 200);
        }

        return response()->json([
            'success' => true,
            'membership_aktif' => true,
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'rfid' => $member->rfid,
                'membership_mulai' => Carbon::parse($membership->tgl_mulai)->format('d-m-Y'),
                'membership_akhir' => Carbon::parse($membership->tgl_selesai)->format('d-m-Y'),
            ]
        ], 200);
    }
}
