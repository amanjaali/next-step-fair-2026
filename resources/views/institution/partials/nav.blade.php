{{-- Portal sub-navigation. Deliberately flat: four things a recruiter does. --}}
@php($current = $current ?? '')
<div class="border-b border-[rgba(5,7,8,0.14)] mb-9">
    <div class="flex items-end justify-between gap-6 flex-wrap">
        <nav class="flex gap-[clamp(14px,2vw,28px)] flex-wrap" aria-label="{{ __('institution.nav.portal') }}">
            @foreach ([
                'dashboard' => route('portal.dashboard'),
                'profile' => route('portal.profile'),
                'students' => route('portal.students'),
                'leads' => route('portal.leads'),
                'scanner' => route('portal.scanner'),
            ] as $key => $url)
                <a href="{{ $url }}"
                   @class([
                       'font-[family-name:var(--ns-body)] text-[14.5px] font-medium py-3 border-b-2 whitespace-nowrap',
                       'text-ink border-cobalt' => $current === $key,
                       'text-slate border-transparent hover:text-ink' => $current !== $key,
                   ])>{{ __("institution.nav.$key") }}</a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('portal.signout') }}" class="pb-2">
            @csrf
            <button type="submit" class="ns-meta hover:underline bg-transparent border-0 cursor-pointer p-0">
                {{ __('institution.nav.sign_out') }}
            </button>
        </form>
    </div>
</div>
