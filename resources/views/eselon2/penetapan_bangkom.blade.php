@extends('layouts.app')
@section('title', 'Penetapan Bangkom Unit')
@section('header_title', 'Penetapan Rencana Bangkom Unit')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px;">✅ Penetapan Bangkom Unit</h1>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:var(--danger);margin-bottom:16px;">❌ {{ session('error') }}</div>
@endif

@php
    $activeRole = session('active_role', auth()->user()->role);

    // Verifikator Level 1: eselon3, kombinasi (menetapkan rencana bangkom & pembatalan)
    $canVerifLevel1 = in_array($activeRole, ['eselon3', 'kombinasi', 'admin']);

    // Verifikator Level 2: eselon2, karoSDM (menyetujui realisasi bangkom)
    $canVerifLevel2 = in_array($activeRole, ['eselon2', 'karoSDM', 'admin']);
@endphp

{{-- Combined plans table card --}}
<div class="card">
    <div class="card-title" style="font-size:18px;">📋 Daftar Rencana & Penetapan Bangkom Unit</div>

    {{-- Filter & Sort Bar --}}
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
        <div style="font-weight:600; color:var(--text-secondary);">🔍 Cari:</div>
        <input type="text" id="search-title" class="form-control" style="width:200px; height:32px; padding:0 8px; font-size:12px;" placeholder="Cari nama kegiatan..." onkeyup="filterAndSortPlans()">
        <div style="font-weight:600; color:var(--text-secondary); margin-left:8px;">Filter:</div>
        <select id="filter-metode" class="form-control" style="width:140px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortPlans()">
            <option value="">Semua Metode</option>
            <option value="Luring">Luring</option>
            <option value="Daring">Daring</option>
        </select>
        <select id="filter-status" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortPlans()">
            <option value="">Semua Status</option>
            <option value="Draft">Draft</option>
            <option value="Menunggu Penetapan">Menunggu Penetapan</option>
            <option value="Ditetapkan">Ditetapkan</option>
            <option value="Perlu Revisi">Perlu Revisi</option>
            <option value="Realisasi">Realisasi</option>
            <option value="Persetujuan Realisasi">Persetujuan Realisasi</option>
            <option value="Realisasi Disetujui">Realisasi Disetujui</option>
        </select>
        <select id="filter-competency" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortPlans()">
            <option value="">Semua Kompetensi</option>
            <option value="Analisis Data">Analisis Data</option>
            <option value="Komunikasi">Komunikasi</option>
            <option value="Komunikasi Hasil">Komunikasi Hasil</option>
            <option value="Fraud Risk Management">Fraud Risk Management</option>
            <option value="Keamanan Data Dasar">Keamanan Data Dasar</option>
        </select>
        <div style="font-weight:600; color:var(--text-secondary); margin-left:12px;">⇅ Urutkan:</div>
        <select id="sort-by" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortPlans()">
            <option value="">Default</option>
            <option value="title-asc">Nama Kegiatan (A-Z)</option>
            <option value="title-desc">Nama Kegiatan (Z-A)</option>
            <option value="jp-desc">JP: Tinggi-Rendah</option>
            <option value="jp-asc">JP: Rendah-Tinggi</option>
        </select>
    </div>

    <table id="table-plans">
        <thead>
            <tr>
                <th>Nama Kegiatan</th>
                <th>Kompetensi</th>
                <th>Jalur Pembelajaran</th>
                <th>Tanggal</th>
                <th>JP</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $p)
            @php
                $statusLower = strtolower($p->status);
                $statusMapping = [
                    'draft' => 'Draft',
                    'menunggu penetapan' => 'Menunggu Penetapan',
                    'ditetapkan' => 'Ditetapkan',
                    'revisi' => 'Perlu Revisi',
                    'pembatalan diajukan' => 'Pembatalan Diajukan',
                    'pembatalan disetujui' => 'Pembatalan Disetujui',
                    'realisasi' => 'Realisasi',
                    'persetujuan realisasi' => 'Persetujuan Realisasi',
                    'realisasi disetujui' => 'Realisasi Disetujui',
                    'selesai' => 'Selesai'
                ];
                $displayStatus = $statusMapping[$statusLower] ?? ucwords($p->status);

                $statusColors = [
                    'Draft' => 'badge-neutral',
                    'Menunggu Penetapan' => 'badge-warning',
                    'Ditetapkan' => 'badge-success',
                    'Perlu Revisi' => 'badge-danger',
                    'Pembatalan Diajukan' => 'badge-warning',
                    'Pembatalan Disetujui' => 'badge-neutral',
                    'Realisasi' => 'badge-info',
                    'Persetujuan Realisasi' => 'badge-warning',
                    'Realisasi Disetujui' => 'badge-success',
                    'Selesai' => 'badge-success'
                ];
                $sc = $statusColors[$displayStatus] ?? 'badge-neutral';
            @endphp
            <tr class="plan-row" data-title="{{ strtolower($p->nama_kegiatan) }}" data-metode="{{ $p->metode }}" data-status="{{ $displayStatus }}" data-jp="{{ $p->jp }}" data-competency="{{ $p->kompetensi_dasar }}">
                <td>
                    <strong>{{ $p->nama_kegiatan }}</strong><br>
                    <span class="text-muted text-sm">{{ $p->unit_pengusul }}</span>
                </td>
                <td>{{ $p->kompetensi_dasar }}</td>
                <td>{{ $p->jalur_pembelajaran }}</td>
                <td>{{ $p->tanggal_mulai }}</td>
                <td>{{ $p->jp }} JP</td>
                <td>
                    <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                </td>
                <td>
                    <div style="display:flex; gap:4px; flex-direction:column;">
                        <button type="button" class="btn btn-sm btn-neutral" style="width:100%;" onclick="showDetail({{ json_encode($p) }})">🔍 Detail</button>
                        
                        @if($canVerifLevel1 && $statusLower === 'menunggu penetapan')
                        <button type="button" class="btn btn-sm btn-success" style="width:100%;" onclick="showConfirmApprovePlan('{{ $p->id }}', '{{ addslashes($p->nama_kegiatan) }}')">✅ Tetapkan</button>
                        <button type="button" class="btn btn-sm btn-danger" style="width:100%;" onclick="openRejectPlanModal('{{ $p->id }}', '{{ addslashes($p->nama_kegiatan) }}')">Perlu Revisi</button>
                        @endif

                        @if(in_array($statusLower, ['realisasi', 'persetujuan realisasi', 'realisasi disetujui']))
                        <button type="button" class="btn btn-sm btn-info" style="width:100%;background:#3b82f6;color:#fff;font-weight:600;" onclick="showDetailRealisasi({{ json_encode($p) }})">📋 Detail Realisasi</button>
                        @endif

                        @if($canVerifLevel2 && $statusLower === 'persetujuan realisasi')
                        <button type="button" class="btn btn-sm btn-success" style="width:100%;background:#10b981;color:#fff;font-weight:600;" onclick="showConfirmApproveRealisasi('{{ $p->id }}', '{{ addslashes($p->nama_kegiatan) }}')">✅ Approve Realisasi</button>
                        <button type="button" class="btn btn-sm btn-danger" style="width:100%;" onclick="openRejectRealisasiModal('{{ $p->id }}', '{{ addslashes($p->nama_kegiatan) }}')">↩️ Kembalikan Realisasi</button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted text-sm" style="text-align:center;padding:24px;">Belum ada rencana bangkom dari Pengampu SDM.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div id="pagination-controls" style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:#F8FAFC; border-top:1px solid #E2E8F0; border-radius:0 0 8px 8px;">
        <div id="pagination-info" style="font-size:12px; color:var(--text-secondary);">Showing 0 to 0 of 0 entries</div>
        <div style="display:flex; gap:4px;" id="pagination-buttons"></div>
    </div>
