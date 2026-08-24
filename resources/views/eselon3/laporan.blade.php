@extends('layouts.app')
@section('title', 'Laporan Bangkom - Kepala Unit')
@section('header_title', 'Laporan Bangkom Unit')

@section('content')
<div class="page-header">
    <h1>📊 Laporan Bangkom Unit</h1>
</div>

<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value">{{ $totalPlan }}</div>
        <div class="stat-label">Total Rencana</div>
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

<div class="card">
    <div class="card-title">Riwayat Semua Kegiatan Bangkom</div>
    <table>
        <thead>
            <tr><th>Nama Kegiatan</th><th>Kompetensi</th><th>Tanggal</th><th>JP</th><th>JP Realisasi</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse($plans as $p)
            <tr>
                <td><strong>{{ $p->nama_kegiatan }}</strong></td>
                <td>{{ $p->kompetensi_dasar }}</td>
                <td>{{ $p->tanggal_mulai }} s/d {{ $p->tanggal_selesai }}</td>
                <td>{{ $p->jp }}</td>
                <td>{{ $p->realised_jp ?? '-' }}</td>
                <td>
                    @php
                        $statusColors = ['Draft'=>'badge-neutral','Menunggu Penetapan'=>'badge-warning','Ditetapkan'=>'badge-success','Perlu Revisi'=>'badge-danger','Pembatalan Diajukan'=>'badge-warning','Realisasi'=>'badge-info'];
                        $sc = $statusColors[$p->status] ?? 'badge-neutral';
                    @endphp
                    <span class="badge {{ $sc }}">{{ $p->status }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

