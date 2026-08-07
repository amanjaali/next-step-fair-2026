{{ __('notifications.email.rsvp_greeting', ['title' => $registration->position, 'name' => $registration->full_name], $locale) }}

{{ __($pending ? 'notifications.email.rsvp_pending' : 'notifications.email.rsvp_confirmed', [], $locale) }}
@unless ($pending)

{{ __('notifications.email.rsvp_badge_note', [], $locale) }}
{{ __('notifications.email.rsvp_ticket', [], $locale) }}: {{ $registration->ticket_ref }}
@endunless

{{ __('notifications.email.rsvp_venue', [], $locale) }}
{{ __('notifications.email.rsvp_venue_body', [], $locale) }}
{{ __('notifications.email.rsvp_interpretation', [], $locale) }}
@if ($sessions->isNotEmpty())

{{ __('notifications.email.rsvp_programme', [], $locale) }}
@foreach ($sessions as $session)
- {{ $session->timeLabel() }} {{ $session->getTranslation('title', $locale, true) }}
@endforeach
@endif

{{ __('notifications.email.rsvp_modify', [], $locale) }}: {{ $manageUrl }}
{{ __('notifications.email.rsvp_contact', ['email' => config('nextstep.contact.protocol')], $locale) }}

{{ __('notifications.email.rsvp_signoff', [], $locale) }}
{{ config('nextstep.event.venue.address.en') }}
