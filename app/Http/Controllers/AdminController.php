<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * User CRUD listing.
     */
    public function usersIndex(Request $request)
    {
        $users = User::all();
        $allRoles = ['admin', 'executive', 'pengampuSDM', 'eselon2', 'eselon3', 'staff'];
        return view('admin.user_management', compact('users', 'allRoles'));
    }

    /**
     * Store new User.
     */
    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'scope' => 'required|string',
            'password' => 'nullable|string|min:6',
            'jabatan' => 'nullable|string|max:200',
            'unit_eselon2' => 'nullable|string|max:200',
            'unit_eselon1' => 'nullable|string|max:200',
        ]);

        // Enforce max 1 perencana, verifikator level 1, and verifikator level 2 per unit eselon 2
        if (in_array($request->role, ['pengampuSDM', 'eselon3', 'eselon2']) && $request->filled('unit_eselon2')) {
            $exists = User::where('role', $request->role)
                ->where('unit_eselon2', $request->unit_eselon2)
                ->exists();
            if ($exists) {
                $roleLabel = [
                    'pengampuSDM' => 'perencana',
                    'eselon3' => 'verifikator level 1',
                    'eselon2' => 'verifikator level 2'
                ][$request->role];
                return back()->with('error', 'Unit kerja Eselon II "' . $request->unit_eselon2 . '" sudah memiliki ' . $roleLabel . '.')->withInput();
            }
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        } else {
            $user->password = Hash::make('password'); // default password
        }
        $user->role = $request->role;
        
        // Biro SDM managers (non-staff) automatically get scope 'nasional'
        $scope = $request->scope;
        if ($request->unit_eselon2 === 'Biro Sumber Daya Manusia' && $request->role !== 'staff') {
            $scope = 'nasional';
        }
        $user->scope = $scope;
        
        $user->mode_individu = $request->has('mode_individu');
        $user->nip = null; // Removed NIP association from form
        $user->jabatan = $request->jabatan;
        $user->unit_eselon2 = $request->unit_eselon2;
        $user->unit_eselon1 = $request->unit_eselon1;
        $user->save();

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Tambah User',
            'object' => $request->name . ' (Role: ' . $request->role . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'User ' . $request->name . ' berhasil ditambahkan.');
    }

    /**
     * Update User.
     */
    public function usersUpdate($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|string',
            'scope' => 'required|string',
            'password' => 'nullable|string|min:6',
            'jabatan' => 'nullable|string|max:200',
            'unit_eselon2' => 'nullable|string|max:200',
            'unit_eselon1' => 'nullable|string|max:200',
        ]);

        // Enforce max 1 perencana, verifikator level 1, and verifikator level 2 per unit eselon 2
        if (in_array($request->role, ['pengampuSDM', 'eselon3', 'eselon2']) && $request->filled('unit_eselon2')) {
            $exists = User::where('role', $request->role)
                ->where('unit_eselon2', $request->unit_eselon2)
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                $roleLabel = [
                    'pengampuSDM' => 'perencana',
                    'eselon3' => 'verifikator level 1',
                    'eselon2' => 'verifikator level 2'
                ][$request->role];
                return back()->with('error', 'Unit kerja Eselon II "' . $request->unit_eselon2 . '" sudah memiliki ' . $roleLabel . '.')->withInput();
            }
        }

        $user = User::findOrFail($id);
        
        $oldRole = $user->role;
        $oldScope = $user->scope;

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role;
        
        // Biro SDM managers (non-staff) automatically get scope 'nasional'
        $scope = $request->scope;
        if ($request->unit_eselon2 === 'Biro Sumber Daya Manusia' && $request->role !== 'staff') {
            $scope = 'nasional';
        }
        $user->scope = $scope;
        
        $user->mode_individu = $request->has('mode_individu');
        $user->jabatan = $request->jabatan;
        $user->unit_eselon2 = $request->unit_eselon2;
        $user->unit_eselon1 = $request->unit_eselon1;
        $user->save();

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Update User',
            'object' => $request->name . ' (Ubah role: ' . $oldRole . ' -> ' . $request->role . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'User ' . $request->name . ' berhasil diupdate.');
    }

    /**
     * Delete User.
     */
    public function usersDelete($id)
    {
        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Hapus User',
            'object' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'User ' . $name . ' berhasil dihapus.');
    }

    /**
     * SETARA Integration monitor.
     */
    public function setaraIndex()
    {
        $datasets = [
            ['id' => 'smile', 'name' => 'SMILE (HR Master Data)', 'app' => 'SMILE', 'refresh' => '10 Aug 2026 08:00', 'status' => 'Sehat', 'issue' => 'Tidak ada'],
            ['id' => 'compass', 'name' => 'COMPASS (Assessment & Gap)', 'app' => 'COMPASS', 'refresh' => '09 Aug 2026 17:30', 'status' => 'Sehat', 'issue' => 'Tidak ada'],
            ['id' => 'simpel', 'name' => 'SIMPEL (Nominasi Pelatihan)', 'app' => 'SIMPEL', 'refresh' => '10 Aug 2026 08:15', 'status' => 'Sehat', 'issue' => 'Tidak ada'],
            ['id' => 'interna', 'name' => 'INTERNA (Katalog Program)', 'app' => 'INTERNA', 'refresh' => '10 Aug 2026 08:15', 'status' => 'Sehat', 'issue' => 'Tidak ada'],
            ['id' => 'situbel', 'name' => 'SITUBEL (Tugas Belajar)', 'app' => 'SITUBEL', 'refresh' => '08 Aug 2026 10:00', 'status' => 'Sehat', 'issue' => 'Tidak ada']
        ];
        return view('admin.setara', compact('datasets'));
    }

    /**
     * Refresh Dataset.
     */
    public function setaraRefresh($dataset)
    {
        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Koreksi/Refresh SETARA',
            'object' => 'Dataset: ' . strtoupper($dataset),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Pemicu sinkronisasi dataset ' . strtoupper($dataset) . ' berhasil dijalankan via API Gateway SETARA.');
    }

    /**
     * Audit Trail page.
     */
    public function auditTrailIndex()
    {
        $audits = DB::table('audit_trails')->orderByDesc('id')->get();
        return view('admin.audit_log', compact('audits'));
    }

    /**
     * Reference & Parameters.
     */
    public function referenceIndex()
    {
        $references = DB::table('reference_parameters')->get();
        return view('admin.reference', compact('references'));
    }

    /**
     * Store Reference.
     */
    public function referenceStore(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'key' => 'required|string',
            'value' => 'required|string'
        ]);

        DB::table('reference_parameters')->insert([
            'category' => $request->category,
            'key' => $request->key,
            'value' => $request->value,
            'owner' => Auth::user()->scope ?? 'Pusat',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Tambah Referensi',
            'object' => $request->category . ' (' . $request->key . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Parameter referensi berhasil disimpan.');
    }

    /**
     * Delete Reference.
     */
    public function referenceDelete($id)
    {
        $ref = DB::table('reference_parameters')->where('id', $id)->first();
        if (!$ref) return back()->with('error', 'Referensi tidak ditemukan.');

        DB::table('reference_parameters')->where('id', $id)->delete();

        // Audit Trail
        DB::table('audit_trails')->insert([
            'time' => now()->format('Y-m-d H:i:s'),
            'actor' => Auth::user()->name,
            'action' => 'Hapus Referensi',
            'object' => $ref->category . ' (' . $ref->key . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Parameter referensi berhasil dihapus.');
    }

    /**
     * Test API SMILE integration (Opsi 1: Display Only)
     */
    public function testApiSmile()
    {
        $apiUrl = env('SMILE_API_URL');
        $apiToken = env('SMILE_API_TOKEN');
        $apiData = [];
        $status = 'Belum Dikonfigurasi (Cek file .env)';

        if ($apiUrl && $apiToken) {
            try {
                // Catatan: Jika URL aslinya sudah ada, buka komentar kode di bawah ini:
                
                // $response = \Illuminate\Support\Facades\Http::withToken($apiToken)->get($apiUrl);
                // if ($response->successful()) {
                //     $apiData = $response->json();
                //     $status = 'Sukses Terhubung';
                // } else {
                //     $status = 'Gagal: ' . $response->status();
                // }
                
                // Ini adalah data MOCK (Palsu) sebagai contoh tampilan jika API berhasil dipanggil
                $status = 'Koneksi Siap (Simulasi Data ditampilkan)';
                $apiData = [
                    ['nip_karyawan' => '19800101', 'nama_lengkap_peg' => 'Dummy Budi Santoso', 'posisi_saat_ini' => 'Manager IT'],
                    ['nip_karyawan' => '19920515', 'nama_lengkap_peg' => 'Dummy Siti Aminah', 'posisi_saat_ini' => 'Staff Keuangan'],
                    ['nip_karyawan' => '19851212', 'nama_lengkap_peg' => 'Dummy Anton Wibowo', 'posisi_saat_ini' => 'Kepala Unit']
                ];
            } catch (\Exception $e) {
                $status = 'Error: ' . $e->getMessage();
            }
        }

        return view('admin.test_api_smile', compact('apiUrl', 'apiToken', 'status', 'apiData'));
    }
}
