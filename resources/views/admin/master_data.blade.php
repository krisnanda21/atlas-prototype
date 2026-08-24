@extends('layouts.app')
@section('title', 'Master Data - Admin')
@section('header_title', 'Kelola Master Data')

@section('content')
<div class="page-header">
    <h1>🗂️ Kelola Master Data</h1>
</div>

{{-- Tabs --}}
<div style="display:flex;gap:0;border-bottom:1px solid rgba(255,255,255,0.08);margin-bottom:20px;">
    @foreach(['Kompetensi' => 'tab-comp', 'Unit Kerja' => 'tab-unit', 'Jabatan' => 'tab-jab'] as $label => $tabId)
    <button onclick="switchTab('{{ $tabId }}')" id="btn-{{ $tabId }}" 
        style="padding:10px 20px;background:none;border:none;color:var(--text-secondary);cursor:pointer;font-size:13px;border-bottom:2px solid transparent;transition:all .2s;"
        class="tab-btn">{{ $label }}</button>
    @endforeach
</div>

{{-- Kompetensi Tab --}}
<div id="tab-comp" class="tab-content">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="card-title" style="margin-bottom:0;">Jenis Kompetensi</div>
            <form method="POST" action="{{ route('admin.storeCompetency') }}" style="display:flex;gap:8px;">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="Nama kompetensi baru..." style="min-width:240px;" required>
                <input type="text" name="category" class="form-control" placeholder="Kategori..." style="min-width:160px;">
                <button type="submit" class="btn btn-primary">+ Tambah</button>
            </form>
        </div>
        <table>
            <thead>
                <tr><th>#</th><th>Nama Kompetensi</th><th>Kategori</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($competencies as $i => $c)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->category ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.deleteCompetency', $c->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kompetensi ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada data kompetensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Unit Kerja Tab --}}
<div id="tab-unit" class="tab-content" style="display:none;">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="card-title" style="margin-bottom:0;">Daftar Unit Kerja</div>
            <form method="POST" action="{{ route('admin.storeUnit') }}" style="display:flex;gap:8px;">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="Nama unit kerja..." style="min-width:280px;" required>
                <button type="submit" class="btn btn-primary">+ Tambah</button>
            </form>
        </div>
        <table>
            <thead>
                <tr><th>#</th><th>Nama Unit Kerja</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($units as $i => $u)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $u->name }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.deleteUnit', $u->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus unit ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada data unit kerja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Jabatan Tab --}}
<div id="tab-jab" class="tab-content" style="display:none;">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="card-title" style="margin-bottom:0;">Daftar Jabatan</div>
            <form method="POST" action="{{ route('admin.storeJabatan') }}" style="display:flex;gap:8px;">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="Nama jabatan..." style="min-width:280px;" required>
                <input type="text" name="level" class="form-control" placeholder="Level (JFA/Non-JFA)..." style="min-width:160px;">
                <button type="submit" class="btn btn-primary">+ Tambah</button>
            </form>
        </div>
        <table>
            <thead>
                <tr><th>#</th><th>Nama Jabatan</th><th>Level</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($jabatans as $i => $j)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $j->name }}</td>
                    <td>{{ $j->level ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.deleteJabatan', $j->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus jabatan ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada data jabatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(activeId) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.style.color = 'var(--text-secondary)';
        b.style.borderBottomColor = 'transparent';
    });
    document.getElementById(activeId).style.display = 'block';
    document.getElementById('btn-' + activeId).style.color = 'var(--accent)';
    document.getElementById('btn-' + activeId).style.borderBottomColor = 'var(--accent)';
}
// Init first tab active
document.addEventListener('DOMContentLoaded', () => switchTab('tab-comp'));
</script>
@endpush
