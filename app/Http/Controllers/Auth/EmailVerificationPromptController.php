<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Helpers\RoleRedirectHelper;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        return $user->hasVerifiedEmail()
            ? redirect()->intended(RoleRedirectHelper::redirectBasedOnRole($user))
            : view('authentication.verify-email');
    }
}
