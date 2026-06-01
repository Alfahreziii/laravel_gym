<?php

namespace App\Helpers;

class RoleRedirectHelper
{
    /**
     * Redirect user berdasarkan role.
     */
    public static function redirectBasedOnRole($user)
    {
        $roleFallbacks = [
            'admin'   => route('dashboard'),
            'guest'   => route('kehadiranmember.index'),
            'spv'     => route('dashboard'),
            'trainer' => route('trainer.dashboard'),
            'member'  => route('member.profile'),
        ];

        foreach ($user->getRoleNames() as $role) {
            $roleLower = strtolower($role);
            if (isset($roleFallbacks[$roleLower])) {
                return $roleFallbacks[$roleLower];
            }
        }

        return route('login');
    }
}
