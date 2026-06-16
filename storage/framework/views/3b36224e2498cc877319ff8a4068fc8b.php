<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label' => null, 'name' => null, 'rows' => 4]));

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

foreach (array_filter((['label' => null, 'name' => null, 'rows' => 4]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $hasError = is_string($name) && $name !== '' && $errors->has($name);
?>
<div class="space-y-1">
  <?php if($label): ?>
    <label class="text-sm font-medium" for="<?php echo e($name); ?>"><?php echo e($label); ?></label>
  <?php endif; ?>
  <textarea
    rows="<?php echo e($rows); ?>"
    <?php echo e($attributes->merge([
      'class' => 'w-full rounded-xl border bg-white/70 dark:bg-zinc-900 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] ' .
        ($hasError ? 'border-[rgb(var(--c-danger))] ring-2 ring-[rgb(var(--c-danger)/.15)]' : 'border-[rgb(var(--c-border))]'),
      'name' => $name,
      'id' => $name,
    ])); ?>

  ><?php echo e($slot); ?></textarea>
  <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
    <p class="text-sm text-[rgb(var(--c-danger))]"><?php echo e($message); ?></p>
  <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/textarea.blade.php ENDPATH**/ ?>