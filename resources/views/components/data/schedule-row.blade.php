@props([
    'entry',     // due_date, amount, status, installment_no
    'current' => false,
])

@php
$status        = $entry['status'] ?? $entry->status ?? 'pending';
$dueDate       = $entry['due_date'] ?? $entry->due_date ?? '';
$amount        = $entry['amount'] ?? $entry->amount ?? 0;
$installmentNo = $entry['installment_no'] ?? $entry->installment_no ?? '';

$rowClass = match($status) {
    'paid'    => 'opacity-60',
    'overdue' => 'border-l-2 border-on-tertiary-container',
    default   => $current ? 'border-l-2 border-primary-fixed' : '',
};
@endphp

<div class="bg-surface-container rounded-xl p-4 border border-white/5 {{ $rowClass }}">
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-surface-bright flex items-center justify-center border border-white/10">
                @if($status === 'paid')
                    <span class="material-symbols-outlined text-primary-fixed text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                @elseif($status === 'overdue')
                    <span class="material-symbols-outlined text-yellow-500 text-[18px]" style="font-variation-settings: 'FILL' 1;">warning</span>
                @else
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px]">calendar_today</span>
                @endif
            </div>
            <div>
                <p class="font-label-md text-label-md text-on-surface">
                    {{ $installmentNo ? 'Installment #' . $installmentNo : '' }}
                </p>
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    {{ is_string($dueDate) ? $dueDate : $dueDate->format('M d, Y') }}
                </p>
            </div>
        </div>

        <div class="text-right">
            <p class="font-headline-md text-headline-md {{ $status === 'paid' ? 'text-primary-fixed/60' : 'text-primary-fixed' }}">
                ₱{{ number_format($amount, 2) }}
            </p>
            <x-ui.badge :variant="$status">{{ ucfirst($status) }}</x-ui.badge>
        </div>
    </div>
</div>
