<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class SettingsComponent extends Component
{
    public string $activeTab = 'general'; // general | holidays | loan-rates

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.admin.settings-component')
            ->layout('components.layout.admin-shell', [
                'title'     => 'Settings',
                'activeNav' => 'settings',
            ]);
    }
}
