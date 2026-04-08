@extends('layouts.app')
@section('title','Agent Performance')
@section('topbar-title','Agent Performance')
@section('topbar-sub','Resolution rates, ticket load, and average response times per agent')

@push('styles')
    .perf-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .perf-table thead th { padding:.6rem 1rem; font-size:.63rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); border-bottom:1px solid var(--border); white-space:nowrap; }
    .perf-table tbody tr { border-bottom:1px solid var(--border); transition:background .15s; }
    .perf-table tbody tr:last-child { border-bottom:none; }
    .perf-table tbody tr:hover { background:var(--surface2); }
    .perf-table tbody td { padding:.75rem 1rem; vertical-align:middle; }
    .rate-bar-wrap { width:100px; height:6px; background:var(--surface2); border-radius:9999px; overflow:hidden; display:inline-block; vertical-align:middle; margin-right:.5rem; }
    .rate-bar { height:100%; border-radius:9999px; }
    .stat-num { font-size:.88rem; font-weight:700; color:var(--text); }
    .stat-sub { font-size:.7rem; color:var(--muted); }
    .dept-chip { display:inline-flex; align-items:center; padding:.18rem .6rem; border-radius:9999px; font-size:.68rem; font-weight:700; background:rgba(99,102,241,.12); color:#818cf8; }
@endpush

@section('content')
<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="bi bi-person-badge me-2" style="color:var(--accent)"></i>Agent Performance</span>
        <span style="font-size:.78rem;color:var(--muted)">{{ $agentPerf->count() }} agent{{ $agentPerf->count() !== 1 ? 's' : '' }} with ticket activity</span>
    </div>

    @if($agentPerf->isEmpty())
    <div style="padding:4rem;text-align:center;color:var(--muted)">
        <i class="bi bi-person-x" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
        <div style="font-size:.95rem;font-weight:600">No agent data yet</div>
        <div style="font-size:.82rem;margin-top:.35rem">Agent performance will appear here once tickets are assigned.</div>
    </div>
    @else
    <div style="overflow-x:auto">
        <table class="perf-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Agent</th>
                    <th>Department</th>
                    <th style="text-align:center">Total</th>
                    <th style="text-align:center">Open</th>
                    <th style="text-align:center">In Progress</th>
                    <th style="text-align:center">Resolved</th>
                    <th>Resolution Rate</th>
                    <th>Avg. Resolution</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agentPerf as $i => $a)
                @php
                    $rate = $a->rate;
                    $barColor = $rate >= 70 ? '#34d399' : ($rate >= 40 ? '#fbbf24' : '#f87171');
                @endphp
                <tr>
                    <td style="color:var(--muted);font-size:.75rem;font-weight:700">{{ $i + 1 }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.6rem">
                            <div style="width:30px;height:30px;min-width:30px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:white">{{ strtoupper(substr($a->name, 0, 1)) }}</div>
                            <span style="font-weight:600;color:var(--text);white-space:nowrap">{{ $a->name }}</span>
                        </div>
                    </td>
                    <td><span class="dept-chip">{{ $a->dept ?: '—' }}</span></td>
                    <td style="text-align:center"><span class="stat-num">{{ $a->total }}</span></td>
                    <td style="text-align:center">
                        @if($a->open > 0)
                            <span style="color:#818cf8;font-weight:600">{{ $a->open }}</span>
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        @if($a->progress > 0)
                            <span style="color:#fbbf24;font-weight:600">{{ $a->progress }}</span>
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        @if($a->resolved > 0)
                            <span style="color:#34d399;font-weight:600">{{ $a->resolved }}</span>
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div class="rate-bar-wrap"><div class="rate-bar" style="width:{{ $rate }}%;background:{{ $barColor }}"></div></div>
                            <span style="font-size:.8rem;font-weight:700;color:{{ $barColor }}">{{ $rate }}%</span>
                        </div>
                    </td>
                    <td>
                        @if($a->avg_hrs !== null)
                            <span class="stat-num">{{ $a->avg_hrs }}h</span>
                        @else
                            <span style="color:var(--muted);font-size:.78rem">No data</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
