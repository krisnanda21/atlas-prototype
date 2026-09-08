@extends('layouts.app')
@section('title', 'Certification & Diklat Control')
@section('header_title', 'Certification & Diklat Control')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">🏅 Certification Control</h1>
</div>

<div style="display:grid;grid-template-columns:1fr;gap:24px;">
    {{-- Card: Pencocokan Data SIMPEL & SMILE --}}
    <div class="card">
        <div class="card-title" style="font-size:18px">🔗 Daftar Kegiatan Pelatihan Sertifikasi</div>

        {{-- Filter & Search --}}
        <div style="background:#F8FAFC; border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:12px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
            <form action="{{ route('pengampu.certification') }}" method="GET" style="display:flex; gap:10px; width:100%; align-items:center;">
                <div style="font-weight:600; color:var(--text-secondary);">🔍 Cari:</div>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" style="width:300px; height:32px; padding:0 8px; font-size:12px;" placeholder="Nama Pelatihan / Sertifikasi">

                <button type="submit" class="btn btn-sm btn-primary" style="height:32px; padding:0 12px; font-size:12px;">Terapkan</button>
                <a href="{{ route('pengampu.certification') }}" class="btn btn-sm btn-neutral" style="height:32px; padding:0 10px; font-size:12px; line-height:30px;">✕ Reset</a>
            </form>
        </div>

        @if(session('success'))
        <div style="background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); color:#4ade80; padding:12px; border-radius:6px; margin-bottom:16px; font-size:13px;">
            {{ session('success') }}
        </div>
        @endif

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th style="width:140px;">Kode Pelatihan</th>
                        <th>Nama Kegiatan</th>
                        <th style="width:180px;">Tanggal</th>
                        <th style="width:180px; text-align:center;">Total Peserta</th>
                        <th style="width:150px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kegiatans as $k)
                    <tr>
                        <td><code>{{ $k->kode_pelatihan }}</code></td>
                        <td>
                            <strong>{{ $k->nama_pelatihan }}</strong>
                        </td>
                        <td>
                            <div class="text-xs">{{ \Carbon\Carbon::parse($k->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($k->tanggal_selesai)->format('d M Y') }}</div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge badge-neutral" style="color:white;">{{ $k->total_peserta }} Peserta</span>
                        </td>
                        <td style="text-align:center;">
                            <button type="button" class="btn btn-sm btn-neutral" onclick="openPesertaModal('{{ $k->kode_pelatihan }}', '{{ addslashes($k->nama_pelatihan) }}')">
                                👥 Lihat Daftar Pegawai
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-muted text-sm" style="text-align:center;padding:24px;">Tidak ada data kegiatan sertifikasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($kegiatans->hasPages())
        <div style="margin-top:16px; display:flex; justify-content:flex-end;">
            {{ $kegiatans->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal Daftar Peserta --}}
<div id="modal-peserta" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9998;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:1000px;max-height:85vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.12);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:0;">👥 Daftar Peserta & Pencocokan Sertifikat</h2>
            <button onclick="document.getElementById('modal-peserta').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        
        <div style="margin-bottom:16px;">
            <div style="font-size:12px; color:var(--text-secondary);">Kegiatan:</div>
            <div id="peserta-nama-pelatihan" style="font-size:15px; font-weight:600; color:#fff;"></div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.1); color:var(--text-secondary); font-size:12px;">
                        <th style="padding:8px 10px; text-align:left; width:120px;">NIP</th>
                        <th style="padding:8px 10px; text-align:left;">Nama Pegawai</th>
                        <th style="padding:8px 10px; text-align:left; width:250px;">Status Sertifikat (SMILE)</th>
                        <th style="padding:8px 10px; text-align:center; width:100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="peserta-tbody">
                    <tr><td colspan="4" style="text-align:center; padding:20px;">Memuat data peserta...</td></tr>
                </tbody>
            </table>
        </div>
        
        <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--divider);padding-top:16px; margin-top:20px;">
            <button type="button" onclick="document.getElementById('modal-peserta').style.display='none'" class="btn btn-neutral" style="padding:8px 18px;">Tutup</button>
        </div>
    </div>
</div>