</div>

{{-- Modal Detail --}}
<div id="modal-detail" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:600px;max-width:95%;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="font-size:16px;font-weight:600;">📋 Detail Rencana Kegiatan</h2>
            <button onclick="document.getElementById('modal-detail').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <div id="detail-content" style="margin-bottom:20px;"></div>
        <div style="display:flex;justify-content:flex-end;">
            <button type="button" onclick="document.getElementById('modal-detail').style.display='none'" class="btn btn-neutral">Tutup</button>
        </div>
    </div>
</div>

{{-- Modal Reject / Revisi Plan --}}
<div id="modal-reject-plan" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:500px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:var(--text-primary);margin:0;">❌ Pengembalian / Revisi Rencana Bangkom</h2>
            <button onclick="document.getElementById('modal-reject-plan').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="reject-plan-name" style="margin-bottom:16px;color:var(--text-primary);font-weight:600;padding:8px;background:#F1F5F9;border-radius:6px;"></p>
        <form method="POST" id="form-reject-plan" style="text-align:left;">
            @csrf
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Alasan Penolakan / Catatan Perbaikan *</label>
                <textarea name="reject_reason" class="form-control" rows="4" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;resize:vertical;" required placeholder="Tuliskan catatan perbaikan atau alasan penolakan..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-reject-plan').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
                <button type="submit" class="btn btn-danger" style="padding:8px 16px;border-radius:6px;font-weight:600;cursor:pointer;background:#ef4444;color:#fff;border:none;">Kirim Catatan Perbaikan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Tetapkan Rencana --}}
