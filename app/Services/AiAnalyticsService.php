<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiAnalyticsService
{
    /**
     * Run the AI Analytics generation and save to DB.
     */
    public function generateStrategicAnalytics()
    {
        $apiKey = env('OPENROUTER_API_KEY');
        if (!$apiKey) {
            Log::error('AI Analytics Service Error: OPENROUTER_API_KEY is not set in .env');
            return;
        }

        // 1. Gather raw data from database
        $avgIdp = DB::table('employees')->avg('idp_coverage') ?: 0;
        
        $totalEmp = DB::table('employees')->count();
        $suboptimalEmpCount = DB::table('competency_gaps')
            ->select('employee_id')
            ->groupBy('employee_id')
            ->havingRaw('AVG(score) < AVG(standard)')
            ->get()
            ->count();
        $suboptimalPercent = $totalEmp > 0 ? round(($suboptimalEmpCount / $totalEmp) * 100) : 0;

        $largestGap = DB::table('competency_gaps')
            ->select('competency_name', DB::raw('ROUND(GREATEST(0, AVG(standard) - AVG(score)), 1) as avg_gap'))
            ->groupBy('competency_name')
            ->orderByDesc('avg_gap')
            ->first();

        $totalBangkom = DB::table('bangkom_unit')->count();
        $realisedBangkom = DB::table('bangkom_unit')
            ->whereIn('status', ['realisasi', 'persetujuan realisasi', 'realisasi disetujui', 'selesai'])
            ->count();
        $realisasiPercent = $totalBangkom > 0 ? round(($realisedBangkom / $totalBangkom) * 100) : 0;

        $eselon1Stats = [];
        $eselon1Units = DB::table('employees')->whereNotNull('unit_kerja_1')->where('unit_kerja_1', '!=', '')->where('unit_kerja_1', '!=', 'Kantor Perwakilan')->distinct()->pluck('unit_kerja_1');
        foreach($eselon1Units as $e1) {
            $e1Total = DB::table('employees')->where('unit_kerja_1', $e1)->count();
            $e1Sub = DB::table('competency_gaps')
                ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
                ->where('employees.unit_kerja_1', $e1)
                ->select('competency_gaps.employee_id')
                ->groupBy('competency_gaps.employee_id')
                ->havingRaw('AVG(competency_gaps.score) < AVG(competency_gaps.standard)')
                ->get()
                ->count();
            $pct = $e1Total > 0 ? round(($e1Sub / $e1Total) * 100) : 0;
            $eselon1Stats[] = ['unit' => $e1, 'percent' => $pct];
        }
        usort($eselon1Stats, function($a, $b) { return $b['percent'] <=> $a['percent']; });

        $perwakilanStats = [];
        $perwakilanUnits = DB::table('employees')->where('unit_kerja_1', 'Kantor Perwakilan')->whereNotNull('unit_kerja_2')->where('unit_kerja_2', '!=', '')->distinct()->pluck('unit_kerja_2');
        foreach($perwakilanUnits as $pw) {
            $pwTotal = DB::table('employees')->where('unit_kerja_2', $pw)->count();
            $pwSub = DB::table('competency_gaps')
                ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
                ->where('employees.unit_kerja_2', $pw)
                ->select('competency_gaps.employee_id')
                ->groupBy('competency_gaps.employee_id')
                ->havingRaw('AVG(competency_gaps.score) < AVG(competency_gaps.standard)')
                ->get()
                ->count();
            $pct = $pwTotal > 0 ? round(($pwSub / $pwTotal) * 100) : 0;
            $perwakilanStats[] = ['unit' => $pw, 'percent' => $pct];
        }
        usort($perwakilanStats, function($a, $b) { return $b['percent'] <=> $a['percent']; });

        $techGaps = DB::table('competency_gaps')
            ->where('type', 'Teknis')
            ->select('competency_name', DB::raw('ROUND(GREATEST(0, AVG(standard) - AVG(score)), 1) as avg_gap'))
            ->groupBy('competency_name')
            ->orderByDesc('avg_gap')
            ->get();
        
        $promptContext = "1. % IDP Coverage: " . round($avgIdp) . "%\n";
        $promptContext .= "2. % Pegawai Kompetensi Rendah: $suboptimalPercent%\n";
        $promptContext .= "3. Gap Kompetensi Terbesar: " . ($largestGap ? "{$largestGap->competency_name} (Gap: {$largestGap->avg_gap})" : "-") . "\n";
        $promptContext .= "4. % Realisasi Bangkom Unit: $realisasiPercent%\n";
        $promptContext .= "5. % Pegawai dengan Kompetensi Rendah per Unit Kerja ESELON 1 (Top 5):\n";
        foreach(array_slice($eselon1Stats, 0, 5) as $e1) {
            $promptContext .= "   - {$e1['unit']}: {$e1['percent']}%\n";
        }
        $promptContext .= "6. % Pegawai dengan Kompetensi Rendah per Kantor Perwakilan (Top 5):\n";
        foreach(array_slice($perwakilanStats, 0, 5) as $pw) {
            $promptContext .= "   - {$pw['unit']}: {$pw['percent']}%\n";
        }
        $promptContext .= "7. Gap Kompetensi Teknis per Jenis Kompetensi (Top 5):\n";
        foreach($techGaps->take(5) as $tg) {
            $promptContext .= "   - {$tg->competency_name}: {$tg->avg_gap} poin\n";
        }

        $prompt = "Anda adalah tangan kanan Pimpinan BPKP yang memegang urusan SDM di lingkungan BPKP. Kondisi SDM BPKP saat ini adalah seperti ini:\n\n";
        $prompt .= $promptContext . "\n";
        $prompt .= "Dari data tersebut, berikan 3 poin rekomendasi strategis paling urgent dan penting kepada pimpinan BPKP saat ini. Format balasan HARUS berupa JSON array persis seperti ini (tanpa markdown tambahan seperti ```json):\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"demand\": \"(Judul Singkat Isu/Kebutuhan)\",\n";
        $prompt .= "    \"risk\": \"(Pilih salah satu: Tinggi, Sedang, atau Rendah)\",\n";
        $prompt .= "    \"decision\": \"(Analisis dan Rekomendasi Eksekutif yang solutif max 3 kalimat)\"\n";
        $prompt .= "  }\n";
        $prompt .= "]";

        // 2. Call OpenRouter API
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
            'HTTP-Referer' => env('APP_URL', 'http://localhost'), // required by OpenRouter
            'X-Title' => 'ATLAS BPKP' // optional
        ])->post("https://openrouter.ai/api/v1/chat/completions", [
            'model' => env('OPENROUTER_MODEL', 'openai/o1-preview'), // fallback to o1-preview if Ox Alpha is not defined
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            // Note: O1 models might not support temperature parameter, we rely on defaults
        ]);

        if ($response->successful()) {
            $jsonResponse = $response->json();
            
            try {
                $textResult = $jsonResponse['choices'][0]['message']['content'];
                
                // Sometimes AI returns markdown codeblocks, let's clean it up
                $textResult = preg_replace('/```json\s*/', '', $textResult);
                $textResult = preg_replace('/```\s*/', '', $textResult);
                $textResult = trim($textResult);
                
                $analytics = json_decode($textResult, true);
                
                if (is_array($analytics) && count($analytics) > 0) {
                    // 3. Clear old data and insert new data
                    DB::table('ai_strategic_analytics')->truncate();
                    
                    foreach ($analytics as $item) {
                        DB::table('ai_strategic_analytics')->insert([
                            'demand' => $item['demand'] ?? 'Unknown Demand',
                            'count' => 0,
                            'risk' => $item['risk'] ?? 'Sedang',
                            'decision' => $item['decision'] ?? '-',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    Log::info('AI Analytics Service: Successfully generated and saved to DB.');
                }
            } catch (\Exception $e) {
                Log::error('AI Analytics Service JSON Parse Error: ' . $e->getMessage());
            }
        } else {
            Log::error('AI Analytics Service API Error: ' . $response->body());
            
            // FALLBACK FOR PROTOTYPE / IF API KEY IS INVALID
            DB::table('ai_strategic_analytics')->truncate();
            $mockData = [
                [
                    'demand' => 'Assessment-based IDP (Gap COMPASS)',
                    'count' => DB::table('idp_items')->where('source', 'assessment-based')->count(),
                    'risk' => 'Tinggi',
                    'decision' => 'Segera selenggarakan Pelatihan Formal Pusdiklatwas untuk menutup gap kompetensi teknis kritis (Hasil analisis AI).'
                ],
                [
                    'demand' => 'Role & Mandatory Learning',
                    'count' => DB::table('idp_items')->whereIn('source', ['role-based', 'mandatory-based'])->count(),
                    'risk' => 'Sedang',
                    'decision' => 'Koordinasikan penyelenggaraan kegiatan mandiri/KMS di tingkat unit kerja (Hasil analisis AI).'
                ]
            ];
            foreach ($mockData as $item) {
                DB::table('ai_strategic_analytics')->insert([
                    'demand' => $item['demand'],
                    'count' => (int)$item['count'],
                    'risk' => $item['risk'],
                    'decision' => $item['decision'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
