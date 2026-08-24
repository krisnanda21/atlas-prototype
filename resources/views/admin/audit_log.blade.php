@extends('layouts.app')
@section('title', 'Audit Log')
@section('header_title', 'Audit Log Sistem')

@section('content')
<div class="page-header">
    <h1>📝 Audit Log Sistem</h1>
</div>

{{-- Filter --}}
<div class="card mb-4" style="padding:16px;">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin-bottom:0;min-width:200px;">
            <label class="form-label">Pengguna</label>
            <select name="user_id" class="form-control">
                <option value="">Semua Pengguna</option>
                @foreach($allUsers as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;min-width:160px;">
            <label class="form-label">Aksi</label>
            <select name="action" class="form-control">
                <option value="">Semua Aksi</option>
                @foreach($allActions as $a)
                <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;min-width:140px;">
            <label class="form-label">Tanggal</label>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <button type="submit" class="btn btn-primary" style="height:40px;">Filter</button>
        <a href="{{ route('admin.auditLog') }}" class="btn btn-neutral" style="height:40px;line-height:24px;">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-title">Riwayat Aktivitas Sistem</div>
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Pengguna</th>
                <th>Role</th>
                <th>Aksi</th>
                <th>Detail</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="font-size:12px;white-space:nowrap;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                <td>{{ $log->user_name ?? '-' }}</td>
                <td><span class="badge badge-neutral" style="font-size:10px;">{{ $log->user_role ?? '-' }}</span></td>
                <td>
                    @php
                        $actionColors = ['login'=>'badge-success','logout'=>'badge-neutral','create'=>'badge-info','update'=>'badge-warning','delete'=>'badge-danger','approve'=>'badge-success'];
                        $ac = $actionColors[strtolower($log->action)] ?? 'badge-neutral';
                    @endphp
                    <span class="badge {{ $ac }}">{{ $log->action }}</span>
                </td>
                <td style="font-size:12px;max-width:300px;">{{ Str::limit($log->description ?? '', 120) }}</td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $log->ip_address ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted text-sm" style="text-align:center;padding:24px;">Belum ada log.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px;">{{ $logs->withQueryString()->links() }}</div>
</div>
@endsection
