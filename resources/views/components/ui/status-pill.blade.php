@props([
    'status' => 'on-track',
    'pulse'  => false,
])

@php
$statusMap = [
    'on-track'  => ['bg-primary-fixed/10 border-primary-fixed/20 text-primary-fixed', 'bg-primary-fixed'],
    'paid'      => ['bg-primary-fixed/10 border-primary-fixed/20 text-primary-fixed', 'bg-primary-fixed'],
    'overdue'   => ['bg-on-tertiary-fixed-variant/20 border-on-tertiary-container/30 text-on-tertiary-container', 'bg-on-tertiary-container'],
    'pending'   => ['bg-surface-variant border-outline-variant text-on-surface-variant', 'bg-on-surface-variant'],
    'warning'   => ['bg-yellow-900/30 border-yellow-700/30 text-yellow-400', 'bg-yellow-400'],
    'active'    => ['bg-primary-fixed/10 border-primary-fixed/20 text-primary-fixed', 'bg-primary-fixed'],
];

[$pillClass, $dotClass] = $statusMap[$status] ?? $statusMap['pending'];
@endphp

<div class="inline-flex items-center gap-2 {{ $pillClass }} border px-4 py-1.5 rounded-full">
    <span class="w-2 h-2 rounded-full {{ $dotClass }} {{ $pulse ? 'animate-pulse' : '' }}"></span>
    <span class="font-label-sm text-label-sm uppercase tracking-wider">{{ $slot }}</span>
</div>
