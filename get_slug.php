<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = \App\Models\Service::first();
if ($s) echo "Slug: " . $s->slug . "\n";
