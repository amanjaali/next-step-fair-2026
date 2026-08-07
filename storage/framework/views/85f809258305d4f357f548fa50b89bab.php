<?php
    // Nav order follows the design: Home, The Expo, The Conference, Agenda,
    // Speakers, Universities, Sponsors, News, SDG. News sits before SDG.
    $navItems = [
        ['key' => 'home', 'label' => __('site.nav.home'), 'route' => 'home'],
        ['key' => 'fair', 'label' => __('site.nav.expo'), 'route' => 'fair'],
        ['key' => 'conference', 'label' => __('site.nav.conference'), 'route' => 'conference'],
        ['key' => 'scholarship', 'label' => __('site.nav.scholarship'), 'route' => 'scholarship.home'],
        ['key' => 'agenda', 'label' => __('site.nav.agenda'), 'route' => 'agenda'],
        ['key' => 'speakers', 'label' => __('site.nav.speakers'), 'route' => 'speakers'],
        ['key' => 'universities', 'label' => __('site.nav.universities'), 'route' => 'universities'],
        ['key' => 'sponsors', 'label' => __('site.nav.sponsors'), 'route' => 'sponsors'],
        ['key' => 'news', 'label' => __('site.nav.news'), 'route' => 'news'],
        ['key' => 'sdg', 'label' => __('site.nav.sdg'), 'route' => 'sdg'],
    ];
    // Opportunities is only worth a menu slot for somebody who can open them.
    // For everyone else it would be a link to a locked page, and an eleventh item
    // is exactly what breaks the header onto two rows.
    if (auth('attendee')->check()) {
        array_splice($navItems, 4, 0, [[
            'key' => 'opportunities',
            'label' => __('site.nav.opportunities'),
            'route' => 'opportunities',
        ]]);
    }

    $current = $navKey ?? null;
?>

<header class="sticky top-0 z-50 bg-bone border-b border-[rgba(5,7,8,0.14)]" x-data="nsNav">
    <div class="ns-wrap flex items-center justify-between gap-[clamp(12px,2vw,32px)] py-3 min-h-[76px] flex-wrap">

        
        <div class="flex items-center gap-4 shrink-0">
            <a href="<?php echo e(route('home')); ?>" class="block shrink-0">
                <img src="<?php echo e(asset('assets/brand/nextstep-transparent-sm.png')); ?>"
                     alt="<?php echo e(__('site.header.logo_alt')); ?>" class="h-[46px] w-auto block">
            </a>
            
            <span class="w-px h-11 bg-[rgba(5,7,8,0.2)] shrink-0 hidden sm:block xl:hidden 2xl:block"></span>
            <div class="hidden sm:flex xl:hidden 2xl:flex items-center gap-[11px] shrink-0">
                <img src="<?php echo e(asset('assets/brand/mohe.png')); ?>"
                     alt="<?php echo e(__('site.header.partnership_kicker')); ?> — <?php echo e(__('site.header.mohe')); ?>"
                     title="<?php echo e(__('site.header.mohe')); ?>" class="h-10 w-auto block">
            </div>
        </div>

        
        
        <nav class="hidden xl:flex items-center gap-[clamp(9px,0.92vw,18px)] flex-1 min-w-0 flex-wrap gap-y-2"
             aria-label="<?php echo e(__('site.nav.menu')); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($item['route'])); ?>"
                   class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                       'font-[family-name:var(--ns-body)] text-[13.5px] font-medium py-1 whitespace-nowrap border-b-2',
                       'text-ink border-magenta' => $current === $item['key'],
                       'text-slate border-transparent hover:text-ink' => $current !== $item['key'],
                   ]); ?>"
                   <?php if($current === $item['key']): ?> aria-current="page" <?php endif; ?>><?php echo e($item['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </nav>

        <div class="flex items-center gap-3 shrink-0">
            <?php echo $__env->make('partials.language-switcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard('attendee')->check()): ?>
                <a href="<?php echo e(route('me')); ?>"
                   class="ns-btn ns-btn-ghost !py-[13px] !px-4 !text-[13.5px] whitespace-nowrap hidden md:inline-flex">
                    <?php echo e(__('attendee.nav.my_next_step')); ?>

                </a>
            <?php else: ?>
                <a href="<?php echo e(route('register.fair')); ?>"
                   class="ns-btn ns-btn-magenta !py-[14px] !px-5 !text-[13.5px] whitespace-nowrap hidden sm:inline-flex">
                    <?php echo e(__('site.cta.register_fair')); ?>

                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button type="button" @click="toggle()" class="xl:hidden p-3 -me-2 bg-transparent border-0 cursor-pointer"
                    :aria-expanded="open.toString()" aria-controls="ns-mobile-nav" aria-label="<?php echo e(__('site.nav.menu')); ?>">
                <span class="block w-[22px] h-0.5 bg-ink mb-[5px]"></span>
                <span class="block w-[22px] h-0.5 bg-ink mb-[5px]"></span>
                <span class="block w-[22px] h-0.5 bg-ink"></span>
            </button>
        </div>
    </div>

    
    <div id="ns-mobile-nav" x-show="open" x-collapse x-cloak class="xl:hidden border-t border-[rgba(5,7,8,0.14)] bg-bone">
        <div class="ns-wrap py-6 flex flex-col gap-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($item['route'])); ?>"
                   class="font-[family-name:var(--ns-body)] text-base font-semibold py-3 border-b border-[rgba(5,7,8,0.08)] <?php echo e($current === $item['key'] ? 'text-magenta' : 'text-ink'); ?>">
                    <?php echo e($item['label']); ?>

                </a>
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