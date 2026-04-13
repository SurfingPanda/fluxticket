@extends('layouts.app')
@section('title','My Queue')
@section('topbar-title','My Queue')
@section('topbar-sub', $tickets->count() . ' ticket' . ($tickets->count() !== 1 ? 's' : '') . ' assigned to you')

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
    .btn-reject { background:rgba(248,113,113,.1); border:1px solid rgba(248,113,113,.25); border-radius:.45rem; padding:.25rem .7rem; font-size:.72rem; font-weight:600; color:#f87171; cursor:pointer; transition:all .15s; white-space:nowrap; }
    .btn-reject:hover { background:rgba(248,113,113,.2); border-color:rgba(248,113,113,.5); }
    .s-rejected { background:rgba(248,113,113,.12); color:#f87171; border:1px solid rgba(248,113,113,.25); }
    .btn-new { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.6rem; color:white; font-weight:600; font-size:.8rem; padding:.45rem 1rem; display:flex; align-items:center; gap:.4rem; transition:opacity .2s,transform .15s; box-shadow:0 3px 12px rgba(99,102,241,.35); cursor:pointer; text-decoration:none !important; }
    .btn-new:hover { opacity:.9; transform:translateY(-1px); color:white; }
    .priority-pill.selected[data-val="low"]    { border-color:#34d399 !important; box-shadow:0 0 0 3px rgba(52,211,153,.18); }
    .priority-pill.selected[data-val="medium"] { border-color:#fbbf24 !important; box-shadow:0 0 0 3px rgba(251,191,36,.18); }
    .priority-pill.selected[data-val="high"]   { border-color:#f87171 !important; box-shadow:0 0 0 3px rgba(248,113,113,.18); }
    .m-field { margin-bottom:1rem; } .m-textarea { resize:vertical; min-height:90px; }
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
    .btn-submit { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.6rem; color:white; font-size:.875rem; font-weight:600; padding:.5rem 1.4rem; cursor:pointer; box-shadow:0 3px 12px rgba(99,102,241,.35); display:flex; align-items:center; gap:.4rem; transition:opacity .2s,transform .15s; }
    .btn-submit:hover { opacity:.9; transform:translateY(-1px); }
    .resolution-box { background:rgba(52,211,153,.08); border:1px solid rgba(52,211,153,.2); border-radius:.75rem; padding:.85rem 1rem; font-size:.85rem; color:#34d399; line-height:1.6; white-space:pre-wrap; }
@endpush


@section('content')
@php
    $statusMap   = ['open'=>['s-open','Open'],'progress'=>['s-progress','In Progress'],'resolved'=>['s-resolved','Resolved'],'closed'=>['s-closed','Closed'],'rejected'=>['s-rejected','Rejected']];
    $priorityMap = ['high'=>'p-high','medium'=>'p-medium','low'=>'p-low'];
@endphp

<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="bi bi-clock-history me-2" style="color:var(--accent)"></i>My Assigned Tickets</span>
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

    @if($tickets->isEmpty())
    <div style="padding:4rem;text-align:center;color:var(--muted)">
        <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
        <div style="font-size:.95rem;font-weight:600">Your queue is empty</div>
        <div style="font-size:.82rem;margin-top:.35rem">No tickets are assigned to you right now.</div>
    </div>
    @else
    <div id="tableWrap" style="overflow-x:auto">
        <table class="flux-table" id="ticketTable">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Subject</th>
                    <th>Requester</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Created</th>
                    <th>Last Updated</th>
                    <th style="text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $t)
                <tr data-status="{{ $t->status }}" data-priority="{{ $t->priority }}" data-type="{{ $t->type ?? '' }}" data-search="{{ strtolower($t->ticket_number . ' ' . $t->subject . ' ' . ($t->requester ?? $t->user->name ?? '') . ' ' . $t->category . ' ' . ($t->type ?? '')) }}">
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
                    <td><span class="badge-priority {{ $priorityMap[$t->priority] ?? 'p-low' }}">{{ ucfirst($t->priority) }}</span></td>
                    <td><span class="badge-status {{ $statusMap[$t->status][0] ?? 's-open' }}">{{ $statusMap[$t->status][1] ?? 'Open' }}</span></td>
                    <td style="color:var(--muted);font-size:.82rem;white-space:nowrap">{{ $t->type ?: '—' }}</td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->created_at->diffForHumans() }}</td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->updated_at->diffForHumans() }}</td>
                    <td style="text-align:center">
                        <div style="display:flex;align-items:center;justify-content:center;gap:.4rem">
                            <button class="btn-view" onclick='openView(@json($t->toArray()), @json($t->requester ?? $t->user->name ?? "Unknown"))'>
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                            @if(!in_array($t->status, ['resolved','closed','rejected']))
                            <button class="btn-reject" onclick="openRejectModal({{ $t->id }}, '{{ addslashes($t->ticket_number) }}')">
                                <i class="bi bi-x-circle me-1"></i>Reject
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
{{-- View Modal (same as All Tickets) --}}
<div class="flux-modal-backdrop" id="viewModal" onclick="if(event.target===this)closeModal('viewModal')">
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
            <div id="vm-edit-section" style="display:none">
                <div class="modal-section">Update Ticket</div>
                <form id="editTicketForm" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem" class="m-field">
                        <div><label class="m-label">Status</label><select class="m-select" name="status" id="vm-status-sel"><option value="open">Open</option><option value="progress">In Progress</option><option value="resolved">Resolved</option><option value="closed">Closed</option></select></div>
                        <div><label class="m-label">Priority</label><select class="m-select" id="vm-priority-sel" onchange="document.getElementById('vm-priority-hidden').value=this.value"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option></select><input type="hidden" name="priority" id="vm-priority-hidden"></div>
                    </div>
                    <div class="m-field"><label class="m-label">Assigned To <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(read only)</span></label><input class="m-input" name="assignee" id="vm-assignee-inp" type="text" readonly style="opacity:.65;cursor:not-allowed;pointer-events:none"></div>
                    <div class="m-field" style="margin-bottom:0"><label class="m-label">Resolution Notes</label><textarea class="m-textarea" name="resolution" id="vm-resolution-inp" placeholder="Describe how the issue was resolved…"></textarea></div>
                </form>
            </div>
            {{-- ── Linked KBAs ── --}}
            <div class="modal-section" style="margin-top:1.25rem">Linked Knowledge Articles</div>
            <div id="vm-kba-list" style="margin-bottom:.6rem"></div>
            <div style="position:relative;margin-bottom:1rem">
                <div style="position:relative">
                    <i class="bi bi-search" style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.8rem;pointer-events:none"></i>
                    <input type="text" id="vm-kba-search" placeholder="Search KBAs to attach…"
                        style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;color:var(--text);font-size:.8rem;padding:.4rem .75rem .4rem 2rem;outline:none"
                        oninput="filterKbaDropdown(this.value)" onfocus="showKbaDropdown()">
                </div>
                <div id="vm-kba-dropdown" style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--surface);border:1px solid var(--border);border-radius:.65rem;box-shadow:0 8px 24px rgba(0,0,0,.3);max-height:200px;overflow-y:auto;z-index:9999"></div>
            </div>

            <div class="modal-section" style="margin-top:1.25rem">Activity &amp; Notes</div>
            <div id="vm-notes-timeline" style="margin-bottom:.85rem"></div>
            <div id="vm-add-note-section" style="display:none">
                <div id="vm-note-error" style="display:none;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;padding:.45rem .75rem;border-radius:.6rem;font-size:.78rem;margin-bottom:.5rem"></div>
                <textarea class="m-textarea" id="vm-note-input" placeholder="Add a note…" style="min-height:72px;margin-bottom:.5rem;width:100%"></textarea>
                <div style="margin-bottom:.5rem">
                    <label style="display:flex;align-items:center;gap:.5rem;background:var(--surface2);border:1px dashed var(--border);border-radius:.6rem;padding:.4rem .75rem;cursor:pointer;font-size:.78rem;color:var(--muted);transition:border-color .15s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                        <i class="bi bi-paperclip"></i>
                        <span id="vm-note-file-label">Attach a file (max 5 MB)</span>
                        <input type="file" id="vm-note-file" style="display:none" onchange="updateNoteFileLabel(this)">
                    </label>
                </div>
                <button type="button" id="vm-note-submit" onclick="submitNote()" class="btn-submit" style="font-size:.8rem;padding:.4rem .9rem">
                    <i class="bi bi-chat-left-text-fill"></i> Add Note
                </button>
            </div>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('viewModal')">Close</button>
            <a id="vm-print-btn" href="#" target="_blank" class="btn-ghost"><i class="bi bi-printer"></i> Print / PDF</a>
            <button id="vm-reject-btn" onclick="openRejectFromView()" style="display:none;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);border-radius:.6rem;color:#f87171;font-size:.875rem;font-weight:600;padding:.5rem 1rem;align-items:center;gap:.4rem;cursor:pointer"><i class="bi bi-x-circle"></i> Reject</button>
            <button id="vm-route-btn" onclick="openRouteFromView()" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);border-radius:.6rem;color:#fbbf24;font-size:.875rem;font-weight:600;padding:.5rem 1rem;display:flex;align-items:center;gap:.4rem;cursor:pointer"><i class="bi bi-arrow-left-right"></i> Route</button>
            <button id="vm-save-btn" class="btn-submit" style="display:none" onclick="document.getElementById('editTicketForm').submit()"><i class="bi bi-floppy"></i> Save Changes</button>
        </div>
    </div>
