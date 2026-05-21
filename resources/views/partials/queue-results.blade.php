{{-- My Queue results — server-rendered, swapped in via AJAX. Expects $tickets (paginator). --}}
@php
    $statusMap   = ['open'=>['s-open','Open'],'progress'=>['s-progress','In Progress'],'resolved'=>['s-resolved','Resolved'],'closed'=>['s-closed','Closed'],'rejected'=>['s-rejected','Rejected']];
    $priorityMap = ['high'=>'p-high','medium'=>'p-medium','low'=>'p-low'];
@endphp
@if($tickets->isEmpty())
<div style="padding:4rem;text-align:center;color:var(--muted)">
    <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
    <div style="font-size:.95rem;font-weight:600">No tickets found</div>
    <div style="font-size:.82rem;margin-top:.35rem">
        @if(request()->hasAny(['status','priority','type','q']))
            No tickets match the current filters.
        @else
            No tickets are assigned to you right now.
        @endif
    </div>
</div>
@else
<div style="overflow-x:auto">
    <table class="flux-table">
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
            <tr>
                <td><span style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace">{{ $t->ticket_number }}</span></td>
                <td style="max-width:200px">
                    <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->subject }}</div>
                    <div style="font-size:.72rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->description }}</div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <div style="width:25px;height:25px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:white;flex-shrink:0">{{ strtoupper(substr($t->requester ?? $t->user?->name ?? '?', 0, 1)) }}</div>
                        <span style="white-space:nowrap">{{ $t->requester ?? $t->user?->name ?? '—' }}</span>
                    </div>
                </td>
                <td><span class="badge-priority {{ $priorityMap[$t->priority] ?? 'p-low' }}">{{ ucfirst($t->priority) }}</span></td>
                <td><span class="badge-status {{ $statusMap[$t->status][0] ?? 's-open' }}">{{ $statusMap[$t->status][1] ?? 'Open' }}</span></td>
                <td style="color:var(--muted);font-size:.82rem;white-space:nowrap">{{ $t->type ?: '—' }}</td>
                <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->created_at->diffForHumans() }}</td>
                <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->updated_at->diffForHumans() }}</td>
                <td style="text-align:center">
                    <div style="display:flex;align-items:center;justify-content:center;gap:.4rem">
                        <button class="btn-view" onclick='openView(@json($t->toArray()), @json($t->requester ?? $t->user?->name ?? "Unknown"))'>
                            <i class="bi bi-eye me-1"></i>View
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('partials.pager', ['paginator' => $tickets])
@endif
