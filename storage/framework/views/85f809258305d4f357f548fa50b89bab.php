<?php
    /**
     * Eight things at the top level, not eleven.
     *
     * The four pages that are all "what happens at the fair" — the two halves of
     * the event and the two ways of reading its programme — sit inside one group.
     * That is a shorter menu to scan, and the width it gives back is what lets the
     * ministry partnership mark stay in the header at every size rather than
     * disappearing above 1280.
     */
    $navItems = [
        ['key' => 'home', 'label' => __('site.nav.home'), 'route' => 'home'],
        [
            'key' => 'event',
            'label' => __('site.nav.the_next_step'),
            'children' => [
                ['key' => 'fair', 'label' => __('site.nav.expo'), 'route' => 'fair'],
                ['key' => 'conference', 'label' => __('site.nav.conference'), 'route' => 'conference'],
                ['key' => 'agenda', 'label' => __('site.nav.agenda'), 'route' => 'agenda'],
                ['key' => 'speakers', 'label' => __('site.nav.speakers'), 'route' => 'speakers'],
            ],
        ],
        ['key' => 'scholarship', 'label' => __('site.nav.scholarship'), 'route' => 'scholarship.home'],
        ['key' => 'opportunities', 'label' => __('site.nav.opportunities'), 'route' => 'opportunities'],
        ['key' => 'universities', 'label' => __('site.nav.universities'), 'route' => 'universities'],
        ['key' => 'sponsors', 'label' => __('site.nav.sponsors'), 'route' => 'sponsors'],
        ['key' => 'news', 'label' => __('site.nav.news'), 'route' => 'news'],
        ['key' => 'sdg', 'label' => __('site.nav.sdg'), 'route' => 'sdg'],
    ];

    $current = $navKey ?? null;

    // A group reads as the current section when the page open is one of its own.
    $isCurrentGroup = fn (array $item) => isset($item['children'])
        && in_array($current, array_column($item['children'], 'key'), true);
?>

