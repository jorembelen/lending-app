<?php

namespace App\Livewire\Collector;

use App\Models\Borrower;
use Livewire\Component;

class QrScannerComponent extends Component
{
    public ?int    $prefillBorrowerId = null;
    public ?string $scannedValue      = null;
    public ?string $errorMessage      = null;

    public function mount(?int $borrower = null): void
    {
        $this->prefillBorrowerId = $borrower;
    }

    public function handleScan(string $value): void
    {
        $this->scannedValue  = $value;
        $this->errorMessage  = null;

        $borrower = Borrower::where('qr_reference', $value)
            ->orWhere('borrower_code', $value)
            ->first();

        if (! $borrower) {
            $this->errorMessage = 'No borrower found for this QR code. Try searching manually.';
            return;
        }

        $this->redirect(route('collector.payment', $borrower->id), navigate: true);
    }

    public function render()
    {
        return view('livewire.collector.qr-scanner-component')
            ->layout('components.layout.standalone-page', [
                'title'     => 'Scan QR — Voltage',
                'withQrLib' => true,
                'pwa'       => true,
            ]);
    }
}
