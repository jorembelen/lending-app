<div
    class="min-h-screen bg-[#0A0A0A] relative overflow-hidden"
    x-data="{
        flashOn: false,
        scanning: false,
        startCamera() {
            this.scanning = true;
            if (typeof Html5Qrcode === 'undefined') return;
            const scanner = new Html5Qrcode('qr-reader');
            scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                (decoded) => {
                    scanner.stop();
                    $wire.handleScan(decoded);
                },
                () => {}
            );
        }
    }"
    x-init="startCamera()"
>
    <!-- Camera feed bg -->
    <div class="absolute inset-0 z-0 bg-[#0A0A0A]">
        <div id="qr-reader" class="w-full h-full opacity-70"></div>
    </div>

    <!-- Top Bar -->
    <header class="fixed top-0 w-full z-50 flex items-center justify-between px-margin-mobile h-touch-target-min">
        <a href="{{ route('collector.route') }}"
           class="w-12 h-12 flex items-center justify-center rounded-full bg-surface-container/40 backdrop-blur-md active:scale-95 transition-transform"
           aria-label="Close Scanner">
            <span class="material-symbols-outlined text-primary">close</span>
        </a>

        <div class="bg-surface-container/40 backdrop-blur-md px-4 py-2 rounded-full border border-white/10">
            <span class="font-label-md text-label-md text-primary-fixed uppercase tracking-wider">Scanning QR</span>
        </div>

        <button
            aria-label="Toggle Flashlight"
            @click="flashOn = !flashOn"
            :class="flashOn ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-surface-container/40 text-primary'"
            class="w-12 h-12 flex items-center justify-center rounded-full backdrop-blur-md active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined" x-text="flashOn ? 'flashlight_off' : 'flashlight_on'">flashlight_on</span>
        </button>
    </header>

    <!-- Viewfinder -->
    <div class="fixed inset-0 flex items-center justify-center z-10 pointer-events-none">
        <div class="relative w-60 h-60">
            <!-- Corner marks -->
            @foreach(['top-0 left-0 border-t-4 border-l-4 rounded-tl-xl', 'top-0 right-0 border-t-4 border-r-4 rounded-tr-xl', 'bottom-0 left-0 border-b-4 border-l-4 rounded-bl-xl', 'bottom-0 right-0 border-b-4 border-r-4 rounded-br-xl'] as $corner)
            <div class="absolute w-8 h-8 border-primary-fixed {{ $corner }}"></div>
            @endforeach

            <!-- Scan line animation -->
            <div class="absolute left-2 right-2 h-0.5 bg-primary-fixed/80 animate-bounce" style="top: 50%; animation-duration: 1.5s;"></div>

            <div class="absolute inset-0 flex items-center justify-center opacity-30">
                <span class="material-symbols-outlined text-primary-fixed" style="font-size:64px; font-variation-settings: 'wght' 200;">qr_code_2</span>
            </div>
        </div>
    </div>

    <!-- Error message -->
    @if($errorMessage)
    <div class="fixed top-24 left-4 right-4 z-50 bg-error-container/90 backdrop-blur-md border border-error/30 rounded-xl px-5 py-3">
        <p class="font-label-md text-label-md text-on-error-container">{{ $errorMessage }}</p>
    </div>
    @endif

    <!-- Bottom Actions -->
    <footer class="fixed bottom-0 w-full z-50 pb-safe px-margin-mobile pt-8 bg-gradient-to-t from-background via-background/80 to-transparent">
        <div class="max-w-md mx-auto flex flex-col items-center gap-stack-md mb-8">
            <p class="text-on-surface-variant text-center font-body-md px-8">
                Align the customer's QR code within the frame to verify identity and process payment.
            </p>

            <x-ui.button variant="secondary" size="lg">
                Open Camera Gallery
            </x-ui.button>

            @if(Route::has('collector.payment.manual'))
            <a href="{{ route('collector.payment.manual') }}"
               class="flex items-center gap-2 text-primary-fixed hover:text-primary transition-colors py-2 active:scale-95">
                <span class="font-label-md text-label-md underline underline-offset-4 decoration-primary-fixed/40">Can't scan? Search borrower manually</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
            @endif
        </div>
    </footer>
</div>

{{-- html5-qrcode loaded via Vite in the standalone-page layout --}}
