@extends('layouts.app')
@section('title', 'National Control Centre')
@section('header_title', 'National Control Centre (NCC)')

@section('content')
<div class="page-header">
    <h1>🏛️ National Control Centre</h1>
    <div style="display:flex;gap:8px;align-items:center;">
        <span style="font-size:12px;color:var(--text-secondary);">Komposisi IDP Nasional:</span>
        <span class="badge badge-info">Assessment {{ $assessmentPercent }}%</span>
        <span class="badge badge-warning">Role/Mandatory {{ $rolePercent }}%</span>
        <span class="badge badge-neutral">Strategic {{ $strategicPercent }}%</span>
    </div>
</div>

{{-- Alert: Lowest Unit --}}
@if($lowestUnit !== '-')
<div style="padding:14px 16px;border-radius:8px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <span style="font-size:20px;">⚠️</span>
    <div>
        <div style="font-weight:600;font-size:13px;color:var(--danger);">Unit dengan Coverage Terendah</div>
        <div style="font-size:12px;color:var(--text-secondary);">{{ $lowestUnit }}</div>
    </div>
</div>
@endif

{{-- Grafik Coverage IDP per Unit Kerja (Side-to-Side) --}}
@if(in_array($activeRole, ['sesma', 'admin', 'karoSDM', 'kombinasi', 'bangkom']))
<div class="card mb-4" style="padding:20px;">
    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:16px;">
        <div>
            <div class="card-title" style="margin-bottom:4px;">📊 Persentase IDP Coverage per Unit Kerja</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap:24px; align-items:start;">
        
        {{-- SEBELAH KIRI: Unit Kerja Pusat (Eselon 1 & Cascading Eselon 2) --}}
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.05); flex-wrap:wrap; gap:8px;">
                <div>
                    <div style="font-size:14px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                        <span>🏢 Pusat</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <button type="button" id="btn-toggle-all-e1" onclick="toggleAllEselon1()" class="btn btn-neutral" style="padding:3px 8px; font-size:11px; height:26px; border-radius:4px; display:flex; align-items:center; gap:4px; cursor:pointer;">
                        <span id="btn-toggle-all-icon">📂</span>
                        <span id="btn-toggle-all-text">Buka Semua</span>
                    </button>
                    <div style="text-align:right;">
                        <span style="font-size:11px; color:var(--text-secondary);">Rata-rata:</span>
                        <span style="font-size:13px; font-weight:700; color:{{ $avgPusat >= 75 ? 'var(--success)' : ($avgPusat >= 50 ? 'var(--warning)' : 'var(--danger)') }}">{{ $avgPusat }}%</span>
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

            {{-- Chart Items Container (Cascading Eselon 1 -> Eselon 2) --}}
            <div id="ncc-pusat-container" style="display:flex; flex-direction:column; gap:10px; max-height:650px; overflow-y:auto; padding-right:4px;">
                @foreach($unitStatsPusatEselon1 as $e1)
                @php
                    $e1RiskColor = $e1['risk'] === 'Tinggi' ? 'var(--danger)' : ($e1['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                    $e1RiskGradient = $e1['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($e1['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                @endphp
                <div class="ncc-eselon1-group" 
                     id="group-{{ $e1['code'] }}" 
                     data-code="{{ $e1['code'] }}" 
                     data-name="{{ $e1['name'] }}" 
                     data-coverage="{{ $e1['coverage'] }}" 
                     data-risk="{{ $e1['risk'] }}"
                     style="display:flex; flex-direction:column; gap:4px;">
                    
                    {{-- Master Row: Eselon 1 --}}
                    <div class="ncc-eselon1-row" 
                         onclick="toggleEselon1('{{ $e1['code'] }}')"
                         title="{{ $e1['name'] }} ({{ $e1['coverage'] }}% IDP Coverage · {{ count($e1['children']) }} Unit Eselon II) - Klik untuk cascading"
                         style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:rgba(255,255,255,0.035); border:1px solid rgba(255,255,255,0.08); border-radius:6px; cursor:pointer; transition: all 0.2s ease;">
                        
                        {{-- Sumbu Y: Toggle Chevron & Kode Eselon 1 --}}
                        <div style="width:100px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:12px; font-weight:800; color:var(--text-primary); text-align:right; flex-shrink:0;">
                            <span id="chevron-{{ $e1['code'] }}" style="font-size:9px; color:var(--text-secondary); transition:transform 0.2s ease;">▶</span>
                            <span style="letter-spacing:0.5px;">{{ $e1['code'] }}</span>
                        </div>

                        {{-- Sumbu X: Bar IDP Coverage Eselon 1 --}}
                        <div style="flex:1; background:rgba(255,255,255,0.08); border-radius:4px; height:16px; position:relative; overflow:hidden;">
                            {{-- Subtle Grid markers at 25%, 50%, 75% --}}
                            <div style="position:absolute; left:25%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.08); z-index:1;"></div>
                            <div style="position:absolute; left:50%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.08); z-index:1;"></div>
                            <div style="position:absolute; left:75%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.08); z-index:1;"></div>
                            
                            {{-- Bar Fill --}}
                            <div style="height:100%; width:{{ min($e1['coverage'], 100) }}%; background:{{ $e1RiskGradient }}; border-radius:4px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); position:relative; z-index:2;"></div>
                        </div>

                        {{-- Value & Child Count --}}
                        <div style="width:55px; font-size:12px; font-weight:800; color:{{ $e1RiskColor }}; text-align:right; flex-shrink:0;">
                            {{ $e1['coverage'] }}%
                        </div>
                    </div>

                    {{-- Cascading Children Container (Eselon 2) --}}
                    <div id="children-{{ $e1['code'] }}" 
                         class="ncc-eselon2-children" 
                         style="display:none; flex-direction:column; gap:5px; padding-left:14px; margin-left:10px; border-left:2px dashed rgba(255,255,255,0.12); margin-top:2px; margin-bottom:4px;">
                        
                        @foreach($e1['children'] as $child)
                        @php
                            $childRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                            $childRiskGradient = $child['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($child['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                        @endphp
                        <div class="ncc-eselon2-row" 
                             data-code="{{ $child['code'] }}" 
                             data-name="{{ $child['unit'] }}" 
                             data-coverage="{{ $child['coverage'] }}" 
                             data-risk="{{ $child['risk'] }}"
                             title="{{ $child['unit'] }} ({{ $child['coverage'] }}% IDP Coverage · {{ $child['non_jfa_path'] }} Non-JFA · {{ $child['strategic_direction'] }} Arahan)"
                             style="display:flex; align-items:center; gap:10px; padding:5px 8px; background:rgba(255,255,255,0.015); border:1px solid rgba(255,255,255,0.03); border-radius:5px; transition: all 0.2s ease;">
                            
                            {{-- Sumbu Y: Kode Eselon 2 --}}
                            <div style="width:86px; font-size:10.5px; font-weight:700; color:var(--text-secondary); text-align:right; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:help;" title="{{ $child['unit'] }}">
                                {{ $child['code'] }}
                            </div>

                            {{-- Sumbu X: Bar IDP Coverage Eselon 2 --}}
                            <div style="flex:1; background:rgba(255,255,255,0.05); border-radius:3px; height:11px; position:relative; overflow:hidden;">
                                <div style="position:absolute; left:25%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.06); z-index:1;"></div>
                                <div style="position:absolute; left:50%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.06); z-index:1;"></div>
                                <div style="position:absolute; left:75%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.06); z-index:1;"></div>
                                
                                <div style="height:100%; width:{{ min($child['coverage'], 100) }}%; background:{{ $childRiskGradient }}; border-radius:3px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); position:relative; z-index:2;"></div>
                            </div>

                            {{-- Value --}}
                            <div style="width:55px; font-size:10.5px; font-weight:700; color:{{ $childRiskColor }}; text-align:right; flex-shrink:0;">
                                {{ $child['coverage'] }}%
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if(empty($unitStatsPusatEselon1))
                <div class="ncc-empty-pusat" style="text-align:center; padding:32px; color:var(--text-secondary); font-size:12px;">Tidak ada unit kerja pusat yang cocok.</div>
                @endif
            </div>
        </div>

        {{-- SEBELAH KANAN: Unit Kerja Kantor Perwakilan (Per Wilayah / Pulau & Cascading) --}}
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.05); flex-wrap:wrap; gap:8px;">
                <div>
                    <div style="font-size:14px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                        <span>📍 Perwakilan</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <button type="button" id="btn-toggle-all-pw" onclick="toggleAllWilayah()" class="btn btn-neutral" style="padding:3px 8px; font-size:11px; height:26px; border-radius:4px; display:flex; align-items:center; gap:4px; cursor:pointer;">
                        <span id="btn-toggle-all-pw-icon">📂</span>
                        <span id="btn-toggle-all-pw-text">Buka Semua</span>
                    </button>
                    <div style="text-align:right;">
                        <span style="font-size:11px; color:var(--text-secondary);">Rata-rata:</span>
                        <span style="font-size:13px; font-weight:700; color:{{ $avgPerwakilan >= 75 ? 'var(--success)' : ($avgPerwakilan >= 50 ? 'var(--warning)' : 'var(--danger)') }}">{{ $avgPerwakilan }}%</span>
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

            {{-- Chart Items Container (Cascading Wilayah -> PW Unit) --}}
            <div id="ncc-perwakilan-container" style="display:flex; flex-direction:column; gap:10px; max-height:650px; overflow-y:auto; padding-right:4px;">
                @foreach($unitStatsPerwakilanWilayah as $w)
                @php
                    $wRiskColor = $w['risk'] === 'Tinggi' ? 'var(--danger)' : ($w['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                    $wRiskGradient = $w['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($w['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                @endphp
                <div class="ncc-wilayah-group" 
                     id="group-w-{{ $w['code'] }}" 
                     data-code="{{ $w['code'] }}" 
                     data-name="{{ $w['name'] }}" 
                     data-coverage="{{ $w['coverage'] }}" 
                     data-risk="{{ $w['risk'] }}"
                     style="display:flex; flex-direction:column; gap:4px;">
                    
                    {{-- Master Row: Wilayah / Pulau --}}
                    <div class="ncc-wilayah-row" 
                         onclick="toggleWilayah('{{ $w['code'] }}')"
                         title="{{ $w['name'] }} ({{ $w['coverage'] }}% IDP Coverage · {{ count($w['children']) }} Unit Perwakilan) - Klik untuk cascading"
                         style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:rgba(255,255,255,0.035); border:1px solid rgba(255,255,255,0.08); border-radius:6px; cursor:pointer; transition: all 0.2s ease;">
                        
                        {{-- Sumbu Y: Toggle Chevron & Nama Wilayah --}}
                        <div style="width:100px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:11.5px; font-weight:800; color:var(--text-primary); text-align:right; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <span id="chevron-w-{{ $w['code'] }}" style="font-size:9px; color:var(--text-secondary); transition:transform 0.2s ease;">▶</span>
                            <span>{{ $w['short'] }}</span>
                        </div>

                        {{-- Sumbu X: Bar IDP Coverage Wilayah --}}
                        <div style="flex:1; background:rgba(255,255,255,0.08); border-radius:4px; height:16px; position:relative; overflow:hidden;">
                            <div style="position:absolute; left:25%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.08); z-index:1;"></div>
                            <div style="position:absolute; left:50%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.08); z-index:1;"></div>
                            <div style="position:absolute; left:75%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.08); z-index:1;"></div>
                            
                            <div style="height:100%; width:{{ min($w['coverage'], 100) }}%; background:{{ $wRiskGradient }}; border-radius:4px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); position:relative; z-index:2;"></div>
                        </div>

                        {{-- Value --}}
                        <div style="width:55px; font-size:12px; font-weight:800; color:{{ $wRiskColor }}; text-align:right; flex-shrink:0;">
                            {{ $w['coverage'] }}%
                        </div>
                    </div>

                    {{-- Cascading Children Container (PW Units) --}}
                    <div id="children-w-{{ $w['code'] }}" 
                         class="ncc-pw-children" 
                         style="display:none; flex-direction:column; gap:5px; padding-left:14px; margin-left:10px; border-left:2px dashed rgba(255,255,255,0.12); margin-top:2px; margin-bottom:4px;">
                        
                        @foreach($w['children'] as $child)
                        @php
                            $pwRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                            $pwRiskGradient = $child['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($child['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
                        @endphp
                        <div class="ncc-pw-row" 
                             data-code="{{ $child['code'] }}" 
                             data-name="{{ $child['unit'] }}" 
                             data-coverage="{{ $child['coverage'] }}" 
                             data-risk="{{ $child['risk'] }}"
                             title="{{ $child['unit'] }} ({{ $child['coverage'] }}% IDP Coverage · {{ $child['non_jfa_path'] }} Non-JFA · {{ $child['strategic_direction'] }} Arahan)"
                             style="display:flex; align-items:center; gap:10px; padding:5px 8px; background:rgba(255,255,255,0.015); border:1px solid rgba(255,255,255,0.03); border-radius:5px; transition: all 0.2s ease;">
                            
                            {{-- Sumbu Y: Kode Perwakilan --}}
                            <div style="width:86px; font-size:10.5px; font-weight:700; color:var(--text-secondary); text-align:right; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:help;" title="{{ $child['unit'] }}">
                                {{ $child['code'] }}
                            </div>

                            {{-- Sumbu X: Bar IDP Coverage PW --}}
                            <div style="flex:1; background:rgba(255,255,255,0.05); border-radius:3px; height:11px; position:relative; overflow:hidden;">
                                <div style="position:absolute; left:25%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.06); z-index:1;"></div>
                                <div style="position:absolute; left:50%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.06); z-index:1;"></div>
                                <div style="position:absolute; left:75%; top:0; bottom:0; width:1px; background:rgba(255,255,255,0.06); z-index:1;"></div>
                                
                                <div style="height:100%; width:{{ min($child['coverage'], 100) }}%; background:{{ $pwRiskGradient }}; border-radius:3px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); position:relative; z-index:2;"></div>
                            </div>

                            {{-- Value --}}
                            <div style="width:55px; font-size:10.5px; font-weight:700; color:{{ $pwRiskColor }}; text-align:right; flex-shrink:0;">
                                {{ $child['coverage'] }}%
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if(empty($unitStatsPerwakilanWilayah))
                <div class="ncc-empty-perwakilan" style="text-align:center; padding:32px; color:var(--text-secondary); font-size:12px;">Data tidak tersedia.</div>
                @endif
            </div>
        </div>

    </div>

</div>
@elseif($activeRole === 'deputi')
{{-- Grafik Coverage IDP Deputi --}}
<div class="card mb-4" style="padding:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:16px;">
        <div>
            <div class="card-title" style="margin-bottom:4px;">📊 Persentase IDP Coverage per Unit Kerja</div>
        </div>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.05); flex-wrap:wrap; gap:8px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="text-align:right;">
                @php
                    $totalCov = 0;
                    $totalCount = count($deputiUnits);
                    foreach($deputiUnits as $u) $totalCov += $u['coverage'];
                    $avgCov = $totalCount > 0 ? round($totalCov / $totalCount) : 0;
                @endphp
                <span style="font-size:11px; color:var(--text-secondary);">Rata-rata IDP Coverage:</span>
                <span style="font-size:13px; font-weight:700; color:{{ $avgCov >= 75 ? 'var(--success)' : ($avgCov >= 50 ? 'var(--warning)' : 'var(--danger)') }}">{{ $avgCov }}%</span>
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

    <div style="display:flex; flex-direction:column; gap:10px; max-height:650px; overflow-y:auto; padding-right:4px;">
        @if(!empty($deputiUnits))
            @foreach($deputiUnits as $child)
            @php
                $childRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                $childRiskGradient = $child['risk'] === 'Tinggi' ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($child['risk'] === 'Sedang' ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)');
            @endphp
            <div title="{{ $child['unit'] }} ({{ $child['coverage'] }}%)"
                 style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:rgba(255,255,255,0.035); border:1px solid rgba(255,255,255,0.08); border-radius:6px; margin-bottom:4px;">
                <div style="width:100px; display:flex; align-items:center; justify-content:flex-end; gap:4px; font-size:12px; font-weight:800; color:var(--text-primary); text-align:right;">
                    <span>{{ $child['code'] }}</span>
                </div>
                <div style="flex:1; background:rgba(255,255,255,0.08); border-radius:4px; height:16px; position:relative; overflow:hidden;">
                    <div style="position:absolute; left:25%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                    <div style="position:absolute; left:50%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                    <div style="position:absolute; left:75%; width:1px; height:100%; background:rgba(255,255,255,0.08);"></div>
                    <div style="height:100%; width:{{ min($child['coverage'], 100) }}%; background:{{ $childRiskGradient }}; border-radius:4px;"></div>
                </div>
                <div style="width:55px; font-size:12px; font-weight:800; color:{{ $childRiskColor }}; text-align:right;">
                    {{ $child['coverage'] }}%
                </div>
            </div>
            @endforeach
        @else
        <div style="text-align:center; padding:32px; color:var(--text-secondary); font-size:12px;">Data tidak tersedia.</div>
        @endif
    </div>
</div>
@endif

{{-- Data Table Section --}}
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <div class="card-title" style="margin-bottom:0;">📋 Tabel Ringkas Nasional</div>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                    <th style="width:90px; text-align:left; padding:10px 8px;">Kode</th>
                    <th style="text-align:left; padding:10px 8px;">Unit Kerja</th>
                    <th style="width:170px; text-align:left; padding:10px 8px;">% IDP Coverage</th>
                    <th style="width:120px; text-align:center; padding:10px 8px;" title="Jumlah Bangkom Unit">Bangkom Unit</th>
                    <th style="width:140px; text-align:center; padding:10px 8px;" title="Jumlah Bangkom Realisasi">Realisasi Bangkom</th>
                    <th style="width:140px; text-align:center; padding:10px 8px;" title="Jumlah Strategic Direction">Strategic Direction</th>
                </tr>
            </thead>
            <tbody id="ncc-table-body">
                @if(in_array($activeRole, ['sesma', 'admin', 'karoSDM', 'kombinasi', 'bangkom']))
                @foreach($tableCategories as $catKey => $cat)
                @if(!empty($cat['groups']))
                {{-- Category Header Row --}}
                <tr class="ncc-cat-header-row" data-cat="{{ $catKey }}" style="background:rgba(255,255,255,0.04); border-top:1px solid rgba(255,255,255,0.08); border-bottom:1px solid rgba(255,255,255,0.08);">
                    <td colspan="6" style="padding:8px 12px; font-weight:800; font-size:11.5px; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-primary);">
                        <span class="badge {{ $cat['badge_class'] }}" style="font-size:10px; margin-right:6px;">{{ $cat['badge'] }}</span>
                        {{ $cat['title'] }}
                    </td>
                </tr>

                @foreach($cat['groups'] as $gIdx => $group)
                @php
                    $gRiskColor = $group['risk'] === 'Tinggi' ? 'var(--danger)' : ($group['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                    $groupId = 't-group-' . $catKey . '-' . preg_replace('/[^a-zA-Z0-9]/', '', $group['code']);
                @endphp
                {{-- Parent Row --}}
                <tr class="ncc-table-parent-row" 
                    id="row-{{ $groupId }}"
                    data-group-id="{{ $groupId }}"
                    data-code="{{ $group['code'] }}"
                    data-name="{{ $group['name'] }}"
                    data-coverage="{{ $group['coverage'] }}"
                    onclick="toggleTableParent('{{ $groupId }}')"
                    style="cursor:pointer; background:rgba(255,255,255,0.02); border-bottom:1px solid rgba(255,255,255,0.04); font-weight:600;">
                    
                    <td style="padding:9px 8px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span id="t-chevron-{{ $groupId }}" style="font-size:9px; color:var(--text-secondary); transition:transform 0.2s ease;">▶</span>
                            <span class="badge badge-neutral" style="font-size:11px; font-weight:800; letter-spacing:0.5px;">{{ $group['code'] }}</span>
                        </div>
                    </td>
                    <td style="padding:9px 8px;">
                        <div style="font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                            <span>{{ $group['name'] }}</span>
                        </div>
                    </td>
                    <td style="padding:9px 8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="background:rgba(255,255,255,0.08); border-radius:3px; height:7px; width:80px; overflow:hidden; flex-shrink:0;">
                                <div style="height:100%; width:{{ min($group['coverage'],100) }}%; background:{{ $gRiskColor }};"></div>
                            </div>
                            <span style="font-size:12.5px; font-weight:700; color:{{ $gRiskColor }};">{{ $group['coverage'] }}%</span>
                        </div>
                    </td>
                    <td style="padding:9px 8px; text-align:center;">
                        <span class="badge badge-neutral" style="font-size:11px; font-weight:700;">{{ $group['bangkom_count'] }}</span>
                    </td>
                    <td style="padding:9px 8px; text-align:center;">
                        <span class="badge {{ $group['bangkom_realisasi_count'] > 0 ? 'badge-success' : 'badge-neutral' }}" style="font-size:11px; font-weight:700;">{{ $group['bangkom_realisasi_count'] }}</span>
                    </td>
                    <td style="padding:9px 8px; text-align:center;">
                        <span class="badge badge-info" style="font-size:11px; font-weight:700;">{{ $group['strategic_direction'] }}</span>
                    </td>
                </tr>

                {{-- Children Rows --}}
                @foreach($group['children'] as $child)
                @php
                    $cRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                @endphp
                <tr class="ncc-table-child-row child-of-{{ $groupId }}" 
                    data-parent-id="{{ $groupId }}"
                    data-code="{{ $child['code'] }}"
                    data-name="{{ $child['unit'] }}"
                    data-coverage="{{ $child['coverage'] }}"
                    style="display:none; background:rgba(255,255,255,0.008); border-bottom:1px solid rgba(255,255,255,0.025); font-size:12px;">
                    
                    <td style="padding:7px 8px 7px 22px;">
                        <span class="badge badge-neutral" style="font-size:10px; font-weight:700;">{{ $child['code'] }}</span>
                    </td>
                    <td style="padding:7px 8px 7px 18px; color:var(--text-secondary);">
                        {{ $child['unit'] }}
                    </td>
                    <td style="padding:7px 8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="background:rgba(255,255,255,0.06); border-radius:3px; height:5px; width:70px; overflow:hidden; flex-shrink:0;">
                                <div style="height:100%; width:{{ min($child['coverage'],100) }}%; background:{{ $cRiskColor }};"></div>
                            </div>
                            <span style="font-size:11.5px; font-weight:600; color:{{ $cRiskColor }};">{{ $child['coverage'] }}%</span>
                        </div>
                    </td>
                    <td style="padding:7px 8px; text-align:center;">
                        <span style="font-size:11px;">{{ $child['bangkom_count'] }}</span>
                    </td>
                    <td style="padding:7px 8px; text-align:center;">
                        <span style="font-size:11px; color:{{ $child['bangkom_realisasi_count'] > 0 ? 'var(--success)' : 'var(--text-secondary)' }}; font-weight:{{ $child['bangkom_realisasi_count'] > 0 ? '700' : '400' }};">{{ $child['bangkom_realisasi_count'] }}</span>
                    </td>
                    <td style="padding:7px 8px; text-align:center;">
                        <span style="font-size:11px;">{{ $child['strategic_direction'] }}</span>
                    </td>
                </tr>
                @endforeach

                @endforeach
                @endif
                @endforeach
                
                @elseif($activeRole === 'deputi')
                @foreach($deputiUnits as $child)
                @php
                    $cRiskColor = $child['risk'] === 'Tinggi' ? 'var(--danger)' : ($child['risk'] === 'Sedang' ? 'var(--warning)' : 'var(--success)');
                @endphp
                <tr class="ncc-table-child-row" 
                    style="background:rgba(255,255,255,0.008); border-bottom:1px solid rgba(255,255,255,0.025); font-size:12px;">
                    
                    <td style="padding:7px 8px 7px 22px;">
                        <span class="badge badge-neutral" style="font-size:10px; font-weight:700;">{{ $child['code'] }}</span>
                    </td>
                    <td style="padding:7px 8px 7px 18px; color:var(--text-primary); font-weight:600;">
                        {{ $child['unit'] }}
                    </td>
                    <td style="padding:7px 8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="background:rgba(255,255,255,0.06); border-radius:3px; height:5px; width:70px; overflow:hidden; flex-shrink:0;">
                                <div style="height:100%; width:{{ min($child['coverage'],100) }}%; background:{{ $cRiskColor }};"></div>
                            </div>
                            <span style="font-size:11.5px; font-weight:600; color:{{ $cRiskColor }};">{{ $child['coverage'] }}%</span>
                        </div>
                    </td>
                    <td style="padding:7px 8px; text-align:center;">
                        <span style="font-size:11px;">{{ $child['bangkom_count'] }}</span>
                    </td>
                    <td style="padding:7px 8px; text-align:center;">
                        <span style="font-size:11px; color:{{ $child['bangkom_realisasi_count'] > 0 ? 'var(--success)' : 'var(--text-secondary)' }}; font-weight:{{ $child['bangkom_realisasi_count'] > 0 ? '700' : '400' }};">{{ $child['bangkom_realisasi_count'] }}</span>
                    </td>
                    <td style="padding:7px 8px; text-align:center;">
                        <span style="font-size:11px;">{{ $child['strategic_direction'] }}</span>
                    </td>
                </tr>
                @endforeach
                @if(empty($deputiUnits))
                <tr>
                    <td colspan="6" style="text-align:center; padding:32px; color:var(--text-secondary); font-size:12px;">Data tidak tersedia.</td>
                </tr>
                @endif
                @endif
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
let allEselon1Expanded = false;
let allWilayahExpanded = false;
let allTableExpanded = false;

function toggleEselon1(code) {
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

function toggleAllEselon1() {
    allEselon1Expanded = !allEselon1Expanded;
    const containers = document.querySelectorAll('.ncc-eselon2-children');
    const chevrons = document.querySelectorAll('[id^="chevron-SU"], [id^="chevron-D"], [id^="chevron-IN"], [id^="chevron-Pusat"], [id^="chevron-PST"]');
    const btnIcon = document.getElementById('btn-toggle-all-icon');
    const btnText = document.getElementById('btn-toggle-all-text');

    containers.forEach(c => {
        c.style.display = allEselon1Expanded ? 'flex' : 'none';
    });

    chevrons.forEach(ch => {
        ch.style.transform = allEselon1Expanded ? 'rotate(90deg)' : 'rotate(0deg)';
    });

    if (btnIcon && btnText) {
        btnIcon.textContent = allEselon1Expanded ? '📁' : '📂';
        btnText.textContent = allEselon1Expanded ? 'Tutup Semua' : 'Buka Semua';
    }
}

function toggleWilayah(code) {
    const childrenContainer = document.getElementById('children-w-' + code);
    const chevron = document.getElementById('chevron-w-' + code);
    
    if (childrenContainer) {
        const isHidden = childrenContainer.style.display === 'none' || childrenContainer.style.display === '';
        childrenContainer.style.display = isHidden ? 'flex' : 'none';
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
        }
    }
}

function toggleAllWilayah() {
    allWilayahExpanded = !allWilayahExpanded;
    const containers = document.querySelectorAll('.ncc-pw-children');
    const chevrons = document.querySelectorAll('[id^="chevron-w-"]');
    const btnIcon = document.getElementById('btn-toggle-all-pw-icon');
    const btnText = document.getElementById('btn-toggle-all-pw-text');

    containers.forEach(c => {
        c.style.display = allWilayahExpanded ? 'flex' : 'none';
    });

    chevrons.forEach(ch => {
        ch.style.transform = allWilayahExpanded ? 'rotate(90deg)' : 'rotate(0deg)';
    });

    if (btnIcon && btnText) {
        btnIcon.textContent = allWilayahExpanded ? '📁' : '📂';
        btnText.textContent = allWilayahExpanded ? 'Tutup Semua' : 'Buka Semua';
    }
}

function toggleTableParent(groupId) {
    const childRows = document.querySelectorAll('.child-of-' + groupId);
    const chevron = document.getElementById('t-chevron-' + groupId);
    
    if (childRows.length > 0) {
        const isHidden = childRows[0].style.display === 'none' || childRows[0].style.display === '';
        childRows.forEach(r => {
            r.style.display = isHidden ? '' : 'none';
        });
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
        }
    }
}

function toggleAllTable() {
    allTableExpanded = !allTableExpanded;
    const childRows = document.querySelectorAll('.ncc-table-child-row');
    const chevrons = document.querySelectorAll('[id^="t-chevron-"]');
    const btnIcon = document.getElementById('btn-toggle-all-table-icon');
    const btnText = document.getElementById('btn-toggle-all-table-text');

    childRows.forEach(r => {
        r.style.display = allTableExpanded ? '' : 'none';
    });

    chevrons.forEach(ch => {
        ch.style.transform = allTableExpanded ? 'rotate(90deg)' : 'rotate(0deg)';
    });

    if (btnIcon && btnText) {
        btnIcon.textContent = allTableExpanded ? '📁' : '📂';
        btnText.textContent = allTableExpanded ? 'Tutup Semua Tabel' : 'Buka Semua Tabel';
    }
}
</script>
@endpush
@endsection
