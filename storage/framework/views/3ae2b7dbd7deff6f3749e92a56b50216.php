
<div class="flex border border-[rgba(5,7,8,0.2)] shrink-0" role="group" aria-label="<?php echo e(__('site.nav.language')); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = config('nextstep.locales'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $conf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <form method="GET" action="<?php echo e(route('locale.switch', $code)); ?>">
            <input type="hidden" name="to" value="<?php echo e(request()->path()); ?>">
            <input type="hidden" name="query" value="<?php echo e(request()->getQueryString()); ?>">
            <button type="submit"
                    class="font-[family-name:'Space_Grotesk'] text-[11px] font-semibold tracking-[0.14em] py-[7px] px-[9px] border-0 cursor-pointer <?php echo e($code === $locale ? 'bg-ink text-white' : 'bg-transparent text-ink'); ?>"
                    lang="<?php echo e($conf['html_lang']); ?>"
                    aria-label="<?php echo e($conf['label']); ?>"
                    <?php if($code === $locale): ?> aria-current="true" <?php endif; ?>><?php echo e($conf['code']); ?></button>
        </form>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/partials/language-switcher.blade.php ENDPATH**/ ?>