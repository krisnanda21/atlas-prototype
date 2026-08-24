<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
use Illuminate\Support\Facades\DB;

$updated = DB::table('competency_gaps')
    ->where('type', 'Manajerial')
    ->update(['type' => 'Mansoskul']);

echo "Updated $updated rows to Mansoskul.\n";
