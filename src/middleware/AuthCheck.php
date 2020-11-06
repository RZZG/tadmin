<?php

namespace tadmin\middleware;

use tadmin\service\auth\facade\Auth;

class AuthCheck
{
    public function handle($request, \Closure $next)
    {
        if (Auth::guard()->guest()) {
            return redirect_route('tadmin.auth.passport.login');
        }

        return $next($request);
    }
}
