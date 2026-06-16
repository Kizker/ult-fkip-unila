@props([
  'href' => null,
  'variant' => 'primary', // primary|secondary|ghost|danger
  'type' => 'button'
])

@php
  $base = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed';
  $variants = [
    'primary' => 'border border-transparent bg-[rgb(var(--c-primary))] text-white hover:bg-white hover:text-[rgb(var(--c-primary))] hover:border-[rgb(var(--c-primary))]',
    'secondary' => 'border border-[rgb(var(--c-border))] bg-white/70 text-[rgb(var(--c-fg))] hover:bg-[rgb(var(--c-primary))] hover:text-white hover:border-[rgb(var(--c-primary))] dark:bg-zinc-800 dark:text-[rgb(var(--c-fg))] dark:hover:bg-[rgb(var(--c-primary))] dark:hover:text-white dark:hover:border-[rgb(var(--c-primary))]',
    'ghost' => 'border border-[rgb(var(--c-border))] bg-white/70 text-[rgb(var(--c-fg))] hover:bg-[rgb(var(--c-primary))] hover:text-white hover:border-[rgb(var(--c-primary))] dark:bg-zinc-900/70 dark:text-[rgb(var(--c-fg))] dark:hover:bg-[rgb(var(--c-primary))] dark:hover:text-white dark:hover:border-[rgb(var(--c-primary))]',
    'danger' => 'bg-[rgb(var(--c-danger))] text-white hover:opacity-95',
  ];
  $cls = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }}>
    {{ $slot }}
  </a>
@else
  <button type="{{ $type }}" {{ $attributes->merge(['class' => $cls]) }}>
    {{ $slot }}
  </button>
@endif
