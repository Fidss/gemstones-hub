<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Vercel specific configuration
if (isset($_SERVER['VERCEL_ENV'])) {
    // Ensure storage directory exists
    $storagePath = '/tmp/storage';
    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0755, true);
    }
    if (!is_dir($storagePath . '/framework')) {
        mkdir($storagePath . '/framework', 0755, true);
        mkdir($storagePath . '/framework/cache', 0755, true);
        mkdir($storagePath . '/framework/sessions', 0755, true);
        mkdir($storagePath . '/framework/views', 0755, true);
    }
    
    // Set storage path for Vercel
    $_ENV['APP_STORAGE_PATH'] = $storagePath;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);