@extends('layouts.app')
@section('title', 'Executive Dashboard')
@section('header_title', 'Executive Dashboard - ATLAS')

@section('content')
<div class="page-header">
    <h1>🏢 Executive Dashboard</h1>
    <span class="badge badge-neutral">{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</span>
</div>

{{-- KPI Cards --}}
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value">{{ $totalPlans }}</div>
        <div class="stat-label">Total Rencana Bangkom</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $totalRealized }}</div>
        <div class="stat-label">Terealisasi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-value">{{ $realizationRate }}%</div>
        <div class="stat-label">Overall Realization Rate</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value">{{ $totalJP }}</div>
        <div class="stat-label">Total JP Terealisasi</div>
    </div>
</div>

{{-- Unit Performance --}}
<div class="card mb-4">
    <div class="card-title">🏆 Performa Bangkom Per Unit</div>
    <table>
        <thead>
            <tr><th>Unit</th><th>Rencana</th><th>Terealisasi</th><th>JP Total</th><th>Realization Rate</th><th>Progress</th></tr>
        </thead>
        <tbody>
            @forelse($unitPerformance as $up)
            <tr>
                <td><strong>{{ $up['unit'] }}</strong></td>
                <td>{{ $up['total_plans'] }}</td>
                <td>{{ $up['realized'] }}</td>
                <td>{{ $up['total_jp'] }} JP</td>
                <td>
                    <span style="color:{{ $up['rate'] >= 80 ? 'var(--success)' : ($up['rate'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};font-weight:600;">
                        {{ $up['rate'] }}%
                    </span>
                </td>
                <td style="min-width:120px;">
                    <div style="background:rgba(255,255,255,0.1);border-radius:10px;height:6px;overflow:hidden;">
                        <div style="background:{{ $up['rate'] >= 80 ? 'var(--success)' : ($up['rate'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};height:100%;width:{{ min($up['rate'], 100) }}%;transition:width .4s;"></div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada data unit.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Competency Heatmap (simplified list) --}}
<div class="card">
    <div class="card-title">🗺️ Peta Kompetensi (Top Demand)</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
        @foreach($competencyDemand as $cd)
        @php
            $intensity = min(1, $cd['count'] / max(collect($competencyDemand)->pluck('count')->max(), 1));
            $bg = "rgba(45, 140, 240, " . (0.1 + $intensity * 0.4) . ")";
        @endphp
        <div style="background:{{ $bg }};border:1px solid rgba(45,140,240,{{ 0.2 + $intensity * 0.3 }});border-radius:8px;padding:12px 14px;">
            <div style="font-weight:600;font-size:13px;color:var(--text-primary);">{{ $cd['competency'] }}</div>
            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">{{ $cd['count'] }} demand · {{ $cd['jp'] }} JP</div>
        </div>
        @endforeach
        @if(empty($competencyDemand))
        <div class="text-muted text-sm">Belum ada data kompetensi.</div>
        @endif
    </div>
</div>
@endsection
