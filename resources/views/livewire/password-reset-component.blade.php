<div>
    @section('subtitle', 'Set a new password')

    <div class="p-6 space-y-5">
        <div>
            <h2 class="font-bold text-on-surface text-lg">Create New Password</h2>
            <p class="text-sm text-on-surface-variant mt-1">Your new password must be at least 8 characters and include uppercase, lowercase, numbers, and symbols.</p>
        </div>

        @if (session('success'))
        <div class="flex items-center gap-3 bg-primary-fixed/10 border border-primary-fixed/30 text-primary-fixed rounded-xl px-4 py-3 text-sm">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">check_circle</span>
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="flex items-center gap-3 bg-error/10 border border-error/30 text-error rounded-xl px-4 py-3 text-sm">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">error</span>
            {{ session('error') }}
        </div>
        @endif

        <div class="space-y-4" x-data="{ showNew: false, showConfirm: false }">
            <div>
                <label for="pr-password" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">New Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[18px]">lock</span>
                    <input id="pr-password"
                           type="password" :type="showNew ? 'text' : 'password'"
                           class="w-full h-12 bg-surface-container pl-10 pr-12 rounded-xl border border-outline-variant text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary-fixed transition-colors @error('password') border-error @enderror"
                           wire:model.blur="password"
                           placeholder="Min. 8 characters"
                           autocomplete="new-password">
                    <button type="button" @click="showNew = !showNew"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors"
                            :aria-label="showNew ? 'Hide password' : 'Show password'">
                        <span class="material-symbols-outlined text-[18px]" x-text="showNew ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-error mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>{{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="pr-confirm" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Confirm Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[18px]">lock_reset</span>
                    <input id="pr-confirm"
                           type="password" :type="showConfirm ? 'text' : 'password'"
                           class="w-full h-12 bg-surface-container pl-10 pr-12 rounded-xl border border-outline-variant text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary-fixed transition-colors @error('password_confirmation') border-error @enderror"
                           wire:model.blur="password_confirmation"
                           placeholder="Repeat new password"
                           autocomplete="new-password">
                    <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors"
                            :aria-label="showConfirm ? 'Hide password' : 'Show password'">
                        <span class="material-symbols-outlined text-[18px]" x-text="showConfirm ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="text-xs text-error mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>{{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <div class="space-y-3 pt-1">
            <button type="button"
                    class="w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-[0.98] transition-all disabled:opacity-50"
                    wire:click="resetPassword"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="resetPassword" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">check_circle</span>Update Password
                </span>
                <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    Updating...
                </span>
            </button>

            <button type="button"
                    class="w-full h-10 border border-outline-variant text-on-surface-variant rounded-xl text-sm hover:border-outline hover:text-on-surface transition-colors flex items-center justify-center gap-1"
                    wire:click="logout">
                <span class="material-symbols-outlined text-[16px]">logout</span>
                Sign out instead
            </button>
        </div>
    </div>
</div>
