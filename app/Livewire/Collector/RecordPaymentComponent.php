<?php

namespace App\Livewire\Collector;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Str;
use Livewire\Component;

class RecordPaymentComponent extends Component
{
    public int|string $borrowerId;
    public float      $amount      = 0;
    public float      $exactAmount = 0;
    public string     $notes       = '';

    public function mount(int|string $borrowerId): void
    {
        $this->borrowerId = $borrowerId;
        $loan = $this->loan;
        if ($loan) {
            $this->exactAmount = (float) $loan->daily_installment;
            $this->amount      = $this->exactAmount;
        }
    }

    public function getBorrowerProperty(): ?Borrower
    {
        return Borrower::find($this->borrowerId);
    }

    public function getLoanProperty(): ?Loan
    {
        return Loan::where('borrower_id', $this->borrowerId)
            ->whereIn('status', ['active', 'overdue'])
            ->orderByDesc('created_at')
            ->first();
    }

    public function setExact(): void
    {
        $this->amount = $this->exactAmount;
    }

    public function setPartial(): void
    {
        $this->amount = round($this->exactAmount * 0.5, 2);
    }

    public function clearAmount(): void
    {
        $this->amount = 0;
    }

    public function getNewBalanceProperty(): float
    {
        if (! $this->loan) { return 0.0; }
        return max(0, (float) $this->loan->remaining_balance - $this->amount);
    }

    public function confirm(): void
    {
        $this->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $payment = Payment::create([
            'loan_id'           => $this->loan->id,
            'collector_user_id' => auth()->id(),
            'amount'            => $this->amount,
            'collected_at'      => now(),
            'recorded_at'       => now(),
            'idempotency_key'   => Str::uuid()->toString(),
            'is_voided'         => false,
        ]);

        $this->redirect(route('collector.payment.confirmed', $payment->id), navigate: true);
    }

    public function render()
    {
        return view('livewire.collector.record-payment-component')
            ->layout('components.layout.collector-shell', [
                'title'    => 'Record Payment',
                'activeTab'=> 'route',
                'showBack' => true,
                'backHref' => route('collector.borrower', $this->borrowerId),
            ]);
    }
}
