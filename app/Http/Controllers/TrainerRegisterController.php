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

class TrainerRegisterController extends Controller
{
    public function showForm()
    {
        $specialisasis = Specialisasi::all();
        return view('authentication.register-trainer', compact('specialisasis'));
    }

    public function register(Request $request)
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
            'tempat_lahir'    => 'required|string',
            'tgl_lahir'       => 'required|date',
            'jenis_kelamin'   => 'required|string|max:20',
            'alamat'          => 'required|string',

            // Validasi jadwal
            'jadwal.*.day_of_week' => 'required|string',
            'jadwal.*.start_time'  => 'required',
            'jadwal.*.end_time'    => 'required',
            
            // Terms & Conditions
            'terms'           => 'required|accepted',
        ]);

        DB::beginTransaction();
        try {
            // 1️⃣ Upload foto
            $photoPath = $request->file('photo')->store('trainers', 'public');

            // 2️⃣ Buat Trainer dengan status 'pending'
            $trainer = Trainer::create([
                'id_specialisasi' => $request->id_specialisasi,
                'rfid'            => $request->rfid,
                'no_telp'         => $request->no_telp,
                'experience'      => $request->experience,
                'tgl_gabung'      => now(),
                'status'          => Trainer::STATUS_PENDING,
                'keterangan'      => 'Pendaftaran mandiri',
                'tempat_lahir'    => $request->tempat_lahir,
                'tgl_lahir'       => $request->tgl_lahir,
                'jenis_kelamin'   => $request->jenis_kelamin,
                'alamat'          => $request->alamat,
                'sesi_sudah_dijalani' => 0,
                'sesi_belum_dijalani' => 0,
            ]);

            // 3️⃣ Buat User
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'trainer_id' => $trainer->id,
                'photo'      => $photoPath,
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
            $user->sendEmailVerificationNotification();

            // 7️⃣ Login otomatis
            \Auth::login($user);

            DB::commit();

            return redirect()->route('verification.notice')->with('status', 'verification-link-sent');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            
            Log::error('Gagal register trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal melakukan pendaftaran: ' . $e->getMessage());
        }
    }
}