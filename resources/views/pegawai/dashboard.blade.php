@extends('layouts.app')
@section('title', 'Dashboard Pegawai')
@section('header_title', 'Dashboard Saya')

@section('content')
<div class="page-header">
    <h1>👋 Selamat Datang, {{ $employee->name ?? auth()->user()->name }}</h1>
</div>

{{-- KPI Cards --}}
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value">{{ $draftCount }}</div>
        <div class="stat-label">IDP Draft</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $agreedCount }}</div>
        <div class="stat-label">IDP Disepakati</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-value" style="color:{{ $idpCoverage >= 75 ? 'var(--success)' : ($idpCoverage >= 50 ? 'var(--warning)' : 'var(--danger)') }};">{{ $idpCoverage }}%</div>
        <div class="stat-label">IDP Gap Coverage</div>
        <div style="font-size:10px; color:var(--text-secondary); margin-top:2px;">{{ $coverageDetail['matched_count'] }} dari {{ $coverageDetail['total_target'] }} gap terpenuhi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💡</div>
        <div class="stat-value">{{ $recCount }}</div>
        <div class="stat-label">Rekomendasi Sistem</div>
    </div>
</div>

{{-- Competency Radar (Static Visual) --}}
<div style="margin-bottom:20px;">
    <div class="card">
        <div class="card-title">📊 Profil Kompetensi Saya</div>
        @if($compassAverage)
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px;background:rgba(255,255,255,0.02);padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.05);text-align:center;">
            <div style="padding:4px;">
                <div style="font-size:10px;color:var(--text-secondary);margin-bottom:4px;line-height:1.2;height:24px;">Hasil Penilaian Kompetensi Teknis</div>
                <div style="font-size:16px;font-weight:700;color:var(--success);">{{ number_format($compassAverage->nilai_teknis, 1) }}</div>
            </div>
            <div style="padding:4px;border-left:1px solid rgba(255,255,255,0.08);border-right:1px solid rgba(255,255,255,0.08);">
                <div style="font-size:10px;color:var(--text-secondary);margin-bottom:4px;line-height:1.2;height:24px;">Hasil Penilaian Kompetensi Mansoskul</div>
                <div style="font-size:16px;font-weight:700;color:var(--warning);">{{ number_format($compassAverage->nilai_mansoskul, 1) }}</div>
            </div>
            <div style="padding:4px;">
                <div style="font-size:10px;color:var(--text-secondary);margin-bottom:4px;line-height:1.2;height:24px;">Hasil Penilaian Potensi Pegawai</div>
                <div style="font-size:16px;font-weight:700;color:var(--info);">{{ number_format($compassAverage->nilai_potensi, 0) }}</div>
            </div>
        </div>
        @endif
        @if($employee && $employee->assessment)
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:20px;">
            <div style="text-align:center;">
                <div style="font-size:12px; font-weight:700; color:var(--text-secondary); margin-bottom:8px;">Kompetensi Teknis</div>
                <div style="height:320px; position:relative;">
                    <canvas id="techSpiderChart"></canvas>
                </div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:12px; font-weight:700; color:var(--text-secondary); margin-bottom:8px;">Kompetensi Mansoskul</div>
                <div style="height:320px; position:relative;">
                    <canvas id="mansosSpiderChart"></canvas>
                </div>
            </div>
        </div>
        @else
        <p class="text-muted text-sm">Pegawai Non-JFA: profil kompetensi berbasis jabatan.</p>
        <div style="display:flex;flex-direction:column;gap:8px;margin-top:12px;">
            <div style="background:rgba(45,140,240,0.1);border-radius:6px;padding:10px 12px;font-size:13px;">
                <strong>Jabatan pegawai:</strong> {{ $employee->category ?? 'Non-JFA' }}<br>
                <strong>Jabatan:</strong> {{ $employee->role ?? '-' }}<br>
                <strong>Unit:</strong> {{ $employee->unit ?? '-' }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Employee Profile Card --}}
