@extends('auth.main')

@section('title', '2FA Verification')
@section('subtitle', 'Two-factor authentication')

@section('content')
<div class="p-6 space-y-5">

    <div class="flex flex-col items-center text-center pt-2 pb-1">
        <div class="w-14 h-14 rounded-full bg-secondary-container/20 border border-secondary-container/30 flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-secondary-container text-[28px]" style="font-variation-settings:'FILL' 1;">security</span>
        </div>
        <h2 class="font-bold text-on-surface text-lg">One-Time Password</h2>
        <p class="text-sm text-on-surface-variant mt-1">Enter the 6-digit code from your authenticator app.</p>
    </div>

    @if ($errors->any())
    <div class="flex items-center gap-3 bg-error/10 border border-error/30 text-error rounded-xl px-4 py-3 text-sm">
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">error</span>
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('2faVerify') }}" id="otpForm">
        @csrf
        <div>
            <label for="otp-code" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">Authenticator Code</label>
            <input id="otp-code"
                   type="text"
                   inputmode="numeric"
                   name="one_time_password"
                   class="w-full h-14 bg-surface-container rounded-xl border border-outline-variant text-on-surface text-center text-2xl font-bold tracking-[0.5em] placeholder:tracking-normal placeholder:text-on-surface-variant/40 focus:outline-none focus:border-primary-fixed transition-colors"
                   placeholder="000000"
                   maxlength="6"
                   autocomplete="one-time-code"
                   autofocus
                   required>
        </div>

        <button type="submit"
                class="mt-5 w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-[0.98] transition-all"
                id="otpSubmit">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">verified_user</span>
            Verify & Sign In
        </button>
    </form>

</div>

@push('js')
<script>
    document.getElementById('otp-code').addEventListener('input', function () {
        if (this.value.replace(/\D/g,'').length === 6) {
            document.getElementById('otpForm').submit();
        }
    });
</script>
@endpush
@endsection
