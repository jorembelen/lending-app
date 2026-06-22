<?php

namespace App\Livewire\Borrower;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginComponent extends Component
{
    public string $phone    = '';
    public string $pin      = '';

    protected array $rules = [
        'phone' => 'required',
        'pin'   => 'required|digits:4',
    ];

    public function login(): void
    {
        $this->validate();

        if (Auth::attempt(['phone' => $this->phone, 'password' => $this->pin], true)) {
            session()->regenerate();
            $this->redirect(route('borrower.home'), navigate: true);
            return;
        }

        $this->addError('phone', 'Invalid phone number or PIN.');
    }

    public function render()
    {
        return view('livewire.borrower.login-component')
            ->layout('components.layout.standalone-page', ['title' => 'Voltage — Borrower Login']);
    }
}
