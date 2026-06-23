<?php

namespace App\Livewire\Admin;

use App\Models\RebateGrant;
use Livewire\Component;

class PendingRebatesComponent extends Component
{
    public function getPendingProperty()
    {
        return RebateGrant::with('borrower')
            ->where('status', 'pending_approval')
            ->orderByDesc('created_at')
            ->get();
    }

    public function approve(int $id): void
    {
        RebateGrant::findOrFail($id)->update([
            'status'               => 'approved',
            'approved_by_user_id'  => auth()->id(),
        ]);
    }

    public function reject(int $id): void
    {
        RebateGrant::findOrFail($id)->update([
            'status'               => 'rejected',
            'approved_by_user_id'  => auth()->id(),
        ]);
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
