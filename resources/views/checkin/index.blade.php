<x-checkin.layout :secret="$secret ?? false">
    <main class="ck-shell"
          data-scan-url="{{ $urls['scan'] }}"
          data-search-url="{{ $urls['search'] }}"
          data-sync-url="{{ $urls['sync'] }}"
          data-manifest-url="{{ $urls['manifest'] }}"
          data-manual-url="{{ $urls['manual'] }}"
          data-suggested-day="{{ $day }}"
          data-suggested-gate="{{ $gate }}"
          data-csrf="{{ csrf_token() }}"
          data-label-gate="{{ __('checkin.gate', ['gate' => ':gate', 'day' => ':day']) }}"
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
          data-label-check-in="{{ __('checkin.check_in') }}"
          data-label-synced="{{ __('checkin.synced') }}"
          data-label-offline="{{ __('checkin.offline', ['count' => ':count']) }}">

        {{-- First open: pick day + gate before the camera starts. --}}
        <section class="ck-setup" data-setup hidden>
            <div class="ck-setup__body">
                <p class="ck-setup__eyebrow">{{ __('checkin.title') }}</p>
                <h1 class="ck-setup__title">{{ __('checkin.setup_title') }}</h1>
                <p class="ck-setup__lead">{{ __('checkin.setup_lead') }}</p>

                <div class="ck-form">
                    <label class="ck-field">
                        <span class="ck-label">{{ __('checkin.day_selector') }}</span>
                        <select class="ck-select" data-setup-day>
                            @foreach ($days as $number)
                                <option value="{{ $number }}" @selected($day === $number)>{{ __('site.common.day', ['n' => $number]) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ck-field">
                        <span class="ck-label">{{ __('checkin.gate_selector') }}</span>
                        <select class="ck-select" data-setup-gate>
                            @foreach (['A', 'B', 'C'] as $option)
                                <option value="{{ $option }}" @selected($gate === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="button" class="ck-btn ck-btn--primary" data-setup-save>
                        {{ __('checkin.setup_start') }}
                    </button>
                </div>
            </div>
        </section>

        <div class="ck-workspace" data-workspace hidden>
            <header class="ck-top">
                <div class="ck-top__left">
                    <strong class="ck-top__gate" data-gate-label>{{ __('checkin.gate', ['gate' => $gate, 'day' => $day]) }}</strong>
                    <span class="ck-top__count" data-count>{{ $todayCount }}</span>
                </div>
                <div class="ck-top__right">
                    <span class="ck-sync" data-sync-state>{{ __('checkin.synced') }}</span>
                    <button type="button" class="ck-btn ck-btn--link ck-top__settings" data-open-settings>
                        {{ __('checkin.settings') }}
                    </button>
                </div>
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
                <button type="button" class="ck-btn ck-btn--link" data-open-search>{{ __('checkin.search_fallback') }}</button>
            </section>

            {{-- Manual fallback for a lost QR. --}}
            <section class="ck-search" data-search hidden>
                <input type="search" class="ck-input" data-search-input
                       placeholder="{{ __('checkin.search_placeholder') }}" autocomplete="off">
                <div class="ck-search__results" data-search-results></div>
            </section>

            <footer class="ck-foot">
                <p class="ck-foot__hint">{{ __('checkin.settings_hint') }}</p>

                @unless ($secret ?? false)
                    <form method="POST" action="{{ route('checkin.logout') }}">
                        @csrf
                        <button type="submit" class="ck-btn ck-btn--link">{{ __('checkin.sign_out') }}</button>
                    </form>
                @endunless
            </footer>
        </div>

        {{-- Change day / gate without leaving the page. --}}
        <section class="ck-settings" data-settings hidden>
            <div class="ck-settings__panel">
                <h2 class="ck-settings__title">{{ __('checkin.settings_title') }}</h2>
                <p class="ck-settings__lead">{{ __('checkin.settings_lead') }}</p>

                <div class="ck-form">
                    <label class="ck-field">
                        <span class="ck-label">{{ __('checkin.day_selector') }}</span>
                        <select class="ck-select" data-settings-day>
                            @foreach ($days as $number)
                                <option value="{{ $number }}">{{ __('site.common.day', ['n' => $number]) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ck-field">
                        <span class="ck-label">{{ __('checkin.gate_selector') }}</span>
                        <select class="ck-select" data-settings-gate>
                            @foreach (['A', 'B', 'C'] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="button" class="ck-btn ck-btn--primary" data-settings-save>
                        {{ __('checkin.settings_save') }}
                    </button>
                    <button type="button" class="ck-btn ck-btn--ghost" data-settings-cancel>
                        {{ __('checkin.settings_cancel') }}
                    </button>
                </div>
            </div>
        </section>
    </main>
</x-checkin.layout>
