<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExecutiveController extends Controller
{
    /**
     * Get units in scope for the active role.
     * Deputi: hanya unit di bawah scope-nya sendiri sesuai unit_kerja_relasi.csv.
     * Sesma/Sesma-level: semua unit (scope nasional).
     */
    private function getUnitsInScope()
    {
        return DB::table('employees')->distinct()->pluck('unit')->toArray();
    }

    /**
     * Resolve Eselon 1 code (D1–D6, SU) dari field scope user deputi.
     * Mendukung: kode langsung (D1, d2, dll), nama deputi, dan keyword.
     */
    private function resolveDeputiE1Code(?string $scope): ?string
    {
        if (empty($scope)) return null;

        $scopeTrimmed = trim($scope);
        $scopeUpper   = strtoupper($scopeTrimmed);
        $scopeLower   = strtolower($scopeTrimmed);

        // 1. Cocok langsung dengan kode E1 (D1–D6, SU)
        if (preg_match('/^(D[1-6]|SU)$/i', $scopeTrimmed, $m)) {
            return strtoupper($m[1]);
        }

        // 2. Kode ada di dalam scope string, mis. "Deputi D1" atau "(D1)"
        if (preg_match('/\b(D[1-6]|SU)\b/i', $scopeTrimmed, $m)) {
            return strtoupper($m[1]);
        }

        // 3. Keyword matching berdasarkan nama kedeputian (urutan penting: lebih spesifik duluan)
        $keywordMap = [
            'D6' => ['investigasi'],
            'D5' => ['akuntan negara'],
            'D4' => ['keuangan daerah', 'ppkd', 'penyelenggaraan keuangan daerah'],
            'D3' => ['pemberdayaan masyarakat', 'pangan'],
            'D2' => ['polhukam', 'politik', 'keamanan', 'hukum', 'pembangunan manusia', 'kebudayaan', 'pmk'],
            'D1' => ['perekonomian', 'infrastruktur', 'kewilayahan'],
            'SU' => ['sekretariat utama', 'sesma', 'sekretaris utama'],
        ];

        foreach ($keywordMap as $code => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($scopeLower, $kw)) {
                    return $code;
                }
            }
        }

        return null;
    }

    /**
     * Baca unit_kerja_relasi.csv dan kembalikan semua kode E2 di bawah $e1Code.
     */
    private function getChildCodesFromCsv(string $e1Code): array
    {
        $csvPaths = [
            base_path('../unit_kerja_relasi.csv'),
            'E:\\Anti Gravity\\ATLAS Prototype\\unit_kerja_relasi.csv',
            base_path('unit_kerja_relasi.csv'),
        ];

        foreach ($csvPaths as $csvPath) {
            if (!file_exists($csvPath)) continue;

            $fh = fopen($csvPath, 'r');
            if ($fh === false) continue;

            $codes = [];
            fgetcsv($fh); // skip header
            while (($row = fgetcsv($fh)) !== false) {
                if (count($row) >= 2 && strtoupper(trim($row[1])) === strtoupper($e1Code)) {
                    $codes[] = trim($row[0]);
                }
            }
            fclose($fh);

            return $codes; // Kembalikan hasil dari CSV pertama yang berhasil dibaca
        }

        return [];
    }

    /**
     * Konversi daftar kode E2/PW (mis. D101, PW01) ke nama unit.
     * Menggunakan eselon1Config children (Eselon 2 only) dan getPwNameMap sebagai sumber.
     * Nama Eselon 1 TIDAK dimasukkan — deputi melihat unit bawahannya, bukan unitnya sendiri.
     */
    private function resolveUnitNamesFromCodes(array $codes): array
    {
        // Bangun master map: kode => nama unit
        $codeToName = [];

        // Sumber 1: eselon1Config — HANYA children (Eselon 2), tidak termasuk nama E1
        $eselon1Config = $this->getEselon1Config();
        foreach ($eselon1Config as $e1Data) {
            foreach ($e1Data['children'] as $childCode => $childName) {
                $codeToName[strtoupper($childCode)] = $childName;
            }
        }

        // Sumber 2: Perwakilan name map (PW01–PW36)
        $pwNameMap = $this->getPwNameMap();
        foreach ($pwNameMap as $pwCode => $pwName) {
            $codeToName[strtoupper($pwCode)] = $pwName;
        }

        // Resolve nama untuk setiap kode dari CSV relasi
        $resolved = [];
        foreach ($codes as $code) {
            $upperCode = strtoupper($code);
            if (isset($codeToName[$upperCode])) {
                $resolved[] = $codeToName[$upperCode];
            }
        }

        return array_values(array_unique(array_filter($resolved)));
    }


    /**
     * Executive Insights.
     */
    public function insights(Request $request)
    {
        $units = $this->getUnitsInScope();

        // 1. KPI Cards
        // Card 1: % IDP Coverage
        $averageCoverage = DB::table('employees')->whereIn('unit', $units)->avg('idp_coverage') ?: 0;
        $averageCoverage = round($averageCoverage);

        // Card 2: % Pegawai Tidak Optimal & Kurang Optimal
        $totalEmp = DB::table('employees')->whereIn('unit', $units)->count();
        $suboptimalEmpCount = DB::table('competency_gaps')
            ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
            ->whereIn('employees.unit', $units)
            ->select('competency_gaps.employee_id')
            ->groupBy('competency_gaps.employee_id')
            ->havingRaw('AVG(competency_gaps.score) < AVG(competency_gaps.standard)')
            ->get()
            ->count();
        $suboptimalPercent = $totalEmp > 0 ? round(($suboptimalEmpCount / $totalEmp) * 100) : 0;

        // Card 3: Jenis Kompetensi Teknis dengan Gap Terbesar
        $largestTechGap = DB::table('competency_gaps')
            ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
            ->whereIn('employees.unit', $units)
            ->where('competency_gaps.type', 'Teknis')
            ->select('competency_gaps.competency_name', DB::raw('ROUND(GREATEST(0, AVG(competency_gaps.standard) - AVG(competency_gaps.score)), 1) as avg_gap'))
            ->groupBy('competency_gaps.competency_name')
            ->orderByDesc('avg_gap')
            ->first();
        $largestGapValue = $largestTechGap ? (float)$largestTechGap->avg_gap : 0;
        $largestGapName = $largestTechGap ? $largestTechGap->competency_name : '-';

        // Card 4: % Realisasi Bangkom Unit Kerja
        $totalBangkom = DB::table('bangkom_unit')->whereIn('unit_pengusul', $units)->count();
        $realisedBangkom = DB::table('bangkom_unit')
            ->whereIn('unit_pengusul', $units)
            ->whereIn('status', ['realisasi', 'persetujuan realisasi', 'realisasi disetujui', 'selesai'])
            ->count();
        $realisasiPercent = $totalBangkom > 0 ? round(($realisedBangkom / $totalBangkom) * 100) : 0;

        // 2. Charts Data
        // Unit Performance: Persentase Pegawai dengan Kompetensi Rendah per Unit
        $unitStatsPusat = [];
        $unitStatsPerwakilan = [];

        foreach ($units as $u) {
            $totalEmpU = DB::table('employees')->where('unit', $u)->count();
            $suboptimalEmpCountU = DB::table('competency_gaps')
                ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
                ->where('employees.unit', $u)
                ->select('competency_gaps.employee_id')
                ->groupBy('competency_gaps.employee_id')
                ->havingRaw('AVG(competency_gaps.score) < AVG(competency_gaps.standard)')
                ->get()
                ->count();
            $val = $totalEmpU > 0 ? round(($suboptimalEmpCountU / $totalEmpU) * 100) : 0;
            
            $code = $this->getUnitCode($u);
            $type = $this->getUnitType($u);

            $statItem = [
                'unit' => $u,
                'code' => $code,
                'type' => $type,
                'value' => $val,
            ];

            if ($type === 'perwakilan') {
                $unitStatsPerwakilan[] = $statItem;
            } else {
                $unitStatsPusat[] = $statItem;
            }
        }

        $eselon1Config = $this->getEselon1Config();
        $perwakilanWilayahConfig = $this->getPerwakilanWilayahConfig();
        $pwNameMap = $this->getPwNameMap();

        $statsByCode = [];
        $statsByUnit = [];
        foreach ($unitStatsPusat as $usp) {
            $statsByCode[$usp['code']] = $usp;
            $statsByUnit[$usp['unit']] = $usp;
        }

        $unitStatsPusatEselon1 = [];
        foreach ($eselon1Config as $e1Key => $e1Data) {
            $childrenStats = [];
            $valSum = 0;
            $valCount = 0;

            // Kumpulkan children dari dua sumber:
            // (a) exact match nama dari eselon1Config['children'] vs $units
            // (b) unit dari $unitStatsPusat yang kode-nya dimulai dengan prefix E1 ini (mis. D101→D1, SU01→SU)
            $processedUnits = [];

            // Sumber (a): children dari config yang ada di $units
            foreach ($e1Data['children'] as $childCode => $childName) {

                if (in_array($childName, $processedUnits)) continue;

                $childStat = $statsByCode[$childCode] ?? $statsByUnit[$childName] ?? null;

                if (!$childStat) {
                    $totalEmpU = DB::table('employees')->where('unit', $childName)->count();
                    $suboptimalEmpCountU = DB::table('competency_gaps')
                        ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
                        ->where('employees.unit', $childName)
                        ->select('competency_gaps.employee_id')
                        ->groupBy('competency_gaps.employee_id')
                        ->havingRaw('AVG(competency_gaps.score) < AVG(competency_gaps.standard)')
                        ->get()
                        ->count();
                    $val = $totalEmpU > 0 ? round(($suboptimalEmpCountU / $totalEmpU) * 100) : 0;
                    $risk = $val > 50 ? 'Tinggi' : ($val > 20 ? 'Sedang' : 'Rendah');
                    $childStat = ['unit' => $childName, 'code' => $childCode, 'type' => 'pusat', 'value' => $val, 'risk' => $risk];
                } else {
                    $childStat['risk'] = $childStat['value'] > 50 ? 'Tinggi' : ($childStat['value'] > 20 ? 'Sedang' : 'Rendah');
                }

                $processedUnits[] = $childName;
                $childrenStats[] = $childStat;
                $valSum += $childStat['value'];
                $valCount++;
            }

            // Sumber (b): unit dari $unitStatsPusat yang kode-nya ber-prefix E1 ini
            // (mis. kode 'D101' → prefix 'D1'; kode 'SU01' → prefix 'SU')
            foreach ($unitStatsPusat as $usp) {
                $unitCode = $usp['code'];
                if (in_array($usp['unit'], $processedUnits)) continue;

                // Cek apakah kode unit ini berada di bawah e1Key
                // Contoh: D1 → kode dimulai dengan D1 diikuti digit; SU → dimulai SU diikuti digit
                $isChildOfE1 = false;
                if ($e1Key === 'SU' && preg_match('/^SU\d+$/i', $unitCode)) {
                    $isChildOfE1 = true;
                } elseif ($e1Key === 'PST' && in_array($unitCode, ['DL', 'RJ', 'IP', 'JF'])) {
                    $isChildOfE1 = true;
                } elseif ($e1Key === 'IN' && $unitCode === 'IN') {
                    $isChildOfE1 = true;
                } elseif (preg_match('/^D(\d)/', $e1Key, $m1) && preg_match('/^D' . $m1[1] . '\d+$/i', $unitCode)) {
                    $isChildOfE1 = true;
                }

                if (!$isChildOfE1) continue;

                $childStat = $usp;
                $childStat['risk'] = $childStat['value'] > 50 ? 'Tinggi' : ($childStat['value'] > 20 ? 'Sedang' : 'Rendah');

                $processedUnits[] = $usp['unit'];
                $childrenStats[] = $childStat;
                $valSum += $childStat['value'];
                $valCount++;
            }



            if (empty($childrenStats)) continue;

            $e1Val = $valCount > 0 ? round($valSum / $valCount) : 0;
            $e1Risk = $e1Val > 50 ? 'Tinggi' : ($e1Val > 20 ? 'Sedang' : 'Rendah');

            $unitStatsPusatEselon1[] = [
                'code'     => $e1Data['code'],
                'name'     => $e1Data['name'],
                'short'    => $e1Data['short'],
                'value'    => $e1Val,
                'risk'     => $e1Risk,
                'children' => $childrenStats
            ];
        }

        $statsPwByCode = [];
        $statsPwByUnit = [];
        foreach ($unitStatsPerwakilan as $uspw) {
            $statsPwByCode[$uspw['code']] = $uspw;
            $statsPwByUnit[$uspw['unit']] = $uspw;
        }

        $unitStatsPerwakilanWilayah = [];
        foreach ($perwakilanWilayahConfig as $wilayahName => $wilayahData) {
            $childrenStats = [];
            $valSum = 0;
            $valCount = 0;

            foreach ($wilayahData['codes'] as $pwCode) {
                $pwUnitName = $pwNameMap[$pwCode] ?? $pwCode;
                


                $pwStat = $statsPwByCode[$pwCode] ?? $statsPwByUnit[$pwUnitName] ?? null;

                if (!$pwStat) {
                    $totalEmpU = DB::table('employees')->where('unit', $pwUnitName)->count();
                    $suboptimalEmpCountU = DB::table('competency_gaps')
                        ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
                        ->where('employees.unit', $pwUnitName)
                        ->select('competency_gaps.employee_id')
                        ->groupBy('competency_gaps.employee_id')
                        ->havingRaw('AVG(competency_gaps.score) < AVG(competency_gaps.standard)')
                        ->get()
                        ->count();
                    $val = $totalEmpU > 0 ? round(($suboptimalEmpCountU / $totalEmpU) * 100) : 0;
                    
                    $risk = $val > 50 ? 'Tinggi' : ($val > 20 ? 'Sedang' : 'Rendah');

                    $pwStat = [
                        'unit' => $pwUnitName,
                        'code' => $pwCode,
                        'type' => 'perwakilan',
                        'value' => $val,
                        'risk' => $risk
                    ];
                } else {
                    $pwStat['risk'] = $pwStat['value'] > 50 ? 'Tinggi' : ($pwStat['value'] > 20 ? 'Sedang' : 'Rendah');
                }

                $childrenStats[] = $pwStat;
                $valSum += $pwStat['value'];
                $valCount++;
            }

            if (empty($childrenStats)) continue;

            $wVal = $valCount > 0 ? round($valSum / $valCount) : 0;
            $wRisk = $wVal > 50 ? 'Tinggi' : ($wVal > 20 ? 'Sedang' : 'Rendah');

            $unitStatsPerwakilanWilayah[] = [
                'code' => $wilayahData['code'],
                'name' => $wilayahData['name'],
                'short' => $wilayahData['short'],
                'value' => $wVal,
                'risk' => $wRisk,
                'children' => $childrenStats
            ];
        }

        // Sort descending by value (higher suboptimal is worse)
        usort($unitStatsPusatEselon1, function($a, $b) {
            return $b['value'] <=> $a['value'];
        });
        usort($unitStatsPerwakilanWilayah, function($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        $avgPusatVal = count($unitStatsPusatEselon1) > 0 ? round(collect($unitStatsPusatEselon1)->avg('value')) : 0;
        $avgPerwakilanVal = count($unitStatsPerwakilanWilayah) > 0 ? round(collect($unitStatsPerwakilanWilayah)->avg('value')) : 0;

        // Gap Kompetensi (Side-Bar Chart)
        $techGaps = DB::table('competency_gaps')
            ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
            ->whereIn('employees.unit', $units)
            ->where('competency_gaps.type', 'Teknis')
            ->select('competency_gaps.competency_name', DB::raw('ROUND(GREATEST(0, AVG(competency_gaps.standard) - AVG(competency_gaps.score)), 1) as avg_gap'))
            ->groupBy('competency_gaps.competency_name')
            ->orderByDesc('avg_gap')
            ->get();

        // Strategic Analytics
        $strategicAnalyticsRaw = DB::table('ai_strategic_analytics')->get();
        $strategicAnalytics = [];
        $lastAiUpdate = null;
        
        if ($strategicAnalyticsRaw->count() > 0) {
            foreach ($strategicAnalyticsRaw as $sa) {
                $strategicAnalytics[] = [
                    'demand' => $sa->demand,
                    'count' => $sa->count,
                    'risk' => $sa->risk,
                    'decision' => $sa->decision
                ];
                $lastAiUpdate = $sa->created_at;
            }
        } else {
            // Fallback if AI hasn't run yet
            $strategicAnalytics = [
                [
                    'demand' => 'AI Analytics Belum Tersedia',
                    'count' => 0,
                    'risk' => 'Sedang',
                    'decision' => 'Tunggu scheduler harian AI berjalan atau trigger secara manual.'
                ]
            ];
        }

        // Sync monitoring datasets
        $datasets = [
            ['name' => 'SMILE (HR Master Data)', 'app' => 'SMILE', 'refresh' => '10 Aug 2026 08:00', 'status' => 'Sehat'],
            ['name' => 'COMPASS (Assessment)', 'app' => 'COMPASS', 'refresh' => '09 Aug 2026 17:30', 'status' => 'Sehat'],
            ['name' => 'SIMPEL (Nominasi Diklat)', 'app' => 'SIMPEL', 'refresh' => '10 Aug 2026 08:15', 'status' => 'Sehat'],
            ['name' => 'INTERNA (Katalog Diklat)', 'app' => 'INTERNA', 'refresh' => '10 Aug 2026 08:15', 'status' => 'Sehat']
        ];

        $user = Auth::user();
        $role = session('active_role', $user->role);
        $scopeLabel = ($user->scope && strtolower($user->scope) !== 'nasional' && !in_array($role, ['sesma', 'admin'])) 
            ? $user->scope 
            : 'Tingkat Nasional';

        $activeRole = $role;
        $unitStatsDeputi = [];

        if ($activeRole === 'deputi') {
            $e1Code = $this->resolveDeputiE1Code($user->scope);
            if ($e1Code) {
                $childE2Codes = $this->getChildCodesFromCsv($e1Code);
                $allowedUnits = $this->resolveUnitNamesFromCodes($childE2Codes);

                foreach ($allowedUnits as $uName) {
                    $totalEmpU = DB::table('employees')->where('unit', $uName)->count();
                    $suboptimalEmpCountU = DB::table('competency_gaps')
                        ->join('employees', 'competency_gaps.employee_id', '=', 'employees.id')
                        ->where('employees.unit', $uName)
                        ->select('competency_gaps.employee_id')
                        ->groupBy('competency_gaps.employee_id')
                        ->havingRaw('AVG(competency_gaps.score) < AVG(competency_gaps.standard)')
                        ->get()
                        ->count();
                    
                    $val = $totalEmpU > 0 ? round(($suboptimalEmpCountU / $totalEmpU) * 100) : 0;
                    $risk = $val > 50 ? 'Tinggi' : ($val > 20 ? 'Sedang' : 'Rendah');
                    $uCode = $this->getUnitCode($uName);
                    
                    $unitStatsDeputi[] = [
                        'unit' => $uName,
                        'code' => $uCode,
                        'value' => $val,
                        'risk' => $risk
                    ];
                }
                
                usort($unitStatsDeputi, function($a, $b) {
                    return $b['value'] <=> $a['value'];
                });
            }
        }

        return view('executive.insights', compact(
            'averageCoverage', 'suboptimalPercent', 'suboptimalEmpCount', 'totalEmp',
            'largestGapValue', 'largestGapName', 'realisasiPercent', 'realisedBangkom', 'totalBangkom',
            'unitStatsPusatEselon1', 'unitStatsPerwakilanWilayah', 'avgPusatVal', 'avgPerwakilanVal',
            'techGaps', 'strategicAnalytics', 'lastAiUpdate', 'datasets', 'scopeLabel',
            'activeRole', 'unitStatsDeputi'
        ));
    }

    /**
     * National Control Centre.
     */
    /**
     * Get reference map of unit name to unit code from unit_kerja_ref.csv.
     */
    private function getUnitRefMap(): array
    {
        static $refMap = null;
        if ($refMap !== null) {
            return $refMap;
        }

        $refMap = [];
        $csvPaths = [
            base_path('../unit_kerja_ref.csv'),
            base_path('unit_kerja_ref.csv'),
            'E:\\Anti Gravity\\ATLAS Prototype\\unit_kerja_ref.csv'
        ];

        foreach ($csvPaths as $path) {
            if (file_exists($path) && ($fh = fopen($path, 'r')) !== false) {
                fgetcsv($fh); // skip header (kode_unit, unitkerja)
                while (($row = fgetcsv($fh)) !== false) {
                    if (count($row) >= 2 && !empty(trim($row[0])) && !empty(trim($row[1]))) {
                        $code = trim($row[0]);
                        $unit = trim($row[1]);
                        $refMap[$unit] = $code;
                        
                        // Index normalized key for robust matching
                        $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $unit));
                        $refMap[$cleanKey] = $code;
                    }
                }
                fclose($fh);
                break;
            }
        }

        return $refMap;
    }

    /**
     * Map unit name to standard short unit code (Kode Unit Kerja) from unit_kerja_ref.csv.
     */
    private function getUnitCode(string $unitName): string
    {
        $refMap = $this->getUnitRefMap();

        // 1. Direct match from CSV
        if (isset($refMap[$unitName])) {
            return $refMap[$unitName];
        }

        // 2. Clean normalized match from CSV
        $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $unitName));
        if (isset($refMap[$cleanKey])) {
            return $refMap[$cleanKey];
        }

        // 3. Known aliases / slight variations against unit_kerja_ref.csv
        $aliases = [
            'Biro Manajemen Kinerja, Organisasi, dan Tata Kelola' => 'SU01',
            'Biro Sumber Daya Manusia' => 'SU02',
            'Biro Keuangan' => 'SU03',
            'Biro Hukum dan Komunikasi' => 'SU04',
            'Biro Umum dan Pengadaan Barang/Jasa' => 'SU05',
            'Biro Umum' => 'SU05',
            'Sekretariat Utama' => 'SU',
            'Inspektorat' => 'IN',
            'Pusat Pendidikan dan Pelatihan Pengawasan' => 'DL',
            'Pusat Informasi Pengawasan' => 'IP',
            'Pusat Pembinaan Jabatan Fungsional Auditor' => 'JF',
            'Pusat Strategi Kebijakan Pengawasan' => 'RJ',
            'Pusat Strategi dan Kebijakan Pengawasan' => 'RJ',
            'Direktorat Pengawasan Bidang Ekonomi dan Keuangan' => 'D101',
            'Direktorat Pengawasan Bidang Pertahanan dan Keamanan' => 'D201',
            'Direktorat Pengawasan Bidang Politik dan Penegakan Hukum' => 'D202',
            'Direktorat Pengawasan Bidang Kesehatan, Pemberdayaan Keluarga dan Bencana' => 'D203',
            'Direktorat Pengawasan Bidang Pembangunan Manusia dan Kebudayaan' => 'D204',
            'Direktorat Pengawasan Bidang Pengembangan Ilmu Pengetahuan, Teknologi, dan Reformasi Birokrasi' => 'D205',
            'Direktorat Pengawasan Bidang Sosial dan Pelindungan Pekerja Migran' => 'D301',
            'Direktorat Pengawasan Bidang Pemberdayaan Ekonomi Masyarakat' => 'D302',
            'Direktorat Pengawasan Bidang Pangan' => 'D303',
            'Direktorat Pengawasan Bidang Kehutanan dan Lingkungan Hidup' => 'D304',
            'Direktorat Pengawasan Akuntabilitas Keuangan Daerah' => 'D401',
            'Direktorat Pengawasan Akuntabilitas Program Lintas Sektoral Pembangunan Daerah' => 'D402',
            'Direktorat Pengawasan Akuntabilitas Keuangan, Pembangunan, dan Tata Kelola Pemerintahan Desa' => 'D403',
            'Direktorat Pengawasan Tata Kelola Pemerintah Daerah' => 'D404',
            'Direktorat Pengawasan Badan Usaha Agrobisnis dan Infrastruktur' => 'D501',
            'Direktorat Pengawasan Badan Usaha Konektivitas dan Pariwisata' => 'D502',
            'Direktorat Pengawasan Badan Usaha Jasa Keuangan dan Manufaktur' => 'D503',
            'Direktorat Pengawasan Badan Usaha Energi dan Pertambangan' => 'D504',
            'Direktorat Pengawasan Badan Layanan Umum, Badan Usaha Milik Daerah dan Desa' => 'D505',
            'Direktorat Investigasi I' => 'D601',
            'Direktorat Investigasi II' => 'D602',
            'Direktorat Investigasi III' => 'D603',
            'Direktorat Investigasi IV' => 'D604',
            'Direktorat Forensik Digital dan Analitika Data' => 'D605',
        ];
        if (isset($aliases[$unitName])) {
            return $aliases[$unitName];
        }

        // 4. Perwakilan matching using unit_kerja_ref.csv codes (PW01-PW36)
        if (stripos($unitName, 'perwakilan') !== false) {
            $pwProvinceKeywords = [
                'aceh' => 'PW01',
                'sumatera utara' => 'PW02', 'sumut' => 'PW02',
                'sumatera barat' => 'PW03', 'sumbar' => 'PW03',
                'riau' => 'PW04',
                'jambi' => 'PW05',
                'bengkulu' => 'PW06',
                'sumatera selatan' => 'PW07', 'sumsel' => 'PW07',
                'lampung' => 'PW08',
                'jakarta' => 'PW09', 'dki' => 'PW09',
                'jawa barat' => 'PW10', 'jabar' => 'PW10',
                'jawa tengah' => 'PW11', 'jateng' => 'PW11',
                'yogyakarta' => 'PW12', 'diy' => 'PW12',
                'jawa timur' => 'PW13', 'jatim' => 'PW13',
                'kalimantan barat' => 'PW14', 'kalbar' => 'PW14',
                'kalimantan tengah' => 'PW15', 'kalteng' => 'PW15',
                'kalimantan selatan' => 'PW16', 'kalsel' => 'PW16',
                'kalimantan timur' => 'PW17', 'kaltim' => 'PW17',
                'sulawesi utara' => 'PW18', 'sulut' => 'PW18',
                'sulawesi tengah' => 'PW19', 'sulteng' => 'PW19',
                'sulawesi tenggara' => 'PW20', 'sultra' => 'PW20',
                'sulawesi selatan' => 'PW21', 'sulsel' => 'PW21',
                'bali' => 'PW22',
                'nusa tenggara barat' => 'PW23', 'ntb' => 'PW23',
                'nusa tenggara timur' => 'PW24', 'ntt' => 'PW24',
                'maluku utara' => 'PW33', 'malut' => 'PW33',
                'maluku' => 'PW25',
                'papua barat daya' => 'PW35',
                'papua tengah' => 'PW36',
                'papua barat' => 'PW27',
                'papua' => 'PW26',
                'kepulauan riau' => 'PW28', 'kepri' => 'PW28',
                'bangka belitung' => 'PW29', 'babel' => 'PW29',
                'banten' => 'PW30',
                'gorontalo' => 'PW31',
                'sulawesi barat' => 'PW32', 'sulbar' => 'PW32',
                'kalimantan utara' => 'PW34', 'kaltara' => 'PW34',
            ];

            $low = strtolower($unitName);
            foreach ($pwProvinceKeywords as $provKeyword => $code) {
                if (str_contains($low, $provKeyword)) {
                    return $code;
                }
            }
        }

        // 5. Fallback acronym if not found
        $words = preg_split('/[\s,\-\/]+/', $unitName);
        $acronym = '';
        $ignore = ['dan', 'yang', 'di', 'ke', 'dari', 'untuk', 'pada', 'atau', 'bidang'];
        foreach ($words as $w) {
            $w = strtolower($w);
            if (!in_array($w, $ignore) && strlen($w) > 0) {
                $acronym .= strtoupper(substr($w, 0, 1));
            }
        }
        return substr($acronym, 0, 8) ?: 'UNIT';
    }

    /**
     * Determine unit classification: 'pusat' or 'perwakilan'.
     */
    private function getUnitType(string $unitName): string
    {
        if (stripos($unitName, 'perwakilan') !== false || stripos($unitName, 'pw-') === 0 || stripos($unitName, 'pw ') === 0) {
            return 'perwakilan';
        }
        return 'pusat';
    }

    /**
     * National Control Centre.
     */
    /**
     * National Control Centre.
     */
    public function nationalControlCentre(Request $request)
    {
        $units = $this->getUnitsInScope();
        $search = $request->query('q');
        
        $unitStats = [];
        $unitStatsPusat = [];
        $unitStatsPerwakilan = [];

        // 1. Process all active units
        foreach ($units as $u) {
            if ($search && stripos($u, $search) === false && stripos($this->getUnitCode($u), $search) === false) {
                continue;
            }

            $cov = DB::table('employees')->where('unit', $u)->avg('idp_coverage') ?: 0;
            $cov = round($cov);

            $nonJfaCount = DB::table('idp_items')
                ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
                ->where('employees.unit', $u)
                ->where('employees.category', 'Non-JFA')
                ->count();

            $stratCount = DB::table('strategic_directions')->where('unit', $u)->count();
            
            // Bangkom Unit (Rencana) & Bangkom Realisasi
            $bangkomCount = DB::table('bangkom_unit')->where('unit_pengusul', $u)->count();
            $bangkomRealCount = DB::table('bangkom_unit')->where('unit_pengusul', $u)->whereIn('status', ['realisasi', 'selesai'])->count();

            // Risk Assessment
            $risk = 'Rendah';
            if ($cov < 50) {
                $risk = 'Tinggi';
            } elseif ($cov < 75) {
                $risk = 'Sedang';
            }

            $code = $this->getUnitCode($u);
            $type = $this->getUnitType($u);

            $statItem = [
                'unit' => $u,
                'code' => $code,
                'type' => $type,
                'coverage' => $cov,
                'non_jfa_path' => $nonJfaCount,
                'strategic_direction' => $stratCount,
                'bangkom_count' => $bangkomCount,
                'bangkom_realisasi_count' => $bangkomRealCount,
                'risk' => $risk
            ];

            $unitStats[] = $statItem;
            if ($type === 'perwakilan') {
                $unitStatsPerwakilan[] = $statItem;
            } else {
                $unitStatsPusat[] = $statItem;
            }
        }

        // 2. Build Hierarchical Structure for Unit Kerja Pusat (Eselon 1 -> Eselon 2)
        $eselon1Config = $this->getEselon1Config();

        // Pre-index unitStatsPusat by unit name and code
        $statsByUnit = [];
        $statsByCode = [];
        foreach ($unitStatsPusat as $usp) {
            $statsByUnit[$usp['unit']] = $usp;
            $statsByCode[$usp['code']] = $usp;
        }

        $unitStatsPusatEselon1 = [];
        foreach ($eselon1Config as $e1Key => $e1Data) {
            $childrenStats = [];
            $covSum = 0;
            $covCount = 0;
            $e1NonJfa = 0;
            $e1Strat = 0;
            $e1Bangkom = 0;
            $e1BangkomReal = 0;
            $processedUnits = [];

            // Helper untuk build childStat dari DB langsung
            $buildChildStat = function(string $unitName, string $unitCode, string $unitType = 'pusat') use ($search, $e1Data): ?array {
                $cov = DB::table('employees')->where('unit', $unitName)->avg('idp_coverage') ?: 0;
                $cov = round($cov);
                $nonJfaCount = DB::table('idp_items')
                    ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
                    ->where('employees.unit', $unitName)
                    ->where('employees.category', 'Non-JFA')
                    ->count();
                $stratCount = DB::table('strategic_directions')->where('unit', $unitName)->count();
                $bCount = DB::table('bangkom_unit')->where('unit_pengusul', $unitName)->count();
                $bRealCount = DB::table('bangkom_unit')->where('unit_pengusul', $unitName)->whereIn('status', ['realisasi', 'selesai'])->count();
                $risk = $cov >= 75 ? 'Rendah' : ($cov >= 50 ? 'Sedang' : 'Tinggi');
                return [
                    'unit' => $unitName, 'code' => $unitCode, 'type' => $unitType,
                    'coverage' => $cov, 'non_jfa_path' => $nonJfaCount,
                    'strategic_direction' => $stratCount, 'bangkom_count' => $bCount,
                    'bangkom_realisasi_count' => $bRealCount, 'risk' => $risk
                ];
            };

            // Sumber (a): children dari config yang ada di $units
            foreach ($e1Data['children'] as $childCode => $childName) {

                if (in_array($childName, $processedUnits)) continue;

                $childStat = $statsByCode[$childCode] ?? $statsByUnit[$childName] ?? null;
                if (!$childStat) {
                    $childStat = $buildChildStat($childName, $childCode);
                }

                if ($search) {
                    $matchesParent = stripos($e1Data['name'], $search) !== false || stripos($e1Data['code'], $search) !== false;
                    $matchesChild = stripos($childStat['unit'], $search) !== false || stripos($childStat['code'], $search) !== false;
                    if (!$matchesParent && !$matchesChild) continue;
                }

                $processedUnits[] = $childName;
                $childrenStats[] = $childStat;
                $covSum += $childStat['coverage'];
                $covCount++;
                $e1NonJfa += $childStat['non_jfa_path'];
                $e1Strat += $childStat['strategic_direction'];
                $e1Bangkom += $childStat['bangkom_count'];
                $e1BangkomReal += $childStat['bangkom_realisasi_count'];
            }

            // Sumber (b): unit dari $unitStatsPusat yang kode-nya ber-prefix E1 ini
            foreach ($unitStatsPusat as $usp) {
                $unitCode = $usp['code'];
                if (in_array($usp['unit'], $processedUnits)) continue;

                $isChildOfE1 = false;
                if ($e1Key === 'SU' && preg_match('/^SU\d+$/i', $unitCode)) {
                    $isChildOfE1 = true;
                } elseif ($e1Key === 'PST' && in_array($unitCode, ['DL', 'RJ', 'IP', 'JF'])) {
                    $isChildOfE1 = true;
                } elseif ($e1Key === 'IN' && $unitCode === 'IN') {
                    $isChildOfE1 = true;
                } elseif (preg_match('/^D(\d)/', $e1Key, $m1) && preg_match('/^D' . $m1[1] . '\d+$/i', $unitCode)) {
                    $isChildOfE1 = true;
                }
                if (!$isChildOfE1) continue;

                $childStat = $usp;
                if ($search) {
                    $matchesParent = stripos($e1Data['name'], $search) !== false || stripos($e1Data['code'], $search) !== false;
                    $matchesChild = stripos($childStat['unit'], $search) !== false || stripos($childStat['code'], $search) !== false;
                    if (!$matchesParent && !$matchesChild) continue;
                }

                $processedUnits[] = $usp['unit'];
                $childrenStats[] = $childStat;
                $covSum += $childStat['coverage'];
                $covCount++;
                $e1NonJfa += $childStat['non_jfa_path'];
                $e1Strat += $childStat['strategic_direction'];
                $e1Bangkom += $childStat['bangkom_count'];
                $e1BangkomReal += $childStat['bangkom_realisasi_count'];
            }



            if (empty($childrenStats)) continue;

            $e1Coverage = $covCount > 0 ? round($covSum / $covCount) : 0;
            $e1Risk = $e1Coverage >= 75 ? 'Rendah' : ($e1Coverage >= 50 ? 'Sedang' : 'Tinggi');

            $unitStatsPusatEselon1[] = [
                'code' => $e1Data['code'],
                'name' => $e1Data['name'],
                'short' => $e1Data['short'],
                'coverage' => $e1Coverage,
                'non_jfa_path' => $e1NonJfa,
                'strategic_direction' => $e1Strat,
                'bangkom_count' => $e1Bangkom,
                'bangkom_realisasi_count' => $e1BangkomReal,
                'risk' => $e1Risk,
                'children' => $childrenStats
            ];
        }

        // 3. Build Hierarchical Structure for Kantor Perwakilan per Wilayah (Pulau)
        $perwakilanWilayahConfig = $this->getPerwakilanWilayahConfig();
        $pwNameMap = $this->getPwNameMap();

        // Pre-index unitStatsPerwakilan by code and name
        $statsPwByCode = [];
        $statsPwByUnit = [];
        foreach ($unitStatsPerwakilan as $uspw) {
            $statsPwByCode[$uspw['code']] = $uspw;
            $statsPwByUnit[$uspw['unit']] = $uspw;
        }

        $unitStatsPerwakilanWilayah = [];
        foreach ($perwakilanWilayahConfig as $wilayahName => $wilayahData) {
            $childrenStats = [];
            $covSum = 0;
            $covCount = 0;
            $wNonJfa = 0;
            $wStrat = 0;
            $wBangkom = 0;
            $wBangkomReal = 0;

            foreach ($wilayahData['codes'] as $pwCode) {
                $pwUnitName = $pwNameMap[$pwCode] ?? $pwCode;
                


                $pwStat = $statsPwByCode[$pwCode] ?? $statsPwByUnit[$pwUnitName] ?? null;

                if (!$pwStat) {
                    // Try DB search directly
                    $cov = DB::table('employees')->where('unit', $pwUnitName)->avg('idp_coverage') ?: 0;
                    $cov = round($cov);
                    $nonJfaCount = DB::table('idp_items')
                        ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
                        ->where('employees.unit', $pwUnitName)
                        ->where('employees.category', 'Non-JFA')
                        ->count();
                    $stratCount = DB::table('strategic_directions')->where('unit', $pwUnitName)->count();
                    $bCount = DB::table('bangkom_unit')->where('unit_pengusul', $pwUnitName)->count();
                    $bRealCount = DB::table('bangkom_unit')->where('unit_pengusul', $pwUnitName)->whereIn('status', ['realisasi', 'selesai'])->count();
                    
                    $risk = $cov >= 75 ? 'Rendah' : ($cov >= 50 ? 'Sedang' : 'Tinggi');

                    $pwStat = [
                        'unit' => $pwUnitName,
                        'code' => $pwCode,
                        'type' => 'perwakilan',
                        'coverage' => $cov,
                        'non_jfa_path' => $nonJfaCount,
                        'strategic_direction' => $stratCount,
                        'bangkom_count' => $bCount,
                        'bangkom_realisasi_count' => $bRealCount,
                        'risk' => $risk
                    ];
                }

                // Search filtering
                if ($search) {
                    $matchesWilayah = stripos($wilayahData['name'], $search) !== false || stripos($wilayahData['short'], $search) !== false;
                    $matchesChild = stripos($pwStat['unit'], $search) !== false || stripos($pwStat['code'], $search) !== false;
                    if (!$matchesWilayah && !$matchesChild) {
                        continue;
                    }
                }

                $childrenStats[] = $pwStat;
                $covSum += $pwStat['coverage'];
                $covCount++;
                $wNonJfa += $pwStat['non_jfa_path'];
                $wStrat += $pwStat['strategic_direction'];
                $wBangkom += $pwStat['bangkom_count'];
                $wBangkomReal += $pwStat['bangkom_realisasi_count'];
            }

            if (empty($childrenStats) && !$search) continue;
            if (empty($childrenStats)) continue;

            if ($search && empty($childrenStats)) {
                continue;
            }

            $wCoverage = $covCount > 0 ? round($covSum / $covCount) : 0;
            $wRisk = $wCoverage >= 75 ? 'Rendah' : ($wCoverage >= 50 ? 'Sedang' : 'Tinggi');

            $unitStatsPerwakilanWilayah[] = [
                'code' => $wilayahData['code'],
                'name' => $wilayahData['name'],
                'short' => $wilayahData['short'],
                'coverage' => $wCoverage,
                'non_jfa_path' => $wNonJfa,
                'strategic_direction' => $wStrat,
                'bangkom_count' => $wBangkom,
                'bangkom_realisasi_count' => $wBangkomReal,
                'risk' => $wRisk,
                'children' => $childrenStats
            ];
        }

        // 4. Build Structured Categories for Cascading Summary Table
        // Order: Kedeputian, Sesma, Pusat, Inspektorat, Kantor Perwakilan
        $tableCategories = [
            'kedeputian' => [
                'title' => 'Kedeputian',
                'badge' => 'Eselon I',
                'badge_class' => 'badge-info',
                'groups' => array_values(array_filter($unitStatsPusatEselon1, fn($e) => in_array($e['code'], ['D1', 'D2', 'D3', 'D4', 'D5', 'D6'])))
            ],
            'sesma' => [
                'title' => 'Sekretariat Utama (Sesma)',
                'badge' => 'Eselon I',
                'badge_class' => 'badge-info',
                'groups' => array_values(array_filter($unitStatsPusatEselon1, fn($e) => $e['code'] === 'SU'))
            ],
            'pusat' => [
                'title' => 'Pusat-Pusat',
                'badge' => 'Pusat',
                'badge_class' => 'badge-primary',
                'groups' => array_values(array_filter($unitStatsPusatEselon1, fn($e) => in_array($e['code'], ['Pusat', 'PST'])))
            ],
            'inspektorat' => [
                'title' => 'Inspektorat',
                'badge' => 'Inspektorat',
                'badge_class' => 'badge-neutral',
                'groups' => array_values(array_filter($unitStatsPusatEselon1, fn($e) => $e['code'] === 'IN'))
            ],
            'perwakilan' => [
                'title' => 'Kantor Perwakilan',
                'badge' => 'Wilayah Perwakilan',
                'badge_class' => 'badge-warning',
                'groups' => $unitStatsPerwakilanWilayah
            ],
        ];

        // Sort by coverage descending by default
        usort($unitStats, function($a, $b) {
            return $b['coverage'] <=> $a['coverage'];
        });
        usort($unitStatsPusat, function($a, $b) {
            return $b['coverage'] <=> $a['coverage'];
        });
        usort($unitStatsPusatEselon1, function($a, $b) {
            return $b['coverage'] <=> $a['coverage'];
        });
        usort($unitStatsPerwakilan, function($a, $b) {
            return $b['coverage'] <=> $a['coverage'];
        });
        usort($unitStatsPerwakilanWilayah, function($a, $b) {
            return $b['coverage'] <=> $a['coverage'];
        });

        // National stats
        $assessmentCount = DB::table('idp_items')->where('source', 'assessment-based')->count();
        $totalIdp = DB::table('idp_items')->count();
        $assessmentPercent = $totalIdp > 0 ? round(($assessmentCount / $totalIdp) * 100) : 0;
        $rolePercent = $totalIdp > 0 ? round((DB::table('idp_items')->whereIn('source', ['role-based', 'mandatory-based'])->count() / $totalIdp) * 100) : 0;
        $strategicPercent = $totalIdp > 0 ? round((DB::table('idp_items')->where('source', 'unit-strategic-direction-based')->count() / $totalIdp) * 100) : 0;

        $lowestUnit = empty($unitStats) ? '-' : end($unitStats)['unit'] . ' (' . end($unitStats)['coverage'] . '%)';

        // Average coverages for Pusat vs Perwakilan
        $avgPusat = count($unitStatsPusat) > 0 ? round(array_sum(array_column($unitStatsPusat, 'coverage')) / count($unitStatsPusat)) : 0;
        $avgPerwakilan = count($unitStatsPerwakilan) > 0 ? round(array_sum(array_column($unitStatsPerwakilan, 'coverage')) / count($unitStatsPerwakilan)) : 0;
        $user = Auth::user();
        $activeRole = session('active_role', $user->role);
        $deputiUnits = [];

        if ($activeRole === 'deputi') {
            $e1Code = $this->resolveDeputiE1Code($user->scope);
            if ($e1Code) {
                $childE2Codes = $this->getChildCodesFromCsv($e1Code);
                $allowedUnits = $this->resolveUnitNamesFromCodes($childE2Codes);

                foreach ($allowedUnits as $uName) {
                    $uCode = $this->getUnitCode($uName);
                    
                    if ($search) {
                        $matchesChild = stripos($uName, $search) !== false || stripos($uCode, $search) !== false;
                        if (!$matchesChild) continue;
                    }

                    $cov = DB::table('employees')->where('unit', $uName)->avg('idp_coverage') ?: 0;
                    $cov = round($cov);
                    $nonJfaCount = DB::table('idp_items')
                        ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
                        ->where('employees.unit', $uName)
                        ->where('employees.category', 'Non-JFA')
                        ->count();
                    $stratCount = DB::table('strategic_directions')->where('unit', $uName)->count();
                    $bCount = DB::table('bangkom_unit')->where('unit_pengusul', $uName)->count();
                    $bRealCount = DB::table('bangkom_unit')->where('unit_pengusul', $uName)->whereIn('status', ['realisasi', 'selesai'])->count();
                    
                    $risk = $cov >= 75 ? 'Rendah' : ($cov >= 50 ? 'Sedang' : 'Tinggi');
                    
                    $deputiUnits[] = [
                        'unit' => $uName,
                        'code' => $uCode,
                        'coverage' => $cov,
                        'non_jfa_path' => $nonJfaCount,
                        'strategic_direction' => $stratCount,
                        'bangkom_count' => $bCount,
                        'bangkom_realisasi_count' => $bRealCount,
                        'risk' => $risk
                    ];
                }
                
                usort($deputiUnits, function($a, $b) {
                    return $b['coverage'] <=> $a['coverage'];
                });
            }
        }

        return view('executive.ncc', compact(
            'unitStats',
            'unitStatsPusat',
            'unitStatsPusatEselon1',
            'unitStatsPerwakilan',
            'unitStatsPerwakilanWilayah',
            'tableCategories',
            'avgPusat',
            'avgPerwakilan',
            'assessmentPercent',
            'rolePercent',
            'strategicPercent',
            'lowestUnit',
            'search',
            'activeRole',
            'deputiUnits'
        ));
    }

    private function getEselon1Config()
    {
        return [
            'D1' => [
                'code' => 'D1',
                'name' => 'Deputi Bidang Pengawasan Instansi Pemerintah Bidang Perekonomian, Infrastruktur, dan Pembangunan Kewilayahan',
                'short' => 'Deputi Perekonomian, Infrastruktur & Kewilayahan (D1)',
                'children' => [
                    'D101' => 'Direktorat Pengawasan Bidang Ekonomi dan Keuangan',
                    'D102' => 'Direktorat Pengawasan Bidang Energi, Pariwisata, dan Pembangunan Kewilayahan',
                    'D103' => 'Direktorat Pengawasan Bidang Infrastruktur dan Perhubungan',
                    'D104' => 'Direktorat Pengawasan Bidang Perdagangan, Perindustrian dan Ketenagakerjaan',
                    'D105' => 'Direktorat Pengawasan Bidang Pembiayaan, Investasi, dan Kawasan',
                ]
            ],
            'D2' => [
                'code' => 'D2',
                'name' => 'Deputi Bidang Pengawasan Instansi Pemerintah Bidang Politik, Keamanan, Hukum, Pembangunan Manusia, dan Kebudayaan',
                'short' => 'Deputi Polhukam, PMK & Kebudayaan (D2)',
                'children' => [
                    'D201' => 'Direktorat Pengawasan Bidang Pertahanan dan Keamanan',
                    'D202' => 'Direktorat Pengawasan Bidang Politik dan Penegakan Hukum',
                    'D203' => 'Direktorat Pengawasan Bidang Kesehatan, Pemberdayaan Keluarga dan Bencana',
                    'D204' => 'Direktorat Pengawasan Bidang Pembangunan Manusia dan Kebudayaan',
                    'D205' => 'Direktorat Pengawasan Bidang Pengembangan Ilmu Pengetahuan, Teknologi, dan Reformasi Birokrasi',
                ]
            ],
            'D3' => [
                'code' => 'D3',
                'name' => 'Deputi Bidang Pengawasan Instansi Pemerintah Bidang Pemberdayaan Masyarakat dan Pangan',
                'short' => 'Deputi Pemberdayaan Masyarakat & Pangan (D3)',
                'children' => [
                    'D301' => 'Direktorat Pengawasan Bidang Sosial dan Pelindungan Pekerja Migran',
                    'D302' => 'Direktorat Pengawasan Bidang Pemberdayaan Ekonomi Masyarakat',
                    'D303' => 'Direktorat Pengawasan Bidang Pangan',
                    'D304' => 'Direktorat Pengawasan Bidang Kehutanan dan Lingkungan Hidup',
                ]
            ],
            'D4' => [
                'code' => 'D4',
                'name' => 'Deputi Bidang Pengawasan Penyelenggaraan Keuangan Daerah',
                'short' => 'Deputi Penyelenggaraan Keuangan Daerah (D4)',
                'children' => [
                    'D401' => 'Direktorat Pengawasan Akuntabilitas Keuangan Daerah',
                    'D402' => 'Direktorat Pengawasan Akuntabilitas Program Lintas Sektoral Pembangunan Daerah',
                    'D403' => 'Direktorat Pengawasan Akuntabilitas Keuangan, Pembangunan, dan Tata Kelola Pemerintahan Desa',
                    'D404' => 'Direktorat Pengawasan Tata Kelola Pemerintah Daerah',
                ]
            ],
            'D5' => [
                'code' => 'D5',
                'name' => 'Deputi Bidang Akuntan Negara',
                'short' => 'Deputi Akuntan Negara (D5)',
                'children' => [
                    'D501' => 'Direktorat Pengawasan Badan Usaha Agrobisnis dan Infrastruktur',
                    'D502' => 'Direktorat Pengawasan Badan Usaha Konektivitas dan Pariwisata',
                    'D503' => 'Direktorat Pengawasan Badan Usaha Jasa Keuangan dan Manufaktur',
                    'D504' => 'Direktorat Pengawasan Badan Usaha Energi dan Pertambangan',
                    'D505' => 'Direktorat Pengawasan Badan Layanan Umum, Badan Usaha Milik Daerah dan Desa',
                ]
            ],
            'D6' => [
                'code' => 'D6',
                'name' => 'Deputi Bidang Investigasi',
                'short' => 'Deputi Investigasi (D6)',
                'children' => [
                    'D601' => 'Direktorat Investigasi I',
                    'D602' => 'Direktorat Investigasi II',
                    'D603' => 'Direktorat Investigasi III',
                    'D604' => 'Direktorat Investigasi IV',
                    'D605' => 'Direktorat Forensik Digital dan Analitika Data',
                ]
            ],
            'SU' => [
                'code' => 'SU',
                'name' => 'Sekretariat Utama',
                'short' => 'Sekretariat Utama (Sesma)',
                'children' => [
                    'SU01' => 'Biro Manajemen Kinerja, Organisasi, dan Tata Kelola',
                    'SU02' => 'Biro Sumber Daya Manusia',
                    'SU03' => 'Biro Keuangan',
                    'SU04' => 'Biro Hukum dan Komunikasi',
                    'SU05' => 'Biro Umum dan Pengadaan Barang/Jasa',
                ]
            ],
            'PST' => [
                'code' => 'Pusat',
                'name' => 'Kantor Pusat',
                'short' => 'Pusat-Pusat',
                'children' => [
                    'DL' => 'Pusat Pendidikan dan Pelatihan Pengawasan',
                    'RJ' => 'Pusat Strategi dan Kebijakan Pengawasan',
                    'IP' => 'Pusat Informasi Pengawasan',
                    'JF' => 'Pusat Pembinaan Jabatan Fungsional Auditor',
                ]
            ],
            'IN' => [
                'code' => 'IN',
                'name' => 'Inspektorat',
                'short' => 'Inspektorat (IN)',
                'children' => [
                    'IN' => 'Inspektorat'
                ]
            ],
        ];
    }

    private function getPerwakilanWilayahConfig()
    {
        return [
            'Sumatra' => [
                'code' => 'SUMATRA',
                'name' => 'Wilayah Sumatra',
                'short' => 'Sumatra',
                'codes' => ['PW01', 'PW02', 'PW03', 'PW04', 'PW05', 'PW06', 'PW07', 'PW08', 'PW28', 'PW29'],
            ],
            'Jawa' => [
                'code' => 'JAWA',
                'name' => 'Wilayah Jawa',
                'short' => 'Jawa',
                'codes' => ['PW09', 'PW10', 'PW11', 'PW12', 'PW13', 'PW30'],
            ],
            'Kalimantan' => [
                'code' => 'KALIMANTAN',
                'name' => 'Wilayah Kalimantan',
                'short' => 'Kalimantan',
                'codes' => ['PW14', 'PW15', 'PW16', 'PW17', 'PW34'],
            ],
            'Sulawesi' => [
                'code' => 'SULAWESI',
                'name' => 'Wilayah Sulawesi',
                'short' => 'Sulawesi',
                'codes' => ['PW18', 'PW19', 'PW20', 'PW21', 'PW31', 'PW32'],
            ],
            'Nusa Bali' => [
                'code' => 'BALI_NUSA',
                'name' => 'Wilayah Bali & Nusa Tenggara',
                'short' => 'Nusa Bali',
                'codes' => ['PW22', 'PW23', 'PW24'],
            ],
            'Maluku' => [
                'code' => 'MALUKU',
                'name' => 'Wilayah Maluku',
                'short' => 'Maluku',
                'codes' => ['PW25', 'PW33'],
            ],
            'Papua' => [
                'code' => 'PAPUA',
                'name' => 'Wilayah Papua',
                'short' => 'Papua',
                'codes' => ['PW26', 'PW27', 'PW35', 'PW36'],
            ],
        ];
    }

    private function getPwNameMap()
    {
        return [
            'PW01' => 'Perwakilan BPKP Aceh',
            'PW02' => 'Perwakilan BPKP Provinsi Sumatera Utara',
            'PW03' => 'Perwakilan BPKP Provinsi Sumatera Barat',
            'PW04' => 'Perwakilan BPKP Provinsi Riau',
            'PW05' => 'Perwakilan BPKP Provinsi Jambi',
            'PW06' => 'Perwakilan BPKP Provinsi Bengkulu',
            'PW07' => 'Perwakilan BPKP Provinsi Sumatera Selatan',
            'PW08' => 'Perwakilan BPKP Provinsi Lampung',
            'PW09' => 'Perwakilan BPKP Provinsi Daerah Khusus Ibukota Jakarta',
            'PW10' => 'Perwakilan BPKP Provinsi Jawa Barat',
            'PW11' => 'Perwakilan BPKP Provinsi Jawa Tengah',
            'PW12' => 'Perwakilan BPKP Daerah Istimewa Yogyakarta',
            'PW13' => 'Perwakilan BPKP Provinsi Jawa Timur',
            'PW14' => 'Perwakilan BPKP Provinsi Kalimantan Barat',
            'PW15' => 'Perwakilan BPKP Provinsi Kalimantan Tengah',
            'PW16' => 'Perwakilan BPKP Provinsi Kalimantan Selatan',
            'PW17' => 'Perwakilan BPKP Provinsi Kalimantan Timur',
            'PW18' => 'Perwakilan BPKP Provinsi Sulawesi Utara',
            'PW19' => 'Perwakilan BPKP Provinsi Sulawesi Tengah',
            'PW20' => 'Perwakilan BPKP Provinsi Sulawesi Tenggara',
            'PW21' => 'Perwakilan BPKP Provinsi Sulawesi Selatan',
            'PW22' => 'Perwakilan BPKP Provinsi Bali',
            'PW23' => 'Perwakilan BPKP Provinsi Nusa Tenggara Barat',
            'PW24' => 'Perwakilan BPKP Provinsi Nusa Tenggara Timur',
            'PW25' => 'Perwakilan BPKP Provinsi Maluku',
            'PW26' => 'Perwakilan BPKP Provinsi Papua',
            'PW27' => 'Perwakilan BPKP Provinsi Papua Barat',
            'PW28' => 'Perwakilan BPKP Provinsi Kepulauan Riau',
            'PW29' => 'Perwakilan BPKP Provinsi Kepulauan Bangka Belitung',
            'PW30' => 'Perwakilan BPKP Provinsi Banten',
            'PW31' => 'Perwakilan BPKP Provinsi Gorontalo',
            'PW32' => 'Perwakilan BPKP Provinsi Sulawesi Barat',
            'PW33' => 'Perwakilan BPKP Provinsi Maluku Utara',
            'PW34' => 'Perwakilan BPKP Provinsi Kalimantan Utara',
            'PW35' => 'Perwakilan BPKP Provinsi Papua Barat Daya',
            'PW36' => 'Perwakilan BPKP Provinsi Papua Tengah',
        ];
    }

    /**
     * Talent Finder (National scope).
     */
    public function talentFinder(Request $request)
    {
        $units = $this->getUnitsInScope();
        $user = Auth::user();
        
        $type = $request->query('type');
        
        $kompetensiList = $request->query('kompetensi', []);
        $sertifikasiList = $request->query('sertifikasi', []);
        $jabatanList = $request->query('jabatan', []);
        $eselon1List = $request->query('eselon1', []);
        $eselon2List = $request->query('eselon2', []);

        $role = session('active_role', $user->role);
        $showEselon1 = false;
        $showEselon2 = false;
        $eselon1Options = [];
        $eselon2Options = [];

        $isKedeputian = preg_match('/^Deputi/i', $user->unit_eselon1 ?? '') || preg_match('/^D[1-6]/i', $user->unit_eselon1 ?? '') || preg_match('/^Deputi/i', $user->scope ?? '');

        if (in_array($role, ['sesma', 'karoSDM', 'kombinasi', 'bangkom'])) {
            $showEselon1 = true;
            $showEselon2 = true;
            $eselon1Options = DB::table('employees')->whereNotNull('unit_kerja_1')->where('unit_kerja_1', '!=', '')->distinct()->pluck('unit_kerja_1')->toArray();
            $eselon2Options = DB::table('employees')->whereNotNull('unit_kerja_2')->where('unit_kerja_2', '!=', '')->distinct()->pluck('unit_kerja_2')->toArray();
        } elseif ($role === 'deputi' || ($role === 'eselon2' && $isKedeputian) || ($role === 'eselon3' && $isKedeputian)) {
            $showEselon2 = true;
            $scopeToUse = $role === 'deputi' ? $user->scope : $user->unit_eselon1;
            $eselon2Options = \App\Helpers\UnitKerjaHelper::getSubUnits($scopeToUse);
        }
        
        $allCompetencies = \Illuminate\Support\Facades\DB::table('compass_nilai_teknis')->distinct()->pluck('kompetensi')->toArray();
        if (empty($allCompetencies)) {
            $allCompetencies = [
                'Analisis Data', 'Analisis Kebijakan Publik', 'Analisis Proses Bisnis', 
                'Fraud Risk Management', 'Governance, Risk, Control, and Compliance',
                'Keuangan Negara/Daerah dan Kekayaan yang Dipisahkan', 'Literasi Digital',
                'Manajemen dan Analisis Keuangan', 'Manajemen Penugasan Pengawasan Intern',
                'Manajemen Strategis Pemerintah', 'Metode dan Teknik Pengawasan Intern',
                'Pelaksanaan Pengawasan Intern', 'Standar Audit dan Kode Etik'
            ];
        }

        $certifications = DB::table('interna_rencana_diklat')
            ->where('jenis_pembelajaran', 'like', 'Sertifikasi%')
            ->where('status', 'Approved')
            ->distinct()
            ->pluck('program_pembelajaran')
            ->toArray();
        $availableUnits = DB::table('employees')->whereIn('unit', $units)->distinct()->pluck('unit')->toArray();

        $employees = [];
        if ($type) {
            $query = DB::table('employees')->whereIn('unit', $units);
                        if ($showEselon1 && !empty($eselon1List) && !in_array('All Eselon 1', $eselon1List)) {
                $query->whereIn('unit_kerja_1', $eselon1List);
            }

            if ($showEselon2 && !empty($eselon2List) && !in_array('All Eselon 2', $eselon2List)) {
                $query->whereIn('unit_kerja_2', $eselon2List);
            }

            if (!empty($jabatanList) && !in_array('All Jabatan', $jabatanList)) {
                $query->whereIn('jabatan', $jabatanList);
            }
            
            if ($type === 'Kompetensi' && !empty($kompetensiList)) {
                if (!in_array('All Kompetensi', $kompetensiList)) {
                    $query->whereExists(function($sub) use ($kompetensiList) {
                        $sub->select(DB::raw(1))
                            ->from('compass_nilai_teknis')
                            ->whereColumn('compass_nilai_teknis.employee_id', 'employees.id')
                            ->whereIn('compass_nilai_teknis.kompetensi', $kompetensiList);
                    });
                }
                
                $employees = $query->get()->map(function($emp) use ($kompetensiList) {
                    $nilaiQuery = DB::table('compass_nilai_teknis')->where('employee_id', $emp->id);
                    if (!in_array('All Kompetensi', $kompetensiList)) {
                        $nilaiQuery->whereIn('kompetensi', $kompetensiList);
                    }
                    $emp->rata_rata_nilai = round($nilaiQuery->avg('nilai'), 2);
                    return $emp;
                })->sortByDesc('rata_rata_nilai')->values();

            } elseif ($type === 'Sertifikasi' && !empty($sertifikasiList)) {
                $query->whereExists(function($sub) use ($sertifikasiList) {
                    $sub->select(DB::raw(1))
                        ->from('employee_sertifikasis')
                        ->whereColumn('employee_sertifikasis.employee_id', 'employees.id')
                        ->whereIn('employee_sertifikasis.nama_sertifikasi', $sertifikasiList);
                });
                $employees = $query->get();
                
                $empIds = $employees->pluck('id');
                
                $simpelData = DB::table('simpel_t_pendaftar')
                    ->join('simpel_t_diklat', 'simpel_t_pendaftar.kode_pelatihan', '=', 'simpel_t_diklat.kode_pelatihan')
                    ->whereIn('simpel_t_pendaftar.employee_id', $empIds)
                    ->select('simpel_t_pendaftar.employee_id', 'simpel_t_diklat.kode_pelatihan', 'simpel_t_diklat.nama_pelatihan')
                    ->get()
                    ->groupBy('employee_id');
                    
                $mappings = DB::table('certification_mappings')->get();
                
                $employees = $employees->filter(function($emp) use ($sertifikasiList, $simpelData, $mappings) {
                    $empSimpel = $simpelData->get($emp->id, collect());
                    if ($empSimpel->isEmpty()) return false;
                    
                    foreach ($sertifikasiList as $sertName) {
                        foreach ($empSimpel as $simpel) {
                            $mapped = $mappings->where('simpel_kode_pelatihan', $simpel->kode_pelatihan)
                                               ->where('smile_sertifikasi_name', $sertName)
                                               ->first();
                            if ($mapped) return true;
                            
                            if (strtolower(trim($sertName)) == strtolower(trim($simpel->nama_pelatihan))) {
                                return true;
                            }
                            
                            similar_text(strtolower(trim($sertName)), strtolower(trim($simpel->nama_pelatihan)), $percent);
                            if ($percent >= 80) {
                                return true;
                            }
                        }
                    }
                    return false;
                })->values();
            }
        }

        $employees = collect($employees);
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 25;
        $items = $employees->forPage($page, $perPage);
        $employeesPaginated = new \Illuminate\Pagination\LengthAwarePaginator($items, $employees->count(), $perPage, $page, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
        $employeesPaginated->withQueryString();
        $employees = $employeesPaginated;

        return view('executive.talent_finder', compact('employees', 'type', 'allCompetencies', 'certifications', 'availableUnits', 'showEselon1', 'showEselon2', 'eselon1Options', 'eselon2Options'));
    }

    /**
     * Report and Brief Generator.
     */
    public function reportGenerator(Request $request)
    {
        $briefContent = null;
        $reportType = $request->input('report_type');

        if ($reportType) {
            // Generate mock brief content based on report type
            if ($reportType === 'koordinator_monitoring') {
                $briefContent = "### LAPORAN MONITORING ORKESTRASI 3 STREAM\n\n"
                    . "**1. Stream Penilaian Kompetensi:**\n"
                    . "- Pegawai JFA yang dinilai di COMPASS: 100%\n"
                    . "- Pegawai Non-JFA yang dinilai: 0% (Masuk jalur Self-Assessment/Role-based)\n\n"
                    . "**2. Stream Pengembangan Kompetensi:**\n"
                    . "- Capaian IDP Coverage Nasional: **78%**\n"
                    . "- Kegiatan Bangkom Unit terealisasi: **12 program**\n\n"
                    . "**3. Stream Pembinaan SDM (Sertifikasi):**\n"
                    . "- Pegawai bersertifikat diunggah ke SMILE: **85%**\n"
                    . "- Kebutuhan program sertifikasi baru: CBDA, CFE.";
            } elseif ($reportType === 'karo_brief') {
                $briefContent = "### EXECUTIVE BRIEF KEPALA BIRO SDM\n\n"
                    . "**Prioritas Kebijakan Triwulan III 2026:**\n"
                    . "1. **Intervensi Unit Berisiko:** Unit Perwakilan Banten memiliki IDP Coverage terendah (55%). Perlu pendampingan pengusulan Bangkom Unit.\n"
                    . "2. **Standardisasi Program:** Optimalkan program mandiri 'Library Cafe' untuk pegawai Non-JFA Pelaksana.\n"
                    . "3. **Sinkronisasi SIMPEL:** Segera lakukan mapping kuota untuk Diklat Teknis JFA Audit PBJ.";
            } elseif ($reportType === 'risk_units') {
                $briefContent = "### ANALISIS UNIT KERJA BERISIKO TINGGI\n\n"
                    . "| Unit Kerja | IDP Coverage | Strategic Directions | Status Risiko |\n"
                    . "| --- | --- | --- | --- |\n"
                    . "| Perwakilan Banten | 55% | 1 Aktif | Tinggi |\n"
                    . "| Direktorat A | 60% | 0 Aktif | Sedang |\n"
                    . "| Direktorat B | 70% | 0 Aktif | Sedang |\n\n"
                    . "**Rekomendasi:** Berikan alokasi kuota prioritas bagi pegawai Perwakilan Banten pada Diklat Audit PBJ di Triwulan III.";
            } else {
                $briefContent = "### ANALISIS DEMAND-PROGRAM DAN COVERAGE JFA vs NON-JFA\n\n"
                    . "- **Pegawai JFA:** IDP Coverage rata-rata **82%** (Dominan assessment-based IDP).\n"
                    . "- **Pegawai Non-JFA:** IDP Coverage rata-rata **48%** (Dominan role-based / mandatory learning).\n\n"
                    . "**Gap Analisis:** Pegawai Non-JFA masih kekurangan wadah Bangkom Unit. Diperlukan penambahan webinar bertema Literasi Digital.";
            }
        }

        return view('executive.report_generator', compact('briefContent', 'reportType'));
    }

    /**
     * Mock XLSX Export.
     */
    public function exportXlsx()
    {
        // Simple CSV style response for XLSX mock download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ATLAS_Report_Export_' . date('Ymd') . '.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Unit Kerja', 'IDP Coverage (%)', 'Kategori', 'Strategic Directions', 'Risiko']);
            $units = \Illuminate\Support\Facades\DB::table('employees')
                ->select('unit', \Illuminate\Support\Facades\DB::raw('AVG(idp_coverage) as avg_coverage'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                ->groupBy('unit')
                ->get();
            foreach ($units as $u) {
                $risk = $u->avg_coverage >= 70 ? 'Rendah' : ($u->avg_coverage >= 55 ? 'Sedang' : 'Tinggi');
                fputcsv($file, [$u->unit, round($u->avg_coverage), 'JFA & Non-JFA', '-', $risk]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Sestama Brief page.
     */
    public function sestamaBrief(Request $request)
    {
        $briefContent = null;
        if ($request->isMethod('post')) {
            // Ambil data dinamis dari DB
            $avgCoverage = DB::table('employees')->avg('idp_coverage') ?? 0;
            $unitKritis  = DB::table('employees')
                ->select('unit', DB::raw('AVG(idp_coverage) as avg_cov'))
                ->groupBy('unit')
                ->orderBy('avg_cov')
                ->first();
            $unitKritisLabel = $unitKritis ? $unitKritis->unit . ' (' . round($unitKritis->avg_cov) . '% coverage)' : 'Tidak ada data';

            $briefContent = "### BRADING RAPAT PIMPINAN (SESTAMA BRIEF)\n\n"
                . "**Tanggal Dokumen:** " . date('d M Y') . "\n"
                . "**Aktor Pengusul:** Sekretaris Utama BPKP\n\n"
                . "**Ringkasan Kondisi Nasional:**\n"
                . "- **Rata-rata IDP Coverage BPKP:** " . round($avgCoverage) . "%.\n"
                . "- **Unit dengan coverage terendah:** " . $unitKritisLabel . ".\n"
                . "- **Kompetensi Kritis (Gap COMPASS Terbanyak):** Analisis Data dan Komunikasi Hasil.\n\n"
                . "**Rekomendasi Kebijakan:**\n"
                . "1. Prioritaskan bangkom unit bagi pegawai dengan IDP Coverage di bawah 60%.\n"
                . "2. Integrasikan dataset SETARA secara penuh sebelum akhir tahun anggaran untuk monitoring real-time.";
        }

        return view('executive.sestama_brief', compact('briefContent'));
    }

    /**
     * Deputi Eselon II Review page.
     */
    public function reviewEselon2(Request $request)
    {
        $user = Auth::user();
        $role = session('active_role', $user->role);
        
        $search = $request->query('q');
        
        $allowedUnits = [];
        if (in_array($role, ['deputi', 'sesma'])) {
            $e1Code = $role === 'sesma' ? 'SU' : $this->resolveDeputiE1Code($user->scope);
            if ($e1Code) {
                $childE2Codes = $this->getChildCodesFromCsv($e1Code);
                $allowedUnits = $this->resolveUnitNamesFromCodes($childE2Codes);
            }
        } else {
            $allowedUnits = $this->getUnitsInScope();
        }
        
        $eselon2Employees = DB::table('users')
            ->where('role', 'eselon2')
            ->whereIn('scope', $allowedUnits)
            ->pluck('nip')
            ->filter()
            ->toArray();

        $query = DB::table('idp_items')
            ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
            ->whereIn('employees.id', $eselon2Employees)
            ->where('idp_items.status', '!=', 'Draft')
            ->select('idp_items.*', 'employees.id as emp_id', 'employees.name as employee_name', 'employees.jabatan', 'employees.unit_kerja_2', 'employees.category as employee_category', 'employees.role as employee_role');

        if ($search) {
            $query->where(function($q) use ($search, $user) {
                $q->where('employees.name', 'LIKE', '%' . $search . '%')
                  ->orWhere('employees.id', 'LIKE', '%' . $search . '%')
                  ->orWhere('employees.jabatan', 'LIKE', '%' . $search . '%');
                
                $e1 = $user->unit_eselon1 ?? $user->scope ?? '';
                if (stripos($e1, 'Deput') === 0) {
                    $q->orWhere('employees.unit_kerja_2', 'LIKE', '%' . $search . '%');
                }
            });
        }

        $idps = $query->get();
        
        $groupedIdps = $idps->groupBy('emp_id')->map(function ($items, $empId) {
            $first = $items->first();
            return [
                'emp_id' => (string) $empId,
                'employee_name' => $first->employee_name,
                'jabatan' => $first->jabatan ?? $first->employee_role ?? '-',
                'unit_kerja_2' => $first->unit_kerja_2 ?? '-',
                'category' => $first->employee_category ?? '-',
                'total_idp' => $items->count(),
                'items' => $items->toArray()
            ];
        })->values();

        return view('executive.review_eselon2', compact('groupedIdps', 'search', 'user', 'allowedUnits'));
    }

    /**
     * Approve Eselon II IDP.
     */
    public function approveEselon2($id)
    {
        $idp = DB::table('idp_items')->where('id', $id)->first();
        if (!$idp) return back()->with('error', 'IDP tidak ditemukan.');

        DB::table('idp_items')->where('id', $id)->update([
            'status' => 'Disepakati',
            'updated_at' => now()
        ]);

        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Deputi Menyepakati IDP Eselon II',
            'object' => $idp->need,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'IDP Pejabat Eselon II berhasil disepakati.');
    }

    public function agreeBatchEselon2(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) return back()->with('error', 'Tidak ada IDP yang dipilih.');

        DB::table('idp_items')->whereIn('id', $ids)->update([
            'status' => 'Disepakati',
            'updated_at' => now()
        ]);

        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Deputi Menyepakati Batch IDP Eselon II',
            'object' => count($ids) . ' IDP items',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', count($ids) . ' IDP berhasil disepakati.');
    }

    public function agreeAllEselon2(Request $request)
    {
        $user = Auth::user();
        $role = session('active_role', $user->role);
        
        $allowedUnits = [];
        if (in_array($role, ['deputi', 'sesma'])) {
            $e1Code = $role === 'sesma' ? 'SU' : $this->resolveDeputiE1Code($user->scope);
            if ($e1Code) {
                $childE2Codes = $this->getChildCodesFromCsv($e1Code);
                $allowedUnits = $this->resolveUnitNamesFromCodes($childE2Codes);
            }
        } else {
            $allowedUnits = $this->getUnitsInScope();
        }

        $eselon2Employees = DB::table('users')
            ->where('role', 'eselon2')
            ->whereIn('scope', $allowedUnits)
            ->pluck('nip')
            ->filter()
            ->toArray();

        $affected = DB::table('idp_items')
            ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
            ->whereIn('employees.id', $eselon2Employees)
            ->where('idp_items.status', 'Diajukan')
            ->update([
                'idp_items.status' => 'Disepakati',
                'idp_items.updated_at' => now()
            ]);

        if ($affected > 0) {
            DB::table('audit_trails')->insert([
                'time' => now()->format('Y-m-d H:i:s'),
                'actor' => Auth::user()->name,
                'action' => 'Deputi Menyepakati Semua IDP Eselon II',
                'object' => $affected . ' IDP items',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', $affected . ' IDP yang diajukan berhasil disepakati.');
    }

    public function reviseEselon2(Request $request, $id)
    {
        $request->validate(['revision_note' => 'required|string']);

        $idp = DB::table('idp_items')->where('id', $id)->first();
        if (!$idp) return back()->with('error', 'IDP tidak ditemukan.');

        DB::table('idp_items')->where('id', $id)->update([
            'status' => 'Perlu Perbaikan',
            'revision_note' => $request->input('revision_note'),
            'updated_at' => now()
        ]);

        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Deputi Meminta Perbaikan IDP Eselon II',
            'object' => $idp->need,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Catatan perbaikan berhasil dikirim.');
    }

    /**
     * Profil 360 & Kompetensi Pegawai (National scope).
     */
    public function profil360(Request $request)
    {
        $units = $this->getUnitsInScope();
        $search = $request->query('q');
        
        $employeesQuery = DB::table('employees')->whereIn('unit', $units);
        if ($search) {
            $employeesQuery->where(function($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('id', 'LIKE', '%' . $search . '%')
                    ->orWhere('jabatan', 'LIKE', '%' . $search . '%')
                    ->orWhere('unit', 'LIKE', '%' . $search . '%');
            });
        }
        
        $employeesQuery->limit(100);
        $employees = $employeesQuery->get();
        $employee = $employees->first();


        $activeEmpId = $request->query('emp_id');
        if ($activeEmpId) {
            $employee = DB::table('employees')->where('id', $activeEmpId)->whereIn('unit', $units)->first();
        }

        $needs = [];
        $idpItems = [];
        $diklats = [];
        $sertifikasis = [];
        $bangkoms = [];
        if ($employee) {
            $needs = DB::table('competency_gaps')->where('employee_id', $employee->id)->get();
            $idpItems = DB::table('idp_items')->where('employee_id', $employee->id)->get();
            $diklats = DB::table('employee_diklats')->where('employee_id', $employee->id)->get();
            $sertifikasis = DB::table('employee_sertifikasis')->where('employee_id', $employee->id)->get();
            
            $bangkoms = DB::table('bangkom_unit_realisasi')
                ->join('bangkom_unit', 'bangkom_unit_realisasi.bangkom_unit_id', '=', 'bangkom_unit.id')
                ->where('bangkom_unit_realisasi.employee_id', $employee->id)
                ->select('bangkom_unit.*', 'bangkom_unit_realisasi.*')
                ->get();
        }

        return view('executive.profil_360', compact('employee', 'employees', 'needs', 'idpItems', 'search', 'diklats', 'sertifikasis', 'bangkoms'));
    }

    /**
     * Bangkom Nasional (View Only Rencana & Realisasi for Bangkom User)
     */
    public function bangkomNasional(Request $request)
    {
        $plans = DB::table('bangkom_unit')
            ->get()
            ->map(function ($plan) {
                $plan->realised_participants_count = DB::table('bangkom_unit_realisasi')
                    ->where('bangkom_unit_id', $plan->id)
                    ->count();

                $plan->participants = DB::table('bangkom_unit_realisasi')
                    ->join('employees', 'bangkom_unit_realisasi.employee_id', '=', 'employees.id')
                    ->where('bangkom_unit_realisasi.bangkom_unit_id', $plan->id)
                    ->select(
                        'bangkom_unit_realisasi.*',
                        'employees.name as employee_name',
                        'employees.id as employee_nip',
                        'employees.unit as employee_unit'
                    )
                    ->get();

                return $plan;
            });

        $employees = DB::table('employees')->get();

        $allCompetencies = [
            'Integritas', 'Kerja Sama', 'Komunikasi', 'Orientasi pada Hasil', 'Pelayanan Publik',
            'Pengembangan Diri & Orang Lain', 'Mengelola Perubahan', 'Pengambilan Keputusan', 'Perekat Bangsa',
            'Manajemen Pengawasan Intern', 'Standar Audit', 'Analisis Data', 'Audit PBJ', 'Fraud Risk Management',
            'Manajemen ASN', 'Literasi Digital', 'Keamanan Data Dasar'
        ];

        return view('executive.bangkom_nasional', compact('plans', 'employees', 'allCompetencies'));
    }
}
