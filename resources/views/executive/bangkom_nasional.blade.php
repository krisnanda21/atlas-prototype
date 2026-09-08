@extends('layouts.app')
@section('title', 'Bangkom Nasional')
@section('header_title', 'Bangkom Nasional (Seluruh Unit Kerja)')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">📅 Bangkom Nasional</h1>
</div>

<div class="card">
    <div class="card-title" style="font-size:18px">Daftar Kegiatan Bangkom Nasional</div>

    {{-- Filter & Sort Bar --}}
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
        <div style="font-weight:600; color:var(--text-secondary);">🔍 Cari:</div>
        <input type="text" id="search-title" class="form-control" style="width:200px; height:32px; padding:0 8px; font-size:12px;" placeholder="Cari nama kegiatan..." onkeyup="filterAndSortRencana()">
        <div style="font-weight:600; color:var(--text-secondary); margin-left:8px;">Filter:</div>
        <select id="filter-metode" class="form-control" style="width:140px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortRencana()">
            <option value="">Semua Metode</option>
            <option value="Luring">Luring</option>
            <option value="Daring">Daring</option>
        </select>
        <select id="filter-status" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortRencana()">
            <option value="">Semua Status</option>
            <option value="Draft">Draft</option>
            <option value="Menunggu Penetapan">Menunggu Penetapan</option>
            <option value="Ditetapkan">Ditetapkan</option>
            <option value="Perlu Revisi">Perlu Revisi</option>
            <option value="Pembatalan Diajukan">Pembatalan Diajukan</option>
            <option value="Realisasi">Realisasi</option>
            <option value="Persetujuan Realisasi">Persetujuan Realisasi</option>
            <option value="Realisasi Disetujui">Realisasi Disetujui</option>
        </select>
        <select id="filter-competency" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortRencana()">
            <option value="">Semua Kompetensi</option>
            @foreach($allCompetencies as $comp)
                <option value="{{ $comp }}">{{ $comp }}</option>
            @endforeach
        </select>
        <div style="font-weight:600; color:var(--text-secondary); margin-left:12px;">⇅ Urutkan:</div>
        <select id="sort-by" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortRencana()">
            <option value="">Default</option>
            <option value="title-asc">Nama Kegiatan (A-Z)</option>
            <option value="title-desc">Nama Kegiatan (Z-A)</option>
            <option value="jp-desc">JP: Tinggi-Rendah</option>
            <option value="jp-asc">JP: Rendah-Tinggi</option>
        </select>
    </div>

    <table id="table-rencana">
        <thead>
            <tr><th>Nama Kegiatan</th><th>Unit Pengusul</th><th>Kompetensi</th><th>Jalur Pembelajaran</th><th>Tanggal</th><th>JP</th><th>Status</th><th>Aksi</th></tr>
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
                $displayStatus = $statusMapping[$statusLower] ?? ucfirst($p->status);

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
            <tr class="rencana-row" data-title="{{ strtolower($p->nama_kegiatan) }}" data-metode="{{ $p->metode }}" data-status="{{ $displayStatus }}" data-jp="{{ $p->jp }}" data-competency="{{ $p->kompetensi_dasar }}">
                <td><strong>{{ $p->nama_kegiatan }}</strong></td>
                <td>{{ $p->unit_pengusul }}</td>
                <td>{{ $p->kompetensi_dasar }}</td>
                <td>{{ $p->jalur_pembelajaran }}</td>
                <td>{{ $p->tanggal_mulai }}</td>
                <td>{{ $p->jp }} JP</td>
                <td>
                    <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                </td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="btn btn-sm btn-neutral" onclick="showDetail({{ json_encode($p) }})">Detail Rencana</button>
                    @if(in_array($statusLower, ['realisasi', 'persetujuan realisasi', 'realisasi disetujui', 'selesai']))
                    <button class="btn btn-sm btn-info" onclick="showDetailRealisasi({{ json_encode($p) }})">Detail Realisasi</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada rencana kegiatan.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div id="pagination-controls" style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:#F8FAFC; border-top:1px solid #E2E8F0; border-radius:0 0 8px 8px;">
        <div id="pagination-info" style="font-size:12px; color:var(--text-secondary);">Showing 0 to 0 of 0 entries</div>
        <div style="display:flex; gap:4px;" id="pagination-buttons"></div>
    </div>
</div>

{{-- Modal Detail Rencana --}}
<div id="modal-detail" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:540px;max-height:80vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="font-size:16px;font-weight:600;">📋 Detail Rencana Kegiatan</h2>
            <button onclick="document.getElementById('modal-detail').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <div id="detail-content"></div>
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

@endsection

