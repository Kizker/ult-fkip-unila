@extends('layouts.app')
@section('section', 'Panduan Placeholder')

@section('content')
@php
  $requiredKeyCount = (int) count($requiredTemplateKeys ?? []);
  $lockedRuleCount = (int) count($lockedRules ?? []);
  $groupCount = (int) count($groups ?? []);
  $exampleCount = (int) collect($groups ?? [])->sum(fn ($group) => count($group['examples'] ?? []));
  $sourceRefCount = (int) collect($groups ?? [])->sum(fn ($group) => count($group['source_refs'] ?? []));
  $certificateGlobalCount = (int) count($certificateGuide['required_global'] ?? []);
  $certificatePerSignerCount = (int) count($certificateGuide['required_per_signer'] ?? []);
  $certificateExampleImageUrl = asset('example/CONTOH SERTIFIKAT_page-0001.jpg');
@endphp

<div class="page-admin-services-guide" data-services-guide-page>
  <header class="admin-page-header asg-hero">
    <div class="admin-page-heading asg-hero__heading">
      <div class="admin-page-kicker">Layanan dokumen</div>
      <h1 class="admin-page-title">Panduan Placeholder Template Dokumen</h1>
      <p class="admin-page-subtitle">Acuan baku untuk pembuat template DOCX agar key placeholder konsisten di semua layanan.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="ghost" href="{{ route('admin.layanan.index') }}">Kembali ke Data Layanan</x-button>
    </div>
  </header>

  <section class="asg-kpi-grid" aria-label="Ringkasan pedoman placeholder">
    <article class="asg-kpi-card">
      <div class="asg-kpi-card__label">Key wajib di template</div>
      <div class="asg-kpi-card__value">{{ $requiredKeyCount }}</div>
      <div class="asg-kpi-card__meta">Wajib ada agar nomor surat dan tanggal terisi otomatis.</div>
    </article>
    <article class="asg-kpi-card">
      <div class="asg-kpi-card__label">Aturan terkunci</div>
      <div class="asg-kpi-card__value">{{ $lockedRuleCount }}</div>
      <div class="asg-kpi-card__meta">Rule ini tidak boleh diubah di mapping layanan.</div>
    </article>
    <article class="asg-kpi-card">
      <div class="asg-kpi-card__label">Kelompok layanan</div>
      <div class="asg-kpi-card__value">{{ $groupCount }}</div>
      <div class="asg-kpi-card__meta">Kelompok pedoman dengan contoh placeholder berbeda.</div>
    </article>
    <article class="asg-kpi-card">
      <div class="asg-kpi-card__label">Contoh mapping</div>
      <div class="asg-kpi-card__value">{{ $exampleCount + $sourceRefCount }}</div>
      <div class="asg-kpi-card__meta">{{ $exampleCount }} placeholder + {{ $sourceRefCount }} source_ref.</div>
    </article>
  </section>

  <x-card class="asg-card asg-foundation">
    <div class="asg-card-head">
      <div class="asg-card-head__main">
        <div class="asg-eyebrow">Pondasi Template</div>
        <div class="admin-card-title">Format Dasar Placeholder</div>
        <div class="admin-card-subtitle">Gunakan format ini agar validasi, ekstraksi, dan mapping berjalan konsisten.</div>
      </div>
    </div>

    <div class="asg-foundation-grid">
      <div class="asg-format-box">
        <div class="asg-format-box__label">Format baku</div>
        <div class="asg-format-box__code">{{ $placeholderFormat }}</div>
        <div class="asg-format-box__hint">Gunakan huruf kapital, angka, dan underscore. Hindari spasi atau karakter khusus.</div>
      </div>

      <div class="asg-rules-box">
        <div class="asg-rules-box__title">Aturan Penulisan</div>
        <ul class="asg-help-list">
          @foreach($keyRules as $rule)
            <li>{{ $rule }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </x-card>

  <div class="asg-main-grid">
    <x-card class="asg-card">
      <div class="asg-card-head">
        <div class="asg-card-head__main">
          <div class="asg-eyebrow">Validasi Minimum</div>
          <div class="admin-card-title">Placeholder Wajib di Template</div>
          <div class="admin-card-subtitle">Token berikut harus selalu ada untuk menjamin proses penerbitan surat.</div>
        </div>
      </div>

      <div class="asg-table-wrap">
        <table class="asg-table">
          <thead>
            <tr>
              <th>Key</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($requiredTemplateKeys as $item)
              <tr>
                <td data-label="Key">
                  <span class="asg-key">{{ '{' }}{{ '{' }}{{ $item['key'] }}{{ '}' }}{{ '}' }}</span>
                </td>
                <td data-label="Keterangan">{{ $item['reason'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </x-card>

    <x-card class="asg-card">
      <div class="asg-card-head">
        <div class="asg-card-head__main">
          <div class="asg-eyebrow">Constraint Sistem</div>
          <div class="admin-card-title">Aturan Terkunci</div>
          <div class="admin-card-subtitle">Constraint ini dipakai untuk menjaga integritas nomor surat dan tanggal surat.</div>
        </div>
      </div>

      <ul class="asg-lock-list">
        @foreach($lockedRules as $rule)
          <li>
            <span class="asg-lock-list__dot">!</span>
            <span>{{ $rule }}</span>
          </li>
        @endforeach
      </ul>
    </x-card>
  </div>

  <section class="asg-section" aria-label="Pedoman per kelompok layanan">
    <div class="asg-section-head">
      <div>
        <div class="asg-eyebrow">Library Placeholder</div>
        <h2 class="asg-section-title">Pedoman per Kelompok Layanan</h2>
      </div>
      <div class="asg-section-meta">Pastikan token di dokumen template sesuai source_ref yang dipetakan.</div>
    </div>

    <div class="asg-group-grid">
      @foreach($groups as $group)
        <x-card class="asg-card asg-group-card">
          <div class="asg-group-card__head">
            <span class="asg-chip">{{ $group['type'] }}</span>
            <h3 class="asg-group-card__title">{{ $group['title'] }}</h3>
          </div>
          <p class="asg-group-card__desc">{{ $group['desc'] }}</p>

          <div class="asg-token-grid">
            <section class="asg-token-panel">
              <div class="asg-token-panel__title">Contoh Placeholder</div>
              <ul class="asg-token-list">
                @foreach($group['examples'] as $example)
                  <li><span class="asg-token">{{ '{' }}{{ '{' }}{{ $example }}{{ '}' }}{{ '}' }}</span></li>
                @endforeach
              </ul>
            </section>

            <section class="asg-token-panel">
              <div class="asg-token-panel__title">Contoh source_ref</div>
              <ul class="asg-token-list asg-token-list--source">
                @foreach($group['source_refs'] as $ref)
                  <li><span class="asg-token">{{ $ref }}</span></li>
                @endforeach
              </ul>
            </section>
          </div>
        </x-card>
      @endforeach
    </div>
  </section>

  <x-card class="asg-card asg-checklist-card">
    <div class="asg-card-head">
      <div class="asg-card-head__main">
        <div class="asg-eyebrow">Pra Upload</div>
        <div class="admin-card-title">Checklist Sebelum Upload Template</div>
      </div>
    </div>

    <ul class="asg-checklist">
      <li>
        <span class="asg-checklist__index">1</span>
        <span>Pastikan semua placeholder pakai format <span class="asg-mono">{{ $placeholderFormat }}</span>.</span>
      </li>
      <li>
        <span class="asg-checklist__index">2</span>
        <span>Pastikan key wajib <span class="asg-mono">{{ '{' }}{{ '{' }}NOMOR_SURAT{{ '}' }}{{ '}' }}</span> dan <span class="asg-mono">{{ '{' }}{{ '{' }}TANGGAL_SURAT{{ '}' }}{{ '}' }}</span> ada di dokumen.</span>
      </li>
      <li>
        <span class="asg-checklist__index">3</span>
        <span>Setelah upload template, jalankan ekstrak placeholder lalu cek mapping per source_type.</span>
      </li>
      <li>
        <span class="asg-checklist__index">4</span>
        <span>Jika source_type = FORM, pastikan field form tersedia dan terhubung ke placeholder.</span>
      </li>
    </ul>
  </x-card>

  @if(!empty($certificateGuide))
    <x-card class="asg-card asg-certificate-card">
      <div class="asg-card-head">
        <div class="asg-card-head__main">
          <div class="asg-eyebrow">Lampiran Khusus</div>
          <div class="admin-card-title">Pedoman Placeholder Sertifikat/Piagam</div>
          <div class="admin-card-subtitle">Panduan ini khusus untuk layanan dengan sumber dokumen <span class="asg-mono">.pptx</span> dari pemohon.</div>
        </div>
      </div>

      <div class="asg-cert-kpi-grid">
        <article class="asg-cert-kpi">
          <div class="asg-cert-kpi__label">Token global</div>
          <div class="asg-cert-kpi__value">{{ $certificateGlobalCount }}</div>
        </article>
        <article class="asg-cert-kpi">
          <div class="asg-cert-kpi__label">Token per signer</div>
          <div class="asg-cert-kpi__value">{{ $certificatePerSignerCount }}</div>
        </article>
      </div>

      <ul class="asg-help-list mt-4">
        @foreach(($certificateGuide['notes'] ?? []) as $note)
          <li>{{ $note }}</li>
        @endforeach
      </ul>

      <section class="asg-example-card mt-4">
        <div class="asg-token-panel__title">Checklist kompatibilitas LibreOffice (wajib untuk deploy)</div>
        <ul class="asg-help-list mt-2">
          <li>Gunakan nama font fisik di template, bukan font theme seperti <span class="asg-mono">+mn-lt</span> atau <span class="asg-mono">+mj-lt</span>.</li>
          <li>Rekomendasi umum:
            Serif = <span class="asg-mono">Times New Roman</span>, <span class="asg-mono">Cambria</span>, <span class="asg-mono">Georgia</span>;
            Serif tambahan = <span class="asg-mono">Garamond</span>, <span class="asg-mono">Palatino Linotype</span>, <span class="asg-mono">Book Antiqua</span>, <span class="asg-mono">Constantia</span>;
            Sans Serif = <span class="asg-mono">Arial</span>, <span class="asg-mono">Calibri</span>, <span class="asg-mono">Segoe UI</span>, <span class="asg-mono">Verdana</span>;
            Sans Serif tambahan = <span class="asg-mono">Tahoma</span>, <span class="asg-mono">Trebuchet MS</span>, <span class="asg-mono">Corbel</span>, <span class="asg-mono">Candara</span>;
            Monospace (opsional) = <span class="asg-mono">Consolas</span>, <span class="asg-mono">Courier New</span>.
          </li>
          <li>Jalankan <strong>Replace Fonts</strong> di PowerPoint sebelum upload template agar tidak ada fallback font saat render di server.</li>
          <li>Sebelum finalisasi pedoman, cek font yang benar-benar tersedia di server: <span class="asg-mono">fc-list | sort</span>.</li>
          <li>Gunakan font regular. Untuk tebal/miring/garis bawah, gunakan style <strong>Bold</strong>, <strong>Italic</strong>, dan <strong>Underline</strong> (jangan ganti ke font dekoratif lain).</li>
          <li>Elemen yang di-mirror/flip sebaiknya diubah menjadi gambar final (PNG) untuk mencegah orientasi berubah saat konversi PDF.</li>
          <li>Text box penting disarankan <strong>Single line spacing</strong>, <strong>Before/After 0 pt</strong>, dan <strong>Do not Autofit</strong>.</li>
          <li>Sebelum publish layanan, lakukan uji submit 1 request dan cek <strong>Preview Dokumen</strong> di detail request untuk memastikan layout sesuai.</li>
        </ul>
      </section>

      <section class="asg-example-card mt-4">
        <div class="asg-token-panel__title">Contoh visual sertifikat/piagam</div>
        <a class="block mt-3" href="{{ $certificateExampleImageUrl }}" target="_blank" rel="noopener noreferrer">
          <img
            src="{{ $certificateExampleImageUrl }}"
            alt="Contoh layout sertifikat/piagam untuk acuan penempatan placeholder"
            class="w-full rounded-xl border border-[rgb(var(--c-border))] bg-white shadow-sm"
            loading="lazy"
          >
        </a>
        <div class="text-xs text-muted mt-2">Klik gambar untuk membuka ukuran penuh.</div>
      </section>

      <div class="asg-certificate-grid">
        <section class="asg-token-panel">
          <div class="asg-token-panel__title">Token wajib (global)</div>
          <ul class="asg-token-list">
            @foreach(($certificateGuide['required_global'] ?? []) as $token)
              <li><span class="asg-token">{{ '{' }}{{ '{' }}{{ $token }}{{ '}' }}{{ '}' }}</span></li>
            @endforeach
          </ul>
        </section>

        <section class="asg-token-panel">
          <div class="asg-token-panel__title">Token wajib per signer (i = 1..n)</div>
          <ul class="asg-token-list">
            @foreach(($certificateGuide['required_per_signer'] ?? []) as $token)
              <li><span class="asg-token">{{ '{' }}{{ '{' }}{{ $token }}{{ '}' }}{{ '}' }}</span></li>
            @endforeach
          </ul>
        </section>

        <section class="asg-token-panel">
          <div class="asg-token-panel__title">Token opsional</div>
          <ul class="asg-token-list">
            @foreach(($certificateGuide['optional'] ?? []) as $token)
              <li><span class="asg-token">{{ '{' }}{{ '{' }}{{ $token }}{{ '}' }}{{ '}' }}</span></li>
            @endforeach
          </ul>
        </section>

        <section class="asg-token-panel">
          <div class="asg-token-panel__title">Token yang tidak dipakai</div>
          <ul class="asg-token-list asg-token-list--muted">
            @foreach(($certificateGuide['unused'] ?? []) as $token)
              <li><span class="asg-token">{{ '{' }}{{ '{' }}{{ $token }}{{ '}' }}{{ '}' }}</span></li>
            @endforeach
          </ul>
        </section>
      </div>

      <div class="asg-example-grid">
        <section class="asg-example-card">
          <div class="asg-token-panel__title">Contoh siap pakai: 1 signer</div>
          <pre class="asg-code">@foreach(($certificateGuide['example_1_signer'] ?? []) as $row){{ $row }}
@endforeach</pre>
        </section>
        <section class="asg-example-card">
          <div class="asg-token-panel__title">Contoh siap pakai: 2 signer</div>
          <pre class="asg-code">@foreach(($certificateGuide['example_2_signer'] ?? []) as $row){{ $row }}
@endforeach</pre>
        </section>
      </div>
    </x-card>
  @endif
</div>
@endsection
