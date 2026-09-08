@extends('layouts.app')
@section('title', 'Report Generator')
@section('header_title', 'Report & Brief Generator')

@section('content')
<div class="page-header">
    <h1>📄 Report & Brief Generator</h1>
    <a href="{{ route('executive.exportXlsx') }}" class="btn btn-success">⬇️ Export Excel (CSV)</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    {{-- Generator Form --}}
    <div class="card">
        <div class="card-title">🛠️ Pilih Jenis Laporan</div>
        <form method="POST" action="{{ route('executive.generateBrief') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">
                @php
                    $reportTypes = [
                        'koordinator_monitoring' => ['icon' => '📊', 'label' => 'Monitoring Orkestrasi 3 Stream', 'desc' => 'Laporan status 3 stream: Penilaian, Pengembangan, Pembinaan SDM'],
                        'karo_brief' => ['icon' => '📋', 'label' => 'Executive Brief Kepala Biro', 'desc' => 'Brief prioritas kebijakan dan intervensi unit berisiko'],
                        'risk_units' => ['icon' => '⚠️', 'label' => 'Analisis Unit Berisiko Tinggi', 'desc' => 'Daftar unit dengan IDP coverage di bawah threshold'],
                        'jfa_non_jfa' => ['icon' => '📈', 'label' => 'Analisis JFA vs Non-JFA', 'desc' => 'Perbandingan coverage dan demand JFA vs Non-JFA'],
                    ];
                @endphp
                @foreach($reportTypes as $val => $info)
                <label style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:8px;border:1px solid #E2E8F0;cursor:pointer;transition:all .2s;background:{{ $reportType === $val ? 'rgba(14,165,233,0.1)' : 'rgba(255,255,255,0.03)' }};"
                    onmouseover="this.style.background='rgba(14,165,233,0.08)'" onmouseout="this.style.background='{{ $reportType === $val ? 'rgba(14,165,233,0.1)' : 'rgba(255,255,255,0.03)' }}'">
                    <input type="radio" name="report_type" value="{{ $val }}" style="margin-top:3px;accent-color:var(--accent);" {{ $reportType === $val ? 'checked' : '' }}>
                    <div>
                        <div style="font-weight:600;font-size:13px;">{{ $info['icon'] }} {{ $info['label'] }}</div>
                        <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">{{ $info['desc'] }}</div>
                    </div>
                </label>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">⚡ Generate Brief</button>
        </form>
    </div>

    {{-- Generated Brief --}}
    <div class="card">
        <div class="card-title">📝 Hasil Brief</div>
        @if($briefContent)
        <div style="background:rgba(0,0,0,0.2);border-radius:8px;padding:16px;font-size:13px;line-height:1.8;color:var(--text-primary);white-space:pre-wrap;font-family:monospace;max-height:500px;overflow-y:auto;">{{ $briefContent }}</div>
        <div style="margin-top:12px;display:flex;gap:8px;">
            <button onclick="copyBrief()" class="btn btn-neutral" style="font-size:12px;">📋 Salin Teks</button>
        </div>
        @else
        <div style="text-align:center;padding:60px 20px;">
            <div style="font-size:48px;margin-bottom:12px;">📝</div>
            <p class="text-muted">Pilih jenis laporan dan klik "Generate Brief" untuk membuat laporan otomatis.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyBrief() {
    const text = document.querySelector('[style*="white-space:pre-wrap"]')?.textContent;
    if (text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Brief berhasil disalin ke clipboard!');
        });
    }
}
</script>
@endpush
