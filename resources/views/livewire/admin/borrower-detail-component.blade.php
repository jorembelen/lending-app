<div>
    @if(!$this->borrower)
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <span class="material-symbols-outlined text-on-surface-variant/40 mb-3" style="font-size:48px;">person_off</span>
            <p class="font-headline-md text-headline-md text-on-surface-variant/60">Borrower not found</p>
        </div>
    @else

    @php
        $b          = $this->borrower;
        $activeLoan = $this->activeLoan;
        $amountPaid = $activeLoan
            ? (float) $activeLoan->payments()->where('is_voided', false)->sum('amount')
            : 0.0;
        $paidPct    = ($activeLoan && $activeLoan->total_payable > 0)
            ? min(100, $amountPaid / (float) $activeLoan->total_payable * 100)
            : 0;
    @endphp

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-6 text-label-sm font-label-sm">
        @if(Route::has('admin.borrowers'))
        <a href="{{ route('admin.borrowers') }}" class="text-secondary-fixed-dim hover:text-primary-fixed transition-colors uppercase tracking-wider">Borrowers</a>
        <span class="text-outline-variant">/</span>
        @endif
        <span class="text-primary-fixed font-bold uppercase tracking-wider">{{ $b->full_name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Profile + QR + Loan Summary -->
        <div class="lg:col-span-1 space-y-6">
            <x-ui.card>
                <!-- Avatar + Name -->
                <div class="flex flex-col items-center text-center gap-3">
                    <div class="w-20 h-20 rounded-xl bg-surface-bright border border-outline flex items-center justify-center overflow-hidden">
                        @if($b->photo_path)
                            <img src="{{ asset('storage/' . $b->photo_path) }}" alt="{{ $b->full_name }}" class="w-full h-full object-cover" />
                        @else
                            <span class="material-symbols-outlined text-on-surface-variant" style="font-size:40px;">person</span>
                        @endif
                    </div>
                    <div>
                        <h2 class="font-headline-md text-headline-md text-primary">{{ $b->full_name }}</h2>
                        <p class="font-label-sm text-label-sm text-secondary-fixed-dim mt-1 tracking-widest font-mono">
                            {{ $b->borrower_code ?? '—' }}
                        </p>
                    </div>
                    @if($activeLoan)
                        <x-ui.badge :variant="$activeLoan->status">{{ ucfirst($activeLoan->status) }}</x-ui.badge>
                    @endif
                </div>

                <!-- Contact Info -->
                <div class="mt-5 pt-5 border-t border-outline-variant space-y-3">
                    <div class="flex items-center gap-3 text-label-md font-label-md">
                        <span class="material-symbols-outlined text-[18px] text-secondary-fixed-dim">phone</span>
                        <span class="text-on-surface">{{ $b->phone_number ?? '—' }}</span>
                    </div>
                    @if($b->account?->email)
                    <div class="flex items-center gap-3 text-label-md font-label-md">
                        <span class="material-symbols-outlined text-[18px] text-secondary-fixed-dim">email</span>
                        <span class="text-on-surface truncate">{{ $b->account->email }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-3 text-label-md font-label-md">
                        <span class="material-symbols-outlined text-[18px] text-secondary-fixed-dim">location_on</span>
                        <span class="text-on-surface">{{ $b->address ?? '—' }}</span>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="mt-5 pt-5 border-t border-outline-variant flex flex-col items-center gap-3">
                    <p class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider self-start">Borrower QR Code</p>
                    <div class="bg-white rounded-xl p-3 [&_svg]:w-full [&_svg]:h-full w-44 h-44">
                        {!! $this->qrCodeSvg !!}
                    </div>
                    <p class="font-mono text-xs text-on-surface-variant tracking-widest">{{ $b->borrower_code ?? '—' }}</p>
                    <p class="text-[10px] text-on-surface-variant/50 text-center leading-relaxed">
                        Collector scans this QR to instantly identify the borrower and log payments.
                    </p>
                </div>
            </x-ui.card>

            @if(Route::has('admin.loans.create'))
            <a href="{{ route('admin.loans.create', ['borrower' => $borrowerId]) }}"
               class="w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl flex items-center justify-center gap-2 font-label-md text-label-md font-bold hover:brightness-110 transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Release New Loan
            </a>
            @endif
        </div>

        <!-- Right: Active Loan + Loan History -->
        <div class="lg:col-span-2 space-y-6">

            @if($activeLoan)
            <x-ui.card>
                <h3 class="font-label-md text-label-md text-primary-fixed uppercase tracking-wider mb-4">Active Loan</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <x-data.kpi-card label="Principal"
                        :value="'₱' . number_format((float) $activeLoan->principal, 2)" />
                    <x-data.kpi-card label="Amount Paid"
                        :value="'₱' . number_format($amountPaid, 2)"
                        :highlight="true" />
                    <x-data.kpi-card label="Remaining"
                        :value="'₱' . number_format((float) $activeLoan->remaining_balance, 2)" />
                </div>
                <div class="mt-4 space-y-1">
                    <x-ui.progress-bar :percent="$paidPct" :label="round($paidPct) . '% paid'" />
                </div>
                <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                    <div class="bg-surface-dim rounded-lg p-3">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Daily</p>
                        <p class="font-bold text-on-surface text-sm mt-0.5">₱{{ number_format((float) $activeLoan->daily_installment, 2) }}</p>
                    </div>
                    <div class="bg-surface-dim rounded-lg p-3">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Term</p>
                        <p class="font-bold text-on-surface text-sm mt-0.5">{{ $activeLoan->term_days_locked }} days</p>
                    </div>
                    <div class="bg-surface-dim rounded-lg p-3">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Rate</p>
                        <p class="font-bold text-on-surface text-sm mt-0.5">₱{{ number_format((float) $activeLoan->rate_per_1000_locked, 2) }}/₱1k</p>
                    </div>
                </div>
            </x-ui.card>
            @endif

            <x-ui.card>
                <h3 class="font-label-md text-label-md text-primary-fixed uppercase tracking-wider mb-4">All Loans</h3>
                <p class="text-[11px] text-on-surface-variant/50 -mt-3 mb-2">Tap a loan to view its statement of account.</p>
                @forelse($this->loans as $loan)
                <button
                    type="button"
                    wire:click="selectLoan({{ $loan->id }})"
                    class="w-full flex items-center justify-between py-3 px-2 -mx-2 rounded-lg border-b border-outline-variant/30 last:border-0 text-left hover:bg-surface-dim/60 active:bg-surface-dim transition-colors cursor-pointer group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-surface-dim flex items-center justify-center flex-shrink-0 group-hover:bg-primary-fixed/10 transition-colors">
                            <span class="material-symbols-outlined text-[16px] text-secondary-fixed-dim group-hover:text-primary-fixed transition-colors">receipt_long</span>
                        </div>
                        <div>
                            <p class="font-label-md text-label-md text-on-surface font-mono">#{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</p>
                            <p class="font-label-sm text-label-sm text-secondary-fixed-dim">
                                {{ $loan->disbursed_at?->format('M d, Y') }} • ₱{{ number_format((float) $loan->principal, 2) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-ui.badge :variant="$loan->status">{{ ucfirst($loan->status) }}</x-ui.badge>
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant/40 group-hover:text-primary-fixed transition-colors">chevron_right</span>
                    </div>
                </button>
                @empty
                <p class="text-secondary-fixed-dim font-body-md text-center py-8">No loan history found.</p>
                @endforelse
            </x-ui.card>
        </div>
    </div>

    @endif

    {{-- ═══════════ Statement of Account Modal ═══════════ --}}
    @if($this->selectedLoan)
    @php
        $sl          = $this->selectedLoan;
        $slPaid      = (float) $sl->payments->where('is_voided', false)->sum('amount');
        $slRemaining = max(0, (float) $sl->total_payable - $slPaid);
        $slPct       = $sl->total_payable > 0 ? min(100, $slPaid / (float) $sl->total_payable * 100) : 0;
        $slInterest  = (float) $sl->total_payable - (float) $sl->principal;
    @endphp

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" wire:click.self="closeStatement">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeStatement"></div>

        <div class="relative z-10 w-full max-w-2xl bg-surface-container-low rounded-2xl border border-white/10 shadow-2xl flex flex-col max-h-[90vh]">

            <!-- Header -->
            <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-outline-variant/30 flex-shrink-0">
                <div>
                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Statement of Account</p>
                    <h2 class="font-headline-md text-headline-md text-primary-fixed">Loan #{{ str_pad($sl->id, 5, '0', STR_PAD_LEFT) }}</h2>
                    <p class="font-label-sm text-label-sm text-secondary-fixed-dim mt-0.5">
                        Released {{ $sl->disbursed_at?->format('F j, Y') ?? '—' }}
                        @if($sl->disbursedBy) · by {{ $sl->disbursedBy->name }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <x-ui.badge :variant="$sl->status">{{ ucfirst($sl->status) }}</x-ui.badge>
                    <button type="button" wire:click="closeStatement"
                            class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-surface-dim text-on-surface-variant hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
            </div>

            <!-- Scrollable Body -->
            <div class="overflow-y-auto flex-1 px-6 py-5 space-y-6">

                <!-- Financial Summary -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-surface-dim rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Principal</p>
                        <p class="font-bold text-on-surface text-[15px]">₱{{ number_format((float) $sl->principal, 2) }}</p>
                    </div>
                    <div class="bg-surface-dim rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Interest</p>
                        <p class="font-bold text-on-surface text-[15px]">₱{{ number_format($slInterest, 2) }}</p>
                    </div>
                    <div class="bg-surface-dim rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Total Payable</p>
                        <p class="font-bold text-primary-fixed text-[15px]">₱{{ number_format((float) $sl->total_payable, 2) }}</p>
                    </div>
                </div>

                <!-- Progress -->
                <div class="bg-surface-dim rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Amount Paid</p>
                            <p class="font-bold text-[18px] text-primary">₱{{ number_format($slPaid, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Balance Remaining</p>
                            <p class="font-bold text-[18px] {{ $slRemaining > 0 ? 'text-error' : 'text-primary' }}">
                                ₱{{ number_format($slRemaining, 2) }}
                            </p>
                        </div>
                    </div>
                    <x-ui.progress-bar :percent="round($slPct)" :label="round($slPct) . '% of total payable collected'" />
                </div>

                <!-- Loan Terms -->
                <div>
                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-3">Loan Terms</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-surface-dim rounded-xl p-4 text-center">
                            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Daily</p>
                            <p class="font-bold text-on-surface text-sm">₱{{ number_format((float) $sl->daily_installment, 2) }}</p>
                        </div>
                        <div class="bg-surface-dim rounded-xl p-4 text-center">
                            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Term</p>
                            <p class="font-bold text-on-surface text-sm">{{ $sl->term_days_locked }} days</p>
                        </div>
                        <div class="bg-surface-dim rounded-xl p-4 text-center">
                            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Rate</p>
                            <p class="font-bold text-on-surface text-sm">₱{{ number_format((float) $sl->rate_per_1000_locked, 2) }}/₱1k</p>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Payment History</p>
                        <span class="text-[10px] font-bold text-on-surface-variant">
                            {{ $sl->payments->count() }} {{ $sl->payments->count() === 1 ? 'record' : 'records' }}
                        </span>
                    </div>

                    @if($sl->payments->isEmpty())
                        <div class="bg-surface-dim rounded-xl p-8 text-center">
                            <span class="material-symbols-outlined text-on-surface-variant/40 block mb-2" style="font-size:36px;">payments</span>
                            <p class="font-label-md text-label-md text-on-surface-variant/60">No payments recorded yet</p>
                        </div>
                    @else
                        <!-- Header row -->
                        <div class="bg-surface-dim rounded-t-xl px-4 py-2 grid grid-cols-12 gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                            <div class="col-span-1">#</div>
                            <div class="col-span-4">Date</div>
                            <div class="col-span-3 text-right">Amount</div>
                            <div class="col-span-2">Collector</div>
                            <div class="col-span-2 text-center">Status</div>
                        </div>

                        <div class="border border-outline-variant/20 rounded-b-xl divide-y divide-outline-variant/20 overflow-hidden">
                            @foreach($sl->payments->sortBy('collected_at')->values() as $i => $payment)
                            <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center {{ $payment->is_voided ? 'opacity-50 bg-error/5' : 'hover:bg-surface-dim/40' }} transition-colors">
                                <div class="col-span-1 font-mono text-[11px] text-on-surface-variant">{{ $i + 1 }}</div>
                                <div class="col-span-4">
                                    <p class="font-label-sm text-label-sm text-on-surface">{{ $payment->collected_at?->format('M d, Y') ?? '—' }}</p>
                                    <p class="text-[10px] text-on-surface-variant">{{ $payment->collected_at?->format('h:i A') ?? '' }}</p>
                                </div>
                                <div class="col-span-3 text-right">
                                    <p class="font-bold text-[14px] {{ $payment->is_voided ? 'text-on-surface-variant line-through' : 'text-primary-fixed' }}">
                                        ₱{{ number_format((float) $payment->amount, 2) }}
                                    </p>
                                </div>
                                <div class="col-span-2">
                                    <p class="font-label-sm text-label-sm text-on-surface truncate">{{ $payment->collector?->name ?? '—' }}</p>
                                </div>
                                <div class="col-span-2 flex justify-center">
                                    @if($payment->is_voided)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-error/20 text-error border border-error/30">Voided</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-primary-fixed/10 text-primary-fixed border border-primary-fixed/20">Paid</span>
                                    @endif
                                </div>
                                @if($payment->is_voided && $payment->voided_reason)
                                <div class="col-span-12 -mt-1">
                                    <p class="text-[10px] text-error/70 italic">Void reason: {{ $payment->voided_reason }}</p>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <!-- Totals footer -->
                        <div class="mt-2 bg-surface-dim rounded-xl px-4 py-3 flex justify-between items-center">
                            <p class="font-label-sm text-label-sm text-on-surface-variant">
                                {{ $sl->payments->where('is_voided', false)->count() }} valid payment(s)
                                @if($sl->payments->where('is_voided', true)->count())
                                    · {{ $sl->payments->where('is_voided', true)->count() }} voided
                                @endif
                            </p>
                            <p class="font-bold text-primary-fixed">Total Paid: ₱{{ number_format($slPaid, 2) }}</p>
                        </div>
                    @endif
                </div>

                @if($sl->closed_at)
                <div class="bg-surface-dim rounded-xl px-4 py-3 flex items-center gap-2 text-on-surface-variant">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                    <p class="font-label-sm text-label-sm">
                        Loan closed on {{ $sl->closed_at->format('F j, Y') }}
                        @if($sl->missed_days_at_closure) · {{ $sl->missed_days_at_closure }} missed day(s) at closure @endif
                    </p>
                </div>
                @endif

            </div>
        </div>
    </div>
    @endif

</div>
