@extends('layouts.app')
@section('section','Template Nomor Surat')

@section('content')
<div class="page-admin-doc-formats-create">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Nomor surat</div>
      <h1 class="admin-page-title">Tambah Template Nomor Surat</h1>
      <p class="admin-page-subtitle">Siapkan template agar input nomor surat lebih cepat dan konsisten.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="ghost" href="{{ route('admin.letter_formats.index') }}">Kembali</x-button>
    </div>
  </header>

  <div
    class="dn-form-layout"
    x-data="{
      template: @js(old('template', $defaults['template'] ?? '{SEQ:5}/UN26.13/PN.01.00/{YYYY}')),
      seqPadding: @js(old('seq_padding', $defaults['seq_padding'] ?? 5)),
      sampleSeq: 0,
      unitCode: 'UN26.13',
      unitName: '',
      unitType: '',
      unitResizeSyncBound: false,
      year: new Date().getFullYear(),
      month: String(new Date().getMonth() + 1).padStart(2, '0'),
      setPreset(tpl, pad) { this.template = tpl; if (pad) this.seqPadding = pad; },
      syncUnitOptionLabels(selectEl) {
        if (!selectEl) return;
        const compact = window.matchMedia('(max-width: 767px)').matches;
        for (const opt of Array.from(selectEl.options || [])) {
          const shortLabel = opt.dataset.labelShort || opt.dataset.code || opt.textContent || '';
          const fullLabel = opt.dataset.labelFull || shortLabel;
          opt.textContent = compact ? shortLabel : fullLabel;
        }
      },
      initUnitSelect(selectEl) {
        if (!selectEl) return;
        this.syncUnitOptionLabels(selectEl);
        this.setUnitMeta(selectEl.options[selectEl.selectedIndex]);
        if (this.unitResizeSyncBound) return;
        this.unitResizeSyncBound = true;
        window.addEventListener('resize', () => this.syncUnitOptionLabels(selectEl), { passive: true });
      },
      setUnitMeta(opt) {
        if (!opt) return;
        this.unitCode = opt.dataset.code || this.unitCode;
        this.unitName = opt.dataset.name || '';
        this.unitType = opt.dataset.type || '';
      },
      renderPreview() {
        let tpl = this.template || '';
        let pad = parseInt(this.seqPadding || 3, 10);
        if (!pad || pad < 1) pad = 3;
        if (pad > 10) pad = 10;
        const m = tpl.match(/\{SEQ:(\d+)\}/);
        if (m) {
          pad = Math.max(1, Math.min(10, parseInt(m[1], 10)));
        }
        const seqText = String(this.sampleSeq).padStart(pad, '0');
        let out = tpl;
        out = out.replace(/\{SEQ:\d+\}/g, seqText);
        out = out.replace(/\{SEQ\}/g, seqText);
        out = out.split('{UNIT_CODE}').join(this.unitCode);
        out = out.split('{UNIT}').join(this.unitCode);
        out = out.split('{YYYY}').join(String(this.year));
        out = out.split('{MM}').join(this.month);
        return out || '-';
      },
    }"
  >
    <x-card class="dn-form-card">
      <form method="POST" action="{{ route('admin.letter_formats.store') }}" class="dn-form">
        @csrf

        <div class="dn-form__section">
          <div class="dn-form__title">Target unit</div>
          <div class="dn-form__grid">
            <div class="dn-field">
              <label class="dn-label">Unit</label>
              <select
                name="unit_id"
                required
                class="dn-select dn-select--unit"
                x-ref="unitSelect"
                x-init="$nextTick(() => initUnitSelect($el))"
                @change="setUnitMeta($event.target.options[$event.target.selectedIndex])"
              >
                @foreach($units as $u)
                  <option
                    value="{{ $u->id }}"
                    data-code="{{ $u->code }}"
                    data-name="{{ $u->name }}"
                    data-type="{{ $u->type->value }}"
                    data-label-short="{{ $u->code }}"
                    data-label-full="{{ $u->type->value }} - {{ $u->name }} ({{ $u->code }})"
                    @selected((int)old('unit_id', $defaults['unit_id'] ?? 0) === (int)$u->id)
                  >{{ $u->type->value }} - {{ $u->name }} ({{ $u->code }})</option>
                @endforeach
              </select>
              <p class="dn-hint" x-show="unitName" x-text="(unitType ? unitType + ' - ' : '') + unitName"></p>
            </div>
          </div>
        </div>

        <div class="dn-form__section">
          <div class="dn-form__title">Identitas template</div>
          <div class="dn-form__grid dn-form__grid--2">
            <div class="dn-field">
              <x-input name="format_key" label="Format Key" value="{{ old('format_key', $defaults['format_key'] ?? 'default') }}" required />
            </div>
            <div class="dn-field">
              <x-input name="name" label="Nama" value="{{ old('name', $defaults['name'] ?? 'Default') }}" required />
            </div>
          </div>
        </div>

        <div class="dn-form__section">
          <div class="dn-form__title">Template</div>
          <div class="dn-form__grid">
            <div class="dn-field">
              <x-textarea name="template" label="Template" rows="3" required x-model="template">{{ old('template', $defaults['template'] ?? '{SEQ:5}/UN26.13/PN.01.00/{YYYY}') }}</x-textarea>
              <p class="dn-hint">Pastikan template mengandung <span class="dn-mono">{SEQ}</span> atau <span class="dn-mono">{SEQ:n}</span>. Gunakan preset & preview di samping agar lebih cepat.</p>
            </div>
          </div>
        </div>

        <div class="dn-form__section">
          <div class="dn-form__title">Opsi</div>
          <div class="dn-form__grid dn-form__grid--2 dn-form__grid--tight">
            <div class="dn-field">
              <x-input type="number" name="seq_padding" label="Padding default" value="{{ old('seq_padding', $defaults['seq_padding'] ?? 5) }}" min="1" max="10" x-model.number="seqPadding" />
            </div>
            <label class="dn-switch">
              <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $defaults['is_active'] ?? true)) class="dn-switch__box">
              <span class="dn-switch__label">Aktif</span>
            </label>
          </div>
        </div>

        <div class="dn-form__actions">
          <x-button type="submit">Simpan</x-button>
          <x-button variant="ghost" href="{{ route('admin.letter_formats.index') }}">Batal</x-button>
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
          <div class="dn-example__code dn-mono">{SEQ:5}/UN26.13/PN.01.00/{YYYY}</div>
        </div>
        <div class="dn-example mt-3" aria-label="Contoh output">
          <div class="dn-example__label">Contoh output (dummy)</div>
          <div class="dn-example__code dn-mono">00000/UN26.13/PN.01.00/2025</div>
        </div>
      </x-card>

      <x-card class="mt-4">
        <div class="admin-card-title">Preset & Preview</div>
        <div class="admin-card-subtitle">Pilih preset untuk mempercepat, lalu cek preview hasil.</div>

        <div class="dn-preset-actions mt-3 flex flex-wrap gap-2">
          <x-button type="button" variant="secondary" @click="setPreset('{SEQ:5}/UN26.13/PN.01.00/{YYYY}', 5)">Preset UN26.13</x-button>
          <x-button type="button" variant="ghost" @click="setPreset('{SEQ:3}/ULT-FKIP/{UNIT_CODE}/{YYYY}', 3)">Preset ULT-FKIP</x-button>
        </div>

        <div class="mt-4 space-y-1">
          <label class="text-sm font-medium">Contoh nomor urut</label>
          <input type="number" min="0" class="as-input" x-model.number="sampleSeq">
          <div class="text-xs text-muted">Dipakai hanya untuk preview.</div>
        </div>

        <div class="dn-example mt-4" aria-label="Preview output">
          <div class="dn-example__label">Preview hasil</div>
          <div class="dn-example__code dn-mono" x-text="renderPreview()"></div>
        </div>
      </x-card>
    </div>
  </div>
</div>
@endsection
