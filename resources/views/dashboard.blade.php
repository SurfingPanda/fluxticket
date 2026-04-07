@extends('layouts.app')
@section('title', 'Dashboard')
@section('topbar-title', 'Dashboard')
@section('topbar-sub'){{ now()->format('l, F j, Y') }}@endsection

@push('styles')
    .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
    @media (max-width:1100px) { .stat-grid { grid-template-columns:repeat(2,1fr); } }
    @media (max-width:600px)  { .stat-grid { grid-template-columns:1fr; } }
    .stat-card { padding:1.25rem 1.35rem; display:flex; flex-direction:column; gap:.75rem; cursor:default; transition:background .3s,border-color .25s,transform .28s cubic-bezier(.22,.68,0,1.3),box-shadow .28s; }
    .stat-card:hover { transform:translateY(-6px) scale(1.03); }
    .stat-card:nth-child(1):hover { border-color:rgba(129,140,248,.55); box-shadow:0 14px 40px rgba(99,102,241,.22),0 0 0 1px rgba(129,140,248,.2); }
    .stat-card:nth-child(2):hover { border-color:rgba(251,191,36,.5);   box-shadow:0 14px 40px rgba(251,191,36,.2),0 0 0 1px rgba(251,191,36,.2); }
    .stat-card:nth-child(3):hover { border-color:rgba(52,211,153,.5);   box-shadow:0 14px 40px rgba(52,211,153,.2),0 0 0 1px rgba(52,211,153,.2); }
    .stat-card:nth-child(4):hover { border-color:rgba(248,113,113,.5);  box-shadow:0 14px 40px rgba(248,113,113,.2),0 0 0 1px rgba(248,113,113,.2); }
    .stat-card:hover .stat-icon-wrap { transform:scale(1.2) rotate(-8deg); }
    .stat-icon-wrap { width:40px; height:40px; border-radius:.75rem; display:flex; align-items:center; justify-content:center; font-size:1.1rem; transition:transform .28s cubic-bezier(.22,.68,0,1.4); }
    .stat-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); }
    .stat-value { font-size:1.8rem; font-weight:800; color:var(--text); line-height:1; }
    .stat-trend { font-size:.75rem; display:flex; align-items:center; gap:.25rem; }
    .trend-up { color:#34d399; } .trend-down { color:#f87171; }
    .bottom-grid { display:grid; grid-template-columns:1fr 320px; gap:1rem; margin-top:1rem; }
    @media (max-width:900px) { .bottom-grid { grid-template-columns:1fr; } }
    .activity-item { display:flex; gap:.85rem; align-items:flex-start; padding:.8rem 1.35rem; border-bottom:1px solid var(--border); }
    .activity-item:last-child { border-bottom:none; }
    .activity-dot { width:30px; height:30px; flex-shrink:0; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; margin-top:.1rem; }
@endpush

@section('content')

{{-- ── Greeting ── --}}
<div class="mb-4">
    <h5 style="font-weight:700;font-size:1.1rem;margin-bottom:.2rem">
        @php
            $h = now()->hour;
            $greet = $h >= 18 ? 'evening' : ($h >= 12 ? 'afternoon' : ($h >= 5 ? 'morning' : 'evening'));
        @endphp
        Good {{ $greet }}, {{ auth()->user()->name ?? 'there' }} 👋
    </h5>
    <p style="color:var(--muted);font-size:.825rem;margin:0">Here's what's happening with your tickets today.</p>
</div>

{{-- ── Stat cards ── --}}
<div class="stat-grid">

    <div class="stat-card panel">
        <div class="d-flex align-items-center justify-content-between">
            <span class="stat-label">Open</span>
            <div class="stat-icon-wrap" style="background:rgba(99,102,241,.15)">
                <i class="bi bi-ticket-perforated" style="color:#818cf8"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['open'] }}</div>
        <div class="stat-trend" style="color:var(--muted)">Total open tickets</div>
    </div>

    <div class="stat-card panel">
        <div class="d-flex align-items-center justify-content-between">
            <span class="stat-label">In Progress</span>
            <div class="stat-icon-wrap" style="background:rgba(251,191,36,.12)">
                <i class="bi bi-arrow-repeat" style="color:#fbbf24"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['progress'] }}</div>
        <div class="stat-trend" style="color:var(--muted)">Being worked on</div>
    </div>

    <div class="stat-card panel">
        <div class="d-flex align-items-center justify-content-between">
            <span class="stat-label">Resolved</span>
            <div class="stat-icon-wrap" style="background:rgba(52,211,153,.12)">
                <i class="bi bi-check-circle" style="color:#34d399"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['resolved'] }}</div>
        <div class="stat-trend" style="color:var(--muted)">Successfully closed</div>
    </div>

    <div class="stat-card panel">
        <div class="d-flex align-items-center justify-content-between">
            <span class="stat-label">Total</span>
            <div class="stat-icon-wrap" style="background:rgba(248,113,113,.12)">
                <i class="bi bi-stack" style="color:#f87171"></i>
            </div>
        </div>
        <div class="stat-value">{{ array_sum($stats) }}</div>
        <div class="stat-trend" style="color:var(--muted)">All time tickets</div>
    </div>

