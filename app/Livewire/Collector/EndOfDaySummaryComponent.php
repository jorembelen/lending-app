<?php

namespace App\Livewire\Collector;

use App\Models\Payment;
use App\Models\ScheduleItem;
use Illuminate\Support\Collection;
use Livewire\Component;

class EndOfDaySummaryComponent extends Component
{
    /**
     * Today's route = schedule items due today on active loans.
     * All figures below derive from this same set so the screen is internally
     * consistent and tracks the real records.
     */
    public function getRouteItemsProperty(): Collection
    {
        return ScheduleItem::with('loan.borrower')
            ->whereHas('loan', fn ($q) => $q
                ->where('status', 'active')
                ->where('assigned_collector_id', auth()->id()))
            ->whereDate('due_date', today())
            ->get();
    }

    public function getSummaryProperty(): array
    {
        $items = $this->routeItems;

        $assigned  = $items->count();
        $completed = $items->whereIn('status', ['paid', 'partially_paid'])->count();
        $missed    = $assigned - $completed;

        // Real money collected today by this collector (valid payments only).
        $totalCollected = (float) Payment::where('collector_user_id', auth()->id())
            ->where('is_voided', false)
            ->whereDate('collected_at', today())
            ->sum('amount');

        $efficiency = $assigned > 0 ? round(($completed / $assigned) * 100, 1) : 0;

        return compact('totalCollected', 'assigned', 'completed', 'missed', 'efficiency');
    }

    /**
     * Stops on today's route that were not fully collected.
     */
    public function getMissedProperty(): Collection
    {
        return $this->routeItems
            ->whereIn('status', ['pending', 'missed'])
            ->values();
    }

    public function render()
    {
        return view('livewire.collector.end-of-day-summary-component')
            ->layout('components.layout.collector-shell', [
                'title'    => 'Daily Summary',
                'activeTab'=> 'summary',
                'showBack' => true,
                'backHref' => route('collector.route'),
            ]);
    }
}
