<div>
<main class="flex flex-col min-h-screen px-margin-mobile max-w-md mx-auto">

    <!-- Brand -->
    <header class="flex flex-col items-center pt-12 pb-8">
        <div class="w-16 h-16 rounded-xl bg-surface-container-low border border-white/10 flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-primary-fixed" style="font-size:40px; font-variation-settings: 'FILL' 1;">bolt</span>
        </div>
        <h1 class="font-display-lg text-display-lg text-primary-fixed tracking-tight">VOLTAGE</h1>
        <p class="font-label-md text-label-md text-on-surface-variant mt-2">Borrower Portal</p>
    </header>

    <!-- Form Card -->
    <section class="bg-surface-container-low rounded-2xl border border-white/5 p-6 flex flex-col gap-stack-md">
        <x-ui.input
            label="Mobile Number"
            type="tel"
            inputmode="tel"
            placeholder="+63 9XX XXX XXXX"
            icon="phone"
            wire:model.blur="phone"
            :error="$errors->first('phone')"
        />

        <fieldset class="border-0 p-0 m-0">
            <legend class="font-label-md text-label-md text-on-surface ml-1 mb-stack-sm">PIN (4 digits)</legend>
            <x-ui.pin-input :digits="4" model="pin" />
            @error('pin')
                <p class="font-label-sm text-label-sm text-error text-center mt-2">{{ $message }}</p>
            @enderror
        </fieldset>
    </section>

    <!-- Actions -->
    <section class="mt-stack-md flex flex-col gap-4 pb-12">
        <x-ui.button
            variant="primary"
            size="lg"
            icon="login"
            wire:click="login"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="login">Access My Account</span>
            <span wire:loading wire:target="login">Verifying...</span>
        </x-ui.button>

        <p class="text-center font-label-sm text-label-sm text-on-surface-variant">
            Need help? <a href="#" class="text-primary-fixed hover:underline">Contact your collector</a>
        </p>
    </section>

</main>

<div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
    <div class="absolute top-[-5%] left-[-5%] w-[40%] h-[40%] bg-primary-fixed/8 rounded-full blur-[100px]"></div>
</div>
</div>
