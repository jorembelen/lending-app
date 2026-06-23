<?php

namespace App\Livewire\Collector;

use App\Models\Payment;
use App\Models\ScheduleItem;
use Livewire\Component;

class EndOfDaySummaryComponent extends Component
{
    public function getSummaryProperty(): array
    {
        $collectorId    = auth()->id();
        $totalCollected = Payment::where('collector_user_id', $collectorId)
            ->whereDate('collected_at', today())->sum('amount');

        $assigned  = ScheduleItem::whereDate('due_date', today())->count();
        $completed = Payment::where('collector_user_id', $collectorId)
            ->whereDate('collected_at', today())->count();
        $missed    = max(0, $assigned - $completed);
        $efficiency = $assigned > 0 ? round(($completed / $assigned) * 100, 1) : 0;

        return compact('totalCollected', 'assigned', 'completed', 'missed', 'efficiency');
    }

    public function getMissedProperty()
    {
        $collectorId = auth()->id();
        $paidLoanIds = Payment::where('collector_user_id', $collectorId)
            ->whereDate('collected_at', today())->pluck('loan_id');

        return ScheduleItem::with('loan.borrower')
            ->whereDate('due_date', today())
            ->whereNotIn('loan_id', $paidLoanIds)
            ->get();
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
