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
    <div class="ns-wrap max-w-[720px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta"><?php echo e(__('register.kicker')); ?></span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3"><?php echo e(__('register.step4.heading')); ?></h1>
        <p class="ns-body max-w-[58ch] mb-8"><?php echo e(__('register.step4.lead')); ?></p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($testingCode): ?>
            <div class="border-2 border-dashed border-[#F2A93B] bg-[#FFF8EC] p-5 mb-6" role="status">
                <div class="ns-eyebrow !text-[10.5px] !text-[#8A6100] mb-2">Test mode · no WhatsApp message was sent</div>
                <div class="flex items-baseline gap-3 flex-wrap">
                    <span class="ns-num font-[family-name:var(--ns-display)] text-[30px] font-bold tracking-[0.18em]"><?php echo e($testingCode); ?></span>
                    <span class="font-[family-name:var(--ns-body)] text-[13.5px] text-body-soft">
                        Enter this code to continue. It disappears once WhatsApp is connected.
                    </span>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="ns-card" x-data="nsOtp(<?php echo e($cooldown); ?>)">
            <div class="ns-eyebrow !text-[11px] mb-4"><?php echo e(__('register.step4.verification')); ?></div>

            <form method="POST" action="<?php echo e(route('register.fair.verify.submit', $registration->ticket_id)); ?>"
                  class="flex items-end gap-4 flex-wrap">
                <?php echo csrf_field(); ?>
                <label>
                    <span class="ns-label"><?php echo e(__('register.step4.code')); ?></span>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                           maxlength="6" placeholder="000000" @input="onInput($event)"
                           class="ns-input !w-[190px] ns-num !text-[22px] !tracking-[0.24em] !bg-white"
                           <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>>
                </label>
                <button type="submit" class="ns-btn ns-btn-magenta"><?php echo e(__('register.submit')); ?></button>
            </form>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="ns-error"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="flex items-center gap-4 flex-wrap mt-6 pt-5 border-t border-[rgba(5,7,8,0.12)]">
                <form method="POST" action="<?php echo e(route('register.fair.resend', $registration->ticket_id)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="ns-btn ns-btn-ghost ns-btn-sm !text-magenta !border-[rgba(182,70,152,0.5)]"
                            :disabled="cooldown > 0" :class="cooldown > 0 ? 'opacity-50' : ''">
                        <span x-show="cooldown <= 0"><?php echo e(__('register.step4.resend')); ?></span>
                        <span x-show="cooldown > 0" x-cloak x-text="'<?php echo e(__('register.step4.resend_in', ['seconds' => ':s'])); ?>'.replace(':s', cooldown)"></span>
                    </button>
                </form>
                <span class="ns-meta"><?php echo e(__('register.step4.sent_to', ['phone' => $registration->phone_country.' '.$registration->maskedPhone()])); ?></span>
            </div>
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
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/register/verify.blade.php ENDPATH**/ ?>