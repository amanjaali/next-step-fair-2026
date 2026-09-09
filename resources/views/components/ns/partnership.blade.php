{{--
    "In partnership with", and the marks.

    One component because this block appears in four places — the badge PDF, the
    badge picture, the card people post, and the badge drawn on screen after
    registering — and the fourth was written out by hand, so when the marks were
    added to the other three it silently stayed as it was. A partner missing
    from the one screen a registrant actually looks at is the version they
    report.

    Sized with inline styles rather than utility classes, deliberately. Classes
    that appear for the first time in a new file are not in the compiled
    stylesheet until someone runs the front-end build, and the failure is not
    subtle: a ministry logo at its natural size is a metre wide and pushes the
    badge off the screen. This block has to be right on a checkout that has only
    been pulled.

    White ground behind the marks, always: they are supplied as coloured marks
    on white, and a ministry's seal knocked onto a magenta badge is not the mark
    any more.
--}}
@props(['height' => 30])

@php($marks = ns_partner_marks())

@if ($marks)
    <div {{ $attributes->merge(['class' => 'shrink-0']) }}
         style="display:flex;flex-direction:column;align-items:flex-end;gap:7px">

        <div style="font-family:var(--ns-body);font-size:9px;font-weight:800;letter-spacing:0.16em;text-transform:uppercase;color:rgba(255,255,255,0.7);white-space:nowrap">
            {{ __('site.common.in_partnership') }}
        </div>

        <div style="background:#fff;padding:7px 10px;display:flex;align-items:center;gap:10px">
            @foreach ($marks as $index => $mark)
                @if ($index > 0)
                    <span style="width:1px;height:{{ (int) ($height * 0.72) }}px;background:rgba(5,7,8,0.18)"></span>
                @endif
                <img src="{{ $mark['src'] }}" alt="{{ $mark['name'] }}"
                     style="height:{{ (int) $height }}px;width:auto;max-width:{{ (int) ($height * 2.5) }}px;object-fit:contain;display:block">
            @endforeach
        </div>
    </div>
@endif
