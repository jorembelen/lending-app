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

        $loan = $this->loan;

        if (! $loan) {
            $this->addError('amount', 'No active loan found for this borrower. Please refresh and try again.');
            return;
        }

        $payment = \Illuminate\Support\Facades\DB::transaction(function () use ($loan) {
            $payment = Payment::create([
                'loan_id'           => $loan->id,
                'collector_user_id' => auth()->id(),
                'amount'            => $this->amount,
                'collected_at'      => now(),
                'recorded_at'       => now(),
                'idempotency_key'   => Str::uuid()->toString(),
                'is_voided'         => false,
            ]);

            $this->allocateToSchedule($loan, (float) $this->amount);

            return $payment;
        });

        $this->redirect(route('collector.payment.confirmed', $payment->id), navigate: true);
    }

    /**
     * Apply a payment across the loan's unpaid schedule items in due order (FIFO),
     * updating each item's amount_paid and status so the route and summaries
     * always reflect real collections.
     */
    private function allocateToSchedule(Loan $loan, float $amount): void
    {
        $remaining = $amount;

        $items = $loan->scheduleItems()
            ->whereIn('status', ['pending', 'partially_paid', 'missed'])
            ->orderBy('due_date')
            ->orderBy('sequence_number')
            ->get();

        foreach ($items as $item) {
            if ($remaining <= 0) {
                break;
            }

            $owed = (float) $item->amount_due - (float) $item->amount_paid;
            if ($owed <= 0) {
                continue;
            }

            $applied  = min($remaining, $owed);
            $newPaid  = (float) $item->amount_paid + $applied;

            $item->update([
                'amount_paid' => $newPaid,
                'status'      => $newPaid >= (float) $item->amount_due ? 'paid' : 'partially_paid',
            ]);

            $remaining -= $applied;
        }
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
