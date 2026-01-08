<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
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
            'anggotaMemberships' => function($query) {
                $query->latest('tgl_selesai');
            },
            'kehadirans' => function($query) {
                $query->latest()->limit(10);
            }
        ]);
        
        // Statistik kehadiran
        $totalKehadiran = $anggota->kehadirans->count();
        $kehadiranBulanIni = $anggota->kehadirans()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        return view('pages.member.profile', compact('anggota', 'totalKehadiran', 'kehadiranBulanIni'));
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