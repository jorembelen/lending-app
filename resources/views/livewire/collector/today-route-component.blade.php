<div>
    <!-- Search -->
    <div class="mt-stack-sm mb-stack-md">
        <div class="relative">
            <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search borrowers or IDs..."
                class="w-full h-touch-target-min pl-12 pr-4 bg-surface-container rounded-xl border-none text-on-surface focus:ring-2 focus:ring-primary-fixed placeholder:text-on-surface-variant/50 outline-none" />
        </div>
    </div>

    <!-- Summary Strip -->
    <section class="bg-surface-container-low rounded-xl p-5 mb-stack-md border border-white/5">
        <div class="flex justify-between items-end mb-4">
            <div>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Collected
                </p>
                <p class="font-headline-md text-headline-md text-primary-fixed">
                    ₱{{ number_format($this->summary['collected'], 2) }}</p>
            </div>
            <div class="text-right">
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Target</p>
                <p class="font-headline-md text-headline-md text-white">₱{{ number_format($this->summary['total'], 2) }}
                </p>
            </div>
        </div>

        <x-ui.progress-bar :percent="$this->summary['percent']" :label="$this->summary['percent'] . '% COMPLETED'" :sublabel="$this->summary['remaining'] . ' STOPS REMAINING'" />
    </section>

    <!-- Filter Chips -->
    <div class="flex gap-2 mb-stack-md overflow-x-auto custom-scrollbar pb-1">
        @php
            $filters = [
                'all'            => 'All',
                'pending'        => 'Pending',
                'partially_paid' => 'Partial',
                'missed'         => 'Missed',
                'paid'           => 'Paid',
            ];
        @endphp
        @foreach ($filters as $value => $label)
            @php $count = $value === 'all' ? $this->routeItems->count() : $this->routeItems->where('status', $value)->count(); @endphp
            <button wire:click="setFilter('{{ $value }}')"
                class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-colors flex items-center gap-2
                       {{ $filter === $value ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-surface-container border border-white/10 text-on-surface' }}">
                {{ $label }}
                <span class="text-[11px] font-bold {{ $filter === $value ? 'text-on-primary-fixed/70' : 'text-on-surface-variant' }}">{{ $count }}</span>
            </button>
        @endforeach
    </div>

    <!-- Borrower List -->
    <div class="space-y-stack-sm" wire:loading.class="opacity-60">
        @forelse($this->borrowers as $borrower)
            <x-data.borrower-list-row :borrower="$borrower" :href="$borrower['href']"
                action="{{ $borrower['status'] === 'paid' ? 'details' : 'collect' }}" />
        @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <span class="material-symbols-outlined text-on-surface-variant/40 mb-3"
                    style="font-size: 48px;">route</span>
                <p class="font-headline-md text-headline-md text-on-surface-variant/60">No stops found</p>
                <p class="font-label-sm text-label-sm text-on-surface-variant/40 mt-1">
                    {{ $search ? 'Try a different search term' : 'Your route is clear for today' }}
                </p>
            </div>
        @endforelse
    </div>

    <!-- Map FAB — opens today's stops in Google Maps -->
    <a href="{{ $this->mapUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       title="View today's route on Google Maps"
       class="fixed bottom-[96px] right-4 w-14 h-14 bg-primary-fixed text-on-primary-fixed rounded-full shadow-2xl shadow-primary-fixed/30 flex items-center justify-center active:scale-95 transition-transform z-40">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">map</span>
    </a>
</div>
