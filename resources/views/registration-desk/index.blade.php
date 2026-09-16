<x-registration-desk.layout>
    <main class="rg-shell"
          data-search-url="{{ $urls['search'] }}"
          data-store-url="{{ $urls['store'] }}"
          data-csrf="{{ csrf_token() }}"
          data-cities="{{ implode(',', config('nextstep.cities')) }}"
          data-phone-countries="{{ implode(',', array_keys(config('nextstep.phone.countries'))) }}"
          data-default-phone-country="{{ config('nextstep.phone.default_country') }}"
          data-label-no-results="{{ __('registration_desk.no_results') }}"
          data-label-walk-in="{{ __('registration_desk.walk_in') }}"
          data-label-checked-in-today="{{ __('registration_desk.checked_in_today') }}"
          data-label-form-title-new="{{ __('registration_desk.form_title_new') }}"
          data-label-form-title-edit="{{ __('registration_desk.form_title_edit') }}"
          data-label-saving="{{ __('registration_desk.saving') }}"
          data-label-save="{{ __('registration_desk.save') }}"
          data-label-delete-confirm="{{ __('registration_desk.delete_confirm') }}"
          data-label-delete-window-hint="{{ __('registration_desk.delete_window_hint') }}"
          data-label-delete-window-expired-hint="{{ __('registration_desk.delete_window_expired_hint') }}"
          data-label-saved="{{ __('registration_desk.saved') }}"
          data-label-created-walk-in="{{ __('registration_desk.created_walk_in') }}"
          data-label-deleted="{{ __('registration_desk.deleted') }}"
          data-label-showing-count="{{ __('registration_desk.showing_count', ['shown' => ':shown', 'total' => ':total']) }}"
          data-label-fill-required="{{ __('registration_desk.fill_required') }}">

        <header class="rg-top">
            <strong class="rg-top__title">{{ __('registration_desk.title') }}</strong>
            <form method="POST" action="{{ route('registration.logout') }}">
                @csrf
                <button type="submit" class="rg-btn rg-btn--link">{{ __('registration_desk.sign_out') }}</button>
            </form>
        </header>

        <section class="rg-search">
            <p class="rg-search__lead">{{ __('registration_desk.search_lead') }}</p>
            <input type="search" class="rg-input" data-search-input
                   placeholder="{{ __('registration_desk.search_placeholder') }}" autocomplete="off">

            <div class="rg-tabs" data-filter-tabs>
                <button type="button" class="rg-tab rg-tab--active" data-filter="all">{{ __('registration_desk.filter_all') }}</button>
                <button type="button" class="rg-tab" data-filter="student">{{ __('registration_desk.filter_student') }}</button>
                <button type="button" class="rg-tab" data-filter="parent">{{ __('registration_desk.filter_parent') }}</button>
            </div>

            <button type="button" class="rg-btn rg-btn--primary rg-search__new" data-open-new>
                {{ __('registration_desk.new_walk_in') }}
            </button>
        </section>

        <section class="rg-list" data-results></section>

        <div class="rg-more">
            <p class="rg-more__count" data-results-count></p>
            <button type="button" class="rg-btn rg-btn--ghost" data-load-more hidden>
                {{ __('registration_desk.load_more') }}
            </button>
        </div>

        {{-- Create / edit panel --}}
        <section class="rg-panel" data-panel hidden>
            <div class="rg-panel__body">
                <div class="rg-panel__head">
                    <h2 class="rg-panel__title" data-panel-title>{{ __('registration_desk.form_title_new') }}</h2>
                    <button type="button" class="rg-btn rg-btn--link" data-panel-close>{{ __('registration_desk.close') }}</button>
                </div>

                <div class="rg-form" data-form>
                    <label class="rg-field">
                        <span class="rg-label">{{ __('registration_desk.field_name') }} *</span>
                        <input type="text" class="rg-input" data-field="full_name" data-required maxlength="120">
                    </label>

                    <div class="rg-row">
                        <label class="rg-field">
                            <span class="rg-label">{{ __('registration_desk.field_type') }} *</span>
                            <select class="rg-select" data-field="type" data-required>
                                <option value="student">{{ __('registration_desk.type_student') }}</option>
                                <option value="parent">{{ __('registration_desk.type_parent') }}</option>
                            </select>
                        </label>

                        <label class="rg-field">
                            <span class="rg-label">{{ __('registration_desk.field_locale') }} *</span>
                            <select class="rg-select" data-field="locale" data-required>
                                <option value="en">EN</option>
                                <option value="ku">KU</option>
                                <option value="ar">AR</option>
                            </select>
                        </label>
                    </div>

                    <div class="rg-row">
                        <label class="rg-field rg-field--narrow">
                            <span class="rg-label">{{ __('registration_desk.field_phone_country') }} *</span>
                            <select class="rg-select" data-field="phone_country" data-required></select>
                        </label>
                        <label class="rg-field">
                            <span class="rg-label">{{ __('registration_desk.field_phone') }} *</span>
                            <input type="tel" class="rg-input" data-field="phone" data-required>
                        </label>
                    </div>

                    <label class="rg-field">
                        <span class="rg-label">{{ __('registration_desk.field_email') }}</span>
                        <input type="email" class="rg-input" data-field="email">
                    </label>

                    <label class="rg-field">
                        <span class="rg-label">{{ __('registration_desk.field_city') }} *</span>
                        <select class="rg-select" data-field="city" data-required></select>
                    </label>

                    <fieldset class="rg-field" data-days-field>
                        <legend class="rg-label">{{ __('registration_desk.field_days') }} *</legend>
                        <div class="rg-days">
                            <label class="rg-day"><input type="checkbox" value="1" data-day> 1</label>
                            <label class="rg-day"><input type="checkbox" value="2" data-day> 2</label>
                            <label class="rg-day"><input type="checkbox" value="3" data-day> 3</label>
                        </div>
                    </fieldset>

                    <label class="rg-field" data-field-wrap="school_name">
                        <span class="rg-label">{{ __('registration_desk.field_school') }} *</span>
                        <input type="text" class="rg-input" data-field="school_name" data-required>
                    </label>

                    <label class="rg-field" data-field-wrap="relationship">
                        <span class="rg-label">{{ __('registration_desk.field_relationship') }} *</span>
                        <input type="text" class="rg-input" data-field="relationship" data-required>
                    </label>

                    <label class="rg-field">
                        <span class="rg-label">{{ __('registration_desk.field_notes') }}</span>
                        <textarea class="rg-input" rows="2" data-field="notes"></textarea>
                    </label>

                    <p class="rg-hint" data-delete-hint></p>

                    <div class="rg-panel__actions">
                        <button type="button" class="rg-btn rg-btn--danger" data-delete hidden>
                            {{ __('registration_desk.delete') }}
                        </button>
                        <button type="button" class="rg-btn rg-btn--primary" data-save>
                            {{ __('registration_desk.save') }}
                        </button>
                    </div>

                    <div class="rg-history" data-history hidden>
                        <h3 class="rg-history__title">{{ __('registration_desk.history') }}</h3>
                        <div data-history-list></div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-registration-desk.layout>
