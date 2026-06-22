<?php

namespace App\Http\Middleware;

use Closure;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if(auth()->user()->super_user) {

            return $next($request);
        }

        session()->flash('error', 'Sorry, You are not allowed to access the admin page.');

        return redirect()->route('home');

        }

}
