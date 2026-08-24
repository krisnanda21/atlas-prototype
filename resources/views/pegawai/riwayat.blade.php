@extends('layouts.app')
@section('title', 'Riwayat Pengembangan')
@section('header_title', 'Riwayat Pengembangan Kompetensi')

@section('content')
<div class="page-header">
    <h1>📜 Riwayat Pengembangan Kompetensi</h1>
</div>

{{-- Summary KPIs --}}
@php
    $totalJP = collect($riwayats)->sum('jp');
    $selesai = collect($riwayats)->where('status', 'Selesai')->count() + collect($riwayats)->where('status', 'Lulus')->count();
@endphp
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value">{{ count($riwayats) }}</div>
        <div class="stat-label">Total Kegiatan</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $selesai }}</div>
        <div class="stat-label">Selesai / Lulus</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value">{{ $totalJP }}</div>
        <div class="stat-label">Total JP Diperoleh</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👤</div>
        <div class="stat-value">{{ $employee->name ?? '-' }}</div>
        <div class="stat-label" style="font-size:10px;">Pegawai Aktif</div>
    </div>
</div>

<div class="card">
    <div class="card-title">Riwayat Kegiatan Bangkom & Pembelajaran</div>
    <table>
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Nama Kegiatan</th>
                <th>Jenis</th>
                <th>JP</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="riwayat-table-body">
            @forelse($riwayats as $r)
            <tr>
                <td>{{ $r['tahun'] }}</td>
                <td><strong>{{ $r['kegiatan'] }}</strong></td>
                <td>
                    @php
                        $ic = match($r['jenis']) {
                            'Pelatihan formal' => '🎓',
                            'Bangkom Unit' => '🏢',
                            'Mandiri' => '📖',
                            default => '📋'
                        };
                    @endphp
                    {{ $ic }} {{ $r['jenis'] }}
                </td>
                <td>{{ $r['jp'] }} JP</td>
                <td>
                    @if($r['status'] === 'Lulus') <span class="badge badge-success">🏆 Lulus</span>
                    @elseif($r['status'] === 'Selesai') <span class="badge badge-success">✅ Selesai</span>
                    @elseif($r['status'] === 'Berjalan') <span class="badge badge-info">⏳ Berjalan</span>
                    @else <span class="badge badge-neutral">{{ $r['status'] }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:24px;">Belum ada riwayat pengembangan.</td></tr>
            @endforelse
        </tbody>
    </table>
    
    {{-- Pagination controls for Riwayat Bangkom --}}
    @if(count($riwayats) > 0)
    <div id="riwayat-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:8px;">
        <button id="btn-riwayat-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
        <span id="riwayat-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
        <button id="btn-riwayat-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    paginateTable('riwayat-table-body', 'riwayat-pagination-controls', 'btn-riwayat-prev', 'btn-riwayat-next', 'riwayat-page-info', 10);
});
</script>
@endpush
