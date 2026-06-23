<div class="flex flex-col min-h-screen">

    <!-- Top Bar -->
    <header class="fixed top-0 w-full z-50 bg-background flex items-center justify-between px-margin-mobile h-touch-target-min">
        <a href="{{ route('collector.route') }}" class="active:scale-95 transition-transform hover:bg-surface-variant p-2 rounded-full">
            <span class="material-symbols-outlined text-primary">arrow_back</span>
        </a>
        <h1 class="font-headline-md text-headline-md font-bold text-primary">Receipt</h1>
        <div class="w-10"></div>
    </header>

    <main class="flex-1 flex flex-col items-center pt-24 pb-32 px-margin-mobile">

        <!-- Success Visual -->
        <div class="mb-stack-lg flex flex-col items-center">
            <div class="w-24 h-24 rounded-full bg-primary-fixed flex items-center justify-center mb-6 shadow-[0_0_40px_rgba(195,244,0,0.3)]">
                <span class="material-symbols-outlined text-on-primary-fixed" style="font-size:48px; font-variation-settings: 'wght' 700;">check</span>
            </div>
            <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-center text-primary-fixed">Payment Successful</h2>
            @if($this->payment)
                <p class="font-body-md text-body-md text-on-surface-variant mt-2 text-center">
                    Transaction ID: #{{ $this->payment->id }}
                </p>
            @endif
        </div>

        <!-- Receipt Card -->
        @if($this->payment)
        <div class="w-full bg-surface-container border border-white/10 rounded-xl p-5 mb-stack-lg">
            <div class="flex flex-col gap-stack-sm">
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="font-label-md text-label-md text-on-surface-variant">Amount Collected</span>
                    <span class="font-headline-md text-headline-md text-primary-fixed">₱{{ number_format($this->payment->amount, 2) }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4 py-2">
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mb-1">New Balance</p>
                        <p class="font-body-lg text-body-lg text-primary font-semibold">
                            ₱{{ number_format($this->payment->loan?->remaining_balance ?? 0, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mb-1">Date Collected</p>
                        <p class="font-body-lg text-body-lg text-primary font-semibold">
                            {{ $this->payment->collected_at?->format('M d, Y') ?? today()->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                <div class="mt-2 p-3 bg-surface-container-high rounded-lg border border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-surface-bright flex items-center justify-center overflow-hidden">
                            @if($this->payment->loan?->borrower?->photo_path)
                                <img src="{{ asset('storage/' . $this->payment->loan->borrower->photo_path) }}" alt="" class="w-full h-full object-cover" />
                            @else
                                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">person</span>
                            @endif
                        </div>
                        <div>
                            <p class="font-label-md text-label-md text-primary">{{ $this->payment->loan?->borrower?->full_name ?? 'Borrower' }}</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">Loan #{{ str_pad($this->payment->loan_id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="w-full flex flex-col gap-4">
            <div class="grid grid-cols-2 gap-4">
                <x-ui.button variant="secondary" size="sm" icon="print" iconPosition="left">
                    Print Receipt
                </x-ui.button>
                <x-ui.button variant="secondary" size="sm" icon="share" iconPosition="left">
                    Share
                </x-ui.button>
            </div>
            <a href="{{ route('collector.route') }}"
               class="w-full h-14 bg-primary-fixed text-on-primary-fixed rounded-xl flex items-center justify-center gap-2 font-label-md text-label-md font-bold active:scale-[0.98] transition-all">
                <span class="material-symbols-outlined">directions_run</span>
                Back to Route
            </a>
        </div>
    </main>
</div>
