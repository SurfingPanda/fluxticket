@extends('layouts.app')
@section('title','Reports')
@section('topbar-title','Reports')
@section('topbar-sub','KPI dashboard — volume, speed, and quality metrics')

@push('styles')
/* ── Grid layouts ── */
.kpi-grid    { display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem; }
.charts-grid { display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem; }
.three-grid  { display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem; }
.full-row    { margin-bottom:1rem; }
@media(max-width:1200px){ .kpi-grid { grid-template-columns:repeat(3,1fr); } .three-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:900px) { .kpi-grid { grid-template-columns:repeat(2,1fr); } .charts-grid,.three-grid { grid-template-columns:1fr; } }
@media(max-width:520px) { .kpi-grid { grid-template-columns:1fr 1fr; } }

/* ── Flux Animations ── */
@keyframes popIn { from{opacity:0;transform:scale(.94) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
.reveal { opacity:0;transform:translateY(22px);transition:opacity .5s ease,transform .5s cubic-bezier(.22,.68,0,1.2); }
.reveal.visible { opacity:1;transform:translateY(0); }

/* ── KPI cards ── */
.kpi-card {
    background:var(--surface);border:1px solid var(--border);border-radius:1rem;
    padding:1.25rem 1.3rem;position:relative;overflow:hidden;
    transition:background .3s,border-color .25s,transform .28s cubic-bezier(.22,.68,0,1.3),box-shadow .28s;
    cursor:default;
    animation:popIn .45s cubic-bezier(.22,.68,0,1.2) both;
}
.kpi-card:nth-child(1) { animation-delay:.04s; }
.kpi-card:nth-child(2) { animation-delay:.12s; }
.kpi-card:nth-child(3) { animation-delay:.20s; }
.kpi-card:nth-child(4) { animation-delay:.28s; }
.kpi-card:nth-child(5) { animation-delay:.36s; }
.kpi-card:nth-child(6) { animation-delay:.44s; }
.kpi-card:nth-child(7) { animation-delay:.52s; }
.kpi-card:nth-child(8) { animation-delay:.60s; }
.kpi-card:hover { transform:translateY(-6px) scale(1.03); }
/* Per-card coloured glow matching each card's accent */
.kpi-card:nth-child(1):hover { border-color:rgba(99,102,241,.55);  box-shadow:0 14px 40px rgba(99,102,241,.22), 0 0 0 1px rgba(99,102,241,.2); }
.kpi-card:nth-child(2):hover { border-color:rgba(129,140,248,.55); box-shadow:0 14px 40px rgba(129,140,248,.22),0 0 0 1px rgba(129,140,248,.2); }
.kpi-card:nth-child(3):hover { border-color:rgba(245,158,11,.5);   box-shadow:0 14px 40px rgba(245,158,11,.2),  0 0 0 1px rgba(245,158,11,.2); }
.kpi-card:nth-child(4):hover { border-color:rgba(16,185,129,.5);   box-shadow:0 14px 40px rgba(16,185,129,.2),  0 0 0 1px rgba(16,185,129,.2); }
.kpi-card:nth-child(5):hover { border-color:rgba(148,163,184,.4);  box-shadow:0 14px 40px rgba(148,163,184,.18),0 0 0 1px rgba(148,163,184,.2); }
.kpi-card:nth-child(6):hover { border-color:rgba(6,182,212,.5);    box-shadow:0 14px 40px rgba(6,182,212,.2),   0 0 0 1px rgba(6,182,212,.2); }
.kpi-card:nth-child(7):hover { border-color:rgba(139,92,246,.5);   box-shadow:0 14px 40px rgba(139,92,246,.2),  0 0 0 1px rgba(139,92,246,.2); }
.kpi-card:nth-child(8):hover { border-color:rgba(249,115,22,.5);   box-shadow:0 14px 40px rgba(249,115,22,.2),  0 0 0 1px rgba(249,115,22,.2); }
/* Icon bounce on hover */
.kpi-card:hover .kc-icon { transform:scale(1.2) rotate(-8deg); }
.kpi-card .kc-icon { width:40px;height:40px;border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-size:1.05rem;margin-bottom:.8rem;flex-shrink:0;transition:transform .28s cubic-bezier(.22,.68,0,1.3); }
.kpi-card .kc-val  { font-size:2rem;font-weight:900;line-height:1;margin-bottom:.25rem; }
.kpi-card .kc-lbl  { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted); }
.kpi-card .kc-sub  { font-size:.72rem;color:var(--muted);margin-top:.35rem; }
.kpi-card .kc-bar  { position:absolute;bottom:0;left:0;right:0;height:3px;border-radius:0 0 1rem 1rem; }

/* ── Section panels ── */
.rp { background:var(--surface);border:1px solid var(--border);border-radius:1rem;overflow:hidden; }
.rp-head { padding:.9rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.55rem; }
.rp-title { font-size:.8rem;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.06em; }
.rp-body  { padding:1.15rem 1.25rem; }

/* ── Bar rows ── */
.bar-row { display:flex;align-items:center;gap:.75rem;margin-bottom:.65rem; }
.bar-row:last-child { margin-bottom:0; }
.br-label { font-size:.76rem;color:var(--text);min-width:120px;max-width:120px;text-align:right;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.br-track  { flex:1;height:8px;background:var(--border);border-radius:9999px;overflow:hidden; }
.br-fill   { height:100%;border-radius:9999px;transition:width .8s cubic-bezier(.4,0,.2,1); }
.br-count  { font-size:.75rem;font-weight:700;color:var(--muted);min-width:28px;text-align:right; }

/* ── SLA tiles ── */
.sla-tiles { display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;margin-top:.75rem; }
@media(max-width:600px){ .sla-tiles { grid-template-columns:repeat(2,1fr); } }
.sla-tile { background:var(--surface2);border:1px solid var(--border);border-radius:.7rem;padding:.8rem;text-align:center; }
.sla-tile .st-val { font-size:1.45rem;font-weight:800;line-height:1;margin-bottom:.25rem; }
.sla-tile .st-lbl { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted); }

/* ── Agent table ── */
.agent-table { width:100%;border-collapse:collapse; }
.agent-table th { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);padding:.55rem .7rem;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap; }
.agent-table td { font-size:.78rem;color:var(--text);padding:.6rem .7rem;border-bottom:1px solid var(--border);vertical-align:middle; }
.agent-table tr:last-child td { border-bottom:none; }
.agent-table tr:hover td { background:var(--surface2); }
.rate-bar { height:6px;border-radius:9999px;background:var(--border);overflow:hidden;min-width:55px; }
.rate-fill { height:100%;border-radius:9999px;background:linear-gradient(90deg,#6366f1,#8b5cf6); }

/* ── Stat pair boxes ── */
.stat-pair { display:grid;grid-template-columns:1fr 1fr;gap:.65rem; }
.sp-box { background:var(--surface2);border:1px solid var(--border);border-radius:.75rem;padding:.9rem;text-align:center; }
.sp-val { font-size:1.45rem;font-weight:800;line-height:1;margin-bottom:.25rem; }
.sp-lbl { font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted); }

/* ── Section label ── */
.sec-label { font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:var(--muted);margin:1.25rem 0 .75rem;display:flex;align-items:center;gap:.4rem; }
.sec-label::after { content:'';flex:1;height:1px;background:var(--border); }

/* ── Chart canvas wrapper ── */
.chart-wrap { position:relative; }
@endpush

@section('content')

{{-- ── KPI CARDS ── --}}
@php
$kpis = [
    ['Total',          $stats['total'],               '#6366f1', 'bi-ticket-perforated',  'All time',             'from:#6366f1,to:#8b5cf6'],
    ['Open',           $stats['open'],                '#818cf8', 'bi-circle',             'Awaiting action',      'from:#818cf8,to:#a5b4fc'],
    ['In Progress',    $stats['progress'],            '#f59e0b', 'bi-arrow-repeat',       'Being worked on',      'from:#f59e0b,to:#fbbf24'],
    ['Resolved',       $stats['resolved'],            '#10b981', 'bi-check-circle',       'Successfully resolved', 'from:#10b981,to:#34d399'],
    ['Closed',         $stats['closed'],              '#94a3b8', 'bi-x-circle',           'Fully closed',         'from:#94a3b8,to:#cbd5e1'],
    ['This Week',      $stats['this_week'],           '#06b6d4', 'bi-calendar-week',      'Created last 7 days',  'from:#06b6d4,to:#22d3ee'],
    ['This Month',     $stats['this_month'],          '#8b5cf6', 'bi-calendar-month',     'Created this month',   'from:#8b5cf6,to:#a78bfa'],
    ['Resolution Rate',$stats['resolution_rate'].'%','#f97316', 'bi-percent',            'Resolved ÷ total',     'from:#f97316,to:#fb923c'],
];
@endphp
<div class="kpi-grid">
@foreach($kpis as [$lbl,$val,$color,$icon,$note,$grad])
@php [$from,$to] = array_map('trim', explode(',', str_replace(['from:','to:'],'',$grad))); @endphp
<div class="kpi-card">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.6rem">
        <div class="kc-icon" style="background:{{ $color }}22"><i class="bi {{ $icon }}" style="color:{{ $color }}"></i></div>
        <div style="font-size:.65rem;color:var(--muted);font-weight:600;text-align:right;line-height:1.3">{{ $note }}</div>
    </div>
    <div class="kc-val" style="color:{{ $color }}">{{ $val }}</div>
    <div class="kc-lbl">{{ $lbl }}</div>
    <div class="kc-bar" style="background:linear-gradient(90deg,{{ $from }}88,{{ $to }}88)"></div>
</div>
@endforeach
</div>

{{-- ── CHARTS ROW 1: Status donut + Priority bar + SLA ring ── --}}
<div class="sec-label"><i class="bi bi-pie-chart-fill"></i> Breakdown &amp; SLA</div>
<div class="three-grid" style="margin-bottom:1rem">

    {{-- Status donut --}}
    <div class="rp reveal">
        <div class="rp-head">
            <i class="bi bi-pie-chart" style="color:var(--accent)"></i>
            <span class="rp-title">Ticket Status</span>
        </div>
        <div class="rp-body" style="display:flex;flex-direction:column;align-items:center">
            <div class="chart-wrap" style="width:180px;height:180px;margin-bottom:1rem">
                <canvas id="statusChart"></canvas>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem .9rem;width:100%">
                @foreach(['open'=>['#818cf8','Open'],'progress'=>['#f59e0b','In Progress'],'resolved'=>['#10b981','Resolved'],'closed'=>['#94a3b8','Closed']] as $k=>[$c,$l])
                <div style="display:flex;align-items:center;gap:.4rem">
                    <span style="width:10px;height:10px;border-radius:2px;background:{{ $c }};flex-shrink:0;display:inline-block"></span>
                    <span style="font-size:.73rem;color:var(--muted)">{{ $l }}</span>
                    <span style="font-size:.73rem;font-weight:700;color:var(--text);margin-left:auto">{{ $stats[$k] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Priority bar chart --}}
    <div class="rp reveal">
        <div class="rp-head">
            <i class="bi bi-bar-chart-fill" style="color:var(--accent)"></i>
            <span class="rp-title">By Priority</span>
        </div>
        <div class="rp-body">
            <div class="chart-wrap" style="height:160px;margin-bottom:1rem">
                <canvas id="priorityChart"></canvas>
            </div>
            @php $maxP = max($stats['by_priority']['high']??0,$stats['by_priority']['medium']??0,$stats['by_priority']['low']??0,1); @endphp
            @foreach(['high'=>['#f87171','High','bi-arrow-up-circle-fill'],'medium'=>['#fbbf24','Medium','bi-dash-circle-fill'],'low'=>['#34d399','Low','bi-arrow-down-circle-fill']] as $key=>[$color,$label,$icon])
            @php $cnt = $stats['by_priority'][$key] ?? 0; @endphp
            <div class="bar-row">
                <div class="br-label" style="display:flex;align-items:center;gap:.35rem;justify-content:flex-end">
                    <i class="bi {{ $icon }}" style="color:{{ $color }};font-size:.7rem"></i>{{ $label }}
                </div>
                <div class="br-track"><div class="br-fill" style="width:{{ round($cnt/$maxP*100) }}%;background:{{ $color }}"></div></div>
                <div class="br-count">{{ $cnt }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- SLA compliance ring --}}
    <div class="rp reveal">
        <div class="rp-head">
            <i class="bi bi-shield-check" style="color:var(--accent)"></i>
            <span class="rp-title">SLA Compliance</span>
        </div>
        <div class="rp-body">
            @php
            $slaComp   = $stats['sla_compliance'] ?? 0;
            $slaBreach = $stats['sla_breach_rate'] ?? 0;
            $circumf   = 2 * M_PI * 44;
            $dashOff   = $circumf * (1 - $slaComp / 100);
            $ringColor = $slaComp >= 80 ? '#10b981' : ($slaComp >= 60 ? '#f59e0b' : '#f87171');
            @endphp
            <div style="display:flex;justify-content:center;margin-bottom:.9rem;position:relative">
                <svg width="130" height="130" viewBox="0 0 110 110">
                    <circle cx="55" cy="55" r="44" fill="none" stroke="var(--border)" stroke-width="10"/>
                    <circle cx="55" cy="55" r="44" fill="none" stroke="{{ $ringColor }}" stroke-width="10"
                        stroke-dasharray="{{ $circumf }}" stroke-dashoffset="{{ $dashOff }}"
                        stroke-linecap="round" style="transform:rotate(-90deg);transform-origin:55px 55px;transition:stroke-dashoffset .8s"/>
                </svg>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center">
                    <div style="font-size:1.5rem;font-weight:900;color:{{ $ringColor }}">{{ $slaComp }}%</div>
                    <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;color:var(--muted)">SLA Met</div>
                </div>
            </div>
            <div class="sla-tiles">
                @foreach(['met'=>['#818cf8','Met'],'ok'=>['#10b981','On Track'],'warning'=>['#f59e0b','At Risk'],'breached'=>['#f87171','Breached']] as $k=>[$c,$l])
                <div class="sla-tile">
                    <div class="st-val" style="color:{{ $c }}">{{ $stats['sla'][$k] ?? 0 }}</div>
                    <div class="st-lbl">{{ $l }}</div>
                </div>
                @endforeach
            </div>
            <div style="margin-top:.75rem;padding:.55rem .8rem;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);border-radius:.6rem;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:.72rem;color:var(--muted);font-weight:600">Breach Rate</span>
                <span style="font-size:.88rem;font-weight:800;color:#f87171">{{ $slaBreach }}%</span>
            </div>
        </div>
    </div>

