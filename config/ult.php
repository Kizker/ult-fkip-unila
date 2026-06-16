<?php

return [
    'legalization_base_url' => env('ULT_LEGALIZATION_BASE_URL', ''),
    // DATA TIDAK TERSEDIA: Format nomor dokumen final FKIP. Harus configurable via site_settings/master data.
    // Default : {SEQ}/ULT-FKIP/{UNIT}/{YYYY}
    'doc_number_default_format' => '{SEQ}/ULT-FKIP/{UNIT}/{YYYY}',
    'doc_number_format_key' => env('ULT_DOC_NUMBER_FORMAT_KEY', 'default'),
    'private_disk' => env('PRIVATE_FILESYSTEM_DISK', 'private'),
    'soffice_path' => env('ULT_SOFFICE_PATH', ''),
    // Default false to preserve exact DOCX template typography/layout in preview.
    'preview_as_pdf' => env('ULT_PREVIEW_AS_PDF', false),
    'upload' => [
        'max_size_mb' => 10,
        'allowed_mimes' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
        'allowed_ext' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
    ],
];
