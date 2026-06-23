<?php

namespace App\Livewire\Collector;

use App\Models\ScheduleItem;
use Livewire\Component;

class TodayRouteComponent extends Component
{
    public string $search  = '';
    public string $filter  = 'all';  // all | pending | overdue | paid

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function getBorrowersProperty()
    {
        return ScheduleItem::with(['loan.borrower'])
            ->whereDate('due_date', today())
            ->whereHas('loan', fn ($q) => $q->where('status', 'active'))
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->when($this->search, fn ($q) =>
                $q->whereHas('loan.borrower', fn ($q) =>
                    $q->where('full_name', 'like', "%{$this->search}%")
                )
            )
            ->get()
            ->map(fn ($item) => [
                'name'       => $item->loan->borrower->full_name ?? 'Unknown',
                'loan_id'    => $item->loan_id,
                'amount_due' => $item->amount_due,
                'status'     => $item->status,
                'avatar'     => null,
                'paid_at'    => $item->loan?->payments()->latest('collected_at')->value('collected_at')?->format('h:i A'),
                'href'       => route('collector.borrower', $item->loan->borrower_id ?? $item->loan_id),
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
