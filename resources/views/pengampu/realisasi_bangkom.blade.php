@extends('layouts.app')
@section('title', 'Realisasi Bangkom Unit')
@section('header_title', 'Realisasi Bangkom Unit')

@section('content')
<div class="page-header">
    <h1>✅ Realisasi Bangkom Unit</h1>
</div>

<div class="card">
    <div class="card-title">Daftar Kegiatan Bangkom (Ditetapkan & Terealisasi)</div>

    {{-- Filter & Sort Bar --}}
    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; font-size:13px; margin-bottom:16px;">
        <div style="font-weight:600; color:var(--text-secondary);">🔍 Cari:</div>
        <input type="text" id="search-title" class="form-control" style="width:200px; height:32px; padding:0 8px; font-size:12px;" placeholder="Cari nama kegiatan..." onkeyup="filterAndSortRealisasi()">
        <div style="font-weight:600; color:var(--text-secondary); margin-left:8px;">Filter:</div>
        <select id="filter-status" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortRealisasi()">
            <option value="">Semua Status</option>
            <option value="ditetapkan">Ditetapkan</option>
            <option value="realisasi">Realisasi</option>
            <option value="persetujuan realisasi">Persetujuan Realisasi</option>
            <option value="realisasi disetujui">Realisasi Disetujui</option>
        </select>
        <select id="filter-competency" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortRealisasi()">
            <option value="">Semua Kompetensi</option>
            <option value="Analisis Data">Analisis Data</option>
            <option value="Komunikasi">Komunikasi</option>
            <option value="Komunikasi Hasil">Komunikasi Hasil</option>
            <option value="Fraud Risk Management">Fraud Risk Management</option>
            <option value="Keamanan Data Dasar">Keamanan Data Dasar</option>
        </select>
        <div style="font-weight:600; color:var(--text-secondary); margin-left:12px;">⇅ Urutkan:</div>
        <select id="sort-by" class="form-control" style="width:180px; height:32px; padding:0 8px; font-size:12px;" onchange="filterAndSortRealisasi()">
            <option value="">Default</option>
            <option value="title-asc">Nama Kegiatan (A-Z)</option>
            <option value="title-desc">Nama Kegiatan (Z-A)</option>
        </select>
    </div>

    @php
        $activeUserRole = session('active_role', auth()->user()->role ?? '');
        $isVerifikator2 = in_array($activeUserRole, ['bangkom', 'karoSDM', 'kombinasi', 'admin']);
    @endphp

    <table id="table-realisasi">
        <thead>
            <tr><th>Nama Kegiatan</th><th>Kompetensi</th><th>Tanggal</th><th>JP Rencana</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($plans as $p)
            @php
                $statusLower = strtolower($p->status);
            @endphp
            <tr class="realisasi-row" data-title="{{ strtolower($p->nama_kegiatan) }}" data-status="{{ $statusLower }}" data-competency="{{ $p->kompetensi_dasar }}">
                <td><strong>{{ $p->nama_kegiatan }}</strong></td>
                <td>{{ $p->kompetensi_dasar }}</td>
                <td>{{ $p->tanggal_mulai }} s/d {{ $p->tanggal_selesai }}</td>
                <td>{{ $p->jp }} JP</td>
                <td>
                    @if($statusLower === 'ditetapkan') 
                        <span class="badge badge-neutral">Ditetapkan</span>
                    @elseif($statusLower === 'realisasi') 
                        <span class="badge badge-info">Realisasi</span>
                    @elseif($statusLower === 'persetujuan realisasi') 
                        <span class="badge badge-warning">Persetujuan Realisasi</span>
                    @elseif($statusLower === 'realisasi disetujui') 
                        <span class="badge badge-success">✓ Realisasi Disetujui</span>
                    @else 
                        <span class="badge badge-neutral">{{ ucwords($p->status) }}</span>
                    @endif
                </td>
                <td>
                    @if($statusLower === 'ditetapkan')
                        <button class="btn btn-sm btn-primary" onclick="openRealisasiModal({{ json_encode($p) }})">Input Realisasi</button>
                    @elseif($statusLower === 'realisasi')
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                            <button class="btn btn-sm btn-primary" onclick="openRealisasiModal({{ json_encode($p) }})">✏️ Edit Realisasi</button>
                            <button type="button" class="btn btn-sm btn-warning" style="background:#f59e0b;color:#000;font-weight:600;" onclick="showConfirmAjukanPersetujuan('{{ $p->id }}', '{{ addslashes($p->nama_kegiatan) }}')">📤 Ajukan Persetujuan</button>
                            <button type="button" class="btn btn-sm btn-neutral" onclick="showDetailRealisasi({{ json_encode($p) }})">📋 Detail Realisasi</button>
                        </div>
                    @elseif($statusLower === 'persetujuan realisasi')
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                            <span class="badge badge-warning" style="font-size:11px;">⏳ Menunggu Persetujuan Realisasi</span>
                            <button type="button" class="btn btn-sm btn-neutral" onclick="showDetailRealisasi({{ json_encode($p) }})">📋 Detail Realisasi</button>
                        </div>
                    @elseif($statusLower === 'realisasi disetujui')
                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                            <button type="button" class="btn btn-sm btn-neutral" onclick="showDetailRealisasi({{ json_encode($p) }})">📋 Detail Realisasi</button>
                            <span class="text-sm text-muted">Peserta: {{ $p->realised_participants_count }} | Disetujui: {{ $p->verified2_by ?? 'Verifikator Level 2' }}</span>
                        </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada data kegiatan bangkom.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div id="pagination-controls" style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:rgba(255,255,255,0.02); border-top:1px solid rgba(255,255,255,0.05); border-radius:0 0 8px 8px;">
        <div id="pagination-info" style="font-size:12px; color:var(--text-secondary);">Showing 0 to 0 of 0 entries</div>
        <div style="display:flex; gap:4px;" id="pagination-buttons"></div>
    </div>
