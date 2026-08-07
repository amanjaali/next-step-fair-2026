{{-- Persistent across pages; the choice is stored in a year-long cookie. --}}
<div class="flex border border-[rgba(5,7,8,0.2)] shrink-0" role="group" aria-label="{{ __('site.nav.language') }}">
    @foreach (config('nextstep.locales') as $code => $conf)
        <form method="GET" action="{{ route('locale.switch', $code) }}">
            <input type="hidden" name="to" value="{{ request()->path() }}">
            <input type="hidden" name="query" value="{{ request()->getQueryString() }}">
            <button type="submit"
                    class="font-[family-name:'Space_Grotesk'] text-[11px] font-semibold tracking-[0.14em] py-[7px] px-[9px] border-0 cursor-pointer {{ $code === $locale ? 'bg-ink text-white' : 'bg-transparent text-ink' }}"
                    lang="{{ $conf['html_lang'] }}"
                    aria-label="{{ $conf['label'] }}"
                    @if($code === $locale) aria-current="true" @endif>{{ $conf['code'] }}</button>
        </form>
    @endforeach
</div>
