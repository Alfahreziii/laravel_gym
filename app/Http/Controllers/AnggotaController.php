<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AnggotaController extends Controller
{
    public function exportPdf(Request $request)
    {
        try {
            $statusFilter = $request->input('status_filter', 'all');
            
            // Hitung statistik dari SEMUA data (tidak terfilter)
            $allAnggotas = Anggota::with(['anggotaMemberships' => function($query) {
                $query->latest('tgl_selesai');
            }])->get();
            
            $totalAnggota = $allAnggotas->count();
            $totalAktif = $allAnggotas->filter(function($anggota) {
                return $anggota->status_keanggotaan === true;
            })->count();
            $totalTidakAktif = $allAnggotas->filter(function($anggota) {
                return $anggota->status_keanggotaan === false;
            })->count();
            
            // Query dengan join dan filter untuk data yang akan ditampilkan
            $query = Anggota::with(['anggotaMemberships', 'user'])
                ->join('users', 'anggotas.id', '=', 'users.anggota_id')
                ->select('anggotas.*');
            
            // Filter berdasarkan status
            if ($statusFilter === 'aktif') {
                $query->whereHas('anggotaMemberships', function($q) {
                    $q->where('is_active', true);
                });
                $title = 'Laporan Anggota Aktif';
            } elseif ($statusFilter === 'tidak_aktif') {
                $query->whereDoesntHave('anggotaMemberships', function($q) {
                    $q->where('is_active', true);
                });
                $title = 'Laporan Anggota Tidak Aktif';
            } else {
                $title = 'Laporan Semua Anggota';
            }
            
            $anggotas = $query->orderBy('users.name', 'asc')->get();

            $pdf = Pdf::loadView('pages.anggota.pdf', compact(
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

            return redirect()->back()
                ->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $anggotas = Anggota::with(['user', 'anggotaMemberships'])->latest()->paginate(10);
        return view('pages.anggota.index', compact('anggotas'));
    }

    public function create()
    {
        return view('pages.anggota.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            // Data User
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:8|confirmed',
            'photo'            => 'required|image|mimes:jpg,jpeg,png|max:2048',
            
            // Data Anggota
            'id_kartu'         => 'required|string|max:30|unique:anggotas,id_kartu',
            'no_telp'          => 'required|string|max:50',
            'alamat'           => 'required|string',
            'gol_darah'        => 'required|string|max:2',
            'tinggi'           => 'required|integer',
            'berat'            => 'required|integer',
            'tempat_lahir'     => 'required|string',
            'tgl_lahir'        => 'required|date',
            'tgl_daftar'       => 'required|date',
            'jenis_kelamin'    => 'required|string|max:20',
            'riwayat_kesehatan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 1️⃣ Upload foto ke folder anggotas
            $photoPath = $request->file('photo')->store('anggotas', 'public');

            // 2️⃣ Buat Anggota - TANPA name dan photo
            $anggota = Anggota::create([
                'id_kartu'         => $request->id_kartu,
                'no_telp'          => $request->no_telp,
                'alamat'           => $request->alamat,
                'gol_darah'        => $request->gol_darah,
                'tinggi'           => $request->tinggi,
                'berat'            => $request->berat,
                'tempat_lahir'     => $request->tempat_lahir,
                'tgl_lahir'        => $request->tgl_lahir,
                'tgl_daftar'       => $request->tgl_daftar,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'riwayat_kesehatan' => $request->riwayat_kesehatan,
            ]);

            // 3️⃣ Buat User dan link ke anggota - DENGAN name dan photo
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'anggota_id' => $anggota->id,
                'photo'      => $photoPath,
            ]);

            // 4️⃣ Assign role 'member'
            $user->assignRole('member');

            // 5️⃣ Trigger event untuk kirim email verifikasi
            event(new Registered($user));

            // Kirim email verifikasi secara manual
            $user->sendEmailVerificationNotification();

            DB::commit();

            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil ditambahkan. Email verifikasi telah dikirim ke ' . $user->email);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus foto jika ada error
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
        return view('pages.anggota.edit', compact('anggota'));
    }

    public function update(Request $request, Anggota $anggota)
    {
        $request->validate([
            // Data User
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $anggota->user->id,
            'password'         => 'nullable|string|min:8|confirmed',
            'photo'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            
            // Data Anggota
            'id_kartu'         => 'required|string|max:30|unique:anggotas,id_kartu,' . $anggota->id,
            'no_telp'          => 'required|string|max:50',
            'alamat'           => 'required|string',
            'gol_darah'        => 'required|string|max:2',
            'tinggi'           => 'required|integer',
            'berat'            => 'required|integer',
            'tempat_lahir'     => 'required|string',
            'tgl_lahir'        => 'required|date',
            'tgl_daftar'       => 'required|date',
            'jenis_kelamin'    => 'required|string|max:20',
            'riwayat_kesehatan' => 'nullable|string',
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
                if ($anggota->user->photo && Storage::disk('public')->exists($anggota->user->photo)) {
                    Storage::disk('public')->delete($anggota->user->photo);
                }
                $userData['photo'] = $request->file('photo')->store('anggotas', 'public');
            }

            $anggota->user->update($userData);

            // 2️⃣ Update Anggota (TANPA name dan photo)
            $anggotaData = $request->only([
                'id_kartu', 'no_telp', 'alamat', 'gol_darah', 'tinggi', 'berat',
                'tempat_lahir', 'tgl_lahir', 'tgl_daftar', 'jenis_kelamin', 'riwayat_kesehatan'
            ]);

            $anggota->update($anggotaData);

            DB::commit();

            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update anggota', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update anggota: ' . $e->getMessage());
        }
    }

    public function destroy(Anggota $anggota)
    {
        DB::beginTransaction();
        try {
            // Simpan user untuk dihapus
            $user = $anggota->user;

            // Hapus photo dari USER
            if ($user && $user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            // Hapus anggota
            $anggota->delete();

            // Hapus user
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('anggota.index')
                ->with('success', 'Anggota dan akun login berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus anggota', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus anggota: ' . $e->getMessage());
        }
    }
}