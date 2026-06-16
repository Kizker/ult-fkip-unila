@extends('layouts.app')
@section('section','Template Nomor Dokumen')

@section('content')
<div class="page-admin-doc-formats-edit">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Nomor dokumen</div>
      <h1 class="admin-page-title">Edit Format Nomor</h1>
      <p class="admin-page-subtitle">Unit: {{ $format->unit->name }} ({{ $format->unit->code }})</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="ghost" href="{{ route('admin.doc_formats.index') }}">Kembali</x-button>
    </div>
  </header>

  <div class="dn-form-layout">
    <x-card class="dn-form-card">
      <form method="POST" action="{{ route('admin.doc_formats.update', $format) }}" class="dn-form">
        @csrf
        @method('PUT')

        <div class="dn-form__section">
          <div class="dn-form__title">Target unit</div>
          <div class="dn-form__grid">
            <div class="dn-field">
              <label class="dn-label">Unit</label>
              <select name="unit_id" required class="dn-select">
                @foreach($units as $u)
                  <option value="{{ $u->id }}" @selected((int)$u->id === (int)$format->unit_id)>{{ $u->type->value }} &mdash; {{ $u->name }} ({{ $u->code }})</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="dn-form__section">
          <div class="dn-form__title">Identitas format</div>
          <div class="dn-form__grid dn-form__grid--2">
            <div class="dn-field">
              <x-input name="format_key" label="Format Key" value="{{ old('format_key', $format->format_key) }}" required />
            </div>
            <div class="dn-field">
              <x-input name="name" label="Nama" value="{{ old('name', $format->name) }}" required />
            </div>
          </div>
        </div>

        <div class="dn-form__section">
          <div class="dn-form__title">Template</div>
          <div class="dn-form__grid">
            <div class="dn-field">
              <x-textarea name="template" label="Template" rows="3" required>{{ old('template', $format->template) }}</x-textarea>
              <p class="dn-hint">Pastikan template mengandung <span class="dn-mono">{SEQ}</span> atau <span class="dn-mono">{SEQ:n}</span>.</p>
            </div>
          </div>
        </div>

        <div class="dn-form__section">
          <div class="dn-form__title">Opsi</div>
          <div class="dn-form__grid dn-form__grid--2 dn-form__grid--tight">
            <div class="dn-field">
              <x-input type="number" name="seq_padding" label="Padding default" value="{{ old('seq_padding', $format->seq_padding) }}" min="1" max="10" />
            </div>
            <label class="dn-switch">
              <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $format->is_active)) class="dn-switch__box">
              <span class="dn-switch__label">Aktif</span>
            </label>
          </div>
        </div>

        <div class="dn-form__actions">
          <x-button type="submit">Simpan</x-button>
          <x-button variant="ghost" href="{{ route('admin.doc_formats.index') }}">Batal</x-button>
        </div>
      </form>
    </x-card>

    <div class="dn-aside">
      <x-card>
        <div class="admin-card-title">Placeholder tersedia</div>
        <div class="admin-card-subtitle">Gunakan placeholder agar konsisten antar unit.</div>
        <ul class="dn-help-list">
          <li><span class="dn-mono">{SEQ}</span> / <span class="dn-mono">{SEQ:4}</span>: urutan + padding otomatis</li>
          <li><span class="dn-mono">{UNIT_CODE}</span> atau <span class="dn-mono">{UNIT}</span></li>
          <li><span class="dn-mono">{YYYY}</span> dan <span class="dn-mono">{MM}</span></li>
        </ul>
        <div class="dn-example" aria-label="Contoh template">
          <div class="dn-example__label">Contoh</div>
          <div class="dn-example__code dn-mono">{SEQ:4}/ULT-FKIP/{UNIT_CODE}/{YYYY}</div>
        </div>
      </x-card>
    </div>
  </div>
</div>
@endsection