</div>

{{-- ── CHARTS ROW 2: Category bar + Resolution time + Volume snapshot ── --}}
<div class="charts-grid" style="margin-bottom:1rem">

    {{-- By Category --}}
    <div class="rp reveal">
        <div class="rp-head">
            <i class="bi bi-diagram-3" style="color:var(--accent)"></i>
            <span class="rp-title">Tickets by Category</span>
        </div>
        <div class="rp-body">
            <div class="chart-wrap" style="height:180px;margin-bottom:1.1rem">
                <canvas id="categoryChart"></canvas>
            </div>
            @php $maxC = $stats['by_category']->max('count') ?: 1; @endphp
            @forelse($stats['by_category']->sortByDesc('count')->take(10) as $row)
            <div class="bar-row">
                <div class="br-label">{{ $row->category }}</div>
                <div class="br-track"><div class="br-fill" style="width:{{ round($row->count/$maxC*100) }}%;background:linear-gradient(90deg,#6366f1,#8b5cf6)"></div></div>
                <div class="br-count">{{ $row->count }}</div>
            </div>
            @empty
            <div style="text-align:center;color:var(--muted);font-size:.82rem;padding:1rem 0">No data yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Resolution Time + Volume Snapshot stacked --}}
    <div style="display:flex;flex-direction:column;gap:1rem">
        <div class="rp reveal">
            <div class="rp-head">
                <i class="bi bi-clock-history" style="color:var(--accent)"></i>
                <span class="rp-title">Avg Resolution Time</span>
            </div>
            <div class="rp-body">
                <div style="text-align:center;margin-bottom:.9rem">
                    <div style="font-size:2.6rem;font-weight:900;color:var(--accent);line-height:1">
                        {{ $stats['avg_res_hrs'] !== null ? $stats['avg_res_hrs'] : '—' }}
                    </div>
                    <div style="font-size:.72rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-top:.25rem">
                        {{ $stats['avg_res_hrs'] !== null ? 'hours overall avg' : 'no resolved tickets yet' }}
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:.4rem">
                @foreach(['high'=>['High','#f87171'],'medium'=>['Medium','#fbbf24'],'low'=>['Low','#34d399']] as $key=>[$label,$color])
                @php $hrs = $stats['avg_res_by_priority'][$key] ?? null; @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;background:var(--surface2);border-radius:.5rem;padding:.4rem .7rem">
                    <div style="display:flex;align-items:center;gap:.4rem">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $color }};display:inline-block"></span>
                        <span style="font-size:.75rem;color:var(--text);font-weight:500">{{ $label }} Priority</span>
                    </div>
                    <span style="font-size:.82rem;font-weight:800;color:{{ $color }}">{{ $hrs !== null ? $hrs.' hrs' : '—' }}</span>
                </div>
                @endforeach
                </div>
            </div>
        </div>

        <div class="rp reveal">
            <div class="rp-head">
                <i class="bi bi-graph-up-arrow" style="color:var(--accent)"></i>
                <span class="rp-title">Volume Snapshot</span>
            </div>
            <div class="rp-body">
                <div class="stat-pair" style="margin-bottom:.65rem">
                    <div class="sp-box"><div class="sp-val" style="color:#6366f1">{{ $stats['this_week'] }}</div><div class="sp-lbl">This Week</div></div>
                    <div class="sp-box"><div class="sp-val" style="color:#8b5cf6">{{ $stats['this_month'] }}</div><div class="sp-lbl">This Month</div></div>
                </div>
                <div class="stat-pair">
                    <div class="sp-box"><div class="sp-val" style="color:#10b981">{{ $stats['resolved_this_month'] }}</div><div class="sp-lbl">Resolved/Mo</div></div>
                    <div class="sp-box"><div class="sp-val" style="color:#f97316">{{ $stats['resolution_rate'] }}%</div><div class="sp-lbl">Res. Rate</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── By Department ── --}}
