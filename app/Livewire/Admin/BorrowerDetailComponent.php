<?php

namespace App\Livewire\Admin;

use App\Models\Borrower;
use App\Models\Loan;
use Livewire\Component;

class BorrowerDetailComponent extends Component
{
    public int|string $borrowerId;

    public function mount(int|string $borrowerId): void
    {
        $this->borrowerId = $borrowerId;
    }

    public function getBorrowerProperty(): ?Borrower
    {
        return Borrower::find($this->borrowerId);
    }

    public function getLoansProperty()
    {
        return Loan::where('borrower_id', $this->borrowerId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getActiveLoanProperty(): ?Loan
    {
        return $this->loans->firstWhere('status', 'active')
            ?? $this->loans->first();
    }

    public function render()
    {
        return view('livewire.admin.borrower-detail-component')
            ->layout('components.layout.admin-shell', [
                'title'     => $this->borrower?->name ?? 'Borrower Detail',
                'activeNav' => 'borrowers',
            ]);
    }
}
