<div>
    @if(!$this->borrower)
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <span class="material-symbols-outlined text-on-surface-variant/40 mb-3" style="font-size:48px;">person_off</span>
            <p class="font-headline-md text-headline-md text-on-surface-variant/60">Borrower not found</p>
        </div>
    @else

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-6 text-label-sm font-label-sm">
        @if(Route::has('admin.borrowers'))
        <a href="{{ route('admin.borrowers') }}" class="text-secondary-fixed-dim hover:text-primary-fixed transition-colors">Borrowers</a>
        <span class="text-outline-variant">/</span>
        @endif
        <span class="text-primary-fixed font-bold">{{ $this->borrower->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Profile + Loan Summary -->
        <div class="lg:col-span-1 space-y-6">
            <x-ui.card>
                <div class="flex flex-col items-center text-center gap-3">
                    <div class="w-20 h-20 rounded-xl bg-surface-bright border border-outline flex items-center justify-center overflow-hidden">
                        @if($this->borrower->avatar)
                            <img src="{{ $this->borrower->avatar }}" alt="{{ $this->borrower->name }}" class="w-full h-full object-cover" />
                        @else
                            <span class="material-symbols-outlined text-on-surface-variant" style="font-size:40px;">person</span>
                        @endif
                    </div>
                    <div>
                        <h2 class="font-headline-md text-headline-md text-primary">{{ $this->borrower->name }}</h2>
                        <p class="font-label-sm text-label-sm text-secondary-fixed-dim mt-1">{{ $this->borrower->borrower_id ?? '' }}</p>
                    </div>
                    @if($this->activeLoan)
                        <x-ui.badge :variant="$this->activeLoan->status">{{ ucfirst($this->activeLoan->status) }}</x-ui.badge>
                    @endif
                </div>

                <div class="mt-5 pt-5 border-t border-outline-variant space-y-3">
                    <div class="flex items-center gap-3 text-label-md font-label-md">
                        <span class="material-symbols-outlined text-[18px] text-secondary-fixed-dim">email</span>
                        <span class="text-on-surface">{{ $this->borrower->email ?? '—' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-label-md font-label-md">
                        <span class="material-symbols-outlined text-[18px] text-secondary-fixed-dim">phone</span>
                        <span class="text-on-surface">{{ $this->borrower->phone ?? '—' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-label-md font-label-md">
                        <span class="material-symbols-outlined text-[18px] text-secondary-fixed-dim">location_on</span>
                        <span class="text-on-surface">{{ $this->borrower->address ?? '—' }}</span>
                    </div>
                </div>
            </x-ui.card>

            @if(Route::has('admin.loans.create'))
            <a href="{{ route('admin.loans.create', ['borrower' => $borrowerId]) }}"
               class="w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl flex items-center justify-center gap-2 font-label-md text-label-md font-bold hover:brightness-110 transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Release New Loan
            </a>
            @endif
        </div>

        <!-- Right: Loan History + Schedule -->
        <div class="lg:col-span-2 space-y-6">
            @if($this->activeLoan)
            <x-ui.card>
                <h3 class="font-label-md text-label-md text-primary-fixed uppercase tracking-wider mb-4">Active Loan</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <x-data.kpi-card label="Principal"   :value="'₱' . number_format($this->activeLoan->principal, 2)" />
                    <x-data.kpi-card label="Amount Paid" :value="'₱' . number_format($this->activeLoan->amount_paid ?? 0, 2)" :highlight="true" />
                    <x-data.kpi-card label="Remaining"   :value="'₱' . number_format($this->activeLoan->remaining_balance ?? 0, 2)" />
                </div>
                @php $paidPct = $this->activeLoan->principal > 0 ? ($this->activeLoan->amount_paid / $this->activeLoan->principal * 100) : 0; @endphp
                <x-ui.progress-bar :percent="$paidPct" :label="round($paidPct) . '% paid'" class="mt-4" />
            </x-ui.card>
            @endif

            <x-ui.card>
                <h3 class="font-label-md text-label-md text-primary-fixed uppercase tracking-wider mb-4">All Loans</h3>
                @forelse($this->loans as $loan)
                <div class="flex items-center justify-between py-3 border-b border-outline-variant/30 last:border-0">
                    <div>
                        <p class="font-label-md text-label-md text-on-surface">{{ $loan->loan_id }}</p>
                        <p class="font-label-sm text-label-sm text-secondary-fixed-dim">
                            {{ $loan->created_at?->format('M d, Y') }} • ₱{{ number_format($loan->principal, 2) }}
                        </p>
                    </div>
                    <x-ui.badge :variant="$loan->status">{{ ucfirst($loan->status) }}</x-ui.badge>
                </div>
                @empty
                <p class="text-secondary-fixed-dim font-body-md text-center py-8">No loan history found.</p>
                @endforelse
            </x-ui.card>
        </div>
    </div>

    @endif
</div>
