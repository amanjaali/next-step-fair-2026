<x-filament-widgets::widget>
    <x-filament::section :heading="__('admin.widgets.funnel')">
        <div class="flex flex-col gap-4">
            @foreach ($this->getSteps() as $step)
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $step['label'] }}</span>
                        <span class="text-gray-500 tabular-nums">{{ number_format($step['value']) }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-800">
                        <div class="h-2 bg-gray-950 dark:bg-white" style="width: {{ max($step['percent'], 1) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
