<div>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 mb-8">
        <div>
            <h2 class="font-headline-md text-headline-md text-primary mb-1">Borrowers</h2>
            <p class="text-secondary-fixed-dim font-body-md">Manage your active client portfolio and loyalty profiles.</p>
        </div>
        @if(Route::has('admin.borrowers.create'))
        <a href="{{ route('admin.borrowers.create') }}"
           class="bg-primary-fixed text-on-primary-fixed px-6 py-3 font-bold flex items-center gap-2 rounded-lg active:opacity-80 transition-all hover:brightness-110 whitespace-nowrap self-start sm:self-auto">
            <span class="material-symbols-outlined">person_add</span>
            + New Borrower
        </a>
        @endif
    </div>

    <!-- KPI Stats Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-data.kpi-card label="Total Borrowers" :value="$this->stats['total']" icon="group" />
        <x-data.kpi-card label="Active Loans"    :value="$this->stats['active']"    icon="trending_up" :highlight="true" />
        <x-data.kpi-card label="Overdue"          :value="$this->stats['overdue']"   icon="warning" />
        <x-data.kpi-card label="Completed"        :value="$this->stats['completed']" icon="check_circle" />
    </div>

    <!-- Filters + Search -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary-fixed-dim">search</span>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Search borrowers by name, email or ID..."
                class="w-full bg-surface-container-low border border-outline-variant rounded-lg py-2 pl-10 pr-4 text-body-md focus:outline-none focus:border-primary-fixed transition-colors text-on-surface"
            />
        </div>
        <div class="flex gap-2 overflow-x-auto custom-scrollbar pb-1">
            @foreach(['all' => 'All', 'active' => 'Active', 'overdue' => 'Overdue', 'completed' => 'Completed'] as $value => $label)
            <button
                wire:click="$set('filter', '{{ $value }}')"
                class="px-4 py-2 rounded-lg font-label-md text-label-md whitespace-nowrap transition-colors
                       {{ $filter === $value ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-surface-container border border-outline-variant text-secondary-fixed-dim hover:text-primary-fixed' }}"
            >{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <!-- Borrowers Table (desktop) / Cards (mobile) -->
    <div class="hidden md:block bg-surface-container rounded-xl border border-outline-variant overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-outline-variant">
                    <th class="text-left px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Borrower</th>
                    <th class="text-left px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Loan ID</th>
                    <th class="text-left px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Balance</th>
                    <th class="text-left px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse($borrowers as $borrower)
                @php $loan = $borrower->loans->first(); @endphp
                <tr class="hover:bg-surface-container-high transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-surface-bright border border-outline flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($borrower->avatar)
                                    <img src="{{ $borrower->avatar }}" alt="{{ $borrower->name }}" class="w-full h-full object-cover" />
                                @else
                                    <span class="material-symbols-outlined text-[16px] text-on-surface-variant">person</span>
                                @endif
                            </div>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface">{{ $borrower->name }}</p>
                                <p class="font-label-sm text-label-sm text-secondary-fixed-dim">{{ $borrower->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-label-sm text-label-sm text-secondary-fixed-dim">{{ $loan?->loan_id ?? '—' }}</td>
                    <td class="px-6 py-4 font-label-md text-label-md text-on-surface">
                        {{ $loan ? '₱' . number_format($loan->remaining_balance ?? 0, 2) : '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($loan)
                            <x-ui.badge :variant="$loan->status">{{ ucfirst($loan->status) }}</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">No Loan</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if(Route::has('admin.borrowers.show'))
                        <a href="{{ route('admin.borrowers.show', $borrower) }}"
                           class="font-label-sm text-label-sm text-primary-fixed hover:underline">View</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <span class="material-symbols-outlined text-on-surface-variant/40 mb-2" style="font-size:40px;">group_off</span>
                        <p class="font-body-md text-on-surface-variant/60 mt-2">No borrowers found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile card list -->
    <div class="md:hidden space-y-3">
        @forelse($borrowers as $borrower)
        @php $loan = $borrower->loans->first(); @endphp
        <div class="bg-surface-container rounded-xl border border-outline-variant p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-surface-bright border border-outline flex items-center justify-center flex-shrink-0 overflow-hidden">
                @if($borrower->avatar)
                    <img src="{{ $borrower->avatar }}" alt="{{ $borrower->name }}" class="w-full h-full object-cover" />
                @else
                    <span class="material-symbols-outlined text-on-surface-variant">person</span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-on-surface truncate">{{ $borrower->name }}</p>
                <p class="font-label-sm text-label-sm text-secondary-fixed-dim">{{ $loan?->loan_id ?? 'No loan' }}</p>
            </div>
            <div class="flex flex-col items-end gap-1">
                @if($loan)
                    <x-ui.badge :variant="$loan->status">{{ ucfirst($loan->status) }}</x-ui.badge>
                    <span class="font-label-sm text-label-sm text-primary-fixed">₱{{ number_format($loan->remaining_balance ?? 0, 2) }}</span>
                @endif
            </div>
        </div>
        @empty
        <div class="py-16 text-center text-on-surface-variant/60">No borrowers found.</div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">{{ $borrowers->links() }}</div>
</div>
