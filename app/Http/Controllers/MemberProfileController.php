<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\PaketMembership;
use App\Models\PaketPersonalTrainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MemberProfileController extends Controller
{
    /**
     * Tampilkan profil member yang sedang login
     */
    public function index()
    {
        $user = Auth::user();

        // Pastikan user adalah member
        if (!$user->isMember()) {
            abort(403, 'Unauthorized access');
        }

        $anggota = $user->anggota;

        if (!$anggota) {
            return redirect()->route('dashboard')
                ->with('error', 'Data anggota tidak ditemukan');
        }

        // Load relasi yang diperlukan
        $anggota->load([
            'anggotaMemberships' => function ($query) {
                $query->latest('tgl_selesai');
            },
            'kehadirans' => function ($query) {
                $query->latest()->limit(10);
            },
            'memberTrainers.trainer',
            'memberTrainers.paketPersonalTrainer',
        ]);

        // Statistik kehadiran
        $totalKehadiran = $anggota->kehadirans->count();
        $kehadiranBulanIni = $anggota->kehadirans()
            ->whereBetween('created_at', tenant_month_range())
            ->count();

        // Paket Personal Trainer yang relevan: aktif dulu, kalau tidak ada pakai yang terbaru
        $ptMembership = $anggota->memberTrainers
            ->filter(fn ($mt) => $mt->is_active)
            ->sortByDesc('tgl_selesai')
            ->first()
            ?? $anggota->memberTrainers->sortByDesc('tgl_selesai')->first();

        $ptSisaSesi      = 0;
        $ptTotalSesi     = 0;
        $ptSesiDijalani  = 0;
        $ptDaysRemaining = 0;
        $ptSessionPct    = 0;

        if ($ptMembership) {
            $ptSisaSesi      = $ptMembership->sisa_sesi;
            $ptTotalSesi     = optional($ptMembership->paketPersonalTrainer)->jumlah_sesi ?? 0;
            $ptSesiDijalani  = max($ptTotalSesi - $ptSisaSesi, 0);
            $ptDaysRemaining = max(0, tenant_today()->diffInDays($ptMembership->tgl_selesai, false));
            $ptSessionPct    = $ptTotalSesi > 0 ? (int) round($ptSesiDijalani / $ptTotalSesi * 100) : 0;
        }

        return view('pages.member.profile', compact(
            'anggota',
            'totalKehadiran',
            'kehadiranBulanIni',
            'ptMembership',
            'ptSisaSesi',
            'ptTotalSesi',
            'ptSesiDijalani',
            'ptDaysRemaining',
            'ptSessionPct'
        ));
    }

    /**
     * Tampilkan katalog paket membership & personal trainer untuk member
     */
    public function paket()
    {
        $user = Auth::user();

        if (!$user->isMember()) {
            abort(403, 'Unauthorized access');
        }

        $anggota = $user->anggota;

        if (!$anggota) {
            return redirect()->route('dashboard')
                ->with('error', 'Data anggota tidak ditemukan');
        }

        $paketMemberships = PaketMembership::with('kategori')->get();
        $paketTrainers    = PaketPersonalTrainer::get();
        $tenant           = app('tenant');

        return view('pages.member.paket', compact('anggota', 'paketMemberships', 'paketTrainers', 'tenant'));
    }

    /**
     * Download kartu member dengan barcode (PDF)
     */
    public function downloadCard()
    {
        $user = Auth::user();

        if (!$user->isMember()) {
            abort(403, 'Unauthorized access');
        }

        $anggota = $user->anggota;

        if (!$anggota) {
            return redirect()->back()
                ->with('error', 'Data anggota tidak ditemukan');
        }

        // Generate PDF kartu member
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.member.card-pdf', compact('anggota'));

        $pdf->setPaper([0, 0, 283.465, 425.197], 'portrait'); // Ukuran kartu ID (3.5 x 2.5 inch)

        $filename = 'Kartu_Member_' . $anggota->id_kartu . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Tampilkan barcode dalam format besar untuk di-print
     */
    public function showBarcode()
    {
        $user = Auth::user();

        if (!$user->isMember()) {
            abort(403, 'Unauthorized access');
        }

        $anggota = $user->anggota;

        if (!$anggota) {
            return redirect()->back()
                ->with('error', 'Data anggota tidak ditemukan');
        }

        return view('pages.member.barcode', compact('anggota'));
    }
}