</div>

{{-- Reject Confirmation Modal --}}
<div class="flux-modal-backdrop" id="rejectModal" onclick="if(event.target===this)closeModal('rejectModal')">
    <div class="flux-modal" style="max-width:440px">
        <div class="flux-modal-header">
            <div>
                <div style="font-size:.95rem;font-weight:700;color:#f87171">Reject Ticket</div>
                <div id="reject-sub" style="font-size:.75rem;color:var(--muted)"></div>
            </div>
            <button onclick="closeModal('rejectModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="flux-modal-body">
            <div style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);border-radius:.75rem;padding:.75rem 1rem;font-size:.82rem;color:#f87171;margin-bottom:1rem;display:flex;align-items:flex-start;gap:.5rem">
                <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;margin-top:.1rem"></i>
                <span>This will permanently reject the ticket and stop the SLA timer. This action cannot be undone.</span>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="m-field" style="margin-bottom:0">
                    <label class="m-label">Reason for Rejection <span style="color:#f87171">*</span></label>
                    <textarea class="m-textarea" name="rejection_reason" id="reject-reason" placeholder="Explain why this ticket is being rejected…" required style="min-height:90px"></textarea>
                </div>
            </form>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('rejectModal')">Cancel</button>
            <button type="button" onclick="submitReject()" style="background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.3);border-radius:.6rem;color:#f87171;font-size:.875rem;font-weight:600;padding:.5rem 1.25rem;cursor:pointer;display:flex;align-items:center;gap:.4rem"><i class="bi bi-x-circle-fill"></i> Confirm Reject</button>
        </div>
    </div>
