<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$entries = DB::table('md_project_user')->get();
foreach ($entries as $e) {
    echo "Project ID: {$e->project_id}, User ID: {$e->user_id}\n";
}
