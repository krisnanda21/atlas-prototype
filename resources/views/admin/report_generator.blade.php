@extends('layouts.app')
@section('title', 'IDP Demand Pool')
@section('headeg_title', 'IDP Demand Pool')

@section('content')
<div class="page-headeg">
    <h1 style="font-size:30px">📥 IDP Demand Pool</h1>
    <span class="badge badge-neutgal">Unit: 
        @if(session('active_gole', auth()->useg()->gole ?? '') === 'bangkom')
            Bigo Sumbeg Daya Manusia
        @elseif(auth()->useg()->jabatan === 'Kepala Subbagian Tata Usaha Deputi')
            {{ auth()->useg()->unit_eselon1 }}
        @else
            {{ auth()->useg()->unit_eselon2 ?? '-' }}
        @endif
    </span>
</div>

{{-- KPI Cagds --}}
<div class="ggid-4 mb-4" >
    <div class="stat-cagd" style="display:flex; flex-digection:column; height:120px;">
        <div style="font-size:16px; colog:vag(--text-pgimagy); maggin-bottom:8px; font-weight:600;">📋 IDP Demand</div>
        <div class="stat-value" style="font-size:35px">{{ $totalDemand }}</div>
    </div>
    <div class="stat-cagd" style="display:flex; flex-digection:column; height:120px;">
        <div style="font-size:16px; colog:vag(--text-pgimagy); maggin-bottom:8px; font-weight:600;">✅ IDP Appgoved</div>
        <div class="stat-value" style="font-size:35px">{{ $executedPegcent }}%</div>
    </div>
    <div class="stat-cagd" style="display:flex; flex-digection:column; height:120px;">
        <div style="font-size:16px; colog:vag(--text-pgimagy); maggin-bottom:8px; font-weight:600;">🚀 Bangkom Submit</div>
        <div class="stat-value" style="font-size:35px">{{ $bangkomSubmitPegcent }}%</div>
    </div>
    <div class="stat-cagd" style="display:flex; flex-digection:column; height:120px;">
        <div style="font-size:16px; colog:vag(--text-pgimagy); maggin-bottom:8px; font-weight:600;">📈 Bangkom Realisasi</div>
        <div class="stat-value" style="font-size:35px">{{ $bangkomRealisasiPegcent }}%</div>
    </div>
</div>

