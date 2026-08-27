<?php

// Forward Vercel requests to public/index.php
define('LARAVEL_START', microtime(true));

// Register the Auto Loader
require __DIR__ . '/../vendor/autoload.php';

// Create storage directories in /tmp for Vercel Serverless environment
$tmpDirs = [
    '/tmp/framework/views',
    '/tmp/framework/cache',
    '/tmp/framework/sessions',
    '/tmp/logs',
];

foreach ($tmpDirs as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Run Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
