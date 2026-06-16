<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
  'user',
  'size' => 36,
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
  'user',
  'size' => 36,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $url = $user?->profile_photo_url;
  $name = (string) ($user?->name ?? '');
  $initials = collect(explode(' ', trim($name)))
    ->filter()
    ->map(fn($p) => mb_substr($p, 0, 1))
    ->take(2)
    ->join('');
  $fallbackClass = 'inline-flex items-center justify-center text-center leading-none text-xs font-semibold bg-zinc-100 dark:bg-zinc-900 border border-[rgb(var(--c-border))] text-zinc-700 dark:text-zinc-200 select-none shrink-0';
  $fallbackStyle = "width: {$size}px; height: {$size}px; line-height: 1;";
?>

<?php if($url): ?>
  <span class="relative inline-flex shrink-0" style="<?php echo e($fallbackStyle); ?>" data-avatar-root>
    <img
      <?php echo e($attributes->merge([
        'src' => $url,
        'alt' => $name ? "Foto profil {$name}" : 'Foto profil',
        'class' => 'object-cover border border-[rgb(var(--c-border))] bg-white relative z-10',
        'style' => "width: {$size}px; height: {$size}px;",
        'loading' => 'lazy',
        'decoding' => 'async',
        'data-avatar-image' => true,
        'onerror' => "this.hidden=true;this.nextElementSibling.hidden=false;",
      ])); ?>

    />
    <span
      hidden
      aria-hidden="true"
      data-avatar-fallback
      class="<?php echo e($fallbackClass); ?> absolute inset-0 <?php echo e($attributes->get('class')); ?>"
      style="<?php echo e($fallbackStyle); ?>"
    >
      <span style="display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;line-height:1;transform:translateY(-0.02em);"><?php echo e($initials ?: '?'); ?></span>
    </span>
  </span>
<?php else: ?>
  <div
    <?php echo e($attributes->merge([
      'class' => $fallbackClass,
      'style' => $fallbackStyle,
      'aria-hidden' => 'true',
    ])); ?>

  >
    <span style="display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;line-height:1;transform:translateY(-0.02em);"><?php echo e($initials ?: '?'); ?></span>
  </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/user/avatar.blade.php ENDPATH**/ ?>