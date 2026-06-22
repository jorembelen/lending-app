@props(['title' => config('app.name')])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}">
    @vite(['resources/css/tailwind.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('css')
</head>
<body class="min-h-screen bg-background text-on-surface font-sans">

    <!-- Top bar -->
    <header class="h-14 border-b border-outline-variant bg-surface-container-low flex items-center justify-between px-6">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-fixed" style="font-variation-settings:'FILL' 1;">bolt</span>
            <span class="font-bold text-on-surface tracking-tight">{{ config('app.name') }}</span>
        </div>
        <div class="flex items-center gap-4">
            @auth
            <span class="text-sm text-on-surface-variant">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </button>
            </form>
            @endauth
        </div>
    </header>

    <!-- Content -->
    <main class="p-6">
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('js')
</body>
</html>
