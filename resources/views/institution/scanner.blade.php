<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap max-w-[720px] pt-[clamp(24px,3vw,40px)] pb-[clamp(72px,10vw,140px)]"
         x-data="nsBoothScanner('{{ route('portal.scan') }}', '{{ csrf_token() }}')">
        @include('institution.partials.nav', ['current' => 'scanner'])

        <h1 class="ns-h1 !text-[clamp(28px,3.6vw,40px)] mb-3">{{ __('institution.scanner.title') }}</h1>
        <p class="ns-body max-w-[58ch] mb-8">{{ __('institution.scanner.lead') }}</p>

        <div class="ns-card mb-6">
            {{-- The camera is the fast path; the manual box is the one that works
                 when a screen is cracked or the light is wrong at the desk. --}}
            <div class="relative bg-ink aspect-[4/3] mb-4 overflow-hidden" x-show="scanning" x-cloak>
                <video x-ref="video" class="w-full h-full object-cover" playsinline muted></video>
            </div>

            <div class="flex gap-3 flex-wrap">
                <button type="button" class="ns-btn ns-btn-cobalt" @click="start()" x-show="! scanning">
                    {{ __('institution.scanner.start') }}
                </button>
                <button type="button" class="ns-btn ns-btn-ghost" @click="stop()" x-show="scanning" x-cloak>
                    {{ __('institution.scanner.stop') }}
                </button>
            </div>

            <div class="mt-6 pt-5 border-t border-[rgba(5,7,8,0.12)]">
                <label class="block">
                    <span class="ns-label">{{ __('institution.scanner.manual') }}</span>
                    <div class="flex gap-2">
                        <input type="text" x-model="manual" class="ns-input ns-num flex-1" placeholder="8F2C-41A9-D77E"
                               @keydown.enter.prevent="submitManual()">
                        <button type="button" class="ns-btn ns-btn-ghost" @click="submitManual()">→</button>
                    </div>
                </label>
            </div>
        </div>

        {{-- Result card. Colour carries the state so it reads at arm's length. --}}
        <template x-if="result">
            <div class="ns-card !p-6" :class="{
                    'border-s-[6px] border-s-[#0E9B94]': result.state === 'ok',
                    'border-s-[6px] border-s-[#F2A93B]': result.state === 'already',
                    'border-s-[6px] border-s-[#8A1B3C]': result.state === 'invalid',
                 }">
                <div class="ns-eyebrow !text-[10px] mb-2"
                     x-text="result.state === 'ok' ? '{{ __('institution.scanner.ok') }}'
                           : result.state === 'already' ? '{{ __('institution.scanner.already') }}'
                           : '{{ __('institution.scanner.invalid') }}'"></div>

                <template x-if="result.state !== 'invalid'">
                    <div>
                        <div class="font-[family-name:var(--ns-display)] text-[26px] font-semibold mb-1" x-text="result.name"></div>
                        <div class="ns-meta ns-num mb-3" x-text="[result.ticket, result.city].filter(Boolean).join(' · ')"></div>

                        <div class="flex gap-2 flex-wrap mb-4">
                            <template x-for="f in (result.fields || [])" :key="f">
                                <span class="ns-typechip ns-typechip-conf" x-text="f"></span>
                            </template>
                        </div>

                        <div class="flex gap-5 flex-wrap items-center mb-5">
                            <template x-if="result.match">
                                <span class="font-[family-name:var(--ns-body)] text-[14px]">
                                    {{ __('institution.scanner.match') }}:
                                    <span class="ns-num font-bold text-cobalt" x-text="result.match + '/100'"></span>
                                </span>
                            </template>
                            <span class="ns-meta text-[12.5px]"
                                  x-text="result.shared ? '{{ __('institution.scanner.shared') }}' : '{{ __('institution.scanner.not_shared') }}'"></span>
                        </div>

                        <div class="flex gap-2">
                            <input type="text" x-model="note" class="ns-input flex-1"
                                   placeholder="{{ __('institution.scanner.note_placeholder') }}">
                            <button type="button" class="ns-btn ns-btn-ghost ns-btn-sm" @click="saveNote()">
                                {{ __('institution.scanner.save_note') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('nsBoothScanner', (endpoint, token) => ({
                    scanning: false, manual: '', note: '', result: null, lastTicket: null,
                    detector: null, stream: null, timer: null,

                    async start() {
                        if (! ('BarcodeDetector' in window)) { this.scanning = false; return; }
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                            this.$refs.video.srcObject = this.stream;
                            await this.$refs.video.play();
                            this.detector = new BarcodeDetector({ formats: ['qr_code'] });
                            this.scanning = true;
                            this.timer = setInterval(() => this.tick(), 400);
                        } catch (e) { this.scanning = false; }
                    },

                    stop() {
                        clearInterval(this.timer);
                        this.stream?.getTracks().forEach((t) => t.stop());
                        this.scanning = false;
                    },

                    async tick() {
                        if (! this.scanning) return;
                        try {
                            const codes = await this.detector.detect(this.$refs.video);
                            if (codes.length) this.send(codes[0].rawValue);
                        } catch (e) { /* a frame that would not decode is not an error */ }
                    },

                    submitManual() {
                        if (this.manual.trim()) this.send(this.manual.trim());
                    },

                    /** The QR carries a verify URL; a typed value is a ticket reference. */
                    parse(raw) {
                        try {
                            const url = new URL(raw);
                            return { ticket: url.pathname.split('/').pop(), sig: url.searchParams.get('sig') };
                        } catch (e) {
                            return { ticket: raw, sig: null };
                        }
                    },

                    async send(raw) {
                        const { ticket, sig } = this.parse(raw);
                        // Do not re-post the same badge while it sits in front of the lens.
                        if (ticket === this.lastTicket) return;
                        this.lastTicket = ticket;
                        setTimeout(() => { this.lastTicket = null; }, 4000);

                        const res = await fetch(endpoint, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, Accept: 'application/json' },
                            body: JSON.stringify({ ticket, sig }),
                        });
                        this.result = await res.json();
                        this.note = '';
                        this.manual = '';
                        if (navigator.vibrate) navigator.vibrate(this.result.state === 'ok' ? 60 : [40, 40, 40]);
                    },

                    async saveNote() {
                        if (! this.result || ! this.note.trim()) return;
                        await fetch(endpoint, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, Accept: 'application/json' },
                            body: JSON.stringify({ ticket: this.result.ticket, notes: this.note }),
                        });
                        this.note = '';
                    },
                }));
            });
        </script>
    @endpush
</x-layouts.site>
