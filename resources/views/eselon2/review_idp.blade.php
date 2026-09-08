@extends('layouts.app')
@section('title', 'Review IDP Pegawai')
@section('header_title', 'Review IDP Pegawai Unit')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">📝 Review IDP Pegawai Unit</h1>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:var(--danger);margin-bottom:16px;">❌ {{ session('error') }}</div>
@endif

{{-- Search --}}
<div class="card mb-4" style="padding:16px;">
    <form method="GET" style="display:flex;gap:10px;align-items:flex-end;">
        <div class="form-group" style="margin-bottom:0;flex:1;">
            <label class="form-label" style="font-size:14px;color:var(--text-primary)">Cari Pegawai (Nama, NIP, Jabatan, Unit Kerja)</label>
            <input type="text" name="q" class="form-control" value="{{ $search }}" placeholder="Ketik kata kunci pencarian...">
        </div>
        <button type="submit" class="btn btn-primary" style="height:40px;">Cari</button>
        @if($search) <a href="{{ route('eselon2.reviewIdp') }}" class="btn btn-neutral" style="height:40px;line-height:24px;">Reset</a> @endif
    </form>
</div>

<div class="card">
    <div class="card-title" style="font-size:18px;">Daftar Pegawai & IDP</div>
    
    {{-- Filter & Sort Bar --}}
    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
        <div style="font-weight:600; color:var(--text-secondary);">🔍 Filter:</div>
        <select id="filter-jabatan" class="form-control" style="width:160px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortIdp()">
            <option value="">Semua Jabatan</option>
            @foreach(collect($groupedIdps)->pluck('jabatan')->unique()->filter()->sort() as $jab)
                <option value="{{ $jab }}">{{ $jab }}</option>
            @endforeach
        </select>
        
        @if(stripos($user->unit_eselon1 ?? $user->scope ?? '', 'Deput') === 0)
        <select id="filter-unit-kerja" class="form-control" style="width:160px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortIdp()">
            <option value="">Semua Unit Kerja</option>
            @foreach(collect($groupedIdps)->pluck('unit_kerja_2')->unique()->filter()->sort() as $uk)
                <option value="{{ $uk }}">{{ $uk }}</option>
            @endforeach
        </select>
        @endif
        
        <div style="font-weight:600; color:var(--text-secondary); margin-left:12px;">⇅ Urutkan:</div>
        <select id="sort-by" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortIdp()">
            <option value="total-desc">Total IDP Terbanyak</option>
            <option value="total-asc">Total IDP Paling Sedikit</option>
            <option value="name-asc">Nama Pegawai (A-Z)</option>
            <option value="name-desc">Nama Pegawai (Z-A)</option>
        </select>
    </div>

    {{-- Daftar Pegawai --}}
    <table id="table-idp">
        <thead>
            <tr>
                <th>Nama Pegawai</th>
                <th>Jabatan</th>
                <th>Unit Kerja Eselon II</th>
                <th>Jumlah IDP</th>
                <th style="width:100px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groupedIdps as $group)
            <tr class="idp-row" data-name="{{ strtolower($group['employee_name']) }}" data-jabatan="{{ $group['jabatan'] }}" data-unit="{{ $group['unit_kerja_2'] }}" data-total="{{ $group['total_idp'] }}">
                <td><strong>{{ $group['employee_name'] }}</strong><br><small class="text-muted">{{ $group['emp_id'] }}</small></td>
                <td>{{ $group['jabatan'] }}</td>
                <td>{{ $group['unit_kerja_2'] }}</td>
                <td><span class="badge badge-info">{{ $group['total_idp'] }} IDP</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick="openDetailModal('{{ $group['emp_id'] }}')">Detail</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:24px;">
                @if($search) Tidak ada pegawai yang cocok dengan pencarian "{{ $search }}". @else Belum ada IDP yang diajukan. @endif
            </td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination Controls (Main) --}}
    @if(count($groupedIdps) > 0)
    <div id="idp-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:8px;">
        <div style="font-size:12px;color:var(--text-secondary);" id="idp-record-info">
            Menampilkan 1-10 dari {{ count($groupedIdps) }} data
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            <button type="button" id="btn-idp-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 10px;cursor:pointer;">← Prev</button>
            <span id="idp-page-info" style="font-size:11px;color:var(--text-primary);font-weight:600;padding:0 8px;">Halaman 1 dari 1</span>
            <button type="button" id="btn-idp-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 10px;cursor:pointer;">Next →</button>
        </div>
    </div>
    @endif
