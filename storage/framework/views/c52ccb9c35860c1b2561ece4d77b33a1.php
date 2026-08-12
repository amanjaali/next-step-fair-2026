<?php if (isset($component)) { $__componentOriginalfefb4fd9b7004fa65f70c415ac76903e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfefb4fd9b7004fa65f70c415ac76903e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.site','data' => ['title' => $title,'description' => $description,'navKey' => $navKey,'track' => $speaker->track]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.site'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description),'navKey' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($navKey),'track' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($speaker->track)]); ?>

    <div class="pb-[clamp(72px,10vw,140px)]">
        <div class="ns-wrap pt-7">
            <nav class="ns-meta flex gap-2 items-center flex-wrap" aria-label="Breadcrumb">
                <a href="<?php echo e(route('speakers')); ?>"><?php echo e(__('site.pages.speakers.title')); ?></a>
                <span aria-hidden="true">/</span>
                <span><?php echo e($speaker->t('name')); ?></span>
            </nav>
        </div>

        <section class="ns-wrap pt-9">
            
            <div class="flex flex-wrap gap-14 items-start">
                <div class="flex-[1_1_300px] max-w-[400px] min-w-0">
                    <?php if (isset($component)) { $__componentOriginalfe5b2835aa6a3ec2adfa439570add664 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe5b2835aa6a3ec2adfa439570add664 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.frame','data' => ['src' => $speaker->photoUrl(),'alt' => $speaker->t('name'),'ratio' => '1/1','label' => __('site.pages.speakers.title'),'class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.frame'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($speaker->photoUrl()),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($speaker->t('name')),'ratio' => '1/1','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.speakers.title')),'class' => 'mb-5']); ?>
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

                    <div class="ns-card !p-[26px]">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                            __('site.common.track') => $speaker->track === 'conference' ? __('site.nav.conference') : __('site.nav.expo'),
                            __('site.common.role') => $speaker->speaker_type,
                            __('site.common.institution') => $speaker->t('organization'),
                            __('site.common.country') => $speaker->countryName(),
                            __('site.common.sessions') => (string) $speaker->sessions->count(),
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex justify-between gap-[14px] font-[family-name:var(--ns-body)] text-[13.5px] border-b border-[rgba(5,7,8,0.1)] py-[10px]">
                                <span class="text-slate"><?php echo e($key); ?></span>
                                <span class="font-bold text-end"><?php echo e($value); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($speaker->links): ?>
                            <div class="flex gap-2 flex-wrap pt-[18px]">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $speaker->links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e($link['url'] ?? '#'); ?>" target="_blank" rel="noopener"
                                       class="font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-ink border border-[rgba(5,7,8,0.2)] px-3 py-[9px] hover:border-magenta">
                                        <?php echo e($link['label'] ?? ''); ?> ↗
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="flex-[1_1_480px] min-w-0">
                    <div class="flex items-center gap-3 mb-5 flex-wrap">
                        <span class="w-[26px] h-2" style="background:<?php echo e($speaker->accent()); ?>"></span>
                        <span class="ns-eyebrow" style="color:<?php echo e($speaker->accent()); ?>">
                            <?php echo e($speaker->track === 'conference' ? __('site.nav.conference') : __('site.nav.expo')); ?> · <?php echo e($speaker->speaker_type); ?>

                        </span>
                    </div>

                    <h1 class="ns-h1 !text-[clamp(36px,5.5vw,60px)] max-w-[22ch] mb-4"><?php echo e($speaker->t('name')); ?></h1>
                    <div class="font-[family-name:var(--ns-display)] text-2xl font-semibold leading-[1.25] mb-[6px]"><?php echo e($speaker->t('role')); ?></div>
                    <div class="font-[family-name:var(--ns-body)] text-[17px] text-slate mb-9"><?php echo e($speaker->t('organization')); ?></div>

                    <div class="border-t border-[rgba(5,7,8,0.14)] pt-[30px] mb-10">
                        <div class="ns-eyebrow !text-[11px] mb-[18px]"><?php echo e(__('site.common.biography')); ?></div>
                        <div class="ns-prose">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim(strip_tags($speaker->t('bio')))): ?>
                                <?php echo $speaker->t('bio'); ?>

                            <?php else: ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $speaker->bioParagraphs(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paragraph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <p><?php echo e(strip_tags($paragraph)); ?></p>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($speaker->sessions->isNotEmpty()): ?>
                        <div class="border-t border-[rgba(5,7,8,0.14)] pt-[30px] mb-10">
                            <div class="ns-eyebrow !text-[11px] mb-[18px]"><?php echo e(__('site.pages.speakers.sessions_2026')); ?></div>
                            <div class="flex flex-col">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $speaker->sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(route('agenda', ['day' => $session->day])); ?>"
                                       class="text-ink hover:text-ink bg-white border border-[rgba(5,7,8,0.14)] px-6 py-[22px] mb-3 flex flex-wrap gap-x-[22px] gap-y-[14px] items-center"
                                       style="border-inline-start:6px solid <?php echo e($speaker->accent()); ?>">
                                        <div class="flex-none">
                                            <div class="ns-num font-[family-name:var(--ns-display)] text-lg font-semibold"><?php echo e($session->timeLabel()); ?></div>
                                            <div class="ns-meta text-[12.5px] mt-[3px]"><?php echo e(__('site.common.day', ['n' => $session->day])); ?> · <?php echo e(ns_day_date($session->day)); ?></div>
                                        </div>
                                        <div class="flex-[1_1_260px] min-w-0">
                                            <div class="font-[family-name:var(--ns-body)] text-[16.5px] font-bold leading-[1.35] mb-[5px]"><?php echo e($session->t('title')); ?></div>
                                            <div class="ns-meta"><?php echo e($session->hallLabel()); ?> · <?php echo e($session->pivot->role); ?></div>
                                        </div>
                                        <span class="font-[family-name:var(--ns-body)] text-[13px] font-bold text-cobalt whitespace-nowrap flex-none">
                                            <?php echo e(__('site.cta.view_in_agenda')); ?>

                                        </span>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($speaker->topics): ?>
                        <div class="border-t border-[rgba(5,7,8,0.14)] pt-[30px]">
                            <div class="ns-eyebrow !text-[11px] mb-[18px]"><?php echo e(__('site.common.topics')); ?></div>
                            <div class="flex gap-2 flex-wrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($speaker->topics[app()->getLocale()] ?? $speaker->topics['en'] ?? $speaker->topics); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_string($topic)): ?>
                                        <span class="font-[family-name:var(--ns-body)] text-[13px] font-semibold text-ink border border-[rgba(5,7,8,0.2)] px-[13px] py-[9px]"><?php echo e($topic); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($related->isNotEmpty()): ?>
            <section class="bg-white border-t border-[rgba(5,7,8,0.12)] mt-20">
                <div class="ns-wrap py-16">
                    <div class="flex items-baseline justify-between gap-6 flex-wrap mb-8">
                        <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)]"><?php echo e(__('site.pages.speakers.also_speaking')); ?></h2>
                        <a href="<?php echo e(route('speakers')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm"><?php echo e(__('site.cta.all_speakers', ['count' => $related->count() + 1])); ?></a>
                    </div>
                    <div class="grid gap-7 sm:grid-cols-2 xl:grid-cols-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $other): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginalbbe272859a900381507dbc0ba164cbc6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbbe272859a900381507dbc0ba164cbc6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.speaker-card','data' => ['speaker' => $other,'showSessions' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.speaker-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['speaker' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($other),'showSessions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/speakers/show.blade.php ENDPATH**/ ?>