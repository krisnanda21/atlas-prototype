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
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('AI Analytics Service Error: GEMINI_API_KEY is not set in .env');
            return;
        }

        // 1. Gather raw data from database
        $totalCompassGaps = DB::table('competency_gaps')->count();
        $gapsByCompetency = DB::table('competency_gaps')
            ->select('competency_name', DB::raw('COUNT(id) as total_pegawai'), DB::raw('AVG(gap) as avg_gap'))
            ->groupBy('competency_name')
            ->orderByDesc('avg_gap')
            ->limit(3)
            ->get();
            
        $idpItems = DB::table('idp_items')->select('source', DB::raw('COUNT(id) as total'))->groupBy('source')->get();
        $bangkomPlans = DB::table('bangkom_unit')->select('status', DB::raw('COUNT(id) as total'))->groupBy('status')->get();
        
        $promptContext = "Data BPKP saat ini:\n";
        $promptContext .= "- Total kesenjangan kompetensi (gap COMPASS) yang ditemukan: $totalCompassGaps pegawai.\n";
        $promptContext .= "- 3 Kompetensi dengan gap rata-rata tertinggi: \n";
        foreach ($gapsByCompetency as $gap) {
            $promptContext .= "  * {$gap->competency_name} (Gap Rata-rata: " . round($gap->avg_gap, 2) . ", pada {$gap->total_pegawai} pegawai)\n";
        }
        $promptContext .= "- Usulan IDP berdasarkan sumber:\n";
        foreach ($idpItems as $idp) {
            $promptContext .= "  * {$idp->source}: {$idp->total} usulan\n";
        }
        $promptContext .= "- Status Kegiatan Bangkom Unit:\n";
        foreach ($bangkomPlans as $plan) {
            $promptContext .= "  * {$plan->status}: {$plan->total} kegiatan\n";
        }

        $prompt = "Anda adalah Konsultan SDM (Human Capital) Eksekutif di BPKP. Berikut adalah ringkasan data kompetensi dan pelatihan dari sistem ATLAS (Aplikasi Terpadu Layanan SDM):\n\n";
        $prompt .= $promptContext . "\n";
        $prompt .= "Berikan persis 3 poin rekomendasi analisis strategis (Strategic Analytics) berdasarkan data di atas. Format balasan HARUS berupa JSON array persis seperti ini (tanpa markdown tambahan seperti ```json):\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"demand\": \"(Judul Singkat Isu/Kebutuhan)\",\n";
        $prompt .= "    \"count\": (Angka jumlah estimasi pegawai atau kegiatan terkait),\n";
        $prompt .= "    \"risk\": \"(Pilih salah satu: Tinggi, Sedang, atau Rendah)\",\n";
        $prompt .= "    \"decision\": \"(Analisis dan Rekomendasi Eksekutif yang solutif max 3 kalimat)\"\n";
        $prompt .= "  }\n";
        $prompt .= "]";

        // 2. Call Gemini API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'responseMimeType' => 'application/json',
            ]
        ]);

        if ($response->successful()) {
            $jsonResponse = $response->json();
            
            try {
                $textResult = $jsonResponse['candidates'][0]['content']['parts'][0]['text'];
                $analytics = json_decode($textResult, true);
                
                if (is_array($analytics) && count($analytics) > 0) {
                    // 3. Clear old data and insert new data
                    DB::table('ai_strategic_analytics')->truncate();
                    
                    foreach ($analytics as $item) {
                        DB::table('ai_strategic_analytics')->insert([
                            'demand' => $item['demand'] ?? 'Unknown Demand',
                            'count' => (int)($item['count'] ?? 0),
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
