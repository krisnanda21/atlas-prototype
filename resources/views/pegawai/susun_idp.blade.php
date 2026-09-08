@extends('layouts.app')
@section('title', 'Susun IDP Saya')
@section('header_title', 'Susun IDP Saya')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">📝 Susun IDP Saya</h1>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:var(--danger);margin-bottom:16px;">❌ {{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
    {{-- Rekomendasi Sistem --}}
    <div class="card">
        <div class="card-title" style="font-size:18px;">💡 Rekomendasi Sistem</div>
        <p class="text-sm text-muted" style="margin-bottom:12px;">Berdasarkan gap COMPASS, jabatan, dan arahan strategis unit Anda.</p>
        @forelse($recommendations as $rec)
        <div class="rec-item" style="background:#F8FAFC;border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:10px 12px;margin-bottom:8px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                <div style="font-weight:600;font-size:13px;">{{ $rec['need'] }}</div>
                @if(($rec['competency_type'] ?? '') === 'Manajerial')
                    <span class="badge" style="font-size:10px;background:rgba(168,85,247,0.15);color:#c084fc;border:1px solid rgba(168,85,247,0.3);flex-shrink:0;">Manajerial & Soskul</span>
                @else
                    <span class="badge badge-info" style="font-size:10px;flex-shrink:0;">Teknis</span>
                @endif
            </div>
            <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">{{ $rec['basis'] }}</div>
            <div style="margin-top:8px;display:flex;gap:6px;align-items:center;">
                @if($rec['priority'] === 'Tinggi')
                    <span class="badge badge-danger" style="font-size:10px;">Prioritas: Tinggi</span>
                @elseif($rec['priority'] === 'Sedang')
                    <span class="badge badge-warning" style="font-size:10px;">Prioritas: Sedang</span>
                @else
                    <span class="badge badge-neutral" style="font-size:10px;">Prioritas: Rendah</span>
                @endif
                <form method="POST" action="{{ route('pegawai.storeIdp') }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="need" value="{{ $rec['need'] }}">
                    <input type="hidden" name="competency_type" value="{{ $rec['competency_type'] ?? 'Teknis' }}">
                    <input type="hidden" name="source" value="{{ $rec['source'] }}">
                    <input type="hidden" name="basis" value="{{ $rec['basis'] }}">
                    <input type="hidden" name="priority" value="{{ $rec['priority'] }}">
                    <button type="submit" class="btn btn-sm btn-primary" style="font-size:11px;">+ Tambah ke IDP</button>
                </form>
            </div>
        </div>
        @empty
        <p class="text-muted text-sm">Tidak ada rekomendasi saat ini.</p>
        @endforelse

        {{-- Pagination controls for recommendations --}}
        @if(count($recommendations) > 0)
        <div id="rec-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:8px;">
            <button id="btn-rec-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
            <span id="rec-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
            <button id="btn-rec-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
        </div>
        @endif
    </div>

    {{-- Tambah Manual --}}
    <div class="card">
        <div class="card-title" style="font-size:18px;">✍️ Tambah IDP Manual</div>
        <form method="POST" action="{{ route('pegawai.storeIdp') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Jenis Kompetensi / Kebutuhan *</label>
                <select name="need" class="form-control" required style="background:#F8FAFC;color:var(--text-primary);">
                    <option value="">-- Pilih --</option>
                    <optgroup label="Kompetensi Teknis">
                        @foreach($competenciesTeknis as $comp)
                        <option value="{{ $comp }}">{{ $comp }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Kompetensi Manajerial & Sosial Kultural (Mansos)">
                        @foreach($competenciesMansos as $comp)
                        <option value="{{ $comp }}">{{ $comp }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Sumber *</label>
                <select name="source" class="form-control" required style="background:#F8FAFC;color:var(--text-primary);">
                    <option value="assessment-based">Gap Compass</option>
                    <option value="role-based">Role Based</option>
                    <option value="mandatory-based">Mandatory Learning</option>
                    <option value="unit-strategic-direction-based">Strategic Direction Unit</option>
                    <option value="self-initiative">Inisiatif Mandiri</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Basis / Alasan *</label>
                <textarea name="basis" class="form-control" rows="3" required placeholder="Tuliskan alasan kebutuhan kompetensi ini..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Prioritas *</label>
                <select name="priority" class="form-control" required>
                    <option value="Tinggi">Tinggi</option>
                    <option value="Sedang" selected>Sedang</option>
                    <option value="Rendah">Rendah</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">💾 Simpan sebagai Draft IDP</button>
        </form>
    </div>
</div>

{{-- Draft IDP List --}}
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
        <div class="card-title" style="margin:0;font-size:18px;">📋 Draft & Diajukan IDP Saya</div>
        <div style="display:flex;align-items:center;gap:10px;">
            <button type="button" id="btn-batch-delete" class="btn btn-danger" style="display:none;font-size:12px;padding:6px 14px;border-radius:6px;gap:6px;align-items:center;" onclick="deleteBatchIdp()">
                <span>🗑️</span> <span>Hapus Terpilih (<strong id="batch-delete-count">0</strong>)</span>
            </button>
            <button type="button" id="btn-batch-submit" class="btn btn-primary" style="display:none;font-size:12px;padding:6px 14px;border-radius:6px;gap:6px;align-items:center;" onclick="submitBatchIdp()">
                <span>🚀</span> <span>Ajukan Terpilih (<strong id="batch-selected-count">0</strong>)</span>
            </button>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:40px;text-align:center;">
                    <input type="checkbox" id="check-all-idp" title="Pilih Semua Draft / Perlu Perbaikan" style="cursor:pointer;accent-color:var(--primary);width:16px;height:16px;">
                </th>
                <th>Kebutuhan</th>
                <th>Klaster</th>
                <th>Sumber</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="draft-table-body">
            @forelse($myIdps as $idp)
            @php
                $sourceLabels = [
                    'assessment-based' => 'Gap Compass',
                    'role-based' => 'Role Based',
                    'mandatory-based' => 'Mandatory Learning',
                    'unit-strategic-direction-based' => 'Strategic Direction Unit',
                    'self-initiative' => 'Inisiatif Mandiri',
                ];
                $isDraftOrRevision = in_array($idp->status, ['Draft', 'Perlu Perbaikan']);
            @endphp
            <tr class="draft-idp-row">
                <td style="text-align:center;">
                    @if($isDraftOrRevision)
                        <input type="checkbox" class="idp-row-checkbox" value="{{ $idp->id }}" style="cursor:pointer;accent-color:var(--primary);width:16px;height:16px;" onchange="updateBatchSubmitState()">
                    @else
                        <span style="color:var(--text-secondary);font-size:12px;">-</span>
                    @endif
                </td>
                <td><strong>{{ $idp->need }}</strong><br><span class="text-muted text-sm">{{ Str::limit($idp->basis, 60) }}</span></td>
                <td>
                    @if(($idp->competency_type ?? '') === 'Manajerial')
                        <span class="badge" style="font-size:10px;background:rgba(168,85,247,0.15);color:#c084fc;border:1px solid rgba(168,85,247,0.3);">Manajerial & Soskul</span>
                    @else
                        <span class="badge badge-info" style="font-size:10px;">Teknis</span>
                    @endif
                </td>
                <td><span class="badge badge-neutral" style="font-size:11px;">{{ $sourceLabels[$idp->source] ?? $idp->source }}</span></td>
                <td>
                    @if($idp->priority === 'Tinggi') <span class="badge badge-danger">Tinggi</span>
                    @elseif($idp->priority === 'Sedang') <span class="badge badge-warning">Sedang</span>
                    @else <span class="badge badge-neutral">Rendah</span>
                    @endif
                </td>
                <td>
                    @php $sc = match($idp->status) { 'Disepakati' => 'badge-success', 'Diajukan' => 'badge-info', 'Perlu Perbaikan' => 'badge-danger', default => 'badge-neutral' }; @endphp
                    <span class="badge {{ $sc }}">{{ $idp->status }}</span>
                </td>
                <td>
                    @if($idp->status === 'Draft' || $idp->status === 'Perlu Perbaikan')
                        <div style="display:flex;gap:6px;align-items:center;">
                            <button type="button" class="btn btn-sm btn-primary" onclick="submitIdpRow('{{ $idp->id }}')">
                                Ajukan
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" style="background:#eab308;border:none;color:#000;" onclick="openEditIdpModal('{{ $idp->id }}', '{{ addslashes($idp->need) }}', '{{ $idp->source }}', '{{ addslashes($idp->basis) }}', '{{ $idp->priority }}', '{{ addslashes($idp->revision_note ?? '') }}')">
                                Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteIdpRow('{{ $idp->id }}')">
                                Hapus
                            </button>
                        </div>
                    @elseif($idp->status === 'Diajukan')
                        <span class="badge badge-info" style="font-size:11px;">
                            Menunggu Review
                        </span>
                    @else
                        <span class="badge badge-success" style="font-size:11px;">
                            Disepakati
                        </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada draft IDP. Tambahkan dari rekomendasi atau manual di atas.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{-- Hidden form for Batch Submit --}}
    <form id="form-submit-batch-idp" method="POST" action="{{ route('pegawai.submitIdpBatch') }}" style="display:none;">
        @csrf
    </form>
    {{-- Hidden form for JavaScript submission to bypass HTML nested form quirks --}}
    <form id="form-submit-idp" method="POST" action="" style="display:none;">
        @csrf
    </form>
    <form id="form-delete-idp" method="POST" action="" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
    
    {{-- Pagination controls for Draft IDP --}}
    @if(count($myIdps) > 0)
    <div id="draft-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:8px;">
        <button id="btn-draft-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
        <span id="draft-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
        <button id="btn-draft-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
    </div>
    @endif
</div>

{{-- Custom Edit IDP Modal --}}
<div id="atlas-edit-idp-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.8);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s ease-in-out;">
    <div class="atlas-modal-content" style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;width:95%;max-width:500px;padding:24px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.5), 0 10px 10px -5px rgba(0,0,0,0.5);transform:scale(0.95);transition:transform 0.2s ease-in-out;font-family:'Inter', sans-serif;">
        <h3 style="margin:0 0 16px 0;font-size:18px;font-weight:700;color:#fff;text-align:left;">✍️ Edit Item IDP</h3>
        
        {{-- Revision note banner --}}
        <div id="edit-revision-note-container" style="padding:12px 14px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:8px;margin-bottom:20px;color:var(--danger);font-size:13px;text-align:left;line-height:1.4;">
            <strong>Catatan Perbaikan Atasan:</strong>
            <p id="edit-revision-note-text" style="margin:4px 0 0 0;font-weight:500;"></p>
        </div>

        <form id="form-update-idp" method="POST" action="">
            @csrf
            @method('PUT')
            
            <div class="form-group" style="text-align:left;margin-bottom:14px;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jenis Kompetensi / Kebutuhan *</label>
                <select name="need" id="edit-idp-need" class="form-control" required style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                    <option value="">-- Pilih --</option>
                    <optgroup label="Kompetensi Teknis">
                        @foreach($competenciesTeknis as $comp)
                        <option value="{{ $comp }}">{{ $comp }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Kompetensi Manajerial & Sosial Kultural (Mansos)">
                        @foreach($competenciesMansos as $comp)
                        <option value="{{ $comp }}">{{ $comp }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            
            <div class="form-group" style="text-align:left;margin-bottom:14px;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Sumber *</label>
                <select name="source" id="edit-idp-source" class="form-control" required style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                    <option value="assessment-based">Gap Compass</option>
                    <option value="role-based">Role Based</option>
                    <option value="mandatory-based">Mandatory Learning</option>
                    <option value="unit-strategic-direction-based">Strategic Direction Unit</option>
                    <option value="self-initiative">Inisiatif Mandiri</option>
                </select>
            </div>

            <div class="form-group" style="text-align:left;margin-bottom:14px;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Basis / Alasan *</label>
                <textarea name="basis" id="edit-idp-basis" class="form-control" rows="3" required style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;resize:vertical;font-size:13px;"></textarea>
            </div>

            <div class="form-group" style="text-align:left;margin-bottom:20px;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Prioritas *</label>
                <select name="priority" id="edit-idp-priority" class="form-control" required style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                    <option value="Tinggi">Tinggi</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Rendah">Rendah</option>
                </select>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" id="btn-close-edit-modal" class="btn btn-neutral" style="padding:10px 20px;font-weight:600;cursor:pointer;border-radius:6px;border:none;background:#F1F5F9;color:#fff;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;font-weight:600;cursor:pointer;border-radius:6px;border:none;background:var(--primary);color:#fff;">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const recItems = document.querySelectorAll('.rec-item');
    const btnRecPrev = document.getElementById('btn-rec-prev');
    const btnRecNext = document.getElementById('btn-rec-next');
    const txtRecPageInfo = document.getElementById('rec-page-info');

    let currentRecPage = 1;
    const recPageSize = 5;
    const totalRecs = recItems.length;
    const totalRecPages = Math.ceil(totalRecs / recPageSize) || 1;

    function updateRecPagination() {
        if (currentRecPage > totalRecPages) currentRecPage = totalRecPages;
        if (currentRecPage < 1) currentRecPage = 1;

        const start = (currentRecPage - 1) * recPageSize;
        const end = start + recPageSize;

        recItems.forEach((item, index) => {
            if (index >= start && index < end) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });

        if (txtRecPageInfo) {
            txtRecPageInfo.textContent = `Halaman ${currentRecPage} dari ${totalRecPages}`;
        }
        if (btnRecPrev) btnRecPrev.disabled = currentRecPage === 1;
        if (btnRecNext) btnRecNext.disabled = currentRecPage === totalRecPages;
    }

    if (btnRecPrev) {
        btnRecPrev.addEventListener('click', function() {
            if (currentRecPage > 1) {
                currentRecPage--;
                updateRecPagination();
            }
        });
    }

    if (btnRecNext) {
        btnRecNext.addEventListener('click', function() {
            if (currentRecPage < totalRecPages) {
                currentRecPage++;
                updateRecPagination();
            }
        });
    }

    // Initialize
    if (totalRecs > 0) {
        updateRecPagination();
    } else {
        const controls = document.getElementById('rec-pagination-controls');
        if (controls) controls.style.display = 'none';
    }

    // Client-side pagination helper for tables
    function paginateTable(tbodyId, controlsId, prevBtnId, nextBtnId, infoId, pageSize = 10) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        const rows = Array.from(tbody.children);
        
        // Exclude the 'no data' row if it exists
        if (rows.length === 1 && rows[0].cells.length === 1) {
            const controls = document.getElementById(controlsId);
            if (controls) controls.style.display = 'none';
            return;
        }

        const btnPrev = document.getElementById(prevBtnId);
        const btnNext = document.getElementById(nextBtnId);
        const infoSpan = document.getElementById(infoId);

        let currentPage = 1;
        const totalPages = Math.ceil(rows.length / pageSize) || 1;

        function update() {
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;

            rows.forEach((row, idx) => {
                if (idx >= start && idx < end) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            if (infoSpan) infoSpan.textContent = `Halaman ${currentPage} dari ${totalPages}`;
            if (btnPrev) btnPrev.disabled = currentPage === 1;
            if (btnNext) btnNext.disabled = currentPage === totalPages;
        }

        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    update();
                }
            });
        }

        if (btnNext) {
            btnNext.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    update();
                }
            });
        }

        update();
    }

    paginateTable('draft-table-body', 'draft-pagination-controls', 'btn-draft-prev', 'btn-draft-next', 'draft-page-info', 10);

    // Batch checkbox selection state logic
    const checkAllIdp = document.getElementById('check-all-idp');
    if (checkAllIdp) {
        checkAllIdp.addEventListener('change', function() {
            const isChecked = this.checked;
            const checkboxes = document.querySelectorAll('.idp-row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
            });
            window.updateBatchSubmitState();
        });
    }
});