@push('scripts')
<script>
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
        <table style="width:100%;font-size:13px;border-collapse:collapse;color:#fff;">
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

function showDetailRealisasi(plan) {
    const modal = document.getElementById('modal-detail-realisasi');
    const content = document.getElementById('detail-realisasi-content');
    
    const statusLower = (plan.status || '').toLowerCase();
    let statusBadge = `<span class="badge badge-neutral">${plan.status}</span>`;
    if (statusLower === 'realisasi') statusBadge = `<span class="badge badge-info">Realisasi</span>`;
    else if (statusLower === 'persetujuan realisasi') statusBadge = `<span class="badge badge-warning">Persetujuan Realisasi</span>`;
    else if (statusLower === 'realisasi disetujui') statusBadge = `<span class="badge badge-success">✓ Realisasi Disetujui</span>`;

    const isLevel2Plan = parseInt(plan.jenis_evaluasi) === 2;

    let participantsRows = '';
    if (plan.participants && plan.participants.length > 0) {
        plan.participants.forEach((p, idx) => {
            participantsRows += `
                <tr style="border-bottom:1px solid #E2E8F0; font-size:12px;">
                    <td style="padding:8px 10px; color:#fff;">
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
                <div style="font-weight:700; color:#fff; font-size:14px; margin-bottom:10px;">${plan.id} - ${plan.nama_kegiatan}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Unit Pengusul</div>
                <div style="color:#fff; margin-bottom:10px;">${plan.unit_pengusul || '-'}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Kompetensi Dasar</div>
                <div style="color:#fff; margin-bottom:10px;">${plan.kompetensi_dasar || '-'}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Jalur & Metode</div>
                <div style="color:#fff;">${plan.jalur_pembelajaran || '-'} (${plan.metode || '-'})</div>
            </div>
            <div>
                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Status Realisasi</div>
                <div style="margin-bottom:10px;">${statusBadge}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Waktu Pelaksanaan & JP</div>
                <div style="color:#fff; margin-bottom:10px;">${plan.tanggal_mulai} s/d ${plan.tanggal_selesai} • <strong>${plan.jp} JP</strong></div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Fasilitator</div>
                <div style="color:#fff; margin-bottom:10px;">${plan.fasilitator || '-'}</div>

                <div style="color:var(--text-secondary); font-size:11px; margin-bottom:2px;">Jenis Evaluasi</div>
                <div style="color:#fff;">${isLevel2Plan ? 'Level 2 (Reaksi & Pre/Post Test)' : 'Level 1 (Reaksi Penyelenggaraan)'}</div>
            </div>
        </div>

        <div style="margin-bottom:20px; background:#F8FAFC; border:1px solid var(--card-border); border-radius:8px; padding:14px;">
            <div style="font-weight:600; color:var(--text-primary); font-size:13px; margin-bottom:10px;">📁 Dokumen Bukti Realisasi</div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; font-size:12px;">
                <div style="background:#F8FAFC; padding:10px; border-radius:6px; border:1px solid #F1F5F9;">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">1. Daftar Hadir</div>
                    <div style="color:var(--text-primary); font-weight:600; word-break:break-all;">📄 ${plan.dok_daftar_hadir || '<span class="text-muted">Belum ada</span>'}</div>
                </div>
                <div style="background:#F8FAFC; padding:10px; border-radius:6px; border:1px solid #F1F5F9;">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">2. Notulen Kegiatan</div>
                    <div style="color:var(--text-primary); font-weight:600; word-break:break-all;">📄 ${plan.dok_notulen || '<span class="text-muted">Belum ada</span>'}</div>
                </div>
                <div style="background:#F8FAFC; padding:10px; border-radius:6px; border:1px solid #F1F5F9;">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">3. Foto Dokumentasi</div>
                    <div style="color:var(--text-primary); font-weight:600; word-break:break-all;">🖼️ ${plan.dok_dokumentasi || '<span class="text-muted">Belum ada</span>'}</div>
                </div>
            </div>
        </div>

        <div style="background:#F8FAFC; border:1px solid var(--card-border); border-radius:8px; padding:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div style="font-weight:600; color:#fff; font-size:13px;">👥 Daftar Peserta & Skor (${plan.participants ? plan.participants.length : 0} Pegawai)</div>
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
    modal.style.display = 'flex';
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

function filterAndSortRencana() {
    const searchQuery = document.getElementById('search-title').value.toLowerCase();
    const filterMetode = document.getElementById('filter-metode').value;
    const filterStatus = document.getElementById('filter-status').value;
    const filterCompetency = document.getElementById('filter-competency').value;
    const sortBy = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('.rencana-row'));
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
    
    const tbody = document.querySelector('#table-rencana tbody');
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
    filterAndSortRencana();
});
</script>
@endpush
