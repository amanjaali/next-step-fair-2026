@props([
    'attendee' => null,
    'variant' => 'light',
    'class' => '',
])

@php
    /**
     * "Start your application" — but only for somebody who could.
     *
     * The scholarship is for students, and only a student account can apply. A
     * parent signed in to their own badge was still being shown the button, and
     * pressing it took them to a gate that told them no. That is the wrong order:
     * say who it is for first, and give them the thing they can actually do.
     *
     * A signed-out visitor keeps the button. They might be the student, and the
     * gate page is where the requirements are explained.
     */
    $isStudent = $attendee?->type === \App\Models\Registration::TYPE_STUDENT;
    $applicationIsForThem = $attendee === null || $isStudent;

    // What a parent can do instead: put it in front of the person it is for.
    $message = __('scholarship.not_for_you.message', [
        'url' => route('scholarship.home'),
    ]);

    $dark = $variant === 'dark';
@endphp

@if ($applicationIsForThem)
    <a href="{{ route('scholarship.apply') }}" {{ $attributes->merge(['class' => 'ns-btn ns-btn-magenta '.$class]) }}>
        {{ __('scholarship.home.cta') }}
    </a>
@else
    <div class="{{ $class }}">
        <div @class([
            'border-s-[6px] border-magenta px-5 py-4 max-w-[52ch]',
            'bg-white/10' => $dark,
            'bg-bone-200' => ! $dark,
        ])>
            <div @class([
                'font-[family-name:var(--ns-body)] text-[15px] font-bold mb-1',
                'text-white' => $dark,
            ])>{{ __('scholarship.not_for_you.title') }}</div>

            <p @class([
                'font-[family-name:var(--ns-body)] text-[14px] leading-[1.6] mb-4',
                'text-white/75' => $dark,
                'text-body-soft' => ! $dark,
            ])>{{ __('scholarship.not_for_you.body') }}</p>

            <div class="flex gap-3 flex-wrap">
                <a href="https://wa.me/?text={{ rawurlencode($message) }}" target="_blank" rel="noopener"
                   class="ns-btn ns-btn-magenta ns-btn-sm">{{ __('scholarship.not_for_you.send') }}</a>

                <a href="{{ route('register.fair', ['type' => 'student']) }}"
                   @class([
                       'ns-btn ns-btn-ghost ns-btn-sm',
                       '!text-white !border-white/40 hover:!bg-white/10' => $dark,
                   ])>{{ __('scholarship.not_for_you.register') }}</a>
            </div>
        </div>
    </div>
@endif
