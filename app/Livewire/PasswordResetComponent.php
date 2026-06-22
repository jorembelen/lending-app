<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class PasswordResetComponent extends Component
{
    public $password;
    public $password_confirmation;

    protected $rules = [
        'password' => [
            'required',
            'confirmed',
        ],
        'password_confirmation' => 'required',
    ];

    protected $messages = [
        'password.required' => 'Password is required.',
        'password.confirmed' => 'Password confirmation does not match.',
        'password_confirmation.required' => 'Password confirmation is required.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.password-reset-component')->extends('auth.main')->section('content');
    }

    public function resetPassword() 
    {
        // Add password complexity rules dynamically
        $this->rules['password'][] = Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();

        $data = $this->validate();

        try {
            $user = Auth::user();
            
            // Check if new password is same as current password
            if (Hash::check($this->password, $user->password)) {
                return $this->addError('password', 'The new password cannot be the same as your current password.');
            }

            // Update password with additional security measures
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($data['password']),
                    'password_reset' => 1,
                    'updated_at' => now(),
                ]);

            // Log the password reset for security audit
            Log::info('Password reset completed', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Clear form fields
            $this->reset(['password', 'password_confirmation']);

            // Show success message
            session()->flash('success', 'Your password has been successfully updated!');

            // Redirect to dashboard
            return redirect()->route('home');

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'ip_address' => request()->ip(),
            ]);

            session()->flash('error', 'An error occurred while updating your password. Please try again.');
        }
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
