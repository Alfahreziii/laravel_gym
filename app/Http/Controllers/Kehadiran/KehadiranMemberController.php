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
use App\Http\Controllers\Concerns\ResolvesMemberExpiry;

class KehadiranMemberController extends Controller
{
    use ExportsExcel;
    use ResolvesMemberExpiry;

    /**
     * Jeda minimum (detik) sebelum RFID yang sama boleh absen lagi.
     * Mencegah duplikat saat kartu/QR masih terbaca kamera berkali-kali.
     */
    private const ATTENDANCE_COOLDOWN_SECONDS = 60;

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

            $totalKehadiran  = $kehadiranMembers->count();
            $totalIn         = $kehadiranMembers->where('status', 'in')->count();
            $totalOut        = $kehadiranMembers->where('status', 'out')->count();
            $totalMemberUnik = $kehadiranMembers->unique('rfid')->count();

            $anggotaMap = $this->anggotaMapForRfids($kehadiranMembers->pluck('rfid')->all());
            $expiryByRfid = $kehadiranMembers->mapWithKeys(function ($item) use ($anggotaMap) {
                return [$item->id => $this->memberExpiryInfo($anggotaMap->get(strtoupper($item->rfid)))];
            });

            $title = 'Laporan Kehadiran Member';
            if ($filterType !== 'all') {
                $title .= ' - ' . $filterInfo;
            }
            $tenant = app('tenant');

