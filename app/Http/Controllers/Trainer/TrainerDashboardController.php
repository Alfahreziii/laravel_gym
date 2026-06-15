<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trainer;
use App\Models\MemberTrainer;
use App\Models\SesiMemberTrainer;
use App\Models\SesiTrainer;
use App\Models\KehadiranMember;
use App\Models\PlaylistTrainer;
use App\Models\PlaylistMemberTrainer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Concerns\ExportsExcel;

class TrainerDashboardController extends Controller
{
    use ExportsExcel;

    public function index()
    {
        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        $memberInGymToday = KehadiranMember::whereDate('created_at', now()->toDateString())
            ->latest()
            ->get()
            ->groupBy('rfid')
            ->map(fn($items) => $items->first())
            ->filter(fn($item) => strtolower($item->status) === 'in')
            ->pluck('rfid')
            ->toArray();

        $today = now()->toDateString();

        $memberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'sesiLogs'])
            ->where('id_trainer', $trainer->id)
            ->whereDate('tgl_mulai', '<=', $today)
            ->whereDate('tgl_selesai', '>=', $today)
            ->where('sesi', '>', 0)
            ->get()
            ->map(function ($mt) use ($memberInGymToday) {
                $mt->is_checked_in = in_array($mt->anggota->id_kartu ?? null, $memberInGymToday);
                return $mt;
            });

        return view('pages.trainer.dashboard', compact('trainer', 'memberTrainers'));
    }

    public function waiting()
    {
        return view('pages.trainer.waiting');
    }

    public function startSession($memberTrainerId)
    {
        DB::beginTransaction();
        try {
            $memberTrainer = MemberTrainer::with('trainer', 'anggota')->findOrFail($memberTrainerId);
            $trainer       = $memberTrainer->trainer;

            $latestKehadiran = KehadiranMember::where('rfid', $memberTrainer->anggota->id_kartu)
                ->whereDate('created_at', now()->toDateString())
                ->orderBy('created_at', 'desc')
                ->first();

            Log::info('Check-in validation', [
                'member_id'       => $memberTrainer->anggota->id,
                'member_name'     => $memberTrainer->anggota->name,
                'id_kartu'        => $memberTrainer->anggota->id_kartu,
                'kehadiran_found' => $latestKehadiran ? 'yes' : 'no',
                'status'          => $latestKehadiran ? $latestKehadiran->status : 'N/A'
            ]);

            if (!$latestKehadiran) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Member belum melakukan check-in hari ini.');
            }

            if (strtolower(trim($latestKehadiran->status)) !== 'in') {
                DB::rollBack();
                return redirect()->back()->with('error', 'Status kehadiran member bukan "in". Status saat ini: ' . $latestKehadiran->status);
            }

            if ($trainer->isTraining()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Anda sedang melatih member lain. Selesaikan sesi tersebut terlebih dahulu.');
            }

            if ($memberTrainer->sisa_sesi <= 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Sesi training untuk member ini sudah habis.');
            }

            $memberTrainer->update([
                'is_session_active'  => true,
                'session_started_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('trainer.monitoring')->with('success', 'Sesi training dimulai untuk ' . $memberTrainer->anggota->name);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memulai sesi', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memulai sesi: ' . $e->getMessage());
        }
    }

    public function endSession($memberTrainerId)
    {
        DB::beginTransaction();
        try {
            $memberTrainer = MemberTrainer::with('trainer', 'paketPersonalTrainer')->findOrFail($memberTrainerId);
            $trainer       = $memberTrainer->trainer;

            if (!$memberTrainer->is_session_active) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Tidak ada sesi aktif untuk member ini.');
            }

            // Hitung sesi ke berapa ini
            $sesiKe = $memberTrainer->paketPersonalTrainer->jumlah_sesi - $memberTrainer->sesi + 1;

            // Cek apakah trainer punya playlist
            $totalPlaylist = PlaylistTrainer::where('id_trainer', $trainer->id)->count();

            if ($totalPlaylist > 0) {
                // Cek apakah semua playlist sudah diisi untuk sesi ini
                $savedCount = PlaylistMemberTrainer::where('id_member_trainer', $memberTrainer->id)
                    ->where('sesi_ke', $sesiKe)
                    ->count();

                if ($savedCount < $totalPlaylist) {
                    DB::rollBack();
                    return redirect()->route('trainer.monitoring')
                        ->with('error', 'Semua playlist latihan harus dicatat terlebih dahulu sebelum menyelesaikan sesi. (' . $savedCount . '/' . $totalPlaylist . ' dicatat)');
                }
            }

            $duration = $memberTrainer->session_started_at
                ? now()->diffInMinutes($memberTrainer->session_started_at, true)
                : 0;

            $memberTrainer->decrement('sesi', 1);
            $trainer->decrement('sesi_belum_dijalani', 1);
            $trainer->increment('sesi_sudah_dijalani', 1);

            SesiMemberTrainer::create([
                'id_member_trainer' => $memberTrainer->id,
                'type'              => 'out',
                'sesi'              => 1,
                'current_sesi'      => $memberTrainer->sesi,
                'description'       => "Sesi training selesai (durasi: {$duration} menit)",
            ]);

            SesiTrainer::create([
                'id_trainer'   => $trainer->id,
                'type'         => 'out',
                'sesi'         => 1,
                'current_sesi' => $trainer->sesi_sudah_dijalani,
                'description'  => "Melatih {$memberTrainer->anggota->name} (durasi: {$duration} menit)",
            ]);

            $memberTrainer->update([
                'is_session_active'  => false,
                'session_started_at' => null,
            ]);

            DB::commit();

            return redirect()->route('trainer.dashboard')
                ->with('success', 'Sesi training selesai untuk ' . $memberTrainer->anggota->name . '. Durasi: ' . $duration . ' menit.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyelesaikan sesi', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menyelesaikan sesi: ' . $e->getMessage());
        }
    }

    public function sessionLogs()
    {
        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        $logs = SesiTrainer::where('id_trainer', $trainer->id)
            ->latest()
            ->paginate(20);

        return view('pages.trainer.session-logs', compact('trainer', 'logs'));
    }

    public function exportSessionLogsPdf(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:all,single,range,daily',
            'bulan'       => 'nullable|required_if:filter_type,single|integer|between:1,12',
            'tahun'       => 'nullable|required_if:filter_type,single|integer|min:2000',
            'bulan_dari'  => 'nullable|required_if:filter_type,range|integer|between:1,12',
            'tahun_dari'  => 'nullable|required_if:filter_type,range|integer|min:2000',
            'bulan_sampai' => 'nullable|required_if:filter_type,range|integer|between:1,12',
            'tahun_sampai' => 'nullable|required_if:filter_type,range|integer|min:2000',
            'tgl_dari'    => 'nullable|required_if:filter_type,daily|date',
            'tgl_sampai'  => 'nullable|required_if:filter_type,daily|date|after_or_equal:tgl_dari',
        ]);

        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        $filterType = $request->filter_type;
        $filterInfo = 'Semua Tanggal';

        $query = SesiTrainer::where('id_trainer', $trainer->id)->latest();

        if ($filterType === 'single') {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $query->whereYear('created_at', $tahun)->whereMonth('created_at', $bulan);
            $filterInfo = Carbon::create($tahun, $bulan)->locale('id')->isoFormat('MMMM YYYY');
        } elseif ($filterType === 'range') {
            $dariTanggal   = Carbon::create($request->tahun_dari, $request->bulan_dari, 1)->startOfMonth();
            $sampaiTanggal = Carbon::create($request->tahun_sampai, $request->bulan_sampai, 1)->endOfMonth();
            $query->whereBetween('created_at', [$dariTanggal, $sampaiTanggal]);
            $filterInfo = $dariTanggal->locale('id')->isoFormat('MMMM YYYY') . ' - ' .
                $sampaiTanggal->locale('id')->isoFormat('MMMM YYYY');
        } elseif ($filterType === 'daily') {
            $dariTanggal   = Carbon::parse($request->tgl_dari)->startOfDay();
            $sampaiTanggal = Carbon::parse($request->tgl_sampai)->endOfDay();
            $query->whereBetween('created_at', [$dariTanggal, $sampaiTanggal]);
            $filterInfo = $dariTanggal->format('d M Y') . ' - ' . $sampaiTanggal->format('d M Y');
        }

        $logs = $query->get()->map(function ($log) {
            $log->clean_description = preg_replace_callback(
                '/durasi:\s*(-?[\d.]+)\s*menit/i',
                function ($matches) {
                    $durasi = round(abs((float) $matches[1]));
                    return "durasi: {$durasi} menit";
                },
                $log->description,
            );
            return $log;
        });

        $totalSesi = $logs->where('type', 'out')->count();

        $pdf = Pdf::loadView('pages.trainer.session-logs-pdf', compact('trainer', 'logs', 'filterInfo', 'totalSesi'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('riwayat-sesi-' . $trainer->name . '-' . now()->format('Ymd') . '.pdf');
    }

    public function exportSessionLogsExcel(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:all,single,range,daily',
            'bulan'       => 'nullable|required_if:filter_type,single|integer|between:1,12',
            'tahun'       => 'nullable|required_if:filter_type,single|integer|min:2000',
            'bulan_dari'  => 'nullable|required_if:filter_type,range|integer|between:1,12',
            'tahun_dari'  => 'nullable|required_if:filter_type,range|integer|min:2000',
            'bulan_sampai' => 'nullable|required_if:filter_type,range|integer|between:1,12',
            'tahun_sampai' => 'nullable|required_if:filter_type,range|integer|min:2000',
            'tgl_dari'    => 'nullable|required_if:filter_type,daily|date',
            'tgl_sampai'  => 'nullable|required_if:filter_type,daily|date|after_or_equal:tgl_dari',
        ]);

        $user    = Auth::user();
        $trainer = Trainer::where('id', $user->trainer_id ?? 0)->first();

        if (!$trainer) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai trainer.');
        }

        $filterType = $request->filter_type;
        $filterInfo = 'Semua Tanggal';

        $query = SesiTrainer::where('id_trainer', $trainer->id)->latest();

        if ($filterType === 'single') {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $query->whereYear('created_at', $tahun)->whereMonth('created_at', $bulan);
            $filterInfo = Carbon::create($tahun, $bulan)->locale('id')->isoFormat('MMMM YYYY');
        } elseif ($filterType === 'range') {
            $dariTanggal   = Carbon::create($request->tahun_dari, $request->bulan_dari, 1)->startOfMonth();
            $sampaiTanggal = Carbon::create($request->tahun_sampai, $request->bulan_sampai, 1)->endOfMonth();
            $query->whereBetween('created_at', [$dariTanggal, $sampaiTanggal]);
            $filterInfo = $dariTanggal->locale('id')->isoFormat('MMMM YYYY') . ' - ' .
                $sampaiTanggal->locale('id')->isoFormat('MMMM YYYY');
        } elseif ($filterType === 'daily') {
            $dariTanggal   = Carbon::parse($request->tgl_dari)->startOfDay();
            $sampaiTanggal = Carbon::parse($request->tgl_sampai)->endOfDay();
            $query->whereBetween('created_at', [$dariTanggal, $sampaiTanggal]);
            $filterInfo = $dariTanggal->format('d M Y') . ' - ' . $sampaiTanggal->format('d M Y');
        }

        $logs = $query->get()->map(function ($log) {
            $log->clean_description = preg_replace_callback(
                '/durasi:\s*(-?[\d.]+)\s*menit/i',
                function ($matches) {
                    $durasi = round(abs((float) $matches[1]));
                    return "durasi: {$durasi} menit";
                },
                $log->description,
            );
            return $log;
        });

        $totalSesi = $logs->where('type', 'out')->count();

        $title = 'Riwayat Sesi Training - ' . $trainer->name;

        $rows = '';
        foreach ($logs as $index => $log) {
            $tipe = $log->type === 'in' ? 'Masuk' : 'Selesai';
            $rows .= '<tr>'
                . '<td class="center">' . ($index + 1) . '</td>'
                . '<td class="center">' . $log->created_at->format('d/m/Y H:i') . '</td>'
                . '<td class="center">' . $tipe . '</td>'
                . '<td class="center">' . $log->sesi . '</td>'
                . '<td class="center">' . $log->current_sesi . '</td>'
                . '<td>' . $this->exEsc($log->clean_description) . '</td>'
                . '</tr>';
        }

        if ($logs->isEmpty()) {
            $rows = '<tr><td colspan="6" class="center">Tidak ada data pada periode ini</td></tr>';
        }

        $html = '<table>';
        $html .= '<tr><td colspan="6" class="title">' . $this->exEsc($title) . '</td></tr>';
        $html .= '<tr><td colspan="6" class="subtitle">Dicetak: ' . now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') . ' WIB &nbsp;|&nbsp; Periode: ' . $this->exEsc($filterInfo) . '</td></tr>';
        $html .= '<tr><td colspan="6"></td></tr>';
        $html .= '<tr>'
            . '<td colspan="2" class="summary-label">Total Data</td><td colspan="1" class="summary-val">' . $logs->count() . ' log</td>'
            . '<td colspan="2" class="summary-label">Total Sesi Selesai</td><td colspan="1" class="summary-val">' . $totalSesi . ' sesi</td>'
            . '</tr>';
        $html .= '<tr><td colspan="6"></td></tr>';
        $html .= '<tr>'
            . '<th>No</th><th>Tanggal & Waktu</th><th>Tipe</th><th>Sesi</th><th>Total Sesi</th><th>Keterangan</th>'
            . '</tr>';
        $html .= $rows;
        $html .= '</table>';

        $filename = 'riwayat-sesi-' . $trainer->name . '-' . now()->format('Ymd') . '.xls';

        return $this->excelDownload($html, $title, $filename);
    }
}
