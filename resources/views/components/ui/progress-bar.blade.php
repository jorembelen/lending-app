@props([
    'percent'   => 0,
    'label'     => null,
    'sublabel'  => null,
    'height'    => 'h-2',
    'color'     => 'bg-primary-fixed',
    'trackColor'=> 'bg-surface-variant',
])

<div {{ $attributes }}>
    @if($label || $sublabel)
        <div class="flex justify-between items-end mb-2">
            @if($label)
                <span class="font-label-sm text-label-sm text-primary-fixed">{{ $label }}</span>
            @endif
            @if($sublabel)
                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $sublabel }}</span>
            @endif
        </div>
    @endif

    <div class="{{ $height }} w-full {{ $trackColor }} rounded-full overflow-hidden">
        <div
            class="{{ $height }} {{ $color }} rounded-full transition-all duration-500"
            style="width: {{ min(100, max(0, $percent)) }}%"
        ></div>
    </div>
</div>
