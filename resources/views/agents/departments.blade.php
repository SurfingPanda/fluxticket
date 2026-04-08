@extends('layouts.app')
@section('title','Departments')
@section('topbar-title','Departments')
@section('topbar-sub', $departments->count() . ' department' . ($departments->count() !== 1 ? 's' : ''))

@push('styles')
    .dept-section { margin-bottom:0; }
    .dept-header { display:flex; align-items:center; gap:.75rem; padding:.75rem 1.25rem; border-bottom:1px solid var(--border); background:var(--surface2); border-radius:.75rem .75rem 0 0; }
    .dept-icon { width:34px; height:34px; border-radius:.5rem; background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(124,58,237,.15)); border:1px solid rgba(99,102,241,.25); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .dept-name { font-size:.9rem; font-weight:700; color:var(--text); }
    .dept-count { font-size:.72rem; color:var(--muted); margin-left:.35rem; }
    .agent-row { display:flex; align-items:center; gap:1rem; padding:.75rem 1.25rem; border-bottom:1px solid var(--border); transition:background .15s; }
    .agent-row:last-child { border-bottom:none; border-radius:0 0 .75rem .75rem; }
    .agent-row:hover { background:var(--surface2); }
    .agent-avatar { width:34px; height:34px; min-width:34px; border-radius:50%; background:linear-gradient(135deg,#4f46e5,#7c3aed); display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; color:white; flex-shrink:0; }
    .stat-chip { display:inline-flex; align-items:center; gap:.3rem; padding:.18rem .6rem; border-radius:9999px; font-size:.7rem; font-weight:600; white-space:nowrap; }
    .chip-open     { background:rgba(99,102,241,.12); color:#818cf8; }
    .chip-progress { background:rgba(251,191,36,.12);  color:#fbbf24; }
    .chip-resolved { background:rgba(52,211,153,.12);  color:#34d399; }
@endpush

@section('content')
@if($departments->isEmpty())
<div class="panel" style="padding:4rem;text-align:center;color:var(--muted)">
    <i class="bi bi-building" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
    <div style="font-size:.95rem;font-weight:600">No departments found</div>
    <div style="font-size:.82rem;margin-top:.35rem">No agents with departments are registered yet.</div>
</div>
@else
<div style="display:flex;flex-direction:column;gap:.5rem">
@foreach($departments as $deptName => $agents)
<div class="panel dept-section" style="overflow:hidden">
    <div class="dept-header">
        <div class="dept-icon"><i class="bi bi-building" style="color:#818cf8;font-size:.9rem"></i></div>
        <div>
            <span class="dept-name">{{ $deptName }}</span>
            <span class="dept-count">{{ $agents->count() }} agent{{ $agents->count() !== 1 ? 's' : '' }}</span>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem">
            <span class="stat-chip chip-open">{{ $agents->sum('open_tickets') }} open</span>
            <span class="stat-chip chip-progress">{{ $agents->sum('active_tickets') }} active</span>
            <span class="stat-chip chip-resolved">{{ $agents->sum('resolved_tickets') }} resolved</span>
        </div>
    </div>
    @foreach($agents as $agent)
    <div class="agent-row">
        <div class="agent-avatar">{{ strtoupper(substr($agent->name, 0, 1)) }}</div>
        <div style="flex:1;min-width:0">
            <div style="font-size:.85rem;font-weight:600;color:var(--text)">{{ $agent->name }}</div>
            <div style="font-size:.72rem;color:var(--muted)">{{ $agent->email }}</div>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:flex-end">
            <span class="stat-chip chip-open"><i class="bi bi-circle-fill" style="font-size:.45rem"></i>{{ $agent->open_tickets }} open</span>
            <span class="stat-chip chip-progress"><i class="bi bi-circle-fill" style="font-size:.45rem"></i>{{ $agent->active_tickets }} active</span>
            <span class="stat-chip chip-resolved"><i class="bi bi-circle-fill" style="font-size:.45rem"></i>{{ $agent->resolved_tickets }} resolved</span>
            <span style="font-size:.72rem;color:var(--muted);align-self:center;padding:.18rem .6rem">{{ $agent->total_tickets }} total</span>
        </div>
    </div>
    @endforeach
</div>
@endforeach
</div>
@endif
@endsection
