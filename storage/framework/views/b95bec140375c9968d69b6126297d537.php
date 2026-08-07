<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'registration',
    'heading' => true,
]));

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

foreach (array_filter(([
    'registration',
    'heading' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    /**
     * "I'm attending Next Step Fair 2026" — the picture, the words, the buttons.
     *
     * Used on both confirmation pages and on the share page under /me, so a
     * conference delegate who has no account still gets the whole thing on the
     * screen where they are most likely to use it: the one that just told them
     * they are in.
     */
    $kit = \App\Services\ShareKit::for($registration);
    $caption = $kit->caption();
?>

<div x-data="{ format: 'feed', copied: false }">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heading): ?>
        <div class="ns-eyebrow !text-magenta mb-2"><?php echo e(__('share.kicker')); ?></div>
        <h2 class="ns-h2 !text-[clamp(22px,2.6vw,30px)] mb-2"><?php echo e(__('share.title')); ?></h2>
        <p class="ns-body !text-[14.5px] max-w-[58ch] mb-7"><?php echo e(__('share.lead')); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid gap-8 md:grid-cols-[minmax(240px,320px)_minmax(0,1fr)] items-start">

        
        <div>
            
            <div class="flex gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] mb-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Services\ShareKit::formats(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" @click="format = '<?php echo e($option); ?>'"
                            class="flex-1 px-4 py-3 text-start border-0 cursor-pointer transition-colors"
                            :class="format === '<?php echo e($option); ?>' ? 'bg-ink text-white' : 'bg-white text-ink'">
                        <span class="block font-[family-name:var(--ns-body)] text-[13.5px] font-bold">
                            <?php echo e(__("share.formats.{$option}")); ?>

                        </span>
                        <span class="block font-[family-name:var(--ns-body)] text-[11px] mt-[2px]"
                              :class="format === '<?php echo e($option); ?>' ? 'text-white/60' : 'text-slate'">
                            <?php echo e(__("share.formats.{$option}_note")); ?>

                        </span>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Services\ShareKit::formats(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $slot = \App\Services\ShareKit::nameSlot($option); ?>

                
                <div x-show="format === '<?php echo e($option); ?>'" x-cloak
                     data-share-figure
                     data-card="<?php echo e($kit->image($option)); ?>"
                     data-name="<?php echo e($kit->displayName()); ?>"
                     data-filename="next-step-2026-<?php echo e($option); ?>.png"
                     data-width="<?php echo e($slot['width']); ?>" data-height="<?php echo e($slot['height']); ?>"
                     data-x="<?php echo e($slot['x']); ?>" data-y="<?php echo e($slot['y']); ?>"
                     data-size="<?php echo e($slot['size']); ?>" data-max="<?php echo e($slot['max']); ?>">

                    <img src="<?php echo e($kit->image($option)); ?>" alt="<?php echo e(__('share.title')); ?>"
                         class="w-full h-auto block border border-[rgba(5,7,8,0.14)] mb-3">

                    <canvas class="w-full h-auto block border border-[rgba(5,7,8,0.14)] mb-3 hidden"></canvas>

                    <a href="<?php echo e($kit->image($option)); ?>" download data-share-download
                       class="ns-btn ns-btn-magenta w-full"><?php echo e(__('share.download')); ?></a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div>
            <div class="ns-eyebrow !text-[10.5px] mb-2"><?php echo e(__('share.caption_label')); ?></div>

            <textarea x-ref="caption" rows="10"
                      class="ns-input !font-[family-name:var(--ns-body)] !text-[14px] !leading-[1.6] w-full resize-y mb-3"
            ><?php echo e($caption); ?></textarea>

            <button type="button"
                    @click="navigator.clipboard.writeText($refs.caption.value); copied = true; setTimeout(() => copied = false, 2000)"
                    class="ns-btn ns-btn-ghost ns-btn-sm mb-7">
                <span x-show="! copied"><?php echo e(__('share.copy')); ?></span>
                <span x-show="copied" x-cloak><?php echo e(__('share.copied')); ?></span>
            </button>

            
            <div class="flex flex-wrap gap-3 mb-7">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kit->targets(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $target): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($target['url']); ?>" target="_blank" rel="noopener"
                       class="ns-btn ns-btn-ghost ns-btn-sm flex-1 basis-[190px] whitespace-nowrap"><?php echo e($target['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="border-s-[6px] border-magenta bg-bone-50 px-5 py-4 mb-4">
                <div class="font-[family-name:var(--ns-body)] text-[14px] font-bold mb-1">
                    <?php echo e(__('share.instagram_title')); ?>

                </div>
                <p class="ns-body !text-[13.5px] text-body-soft"><?php echo e(__('share.instagram_note')); ?></p>
            </div>

            <p class="ns-meta text-[12px] leading-[1.6] max-w-[56ch]"><?php echo e(__('share.no_qr_note')); ?></p>
        </div>
    </div>
</div>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/components/ns/share-block.blade.php ENDPATH**/ ?>