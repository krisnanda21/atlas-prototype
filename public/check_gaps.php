<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
use Illuminate\Support\Facades\DB;

$empId = '196009222014072000';
$gaps = DB::table('competency_gaps')->where('employee_id', $empId)->get();
echo "Gaps for $empId:\n";
foreach ($gaps as $g) {
    echo $g->type . " - " . $g->competency_name . " (Score: " . $g->score . ")\n";
}
