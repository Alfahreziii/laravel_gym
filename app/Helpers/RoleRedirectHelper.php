<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class RoleRedirectHelper
{
    /**
     * Redirect user berdasarkan role.
     */
    public static function redirectBasedOnRole($user)
    {
        // Cek apakah user sudah verifikasi email
        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            return route('verification.notice'); // arahkan ke halaman verifikasi
        }

        $roleFallbacks = [
            'admin'   => route('dashboard'),
            'guest'   => route('kehadiranmember.index'),
            'spv'     => route('dashboard'),
            'trainer' => route('trainer.dashboard'),
        ];

        foreach ($user->getRoleNames() as $role) {
            $roleLower = strtolower($role); // aman case-insensitive
            if (isset($roleFallbacks[$roleLower])) {
                return $roleFallbacks[$roleLower];
            }
        }

        return route('login');
    }

}
