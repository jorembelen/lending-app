<div class="space-y-stack-md pb-8">

    @php $user = auth()->user(); @endphp

    <!-- Profile Card -->
    <section class="bg-surface-container border border-white/10 rounded-xl p-6 flex flex-col items-center text-center gap-3">
        <div class="w-20 h-20 rounded-full bg-primary-fixed/10 border-2 border-primary-fixed/30 flex items-center justify-center">
            <span class="text-3xl font-bold text-primary-fixed select-none">
                {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
            </span>
        </div>
        <div>
            <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-on-surface">{{ $user->name }}</h2>
            <p class="font-label-sm text-label-sm text-on-surface-variant">{{ $user->email }}</p>
        </div>
        <x-ui.badge variant="neutral">COLLECTOR</x-ui.badge>
    </section>

    <!-- Today's Performance -->
    <section class="grid grid-cols-2 gap-3">
        <div class="bg-surface-container p-5 rounded-xl border border-white/10 flex flex-col gap-2">
            <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Collected Today</span>
            <span class="font-headline-md text-headline-md text-primary-fixed leading-none tabular-nums break-all">
                ₱{{ number_format($this->todayStats['collected'], 2) }}
            </span>
        </div>
        <div class="bg-surface-container p-5 rounded-xl border border-white/10 flex flex-col gap-2">
            <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Payments</span>
            <span class="font-headline-md text-headline-md text-primary leading-none tabular-nums">
                {{ $this->todayStats['count'] }}
            </span>
        </div>
    </section>

    <!-- Account Info -->
    <section class="bg-surface-container border border-white/10 rounded-xl overflow-hidden">
        <h3 class="px-5 pt-4 pb-3 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider border-b border-white/10">ACCOUNT INFORMATION</h3>
        <div class="divide-y divide-white/5">
            @foreach([
                ['person', 'Full Name', $user->name     ?? '—'],
                ['badge',  'Username',  $user->username  ?? '—'],
                ['mail',   'Email',     $user->email     ?? '—'],
                ['shield_person', 'Role', strtoupper($user->getRoleNames()->first() ?? 'Collector')],
            ] as [$icon, $label, $value])
            <div class="flex items-center gap-4 px-5 py-4">
                <span class="material-symbols-outlined text-on-surface-variant text-[20px]">{{ $icon }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">{{ $label }}</p>
                    <p class="text-[14px] font-medium text-on-surface truncate">{{ $value }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Quick Links -->
    <section class="bg-surface-container border border-white/10 rounded-xl overflow-hidden">
        <h3 class="px-5 pt-4 pb-3 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider border-b border-white/10">SHORTCUTS</h3>
        <div class="divide-y divide-white/5">
            <a href="{{ route('collector.route') }}" class="flex items-center gap-4 px-5 py-4 active:bg-surface-variant transition-colors">
                <span class="material-symbols-outlined text-on-surface-variant text-[20px]">directions_run</span>
                <p class="flex-1 text-[14px] font-medium text-on-surface">Today's Route</p>
                <span class="material-symbols-outlined text-on-surface-variant text-[18px]">chevron_right</span>
            </a>
            <a href="{{ route('collector.summary') }}" class="flex items-center gap-4 px-5 py-4 active:bg-surface-variant transition-colors">
                <span class="material-symbols-outlined text-on-surface-variant text-[20px]">query_stats</span>
                <p class="flex-1 text-[14px] font-medium text-on-surface">Daily Summary</p>
                <span class="material-symbols-outlined text-on-surface-variant text-[18px]">chevron_right</span>
            </a>
        </div>
    </section>

    <!-- Sign Out -->
    <x-ui.button
        variant="destructive"
        size="lg"
        icon="logout"
        wire:click="logout"
        wire:confirm="Are you sure you want to sign out?"
    >Sign Out</x-ui.button>

    <p class="text-center text-xs text-on-surface-variant pb-4">Voltage v1.0 • Collector Field App</p>

</div>
