<div class="max-w-4xl space-y-6">

    <div class="flex items-center gap-4 mb-2">
        @if(Route::has('admin.loyalty'))
        <a href="{{ route('admin.loyalty') }}" class="material-symbols-outlined text-secondary-fixed-dim hover:text-primary transition-colors">arrow_back</a>
        @endif
        <h2 class="font-headline-md text-headline-md text-primary">Pending Rebate Approvals</h2>
    </div>

    @if($this->pending->isEmpty())
    <x-ui.card>
        <div class="py-12 text-center">
            <span class="material-symbols-outlined text-primary-fixed mb-3" style="font-size:48px; font-variation-settings: 'FILL' 1;">check_circle</span>
            <p class="font-headline-md text-headline-md text-primary-fixed">All caught up!</p>
            <p class="font-body-md text-secondary-fixed-dim mt-2">No pending rebate requests at this time.</p>
        </div>
    </x-ui.card>
    @else
    <div class="space-y-4">
        @foreach($this->pending as $request)
        <x-ui.card>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-surface-bright border border-outline flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-on-surface-variant">person</span>
                    </div>
                    <div>
                        <h3 class="font-label-md text-label-md text-on-surface">{{ $request->borrower?->name ?? 'Unknown' }}</h3>
                        <p class="font-label-sm text-label-sm text-secondary-fixed-dim">
                            Rebate: ₱{{ number_format($request->amount ?? 0, 2) }} •
                            {{ $request->created_at?->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 self-end sm:self-auto">
                    <x-ui.button
                        variant="destructive"
                        size="sm"
                        wire:click="reject({{ $request->id }})"
                        wire:confirm="Reject this rebate request?"
                    >Reject</x-ui.button>
                    <x-ui.button
                        variant="primary"
                        size="sm"
                        icon="check"
                        wire:click="approve({{ $request->id }})"
                        wire:confirm="Approve this rebate request?"
                    >Approve</x-ui.button>
                </div>
            </div>
        </x-ui.card>
        @endforeach
    </div>
    @endif
</div>
