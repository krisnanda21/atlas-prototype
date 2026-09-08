@extends('layouts.app')
@section('title', 'Referensi & Parameter')
@section('header_title', 'Parameter Referensi Sistem')

@section('content')
<div class="page-header">
    <h1>⚙️ Parameter Referensi Sistem</h1>
    <button class="btn btn-primary" onclick="document.getElementById('modal-add').style.display='flex'">+ Tambah Parameter</button>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">✅ {{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-title">Daftar Parameter Referensi</div>
    <table>
        <thead>
            <tr><th>Kategori</th><th>Key</th><th>Value</th><th>Owner</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($references as $ref)
            <tr>
                <td><span class="badge badge-neutral" style="font-size:10px;">{{ $ref->category }}</span></td>
                <td><code style="background:rgba(255,255,255,0.05);padding:2px 6px;border-radius:4px;font-size:12px;">{{ $ref->key }}</code></td>
                <td>{{ $ref->value }}</td>
                <td>{{ $ref->owner ?? '-' }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.reference.delete', $ref->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus parameter ini?')">🗑 Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:24px;">Belum ada parameter. Klik "Tambah Parameter" untuk membuat yang pertama.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal Add --}}
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:460px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="font-size:16px;font-weight:600;">+ Tambah Parameter Referensi</h2>
            <button onclick="document.getElementById('modal-add').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.reference.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Kategori *</label>
                <input type="text" name="category" class="form-control" required placeholder="Mis: Threshold, Label, Config">
            </div>
            <div class="form-group">
                <label class="form-label">Key *</label>
                <input type="text" name="key" class="form-control" required placeholder="Mis: idp_coverage_threshold">
            </div>
            <div class="form-group">
                <label class="form-label">Value *</label>
                <input type="text" name="value" class="form-control" required placeholder="Mis: 80">
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
                <button type="button" onclick="document.getElementById('modal-add').style.display='none'" class="btn btn-neutral">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection
