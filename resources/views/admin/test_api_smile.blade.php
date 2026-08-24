@extends('layouts.app')

@section('title', 'Uji Coba API SMILE - ATLAS')

@section('content')
<div class="content-header">
    <div class="header-title">
        <h1>Uji Coba API SMILE</h1>
        <p class="subtitle">Halaman khusus untuk menguji koneksi dan format respons dari API SMILE (Display Only)</p>
    </div>
</div>

<div class="dashboard-content" style="padding: 24px; max-width: 1000px; margin: 0 auto;">
    
    <!-- Status Card -->
    <div class="stat-card" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid {{ $apiUrl && $apiToken ? 'var(--primary)' : 'var(--danger)' }};">
        <div>
            <h3 style="font-size: 14px; color: var(--text-secondary); margin-bottom: 4px;">Status Koneksi</h3>
            <div style="font-size: 18px; font-weight: 600; color: var(--text-primary);">{{ $status }}</div>
            
            <div style="margin-top: 12px; font-size: 13px; color: var(--text-secondary);">
                <strong>Endpoint URL:</strong> {{ $apiUrl ? $apiUrl : 'Belum di-set di .env (SMILE_API_URL)' }} <br>
                <strong>API Token:</strong> {{ $apiToken ? '****' . substr($apiToken, -4) . ' (Terdeteksi)' : 'Belum di-set di .env (SMILE_API_TOKEN)' }}
            </div>
        </div>
        
        <div>
            <a href="{{ route('admin.testApiSmile') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>🔄</span> Coba Panggil Ulang
            </a>
        </div>
    </div>

    <!-- Peringatan -->
    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; padding: 16px; margin-bottom: 24px; display: flex; gap: 12px;">
        <div style="font-size: 20px;">ℹ️</div>
        <div>
            <strong style="color: var(--primary); display: block; margin-bottom: 4px;">Catatan Mode "Display Only"</strong>
            <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                Halaman ini hanya mengambil data dari server SMILE dan menampilkannya di layar (RAM). <strong>Tidak ada data yang disimpan ke database ATLAS</strong>. Ini memastikan database dummy lokal Anda tetap 100% aman dan tidak mengalami kerusakan relasi (error).
            </p>
        </div>
    </div>

    <!-- Data Table -->
    @if($apiUrl && $apiToken)
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background: #f8fafc;">
                <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Hasil Response API (Data Mentah / Mapping)</h2>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #f1f5f9; text-align: left;">
                            <th style="padding: 12px 16px; font-weight: 600; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">NIP Karyawan</th>
                            <th style="padding: 12px 16px; font-weight: 600; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Nama Lengkap</th>
                            <th style="padding: 12px 16px; font-weight: 600; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Posisi / Jabatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apiData as $data)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 12px 16px;">{{ $data['nip_karyawan'] ?? '-' }}</td>
                                <td style="padding: 12px 16px; font-weight: 500;">{{ $data['nama_lengkap_peg'] ?? '-' }}</td>
                                <td style="padding: 12px 16px;">{{ $data['posisi_saat_ini'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding: 24px; text-align: center; color: var(--text-secondary);">
                                    Tidak ada data dikembalikan dari API.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px 20px; background: #f8fafc; font-size: 12px; color: var(--text-secondary); text-align: right;">
                Menampilkan {{ count($apiData) }} baris data dari memori.
            </div>
        </div>
    @endif
</div>
@endsection
