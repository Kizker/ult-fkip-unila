<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
$users = User::all();
foreach($users as $u) {
    if (!str_starts_with($u->password, '$2y$')) {
        echo "Broken hash for {$u->email}: {$u->password}\n";
    }
}
echo "Done checking all users.\n";
