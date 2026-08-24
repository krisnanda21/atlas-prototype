<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Import Users dari CSV
        $roleScopeOverride = [
            'sesma'     => 'nasional',
            'karoSDM'   => 'nasional',
            'kombinasi' => 'nasional',
            'bangkom'   => 'nasional',
            'admin'     => 'teknis',
        ];

        $modeIndividu = [
            'sesma'  => false,
            'deputi' => false,
        ];

        $csvPath = __DIR__ . '/../../../akun_dummy.csv';
        if (!file_exists($csvPath)) {
            $csvPath = 'E:\\Anti Gravity\\ATLAS Prototype\\akun_dummy.csv';
        }

        $rows = [];
        if (($fh = fopen($csvPath, 'r')) !== false) {
            $header = null;
            while (($line = fgetcsv($fh)) !== false) {
                if ($header === null) {
                    $header = $line;
                    continue;
                }
                if (empty(array_filter($line))) continue;

                [$no, $nama, $email, $jabatan, $unitEselon2, $unitEselon1, $role] = $line;

                $role = trim($role);
                if (isset($roleScopeOverride[$role])) {
                    $scope = $roleScopeOverride[$role];
                } elseif ($role === 'deputi') {
                    $scope = trim($unitEselon1) !== '-' ? trim($unitEselon1) : 'nasional';
                } elseif (in_array($role, ['eselon2', 'eselon3', 'pengampuSDM', 'staff'])) {
                    $scope = trim($unitEselon2) !== '-' ? trim($unitEselon2) : trim($unitEselon1);
                } else {
                    $scope = trim($unitEselon2) !== '-' ? trim($unitEselon2) : trim($unitEselon1);
                }

                $mode = isset($modeIndividu[$role]) ? $modeIndividu[$role] : true;

                $rows[] = [
                    'name'          => trim($nama),
                    'email'         => strtolower(trim($email)),
                    'password'      => Hash::make('password'),
                    'role'          => $role,
                    'scope'         => $scope,
                    'jabatan'       => trim($jabatan),
                    'unit_eselon2'  => trim($unitEselon2),
                    'unit_eselon1'  => trim($unitEselon1),
                    'mode_individu' => $mode,
                    'nip'           => null,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
            fclose($fh);
        }

        // Tambah Admin
        $rows[] = [
            'name'          => 'Admin',
            'email'         => 'admin@atlas.id',
            'password'      => Hash::make('password'),
            'role'          => 'admin',
            'scope'         => 'teknis',
            'jabatan'       => 'Admin Sistem',
            'unit_eselon2'  => '-',
            'unit_eselon1'  => '-',
            'mode_individu' => true,
            'nip'           => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('users')->insert($rows);

        // 2. Seed Employees & IDP dari Users
        $pegawaiPath = 'E:\Anti Gravity\ATLAS Prototype\dummy_data\interna\data_pegawai.csv';
        $diklatPath = 'E:\Anti Gravity\ATLAS Prototype\dummy_data\smile\data_diklat_final.csv';
        $sertifikasiPath = 'E:\Anti Gravity\ATLAS Prototype\dummy_data\smile\data_sertifikasi_final.csv';

        $employeesData = [];
        $emailToNipMap = [];
        if (file_exists($pegawaiPath) && ($fh = fopen($pegawaiPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $nip = trim($line[0]);
                $employeesData[] = [
                    'id' => $nip,
                    'name' => trim($line[1]),
                    'jenis_kelamin' => trim($line[2]) !== '\N' ? trim($line[2]) : null,
                    'tanggal_lahir' => trim($line[3]) !== '\N' ? trim($line[3]) : null,
                    'email_dinas' => strtolower(trim($line[4])),
                    'unit_kerja_1' => trim($line[5]),
                    'unit_kerja_2' => trim($line[6]),
                    'jabatan' => trim($line[7]),
                    'pangkat' => trim($line[8]),
                    'strata' => trim($line[9]),
                    'jurusan' => trim($line[10]),
                    'toefl' => trim($line[11]) !== '\N' && trim($line[11]) !== '' ? (int)$line[11] : null,
                    'ielts' => trim($line[12]) !== '\N' && trim($line[12]) !== '' ? (float)$line[12] : null,
                    'initial' => trim($line[13]),
                    'category' => trim($line[14]),
                    'unit' => trim($line[15]),
                    'role' => trim($line[16]),
                    'assessment' => trim($line[17]) == '1' ? true : false,
                    'assessment_label' => trim($line[18]),
                    'basis' => trim($line[19]),
                    'idp_coverage' => (int)trim($line[20]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $emailToNipMap[strtolower(trim($line[4]))] = $nip;
            }
            fclose($fh);
        }

        $usersFromDb = DB::table('users')->get();
        static $dummyNipCounter = 1;
        
        foreach ($usersFromDb as $u) {
            if ($u->role === 'admin') continue;
            
            $emailKey = strtolower(trim($u->email));
            
            if (isset($emailToNipMap[$emailKey])) {
                $nip = $emailToNipMap[$emailKey];
                $empRecord = collect($employeesData)->firstWhere('email_dinas', $emailKey);
                
                DB::table('users')->where('id', $u->id)->update([
                    'nip' => $nip,
                    'jabatan' => $empRecord['jabatan'],
                    'unit_eselon1' => $empRecord['unit_kerja_1'],
                    'unit_eselon2' => $empRecord['unit_kerja_2'],
                ]);
            } else {
                // Penanganan Edge Case jika tidak cocok
                $nip = '99999' . str_pad($dummyNipCounter++, 13, '0', STR_PAD_LEFT);
                
                DB::table('users')->where('id', $u->id)->update([
                    'nip' => $nip
                ]);
                
                $employeesData[] = [
                    'id' => $nip,
                    'name' => $u->name,
                    'jenis_kelamin' => 'Laki-laki',
                    'tanggal_lahir' => '1985-01-01',
                    'email_dinas' => $u->email,
                    'unit_kerja_1' => $u->unit_eselon1 ?? '-',
                    'unit_kerja_2' => $u->unit_eselon2 ?? '-',
                    'jabatan' => $u->jabatan ?? 'Pegawai',
                    'pangkat' => '-',
                    'strata' => '-',
                    'jurusan' => '-',
                    'toefl' => null,
                    'ielts' => null,
                    'initial' => substr($u->name, 0, 3),
                    'category' => 'Non-JFA',
                    'unit' => $u->unit_eselon1 ?? '-',
                    'role' => $u->jabatan ?? 'Pegawai',
                    'assessment' => false,
                    'assessment_label' => 'Belum Dinilai',
                    'basis' => 'Penugasan rutin',
                    'idp_coverage' => rand(30, 85),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Add remaining employees as users with role 'staff'
        $existingEmails = DB::table('users')->pluck('email')->map(fn($e) => strtolower(trim($e)))->toArray();
        $existingNips = DB::table('users')->whereNotNull('nip')->pluck('nip')->toArray();
        
        $newUsers = [];
        foreach ($employeesData as $emp) {
            $empEmail = strtolower(trim($emp['email_dinas']));
            $empNip = $emp['id'];
            
            if (in_array($empEmail, $existingEmails) || in_array($empNip, $existingNips)) {
                continue;
            }
            
            $scope = ($emp['unit_kerja_2'] !== '-' && $emp['unit_kerja_2'] !== '') ? $emp['unit_kerja_2'] : $emp['unit_kerja_1'];
            
            $newUsers[] = [
                'name'          => $emp['name'],
                'email'         => $empEmail,
                'password'      => Hash::make('password'),
                'role'          => 'staff',
                'scope'         => $scope,
                'jabatan'       => $emp['jabatan'],
                'unit_eselon2'  => $emp['unit_kerja_2'],
                'unit_eselon1'  => $emp['unit_kerja_1'],
                'mode_individu' => true,
                'nip'           => $empNip,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
            
            $existingEmails[] = $empEmail;
            $existingNips[] = $empNip;
        }
        
        if (!empty($newUsers)) {
            foreach (array_chunk($newUsers, 100) as $chunk) {
                DB::table('users')->insert($chunk);
            }
        }

        $employees = $employeesData; // Data pegawai sudah lengkap
        $idpItems = [];
        $gaps = [];
        
        $updatedUsers = DB::table('users')->get()->keyBy('nip');

        foreach ($employeesData as $emp) {
            $nip = $emp['id'];
            $unit = ($emp['unit_kerja_2'] !== '-' && $emp['unit_kerja_2'] !== '') ? $emp['unit_kerja_2'] : $emp['unit_kerja_1'];

            $userRole = 'staff';
            if (isset($updatedUsers[$nip])) {
                $userRole = $updatedUsers[$nip]->role;
            }

            if ($userRole === 'staff' || $userRole === 'eselon2') {
                $idpItems[] = [
                    'id'              => Str::uuid()->toString(),
                    'employee_id'     => $nip,
                    'need'            => 'Peningkatan Kompetensi Teknis ' . ($unit !== 'nasional' ? $unit : 'Layanan SDM'),
                    'competency_type' => 'Teknis',
                    'source'          => 'unit-strategic-direction-based',
                    'basis'           => 'Kebutuhan pengembangan berdasarkan arahan strategis unit ' . ($unit !== 'nasional' ? $unit : 'Layanan SDM'),
                    'priority'        => 'Sedang',
                    'status'          => $userRole === 'eselon2' ? 'Diajukan' : 'Draft',
                    'revision_note'   => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
                $idpItems[] = [
                    'id'              => Str::uuid()->toString(),
                    'employee_id'     => $nip,
                    'need'            => 'Fraud Risk Management',
                    'competency_type' => 'Teknis',
                    'source'          => 'role-based',
                    'basis'           => 'Kebutuhan jabatan ' . $emp['jabatan'],
                    'priority'        => 'Tinggi',
                    'status'          => 'Draft',
                    'revision_note'   => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        // Representative employees for Perwakilan BPKP is now completely removed 
        // because the new data_pegawai.csv already contains comprehensive representative data for Perwakilan units.

        $diklats = [];
        if (file_exists($diklatPath) && ($fh = fopen($diklatPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $diklats[] = [
                    'employee_id' => trim($line[0]),
                    'no_sertifikat' => trim($line[1]),
                    'nama_diklat' => trim($line[2]),
                    'jumlah_jam' => (int)trim($line[3]),
                    'dokumen' => trim($line[4]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            fclose($fh);
        }

        $sertifikasis = [];
        if (file_exists($sertifikasiPath) && ($fh = fopen($sertifikasiPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $sertifikasis[] = [
                    'employee_id' => trim($line[0]),
                    'nama_sertifikasi' => trim($line[1]),
                    'nomor_sertifikasi' => trim($line[2]),
                    'dokumen' => trim($line[3]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            fclose($fh);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('employees')->truncate();
        DB::table('idp_items')->truncate();
        DB::table('competency_gaps')->truncate();
        DB::table('employee_diklats')->truncate();
        DB::table('employee_sertifikasis')->truncate();
        DB::table('compass_nilai_mansos')->truncate();
        DB::table('compass_nilai_teknis')->truncate();
        DB::table('compass_nilai_rata_rata')->truncate();
        DB::table('interna_rencana_diklat')->truncate();
        DB::table('simpel_t_diklat')->truncate();
        DB::table('simpel_t_pendaftar')->truncate();
        DB::table('situbel_t_tubel')->truncate();
        DB::table('compass_req_mansos')->truncate();
        DB::table('strategic_directions')->truncate();
        DB::table('bangkom_unit')->truncate();
        DB::table('bangkom_unit_realisasi')->truncate();
        DB::table('audit_trails')->truncate();
        DB::table('reference_parameters')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach (array_chunk($employees, 100) as $chunk) {
            DB::table('employees')->insert($chunk);
        }
        DB::table('idp_items')->insert($idpItems);
        
        foreach (array_chunk($diklats, 100) as $chunk) {
            DB::table('employee_diklats')->insert($chunk);
        }
        foreach (array_chunk($sertifikasis, 100) as $chunk) {
            DB::table('employee_sertifikasis')->insert($chunk);
        }

        // Seed COMPASS data
        $mansosPath = 'E:\Anti Gravity\ATLAS Prototype\dummy_data\compass\nilai_mansos_all.csv';
        $teknisPath = 'E:\Anti Gravity\ATLAS Prototype\dummy_data\compass\nilai_teknis_all.csv';
        $avgPath = 'E:\Anti Gravity\ATLAS Prototype\dummy_data\compass\nilai_rata_rata_final.csv';

        $insertedNips = collect($employees)->pluck('id')->toArray();
        $insertedNipsMap = array_flip($insertedNips);

        $mansos = [];
        if (file_exists($mansosPath) && ($fh = fopen($mansosPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $nip = trim($line[0]);
                if (isset($insertedNipsMap[$nip])) {
                    $mansos[] = [
                        'employee_id' => $nip,
                        'kompetensi' => trim($line[1]),
                        'nilai' => (int)trim($line[2]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            fclose($fh);
        }

        $teknis = [];
        if (file_exists($teknisPath) && ($fh = fopen($teknisPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $nip = trim($line[0]);
                if (isset($insertedNipsMap[$nip])) {
                    $teknis[] = [
                        'employee_id' => $nip,
                        'kompetensi' => trim($line[1]),
                        'nilai' => (int)trim($line[2]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            fclose($fh);
        }

        $averages = [];
        if (file_exists($avgPath) && ($fh = fopen($avgPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $nip = trim($line[0]);
                if (isset($insertedNipsMap[$nip])) {
                    $averages[] = [
                        'employee_id' => $nip,
                        'nilai_teknis' => (double)trim($line[1]),
                        'nilai_mansoskul' => (double)trim($line[2]),
                        'nilai_potensi' => (int)trim($line[3]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            fclose($fh);
        }

        foreach (array_chunk($mansos, 100) as $chunk) {
            DB::table('compass_nilai_mansos')->insert($chunk);
        }
        foreach (array_chunk($teknis, 100) as $chunk) {
            DB::table('compass_nilai_teknis')->insert($chunk);
        }
        foreach (array_chunk($averages, 100) as $chunk) {
            DB::table('compass_nilai_rata_rata')->insert($chunk);
        }

        // Seed INTERNA & SIMPEL data
        $rencanaPath = base_path('../dummy_data/interna/rencana_diklat.csv');
        $tDiklatPath = base_path('../dummy_data/interna/t_diklat.csv');
        $tPendaftarPath = base_path('../dummy_data/interna/t_pendaftar_all.csv');

        $rencanas = [];
        if (file_exists($rencanaPath) && ($fh = fopen($rencanaPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $rencanas[] = [
                    'kode_pembelajaran' => trim($line[0]),
                    'unit_pengusul' => trim($line[1]),
                    'program_pembelajaran' => trim($line[2]),
                    'judul_program' => trim($line[3]),
                    'jenis_kompetensi' => trim($line[4]),
                    'mulai_e_learning' => trim($line[5]),
                    'selesai_e_learning' => trim($line[6]),
                    'mulai_tatap_muka' => trim($line[7]),
                    'selesai_tatap_muka' => trim($line[8]),
                    'jenis_pembelajaran' => trim($line[9]),
                    'status' => trim($line[10]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            fclose($fh);
        }

        $tDiklats = [];
        if (file_exists($tDiklatPath) && ($fh = fopen($tDiklatPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $tDiklats[] = [
                    'kode_pelatihan' => trim($line[0]),
                    'nama_pelatihan' => trim($line[1]),
                    'jenis_pelatihan' => trim($line[2]),
                    'kompetensi' => trim($line[3]),
                    'tanggal_mulai_daftar' => trim($line[4]),
                    'tanggal_selesai_daftar' => trim($line[5]),
                    'tanggal_mulai' => trim($line[6]),
                    'tanggal_selesai' => trim($line[7]),
                    'batas_daftar' => trim($line[8]),
                    'jam_pelatihan' => (int)trim($line[9]),
                    'jumlah_kuota' => (int)trim($line[10]),
                    'pendaftaran' => trim($line[11]),
                    'syarat_jabatan' => trim($line[12]),
                    'syarat_pendidikan' => trim($line[13]),
                    'skp' => trim($line[14]),
                    'pic' => trim($line[15]),
                    'unit_penyelenggara' => trim($line[16]),
                    'jumlah_pendaftar' => (int)trim($line[17]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            fclose($fh);
        }

        $tPendaftars = [];
        $tDiklatMap = collect($tDiklats)->pluck('kode_pelatihan')->toArray();
        $tDiklatMap = array_flip($tDiklatMap);

        if (file_exists($tPendaftarPath) && ($fh = fopen($tPendaftarPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $kodePelatihan = trim($line[0]);
                $nip = trim($line[2]);
                if (isset($insertedNipsMap[$nip]) && isset($tDiklatMap[$kodePelatihan])) {
                    $tPendaftars[] = [
                        'kode_pelatihan' => $kodePelatihan,
                        'nama' => trim($line[1]),
                        'employee_id' => $nip,
                        'jabatan' => trim($line[3]),
                        'unit_eselon2' => trim($line[4]),
                        'status' => trim($line[5]),
                        'peserta' => isset($line[6]) ? trim($line[6]) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            fclose($fh);
        }

        foreach (array_chunk($rencanas, 100) as $chunk) {
            DB::table('interna_rencana_diklat')->insert($chunk);
        }
        foreach (array_chunk($tDiklats, 100) as $chunk) {
            DB::table('simpel_t_diklat')->insert($chunk);
        }
        foreach (array_chunk($tPendaftars, 100) as $chunk) {
            DB::table('simpel_t_pendaftar')->insert($chunk);
        }

        // Seed SITUBEL data
        $tTubelPath = base_path('../dummy_data/situbel/t_tubel.csv');
        $tTubels = [];
        if (file_exists($tTubelPath) && ($fh = fopen($tTubelPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $nip = trim($line[0]);
                if (isset($insertedNipsMap[$nip])) {
                    $tTubels[] = [
                        'employee_id' => $nip,
                        'nama' => trim($line[1]),
                        'sponsor' => trim($line[2]),
                        'program_studi' => trim($line[3]),
                        'jurusan' => trim($line[4]),
                        'tanggal_mulai_studi' => trim($line[5]),
                        'tanggal_akhir_studi' => trim($line[6]),
                        'no_spptb' => trim($line[7]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            fclose($fh);
        }

        foreach (array_chunk($tTubels, 100) as $chunk) {
            DB::table('situbel_t_tubel')->insert($chunk);
        }

        // 18. Seed Required Mansos
        $reqMansos = [];
        $reqPath = base_path('../dummy_data/compass/req_mansos.csv');
        if (file_exists($reqPath) && ($fh = fopen($reqPath, 'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($line = fgetcsv($fh)) !== false) {
                if (empty(array_filter($line))) continue;
                $reqMansos[] = [
                    'jenjang' => trim($line[0]),
                    'integritas' => (int)trim($line[1]),
                    'kerja_sama' => (int)trim($line[2]),
                    'komunikasi' => (int)trim($line[3]),
                    'orientasi_pada_hasil' => (int)trim($line[4]),
                    'pelayanan_publik' => (int)trim($line[5]),
                    'pengembangan_diri_dan_orang_lain' => (int)trim($line[6]),
                    'mengelola_perubahan' => (int)trim($line[7]),
                    'pengambilan_keputusan' => (int)trim($line[8]),
                    'karakteristik_lintas_manajerial' => (int)trim($line[9]),
                    'kepemimpinan' => (int)trim($line[10]),
                    'komunikasi_trusted_advisor_value_driver' => (int)trim($line[11]),
                    'perekat_bangsa' => (int)trim($line[12]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            fclose($fh);
        }
        DB::table('compass_req_mansos')->insert($reqMansos);

        // Generate dynamic competency gaps based on actual COMPASS scores and standards
        $gaps = [];
        
        // 1. Technical Gaps
        // Optimal (>= 90), Cukup optimal (>= 78 s.d < 90), Kurang optimal (>= 60 s.d < 78), Tidak optimal (< 60)
        // Standard is 78.
        foreach ($teknis as $scoreRow) {
            $score = $scoreRow['nilai'];
            if ($score >= 90) {
                $lvl = 'Optimal';
            } elseif ($score >= 78) {
                $lvl = 'Cukup optimal';
            } elseif ($score >= 60) {
                $lvl = 'Kurang optimal';
            } else {
                $lvl = 'Tidak optimal';
            }
            
            $std = 78;
            $gap = $score < $std ? ($std - $score) : 0;
            
            $gaps[] = [
                'employee_id' => $scoreRow['employee_id'],
                'competency_name' => $scoreRow['kompetensi'],
                'score' => $score,
                'type' => 'Teknis',
                'standard' => $std,
                'gap' => $gap,
                'level' => $lvl,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 2. Mansoskul Gaps
        $mansosStdMap = [];
        foreach ($reqMansos as $row) {
            $mansosStdMap[$row['jenjang']] = $row;
        }

        foreach ($mansos as $scoreRow) {
            $nip = $scoreRow['employee_id'];
            $compName = $scoreRow['kompetensi'];
            $score = $scoreRow['nilai'];
            
            $empRecord = collect($employeesData)->firstWhere('id', $nip);
            $jabatan = $empRecord ? strtolower($empRecord['jabatan']) : '';
            
            $jenjang = 'Auditor Ahli Pertama';
            if (str_contains($jabatan, 'terampil')) $jenjang = 'Auditor Terampil';
            elseif (str_contains($jabatan, 'mahir')) $jenjang = 'Auditor Mahir';
            elseif (str_contains($jabatan, 'penyelia')) $jenjang = 'Auditor Penyelia';
            elseif (str_contains($jabatan, 'ahli pertama') || str_contains($jabatan, 'pertama')) $jenjang = 'Auditor Ahli Pertama';
            elseif (str_contains($jabatan, 'ahli muda') || str_contains($jabatan, 'muda')) $jenjang = 'Auditor Ahli Muda';
            elseif (str_contains($jabatan, 'ahli madya') || str_contains($jabatan, 'madya')) $jenjang = 'Auditor Ahli Madya';
            
            $stdRow = isset($mansosStdMap[$jenjang]) ? $mansosStdMap[$jenjang] : null;
            
            $keyMap = [
                'Integritas' => 'integritas',
                'Kerja sama' => 'kerja_sama',
                'Kerja Sama' => 'kerja_sama',
                'Komunikasi' => 'komunikasi',
                'Orientasi pada Hasil' => 'orientasi_pada_hasil',
                'Pelayanan Publik' => 'pelayanan_publik',
                'Pengembangan Diri dan Orang Lain' => 'pengembangan_diri_dan_orang_lain',
                'Pengembangan Diri & Orang Lain' => 'pengembangan_diri_dan_orang_lain',
                'Mengelola Perubahan' => 'mengelola_perubahan',
                'Pengambilan Keputusan' => 'pengambilan_keputusan',
                'Karakteristik Lintas Manajerial' => 'karakteristik_lintas_manajerial',
                'Kepemimpinan' => 'kepemimpinan',
                'Komunikasi dalam Peran sebagai Trusted Advisor dan Value Driver' => 'komunikasi_trusted_advisor_value_driver',
                'Perekat Bangsa' => 'perekat_bangsa'
            ];
            
            $std = 0;
            if ($stdRow && isset($keyMap[$compName])) {
                $dbKey = $keyMap[$compName];
                $std = $stdRow[$dbKey];
            }
            
            $gap = $score < $std ? ($std - $score) : 0;
            
            if ($score >= $std) {
                $lvl = 'Optimal';
            } elseif ($score == $std - 1) {
                $lvl = 'Cukup optimal';
            } elseif ($score == $std - 2) {
                $lvl = 'Kurang optimal';
            } else {
                $lvl = 'Tidak optimal';
            }
            
            $gaps[] = [
                'employee_id' => $nip,
                'competency_name' => $compName,
                'score' => $score,
                'type' => 'Manajerial',
                'standard' => $std,
                'gap' => $gap,
                'level' => $lvl,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($gaps, 100) as $chunk) {
            DB::table('competency_gaps')->insert($chunk);
        }

        // 3. Seed Hierarchy (Atasan-Bawahan)
        $chains = [
            'doni.gunawan'    => 'dedi.pratama',
            'joko.wibowo'     => 'doni.gunawan',
            'yuni.haryanto'   => 'joko.wibowo',
            'dedi.kusuma'     => 'yuni.haryanto',
            'tuti.susanti'    => 'yuni.haryanto',
            'hendra.sari'     => 'yuni.haryanto',
            'siti.nasution'   => 'yuni.haryanto',
            'sri.widodo'      => 'dedi.pratama',
            'tono.widodo'     => 'sri.widodo',
            'anton.pratama'   => 'tono.widodo',
            'rini.nugroho'    => 'anton.pratama',
            'eko.simanjuntak' => 'anton.pratama',
            'doni.santoso'    => 'anton.pratama',
            'sri.rahmat'      => 'anton.pratama',
            'dina.wijaya'     => 'dedi.pratama',
            'sigit.handayani' => 'dina.wijaya',
            'bayu.pratama'    => 'dina.wijaya',
            'wati.simanjuntak'=> 'bayu.pratama',
            'ratna.sarumpaet' => 'wati.simanjuntak',
            'tuti.wijaya'     => 'wati.simanjuntak',
            'dina.nasution'   => 'wati.simanjuntak',
            'adi.kusuma'      => 'wati.simanjuntak',
            'marni.sari'      => 'wati.simanjuntak',
        ];

        foreach ($chains as $bawahanPrefix => $atasanPrefix) {
            $bawahan = DB::table('users')->whereRaw("LOWER(SUBSTRING_INDEX(email,'@',1)) = ?", [$bawahanPrefix])->first();
            $atasan = DB::table('users')->whereRaw("LOWER(SUBSTRING_INDEX(email,'@',1)) = ?", [$atasanPrefix])->first();
            if ($bawahan && $atasan) {
                DB::table('users')->where('id', $bawahan->id)->update(['atasan_id' => $atasan->id]);
            }
        }

        // Get NIP mapping
        $nipMap = DB::table('users')->pluck('nip', 'name')->toArray();

        $getNip = function($name) use ($nipMap) {
            if (isset($nipMap[$name])) return $nipMap[$name];
            $id = DB::table('employees')->where('name', $name)->value('id');
            if ($id) return $id;
            return DB::table('employees')->value('id') ?: '999999999999999999';
        };

        // 4. Seed Strategic Directions
        $directions = [
            [
                'id' => 'D001',
                'title' => 'Penguatan Analisis Data untuk Layanan SDM',
                'basis' => 'Kinerja unit',
                'context' => 'Layanan SDM membutuhkan dashboard dan analitik yang lebih cepat untuk rapat pimpinan.',
                'competency' => 'Analisis Data',
                'priority' => 'Tinggi',
                'period' => 'TW III 2026',
                'path' => 'Workshop/PKS/CBDA',
                'status' => 'Masuk Demand Pool',
                'follow_up' => 'Belum',
                'unit' => 'Biro Sumber Daya Manusia',
                'sasaran_pegawai' => 'Non-JFA'
            ],
            [
                'id' => 'D002',
                'title' => 'Penguatan Komunikasi Hasil dan Briefing Pimpinan',
                'basis' => 'Sasaran/IKK',
                'context' => 'Kualitas bahan rapat dan nota dinas perlu distandardisasi agar lebih tajam.',
                'competency' => 'Komunikasi Hasil',
                'priority' => 'Sedang',
                'period' => 'TW IV 2026',
                'path' => 'Coaching/PKS',
                'status' => 'Ditetapkan',
                'follow_up' => 'Bangkom Unit',
                'unit' => 'Biro Sumber Daya Manusia',
                'sasaran_pegawai' => 'JFA'
            ],
            [
                'id' => 'D003',
                'title' => 'Sosialisasi Fraud Risk Management Sektor Ekonomi',
                'basis' => 'Risiko unit',
                'context' => 'Pencegahan kecurangan pengelolaan keuangan di direktorat pengawasan.',
                'competency' => 'Fraud Risk Management',
                'priority' => 'Tinggi',
                'period' => 'TW III 2026',
                'path' => 'Sosialisasi / Bimtek',
                'status' => 'Masuk Demand Pool',
                'follow_up' => 'Belum',
                'unit' => 'Direktorat Pengawasan Bidang Ekonomi dan Keuangan',
                'sasaran_pegawai' => 'JFA'
            ]
        ];
        DB::table('strategic_directions')->insert($directions);

        // 5. Seed Bangkom Unit (Rencana & Realisasi)
        $plans = [
            [
                'id' => 'BK0001',
                'nama_kegiatan' => 'Workshop Analisis Data untuk Layanan SDM',
                'unit_pengusul' => 'Biro Sumber Daya Manusia',
                'indikator_kinerja' => 'Kualitas visualisasi laporan analisis data kepegawaian meningkat',
                'jenis_latar_belakang' => 'Hasil Asesmen',
                'latar_belakang' => 'Banyak pegawai di Biro SDM belum mampu menyajikan visualisasi data yang informatif.',
                'tujuan_kegiatan' => 'Meningkatkan keahlian visualisasi data SDM menggunakan dashboard digital.',
                'kompetensi_dasar' => 'Analisis Data',
                'indikator_keberhasilan' => json_encode(['Peserta mampu membuat grafik pivot', 'Peserta menghasilkan dashboard profil pegawai']),
                'penugasan_terkait' => json_encode(['Penyusunan Executive Dashboard Kepegawaian']),
                'metode' => 'Hybrid',
                'tanggal_mulai' => '2026-08-12',
                'tanggal_selesai' => '2026-08-13',
                'jumlah_kelas' => 2,
                'jalur_pembelajaran' => 'Klasikal - Workshop',
                'nilai_anggaran' => 15000000,
                'kriteria_peserta' => json_encode(['Mengelola data kepegawaian', 'Minimal gol II/a']),
                'fasilitator' => 'Pusdiklat BPKP',
                'evaluasi' => 'Ya',
                'jenis_evaluasi' => 1,
                'status' => 'menunggu penetapan',
                'dok_daftar_hadir' => null,
                'dok_notulen' => null,
                'dok_dokumentasi' => null,
                'dok_nilai' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 'BK0002',
                'nama_kegiatan' => 'Coaching Komunikasi Hasil',
                'unit_pengusul' => 'Biro Sumber Daya Manusia',
                'indikator_kinerja' => 'Kecepatan penyusunan brief paper hasil pengawasan meningkat',
                'jenis_latar_belakang' => 'Arahan Strategis',
                'latar_belakang' => 'Arahan pimpinan eselon II untuk memodernisasi cara penyampaian komunikasi laporan.',
                'tujuan_kegiatan' => 'Teknik penyajian brief paper laporan untuk pimpinan.',
                'kompetensi_dasar' => 'Komunikasi',
                'indikator_keberhasilan' => json_encode(['Draft laporan ringkas selesai dalam 1 hari']),
                'penugasan_terkait' => json_encode(['Penyusunan Executive Briefing Bulanan']),
                'metode' => 'Full Tatap Muka',
                'tanggal_mulai' => '2026-08-22',
                'tanggal_selesai' => '2026-08-22',
                'jumlah_kelas' => 1,
                'jalur_pembelajaran' => 'Non Klasikal - Coaching',
                'nilai_anggaran' => 5000000,
                'kriteria_peserta' => json_encode(['Pegawai yang bertugas merangkum hasil kerja unit']),
                'fasilitator' => 'Sri Widodo (Widyaiswara Ahli Utama)',
                'evaluasi' => 'Tidak',
                'jenis_evaluasi' => null,
                'status' => 'ditetapkan',
                'dok_daftar_hadir' => null,
                'dok_notulen' => null,
                'dok_dokumentasi' => null,
                'dok_nilai' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 'BK0003',
                'nama_kegiatan' => 'Sosialisasi Keamanan Data',
                'unit_pengusul' => 'Biro Sumber Daya Manusia',
                'indikator_kinerja' => 'Zero security leakage insiden data kepegawaian',
                'jenis_latar_belakang' => 'Isu Terkini',
                'latar_belakang' => 'Adanya serangan siber marak yang menargetkan data ASN nasional.',
                'tujuan_kegiatan' => 'Meningkatkan kesadaran keamanan data kepegawaian di lingkungan unit kerja.',
                'kompetensi_dasar' => 'Keamanan Data Dasar',
                'indikator_keberhasilan' => json_encode(['Peserta lulus tes post-test keamanan siber', 'Penggunaan password berkala diimplementasikan']),
                'penugasan_terkait' => json_encode(['Penerapan otentikasi dua faktor akun ATLAS']),
                'metode' => 'PJJ',
                'tanggal_mulai' => '2026-08-01',
                'tanggal_selesai' => '2026-08-01',
                'jumlah_kelas' => 1,
                'jalur_pembelajaran' => 'Klasikal - Sosialisasi',
                'nilai_anggaran' => 8000000,
                'kriteria_peserta' => json_encode(['Seluruh staf Biro SDM']),
                'fasilitator' => 'Pusat Informasi Pengawasan BPKP',
                'evaluasi' => 'Ya',
                'jenis_evaluasi' => 2,
                'status' => 'realisasi',
                'dok_daftar_hadir' => 'daftar_hadir_sec.pdf',
                'dok_notulen' => 'notulen_sec.pdf',
                'dok_dokumentasi' => 'dokumentasi_sec.jpg',
                'dok_nilai' => 'nilai_sec.xls',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        DB::table('bangkom_unit')->insert($plans);

        // Seed Realization details for BK0003
        $realizations = [
            [
                'bangkom_unit_id' => 'BK0003',
                'employee_id' => $getNip('Dedi Kusuma'),
                'skor_penyelenggara' => 85,
                'skor_materi' => 90,
                'skor_fasilitator' => 88,
                'skor_pre' => 60,
                'skor_post' => 95,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'bangkom_unit_id' => 'BK0003',
                'employee_id' => $getNip('Hendra Sari'),
                'skor_penyelenggara' => 90,
                'skor_materi' => 85,
                'skor_fasilitator' => 87,
                'skor_pre' => 70,
                'skor_post' => 90,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        DB::table('bangkom_unit_realisasi')->insert($realizations);



        // 7. Seed Audit Trails
        $audits = [
            [
                'time' => '2026-08-03 14:20:00',
                'actor' => 'Sri Widodo',
                'action' => 'Menetapkan arahan strategis',
                'object' => 'Penguatan Analisis Data'
            ],
            [
                'time' => '2026-08-03 14:21:00',
                'actor' => 'Anton Pratama',
                'action' => 'Membuat rencana bangkom',
                'object' => 'Workshop Analisis Data'
            ]
        ];
        DB::table('audit_trails')->insert($audits);

        // 8. Seed Reference & Parameters
        $refs = [
            ['category' => 'IDP Source Type', 'key' => 'assessment', 'value' => 'Penilaian Kompetensi (COMPASS)'],
            ['category' => 'IDP Source Type', 'key' => 'role', 'value' => 'Self-Assessment / Jabatan'],
            ['category' => 'IDP Source Type', 'key' => 'mandatory', 'value' => 'Mandatory Learning'],
            ['category' => 'IDP Source Type', 'key' => 'strategic_direction', 'value' => 'Arahan Strategis Unit'],
            ['category' => 'Jenis Bangkom', 'key' => 'workshop', 'value' => 'Workshop'],
            ['category' => 'Jenis Bangkom', 'key' => 'coaching', 'value' => 'Coaching'],
            ['category' => 'Jenis Bangkom', 'key' => 'mentoring', 'value' => 'Mentoring'],
            ['category' => 'Jenis Bangkom', 'key' => 'pks', 'value' => 'Pendidikan/Pelatihan'],
            ['category' => 'Jenis Bangkom', 'key' => 'library_cafe', 'value' => 'Library Cafe'],
            ['category' => 'Status Arahan', 'key' => 'draft', 'value' => 'Draft'],
            ['category' => 'Status Arahan', 'key' => 'ditetapkan', 'value' => 'Ditetapkan'],
            ['category' => 'Status Arahan', 'key' => 'masuk_demand_pool', 'value' => 'Masuk Demand Pool'],
            ['category' => 'Status Arahan', 'key' => 'ditindaklanjuti', 'value' => 'Ditindaklanjuti'],
            ['category' => 'Role', 'key' => 'staff',       'value' => 'staff'],
            ['category' => 'Role', 'key' => 'pengampuSDM', 'value' => 'perencana'],
            ['category' => 'Role', 'key' => 'eselon2',     'value' => 'verifikator level 2'],
            ['category' => 'Role', 'key' => 'eselon3',     'value' => 'verifikator level 1'],
            ['category' => 'Role', 'key' => 'bangkom',     'value' => 'perencana bangkom'],
            ['category' => 'Role', 'key' => 'kombinasi',   'value' => 'verifikator bangkom level 1'],
            ['category' => 'Role', 'key' => 'karoSDM',     'value' => 'verifikator bangkom level 2'],
            ['category' => 'Role', 'key' => 'deputi',      'value' => 'executive deputi'],
            ['category' => 'Role', 'key' => 'sesma',       'value' => 'executive sesma'],
            ['category' => 'Role', 'key' => 'admin',       'value' => 'admin'],
            ['category' => 'Periode', 'key' => 'tw3_2026', 'value' => 'Triwulan III 2026'],
            ['category' => 'Periode', 'key' => 'tw4_2026', 'value' => 'Triwulan IV 2026'],
            ['category' => 'Periode', 'key' => 'tw1_2027', 'value' => 'Triwulan I 2027'],
            ['category' => 'Unit Kerja', 'key' => 'biro_sdm', 'value' => 'Biro Sumber Daya Manusia'],
            ['category' => 'Unit Kerja', 'key' => 'biro_mktk', 'value' => 'Biro Manajemen Kinerja, Organisasi, dan Tata Kelola'],
            ['category' => 'Unit Kerja', 'key' => 'direktorat_ekonomi', 'value' => 'Direktorat Pengawasan Bidang Ekonomi dan Keuangan'],
            ['category' => 'Unit Kerja', 'key' => 'deputi_ppkd', 'value' => 'Deputi Bidang PPKD'],
            ['category' => 'Unit Kerja', 'key' => 'deputi_investigasi', 'value' => 'Deputi Bidang Investigasi'],
            ['category' => 'Unit Kerja', 'key' => 'perwakilan_banten', 'value' => 'Perwakilan Provinsi Banten'],
            ['category' => 'Display Parameter', 'key' => 'cols', 'value' => 'Default Columns']
        ];
        DB::table('reference_parameters')->insert($refs);

        // Sync IDP Coverage for all employees using Exact Competency Matching formula
        \App\Helpers\IdpCoverageHelper::syncAllEmployeesCoverage();
    }
}
