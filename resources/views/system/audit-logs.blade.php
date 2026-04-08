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

    /* Search bar in panel header */
    .log-search-bar { display:flex;align-items:center;gap:.5rem;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;padding:.38rem .85rem;width:260px;transition:border-color .2s; }
    .log-search-bar:focus-within { border-color:var(--accent); }
    .log-search-bar input { border:none;background:transparent;outline:none;font-size:.82rem;color:var(--text);width:100%; }

    /* Filter bar */
    .filter-bar { display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;padding:.75rem 1.25rem;border-bottom:1px solid var(--border);background:var(--surface2); }
    .filter-label { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);white-space:nowrap;display:flex;align-items:center;gap:.3rem; }
    .filter-input  { background:var(--surface);border:1px solid var(--border);border-radius:.5rem;padding:.32rem .75rem;font-size:.78rem;color:var(--text);outline:none;transition:border-color .2s; }
    .filter-input:focus { border-color:var(--accent); }
    .filter-select { background:var(--surface);border:1px solid var(--border);border-radius:.5rem;padding:.32rem .65rem;font-size:.78rem;color:var(--text);outline:none;cursor:pointer; }
    .btn-filter { display:inline-flex;align-items:center;gap:.35rem;background:var(--accent);border:none;border-radius:.5rem;color:#fff;font-size:.76rem;font-weight:600;padding:.35rem .85rem;cursor:pointer;transition:opacity .15s;white-space:nowrap; }
    .btn-filter:hover { opacity:.85; }
    .btn-clear-filter { display:inline-flex;align-items:center;gap:.35rem;background:transparent;border:1px solid var(--border);border-radius:.5rem;color:var(--muted);font-size:.76rem;padding:.33rem .75rem;cursor:pointer;text-decoration:none;transition:background .15s;white-space:nowrap; }
    .btn-clear-filter:hover { background:var(--surface);color:var(--text); }
    .filter-count { margin-left:auto;font-size:.72rem;color:var(--muted);white-space:nowrap; }

    /* Toggle button for mobile */
    .btn-filter-toggle { display:none;align-items:center;gap:.4rem;background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;color:var(--text);font-size:.78rem;font-weight:600;padding:.38rem .8rem;cursor:pointer; }

    /* Mobile log card view */
    .log-card { display:none;flex-direction:column;gap:.35rem;padding:.85rem 1rem;border-bottom:1px solid var(--border); }
    .log-card:last-child { border-bottom:none; }
    .log-card-row { display:flex;align-items:flex-start;gap:.5rem;font-size:.78rem; }
    .log-card-label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);min-width:64px;padding-top:.1rem; }

    @media (max-width:768px) {
        .log-search-bar { width:100%; }
        .panel-header { flex-direction:column; align-items:stretch !important; gap:.6rem; }
        .filter-bar { flex-direction:column; align-items:stretch; gap:.5rem; }
        .filter-label { margin-bottom:.1rem; }
        .filter-input, .filter-select { width:100%; box-sizing:border-box; }
        .filter-count { margin-left:0; }
        .btn-filter-toggle { display:inline-flex; }
        .filter-collapsible { display:none; }
        .filter-collapsible.open { display:flex; flex-direction:column; gap:.5rem; width:100%; }
        /* Hide table, show cards on mobile */
        .log-table-wrap { display:none !important; }
        .log-card { display:flex; }
        .btn-filter-row { display:flex; gap:.5rem; }
    }
    @media (min-width:769px) {
        .log-table-wrap { display:block; }
        .log-card { display:none !important; }
        .filter-collapsible { display:contents; }
    }
@endpush

