<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap pt-[clamp(24px,3vw,40px)] pb-[clamp(72px,10vw,140px)]">
        @include('institution.partials.nav', ['current' => 'students'])

        <h1 class="ns-h1 !text-[clamp(28px,3.6vw,40px)] mb-3">{{ __('institution.students.title') }}</h1>
        <p class="ns-body max-w-[64ch] mb-3">{{ __('institution.students.lead') }}</p>
        <p class="ns-meta mb-8">{{ __('institution.students.shared_note', ['count' => $sharedCount]) }}</p>

        <form method="GET" class="flex gap-3 flex-wrap mb-8 items-end">
            <label>
                <span class="ns-label">{{ __('institution.students.filter_field') }}</span>
                <select name="field" class="ns-select !w-[240px]" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach ($fields as $field)
                        <option value="{{ $field->id }}" @selected(request('field') == $field->id)>{{ $field->t('name') }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="ns-label">{{ __('institution.students.filter_min') }}</span>
                <select name="min" class="ns-select !w-[150px] ns-num" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach ([50, 60, 70, 80] as $min)
                        <option value="{{ $min }}" @selected(request('min') == $min)>{{ $min }}+</option>
                    @endforeach
                </select>
            </label>
        </form>

        @forelse ($matches as $match)
            @php($student = $match->registration)
            <div class="grid gap-5 border-t border-[rgba(5,7,8,0.14)] py-5 items-start lg:grid-cols-[80px_minmax(0,1fr)_240px]">
                <div class="ns-num font-[family-name:var(--ns-display)] text-[26px] font-bold text-cobalt leading-none">
                    {{ $match->score }}
                </div>

                <div class="min-w-0">
                    {{-- Consent decides whether a name is shown. Without it the row
                         still counts toward demand, but nobody can be contacted. --}}
                    <div class="font-[family-name:var(--ns-body)] text-[16px] font-semibold mb-1">
                        @if ($student->share_with_institutions)
                            {{ $student->full_name }}
                        @else
                            <span class="text-slate">{{ __('institution.students.anonymous') }}</span>
                        @endif
                    </div>
                    <div class="ns-meta text-[13px] mb-2">
                        {{ collect([
                            $student->city,
                            $student->degree_level ? __("taxonomy.levels.{$student->degree_level}") : null,
                            $student->grade_band ? __("taxonomy.grades.{$student->grade_band}") : null,
                        ])->filter()->join(' · ') }}
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        @foreach ($student->fields as $field)
                            <span class="ns-typechip ns-typechip-conf">{{ $field->t('name') }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="lg:text-end">
                    @if ($student->share_with_institutions)
                        <div class="ns-eyebrow !text-[9.5px] !text-[#0E9B94] mb-1">{{ __('institution.students.consented') }}</div>
                        <div class="font-[family-name:var(--ns-body)] text-[13.5px] ns-num">{{ $student->phone_country }} {{ $student->phone }}</div>
                        @if ($student->email)
                            <div class="font-[family-name:var(--ns-body)] text-[13.5px] break-all">{{ $student->email }}</div>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <p class="ns-body text-body-soft py-8">{{ __('institution.students.none') }}</p>
        @endforelse

        <div class="mt-8">{{ $matches->links() }}</div>
    </div>
</x-layouts.site>
