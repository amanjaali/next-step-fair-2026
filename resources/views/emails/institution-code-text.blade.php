{{ __('institution.mail.greeting', ['name' => $user->firstName()], $user->locale) }}

{{ __('institution.mail.body', [], $user->locale) }}

{{ $code }}

{{ __('institution.mail.ignore', [], $user->locale) }}