</div>

{{-- Modal Input Realisasi --}}
<div id="modal-realisasi" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:95%;max-width:800px;max-height:85vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.05);padding-bottom:12px;">
            <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">📋 Input Realisasi Kegiatan</h2>
            <button onclick="document.getElementById('modal-realisasi').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;">✕</button>
        </div>
        <p class="text-sm" id="realisasi-plan-name" style="margin-bottom:20px;color:#fff;font-weight:600;padding:8px;background:rgba(255,255,255,0.02);border-radius:6px;"></p>
        <form method="POST" id="realisasi-form" enctype="multipart/form-data" style="text-align:left;">
            @csrf
            
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Realisasi JP *</label>
                    <input type="number" name="realisasi_jp" class="form-control" max="99" min="0" required style="background:#1a2e45;color:#fff;width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;">
                    <div id="info-rencana-jp" style="font-size:11px;color:var(--text-secondary);margin-top:4px;">Dirancang: - JP</div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Total Realisasi Anggaran *</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:8px;color:#94a3b8;font-size:13px;">Rp</span>
                        <input type="text" id="realisasi_anggaran_display" class="form-control" required style="background:#1a2e45;color:#fff;width:100%;padding:8px 8px 8px 30px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;" onkeyup="formatCurrency(this, 'realisasi_anggaran_val')">
                        <input type="hidden" name="realisasi_anggaran" id="realisasi_anggaran_val">
                    </div>
                    <div id="info-rencana-anggaran" style="font-size:11px;color:var(--text-secondary);margin-top:4px;">Dirancang: Rp -</div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jumlah Peserta</label>
                    <input type="number" name="realisasi_peserta" id="realisasi_peserta" class="form-control" readonly style="background:#1a2e45;color:#94a3b8;width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;">
                </div>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Upload Daftar Hadir * (PDF, max 2MB)</label>
                    <input type="file" name="file_daftar_hadir" class="form-control" accept=".pdf" required style="background:#1a2e45;color:#fff;width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Upload Notulen * (PDF, max 2MB)</label>
                    <input type="file" name="file_notulen" class="form-control" accept=".pdf" required style="background:#1a2e45;color:#fff;width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;">
                </div>
            </div>
            <div style="margin-bottom:20px;">
                <div class="form-group">
                    <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Upload Dokumentasi * (JPG/PNG, max 2MB)</label>
                    <input type="file" name="file_dokumentasi" class="form-control" accept=".jpg,.jpeg,.png" required style="background:#1a2e45;color:#fff;width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;">
                </div>
            </div>

            {{-- Participant list inputs --}}
            <div style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                    <h3 style="font-size:14px;font-weight:600;color:#fff;margin:0;">👥 Daftar Peserta & Skor Penilaian</h3>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <a href="/template_evaluasi_bangkom.xls" download class="btn" style="padding:6px 12px;font-size:12px;font-weight:600;border-radius:6px;background:rgba(255,255,255,0.08);color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,0.1);display:inline-flex;align-items:center;gap:4px;cursor:pointer;">📥 Download XLS</a>
                        <label class="btn" style="padding:6px 12px;font-size:12px;font-weight:600;border-radius:6px;background:#2563eb;color:#fff;display:inline-flex;align-items:center;gap:4px;cursor:pointer;margin:0;border:none;">
                            📤 Import Nilai
                            <input type="file" id="import-excel-file" accept=".xls,.xlsx" style="display:none;" onchange="handleExcelImport(event)">
                        </label>
                    </div>
                </div>
                
                <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);border-radius:8px;padding:12px;overflow:visible;">
                    <table style="width:100%;border-collapse:collapse;font-size:12px;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,0.1);text-align:left;color:var(--text-secondary);">
                                <th style="padding:8px;">Nama Pegawai</th>
                                <th style="padding:8px;width:100px;">Penyelenggara</th>
                                <th style="padding:8px;width:100px;">Materi</th>
                                <th style="padding:8px;width:100px;">Fasilitator</th>
                                <th class="level2-header" style="padding:8px;width:100px;display:none;">Pre-Test</th>
                                <th class="level2-header" style="padding:8px;width:100px;display:none;">Post-Test</th>
                                <th style="padding:8px;width:50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="participant-rows-container">
                            {{-- Generated via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;border-top:1px solid rgba(255,255,255,0.05);padding-top:16px;">
                <button type="button" onclick="document.getElementById('modal-realisasi').style.display='none'" class="btn btn-neutral" style="padding:10px 20px;border-radius:6px;border:none;background:rgba(255,255,255,0.1);color:#fff;cursor:pointer;font-weight:600;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;border-radius:6px;border:none;background:var(--primary);color:#fff;cursor:pointer;font-weight:600;">💾 Simpan Realisasi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Realisasi --}}
