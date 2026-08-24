<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
try {
    require __DIR__.'/load_compass_overwrite.php';
} catch (\Throwable $e) {
    echo "SHORT ERROR: " . substr($e->getMessage(), 0, 500) . "\n";
}
