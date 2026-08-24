@extends('layouts.app')
@section('title', 'Rencana Bangkom Unit')
@section('header_title', 'Rencana & Penetapan Bangkom Unit')

@section('content')
<div class="page-header">
    <h1>📅 Rencana & Penetapan Bangkom Unit</h1>
    <button class="btn btn-primary" onclick="openAddModal()">+ Add Kegiatan</button>
</div>

<div class="card">
    <div class="card-title">Daftar Rencana Kegiatan Bangkom</div>

    {{-- Filter & Sort Bar --}}
    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
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
            <option value="Analisis Data">Analisis Data</option>
            <option value="Komunikasi">Komunikasi</option>
            <option value="Komunikasi Hasil">Komunikasi Hasil</option>
            <option value="Fraud Risk Management">Fraud Risk Management</option>
            <option value="Keamanan Data Dasar">Keamanan Data Dasar</option>
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
            <tr><th>Nama Kegiatan</th><th>Kompetensi</th><th>Jalur Pembelajaran</th><th>Tanggal</th><th>JP</th><th>Status</th><th>Aksi</th></tr>
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
                <td>{{ $p->kompetensi_dasar }}</td>
                <td>{{ $p->jalur_pembelajaran }}</td>
                <td>{{ $p->tanggal_mulai }}</td>
                <td>{{ $p->jp }} JP</td>
                <td>
                    <span class="badge {{ $sc }}">{{ $displayStatus }}</span>
                </td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    @if($statusLower === 'draft' || $statusLower === 'revisi')
                    <button class="btn btn-sm btn-primary" onclick="showEditModal({{ json_encode($p) }})">✏️ Edit</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="showConfirmSubmitPlan('{{ $p->id }}', '{{ addslashes($p->nama_kegiatan) }}')">Ajukan</button>
                    @endif
                    
                    @if($statusLower !== 'draft' && $statusLower !== 'realisasi' && $statusLower !== 'persetujuan realisasi' && $statusLower !== 'realisasi disetujui' && $statusLower !== 'selesai' && $statusLower !== 'pembatalan diajukan' && $statusLower !== 'pembatalan disetujui' && date('Y-m-d') <= $p->tanggal_mulai)
                    <button class="btn btn-sm btn-danger" onclick="showCancelModal('{{ $p->id }}','{{ addslashes($p->nama_kegiatan) }}')">Batalkan</button>
                    @endif
                    
                    <button class="btn btn-sm btn-neutral" onclick="showDetail({{ json_encode($p) }})">Detail</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada rencana kegiatan.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div id="pagination-controls" style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:rgba(255,255,255,0.02); border-top:1px solid rgba(255,255,255,0.05); border-radius:0 0 8px 8px;">
        <div id="pagination-info" style="font-size:12px; color:var(--text-secondary);">Showing 0 to 0 of 0 entries</div>
        <div style="display:flex; gap:4px;" id="pagination-buttons"></div>
    </div>
</div>

