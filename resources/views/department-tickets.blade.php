@extends('layouts.app')
@section('title','Department Tickets')
@section('topbar-title','Department Tickets')
@section('topbar-sub', $dept
    ? ($tickets->count() . ' ticket' . ($tickets->count() !== 1 ? 's' : '') . ' for the ' . $dept . ' department')
    : 'No department assigned to your account')

@push('styles')
    @keyframes spin { to { transform:rotate(360deg); } }
    .topbar-search { display:flex; align-items:center; gap:.5rem; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; padding:.4rem .85rem; width:220px; transition:border-color .2s; }
    .topbar-search:focus-within { border-color:var(--accent); }
    .topbar-search input { border:none; background:transparent; outline:none; font-size:.825rem; color:var(--text); width:100%; }
    .topbar-search input::placeholder { color:var(--muted); }
    .sla-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .6rem; border-radius:9999px; font-size:.68rem; font-weight:700; white-space:nowrap; }
    .sla-ok { background:rgba(52,211,153,.12); color:#34d399; } .sla-warning { background:rgba(251,191,36,.12); color:#fbbf24; }
    .sla-breached { background:rgba(248,113,113,.12); color:#f87171; } .sla-met { background:rgba(99,102,241,.12); color:#818cf8; }
    .sla-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
    .sla-ok .sla-dot { background:#34d399; } .sla-warning .sla-dot { background:#fbbf24; }
    .sla-breached .sla-dot { background:#f87171; } .sla-met .sla-dot { background:#818cf8; }
    .btn-view { background:var(--surface2); border:1px solid var(--border); border-radius:.45rem; padding:.25rem .7rem; font-size:.72rem; font-weight:600; color:var(--muted); cursor:pointer; transition:all .15s; white-space:nowrap; }
    .btn-view:hover { background:rgba(99,102,241,.15); border-color:rgba(99,102,241,.4); color:#818cf8; }
    .btn-accept { background:rgba(52,211,153,.1); border:1px solid rgba(52,211,153,.25); border-radius:.45rem; padding:.25rem .7rem; font-size:.72rem; font-weight:600; color:#34d399; cursor:pointer; transition:all .15s; white-space:nowrap; }
    .btn-accept:hover { background:rgba(52,211,153,.2); border-color:rgba(52,211,153,.5); }
    .s-rejected { background:rgba(248,113,113,.12); color:#f87171; border:1px solid rgba(248,113,113,.25); }
    .unassigned-tag { display:inline-flex; align-items:center; gap:.25rem; font-size:.68rem; font-weight:700; color:#fbbf24; }
    /* Modal styles */
    .flux-modal-backdrop { position:fixed; inset:0; z-index:999; background:rgba(0,0,0,.6); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; padding:1rem; opacity:0; pointer-events:none; transition:opacity .2s; }
    .flux-modal-backdrop.open { opacity:1; pointer-events:all; }
    .flux-modal { background:var(--surface); border:1px solid var(--border); border-radius:1.25rem; width:100%; max-width:640px; box-shadow:0 30px 80px rgba(0,0,0,.45); transform:translateY(16px) scale(.98); transition:transform .25s,opacity .25s; opacity:0; max-height:92vh; display:flex; flex-direction:column; }
    .flux-modal-backdrop.open .flux-modal { transform:translateY(0) scale(1); opacity:1; }
    .flux-modal-header { padding:1.15rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; flex-shrink:0; }
    .flux-modal-body { padding:1.35rem 1.5rem; overflow-y:auto; flex:1; }
    .flux-modal-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:.65rem; flex-shrink:0; flex-wrap:wrap; }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem 1.25rem; margin-bottom:1.25rem; }
    .info-item .info-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:.2rem; }
    .info-item .info-val { font-size:.875rem; color:var(--text); font-weight:500; }
    .desc-box { background:var(--surface2); border:1px solid var(--border); border-radius:.75rem; padding:.85rem 1rem; font-size:.85rem; color:var(--text); line-height:1.6; white-space:pre-wrap; margin-bottom:1rem; }
    .modal-section { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); margin:.25rem 0 .75rem; display:flex; align-items:center; gap:.5rem; }
    .modal-section::after { content:''; flex:1; height:1px; background:var(--border); }
    .btn-cancel { background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; color:var(--muted); font-size:.875rem; font-weight:600; padding:.5rem 1.1rem; cursor:pointer; transition:background .15s,color .15s; }
    .btn-cancel:hover { background:var(--border); color:var(--text); }
    .resolution-box { background:rgba(52,211,153,.08); border:1px solid rgba(52,211,153,.2); border-radius:.75rem; padding:.85rem 1rem; font-size:.85rem; color:#34d399; line-height:1.6; white-space:pre-wrap; }
@endpush

@section('content')
@php
    $statusMap   = ['open'=>['s-open','Open'],'progress'=>['s-progress','In Progress'],'resolved'=>['s-resolved','Resolved'],'closed'=>['s-closed','Closed'],'rejected'=>['s-rejected','Rejected']];
    $priorityMap = ['high'=>'p-high','medium'=>'p-medium','low'=>'p-low'];
@endphp

<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="bi bi-building me-2" style="color:var(--accent)"></i>{{ $dept ? $dept . ' Department Tickets' : 'Department Tickets' }}</span>
        <div id="statusFilters" class="d-flex gap-2 flex-wrap">
            <button class="filter-btn active" data-status="all"      onclick="filterStatus('all',this)">All</button>
            <button class="filter-btn"        data-status="open"     onclick="filterStatus('open',this)">Open</button>
            <button class="filter-btn"        data-status="progress" onclick="filterStatus('progress',this)">In Progress</button>
            <button class="filter-btn"        data-status="resolved" onclick="filterStatus('resolved',this)">Resolved</button>
            <button class="filter-btn"        data-status="closed"   onclick="filterStatus('closed',this)">Closed</button>
        </div>
    </div>
    <div style="padding:.55rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:1.75rem;flex-wrap:wrap;background:var(--surface2)">
        <div style="display:flex;align-items:center;gap:.6rem">
            <span style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);white-space:nowrap">Priority</span>
            <div id="priorityFilters" class="d-flex gap-1">
                <button class="filter-btn active" data-priority="all"    onclick="filterPriority('all',this)">All</button>
                <button class="filter-btn"        data-priority="high"   onclick="filterPriority('high',this)" style="color:#f87171">High</button>
                <button class="filter-btn"        data-priority="medium" onclick="filterPriority('medium',this)" style="color:#fbbf24">Medium</button>
                <button class="filter-btn"        data-priority="low"    onclick="filterPriority('low',this)" style="color:#34d399">Low</button>
            </div>
        </div>
        <div style="width:1px;height:20px;background:var(--border);flex-shrink:0"></div>
        <div style="display:flex;align-items:center;gap:.6rem">
            <span style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);white-space:nowrap">Type</span>
            <div id="typeFilters" class="d-flex gap-1 flex-wrap">
                <button class="filter-btn active" data-type="all"             onclick="filterType('all',this)">All</button>
                <button class="filter-btn"        data-type="incident"        onclick="filterType('incident',this)">Incident</button>
                <button class="filter-btn"        data-type="service_request" onclick="filterType('service_request',this)">Service Request</button>
                <button class="filter-btn"        data-type="question"        onclick="filterType('question',this)">Question</button>
                <button class="filter-btn"        data-type="change_request"  onclick="filterType('change_request',this)">Change Request</button>
            </div>
        </div>
    </div>

    @if(!$dept)
    <div style="padding:4rem;text-align:center;color:var(--muted)">
        <i class="bi bi-building-x" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
        <div style="font-size:.95rem;font-weight:600">No department assigned</div>
        <div style="font-size:.82rem;margin-top:.35rem">Your account has no department, so there are no department tickets to show. Contact an administrator.</div>
    </div>
    @elseif($tickets->isEmpty())
    <div style="padding:4rem;text-align:center;color:var(--muted)">
        <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
        <div style="font-size:.95rem;font-weight:600">No tickets for {{ $dept }} yet</div>
        <div style="font-size:.82rem;margin-top:.35rem">Tickets routed to or submitted by your department will appear here.</div>
    </div>
    @else
    <div id="tableWrap" style="overflow-x:auto">
        <table class="flux-table" id="ticketTable">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Subject</th>
                    <th>Requester</th>
                    <th>Department</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assignee</th>
                    <th>Created</th>
                    <th style="text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $t)
                @php
                    // Accept is offered for unassigned, non-terminal tickets that belong to
                    // this department (routed here) or are not yet routed anywhere.
                    $canAccept = empty($t->assignee)
                        && !in_array($t->status, ['resolved','closed','rejected'])
                        && (empty($t->department) || $t->department === $dept);
                @endphp
                <tr data-status="{{ $t->status }}" data-priority="{{ $t->priority }}" data-type="{{ $t->type ?? '' }}" data-search="{{ strtolower($t->ticket_number . ' ' . $t->subject . ' ' . ($t->requester ?? $t->user->name ?? '') . ' ' . $t->category . ' ' . ($t->type ?? '') . ' ' . ($t->assignee ?? '')) }}">
                    <td><span style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace">{{ $t->ticket_number }}</span></td>
                    <td style="max-width:200px">
                        <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->subject }}</div>
                        <div style="font-size:.72rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->description }}</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="width:25px;height:25px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:white;flex-shrink:0">{{ strtoupper(substr($t->requester ?? $t->user->name ?? '?', 0, 1)) }}</div>
                            <span style="white-space:nowrap">{{ $t->requester ?? $t->user->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td style="color:var(--muted);font-size:.82rem;white-space:nowrap">{{ $t->department ?: ($t->requester_dept ?: '—') }}</td>
                    <td><span class="badge-priority {{ $priorityMap[$t->priority] ?? 'p-low' }}">{{ ucfirst($t->priority) }}</span></td>
                    <td><span class="badge-status {{ $statusMap[$t->status][0] ?? 's-open' }}">{{ $statusMap[$t->status][1] ?? 'Open' }}</span></td>
                    <td style="white-space:nowrap">
                        @if($t->assignee)
                            <span style="font-size:.82rem">{{ $t->assignee }}</span>
                        @else
                            <span class="unassigned-tag"><i class="bi bi-person-dash"></i> Unassigned</span>
                        @endif
                    </td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->created_at->diffForHumans() }}</td>
                    <td style="text-align:center">
                        <div style="display:flex;align-items:center;justify-content:center;gap:.4rem">
                            <button class="btn-view" onclick='openView(@json($t->toArray()), @json($t->requester ?? $t->user->name ?? "Unknown"))'>
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                            @if($canAccept)
                            <button class="btn-accept" onclick="openAccept({{ $t->id }}, '{{ addslashes($t->ticket_number) }}')">
                                <i class="bi bi-check2-circle me-1"></i>Accept
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

@push('modals')
{{-- Read-only View Modal --}}
<div class="flux-modal-backdrop" id="viewModal">
    <div class="flux-modal">
        <div class="flux-modal-header">
            <div>
                <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.25rem">
                    <div style="width:26px;height:26px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:.4rem;display:flex;align-items:center;justify-content:center;font-size:.7rem"><span style="color:white">🎫</span></div>
                    <span id="vm-number" style="font-size:.85rem;font-weight:800;color:#818cf8;font-family:monospace"></span>
                    <span id="vm-status-badge" class="badge-status"></span>
                </div>
                <div id="vm-subject" style="font-size:1rem;font-weight:700;color:var(--text)"></div>
            </div>
            <button onclick="closeModal('viewModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem;flex-shrink:0"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="flux-modal-body">
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Requester</div><div class="info-val" id="vm-requester"></div></div>
                <div class="info-item"><div class="info-label">Category</div><div class="info-val" id="vm-category"></div></div>
                <div class="info-item"><div class="info-label">Priority</div><div id="vm-priority"></div></div>
                <div class="info-item"><div class="info-label">Created</div><div class="info-val" id="vm-created"></div></div>
                <div class="info-item"><div class="info-label">Department</div><div class="info-val" id="vm-department"></div></div>
                <div class="info-item"><div class="info-label">Assigned To</div><div class="info-val" id="vm-assignee"></div></div>
                <div class="info-item"><div class="info-label">Resolved By</div><div class="info-val" id="vm-resolved-by"></div></div>
                <div class="info-item" id="vm-rejected-by-row" style="display:none"><div class="info-label" style="color:#f87171">Rejected By</div><div class="info-val" style="color:#f87171" id="vm-rejected-by"></div></div>
                <div class="info-item"><div class="info-label">SLA Deadline</div><div class="info-val" id="vm-sla-due"></div></div>
                <div class="info-item"><div class="info-label">SLA Status</div><div id="vm-sla-badge"></div></div>
            </div>
            <div id="vm-sla-bar-wrap" style="margin-bottom:1rem;display:none">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-bottom:.3rem"><span>SLA Progress</span><span id="vm-sla-pct-label"></span></div>
                <div style="height:6px;background:var(--surface2);border-radius:9999px;overflow:hidden"><div id="vm-sla-bar" style="height:100%;border-radius:9999px;transition:width .4s"></div></div>
                <div style="font-size:.7rem;color:var(--muted);margin-top:.25rem" id="vm-sla-time-label"></div>
            </div>
            <div class="modal-section">Description</div>
            <div class="desc-box" id="vm-description"></div>
            <div id="vm-resolution-section" style="display:none">
                <div class="modal-section">Resolution</div>
                <div class="resolution-box" id="vm-resolution"></div>
            </div>
            <div id="vm-rejection-section" style="display:none">
                <div class="modal-section" style="color:#f87171">Rejection Reason</div>
                <div style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);border-radius:.75rem;padding:.85rem 1rem;font-size:.85rem;color:#f87171;line-height:1.6;white-space:pre-wrap" id="vm-rejection-reason"></div>
            </div>
            <div class="modal-section" style="margin-top:1.25rem">Activity &amp; Notes</div>
            <div id="vm-notes-timeline"></div>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('viewModal')">Close</button>
            <a id="vm-print-btn" href="#" target="_blank" class="btn-ghost"><i class="bi bi-printer"></i> Print / PDF</a>
            <button id="vm-accept-btn" onclick="acceptFromView()" style="display:none;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);border-radius:.6rem;color:#34d399;font-size:.875rem;font-weight:600;padding:.5rem 1.1rem;align-items:center;gap:.4rem;cursor:pointer"><i class="bi bi-check2-circle"></i> Accept Ticket</button>
        </div>
    </div>
