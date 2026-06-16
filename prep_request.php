<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$r = \App\Models\Request::latest()->first(); 
$r->status = 'menunggu_verifikasi'; 
$r->save(); 
$s = \App\Models\Signoff::firstOrCreate([
    'request_id' => $r->id, 
    'signer_id' => \App\Models\User::where('email', 'wadek1@demo.test')->first()->id
]); 
$s->status = 'pending'; 
$s->save();

echo "Set request {$r->id} to waiting for wadek1@demo.test\n";
