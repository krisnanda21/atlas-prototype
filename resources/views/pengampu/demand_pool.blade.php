@extends('layouts.app')
@section('title', 'IDP Demand Pool')
@section('header_title', 'IDP Demand Pool')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">📥 IDP Demand Pool</h1>
    <span class="badge badge-neutral">Unit: 
        @if(session('active_role', auth()->user()->role ?? '') === 'bangkom')
            Biro Sumber Daya Manusia
        @elseif(auth()->user()->jabatan === 'Kepala Subbagian Tata Usaha Deputi')
            {{ auth()->user()->unit_eselon1 }}
        @else
            {{ auth()->user()->unit_eselon2 ?? '-' }}
        @endif
    </span>
</div>

{{-- KPI Cards --}}
<div class="grid-4 mb-4" >
    <div class="stat-card" style="display:flex; flex-direction:column; height:120px;">
        <div style="font-size:16px; color:var(--text-primary); margin-bottom:8px; font-weight:600;">📋 IDP Demand</div>
        <div class="stat-value" style="font-size:35px">{{ $totalDemand }}</div>
    </div>
    <div class="stat-card" style="display:flex; flex-direction:column; height:120px;">
        <div style="font-size:16px; color:var(--text-primary); margin-bottom:8px; font-weight:600;">✅ IDP Approved</div>
        <div class="stat-value" style="font-size:35px">{{ $executedPercent }}%</div>
    </div>
    <div class="stat-card" style="display:flex; flex-direction:column; height:120px;">
        <div style="font-size:16px; color:var(--text-primary); margin-bottom:8px; font-weight:600;">🚀 Bangkom Submit</div>
        <div class="stat-value" style="font-size:35px">{{ $bangkomSubmitPercent }}%</div>
    </div>
    <div class="stat-card" style="display:flex; flex-direction:column; height:120px;">
        <div style="font-size:16px; color:var(--text-primary); margin-bottom:8px; font-weight:600;">📈 Bangkom Realisasi</div>
        <div class="stat-value" style="font-size:35px">{{ $bangkomRealisasiPercent }}%</div>
    </div>
</div>

