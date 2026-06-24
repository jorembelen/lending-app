<?php

namespace App\Livewire\Collector;

use App\Models\Payment;
use Livewire\Component;

class ProfileComponent extends Component
{
    /**
     * This collector's collection performance for today (valid payments only),
     * mirroring how the daily summary derives its figures.
     */
    public function getTodayStatsProperty(): array
    {
        $query = Payment::where('collector_user_id', auth()->id())
            ->where('is_voided', false)
            ->whereDate('collected_at', today());

        return [
            'count'     => (int) $query->clone()->count(),
            'collected' => (float) $query->clone()->sum('amount'),
        ];
    }

    public function logout(): void
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('collector.login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.collector.profile-component')
            ->layout('components.layout.collector-shell', [
                'title'     => 'Profile',
                'activeTab' => 'profile',
            ]);
    }
}
