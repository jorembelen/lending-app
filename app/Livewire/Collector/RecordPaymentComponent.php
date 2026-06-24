<?php

namespace App\Livewire\Collector;

use App\Models\Borrower;
use App\Models\Loan;
use App\Services\PaymentRecorder;
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

    /**
     * Online confirm path. Note: the production UX captures payments
     * queue-first in the browser (IndexedDB) and syncs them through the
     * collector payments API so the offline and online paths share one code
     * path (see PaymentRecorder). This server-side handler remains as a
     * progressive-enhancement fallback for when JavaScript is unavailable,
     * and delegates to the same recorder so behaviour is identical.
     */
    public function confirm(PaymentRecorder $recorder): void
    {
        $this->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $loan = $this->loan;

        if (! $loan) {
            $this->addError('amount', 'No active loan found for this borrower. Please refresh and try again.');
            return;
        }

        $result = $recorder->record(
            loan: $loan,
            amount: (float) $this->amount,
            idempotencyKey: Str::uuid()->toString(),
            collectorUserId: auth()->id(),
        );

        $this->redirect(route('collector.payment.confirmed', $result['payment']->id), navigate: true);
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
