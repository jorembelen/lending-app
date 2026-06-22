<div class="space-y-6 max-w-[1600px]" wire:poll.30s>

    <!-- Goal Progress -->
    <section class="bg-surface-container border border-outline-variant p-6 rounded-xl">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 mb-4">
            <div>
                <h2 class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-widest mb-1">Today's Goal Progress</h2>
                <div class="flex items-baseline gap-2">
                    <span class="text-[32px] font-bold text-primary leading-none">₱{{ number_format($this->goal['collected'], 2) }}</span>
                    <span class="text-secondary-fixed-dim font-body-md opacity-50">/ ₱{{ number_format($this->goal['target'], 2) }}</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-primary-fixed text-[32px] font-bold leading-none">{{ $this->goal['percent'] }}%</span>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest mt-1">Completion</p>
            </div>
        </div>

        <div class="h-3 w-full bg-surface-container-highest rounded-full overflow-hidden border border-outline-variant relative">
            <div class="h-full bg-primary-fixed transition-all duration-1000 ease-out" style="width: {{ $this->goal['percent'] }}%">
                <div class="absolute inset-y-0 right-0 w-16 bg-gradient-to-l from-white/30 to-transparent animate-pulse"></div>
            </div>
        </div>
    </section>

    <!-- View Toggle + Actions -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex bg-surface-container p-1 border border-outline-variant rounded-lg self-start">
            <button
                wire:click="setView('list')"
                class="px-4 py-1.5 font-label-sm text-label-sm rounded transition-all
                       {{ $view === 'list' ? 'bg-primary-fixed text-on-primary-fixed' : 'text-secondary-fixed-dim hover:text-primary' }}"
            >LIST VIEW</button>
            <button
                wire:click="setView('map')"
                class="px-4 py-1.5 font-label-sm text-label-sm rounded transition-all
                       {{ $view === 'map' ? 'bg-primary-fixed text-on-primary-fixed' : 'text-secondary-fixed-dim hover:text-primary' }}"
            >MAP VIEW</button>
        </div>

        <div class="flex gap-3 flex-wrap">
            <button class="flex items-center gap-2 px-4 py-2 border border-outline-variant bg-surface-container text-label-sm font-label-sm text-primary hover:bg-surface-container-highest transition-colors rounded-lg">
                <span class="material-symbols-outlined text-[18px]">filter_list</span>
                Filters
            </button>
            @if(Route::has('admin.loans.create'))
            <a href="{{ route('admin.loans.create') }}"
               class="flex items-center gap-2 px-4 py-2 bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm font-bold hover:brightness-110 transition-all rounded-lg">
                <span class="material-symbols-outlined text-[18px]">add</span>
                + New Loan
            </a>
            @endif
        </div>
    </div>

    <!-- Collector Table / Map -->
    @if($view === 'list')
    <div class="bg-surface-container border border-outline-variant rounded-xl overflow-hidden">
        <div class="bg-surface-container-high px-6 py-3 border-b border-outline-variant flex items-center">
            <div class="w-2 h-2 rounded-full bg-primary-fixed mr-3 animate-pulse"></div>
            <h3 class="font-label-sm text-label-sm text-primary uppercase tracking-widest">
                Active Collectors ({{ $this->activeCollectors->count() }})
            </h3>
        </div>

        @if($this->activeCollectors->isEmpty())
            <div class="px-6 py-12 text-center text-secondary-fixed-dim font-body-md">
                No active collectors today.
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant">
                        <th class="text-left py-4 px-6 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Collector</th>
                        <th class="text-left py-4 px-6 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider w-1/4">Route Progress</th>
                        <th class="text-right py-4 px-6 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Amount Collected</th>
                        <th class="text-left py-4 px-6 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Last Sync</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @foreach($this->activeCollectors as $collector)
                    <tr class="hover:bg-surface-container-high transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-fixed/20 text-primary-fixed flex items-center justify-center text-[11px] font-bold">
                                    {{ strtoupper(substr($collector['name'], 0, 2)) }}
                                </div>
                                <span class="font-label-md text-label-md text-on-surface">{{ $collector['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <x-ui.progress-bar :percent="$collector['percent']" class="flex-1" />
                                <span class="font-label-sm text-label-sm text-on-surface-variant whitespace-nowrap">
                                    {{ $collector['completed'] }}/{{ $collector['assigned'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right font-label-md text-label-md text-primary-fixed">
                            ₱{{ number_format($collector['collected'], 2) }}
                        </td>
                        <td class="px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim">
                            {{ $collector['last_sync'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @else
    <div class="bg-surface-container border border-outline-variant rounded-xl p-6 flex items-center justify-center" style="min-height: 400px;">
        <div class="text-center text-secondary-fixed-dim">
            <span class="material-symbols-outlined mb-3" style="font-size:48px;">map</span>
            <p class="font-body-md">Map view coming in v2 — use Reverb + real-time GPS events.</p>
        </div>
    </div>
    @endif

</div>
