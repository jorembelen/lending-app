<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         if(config('app.env') === 'production') {
            // Check if user is authenticated and needs password reset
            if (auth()->check() && auth()->user()->password_reset == 0) {
                // Allow access to password reset page and logout routes
                if ($request->routeIs('reset.password') || $request->routeIs('logout')) {
                    return $next($request);
                }
                
                // Let Livewire requests proceed - they will be handled by BaseComponent
                if ($request->header('X-Livewire')) {
                    return $next($request);
                }
                
                // Redirect to password reset page for regular requests
                return redirect()->route('reset.password');
            }
        }

        return $next($request);
    }
}