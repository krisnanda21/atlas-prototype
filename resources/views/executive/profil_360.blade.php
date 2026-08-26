@extends('layouts.app')
@section('title', 'Profil 360 - Executive')
@section('header_title', 'Profil 360° Kompetensi Pegawai Nasional')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px;">🔮 Profil 360° Nasional</h1>
</div>

@php
    $isSearching = request()->has('emp_id') || request()->has('q');
@endphp

@if(!$isSearching)
<div class="card" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:80px 20px; gap:20px; min-height:400px;">
    <div style="font-size:48px;margin-bottom:12px;">🔍</div>
    <h2 style="font-size:24px; font-weight:700; margin:0;">Cari Profil Pegawai</h2>
    <p class="text-muted" style="margin-bottom:20px; text-align:center;">Masukkan Nama atau NIP pegawai untuk melihat profil 360°, gap kompetensi, dan riwayat pelatihan.</p>
    
    <div style="position:relative; width:100%; max-width:600px;">
        <div style="display:flex; gap:10px; width:100%;">
            <input type="text" id="main-search-input" class="form-control" placeholder="Ketik nama atau NIP..." autocomplete="off" style="padding:12px 16px; font-size:16px; width:100%;" oninput="handleMainSearchInput()" onfocus="handleMainSearchInput()">
        </div>
        <div id="main-search-results" style="display:none; position:absolute; top:100%; left:0; width:100%; background:var(--bg-card); border:1px solid rgba(255,255,255,0.1); border-radius:6px; margin-top:4px; max-height:300px; overflow-y:auto; z-index:100; box-shadow:0 10px 15px -3px rgba(0,0,0,0.5); text-align:left;">
        </div>
    </div>
</div>

<script>
    let searchTimeoutMain;

    function handleMainSearchInput() {
        const q = document.getElementById('main-search-input').value.trim();
        const container = document.getElementById('main-search-results');
        
        if (q.length < 2) {
            container.style.display = 'none';
            return;
        }

        container.innerHTML = '<div style="padding:12px 16px; color:var(--text-secondary); text-align:center;">Mencari...</div>';
        container.style.display = 'block';

        clearTimeout(searchTimeoutMain);
        searchTimeoutMain = setTimeout(() => {
            fetch(`/api/employees/search?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(matches => {
                    if (matches.length === 0) {
                        container.innerHTML = '<div style="padding:12px 16px; color:var(--text-secondary); text-align:center;">Pegawai tidak ditemukan</div>';
                        return;
                    }
                    
                    let html = '';
                    matches.forEach(e => {
                        html += `
                            <a href="?emp_id=${e.id}&q=${encodeURIComponent(e.name)}" style="display:block; padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.05); text-decoration:none; color:var(--text-primary); transition:background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.05)'" onmouseleave="this.style.background='transparent'">
                                <div style="font-weight:600;">${e.name}</div>
                                <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">NIP: ${e.id} &bull; ${e.role || '-'}</div>
                            </a>
                        `;
                    });
                    container.innerHTML = html;
                })
                .catch(err => {
                    console.error('Search error:', err);
                    container.innerHTML = '<div style="padding:12px 16px; color:var(--text-secondary); text-align:center;">Terjadi kesalahan saat mencari</div>';
                });
        }, 300);
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#main-search-input') && !e.target.closest('#main-search-results')) {
            const el = document.getElementById('main-search-results');
            if(el) el.style.display = 'none';
        }
    });
</script>

@else

<div class="card" style="margin-bottom: 20px; padding: 15px 20px;">
    <div style="display:flex; gap:10px; width:100%; align-items:center;">
        <span style="font-size:20px;">🔍</span>
        <div style="position:relative; flex:1;">
            <input type="text" id="top-search-input" class="form-control" value="{{ request('q') }}" placeholder="Cari profil pegawai..." autocomplete="off" style="width:100%; padding:10px 14px; font-size:15px;" oninput="handleTopSearchInput()" onfocus="handleTopSearchInput()">
            <div id="top-search-results" style="display:none; position:absolute; top:100%; left:0; width:100%; background:var(--bg-card); border:1px solid rgba(255,255,255,0.1); border-radius:6px; margin-top:4px; max-height:300px; overflow-y:auto; z-index:100; box-shadow:0 10px 15px -3px rgba(0,0,0,0.5); text-align:left;">
            </div>
        </div>
        <a href="?" class="btn btn-neutral" style="padding:10px 20px; font-weight:600; text-decoration:none;">Reset</a>
    </div>
</div>

<script>
    let searchTimeoutTop;

    function handleTopSearchInput() {
        const q = document.getElementById('top-search-input').value.trim();
        const container = document.getElementById('top-search-results');
        
        if (q.length < 2) {
            container.style.display = 'none';
            return;
        }

        container.innerHTML = '<div style="padding:12px 16px; color:var(--text-secondary); text-align:center;">Mencari...</div>';
        container.style.display = 'block';

        clearTimeout(searchTimeoutTop);
        searchTimeoutTop = setTimeout(() => {
            fetch(`/api/employees/search?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(matches => {
                    if (matches.length === 0) {
                        container.innerHTML = '<div style="padding:12px 16px; color:var(--text-secondary); text-align:center;">Pegawai tidak ditemukan</div>';
                        return;
                    }
                    
                    let html = '';
                    matches.forEach(e => {
                        html += `
                            <a href="?emp_id=${e.id}&q=${encodeURIComponent(e.name)}" style="display:block; padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.05); text-decoration:none; color:var(--text-primary); transition:background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.05)'" onmouseleave="this.style.background='transparent'">
                                <div style="font-weight:600;">${e.name}</div>
                                <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">NIP: ${e.id} &bull; ${e.role || '-'}</div>
                            </a>
                        `;
                    });
                    container.innerHTML = html;
                })
                .catch(err => {
                    console.error('Search error:', err);
                    container.innerHTML = '<div style="padding:12px 16px; color:var(--text-secondary); text-align:center;">Terjadi kesalahan saat mencari</div>';
                });
        }, 300);
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#top-search-input') && !e.target.closest('#top-search-results')) {
            const el = document.getElementById('top-search-results');
            if(el) el.style.display = 'none';
        }
    });
