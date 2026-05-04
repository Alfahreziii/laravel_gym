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
     * ABSENSI MELALUI RFID + FOTO Buka gate
     */
    public function absen(Request $request)
    {
        $request->validate([
            'kartu' => 'required|string',
        ]);

        $rfid = $request->kartu;
        $today = Carbon::today();

        $member = Anggota::where('id_kartu', $rfid)->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data Member tidak ditemukan!',
                'gate' => 'tutup'
            ], 404);
        }

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

        $mulai = Carbon::parse($membership->tgl_mulai);
        $akhir = Carbon::parse($membership->tgl_selesai);

        if ($today->lt($mulai)) {
            return response()->json([
                'success' => false,
                'message' => 'Membership belum aktif!',
                'tanggal_mulai' => $mulai->format('d-m-Y'),
                'gate' => 'tutup'
            ], 403);
        }

        if ($today->gt($akhir)) {
            return response()->json([
                'success' => false,
                'message' => 'Membership sudah kadaluarsa!',
                'tanggal_akhir' => $akhir->format('d-m-Y'),
                'gate' => 'tutup'
            ], 403);
        }

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

        return response()->json([
            'success' => true,
            'message' => "Selamat Datang, {$member->name}!",
            'gate' => 'buka',
            'waktu' => Carbon::now()->format('d-m-Y H:i:s')
        ], 200);
    }

    /**
     * ABSENSI MELALUI FINGERPRINT — Buka gate + catat kehadiran
     */
    /**
     * ABSENSI MELALUI FINGERPRINT — Buka gate + catat kehadiran
     * Cek member dulu, jika tidak ditemukan cek trainer
     */
    public function absenfinger(Request $request)
    {
        $request->validate([
            'kartu' => 'required|string',
        ]);

        $rfid = $request->kartu;
        $today = Carbon::today();

        // ============================================================
        // CEK MEMBER DULU
        // ============================================================
        $member = Anggota::where('id_kartu', $rfid)->first();

        if ($member) {
            $membership = AnggotaMembership::where('id_anggota', $member->id)
                ->latest()
                ->first();

            if (!$membership) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member ditemukan tapi tidak memiliki paket membership!',
                    'gate'    => 'tutup'
                ], 403);
            }

            $mulai = Carbon::parse($membership->tgl_mulai);
            $akhir = Carbon::parse($membership->tgl_selesai);

            if ($today->lt($mulai)) {
                return response()->json([
                    'success'        => false,
                    'message'        => 'Membership belum aktif!',
                    'tanggal_mulai'  => $mulai->format('d-m-Y'),
                    'gate'           => 'tutup'
                ], 403);
            }

            if ($today->gt($akhir)) {
                return response()->json([
                    'success'       => false,
                    'message'       => 'Membership sudah kadaluarsa!',
                    'tanggal_akhir' => $akhir->format('d-m-Y'),
                    'gate'          => 'tutup'
                ], 403);
            }

            $lastPresence = KehadiranMember::where('rfid', $rfid)
                ->whereDate('created_at', $today)
                ->latest()
                ->first();

            $status = (!$lastPresence || $lastPresence->status === 'out') ? 'in' : 'out';

            KehadiranMember::create([
                'rfid'   => $member->id_kartu,
                'nama'   => $member->name,
                'status' => $status,
                'foto'   => null,
            ]);

            return response()->json([
                'success'      => true,
                'tipe'         => 'member',
                'message'      => "Selamat Datang, {$member->name}!",
                'status_absen' => $status,
                'gate'         => 'buka',
                'waktu'        => Carbon::now()->format('d-m-Y H:i:s')
            ], 200);
        }

        // ============================================================
        // JIKA BUKAN MEMBER, CEK TRAINER
        // ============================================================
        $trainer = \App\Models\Trainer::where('rfid', $rfid)->first();

        if (!$trainer) {
            return response()->json([
                'success' => false,
                'message' => 'RFID tidak ditemukan sebagai member maupun trainer!',
                'gate'    => 'tutup'
            ], 404);
        }

        $lastPresenceTrainer = \App\Models\KehadiranTrainer::where('rfid', $rfid)
            ->whereDate('created_at', $today)
            ->latest()
            ->first();

        $statusTrainer = (!$lastPresenceTrainer || $lastPresenceTrainer->status === 'out') ? 'in' : 'out';

        \App\Models\KehadiranTrainer::create([
            'rfid'   => $trainer->rfid,
            'nama'   => $trainer->name,
            'status' => $statusTrainer,
            'foto'   => null,
        ]);

        return response()->json([
            'success'      => true,
            'tipe'         => 'trainer',
            'message'      => "Selamat Datang, {$trainer->name}!",
            'status_absen' => $statusTrainer,
            'gate'         => 'buka',
            'waktu'        => Carbon::now()->format('d-m-Y H:i:s')
        ], 200);
    }

    /**
     * ENROLL FINGERPRINT
     */
    public function enrollfinger(Request $request)
    {
        $request->validate([
            'kartu' => 'required|string',
        ]);

        $rfid = $request->kartu;
        $today = Carbon::today();

        $member = Anggota::where('status_finger', 0)->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data Member tidak ditemukan!',
                'gate' => 'tutup'
            ], 404);
        }

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

        $mulai = Carbon::parse($membership->tgl_mulai);
        $akhir = Carbon::parse($membership->tgl_selesai);

        if ($today->lt($mulai)) {
            return response()->json([
                'success' => false,
                'message' => 'Membership belum aktif!',
                'tanggal_mulai' => $mulai->format('d-m-Y'),
                'gate' => 'tutup'
            ], 403);
        }

        if ($today->gt($akhir)) {
            return response()->json([
                'success' => false,
                'message' => 'Membership sudah kadaluarsa!',
                'tanggal_akhir' => $akhir->format('d-m-Y'),
                'gate' => 'tutup'
            ], 403);
        }

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

        return response()->json([
            'success'    => true,
            'message'    => "Selamat Datang, {$member->name}!",
            'idmember'   => "{$member->id_kartu}",
            'namamember' => "{$member->name}",
            'gate'       => 'buka',
            'waktu'      => Carbon::now()->format('d-m-Y H:i:s')
        ], 200);
    }

    /**
     * DELETE FINGERPRINT
     */
    public function deletefinger(Request $request)
    {
        $request->validate([
            'kartu' => 'required|string',
        ]);

        $member = Anggota::where('status_finger', 1)->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data Member tidak ditemukan!',
                'gate' => 'tutup'
            ], 404);
        }

        return response()->json([
            'success'    => true,
            'message'    => "Nama Member, {$member->name}!",
            'idmember'   => "{$member->id_kartu}",
            'namamember' => "{$member->name}",
            'gate'       => 'buka',
            'waktu'      => Carbon::now()->format('d-m-Y H:i:s')
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

    /**
     * PING
     */
    public function ping()
    {
        return response()->json([
            'success'   => true,
            'message'   => 'Gate API is running',
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
                'success'          => true,
                'membership_aktif' => false,
                'reason'           => 'no_membership',
                'member'           => $member
            ], 200);
        }

        return response()->json([
            'success'          => true,
            'membership_aktif' => true,
            'member'           => [
                'id'                 => $member->id,
                'name'               => $member->name,
                'rfid'               => $member->rfid,
                'membership_mulai'   => Carbon::parse($membership->tgl_mulai)->format('d-m-Y'),
                'membership_akhir'   => Carbon::parse($membership->tgl_selesai)->format('d-m-Y'),
            ]
        ], 200);
    }
}
