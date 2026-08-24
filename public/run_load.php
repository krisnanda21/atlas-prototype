<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
try {
    ob_start();
    require __DIR__.'/load_compass_overwrite.php';
    ob_end_clean();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "LINE: " . $e->getLine() . "\n";
    echo "FILE: " . $e->getFile() . "\n";
}
