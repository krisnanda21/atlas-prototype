@extends('layouts.app')
@section('title', 'Approve Rencana Bangkom')
@section('header_title', 'Approval Rencana Bangkom')

@section('content')
<div class="page-header">
    <h1>✅ Approval Rencana Bangkom</h1>
</div>

@if(session('success'))
<div class="alert-success" style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">
    ✅ {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-title">Rencana Kegiatan Menunggu Penetapan</div>
    
    {{-- Filter & Sort Bar --}}
    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
        <div style="font-weight:600; color:var(--text-secondary);">🔍 Cari:</div>
        <input type="text" id="search-title" class="form-control" style="width:200px; height:32px; padding:0 8px; font-size:12px;" placeholder="Cari nama kegiatan..." onkeyup="filterAndSortPlans()">
        <div style="font-weight:600; color:var(--text-secondary); margin-left:8px;">Filter:</div>
        <select id="filter-metode" class="form-control" style="width:140px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortPlans()">
            <option value="">Semua Metode</option>
            <option value="Luring">Luring</option>
            <option value="Daring">Daring</option>
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
                <th>Pengampu</th>
                <th>Tanggal</th>
                <th>JP</th>
                <th>Peserta</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingPlans as $p)
            @php
                $decoded = json_decode($p->kriteria_peserta ?? '', true);
                $pCount = is_array($decoded) ? count($decoded) : 0;
            @endphp
            <tr class="plan-row" data-title="{{ strtolower($p->nama_kegiatan) }}" data-competency="{{ $p->kompetensi_dasar }}" data-jp="{{ $p->jp }}" data-metode="{{ $p->metode }}">
                <td>
                    <strong>{{ $p->nama_kegiatan }}</strong><br>
                    <span class="text-muted text-sm">{{ $p->tujuan_kegiatan ? Str::limit($p->tujuan_kegiatan, 80) : '' }}</span>
                </td>
                <td>{{ $p->kompetensi_dasar }}<br><span class="text-sm text-muted">{{ $p->jenis_latar_belakang }}</span></td>
                <td>{{ $p->unit_pengusul }}</td>
                <td>{{ $p->tanggal_mulai }}<br>s/d {{ $p->tanggal_selesai }}</td>
                <td>{{ $p->jp }} JP<br><span class="text-sm text-muted">{{ $p->metode }}</span></td>
                <td>{{ $pCount }} kriteria</td>
                <td style="display:flex;flex-direction:column;gap:6px;">
                    <form method="POST" action="{{ route('eselon3.approveBangkom', $p->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success" style="width:100%;">✅ Tetapkan</button>
                    </form>
                    <button class="btn btn-sm btn-danger" onclick="openRevisiModal('{{ $p->id }}','{{ addslashes($p->nama_kegiatan) }}')">Perlu Revisi</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted text-sm" style="text-align:center;padding:24px;">Tidak ada rencana yang menunggu penetapan. ✨</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Riwayat Approval --}}
<div class="card" style="margin-top:20px;">
    <div class="card-title">📜 Riwayat Penetapan</div>
    <table>
        <thead>
            <tr><th>Nama Kegiatan</th><th>Tanggal Penetapan</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse($approvedPlans as $p)
            <tr>
                <td>{{ $p->nama_kegiatan }}</td>
                <td>{{ $p->updated_at->format('d M Y') }}</td>
                <td>
                    @if($p->status === 'Ditetapkan') <span class="badge badge-success">✅ Ditetapkan</span>
                    @elseif($p->status === 'Perlu Revisi') <span class="badge badge-danger">Perlu Revisi</span>
                    @elseif($p->status === 'Pembatalan Diajukan') <span class="badge badge-warning">Pembatalan Diajukan</span>
                    @else <span class="badge badge-neutral">{{ $p->status }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-muted text-sm" style="text-align:center;padding:16px;">Belum ada riwayat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal Revisi --}}
<div id="modal-revisi" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#1a2e45;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:460px;">
        <h2 style="font-size:16px;margin-bottom:12px;">✏️ Perlu Revisi</h2>
        <p class="text-sm text-muted" id="revisi-plan-name" style="margin-bottom:14px;"></p>
        <form method="POST" id="revisi-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Catatan Revisi untuk Pengampu SDM *</label>
                <textarea name="revision_notes" class="form-control" rows="4" required placeholder="Wajib diisi..."></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:12px;">
                <button type="submit" class="btn btn-warning">Kirim Catatan Revisi</button>
                <button type="button" onclick="document.getElementById('modal-revisi').style.display='none'" class="btn btn-neutral">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRevisiModal(id, title) {
    document.getElementById('revisi-plan-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('revisi-form').action = `/eselon3/rencana/${id}/revisi`;
    document.getElementById('modal-revisi').style.display = 'flex';
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

function filterAndSortPlans() {
    const searchQuery = document.getElementById('search-title').value.toLowerCase();
    const filterMetode = document.getElementById('filter-metode').value;
    const filterCompetency = document.getElementById('filter-competency').value;
    const sortBy = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('.plan-row'));
    
    rows.forEach(row => {
        const title = row.getAttribute('data-title') || '';
        const m = row.getAttribute('data-metode');
        const c = row.getAttribute('data-competency');
        
        const matchSearch = matchQuery(title, searchQuery);
        const matchMetode = !filterMetode || m === filterMetode;
        const matchCompetency = !filterCompetency || c === filterCompetency;
        
        row.style.display = (matchSearch && matchMetode && matchCompetency) ? '' : 'none';
    });
    
    const tbody = document.querySelector('#table-plans tbody');
    if (sortBy) {
        rows.sort((a, b) => {
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
        
        rows.forEach(row => tbody.appendChild(row));
    }
}
</script>
@endpush





