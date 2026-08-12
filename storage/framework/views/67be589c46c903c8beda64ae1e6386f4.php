<?php
    $isStudent = $type === \App\Models\Registration::TYPE_STUDENT;
    // Completing a visitor pass: the name and the verified number are already on
    // file, so the form starts from what is known and the badge never changes.
    $upgrade = $upgrade ?? null;
?>

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
    <div class="ns-wrap max-w-[760px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('register.kicker')); ?></span>
        </div>

        <h1 class="ns-h1 !text-[clamp(32px,4.6vw,48px)] mb-3"><?php echo e(__('register.title')); ?></h1>
        <p class="ns-body max-w-[58ch] mb-3"><?php echo e($isStudent ? __('register.lead_student') : __('register.lead_parent')); ?></p>
        <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.6] text-slate max-w-[58ch] mb-9">
            <?php echo __('register.cross_link', ['link' => '<a href="'.route('register.conference').'">'.__('register.cross_link_label').'</a>']); ?>

        </p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($upgrade): ?>
            <div class="ns-card border-t-[6px] !border-t-magenta mb-9">
                <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold mb-3"><?php echo e(__('register.upgrade.title')); ?></h2>
                <p class="ns-body mb-2 max-w-[58ch]"><?php echo e(__('register.upgrade.body')); ?></p>
                <p class="ns-meta text-[13px]"><?php echo e(__('register.upgrade.keeping', ['ticket' => $upgrade->ticket_ref])); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('duplicate')): ?>
            
            <div class="ns-card border-t-[6px] !border-t-magenta mb-9" role="alert">
                <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold mb-3"><?php echo e(__('register.duplicate.title')); ?></h2>
                <p class="ns-body mb-6 max-w-[58ch]"><?php echo e(__('register.duplicate.body')); ?></p>
                <div class="flex gap-3 flex-wrap items-center">
                    <form method="POST" action="<?php echo e(route('register.duplicate.resend')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="ns-btn ns-btn-magenta ns-btn-sm"><?php echo e(__('register.duplicate.resend')); ?></button>
                    </form>
                    <a href="<?php echo e(route('attendee.signin')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm"><?php echo e(__('register.duplicate.signin')); ?></a>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-bone-200 p-5 mb-9 font-[family-name:var(--ns-body)] text-[15px]"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="ns-card border-t-[6px] !border-t-crimson mb-9" role="alert">
                <ul class="list-none m-0 p-0 flex flex-col gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="font-[family-name:var(--ns-body)] text-[15px] text-crimson"><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] mb-9 sm:grid-cols-2 border border-[rgba(5,7,8,0.14)]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [\App\Models\Registration::TYPE_STUDENT, \App\Models\Registration::TYPE_PARENT]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('register.fair', ['type' => $option])); ?>"
                   class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                       'block px-6 py-[18px] border-s-4',
                       'bg-white border-magenta' => $type === $option,
                       'bg-white/55 border-transparent' => $type !== $option,
                   ]); ?>">
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-[3px]',
                        'text-magenta' => $type === $option,
                        'text-ink' => $type !== $option,
                    ]); ?>"><?php echo e(__("register.types.$option.label")); ?></div>
                    <div class="ns-meta text-[12.5px]"><?php echo e(__("register.types.$option.note")); ?></div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="ns-card">
            <form method="POST" action="<?php echo e(route('register.fair.store')); ?>" novalidate>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="type" value="<?php echo e($type); ?>">
                <input type="hidden" name="locale" value="<?php echo e(app()->getLocale()); ?>">
                <input type="hidden" name="utm_source" value="<?php echo e(request('utm_source')); ?>">
                <input type="hidden" name="utm_medium" value="<?php echo e(request('utm_medium')); ?>">
                <input type="hidden" name="utm_campaign" value="<?php echo e(request('utm_campaign')); ?>">

                
                <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                <div class="grid gap-[22px] sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="ns-label"><?php echo e(__('register.step1.name')); ?> <span class="ns-req">*</span></span>
                        <input type="text" name="full_name" value="<?php echo e(old('full_name', $upgrade?->full_name)); ?>" autocomplete="name"
                               placeholder="<?php echo e(__('register.step1.name_hint')); ?>" class="ns-input">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>

                    <label class="<?php echo \Illuminate\Support\Arr::toCssClasses(['sm:col-span-2' => ! $isStudent]); ?>">
                        <span class="ns-label"><?php echo e(__('register.step1.phone')); ?> <span class="ns-req">*</span></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($upgrade): ?>
                            
                            <input type="hidden" name="phone" value="<?php echo e($upgrade->phone); ?>">
                            <input type="hidden" name="phone_country" value="<?php echo e($upgrade->phone_country); ?>">
                            <span class="ns-input flex items-center gap-2 bg-bone-200 text-body-soft ns-num">
                                <span><?php echo e($upgrade->phone_country); ?></span>
                                <span><?php echo e($upgrade->phone); ?></span>
                            </span>
                            <span class="ns-hint"><?php echo e(__('register.upgrade.phone_locked')); ?></span>
                        <?php else: ?>
                            <span class="flex gap-2">
                                <select name="phone_country" class="ns-prefix !w-auto cursor-pointer" aria-label="<?php echo e(__('rsvp.step1.country_code')); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('nextstep.phone.countries'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($code); ?>" <?php if(old('phone_country', config('nextstep.phone.default_country')) === $code): echo 'selected'; endif; ?>><?php echo e($code); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <input type="tel" name="phone" value="<?php echo e(old('phone')); ?>" inputmode="numeric" autocomplete="tel"
                                       placeholder="<?php echo e(__('register.step1.phone_placeholder')); ?>" class="ns-input flex-1 min-w-0">
                            </span>
                            <span class="ns-hint"><?php echo e(__('register.step1.phone_note')); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isStudent): ?>
                        <label>
                            <span class="ns-label"><?php echo e(__('register.step1.dob')); ?> <span class="ns-req">*</span></span>
                            <input type="date" name="date_of_birth" value="<?php echo e(old('date_of_birth', $upgrade?->date_of_birth?->format('Y-m-d'))); ?>"
                                   max="<?php echo e(now()->subYears(10)->format('Y-m-d')); ?>" class="ns-input">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['date_of_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <label>
                        <span class="ns-label"><?php echo e(__('register.step1.city')); ?> <span class="ns-req">*</span></span>
                        <select name="city" class="ns-select">
                            <option value=""><?php echo e(__('register.step1.city_placeholder')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('nextstep.cities'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($city); ?>" <?php if(old('city', $upgrade?->city) === $city): echo 'selected'; endif; ?>><?php echo e($city); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isStudent): ?>
                        <label>
                            <span class="ns-label"><?php echo e(__('register.step1.stage')); ?> <span class="ns-req">*</span></span>
                            <select name="education_stage" class="ns-select">
                                <option value=""><?php echo e(__('register.step1.stage_placeholder')); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_keys(config('nextstep.education_stages')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($stage); ?>" <?php if(old('education_stage', $upgrade?->education_stage) === $stage): echo 'selected'; endif; ?>>
                                        <?php echo e(__("register.options.stage.$stage")); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <span class="ns-hint"><?php echo e(__('register.step1.stage_note')); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['education_stage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>

                        <label>
                            <span class="ns-label"><?php echo e(__('register.step1.school')); ?></span>
                            <input type="text" name="school_name" value="<?php echo e(old('school_name', $upgrade?->school_name)); ?>" class="ns-input">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['school_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isStudent): ?>
                    
                    <div class="mt-9 pt-7 border-t border-[rgba(5,7,8,0.14)]">
                        <div class="ns-eyebrow !text-[10.5px] mb-2"><?php echo e(__('register.account.title')); ?></div>
                        <p class="ns-body !text-[14.5px] text-body-soft max-w-[56ch] mb-6"><?php echo e(__('register.account.lead')); ?></p>

                        <div class="grid gap-[22px] sm:grid-cols-2">
                            <label>
                                <span class="ns-label"><?php echo e(__('register.step1.email')); ?> <span class="ns-req">*</span></span>
                                <input type="email" name="email" value="<?php echo e(old('email', $upgrade?->email)); ?>" autocomplete="email"
                                       placeholder="<?php echo e(__('register.step1.email_placeholder')); ?>" class="ns-input">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>

                            <label>
                                <span class="ns-label">
                                    <?php echo e(__('register.account.password')); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($upgrade?->isStudentAccount())): ?><span class="ns-req">*</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                                <input type="password" name="password" autocomplete="new-password"
                                       placeholder="<?php echo e(__('register.account.password_hint')); ?>" class="ns-input">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>
                        </div>

                        <ul class="list-none m-0 mt-6 p-0 flex flex-col gap-[7px]">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = __('register.account.benefits'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $benefit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="font-[family-name:var(--ns-body)] text-[14px] text-body-soft flex gap-[10px]">
                                    <span class="text-teal font-bold">✓</span><span><?php echo e($benefit); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <label class="flex gap-[14px] items-start cursor-pointer mt-8 mb-7">
                    <input type="checkbox" name="consent_terms" value="1" class="sr-only" <?php if(old('consent_terms')): echo 'checked'; endif; ?>>
                    <span class="ns-box mt-[2px]"></span>
                    <span class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.55]">
                        <?php echo e(__('register.consents.combined')); ?>

                        <a href="<?php echo e(route('terms')); ?>" target="_blank"><?php echo e(__('site.footer.terms')); ?></a> ·
                        <a href="<?php echo e(route('privacy')); ?>" target="_blank"><?php echo e(__('site.footer.privacy')); ?></a>
                    </span>
                </label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['consent_terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ns-error block mb-5"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <button type="submit" class="ns-btn ns-btn-magenta"><?php echo e(__('register.submit')); ?></button>

                <p class="ns-meta text-[12.5px] mt-5 max-w-[54ch]"><?php echo e(__('register.after_note')); ?></p>
            </form>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($upgrade)): ?>
                <div class="mt-7 pt-5 border-t border-[rgba(5,7,8,0.12)] flex items-center gap-3 flex-wrap">
                    <span class="ns-meta"><?php echo e(__('register.quick.switch_back')); ?></span>
                    <a href="<?php echo e(route('register.quick')); ?>"
                       class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                        <?php echo e(__('register.quick.switch_back_link')); ?>

                    </a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/register/fair.blade.php ENDPATH**/ ?>