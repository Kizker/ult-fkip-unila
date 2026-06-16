<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
  'type' => 'info',
  'title' => null,
  'dismissible' => false,
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
  'type' => 'info',
  'title' => null,
  'dismissible' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $styles = [
    'success' => 'border-green-200 bg-green-50 text-green-900 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-100',
    'danger' => 'border-red-200 bg-red-50 text-red-900 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-100',
    'warning' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-100',
    'info' => 'border-slate-200 bg-slate-50 text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/30 dark:text-zinc-100',
  ];

  $icon = match ($type) {
    'success' => '<path d="M20 6 9 17l-5-5" />',
    'danger' => '<path d="M12 9v4" /><path d="M12 17h.01" /><path d="M10.3 3.1h3.4l8.2 14.2a2 2 0 0 1-1.7 3H3.8a2 2 0 0 1-1.7-3l8.2-14.2Z" />',
    'warning' => '<path d="M12 9v4" /><path d="M12 17h.01" /><path d="M10.3 3.1h3.4l8.2 14.2a2 2 0 0 1-1.7 3H3.8a2 2 0 0 1-1.7-3l8.2-14.2Z" />',
    default => '<path d="M12 16v-4" /><path d="M12 8h.01" /><path d="M12 22a10 10 0 1 0-10-10 10 10 0 0 0 10 10Z" />',
  };

  $closeLabel = trans()->has('app.close') ? __('app.close') : 'Tutup';
?>

<div
  x-data="{ open: true }"
  x-show="open"
  x-transition.opacity.duration.150ms
  role="alert"
  aria-live="polite"
  <?php echo e($attributes->merge(['class' => 'alert rounded-2xl border p-4 mb-4 '.($styles[$type] ?? $styles['info'])])); ?>

>
  <div class="flex items-start gap-3">
    <div class="mt-0.5 shrink-0">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <?php echo $icon; ?>

      </svg>
    </div>

    <div class="min-w-0 flex-1">
      <?php if($title): ?>
        <div class="text-sm font-semibold"><?php echo e($title); ?></div>
      <?php endif; ?>
      <div class="text-sm leading-relaxed">
        <?php echo e($slot); ?>

      </div>
    </div>

    <?php if($dismissible): ?>
      <button
        type="button"
        class="shrink-0 rounded-lg p-1 -m-1 opacity-70 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current/30"
        @click="open = false"
        aria-label="<?php echo e($closeLabel); ?>"
      >
        <span aria-hidden="true">&times;</span>
      </button>
    <?php endif; ?>
  </div>
</div>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/alert.blade.php ENDPATH**/ ?>