</script>

<div>
        @if($employee)
        {{-- Profile header --}}
        @php
            $nilaiRata = DB::table('compass_nilai_rata_rata')->where('employee_id', $employee->id)->first();
        @endphp
        <div class="card mb-4" style="display:flex;align-items:center;gap:20px;padding:20px;">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(45,140,240,0.2);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($employee->name, 0, 1)) }}</div>
            <div style="flex:1;">
                <div style="font-size:18px;font-weight:700;color:var(--text-primary);">{{ $employee->name }}</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-top:2px;">NIP: {{ $employee->id }}</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-top:2px;">{{ $employee->role }} · {{ $employee->unit }}</div>
                <div style="display:flex;gap:8px;margin-top:8px;">
                    <span class="badge badge-neutral" style="font-size:10px;">{{ $employee->category }}</span>
                    @if($employee->assessment) <span class="badge badge-success" style="font-size:10px;">✓ COMPASS</span> @else <span class="badge badge-neutral" style="font-size:10px;">Non-Assessment</span> @endif
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;font-size:12px;border-left:1px solid rgba(255,255,255,0.1);padding-left:20px;min-width:240px;">
                <div><span style="color:var(--text-secondary);">Pangkat:</span> <strong>{{ $employee->pangkat ?? '-' }}</strong></div>
                <div><span style="color:var(--text-secondary);">Pendidikan:</span> <strong>{{ $employee->strata ?? '-' }} {{ $employee->jurusan ? '- ' . $employee->jurusan : '' }}</strong></div>
                <div><span style="color:var(--text-secondary);">Bahasa:</span> <strong>
                    @if($employee->toefl || $employee->ielts)
                        {{ $employee->toefl ? 'TOEFL: ' . $employee->toefl : '' }}
                        {{ $employee->toefl && $employee->ielts ? ' | ' : '' }}
                        {{ $employee->ielts ? 'IELTS: ' . $employee->ielts : '' }}
                    @else
                        -
                    @endif
                </strong></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;font-size:12px;border-left:1px solid rgba(255,255,255,0.1);padding-left:20px;min-width:200px;">
                <div><span style="color:var(--text-secondary);">Rata-Rata Teknis:</span> <strong>{{ $nilaiRata ? $nilaiRata->nilai_teknis : '-' }}</strong></div>
                <div><span style="color:var(--text-secondary);">Rata-Rata Mansoskul:</span> <strong>{{ $nilaiRata ? $nilaiRata->nilai_mansoskul : '-' }}</strong></div>
                <div><span style="color:var(--text-secondary);">Nilai Potensi:</span> <strong>{{ $nilaiRata ? $nilaiRata->nilai_potensi : '-' }}</strong></div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-title">📊 Gap Kompetensi (COMPASS)</div>
            @if($needs->count())
            @php
                $needsTeknis = $needs->filter(fn($n) => !(isset($n->type) && strtolower($n->type) === 'mansoskul'))->sortBy('score');
                $needsMansoskul = $needs->filter(fn($n) => isset($n->type) && strtolower($n->type) === 'mansoskul')->sortBy('score');
            @endphp
            
            <details style="margin-bottom:8px; border:1px solid rgba(255,255,255,0.1); border-radius:6px; overflow:hidden;" open>
                <summary style="background:rgba(255,255,255,0.05); padding:10px 14px; cursor:pointer; font-weight:600; font-size:13px; list-style:none; display:flex; justify-content:space-between; align-items:center;">
                    <span>Kompetensi Teknis ({{ $needsTeknis->count() }})</span>
                    <span style="font-size:10px;">▼</span>
                </summary>
                <div style="padding:14px; display:flex; flex-direction:column; gap:8px;">
                    @forelse($needsTeknis as $need)
                        @php
                            $scoreColor = $need->score < 60 ? 'var(--danger)' : ($need->score < 78 ? 'var(--warning)' : 'var(--success)');
                        @endphp
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:200px;font-size:12px;color:var(--text-secondary);flex-shrink:0;">{{ $need->competency_name }}</div>
                            <div style="flex:1;background:rgba(255,255,255,0.08);border-radius:4px;height:8px;overflow:hidden;">
                                <div style="height:100%;width:{{ $need->score }}%;background:{{ $scoreColor }};"></div>
                            </div>
                            <div style="font-size:12px;font-weight:700;width:30px;text-align:right;color:{{ $scoreColor }}">{{ $need->score }}</div>
                        </div>
                    @empty
                        <div class="text-muted text-sm">Tidak ada gap kompetensi Teknis.</div>
                    @endforelse
                </div>
            </details>

            <details style="margin-bottom:8px; border:1px solid rgba(255,255,255,0.1); border-radius:6px; overflow:hidden;">
                <summary style="background:rgba(255,255,255,0.05); padding:10px 14px; cursor:pointer; font-weight:600; font-size:13px; list-style:none; display:flex; justify-content:space-between; align-items:center;">
                    <span>Kompetensi Manajerial & Sosial Kultural ({{ $needsMansoskul->count() }})</span>
                    <span style="font-size:10px;">▼</span>
                </summary>
                <div style="padding:14px; display:flex; flex-direction:column; gap:8px;">
                    @forelse($needsMansoskul as $need)
                        @php
                            $scoreWidth = ($need->score / 5) * 100;
                            $scoreColor = $need->score < 3 ? 'var(--danger)' : ($need->score < 4 ? 'var(--warning)' : 'var(--success)');
                        @endphp
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:200px;font-size:12px;color:var(--text-secondary);flex-shrink:0;">{{ $need->competency_name }}</div>
                            <div style="flex:1;background:rgba(255,255,255,0.08);border-radius:4px;height:8px;overflow:hidden;">
                                <div style="height:100%;width:{{ $scoreWidth }}%;background:{{ $scoreColor }};"></div>
                            </div>
                            <div style="font-size:12px;font-weight:700;width:30px;text-align:right;color:{{ $scoreColor }}">{{ $need->score }}</div>
                        </div>
                    @empty
                        <div class="text-muted text-sm">Tidak ada gap kompetensi Mansoskul.</div>
                    @endforelse
                </div>
            </details>
            @else
            <p class="text-muted text-sm">Tidak ada data gap kompetensi (Non-Assessment / belum dinilai).</p>
            @endif
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>📋 IDP Items</span>
                <div id="pagination-idp" class="pagination-controls" style="font-size:12px; display:flex; gap:8px; align-items:center;"></div>
            </div>
            <table id="table-idp" data-page-size="5">
                <thead><tr><th>Kebutuhan</th><th>Sumber</th><th>Prioritas</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($idpItems as $idp)
                    <tr class="paginate-row">
                        <td>{{ $idp->need }}</td>
                        <td>{{ $idp->source }}</td>
                        <td>
                            @if($idp->priority === 'Tinggi') <span class="badge badge-danger">Tinggi</span>
                            @elseif($idp->priority === 'Sedang') <span class="badge badge-warning">Sedang</span>
                            @else <span class="badge badge-neutral">Rendah</span>
                            @endif
                        </td>
                        <td><span class="badge {{ match($idp->status) { 'Disepakati'=>'badge-success','Diajukan'=>'badge-info','Perlu Perbaikan'=>'badge-danger',default=>'badge-neutral' } }}">{{ $idp->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-sm" style="text-align:center;padding:16px;">Belum ada IDP.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Riwayat Bangkom Unit --}}
        <div class="card" style="margin-top:20px;">
            <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>🎓 Riwayat Bangkom Unit</span>
                <div id="pagination-bangkom" class="pagination-controls" style="font-size:12px; display:flex; gap:8px; align-items:center;"></div>
            </div>
            <table id="table-bangkom" data-page-size="5">
                <thead>
                    <tr>
                        <th>Nama Kegiatan</th>
                        <th>Jalur Pembelajaran</th>
                        <th>JP</th>
                        <th>Unit Penyelenggara</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bangkoms as $bangkom)
                    <tr class="paginate-row">
                        <td><strong>{{ $bangkom->nama_kegiatan }}</strong></td>
                        <td>{{ $bangkom->jenis_pelatihan }} / {{ $bangkom->metode }}</td>
                        <td>{{ $bangkom->jp }} JP</td>
                        <td>{{ $bangkom->unit_pengusul }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-sm" style="text-align:center;padding:16px;">Belum ada riwayat bangkom unit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Riwayat Pelatihan / Diklat --}}
        <div class="card" style="margin-top:20px;">
            <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>📚 Riwayat Pelatihan / Diklat (SMILE)</span>
                <div id="pagination-diklat" class="pagination-controls" style="font-size:12px; display:flex; gap:8px; align-items:center;"></div>
            </div>
            <table id="table-diklat" data-page-size="5">
                <thead>
                    <tr>
                        <th>Nama Pelatihan / Diklat</th>
                        <th>Durasi (JP)</th>
                        <th>No. Sertifikat</th>
                        <th>Status Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($diklats as $diklat)
                    <tr class="paginate-row">
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
                    <tr><td colspan="4" class="text-muted text-sm" style="text-align:center;padding:16px;">Belum ada riwayat pelatihan/diklat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Riwayat Sertifikasi Profesional --}}
        <div class="card" style="margin-top:20px;">
            <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>🏅 Riwayat Sertifikasi Profesional (SMILE)</span>
                <div id="pagination-sertifikasi" class="pagination-controls" style="font-size:12px; display:flex; gap:8px; align-items:center;"></div>
            </div>
            <table id="table-sertifikasi" data-page-size="5">
                <thead>
                    <tr>
                        <th>Nama Sertifikasi</th>
                        <th>Nomor Sertifikasi</th>
                        <th>Status Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sertifikasis as $sert)
                    <tr class="paginate-row">
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
                    <tr><td colspan="3" class="text-muted text-sm" style="text-align:center;padding:16px;">Belum ada riwayat sertifikasi profesional.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @else
        <div class="card" style="text-align:center;padding:60px;">
            <div style="font-size:56px;margin-bottom:16px;">🔍</div>
            <p class="text-muted">Cari pegawai di sebelah kiri untuk melihat profil kompetensinya.</p>
        </div>
        @endif
    </div>
