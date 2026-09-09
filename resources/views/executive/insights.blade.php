@extends('layouts.app')
@section('title', 'Executive Insights')
@section('header_title', 'Executive Insights Dashboard')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">🔭 Executive Insights</h1>
    <span class="badge badge-neutral">{{ ucwords(str_replace('_', ' ', session('active_role', auth()->user()->role))) }}</span>
</div>

{{-- KPI Cards --}}
<div class="grid-4" style="margin-bottom: 12px;">
    {{-- Card 1: % IDP Coverage --}}
    <div class="stat-card" style="text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 16px; min-height:135px;">
        <div style="font-size:15px; font-weight:700; color:var(--text-secondary); margin-bottom:8px; line-height:1.2; text-align:center;">% IDP Coverage</div>
        <div class="stat-value" style="color:{{ $averageCoverage >= 75 ? 'var(--success)' : ($averageCoverage >= 50 ? 'var(--warning)' : 'var(--danger)') }}; font-size:30px; font-weight:800; line-height:1; margin:0; text-align:center;">{{ $averageCoverage }}%</div>
    </div>

    {{-- Card 2: % Pegawai Kompetensi Rendah --}}
    <div class="stat-card" style="text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 16px; min-height:135px;">
        <div style="font-size:15px; font-weight:700; color:var(--text-secondary); margin-bottom:8px; line-height:1.2; text-align:center;">% Pegawai Kompetensi Rendah</div>
        <div class="stat-value" style="color:{{ $suboptimalPercent > 50 ? 'var(--danger)' : ($suboptimalPercent > 20 ? 'var(--warning)' : 'var(--success)') }}; font-size:30px; font-weight:800; line-height:1; margin:0; text-align:center;">{{ $suboptimalPercent }}%</div>
    </div>

    {{-- Card 3: Gap Kompetensi Terbesar --}}
    <div class="stat-card" style="text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 16px; min-height:135px;">
        <div style="font-size:15px; font-weight:700; color:var(--text-secondary); margin-bottom:8px; line-height:1.2; text-align:center;">Gap Kompetensi Terbesar</div>
        <div class="stat-value" style="color:var(--danger); font-size:30px; font-weight:800; line-height:1; margin:0; text-align:center;">
            {{ $largestGapValue > 0 ? '-' . $largestGapValue : '0' }} <span style="font-size:14px; font-weight:600; color:var(--text-secondary);">poin</span>
        </div>
        <div style="font-size:12px; color:var(--text-secondary); font-weight:500; margin-top:8px; line-height:1.3; text-align:center; word-wrap:break-word; white-space:normal; max-width:100%;">
            {{ $largestGapName }}
        </div>
    </div>

    {{-- Card 4: % Realisasi Bangkom Unit --}}
    <div class="stat-card" style="text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 16px; min-height:135px;">
        <div style="font-size:15px; font-weight:700; color:var(--text-secondary); margin-bottom:8px; line-height:1.2; text-align:center;">% Realisasi Bangkom Unit</div>
        <div class="stat-value" style="color:{{ $realisasiPercent >= 75 ? 'var(--success)' : ($realisasiPercent >= 50 ? 'var(--warning)' : 'var(--info)') }}; font-size:30px; font-weight:800; line-height:1; margin:0; text-align:center;">{{ $realisasiPercent }}%</div>
    </div>
</div>

{{-- KPI Narasi Interpretasi --}}
<div class="grid-4 mb-4" style="align-items:stretch;">
    {{-- Narasi Card 1 --}}
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px 16px; font-size:12.5px; color:var(--text-secondary); line-height:1.5; text-align:center; display:flex; align-items:center; justify-content:center;">
        <div>Di <strong style="color:var(--text-primary);">{{ $scopeLabel }}</strong>, dari Total Gap Kompetensi yang dimiliki oleh keseluruhan pegawai, <strong style="color:var(--text-primary);">{{ $averageCoverage }}%</strong> sudah terdapat IDP untuk ditindaklanjuti oleh unit kerja.</div>
    </div>

    {{-- Narasi Card 2 --}}
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px 16px; font-size:12.5px; color:var(--text-secondary); line-height:1.5; text-align:center; display:flex; align-items:center; justify-content:center;">
        <div><strong style="color:var(--text-primary);">{{ $suboptimalPercent }}%</strong> pegawai <strong style="color:var(--text-primary);">{{ $scopeLabel }}</strong> memiliki nilai rata-rata kompetensi teknis dibawah 78.</div>
    </div>

    {{-- Narasi Card 3 --}}
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px 16px; font-size:12.5px; color:var(--text-secondary); line-height:1.5; text-align:center; display:flex; align-items:center; justify-content:center;">
        <div>Gap Kompetensi terbesar di <strong style="color:var(--text-primary);">{{ $scopeLabel }}</strong> ada pada Kompetensi <strong style="color:var(--text-primary);">{{ $largestGapName }}</strong> dengan nilai gap sebesar <strong style="color:var(--danger);">{{ $largestGapValue > 0 ? '-' . $largestGapValue : '0' }} poin</strong>.</div>
    </div>

    {{-- Narasi Card 4 --}}
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px 16px; font-size:12.5px; color:var(--text-secondary); line-height:1.5; text-align:center; display:flex; align-items:center; justify-content:center; min-width:0;">
        <div>Sebesar <strong style="color:var(--text-primary);">{{ $realisasiPercent }}%</strong> Kegiatan Bangkom di <strong style="color:var(--text-primary);">{{ $scopeLabel }}</strong> sudah terealisasi.</div>
    </div>
