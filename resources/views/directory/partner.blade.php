<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[1000px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <nav class="ns-meta flex gap-2 items-center flex-wrap mb-9" aria-label="Breadcrumb">
            <a href="{{ route('sponsors') }}">{{ __('site.pages.sponsors.title') }}</a>
            <span aria-hidden="true">/</span>
            <span>{{ $partner->t('name') }}</span>
        </nav>

        {{-- The mark first, at the size it is worth: somebody arriving here
             clicked a logo, and this is the confirmation they landed where they
             meant to. --}}
        <div class="flex items-center gap-[clamp(20px,3vw,36px)] flex-wrap mb-9">
            @if ($logo = $partner->logoUrl())
                <div class="bg-white border border-[rgba(5,7,8,0.14)] p-5 shrink-0">
                    <img src="{{ $logo }}" alt="{{ $partner->t('name') }}" class="h-[92px] w-auto block max-w-[220px] object-contain">
                </div>
            @endif

            <div class="min-w-[260px] flex-1">
                <div class="ns-eyebrow !text-magenta mb-3">
                    {{ $partner->kind === \App\Models\Organization::KIND_STRATEGIC
                        ? __('site.pages.partner.strategic')
                        : __('site.pages.partner.supporter') }}
                </div>
                <h1 class="ns-h1 !text-[clamp(26px,3.6vw,42px)] mb-3">{{ $partner->t('name') }}</h1>
                @if ($partner->t('description'))
                    <p class="ns-body max-w-[58ch]">{{ $partner->t('description') }}</p>
                @endif
            </div>
        </div>

        <div class="grid gap-[clamp(32px,4vw,56px)] lg:grid-cols-[1.5fr_1fr] items-start">
            <div class="min-w-0">
                @if ($partner->t('about'))
                    <section class="mb-11">
                        <div class="flex items-baseline gap-5 mb-5 flex-wrap">
                            <span class="ns-eyebrow">{{ __('site.pages.partner.about') }}</span>
                            <span class="ns-rule"></span>
                        </div>
                        <div class="ns-prose">{!! ns_rich($partner->t('about')) !!}</div>
                    </section>
                @endif

                @if ($partner->t('partnership'))
                    <section class="mb-11">
                        <div class="flex items-baseline gap-5 mb-5 flex-wrap">
                            <span class="ns-eyebrow">{{ __('site.pages.partner.partnership') }}</span>
                            <span class="ns-rule"></span>
                        </div>
                        <div class="ns-prose">{!! ns_rich($partner->t('partnership')) !!}</div>
                    </section>
                @endif
            </div>

            <aside class="min-w-0">
                {{-- Contact, as things to press rather than things to copy. --}}
                @if ($partner->website || $partner->public_email || $partner->public_phone || $partner->since_year)
                    <div class="border border-[rgba(5,7,8,0.14)] bg-white p-[clamp(20px,2.4vw,28px)] mb-6">
                        <div class="ns-eyebrow !text-[9.5px] mb-4">{{ __('site.pages.partner.contact') }}</div>

                        <dl class="m-0 border-t border-[rgba(5,7,8,0.12)]">
                            @if ($partner->website)
                                <div class="py-[11px] border-b border-[rgba(5,7,8,0.08)]">
                                    <dt class="ns-eyebrow !text-[9px] mb-[3px]">{{ __('site.pages.partner.website') }}</dt>
                                    <dd class="m-0">
                                        <a href="{{ $partner->website }}" target="_blank" rel="noopener"
                                           class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold text-magenta break-words">
                                            {{ preg_replace('#^https?://(www\.)?#', '', rtrim($partner->website, '/')) }}
                                        </a>
                                    </dd>
                                </div>
                            @endif

                            @if ($partner->public_email)
                                <div class="py-[11px] border-b border-[rgba(5,7,8,0.08)]">
                                    <dt class="ns-eyebrow !text-[9px] mb-[3px]">{{ __('site.pages.partner.email') }}</dt>
                                    <dd class="m-0">
                                        <a href="mailto:{{ $partner->public_email }}"
                                           class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold text-magenta break-words">{{ $partner->public_email }}</a>
                                    </dd>
                                </div>
                            @endif

                            @if ($partner->public_phone)
                                <div class="py-[11px] border-b border-[rgba(5,7,8,0.08)]">
                                    <dt class="ns-eyebrow !text-[9px] mb-[3px]">{{ __('site.pages.partner.phone') }}</dt>
                                    <dd class="m-0">
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $partner->public_phone) }}" dir="ltr"
                                           class="ns-num font-[family-name:var(--ns-body)] text-[14.5px] font-bold text-magenta">{{ $partner->public_phone }}</a>
                                    </dd>
                                </div>
                            @endif

                            @if ($partner->since_year)
                                <div class="py-[11px]">
                                    <dt class="ns-eyebrow !text-[9px] mb-[3px]">{{ __('site.pages.partner.since') }}</dt>
                                    <dd class="m-0 ns-num font-[family-name:var(--ns-body)] text-[14.5px] font-bold">{{ $partner->since_year }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                @if ($others->isNotEmpty())
                    <div class="border border-[rgba(5,7,8,0.14)] p-[clamp(20px,2.4vw,28px)]">
                        <div class="ns-eyebrow !text-[9.5px] mb-4">{{ __('site.pages.partner.others') }}</div>
                        <div class="flex flex-col gap-[10px]">
                            @foreach ($others as $other)
                                <a href="{{ $other->partnerUrl() }}"
                                   class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold text-ink hover:text-magenta">
                                    {{ $other->t('name') }}
                                </a>
                            @endforeach
                            <a href="{{ route('sponsors') }}"
                               class="font-[family-name:var(--ns-body)] text-[13.5px] text-slate hover:text-magenta mt-2">
                                {{ __('site.cta.all_partners', ['count' => '']) }}
                            </a>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.site>
