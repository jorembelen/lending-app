@props([
    'title'     => config('app.name'),
    'withQrLib' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }}</title>
    @if($withQrLib)
        @vite(['resources/css/tailwind.css', 'resources/js/app.js', 'resources/js/qr-scanner.js'])
    @else
        @vite(['resources/css/tailwind.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="dark flex flex-col min-h-screen bg-background text-on-surface font-sans">
    {{ $slot }}
    @livewireScripts
    @stack('scripts')
</body>
</html>