{{-- Modal Add/Edit Kegiatan --}}
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s ease-in-out;">
    <div class="atlas-modal-content" style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:95%;max-width:700px;max-height:85vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);transform:scale(0.95);transition:transform 0.2s ease-in-out;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.05);padding-bottom:12px;">
            <h2 id="modal-title" style="font-size:18px;font-weight:700;color:#fff;margin:0;">📅 + Tambah Kegiatan Bangkom Unit</h2>
            <button onclick="closeAddModal()" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('pengampu.storeRencanaBangkom') }}" id="form-kegiatan" onsubmit="return validateFormDates()">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            {{-- 1. Nama Kegiatan --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Nama Kegiatan *</label>
                <input type="text" name="nama_kegiatan" id="input-nama-kegiatan" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required placeholder="Nama kegiatan bangkom unit (maksimal 999 karakter)" maxlength="999">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                {{-- 2. Unit Pengusul --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Unit Pengusul *</label>
                    @if(auth()->user()->jabatan === 'Kepala Subbagian Tata Usaha Deputi')
                        <input list="unit-pengusul-list" name="unit_pengusul" id="input-unit-pengusul" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required value="" placeholder="Ketik untuk mencari direktorat...">
                        <datalist id="unit-pengusul-list">
                            @foreach($units as $unit)
                                @if(!str_contains(strtolower($unit), 'perwakilan'))
                                    <option value="{{ $unit }}"></option>
                                @endif
                            @endforeach
                        </datalist>
                    @else
                        <input type="text" name="unit_pengusul" id="input-unit-pengusul" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required value="{{ auth()->user()->unit_eselon2 }}" readonly>
                    @endif
                </div>
                {{-- 3. Kompetensi Dasar --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Kompetensi Dasar *</label>
                    <select name="kompetensi_dasar" id="input-kompetensi-dasar" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required>
                        <option value="">-- Pilih Kompetensi --</option>
                        <optgroup label="Kompetensi Teknis">
                            <option value="Analisis Data">Analisis Data</option>
                            <option value="Analisis Proses Bisnis">Analisis Proses Bisnis</option>
                            <option value="Fraud Risk Management">Fraud Risk Management</option>
                            <option value="Governance, Risk, Control, and Compliance">Governance, Risk, Control, and Compliance</option>
                            <option value="Keuangan Negara/Daerah dan Kekayaan yang Dipisahkan">Keuangan Negara/Daerah dan Kekayaan yang Dipisahkan</option>
                            <option value="Literasi Digital">Literasi Digital</option>
                            <option value="Manajemen dan Analisis Keuangan">Manajemen dan Analisis Keuangan</option>
                            <option value="Manajemen Penugasan Pengawasan Intern">Manajemen Penugasan Pengawasan Intern</option>
                            <option value="Manajemen Strategis Pemerintah">Manajemen Strategis Pemerintah</option>
                            <option value="Metode dan Teknik Pengawasan Intern">Metode dan Teknik Pengawasan Intern</option>
                            <option value="Pelaksanaan Pengawasan Intern">Pelaksanaan Pengawasan Intern</option>
                            <option value="Standar Audit dan Kode Etik">Standar Audit dan Kode Etik</option>
                            <option value="Analisis Kebijakan Publik">Analisis Kebijakan Publik</option>
                        </optgroup>
                        <optgroup label="Kompetensi Manajerial & Sosial Kultural">
                            <option value="Integritas">Integritas</option>
                            <option value="Kerja Sama">Kerja Sama</option>
                            <option value="Komunikasi">Komunikasi</option>
                            <option value="Orientasi pada Hasil">Orientasi pada Hasil</option>
                            <option value="Pelayanan Publik">Pelayanan Publik</option>
                            <option value="Pengembangan Diri & Orang Lain">Pengembangan Diri & Orang Lain</option>
                            <option value="Mengelola Perubahan">Mengelola Perubahan</option>
                            <option value="Pengambilan Keputusan">Pengambilan Keputusan</option>
                            <option value="Perekat Bangsa">Perekat Bangsa</option>
                            <option value="Karakteristik Lintas Manajerial">Karakteristik Lintas Manajerial</option>
                            <option value="Kepemimpinan">Kepemimpinan</option>
                            <option value="Komunikasi dalam Peran sebagai Trusted Advisor dan Value Driver">Komunikasi dalam Peran sebagai Trusted Advisor dan Value Driver</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            {{-- 4. Indikator Kinerja --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Indikator Kinerja *</label>
                <input type="text" name="indikator_kinerja" id="input-indikator-kinerja" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required placeholder="Indikator kinerja terkait (maksimal 999 karakter)" maxlength="999">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:16px;margin-bottom:14px;">
                {{-- 5. Jenis Latar Belakang --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jenis Latar Belakang *</label>
                    <select name="jenis_latar_belakang" id="input-jenis-latar-belakang" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required>
                        <option value="assessment-based">Gap Compass</option>
                        <option value="role-based">Role Based</option>
                        <option value="mandatory-based">Mandatory Learning</option>
                        <option value="unit-strategic-direction-based">Strategic Direction Unit</option>
                        <option value="self-initiative">Inisiatif Mandiri</option>
                    </select>
                </div>
                {{-- 6. Latar Belakang --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Latar Belakang *</label>
                    <input type="text" name="latar_belakang" id="input-latar-belakang" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required placeholder="Deskripsi latar belakang (maksimal 999 karakter)" maxlength="999">
                </div>
            </div>

            {{-- 7. Tujuan Kegiatan --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Tujuan Kegiatan *</label>
                <textarea name="tujuan_kegiatan" id="input-tujuan-kegiatan" class="form-control" rows="2" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;resize:vertical;" required placeholder="Tujuan dilaksanakannya kegiatan ini..." maxlength="999"></textarea>
            </div>

            {{-- 8. Indikator Keberhasilan (Repeater) --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                    <span>Indikator Keberhasilan (Minimal 1) *</span>
                    <button type="button" onclick="addRepeaterRow('repeater-keberhasilan', 'indikator_keberhasilan')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </label>
                <div id="repeater-keberhasilan" style="display:flex;flex-direction:column;gap:8px;">
                    {{-- Row inputs will be generated dynamically by JS --}}
                </div>
            </div>

            {{-- 9. Penugasan Terkait (Repeater) --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                    <span>Penugasan Terkait (Minimal 1) *</span>
                    <button type="button" onclick="addRepeaterRow('repeater-penugasan', 'penugasan_terkait')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </label>
                <div id="repeater-penugasan" style="display:flex;flex-direction:column;gap:8px;">
                    {{-- Row inputs will be generated dynamically by JS --}}
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px;">
                {{-- 10. Metode --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Metode *</label>
                    <select name="metode" id="input-metode" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required>
                        <option value="Full Tatap Muka">Full Tatap Muka</option>
                        <option value="Hybrid">Hybrid</option>
                        <option value="PJJ">PJJ</option>
                    </select>
                </div>
                {{-- 11. Jalur Pembelajaran --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jalur Pembelajaran *</label>
                    <select name="jalur_pembelajaran" id="input-jalur-pembelajaran" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required>
                        <option value="Pelatihan">Pelatihan</option>
                        <option value="Seminar/konferensi/sarasehan">Seminar/konferensi/sarasehan</option>
                        <option value="Kursus">Kursus</option>
                        <option value="Lokakarya (workshop)">Lokakarya (workshop)</option>
                        <option value="Belajar mandiri">Belajar mandiri</option>
                        <option value="Coaching">Coaching</option>
                        <option value="Mentoring">Mentoring</option>
                        <option value="Bimbingan teknis">Bimbingan teknis</option>
                        <option value="Sosialisasi">Sosialisasi</option>
                        <option value="Detasering (secondment)">Detasering (secondment)</option>
                        <option value="Job shadowing">Job shadowing</option>
                        <option value="Outbound">Outbound</option>
                        <option value="Benchmarking">Benchmarking</option>
                        <option value="Pertukaran PNS">Pertukaran PNS</option>
                        <option value="Community of practices">Community of practices</option>
                        <option value="Pelatihan di kantor sendiri">Pelatihan di kantor sendiri</option>
                        <option value="Library cafe">Library cafe</option>
                        <option value="Magang/praktik kerja">Magang/praktik kerja</option>
                    </select>
                </div>
                {{-- 12. JP --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Total JP *</label>
                    <input type="number" name="jp" id="input-jp" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" min="1" max="99" required placeholder="e.g. 10">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                {{-- 13. Tanggal Mulai --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" id="input-tanggal-mulai" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required>
                </div>
                {{-- 14. Tanggal Selesai --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" id="input-tanggal-selesai" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                {{-- 15. Jumlah Kelas --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jumlah Kelas *</label>
                    <input type="number" name="jumlah_kelas" id="input-jumlah-kelas" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" min="1" max="10" required placeholder="Maksimal 10 kelas">
                </div>
                {{-- 16. Nilai Anggaran --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Nilai Anggaran (Rupiah) *</label>
                    <input type="number" name="nilai_anggaran" id="input-nilai-anggaran" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" min="0" required placeholder="e.g. 15000000">
                </div>
            </div>

            {{-- 17. Kriteria Peserta (Repeater) --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                    <span>Kriteria Peserta (Minimal 1) *</span>
                    <button type="button" onclick="addRepeaterRow('repeater-kriteria', 'kriteria_peserta')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </label>
                <div id="repeater-kriteria" style="display:flex;flex-direction:column;gap:8px;">
                    {{-- Row inputs will be generated dynamically by JS --}}
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:12px;margin-bottom:20px;">
                {{-- 18. Fasilitator --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Fasilitator *</label>
                    <input type="text" name="fasilitator" id="input-fasilitator" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" required placeholder="Nama / lembaga fasilitator" maxlength="100">
                </div>
                {{-- 19. Evaluasi --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Evaluasi *</label>
                    <select name="evaluasi" id="input-evaluasi" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" onchange="toggleEvaluasiType()" required>
                        <option value="Ya">Ya</option>
                        <option value="Tidak">Tidak</option>
                    </select>
                </div>
                {{-- 20. Jenis Evaluasi --}}
                <div class="form-group" id="jenis-evaluasi-group" style="text-align:left;">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jenis Evaluasi *</label>
                    <select name="jenis_evaluasi" id="input-jenis-evaluasi" class="form-control" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;">
                        <option value="1">Level 1 (Penyelenggara, Materi, Fasilitator)</option>
                        <option value="2">Level 2 (Pre-test & Post-test)</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;border-top:1px solid rgba(255,255,255,0.05);padding-top:16px;">
                <button type="button" onclick="closeAddModal()" class="btn btn-neutral" style="padding:10px 20px;font-weight:600;cursor:pointer;border-radius:6px;border:none;background:rgba(255,255,255,0.1);color:#fff;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;font-weight:600;cursor:pointer;border-radius:6px;border:none;background:var(--primary);color:#fff;">💾 Simpan Rencana</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Cancel --}}
<div id="modal-cancel" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#1a2e45;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:480px;">
        <h2 style="font-size:16px;margin-bottom:16px;">❌ Batalkan Rencana Kegiatan</h2>
        <p class="text-sm text-muted" id="cancel-plan-name" style="margin-bottom:16px;"></p>
        <form method="POST" id="cancel-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Alasan Pembatalan *</label>
                <textarea name="cancel_reason" class="form-control" rows="3" required placeholder="Wajib diisi..."></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:12px;">
                <button type="submit" class="btn btn-danger">Ajukan Pembatalan</button>
                <button type="button" onclick="document.getElementById('modal-cancel').style.display='none'" class="btn btn-neutral">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail --}}
<div id="modal-detail" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#1a2e45;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:540px;max-height:80vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="font-size:16px;font-weight:600;">📋 Detail Kegiatan</h2>
            <button onclick="document.getElementById('modal-detail').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <div id="detail-content"></div>
    </div>
</div>

{{-- Modal Konfirmasi Ajukan Rencana --}}
<div id="modal-confirm-submit-plan" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:95%;max-width:480px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);text-align:left;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.05);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">📤 Konfirmasi Pengajuan Rencana Bangkom</h2>
            <button onclick="document.getElementById('modal-confirm-submit-plan').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="submit-plan-name" style="margin-bottom:16px;color:#fff;font-weight:600;padding:10px;background:rgba(255,255,255,0.02);border-radius:6px;"></p>
        <p class="text-sm text-muted" style="margin-bottom:20px;line-height:1.5;">Apakah Anda yakin ingin mengajukan rencana kegiatan ini ke Kepala Unit untuk penetapan? Status akan berubah menjadi <strong>Menunggu Penetapan</strong>.</p>
        <form method="POST" id="form-submit-plan" style="display:flex;gap:10px;justify-content:flex-end;">
            @csrf
            <button type="button" onclick="document.getElementById('modal-confirm-submit-plan').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
            <button type="submit" class="btn btn-primary" style="padding:8px 18px;border-radius:6px;font-weight:600;cursor:pointer;background:var(--primary);color:#fff;border:none;">Ya, Ajukan Kegiatan</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleEvaluasiType() {
    const evalVal = document.getElementById('input-evaluasi').value;
    const group = document.getElementById('jenis-evaluasi-group');
    if (evalVal === 'Ya') {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
    }
}

function addRepeaterRow(containerId, namePrefix, initialValue = '') {
    const container = document.getElementById(containerId);
    if (!container) return;

    const rowId = 'rep-' + Math.random().toString(36).substr(2, 9);
    const div = document.createElement('div');
    div.id = rowId;
    div.style.display = 'flex';
    div.style.gap = '8px';
    div.style.alignItems = 'center';

    const input = document.createElement('input');
    input.type = 'text';
    input.name = `${namePrefix}[]`;
    input.value = initialValue;
    input.className = 'form-control';
    input.style.background = '#1a2e45';
    input.style.color = '#fff';
    input.style.flex = '1';
    input.style.padding = '8px';
    input.style.borderRadius = '6px';
    input.style.border = '1px solid rgba(255,255,255,0.1)';
    input.style.fontSize = '12px';
    input.required = true;

    div.appendChild(input);

    // First row in repeater should not have delete button if it is index 0
    const isFirst = container.children.length === 0;
    if (!isFirst) {
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.textContent = '✕';
        delBtn.style.background = 'rgba(239,68,68,0.2)';
        delBtn.style.color = 'var(--danger)';
        delBtn.style.border = 'none';
        delBtn.style.borderRadius = '4px';
        delBtn.style.padding = '8px 12px';
        delBtn.style.cursor = 'pointer';
        delBtn.style.fontWeight = 'bold';
        delBtn.onclick = function() {
            div.remove();
        };
        div.appendChild(delBtn);
    }

    container.appendChild(div);
}

function initRepeater(containerId, namePrefix, values = []) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';

    if (values.length === 0) {
        addRepeaterRow(containerId, namePrefix);
    } else {
        values.forEach(v => {
            addRepeaterRow(containerId, namePrefix, v);
        });
    }
}

function openAddModal() {
    const modal = document.getElementById('modal-add');
    const modalContent = modal.querySelector('.atlas-modal-content');
    
    document.getElementById('modal-title').textContent = '📅 + Tambah Kegiatan Bangkom Unit';
    document.getElementById('form-kegiatan').action = "{{ route('pengampu.storeRencanaBangkom') }}";
    document.getElementById('form-method').value = 'POST';
    document.getElementById('form-kegiatan').reset();

    // Init default repeaters
    initRepeater('repeater-keberhasilan', 'indikator_keberhasilan');
    initRepeater('repeater-penugasan', 'penugasan_terkait');
    initRepeater('repeater-kriteria', 'kriteria_peserta');

    toggleEvaluasiType();

    modal.style.display = 'flex';
    modal.offsetHeight; // trigger reflow
    modal.style.opacity = '1';
    modalContent.style.transform = 'scale(1)';
}

function closeAddModal() {
    const modal = document.getElementById('modal-add');
    const modalContent = modal.querySelector('.atlas-modal-content');
    modal.style.opacity = '0';
    modalContent.style.transform = 'scale(0.95)';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 200);
}

function showEditModal(plan) {
    const modal = document.getElementById('modal-add');
    const modalContent = modal.querySelector('.atlas-modal-content');
    
    document.getElementById('modal-title').textContent = '✏️ Edit Rencana Bangkom Unit';
    document.getElementById('form-kegiatan').action = `/pengampu/rencana-bangkom/${plan.id}/update`;
    document.getElementById('form-method').value = 'PUT';

    document.getElementById('input-nama-kegiatan').value = plan.nama_kegiatan;
    document.getElementById('input-unit-pengusul').value = plan.unit_pengusul;
    document.getElementById('input-kompetensi-dasar').value = plan.kompetensi_dasar;
    document.getElementById('input-indikator-kinerja').value = plan.indikator_kinerja;
    document.getElementById('input-jenis-latar-belakang').value = plan.jenis_latar_belakang;
    document.getElementById('input-latar-belakang').value = plan.latar_belakang;
    document.getElementById('input-tujuan-kegiatan').value = plan.tujuan_kegiatan;
    document.getElementById('input-metode').value = plan.metode;
    document.getElementById('input-jalur-pembelajaran').value = plan.jalur_pembelajaran;
    document.getElementById('input-jp').value = plan.jp;
    document.getElementById('input-tanggal-mulai').value = plan.tanggal_mulai;
    document.getElementById('input-tanggal-selesai').value = plan.tanggal_selesai;
    document.getElementById('input-jumlah-kelas').value = plan.jumlah_kelas;
    document.getElementById('input-nilai-anggaran').value = plan.nilai_anggaran;
    document.getElementById('input-fasilitator').value = plan.fasilitator;
    document.getElementById('input-evaluasi').value = plan.evaluasi;
    document.getElementById('input-jenis-evaluasi').value = plan.jenis_evaluasi || 1;

    // Load repeaters from JSON arrays
    let keberhasilan = [];
    let penugasan = [];
    let kriteria = [];
    try { keberhasilan = JSON.parse(plan.indikator_keberhasilan) || []; } catch(e) {}
    try { penugasan = JSON.parse(plan.penugasan_terkait) || []; } catch(e) {}
    try { kriteria = JSON.parse(plan.kriteria_peserta) || []; } catch(e) {}

    initRepeater('repeater-keberhasilan', 'indikator_keberhasilan', keberhasilan);
    initRepeater('repeater-penugasan', 'penugasan_terkait', penugasan);
    initRepeater('repeater-kriteria', 'kriteria_peserta', kriteria);

    toggleEvaluasiType();

    modal.style.display = 'flex';
    modal.offsetHeight; // trigger reflow
    modal.style.opacity = '1';
    modalContent.style.transform = 'scale(1)';
}

function showCancelModal(id, title) {
    document.getElementById('cancel-plan-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('cancel-form').action = `/pengampu/rencana-bangkom/${id}/cancel`;
    document.getElementById('modal-cancel').style.display = 'flex';
}

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

function validateFormDates() {
    const tglMulaiStr = document.getElementById('input-tanggal-mulai').value;
    const tglSelesaiStr = document.getElementById('input-tanggal-selesai').value;
    if (!tglMulaiStr || !tglSelesaiStr) return true;

    const tglMulai = new Date(tglMulaiStr);
    const tglSelesai = new Date(tglSelesaiStr);

    if (tglMulai > tglSelesai) {
        alert('Tanggal selesai tidak boleh mendahului tanggal mulai.');
        return false;
    }

    // Tanggal mulai minimal H+1 dari hari ini (hari pembuatan)
    const today = new Date();
    today.setHours(0,0,0,0);
    const minMulai = new Date(today.getTime() + 86400000); // +1 day

    if (tglMulai < minMulai) {
        alert('Tanggal mulai harus minimal H+1 hari dari tanggal pembuatan kegiatan.');
        return false;
    }

    return true;
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

function showConfirmSubmitPlan(id, title) {
    document.getElementById('submit-plan-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('form-submit-plan').action = '/pengampu/rencana-bangkom/' + id + '/submit';
    document.getElementById('modal-confirm-submit-plan').style.display = 'flex';
}
</script>
@endpush