</div>

{{-- ── Recent Tickets Table ── --}}
@php
    $statusMap   = ['open'=>['s-open','Open'],'progress'=>['s-progress','In Progress'],'resolved'=>['s-resolved','Resolved'],'closed'=>['s-closed','Closed']];
    $priorityMap = ['high'=>'p-high','medium'=>'p-medium','low'=>'p-low'];
@endphp
<div class="panel mb-0">
    <div class="panel-header">
        <span class="panel-title"><i class="bi bi-ticket-perforated me-2" style="color:var(--accent)"></i>Recent Tickets</span>
        <a href="{{ route('tickets.index') }}" style="font-size:.775rem;color:#818cf8;font-weight:600">View all <i class="bi bi-arrow-right"></i></a>
    </div>
    <div style="overflow-x:hidden">
        @if($tickets->isEmpty())
            <div style="padding:3rem;text-align:center;color:var(--muted)">
                <i class="bi bi-ticket-perforated" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                <div style="font-size:.875rem;font-weight:500">No tickets yet</div>
                <div style="font-size:.78rem;margin-top:.25rem">Click <b>New Ticket</b> to submit your first request.</div>
            </div>
        @else
        <table class="flux-table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Subject</th>
                    <th>Requester</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $t)
                <tr>
                    <td><span style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace">{{ $t->ticket_number }}</span></td>
                    <td style="max-width:220px">
                        <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->subject }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:24px;height:24px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:white;flex-shrink:0">
                                {{ strtoupper(substr($t->user->name ?? '?', 0, 1)) }}
                            </div>
                            <span>{{ $t->user->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-priority {{ $priorityMap[$t->priority] ?? 'p-low' }}">
                            {{ ucfirst($t->priority) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge-status {{ $statusMap[$t->status][0] ?? 's-open' }}">
                            {{ $statusMap[$t->status][1] ?? 'Open' }}
                        </span>
                    </td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->created_at->diffForHumans() }}</td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->updated_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- ── Bottom row ── --}}
