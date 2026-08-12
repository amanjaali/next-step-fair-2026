<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['popup' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['popup' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($popup): ?>
    <?php
        /**
         * The offer popup, shown once on the home page.
         *
         * Once per version, not once ever: the dismiss key carries the popup's
         * updated_at, so editing it in the dashboard brings it back for everybody
         * and leaving it alone keeps it closed. That is what makes a popup the
         * team rewrites most days worth having.
         *
         * It decides whether to open in the browser, from localStorage, so it is
         * unaffected by page caching — and it never renders on the server as
         * "open", so nobody sees it flash before the script has decided.
         */
        $key = $popup->dismissKey();
    ?>

    <div x-data="nsOfferPopup('<?php echo e($key); ?>')" x-cloak @keydown.escape.window="close()">
        
        <div x-show="open"
             x-cloak
             style="display: none"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-[100] bg-[rgba(5,7,8,0.72)] flex items-start sm:items-center justify-center p-4 overflow-y-auto"
             @click.self="close()"
             role="dialog" aria-modal="true" aria-labelledby="ns-popup-title">

            <div class="bg-bone w-full max-w-[600px] my-auto border-t-[6px] border-magenta relative rounded-[var(--ns-radius-lg)] overflow-hidden"
                 x-transition:enter="transition ease-out duration-200 delay-75"
                 x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0">

                <button type="button" @click="close()" aria-label="<?php echo e(__('popup.close')); ?>"
                        class="absolute top-3 end-3 w-9 h-9 flex items-center justify-center bg-transparent border-0 cursor-pointer text-slate hover:text-ink text-[22px] leading-none">
                    &times;
                </button>

                <div class="px-[clamp(22px,4vw,38px)] pt-[clamp(26px,4vw,38px)] pb-4">
                    <div class="ns-eyebrow !text-magenta mb-3"><?php echo e(__('popup.kicker')); ?></div>

                    <h2 id="ns-popup-title" class="ns-h2 !text-[clamp(22px,3.4vw,30px)] leading-[1.15] mb-3">
                        <?php echo e($popup->t('title')); ?>

                    </h2>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($popup->t('intro')): ?>
                        <p class="ns-body !text-[14.5px] text-body-soft max-w-[52ch]"><?php echo e($popup->t('intro')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="px-[clamp(22px,4vw,38px)] max-h-[52vh] overflow-y-auto">
                    <div class="grid gap-px bg-[rgba(5,7,8,0.1)] border border-[rgba(5,7,8,0.1)] ns-radius overflow-hidden">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $popup->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-white px-5 py-[18px]">
                                <div class="flex items-start justify-between gap-3 mb-1">
                                    <div class="font-[family-name:var(--ns-display)] text-[17px] font-semibold leading-[1.25] text-ink">
                                        <?php echo e($item->t('title')); ?>

                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->badge): ?>
                                        <span class="ns-eyebrow !text-[9px] !text-magenta whitespace-nowrap shrink-0 mt-1"><?php echo e($item->badge); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->t('body')): ?>
                                    <p class="ns-body !text-[14px] text-body-soft mb-3"><?php echo e($item->t('body')); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->action_url): ?>
                                    <a href="<?php echo e(route('popup.go', $item)); ?>"
                                       class="font-[family-name:var(--ns-body)] text-[13.5px] font-bold text-magenta">
                                        <?php echo e($item->actionLabel()); ?> →
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="px-[clamp(22px,4vw,38px)] py-[clamp(20px,3vw,28px)] flex gap-3 flex-wrap items-center">
                    <a href="<?php echo e(route('opportunities')); ?>" class="ns-btn ns-btn-magenta"><?php echo e(__('popup.see_all')); ?></a>
                    <button type="button" @click="close()" class="ns-btn ns-btn-ghost"><?php echo e($popup->dismissLabel()); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/ns/offer-popup.blade.php ENDPATH**/ ?>