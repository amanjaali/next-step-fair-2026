<?php if (isset($component)) { $__componentOriginal2c4a003b5c92a492309b413341710395 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c4a003b5c92a492309b413341710395 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.scholarship','data' => ['title' => $title,'scholarshipNav' => $scholarshipNav,'attendee' => $attendee,'application' => $application]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.scholarship'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'scholarshipNav' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scholarshipNav),'attendee' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attendee),'application' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($application)]); ?>
    <div class="ns-wrap max-w-[880px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('scholarship.apply.kicker', ['cycle' => $cycle])); ?></span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3"><?php echo e(__('scholarship.apply.gate_heading')); ?></h1>
        <p class="ns-body max-w-[58ch] mb-6"><?php echo e(__('scholarship.apply.gate_lead')); ?></p>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('gate_blocked')): ?>
            <div class="border-s-[5px] border-magenta bg-white ns-radius px-5 py-4 mb-8 max-w-[62ch]" role="status">
                <p class="ns-body !text-[14.5px] !m-0"><?php echo e(__('scholarship.eligibility.gate_blocked')); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-10"></div>

        <?php
            // Three gates, shown together with their state. Discovering them one at
            // a time — sign up, then a check, then a form — is how an application
            // gets abandoned halfway.
            $steps = [
                [
                    'n' => 1,
                    'title' => __('scholarship.apply.gate1_title'),
                    'body' => __('scholarship.apply.gate1_body'),
                    'done' => $hasAccount,
                    'cta' => $hasAccount ? null : __('scholarship.apply.gate1_cta'),
                    'href' => route('register.fair', ['type' => 'student']),
                    'state' => $hasAccount ? __('scholarship.apply.state_done') : __('scholarship.apply.state_required'),
                ],
                [
                    'n' => 2,
                    'title' => __('scholarship.apply.gate2_title'),
                    'body' => __('scholarship.apply.gate2_body'),
                    'done' => $checkPassed,
                    'cta' => $isEligibleStudent ? ($checkPassed ? __('scholarship.apply.gate2_review') : __('scholarship.apply.gate2_cta')) : null,
                    'href' => route('scholarship.eligibility'),
                    'state' => $checkPassed
                        ? __('scholarship.apply.state_passed')
                        : ($isEligibleStudent ? __('scholarship.apply.state_required') : __('scholarship.apply.state_locked')),
                ],
                [
                    'n' => 3,
                    'title' => __('scholarship.apply.gate3_title'),
                    'body' => __('scholarship.apply.gate3_body'),
                    'done' => $application?->isSubmitted() ?? false,
                    'cta' => $checkPassed ? __('scholarship.apply.gate3_cta') : null,
                    'href' => $application?->isSubmitted() ? route('scholarship.status') : route('scholarship.apply.form'),
                    'state' => $application?->isSubmitted()
                        ? __('scholarship.apply.state_submitted')
                        : ($checkPassed ? __('scholarship.apply.state_open') : __('scholarship.apply.state_locked')),
                ],
            ];
        ?>

        <div class="grid gap-5 md:grid-cols-3 mb-12">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'border-t-4 px-6 py-6 flex flex-col',
                    'bg-white border-teal' => $step['done'],
                    'bg-white border-magenta' => ! $step['done'] && $step['cta'],
                    'bg-bone-50 border-[#C9C4BF]' => ! $step['done'] && ! $step['cta'],
                ]); ?>">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'w-7 h-7 flex items-center justify-center font-[family-name:var(--ns-display)] text-[13px] font-bold text-white ns-num',
                            'bg-teal' => $step['done'],
                            'bg-magenta' => ! $step['done'] && $step['cta'],
                            'bg-[#C9C4BF]' => ! $step['done'] && ! $step['cta'],
                        ]); ?>"><?php echo e($step['done'] ? '✓' : $step['n']); ?></span>
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'ns-eyebrow !text-[10px]',
                            '!text-teal' => $step['done'],
                            '!text-magenta' => ! $step['done'] && $step['cta'],
                            '!text-muted' => ! $step['done'] && ! $step['cta'],
                        ]); ?>"><?php echo e($step['state']); ?></span>
                    </div>

                    <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-2"><?php echo e($step['title']); ?></div>
                    <p class="ns-body !text-[14px] text-body-soft mb-5 flex-1"><?php echo e($step['body']); ?></p>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step['cta']): ?>
                        <a href="<?php echo e($step['href']); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'ns-btn ns-btn-sm w-full',
                            'ns-btn-magenta' => ! $step['done'],
                            'ns-btn-ghost' => $step['done'],
                        ]); ?>"><?php echo e($step['cta']); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($hasAccount)): ?>
            <div class="ns-card border-s-[6px] !border-s-magenta mb-12">
                <p class="ns-body !text-[15px] mb-4 max-w-[58ch]"><?php echo e(__('scholarship.apply.have_account')); ?></p>
                <a href="<?php echo e(route('attendee.signin')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm"><?php echo e(__('attendee.signin.title')); ?></a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasAccount && ! $isEligibleStudent): ?>
            
            <div class="ns-card border-s-[6px] !border-s-[#C08A1E] mb-12">
                <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-2"><?php echo e(__('scholarship.apply.not_eligible_title')); ?></div>
                <p class="ns-body !text-[15px] max-w-[58ch]"><?php echo e(__('scholarship.apply.not_eligible_body')); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <h2 class="ns-h2 !text-[clamp(21px,2.4vw,27px)] mb-2"><?php echo e(__('scholarship.apply.ready_title')); ?></h2>
        <p class="ns-body !text-[15px] text-body-soft max-w-[58ch] mb-6"><?php echo e(__('scholarship.apply.ready_lead')); ?></p>

        <div class="border border-[rgba(5,7,8,0.14)]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = __('scholarship.apply.ready'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between gap-6 px-5 py-[13px] border-b border-[rgba(5,7,8,0.1)] last:border-b-0 flex-wrap">
                    <span class="font-[family-name:var(--ns-body)] text-[14.5px] font-medium"><?php echo e($item['what']); ?></span>
                    <span class="ns-meta text-[12.5px]"><?php echo e($item['note']); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c4a003b5c92a492309b413341710395)): ?>
<?php $attributes = $__attributesOriginal2c4a003b5c92a492309b413341710395; ?>
<?php unset($__attributesOriginal2c4a003b5c92a492309b413341710395); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c4a003b5c92a492309b413341710395)): ?>
<?php $component = $__componentOriginal2c4a003b5c92a492309b413341710395; ?>
<?php unset($__componentOriginal2c4a003b5c92a492309b413341710395); ?>
<?php endif; ?>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/scholarship/gate.blade.php ENDPATH**/ ?>