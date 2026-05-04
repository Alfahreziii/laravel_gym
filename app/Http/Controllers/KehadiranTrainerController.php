<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KehadiranTrainer;
use App\Models\Trainer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class KehadiranTrainerController extends Controller
{
    /**
     * Export PDF dengan filter range tanggal
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'filter_type'    => 'required|in:all,range',
            'tanggal_dari'   => 'nullable|required_if:filter_type,range|date',
            'tanggal_sampai' => 'nullable|required_if:filter_type,range|date|after_or_equal:tanggal_dari',
        ]);

        try {
            $filterType = $request->filter_type;

            $allKehadiran = KehadiranTrainer::all();

            $totalKehadiran   = $allKehadiran->count();
            $totalIn          = $allKehadiran->where('status', 'in')->count();
            $totalOut         = $allKehadiran->where('status', 'out')->count();
            $totalTrainerUnik = $allKehadiran->unique('rfid')->count();

            $query = KehadiranTrainer::query();

            $filterInfo = '';
            if ($filterType === 'range') {
                $tanggalDari   = Carbon::parse($request->tanggal_dari)->startOfDay();
                $tanggalSampai = Carbon::parse($request->tanggal_sampai)->endOfDay();

                $query->whereBetween('created_at', [$tanggalDari, $tanggalSampai]);

                $filterInfo = $tanggalDari->locale('id')->isoFormat('D MMMM YYYY') . ' - ' .
                    $tanggalSampai->locale('id')->isoFormat('D MMMM YYYY');
            } else {
                $filterInfo = 'Semua Periode';
            }

            $kehadiranTrainers = $query->orderBy('created_at', 'desc')->get();

            $title = 'Laporan Kehadiran Trainer';
            if ($filterType !== 'all') {
                $title .= ' - ' . $filterInfo;
            }

            $pdf = Pdf::loadView('pages.kehadirantrainer.pdf', compact(
                'kehadiranTrainers',
                'totalKehadiran',
                'totalIn',
                'totalOut',
                'totalTrainerUnik',
                'title',
                'filterInfo',
                'filterType'
            ));

            $pdf->setPaper('a4', 'landscape');

            $filename = 'Laporan_Kehadiran_Trainer_' . date('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Gagal export PDF kehadiran trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar kehadiran
     */
    public function index()
    {
        $kehadirantrainers = KehadiranTrainer::whereDate('created_at', now()->toDateString())
            ->latest()
            ->get();

        return view('pages.kehadirantrainer.index', compact('kehadirantrainers'));
    }

    /**
     * Datatable
     */
    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = KehadiranTrainer::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rfid', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'         => (($page - 1) * $perPage) + $index + 1,
                    'id'         => $item->id,
                    'rfid'       => $item->rfid,
                    'foto'       => $item->foto ? asset('storage/' . $item->foto) : null,
                    'name'       => $item->nama ?? '-',
                    'status'     => $item->status,
                    'time'       => $item->created_at->format('d M Y - H:i:s'),
                    'delete_url' => route('kehadirantrainer.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Menyimpan data kehadiran (dengan foto)
     */
    public function store(Request $request)
    {
        $request->validate([
            'rfid' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $rfid = strtoupper(trim($request->rfid, '0'));
        $trainer = Trainer::whereRaw('UPPER(rfid) = ?', [$rfid])->first();

        if (!$trainer) {
            return redirect()->route('kehadirantrainer.index')
                ->with('danger', 'Kartu dengan RFID ' . e($request->rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        $lastAttendance = KehadiranTrainer::where('rfid', $request->rfid)
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->first();

        $status = (!$lastAttendance || $lastAttendance->status === 'out') ? 'in' : 'out';

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kehadiran_foto', 'public');
        }

        try {
            KehadiranTrainer::create([
                'rfid'   => $trainer->rfid,
                'nama'   => $trainer->name,
                'status' => $status,
                'foto'   => $fotoPath,
            ]);

            return redirect()->route('kehadirantrainer.index')
                ->with('success', 'Absensi untuk trainer ' . e($trainer->name) . ' berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->route('kehadirantrainer.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data kehadiran (dan fotonya)
     */
    public function destroy(KehadiranTrainer $kehadirantrainer)
    {
        try {
            if ($kehadirantrainer->foto && Storage::disk('public')->exists($kehadirantrainer->foto)) {
                Storage::disk('public')->delete($kehadirantrainer->foto);
            }

            $kehadirantrainer->delete();
            return redirect()->route('kehadirantrainer.index')
                ->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('kehadirantrainer.index')
                ->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }
}
