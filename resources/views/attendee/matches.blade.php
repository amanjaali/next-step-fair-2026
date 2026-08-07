<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[980px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.profile.title') }}</span>
        </div>

        <div class="flex justify-between items-end gap-6 flex-wrap mb-4">
            <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)]">{{ __('attendee.matches.title') }}</h1>
            <a href="{{ route('me.interests') }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                {{ __('attendee.matches.edit') }}
            </a>
        </div>

        <p class="ns-body max-w-[62ch] mb-8">{{ __('attendee.matches.lead') }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-8 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        @forelse ($matches as $match)
            @php($org = $match->organization)
            <article class="ns-card mb-5">
                <div class="flex justify-between items-start gap-5 flex-wrap mb-4">
                    <div class="min-w-0">
                        <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold leading-[1.2] mb-1">
                            {{ $org->t('name') }}
                        </h2>
                        <div class="ns-meta text-[13px]">
                            {{ collect([$org->city, __("taxonomy.countries.{$org->country}")])->filter()->join(' · ') }}
                            @if ($org->booth)
                                · {{ __('attendee.matches.booth') }} <span class="ns-num">{{ $org->booth }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- The number, and the plain-word version of it. A score with no
                         explanation is a score nobody acts on. --}}
                    <div class="text-end shrink-0">
                        <div class="ns-num font-[family-name:var(--ns-display)] text-[30px] font-bold text-magenta leading-none">
                            {{ $match->score }}<span class="text-[15px] text-slate">/100</span>
                        </div>
                        <div class="ns-eyebrow !text-[9.5px] mt-1">{{ __('attendee.matches.band.'.$match->band()) }}</div>
                    </div>
                </div>

                <div class="border-t border-[rgba(5,7,8,0.12)] pt-4">
                    <div class="ns-eyebrow !text-[10px] mb-2">{{ __('attendee.matches.why') }}</div>
                    <ul class="list-none m-0 p-0 flex flex-wrap gap-x-5 gap-y-2">
                        @if (! empty($match->reasons['fields']))
                            <li class="font-[family-name:var(--ns-body)] text-[14px]">
                                <span class="text-slate">{{ __('attendee.matches.teaches') }}:</span>
                                <span class="font-semibold">{{ implode(', ', $match->reasons['fields']) }}</span>
                            </li>
                        @endif
                        @if (! empty($match->reasons['country']))
                            <li class="font-[family-name:var(--ns-body)] text-[14px]">
                                <span class="text-slate">{{ __('attendee.matches.in_country') }}:</span>
                                <span class="font-semibold">{{ collect($match->reasons['country'])->map(fn ($c) => __("taxonomy.countries.$c"))->join(', ') }}</span>
                            </li>
                        @endif
                        @if (! empty($match->reasons['language']))
                            <li class="font-[family-name:var(--ns-body)] text-[14px]">
                                <span class="text-slate">{{ __('attendee.matches.in_language') }}:</span>
                                <span class="font-semibold">{{ __('taxonomy.languages.'.$match->reasons['language']) }}</span>
                            </li>
                        @endif
                        @if (! empty($match->reasons['budget']))
                            <li class="font-[family-name:var(--ns-body)] text-[14px] font-semibold">{{ __('attendee.matches.within_budget') }}</li>
                        @endif
                        @if (! empty($match->reasons['scholarship']))
                            <li class="font-[family-name:var(--ns-body)] text-[14px] font-semibold">{{ __('attendee.matches.has_scholarship') }}</li>
                        @endif
                        @if (! empty($match->reasons['meets_entry']))
                            <li class="font-[family-name:var(--ns-body)] text-[14px] font-semibold">{{ __('attendee.matches.meets_entry') }}</li>
                        @endif
                    </ul>
                </div>

                <div class="flex gap-3 flex-wrap mt-5 pt-4 border-t border-[rgba(5,7,8,0.12)]">
                    <form method="POST" action="{{ route('me.matches.shortlist', $org) }}">
                        @csrf
                        @php($isSaved = in_array($org->id, $shortlisted, true))
                        <button type="submit" @class([
                            'ns-btn ns-btn-sm',
                            'ns-btn-magenta' => $isSaved,
                            'ns-btn-ghost' => ! $isSaved,
                        ])>{{ $isSaved ? __('attendee.matches.saved_label') : __('attendee.matches.save') }}</button>
                    </form>
                    @if ($org->website)
                        <a href="{{ $org->website }}" target="_blank" rel="noopener"
                           class="ns-btn ns-btn-ghost ns-btn-sm">{{ $org->website }}</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="ns-card text-center py-12">
                <p class="ns-body mb-6">{{ __('attendee.matches.empty') }}</p>
                <a href="{{ route('me.interests') }}" class="ns-btn ns-btn-magenta">
                    {{ __('attendee.matches.complete_first') }}
                </a>
            </div>
        @endforelse
    </div>
</x-layouts.site>
