<?php

header('Content-Type: text/html; charset=utf-8');

try {
    define('LARAVEL_START', microtime(true));

    // Sanitize key environment variables if variable names were pasted into Vercel value fields
    foreach (['APP_KEY', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $envKey) {
        $val = getenv($envKey) ?: ($_ENV[$envKey] ?? null);
        if ($val) {
            $cleaned = preg_replace('/^' . $envKey . '=/i', '', trim($val));
            $_ENV[$envKey] = $cleaned;
            $_SERVER[$envKey] = $cleaned;
            putenv($envKey . '=' . $cleaned);
        }
    }

    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        throw new Exception("Vendor autoloader not found at: " . realpath(__DIR__ . '/..') . "/vendor/autoload.php");
    }

    require $autoloadPath;

    // Prepare /tmp storage directories for Vercel serverless environment
    $storagePath = '/tmp/storage';
    $dirs = [
        $storagePath . '/framework/views',
        $storagePath . '/framework/cache',
        $storagePath . '/framework/cache/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/framework/testing',
        $storagePath . '/logs',
        $storagePath . '/app/public',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    // Run Laravel application
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Bind storage path to writable /tmp/storage
    $app->useStoragePath($storagePath);

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    $response->send();

    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h2>ReadyGrocery - Application Error</h2>';
    echo '<p><b>Message:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><b>File:</b> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
