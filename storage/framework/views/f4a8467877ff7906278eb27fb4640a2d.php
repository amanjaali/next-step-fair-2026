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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.page-head','data' => ['title' => __('site.pages.directory.title'),'lead' => __('site.pages.directory.lead', ['universities' => $universities->count(), 'exhibitors' => $exhibitors->count()])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.page-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.directory.title')),'lead' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.directory.lead', ['universities' => $universities->count(), 'exhibitors' => $exhibitors->count()]))]); ?>

            <div class="ns-tabs mb-8">
                <a href="<?php echo e(route('universities')); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-tab', 'is-on' => $tab === 'universities']); ?>">
                    <?php echo e(__('site.pages.directory.universities_tab', ['count' => $universities->count()])); ?>

                </a>
                <a href="<?php echo e(route('exhibitors')); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-tab', 'is-on' => $tab === 'exhibitors']); ?>">
                    <?php echo e(__('site.pages.directory.exhibitors_tab', ['count' => $exhibitors->count()])); ?>

                </a>
            </div>

            <div class="ns-hairgrid md:grid-cols-2 xl:grid-cols-3 mb-11">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-[26px] flex gap-5 items-start">
                        <div class="w-16 h-16 bg-bone-200 flex-none flex items-center justify-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($org->logo_path): ?>
                                <img src="<?php echo e(asset('assets/'.$org->logo_path)); ?>" alt="<?php echo e($org->t('name')); ?>" class="max-w-[52px] max-h-[52px] object-contain" loading="lazy">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <div class="font-[family-name:var(--ns-body)] text-base font-bold leading-[1.3] mb-[6px]"><?php echo e($org->t('name')); ?></div>
                            <div class="ns-meta leading-[1.5] mb-[10px]"><?php echo e($org->t('description')); ?></div>
                            <div class="flex gap-2 items-center flex-wrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($org->booth): ?>
                                    <span class="ns-typechip border border-[rgba(182,70,152,0.4)] text-magenta !tracking-[0.14em] !text-[10.5px]">
                                        <?php echo e($org->hall?->code ? __('site.common.day', ['n' => '']) : ''); ?><?php echo e($org->booth); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($org->website): ?>
                                    <a href="<?php echo e($org->website); ?>" target="_blank" rel="noopener"
                                       class="font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-cobalt"><?php echo e(__('site.cta.website')); ?></a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="ns-card">
                <div class="flex justify-between items-baseline gap-5 flex-wrap mb-[22px]">
                    <h2 class="ns-h2 !text-[clamp(24px,2.8vw,30px)]"><?php echo e(__('site.pages.directory.floor_plan')); ?></h2>
                    <span class="ns-meta"><?php echo e(__('site.pages.directory.halls_note')); ?></span>
                </div>

                <a href="<?php echo e(route('floorplan')); ?>" class="grid gap-3 sm:grid-cols-[1.3fr_1fr_1fr] h-[260px] text-white hover:text-white">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $halls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hall): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-5 flex flex-col justify-between text-start" style="background:<?php echo e($hall->color); ?>">
                            <span class="font-[family-name:var(--ns-display)] text-[11px] font-bold tracking-[0.2em]"><?php echo e(__('site.common.day', ['n' => ''])); ?>HALL <?php echo e($hall->code); ?></span>
                            <span class="font-[family-name:var(--ns-display)] text-2xl font-semibold leading-tight"><?php echo e($hall->t('name')); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>

                <div class="flex items-center justify-between gap-5 flex-wrap mt-[18px]">
                    <span class="ns-meta"><?php echo e(__('site.pages.directory.colour_note')); ?></span>
                    <a href="<?php echo e(route('floorplan')); ?>" class="ns-btn ns-btn-ink ns-btn-sm"><?php echo e(__('site.cta.open_floorplan')); ?></a>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/directory/index.blade.php ENDPATH**/ ?>