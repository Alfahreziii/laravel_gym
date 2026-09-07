<?php

namespace App\Http\Controllers\Kehadiran;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\KehadiranTrainer;
use App\Models\Trainer;
use App\Models\KehadiranMember;
use App\Models\Anggota;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Concerns\ResolvesMemberExpiry;

class NoRoleController extends Controller
{
    use ResolvesMemberExpiry;
    /**
     * Jeda minimum (detik) sebelum RFID yang sama boleh absen lagi.
     * Mencegah duplikat saat kartu/QR masih terbaca kamera berkali-kali.
     */
    private const ATTENDANCE_COOLDOWN_SECONDS = 60;

    // ─── AJAX endpoint untuk Scanner Drawer di halaman kehadiran-trainer ────

    public function storetainerForLayout(Request $request)
    {
        $isAjax = $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        try {
            $request->validate([
                'rfid' => 'required|string',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Data tidak valid.'], 422);
            }
            throw $e;
        }

        $rfid    = strtoupper(trim($request->rfid, '0'));
        $trainer = Trainer::whereRaw('UPPER(rfid) = ?', [$rfid])->first();

        if (!$trainer) {
            $msg = 'Kartu RFID ' . e($rfid) . ' tidak ditemukan!';
            if ($isAjax) return response()->json(['success' => false, 'message' => $msg], 404);
            return redirect()->route('absensi.trainer')->with('danger', $msg);
        }

        $last  = KehadiranTrainer::whereRaw('UPPER(rfid) = ?', [$rfid])
            ->whereBetween('created_at', tenant_today_range())
            ->orderByDesc('created_at')
            ->first();

        if ($last && $last->created_at->diffInSeconds(now()) < self::ATTENDANCE_COOLDOWN_SECONDS) {
            $sisaDetik = self::ATTENDANCE_COOLDOWN_SECONDS - $last->created_at->diffInSeconds(now());
            $msg = 'Absensi untuk ' . e($trainer->name) . ' baru saja tercatat. Tunggu ' . $sisaDetik . ' detik sebelum scan ulang.';
            if ($isAjax) return response()->json(['success' => false, 'message' => $msg]);
            return redirect()->route('absensi.trainer')->with('danger', $msg);
        }

        $status   = (!$last || $last->status === 'out') ? 'in' : 'out';
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store(tenant_storage_path('kehadiran_foto'), 'public');
        }

        try {
            KehadiranTrainer::create([
                'rfid'   => $trainer->rfid,
                'nama'   => $trainer->name,
                'status' => $status,
                'foto'   => $fotoPath,
            ]);

            $msg = 'Absensi ' . strtoupper($status) . ' untuk ' . e($trainer->name) . ' berhasil dicatat!';
            if ($isAjax) return response()->json(['success' => true, 'message' => $msg]);
            return redirect()->route('absensi.trainer')->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data absensi trainer (layout scanner)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = 'Gagal menyimpan data absensi. Silakan coba lagi atau hubungi admin.';
            if ($isAjax) return response()->json(['success' => false, 'message' => $msg], 500);
            return redirect()->route('absensi.trainer')->with('danger', $msg);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Menampilkan halaman absensi member
     */
    public function index()
    {
        $kehadiranmembers = KehadiranMember::whereBetween('created_at', tenant_today_range())
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

        $anggotaMap = $this->anggotaMapForRfids($data->pluck('rfid')->all());

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage, $anggotaMap) {
                $expiry = $this->memberExpiryInfo($anggotaMap->get(strtoupper($item->rfid)));

                return [
                    'no'                => (($page - 1) * $perPage) + $index + 1,
                    'id'                => $item->id,
                    'rfid'              => $item->rfid,
                    'foto'              => $item->foto ? asset('storage/' . $item->foto) : null,
                    'name'              => $item->nama ?? '-',
                    'status'            => $item->status,
                    'date'              => to_tenant_tz($item->created_at)->format('d/m/Y'),
                    'time'              => to_tenant_tz($item->created_at)->format('H:i:s'),
                    'delete_url'        => route('absen.destroy', $item->id),
                    'profile_photo'     => $expiry['profile_photo'],
                    'expired_at'        => $expiry['expired_at'],
                    'membership_status' => $expiry['membership_status'],
                    'membership_type'   => $expiry['membership_type'],
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

        $lastAttendance = KehadiranMember::whereRaw('UPPER(rfid) = ?', [$rfid])
            ->whereBetween('created_at', tenant_today_range())
            ->orderByDesc('created_at')
            ->first();

        if ($lastAttendance && $lastAttendance->created_at->diffInSeconds(now()) < self::ATTENDANCE_COOLDOWN_SECONDS) {
            $sisaDetik = self::ATTENDANCE_COOLDOWN_SECONDS - $lastAttendance->created_at->diffInSeconds(now());
            $msg = 'Absensi untuk ' . e($anggota->name) . ' baru saja tercatat. Tunggu ' . $sisaDetik . ' detik sebelum scan ulang.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->route('absen.index')->with('danger', $msg);
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

            if ($isAjax) {
                // Build notif payload langsung di sini (tidak tunggu observer/cache)
                // Status "aktif" tetap dari status_keanggotaan (cek keabsahan hari ini),
                // tapi tanggal/sisa hari yang DITAMPILKAN ikut latest_membership supaya
                // perpanjangan yang belum mulai tetap tercermin.
                $isAktif = $anggota->status_keanggotaan;
                $latestMembership = $anggota->latest_membership;
                $sisaHari = null;
                $tglSelesai = null;
                $alasanTidakAktif = null;
                if ($isAktif && $latestMembership) {
                    $sisaHari   = (int) tenant_today()->diffInDays($latestMembership->tgl_selesai->endOfDay(), false);
                    $tglSelesai = $latestMembership->tgl_selesai->format('d M Y');
                } else {
                    if (!$latestMembership) $alasanTidakAktif = 'Belum pernah memiliki membership';
                    elseif ($latestMembership->status_pembayaran !== 'lunas') $alasanTidakAktif = 'Pembayaran membership belum lunas';
                    else $alasanTidakAktif = 'Membership expired sejak ' . $latestMembership->tgl_selesai->format('d M Y');
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
                        'waktu'              => tenant_now()->format('d M Y - H:i:s'),
                        'timestamp'          => now()->timestamp,
                    ],
                ]);
            }

            return redirect()->route('absen.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk ' . e($anggota->name) . ' berhasil dicatat!');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data absensi member', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan data absensi. Silakan coba lagi atau hubungi admin.'], 500);
            }
            return redirect()->route('absen.index')
                ->with('danger', 'Gagal menyimpan data absensi. Silakan coba lagi atau hubungi admin.');
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
            Log::error('Gagal menghapus data kehadiran member', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('absen.index')
                ->with('danger', 'Gagal menghapus data kehadiran. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Menampilkan halaman absensi trainer
     */
    public function indextrainer()
    {
        $kehadirantrainers = KehadiranTrainer::whereBetween('created_at', tenant_today_range())
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
                    'date'       => to_tenant_tz($item->created_at)->format('d/m/Y'),
                    'time'       => to_tenant_tz($item->created_at)->format('H:i:s'),
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

        $lastAttendance = KehadiranTrainer::whereRaw('UPPER(rfid) = ?', [$rfid])
            ->whereBetween('created_at', tenant_today_range())
            ->orderByDesc('created_at')
            ->first();

        if ($lastAttendance && $lastAttendance->created_at->diffInSeconds(now()) < self::ATTENDANCE_COOLDOWN_SECONDS) {
            $sisaDetik = self::ATTENDANCE_COOLDOWN_SECONDS - $lastAttendance->created_at->diffInSeconds(now());
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Absensi untuk ' . e($trainer->name) . ' baru saja tercatat. Tunggu ' . $sisaDetik . ' detik sebelum scan ulang.');
        }

        $status = (!$lastAttendance || $lastAttendance->status === 'out') ? 'in' : 'out';

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store(tenant_storage_path('kehadiran_foto'), 'public');
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
            Log::error('Gagal menyimpan data absensi trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Gagal menyimpan data absensi. Silakan coba lagi atau hubungi admin.');
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
            Log::error('Gagal menghapus data kehadiran trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('absentrainer.index')
                ->with('danger', 'Gagal menghapus data kehadiran. Silakan coba lagi atau hubungi admin.');
        }
    }
}
