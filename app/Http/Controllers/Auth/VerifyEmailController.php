<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Helpers\RoleRedirectHelper;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Validasi signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        // Ambil user berdasarkan ID dari URL
        $user = User::findOrFail($request->route('id'));

        // Login user jika belum login
        if (!Auth::check()) {
            Auth::login($user);
        }

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
