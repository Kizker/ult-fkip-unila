@props(['label' => null, 'name' => null, 'rows' => 4])

@php
  $hasError = is_string($name) && $name !== '' && $errors->has($name);
@endphp
<div class="space-y-1">
  @if($label)
    <label class="text-sm font-medium" for="{{ $name }}">{{ $label }}</label>
  @endif
  <textarea
    rows="{{ $rows }}"
    {{ $attributes->merge([
      'class' => 'w-full rounded-xl border bg-white/70 dark:bg-zinc-900 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] ' .
        ($hasError ? 'border-[rgb(var(--c-danger))] ring-2 ring-[rgb(var(--c-danger)/.15)]' : 'border-[rgb(var(--c-border))]'),
      'name' => $name,
      'id' => $name,
    ]) }}
  >{{ $slot }}</textarea>
  @error($name)
    <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
  @enderror
</div>
