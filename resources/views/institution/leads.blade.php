<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap pt-[clamp(24px,3vw,40px)] pb-[clamp(72px,10vw,140px)]">
        @include('institution.partials.nav', ['current' => 'leads'])

        <h1 class="ns-h1 !text-[clamp(28px,3.6vw,40px)] mb-3">{{ __('institution.leads.title') }}</h1>
        <p class="ns-body max-w-[64ch] mb-8">{{ __('institution.leads.lead') }}</p>

        <form method="GET" class="mb-8">
            <label class="ns-chip inline-flex">
                <input type="checkbox" name="follow_up" value="1" class="sr-only"
                       onchange="this.form.submit()" @checked(request()->boolean('follow_up'))>
                <span>{{ __('institution.leads.follow_up_only') }}</span>
            </label>
        </form>

        @forelse ($interactions as $interaction)
            @php($student = $interaction->registration)
            <div class="border-t border-[rgba(5,7,8,0.14)] py-4">
                <div class="flex justify-between gap-4 flex-wrap items-baseline">
                    <span class="font-[family-name:var(--ns-body)] text-[15.5px] font-semibold">
                        {{ $student?->share_with_institutions ? $student->full_name : __('institution.students.anonymous') }}
                    </span>
                    <span class="ns-meta text-[12.5px] ns-num">
                        {{ $interaction->occurred_at?->format('j M · H:i') }}
                        @if ($interaction->day) · {{ __('institution.leads.day') }} {{ $interaction->day }} @endif
                    </span>
                </div>
                <div class="ns-meta text-[13px] mt-1">
                    {{ collect([
                        $student?->city,
                        $interaction->field?->t('name'),
                        $interaction->rating ? __('institution.leads.rating').' '.$interaction->rating.'/5' : null,
                    ])->filter()->join(' · ') }}
                </div>
                @if ($interaction->notes)
                    <p class="ns-body !text-[14px] text-body-soft mt-2 max-w-[70ch]">{{ $interaction->notes }}</p>
                @endif
            </div>
        @empty
            <p class="ns-body text-body-soft py-8">{{ __('institution.leads.none') }}</p>
        @endforelse

        <div class="mt-8">{{ $interactions->links() }}</div>
    </div>
</x-layouts.site>
