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
    <div class="ns-wrap max-w-[980px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('attendee.profile.title')); ?></span>
        </div>

        <div class="flex justify-between items-end gap-6 flex-wrap mb-4">
            <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)]"><?php echo e(__('attendee.matches.title')); ?></h1>
            <a href="<?php echo e(route('me.interests')); ?>" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                <?php echo e(__('attendee.matches.edit')); ?>

            </a>
        </div>

        <p class="ns-body max-w-[62ch] mb-8"><?php echo e(__('attendee.matches.lead')); ?></p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-bone-200 p-5 mb-8 font-[family-name:var(--ns-body)] text-[15px]"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php ($org = $match->organization); ?>
            <article class="ns-card mb-5">
                <div class="flex justify-between items-start gap-5 flex-wrap mb-4">
                    <div class="min-w-0">
                        <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold leading-[1.2] mb-1">
                            <?php echo e($org->t('name')); ?>

                        </h2>
                        <div class="ns-meta text-[13px]">
                            <?php echo e(collect([$org->city, __("taxonomy.countries.{$org->country}")])->filter()->join(' · ')); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($org->booth): ?>
                                · <?php echo e(__('attendee.matches.booth')); ?> <span class="ns-num"><?php echo e($org->booth); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="text-end shrink-0">
                        <div class="ns-num font-[family-name:var(--ns-display)] text-[30px] font-bold text-magenta leading-none">
                            <?php echo e($match->score); ?><span class="text-[15px] text-slate">/100</span>
                        </div>
                        <div class="ns-eyebrow !text-[9.5px] mt-1"><?php echo e(__('attendee.matches.band.'.$match->band())); ?></div>
                    </div>
                </div>

                <div class="border-t border-[rgba(5,7,8,0.12)] pt-4">
                    <div class="ns-eyebrow !text-[10px] mb-2"><?php echo e(__('attendee.matches.why')); ?></div>
                    <ul class="list-none m-0 p-0 flex flex-wrap gap-x-5 gap-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($match->reasons['fields'])): ?>
                            <li class="font-[family-name:var(--ns-body)] text-[14px]">
                                <span class="text-slate"><?php echo e(__('attendee.matches.teaches')); ?>:</span>
                                <span class="font-semibold"><?php echo e(implode(', ', $match->reasons['fields'])); ?></span>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($match->reasons['country'])): ?>
                            <li class="font-[family-name:var(--ns-body)] text-[14px]">
                                <span class="text-slate"><?php echo e(__('attendee.matches.in_country')); ?>:</span>
                                <span class="font-semibold"><?php echo e(collect($match->reasons['country'])->map(fn ($c) => __("taxonomy.countries.$c"))->join(', ')); ?></span>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($match->reasons['language'])): ?>
                            <li class="font-[family-name:var(--ns-body)] text-[14px]">
                                <span class="text-slate"><?php echo e(__('attendee.matches.in_language')); ?>:</span>
                                <span class="font-semibold"><?php echo e(__('taxonomy.languages.'.$match->reasons['language'])); ?></span>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($match->reasons['budget'])): ?>
                            <li class="font-[family-name:var(--ns-body)] text-[14px] font-semibold"><?php echo e(__('attendee.matches.within_budget')); ?></li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($match->reasons['scholarship'])): ?>
                            <li class="font-[family-name:var(--ns-body)] text-[14px] font-semibold"><?php echo e(__('attendee.matches.has_scholarship')); ?></li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($match->reasons['meets_entry'])): ?>
                            <li class="font-[family-name:var(--ns-body)] text-[14px] font-semibold"><?php echo e(__('attendee.matches.meets_entry')); ?></li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>

                <div class="flex gap-3 flex-wrap mt-5 pt-4 border-t border-[rgba(5,7,8,0.12)]">
                    <form method="POST" action="<?php echo e(route('me.matches.shortlist', $org)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php ($isSaved = in_array($org->id, $shortlisted, true)); ?>
                        <button type="submit" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'ns-btn ns-btn-sm',
                            'ns-btn-magenta' => $isSaved,
                            'ns-btn-ghost' => ! $isSaved,
                        ]); ?>"><?php echo e($isSaved ? __('attendee.matches.saved_label') : __('attendee.matches.save')); ?></button>
                    </form>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($org->website): ?>
                        <a href="<?php echo e($org->website); ?>" target="_blank" rel="noopener"
                           class="ns-btn ns-btn-ghost ns-btn-sm"><?php echo e($org->website); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="ns-card text-center py-12">
                <p class="ns-body mb-6"><?php echo e(__('attendee.matches.empty')); ?></p>
                <a href="<?php echo e(route('me.interests')); ?>" class="ns-btn ns-btn-magenta">
                    <?php echo e(__('attendee.matches.complete_first')); ?>

                </a>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/attendee/matches.blade.php ENDPATH**/ ?>