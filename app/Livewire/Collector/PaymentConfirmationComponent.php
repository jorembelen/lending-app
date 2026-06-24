<?php

namespace App\Livewire\Collector;

use App\Models\Payment;
use Livewire\Component;

class PaymentConfirmationComponent extends Component
{
    public int|string $paymentId;

    public function mount(int|string $paymentId): void
    {
        $this->paymentId = $paymentId;
    }

    public function getPaymentProperty(): ?Payment
    {
        return Payment::with(['loan.borrower'])->find($this->paymentId);
    }

    public function render()
    {
        return view('livewire.collector.payment-confirmation-component')
            ->layout('components.layout.standalone-page', [
                'title' => 'Payment Confirmed — Voltage',
                'pwa'   => true,
            ]);
    }
}
