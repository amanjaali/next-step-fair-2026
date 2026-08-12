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

    <div class="pb-[clamp(72px,10vw,140px)]">
        <?php if (isset($component)) { $__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.page-head','data' => ['title' => __('site.pages.speakers.title'),'lead' => __('site.pages.speakers.lead', ['count' => $total ?: $speakers->count()])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.page-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.speakers.title')),'lead' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.speakers.lead', ['count' => $total ?: $speakers->count()]))]); ?>

            <div class="flex gap-2 flex-wrap mb-9">
                <?php if (isset($component)) { $__componentOriginal104647ec22cc3c80898ef1e59da6d694 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal104647ec22cc3c80898ef1e59da6d694 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.filter-chips','data' => ['param' => 'track','active' => $activeTrack,'options' => [
                    'all' => __('site.common.all'),
                    'conference' => __('site.nav.conference'),
                    'fair' => __('site.nav.expo'),
                ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.filter-chips'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['param' => 'track','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeTrack),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                    'all' => __('site.common.all'),
                    'conference' => __('site.nav.conference'),
                    'fair' => __('site.nav.expo'),
                ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal104647ec22cc3c80898ef1e59da6d694)): ?>
<?php $attributes = $__attributesOriginal104647ec22cc3c80898ef1e59da6d694; ?>
<?php unset($__attributesOriginal104647ec22cc3c80898ef1e59da6d694); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal104647ec22cc3c80898ef1e59da6d694)): ?>
<?php $component = $__componentOriginal104647ec22cc3c80898ef1e59da6d694; ?>
<?php unset($__componentOriginal104647ec22cc3c80898ef1e59da6d694); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal104647ec22cc3c80898ef1e59da6d694 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal104647ec22cc3c80898ef1e59da6d694 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.filter-chips','data' => ['param' => 'type','active' => $activeType,'options' => [
                    'all' => __('site.common.all'),
                    'panelist' => 'Panelist',
                    'moderator' => 'Moderator',
                    'international' => 'International',
                ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.filter-chips'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['param' => 'type','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeType),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                    'all' => __('site.common.all'),
                    'panelist' => 'Panelist',
                    'moderator' => 'Moderator',
                    'international' => 'International',
                ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal104647ec22cc3c80898ef1e59da6d694)): ?>
<?php $attributes = $__attributesOriginal104647ec22cc3c80898ef1e59da6d694; ?>
<?php unset($__attributesOriginal104647ec22cc3c80898ef1e59da6d694); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal104647ec22cc3c80898ef1e59da6d694)): ?>
<?php $component = $__componentOriginal104647ec22cc3c80898ef1e59da6d694; ?>
<?php unset($__componentOriginal104647ec22cc3c80898ef1e59da6d694); ?>
<?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fallbackYear): ?>
                
                <div class="ns-card mb-9 max-w-[70ch]">
                    <p class="ns-body"><?php echo e(__('site.pages.speakers.to_be_announced')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid gap-7 sm:grid-cols-2 xl:grid-cols-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $speakers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $speaker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginalbbe272859a900381507dbc0ba164cbc6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbbe272859a900381507dbc0ba164cbc6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.speaker-card','data' => ['speaker' => $speaker]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.speaker-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['speaker' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($speaker)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbbe272859a900381507dbc0ba164cbc6)): ?>
<?php $attributes = $__attributesOriginalbbe272859a900381507dbc0ba164cbc6; ?>
<?php unset($__attributesOriginalbbe272859a900381507dbc0ba164cbc6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbbe272859a900381507dbc0ba164cbc6)): ?>
<?php $component = $__componentOriginalbbe272859a900381507dbc0ba164cbc6; ?>
<?php unset($__componentOriginalbbe272859a900381507dbc0ba164cbc6); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc)): ?>
<?php $attributes = $__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc; ?>
<?php unset($__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc)): ?>
<?php $component = $__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc; ?>
<?php unset($__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc); ?>
<?php endif; ?>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/speakers/index.blade.php ENDPATH**/ ?>