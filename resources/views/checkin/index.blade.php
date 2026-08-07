<x-checkin.layout>
    <main class="ck-shell"
          data-scan-url="{{ route('checkin.scan') }}"
          data-search-url="{{ route('checkin.search') }}"
          data-sync-url="{{ route('checkin.sync') }}"
          data-manifest-url="{{ route('checkin.offline') }}"
          data-manual-url="{{ url('/checkin/manual') }}"
          data-day="{{ $day }}"
          data-csrf="{{ csrf_token() }}"
          data-label-valid="{{ __('checkin.states.valid') }}"
          data-label-valid-conference="{{ __('checkin.states.valid_conference') }}"
          data-label-already="{{ __('checkin.states.already') }}"
          data-label-invalid="{{ __('checkin.states.invalid') }}"
          data-label-cancelled="{{ __('checkin.states.cancelled') }}"
          data-label-wrong-day="{{ __('checkin.states.wrong_day') }}"
          data-label-pending="{{ __('checkin.states.pending') }}"
          data-label-invalid-detail="{{ __('checkin.invalid_detail') }}"
          data-label-camera-denied="{{ __('checkin.camera_denied') }}"
          data-label-no-results="{{ __('checkin.no_results') }}"
          data-label-synced="{{ __('checkin.synced') }}"
          data-label-offline="{{ __('checkin.offline', ['count' => ':count']) }}">

        <header class="ck-top">
            <div class="ck-top__left">
                <strong class="ck-top__gate">{{ __('checkin.gate', ['gate' => $gate, 'day' => $day]) }}</strong>
                <span class="ck-top__count" data-count>{{ $todayCount }}</span>
            </div>
            <span class="ck-sync" data-sync-state>{{ __('checkin.synced') }}</span>
        </header>

        {{-- Camera viewport. The reader draws into #ck-reader. --}}
        <section class="ck-camera">
            <div id="ck-reader" class="ck-camera__view"></div>
            <div class="ck-camera__frame" aria-hidden="true"></div>
            <span class="ck-camera__hint" data-camera-hint>{{ __('checkin.camera') }}</span>
        </section>

        {{-- Result panel: green, amber or red, tap to dismiss. --}}
        <button type="button" class="ck-result" data-result hidden>
            <span class="ck-result__status" data-result-status></span>
            <span class="ck-result__title" data-result-title></span>
            <span class="ck-result__detail" data-result-detail></span>
        </button>

        <section class="ck-actions">
            <button type="button" class="ck-btn ck-btn--ghost" data-rescan>{{ __('checkin.scan_next') }}</button>
            <button type="button" class="ck-btn ck-btn--link" data-open-search>{{ __('checkin.search_fallback') }}</button>
        </section>

        {{-- Manual fallback for a lost QR. --}}
        <section class="ck-search" data-search hidden>
            <input type="search" class="ck-input" data-search-input
                   placeholder="{{ __('checkin.search_placeholder') }}" autocomplete="off">
            <div class="ck-search__results" data-search-results></div>
        </section>

        <footer class="ck-foot">
            <form method="GET" action="{{ route('checkin.index') }}" class="ck-foot__form">
                <label class="ck-foot__field">
                    <span>{{ __('checkin.day_selector') }}</span>
                    <select name="day" class="ck-select" onchange="this.form.submit()">
                        @foreach ($days as $number)
                            <option value="{{ $number }}" @selected($day === $number)>{{ __('site.common.day', ['n' => $number]) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="ck-foot__field">
                    <span>{{ __('checkin.gate_selector') }}</span>
                    <select name="gate" class="ck-select" onchange="this.form.submit()">
                        @foreach (['A', 'B', 'C'] as $option)
                            <option value="{{ $option }}" @selected($gate === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
            </form>

            <form method="POST" action="{{ route('checkin.logout') }}">
                @csrf
                <button type="submit" class="ck-btn ck-btn--link">{{ __('checkin.sign_out') }}</button>
            </form>
        </footer>
    </main>
</x-checkin.layout>
