<?php

namespace App\Observers;

use App\Models\KehadiranMember;
use App\Models\Anggota;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class KehadiranMemberObserver
{
    public function created(KehadiranMember $kehadiran): void
    {
        // Ambil data anggota untuk status keanggotaan
        $anggota = Anggota::whereRaw('UPPER(id_kartu) = ?', [strtoupper($kehadiran->rfid)])->first();

        $isAktif          = false;
        $sisaHari         = null;
        $tglSelesai       = null;
        $alasanTidakAktif = null;
        $fotoUrl          = $kehadiran->foto ? asset('storage/' . $kehadiran->foto) : null;

        if ($anggota) {
            $isAktif          = $anggota->status_keanggotaan;
            $activeMembership = $anggota->active_membership;

            if ($isAktif && $activeMembership) {
                $sisaHari   = now()->startOfDay()->diffInDays($activeMembership->tgl_selesai->endOfDay(), false);
                $tglSelesai = $activeMembership->tgl_selesai->format('d M Y');
            } else {
                $latest = $anggota->anggotaMemberships()->latest('tgl_selesai')->first();
                if (!$latest) {
                    $alasanTidakAktif = 'Belum pernah memiliki membership';
                } elseif ($latest->status_pembayaran !== 'lunas') {
                    $alasanTidakAktif = 'Pembayaran membership belum lunas';
                } else {
                    $alasanTidakAktif = 'Membership expired sejak ' . $latest->tgl_selesai->format('d M Y');
                }
            }
        }

        $payload = [
            'id'                 => $kehadiran->id,
            'nama'               => $kehadiran->nama ?? ($anggota?->name ?? '-'),
            'status'             => $kehadiran->status,
            'is_aktif'           => $isAktif,
            'sisa_hari'          => $sisaHari,
            'tgl_selesai'        => $tglSelesai,
            'alasan_tidak_aktif' => $alasanTidakAktif,
            'foto'               => $fotoUrl,
            'waktu'              => $kehadiran->created_at->format('d M Y - H:i:s'),
            'timestamp'          => $kehadiran->created_at->timestamp,
        ];

        // Simpan ke cache sebagai "latest" — TTL 10 menit
        Cache::put('absen_notif_latest', $payload, now()->addMinutes(10));
    }
}