{{-- Modal Tautkan Manual (Diatas Modal Peserta) --}}
<div id="modal-map" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.9);backdrop-filter:blur(2px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:500px;box-shadow:0 8px 32px rgba(0,0,0,0.12);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:0;">🔗 Tautkan Sertifikasi Manual</h2>
            <button onclick="document.getElementById('modal-map').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        
        <form action="{{ route('pengampu.mapCertification') }}" method="POST">
            @csrf
            <input type="hidden" name="kode_pelatihan" id="map-kode">
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; color:var(--text-secondary); margin-bottom:4px;">Pelatihan dari SIMPEL</label>
                <div id="map-simpel-name" style="background:#F8FAFC; border:1px solid rgba(255,255,255,0.05); border-radius:6px; padding:10px; color:#fff; font-size:13px; font-weight:600;"></div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; color:var(--text-secondary); margin-bottom:4px;">Nama Pegawai</label>
                <div id="map-emp-name" style="color:#fff; font-size:13px; font-weight:600;"></div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; color:var(--text-secondary); margin-bottom:4px;">Pilih Sertifikat dari Riwayat SMILE Pegawai Ini</label>
                <select name="sertifikasi_name" id="map-smile-select" class="form-control" style="width:100%; height:40px; font-size:13px;" required>
                    <!-- Options populated by JS -->
                </select>
                <div style="font-size:11px; color:var(--text-secondary); margin-top:6px;">
                    *Sertifikat yang dipilih akan dikaitkan dengan pelatihan SIMPEL di atas untuk pencocokan di masa depan.
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--divider);padding-top:16px;">
                <button type="button" onclick="document.getElementById('modal-map').style.display='none'" class="btn btn-neutral" style="padding:8px 18px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 18px;">Simpan Tautan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPelatihanName = '';

function openPesertaModal(kode, namaPelatihan) {
    document.getElementById('peserta-nama-pelatihan').textContent = namaPelatihan;
    currentPelatihanName = namaPelatihan;
    document.getElementById('modal-peserta').style.display = 'flex';
    
    const tbody = document.getElementById('peserta-tbody');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;"><div class="spinner-border spinner-border-sm" role="status"></div> Memuat data peserta...</td></tr>';
    
    fetch(`/pengampu/certification/${kode}/peserta`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:var(--text-secondary);">Tidak ada peserta dari unit Anda pada kegiatan ini.</td></tr>';
                return;
            }
            
            let rows = '';
            data.forEach(p => {
                let statusBadge = '';
                let actionBtn = '';
                
                if (p.match_status === 'Mapped') {
                    statusBadge = `<span class="badge badge-success">✅ Mapped (Manual)</span><div class="text-muted text-xs" style="margin-top:4px;">${p.matched_sertifikat}</div>`;
                    actionBtn = `<span class="text-muted" style="font-size:12px;">Selesai</span>`;
                } else if (p.match_status === 'Auto-Match') {
                    statusBadge = `<span class="badge badge-info">⚡ Auto-Match</span><div class="text-muted text-xs" style="margin-top:4px;">${p.matched_sertifikat}</div>`;
                    const sertifikasisJson = encodeURIComponent(JSON.stringify(p.sertifikasis));
                    actionBtn = `<button type="button" class="btn btn-sm btn-neutral" style="font-size:11px;" onclick="openManualMap('${kode}', decodeURIComponent('${sertifikasisJson}'), '${p.nama_pegawai}')">🔗 Ubah Tautan</button>`;
                } else {
                    statusBadge = `<span class="badge badge-danger">❌ Belum Terdeteksi</span>`;
                    const sertifikasisJson = encodeURIComponent(JSON.stringify(p.sertifikasis));
                    actionBtn = `<button type="button" class="btn btn-sm btn-primary" style="font-size:11px;" onclick="openManualMap('${kode}', decodeURIComponent('${sertifikasisJson}'), '${p.nama_pegawai}')">🔗 Tautkan</button>`;
                }
                
                rows += `
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px;">
                        <td style="padding:10px;"><code>${p.employee_id}</code></td>
                        <td style="padding:10px;">
                            <strong>${p.nama_pegawai}</strong>
                            <div class="text-muted text-xs">${p.jabatan || '-'}</div>
                        </td>
                        <td style="padding:10px;">${statusBadge}</td>
                        <td style="padding:10px; text-align:center;">${actionBtn}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = rows;
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:#ef4444;">Gagal memuat data peserta.</td></tr>';
        });
}

function openManualMap(kode, sertifikasisJson, empName) {
    document.getElementById('map-kode').value = kode;
    document.getElementById('map-simpel-name').textContent = currentPelatihanName;
    document.getElementById('map-emp-name').textContent = empName;
    
    const sertifikasis = JSON.parse(sertifikasisJson);
    const validSertifikasis = sertifikasis.filter(s => s.dokumen && s.dokumen.trim() !== '' && s.dokumen.trim() !== '-');

    const select = document.getElementById('map-smile-select');
    select.innerHTML = '<option value="">-- Pilih Sertifikat --</option>';
    
    if (validSertifikasis.length > 0) {
        validSertifikasis.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.nama_sertifikasi;
            opt.textContent = s.nama_sertifikasi;
            select.appendChild(opt);
        });
    } else {
        select.innerHTML = '<option value="">-- Pegawai belum mengunggah dokumen sertifikat SMILE --</option>';
    }
    
    document.getElementById('modal-map').style.display = 'flex';
}
</script>
@endpush
