<x-filament-panels::page>
    <x-filament::section heading="Status">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            @foreach ($this->statusLines() as $label => $value)
                <tr style="border-top:1px solid rgba(127,127,127,0.18);">
                    <th scope="row" style="text-align:start; font-weight:500; padding:0.5rem 1.5rem 0.5rem 0; width:14rem; vertical-align:top; opacity:0.7;">{{ $label }}</th>
                    <td style="padding:0.5rem 0; overflow-wrap:anywhere;">{{ $value }}</td>
                </tr>
            @endforeach
        </table>
    </x-filament::section>

    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top:1.5rem;">
            <x-filament::button type="submit">Save</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