<div id="modal-detail-realisasi" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:95%;max-width:850px;max-height:85vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.05);padding-bottom:12px;">
            <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">📋 Detail Realisasi Kegiatan Bangkom</h2>
            <button onclick="document.getElementById('modal-detail-realisasi').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <div id="detail-realisasi-content"></div>
        <div id="detail-realisasi-actions" style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;border-top:1px solid rgba(255,255,255,0.05);padding-top:16px;">
            <button type="button" onclick="document.getElementById('modal-detail-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Tutup</button>
        </div>
    </div>
</div>

{{-- Modal Reject / Kembalikan Realisasi --}}
<div id="modal-reject-realisasi" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:95%;max-width:500px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.05);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">↩️ Kembalikan Realisasi Kegiatan</h2>
            <button onclick="document.getElementById('modal-reject-realisasi').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="reject-realisasi-name" style="margin-bottom:16px;color:#fff;font-weight:600;padding:8px;background:rgba(255,255,255,0.02);border-radius:6px;"></p>
        <form method="POST" id="form-reject-realisasi" style="text-align:left;">
            @csrf
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label" style="color:#fff;font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Alasan Pengembalian / Catatan Perbaikan *</label>
                <textarea name="reject_reason" class="form-control" rows="4" style="background:#1a2e45;color:#fff;width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);font-size:13px;resize:vertical;" required placeholder="Tuliskan catatan perbaikan atau alasan pengembalian ke pengampu..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-reject-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
                <button type="submit" class="btn btn-danger" style="padding:8px 16px;border-radius:6px;font-weight:600;cursor:pointer;background:#ef4444;color:#fff;border:none;">Kirim Catatan Pengembalian</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Ajukan Persetujuan Realisasi --}}
