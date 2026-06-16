<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$r = \App\Models\Request::first();
if ($r) {
    // We found a request. Who does it belong to?
    $user = \App\Models\User::find($r->student_id);
    if ($user) {
        echo "Request ID: " . $r->id . "\n";
        echo "User Email: " . $user->email . "\n";
    } else {
        echo "No user for request\n";
    }
} else {
    echo "No requests found\n";
}
