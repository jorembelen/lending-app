<div class="space-y-stack-md pb-8">

    <!-- Loan Summary Strip -->
    @if($this->loan)
    <div class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl p-4 flex items-center justify-between">
        <div>
            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">LOAN #{{ $this->loan->id }}</p>
            <p class="font-bold text-on-surface">₱{{ number_format($this->loan->principal, 2) }} principal</p>
        </div>
        <x-ui.badge :variant="$this->loan->status === 'overdue' ? 'overdue' : 'on-track'">
            {{ strtoupper($this->loan->status) }}
        </x-ui.badge>
    </div>

    <!-- Filter Chips -->
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
        @foreach(['all' => 'All', 'paid' => 'Paid', 'pending' => 'Pending', 'overdue' => 'Overdue'] as $value => $label)
        <button
            wire:click="setFilter('{{ $value }}')"
            class="flex-shrink-0 px-4 h-8 rounded-full text-xs font-bold border transition-all
                {{ $filter === $value
                    ? 'bg-primary-fixed text-on-primary-fixed border-transparent'
                    : 'bg-surface-container-low text-on-surface-variant border-outline-variant hover:border-outline' }}"
        >{{ $label }}</button>
        @endforeach
    </div>

    <!-- Schedule List -->
    @if($this->schedule->count())
    <div class="space-y-3">
        @foreach($this->schedule as $index => $payment)
        @php
            $isOverdue = $payment->status === 'overdue';
            $isPaid    = $payment->status === 'paid';
            $isCurrent = ! $isPaid && ! $isOverdue;
            $borderColor = $isOverdue ? 'border-l-error' : ($isPaid ? 'border-l-outline-variant' : 'border-l-primary-fixed');
            $iconName    = $isPaid ? 'check_circle' : ($isOverdue ? 'error' : 'schedule');
            $iconColor   = $isPaid ? 'text-primary-fixed' : ($isOverdue ? 'text-error' : 'text-on-surface-variant');
        @endphp
        <div class="bg-[#1A1C24] border border-[#2D2F39] border-l-4 {{ $borderColor }} rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined {{ $iconColor }}" style="font-variation-settings: 'FILL' {{ $isPaid ? '1' : '0' }};">{{ $iconName }}</span>
                <div>
                    <p class="font-bold text-on-surface text-[14px]">
                        Installment #{{ $index + 1 }}
                        @if($isCurrent) <span class="text-[10px] font-bold text-primary-fixed ml-1 uppercase">NEXT</span> @endif
                    </p>
                    <p class="text-xs text-on-surface-variant">
                        Due {{ $payment->due_date?->format('M d, Y') ?? '—' }}
                        @if($isOverdue) <span class="text-error font-medium"> • {{ $payment->due_date?->diffForHumans() }}</span> @endif
                    </p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-bold text-on-surface">₱{{ number_format($payment->amount, 2) }}</p>
                @if($isPaid)
                <p class="text-[10px] text-on-surface-variant">{{ $payment->collected_at?->format('M d') }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <span class="material-symbols-outlined text-on-surface-variant/40 mb-3" style="font-size:48px;">calendar_month</span>
        <p class="text-on-surface-variant/60">No {{ $filter !== 'all' ? $filter : '' }} payments found.</p>
    </div>
    @endif

    @else
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <span class="material-symbols-outlined text-on-surface-variant/40 mb-3" style="font-size:48px;">account_balance</span>
        <p class="font-headline-md text-headline-md text-on-surface-variant/60">No active loan</p>
        <p class="font-body-md text-secondary-fixed-dim mt-1">You have no current repayment schedule.</p>
    </div>
    @endif

</div>
