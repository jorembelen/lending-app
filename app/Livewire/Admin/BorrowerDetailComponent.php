<?php

namespace App\Livewire\Admin;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BorrowerDetailComponent extends Component
{
    public int|string $borrowerId;
    public ?int       $selectedLoanId = null;

    public function mount(int|string $borrowerId): void
    {
        $this->borrowerId = $borrowerId;
    }

    public function getBorrowerProperty(): ?Borrower
    {
        return Borrower::with('account')->find($this->borrowerId);
    }

    public function getLoansProperty()
    {
        return Loan::where('borrower_id', $this->borrowerId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getActiveLoanProperty(): ?Loan
    {
        return $this->loans->firstWhere('status', 'active')
            ?? $this->loans->first();
    }

    public function getCollectorsProperty()
    {
        return User::role('collector')->orderBy('name')->get();
    }

    public function assignCollector(int $loanId, ?int $collectorId): void
    {
        $loan = Loan::where('borrower_id', $this->borrowerId)->find($loanId);
        if (! $loan) { return; }

        $loan->update(['assigned_collector_id' => $collectorId ?: null]);

        session()->flash('success', 'Collector assignment updated.');
    }

    public function getSelectedLoanProperty(): ?Loan
    {
        if (! $this->selectedLoanId) { return null; }

        return Loan::with([
                'payments' => fn ($q) => $q->orderBy('collected_at'),
                'payments.collector',
                'disbursedBy',
                'assignedCollector',
            ])
            ->where('borrower_id', $this->borrowerId)
            ->find($this->selectedLoanId);
    }

    public function selectLoan(int $loanId): void
    {
        $this->selectedLoanId = $loanId;
    }

    public function closeStatement(): void
    {
        $this->selectedLoanId = null;
    }

    public function getQrCodeSvgProperty(): string
    {
        $borrower = $this->borrower;
        if (! $borrower) { return ''; }

        $value = $borrower->qr_reference ?? $borrower->borrower_code ?? (string) $borrower->id;

        return (string) QrCode::format('svg')
            ->size(160)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($value);
    }

    public function render()
    {
        return view('livewire.admin.borrower-detail-component')
            ->layout('components.layout.admin-shell', [
                'title'     => $this->borrower?->full_name ?? 'Borrower Detail',
                'activeNav' => 'borrowers',
            ]);
    }
}
