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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.page-head','data' => ['title' => __('site.pages.agenda.title'),'lead' => __('site.pages.agenda.lead', ['count' => $totalCount])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.page-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.agenda.title')),'lead' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.agenda.lead', ['count' => $totalCount]))]); ?>

            
            <div class="ns-tabs mb-[22px]">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('nextstep.event.days'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $number => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(request()->fullUrlWithQuery(['day' => $number])); ?>"
                       class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-tab', 'is-on' => $day === $number]); ?>"
                       <?php if($day === $number): ?> aria-current="page" <?php endif; ?>>
                        <?php echo e(__('site.common.day', ['n' => $number])); ?>

                        <span class="ns-num font-normal text-[13px] ms-2 opacity-70"><?php echo e(ns_day_date($number)); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="flex items-baseline gap-3 flex-wrap mb-2">
                <span class="ns-meta text-[12.5px] shrink-0"><?php echo e(__('site.pages.agenda.filter_by')); ?></span>
                <?php if (isset($component)) { $__componentOriginal104647ec22cc3c80898ef1e59da6d694 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal104647ec22cc3c80898ef1e59da6d694 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.filter-chips','data' => ['param' => 'type','active' => $activeType,'options' => $typeOptions]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.filter-chips'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['param' => 'type','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeType),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($typeOptions)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal104647ec22cc3c80898ef1e59da6d694)): ?>
<?php $attributes = $__attributesOriginal104647ec22cc3c80898ef1e59da6d694; ?>
<?php unset($__attributesOriginal104647ec22cc3c80898ef1e59da6d694); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal104647ec22cc3c80898ef1e59da6d694)): ?>
<?php $component = $__componentOriginal104647ec22cc3c80898ef1e59da6d694; ?>
<?php unset($__componentOriginal104647ec22cc3c80898ef1e59da6d694); ?>
<?php endif; ?>
            </div>

            <div class="flex items-center justify-between gap-4 flex-wrap mb-[26px]">
                <span class="ns-meta"><?php echo e(__('site.pages.agenda.export', ['count' => $sessions->count()])); ?></span>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('agenda.pdf')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm">PDF</a>
                    <a href="<?php echo e(route('agenda.ics')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm">.ics</a>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sessions->isEmpty()): ?>
                <div class="ns-card max-w-[64ch]">
                    <p class="ns-body"><?php echo e(__('site.pages.agenda.empty')); ?></p>
                    <a href="<?php echo e(route('agenda', ['day' => $day])); ?>" class="ns-btn ns-btn-ink mt-6"><?php echo e(__('site.common.show_all')); ?></a>
                </div>
            <?php else: ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $track => $trackSessions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTrackHeadings): ?>
                        
                        <div class="mt-[clamp(28px,3vw,44px)] mb-1 pt-6 border-t-2 <?php echo e($track === 'conference' ? 'border-cobalt' : 'border-magenta'); ?>">
                            <div class="flex items-baseline gap-x-4 gap-y-1 flex-wrap">
                                <h2 class="font-[family-name:var(--ns-display)] text-[clamp(20px,2.4vw,26px)] font-semibold leading-tight">
                                    <?php echo e($track === 'conference' ? __('site.nav.conference') : __('site.nav.expo')); ?>

                                </h2>
                                <span class="ns-eyebrow !text-[9.5px] <?php echo e($track === 'conference' ? '!text-cobalt' : '!text-magenta'); ?>">
                                    <?php echo e($track === 'conference' ? __('site.pages.agenda.tracks.conference_who') : __('site.pages.agenda.tracks.expo_who')); ?>

                                </span>
                            </div>
                            <p class="ns-body !text-[14.5px] text-body-soft max-w-[68ch] mt-2">
                                <?php echo e($track === 'conference' ? __('site.pages.agenda.tracks.conference_note') : __('site.pages.agenda.tracks.expo_note')); ?>

                                <a href="<?php echo e($track === 'conference' ? route('conference') : route('fair')); ?>" class="ns-link !text-[14.5px]">
                                    <?php echo e($track === 'conference' ? __('site.pages.agenda.tracks.conference_link') : __('site.pages.agenda.tracks.expo_link')); ?>

                                </a>
                            </p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="flex flex-col">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $trackSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginal25e4ebd3f5df598888dcf51de1907dd5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25e4ebd3f5df598888dcf51de1907dd5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.session-row','data' => ['session' => $session]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.session-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['session' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($session)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25e4ebd3f5df598888dcf51de1907dd5)): ?>
<?php $attributes = $__attributesOriginal25e4ebd3f5df598888dcf51de1907dd5; ?>
<?php unset($__attributesOriginal25e4ebd3f5df598888dcf51de1907dd5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25e4ebd3f5df598888dcf51de1907dd5)): ?>
<?php $component = $__componentOriginal25e4ebd3f5df598888dcf51de1907dd5; ?>
<?php unset($__componentOriginal25e4ebd3f5df598888dcf51de1907dd5); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/agenda/index.blade.php ENDPATH**/ ?>