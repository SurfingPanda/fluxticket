@php
$typeLabels = [
    'incident'        => 'Incident Tickets',
    'service_request' => 'Service Requests',
    'question'        => 'Question',
    'change_request'  => 'Change Request',
];
$pageTitle = isset($type) && $type ? ($typeLabels[$type] ?? 'All Tickets') : 'All Tickets';
@endphp
@extends('layouts.app')

@section('title'){{ $pageTitle }}@endsection
@section('topbar-title'){{ $pageTitle }}@endsection
@section('topbar-sub'){{ $tickets->count() }} ticket{{ $tickets->count() !== 1 ? 's' : '' }} total@endsection

@push('styles')
    /* Ticket-list–specific */
    .btn-view { background:var(--surface2); border:1px solid var(--border); border-radius:.45rem; padding:.25rem .7rem; font-size:.72rem; font-weight:600; color:var(--muted); cursor:pointer; transition:all .15s; white-space:nowrap; }
    .btn-view:hover { background:rgba(99,102,241,.15); border-color:rgba(99,102,241,.4); color:#818cf8; }
    .btn-assign { background:rgba(52,211,153,.12); border:1px solid rgba(52,211,153,.3); border-radius:.45rem; padding:.25rem .7rem; font-size:.72rem; font-weight:600; color:#34d399; cursor:pointer; transition:all .15s; white-space:nowrap; }
    .btn-assign:hover { background:rgba(52,211,153,.2); }
    .btn-accept { background:rgba(52,211,153,.15); border:1px solid rgba(52,211,153,.35); border-radius:.6rem; color:#34d399; font-size:.875rem; font-weight:600; padding:.5rem 1.1rem; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:.4rem; }
    .btn-accept:hover { background:rgba(52,211,153,.25); }

    .sla-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .6rem; border-radius:9999px; font-size:.68rem; font-weight:700; white-space:nowrap; }
    .sla-ok      { background:rgba(52,211,153,.12); color:#34d399; }
    .sla-warning { background:rgba(251,191,36,.12);  color:#fbbf24; }
    .sla-breached{ background:rgba(248,113,113,.12); color:#f87171; }
    .sla-met     { background:rgba(99,102,241,.12);  color:#818cf8; }
    .sla-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
    .sla-ok .sla-dot { background:#34d399; } .sla-warning .sla-dot { background:#fbbf24; }
    .sla-breached .sla-dot { background:#f87171; } .sla-met .sla-dot { background:#818cf8; }

    .assign-modal { max-width:420px; }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem 1.25rem; margin-bottom:1.25rem; }
    .info-item .info-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:.2rem; }
    .info-item .info-val { font-size:.875rem; color:var(--text); font-weight:500; }
    .desc-box { background:var(--surface2); border:1px solid var(--border); border-radius:.75rem; padding:.85rem 1rem; font-size:.85rem; color:var(--text); line-height:1.6; white-space:pre-wrap; margin-bottom:1rem; }
    .resolution-box { background:rgba(52,211,153,.08); border:1px solid rgba(52,211,153,.2); border-radius:.75rem; padding:.85rem 1rem; font-size:.85rem; color:#34d399; line-height:1.6; white-space:pre-wrap; }
    .modal-section { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); margin:.25rem 0 .75rem; display:flex; align-items:center; gap:.5rem; }
    .modal-section::after { content:''; flex:1; height:1px; background:var(--border); }
@endpush

@section('content')

<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="bi bi-ticket-perforated me-2" style="color:var(--accent)"></i>{{ $pageTitle }}</span>
        <div style="display:flex;flex-direction:column;gap:.45rem;align-items:flex-end">
            <div class="d-flex gap-2 flex-wrap" id="statusFilters">
                <button class="filter-btn active" data-status="all"      onclick="filterStatus('all',this)">All</button>
                <button class="filter-btn"        data-status="open"     onclick="filterStatus('open',this)">Open</button>
                <button class="filter-btn"        data-status="progress" onclick="filterStatus('progress',this)">In Progress</button>
                <button class="filter-btn"        data-status="resolved" onclick="filterStatus('resolved',this)">Resolved</button>
                <button class="filter-btn"        data-status="closed"   onclick="filterStatus('closed',this)">Closed</button>
            </div>
            <div class="d-flex gap-2 flex-wrap" id="priorityFilters">
                <span style="font-size:.68rem;color:var(--muted);align-self:center;margin-right:.15rem">Priority:</span>
                <button class="filter-btn active" data-priority="all"    onclick="filterPriority('all',this)">All</button>
                <button class="filter-btn"        data-priority="high"   onclick="filterPriority('high',this)" style="color:#f87171">High</button>
                <button class="filter-btn"        data-priority="medium" onclick="filterPriority('medium',this)" style="color:#fbbf24">Medium</button>
                <button class="filter-btn"        data-priority="low"    onclick="filterPriority('low',this)" style="color:#34d399">Low</button>
            </div>
        </div>
    </div>

    @php
        $statusMap   = ['open'=>['s-open','Open'],'progress'=>['s-progress','In Progress'],'resolved'=>['s-resolved','Resolved'],'closed'=>['s-closed','Closed']];
        $priorityMap = ['high'=>'p-high','medium'=>'p-medium','low'=>'p-low'];
    @endphp

    @if($tickets->isEmpty())
        <div style="padding:4rem;text-align:center;color:var(--muted)">
            <i class="bi bi-ticket-perforated" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
            <div style="font-size:.95rem;font-weight:600">No tickets found</div>
            <div style="font-size:.82rem;margin-top:.35rem">Submit a new ticket from the dashboard.</div>
        </div>
    @else
    <div id="tableWrap" style="overflow-x:auto;border-radius:0 0 1rem 1rem">
        <table class="flux-table" id="ticketTable">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Subject</th>
                    <th>Requester</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th>Last Updated</th>
                    <th style="text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $t)
                <tr data-status="{{ $t->status }}" data-priority="{{ $t->priority }}" data-ticket-id="{{ $t->id }}">
                    <td><span style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace">{{ $t->ticket_number }}</span></td>
                    <td style="max-width:220px">
                        <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->subject }}</div>
                        @if($t->description)
                        <div style="font-size:.72rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">{{ $t->description }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:25px;height:25px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:white;flex-shrink:0">
                                {{ strtoupper(substr($t->requester ?? $t->user->name ?? '?', 0, 1)) }}
                            </div>
                            <span style="white-space:nowrap">{{ $t->requester ?? $t->user->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td><span class="badge-priority {{ $priorityMap[$t->priority] ?? 'p-low' }}">{{ ucfirst($t->priority) }}</span></td>
                    <td><span class="badge-status {{ $statusMap[$t->status][0] ?? 's-open' }}">{{ $statusMap[$t->status][1] ?? 'Open' }}</span></td>
                    <td style="color:var(--muted);font-size:.82rem;white-space:nowrap">{{ $t->type ?: '—' }}</td>
                    <td style="color:var(--muted);white-space:nowrap;font-size:.82rem">{{ $t->assignee ?: 'Unassigned' }}</td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->created_at->diffForHumans() }}</td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->updated_at->diffForHumans() }}</td>
                    <td style="text-align:center;white-space:nowrap">
                        @php
                            $authUser        = auth()->user();
                            $isSubmitter     = $t->user_id === $authUser->id;
                            $isAssignee      = $t->assignee === $authUser->name;
                            $isRoutedToDept  = $t->department && $t->department === $authUser->department;
                            $alreadyAssigned = !empty($t->assignee);
                            $showAccept      = !$isSubmitter && !$isAssignee && $isRoutedToDept && !$alreadyAssigned;
                        @endphp
                        <div class="d-flex gap-2 justify-content-center">
                            <button class="btn-view" onclick='openView(@json($t->toArray()))'>
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                            @if($showAccept)
                            <button class="btn-assign" onclick="openAssign({{ $t->id }}, '{{ addslashes($t->ticket_number) }}')">
                                <i class="bi bi-person-check me-1"></i>Accept
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

{{-- ════════════════════════════════════
     VIEW / EDIT TICKET MODAL
════════════════════════════════════ --}}
<div class="flux-modal-backdrop" id="viewModal" onclick="handleBdClick(event,'viewModal')">
    <div class="flux-modal">
        <div class="flux-modal-header">
            <div>
                <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.2rem">
                    <div style="width:28px;height:28px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:.45rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-ticket-perforated-fill text-white" style="font-size:.75rem"></i>
                    </div>
                    <span id="vm-number" style="font-size:.85rem;font-weight:800;color:#818cf8;font-family:monospace"></span>
                    <span id="vm-status-badge" class="badge-status"></span>
                </div>
                <div id="vm-subject" style="font-size:1rem;font-weight:700;color:var(--text)"></div>
            </div>
            <button onclick="closeModal('viewModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem;flex-shrink:0">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="flux-modal-body">

            <div class="info-grid">
                <div class="info-item"><div class="info-label">Requester</div><div class="info-val" id="vm-requester"></div></div>
                <div class="info-item"><div class="info-label">Category</div><div class="info-val" id="vm-category"></div></div>
                <div class="info-item"><div class="info-label">Type</div><div class="info-val" id="vm-type"></div></div>
                <div class="info-item"><div class="info-label">Created</div><div class="info-val" id="vm-created"></div></div>
                <div class="info-item"><div class="info-label">Assigned To</div><div class="info-val" id="vm-assignee"></div></div>
                <div class="info-item"><div class="info-label">Resolved By</div><div class="info-val" id="vm-resolved-by"></div></div>
                <div class="info-item"><div class="info-label">Last Updated</div><div class="info-val" id="vm-updated-at"></div></div>
                <div class="info-item">
                    <div class="info-label">SLA Deadline</div>
                    <div class="info-val" id="vm-sla-due"></div>
                </div>
                <div class="info-item">
                    <div class="info-label">SLA Status</div>
                    <div id="vm-sla-badge"></div>
                </div>
            </div>

            <div id="vm-sla-bar-wrap" style="margin-bottom:1rem;display:none">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-bottom:.3rem">
                    <span>SLA Progress</span>
                    <span id="vm-sla-pct-label"></span>
                </div>
                <div style="height:6px;background:var(--surface2);border-radius:9999px;overflow:hidden">
                    <div id="vm-sla-bar" style="height:100%;border-radius:9999px;transition:width .4s ease"></div>
                </div>
                <div style="font-size:.7rem;color:var(--muted);margin-top:.25rem" id="vm-sla-time-label"></div>
            </div>

            <div class="modal-section">Description</div>
            <div class="desc-box" id="vm-description"></div>

            <div id="vm-resolution-section" style="display:none">
                <div class="modal-section">Resolution</div>
                <div class="resolution-box" id="vm-resolution"></div>
            </div>

            <div id="vm-edit-section" style="display:none">
                <div class="modal-section">Update Ticket</div>
                <form id="editTicketForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="m-row m-field">
                        <div>
                            <label class="m-label">Status</label>
                            <select class="m-select" name="status" id="vm-status-sel">
                                <option value="open">Open</option>
                                <option value="progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Priority</label>
                            <select class="m-select" name="priority" id="vm-priority-sel">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="m-field">
                        <label class="m-label">Assigned To <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(read only)</span></label>
                        <input class="m-input" name="assignee" id="vm-assignee-inp" type="text" placeholder="Unassigned" readonly
                               style="opacity:.65;cursor:not-allowed;pointer-events:none">
                    </div>

                    <div class="m-field">
                        <label class="m-label">Resolution Notes <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(required when resolving/closing)</span></label>
                        <textarea class="m-textarea" name="resolution" id="vm-resolution-inp" placeholder="Describe how the issue was resolved…"></textarea>
                    </div>

                    <div class="m-field" style="margin-bottom:0">
                        <label class="m-label">Resolution Image <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(optional)</span></label>
                        <div style="border:1.5px dashed var(--border);border-radius:.65rem;padding:.85rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s"
                             onclick="document.getElementById('res-img-input').click()"
                             onmouseover="this.style.borderColor='var(--accent)'"
                             onmouseout="this.style.borderColor='var(--border)'">
                            <i class="bi bi-image" style="font-size:1.2rem;color:var(--muted)"></i>
                            <div style="font-size:.78rem;color:var(--muted);margin-top:.25rem">Click to upload an image</div>
                            <div id="res-img-display" style="font-size:.75rem;color:#818cf8;margin-top:.25rem;display:none"></div>
                        </div>
                        <input type="file" id="res-img-input" name="resolution_image" accept="image/*" style="display:none"
                               onchange="showResImg(this)">
                        <div id="res-img-preview-wrap" style="display:none;margin-top:.5rem">
                            <a id="res-img-preview" href="#" download target="_blank"
                               style="display:inline-flex;align-items:center;gap:.4rem;font-size:.8rem;color:#818cf8;text-decoration:none;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:.5rem;padding:.35rem .75rem">
                                <i class="bi bi-paperclip"></i>
                                <span id="res-img-filename">resolution-image</span>
                            </a>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:.25rem">
                                Uploaded <span id="res-img-uploaded-at"></span> &middot; upload a new one to replace
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div id="vm-readonly-notice" style="display:none;margin-top:.5rem">
                <div style="display:flex;align-items:center;gap:.65rem;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:.75rem;padding:.75rem 1rem;font-size:.82rem;color:#818cf8">
                    <i class="bi bi-lock-fill" style="flex-shrink:0"></i>
                    <span>You are viewing this ticket in <b>read-only</b> mode. Click <b>Accept</b> to take ownership and enable editing.</span>
                </div>
            </div>

            <div class="modal-section" style="margin-top:1.25rem">Linked Knowledge Articles</div>
            <div id="vm-kba-list" style="margin-bottom:.6rem"></div>
            <div style="position:relative;margin-bottom:1rem">
                <div style="display:flex;gap:.5rem;align-items:center">
                    <div style="position:relative;flex:1">
                        <i class="bi bi-search" style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.8rem;pointer-events:none"></i>
                        <input type="text" id="vm-kba-search" placeholder="Search KBAs to attach…"
                            style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;color:var(--text);font-size:.8rem;padding:.4rem .75rem .4rem 2rem;outline:none"
                            oninput="filterKbaDropdown(this.value)" onfocus="showKbaDropdown()">
                    </div>
                </div>
                <div id="vm-kba-dropdown" style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--surface);border:1px solid var(--border);border-radius:.65rem;box-shadow:0 8px 24px rgba(0,0,0,.3);max-height:200px;overflow-y:auto;z-index:9999"></div>
            </div>

            <div class="modal-section" style="margin-top:1.25rem">Activity &amp; Notes</div>
            <div id="vm-notes-timeline" style="margin-bottom:.85rem"></div>

            <div id="vm-add-note-section" style="display:none">
                <div id="vm-note-error" style="display:none;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;padding:.45rem .75rem;border-radius:.6rem;font-size:.78rem;margin-bottom:.5rem"></div>
                <textarea class="m-textarea" id="vm-note-input" placeholder="Add a note or comment to this ticket…" style="min-height:72px;margin-bottom:.5rem;width:100%"></textarea>
                <div style="margin-bottom:.5rem">
                    <label style="display:flex;align-items:center;gap:.5rem;background:var(--surface2);border:1px dashed var(--border);border-radius:.6rem;padding:.4rem .75rem;cursor:pointer;font-size:.78rem;color:var(--muted);transition:border-color .15s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                        <i class="bi bi-paperclip"></i>
                        <span id="vm-note-file-label">Attach a file (max 5 MB)</span>
                        <input type="file" id="vm-note-file" style="display:none" onchange="updateNoteFileLabel(this)">
                    </label>
                </div>
                <button type="button" id="vm-note-submit" onclick="submitNote()"
                    style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border:none;border-radius:.6rem;color:white;font-size:.8rem;font-weight:600;padding:.4rem 1rem;cursor:pointer;display:flex;align-items:center;gap:.4rem;box-shadow:0 2px 8px rgba(99,102,241,.3)">
                    <i class="bi bi-chat-left-text-fill"></i> Add Note
                </button>
            </div>
        </div>

        <div class="flux-modal-footer" style="flex-wrap:wrap;gap:.5rem">
            <button class="btn-cancel" onclick="closeModal('viewModal')">Close</button>
            <a id="vm-print-btn" href="#" target="_blank"
               style="background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;color:var(--muted);font-size:.875rem;font-weight:600;padding:.5rem 1rem;display:flex;align-items:center;gap:.4rem;text-decoration:none !important;transition:background .15s,color .15s"
               onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                <i class="bi bi-printer"></i> Print / PDF
            </a>
            <button id="vm-route-btn" onclick="openRouteFromView()"
                style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);border-radius:.6rem;color:#fbbf24;font-size:.875rem;font-weight:600;padding:.5rem 1rem;display:flex;align-items:center;gap:.4rem;cursor:pointer;transition:background .15s">
                <i class="bi bi-arrow-left-right"></i> Route
            </button>
            <button id="vm-save-btn" class="btn-submit" style="display:none" onclick="document.getElementById('editTicketForm').submit()">
                <i class="bi bi-floppy"></i> Save Changes
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════
     ROUTE TICKET MODAL
════════════════════════════════════ --}}
<div class="flux-modal-backdrop" id="routeModal" onclick="handleBdClick(event,'routeModal')">
    <div class="flux-modal" style="max-width:480px">
        <div class="flux-modal-header">
            <div>
                <div style="font-size:.95rem;font-weight:700;color:var(--text)">Route Ticket</div>
                <div id="route-sub" style="font-size:.75rem;color:var(--muted);margin-top:.1rem">Forward to another department or technician</div>
            </div>
            <button onclick="closeModal('routeModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="flux-modal-body">
            <form id="routeForm" method="POST">
                @csrf
                <div class="m-field">
                    <label class="m-label">Department <span style="color:#f87171">*</span></label>
                    <select class="m-select" id="route-dept" name="department" required onchange="loadRouteDeptUsers(this.value)">
                        <option value="" disabled selected>Select department…</option>
                        @foreach($allowedDepts ?? ['IT','HR','Finance','OPIC','Dispatch','Asset/Admin','Marketing','RSO','Store','Accounting','Security'] as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="m-field">
                    <label class="m-label">Route To <span style="color:#f87171">*</span></label>
                    <select class="m-select" id="route-person" name="routed_to" required disabled>
                        <option value="" disabled selected>Select department first…</option>
                    </select>
                </div>
                <div class="m-field" style="margin-bottom:0">
                    <label class="m-label">Routing Note</label>
                    <textarea class="m-textarea" name="routing_note" placeholder="Reason for routing or additional context…" style="min-height:80px"></textarea>
                </div>
            </form>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('routeModal')">Cancel</button>
            <button type="button" id="routeSubmitBtn" onclick="submitRouteTicket()"
                style="background:rgba(251,191,36,.15);border:1px solid rgba(251,191,36,.3);border-radius:.6rem;color:#d97706;font-size:.875rem;font-weight:600;padding:.5rem 1.25rem;cursor:pointer;display:flex;align-items:center;gap:.4rem">
                <i class="bi bi-arrow-left-right"></i> Route Ticket
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════
     ASSIGN-TO-ME MODAL
════════════════════════════════════ --}}
<div class="flux-modal-backdrop" id="assignModal" onclick="handleBdClick(event,'assignModal')">
    <div class="flux-modal assign-modal">
        <div class="flux-modal-header">
            <div>
                <div style="font-size:.95rem;font-weight:700;color:var(--text)">Accept Ticket</div>
                <div id="assign-sub" style="font-size:.75rem;color:var(--muted);margin-top:.15rem"></div>
            </div>
            <button onclick="closeModal('assignModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="flux-modal-body">
            <div style="text-align:center;padding:1rem 0 1.5rem">
                <div style="width:56px;height:56px;background:rgba(52,211,153,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                    <i class="bi bi-person-check-fill" style="font-size:1.5rem;color:#34d399"></i>
                </div>
                <div style="font-size:.95rem;font-weight:700;color:var(--text);margin-bottom:.35rem">Accept this ticket?</div>
                <div style="font-size:.83rem;color:var(--muted)">You will be assigned as the agent responsible for resolving this ticket.</div>
            </div>
            <form id="assignForm" method="POST">
                @csrf
            </form>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('assignModal')">Cancel</button>
            <button type="submit" form="assignForm" class="btn-accept">
                <i class="bi bi-person-check-fill"></i> Accept Ticket
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════
     KBA DETACH CONFIRMATION MODAL
