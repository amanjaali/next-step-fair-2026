<x-registration-desk.layout :title="__('registration_desk.login_title')">
    <main class="rg-shell rg-shell--center">
        <div class="rg-login">
            <img src="{{ ns_brand('logo_light', 'assets/brand/nextstep-white-sm.png') }}" alt="Next Step" class="rg-login__logo">
            <h1 class="rg-login__title">{{ __('registration_desk.login_title') }}</h1>
            <p class="rg-login__lead">{{ __('registration_desk.login_lead') }}</p>

            @if ($errors->any())
                <div class="rg-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('registration.login.attempt') }}" class="rg-form">
                @csrf
                <label class="rg-field">
                    <span class="rg-label">{{ __('registration_desk.email') }}</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="rg-input">
                </label>
                <label class="rg-field">
                    <span class="rg-label">{{ __('registration_desk.password') }}</span>
                    <input type="password" name="password" required autocomplete="current-password" class="rg-input">
                </label>
                <button type="submit" class="rg-btn rg-btn--primary">{{ __('registration_desk.sign_in') }}</button>
            </form>
        </div>
    </main>
</x-registration-desk.layout>
