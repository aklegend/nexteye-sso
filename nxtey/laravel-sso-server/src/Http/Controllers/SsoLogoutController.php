<?php

namespace Nxtey\SsoServer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoLogoutController extends Controller
{
    /**
     * Revoke Passport tokens and clear global session contexts safely.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // 1. Find and revoke all active access tokens for this specific user session
            $user->tokens->each(function ($token) {
                $token->revoke();
            });
        }

        // 2. Clear out the central ://nxtey.com session identifiers completely
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Read the intended return destination parameter safely
        $fallbackUrl = $request->input('return_to', config('app.url'));

        // 4. Secure validation: Only allow redirects back to your own known .nxtey.com subdomains
        $parsedUrl = parse_url($fallbackUrl);
        if (isset($parsedUrl['host']) && !str_ends_with($parsedUrl['host'], '.nxtey.com')) {
            // Halt redirection if an outside attacker tries to hijack the redirect path
            return redirect()->to(config('app.url'));
        }

        return redirect()->to($fallbackUrl);
    }
}