<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Helpers\UnitKerjaHelper;

class PengampuController extends Controller
{
    /**
     * Get the unit scope for the current authenticated user/role.
     * Returns an array of scopes.
     */
    private function getUnitScope()
    {
        $user = Auth::user();
        $role = session('active_role', $user->role);
        
        if ($role === 'bangkom') {
            return ['Biro Sumber Daya Manusia'];
        }
        
        if ($user->scope === 'Kantor Perwakilan') {
            return [$user->unit_eselon2];
        }
        
        return UnitKerjaHelper::getSubUnits($user->scope);
    }

    /**
     * IDP Demand Pool.
     */
    public function demandPool(Request $request)
    {
        $units = $this->getUnitScope();

        // 1. KPI Aggregates
        $idpItemsQuery = DB::table('idp_items')
            ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
            ->whereIn('employees.unit', $units);
        
        $totalDemand = $idpItemsQuery->count();
        $agreedCount = DB::table('idp_items')
            ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
            ->whereIn('employees.unit', $units)
            ->whereIn('idp_items.status', ['Disepakati', 'Realisasi'])->count();
        $executedPercent = $totalDemand > 0 ? round(($agreedCount / $totalDemand) * 100) : 0;

        // Most Needed IDP
        $mostNeeded = DB::table('idp_items')
            ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
            ->whereIn('employees.unit', $units)
            ->select('idp_items.need', DB::raw('count(*) as total'))
            ->groupBy('idp_items.need')
            ->orderByDesc('total')
            ->first();
        $mostNeededLabel = $mostNeeded ? $mostNeeded->need . ' (' . $mostNeeded->total . ')' : '-';

        // Non-JFA tanpa IDP
        $nonJfaWithoutIdp = DB::table('employees')
            ->whereIn('unit', $units)
            ->where('category', 'Non-JFA')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('idp_items')
                    ->whereColumn('idp_items.employee_id', 'employees.id');
            })
            ->count();

        // 2. Kanban Work Board
        $kanbanStrategic = DB::table('strategic_directions')->whereIn('unit', $units)->get();
        foreach ($kanbanStrategic as $dir) {
            $dir->is_followed_up = DB::table('bangkom_unit')
                ->whereIn('unit_pengusul', $units)
                ->where('kompetensi_dasar', $dir->competency)
                ->exists();
        }

        $rawNeeds = DB::table('idp_items')
            ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
            ->whereIn('employees.unit', $units)
            ->whereIn('idp_items.status', ['Diajukan', 'Disepakati'])
            ->select('idp_items.*', 'employees.name as employee_name')
            ->get();

        $kanbanNeedsGrouped = [];
        foreach ($rawNeeds as $item) {
            $comp = $item->need;
            if (!isset($kanbanNeedsGrouped[$comp])) {
                $isFollowedUp = DB::table('bangkom_unit')
                    ->whereIn('unit_pengusul', $units)
                    ->where('kompetensi_dasar', $comp)
                    ->exists();

                $kanbanNeedsGrouped[$comp] = (object)[
                    'competency' => $comp,
                    'count' => 0,
                    'employees' => [],
                    'priorities' => [],
                    'is_followed_up' => $isFollowedUp,
                ];
            }
            $kanbanNeedsGrouped[$comp]->count++;
            if (!in_array($item->employee_name, $kanbanNeedsGrouped[$comp]->employees)) {
                $kanbanNeedsGrouped[$comp]->employees[] = $item->employee_name;
            }
            $kanbanNeedsGrouped[$comp]->priorities[] = $item->priority;
        }

        foreach ($kanbanNeedsGrouped as $k => $obj) {
            if (in_array('Tinggi', $obj->priorities)) {
                $obj->top_priority = 'Tinggi';
            } elseif (in_array('Sedang', $obj->priorities)) {
                $obj->top_priority = 'Sedang';
            } else {
                $obj->top_priority = 'Rendah';
            }
        }
        $kanbanNeeds = array_values($kanbanNeedsGrouped);

        $kanbanPlans = DB::table('bangkom_unit')
            ->whereIn('unit_pengusul', $units)
            ->whereIn('status', ['draft', 'menunggu penetapan', 'ditetapkan'])
            ->select('id', 'nama_kegiatan as title', 'kompetensi_dasar as competency', 'status', 'unit_pengusul', 'jp', 'tanggal_mulai')
            ->get();

        $kanbanEvidence = DB::table('bangkom_unit')
            ->whereIn('unit_pengusul', $units)
            ->whereIn('status', ['realisasi', 'realisasi diajukan', 'selesai'])
            ->select('id', 'nama_kegiatan as title', 'kompetensi_dasar as competency', 'status', 'unit_pengusul', 'jp', 'tanggal_mulai')
            ->get();

        foreach ($kanbanEvidence as $ev) {
            $ev->peserta_count = DB::table('bangkom_unit_realisasi')->where('bangkom_unit_id', $ev->id)->count();
        }

        // 3. Demand Pool Table Multi-Source
        // Fetch all IDP items for the unit
        $idpDemands = DB::table('idp_items')
            ->join('employees', 'idp_items.employee_id', '=', 'employees.id')
            ->whereIn('employees.unit', $units)
            ->select('idp_items.id', 'idp_items.need as competency', 'idp_items.competency_type', 'idp_items.source', 'idp_items.priority', 'idp_items.status', 'idp_items.basis', 'employees.name as employee_name')
            ->get();

        // Fetch strategic directions
        $strategicDemands = DB::table('strategic_directions')
            ->whereIn('unit', $units)
            ->select('id', 'competency', 'basis as source', 'priority', 'status', 'context as basis', DB::raw("NULL as employee_name"))
            ->get();

        // Define exact competencies
        $kompetensiTeknis = [
            'Analisis Data', 'Analisis Proses Bisnis', 'Fraud Risk Management',
            'Governance, Risk, Control, and Compliance', 'Keuangan Negara/Daerah dan Kekayaan yang Dipisahkan',
            'Literasi Digital', 'Manajemen dan Analisis Keuangan', 'Manajemen Penugasan Pengawasan Intern',
            'Manajemen Strategis Pemerintah', 'Metode dan Teknik Pengawasan Intern',
            'Pelaksanaan Pengawasan Intern', 'Standar Audit dan Kode Etik', 'Analisis Kebijakan Publik',
        ];

        $kompetensiMansoskul = [
            'Integritas', 'Kerja Sama', 'Komunikasi', 'Orientasi pada Hasil', 'Pelayanan Publik',
            'Pengembangan Diri & Orang Lain', 'Mengelola Perubahan', 'Pengambilan Keputusan', 'Perekat Bangsa',
            'Karakteristik Lintas Manajerial', 'Kepemimpinan', 'Komunikasi dalam Peran sebagai Trusted Advisor dan Value Driver',
        ];

        $groupedDemands = [];

        // Initialize standard competencies
        foreach ($kompetensiTeknis as $comp) {
            $bangkomUnitCount = DB::table('bangkom_unit')
                ->whereIn('unit_pengusul', $units)
                ->where('kompetensi_dasar', $comp)
                ->count();
                
            $groupedDemands[$comp] = [
                'competency' => $comp,
                'total_demand' => 0,
                'items' => [],
                'follow_up' => $bangkomUnitCount > 0 ? 'Sudah' : 'Belum',
                'bangkom_unit_count' => $bangkomUnitCount,
                'category' => 'Teknis'
            ];
        }

        foreach ($kompetensiMansoskul as $comp) {
            $bangkomUnitCount = DB::table('bangkom_unit')
                ->whereIn('unit_pengusul', $units)
                ->where('kompetensi_dasar', $comp)
                ->count();
                
            $groupedDemands[$comp] = [
                'competency' => $comp,
                'total_demand' => 0,
                'items' => [],
                'follow_up' => $bangkomUnitCount > 0 ? 'Sudah' : 'Belum',
                'bangkom_unit_count' => $bangkomUnitCount,
                'category' => 'Mansoskul'
            ];
        }

        // Map idpDemands
        foreach ($idpDemands as $item) {
            $comp = $item->competency;
            if ($comp === 'Pengembangan Diri dan Orang Lain') {
                $comp = 'Pengembangan Diri & Orang Lain';
            }
            
            if (isset($groupedDemands[$comp])) {
                $groupedDemands[$comp]['total_demand']++;
                $groupedDemands[$comp]['items'][] = [
                    'id' => $item->id,
                    'employee_name' => $item->employee_name,
                    'source' => $item->source,
                    'basis' => $item->basis,
                    'priority' => $item->priority,
                    'status' => $item->status,
                    'type' => 'Pegawai IDP'
                ];
            } else {
                $type = in_array($item->competency_type, ['Manajerial', 'Sosial Kultural', 'Mansoskul']) ? 'Mansoskul' : 'Teknis';
                $lainnyaKey = "Lainnya ($type)";
                if (!isset($groupedDemands[$lainnyaKey])) {
                    $groupedDemands[$lainnyaKey] = [
                        'competency' => $lainnyaKey,
                        'total_demand' => 0,
                        'items' => [],
                        'follow_up' => 'Belum',
                        'bangkom_unit_count' => 0,
                        'category' => $type
                    ];
                }
                $groupedDemands[$lainnyaKey]['total_demand']++;
                $groupedDemands[$lainnyaKey]['items'][] = [
                    'id' => $item->id,
                    'employee_name' => $item->employee_name,
                    'source' => $item->source,
                    'basis' => $item->basis . ' (IDP: ' . $item->competency . ')',
                    'priority' => $item->priority,
                    'status' => $item->status,
                    'type' => 'Pegawai IDP'
                ];
            }
        }

        // Map strategicDemands
        foreach ($strategicDemands as $item) {
            $comp = $item->competency;
            if ($comp === 'Pengembangan Diri dan Orang Lain') {
                $comp = 'Pengembangan Diri & Orang Lain';
            }

            if (isset($groupedDemands[$comp])) {
                $groupedDemands[$comp]['total_demand']++;
                $groupedDemands[$comp]['items'][] = [
                    'id' => $item->id,
                    'employee_name' => 'Strategis Unit Kerja',
                    'source' => 'unit-strategic-direction-based',
                    'basis' => $item->basis,
                    'priority' => $item->priority,
                    'status' => $item->status,
                    'type' => 'Strategic Direction'
                ];
            } else {
                $lainnyaKey = "Lainnya (Teknis)";
                if (!isset($groupedDemands[$lainnyaKey])) {
                    $groupedDemands[$lainnyaKey] = [
                        'competency' => $lainnyaKey,
                        'total_demand' => 0,
                        'items' => [],
                        'follow_up' => 'Belum',
                        'bangkom_unit_count' => 0,
                        'category' => 'Teknis'
                    ];
                }
                $groupedDemands[$lainnyaKey]['total_demand']++;
                $groupedDemands[$lainnyaKey]['items'][] = [
                    'id' => $item->id,
                    'employee_name' => 'Strategis Unit Kerja',
                    'source' => 'unit-strategic-direction-based',
                    'basis' => $item->basis . ' (Arahan: ' . $item->competency . ')',
                    'priority' => $item->priority,
                    'status' => $item->status,
                    'type' => 'Strategic Direction'
                ];
            }
        }

        $demands = array_values($groupedDemands);
        $demandsTeknis = array_values(array_filter($demands, fn($d) => $d['category'] === 'Teknis'));
        $demandsMansoskul = array_values(array_filter($demands, fn($d) => $d['category'] === 'Mansoskul'));
        $scopeUnit = implode(', ', $units);

        $allEselon2Units = DB::table('users')
            ->where('unit_eselon2', '!=', '-')
            ->where('unit_eselon2', '!=', '')
            ->whereNotNull('unit_eselon2')
            ->distinct()
            ->pluck('unit_eselon2');

        return view('pengampu.demand_pool', compact(
            'totalDemand', 'executedPercent', 'mostNeededLabel', 'nonJfaWithoutIdp',
            'kanbanStrategic', 'kanbanNeeds', 'kanbanPlans', 'kanbanEvidence',
            'demands', 'demandsTeknis', 'demandsMansoskul', 'scopeUnit', 'allEselon2Units', 'units'
        ));
    }

    /**
     * Strategic Direction List.
     */
    public function strategicDirection(Request $request)
    {
        $units = $this->getUnitScope();
        $directions = DB::table('strategic_directions')->whereIn('unit', $units)->get();
        
        $bangkomStatuses = DB::table('bangkom_unit')
            ->whereNotNull('strategic_direction_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('strategic_direction_id');

        foreach ($directions as $d) {
            $latestBangkom = $bangkomStatuses->get($d->id)?->first();
            $d->bangkom_status = $latestBangkom ? $latestBangkom->status : null;
        }
        $employees = DB::table('employees')->whereIn('unit', $units)->get();
        
        $allCompetencies = [
            'Integritas', 'Kerja Sama', 'Komunikasi', 'Orientasi pada Hasil', 'Pelayanan Publik',
            'Pengembangan Diri & Orang Lain', 'Mengelola Perubahan', 'Pengambilan Keputusan', 'Perekat Bangsa',
            'Manajemen Pengawasan Intern', 'Standar Audit', 'Analisis Data', 'Audit PBJ', 'Fraud Risk Management',
            'Manajemen ASN', 'Literasi Digital', 'Keamanan Data Dasar'
        ];

        return view('pengampu.strategic_direction', compact('directions', 'employees', 'allCompetencies', 'units'));
    }

    /**
     * Rencana & Penetapan Bangkom Unit.
     */
    public function rencanaBangkom(Request $request)
    {
        $units = $this->getUnitScope();
        $plans = DB::table('bangkom_unit')->whereIn('unit_pengusul', $units)->get();
        $employees = DB::table('employees')->whereIn('unit', $units)->get();
        
        $allCompetencies = [
            'Integritas', 'Kerja Sama', 'Komunikasi', 'Orientasi pada Hasil', 'Pelayanan Publik',
            'Pengembangan Diri & Orang Lain', 'Mengelola Perubahan', 'Pengambilan Keputusan', 'Perekat Bangsa',
            'Manajemen Pengawasan Intern', 'Standar Audit', 'Analisis Data', 'Audit PBJ', 'Fraud Risk Management',
            'Manajemen ASN', 'Literasi Digital', 'Keamanan Data Dasar'
        ];

        $allEselon2Units = DB::table('users')
            ->where('unit_eselon2', '!=', '-')
            ->where('unit_eselon2', '!=', '')
            ->whereNotNull('unit_eselon2')
            ->distinct()
            ->pluck('unit_eselon2');

        return view('pengampu.rencana_bangkom', compact('plans', 'employees', 'allCompetencies', 'allEselon2Units', 'units'));
    }

    /**
     * Store Rencana Bangkom.
     */
    public function storeRencanaBangkom(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:999',
            'unit_pengusul' => 'required|string',
            'indikator_kinerja' => 'required|string|max:999',
            'jenis_latar_belakang' => 'required|string',
            'latar_belakang' => 'required|string|max:999',
            'tujuan_kegiatan' => 'required|string|max:999',
            'kompetensi_dasar' => 'required|string',
            'indikator_keberhasilan' => 'required|array|min:1',
            'indikator_keberhasilan.*' => 'required|string|max:999',
            'penugasan_terkait' => 'required|array|min:1',
            'penugasan_terkait.*' => 'required|string|max:999',
            'metode' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_kelas' => 'required|integer|min:1|max:10',
            'jalur_pembelajaran' => 'required|string',
            'nilai_anggaran' => 'required|numeric|min:0',
            'kriteria_peserta' => 'required|array|min:1',
            'kriteria_peserta.*' => 'required|string|max:999',
            'fasilitator' => 'required|string|max:100',
            'evaluasi' => 'required|string',
            'jenis_evaluasi' => 'required_if:evaluasi,Ya|nullable|integer',
            'jp' => 'required|integer|min:1|max:99',
        ]);

        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        if ($request->tanggal_mulai < $tomorrow) {
            return back()->withInput()->with('error', 'Tanggal Mulai harus minimal H+1 hari dari tanggal pembuatan kegiatan.');
        }

        // Auto generate ID: BK0001, etc.
        $maxId = DB::table('bangkom_unit')->select(DB::raw('MAX(id) as max_id'))->first()->max_id;
        $nextNum = 1;
        if ($maxId && preg_match('/^BK(\d+)$/', $maxId, $matches)) {
            $nextNum = intval($matches[1]) + 1;
        }
        $newId = 'BK' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        DB::table('bangkom_unit')->insert([
            'id' => $newId,
            'nama_kegiatan' => $request->nama_kegiatan,
            'unit_pengusul' => $request->unit_pengusul,
            'indikator_kinerja' => $request->indikator_kinerja,
            'jenis_latar_belakang' => $request->jenis_latar_belakang,
            'latar_belakang' => $request->latar_belakang,
            'strategic_direction_id' => $request->strategic_direction_id,
            'tujuan_kegiatan' => $request->tujuan_kegiatan,
            'kompetensi_dasar' => $request->kompetensi_dasar,
            'indikator_keberhasilan' => json_encode(array_values($request->indikator_keberhasilan)),
            'penugasan_terkait' => json_encode(array_values($request->penugasan_terkait)),
            'metode' => $request->metode,
            'tanggal_dibuat' => now(),
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_kelas' => $request->jumlah_kelas,
            'jalur_pembelajaran' => $request->jalur_pembelajaran,
            'nilai_anggaran' => $request->nilai_anggaran,
            'kriteria_peserta' => json_encode(array_values($request->kriteria_peserta)),
            'fasilitator' => $request->fasilitator,
            'evaluasi' => $request->evaluasi,
            'jenis_evaluasi' => $request->evaluasi === 'Ya' ? $request->jenis_evaluasi : null,
            'status' => 'draft',
            'jp' => $request->jp,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($request->filled('strategic_direction_id')) {
            DB::table('strategic_directions')->where('id', $request->strategic_direction_id)->update([
                'status' => 'Draft Rencana',
                'follow_up' => 'Sudah',
                'updated_at' => now()
            ]);
        }

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Tambah Rencana Bangkom Unit',
            'object' => $request->nama_kegiatan . ' (' . $newId . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Rencana kegiatan berhasil ditambahkan sebagai Draft.');
    }

    /**
     * Update Rencana Bangkom.
     */
    public function updateRencanaBangkom($id, Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:999',
            'unit_pengusul' => 'required|string',
            'indikator_kinerja' => 'required|string|max:999',
            'jenis_latar_belakang' => 'required|string',
            'latar_belakang' => 'required|string|max:999',
            'tujuan_kegiatan' => 'required|string|max:999',
            'kompetensi_dasar' => 'required|string',
            'indikator_keberhasilan' => 'required|array|min:1',
            'indikator_keberhasilan.*' => 'required|string|max:999',
            'penugasan_terkait' => 'required|array|min:1',
            'penugasan_terkait.*' => 'required|string|max:999',
            'metode' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_kelas' => 'required|integer|min:1|max:10',
            'jalur_pembelajaran' => 'required|string',
            'nilai_anggaran' => 'required|numeric|min:0',
            'kriteria_peserta' => 'required|array|min:1',
            'kriteria_peserta.*' => 'required|string|max:999',
            'fasilitator' => 'required|string|max:100',
            'evaluasi' => 'required|string',
            'jenis_evaluasi' => 'required_if:evaluasi,Ya|nullable|integer',
            'jp' => 'required|integer|min:1|max:99',
        ]);

        $plan = DB::table('bangkom_unit')->where('id', $id)->first();
        if (!$plan) return back()->with('error', 'Rencana tidak ditemukan.');

        $createdTime = strtotime($plan->tanggal_dibuat);
        $minStartDate = date('Y-m-d', $createdTime + 86400);
        if ($request->tanggal_mulai < $minStartDate) {
            return back()->withInput()->with('error', 'Tanggal Mulai harus minimal H+1 hari dari tanggal pembuatan kegiatan.');
        }

        DB::table('bangkom_unit')->where('id', $id)->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'unit_pengusul' => $request->unit_pengusul,
            'indikator_kinerja' => $request->indikator_kinerja,
            'jenis_latar_belakang' => $request->jenis_latar_belakang,
            'latar_belakang' => $request->latar_belakang,
            'tujuan_kegiatan' => $request->tujuan_kegiatan,
            'kompetensi_dasar' => $request->kompetensi_dasar,
            'indikator_keberhasilan' => json_encode(array_values($request->indikator_keberhasilan)),
            'penugasan_terkait' => json_encode(array_values($request->penugasan_terkait)),
            'metode' => $request->metode,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_kelas' => $request->jumlah_kelas,
            'jalur_pembelajaran' => $request->jalur_pembelajaran,
            'nilai_anggaran' => $request->nilai_anggaran,
            'kriteria_peserta' => json_encode(array_values($request->kriteria_peserta)),
            'fasilitator' => $request->fasilitator,
            'evaluasi' => $request->evaluasi,
            'jenis_evaluasi' => $request->evaluasi === 'Ya' ? $request->jenis_evaluasi : null,
            'status' => 'draft', // Reset to draft on update
            'jp' => $request->jp,
            'edited_by' => Auth::user()->email,
            'updated_at' => now()
        ]);

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Update Rencana Bangkom Unit',
            'object' => $request->nama_kegiatan . ' (' . $id . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Rencana kegiatan berhasil diperbarui.');
    }

    /**
     * Submit Rencana Bangkom.
     */
    public function submitRencanaBangkom($id)
    {
        $plan = DB::table('bangkom_unit')->where('id', $id)->first();
        if (!$plan) return back()->with('error', 'Rencana tidak ditemukan.');

        DB::table('bangkom_unit')->where('id', $id)->update([
            'status' => 'menunggu penetapan',
            'updated_at' => now()
        ]);

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Mengajukan Rencana Bangkom',
            'object' => $plan->nama_kegiatan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Rencana kegiatan berhasil diajukan ke Kepala Unit.');
    }

    /**
     * Cancel Rencana Bangkom.
     */
    public function cancelRencanaBangkom($id, Request $request)
    {
        $request->validate(['cancel_reason' => 'required|string']);

        $plan = DB::table('bangkom_unit')->where('id', $id)->first();
        if (!$plan) return back()->with('error', 'Rencana tidak ditemukan.');

        $statusLower = strtolower($plan->status);
        if ($statusLower === 'draft' || $statusLower === 'realisasi') {
            return back()->with('error', 'Rencana dengan status Draft atau Realisasi tidak dapat dibatalkan.');
        }

        $today = date('Y-m-d');
        if ($today > $plan->tanggal_mulai) {
            return back()->with('error', 'Kegiatan tidak dapat dibatalkan karena tanggal mulai kegiatan sudah terlewati.');
        }

        DB::table('bangkom_unit')->where('id', $id)->update([
            'status' => 'pembatalan diajukan',
            'cancelled_by' => Auth::user()->email,
            'cancel_reason' => $request->cancel_reason,
            'updated_at' => now()
        ]);

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Mengajukan Pembatalan Bangkom',
            'object' => $plan->nama_kegiatan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Pembatalan rencana kegiatan berhasil diajukan.');
    }

    public function realisasiBangkom(Request $request)
    {
        $units = $this->getUnitScope();
        $user = Auth::user();
        $role = session('active_role', $user->role);

        $query = DB::table('bangkom_unit')
            ->whereIn('status', ['ditetapkan', 'realisasi', 'persetujuan realisasi', 'realisasi disetujui']);

        // Scope to user's unit if not national/executive/admin roles
        if (!in_array($role, ['bangkom', 'karoSDM', 'kombinasi', 'admin'])) {
            $query->whereIn('unit_pengusul', $units);
        }

        $plans = $query->get()
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

        $employees = DB::table('employees')->orderBy('name')->get();
        return view('pengampu.realisasi_bangkom', compact('plans', 'employees'));
    }

    /**
     * Store Realisasi Bangkom.
     */
    public function storeRealisasiBangkom($id, Request $request)
    {
        $request->validate([
            'file_daftar_hadir' => 'required|file|max:2048|mimes:pdf',
            'file_notulen' => 'required|file|max:2048|mimes:pdf',
            'file_dokumentasi' => 'required|file|max:2048|mimes:jpg,jpeg,png',
            'participants' => 'required|array|min:1',
            'participants.*.employee_id' => 'required|string|exists:employees,id',
            'participants.*.skor_penyelenggara' => 'required|integer|min:1|max:100',
            'participants.*.skor_materi' => 'required|integer|min:1|max:100',
            'participants.*.skor_fasilitator' => 'required|integer|min:1|max:100',
            'participants.*.skor_pre' => 'nullable|integer|min:1|max:100',
            'participants.*.skor_post' => 'nullable|integer|min:1|max:100',
        ]);

        $plan = DB::table('bangkom_unit')->where('id', $id)->first();
        if (!$plan) return back()->with('error', 'Kegiatan tidak ditemukan.');

        // Validate pre/post test if jenis_evaluasi is Level 2 (2)
        if ($plan->jenis_evaluasi == 2) {
            foreach ($request->participants as $p) {
                if (empty($p['skor_pre']) || empty($p['skor_post'])) {
                    return back()->withInput()->with('error', 'Skor Pre-test dan Post-test wajib diisi untuk evaluasi Level 2.');
                }
            }
        }

        // Mock upload files by saving local names
        $fileDaftarHadir = $request->file('file_daftar_hadir')->getClientOriginalName();
        $fileNotulen = $request->file('file_notulen')->getClientOriginalName();
        $fileDokumentasi = $request->file('file_dokumentasi')->getClientOriginalName();
        $fileNilai = $request->hasFile('file_nilai') ? $request->file('file_nilai')->getClientOriginalName() : null;

        DB::table('bangkom_unit')->where('id', $id)->update([
            'status' => 'realisasi',
            'dok_daftar_hadir' => $fileDaftarHadir,
            'dok_notulen' => $fileNotulen,
            'dok_dokumentasi' => $fileDokumentasi,
            'dok_nilai' => $fileNilai,
            'updated_at' => now()
        ]);

        // Clean existing realizations
        DB::table('bangkom_unit_realisasi')->where('bangkom_unit_id', $id)->delete();

        // Save new realizations and update employee IDP coverage
        foreach ($request->participants as $p) {
            DB::table('bangkom_unit_realisasi')->insert([
                'bangkom_unit_id' => $id,
                'employee_id' => $p['employee_id'],
                'skor_penyelenggara' => $p['skor_penyelenggara'],
                'skor_materi' => $p['skor_materi'],
                'skor_fasilitator' => $p['skor_fasilitator'],
                'skor_pre' => $p['skor_pre'] ?? null,
                'skor_post' => $p['skor_post'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Sync IDP Coverage using Exact Matching
            \App\Helpers\IdpCoverageHelper::syncEmployeeCoverage($p['employee_id']);
        }

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Input Realisasi Bangkom Unit',
            'object' => $plan->nama_kegiatan . ' (' . $id . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Realisasi kegiatan berhasil disimpan dan status diubah menjadi Realisasi.');
    }

    /**
     * Submit Realisasi to Verifikator Level 2 (Status: persetujuan realisasi).
     */
    public function submitPersetujuanRealisasi($id)
    {
        $plan = DB::table('bangkom_unit')->where('id', $id)->first();
        if (!$plan) return back()->with('error', 'Kegiatan tidak ditemukan.');

        DB::table('bangkom_unit')->where('id', $id)->update([
            'status' => 'persetujuan realisasi',
            'updated_at' => now()
        ]);

        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Mengajukan Persetujuan Realisasi Bangkom',
            'object' => $plan->nama_kegiatan . ' (' . $id . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Realisasi kegiatan berhasil diajukan untuk Persetujuan Verifikator Level 2.');
    }

    /**
     * Approve Realisasi by Verifikator Level 2 (Status: realisasi disetujui).
     */
    public function approveRealisasi($id)
    {
        $plan = DB::table('bangkom_unit')->where('id', $id)->first();
        if (!$plan) return back()->with('error', 'Kegiatan tidak ditemukan.');

        DB::table('bangkom_unit')->where('id', $id)->update([
            'status' => 'realisasi disetujui',
            'updated_at' => now()
        ]);

        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Menyetujui Realisasi Bangkom Unit (Verifikator Level 2)',
            'object' => $plan->nama_kegiatan . ' (' . $id . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Realisasi kegiatan berhasil Disetujui oleh Verifikator Level 2.');
    }

    /**
     * Reject/Return Realisasi by Verifikator Level 2 (Status: realisasi).
     */
    public function rejectRealisasi($id, Request $request)
    {
        $request->validate([
            'reject_reason' => 'required|string|max:1000',
        ]);

        $plan = DB::table('bangkom_unit')->where('id', $id)->first();
        if (!$plan) return back()->with('error', 'Kegiatan tidak ditemukan.');

        DB::table('bangkom_unit')->where('id', $id)->update([
            'status' => 'realisasi',
            'updated_at' => now()
        ]);

        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Mengembalikan Realisasi Bangkom untuk Diperbaiki',
            'object' => $plan->nama_kegiatan . ' (' . $id . ') - Alasan: ' . $request->input('reject_reason'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Realisasi kegiatan berhasil dikembalikan ke Pengampu untuk diperbaiki dengan catatan: ' . $request->input('reject_reason'));
    }

    /**
     * Certification Control.
     */
    public function certification(Request $request)
    {
        $units = $this->getUnitScope();
        $role = session('active_role', Auth::user()->role);

        $user = Auth::user();

        // Subquery untuk menghitung total peserta per pelatihan sesuai scope
        $pesertaSubQuery = DB::table('simpel_t_pendaftar')
            ->join('employees', 'simpel_t_pendaftar.employee_id', '=', 'employees.id')
            ->select('simpel_t_pendaftar.kode_pelatihan', DB::raw('COUNT(simpel_t_pendaftar.id) as total_peserta'))
            ->groupBy('simpel_t_pendaftar.kode_pelatihan');

        if (!in_array($role, ['bangkom', 'karoSDM', 'kombinasi', 'admin'])) {
            if (str_ends_with($user->jabatan, 'Tata Usaha Deputi')) {
                $pesertaSubQuery->where('employees.unit_kerja_1', $user->unit_eselon1);
            } else {
                $pesertaSubQuery->where('employees.unit_kerja_2', $user->unit_eselon2);
            }
        }

        // Ambil data kegiatan sertifikasi beserta total pesertanya
        $diklatQuery = DB::table('simpel_t_diklat')
            ->leftJoinSub($pesertaSubQuery, 'peserta', function ($join) {
                $join->on('simpel_t_diklat.kode_pelatihan', '=', 'peserta.kode_pelatihan');
            })
            ->select(
                'simpel_t_diklat.kode_pelatihan',
                'simpel_t_diklat.nama_pelatihan',
                'simpel_t_diklat.tanggal_mulai',
                'simpel_t_diklat.tanggal_selesai',
                DB::raw('COALESCE(peserta.total_peserta, 0) as total_peserta')
            )
            ->where('simpel_t_diklat.nama_pelatihan', 'like', 'Pelatihan dan Sertifikasi%');

        if ($request->filled('q')) {
            $q = $request->q;
            $diklatQuery->where('simpel_t_diklat.nama_pelatihan', 'like', "%{$q}%");
        }

        $diklatQuery->orderByDesc('total_peserta');

        $kegiatans = $diklatQuery->paginate(15)->withQueryString();

        return view('pengampu.certification', compact('kegiatans'));
    }

    public function getPesertaSertifikasi(Request $request, $kode)
    {
        $units = $this->getUnitScope();
        $role = session('active_role', Auth::user()->role);

        $pesertaQuery = DB::table('simpel_t_pendaftar')
            ->join('employees', 'simpel_t_pendaftar.employee_id', '=', 'employees.id')
            ->join('simpel_t_diklat', 'simpel_t_pendaftar.kode_pelatihan', '=', 'simpel_t_diklat.kode_pelatihan')
            ->where('simpel_t_pendaftar.kode_pelatihan', $kode)
            ->select(
                'simpel_t_pendaftar.employee_id',
                'employees.name as nama_pegawai',
                'employees.unit',
                'employees.jabatan',
                'simpel_t_pendaftar.kode_pelatihan',
                'simpel_t_diklat.nama_pelatihan'
            );

        if (!in_array($role, ['bangkom', 'karoSDM', 'kombinasi', 'admin'])) {
            $user = Auth::user();
            if (str_ends_with($user->jabatan, 'Tata Usaha Deputi')) {
                $pesertaQuery->where('employees.unit_kerja_1', $user->unit_eselon1);
            } else {
                $pesertaQuery->where('employees.unit_kerja_2', $user->unit_eselon2);
            }
        }

        $peserta = $pesertaQuery->get();

        if ($peserta->isEmpty()) {
            return response()->json([]);
        }

        $mappings = DB::table('certification_mappings')->where('simpel_kode_pelatihan', $kode)->get();
        $empIds = $peserta->pluck('employee_id')->unique();
        
        $allSertifikasis = DB::table('employee_sertifikasis')
            ->whereIn('employee_id', $empIds)
            ->get()
            ->groupBy('employee_id');

        $result = [];
        foreach ($peserta as $p) {
            $p->sertifikasis = $allSertifikasis->get($p->employee_id, collect());
            $p->match_status = 'Belum Terdeteksi';
            $p->matched_sertifikat = null;

            // 1. Cek Manual Mapping
            foreach ($mappings as $map) {
                $matched = $p->sertifikasis->firstWhere('nama_sertifikasi', $map->smile_sertifikasi_name);
                if ($matched) {
                    $p->match_status = 'Mapped';
                    $p->matched_sertifikat = $matched->nama_sertifikasi;
                    break;
                }
            }

            // 2. Cek Fuzzy Matching
            if ($p->match_status === 'Belum Terdeteksi') {
                $sanitizedSimpel = $this->sanitizeSertName($p->nama_pelatihan);
                
                foreach ($p->sertifikasis as $sert) {
                    $sanitizedSmile = $this->sanitizeSertName($sert->nama_sertifikasi);
                    similar_text($sanitizedSimpel, $sanitizedSmile, $perc);
                    if ($perc > 75) {
                        $p->match_status = 'Auto-Match';
                        $p->matched_sertifikat = $sert->nama_sertifikasi;
                        break;
                    }
                }
            }

            $result[] = [
                'employee_id' => $p->employee_id,
                'nama_pegawai' => $p->nama_pegawai,
                'jabatan' => $p->jabatan,
                'unit' => $p->unit,
                'match_status' => $p->match_status,
                'matched_sertifikat' => $p->matched_sertifikat,
                'sertifikasis' => $p->sertifikasis->toArray() // Untuk dropdown manual map
            ];
        }

        return response()->json($result);
    }

    private function sanitizeSertName($name) {
        $name = strtolower($name);
        $remove = ['diklat', 'pelatihan', 'sertifikasi', 'bimtek', 'workshop', 'ahli', 'pertama', 'muda', 'madya', 'utama', 'tingkat', 'dasar'];
        foreach ($remove as $word) {
            $name = str_replace($word, '', $name);
        }
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    public function mapCertification(Request $request)
    {
        $request->validate([
            'kode_pelatihan' => 'required',
            'sertifikasi_name' => 'required'
        ]);

        DB::table('certification_mappings')->updateOrInsert(
            [
                'simpel_kode_pelatihan' => $request->kode_pelatihan,
                'smile_sertifikasi_name' => $request->sertifikasi_name
            ],
            ['updated_at' => now(), 'created_at' => now()]
        );

        return redirect()->back()->with('success', 'Mapping sertifikasi berhasil disimpan.');
    }

    /**
     * Profil 360 & Kompetensi Pegawai Unit.
     */
    public function profil360(Request $request)
    {
        $units = $this->getUnitScope();
        
        // Search employee query within unit
        $search = $request->query('q');
        
        $employeesQuery = DB::table('employees')->whereIn('unit', $units);
        $employees = $employeesQuery->get();
        $employee = $employees->first();

        // If selected specific employee from search click
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

        return view('pengampu.profil_360', compact('employee', 'employees', 'needs', 'idpItems', 'search', 'diklats', 'sertifikasis', 'bangkoms'));
    }
}
