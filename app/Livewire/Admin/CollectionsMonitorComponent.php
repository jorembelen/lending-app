<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use App\Models\ScheduleItem;
use App\Models\User;
use Livewire\Component;

class CollectionsMonitorComponent extends Component
{
    public string $view = 'list'; // list | map

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function getGoalProperty(): array
    {
        $target    = (float) config('app.daily_collection_target', 150000);
        $collected = Payment::whereDate('collected_at', today())->sum('amount');
        $percent   = $target > 0 ? min(100, round(($collected / $target) * 100, 1)) : 0;

        return compact('target', 'collected', 'percent');
    }

    public function getActiveCollectorsProperty()
    {
        return User::role('collector')
            ->with(['collectedPayments' => fn ($q) => $q->whereDate('collected_at', today())])
            ->get()
            ->map(function ($user) {
                $totalAssigned  = ScheduleItem::whereDate('due_date', today())->count();
                $totalCollected = $user->collectedPayments->sum('amount');
                $completed      = $user->collectedPayments->count();
                $pct            = $totalAssigned > 0 ? round(($completed / $totalAssigned) * 100) : 0;

                return [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'assigned'   => $totalAssigned,
                    'completed'  => $completed,
                    'percent'    => $pct,
                    'collected'  => $totalCollected,
                    'last_sync'  => $user->last_active_at?->diffForHumans() ?? 'Unknown',
                ];
            });
    }

    public function render()
    {
        return view('livewire.admin.collections-monitor-component')
            ->layout('components.layout.admin-shell', [
                'title'     => 'Collections Monitor',
                'activeNav' => 'collections',
            ]);
    }
}
