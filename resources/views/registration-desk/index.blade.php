<x-registration-desk.layout>
    <main class="rg-shell"
          data-search-url="{{ $urls['search'] }}"
          data-store-url="{{ $urls['store'] }}"
          data-csrf="{{ csrf_token() }}"
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
          data-label-created-walk-ins="{{ __('registration_desk.created_walk_ins') }}"
          data-label-deleted="{{ __('registration_desk.deleted') }}"
          data-label-showing-count="{{ __('registration_desk.showing_count', ['shown' => ':shown', 'total' => ':total']) }}"
          data-label-fill-required="{{ __('registration_desk.fill_required') }}"
          data-label-phone-invalid="{{ __('registration_desk.phone_invalid') }}"
          data-label-add-member="{{ __('registration_desk.add_member') }}"
          data-label-remove-member="{{ __('registration_desk.remove_member') }}"
          data-label-member-name-placeholder="{{ __('registration_desk.member_name_placeholder') }}"
          data-label-history-by="{{ __('registration_desk.history_by', ['user' => ':user', 'field' => ':field', 'old' => ':old', 'new' => ':new']) }}"
          data-label-history-empty="{{ __('registration_desk.history_empty') }}"
          data-label-field-name="{{ __('registration_desk.field_name') }}"
          data-label-field-phone-country="{{ __('registration_desk.field_phone_country') }}"
          data-label-field-phone="{{ __('registration_desk.field_phone') }}"
          data-label-field-type="{{ __('registration_desk.field_type') }}"
          data-label-type-visitor="{{ __('registration_desk.type_visitor') }}"
          data-label-type-student="{{ __('registration_desk.type_student') }}"
          data-label-type-parent="{{ __('registration_desk.type_parent') }}">

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

                    <label class="rg-field">
                        <span class="rg-label">{{ __('registration_desk.field_type') }} *</span>
                        <select class="rg-select" data-field="type" data-required>
                            <option value="visitor">{{ __('registration_desk.type_visitor') }}</option>
                            <option value="parent">{{ __('registration_desk.type_parent') }}</option>
                            <option value="student">{{ __('registration_desk.type_student') }}</option>
                        </select>
                    </label>

                    <div class="rg-row">
                        <label class="rg-field rg-field--narrow">
                            <span class="rg-label">{{ __('registration_desk.field_phone_country') }} *</span>
                            <select class="rg-select" data-field="phone_country" data-required></select>
                        </label>
                        <label class="rg-field">
                            <span class="rg-label">{{ __('registration_desk.field_phone') }} *</span>
                            <input type="tel" class="rg-input" data-field="phone" data-required inputmode="numeric" maxlength="11">
                        </label>
                    </div>

                    <div data-members-feature>
                        <label class="rg-checkbox">
                            <input type="checkbox" data-add-members-toggle>
                            {{ __('registration_desk.add_members_toggle') }}
                        </label>

                        <div class="rg-members" data-members hidden>
                            <div data-member-rows></div>
                            <button type="button" class="rg-btn rg-btn--ghost" data-add-member>
                                {{ __('registration_desk.add_member') }}
                            </button>
                        </div>
                    </div>

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
