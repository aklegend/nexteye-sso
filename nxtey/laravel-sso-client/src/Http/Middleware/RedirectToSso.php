<?php

namespace Nxtey\SsoClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectToSso
{
    public function handle(Request $request, Closure $next)
    {
        // If the user is already authenticated, let them pass
        if ($request->user()) {
            return $next($request);
        }

        // Intercept common Laravel auth routes and redirect to SSO
        $interceptedPaths = ['/login', '/register', '/password/reset', '/forgot-password'];
        
        if (in_array($request->path(), $interceptedPaths) || str_starts_with($request->path(), 'password/')) {
            return redirect()->route('sso.login');
        }

        return $next($request);
    }
}