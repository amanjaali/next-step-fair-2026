{{-- Table-based HTML for Outlook, with an RTL mirror for Kurdish and Arabic. --}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('notifications.email.rsvp_subject', [], $locale) }}</title>
</head>
<body style="margin:0;padding:0;background:#F4F1EE;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4F1EE;">
    <tr>
        <td align="center" style="padding:24px 12px;">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="width:600px;max-width:100%;background:#ffffff;border:1px solid #dcd8d4;">

                <tr>
                    <td style="background:#050708;padding:28px 32px;" dir="{{ $dir }}">
                        <div style="font-family:Arial,Helvetica,sans-serif;font-size:19px;font-weight:bold;color:#ffffff;line-height:1.2;">
                            Next Step Conference {{ config('nextstep.event.year') }}
                        </div>
                        <div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#8f9193;padding-top:8px;">
                            {{ __('site.common.day', ['n' => 1], $locale) }} · 28 September 2026 · Hall B
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px 32px 8px;" dir="{{ $dir }}">
                        <p style="font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:1.6;color:#1A1C1D;margin:0 0 18px;">
                            {{ __('notifications.email.rsvp_greeting', ['title' => $registration->position, 'name' => $registration->full_name], $locale) }}
                        </p>
                        <p style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;color:#1A1C1D;margin:0 0 18px;">
                            {{ __($pending ? 'notifications.email.rsvp_pending' : 'notifications.email.rsvp_confirmed', [], $locale) }}
                        </p>

                        @unless ($pending)
                            <p style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;color:#1A1C1D;margin:0 0 18px;">
                                {{ __('notifications.email.rsvp_badge_note', [], $locale) }}
                            </p>
                        @endunless
                    </td>
                </tr>

                @unless ($pending)
                    <tr>
                        <td style="padding:8px 32px 24px;" dir="{{ $dir }}">
                            {{-- Badge block: name, institution and the QR, inline. --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#2C4BE0;">
                                <tr>
                                    <td style="padding:24px;">
                                        <div style="font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;color:#ffffff;line-height:1.2;">
                                            {{ $registration->full_name }}
                                        </div>
                                        <div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;color:#ffffff;padding-top:6px;">
                                            {{ $registration->organization }}
                                        </div>
                                        <div style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#d5ddfb;padding-top:4px;">
                                            {{ $registration->position }} · {{ strtoupper($registration->type) }}
                                        </div>
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:18px;background:#ffffff;">
                                            <tr>
                                                <td style="padding:12px;">
                                                    <img src="{{ $qrDataUri }}" width="150" height="150" alt="QR"
                                                         style="display:block;width:150px;height:150px;" />
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:1px;color:#ffffff;padding-top:12px;">
                                            {{ __('notifications.email.rsvp_ticket', [], $locale) }}: {{ $registration->ticket_ref }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endunless

                @if ($sessions->isNotEmpty())
                    <tr>
                        <td style="padding:0 32px 24px;" dir="{{ $dir }}">
                            <div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#4A4B4D;padding-bottom:10px;">
                                {{ __('notifications.email.rsvp_programme', [], $locale) }}
                            </div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                @foreach ($sessions as $session)
                                    <tr>
                                        <td style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#1A1C1D;padding:8px 0;border-bottom:1px solid #eae6e2;">
                                            <strong style="direction:ltr;unicode-bidi:isolate;">{{ $session->timeLabel() }}</strong>
                                            &nbsp;{{ $session->getTranslation('title', $locale, true) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif

                <tr>
                    <td style="padding:0 32px 24px;" dir="{{ $dir }}">
                        <div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#4A4B4D;padding-bottom:10px;">
                            {{ __('notifications.email.rsvp_venue', [], $locale) }}
                        </div>
                        <p style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;color:#1A1C1D;margin:0 0 12px;">
                            {{ __('notifications.email.rsvp_venue_body', [], $locale) }}
                        </p>
                        <p style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;color:#1A1C1D;margin:0 0 12px;">
                            {{ __('notifications.email.rsvp_interpretation', [], $locale) }}
                        </p>
                        @unless ($pending)
                            <p style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;color:#4A4B4D;margin:0;">
                                {{ __('notifications.email.rsvp_calendar', [], $locale) }}
                            </p>
                        @endunless
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 32px 32px;" dir="{{ $dir }}">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background:#050708;">
                                    <a href="{{ $manageUrl }}"
                                       style="display:inline-block;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;padding:14px 22px;">
                                        {{ __('notifications.email.rsvp_modify', [], $locale) }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <p style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.6;color:#4A4B4D;margin:18px 0 0;">
                            {{ __('notifications.email.rsvp_contact', ['email' => config('nextstep.contact.protocol')], $locale) }}
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background:#F4F1EE;padding:20px 32px;border-top:1px solid #dcd8d4;" dir="{{ $dir }}">
                        <div style="font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#4A4B4D;">
                            {{ __('notifications.email.rsvp_signoff', [], $locale) }}<br />
                            {{ config('nextstep.event.venue.address.'.$locale, config('nextstep.event.venue.address.en')) }}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