<div id="modal-confirm-ajukan-persetujuan" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:95%;max-width:480px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);text-align:left;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.05);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">📤 Konfirmasi Pengajuan Persetujuan Realisasi</h2>
            <button onclick="document.getElementById('modal-confirm-ajukan-persetujuan').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="ajukan-persetujuan-plan-name" style="margin-bottom:16px;color:#fff;font-weight:600;padding:10px;background:rgba(255,255,255,0.02);border-radius:6px;"></p>
        <p class="text-sm text-muted" style="margin-bottom:20px;line-height:1.5;">Apakah Anda yakin ingin mengajukan data realisasi ini untuk diverifikasi oleh <strong>Verifikator Level 2</strong>? Status akan berubah menjadi <strong>Persetujuan Realisasi</strong>.</p>
        <form method="POST" id="form-ajukan-persetujuan" style="display:flex;gap:10px;justify-content:flex-end;">
            @csrf
            <button type="button" onclick="document.getElementById('modal-confirm-ajukan-persetujuan').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
            <button type="submit" class="btn btn-warning" style="padding:8px 18px;border-radius:6px;font-weight:600;cursor:pointer;background:#f59e0b;color:#000;border:none;">Ya, Ajukan Persetujuan</button>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Approve Realisasi --}}
<div id="modal-confirm-approve-realisasi" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:10001;align-items:center;justify-content:center;">
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:95%;max-width:480px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);text-align:left;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.05);padding-bottom:12px;">
            <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">✅ Konfirmasi Persetujuan Realisasi</h2>
            <button onclick="document.getElementById('modal-confirm-approve-realisasi').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <p class="text-sm" id="approve-realisasi-plan-name" style="margin-bottom:16px;color:#fff;font-weight:600;padding:10px;background:rgba(255,255,255,0.02);border-radius:6px;"></p>
        <p class="text-sm text-muted" style="margin-bottom:20px;line-height:1.5;">Apakah Anda yakin ingin menyetujui realisasi kegiatan bangkom ini sebagai <strong>Verifikator Level 2</strong>? Status akan berubah menjadi <strong>Realisasi Disetujui</strong> dan email verifikator Anda akan tercatat.</p>
        <form method="POST" id="form-approve-realisasi" style="display:flex;gap:10px;justify-content:flex-end;">
            @csrf
            <button type="button" onclick="document.getElementById('modal-confirm-approve-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px;border-radius:6px;cursor:pointer;">Batal</button>
            <button type="submit" class="btn btn-success" style="padding:8px 18px;border-radius:6px;font-weight:600;cursor:pointer;background:#10b981;color:#fff;border:none;">Ya, Setujui Realisasi</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
const employeesList = @json($employees);
let isLevel2 = false;
let rowIndex = 0;

