@extends('layouts.app')
@section('title','Calendar')
@section('topbar-title','Calendar')
@section('topbar-sub','Ticket due dates, SLA deadlines, and schedule overview')

@push('styles')
    /* Lock page scroll for calendar only */
    html, body { overflow: hidden !important; height: 100% !important; }
    .main-wrap  { overflow: hidden !important; height: 100vh; }
    .content    { overflow: hidden !important; display: flex; flex-direction: column; }

    /* The grid fills whatever content height remains */
    .cal-wrap  { display:grid; grid-template-columns:1fr 320px; gap:1rem; flex:1; min-height:0; overflow:hidden; }
    .cal-left  { min-height:0; overflow:hidden; display:flex; flex-direction:column; }
    .cal-right { min-height:0; overflow:hidden; display:flex; flex-direction:column; gap:1rem; }
    @media(max-width:900px){ .cal-wrap { grid-template-columns:1fr; overflow:auto; } .cal-right { height:auto; } }

    /* ── Calendar grid ── */
    .cal-panel { background:var(--surface); border:1px solid var(--border); border-radius:1rem; overflow:hidden; }
    .cal-nav { display:flex; align-items:center; justify-content:space-between; padding:.85rem 1.25rem; border-bottom:1px solid var(--border); }
    .cal-month { font-size:1rem; font-weight:700; color:var(--text); }
    .cal-nav-btn { background:var(--surface2); border:1px solid var(--border); border-radius:.5rem; width:30px; height:30px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--muted); font-size:.85rem; transition:all .15s; }
    .cal-nav-btn:hover { background:rgba(99,102,241,.15); border-color:rgba(99,102,241,.4); color:#818cf8; }
    .cal-grid { display:grid; grid-template-columns:repeat(7,1fr); }
    .cal-dow { padding:.5rem .25rem; text-align:center; font-size:.63rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); border-bottom:1px solid var(--border); }
    .cal-cell { min-height:0; height:100%; padding:.4rem .5rem; border-right:1px solid var(--border); border-bottom:1px solid var(--border); position:relative; cursor:pointer; transition:background .15s; overflow:hidden; }
    .cal-cell:hover { background:var(--surface2); }
    .cal-cell:nth-child(7n) { border-right:none; }
    .cal-cell.other-month .cal-day-num { opacity:.3; }
    .cal-cell.today { background:rgba(99,102,241,.06); }
    .cal-cell.today .cal-day-num { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:white; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-weight:700; }
    .cal-cell.selected { background:rgba(99,102,241,.1); outline:2px solid rgba(99,102,241,.4); outline-offset:-1px; }
    .cal-day-num { font-size:.78rem; font-weight:600; color:var(--text); margin-bottom:.3rem; width:22px; height:22px; display:flex; align-items:center; justify-content:center; }
    .cal-dot { font-size:.65rem; font-weight:700; border-radius:.3rem; padding:.1rem .4rem; margin-bottom:.2rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; display:block; }
    .dot-open     { background:rgba(99,102,241,.15); color:#818cf8; }
    .dot-progress { background:rgba(251,191,36,.12);  color:#fbbf24; }
    .dot-resolved { background:rgba(52,211,153,.12);  color:#34d399; }
    .dot-sla      { background:rgba(248,113,113,.12); color:#f87171; }
    .dot-more     { background:var(--surface2); color:var(--muted); border:1px solid var(--border); cursor:pointer; }

    /* ── Side panel ── */
    .side-panel { background:var(--surface); border:1px solid var(--border); border-radius:1rem; overflow:hidden; }
    .side-header { padding:.85rem 1.1rem; border-bottom:1px solid var(--border); font-size:.85rem; font-weight:700; color:var(--text); }
    .event-item { display:flex; gap:.65rem; padding:.7rem 1.1rem; border-bottom:1px solid var(--border); transition:background .15s; cursor:pointer; }
    .event-item:last-child { border-bottom:none; }
    .event-item:hover { background:var(--surface2); }
    .event-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:.35rem; }
    .event-num  { font-size:.68rem; font-weight:700; color:#818cf8; font-family:monospace; }
    .event-subj { font-size:.8rem; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .event-meta { font-size:.7rem; color:var(--muted); }
    .mini-cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; padding:.85rem 1.1rem; }
    .mini-cal-cell { text-align:center; font-size:.7rem; padding:.25rem .1rem; border-radius:.3rem; cursor:pointer; color:var(--muted); transition:background .15s; }
    .mini-cal-cell:hover { background:var(--surface2); color:var(--text); }
    .mini-cal-cell.today { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:white; font-weight:700; }
    .mini-cal-cell.has-event { color:var(--text); font-weight:600; }
    .mini-cal-cell.other { opacity:.35; }
    .mini-cal-cell.selected { outline:2px solid rgba(99,102,241,.5); outline-offset:-1px; border-radius:.3rem; }
@endpush

@section('content')
@php
    $ticketsByDate = [];
    foreach ($tickets as $t) {
        if ($t->sla_due_at) {
            $key = \Carbon\Carbon::parse($t->sla_due_at)->format('Y-m-d');
            $ticketsByDate[$key][] = ['ticket' => $t, 'kind' => 'sla'];
        }
        $key = $t->created_at->format('Y-m-d');
        $ticketsByDate[$key][] = ['ticket' => $t, 'kind' => 'created'];
    }
@endphp

<div class="cal-wrap">
    {{-- ── Main calendar ── --}}
    <div class="cal-left">
        <div class="cal-panel" style="flex:1;overflow:hidden;display:flex;flex-direction:column">
            <div class="cal-nav">
                <button class="cal-nav-btn" id="prevBtn"><i class="bi bi-chevron-left"></i></button>
                <span class="cal-month" id="calMonthLabel"></span>
                <button class="cal-nav-btn" id="nextBtn"><i class="bi bi-chevron-right"></i></button>
            </div>
            <div class="cal-grid" id="calDow">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                <div class="cal-dow">{{ $d }}</div>
                @endforeach
            </div>
            <div class="cal-grid" id="calDays" style="flex:1;overflow:hidden;grid-auto-rows:1fr"></div>
        </div>
    </div>

    {{-- ── Side panel ── --}}
    <div class="cal-right">
        {{-- Legend --}}
        <div class="side-panel" style="padding:.85rem 1.1rem">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.6rem">Legend</div>
            <div style="display:flex;flex-direction:column;gap:.4rem">
                <div style="display:flex;align-items:center;gap:.5rem"><span class="cal-dot dot-sla" style="margin:0;padding:.15rem .55rem">SLA Due</span><span style="font-size:.75rem;color:var(--muted)">Deadline approaching</span></div>
                <div style="display:flex;align-items:center;gap:.5rem"><span class="cal-dot dot-open" style="margin:0;padding:.15rem .55rem">Open</span><span style="font-size:.75rem;color:var(--muted)">Ticket created</span></div>
                <div style="display:flex;align-items:center;gap:.5rem"><span class="cal-dot dot-progress" style="margin:0;padding:.15rem .55rem">In Progress</span><span style="font-size:.75rem;color:var(--muted)">Active work</span></div>
            </div>
        </div>

        {{-- Selected day events --}}
        <div class="side-panel">
            <div class="side-header" id="sideHeader">Select a day to view tickets</div>
            <div id="sideList">
                <div style="padding:2rem 1.1rem;text-align:center;color:var(--muted)">
                    <i class="bi bi-calendar3" style="font-size:1.8rem;opacity:.2;display:block;margin-bottom:.5rem"></i>
                    <div style="font-size:.8rem">Click any date on the calendar</div>
                </div>
            </div>
        </div>

        {{-- Upcoming SLA deadlines --}}
        <div class="side-panel" style="flex:1;min-height:0;display:flex;flex-direction:column">
            <div class="side-header" style="flex-shrink:0"><i class="bi bi-alarm me-1" style="color:#f87171"></i>Upcoming SLA Deadlines</div>
            @php
                $upcoming = $tickets->filter(fn($t) => $t->sla_due_at && !in_array($t->status, ['resolved','closed']) && \Carbon\Carbon::parse($t->sla_due_at)->isFuture())
                    ->sortBy('sla_due_at')->take(5);
            @endphp
            <div style="overflow-y:auto;flex:1">
                @forelse($upcoming as $t)
                @php $due = \Carbon\Carbon::parse($t->sla_due_at); $diffHrs = now()->diffInHours($due, false); @endphp
                <div class="event-item">
                    <span class="event-dot" style="background:{{ $diffHrs < 24 ? '#f87171' : ($diffHrs < 72 ? '#fbbf24' : '#34d399') }}"></span>
                    <div style="min-width:0;flex:1">
                        <div class="event-num">{{ $t->ticket_number }}</div>
                        <div class="event-subj">{{ $t->subject }}</div>
                        <div class="event-meta">Due {{ $due->format('M d, h:i A') }} · {{ $due->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div style="padding:1.5rem 1.1rem;text-align:center;color:var(--muted);font-size:.8rem">No upcoming SLA deadlines</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const _ticketsByDate = @json($ticketsByDate);

// Convert to flat lookup: date string → array of event objects
const eventMap = {};
Object.entries(_ticketsByDate).forEach(([date, items]) => {
    eventMap[date] = items;
});

let currentYear, currentMonth, selectedDate = null;

function init() {
    const now = new Date();
    currentYear  = now.getFullYear();
    currentMonth = now.getMonth();
    render();
}

function render() {
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('calMonthLabel').textContent = monthNames[currentMonth] + ' ' + currentYear;

    const grid   = document.getElementById('calDays');
    grid.innerHTML = '';

    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysIn   = new Date(currentYear, currentMonth + 1, 0).getDate();
    const prevDays = new Date(currentYear, currentMonth, 0).getDate();
    const today    = new Date();

    let cells = [];

    // Prev month padding
    for (let i = firstDay - 1; i >= 0; i--) {
        cells.push({ day: prevDays - i, month: currentMonth - 1, year: currentMonth === 0 ? currentYear - 1 : currentYear, other: true });
    }
    // Current month
    for (let d = 1; d <= daysIn; d++) {
        cells.push({ day: d, month: currentMonth, year: currentYear, other: false });
    }
    // Next month padding
    let next = 1;
    while (cells.length % 7 !== 0) {
        cells.push({ day: next++, month: currentMonth + 1, year: currentMonth === 11 ? currentYear + 1 : currentYear, other: true });
    }

    cells.forEach(c => {
        const dateStr = `${c.year}-${String(c.month + 1).padStart(2,'0')}-${String(c.day).padStart(2,'0')}`;
        const isToday = !c.other && c.day === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear();
        const isSel   = dateStr === selectedDate;
        const events  = eventMap[dateStr] || [];

        const cell = document.createElement('div');
        cell.className = 'cal-cell' + (c.other ? ' other-month' : '') + (isToday ? ' today' : '') + (isSel ? ' selected' : '');
        cell.dataset.date = dateStr;

        // Day number
        const num = document.createElement('div');
        num.className = 'cal-day-num';
        num.textContent = c.day;
        cell.appendChild(num);

        // Event dots (max 2 visible + more)
        const slaEvents  = events.filter(e => e.kind === 'sla');
        const tickEvents = events.filter(e => e.kind !== 'sla');
        const shown = [...slaEvents, ...tickEvents].slice(0, 2);
        shown.forEach(e => {
            const dot = document.createElement('span');
            const cls = e.kind === 'sla' ? 'dot-sla' : (e.ticket.status === 'progress' ? 'dot-progress' : 'dot-open');
            dot.className = 'cal-dot ' + cls;
            dot.textContent = e.ticket.ticket_number;
            cell.appendChild(dot);
        });
        if (events.length > 2) {
            const more = document.createElement('span');
            more.className = 'cal-dot dot-more';
            more.textContent = '+' + (events.length - 2) + ' more';
            cell.appendChild(more);
        }

        cell.addEventListener('click', () => selectDate(dateStr, events));
        grid.appendChild(cell);
    });
}

function selectDate(dateStr, events) {
    selectedDate = dateStr;
    render();

    const d = new Date(dateStr + 'T00:00:00');
    const label = d.toLocaleDateString('en-US', { weekday:'long', month:'long', day:'numeric', year:'numeric' });
    document.getElementById('sideHeader').textContent = label;

    const list = document.getElementById('sideList');
    if (!events || events.length === 0) {
        list.innerHTML = '<div style="padding:1.5rem 1.1rem;text-align:center;color:var(--muted);font-size:.8rem">No tickets on this day</div>';
        return;
    }

    const statusColors = { open:'#818cf8', progress:'#fbbf24', resolved:'#34d399', closed:'#94a3b8' };
    list.innerHTML = events.map(e => {
        const t = e.ticket;
        const col = statusColors[t.status] || '#94a3b8';
        const kindLabel = e.kind === 'sla' ? '⏰ SLA Due' : '🎫 Created';
        return `<div class="event-item">
            <span class="event-dot" style="background:${col}"></span>
            <div style="min-width:0;flex:1">
                <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.15rem">
                    <span class="event-num">${escH(t.ticket_number)}</span>
                    <span style="font-size:.63rem;color:var(--muted)">${kindLabel}</span>
                </div>
                <div class="event-subj">${escH(t.subject)}</div>
                <div class="event-meta">${escH(t.status)} · ${escH(t.priority || '')} priority${t.assignee ? ' · ' + escH(t.assignee) : ''}</div>
            </div>
        </div>`;
    }).join('');
}

function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

document.getElementById('prevBtn').addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    render();
});
document.getElementById('nextBtn').addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    render();
});

init();
</script>
@endpush
