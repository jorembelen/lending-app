@props([
    'padding' => 'p-5',
    'glow'    => false,
])

@php
$base = 'bg-surface-container rounded-xl border border-white/5';
$classes = implode(' ', array_filter([$base, $padding, $glow ? 'shadow-[0_0_30px_rgba(195,244,0,0.05)]' : '']));
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
