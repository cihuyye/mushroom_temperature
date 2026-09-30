<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// 1. Set direktori Storage Laravel ke /tmp (Serverless Writable Area)
$app->useStoragePath('/tmp/storage');

// 2. Buat direktori yang dibutuhkan untuk Blade Compiled View & Cache
$storageDirs = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 3. Paksa Laravel Blade me-render compiled view di /tmp
config([
    'view.compiled' => '/tmp/storage/framework/views',
    'cache.stores.file.path' => '/tmp/storage/framework/cache/data',
]);

// 4. Jika menggunakan FIREBASE_CREDENTIALS_JSON dari Env, tulis ke /tmp
if ($firebaseJson = getenv('FIREBASE_CREDENTIALS_JSON')) {
    $credentialsPath = '/tmp/firebase_credentials.json';
    file_put_contents($credentialsPath, $firebaseJson);
    putenv("FIREBASE_CREDENTIALS={$credentialsPath}");
    $_ENV['FIREBASE_CREDENTIALS'] = $credentialsPath;
    $_SERVER['FIREBASE_CREDENTIALS'] = $credentialsPath;
}

// 5. Eksekusi Request Laravel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
