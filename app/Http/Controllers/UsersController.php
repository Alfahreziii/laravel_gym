<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function usersList()
    {
        $users = User::latest()->get();
        return view('users/usersList', compact('users'));
    }

    /**
     * Update role user
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'role' => 'required|in:admin,spv,guest'
        ], [
            'role.required' => 'Role harus dipilih',
            'role.in' => 'Role tidak valid. Pilih antara Admin, SPV, atau Guest'
        ]);

        try {
            DB::beginTransaction();

            // Cari user berdasarkan ID
            $user = User::findOrFail($id);

            // Cek jika user mencoba mengubah role diri sendiri
            if (auth()->id() == $user->id) {
                return redirect()->back()->with('danger', 'Anda tidak dapat mengubah role diri sendiri!');
            }

            // Hapus semua role lama
            $user->syncRoles([]);

            // Assign role baru
            $user->assignRole($request->role);

            DB::commit();

            // Role name untuk display
            $roleDisplay = [
                'admin' => 'Admin',
                'spv' => 'Supervisor (SPV)',
                'guest' => 'Guest'
            ];

            return redirect()->back()->with('success', "Role user {$user->name} berhasil diubah menjadi {$roleDisplay[$request->role]}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update roles (opsional - untuk update banyak user sekaligus)
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|in:admin,spv,guest'
        ]);

        try {
            DB::beginTransaction();

            $users = User::whereIn('id', $request->user_ids)->get();
            
            foreach ($users as $user) {
                // Jangan ubah role user yang sedang login
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
    
    
    public function viewProfile()
    {
        return view('users/viewProfile');
    }
    
}
