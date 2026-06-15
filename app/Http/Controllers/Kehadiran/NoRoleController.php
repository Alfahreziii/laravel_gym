<?php

namespace App\Http\Controllers\Kehadiran;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\KehadiranTrainer;
use App\Models\Trainer;
use App\Models\KehadiranMember;
use App\Models\Anggota;
use Illuminate\Support\Facades\Storage;

class NoRoleController extends Controller
{
    /**
     * Menampilkan halaman absensi member
     */
    public function index()
    {
        $kehadiranmembers = KehadiranMember::whereDate('created_at', now()->toDateString())
            ->latest()
            ->get();

        return view('pages.norole.kehadiranmember', compact('kehadiranmembers'));
    }

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
                    'date'       => $item->created_at->format('d/m/Y'),
                    'time'       => $item->created_at->format('H:i:s'),
                    'delete_url' => route('absen.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Menyimpan data kehadiran member (dengan foto)
     */
    public function store(Request $request)
    {
        $isAjax = $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        try {
            $request->validate([
                'rfid' => 'required|string',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Data tidak valid: ' . collect($e->errors())->flatten()->first()], 422);
            }
            throw $e;
        }

        $rfid = strtoupper(trim($request->rfid, '0'));

        $anggota = Anggota::whereRaw('UPPER(id_kartu) = ?', [$rfid])->first();

        if (!$anggota) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Kartu RFID ' . e($rfid) . ' tidak ditemukan!'], 404);
            }
            return redirect()->route('absen.index')->with('danger', 'Kartu dengan RFID ' . e($rfid) . ' tidak ditemukan!');
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

            if ($isAjax) {
                // Build notif payload langsung di sini (tidak tunggu observer/cache)
                $today2   = \Carbon\Carbon::today();
                $isAktif  = $anggota->status_keanggotaan;
                $activeMembership = $anggota->active_membership;
                $sisaHari = null;
                $tglSelesai = null;
                $alasanTidakAktif = null;
                if ($isAktif && $activeMembership) {
                    $sisaHari   = (int) now()->startOfDay()->diffInDays($activeMembership->tgl_selesai->endOfDay(), false);
                    $tglSelesai = $activeMembership->tgl_selesai->format('d M Y');
                } else {
                    $latest = $anggota->anggotaMemberships()->latest('tgl_selesai')->first();
                    if (!$latest) $alasanTidakAktif = 'Belum pernah memiliki membership';
                    elseif ($latest->status_pembayaran !== 'lunas') $alasanTidakAktif = 'Pembayaran membership belum lunas';
                    else $alasanTidakAktif = 'Membership expired sejak ' . $latest->tgl_selesai->format('d M Y');
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Absensi ' . strtoupper($status) . ' untuk ' . $anggota->name . ' berhasil dicatat!',
                    'notif'   => [
                        'id'                 => 0,
                        'nama'               => $anggota->name,
                        'status'             => $status,
                        'is_aktif'           => $isAktif,
                        'sisa_hari'          => $sisaHari,
                        'tgl_selesai'        => $tglSelesai,
                        'alasan_tidak_aktif' => $alasanTidakAktif,
                        'foto'               => $fotoPath ? asset('storage/' . $fotoPath) : null,
                        'waktu'              => now()->format('d M Y - H:i:s'),
                        'timestamp'          => now()->timestamp,
                    ],
                ]);
            }

            return redirect()->route('absen.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk ' . e($anggota->name) . ' berhasil dicatat!');
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->route('absen.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data kehadiran member
     */
    public function destroy(KehadiranMember $kehadiranmember)
    {
        try {
            if ($kehadiranmember->foto && Storage::disk('public')->exists($kehadiranmember->foto)) {
                Storage::disk('public')->delete($kehadiranmember->foto);
            }

            $kehadiranmember->delete();

            return redirect()->route('absen.index')->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('absen.index')
                ->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman absensi trainer
     */
    public function indextrainer()
    {
        $kehadirantrainers = KehadiranTrainer::whereDate('created_at', now()->toDateString())
            ->latest()
            ->get();

        return view('pages.norole.kehadirantrainer', compact('kehadirantrainers'));
    }

    /**
     * Datatable untuk absensi trainer
     */
    public function datatabletrainer(Request $request)
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
                    'date'       => $item->created_at->format('d/m/Y'),
                    'time'       => $item->created_at->format('H:i:s'),
                    'delete_url' => route('absentrainer.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Menyimpan data kehadiran trainer (dengan foto)
     */
    public function storetrainer(Request $request)
    {
        $request->validate([
            'rfid' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $rfid = strtoupper(trim($request->rfid, '0'));
        $trainer = Trainer::whereRaw('UPPER(rfid) = ?', [$rfid])->first();

        if (!$trainer) {
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Kartu dengan RFID ' . e($rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        $lastAttendance = KehadiranTrainer::whereRaw('UPPER(rfid) = ?', [$rfid])
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

            return redirect()->route('absentrainer.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk ' . e($trainer->name) . ' berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data kehadiran trainer
     */
    public function destroytrainer(KehadiranTrainer $kehadirantrainer)
    {
        try {
            if ($kehadirantrainer->foto && Storage::disk('public')->exists($kehadirantrainer->foto)) {
                Storage::disk('public')->delete($kehadirantrainer->foto);
            }

            $kehadirantrainer->delete();

            return redirect()->route('absentrainer.index')->with('success', 'Data kehadiran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Gagal menghapus data kehadiran: ' . $e->getMessage());
        }
    }
}
