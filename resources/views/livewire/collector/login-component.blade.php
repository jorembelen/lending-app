<x-layout.standalone-page title="Voltage — Field Operations Login">
<main class="flex-grow flex flex-col px-margin-mobile pt-stack-lg pb-stack-md max-w-md mx-auto w-full">

    <!-- Brand -->
    <header class="flex flex-col items-center justify-center pt-12 pb-16">
        <div class="relative group">
            <div class="absolute -inset-1 bg-primary-fixed blur opacity-20 group-hover:opacity-40 transition duration-1000 rounded-xl"></div>
            <div class="relative bg-surface-container-lowest p-6 rounded-xl border border-white/5 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary-fixed" style="font-size:64px; font-variation-settings: 'FILL' 1;">bolt</span>
            </div>
        </div>
        <h1 class="font-display-lg text-display-lg mt-8 text-primary-fixed tracking-tight">VOLTAGE</h1>
        <p class="font-label-md text-label-md text-on-surface-variant mt-2 uppercase tracking-[0.2em]">Field Operations Pro</p>
    </header>

    <!-- Form -->
    <section class="flex flex-col space-y-stack-md flex-grow">

        @if($errors->has('email') && !$errors->has('email')){{-- handled below --}}@endif

        <x-ui.input
            label="Work Email"
            type="email"
            placeholder="agent@voltage.com"
            icon="alternate_email"
            wire:model.blur="email"
            :error="$errors->first('email')"
        />

        <div class="space-y-base" x-data="{ show: false }">
            <label for="collector-password" class="font-label-md text-label-md text-on-surface ml-1 block">Secure Password</label>
            <div class="relative">
                <input
                    id="collector-password"
                    :type="show ? 'text' : 'password'"
                    wire:model="password"
                    placeholder="••••••••"
                    class="w-full h-14 bg-surface-container-low border {{ $errors->first('password') ? 'border-error' : 'border-white/10' }} rounded-xl px-5 pr-12 text-body-md font-sans text-on-surface placeholder:text-on-surface-variant/40 focus:outline-none focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed"
                />
                <button type="button" @click="show = !show" class="absolute right-4 top-4 text-on-surface-variant/40 hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined" x-text="show ? 'visibility' : 'visibility_off'">visibility_off</span>
                </button>
            </div>
            @error('password')
                <p class="font-label-sm text-label-sm text-error ml-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between pt-2">
            <label class="flex items-center cursor-pointer group" x-data>
                <input type="checkbox" wire:model="remember" class="hidden peer" />
                <div class="w-5 h-5 border border-white/20 rounded bg-surface-container peer-checked:bg-primary-fixed peer-checked:border-primary-fixed transition-all flex items-center justify-center">
                    <span class="material-symbols-outlined text-[14px] text-on-primary font-bold hidden peer-checked:block">check</span>
                </div>
                <span class="ml-3 font-label-sm text-label-sm text-on-surface-variant group-hover:text-on-surface transition-colors">Keep me active</span>
            </label>
            <a href="#" class="font-label-sm text-label-sm text-primary-fixed hover:underline">Forgot Access?</a>
        </div>
    </section>

    <!-- Actions -->
    <section class="mt-stack-lg flex flex-col space-y-4">
        <x-ui.button
            variant="primary"
            size="lg"
            icon="login"
            wire:click="login"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="login">LOG IN</span>
            <span wire:loading wire:target="login">Signing in...</span>
        </x-ui.button>

        <x-ui.button variant="secondary" size="lg" icon="fingerprint" iconPosition="left">
            Biometric Entry
        </x-ui.button>
    </section>

    <footer class="mt-12 text-center">
        <p class="font-label-sm text-label-sm text-on-surface-variant/40">Encrypted Session — VOLT</p>
    </footer>

</main>

<!-- Background glow -->
<div class="fixed inset-0 -z-10 pointer-events-none opacity-20 overflow-hidden">
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary-fixed/10 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[30%] h-[30%] bg-primary-fixed/5 rounded-full blur-[100px]"></div>
</div>
</x-layout.standalone-page>
