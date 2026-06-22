<div class="space-y-8 max-w-[1600px]">

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-surface-container border border-outline-variant p-6 rounded-xl flex flex-col justify-between">
            <div>
                <p class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider mb-2">Total Collected Today</p>
                <h2 class="text-[32px] font-bold text-primary tracking-tight leading-none">₱{{ number_format($this->kpis['todayCollected'], 2) }}</h2>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="{{ $this->kpis['vsYesterday'] >= 0 ? 'text-primary-fixed bg-on-primary-container/20' : 'text-error bg-error-container/20' }} px-2 py-0.5 text-[12px] font-bold rounded">
                    {{ $this->kpis['vsYesterday'] >= 0 ? '+' : '' }}{{ $this->kpis['vsYesterday'] }}%
                </span>
                <span class="text-secondary-fixed-dim text-[12px]">vs yesterday</span>
            </div>
        </div>

        <div class="bg-surface-container border border-outline-variant p-6 rounded-xl flex flex-col justify-between">
            <div>
                <p class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider mb-2">Total Outstanding</p>
                <h2 class="text-[32px] font-bold text-primary tracking-tight leading-none">₱{{ number_format($this->kpis['outstanding'] / 1000000, 2) }}M</h2>
            </div>
            <div class="mt-4">
                <x-ui.progress-bar :percent="65" height="h-1" />
            </div>
        </div>

        <div class="bg-surface-container border border-outline-variant p-6 rounded-xl flex flex-col justify-between">
            <div>
                <p class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider mb-2">Active Loans</p>
                <h2 class="text-[32px] font-bold text-primary tracking-tight leading-none">{{ number_format($this->kpis['activeLoans']) }}</h2>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary-fixed text-[16px]">trending_up</span>
                <span class="text-secondary-fixed-dim text-[12px]">Steady growth</span>
            </div>
        </div>

        <div class="bg-surface-container border border-outline-variant p-6 rounded-xl flex flex-col justify-between">
            <div>
                <p class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider mb-2">Arrears Rate</p>
                <h2 class="text-[32px] font-bold text-error tracking-tight leading-none">{{ $this->kpis['arrearsRate'] }}%</h2>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="text-error bg-error-container/20 px-2 py-0.5 text-[12px] font-bold rounded">Overdue loans</span>
            </div>
        </div>
    </div>

    <!-- Collections Trend Chart -->
    <section class="bg-surface-container border border-outline-variant p-6 lg:p-8 rounded-xl">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 mb-8">
            <div>
                <h3 class="font-headline-md text-headline-md text-primary mb-1">Collections Trend</h3>
                <p class="text-secondary-fixed-dim font-body-md">Daily volume overview</p>
            </div>
            <div class="flex gap-2">
                @foreach(['wtd' => 'WTD', 'mtd' => 'MTD', 'ytd' => 'YTD'] as $period => $label)
                <button
                    wire:click="setChartPeriod('{{ $period }}')"
                    class="px-4 py-1.5 border text-label-sm font-label-sm transition-colors rounded
                           {{ $chartPeriod === $period ? 'border-primary-fixed bg-surface-container-high text-primary-fixed' : 'border-outline-variant text-secondary-fixed-dim hover:bg-surface-container-highest' }}"
                >{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <!-- SVG Line Chart -->
        <div class="w-full overflow-hidden" style="height: 200px;">
            <svg class="w-full h-full overflow-visible" viewBox="0 0 1000 300" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="chart-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#c3f400;stop-opacity:0.3"/>
                        <stop offset="100%" style="stop-color:#c3f400;stop-opacity:0"/>
                    </linearGradient>
                </defs>
                <line x1="0" y1="75"  x2="1000" y2="75"  stroke="#2D2D2D" stroke-width="1"/>
                <line x1="0" y1="150" x2="1000" y2="150" stroke="#2D2D2D" stroke-width="1"/>
                <line x1="0" y1="225" x2="1000" y2="225" stroke="#2D2D2D" stroke-width="1"/>
                <line x1="0" y1="300" x2="1000" y2="300" stroke="#2D2D2D" stroke-width="1"/>
                <path d="M0,300 L0,220 C100,240 200,100 300,150 C400,200 500,50 600,120 C700,180 800,40 900,80 L1000,60 L1000,300 Z"
                      fill="url(#chart-gradient)"/>
                <path d="M0,220 C100,240 200,100 300,150 C400,200 500,50 600,120 C700,180 800,40 900,80 L1000,60"
                      fill="none" stroke="#c3f400" stroke-width="3" stroke-linecap="round"/>
                <circle cx="300" cy="150" r="6" fill="#c3f400" stroke="#131313" stroke-width="2"/>
                <circle cx="600" cy="120" r="6" fill="#c3f400" stroke="#131313" stroke-width="2"/>
                <circle cx="900" cy="80"  r="6" fill="#c3f400" stroke="#131313" stroke-width="2"/>
            </svg>
        </div>
        <div class="flex justify-between mt-2">
            <span class="font-label-sm text-label-sm text-secondary-fixed-dim">Start</span>
            <span class="font-label-sm text-label-sm text-secondary-fixed-dim">Mid</span>
            <span class="font-label-sm text-label-sm text-primary-fixed">Today</span>
        </div>
    </section>

    <!-- Collector Performance Table -->
    <section class="bg-surface-container border border-outline-variant rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center">
            <h3 class="font-headline-md text-headline-md text-primary">Collector Performance Today</h3>
            @if(Route::has('admin.collections'))
            <a href="{{ route('admin.collections') }}" class="font-label-sm text-label-sm text-primary-fixed hover:underline">View Monitor</a>
            @endif
        </div>

        @if($this->collectorPerformance->isEmpty())
            <div class="px-6 py-12 text-center text-secondary-fixed-dim font-body-md">No collections recorded today yet.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Collector</th>
                        <th class="px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider text-right">Collected Today</th>
                        <th class="px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider"># Collections</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @foreach($this->collectorPerformance as $index => $row)
                    <tr class="hover:bg-surface-container-high transition-colors">
                        <td class="px-6 py-4 font-label-md text-label-md text-primary">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-primary-fixed/20 text-primary-fixed rounded-full flex items-center justify-center text-[11px] font-bold">
                                    {{ strtoupper(substr($row->collector?->name ?? 'U', 0, 2)) }}
                                </div>
                                <span class="font-label-md text-label-md text-on-surface">{{ $row->collector?->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right font-label-md text-label-md text-primary-fixed">₱{{ number_format($row->total, 2) }}</td>
                        <td class="px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim">{{ $row->collections }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>

</div>
