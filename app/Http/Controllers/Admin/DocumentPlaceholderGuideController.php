<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DocumentPlaceholderGuideController extends Controller
{
    public function index()
    {
        return view('admin.services.placeholder_guide', [
            'placeholderFormat' => '{{PLACEHOLDER_KEY}}',
            'keyRules' => [
                'Gunakan huruf kapital, angka, dan underscore saja (A-Z, 0-9, _).',
                'Tidak boleh mengandung spasi, dash, titik, atau karakter khusus.',
                'Setiap key harus unik dalam satu template.',
            ],
            'requiredTemplateKeys' => [
                ['key' => 'NOMOR_SURAT', 'reason' => 'Wajib ada di template untuk nomor dokumen saat gate awal (Admin Jurusan/Staf ULT).'],
                ['key' => 'TANGGAL_SURAT', 'reason' => 'Wajib ada di template untuk tanggal surat otomatis saat signing selesai.'],
            ],
            'groups' => [
                [
                    'type' => 'FORM',
                    'title' => 'Form Pemohon (input manual pemohon)',
                    'desc' => 'Data diambil dari field form layanan. Jika source_ref kosong, sistem akan membuat field otomatis dari nama placeholder.',
                    'examples' => ['NAMA_MAHASISWA', 'NPM', 'JUDUL_KEGIATAN', 'TANGGAL_KEGIATAN'],
                    'source_refs' => [
                        'nama_mahasiswa',
                        'npm',
                        'judul_kegiatan',
                    ],
                ],
                [
                    'type' => 'PROFILE',
                    'title' => 'Profil (akun, unit, dan signer)',
                    'desc' => 'Data diambil dari profil pemohon atau profil signer sesuai role di rantai penandatangan.',
                    'examples' => ['NAMA_PEMOHON', 'EMAIL_PEMOHON', 'NAMA_DEKAN', 'NIP_KAJUR'],
                    'source_refs' => [
                        'user.name',
                        'user.email',
                        'user.user_number',
                        'unit.prodi.name',
                        'unit.jurusan.name',
                        'unit.fakultas.name',
                        'signer.DEKAN.name',
                        'signer.DEKAN.user_number',
                        'signer.KAJUR.name',
                        'signer.KAJUR.unit.prodi.name',
                        'signer.KAJUR.unit.jurusan.name',
                        'signer.KAJUR.unit.fakultas.name',
                    ],
                ],
                [
                    'type' => 'INTERNAL',
                    'title' => 'Internal (diisi proses internal sistem)',
                    'desc' => 'Dipakai untuk data yang berasal dari proses workflow/gate/signing, bukan dari form pemohon.',
                    'examples' => ['NOMOR_SURAT', 'NAMA_PENANDATANGAN', 'NIP_PENANDATANGAN'],
                    'source_refs' => ['(umumnya kosong)'],
                ],
                [
                    'type' => 'SYSTEM_AUTOFILL',
                    'title' => 'Autofill Sistem (otomatis penuh)',
                    'desc' => 'Nilai diisi otomatis oleh sistem pada momen tertentu tanpa input manual admin/pemohon.',
                    'examples' => ['TANGGAL_SURAT'],
                    'source_refs' => ['(umumnya kosong)'],
                ],
            ],
            'lockedRules' => [
                'NOMOR_SURAT wajib source_type INTERNAL dan tidak boleh diubah.',
                'TANGGAL_SURAT wajib source_type SYSTEM_AUTOFILL dan tidak boleh diubah.',
            ],
            'certificateGuide' => [
                'required_global' => ['nomor_surat', 'tanggal_ttd'],
                'required_per_signer' => ['ttd_i', 'nama_penandatangan_i', 'id_penandatangan_i'],
                'optional' => ['jabatan_penandatangan_i', 'nama_penerima'],
                'unused' => ['kota_ttd', 'tanggal_surat'],
                'notes' => [
                    'Dokumen sumber wajib format .pptx (diunggah pemohon).',
                    'Token {{tanggal_ttd}} selalu mengikuti waktu signer terakhir yang menyetujui.',
                    'Jumlah signer harus sama dengan indeks token per signer (i = 1..n).',
                    'Setiap token {{ttd_i}} wajib diletakkan di dalam shape khusus area tanda tangan.',
                    'Shape untuk {{ttd_i}} wajib transparan (tanpa fill dan tanpa outline) agar hanya gambar tanda tangan yang terlihat.',
                    'Samakan tinggi shape untuk semua {{ttd_i}} agar ukuran TTD konsisten; sistem menjaga tinggi shape dan lebar TTD dihitung proporsional dari tinggi (bukan tinggi menyesuaikan lebar).',
                ],
                'example_1_signer' => [
                    '{{nomor_surat}}',
                    '{{tanggal_ttd}}',
                    '{{ttd_1}}',
                    '{{nama_penandatangan_1}}',
                    '{{id_penandatangan_1}}',
                    '{{jabatan_penandatangan_1}}',
                    '{{nama_penerima}}',
                ],
                'example_2_signer' => [
                    '{{nomor_surat}}',
                    '{{tanggal_ttd}}',
                    '{{ttd_1}}',
                    '{{nama_penandatangan_1}}',
                    '{{id_penandatangan_1}}',
                    '{{jabatan_penandatangan_1}}',
                    '{{ttd_2}}',
                    '{{nama_penandatangan_2}}',
                    '{{id_penandatangan_2}}',
                    '{{jabatan_penandatangan_2}}',
                    '{{nama_penerima}}',
                ],
            ],
        ]);
    }
}
