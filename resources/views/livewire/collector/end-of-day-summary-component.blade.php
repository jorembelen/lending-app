<div class="space-y-stack-lg">

    <!-- Stats Bento Grid -->
    <section class="grid grid-cols-2 gap-4">
        <div class="col-span-2 bg-surface-container p-5 rounded-xl border border-white/10 flex flex-col justify-between" style="min-height: 160px;">
            <span class="font-label-md text-on-surface-variant uppercase tracking-wider">Total Collected</span>
            <div class="flex items-baseline gap-2">
                <span class="text-primary-fixed font-bold" style="font-size: 42px; line-height: 1;">
                    ₱{{ number_format($this->summary['totalCollected'], 2) }}
                </span>
            </div>
            @if($this->summary['efficiency'] >= 100)
            <div class="flex items-center gap-2 text-primary-fixed font-label-sm text-label-sm">
                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                <span>Above daily target</span>
            </div>
            @endif
        </div>

        <div class="bg-surface-container p-5 rounded-xl border border-white/10 flex flex-col justify-between" style="min-height: 140px;">
            <span class="font-label-md text-on-surface-variant uppercase tracking-wider">Visits</span>
            <span class="font-headline-lg text-headline-lg text-primary leading-none">
                {{ $this->summary['completed'] }}/{{ $this->summary['assigned'] }}
            </span>
            <x-ui.progress-bar :percent="$this->summary['assigned'] > 0 ? ($this->summary['completed'] / $this->summary['assigned'] * 100) : 0" height="h-1.5" />
        </div>

        <div class="bg-surface-container p-5 rounded-xl border border-white/10 flex flex-col justify-between" style="min-height: 140px;">
            <span class="font-label-md text-on-surface-variant uppercase tracking-wider">Efficiency</span>
            <span class="font-headline-lg text-headline-lg text-primary-fixed leading-none">{{ $this->summary['efficiency'] }}%</span>
            <span class="font-label-sm text-label-sm text-on-surface-variant">
                {{ $this->summary['efficiency'] >= 90 ? 'High performance' : ($this->summary['efficiency'] >= 70 ? 'Good' : 'Needs improvement') }}
            </span>
        </div>
    </section>

    <!-- Missed Visits -->
    @if($this->missed->count())
    <section class="space-y-stack-sm">
        <div class="flex items-center justify-between">
            <h2 class="font-headline-md text-headline-md text-primary">Missed Visits ({{ $this->missed->count() }})</h2>
            <x-ui.badge variant="danger">Attention Required</x-ui.badge>
        </div>

        <div class="bg-surface-container rounded-xl border border-white/10 overflow-hidden divide-y divide-white/5">
            @foreach($this->missed as $item)
            @php $borrower = $item->loan?->borrower; @endphp
            <a href="{{ route('collector.payment', $item->loan?->borrower_id ?? $item->loan_id) }}"
               class="p-4 flex items-center justify-between active:bg-surface-variant transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center">
                        <span class="material-symbols-outlined text-on-surface-variant">person</span>
                    </div>
                    <div>
                        <p class="font-label-md text-label-md text-primary">{{ $borrower?->full_name ?? 'Unknown' }}</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">
                            {{ $borrower?->borrower_code ?? ('#' . $item->loan_id) }} • {{ $borrower?->address ?? '' }}
                        </p>
                    </div>
                </div>
                <span class="font-headline-md text-headline-md text-error">
                    ₱{{ number_format((float) $item->amount_due - (float) $item->amount_paid, 2) }}
                </span>
            </a>
            @endforeach
        </div>
    </section>
    @else
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <span class="material-symbols-outlined text-primary-fixed mb-3" style="font-size:48px; font-variation-settings: 'FILL' 1;">check_circle</span>
        <p class="font-headline-md text-headline-md text-primary-fixed">All visits completed!</p>
        <p class="font-body-md text-on-surface-variant mt-1">Great work today.</p>
    </div>
    @endif

    <!-- Submit Day Button -->
    <x-ui.button variant="primary" size="lg" icon="send">
        Submit Day Report
    </x-ui.button>

</div>
