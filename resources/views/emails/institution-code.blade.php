@php($dir = config("nextstep.locales.{$user->locale}.dir", 'ltr'))
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F4F1EE;padding:32px 0;font-family:Arial,sans-serif">
    <tr><td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="background:#fff;max-width:560px;width:100%" dir="{{ $dir }}">
            <tr><td style="background:#050708;padding:22px 28px">
                <span style="color:#fff;font-size:15px;font-weight:bold;letter-spacing:.04em">
                    {{ config('nextstep.event.name') }}
                </span>
            </td></tr>
            <tr><td style="padding:32px 28px">
                <p style="margin:0 0 16px;font-size:15px;color:#050708">{{ __('institution.mail.greeting', ['name' => $user->firstName()], $user->locale) }}</p>
                <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#3A3B3D">{{ __('institution.mail.body', [], $user->locale) }}</p>
                <div style="background:#F4F1EE;padding:20px;text-align:center;margin-bottom:24px">
                    <span style="font-size:32px;font-weight:bold;letter-spacing:.22em;color:#050708;direction:ltr;unicode-bidi:isolate">{{ $code }}</span>
                </div>
                <p style="margin:0;font-size:13px;line-height:1.6;color:#6A6B6D">{{ __('institution.mail.ignore', [], $user->locale) }}</p>
            </td></tr>
        </table>
    </td></tr>
</table>
