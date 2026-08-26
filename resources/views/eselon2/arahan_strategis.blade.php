@extends('layouts.app')
@section('title', 'Arahan Strategis - Kepala Unit')
@section('header_title', 'Penetapan Arahan Strategis')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px;">🎯 Penetapan Arahan Strategis</h1>
    <button class="btn btn-primary" onclick="document.getElementById('modal-add').style.display='flex'">+ Buat Arahan Baru</button>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">✅ {{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-title" style="font-size:18px;">Daftar Arahan Strategis Kompetensi Unit</div>
    
    {{-- Filter & Sort Bar --}}
    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
        <div style="font-weight:600; color:var(--text-secondary);">🔍 Cari:</div>
        <input type="text" id="search-title" class="form-control" style="width:200px; height:32px; padding:0 8px; font-size:12px;" placeholder="Cari nama arahan..." onkeyup="filterAndSortArahan()">
        <div style="font-weight:600; color:var(--text-secondary); margin-left:8px;">Filter:</div>
        <select id="filter-priority" class="form-control" style="width:140px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortArahan()">
            <option value="">Semua Prioritas</option>
            <option value="Tinggi">Tinggi</option>
            <option value="Sedang">Sedang</option>
            <option value="Rendah">Rendah</option>
        </select>
        <select id="filter-status" class="form-control" style="width:160px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortArahan()">
            <option value="">Semua Status</option>
            <option value="Belum">Belum Ditindaklanjuti</option>
            <option value="Bangkom Unit">Sudah Ditindaklanjuti</option>
        </select>
        <select id="filter-competency" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortArahan()">
            <option value="">Semua Kompetensi</option>
            <optgroup label="Kompetensi Teknis">
                @foreach($kompetensiTeknis as $c)
                <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </optgroup>
            <optgroup label="Kompetensi Mansoskul">
                @foreach($kompetensiMansoskul as $c)
                <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </optgroup>
        </select>
        <div style="font-weight:600; color:var(--text-secondary); margin-left:12px;">⇅ Urutkan:</div>
        <select id="sort-by" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortArahan()">
            <option value="">Default</option>
            <option value="title-asc">Judul Arahan (A-Z)</option>
            <option value="title-desc">Judul Arahan (Z-A)</option>
            <option value="priority-desc">Prioritas: Tinggi-Rendah</option>
            <option value="priority-asc">Prioritas: Rendah-Tinggi</option>
        </select>
    </div>

    <table id="table-arahan">
        <thead>
            <tr><th>Judul Arahan</th><th>Kompetensi</th><th>Sasaran Pegawai</th><th>Prioritas</th><th>Periode</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse($directions as $d)
            <tr class="arahan-row" data-title="{{ strtolower($d->title) }}" data-priority="{{ $d->priority }}" data-status="{{ $d->follow_up }}" data-competency="{{ $d->competency }}">
                <td><strong>{{ $d->title }}</strong></td>
                <td>{{ $d->competency }}</td>
                <td>{{ $d->sasaran_pegawai ?? '-' }}</td>
                <td>
                    @if($d->priority === 'Tinggi') <span class="badge badge-danger">Tinggi</span>
                    @elseif($d->priority === 'Sedang') <span class="badge badge-warning">Sedang</span>
                    @else <span class="badge badge-neutral">Rendah</span>
                    @endif
                </td>
                <td>{{ $d->period }}</td>
                <td>
                    @if($d->bangkom_status === 'draft')
                        <span class="badge" style="background:rgba(234,179,8,0.2);color:#eab308;">Draft Rencana</span>
                    @elseif($d->bangkom_status === 'ditetapkan')
                        <span class="badge" style="background:rgba(59,130,246,0.2);color:#3b82f6;">Rencana Ditetapkan</span>
                    @elseif($d->bangkom_status === 'realisasi')
                        <span class="badge badge-success">Terealisasi</span>
                    @elseif($d->bangkom_status === 'selesai' || $d->bangkom_status === 'dievaluasi')
                        <span class="badge badge-success">✓ Selesai</span>
                    @elseif($d->bangkom_status === 'pembatalan diajukan')
                        <span class="badge badge-warning">Menunggu Batal</span>
                    @elseif($d->bangkom_status === 'pembatalan disetujui')
                        <span class="badge badge-danger">Rencana Dibatalkan</span>
                    @else
                        @if($d->follow_up === 'Belum')
                            <span class="badge" style="background:rgba(239,68,68,0.2);color:#ef4444;">Belum Ditindaklanjuti</span>
                        @else
                            <span class="badge badge-success">✓ Sudah</span>
                        @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-sm" style="text-align:center;padding:24px;">Belum ada arahan strategis. Klik "Buat Arahan Baru" untuk memulai.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $directions->links() }}
