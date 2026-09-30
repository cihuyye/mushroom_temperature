<?php

require __DIR__ . '/../vendor/autoload.php';

// Fix Environment Variables & Storage Directory untuk Vercel Serverless
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

// Tulis credentials file dari env jika ada
if ($firebaseJson = getenv('FIREBASE_CREDENTIALS_JSON')) {
    $credentialsPath = '/tmp/firebase_credentials.json';
    file_put_contents($credentialsPath, $firebaseJson);
    putenv("FIREBASE_CREDENTIALS={$credentialsPath}");
    $_ENV['FIREBASE_CREDENTIALS'] = $credentialsPath;
}

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Set storage ke /tmp
$app->useStoragePath('/tmp/storage');

// Buat direktori yang dibutuhkan secara otomatis
$storageDirs = [
    '/tmp/storage/app',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Bootstrap & Run Kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
