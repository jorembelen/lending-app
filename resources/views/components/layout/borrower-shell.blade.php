@props([
    'title'     => 'Voltage',
    'activeTab' => 'home',
    'showBack'  => false,
    'backHref'  => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }} — {{ config('app.name') }}</title>
    @vite(['resources/css/tailwind.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="dark flex flex-col min-h-screen bg-background text-on-surface font-sans overflow-x-hidden pb-32">

    <!-- Top App Bar -->
    <header class="w-full sticky top-0 z-50 bg-background flex justify-between items-center h-14 px-margin-mobile border-b border-white/5">
        <div class="flex items-center gap-3">
            @if($showBack)
                <a href="{{ $backHref ?? 'javascript:history.back()' }}"
                   class="flex items-center justify-center w-10 h-10 -ml-2 rounded-full active:bg-surface-container transition-colors"
                   aria-label="Go back">
                    <span class="material-symbols-outlined text-primary">arrow_back</span>
                </a>
            @else
                <div class="w-8 h-8 rounded-full bg-surface-container overflow-hidden border border-outline-variant flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">person</span>
                </div>
                <span class="font-bold text-primary-fixed text-[20px] leading-tight tracking-tight">Voltage</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            {{ $actions ?? '' }}
            <button class="text-on-surface-variant hover:bg-surface-variant p-2 rounded-full transition-transform active:scale-90">
                <span class="material-symbols-outlined">notifications</span>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="px-margin-mobile pt-4 space-y-stack-md">
        {{ $slot }}
    </main>

    <!-- Bottom Tab Bar -->
    <nav class="fixed bottom-0 w-full z-50 bg-surface-container border-t border-white/10 flex justify-around items-center px-4 pb-safe" style="height: 64px;">
        <a href="{{ route('borrower.home') }}"
           class="flex flex-col items-center justify-center gap-0.5 {{ $activeTab === 'home' ? 'text-primary-fixed' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined" @if($activeTab === 'home') style="font-variation-settings: 'FILL' 1" @endif>home</span>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="{{ route('borrower.schedule') }}"
           class="flex flex-col items-center justify-center gap-0.5 {{ $activeTab === 'schedule' ? 'text-primary-fixed' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined" @if($activeTab === 'schedule') style="font-variation-settings: 'FILL' 1" @endif>calendar_month</span>
            <span class="text-[10px] font-medium">Schedule</span>
        </a>
        <a href="{{ route('borrower.rewards') }}"
           class="flex flex-col items-center justify-center gap-0.5 {{ $activeTab === 'rewards' ? 'text-primary-fixed' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined" @if($activeTab === 'rewards') style="font-variation-settings: 'FILL' 1" @endif>card_giftcard</span>
            <span class="text-[10px] font-medium">Rewards</span>
        </a>
        <a href="{{ route('borrower.history') }}"
           class="flex flex-col items-center justify-center gap-0.5 {{ $activeTab === 'history' ? 'text-primary-fixed' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined" @if($activeTab === 'history') style="font-variation-settings: 'FILL' 1" @endif>history</span>
            <span class="text-[10px] font-medium">History</span>
        </a>
        <a href="{{ route('borrower.profile') }}"
           class="flex flex-col items-center justify-center gap-0.5 {{ $activeTab === 'profile' ? 'text-primary-fixed' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined" @if($activeTab === 'profile') style="font-variation-settings: 'FILL' 1" @endif>person</span>
            <span class="text-[10px] font-medium">Profile</span>
        </a>
    </nav>

    @livewireScripts
</body>
</html>
