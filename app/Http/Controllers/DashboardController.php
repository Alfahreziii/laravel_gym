<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\PembayaranMembership;
use App\Models\PembayaranMemberTrainer;
use App\Models\KehadiranMember;

class DashboardController extends Controller
{
    public function index()
    {
        // Member yang hadir hari ini
        $memberInGymToday = KehadiranMember::with('anggota')
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->get()
            ->groupBy('rfid')
            ->map(fn($items) => $items->first())
            ->filter(fn($item) => strtolower($item->status) === 'in')
            ->values();

        $kehadiranmembers = KehadiranMember::with('anggota')->latest()->get();
        $totalMember = Anggota::count();
        $memberAktif = Anggota::all()->filter(fn($anggota) => $anggota->status_keanggotaan)->count();
        $memberInGym = KehadiranMember::with('anggota')
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->get()
            ->groupBy('rfid')
            ->map(fn($items) => $items->first())
            ->filter(fn($item) => strtolower($item->status) === 'in')
            ->count();


        // Semua pembayaran
        $membershipPayments = PembayaranMembership::select('tgl_bayar', 'jumlah_bayar')->get();
        $trainerPayments = PembayaranMemberTrainer::select('tgl_bayar', 'jumlah_bayar')->get();
        $allPayments = $membershipPayments->concat($trainerPayments);

        $totalRevenue = $allPayments->sum('jumlah_bayar');

        // Revenue per bulan (Monthly)
        $monthlyRevenue = $allPayments
            ->groupBy(fn($item) => date('M', strtotime($item->tgl_bayar)))
            ->map(fn($row) => $row->sum('jumlah_bayar'));
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthlyData = array_map(fn($m) => $monthlyRevenue[$m] ?? 0, $months);

        // Revenue per hari (Weekly)
        $weeklyRevenue = $allPayments
            ->groupBy(fn($item) => date('D', strtotime($item->tgl_bayar)))
            ->map(fn($row) => $row->sum('jumlah_bayar'));
        $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $weeklyData = array_map(fn($d) => $weeklyRevenue[$d] ?? 0, $days);

        return view('pages.dashboard.index', compact(
            'totalRevenue',
            'monthlyData',
            'weeklyData',
            'totalMember', 
            'memberAktif', 
            'memberInGym',
            'kehadiranmembers',
            'memberInGymToday'
        ));
    }
}
