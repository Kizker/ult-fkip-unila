<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

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

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
  $map = [
    'DIAJUKAN' => ['primary','Diajukan'],
    'PERLU_PERBAIKAN' => ['warning','Perlu Perbaikan'],
    'DIVERIFIKASI_UNIT' => ['primary','Diverifikasi Unit'],
    'MENUNGGU_TTD_UNIT' => ['warning','Menunggu TTD Unit'],
    'REVIEW_ULT' => ['primary','Review ULT'],
    'MENUNGGU_TTD_FAKULTAS' => ['warning','Menunggu TTD Fakultas'],
    'NOMOR_DOKUMEN_TERBIT' => ['primary','Nomor Terbit'],
    'DIPROSES' => ['primary','Diproses'],
    'SELESAI' => ['success','Selesai'],
    'DITOLAK' => ['danger','Ditolak'],

    // Document module (layanan dokumen)
    'GATE_VERIFIED' => ['primary','Gate Verified'],
    'NOMOR_SURAT_FILLED' => ['primary','Nomor Surat Diisi'],
    'IN_SIGNING' => ['warning','Penandatanganan'],
    'REJECTED_IN_SIGNING' => ['danger','Ditolak TTD'],
    'READY_FOR_FINAL' => ['warning','Penandatanganan'],
    // Backward compatibility for old records before status unification.
    'COMPLETED' => ['success','Selesai'],
    'DITOLAK_ADMIN' => ['danger','Ditolak Admin'],
  ];
  $v = $map[$status] ?? ['default',$status];
?>
<?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $v[0],'attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($v[0]),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?><?php echo e($v[1]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/status-badge.blade.php ENDPATH**/ ?>