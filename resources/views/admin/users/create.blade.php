@extends('layouts.app')
@section('section','Pengguna')
@section('content')
<div class="page-admin-users-create page-admin-user-form">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master data</div>
      <h1 class="admin-page-title">Tambah User</h1>
      <p class="admin-page-subtitle">Superadmin dapat menambahkan user dan menentukan role.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="ghost" href="{{ route('admin.users.index') }}">Kembali</x-button>
    </div>
  </header>

  <form class="au-form" method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="au-layout grid gap-4 lg:grid-cols-12 items-start">
      <x-card class="au-main-card lg:col-span-7">
        <div class="au-section-head flex flex-col gap-1 mb-4">
          <div class="text-sm font-semibold">Data user</div>
          <div class="text-xs text-muted">Informasi dasar dan identitas.</div>
        </div>

        <div class="au-grid grid gap-4 lg:grid-cols-2">
          <x-input name="name" label="Nama" value="{{ old('name') }}" required />
          <x-input name="email" type="email" label="Email" value="{{ old('email') }}" required />
          <div class="au-span-2 lg:col-span-2">
            <x-input name="password" type="password" label="Password" required />
            <p class="text-xs text-muted mt-1">Minimal 8 karakter. Gunakan kombinasi huruf & angka.</p>
          </div>
          <x-input name="student_number" label="Nomor Induk (NIP/NPM) (opsional)" value="{{ old('student_number') }}" />

          @php
            $currentRole = (string) old('role', 'Mahasiswa');
            $currentRoleUpper = strtoupper($currentRole);
            $showJabatan = $currentRoleUpper !== 'MAHASISWA';

            $jabatanOptions = [
              'Dekan',
              'Wakil Dekan Bidang Akademik dan Kerja Sama',
              'Wakil Dekan Bidang Umum dan Keuangan',
              'Wakil Dekan Bidang Kemahasiswaan dan Alumni',
              'Ketua Jurusan',
              'Sekretaris Jurusan',
              'Ketua Program Studi',
              'Dosen',
              'Pembimbing Akademik',
              'Admin Jurusan',
              'Admin Jurusan per Prodi',
            ];
            $jabatanValue = (string) old('jabatan', '');
            $jabatanSelect = in_array($jabatanValue, $jabatanOptions, true) ? $jabatanValue : ($jabatanValue !== '' ? '__other__' : '');
            $jabatanOther = (string) old('jabatan_other', $jabatanSelect === '__other__' ? $jabatanValue : '');
          @endphp
          <div class="au-field {{ $showJabatan ? '' : 'hidden' }}" data-jabatan-field>
            <x-select name="jabatan" label="Jabatan (opsional)" data-jabatan-select>
              <option value="">-</option>
              @foreach($jabatanOptions as $opt)
                <option value="{{ $opt }}" @selected($jabatanSelect === $opt)>{{ $opt }}</option>
              @endforeach
              <option value="__other__" @selected($jabatanSelect === '__other__')>Lainnya (isi sendiri)</option>
            </x-select>
            <div class="{{ $jabatanSelect === '__other__' ? '' : 'hidden' }} mt-2" data-jabatan-other>
              <x-input name="jabatan_other" label="Jabatan lainnya" value="{{ $jabatanOther }}" />
            </div>
          </div>

          @php
            $initialUnitId = (string) old('unit_id', '');
            $initialScopes = array_values(array_unique(array_map('intval', (array) old('scoped_unit_ids', []))));
            $initialRole = $currentRoleUpper;
            $initialJabatan = $jabatanSelect === '__other__' ? $jabatanOther : $jabatanSelect;
          @endphp
          <div
            class="au-span-2 au-unit-picker lg:col-span-2"
            data-unit-picker
            data-initial-unit-id="{{ $initialUnitId }}"
            data-initial-scopes="{{ e(json_encode($initialScopes, JSON_UNESCAPED_UNICODE)) }}"
            data-initial-role="{{ $initialRole }}"
            data-initial-jabatan="{{ $initialJabatan }}"
          >
            <script type="application/json" data-prodi-by-jurusan-json>@json($prodiByJurusan ?? [])</script>
            <input type="hidden" name="unit_id" value="{{ $initialUnitId }}" data-unit-id-hidden>

            <div class="text-sm font-medium mb-1">Unit kerja</div>
            <div class="au-unit-grid grid gap-2 sm:grid-cols-2">
              <div data-unit-fakultas-wrap class="hidden">
                <label class="text-sm font-medium">Fakultas</label>
                @php
                  $fakultasList = $fakultasUnits ?? collect();
                  $singleFakultas = $fakultasList->count() === 1 ? $fakultasList->first() : null;
                @endphp
                @if($singleFakultas)
                  <div class="mt-1" data-unit-fakultas-id="{{ $singleFakultas->id }}">
                    <input class="as-input" value="{{ $singleFakultas->name }}" readonly>
                  </div>
                @else
                  <select class="as-input mt-1" data-unit-fakultas disabled>
                    @foreach($fakultasList as $u)
                      <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                  </select>
                @endif
              </div>

              <div data-unit-jurusan-wrap class="hidden">
                <label class="text-sm font-medium">Jurusan</label>
                <select class="as-input mt-1" data-unit-jurusan>
                  <option value="">-</option>
                  @foreach(($jurusanUnits ?? []) as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                  @endforeach
                </select>
              </div>

              <div data-unit-prodi-wrap class="hidden au-span-2-sm sm:col-span-2">
                <label class="text-sm font-medium">Program studi</label>
                <select class="as-input mt-1" data-unit-prodi>
                  <option value="">-</option>
                  @foreach(($prodiUnits ?? []) as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                  @endforeach
                </select>
              </div>

              <div data-admin-jurusan-mode-wrap class="hidden au-span-2-sm sm:col-span-2">
                <label class="text-sm font-medium">Tipe Admin Jurusan</label>
                <select class="as-input mt-1" data-admin-jurusan-mode>
                  <option value="utama">Admin jurusan utama (kelola semua prodi)</option>
                  <option value="per_prodi">Admin jurusan per prodi (pilih 1+ prodi)</option>
                </select>
              </div>

              <div data-unit-manual-wrap class="hidden au-span-2-sm sm:col-span-2">
                <label class="text-sm font-medium">Unit (manual)</label>
                <select class="as-input mt-1" data-unit-manual>
                  <option value="">-</option>
                  @foreach(($units ?? []) as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <p class="text-xs text-muted mt-1" data-unit-helper>Unit akan menyesuaikan berdasarkan jabatan/role.</p>
          </div>

          @php
            $showScopes = $currentRoleUpper === 'SUPERADMIN'
              || $currentRoleUpper === 'ADMIN'
              || str_starts_with($currentRoleUpper, 'ADMIN_')
              || str_starts_with($currentRoleUpper, 'ADMIN ');
            if (!$showScopes) {
              $jabUpper = strtoupper(trim((string) $initialJabatan));
              $showScopes = in_array($jabUpper, ['ADMIN JURUSAN', 'ADMIN JURUSAN PER PRODI'], true);
            }
          @endphp
          <div class="au-span-2 au-scoped lg:col-span-2 {{ $showScopes ? '' : 'hidden' }}" data-scoped-units>
            <div class="text-sm font-medium mb-1">Program studi yang dikelola (tambahan)</div>
            <select
              name="scoped_unit_ids[]"
              multiple
              class="au-scoped-select w-full rounded-xl border border-[rgb(var(--c-border))] bg-white/70 dark:bg-zinc-900 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))]"
              @disabled(!$showScopes)
              data-scoped-prodi
            >
              @foreach(($prodiUnits ?? []) as $u)
                <option value="{{ $u->id }}" @selected(in_array((string)$u->id, (array)old('scoped_unit_ids', []), true))>{{ $u->name }}</option>
              @endforeach
            </select>
            <p class="text-xs text-muted mt-1">Khusus untuk admin yang menangani lebih dari 1 prodi (mis. admin jurusan per-prodi / multi-prodi).</p>
            @error('scoped_unit_ids')
              <p class="text-sm text-[rgb(var(--c-danger))] mt-1">{{ $message }}</p>
            @enderror
            @error('scoped_unit_ids.*')
              <p class="text-sm text-[rgb(var(--c-danger))] mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </x-card>

      <div class="au-side lg:col-span-5 space-y-4">
        <x-card class="au-photo-card">
          <div class="flex items-center justify-between gap-3 mb-4">
            <div>
              <div class="text-sm font-semibold">Foto profil</div>
              <div class="text-xs text-muted mt-0.5">Opsional, agar tampilan tidak monoton.</div>
            </div>
          </div>

          <div class="au-photo-row flex items-start gap-4">
            <div class="au-photo-preview rounded-2xl grid place-items-center text-xs font-semibold border border-[rgb(var(--c-border))] bg-zinc-50 dark:bg-zinc-900/60 text-muted w-20 h-20 select-none">
              Foto
            </div>
            <div class="flex-1 min-w-0">
              <div class="space-y-1">
                <label class="text-sm font-medium" for="profile_photo">Upload</label>
                <div class="au-file-field" data-file-field data-file-empty-label="Belum ada file dipilih">
                  <input
                    id="profile_photo"
                    name="profile_photo"
                    type="file"
                    accept="image/*"
                    class="sr-only"
                    data-file-input
                  />
                  <button type="button" class="au-file-field__button" data-file-trigger>Pilih file</button>
                  <div class="au-file-field__name" data-file-name aria-live="polite">Belum ada file dipilih</div>
                </div>
                @error('profile_photo')
                  <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
                @enderror
              </div>
              <p class="text-xs text-muted mt-1">PNG/JPG/WEBP, maksimal 2MB.</p>
            </div>
          </div>
        </x-card>

        <x-card class="au-role-card">
          <div class="flex flex-col gap-1 mb-4">
            <div class="text-sm font-semibold">Role</div>
            <div class="text-xs text-muted">Pilih role yang akan digunakan (tidak perlu set per-user lagi).</div>
          </div>

          <x-select name="role" label="Role" required>
            @foreach($roles as $r)
              <option value="{{ $r->name }}" @selected(old('role', 'Mahasiswa') === $r->name)>{{ $r->name }}</option>
            @endforeach
          </x-select>
        </x-card>
      </div>
    </div>

    <div class="au-actions mt-4 flex items-center justify-end">
      <x-button type="submit">Simpan</x-button>
      <x-button variant="ghost" href="{{ route('admin.users.index') }}">Batal</x-button>
    </div>
  </form>
</div>
@endsection
