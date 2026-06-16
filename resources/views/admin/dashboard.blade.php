@extends('layouts.app')
@section('section','Dashboard')
@section('content')
@php
  $total = (int) collect($kpi)->sum();
  $queueCount = method_exists($queue, 'count') ? (int) $queue->count() : (int) count($queue);
@endphp

<div class="page-admin-dashboard">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Ringkasan</div>
      <h1 class="admin-page-title">Dashboard</h1>
      <p class="admin-page-subtitle">Pantau antrian terbaru dan KPI status permohonan.</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total permohonan">
          <div class="admin-meta-pill__label">Total</div>
          <div class="admin-meta-pill__value">{{ $total }}</div>
        </div>
        <div class="admin-meta-pill" aria-label="Antrian ditampilkan">
          <div class="admin-meta-pill__label">Antrian</div>
          <div class="admin-meta-pill__value">{{ $queueCount }}</div>
        </div>
      </div>
      <x-button href="{{ route('admin.requests.index') }}">Kelola permohonan</x-button>
    </div>
  </header>

  <div class="admin-dashboard-layout">
    <x-card class="admin-dashboard-main">
      <div class="admin-card-header">
        <div>
          <div class="admin-card-title">Antrian terbaru</div>
          <div class="admin-card-subtitle">Daftar permohonan terbaru yang perlu diproses.</div>
        </div>
        <x-button variant="ghost" href="{{ route('admin.requests.index') }}">Lihat semua</x-button>
      </div>

      <div class="admin-queue-list">
        @forelse($queue as $r)
          <div class="admin-queue-item">
            <div class="admin-queue-item__meta">
              <div class="admin-queue-item__title">
                <a href="{{ route('admin.requests.show',$r) }}">{{ $r->display_title }}</a>
              </div>
              <div class="admin-queue-item__sub">{{ $r->student->name }} &bull; {{ $r->created_at->format('d M Y H:i') }}</div>
            </div>
            <div class="admin-queue-item__actions">
              <x-status-badge :status="$r->current_status->value ?? $r->current_status" />
              <x-button variant="secondary" href="{{ route('admin.requests.show',$r) }}">Detail</x-button>
            </div>
          </div>
        @empty
          <div class="admin-empty">Belum ada antrian.</div>
        @endforelse
      </div>
    </x-card>

    <x-card class="admin-dashboard-side">
      <div class="admin-card-header">
        <div>
          <div class="admin-card-title">KPI Status</div>
          <div class="admin-card-subtitle">Agregat permohonan berdasarkan status.</div>
        </div>
      </div>
      <div class="admin-kpi-list">
        @foreach($kpi as $st => $c)
          <a
            href="{{ route('admin.requests.index', ['status' => $st]) }}"
            class="admin-kpi-row"
            aria-label="Lihat permohonan dengan status {{ str_replace('_', ' ', $st) }}"
          >
            <x-status-badge :status="$st" />
            <div class="admin-kpi-row__count">{{ $c }}</div>
          </a>
        @endforeach
      </div>
    </x-card>
  </div>
</div>
@endsection