</div>

{{-- Modal Detail IDP Pegawai --}}
<div id="modal-detail-idp" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9990;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:24px;width:950px;max-width:95vw;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 8px 24px rgba(0,0,0,0.10);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-shrink:0;">
            <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:0;">📋 Detail IDP - <span id="detail-employee-name"></span></h3>
            <button onclick="document.getElementById('modal-detail-idp').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        
        <div style="display:flex;gap:10px;margin-bottom:16px;align-items:center;justify-content:flex-start;flex-shrink:0;">
            <button type="button" id="btn-batch-agree-modal" class="btn btn-success btn-sm" style="display:none;align-items:center;gap:6px;" onclick="openBatchAgreeModal()">
                <span>✅</span> <span>Approve Semua (<strong id="batch-agree-count-modal">0</strong>)</span>
            </button>
        </div>

        <div style="overflow-y:auto;flex:1;border:1px solid var(--card-border);border-radius:8px;">
            <table id="table-detail-idp" style="width:100%;font-size:13px;text-align:left;border-collapse:collapse;margin:0;">
                <thead style="background:#334155;position:sticky;top:0;z-index:2;box-shadow:0 1px 0 var(--divider);">
                    <tr>
                        <th style="padding:10px;width:40px;text-align:center;">
                            <input type="checkbox" id="check-all-detail-idp" onchange="toggleCheckAllDetail(this)" title="Pilih Semua IDP Diajukan" style="cursor:pointer;accent-color:var(--success);width:16px;height:16px;">
                        </th>
                        <th style="padding:10px;">Kebutuhan</th>
                        <th style="padding:10px;">Klaster</th>
                        <th style="padding:10px;">Sumber</th>
                        <th style="padding:10px;">Prioritas</th>
                        <th style="padding:10px;">Status</th>
                        <th style="padding:10px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="detail-idp-body">
                    <!-- populates by JS -->
                </tbody>
            </table>
        </div>
        
        <div id="detail-pagination" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:8px;flex-shrink:0;">
            <div style="font-size:12px;color:var(--text-secondary);" id="detail-record-info">Menampilkan 0 data</div>
            <div style="display:flex;align-items:center;gap:6px;">
                <button type="button" id="btn-detail-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 10px;cursor:pointer;" onclick="changeDetailPage(-1)">← Prev</button>
                <span id="detail-page-info" style="font-size:11px;color:var(--text-primary);font-weight:600;padding:0 8px;">Halaman 1 dari 1</span>
                <button type="button" id="btn-detail-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 10px;cursor:pointer;" onclick="changeDetailPage(1)">Next →</button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden Form for Single Agree --}}
<form id="form-single-agree" method="POST" action="" style="display:none;">
    @csrf
</form>

{{-- Hidden Form for Batch Agree --}}
<form id="form-batch-agree" method="POST" action="{{ route('eselon2.agreeBatchIdp') }}" style="display:none;">
    @csrf
</form>

{{-- Modal Konfirmasi Single Agree --}}
<div id="modal-confirm-agree-single" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:24px;width:440px;box-shadow:0 8px 24px rgba(0,0,0,0.10);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-size:16px;font-weight:700;color:var(--text-primary);margin:0;">✅ Konfirmasi Kesepakatan IDP</h3>
            <button onclick="document.getElementById('modal-confirm-agree-single').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p style="font-size:13px;color:var(--text-secondary);line-height:1.5;margin-bottom:20px;" id="text-single-agree-confirm">
            Apakah Anda yakin ingin menyepakati item IDP ini?
        </p>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" onclick="document.getElementById('modal-confirm-agree-single').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
            <button type="button" id="btn-submit-single-agree" class="btn btn-success" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Ya, Sepakati</button>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Batch Agree --}}
