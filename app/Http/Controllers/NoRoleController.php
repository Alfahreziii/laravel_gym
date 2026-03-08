<?php

namespace App\Http\Controllers;

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
        $kehadiranmembers = KehadiranMember::with('anggota.user')
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->get();

        return view('pages.norole.kehadiranmember', compact('kehadiranmembers'));
    }

    public function datatable(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page = (int) $request->get('page', 1);

        $query = KehadiranMember::with('anggota.user')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rfid', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereIn('rfid', function ($sub) use ($search) {
                        $sub->select('id_kartu')
                            ->from('anggotas')
                            ->whereIn('id', function ($userSub) use ($search) {
                                $userSub->select('anggota_id')
                                    ->from('users')
                                    ->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $total = (clone $query)->count();
        $data = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'         => (($page - 1) * $perPage) + $index + 1,
                    'id'         => $item->id,
                    'rfid'       => $item->rfid,
                    'foto'       => $item->foto ? asset('storage/' . $item->foto) : null,
                    'name'       => $item->anggota?->user?->name ?? '-',
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
        $request->validate([
            'rfid' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // NORMALISASI RFID: Ubah ke uppercase untuk konsistensi
        $rfid = strtoupper(trim($request->rfid, '0'));

        // Cek apakah kartu terdaftar (case-insensitive)
        $anggota = Anggota::whereRaw('UPPER(id_kartu) = ?', [$rfid])->first();

        if (!$anggota) {
            return redirect()->route('absen.index')
                ->with('danger', 'Kartu dengan RFID ' . e($rfid) . ' tidak ditemukan!');
        }

        $today = now()->toDateString();

        // Cari kehadiran terakhir dengan case-insensitive
        $lastAttendance = KehadiranMember::whereRaw('UPPER(rfid) = ?', [$rfid])
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
                'rfid'   => $anggota->id_kartu, // Gunakan ID kartu asli dari database
                'status' => $status,
                'foto'   => $fotoPath,
            ]);

            return redirect()->route('absen.index')
                ->with('success', 'Absensi ' . strtoupper($status) . ' untuk ' . e($anggota->name) . ' berhasil dicatat!');
        } catch (\Exception $e) {
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
            // Hapus foto jika ada
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
        $kehadirantrainers = KehadiranTrainer::with('trainer')->latest()->get();
        return view('pages.norole.kehadirantrainer', compact('kehadirantrainers'));
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

        // NORMALISASI RFID untuk trainer juga
        $rfid = strtoupper(trim($request->rfid));

        // Cek apakah kartu ada di tabel trainers (case-insensitive)
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

        // Tentukan status otomatis (in/out)
        $status = (!$lastAttendance || $lastAttendance->status === 'out') ? 'in' : 'out';

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kehadiran_foto', 'public');
        }

        try {
            KehadiranTrainer::create([
                'rfid'   => $trainer->rfid, // Gunakan RFID asli dari database
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