<div class="bottom-grid">

    {{-- SLA Overview --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="bi bi-bar-chart-line me-2" style="color:var(--accent)"></i>SLA Overview</span>
            <span style="font-size:.72rem;color:var(--muted)">Live</span>
        </div>
        <div style="padding:1.25rem 1.35rem;display:flex;flex-direction:column;gap:1rem">
            @php
            use App\Models\Ticket;
            $allT    = Ticket::whereNotNull('sla_due_at')->get();
            $total   = $allT->count();
            $metCnt    = $allT->filter(fn($t) => $t->sla_status === 'met')->count();
            $okCnt     = $allT->filter(fn($t) => $t->sla_status === 'ok')->count();
            $warnCnt   = $allT->filter(fn($t) => $t->sla_status === 'warning')->count();
            $breachCnt = $allT->filter(fn($t) => $t->sla_status === 'breached')->count();
            $pct = fn($n) => $total > 0 ? round(($n / $total) * 100) : 0;
            $slaRows = [
                ['label'=>'Met (Resolved within SLA)',   'n'=>$metCnt,    'color'=>'#818cf8'],
                ['label'=>'On Track (Open, within SLA)', 'n'=>$okCnt,     'color'=>'#34d399'],
                ['label'=>'At Risk (≤25% time left)',    'n'=>$warnCnt,   'color'=>'#fbbf24'],
                ['label'=>'Breached (Overdue)',          'n'=>$breachCnt, 'color'=>'#f87171'],
            ];
            @endphp

            @if($total === 0)
                <div style="text-align:center;color:var(--muted);font-size:.8rem;padding:.5rem 0">No SLA data yet. Create tickets to see stats.</div>
            @else
                @foreach($slaRows as $s)
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:.78rem">
                        <span style="color:var(--text);font-weight:500">{{ $s['label'] }}</span>
                        <span style="color:{{ $s['color'] }};font-weight:700">{{ $s['n'] }} / {{ $total }} ({{ $pct($s['n']) }}%)</span>
                    </div>
                    <div style="height:6px;background:var(--surface2);border-radius:9999px;overflow:hidden">
                        <div style="height:100%;width:{{ $pct($s['n']) }}%;background:{{ $s['color'] }};border-radius:9999px;transition:width .6s ease"></div>
                    </div>
                </div>
                @endforeach

                <div style="border-top:1px solid var(--border);padding-top:.75rem;margin-top:.25rem">
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.5rem">SLA Targets by Priority</div>
                    @foreach(['high'=>'1-2 days','medium'=>'2-3 days','low'=>'1-7 days'] as $p => $target)
                    @php
                        $pc = $allT->filter(fn($t)=>$t->priority===$p);
                        $pcMet = $pc->filter(fn($t)=>in_array($t->sla_status,['met','ok']))->count();
                        $pcTotal = $pc->count();
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.75rem">
                        <span class="badge-priority p-{{ $p }}" style="min-width:52px;text-align:center">{{ ucfirst($p) }}</span>
                        <span style="color:var(--muted)">{{ $target }}</span>
                        <span style="margin-left:auto;color:var(--text);font-weight:600">{{ $pcMet }}/{{ $pcTotal }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Activity Feed --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="bi bi-activity me-2" style="color:var(--accent)"></i>Recent Activity</span>
        </div>
        <div style="max-height:310px;overflow-y:auto;">
            @forelse($recentActivity as $a)
            @php
                $isRoute = $a->type === 'route_event';
                $icon    = $isRoute ? 'bi-arrow-left-right' : 'bi-chat-left-text-fill';
                $color   = $isRoute ? 'rgba(251,191,36,.12)' : 'rgba(99,102,241,.15)';
                $ic      = $isRoute ? '#fbbf24' : '#818cf8';
                $num     = $a->ticket->ticket_number ?? '—';
                $who     = $a->user->name ?? 'Someone';
                $action  = $isRoute ? "routed ticket <b>{$num}</b>" : "added a note on <b>{$num}</b>";
            @endphp
            <div class="activity-item">
                <div class="activity-dot" style="background:{{ $color }}">
                    <i class="bi {{ $icon }}" style="color:{{ $ic }}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.78rem;color:var(--text);line-height:1.4">
                        <b>{{ $who }}</b> {!! $action !!}
                    </div>
                    <div style="font-size:.7rem;color:var(--muted);margin-top:.15rem">{{ $a->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="padding:1.5rem;text-align:center;color:var(--muted);font-size:.8rem">No recent activity.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
