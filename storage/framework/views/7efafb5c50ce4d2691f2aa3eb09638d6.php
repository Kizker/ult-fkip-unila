<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
  'href' => null,
  'variant' => 'primary', // primary|secondary|ghost|danger
  'type' => 'button'
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
  'href' => null,
  'variant' => 'primary', // primary|secondary|ghost|danger
  'type' => 'button'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $base = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed';
  $variants = [
    'primary' => 'border border-transparent bg-[rgb(var(--c-primary))] text-white hover:bg-white hover:text-[rgb(var(--c-primary))] hover:border-[rgb(var(--c-primary))]',
    'secondary' => 'border border-[rgb(var(--c-border))] bg-white/70 text-[rgb(var(--c-fg))] hover:bg-[rgb(var(--c-primary))] hover:text-white hover:border-[rgb(var(--c-primary))] dark:bg-zinc-800 dark:text-[rgb(var(--c-fg))] dark:hover:bg-[rgb(var(--c-primary))] dark:hover:text-white dark:hover:border-[rgb(var(--c-primary))]',
    'ghost' => 'border border-[rgb(var(--c-border))] bg-white/70 text-[rgb(var(--c-fg))] hover:bg-[rgb(var(--c-primary))] hover:text-white hover:border-[rgb(var(--c-primary))] dark:bg-zinc-900/70 dark:text-[rgb(var(--c-fg))] dark:hover:bg-[rgb(var(--c-primary))] dark:hover:text-white dark:hover:border-[rgb(var(--c-primary))]',
    'danger' => 'bg-[rgb(var(--c-danger))] text-white hover:opacity-95',
  ];
  $cls = $base.' '.($variants[$variant] ?? $variants['primary']);
?>

<?php if($href): ?>
  <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => $cls])); ?>>
    <?php echo e($slot); ?>

  </a>
<?php else: ?>
  <button type="<?php echo e($type); ?>" <?php echo e($attributes->merge(['class' => $cls])); ?>>
    <?php echo e($slot); ?>

  </button>
<?php endif; ?>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/button.blade.php ENDPATH**/ ?>