<?php

namespace App\Livewire\Admin;

use App\Models\LoyaltyTier;
use Livewire\Component;

class LoyaltyRebatesComponent extends Component
{
    public function getTiersProperty()
    {
        return LoyaltyTier::orderBy('rank')->get();
    }

    public function render()
    {
        return view('livewire.admin.loyalty-rebates-component')
            ->layout('components.layout.admin-shell', [
                'title'     => 'Loyalty & Rebates',
                'activeNav' => 'loyalty',
            ]);
    }
}
