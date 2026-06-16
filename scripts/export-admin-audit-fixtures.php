<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pick = static function (string $table, string $col = 'id') {
    try {
        return DB::table($table)->orderBy($col)->value($col);
    } catch (Throwable) {
        return null;
    }
};

$out = [
    'generated_at' => now()->toIso8601String(),
    'sample_ids' => [
        'blog' => $pick('cms_blogs'),
        'announcement' => $pick('cms_announcements'),
        'category' => $pick('cms_categories'),
        'doc_format' => $pick('document_number_formats'),
        'letter_format' => $pick('letter_number_formats'),
        'role' => DB::table('roles')->where('name', '!=', 'Superadmin')->orderBy('id')->value('id') ?: $pick('roles'),
        'user' => DB::table('users')->where('email', '!=', 'qa-superadmin-audit@example.test')->orderBy('id')->value('id') ?: $pick('users'),
        'jurusan' => DB::table('units')->where('type', 'JURUSAN')->orderBy('id')->value('id'),
        'prodi' => DB::table('units')->where('type', 'PRODI')->orderBy('id')->value('id'),
        'layanan' => $pick('services'),
        'request' => $pick('requests'),
    ],
    'counts' => [
        'cms_blogs' => DB::table('cms_blogs')->count(),
        'cms_announcements' => DB::table('cms_announcements')->count(),
        'cms_categories' => DB::table('cms_categories')->count(),
        'document_number_formats' => DB::table('document_number_formats')->count(),
        'letter_number_formats' => DB::table('letter_number_formats')->count(),
        'roles' => DB::table('roles')->count(),
        'users' => DB::table('users')->count(),
        'units' => DB::table('units')->count(),
        'services' => DB::table('services')->count(),
        'requests' => DB::table('requests')->count(),
    ],
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
