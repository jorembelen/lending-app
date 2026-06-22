<?php

namespace App\Livewire\Borrower;

use App\Models\Loan;
use App\Models\Payment;
use Livewire\Component;

class RepaymentScheduleComponent extends Component
{
    public string $filter = 'all'; // all | paid | pending | overdue

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function getLoanProperty(): ?Loan
    {
        return Loan::where('borrower_id', auth()->id())
            ->whereIn('status', ['active', 'overdue'])
            ->orderByDesc('created_at')
            ->first();
    }

    public function getScheduleProperty()
    {
        if (! $this->loan) { return collect(); }

        return $this->loan->payments()
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->orderBy('due_date')
            ->get();
    }

    public function render()
    {
        return view('livewire.borrower.repayment-schedule-component')
            ->layout('components.layout.borrower-shell', [
                'title'     => 'Schedule',
                'activeTab' => 'schedule',
            ]);
    }
}