<div id="modal-confirm-approve-plan" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:480px;box-shadow:0 8px 32px rgba(0,0,0,0.12);text-align:left;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:var(--text-primary);margin:0;">✅ Konfirmasi Penetapan Rencana Bangkom</h2>
            <button onclick="document.getElementById('modal-confirm-approve-plan').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="approve-plan-name" style="margin-bottom:16px;color:var(--text-primary);font-weight:600;padding:10px;background:#F1F5F9;border-radius:6px;"></p>
        <p class="text-sm text-muted" style="margin-bottom:20px;line-height:1.5;">Apakah Anda yakin ingin menetapkan rencana kegiatan bangkom ini? Status kegiatan akan berubah menjadi <strong>Ditetapkan</strong> dan email verifikator Anda akan tercatat.</p>
        <form method="POST" id="form-approve-plan" style="display:flex;gap:10px;justify-content:flex-end;">
            @csrf
            <button type="button" onclick="document.getElementById('modal-confirm-approve-plan').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
            <button type="submit" class="btn btn-success" style="padding:8px 18px;border-radius:6px;font-weight:600;cursor:pointer;background:#10b981;color:#fff;border:none;">Ya, Tetapkan Rencana</button>
        </form>
    </div>
</div>
{{-- Modal Detail Realisasi --}}
<div id="modal-detail-realisasi" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:850px;max-height:85vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:0;">📋 Detail Realisasi Kegiatan Bangkom</h2>
            <button onclick="document.getElementById('modal-detail-realisasi').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <div id="detail-realisasi-content"></div>
        <div id="detail-realisasi-actions" style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;border-top:1px solid var(--divider);padding-top:16px;">
            <button type="button" onclick="document.getElementById('modal-detail-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Tutup</button>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Approve Realisasi --}}
<div id="modal-confirm-approve-realisasi" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:10001;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:480px;box-shadow:0 8px 32px rgba(0,0,0,0.12);text-align:left;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:var(--text-primary);margin:0;">✅ Konfirmasi Persetujuan Realisasi</h2>
            <button onclick="document.getElementById('modal-confirm-approve-realisasi').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="approve-realisasi-plan-name" style="margin-bottom:16px;color:var(--text-primary);font-weight:600;padding:10px;background:#F1F5F9;border-radius:6px;"></p>
        <p class="text-sm text-muted" style="margin-bottom:20px;line-height:1.5;">Apakah Anda yakin ingin menyetujui realisasi kegiatan bangkom ini sebagai <strong>Verifikator Level 2</strong>? Status akan berubah menjadi <strong>Realisasi Disetujui</strong> dan email verifikator Anda akan tercatat.</p>
        <form method="POST" id="form-approve-realisasi" style="display:flex;gap:10px;justify-content:flex-end;">
            @csrf
            <button type="button" onclick="document.getElementById('modal-confirm-approve-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
            <button type="submit" class="btn btn-success" style="padding:8px 18px;border-radius:6px;font-weight:600;cursor:pointer;background:#10b981;color:#fff;border:none;">Ya, Setujui Realisasi</button>
        </form>
    </div>
</div>

