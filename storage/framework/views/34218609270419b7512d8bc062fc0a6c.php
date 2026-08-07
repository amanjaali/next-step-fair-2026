<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['speaker', 'dark' => false, 'showSessions' => true]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['speaker', 'dark' => false, 'showSessions' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'flex flex-col',
    'bg-white border border-[rgba(5,7,8,0.12)]' => ! $dark,
]); ?>">
    <a href="<?php echo e(route('speakers.show', $speaker)); ?>" class="block">
        <?php if (isset($component)) { $__componentOriginalfe5b2835aa6a3ec2adfa439570add664 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe5b2835aa6a3ec2adfa439570add664 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.frame','data' => ['src' => $speaker->photoUrl(),'alt' => $speaker->t('name'),'ratio' => '1/1','center' => true,'label' => __('site.pages.speakers.title'),'class' => $dark ? '!bg-ink-700 !text-white/30' : '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.frame'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($speaker->photoUrl()),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($speaker->t('name')),'ratio' => '1/1','center' => true,'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.speakers.title')),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dark ? '!bg-ink-700 !text-white/30' : '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe5b2835aa6a3ec2adfa439570add664)): ?>
<?php $attributes = $__attributesOriginalfe5b2835aa6a3ec2adfa439570add664; ?>
<?php unset($__attributesOriginalfe5b2835aa6a3ec2adfa439570add664); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe5b2835aa6a3ec2adfa439570add664)): ?>
<?php $component = $__componentOriginalfe5b2835aa6a3ec2adfa439570add664; ?>
<?php unset($__componentOriginalfe5b2835aa6a3ec2adfa439570add664); ?>
<?php endif; ?>
    </a>

    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex flex-col', 'p-[22px] pb-6' => ! $dark, 'pt-[14px]' => $dark]); ?>">
        <div class="flex items-center gap-2 mb-[10px]">
            <span class="w-4 h-[5px] shrink-0" style="background:<?php echo e($speaker->accent()); ?>"></span>
            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-eyebrow !text-[10px] !tracking-[0.18em]', '!text-white/55' => $dark]); ?>">
                <?php echo e($speaker->speaker_type); ?>

            </span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($speaker->country !== 'IQ'): ?>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-meta ms-auto text-[11px]', '!text-white/55' => $dark]); ?>"><?php echo e($speaker->country); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <a href="<?php echo e(route('speakers.show', $speaker)); ?>"
           class="<?php echo \Illuminate\Support\Arr::toCssClasses([
               'font-[family-name:var(--ns-display)] text-[19px] font-semibold leading-[1.15] mb-[7px] block',
               'text-ink hover:text-magenta' => ! $dark,
               'text-white hover:text-white' => $dark,
           ]); ?>"><?php echo e($speaker->t('name')); ?></a>

        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-[family-name:var(--ns-body)] text-[13.5px] leading-[1.5]', 'text-body-soft' => ! $dark, 'text-white/62' => $dark]); ?>">
            <?php echo e($speaker->t('role')); ?>

        </div>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-[family-name:var(--ns-body)] text-[13.5px] leading-[1.5] text-slate', '!text-white/62' => $dark]); ?>">
            <?php echo e($speaker->t('organization')); ?>

        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSessions && ! $dark && $speaker->relationLoaded('sessions') && $speaker->sessions->isNotEmpty()): ?>
            <div class="flex gap-[6px] flex-wrap mt-[14px]">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $speaker->sessions->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('agenda', ['day' => $session->day])); ?>"
                       class="font-[family-name:var(--ns-body)] text-[11.5px] font-semibold text-ink border border-[rgba(5,7,8,0.2)] px-[9px] py-[6px] hover:border-magenta">
                        <?php echo e(__('site.common.day', ['n' => $session->day])); ?> · <span class="ns-num"><?php echo e($session->timeLabel()); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/ns/speaker-card.blade.php ENDPATH**/ ?>