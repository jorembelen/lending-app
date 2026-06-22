<?php

namespace App\Livewire\Admin;

use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MainDashboardComponent extends Component
{
    public string $chartPeriod = 'mtd'; // wtd | mtd | ytd

    public function setChartPeriod(string $period): void
    {
        $this->chartPeriod = $period;
    }

    public function getKpisProperty(): array
    {
        $todayCollected   = Payment::whereDate('collected_at', today())->sum('amount');
        $yesterday        = Payment::whereDate('collected_at', today()->subDay())->sum('amount');
        $vsYesterday      = $yesterday > 0 ? round((($todayCollected - $yesterday) / $yesterday) * 100, 1) : 0;
        $outstanding      = (float) DB::selectOne('
            SELECT COALESCE(SUM(l.total_payable), 0) - COALESCE((
                SELECT SUM(p.amount) FROM payments p
                WHERE p.loan_id IN (SELECT id FROM loans WHERE status = ?) AND p.is_voided = 0
            ), 0) AS remaining
            FROM loans l WHERE l.status = ?
        ', ['active', 'active'])->remaining;
        $activeLoans      = Loan::where('status', 'active')->count();
        $totalLoans       = Loan::count();
        $overdueLoans     = Loan::where('status', 'overdue')->count();
        $arrearsRate      = $totalLoans > 0 ? round(($overdueLoans / $totalLoans) * 100, 1) : 0;

        return compact('todayCollected', 'vsYesterday', 'outstanding', 'activeLoans', 'arrearsRate');
    }

    public function getCollectorPerformanceProperty()
    {
        return Payment::with('collector')
            ->whereDate('collected_at', today())
            ->selectRaw('collector_user_id, SUM(amount) as total, COUNT(*) as collections')
            ->groupBy('collector_user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.main-dashboard-component')
            ->layout('components.layout.admin-shell', [
                'title'     => 'Dashboard',
                'activeNav' => 'dashboard',
            ]);
    }
}