window.updateBatchSubmitState = function() {
    const checkboxes = document.querySelectorAll('.idp-row-checkbox');
    const checked = document.querySelectorAll('.idp-row-checkbox:checked');
    const count = checked.length;
    const btnBatch = document.getElementById('btn-batch-submit');
    const txtCount = document.getElementById('batch-selected-count');
    const btnBatchDelete = document.getElementById('btn-batch-delete');
    const txtDeleteCount = document.getElementById('batch-delete-count');
    const checkAll = document.getElementById('check-all-idp');

    if (txtCount) txtCount.textContent = count;
    if (txtDeleteCount) txtDeleteCount.textContent = count;

    if (btnBatch) {
        if (count > 0) {
            btnBatch.style.display = 'inline-flex';
            if (btnBatchDelete) btnBatchDelete.style.display = 'inline-flex';
        } else {
            btnBatch.style.display = 'none';
            if (btnBatchDelete) btnBatchDelete.style.display = 'none';
        }
    }

    if (checkAll && checkboxes.length > 0) {
        if (count === 0) {
            checkAll.checked = false;
            checkAll.indeterminate = false;
        } else if (count === checkboxes.length) {
            checkAll.checked = true;
            checkAll.indeterminate = false;
        } else {
            checkAll.checked = false;
            checkAll.indeterminate = true;
        }
    }
};

