<div class="space-y-stack-md pb-8">

    <!-- Profile Card -->
    @php $user = auth()->user(); @endphp
    <section class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl p-6 flex flex-col items-center text-center space-y-3">
        <div class="w-20 h-20 rounded-full bg-primary-fixed/10 border-2 border-primary-fixed/30 flex items-center justify-center">
            <span class="text-3xl font-bold text-primary-fixed select-none">
                {{ strtoupper(substr($user->name ?? 'B', 0, 1)) }}
            </span>
        </div>
        <div>
            <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-on-surface">{{ $user->name }}</h2>
            <p class="font-label-sm text-label-sm text-on-surface-variant">{{ $user->phone ?? $user->email }}</p>
        </div>
        <x-ui.badge variant="neutral">BORROWER</x-ui.badge>
    </section>

    <!-- Account Info -->
    <section class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl overflow-hidden">
        <h3 class="px-5 pt-4 pb-3 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider border-b border-[#2D2F39]">ACCOUNT INFORMATION</h3>
        <div class="divide-y divide-[#2D2F39]">
            @foreach([
                ['person', 'Full Name',    $user->name     ?? '—'],
                ['phone',  'Phone Number', $user->phone    ?? '—'],
                ['mail',   'Email',        $user->email    ?? '—'],
                ['badge',  'Borrower ID',  str_pad($user->id ?? 0, 6, '0', STR_PAD_LEFT)],
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

    <!-- Loyalty Badge -->
    <section class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl p-5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span class="material-symbols-outlined text-primary-fixed text-[32px]" style="font-variation-settings: 'FILL' 1;">military_tech</span>
            <div>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">LOYALTY TIER</p>
                <p class="font-bold text-on-surface">{{ $user->loyalty_tier ?? 'Bronze' }}</p>
            </div>
        </div>
        @if(Route::has('borrower.rewards'))
        <a href="{{ route('borrower.rewards') }}"
           class="flex items-center gap-1 text-xs font-bold text-primary-fixed px-3 py-2 rounded-lg bg-primary-fixed/10 hover:bg-primary-fixed/20 transition-colors">
            View <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
        </a>
        @endif
    </section>

    <!-- App Settings Placeholder -->
    <section class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl overflow-hidden">
        <h3 class="px-5 pt-4 pb-3 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider border-b border-[#2D2F39]">PREFERENCES</h3>
        <div class="divide-y divide-[#2D2F39]">
            <div class="flex items-center justify-between px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-on-surface-variant text-[20px]">notifications</span>
                    <p class="text-[14px] font-medium text-on-surface">Payment Reminders</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <span class="sr-only">Payment Reminders</span>
                    <input type="checkbox" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-outline-variant rounded-full peer peer-checked:bg-primary-fixed after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
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

    <p class="text-center text-xs text-on-surface-variant pb-4">Voltage v1.0 • Borrower Portal</p>

</div>
