<div class="space-y-stack-md pb-8">

    <!-- Hero Points Card -->
    @php
        $borrower = $this->borrower;
        $points   = $borrower->loyalty_points ?? 0;
        $streak   = $borrower->payment_streak ?? 0;
        $currentTier = $this->tiers->last(fn ($t) => $t->min_points <= $points);
        $nextTier    = $this->tiers->first(fn ($t) => $t->min_points > $points);
        $progress    = $nextTier ? min(100, ($points / $nextTier->min_points) * 100) : 100;
    @endphp

    <section class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl p-6 text-center space-y-4 relative overflow-hidden">
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-secondary-container/10 blur-3xl rounded-full pointer-events-none"></div>

        <div class="flex flex-col items-center">
            <span class="material-symbols-outlined text-[52px] mb-2" style="color:#c3f400; font-variation-settings: 'FILL' 1;">military_tech</span>
            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">YOUR TIER</p>
            <h1 class="font-display-lg text-display-lg text-primary-fixed leading-tight">{{ $currentTier?->name ?? 'BRONZE' }}</h1>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="bg-surface-container-low rounded-xl p-3 flex flex-col items-center">
                <span class="text-2xl font-bold text-primary">{{ number_format($points) }}</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Points</span>
            </div>
            <div class="bg-surface-container-low rounded-xl p-3 flex flex-col items-center">
                <span class="text-2xl font-bold text-secondary-container">{{ $streak }}</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Day Streak</span>
            </div>
        </div>

        @if($nextTier)
        <div class="space-y-2">
            <div class="flex justify-between items-center text-xs">
                <span class="text-on-surface-variant uppercase font-bold">{{ $currentTier?->name ?? 'BRONZE' }}</span>
                <span class="text-on-surface-variant uppercase font-bold">{{ $nextTier->name }}</span>
            </div>
            <x-ui.progress-bar :percent="$progress" />
            <p class="text-xs text-on-surface-variant">
                <span class="text-primary font-bold">{{ number_format($nextTier->min_points - $points) }}</span> points to <span class="font-bold">{{ $nextTier->name }}</span>
            </p>
        </div>
        @else
        <div class="bg-primary-fixed/10 border border-primary-fixed/20 rounded-xl p-3">
            <p class="text-primary-fixed font-bold text-sm">You've reached the highest tier!</p>
        </div>
        @endif
    </section>

    <!-- Tiers Breakdown -->
    <section class="space-y-3">
        <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider px-1">ALL TIERS</h3>
        @foreach($this->tiers as $tier)
        @php $isActive = $tier->id === ($currentTier?->id); @endphp
        <div class="bg-[#1A1C24] border rounded-xl p-4 flex items-center gap-4 transition-all
            {{ $isActive ? 'border-primary-fixed/40 ring-1 ring-primary-fixed/20' : 'border-[#2D2F39]' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center
                {{ $isActive ? 'bg-primary-fixed/20' : 'bg-surface-container-low' }}">
                <span class="material-symbols-outlined {{ $isActive ? 'text-primary-fixed' : 'text-on-surface-variant' }}"
                    style="font-variation-settings: 'FILL' {{ $isActive ? '1' : '0' }};">military_tech</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <p class="font-bold text-on-surface">{{ $tier->name }}</p>
                    @if($isActive)
                    <span class="text-[10px] font-bold bg-primary-fixed text-on-primary-fixed px-2 py-0.5 rounded-full uppercase">CURRENT</span>
                    @endif
                </div>
                <p class="text-xs text-on-surface-variant">{{ number_format($tier->min_points) }} pts minimum</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-primary">{{ $tier->rebate_percent }}%</p>
                <p class="text-[10px] text-on-surface-variant uppercase">Rebate</p>
            </div>
        </div>
        @endforeach
    </section>

    <!-- How it Works -->
    <section class="bg-surface-container-low rounded-xl p-4 border border-outline-variant space-y-3">
        <h4 class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">info</span>
            How It Works
        </h4>
        <div class="space-y-2">
            @foreach([
                ['bolt', 'Earn 10 points for every ₱100 paid on time'],
                ['military_tech', 'Maintain your streak to unlock higher tiers'],
                ['redeem', 'Rebates are automatically applied to your account'],
            ] as [$icon, $text])
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary-fixed/70 text-[18px] mt-0.5" style="font-variation-settings: 'FILL' 1;">{{ $icon }}</span>
                <p class="text-sm text-on-surface-variant">{{ $text }}</p>
            </div>
            @endforeach
        </div>
    </section>

</div>