<div id="modal-confirm-agree-batch" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:24px;width:440px;box-shadow:0 8px 24px rgba(0,0,0,0.10);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-size:16px;font-weight:700;color:var(--text-primary);margin:0;">✅ Konfirmasi Kesepakatan IDP Terpilih</h3>
            <button onclick="document.getElementById('modal-confirm-agree-batch').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p style="font-size:13px;color:var(--text-secondary);line-height:1.5;margin-bottom:20px;" id="text-batch-agree-confirm">
            Apakah Anda yakin ingin menyepakati item IDP yang dipilih?
        </p>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" onclick="document.getElementById('modal-confirm-agree-batch').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
            <button type="button" id="btn-submit-batch-agree" class="btn btn-success" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Ya, Sepakati Semua Terpilih</button>
        </div>
    </div>
</div>

{{-- Modal Revisi --}}
<div id="modal-revisi" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:460px;">
        <h2 style="font-size:16px;margin-bottom:12px;">✏️ Minta Perbaikan IDP</h2>
        <p class="text-sm text-muted" id="revisi-idp-name" style="margin-bottom:14px;"></p>
        <form method="POST" id="revisi-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Catatan Perbaikan *</label>
                <textarea name="revision_note" class="form-control" rows="4" required placeholder="Tuliskan catatan untuk pegawai..."></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:12px;">
                <button type="submit" class="btn btn-warning">Kirim Catatan Perbaikan</button>
                <button type="button" onclick="document.getElementById('modal-revisi').style.display='none'" class="btn btn-neutral">Batal</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const groupedData = @json($groupedIdps);
let currentDetailItems = [];
let currentDetailPage = 1;
const detailPageSize = 10;

// Main Table Pagination
const pageSize = 10;
let currentPage = 1;

