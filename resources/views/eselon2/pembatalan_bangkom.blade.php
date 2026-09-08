@extends('layouts.app')
@section('title', 'Pembatalan Bangkom - Kepala Unit')
@section('header_title', 'Konfirmasi Pembatalan Bangkom')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">❌ Konfirmasi Pembatalan Bangkom</h1>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:var(--danger);margin-bottom:16px;">❌ {{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-title" style="font-size:18px;">Pengajuan Pembatalan dari Pengampu SDM</div>
    
    {{-- Filter & Sort Bar --}}
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
        <div style="font-weight:600; color:var(--text-secondary);">🔍 Cari:</div>
        <input type="text" id="search-title" class="form-control" style="width:200px; height:32px; padding:0 8px; font-size:12px;" placeholder="Cari nama kegiatan..." onkeyup="filterAndSortCancellations()">
        <div style="font-weight:600; color:var(--text-secondary); margin-left:8px;">Filter:</div>
        <select id="filter-competency" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortCancellations()">
            <option value="">Semua Kompetensi</option>
            <option value="Analisis Data">Analisis Data</option>
            <option value="Komunikasi">Komunikasi</option>
            <option value="Komunikasi Hasil">Komunikasi Hasil</option>
            <option value="Fraud Risk Management">Fraud Risk Management</option>
            <option value="Keamanan Data Dasar">Keamanan Data Dasar</option>
        </select>
        <div style="font-weight:600; color:var(--text-secondary); margin-left:12px;">⇅ Urutkan:</div>
        <select id="sort-by" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortCancellations()">
            <option value="">Default</option>
            <option value="title-asc">Nama Kegiatan (A-Z)</option>
            <option value="title-desc">Nama Kegiatan (Z-A)</option>
        </select>
    </div>

    <table id="table-cancellations">
        <thead>
            <tr><th>Nama Kegiatan</th><th>Kompetensi</th><th>Tanggal Kegiatan</th><th>Alasan Pembatalan</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($plans as $p)
            @php
                $statusLower = strtolower($p->status);
                $activeRole = session('active_role', auth()->user()->role);
                $isKorwas = ($activeRole === 'eselon3' && (auth()->user()->jabatan ?? '') === 'Koordinator Pengawasan');
                $canVerifLevel1 = in_array($activeRole, ['eselon3', 'kombinasi', 'admin']) && !$isKorwas;
            @endphp
            <tr class="cancel-row" data-title="{{ strtolower($p->nama_kegiatan) }}" data-competency="{{ $p->kompetensi_dasar }}">
                <td><strong>{{ $p->nama_kegiatan }}</strong></td>
                <td>{{ $p->kompetensi_dasar }}</td>
                <td>{{ $p->tanggal_mulai }} s/d {{ $p->tanggal_selesai }}</td>
                <td>{{ $p->cancel_reason ?? 'Penyusunan ulang program unit' }}</td>
                <td style="display:flex;gap:6px;align-items:center;">
                    @if($statusLower === 'pembatalan diajukan')
                        @if($canVerifLevel1)
                            <button type="button" class="btn btn-sm btn-danger" onclick="showConfirmDelete('{{ route('eselon2.approvePembatalan', $p->id) }}', '{{ addslashes($p->nama_kegiatan) }}')">✅ Approve </button>
                            <button type="button" class="btn btn-sm btn-neutral" onclick="showConfirmRejectPembatalan('{{ route('eselon2.rejectPembatalan', $p->id) }}', '{{ addslashes($p->nama_kegiatan) }}')">Reject</button>
                        @else
                            <span class="badge badge-neutral">Menunggu Approval (Level 1)</span>
                        @endif
                    @elseif($statusLower === 'pembatalan disetujui')
                        <span class="badge badge-success">✓ Pembatalan Disetujui</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-sm" style="text-align:center;padding:24px;">Belum ada pengajuan pembatalan kegiatan bangkom.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div id="pagination-controls" style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:#F8FAFC; border-top:1px solid #E2E8F0; border-radius:0 0 8px 8px;">
        <div id="pagination-info" style="font-size:12px; color:var(--text-secondary);">Showing 0 to 0 of 0 entries</div>
        <div style="display:flex; gap:4px;" id="pagination-buttons"></div>
    </div>
</div>

{{-- Custom Confirm Approve Delete Modal --}}
<div id="modal-confirm-delete" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:480px;box-shadow:0 8px 32px rgba(0,0,0,0.12);text-align:left;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:var(--danger);margin:0;">⚠️ Konfirmasi Setujui Pembatalan</h2>
            <button onclick="document.getElementById('modal-confirm-delete').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="confirm-delete-plan-name" style="margin-bottom:16px;color:var(--text-primary);font-weight:600;padding:10px;background:#F1F5F9;border-radius:6px;"></p>
        <p class="text-sm text-muted" style="margin-bottom:20px;line-height:1.5;">Apakah Anda yakin ingin menyetujui pembatalan rencana kegiatan ini? Tindakan ini akan membatalkan kegiatan secara resmi.</p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" onclick="document.getElementById('modal-confirm-delete').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Kembali</button>
            <button type="button" id="btn-confirm-delete-submit" class="btn btn-danger" style="padding:8px 18px;border-radius:6px;font-weight:600;cursor:pointer;background:#ef4444;color:#fff;border:none;">Ya, Setujui Pembatalan</button>
        </div>
    </div>
</div>

{{-- Custom Confirm Reject Cancel Modal --}}
<div id="modal-confirm-reject-cancel" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:480px;box-shadow:0 8px 32px rgba(0,0,0,0.12);text-align:left;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:var(--text-primary);margin:0;">↩️ Tolak Pembatalan (Tetapkan Kembali)</h2>
            <button onclick="document.getElementById('modal-confirm-reject-cancel').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="confirm-reject-plan-name" style="margin-bottom:16px;color:var(--text-primary);font-weight:600;padding:10px;background:#F1F5F9;border-radius:6px;"></p>
        <p class="text-sm text-muted" style="margin-bottom:20px;line-height:1.5;">Apakah Anda ingin menolak pembatalan ini dan mengembalikan status kegiatan menjadi <strong>Ditetapkan</strong>?</p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" onclick="document.getElementById('modal-confirm-reject-cancel').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
            <button type="button" id="btn-confirm-reject-submit" class="btn btn-primary" style="padding:8px 18px;border-radius:6px;font-weight:600;cursor:pointer;background:var(--primary);color:#fff;border:none;">Ya, Tetapkan Kembali</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let targetDeleteUrl = '';
let targetRejectUrl = '';

function showConfirmDelete(url, title) {
    targetDeleteUrl = url;
    document.getElementById('confirm-delete-plan-name').textContent = 'Kegiatan: ' + (title || '-');
    document.getElementById('modal-confirm-delete').style.display = 'flex';
}

function showConfirmRejectPembatalan(url, title) {
    targetRejectUrl = url;
    document.getElementById('confirm-reject-plan-name').textContent = 'Kegiatan: ' + (title || '-');
    document.getElementById('modal-confirm-reject-cancel').style.display = 'flex';
}

document.getElementById('btn-confirm-reject-submit').addEventListener('click', function() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = targetRejectUrl;
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
});

document.getElementById('btn-confirm-delete-submit').addEventListener('click', function() {
    // Create and submit a dynamic form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = targetDeleteUrl;
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
});

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

function filterAndSortCancellations() {
    const searchQuery = document.getElementById('search-title').value.toLowerCase();
    const filterCompetency = document.getElementById('filter-competency').value;
    const sortBy = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('.cancel-row'));
    filteredRows = [];
    
    rows.forEach(row => {
        const title = row.getAttribute('data-title') || '';
        const c = row.getAttribute('data-competency');
        
        const matchSearch = matchQuery(title, searchQuery);
        const matchCompetency = !filterCompetency || c === filterCompetency;
        
        if (matchSearch && matchCompetency) {
            filteredRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });
    
    const tbody = document.querySelector('#table-cancellations tbody');
    if (sortBy) {
        filteredRows.sort((a, b) => {
            if (sortBy === 'title-asc') {
                return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
            } else if (sortBy === 'title-desc') {
                return b.getAttribute('data-title').localeCompare(a.getAttribute('data-title'));
            }
            return 0;
        });
    }
    
    filteredRows.forEach(row => tbody.appendChild(row));
    displayPage(1);
}

document.addEventListener('DOMContentLoaded', () => {
    filterAndSortCancellations();
});
</script>
@endpush
@endsection



