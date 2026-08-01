<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$entries = \Illuminate\Support\Facades\DB::table('md_project_user')->get();
foreach ($entries as $e) {
    echo "Project ID: {$e->project_id}, User ID: {$e->user_id}\n";
}
