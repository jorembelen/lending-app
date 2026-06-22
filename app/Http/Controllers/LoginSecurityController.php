<?php

namespace App\Http\Controllers;

use App\Models\LoginSecurity;
use function App\Helpers\agent_information;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class LoginSecurityController extends Controller

{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function disable()
    {
       return view('google2fa.disable-2fa');
    }

    /**
     * Show 2FA Setting form
     */
    public function show2faForm(Request $request)
    {
        $user = auth()->user();
        $google2fa_url = "";
        $secret_key = "";

        if($user->loginSecurity()->exists()){
            $google2fa = app('pragmarx.google2fa');
            $google2fa_url = $google2fa->getQRCodeInline(
                config('app.name'),
                $user->email,
                $user->loginSecurity->google2fa_secret
            );
            $secret_key = $user->loginSecurity->google2fa_secret;
        }

        $data = array(
            'user' => $user,
            'secret' => $secret_key,
            'google2fa_url' => $google2fa_url
        );

        return view('auth.2fa_settings')->with('data', $data);
    }

    /**
     * Generate 2FA secret key
     */
    public function generate2faSecret(Request $request){
        $user = auth()->user();
        // Initialise the 2FA class
        $google2fa = app('pragmarx.google2fa');

        // Add the secret key to the registration data
        $login_security = LoginSecurity::firstOrNew(array('user_id' => $user->id));
        $login_security->user_id = $user->id;
        $login_security->google2fa_enable = 0;
        $login_security->google2fa_secret = $google2fa->generateSecretKey();
        $login_security->save();

        // Log the 2FA secret generation activity
        $msg = "2FA secret key was generated for {$user->name} ({$user->email})";
        activity()
            ->causedBy($user)
            ->withProperties([
                'attributes' => [
                    'name' => '2fa secret generated',
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'ip_address' => request()->ip(),
                     'user_agent' => agent_information(request()),
                    'generated_at' => now()->toDateTimeString(),
                ]
            ])
            ->log($msg);

        return redirect('/2fa')->with('success',"Secret key is generated.");
    }

    /**
     * Enable 2FA
     */
    public function enable2fa(Request $request){
        $user = auth()->user();
        $google2fa = app('pragmarx.google2fa');

        $secret = $request->input('secret');
        $valid = $google2fa->verifyKey($user->loginSecurity->google2fa_secret, $secret);

        if($valid){
            $user->loginSecurity->google2fa_enable = 1;
            $user->loginSecurity->save();

            // Trusted device logic
            $deviceToken = bin2hex(random_bytes(32));
            $expiresAt = now()->addDays(7);
            \App\Models\TrustedDevice::create([
                'user_id' => $user->id,
                'device_token' => $deviceToken,
                 'user_agent' => agent_information(request()),
                'ip_address' => request()->ip(),
                'expires_at' => $expiresAt,
            ]);
            // Set signed, encrypted cookie for trusted device
            cookie()->queue(cookie('trusted_device', $deviceToken, 60 * 24 * 7, null, null, true, true, false, 'strict'));

            // Log the 2FA enable activity
            $msg = "2FA (Two-Factor Authentication) was enabled for {$user->name} ({$user->email})";
            activity()
                ->causedBy($user)
                ->withProperties([
                    'attributes' => [
                        'name' => '2fa enabled',
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'ip_address' => request()->ip(),
                         'user_agent' => agent_information(request()),
                        'enabled_at' => now()->toDateTimeString(),
                    ]
                ])
                ->log($msg);

            return redirect('/')->with('success',"2FA is enabled successfully.");
        }else{
            return redirect('2fa')->with('error',"Invalid verification Code, Please try again.");
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2fa(Request $request){
        if ((Hash::check($request->get('current-password'), auth()->user()->password))) {
            // The passwords matches
            $validatedData = $request->validate([
                'current-password' => 'required',
            ]);
            $user = auth()->user();
            $user->loginSecurity->google2fa_enable = 0;
            $user->loginSecurity->save();
            
            // Log the 2FA disable activity
            $msg = "2FA (Two-Factor Authentication) was disabled for {$user->name} ({$user->email})";
            activity()
                ->causedBy($user)
                ->withProperties([
                    'attributes' => [
                        'name' => '2fa disabled',
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'ip_address' => request()->ip(),
                         'user_agent' => agent_information(request()),
                        'disabled_at' => now()->toDateTimeString(),
                    ]
                ])
                ->log($msg);
            
            return redirect('/')->with('success',"2FA is now disabled.");
        }else{
            session()->flash('error', "Your password does not matches with your account password. Please try again.");
            return redirect()->back();
        }
    }

     /**
     * Verify 2FA OTP and set trusted device cookie for already enabled users
     */
    public function verify2fa(Request $request)
    {
        $user = auth()->user();
        $google2fa = app('pragmarx.google2fa');
        $otp = $request->input('one_time_password');
        $valid = $google2fa->verifyKey($user->loginSecurity->google2fa_secret, $otp);

        if ($valid) {
            // Show confirmation view to trust device
            $deviceToken = bin2hex(random_bytes(32));
            $expiresAt = now()->addDays(7);
            $userAgent = agent_information(request());
            $ip = $request->ip();
            // Store device info in session for confirmation
            session([
                'pending_trusted_device' => [
                    'user_id' => $user->id,
                    'device_token' => $deviceToken,
                    'user_agent' => $userAgent,
                    'ip_address' => $ip,
                    'expires_at' => $expiresAt,
                ]
            ]);
            // Log OTP verified but not yet trusted
            $msg = "2FA OTP verified for {$user->name} ({$user->email}), pending device trust confirmation.";
            activity()
                ->causedBy($user)
                ->withProperties([
                    'attributes' => [
                        'name' => '2fa verified',
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'ip_address' => $ip,
                        'user_agent' => $userAgent,
                        'verified_at' => now()->toDateTimeString(),
                        'pending_trust' => true,
                    ]
                ])
                ->log($msg);
            // Show confirmation view
            return view('auth.confirm_trust_device', [
                'user' => $user,
                'device_token' => $deviceToken,
                'user_agent' => $userAgent,
                'ip_address' => $ip,
                'expires_at' => $expiresAt,
            ]);
        } else {
            return redirect()->back()->with('error', 'Invalid OTP. Please try again.');
        }
    }

    /**
     * Confirm trusted device after OTP verification
     */
    public function confirmTrustDevice(Request $request)
    {
        $pending = session('pending_trusted_device');
        $user = auth()->user();
      
        if ($pending) {
            \App\Models\TrustedDevice::create($pending);
            // Set signed, encrypted cookie for trusted device
            cookie()->queue(cookie('trusted_device', $pending['device_token'], 60 * 24 * 7, null, null, true, true, false, 'strict'));
            // Log trusted device confirmation
            $msg = "Trusted device confirmed for {$user->name} ({$user->email})";
            activity()
                ->causedBy($user)
                ->withProperties([
                    'attributes' => [
                        'name' => 'trusted device confirmed',
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'ip_address' => $pending['ip_address'],
                        'user_agent' => $pending['user_agent'],
                        'confirmed_at' => now()->toDateTimeString(),
                    ]
                ])
                ->log($msg);
            // Remove pending from session
            session()->forget('pending_trusted_device');
            // Redirect to profile component
            return redirect('/profile')->with('success', 'Device trusted and added to your profile.');
        }
        // If not trusting, just redirect to dashboard or home
        return redirect('/')->with('info', 'You chose not to trust this device.');
    }

}
