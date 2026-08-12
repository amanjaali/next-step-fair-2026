<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['opportunity']));

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

foreach (array_filter((['opportunity']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $daysLeft = $opportunity->daysLeft();
?>

<a href="<?php echo e(route('opportunities.show', $opportunity->slug)); ?>"
   
   class="bg-white border border-[rgba(5,7,8,0.1)] border-t-4 <?php echo e($opportunity->isClosingSoon() ? 'border-t-crimson' : 'border-t-magenta'); ?> ns-radius ns-shadow-sm p-6 flex flex-col no-underline transition-shadow duration-200 hover:border-t-ink">

    <div class="flex items-start justify-between gap-4 mb-4">
        <span class="ns-eyebrow !text-[9.5px] !text-magenta"><?php echo e(__("opportunities.kinds.{$opportunity->kind}")); ?></span>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isClosingSoon()): ?>
            <span class="ns-urgent-pill ns-num font-[family-name:var(--ns-body)] text-[11.5px]">
                <?php echo e(trans_choice('opportunities.closing_in', max($daysLeft, 0), ['count' => max($daysLeft, 0)])); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold leading-[1.25] text-ink mb-2">
        <?php echo e($opportunity->t('title')); ?>

    </div>

    <p class="ns-body !text-[14px] text-body-soft mb-5 flex-1"><?php echo e($opportunity->t('summary')); ?></p>

    <div class="flex items-center gap-3 pt-4 border-t border-[rgba(5,7,8,0.1)] mt-auto">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->logo()): ?>
            <img src="<?php echo e($opportunity->logo()); ?>" alt="" class="h-7 w-auto max-w-[76px] object-contain shrink-0">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <span class="ns-meta text-[12px] min-w-0"><?php echo e($opportunity->partner()); ?></span>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->places): ?>
            <span class="ns-meta text-[12px] ns-num ms-auto whitespace-nowrap">
                <?php echo e(trans_choice('opportunities.places', $opportunity->places, ['count' => $opportunity->places])); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</a>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/ns/opportunity-card.blade.php ENDPATH**/ ?>