function handleExcelImport(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            
            const worksheet = workbook.Sheets['template_evaluasi_bangkom'];
            if (!worksheet) {
                alert("Gagal: Sheet dengan nama 'template_evaluasi_bangkom' tidak ditemukan di dalam file Excel!");
                return;
            }

            const rows = XLSX.utils.sheet_to_json(worksheet);
            if (rows.length === 0) {
                alert("Peringatan: Tidak ada data peserta yang ditemukan di dalam sheet 'template_evaluasi_bangkom'.");
                return;
            }

            // Clear existing rows
            const container = document.getElementById('participant-rows-container');
            container.innerHTML = '';
            rowIndex = 0;

            let importedCount = 0;

            rows.forEach(row => {
                const nip = String(row.nip_baru || '').trim();
                if (!nip) return;

                const emp = employeesList.find(e => {
                    const dbNip = String(e.id).trim();
                    return dbNip === nip || dbNip.replace(/\D/g, '') === nip.replace(/\D/g, '');
                });
                if (emp) {
                    addParticipantRow();
                    const currentIdx = rowIndex - 1;
                    const rowEl = document.getElementById(`participant-row-${currentIdx}`);
                    if (rowEl) {
                        // Set NIP and display Name
                        rowEl.querySelector('.emp-search-input').value = emp.name;
                        rowEl.querySelector('.emp-id-hidden').value = emp.id;

                        // Set scores
                        if (row.skor_penyelenggara !== undefined) {
                            rowEl.querySelector(`input[name="participants[${currentIdx}][skor_penyelenggara]"]`).value = row.skor_penyelenggara;
                        }
                        if (row.skor_materi !== undefined) {
                            rowEl.querySelector(`input[name="participants[${currentIdx}][skor_materi]"]`).value = row.skor_materi;
                        }
                        if (row.skor_fasilitator !== undefined) {
                            rowEl.querySelector(`input[name="participants[${currentIdx}][skor_fasilitator]"]`).value = row.skor_fasilitator;
                        }
                        if (isLevel2) {
                            if (row.pre_test !== undefined) {
                                rowEl.querySelector(`input[name="participants[${currentIdx}][skor_pre]"]`).value = row.pre_test;
                            }
                            if (row.post_test !== undefined) {
                                rowEl.querySelector(`input[name="participants[${currentIdx}][skor_post]"]`).value = row.post_test;
                            }
                        }
                        importedCount++;
                    }
                }
            });

            alert(`Berhasil mengimpor ${importedCount} data peserta dari Excel.`);
        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan saat membaca file Excel: " + err.message);
        } finally {
            event.target.value = '';
        }
    };
    reader.readAsArrayBuffer(file);
}

function openRealisasiModal(plan) {
    document.getElementById('realisasi-plan-name').textContent = `Kegiatan: ${plan.nama_kegiatan} (${plan.id})`;
    document.getElementById('realisasi-form').action = `/pengampu/realisasi-bangkom/${plan.id}/store`;
    
    // Check if evaluation level is Level 2 (Pre/Post Test)
    isLevel2 = (plan.evaluasi === 'Ya' && plan.jenis_evaluasi == 2);
    
    // Show/Hide level 2 columns
    const level2Headers = document.querySelectorAll('.level2-header');
    level2Headers.forEach(el => {
        el.style.display = isLevel2 ? 'table-cell' : 'none';
    });

    // Reset rows container
    const container = document.getElementById('participant-rows-container');
    container.innerHTML = `<tr><td colspan="${isLevel2 ? 6 : 4}" id="empty-participant-row" style="text-align:center; padding:16px; color:var(--text-secondary);">Silakan unduh template XLS dan impor nilai untuk mengisi daftar peserta.</td></tr>`;
    rowIndex = 0;

    // Set info rencana
    document.getElementById('info-rencana-jp').textContent = `Dirancang: ${plan.jp || '-'} JP`;
    document.getElementById('info-rencana-anggaran').textContent = `Dirancang: Rp ${plan.nilai_anggaran ? parseInt(plan.nilai_anggaran).toLocaleString('id-ID') : '-'}`;

    // Show Modal
    document.getElementById('modal-realisasi').style.display = 'flex';
    updateParticipantCount();
}

