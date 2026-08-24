@extends('layouts.app')
@section('title', 'Competency Heatmap')
@section('header_title', 'Competency Heatmap')

@section('content')
<div class="page-header">
    <h1>🗺️ Competency Heatmap</h1>
</div>

{{-- Summary stats --}}
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon">🧠</div>
        <div class="stat-value">{{ count($competencyStats) }}</div>
        <div class="stat-label">Jenis Kompetensi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-value">{{ collect($competencyStats)->sum('demand') }}</div>
        <div class="stat-label">Total Demand</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ collect($competencyStats)->sum('realized') }}</div>
        <div class="stat-label">Total Terealisasi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value">{{ collect($competencyStats)->sum('jp') }}</div>
        <div class="stat-label">Total JP</div>
    </div>
</div>

{{-- Heatmap Grid --}}
<div class="card">
    <div class="card-title">📊 Peta Kompetensi Seluruh Unit</div>
    <p class="text-sm text-muted" style="margin-bottom:16px;">Intensitas warna menunjukkan besarnya demand kompetensi. Hover untuk detail.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;">
        @php $maxDemand = max(1, collect($competencyStats)->pluck('demand')->max()); @endphp
        @foreach($competencyStats as $cs)
        @php
            $pct = min(100, round($cs['demand'] / $maxDemand * 100));
            $realPct = $cs['demand'] > 0 ? round($cs['realized'] / $cs['demand'] * 100) : 0;
        @endphp
        <div title="{{ $cs['competency'] }}: {{ $cs['demand'] }} demand, {{ $cs['realized'] }} terealisasi"
             style="background:rgba(45,140,240,{{ 0.08 + ($pct/100)*0.45 }});border:1px solid rgba(45,140,240,{{ 0.15 + ($pct/100)*0.5 }});border-radius:10px;padding:14px;transition:transform .2s;"
             onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
            <div style="font-weight:600;font-size:13px;color:var(--text-primary);margin-bottom:6px;">{{ $cs['competency'] }}</div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-secondary);margin-bottom:8px;">
                <span>{{ $cs['demand'] }} demand</span>
                <span>{{ $cs['jp'] }} JP</span>
            </div>
            <div style="background:rgba(255,255,255,0.1);border-radius:6px;height:4px;overflow:hidden;">
                <div style="background:{{ $realPct >= 80 ? 'var(--success)' : ($realPct >= 50 ? 'var(--warning)' : '#ef4444') }};height:100%;width:{{ $realPct }}%;"></div>
            </div>
            <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;">Realisasi: {{ $realPct }}%</div>
        </div>
        @endforeach
        @if(empty($competencyStats))
        <div class="text-muted text-sm col-span-4">Belum ada data kompetensi.</div>
        @endif
    </div>
</div>

{{-- Unit x Competency Matrix --}}
<div class="card" style="margin-top:20px;overflow-x:auto;">
    <div class="card-title">🔢 Matriks Unit × Kompetensi</div>
    <table style="min-width:600px;">
        <thead>
            <tr>
                <th>Unit</th>
                @foreach(collect($competencyStats)->pluck('competency') as $c)
                <th style="font-size:11px;max-width:80px;word-break:break-word;">{{ $c }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($unitMatrix as $unit => $cells)
            <tr>
                <td><strong>{{ $unit }}</strong></td>
                @foreach(collect($competencyStats)->pluck('competency') as $c)
                @php $v = $cells[$c] ?? 0; @endphp
                <td style="text-align:center;background:rgba(45,140,240,{{ $v > 0 ? min(0.5, $v * 0.15) : 0 }});">
                    {{ $v > 0 ? $v : '' }}
                </td>
                @endforeach
            </tr>
            @endforeach
            @if(empty($unitMatrix))
            <tr><td colspan="99" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada data.</td></tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
