<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Trainer;
use App\Models\MemberTrainer;
use App\Models\KehadiranMember;

class TrainerListMemberController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        $memberInGymToday = KehadiranMember::whereDate('created_at', now()->toDateString())
            ->latest()
            ->get()
            ->groupBy('rfid')
            ->map(fn($items) => $items->first())
            ->filter(fn($item) => strtolower($item->status) === 'in')
            ->pluck('rfid')
            ->toArray();

        $today = now()->toDateString();

        $allMemberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'sesiLogs'])
            ->where('id_trainer', $trainer->id)
            ->get();

        $groupedMembers = $allMemberTrainers->groupBy('id_anggota')->map(function ($memberGroup) use ($memberInGymToday, $today) {
            $member = $memberGroup->first()->anggota;

            $activeSessions = $memberGroup->filter(function ($mt) use ($today) {
                return $mt->tgl_mulai->format('Y-m-d') <= $today
                    && $mt->tgl_selesai->format('Y-m-d') >= $today
                    && $mt->sesi > 0;
            });

            $expiredSessions = $memberGroup->filter(function ($mt) use ($today) {
                return $mt->tgl_selesai->format('Y-m-d') < $today || $mt->sesi <= 0;
            });

            $totalSesiAktif      = $activeSessions->sum('sesi');
            $totalSesiKadaluarsa = $expiredSessions->sum(fn($mt) => $mt->paketPersonalTrainer->jumlah_sesi ?? 0);
            $totalSesiSelesai    = $memberGroup->sum(fn($mt) => $mt->sesi_sudah_dijalani);

            // Paket yang sedang running (is_session_active = true)
            $activeTrainingSession = $activeSessions->firstWhere('is_session_active', true);

            // Paket aktif pertama untuk tombol MULAI SESI (belum running)
            $firstAvailableSession = $activeSessions->first();

            return (object) [
                'anggota'                => $member,
                'id_anggota'             => $member->id,
                'is_checked_in'          => in_array($member->id_kartu ?? null, $memberInGymToday),
                'total_paket_aktif'      => $activeSessions->count(),
                'total_paket_kadaluarsa' => $expiredSessions->count(),
                'total_sesi_aktif'       => $totalSesiAktif,
                'total_sesi_kadaluarsa'  => $totalSesiKadaluarsa,
                'total_sesi_selesai'     => $totalSesiSelesai,
                'is_session_active'      => $activeTrainingSession ? true : false,
                'active_session'         => $activeTrainingSession,        // paket yang sedang running
                'first_available_session' => $firstAvailableSession,        // paket untuk mulai sesi
                'session_started_at'     => $activeTrainingSession ? $activeTrainingSession->session_started_at : null,
                'all_sessions'           => $memberGroup,
            ];
        })->values();

        return view('pages.trainer.trainer-list-member.index', compact('trainer', 'groupedMembers'));
    }

    public function datatable(Request $request)
    {
        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return response()->json([
                'data' => [], 'total' => 0, 'perPage' => 10, 'page' => 1, 'lastPage' => 1,
            ]);
        }

        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $memberInGymToday = KehadiranMember::whereDate('created_at', now()->toDateString())
            ->latest()
            ->get()
            ->groupBy('rfid')
            ->map(fn($items) => $items->first())
            ->filter(fn($item) => strtolower($item->status) === 'in')
            ->pluck('rfid')
            ->toArray();

        $today             = now()->toDateString();
        $trainerIsTraining = $trainer->isTraining();

        $allMemberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'sesiLogs'])
            ->where('id_trainer', $trainer->id)
            ->get();

        $groupedMembers = $allMemberTrainers->groupBy('id_anggota')->map(function ($memberGroup) use ($memberInGymToday, $today) {
            $member = $memberGroup->first()->anggota;

            $activeSessions = $memberGroup->filter(function ($mt) use ($today) {
                return $mt->tgl_mulai->format('Y-m-d') <= $today
                    && $mt->tgl_selesai->format('Y-m-d') >= $today
                    && $mt->sesi > 0;
            });

            $expiredSessions = $memberGroup->filter(function ($mt) use ($today) {
                return $mt->tgl_selesai->format('Y-m-d') < $today || $mt->sesi <= 0;
            });

            $totalSesiAktif      = $activeSessions->sum('sesi');
            $totalSesiKadaluarsa = $expiredSessions->sum(fn($mt) => $mt->paketPersonalTrainer->jumlah_sesi ?? 0);
            $totalSesiSelesai    = $memberGroup->sum(fn($mt) => $mt->sesi_sudah_dijalani);

            $activeTrainingSession = $activeSessions->firstWhere('is_session_active', true);
            $firstAvailableSession = $activeSessions->first();

            return (object) [
                'anggota'                 => $member,
                'id_anggota'              => $member->id,
                'is_checked_in'           => in_array($member->id_kartu ?? null, $memberInGymToday),
                'total_paket_aktif'       => $activeSessions->count(),
                'total_sesi_aktif'        => $totalSesiAktif,
                'total_sesi_kadaluarsa'   => $totalSesiKadaluarsa,
                'total_sesi_selesai'      => $totalSesiSelesai,
                'is_session_active'       => $activeTrainingSession ? true : false,
                'active_session'          => $activeTrainingSession,
                'first_available_session' => $firstAvailableSession,
                'session_started_at'      => $activeTrainingSession ? $activeTrainingSession->session_started_at : null,
            ];
        })->values();

        if ($search) {
            $groupedMembers = $groupedMembers->filter(function ($m) use ($search) {
                $query = strtolower($search);
                return str_contains(strtolower($m->anggota->name ?? ''), $query)
                    || str_contains(strtolower($m->anggota->no_telp ?? ''), $query);
            })->values();
        }

        $total         = $groupedMembers->count();
        $paged         = $groupedMembers->skip(($page - 1) * $perPage)->take($perPage)->values();
        $monitoringUrl = route('trainer.monitoring');

        return response()->json([
            'data' => $paged->map(function ($m, $index) use ($page, $perPage, $trainerIsTraining, $monitoringUrl) {
                $sessionId = $m->active_session?->id ?? $m->first_available_session?->id ?? 0;
                return [
                    'no'                    => (($page - 1) * $perPage) + $index + 1,
                    'id_anggota'            => $m->id_anggota,
                    'anggota_name'          => $m->anggota->name ?? '-',
                    'anggota_no_telp'       => $m->anggota->no_telp ?? '-',
                    'detail_url'            => route('trainerlistmember.detail', $m->id_anggota),
                    'total_paket_aktif'     => $m->total_paket_aktif,
                    'total_sesi_aktif'      => $m->total_sesi_aktif,
                    'total_sesi_selesai'    => $m->total_sesi_selesai,
                    'total_sesi_kadaluarsa' => $m->total_sesi_kadaluarsa,
                    'is_checked_in'         => (bool) $m->is_checked_in,
                    'is_session_active'     => (bool) $m->is_session_active,
                    'session_started_at'    => $m->session_started_at
                        ? \Carbon\Carbon::parse($m->session_started_at)->format('H:i')
                        : null,
                    'trainer_is_training'   => $trainerIsTraining,
                    'start_session_url'     => route('trainer.session.start', $sessionId),
                    'monitoring_url'        => $monitoringUrl,
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function datatableActivePackages(Request $request, $idAnggota)
    {
        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return response()->json(['data' => [], 'total' => 0, 'perPage' => 10, 'page' => 1, 'lastPage' => 1]);
        }

        $member  = \App\Models\Anggota::findOrFail($idAnggota);
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);
        $today   = now()->toDateString();

        $trainerIsTraining = $trainer->isTraining();

        $lastKehadiran = KehadiranMember::where('rfid', $member->id_kartu)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->first();
        $isCheckedIn = $lastKehadiran && strtolower(trim($lastKehadiran->status)) === 'in';

        $query = MemberTrainer::with(['paketPersonalTrainer'])
            ->where('id_trainer', $trainer->id)
            ->where('id_anggota', $idAnggota)
            ->whereDate('tgl_mulai', '<=', $today)
            ->whereDate('tgl_selesai', '>=', $today)
            ->where('sesi', '>', 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('paketPersonalTrainer', fn($q2) => $q2->where('nama_paket', 'like', "%{$search}%"));
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->orderBy('tgl_mulai')->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($mt, $index) use ($page, $perPage, $trainerIsTraining, $isCheckedIn, $member) {
                return [
                    'no'                  => (($page - 1) * $perPage) + $index + 1,
                    'paket_nama'          => $mt->paketPersonalTrainer->nama_paket ?? '-',
                    'kode_transaksi'      => $mt->kode_transaksi,
                    'periode'             => \Carbon\Carbon::parse($mt->tgl_mulai)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($mt->tgl_selesai)->format('d M Y'),
                    'sesi'                => $mt->sesi,
                    'jumlah_sesi'         => $mt->paketPersonalTrainer->jumlah_sesi ?? 0,
                    'is_session_active'   => (bool) $mt->is_session_active,
                    'session_started_at'  => $mt->is_session_active && $mt->session_started_at
                        ? \Carbon\Carbon::parse($mt->session_started_at)->format('H:i')
                        : null,
                    'trainer_is_training' => $trainerIsTraining,
                    'is_checked_in'       => $isCheckedIn,
                    'member_name'         => $member->name ?? '-',
                    'start_session_url'   => route('trainer.session.start', $mt->id),
                    'monitoring_url'      => route('trainer.monitoring'),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function datatableHistory(Request $request, $idAnggota)
    {
        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return response()->json(['data' => [], 'total' => 0, 'perPage' => 10, 'page' => 1, 'lastPage' => 1]);
        }

        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);
        $today   = now()->toDateString();

        $query = MemberTrainer::with(['paketPersonalTrainer'])
            ->where('id_trainer', $trainer->id)
            ->where('id_anggota', $idAnggota)
            ->orderBy('tgl_mulai', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('paketPersonalTrainer', fn($q2) => $q2->where('nama_paket', 'like', "%{$search}%"));
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($mt, $index) use ($page, $perPage, $today) {
                $isActive  = $mt->tgl_mulai->format('Y-m-d') <= $today
                          && $mt->tgl_selesai->format('Y-m-d') >= $today
                          && $mt->sesi > 0;
                $isExpired = $mt->tgl_selesai->format('Y-m-d') < $today || $mt->sesi <= 0;

                if ($isActive)      { $statusType = 'success'; $statusLabel = 'Aktif'; }
                elseif ($isExpired) { $statusType = 'warning'; $statusLabel = 'Kadaluarsa'; }
                else                { $statusType = 'neutral'; $statusLabel = 'Belum Dimulai'; }

                return [
                    'no'             => (($page - 1) * $perPage) + $index + 1,
                    'kode_transaksi' => $mt->kode_transaksi,
                    'history_url'    => route('trainer.member.history', $mt->id),
                    'paket_nama'     => $mt->paketPersonalTrainer->nama_paket ?? '-',
                    'periode'        => \Carbon\Carbon::parse($mt->tgl_mulai)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($mt->tgl_selesai)->format('d M Y'),
                    'jumlah_sesi'    => $mt->paketPersonalTrainer->jumlah_sesi ?? 0,
                    'sesi_selesai'   => $mt->sesi_sudah_dijalani,
                    'sisa_sesi'      => $mt->sesi,
                    'status_type'    => $statusType,
                    'status_label'   => $statusLabel,
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function memberDetail($idAnggota)
    {
        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        $member = \App\Models\Anggota::findOrFail($idAnggota);

        $memberTrainers = MemberTrainer::with(['paketPersonalTrainer', 'sesiLogs', 'pembayaranMemberTrainers'])
            ->where('id_trainer', $trainer->id)
            ->where('id_anggota', $idAnggota)
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        $today = now()->toDateString();

        $activePackages = $memberTrainers->filter(function ($mt) use ($today) {
            return $mt->tgl_mulai->format('Y-m-d') <= $today
                && $mt->tgl_selesai->format('Y-m-d') >= $today
                && $mt->sesi > 0;
        });

        $expiredPackages = $memberTrainers->filter(function ($mt) use ($today) {
            return $mt->tgl_selesai->format('Y-m-d') < $today || $mt->sesi <= 0;
        });

        $totalSesiAktif      = $activePackages->sum('sesi');
        $totalSesiKadaluarsa = $expiredPackages->sum(fn($mt) => $mt->paketPersonalTrainer->jumlah_sesi ?? 0);
        $totalSesiSelesai    = $memberTrainers->sum(fn($mt) => $mt->sesi_sudah_dijalani);
        $totalPaket          = $memberTrainers->count();

        $isCheckedInToday = KehadiranMember::where('rfid', $member->id_kartu)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->first();

        $isCheckedIn   = $isCheckedInToday && strtolower(trim($isCheckedInToday->status)) === 'in';
        $activeSession = $activePackages->firstWhere('is_session_active', true);

        return view('pages.trainer.trainer-list-member.member-detail', compact(
            'trainer',
            'member',
            'memberTrainers',
            'activePackages',
            'expiredPackages',
            'totalSesiAktif',
            'totalSesiSelesai',
            'totalSesiKadaluarsa',
            'totalPaket',
            'isCheckedIn',
            'activeSession'
        ));
    }
}
