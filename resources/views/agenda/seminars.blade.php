<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.footer.links.seminars')" :lead="__('site.pages.agenda.lead', ['count' => $sessionsByDay->flatten()->count()])">
            @foreach ($sessionsByDay as $day => $sessions)
                <section class="mb-12">
                    <div class="flex items-baseline gap-5 mb-2">
                        <span class="ns-eyebrow">{{ __('site.common.day', ['n' => $day]) }} · <span class="ns-num">{{ ns_day_date($day) }}</span></span>
                        <span class="ns-rule"></span>
                    </div>
                    <div class="flex flex-col">
                        @foreach ($sessions as $session)
                            <x-ns.session-row :session="$session" />
                        @endforeach
                    </div>
                </section>
            @endforeach
        </x-ns.page-head>
    </div>
</x-layouts.site>
