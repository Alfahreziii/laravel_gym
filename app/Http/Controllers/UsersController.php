<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function usersList()
    {
        return view('users/usersList');
    }

    public function datatableUsers(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = User::with('roles')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('roles', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                if ($item->last_activity) {
                    $last          = Carbon::parse($item->last_activity)->setTimezone('Asia/Jakarta');
                    $now           = now()->setTimezone('Asia/Jakarta');
                    $diffInMinutes = abs($now->diffInMinutes($last));
                    $isActive      = $last->diffInMinutes($now, false) <= 2;

                    if ($isActive && $last <= $now) {
                        $status      = 'Active';
                        $statusClass = 'text-green-600 font-semibold';
                        $timeInfo    = '(' . number_format($diffInMinutes, 0) . ' menit yang lalu)';
                    } else {
                        $status      = 'Offline';
                        $statusClass = 'text-gray-400';
                        if ($diffInMinutes < 60) {
                            $timeInfo = '(' . number_format($diffInMinutes, 0) . ' menit yang lalu)';
                        } elseif ($diffInMinutes < 1440) {
                            $timeInfo = '(' . floor($diffInMinutes / 60) . ' jam yang lalu)';
                        } else {
                            $timeInfo = '(' . floor($diffInMinutes / 1440) . ' hari yang lalu)';
                        }
                    }
                } else {
                    $status      = 'Never Active';
                    $statusClass = 'text-gray-300 italic';
                    $timeInfo    = '';
                }

                $currentRole = $item->getRoleNames()->first();

                return [
                    'no'           => (($page - 1) * $perPage) + $index + 1,
                    'id'           => $item->id,
                    'foto'         => $item->foto ? asset('storage/' . $item->foto) : null,
                    'name'         => $item->name,
                    'email'        => $item->email,
                    'role'         => $item->getRoleNames()->implode(', '),
                    'current_role' => $currentRole,
                    'status'       => $status,
                    'status_class' => $statusClass,
                    'time_info'    => $timeInfo,
                    'can_edit_role' => !in_array($currentRole, ['member', 'trainer']),
                    'update_url'   => route('role.update', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Update role user
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,spv,guest,member'
        ], [
            'role.required' => 'Role harus dipilih',
            'role.in' => 'Role tidak valid. Pilih antara Admin, SPV, Guest, atau Member'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            if (auth()->id() == $user->id) {
                return redirect()->back()->with('danger', 'Anda tidak dapat mengubah role diri sendiri!');
            }

            $user->syncRoles([]);
            $user->assignRole($request->role);

            DB::commit();

            $roleDisplay = [
                'admin' => 'Admin',
                'spv' => 'Supervisor (SPV)',
                'guest' => 'Guest',
                'member' => 'Member'
            ];

            return redirect()->back()->with('success', "Role user {$user->name} berhasil diubah menjadi {$roleDisplay[$request->role]}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update roles
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|in:admin,spv,guest,member'
        ]);

        try {
            DB::beginTransaction();

            $users = User::whereIn('id', $request->user_ids)->get();
            
            foreach ($users as $user) {
                if (auth()->id() != $user->id) {
                    $user->syncRoles([]);
                    $user->assignRole($request->role);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Role berhasil diupdate untuk ' . count($users) . ' user!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Show view profile page
     */
    public function viewProfile()
    {
        $user = Auth::user();
        
        // Load relasi trainer jika user adalah trainer
        if ($user->isTrainer() && $user->trainer) {
            $user->load('trainer.specialisasi');
        }
        
        return view('users/viewProfile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan oleh user lain',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Format gambar harus jpg, jpeg, atau png',
            'photo.max' => 'Ukuran gambar maksimal 2MB',
        ]);

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Hapus foto lama jika ada
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                // Upload foto baru
                $userData['photo'] = $request->file('photo')->store('users/photos', 'public');
            }

            $user->update($userData);

            DB::commit();

            return redirect()->back()->with('success', 'Profile berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus foto yang baru diupload jika ada error
            if (isset($userData['photo']) && Storage::disk('public')->exists($userData['photo'])) {
                Storage::disk('public')->delete($userData['photo']);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui profile: ' . $e->getMessage());
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi',
            'password.required' => 'Password baru wajib diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.min' => 'Password minimal 8 karakter',
        ]);

        $user = Auth::user();

        // Cek apakah current password benar
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai'])
                ->withInput();
        }

        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return redirect()->back()->with('success', 'Password berhasil diubah!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }

    /**
     * Delete profile photo
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        try {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
                
                $user->update(['photo' => null]);

                return redirect()->back()->with('success', 'Foto profile berhasil dihapus!');
            }

            return redirect()->back()->with('info', 'Tidak ada foto untuk dihapus');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus foto: ' . $e->getMessage());
        }
    }

    public function updateTrainerProfile(Request $request)
    {
        $user = Auth::user();
        
        // Validasi bahwa user adalah trainer
        if (!$user->isTrainer() || !$user->trainer) {
            return redirect()->back()->with('error', 'Anda bukan trainer.');
        }
        
        // Validasi input
        $validated = $request->validate([
            // Data User
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Data Trainer
            'no_telp' => 'required|string|max:50',
            'alamat' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tgl_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'experience' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:100',
        ]);
        
        DB::beginTransaction();
        try {
            // 1️⃣ Update data user
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            
            // Handle foto - SIMPAN DI USER (sesuai struktur TrainerController)
            if ($request->hasFile('photo')) {
                // Hapus foto lama jika ada
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                
                // Upload foto baru ke folder trainers
                $userData['photo'] = $request->file('photo')->store('trainers', 'public');
            }
            
            $user->update($userData);
            
            // 2️⃣ Update data trainer (TANPA photo karena photo ada di user)
            $trainerData = [
                'no_telp' => $validated['no_telp'],
                'alamat' => $validated['alamat'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tgl_lahir' => $validated['tgl_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'experience' => $validated['experience'],
                'keterangan' => $validated['keterangan'],
            ];
            
            $user->trainer->update($trainerData);
            
            DB::commit();
            return redirect()->back()->with('success', 'Profile berhasil diupdate!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update trainer profile', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function codeGenerator()
    {
        return view('aiapplication/codeGenerator');
    }

    public function addUser()
    {
        return view('users/addUser');
    }
    
    public function usersGrid()
    {
        return view('users/usersGrid');
    }
}