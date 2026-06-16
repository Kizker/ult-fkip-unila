@props([
  'user',
  'size' => 36,
])

@php
  $url = $user?->profile_photo_url;
  $name = (string) ($user?->name ?? '');
  $initials = collect(explode(' ', trim($name)))
    ->filter()
    ->map(fn($p) => mb_substr($p, 0, 1))
    ->take(2)
    ->join('');
  $fallbackClass = 'inline-flex items-center justify-center text-center leading-none text-xs font-semibold bg-zinc-100 dark:bg-zinc-900 border border-[rgb(var(--c-border))] text-zinc-700 dark:text-zinc-200 select-none shrink-0';
  $fallbackStyle = "width: {$size}px; height: {$size}px; line-height: 1;";
@endphp

@if($url)
  <span class="relative inline-flex shrink-0" style="{{ $fallbackStyle }}" data-avatar-root>
    <img
      {{ $attributes->merge([
        'src' => $url,
        'alt' => $name ? "Foto profil {$name}" : 'Foto profil',
        'class' => 'object-cover border border-[rgb(var(--c-border))] bg-white relative z-10',
        'style' => "width: {$size}px; height: {$size}px;",
        'loading' => 'lazy',
        'decoding' => 'async',
        'data-avatar-image' => true,
        'onerror' => "this.hidden=true;this.nextElementSibling.hidden=false;",
      ]) }}
    />
    <span
      hidden
      aria-hidden="true"
      data-avatar-fallback
      class="{{ $fallbackClass }} absolute inset-0 {{ $attributes->get('class') }}"
      style="{{ $fallbackStyle }}"
    >
      <span style="display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;line-height:1;transform:translateY(-0.02em);">{{ $initials ?: '?' }}</span>
    </span>
  </span>
@else
  <div
    {{ $attributes->merge([
      'class' => $fallbackClass,
      'style' => $fallbackStyle,
      'aria-hidden' => 'true',
    ]) }}
  >
    <span style="display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;line-height:1;transform:translateY(-0.02em);">{{ $initials ?: '?' }}</span>
  </div>
@endif
