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
    <div class="ns-wrap pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('attendee.profile.title')); ?></span>
        </div>

        <div class="flex justify-between items-end gap-6 flex-wrap mb-4">
            <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)]"><?php echo e(__('attendee.agenda.title')); ?></h1>
            <a href="<?php echo e(route('me')); ?>" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                <?php echo e(__('attendee.profile.title')); ?>

            </a>
        </div>

        <p class="ns-body max-w-[62ch] mb-4"><?php echo e(__('attendee.agenda.lead')); ?></p>
        <p class="ns-meta mb-9"><?php echo e(trans_choice('attendee.agenda.count', count($saved), ['count' => count($saved)])); ?></p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-bone-200 p-5 mb-8 font-[family-name:var(--ns-body)] text-[15px]"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $sessions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section class="mb-12">
                <div class="flex items-baseline gap-3 mb-2">
                    <h2 class="ns-h2 !text-[clamp(22px,2.4vw,30px)]"><?php echo e(__('site.common.day', ['n' => $day])); ?></h2>
                    <span class="ns-meta ns-num"><?php echo e(ns_day_date($day)); ?></span>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($isSaved = in_array($session->id, $saved, true)); ?>
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'grid gap-6 border-t border-[rgba(5,7,8,0.14)] py-[22px] items-start lg:grid-cols-[110px_minmax(0,1fr)_190px]',
                        'bg-white/70' => $isSaved,
                    ]); ?>">
                        <div class="ns-num font-[family-name:var(--ns-display)] text-[20px] font-semibold">
                            <?php echo e($session->timeLabel()); ?>

                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-[9px] mb-2 flex-wrap">
                                <span class="ns-typechip <?php echo e($session->chipClass()); ?>"><?php echo e($session->typeLabel()); ?></span>
                                <span class="ns-meta text-xs"><?php echo e($session->hallLabel()); ?></span>
                            </div>
                            <h3 class="font-[family-name:var(--ns-display)] text-[21px] font-semibold leading-[1.2] mb-1">
                                <?php echo e($session->t('title')); ?>

                            </h3>
                            <p class="font-[family-name:var(--ns-body)] text-[14px] leading-[1.55] text-slate max-w-[64ch]">
                                <?php echo e($session->t('description')); ?>

                            </p>
                        </div>

                        <div class="flex lg:justify-end">
                            <form method="POST" action="<?php echo e(route('me.agenda.toggle', $session)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'ns-btn ns-btn-sm whitespace-nowrap',
                                    'ns-btn-magenta' => ! $isSaved,
                                    'ns-btn-ghost' => $isSaved,
                                ]); ?>">
                                    <?php echo e($isSaved ? __('attendee.agenda.remove') : __('attendee.agenda.add')); ?>

                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/attendee/agenda.blade.php ENDPATH**/ ?>