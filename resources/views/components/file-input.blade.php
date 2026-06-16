@props([
  'label' => null,
  'name' => null,
  'help' => null,
  'buttonLabel' => 'Pilih file',
  'emptyLabel' => 'Belum ada file dipilih',
])

@php
  $hasError = is_string($name) && $name !== '' && $errors->has($name);
  $inputId = (string) ($attributes->get('id') ?: $name);
  $inputClass = trim((string) ($attributes->get('class') ?? ''));
  $wrapperClass = 'ui-file-field' . ($hasError ? ' is-error' : '');
@endphp

<div class="space-y-1">
  @if($label)
    <label class="text-sm font-medium" for="{{ $inputId }}">{{ $label }}</label>
  @endif

  <div class="{{ $wrapperClass }}" data-file-field data-file-empty-label="{{ $emptyLabel }}">
    <input
      {{ $attributes->except('class', 'type')->merge([
        'type' => 'file',
        'name' => $name,
        'id' => $inputId,
        'class' => trim('sr-only ' . $inputClass),
        'data-file-input' => '',
      ]) }}
    />
    <button type="button" class="ui-file-field__button" data-file-trigger>{{ $buttonLabel }}</button>
    <div class="ui-file-field__name" data-file-name aria-live="polite">{{ $emptyLabel }}</div>
  </div>

  @if($help)
    <p class="text-xs text-muted">{{ $help }}</p>
  @endif

  @error($name)
    <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
  @enderror
</div>
