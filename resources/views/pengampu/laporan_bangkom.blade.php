@extends('layouts.app')
@section('title', 'Laporan Bangkom Unit')
@section('header_title', 'Laporan Bangkom Unit')

@section('content')
<div class="page-header">
    <h1>📊 Laporan Bangkom Unit</h1>
    <span class="badge badge-neutral">Unit: {{ auth()->user()->scope }}</span>
</div>

{{-- KPI Summary --}}
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value">{{ $totalPlan }}</div>
        <div class="stat-label">Total Rencana Kegiatan</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $totalRealisasi }}</div>
        <div class="stat-label">Terealisasi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-value">{{ $realizationRate }}%</div>
        <div class="stat-label">Realization Rate</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value">{{ $totalJP }}</div>
        <div class="stat-label">Total JP Terealisasi</div>
    </div>
</div>

{{-- Tabel Laporan --}}
<div class="card">
    <div class="card-title">Riwayat Kegiatan Bangkom</div>
    <table>
        <thead>
            <tr><th>Nama Kegiatan</th><th>Kompetensi</th><th>Tanggal</th><th>JP Rencana</th><th>JP Realisasi</th><th>Peserta</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse($plans as $p)
            <tr>
                <td><strong>{{ $p->title }}</strong></td>
                <td>{{ $p->competency }}</td>
                <td>{{ $p->tgl_mulai }} s/d {{ $p->tgl_selesai }}</td>
                <td>{{ $p->jp }} JP</td>
                <td>{{ $p->realised_jp ? $p->realised_jp . ' JP' : '-' }}</td>
                <td>{{ $p->realised_participants_count ?? '-' }}</td>
                <td>
                    @php
                        $statusColors = ['Draft'=>'badge-neutral','Menunggu Penetapan'=>'badge-warning','Ditetapkan'=>'badge-success','Perlu Revisi'=>'badge-danger','Pembatalan Diajukan'=>'badge-warning','Realisasi'=>'badge-info'];
                        $sc = $statusColors[$p->status] ?? 'badge-neutral';
                    @endphp
                    <span class="badge {{ $sc }}">{{ $p->status }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada data laporan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
