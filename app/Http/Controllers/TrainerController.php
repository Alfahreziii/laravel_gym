<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\User;
use App\Models\Specialisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TrainerController extends Controller
{
    public function exportPdf(Request $request)
    {
        try {
            $statusFilter = $request->input('status_filter', 'all');
            
            // Hitung statistik dari SEMUA data (tidak terfilter)
            $allTrainers = Trainer::all();
            $totalTrainer = $allTrainers->count();
            $totalAktif = $allTrainers->where('status', Trainer::STATUS_AKTIF)->count();
            $totalNonaktif = $allTrainers->where('status', Trainer::STATUS_NONAKTIF)->count();
            $totalPending = $allTrainers->where('status', Trainer::STATUS_PENDING)->count();
            
            // Query dengan join dan filter untuk data yang akan ditampilkan
            $query = Trainer::with(['specialisasi', 'user', 'schedules'])
                ->join('users', 'trainers.id', '=', 'users.trainer_id')
                ->select('trainers.*');
            
            // Filter berdasarkan status
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
            
            // Generate filename dengan status filter
            $filename = 'Laporan_Trainer_' . ucfirst($statusFilter) . '_' . date('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Gagal export PDF trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $trainers = Trainer::with(['specialisasi', 'user'])->latest()->paginate(10);
        return view('pages.trainer.index', compact('trainers'));
    }

    public function create()
    {
        $specialisasis = Specialisasi::all();
        return view('pages.trainer.create', compact('specialisasis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Data User
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8|confirmed',
            'photo'           => 'required|image|mimes:jpg,jpeg,png|max:2048',
            
            // Data Trainer
            'id_specialisasi' => 'required|exists:specialisasis,id',
            'rfid'            => 'required|unique:trainers,rfid|max:30',
            'no_telp'         => 'required|string|max:50',
            'experience'      => 'required|string|max:100',
            'tgl_gabung'      => 'required|date',
            'keterangan'      => 'nullable|string|max:100',
            'tempat_lahir'    => 'required|string',
            'tgl_lahir'       => 'required|date',
            'jenis_kelamin'   => 'required|string|max:20',
            'alamat'          => 'required|string',

            // Validasi jadwal
            'jadwal.*.day_of_week' => 'required|string',
            'jadwal.*.start_time'  => 'required',
            'jadwal.*.end_time'    => 'required',
        ]);

        DB::beginTransaction();
        try {
            // 1️⃣ Upload foto ke folder trainers
            $photoPath = $request->file('photo')->store('trainers', 'public');

            // 2️⃣ Buat Trainer dengan status 'pending' (default) - TANPA PHOTO
            $trainer = Trainer::create([
                'id_specialisasi' => $request->id_specialisasi,
                'rfid'            => $request->rfid,
                'no_telp'         => $request->no_telp,
                'experience'      => $request->experience,
                'tgl_gabung'      => $request->tgl_gabung,
                'status'          => Trainer::STATUS_PENDING,
                'keterangan'      => $request->keterangan,
                'tempat_lahir'    => $request->tempat_lahir,
                'tgl_lahir'       => $request->tgl_lahir,
                'jenis_kelamin'   => $request->jenis_kelamin,
                'alamat'          => $request->alamat,
                'sesi_sudah_dijalani' => 0,
                'sesi_belum_dijalani' => 0,
            ]);

            // 3️⃣ Buat User dan link ke trainer - DENGAN PHOTO
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'trainer_id' => $trainer->id,
                'photo'      => $photoPath, // Photo disimpan di users
            ]);

            // 4️⃣ Assign role 'trainer'
            $user->assignRole('trainer');

            // 5️⃣ Simpan jadwal
            if($request->has('jadwal')){
                foreach($request->jadwal as $j){
                    $trainer->schedules()->create([
                        'day_of_week' => $j['day_of_week'],
                        'start_time'  => $j['start_time'],
                        'end_time'    => $j['end_time'],
                    ]);
                }
            }

            // 6️⃣ Trigger event untuk kirim email verifikasi
            event(new Registered($user));

            // Kirim email verifikasi secara manual (karena user tidak login)
            $user->sendEmailVerificationNotification();

            DB::commit();

            return redirect()->route('trainer.index')
                ->with('success', 'Trainer berhasil ditambahkan. Email verifikasi telah dikirim ke ' . $user->email);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus foto jika ada error
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
            // Data User
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $trainer->user->id,
            'password'        => 'nullable|string|min:8|confirmed',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            
            // Data Trainer
            'id_specialisasi' => 'required|exists:specialisasis,id',
            'rfid'            => 'required|max:30|unique:trainers,rfid,' . $trainer->id,
            'no_telp'         => 'required|string|max:50',
            'experience'      => 'required|string|max:100',
            'tgl_gabung'      => 'required|date',
            'keterangan'      => 'nullable|string|max:100',
            'tempat_lahir'    => 'required|string',
            'tgl_lahir'       => 'required|date',
            'jenis_kelamin'   => 'required|string|max:20',
            'alamat'          => 'required|string',

            // Validasi jadwal
            'jadwal.*.day_of_week' => 'required|string',
            'jadwal.*.start_time'  => 'required',
            'jadwal.*.end_time'    => 'required',
        ]);

        DB::beginTransaction();
        try {
            // 1️⃣ Update User
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            // Handle photo upload untuk USER
            if($request->hasFile('photo')){
                // Hapus foto lama dari USER
                if ($trainer->user->photo && Storage::disk('public')->exists($trainer->user->photo)) {
                    Storage::disk('public')->delete($trainer->user->photo);
                }
                $userData['photo'] = $request->file('photo')->store('trainers', 'public');
            }

            $trainer->user->update($userData);

            // 2️⃣ Update Trainer (TANPA photo karena sudah dipindah ke users)
            $trainerData = $request->only([
                'id_specialisasi', 'rfid', 'no_telp', 'experience', 'tgl_gabung', 
                'keterangan', 'tempat_lahir', 'tgl_lahir', 'jenis_kelamin', 'alamat'
            ]);

            $trainer->update($trainerData);

            // 3️⃣ Update jadwal
            $trainer->schedules()->delete();
            if($request->has('jadwal')){
                foreach($request->jadwal as $j){
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
            Log::error('Gagal update trainer', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update trainer: ' . $e->getMessage());
        }
    }

    /**
     * Update status trainer (khusus admin)
     */
    public function updateStatus(Request $request, Trainer $trainer)
    {
        $request->validate([
            'status' => 'required|in:' . Trainer::STATUS_PENDING . ',' . Trainer::STATUS_NONAKTIF . ',' . Trainer::STATUS_AKTIF
        ]);

        try {
            $trainer->update([
                'status' => $request->status
            ]);

            return redirect()->back()
                ->with('success', 'Status trainer berhasil diperbarui menjadi ' . $trainer->status_label['text']);

        } catch (\Exception $e) {
            Log::error('Gagal update status trainer', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal mengubah status trainer');
        }
    }

    public function destroy(Trainer $trainer)
    {
        DB::beginTransaction();
        try {
            // Simpan user untuk dihapus
            $user = $trainer->user;

            // Hapus photo dari USER (bukan trainer lagi)
            if ($user && $user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            // Hapus jadwal
            $trainer->schedules()->delete();

            // Hapus trainer
            $trainer->delete();

            // Hapus user
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('trainer.index')
                ->with('success', 'Trainer dan akun login berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus trainer', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus trainer: ' . $e->getMessage());
        }
    }
}