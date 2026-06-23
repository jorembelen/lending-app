<?php

namespace App\Livewire\Collector;

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

    public function getLoanProperty(): ?Loan
    {
        return Loan::where('borrower_id', $this->borrowerId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function getScheduleProperty()
    {
        if (! $this->loan) { return collect(); }

        return $this->loan->scheduleItems()
            ->orderBy('due_date')
            ->get()
            ->map(fn ($item) => [
                'due_date'       => $item->due_date,
                'amount'         => $item->amount_due,
                'status'         => $item->status,
                'installment_no' => $item->sequence_number ?? null,
                'method'         => null,
            ]);
    }

    public function render()
    {
        return view('livewire.collector.borrower-detail-component')
            ->layout('components.layout.collector-shell', [
                'title'    => 'Route Details',
                'activeTab'=> 'route',
                'showBack' => true,
                'backHref' => route('collector.route'),
            ]);
    }
}
