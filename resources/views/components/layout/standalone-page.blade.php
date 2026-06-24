@props([
    'title'     => config('app.name'),
    'withQrLib' => false,
    'pwa'       => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @if($pwa)
        {{-- PWA: collector app shell (same scope/manifest as collector-shell) --}}
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}" />
        <meta name="theme-color" content="#131313" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}" />
    @endif

    <title>{{ $title }}</title>
    @php
        $entries = ['resources/css/tailwind.css', 'resources/js/app.js'];
        if ($withQrLib) { $entries[] = 'resources/js/qr-scanner.js'; }
        if ($pwa)       { $entries[] = 'resources/js/collector/index.js'; }
    @endphp
    @vite($entries)
    @livewireStyles
</head>
<body class="dark flex flex-col min-h-screen bg-background text-on-surface font-sans">
    {{ $slot }}
    @livewireScripts
    @stack('scripts')
</body>
</html>
