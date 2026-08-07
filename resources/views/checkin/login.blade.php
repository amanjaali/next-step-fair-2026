<x-checkin.layout :title="__('checkin.login_title')">
    <main class="ck-shell ck-shell--center">
        <div class="ck-login">
            <img src="{{ asset('assets/brand/nextstep-white-sm.png') }}" alt="Next Step" class="ck-login__logo">
            <h1 class="ck-login__title">{{ __('checkin.login_title') }}</h1>
            <p class="ck-login__lead">{{ __('checkin.login_lead') }}</p>

            @if ($errors->any())
                <div class="ck-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('checkin.login.attempt') }}" class="ck-form">
                @csrf
                <label class="ck-field">
                    <span class="ck-label">{{ __('checkin.email') }}</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="ck-input">
                </label>
                <label class="ck-field">
                    <span class="ck-label">{{ __('checkin.password') }}</span>
                    <input type="password" name="password" required autocomplete="current-password" class="ck-input">
                </label>
                <button type="submit" class="ck-btn ck-btn--primary">{{ __('checkin.sign_in') }}</button>
            </form>
        </div>
    </main>
</x-checkin.layout>