@if($employee)
<div class="card">
    <div class="card-title">👤 Profil Saya</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;font-size:13px;">
        <div><span style="color:var(--text-secondary);">Nama</span><br><strong>{{ $employee->name }}</strong></div>
        <div><span style="color:var(--text-secondary);">NIP</span><br><strong>{{ $employee->id }}</strong></div>
        <div><span style="color:var(--text-secondary);">Jabatan</span><br><strong>{{ $employee->role }}</strong></div>
        <div><span style="color:var(--text-secondary);">Unit</span><br><strong>{{ $employee->unit }}</strong></div>
        <div><span style="color:var(--text-secondary);">Assessment</span><br>
            @if($employee->assessment)
            <span class="badge badge-success">✓ Sudah Assessment</span>
            @else
            <span class="badge badge-neutral">Non-Assessment</span>
            @endif
        </div>
        <div><span style="color:var(--text-secondary);">Potensi</span><br>
            @if($compassAverage && $compassAverage->nilai_potensi !== null)
            <span class="badge badge-success">✓ Sudah</span>
            @else
            <span class="badge badge-neutral">Belum</span>
            @endif
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Technical Competencies Radar Chart
    const techCtx = document.getElementById('techSpiderChart');
    if (techCtx) {
        const techLabels = {!! json_encode($techGaps->pluck('competency_name')) !!};
        const techScores = {!! json_encode($techGaps->pluck('score')) !!};
        const techStds = {!! json_encode($techGaps->pluck('standard')) !!};

        new Chart(techCtx.getContext('2d'), {
            type: 'radar',
            data: {
                labels: techLabels,
                datasets: [
                    {
                        label: 'Nilai Aktual',
                        data: techScores,
                        backgroundColor: 'rgba(45, 140, 240, 0.2)',
                        borderColor: '#2d8cf0',
                        pointBackgroundColor: '#2d8cf0',
                        borderWidth: 2
                    },
                    {
                        label: 'Standar Kebutuhan',
                        data: techStds,
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderColor: '#ef4444',
                        pointBackgroundColor: '#ef4444',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: 'rgba(255, 255, 255, 0.1)' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        pointLabels: {
                            color: '#a0aec0',
                            font: { size: 10 }
                        },
                        ticks: {
                            color: '#718096',
                            backdropColor: 'transparent',
                            beginAtZero: true,
                            max: 100
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#e2e8f0', boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 2. Mansoskul Competencies Radar Chart
    const mansosCtx = document.getElementById('mansosSpiderChart');
    if (mansosCtx) {
        const mansosLabels = {!! json_encode($mansosGaps->pluck('competency_name')) !!};
        const mansosScores = {!! json_encode($mansosGaps->pluck('score')) !!};
        const mansosStds = {!! json_encode($mansosGaps->pluck('standard')) !!};

        new Chart(mansosCtx.getContext('2d'), {
            type: 'radar',
            data: {
                labels: mansosLabels.map(label => label.length > 18 ? label.substring(0, 18) + '...' : label),
                datasets: [
                    {
                        label: 'Nilai Aktual',
                        data: mansosScores,
                        backgroundColor: 'rgba(45, 140, 240, 0.2)',
                        borderColor: '#2d8cf0',
                        pointBackgroundColor: '#2d8cf0',
                        borderWidth: 2
                    },
                    {
                        label: 'Standar Kebutuhan',
                        data: mansosStds,
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderColor: '#ef4444',
                        pointBackgroundColor: '#ef4444',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: 'rgba(255, 255, 255, 0.1)' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        pointLabels: {
                            color: '#a0aec0',
                            font: { size: 10 }
                        },
                        ticks: {
                            color: '#718096',
                            backdropColor: 'transparent',
                            beginAtZero: true,
                            max: 5,
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#e2e8f0', boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