</div>

{{-- Gap Kompetensi Teknis (Spider Chart) + Strategic Analytics: Side-by-Side --}}
<div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:16px;">

    {{-- Card Gap Kompetensi Teknis (Spider Chart) --}}
    <div class="card" style="padding:20px; margin-bottom:0; display:flex; flex-direction:column; min-width:0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #E2E8F0; padding-bottom:14px; flex-shrink:0;">
            <div class="card-title" style="margin-bottom:0;font-size:18px">📉 Gap Kompetensi Teknis</div>
        </div>

        @if($techGaps->isEmpty())
        <div style="text-align:center; padding:40px; color:var(--text-secondary); font-size:13px;">Tidak ada data gap kompetensi teknis.</div>
        @else
        <div style="position:relative; flex:1; min-height:280px; min-width:0;">
            <canvas id="spiderChartGapKompetensi" style="width:100%; height:100%;"></canvas>
        </div>
        <div style="display:flex; justify-content:center; gap:20px; margin-top:12px; flex-wrap:wrap; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:6px; font-size:11.5px; color:var(--text-secondary);">
                <div style="width:14px; height:14px; border-radius:50%; background:rgba(96,165,250,0.7); border:1.5px solid #60a5fa;"></div>
                <span>Nilai Rata-rata</span>
            </div>
            <div style="display:flex; align-items:center; gap:6px; font-size:11.5px; color:var(--text-secondary);">
                <div style="width:14px; height:14px; border-radius:50%; background:rgba(239,68,68,0.25); border:1.5px dashed #ef4444;"></div>
                <span>Standar Nilai</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Card Strategic Analytics --}}
    <div class="card" style="padding:20px; margin-bottom:0; min-width:0;">
        <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:14px; border-bottom:1px solid #E2E8F0; padding-bottom:12px;">
            <div class="card-title" style="margin-bottom:0;font-size:18px">🎯 Analisis Strategis & Rekomendasi</div>
            @if($lastAiUpdate)
            <div style="font-size:12px; color:var(--text-secondary); display:flex; align-items:center; gap:4px;">
                <span>✨</span> Diperbarui {{ \Carbon\Carbon::parse($lastAiUpdate)->locale('id')->diffForHumans() }}
            </div>
            @endif
        </div>
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($strategicAnalytics as $sa)
            <div style="background:#F8FAFC;border-radius:8px;padding:14px 16px;border-left:4px solid {{ $sa['risk'] === 'Tinggi' ? 'var(--danger)' : ($sa['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)') }};">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;gap:8px;">
                    <span style="font-weight:700;font-size:13px;color:var(--text-primary); line-height:1.3;">{{ $sa['demand'] }}</span>
                    <span class="badge {{ $sa['risk'] === 'Tinggi' ? 'badge-danger' : ($sa['risk'] === 'Sedang' ? 'badge-warning' : 'badge-success') }}" style="font-size:10px; white-space:nowrap; flex-shrink:0;">Risiko {{ $sa['risk'] }}</span>
                </div>
                <p style="font-size:12px;color:var(--text-secondary);margin:0;line-height:1.5;">{{ $sa['decision'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- Unit Performance Chart (visual bars) - Cascading --}}
@if(in_array($activeRole, ['sesma', 'admin', 'karoSDM', 'kombinasi', 'bangkom']))
<div class="card mb-4" style="padding:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; border-bottom:1px solid #E2E8F0; padding-bottom:16px;">
        <div>
            <div class="card-title" style="margin-bottom:4px; font-size:18px;">📊 Persentase Pegawai dengan Kompetensi Rendah per Unit Kerja</div>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <button type="button" onclick="toggleAllUnits()" class="btn btn-neutral" style="padding:3px 8px; font-size:11px; height:26px; border-radius:4px; display:flex; align-items:center; gap:4px; cursor:pointer;">
                <span id="btn-toggle-all-icon">📂</span>
                <span id="btn-toggle-all-text">Buka Semua</span>
            </button>
            @php
                $totalVal = 0;
                $totalCount = count($unitStatsPusatEselon1) + count($unitStatsPerwakilanWilayah);
                foreach($unitStatsPusatEselon1 as $u) $totalVal += $u['value'];
                foreach($unitStatsPerwakilanWilayah as $u) $totalVal += $u['value'];
                $avgAllVal = $totalCount > 0 ? round($totalVal / $totalCount) : 0;
            @endphp
            <div style="text-align:right;">
                <span style="font-size:11px; color:var(--text-secondary);">Rata-rata:</span>
                <span style="font-size:13px; font-weight:700; color:{{ $avgAllVal > 50 ? 'var(--danger)' : ($avgAllVal > 20 ? 'var(--warning)' : 'var(--success)') }}"> {{ $avgAllVal }}%</span>
            </div>
        </div>
    </div>

    {{-- Side-by-side: Unit Kerja Pusat & Kantor Perwakilan --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start;">

        {{-- Kolom Kiri: Unit Kerja Pusat --}}
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; gap:8px;">
                <div style="font-size:13px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                    <span>🏢 Unit Kerja Pusat</span>
                </div>
                @php
                    $avgPusatValLocal = count($unitStatsPusatEselon1) > 0 ? round(collect($unitStatsPusatEselon1)->avg('value')) : 0;
                @endphp
                <div>
                    <span style="font-size:10px; color:var(--text-secondary);">Rata-rata:</span>
                    <span style="font-size:12px; font-weight:700; color:{{ $avgPusatValLocal > 50 ? 'var(--danger)' : ($avgPusatValLocal > 20 ? 'var(--warning)' : 'var(--success)') }}"> {{ $avgPusatValLocal }}%</span>
                </div>
            </div>
            {{-- X-Axis Scale Header --}}
            <div style="display:flex; align-items:center; margin-bottom:8px; padding-left:100px; padding-right:52px; font-size:9.5px; color:var(--text-secondary);">
                <div style="flex:1; display:flex; justify-content:space-between;">
                    <span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span>
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; max-height:500px; overflow-y:auto; padding-right:4px;">
                @if(!empty($unitStatsPusatEselon1))
                    @foreach($unitStatsPusatEselon1 as $e1)
                    @php
                        $e1RiskColor = $e1['risk'] === 'Tinggi' ? 'var(--danger)' : ($e1['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                        $e1RiskGradient = $e1['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($e1['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                    @endphp
                    <div style="display:flex; flex-direction:column; gap:3px;">
                        <div onclick="toggleUnit('{{ $e1['code'] }}')"
                             title="{{ $e1['name'] }} ({{ $e1['value'] }}%)"
                             style="display:flex; align-items:center; gap:8px; padding:7px 8px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; cursor:pointer;">
                            <div style="width:88px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:11.5px; font-weight:800; color:var(--text-primary); text-align:right;">
                                <span id="chevron-{{ $e1['code'] }}" style="font-size:9px; color:var(--text-secondary); transition:transform 0.2s ease;">▶</span>
                                <span>{{ $e1['code'] }}</span>
                            </div>
                            <div style="flex:1; background:#F1F5F9; border-radius:4px; height:14px; position:relative; overflow:hidden;">
                                <div style="position:absolute; left:25%; width:1px; height:100%; background:#F1F5F9;"></div>
                                <div style="position:absolute; left:50%; width:1px; height:100%; background:#F1F5F9;"></div>
                                <div style="position:absolute; left:75%; width:1px; height:100%; background:#F1F5F9;"></div>
                                <div style="height:100%; width:{{ min($e1['value'], 100) }}%; background:{{ $e1RiskGradient }}; border-radius:4px;"></div>
                            </div>
                            <div style="width:42px; font-size:11.5px; font-weight:800; color:{{ $e1RiskColor }}; text-align:right;">{{ $e1['value'] }}%</div>
                        </div>
                        <div id="children-{{ $e1['code'] }}" class="ncc-unit-children" style="display:none; flex-direction:column; gap:4px; padding-left:12px; margin-left:8px; border-left:2px dashed #CBD5E10.12); margin-top:2px; margin-bottom:3px;">
                            @foreach($e1['children'] as $child)
                            @php
                                $childRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                                $childRiskGradient = $child['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($child['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                            @endphp
                            <div title="{{ $child['unit'] }} ({{ $child['value'] }}%)"
                                 style="display:flex; align-items:center; gap:8px; padding:4px 6px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:5px;">
                                <div style="width:74px; font-size:10px; font-weight:700; color:var(--text-secondary); text-align:right; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:help;">{{ $child['code'] }}</div>
                                <div style="flex:1; background:#F8FAFC; border-radius:3px; height:10px; position:relative; overflow:hidden;">
                                    <div style="position:absolute; left:25%; width:1px; height:100%; background:#F1F5F9;"></div>
                                    <div style="position:absolute; left:50%; width:1px; height:100%; background:#F1F5F9;"></div>
                                    <div style="position:absolute; left:75%; width:1px; height:100%; background:#F1F5F9;"></div>
                                    <div style="height:100%; width:{{ min($child['value'], 100) }}%; background:{{ $childRiskGradient }}; border-radius:3px;"></div>
                                </div>
                                <div style="width:38px; font-size:10px; font-weight:700; color:{{ $childRiskColor }}; text-align:right;">{{ $child['value'] }}%</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @else
                <div style="text-align:center; padding:24px; color:var(--text-secondary); font-size:12px;">Data tidak tersedia.</div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Kantor Perwakilan --}}
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; gap:8px;">
                <div style="font-size:13px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                    <span>📍 Kantor Perwakilan</span>
                </div>
                @php
                    $avgPerwakValLocal = count($unitStatsPerwakilanWilayah) > 0 ? round(collect($unitStatsPerwakilanWilayah)->avg('value')) : 0;
                @endphp
                <div>
                    <span style="font-size:10px; color:var(--text-secondary);">Rata-rata:</span>
                    <span style="font-size:12px; font-weight:700; color:{{ $avgPerwakValLocal > 50 ? 'var(--danger)' : ($avgPerwakValLocal > 20 ? 'var(--warning)' : 'var(--success)') }}"> {{ $avgPerwakValLocal }}%</span>
                </div>
            </div>
            {{-- X-Axis Scale Header --}}
            <div style="display:flex; align-items:center; margin-bottom:8px; padding-left:100px; padding-right:52px; font-size:9.5px; color:var(--text-secondary);">
                <div style="flex:1; display:flex; justify-content:space-between;">
                    <span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span>
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; max-height:500px; overflow-y:auto; padding-right:4px;">
                @if(!empty($unitStatsPerwakilanWilayah))
                    @foreach($unitStatsPerwakilanWilayah as $w)
                    @php
                        $wRiskColor = $w['risk'] === 'Tinggi' ? 'var(--danger)' : ($w['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                        $wRiskGradient = $w['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($w['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                    @endphp
                    <div style="display:flex; flex-direction:column; gap:3px;">
                        <div onclick="toggleUnit('{{ $w['code'] }}')"
                             title="{{ $w['name'] }} ({{ $w['value'] }}%)"
                             style="display:flex; align-items:center; gap:8px; padding:7px 8px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; cursor:pointer;">
                            <div style="width:88px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:11px; font-weight:800; color:var(--text-primary); text-align:right; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <span id="chevron-{{ $w['code'] }}" style="font-size:9px; color:var(--text-secondary); transition:transform 0.2s ease;">▶</span>
                                <span>{{ $w['short'] }}</span>
                            </div>
                            <div style="flex:1; background:#F1F5F9; border-radius:4px; height:14px; position:relative; overflow:hidden;">
                                <div style="position:absolute; left:25%; width:1px; height:100%; background:#F1F5F9;"></div>
                                <div style="position:absolute; left:50%; width:1px; height:100%; background:#F1F5F9;"></div>
                                <div style="position:absolute; left:75%; width:1px; height:100%; background:#F1F5F9;"></div>
                                <div style="height:100%; width:{{ min($w['value'], 100) }}%; background:{{ $wRiskGradient }}; border-radius:4px;"></div>
                            </div>
                            <div style="width:42px; font-size:11.5px; font-weight:800; color:{{ $wRiskColor }}; text-align:right;">{{ $w['value'] }}%</div>
                        </div>
                        <div id="children-{{ $w['code'] }}" class="ncc-unit-children" style="display:none; flex-direction:column; gap:4px; padding-left:12px; margin-left:8px; border-left:2px dashed #CBD5E10.12); margin-top:2px; margin-bottom:3px;">
                            @foreach($w['children'] as $child)
                            @php
                                $pwRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                                $pwRiskGradient = $child['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($child['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                            @endphp
                            <div title="{{ $child['unit'] }} ({{ $child['value'] }}%)"
                                 style="display:flex; align-items:center; gap:8px; padding:4px 6px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:5px;">
                                <div style="width:74px; font-size:10px; font-weight:700; color:var(--text-secondary); text-align:right; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:help;">{{ $child['code'] }}</div>
                                <div style="flex:1; background:#F8FAFC; border-radius:3px; height:10px; position:relative; overflow:hidden;">
                                    <div style="position:absolute; left:25%; width:1px; height:100%; background:#F1F5F9;"></div>
                                    <div style="position:absolute; left:50%; width:1px; height:100%; background:#F1F5F9;"></div>
                                    <div style="position:absolute; left:75%; width:1px; height:100%; background:#F1F5F9;"></div>
                                    <div style="height:100%; width:{{ min($child['value'], 100) }}%; background:{{ $pwRiskGradient }}; border-radius:3px;"></div>
                                </div>
                                <div style="width:38px; font-size:10px; font-weight:700; color:{{ $pwRiskColor }}; text-align:right;">{{ $child['value'] }}%</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @else
                <div style="text-align:center; padding:24px; color:var(--text-secondary); font-size:12px;">Data tidak tersedia.</div>
                @endif
            </div>
        </div>

    </div>{{-- end side-by-side grid --}}
</div>
@elseif($activeRole === 'deputi')
{{-- Unit Performance Chart Deputi --}}
<div class="card mb-4" style="padding:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; border-bottom:1px solid #E2E8F0; padding-bottom:16px;">
        <div>
            <div class="card-title" style="margin-bottom:4px; font-size:18px;">📊 Persentase Pegawai dengan Kompetensi Rendah per Unit Kerja</div>
        </div>
    </div>

    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; gap:8px;">
            <div>
                <div style="font-size:14px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                    <span>🏢 Unit Kerja Eselon 2 (Scope Deputi)</span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="text-align:right;">
                    @php
                        $totalVal = 0;
                        $totalCount = count($unitStatsDeputi);
                        foreach($unitStatsDeputi as $u) $totalVal += $u['value'];
                        $avgAllVal = $totalCount > 0 ? round($totalVal / $totalCount) : 0;
                    @endphp
                    <span style="font-size:11px; color:var(--text-secondary);">Rata-rata:</span>
                    <span style="font-size:13px; font-weight:700; color:{{ $avgAllVal > 50 ? 'var(--danger)' : ($avgAllVal > 20 ? 'var(--warning)' : 'var(--success)') }}">{{ $avgAllVal }}%</span>
                </div>
            </div>
        </div>

        {{-- X-Axis Scale Header (0% to 100%) --}}
        <div style="display:flex; align-items:center; margin-bottom:10px; padding-left:110px; padding-right:60px; font-size:10px; color:var(--text-secondary);">
            <div style="flex:1; display:flex; justify-content:space-between; position:relative;">
                <span>0%</span>
                <span>25%</span>
                <span>50%</span>
                <span>75%</span>
                <span>100%</span>
            </div>
        </div>

        {{-- Chart Items Container --}}
        <div style="display:flex; flex-direction:column; gap:10px; max-height:500px; overflow-y:auto; padding-right:4px;">
            @if(!empty($unitStatsDeputi))
                @foreach($unitStatsDeputi as $child)
                @php
                    $childRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                    $childRiskGradient = $child['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($child['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                @endphp
                <div title="{{ $child['unit'] }} ({{ $child['value'] }}%)"
                     style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; margin-bottom:4px;">
                    <div style="width:100px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:12px; font-weight:800; color:var(--text-primary); text-align:right;">
                        <span>{{ $child['code'] }}</span>
                    </div>
                    <div style="flex:1; background:#F1F5F9; border-radius:4px; height:16px; position:relative; overflow:hidden;">
                        <div style="position:absolute; left:25%; width:1px; height:100%; background:#F1F5F9;"></div>
                        <div style="position:absolute; left:50%; width:1px; height:100%; background:#F1F5F9;"></div>
                        <div style="position:absolute; left:75%; width:1px; height:100%; background:#F1F5F9;"></div>
                        <div style="height:100%; width:{{ min($child['value'], 100) }}%; background:{{ $childRiskGradient }}; border-radius:4px;"></div>
                    </div>
                    <div style="width:55px; font-size:12px; font-weight:800; color:{{ $childRiskColor }}; text-align:right;">
                        {{ $child['value'] }}%
                    </div>
                </div>
                @endforeach
            @else
            <div style="text-align:center; padding:32px; color:var(--text-secondary); font-size:12px;">Data tidak tersedia.</div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Gap Kompetensi Teknis sekarang ada di dalam grid side-by-side di atas (bersama Strategic Analytics) --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
let allUnitsExpanded = false;

function toggleUnit(code) {
    const childrenContainer = document.getElementById('children-' + code);
    const chevron = document.getElementById('chevron-' + code);
    if (childrenContainer) {
        const isHidden = childrenContainer.style.display === 'none' || childrenContainer.style.display === '';
        childrenContainer.style.display = isHidden ? 'flex' : 'none';
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
        }
    }
}

function toggleAllUnits() {
    allUnitsExpanded = !allUnitsExpanded;
    const containers = document.querySelectorAll('.ncc-unit-children');
    const chevrons = document.querySelectorAll('[id^="chevron-"]');
    const btnIcon = document.getElementById('btn-toggle-all-icon');
    const btnText = document.getElementById('btn-toggle-all-text');

    containers.forEach(c => {
        c.style.display = allUnitsExpanded ? 'flex' : 'none';
    });
    chevrons.forEach(ch => {
        ch.style.transform = allUnitsExpanded ? 'rotate(90deg)' : 'rotate(0deg)';
    });
    if (btnIcon && btnText) {
        btnIcon.textContent = allUnitsExpanded ? '📁' : '📂';
        btnText.textContent = allUnitsExpanded ? 'Tutup Semua' : 'Buka Semua';
    }
}

// Spider Chart – Gap Kompetensi Teknis
(function() {
    const canvas = document.getElementById('spiderChartGapKompetensi');
    if (!canvas) return;

    const labels = {!! json_encode($techGaps->pluck('competency_name')) !!};
    const avgScores = {!! json_encode($techGaps->pluck('avg_score')) !!};
    const avgStandards = {!! json_encode($techGaps->pluck('avg_standard')) !!};

    if (!labels.length) return;

    // Shorten label jika terlalu panjang
    const shortLabels = labels.map(l => l.length > 22 ? l.substring(0, 20) + '…' : l);

    const container = canvas.parentElement;
    const spiderChart = new Chart(canvas, {
        type: 'radar',
        data: {
            labels: shortLabels,
            datasets: [
                {
                    label: 'Nilai Rata-rata',
                    data: avgScores,
                    backgroundColor: 'rgba(96, 165, 250, 0.18)',
                    borderColor: 'rgba(96, 165, 250, 0.85)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(96, 165, 250, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(96, 165, 250, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Standar Nilai',
                    data: avgStandards,
                    backgroundColor: 'rgba(239, 68, 68, 0.08)',
                    borderColor: 'rgba(239, 68, 68, 0.6)',
                    borderWidth: 2,
                    borderDash: [5, 4],
                    pointBackgroundColor: 'rgba(239, 68, 68, 0.7)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(239, 68, 68, 1)',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,20,40,0.92)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#94a3b8',
                    borderColor: '#E2E8F0',
                    borderWidth: 1,
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': ' + ctx.raw;
                        }
                    }
                }
            },
            scales: {
                r: {
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        color: 'rgba(148,163,184,0.7)',
                        font: { size: 9.5 },
                        backdropColor: 'transparent',
                    },
                    grid: {
                        color: 'rgba(100,116,139,0.15)',
                    },
                    angleLines: {
                        color: 'rgba(100,116,139,0.15)',
                    },
                    pointLabels: {
                        color: 'rgba(71,85,105,0.9)',
                        font: { size: 10.5, weight: '600' },
                    }
                }
            }
        }
    });

    // Pasang ResizeObserver pada container agar chart resize
    // mengikuti perubahan ukuran window/container secara akurat
    if (typeof ResizeObserver !== 'undefined') {
        const ro = new ResizeObserver(() => {
            spiderChart.resize();
        });
        ro.observe(container);
    } else {
        // Fallback untuk browser lama
        window.addEventListener('resize', () => spiderChart.resize());
    }
})();
</script>
@endpush


@endsection
