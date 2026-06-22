<div>
    @section('subtitle', 'Sign in to continue')

    @if (session('success'))
        <div class="px-6 pt-5">
            <div class="flex items-center gap-3 bg-primary-fixed/10 border border-primary-fixed/30 text-primary-fixed rounded-xl px-4 py-3 text-sm">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">check_circle</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (! $showEmailTab)
    {{-- ── Password Login ─────────────────────────────────── --}}
    <div class="p-6 space-y-5">
        <h2 class="font-bold text-on-surface text-lg">Welcome back</h2>

        <div class="space-y-4">
            <div>
                <label for="login-email" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Email or Username</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[18px]">person</span>
                    <input id="login-email"
                           type="text"
                           class="w-full h-12 bg-surface-container pl-10 pr-4 rounded-xl border border-outline-variant text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary-fixed transition-colors @error('email') border-error @enderror"
                           wire:model.blur="email"
                           placeholder="Enter email or username"
                           autocomplete="username"
                           autofocus>
                </div>
                @error('email')
                    <p class="text-xs text-error mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>{{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="login-password" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Password</label>
                <div class="relative" x-data="{ show: false }">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[18px]">lock</span>
                    <input id="login-password"
                           type="password" :type="show ? 'text' : 'password'"
                           class="w-full h-12 bg-surface-container pl-10 pr-12 rounded-xl border border-outline-variant text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary-fixed transition-colors @error('password') border-error @enderror"
                           wire:model="password"
                           placeholder="Enter password"
                           autocomplete="current-password">
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors"
                            :aria-label="show ? 'Hide password' : 'Show password'">
                        <span class="material-symbols-outlined text-[18px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-error mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>{{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input id="login-remember" type="checkbox" class="w-4 h-4 rounded border-outline-variant bg-surface-container accent-primary-fixed"
                       wire:model="remember">
                <label for="login-remember" class="text-sm text-on-surface-variant select-none cursor-pointer">Keep me signed in</label>
            </div>
        </div>

        <div class="space-y-3 pt-1">
            <button type="button"
                    class="w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-[0.98] transition-all disabled:opacity-50"
                    wire:click="login"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">login</span>Sign In
                </span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    Signing in...
                </span>
            </button>

            <button type="button"
                    class="w-full h-10 border border-outline-variant text-on-surface-variant rounded-xl text-sm hover:border-outline hover:text-on-surface transition-colors"
                    wire:click="emailTab">
                Sign in with a magic link
            </button>
        </div>
    </div>

    @else
    {{-- ── Magic Link ──────────────────────────────────────── --}}
    <div class="p-6 space-y-5">
        <div>
            <h2 class="font-bold text-on-surface text-lg">Magic Link</h2>
            <p class="text-sm text-on-surface-variant mt-1">We'll send a one-click sign-in link to your email.</p>
        </div>

        <div>
            <label for="magic-email" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Email Address</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[18px]">mail</span>
                <input id="magic-email"
                       type="email"
                       class="w-full h-12 bg-surface-container pl-10 pr-4 rounded-xl border border-outline-variant text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary-fixed transition-colors @error('email') border-error @enderror"
                       wire:model.blur="email"
                       placeholder="you@example.com"
                       autocomplete="email"
                       autofocus>
            </div>
            @error('email')
                <p class="text-xs text-error mt-1.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>{{ $message }}
                </p>
            @enderror
        </div>

        <div class="space-y-3 pt-1">
            <button type="button"
                    class="w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-[0.98] transition-all disabled:opacity-50"
                    wire:click="link"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="link" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">send</span>Send Magic Link
                </span>
                <span wire:loading wire:target="link" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    Sending...
                </span>
            </button>

            <button type="button"
                    class="w-full h-10 border border-outline-variant text-on-surface-variant rounded-xl text-sm hover:border-outline hover:text-on-surface transition-colors flex items-center justify-center gap-1"
                    wire:click="$set('showEmailTab', false)">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Back to password login
            </button>
        </div>
    </div>
    @endif
</div>
