<div class="pt-2 pb-32">

    <!-- Borrower Context Card -->
    <section class="mt-stack-md">
        <div class="bg-surface-container rounded-xl p-4 border border-white/10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 bg-surface-variant flex items-center justify-center">
                @if($this->borrower?->avatar)
                    <img src="{{ $this->borrower->avatar }}" alt="{{ $this->borrower->name }}" class="w-full h-full object-cover" />
                @else
                    <span class="material-symbols-outlined text-on-surface-variant">person</span>
                @endif
            </div>
            <div class="flex-grow">
                <h2 class="font-label-md text-on-surface font-bold">{{ $this->borrower?->name ?? 'Unknown' }}</h2>
                <p class="font-label-sm text-on-surface-variant">ID: {{ $this->loan?->loan_id ?? '—' }}</p>
            </div>
            <div class="text-right">
                @if($this->loan)
                    <x-ui.badge :variant="$this->loan->status">{{ ucfirst($this->loan->status) }}</x-ui.badge>
                @endif
            </div>
        </div>
    </section>

    <!-- Amount Input -->
    <section class="mt-stack-lg text-center">
        <label for="payment-amount" class="block font-label-sm text-on-surface-variant uppercase tracking-widest mb-2">
            Current Due Amount
        </label>
        <div class="relative flex flex-col items-center">
            <div class="flex items-center justify-center gap-2">
                <span class="font-display-lg text-display-lg text-primary-fixed">₱</span>
                <input
                    id="payment-amount"
                    type="number"
                    inputmode="decimal"
                    wire:model.live="amount"
                    step="0.01"
                    min="0"
                    class="bg-transparent border-none text-center font-display-lg text-display-lg text-primary-fixed w-full max-w-[280px] focus:ring-0 outline-none p-0"
                />
            </div>
            <div class="h-[2px] w-40 bg-primary-fixed mt-2 opacity-50"></div>
        </div>

        @error('amount')
            <p class="font-label-sm text-label-sm text-error mt-2">{{ $message }}</p>
        @enderror

        <!-- Quick Select -->
        <div class="flex justify-between gap-3 mt-stack-md">
            <button
                wire:click="setExact"
                class="flex-1 h-touch-target-min bg-surface-container rounded-xl font-label-md text-on-surface border border-white/5 active:bg-surface-variant transition-colors">
                Exact
            </button>
            <button
                wire:click="setPartial"
                class="flex-1 h-touch-target-min bg-surface-container rounded-xl font-label-md text-on-surface border border-white/5 active:bg-surface-variant transition-colors">
                Partial (50%)
            </button>
            <button
                wire:click="clearAmount"
                class="flex-1 h-touch-target-min bg-surface-container rounded-xl font-label-md text-on-surface border border-white/5 active:bg-surface-variant transition-colors">
                Custom
            </button>
        </div>
    </section>

    <!-- Summary -->
    <section class="mt-stack-lg bg-surface-container p-5 rounded-xl border border-white/10">
        <div class="flex justify-between items-center mb-4">
            <span class="font-body-md text-on-surface-variant">Total Remaining</span>
            <span class="font-headline-md text-on-surface">₱{{ number_format($this->loan?->remaining_balance ?? 0, 2) }}</span>
        </div>
        <div class="h-[1px] bg-white/5 mb-4"></div>
        <div class="flex justify-between items-center">
            <span class="font-body-md text-on-surface-variant">New Balance</span>
            <span class="font-headline-md text-primary-fixed font-bold">₱{{ number_format($this->newBalance, 2) }}</span>
        </div>
    </section>

    <!-- Notes -->
    <section class="mt-stack-md">
        <label for="payment-notes" class="block font-label-md text-on-surface-variant mb-2 ml-1">Payment Notes (Optional)</label>
        <textarea
            id="payment-notes"
            wire:model="notes"
            placeholder="Add details about the collection..."
            class="w-full bg-surface-container rounded-xl border border-white/10 p-4 font-body-md text-on-surface focus:outline-none focus:border-primary-fixed transition-all resize-none h-32 placeholder:text-on-surface-variant/40"
        ></textarea>
    </section>

    <!-- Fixed Confirm Button -->
    <div class="fixed bottom-[80px] left-0 w-full bg-background/80 backdrop-blur-md px-margin-mobile py-4 z-40">
        <x-ui.button
            variant="primary"
            size="lg"
            icon="check_circle"
            wire:click="confirm"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="confirm">CONFIRM PAYMENT</span>
            <span wire:loading wire:target="confirm">Processing...</span>
        </x-ui.button>
    </div>
</div>
