@props([
    'borrower',       // Eloquent model or array-like: name, loan_id, amount_due, status
    'href'   => '#',
    'action' => null, // 'collect', 'details', null
])

@php
$status     = $borrower['status'] ?? $borrower->status ?? 'pending';
$name       = $borrower['name'] ?? $borrower->name ?? '';
$loanId     = $borrower['loan_id'] ?? $borrower->loan_id ?? '';
$amountDue  = $borrower['amount_due'] ?? $borrower->amount_due ?? 0;
$avatar     = $borrower['avatar'] ?? $borrower->avatar ?? null;

$statusIcon = match($status) {
    'paid'           => ['icon' => 'check_circle', 'color' => 'text-primary-fixed'],
    'partially_paid' => ['icon' => 'pie_chart',    'color' => 'text-yellow-400'],
    'missed'         => ['icon' => 'error',         'color' => 'text-error'],
    default          => null,
};

$statusLabel = ucwords(str_replace('_', ' ', $status));
$amountClass = $status === 'paid' ? 'text-primary-fixed/60' : 'text-primary-fixed';
@endphp

<div class="bg-surface-container rounded-xl p-5 border border-white/5 active:scale-[0.98] transition-transform {{ $status === 'paid' ? 'opacity-80' : '' }}">
    <div class="flex justify-between items-start mb-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-surface-bright flex items-center justify-center border border-white/10 overflow-hidden flex-shrink-0">
                @if($avatar)
                    <img src="{{ $avatar }}" alt="{{ $name }}" class="w-full h-full object-cover" />
                @else
                    <span class="material-symbols-outlined text-on-surface-variant">person</span>
                @endif
            </div>
            <div>
                <h3 class="font-body-lg text-body-lg font-bold text-white leading-tight">{{ $name }}</h3>
                <p class="font-label-sm text-label-sm text-on-surface-variant">Loan ID: #{{ $loanId }}</p>
            </div>
        </div>

        <div class="flex flex-col items-end gap-1">
            @if($statusIcon)
                <span class="material-symbols-outlined {{ $statusIcon['color'] }}" style="font-variation-settings: 'FILL' 1;">{{ $statusIcon['icon'] }}</span>
            @else
                <div class="w-6 h-6 rounded-full border-2 border-on-surface-variant/30 flex items-center justify-center">
                    <span class="w-2 h-2 rounded-full bg-on-surface-variant/30"></span>
                </div>
            @endif
            <x-ui.badge :variant="$status">{{ $statusLabel }}</x-ui.badge>
        </div>
    </div>

    <div class="flex justify-between items-center pt-3 border-t border-white/5">
        <div>
            <p class="font-label-sm text-label-sm text-on-surface-variant">Amount Due</p>
            <p class="font-headline-md text-headline-md {{ $amountClass }}">₱{{ number_format($amountDue, 2) }}</p>
        </div>

        @if($action === 'collect' && $status !== 'paid')
            <a href="{{ $href }}" class="bg-primary-fixed text-on-primary-fixed px-4 py-2 rounded-lg font-label-md text-label-md font-bold">Collect</a>
        @elseif($action === 'details')
            <a href="{{ $href }}" class="bg-surface-bright text-white px-4 py-2 rounded-lg font-label-md text-label-md border border-white/10">Details</a>
        @elseif($status === 'paid')
            <span class="text-on-surface-variant font-label-md text-label-md">
                {{ $borrower['paid_at'] ?? '' }}
            </span>
        @endif
    </div>
</div>
