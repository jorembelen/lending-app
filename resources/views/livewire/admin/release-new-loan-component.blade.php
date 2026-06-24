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
                        <label for="borrower-search" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">
                            Search Borrower
                        </label>
                        <div class="relative">
                            <div class="flex items-center bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 focus-within:border-primary-fixed transition-all">
                                <span class="material-symbols-outlined text-primary-fixed mr-3 flex-shrink-0">person_search</span>
                                <input
                                    id="borrower-search"
                                    type="text"
                                    wire:model.live.debounce.300ms="borrowerSearch"
                                    @focus="open = true"
                                    @click.outside="open = false"
                                    placeholder="Type at least 2 characters…"
                                    autocomplete="off"
                                    class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/50 outline-none text-sm"
                                />
                                @if($selectedBorrower)
                                    <span class="material-symbols-outlined text-primary-fixed flex-shrink-0" style="font-variation-settings: 'FILL' 1;">verified</span>
                                @endif
                            </div>

                            @if($selectedBorrower)
                                <p class="mt-1 text-[11px] text-primary-fixed font-bold tracking-widest uppercase">
                                    {{ $selectedBorrower->full_name }} &bull; {{ $selectedBorrower->loans()->where('status','completed')->count() }} completed loan(s)
                                </p>
                            @endif

                            <!-- Dropdown results -->
                            @if($borrowerResults->count() && strlen($borrowerSearch) >= 2)
                            <div
                                x-show="open"
                                class="absolute top-full left-0 w-full mt-1 bg-surface-container-high border border-outline-variant rounded-xl overflow-hidden z-20 shadow-lg"
                            >
                                @foreach($borrowerResults as $result)
                                <button
                                    type="button"
                                    wire:click="selectBorrower({{ $result->id }})"
                                    @click="open = false"
                                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-surface-container-highest text-left transition-colors border-b border-outline-variant/20 last:border-0"
                                >
                                    <span class="w-8 h-8 rounded-full bg-primary-fixed/10 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-primary-fixed text-[16px]">person</span>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-on-surface text-sm truncate">{{ $result->full_name }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $result->borrower_code ?? '—' }}</p>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @error('borrowerId') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Rate Preset -->
                    <div class="space-y-2">
                        <label for="rate-preset" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">
                            Rate Preset
                        </label>
                        <select
                            id="rate-preset"
                            wire:change="selectPreset($event.target.value)"
                            class="w-full bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-primary-fixed transition-all text-sm"
                        >
                            <option value="">— Select a preset —</option>
                            @foreach($ratePresets as $preset)
                            <option value="{{ $preset->id }}" {{ $ratePresetId == $preset->id ? 'selected' : '' }}>
                                {{ $preset->name }} (₱{{ number_format($preset->rate_per_1000, 2) }}/₱1000 · {{ $preset->term_days }}d)
                            </option>
                            @endforeach
                        </select>
                        @error('ratePresetId') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Assigned Collector -->
                    <div class="space-y-2">
                        <label for="collector" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">
                            Assign Collector
                        </label>
                        <select
                            id="collector"
                            wire:model="collectorId"
                            class="w-full bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-primary-fixed transition-all text-sm"
                        >
                            <option value="">— Select a collector —</option>
                            @foreach($collectors as $collector)
                            <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                            @endforeach
                        </select>
                        @error('collectorId') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                        <!-- Principal Amount -->
                        <div class="space-y-2">
                            <label for="principal" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">
                                Principal Amount
                            </label>
                            <div class="flex items-center bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 focus-within:border-primary-fixed transition-all">
                                <span class="text-on-surface-variant mr-2 font-bold flex-shrink-0">₱</span>
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

                        <!-- Rate per ₱1000 -->
                        <div class="space-y-2">
                            <label for="interest-rate" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">
                                Rate per ₱1,000
                            </label>
                            <div class="flex items-center bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 focus-within:border-primary-fixed transition-all">
                                <span class="text-on-surface-variant mr-2 font-bold flex-shrink-0">₱</span>
                                <input
                                    id="interest-rate"
                                    type="number"
                                    inputmode="decimal"
                                    wire:model.blur="interestRate"
                                    placeholder="0.00"
                                    class="bg-transparent border-none focus:ring-0 w-full text-on-surface text-headline-md font-headline-md outline-none"
                                />
                                <span class="text-on-surface-variant ml-2 text-label-sm font-label-sm flex-shrink-0">/₱1k</span>
                            </div>
                            @error('interestRate') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Term (Days) -->
                        <div class="space-y-2">
                            <label for="term-days" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">
                                Loan Term
                            </label>
                            <div class="flex items-center bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 focus-within:border-primary-fixed transition-all">
                                <input
                                    id="term-days"
                                    type="number"
                                    inputmode="numeric"
                                    wire:model.blur="termDays"
                                    placeholder="60"
                                    class="bg-transparent border-none focus:ring-0 w-full text-on-surface outline-none"
                                />
                                <span class="text-on-surface-variant ml-2 text-label-sm font-label-sm flex-shrink-0">days</span>
                            </div>
                            @error('termDays') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Release Date -->
                        <div class="space-y-2">
                            <label for="release-date" class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider block">
                                Release Date
                            </label>
                            <input
                                id="release-date"
                                type="date"
                                wire:model="releaseDate"
                                class="w-full bg-surface-dim border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-primary-fixed transition-all"
                            />
                            @error('releaseDate') <p class="font-label-sm text-label-sm text-error mt-1">{{ $message }}</p> @enderror
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
                        <span class="font-label-md text-label-md text-secondary-fixed-dim">Borrower</span>
                        <span class="font-label-md text-label-md text-on-surface text-right max-w-[140px] truncate">
                            {{ $selectedBorrower?->full_name ?? '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                        <span class="font-label-md text-label-md text-secondary-fixed-dim">Collector</span>
                        <span class="font-label-md text-label-md text-on-surface text-right max-w-[140px] truncate">
                            {{ $collectors->firstWhere('id', (int) $collectorId)?->name ?? '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                        <span class="font-label-md text-label-md text-secondary-fixed-dim">Principal</span>
                        <span class="font-label-md text-label-md text-on-surface">₱{{ number_format((float)$principal, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                        <span class="font-label-md text-label-md text-secondary-fixed-dim">Rate</span>
                        <span class="font-label-md text-label-md text-on-surface">₱{{ $interestRate ?: '0' }} / ₱1k</span>
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
                <span wire:loading wire:target="save">Processing…</span>
            </x-ui.button>
        </div>
    </div>
</div>
