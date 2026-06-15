<?php

namespace App\Http\Controllers\Kehadiran;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\KehadiranMember;
use App\Models\Anggota;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Controllers\Concerns\ExportsExcel;

class KehadiranMemberController extends Controller
{
    use ExportsExcel;

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

            // Statistik dari SEMUA data
            $allKehadiran = KehadiranMember::all();

            $totalKehadiran  = $allKehadiran->count();
            $totalIn         = $allKehadiran->where('status', 'in')->count();
            $totalOut        = $allKehadiran->where('status', 'out')->count();
            $totalMemberUnik = $allKehadiran->unique('rfid')->count();

            $query = KehadiranMember::query();

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

            $kehadiranMembers = $query->orderBy('created_at', 'desc')->get();

            $title = 'Laporan Kehadiran Member';
            if ($filterType !== 'all') {
                $title .= ' - ' . $filterInfo;
            }

            $pdf = Pdf::loadView('pages.admin.kehadiranmember.pdf', compact(
                'kehadiranMembers',
                'totalKehadiran',
                'totalIn',
                'totalOut',
                'totalMemberUnik',
                'title',
                'filterInfo',
                'filterType'
            ));

            $pdf->setPaper('a4', 'landscape');

            $filename = 'Laporan_Kehadiran_Member_' . date('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Gagal export PDF kehadiran member', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel dengan filter range tanggal
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'filter_type'    => 'required|in:all,range',
            'tanggal_dari'   => 'nullable|required_if:filter_type,range|date',
            'tanggal_sampai' => 'nullable|required_if:filter_type,range|date|after_or_equal:tanggal_dari',
        ]);

        try {
            $filterType = $request->filter_type;

            $allKehadiran = KehadiranMember::all();

            $totalKehadiran  = $allKehadiran->count();
            $totalIn         = $allKehadiran->where('status', 'in')->count();
            $totalOut        = $allKehadiran->where('status', 'out')->count();
            $totalMemberUnik = $allKehadiran->unique('rfid')->count();

            $query = KehadiranMember::query();

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

            $kehadiranMembers = $query->orderBy('created_at', 'desc')->get();

            $title = 'Laporan Kehadiran Member';
            if ($filterType !== 'all') {
                $title .= ' - ' . $filterInfo;
            }

            $rows = '';
            foreach ($kehadiranMembers as $index => $item) {
                $status = $item->status === 'in' ? 'CHECK IN' : 'CHECK OUT';
                $rows .= '<tr>'
                    . '<td class="center">' . ($index + 1) . '</td>'
                    . '<td>' . $this->exEsc($item->rfid) . '</td>'
                    . '<td>' . $this->exEsc($item->nama ?? '-') . '</td>'
                    . '<td>' . Carbon::parse($item->created_at)->locale('id')->isoFormat('dddd, D MMMM YYYY') . '</td>'
                    . '<td class="center">' . Carbon::parse($item->created_at)->format('H:i:s') . ' WIB</td>'
                    . '<td class="center">' . $status . '</td>'
                    . '</tr>';
            }

            if ($kehadiranMembers->isEmpty()) {
                $rows = '<tr><td colspan="6" class="center">Tidak ada data kehadiran untuk periode ini.</td></tr>';
            }

            $html = '<table>';
            $html .= '<tr><td colspan="6" class="title">' . $this->exEsc($title) . '</td></tr>';
            $html .= '<tr><td colspan="6" class="subtitle">Dicetak: ' . now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') . ' WIB &nbsp;|&nbsp; Filter Periode: ' . $this->exEsc($filterInfo) . '</td></tr>';
            $html .= '<tr><td colspan="6"></td></tr>';
            $html .= '<tr>'
                . '<td colspan="1" class="summary-label">Total Kehadiran</td><td colspan="1" class="summary-val">' . $totalKehadiran . '</td>'
                . '<td colspan="1" class="summary-label">Check In / Out</td><td colspan="1" class="summary-val">' . $totalIn . ' / ' . $totalOut . '</td>'
                . '<td colspan="1" class="summary-label">Member Unik</td><td colspan="1" class="summary-val">' . $totalMemberUnik . '</td>'
                . '</tr>';
            $html .= '<tr><td colspan="6"></td></tr>';
            $html .= '<tr>'
                . '<th>No</th><th>RFID</th><th>Nama Member</th><th>Tanggal</th><th>Waktu</th><th>Status</th>'
                . '</tr>';
            $html .= $rows;
            $html .= '</table>';

            $filename = 'Laporan_Kehadiran_Member_' . date('Y-m-d_His') . '.xls';

            return $this->excelDownload($html, $title, $filename);
        } catch (\Exception $e) {
            Log::error('Gagal export Excel kehadiran member', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar kehadiran
     */
    public function index()
    {
        $kehadiranmembers = KehadiranMember::whereDate('created_at', now()->toDateString())
            ->latest()
            ->get();

        return view('pages.admin.kehadiranmember.index', compact('kehadiranmembers'));
    }

    /**
     * Datatable
     */
    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = KehadiranMember::latest();

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
                    'delete_url' => route('kehadiranmember.destroy', $item->id),
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

        $anggota = Anggota::whereRaw('UPPER(id_kartu) = ?', [$rfid])->first();

        if (!$anggota) {
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Kartu dengan RFID ' . e($rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        $lastAttendance = KehadiranMember::whereRaw('UPPER(rfid) = ?', [$rfid])
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->first();

        $status = (!$lastAttendance || $lastAttendance->status === 'out') ? 'in' : 'out';

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kehadiran_foto', 'public');
        }

        try {
            KehadiranMember::create([
                'rfid'   => $anggota->id_kartu,
                'nama'   => $anggota->name,
                'status' => $status,
                'foto'   => $fotoPath,
            ]);

            return redirect()->route('kehadiranmember.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk ' . e($anggota->name) . ' berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data kehadiran (dan fotonya)
     */
    public function destroy(KehadiranMember $kehadiranmember)
    {
        try {
            if ($kehadiranmember->foto && Storage::disk('public')->exists($kehadiranmember->foto)) {
                Storage::disk('public')->delete($kehadiranmember->foto);
            }

            $kehadiranmember->delete();

            return redirect()->route('kehadiranmember.index')
                ->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }
}
