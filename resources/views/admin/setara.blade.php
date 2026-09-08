@extends('layouts.app')
@section('title', 'SETARA Integration')
@section('header_title', 'Monitor Integrasi SETARA')

@section('content')
<div class="page-header">
    <h1>🔗 Monitor Integrasi SETARA</h1>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">✅ {{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;">
    @foreach($datasets as $ds)
    <div style="background:#F8FAFC;border:1px solid rgba({{ $ds['status'] === 'Sehat' ? '34,197,94' : '239,68,68' }},0.25);border-radius:12px;padding:20px;">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px;">
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">{{ $ds['app'] }}</div>
                <div style="font-weight:600;font-size:14px;color:var(--text-primary);">{{ $ds['name'] }}</div>
            </div>
            @if($ds['status'] === 'Sehat')
            <span class="badge badge-success">✓ Sehat</span>
            @else
            <span class="badge badge-danger">⚠ Masalah</span>
            @endif
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;color:var(--text-secondary);margin-bottom:14px;">
            <div>
                <div style="color:var(--text-secondary);">Refresh Terakhir</div>
                <div style="color:var(--text-primary);font-weight:500;margin-top:2px;">{{ $ds['refresh'] }}</div>
            </div>
            <div>
                <div style="color:var(--text-secondary);">Isu Terdeteksi</div>
                <div style="color:var(--text-primary);font-weight:500;margin-top:2px;">{{ $ds['issue'] }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.setara.refresh', $ds['id']) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-neutral" style="width:100%;">🔄 Trigger Sinkronisasi Manual</button>
        </form>
    </div>
    @endforeach
</div>

<div class="card" style="margin-top:20px;">
    <div class="card-title">ℹ️ Tentang SETARA</div>
    <p class="text-sm text-muted" style="line-height:1.8;">
        SETARA (Sistem Terintegrasi Data SDM BPKP) adalah API Gateway yang menghubungkan ATLAS dengan sistem eksternal:
        <strong>SMILE</strong> (Master Data Pegawai), <strong>COMPASS</strong> (Data Assessment Kompetensi),
        <strong>SIMPEL</strong> (Nominasi Pelatihan Formal), <strong>INTERNA</strong> (Katalog Program Internal),
        dan <strong>SITUBEL</strong> (Data Tugas Belajar).
    </p>
    <p class="text-sm text-muted" style="margin-top:8px;">
        Sinkronisasi otomatis berjalan setiap hari pukul 08.00 WIB. Jika ada ketidaksesuaian data, gunakan tombol "Trigger Sinkronisasi Manual" untuk memperbarui secara manual melalui API Gateway.
    </p>
</div>
@endsection
