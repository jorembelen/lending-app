<div class="pt-2 pb-32">

    @if(!$this->borrower)
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <span class="material-symbols-outlined text-on-surface-variant/40 mb-3" style="font-size:48px;">person_off</span>
            <p class="font-headline-md text-headline-md text-on-surface-variant/60">Borrower not found</p>
        </div>
    @else

    @php
        $b          = $this->borrower;
        $amountPaid = $this->loan
            ? (float) $this->loan->payments()->where('is_voided', false)->sum('amount')
            : 0.0;
        $paidPct    = ($this->loan && (float) $this->loan->total_payable > 0)
            ? min(100, $amountPaid / (float) $this->loan->total_payable * 100)
            : 0;
    @endphp

    <!-- Profile Section -->
    <section class="flex flex-col gap-stack-md">
        <div class="flex items-center gap-gutter">
            <div class="w-20 h-20 rounded-xl overflow-hidden bg-surface-container-high border border-white/10 flex-shrink-0 flex items-center justify-center">
                @if($b->photo_path)
                    <img src="{{ asset('storage/' . $b->photo_path) }}" alt="{{ $b->full_name }}" class="w-full h-full object-cover" />
                @else
                    <span class="material-symbols-outlined text-on-surface-variant" style="font-size:40px;">person</span>
                @endif
            </div>
            <div class="flex flex-col min-w-0">
                <h2 class="font-headline-md text-headline-md text-primary leading-tight">{{ $b->full_name }}</h2>
                <p class="font-mono text-xs text-on-surface-variant tracking-widest mt-0.5">{{ $b->borrower_code ?? '—' }}</p>
                <p class="font-label-md text-label-md text-on-surface-variant flex items-center gap-1 mt-1">
                    <span class="material-symbols-outlined text-[16px]">phone</span>
                    {{ $b->phone_number ?? 'No phone on file' }}
                </p>
            </div>
        </div>

        <!-- Loan Summary Bento -->
        @if($this->loan)
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2 bg-surface-container-low p-5 rounded-xl border border-white/5 flex flex-col gap-1">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">TOTAL PRINCIPAL</span>
                <span class="font-headline-lg-mobile text-headline-lg-mobile text-primary-fixed">₱{{ number_format((float) $this->loan->principal, 2) }}</span>
            </div>
            <div class="bg-surface-container-low p-5 rounded-xl border border-white/5 flex flex-col gap-1">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">PAID</span>
                <span class="font-headline-md text-headline-md text-primary">₱{{ number_format($amountPaid, 2) }}</span>
                <x-ui.progress-bar :percent="$paidPct" height="h-1" class="mt-2" />
            </div>
            <div class="bg-surface-container-low p-5 rounded-xl border border-white/5 flex flex-col gap-1">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">REMAINING</span>
                <span class="font-headline-md text-headline-md text-error">₱{{ number_format((float) $this->loan->remaining_balance, 2) }}</span>
            </div>
        </div>
        @endif
    </section>

    <!-- Payment Schedule -->
    @if($this->schedule->count())
    <section class="flex flex-col gap-stack-sm mt-stack-lg">
        <div class="flex items-center justify-between">
            <h3 class="font-label-md text-label-md text-primary-fixed uppercase tracking-wider">Payment Schedule</h3>
            <span class="font-label-sm text-label-sm text-on-surface-variant">
                Next: {{ $this->schedule->where('status', 'pending')->first()['due_date'] ?? '—' }}
            </span>
        </div>

        <div class="flex flex-col bg-surface-container rounded-xl divide-y divide-white/5 border border-white/5">
            @foreach($this->schedule as $entry)
            <div class="flex items-center justify-between px-gutter h-[64px] active:bg-surface-variant transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                        {{ $entry['status'] === 'paid' ? 'bg-primary-fixed/20' : ($entry['status'] === 'overdue' ? 'bg-error/20' : 'bg-surface-variant') }}">
                        <span class="material-symbols-outlined text-[20px]
                            {{ $entry['status'] === 'paid' ? 'text-primary-fixed' : ($entry['status'] === 'overdue' ? 'text-error' : 'text-on-surface-variant') }}"
                            @if($entry['status'] === 'paid') style="font-variation-settings: 'FILL' 1" @endif>
                            {{ $entry['status'] === 'paid' ? 'check_circle' : ($entry['status'] === 'overdue' ? 'warning' : 'schedule') }}
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-label-md text-label-md {{ $entry['status'] === 'overdue' ? 'text-error' : 'text-primary' }} {{ $entry['status'] === 'pending' ? 'opacity-60' : '' }}">
                            {{ is_string($entry['due_date']) ? $entry['due_date'] : $entry['due_date']->format('M d, Y') }}
                        </span>
                        <span class="font-label-sm text-label-sm {{ $entry['status'] === 'overdue' ? 'text-error/80' : 'text-on-surface-variant' }}">
                            {{ $entry['status'] === 'paid' ? ('Paid via ' . ($entry['method'] ?? 'Cash')) : ($entry['status'] === 'overdue' ? 'Missed Payment' : 'Upcoming') }}
                        </span>
                    </div>
                </div>
                <span class="font-label-md text-label-md {{ $entry['status'] === 'overdue' ? 'text-error' : 'text-primary' }} {{ $entry['status'] === 'pending' ? 'opacity-60' : '' }}">
                    ₱{{ number_format($entry['amount'], 2) }}
                </span>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    @endif

    <!-- Fixed Scan CTA -->
    @if($this->borrower && $this->loan)
    <div class="fixed bottom-[80px] left-0 w-full px-margin-mobile pb-3 bg-gradient-to-t from-background via-background/90 to-transparent z-40">
        <a href="{{ route('collector.scan', ['borrower' => $borrowerId]) }}"
           class="w-full h-14 bg-primary-fixed text-on-primary-fixed rounded-xl flex items-center justify-center gap-3 font-label-md text-label-md font-bold active:scale-95 transition-all shadow-lg shadow-primary-fixed/20">
            <span class="material-symbols-outlined">qr_code_scanner</span>
            Scan QR to Collect Payment
        </a>
    </div>
    @endif
</div>
