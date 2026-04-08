@extends('layouts.app')
@section('title','Audit Logs')
@section('topbar-title','Audit Logs')
@section('topbar-sub','Track all system activity and changes')

@push('styles')
    .log-table { width:100%; border-collapse:collapse; font-size:.8rem; }
    .log-table thead th { padding:.6rem 1rem; font-size:.63rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); border-bottom:1px solid var(--border); white-space:nowrap; }
    .log-table tbody tr { border-bottom:1px solid var(--border); transition:background .15s; }
    .log-table tbody tr:last-child { border-bottom:none; }
    .log-table tbody tr:hover { background:var(--surface2); }
    .log-table tbody td { padding:.7rem 1rem; vertical-align:middle; color:var(--text); }
    .action-chip { display:inline-flex; align-items:center; padding:.2rem .65rem; border-radius:9999px; font-size:.68rem; font-weight:700; white-space:nowrap; }
    .action-created  { background:rgba(52,211,153,.12); color:#34d399; }
    .action-updated  { background:rgba(251,191,36,.12);  color:#fbbf24; }
    .action-deleted  { background:rgba(248,113,113,.12); color:#f87171; }
    .action-login    { background:rgba(99,102,241,.12);  color:#818cf8; }
    .action-default  { background:var(--surface2); color:var(--muted); border:1px solid var(--border); }
    .search-bar { display:flex; align-items:center; gap:.5rem; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; padding:.4rem .85rem; width:260px; transition:border-color .2s; }
    .search-bar:focus-within { border-color:var(--accent); }
    .search-bar input { border:none; background:transparent; outline:none; font-size:.825rem; color:var(--text); width:100%; }
@endpush

@section('content')
<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="bi bi-journal-text me-2" style="color:var(--accent)"></i>Audit Logs</span>
        <div class="search-bar">
            <i class="bi bi-search" style="color:var(--muted);font-size:.8rem;flex-shrink:0"></i>
            <input type="text" id="logSearch" placeholder="Search logs…" oninput="filterLogs(this.value)">
        </div>
    </div>

    @if($logs->isEmpty())
    <div style="padding:4rem;text-align:center;color:var(--muted)">
        <i class="bi bi-journal-x" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
        <div style="font-size:.95rem;font-weight:600">No audit logs yet</div>
        <div style="font-size:.82rem;margin-top:.35rem">System activity will be recorded here as actions are performed.</div>
        @if(!Schema::hasTable('audit_logs'))
        <div style="margin-top:1rem;background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.2);border-radius:.65rem;padding:.65rem 1rem;display:inline-flex;align-items:center;gap:.5rem;font-size:.8rem;color:#fbbf24">
            <i class="bi bi-exclamation-triangle-fill"></i> Run <code style="background:rgba(251,191,36,.1);padding:.1rem .4rem;border-radius:.3rem">php artisan migrate</code> to create the audit_logs table.
        </div>
        @endif
    </div>
    @else
    <div style="overflow-x:auto">
        <table class="log-table" id="logTable">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                @php
                    $actionKey = strtolower(explode('.', $log->action)[1] ?? $log->action);
                    $chipClass = match(true) {
                        str_contains($actionKey, 'creat') => 'action-created',
                        str_contains($actionKey, 'updat') || str_contains($actionKey, 'chang') => 'action-updated',
                        str_contains($actionKey, 'delet') || str_contains($actionKey, 'remov') => 'action-deleted',
                        str_contains($actionKey, 'login') || str_contains($actionKey, 'auth')  => 'action-login',
                        default => 'action-default',
                    };
                @endphp
                <tr data-search="{{ strtolower($log->action . ' ' . ($log->user->name ?? '') . ' ' . $log->description . ' ' . $log->subject_label) }}">
                    <td style="white-space:nowrap;color:var(--muted);font-size:.75rem">
                        <div>{{ $log->created_at->format('M d, Y') }}</div>
                        <div>{{ $log->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        @if($log->user)
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="width:26px;height:26px;min-width:26px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:white">{{ strtoupper(substr($log->user->name, 0, 1)) }}</div>
                            <span style="white-space:nowrap;font-size:.8rem">{{ $log->user->name }}</span>
                        </div>
                        @else
                        <span style="color:var(--muted);font-size:.78rem">System</span>
                        @endif
                    </td>
                    <td><span class="action-chip {{ $chipClass }}">{{ $log->action }}</span></td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $log->subject_label ?: '—' }}</td>
                    <td style="max-width:320px;color:var(--muted);font-size:.78rem">{{ $log->description ?: '—' }}</td>
                    <td style="color:var(--muted);font-size:.75rem;font-family:monospace;white-space:nowrap">{{ $log->ip_address ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div style="padding:.75rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
        {{ $logs->links() }}
    </div>
    @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
    function filterLogs(q) {
        const term = q.trim().toLowerCase();
        document.querySelectorAll('#logTable tbody tr').forEach(row => {
            row.style.display = !term || (row.dataset.search || '').includes(term) ? '' : 'none';
        });
    }
</script>
@endpush
