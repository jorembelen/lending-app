<?php

namespace App\Livewire\Borrower;

use Livewire\Component;

class ProfileComponent extends Component
{
    public function logout(): void
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('borrower.login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.borrower.profile-component')
            ->layout('components.layout.borrower-shell', [
                'title'     => 'Profile',
                'activeTab' => 'profile',
            ]);
    }
}