function renderPagination() {
    const filterJabatan = document.getElementById('filter-jabatan') ? document.getElementById('filter-jabatan').value : '';
    const filterUnitKerja = document.getElementById('filter-unit-kerja') ? document.getElementById('filter-unit-kerja').value : '';
    const sortBy = document.getElementById('sort-by') ? document.getElementById('sort-by').value : 'total-desc';
    
    const rows = Array.from(document.querySelectorAll('.idp-row'));
    const tbody = document.querySelector('#table-idp tbody');

    // Filter
    const matchedRows = [];
    rows.forEach(row => {
        const j = row.getAttribute('data-jabatan');
        const u = row.getAttribute('data-unit');
        
        const matchJabatan = !filterJabatan || j === filterJabatan;
        const matchUnit = !filterUnitKerja || u === filterUnitKerja;
        
        if (matchJabatan && matchUnit) {
            matchedRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    // Sort
    matchedRows.sort((a, b) => {
        if (sortBy === 'name-asc') {
            return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
        } else if (sortBy === 'name-desc') {
            return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
        } else if (sortBy === 'total-desc') {
            return parseInt(b.getAttribute('data-total')) - parseInt(a.getAttribute('data-total'));
        } else if (sortBy === 'total-asc') {
            return parseInt(a.getAttribute('data-total')) - parseInt(b.getAttribute('data-total'));
        }
        return 0;
    });
    
    matchedRows.forEach(row => tbody.appendChild(row));

    const totalRecords = matchedRows.length;
    const totalPages = Math.ceil(totalRecords / pageSize) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const start = (currentPage - 1) * pageSize;
    const end = start + pageSize;

    // Show/hide according to current page
    matchedRows.forEach((row, idx) => {
        if (idx >= start && idx < end) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Update pagination controls UI
    const controls = document.getElementById('idp-pagination-controls');
    const recordInfo = document.getElementById('idp-record-info');
    const pageInfo = document.getElementById('idp-page-info');
    const btnPrev = document.getElementById('btn-idp-prev');
    const btnNext = document.getElementById('btn-idp-next');

    if (controls) {
        if (totalRecords === 0) {
            controls.style.display = 'none';
        } else {
            controls.style.display = 'flex';
            const displayStart = totalRecords > 0 ? start + 1 : 0;
            const displayEnd = Math.min(end, totalRecords);
            if (recordInfo) recordInfo.textContent = `Menampilkan ${displayStart}-${displayEnd} dari ${totalRecords} data`;
            if (pageInfo) pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
            if (btnPrev) {
                btnPrev.disabled = currentPage <= 1;
                btnPrev.style.opacity = currentPage <= 1 ? '0.5' : '1';
                btnPrev.style.cursor = currentPage <= 1 ? 'not-allowed' : 'pointer';
            }
            if (btnNext) {
                btnNext.disabled = currentPage >= totalPages;
                btnNext.style.opacity = currentPage >= totalPages ? '0.5' : '1';
                btnNext.style.cursor = currentPage >= totalPages ? 'not-allowed' : 'pointer';
            }
        }
    }
}

function filterAndSortIdp() {
    currentPage = 1;
    renderPagination();
}

document.addEventListener('DOMContentLoaded', function() {
    const btnPrev = document.getElementById('btn-idp-prev');
    const btnNext = document.getElementById('btn-idp-next');

    if (btnPrev) {
        btnPrev.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
            }
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', function() {
            currentPage++;
            renderPagination();
        });
    }

    renderPagination();
});

// Detail Modal Functions
function openDetailModal(empId) {
    const group = groupedData.find(g => g.emp_id === empId);
    if(!group) return;
    
    document.getElementById('detail-employee-name').textContent = group.employee_name;
    currentDetailItems = group.items;
    currentDetailPage = 1;
    
    // reset check all
    const checkAll = document.getElementById('check-all-detail-idp');
    if(checkAll) { checkAll.checked = false; checkAll.indeterminate = false; }
    
    renderDetailTable();
    document.getElementById('modal-detail-idp').style.display = 'flex';
}

function renderDetailTable() {
    const tbody = document.getElementById('detail-idp-body');
    tbody.innerHTML = '';
    
    const totalRecords = currentDetailItems.length;
    const totalPages = Math.ceil(totalRecords / detailPageSize) || 1;
    if (currentDetailPage > totalPages) currentDetailPage = totalPages;
    if (currentDetailPage < 1) currentDetailPage = 1;
    
    const start = (currentDetailPage - 1) * detailPageSize;
    const end = start + detailPageSize;
    
    const itemsToShow = currentDetailItems.slice(start, end);
    
    if(itemsToShow.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-secondary);">Tidak ada IDP yang diajukan.</td></tr>`;
    } else {
        itemsToShow.forEach(idp => {
            let sc = 'badge-neutral';
            if(idp.status === 'Disepakati') sc = 'badge-success';
            else if(idp.status === 'Diajukan') sc = 'badge-info';
            else if(idp.status === 'Perlu Perbaikan') sc = 'badge-danger';
            
            let prioBadge = 'badge-neutral';
            if(idp.priority === 'Tinggi') prioBadge = 'badge-danger';
            else if(idp.priority === 'Sedang') prioBadge = 'badge-warning';

            let checkboxHtml = idp.status === 'Diajukan' 
                ? `<input type="checkbox" class="review-idp-checkbox" value="${idp.id}" style="cursor:pointer;accent-color:var(--success);width:16px;height:16px;" onchange="updateReviewBatchState()">`
                : `<span style="color:var(--text-secondary);">-</span>`;
                
            let empNameSafe = idp.employee_name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            let needSafe = idp.need.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                
            let aksiHtml = idp.status === 'Diajukan'
                ? `<div style="display:flex;flex-direction:column;gap:4px;">
                    <button type="button" class="btn btn-sm btn-success" style="width:100%;font-size:11px;" onclick="openSingleAgreeModal('${idp.id}', '${empNameSafe}', '${needSafe}')">Approve</button>
                    <button type="button" class="btn btn-sm btn-danger" style="width:100%;font-size:11px;" onclick="openRevisiModal('${idp.id}', '${needSafe}')">Reject</button>
                   </div>`
                : `<span class="text-muted text-sm">—</span>`;

            let noteHtml = idp.revision_note ? `<div style="font-size:11px;color:var(--warning);margin-top:4px;">📝 Catatan: ${idp.revision_note}</div>` : '';

            let tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
            tr.innerHTML = `
                <td style="text-align:center;padding:12px 10px;">${checkboxHtml}</td>
                <td style="padding:12px 10px;"><strong>${idp.need}</strong>${noteHtml}</td>
                <td style="padding:12px 10px;">${idp.competency_type || 'Teknis'}</td>
                <td style="padding:12px 10px;">${idp.source || '-'}</td>
                <td style="padding:12px 10px;"><span class="badge ${prioBadge}">${idp.priority}</span></td>
                <td style="padding:12px 10px;"><span class="badge ${sc}">${idp.status}</span></td>
                <td style="padding:12px 10px;">${aksiHtml}</td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    // update modal pagination UI
    const recordInfo = document.getElementById('detail-record-info');
    const pageInfo = document.getElementById('detail-page-info');
    const btnPrev = document.getElementById('btn-detail-prev');
    const btnNext = document.getElementById('btn-detail-next');
    
    if(recordInfo) recordInfo.textContent = totalRecords > 0 ? `Menampilkan ${start + 1}-${Math.min(end, totalRecords)} dari ${totalRecords} data` : 'Menampilkan 0 data';
    if(pageInfo) pageInfo.textContent = `Halaman ${currentDetailPage} dari ${totalPages}`;
    
    if(btnPrev) {
        btnPrev.disabled = currentDetailPage <= 1;
        btnPrev.style.opacity = currentDetailPage <= 1 ? '0.5' : '1';
    }
    if(btnNext) {
        btnNext.disabled = currentDetailPage >= totalPages;
        btnNext.style.opacity = currentDetailPage >= totalPages ? '0.5' : '1';
    }
    
    updateReviewBatchState();
}

function changeDetailPage(delta) {
    currentDetailPage += delta;
    renderDetailTable();
}

function toggleCheckAllDetail(el) {
    const isChecked = el.checked;
    const checkboxes = document.querySelectorAll('#table-detail-idp .review-idp-checkbox');
    checkboxes.forEach(cb => { cb.checked = isChecked; });
    updateReviewBatchState();
}

function updateReviewBatchState() {
    const checkboxes = document.querySelectorAll('#table-detail-idp .review-idp-checkbox');
    const checked = document.querySelectorAll('#table-detail-idp .review-idp-checkbox:checked');
    const count = checked.length;
    
    const btnBatch = document.getElementById('btn-batch-agree-modal');
    const txtCount = document.getElementById('batch-agree-count-modal');
    const checkAll = document.getElementById('check-all-detail-idp');

    if (txtCount) txtCount.textContent = count;
    if (btnBatch) btnBatch.style.display = count > 0 ? 'inline-flex' : 'none';

    if (checkAll && checkboxes.length > 0) {
        if (count === 0) { checkAll.checked = false; checkAll.indeterminate = false; }
        else if (count === checkboxes.length) { checkAll.checked = true; checkAll.indeterminate = false; }
        else { checkAll.checked = false; checkAll.indeterminate = true; }
    }
}

// Actions Modals
function openRevisiModal(id, need) {
    document.getElementById('revisi-idp-name').textContent = 'IDP: ' + need;
    // For Kepala Unit routes
    document.getElementById('revisi-form').action = `/eselon2/review-idp/${id}/revise`;
    document.getElementById('modal-revisi').style.display = 'flex';
}

function openSingleAgreeModal(id, employeeName, need) {
    const textEl = document.getElementById('text-single-agree-confirm');
    if (textEl) {
        textEl.innerHTML = `Apakah Anda yakin ingin menyepakati item IDP <strong>"${need}"</strong> untuk pegawai <strong>"${employeeName}"</strong>?`;
    }
    const btnSubmit = document.getElementById('btn-submit-single-agree');
    if (btnSubmit) {
        btnSubmit.onclick = function() {
            const form = document.getElementById('form-single-agree');
            // For Kepala Unit routes
            form.action = `/eselon2/review-idp/${id}/agree`;
            form.submit();
        };
    }
    document.getElementById('modal-confirm-agree-single').style.display = 'flex';
}

function openBatchAgreeModal() {
    const checked = Array.from(document.querySelectorAll('#table-detail-idp .review-idp-checkbox:checked'));
    const count = checked.length;
    if (count === 0) return;

    const textEl = document.getElementById('text-batch-agree-confirm');
    if (textEl) {
        textEl.innerHTML = `Apakah Anda yakin ingin menyepakati <strong>${count} item IDP</strong> terpilih secara bersamaan?`;
    }
    const btnSubmit = document.getElementById('btn-submit-batch-agree');
    if (btnSubmit) {
        btnSubmit.onclick = function() {
            const form = document.getElementById('form-batch-agree');
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            form.submit();
        };
    }
    document.getElementById('modal-confirm-agree-batch').style.display = 'flex';
}
</script>
@endpush




