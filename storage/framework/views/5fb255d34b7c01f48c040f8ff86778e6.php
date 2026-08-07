<?php session()->flash('conversion', $conversion); ?>
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
    <div class="ns-wrap max-w-[1080px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('register.done.kicker')); ?></span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.4vw,46px)] mb-3">
            <?php echo e(__('register.done.title', ['name' => $registration->firstName()])); ?>

        </h1>
        <p class="ns-body max-w-[60ch] mb-9"><?php echo e(__('register.done.lead')); ?></p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-bone-200 p-5 mb-9 font-[family-name:var(--ns-body)] text-[15px] max-w-[62ch]"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid gap-8 lg:grid-cols-[1fr_1.05fr] items-start">

            
            <div class="bg-magenta text-white px-8 pt-[34px] pb-[30px]">
                <div class="flex justify-between items-start gap-4 mb-[26px]">
                    <div>
                        <div class="font-[family-name:var(--ns-display)] text-[21px] font-bold leading-[1.1]">
                            Next Step Fair<br><?php echo e(config('nextstep.event.year')); ?>

                        </div>
                        <div class="ns-eyebrow !text-white/75 !text-[10px] mt-[7px]"><?php echo e(__('site.common.edition_4')); ?></div>
                    </div>
                    <span class="bg-white text-magenta font-[family-name:var(--ns-display)] text-[10.5px] font-bold tracking-[0.18em] px-[10px] py-[6px]">
                        <?php echo e($registration->typeChip()); ?>

                    </span>
                </div>

                <div class="font-[family-name:var(--ns-display)] text-[clamp(22px,3vw,30px)] font-bold leading-[1.05] tracking-[-0.02em] mb-[7px]">
                    <?php echo e($registration->full_name); ?>

                </div>
                <div class="font-[family-name:var(--ns-body)] text-sm text-white/85 mb-6">
                    <?php echo e($registration->city); ?> · <?php echo e($registration->daysLabel()); ?>

                </div>

                <div class="bg-white p-4 inline-block">
                    <img src="<?php echo e($qrUrl); ?>" alt="QR" width="180" height="180" class="block w-[180px] h-[180px]">
                </div>

                <div class="ns-num font-[family-name:var(--ns-display)] text-[11px] tracking-[0.12em] text-white/75 mt-[14px]">
                    <?php echo e(__('register.done.ticket', ['id' => $registration->ticket_ref])); ?>

                </div>
            </div>

            <div>
                <div class="ns-card mb-5">
                    <div class="ns-eyebrow !text-[11px] mb-4"><?php echo e(__('register.done.next_steps')); ?></div>
                    <div class="flex flex-col gap-3">
                        <a href="<?php echo e(route('ticket.png', $registration->ticket_id)); ?>" class="ns-btn ns-btn-ink !justify-start"><?php echo e(__('register.done.download_png')); ?></a>
                        <a href="<?php echo e(route('ticket.pdf', $registration->ticket_id)); ?>" class="ns-btn ns-btn-ghost !justify-start"><?php echo e(__('register.done.download_pdf')); ?></a>
                        <a href="<?php echo e(route('ticket.ics', $registration->ticket_id)); ?>" class="ns-btn ns-btn-ghost !justify-start"><?php echo e(__('register.done.calendar')); ?></a>
                    </div>
                </div>

                <div class="ns-card mb-5">
                    <div class="ns-eyebrow !text-[11px] mb-4"><?php echo e(__('register.done.delivery')); ?></div>
                    <div class="flex flex-col gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $registration->messages()->latest()->take(3)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex gap-3 items-center">
                                <span class="w-[9px] h-[9px] shrink-0 <?php echo e($message->isFailed() ? 'bg-crimson' : 'bg-magenta'); ?>"></span>
                                <span class="font-[family-name:var(--ns-body)] text-sm">
                                    <?php echo e(__('register.done.delivered_to', [
                                        'phone' => $registration->phone_country.' '.$registration->maskedPhone(),
                                        'status' => $message->status,
                                    ])); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex gap-3 items-center">
                            <span class="w-[9px] h-[9px] bg-cobalt shrink-0"></span>
                            <span class="font-[family-name:var(--ns-body)] text-sm">
                                <?php echo e(__('register.done.reminder_scheduled', ['date' => ns_format_date(\Illuminate\Support\Carbon::parse(config('nextstep.event.start_date'))->subDays(3), false)])); ?>

                            </span>
                        </div>
                        <div class="flex gap-3 items-center">
                            <span class="w-[9px] h-[9px] bg-cobalt shrink-0"></span>
                            <span class="font-[family-name:var(--ns-body)] text-sm">
                                <?php echo e(__('register.done.directions_scheduled', ['date' => ns_day_date(1).', 08:00'])); ?>

                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-bone-200 px-[26px] py-6 font-[family-name:var(--ns-body)] text-sm leading-[1.65] text-body-soft">
                    <?php echo e(__('register.done.privacy_note')); ?>

                </div>

                <div class="mt-6 flex gap-[14px] flex-wrap">
                    <a href="<?php echo e(route('agenda')); ?>" class="ns-btn ns-btn-ghost !text-magenta !border-[rgba(182,70,152,0.5)]"><?php echo e(__('site.cta.build_agenda')); ?></a>
                    <a href="<?php echo e(route('home')); ?>" class="ns-btn ns-btn-ghost"><?php echo e(__('site.cta.back_home')); ?></a>
                </div>
            </div>
        </div>

        <div class="mt-14 pt-12 border-t border-[rgba(5,7,8,0.14)]">
            <?php if (isset($component)) { $__componentOriginal557afde84eed89420173748bb43d8cb8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal557afde84eed89420173748bb43d8cb8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.share-block','data' => ['registration' => $registration]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.share-block'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['registration' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($registration)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal557afde84eed89420173748bb43d8cb8)): ?>
<?php $attributes = $__attributesOriginal557afde84eed89420173748bb43d8cb8; ?>
<?php unset($__attributesOriginal557afde84eed89420173748bb43d8cb8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal557afde84eed89420173748bb43d8cb8)): ?>
<?php $component = $__componentOriginal557afde84eed89420173748bb43d8cb8; ?>
<?php unset($__componentOriginal557afde84eed89420173748bb43d8cb8); ?>
<?php endif; ?>
        </div>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/register/done.blade.php ENDPATH**/ ?>