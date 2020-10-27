<?php

namespace App\Http\Middleware;

use DB;
use Auth;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);
        $user = Auth::user();
        if ($user->force_logout == 1) {
            Auth::logout();
            return redirect()->route('login');
        }

        // if(!DB::table('tbl_whitelist')->where('ip_address', $request->ip())->exists()){
        //     abort(403,'Access denied');
        // }
         
        return $next($request);
    }
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
