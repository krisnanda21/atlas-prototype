@extends('layouts.app')
@section('title', 'Executive Insights')
@section('header_title', 'Executive Insights Dashboard')

@section('content')
<div class="page-header">
    <h1>🔭 Executive Insights</h1>
    <span class="badge badge-neutral">{{ ucwords(str_replace('_', ' ', session('active_role', auth()->user()->role))) }}</span>
</div>

{{-- KPI Cards --}}
<div class="grid-4" style="margin-bottom: 12px;">
    {{-- Card 1: % IDP Coverage --}}
    <div class="stat-card" style="text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 16px; min-height:135px;">
        <div style="font-size:15px; font-weight:700; color:#ffffff; margin-bottom:8px; line-height:1.2; text-align:center;">% IDP Coverage</div>
        <div class="stat-value" style="color:{{ $averageCoverage >= 75 ? 'var(--success)' : ($averageCoverage >= 50 ? 'var(--warning)' : 'var(--danger)') }}; font-size:30px; font-weight:800; line-height:1; margin:0; text-align:center;">{{ $averageCoverage }}%</div>
    </div>

    {{-- Card 2: % Pegawai Kompetensi Rendah --}}
    <div class="stat-card" style="text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 16px; min-height:135px;">
        <div style="font-size:15px; font-weight:700; color:#ffffff; margin-bottom:8px; line-height:1.2; text-align:center;">% Pegawai Kompetensi Rendah</div>
        <div class="stat-value" style="color:{{ $suboptimalPercent > 50 ? 'var(--danger)' : ($suboptimalPercent > 20 ? 'var(--warning)' : 'var(--success)') }}; font-size:30px; font-weight:800; line-height:1; margin:0; text-align:center;">{{ $suboptimalPercent }}%</div>
    </div>

    {{-- Card 3: Gap Kompetensi Terbesar --}}
    <div class="stat-card" style="text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 16px; min-height:135px;">
        <div style="font-size:15px; font-weight:700; color:#ffffff; margin-bottom:8px; line-height:1.2; text-align:center;">Gap Kompetensi Terbesar</div>
        <div class="stat-value" style="color:var(--danger); font-size:30px; font-weight:800; line-height:1; margin:0; text-align:center;">
            {{ $largestGapValue > 0 ? '-' . $largestGapValue : '0' }} <span style="font-size:14px; font-weight:600; color:var(--text-secondary);">poin</span>
        </div>
        <div style="font-size:12px; color:var(--text-secondary); font-weight:500; margin-top:8px; line-height:1.3; text-align:center; word-wrap:break-word; white-space:normal; max-width:100%;">
            {{ $largestGapName }}
        </div>
    </div>

    {{-- Card 4: % Realisasi Bangkom Unit --}}
    <div class="stat-card" style="text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 16px; min-height:135px;">
        <div style="font-size:15px; font-weight:700; color:#ffffff; margin-bottom:8px; line-height:1.2; text-align:center;">% Realisasi Bangkom Unit</div>
        <div class="stat-value" style="color:{{ $realisasiPercent >= 75 ? 'var(--success)' : ($realisasiPercent >= 50 ? 'var(--warning)' : 'var(--info)') }}; font-size:30px; font-weight:800; line-height:1; margin:0; text-align:center;">{{ $realisasiPercent }}%</div>
    </div>
</div>

{{-- KPI Narasi Interpretasi --}}
<div class="grid-4 mb-4" style="align-items:stretch;">
    {{-- Narasi Card 1 --}}
    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:8px; padding:14px 16px; font-size:12.5px; color:var(--text-secondary); line-height:1.5; text-align:center; display:flex; align-items:center; justify-content:center;">
        <div>Di <strong style="color:#ffffff;">{{ $scopeLabel }}</strong>, dari Total Gap Kompetensi yang dimiliki oleh keseluruhan pegawai, <strong style="color:#ffffff;">{{ $averageCoverage }}%</strong> sudah terdapat IDP untuk ditindaklanjuti oleh unit kerja.</div>
    </div>

    {{-- Narasi Card 2 --}}
    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:8px; padding:14px 16px; font-size:12.5px; color:var(--text-secondary); line-height:1.5; text-align:center; display:flex; align-items:center; justify-content:center;">
        <div><strong style="color:#ffffff;">{{ $suboptimalPercent }}%</strong> pegawai <strong style="color:#ffffff;">{{ $scopeLabel }}</strong> memiliki nilai rata-rata kompetensi teknis dibawah 78.</div>
    </div>

    {{-- Narasi Card 3 --}}
    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:8px; padding:14px 16px; font-size:12.5px; color:var(--text-secondary); line-height:1.5; text-align:center; display:flex; align-items:center; justify-content:center;">
        <div>Gap Kompetensi terbesar di <strong style="color:#ffffff;">{{ $scopeLabel }}</strong> ada pada Kompetensi <strong style="color:#ffffff;">{{ $largestGapName }}</strong> dengan nilai gap sebesar <strong style="color:var(--danger);">{{ $largestGapValue > 0 ? '-' . $largestGapValue : '0' }} poin</strong>.</div>
    </div>

    {{-- Narasi Card 4 --}}
    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:8px; padding:14px 16px; font-size:12.5px; color:var(--text-secondary); line-height:1.5; text-align:center; display:flex; align-items:center; justify-content:center;">
        <div>Sebesar <strong style="color:#ffffff;">{{ $realisasiPercent }}%</strong> Kegiatan Bangkom di <strong style="color:#ffffff;">{{ $scopeLabel }}</strong> sudah terealisasi.</div>
    </div>
