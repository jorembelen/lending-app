<?php

namespace App\Livewire\Admin;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\RatePreset;
use Livewire\Component;

class ReleaseNewLoanComponent extends Component
{
    public ?int      $borrowerId       = null;
    public string    $borrowerSearch   = '';
    public ?Borrower $selectedBorrower = null;

    public string    $ratePresetId     = '';
    public string    $principal        = '';
    public string    $interestRate     = '';   // rate per ₱1000
    public string    $termDays         = '';
    public string    $releaseDate      = '';

    protected array $rules = [
        'borrowerId'   => 'required|exists:borrowers,id',
        'ratePresetId' => 'required|exists:rate_presets,id',
        'principal'    => 'required|numeric|min:1',
        'interestRate' => 'required|numeric|min:0',
        'termDays'     => 'required|integer|min:1',
        'releaseDate'  => 'required|date',
    ];

    protected array $messages = [
        'borrowerId.required'   => 'Please select a borrower.',
        'ratePresetId.required' => 'Please select a rate preset.',
        'principal.required'    => 'Principal amount is required.',
        'interestRate.required' => 'Rate per ₱1000 is required.',
        'termDays.required'     => 'Loan term is required.',
        'releaseDate.required'  => 'Release date is required.',
    ];

    public function mount(?int $borrower = null): void
    {
        $this->releaseDate = today()->toDateString();

        $default = RatePreset::where('is_default', true)->where('is_active', true)->first()
                ?? RatePreset::where('is_active', true)->first();

        if ($default) {
            $this->selectPreset((string) $default->id);
        }

        if ($borrower) {
            $this->selectBorrower(Borrower::find($borrower));
        }
    }

    public function selectBorrower(?Borrower $borrower): void
    {
        if (! $borrower) { return; }
        $this->selectedBorrower = $borrower;
        $this->borrowerId       = $borrower->id;
        $this->borrowerSearch   = $borrower->full_name ?? '';
    }

    public function selectPreset(string $id): void
    {
        $preset = RatePreset::find($id);
        if (! $preset) { return; }
        $this->ratePresetId = (string) $preset->id;
        $this->interestRate = (string) $preset->rate_per_1000;
        $this->termDays     = (string) $preset->term_days;
    }

    public function getDailyPaymentProperty(): float
    {
        if (! $this->principal || ! $this->interestRate) {
            return 0.0;
        }
        // daily installment = (principal / 1000) × rate_per_1000
        return round((float) $this->principal / 1000 * (float) $this->interestRate, 2);
    }

    public function getTotalPayableProperty(): float
    {
        if (! $this->termDays) {
            return 0.0;
        }
        return round($this->dailyPayment * (float) $this->termDays, 2);
    }

    public function save(): void
    {
        $this->validate();

        Loan::create([
            'borrower_id'          => $this->borrowerId,
            'rate_preset_id'       => $this->ratePresetId,
            'principal'            => $this->principal,
            'rate_per_1000_locked' => $this->interestRate,
            'term_days_locked'     => $this->termDays,
            'daily_installment'    => $this->dailyPayment,
            'total_payable'        => $this->totalPayable,
            'disbursed_at'         => $this->releaseDate,
            'disbursed_by_user_id' => auth()->id(),
            'status'               => 'active',
        ]);

        session()->flash('success', 'Loan released successfully.');
        $this->redirect(route('admin.borrowers'), navigate: true);
    }

    public function getRatePresetsProperty()
    {
        return RatePreset::where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        $borrowerResults = strlen($this->borrowerSearch) >= 2
            ? Borrower::where('full_name', 'like', "%{$this->borrowerSearch}%")
                      ->orWhere('borrower_code', 'like', "%{$this->borrowerSearch}%")
                      ->limit(8)->get()
            : collect();

        return view('livewire.admin.release-new-loan-component', [
            'borrowerResults' => $borrowerResults,
            'ratePresets'     => $this->ratePresets,
        ])->layout('components.layout.admin-shell', [
            'title'     => 'Release New Loan',
            'activeNav' => 'loans',
        ]);
    }
}
