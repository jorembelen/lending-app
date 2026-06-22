<?php

namespace App\Livewire\Collector;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginComponent extends Component
{
    public string $email    = '';
    public string $password = '';
    public bool   $remember = false;

    protected array $rules = [
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ];

    protected array $messages = [
        'email.required'    => 'Work email is required.',
        'email.email'       => 'Enter a valid email address.',
        'password.required' => 'Password is required.',
        'password.min'      => 'Password must be at least 6 characters.',
    ];

    public function login(): void
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            $this->redirect(route('collector.route'), navigate: true);
            return;
        }

        $this->addError('email', 'Invalid credentials. Please try again.');
    }

    public function render()
    {
        return view('livewire.collector.login-component');
    }
}
