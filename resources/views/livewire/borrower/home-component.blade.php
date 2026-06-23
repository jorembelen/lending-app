<div class="space-y-stack-md pb-8">

    @php
        $borrowerRecord = \App\Models\Borrower::find(auth()->id());
        $accountId  = $borrowerRecord?->borrower_code ?? ('BRW-' . str_pad(auth()->id(), 6, '0', STR_PAD_LEFT));
        $borrowerName = auth()->user()->name ?? 'Borrower';
        $qrSvg      = $this->qrCodeSvg;
    @endphp

    <!-- ID + QR Card -->
    <div x-data="{ showQr: false, copied: false }" class="space-y-2">

        <!-- Account ID Row -->
        <button type="button"
                class="w-full bg-surface-container-low rounded-xl border border-white/5 p-5 flex items-center justify-between gap-4 text-left active:opacity-80 transition-opacity"
                x-on:click="
                    navigator.clipboard?.writeText('{{ $accountId }}');
                    copied = true;
                    setTimeout(() => copied = false, 2000);
                "
                aria-label="Copy account ID {{ $accountId }}">
            <div>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Account ID</p>
                <p class="font-headline-lg-mobile text-headline-lg-mobile text-primary-fixed tracking-widest mt-0.5">{{ $accountId }}</p>
                <p class="font-label-sm text-label-sm text-on-surface-variant/60 mt-1">Tap to copy · or use your QR below</p>
            </div>
            <div class="flex-shrink-0 flex flex-col items-center gap-1">
                <span class="material-symbols-outlined text-primary-fixed/60 text-[24px]"
                      x-text="copied ? 'check_circle' : 'content_copy'"
                      :style="copied ? 'font-variation-settings: \'FILL\' 1' : ''"></span>
                <span class="font-label-sm text-label-sm text-on-surface-variant/50 text-[10px]"
                      x-text="copied ? 'Copied!' : 'Tap to copy'"></span>
            </div>
        </button>

        <!-- QR Code Preview Card -->
        <button type="button"
                @click="showQr = true"
                class="w-full bg-surface-container-low rounded-xl border border-white/5 p-5 flex items-center gap-5 text-left active:opacity-80 transition-opacity">
            <!-- Mini QR preview -->
            <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-white p-1 overflow-hidden [&_svg]:w-full [&_svg]:h-full">
                {!! $qrSvg !!}
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Your QR Code</p>
                <p class="font-bold text-on-surface text-[15px] mt-0.5">Tap to view &amp; share</p>
                <p class="font-label-sm text-label-sm text-on-surface-variant/60 mt-0.5">Show this to your collector for fast payment</p>
            </div>
            <span class="material-symbols-outlined text-on-surface-variant/40 text-[20px] flex-shrink-0">arrow_forward_ios</span>
        </button>

        <!-- QR Full-Screen Modal -->
        <div x-show="showQr"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex flex-col items-center justify-end bg-black/70 backdrop-blur-sm"
             style="display:none;"
             @click.self="showQr = false">

            <div x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="w-full max-w-md bg-[#1A1C24] rounded-t-2xl p-6 space-y-5 border-t border-[#2D2F39]">

                <!-- Handle + Header -->
                <div class="flex items-center justify-between">
                    <h2 class="font-headline-md text-headline-md text-on-surface">Your QR Code</h2>
                    <button @click="showQr = false"
                            class="w-9 h-9 rounded-full bg-surface-container-high flex items-center justify-center">
                        <span class="material-symbols-outlined text-on-surface-variant text-[20px]">close</span>
                    </button>
                </div>

                <!-- QR Card (white, scannable) -->
                <div id="borrower-qr-card"
                     class="bg-white rounded-2xl p-6 flex flex-col items-center gap-3 mx-auto max-w-xs">
                    <div class="w-52 h-52 [&_svg]:w-full [&_svg]:h-full">
                        {!! $qrSvg !!}
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-gray-900 text-[17px] leading-tight">{{ $borrowerName }}</p>
                        <p class="text-gray-500 text-[13px] mt-0.5 font-mono tracking-widest">{{ $accountId }}</p>
                    </div>
                    <div class="flex items-center gap-1.5 bg-gray-100 rounded-full px-3 py-1">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-[11px] font-bold text-gray-600 uppercase tracking-wider">LendPro Borrower</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3"
                     x-data="{
                        downloadQr() {
                            const card = document.getElementById('borrower-qr-card');
                            const svg  = card.querySelector('svg');
                            if (!svg) return;
                            const clone = svg.cloneNode(true);
                            clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
                            const svgStr  = new XMLSerializer().serializeToString(clone);
                            const blob    = new Blob([svgStr], { type: 'image/svg+xml' });
                            const url     = URL.createObjectURL(blob);
                            const a       = document.createElement('a');
                            a.href        = url;
                            a.download    = 'qr-{{ $accountId }}.svg';
                            a.click();
                            URL.revokeObjectURL(url);
                        },
                        async shareQr() {
                            if (navigator.share) {
                                try {
                                    await navigator.share({
                                        title: 'My LendPro QR Code',
                                        text:  'My borrower ID is {{ $accountId }}. Show this code to your collector.',
                                    });
                                } catch(e) {}
                            } else {
                                navigator.clipboard?.writeText('{{ $accountId }}');
                            }
                        }
                     }">
                    <button @click="downloadQr()"
                            class="flex items-center justify-center gap-2 h-12 rounded-xl bg-surface-container-high font-bold text-on-surface text-[14px] active:opacity-70 transition-opacity">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Download
                    </button>
                    <button @click="shareQr()"
                            class="flex items-center justify-center gap-2 h-12 rounded-xl bg-primary-fixed font-bold text-on-primary-fixed text-[14px] active:opacity-70 transition-opacity">
                        <span class="material-symbols-outlined text-[18px]">share</span>
                        Share
                    </button>
                </div>

                <p class="text-center text-[11px] text-on-surface-variant/50">
                    This QR encodes your unique borrower reference and can be scanned by your collector to log payments instantly.
                </p>
            </div>
        </div>

    </div>

    <!-- Status Pill -->
    @if($this->loan)
    <div class="flex justify-center">
        <x-ui.status-pill :status="$this->loan->status === 'active' ? 'on-track' : $this->loan->status" :pulse="true">
            {{ $this->loan->status === 'active' ? 'ON TRACK' : strtoupper($this->loan->status) }}
        </x-ui.status-pill>
    </div>
    @endif

    <!-- Hero Loan Card -->
    @if($this->loan)
    <section class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl p-6 relative overflow-hidden active:scale-[0.98] transition-transform">
        <div class="absolute -right-12 -top-12 w-32 h-32 bg-primary-fixed/5 blur-3xl rounded-full pointer-events-none"></div>

        <div class="flex flex-col items-center text-center space-y-stack-sm">
            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">REMAINING BALANCE</p>
            <h1 class="text-[40px] font-bold text-primary leading-none flex items-baseline gap-1">
                <span class="text-[20px] opacity-60">₱</span>
                {{ number_format($this->loan->remaining_balance ?? 0, 2) }}
            </h1>

            <!-- Circular Progress Ring -->
            @php $paidPct = $this->loan->principal > 0 ? min(100, ($this->loan->amount_paid / $this->loan->principal) * 100) : 0; @endphp
            <div class="relative w-40 h-40 flex items-center justify-center mt-4">
                <svg class="absolute inset-0 w-full h-full -rotate-90" viewBox="0 0 160 160">
                    <circle cx="80" cy="80" r="68" fill="none" stroke="#2D2F39" stroke-width="8"/>
                    <circle cx="80" cy="80" r="68" fill="none" stroke="#c3f400" stroke-width="8"
                        stroke-linecap="round"
                        stroke-dasharray="{{ round(427.256) }}"
                        stroke-dashoffset="{{ round(427.256 * (1 - $paidPct / 100)) }}"
                        style="transition: stroke-dashoffset 1s ease-out;"
                    />
                </svg>
                <div class="flex flex-col items-center justify-center z-10">
                    <span class="text-[28px] font-bold text-primary leading-none">{{ round($paidPct) }}%</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">PAID</span>
                </div>
            </div>

            <div class="w-full pt-4">
                @if(Route::has('borrower.schedule'))
                <a href="{{ route('borrower.schedule') }}"
                   class="w-full h-14 bg-primary-fixed text-on-primary-fixed rounded-xl flex items-center justify-center gap-2 font-label-md text-label-md font-bold active:scale-95 transition-all">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">bolt</span>
                    Make a Payment
                </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Quick Stats -->
    <section class="grid grid-cols-3 gap-3">
        <div class="bg-[#1A1C24] border border-[#2D2F39] p-3 rounded-xl flex flex-col items-center justify-center text-center">
            <span class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase">NEXT DUE</span>
            <span class="font-bold text-on-surface text-[14px]">
                {{ $this->loan->next_due_date?->format('M d') ?? '—' }}
            </span>
        </div>
        <div class="bg-[#1A1C24] border border-[#2D2F39] p-3 rounded-xl flex flex-col items-center justify-center text-center">
            <span class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase">TODAY'S</span>
            <span class="font-bold text-primary-fixed text-[14px]">
                ₱{{ number_format($this->loan->daily_payment ?? 0, 2) }}
            </span>
        </div>
        <div class="bg-[#1A1C24] border border-[#2D2F39] p-3 rounded-xl flex flex-col items-center justify-center text-center">
            <span class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase">DAYS LEFT</span>
            <span class="font-bold text-on-surface text-[14px]">
                {{ $this->loan->days_remaining ?? '—' }}
            </span>
        </div>
    </section>
    @else
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <span class="material-symbols-outlined text-on-surface-variant/40 mb-3" style="font-size:48px;">account_balance_wallet</span>
        <p class="font-headline-md text-headline-md text-on-surface-variant/60">No active loan</p>
        <p class="font-body-md text-secondary-fixed-dim mt-1">Contact your collector to apply for a loan.</p>
    </div>
    @endif

    <!-- Loyalty Status -->
    <section class="bg-[#1A1C24] border border-[#2D2F39] rounded-xl p-5 space-y-4">
        <div class="flex justify-between items-start">
            <div>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">LOYALTY STATUS</p>
                <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-primary">{{ $this->loyalty['tier'] }}</h2>
            </div>
            <div class="bg-secondary-container/20 border border-secondary-container/40 p-2 rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary-container" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-secondary-container uppercase leading-none">Streak</span>
                    <span class="text-sm font-bold text-on-surface">{{ $this->loyalty['streak'] }} Days</span>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex justify-between items-end">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">NEXT: {{ $this->loyalty['next_tier'] }}</span>
                <span class="text-xs font-medium text-primary">
                    {{ number_format($this->loyalty['points']) }} / {{ number_format($this->loyalty['next_points']) }} pts
                </span>
            </div>
            <x-ui.progress-bar :percent="$this->loyalty['next_points'] > 0 ? ($this->loyalty['points'] / $this->loyalty['next_points'] * 100) : 0" />
        </div>
    </section>

    <!-- Recent Payments -->
    @if($this->recentPayments->count())
    <section class="space-y-3">
        <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider px-1">RECENT REPAYMENTS</h3>
        @foreach($this->recentPayments as $payment)
        <div class="bg-surface-container-low p-4 rounded-xl flex items-center justify-between border border-transparent hover:border-outline-variant transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-fixed/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary-fixed" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <div>
                    <p class="font-bold text-on-surface text-[14px]">Weekly Installment</p>
                    <p class="text-xs text-on-surface-variant">{{ $payment->collected_at?->format('M d, Y') }} • Paid</p>
                </div>
            </div>
            <span class="font-bold text-on-surface">₱{{ number_format($payment->amount, 2) }}</span>
        </div>
        @endforeach
    </section>
    @endif

</div>
