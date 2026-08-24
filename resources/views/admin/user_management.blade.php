@extends('layouts.app')
@section('title', 'User Management')
@section('header_title', 'Manajemen Pengguna')

@section('content')
<div class="page-header">
    <h1>👥 Manajemen Pengguna</h1>
    <button class="btn btn-primary" onclick="document.getElementById('modal-add').style.display='flex'">+ Tambah User</button>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);margin-bottom:16px;">
    ✅ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:var(--danger);margin-bottom:16px;">
    ❌ {{ session('error') }}
</div>
@endif

<div class="card">
    <div class="card-title">Daftar Pengguna Sistem</div>
    <table>
        <thead>
            <tr><th>Nama</th><th>Email</th><th>Role</th><th>Unit / Scope</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr>
                <td><strong>{{ $u->name }}</strong></td>
                <td>{{ $u->email }}</td>
                <td><span class="badge badge-neutral" style="font-size:10px;">{{ $u->role }}</span></td>
                <td>{{ $u->scope ?: '-' }}</td>

                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="btn btn-sm btn-neutral" onclick="openEditModal({{ json_encode($u) }})">Edit</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted text-sm" style="text-align:center;padding:20px;">Belum ada pengguna.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal Add --}}
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#1a2e45;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:480px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="font-size:16px;font-weight:600;">+ Tambah Pengguna</h2>
            <button onclick="document.getElementById('modal-add').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" class="form-control" required maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label">Role *</label>
                <select name="role" class="form-control" required>
                    @foreach($allRoles as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Unit / Scope</label>
                <input type="text" name="scope" class="form-control" placeholder="Contoh: Unit A">
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
                <button type="button" onclick="document.getElementById('modal-add').style.display='none'" class="btn btn-neutral">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modal-edit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#1a2e45;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:28px;width:480px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="font-size:16px;font-weight:600;">✏️ Edit Pengguna</h2>
            <button onclick="document.getElementById('modal-edit').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <form method="POST" id="edit-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama *</label>
                <input type="text" id="edit-name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" id="edit-email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role *</label>
                <select id="edit-role" name="role" class="form-control" required>
                    @foreach($allRoles as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Unit / Scope</label>
                <input type="text" id="edit-scope" name="scope" class="form-control">
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary">💾 Update</button>
                <button type="button" onclick="document.getElementById('modal-edit').style.display='none'" class="btn btn-neutral">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(user) {
    document.getElementById('edit-name').value = user.name;
    document.getElementById('edit-email').value = user.email;
    document.getElementById('edit-role').value = user.role;
    document.getElementById('edit-scope').value = user.scope || '';
    document.getElementById('edit-form').action = `/admin/users/${user.id}/update`;
    document.getElementById('modal-edit').style.display = 'flex';
}
</script>
@endpush
