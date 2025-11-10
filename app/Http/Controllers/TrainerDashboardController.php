<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainer;
use App\Models\MemberTrainer;
use App\Models\SesiMemberTrainer;
use App\Models\SesiTrainer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrainerDashboardController extends Controller
{
    /**
     * Dashboard trainer - lihat daftar member yang dia latih
     */
    public function index()
    {
        // Asumsi: User login memiliki relasi ke Trainer (sesuaikan dengan sistem Anda)
        $user = Auth::user();
        
        // Cari trainer berdasarkan user login (bisa pakai email, atau field lain)
        // Sesuaikan dengan struktur database Anda
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();
        
        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        // Ambil semua member yang dilatih trainer ini
        $memberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'sesiLogs'])
            ->where('id_trainer', $trainer->id) // Hanya yang sudah lunas
            ->get();

        return view('pages.trainer.dashboard', compact('trainer', 'memberTrainers'));
    }

    public function waiting()
    {
        return view('pages.trainer.waiting');
    }

    /**
     * Mulai sesi training (ubah status jadi OPEN)
     */
    public function startSession($memberTrainerId)
    {
        DB::beginTransaction();
        try {
            $memberTrainer = MemberTrainer::with('trainer')->findOrFail($memberTrainerId);
            $trainer = $memberTrainer->trainer;

            // Cek apakah trainer sedang melatih member lain
            if ($trainer->isTraining()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Anda sedang melatih member lain. Selesaikan sesi tersebut terlebih dahulu.');
            }

            // Cek apakah member masih punya sisa sesi
            if ($memberTrainer->sisa_sesi <= 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Sesi training untuk member ini sudah habis.');
            }

            // Update status sesi jadi aktif
            $memberTrainer->update([
                'is_session_active' => true,
                'session_started_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Sesi training dimulai untuk ' . $memberTrainer->anggota->name);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memulai sesi', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memulai sesi: ' . $e->getMessage());
        }
    }

    /**
     * Selesaikan sesi training (ubah status jadi CLOSED & catat log)
     */
    public function endSession($memberTrainerId)
    {
        DB::beginTransaction();
        try {
            $memberTrainer = MemberTrainer::with('trainer', 'paketPersonalTrainer')->findOrFail($memberTrainerId);
            $trainer = $memberTrainer->trainer;

            // Cek apakah sesi sedang aktif
            if (!$memberTrainer->is_session_active) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Tidak ada sesi aktif untuk member ini.');
            }

            // Hitung durasi sesi
            $duration = $memberTrainer->session_started_at 
                ? now()->diffInMinutes($memberTrainer->session_started_at) 
                : 0;

            // ✅ PERBAIKAN: Kurangi sisa sesi (decrement, bukan increment!)
            $memberTrainer->decrement('sesi', 1);

            // Update trainer: kurangi sesi_belum_dijalani, tambah sesi_sudah_dijalani
            $trainer->decrement('sesi_belum_dijalani', 1);
            $trainer->increment('sesi_sudah_dijalani', 1);

            // Catat log sesi untuk member_trainer
            SesiMemberTrainer::create([
                'id_member_trainer' => $memberTrainer->id,
                'type' => 'out', // Sesi selesai
                'sesi' => 1,
                'current_sesi' => $memberTrainer->sesi, // Sisa sesi setelah dikurangi
                'description' => "Sesi training selesai (durasi: {$duration} menit)",
            ]);

            // Catat log sesi untuk trainer
            SesiTrainer::create([
                'id_trainer' => $trainer->id,
                'type' => 'out', // Sesi selesai
                'sesi' => 1,
                'current_sesi' => $trainer->sesi_sudah_dijalani,
                'description' => "Melatih {$memberTrainer->anggota->name} (durasi: {$duration} menit)",
            ]);

            // Set status jadi tidak aktif
            $memberTrainer->update([
                'is_session_active' => false,
                'session_started_at' => null,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Sesi training selesai untuk ' . $memberTrainer->anggota->name . '. Durasi: ' . $duration . ' menit.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyelesaikan sesi', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menyelesaikan sesi: ' . $e->getMessage());
        }
    }

    /**
     * Lihat riwayat/log sesi trainer
     */
    public function sessionLogs()
    {
        $user = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        $logs = SesiTrainer::where('id_trainer', $trainer->id)
            ->latest()
            ->paginate(20);

        return view('pages.trainer.session-logs', compact('trainer', 'logs'));
    }
}