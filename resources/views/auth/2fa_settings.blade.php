@extends('auth.main')

@section('title', 'Two-Factor Authentication')
@section('subtitle', 'Account security')

@section('content')
<div class="p-6 space-y-5" x-data="{ helpOpen: false }">

    <!-- Help modal backdrop -->
    <div x-show="helpOpen"
         x-transition.opacity
         class="fixed inset-0 bg-black/60 z-40 flex items-center justify-center p-4"
         @click.self="helpOpen = false"
         style="display:none">
        <div class="bg-surface-container-low border border-outline-variant rounded-2xl w-full max-w-lg max-h-[80vh] overflow-y-auto shadow-2xl"
             @click.stop>
            <div class="sticky top-0 bg-surface-container-low border-b border-outline-variant flex items-center justify-between px-6 py-4">
                <h3 class="font-bold text-on-surface">About Two-Factor Authentication</h3>
                <button type="button" @click="helpOpen = false"
                        class="text-on-surface-variant hover:text-on-surface transition-colors"
                        aria-label="Close help">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm text-on-surface-variant">
                <p>2FA adds an extra layer of security by requiring two types of verification: your password <em>and</em> a time-based code from your phone.</p>
                <div>
                    <p class="font-bold text-on-surface mb-1">Why enable 2FA?</p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Protects your account even if your password is stolen</li>
                        <li>Prevents unauthorized access from hackers</li>
                        <li>Secures sensitive data and personal information</li>
                    </ul>
                </div>
                <div>
                    <p class="font-bold text-on-surface mb-1">How it works</p>
                    <ol class="space-y-1 list-decimal list-inside">
                        <li>After entering your password, you'll be asked for a 6-digit code</li>
                        <li>Open your authenticator app (Google Authenticator, Authy, etc.)</li>
                        <li>Enter the code shown — it refreshes every 30 seconds</li>
                    </ol>
                </div>
                <div class="bg-primary-fixed/10 border border-primary-fixed/20 rounded-xl px-4 py-3">
                    <p class="text-primary-fixed font-medium">Tip: If you lose your authenticator app, you may be locked out. Save backup codes in a safe place.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-secondary-container/20 border border-secondary-container/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-secondary-container text-[20px]" style="font-variation-settings:'FILL' 1;">security</span>
            </div>
            <h2 class="font-bold text-on-surface text-lg">Two-Factor Auth</h2>
        </div>
        <button type="button" @click="helpOpen = true"
                class="flex items-center gap-1.5 text-xs font-bold text-on-surface-variant border border-outline-variant px-3 py-1.5 rounded-lg hover:border-outline hover:text-on-surface transition-colors">
            <span class="material-symbols-outlined text-[14px]">help</span>What is 2FA?
        </button>
    </div>

    @if (session('error'))
    <div class="flex items-center gap-3 bg-error/10 border border-error/30 text-error rounded-xl px-4 py-3 text-sm">
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">error</span>
        {{ session('error') }}
    </div>
    @endif
    @if (session('success'))
    <div class="flex items-center gap-3 bg-primary-fixed/10 border border-primary-fixed/30 text-primary-fixed rounded-xl px-4 py-3 text-sm">
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    @if ($data['user']->loginSecurity == null)
    {{-- ── Step 1: Generate secret ─────────────────────────── --}}
    <div class="flex flex-col items-center text-center py-4 space-y-3">
        <img src="{{ asset('assets/images/2fa-image.png') }}" alt="2FA illustration" class="w-32 opacity-80">
        <p class="text-sm text-on-surface-variant">2FA is not yet enabled on your account. Generate a secret key to get started.</p>
    </div>
    <form method="POST" action="{{ route('generate2faSecret') }}">
        @csrf
        <button type="submit"
                class="w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-[0.98] transition-all">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">key</span>
            Generate Secret Key
        </button>
    </form>

    @elseif(! $data['user']->loginSecurity->google2fa_enable)
    {{-- ── Step 2: Scan QR & verify ────────────────────────── --}}
    <div class="space-y-4">
        <div class="bg-surface-container rounded-xl border border-outline-variant p-4 space-y-3">
            <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Step 1 — Scan this QR code</p>
            <div class="flex justify-center">
                {!! $data['google2fa_url'] !!}
            </div>
            <p class="text-xs text-on-surface-variant text-center">Or enter this code manually:</p>
            <div class="bg-background border border-outline-variant rounded-lg px-4 py-2 text-center font-mono text-sm tracking-widest text-on-surface select-all">
                {{ $data['secret'] }}
            </div>
        </div>

        <form method="POST" action="{{ route('enable2fa') }}" class="space-y-4">
            @csrf
            <div>
                <label for="2fa-code" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Step 2 — Enter Authenticator Code</label>
                <input id="2fa-code"
                       type="text"
                       inputmode="numeric"
                       name="secret"
                       class="w-full h-14 bg-surface-container rounded-xl border border-outline-variant text-on-surface text-center text-2xl font-bold tracking-[0.5em] placeholder:tracking-normal placeholder:text-on-surface-variant/40 focus:outline-none focus:border-primary-fixed transition-colors @error('verify-code') border-error @enderror"
                       placeholder="000000"
                       maxlength="6"
                       autocomplete="one-time-code"
                       required>
                @error('verify-code')
                    <p class="text-xs text-error mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>{{ $message }}
                    </p>
                @enderror
            </div>
            <button type="submit"
                    class="w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-[0.98] transition-all">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">verified_user</span>
                Enable 2FA
            </button>
        </form>
    </div>

    @else
    {{-- ── 2FA Enabled: disable option ─────────────────────── --}}
    <div class="flex items-center gap-3 bg-primary-fixed/10 border border-primary-fixed/30 rounded-xl px-4 py-3">
        <span class="material-symbols-outlined text-primary-fixed text-[20px]" style="font-variation-settings:'FILL' 1;">shield</span>
        <p class="text-sm text-primary-fixed font-medium">2FA is <strong>enabled</strong> on your account.</p>
    </div>

    <form method="POST" action="{{ route('disable2fa') }}" class="space-y-4">
        @csrf
        <div>
            <label for="dis-password" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Confirm Current Password to Disable</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[18px]">lock</span>
                <input id="dis-password"
                       type="password"
                       name="current-password"
                       class="w-full h-12 bg-surface-container pl-10 pr-4 rounded-xl border border-outline-variant text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary-fixed transition-colors @error('current-password') border-error @enderror"
                       placeholder="Enter current password"
                       autocomplete="current-password"
                       required>
            </div>
            @error('current-password')
                <p class="text-xs text-error mt-1.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>{{ $message }}
                </p>
            @enderror
        </div>
        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 h-12 border border-error text-error rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:bg-error/10 transition-colors">
                <span class="material-symbols-outlined text-[18px]">shield_off</span>
                Disable 2FA
            </button>
            <a href="{{ route('home') }}"
               class="flex-1 h-12 bg-primary-fixed text-on-primary-fixed rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:opacity-90 transition-all">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">dashboard</span>
                Dashboard
            </a>
        </div>
    </form>
    @endif

</div>
@endsection
