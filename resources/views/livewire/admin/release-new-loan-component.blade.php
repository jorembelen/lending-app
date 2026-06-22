<div class="max-w-5xl">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-6 text-label-sm font-label-sm">
        <span class="text-secondary-fixed-dim uppercase tracking-wider">Loans</span>
        <span class="text-outline-variant">/</span>
        <span class="text-primary-fixed font-bold uppercase tracking-wider">Release New Loan</span>
    </nav>

    @if(session('success'))
        <div class="mb-6 bg-primary-fixed/10 border border-primary-fixed/30 text-primary-fixed rounded-xl px-5 py-3 font-label-md text-label-md">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Left: Form -->
        <div class="flex-[1.5] space-y-6">
            <x-ui.card>
                <h2 class="font-headline-md text-headline-md text-primary mb-6">Loan Disbursement Details</h2>

                <div class="space-y-6">

                    <!-- Borrower Search -->
                    <div class="space-y-2" x-data="{ open: false }">
                        <label for="borrower-search" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">Search Borrower</label>
                        <div class="relative">
                            <div class="flex items-center bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 focus-within:border-primary-fixed transition-all">
                                <span class="material-symbols-outlined text-primary-fixed mr-3">person_search</span>
                                <input
                                    id="borrower-search"
                                    type="text"
                                    wire:model.live.debounce.300ms="borrowerSearch"
                                    @focus="open = true"
                                    @click.outside="open = false"
                                    placeholder="Search by name or ID..."
                                    class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-secondary-fixed-dim/50 outline-none"
                                />
                                @if($selectedBorrower)
                                    <span class="material-symbols-outlined text-primary-fixed" style="font-variation-settings: 'FILL' 1;">verified</span>
                                @endif
                            </div>
                            @if($selectedBorrower)
                                <p class="absolute -bottom-5 left-0 text-[10px] text-primary-fixed font-bold tracking-widest uppercase">
                                    TRUSTED BORROWER • {{ $selectedBorrower->loans()->where('status','completed')->count() }} COMPLETED LOANS
                                </p>
                            @endif

                            <!-- Dropdown results -->
                            @if($borrowerResults->count() && $borrowerSearch)
                            <div class="absolute top-full left-0 w-full mt-1 bg-surface-container-high border border-outline-variant rounded-xl overflow-hidden z-10">
                                @foreach($borrowerResults as $result)
                                <button
                                    type="button"
                                    wire:click="selectBorrower({{ $result->id }})"
                                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-surface-container-highest text-left transition-colors"
                                >
                                    <span class="material-symbols-outlined text-secondary-fixed-dim text-[18px]">person</span>
                                    <div>
                                        <p class="font-label-md text-label-md text-on-surface">{{ $result->name }}</p>
                                        <p class="font-label-sm text-label-sm text-secondary-fixed-dim">{{ $result->borrower_id }}</p>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @error('borrowerId') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                        <!-- Principal Amount -->
                        <div class="space-y-2">
                            <label for="principal" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">Principal Amount</label>
                            <div class="flex items-center bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 focus-within:border-primary-fixed transition-all">
                                <span class="text-secondary-fixed-dim mr-2 font-bold">₱</span>
                                <input
                                    id="principal"
                                    type="number"
                                    inputmode="numeric"
                                    wire:model.blur="principal"
                                    placeholder="0.00"
                                    class="bg-transparent border-none focus:ring-0 w-full text-on-surface text-headline-md font-headline-md outline-none"
                                />
                            </div>
                            @error('principal') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Interest Rate -->
                        <div class="space-y-2">
                            <label for="interest-rate" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">Interest Rate (%)</label>
                            <div class="flex items-center bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 focus-within:border-primary-fixed transition-all">
                                <input
                                    id="interest-rate"
                                    type="number"
                                    inputmode="decimal"
                                    wire:model.blur="interestRate"
                                    placeholder="0.00"
                                    class="bg-transparent border-none focus:ring-0 w-full text-on-surface text-headline-md font-headline-md outline-none"
                                />
                                <span class="text-secondary-fixed-dim ml-2 font-bold">%</span>
                            </div>
                            @error('interestRate') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Term -->
                        <div class="space-y-2">
                            <label for="term-days" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">Loan Term (Days)</label>
                            <div class="flex items-center bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 focus-within:border-primary-fixed transition-all">
                                <input
                                    id="term-days"
                                    type="number"
                                    inputmode="numeric"
                                    wire:model.blur="termDays"
                                    placeholder="30"
                                    class="bg-transparent border-none focus:ring-0 w-full text-on-surface outline-none"
                                />
                                <span class="text-secondary-fixed-dim ml-2 text-label-sm font-label-sm">days</span>
                            </div>
                            @error('termDays') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Payment Frequency -->
                        <div class="space-y-2">
                            <label for="payment-frequency" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">Payment Frequency</label>
                            <select
                                id="payment-frequency"
                                wire:model="paymentFrequency"
                                class="w-full bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-primary-fixed transition-all"
                            >
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <!-- Release Date -->
                        <div class="space-y-2 sm:col-span-2">
                            <label for="release-date" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">Release Date</label>
                            <input
                                id="release-date"
                                type="date"
                                wire:model="releaseDate"
                                class="w-full bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-primary-fixed transition-all"
                            />
                            @error('releaseDate') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Purpose -->
                        <div class="space-y-2 sm:col-span-2">
                            <label for="purpose" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">Loan Purpose (Optional)</label>
                            <input
                                id="purpose"
                                type="text"
                                wire:model="purpose"
                                placeholder="Business, personal, etc."
                                class="w-full bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 text-on-surface placeholder:text-secondary-fixed-dim/50 focus:outline-none focus:border-primary-fixed transition-all"
                            />
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Right: Summary -->
        <div class="lg:w-80 space-y-4">
            <x-ui.card>
                <h3 class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider mb-4">Loan Summary</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                        <span class="font-label-md text-label-md text-secondary-fixed-dim">Principal</span>
                        <span class="font-label-md text-label-md text-on-surface">₱{{ number_format((float)$principal, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                        <span class="font-label-md text-label-md text-secondary-fixed-dim">Interest</span>
                        <span class="font-label-md text-label-md text-on-surface">{{ $interestRate ?: '0' }}%</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                        <span class="font-label-md text-label-md text-secondary-fixed-dim">Term</span>
                        <span class="font-label-md text-label-md text-on-surface">{{ $termDays ?: '0' }} days</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                        <span class="font-label-md text-label-md text-secondary-fixed-dim">Daily Payment</span>
                        <span class="font-headline-md text-headline-md text-primary-fixed">₱{{ number_format($this->dailyPayment, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 bg-primary-fixed/5 rounded-lg px-3 -mx-1">
                        <span class="font-label-md text-label-md text-primary-fixed font-bold">Total Payable</span>
                        <span class="font-headline-md text-headline-md text-primary-fixed">₱{{ number_format($this->totalPayable, 2) }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.button
                variant="primary"
                size="lg"
                icon="check_circle"
                wire:click="save"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="save">Release Loan</span>
                <span wire:loading wire:target="save">Processing...</span>
            </x-ui.button>
        </div>
    </div>
</div>
