@props(['label' => null, 'name' => null])
@php
  $hasError = is_string($name) && $name !== '' && $errors->has($name);
  $isRequired = $attributes->has('required');
@endphp
<div class="space-y-1">
  @if($label)
    <label class="text-sm font-medium" for="{{ $name }}">
      {{ $label }}
      @if($isRequired)
        <span class="text-[rgb(var(--c-danger))]" aria-hidden="true"> *</span>
      @endif
    </label>
  @endif
  <select
    {{ $attributes->merge([
      'class' => 'h-11 w-full rounded-xl border bg-white/70 px-4 text-sm leading-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] dark:bg-zinc-900 ' .
        ($hasError ? 'border-[rgb(var(--c-danger))] ring-2 ring-[rgb(var(--c-danger)/.15)]' : 'border-[rgb(var(--c-border))]'),
      'name' => $name,
      'id' => $name,
      'data-scrollable-select' => '1',
    ]) }}
  >
    {{ $slot }}
  </select>
  @error($name)
    <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
  @enderror
</div>
