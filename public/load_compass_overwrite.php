<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

ini_set('memory_limit', '1G');
set_time_limit(0);

try {
    DB::beginTransaction();

    // 1. Truncate tables
    DB::table('compass_nilai_teknis')->delete();
    DB::table('compass_nilai_mansos')->delete();
    DB::table('compass_nilai_rata_rata')->delete();
    DB::table('competency_gaps')->delete();

    // 2. Load compass_nilai_teknis
    $teknisPath = "E:\\Anti Gravity\\ATLAS Prototype\\dummy_data\\compass\\nilai_teknis_all.csv";
    $countTeknis = 0;
    $gapsToInsert = [];
    
    if (($handle = fopen($teknisPath, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",");
        $header[0] = trim(preg_replace('/[\xef\xbb\xbf]/', '', $header[0]));
        $teknisBatch = [];
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = array_combine($header, $data);
            
            $teknisBatch[] = [
                'employee_id' => $row['nip_baru'],
                'kompetensi' => $row['kompetensi'],
                'nilai' => $row['nilai'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $score = (float)$row['nilai'];
            $standard = 78;
            $gap = $score < $standard ? $standard - $score : 0;
            
            $level = 'Optimal';
            if ($gap > 0) {
                if ($gap <= 10) $level = 'Cukup optimal';
                elseif ($gap <= 20) $level = 'Kurang optimal';
                else $level = 'Tidak optimal';
            }
            
            $gapsToInsert[] = [
                'employee_id' => $row['nip_baru'],
                'type' => 'Teknis',
                'competency_name' => $row['kompetensi'],
                'score' => $score,
                'standard' => $standard,
                'gap' => $gap,
                'level' => $level,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $countTeknis++;
            if (count($teknisBatch) >= 500) {
                DB::table('compass_nilai_teknis')->insert($teknisBatch);
                $teknisBatch = [];
            }
        }
        if (count($teknisBatch) > 0) DB::table('compass_nilai_teknis')->insert($teknisBatch);
        fclose($handle);
    }

    // 3. Load compass_nilai_mansos
    $mansosPath = "E:\\Anti Gravity\\ATLAS Prototype\\dummy_data\\compass\\nilai_mansos_all.csv";
    $countMansos = 0;
    
    if (($handle = fopen($mansosPath, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",");
        $header[0] = trim(preg_replace('/[\xef\xbb\xbf]/', '', $header[0]));
        $mansosBatch = [];
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = array_combine($header, $data);
            
            $mansosBatch[] = [
                'employee_id' => $row['nip_baru'],
                'kompetensi' => $row['kompetensi'],
                'nilai' => $row['nilai'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $score = (float)$row['nilai'];
            $standard = 4;
            $gap = $score < $standard ? $standard - $score : 0;
            
            $level = 'Optimal';
            if ($gap > 0) {
                if ($gap == 1) $level = 'Cukup optimal';
                elseif ($gap == 2) $level = 'Kurang optimal';
                else $level = 'Tidak optimal';
            }
            
            $gapsToInsert[] = [
                'employee_id' => $row['nip_baru'],
                'type' => 'Mansoskul',
                'competency_name' => $row['kompetensi'],
                'score' => $score,
                'standard' => $standard,
                'gap' => $gap,
                'level' => $level,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $countMansos++;
            if (count($mansosBatch) >= 500) {
                DB::table('compass_nilai_mansos')->insert($mansosBatch);
                $mansosBatch = [];
            }
        }
        if (count($mansosBatch) > 0) DB::table('compass_nilai_mansos')->insert($mansosBatch);
        fclose($handle);
    }

    // Insert Gaps in chunks
    $chunks = array_chunk($gapsToInsert, 500);
    foreach ($chunks as $chunk) {
        DB::table('competency_gaps')->insert($chunk);
    }
    $countGaps = count($gapsToInsert);

    // 4. Load compass_nilai_rata_rata
    $rataPath = "E:\\Anti Gravity\\ATLAS Prototype\\dummy_data\\compass\\nilai_rata_rata_final.csv";
    $countRata = 0;
    
    if (($handle = fopen($rataPath, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",");
        $header[0] = trim(preg_replace('/[\xef\xbb\xbf]/', '', $header[0]));
        $rataBatch = [];
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = array_combine($header, $data);
            
            $rataBatch[] = [
                'employee_id' => $row['nip_baru'],
                'nilai_teknis' => $row['nilai_teknis'] ?: 0,
                'nilai_mansoskul' => $row['nilai_mansoskul'] ?: 0,
                'nilai_potensi' => $row['nilai_potensi'] ?: 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $countRata++;
            if (count($rataBatch) >= 500) {
                DB::table('compass_nilai_rata_rata')->insert($rataBatch);
                $rataBatch = [];
            }
        }
        if (count($rataBatch) > 0) DB::table('compass_nilai_rata_rata')->insert($rataBatch);
        fclose($handle);
    }

    DB::commit();
    echo "Successfully loaded data:\n";
    echo "- Teknis: $countTeknis\n";
    echo "- Mansos: $countMansos\n";
    echo "- Rata-rata: $countRata\n";
    echo "- Gaps Generated: $countGaps\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "Error: " . substr($e->getMessage(), 0, 500) . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
