<?php

namespace Nxtey\SsoServer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoLogoutController extends Controller
{
    public function logout(Request $request)
    {
        if ($request->user()) {
            $token = $request->user()->token();
            if ($token) {
                $token->revoke();
            }
            Auth::logout();
        }
        
        $returnTo = $request->query('return_to', url('/'));
        return redirect($returnTo);
    }
}