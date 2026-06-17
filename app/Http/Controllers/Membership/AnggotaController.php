<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;

use App\Models\Anggota;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Controllers\Concerns\ExportsExcel;

class AnggotaController extends Controller
{
    use ExportsExcel;

    public function exportPdf(Request $request)
    {
        try {
            $statusFilter = $request->input('status_filter', 'all');

            $allAnggotas = Anggota::with(['anggotaMemberships' => function ($query) {
                $query->latest('tgl_selesai');
            }])->get();

            $totalAnggota    = $allAnggotas->count();
            $totalAktif      = $allAnggotas->filter(fn($a) => $a->status_keanggotaan === true)->count();
            $totalTidakAktif = $allAnggotas->filter(fn($a) => $a->status_keanggotaan === false)->count();

            $query = Anggota::with(['anggotaMemberships', 'user'])
                ->join('users', 'anggotas.id', '=', 'users.anggota_id')
                ->select('anggotas.*');

            if ($statusFilter === 'aktif') {
                $query->whereHas('anggotaMemberships', fn($q) => $q->where('is_active', true));
                $title = 'Laporan Anggota Aktif';
            } elseif ($statusFilter === 'tidak_aktif') {
                $query->whereDoesntHave('anggotaMemberships', fn($q) => $q->where('is_active', true));
                $title = 'Laporan Anggota Tidak Aktif';
            } else {
                $title = 'Laporan Semua Anggota';
            }

            $anggotas = $query->orderBy('users.name', 'asc')->get();

            $pdf = Pdf::loadView('pages.admin.membership.anggota.pdf', compact(
                'anggotas',
                'totalAnggota',
                'totalAktif',
                'totalTidakAktif',
                'title',
                'statusFilter'
            ));

            $pdf->setPaper('a4', 'landscape');
            $filename = 'Laporan_Anggota_' . ucfirst($statusFilter) . '_' . date('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Gagal export PDF anggota', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export data anggota ke Excel (.xls) — filter status sama seperti exportPdf.
     */
    public function exportExcel(Request $request)
    {
        try {
            $statusFilter = $request->input('status_filter', 'all');

            $allAnggotas = Anggota::with(['anggotaMemberships' => function ($query) {
                $query->latest('tgl_selesai');
            }])->get();

            $totalAnggota    = $allAnggotas->count();
            $totalAktif      = $allAnggotas->filter(fn($a) => $a->status_keanggotaan === true)->count();
            $totalTidakAktif = $allAnggotas->filter(fn($a) => $a->status_keanggotaan === false)->count();

            $query = Anggota::with(['anggotaMemberships', 'user'])
                ->join('users', 'anggotas.id', '=', 'users.anggota_id')
                ->select('anggotas.*', 'users.name', 'users.email');

            if ($statusFilter === 'aktif') {
                $query->whereHas('anggotaMemberships', fn($q) => $q->where('is_active', true));
                $title = 'Laporan Anggota Aktif';
            } elseif ($statusFilter === 'tidak_aktif') {
                $query->whereDoesntHave('anggotaMemberships', fn($q) => $q->where('is_active', true));
                $title = 'Laporan Anggota Tidak Aktif';
            } else {
                $title = 'Laporan Semua Anggota';
            }

            $anggotas = $query->orderBy('users.name', 'asc')->get();

            $rows = '';
            foreach ($anggotas as $i => $anggota) {
                $status = $anggota->status_keanggotaan ? 'Aktif' : 'Tidak Aktif';

                $rows .= '<tr>'
                    . '<td class="center">' . ($i + 1) . '</td>'
                    . '<td>' . $this->exEsc($anggota->id_kartu) . '</td>'
                    . '<td>' . $this->exEsc($anggota->name) . '</td>'
                    . '<td>' . $this->exEsc($anggota->email) . '</td>'
                    . '<td class="center">' . Carbon::parse($anggota->tgl_lahir)->format('d/m/Y') . '</td>'
                    . '<td>' . $this->exEsc($anggota->no_telp) . '</td>'
                    . '<td>' . $this->exEsc($anggota->alamat) . '</td>'
                    . '<td class="center">' . $this->exEsc($anggota->gol_darah) . '</td>'
                    . '<td class="center">' . $status . '</td>'
                    . '</tr>';
            }

            $html = '<table>';
            $html .= '<tr><td colspan="9" class="title">' . $this->exEsc($title) . '</td></tr>';
            $html .= '<tr><td colspan="9" class="subtitle">Dicetak: ' . now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') . ' WIB</td></tr>';
            $html .= '<tr><td colspan="9"></td></tr>';
            $html .= '<tr>'
                . '<td colspan="3" class="summary-label">Total Anggota</td><td colspan="2" class="summary-val">' . $totalAnggota . '</td>'
                . '<td colspan="2" class="summary-label">Aktif / Tidak Aktif</td><td colspan="2" class="summary-val">' . $totalAktif . ' / ' . $totalTidakAktif . '</td>'
                . '</tr>';
            $html .= '<tr><td colspan="9"></td></tr>';
            $html .= '<tr>'
                . '<th>No</th><th>ID Kartu</th><th>Nama Lengkap</th><th>Email</th><th>Tanggal Lahir</th>'
                . '<th>No. Telepon</th><th>Alamat</th><th>Gol. Darah</th><th>Status</th>'
                . '</tr>';
            $html .= $rows;
            $html .= '</table>';

            $filename = 'Laporan_Anggota_' . ucfirst($statusFilter) . '_' . date('Y-m-d_His') . '.xls';

            return $this->excelDownload($html, $title, $filename);
        } catch (\Exception $e) {
            Log::error('Gagal export Excel anggota', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    public function index()
    {
        return view('pages.admin.membership.anggota.index');
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = Anggota::with('user')->orderByRaw("FIELD(status_finger, 0, 1, 2)");

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id_kartu', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%")
                    ->orWhereHas(
                        'user',
                        fn($q2) => $q2
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                    );
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'            => (($page - 1) * $perPage) + $index + 1,
                    'id'            => $item->id,
                    'id_kartu'      => $item->id_kartu,
                    'foto'          => $item->user?->photo ? asset('storage/' . $item->user->photo) : null,
                    'name'          => $item->user?->name ?? '-',
                    'email'         => $item->user?->email ?? '-',
                    'tgl_lahir'     => $item->tgl_lahir ? $item->tgl_lahir->format('d M Y') : '-',
                    'no_telp'       => $item->no_telp ?? '-',
                    'status'        => $item->status_keanggotaan,
                    'status_finger' => $item->status_finger,
                    'edit_url'      => route('anggota.edit', $item->id),
                    'delete_url'    => route('anggota.destroy', $item->id),
                    'status_finger_url' => route('anggota.update_status_finger', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function create()
    {
        return view('pages.admin.membership.anggota.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:8|confirmed',
            'photo'             => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'id_kartu'          => 'required|string|max:30|unique:anggotas,id_kartu',
            'no_telp'           => 'required|string|max:50',
            'alamat'            => 'required|string',
            'gol_darah'         => 'required|string|max:2',
            'tinggi'            => 'required|integer',
            'berat'             => 'required|integer',
            'tempat_lahir'      => 'required|string',
            'tgl_lahir'         => 'required|date',
            'tgl_daftar'        => 'required|date',
            'jenis_kelamin'     => 'required|string|max:20',
            'riwayat_kesehatan' => 'nullable|string',
            'status_finger'     => 'required|in:0,1,2',
        ]);

        // Cek id_kartu tidak boleh sama dengan rfid di tabel trainers
        if (Trainer::where('rfid', $request->id_kartu)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "ID Kartu '{$request->id_kartu}' sudah digunakan sebagai RFID Trainer.");
        }

        // Cek status_finger cross-table
        if (in_array($request->status_finger, ['0', '1'])) {
            $existsInAnggota = Anggota::where('status_finger', $request->status_finger)->exists();
            $existsInTrainer = Trainer::where('status_finger', $request->status_finger)->exists();
            if ($existsInAnggota || $existsInTrainer) {
                $label = $request->status_finger == '0' ? 'Enroll' : 'Delete';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Sudah ada member/trainer dengan status finger '{$label}'. Hanya boleh ada 1 secara keseluruhan.");
            }
        }

        DB::beginTransaction();
        try {
            $photoPath = $request->file('photo')->store('anggotas', 'public');

            $anggota = Anggota::create([
                'id_kartu'          => $request->id_kartu,
                'no_telp'           => $request->no_telp,
                'alamat'            => $request->alamat,
                'gol_darah'         => $request->gol_darah,
                'tinggi'            => $request->tinggi,
                'berat'             => $request->berat,
                'tempat_lahir'      => $request->tempat_lahir,
                'tgl_lahir'         => $request->tgl_lahir,
                'tgl_daftar'        => $request->tgl_daftar,
                'jenis_kelamin'     => $request->jenis_kelamin,
                'riwayat_kesehatan' => $request->riwayat_kesehatan,
                'status_finger'     => $request->status_finger ?? 2,
            ]);

            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'password'          => Hash::make($request->password),
                'anggota_id'        => $anggota->id,
                'photo'             => $photoPath,
                'email_verified_at' => now(),
            ]);

            $user->assignRole('member');

            DB::commit();

            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('Gagal membuat anggota', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan anggota: ' . $e->getMessage());
        }
    }

    public function edit(Anggota $anggota)
    {
        $anggota->load('user');
        return view('pages.admin.membership.anggota.edit', compact('anggota'));
    }

    public function update(Request $request, Anggota $anggota)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email,' . $anggota->user->id,
            'password'          => 'nullable|string|min:8|confirmed',
            'photo'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_kartu'          => 'required|string|max:30|unique:anggotas,id_kartu,' . $anggota->id,
            'no_telp'           => 'required|string|max:50',
            'alamat'            => 'required|string',
            'gol_darah'         => 'required|string|max:2',
            'tinggi'            => 'required|integer',
            'berat'             => 'required|integer',
            'tempat_lahir'      => 'required|string',
            'tgl_lahir'         => 'required|date',
            'tgl_daftar'        => 'required|date',
            'jenis_kelamin'     => 'required|string|max:20',
            'riwayat_kesehatan' => 'nullable|string',
            'status_finger'     => 'required|in:0,1,2',
        ]);

        // Cek id_kartu tidak boleh sama dengan rfid di tabel trainers
        if (Trainer::where('rfid', $request->id_kartu)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "ID Kartu '{$request->id_kartu}' sudah digunakan sebagai RFID Trainer.");
        }

        // Cek status_finger cross-table (exclude diri sendiri)
        if (in_array($request->status_finger, ['0', '1'])) {
            $existsInAnggota = Anggota::where('status_finger', $request->status_finger)
                ->where('id', '!=', $anggota->id)
                ->exists();
            $existsInTrainer = Trainer::where('status_finger', $request->status_finger)->exists();
            if ($existsInAnggota || $existsInTrainer) {
                $label = $request->status_finger == '0' ? 'Enroll' : 'Delete';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Sudah ada member/trainer dengan status finger '{$label}'. Hanya boleh ada 1 secara keseluruhan.");
            }
        }

        DB::beginTransaction();
        try {
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('photo')) {
                if ($anggota->user->photo && Storage::disk('public')->exists($anggota->user->photo)) {
                    Storage::disk('public')->delete($anggota->user->photo);
                }
                $userData['photo'] = $request->file('photo')->store('anggotas', 'public');
            }

            $anggota->user->update($userData);

            $anggota->update($request->only([
                'id_kartu',
                'no_telp',
                'alamat',
                'gol_darah',
                'tinggi',
                'berat',
                'tempat_lahir',
                'tgl_lahir',
                'tgl_daftar',
                'jenis_kelamin',
                'riwayat_kesehatan',
                'status_finger',
            ]));

            DB::commit();

            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update anggota', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update anggota: ' . $e->getMessage());
        }
    }

    /**
     * Update status_finger saja (dipakai dari popup di halaman index, via AJAX).
     */
    public function updateStatusFinger(Request $request, Anggota $anggota)
    {
        $request->validate([
            'status_finger' => 'required|in:0,1,2',
        ]);

        // Cek status_finger cross-table (exclude diri sendiri) — sama seperti di update()
        if (in_array($request->status_finger, ['0', '1'])) {
            $existsInAnggota = Anggota::where('status_finger', $request->status_finger)
                ->where('id', '!=', $anggota->id)
                ->exists();
            $existsInTrainer = Trainer::where('status_finger', $request->status_finger)->exists();
            if ($existsInAnggota || $existsInTrainer) {
                $label = $request->status_finger == '0' ? 'Enroll' : 'Delete';
                return response()->json([
                    'message' => "Sudah ada member/trainer dengan status finger '{$label}'. Hanya boleh ada 1 secara keseluruhan.",
                ], 422);
            }
        }

        $anggota->update(['status_finger' => $request->status_finger]);

        return response()->json([
            'message'       => 'Status fingerprint berhasil diperbarui.',
            'status_finger' => (int) $request->status_finger,
        ]);
    }

    public function destroy(Anggota $anggota)
    {
        DB::beginTransaction();
        try {
            $user = $anggota->user;

            if ($user && $user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $anggota->delete();

            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('anggota.index')
                ->with('success', 'Anggota dan akun login berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus anggota', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus anggota: ' . $e->getMessage());
        }
    }
}
