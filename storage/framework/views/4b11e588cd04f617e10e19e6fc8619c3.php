<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
  'name',
  'value' => '',
  'label' => null,
  'localeHint' => null,
  'help' => null,
  'placeholder' => '',
  'height' => 'min-h-[220px]'
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
  'name',
  'value' => '',
  'label' => null,
  'localeHint' => null,
  'help' => null,
  'placeholder' => '',
  'height' => 'min-h-[220px]'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
  $id = $attributes->get('id') ?? ('tiptap_' . \Illuminate\Support\Str::uuid());
  $uploadUrl = \Illuminate\Support\Facades\Route::has('admin.utils.upload_media')
    ? route('admin.utils.upload_media')
    : '';
  $hasError = $errors->has($name);
?>

<div class="space-y-2">
  <?php if($label): ?>
    <label class="text-sm font-medium" for="<?php echo e($id); ?>_input">
      <?php echo e($label); ?>

      <?php if($localeHint): ?>
        <span class="ml-2 text-xs text-muted"><?php echo e($localeHint); ?></span>
      <?php endif; ?>
    </label>
  <?php endif; ?>

  <div
    data-tiptap
    data-tiptap-input-id="<?php echo e($id); ?>_input"
    data-tiptap-upload-url="<?php echo e($uploadUrl); ?>"
    class="rounded-2xl border bg-card shadow-sm <?php echo e($hasError ? 'border-[rgb(var(--c-danger))] ring-2 ring-[rgb(var(--c-danger)/.15)]' : 'border-[rgb(var(--c-border))]'); ?>"
  >
    <div class="tiptap-toolbar flex flex-wrap items-center gap-1 border-b border-[rgb(var(--c-border))] p-2">
      <button type="button" class="tiptap-btn" data-tiptap-action="bold" aria-label="Bold"><strong>B</strong></button>
      <button type="button" class="tiptap-btn" data-tiptap-action="italic" aria-label="Italic"><em>I</em></button>
      <button type="button" class="tiptap-btn" data-tiptap-action="strike" aria-label="Strike">S</button>
      <span class="tiptap-toolbar__sep" aria-hidden="true"></span>
      <button type="button" class="tiptap-btn" data-tiptap-action="heading" data-tiptap-level="2" aria-label="Heading 2">H2</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="heading" data-tiptap-level="3" aria-label="Heading 3">H3</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="bulletList" aria-label="Bullet list">• List</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="orderedList" aria-label="Ordered list">1. List</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="blockquote" aria-label="Blockquote">❝</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="hr" aria-label="Horizontal rule">HR</button>
      <span class="tiptap-toolbar__sep" aria-hidden="true"></span>
      <button type="button" class="tiptap-btn" data-tiptap-action="align" data-tiptap-align="left" aria-label="Align left">⟸</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="align" data-tiptap-align="center" aria-label="Align center">≡</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="align" data-tiptap-align="right" aria-label="Align right">⟹</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="align" data-tiptap-align="justify" aria-label="Justify">☰</button>
      <span class="tiptap-toolbar__sep" aria-hidden="true"></span>
      <button type="button" class="tiptap-btn" data-tiptap-action="indent" aria-label="Indent">⇥</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="outdent" aria-label="Outdent">⇤</button>
      <span class="tiptap-toolbar__sep" aria-hidden="true"></span>
      <button type="button" class="tiptap-btn" data-tiptap-action="image" aria-label="Upload image">🖼</button>
      <span class="tiptap-toolbar__sep" aria-hidden="true"></span>
      <button type="button" class="tiptap-btn" data-tiptap-action="link" aria-label="Add link">🔗</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="unlink" aria-label="Remove link">⛓</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="video" aria-label="Embed video URL">▶</button>
      <span class="tiptap-toolbar__sep" aria-hidden="true"></span>
      <button type="button" class="tiptap-btn" data-tiptap-action="undo" aria-label="Undo">↶</button>
      <button type="button" class="tiptap-btn" data-tiptap-action="redo" aria-label="Redo">↷</button>

      <div class="ml-auto text-xs text-muted">
        Chars: <span data-tiptap-count>0</span>
      </div>
    </div>

    <div class="p-3">
      <div
        data-tiptap-editor
        class="tiptap-editor <?php echo e($height); ?> w-full rounded-xl border border-[rgb(var(--c-border))] bg-bg p-3 text-sm leading-relaxed outline-none"
      ></div>

      <textarea id="<?php echo e($id); ?>_input" name="<?php echo e($name); ?>" class="hidden"><?php echo e($value); ?></textarea>
      <input type="file" class="hidden" data-tiptap-upload-input accept="image/png,image/jpeg,image/jpg,image/webp,image/gif">
    </div>
  </div>

  <?php if($help): ?>
    <div class="text-xs text-muted"><?php echo e($help); ?></div>
  <?php endif; ?>

  <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
    <div class="text-sm text-[rgb(var(--c-danger))]"><?php echo e($message); ?></div>
  <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/tiptap-editor.blade.php ENDPATH**/ ?>