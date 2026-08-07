<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => null,
    'src' => null,
    'alt' => '',
    'ratio' => null,
    'center' => false,
    'height' => null,
]));

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

foreach (array_filter(([
    'label' => null,
    'src' => null,
    'alt' => '',
    'ratio' => null,
    'center' => false,
    'height' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div <?php echo e($attributes->class([
        'ns-frame',
        'ns-frame-center' => $center || ! $src,
    ])->merge([
        'style' => trim(($ratio ? "aspect-ratio:$ratio;" : '').($height ? "height:$height;" : '')),
    ])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($src): ?>
        <img src="<?php echo e($src); ?>" alt="<?php echo e($alt); ?>" loading="lazy" decoding="async">
    <?php elseif($label): ?>
        <span><?php echo e($label); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/ns/frame.blade.php ENDPATH**/ ?>