@section('content')
<div class="panel">

    {{-- Header --}}
    <div class="panel-header" style="align-items:center">
        <span class="panel-title"><i class="bi bi-journal-text me-2" style="color:var(--accent)"></i>Audit Logs</span>
        <div style="margin-left:auto;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;width:100%;max-width:260px">
            <div class="log-search-bar">
                <i class="bi bi-search" style="color:var(--muted);font-size:.8rem;flex-shrink:0"></i>
                <input type="text" id="logSearch" placeholder="Search logs…" oninput="filterLogs(this.value)">
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('audit.logs') }}" id="filterForm">
        <div class="filter-bar">

            {{-- Label + toggle button (mobile) --}}
            <div style="display:flex;align-items:center;justify-content:space-between;width:100%">
                <span class="filter-label"><i class="bi bi-funnel-fill"></i> Filter</span>
                <button type="button" class="btn-filter-toggle" onclick="toggleFilters(this)">
                    <i class="bi bi-sliders" id="filterToggleIcon"></i>
                    <span id="filterToggleLabel">Show Filters</span>
                </button>
            </div>

            {{-- Collapsible filter controls --}}
            <div class="filter-collapsible" id="filterCollapsible">

                <select name="action_filter" class="filter-select">
                    <option value="">All Actions</option>
                    <option value="security.login_failed"    {{ request('action_filter') === 'security.login_failed'    ? 'selected' : '' }}>Login Failed</option>
                    <option value="security.login_threshold" {{ request('action_filter') === 'security.login_threshold' ? 'selected' : '' }}>Login Threshold (3×)</option>
                    <option value="security"                 {{ request('action_filter') === 'security'                 ? 'selected' : '' }}>All Security Events</option>
                    <option value="ticket.sla_breached"      {{ request('action_filter') === 'ticket.sla_breached'      ? 'selected' : '' }}>SLA Breached</option>
                    <option value="ticket"                   {{ request('action_filter') === 'ticket'                   ? 'selected' : '' }}>All Ticket Events</option>
                </select>

                <input type="text" name="user_filter" class="filter-input" placeholder="User / email…"
                    value="{{ request('user_filter') }}">

                <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap">
                    <input type="date" name="date_from" class="filter-input" title="From date"
                        value="{{ request('date_from') }}" style="flex:1;min-width:130px">
                    <span style="font-size:.72rem;color:var(--muted)">to</span>
                    <input type="date" name="date_to" class="filter-input" title="To date"
                        value="{{ request('date_to') }}" style="flex:1;min-width:130px">
                </div>

                <div class="btn-filter-row">
                    <button type="submit" class="btn-filter" style="flex:1;justify-content:center"><i class="bi bi-search"></i> Apply</button>
                    @if(request()->hasAny(['action_filter','user_filter','date_from','date_to']))
                    <a href="{{ route('audit.logs') }}" class="btn-clear-filter"><i class="bi bi-x-lg"></i> Clear</a>
                    @endif
                </div>

                @if($logs->total() > 0)
                <span class="filter-count">{{ number_format($logs->total()) }} result{{ $logs->total() !== 1 ? 's' : '' }}</span>
                @endif

            </div>{{-- /filter-collapsible --}}

            {{-- Desktop: show action select + quick apply inline (hidden on mobile via .filter-collapsible) --}}
        </div>
    </form>

    @if($logs->isEmpty())
    <div style="padding:4rem 1.5rem;text-align:center;color:var(--muted)">
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

    {{-- ── Desktop Table ── --}}
    <div style="overflow-x:auto" class="log-table-wrap">
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
                @php [$chipClass, $actionIcon] = auditChip($log->action); @endphp
                <tr data-search="{{ strtolower($log->action . ' ' . ($log->user->name ?? '') . ' ' . $log->description . ' ' . $log->subject_label) }}">
                    <td style="white-space:nowrap;color:var(--muted);font-size:.75rem">
                        <div>{{ $log->created_at->format('M d, Y') }}</div>
                        <div>{{ $log->created_at->format('h:i A') }}</div>
                    </td>
                    <td>@include('system._audit-user', ['log' => $log])</td>
                    <td><span class="action-chip {{ $chipClass }}">@if($actionIcon)<i class="bi {{ $actionIcon }}" style="margin-right:.3rem"></i>@endif{{ $log->action }}</span></td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $log->subject_label ?: '—' }}</td>
                    <td style="max-width:340px;color:var(--muted);font-size:.78rem">{{ $log->description ?: '—' }}</td>
                    <td style="color:var(--muted);font-size:.75rem;font-family:monospace;white-space:nowrap">{{ $log->ip_address ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Mobile Cards ── --}}
    <div id="mobileCards">
        @foreach($logs as $log)
        @php [$chipClass, $actionIcon] = auditChip($log->action); @endphp
        <div class="log-card" data-search="{{ strtolower($log->action . ' ' . ($log->user->name ?? '') . ' ' . $log->description . ' ' . $log->subject_label) }}">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap">
                <span class="action-chip {{ $chipClass }}" style="font-size:.65rem">
                    @if($actionIcon)<i class="bi {{ $actionIcon }}" style="margin-right:.3rem"></i>@endif
                    {{ $log->action }}
                </span>
                <span style="font-size:.7rem;color:var(--muted)">{{ $log->created_at->format('M d, Y h:i A') }}</span>
            </div>
            <div class="log-card-row">
                <span class="log-card-label">User</span>
                <span>@include('system._audit-user', ['log' => $log])</span>
            </div>
            @if($log->subject_label)
            <div class="log-card-row">
                <span class="log-card-label">Subject</span>
                <span style="color:var(--muted);font-size:.78rem">{{ $log->subject_label }}</span>
            </div>
            @endif
            @if($log->description)
            <div class="log-card-row">
                <span class="log-card-label">Details</span>
                <span style="color:var(--muted);font-size:.78rem">{{ $log->description }}</span>
            </div>
            @endif
            @if($log->ip_address)
            <div class="log-card-row">
                <span class="log-card-label">IP</span>
                <span style="color:var(--muted);font-size:.75rem;font-family:monospace">{{ $log->ip_address }}</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    @if($logs->hasPages())
    <div style="padding:.75rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
        {{ $logs->links() }}
    </div>
    @endif
    @endif
