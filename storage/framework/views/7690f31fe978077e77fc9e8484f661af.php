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
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('attendee.profile.title')); ?></span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3"><?php echo e(__('attendee.interests.title')); ?></h1>
        <p class="ns-body max-w-[62ch] mb-9"><?php echo e(__('attendee.interests.lead')); ?></p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="ns-card border-t-[6px] !border-t-crimson mb-8" role="alert">
                <ul class="list-none m-0 p-0 flex flex-col gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="font-[family-name:var(--ns-body)] text-[15px] text-crimson"><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('me.interests.save')); ?>" novalidate>
            <?php echo csrf_field(); ?>

            
            <div class="ns-card mb-6">
                <span class="ns-label"><?php echo e(__('attendee.interests.fields')); ?> <span class="ns-req">*</span></span>
                <span class="ns-hint mb-4 block"><?php echo e(__('attendee.interests.fields_hint')); ?></span>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sectors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sector): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-5">
                        <div class="ns-eyebrow !text-[10px] mb-2"><?php echo e($sector->t('name')); ?></div>
                        <div class="flex gap-2 flex-wrap">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sector->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="ns-chip">
                                    <input type="checkbox" name="fields[]" value="<?php echo e($field->id); ?>" class="sr-only"
                                           <?php if(in_array($field->id, old('fields', $chosen))): echo 'checked'; endif; ?>>
                                    <span><?php echo e($field->t('name')); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="ns-card mb-6">
                <div class="mb-6">
                    <span class="ns-label"><?php echo e(__('attendee.interests.level')); ?> <span class="ns-req">*</span></span>
                    <div class="flex gap-2 flex-wrap">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('taxonomy.degree_levels'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="ns-chip">
                                <input type="radio" name="degree_level" value="<?php echo e($level); ?>" class="sr-only"
                                       <?php if(old('degree_level', $registration->degree_level) === $level): echo 'checked'; endif; ?>>
                                <span><?php echo e(__("taxonomy.levels.$level")); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <span class="ns-label"><?php echo e(__('attendee.interests.countries')); ?></span>
                    <span class="ns-hint mb-2 block"><?php echo e(__('attendee.interests.countries_hint')); ?></span>
                    <div class="flex gap-2 flex-wrap">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('taxonomy.countries'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="ns-chip">
                                <input type="checkbox" name="preferred_countries[]" value="<?php echo e($code); ?>" class="sr-only"
                                       <?php if(in_array($code, old('preferred_countries', $registration->preferred_countries ?? []))): echo 'checked'; endif; ?>>
                                <span><?php echo e(__("taxonomy.countries.$code")); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <label>
                        <span class="ns-label"><?php echo e(__('attendee.interests.language')); ?></span>
                        <select name="language_preference" class="ns-select">
                            <option value="">—</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('taxonomy.languages'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($code); ?>" <?php if(old('language_preference', $registration->language_preference) === $code): echo 'selected'; endif; ?>>
                                    <?php echo e(__("taxonomy.languages.$code")); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </label>

                    <label>
                        <span class="ns-label"><?php echo e(__('attendee.interests.grade')); ?></span>
                        <select name="grade_band" class="ns-select">
                            <option value="">—</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_keys(config('taxonomy.grade_bands')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $band): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($band); ?>" <?php if(old('grade_band', $registration->grade_band) === $band): echo 'selected'; endif; ?>>
                                    <?php echo e(__("taxonomy.grades.$band")); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </label>

                    <label class="sm:col-span-2">
                        <span class="ns-label"><?php echo e(__('attendee.interests.budget')); ?></span>
                        <select name="budget_band" class="ns-select">
                            <option value="">—</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_keys(config('taxonomy.budget_bands')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $band): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($band); ?>" <?php if(old('budget_band', $registration->budget_band) === $band): echo 'selected'; endif; ?>>
                                    <?php echo e(__("taxonomy.budgets.$band")); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <span class="ns-hint"><?php echo e(__('attendee.interests.budget_hint')); ?></span>
                    </label>

                    <label>
                        <span class="ns-label"><?php echo e(__('attendee.interests.start')); ?></span>
                        <select name="start_year" class="ns-select ns-num">
                            <option value="">—</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($y = (int) config('nextstep.event.year'); $y <= (int) config('nextstep.event.year') + 4; $y++): ?>
                                <option value="<?php echo e($y); ?>" <?php if((int) old('start_year', $registration->start_year) === $y): echo 'selected'; endif; ?>><?php echo e($y); ?></option>
                            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </label>

                    <label>
                        <span class="ns-label"><?php echo e(__('attendee.interests.career')); ?></span>
                        <select name="career_goal" class="ns-select">
                            <option value="">—</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('taxonomy.career_goals'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($goal); ?>" <?php if(old('career_goal', $registration->career_goal) === $goal): echo 'selected'; endif; ?>>
                                    <?php echo e(__("taxonomy.careers.$goal")); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </label>
                </div>
            </div>

            
            <div class="ns-card mb-8">
                <label class="flex gap-[14px] items-start cursor-pointer">
                    <input type="checkbox" name="share_with_institutions" value="1" class="sr-only"
                           <?php if(old('share_with_institutions', $registration->share_with_institutions)): echo 'checked'; endif; ?>>
                    <span class="ns-box mt-[2px]"></span>
                    <span>
                        <span class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.55] block">
                            <?php echo e(__('attendee.interests.share')); ?>

                        </span>
                        <span class="ns-hint"><?php echo e(__('attendee.interests.share_hint')); ?></span>
                    </span>
                </label>
            </div>

            <button type="submit" class="ns-btn ns-btn-magenta ns-btn-lg"><?php echo e(__('attendee.interests.save')); ?></button>
        </form>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/attendee/interests.blade.php ENDPATH**/ ?>