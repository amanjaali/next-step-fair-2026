<?php if (isset($component)) { $__componentOriginalfefb4fd9b7004fa65f70c415ac76903e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfefb4fd9b7004fa65f70c415ac76903e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.site','data' => ['title' => $title,'navKey' => $navKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.site'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'navKey' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($navKey)]); ?>

    <div class="pb-[clamp(72px,10vw,140px)]">
        <?php if (isset($component)) { $__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.page-head','data' => ['kicker' => config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'),'title' => __('site.pages.floorplan.title'),'lead' => __('site.pages.floorplan.lead', ['booths' => $boothCount]),'breadcrumb' => [
                            ['label' => __('site.pages.directory.title'), 'url' => route('universities')],
                            ['label' => __('site.pages.floorplan.title')],
                        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.page-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kicker' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city')),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.floorplan.title')),'lead' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.floorplan.lead', ['booths' => $boothCount])),'breadcrumb' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                            ['label' => __('site.pages.directory.title'), 'url' => route('universities')],
                            ['label' => __('site.pages.floorplan.title')],
                        ])]); ?>

            <div class="flex gap-2 flex-wrap mb-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $halls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('floorplan', ['hall' => $code])); ?>"
                       class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-chip ns-chip-ink', 'is-on' => $hall->code === $code]); ?>"><?php echo e($h->t('name')); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="ns-card mb-8">
                <div class="grid gap-[10px] sm:grid-cols-[1.35fr_1fr] grid-rows-[200px_150px] mb-[10px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['A', 'B', 'C']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $h = $halls[$code] ?? null; ?>
                        <?php if(! $h) continue; ?>
                        <?php $on = $hall->code === $code; ?>
                        <a href="<?php echo e(route('floorplan', ['hall' => $code])); ?>"
                           class="<?php echo \Illuminate\Support\Arr::toCssClasses(['p-[22px] flex flex-col justify-between text-start', 'row-span-2' => $code === 'A']); ?>"
                           style="background:<?php echo e($on ? $h->color : $h->color.'1f'); ?>;color:<?php echo e($on ? '#fff' : '#050708'); ?>;border:2px solid <?php echo e($h->color); ?><?php echo e($on ? '' : '59'); ?>">
                            <div class="flex justify-between items-start gap-3">
                                <span class="font-[family-name:var(--ns-display)] text-xs font-bold tracking-[0.2em]">HALL <?php echo e($h->code); ?></span>
                                <span class="font-[family-name:var(--ns-body)] text-xs opacity-85"><?php echo e($h->t('meta')); ?></span>
                            </div>
                            <div>
                                <div class="font-[family-name:var(--ns-display)] text-[clamp(18px,2vw,26px)] font-semibold leading-[1.1] mb-2"><?php echo e($h->t('name')); ?></div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="grid gap-[10px] sm:grid-cols-2 xl:grid-cols-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $servicePoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-bone-200 px-[18px] py-4">
                            <div class="ns-eyebrow !text-[10.5px] mb-[7px]"><?php echo e($point->code); ?></div>
                            <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold leading-[1.3]"><?php echo e($point->t('name')); ?></div>
                            <div class="ns-meta text-[12.5px] mt-1"><?php echo e($point->t('kind')); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="flex gap-5 flex-wrap items-center border-y border-[rgba(5,7,8,0.14)] py-[18px] mb-10">
                <span class="ns-eyebrow !text-[10.5px]"><?php echo e(__('site.pages.floorplan.zone_key')); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $halls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="flex items-center gap-[9px] font-[family-name:var(--ns-body)] text-[13px]">
                        <span class="w-4 h-4 flex-none" style="background:<?php echo e($h->color); ?>"></span>
                        <span class="font-[family-name:var(--ns-display)] font-bold text-xs"><?php echo e($code); ?></span>
                        <span><?php echo e($h->t('name')); ?></span>
                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="ns-meta text-[12.5px] ms-auto"><?php echo e(__('site.pages.floorplan.magenta_note')); ?></span>
            </div>

            <section class="mb-11">
                <div class="flex items-baseline justify-between gap-6 flex-wrap mb-2">
                    <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]"><?php echo e($hall->t('name')); ?></h2>
                    <span class="ns-meta text-[13.5px]"><?php echo e($hall->t('meta')); ?></span>
                </div>
                <p class="ns-body max-w-[70ch] mb-7"><?php echo e($hall->t('description')); ?></p>

                <div class="ns-hairgrid sm:grid-cols-2 xl:grid-cols-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $hall->booths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booth): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-5 pb-[22px] flex gap-[14px] items-start">
                            <span class="font-[family-name:var(--ns-display)] text-xs font-bold tracking-[0.06em] text-white px-2 py-[6px] flex-none"
                                  style="background:<?php echo e($hall->color); ?>"><?php echo e($booth->code); ?></span>
                            <div class="min-w-0">
                                <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold leading-[1.3] mb-1"><?php echo e($booth->t('name')); ?></div>
                                <div class="ns-meta text-[12.5px] leading-[1.45]"><?php echo e($booth->t('kind')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <div class="grid gap-8 lg:grid-cols-2">
                <div class="ns-card">
                    <div class="ns-eyebrow !text-[11px] mb-4"><?php echo e(__('site.pages.floorplan.getting_in')); ?></div>
                    <div class="flex flex-col gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Models\Setting::get('access_notes', [])[app()->getLocale()] ?? \App\Models\Setting::get('access_notes', [])['en'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex gap-[14px] items-start">
                                <span class="ns-bar bg-magenta mt-[9px]"></span>
                                <span class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.6] text-body-soft"><?php echo e($note); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="ns-panel-ink flex flex-col">
                    <div class="ns-eyebrow !text-white/50 !text-[11px] mb-[14px]"><?php echo e(__('site.cta.download')); ?></div>
                    <h3 class="font-[family-name:var(--ns-display)] text-[26px] font-semibold leading-[1.15] mb-[14px]"><?php echo e(__('site.pages.floorplan.offline_title')); ?></h3>
                    <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-white/78 mb-6"><?php echo e(__('site.pages.floorplan.offline_body')); ?></p>
                    <div class="mt-auto flex flex-col gap-[10px]">
                        <?php $plan = \App\Models\Download::where('group', 'floorplan')->where('published', true)->first(); ?>
                        <a href="<?php echo e($plan?->url() ?: '#'); ?>" class="ns-btn ns-btn-magenta !justify-start"><?php echo e(__('site.pages.floorplan.download')); ?></a>
                        <a href="<?php echo e(route('universities')); ?>" class="ns-btn ns-btn-ghost-light !justify-start"><?php echo e(__('site.cta.exhibitor_list')); ?></a>
                    </div>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc)): ?>
<?php $attributes = $__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc; ?>
<?php unset($__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc)): ?>
<?php $component = $__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc; ?>
<?php unset($__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc); ?>
<?php endif; ?>
    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfefb4fd9b7004fa65f70c415ac76903e)): ?>
<?php $attributes = $__attributesOriginalfefb4fd9b7004fa65f70c415ac76903e; ?>
<?php unset($__attributesOriginalfefb4fd9b7004fa65f70c415ac76903e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfefb4fd9b7004fa65f70c415ac76903e)): ?>
<?php $component = $__componentOriginalfefb4fd9b7004fa65f70c415ac76903e; ?>
<?php unset($__componentOriginalfefb4fd9b7004fa65f70c415ac76903e); ?>
<?php endif; ?>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/directory/floorplan.blade.php ENDPATH**/ ?>