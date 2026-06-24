<div class="pt-2">

    @php
        $b    = $this->borrower;
        $loan = $this->loan;
    @endphp

    @if(! $b || ! $loan)
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined text-on-surface-variant/30 mb-3" style="font-size:56px;">receipt_long</span>
            <p class="font-headline-md text-headline-md text-on-surface-variant/60">No active loan found</p>
            <p class="font-label-sm text-label-sm text-on-surface-variant/40 mt-1">This borrower has no active or overdue loan.</p>
        </div>
    @else

    <!-- ── Borrower Card ──────────────────────────────────────────── -->
    <section class="mt-stack-md">
        <div class="bg-surface-container-low rounded-2xl px-5 py-4 border border-white/10 flex items-center gap-4">

            <!-- Avatar -->
            <div class="w-14 h-14 rounded-full overflow-hidden flex-shrink-0 bg-surface-variant border border-white/10 flex items-center justify-center">
                @if($b->photo_path)
                    <img src="{{ asset('storage/' . $b->photo_path) }}"
                         alt="{{ $b->full_name }}"
                         class="w-full h-full object-cover" />
                @else
                    <span class="text-xl font-bold text-on-surface-variant select-none">
                        {{ strtoupper(substr($b->full_name, 0, 1)) }}
                    </span>
                @endif
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-on-surface text-[16px] leading-tight truncate">{{ $b->full_name }}</h2>
                <p class="font-mono text-xs text-on-surface-variant tracking-widest mt-0.5">
                    ID: {{ $b->borrower_code ?? ('BRW-' . str_pad($b->id, 6, '0', STR_PAD_LEFT)) }}
                </p>
            </div>

            <!-- Status Badge -->
            <div class="flex-shrink-0">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wider
                    {{ $loan->status === 'overdue' ? 'bg-error/20 text-error border border-error/30' : 'bg-primary-fixed/10 text-primary-fixed border border-primary-fixed/20' }}">
                    {{ strtoupper($loan->status) }}
                </span>
            </div>
        </div>
    </section>

    <!-- ── Amount Display ─────────────────────────────────────────── -->
    <section class="mt-stack-xl text-center px-4">
        <label for="payment-amount" class="block font-label-sm text-label-sm text-on-surface-variant uppercase tracking-[0.2em] mb-4">
            Current Due Amount
        </label>

        <div class="flex items-start justify-center gap-2">
            <span class="text-[28px] font-bold text-primary-fixed mt-2 leading-none">₱</span>
            <input
                id="payment-amount"
                type="number"
                inputmode="decimal"
                wire:model.live.debounce.300ms="amount"
                step="0.01"
                min="0"
                class="bg-transparent border-none text-center text-[56px] font-extrabold text-primary-fixed leading-none w-full max-w-[260px] focus:ring-0 outline-none p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                placeholder="0.00"
            />
        </div>

        <!-- Underline accent -->
        <div class="mx-auto mt-3 h-[3px] w-36 rounded-full bg-primary-fixed/60"></div>

        @error('amount')
            <p class="font-label-sm text-label-sm text-error mt-3">{{ $message }}</p>
        @enderror

        <!-- Quick-select chips -->
        <div class="flex gap-3 mt-stack-md">
            <button
                wire:click="setExact"
                class="flex-1 min-h-[52px] px-3 py-3 bg-surface-container-high rounded-xl font-semibold text-[13px] text-white border border-white/20 active:bg-surface-container-highest active:scale-95 transition-all">
                Exact
            </button>
            <button
                wire:click="setPartial"
                class="flex-1 min-h-[52px] px-3 py-3 bg-surface-container-high rounded-xl font-semibold text-[13px] text-white border border-white/20 active:bg-surface-container-highest active:scale-95 transition-all">
                Partial (50%)
            </button>
            <button
                wire:click="clearAmount"
                class="flex-1 min-h-[52px] px-3 py-3 bg-surface-container-high rounded-xl font-semibold text-[13px] text-white border border-white/20 active:bg-surface-container-highest active:scale-95 transition-all">
                Custom
            </button>
        </div>
    </section>

    <!-- ── Summary Card ───────────────────────────────────────────── -->
    <section class="mt-stack-lg bg-surface-container-low rounded-2xl border border-white/10 overflow-hidden">
        <div class="flex justify-between items-center px-5 py-4">
            <span class="font-label-md text-label-md text-on-surface-variant">Total Remaining</span>
            <span class="font-bold text-on-surface text-[15px]">
                ₱{{ number_format((float) $loan->remaining_balance, 2) }}
            </span>
        </div>
        <div class="h-px bg-white/5 mx-5"></div>
        <div class="flex justify-between items-center px-5 py-4">
            <span class="font-label-md text-label-md text-on-surface-variant">Daily Installment</span>
            <span class="font-bold text-on-surface-variant text-[15px]">
                ₱{{ number_format((float) $loan->daily_installment, 2) }}
            </span>
        </div>
        <div class="h-px bg-white/5 mx-5"></div>
        <div class="flex justify-between items-center px-5 py-4">
            <span class="font-bold text-on-surface text-[15px]">New Balance</span>
            <span class="font-extrabold text-primary-fixed text-[18px]">
                ₱{{ number_format($this->newBalance, 2) }}
            </span>
        </div>
    </section>

    <!-- ── Notes ─────────────────────────────────────────────────── -->
    <section class="mt-stack-md">
        <label for="payment-notes" class="block font-label-md text-label-md text-on-surface-variant mb-2 ml-1">
            Payment Notes <span class="opacity-50">(Optional)</span>
        </label>
        <textarea
            id="payment-notes"
            wire:model="notes"
            rows="3"
            placeholder="Add details about the collection…"
            class="w-full bg-surface-container-low rounded-2xl border border-white/10 px-5 py-4 font-body-md text-on-surface placeholder:text-on-surface-variant/30 focus:outline-none focus:border-primary-fixed/50 transition-colors resize-none"
        ></textarea>
    </section>

    <!-- ── Confirm Button (queue-first capture, online or offline) ──── -->
    <section
        class="mt-stack-lg mb-4"
        x-data="{
            submitting: false,
            result: null,        // null | 'synced' | 'queued' | 'needs_attention' | 'invalid'
            message: '',
            async submit() {
                const input = document.getElementById('payment-amount');
                const val = parseFloat((input && input.value) || '0');
                if (!(val > 0)) {
                    this.result = 'invalid';
                    this.message = 'Enter an amount greater than zero.';
                    return;
                }
                if (!window.collectorApp) {
                    // JS bundle not ready — fall back to the server round-trip.
                    @this.call('confirm');
                    return;
                }
                this.submitting = true;
                try {
                    const rec = await window.collectorApp.queuePayment({ loanId: {{ $loan->id }}, amount: val });
                    let state = 'queued';
                    if (navigator.onLine) {
                        await window.collectorApp.flush();
                        const updated = await window.collectorApp.db.getPayment(rec.idempotency_key);
                        state = updated ? updated.state : 'queued';
                    }
                    this.result = state;
                } catch (e) {
                    // Already durably queued; surface it as queued.
                    this.result = 'queued';
                } finally {
                    this.submitting = false;
                }
            }
        }"
    >
        <!-- Confirm button (hidden once we have a terminal result) -->
        <button
            x-show="result === null || result === 'invalid'"
            @click="submit()"
            :disabled="submitting"
            class="w-full h-[56px] bg-primary-fixed text-on-primary-fixed rounded-2xl flex items-center justify-center gap-3 font-extrabold text-[15px] uppercase tracking-widest active:brightness-90 active:scale-[0.98] transition-all disabled:opacity-60 shadow-lg shadow-primary-fixed/20"
        >
            <span x-show="!submitting" class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                Confirm Payment
            </span>
            <span x-show="submitting" class="flex items-center gap-2">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Processing…
            </span>
        </button>

        <p x-show="result === 'invalid'" x-cloak class="font-label-sm text-label-sm text-error mt-3 text-center" x-text="message"></p>

        <!-- Online success: standard confirmation -->
        <div x-show="result === 'synced'" x-cloak class="bg-primary-fixed/10 border border-primary-fixed/30 rounded-2xl px-5 py-6 text-center">
            <span class="material-symbols-outlined text-primary-fixed text-[44px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <p class="font-bold text-on-surface text-[17px] mt-2">Payment recorded</p>
            <p class="text-on-surface-variant text-sm mt-1">The collection has been saved.</p>
            <a href="{{ route('collector.route') }}" wire:navigate
               class="mt-5 inline-flex items-center justify-center w-full h-[52px] bg-primary-fixed text-on-primary-fixed rounded-2xl font-bold uppercase tracking-widest text-[14px] active:brightness-90">
                Back to Route
            </a>
        </div>

        <!-- Offline / not-yet-synced: queued state, distinct from success -->
        <div x-show="result === 'queued'" x-cloak class="bg-[#3c4d00]/30 border border-primary-fixed/30 rounded-2xl px-5 py-6 text-center">
            <span class="material-symbols-outlined text-primary-fixed text-[44px]">cloud_queue</span>
            <p class="font-bold text-on-surface text-[17px] mt-2">Queued — will sync</p>
            <p class="text-on-surface-variant text-sm mt-1 leading-relaxed">
                Saved on this device. It will sync automatically once you're back on signal — no action needed.
            </p>
            <a href="{{ route('collector.route') }}" wire:navigate
               class="mt-5 inline-flex items-center justify-center w-full h-[52px] bg-surface-container-high text-white border border-white/20 rounded-2xl font-bold uppercase tracking-widest text-[14px] active:brightness-90">
                Continue
            </a>
        </div>

        <!-- Server rejected for a real reason -->
        <div x-show="result === 'needs_attention'" x-cloak class="bg-error/10 border border-error/30 rounded-2xl px-5 py-6 text-center">
            <span class="material-symbols-outlined text-error text-[44px]">error</span>
            <p class="font-bold text-error text-[17px] mt-2">Needs attention</p>
            <p class="text-on-surface-variant text-sm mt-1 leading-relaxed">
                The server couldn't accept this payment. Check the End of Day summary and contact the office.
            </p>
            <a href="{{ route('collector.summary') }}" wire:navigate
               class="mt-5 inline-flex items-center justify-center w-full h-[52px] bg-surface-container-high text-white border border-white/20 rounded-2xl font-bold uppercase tracking-widest text-[14px] active:brightness-90">
                View Summary
            </a>
        </div>
    </section>

    @endif
</div>