</div>

@push('scripts')
<script>
function matchQuery(text, query) {
    if (!query) return true;
    const targetText = text.toLowerCase().trim();
    const q = query.toLowerCase().trim();
    if (!q) return true;
    const words = targetText.split(/\s+/);
    const queryWords = q.split(/\s+/);
    return queryWords.every(qw => words.some(w => w.startsWith(qw)));
}

function filterAndSortArahan() {
    const searchQuery = document.getElementById('search-title').value.toLowerCase();
    const filterPriority = document.getElementById('filter-priority').value;
    const filterStatus = document.getElementById('filter-status').value;
    const filterCompetency = document.getElementById('filter-competency').value;
    const sortBy = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('.arahan-row'));
    
    rows.forEach(row => {
        const title = row.getAttribute('data-title') || '';
        const p = row.getAttribute('data-priority');
        const s = row.getAttribute('data-status');
        const c = row.getAttribute('data-competency');
        
        const matchSearch = matchQuery(title, searchQuery);
        const matchPriority = !filterPriority || p === filterPriority;
        const matchStatus = !filterStatus || s === filterStatus;
        const matchCompetency = !filterCompetency || c === filterCompetency;
        
        row.style.display = (matchSearch && matchPriority && matchStatus && matchCompetency) ? '' : 'none';
    });
    
    const tbody = document.querySelector('#table-arahan tbody');
    if (sortBy) {
        rows.sort((a, b) => {
            if (sortBy === 'title-asc') {
                return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
            } else if (sortBy === 'title-desc') {
                return b.getAttribute('data-title').localeCompare(a.getAttribute('data-title'));
            } else if (sortBy === 'priority-desc') {
                const pMap = { 'Tinggi': 3, 'Sedang': 2, 'Rendah': 1 };
                return pMap[b.getAttribute('data-priority')] - pMap[a.getAttribute('data-priority')];
            } else if (sortBy === 'priority-asc') {
                const pMap = { 'Tinggi': 3, 'Sedang': 2, 'Rendah': 1 };
                return pMap[a.getAttribute('data-priority')] - pMap[b.getAttribute('data-priority')];
            }
            return 0;
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }
}
</script>
@endpush

{{-- Modal Add --}}
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#1a2e45;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:580px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="font-size:16px;font-weight:600;">🎯 Buat Arahan Strategis Baru</h2>
            <button onclick="document.getElementById('modal-add').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <form method="POST" action="{{ route('eselon2.storeArahan') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Judul Arahan *</label>
                <input type="text" name="title" class="form-control" maxlength="100" required placeholder="Mis: Peningkatan Kompetensi Analisis Data JFA">
            </div>
            <div class="form-group">
                <label class="form-label">Dasar / Latar Belakang *</label>
                <textarea name="basis" class="form-control" rows="2" required placeholder="KAP, Renstra, Hasil penilaian kompetensi, dll."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Konteks / Tujuan *</label>
                <textarea name="context" class="form-control" rows="2" required placeholder="Jelaskan mengapa kompetensi ini dibutuhkan..."></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label class="form-label">Kompetensi yang Disasar *</label>
                    <select name="competency" class="form-control" required>
                        <option value="">-- Pilih Kompetensi --</option>
                        <optgroup label="🛠️ Kompetensi Teknis (13)">
                            @foreach($kompetensiTeknis as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="🤝 Kompetensi Mansoskul (12)">
                            @foreach($kompetensiMansoskul as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sasaran Pegawai *</label>
                    <select name="sasaran_pegawai" class="form-control" required>
                        <option value="Semua Pegawai">Semua Pegawai</option>
                        <option value="JFA">JFA</option>
                        <option value="Enabler">Enabler</option>
                        <option value="Koordinator/Korwas">Koordinator/Korwas</option>
                        <option value="Subkoordinator/Ketua Tim">Subkoordinator/Ketua Tim</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label class="form-label">Prioritas *</label>
                    <select name="priority" class="form-control" required>
                        <option value="Tinggi">Tinggi</option>
                        <option value="Sedang" selected>Sedang</option>
                        <option value="Rendah">Rendah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Periode *</label>
                    <input type="text" name="period" class="form-control" placeholder="Mis: 2026" required>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary">🎯 Tetapkan Arahan</button>
                <button type="button" onclick="document.getElementById('modal-add').style.display='none'" class="btn btn-neutral">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection



