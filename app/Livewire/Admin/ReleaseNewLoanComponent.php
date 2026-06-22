<?php

namespace App\Livewire\Admin;

use App\Models\Borrower;
use App\Models\Loan;
use Livewire\Component;

class ReleaseNewLoanComponent extends Component
{
    public ?int      $borrowerId        = null;
    public string    $borrowerSearch    = '';
    public ?Borrower $selectedBorrower  = null;

    public string    $principal         = '';
    public string    $interestRate      = '';
    public string    $termDays          = '';
    public string    $paymentFrequency  = 'daily';
    public string    $releaseDate       = '';
    public string    $purpose           = '';
    public string    $notes             = '';

    protected array $rules = [
        'borrowerId'       => 'required|exists:borrowers,id',
        'principal'        => 'required|numeric|min:1',
        'interestRate'     => 'required|numeric|min:0|max:100',
        'termDays'         => 'required|integer|min:1',
        'paymentFrequency' => 'required|in:daily,weekly,monthly',
        'releaseDate'      => 'required|date',
    ];

    public function mount(?int $borrower = null): void
    {
        $this->releaseDate = today()->toDateString();

        if ($borrower) {
            $this->selectBorrower(Borrower::find($borrower));
        }
    }

    public function selectBorrower(?Borrower $borrower): void
    {
        if (! $borrower) { return; }
        $this->selectedBorrower = $borrower;
        $this->borrowerId       = $borrower->id;
        $this->borrowerSearch   = $borrower->name;
    }

    public function getDailyPaymentProperty(): float
    {
        if (! $this->principal || ! $this->interestRate || ! $this->termDays) {
            return 0.0;
        }
        $total = (float) $this->principal * (1 + (float) $this->interestRate / 100);
        return round($total / (float) $this->termDays, 2);
    }

    public function getTotalPayableProperty(): float
    {
        if (! $this->principal || ! $this->interestRate) {
            return 0.0;
        }
        return round((float) $this->principal * (1 + (float) $this->interestRate / 100), 2);
    }

    public function save(): void
    {
        $this->validate();

        Loan::create([
            'borrower_id'       => $this->borrowerId,
            'principal'         => $this->principal,
            'interest_rate'     => $this->interestRate,
            'term_days'         => $this->termDays,
            'payment_frequency' => $this->paymentFrequency,
            'release_date'      => $this->releaseDate,
            'purpose'           => $this->purpose,
            'notes'             => $this->notes,
            'status'            => 'active',
            'amount_paid'       => 0,
        ]);

        session()->flash('success', 'Loan released successfully.');
        $this->redirect(route('admin.borrowers'), navigate: true);
    }

    public function render()
    {
        $borrowerResults = $this->borrowerSearch
            ? Borrower::where('name', 'like', "%{$this->borrowerSearch}%")
                      ->orWhere('borrower_id', 'like', "%{$this->borrowerSearch}%")
                      ->limit(5)->get()
            : collect();

        return view('livewire.admin.release-new-loan-component', compact('borrowerResults'))
            ->layout('components.layout.admin-shell', [
                'title'     => 'Release New Loan',
                'activeNav' => 'loans',
            ]);
    }
}
