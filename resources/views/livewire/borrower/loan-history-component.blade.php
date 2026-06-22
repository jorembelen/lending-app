<div class="space-y-stack-md pb-8">

    @if($this->loans->count())

    <!-- Stats Strip -->
    @php
        $totalLoans     = $this->loans->count();
        $completedLoans = $this->loans->where('status', 'completed')->count();
        $totalBorrowed  = $this->loans->sum('principal');
    @endphp
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-[#1A1C24] border border-[#2D2F39] p-3 rounded-xl text-center">
            <p class="text-2xl font-bold text-on-surface">{{ $totalLoans }}</p>
            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">Loans</p>
        </div>
        <div class="bg-[#1A1C24] border border-[#2D2F39] p-3 rounded-xl text-center">
            <p class="text-2xl font-bold text-primary">{{ $completedLoans }}</p>
            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">Completed</p>
        </div>
        <div class="bg-[#1A1C24] border border-[#2D2F39] p-3 rounded-xl text-center">
            <p class="text-lg font-bold text-on-surface leading-tight">₱{{ number_format($totalBorrowed / 1000, 1) }}K</p>
            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">Borrowed</p>
        </div>
    </div>

    <!-- Loan List -->
    <div class="space-y-4">
        @foreach($this->loans as $loan)
        @php
            $statusVariant = match($loan->status) {
                'completed' => 'paid',
                'overdue'   => 'overdue',
                'active'    => 'on-track',
                default     => 'neutral',
            };
            $paidAmt  = $loan->amount_paid ?? 0;
            $paidPct  = $loan->principal > 0 ? min(100, ($paidAmt / $loan->principal) * 100) : 0;
        @endphp
        <div class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl p-5 space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">LOAN #{{ $loan->id }}</p>
                    <p class="text-xl font-bold text-on-surface">₱{{ number_format($loan->principal, 2) }}</p>
                    <p class="text-xs text-on-surface-variant">Released {{ $loan->created_at?->format('M d, Y') }}</p>
                </div>
                <x-ui.badge :variant="$statusVariant">{{ strtoupper($loan->status) }}</x-ui.badge>
            </div>

            <div class="space-y-1.5">
                <div class="flex justify-between text-xs text-on-surface-variant">
                    <span>₱{{ number_format($paidAmt, 2) }} paid</span>
                    <span>{{ round($paidPct) }}%</span>
                </div>
                <x-ui.progress-bar :percent="$paidPct" :color="$loan->status === 'overdue' ? 'bg-error' : null" />
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <p class="text-on-surface-variant uppercase font-bold mb-0.5">Term</p>
                    <p class="text-on-surface">{{ $loan->term_days ?? '—' }} days</p>
                </div>
                <div>
                    <p class="text-on-surface-variant uppercase font-bold mb-0.5">Daily Payment</p>
                    <p class="text-on-surface">₱{{ number_format($loan->daily_payment ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-on-surface-variant uppercase font-bold mb-0.5">Interest Rate</p>
                    <p class="text-on-surface">{{ $loan->interest_rate ?? '—' }}%</p>
                </div>
                <div>
                    <p class="text-on-surface-variant uppercase font-bold mb-0.5">Remaining</p>
                    <p class="{{ $loan->status === 'completed' ? 'text-primary' : 'text-on-surface' }} font-bold">
                        {{ $loan->status === 'completed' ? 'PAID OFF' : '₱'.number_format($loan->remaining_balance ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @else
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <span class="material-symbols-outlined text-on-surface-variant/40 mb-3" style="font-size:48px;">history</span>
        <p class="font-headline-md text-headline-md text-on-surface-variant/60">No loan history</p>
        <p class="font-body-md text-secondary-fixed-dim mt-1">Your past and current loans will appear here.</p>
    </div>
    @endif

</div>
