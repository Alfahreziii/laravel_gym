<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\PembayaranMembership;
use App\Models\PembayaranMemberTrainer;
use App\Models\KehadiranMember;
use App\Models\TransaksiKeuangan;
use App\Models\AkunKeuangan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
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

        // ========================================
        // DAFTAR TAHUN TERSEDIA
        // ========================================
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12
        
        // Ambil tahun dari pembayaran membership & trainer
        $membershipYears = PembayaranMembership::selectRaw('YEAR(tgl_bayar) as year')
            ->distinct()
            ->pluck('year');
        
        $trainerYears = PembayaranMemberTrainer::selectRaw('YEAR(tgl_bayar) as year')
            ->distinct()
            ->pluck('year');
        
        $paymentYears = $membershipYears->merge($trainerYears)->unique()->sort()->values();
        
        // Ambil tahun dari transaksi produk
        $productYears = TransaksiKeuangan::selectRaw('YEAR(tanggal) as year')
            ->distinct()
            ->pluck('year');
        
        // Gabungkan semua tahun
        $availableYears = $paymentYears->merge($productYears)->unique()->sort()->values();
        
        // Jika tidak ada data, tambahkan tahun sekarang
        if ($availableYears->isEmpty()) {
            $availableYears = collect([$currentYear]);
        }

        // ========================================
        // CHART 1: Membership & Personal Trainer
        // ========================================
        $membershipPayments = PembayaranMembership::select('tgl_bayar', 'jumlah_bayar')->get();
        $trainerPayments = PembayaranMemberTrainer::select('tgl_bayar', 'jumlah_bayar')->get();
        $allPayments = $membershipPayments->concat($trainerPayments);

        $totalRevenue = $allPayments->sum('jumlah_bayar');

        // Data per tahun untuk Membership & Trainer
        $membershipDataByYear = [];
        foreach ($availableYears as $year) {
            $yearPayments = $allPayments->filter(function($item) use ($year) {
                return date('Y', strtotime($item->tgl_bayar)) == $year;
            });

            // Revenue per bulan
            $monthlyRevenue = $yearPayments
                ->groupBy(fn($item) => date('M', strtotime($item->tgl_bayar)))
                ->map(fn($row) => $row->sum('jumlah_bayar'));
            
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $monthlyData = array_map(fn($m) => $monthlyRevenue[$m] ?? 0, $months);

            // Revenue per minggu untuk setiap bulan
            $weeklyDataByMonth = [];
            for ($month = 1; $month <= 12; $month++) {
                $weeklyDataByMonth[$month] = $this->getWeeklyDataForMonth($yearPayments, $year, $month, 'tgl_bayar', 'jumlah_bayar');
            }

            $membershipDataByYear[$year] = [
                'monthly' => $monthlyData,
                'weekly' => $weeklyDataByMonth,
            ];
        }

        // ========================================
        // CHART 2: Penjualan Produk
        // ========================================
        $akunPenjualanProduk = AkunKeuangan::where('kode', 'MOD005')->first();
        
        $totalProductRevenue = 0;
        $productDataByYear = [];

        if ($akunPenjualanProduk) {
            $productSalesData = TransaksiKeuangan::where('akun_id', $akunPenjualanProduk->id)
                ->where('kredit', '>', 0)
                ->select('tanggal', 'kredit')
                ->get();

            $totalProductRevenue = $productSalesData->sum('kredit');

            // Data per tahun untuk Produk
            foreach ($availableYears as $year) {
                $yearProducts = $productSalesData->filter(function($item) use ($year) {
                    return date('Y', strtotime($item->tanggal)) == $year;
                });

                // Revenue per bulan
                $monthlyProductRevenue = $yearProducts
                    ->groupBy(fn($item) => date('M', strtotime($item->tanggal)))
                    ->map(fn($row) => $row->sum('kredit'));
                
                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                $monthlyProductData = array_map(fn($m) => $monthlyProductRevenue[$m] ?? 0, $months);

                // Revenue per minggu untuk setiap bulan
                $weeklyProductDataByMonth = [];
                for ($month = 1; $month <= 12; $month++) {
                    $weeklyProductDataByMonth[$month] = $this->getWeeklyDataForMonth($yearProducts, $year, $month, 'tanggal', 'kredit');
                }

                $productDataByYear[$year] = [
                    'monthly' => $monthlyProductData,
                    'weekly' => $weeklyProductDataByMonth,
                ];
            }
        } else {
            // Jika akun tidak ditemukan
            foreach ($availableYears as $year) {
                $weeklyDataByMonth = [];
                for ($month = 1; $month <= 12; $month++) {
                    $weeklyDataByMonth[$month] = [0, 0, 0, 0, 0, 0];
                }
                
                $productDataByYear[$year] = [
                    'monthly' => array_fill(0, 12, 0),
                    'weekly' => $weeklyDataByMonth,
                ];
            }
        }

        return view('pages.dashboard.index', compact(
            'totalRevenue',
            'totalProductRevenue',
            'membershipDataByYear',
            'productDataByYear',
            'availableYears',
            'currentYear',
            'currentMonth',
            'totalMember', 
            'memberAktif', 
            'memberInGym',
            'kehadiranmembers',
            'memberInGymToday'
        ));
    }

    /**
     * Helper untuk menghitung revenue per minggu dalam satu bulan
     */
    private function getWeeklyDataForMonth($data, $year, $month, $dateField, $amountField)
    {
        // Filter data untuk bulan tertentu
        $monthData = $data->filter(function($item) use ($year, $month, $dateField) {
            $date = strtotime($item->$dateField);
            return date('Y', $date) == $year && date('n', $date) == $month;
        });

        // Inisialisasi array untuk max 6 minggu (untuk menampung bulan dengan 31 hari)
        $weeklyData = [0, 0, 0, 0, 0, 0];

        // Kelompokkan berdasarkan minggu
        foreach ($monthData as $item) {
            $date = strtotime($item->$dateField);
            $day = (int) date('j', $date); // Tanggal 1-31
            
            // Tentukan minggu (0-5)
            $weekNumber = (int) floor(($day - 1) / 7);
            
            // Pastikan week number tidak lebih dari 5
            $weekNumber = min($weekNumber, 5);
            
            $weeklyData[$weekNumber] += $item->$amountField;
        }

        // Hitung jumlah minggu yang ada di bulan tersebut
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $weeksInMonth = (int) ceil($daysInMonth / 7);

        // Kembalikan hanya minggu yang relevan
        return array_slice($weeklyData, 0, $weeksInMonth);
    }
}