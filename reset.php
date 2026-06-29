<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$u1 = User::where('email', 'mahasiswa@demo.test')->first();
if($u1) {
    $u1->password = Hash::make('password');
    $u1->save();
}

$u2 = User::where('email', 'superadmin@demo.test')->first();
if($u2) {
    $u2->password = Hash::make('password');
    $u2->save();
}

echo "Passwords reset successfully.\n";
