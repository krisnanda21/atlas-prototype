@extends('layouts.app')
@section('title', 'Profil 360 - Kompetensi & IDP')
@section('header_title', 'Profil 360° Kompetensi & IDP')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">🔮 Profil 360° Kompetensi & IDP</h1>
</div>


@if($employee)
    {{-- Employee Summary --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-title" style="font-size:18px;">👤 Profil Pegawai</div>
        <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:20px;font-size:13px;padding-top:8px;">
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">Nama</div>
                <strong style="font-size:14px;">{{ $employee->name }}</strong>
            </div>
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">NIP</div>
                <strong>{{ $employee->id }}</strong>
            </div>
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">Jabatan</div>
                <strong>{{ $employee->role }}</strong>
            </div>
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">Unit</div>
                <strong>{{ $employee->unit }}</strong>
            </div>
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">Kategori</div>
                <strong>{{ $employee->category }}</strong>
            </div>
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">Pangkat</div>
                <strong>{{ $employee->pangkat ?? '-' }}</strong>
            </div>
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">Pendidikan</div>
                <strong>{{ $employee->strata ?? '-' }} {{ $employee->jurusan ? '- ' . $employee->jurusan : '' }}</strong>
            </div>
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">Sertifikasi Bahasa</div>
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
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">Assessment</div>
                @if($employee->assessment)
                <span class="badge badge-success">✓ COMPASS</span>
                @else
                <span class="badge badge-neutral">Non-Assessment</span>
                @endif
            </div>
            <div>
                <div style="color:var(--text-secondary);font-size:11px;margin-bottom:4px;">IDP Gap Coverage</div>
                @php
                    $cov = $employee->idp_coverage ?? ($coverageDetail['coverage_percent'] ?? 0);
                    $badgeClass = $cov >= 75 ? 'badge-success' : ($cov >= 50 ? 'badge-warning' : 'badge-danger');
                @endphp
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="badge {{ $badgeClass }}" style="font-weight:700;">{{ $cov }}%</span>
                    @if(isset($coverageDetail) && $coverageDetail['total_target'] > 0)
                    <span style="font-size:11px;color:var(--text-secondary);">{{ $coverageDetail['matched_count'] }}/{{ $coverageDetail['total_target'] }} gap tertutupi</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Competency Gaps Side-by-Side --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        {{-- Teknis --}}
        <div class="card">
            <div class="card-title" style="font-size:18px;">📐 Kompetensi Teknis</div>
            @php
                $teknisNeeds = $needs->where('type', 'Teknis');
            @endphp
            @if($teknisNeeds->count())
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($teknisNeeds as $need)
                @php
                    $color = match($need->level) {
                        'Tidak optimal' => 'var(--danger)',
                        'Kurang optimal' => 'var(--warning)',
                        'Cukup optimal' => '#eab308',
                        default => 'var(--success)'
                    };
                    $pct = $need->score;
                @endphp
                <div class="gap-item" style="background:#F8FAFC;border-radius:6px;padding:10px 12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:13px;font-weight:600;">{{ $need->competency_name }}</span>
                        <span style="font-size:13px;font-weight:700;color:{{ $color }}">{{ $need->score }}</span>
                    </div>
                    <div style="background:#F1F5F9;border-radius:4px;height:6px;margin-top:6px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $color }};"></div>
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;">Standard: {{ $need->standard }} | Gap: {{ $need->gap ?? '-' }} | Level: {{ $need->level ?? '-' }}</div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-muted text-sm">Tidak ada data kompetensi teknis.</p>
            @endif
        </div>

        {{-- Mansoskul --}}
        <div class="card">
            <div class="card-title" style="font-size:18px;">🤝 Kompetensi Mansoskul</div>
            @php
                $mansoskulNeeds = $needs->where('type', 'Mansoskul');
            @endphp
            @if($mansoskulNeeds->count())
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($mansoskulNeeds as $need)
                @php
                    $color = match($need->level) {
                        'Tidak optimal' => 'var(--danger)',
                        'Kurang optimal' => 'var(--warning)',
                        'Cukup optimal' => '#eab308',
                        default => 'var(--success)'
                    };
                    $pct = ($need->score / 5) * 100;
                @endphp
                <div class="gap-item" style="background:#F8FAFC;border-radius:6px;padding:10px 12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:13px;font-weight:600;">{{ $need->competency_name }}</span>
                        <span style="font-size:13px;font-weight:700;color:{{ $color }}">{{ $need->score }}</span>
                    </div>
                    <div style="background:#F1F5F9;border-radius:4px;height:6px;margin-top:6px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $color }};"></div>
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;">Standard: {{ $need->standard }} | Gap: {{ $need->gap ?? '-' }} | Level: {{ $need->level ?? '-' }}</div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-muted text-sm">Tidak ada data kompetensi mansoskul.</p>
            @endif
        </div>
    </div>

{{-- IDP Items --}}
<div class="card" style="margin-top:20px;">
    <div class="card-title" style="font-size:18px;">📋 Daftar Item IDP</div>
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
    <div id="idp-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid #E2E8F0;flex-wrap:wrap;gap:8px;">
        <button id="btn-idp-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
        <span id="idp-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
        <button id="btn-idp-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
    </div>
    @endif
</div>

{{-- Riwayat Bangkom Unit --}}
<div class="card" style="margin-top:20px;">
    <div class="card-title" style="font-size:18px;">🏆 Riwayat Bangkom Unit</div>
    <table>
        <thead>
            <tr>
                <th>Nama Kegiatan</th>
                <th>Jenis Kompetensi</th>
                <th>Unit Pengusul</th>
                <th>Jenis Pembelajaran</th>
                <th>JP</th>
            </tr>
        </thead>
        <tbody id="bangkom-table-body">
            @forelse($riwayatBangkom as $rb)
            <tr>
                <td><strong>{{ $rb->nama_kegiatan }}</strong><br><small style="color:var(--text-secondary);">Selesai: {{ $rb->tanggal_selesai ? \Carbon\Carbon::parse($rb->tanggal_selesai)->format('d M Y') : '-' }}</small></td>
                <td>{{ $rb->kompetensi_dasar }}</td>
                <td>{{ $rb->unit_pengusul }}</td>
                <td>{{ $rb->jalur_pembelajaran ?? '-' }}</td>
                <td>{{ $rb->jp }} JP</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada riwayat Bangkom Unit.</td></tr>
            @endforelse
        </tbody>
    </table>
    
    {{-- Pagination controls for Riwayat Bangkom --}}
    @if(count($riwayatBangkom) > 0)
    <div id="bangkom-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid #E2E8F0;flex-wrap:wrap;gap:8px;">
        <button id="btn-bangkom-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
        <span id="bangkom-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
        <button id="btn-bangkom-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
    </div>
    @endif
</div>

{{-- Riwayat Pelatihan / Diklat --}}
<div class="card" style="margin-top:20px;">
    <div class="card-title" style="font-size:18px;">📚 Riwayat Pelatihan / Diklat (SMILE)</div>
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
    <div id="diklat-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid #E2E8F0;flex-wrap:wrap;gap:8px;">
        <button id="btn-diklat-prev" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">← Prev</button>
        <span id="diklat-page-info" style="font-size:11px;color:var(--text-secondary);font-weight:600;">Halaman 1</span>
        <button id="btn-diklat-next" class="btn btn-neutral btn-sm" style="font-size:11px;padding:4px 8px;cursor:pointer;">Next →</button>
    </div>
    @endif
</div>

{{-- Riwayat Sertifikasi Profesional --}}
<div class="card" style="margin-top:20px;">
    <div class="card-title" style="font-size:18px;">🏅 Riwayat Sertifikasi Profesional (SMILE)</div>
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
    <div id="sert-pagination-controls" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid #E2E8F0;flex-wrap:wrap;gap:8px;">
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
    paginateTable('bangkom-table-body', 'bangkom-pagination-controls', 'btn-bangkom-prev', 'btn-bangkom-next', 'bangkom-page-info');
    paginateTable('diklat-table-body', 'diklat-pagination-controls', 'btn-diklat-prev', 'btn-diklat-next', 'diklat-page-info');
    paginateTable('sert-table-body', 'sert-pagination-controls', 'btn-sert-prev', 'btn-sert-next', 'sert-page-info');
});
</script>
@endpush
