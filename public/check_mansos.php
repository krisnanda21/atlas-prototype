<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
use Illuminate\Support\Facades\DB;

$staff = DB::table('employees')
    ->where('role', 'staff')
    ->get();

$missing = [];
foreach ($staff as $emp) {
    $hasMansos = DB::table('competency_gaps')
        ->where('employee_id', $emp->id)
        ->where('type', 'Mansoskul')
        ->exists();
    if (!$hasMansos) {
        $missing[] = $emp->id . " (" . $emp->name . ") - " . $emp->unit;
    }
}
echo "Total staff: " . count($staff) . "\n";
echo "Staff missing Mansoskul in competency_gaps: " . count($missing) . "\n";
foreach(array_slice($missing, 0, 10) as $m) echo $m . "\n";