function addParticipantRow() {
    const container = document.getElementById('participant-rows-container');
    const rowId = `participant-row-${rowIndex}`;

    const tr = document.createElement('tr');
    tr.id = rowId;
    tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';

    // Employee Dropdown cell
    const tdEmployee = document.createElement('td');
    tdEmployee.style.padding = '8px 4px';
    tdEmployee.innerHTML = `
        <div style="position:relative;">
            <input type="text" class="form-control emp-search-input" readonly style="background:transparent;color:#fff;width:100%;padding:6px;border:none;font-size:12px;outline:none;">
            <input type="hidden" name="participants[${rowIndex}][employee_id]" class="emp-id-hidden">
        </div>
    `;
    tr.appendChild(tdEmployee);

    // Penyelenggara Score cell
    const tdPenyelenggara = document.createElement('td');
    tdPenyelenggara.style.padding = '8px 4px';
    tdPenyelenggara.innerHTML = `
        <input type="text" name="participants[${rowIndex}][skor_penyelenggara]" class="form-control" readonly required style="background:transparent;color:#fff;width:100%;padding:6px;border:none;font-size:12px;text-align:center;outline:none;">
    `;
    tr.appendChild(tdPenyelenggara);

    // Materi Score cell
    const tdMateri = document.createElement('td');
    tdMateri.style.padding = '8px 4px';
    tdMateri.innerHTML = `
        <input type="text" name="participants[${rowIndex}][skor_materi]" class="form-control" readonly required style="background:transparent;color:#fff;width:100%;padding:6px;border:none;font-size:12px;text-align:center;outline:none;">
    `;
    tr.appendChild(tdMateri);

    // Fasilitator Score cell
    const tdFasilitator = document.createElement('td');
    tdFasilitator.style.padding = '8px 4px';
    tdFasilitator.innerHTML = `
        <input type="text" name="participants[${rowIndex}][skor_fasilitator]" class="form-control" readonly required style="background:transparent;color:#fff;width:100%;padding:6px;border:none;font-size:12px;text-align:center;outline:none;">
    `;
    tr.appendChild(tdFasilitator);

    // Pre-test Score cell (conditionally visible)
    const tdPre = document.createElement('td');
    tdPre.className = 'level2-column';
    tdPre.style.padding = '8px 4px';
    tdPre.style.display = isLevel2 ? 'table-cell' : 'none';
    tdPre.innerHTML = `
        <input type="text" name="participants[${rowIndex}][skor_pre]" class="form-control" readonly ${isLevel2 ? 'required' : ''} style="background:transparent;color:#fff;width:100%;padding:6px;border:none;font-size:12px;text-align:center;outline:none;">
    `;
    tr.appendChild(tdPre);

    // Post-test Score cell (conditionally visible)
    const tdPost = document.createElement('td');
    tdPost.className = 'level2-column';
    tdPost.style.padding = '8px 4px';
    tdPost.style.display = isLevel2 ? 'table-cell' : 'none';
    tdPost.innerHTML = `
        <input type="text" name="participants[${rowIndex}][skor_post]" class="form-control" readonly ${isLevel2 ? 'required' : ''} style="background:transparent;color:#fff;width:100%;padding:6px;border:none;font-size:12px;text-align:center;outline:none;">
    `;
    tr.appendChild(tdPost);

    // Action/Delete button cell
    const tdAction = document.createElement('td');
    tdAction.style.padding = '8px 4px';
    tdAction.style.textAlign = 'center';
    
    tdAction.innerHTML = '-';
    tr.appendChild(tdAction);

    container.appendChild(tr);
    rowIndex++;
    updateParticipantCount();
}

function updateParticipantCount() {
    const container = document.getElementById('participant-rows-container');
    let count = container.querySelectorAll('tr').length;
    if (document.getElementById('empty-participant-row')) {
        count = 0;
    }
    document.getElementById('realisasi_peserta').value = count;
}

