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

// 3. Tulis file firebase_credentials.json ke /tmp jika env string tersedia
if ($firebaseJson = getenv('FIREBASE_CREDENTIALS_JSON')) {
    $credentialsPath = '/tmp/firebase_credentials.json';
    file_put_contents($credentialsPath, $firebaseJson);
    putenv("FIREBASE_CREDENTIALS={$credentialsPath}");
    $_ENV['FIREBASE_CREDENTIALS'] = $credentialsPath;
    $_SERVER['FIREBASE_CREDENTIALS'] = $credentialsPath;
}

// 4. Handle Incoming Request (Kernel akan mem-bootstrap service providers & config)
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set compiled view path secara aman via instance config $app
$app['config']->set('view.compiled', '/tmp/storage/framework/views');
$app['config']->set('cache.stores.file.path', '/tmp/storage/framework/cache/data');

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