<header class="sticky top-0 z-50 bg-bone border-b border-[rgba(5,7,8,0.1)]" x-data="nsNav">
    <div class="ns-wrap flex items-center justify-between gap-[clamp(10px,1.1vw,20px)] py-3 min-h-[76px] flex-wrap">

        
        <div class="flex items-center gap-4 shrink-0">
            <a href="<?php echo e(route('home')); ?>" class="block shrink-0">
                <img src="<?php echo e(asset('assets/brand/nextstep-transparent-sm.png')); ?>"
                     alt="<?php echo e(__('site.header.logo_alt')); ?>" class="h-[46px] w-auto block">
            </a>
            <span class="w-px h-11 bg-[rgba(5,7,8,0.16)] shrink-0 hidden sm:block"></span>
            <div class="hidden sm:flex items-center gap-[11px] shrink-0">
                <img src="<?php echo e(asset('assets/brand/mohe.png')); ?>"
                     alt="<?php echo e(__('site.header.partnership_kicker')); ?> — <?php echo e(__('site.header.mohe')); ?>"
                     title="<?php echo e(__('site.header.mohe')); ?>" class="h-10 w-auto block">
            </div>
        </div>

        
        <nav class="hidden xl:flex items-center gap-[clamp(0px,0.15vw,6px)] flex-1 min-w-0 flex-wrap gap-y-2"
             aria-label="<?php echo e(__('site.nav.menu')); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($item['children'])): ?>
                    <?php $groupOpen = $isCurrentGroup($item); ?>
                    
                    <div class="relative" x-data="nsNavGroup"
                         @mouseenter="enter()" @mouseleave="leave()"
                         @click.outside="close()" @keydown.escape.window="close()">
                        <button type="button" @click="press()"
                                :aria-expanded="open.toString()"
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'ns-navlink inline-flex items-center gap-1.5 bg-transparent border-0 cursor-pointer',
                                    'is-current' => $groupOpen,
                                ]); ?>">
                            <?php echo e($item['label']); ?>

                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"
                                 class="transition-transform duration-200 shrink-0" :class="open && 'rotate-180'">
                                <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.6"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        
                        <div x-show="open" x-cloak style="display: none"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute top-full start-0 pt-2 min-w-[228px] z-10">
                            <div class="bg-white ns-radius ns-shadow border border-[rgba(5,7,8,0.08)] p-2 flex flex-col">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(route($child['route'])); ?>"
                                       class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                           'ns-navsub',
                                           'is-current' => $current === $child['key'],
                                       ]); ?>"
                                       <?php if($current === $child['key']): ?> aria-current="page" <?php endif; ?>><?php echo e($child['label']); ?></a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo e(route($item['route'])); ?>"
                       class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ns-navlink', 'is-current' => $current === $item['key']]); ?>"
                       <?php if($current === $item['key']): ?> aria-current="page" <?php endif; ?>><?php echo e($item['label']); ?></a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </nav>

        <div class="flex items-center gap-3 shrink-0">
            <?php echo $__env->make('partials.language-switcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard('attendee')->check()): ?>
                <a href="<?php echo e(route('me')); ?>"
                   class="ns-btn ns-btn-sm ns-btn-ghost !text-[13.5px] whitespace-nowrap hidden md:inline-flex">
                    <?php echo e(__('attendee.nav.my_next_step')); ?>

                </a>
            <?php else: ?>
                <a href="<?php echo e(route('register.fair')); ?>"
                   class="ns-btn ns-btn-sm ns-btn-magenta !text-[13.5px] !px-[18px] whitespace-nowrap hidden sm:inline-flex">
                    <?php echo e(__('site.cta.register_fair')); ?>

                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button type="button" @click="toggle()" class="xl:hidden p-3 -me-2 bg-transparent border-0 cursor-pointer"
                    :aria-expanded="open.toString()" aria-controls="ns-mobile-nav" aria-label="<?php echo e(__('site.nav.menu')); ?>">
                <span class="block w-[22px] h-0.5 bg-ink mb-[5px] rounded-full"></span>
                <span class="block w-[22px] h-0.5 bg-ink mb-[5px] rounded-full"></span>
                <span class="block w-[22px] h-0.5 bg-ink rounded-full"></span>
            </button>
        </div>
    </div>

    
    <div id="ns-mobile-nav" x-show="open" x-collapse x-cloak class="xl:hidden border-t border-[rgba(5,7,8,0.1)] bg-bone">
        <div class="ns-wrap py-6 flex flex-col gap-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($item['children'])): ?>
                    <div class="font-[family-name:var(--ns-body)] text-[12px] font-bold uppercase tracking-[0.12em] text-muted pt-5 pb-2">
                        <?php echo e($item['label']); ?>

                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route($child['route'])); ?>"
                           class="font-[family-name:var(--ns-body)] text-base font-bold py-3 ps-4 border-b border-[rgba(5,7,8,0.07)] <?php echo e($current === $child['key'] ? 'text-magenta' : 'text-ink'); ?>">
                            <?php echo e($child['label']); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo e(route($item['route'])); ?>"
                       class="font-[family-name:var(--ns-body)] text-base font-bold py-3 border-b border-[rgba(5,7,8,0.07)] <?php echo e($current === $item['key'] ? 'text-magenta' : 'text-ink'); ?>">
                        <?php echo e($item['label']); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="flex flex-col gap-3 pt-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard('attendee')->check()): ?>
                    <a href="<?php echo e(route('me')); ?>" class="ns-btn ns-btn-magenta w-full"><?php echo e(__('attendee.nav.my_next_step')); ?></a>
                    <a href="<?php echo e(route('me.agenda')); ?>" class="ns-btn ns-btn-ghost w-full"><?php echo e(__('attendee.agenda.title')); ?></a>
                <?php else: ?>
                    <a href="<?php echo e(route('register.fair')); ?>" class="ns-btn ns-btn-magenta w-full"><?php echo e(__('site.cta.register_fair')); ?></a>
                    <a href="<?php echo e(route('register.conference')); ?>" class="ns-btn ns-btn-cobalt w-full"><?php echo e(__('site.cta.conference_rsvp')); ?></a>
                    <a href="<?php echo e(route('attendee.signin')); ?>" class="ns-btn ns-btn-ghost w-full"><?php echo e(__('attendee.nav.sign_in')); ?></a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</header>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/partials/header.blade.php ENDPATH**/ ?>