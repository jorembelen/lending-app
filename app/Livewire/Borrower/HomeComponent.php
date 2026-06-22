<?php

namespace App\Livewire\Borrower;

use App\Models\Loan;
use App\Models\Payment;
use Livewire\Component;

class HomeComponent extends Component
{
    public function getLoanProperty(): ?Loan
    {
        return Loan::where('borrower_id', auth()->id())
            ->whereIn('status', ['active', 'overdue'])
            ->orderByDesc('created_at')
            ->first();
    }

    public function getRecentPaymentsProperty()
    {
        if (! $this->loan) { return collect(); }
        return Payment::where('loan_id', $this->loan->id)
            ->orderByDesc('collected_at')
            ->limit(5)
            ->get();
    }

    public function getLoyaltyProperty(): array
    {
        $borrower = auth()->user();
        return [
            'tier'        => $borrower->loyalty_tier ?? 'Standard',
            'points'      => $borrower->loyalty_points ?? 0,
            'next_tier'   => 'Preferred',
            'next_points' => 1000,
            'streak'      => $borrower->payment_streak ?? 0,
        ];
    }

    public function render()
    {
        return view('livewire.borrower.home-component')
            ->layout('components.layout.borrower-shell', [
                'title'     => 'Home',
                'activeTab' => 'home',
            ]);
    }
}
