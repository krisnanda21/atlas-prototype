@extends('layouts.app')
@section('title', 'Profil 360 - Kompetensi & IDP')
@section('header_title', 'Profil 360° Kompetensi & IDP')

@section('content')
<div class="page-header">
    <h1>🔮 Profil 360° Kompetensi & IDP</h1>
</div>


@if($employee)
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    {{-- Employee Summary --}}
    <div class="card">
        <div class="card-title">👤 Profil Pegawai</div>
        <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">Nama</span>
                <strong>{{ $employee->name }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">NIP</span>
                <strong>{{ $employee->id }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">Jabatan</span>
                <strong>{{ $employee->role }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">Unit</span>
                <strong>{{ $employee->unit }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">Kategori</span>
                <strong>{{ $employee->category }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">Pangkat</span>
                <strong>{{ $employee->pangkat ?? '-' }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">Pendidikan</span>
                <strong>{{ $employee->strata ?? '-' }} {{ $employee->jurusan ? '- ' . $employee->jurusan : '' }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">Sertifikasi Bahasa</span>
                <strong>
                    @if($employee->toefl || $employee->ielts)
                        {{ $employee->toefl ? 'TOEFL: ' . $employee->toefl : '' }}
                        {{ $employee->toefl && $employee->ielts ? ' | ' : '' }}
                        {{ $employee->ielts ? 'IELTS: ' . $employee->ielts : '' }}
                    @else
                        -
                    @endif
                </strong>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="color:var(--text-secondary);">Assessment</span>
                @if($employee->assessment)
                <span class="badge badge-success">✓ COMPASS</span>
                @else
                <span class="badge badge-neutral">Non-Assessment</span>
                @endif
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;">
                <span style="color:var(--text-secondary);">IDP Gap Coverage</span>
                @php
                    $cov = $employee->idp_coverage ?? ($coverageDetail['coverage_percent'] ?? 0);
                    $badgeClass = $cov >= 75 ? 'badge-success' : ($cov >= 50 ? 'badge-warning' : 'badge-danger');
                @endphp
                <div style="text-align:right;">
                    <span class="badge {{ $badgeClass }}" style="font-weight:700;">{{ $cov }}%</span>
                    @if(isset($coverageDetail) && $coverageDetail['total_target'] > 0)
                    <div style="font-size:10.5px;color:var(--text-secondary);margin-top:2px;">{{ $coverageDetail['matched_count'] }}/{{ $coverageDetail['total_target'] }} gap tertutupi</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Competency Gaps --}}
    <div class="card">
        <div class="card-title">📊 Kebutuhan Kompetensi / Gap</div>
        
        {{-- Toggle buttons: Teknis / Mansoskul --}}
        <div style="display:flex;gap:8px;margin-bottom:16px;">
            <button id="btn-show-teknis" class="btn btn-primary" style="flex:1;font-size:12px;padding:8px 12px;cursor:pointer;">📐 Kompetensi Teknis</button>
            <button id="btn-show-mansoskul" class="btn btn-neutral" style="flex:1;font-size:12px;padding:8px 12px;cursor:pointer;">🤝 Kompetensi Mansoskul</button>
        </div>

        @if($needs->count())
        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach($needs as $need)
            @php
                $color = match($need->level) {
                    'Tidak optimal' => 'var(--danger)',
                    'Kurang optimal' => 'var(--warning)',
                    'Cukup optimal' => '#eab308', // Amber/Yellow
                    default => 'var(--success)'
                };
                $pct = $need->type === 'Teknis' ? $need->score : ($need->score / 5) * 100;
            @endphp
            <div class="gap-item" data-type="{{ $need->type }}" style="background:rgba(255,255,255,0.04);border-radius:6px;padding:10px 12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;font-weight:600;">{{ $need->competency_name }}</span>
                    <span style="font-size:13px;font-weight:700;color:{{ $color }}">{{ $need->score }}</span>
                </div>
                <div style="background:rgba(255,255,255,0.08);border-radius:4px;height:6px;margin-top:6px;overflow:hidden;">
                    <div style="height:100%;width:{{ $pct }}%;background:{{ $color }};"></div>
                </div>
                <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;">Standard: {{ $need->standard }} | Gap: {{ $need->gap ?? '-' }} | Level: {{ $need->level ?? '-' }}</div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-muted text-sm">Tidak ada data gap kompetensi.</p>
        @endif
    </div>
</div>

{{-- IDP Items --}}
<div class="card" style="margin-top:20px;">
    <div class="card-title">📋 Daftar Item IDP</div>
    <table>
        <thead>
            <tr><th>Kebutuhan</th><th>Sumber</th><th>Basis</th><th>Prioritas</th><th>Status</th></tr>
        </thead>
        <tbody id="idp-table-body">
            @forelse($idpItems as $idp)
            <tr>
                <td><strong>{{ $idp->need }}</strong></td>
                <td>{{ $idp->source }}</td>
                <td>{{ Str::limit($idp->basis ?? '', 80) }}</td>
                <td>
                    @if($idp->priority === 'Tinggi') <span class="badge badge-danger">Tinggi</span>
                    @elseif($idp->priority === 'Sedang') <span class="badge badge-warning">Sedang</span>
                    @else <span class="badge badge-neutral">Rendah</span>
                    @endif
                </td>
                <td>
                    @php
                        $sc = match($idp->status) { 'Disepakati' => 'badge-success', 'Diajukan' => 'badge-info', 'Perlu Perbaikan' => 'badge-danger', default => 'badge-neutral' };
                    @endphp
                    <span class="badge {{ $sc }}">{{ $idp->status }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada item IDP.</td></tr>
            @endforelse
        </tbody>
    </table>
    
    {{-- Pagination controls for IDP --}}
    @if(count($idpItems) > 0)
    <div id="idp-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:8px;">
        <button id="btn-idp-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
        <span id="idp-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
        <button id="btn-idp-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
    </div>
    @endif
</div>

{{-- Riwayat Pelatihan / Diklat --}}
<div class="card" style="margin-top:20px;">
    <div class="card-title">📚 Riwayat Pelatihan / Diklat (SMILE)</div>
    <table>
        <thead>
            <tr>
                <th>Nama Pelatihan / Diklat</th>
                <th>Durasi (JP)</th>
                <th>No. Sertifikat</th>
                <th>Status Dokumen</th>
            </tr>
        </thead>
        <tbody id="diklat-table-body">
            @forelse($diklats as $diklat)
            <tr>
                <td><strong>{{ $diklat->nama_diklat }}</strong></td>
                <td>{{ $diklat->jumlah_jam }} JP</td>
                <td>{{ $diklat->no_sertifikat }}</td>
                <td>
                    @if($diklat->dokumen)
                        <span class="badge badge-success">✓ Ada (Dokumen {{ $diklat->dokumen }})</span>
                    @else
                        <span class="badge badge-neutral">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada riwayat pelatihan/diklat.</td></tr>
            @endforelse
        </tbody>
    </table>
    
    {{-- Pagination controls for Diklat --}}
    @if(count($diklats) > 0)
    <div id="diklat-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:8px;">
        <button id="btn-diklat-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
        <span id="diklat-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
        <button id="btn-diklat-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
    </div>
    @endif
</div>

{{-- Riwayat Sertifikasi Profesional --}}
<div class="card" style="margin-top:20px;">
    <div class="card-title">🏅 Riwayat Sertifikasi Profesional (SMILE)</div>
    <table>
        <thead>
            <tr>
                <th>Nama Sertifikasi</th>
                <th>Nomor Sertifikasi</th>
                <th>Status Dokumen</th>
            </tr>
        </thead>
        <tbody id="sert-table-body">
            @forelse($sertifikasis as $sert)
            <tr>
                <td><strong>{{ $sert->nama_sertifikasi }}</strong></td>
                <td>{{ $sert->nomor_sertifikasi }}</td>
                <td>
                    @if($sert->dokumen)
                        <span class="badge badge-success">✓ Ada (Dokumen {{ $sert->dokumen }})</span>
                    @else
                        <span class="badge badge-neutral">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada riwayat sertifikasi profesional.</td></tr>
            @endforelse
        </tbody>
    </table>
    
    {{-- Pagination controls for Sertifikasi --}}
    @if(count($sertifikasis) > 0)
    <div id="sert-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:8px;">
        <button id="btn-sert-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
        <span id="sert-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
        <button id="btn-sert-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
    </div>
    @endif
</div>
@else
<div class="card" style="text-align:center;padding:40px;">
    <div style="font-size:48px;margin-bottom:12px;">🔍</div>
    <p class="text-muted">Profil kompetensi Anda tidak ditemukan.</p>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTeknis = document.getElementById('btn-show-teknis');
    const btnMansoskul = document.getElementById('btn-show-mansoskul');
    const gapItems = document.querySelectorAll('.gap-item');

    function showType(type) {
        if (type === 'Teknis') {
            btnTeknis.className = 'btn btn-primary';
            btnMansoskul.className = 'btn btn-neutral';
            btnTeknis.style.pointerEvents = 'none'; // prevent redundant clicks
            btnMansoskul.style.pointerEvents = 'auto';
        } else {
            btnTeknis.className = 'btn btn-neutral';
            btnMansoskul.className = 'btn btn-primary';
            btnTeknis.style.pointerEvents = 'auto';
            btnMansoskul.style.pointerEvents = 'none'; // prevent redundant clicks
        }

        gapItems.forEach(item => {
            const targetType = type === 'Teknis' ? 'Teknis' : 'Mansoskul';
            if (item.getAttribute('data-type') === targetType) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    if (btnTeknis && btnMansoskul) {
        btnTeknis.addEventListener('click', () => showType('Teknis'));
        btnMansoskul.addEventListener('click', () => showType('Mansoskul'));

        // Default: show Teknis first
        showType('Teknis');
    }

    // Client-side pagination helper for tables
    function paginateTable(tbodyId, controlsId, prevBtnId, nextBtnId, infoId, pageSize = 5) {
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

    // Initialize pagination
    paginateTable('idp-table-body', 'idp-pagination-controls', 'btn-idp-prev', 'btn-idp-next', 'idp-page-info');
    paginateTable('diklat-table-body', 'diklat-pagination-controls', 'btn-diklat-prev', 'btn-diklat-next', 'diklat-page-info');
    paginateTable('sert-table-body', 'sert-pagination-controls', 'btn-sert-prev', 'btn-sert-next', 'sert-page-info');
});
</script>
@endpush
