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
    <div class="ns-wrap max-w-[1040px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('attendee.profile.title')); ?></span>
        </div>

        <div class="flex justify-between items-end gap-6 flex-wrap mb-9">
            <div class="flex items-center gap-5 min-w-0">
                
                <div class="w-[72px] h-[72px] shrink-0 border border-[rgba(5,7,8,0.16)] bg-bone-200 overflow-hidden flex items-center justify-center">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->photoUrl()): ?>
                        <img src="<?php echo e($registration->photoUrl()); ?>" alt="" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="font-[family-name:var(--ns-display)] text-[24px] font-bold text-slate">
                            <?php echo e($registration->initials()); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="min-w-0">
                    <h1 class="ns-h1 !text-[clamp(28px,4vw,42px)]">
                        <?php echo e(__('attendee.profile.welcome', ['name' => $registration->firstName()])); ?>

                    </h1>
                    <a href="<?php echo e(route('me.edit')); ?>"
                       class="font-[family-name:var(--ns-body)] text-[13.5px] font-bold text-magenta">
                        <?php echo e(__('attendee.edit.edit_link')); ?>

                    </a>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('attendee.signout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="ns-btn ns-btn-ghost ns-btn-sm"><?php echo e(__('attendee.nav.sign_out')); ?></button>
            </form>
        </div>


        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="border-s-[6px] border-magenta bg-white px-6 py-5 mb-9 flex items-center justify-between gap-6 flex-wrap">
            <div class="min-w-0">
                <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-1"><?php echo e(__('share.title')); ?></div>
                <p class="ns-body !text-[14.5px] max-w-[54ch]"><?php echo e(__('share.lead')); ?></p>
            </div>
            <a href="<?php echo e(route('me.share')); ?>" class="ns-btn ns-btn-magenta ns-btn-sm shrink-0"><?php echo e(__('share.kicker')); ?></a>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->isQuickPass()): ?>
            <div class="border-s-[6px] border-[#F2A93B] bg-[#FFF8EC] px-6 py-5 mb-9">
                <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-1">
                    <?php echo e(__('attendee.profile.quick_pass')); ?>

                </div>
                <p class="ns-body !text-[14.5px] mb-4 max-w-[62ch]"><?php echo e(__('attendee.profile.quick_pass_note')); ?></p>
                <a href="<?php echo e(route('register.fair')); ?>" class="ns-btn ns-btn-magenta ns-btn-sm">
                    <?php echo e(__('attendee.profile.quick_pass_upgrade')); ?>

                </a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->type === \App\Models\Registration::TYPE_STUDENT): ?>
            <div class="border-s-[6px] border-magenta bg-white px-6 py-5 mb-9">
                <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-1">
                    <?php echo e(__('attendee.services.title')); ?>

                </div>
                <p class="ns-body !text-[14.5px] mb-5 max-w-[64ch]"><?php echo e(__('attendee.services.lead')); ?></p>

                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="<?php echo e(route('me.agenda')); ?>" class="border border-[rgba(5,7,8,0.16)] bg-bone-50 px-4 py-3 block hover:border-magenta">
                        <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]"><?php echo e(__('attendee.services.agenda')); ?></div>
                        <div class="ns-meta text-[12px]"><?php echo e(__('attendee.services.agenda_note')); ?></div>
                    </a>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->canApplyForScholarship()): ?>
                        <a href="<?php echo e(route('scholarship.home')); ?>" class="border border-[rgba(5,7,8,0.16)] bg-bone-50 px-4 py-3 block hover:border-magenta">
                            <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]"><?php echo e(__('attendee.services.scholarship')); ?></div>
                            <div class="ns-meta text-[12px]"><?php echo e(__('attendee.services.scholarship_note')); ?></div>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <a href="<?php echo e(route('opportunities')); ?>" class="border border-[rgba(5,7,8,0.16)] bg-bone-50 px-4 py-3 block hover:border-magenta">
                        <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]"><?php echo e(__('attendee.services.opportunities')); ?></div>
                        <div class="ns-meta text-[12px]"><?php echo e(__('attendee.services.opportunities_note')); ?></div>
                    </a>

                    
                    <a href="<?php echo e(route('me.interests')); ?>" class="border border-[rgba(5,7,8,0.16)] bg-bone-50 px-4 py-3 block hover:border-magenta">
                        <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]">
                            <?php echo e($registration->matches()->count()
                                ? __('attendee.matches.title')
                                : __('attendee.services.matches')); ?>

                        </div>
                        <div class="ns-meta text-[12px]"><?php echo e(__('attendee.services.matches_note')); ?></div>
                    </a>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="flex flex-wrap gap-10 items-start">
            <div class="flex-[1_1_460px] min-w-0">

                
                <section class="mb-12">
                    <div class="flex items-baseline justify-between gap-4 flex-wrap mb-4">
                        <h2 class="ns-h2 !text-[clamp(22px,2.4vw,28px)]"><?php echo e(__('attendee.profile.agenda')); ?></h2>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($registration->isQuickPass())): ?>
                            <a href="<?php echo e(route('me.agenda')); ?>" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                                <?php echo e(__('attendee.profile.edit_agenda')); ?>

                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $saved; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $sessions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mb-6">
                            <div class="ns-eyebrow !text-[10.5px] mb-3">
                                <?php echo e(__('site.common.day', ['n' => $day])); ?> · <span class="ns-num"><?php echo e(ns_day_date($day)); ?></span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-start gap-4 border-t border-[rgba(5,7,8,0.14)] py-4">
                                    <span class="ns-num font-[family-name:var(--ns-display)] text-[18px] font-semibold w-[58px] shrink-0">
                                        <?php echo e($session->timeLabel()); ?>

                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-[family-name:var(--ns-body)] text-[15.5px] font-semibold leading-[1.35]">
                                            <?php echo e($session->t('title')); ?>

                                        </div>
                                        <div class="ns-meta text-[12.5px] mt-[3px]"><?php echo e($session->hallLabel()); ?></div>
                                    </div>
                                    <form method="POST" action="<?php echo e(route('me.agenda.toggle', $session)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="ns-meta !text-crimson hover:underline bg-transparent border-0 cursor-pointer p-0">
                                            <?php echo e(__('attendee.agenda.remove')); ?>

                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="ns-body !text-[14.5px] text-body-soft max-w-[58ch]"><?php echo e(__('attendee.agenda.empty')); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($registration->isQuickPass())): ?>
                            <a href="<?php echo e(route('me.agenda')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm mt-4">
                                <?php echo e(__('attendee.agenda.add')); ?>

                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </section>

                
                <section class="mb-12">
                    <h2 class="ns-h2 !text-[clamp(22px,2.4vw,28px)] mb-4"><?php echo e(__('attendee.profile.attendance')); ?></h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $checkIns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checkIn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border-t border-[rgba(5,7,8,0.14)] py-[14px] font-[family-name:var(--ns-body)] text-[14.5px]">
                            <span class="ns-num"><?php echo e(__('attendee.profile.attendance_row', [
                                'day' => $checkIn->day,
                                'time' => $checkIn->checked_in_at?->format('H:i'),
                                'gate' => $checkIn->gate ?: 'A',
                            ])); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="ns-body !text-[14.5px] text-body-soft"><?php echo e(__('attendee.profile.attendance_empty')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </section>

                
                <section>
                    <h2 class="ns-h2 !text-[clamp(22px,2.4vw,28px)] mb-4"><?php echo e(__('attendee.profile.messages')); ?></h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border-t border-[rgba(5,7,8,0.14)] py-[14px]">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <span class="font-[family-name:var(--ns-body)] text-[14.5px] font-semibold">
                                    <?php echo e(__('attendee.templates.'.$message->template_key)); ?>

                                </span>
                                <span class="ns-meta text-[12px] ns-num">
                                    <?php echo e($message->created_at?->format('j M · H:i')); ?> · <?php echo e($message->status); ?>

                                </span>
                            </div>
                            <p class="ns-body !text-[13.5px] text-body-soft mt-1 max-w-[62ch]">
                                <?php echo e(Str::limit($message->preview, 130)); ?>

                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="ns-body !text-[14.5px] text-body-soft"><?php echo e(__('attendee.profile.messages_empty')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </section>
            </div>

            
            <aside class="flex-[1_1_280px] max-w-[340px] min-w-0">
                <div class="ns-card !p-6 mb-5">
                    <div class="ns-eyebrow !text-[10.5px] mb-4"><?php echo e(__('attendee.profile.badge')); ?></div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qr): ?>
                        <img src="<?php echo e($qr); ?>" alt="<?php echo e(__('attendee.profile.badge')); ?>" class="w-full max-w-[210px] mx-auto block mb-4">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <dl class="m-0 mb-5">
                        <div class="flex flex-col gap-[3px] py-[9px] border-b border-[rgba(5,7,8,0.1)]">
                            <dt class="ns-eyebrow !text-[9.5px]"><?php echo e(__('attendee.profile.ticket')); ?></dt>
                            <dd class="m-0 font-[family-name:var(--ns-display)] text-[15px] font-bold ns-num"><?php echo e($registration->ticket_ref); ?></dd>
                        </div>
                        <div class="flex flex-col gap-[3px] py-[9px]">
                            <dt class="ns-eyebrow !text-[9.5px]"><?php echo e(__('attendee.profile.days')); ?></dt>
                            <dd class="m-0 font-[family-name:var(--ns-body)] text-sm text-body"><?php echo e($registration->daysLabel()); ?></dd>
                        </div>
                    </dl>

                    <p class="ns-body !text-[13px] text-body-soft mb-4"><?php echo e(__('attendee.profile.badge_note')); ?></p>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->badgeIssued()): ?>
                        <div class="flex flex-col gap-2">
                            <a href="<?php echo e(route('ticket.png', $registration->ticket_id)); ?>" class="ns-btn ns-btn-ghost ns-btn-sm w-full">
                                <?php echo e(__('attendee.profile.download_badge')); ?>

                            </a>
                            <a href="<?php echo e(route('ticket.ics', $registration->ticket_id)); ?>" class="ns-btn ns-btn-ghost ns-btn-sm w-full">
                                <?php echo e(__('attendee.profile.add_calendar')); ?>

                            </a>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="ns-card !p-6">
                    <div class="ns-eyebrow !text-[10.5px] mb-3"><?php echo e(__('attendee.profile.details')); ?></div>
                    <dl class="m-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_filter([
                            __('attendee.profile.name') => $registration->full_name,
                            __('attendee.profile.phone') => $registration->phone_country.' '.$registration->maskedPhone(),
                            __('attendee.profile.email') => $registration->email,
                            __('attendee.profile.city') => $registration->city,
                        ]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex flex-col gap-[2px] py-[8px] border-b border-[rgba(5,7,8,0.08)]">
                                <dt class="ns-eyebrow !text-[9px]"><?php echo e($label); ?></dt>
                                <dd class="m-0 font-[family-name:var(--ns-body)] text-[13.5px] text-body"><?php echo e($value); ?></dd>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </dl>

                    <div class="ns-eyebrow !text-[10.5px] mt-5 mb-2"><?php echo e(__('attendee.profile.consents')); ?></div>
                    <ul class="list-none m-0 p-0 flex flex-col gap-[6px]">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                            'terms' => __('attendee.profile.consent_terms'),
                            'whatsapp' => __('attendee.profile.consent_whatsapp'),
                            'photography' => __('attendee.profile.consent_photography'),
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="font-[family-name:var(--ns-body)] text-[13px] text-body-soft flex gap-2">
                                <span><?php echo e(($registration->consents[$key] ?? false) ? '✓' : '—'); ?></span>
                                <span><?php echo e($label); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            </aside>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/attendee/profile.blade.php ENDPATH**/ ?>