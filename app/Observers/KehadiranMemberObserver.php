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
            // Status "aktif" tetap dari status_keanggotaan (cek keabsahan hari ini),
            // tapi tanggal/sisa hari yang DITAMPILKAN ikut latest_membership supaya
            // perpanjangan yang belum mulai tetap tercermin.
            $isAktif = $anggota->status_keanggotaan;
            $latestMembership = $anggota->latest_membership;

            if ($isAktif && $latestMembership) {
                $sisaHari   = (int) tenant_today()->diffInDays($latestMembership->tgl_selesai->endOfDay(), false);
                $tglSelesai = $latestMembership->tgl_selesai->format('d M Y');
            } else {
                if (!$latestMembership) {
                    $alasanTidakAktif = 'Belum pernah memiliki membership';
                } elseif ($latestMembership->status_pembayaran !== 'lunas') {
                    $alasanTidakAktif = 'Pembayaran membership belum lunas';
                } else {
                    $alasanTidakAktif = 'Membership expired sejak ' . $latestMembership->tgl_selesai->format('d M Y');
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
            'waktu'              => to_tenant_tz($kehadiran->created_at)->format('d M Y - H:i:s'),
            'timestamp'          => $kehadiran->created_at->timestamp,
        ];

        // Simpan ke cache sebagai "latest" — TTL 10 menit, di-scope per tenant
        // supaya notif absen tenant lain tidak ikut terbaca.
        Cache::put(tenant_cache_key('absen_notif_latest'), $payload, now()->addMinutes(10));
    }
}
