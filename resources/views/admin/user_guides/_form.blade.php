@php
    $isEdit = isset($item) && $item;
    $selectedRoles = collect(old('allowed_roles', $isEdit ? $item->roles->pluck('id')->all() : []))
        ->map(static fn ($id): int => (int) $id)
        ->all();
    $isPublic = (bool) old('is_public', $isEdit ? (bool) $item->is_public : false);
    $isPublished = (bool) old('is_published', $isEdit ? (bool) $item->is_published : true);
    $publishedAt = old('published_at', $isEdit && $item->published_at ? $item->published_at->format('Y-m-d') : now()->format('Y-m-d'));
    $contentType = old('content_type', $isEdit ? ($item->content_type ?: \App\Models\UserGuide::CONTENT_TYPE_PDF) : \App\Models\UserGuide::CONTENT_TYPE_PDF);
    $isPdf = $contentType === \App\Models\UserGuide::CONTENT_TYPE_PDF;
@endphp

<x-card class="ug-form-card">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="ug-form">
        @csrf
        @if (!empty($method) && strtoupper((string) $method) !== 'POST')
            @method($method)
        @endif

        <div class="ug-form-grid">
            <x-input
                name="title_id"
                label="Judul Panduan (ID)"
                value="{{ old('title_id', $isEdit ? $item->title_id : '') }}"
                required
            />
            <x-input
                name="title_en"
                label="Guide Title (EN)"
                value="{{ old('title_en', $isEdit ? $item->title_en : '') }}"
            />
            <x-input
                name="slug"
                label="Slug"
                value="{{ old('slug', $isEdit ? $item->slug : '') }}"
                placeholder="auto jika kosong"
            />
            <x-input
                name="published_at"
                label="Tanggal Publikasi"
                value="{{ $publishedAt }}"
                placeholder="YYYY-MM-DD"
            />
        </div>

        <div class="ug-form-grid mt-4">
            <x-textarea name="summary_id" label="Ringkasan (ID)" rows="4">{{ old('summary_id', $isEdit ? $item->summary_id : '') }}</x-textarea>
            <x-textarea name="summary_en" label="Summary (EN)" rows="4">{{ old('summary_en', $isEdit ? $item->summary_en : '') }}</x-textarea>
        </div>

        <div class="ug-form-section mt-4">
            <div class="ug-form-section__title">Jenis Konten</div>
            <div class="ug-content-type-grid">
                <label class="ug-content-type-option">
                    <input type="radio" name="content_type" value="pdf" {{ $isPdf ? 'checked' : '' }} data-guide-content-type>
                    <span>
                        <strong>PDF</strong>
                        <small>Upload dokumen panduan seperti alur saat ini.</small>
                    </span>
                </label>
                <label class="ug-content-type-option">
                    <input type="radio" name="content_type" value="video" {{ !$isPdf ? 'checked' : '' }} data-guide-content-type>
                    <span>
                        <strong>Video Tutorial</strong>
                        <small>Gunakan tautan YouTube untuk ditampilkan di halaman internal.</small>
                    </span>
                </label>
            </div>
            @error('content_type')
                <p class="text-sm text-[rgb(var(--c-danger))] mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="ug-form-section mt-4" data-guide-pdf-panel {{ $isPdf ? '' : 'hidden' }}>
            <div class="ug-form-section__title">File PDF</div>
            @if ($isEdit && $item->isPdf())
                <div class="ug-current-file">
                    <div>
                        <div class="font-semibold">{{ $item->original_name }}</div>
                        <div class="text-xs text-muted">
                            Ukuran: {{ number_format(((int) $item->size) / 1024, 1) }} KB
                        </div>
                    </div>
                    <div class="inline-flex flex-wrap gap-2">
                        <x-button href="{{ route('user_guides.file', $item->slug) }}" variant="ghost" target="_blank">
                            Preview publik
                        </x-button>
                        <x-button href="{{ route('user_guides.download', $item->slug) }}" variant="secondary" target="_blank">
                            Unduh file
                        </x-button>
                    </div>
                </div>
            @endif

            <x-file-input
                id="pdf"
                name="pdf"
                :label="$isEdit ? 'Ganti file PDF (opsional)' : 'Upload file PDF'"
                accept=".pdf,application/pdf"
                :required="!$isEdit"
                help="Format wajib PDF. Ukuran maksimum mengikuti konfigurasi sistem."
            />
        </div>

        <div class="ug-form-section mt-4" data-guide-video-panel {{ $isPdf ? 'hidden' : '' }}>
            <div class="ug-form-section__title">Tautan Video</div>
            <x-input
                name="video_url"
                label="Tautan YouTube"
                value="{{ old('video_url', $isEdit ? $item->video_url : '') }}"
                placeholder="https://www.youtube.com/watch?v=..."
                help="Saat ini hanya mendukung tautan YouTube yang valid."
            />
        </div>

        <div class="ug-form-section mt-4">
            <div class="ug-form-section__title">Visibilitas</div>
            <div class="ug-toggle-row">
                <label class="ug-toggle">
                    <input type="checkbox" name="is_public" value="1" {{ $isPublic ? 'checked' : '' }}>
                    <span>Dapat dilihat umum (tanpa login)</span>
                </label>
                <label class="ug-toggle">
                    <input type="checkbox" name="is_published" value="1" {{ $isPublished ? 'checked' : '' }}>
                    <span>Published</span>
                </label>
            </div>

            <div class="ug-role-panel">
                <div class="ug-role-panel__title">Role yang boleh melihat (jika tidak publik)</div>
                <p class="ug-role-panel__hint">
                    Pilih 1 atau lebih role untuk akses berbasis login. Jika opsi "umum" aktif, semua orang tetap bisa melihat.
                </p>
                <div class="ug-role-grid">
                    @forelse($roles as $role)
                        <label class="ug-role-item">
                            <input
                                type="checkbox"
                                name="allowed_roles[]"
                                value="{{ $role->id }}"
                                {{ in_array((int) $role->id, $selectedRoles, true) ? 'checked' : '' }}
                            >
                            <span>{{ $role->name }}</span>
                        </label>
                    @empty
                        <div class="text-sm text-muted">Belum ada role.</div>
                    @endforelse
                </div>
                @error('allowed_roles')
                    <p class="text-sm text-[rgb(var(--c-danger))] mt-2">{{ $message }}</p>
                @enderror
                @error('allowed_roles.*')
                    <p class="text-sm text-[rgb(var(--c-danger))] mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="ug-form-actions">
            <x-button type="submit">{{ $submitLabel }}</x-button>
            <x-button href="{{ $backHref }}" variant="ghost">Batal</x-button>
        </div>
    </form>
</x-card>
