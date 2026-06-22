<?php

namespace App\Livewire\Admin;

use App\Models\RebateRequest;
use Livewire\Component;

class PendingRebatesComponent extends Component
{
    public function getPendingProperty()
    {
        return RebateRequest::with('borrower')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();
    }

    public function approve(int $id): void
    {
        RebateRequest::findOrFail($id)->update(['status' => 'approved', 'approved_by' => auth()->id()]);
    }

    public function reject(int $id): void
    {
        RebateRequest::findOrFail($id)->update(['status' => 'rejected', 'approved_by' => auth()->id()]);
    }

    public function render()
    {
        return view('livewire.admin.pending-rebates-component')
            ->layout('components.layout.admin-shell', [
                'title'     => 'Pending Rebate Approvals',
                'activeNav' => 'loyalty',
            ]);
    }
}
