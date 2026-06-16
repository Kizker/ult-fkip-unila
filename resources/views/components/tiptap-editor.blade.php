@props([
  'name',
  'value' => '',
  'label' => null,
  'localeHint' => null,
  'help' => null,
  'placeholder' => '',
  'height' => 'min-h-[220px]'
])

@php
  $id = $attributes->get('id') ?? ('tiptap_' . \Illuminate\Support\Str::uuid());
  $uploadUrl = \Illuminate\Support\Facades\Route::has('admin.utils.upload_media')
    ? route('admin.utils.upload_media')
    : '';
  $hasError = $errors->has($name);
@endphp

<div class="space-y-2">
  @if($label)
    <label class="text-sm font-medium" for="{{ $id }}_input">
      {{ $label }}
      @if($localeHint)
        <span class="ml-2 text-xs text-muted">{{ $localeHint }}</span>
      @endif
    </label>
  @endif

  <div
    data-tiptap
    data-tiptap-input-id="{{ $id }}_input"
    data-tiptap-upload-url="{{ $uploadUrl }}"
    class="rounded-2xl border bg-card shadow-sm {{ $hasError ? 'border-[rgb(var(--c-danger))] ring-2 ring-[rgb(var(--c-danger)/.15)]' : 'border-[rgb(var(--c-border))]' }}"
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
        class="tiptap-editor {{ $height }} w-full rounded-xl border border-[rgb(var(--c-border))] bg-bg p-3 text-sm leading-relaxed outline-none"
      ></div>

      <textarea id="{{ $id }}_input" name="{{ $name }}" class="hidden">{{ $value }}</textarea>
      <input type="file" class="hidden" data-tiptap-upload-input accept="image/png,image/jpeg,image/jpg,image/webp,image/gif">
    </div>
  </div>

  @if($help)
    <div class="text-xs text-muted">{{ $help }}</div>
  @endif

  @error($name)
    <div class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</div>
  @enderror
</div>
