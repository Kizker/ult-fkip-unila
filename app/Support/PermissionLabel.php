<?php

namespace App\Support;

class PermissionLabel
{
    /**
     * Label khusus untuk permission yang sudah dikenal sistem.
     *
     * @var array<string, string>
     */
    private const EXACT_LABELS = [
        // Identity & access
        'users.manage' => 'Kelola pengguna',
        'roles.manage' => 'Kelola role',
        'permissions.manage' => 'Kelola permission',

        // Core domain
        'services.manage' => 'Kelola layanan',
        'cms.manage' => 'Kelola konten website',
        'site_settings.manage' => 'Kelola pengaturan situs',
        'academics.manage' => 'Kelola data akademik',

        // Requests access
        'requests.view_any' => 'Lihat semua pengajuan',
        'requests.view_unit' => 'Lihat pengajuan unit',
        'requests.view_own' => 'Lihat pengajuan sendiri',
        'requests.create_own' => 'Buat pengajuan sendiri',
        'requests.update_own' => 'Ubah pengajuan sendiri',

        // Processing / gatekeeper
        'requests.process_unit' => 'Proses pengajuan unit',
        'requests.review_ult' => 'Review pengajuan ULT',
        'requests.forward_faculty' => 'Teruskan ke fakultas',

        // Approvals & numbering
        'approvals.unit.sign' => 'Tanda tangan persetujuan unit',
        'approvals.faculty.sign' => 'Tanda tangan persetujuan fakultas',
        'document_numbers.issue' => 'Terbitkan nomor dokumen',
        'document_numbers.manage_formats' => 'Kelola format nomor dokumen',
        'letter_numbers.manage_formats' => 'Kelola format nomor surat',

        // Attachments
        'attachments.upload_own' => 'Unggah lampiran sendiri',
        'attachments.upload_output' => 'Unggah lampiran output',
        'attachments.download_private' => 'Unduh lampiran privat',

        // Reporting & audit
        'reports.view' => 'Lihat laporan',
        'audit_logs.view' => 'Lihat log audit',

        // Document module
        'doc_services.manage' => 'Kelola layanan dokumen',
        'doc_services.publish' => 'Publikasikan layanan dokumen',
        'doc_templates.upload' => 'Unggah template dokumen',
        'doc_placeholders.manage' => 'Kelola placeholder dokumen',
        'doc_signers.manage' => 'Kelola signer dokumen',
        'doc_requests.gate' => 'Verifikasi awal pengajuan dokumen',
        'doc_requests.assemble' => 'Susun dokumen pengajuan',
        'doc_signoffs.decide' => 'Putuskan penandatanganan dokumen',
    ];

    /**
     * Label fallback untuk bagian modul.
     *
     * @var array<string, string>
     */
    private const MODULE_LABELS = [
        'users' => 'Pengguna',
        'roles' => 'Role',
        'permissions' => 'Permission',
        'services' => 'Layanan',
        'cms' => 'Konten website',
        'site_settings' => 'Pengaturan situs',
        'academics' => 'Akademik',
        'requests' => 'Pengajuan',
        'approvals' => 'Persetujuan',
        'document_numbers' => 'Nomor dokumen',
        'letter_numbers' => 'Nomor surat',
        'attachments' => 'Lampiran',
        'reports' => 'Laporan',
        'audit_logs' => 'Log audit',
        'doc_services' => 'Layanan dokumen',
        'doc_templates' => 'Template dokumen',
        'doc_placeholders' => 'Placeholder dokumen',
        'doc_signers' => 'Signer dokumen',
        'doc_requests' => 'Pengajuan dokumen',
        'doc_signoffs' => 'Penandatanganan dokumen',
    ];

    /**
     * Label fallback untuk bagian aksi.
     *
     * @var array<string, string>
     */
    private const ACTION_LABELS = [
        'manage' => 'Kelola',
        'view' => 'Lihat',
        'view_any' => 'Lihat semua',
        'view_unit' => 'Lihat unit',
        'view_own' => 'Lihat sendiri',
        'create' => 'Buat',
        'create_own' => 'Buat sendiri',
        'update' => 'Ubah',
        'update_own' => 'Ubah sendiri',
        'delete' => 'Hapus',
        'sign' => 'Tanda tangan',
        'issue' => 'Terbitkan nomor',
        'publish' => 'Publikasikan',
        'upload' => 'Unggah',
        'upload_own' => 'Unggah sendiri',
        'upload_output' => 'Unggah output',
        'download' => 'Unduh',
        'download_private' => 'Unduh privat',
        'process_unit' => 'Proses unit',
        'review_ult' => 'Review ULT',
        'forward_faculty' => 'Teruskan ke fakultas',
        'gate' => 'Verifikasi awal',
        'assemble' => 'Susun',
        'decide' => 'Putuskan',
        'manage_formats' => 'Kelola format',
    ];

    /**
     * Label fallback untuk scope tambahan di tengah key.
     *
     * @var array<string, string>
     */
    private const SCOPE_LABELS = [
        'unit' => 'unit',
        'faculty' => 'fakultas',
        'ult' => 'ULT',
        'own' => 'sendiri',
        'any' => 'semua',
        'private' => 'privat',
        'output' => 'output',
    ];

    public static function make(?string $permissionName): string
    {
        $permissionName = trim((string) $permissionName);
        if ($permissionName === '') {
            return '-';
        }

        if (isset(self::EXACT_LABELS[$permissionName])) {
            return self::EXACT_LABELS[$permissionName];
        }

        $parts = array_values(array_filter(explode('.', $permissionName), static fn ($v) => $v !== ''));
        if (count($parts) < 2) {
            return self::headline($permissionName);
        }

        $moduleKey = array_shift($parts);
        $actionKey = array_pop($parts);

        $moduleLabel = self::MODULE_LABELS[$moduleKey] ?? self::headline($moduleKey);
        $actionLabel = self::ACTION_LABELS[$actionKey] ?? self::headline($actionKey);

        if (count($parts) === 0) {
            return trim($actionLabel.' '.$moduleLabel);
        }

        $scopeLabel = implode(' ', array_map(
            static fn (string $scope): string => self::SCOPE_LABELS[$scope] ?? self::headline($scope),
            $parts
        ));

        return trim($actionLabel.' '.$moduleLabel.' ('.$scopeLabel.')');
    }

    private static function headline(string $value): string
    {
        $clean = trim(str_replace(['.', '_', '-'], ' ', $value));
        if ($clean === '') {
            return '-';
        }

        return ucwords(strtolower($clean));
    }
}
