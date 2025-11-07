<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\MemberTrainer;
use App\Models\Trainer;
use Illuminate\Support\Facades\Auth;

class TrainerNavbarComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        
        // Cek apakah user adalah trainer
        if (!$user || !$user->hasRole('trainer')) {
            $view->with('trainerNotifications', collect([]));
            return;
        }

        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();
        
        if (!$trainer) {
            $view->with('trainerNotifications', collect([]));
            return;
        }

        // Ambil notifikasi untuk trainer
        $notifications = collect();

        // 1. Member dengan sesi hampir habis (≤ 2 sesi)
        $lowSessionMembers = MemberTrainer::with('anggota')
            ->where('id_trainer', $trainer->id)
            ->where('status_pembayaran', 'Lunas')
            ->where('sesi', '>', 0)
            ->where('sesi', '<=', 2)
            ->get()
            ->map(function($mt) {
                return [
                    'type' => 'low_session',
                    'title' => $mt->anggota->name,
                    'message' => "Sisa {$mt->sesi} sesi",
                    'icon' => 'mdi:alert-circle-outline',
                    'color' => 'warning',
                    'url' => route('trainer.dashboard')
                ];
            });

        // 2. Member dengan sesi aktif yang belum diselesaikan > 2 jam
        $stuckSessions = MemberTrainer::with('anggota')
            ->where('id_trainer', $trainer->id)
            ->where('is_session_active', true)
            ->where('session_started_at', '<', now()->subHours(2))
            ->get()
            ->map(function($mt) {
                return [
                    'type' => 'stuck_session',
                    'title' => $mt->anggota->name,
                    'message' => 'Sesi aktif > 2 jam',
                    'icon' => 'mdi:clock-alert-outline',
                    'color' => 'danger',
                    'url' => route('trainer.dashboard')
                ];
            });

        // 3. Member baru yang belum pernah training
        $newMembers = MemberTrainer::with(['anggota', 'paketPersonalTrainer'])
            ->where('id_trainer', $trainer->id)
            ->where('status_pembayaran', 'Lunas')
            ->whereDoesntHave('sesiLogs')
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->map(function($mt) {
                return [
                    'type' => 'new_member',
                    'title' => $mt->anggota->name,
                    'message' => 'Member baru belum training',
                    'icon' => 'mdi:account-plus-outline',
                    'color' => 'info',
                    'url' => route('trainer.dashboard')
                ];
            });

        // Gabungkan semua notifikasi
        $notifications = $notifications
            ->concat($stuckSessions)
            ->concat($lowSessionMembers)
            ->concat($newMembers)
            ->take(5); // Batasi 5 notifikasi

        $view->with('trainerNotifications', $notifications);
    }
}