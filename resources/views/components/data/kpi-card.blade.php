@props([
    'label'   => '',
    'value'   => '',
    'subtext' => null,
    'icon'    => null,
    'trend'   => null,   // 'up', 'down', null
    'trendValue' => null,
    'highlight' => false,
])

<div {{ $attributes->merge(['class' => 'bg-surface-container rounded-xl p-5 border border-white/5']) }}>
    <div class="flex justify-between items-start mb-3">
        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">{{ $label }}</p>
        @if($icon)
            <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px]">{{ $icon }}</span>
        @endif
    </div>

    <p class="font-headline-md text-headline-md {{ $highlight ? 'text-primary-fixed' : 'text-on-surface' }} mb-1">{{ $value }}</p>

    @if($subtext || $trend)
        <div class="flex items-center gap-2 mt-1">
            @if($trend)
                <span class="material-symbols-outlined text-[14px] {{ $trend === 'up' ? 'text-primary-fixed' : 'text-error' }}">
                    {{ $trend === 'up' ? 'trending_up' : 'trending_down' }}
                </span>
                @if($trendValue)
                    <span class="font-label-sm text-label-sm {{ $trend === 'up' ? 'text-primary-fixed' : 'text-error' }}">{{ $trendValue }}</span>
                @endif
            @endif
            @if($subtext)
                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $subtext }}</span>
            @endif
        </div>
    @endif
</div>
