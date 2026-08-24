<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
use Illuminate\Support\Facades\DB;

$types = DB::table('competency_gaps')
    ->select('type', DB::raw('count(*) as count'))
    ->groupBy('type')
    ->get();

foreach ($types as $t) {
    echo $t->type . ": " . $t->count . "\n";
}
