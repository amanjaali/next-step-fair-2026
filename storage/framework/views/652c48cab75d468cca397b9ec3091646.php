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

    
    <?php if (isset($component)) { $__componentOriginal3eb1f7c048162dbd6ed55676eb392c46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3eb1f7c048162dbd6ed55676eb392c46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.offer-popup','data' => ['popup' => $popup]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.offer-popup'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['popup' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($popup)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3eb1f7c048162dbd6ed55676eb392c46)): ?>
<?php $attributes = $__attributesOriginal3eb1f7c048162dbd6ed55676eb392c46; ?>
<?php unset($__attributesOriginal3eb1f7c048162dbd6ed55676eb392c46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3eb1f7c048162dbd6ed55676eb392c46)): ?>
<?php $component = $__componentOriginal3eb1f7c048162dbd6ed55676eb392c46; ?>
<?php unset($__componentOriginal3eb1f7c048162dbd6ed55676eb392c46); ?>
<?php endif; ?>

    
    <section class="bg-ink text-white relative overflow-hidden ns-track-rule">
        <div class="absolute inset-0 bg-ink-800 flex items-center justify-center">
            <span class="ns-eyebrow !text-white/25"><?php echo e(__('site.home.hero_media')); ?></span>
        </div>
        <div class="absolute inset-0"
             style="background:linear-gradient(90deg, rgba(5,7,8,0.95) 0%, rgba(5,7,8,0.84) 55%, rgba(5,7,8,0.55) 100%)"></div>

        <div class="ns-wrap relative pt-[clamp(64px,9vw,112px)] pb-[clamp(56px,8vw,96px)]">
            <div class="flex items-center gap-[14px] mb-7 flex-wrap">
                <span class="ns-eyebrow !text-magenta"><?php echo e(__('site.common.edition_4')); ?></span>
                <span class="w-9 h-px bg-white/30"></span>
                <span class="ns-eyebrow !text-white/70"><?php echo e(__('site.common.location')); ?></span>
            </div>

            <h1 class="ns-display max-w-[14ch] mb-7"><?php echo e(config('nextstep.event.name')); ?></h1>

            <p class="ns-lead !text-white/80 max-w-[56ch] mb-10">
                <?php echo e(__('site.home.hero_lead', ['universities' => 32, 'sessions' => 26])); ?>

            </p>

            <div class="flex gap-9 items-start mb-11 flex-wrap">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    ['label' => __('site.common.dates'), 'value' => ns_event_dates(), 'isolate' => false],
                    ['label' => __('site.common.venue'), 'value' => config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'), 'isolate' => false],
                    ['label' => __('site.common.hours'), 'value' => config('nextstep.event.opening_hours'), 'isolate' => true],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <div class="ns-eyebrow !text-white/50 mb-2"><?php echo e($fact['label']); ?></div>
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'font-[family-name:var(--ns-display)] text-[23px] font-semibold',
                            'ns-num' => $fact['isolate'],
                        ]); ?>"><?php echo e($fact['value']); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee): ?>
                <div class="flex gap-[14px] flex-wrap">
                    <a href="<?php echo e(route('me')); ?>" class="ns-btn ns-btn-lg ns-btn-magenta"><?php echo e(__('site.home.signed_in.my_badge')); ?></a>
                    <a href="<?php echo e(route('agenda')); ?>" class="ns-btn ns-btn-lg ns-btn-ghost !text-white !border-white/40 hover:!bg-white/10">
                        <?php echo e(__('site.home.signed_in.browse_programme')); ?>

                    </a>
                </div>
            <?php else: ?>
                <div class="flex gap-[14px] flex-wrap">
                    <a href="<?php echo e(route('register.fair')); ?>" class="ns-btn ns-btn-lg ns-btn-magenta"><?php echo e(__('site.cta.register_fair')); ?></a>
                    <a href="<?php echo e(route('register.conference')); ?>" class="ns-btn ns-btn-lg ns-btn-cobalt"><?php echo e(__('site.cta.conference_rsvp')); ?></a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee): ?>
        
        <section class="bg-bone-200 border-b border-[rgba(5,7,8,0.14)]">
            <div class="ns-wrap py-[clamp(32px,4.5vw,56px)]">
                <div class="flex items-end justify-between gap-6 flex-wrap mb-7">
                    <div>
                        <div class="ns-eyebrow !text-magenta mb-2"><?php echo e(__('site.home.signed_in.kicker')); ?></div>
                        <h2 class="ns-h2 !text-[clamp(24px,3vw,34px)]">
                            <?php echo e(__('site.home.signed_in.welcome', ['name' => $attendee->firstName()])); ?>

                        </h2>
                    </div>
                    <div class="ns-meta text-[13px] ns-num">
                        <?php echo e(__('site.home.signed_in.ticket', ['ticket' => $attendee->ticket_ref])); ?>

                    </div>
                </div>

                <?php
                    // Only offer what this person can actually use. A parent has no
                    // scholarship to apply for, and a card that leads to a locked
                    // page is worse than no card.
                    $isStudent = $attendee->type === \App\Models\Registration::TYPE_STUDENT;

                    $next = array_values(array_filter([
                        [
                            'title' => __('site.home.signed_in.cards.agenda'),
                            'note' => $savedCount
                                ? trans_choice('site.home.signed_in.cards.agenda_saved', $savedCount, ['count' => $savedCount])
                                : __('site.home.signed_in.cards.agenda_empty'),
                            'href' => $isStudent ? route('me.agenda') : route('agenda'),
                            'accent' => 'border-magenta',
                        ],
                        $attendee->canApplyForScholarship() ? [
                            'title' => __('site.home.signed_in.cards.scholarship'),
                            'note' => __('site.home.signed_in.cards.scholarship_note'),
                            'href' => route('scholarship.apply'),
                            'accent' => 'border-magenta',
                        ] : null,
                        [
                            'title' => __('site.home.signed_in.cards.opportunities'),
                            'note' => __('site.home.signed_in.cards.opportunities_note'),
                            'href' => route('opportunities'),
                            'accent' => 'border-teal',
                        ],
                        [
                            'title' => __('site.home.signed_in.cards.zankoline'),
                            'note' => __('site.home.signed_in.cards.zankoline_note'),
                            'href' => route('scholarships'),
                            'accent' => 'border-cobalt',
                        ],
                        [
                            'title' => __('site.home.signed_in.cards.universities'),
                            'note' => __('site.home.signed_in.cards.universities_note'),
                            'href' => route('universities'),
                            'accent' => 'border-cobalt',
                        ],
                        [
                            'title' => __('site.home.signed_in.cards.seminars'),
                            // The real count, not a number written into the copy that
                            // goes stale the first time a session is added.
                            'note' => __('site.home.signed_in.cards.seminars_note', ['count' => $sessionCount]),
                            'href' => route('seminars'),
                            'accent' => 'border-teal',
                        ],
                    ]));
                ?>

                <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2 lg:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $next; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($card['href']); ?>" class="bg-white px-6 py-[22px] block no-underline hover:bg-bone-50 border-s-4 <?php echo e($card['accent']); ?>">
                            <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold text-ink mb-1"><?php echo e($card['title']); ?></div>
                            <div class="ns-meta text-[12.5px]"><?php echo e($card['note']); ?></div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </section>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunities->isNotEmpty()): ?>
            
            <section class="ns-wrap pt-[clamp(44px,6vw,80px)] pb-[clamp(44px,6vw,80px)]">
                <div class="flex items-end justify-between gap-6 flex-wrap mb-7">
                    <div>
                        <div class="ns-eyebrow !text-magenta mb-2"><?php echo e(__('opportunities.kicker')); ?></div>
                        <h2 class="ns-h2 !text-[clamp(24px,3vw,36px)]"><?php echo e(__('opportunities.home_title')); ?></h2>
                        <p class="ns-body !text-[15px] text-body-soft max-w-[56ch] mt-2"><?php echo e(__('opportunities.home_lead')); ?></p>
                    </div>
                    <a href="<?php echo e(route('opportunities')); ?>" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                        <?php echo e(__('opportunities.see_all')); ?>

                    </a>
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $opportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal30ede29f76a871128dc01de573759a22 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal30ede29f76a871128dc01de573759a22 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.opportunity-card','data' => ['opportunity' => $opportunity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.opportunity-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['opportunity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunity)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal30ede29f76a871128dc01de573759a22)): ?>
<?php $attributes = $__attributesOriginal30ede29f76a871128dc01de573759a22; ?>
<?php unset($__attributesOriginal30ede29f76a871128dc01de573759a22); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal30ede29f76a871128dc01de573759a22)): ?>
<?php $component = $__componentOriginal30ede29f76a871128dc01de573759a22; ?>
<?php unset($__componentOriginal30ede29f76a871128dc01de573759a22); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <section class="bg-ink-900 text-white border-t border-white/10">
        <div class="ns-wrap ns-counters">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $counters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $counter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <div class="ns-stat ns-num text-[clamp(30px,4vw,44px)] mb-2"><?php echo e($counter['value']); ?></div>
                    <div class="ns-eyebrow !text-white/55"><?php echo e($counter['label']); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    
    <section class="ns-wrap pt-[clamp(56px,7vw,96px)]">
        <div class="flex items-baseline gap-5 mb-9">
            <span class="ns-eyebrow"><?php echo e(__('site.home.two_tracks')); ?></span>
            <span class="ns-rule"></span>
        </div>

        <div class="ns-hairgrid md:grid-cols-2">
            <div class="p-[clamp(28px,4vw,52px)] flex flex-col gap-[22px] border-t-[6px] border-magenta">
                <span class="ns-eyebrow !text-magenta"><?php echo e(__('site.home.fair_kicker')); ?></span>
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,40px)]"><?php echo e(__('site.home.fair_title')); ?></h2>
                <p class="ns-body max-w-[46ch]"><?php echo e(__('site.home.fair_body')); ?></p>
                <ul class="list-none m-0 p-0 flex flex-col gap-[10px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        __('site.home.universities_title', ['count' => 32]),
                        __('site.footer.links.seminars'),
                        __('site.pages.scholarships.title'),
                        __('site.common.free_entry'),
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="font-[family-name:var(--ns-body)] text-[14.5px] flex gap-3 items-start">
                            <span class="ns-bar bg-magenta mt-2"></span><span><?php echo e($point); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
                <div class="mt-auto pt-[14px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee): ?>
                        <a href="<?php echo e(route('fair')); ?>" class="ns-btn ns-btn-magenta"><?php echo e(__('site.cta.explore_expo')); ?></a>
                    <?php else: ?>
                        <a href="<?php echo e(route('register.fair')); ?>" class="ns-btn ns-btn-magenta"><?php echo e(__('site.cta.register_fair')); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="p-[clamp(28px,4vw,52px)] flex flex-col gap-[22px] border-t-[6px] border-cobalt">
                <span class="ns-eyebrow !text-cobalt"><?php echo e(__('site.home.conf_kicker')); ?></span>
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,40px)]"><?php echo e(__('site.home.conf_title')); ?></h2>
                <p class="ns-body max-w-[46ch]"><?php echo e(__('site.home.conf_body')); ?></p>
                <ul class="list-none m-0 p-0 flex flex-col gap-[10px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        __('site.pages.conference.programme'),
                        __('site.pages.conference.themes'),
                        'KU · AR · EN',
                        __('rsvp.step2.letter'),
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="font-[family-name:var(--ns-body)] text-[14.5px] flex gap-3 items-start">
                            <span class="ns-bar bg-cobalt mt-2"></span><span><?php echo e($point); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
                <div class="mt-auto pt-[14px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee): ?>
                        <a href="<?php echo e(route('conference')); ?>" class="ns-btn ns-btn-cobalt"><?php echo e(__('site.cta.explore_conference')); ?></a>
                    <?php else: ?>
                        <a href="<?php echo e(route('register.conference')); ?>" class="ns-btn ns-btn-cobalt"><?php echo e(__('site.cta.conference_rsvp')); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    
    <section class="ns-wrap ns-section">
        <div class="grid gap-16 lg:grid-cols-2 items-start">
            <div class="ns-rise">
                <span class="ns-eyebrow"><?php echo e(__('site.home.about_kicker')); ?></span>
                <h2 class="ns-h2 !text-[clamp(30px,4vw,48px)] mt-[22px] mb-[26px]"><?php echo e(__('site.home.about_title')); ?></h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['about_p1', 'about_p2', 'about_p3']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paragraph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p class="ns-body max-w-[62ch] mb-[18px]"><?php echo e(__('site.home.'.$paragraph)); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('about')); ?>" class="ns-link"><?php echo e(__('site.cta.about_next_step')); ?></a>
            </div>
            <?php if (isset($component)) { $__componentOriginalfe5b2835aa6a3ec2adfa439570add664 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe5b2835aa6a3ec2adfa439570add664 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.frame','data' => ['label' => __('site.home.about_photo'),'center' => true,'height' => '470px','class' => 'ns-rise']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.frame'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.home.about_photo')),'center' => true,'height' => '470px','class' => 'ns-rise']); ?>
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
        </div>
    </section>

    
    <section class="bg-white border-y border-[rgba(5,7,8,0.12)]">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-11">
                <h2 class="ns-h2"><?php echo e(__('site.home.why_attend')); ?></h2>
                <span class="ns-meta text-[13.5px]"><?php echo e(__('site.common.free_entry')); ?></span>
            </div>

            <div class="ns-hairgrid sm:grid-cols-2 xl:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $featureCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-[34px] pt-[38px] pb-[42px] ns-rise">
                        
                        <svg viewBox="0 0 24 24" width="34" height="34" class="block mb-6" aria-hidden="true">
                            <path d="<?php echo e($card->icon_path); ?>" fill="#050708"></path>
                            <path d="<?php echo e($card->icon_accent); ?>" fill="#B64698"></path>
                        </svg>
                        <h3 class="font-[family-name:var(--ns-body)] text-xl font-bold mb-3 tracking-[-0.01em]"><?php echo e($card->t('title')); ?></h3>
                        <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.65] text-slate"><?php echo e($card->t('body')); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="ns-wrap ns-section">
        <div class="flex items-baseline gap-5 mb-[14px]">
            <span class="ns-eyebrow"><?php echo e(__('site.home.sdg_kicker')); ?></span>
            <span class="ns-rule"></span>
        </div>

        <div class="grid gap-14 lg:grid-cols-[1.1fr_1fr] items-end mb-9">
            <h2 class="ns-h2"><?php echo e(__('site.home.sdg_title')); ?></h2>
            <p class="ns-body"><?php echo e(__('site.home.sdg_body')); ?></p>
        </div>

        <div class="grid gap-3 grid-cols-2 lg:grid-cols-5 mb-7">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sdgGoals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-white px-5 pt-[22px] pb-[26px] min-h-[190px] flex flex-col"
                     style="background:<?php echo e($goal->color); ?>">
                    <div class="font-[family-name:var(--ns-display)] text-[11px] font-bold tracking-[0.16em] mb-[10px] ns-num">
                        SDG <?php echo e($goal->number); ?>

                    </div>
                    <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold leading-[1.1] mb-auto">
                        <?php echo e($goal->t('title')); ?>

                    </div>
                    <div class="font-[family-name:var(--ns-body)] text-[12.5px] font-medium leading-[1.45] text-white/90 pt-4">
                        <?php echo e($goal->t('metric')); ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="flex items-center gap-[18px] flex-wrap border-t border-[rgba(5,7,8,0.14)] pt-[26px]">
            <a href="<?php echo e(route('sdg')); ?>" class="ns-btn ns-btn-ink"><?php echo e(__('site.cta.our_sdg')); ?></a>
            <a href="<?php echo e(config('nextstep.links.act4sdgs')); ?>" target="_blank" rel="noopener"
               class="font-[family-name:var(--ns-body)] text-[13.5px] font-bold text-cobalt border-b-2 border-cobalt pb-[2px]">
                <?php echo e(__('site.cta.verify_act4sdgs')); ?>

            </a>
        </div>
    </section>

    
    <section class="bg-ink text-white">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-11">
                <h2 class="ns-h2"><?php echo e(__('site.home.featured_speakers')); ?></h2>
                <a href="<?php echo e(route('speakers')); ?>" class="ns-btn ns-btn-ghost-light ns-btn-sm">
                    <?php echo e(__('site.cta.all_speakers', ['count' => $speakers->count()])); ?>

                </a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $speakers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $speaker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginalbbe272859a900381507dbc0ba164cbc6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbbe272859a900381507dbc0ba164cbc6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.speaker-card','data' => ['speaker' => $speaker,'dark' => true,'showSessions' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.speaker-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['speaker' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($speaker),'dark' => true,'showSessions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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

    
    <section class="ns-wrap ns-section">
        <div class="flex items-baseline justify-between gap-6 flex-wrap mb-8">
            <h2 class="ns-h2"><?php echo e(__('site.home.agenda_preview')); ?></h2>
            <a href="<?php echo e(route('agenda')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm"><?php echo e(__('site.cta.full_agenda')); ?></a>
        </div>

        <div class="ns-tabs mb-7">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('nextstep.event.days'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $number => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('home', ['day' => $number])); ?>#agenda"
                   class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-tab', 'is-on' => $agendaDay === $number]); ?>"><?php echo e(__('site.common.day', ['n' => $number])); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div id="agenda" class="ns-hairgrid lg:grid-cols-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $agendaPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-[26px] px-7 flex gap-6">
                    <div class="ns-num font-[family-name:var(--ns-display)] text-[19px] font-semibold min-w-[96px]">
                        <?php echo e($session->timeLabel()); ?>

                    </div>
                    <div>
                        <div class="flex items-center gap-[9px] mb-2 flex-wrap">
                            <span class="ns-typechip <?php echo e($session->chipClass()); ?>"><?php echo e($session->typeLabel()); ?></span>
                            <span class="ns-meta text-xs"><?php echo e($session->hallLabel()); ?></span>
                        </div>
                        <div class="font-[family-name:var(--ns-body)] text-base font-bold leading-[1.35] mb-[6px]">
                            <?php echo e($session->t('title')); ?>

                        </div>
                        <div class="ns-meta"><?php echo e($session->t('who')); ?></div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    
    <section class="bg-white border-t border-[rgba(5,7,8,0.12)]">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-9">
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">
                    <?php echo e(__('site.home.universities_title', ['count' => $universities->count()])); ?>

                </h2>
                <a href="<?php echo e(route('universities')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm"><?php echo e(__('site.cta.who_is_exhibiting')); ?></a>
            </div>

            <div class="ns-hairgrid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $universities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $university): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="h-[104px] flex items-center justify-center p-[14px] text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($university->logo_path): ?>
                            <img src="<?php echo e(asset('storage/'.$university->logo_path)); ?>" alt="<?php echo e($university->t('name')); ?>"
                                 class="max-h-[64px] w-auto" loading="lazy">
                        <?php else: ?>
                            <span class="font-[family-name:var(--ns-body)] text-[11.5px] font-medium leading-[1.35] text-muted">
                                <?php echo e($university->t('name')); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="ns-wrap ns-section-tight">
        <div class="flex items-baseline justify-between gap-6 flex-wrap mb-10">
            <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]"><?php echo e(__('site.home.partners_title')); ?></h2>
            <a href="<?php echo e(route('sponsors')); ?>" class="ns-btn ns-btn-ghost ns-btn-sm">
                <?php echo e(__('site.cta.all_partners', ['count' => collect($sponsorTiers)->sum(fn ($t) => $t['items']->count())])); ?>

            </a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sponsorTiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($tier['items']->isEmpty()) continue; ?>
            <div class="border-t border-[rgba(5,7,8,0.14)] pt-[26px] pb-[34px] grid gap-10 lg:grid-cols-[220px_1fr] items-start">
                <div>
                    <div class="ns-eyebrow !text-ink !tracking-[0.18em] mb-[7px]"><?php echo e($tier['tier']); ?></div>
                    <div class="ns-meta leading-[1.5]"><?php echo e($tier['note']); ?></div>
                </div>
                <div class="flex gap-[14px] flex-wrap">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tier['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-bone-200 flex items-center justify-center p-[10px] text-center"
                             style="height:<?php echo e($tier['height']); ?>;width:<?php echo e($tier['width']); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->logo_path): ?>
                                <img src="<?php echo e(asset('assets/'.$item->logo_path)); ?>" alt="<?php echo e($item->t('name')); ?>"
                                     class="max-h-full max-w-full object-contain" loading="lazy">
                            <?php else: ?>
                                <span class="font-[family-name:var(--ns-body)] text-[11.5px] font-medium text-muted leading-[1.3]">
                                    <?php echo e($item->t('name')); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="border-t border-[rgba(5,7,8,0.14)] pt-[30px] flex gap-[14px] flex-wrap">
            <a href="<?php echo e(route('sponsors')); ?>" class="ns-btn ns-btn-cobalt"><?php echo e(__('site.cta.become_sponsor')); ?></a>
            <a href="<?php echo e(route('exhibit')); ?>" class="ns-btn ns-btn-ghost"><?php echo e(__('site.cta.exhibit')); ?></a>
        </div>
    </section>

    
    <section class="bg-white border-t border-[rgba(5,7,8,0.12)]">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-9">
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]"><?php echo e(__('site.home.news_title')); ?></h2>
                <a href="<?php echo e(route('news')); ?>" class="ns-link"><?php echo e(__('site.cta.all_news')); ?></a>
            </div>

            <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $news; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6fb2d21cf5d8cf2d95f6fc703e72c0e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.post-card','data' => ['post' => $post,'showReadTime' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.post-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['post' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post),'showReadTime' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
        </div>
    </section>

    
    <section class="ns-wrap ns-section-tight">
        <div class="flex items-baseline justify-between gap-6 flex-wrap mb-7">
            <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]"><?php echo e(__('site.home.media_title')); ?></h2>
            <a href="<?php echo e(route('media')); ?>" class="ns-link"><?php echo e(__('site.cta.photos_videos')); ?></a>
        </div>

        <div class="grid gap-3 grid-cols-2 lg:grid-cols-4 auto-rows-[180px]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $mediaStrip->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('media.photos')); ?>"
                   class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-frame', 'row-span-2' => $index === 0]); ?>">
                    <span><?php echo e($item->category); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featuredVideo): ?>
                <a href="<?php echo e(route('media.videos')); ?>"
                   class="bg-ink row-span-2 flex flex-col justify-between p-5 text-white hover:text-white">
                    <span class="ns-eyebrow !text-white/50"><?php echo e(__('site.pages.media.videos')); ?></span>
                    <div>
                        <div class="w-[34px] h-[34px] border-2 border-white flex items-center justify-center mb-3">
                            <span class="text-xs">▶</span>
                        </div>
                        <span class="font-[family-name:var(--ns-display)] text-lg font-semibold"><?php echo e($featuredVideo->t('alt')); ?></span>
                    </div>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    
    <section class="bg-white border-t border-[rgba(5,7,8,0.12)]">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-9">
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]"><?php echo e(__('site.home.archive_title')); ?></h2>
                <span class="ns-meta text-[13.5px] max-w-[60ch]"><?php echo e(__('site.home.archive_note')); ?></span>
            </div>

            <div class="grid gap-7 md:grid-cols-2 xl:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $editions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $edition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('archive.show', $edition->year)); ?>"
                       class="text-ink hover:text-ink border border-[rgba(5,7,8,0.14)] block group">
                        <?php if (isset($component)) { $__componentOriginalfe5b2835aa6a3ec2adfa439570add664 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe5b2835aa6a3ec2adfa439570add664 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ns.frame','data' => ['label' => $edition->year.' cover','ratio' => '16/9','center' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ns.frame'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($edition->year.' cover'),'ratio' => '16/9','center' => true]); ?>
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
                        <div class="p-[26px]">
                            <div class="ns-stat ns-num text-[34px] mb-[14px] group-hover:text-magenta"><?php echo e($edition->year); ?></div>
                            <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[6px]">
                                <?php echo e(collect($edition->stats)->take(2)->map(fn ($s) => $s['v'].' '.($s['k'][app()->getLocale()] ?? $s['k']['en'] ?? ''))->implode(' · ')); ?>

                            </div>
                            <div class="ns-meta text-[13.5px]"><?php echo e($edition->t('headline')); ?></div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="ns-wrap ns-section-tight">
        <div class="grid gap-14 lg:grid-cols-[1fr_1.15fr] items-stretch">
            <div>
                <span class="ns-eyebrow"><?php echo e(__('site.home.venue_kicker')); ?></span>
                <h2 class="ns-h2 mt-5 mb-6"><?php echo e(__('site.home.venue_title')); ?></h2>

                <div class="flex flex-col gap-[18px] mb-[26px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('nextstep.event.venue.address'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <div class="ns-eyebrow !text-[10.5px] mb-[5px]"><?php echo e(config("nextstep.locales.$code.label")); ?></div>
                            <div dir="<?php echo e(config("nextstep.locales.$code.dir")); ?>"
                                 class="font-[family-name:<?php echo e($code === 'en' ? 'var(--ns-body)' : "'Noto Sans Arabic'"); ?>] text-[15.5px] leading-[1.6]">
                                <?php echo e($address); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="border-t border-[rgba(5,7,8,0.14)] pt-5 flex flex-col gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = __('site.home.venue_notes'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="font-[family-name:var(--ns-body)] text-sm text-body-soft"><?php echo e($note); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <a href="<?php echo e(config('nextstep.event.venue.map_url')); ?>" target="_blank" rel="noopener"
               class="ns-frame ns-frame-center min-h-[400px] relative">
                <span><?php echo e(__('site.home.venue_map')); ?></span>
                <span class="absolute left-1/2 top-1/2 w-[14px] h-[14px] bg-magenta"></span>
            </a>
        </div>
    </section>

    <?php $__env->startPush('schema'); ?>
        <?php
            // Event + Organization JSON-LD. Built in PHP because Blade parses
            // an @-prefixed string inside a directive as a directive.
            $eventSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => config('nextstep.event.name'),
                'startDate' => config('nextstep.event.start_date'),
                'endDate' => config('nextstep.event.end_date'),
                'eventStatus' => 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'inLanguage' => ['en', 'ckb', 'ar'],
                'image' => [asset('assets/brand/nextstep-transparent-sm.png')],
                'description' => __('site.seo.default_description'),
                'location' => [
                    '@type' => 'Place',
                    'name' => config('nextstep.event.venue.name'),
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => config('nextstep.event.venue.address.en'),
                        'addressLocality' => config('nextstep.event.venue.city'),
                        'addressCountry' => 'IQ',
                    ],
                    'geo' => [
                        '@type' => 'GeoCoordinates',
                        'latitude' => config('nextstep.event.venue.latitude'),
                        'longitude' => config('nextstep.event.venue.longitude'),
                    ],
                ],
                'organizer' => [
                    '@type' => 'Organization',
                    'name' => config('nextstep.event.organisation'),
                    'url' => config('app.url'),
                    'logo' => asset('assets/brand/nextstep-transparent-sm.png'),
                    'email' => config('nextstep.contact.general'),
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'IQD',
                    'availability' => 'https://schema.org/InStock',
                    'url' => route('register.fair'),
                    'validFrom' => now()->toIso8601String(),
                ],
            ];
        ?>
        <script type="application/ld+json"><?php echo json_encode($eventSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <?php $__env->stopPush(); ?>

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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/home.blade.php ENDPATH**/ ?>