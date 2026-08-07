
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e(config("nextstep.locales.$locale.dir", 'ltr')); ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo e($registration->full_name); ?> — <?php echo e($registration->ticket_ref); ?></title>
    <style>
        @page { margin: 0; size: A6 portrait; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #ffffff;
            background: <?php echo e($accent); ?>;
        }
        .badge { width: 100%; padding: 9mm 8mm 7mm; }
        .brand { font-size: 13pt; font-weight: bold; line-height: 1.1; letter-spacing: -0.2pt; }
        .edition { font-size: 6.5pt; letter-spacing: 1.6pt; text-transform: uppercase; opacity: 0.78; margin-top: 2mm; }
        .chip {
            background: #ffffff; color: <?php echo e($accent); ?>; font-size: 7pt; font-weight: bold;
            letter-spacing: 1.2pt; padding: 1.6mm 2.4mm; white-space: nowrap;
        }
        .name { font-size: <?php echo e(mb_strlen($registration->full_name) > 26 ? '15pt' : '19pt'); ?>; font-weight: bold; line-height: 1.05; margin-top: 7mm; }
        .institution { font-size: 10pt; font-weight: bold; line-height: 1.3; margin-top: 2mm; }
        .position { font-size: 8.5pt; opacity: 0.85; margin-top: 1mm; }
        .meta { font-size: 8pt; opacity: 0.9; margin-top: 2mm; }
        .qr-wrap { background: #ffffff; padding: 3mm; margin-top: 6mm; width: 46mm; }
        .qr-wrap img { display: block; width: 40mm; height: 40mm; }
        .ticket { font-size: 7pt; letter-spacing: 1pt; opacity: 0.85; margin-top: 3mm; }
        .foot { font-size: 6.5pt; opacity: 0.75; margin-top: 2mm; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
    </style>
</head>
<body>
<div class="badge">
    <table>
        <tr>
            <td>
                <div class="brand">
                    <?php echo e($isConference ? 'Next Step' : 'Next Step Fair'); ?><br>
                    <?php echo e($isConference ? 'Conference '.config('nextstep.event.year') : config('nextstep.event.year')); ?>

                </div>
                <div class="edition">
                    <?php echo e($isConference ? __('site.common.day', ['n' => 1], $locale).' · '.ns_day_date(1) : __('site.common.edition_4', [], $locale)); ?>

                </div>
            </td>
            <td style="text-align: end; width: 30mm;">
                <span class="chip"><?php echo e($typeChip); ?></span>
            </td>
        </tr>
    </table>

    <div class="name"><?php echo e($registration->full_name); ?></div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isConference): ?>
        
        <div class="institution"><?php echo e($registration->organization); ?></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->position): ?>
            <div class="position"><?php echo e($registration->position); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <div class="meta"><?php echo e($registration->city); ?> · <?php echo e($daysLabel); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="qr-wrap">
        <img src="<?php echo e($qr); ?>" alt="QR">
    </div>

    <div class="ticket">TICKET <?php echo e($registration->ticket_ref); ?></div>
    <div class="foot">
        <?php echo e(ns_event_dates()); ?> · <?php echo e(config('nextstep.event.venue.name')); ?>, <?php echo e(config('nextstep.event.venue.city')); ?>

    </div>
</div>
</body>
</html>
<?php /**PATH /Users/amanjali/Downloads/next-step-fair-2026/resources/views/badges/badge.blade.php ENDPATH**/ ?>