<x-layouts.site :title="$title" :navKey="$navKey" :track="$track">
    <div class="ns-wrap max-w-[720px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('rsvp.manage.title') }}</h1>
        <p class="ns-body max-w-[60ch] mb-8">{{ __('rsvp.manage.lead') }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        <div class="ns-card mb-6">
            @foreach ([
                __('register.step1.name') => $registration->full_name,
                __('rsvp.step1.position') => $registration->position,
                __('site.common.institution') => $registration->organization,
                __('site.common.email_address') => $registration->email,
                __('register.done.ticket', ['id' => '']) => $registration->ticket_ref,
                __('site.common.track') => $registration->status,
            ] as $key => $value)
                @continue(! $value)
                <div class="flex justify-between gap-4 font-[family-name:var(--ns-body)] text-[14.5px] border-b border-[rgba(5,7,8,0.1)] py-3">
                    <span class="text-slate">{{ $key }}</span>
                    <span class="font-bold text-end">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        @unless ($registration->isCancelled())
            <form method="POST" action="{{ \Illuminate\Support\Facades\URL::signedRoute('rsvp.cancel', ['ticket' => $registration->ticket_id]) }}">
                @csrf
                <button type="submit" class="ns-btn ns-btn-ghost !text-crimson !border-crimson">{{ __('rsvp.manage.cancel') }}</button>
            </form>
        @endunless
    </div>
</x-layouts.site>
