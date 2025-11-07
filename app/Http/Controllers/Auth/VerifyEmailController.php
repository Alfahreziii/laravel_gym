<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Helpers\RoleRedirectHelper;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Kalau sudah terverifikasi sebelumnya
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RoleRedirectHelper::redirectBasedOnRole($user) . '?verified=1');
        }

        // Tandai sebagai verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(RoleRedirectHelper::redirectBasedOnRole($user) . '?verified=1');
    }
}
