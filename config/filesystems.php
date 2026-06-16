<?php

$publicDiskRootMode = strtolower((string) env('PUBLIC_DISK_ROOT', 'public'));
$publicDiskRoot = $publicDiskRootMode === 'storage'
    ? storage_path('app/public')
    : public_path('storage');

$storageLinks = $publicDiskRootMode === 'storage'
    ? [
        public_path('storage') => storage_path('app/public'),
    ]
    : [];

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        // Private, policy-gated storage (default for attachments & outputs)
        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'throw' => false,
            'visibility' => 'private',
        ],

        'public' => [
            'driver' => 'local',
            // PUBLIC_DISK_ROOT:
            // - public  => write directly to public/storage (recommended for shared hosting/cPanel)
            // - storage => write to storage/app/public (requires php artisan storage:link)
            'root' => $publicDiskRoot,
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Ready for S3-compatible migration
        's3' => [
            'driver' => 's3',
            'key' => env('S3_ACCESS_KEY_ID'),
            'secret' => env('S3_SECRET_ACCESS_KEY'),
            'region' => env('S3_DEFAULT_REGION'),
            'bucket' => env('S3_BUCKET'),
            'url' => env('S3_URL'),
            'endpoint' => env('S3_ENDPOINT'),
            'use_path_style_endpoint' => env('S3_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    'links' => $storageLinks,

];
