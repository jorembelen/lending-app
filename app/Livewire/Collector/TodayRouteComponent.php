<?php

namespace App\Livewire\Collector;

use App\Models\Loan;
use Livewire\Component;

class TodayRouteComponent extends Component
{
    public string $search     = '';
    public string $filter     = 'all';  // all | pending | overdue | paid

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function getBorrowersProperty()
    {
        $collectorId = auth()->id();

        // Fallback to seeded data if no real loans exist yet
        $query = Loan::with('borrower')
            ->whereHas('borrower', fn ($q) =>
                $q->where('collector_id', $collectorId)
                  ->when($this->search, fn ($q) =>
                      $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('borrower_id', 'like', "%{$this->search}%")
                  )
            )
            ->whereDate('due_date', today());

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return $query->get()->map(fn ($loan) => [
            'name'      => $loan->borrower->name ?? 'Unknown',
            'loan_id'   => $loan->loan_id ?? $loan->id,
            'amount_due'=> $loan->amount_due ?? $loan->daily_payment ?? 0,
            'status'    => $loan->status ?? 'pending',
            'avatar'    => $loan->borrower->avatar ?? null,
            'paid_at'   => $loan->payments()->latest('collected_at')->value('collected_at')?->format('h:i A') ?? null,
            'href'      => route('collector.borrower', $loan->borrower_id ?? $loan->id),
        ]);
    }

    public function getSummaryProperty(): array
    {
        $borrowers = $this->borrowers;
        $total     = $borrowers->sum('amount_due');
        $collected = $borrowers->where('status', 'paid')->sum('amount_due');
        $remaining = $borrowers->where('status', '!=', 'paid')->count();
        $percent   = $total > 0 ? round(($collected / $total) * 100) : 0;

        return compact('total', 'collected', 'remaining', 'percent');
    }

    public function render()
    {
        return view('livewire.collector.today-route-component')
            ->layout('components.layout.collector-shell', [
                'title'     => "Today's Route",
                'activeTab' => 'route',
            ]);
    }
}
