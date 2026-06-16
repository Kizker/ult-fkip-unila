<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
  'showValidation' => true,
  'showValidationList' => false,
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
  'showValidation' => true,
  'showValidationList' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $titleSuccess = trans()->has('app.success') ? __('app.success') : 'Berhasil';
  $titleInfo = trans()->has('app.info') ? __('app.info') : 'Info';
  $titleWarning = trans()->has('app.warning') ? __('app.warning') : 'Perhatian';
  $titleError = trans()->has('app.error') ? __('app.error') : 'Gagal';

  $validationTitle = trans()->has('app.validation_error_title') ? __('app.validation_error_title') : 'Periksa kembali input';
  $validationHint = trans()->has('app.validation_error_hint')
    ? __('app.validation_error_hint')
    : 'Ada beberapa input yang perlu diperbaiki. Silakan cek kolom yang ditandai.';

  $flashes = [
    ['key' => 'success', 'type' => 'success', 'title' => $titleSuccess],
    ['key' => 'status', 'type' => 'success', 'title' => $titleSuccess],
    ['key' => 'info', 'type' => 'info', 'title' => $titleInfo],
    ['key' => 'warning', 'type' => 'warning', 'title' => $titleWarning],
    ['key' => 'error', 'type' => 'danger', 'title' => $titleError],
  ];
?>

<?php $__currentLoopData = $flashes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php if(session()->has($f['key'])): ?>
    <?php
      $message = session($f['key']);

      if ($f['key'] === 'status' && $message === 'verification-link-sent') {
        $message = trans()->has('app.verification_link_sent')
          ? __('app.verification_link_sent')
          : 'Link verifikasi baru telah dikirim.';
      }
    ?>

    <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['type' => $f['type'],'title' => $f['title'],'dismissible' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($f['type']),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($f['title']),'dismissible' => true]); ?>
      <?php echo e($message); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
  <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($showValidation && $errors->any()): ?>
  <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['type' => 'danger','title' => $validationTitle,'dismissible' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($validationTitle),'dismissible' => true]); ?>
    <div class="space-y-2">
      <div><?php echo e($validationHint); ?></div>

      <?php
        $messages = $errors->getMessages();
        $flatCount = count($errors->all());
        $max = 8;
        $shown = 0;
      ?>

      <?php if($showValidationList || $flatCount <= $max): ?>
        <ul class="list-disc pl-5 space-y-0.5">
          <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $errs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $errs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php $shown++; ?>
              <li>
                <?php if(is_string($field) && $field !== ''): ?>
                  <span class="as-mono text-xs text-muted"><?php echo e($field); ?></span> — <?php echo e($e); ?>

                <?php else: ?>
                  <?php echo e($e); ?>

                <?php endif; ?>
              </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      <?php else: ?>
        <ul class="list-disc pl-5 space-y-0.5">
          <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $errs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $errs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php $shown++; ?>
              <?php if($shown <= $max): ?>
                <li>
                  <span class="as-mono text-xs text-muted"><?php echo e($field); ?></span> — <?php echo e($e); ?>

                </li>
              <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <div class="text-xs text-muted">Menampilkan <?php echo e(min($max, $flatCount)); ?> dari <?php echo e($flatCount); ?> pesan error.</div>
      <?php endif; ?>
    </div>
   <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/flash.blade.php ENDPATH**/ ?>