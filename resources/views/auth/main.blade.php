<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title')</title>
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}">
    @vite(['resources/css/tailwind.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('css')
</head>
<body class="min-h-screen bg-background text-on-surface font-sans flex items-center justify-center px-4 py-12">

    <!-- Ambient glow -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10" aria-hidden="true">
        <div class="absolute top-[-10%] left-[-5%] w-[40%] h-[50%] bg-primary-fixed/6 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[35%] h-[40%] bg-secondary-container/5 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-md">
        <!-- Brand -->
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-surface-container-low border border-white/10 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-primary-fixed" style="font-size:32px; font-variation-settings: 'FILL' 1;">bolt</span>
            </div>
            <h1 class="font-display-lg text-display-lg text-primary-fixed tracking-tight leading-none">{{ config('app.name') }}</h1>
            <p class="text-sm text-on-surface-variant mt-1">@yield('subtitle', 'Management Portal')</p>
        </div>

        <!-- Card -->
        <div class="bg-surface-container-low border border-outline-variant rounded-2xl overflow-hidden shadow-2xl">
            @yield('content')
        </div>

        <p class="text-center text-xs text-on-surface-variant mt-6">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>

    @livewireScripts
    @stack('js')
</body>
</html>
