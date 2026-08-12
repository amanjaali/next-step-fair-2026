<?php if (isset($component)) { $__componentOriginalfefb4fd9b7004fa65f70c415ac76903e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfefb4fd9b7004fa65f70c415ac76903e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.site','data' => ['title' => $title,'description' => $description,'navKey' => $navKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.site'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description),'navKey' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($navKey)]); ?>
    <div class="pb-[clamp(72px,10vw,140px)]">
        <?php if (isset($component)) { $__componentOriginal24e6ccf8afa0954b484bb8d878cdaebc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal24e6ccf8afa0954b484bb8d878cdaebc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.page-head','data' => ['kicker' => $page->t('kicker'),'title' => $page->t('title'),'lead' => $page->t('standfirst')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.page-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kicker' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page->t('kicker')),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page->t('title')),'lead' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page->t('standfirst'))]); ?>
            <div class="flex gap-3 flex-wrap mb-11">
                <a href="<?php echo e(route('register.fair')); ?>" class="ns-btn ns-btn-magenta"><?php echo e(__('site.cta.register_fair')); ?></a>
                <a href="<?php echo e(route('universities')); ?>" class="ns-btn ns-btn-ghost"><?php echo e(__('site.cta.who_is_exhibiting')); ?></a>
                <a href="<?php echo e(route('floorplan')); ?>" class="ns-btn ns-btn-ghost"><?php echo e(__('site.pages.floorplan.title')); ?></a>
            </div>

            <div class="flex flex-wrap gap-14 items-start border-t border-[rgba(5,7,8,0.14)] pt-9">
                <div class="flex-[1_1_520px] min-w-0">
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <section class="mb-11">
                            <div class="flex items-baseline gap-[14px] mb-[14px]">
                                <span class="ns-num font-[family-name:var(--ns-display)] text-[13px] font-bold text-magenta"><?php echo e($section['n']); ?></span>
                                <h2 class="font-[family-name:var(--ns-display)] text-[28px] font-semibold leading-[1.15]"><?php echo e($section['h']); ?></h2>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $section['p']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paragraph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <p class="ns-body mb-[18px] max-w-[68ch]"><?php echo e($paragraph); ?></p>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($section['list']): ?>
                                <ul class="list-none m-0 p-0 flex flex-col gap-[10px] mt-5">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $section['list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="font-[family-name:var(--ns-body)] text-base leading-[1.65] text-body-soft flex gap-[14px] max-w-[68ch]">
                                            <span class="ns-bar bg-magenta mt-[9px]"></span><span><?php echo e($item); ?></span>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </ul>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </section>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <aside class="flex-[1_1_260px] max-w-[320px] min-w-0">
                    <div class="ns-card !p-6 mb-5">
                        <div class="ns-eyebrow !text-[10.5px] mb-[10px]"><?php echo e(__('site.common.location')); ?></div>
                        <dl class="m-0">
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                                [__('site.common.dates'), ns_event_dates(), false],
                                [__('site.common.venue'), config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'), false],
                                [__('site.common.hours'), config('nextstep.event.opening_hours'), true],
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $isolate]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex flex-col gap-[3px] py-[10px] border-b border-[rgba(5,7,8,0.1)]">
                                    <dt class="ns-eyebrow !text-[9.5px]"><?php echo e($label); ?></dt>
                                    <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses(['m-0 font-[family-name:var(--ns-body)] text-sm leading-[1.5] text-body', 'ns-num' => $isolate]); ?>"><?php echo e($value); ?></dd>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="flex flex-col gap-[3px] pt-[10px]">
                                <dt class="ns-eyebrow !text-[9.5px]"><?php echo e(__('site.common.free_entry')); ?></dt>
                                <dd class="m-0 font-[family-name:var(--ns-body)] text-sm leading-[1.5] text-body"><?php echo e(config('nextstep.event.venue.address.'.app()->getLocale())); ?></dd>
                            </div>
                        </dl>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($aside['title'])): ?>
                        <div class="ns-card !p-6 mb-5">
                            <div class="ns-eyebrow !text-[10.5px] mb-[10px]"><?php echo e($aside['title']); ?></div>
                            <div class="font-[family-name:var(--ns-body)] text-sm leading-[1.6] text-body-soft mb-[14px]"><?php echo e($aside['body']); ?></div>
                            <a href="mailto:<?php echo e($aside['contact']); ?>" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta"><?php echo e($aside['contact']); ?></a>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="ns-panel-ink !p-6">
                        <div class="ns-eyebrow !text-white/50 !text-[10.5px] mb-3"><?php echo e(__('site.cta.register_fair')); ?></div>
                        <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.6] text-white/80 mb-4"><?php echo e(__('site.common.free_entry')); ?></p>
                        <a href="<?php echo e(route('register.fair')); ?>" class="ns-btn ns-btn-magenta w-full"><?php echo e(__('site.cta.register_fair')); ?></a>
                    </div>
                </aside>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sessions->isNotEmpty()): ?>
                <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)] mb-6 mt-4"><?php echo e(__('site.pages.agenda.title')); ?></h2>
                <div class="flex flex-col mb-8">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal25e4ebd3f5df598888dcf51de1907dd5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25e4ebd3f5df598888dcf51de1907dd5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.session-row','data' => ['session' => $session,'saveable' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.session-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['session' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($session),'saveable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25e4ebd3f5df598888dcf51de1907dd5)): ?>
<?php $attributes = $__attributesOriginal25e4ebd3f5df598888dcf51de1907dd5; ?>
<?php unset($__attributesOriginal25e4ebd3f5df598888dcf51de1907dd5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25e4ebd3f5df598888dcf51de1907dd5)): ?>
<?php $component = $__componentOriginal25e4ebd3f5df598888dcf51de1907dd5; ?>
<?php unset($__componentOriginal25e4ebd3f5df598888dcf51de1907dd5); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <a href="<?php echo e(route('agenda')); ?>" class="ns-btn ns-btn-ghost"><?php echo e(__('site.cta.full_agenda')); ?></a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/pages/fair.blade.php ENDPATH**/ ?>