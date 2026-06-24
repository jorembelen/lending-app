<div class="min-h-screen px-margin-mobile py-8">
    <div class="max-w-md mx-auto">

        <!-- Header -->
        <header class="mb-6">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-1 text-on-surface-variant text-sm mb-4 active:opacity-70">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
            </a>
            <h1 class="font-headline-lg-mobile text-headline-lg-mobile font-bold text-primary tracking-tight">
                Collector App Setup
            </h1>
            <p class="text-on-surface-variant text-sm mt-2 leading-relaxed">
                Follow these steps <strong>once per phone</strong> when issuing a device to a new
                collector. Do this at the office on Wi-Fi.
            </p>
        </header>

        <!-- Critical callout -->
        <div class="bg-error/10 border border-error/30 rounded-2xl px-5 py-4 mb-6 flex gap-3">
            <span class="material-symbols-outlined text-error flex-shrink-0">warning</span>
            <div class="text-sm text-on-surface leading-relaxed">
                <p class="font-bold text-error mb-1">Must be done online, the first time.</p>
                A PWA is not a native app — the phone has to download and cache the app once
                <em>while connected</em> before offline collection will work. Opening the app for the
                very first time with no signal will just show a browser error. That is expected.
            </div>
        </div>

        <!-- Browser requirement -->
        <div class="bg-surface-container-low border border-white/10 rounded-2xl px-5 py-4 mb-6 flex gap-3">
            <span class="material-symbols-outlined text-primary flex-shrink-0">chrome_reader_mode</span>
            <div class="text-sm text-on-surface leading-relaxed">
                <p class="font-bold mb-1">Use Google Chrome.</p>
                Chrome is the only reliably supported browser for installing this app on Android.
                Do not use Samsung Internet, Firefox, or others.
            </div>
        </div>

        <!-- Steps -->
        <ol class="space-y-4">
            @php
                $steps = [
                    ['title' => 'Connect to office Wi-Fi', 'body' => "On the collector's phone, make sure you have a solid internet connection before starting."],
                    ['title' => 'Open the app in Chrome', 'body' => 'Go to the collector app URL below in Chrome.'],
                    ['title' => "Log in with the collector's credentials", 'body' => 'Sign in as the collector so their route is tied to this device.'],
                    ['title' => 'Wait for the page to fully load', 'body' => 'This triggers the service worker to install and cache the app. Give it a few seconds.'],
                    ['title' => 'Install to home screen', 'body' => 'Tap Chrome\'s "Install app" / "Add to Home Screen" prompt. If it does not appear automatically, open Chrome\'s menu (⋮) → "Install app" or "Add to Home Screen".'],
                    ['title' => 'Confirm the icon appears', 'body' => 'Check the home screen for the app icon. Open it — it should launch full-screen with no browser address bar (standalone mode).'],
                    ['title' => "Fetch today's route once", 'body' => 'Inside the installed app, load Today\'s Route while still online to confirm data caching works. Then close it. The device is now ready for offline collection.'],
                ];
            @endphp

            @foreach($steps as $i => $step)
                <li class="bg-surface-container-low border border-white/10 rounded-2xl px-5 py-4 flex gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-fixed text-on-primary-fixed font-bold flex items-center justify-center text-sm">
                        {{ $i + 1 }}
                    </span>
                    <div>
                        <p class="font-semibold text-on-surface text-[15px]">{{ $step['title'] }}</p>
                        <p class="text-on-surface-variant text-sm mt-1 leading-relaxed">{{ $step['body'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <!-- App URL -->
        <div class="mt-6 bg-surface-container-high border border-white/10 rounded-2xl px-5 py-4">
            <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-2">Collector app URL</p>
            <p class="font-mono text-primary-fixed text-sm break-all select-all">{{ route('collector.route') }}</p>
        </div>

        <p class="text-on-surface-variant/60 text-xs mt-6 leading-relaxed">
            After setup, the daily routine is automatic: opening the app on signal syncs any queued
            payments, refreshes today's route, and updates the app in the background. During the day,
            payments recorded with no signal are queued locally and flush automatically once signal
            returns — the collector does not need to do anything.
        </p>
    </div>
</div>
