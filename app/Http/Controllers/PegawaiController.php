<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    /**
     * Get active employee instance.
     */
    private function getActiveEmployee($request)
    {
        $user = Auth::user();

        // Cek session jika ada employee yang dipilih secara manual
        $activeEmpId = session('active_employee_id');

        if ($activeEmpId) {
            $employee = DB::table('employees')->where('id', $activeEmpId)->first();
            if ($employee) return $employee;
        }

        // Fallback: cari employee berdasarkan nama user yang login
        if ($user->nip) {
            $employee = DB::table('employees')->where('id', $user->nip)->first();
            if ($employee) return $employee;
        }

        // Cari berdasarkan kecocokan nama
        $employee = DB::table('employees')
            ->whereRaw('LOWER(name) = ?', [strtolower($user->name)])
            ->first();
        if ($employee) return $employee;

        // Fallback: ambil employee pertama dari unit yang sama
        $employee = DB::table('employees')
            ->where('unit', $user->scope)
            ->first();

        return $employee;
    }

    /**
     * Dashboard Pegawai.
     */
    public function dashboard(Request $request)
    {
        $employee = $this->getActiveEmployee($request);
        
        // Count IDP statuses
        $myIdps = DB::table('idp_items')->where('employee_id', $employee->id)->get();
        $draftCount = $myIdps->where('status', 'Draft')->count();
        $agreedCount = $myIdps->where('status', 'Disepakati')->count();
        
        // Recommendations (up to 3)
        $recommendations = $this->getRecommendations($employee);
        $existingNeeds = $myIdps->pluck('need')->toArray();
        $recommendations = array_filter($recommendations, function($rec) use ($existingNeeds) {
            return !in_array($rec['need'], $existingNeeds);
        });
        $recCount = count($recommendations);
        $recommendations = array_slice(array_values($recommendations), 0, 3);

        // Load COMPASS average scores
        $compassAverage = DB::table('compass_nilai_rata_rata')
            ->where('employee_id', $employee->id)
            ->first();

        // Load detailed competency gaps for spider charts
        $gaps = DB::table('competency_gaps')
            ->where('employee_id', $employee->id)
            ->get();
            
        $techGaps = $gaps->where('type', 'Teknis');
        $mansosGaps = $gaps->where('type', 'Mansoskul');

        // IDP Coverage Detail based on Exact Matching
        $coverageDetail = \App\Helpers\IdpCoverageHelper::getEmployeeCoverageDetail($employee->id);
        $idpCoverage = $coverageDetail['coverage_percent'];

        return view('pegawai.dashboard', compact('employee', 'draftCount', 'agreedCount', 'recommendations', 'recCount', 'compassAverage', 'techGaps', 'mansosGaps', 'coverageDetail', 'idpCoverage'));
    }

    /**
     * Profil Kompetensi & IDP 360.
     */
    public function profil360(Request $request)
    {
        $user = Auth::user();
        
        // Find employee by NIP or name match
        $employee = null;
        if ($user->nip) {
            $employee = DB::table('employees')->where('id', $user->nip)->first();
        }
        if (!$employee) {
            $employee = DB::table('employees')
                ->whereRaw('LOWER(name) = ?', [strtolower($user->name)])
                ->first();
        }
        if (!$employee) {
            $employee = DB::table('employees')
                ->where('unit', $user->scope)
                ->first();
        }

        $needs = $employee ? DB::table('competency_gaps')
            ->where('employee_id', $employee->id)
            ->orderBy('score', 'asc')
            ->get() : collect();
        $idpItems = $employee ? DB::table('idp_items')->where('employee_id', $employee->id)->get() : collect();
        $diklats = $employee ? DB::table('employee_diklats')->where('employee_id', $employee->id)->get() : collect();
        $sertifikasis = $employee ? DB::table('employee_sertifikasis')->where('employee_id', $employee->id)->get() : collect();
        $coverageDetail = $employee ? \App\Helpers\IdpCoverageHelper::getEmployeeCoverageDetail($employee->id) : null;
        $search = null;

        return view('pegawai.profil_360', compact('employee', 'needs', 'idpItems', 'search', 'diklats', 'sertifikasis', 'coverageDetail'));
    }

    /**
     * Helper to get competency type (Teknis vs Manajerial) based on daftar_kompetensi.xlsx
     */
    public function getCompetencyType(string $competencyName): string
    {
        $mansosList = [
            'Integritas',
            'Kerja Sama',
            'Kerja sama',
            'Komunikasi',
            'Orientasi pada Hasil',
            'Pelayanan Publik',
            'Pengembangan Diri & Orang Lain',
            'Pengembangan Diri dan Orang Lain',
            'Mengelola Perubahan',
            'Pengambilan Keputusan',
            'Perekat Bangsa',
            'Karakteristik Lintas Manajerial',
            'Kepemimpinan',
            'Komunikasi dalam Peran sebagai Trusted Advisor dan Value Driver',
        ];

        foreach ($mansosList as $m) {
            if (strcasecmp($m, trim($competencyName)) === 0 || stripos($competencyName, $m) !== false) {
                return 'Manajerial';
            }
        }

        return 'Teknis';
    }

    /**
     * Susun IDP Saya.
     */
    public function susunIdp(Request $request)
    {
        $employee = $this->getActiveEmployee($request);
        
        // Search employee query for context
        $search = $request->query('q');
        if ($search) {
            $found = DB::table('employees')
                ->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('id', 'LIKE', '%' . $search . '%')
                ->first();
            if ($found) {
                session(['active_employee_id' => $found->id]);
                $employee = $found;
            }
        }

        $myIdps = DB::table('idp_items')
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['Draft', 'Diajukan', 'Disepakati', 'Perlu Perbaikan'])
            ->get();

        $recommendations = $this->getRecommendations($employee);
        $existingNeeds = $myIdps->pluck('need')->toArray();
        $recommendations = array_filter($recommendations, function($rec) use ($existingNeeds) {
            return !in_array($rec['need'], $existingNeeds);
        });
        $recommendations = array_values($recommendations);

        // Competencies strictly based on dummy_data/compass/daftar_kompetensi.xlsx
        $competenciesTeknis = [
            'Analisis Data',
            'Analisis Proses Bisnis',
            'Fraud Risk Management',
            'Governance, Risk, Control, and Compliance',
            'Keuangan Negara/Daerah dan Kekayaan yang Dipisahkan',
            'Literasi Digital',
            'Manajemen dan Analisis Keuangan',
            'Manajemen Penugasan Pengawasan Intern',
            'Manajemen Strategis Pemerintah',
            'Metode dan Teknik Pengawasan Intern',
            'Pelaksanaan Pengawasan Intern',
            'Standar Audit dan Kode Etik',
            'Analisis Kebijakan Publik',
        ];

        $competenciesMansos = [
            'Integritas',
            'Kerja Sama',
            'Komunikasi',
            'Orientasi pada Hasil',
            'Pelayanan Publik',
            'Pengembangan Diri & Orang Lain',
            'Mengelola Perubahan',
            'Pengambilan Keputusan',
            'Perekat Bangsa',
            'Karakteristik Lintas Manajerial',
            'Kepemimpinan',
            'Komunikasi dalam Peran sebagai Trusted Advisor dan Value Driver',
        ];

        return view('pegawai.susun_idp', compact('employee', 'recommendations', 'myIdps', 'competenciesTeknis', 'competenciesMansos', 'search'));
    }

    /**
     * Add new IDP Item.
     */
    public function storeIdp(Request $request)
    {
        $request->validate([
            'source' => 'required|string',
            'need' => 'required|string',
            'basis' => 'required|string',
            'priority' => 'required|string',
            'competency_type' => 'nullable|string'
        ]);

        $employee = $this->getActiveEmployee($request);
        $id = 'I' . substr(time(), -5);

        // Auto-calculate priority based on COMPASS score if available
        $priority = $request->priority;
        $gap = DB::table('competency_gaps')
            ->where('employee_id', $employee->id)
            ->where('competency_name', $request->need)
            ->first();
        
        if ($gap) {
            if ($gap->score < 60) {
                $priority = 'Tinggi';
            } elseif ($gap->score < 78) {
                $priority = 'Sedang';
            } else {
                $priority = 'Rendah';
            }
        }

        $competencyType = $request->competency_type ?: $this->getCompetencyType($request->need);

        DB::table('idp_items')->insert([
            'id' => $id,
            'employee_id' => $employee->id,
            'need' => $request->need,
            'competency_type' => $competencyType,
            'source' => $request->source,
            'basis' => $request->basis,
            'priority' => $priority,
            'status' => 'Draft',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Log to Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('d M Y H:i'),
            'actor' => Auth::user()->name,
            'action' => 'Tambah IDP',
            'object' => $request->need . ' (Draft)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Draft IDP berhasil disimpan.');
    }

    /**
     * Submit draft IDP to Head of Unit.
     */
    public function submitIdp($id)
    {
        $idp = DB::table('idp_items')->where('id', $id)->first();
        if (!$idp) {
            return back()->with('error', 'IDP tidak ditemukan.');
        }

        DB::table('idp_items')->where('id', $id)->update([
            'status' => 'Diajukan',
            'updated_at' => now()
        ]);

        // Log to Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('d M Y H:i'),
            'actor' => Auth::user()->name,
            'action' => 'Mengajukan IDP',
            'object' => $idp->need,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'IDP berhasil diajukan ke Kepala Unit Eselon II.');
    }

    /**
     * Submit multiple draft IDPs to Head of Unit simultaneously.
     */
    public function submitIdpBatch(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Pilih minimal satu draft IDP untuk diajukan.');
        }

        $items = DB::table('idp_items')
            ->whereIn('id', $ids)
            ->whereIn('status', ['Draft', 'Perlu Perbaikan'])
            ->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Tidak ada draft IDP yang dapat diajukan dari pilihan Anda.');
        }

        $validIds = $items->pluck('id')->toArray();

        DB::table('idp_items')->whereIn('id', $validIds)->update([
            'status' => 'Diajukan',
            'updated_at' => now()
        ]);

        foreach ($items as $item) {
            DB::table('audit_trails')->insert([
                'time' => now()->format('d M Y H:i'),
                'actor' => Auth::user()->name,
                'action' => 'Mengajukan IDP',
                'object' => $item->need . ' (Batch)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', count($validIds) . ' item IDP berhasil diajukan sekaligus ke Kepala Unit Eselon II.');
    }

    /**
     * Delete draft IDP item.
     */
    public function deleteIdp($id)
    {
        $idp = DB::table('idp_items')->where('id', $id)->first();
        if (!$idp) {
            return back()->with('error', 'IDP tidak ditemukan.');
        }

        if (!in_array($idp->status, ['Draft', 'Perlu Perbaikan'])) {
            return back()->with('error', 'Hanya draft IDP atau IDP yang memerlukan perbaikan yang dapat dihapus.');
        }

        DB::table('idp_items')->where('id', $id)->delete();

        // Sync IDP Coverage
        \App\Helpers\IdpCoverageHelper::syncEmployeeCoverage($idp->employee_id);

        // Log to Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('d M Y H:i'),
            'actor' => Auth::user()->name,
            'action' => 'Menghapus Draft IDP',
            'object' => $idp->need,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Draft IDP berhasil dihapus.');
    }

    /**
     * Update draft/revision IDP item.
     */
    public function updateIdp(Request $request, $id)
    {
        $request->validate([
            'need' => 'required|string',
            'source' => 'required|string',
            'basis' => 'required|string',
            'priority' => 'required|string',
            'competency_type' => 'nullable|string',
        ]);

        $idp = DB::table('idp_items')->where('id', $id)->first();
        if (!$idp) {
            return back()->with('error', 'IDP tidak ditemukan.');
        }

        if (!in_array($idp->status, ['Draft', 'Perlu Perbaikan'])) {
            return back()->with('error', 'Hanya draft IDP atau IDP yang memerlukan perbaikan yang dapat diubah.');
        }

        $competencyType = $request->competency_type ?: $this->getCompetencyType($request->need);

        DB::table('idp_items')->where('id', $id)->update([
            'need' => $request->need,
            'competency_type' => $competencyType,
            'source' => $request->source,
            'basis' => $request->basis,
            'priority' => $request->priority,
            'updated_at' => now()
        ]);

        // Log to Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('d M Y H:i'),
            'actor' => Auth::user()->name,
            'action' => 'Mengubah Draft IDP',
            'object' => $request->need,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Draft IDP berhasil diperbarui.');
    }

    /**
     * Kalender Saya.
     */
    public function kalender(Request $request)
    {
        $employee = $this->getActiveEmployee($request);
        
        $plans = DB::table('bangkom_unit')->get();
        $events = [];
        $today = date('Y-m-d');
        
        foreach ($plans as $plan) {
            if ($employee->unit !== $plan->unit_pengusul) {
                continue;
            }
            
            $statusLower = strtolower($plan->status);
            $show = false;
            if ($statusLower === 'pembatalan disetujui' || $statusLower === 'draft') {
                $show = false;
            } elseif (in_array($statusLower, ['ditetapkan', 'pembatalan diajukan', 'realisasi', 'persetujuan realisasi', 'realisasi disetujui', 'selesai', 'menunggu penetapan'])) {
                $show = true;
            } elseif ($plan->tanggal_selesai < $today) {
                $show = true;
            }
            
            if ($show) {
                $color = '#f59e0b';
                $desc = 'Pelatihan Formal';
                
                $sourceLower = strtolower($plan->jenis_latar_belakang);
                if (str_contains($sourceLower, 'mandatory') || str_contains($sourceLower, 'mandiri')) {
                    $color = '#10b981';
                    $desc = 'Mandiri / KMS';
                } elseif (str_contains($sourceLower, 'strategic') || str_contains($sourceLower, 'arahan strategis')) {
                    $color = '#3b82f6';
                    $desc = 'Bangkom Unit';
                }
                
                // Add 1 day to end date for FullCalendar's exclusive end date logic
                $endDate = date('Y-m-d', strtotime($plan->tanggal_selesai . ' +1 day'));
                
                $events[] = [
                    'id' => $plan->id,
                    'title' => $plan->nama_kegiatan,
                    'start' => $plan->tanggal_mulai,
                    'end' => $endDate,
                    'color' => $color,
                    'description' => $desc . ' (' . ucfirst($plan->status) . ')',
                    'extendedProps' => [
                        'jenis' => $desc,
                        'metode' => $plan->metode ?? '-',
                        'jp' => $plan->jp ?? '-',
                        'tanggal' => date('d M Y', strtotime($plan->tanggal_mulai)) . ' - ' . date('d M Y', strtotime($plan->tanggal_selesai)),
                        'status' => ucfirst($plan->status)
                    ]
                ];
            }
        }
        
        // Load SIMPEL Diklat events
        $diklats = DB::table('simpel_t_diklat')->get();
        foreach ($diklats as $diklat) {
            $endDate = date('Y-m-d', strtotime($diklat->tanggal_selesai . ' +1 day'));
            
            $events[] = [
                'id' => $diklat->kode_pelatihan,
                'title' => $diklat->nama_pelatihan,
                'start' => $diklat->tanggal_mulai,
                'end' => $endDate,
                'color' => '#8b5cf6', // Distinct purple color for Diklat SIMPEL
                'description' => 'Diklat SIMPEL',
                'extendedProps' => [
                    'jenis' => 'Diklat SIMPEL',
                    'metode' => $diklat->jenis_pelatihan ?? '-',
                    'jp' => $diklat->jam_pelatihan ?? '-',
                    'tanggal' => date('d M Y', strtotime($diklat->tanggal_mulai)) . ' - ' . date('d M Y', strtotime($diklat->tanggal_selesai)),
                    'status' => 'Tersedia',
                    'kuota' => $diklat->jumlah_kuota ?? '-',
                    'syarat' => $diklat->syarat_jabatan ?? '-'
                ]
            ];
        }
        
        return view('pegawai.kalender', compact('employee', 'events'));
    }

    /**
     * Riwayat Pengembangan.
     */
    public function riwayat(Request $request)
    {
        $employee = $this->getActiveEmployee($request);
        
        $riwayats = [
            ['tahun' => '2025', 'kegiatan' => 'Audit PBJ', 'jenis' => 'Pelatihan formal', 'jp' => '32', 'status' => 'Lulus'],
            ['tahun' => '2026', 'kegiatan' => 'Library Cafe Data', 'jenis' => 'Bangkom Unit', 'jp' => '3', 'status' => 'Selesai'],
            ['tahun' => '2026', 'kegiatan' => 'KMS Manajemen ASN', 'jenis' => 'Mandiri', 'jp' => '3', 'status' => 'Berjalan']
        ];

        return view('pegawai.riwayat', compact('employee', 'riwayats'));
    }

    /**
     * Helper to get system recommendations.
     */
    private function getRecommendations($employee)
    {
        $arr = [];
        if ($employee->assessment) {
            // Gap from COMPASS
            $gaps = DB::table('competency_gaps')
                ->where('employee_id', $employee->id)
                ->where('level', '!=', 'Optimal') // only recommend gaps
                ->get();
            foreach ($gaps as $gap) {
                $priority = 'Rendah';
                if ($gap->level === 'Tidak optimal') {
                    $priority = 'Tinggi';
                } elseif ($gap->level === 'Kurang optimal') {
                    $priority = 'Sedang';
                } elseif ($gap->level === 'Cukup optimal') {
                    $priority = 'Rendah';
                }
                
                $competencyType = ($gap->type === 'Manajerial' || $gap->type === 'Sosial Kultural') ? 'Manajerial' : 'Teknis';

                $arr[] = [
                    'source' => 'assessment-based',
                    'need' => $gap->competency_name,
                    'competency_type' => $competencyType,
                    'basis' => 'Gap COMPASS: ' . $gap->competency_name,
                    'priority' => $priority
                ];
            }
        } else {
            // Non-assessment categories (Analis, Pelaksana)
            if ($employee->category === 'Non-JFA') {
                $arr[] = [
                    'source' => 'role-based',
                    'need' => 'Manajemen dan Analisis Keuangan',
                    'competency_type' => 'Teknis',
                    'basis' => 'Kebutuhan jabatan Analis SDM / Keuangan',
                    'priority' => 'Tinggi'
                ];
                $arr[] = [
                    'source' => 'mandatory-based',
                    'need' => 'Pelayanan Publik',
                    'competency_type' => 'Manajerial',
                    'basis' => 'Mandatory learning',
                    'priority' => 'Tinggi'
                ];
            } else {
                $arr[] = [
                    'source' => 'role-based',
                    'need' => 'Literasi Digital',
                    'competency_type' => 'Teknis',
                    'basis' => 'Mandatory learning pelaksana',
                    'priority' => 'Tinggi'
                ];
                $arr[] = [
                    'source' => 'mandatory-based',
                    'need' => 'Integritas',
                    'competency_type' => 'Manajerial',
                    'basis' => 'Mandatory learning nilai ASN',
                    'priority' => 'Tinggi'
                ];
            }
        }

        // Add Active Strategic Directions from unit
        $directions = DB::table('strategic_directions')
            ->where('unit', $employee->unit)
            ->where('status', '!=', 'Draft')
            ->get();
        
        foreach ($directions as $dir) {
            $arr[] = [
                'source' => 'unit-strategic-direction-based',
                'need' => $dir->competency,
                'competency_type' => $this->getCompetencyType($dir->competency),
                'basis' => 'Arahan: ' . $dir->title,
                'priority' => 'Sedang'
            ];
        }

        // Sort recommendations: Tinggi first, then Sedang, then Rendah
        $priorityOrder = ['Tinggi' => 1, 'Sedang' => 2, 'Rendah' => 3];
        usort($arr, function($a, $b) use ($priorityOrder) {
            $orderA = $priorityOrder[$a['priority']] ?? 99;
            $orderB = $priorityOrder[$b['priority']] ?? 99;
            return $orderA <=> $orderB;
        });

        return $arr;
    }
}
