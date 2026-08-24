@extends('layouts.app')
@section('title', 'Sestama Brief')
@section('header_title', 'Brading Rapat Pimpinan – Sestama Brief')

@section('content')
<div class="page-header">
    <h1>🏛️ Brading Rapat Pimpinan (Sestama Brief)</h1>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-title">⚡ Generate Sestama Brief</div>
        <p class="text-sm text-muted" style="margin-bottom:20px;">Generate dokumen ringkasan kondisi pengembangan SDM nasional untuk kebutuhan Rapat Pimpinan Sestama.</p>
        <form method="POST" action="{{ route('executive.generateSestamaBrief') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Tanggal Brief</label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Catatan Khusus (Opsional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan konteks atau catatan tambahan untuk brief..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">🏛️ Generate Sestama Brief</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">📄 Dokumen Brief</div>
        @if($briefContent)
        <div style="background:rgba(0,0,0,0.2);border-radius:8px;padding:16px;font-size:13px;line-height:1.8;color:var(--text-primary);white-space:pre-wrap;font-family:monospace;max-height:460px;overflow-y:auto;">{{ $briefContent }}</div>
        <div style="margin-top:12px;">
            <button onclick="navigator.clipboard.writeText(document.querySelector('[style*=pre-wrap]').textContent).then(()=>alert('Disalin!'))" class="btn btn-neutral" style="font-size:12px;width:100%;">📋 Salin ke Clipboard</button>
        </div>
        @else
        <div style="text-align:center;padding:60px 20px;">
            <div style="font-size:48px;margin-bottom:12px;">🏛️</div>
            <p class="text-muted">Brief Sestama akan ditampilkan di sini setelah generate.</p>
        </div>
        @endif
    </div>
</div>
@endsection