</div>

{{-- Strategic Analytics (Dipindah ke atas) --}}
<div class="card mb-4" style="padding:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div class="card-title" style="margin-bottom:0;">🎯 Analisis Strategis & Rekomendasi</div>
        @if($lastAiUpdate)
        <div style="font-size:11px; color:var(--text-secondary); background:rgba(255,255,255,0.05); padding:4px 8px; border-radius:4px; display:flex; align-items:center; gap:6px;">
            <span>✨</span> Terakhir diperbarui (AI Analysis): {{ \Carbon\Carbon::parse($lastAiUpdate)->locale('id')->diffForHumans() }}
        </div>
        @endif
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:16px;">
        @foreach($strategicAnalytics as $sa)
        <div style="background:rgba(255,255,255,0.04);border-radius:8px;padding:16px;border-left:4px solid {{ $sa['risk'] === 'Tinggi' ? 'var(--danger)' : ($sa['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)') }};">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <span style="font-weight:700;font-size:14px;color:var(--text-primary);">{{ $sa['demand'] }}</span>
                <span class="badge {{ $sa['risk'] === 'Tinggi' ? 'badge-danger' : ($sa['risk'] === 'Sedang' ? 'badge-warning' : 'badge-success') }}" style="font-size:11px;">Risiko {{ $sa['risk'] }}</span>
            </div>
            <p style="font-size:13px;color:var(--text-secondary);margin:0;line-height:1.5;">{{ $sa['decision'] }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Unit Performance Chart (visual bars) - Cascading --}}