════════════════════════════════════ --}}
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
</div>
<script>
(function() {
    let _kbaConfirmCb = null;
    const overlay = document.getElementById('kbaConfirmModal');
    const box     = document.getElementById('kbaConfirmBox');
    const msg     = document.getElementById('kbaConfirmMsg');
    const okBtn   = document.getElementById('kbaConfirmOk');
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

{{-- KBA ATTACH CONFIRMATION MODAL --}}
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
                <span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;display:none" id="kbaAttachOkSpinner"></span>
                <span id="kbaAttachOkLabel">Yes, Attach KBA</span>
            </button>
        </div>
    </div>
</div>
<script>
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

@push('scripts')
<script>
    // ── Force tickets dropdown open on tickets pages ──
    (function() {
        const submenu = document.getElementById('ticketsSubmenu');
        const chevron = document.getElementById('ticketsDropdownTrigger')?.querySelector('.nav-chevron');
        if (submenu) submenu.classList.add('open');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        localStorage.setItem('ticketsDropdown', 'open');
    })();

    // ── Status filter ──
    const _FKEY = 'flux_filter_tickets';
    let currentStatus   = 'all';
    let currentPriority = 'all';

    function _saveFilter() {
        try { localStorage.setItem(_FKEY, JSON.stringify({ status: currentStatus, priority: currentPriority })); } catch(e){}
    }
    function filterStatus(s, btn) {
        currentStatus = s;
        document.querySelectorAll('#statusFilters .filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        _saveFilter(); applyFilters();
    }
    function filterPriority(p, btn) {
        currentPriority = p;
        document.querySelectorAll('#priorityFilters .filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        _saveFilter(); applyFilters();
    }
    function applyFilters() {
        const q = (document.getElementById('searchInput')?.value || '').toLowerCase();
        document.querySelectorAll('#ticketTable tbody tr').forEach(row => {
            const matchStatus   = currentStatus === 'all'   || row.dataset.status   === currentStatus;
            const matchPriority = currentPriority === 'all' || row.dataset.priority === currentPriority;
            const matchSearch   = !q || row.textContent.toLowerCase().includes(q);
            row.style.display = (matchStatus && matchPriority && matchSearch) ? '' : 'none';
        });
        updateTableScroll();
    }
    function updateTableScroll() {
        const wrap = document.getElementById('tableWrap');
        if (!wrap) return;
        const visible = [...wrap.querySelectorAll('#ticketTable tbody tr')].filter(r => r.style.display !== 'none').length;
        wrap.style.maxHeight = visible > 9 ? '540px' : '';
        wrap.style.overflowY = visible > 9 ? 'auto' : 'hidden';
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
        } catch(e) {}
    })();

    // Auto-open ticket from ?open= URL param (e.g. from notifications)
    (function() {
        const openId = new URLSearchParams(window.location.search).get('open');
        if (!openId) return;
        history.replaceState(null, '', window.location.pathname);
        const row = document.querySelector('#ticketTable tbody tr[data-ticket-id="'+openId+'"]');
        if (row) {
            row.style.display = '';
            const viewBtn = row.querySelector('.btn-view');
            if (viewBtn) setTimeout(() => viewBtn.click(), 200);
        }
    })();

    // ── Modal helpers ──
    function submitRouteTicket() {
        const btn = document.getElementById('routeSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(217,119,6,.35);border-top-color:#d97706;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.35rem"></span> Routing…';
        document.getElementById('routeForm').submit();
    }
    function handleBdClick(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal('viewModal');
            closeModal('assignModal');
            closeModal('routeModal');
        }
    });

    // ── Status/label maps ──
    const sMap   = { open:'s-open', progress:'s-progress', resolved:'s-resolved', closed:'s-closed' };
    const sLabel = { open:'Open', progress:'In Progress', resolved:'Resolved', closed:'Closed' };

    const currentUser   = @json(auth()->user()->name);
    const currentUserId = {{ auth()->id() }};

    // Auto-open a specific ticket passed from server (?open=)
    @if(!empty($openTicket))
    document.addEventListener('DOMContentLoaded', function() {
        const _ot = @json($openTicket->load(['user','notes.user','knowledgeArticles'])->toArray());
        openView(_ot);
    });
    @endif

    // ── View / Edit modal ──
    function openView(t) {
        document.getElementById('vm-number').textContent           = t.ticket_number;
        document.getElementById('vm-subject').textContent          = t.subject;
        document.getElementById('vm-requester').textContent        = t.requester || (t.user?.name ?? 'Unknown');
        document.getElementById('vm-category').textContent         = t.category;
        document.getElementById('vm-type').textContent             = t.type || '—';
        document.getElementById('vm-assignee').textContent         = t.assignee || 'Unassigned';
        document.getElementById('vm-resolved-by').textContent      = t.resolved_by || '—';
        document.getElementById('vm-created').textContent          = new Date(t.created_at).toLocaleString();
        document.getElementById('vm-updated-at').textContent       = t.updated_at ? new Date(t.updated_at).toLocaleString() : '—';
        document.getElementById('vm-description').textContent      = t.description;

        const badge = document.getElementById('vm-status-badge');
        badge.className   = 'badge-status ' + (sMap[t.status] || 's-open');
        badge.textContent = sLabel[t.status] || t.status;

        const resSection = document.getElementById('vm-resolution-section');
        if (t.resolution) {
            resSection.style.display = '';
            document.getElementById('vm-resolution').textContent = t.resolution;
        } else {
            resSection.style.display = 'none';
        }

        const isAssignee = t.assignee && t.assignee === currentUser;

        document.getElementById('vm-edit-section').style.display    = isAssignee ? '' : 'none';
        document.getElementById('vm-readonly-notice').style.display  = isAssignee ? 'none' : '';
        document.getElementById('vm-save-btn').style.display         = isAssignee ? '' : 'none';

        if (isAssignee) {
            document.getElementById('vm-status-sel').value     = t.status;
            document.getElementById('vm-priority-sel').value   = t.priority;
            document.getElementById('vm-assignee-inp').value   = t.assignee || '';
            document.getElementById('vm-resolution-inp').value = t.resolution || '';
            document.getElementById('editTicketForm').action   = '/tickets/' + t.id;

            const previewWrap = document.getElementById('res-img-preview-wrap');
            if (t.resolution_image) {
                const url = '/storage/' + t.resolution_image;
                const filename = t.resolution_image.split('/').pop();
                const link = document.getElementById('res-img-preview');
                link.href = url;
                link.download = filename;
                document.getElementById('res-img-filename').textContent = filename;
                const uploadedAt = t.updated_at
                    ? new Date(t.updated_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'})
                    : '';
                document.getElementById('res-img-uploaded-at').textContent = uploadedAt;
                previewWrap.style.display = '';
            } else {
                previewWrap.style.display = 'none';
            }
        }

        // ── SLA ──
        const slaDue       = document.getElementById('vm-sla-due');
        const slaBadge     = document.getElementById('vm-sla-badge');
        const slaBarWrap   = document.getElementById('vm-sla-bar-wrap');
        const slaBar       = document.getElementById('vm-sla-bar');
        const slaPctLabel  = document.getElementById('vm-sla-pct-label');
        const slaTimeLabel = document.getElementById('vm-sla-time-label');

        const slaColors = { ok:'#34d399', warning:'#fbbf24', breached:'#f87171', met:'#818cf8' };
        const slaLabels = { ok:'On Track', warning:'At Risk', breached:'Breached', met:'Met' };

        if (t.sla_due_at) {
            const due      = new Date(t.sla_due_at);
            const created  = new Date(t.created_at);
            const now      = new Date();
            const isDone   = ['resolved','closed'].includes(t.status);
            const compareAt= isDone && t.resolved_at ? new Date(t.resolved_at) : now;

            let ss;
            if (compareAt > due)          ss = 'breached';
            else if (isDone)              ss = 'met';
            else {
                const total = due - created;
                const rem   = due - now;
                ss = (rem / total) <= 0.25 ? 'warning' : 'ok';
            }

            slaDue.textContent = due.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' })
                + ' — ' + due.toLocaleTimeString('en-US', { hour:'numeric', minute:'2-digit' });
            slaBadge.innerHTML = `<span class="sla-badge sla-${ss}"><span class="sla-dot"></span>${slaLabels[ss]}</span>`;

            if (!isDone) {
                slaBarWrap.style.display = '';
                const totalSec = (due - created) / 1000;
                const usedSec  = (now - created) / 1000;
                const pct      = Math.min(100, Math.round((usedSec / totalSec) * 100));
                const remMs    = due - now;
                const remHrs   = Math.round(remMs / 3600000);
                const remDays  = Math.floor(remHrs / 24);
                const remLabel = remMs < 0
                    ? `Overdue by ${Math.abs(remDays)}d ${Math.abs(remHrs % 24)}h`
                    : remDays > 0 ? `${remDays}d ${remHrs % 24}h remaining` : `${remHrs}h remaining`;

                const barColor = pct >= 100 ? '#f87171' : pct >= 50 ? '#fbbf24' : '#34d399';
                slaBar.style.width      = pct + '%';
                slaBar.style.background = barColor;
                slaPctLabel.textContent = pct + '% elapsed';
                slaTimeLabel.textContent = remLabel;
            } else {
                slaBarWrap.style.display = 'none';
            }
        } else {
            slaDue.textContent  = '—';
            slaBadge.innerHTML  = '<span style="color:var(--muted);font-size:.8rem">—</span>';
            slaBarWrap.style.display = 'none';
        }

        _currentTicketId     = t.id;
        _currentTicketStatus = t.status;
        document.getElementById('vm-print-btn').href = '/tickets/' + t.id + '/print';
        document.getElementById('vm-route-btn').dataset.ticketId  = t.id;
        document.getElementById('vm-route-btn').dataset.ticketNum = t.ticket_number;

        if (_kbaCache[t.id] === undefined) {
            _kbaCache[t.id] = [...(t.knowledge_articles || [])];
        }
        renderKbaList(_kbaCache[t.id]);
        document.getElementById('vm-kba-search').value = '';
        hideKbaDropdown();

        if (_notesCache[t.id] === undefined) {
            _notesCache[t.id] = [...(t.notes || [])];
        }
        renderTimeline(_notesCache[t.id]);

        const isSubmitter = t.user_id === currentUserId;
        document.getElementById('vm-add-note-section').style.display = (isSubmitter || isAssignee) ? '' : 'none';
        document.getElementById('vm-note-input').value = '';
        document.getElementById('vm-note-file').value = '';
        document.getElementById('vm-note-file-label').textContent = 'Attach a file (max 5 MB)';
        document.getElementById('vm-note-error').style.display = 'none';

        const vmBody = document.querySelector('#viewModal .flux-modal-body');
        if (vmBody) { vmBody.style.opacity = '0'; vmBody.style.transition = ''; }
        openModal('viewModal');
        requestAnimationFrame(() => requestAnimationFrame(() => {
            if (vmBody) { vmBody.style.transition = 'opacity .3s ease'; vmBody.style.opacity = '1'; }
        }));
    }

    // ── Note / KBA state ──
    let _currentTicketId     = null;
    let _currentTicketStatus = null;
    const _notesCache = {};
    const _kbaCache   = {};
    const _csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const _allKbas    = @json($allKbas ?? []);

    function renderKbaList(kbas) {
        const el = document.getElementById('vm-kba-list');
        if (!kbas || kbas.length === 0) {
            el.innerHTML = '<div style="font-size:.78rem;color:var(--muted);padding:.35rem 0">No KBAs linked yet.</div>';
            return;
        }
        const locked = ['resolved','closed'].includes(_currentTicketStatus);
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
        dd.innerHTML = !matches.length
            ? '<div style="padding:.5rem .85rem;font-size:.78rem;color:var(--muted)">No matching KBAs found.</div>'
            : matches.map(k => `
                <div onclick="confirmAttachKba(${k.id})"
                     style="padding:.45rem .85rem;cursor:pointer;font-size:.8rem;transition:background .1s;display:flex;align-items:center;gap:.5rem"
                     onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background=''">
                    <i class="bi bi-journal-text" style="color:#818cf8;flex-shrink:0"></i>
                    <span style="color:#818cf8;font-weight:600;flex-shrink:0">${escHtml(k.kba_number || '#KBA-' + String(k.id).padStart(4,'0'))}</span>
                    <span style="color:var(--text);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(k.title)}</span>
                    <span style="color:var(--muted);font-size:.72rem;flex-shrink:0">${escHtml(k.category || '')}</span>
                </div>`).join('');
        dd.style.display = '';
    }

    function showKbaDropdown() { filterKbaDropdown(document.getElementById('vm-kba-search').value); }
    function hideKbaDropdown() { const dd = document.getElementById('vm-kba-dropdown'); if (dd) dd.style.display = 'none'; }

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
        if (btn) { btn.disabled = true; icon.style.display = 'none'; spinner.style.display = 'inline-block'; label.textContent = 'Attaching…'; }

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
        const isRoute        = n.type === 'route_event';
        const isStatusChange = n.type === 'status_change';
        const initial  = (n.user?.name || '?').charAt(0).toUpperCase();
        const dateStr  = new Date(n.created_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'});
        const rawBody  = (n.content || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')
            .replace(/^&gt; (.+)$/gm,'<div style="border-left:3px solid rgba(99,102,241,.3);padding-left:.6rem;color:var(--muted);margin-top:.3rem;font-size:.8rem">$1</div>');

        if (isStatusChange) {
            return `<div style="display:flex;gap:.7rem;margin-bottom:.85rem;align-items:flex-start">
                <div style="width:28px;height:28px;min-width:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:white;background:linear-gradient(135deg,#0891b2,#0e7490)">${initial}</div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem">
                        <span style="font-size:.8rem;font-weight:600;color:var(--text)">${n.user?.name || 'Unknown'}</span>
                        <span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(8,145,178,.15);color:#22d3ee;padding:.1rem .45rem;border-radius:9999px;letter-spacing:.04em"><i class="bi bi-arrow-repeat"></i> Status Update</span>
                        <span style="font-size:.7rem;color:var(--muted)">${dateStr}</span>
                    </div>
                    <div style="font-size:.83rem;color:var(--text);line-height:1.55;background:rgba(8,145,178,.07);border:1px solid rgba(8,145,178,.25);border-radius:.5rem;padding:.55rem .8rem">${rawBody}</div>
                </div>
            </div>`;
        }

        const avatarBg = isRoute ? 'linear-gradient(135deg,#2563eb,#4f46e5)' : 'linear-gradient(135deg,#4f46e5,#7c3aed)';
        const chip     = isRoute
            ? `<span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(59,130,246,.15);color:#60a5fa;padding:.1rem .45rem;border-radius:9999px;letter-spacing:.04em"><i class="bi bi-arrow-left-right"></i> Routed</span>`
            : `<span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(99,102,241,.15);color:#818cf8;padding:.1rem .45rem;border-radius:9999px;letter-spacing:.04em"><i class="bi bi-chat-left-text"></i> Note</span>`;
        const attachHtml = n.attachment
            ? `<div style="margin-top:.4rem"><a href="/storage/${n.attachment}" target="_blank" style="font-size:.75rem;color:#818cf8;display:inline-flex;align-items:center;gap:.3rem"><i class="bi bi-paperclip"></i> View attachment</a></div>`
            : '';
        return `<div style="display:flex;gap:.7rem;margin-bottom:.85rem">
            <div style="width:28px;height:28px;min-width:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:white;background:${avatarBg}">${initial}</div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem">
                    <span style="font-size:.8rem;font-weight:600;color:var(--text)">${n.user?.name || 'Unknown'}</span>
                    ${chip}
                    <span style="font-size:.7rem;color:var(--muted)">${dateStr}</span>
                </div>
                <div style="font-size:.83rem;color:var(--text);line-height:1.55;background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;padding:.55rem .8rem">${rawBody}${attachHtml}</div>
            </div>
        </div>`;
    }

    function renderTimeline(notes) {
        const timeline = document.getElementById('vm-notes-timeline');
        timeline.innerHTML = !notes.length
            ? `<div style="text-align:center;color:var(--muted);font-size:.82rem;padding:.75rem 0;font-style:italic">No notes or activity yet.</div>`
            : notes.map(renderNoteHtml).join('');
    }

    function updateNoteFileLabel(input) {
        const label = document.getElementById('vm-note-file-label');
        if (input.files.length) {
            const f = input.files[0];
            if (f.size > 5 * 1024 * 1024) {
                input.value = '';
                label.textContent = 'Attach a file (max 5 MB)';
                const err = document.getElementById('vm-note-error');
                err.textContent = 'File exceeds 5 MB limit.';
                err.style.display = '';
                return;
            }
            label.textContent = '📎 ' + f.name;
        } else {
            label.textContent = 'Attach a file (max 5 MB)';
        }
    }

    async function submitNote() {
        const content   = document.getElementById('vm-note-input').value.trim();
        const fileInput = document.getElementById('vm-note-file');
        const errEl     = document.getElementById('vm-note-error');
        errEl.style.display = 'none';

        if (!content) { errEl.textContent = 'Note cannot be empty.'; errEl.style.display = ''; return; }

        const fd = new FormData();
        fd.append('_token', _csrfToken);
        fd.append('content', content);
        if (fileInput.files.length) fd.append('attachment', fileInput.files[0]);

        const btn = document.getElementById('vm-note-submit');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:12px;height:12px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.3rem"></span> Adding…';

        try {
            const r    = await fetch('/tickets/' + _currentTicketId + '/notes', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': _csrfToken },
                body: fd,
            });
            const data = await r.json();
            if (!r.ok || !data.ok) throw new Error(data.message || 'Server error');

            if (!_notesCache[_currentTicketId]) _notesCache[_currentTicketId] = [];
            _notesCache[_currentTicketId].push(data.note);

            const timeline = document.getElementById('vm-notes-timeline');
            const empty = timeline.querySelector('div[style*="font-style:italic"]');
            if (empty) timeline.innerHTML = '';
            timeline.insertAdjacentHTML('beforeend', renderNoteHtml(data.note));

            document.getElementById('vm-note-input').value = '';
            fileInput.value = '';
            document.getElementById('vm-note-file-label').textContent = 'Attach a file (max 5 MB)';
        } catch (e) {
            errEl.textContent = e.message || 'Failed to add note. Please try again.';
            errEl.style.display = '';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-chat-left-text-fill"></i> Add Note';
        }
    }

    // ── Route modal ──
    let _routeTicketId = null;
    function openRouteFromView() {
        const btn = document.getElementById('vm-route-btn');
        _routeTicketId = btn.dataset.ticketId;
        document.getElementById('route-sub').textContent = btn.dataset.ticketNum + ' — Forward to another department';
        document.getElementById('routeForm').action = '/tickets/' + _routeTicketId + '/route';
        document.getElementById('route-dept').value = '';
        const personSel = document.getElementById('route-person');
        personSel.innerHTML = '<option value="" disabled selected>Select department first…</option>';
        personSel.disabled = true;
        closeModal('viewModal');
        openModal('routeModal');
    }

    function showResImg(input) {
        const d = document.getElementById('res-img-display');
        if (input.files.length) { d.textContent = '🖼 ' + input.files[0].name; d.style.display = 'block'; }
    }

    // ── Route dept → person dropdown ──
    const deptUsers = @json($deptUsers->map(fn($u) => $u->pluck('name')));
    function loadRouteDeptUsers(dept) {
        const sel   = document.getElementById('route-person');
        const users = deptUsers[dept] || [];
        sel.innerHTML = '<option value="" disabled selected>Select person…</option>';
        users.forEach(name => {
            const opt = document.createElement('option');
            opt.value = name; opt.textContent = name;
            sel.appendChild(opt);
        });
        sel.disabled = users.length === 0;
    }

    // ── Assign-to-me modal ──
    function openAssign(id, ticketNumber) {
        document.getElementById('assign-sub').textContent = 'Ticket ' + ticketNumber;
        document.getElementById('assignForm').action = '/tickets/' + id + '/assign-me';
        openModal('assignModal');
    }
</script>
@endpush
