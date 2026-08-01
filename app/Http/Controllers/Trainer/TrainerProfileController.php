<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TrainerProfileController extends Controller
{
    /**
     * Tampilkan profil trainer yang sedang login
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isTrainer()) {
            abort(403, 'Unauthorized access');
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return redirect()->route('trainer.dashboard')
                ->with('error', 'Data trainer tidak ditemukan');
        }

        $trainer->load([
            'specialisasi',
            'kehadiranTrainers' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        $totalKehadiran = $trainer->kehadiranTrainers->count();
        $kehadiranBulanIni = $trainer->kehadiranTrainers()
            ->whereBetween('created_at', tenant_month_range())
            ->count();

        return view('pages.trainer.profile.index', compact('trainer', 'totalKehadiran', 'kehadiranBulanIni'));
    }

    /**
     * Download kartu trainer dengan barcode (PDF)
     */
    public function downloadCard()
    {
        $user = Auth::user();

        if (!$user->isTrainer()) {
            abort(403, 'Unauthorized access');
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return redirect()->back()
                ->with('error', 'Data trainer tidak ditemukan');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.trainer.profile.card-pdf', compact('trainer'));

        $pdf->setPaper([0, 0, 283.465, 425.197], 'portrait'); // Ukuran kartu ID (3.5 x 2.5 inch)

        $filename = 'Kartu_Trainer_' . $trainer->rfid . '.pdf';

        return $pdf->download($filename);
    }
}
