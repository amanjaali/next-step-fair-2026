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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.page-head','data' => ['kicker' => __('site.pages.sponsors.kicker'),'title' => __('site.pages.sponsors.title'),'lead' => __('site.pages.sponsors.lead', ['count' => 41, 'returning' => 27])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.page-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kicker' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.sponsors.kicker')),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.sponsors.title')),'lead' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.sponsors.lead', ['count' => 41, 'returning' => 27]))]); ?>

            <div class="ns-hairgrid grid-cols-2 xl:grid-cols-4 mb-14">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-6 pt-[26px] pb-7">
                        <div class="ns-stat ns-num text-[36px] mb-[10px]"><?php echo e($stat['value']); ?></div>
                        <div class="ns-eyebrow !text-[10.5px]"><?php echo e($stat['label']); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <section class="mb-14">
                <div class="flex items-baseline gap-5 mb-6">
                    <span class="ns-eyebrow"><?php echo e(__('site.pages.sponsors.strategic')); ?></span>
                    <span class="ns-rule"></span>
                </div>
                <div class="ns-hairgrid lg:grid-cols-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $strategic; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-[clamp(24px,3vw,38px)] flex gap-[26px] items-center border-t-[6px] border-cobalt flex-wrap">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($partner->logo_path): ?>
                                <img src="<?php echo e(asset('assets/'.$partner->logo_path)); ?>" alt="<?php echo e($partner->t('name')); ?>" class="h-[86px] w-auto flex-none">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="min-w-[200px] flex-1">
                                <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold leading-[1.15] mb-2"><?php echo e($partner->t('name')); ?></div>
                                <div class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.6] text-slate"><?php echo e($partner->t('description')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($section['items']->isEmpty()) continue; ?>
                <section class="mb-13" style="margin-bottom:52px">
                    <div class="flex items-baseline gap-5 mb-2 flex-wrap">
                        <span class="ns-eyebrow" style="color:<?php echo e($section['accent']); ?>"><?php echo e($section['tier']); ?></span>
                        <span class="ns-rule"></span>
                        <span class="ns-meta text-[12.5px]"><?php echo e($section['items']->count()); ?></span>
                    </div>
                    <div class="ns-meta text-sm mb-5 max-w-[80ch]"><?php echo e($section['note']); ?></div>

                    <div class="ns-hairgrid sm:grid-cols-2 lg:grid-cols-<?php echo e(min($section['cols'], 4)); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $section['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a <?php if($item->website): ?> href="<?php echo e($item->website); ?>" target="_blank" rel="noopener" <?php endif; ?>
                               class="p-6 flex flex-col gap-[14px] text-ink hover:text-ink">
                                <div class="bg-bone-200 flex items-center justify-center p-3 text-center"
                                     style="height:<?php echo e($section['logoHeight']); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->logo_path): ?>
                                        <img src="<?php echo e(asset('assets/'.$item->logo_path)); ?>" alt="<?php echo e($item->t('name')); ?>" class="max-h-full max-w-full object-contain" loading="lazy">
                                    <?php else: ?>
                                        <span class="font-[family-name:var(--ns-body)] text-xs font-semibold text-muted leading-[1.3]"><?php echo e($item->t('name')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div>
                                    <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold leading-[1.3] mb-[5px]"><?php echo e($item->t('name')); ?></div>
                                    <div class="ns-meta leading-[1.5]"><?php echo e($item->t('description')); ?></div>
                                </div>
                                <div class="mt-auto flex items-center gap-[9px] flex-wrap">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->t('badge')): ?>
                                        <span class="ns-typechip border !text-[10px]" style="color:<?php echo e($section['accent']); ?>;border-color:<?php echo e($section['accent']); ?>66"><?php echo e($item->t('badge')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->website): ?>
                                        <span class="font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-cobalt"><?php echo e(__('site.cta.website')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <section class="border-t border-[rgba(5,7,8,0.14)] pt-12" id="become-a-sponsor">
                <div class="grid gap-14 lg:grid-cols-2 items-start mb-9">
                    <div>
                        <h2 class="ns-h2 !text-[clamp(28px,3.4vw,40px)] mb-[18px]"><?php echo e(__('site.pages.sponsors.become_title')); ?></h2>
                        <p class="ns-body mb-4"><?php echo e(__('site.pages.sponsors.become_p1', ['count' => 32])); ?></p>
                        <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-slate"><?php echo e(__('site.pages.sponsors.become_p2')); ?></p>
                    </div>

                    <div class="ns-panel-ink">
                        <div class="ns-eyebrow !text-white/50 !text-[11px] mb-[14px]"><?php echo e(__('site.pages.sponsors.talk_to_team')); ?></div>
                        <div class="font-[family-name:var(--ns-display)] text-2xl font-semibold leading-[1.2] mb-5 break-words">
                            <a href="mailto:<?php echo e(config('nextstep.contact.partnerships')); ?>" class="text-white hover:text-white"><?php echo e(config('nextstep.contact.partnerships')); ?></a>
                        </div>
                        <div class="flex flex-col gap-3">
                            <?php $deck = \App\Models\Download::where('group', 'deck')->where('published', true)->first(); ?>
                            <a href="<?php echo e($deck?->url() ?: '#'); ?>" class="ns-btn ns-btn-magenta !justify-start"><?php echo e(__('site.pages.sponsors.download_deck')); ?></a>
                            <a href="<?php echo e(route('exhibit')); ?>" class="ns-btn ns-btn-ghost-light !justify-start"><?php echo e(__('site.pages.sponsors.request_call')); ?></a>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[760px] grid gap-px bg-[rgba(5,7,8,0.14)]" style="grid-template-columns:1.4fr repeat(4,1fr)">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tierTable['head'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $heading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-ink text-white px-[18px] py-4 ns-eyebrow !text-[10.5px] !text-white flex items-center"><?php echo e($heading); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tierTable['rows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'px-[18px] py-4 font-[family-name:var(--ns-body)] text-[13.5px] flex items-center',
                                        'bg-bone-50 font-bold' => $index === 0,
                                        'bg-white' => $index !== 0,
                                        'text-disabled' => $cell === '—',
                                    ]); ?>"><?php echo e($cell); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </section>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/directory/sponsors.blade.php ENDPATH**/ ?>