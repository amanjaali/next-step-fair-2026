<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.exhibit.title')" :lead="__('site.pages.exhibit.lead')">
            <div class="grid gap-12 lg:grid-cols-[1.1fr_1fr] items-start">
                <div>
                    <p class="ns-body mb-4">{{ __('site.pages.sponsors.become_p1', ['count' => 32]) }}</p>
                    <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-slate mb-9">{{ __('site.pages.sponsors.become_p2') }}</p>

                    <div class="overflow-x-auto">
                        <div class="min-w-[700px] grid gap-px bg-[rgba(5,7,8,0.14)]" style="grid-template-columns:1.4fr repeat(4,1fr)">
                            @foreach ($tierTable['head'] ?? [] as $heading)
                                <div class="bg-ink text-white px-[18px] py-4 ns-eyebrow !text-[10.5px] !text-white flex items-center">{{ $heading }}</div>
                            @endforeach
                            @foreach ($tierTable['rows'] ?? [] as $row)
                                @foreach ($row as $index => $cell)
                                    <div @class([
                                            'px-[18px] py-4 font-[family-name:var(--ns-body)] text-[13.5px] flex items-center',
                                            'bg-bone-50 font-bold' => $index === 0,
                                            'bg-white' => $index !== 0,
                                            'text-disabled' => $cell === '—',
                                        ])>{{ $cell }}</div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="ns-card">
                    @if (session('status'))
                        <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('exhibit.store') }}" class="flex flex-col gap-5">
                        @csrf
                        <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                        <div class="grid gap-5 sm:grid-cols-2">
                            <label>
                                <span class="ns-label">{{ __('register.step1.name') }} <span class="ns-req">*</span></span>
                                <input type="text" name="name" required value="{{ old('name') }}" class="ns-input">
                                @error('name')<span class="ns-error">{{ $message }}</span>@enderror
                            </label>
                            <label>
                                <span class="ns-label">{{ __('rsvp.step1.position') }}</span>
                                <input type="text" name="role" value="{{ old('role') }}" class="ns-input">
                            </label>
                            <label class="sm:col-span-2">
                                <span class="ns-label">{{ __('rsvp.step1.org_official') }} <span class="ns-req">*</span></span>
                                <input type="text" name="organization" required value="{{ old('organization') }}" class="ns-input">
                                @error('organization')<span class="ns-error">{{ $message }}</span>@enderror
                            </label>
                            <label>
                                <span class="ns-label">{{ __('site.common.email_address') }} <span class="ns-req">*</span></span>
                                <input type="email" name="email" required value="{{ old('email') }}" class="ns-input">
                                @error('email')<span class="ns-error">{{ $message }}</span>@enderror
                            </label>
                            <label>
                                <span class="ns-label">{{ __('register.step1.phone') }}</span>
                                <input type="tel" name="phone" value="{{ old('phone') }}" class="ns-input">
                            </label>
                            <label>
                                <span class="ns-label">{{ __('site.pages.sponsors.benefit') }}</span>
                                <select name="tier" class="ns-select">
                                    <option value="">—</option>
                                    @foreach (['Platinum', 'Gold', 'Silver', 'Bronze', 'Exhibitor booth'] as $tier)
                                        <option value="{{ $tier }}">{{ $tier }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                <span class="ns-label">{{ __('site.pages.floorplan.zone_key') }}</span>
                                <select name="booth_size" class="ns-select">
                                    <option value="">—</option>
                                    @foreach (['6 m²', '12 m²', '18 m²', '24 m²'] as $size)
                                        <option value="{{ $size }}">{{ $size }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <label>
                            <span class="ns-label">{{ __('rsvp.step2.notes') }}</span>
                            <textarea name="message" rows="5" class="ns-textarea">{{ old('message') }}</textarea>
                        </label>

                        <button type="submit" class="ns-btn ns-btn-magenta self-start">{{ __('site.cta.send') }}</button>
                    </form>
                </div>
            </div>
        </x-ns.page-head>
    </div>
</x-layouts.site>
