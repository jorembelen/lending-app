<?php

namespace App\Livewire\Collector;

use App\Models\ScheduleItem;
use Illuminate\Support\Collection;
use Livewire\Component;

class TodayRouteComponent extends Component
{
    public string $search = '';
    public string $filter = 'all';  // all | pending | partially_paid | paid | missed

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * Single source of truth: every stop on today's route, UNFILTERED.
     * Includes items due today plus any still-unpaid arrears carried over
     * from earlier days (so collectors never lose track of missed stops).
     */
    public function getRouteItemsProperty(): Collection
    {
        return ScheduleItem::with(['loan.borrower'])
            ->whereHas('loan', fn ($q) => $q->where('status', 'active'))
            ->where(function ($q) {
                $q->whereDate('due_date', today())
                  ->orWhere(fn ($q2) => $q2
                      ->whereDate('due_date', '<', today())
                      ->whereIn('status', ['pending', 'partially_paid', 'missed']));
            })
            ->orderByRaw("FIELD(status, 'missed', 'partially_paid', 'pending', 'paid')")
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Filtered + searched view of the route, mapped for the list rows.
     */
    public function getBorrowersProperty(): Collection
    {
        $term = trim(mb_strtolower($this->search));

        return $this->routeItems
            ->when($this->filter !== 'all', fn ($c) => $c->where('status', $this->filter))
            ->when($term !== '', fn ($c) => $c->filter(function ($item) use ($term) {
                $b = $item->loan?->borrower;
                if (! $b) { return false; }
                return str_contains(mb_strtolower($b->full_name ?? ''), $term)
                    || str_contains(mb_strtolower($b->borrower_code ?? ''), $term)
                    || str_contains(mb_strtolower($b->phone_number ?? ''), $term);
            }))
            ->map(function ($item) {
                $b = $item->loan?->borrower;
                $paidAt = $item->loan?->payments()
                    ->where('is_voided', false)
                    ->whereDate('collected_at', today())
                    ->latest('collected_at')
                    ->value('collected_at');

                return [
                    'name'       => $b->full_name ?? 'Unknown',
                    'address'    => $b->address ?? '',
                    'loan_id'    => $item->loan_id,
                    'amount_due' => (float) $item->amount_due - (float) $item->amount_paid,
                    'status'     => $item->status,
                    'avatar'     => null,
                    'paid_at'    => $paidAt?->format('h:i A'),
                    'href'       => route('collector.borrower', $item->loan?->borrower_id ?? $item->loan_id),
                ];
            })
            ->values();
    }

    public function getMapUrlProperty(): string
    {
        $addresses = $this->routeItems
            ->map(fn ($item) => $item->loan?->borrower?->address)
            ->filter()
            ->unique()
            ->values();

        if ($addresses->isEmpty()) {
            return 'https://www.google.com/maps';
        }

        if ($addresses->count() === 1) {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($addresses->first());
        }

        return 'https://www.google.com/maps/dir/' . $addresses->map(fn ($a) => urlencode($a))->join('/');
    }

    /**
     * Day totals, always computed from the FULL route (never the filtered view),
     * using real per-item amounts so partial payments are reflected accurately.
     */
    public function getSummaryProperty(): array
    {
        $items = $this->routeItems;

        $target    = (float) $items->sum('amount_due');
        $collected = (float) $items->sum('amount_paid');
        $remaining = $items->where('status', '!=', 'paid')->count();
        $percent   = $target > 0 ? (int) round(($collected / $target) * 100) : 0;

        return compact('target', 'collected', 'remaining', 'percent') + [
            'total' => $target, // alias kept for the existing blade
        ];
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
