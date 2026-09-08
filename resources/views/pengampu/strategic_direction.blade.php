@extends('layouts.app')
@section('title', 'Strategic Direction')
@section('header_title', 'Strategic Direction')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">🎯 Strategic Direction</h1>
</div>

<div class="card">
    <div class="card-title" style="font-size:18px;">Daftar Arahan Strategis Pimpinan</div>

    {{-- Filter & Sort Bar --}}
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
        <div style="font-weight:600; color:var(--text-secondary);">🔍 Cari:</div>
        <input type="text" id="search-title" class="form-control" style="width:200px; height:32px; padding:0 8px; font-size:12px;" placeholder="Cari nama arahan..." onkeyup="filterAndSortStrategic()">
        <div style="font-weight:600; color:var(--text-secondary); margin-left:8px;">Filter:</div>
        <select id="filter-priority" class="form-control" style="width:140px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortStrategic()">
            <option value="">Semua Prioritas</option>
            <option value="Tinggi">Tinggi</option>
            <option value="Sedang">Sedang</option>
            <option value="Rendah">Rendah</option>
        </select>
        <select id="filter-status" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortStrategic()">
            <option value="">Semua Status Tindak Lanjut</option>
            <option value="Belum">Belum Ditindaklanjuti</option>
            <option value="Sudah">Sudah Ditindaklanjuti</option>
        </select>
        <select id="filter-competency" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortStrategic()">
            <option value="">Semua Kompetensi</option>
            <option value="Analisis Data">Analisis Data</option>
            <option value="Komunikasi">Komunikasi</option>
            <option value="Komunikasi Hasil">Komunikasi Hasil</option>
            <option value="Fraud Risk Management">Fraud Risk Management</option>
            <option value="Keamanan Data Dasar">Keamanan Data Dasar</option>
        </select>
        <div style="font-weight:600; color:var(--text-secondary); margin-left:12px;">⇅ Urutkan:</div>
        <select id="sort-by" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortStrategic()">
            <option value="">Default</option>
            <option value="title-asc">Arahan (A-Z)</option>
            <option value="title-desc">Arahan (Z-A)</option>
            <option value="priority-desc">Prioritas: Tinggi-Rendah</option>
            <option value="priority-asc">Prioritas: Rendah-Tinggi</option>
        </select>
    </div>

    <table id="table-strategic">
        <thead>
            <tr>
                <th>Arahan</th>
                <th>Dasar</th>
                <th>Kompetensi</th>
                <th>Sasaran</th>
                <th>Prioritas</th>
                <th>Periode</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($directions as $d)
            <tr class="strategic-row" data-title="{{ strtolower($d->title) }}" data-priority="{{ $d->priority }}" data-status="{{ $d->follow_up }}" data-competency="{{ $d->competency }}">
                <td><strong>{{ $d->title }}</strong></td>
                <td>{{ $d->basis }}</td>
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
                        <span class="badge badge-neutral">{{ $d->status }}</span>
                    @endif
                </td>
                <td>
                    @if($d->follow_up === 'Belum' || $d->bangkom_status === 'pembatalan disetujui')
                    <button class="btn btn-sm btn-primary" onclick="prefillRencana('{{ addslashes($d->competency) }}','{{ addslashes($d->title) }}','{{ $d->id }}')">Buat Rencana</button>
                    @else
                    <span class="badge badge-success text-sm">✓ Ditindaklanjuti</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada arahan strategis dari Kepala Unit.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- Modal Add Kegiatan (same form as Rencana Bangkom) --}}
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s ease-in-out;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:700px;max-height:85vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.12);transform:scale(0.95);transition:transform 0.2s ease-in-out;font-family:'Inter', sans-serif;" id="modal-add-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:0;">📅 + Buat Rencana Kegiatan dari Arahan Strategis</h2>
            <button onclick="closeAddModal()" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('pengampu.storeRencanaBangkom') }}" id="form-kegiatan" onsubmit="return validateFormDates()">
            @csrf
            
            <input type="hidden" name="strategic_direction_id" id="input-strategic-direction-id">

            {{-- 1. Nama Kegiatan --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Nama Kegiatan *</label>
                <input type="text" name="nama_kegiatan" id="input-nama-kegiatan" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required placeholder="Nama kegiatan bangkom unit" maxlength="999">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                {{-- 2. Unit Pengusul --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Unit Pengusul *</label>
                    @if(auth()->user()->jabatan === 'Kepala Subbagian Tata Usaha Deputi')
                        <input list="unit-pengusul-list" name="unit_pengusul" id="input-unit-pengusul" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required value="" placeholder="Ketik untuk mencari direktorat...">
                        <datalist id="unit-pengusul-list">
                            @foreach($units as $unit)
                                @if(!str_contains(strtolower($unit), 'perwakilan'))
                                    <option value="{{ $unit }}"></option>
                                @endif
                            @endforeach
                        </datalist>
                    @else
                        <input type="text" name="unit_pengusul" id="input-unit-pengusul" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required value="{{ auth()->user()->unit_eselon2 }}" readonly>
                    @endif
                </div>
                {{-- 3. Kompetensi Dasar --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Kompetensi Dasar *</label>
                    <select name="kompetensi_dasar" id="input-kompetensi-dasar" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required>
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
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Indikator Kinerja *</label>
                <input type="text" name="indikator_kinerja" id="input-indikator-kinerja" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required placeholder="Indikator kinerja terkait" maxlength="999">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:16px;margin-bottom:14px;">
                {{-- 5. Jenis Latar Belakang --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jenis Latar Belakang *</label>
                    <select name="jenis_latar_belakang" id="input-jenis-latar-belakang" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required>
                        <option value="assessment-based">Gap Compass</option>
                        <option value="role-based">Role Based</option>
                        <option value="mandatory-based">Mandatory Learning</option>
                        <option value="unit-strategic-direction-based" selected>Strategic Direction Unit</option>
                        <option value="self-initiative">Inisiatif Mandiri</option>
                    </select>
                </div>
                {{-- 6. Latar Belakang --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Latar Belakang *</label>
                    <input type="text" name="latar_belakang" id="input-latar-belakang" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required placeholder="Deskripsi latar belakang" maxlength="999">
                </div>
            </div>

            {{-- 7. Tujuan Kegiatan --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Tujuan Kegiatan *</label>
                <textarea name="tujuan_kegiatan" id="input-tujuan-kegiatan" class="form-control" rows="2" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;resize:vertical;" required placeholder="Tujuan dilaksanakannya kegiatan ini..." maxlength="999"></textarea>
            </div>

            {{-- 8. Indikator Keberhasilan (Repeater) --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                    <span>Indikator Keberhasilan (Minimal 1) *</span>
                    <button type="button" onclick="addRepeaterRow('repeater-keberhasilan', 'indikator_keberhasilan')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </label>
                <div id="repeater-keberhasilan" style="display:flex;flex-direction:column;gap:8px;"></div>
            </div>

            {{-- 9. Penugasan Terkait (Repeater) --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                    <span>Penugasan Terkait (Minimal 1) *</span>
                    <button type="button" onclick="addRepeaterRow('repeater-penugasan', 'penugasan_terkait')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </label>
                <div id="repeater-penugasan" style="display:flex;flex-direction:column;gap:8px;"></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px;">
                {{-- 10. Metode --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Metode *</label>
                    <select name="metode" id="input-metode" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required>
                        <option value="Full Tatap Muka">Full Tatap Muka</option>
                        <option value="Hybrid">Hybrid</option>
                        <option value="PJJ">PJJ</option>
                    </select>
                </div>
                {{-- 11. Jalur Pembelajaran --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jalur Pembelajaran *</label>
                    <select name="jalur_pembelajaran" id="input-jalur-pembelajaran" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required>
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
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Total JP *</label>
                    <input type="number" name="jp" id="input-jp" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" min="1" max="99" required placeholder="e.g. 10">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                {{-- 13. Tanggal Mulai --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" id="input-tanggal-mulai" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required>
                </div>
                {{-- 14. Tanggal Selesai --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" id="input-tanggal-selesai" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                {{-- 15. Jumlah Kelas --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jumlah Kelas *</label>
                    <input type="number" name="jumlah_kelas" id="input-jumlah-kelas" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" min="1" max="10" required placeholder="Maksimal 10 kelas">
                </div>
                {{-- 16. Nilai Anggaran --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Nilai Anggaran (Rupiah) *</label>
                    <input type="number" name="nilai_anggaran" id="input-nilai-anggaran" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" min="0" required placeholder="e.g. 15000000">
                </div>
            </div>

            {{-- 17. Kriteria Peserta (Repeater) --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                    <span>Kriteria Peserta (Minimal 1) *</span>
                    <button type="button" onclick="addRepeaterRow('repeater-kriteria', 'kriteria_peserta')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </label>
                <div id="repeater-kriteria" style="display:flex;flex-direction:column;gap:8px;"></div>
            </div>

            <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:12px;margin-bottom:20px;">
                {{-- 18. Fasilitator --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Fasilitator *</label>
                    <input type="text" name="fasilitator" id="input-fasilitator" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required placeholder="Nama / lembaga fasilitator" maxlength="100">
                </div>
                {{-- 19. Evaluasi --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Evaluasi *</label>
                    <select name="evaluasi" id="input-evaluasi" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" onchange="toggleEvaluasiType()" required>
                        <option value="Ya">Ya</option>
                        <option value="Tidak">Tidak</option>
                    </select>
                </div>
                {{-- 20. Jenis Evaluasi --}}
                <div class="form-group" id="jenis-evaluasi-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jenis Evaluasi *</label>
                    <select name="jenis_evaluasi" id="input-jenis-evaluasi" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                        <option value="1">Level 1 (Penyelenggara, Materi, Fasilitator)</option>
                        <option value="2">Level 2 (Pre-test & Post-test)</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;border-top:1px solid var(--divider);padding-top:16px;">
                <button type="button" onclick="closeAddModal()" class="btn btn-neutral" style="padding:10px 20px;font-weight:600;cursor:pointer;border-radius:6px;border:none;background:#F1F5F9;color:#fff;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;font-weight:600;cursor:pointer;border-radius:6px;border:none;background:var(--primary);color:#fff;">💾 Simpan Rencana</button>
            </div>
        </form>
    </div>
</div>

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
    input.className = 'form-control';
    input.required = true;
    input.maxLength = 999;
    input.value = initialValue;
    input.style.cssText = 'background:#F8FAFC;color:var(--text-primary);flex:1;padding:8px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;';

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.textContent = '✕';
    removeBtn.style.cssText = 'background:rgba(255,68,68,0.15);color:#ff4444;border:1px solid rgba(255,68,68,0.2);border-radius:4px;padding:4px 8px;cursor:pointer;font-size:12px;font-weight:600;';
    removeBtn.onclick = () => div.remove();

    div.appendChild(input);
    div.appendChild(removeBtn);
    container.appendChild(div);
}

function openAddModal() {
    // Reset form
    document.getElementById('form-kegiatan').reset();
    document.getElementById('input-jenis-latar-belakang').value = 'unit-strategic-direction-based';
    document.getElementById('jenis-evaluasi-group').style.display = 'block';

    // Reset repeaters and add initial rows
    ['repeater-keberhasilan', 'repeater-penugasan', 'repeater-kriteria'].forEach(id => {
        document.getElementById(id).innerHTML = '';
    });
    addRepeaterRow('repeater-keberhasilan', 'indikator_keberhasilan');
    addRepeaterRow('repeater-penugasan', 'penugasan_terkait');
    addRepeaterRow('repeater-kriteria', 'kriteria_peserta');

    // Show modal with animation
    const modal = document.getElementById('modal-add');
    const modalContent = document.getElementById('modal-add-content');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
        modalContent.style.transform = 'scale(1)';
    }, 10);
}

function closeAddModal() {
    const modal = document.getElementById('modal-add');
    const modalContent = document.getElementById('modal-add-content');
    modal.style.opacity = '0';
    modalContent.style.transform = 'scale(0.95)';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 200);
}

function validateFormDates() {
    const tglMulaiStr = document.getElementById('input-tanggal-mulai').value;
    const tglSelesaiStr = document.getElementById('input-tanggal-selesai').value;
    if (!tglMulaiStr || !tglSelesaiStr) return true;
    const tglMulai = new Date(tglMulaiStr);
    const tglSelesai = new Date(tglSelesaiStr);
    if (tglSelesai < tglMulai) {
        alert('Tanggal Selesai tidak boleh lebih awal dari Tanggal Mulai.');
        return false;
    }
    return true;
}

function prefillRencana(competency, title, sourceId) {
    openAddModal();
    document.getElementById('input-nama-kegiatan').value = 'Workshop: ' + title;
    document.getElementById('input-kompetensi-dasar').value = competency;
    document.getElementById('input-jenis-latar-belakang').value = 'unit-strategic-direction-based';
    document.getElementById('input-latar-belakang').value = 'Tindak lanjut arahan strategis: ' + title;
    document.getElementById('input-strategic-direction-id').value = sourceId;
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

function filterAndSortStrategic() {
    const searchQuery = document.getElementById('search-title').value.toLowerCase();
    const filterPriority = document.getElementById('filter-priority').value;
    const filterStatus = document.getElementById('filter-status').value;
    const filterCompetency = document.getElementById('filter-competency').value;
    const sortBy = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('.strategic-row'));
    
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
    
    const tbody = document.querySelector('#table-strategic tbody');
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
@endsection
