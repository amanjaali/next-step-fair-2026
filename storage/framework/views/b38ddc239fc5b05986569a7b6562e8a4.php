<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'attendee' => null,
    'variant' => 'light',
    'class' => '',
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
    'attendee' => null,
    'variant' => 'light',
    'class' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    /**
     * "Start your application" — but only for somebody who could.
     *
     * The scholarship is for students, and only a student account can apply. A
     * parent signed in to their own badge was still being shown the button, and
     * pressing it took them to a gate that told them no. That is the wrong order:
     * say who it is for first, and give them the thing they can actually do.
     *
     * A signed-out visitor keeps the button. They might be the student, and the
     * gate page is where the requirements are explained.
     */
    $isStudent = $attendee?->type === \App\Models\Registration::TYPE_STUDENT;
    $applicationIsForThem = $attendee === null || $isStudent;

    // What a parent can do instead: put it in front of the person it is for.
    $message = __('scholarship.not_for_you.message', [
        'url' => route('scholarship.home'),
    ]);

    $dark = $variant === 'dark';
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($applicationIsForThem): ?>
    <a href="<?php echo e(route('scholarship.apply')); ?>" <?php echo e($attributes->merge(['class' => 'ns-btn ns-btn-magenta '.$class])); ?>>
        <?php echo e(__('scholarship.home.cta')); ?>

    </a>
<?php else: ?>
    <div class="<?php echo e($class); ?>">
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'border-s-[6px] border-magenta px-5 py-4 max-w-[52ch]',
            'bg-white/10' => $dark,
            'bg-bone-200' => ! $dark,
        ]); ?>">
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'font-[family-name:var(--ns-body)] text-[15px] font-bold mb-1',
                'text-white' => $dark,
            ]); ?>"><?php echo e(__('scholarship.not_for_you.title')); ?></div>

            <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'font-[family-name:var(--ns-body)] text-[14px] leading-[1.6] mb-4',
                'text-white/75' => $dark,
                'text-body-soft' => ! $dark,
            ]); ?>"><?php echo e(__('scholarship.not_for_you.body')); ?></p>

            <div class="flex gap-3 flex-wrap">
                <a href="https://wa.me/?text=<?php echo e(rawurlencode($message)); ?>" target="_blank" rel="noopener"
                   class="ns-btn ns-btn-magenta ns-btn-sm"><?php echo e(__('scholarship.not_for_you.send')); ?></a>

                <a href="<?php echo e(route('register.fair', ['type' => 'student'])); ?>"
                   class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                       'ns-btn ns-btn-ghost ns-btn-sm',
                       '!text-white !border-white/40 hover:!bg-white/10' => $dark,
                   ]); ?>"><?php echo e(__('scholarship.not_for_you.register')); ?></a>
            </div>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/ns/scholarship-cta.blade.php ENDPATH**/ ?>