{{-- Modal Reject / Kembalikan Realisasi --}}
<div id="modal-reject-realisasi" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:500px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:var(--text-primary);margin:0;">↩️ Kembalikan Realisasi Kegiatan</h2>
            <button onclick="document.getElementById('modal-reject-realisasi').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="reject-realisasi-name" style="margin-bottom:16px;color:var(--text-primary);font-weight:600;padding:8px;background:#F1F5F9;border-radius:6px;"></p>
        <form method="POST" id="form-reject-realisasi" style="text-align:left;">
            @csrf
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Alasan Pengembalian / Catatan Perbaikan *</label>
                <textarea name="reject_reason" class="form-control" rows="4" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;resize:vertical;" required placeholder="Tuliskan catatan perbaikan atau alasan pengembalian ke pengampu..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-reject-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
                <button type="submit" class="btn btn-danger" style="padding:8px 16px;border-radius:6px;font-weight:600;cursor:pointer;background:#ef4444;color:#fff;border:none;">Kirim Catatan Pengembalian</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showConfirmApprovePlan(id, title) {
    document.getElementById('approve-plan-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('form-approve-plan').action = '/eselon2/penetapan-bangkom/' + id + '/approve';
    document.getElementById('modal-confirm-approve-plan').style.display = 'flex';
}

function openRejectPlanModal(id, title) {
    document.getElementById('reject-plan-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('form-reject-plan').action = '/eselon2/penetapan-bangkom/' + id + '/reject';
    document.getElementById('modal-reject-plan').style.display = 'flex';
}

function showConfirmApproveRealisasi(id, title) {
    document.getElementById('approve-realisasi-plan-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('form-approve-realisasi').action = '/eselon2/penetapan-bangkom/' + id + '/approve-realisasi';
    document.getElementById('modal-confirm-approve-realisasi').style.display = 'flex';
}

function openRejectRealisasiModal(id, title) {
    document.getElementById('reject-realisasi-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('form-reject-realisasi').action = '/eselon2/penetapan-bangkom/' + id + '/reject-realisasi';
    document.getElementById('modal-reject-realisasi').style.display = 'flex';
}

function showDetailRealisasi(plan) {
    const modal = document.getElementById('modal-detail-realisasi');
    const content = document.getElementById('detail-realisasi-content');
    const actions = document.getElementById('detail-realisasi-actions');

    const statusLower = (plan.status || '').toLowerCase();
    let statusBadge = `<span class="badge badge-neutral">${plan.status}</span>`;
    if (statusLower === 'ditetapkan') statusBadge = `<span class="badge badge-neutral">Ditetapkan</span>`;
    else if (statusLower === 'realisasi') statusBadge = `<span class="badge badge-info">Realisasi</span>`;
    else if (statusLower === 'persetujuan realisasi') statusBadge = `<span class="badge badge-warning">Persetujuan Realisasi</span>`;
    else if (statusLower === 'realisasi disetujui') statusBadge = `<span class="badge badge-success">✓ Realisasi Disetujui</span>`;

    const isLevel2Plan = parseInt(plan.jenis_evaluasi) === 2;

    let participantsRows = '';
    if (plan.participants && plan.participants.length > 0) {
        plan.participants.forEach((p, idx) => {
            participantsRows += `
                <tr style="border-bottom:1px solid #E2E8F0; font-size:12px;">
                    <td style="padding:8px 10px; color:var(--text-primary);">
                        <div style="font-weight:600;">${p.employee_name || p.employee_id}</div>
                        <div style="font-size:11px; color:var(--text-secondary);">${p.employee_nip || p.employee_id} ${p.employee_unit ? '• ' + p.employee_unit : ''}</div>
                    </td>
                    <td style="padding:8px 10px; text-align:center;">${p.skor_penyelenggara ?? '-'}</td>
                    <td style="padding:8px 10px; text-align:center;">${p.skor_materi ?? '-'}</td>
                    <td style="padding:8px 10px; text-align:center;">${p.skor_fasilitator ?? '-'}</td>
                    ${isLevel2Plan ? `
                        <td style="padding:8px 10px; text-align:center; color:#38bdf8;">${p.skor_pre ?? '-'}</td>
                        <td style="padding:8px 10px; text-align:center; color:#4ade80;">${p.skor_post ?? '-'}</td>
                    ` : ''}
                </tr>
            `;
        });
    } else {
        participantsRows = `<tr><td colspan="${isLevel2Plan ? 6 : 4}" style="text-align:center; padding:16px; color:var(--text-secondary);">Belum ada data peserta realisasi yang diinput.</td></tr>`;
    }

    content.innerHTML = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:16px; font-size:13px;">
            <div>
                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">ID & Nama Kegiatan</div>
                <div style="font-weight:700; color:var(--text-primary); font-size:14px; margin-bottom:10px;">${plan.id} - ${plan.nama_kegiatan}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Unit Pengusul</div>
                <div style="color:var(--text-primary); margin-bottom:10px;">${plan.unit_pengusul || '-'}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Kompetensi Dasar</div>
                <div style="color:var(--text-primary); margin-bottom:10px;">${plan.kompetensi_dasar || '-'}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Jalur & Metode</div>
                <div style="color:var(--text-primary);">${plan.jalur_pembelajaran || '-'} (${plan.metode || '-'})</div>
            </div>
            <div>
                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Status Realisasi</div>
                <div style="margin-bottom:10px;">${statusBadge}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Waktu Pelaksanaan & JP</div>
                <div style="color:var(--text-primary); margin-bottom:10px;">${plan.tanggal_mulai} s/d ${plan.tanggal_selesai} • <strong>${plan.jp} JP</strong></div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Fasilitator</div>
                <div style="color:var(--text-primary); margin-bottom:10px;">${plan.fasilitator || '-'}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Jenis Evaluasi</div>
                <div style="color:var(--text-primary);">${isLevel2Plan ? 'Level 2 (Reaksi & Pre/Post Test)' : 'Level 1 (Reaksi Penyelenggaraan)'}</div>
            </div>
        </div>

        <div style="margin-bottom:20px; background:#F8FAFC; border:1px solid var(--card-border); border-radius:8px; padding:14px;">
            <div style="font-weight:600; color:var(--text-primary); font-size:13px; margin-bottom:10px;">📁 Dokumen Bukti Realisasi</div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; font-size:12px;">
                <div style="background:#F8FAFC; padding:10px; border-radius:6px; border:1px solid #F1F5F9;">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">1. Daftar Hadir</div>
                    <div style="color:var(--text-primary); font-weight:600; word-break:break-all;">
                        📄 ${plan.dok_daftar_hadir ? `<a href="/storage/${plan.dok_daftar_hadir}" target="_blank" style="color:var(--accent); text-decoration:none;">${plan.dok_daftar_hadir}</a>` : '<span class="text-muted">Belum ada</span>'}
                    </div>
                </div>
                <div style="background:#F8FAFC; padding:10px; border-radius:6px; border:1px solid #F1F5F9;">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">2. Notulen Kegiatan</div>
                    <div style="color:var(--text-primary); font-weight:600; word-break:break-all;">
                        📄 ${plan.dok_notulen ? `<a href="/storage/${plan.dok_notulen}" target="_blank" style="color:var(--accent); text-decoration:none;">${plan.dok_notulen}</a>` : '<span class="text-muted">Belum ada</span>'}
                    </div>
                </div>
                <div style="background:#F8FAFC; padding:10px; border-radius:6px; border:1px solid #F1F5F9;">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">3. Foto Dokumentasi</div>
                    <div style="color:var(--text-primary); font-weight:600; word-break:break-all;">
                        🖼️ ${plan.dok_dokumentasi ? `<a href="/storage/${plan.dok_dokumentasi}" target="_blank" style="color:var(--accent); text-decoration:none;">${plan.dok_dokumentasi}</a>` : '<span class="text-muted">Belum ada</span>'}
                    </div>
                </div>
            </div>
        </div>

        <div style="background:#F8FAFC; border:1px solid var(--card-border); border-radius:8px; padding:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div style="font-weight:600; color:var(--text-primary); font-size:13px;">👥 Daftar Peserta & Skor (${plan.participants ? plan.participants.length : 0} Pegawai)</div>
            </div>
            <div style="max-height:260px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #E2E8F0; color:var(--text-secondary); font-size:11px; text-align:center;">
                            <th style="padding:6px 10px; text-align:left;">Nama Pegawai</th>
                            <th style="padding:6px 10px; width:90px;">Penyelenggara</th>
                            <th style="padding:6px 10px; width:90px;">Materi</th>
                            <th style="padding:6px 10px; width:90px;">Fasilitator</th>
                            ${isLevel2Plan ? `
                                <th style="padding:6px 10px; width:80px; color:#38bdf8;">Pre-Test</th>
                                <th style="padding:6px 10px; width:80px; color:#4ade80;">Post-Test</th>
                            ` : ''}
                        </tr>
                    </thead>
                    <tbody>
                        ${participantsRows}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    // Action buttons inside modal
    let actionButtons = `<button type="button" onclick="document.getElementById('modal-detail-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px; border-radius:6px; cursor:pointer;">Tutup</button>`;

    const canApproveRealisasi = @json($canVerifLevel2);
    if (statusLower === 'persetujuan realisasi' && canApproveRealisasi) {
        actionButtons = `
            <button type="button" class="btn btn-success" style="padding:8px 18px; border-radius:6px; font-weight:600; cursor:pointer; background:#10b981; color:#fff; border:none;" onclick="document.getElementById('modal-detail-realisasi').style.display='none'; showConfirmApproveRealisasi('${plan.id}', '${(plan.nama_kegiatan || '').replace(/'/g, "\\'")}')">✅ Approve Realisasi</button>
            <button type="button" class="btn btn-danger" style="padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer;" onclick="document.getElementById('modal-detail-realisasi').style.display='none'; openRejectRealisasiModal('${plan.id}', '${(plan.nama_kegiatan || '').replace(/'/g, "\\'")}')">↩️ Kembalikan</button>
            <button type="button" onclick="document.getElementById('modal-detail-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px; border-radius:6px; cursor:pointer;">Tutup</button>
        `;
    }

    actions.innerHTML = actionButtons;
    modal.style.display = 'flex';
}

function showDetail(plan) {
    let keberhasilan = [];
    let penugasan = [];
    let kriteria = [];
    try { keberhasilan = JSON.parse(plan.indikator_keberhasilan) || []; } catch(e) {}
    try { penugasan = JSON.parse(plan.penugasan_terkait) || []; } catch(e) {}
    try { kriteria = JSON.parse(plan.kriteria_peserta) || []; } catch(e) {}

    const listKeberhasilan = keberhasilan.map(v => `<li>${v}</li>`).join('') || '-';
    const listPenugasan = penugasan.map(v => `<li>${v}</li>`).join('') || '-';
    const listKriteria = kriteria.map(v => `<li>${v}</li>`).join('') || '-';

    const formattedAnggaran = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(plan.nilai_anggaran);

    const html = `
        <table style="width:100%;font-size:13px;border-collapse:collapse;color:var(--text-primary);">
            <tr><td style="color:var(--text-secondary);padding:6px 0;width:40%;">ID Kegiatan</td><td><strong>${plan.id}</strong></td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Nama Kegiatan</td><td><strong>${plan.nama_kegiatan}</strong></td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Kompetensi Dasar</td><td>${plan.kompetensi_dasar}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Unit Pengusul</td><td>${plan.unit_pengusul}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Indikator Kinerja</td><td>${plan.indikator_kinerja}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Latar Belakang</td><td>[${plan.jenis_latar_belakang}] ${plan.latar_belakang}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Tujuan</td><td>${plan.tujuan_kegiatan}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Metode</td><td>${plan.metode}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Jalur Pembelajaran</td><td>${plan.jalur_pembelajaran}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Tanggal</td><td>${plan.tanggal_mulai} s/d ${plan.tanggal_selesai}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Total JP</td><td>${plan.jp} JP</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Jumlah Kelas</td><td>${plan.jumlah_kelas} Kelas</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Nilai Anggaran</td><td>${formattedAnggaran}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Fasilitator</td><td>${plan.fasilitator}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Evaluasi</td><td>${plan.evaluasi} ${plan.evaluasi === 'Ya' ? '(Level ' + plan.jenis_evaluasi + ')' : ''}</td></tr>
            <tr><td style="color:var(--text-secondary);padding:6px 0;">Status</td><td><span class="badge badge-info">${plan.status.toUpperCase()}</span></td></tr>
            <tr><td colspan="2" style="padding-top:10px;font-weight:600;color:var(--text-secondary);">Indikator Keberhasilan:</td></tr>
            <tr><td colspan="2"><ul style="margin:4px 0;padding-left:16px;">${listKeberhasilan}</ul></td></tr>
            <tr><td colspan="2" style="padding-top:10px;font-weight:600;color:var(--text-secondary);">Penugasan Terkait:</td></tr>
            <tr><td colspan="2"><ul style="margin:4px 0;padding-left:16px;">${listPenugasan}</ul></td></tr>
            <tr><td colspan="2" style="padding-top:10px;font-weight:600;color:var(--text-secondary);">Kriteria Peserta:</td></tr>
            <tr><td colspan="2"><ul style="margin:4px 0;padding-left:16px;">${listKriteria}</ul></td></tr>
        </table>`;
    document.getElementById('detail-content').innerHTML = html;
    document.getElementById('modal-detail').style.display = 'flex';
}

function matchQuery(text, query) {
    if (!query) return true;
    const targetText = text.toLowerCase().trim();
    const q = query.toLowerCase().trim();
    if (!q) return true;
    const words = targetText.split(/\s+/);
    const queryWords = q.split(/\s+/);
    return queryWords.every(qw => words.some(w => w.startsWith(qw)));
}

let currentPage = 1;
const itemsPerPage = 10;
let filteredRows = [];

function displayPage(page) {
    currentPage = page;
    const totalItems = filteredRows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
    
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    
    filteredRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    const info = document.getElementById('pagination-info');
    if(info) {
        const start = totalItems === 0 ? 0 : startIndex + 1;
        const end = Math.min(endIndex, totalItems);
        info.innerHTML = `Showing ${start} to ${end} of ${totalItems} entries`;
    }
    
    const btnContainer = document.getElementById('pagination-buttons');
    if(btnContainer) {
        let buttonsHtml = '';
        buttonsHtml += `<button class="btn btn-sm btn-neutral" onclick="displayPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Prev</button>`;
        
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if(endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }
        
        for(let i = startPage; i <= endPage; i++) {
            if(i === currentPage) {
                buttonsHtml += `<button class="btn btn-sm btn-primary" style="background:var(--primary);color:#fff;border:none;">${i}</button>`;
            } else {
                buttonsHtml += `<button class="btn btn-sm btn-neutral" onclick="displayPage(${i})">${i}</button>`;
            }
        }
        
        buttonsHtml += `<button class="btn btn-sm btn-neutral" onclick="displayPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
        btnContainer.innerHTML = buttonsHtml;
    }
}

function filterAndSortPlans() {
    const searchQuery = document.getElementById('search-title').value.toLowerCase();
    const filterMetode = document.getElementById('filter-metode').value;
    const filterStatus = document.getElementById('filter-status').value;
    const filterCompetency = document.getElementById('filter-competency').value;
    const sortBy = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('.plan-row'));
    filteredRows = [];
    
    rows.forEach(row => {
        const title = row.getAttribute('data-title') || '';
        const m = row.getAttribute('data-metode');
        const s = row.getAttribute('data-status');
        const c = row.getAttribute('data-competency');
        
        const matchSearch = matchQuery(title, searchQuery);
        const matchMetode = !filterMetode || m === filterMetode;
        const matchStatus = !filterStatus || s === filterStatus;
        const matchCompetency = !filterCompetency || c === filterCompetency;
        
        if (matchSearch && matchMetode && matchStatus && matchCompetency) {
            filteredRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });
    
    const tbody = document.querySelector('#table-plans tbody');
    if (sortBy) {
        filteredRows.sort((a, b) => {
            if (sortBy === 'title-asc') {
                return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
            } else if (sortBy === 'title-desc') {
                return b.getAttribute('data-title').localeCompare(a.getAttribute('data-title'));
            } else if (sortBy === 'jp-desc') {
                return parseInt(b.getAttribute('data-jp')) - parseInt(a.getAttribute('data-jp'));
            } else if (sortBy === 'jp-asc') {
                return parseInt(a.getAttribute('data-jp')) - parseInt(b.getAttribute('data-jp'));
            }
            return 0;
        });
    }
    
    filteredRows.forEach(row => tbody.appendChild(row));
    displayPage(1);
}

document.addEventListener('DOMContentLoaded', () => {
    filterAndSortPlans();
});
</script>
@endpush


