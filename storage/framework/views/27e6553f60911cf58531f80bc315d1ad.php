<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['session', 'saveable' => true]));

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

foreach (array_filter((['session', 'saveable' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="grid gap-7 border-t border-[rgba(5,7,8,0.14)] py-[26px] items-start lg:grid-cols-[130px_minmax(0,1fr)_220px]">
    <div>
        <div class="ns-num font-[family-name:var(--ns-display)] text-[22px] font-semibold"><?php echo e($session->timeLabel()); ?></div>
        <div class="ns-meta ns-num mt-1 text-[12.5px]"><?php echo e($session->duration_label); ?></div>
    </div>

    <div>
        <div class="flex items-center gap-[9px] mb-[9px] flex-wrap">
            <span class="ns-typechip <?php echo e($session->chipClass()); ?>"><?php echo e($session->typeLabel()); ?></span>
            <span class="ns-meta text-xs"><?php echo e($session->hallLabel()); ?> · <?php echo e($session->languages); ?></span>
        </div>

        <h3 class="font-[family-name:var(--ns-display)] text-2xl font-semibold leading-[1.15] mb-2">
            <?php echo e($session->t('title')); ?>

        </h3>

        <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.6] text-slate max-w-[66ch]">
            <?php echo e($session->t('description')); ?>

        </p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session->relationLoaded('speakers') && $session->speakers->isNotEmpty()): ?>
            <div class="flex flex-wrap gap-x-3 gap-y-1 mt-[10px]">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $session->speakers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $speaker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('speakers.show', $speaker)); ?>"
                       class="font-[family-name:var(--ns-body)] text-[13.5px] font-semibold text-cobalt">
                        <?php echo e($speaker->t('name')); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?><span class="text-slate"> ·</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php elseif($session->t('who')): ?>
            <div class="font-[family-name:var(--ns-body)] text-[13.5px] font-semibold text-cobalt mt-[10px]">
                <?php echo e($session->t('who')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="flex lg:justify-end">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($saveable && $session->bookable): ?>
            
            <?php ($isSaved = in_array($session->id, ns_saved_session_ids(), true)); ?>
            <form method="POST" action="<?php echo e(route('me.agenda.toggle', $session)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'ns-btn ns-btn-sm whitespace-nowrap',
                    'ns-btn-ghost' => ! $isSaved,
                    'ns-btn-magenta' => $isSaved,
                ]); ?>">
                    <?php echo e($isSaved ? __('attendee.agenda.saved_label') : __('site.pages.agenda.add_to_agenda')); ?>

                </button>
            </form>
        <?php elseif($session->track === 'conference' && ! $session->bookable): ?>
            
            <span class="ns-meta text-[12.5px] lg:text-end"><?php echo e(__('site.pages.agenda.by_invitation')); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/ns/session-row.blade.php ENDPATH**/ ?>