</div>
@endif
@push('scripts')
<script>
function initPagination(tableId, paginationId) {
    const table = document.getElementById(tableId);
    const paginationContainer = document.getElementById(paginationId);
    if (!table || !paginationContainer) return;

    const rows = Array.from(table.querySelectorAll('tbody tr.paginate-row'));
    if (rows.length === 0) return; // Don't paginate if empty

    const pageSize = parseInt(table.getAttribute('data-page-size')) || 5;
    const totalPages = Math.ceil(rows.length / pageSize);
    let currentPage = 1;

    function renderTable() {
        rows.forEach((row, index) => {
            if (index >= (currentPage - 1) * pageSize && index < currentPage * pageSize) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        renderControls();
    }

    function renderControls() {
        paginationContainer.innerHTML = '';
        if (totalPages <= 1) return;

        const prevBtn = document.createElement('button');
        prevBtn.textContent = '«';
        prevBtn.style.cssText = `background:var(--bg-card); color:var(--text-primary); border:1px solid rgba(255,255,255,0.1); border-radius:4px; padding:2px 8px; cursor:${currentPage === 1 ? 'not-allowed' : 'pointer'}; opacity:${currentPage === 1 ? '0.5' : '1'}`;
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
        paginationContainer.appendChild(prevBtn);

        const info = document.createElement('span');
        info.textContent = `${currentPage} / ${totalPages}`;
        info.style.color = 'var(--text-secondary)';
        paginationContainer.appendChild(info);

        const nextBtn = document.createElement('button');
        nextBtn.textContent = '»';
        nextBtn.style.cssText = `background:var(--bg-card); color:var(--text-primary); border:1px solid rgba(255,255,255,0.1); border-radius:4px; padding:2px 8px; cursor:${currentPage === totalPages ? 'not-allowed' : 'pointer'}; opacity:${currentPage === totalPages ? '0.5' : '1'}`;
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(); } };
        paginationContainer.appendChild(nextBtn);
    }

    renderTable();
}

document.addEventListener('DOMContentLoaded', () => {
    initPagination('table-idp', 'pagination-idp');
    initPagination('table-bangkom', 'pagination-bangkom');
    initPagination('table-diklat', 'pagination-diklat');
    initPagination('table-sertifikasi', 'pagination-sertifikasi');
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

</script>
@endpush
@endsection
