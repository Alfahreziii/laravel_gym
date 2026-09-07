<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiwayatGajiTrainer;
use App\Models\Trainer;
use App\Models\SesiTrainer;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RiwayatGajiTrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua trainer dengan data yang dibutuhkan
        $gajiTrainers = Trainer::with(['user', 'riwayatGaji', 'settingGaji'])
            ->where('status', Trainer::STATUS_AKTIF)
            ->get()
            ->map(function ($trainer) {
                // Sesi yang sudah dijalani (type='out') tapi belum ditandai dibayar
                $sesiBelumDibayar = SesiTrainer::where('id_trainer', $trainer->id)
                    ->where('type', 'out')
                    ->whereNull('id_riwayat_gaji_trainer')
                    ->count();

                // Terakhir gajian
                $terakhirGajian = $trainer->riwayatGaji()
                    ->latest('tgl_bayar')
                    ->first();

                return [
                    'id' => $trainer->id,
                    'nama' => $trainer->name,
                    'terakhir_gajian' => $terakhirGajian ? $terakhirGajian->tgl_bayar->format('d F Y') : 'Belum Pernah',
                    'sesi_belum_dibayar' => $sesiBelumDibayar,
                    'base_rate' => $trainer->settingGaji->base_rate ?? 0,
                ];
            });

        return view('pages.trainer.riwayat-gaji-trainer.index', compact('gajiTrainers'));
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = Trainer::with(['riwayatGaji', 'settingGaji'])
            ->where('status', Trainer::STATUS_AKTIF);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $total    = (clone $query)->count();
        $trainers = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        $data = $trainers->values()->map(function ($trainer, $index) use ($page, $perPage) {
            $sesiBelumDibayar = SesiTrainer::where('id_trainer', $trainer->id)
                ->where('type', 'out')
                ->whereNull('id_riwayat_gaji_trainer')
                ->count();

            $terakhirGajian = $trainer->riwayatGaji()->latest('tgl_bayar')->first();

            return [
                'no'                 => (($page - 1) * $perPage) + $index + 1,
                'id'                 => $trainer->id,
                'nama'               => $trainer->name,
                'terakhir_gajian'    => $terakhirGajian ? $terakhirGajian->tgl_bayar->format('d F Y') : 'Belum Pernah',
                'sesi_belum_dibayar' => $sesiBelumDibayar,
                'base_rate'          => $trainer->settingGaji->base_rate ?? 0,
                'history_url'        => route('riwayat-gaji-trainer.history', $trainer->id),
            ];
        });

        return response()->json([
            'data'     => $data,
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Kalender sesi per tanggal untuk 1 bulan — dipakai di form pembayaran
     * supaya admin bisa lihat tanggal mana yang sudah dijalani & sudah/belum
     * dibayar sebelum menentukan periode.
     */
    public function sesiCalendar(Request $request, $trainerId)
    {
        $request->validate([
            'bulan' => 'nullable|date_format:Y-m',
        ]);

        $bulan = $request->query('bulan', tenant_now()->format('Y-m'));
        $start = Carbon::parse($bulan . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        Trainer::findOrFail($trainerId);

        // Sesi yang dijalani per tanggal (kalender tenant), sekaligus status
        // dibayar per baris sesi (id_riwayat_gaji_trainer sudah terisi = dibayar)
        $sesiPerTanggal = SesiTrainer::where('id_trainer', $trainerId)
            ->where('type', 'out')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get(['created_at', 'id_riwayat_gaji_trainer'])
            ->groupBy(fn($s) => to_tenant_tz($s->created_at)->format('Y-m-d'));

        $days   = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateStr = $cursor->format('Y-m-d');
            $rows    = $sesiPerTanggal->get($dateStr, collect());
            $total   = $rows->count();
            $dibayar = $rows->whereNotNull('id_riwayat_gaji_trainer')->count();

            $days[$dateStr] = [
                'jumlah_sesi'    => $total,
                'jumlah_dibayar' => $dibayar,
                'is_paid'        => $total > 0 && $dibayar === $total,
            ];

            $cursor->addDay();
        }

        return response()->json([
            'success' => true,
            'bulan'   => $start->format('Y-m'),
            'days'    => $days,
        ]);
    }

    /**
     * Get data for payment form
     */
    public function getPaymentData(Request $request, $trainerId)
    {
        try {
            Log::info('=== GET PAYMENT DATA ===');
            Log::info('Trainer ID: ' . $trainerId);
            Log::info('Request Params: ', $request->all());

            // Validasi input
            $request->validate([
                'tgl_mulai' => 'required|date',
                'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai'
            ]);

            $tglMulai = Carbon::parse($request->tgl_mulai)->startOfDay();
            $tglSelesai = Carbon::parse($request->tgl_selesai)->endOfDay();

            Log::info('Tanggal Mulai: ' . $tglMulai->format('Y-m-d H:i:s'));
            Log::info('Tanggal Selesai: ' . $tglSelesai->format('Y-m-d H:i:s'));

            // Ambil setting gaji trainer
            $trainer = Trainer::with('settingGaji')->findOrFail($trainerId);
            $baseRate = $trainer->settingGaji->base_rate ?? 0;

            Log::info('Base Rate: ' . $baseRate);

            // Hitung sesi yang BENAR-BENAR sudah dijalani DAN BELUM DIBAYAR dalam
            // periode ini (pakai log SesiTrainer type='out', id_riwayat_gaji_trainer
            // masih null = belum pernah masuk pembayaran manapun)
            $jumlahSesi = SesiTrainer::where('id_trainer', $trainerId)
                ->where('type', 'out')
                ->whereNull('id_riwayat_gaji_trainer')
                ->whereBetween('created_at', [$tglMulai, $tglSelesai])
                ->count();

            Log::info('Jumlah Sesi Belum Dibayar: ' . $jumlahSesi);

            // Hitung total yang harus dibayarkan
            $totalDibayarkan = $baseRate * $jumlahSesi;

            Log::info('Total Dibayarkan: ' . $totalDibayarkan);

            return response()->json([
                'success' => true,
                'data' => [
                    'jumlah_sesi' => $jumlahSesi,
                    'base_rate' => $baseRate,
                    'total_dibayarkan' => $totalDibayarkan,
                    'formatted_base_rate' => 'Rp ' . number_format($baseRate, 0, ',', '.'),
                    'formatted_total' => 'Rp ' . number_format($totalDibayarkan, 0, ',', '.'),
                ],
                'debug' => [
                    'trainer_id' => $trainerId,
                    'tgl_mulai' => $tglMulai->format('Y-m-d'),
                    'tgl_selesai' => $tglSelesai->format('Y-m-d'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('=== ERROR IN GET PAYMENT DATA ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data. Silakan coba lagi atau hubungi admin.',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('=== STORE GAJI TRAINER ===');
        Log::info('Request Data: ', $request->all());

        $request->validate([
            'id_trainer' => 'required|exists:trainers,id',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'tgl_bayar' => 'required|date',
            'metode_pembayaran' => 'required|in:cash,transfer,e-wallet',
            'bonus' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $tglMulai = Carbon::parse($request->tgl_mulai)->startOfDay();
            $tglSelesai = Carbon::parse($request->tgl_selesai)->endOfDay();

            // Ambil setting gaji trainer
            $trainer = Trainer::with('settingGaji')->findOrFail($request->id_trainer);
            $baseRate = $trainer->settingGaji->base_rate ?? 0;

            // Kunci baris sesi yang mau dibayar (FOR UPDATE) supaya aman dari race
            // condition kalau ada 2 request submit bersamaan untuk periode overlap.
            // Hanya ambil sesi yang BELUM PERNAH dibayar (id_riwayat_gaji_trainer
            // masih null) — jadi sesi yang sudah dibayar di pembayaran manapun
            // sebelumnya TIDAK akan pernah ikut terhitung/terbayar lagi, walau
            // periode yang dipilih sekarang overlap dengan periode lama.
            $sesiIds = SesiTrainer::where('id_trainer', $request->id_trainer)
                ->where('type', 'out')
                ->whereNull('id_riwayat_gaji_trainer')
                ->whereBetween('created_at', [$tglMulai, $tglSelesai])
                ->lockForUpdate()
                ->pluck('id');

            $jumlahSesi = $sesiIds->count();

            Log::info('Jumlah Sesi untuk disimpan: ' . $jumlahSesi);

            if ($jumlahSesi <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada sesi yang belum dibayar dalam periode ini'
                ], 400);
            }

            // Hitung total
            $bonus = $request->bonus ?? 0;
            $totalDibayarkan = ($baseRate * $jumlahSesi) + $bonus;

            Log::info('Total Dibayarkan: ' . $totalDibayarkan);

            // Simpan riwayat gaji
            $riwayat = RiwayatGajiTrainer::create([
                'id_trainer' => $request->id_trainer,
                'jumlah_sesi' => $jumlahSesi,
                'tgl_mulai' => $tglMulai,
                'tgl_selesai' => $tglSelesai,
                'tgl_bayar' => $request->tgl_bayar,
                'base_rate' => $baseRate,
                'bonus' => $bonus,
                'total_dibayarkan' => $totalDibayarkan,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            Log::info('Riwayat Gaji Created: ', $riwayat->toArray());

            // Tandai sesi-sesi yang baru saja dibayar supaya tidak bisa ikut
            // terhitung lagi di pembayaran berikutnya.
            SesiTrainer::whereIn('id', $sesiIds)->update([
                'id_riwayat_gaji_trainer' => $riwayat->id,
            ]);

            // === Jurnal keuangan: catat pembayaran gaji sebagai beban ===
            // Debit  Beban Gaji Trainer (BEB003)  -> beban bertambah
            // Kredit Kas (AST001)                 -> kas berkurang
            $akunBebanGaji = AkunKeuangan::where('kode', 'BEB003')->first();
            $akunKas       = AkunKeuangan::where('kode', 'AST001')->first();

            if (! $akunBebanGaji) {
                throw new \Exception('Akun Beban Gaji Trainer (BEB003) belum ada. Jalankan seeder / insert akun BEB003 di DB tenant ini.');
            }
            if (! $akunKas) {
                throw new \Exception('Akun Kas (AST001) tidak ditemukan.');
            }

            $metodeLabel = $riwayat->metode_pembayaran_label ?? $request->metode_pembayaran;
            $deskripsiGaji = "Pembayaran gaji trainer {$trainer->name} "
                . "({$jumlahSesi} sesi, {$tglMulai->format('d/m/Y')}-{$tglSelesai->format('d/m/Y')}) via {$metodeLabel}";

            TransaksiKeuangan::create([
                'akun_id'         => $akunBebanGaji->id,
                'deskripsi'       => $deskripsiGaji,
                'debit'           => $totalDibayarkan,
                'kredit'          => 0,
                'tanggal'         => $request->tgl_bayar,
                'referensi_id'    => $riwayat->id,
                'referensi_tabel' => 'riwayat_gaji_trainers',
            ]);

            TransaksiKeuangan::create([
                'akun_id'         => $akunKas->id,
                'deskripsi'       => $deskripsiGaji,
                'debit'           => 0,
                'kredit'          => $totalDibayarkan,
                'tanggal'         => $request->tgl_bayar,
                'referensi_id'    => $riwayat->id,
                'referensi_tabel' => 'riwayat_gaji_trainers',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran gaji trainer berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== ERROR IN STORE ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi atau hubungi admin.'
            ], 500);
        }
    }
    
    /**
     * Display history pembayaran gaji trainer (separate page)
     */
    public function history($trainerId)
    {
        try {
            $trainer = Trainer::with(['user', 'settingGaji'])->findOrFail($trainerId);
            
            // Ambil semua riwayat gaji trainer, diurutkan dari yang terbaru
            $riwayatGaji = RiwayatGajiTrainer::byTrainer($trainerId)
                ->latest()
                ->paginate(15);

            // Hitung total statistik
            $totalPembayaran = RiwayatGajiTrainer::byTrainer($trainerId)->sum('total_dibayarkan');
            $totalSesi = RiwayatGajiTrainer::byTrainer($trainerId)->sum('jumlah_sesi');
            $totalBonus = RiwayatGajiTrainer::byTrainer($trainerId)->sum('bonus');

            return view('pages.trainer.riwayat-gaji-trainer.history', compact(
                'trainer',
                'riwayatGaji',
                'totalPembayaran',
                'totalSesi',
                'totalBonus'
            ));
        } catch (\Exception $e) {
            Log::error('Error in history page: ' . $e->getMessage());
            
            return redirect()->route('riwayat-gaji-trainer.index')
                ->with('danger', 'Gagal memuat history. Silakan coba lagi atau hubungi admin.');
        }
    }
}