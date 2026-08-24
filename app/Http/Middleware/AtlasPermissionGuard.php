<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtlasPermissionGuard
{
    /**
     * Handle an incoming request.
     *
     * Role codes (new naming):
     *   staff       – Pegawai / Fungsional
     *   pengampuSDM – Pengampu SDM Unit / Subkoordinator Kepegawaian / Kasubbag Umum (Eselon IV)
     *   eselon2     – Kepala Biro, Kepala Pusat, Inspektur, Direktur, Kepala Perwakilan
     *   eselon3     – Koordinator (Biro/Pusat/Perwakilan) dan Kepala Bagian Umum
     *                 * Subtype 'Koordinator Pengawasan' (jabatan=korwas): view-only pada menu approval unit
     *   karoSDM     – Kepala Biro SDM (akses nasional + unit Biro SDM)
     *   kombinasi   – Koordinator Pengembangan Kompetensi, Penilaian Kompetensi, dan Pembinaan SDM
     *   bangkom     – Subkoordinator Pengembangan Kompetensi (operasional bangkom nasional)
     *   deputi      – Deputi Kepala Eselon I
     *   sesma       – Sekretaris Utama
     *   admin       – Admin Sistem
     */
    public function handle(Request $request, Closure $next, $permissionGroup = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user       = Auth::user();
        $activeRole = session('active_role', $user->role);

        if ($permissionGroup) {
            $permissions = [
                // All roles can access pegawai/staff pages (individual IDP, calendar, etc.)
                'pegawai'    => ['staff','pengampuSDM','eselon2','eselon3','karoSDM','kombinasi','bangkom','deputi','sesma','admin'],
                // Pengampu SDM unit + bangkom (Subkoor bangkom nasional) + admin + karoSDM + kombinasi
                'pengampu'   => ['pengampuSDM','bangkom','admin','karoSDM','kombinasi'],
                // All unit-level heads: Eselon II and Eselon III (same menus)
                'kepalaUnit' => ['eselon2','eselon3','kombinasi','karoSDM','admin','bangkom'],
                // Executive dashboard: bangkom, kombinasi, karoSDM, deputi, sesma, admin
                'executive'  => ['bangkom','kombinasi','karoSDM','deputi','sesma','admin'],
                // Admin only
                'admin'      => ['admin'],
            ];

            $allowed = $permissions[$permissionGroup] ?? [];
            if (!in_array($activeRole, $allowed)) {
                return redirect()->route('home')
                    ->with('error', 'Akses ditolak. Halaman ini bukan kewenangan peran Anda.');
            }
            return $next($request);
        }

        // No permissionGroup: allow any authenticated user (soft guard)
        return $next($request);
    }
}
