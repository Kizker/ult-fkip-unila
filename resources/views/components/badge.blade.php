@props(['variant' => 'default'])
@php
  $map = [
    'default' => 'bg-slate-100 text-slate-900 dark:bg-zinc-800 dark:text-zinc-100',
    'success' => 'bg-green-100 text-green-900 dark:bg-green-900/30 dark:text-green-100',
    'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-100',
    'danger' => 'bg-red-100 text-red-900 dark:bg-red-900/30 dark:text-red-100',
    'primary' => 'bg-violet-100 text-violet-900 dark:bg-violet-900/30 dark:text-violet-100',
  ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium '.($map[$variant] ?? $map['default'])]) }}>
  {{ $slot }}
</span>