function formatCurrency(el, hiddenId) {
    let val = el.value.replace(/[^0-9]/g, '');
    if (val) {
        document.getElementById(hiddenId).value = val;
        el.value = parseInt(val).toLocaleString('id-ID');
    } else {
        document.getElementById(hiddenId).value = '';
        el.value = '';
    }
}
function onEmpSearchInput(input) {
    const container = input.parentElement.querySelector('.emp-search-results');
    const query = input.value.toLowerCase().trim();
    container.innerHTML = '';

    const matched = employeesList.filter(emp => {
        if (!query) return true;
        // Strict prefix matching on NIP or name words
        const isNipMatch = emp.id.toLowerCase().startsWith(query);
        const nameWords = emp.name.toLowerCase().split(/\s+/);
        const isNameMatch = nameWords.some(word => word.startsWith(query));
        return isNipMatch || isNameMatch;
    });

    if (matched.length === 0) {
        container.innerHTML = '<div style="padding:8px 12px;color:var(--text-secondary);font-size:12px;">Tidak ada hasil</div>';
    } else {
        matched.slice(0, 10).forEach(emp => {
            const item = document.createElement('div');
            item.style.padding = '8px 12px';
            item.style.cursor = 'pointer';
            item.style.fontSize = '12px';
            item.style.color = '#fff';
            item.style.transition = 'background 0.2s';
            item.innerHTML = `<strong>${emp.name}</strong> <span style="color:var(--text-secondary);font-size:11px;">(NIP. ${emp.id})</span>`;

            item.onmouseover = () => item.style.background = 'rgba(255,255,255,0.05)';
            item.onmouseout = () => item.style.background = 'transparent';

            item.onmousedown = (e) => {
                e.preventDefault();
                input.value = emp.name;
                input.parentElement.querySelector('.emp-id-hidden').value = emp.id;
                container.style.display = 'none';
            };
            container.appendChild(item);
        });
    }
    container.style.display = 'block';
}

function onEmpSearchFocus(input) {
    onEmpSearchInput(input);
}

function onEmpSearchBlur(input) {
    setTimeout(() => {
        input.parentElement.querySelector('.emp-search-results').style.display = 'none';
        const hiddenInput = input.parentElement.querySelector('.emp-id-hidden');
        if (!hiddenInput.value) {
            input.value = '';
        }
    }, 200);
}

