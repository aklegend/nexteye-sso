<?php

namespace Nxtey\SsoClient\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Routing\Controller;
use Throwable;

class SsoController extends Controller
{
    /**
     * Redirect the user to the Nxtey SSO authentication page.
     */
    public function redirect()
    {
        try {
            return Socialite::driver('nxtey')->redirect();
        } catch (Throwable $e) {
            Log::error('Nxtey SSO Redirect Failed: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['sso' => 'Unable to connect to SSO server.']);
        }
    }

    /**
     * Obtain the user information from Nxtey SSO and authenticate locally.
     */
    public function callback(Request $request)
    {
        try {
            // 1. Get user payload from central server
            $socialiteUser = Socialite::driver('nxtey')->user();
        } catch (Throwable $e) {
            Log::error('Nxtey SSO Callback Failed: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['sso' => 'SSO authentication failed or was denied.']);
        }

        try {
            // 2. Dynamically resolve the local User model to ensure compatibility with any third-party script
            $userModel = Auth::guard()->getProvider()->getModel();

            // 3. Update or create the user locally
            // We generate a cryptographically secure random password to prevent local password login,
            // forcing all authentication through the SSO gateway.
            $user = $userModel::updateOrCreate(
                ['email' => $socialiteUser->getEmail()],
                [
                    'name' => $socialiteUser->getName() ?: $socialiteUser->getNickname() ?: 'SSO User',
                    'password' => Hash::make(Str::random(32)), 
                    'email_verified_at' => now(), // SSO implies the email is verified by the central authority
                ]
            );

            // 4. Log the user into the local application
            Auth::login($user, true); // true = remember me

            // 5. Regenerate session to prevent session fixation attacks
            Session::regenerate();

            // 6. Redirect to the configured dashboard or home
            $redirectPath = config('nxtey-sso.login_redirect_path', '/dashboard');
            return redirect()->intended($redirectPath);

        } catch (Throwable $e) {
            Log::error('Nxtey SSO Local Provisioning Failed: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['sso' => 'An error occurred while provisioning your local account.']);
        }
    }

    /**
     * Log the user out locally and redirect to central SSO logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        $serverUrl = rtrim(config('nxtey-sso.server_url'), '/');
        $returnTo = urlencode(url('/')); // Return to client app home after central logout

        // Redirect to central server to revoke the Passport token globally
        return redirect($serverUrl . '/sso/logout?return_to=' . $returnTo);
    }
}