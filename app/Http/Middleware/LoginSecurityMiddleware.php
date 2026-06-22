<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Google2FAAuthenticator;

class LoginSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('app.env') !== 'production') {
            return $next($request);
        }

        $authenticator = app(Google2FAAuthenticator::class)->boot($request);
        $user = auth()->user();

        // Trusted device logic
        $trustedDeviceToken = $request->cookie('trusted_device');
        if ($trustedDeviceToken && $user) {
            $trustedDevice = \App\Models\TrustedDevice::where('user_id', $user->id)
                ->where('device_token', $trustedDeviceToken)
                ->where('expires_at', '>', now())
                ->first();
            if ($trustedDevice) {
                // Valid trusted device, skip OTP
                return $next($request);
            }
        }

        if ($authenticator->isAuthenticated() && $user->loginSecurity) {
            if($user->loginSecurity->google2fa_enable) {
                return $next($request);
            }
        }
        if(auth()->user()->loginSecurity === null) {
            return redirect()->route('show2fa');
        }
        return $authenticator->makeRequestOneTimePasswordResponse();
    }
}
