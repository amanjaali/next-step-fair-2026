<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['post', 'showReadTime' => true]));

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

foreach (array_filter((['post', 'showReadTime' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $route = $post->type === 'blog' ? route('blog.show', $post) : route('news.show', $post);
?>

<a href="<?php echo e($route); ?>" class="text-ink hover:text-ink flex flex-col group">
    <?php if (isset($component)) { $__componentOriginalfe5b2835aa6a3ec2adfa439570add664 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe5b2835aa6a3ec2adfa439570add664 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.frame','data' => ['label' => $post->cover_placeholder,'src' => $post->coverUrl(),'alt' => $post->t('title'),'ratio' => '3/2','class' => 'mb-[18px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.frame'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post->cover_placeholder),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post->coverUrl()),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post->t('title')),'ratio' => '3/2','class' => 'mb-[18px]']); ?>
        <span class="absolute inset-y-0 start-0 w-2" style="background:<?php echo e($post->accent()); ?>"></span>
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

    <div class="flex items-center gap-[10px] mb-[11px] flex-wrap">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($post->category): ?>
            <span class="ns-typechip border"
                  style="color:<?php echo e($post->accent()); ?>;border-color:<?php echo e($post->accentBorder()); ?>">
                <?php echo e($post->category->t('name')); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <span class="ns-meta ns-num"><?php echo e($post->published_at ? ns_format_date($post->published_at) : ''); ?></span>
    </div>

    <h3 class="font-[family-name:var(--ns-display)] text-[23px] font-semibold leading-[1.15] mb-[10px] text-pretty group-hover:underline">
        <?php echo e($post->t('title')); ?>

    </h3>

    <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.65] text-slate mb-[14px]">
        <?php echo e($post->t('excerpt')); ?>

    </p>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showReadTime): ?>
        <span class="mt-auto font-[family-name:var(--ns-body)] text-[13px] font-bold text-magenta">
            <?php echo e(__('site.common.min_read', ['n' => $post->readingTime()])); ?>

        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</a>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/ns/post-card.blade.php ENDPATH**/ ?>