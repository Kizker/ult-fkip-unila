@extends('layouts.app')
@section('section','Preview')
@section('content')
<div class="page-preview-post">
  <div class="flex items-start justify-between gap-4">
    <div>
      <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium bg-amber-500/15 text-amber-800 dark:text-amber-200">
        PREVIEW (belum dipublikasi)
      </div>
      <h1 class="text-2xl font-semibold mt-3">{{ $post->title_id }}</h1>
      <div class="text-sm text-muted mt-2">Type: <span class="font-medium">{{ $typeLabel ?? '-' }}</span> - Slug: <span class="font-medium">{{ $post->slug }}</span></div>
    </div>
    <x-button href="{{ $editUrl ?? '#' }}" variant="secondary">Kembali ke Editor</x-button>
  </div>

  <x-card class="mt-6">
    @if (filled($post->image_path ?? null))
      <img
        class="content-cover"
        src="{{ asset('storage/' . ltrim((string) $post->image_path, '/')) }}"
        alt="{{ $post->title_id }}"
        loading="lazy"
      >
    @endif
    <article class="prose prose-slate dark:prose-invert max-w-none">
      {!! $post->content_html_id !!}
    </article>
  </x-card>
</div>
@endsection