</div>

{{-- Route Modal --}}
<div class="flux-modal-backdrop" id="routeModal" onclick="if(event.target===this)closeModal('routeModal')">
    <div class="flux-modal" style="max-width:480px">
        <div class="flux-modal-header">
            <div><div style="font-size:.95rem;font-weight:700;color:var(--text)">Route Ticket</div><div id="route-sub" style="font-size:.75rem;color:var(--muted)"></div></div>
            <button onclick="closeModal('routeModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="flux-modal-body">
            <form id="routeForm" method="POST">
                @csrf
                <div class="m-field"><label class="m-label">Department <span style="color:#f87171">*</span></label>
                    <select class="m-select" id="route-dept" name="department" required onchange="loadRouteDeptUsers(this.value)">
                        <option value="" disabled selected>Select department…</option>
                        @foreach($allowedDepts ?? ['IT','HR','Finance','OPIC','Dispatch','Asset/Admin','Marketing','RSO','Store','Accounting','Security'] as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="m-field"><label class="m-label">Route To <span style="color:#f87171">*</span></label>
                    <select class="m-select" id="route-person" name="routed_to" required disabled><option value="" disabled selected>Select department first…</option></select>
                </div>
                <div class="m-field" style="margin-bottom:0"><label class="m-label">Routing Note</label><textarea class="m-textarea" name="routing_note" placeholder="Reason for routing…" style="min-height:80px"></textarea></div>
            </form>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('routeModal')">Cancel</button>
            <button type="button" id="routeSubmitBtn" onclick="submitRouteTicket()" style="background:rgba(251,191,36,.15);border:1px solid rgba(251,191,36,.3);border-radius:.6rem;color:#d97706;font-size:.875rem;font-weight:600;padding:.5rem 1.25rem;cursor:pointer;display:flex;align-items:center;gap:.4rem"><i class="bi bi-arrow-left-right"></i> Route Ticket</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const currentUser   = @json(auth()->user()->name);
    const currentUserId = {{ auth()->id() }};

    @if(!empty($openTicket))
    document.addEventListener('DOMContentLoaded', function () {
        const _ot = @json($openTicket->load(['user','notes.user','knowledgeArticles'])->toArray());
        openView(_ot, _ot.requester || (_ot.user?.name ?? 'Unknown'));
    });
    @endif
    window.deptUsers = @json($deptUsers->map(fn($u) => $u->pluck('name')));
    const _csrfToken = '{{ csrf_token() }}';
    let _currentTicketId = null;

    function submitRouteTicket() {
        const btn = document.getElementById('routeSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(217,119,6,.35);border-top-color:#d97706;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.35rem"></span> Routing…';
        document.getElementById('routeForm').submit();
    }
    let _currentTicketStatus = null;
    const _notesCache    = {};   // ticketId → notes[] (keeps notes after AJAX add)
    const _kbaCache      = {};   // ticketId → kba[]   (keeps KBAs after attach/detach)
    const _allKbas = @json($allKbas ?? []);

    function renderKbaList(kbas) {
        const el = document.getElementById('vm-kba-list');
        if (!kbas || kbas.length === 0) {
            el.innerHTML = '<div style="font-size:.78rem;color:var(--muted);padding:.35rem 0">No KBAs linked yet.</div>';
            return;
        }
        const locked = ['resolved', 'closed'].includes(_currentTicketStatus);
        el.innerHTML = kbas.map(k => {
            const removeBtn = locked
                ? `<span title="Cannot remove from a resolved/closed ticket" style="color:var(--border);font-size:.75rem;line-height:1;display:flex;align-items:center;cursor:not-allowed"><i class="bi bi-x-lg"></i></span>`
                : `<button onclick="detachKba(${k.id})" title="Remove KBA" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:0;font-size:.75rem;line-height:1;display:flex;align-items:center" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'"><i class="bi bi-x-lg"></i></button>`;
            return `
            <div style="display:inline-flex;align-items:center;gap:.4rem;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);border-radius:.5rem;padding:.25rem .6rem;margin:0 .35rem .35rem 0;font-size:.78rem;color:#818cf8">
                <i class="bi bi-journal-text"></i>
                <span style="font-weight:600">${escHtml(k.kba_number || '#KBA-' + String(k.id).padStart(4,'0'))}</span>
                <span style="color:var(--muted)">·</span>
                <span>${escHtml(k.title)}</span>
                ${removeBtn}
            </div>`;
        }).join('');
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function filterKbaDropdown(q) {
        const linked = (_kbaCache[_currentTicketId] || []).map(k => k.id);
        const term = q.trim().toLowerCase();
        const matches = _allKbas.filter(k =>
            !linked.includes(k.id) &&
            (!term || k.title.toLowerCase().includes(term) || k.kba_number.toLowerCase().includes(term) || (k.category || '').toLowerCase().includes(term))
        ).slice(0, 20);
        const dd = document.getElementById('vm-kba-dropdown');
        if (!matches.length) {
            dd.innerHTML = '<div style="padding:.5rem .85rem;font-size:.78rem;color:var(--muted)">No matching KBAs found.</div>';
        } else {
            dd.innerHTML = matches.map(k => `
                <div onclick="confirmAttachKba(${k.id})"
                     style="padding:.45rem .85rem;cursor:pointer;font-size:.8rem;transition:background .1s;display:flex;align-items:center;gap:.5rem"
                     onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background=''"
                >
                    <i class="bi bi-journal-text" style="color:#818cf8;flex-shrink:0"></i>
                    <span style="color:#818cf8;font-weight:600;flex-shrink:0">${escHtml(k.kba_number || '#KBA-' + String(k.id).padStart(4,'0'))}</span>
                    <span style="color:var(--text);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(k.title)}</span>
                    <span style="color:var(--muted);font-size:.72rem;flex-shrink:0">${escHtml(k.category || '')}</span>
                </div>`).join('');
        }
        dd.style.display = '';
    }

    function showKbaDropdown() {
        filterKbaDropdown(document.getElementById('vm-kba-search').value);
    }

    function hideKbaDropdown() {
        const dd = document.getElementById('vm-kba-dropdown');
        if (dd) dd.style.display = 'none';
    }

    // Close KBA dropdown when clicking outside the search area
    document.addEventListener('click', function(e) {
        const search = document.getElementById('vm-kba-search');
        const dd     = document.getElementById('vm-kba-dropdown');
        if (!dd || dd.style.display === 'none') return;
        if (search && (search.contains(e.target) || dd.contains(e.target))) return;
        dd.style.display = 'none';
    });

    let _pendingKbaId = null;

    function confirmAttachKba(articleId) {
        hideKbaDropdown();
        const kba = _allKbas.find(k => k.id === articleId);
        _pendingKbaId = articleId;
        const num   = kba ? (kba.kba_number || '#KBA-' + String(kba.id).padStart(4,'0')) : '';
        const title = kba ? kba.title : '';
        document.getElementById('kba-confirm-num').textContent   = num;
        document.getElementById('kba-confirm-title').textContent = title;
        const overlay = document.getElementById('kbaConfirmBackdrop');
        const box     = document.getElementById('kbaAttachBox');
        overlay.style.display = 'flex';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            box.style.transform = 'scale(1) translateY(0)';
            box.style.opacity   = '1';
        }));
    }

    function closeKbaAttachModal() {
        const overlay = document.getElementById('kbaConfirmBackdrop');
        const box     = document.getElementById('kbaAttachBox');
        box.style.transform = 'scale(.94) translateY(8px)';
        box.style.opacity   = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
            const btn     = document.getElementById('kbaAttachOk');
            const icon    = document.getElementById('kbaAttachOkIcon');
            const spinner = document.getElementById('kbaAttachOkSpinner');
            const label   = document.getElementById('kbaAttachOkLabel');
            if (btn) { btn.disabled = false; icon.style.display = ''; spinner.style.display = 'none'; label.textContent = 'Yes, Attach KBA'; }
        }, 200);
        _pendingKbaId = null;
        document.getElementById('vm-kba-search').value = '';
    }

    function doAttachKba() {
        if (!_pendingKbaId) return;
        const articleId = _pendingKbaId;

        const btn     = document.getElementById('kbaAttachOk');
        const icon    = document.getElementById('kbaAttachOkIcon');
        const spinner = document.getElementById('kbaAttachOkSpinner');
        const label   = document.getElementById('kbaAttachOkLabel');
        if (btn) {
            btn.disabled          = true;
            icon.style.display    = 'none';
            spinner.style.display = 'inline-block';
            label.textContent     = 'Attaching…';
        }

        fetch(`/tickets/${_currentTicketId}/kba/${articleId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(r => r.json()).then(data => {
            closeKbaAttachModal();
            if (data.ok) {
                if (!_kbaCache[_currentTicketId]) _kbaCache[_currentTicketId] = [];
                _kbaCache[_currentTicketId].push(data.article);
                renderKbaList(_kbaCache[_currentTicketId]);
            }
        }).catch(() => { closeKbaAttachModal(); });
    }

    function detachKba(articleId) {
        const kba = (_kbaCache[_currentTicketId] || []).find(k => k.id === articleId);
        const label = kba ? (kba.kba_number || '#KBA-' + String(kba.id).padStart(4,'0')) + ' — ' + kba.title : 'this KBA';
        showKbaConfirm(label, function() {
            fetch(`/tickets/${_currentTicketId}/kba/${articleId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            }).then(r => r.json()).then(data => {
                if (data.ok) {
                    _kbaCache[_currentTicketId] = (_kbaCache[_currentTicketId] || []).filter(k => k.id !== articleId);
                    renderKbaList(_kbaCache[_currentTicketId]);
                }
            }).catch(() => {});
        });
    }

    function renderNoteHtml(n) {
        const isRoute  = n.type === 'route_event';
        const initial  = (n.user?.name || '?').charAt(0).toUpperCase();
        const dateStr  = new Date(n.created_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'});
        const rawBody  = (n.content||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')
            .replace(/^&gt; (.+)$/gm,'<div style="border-left:3px solid rgba(99,102,241,.3);padding-left:.6rem;color:var(--muted);margin-top:.3rem;font-size:.8rem">$1</div>');
        const avatarBg = isRoute ? 'linear-gradient(135deg,#2563eb,#4f46e5)' : 'linear-gradient(135deg,#4f46e5,#7c3aed)';
        const chip     = isRoute
            ? `<span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(59,130,246,.15);color:#60a5fa;padding:.1rem .45rem;border-radius:9999px"><i class="bi bi-arrow-left-right"></i> Routed</span>`
            : `<span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(99,102,241,.15);color:#818cf8;padding:.1rem .45rem;border-radius:9999px"><i class="bi bi-chat-left-text"></i> Note</span>`;
        const attachHtml = n.attachment ? `<div style="margin-top:.4rem"><a href="/storage/${n.attachment}" target="_blank" style="font-size:.75rem;color:#818cf8;display:inline-flex;align-items:center;gap:.3rem"><i class="bi bi-paperclip"></i> View attachment</a></div>` : '';
        return `<div style="display:flex;gap:.7rem;margin-bottom:.85rem"><div style="width:28px;height:28px;min-width:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:white;background:${avatarBg}">${initial}</div><div style="flex:1;min-width:0"><div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem"><span style="font-size:.8rem;font-weight:600;color:var(--text)">${n.user?.name||'Unknown'}</span>${chip}<span style="font-size:.7rem;color:var(--muted)">${dateStr}</span></div><div style="font-size:.83rem;color:var(--text);line-height:1.55;background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;padding:.55rem .8rem">${rawBody}${attachHtml}</div></div></div>`;
    }
    function renderTimeline(notes) {
        const tl = document.getElementById('vm-notes-timeline');
        tl.innerHTML = notes.length ? notes.map(renderNoteHtml).join('') : '<div style="text-align:center;color:var(--muted);font-size:.82rem;padding:.75rem 0;font-style:italic">No notes yet.</div>';
    }
    function updateNoteFileLabel(input) {
        const lbl = document.getElementById('vm-note-file-label');
        if (input.files.length) {
            if (input.files[0].size > 5*1024*1024) { input.value=''; lbl.textContent='Attach a file (max 5 MB)'; const e=document.getElementById('vm-note-error'); e.textContent='File exceeds 5 MB limit.'; e.style.display=''; return; }
            lbl.textContent = '📎 ' + input.files[0].name;
        } else { lbl.textContent = 'Attach a file (max 5 MB)'; }
    }
    async function submitNote() {
        const content = document.getElementById('vm-note-input').value.trim();
        const fileInput = document.getElementById('vm-note-file');
        const errEl = document.getElementById('vm-note-error');
        errEl.style.display = 'none';
        if (!content) { errEl.textContent='Note cannot be empty.'; errEl.style.display=''; return; }
        const fd = new FormData();
        fd.append('_token', _csrfToken); fd.append('content', content);
        if (fileInput.files.length) fd.append('attachment', fileInput.files[0]);
        const btn = document.getElementById('vm-note-submit');
        btn.disabled=true; btn.innerHTML='<span style="display:inline-block;width:12px;height:12px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.3rem"></span> Adding…';
        try {
            const r = await fetch('/tickets/'+_currentTicketId+'/notes',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':_csrfToken},body:fd});
            const data = await r.json();
            if (!r.ok||!data.ok) throw new Error(data.message||'Server error');
            // Update cache so note persists on reopen
            if (!_notesCache[_currentTicketId]) _notesCache[_currentTicketId] = [];
            _notesCache[_currentTicketId].push(data.note);

            const tl = document.getElementById('vm-notes-timeline');
            const empty = tl.querySelector('[style*="font-style:italic"]');
            if (empty) tl.innerHTML='';
            tl.insertAdjacentHTML('beforeend', renderNoteHtml(data.note));
            document.getElementById('vm-note-input').value='';
            fileInput.value='';
            document.getElementById('vm-note-file-label').textContent='Attach a file (max 5 MB)';
        } catch(e) {
            errEl.textContent = e.message||'Failed to add note.'; errEl.style.display='';
        } finally {
            btn.disabled=false; btn.innerHTML='<i class="bi bi-chat-left-text-fill"></i> Add Note';
        }
    }

    document.addEventListener('keydown', e => { if(e.key==='Escape') ['viewModal','routeModal'].forEach(closeModal); });

    // ── Filter (saveable) ──
    const _FKEY = 'flux_filter_queue';
    let _currentStatus   = 'all';
    let _currentPriority = 'all';
    let _currentType     = 'all';

    function _saveFilter() {
        try { localStorage.setItem(_FKEY, JSON.stringify({ status: _currentStatus, priority: _currentPriority, type: _currentType })); } catch(e){}
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
        if (visible > 9) {
            wrap.style.maxHeight = '540px';
            wrap.style.overflowY = 'auto';
        } else {
            wrap.style.maxHeight = '';
            wrap.style.overflowY = 'hidden';
        }
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

    // SLA
    const slaLabels = { ok:'On Track', warning:'At Risk', breached:'Breached', met:'Met' };
    const slaColors = { ok:'#34d399', warning:'#fbbf24', breached:'#f87171', met:'#818cf8' };

    function openView(t, requesterName) {
        document.getElementById('vm-number').textContent  = t.ticket_number;
        document.getElementById('vm-subject').textContent = t.subject;
        const sMap = { open:'s-open', progress:'s-progress', resolved:'s-resolved', closed:'s-closed' };
        const sLabel = { open:'Open', progress:'In Progress', resolved:'Resolved', closed:'Closed' };
        const sb = document.getElementById('vm-status-badge');
        sb.className = 'badge-status ' + (sMap[t.status]||'s-open');
        sb.textContent = sLabel[t.status]||t.status;
        document.getElementById('vm-requester').textContent  = requesterName;
        document.getElementById('vm-category').textContent   = t.category || '—';
        const pMap = { high:'p-high', medium:'p-medium', low:'p-low' };
        document.getElementById('vm-priority').innerHTML = `<span class="badge-priority ${pMap[t.priority]||'p-low'}">${(t.priority||'').charAt(0).toUpperCase()+(t.priority||'').slice(1)}</span>`;
        document.getElementById('vm-created').textContent    = t.created_at ? new Date(t.created_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'}) : '—';
        document.getElementById('vm-assignee').textContent   = t.assignee || 'Unassigned';
        document.getElementById('vm-resolved-by').textContent= t.resolved_by || '—';
        document.getElementById('vm-description').textContent= t.description || '';
        const resSection = document.getElementById('vm-resolution-section');
        if (t.resolution) { resSection.style.display=''; document.getElementById('vm-resolution').textContent=t.resolution; } else { resSection.style.display='none'; }
        const isAssignee = t.assignee && t.assignee === currentUser;
        const isDone     = ['resolved', 'closed'].includes(t.status);
        const editSection = document.getElementById('vm-edit-section');
        const saveBtn = document.getElementById('vm-save-btn');
        const routeBtn = document.getElementById('vm-route-btn');
        editSection.style.display = isAssignee ? '' : 'none';
        saveBtn.style.display     = isAssignee ? '' : 'none';
        routeBtn.style.display    = (isAssignee && !isDone) ? '' : 'none';
        if (isAssignee) {
            document.getElementById('editTicketForm').action = '/tickets/' + t.id;
            document.getElementById('vm-status-sel').value    = t.status;
            document.getElementById('vm-assignee-inp').value  = t.assignee || '';
            document.getElementById('vm-resolution-inp').value = t.resolution || '';
            const prioritySel    = document.getElementById('vm-priority-sel');
            const priorityHidden = document.getElementById('vm-priority-hidden');
            prioritySel.value    = t.priority;
            priorityHidden.value = t.priority;
            // Lock priority when resolved/closed
            prioritySel.disabled = isDone;
            prioritySel.style.opacity = isDone ? '.55' : '';
            prioritySel.style.cursor  = isDone ? 'not-allowed' : '';
        }
        const slaDue=document.getElementById('vm-sla-due'), slaBadge=document.getElementById('vm-sla-badge'),
              slaBarWrap=document.getElementById('vm-sla-bar-wrap'), slaBar=document.getElementById('vm-sla-bar'),
              slaPctLabel=document.getElementById('vm-sla-pct-label'), slaTimeLabel=document.getElementById('vm-sla-time-label');
        if (t.sla_due_at) {
            const due=new Date(t.sla_due_at), created=new Date(t.created_at), now=new Date();
            const isDone=['resolved','closed'].includes(t.status);
            const compareAt=isDone&&t.resolved_at?new Date(t.resolved_at):now;
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
        document.getElementById('vm-route-btn').dataset.ticketId=t.id;
        document.getElementById('vm-route-btn').dataset.ticketNum=t.ticket_number;
        // Notes timeline — use cache so AJAX-added notes survive modal reopen
        _currentTicketId     = t.id;
        _currentTicketStatus = t.status;
        if (_notesCache[t.id] === undefined) {
            _notesCache[t.id] = [...(t.notes || [])];
        }
        renderTimeline(_notesCache[t.id]);

        // Linked KBAs
        if (_kbaCache[t.id] === undefined) {
            _kbaCache[t.id] = [...(t.knowledge_articles || [])];
        }
        renderKbaList(_kbaCache[t.id]);
        document.getElementById('vm-kba-search').value = '';
        hideKbaDropdown();
        const isSubmitter=t.user_id===currentUserId;
        document.getElementById('vm-add-note-section').style.display=(isSubmitter||isAssignee)?'':'none';
        document.getElementById('vm-note-input').value='';
        document.getElementById('vm-note-file').value='';
        document.getElementById('vm-note-file-label').textContent='Attach a file (max 5 MB)';
        document.getElementById('vm-note-error').style.display='none';
        const _vmBody = document.querySelector('#viewModal .flux-modal-body');
        if (_vmBody) { _vmBody.style.opacity='0'; _vmBody.style.transition=''; }
        openModal('viewModal');
        requestAnimationFrame(() => requestAnimationFrame(() => {
            if (_vmBody) { _vmBody.style.transition='opacity .3s ease'; _vmBody.style.opacity='1'; }
        }));
    }

    function openRouteFromView() {
        const btn=document.getElementById('vm-route-btn');
        document.getElementById('route-sub').textContent=btn.dataset.ticketNum+' — Forward to another department';
        document.getElementById('routeForm').action='/tickets/'+btn.dataset.ticketId+'/route';
        document.getElementById('route-dept').value='';
        const ps=document.getElementById('route-person'); ps.innerHTML='<option value="" disabled selected>Select department first…</option>'; ps.disabled=true;
        closeModal('viewModal'); openModal('routeModal');
    }

    function loadRouteDeptUsers(dept) {
        const sel=document.getElementById('route-person');
        const du = window.deptUsers || {};
        sel.innerHTML='<option value="" disabled selected>Select person…</option>';
        (du[dept]||[]).forEach(name=>{ const o=document.createElement('option'); o.value=name; o.textContent=name; sel.appendChild(o); });
        sel.disabled=!(du[dept]||[]).length;
    }


    // ── KBA detach confirmation modal ──
    (function() {
        const overlayHtml = `
        <div id="kbaConfirmModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);align-items:center;justify-content:center">
            <div id="kbaConfirmBox" style="background:var(--surface,#1e1e2e);border:1px solid var(--border,rgba(255,255,255,.08));border-radius:1rem;padding:1.75rem 1.75rem 1.4rem;max-width:420px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,.5);transform:scale(.94) translateY(8px);opacity:0;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .18s ease">
                <div style="display:flex;align-items:flex-start;gap:.9rem;margin-bottom:1.1rem">
                    <div style="width:38px;height:38px;min-width:38px;border-radius:.65rem;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.25);display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-journal-x" style="color:#f87171;font-size:1rem"></i>
                    </div>
                    <div>
                        <div style="font-size:.95rem;font-weight:700;color:var(--text,#e2e8f0);margin-bottom:.3rem">Remove KBA Link</div>
                        <div id="kbaConfirmMsg" style="font-size:.83rem;color:var(--muted,#94a3b8);line-height:1.55"></div>
                    </div>
                </div>
                <div style="background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.18);border-radius:.55rem;padding:.55rem .75rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1.3rem">
                    <i class="bi bi-info-circle" style="color:#fbbf24;font-size:.8rem;flex-shrink:0"></i>
                    <span style="font-size:.78rem;color:#fbbf24">You can re-attach this KBA later if needed.</span>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:.6rem">
                    <button id="kbaConfirmCancel" style="padding:.45rem 1.1rem;border-radius:.55rem;border:1px solid var(--border,rgba(255,255,255,.1));background:var(--surface2,rgba(255,255,255,.05));color:var(--text,#e2e8f0);font-size:.83rem;font-weight:600;cursor:pointer;transition:background .15s">Cancel</button>
                    <button id="kbaConfirmOk" style="padding:.45rem 1.2rem;border-radius:.55rem;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:.83rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:opacity .15s">
                        <i class="bi bi-trash3"></i> Remove
                    </button>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', overlayHtml);
        document.body.insertAdjacentHTML('beforeend', `
        <div id="kbaConfirmBackdrop" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);align-items:center;justify-content:center">
            <div id="kbaAttachBox" style="background:var(--surface,#1e1e2e);border:1px solid var(--border,rgba(255,255,255,.08));border-radius:1rem;padding:1.75rem 1.75rem 1.4rem;max-width:420px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,.5);transform:scale(.94) translateY(8px);opacity:0;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .18s ease">
                <div style="display:flex;align-items:flex-start;gap:.9rem;margin-bottom:1.1rem">
                    <div style="width:38px;height:38px;min-width:38px;border-radius:.65rem;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-journal-plus" style="color:#818cf8;font-size:1rem"></i>
                    </div>
                    <div style="min-width:0;flex:1">
                        <div style="font-size:.95rem;font-weight:700;color:var(--text,#e2e8f0);margin-bottom:.3rem">Attach Knowledge Article?</div>
                        <div style="font-size:.83rem;color:var(--muted,#94a3b8);line-height:1.55">
                            You are about to link <span id="kba-confirm-num" style="color:#818cf8;font-weight:700"></span>
                            <span id="kba-confirm-title" style="color:var(--text,#e2e8f0)"></span> to this ticket.
                        </div>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:.6rem">
                    <button id="kbaAttachCancel" style="padding:.45rem 1.1rem;border-radius:.55rem;border:1px solid var(--border,rgba(255,255,255,.1));background:var(--surface2,rgba(255,255,255,.05));color:var(--text,#e2e8f0);font-size:.83rem;font-weight:600;cursor:pointer;transition:background .15s">Cancel</button>
                    <button id="kbaAttachOk" onclick="doAttachKba()" style="padding:.45rem 1.2rem;border-radius:.55rem;border:none;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;font-size:.83rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;box-shadow:0 3px 12px rgba(99,102,241,.35);transition:opacity .15s">
                        <i class="bi bi-journal-plus" id="kbaAttachOkIcon"></i>
                        <span style="display:none;width:13px;height:13px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle" id="kbaAttachOkSpinner"></span>
                        <span id="kbaAttachOkLabel">Yes, Attach KBA</span>
                    </button>
                </div>
            </div>
        </div>`);

        let _kbaConfirmCb = null;
        const overlay   = document.getElementById('kbaConfirmModal');
        const box       = document.getElementById('kbaConfirmBox');
        const msg       = document.getElementById('kbaConfirmMsg');
        const okBtn     = document.getElementById('kbaConfirmOk');
        const cancelBtn = document.getElementById('kbaConfirmCancel');

        function openKbaConfirm() {
            overlay.style.display = 'flex';
            requestAnimationFrame(() => requestAnimationFrame(() => {
                box.style.transform = 'scale(1) translateY(0)';
                box.style.opacity   = '1';
            }));
        }

        function resetOkBtn() {
            okBtn.disabled = false;
            okBtn.innerHTML = '<i class="bi bi-trash3"></i> Remove';
            okBtn.style.opacity = '1';
        }
        function closeKbaConfirm() {
            box.style.transform = 'scale(.94) translateY(8px)';
            box.style.opacity   = '0';
            setTimeout(() => { overlay.style.display = 'none'; resetOkBtn(); }, 200);
            _kbaConfirmCb = null;
        }
        window.showKbaConfirm = function(label, onConfirm) {
            msg.innerHTML = `Remove <strong style="color:var(--text,#e2e8f0)">"${label}"</strong> from this ticket?`;
            _kbaConfirmCb = onConfirm;
            resetOkBtn();
            openKbaConfirm();
        };
        okBtn.addEventListener('click', function() {
            const cb = _kbaConfirmCb;
            okBtn.disabled = true;
            okBtn.innerHTML = '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.3rem"></span>Removing…';
            closeKbaConfirm();
            if (typeof cb === 'function') cb();
        });
        cancelBtn.addEventListener('click', closeKbaConfirm);
        overlay.addEventListener('click', function(e) { if (e.target === overlay) closeKbaConfirm(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && overlay.style.display === 'flex') closeKbaConfirm(); });
        okBtn.addEventListener('mouseover',  () => { if (!okBtn.disabled) okBtn.style.opacity = '.85'; });
        okBtn.addEventListener('mouseout',   () => { if (!okBtn.disabled) okBtn.style.opacity = '1'; });
        cancelBtn.addEventListener('mouseover', () => cancelBtn.style.background = 'var(--surface,rgba(255,255,255,.08))');
        cancelBtn.addEventListener('mouseout',  () => cancelBtn.style.background = 'var(--surface2,rgba(255,255,255,.05))');
    })();

</script>
@endpush

@push('scripts')
<script>
// Wire up KBA attach confirm modal cancel/backdrop/escape
(function() {
    const overlay   = document.getElementById('kbaConfirmBackdrop');
    const cancelBtn = document.getElementById('kbaAttachCancel');
    if (!overlay || !cancelBtn) return;
    cancelBtn.addEventListener('click', closeKbaAttachModal);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) closeKbaAttachModal(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.style.display === 'flex') closeKbaAttachModal();
    });
})();
</script>
@endpush