{{-- Kanban Work Board --}}
<div class="card mb-4">
    <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
        <span style="font-size:18px;">🗂️ Papan Kerja Bangkom (Pipeline Status)</span>
        <span style="font-size:11px; font-weight:normal; color:var(--text-secondary);">Pantau progres dari arahan strategis hingga realisasi</span>
    </div>
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; overflow-x:auto;">
        
        {{-- 1. Strategic Direction --}}
        <div style="background:rgba(14,165,233,0.06); border:1px solid rgba(14,165,233,0.20); border-radius:10px; padding:12px; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid rgba(14,165,233,0.15);">
                <span style="font-size:11px; font-weight:700; color:var(--accent); text-transform:uppercase; letter-spacing:0.5px;">🎯 Strategic Direction</span>
                <span class="badge" style="background:rgba(14,165,233,0.15); color:var(--accent); font-size:10px; font-weight:700; padding:2px 6px;">{{ $kanbanStrategic->count() }}</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; max-height:300px; overflow-y:auto; padding-right:4px;">
                @foreach($kanbanStrategic as $dir)
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px; font-size:12px; transition:transform 0.15s ease;">
                    <div style="font-weight:600; color:var(--text-primary); margin-bottom:6px; line-height:1.3;">{{ $dir->competency }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                        <span class="badge {{ $dir->priority === 'Tinggi' ? 'badge-danger' : ($dir->priority === 'Sedang' ? 'badge-warning' : 'badge-neutral') }}" style="font-size:10px; padding:2px 6px;">
                            {{ $dir->priority === 'Tinggi' ? '🔴' : ($dir->priority === 'Sedang' ? '🟡' : '⚪') }} {{ $dir->priority }}
                        </span>
                        <span style="font-size:11px; color:var(--text-secondary);">🎯 {{ $dir->period ?? '2026' }}</span>
                    </div>
                    <div style="margin-top:6px; pt:4px; border-top:1px dashed #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:10px; color:var(--text-secondary);">Status Tindak Lanjut:</span>
                        @if($dir->is_followed_up)
                            <span class="badge badge-success" style="font-size:9px; padding:2px 5px;">✓ Masuk Rencana</span>
                        @else
                            <span class="badge" style="font-size:9px; padding:2px 5px; background:rgba(239,68,68,0.15); color:#ef4444;">Belum Terencana</span>
                        @endif
                    </div>
                </div>
                @endforeach
                @if($kanbanStrategic->isEmpty())
                <div class="text-muted text-sm" style="text-align:center; padding:30px 10px;">Belum ada arahan strategis.</div>
                @endif
            </div>
        </div>

        {{-- 2. Need / IDP Diajukan (Agregat Topik) --}}
        <div style="background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.2); border-radius:10px; padding:12px; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid rgba(245,158,11,0.15);">
                <span style="font-size:11px; font-weight:700; color:var(--warning); text-transform:uppercase; letter-spacing:0.5px;">📋 Need / IDP Diajukan</span>
                <span class="badge" style="background:rgba(245,158,11,0.2); color:var(--warning); font-size:10px; font-weight:700; padding:2px 6px;">{{ count($kanbanNeeds) }} Topik</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; max-height:300px; overflow-y:auto; padding-right:4px;">
                @foreach($kanbanNeeds as $need)
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px; font-size:12px;" title="Pegawai: {{ implode(', ', $need->employees) }}">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px; gap:6px;">
                        <div style="font-weight:600; color:var(--text-primary); line-height:1.3;">{{ $need->competency }}</div>
                        <span class="badge badge-info" style="font-size:10px; font-weight:600; padding:2px 6px; white-space:nowrap;">👥 {{ $need->count }} Pegawai</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                        <span class="badge {{ $need->top_priority === 'Tinggi' ? 'badge-danger' : ($need->top_priority === 'Sedang' ? 'badge-warning' : 'badge-neutral') }}" style="font-size:10px; padding:2px 6px;">
                            {{ $need->top_priority === 'Tinggi' ? '🔴' : ($need->top_priority === 'Sedang' ? '🟡' : '⚪') }} {{ $need->top_priority }}
                        </span>
                        @if($need->is_followed_up)
                            <span class="badge badge-success" style="font-size:9px; padding:2px 5px;">✓ Masuk Rencana</span>
                        @else
                            <span class="badge" style="font-size:9px; padding:2px 5px; background:rgba(239,68,68,0.15); color:#ef4444;">⚠️ Belum Terencana</span>
                        @endif
                    </div>
                </div>
                @endforeach
                @if(empty($kanbanNeeds))
                <div class="text-muted text-sm" style="text-align:center; padding:30px 10px;">Tidak ada IDP diajukan.</div>
                @endif
            </div>
        </div>

        {{-- 3. Rencana Bangkom --}}
        <div style="background:rgba(34,197,94,0.06); border:1px solid rgba(34,197,94,0.2); border-radius:10px; padding:12px; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid rgba(34,197,94,0.15);">
                <span style="font-size:11px; font-weight:700; color:var(--success); text-transform:uppercase; letter-spacing:0.5px;">📅 Rencana Bangkom</span>
                <span class="badge" style="background:rgba(34,197,94,0.2); color:var(--success); font-size:10px; font-weight:700; padding:2px 6px;">{{ $kanbanPlans->count() }}</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; max-height:300px; overflow-y:auto; padding-right:4px;">
                @foreach($kanbanPlans as $plan)
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px; font-size:12px;">
                    <div style="font-weight:600; color:var(--text-primary); margin-bottom:4px; line-height:1.3;">{{ $plan->title }}</div>
                    <div style="font-size:11px; color:var(--text-secondary); margin-bottom:6px;">📌 {{ $plan->competency }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                        @if($plan->status === 'ditetapkan')
                            <span class="badge badge-success" style="font-size:9px; padding:2px 6px;">🟢 Ditetapkan</span>
                        @elseif($plan->status === 'menunggu penetapan')
                            <span class="badge badge-warning" style="font-size:9px; padding:2px 6px;">🟡 Menunggu Penetapan</span>
                        @else
                            <span class="badge badge-neutral" style="font-size:9px; padding:2px 6px;">⚪ {{ ucfirst($plan->status) }}</span>
                        @endif
                        <span style="font-size:11px; color:var(--text-secondary);">⏱️ {{ $plan->jp ?? 0 }} JP</span>
                    </div>
                </div>
                @endforeach
                @if($kanbanPlans->isEmpty())
                <div class="text-muted text-sm" style="text-align:center; padding:30px 10px;">Tidak ada rencana aktif.</div>
                @endif
            </div>
        </div>

        {{-- 4. Evidence / Realisasi --}}
        <div style="background:rgba(56,189,248,0.06); border:1px solid rgba(56,189,248,0.2); border-radius:10px; padding:12px; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid rgba(56,189,248,0.15);">
                <span style="font-size:11px; font-weight:700; color:var(--info); text-transform:uppercase; letter-spacing:0.5px;">📎 Evidence / Realisasi</span>
                <span class="badge" style="background:rgba(56,189,248,0.2); color:var(--info); font-size:10px; font-weight:700; padding:2px 6px;">{{ $kanbanEvidence->count() }}</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; max-height:300px; overflow-y:auto; padding-right:4px;">
                @foreach($kanbanEvidence as $ev)
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px; font-size:12px;">
                    <div style="font-weight:600; color:var(--text-primary); margin-bottom:4px; line-height:1.3;">{{ $ev->title }}</div>
                    <div style="font-size:11px; color:var(--text-secondary); margin-bottom:6px;">📌 {{ $ev->competency }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                        <span class="badge badge-info" style="font-size:9px; padding:2px 6px;">{{ ucfirst($ev->status) }}</span>
                        <span style="font-size:11px; color:var(--success); font-weight:600;">✅ {{ $ev->jp ?? 0 }} JP · {{ $ev->peserta_count ?? 0 }} Peserta</span>
                    </div>
                </div>
                @endforeach
                @if($kanbanEvidence->isEmpty())
                <div class="text-muted text-sm" style="text-align:center; padding:30px 10px;">Belum ada realisasi.</div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Demand Pool Table (Cascading View: Teknis & Mansoskul) --}}
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:20px;">
        <div>
            <div class="card-title" style="margin-bottom:4px;font-size:18px">📋 Demand Pool Multi-Source</div>
            <div style="font-size:12px; color:var(--text-secondary);">Daftar kebutuhan bangkom terkelompok secara hierarkis (Teknis & Mansoskul)</div>
        </div>
        
        {{-- Prefix Search Input --}}
        <div style="position:relative; width:320px;">
            <input type="text" id="search-competency" class="form-control" placeholder="Cari kompetensi" oninput="filterDemandTable()" style="height:36px; padding-left:34px; font-size:12px; width:100%;">
            <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-secondary); pointer-events:none;">🔍</span>
        </div>
    </div>

    {{-- Global No Search Results Alert --}}
    <div id="global-no-results" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:8px; padding:16px; text-align:center; color:#ef4444; font-size:13px; margin-bottom:16px;">
        ⚠️ Kompetensi tidak ditemukan "<strong id="search-query-display"></strong>".
    </div>

    {{-- 1. Cascading Section: Kompetensi Teknis --}}
    <div class="category-section" id="section-teknis" style="margin-bottom:28px;">
        <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(56,189,248,0.08); border-left:4px solid var(--accent); padding:10px 14px; border-radius:0 8px 8px 0; margin-bottom:12px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:14px; font-weight:700; color:var(--accent);">🛠️ Kelompok Kompetensi Teknis</span>
                <span class="badge" style="background:rgba(14,165,233,0.15); color:var(--accent); font-size:10px; font-weight:700; padding:2px 8px;" id="count-teknis">
                    {{ count($demandsTeknis) }} Kompetensi
                </span>
            </div>
            <span style="font-size:11px; color:var(--text-secondary);">Substantif & Keahlian Khusus Unit</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nama Kompetensi</th>
                    <th>Jumlah Pengajuan / Demand</th>
                    <th>Status Tindak Lanjut</th>
                    <th>Bangkom Unit</th>
                    <th style="width:200px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody-teknis">
                @forelse($demandsTeknis as $d)
                <tr class="demand-row" data-category="Teknis" data-competency="{{ $d['competency'] }}">
                    <td><strong>{{ $d['competency'] }}</strong></td>
                    <td><strong style="color:var(--accent);">{{ $d['total_demand'] }} pengajuan</strong></td>
                    <td>
                        @if($d['follow_up'] === 'Belum')
                            <span class="badge" style="background:rgba(239,68,68,0.2);color:#ef4444;">Belum</span>
                        @else
                            <span class="badge badge-success">Sudah</span>
                        @endif
                    </td>
                    <td><strong style="color:var(--info);">{{ $d['bangkom_unit_count'] ?? 0 }} kegiatan</strong></td>
                    <td style="text-align:center; display:flex; gap:6px; justify-content:center;">
                        <button class="btn btn-sm btn-neutral" onclick="showDemandDetail({{ json_encode($d['competency']) }}, {{ json_encode($d['items']) }})">🔍 Detail</button>
                        <button class="btn btn-sm btn-primary" onclick="openAddBangkomModal('{{ addslashes($d['competency']) }}')">+ Buat Bangkom</button>
                    </td>
                </tr>
                @empty
                <tr class="db-empty-row"><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:20px;">Tidak ada data kompetensi teknis.</td></tr>
                @endforelse
                <tr id="teknis-search-empty" style="display:none;">
                    <td colspan="5" class="text-muted text-sm" style="text-align:center;padding:16px;">Tidak ada kompetensi teknis yang diawali kata kunci tersebut.</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 2. Cascading Section: Kompetensi Mansoskul --}}
    <div class="category-section" id="section-mansoskul">
        <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(245,158,11,0.08); border-left:4px solid var(--warning); padding:10px 14px; border-radius:0 8px 8px 0; margin-bottom:12px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:14px; font-weight:700; color:var(--warning);">🤝 Kelompok Kompetensi Mansoskul (Manajerial & Sosial Kultural)</span>
                <span class="badge" style="background:rgba(245,158,11,0.2); color:var(--warning); font-size:10px; font-weight:700; padding:2px 8px;" id="count-mansoskul">
                    {{ count($demandsMansoskul) }} Kompetensi
                </span>
            </div>
            <span style="font-size:11px; color:var(--text-secondary);">Standar Kompetensi ASN Terintegrasi</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nama Kompetensi</th>
                    <th>Jumlah Pengajuan / Demand</th>
                    <th>Status Tindak Lanjut</th>
                    <th>Bangkom Unit</th>
                    <th style="width:200px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody-mansoskul">
                @forelse($demandsMansoskul as $d)
                <tr class="demand-row" data-category="Mansoskul" data-competency="{{ $d['competency'] }}">
                    <td><strong>{{ $d['competency'] }}</strong></td>
                    <td><strong style="color:var(--warning);">{{ $d['total_demand'] }} pengajuan</strong></td>
                    <td>
                        @if($d['follow_up'] === 'Belum')
                            <span class="badge" style="background:rgba(239,68,68,0.2);color:#ef4444;">Belum</span>
                        @else
                            <span class="badge badge-success">Sudah</span>
                        @endif
                    </td>
                    <td><strong style="color:var(--info);">{{ $d['bangkom_unit_count'] ?? 0 }} kegiatan</strong></td>
                    <td style="text-align:center; display:flex; gap:6px; justify-content:center;">
                        <button class="btn btn-sm btn-neutral" onclick="showDemandDetail({{ json_encode($d['competency']) }}, {{ json_encode($d['items']) }})">🔍 Detail</button>
                        <button class="btn btn-sm btn-primary" onclick="openAddBangkomModal('{{ addslashes($d['competency']) }}')">+ Buat Bangkom</button>
                    </td>
                </tr>
                @empty
                <tr class="db-empty-row"><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:20px;">Tidak ada data kompetensi mansoskul.</td></tr>
                @endforelse
                <tr id="mansoskul-search-empty" style="display:none;">
                    <td colspan="5" class="text-muted text-sm" style="text-align:center;padding:16px;">Tidak ada kompetensi mansoskul yang diawali kata kunci tersebut.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

