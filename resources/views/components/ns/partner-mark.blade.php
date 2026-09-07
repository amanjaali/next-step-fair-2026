@props([
    'slug',          // the organisation this mark belongs to
    'src',           // the file, already resolved through ns_brand()
    'name',          // what it is, for the alt text and the tooltip
    'kicker' => null, // "In partnership with", where the context needs it
])

{{--
    A partnership mark, linked to the partner behind it.

    The same mark appears in the header, in the footer strip and on the partners
    page, and until now it went nowhere in all three. It links to that partner's
    own page — but only once somebody has written one: a logo that opens an
    empty page is worse than a logo that does nothing, so an unwritten partner
    stays a picture and starts linking the moment their page has words in it.
--}}
@php $href = \App\Models\Organization::partnerLink($slug); @endphp

@if ($href)
    <a href="{{ $href }}" title="{{ $name }}" class="block shrink-0">
        <img src="{{ $src }}" alt="{{ $kicker ? $kicker.' — '.$name : $name }}" {{ $attributes }}>
    </a>
@else
    <img src="{{ $src }}" alt="{{ $kicker ? $kicker.' — '.$name : $name }}" title="{{ $name }}" {{ $attributes }}>
@endif
