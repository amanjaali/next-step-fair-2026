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

    
    <section class="bg-ink text-white">
        <div class="ns-wrap max-w-[1080px] pt-[clamp(44px,7vw,96px)] pb-[clamp(40px,6vw,80px)]">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-[26px] h-2 bg-magenta"></span>
                <span class="ns-eyebrow !text-magenta"><?php echo e(__('scholarship.home.kicker', ['cycle' => $cycle])); ?></span>
            </div>

            <h1 class="ns-h1 !text-white !text-[clamp(34px,5.6vw,62px)] mb-6 max-w-[18ch]"><?php echo e(__('scholarship.home.heading')); ?></h1>
            <p class="font-[family-name:var(--ns-body)] text-[clamp(16px,1.7vw,19px)] leading-[1.6] text-white/75 max-w-[56ch] mb-9">
                <?php echo e(__('scholarship.home.lead')); ?>

            </p>

            
            <div class="flex gap-3 flex-wrap items-start">
                <?php if (isset($component)) { $__componentOriginalb6b847bbfc107072e3c18bdee7da27c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6b847bbfc107072e3c18bdee7da27c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.scholarship-cta','data' => ['attendee' => $attendee,'variant' => 'dark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.scholarship-cta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attendee' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attendee),'variant' => 'dark']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6b847bbfc107072e3c18bdee7da27c2)): ?>
<?php $attributes = $__attributesOriginalb6b847bbfc107072e3c18bdee7da27c2; ?>
<?php unset($__attributesOriginalb6b847bbfc107072e3c18bdee7da27c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6b847bbfc107072e3c18bdee7da27c2)): ?>
<?php $component = $__componentOriginalb6b847bbfc107072e3c18bdee7da27c2; ?>
<?php unset($__componentOriginalb6b847bbfc107072e3c18bdee7da27c2); ?>
<?php endif; ?>
                <a href="<?php echo e(route('scholarship.guidelines')); ?>" class="ns-btn ns-btn-ghost !text-white !border-white/40 hover:!bg-white/10">
                    <?php echo e(__('scholarship.home.cta_rules')); ?>

                </a>
            </div>
        </div>
    </section>

    
    <section class="ns-wrap max-w-[1080px] py-[clamp(40px,6vw,80px)]">
        <div class="flex items-baseline justify-between gap-4 flex-wrap mb-2">
            <h2 class="ns-h2 !text-[clamp(24px,3vw,34px)]"><?php echo e(__('scholarship.home.quota_title')); ?></h2>
            <span class="ns-num font-[family-name:var(--ns-display)] text-[38px] font-bold leading-none text-magenta">
                <?php echo e(config('scholarship.seats')); ?>

            </span>
        </div>
        <p class="ns-body max-w-[62ch] mb-8"><?php echo e(__('scholarship.home.quota_lead')); ?></p>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2 lg:grid-cols-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('scholarship.region', ['code' => strtolower($code)])); ?>"
                   class="bg-white px-5 py-[18px] block no-underline hover:bg-bone-50">
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="font-[family-name:var(--ns-display)] text-[17px] font-semibold text-ink"><?php echo e($region['name']); ?></span>
                        <span class="ns-num font-[family-name:var(--ns-display)] text-[20px] font-bold text-magenta"><?php echo e($region['seats']); ?></span>
                    </div>
                    <div class="ns-meta text-[12px] mt-[3px]">
                        <?php echo e(__("scholarship.region_types.{$region['type']}")); ?> · <?php echo e(trans_choice('scholarship.home.districts', count($region['districts']), ['count' => count($region['districts'])])); ?>

                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <p class="ns-meta text-[13px] mt-4 max-w-[62ch]"><?php echo e(__('scholarship.home.quota_note')); ?></p>
    </section>

    
    <section class="bg-bone-200">
        <div class="ns-wrap max-w-[1080px] py-[clamp(40px,6vw,80px)]">
            <h2 class="ns-h2 !text-[clamp(24px,3vw,34px)] mb-8"><?php echo e(__('scholarship.home.covers_title')); ?></h2>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = __('scholarship.home.covers'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white border-t-4 border-magenta px-5 py-6">
                        <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-2"><?php echo e($item['title']); ?></div>
                        <p class="ns-body !text-[14px] text-body-soft"><?php echo e($item['body']); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="ns-wrap max-w-[1080px] py-[clamp(40px,6vw,80px)]">
        <div class="flex items-baseline justify-between gap-4 flex-wrap mb-8">
            <h2 class="ns-h2 !text-[clamp(24px,3vw,34px)]"><?php echo e(__('scholarship.home.universities_title')); ?></h2>
            <a href="<?php echo e(route('scholarship.universities')); ?>" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                <?php echo e(__('scholarship.home.universities_all')); ?>

            </a>
        </div>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_slice($universities, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $university): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('scholarship.university', ['slug' => $university['slug']])); ?>"
                   class="bg-white px-6 py-5 block no-underline hover:bg-bone-50">
                    <div class="ns-eyebrow !text-[9.5px] !text-magenta mb-2"><?php echo e(__("scholarship.tiers.{$university['tier']}")); ?></div>
                    <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold text-ink mb-1 leading-[1.25]"><?php echo e($university['name']); ?></div>
                    <div class="ns-meta text-[12.5px]">
                        <?php echo e($university['city']); ?> · <span class="ns-num"><?php echo e(collect($university['departments'])->sum('seats')); ?></span> <?php echo e(__('scholarship.seats_word')); ?>

                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    
    <section class="bg-ink text-white">
        <div class="ns-wrap max-w-[1080px] py-[clamp(36px,5vw,64px)] flex items-center justify-between gap-8 flex-wrap">
            <div>
                <div class="ns-eyebrow !text-magenta mb-2"><?php echo e(__('scholarship.home.deadline_kicker')); ?></div>
                <div class="font-[family-name:var(--ns-display)] text-[clamp(22px,3vw,32px)] font-semibold ns-num">
                    <?php echo e(ns_format_date(\Illuminate\Support\Carbon::parse(config('scholarship.timeline.closes')))); ?>

                </div>
            </div>
            <?php if (isset($component)) { $__componentOriginalb6b847bbfc107072e3c18bdee7da27c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6b847bbfc107072e3c18bdee7da27c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.scholarship-cta','data' => ['attendee' => $attendee,'variant' => 'dark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.scholarship-cta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attendee' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attendee),'variant' => 'dark']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6b847bbfc107072e3c18bdee7da27c2)): ?>
<?php $attributes = $__attributesOriginalb6b847bbfc107072e3c18bdee7da27c2; ?>
<?php unset($__attributesOriginalb6b847bbfc107072e3c18bdee7da27c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6b847bbfc107072e3c18bdee7da27c2)): ?>
<?php $component = $__componentOriginalb6b847bbfc107072e3c18bdee7da27c2; ?>
<?php unset($__componentOriginalb6b847bbfc107072e3c18bdee7da27c2); ?>
<?php endif; ?>
        </div>
    </section>

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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/scholarship/home.blade.php ENDPATH**/ ?>