{{-- Modal Detail Demand --}}
<div id="modal-demand" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:780px;max-height:85vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 id="demand-modal-title" style="font-size:16px;font-weight:600;">📋 Detail Pengajuan IDP</h2>
            <button onclick="document.getElementById('modal-demand').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th>Pegawai / Sumber</th>
                    <th>Tipe Sumber</th>
                    <th>Prioritas</th>
                    <th>Basis / Latar Belakang</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="demand-items-body"></tbody>
        </table>
        
        {{-- Pagination Container --}}
        <div id="demand-pagination" style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
        </div>
    </div>
</div>

{{-- Modal Add Kegiatan --}}
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s ease-in-out;">
    <div class="atlas-modal-content" style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:95%;max-width:700px;max-height:85vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.12);transform:scale(0.95);transition:transform 0.2s ease-in-out;font-family:'Inter', sans-serif;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid var(--divider);padding-bottom:12px;">
            <h2 id="modal-title" style="font-size:18px;font-weight:700;color:var(--text-primary);margin:0;">📅 + Tambah Kegiatan Bangkom Unit</h2>
            <button onclick="closeAddModal()" style="background:none;border:none;color:var(--text-secondary);font-size:24px;cursor:pointer;padding:0;line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('pengampu.storeRencanaBangkom') }}" id="form-kegiatan">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            {{-- 1. Nama Kegiatan --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Nama Kegiatan *</label>
                <input type="text" name="nama_kegiatan" id="input-nama-kegiatan" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required placeholder="Nama kegiatan bangkom unit (maksimal 999 karakter)" maxlength="999">
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
                    <input type="text" name="kompetensi_dasar" id="input-kompetensi-dasar" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required readonly>
                </div>
            </div>

            {{-- 4. Indikator Kinerja --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Indikator Kinerja *</label>
                <input type="text" name="indikator_kinerja" id="input-indikator-kinerja" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required placeholder="Indikator kinerja terkait (maksimal 999 karakter)" maxlength="999">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:16px;margin-bottom:14px;">
                {{-- 5. Jenis Latar Belakang --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Jenis Latar Belakang *</label>
                    <select name="jenis_latar_belakang" id="input-jenis-latar-belakang" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required>
                        <option value="assessment-based">Gap Compass</option>
                        <option value="role-based">Role Based</option>
                        <option value="mandatory-based">Mandatory Learning</option>
                        <option value="unit-strategic-direction-based">Strategic Direction Unit</option>
                        <option value="self-initiative">Inisiatif Mandiri</option>
                    </select>
                </div>
                {{-- 6. Latar Belakang --}}
                <div class="form-group" style="text-align:left;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Latar Belakang *</label>
                    <input type="text" name="latar_belakang" id="input-latar-belakang" class="form-control" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" required placeholder="Deskripsi latar belakang (maksimal 999 karakter)" maxlength="999">
                </div>
            </div>

            {{-- 7. Tujuan Kegiatan --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Tujuan Kegiatan *</label>
                <textarea name="tujuan_kegiatan" id="input-tujuan-kegiatan" class="form-control" rows="2" style="background:#F8FAFC;color:var(--text-primary);width:100%;padding:10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;resize:vertical;" required placeholder="Tujuan dilaksanakannya kegiatan ini..." maxlength="999"></textarea>
            </div>

            {{-- 8. Indikator Keberhasilan (Repeater) --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                        <span>Indikator Keberhasilan (Minimal 1) *</span>
                    </label>
                    <button type="button" onclick="addRepeaterRow('repeater-keberhasilan', 'indikator_keberhasilan')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </div>
                
                <div 
                    id="repeater-keberhasilan" 
                    style="display:flex;flex-direction:column;gap:8px;">
                    {{-- Row inputs will be generated dynamically by JS --}}
                </div>
            </div>

            {{-- 9. Penugasan Terkait (Repeater) --}}
            <div class="form-group" style="margin-bottom:14px; text-align:left;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                        <span>Penugasan Terkait (Minimal 1) *</span>
                    </label>
                    <button type="button" onclick="addRepeaterRow('repeater-penugasan', 'penugasan_terkait')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </div>

                <div 
                    id="repeater-penugasan" 
                    style="display:flex;flex-direction:column;gap:8px;">
                    {{-- Row inputs will be generated dynamically by JS --}}
                </div>
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
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="form-label" style="color:var(--text-primary);font-size:12px;font-weight:600;margin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:center;">
                        <span>Kriteria Peserta (Minimal 1) *</span>
                    </label>
                    <button type="button" onclick="addRepeaterRow('repeater-kriteria', 'kriteria_peserta')" style="background:var(--primary);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-weight:600;">+ Tambah</button>
                </div>
                
                <div 
                    id="repeater-kriteria" 
                    style="display:flex;flex-direction:column;gap:8px;">
                    {{-- Row inputs will be generated dynamically by JS --}}
                </div>
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
                <button type="button" onclick="closeAddModal()" class="btn btn-neutral" style="padding:10px 20px;font-weight:600;cursor:pointer;border-radius:6px;border:none;background:#F1F5F9;color:var(--text-primary);">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;font-weight:600;cursor:pointer;border-radius:6px;border:none;background:var(--primary);color:#fff;">💾 Simpan Rencana</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function filterDemandTable() {
    const searchInput = document.getElementById('search-competency');
    const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
    
    const rows = document.querySelectorAll('.demand-row');
    let visibleTeknis = 0;
    let visibleMansoskul = 0;

    rows.forEach(row => {
        const compName = (row.dataset.competency || '').trim().toLowerCase();
        const category = row.dataset.category;

        // Prefix matching ONLY: compName must start with query
        const matchesPrefix = (query === '') || compName.startsWith(query);

        if (matchesPrefix) {
            row.style.display = '';
            if (category === 'Teknis') visibleTeknis++;
            if (category === 'Mansoskul') visibleMansoskul++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update section empty indicators
    const emptyTeknis = document.getElementById('teknis-search-empty');
    const emptyMansoskul = document.getElementById('mansoskul-search-empty');
    const globalNoResults = document.getElementById('global-no-results');
    const queryDisplay = document.getElementById('search-query-display');

    if (emptyTeknis) {
        emptyTeknis.style.display = (visibleTeknis === 0 && query !== '' && {{ count($demandsTeknis) }} > 0) ? '' : 'none';
    }
    if (emptyMansoskul) {
        emptyMansoskul.style.display = (visibleMansoskul === 0 && query !== '' && {{ count($demandsMansoskul) }} > 0) ? '' : 'none';
    }

    if (globalNoResults) {
        if (query !== '' && visibleTeknis === 0 && visibleMansoskul === 0) {
            if (queryDisplay) queryDisplay.textContent = searchInput.value.trim();
            globalNoResults.style.display = '';
        } else {
            globalNoResults.style.display = 'none';
        }
    }
}

let currentDemandItems = [];
let currentDemandPage = 1;
const demandItemsPerPage = 10;

function showDemandDetail(competencyName, items) {
    document.getElementById('demand-modal-title').textContent = '📥 Detail Pengajuan: ' + competencyName;
    currentDemandItems = items;
    currentDemandPage = 1;
    
    renderDemandTable();
    document.getElementById('modal-demand').style.display = 'flex';
}

function renderDemandTable() {
    const startIndex = (currentDemandPage - 1) * demandItemsPerPage;
    const endIndex = startIndex + demandItemsPerPage;
    const itemsToRender = currentDemandItems.slice(startIndex, endIndex);

    let rows = '';
    itemsToRender.forEach(item => {
        let pColor = 'badge-neutral';
        if (item.priority === 'Tinggi') pColor = 'badge-danger';
        else if (item.priority === 'Sedang') pColor = 'badge-warning';

        rows += `
            <tr>
                <td><strong>${item.employee_name}</strong></td>
                <td><span class="badge badge-neutral" style="font-size:10px;">${item.source}</span></td>
                <td><span class="badge ${pColor}">${item.priority}</span></td>
                <td><span class="text-sm" style="color:var(--text-secondary); display:block; max-width:280px; white-space:normal; word-wrap:break-word;">${item.basis || '-'}</span></td>
                <td><span class="badge badge-neutral">${item.status}</span></td>
            </tr>
        `;
    });
    
    document.getElementById('demand-items-body').innerHTML = rows || '<tr><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:20px;">Tidak ada pengajuan.</td></tr>';
    
    renderDemandPagination();
}

function renderDemandPagination() {
    const paginationContainer = document.getElementById('demand-pagination');
    const totalPages = Math.ceil(currentDemandItems.length / demandItemsPerPage);
    
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let paginationHTML = `
        <div style="font-size:12px; color:var(--text-secondary);">
            Menampilkan ${(currentDemandPage - 1) * demandItemsPerPage + 1} - ${Math.min(currentDemandPage * demandItemsPerPage, currentDemandItems.length)} dari ${currentDemandItems.length} data
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn btn-sm btn-neutral" onclick="changeDemandPage(${currentDemandPage - 1})" ${currentDemandPage === 1 ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''}>Prev</button>
            <span style="display:flex; align-items:center; font-size:12px; padding:0 8px;">Halaman ${currentDemandPage} dari ${totalPages}</span>
            <button class="btn btn-sm btn-neutral" onclick="changeDemandPage(${currentDemandPage + 1})" ${currentDemandPage === totalPages ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''}>Next</button>
        </div>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
}

function changeDemandPage(newPage) {
    const totalPages = Math.ceil(currentDemandItems.length / demandItemsPerPage);
    if (newPage >= 1 && newPage <= totalPages) {
        currentDemandPage = newPage;
        renderDemandTable();
    }
}

// Functions for Add Bangkom Modal
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

function openAddBangkomModal(competencyName) {
    const modal = document.getElementById('modal-add');
    const modalContent = modal.querySelector('.atlas-modal-content');
    
    document.getElementById('form-kegiatan').reset();

    // Set the chosen competency directly as readonly
    document.getElementById('input-kompetensi-dasar').value = competencyName;

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
</script>
@endpush
@endsection
