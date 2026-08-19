<?php

namespace Nxtey\SsoServer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            abort(403, 'Unauthorized access to SSO Admin Panel.');
        }
        return $next($request);
    }
}