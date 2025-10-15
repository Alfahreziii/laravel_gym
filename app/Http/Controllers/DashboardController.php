<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\PembayaranMembership;
use App\Models\PembayaranMemberTrainer;

class DashboardController extends Controller
{
    public function index()
    {
        // Total semua anggota
        $totalMember = Anggota::count();

        // Member aktif -> cek accessor StatusKeanggotaan
        $memberAktif = Anggota::all()->filter(function ($anggota) {
            return $anggota->status_keanggotaan; // true kalau aktif
        })->count();

        // Member In Gym (misalnya kalau lagi ada absensi dengan status "checkin")
        // Asumsi ada relasi kehadiran dan kolom "status" di tabel kehadirans
        $memberInGym = Anggota::whereHas('kehadirans', function($q){
            $q->where('status', 'in'); 
        })->count();


        // Ambil semua pembayaran dari kedua tabel
        $membershipPayments = PembayaranMembership::select('tgl_bayar', 'jumlah_bayar')
            ->get();

        $trainerPayments = PembayaranMemberTrainer::select('tgl_bayar', 'jumlah_bayar')
            ->get();

        // Satukan keduanya
        $allPayments = $membershipPayments->concat($trainerPayments);

        // Hitung total revenue
        $totalRevenue = $allPayments->sum('jumlah_bayar');

        // Hitung per bulan (untuk line chart)
        $monthlyRevenue = $allPayments
            ->groupBy(function ($item) {
                return date('M', strtotime($item->tgl_bayar)); // Jan, Feb, dst
            })
            ->map(function ($row) {
                return $row->sum('jumlah_bayar');
            });

        // Pastikan 12 bulan ada meskipun 0
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthlyData = [];
        foreach ($months as $m) {
            $monthlyData[] = $monthlyRevenue[$m] ?? 0;
        }

        // Hitung harian (untuk bar chart weekly)
        $weeklyRevenue = $allPayments
            ->groupBy(function ($item) {
                return date('D', strtotime($item->tgl_bayar)); // Sun, Mon, dst
            })
            ->map(function ($row) {
                return $row->sum('jumlah_bayar');
            });

        $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $weeklyData = [];
        foreach ($days as $d) {
            $weeklyData[] = $weeklyRevenue[$d] ?? 0;
        }

        // Hitung untuk Donut chart: Active/New/Total (contoh dummy)
        $donutData = [
            'active' => 500,
            'new'    => 200,
            'total'  => $totalRevenue,
        ];

        return view('pages.dashboard.index', compact(
            'totalRevenue',
            'monthlyData',
            'weeklyData',
            'donutData',
            'totalMember', 
            'memberAktif', 
            'memberInGym'
        ));
    }

}