function onEmpSearchKeydown(input, e) {
    const hiddenInput = input.parentElement.querySelector('.emp-id-hidden');
    if (hiddenInput.value) {
        // If a selection is present, any key press (other than navigation/control) resets the selection
        const allowedKeys = ['Tab', 'Enter', 'Shift', 'Control', 'Alt', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Escape'];
        if (!allowedKeys.includes(e.key)) {
            hiddenInput.value = '';
            input.value = '';
            // Immediately trigger search again for the key they just typed
            setTimeout(() => {
                onEmpSearchInput(input);
            }, 0);
        }
    }
}

function onEmpSearchPaste(input) {
    const hiddenInput = input.parentElement.querySelector('.emp-id-hidden');
    hiddenInput.value = '';
    input.value = '';
    setTimeout(() => {
        onEmpSearchInput(input);
    }, 0);
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

function filterAndSortRealisasi() {
    const searchQuery = document.getElementById('search-title').value.toLowerCase();
    const filterStatus = document.getElementById('filter-status').value;
    const filterCompetency = document.getElementById('filter-competency').value;
    const sortBy = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('.realisasi-row'));
    filteredRows = [];
    
    rows.forEach(row => {
        const title = row.getAttribute('data-title') || '';
        const s = row.getAttribute('data-status');
        const c = row.getAttribute('data-competency');
        
        const matchSearch = matchQuery(title, searchQuery);
        const matchStatus = !filterStatus || s.toLowerCase() === filterStatus.toLowerCase();
        const matchCompetency = !filterCompetency || c === filterCompetency;
        
        if (matchSearch && matchStatus && matchCompetency) {
            filteredRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });
    
    const tbody = document.querySelector('#table-realisasi tbody');
    if (sortBy) {
        filteredRows.sort((a, b) => {
            if (sortBy === 'title-asc') {
                return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
            } else if (sortBy === 'title-desc') {
                return b.getAttribute('data-title').localeCompare(a.getAttribute('data-title'));
            }
            return 0;
        });
    }
    
    filteredRows.forEach(row => tbody.appendChild(row));
    displayPage(1);
}

document.addEventListener('DOMContentLoaded', () => {
    filterAndSortRealisasi();
});

function showDetailRealisasi(plan) {
    const modal = document.getElementById('modal-detail-realisasi');
    const content = document.getElementById('detail-realisasi-content');
    const actions = document.getElementById('detail-realisasi-actions');

    const statusLower = (plan.status || '').toLowerCase();
    let statusBadge = `<span class="badge badge-neutral">${plan.status}</span>`;
    if (statusLower === 'ditetapkan') statusBadge = `<span class="badge badge-neutral">Ditetapkan</span>`;
    else if (statusLower === 'realisasi') statusBadge = `<span class="badge badge-info">Realisasi</span>`;
    else if (statusLower === 'persetujuan realisasi') statusBadge = `<span class="badge badge-warning">Persetujuan Realisasi</span>`;
    else if (statusLower === 'realisasi disetujui') statusBadge = `<span class="badge badge-success">✓ Realisasi Disetujui</span>`;

    const isLevel2Plan = parseInt(plan.jenis_evaluasi) === 2;

    let participantsRows = '';
    if (plan.participants && plan.participants.length > 0) {
        plan.participants.forEach((p, idx) => {
            participantsRows += `
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05); font-size:12px;">
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
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:16px; font-size:13px;">
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

        <div style="margin-bottom:20px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:14px;">
            <div style="font-weight:600; color:#fff; font-size:13px; margin-bottom:10px;">📁 Dokumen Bukti Realisasi</div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; font-size:12px;">
                <div style="background:#1a2e45; padding:10px; border-radius:6px; border:1px solid rgba(255,255,255,0.05);">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">1. Daftar Hadir</div>
                    <div style="color:#fff; font-weight:600; word-break:break-all;">📄 ${plan.dok_daftar_hadir || '<span class="text-muted">Belum ada</span>'}</div>
                </div>
                <div style="background:#1a2e45; padding:10px; border-radius:6px; border:1px solid rgba(255,255,255,0.05);">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">2. Notulen Kegiatan</div>
                    <div style="color:#fff; font-weight:600; word-break:break-all;">📄 ${plan.dok_notulen || '<span class="text-muted">Belum ada</span>'}</div>
                </div>
                <div style="background:#1a2e45; padding:10px; border-radius:6px; border:1px solid rgba(255,255,255,0.05);">
                    <div style="color:var(--text-secondary); font-size:11px; margin-bottom:4px;">3. Foto Dokumentasi</div>
                    <div style="color:#fff; font-weight:600; word-break:break-all;">🖼️ ${plan.dok_dokumentasi || '<span class="text-muted">Belum ada</span>'}</div>
                </div>
            </div>
        </div>

        <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div style="font-weight:600; color:#fff; font-size:13px;">👥 Daftar Peserta & Skor (${plan.participants ? plan.participants.length : 0} Pegawai)</div>
            </div>
            <div style="max-height:260px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1); color:var(--text-secondary); font-size:11px; text-align:center;">
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

    // Action buttons inside modal
    // Action buttons inside modal for perencana view
    let actionButtons = `<button type="button" onclick="document.getElementById('modal-detail-realisasi').style.display='none'" class="btn btn-neutral" style="padding:8px 16px; border-radius:6px; cursor:pointer;">Tutup</button>`;
    actions.innerHTML = actionButtons;
    modal.style.display = 'flex';
}

function showConfirmAjukanPersetujuan(id, title) {
    document.getElementById('ajukan-persetujuan-plan-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('form-ajukan-persetujuan').action = '/pengampu/realisasi-bangkom/' + id + '/submit-persetujuan';
    document.getElementById('modal-confirm-ajukan-persetujuan').style.display = 'flex';
}

function showConfirmApproveRealisasi(id, title) {
    document.getElementById('approve-realisasi-plan-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('form-approve-realisasi').action = '/pengampu/realisasi-bangkom/' + id + '/approve';
    document.getElementById('modal-confirm-approve-realisasi').style.display = 'flex';
}

function openRejectRealisasiModal(id, title) {
    document.getElementById('reject-realisasi-name').textContent = 'Kegiatan: ' + title;
    document.getElementById('form-reject-realisasi').action = '/pengampu/realisasi-bangkom/' + id + '/reject';
    document.getElementById('modal-reject-realisasi').style.display = 'flex';
}
</script>
@endpush
