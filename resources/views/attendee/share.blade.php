<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[1000px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <a href="{{ route('me') }}" class="ns-meta text-[13px] mb-6 inline-block">← {{ __('attendee.profile.title') }}</a>

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('share.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('share.title') }}</h1>
        <p class="ns-body max-w-[60ch] mb-10">{{ __('share.lead') }}</p>

        <x-ns.share-block :registration="$registration" :heading="false" />
    </div>
</x-layouts.site>
