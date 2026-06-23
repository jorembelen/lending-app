<?php

namespace App\Livewire\Borrower;

use App\Models\LoyaltyTier;
use Livewire\Component;

class RewardsComponent extends Component
{
    public function getTiersProperty()
    {
        return LoyaltyTier::orderBy('rank')->get();
    }

    public function getBorrowerProperty()
    {
        return auth()->user();
    }

    public function render()
    {
        return view('livewire.borrower.rewards-component')
            ->layout('components.layout.borrower-shell', [
                'title'     => 'Rewards',
                'activeTab' => 'rewards',
            ]);
    }
}
