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
        $roleFallbacks = [
            'admin' => route('dashboard'),
            'guest' => route('kehadiranmember.index'),
            'spv'   => route('dashboard'),
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