<div class="rp full-row reveal" style="margin-bottom:1rem">
    <div class="rp-head">
        <i class="bi bi-building" style="color:var(--accent)"></i>
        <span class="rp-title">Tickets by Department</span>
    </div>
    <div class="rp-body">
        <div class="chart-wrap" style="height:160px;margin-bottom:1rem">
            <canvas id="deptChart"></canvas>
        </div>
        @php $maxD = $stats['by_department']->max('count') ?: 1; @endphp
        @forelse($stats['by_department']->sortByDesc('count') as $row)
        @if($row->department)
        <div class="bar-row">
            <div class="br-label">{{ $row->department }}</div>
            <div class="br-track"><div class="br-fill" style="width:{{ round($row->count/$maxD*100) }}%;background:linear-gradient(90deg,#0891b2,#06b6d4)"></div></div>
            <div class="br-count">{{ $row->count }}</div>
        </div>
        @endif
        @empty
        <div style="text-align:center;color:var(--muted);font-size:.82rem;padding:1rem 0">No routed tickets yet.</div>
        @endforelse
    </div>
</div>

{{-- ── AGENT LEADERBOARD ── --}}
<div class="sec-label"><i class="bi bi-people-fill"></i> Agent Performance</div>
<div class="rp full-row reveal">
    <div class="rp-head">
        <i class="bi bi-trophy-fill" style="color:#f59e0b"></i>
        <span class="rp-title">Agent Leaderboard</span>
        <span style="margin-left:auto;font-size:.7rem;color:var(--muted)">Top agents by ticket volume</span>
    </div>
    <div style="overflow-x:auto">
        <table class="agent-table">
            <thead>
                <tr>
                    <th>#</th><th>Agent</th><th>Department</th>
                    <th>Total</th><th>Resolved</th><th>Open/Active</th>
                    <th>Resolution Rate</th><th>Avg Res. Time</th>
                </tr>
            </thead>
            <tbody>
            @forelse($stats['agent_perf'] as $i => $a)
            @php $rankColor = match(true){ $i===0=>'#f59e0b',$i===1=>'#94a3b8',$i===2=>'#cd7c2f',default=>'var(--muted)' }; @endphp
            <tr>
                <td>
                    @if($i < 3)<i class="bi bi-trophy-fill" style="color:{{ $rankColor }};font-size:.8rem"></i>
                    @else<span style="color:var(--muted)">{{ $i+1 }}</span>@endif
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:.55rem">
                        <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#fff;flex-shrink:0">{{ strtoupper(substr($a->name,0,1)) }}</div>
                        <span style="font-weight:600">{{ $a->name }}</span>
                    </div>
                </td>
                <td><span style="background:var(--surface2);border:1px solid var(--border);border-radius:.35rem;padding:.15rem .45rem;font-size:.7rem;color:var(--muted);white-space:nowrap">{{ $a->dept }}</span></td>
                <td style="font-weight:800;color:var(--text)">{{ $a->total }}</td>
                <td style="color:#10b981;font-weight:700">{{ $a->resolved }}</td>
                <td style="color:#f59e0b;font-weight:700">{{ $a->open }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <div class="rate-bar" style="width:72px"><div class="rate-fill" style="width:{{ $a->rate }}%"></div></div>
                        <span style="font-size:.76rem;font-weight:700;color:var(--text)">{{ $a->rate }}%</span>
                    </div>
                </td>
                <td style="font-weight:600;color:{{ $a->avg_hrs !== null ? 'var(--accent)' : 'var(--muted)' }}">{{ $a->avg_hrs !== null ? $a->avg_hrs.' hrs' : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:2.5rem;font-size:.82rem">No assigned tickets yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const isDark  = document.body.classList.contains('dark');
    const surface = isDark ? '#1e293b' : '#ffffff';
    const border  = isDark ? '#334155' : '#e2e8f0';
    const muted   = isDark ? '#94a3b8' : '#64748b';
    const text    = isDark ? '#e2e8f0' : '#1e293b';

    Chart.defaults.color = muted;
    Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
    Chart.defaults.font.size   = 11;

    const pluginNoData = {
        id:'noData',
        afterDraw(chart) {
            const total = chart.data.datasets[0]?.data?.reduce((a,b)=>a+b,0) || 0;
            if (total > 0) return;
            const {ctx,chartArea:{left,top,width,height}} = chart;
            ctx.save();
            ctx.fillStyle = muted;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = '13px Segoe UI';
            ctx.fillText('No data yet', left + width / 2, top + height / 2);
            ctx.restore();
        }
    };

    // Status donut
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Open','In Progress','Resolved','Closed'],
            datasets:[{ data:[{{ $stats['open'] }},{{ $stats['progress'] }},{{ $stats['resolved'] }},{{ $stats['closed'] }}],
                backgroundColor:['#818cf8','#f59e0b','#10b981','#94a3b8'],
                borderColor: surface, borderWidth:3, hoverOffset:6 }]
        },
        options:{
            cutout:'72%', responsive:true, maintainAspectRatio:true,
            plugins:{ legend:{display:false}, tooltip:{callbacks:{label:ctx=>' '+ctx.label+': '+ctx.parsed}} }
        },
        plugins:[pluginNoData]
    });

    // Priority bar
    new Chart(document.getElementById('priorityChart'), {
        type: 'bar',
        data:{
            labels:['High','Medium','Low'],
            datasets:[{ data:[{{ $stats['by_priority']['high'] ?? 0 }},{{ $stats['by_priority']['medium'] ?? 0 }},{{ $stats['by_priority']['low'] ?? 0 }}],
                backgroundColor:['rgba(248,113,113,.8)','rgba(251,191,36,.8)','rgba(52,211,153,.8)'],
                borderRadius:6, borderSkipped:false }]
        },
        options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false} },
            scales:{
                x:{ grid:{display:false}, ticks:{color:muted} },
                y:{ grid:{color:border}, ticks:{color:muted,stepSize:1,precision:0} }
            }
        },
        plugins:[pluginNoData]
    });

    // Category horizontal bar
    @php
    $catLabels = $stats['by_category']->sortByDesc('count')->take(8)->pluck('category');
    $catData   = $stats['by_category']->sortByDesc('count')->take(8)->pluck('count');
    @endphp
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data:{
            labels: @json($catLabels->values()),
            datasets:[{ data: @json($catData->values()),
                backgroundColor:'rgba(99,102,241,.75)', borderRadius:5, borderSkipped:false }]
        },
        options:{
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false} },
            scales:{
                x:{ grid:{color:border}, ticks:{color:muted,precision:0} },
                y:{ grid:{display:false}, ticks:{color:text} }
            }
        },
        plugins:[pluginNoData]
    });

    // Department bar
    @php
    $deptData = $stats['by_department']->filter(fn($r)=>$r->department);
    $deptLabels = $deptData->pluck('department');
    $deptCounts = $deptData->pluck('count');
    @endphp
    new Chart(document.getElementById('deptChart'), {
        type: 'bar',
        data:{
            labels: @json($deptLabels->values()),
            datasets:[{ data: @json($deptCounts->values()),
                backgroundColor:'rgba(6,182,212,.75)', borderRadius:5, borderSkipped:false }]
        },
        options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false} },
            scales:{
                x:{ grid:{display:false}, ticks:{color:muted} },
                y:{ grid:{color:border}, ticks:{color:muted,stepSize:1,precision:0} }
            }
        },
        plugins:[pluginNoData]
    });
})();

// Reveal panels on scroll
(function(){
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if(e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); } });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
})();
</script>
@endpush
