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
<body class="dark flex flex-col min-h-screen bg-background text-on-surface font-sans">

    <!-- Top App Bar -->
    <header class="fixed top-0 w-full z-50 bg-background flex items-center justify-between px-margin-mobile h-touch-target-min border-b border-white/5">
        <div class="flex items-center gap-3">
            @if($showBack)
                <a href="{{ $backHref ?? 'javascript:history.back()' }}" class="material-symbols-outlined text-primary active:scale-95 transition-transform">arrow_back</a>
            @endif
            <h1 class="font-headline-lg-mobile text-headline-lg-mobile font-bold tracking-tight text-primary">{{ $title }}</h1>
        </div>
        <div class="flex items-center gap-2">
            @if($showNotif)
                <button class="material-symbols-outlined text-primary p-2 active:scale-95 transition-transform">notifications</button>
            @endif
            {{ $actions ?? '' }}
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-[64px] pb-[100px] px-margin-mobile">
        {{ $slot }}
    </main>

    <!-- Bottom Tab Bar -->
    <nav class="fixed bottom-0 w-full z-50 h-[80px] bg-surface-container border-t border-white/10 flex justify-around items-center px-4 pb-safe">
        <a href="{{ route('collector.route') }}"
           class="flex flex-col items-center justify-center {{ $activeTab === 'route' ? 'bg-primary-fixed text-on-primary-fixed rounded-xl px-4 py-1' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined" style="{{ $activeTab === 'route' ? \"font-variation-settings: 'FILL' 1\" : '' }}">directions_run</span>
            <span class="font-label-sm text-label-sm">Route</span>
        </a>
        <a href="{{ route('collector.scan') }}"
           class="flex flex-col items-center justify-center {{ $activeTab === 'scan' ? 'bg-primary-fixed text-on-primary-fixed rounded-xl px-4 py-1' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined">qr_code_scanner</span>
            <span class="font-label-sm text-label-sm">Scan</span>
        </a>
        <a href="{{ route('collector.summary') }}"
           class="flex flex-col items-center justify-center {{ $activeTab === 'summary' ? 'bg-primary-fixed text-on-primary-fixed rounded-xl px-4 py-1' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined">query_stats</span>
            <span class="font-label-sm text-label-sm">Summary</span>
        </a>
        <a href="{{ route('collector.profile') }}"
           class="flex flex-col items-center justify-center {{ $activeTab === 'profile' ? 'bg-primary-fixed text-on-primary-fixed rounded-xl px-4 py-1' : 'text-on-surface-variant' }} active:scale-90 transition-all">
            <span class="material-symbols-outlined">person</span>
            <span class="font-label-sm text-label-sm">Profile</span>
        </a>
    </nav>

    @livewireScripts
</body>
</html>
