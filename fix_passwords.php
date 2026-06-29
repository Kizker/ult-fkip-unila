<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$users = User::whereIn('email', ['mahasiswa@demo.test', 'superadmin@demo.test'])->get();
foreach($users as $u) {
    $u->password = Hash::make('Password!2345');
    $u->save();
}
echo "Passwords fixed successfully.\n";
