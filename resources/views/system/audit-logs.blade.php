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
    .action-created  { background:rgba(52,211,153,.12);  color:#34d399; }
    .action-updated  { background:rgba(251,191,36,.12);   color:#fbbf24; }
    .action-deleted  { background:rgba(248,113,113,.12);  color:#f87171; }
    .action-login    { background:rgba(99,102,241,.12);   color:#818cf8; }
    .action-security { background:rgba(248,113,113,.15);  color:#f87171; border:1px solid rgba(248,113,113,.25); }
    .action-sla      { background:rgba(251,146,60,.12);   color:#fb923c; }
    .action-default  { background:var(--surface2); color:var(--muted); border:1px solid var(--border); }
    /* Filter bar */
    .filter-bar { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; padding:.75rem 1.25rem; border-bottom:1px solid var(--border); background:var(--surface2); }
    .filter-input { background:var(--surface); border:1px solid var(--border); border-radius:.5rem; padding:.32rem .75rem; font-size:.78rem; color:var(--text); outline:none; transition:border-color .2s; }
    .filter-input:focus { border-color:var(--accent); }
    .filter-select { background:var(--surface); border:1px solid var(--border); border-radius:.5rem; padding:.32rem .65rem; font-size:.78rem; color:var(--text); outline:none; cursor:pointer; }
    .search-bar { display:flex; align-items:center; gap:.5rem; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; padding:.4rem .85rem; min-width:220px; transition:border-color .2s; }
    .search-bar:focus-within { border-color:var(--accent); }
    .search-bar input { border:none; background:transparent; outline:none; font-size:.825rem; color:var(--text); width:100%; }
    .btn-filter { display:inline-flex;align-items:center;gap:.35rem;background:var(--accent);border:none;border-radius:.5rem;color:#fff;font-size:.76rem;font-weight:600;padding:.35rem .85rem;cursor:pointer;transition:opacity .15s; }
    .btn-filter:hover { opacity:.85; }
    .btn-clear-filter { display:inline-flex;align-items:center;gap:.35rem;background:transparent;border:1px solid var(--border);border-radius:.5rem;color:var(--muted);font-size:.76rem;padding:.33rem .75rem;cursor:pointer;text-decoration:none;transition:background .15s; }
    .btn-clear-filter:hover { background:var(--surface); color:var(--text); }
@endpush

@section('content')
<div class="panel">
    {{-- Header --}}
    <div class="panel-header" style="flex-wrap:wrap;gap:.5rem">
        <span class="panel-title"><i class="bi bi-journal-text me-2" style="color:var(--accent)"></i>Audit Logs</span>
        <div style="margin-left:auto;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
            <div class="search-bar">
                <i class="bi bi-search" style="color:var(--muted);font-size:.8rem;flex-shrink:0"></i>
                <input type="text" id="logSearch" placeholder="Search logs…" oninput="filterLogs(this.value)"
                    value="{{ request('q') }}">
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('audit.logs') }}" id="filterForm">
        <div class="filter-bar">
            <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);white-space:nowrap"><i class="bi bi-funnel-fill" style="margin-right:.3rem"></i>Filter</span>

            <select name="action_filter" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Actions</option>
                <option value="security.login_failed"     {{ request('action_filter') === 'security.login_failed'     ? 'selected' : '' }}>Login Failed</option>
                <option value="security.login_threshold"  {{ request('action_filter') === 'security.login_threshold'  ? 'selected' : '' }}>Login Threshold (3x)</option>
                <option value="security"                  {{ request('action_filter') === 'security'                  ? 'selected' : '' }}>All Security</option>
                <option value="ticket.sla_breached"       {{ request('action_filter') === 'ticket.sla_breached'       ? 'selected' : '' }}>SLA Breached</option>
                <option value="ticket"                    {{ request('action_filter') === 'ticket'                    ? 'selected' : '' }}>All Ticket Events</option>
            </select>

            <input type="text" name="user_filter" class="filter-input" placeholder="User / email…"
                value="{{ request('user_filter') }}" style="width:160px">

            <input type="date" name="date_from" class="filter-input" title="From date"
                value="{{ request('date_from') }}">
            <span style="font-size:.72rem;color:var(--muted)">to</span>
            <input type="date" name="date_to" class="filter-input" title="To date"
                value="{{ request('date_to') }}">

            <button type="submit" class="btn-filter"><i class="bi bi-search"></i> Apply</button>

            @if(request()->hasAny(['action_filter','user_filter','date_from','date_to']))
            <a href="{{ route('audit.logs') }}" class="btn-clear-filter"><i class="bi bi-x-lg"></i> Clear</a>
            @endif

            @if($logs->total() > 0)
            <span style="margin-left:auto;font-size:.72rem;color:var(--muted)">{{ number_format($logs->total()) }} result{{ $logs->total() !== 1 ? 's' : '' }}</span>
            @endif
        </div>
    </form>

    @if($logs->isEmpty())
    <div style="padding:4rem;text-align:center;color:var(--muted)">
        <i class="bi bi-journal-x" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
        @if(request()->hasAny(['action_filter','user_filter','date_from','date_to']))
        <div style="font-size:.95rem;font-weight:600">No logs match your filters</div>
        <div style="font-size:.82rem;margin-top:.35rem"><a href="{{ route('audit.logs') }}" style="color:var(--accent)">Clear filters</a> to see all logs.</div>
        @else
        <div style="font-size:.95rem;font-weight:600">No audit logs yet</div>
        <div style="font-size:.82rem;margin-top:.35rem">System activity will be recorded here as actions are performed.</div>
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
                    $actionKey = strtolower($log->action);
                    $chipClass = match(true) {
                        str_contains($actionKey, 'sla_breached')                                   => 'action-sla',
                        str_contains($actionKey, 'security') || str_contains($actionKey, 'login')  => 'action-security',
                        str_contains($actionKey, 'creat')                                          => 'action-created',
                        str_contains($actionKey, 'updat') || str_contains($actionKey, 'chang')     => 'action-updated',
                        str_contains($actionKey, 'delet') || str_contains($actionKey, 'remov')     => 'action-deleted',
                        default => 'action-default',
                    };
                    $actionIcon = match(true) {
                        $actionKey === 'ticket.sla_breached'      => 'bi-alarm-fill',
                        $actionKey === 'security.login_threshold' => 'bi-shield-exclamation',
                        $actionKey === 'security.login_failed'    => 'bi-shield-x',
                        default => '',
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
                        @elseif($log->subject_label && str_contains($log->action, 'login'))
                        <span style="color:var(--muted);font-size:.78rem;font-style:italic">{{ $log->subject_label }}</span>
                        @else
                        <span style="color:var(--muted);font-size:.78rem">System</span>
                        @endif
                    </td>
                    <td>
                        <span class="action-chip {{ $chipClass }}">
                            @if($actionIcon)<i class="bi {{ $actionIcon }}" style="margin-right:.3rem"></i>@endif
                            {{ $log->action }}
                        </span>
                    </td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $log->subject_label ?: '—' }}</td>
                    <td style="max-width:340px;color:var(--muted);font-size:.78rem">{{ $log->description ?: '—' }}</td>
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
// Auto-run search if value is pre-filled
window.addEventListener('DOMContentLoaded', () => {
    const q = document.getElementById('logSearch').value;
    if (q) filterLogs(q);
});
</script>
@endpush
