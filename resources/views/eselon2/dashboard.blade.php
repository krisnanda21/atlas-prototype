@extends('layouts.app')
@section('title', 'Dashboard Kepala Unit')
@section('header_title', 'Dashboard Kepala Unit')

@section('content')
<div class="page-header">
    <h1>🏠 Dashboard Kepala Unit</h1>
    <span class="badge badge-neutral">Unit: {{ auth()->user()->scope }}</span>
</div>

{{-- KPI Cards --}}
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="font-size:14px; font-weight:bold; margin-bottom:10px;">🎯 IDP Coverage</div>
        <div class="stat-value" style="color:{{ $idpCoverage >= 75 ? 'var(--success)' : ($idpCoverage >= 50 ? 'var(--warning)' : 'var(--danger)') }};">{{ $idpCoverage }}%</div>
        <div class="stat-label" style="font-size:11px; white-space:normal; line-height:1.4; margin-top:8px;">Seberapa besar gap kompetensi pegawai yang sudah ditutup melalui Individual Development Plan (IDP)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="font-size:14px; font-weight:bold; margin-bottom:10px;">🧠 Rata-Rata Nilai Teknis</div>
        <div class="stat-value" style="color:var(--info);">{{ $rataNilaiTeknis }}</div>
        <div class="stat-label" style="font-size:11px; white-space:normal; line-height:1.4; margin-top:8px;">Nilai rata-rata hasil penilaian kompetensi teknis pegawai di {{ auth()->user()->unit_eselon2 ?? auth()->user()->scope }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="font-size:14px; font-weight:bold; margin-bottom:10px;">📉 Rata-rata Gap Kompetensi</div>
        <div class="stat-value" style="color:var(--danger);">{{ $rataGapTeknis }}</div>
        <div class="stat-label" style="font-size:11px; white-space:normal; line-height:1.4; margin-top:8px;">Rata-rata gap kompetensi teknis pegawai di {{ auth()->user()->unit_eselon2 ?? auth()->user()->scope }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="font-size:14px; font-weight:bold; margin-bottom:10px;">📚 Jumlah Diklat Diusulkan</div>
        <div class="stat-value" style="color:var(--success);">{{ $usulanDiklat }}</div>
        <div class="stat-label" style="font-size:11px; white-space:normal; line-height:1.4; margin-top:8px;">Jumlah Kegiatan Diklat yang diusulkan oleh {{ auth()->user()->unit_eselon2 ?? auth()->user()->scope }}</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    {{-- Left Column --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        
        {{-- Pie Chart JP --}}
        @php
            $totalPegawai = $jpRendah + $jpCukup + $jpTinggi;
            $pctRendah = $totalPegawai > 0 ? round(($jpRendah / $totalPegawai) * 100) : 0;
            $pctCukup = $totalPegawai > 0 ? round(($jpCukup / $totalPegawai) * 100) : 0;
            $pctTinggi = $totalPegawai > 0 ? round(($jpTinggi / $totalPegawai) * 100) : 0;
            
            // For conic gradient: Tinggi (Success), Cukup (Warning), Rendah (Danger)
            $degTinggi = ($pctTinggi / 100) * 360;
            $degCukup = ($pctCukup / 100) * 360;
            $degRendah = ($pctRendah / 100) * 360;

            // Positioning calculations for data labels (call-out outside the chart)
            $radius = 140; // Placed outside the 110px chart radius
            $center = 110;
            
            $midTinggi = $degTinggi / 2;
            $radTinggi = deg2rad($midTinggi - 90);
            $xTinggi = $center + $radius * cos($radTinggi);
            $yTinggi = $center + $radius * sin($radTinggi);
            
            $midCukup = $degTinggi + ($degCukup / 2);
            $radCukup = deg2rad($midCukup - 90);
            $xCukup = $center + $radius * cos($radCukup);
            $yCukup = $center + $radius * sin($radCukup);
            
            $midRendah = $degTinggi + $degCukup + ($degRendah / 2);
            $radRendah = deg2rad($midRendah - 90);
            $xRendah = $center + $radius * cos($radRendah);
            $yRendah = $center + $radius * sin($radRendah);
        @endphp
        <div class="card" style="padding:24px; display:flex; flex-direction:column; align-items:center; overflow:visible;">
            <div class="card-title" style="align-self:flex-start; margin-bottom:16px;">📊 Pemenuhan JP Pegawai 2026</div>
            @if($totalPegawai > 0)
                <div style="position:relative; width:220px; height:220px; border-radius:50%; margin-top:20px; margin-bottom:34px; background: conic-gradient(
                    var(--success) 0deg {{ $degTinggi }}deg,
                    var(--warning) {{ $degTinggi }}deg {{ $degTinggi + $degCukup }}deg,
                    var(--danger) {{ $degTinggi + $degCukup }}deg 360deg
                );">
                    @if($jpTinggi > 0)
                        <div style="position:absolute; top:{{ $yTinggi }}px; left:{{ $xTinggi }}px; transform:translate(-50%, -50%); background:var(--bg-panel, #1e1e1e); color:var(--text-primary); border:1px solid var(--success); padding:4px 8px; border-radius:4px; font-weight:bold; font-size:12px; white-space:nowrap; box-shadow:0 2px 4px rgba(0,0,0,0.2);">{{ $pctTinggi }}%</div>
                    @endif
                    @if($jpCukup > 0)
                        <div style="position:absolute; top:{{ $yCukup }}px; left:{{ $xCukup }}px; transform:translate(-50%, -50%); background:var(--bg-panel, #1e1e1e); color:var(--text-primary); border:1px solid var(--warning); padding:4px 8px; border-radius:4px; font-weight:bold; font-size:12px; white-space:nowrap; box-shadow:0 2px 4px rgba(0,0,0,0.2);">{{ $pctCukup }}%</div>
                    @endif
                    @if($jpRendah > 0)
                        <div style="position:absolute; top:{{ $yRendah }}px; left:{{ $xRendah }}px; transform:translate(-50%, -50%); background:var(--bg-panel, #1e1e1e); color:var(--text-primary); border:1px solid var(--danger); padding:4px 8px; border-radius:4px; font-weight:bold; font-size:12px; white-space:nowrap; box-shadow:0 2px 4px rgba(0,0,0,0.2);">{{ $pctRendah }}%</div>
                    @endif
                </div>
                <div style="display:flex; justify-content:center; gap:20px; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-secondary);">
                        <span style="display:inline-block; width:12px; height:12px; background:var(--success); border-radius:3px;"></span> Tinggi (>= 50 JP)
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-secondary);">
                        <span style="display:inline-block; width:12px; height:12px; background:var(--warning); border-radius:3px;"></span> Cukup (40-49 JP)
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-secondary);">
                        <span style="display:inline-block; width:12px; height:12px; background:var(--danger); border-radius:3px;"></span> Rendah (< 40 JP)
                    </div>
                </div>
            @else
                <div style="text-align:center; padding:40px; color:var(--text-secondary);">Belum ada data pegawai</div>
            @endif
        </div>

        {{-- Card Rekap Rencana & Realisasi --}}
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between; padding:24px;">
            <div>
                <div class="card-title">📅 Rekap Rencana & Realisasi Kegiatan Unit</div>
                @php
                    $totalKegiatan = array_sum($planSummary);
                    $cDraft = $planSummary['draft'] ?? 0;
                    $cMenunggu = $planSummary['menunggu penetapan'] ?? 0;
                    $cDitetapkan = $planSummary['ditetapkan'] ?? 0;
                    $cRealisasi = ($planSummary['realisasi'] ?? 0) + ($planSummary['selesai'] ?? 0) + ($planSummary['dievaluasi'] ?? 0) + ($planSummary['persetujuan realisasi'] ?? 0) + ($planSummary['realisasi disetujui'] ?? 0);

                    $pDraft = $totalKegiatan > 0 ? round(($cDraft / $totalKegiatan) * 100) : 0;
                    $pMenunggu = $totalKegiatan > 0 ? round(($cMenunggu / $totalKegiatan) * 100) : 0;
                    $pDitetapkan = $totalKegiatan > 0 ? round(($cDitetapkan / $totalKegiatan) * 100) : 0;
                    $pRealisasi = $totalKegiatan > 0 ? round(($cRealisasi / $totalKegiatan) * 100) : 0;
                @endphp
                <div style="display:flex; flex-direction:column; gap:12px; margin-top:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px;">
                        <span style="color:var(--text-secondary);">Rencana Berstatus Draft</span>
                        <strong class="badge badge-neutral" style="font-size:12px;">{{ $cDraft }} Kegiatan | {{ $pDraft }}%</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px;">
                        <span style="color:var(--text-secondary);">Menunggu Penetapan Anda</span>
                        <strong class="badge badge-warning" style="font-size:12px;">{{ $cMenunggu }} Kegiatan | {{ $pMenunggu }}%</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px;">
                        <span style="color:var(--text-secondary);">Telah Ditetapkan</span>
                        <strong class="badge badge-success" style="font-size:12px;">{{ $cDitetapkan }} Kegiatan | {{ $pDitetapkan }}%</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; font-size:13px;">
                        <span style="color:var(--text-secondary);">Sudah Terealisasi</span>
                        <strong class="badge badge-info" style="font-size:12px;">{{ $cRealisasi }} Kegiatan | {{ $pRealisasi }}%</strong>
                    </div>
                </div>
            </div>
            <div style="background:rgba(255,255,255,0.02); border-radius:8px; padding:16px; margin-top:20px; border:1px solid rgba(255,255,255,0.05);">
                <div style="font-weight:600; font-size:13px; margin-bottom:8px; color:var(--accent);">📈 Statistik Realisasi Akhir:</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:12px;">
                    <div>
                        <div style="color:var(--text-secondary);">Total JP Berjalan:</div>
                        <strong style="font-size:16px; color:var(--text-primary);">{{ $realisationStats->total_jp ?? 0 }} JP</strong>
                    </div>
                    <div>
                        <div style="color:var(--text-secondary);">Peserta Berpartisipasi:</div>
                        <strong style="font-size:16px; color:var(--text-primary);">{{ $realisationStats->total_participants ?? 0 }} Orang</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Jumlah Pengajuan IDP per Kompetensi Teknis --}}
        <div class="card" style="padding:24px; flex-grow:1;">
            <div class="card-title">📝 Jumlah Pengajuan IDP per Kompetensi Teknis</div>
            @if($pengajuanIdpTeknis->isNotEmpty())
                <div style="display:flex; flex-direction:column; gap:10px; margin-top:16px; max-height:280px; overflow-y:auto; padding-right:6px;">
                    @foreach($pengajuanIdpTeknis as $idp)
                    @php
                        $pctIdp = $totalIdpTeknis > 0 ? round(($idp->count / $totalIdpTeknis) * 100) : 0;
                    @endphp
                    <div style="background:rgba(255,255,255,0.03); border-radius:8px; padding:12px; border:1px solid rgba(255,255,255,0.05);">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                            <span style="font-size:13px; font-weight:600; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex:1;" title="{{ $idp->need }}">{{ $idp->need }}</span>
                            <span class="badge badge-info" style="font-size:11px; background:rgba(var(--info-rgb), 0.15); color:var(--info); white-space:nowrap; flex-shrink:0;">{{ $idp->count }} IDP | {{ $pctIdp }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    ✨ Belum ada IDP kompetensi teknis yang diajukan.
                </div>
            @endif
        </div>

    </div>

    {{-- Right Column --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        
        {{-- Leaderboard Block --}}
        <div class="card" style="padding:24px;">
            <div class="card-title">🏆 Leaderboard Pemenuhan JP</div>
            <div style="display:flex; flex-direction:column; gap:24px; margin-top:16px;">
                
                <!-- Top 5 -->
                <div>
                    <div style="font-size:13px; font-weight:600; color:var(--success); margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                        <span>⭐</span> Top 5 JP Tertinggi
                    </div>
                    @if(count($top5Jp) > 0)
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            @foreach($top5Jp as $index => $emp)
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:rgba(255,255,255,0.02); border-radius:8px; border:1px solid rgba(255,255,255,0.05);">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <span style="font-weight:bold; color:var(--text-secondary); width:20px; text-align:center;">#{{ $index + 1 }}</span>
                                    <div style="display:flex; flex-direction:column;">
                                        <span style="font-size:13px; font-weight:600; color:var(--text-primary);">{{ $emp['name'] }}</span>
                                        <span style="font-size:11px; color:var(--text-secondary);">{{ $emp['role'] }}</span>
                                    </div>
                                </div>
                                <strong style="font-size:14px; color:var(--success);">{{ $emp['total_jp'] }} JP</strong>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-size:12px; color:var(--text-secondary);">Belum ada data.</div>
                    @endif
                </div>
                
                <!-- Bottom 5 -->
                <div>
                    <div style="font-size:13px; font-weight:600; color:var(--danger); margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                        <span>⚠️</span> Bottom 5 JP Terendah
                    </div>
                    @if(count($bottom5Jp) > 0)
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            @foreach($bottom5Jp as $index => $emp)
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:rgba(255,255,255,0.02); border-radius:8px; border:1px solid rgba(255,255,255,0.05);">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <span style="font-weight:bold; color:var(--text-secondary); width:20px; text-align:center;">#{{ $index + 1 }}</span>
                                    <div style="display:flex; flex-direction:column;">
                                        <span style="font-size:13px; font-weight:600; color:var(--text-primary);">{{ $emp['name'] }}</span>
                                        <span style="font-size:11px; color:var(--text-secondary);">{{ $emp['role'] }}</span>
                                    </div>
                                </div>
                                <strong style="font-size:14px; color:var(--danger);">{{ $emp['total_jp'] }} JP</strong>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-size:12px; color:var(--text-secondary);">Belum ada data.</div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Card Deskriptif Kompetensi --}}
        <div class="card" style="padding:24px; flex-grow:1;">
            <div class="card-title">📉 Gap Kompetensi Terbanyak di Unit Kerja</div>
            @if($competencyStats->isNotEmpty())
                <div style="display:flex; flex-direction:column; gap:10px; margin-top:16px; max-height:280px; overflow-y:auto; padding-right:6px;">
                    @foreach($competencyStats as $stat)
                    <div style="background:rgba(255,255,255,0.03); border-radius:8px; padding:12px; border:1px solid rgba(255,255,255,0.05);">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:13px; font-weight:600; color:var(--text-primary);">{{ $stat->competency_name }}</span>
                            <span class="badge badge-danger" style="font-size:11px;">{{ $stat->gap_count }} Pegawai</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:11px; color:var(--text-secondary);">
                            <span>Rata-rata Nilai:</span>
                            <strong>{{ $stat->avg_score }} / 100</strong>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    🎉 Tidak ada gap kompetensi terdeteksi pada pegawai di unit ini.
                </div>
            @endif
        </div>
        
    </div>
</div>

@endsection

