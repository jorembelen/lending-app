@props([
    'title'      => 'Collector',
    'activeTab'  => 'route',
    'showBack'   => false,
    'backHref'   => null,
    'showNotif'  => true,
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
<body class="dark flex flex-col min-h-screen bg-background text-on-surface font-sans overflow-x-hidden">

    <!-- Top App Bar -->
    <header class="sticky top-0 z-50 bg-background flex items-center justify-between px-margin-mobile h-14 border-b border-white/5">
        <div class="flex items-center gap-2">
            @if($showBack)
                <a href="{{ $backHref ?? 'javascript:history.back()' }}"
                   class="flex items-center justify-center w-10 h-10 -ml-2 rounded-full active:bg-surface-container transition-colors"
                   aria-label="Go back">
                    <span class="material-symbols-outlined text-primary">arrow_back</span>
                </a>
            @endif
            <h1 class="font-headline-lg-mobile text-headline-lg-mobile font-bold tracking-tight text-primary">{{ $title }}</h1>
        </div>
        <div class="flex items-center gap-1">
            @if($showNotif)
                <button class="flex items-center justify-center w-10 h-10 rounded-full active:bg-surface-container transition-colors"
                        aria-label="Notifications">
                    <span class="material-symbols-outlined text-primary">notifications</span>
                </button>
            @endif
            {{ $actions ?? '' }}
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pb-[100px] px-margin-mobile pt-4">
        <div class="max-w-md mx-auto">
            {{ $slot }}
        </div>
    </main>

    <!-- Bottom Tab Bar -->
    <nav class="fixed bottom-0 w-full z-50 h-[80px] bg-surface-container border-t border-white/10 flex justify-around items-center px-4 pb-safe">
        <a href="{{ route('collector.route') }}"
           class="flex flex-col items-center justify-center gap-0.5 px-4 py-1.5 rounded-xl transition-all active:scale-90
                  {{ $activeTab === 'route' ? 'bg-primary-fixed text-on-primary-fixed' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined" @if($activeTab === 'route') style="font-variation-settings: 'FILL' 1" @endif>directions_run</span>
            <span class="text-[10px] font-medium">Route</span>
        </a>
        <a href="{{ route('collector.scan') }}"
           class="flex flex-col items-center justify-center gap-0.5 px-4 py-1.5 rounded-xl transition-all active:scale-90
                  {{ $activeTab === 'scan' ? 'bg-primary-fixed text-on-primary-fixed' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined">qr_code_scanner</span>
            <span class="text-[10px] font-medium">Scan</span>
        </a>
        <a href="{{ route('collector.summary') }}"
           class="flex flex-col items-center justify-center gap-0.5 px-4 py-1.5 rounded-xl transition-all active:scale-90
                  {{ $activeTab === 'summary' ? 'bg-primary-fixed text-on-primary-fixed' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined">query_stats</span>
            <span class="text-[10px] font-medium">Summary</span>
        </a>
        <a href="#"
           class="flex flex-col items-center justify-center gap-0.5 px-4 py-1.5 rounded-xl transition-all active:scale-90
                  {{ $activeTab === 'profile' ? 'bg-primary-fixed text-on-primary-fixed' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined">person</span>
            <span class="text-[10px] font-medium">Profile</span>
        </a>
    </nav>

    @livewireScripts
</body>
</html>
