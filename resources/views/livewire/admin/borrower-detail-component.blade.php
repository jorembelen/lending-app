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
                @forelse($this->loans as $loan)
                <div class="flex items-center justify-between py-3 border-b border-outline-variant/30 last:border-0">
                    <div>
                        <p class="font-label-md text-label-md text-on-surface font-mono">#{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</p>
                        <p class="font-label-sm text-label-sm text-secondary-fixed-dim">
                            {{ $loan->disbursed_at?->format('M d, Y') }} • ₱{{ number_format((float) $loan->principal, 2) }}
                        </p>
                    </div>
                    <x-ui.badge :variant="$loan->status">{{ ucfirst($loan->status) }}</x-ui.badge>
                </div>
                @empty
                <p class="text-secondary-fixed-dim font-body-md text-center py-8">No loan history found.</p>
                @endforelse
            </x-ui.card>
        </div>
    </div>

    @endif
</div>
