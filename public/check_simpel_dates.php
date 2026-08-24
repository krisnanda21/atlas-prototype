<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
use Illuminate\Support\Facades\DB;

$res = DB::table('simpel_t_diklat')->select('kode_pelatihan', 'nama_pelatihan', 'tanggal_mulai', 'tanggal_selesai')->limit(5)->get();
print_r($res);
