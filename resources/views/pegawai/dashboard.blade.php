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
        <div style="font-size:14px; color:var(--text-primary); margin-bottom:8px; font-weight:600;">📋 IDP Draft</div>
        <div class="stat-value">{{ $draftCount }}</div>
    </div>
    <div class="stat-card">
        <div style="font-size:14px; color:var(--text-primary); margin-bottom:8px; font-weight:600;">✅ IDP Approved</div>
        <div class="stat-value">{{ $agreedCount }}</div>
    </div>
    <div class="stat-card">
        <div style="font-size:14px; color:var(--text-primary); margin-bottom:8px; font-weight:600;">🎯 IDP Coverage</div>
        <div class="stat-value" style="color:{{ $idpCoverage >= 75 ? 'var(--success)' : ($idpCoverage >= 50 ? 'var(--warning)' : 'var(--danger)') }};">{{ $idpCoverage }}%</div>
        <div style="font-size:10px; color:var(--text-secondary); margin-top:2px;">{{ $coverageDetail['matched_count'] }} dari {{ $coverageDetail['total_target'] }} gap terpenuhi</div>
    </div>
    <div class="stat-card">
        <div style="font-size:14px; color:var(--text-primary); margin-bottom:8px; font-weight:600;">📐 Gap Kompetensi Teknis</div>
        @php
            $colorClass = '';
            if ($compassAverage && $compassAverage->nilai_teknis !== null) {
                $gapValue = $compassAverage->nilai_teknis - 78;
                $gapDisplay = number_format($gapValue, 1);
                if ($gapValue > 0) {
                    $gapDisplay = '+' . $gapDisplay;
                    $colorClass = 'color:var(--success);';
                } elseif ($gapValue < 0) {
                    $colorClass = 'color:var(--danger);';
                }
            } else {
                $gapDisplay = '-';
            }
        @endphp
        <div class="stat-value" style="{{ $colorClass }}">{{ $gapDisplay }}</div>
        <div style="font-size:10px; color:var(--text-secondary); margin-top:2px;">Standar Nilai: 78</div>
    </div>
</div>

{{-- Competency Radar (Static Visual) --}}
<div style="margin-bottom:20px;">
    <div class="card">
        <div class="card-title">📊 Profil Kompetensi Saya</div>
        @php
            $hasAnyScore = $compassAverage && ($compassAverage->nilai_teknis !== null || $compassAverage->nilai_mansoskul !== null || $compassAverage->nilai_potensi !== null);
            $hasKompetensi = $compassAverage && ($compassAverage->nilai_teknis !== null || $compassAverage->nilai_mansoskul !== null);
        @endphp

        @if(!$hasAnyScore)
            <div style="padding:20px; text-align:center; background:rgba(255,255,255,0.02); border-radius:8px; border:1px solid rgba(255,255,255,0.05);">
                <p class="text-muted text-sm" style="margin:0;">Anda belum melakukan Penilaian Kompetensi dan Potensi</p>
            </div>
        @else
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px;background:rgba(255,255,255,0.02);padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.05);text-align:center;">
                <div style="padding:4px;">
                    <div style="font-size:14px;color:var(--text-primary);margin-bottom:4px;line-height:1.2;height:24px;">Hasil Penilaian Kompetensi Teknis</div>
                    <div style="font-size:16px;font-weight:700;color:var(--success);">{{ $compassAverage->nilai_teknis !== null ? number_format($compassAverage->nilai_teknis, 1) : '-' }}</div>
                </div>
                <div style="padding:4px;border-left:1px solid rgba(255,255,255,0.08);border-right:1px solid rgba(255,255,255,0.08);">
                    <div style="font-size:14px;color:var(--text-primary);margin-bottom:4px;line-height:1.2;height:24px;">Hasil Penilaian Kompetensi Mansoskul</div>
                    <div style="font-size:16px;font-weight:700;color:var(--warning);">{{ $compassAverage->nilai_mansoskul !== null ? number_format($compassAverage->nilai_mansoskul, 1) : '-' }}</div>
                </div>
                <div style="padding:4px;">
                    <div style="font-size:14px;color:var(--text-primary);margin-bottom:4px;line-height:1.2;height:24px;">Hasil Penilaian Potensi</div>
                    <div style="font-size:16px;font-weight:700;color:var(--info);">{{ $compassAverage->nilai_potensi !== null ? number_format($compassAverage->nilai_potensi, 0) : '-' }}</div>
                </div>
            </div>

            @if(!$hasKompetensi)
                <div style="padding:40px 20px; text-align:center; background:rgba(255,255,255,0.02); border-radius:8px; border:1px solid rgba(255,255,255,0.05); margin-top:20px;">
                    <p class="text-muted text-sm" style="margin:0;">Anda belum melakukan Penilaian Kompetensi</p>
                </div>
            @else
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
            @endif
        @endif
    </div>
</div>

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
