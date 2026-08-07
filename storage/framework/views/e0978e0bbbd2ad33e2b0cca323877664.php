<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'description' => null,
    'navKey' => null,
    'track' => null,
    'ogType' => 'website',
    'ogImage' => null,
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
    'description' => null,
    'navKey' => null,
    'track' => null,
    'ogType' => 'website',
    'ogImage' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<!DOCTYPE html>
<html lang="<?php echo e($localeConfig['html_lang']); ?>" dir="<?php echo e($dir); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050708">

    <title><?php echo e($title ?? config('nextstep.event.name')); ?></title>
    <meta name="description" content="<?php echo e($description ?? __('site.seo.default_description')); ?>">

    
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_keys(config('nextstep.locales')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <link rel="alternate" hreflang="<?php echo e(config("nextstep.locales.$alt.html_lang")); ?>"
              href="<?php echo e(ns_alternate_url($alt)); ?>">
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <link rel="alternate" hreflang="x-default" href="<?php echo e(ns_alternate_url('en')); ?>">

    <meta property="og:type" content="<?php echo e($ogType); ?>">
    <meta property="og:site_name" content="<?php echo e(config('nextstep.event.name')); ?>">
    <meta property="og:title" content="<?php echo e($title ?? config('nextstep.event.name')); ?>">
    <meta property="og:description" content="<?php echo e($description ?? __('site.seo.default_description')); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:locale" content="<?php echo e($localeConfig['html_lang']); ?>">
    
    <meta property="og:image" content="<?php echo e($ogImage ?? asset('assets/share/'.app()->getLocale().'-student-og.png')); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo e($title ?? config('nextstep.event.name')); ?>">

    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($title ?? config('nextstep.event.name')); ?>">
    <meta name="twitter:description" content="<?php echo e($description ?? __('site.seo.default_description')); ?>">
    <meta name="twitter:image" content="<?php echo e($ogImage ?? asset('assets/share/'.app()->getLocale().'-student-og.png')); ?>">

    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?php echo e(asset('assets/brand/nextstep-transparent-sm.png')); ?>">

    <?php echo $__env->yieldPushContent('schema'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->make('partials.analytics', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body <?php if($track): ?> data-track="<?php echo e($track); ?>" <?php endif; ?>>

<a href="#main" class="ns-skip"><?php echo e(__('site.nav.skip')); ?></a>

<?php echo $__env->make('partials.header', ['navKey' => $navKey], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main id="main">
    <?php echo e($slot); ?>

</main>

<?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/layouts/site.blade.php ENDPATH**/ ?>