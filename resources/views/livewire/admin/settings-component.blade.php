<div class="max-w-4xl space-y-6">

    <h2 class="font-headline-md text-headline-md text-primary">Settings</h2>

    <!-- Tab Bar -->
    <div class="flex gap-1 bg-surface-container border border-outline-variant rounded-xl p-1 w-fit">
        @foreach(['general' => 'General', 'holidays' => 'Holidays', 'loan-rates' => 'Loan Rates'] as $tab => $label)
        <button
            wire:click="setTab('{{ $tab }}')"
            class="px-5 py-2 rounded-lg font-label-md text-label-md transition-colors
                   {{ $activeTab === $tab ? 'bg-primary-fixed text-on-primary-fixed' : 'text-secondary-fixed-dim hover:text-primary' }}"
        >{{ $label }}</button>
        @endforeach
    </div>

    <!-- General Settings -->
    @if($activeTab === 'general')
    <x-ui.card>
        <h3 class="font-headline-md text-headline-md text-primary mb-6">General Configuration</h3>
        <div class="space-y-6">
            <x-ui.input label="Company Name" type="text" :value="config('app.name')" placeholder="LendingPro" />
            <x-ui.input label="Daily Collection Target (₱)" type="number" inputmode="numeric" :value="config('app.daily_collection_target', 150000)" placeholder="150000" />
            <div class="space-y-2">
                <label for="timezone" class="font-label-md text-label-md text-on-surface ml-1 block">Timezone</label>
                <select id="timezone" class="w-full h-14 bg-surface-container-low border border-white/10 rounded-xl px-5 text-body-md text-on-surface focus:outline-none focus:border-primary-fixed">
                    <option>Asia/Manila</option>
                    <option>UTC</option>
                </select>
            </div>
            <x-ui.button variant="primary" size="md">Save Changes</x-ui.button>
        </div>
    </x-ui.card>
    @endif

    <!-- Holidays -->
    @if($activeTab === 'holidays')
    <x-ui.card>
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-headline-md text-headline-md text-primary">Holiday Calendar</h3>
            <x-ui.button variant="primary" size="sm" icon="add" iconPosition="left">Add Holiday</x-ui.button>
        </div>
        <p class="font-body-md text-secondary-fixed-dim text-center py-8">
            Holiday management will be available once the Holidays model is set up.
        </p>
    </x-ui.card>
    @endif

    <!-- Loan Rate Presets -->
    @if($activeTab === 'loan-rates')
    <x-ui.card>
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-headline-md text-headline-md text-primary">Loan Rate Presets</h3>
            <x-ui.button variant="primary" size="sm" icon="add" iconPosition="left">Add Preset</x-ui.button>
        </div>
        <p class="font-body-md text-secondary-fixed-dim text-center py-8">
            Rate preset management will be available once the LoanRate model is set up.
        </p>
    </x-ui.card>
    @endif
</div>
