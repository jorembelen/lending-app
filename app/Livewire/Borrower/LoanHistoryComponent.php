<?php

namespace App\Livewire\Borrower;

use App\Models\Loan;
use Livewire\Component;

class LoanHistoryComponent extends Component
{
    public function getLoansProperty()
    {
        return Loan::where('borrower_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.borrower.loan-history-component')
            ->layout('components.layout.borrower-shell', [
                'title'     => 'Loan History',
                'activeTab' => 'history',
            ]);
    }
}
