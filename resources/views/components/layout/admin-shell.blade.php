@props([
    'title' => 'Admin',
    'activeNav' => 'dashboard',
])

@php
    $navItems = [
        'dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard'],
        'borrowers' => ['icon' => 'group', 'label' => 'Borrowers', 'route' => 'admin.borrowers'],
        'loans' => ['icon' => 'payments', 'label' => 'Loans', 'route' => 'admin.loans'],
        'collections' => ['icon' => 'account_balance_wallet', 'label' => 'Collections', 'route' => 'admin.collections'],
        'loyalty' => ['icon' => 'card_giftcard', 'label' => 'Loyalty & Rebates', 'route' => 'admin.loyalty'],
        'reports' => ['icon' => 'assessment', 'label' => 'Reports', 'route' => 'admin.reports'],
        'settings' => ['icon' => 'settings', 'label' => 'Settings', 'route' => 'admin.settings'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }} — {{ config('app.name') }}</title>
    @vite(['resources/css/tailwind.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="dark flex min-h-screen bg-background text-on-surface font-sans">

    <!-- Sidebar -->
    <aside
        class="fixed left-0 top-0 h-full w-[280px] bg-surface-container border-r border-outline-variant flex flex-col py-4 z-50 hidden lg:flex">
        <div class="px-6 mb-8 flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-fixed rounded flex items-center justify-center">
                <span class="material-symbols-outlined text-on-primary-fixed font-bold"
                    style="font-variation-settings: 'FILL' 1;">bolt</span>
            </div>
            <div>
                <h1 class="font-headline-md text-headline-md font-bold text-primary leading-tight">LendingPro</h1>
                <p class="text-[12px] text-secondary-fixed-dim">Enterprise Admin</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1">
            @foreach ($navItems as $key => $item)
                @if (Route::has($item['route']))
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center px-6 py-3 transition-colors duration-200
                          {{ $activeNav === $key
                              ? 'border-l-2 border-primary-fixed bg-surface-container-high text-primary font-bold'
                              : 'text-secondary-fixed-dim hover:text-primary-fixed hover:bg-surface-container-highest' }}">
                        <span class="material-symbols-outlined mr-3">{{ $item['icon'] }}</span>
                        <span class="font-body-md text-body-md">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="mt-auto px-6 space-y-4">
            <div class="pt-4 border-t border-outline-variant">
                <a href="#"
                    class="flex items-center py-2 text-secondary-fixed-dim hover:text-primary transition-colors">
                    <span class="material-symbols-outlined mr-3">account_circle</span>
                    <span class="font-body-md text-body-md">{{ Auth::user()->name ?? 'Profile' }}</span>
                </a>
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('admin-logout').submit();"
                    class="flex items-center py-2 text-error hover:text-red-400 transition-colors">
                    <span class="material-symbols-outlined mr-3">logout</span>
                    <span class="font-body-md text-body-md">Logout</span>
                </a>
                <form id="admin-logout" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:ml-[280px] w-full min-h-screen flex flex-col">

        <!-- Top Nav Bar -->
        <header
            class="h-[64px] bg-surface border-b border-outline-variant flex justify-between items-center px-4 lg:px-8 sticky top-0 z-40">
            <!-- Mobile menu toggle -->
            <button class="lg:hidden material-symbols-outlined text-on-surface-variant" x-data
                @click="$dispatch('toggle-sidebar')">menu</button>

            <div
                class="hidden lg:flex items-center bg-surface-container-low border border-outline-variant px-3 py-1.5 w-96 group focus-within:border-primary-fixed transition-colors rounded">
                <span class="material-symbols-outlined text-secondary-fixed-dim mr-2 text-[20px]">search</span>
                <input
                    class="bg-transparent border-none focus:ring-0 text-body-md w-full placeholder:text-secondary-fixed-dim/50 outline-none"
                    placeholder="Search loans, borrowers..." type="text" />
            </div>

            <div class="flex items-center gap-4">
                @if (Route::has('admin.loans.create'))
                    <a href="{{ route('admin.loans.create') }}"
                        class="bg-primary-fixed text-on-primary-fixed px-4 py-2 font-bold text-label-md flex items-center gap-2 hover:brightness-110 active:opacity-80 transition-all rounded-lg text-sm">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        NEW LOAN
                    </a>
                @endif
                <button class="relative text-secondary-fixed-dim hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-primary-fixed rounded-full"></span>
                </button>
                <div class="flex items-center gap-2 pl-4 border-l border-outline-variant">
                    <div
                        class="w-8 h-8 rounded-full bg-surface-container-high border border-outline flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">person</span>
                    </div>
                    <span class="text-label-md text-primary hidden sm:block">{{ Auth::user()->name ?? 'Admin' }}</span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>

</html>
