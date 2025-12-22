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
        $user = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();
        
        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        // Ambil member yang hadir hari ini dengan status 'in'
        $memberInGymToday = KehadiranMember::with('anggota')
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->get()
            ->groupBy('rfid')
            ->map(fn($items) => $items->first())
            ->filter(fn($item) => strtolower($item->status) === 'in')
            ->pluck('rfid')
            ->toArray();

        $today = now()->toDateString();
        
        // Ambil semua member trainer dengan grouping berdasarkan id_anggota
        $allMemberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'sesiLogs'])
            ->where('id_trainer', $trainer->id)
            ->get();

        // Group by member dan hitung statistik
        $groupedMembers = $allMemberTrainers->groupBy('id_anggota')->map(function($memberGroup) use ($memberInGymToday, $today) {
            $member = $memberGroup->first()->anggota;
            
            // Data paket aktif (dalam rentang tanggal dan sesi > 0)
            $activeSessions = $memberGroup->filter(function($mt) use ($today) {
                return $mt->tgl_mulai <= $today 
                    && $mt->tgl_selesai >= $today 
                    && $mt->sesi > 0;
            });

            // Data paket kadaluarsa
            $expiredSessions = $memberGroup->filter(function($mt) use ($today) {
                return $mt->tgl_selesai < $today || $mt->sesi <= 0;
            });

            // Hitung total sesi
            $totalSesiAktif = $activeSessions->sum('sesi');
            $totalSesiKadaluarsa = $expiredSessions->sum(function($mt) {
                return $mt->paketPersonalTrainer->jumlah_sesi ?? 0;
            });
            $totalSesiSelesai = $memberGroup->sum('sesi_sudah_dijalani');

            // Cek status aktif training
            $activeTrainingSession = $activeSessions->firstWhere('is_session_active', true);

            return (object) [
                'anggota' => $member,
                'id_anggota' => $member->id,
                'is_checked_in' => in_array($member->id_kartu ?? null, $memberInGymToday),
                'total_paket_aktif' => $activeSessions->count(),
                'total_paket_kadaluarsa' => $expiredSessions->count(),
                'total_sesi_aktif' => $totalSesiAktif,
                'total_sesi_kadaluarsa' => $totalSesiKadaluarsa,
                'total_sesi_selesai' => $totalSesiSelesai,
                'is_session_active' => $activeTrainingSession ? true : false,
                'active_session' => $activeTrainingSession,
                'session_started_at' => $activeTrainingSession ? $activeTrainingSession->session_started_at : null,
                'all_sessions' => $memberGroup, // Untuk keperluan detail
            ];
        })->values();

        return view('pages.trainer.trainerlistmember.index', compact('trainer', 'groupedMembers'));
    }

    /**
     * Tambahkan method ini ke TrainerDashboardController
     * Menampilkan detail lengkap member beserta riwayat dengan trainer
     */
    public function memberDetail($idAnggota)
    {
        $user = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();
        
        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        // Ambil data member
        $member = \App\Models\Anggota::findOrFail($idAnggota);

        // Ambil semua riwayat paket PT member dengan trainer ini
        $memberTrainers = MemberTrainer::with(['paketPersonalTrainer', 'sesiLogs', 'pembayaranMemberTrainers'])
            ->where('id_trainer', $trainer->id)
            ->where('id_anggota', $idAnggota)
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        $today = now()->toDateString();

        // Kategorikan paket
        $activePackages = $memberTrainers->filter(function($mt) use ($today) {
            return $mt->tgl_mulai <= $today 
                && $mt->tgl_selesai >= $today 
                && $mt->sesi > 0;
        });

        $expiredPackages = $memberTrainers->filter(function($mt) use ($today) {
            return $mt->tgl_selesai < $today || $mt->sesi <= 0;
        });

        // Statistik keseluruhan
        $totalSesiAktif = $activePackages->sum('sesi');
        $totalSesiSelesai = $memberTrainers->sum('sesi_sudah_dijalani');
        $totalPaket = $memberTrainers->count();
        
        // Total sesi dari paket kadaluarsa
        $totalSesiKadaluarsa = $expiredPackages->sum(function($mt) {
            return $mt->paketPersonalTrainer->jumlah_sesi ?? 0;
        });

        // Cek kehadiran hari ini
        $isCheckedInToday = KehadiranMember::where('rfid', $member->id_kartu)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->first();

        $isCheckedIn = $isCheckedInToday && strtolower(trim($isCheckedInToday->status)) === 'in';

        // Ambil sesi yang sedang aktif
        $activeSession = $activePackages->firstWhere('is_session_active', true);

        return view('pages.trainer.trainerlistmember.member-detail', compact(
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
