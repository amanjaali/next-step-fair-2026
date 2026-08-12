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
    <div class="ns-wrap max-w-[1080px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('opportunities.kicker')); ?></span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3"><?php echo e(__('opportunities.title')); ?></h1>
        <p class="ns-body max-w-[60ch] mb-10"><?php echo e(__('opportunities.lead')); ?></p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $attendee): ?>
            
            <div class="ns-card border-s-[6px] !border-s-magenta">
                <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-3"><?php echo e(__('opportunities.locked_title')); ?></div>
                <p class="ns-body mb-6 max-w-[58ch]"><?php echo e(__('opportunities.locked_body')); ?></p>
                <div class="flex gap-3 flex-wrap">
                    <a href="<?php echo e(route('register.fair', ['type' => 'student'])); ?>" class="ns-btn ns-btn-magenta"><?php echo e(__('opportunities.locked_cta')); ?></a>
                    <a href="<?php echo e(route('attendee.signin')); ?>" class="ns-btn ns-btn-ghost"><?php echo e(__('attendee.signin.title')); ?></a>
                </div>
            </div>
        <?php else: ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->type !== \App\Models\Registration::TYPE_STUDENT): ?>
                <?php
                    $pass = __('opportunities.for_students.message', ['url' => route('opportunities')]);
                ?>
                <div class="border-s-[6px] border-magenta bg-white px-6 py-5 mb-9">
                    <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-1">
                        <?php echo e(__('opportunities.for_students.title')); ?>

                    </div>
                    <p class="ns-body !text-[14.5px] text-body-soft max-w-[62ch] mb-4">
                        <?php echo e(__('opportunities.for_students.body')); ?>

                    </p>
                    <a href="https://wa.me/?text=<?php echo e(rawurlencode($pass)); ?>" target="_blank" rel="noopener"
                       class="ns-btn ns-btn-magenta ns-btn-sm"><?php echo e(__('opportunities.for_students.send')); ?></a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($closingSoon->isNotEmpty()): ?>
                <div class="ns-urgent px-6 py-5 mb-9">
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="ns-urgent-dot" aria-hidden="true"></span>
                        <span class="ns-eyebrow !text-[10px] !text-white"><?php echo e(__('opportunities.closing_title')); ?></span>
                    </div>
                    <ul class="list-none m-0 p-0 flex flex-col gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $closingSoon; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="font-[family-name:var(--ns-body)] text-[15px] flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                <a href="<?php echo e(route('opportunities.show', $opportunity->slug)); ?>" class="font-bold"><?php echo e($opportunity->t('title')); ?></a>
                                <span class="ns-urgent-count ns-num text-[12px]">
                                    <?php echo e(trans_choice('opportunities.closing_in', max($opportunity->daysLeft(), 0), ['count' => max($opportunity->daysLeft(), 0)])); ?>

                                </span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunities->isNotEmpty()): ?>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $opportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal30ede29f76a871128dc01de573759a22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal30ede29f76a871128dc01de573759a22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.opportunity-card','data' => ['opportunity' => $opportunity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.opportunity-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['opportunity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunity)]); ?>
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
            <?php else: ?>
                <div class="ns-card">
                    <div class="font-[family-name:var(--ns-display)] text-[20px] font-semibold mb-2"><?php echo e(__('opportunities.empty_title')); ?></div>
                    <p class="ns-body max-w-[58ch]"><?php echo e(__('opportunities.empty_body')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/opportunities/index.blade.php ENDPATH**/ ?>