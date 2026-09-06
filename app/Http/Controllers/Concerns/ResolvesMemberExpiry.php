<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Anggota;
use Illuminate\Support\Collection;

/**
 * Trait untuk mengambil data foto profil & status expired membership anggota,
 * dipakai di semua tabel/export kehadiran member (bukan trainer — trainer tidak
 * punya membership).
 *
 * Reuse logic status yang sama persis dengan DashboardController::index()
 * bagian memberTerbaru, supaya badge "Aktif/Akan berakhir/Expired/Belum daftar"
 * konsisten di seluruh halaman.
 */
trait ResolvesMemberExpiry
{
    /**
     * Ambil map Anggota (beserta anggotaMemberships) untuk sekumpulan rfid,
     * di-key dengan versi uppercase rfid supaya bisa langsung di-lookup per baris
     * tanpa query per baris (N+1).
     *
     * @param  array<int, string|null>  $rfids
     */
    protected function anggotaMapForRfids(array $rfids): Collection
    {
        $rfids = collect($rfids)
            ->filter()
            ->map(fn ($rfid) => strtoupper($rfid))
            ->unique()
            ->values();

        if ($rfids->isEmpty()) {
            return collect();
        }

        $placeholders = implode(',', array_fill(0, $rfids->count(), '?'));

        return Anggota::with(['anggotaMemberships' => fn ($q) => $q->orderByDesc('tgl_selesai')])
            ->whereRaw("UPPER(id_kartu) IN ({$placeholders})", $rfids->all())
            ->get()
            ->keyBy(fn (Anggota $anggota) => strtoupper($anggota->id_kartu));
    }

    /**
     * Bangun info foto profil + status expired untuk satu anggota (atau null
     * kalau rfid kehadiran tidak punya pasangan anggota).
     *
     * @return array{profile_photo: string, expired_at: string, membership_status: string, membership_type: string}
     */
    protected function memberExpiryInfo(?Anggota $anggota): array
    {
        if (! $anggota) {
            return [
                'profile_photo'     => asset('assets/images/user-grid/user-grid-img14.png'),
                'expired_at'        => '-',
                'membership_status' => 'Belum daftar',
                'membership_type'   => 'neutral',
            ];
        }

        $todayStr = tenant_today()->format('Y-m-d');

        $active = $anggota->anggotaMemberships
            ->filter(fn ($m) => $m->tgl_mulai->format('Y-m-d') <= $todayStr && $m->tgl_selesai->format('Y-m-d') >= $todayStr)
            ->first();
        $latest = $active ?? $anggota->anggotaMemberships->first();

        if (! $latest || $latest->tgl_selesai->format('Y-m-d') < $todayStr) {
            $statusLabel = $latest ? 'Expired' : 'Belum daftar';
            $statusType  = $latest ? 'danger' : 'neutral';
        } elseif (\Carbon\Carbon::parse($todayStr)->diffInDays($latest->tgl_selesai->format('Y-m-d')) <= 7) {
            $statusLabel = 'Akan berakhir';
            $statusType  = 'warning';
        } else {
            $statusLabel = 'Aktif';
            $statusType  = 'success';
        }

        return [
            'profile_photo'     => $anggota->photo_url,
            'expired_at'        => $latest ? $latest->tgl_selesai->format('d M Y') : '-',
            'membership_status' => $statusLabel,
            'membership_type'   => $statusType,
        ];
    }
}
