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
                $socialiteUser = Socialite::driver('nxtey')->user();
            } catch (Throwable $e) {
                Log::error('Nxtey SSO Callback Failed: ' . $e->getMessage());
                return redirect()->route('login')->withErrors(['sso' => 'SSO authentication failed or was denied.']);
            }
    
            try {
                $userModel = Auth::guard()->getProvider()->getModel();
                $userTable = (new $userModel)->getTable();
                
                // Dynamically check if the column exists to prevent SQL errors on diverse third-party apps
                $hasVerifiedAt = \Illuminate\Support\Facades\Schema::hasColumn($userTable, 'email_verified_at');
    
                $payload = [
                    'name' => $socialiteUser->getName() ?: $socialiteUser->getNickname() ?: 'SSO User',
                    'password' => Hash::make(Str::random(32)), 
                ];
    
                if ($hasVerifiedAt) {
                    $payload['email_verified_at'] = now();
                }
    
                $user = $userModel::updateOrCreate(
                    ['email' => $socialiteUser->getEmail()],
                    $payload
                );
    
                Auth::login($user, true);
                Session::regenerate();
    
                return redirect()->intended(config('nxtey-sso.login_redirect_path', '/dashboard'));
    
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
