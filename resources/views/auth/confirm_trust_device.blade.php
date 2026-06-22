@extends('auth.main')

@section('title', 'Trust Device')
@section('subtitle', 'Device verification')

@section('content')
<div class="p-6 space-y-5">

    <div class="flex flex-col items-center text-center pt-2 pb-1">
        <div class="w-14 h-14 rounded-full bg-primary-fixed/10 border border-primary-fixed/30 flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-primary-fixed text-[28px]" style="font-variation-settings:'FILL' 1;">devices</span>
        </div>
        <h2 class="font-bold text-on-surface text-lg">Trust This Device?</h2>
        <p class="text-sm text-on-surface-variant mt-1">Two-factor authentication was confirmed. You may register this device to streamline future sign-ins.</p>
    </div>

    @if (session('error'))
    <div class="flex items-center gap-3 bg-error/10 border border-error/30 text-error rounded-xl px-4 py-3 text-sm">
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">error</span>
        {{ session('error') }}
    </div>
    @endif

    <!-- Device Info -->
    <div class="bg-surface-container rounded-xl border border-outline-variant divide-y divide-outline-variant overflow-hidden">
        <div class="flex items-start gap-3 px-4 py-3">
            <span class="material-symbols-outlined text-on-surface-variant text-[18px] mt-0.5">computer</span>
            <div>
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Device</p>
                <p class="text-sm text-on-surface break-all">{{ $user_agent }}</p>
            </div>
        </div>
        <div class="flex items-start gap-3 px-4 py-3">
            <span class="material-symbols-outlined text-on-surface-variant text-[18px] mt-0.5">location_on</span>
            <div>
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">IP Address</p>
                <p class="text-sm text-on-surface font-mono">{{ $ip_address }}</p>
            </div>
        </div>
        <div class="flex items-start gap-3 px-4 py-3">
            <span class="material-symbols-outlined text-on-surface-variant text-[18px] mt-0.5">schedule</span>
            <div>
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Trust Expires</p>
                <p class="text-sm text-on-surface">{{ \Carbon\Carbon::parse($expires_at)->format('d M Y, h:i a') }}</p>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        <form method="POST" action="{{ route('confirm.trust.device') }}">
            @csrf
            <button type="submit"
                    class="w-full h-12 bg-primary-fixed text-on-primary-fixed rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-[0.98] transition-all">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">check_circle</span>
                Trust This Device &amp; Continue
            </button>
        </form>

        <form method="GET" action="{{ route('home') }}">
            <button type="submit"
                    class="w-full h-10 border border-outline-variant text-on-surface-variant rounded-xl text-sm hover:border-outline hover:text-on-surface transition-colors flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                No, just continue
            </button>
        </form>
    </div>

</div>
@endsection