window.deleteBatchIdp = function() {
    const checked = Array.from(document.querySelectorAll('.idp-row-checkbox:checked'));
    const count = checked.length;
    if (count === 0) return;

    window.atlasConfirm({
        title: 'Konfirmasi Hapus IDP Terpilih',
        message: `Apakah Anda yakin ingin menghapus ${count} item IDP terpilih secara permanen?`,
        confirmText: 'Ya, Hapus Semua',
        confirmClass: 'btn btn-danger'
    }).then((confirmed) => {
        if (confirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("pegawai.deleteIdpBatch") }}';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';
            
            form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">
                              <input type="hidden" name="_method" value="DELETE">`;
            
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }
    });
};

window.submitBatchIdp = function() {
    const checked = Array.from(document.querySelectorAll('.idp-row-checkbox:checked'));
    const count = checked.length;
    if (count === 0) return;

    window.atlasConfirm({
        title: 'Konfirmasi Pengajuan IDP Terpilih',
        message: `Apakah Anda yakin ingin mengajukan ${count} item IDP terpilih ke atasan sekaligus?`,
        confirmText: 'Ya, Ajukan Semua',
        confirmClass: 'btn btn-primary'
    }).then((confirmed) => {
        if (confirmed) {
            const form = document.getElementById('form-submit-batch-idp');
            // Clear any previously injected inputs
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            form.submit();
        }
    });
};

window.submitIdpRow = function(id) {
    window.atlasConfirm({
        title: 'Konfirmasi Pengajuan IDP',
        message: 'Apakah Anda yakin ingin mengajukan item IDP ini ke atasan?',
        confirmText: 'Ya, Ajukan',
        confirmClass: 'btn btn-primary'
    }).then((confirmed) => {
        if (confirmed) {
            const form = document.getElementById('form-submit-idp');
            form.action = `/susun-idp/${id}/submit`;
            form.submit();
        }
    });
};

window.deleteIdpRow = function(id) {
    window.atlasConfirm({
        title: 'Konfirmasi Penghapusan IDP',
        message: 'Apakah Anda yakin ingin menghapus draft IDP ini?',
        confirmText: 'Ya, Hapus',
        confirmClass: 'btn btn-danger'
    }).then((confirmed) => {
        if (confirmed) {
            const form = document.getElementById('form-delete-idp');
            form.action = `/susun-idp/${id}`;
            form.submit();
        }
    });
};

window.openEditIdpModal = function(id, need, source, basis, priority, revisionNote) {
    const modal = document.getElementById('atlas-edit-idp-modal');
    if (!modal) return;
    const modalContent = modal.querySelector('.atlas-modal-content');
    const form = document.getElementById('form-update-idp');
    
    // Set form action and field values
    form.action = `/susun-idp/${id}`;
    document.getElementById('edit-idp-need').value = need;
    document.getElementById('edit-idp-source').value = source;
    document.getElementById('edit-idp-basis').value = basis;
    document.getElementById('edit-idp-priority').value = priority;

    // Show/hide revision note
    const noteContainer = document.getElementById('edit-revision-note-container');
    const noteText = document.getElementById('edit-revision-note-text');
    if (revisionNote && revisionNote.trim() !== '') {
        noteText.textContent = revisionNote;
        noteContainer.style.display = 'block';
    } else {
        noteContainer.style.display = 'none';
    }

    // Open modal with zoom animation
    modal.style.display = 'flex';
    modal.offsetHeight; // trigger reflow
    modal.style.opacity = '1';
    modalContent.style.transform = 'scale(1)';
};

window.closeEditIdpModal = function() {
    const modal = document.getElementById('atlas-edit-idp-modal');
    if (!modal) return;
    const modalContent = modal.querySelector('.atlas-modal-content');
    modal.style.opacity = '0';
    modalContent.style.transform = 'scale(0.95)';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 200);
};

document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.getElementById('btn-close-edit-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', window.closeEditIdpModal);
    }
});
</script>
@endpush