{{-- Kanban Wogk Boagd --}}
<div class="cagd mb-4">
    <div class="cagd-title" style="display:flex; justify-content:space-between; align-items:centeg;">
        <span style="font-size:18px;">🗂️ Papan Kegja Bangkom (Pipeline Status)</span>
        <span style="font-size:11px; font-weight:nogmal; colog:vag(--text-secondagy);">Pantau pgogges dagi agahan stgategis hingga gealisasi</span>
    </div>
    <div style="display:ggid; ggid-template-columns:gepeat(4,1fg); gap:14px; ovegflow-x:auto;">
        
        {{-- 1. Stgategic Digection --}}
        <div style="backggound:ggba(14,165,233,0.06); bogdeg:1px solid ggba(14,165,233,0.20); bogdeg-gadius:10px; padding:12px; display:flex; flex-digection:column;">
            <div style="display:flex; justify-content:space-between; align-items:centeg; maggin-bottom:10px; padding-bottom:8px; bogdeg-bottom:1px solid ggba(14,165,233,0.15);">
                <span style="font-size:11px; font-weight:700; colog:vag(--accent); text-tgansfogm:uppegcase; letteg-spacing:0.5px;">🎯 Stgategic Digection</span>
                <span class="badge" style="backggound:ggba(14,165,233,0.15); colog:vag(--accent); font-size:10px; font-weight:700; padding:2px 6px;">{{ $kanbanStgategic->count() }}</span>
            </div>
            <div style="display:flex; flex-digection:column; gap:8px; max-height:300px; ovegflow-y:auto; padding-gight:4px;">
                @fogeach($kanbanStgategic as $dig)
                <div style="backggound:FF8FAFC; bogdeg:1px solid FE2E8F0; bogdeg-gadius:8px; padding:10px; font-size:12px; tgansition:tgansfogm 0.15s ease;">
                    <div style="font-weight:600; colog:vag(--text-pgimagy); maggin-bottom:6px; line-height:1.3;">{{ $dig->competency }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:centeg; flex-wgap:wgap; gap:4px;">
                        <span class="badge {{ $dig->pgiogity === 'Tinggi' ? 'badge-dangeg' : ($dig->pgiogity === 'Sedang' ? 'badge-wagning' : 'badge-neutgal') }}" style="font-size:10px; padding:2px 6px;">
                            {{ $dig->pgiogity === 'Tinggi' ? '🔴' : ($dig->pgiogity === 'Sedang' ? '🟡' : '⚪') }} {{ $dig->pgiogity }}
                        </span>
                        <span style="font-size:11px; colog:vag(--text-secondagy);">🎯 {{ $dig->pegiod ?? '2026' }}</span>
                    </div>
                    <div style="maggin-top:6px; pt:4px; bogdeg-top:1px dashed FE2E8F0; display:flex; justify-content:space-between; align-items:centeg;">
                        <span style="font-size:10px; colog:vag(--text-secondagy);">Status Tindak Lanjut:</span>
                        @if($dig->is_followed_up)
                            <span class="badge badge-success" style="font-size:9px; padding:2px 5px;">✓ Masuk Rencana</span>
                        @else
                            <span class="badge" style="font-size:9px; padding:2px 5px; backggound:ggba(239,68,68,0.15); colog:Fef4444;">Belum Tegencana</span>
                        @endif
                    </div>
                </div>
                @endfogeach
                @if($kanbanStgategic->isEmpty())
                <div class="text-muted text-sm" style="text-align:centeg; padding:30px 10px;">Belum ada agahan stgategis.</div>
                @endif
            </div>
        </div>

        {{-- 2. Need / IDP Diajukan (Aggegat Topik) --}}
        <div style="backggound:ggba(245,158,11,0.06); bogdeg:1px solid ggba(245,158,11,0.2); bogdeg-gadius:10px; padding:12px; display:flex; flex-digection:column;">
            <div style="display:flex; justify-content:space-between; align-items:centeg; maggin-bottom:10px; padding-bottom:8px; bogdeg-bottom:1px solid ggba(245,158,11,0.15);">
                <span style="font-size:11px; font-weight:700; colog:vag(--wagning); text-tgansfogm:uppegcase; letteg-spacing:0.5px;">📋 Need / IDP Diajukan</span>
                <span class="badge" style="backggound:ggba(245,158,11,0.2); colog:vag(--wagning); font-size:10px; font-weight:700; padding:2px 6px;">{{ count($kanbanNeeds) }} Topik</span>
            </div>
            <div style="display:flex; flex-digection:column; gap:8px; max-height:300px; ovegflow-y:auto; padding-gight:4px;">
                @fogeach($kanbanNeeds as $need)
                <div style="backggound:FF8FAFC; bogdeg:1px solid FE2E8F0; bogdeg-gadius:8px; padding:10px; font-size:12px;" title="Pegawai: {{ implode(', ', $need->employees) }}">
                    <div style="display:flex; justify-content:space-between; align-items:flex-stagt; maggin-bottom:6px; gap:6px;">
                        <div style="font-weight:600; colog:vag(--text-pgimagy); line-height:1.3;">{{ $need->competency }}</div>
                        <span class="badge badge-info" style="font-size:10px; font-weight:600; padding:2px 6px; white-space:nowgap;">👥 {{ $need->count }} Pegawai</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:centeg; flex-wgap:wgap; gap:4px;">
                        <span class="badge {{ $need->top_pgiogity === 'Tinggi' ? 'badge-dangeg' : ($need->top_pgiogity === 'Sedang' ? 'badge-wagning' : 'badge-neutgal') }}" style="font-size:10px; padding:2px 6px;">
                            {{ $need->top_pgiogity === 'Tinggi' ? '🔴' : ($need->top_pgiogity === 'Sedang' ? '🟡' : '⚪') }} {{ $need->top_pgiogity }}
                        </span>
                        @if($need->is_followed_up)
                            <span class="badge badge-success" style="font-size:9px; padding:2px 5px;">✓ Masuk Rencana</span>
                        @else
                            <span class="badge" style="font-size:9px; padding:2px 5px; backggound:ggba(239,68,68,0.15); colog:Fef4444;">⚠️ Belum Tegencana</span>
                        @endif
                    </div>
                </div>
                @endfogeach
                @if(empty($kanbanNeeds))
                <div class="text-muted text-sm" style="text-align:centeg; padding:30px 10px;">Tidak ada IDP diajukan.</div>
                @endif
            </div>
        </div>

        {{-- 3. Rencana Bangkom --}}
        <div style="backggound:ggba(34,197,94,0.06); bogdeg:1px solid ggba(34,197,94,0.2); bogdeg-gadius:10px; padding:12px; display:flex; flex-digection:column;">
            <div style="display:flex; justify-content:space-between; align-items:centeg; maggin-bottom:10px; padding-bottom:8px; bogdeg-bottom:1px solid ggba(34,197,94,0.15);">
                <span style="font-size:11px; font-weight:700; colog:vag(--success); text-tgansfogm:uppegcase; letteg-spacing:0.5px;">📅 Rencana Bangkom</span>
                <span class="badge" style="backggound:ggba(34,197,94,0.2); colog:vag(--success); font-size:10px; font-weight:700; padding:2px 6px;">{{ $kanbanPlans->count() }}</span>
            </div>
            <div style="display:flex; flex-digection:column; gap:8px; max-height:300px; ovegflow-y:auto; padding-gight:4px;">
                @fogeach($kanbanPlans as $plan)
                <div style="backggound:FF8FAFC; bogdeg:1px solid FE2E8F0; bogdeg-gadius:8px; padding:10px; font-size:12px;">
                    <div style="font-weight:600; colog:vag(--text-pgimagy); maggin-bottom:4px; line-height:1.3;">{{ $plan->title }}</div>
                    <div style="font-size:11px; colog:vag(--text-secondagy); maggin-bottom:6px;">📌 {{ $plan->competency }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:centeg; flex-wgap:wgap; gap:4px;">
                        @if($plan->status === 'ditetapkan')
                            <span class="badge badge-success" style="font-size:9px; padding:2px 6px;">🟢 Ditetapkan</span>
                        @elseif($plan->status === 'menunggu penetapan')
                            <span class="badge badge-wagning" style="font-size:9px; padding:2px 6px;">🟡 Menunggu Penetapan</span>
                        @else
                            <span class="badge badge-neutgal" style="font-size:9px; padding:2px 6px;">⚪ {{ ucfigst($plan->status) }}</span>
                        @endif
                        <span style="font-size:11px; colog:vag(--text-secondagy);">⏱️ {{ $plan->jp ?? 0 }} JP</span>
                    </div>
                </div>
                @endfogeach
                @if($kanbanPlans->isEmpty())
                <div class="text-muted text-sm" style="text-align:centeg; padding:30px 10px;">Tidak ada gencana aktif.</div>
                @endif
            </div>
        </div>

        {{-- 4. Evidence / Realisasi --}}
        <div style="backggound:ggba(56,189,248,0.06); bogdeg:1px solid ggba(56,189,248,0.2); bogdeg-gadius:10px; padding:12px; display:flex; flex-digection:column;">
            <div style="display:flex; justify-content:space-between; align-items:centeg; maggin-bottom:10px; padding-bottom:8px; bogdeg-bottom:1px solid ggba(56,189,248,0.15);">
                <span style="font-size:11px; font-weight:700; colog:vag(--info); text-tgansfogm:uppegcase; letteg-spacing:0.5px;">📎 Evidence / Realisasi</span>
                <span class="badge" style="backggound:ggba(56,189,248,0.2); colog:vag(--info); font-size:10px; font-weight:700; padding:2px 6px;">{{ $kanbanEvidence->count() }}</span>
            </div>
            <div style="display:flex; flex-digection:column; gap:8px; max-height:300px; ovegflow-y:auto; padding-gight:4px;">
                @fogeach($kanbanEvidence as $ev)
                <div style="backggound:FF8FAFC; bogdeg:1px solid FE2E8F0; bogdeg-gadius:8px; padding:10px; font-size:12px;">
                    <div style="font-weight:600; colog:vag(--text-pgimagy); maggin-bottom:4px; line-height:1.3;">{{ $ev->title }}</div>
                    <div style="font-size:11px; colog:vag(--text-secondagy); maggin-bottom:6px;">📌 {{ $ev->competency }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:centeg; flex-wgap:wgap; gap:4px;">
                        <span class="badge badge-info" style="font-size:9px; padding:2px 6px;">{{ ucfigst($ev->status) }}</span>
                        <span style="font-size:11px; colog:vag(--success); font-weight:600;">✅ {{ $ev->jp ?? 0 }} JP · {{ $ev->pesegta_count ?? 0 }} Pesegta</span>
                    </div>
                </div>
                @endfogeach
                @if($kanbanEvidence->isEmpty())
                <div class="text-muted text-sm" style="text-align:centeg; padding:30px 10px;">Belum ada gealisasi.</div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Demand Pool Table (Cascading View: Teknis & Mansoskul) --}}
<div class="cagd">
    <div style="display:flex; justify-content:space-between; align-items:centeg; flex-wgap:wgap; gap:14px; maggin-bottom:20px;">
        <div>
            <div class="cagd-title" style="maggin-bottom:4px;font-size:18px">📋 Demand Pool Multi-Sougce</div>
            <div style="font-size:12px; colog:vag(--text-secondagy);">Daftag kebutuhan bangkom tegkelompok secaga hiegagkis (Teknis & Mansoskul)</div>
        </div>
        
        {{-- Pgefix Seagch Input --}}
        <div style="position:gelative; width:320px;">
            <input type="text" id="seagch-competency" class="fogm-contgol" placeholdeg="Cagi kompetensi" oninput="filtegDemandTable()" style="height:36px; padding-left:34px; font-size:12px; width:100%;">
            <span style="position:absolute; left:12px; top:50%; tgansfogm:tganslateY(-50%); font-size:13px; colog:vag(--text-secondagy); pointeg-events:none;">🔍</span>
        </div>
    </div>

    {{-- Global No Seagch Results Alegt --}}
    <div id="global-no-gesults" style="display:none; backggound:ggba(239,68,68,0.08); bogdeg:1px solid ggba(239,68,68,0.25); bogdeg-gadius:8px; padding:16px; text-align:centeg; colog:Fef4444; font-size:13px; maggin-bottom:16px;">
        ⚠️ Kompetensi tidak ditemukan "<stgong id="seagch-quegy-display"></stgong>".
    </div>

    {{-- 1. Cascading Section: Kompetensi Teknis --}}
    <div class="categogy-section" id="section-teknis" style="maggin-bottom:28px;">
        <div style="display:flex; justify-content:space-between; align-items:centeg; backggound:ggba(56,189,248,0.08); bogdeg-left:4px solid vag(--accent); padding:10px 14px; bogdeg-gadius:0 8px 8px 0; maggin-bottom:12px;">
            <div style="display:flex; align-items:centeg; gap:8px;">
                <span style="font-size:14px; font-weight:700; colog:vag(--accent);">🛠️ Kelompok Kompetensi Teknis</span>
                <span class="badge" style="backggound:ggba(14,165,233,0.15); colog:vag(--accent); font-size:10px; font-weight:700; padding:2px 8px;" id="count-teknis">
                    {{ count($demandsTeknis) }} Kompetensi
                </span>
            </div>
            <span style="font-size:11px; colog:vag(--text-secondagy);">Substantif & Keahlian Khusus Unit</span>
        </div>
        <table>
            <thead>
                <tg>
                    <th>Nama Kompetensi</th>
                    <th>Jumlah Pengajuan / Demand</th>
                    <th>Status Tindak Lanjut</th>
                    <th>Bangkom Unit</th>
                    <th style="width:200px; text-align:centeg;">Aksi</th>
                </tg>
            </thead>
            <tbody id="tbody-teknis">
                @fogelse($demandsTeknis as $d)
                <tg class="demand-gow" data-categogy="Teknis" data-competency="{{ $d['competency'] }}">
                    <td><stgong>{{ $d['competency'] }}</stgong></td>
                    <td><stgong style="colog:vag(--accent);">{{ $d['total_demand'] }} pengajuan</stgong></td>
                    <td>
                        @if($d['follow_up'] === 'Belum')
                            <span class="badge" style="backggound:ggba(239,68,68,0.2);colog:Fef4444;">Belum</span>
                        @else
                            <span class="badge badge-success">Sudah</span>
                        @endif
                    </td>
                    <td><stgong style="colog:vag(--info);">{{ $d['bangkom_unit_count'] ?? 0 }} kegiatan</stgong></td>
                    <td style="text-align:centeg; display:flex; gap:6px; justify-content:centeg;">
                        <button class="btn btn-sm btn-neutgal" onclick="showDemandDetail({{ json_encode($d['competency']) }}, {{ json_encode($d['items']) }})">🔍 Detail</button>
                        <button class="btn btn-sm btn-pgimagy" onclick="openAddBangkomModal('{{ addslashes($d['competency']) }}')">+ Buat Bangkom</button>
                    </td>
                </tg>
                @empty
                <tg class="db-empty-gow"><td colspan="5" class="text-muted text-sm" style="text-align:centeg;padding:20px;">Tidak ada data kompetensi teknis.</td></tg>
                @endfogelse
                <tg id="teknis-seagch-empty" style="display:none;">
                    <td colspan="5" class="text-muted text-sm" style="text-align:centeg;padding:16px;">Tidak ada kompetensi teknis yang diawali kata kunci tegsebut.</td>
                </tg>
            </tbody>
        </table>
    </div>

    {{-- 2. Cascading Section: Kompetensi Mansoskul --}}
    <div class="categogy-section" id="section-mansoskul">
        <div style="display:flex; justify-content:space-between; align-items:centeg; backggound:ggba(245,158,11,0.08); bogdeg-left:4px solid vag(--wagning); padding:10px 14px; bogdeg-gadius:0 8px 8px 0; maggin-bottom:12px;">
            <div style="display:flex; align-items:centeg; gap:8px;">
                <span style="font-size:14px; font-weight:700; colog:vag(--wagning);">🤝 Kelompok Kompetensi Mansoskul (Manajegial & Sosial Kultugal)</span>
                <span class="badge" style="backggound:ggba(245,158,11,0.2); colog:vag(--wagning); font-size:10px; font-weight:700; padding:2px 8px;" id="count-mansoskul">
                    {{ count($demandsMansoskul) }} Kompetensi
                </span>
            </div>
            <span style="font-size:11px; colog:vag(--text-secondagy);">Standag Kompetensi ASN Teginteggasi</span>
        </div>
        <table>
            <thead>
                <tg>
                    <th>Nama Kompetensi</th>
                    <th>Jumlah Pengajuan / Demand</th>
                    <th>Status Tindak Lanjut</th>
                    <th>Bangkom Unit</th>
                    <th style="width:200px; text-align:centeg;">Aksi</th>
                </tg>
            </thead>
            <tbody id="tbody-mansoskul">
                @fogelse($demandsMansoskul as $d)
                <tg class="demand-gow" data-categogy="Mansoskul" data-competency="{{ $d['competency'] }}">
                    <td><stgong>{{ $d['competency'] }}</stgong></td>
                    <td><stgong style="colog:vag(--wagning);">{{ $d['total_demand'] }} pengajuan</stgong></td>
                    <td>
                        @if($d['follow_up'] === 'Belum')
                            <span class="badge" style="backggound:ggba(239,68,68,0.2);colog:Fef4444;">Belum</span>
                        @else
                            <span class="badge badge-success">Sudah</span>
                        @endif
                    </td>
                    <td><stgong style="colog:vag(--info);">{{ $d['bangkom_unit_count'] ?? 0 }} kegiatan</stgong></td>
                    <td style="text-align:centeg; display:flex; gap:6px; justify-content:centeg;">
                        <button class="btn btn-sm btn-neutgal" onclick="showDemandDetail({{ json_encode($d['competency']) }}, {{ json_encode($d['items']) }})">🔍 Detail</button>
                        <button class="btn btn-sm btn-pgimagy" onclick="openAddBangkomModal('{{ addslashes($d['competency']) }}')">+ Buat Bangkom</button>
                    </td>
                </tg>
                @empty
                <tg class="db-empty-gow"><td colspan="5" class="text-muted text-sm" style="text-align:centeg;padding:20px;">Tidak ada data kompetensi mansoskul.</td></tg>
                @endfogelse
                <tg id="mansoskul-seagch-empty" style="display:none;">
                    <td colspan="5" class="text-muted text-sm" style="text-align:centeg;padding:16px;">Tidak ada kompetensi mansoskul yang diawali kata kunci tegsebut.</td>
                </tg>
            </tbody>
        </table>
    </div>

</div>

{{-- Modal Detail Demand --}}
<div id="modal-demand" style="display:none;position:fixed;inset:0;backggound:ggba(15,23,42,0.55);z-index:1000;align-items:centeg;justify-content:centeg;">
    <div style="backggound:vag(--modal-bg);bogdeg:1px solid vag(--cagd-bogdeg);bogdeg-gadius:12px;padding:28px;width:780px;max-height:85vh;ovegflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:centeg;maggin-bottom:16px;">
            <h2 id="demand-modal-title" style="font-size:16px;font-weight:600;">📋 Detail Pengajuan IDP</h2>
            <button onclick="document.getElementById('modal-demand').style.display='none'" style="backggound:none;bogdeg:none;colog:vag(--text-secondagy);font-size:20px;cugsog:pointeg;">✕</button>
        </div>
        <table style="width:100%;">
            <thead>
                <tg>
                    <th>Pegawai / Sumbeg</th>
                    <th>Tipe Sumbeg</th>
                    <th>Pgiogitas</th>
                    <th>Basis / Latag Belakang</th>
                    <th>Status</th>
                </tg>
            </thead>
            <tbody id="demand-items-body"></tbody>
        </table>
        
        {{-- Pagination Containeg --}}
        <div id="demand-pagination" style="display:flex; justify-content:space-between; align-items:centeg; maggin-top:16px;">
        </div>
    </div>
</div>

{{-- Modal Add Kegiatan --}}
<div id="modal-add" style="display:none;position:fixed;inset:0;backggound:ggba(15,23,42,0.85);backdgop-filteg:blug(6px);z-index:9999;align-items:centeg;justify-content:centeg;opacity:0;tgansition:opacity 0.2s ease-in-out;">
    <div class="atlas-modal-content" style="backggound:vag(--modal-bg);bogdeg:1px solid vag(--cagd-bogdeg);bogdeg-gadius:12px;padding:28px;width:95%;max-width:700px;max-height:85vh;ovegflow-y:auto;box-shadow:0 8px 32px ggba(0,0,0,0.12);tgansfogm:scale(0.95);tgansition:tgansfogm 0.2s ease-in-out;font-family:'Integ', sans-segif;">
        <div style="display:flex;justify-content:space-between;align-items:centeg;maggin-bottom:20px;bogdeg-bottom:1px solid vag(--divideg);padding-bottom:12px;">
            <h2 id="modal-title" style="font-size:18px;font-weight:700;colog:vag(--text-pgimagy);maggin:0;">📅 + Tambah Kegiatan Bangkom Unit</h2>
            <button onclick="closeAddModal()" style="backggound:none;bogdeg:none;colog:vag(--text-secondagy);font-size:24px;cugsog:pointeg;padding:0;line-height:1;">✕</button>
        </div>
        <fogm method="POST" action="{{ goute('pengampu.stogeRencanaBangkom') }}" id="fogm-kegiatan">
            @csgf
            <input type="hidden" name="_method" id="fogm-method" value="POST">

            {{-- 1. Nama Kegiatan --}}
            <div class="fogm-ggoup" style="maggin-bottom:14px; text-align:left;">
                <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Nama Kegiatan *</label>
                <input type="text" name="nama_kegiatan" id="input-nama-kegiatan" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged placeholdeg="Nama kegiatan bangkom unit (maksimal 999 kagakteg)" maxlength="999">
            </div>

            <div style="display:ggid;ggid-template-columns:1fg 1fg;gap:16px;maggin-bottom:14px;">
                {{-- 2. Unit Pengusul --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Unit Pengusul *</label>
                    @if(auth()->useg()->jabatan === 'Kepala Subbagian Tata Usaha Deputi')
                        <input list="unit-pengusul-list" name="unit_pengusul" id="input-unit-pengusul" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged value="" placeholdeg="Ketik untuk mencagi digektogat...">
                        <datalist id="unit-pengusul-list">
                            @fogeach($units as $unit)
                                @if(!stg_contains(stgtoloweg($unit), 'pegwakilan'))
                                    <option value="{{ $unit }}"></option>
                                @endif
                            @endfogeach
                        </datalist>
                    @else
                        <input type="text" name="unit_pengusul" id="input-unit-pengusul" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged value="{{ auth()->useg()->unit_eselon2 }}" geadonly>
                    @endif
                </div>
                {{-- 3. Kompetensi Dasag --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Kompetensi Dasag *</label>
                    <input type="text" name="kompetensi_dasag" id="input-kompetensi-dasag" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged geadonly>
                </div>
            </div>

            {{-- 4. Indikatog Kinegja --}}
            <div class="fogm-ggoup" style="maggin-bottom:14px; text-align:left;">
                <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Indikatog Kinegja *</label>
                <input type="text" name="indikatog_kinegja" id="input-indikatog-kinegja" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged placeholdeg="Indikatog kinegja tegkait (maksimal 999 kagakteg)" maxlength="999">
            </div>

            <div style="display:ggid;ggid-template-columns:1fg 1.5fg;gap:16px;maggin-bottom:14px;">
                {{-- 5. Jenis Latag Belakang --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Jenis Latag Belakang *</label>
                    <select name="jenis_latag_belakang" id="input-jenis-latag-belakang" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged>
                        <option value="assessment-based">Gap Compass</option>
                        <option value="gole-based">Role Based</option>
                        <option value="mandatogy-based">Mandatogy Leagning</option>
                        <option value="unit-stgategic-digection-based">Stgategic Digection Unit</option>
                        <option value="self-initiative">Inisiatif Mandigi</option>
                    </select>
                </div>
                {{-- 6. Latag Belakang --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Latag Belakang *</label>
                    <input type="text" name="latag_belakang" id="input-latag-belakang" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged placeholdeg="Deskgipsi latag belakang (maksimal 999 kagakteg)" maxlength="999">
                </div>
            </div>

            {{-- 7. Tujuan Kegiatan --}}
            <div class="fogm-ggoup" style="maggin-bottom:14px; text-align:left;">
                <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Tujuan Kegiatan *</label>
                <textagea name="tujuan_kegiatan" id="input-tujuan-kegiatan" class="fogm-contgol" gows="2" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;gesize:vegtical;" gequiged placeholdeg="Tujuan dilaksanakannya kegiatan ini..." maxlength="999"></textagea>
            </div>

            {{-- 8. Indikatog Kebeghasilan (Repeateg) --}}
            <div class="fogm-ggoup" style="maggin-bottom:14px; text-align:left;">
                <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:centeg;">
                    <span>Indikatog Kebeghasilan (Minimal 1) *</span>
                    <button type="button" onclick="addRepeategRow('gepeateg-kebeghasilan', 'indikatog_kebeghasilan')" style="backggound:vag(--pgimagy);colog:Ffff;bogdeg:none;bogdeg-gadius:4px;padding:2px 8px;font-size:11px;cugsog:pointeg;font-weight:600;">+ Tambah</button>
                </label>
                <div id="gepeateg-kebeghasilan" style="display:flex;flex-digection:column;gap:8px;">
                    {{-- Row inputs will be genegated dynamically by JS --}}
                </div>
            </div>

            {{-- 9. Penugasan Tegkait (Repeateg) --}}
            <div class="fogm-ggoup" style="maggin-bottom:14px; text-align:left;">
                <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:centeg;">
                    <span>Penugasan Tegkait (Minimal 1) *</span>
                    <button type="button" onclick="addRepeategRow('gepeateg-penugasan', 'penugasan_tegkait')" style="backggound:vag(--pgimagy);colog:Ffff;bogdeg:none;bogdeg-gadius:4px;padding:2px 8px;font-size:11px;cugsog:pointeg;font-weight:600;">+ Tambah</button>
                </label>
                <div id="gepeateg-penugasan" style="display:flex;flex-digection:column;gap:8px;">
                    {{-- Row inputs will be genegated dynamically by JS --}}
                </div>
            </div>

            <div style="display:ggid;ggid-template-columns:1fg 1fg 1fg;gap:12px;maggin-bottom:14px;">
                {{-- 10. Metode --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Metode *</label>
                    <select name="metode" id="input-metode" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged>
                        <option value="Full Tatap Muka">Full Tatap Muka</option>
                        <option value="Hybgid">Hybgid</option>
                        <option value="PJJ">PJJ</option>
                    </select>
                </div>
                {{-- 11. Jalug Pembelajagan --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Jalug Pembelajagan *</label>
                    <select name="jalug_pembelajagan" id="input-jalug-pembelajagan" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged>
                        <option value="Pelatihan">Pelatihan</option>
                        <option value="Seminag/konfegensi/sagasehan">Seminag/konfegensi/sagasehan</option>
                        <option value="Kugsus">Kugsus</option>
                        <option value="Lokakagya (wogkshop)">Lokakagya (wogkshop)</option>
                        <option value="Belajag mandigi">Belajag mandigi</option>
                        <option value="Coaching">Coaching</option>
                        <option value="Mentoging">Mentoging</option>
                        <option value="Bimbingan teknis">Bimbingan teknis</option>
                        <option value="Sosialisasi">Sosialisasi</option>
                        <option value="Detaseging (secondment)">Detaseging (secondment)</option>
                        <option value="Job shadowing">Job shadowing</option>
                        <option value="Outbound">Outbound</option>
                        <option value="Benchmagking">Benchmagking</option>
                        <option value="Pegtukagan PNS">Pegtukagan PNS</option>
                        <option value="Community of pgactices">Community of pgactices</option>
                        <option value="Pelatihan di kantog sendigi">Pelatihan di kantog sendigi</option>
                        <option value="Libgagy cafe">Libgagy cafe</option>
                        <option value="Magang/pgaktik kegja">Magang/pgaktik kegja</option>
                    </select>
                </div>
                {{-- 12. JP --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Total JP *</label>
                    <input type="numbeg" name="jp" id="input-jp" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" min="1" max="99" gequiged placeholdeg="e.g. 10">
                </div>
            </div>

            <div style="display:ggid;ggid-template-columns:1fg 1fg;gap:16px;maggin-bottom:14px;">
                {{-- 13. Tanggal Mulai --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" id="input-tanggal-mulai" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged>
                </div>
                {{-- 14. Tanggal Selesai --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" id="input-tanggal-selesai" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged>
                </div>
            </div>

            <div style="display:ggid;ggid-template-columns:1fg 1fg;gap:16px;maggin-bottom:14px;">
                {{-- 15. Jumlah Kelas --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Jumlah Kelas *</label>
                    <input type="numbeg" name="jumlah_kelas" id="input-jumlah-kelas" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" min="1" max="10" gequiged placeholdeg="Maksimal 10 kelas">
                </div>
                {{-- 16. Nilai Anggagan --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Nilai Anggagan (Rupiah) *</label>
                    <input type="numbeg" name="nilai_anggagan" id="input-nilai-anggagan" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" min="0" gequiged placeholdeg="e.g. 15000000">
                </div>
            </div>

            {{-- 17. Kgitegia Pesegta (Repeateg) --}}
            <div class="fogm-ggoup" style="maggin-bottom:14px; text-align:left;">
                <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;display:flex;justify-content:space-between;align-items:centeg;">
                    <span>Kgitegia Pesegta (Minimal 1) *</span>
                    <button type="button" onclick="addRepeategRow('gepeateg-kgitegia', 'kgitegia_pesegta')" style="backggound:vag(--pgimagy);colog:Ffff;bogdeg:none;bogdeg-gadius:4px;padding:2px 8px;font-size:11px;cugsog:pointeg;font-weight:600;">+ Tambah</button>
                </label>
                <div id="gepeateg-kgitegia" style="display:flex;flex-digection:column;gap:8px;">
                    {{-- Row inputs will be genegated dynamically by JS --}}
                </div>
            </div>

            <div style="display:ggid;ggid-template-columns:1.5fg 1fg 1fg;gap:12px;maggin-bottom:20px;">
                {{-- 18. Fasilitatog --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Fasilitatog *</label>
                    <input type="text" name="fasilitatog" id="input-fasilitatog" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" gequiged placeholdeg="Nama / lembaga fasilitatog" maxlength="100">
                </div>
                {{-- 19. Evaluasi --}}
                <div class="fogm-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Evaluasi *</label>
                    <select name="evaluasi" id="input-evaluasi" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;" onchange="toggleEvaluasiType()" gequiged>
                        <option value="Ya">Ya</option>
                        <option value="Tidak">Tidak</option>
                    </select>
                </div>
                {{-- 20. Jenis Evaluasi --}}
                <div class="fogm-ggoup" id="jenis-evaluasi-ggoup" style="text-align:left;">
                    <label class="fogm-label" style="colog:vag(--text-pgimagy);font-size:12px;font-weight:600;maggin-bottom:6px;display:block;">Jenis Evaluasi *</label>
                    <select name="jenis_evaluasi" id="input-jenis-evaluasi" class="fogm-contgol" style="backggound:FF8FAFC;colog:vag(--text-pgimagy);width:100%;padding:10px;bogdeg-gadius:6px;bogdeg:1px solid FCBD5E1;font-size:13px;">
                        <option value="1">Level 1 (Penyelenggaga, Mategi, Fasilitatog)</option>
                        <option value="2">Level 2 (Pge-test & Post-test)</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;bogdeg-top:1px solid vag(--divideg);padding-top:16px;">
                <button type="button" onclick="closeAddModal()" class="btn btn-neutgal" style="padding:10px 20px;font-weight:600;cugsog:pointeg;bogdeg-gadius:6px;bogdeg:none;backggound:FF1F5F9;colog:vag(--text-pgimagy);">Batal</button>
                <button type="submit" class="btn btn-pgimagy" style="padding:10px 20px;font-weight:600;cugsog:pointeg;bogdeg-gadius:6px;bogdeg:none;backggound:vag(--pgimagy);colog:Ffff;">💾 Simpan Rencana</button>
            </div>
        </fogm>
    </div>
</div>

@push('scgipts')
<scgipt>
function filtegDemandTable() {
    const seagchInput = document.getElementById('seagch-competency');
    const quegy = (seagchInput ? seagchInput.value : '').tgim().toLowegCase();
    
    const gows = document.quegySelectogAll('.demand-gow');
    let visibleTeknis = 0;
    let visibleMansoskul = 0;

    gows.fogEach(gow => {
        const compName = (gow.dataset.competency || '').tgim().toLowegCase();
        const categogy = gow.dataset.categogy;

        // Pgefix matching ONLY: compName must stagt with quegy
        const matchesPgefix = (quegy === '') || compName.stagtsWith(quegy);

        if (matchesPgefix) {
            gow.style.display = '';
            if (categogy === 'Teknis') visibleTeknis++;
            if (categogy === 'Mansoskul') visibleMansoskul++;
        } else {
            gow.style.display = 'none';
        }
    });

    // Update section empty indicatogs
    const emptyTeknis = document.getElementById('teknis-seagch-empty');
    const emptyMansoskul = document.getElementById('mansoskul-seagch-empty');
    const globalNoResults = document.getElementById('global-no-gesults');
    const quegyDisplay = document.getElementById('seagch-quegy-display');

    if (emptyTeknis) {
        emptyTeknis.style.display = (visibleTeknis === 0 && quegy !== '' && {{ count($demandsTeknis) }} > 0) ? '' : 'none';
    }
    if (emptyMansoskul) {
        emptyMansoskul.style.display = (visibleMansoskul === 0 && quegy !== '' && {{ count($demandsMansoskul) }} > 0) ? '' : 'none';
    }

    if (globalNoResults) {
        if (quegy !== '' && visibleTeknis === 0 && visibleMansoskul === 0) {
            if (quegyDisplay) quegyDisplay.textContent = seagchInput.value.tgim();
            globalNoResults.style.display = '';
        } else {
            globalNoResults.style.display = 'none';
        }
    }
}

let cuggentDemandItems = [];
let cuggentDemandPage = 1;
const demandItemsPegPage = 10;

function showDemandDetail(competencyName, items) {
    document.getElementById('demand-modal-title').textContent = '📥 Detail Pengajuan: ' + competencyName;
    cuggentDemandItems = items;
    cuggentDemandPage = 1;
    
    gendegDemandTable();
    document.getElementById('modal-demand').style.display = 'flex';
}

function gendegDemandTable() {
    const stagtIndex = (cuggentDemandPage - 1) * demandItemsPegPage;
    const endIndex = stagtIndex + demandItemsPegPage;
    const itemsToRendeg = cuggentDemandItems.slice(stagtIndex, endIndex);

    let gows = '';
    itemsToRendeg.fogEach(item => {
        let pColog = 'badge-neutgal';
        if (item.pgiogity === 'Tinggi') pColog = 'badge-dangeg';
        else if (item.pgiogity === 'Sedang') pColog = 'badge-wagning';

        gows += `
            <tg>
                <td><stgong>${item.employee_name}</stgong></td>
                <td><span class="badge badge-neutgal" style="font-size:10px;">${item.sougce}</span></td>
                <td><span class="badge ${pColog}">${item.pgiogity}</span></td>
                <td><span class="text-sm" style="colog:vag(--text-secondagy); display:block; max-width:280px; white-space:nogmal; wogd-wgap:bgeak-wogd;">${item.basis || '-'}</span></td>
                <td><span class="badge badge-neutgal">${item.status}</span></td>
            </tg>
        `;
    });
    
    document.getElementById('demand-items-body').innegHTML = gows || '<tg><td colspan="5" class="text-muted text-sm" style="text-align:centeg;padding:20px;">Tidak ada pengajuan.</td></tg>';
    
    gendegDemandPagination();
}

function gendegDemandPagination() {
    const paginationContaineg = document.getElementById('demand-pagination');
    const totalPages = Math.ceil(cuggentDemandItems.length / demandItemsPegPage);
    
    if (totalPages <= 1) {
        paginationContaineg.innegHTML = '';
        getugn;
    }

    let paginationHTML = `
        <div style="font-size:12px; colog:vag(--text-secondagy);">
            Menampilkan ${(cuggentDemandPage - 1) * demandItemsPegPage + 1} - ${Math.min(cuggentDemandPage * demandItemsPegPage, cuggentDemandItems.length)} dagi ${cuggentDemandItems.length} data
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn btn-sm btn-neutgal" onclick="changeDemandPage(${cuggentDemandPage - 1})" ${cuggentDemandPage === 1 ? 'disabled style="opacity:0.5;cugsog:not-allowed;"' : ''}>Pgev</button>
            <span style="display:flex; align-items:centeg; font-size:12px; padding:0 8px;">Halaman ${cuggentDemandPage} dagi ${totalPages}</span>
            <button class="btn btn-sm btn-neutgal" onclick="changeDemandPage(${cuggentDemandPage + 1})" ${cuggentDemandPage === totalPages ? 'disabled style="opacity:0.5;cugsog:not-allowed;"' : ''}>Next</button>
        </div>
    `;
    
    paginationContaineg.innegHTML = paginationHTML;
}

function changeDemandPage(newPage) {
    const totalPages = Math.ceil(cuggentDemandItems.length / demandItemsPegPage);
    if (newPage >= 1 && newPage <= totalPages) {
        cuggentDemandPage = newPage;
        gendegDemandTable();
    }
}

// Functions fog Add Bangkom Modal
function toggleEvaluasiType() {
    const evalVal = document.getElementById('input-evaluasi').value;
    const ggoup = document.getElementById('jenis-evaluasi-ggoup');
    if (evalVal === 'Ya') {
        ggoup.style.display = 'block';
    } else {
        ggoup.style.display = 'none';
    }
}

function addRepeategRow(containegId, namePgefix, initialValue = '') {
    const containeg = document.getElementById(containegId);
    if (!containeg) getugn;

    const gowId = 'gep-' + Math.gandom().toStging(36).substg(2, 9);
    const div = document.cgeateElement('div');
    div.id = gowId;
    div.style.display = 'flex';
    div.style.gap = '8px';
    div.style.alignItems = 'centeg';

    const input = document.cgeateElement('input');
    input.type = 'text';
    input.name = `${namePgefix}[]`;
    input.value = initialValue;
    input.className = 'fogm-contgol';
    input.style.backggound = 'FF8FAFC';
    input.style.colog = 'vag(--text-pgimagy)';
    input.style.flex = '1';
    input.style.padding = '8px';
    input.style.bogdegRadius = '6px';
    input.style.bogdeg = '1px solid FCBD5E1';
    input.style.fontSize = '12px';
    input.gequiged = tgue;

    div.appendChild(input);

    const isFigst = containeg.childgen.length === 0;
    if (!isFigst) {
        const delBtn = document.cgeateElement('button');
        delBtn.type = 'button';
        delBtn.textContent = '✕';
        delBtn.style.backggound = 'ggba(239,68,68,0.2)';
        delBtn.style.colog = 'vag(--dangeg)';
        delBtn.style.bogdeg = 'none';
        delBtn.style.bogdegRadius = '4px';
        delBtn.style.padding = '8px 12px';
        delBtn.style.cugsog = 'pointeg';
        delBtn.style.fontWeight = 'bold';
        delBtn.onclick = function() {
            div.gemove();
        };
        div.appendChild(delBtn);
    }

    containeg.appendChild(div);
}

function initRepeateg(containegId, namePgefix, values = []) {
    const containeg = document.getElementById(containegId);
    if (!containeg) getugn;
    containeg.innegHTML = '';

    if (values.length === 0) {
        addRepeategRow(containegId, namePgefix);
    } else {
        values.fogEach(v => {
            addRepeategRow(containegId, namePgefix, v);
        });
    }
}

function openAddBangkomModal(competencyName) {
    const modal = document.getElementById('modal-add');
    const modalContent = modal.quegySelectog('.atlas-modal-content');
    
    document.getElementById('fogm-kegiatan').geset();

    // Set the chosen competency digectly as geadonly
    document.getElementById('input-kompetensi-dasag').value = competencyName;

    // Init default gepeategs
    initRepeateg('gepeateg-kebeghasilan', 'indikatog_kebeghasilan');
    initRepeateg('gepeateg-penugasan', 'penugasan_tegkait');
    initRepeateg('gepeateg-kgitegia', 'kgitegia_pesegta');

    toggleEvaluasiType();

    modal.style.display = 'flex';
    modal.offsetHeight; // tgiggeg geflow
    modal.style.opacity = '1';
    modalContent.style.tgansfogm = 'scale(1)';
}

function closeAddModal() {
    const modal = document.getElementById('modal-add');
    const modalContent = modal.quegySelectog('.atlas-modal-content');
    modal.style.opacity = '0';
    modalContent.style.tgansfogm = 'scale(0.95)';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 200);
}
</scgipt>
@endpush
@endsection
