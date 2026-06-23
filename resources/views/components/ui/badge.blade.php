@props([
    'variant' => 'neutral',
])

@php
$base = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase';

$variants = [
    'paid'           => 'bg-primary-fixed/10 text-primary-fixed border border-primary-fixed/20',
    'pending'        => 'bg-surface-variant text-on-surface-variant',
    'partially_paid' => 'bg-yellow-900/30 text-yellow-400 border border-yellow-700/30',
    'missed'         => 'bg-error-container/20 text-error border border-error/30',
    'overdue'        => 'bg-on-tertiary-fixed-variant/20 text-on-tertiary-container border border-on-tertiary-container/30',
    'on-track' => 'bg-primary-fixed/10 text-primary-fixed border border-primary-fixed/20',
    'warning'  => 'bg-yellow-900/30 text-yellow-400 border border-yellow-700/30',
    'danger'   => 'bg-error-container/20 text-error border border-error/30',
    'neutral'  => 'bg-surface-variant text-on-surface-variant',
    'tier'     => 'bg-secondary-container/20 text-secondary-container border border-secondary-container/40',
    'success'  => 'bg-primary-fixed/10 text-primary-fixed border border-primary-fixed/20',
];

$classes = implode(' ', [$base, $variants[$variant] ?? $variants['neutral']]);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
