<div class="max-w-4xl space-y-6">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">
        <div>
            <h2 class="font-headline-md text-headline-md text-primary mb-1">Loyalty & Rebates</h2>
            <p class="text-secondary-fixed-dim font-body-md">Manage borrower tier thresholds and rebate percentages.</p>
        </div>
        @if(Route::has('admin.rebates.pending'))
        <a href="{{ route('admin.rebates.pending') }}"
           class="inline-flex items-center gap-2 px-5 py-2 bg-surface-container border border-outline-variant rounded-lg font-label-md text-label-md text-primary hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined text-[18px]">pending_actions</span>
            Pending Approvals
        </a>
        @endif
    </div>

    <!-- Tier Cards -->
    @if($this->tiers->isEmpty())
    <x-ui.card>
        <div class="py-12 text-center">
            <span class="material-symbols-outlined text-on-surface-variant/40 mb-3" style="font-size:48px;">military_tech</span>
            <p class="font-headline-md text-headline-md text-on-surface-variant/60">No tiers configured yet</p>
            <p class="font-body-md text-secondary-fixed-dim mt-2">Add loyalty tiers to start rewarding on-time borrowers.</p>
        </div>
    </x-ui.card>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($this->tiers as $tier)
        <x-ui.card :glow="true">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-primary-fixed/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary-fixed" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-primary leading-tight">{{ $tier->name }}</h3>
                    <p class="font-label-sm text-label-sm text-secondary-fixed-dim">{{ number_format($tier->min_points) }}+ pts</p>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between font-label-sm text-label-sm">
                    <span class="text-secondary-fixed-dim">Interest Discount</span>
                    <span class="text-primary-fixed font-bold">{{ $tier->interest_discount ?? 0 }}%</span>
                </div>
                <div class="flex justify-between font-label-sm text-label-sm">
                    <span class="text-secondary-fixed-dim">Rebate Rate</span>
                    <span class="text-primary-fixed font-bold">{{ $tier->rebate_rate ?? 0 }}%</span>
                </div>
            </div>
        </x-ui.card>
        @endforeach
    </div>
    @endif

    <x-ui.button variant="primary" size="md" icon="add" iconPosition="left">Add New Tier</x-ui.button>
</div>
