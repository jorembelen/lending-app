<?php

namespace App\Livewire;

use App\Mail\LoginLinkMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class LoginComponent extends Component
{
    use ThrottlesLogins;

    protected $maxLoginAttempts = 5;
    protected $lockoutTime = 300;

    public $email, $password;
    public $remember = false;
    public $showEmailTab = false;

    public function render()
    {
        return view('livewire.login-component');
    }

    public function login(Request $request)
    {
        $this->showEmailTab = false;
        $field = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $this->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            $field => $this->email,
            'password' => $this->password,
        ];

        if ($this->hasTooManyLoginAttempts($request)) {
            $ip = $request->ip();
            $userAgent = $request->userAgent();
            
            // Log lockout attempt
            activity()
                ->withProperties([
                    'attributes' => [
                        'attempted_email' => $this->email,
                        'ip_address' => $ip,
                        'user_agent' => $userAgent,
                        'lockout_time' => now()->toDateTimeString(),
                        'status' => 'locked_out',
                        'max_attempts' => $this->maxLoginAttempts,
                        'lockout_duration' => $this->lockoutTime
                    ]
                ])
                ->log("Account locked out for {$this->email} from IP {$ip} - too many failed attempts");
                
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $this->incrementLoginAttempts($request);

        if (Auth::attempt($credentials, $this->remember)) {
            $user = Auth::user();
            $ip = $request->ip();
            $userAgent = $request->userAgent();
            
            // Enhanced login success logging
            activity()
                ->withProperties([
                    'attributes' => [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'ip_address' => $ip,
                        'user_agent' => $userAgent,
                        'login_method' => $field, // email or username
                        'remember_me' => $this->remember,
                        'login_time' => now()->toDateTimeString(),
                        'status' => 'success'
                    ]
                ])
                ->log("Successful login by {$user->name} from IP {$ip}");
                
            return $this->redirect(route('home'));
        } else {
            $ip = $request->ip();
            $userAgent = $request->userAgent();
            
            // Log failed login attempt
            activity()
                ->withProperties([
                    'attributes' => [
                        'attempted_email' => $this->email,
                        'ip_address' => $ip,
                        'user_agent' => $userAgent,
                        'login_method' => $field,
                        'attempt_time' => now()->toDateTimeString(),
                        'status' => 'failed',
                        'reason' => 'invalid_credentials'
                    ]
                ])
                ->log("Failed login attempt for {$this->email} from IP {$ip}");
                
            $this->addError('email', 'Sorry, this credential are invalid!');
        }
    }

    public function emailTab() 
    {
        $this->showEmailTab = true;
    }

    public function link() 
    {
        $this->showEmailTab = true;
        $this->validate([
            'email' => 'required|email|exists:users,email'
        ], ['email.exists' => 'We can' .'t' .' find a user with that email address.']);

        $user = User::whereEmail($this->email)->first();
        $link = URL::temporarySignedRoute('login.token', now()->addHour(), ['user' => $user->id]);
        $logoUrl = asset('assets/logo.png');
        
        // Log magic link generation
        activity()
            ->withProperties([
                'attributes' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'link_expires_at' => now()->addHour()->toDateTimeString(),
                    'action' => 'magic_link_generated'
                ]
            ])
            ->log("Magic login link generated for {$user->name} ({$user->email})");
            
        Mail::to($user)->send(new LoginLinkMail($link, $logoUrl));
        return session()->flash('success', 'Success ... Login link was sent to your email.');
    }

    protected function hasTooManyLoginAttempts(Request $request)
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request),
            $this->maxLoginAttempts,
            $this->lockoutTime
        );
    }

    protected function username()
    {
        return 'email';
    }



}