</div>

{{-- Accept Confirmation Modal --}}
<div class="flux-modal-backdrop" id="acceptModal">
    <div class="flux-modal" style="max-width:440px">
        <div class="flux-modal-header">
            <div>
                <div style="font-size:.95rem;font-weight:700;color:#34d399">Accept Ticket</div>
                <div id="accept-sub" style="font-size:.75rem;color:var(--muted)"></div>
            </div>
            <button onclick="closeModal('acceptModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="flux-modal-body">
            <div style="background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);border-radius:.75rem;padding:.75rem 1rem;font-size:.82rem;color:#34d399;display:flex;align-items:flex-start;gap:.5rem">
                <i class="bi bi-info-circle-fill" style="flex-shrink:0;margin-top:.1rem"></i>
                <span>This ticket will be assigned to <strong>you</strong> and moved to <strong>In Progress</strong>. The requester will be notified.</span>
            </div>
            <form id="acceptForm" method="POST">@csrf</form>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('acceptModal')">Cancel</button>
            <button type="button" id="acceptConfirmBtn" onclick="submitAccept()" style="background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);border-radius:.6rem;color:#34d399;font-size:.875rem;font-weight:600;padding:.5rem 1.25rem;cursor:pointer;display:flex;align-items:center;gap:.4rem"><i class="bi bi-check2-circle"></i> Confirm Accept</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const myDept = @json($dept);

    @if(!empty($openTicket))
    document.addEventListener('DOMContentLoaded', function () {
        const _ot = @json($openTicket->load(['user','notes.user'])->toArray());
        openView(_ot, _ot.requester || (_ot.user?.name ?? 'Unknown'));
    });
    @endif

    let _acceptTicketId  = null;
    let _acceptTicketNum = null;

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Notes timeline (read-only) ──
    function renderNoteHtml(n) {
        const isRoute     = n.type === 'route_event';
        const isRejection = n.type === 'rejection';
        const isSystem    = n.type === 'system' || n.type === 'status_change';
        const dateStr  = new Date(n.created_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'});
        const rawBody  = (n.content||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')
            .replace(/^&gt; (.+)$/gm,'<div style="border-left:3px solid rgba(248,113,113,.3);padding-left:.6rem;color:var(--muted);margin-top:.3rem;font-size:.8rem">$1</div>');
        if (isRejection) {
            return `<div style="display:flex;gap:.7rem;margin-bottom:.85rem"><div style="width:28px;height:28px;min-width:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:#1a1a2e;background:linear-gradient(135deg,#f59e0b,#d97706)">SYS</div><div style="flex:1;min-width:0"><div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem"><span style="font-size:.8rem;font-weight:600;color:#fbbf24">System</span><span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(248,113,113,.15);color:#f87171;padding:.1rem .45rem;border-radius:9999px"><i class="bi bi-x-circle-fill"></i> Rejected</span><span style="font-size:.7rem;color:var(--muted)">${dateStr}</span></div><div style="font-size:.83rem;color:var(--text);line-height:1.55;background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.25);border-radius:.5rem;padding:.55rem .8rem">${rawBody}</div></div></div>`;
        }
        if (isSystem) {
            return `<div style="display:flex;gap:.7rem;margin-bottom:.85rem"><div style="width:28px;height:28px;min-width:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:#1a1a2e;background:linear-gradient(135deg,#f59e0b,#d97706)">SYS</div><div style="flex:1;min-width:0"><div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem"><span style="font-size:.8rem;font-weight:600;color:#fbbf24">System</span><span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(251,191,36,.12);color:#fbbf24;padding:.1rem .45rem;border-radius:9999px"><i class="bi bi-info-circle-fill"></i> System</span><span style="font-size:.7rem;color:var(--muted)">${dateStr}</span></div><div style="font-size:.83rem;color:var(--text);line-height:1.55;background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.18);border-radius:.5rem;padding:.55rem .8rem">${rawBody}</div></div></div>`;
        }
        const initial  = (n.user?.name || '?').charAt(0).toUpperCase();
        const avatarBg = isRoute ? 'linear-gradient(135deg,#2563eb,#4f46e5)' : 'linear-gradient(135deg,#4f46e5,#7c3aed)';
        const chip     = isRoute
            ? `<span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(59,130,246,.15);color:#60a5fa;padding:.1rem .45rem;border-radius:9999px"><i class="bi bi-arrow-left-right"></i> Routed</span>`
            : `<span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(99,102,241,.15);color:#818cf8;padding:.1rem .45rem;border-radius:9999px"><i class="bi bi-chat-left-text"></i> Note</span>`;
        const attachHtml = n.attachment ? `<div style="margin-top:.4rem"><a href="/storage/${n.attachment}" target="_blank" style="font-size:.75rem;color:#818cf8;display:inline-flex;align-items:center;gap:.3rem"><i class="bi bi-paperclip"></i> View attachment</a></div>` : '';
        return `<div style="display:flex;gap:.7rem;margin-bottom:.85rem"><div style="width:28px;height:28px;min-width:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:white;background:${avatarBg}">${initial}</div><div style="flex:1;min-width:0"><div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem"><span style="font-size:.8rem;font-weight:600;color:var(--text)">${escHtml(n.user?.name||'Unknown')}</span>${chip}<span style="font-size:.7rem;color:var(--muted)">${dateStr}</span></div><div style="font-size:.83rem;color:var(--text);line-height:1.55;background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;padding:.55rem .8rem">${rawBody}${attachHtml}</div></div></div>`;
    }
    function renderTimeline(notes) {
        const tl = document.getElementById('vm-notes-timeline');
        tl.innerHTML = notes.length ? notes.map(renderNoteHtml).join('') : '<div style="text-align:center;color:var(--muted);font-size:.82rem;padding:.75rem 0;font-style:italic">No notes yet.</div>';
    }

    // ── SLA helpers ──
    const slaLabels = { ok:'On Track', warning:'At Risk', breached:'Breached', met:'Met' };
    const slaColors = { ok:'#34d399', warning:'#fbbf24', breached:'#f87171', met:'#818cf8' };

    function openView(t, requesterName) {
        document.getElementById('vm-number').textContent  = t.ticket_number;
        document.getElementById('vm-subject').textContent = t.subject;
        const sMap = { open:'s-open', progress:'s-progress', resolved:'s-resolved', closed:'s-closed', rejected:'s-rejected' };
        const sLabel = { open:'Open', progress:'In Progress', resolved:'Resolved', closed:'Closed', rejected:'Rejected' };
        const sb = document.getElementById('vm-status-badge');
        sb.className = 'badge-status ' + (sMap[t.status]||'s-open');
        sb.textContent = sLabel[t.status]||t.status;
        document.getElementById('vm-requester').textContent   = requesterName;
        document.getElementById('vm-category').textContent    = t.category || '—';
        const pMap = { high:'p-high', medium:'p-medium', low:'p-low' };
        document.getElementById('vm-priority').innerHTML = `<span class="badge-priority ${pMap[t.priority]||'p-low'}">${(t.priority||'').charAt(0).toUpperCase()+(t.priority||'').slice(1)}</span>`;
        document.getElementById('vm-created').textContent     = t.created_at ? new Date(t.created_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'}) : '—';
        document.getElementById('vm-department').textContent  = t.department || t.requester_dept || '—';
        document.getElementById('vm-assignee').textContent    = t.assignee || 'Unassigned';
        document.getElementById('vm-resolved-by').textContent = t.resolved_by || '—';
        document.getElementById('vm-description').textContent = t.description || '';

        const resSection = document.getElementById('vm-resolution-section');
        if (t.resolution) { resSection.style.display=''; document.getElementById('vm-resolution').textContent=t.resolution; } else { resSection.style.display='none'; }

        const isRejected = t.status === 'rejected';
        const rejectedByRow = document.getElementById('vm-rejected-by-row');
        const rejectionSection = document.getElementById('vm-rejection-section');
        if (isRejected) {
            rejectedByRow.style.display = '';
            document.getElementById('vm-rejected-by').textContent = t.rejected_by || '—';
            rejectionSection.style.display = '';
            document.getElementById('vm-rejection-reason').textContent = t.rejection_reason || '';
        } else {
            rejectedByRow.style.display = 'none';
            rejectionSection.style.display = 'none';
        }

        // SLA
        const slaDue=document.getElementById('vm-sla-due'), slaBadge=document.getElementById('vm-sla-badge'),
              slaBarWrap=document.getElementById('vm-sla-bar-wrap'), slaBar=document.getElementById('vm-sla-bar'),
              slaPctLabel=document.getElementById('vm-sla-pct-label'), slaTimeLabel=document.getElementById('vm-sla-time-label');
        if (t.sla_due_at) {
            const due=new Date(t.sla_due_at), created=new Date(t.created_at), now=new Date();
            const isDone=['resolved','closed','rejected'].includes(t.status);
            const compareAt=isDone&&(t.resolved_at||t.rejected_at)?new Date(t.resolved_at||t.rejected_at):now;
            let ss; if(compareAt>due) ss='breached'; else if(isDone) ss='met'; else { const total=due-created,rem=due-now; ss=(rem/total)<=0.25?'warning':'ok'; }
            slaDue.textContent=new Date(t.sla_due_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'});
            slaBadge.innerHTML=`<span class="sla-badge sla-${ss}"><span class="sla-dot"></span>${slaLabels[ss]}</span>`;
            if (!isDone) {
                slaBarWrap.style.display='';
                const totalSec=(due-created)/1000, usedSec=(now-created)/1000, pct=Math.min(100,Math.round((usedSec/totalSec)*100));
                const remMs=due-now, remHrs=Math.round(remMs/3600000), remDays=Math.floor(remHrs/24);
                const remLabel=remMs<0?`Overdue by ${Math.abs(remDays)}d ${Math.abs(remHrs%24)}h`:remDays>0?`${remDays}d ${remHrs%24}h remaining`:`${remHrs}h remaining`;
                slaBar.style.width=pct+'%'; slaBar.style.background=slaColors[ss]; slaPctLabel.textContent=pct+'% elapsed'; slaTimeLabel.textContent=remLabel;
            } else { slaBarWrap.style.display='none'; }
        } else { slaDue.textContent='—'; slaBadge.innerHTML='<span style="color:var(--muted)">—</span>'; slaBarWrap.style.display='none'; }

        document.getElementById('vm-print-btn').href='/tickets/'+t.id+'/print';
        renderTimeline(t.notes || []);

        // Accept button — unassigned, non-terminal tickets in my department (or not yet routed)
        const canAccept = !t.assignee
            && !['resolved','closed','rejected'].includes(t.status)
            && (!t.department || t.department === myDept);
        const acceptBtn = document.getElementById('vm-accept-btn');
        acceptBtn.style.display = canAccept ? 'flex' : 'none';
        acceptBtn.dataset.ticketId  = t.id;
        acceptBtn.dataset.ticketNum = t.ticket_number;

        openModal('viewModal');
    }

    // ── Accept ──
    function openAccept(id, num) {
        _acceptTicketId  = id;
        _acceptTicketNum = num;
        document.getElementById('accept-sub').textContent = num + ' — Assign this ticket to yourself';
        document.getElementById('acceptForm').action      = '/tickets/' + id + '/assign-me';
        openModal('acceptModal');
    }
    function acceptFromView() {
        const btn = document.getElementById('vm-accept-btn');
        closeModal('viewModal');
        openAccept(btn.dataset.ticketId, btn.dataset.ticketNum || '');
    }
    function submitAccept() {
        const btn = document.getElementById('acceptConfirmBtn');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(52,211,153,.35);border-top-color:#34d399;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.35rem"></span> Accepting…';
        document.getElementById('acceptForm').submit();
    }

    document.addEventListener('keydown', e => { if(e.key==='Escape') ['viewModal','acceptModal'].forEach(closeModal); });

    // ── Filters ──
    const _FKEY = 'flux_filter_department';
    let _currentStatus = 'all', _currentPriority = 'all', _currentType = 'all';

    function _saveFilter() {
        try { localStorage.setItem(_FKEY, JSON.stringify({ status:_currentStatus, priority:_currentPriority, type:_currentType })); } catch(e){}
    }
    function filterStatus(s, btn) {
        _currentStatus = s;
        document.querySelectorAll('#statusFilters .filter-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        _saveFilter(); applyFilters();
    }
    function filterPriority(p, btn) {
        _currentPriority = p;
        document.querySelectorAll('#priorityFilters .filter-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        _saveFilter(); applyFilters();
    }
    function filterType(t, btn) {
        _currentType = t;
        document.querySelectorAll('#typeFilters .filter-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        _saveFilter(); applyFilters();
    }
    function applyFilters() {
        const q = (document.getElementById('searchInput')?.value || '').toLowerCase();
        document.querySelectorAll('#ticketTable tbody tr').forEach(row => {
            const matchStatus   = _currentStatus==='all'   || row.dataset.status===_currentStatus;
            const matchPriority = _currentPriority==='all' || row.dataset.priority===_currentPriority;
            const matchType     = _currentType==='all'     || row.dataset.type===_currentType;
            const matchSearch   = !q || (row.dataset.search||'').includes(q);
            row.style.display = (matchStatus && matchPriority && matchType && matchSearch) ? '' : 'none';
        });
        updateTableScroll();
    }
    function updateTableScroll() {
        const wrap = document.getElementById('tableWrap');
        if (!wrap) return;
        const visible = [...wrap.querySelectorAll('#ticketTable tbody tr')].filter(r => r.style.display !== 'none').length;
        if (visible > 9) { wrap.style.maxHeight = '540px'; wrap.style.overflowY = 'auto'; }
        else             { wrap.style.maxHeight = '';      wrap.style.overflowY = 'hidden'; }
    }
    updateTableScroll();
    // Restore saved filter
    (function() {
        try {
            const saved = JSON.parse(localStorage.getItem(_FKEY));
            if (!saved) return;
            if (saved.status && saved.status !== 'all') {
                const b = document.querySelector('#statusFilters [data-status="'+saved.status+'"]');
                if (b) filterStatus(saved.status, b);
            }
            if (saved.priority && saved.priority !== 'all') {
                const b = document.querySelector('#priorityFilters [data-priority="'+saved.priority+'"]');
                if (b) filterPriority(saved.priority, b);
            }
            if (saved.type && saved.type !== 'all') {
                const b = document.querySelector('#typeFilters [data-type="'+saved.type+'"]');
                if (b) filterType(saved.type, b);
            }
        } catch(e) {}
    })();
</script>
@endpush
