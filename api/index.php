<?php

// 1. Load Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Initialize Laravel Application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Fix storage paths for Vercel Serverless Read-Only environment
$app->useStoragePath('/tmp/storage');

$storageDirs = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 4. Handle Incoming Request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