</div>

@php
function auditChip(string $action): array {
    $a = strtolower($action);
    $icon = match(true) {
        $a === 'ticket.sla_breached'      => 'bi-alarm-fill',
        $a === 'security.login_threshold' => 'bi-shield-exclamation',
        $a === 'security.login_failed'    => 'bi-shield-x',
        default => '',
    };
    $cls = match(true) {
        str_contains($a, 'sla_breached')                                  => 'action-sla',
        str_contains($a, 'security') || str_contains($a, 'login')         => 'action-security',
        str_contains($a, 'creat')                                         => 'action-created',
        str_contains($a, 'updat') || str_contains($a, 'chang')            => 'action-updated',
        str_contains($a, 'delet') || str_contains($a, 'remov')            => 'action-deleted',
        default => 'action-default',
    };
    return [$cls, $icon];
}
@endphp
@endsection

@push('scripts')
<script>
function filterLogs(q) {
    const term = q.trim().toLowerCase();
    // Desktop table rows
    document.querySelectorAll('#logTable tbody tr').forEach(row => {
        row.style.display = !term || (row.dataset.search || '').includes(term) ? '' : 'none';
    });
    // Mobile cards
    document.querySelectorAll('#mobileCards .log-card').forEach(card => {
        card.style.display = !term || (card.dataset.search || '').includes(term) ? 'flex' : 'none';
    });
}

function toggleFilters(btn) {
    const panel = document.getElementById('filterCollapsible');
    const icon  = document.getElementById('filterToggleIcon');
    const label = document.getElementById('filterToggleLabel');
    const open  = panel.classList.toggle('open');
    icon.className  = open ? 'bi bi-x-lg' : 'bi bi-sliders';
    label.textContent = open ? 'Hide Filters' : 'Show Filters';
}

window.addEventListener('DOMContentLoaded', () => {
    // Auto-open filters on mobile if any are active
    const hasFilters = {{ request()->hasAny(['action_filter','user_filter','date_from','date_to']) ? 'true' : 'false' }};
    if (hasFilters && window.innerWidth <= 768) {
        document.getElementById('filterCollapsible').classList.add('open');
        document.getElementById('filterToggleIcon').className = 'bi bi-x-lg';
        document.getElementById('filterToggleLabel').textContent = 'Hide Filters';
    }
    // On desktop, always show filters
    if (window.innerWidth > 768) {
        document.getElementById('filterCollapsible').classList.add('open');
    }
});
</script>
@endpush
