@props([
    'variant' => 'primary',
    'size'    => 'md',
    'icon'    => null,
    'iconPosition' => 'right',
    'type'    => 'button',
    'disabled' => false,
])

@php
$base = 'inline-flex items-center justify-center gap-2 font-label-md text-label-md rounded-xl transition-all active:scale-[0.98] focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed';

$sizes = [
    'sm' => 'h-10 px-4',
    'md' => 'h-14 px-6',
    'lg' => 'h-[56px] px-8 w-full',
];

$variants = [
    'primary'     => 'bg-primary-fixed text-on-primary-fixed hover:brightness-110 shadow-[0_8px_24px_rgba(195,244,0,0.15)]',
    'secondary'   => 'border border-white/10 text-on-surface hover:bg-white/5',
    'destructive' => 'bg-error-container text-on-error-container hover:brightness-110',
    'ghost'       => 'text-on-surface-variant hover:bg-white/5',
    'outline'     => 'border border-outline text-primary hover:bg-surface-container-highest',
];

$classes = implode(' ', [$base, $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary']]);
@endphp

<button
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($icon && $iconPosition === 'left')
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">{{ $icon }}</span>
    @endif

    {{ $slot }}

    @if($icon && $iconPosition === 'right')
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">{{ $icon }}</span>
    @endif
</button>
