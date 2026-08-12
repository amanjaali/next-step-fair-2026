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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.page-head','data' => ['kicker' => __('site.pages.news.kicker'),'title' => __('site.pages.news.title'),'lead' => __('site.pages.news.lead', ['blog' => __('site.pages.news.blog_link')])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.page-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kicker' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.news.kicker')),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.news.title')),'lead' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.news.lead', ['blog' => __('site.pages.news.blog_link')]))]); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pinned): ?>
                <article class="border-y border-[rgba(5,7,8,0.14)] mb-11">
                    <div class="grid lg:grid-cols-[1.25fr_1fr] gap-px bg-[rgba(5,7,8,0.14)]">
                        <?php if (isset($component)) { $__componentOriginalfe5b2835aa6a3ec2adfa439570add664 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe5b2835aa6a3ec2adfa439570add664 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.frame','data' => ['label' => $pinned->cover_placeholder,'src' => $pinned->coverUrl(),'alt' => $pinned->t('title'),'class' => 'min-h-[420px] !p-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.frame'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pinned->cover_placeholder),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pinned->coverUrl()),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pinned->t('title')),'class' => 'min-h-[420px] !p-6']); ?>
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
                        <div class="bg-ink text-white p-[clamp(28px,4vw,44px)] flex flex-col">
                            <div class="flex items-center gap-3 mb-[22px] flex-wrap">
                                <span class="ns-typechip bg-magenta text-white !text-[10px] !tracking-[0.2em] font-bold"><?php echo e(__('site.pages.news.pinned')); ?></span>
                                <span class="ns-eyebrow !text-white/65 !text-[10px]"><?php echo e($pinned->category?->t('name')); ?></span>
                            </div>
                            <h2 class="ns-h2 !text-[clamp(26px,3.4vw,38px)] mb-[18px] text-pretty"><?php echo e($pinned->t('title')); ?></h2>
                            <p class="font-[family-name:var(--ns-body)] text-base leading-[1.7] text-white/78 mb-[26px] max-w-[48ch]"><?php echo e($pinned->t('excerpt')); ?></p>
                            <div class="mt-auto flex items-center gap-5 flex-wrap pt-5 border-t border-white/16">
                                <span class="font-[family-name:var(--ns-body)] text-[13px] text-white/60 ns-num">
                                    <?php echo e($pinned->published_at ? ns_format_date($pinned->published_at) : ''); ?> · <?php echo e(__('site.common.min_read', ['n' => $pinned->readingTime()])); ?>

                                </span>
                                <a href="<?php echo e(route('news.show', $pinned)); ?>"
                                   class="font-[family-name:var(--ns-body)] text-sm font-bold text-white hover:text-white border-b-2 border-magenta pb-[3px]">
                                    <?php echo e(__('site.pages.news.read_announcement')); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="flex items-center justify-between gap-x-6 gap-y-3 flex-wrap mb-4">
                <div class="flex items-baseline gap-3 flex-wrap">
                    <span class="ns-meta text-[12.5px] shrink-0"><?php echo e(__('site.pages.agenda.filter_by')); ?></span>
                    <?php if (isset($component)) { $__componentOriginal104647ec22cc3c80898ef1e59da6d694 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal104647ec22cc3c80898ef1e59da6d694 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.filter-chips','data' => ['param' => 'category','active' => $activeCategory,'options' => collect(['all' => __('site.common.all')])->merge($categories->mapWithKeys(fn ($c) => [$c->slug => $c->t('name')]))->all()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.filter-chips'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['param' => 'category','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeCategory),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect(['all' => __('site.common.all')])->merge($categories->mapWithKeys(fn ($c) => [$c->slug => $c->t('name')]))->all())]); ?>
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

                <form method="GET" class="flex items-center gap-2 shrink-0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeCategory !== 'all'): ?>
                        <input type="hidden" name="category" value="<?php echo e($activeCategory); ?>">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <label for="ns-news-year" class="ns-meta text-[12.5px]"><?php echo e(__('site.pages.news.year')); ?></label>
                    <select id="ns-news-year" name="year" class="ns-select !w-auto !min-h-10 !py-0 !h-10 !text-[13.5px] cursor-pointer"
                            onchange="this.form.submit()">
                        <option value=""><?php echo e(__('site.common.all_years')); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y); ?>" <?php if((string) $activeYear === (string) $y): echo 'selected'; endif; ?>><?php echo e($y); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </form>
            </div>

            <div class="ns-meta border-b border-[rgba(5,7,8,0.14)] pb-5 mb-9">
                <?php echo e(trans_choice(__('site.pages.news.count', ['count' => $posts->total()]), $posts->total())); ?>

            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($posts->isEmpty()): ?>
                <div class="ns-card max-w-[64ch] mb-14">
                    <div class="font-[family-name:var(--ns-display)] text-[26px] font-semibold mb-3">
                        <?php echo e(__('site.pages.news.empty_title', ['filter' => $activeCategory === 'all' ? $activeYear : $activeCategory])); ?>

                    </div>
                    <p class="ns-body mb-6"><?php echo e(__('site.pages.news.empty_body')); ?></p>
                    <a href="<?php echo e(route('news')); ?>" class="ns-btn ns-btn-ink"><?php echo e(__('site.common.show_all')); ?></a>
                </div>
            <?php else: ?>
                <div class="grid gap-x-8 gap-y-9 md:grid-cols-2 xl:grid-cols-3 mb-14">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.post-card','data' => ['post' => $post]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.post-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['post' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7)): ?>
<?php $attributes = $__attributesOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7; ?>
<?php unset($__attributesOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7)): ?>
<?php $component = $__componentOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7; ?>
<?php unset($__componentOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php echo e($posts->links()); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr] border-t border-[rgba(5,7,8,0.14)] pt-11">
                <div class="ns-card">
                    <div class="ns-eyebrow !text-[11px] mb-[14px]"><?php echo e(__('site.common.press')); ?></div>
                    <h2 class="font-[family-name:var(--ns-display)] text-[30px] font-semibold leading-[1.12] mb-[14px]"><?php echo e(__('site.pages.news.press_title')); ?></h2>
                    <p class="font-[family-name:var(--ns-body)] text-[15.5px] leading-[1.7] text-body-soft mb-6 max-w-[52ch]"><?php echo e(__('site.pages.news.press_body')); ?></p>
                    <div class="flex gap-3 flex-wrap">
                        <a href="<?php echo e(route('press')); ?>" class="ns-btn ns-btn-ink"><?php echo e(__('site.cta.press_kit')); ?></a>
                        <a href="<?php echo e(route('contact')); ?>" class="ns-btn ns-btn-ghost !text-cobalt !border-[rgba(44,75,224,0.5)]"><?php echo e(__('site.cta.accreditation')); ?></a>
                    </div>
                    <div class="ns-meta text-sm mt-[22px] pt-5 border-t border-[rgba(5,7,8,0.12)]">
                        <?php echo e(config('nextstep.contact.media')); ?> · <span class="ns-num"><?php echo e(config('nextstep.contact.media_phone')); ?></span>
                    </div>
                </div>

                <div class="bg-magenta text-white p-[clamp(24px,3vw,38px)] flex flex-col">
                    <div class="ns-eyebrow !text-white/75 !text-[11px] mb-[14px]"><?php echo e(__('site.common.newsletter')); ?></div>
                    <h2 class="font-[family-name:var(--ns-display)] text-[28px] font-semibold leading-[1.12] mb-3"><?php echo e(__('site.pages.news.newsletter_title')); ?></h2>
                    <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-white/85 mb-6"><?php echo e(__('site.pages.news.newsletter_body')); ?></p>
                    <form method="POST" action="<?php echo e(route('newsletter.store')); ?>" class="mt-auto flex">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="source" value="news">
                        <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                        <label for="news-newsletter" class="sr-only"><?php echo e(__('site.common.email_address')); ?></label>
                        <input id="news-newsletter" type="email" name="email" required placeholder="<?php echo e(__('site.newsletter.placeholder')); ?>"
                               class="flex-1 min-w-0 font-[family-name:var(--ns-body)] text-[15px] p-4 border border-white/50 bg-transparent text-white placeholder:text-white/72">
                        <button type="submit" class="font-[family-name:var(--ns-body)] text-sm font-bold bg-white text-magenta border-0 px-[22px] py-4 cursor-pointer">
                            <?php echo e(__('site.cta.sign_up')); ?>

                        </button>
                    </form>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('newsletter')): ?>
                        <p class="font-[family-name:var(--ns-body)] text-[13px] text-white/90 mt-3"><?php echo e(session('newsletter')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/news/index.blade.php ENDPATH**/ ?>