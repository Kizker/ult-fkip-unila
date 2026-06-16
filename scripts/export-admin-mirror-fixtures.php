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
        'feedback' => $pick('feedback_messages'),
        'user_guide' => $pick('user_guides'),
    ],
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