@if(in_array($activeRole, ['sesma', 'admin', 'karoSDM', 'kombinasi', 'bangkom']))
<div class="card mb-4" style="padding:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:16px;">
        <div>
            <div class="card-title" style="margin-bottom:4px;">📊 Persentase Pegawai dengan Kompetensi Rendah per Unit Kerja</div>
        </div>
    </div>

    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:10px; padding:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.05); flex-wrap:wrap; gap:8px;">
            <div>
                <div style="font-size:14px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                    <span>🏢 Unit Kerja Pusat & 📍 Kantor Perwakilan</span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <button type="button" onclick="toggleAllUnits()" class="btn btn-neutral" style="padding:3px 8px; font-size:11px; height:26px; border-radius:4px; display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <span id="btn-toggle-all-icon">📂</span>
                    <span id="btn-toggle-all-text">Buka Semua</span>
                </button>
                <div style="text-align:right;">
                    @php
                        $totalVal = 0;
                        $totalCount = count($unitStatsPusatEselon1) + count($unitStatsPerwakilanWilayah);
                        foreach($unitStatsPusatEselon1 as $u) $totalVal += $u['value'];
                        foreach($unitStatsPerwakilanWilayah as $u) $totalVal += $u['value'];
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
            
            @if(!empty($unitStatsPusatEselon1))
            <div style="font-size:11.5px; font-weight:700; color:var(--text-secondary); margin-top:5px; margin-bottom:5px; padding-left:5px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:5px;">-- Unit Kerja Pusat --</div>

                @foreach($unitStatsPusatEselon1 as $e1)
                @php
                    $e1RiskColor = $e1['risk'] === 'Tinggi' ? 'var(--danger)' : ($e1['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                    $e1RiskGradient = $e1['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($e1['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                @endphp
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <div onclick="toggleUnit('{{ $e1['code'] }}')"
                         title="{{ $e1['name'] }} ({{ $e1['value'] }}%)"
                         style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:rgba(255,255,255,0.035); border:1px solid rgba(255,255,255,0.08); border-radius:6px; cursor:pointer;">
                        <div style="width:100px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:12px; font-weight:800; color:var(--text-primary); text-align:right;">
                            <span id="chevron-{{ $e1['code'] }}" style="font-size:9px; color:var(--text-secondary); transition:transform 0.2s ease;">▶</span>
                            <span>{{ $e1['code'] }}</span>
                        </div>
                        <div style="flex:1; background:rgba(255,255,255,0.08); border-radius:4px; height:16px; position:relative; overflow:hidden;">
                            <div style="position:absolute; left:25%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                            <div style="position:absolute; left:50%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                            <div style="position:absolute; left:75%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                            <div style="height:100%; width:{{ min($e1['value'], 100) }}%; background:{{ $e1RiskGradient }}; border-radius:4px;"></div>
                        </div>
                        <div style="width:55px; font-size:12px; font-weight:800; color:{{ $e1RiskColor }}; text-align:right;">
                            {{ $e1['value'] }}%
                        </div>
                    </div>
                    <div id="children-{{ $e1['code'] }}" class="ncc-unit-children" style="display:none; flex-direction:column; gap:5px; padding-left:14px; margin-left:10px; border-left:2px dashed rgba(255,255,255,0.12); margin-top:2px; margin-bottom:4px;">
                        @foreach($e1['children'] as $child)
                        @php
                            $childRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                            $childRiskGradient = $child['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($child['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                        @endphp
                        <div title="{{ $child['unit'] }} ({{ $child['value'] }}%)"
                             style="display:flex; align-items:center; gap:10px; padding:5px 8px; background:rgba(255,255,255,0.015); border:1px solid rgba(255,255,255,0.03); border-radius:5px;">
                            <div style="width:86px; font-size:10.5px; font-weight:700; color:var(--text-secondary); text-align:right; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:help;">
                                {{ $child['code'] }}
                            </div>
                            <div style="flex:1; background:rgba(255,255,255,0.05); border-radius:3px; height:11px; position:relative; overflow:hidden;">
                                <div style="position:absolute; left:25%; width:1px; height:100%; background:rgba(255,255,255,0.06);"></div>
                                <div style="position:absolute; left:50%; width:1px; height:100%; background:rgba(255,255,255,0.06);"></div>
                                <div style="position:absolute; left:75%; width:1px; height:100%; background:rgba(255,255,255,0.06);"></div>
                                <div style="height:100%; width:{{ min($child['value'], 100) }}%; background:{{ $childRiskGradient }}; border-radius:3px;"></div>
                            </div>
                            <div style="width:55px; font-size:10.5px; font-weight:700; color:{{ $childRiskColor }}; text-align:right;">
                                {{ $child['value'] }}%
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            @endif

            @if(!empty($unitStatsPerwakilanWilayah))
            <div style="font-size:11.5px; font-weight:700; color:var(--text-secondary); margin-top:15px; margin-bottom:5px; padding-left:5px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:5px;">-- Kantor Perwakilan --</div>

                @foreach($unitStatsPerwakilanWilayah as $w)
                @php
                    $wRiskColor = $w['risk'] === 'Tinggi' ? 'var(--danger)' : ($w['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                    $wRiskGradient = $w['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($w['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                @endphp
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <div onclick="toggleUnit('{{ $w['code'] }}')"
                         title="{{ $w['name'] }} ({{ $w['value'] }}%)"
                         style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:rgba(255,255,255,0.035); border:1px solid rgba(255,255,255,0.08); border-radius:6px; cursor:pointer;">
                        <div style="width:100px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:11.5px; font-weight:800; color:var(--text-primary); text-align:right; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <span id="chevron-{{ $w['code'] }}" style="font-size:9px; color:var(--text-secondary); transition:transform 0.2s ease;">▶</span>
                            <span>{{ $w['short'] }}</span>
                        </div>
                        <div style="flex:1; background:rgba(255,255,255,0.08); border-radius:4px; height:16px; position:relative; overflow:hidden;">
                            <div style="position:absolute; left:25%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                            <div style="position:absolute; left:50%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                            <div style="position:absolute; left:75%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                            <div style="height:100%; width:{{ min($w['value'], 100) }}%; background:{{ $wRiskGradient }}; border-radius:4px;"></div>
                        </div>
                        <div style="width:55px; font-size:12px; font-weight:800; color:{{ $wRiskColor }}; text-align:right;">
                            {{ $w['value'] }}%
                        </div>
                    </div>
                    <div id="children-{{ $w['code'] }}" class="ncc-unit-children" style="display:none; flex-direction:column; gap:5px; padding-left:14px; margin-left:10px; border-left:2px dashed rgba(255,255,255,0.12); margin-top:2px; margin-bottom:4px;">
                        @foreach($w['children'] as $child)
                        @php
                            $pwRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                            $pwRiskGradient = $child['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($child['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                        @endphp
                        <div title="{{ $child['unit'] }} ({{ $child['value'] }}%)"
                             style="display:flex; align-items:center; gap:10px; padding:5px 8px; background:rgba(255,255,255,0.015); border:1px solid rgba(255,255,255,0.03); border-radius:5px;">
                            <div style="width:86px; font-size:10.5px; font-weight:700; color:var(--text-secondary); text-align:right; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:help;">
                                {{ $child['code'] }}
                            </div>
                            <div style="flex:1; background:rgba(255,255,255,0.05); border-radius:3px; height:11px; position:relative; overflow:hidden;">
                                <div style="position:absolute; left:25%; width:1px; height:100%; background:rgba(255,255,255,0.06);"></div>
                                <div style="position:absolute; left:50%; width:1px; height:100%; background:rgba(255,255,255,0.06);"></div>
                                <div style="position:absolute; left:75%; width:1px; height:100%; background:rgba(255,255,255,0.06);"></div>
                                <div style="height:100%; width:{{ min($child['value'], 100) }}%; background:{{ $pwRiskGradient }}; border-radius:3px;"></div>
                            </div>
                            <div style="width:55px; font-size:10.5px; font-weight:700; color:{{ $pwRiskColor }}; text-align:right;">
                                {{ $child['value'] }}%
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            @endif

            @if(empty($unitStatsPusatEselon1) && empty($unitStatsPerwakilanWilayah))
            <div style="text-align:center; padding:32px; color:var(--text-secondary); font-size:12px;">Data tidak tersedia.</div>
            @endif
        </div>
    </div>
</div>
@elseif($activeRole === 'deputi')
{{-- Unit Performance Chart Deputi --}}
<div class="card mb-4" style="padding:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:16px;">
        <div>
            <div class="card-title" style="margin-bottom:4px;">📊 Persentase Pegawai dengan Kompetensi Rendah per Unit Kerja</div>
        </div>
    </div>

    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:10px; padding:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.05); flex-wrap:wrap; gap:8px;">
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
                     style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:rgba(255,255,255,0.035); border:1px solid rgba(255,255,255,0.08); border-radius:6px; margin-bottom:4px;">
                    <div style="width:100px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:12px; font-weight:800; color:var(--text-primary); text-align:right;">
                        <span>{{ $child['code'] }}</span>
                    </div>
                    <div style="flex:1; background:rgba(255,255,255,0.08); border-radius:4px; height:16px; position:relative; overflow:hidden;">
                        <div style="position:absolute; left:25%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                        <div style="position:absolute; left:50%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                        <div style="position:absolute; left:75%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
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

{{-- Gap Kompetensi Teknis (Side-Bar Chart) --}}
<div class="card mb-4" style="padding:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:16px;">
        <div class="card-title" style="margin-bottom:0;">📉 Gap Kompetensi Teknis</div>
    </div>

    @php
        $maxGap = $techGaps->max('avg_gap') ?: 1; // avoid division by zero
    @endphp

    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:10px; padding:18px;">
        {{-- X-Axis Scale Header --}}
        <div style="display:flex; align-items:center; margin-bottom:10px; padding-left:200px; padding-right:50px; font-size:10px; color:var(--text-secondary);">
            <div style="flex:1; display:flex; justify-content:space-between; position:relative;">
                <span>0 Poin</span>
                <span>{{ round($maxGap / 2, 1) }} Poin</span>
                <span>{{ $maxGap }} Poin</span>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:8px; max-height:400px; overflow-y:auto; padding-right:4px;">
            @foreach($techGaps as $gap)
            @php
                $pct = min(($gap->avg_gap / $maxGap) * 100, 100);
            @endphp
            <div style="display:flex; align-items:center; gap:12px; padding:8px 10px; background:rgba(255,255,255,0.015); border:1px solid rgba(255,255,255,0.03); border-radius:6px;">
                {{-- Y-Axis: Nama Kompetensi --}}
                <div style="width:188px; font-size:11.5px; font-weight:600; color:var(--text-primary); text-align:right; line-height:1.2; word-wrap:break-word;">
                    {{ $gap->competency_name }}
                </div>
                
                {{-- X-Axis: Bar --}}
                <div style="flex:1; background:rgba(255,255,255,0.05); border-radius:4px; height:16px; position:relative; overflow:hidden;">
                    <div style="position:absolute; left:50%; width:1px; height:100%; background:rgba(255,255,255,0.06);"></div>
                    <div style="height:100%; width:{{ $pct }}%; background:linear-gradient(90deg, #ef4444, #f87171); border-radius:4px;"></div>
                </div>

                {{-- Value --}}
                <div style="width:45px; font-size:12px; font-weight:800; color:var(--danger); text-align:right;">
                    -{{ $gap->avg_gap }}
                </div>
            </div>
            @endforeach
            @if($techGaps->isEmpty())
            <div style="text-align:center; padding:20px; color:var(--text-secondary); font-size:12px;">Tidak ada gap kompetensi teknis.</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
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
</script>
@endpush


@endsection
