<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = ['employees', 'employee_diklats', 'employee_sertifikasis'];
foreach ($tables as $t) {
    echo "Table: $t\n";
    print_r(Schema::getColumnListing($t));
    echo "\n";
}

function get_csv_headers($file) {
    if (!file_exists($file)) return ["File not found: $file"];
    $f = fopen($file, 'r');
    $h = fgetcsv($f);
    fclose($f);
    return $h;
}

echo "CSV 1 (employees):\n";
print_r(get_csv_headers('E:\\Anti Gravity\\ATLAS Prototype\\dummy_data\\smile\\data_dummy_pegawai.csv'));

echo "CSV 2 (diklat):\n";
print_r(get_csv_headers('E:\\Anti Gravity\\ATLAS Prototype\\dummy_data\\smile\\data_diklat2.csv'));

echo "CSV 3 (sertifikasi):\n";
print_r(get_csv_headers('E:\\Anti Gravity\\ATLAS Prototype\\dummy_data\\smile\\data_sertifikasi2.csv'));
