<?php

namespace App\Livewire\Borrower;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeComponent extends Component
{
    public function getQrCodeSvgProperty(): string
    {
        $borrower = Borrower::find(auth()->id());
        $value    = $borrower?->qr_reference
                 ?? $borrower?->borrower_code
                 ?? 'BRW-' . str_pad(auth()->id(), 6, '0', STR_PAD_LEFT);

        return (string) QrCode::format('svg')
            ->size(220)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($value);
    }

    public function getLoanProperty(): ?Loan
    {
        return Loan::where('borrower_id', auth()->id())
            ->whereIn('status', ['active', 'overdue'])
            ->orderByDesc('created_at')
            ->first();
    }

    public function getRecentPaymentsProperty()
    {
        if (! $this->loan) { return collect(); }
        return Payment::where('loan_id', $this->loan->id)
            ->orderByDesc('collected_at')
            ->limit(5)
            ->get();
    }

    public function getLoyaltyProperty(): array
    {
        $borrower = auth()->user();
        return [
            'tier'        => $borrower->loyalty_tier ?? 'Standard',
            'points'      => $borrower->loyalty_points ?? 0,
            'next_tier'   => 'Preferred',
            'next_points' => 1000,
            'streak'      => $borrower->payment_streak ?? 0,
        ];
    }

    public function render()
    {
        return view('livewire.borrower.home-component')
            ->layout('components.layout.borrower-shell', [
                'title'     => 'Home',
                'activeTab' => 'home',
            ]);
    }
}
