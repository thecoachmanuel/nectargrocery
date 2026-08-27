<?php
// Standalone diagnostic - no Laravel dependency
// Visit: https://nectargrocery.vercel.app/diag

header('Content-Type: text/html; charset=utf-8');

echo '<html><head><title>ReadyGrocery - Diagnostics</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#00ff00;}
table{border-collapse:collapse;width:100%;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#222;}
.ok{color:#00ff00;} .fail{color:#ff4444;} .warn{color:#ffaa00;}
h2{color:#fff;border-bottom:1px solid #333;padding-bottom:5px;}
</style></head><body>';

echo '<h1>🔍 ReadyGrocery — Vercel Diagnostic</h1>';
echo '<p>Time: ' . date('Y-m-d H:i:s') . ' UTC</p>';

// PHP Info
echo '<h2>PHP Environment</h2>';
echo '<table>';
echo '<tr><th>Key</th><th>Value</th></tr>';
echo '<tr><td>PHP Version</td><td class="' . (version_compare(PHP_VERSION, '8.3', '>=') ? 'ok' : 'fail') . '">' . PHP_VERSION . '</td></tr>';
echo '<tr><td>SAPI</td><td>' . php_sapi_name() . '</td></tr>';
echo '<tr><td>OS</td><td>' . PHP_OS . '</td></tr>';
echo '<tr><td>Memory Limit</td><td>' . ini_get('memory_limit') . '</td></tr>';
echo '<tr><td>Max Execution Time</td><td>' . ini_get('max_execution_time') . '</td></tr>';
echo '</table>';

// Required PHP Extensions
echo '<h2>Required PHP Extensions</h2>';
echo '<table><tr><th>Extension</th><th>Status</th></tr>';
$required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'tokenizer', 
             'xml', 'ctype', 'fileinfo', 'curl', 'gd', 'zip', 'intl', 'bcmath'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    echo '<tr><td>' . $ext . '</td><td class="' . ($loaded ? 'ok' : 'fail') . '">' . ($loaded ? '✅ Loaded' : '❌ MISSING') . '</td></tr>';
}
echo '</table>';

// Environment Variables
echo '<h2>Environment Variables</h2>';
echo '<table><tr><th>Variable</th><th>Status</th></tr>';
$envVars = ['APP_KEY', 'APP_ENV', 'APP_URL', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 
            'DB_USERNAME', 'DB_PASSWORD', 'CACHE_DRIVER', 'SESSION_DRIVER', 'LOG_CHANNEL',
            'VIEW_COMPILED_PATH', 'MYSQL_ATTR_SSL_CA'];
foreach ($envVars as $var) {
    $val = getenv($var) ?: ($_ENV[$var] ?? null);
    if ($val) {
        // Mask sensitive values
        $display = in_array($var, ['APP_KEY', 'DB_PASSWORD']) ? substr($val, 0, 8) . '...(set)' : $val;
        echo '<tr><td>' . $var . '</td><td class="ok">✅ ' . htmlspecialchars($display) . '</td></tr>';
    } else {
        echo '<tr><td>' . $var . '</td><td class="fail">❌ NOT SET</td></tr>';
    }
}
echo '</table>';

// Filesystem
echo '<h2>Filesystem Writability</h2>';
echo '<table><tr><th>Path</th><th>Status</th></tr>';
$paths = ['/tmp', '/tmp/storage', '/tmp/storage/framework/views', '/tmp/storage/logs'];
foreach ($paths as $path) {
    if (!is_dir($path)) @mkdir($path, 0777, true);
    $writable = is_writable($path) || @file_put_contents($path . '/.test', 'x') !== false;
    @unlink($path . '/.test');
    echo '<tr><td>' . $path . '</td><td class="' . ($writable ? 'ok' : 'fail') . '">' . ($writable ? '✅ Writable' : '❌ Not Writable') . '</td></tr>';
}
echo '</table>';

// Database Connection Test
echo '<h2>Database Connection Test</h2>';
$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE');
$dbUser = getenv('DB_USERNAME');
$dbPass = getenv('DB_PASSWORD');

if ($dbHost && $dbUser) {
    try {
        $ssl = getenv('MYSQL_ATTR_SSL_CA');
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10];
        if ($ssl && $ssl !== 'true' && file_exists($ssl)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $ssl;
        } elseif ($ssl) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass, $options);
        echo '<p class="ok">✅ Database connected successfully to ' . htmlspecialchars($dbHost) . '</p>';
    } catch (PDOException $e) {
        echo '<p class="fail">❌ Database connection FAILED: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p class="warn">⚠️ DB_HOST or DB_USERNAME not set — skipping test</p>';
}

// Vendor autoload
echo '<h2>Vendor Autoload</h2>';
$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorPath)) {
    try {
        require $vendorPath;
        echo '<p class="ok">✅ vendor/autoload.php loaded successfully</p>';
        
        // Try loading Laravel
        $appPath = __DIR__ . '/../bootstrap/app.php';
        if (file_exists($appPath)) {
            try {
                $storagePath = '/tmp/storage';
                $dirs = [$storagePath.'/framework/views', $storagePath.'/framework/cache',
                         $storagePath.'/framework/cache/data', $storagePath.'/framework/sessions',
                         $storagePath.'/logs'];
                foreach ($dirs as $d) { if(!is_dir($d)) @mkdir($d, 0777, true); }
                
                $app = require $appPath;
                $app->useStoragePath($storagePath);
                echo '<p class="ok">✅ Laravel Application bootstrapped successfully!</p>';
            } catch (\Throwable $e) {
                echo '<p class="fail">❌ Laravel bootstrap FAILED: ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<pre class="fail">' . htmlspecialchars($e->getFile() . ':' . $e->getLine()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
        } else {
            echo '<p class="fail">❌ bootstrap/app.php not found</p>';
        }
    } catch (\Throwable $e) {
        echo '<p class="fail">❌ Autoload FAILED: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p class="fail">❌ vendor/autoload.php NOT FOUND — Composer dependencies not installed!</p>';
}

echo '</body></html>';
