@props(['label' => null, 'name' => null, 'help' => null])

@php
  $hasError = is_string($name) && $name !== '' && $errors->has($name);
  $type = strtolower((string) ($attributes->get('type') ?? 'text'));
  $isRequired = $attributes->has('required');
@endphp

@if($type === 'file')
  <x-file-input :label="$label" :name="$name" :help="$help" {{ $attributes->except('type') }} />
@else
  <div class="space-y-1">
    @if($label)
      <label class="text-sm font-medium" for="{{ $name }}">
        {{ $label }}
        @if($isRequired)
          <span class="text-[rgb(var(--c-danger))]" aria-hidden="true"> *</span>
        @endif
      </label>
    @endif

    <input
      {{ $attributes->merge([
        'class' => 'h-11 w-full rounded-xl border bg-white/70 px-4 text-sm leading-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] dark:bg-zinc-900 ' .
          ($hasError ? 'border-[rgb(var(--c-danger))] ring-2 ring-[rgb(var(--c-danger)/.15)]' : 'border-[rgb(var(--c-border))]'),
        'name' => $name,
        'id' => $name,
      ]) }}
    />

    @if($help)
      <p class="text-xs text-muted">{{ $help }}</p>
    @endif

    @error($name)
      <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
    @enderror
  </div>
@endif
