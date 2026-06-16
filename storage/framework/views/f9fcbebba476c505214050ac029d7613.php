<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => 'default']));

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

foreach (array_filter((['variant' => 'default']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
  $map = [
    'default' => 'bg-slate-100 text-slate-900 dark:bg-zinc-800 dark:text-zinc-100',
    'success' => 'bg-green-100 text-green-900 dark:bg-green-900/30 dark:text-green-100',
    'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-100',
    'danger' => 'bg-red-100 text-red-900 dark:bg-red-900/30 dark:text-red-100',
    'primary' => 'bg-violet-100 text-violet-900 dark:bg-violet-900/30 dark:text-violet-100',
  ];
?>
<span <?php echo e($attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium '.($map[$variant] ?? $map['default'])])); ?>>
  <?php echo e($slot); ?>

</span>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/badge.blade.php ENDPATH**/ ?>