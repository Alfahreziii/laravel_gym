<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiwayatGajiTrainer;
use App\Models\Trainer;
use App\Models\MemberTrainer;
use App\Models\PembayaranMemberTrainer;
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
                // Total sesi keseluruhan dari member_trainers
                $totalSesiKeseluruhan = MemberTrainer::where('id_trainer', $trainer->id)
                    ->join('paket_personal_trainers', 'member_trainers.id_paket_personal_trainer', '=', 'paket_personal_trainers.id')
                    ->sum('paket_personal_trainers.jumlah_sesi');

                // Total sesi yang sudah dibayar
                $totalSesiDibayar = RiwayatGajiTrainer::where('id_trainer', $trainer->id)
                    ->sum('jumlah_sesi');

                // Sesi belum dibayar
                $sesiBelumDibayar = $totalSesiKeseluruhan - $totalSesiDibayar;

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

        return view('pages.trainer.riwayatgajitrainer.index', compact('gajiTrainers'));
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

            // Ambil ID member_trainers yang pembayaran pertamanya ada di periode ini
            $memberTrainerIds = PembayaranMemberTrainer::whereBetween('tgl_bayar', [$tglMulai, $tglSelesai])
                ->select('id_member_trainer', DB::raw('MIN(id) as first_payment_id'))
                ->groupBy('id_member_trainer')
                ->pluck('first_payment_id');

            Log::info('First Payment IDs in period: ', $memberTrainerIds->toArray());

            // Ambil member_trainers yang valid (milik trainer ini dan pembayaran pertamanya di periode ini)
            $validMemberTrainers = MemberTrainer::whereIn('id', function($query) use ($memberTrainerIds) {
                    $query->select('id_member_trainer')
                        ->from('pembayaran_member_trainers')
                        ->whereIn('id', $memberTrainerIds);
                })
                ->where('id_trainer', $trainerId)
                ->with('paketPersonalTrainer')
                ->get();

            Log::info('Valid Member Trainers Count: ' . $validMemberTrainers->count());
            Log::info('Valid Member Trainers IDs: ', $validMemberTrainers->pluck('id')->toArray());

            // Hitung total sesi
            $jumlahSesi = $validMemberTrainers->sum(function($memberTrainer) {
                return $memberTrainer->paketPersonalTrainer->jumlah_sesi ?? 0;
            });

            Log::info('Jumlah Sesi: ' . $jumlahSesi);
            
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
                    'valid_member_trainers_count' => $validMemberTrainers->count(),
                    'first_payment_ids_count' => $memberTrainerIds->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('=== ERROR IN GET PAYMENT DATA ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
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

            // Ambil ID member_trainers yang pembayaran pertamanya ada di periode ini
            $memberTrainerIds = PembayaranMemberTrainer::whereBetween('tgl_bayar', [$tglMulai, $tglSelesai])
                ->select('id_member_trainer', DB::raw('MIN(id) as first_payment_id'))
                ->groupBy('id_member_trainer')
                ->pluck('first_payment_id');

            // Ambil member_trainers yang valid
            $validMemberTrainers = MemberTrainer::whereIn('id', function($query) use ($memberTrainerIds) {
                    $query->select('id_member_trainer')
                        ->from('pembayaran_member_trainers')
                        ->whereIn('id', $memberTrainerIds);
                })
                ->where('id_trainer', $request->id_trainer)
                ->with('paketPersonalTrainer')
                ->get();

            // Hitung total sesi
            $jumlahSesi = $validMemberTrainers->sum(function($memberTrainer) {
                return $memberTrainer->paketPersonalTrainer->jumlah_sesi ?? 0;
            });

            Log::info('Jumlah Sesi untuk disimpan: ' . $jumlahSesi);

            if ($jumlahSesi <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada sesi yang perlu dibayar dalam periode ini'
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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

            return view('pages.trainer.riwayatgajitrainer.history', compact(
                'trainer',
                'riwayatGaji',
                'totalPembayaran',
                'totalSesi',
                'totalBonus'
            ));
        } catch (\Exception $e) {
            Log::error('Error in history page: ' . $e->getMessage());
            
            return redirect()->route('riwayat-gaji-trainer.index')
                ->with('danger', 'Gagal memuat history: ' . $e->getMessage());
        }
    }
}