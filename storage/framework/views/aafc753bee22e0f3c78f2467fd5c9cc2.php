<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'scholarshipNav' => null,
    'attendee' => null,
    'application' => null,
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
    'title' => null,
    'scholarshipNav' => null,
    'attendee' => null,
    'application' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // The programme carries its own sub-navigation. It is a different thing from
    // the expo with its own rules and its own deadlines, and mixing the two menus
    // is what makes a site feel like it has no shape.
    $items = [
        ['key' => 'home', 'label' => __('scholarship.nav.home'), 'route' => 'scholarship.home'],
        ['key' => 'about', 'label' => __('scholarship.nav.about'), 'route' => 'scholarship.about'],
        ['key' => 'universities', 'label' => __('scholarship.nav.universities'), 'route' => 'scholarship.universities'],
        ['key' => 'guidelines', 'label' => __('scholarship.nav.guidelines'), 'route' => 'scholarship.guidelines'],
        ['key' => 'recipients', 'label' => __('scholarship.nav.recipients'), 'route' => 'scholarship.recipients'],
        ['key' => 'apply', 'label' => __('scholarship.nav.apply'), 'route' => 'scholarship.apply'],
    ];

    $hasApplication = $application !== null;
?>

<?php if (isset($component)) { $__componentOriginalfefb4fd9b7004fa65f70c415ac76903e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfefb4fd9b7004fa65f70c415ac76903e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.site','data' => ['title' => $title,'navKey' => 'scholarship']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.site'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'navKey' => 'scholarship']); ?>
    
    <div class="bg-ink text-white">
        <div class="ns-wrap flex items-center justify-between gap-6 flex-wrap py-[10px]">
            <a href="<?php echo e(route('scholarship.home')); ?>" class="flex items-baseline gap-3 no-underline">
                <span class="font-[family-name:var(--ns-display)] text-[15px] font-bold text-white"><?php echo e(__('scholarship.name')); ?></span>
                <span class="ns-num font-[family-name:var(--ns-body)] text-[12px] text-white/60"><?php echo e(config('scholarship.cycle')); ?></span>
            </a>

            <nav class="flex items-center gap-[clamp(12px,1.6vw,26px)] flex-wrap" aria-label="<?php echo e(__('scholarship.name')); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route($item['route'])); ?>"
                       class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                           'font-[family-name:var(--ns-body)] text-[13.5px] font-medium py-1 whitespace-nowrap border-b-2 no-underline',
                           'text-white border-magenta' => $scholarshipNav === $item['key'],
                           'text-white/65 border-transparent hover:text-white' => $scholarshipNav !== $item['key'],
                       ]); ?>"
                       <?php if($scholarshipNav === $item['key']): ?> aria-current="page" <?php endif; ?>><?php echo e($item['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasApplication): ?>
                    <a href="<?php echo e(route('scholarship.status')); ?>"
                       class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                           'font-[family-name:var(--ns-body)] text-[13.5px] font-medium py-1 whitespace-nowrap border-b-2 no-underline',
                           'text-white border-magenta' => $scholarshipNav === 'status',
                           'text-white/65 border-transparent hover:text-white' => $scholarshipNav !== 'status',
                       ]); ?>"><?php echo e(__('scholarship.nav.status')); ?></a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>
        </div>
    </div>

    <?php echo e($slot); ?>

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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/layouts/scholarship.blade.php ENDPATH**/ ?>