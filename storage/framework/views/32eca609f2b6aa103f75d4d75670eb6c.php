<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
  'label' => null,
  'name' => null,
  'help' => null,
  'buttonLabel' => 'Pilih file',
  'emptyLabel' => 'Belum ada file dipilih',
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
  'label' => null,
  'name' => null,
  'help' => null,
  'buttonLabel' => 'Pilih file',
  'emptyLabel' => 'Belum ada file dipilih',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $hasError = is_string($name) && $name !== '' && $errors->has($name);
  $inputId = (string) ($attributes->get('id') ?: $name);
  $inputClass = trim((string) ($attributes->get('class') ?? ''));
  $wrapperClass = 'ui-file-field' . ($hasError ? ' is-error' : '');
?>

<div class="space-y-1">
  <?php if($label): ?>
    <label class="text-sm font-medium" for="<?php echo e($inputId); ?>"><?php echo e($label); ?></label>
  <?php endif; ?>

  <div class="<?php echo e($wrapperClass); ?>" data-file-field data-file-empty-label="<?php echo e($emptyLabel); ?>">
    <input
      <?php echo e($attributes->except('class', 'type')->merge([
        'type' => 'file',
        'name' => $name,
        'id' => $inputId,
        'class' => trim('sr-only ' . $inputClass),
        'data-file-input' => '',
      ])); ?>

    />
    <button type="button" class="ui-file-field__button" data-file-trigger><?php echo e($buttonLabel); ?></button>
    <div class="ui-file-field__name" data-file-name aria-live="polite"><?php echo e($emptyLabel); ?></div>
  </div>

  <?php if($help): ?>
    <p class="text-xs text-muted"><?php echo e($help); ?></p>
  <?php endif; ?>

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
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/file-input.blade.php ENDPATH**/ ?>