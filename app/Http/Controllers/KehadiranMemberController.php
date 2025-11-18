<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KehadiranMember;
use App\Models\Anggota;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class KehadiranMemberController extends Controller
{
    /**
     * Export PDF dengan filter range tanggal
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:all,range',
            'tanggal_dari' => 'nullable|required_if:filter_type,range|date',
            'tanggal_sampai' => 'nullable|required_if:filter_type,range|date|after_or_equal:tanggal_dari',
        ]);

        try {
            $filterType = $request->filter_type;
            
            // Hitung statistik dari SEMUA data (tidak terfilter)
            $allKehadiran = KehadiranMember::with('anggota')->get();
            
            $totalKehadiran = $allKehadiran->count();
            $totalIn = $allKehadiran->where('status', 'in')->count();
            $totalOut = $allKehadiran->where('status', 'out')->count();
            $totalMemberUnik = $allKehadiran->unique('rfid')->count();
            
            // Query untuk data yang akan ditampilkan
            $query = KehadiranMember::with('anggota');
            
            // Filter berdasarkan tanggal
            $filterInfo = '';
            if ($filterType === 'range') {
                $tanggalDari = Carbon::parse($request->tanggal_dari)->startOfDay();
                $tanggalSampai = Carbon::parse($request->tanggal_sampai)->endOfDay();
                
                $query->whereBetween('created_at', [$tanggalDari, $tanggalSampai]);
                
                $filterInfo = $tanggalDari->locale('id')->isoFormat('D MMMM YYYY') . ' - ' . 
                            $tanggalSampai->locale('id')->isoFormat('D MMMM YYYY');
            } else {
                $filterInfo = 'Semua Periode';
            }
            
            $kehadiranMembers = $query->orderBy('created_at', 'desc')->get();
            
            // Buat title dinamis
            $title = 'Laporan Kehadiran Member';
            if ($filterType !== 'all') {
                $title .= ' - ' . $filterInfo;
            }

            $pdf = Pdf::loadView('pages.kehadiranmember.pdf', compact(
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
     * Menampilkan daftar kehadiran
     */
    public function index()
    {
        $kehadiranmembers = KehadiranMember::with('anggota')->latest()->get();
        return view('pages.kehadiranmember.index', compact('kehadiranmembers'));
    }

    /**
     * Menyimpan data kehadiran (dengan foto)
     */
    public function store(Request $request)
    {
        $request->validate([
            'rfid' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // maksimal 2MB
        ]);

        // Cek apakah kartu terdaftar di tabel anggotas
        $anggota = Anggota::where('id_kartu', $request->rfid)->first();

        if (!$anggota) {
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Kartu dengan RFID ' . e($request->rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        $lastAttendance = KehadiranMember::where('rfid', $request->rfid)
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->first();

        // Tentukan status otomatis (in/out)
        $status = (!$lastAttendance || $lastAttendance->status === 'out') ? 'in' : 'out';

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kehadiran_foto', 'public');
        }

        try {
            KehadiranMember::create([
                'rfid'   => $request->rfid,
                'status' => $status,
                'foto'   => $fotoPath,
            ]);

            return redirect()->route('kehadiranmember.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk anggota ' . e($anggota->name) . ' berhasil dicatat.');
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
            // Hapus foto jika ada
            if ($kehadiranmember->foto && Storage::disk('public')->exists($kehadiranmember->foto)) {
                Storage::disk('public')->delete($kehadiranmember->foto);
            }

            $kehadiranmember->delete();

            return redirect()->route('kehadiranmember.index')->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('kehadiranmember.index')
                ->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }
}