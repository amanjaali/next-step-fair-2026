<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo e(config('nextstep.event.name')); ?> — <?php echo e(__('site.pages.agenda.title')); ?></title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #050708; font-size: 10pt; }
        h1 { font-size: 22pt; margin: 0 0 4pt; letter-spacing: -0.5pt; }
        .meta { color: #4A4B4D; font-size: 9pt; margin-bottom: 16pt; }
        h2 { font-size: 13pt; margin: 18pt 0 6pt; border-bottom: 1pt solid #050708; padding-bottom: 3pt; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 6pt 4pt; border-bottom: 0.5pt solid #dcd8d4; }
        .time { width: 60pt; font-weight: bold; }
        .type { width: 70pt; color: #4A4B4D; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.5pt; }
        .title { font-weight: bold; }
        .who { color: #4A4B4D; font-size: 9pt; }
    </style>
</head>
<body>
    <h1><?php echo e(config('nextstep.event.name')); ?></h1>
    <div class="meta">
        <?php echo e(__('site.pages.agenda.title')); ?> · <?php echo e(ns_event_dates()); ?> ·
        <?php echo e(config('nextstep.event.venue.name')); ?>, <?php echo e(config('nextstep.event.venue.city')); ?>

    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sessionsByDay; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $sessions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <h2><?php echo e(__('site.common.day', ['n' => $day])); ?> — <?php echo e(ns_day_date($day)); ?></h2>
        <table>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="time"><?php echo e($session->timeLabel()); ?></td>
                    <td class="type"><?php echo e($session->typeLabel()); ?></td>
                    <td>
                        <div class="title"><?php echo e($session->t('title')); ?></div>
                        <div class="who"><?php echo e($session->hallLabel()); ?> · <?php echo e($session->languages); ?> · <?php echo e($session->t('who')); ?></div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</body>
</html>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/agenda/pdf.blade.php ENDPATH**/ ?>