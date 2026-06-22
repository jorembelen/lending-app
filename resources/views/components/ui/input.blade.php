@props([
    'label'       => null,
    'icon'        => null,
    'type'        => 'text',
    'inputmode'   => null,
    'placeholder' => '',
    'error'       => null,
])

<div class="space-y-base">
    @if($label)
        <label class="font-label-md text-label-md text-on-surface ml-1 block">{{ $label }}</label>
    @endif

    <div class="relative">
        <input
            type="{{ $type }}"
            @if($inputmode) inputmode="{{ $inputmode }}" @endif
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge([
                'class' => 'w-full h-14 bg-surface-container-low border ' .
                           ($error ? 'border-error' : 'border-white/10') .
                           ' rounded-xl px-5 text-body-md font-sans text-on-surface placeholder:text-on-surface-variant/40 focus:outline-none focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed'
                           . ($icon ? ' pr-12' : '')
            ]) }}
        />
        @if($icon)
            <span class="material-symbols-outlined absolute right-4 top-4 text-on-surface-variant/40">{{ $icon }}</span>
        @endif
    </div>

    @if($error)
        <p class="font-label-sm text-label-sm text-error ml-1">{{ $error }}</p>
    @endif
</div>
