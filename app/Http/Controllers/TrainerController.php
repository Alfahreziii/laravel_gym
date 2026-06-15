<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\Anggota;
use App\Models\User;
use App\Models\Specialisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Controllers\Concerns\ExportsExcel;

class TrainerController extends Controller
{
    use ExportsExcel;

    public function exportPdf(Request $request)
    {
        try {
            $statusFilter = $request->input('status_filter', 'all');

            $allTrainers    = Trainer::all();
            $totalTrainer   = $allTrainers->count();
            $totalAktif     = $allTrainers->where('status', Trainer::STATUS_AKTIF)->count();
            $totalNonaktif  = $allTrainers->where('status', Trainer::STATUS_NONAKTIF)->count();
            $totalPending   = $allTrainers->where('status', Trainer::STATUS_PENDING)->count();

            $query = Trainer::with(['specialisasi', 'user', 'schedules'])
                ->join('users', 'trainers.id', '=', 'users.trainer_id')
                ->select('trainers.*');

            if ($statusFilter === 'aktif') {
                $query->where('trainers.status', Trainer::STATUS_AKTIF);
                $title = 'Laporan Trainer Aktif';
            } elseif ($statusFilter === 'nonaktif') {
                $query->where('trainers.status', Trainer::STATUS_NONAKTIF);
                $title = 'Laporan Trainer Non-Aktif';
            } elseif ($statusFilter === 'pending') {
                $query->where('trainers.status', Trainer::STATUS_PENDING);
                $title = 'Laporan Trainer Pending';
            } else {
                $title = 'Laporan Semua Trainer';
            }

            $trainers = $query->orderBy('users.name', 'asc')->get();

            $pdf = Pdf::loadView('pages.trainer.pdf', compact(
                'trainers',
                'totalTrainer',
                'totalAktif',
                'totalNonaktif',
                'totalPending',
                'title',
                'statusFilter'
            ));

            $pdf->setPaper('a4', 'landscape');
            $filename = 'Laporan_Trainer_' . ucfirst($statusFilter) . '_' . date('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Gagal export PDF trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export data trainer ke Excel (.xls) — filter status sama seperti exportPdf.
     */
    public function exportExcel(Request $request)
    {
        try {
            $statusFilter = $request->input('status_filter', 'all');

            $allTrainers   = Trainer::all();
            $totalTrainer  = $allTrainers->count();
            $totalAktif    = $allTrainers->where('status', Trainer::STATUS_AKTIF)->count();
            $totalNonaktif = $allTrainers->where('status', Trainer::STATUS_NONAKTIF)->count();
            $totalPending  = $allTrainers->where('status', Trainer::STATUS_PENDING)->count();

            $query = Trainer::with(['specialisasi', 'user', 'schedules'])
                ->join('users', 'trainers.id', '=', 'users.trainer_id')
                ->select('trainers.*', 'users.name', 'users.email');

            if ($statusFilter === 'aktif') {
                $query->where('trainers.status', Trainer::STATUS_AKTIF);
                $title = 'Laporan Trainer Aktif';
            } elseif ($statusFilter === 'nonaktif') {
                $query->where('trainers.status', Trainer::STATUS_NONAKTIF);
                $title = 'Laporan Trainer Non-Aktif';
            } elseif ($statusFilter === 'pending') {
                $query->where('trainers.status', Trainer::STATUS_PENDING);
                $title = 'Laporan Trainer Pending';
            } else {
                $title = 'Laporan Semua Trainer';
            }

            $trainers = $query->orderBy('users.name', 'asc')->get();

            $rows = '';
            foreach ($trainers as $i => $trainer) {
                $jadwal = $trainer->schedules->map(function ($s) {
                    return $s->day_of_week . ': ' .
                        Carbon::parse($s->start_time)->format('H:i') . '-' .
                        Carbon::parse($s->end_time)->format('H:i');
                })->implode('; ');

                $rows .= '<tr>'
                    . '<td class="center">' . ($i + 1) . '</td>'
                    . '<td>' . $this->exEsc($trainer->rfid) . '</td>'
                    . '<td>' . $this->exEsc($trainer->name) . '</td>'
                    . '<td>' . $this->exEsc($trainer->email) . '</td>'
                    . '<td>' . $this->exEsc($trainer->no_telp) . '</td>'
                    . '<td>' . $this->exEsc($trainer->specialisasi->nama_specialisasi ?? '-') . '</td>'
                    . '<td>' . $this->exEsc($trainer->experience) . '</td>'
                    . '<td class="center">' . $trainer->tgl_gabung->format('d/m/Y') . '</td>'
                    . '<td class="center">' . $trainer->sesi_belum_dijalani . '</td>'
                    . '<td class="center">' . $trainer->sesi_sudah_dijalani . '</td>'
                    . '<td class="center">' . $this->exEsc(ucfirst($trainer->status)) . '</td>'
                    . '<td>' . $this->exEsc($jadwal ?: '-') . '</td>'
                    . '</tr>';
            }

            $html = '<table>';
            $html .= '<tr><td colspan="12" class="title">' . $this->exEsc($title) . '</td></tr>';
            $html .= '<tr><td colspan="12" class="subtitle">Dicetak: ' . now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') . ' WIB</td></tr>';
            $html .= '<tr><td colspan="12"></td></tr>';
            $html .= '<tr>'
                . '<td colspan="3" class="summary-label">Total Trainer</td><td colspan="3" class="summary-val">' . $totalTrainer . '</td>'
                . '<td colspan="3" class="summary-label">Aktif / Non-Aktif / Pending</td><td colspan="3" class="summary-val">' . $totalAktif . ' / ' . $totalNonaktif . ' / ' . $totalPending . '</td>'
                . '</tr>';
            $html .= '<tr><td colspan="12"></td></tr>';
            $html .= '<tr>'
                . '<th>No</th><th>RFID</th><th>Nama</th><th>Email</th><th>No. Telp</th><th>Spesialisasi</th>'
                . '<th>Experience</th><th>Tgl Gabung</th><th>Sesi Belum</th><th>Sesi Sudah</th><th>Status</th><th>Jadwal</th>'
                . '</tr>';
            $html .= $rows;
            $html .= '</table>';

            $filename = 'Laporan_Trainer_' . ucfirst($statusFilter) . '_' . date('Y-m-d_His') . '.xls';

            return $this->excelDownload($html, $title, $filename);
        } catch (\Exception $e) {
            Log::error('Gagal export Excel trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    public function index()
    {
        return view('pages.trainer.index');
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = Trainer::with(['specialisasi', 'user'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rfid', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%")
                    ->orWhere('experience', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('specialisasi', fn($q2) => $q2->where('nama_specialisasi', 'like', "%{$search}%"));
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'                  => (($page - 1) * $perPage) + $index + 1,
                    'id'                  => $item->id,
                    'rfid'                => $item->rfid ?? '-',
                    'foto'                => $item->user?->photo ? asset('storage/' . $item->user->photo) : null,
                    'name'                => $item->user?->name ?? '-',
                    'no_telp'             => $item->no_telp ?? '-',
                    'specialisasi'        => $item->specialisasi?->nama_specialisasi ?? '-',
                    'sesi_belum_dijalani' => $item->sesi_belum_dijalani ?? 0,
                    'sesi_sudah_dijalani' => $item->sesi_sudah_dijalani ?? 0,
                    'experience'          => $item->experience ?? '-',
                    'tgl_gabung'          => $item->tgl_gabung ? $item->tgl_gabung->format('d-m-Y') : '-',
                    'status'              => $item->status ?? '-',
                    'status_label'        => $item->status_label,
                    'status_finger'       => $item->status_finger,
                    'show_url'            => route('trainer.show', $item->id),
                    'edit_url'            => route('trainer.edit', $item->id),
                    'delete_url'          => route('trainer.destroy', $item->id),
                    'update_status_url'   => route('trainer.update-status', $item->id),
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
        $specialisasis = Specialisasi::all();
        return view('pages.trainer.create', compact('specialisasis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email',
            'password'             => 'required|string|min:8|confirmed',
            'photo'                => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'id_specialisasi'      => 'required|exists:specialisasis,id',
            'rfid'                 => 'required|unique:trainers,rfid|max:30',
            'no_telp'              => 'required|string|max:50',
            'experience'           => 'required|string|max:100',
            'tgl_gabung'           => 'required|date',
            'keterangan'           => 'nullable|string|max:100',
            'tempat_lahir'         => 'required|string',
            'tgl_lahir'            => 'required|date',
            'jenis_kelamin'        => 'required|string|max:20',
            'alamat'               => 'required|string',
            'status_finger'        => 'required|in:0,1,2',
            'jadwal.*.day_of_week' => 'required|string',
            'jadwal.*.start_time'  => 'required',
            'jadwal.*.end_time'    => 'required',
        ]);

        // Cek rfid tidak boleh sama dengan id_kartu di tabel anggotas
        if (Anggota::where('id_kartu', $request->rfid)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "RFID '{$request->rfid}' sudah digunakan sebagai ID Kartu Anggota.");
        }

        // Cek status_finger cross-table
        if (in_array($request->status_finger, ['0', '1'])) {
            $existsInTrainer = Trainer::where('status_finger', $request->status_finger)->exists();
            $existsInAnggota = Anggota::where('status_finger', $request->status_finger)->exists();
            if ($existsInTrainer || $existsInAnggota) {
                $label = $request->status_finger == '0' ? 'Enroll' : 'Delete';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Sudah ada member/trainer dengan status finger '{$label}'. Hanya boleh ada 1 secara keseluruhan.");
            }
        }

        DB::beginTransaction();
        try {
            $photoPath = $request->file('photo')->store('trainers', 'public');

            $trainer = Trainer::create([
                'id_specialisasi'     => $request->id_specialisasi,
                'rfid'                => $request->rfid,
                'no_telp'             => $request->no_telp,
                'experience'          => $request->experience,
                'tgl_gabung'          => $request->tgl_gabung,
                'status'              => Trainer::STATUS_PENDING,
                'keterangan'          => $request->keterangan,
                'tempat_lahir'        => $request->tempat_lahir,
                'tgl_lahir'           => $request->tgl_lahir,
                'jenis_kelamin'       => $request->jenis_kelamin,
                'alamat'              => $request->alamat,
                'sesi_sudah_dijalani' => 0,
                'sesi_belum_dijalani' => 0,
                'status_finger'       => $request->status_finger ?? 2,
            ]);

            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'password'          => Hash::make($request->password),
                'trainer_id'        => $trainer->id,
                'photo'             => $photoPath,
                'email_verified_at' => now(),
            ]);

            $user->assignRole('trainer');

            if ($request->has('jadwal')) {
                foreach ($request->jadwal as $j) {
                    $trainer->schedules()->create([
                        'day_of_week' => $j['day_of_week'],
                        'start_time'  => $j['start_time'],
                        'end_time'    => $j['end_time'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('trainer.index')
                ->with('success', 'Trainer berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('Gagal membuat trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan trainer: ' . $e->getMessage());
        }
    }

    public function edit(Trainer $trainer)
    {
        $specialisasis = Specialisasi::all();
        $trainer->load('user', 'schedules');
        return view('pages.trainer.edit', compact('trainer', 'specialisasis'));
    }

    public function show(Trainer $trainer)
    {
        $trainer->load('specialisasi', 'schedules', 'user');
        return view('pages.trainer.show', compact('trainer'));
    }

    public function update(Request $request, Trainer $trainer)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email,' . $trainer->user->id,
            'password'             => 'nullable|string|min:8|confirmed',
            'photo'                => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_specialisasi'      => 'required|exists:specialisasis,id',
            'rfid'                 => 'required|max:30|unique:trainers,rfid,' . $trainer->id,
            'no_telp'              => 'required|string|max:50',
            'experience'           => 'required|string|max:100',
            'tgl_gabung'           => 'required|date',
            'keterangan'           => 'nullable|string|max:100',
            'tempat_lahir'         => 'required|string',
            'tgl_lahir'            => 'required|date',
            'jenis_kelamin'        => 'required|string|max:20',
            'alamat'               => 'required|string',
            'status_finger'        => 'required|in:0,1,2',
            'jadwal.*.day_of_week' => 'required|string',
            'jadwal.*.start_time'  => 'required',
            'jadwal.*.end_time'    => 'required',
        ]);

        // Cek rfid tidak boleh sama dengan id_kartu di tabel anggotas
        if (Anggota::where('id_kartu', $request->rfid)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "RFID '{$request->rfid}' sudah digunakan sebagai ID Kartu Anggota.");
        }

        // Cek status_finger cross-table (exclude diri sendiri di trainer)
        if (in_array($request->status_finger, ['0', '1'])) {
            $existsInTrainer = Trainer::where('status_finger', $request->status_finger)
                ->where('id', '!=', $trainer->id)
                ->exists();
            $existsInAnggota = Anggota::where('status_finger', $request->status_finger)->exists();
            if ($existsInTrainer || $existsInAnggota) {
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
                if ($trainer->user->photo && Storage::disk('public')->exists($trainer->user->photo)) {
                    Storage::disk('public')->delete($trainer->user->photo);
                }
                $userData['photo'] = $request->file('photo')->store('trainers', 'public');
            }

            $trainer->user->update($userData);

            $trainer->update($request->only([
                'id_specialisasi',
                'rfid',
                'no_telp',
                'experience',
                'tgl_gabung',
                'keterangan',
                'tempat_lahir',
                'tgl_lahir',
                'jenis_kelamin',
                'alamat',
                'status_finger',
            ]));

            $trainer->schedules()->delete();
            if ($request->has('jadwal')) {
                foreach ($request->jadwal as $j) {
                    $trainer->schedules()->create([
                        'day_of_week' => $j['day_of_week'],
                        'start_time'  => $j['start_time'],
                        'end_time'    => $j['end_time'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('trainer.index')
                ->with('success', 'Trainer berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update trainer', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update trainer: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Trainer $trainer)
    {
        $request->validate([
            'status' => 'required|in:' . Trainer::STATUS_PENDING . ',' . Trainer::STATUS_NONAKTIF . ',' . Trainer::STATUS_AKTIF
        ]);

        try {
            $trainer->update(['status' => $request->status]);

            return redirect()->back()
                ->with('success', 'Status trainer berhasil diperbarui menjadi ' . $trainer->status_label['text']);
        } catch (\Exception $e) {
            Log::error('Gagal update status trainer', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal mengubah status trainer');
        }
    }

    public function destroy(Trainer $trainer)
    {
        DB::beginTransaction();
        try {
            $user = $trainer->user;

            if ($user && $user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $trainer->schedules()->delete();
            $trainer->delete();

            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('trainer.index')
                ->with('success', 'Trainer dan akun login berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus trainer', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menghapus trainer: ' . $e->getMessage());
        }
    }
}
