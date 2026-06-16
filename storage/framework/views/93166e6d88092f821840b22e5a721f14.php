<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label' => null, 'name' => null, 'help' => null]));

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

foreach (array_filter((['label' => null, 'name' => null, 'help' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $hasError = is_string($name) && $name !== '' && $errors->has($name);
  $type = strtolower((string) ($attributes->get('type') ?? 'text'));
  $isRequired = $attributes->has('required');
?>

<?php if($type === 'file'): ?>
  <?php if (isset($component)) { $__componentOriginal504fe35b8ae10dd8f883a20f1431f8b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal504fe35b8ae10dd8f883a20f1431f8b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.file-input','data' => ['label' => $label,'name' => $name,'help' => $help,'attributes' => $attributes->except('type')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('file-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'help' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($help),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->except('type'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal504fe35b8ae10dd8f883a20f1431f8b8)): ?>
<?php $attributes = $__attributesOriginal504fe35b8ae10dd8f883a20f1431f8b8; ?>
<?php unset($__attributesOriginal504fe35b8ae10dd8f883a20f1431f8b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal504fe35b8ae10dd8f883a20f1431f8b8)): ?>
<?php $component = $__componentOriginal504fe35b8ae10dd8f883a20f1431f8b8; ?>
<?php unset($__componentOriginal504fe35b8ae10dd8f883a20f1431f8b8); ?>
<?php endif; ?>
<?php else: ?>
  <div class="space-y-1">
    <?php if($label): ?>
      <label class="text-sm font-medium" for="<?php echo e($name); ?>">
        <?php echo e($label); ?>

        <?php if($isRequired): ?>
          <span class="text-[rgb(var(--c-danger))]" aria-hidden="true"> *</span>
        <?php endif; ?>
      </label>
    <?php endif; ?>

    <input
      <?php echo e($attributes->merge([
        'class' => 'h-11 w-full rounded-xl border bg-white/70 px-4 text-sm leading-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] dark:bg-zinc-900 ' .
          ($hasError ? 'border-[rgb(var(--c-danger))] ring-2 ring-[rgb(var(--c-danger)/.15)]' : 'border-[rgb(var(--c-border))]'),
        'name' => $name,
        'id' => $name,
      ])); ?>

    />

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
<?php endif; ?>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/input.blade.php ENDPATH**/ ?>