            $pdf = Pdf::loadView('pages.admin.kehadiran.kehadiran-member.pdf', compact(
                'kehadiranMembers',
                'totalKehadiran',
                'totalIn',
                'totalOut',
                'totalMemberUnik',
                'title',
                'filterInfo',
                'filterType',
                'tenant',
                'expiryByRfid'
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
                ->with('danger', 'Gagal export PDF. Silakan coba lagi atau hubungi admin.');
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

            $totalKehadiran  = $kehadiranMembers->count();
            $totalIn         = $kehadiranMembers->where('status', 'in')->count();
            $totalOut        = $kehadiranMembers->where('status', 'out')->count();
            $totalMemberUnik = $kehadiranMembers->unique('rfid')->count();

            // Foto profil di-skip di Excel (gambar sulit diembed di format .xls HTML-table ini).
            $anggotaMap = $this->anggotaMapForRfids($kehadiranMembers->pluck('rfid')->all());

            $title = 'Laporan Kehadiran Member';
            if ($filterType !== 'all') {
                $title .= ' - ' . $filterInfo;
            }

            $rows = '';
            foreach ($kehadiranMembers as $index => $item) {
                $status = $item->status === 'in' ? 'CHECK IN' : 'CHECK OUT';
                $expiry = $this->memberExpiryInfo($anggotaMap->get(strtoupper($item->rfid)));
                $rows .= '<tr>'
                    . '<td class="center">' . ($index + 1) . '</td>'
                    . '<td>' . $this->exEsc($item->rfid) . '</td>'
                    . '<td>' . $this->exEsc($item->nama ?? '-') . '</td>'
                    . '<td>' . to_tenant_tz($item->created_at)->locale('id')->isoFormat('dddd, D MMMM YYYY') . '</td>'
                    . '<td class="center">' . to_tenant_tz($item->created_at)->format('H:i:s') . ' ' . tz_label() . '</td>'
                    . '<td class="center">' . $status . '</td>'
                    . '<td>' . $this->exEsc($expiry['expired_at'] . ' (' . $expiry['membership_status'] . ')') . '</td>'
                    . '</tr>';
            }

            if ($kehadiranMembers->isEmpty()) {
                $rows = '<tr><td colspan="7" class="center">Tidak ada data kehadiran untuk periode ini.</td></tr>';
            }

            $html = '<table>';
            $html .= '<tr><td colspan="7" class="title">' . $this->exEsc($title) . '</td></tr>';
            $html .= '<tr><td colspan="7" class="subtitle">Dicetak: ' . tenant_now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') . ' ' . tz_label() . ' &nbsp;|&nbsp; Filter Periode: ' . $this->exEsc($filterInfo) . '</td></tr>';
            $html .= '<tr><td colspan="7"></td></tr>';
            $html .= '<tr>'
                . '<td colspan="1" class="summary-label">Total Kehadiran</td><td colspan="1" class="summary-val">' . $totalKehadiran . '</td>'
                . '<td colspan="1" class="summary-label">Check In / Out</td><td colspan="1" class="summary-val">' . $totalIn . ' / ' . $totalOut . '</td>'
                . '<td colspan="1" class="summary-label">Member Unik</td><td colspan="1" class="summary-val">' . $totalMemberUnik . '</td>'
                . '<td colspan="1"></td>'
                . '</tr>';
            $html .= '<tr><td colspan="7"></td></tr>';
            $html .= '<tr>'
                . '<th>No</th><th>RFID</th><th>Nama Member</th><th>Tanggal</th><th>Waktu</th><th>Status</th><th>Expired</th>'
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
                ->with('danger', 'Gagal export Excel. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Menampilkan daftar kehadiran
     */
    public function index()
    {
        $kehadiranmembers = KehadiranMember::whereBetween('created_at', tenant_today_range())
            ->latest()
            ->get();

        return view('pages.admin.kehadiran.kehadiran-member.index', compact('kehadiranmembers'));
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

        $anggotaMap = $this->anggotaMapForRfids($data->pluck('rfid')->all());

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage, $anggotaMap) {
                $expiry = $this->memberExpiryInfo($anggotaMap->get(strtoupper($item->rfid)));

                return [
                    'no'                 => (($page - 1) * $perPage) + $index + 1,
                    'id'                 => $item->id,
                    'rfid'               => $item->rfid,
                    'foto'               => $item->foto ? asset('storage/' . $item->foto) : null,
                    'name'               => $item->nama ?? '-',
                    'status'             => $item->status,
                    'time'               => to_tenant_tz($item->created_at)->format('d M Y - H:i:s'),
                    'delete_url'         => route('kehadiranmember.destroy', $item->id),
                    'profile_photo'      => $expiry['profile_photo'],
                    'expired_at'         => $expiry['expired_at'],
                    'membership_status'  => $expiry['membership_status'],
                    'membership_type'    => $expiry['membership_type'],
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Distribusi jumlah check-in member per jam untuk hari ini (00-23).
     */
    public function kedatanganChart(Request $request)
    {
        $rows = KehadiranMember::where('status', 'in')
            ->whereBetween('created_at', tenant_today_range())
            ->get(['created_at']);

        $counts = array_fill(0, 24, 0);
        foreach ($rows as $row) {
            $hour = (int) to_tenant_tz($row->created_at)->format('G');
            $counts[$hour]++;
        }

        $labels = array_map(fn ($h) => str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23));

        return response()->json([
            'labels' => $labels,
            'data'   => $counts,
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

        $lastAttendance = KehadiranMember::whereRaw('UPPER(rfid) = ?', [$rfid])
            ->whereBetween('created_at', tenant_today_range())
            ->orderByDesc('created_at')
            ->first();

        if ($lastAttendance && $lastAttendance->created_at->diffInSeconds(now()) < self::ATTENDANCE_COOLDOWN_SECONDS) {
            $sisaDetik = self::ATTENDANCE_COOLDOWN_SECONDS - $lastAttendance->created_at->diffInSeconds(now());
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Absensi untuk ' . e($anggota->name) . ' baru saja tercatat. Tunggu ' . $sisaDetik . ' detik sebelum scan ulang.');
        }

        $status = (!$lastAttendance || $lastAttendance->status === 'out') ? 'in' : 'out';

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store(tenant_storage_path('kehadiran_foto'), 'public');
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
            Log::error('Gagal menyimpan data absensi member', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Gagal menyimpan data absensi. Silakan coba lagi atau hubungi admin.');
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
            Log::error('Gagal menghapus data kehadiran member', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Gagal menghapus data kehadiran. Silakan coba lagi atau hubungi admin.');
        }
    }
}
