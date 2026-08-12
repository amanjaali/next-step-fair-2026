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
    <div class="ns-wrap max-w-[820px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <a href="<?php echo e(route('opportunities')); ?>" class="ns-meta text-[13px] mb-6 inline-block">← <?php echo e(__('opportunities.title')); ?></a>

        <div class="flex items-center gap-3 mb-4 flex-wrap">
            <span class="ns-eyebrow !text-magenta"><?php echo e(__("opportunities.kinds.{$opportunity->kind}")); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isClosingSoon()): ?>
                <span class="ns-urgent-pill ns-num font-[family-name:var(--ns-body)] text-[12px]">
                    <?php echo e(trans_choice('opportunities.closing_in', max($opportunity->daysLeft(), 0), ['count' => max($opportunity->daysLeft(), 0)])); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <h1 class="ns-h1 !text-[clamp(28px,3.8vw,42px)] mb-4"><?php echo e($opportunity->t('title')); ?></h1>
        <p class="ns-body !text-[clamp(16px,1.6vw,18px)] max-w-[60ch] mb-8"><?php echo e($opportunity->t('summary')); ?></p>

        <div class="flex items-center gap-4 pb-8 mb-8 border-b border-[rgba(5,7,8,0.14)]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->logo()): ?>
                <img src="<?php echo e($opportunity->logo()); ?>" alt="" class="h-11 w-auto max-w-[130px] object-contain">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div>
                <div class="ns-eyebrow !text-[9.5px] mb-1"><?php echo e(__('opportunities.offered_by')); ?></div>
                <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold"><?php echo e($opportunity->partner()); ?></div>
            </div>
        </div>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-3 mb-9">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_filter([
                __('opportunities.closes') => $opportunity->closes_at ? ns_format_date($opportunity->closes_at) : __('opportunities.no_deadline'),
                __('opportunities.places_label') => $opportunity->places,
                __('opportunities.who') => __("opportunities.audiences.{$opportunity->audience}"),
            ]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white px-5 py-4">
                    <div class="ns-eyebrow !text-[9px] mb-1"><?php echo e($label); ?></div>
                    <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold ns-num"><?php echo e($value); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->t('body')): ?>
            <div class="ns-prose max-w-[62ch] mb-9"><?php echo $opportunity->t('body'); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->t('eligibility')): ?>
            <h2 class="ns-h2 !text-[clamp(20px,2.3vw,26px)] mb-3"><?php echo e(__('opportunities.eligibility')); ?></h2>
            <div class="ns-prose max-w-[62ch] mb-9"><?php echo $opportunity->t('eligibility'); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->action_url): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee): ?>
                <a href="<?php echo e(route('opportunities.go', $opportunity->slug)); ?>" target="_blank" rel="noopener"
                   class="ns-btn ns-btn-lg ns-btn-magenta"><?php echo e($opportunity->actionLabel()); ?></a>
                <p class="ns-meta text-[12.5px] mt-4 max-w-[54ch]"><?php echo e(__('opportunities.action_note')); ?></p>
            <?php else: ?>
                <div class="ns-card border-s-[6px] !border-s-magenta">
                    <p class="ns-body !text-[15px] mb-5 max-w-[56ch]"><?php echo e(__('opportunities.locked_body')); ?></p>
                    <a href="<?php echo e(route('register.fair', ['type' => 'student'])); ?>" class="ns-btn ns-btn-magenta ns-btn-sm"><?php echo e(__('opportunities.locked_cta')); ?></a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($related->isNotEmpty()): ?>
            <h2 class="ns-h2 !text-[clamp(20px,2.3vw,26px)] mt-14 mb-6"><?php echo e(__('opportunities.related')); ?></h2>
            <div class="grid gap-5 md:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $other): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal30ede29f76a871128dc01de573759a22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal30ede29f76a871128dc01de573759a22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.opportunity-card','data' => ['opportunity' => $other]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.opportunity-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['opportunity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($other)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal30ede29f76a871128dc01de573759a22)): ?>
<?php $attributes = $__attributesOriginal30ede29f76a871128dc01de573759a22; ?>
<?php unset($__attributesOriginal30ede29f76a871128dc01de573759a22); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal30ede29f76a871128dc01de573759a22)): ?>
<?php $component = $__componentOriginal30ede29f76a871128dc01de573759a22; ?>
<?php unset($__componentOriginal30ede29f76a871128dc01de573759a22); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/opportunities/show.blade.php ENDPATH**/ ?>