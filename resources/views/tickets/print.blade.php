@php use Illuminate\Support\Facades\Storage; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ticket->ticket_number }} — FluxTickets</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #1e293b; background: #fff; }

        /* ── Screen wrapper ── */
        .screen-wrap { max-width: 800px; margin: 2rem auto; padding: 1.5rem; }

        /* ── Print button (hidden in print) ── */
        .print-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .75rem; }
        .btn-print { background: linear-gradient(135deg,#4f46e5,#7c3aed); color: white; border: none; border-radius: .5rem; padding: .5rem 1.25rem; font-size: .875rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: .4rem; }
        .btn-print:hover { opacity: .9; }
        .btn-back { color: #64748b; font-size: .825rem; text-decoration: none; display: flex; align-items: center; gap: .35rem; }
        .btn-back:hover { color: #1e293b; }

        /* ── Document ── */
        .doc { border: 1px solid #e2e8f0; border-radius: .75rem; overflow: hidden; }

        /* Header */
        .doc-header { background: linear-gradient(135deg,#4338ca,#6d28d9); padding: 1.5rem 2rem; color: white; display: flex; align-items: center; justify-content: space-between; }
        .doc-logo { display: flex; align-items: center; gap: .75rem; }
        .logo-icon { width: 40px; height: 40px; background: rgba(255,255,255,.2); border-radius: .65rem; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .logo-name { font-size: 1.15rem; font-weight: 800; letter-spacing: -.01em; }
        .logo-sub  { font-size: .72rem; color: rgba(255,255,255,.7); margin-top: .15rem; }
        .doc-ticket-id { text-align: right; }
        .doc-ticket-id .tid { font-size: 1.1rem; font-weight: 800; font-family: monospace; color: rgba(255,255,255,.95); }
        .doc-ticket-id .tdate { font-size: .72rem; color: rgba(255,255,255,.65); margin-top: .2rem; }

        /* Status strip */
        .status-strip { display: flex; align-items: center; gap: 1.5rem; padding: .65rem 2rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; }
        .strip-item { display: flex; align-items: center; gap: .4rem; font-size: .78rem; }
        .strip-label { color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; font-size: .65rem; }
        .badge { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .6rem; border-radius: 9999px; font-size: .7rem; font-weight: 700; }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
        .b-open     { background: rgba(99,102,241,.12); color: #4f46e5; } .b-open::before { background: #4f46e5; }
        .b-progress { background: rgba(217,119,6,.12);  color: #b45309; } .b-progress::before { background: #d97706; }
        .b-resolved { background: rgba(5,150,105,.12);  color: #065f46; } .b-resolved::before { background: #059669; }
        .b-closed   { background: rgba(100,116,139,.12);color: #475569; } .b-closed::before { background: #94a3b8; }
        .b-high   { background: rgba(220,38,38,.1);  color: #991b1b; border: 1px solid rgba(220,38,38,.2); }
        .b-medium { background: rgba(217,119,6,.1);  color: #92400e; border: 1px solid rgba(217,119,6,.2); }
        .b-low    { background: rgba(5,150,105,.1);  color: #065f46; border: 1px solid rgba(5,150,105,.2); }

        /* Body */
        .doc-body { padding: 1.75rem 2rem; }

        .section-title { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin-bottom: .65rem; display: flex; align-items: center; gap: .5rem; }
        .section-title::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem 2rem; margin-bottom: 1.5rem; }
        .info-row { padding: .45rem 0; border-bottom: 1px dashed #f1f5f9; }
        .info-row .lbl { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-bottom: .1rem; }
        .info-row .val { font-size: .875rem; color: #1e293b; font-weight: 500; }

        .content-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .5rem; padding: .85rem 1rem; font-size: .875rem; line-height: 1.65; color: #334155; white-space: pre-wrap; margin-bottom: 1.25rem; }

        /* Routing info */
        .route-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: .5rem; padding: .85rem 1rem; margin-bottom: 1.25rem; }
        .route-box .route-head { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #3b82f6; margin-bottom: .4rem; display: flex; align-items: center; gap: .35rem; }

        /* Resolution image */
        .res-image { max-width: 100%; border-radius: .5rem; border: 1px solid #e2e8f0; margin-top: .75rem; }

        /* ── Signature section ── */
        .sig-section { margin-top: 2rem; }
        .sig-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 1rem; }
        .sig-box { text-align: center; }
        .sig-line { border-bottom: 1.5px solid #334155; margin-bottom: .4rem; height: 48px; position: relative; }
        .sig-name-prefill { position: absolute; bottom: 6px; left: 0; right: 0; font-size: .8rem; font-weight: 600; color: #334155; text-align: center; }
        .sig-label { font-size: .72rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
        .sig-date-line { border-bottom: 1px solid #94a3b8; margin-top: .75rem; }
        .sig-date-label { font-size: .65rem; color: #94a3b8; margin-top: .2rem; }

        /* Footer */
        .doc-footer { border-top: 1px solid #e2e8f0; padding: .85rem 2rem; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; }
        .doc-footer span { font-size: .68rem; color: #94a3b8; }

        /* ── Print styles ── */
        @page { size: landscape; margin: 0.35in; }
        @media print {
            body { font-size: 8pt; }
            .screen-wrap { margin: 0; padding: 0; max-width: 100%; }
            .print-toolbar { display: none !important; }
            .doc { border: none; border-radius: 0; }

            /* Header */
            .doc-header { padding: .55rem 1.1rem; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .logo-icon { width: 28px; height: 28px; font-size: .85rem; border-radius: .45rem; }
            .logo-name { font-size: .95rem; }
            .logo-sub  { font-size: .6rem; margin-top: .05rem; }
            .doc-ticket-id .tid   { font-size: .95rem; }
            .doc-ticket-id .tdate { font-size: .6rem; margin-top: .1rem; }

            /* Status strip */
            .status-strip { padding: .35rem 1.1rem; gap: 1rem; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .strip-item  { font-size: .68rem; }
            .strip-label { font-size: .55rem; }
            .badge       { font-size: .6rem; padding: .12rem .5rem; }

            /* Body */
            .doc-body { padding: .75rem 1.1rem; }
            .doc-body h2 { font-size: .9rem !important; margin-bottom: .5rem !important; padding-bottom: .3rem !important; border-bottom-width: 1px !important; }
            .section-title { font-size: .58rem; margin-bottom: .3rem; }

            /* Info grid — 4 columns in landscape to save vertical space */
            .info-grid { grid-template-columns: 1fr 1fr 1fr 1fr; gap: .2rem .9rem; margin-bottom: .55rem; }
            .info-row  { padding: .15rem 0; }
            .info-row .lbl { font-size: .52rem; margin-bottom: .02rem; }
            .info-row .val { font-size: .7rem; }

            /* Content boxes */
            .content-box { padding: .4rem .6rem; font-size: .7rem; line-height: 1.35; margin-bottom: .55rem; }
            .route-box   { padding: .4rem .6rem; margin-bottom: .55rem; }
            .route-box .route-head { font-size: .6rem; margin-bottom: .2rem; }

            /* Signatures */
            .sig-section { margin-top: .5rem; page-break-inside: avoid; }
            .sig-section p { font-size: .62rem !important; margin-bottom: .4rem !important; }
            .sig-grid { gap: .9rem; margin-top: .4rem; }
            .sig-line { height: 30px; border-bottom-width: 1px; }
            .sig-name-prefill { font-size: .68rem; bottom: 3px; }
            .sig-label { font-size: .55rem; }
            .sig-date-line { margin-top: .35rem; }
            .sig-date-label { font-size: .5rem; margin-top: .1rem; }

            /* Footer */
            .doc-footer { padding: .35rem 1.1rem; }
            .doc-footer span { font-size: .55rem; }

            a { color: inherit; text-decoration: none; }
            .doc, .doc-body, .sig-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="screen-wrap">

    {{-- Toolbar (screen only) --}}
    <div class="print-toolbar">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('queue') }}" class="btn-back">
            ← Back
        </a>
        <button class="btn-print" onclick="window.print()">
            🖨 Print / Save as PDF
        </button>
    </div>

    {{-- Document --}}
    <div class="doc">

        {{-- Header --}}
        <div class="doc-header">
            <div class="doc-logo">
                <div class="logo-icon">🎫</div>
                <div>
                    <div class="logo-name">FluxTickets</div>
                    <div class="logo-sub">Service Ticket Report</div>
                </div>
            </div>
            <div class="doc-ticket-id">
                <div class="tid">{{ $ticket->ticket_number }}</div>
                <div class="tdate">Printed: {{ now()->format('F j, Y — g:i A') }}</div>
            </div>
        </div>

        {{-- Status strip --}}
        @php
            $sClass = ['open'=>'b-open','progress'=>'b-progress','resolved'=>'b-resolved','closed'=>'b-closed'];
            $sLabel = ['open'=>'Open','progress'=>'In Progress','resolved'=>'Resolved','closed'=>'Closed'];
            $pClass = ['low'=>'b-low','medium'=>'b-medium','high'=>'b-high'];
        @endphp
        <div class="status-strip">
            <div class="strip-item">
                <span class="strip-label">Status</span>
                <span class="badge {{ $sClass[$ticket->status] ?? 'b-open' }}">{{ $sLabel[$ticket->status] ?? $ticket->status }}</span>
            </div>
            <div class="strip-item">
                <span class="strip-label">Priority</span>
                <span class="badge {{ $pClass[$ticket->priority] ?? 'b-low' }}">{{ ucfirst($ticket->priority) }}</span>
            </div>
            <div class="strip-item">
                <span class="strip-label">Category</span>
                <strong style="font-size:.8rem">{{ $ticket->category }}</strong>
            </div>
            @if($ticket->type)
            <div class="strip-item">
                <span class="strip-label">Type</span>
                <strong style="font-size:.8rem">{{ $ticket->type }}</strong>
            </div>
            @endif
        </div>

        {{-- Body --}}
        <div class="doc-body">

            {{-- Subject --}}
            <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin-bottom:1.25rem;padding-bottom:.65rem;border-bottom:2px solid #e2e8f0">
                {{ $ticket->subject }}
            </h2>

            {{-- Info grid --}}
            <div class="section-title">Ticket Details</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="lbl">Requester</div>
                    <div class="val">{{ $ticket->requester ?? $ticket->user->name ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="lbl">Date Submitted</div>
                    <div class="val">{{ $ticket->created_at->format('F j, Y — g:i A') }}</div>
                </div>
                <div class="info-row">
                    <div class="lbl">Assigned To</div>
                    <div class="val">{{ $ticket->assignee ?: 'Unassigned' }}</div>
                </div>
                <div class="info-row">
                    <div class="lbl">Department</div>
                    <div class="val">{{ $ticket->department ?: '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="lbl">Resolved By</div>
                    <div class="val">{{ $ticket->resolved_by ?: '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="lbl">Date Resolved</div>
                    <div class="val">{{ $ticket->resolved_at ? $ticket->resolved_at->format('F j, Y — g:i A') : '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="lbl">SLA Deadline</div>
                    <div class="val">{{ $ticket->sla_due_at ? $ticket->sla_due_at->format('F j, Y g:i A') : '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="lbl">SLA Status</div>
                    <div class="val">
                        @php
                            $sc = ['ok'=>'#059669','warning'=>'#d97706','breached'=>'#dc2626','met'=>'#4f46e5'];
                        @endphp
                        <span style="font-weight:700;color:{{ $sc[$ticket->sla_status] ?? '#64748b' }}">
                            {{ $ticket->sla_label }}
                        </span>
                        @if($ticket->sla_due_at)
                        <span style="color:#64748b;font-size:.8rem">
                            — SLA window: {{ \App\Models\Ticket::SLA_DAYS[$ticket->priority] ?? '?' }} days ({{ ucfirst($ticket->priority) }} priority)
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="section-title">Description</div>
            <div class="content-box">{{ $ticket->description }}</div>

            {{-- Routing info --}}
            @if($ticket->routed_to)
            <div class="section-title">Routing History</div>
            <div class="route-box">
                <div class="route-head">↪ Routed to {{ $ticket->routed_to }} — {{ $ticket->department }}</div>
                <div style="font-size:.82rem;color:#1e40af">
                    <strong>Routed at:</strong> {{ $ticket->routed_at?->format('F j, Y g:i A') }}<br>
                    @if($ticket->routing_note)
                    <strong>Note:</strong> {{ $ticket->routing_note }}
                    @endif
                </div>
            </div>
            @endif

            {{-- Resolution --}}
            @if($ticket->resolution)
            <div class="section-title">Resolution Notes</div>
            <div class="content-box" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534">{{ $ticket->resolution }}</div>

            @if($ticket->resolution_image)
            <div style="margin-bottom:1.5rem">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:.5rem">Resolution Image</div>
                <img src="{{ Storage::url($ticket->resolution_image) }}" alt="Resolution Image" class="res-image">
            </div>
            @endif
            @endif

            {{-- Signatures --}}
            <div class="sig-section">
                <div class="section-title">Acknowledgement &amp; Sign-off</div>
                <p style="font-size:.78rem;color:#64748b;margin-bottom:1rem">
                    By signing below, all parties acknowledge that the above service ticket has been reviewed and the resolution is satisfactory.
                </p>
                <div class="sig-grid">

                    <div class="sig-box">
                        <div class="sig-line">
                            <div class="sig-name-prefill">{{ $ticket->requester ?? $ticket->user->name ?? '' }}</div>
                        </div>
                        <div class="sig-label">Requester</div>
                        <div class="sig-date-line"></div>
                        <div class="sig-date-label">Date</div>
                    </div>

                    <div class="sig-box">
                        <div class="sig-line">
                            <div class="sig-name-prefill">{{ $ticket->assignee ?? '' }}</div>
                        </div>
                        <div class="sig-label">Technician / Assignee</div>
                        <div class="sig-date-line"></div>
                        <div class="sig-date-label">Date</div>
                    </div>

                    <div class="sig-box">
                        <div class="sig-line"></div>
                        <div class="sig-label">Department Head / Supervisor</div>
                        <div class="sig-date-line"></div>
                        <div class="sig-date-label">Date</div>
                    </div>

                </div>
            </div>

        </div>{{-- /doc-body --}}

        {{-- Footer --}}
        <div class="doc-footer">
            <span>FluxTickets — Service Management System</span>
            <span>{{ $ticket->ticket_number }} &bull; Generated {{ now()->format('Y-m-d H:i') }}</span>
        </div>

    </div>{{-- /doc --}}
</div>
</body>
</html>
