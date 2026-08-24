<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class IdpCoverageHelper
{
    /**
     * Calculate exact competency-matching IDP Coverage for an employee.
     * 
     * Formula:
     * True Coverage (%) = (Count(Target Gaps ∩ Agreed IDPs) / Count(Target Gaps)) * 100
     */
    public static function calculateEmployeeCoverage(string $employeeId): int
    {
        $detail = self::getEmployeeCoverageDetail($employeeId);
        return $detail['coverage_percent'];
    }

    /**
     * Get detailed breakdown of IDP Coverage for an employee.
     */
    public static function getEmployeeCoverageDetail(string $employeeId): array
    {
        $employee = DB::table('employees')->where('id', $employeeId)->first();
        if (!$employee) {
            return [
                'target_gaps' => [],
                'agreed_idps' => [],
                'matched_gaps' => [],
                'unmatched_gaps' => [],
                'enrichment_idps' => [],
                'coverage_percent' => 0,
            ];
        }

        // 1. Ambil target kebutuhan/gap kompetensi pegawai
        $targetGaps = [];

        // a. Gap dari COMPASS Assessment (level non-optimal)
        $gaps = DB::table('competency_gaps')
            ->where('employee_id', $employeeId)
            ->where('level', '!=', 'Optimal')
            ->pluck('competency_name')
            ->map(fn($v) => trim($v))
            ->toArray();
        $targetGaps = array_merge($targetGaps, $gaps);

        // b. Arahan Strategis Unit Kerja (Active)
        $strategic = DB::table('strategic_directions')
            ->where('unit', $employee->unit)
            ->where('status', '!=', 'Draft')
            ->pluck('competency')
            ->map(fn($v) => trim($v))
            ->toArray();
        $targetGaps = array_merge($targetGaps, $strategic);

        // c. Baseline Kebutuhan untuk Pegawai tanpa COMPASS / 0 Gap
        if (empty($targetGaps)) {
            if ($employee->category === 'Non-JFA') {
                $targetGaps = ['Manajemen dan Analisis Keuangan', 'Pelayanan Publik'];
            } elseif ($employee->category === 'Pelaksana') {
                $targetGaps = ['Literasi Digital', 'Integritas'];
            } else {
                $targetGaps = ['Standar Audit dan Kode Etik', 'Pelaksanaan Pengawasan Intern'];
            }
        }

        $targetGaps = array_values(array_unique(array_filter($targetGaps)));
        $totalTarget = count($targetGaps);

        // 2. Ambil kompetensi IDP pegawai yang sudah Disepakati
        $agreedIdps = DB::table('idp_items')
            ->where('employee_id', $employeeId)
            ->where('status', 'Disepakati')
            ->pluck('need')
            ->map(fn($v) => trim($v))
            ->unique()
            ->values()
            ->toArray();

        // 3. Exact Matching / Irisan Himpunan
        $matchedGaps = [];
        foreach ($targetGaps as $tg) {
            foreach ($agreedIdps as $ai) {
                if (strcasecmp($tg, $ai) === 0) {
                    $matchedGaps[] = $tg;
                    break;
                }
            }
        }
        $matchedGaps = array_values(array_unique($matchedGaps));

        // Unmatched Gaps (Gap yang belum dibuatkan IDP yang disepakati)
        $unmatchedGaps = array_values(array_diff($targetGaps, $matchedGaps));

        // Enrichment / Inisiatif Mandiri (IDP yang disepakati di luar target gap)
        $enrichmentIdps = [];
        foreach ($agreedIdps as $ai) {
            $isGap = false;
            foreach ($targetGaps as $tg) {
                if (strcasecmp($tg, $ai) === 0) {
                    $isGap = true;
                    break;
                }
            }
            if (!$isGap) {
                $enrichmentIdps[] = $ai;
            }
        }

        $coveragePercent = $totalTarget > 0 
            ? (int)round((count($matchedGaps) / $totalTarget) * 100)
            : 100;
        
        $coveragePercent = min(100, max(0, $coveragePercent));

        return [
            'target_gaps' => $targetGaps,
            'agreed_idps' => $agreedIdps,
            'matched_gaps' => $matchedGaps,
            'unmatched_gaps' => $unmatchedGaps,
            'enrichment_idps' => $enrichmentIdps,
            'coverage_percent' => $coveragePercent,
            'total_target' => $totalTarget,
            'matched_count' => count($matchedGaps),
        ];
    }

    /**
     * Calculate average IDP Coverage for a specific unit.
     */
    public static function calculateUnitCoverage(string $unitName): float
    {
        $avg = DB::table('employees')->where('unit', $unitName)->avg('idp_coverage');
        return $avg !== null ? round((float)$avg, 1) : 0.0;
    }

    /**
     * Sync and update `idp_coverage` column for a specific employee.
     */
    public static function syncEmployeeCoverage(string $employeeId): int
    {
        $cov = self::calculateEmployeeCoverage($employeeId);
        DB::table('employees')->where('id', $employeeId)->update([
            'idp_coverage' => $cov,
            'updated_at' => now(),
        ]);
        return $cov;
    }

    /**
     * Batch sync `idp_coverage` for all employees across the entire organization.
     */
    public static function syncAllEmployeesCoverage(): int
    {
        $employees = DB::table('employees')->select('id')->get();
        $count = 0;

        foreach ($employees as $emp) {
            $cov = self::calculateEmployeeCoverage($emp->id);
            DB::table('employees')->where('id', $emp->id)->update([
                'idp_coverage' => $cov,
            ]);
            $count++;
        }

        return $count;
    }
}
