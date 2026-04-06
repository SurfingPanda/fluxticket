<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ticket Assigned</title>
</head>
<body style="margin:0;padding:0;background:#e8edf6;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#e8edf6;padding:32px 0;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

        {{-- Header --}}
        <tr>
          <td style="background:#0f172a;border-radius:12px 12px 0 0;padding:28px 36px;text-align:center;">
            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
              <tr>
                <td style="vertical-align:middle;padding-right:10px;">
                  <img src="{{ asset('logo/Gemini_Generated_Image_1w1sif1w1sif1w1s-removebg-preview.png') }}"
                       alt="FluxTickets"
                       width="44" height="44"
                       style="display:block;filter:drop-shadow(0 2px 6px rgba(180,20,40,.5));">
                </td>
                <td style="vertical-align:middle;">
                  <span style="font-size:1.35rem;font-weight:700;color:#ffffff;letter-spacing:.02em;">FluxTickets</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- Accent bar --}}
        <tr>
          <td style="height:4px;background:linear-gradient(90deg,#6366f1,#7c3aed);"></td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="background:#ffffff;padding:36px 36px 28px;">

            {{-- Title --}}
            <h1 style="margin:0 0 6px;font-size:1.25rem;font-weight:700;color:#0f172a;">
              You have been assigned a ticket
            </h1>
            <p style="margin:0 0 24px;font-size:.9rem;color:#475569;">
              A new ticket has been assigned to you on <strong style="color:#6366f1;">FluxTickets</strong>. Please review the details below and take the necessary action.
            </p>

            {{-- Ticket info card --}}
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f0f4fb;border-radius:8px;border-left:4px solid #6366f1;margin-bottom:24px;">
              <tr>
                <td style="padding:20px 22px;">

                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding:5px 0;width:40%;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Ticket #</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;font-weight:700;">{{ $ticket->ticket_number }}</td>
                    </tr>
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Subject</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;">{{ $ticket->subject }}</td>
                    </tr>
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Submitted By</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;">{{ optional($ticket->user)->name ?? 'N/A' }}</td>
                    </tr>
                    @if($ticket->user && $ticket->user->email)
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Submitter Email</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;">{{ $ticket->user->email }}</td>
                    </tr>
                    @endif
                    @if($ticket->department)
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Department</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;">{{ $ticket->department }}</td>
                    </tr>
                    @endif
                    @if($ticket->category)
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Category</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;">{{ $ticket->category }}</td>
                    </tr>
                    @endif
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Type</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;">{{ $ticket->type ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Priority</td>
                      <td style="padding:5px 0;">
                        @php
                          $priorityColors = ['high'=>'#ef4444','medium'=>'#f59e0b','low'=>'#22c55e'];
                          $pc = $priorityColors[strtolower($ticket->priority)] ?? '#94a3b8';
                        @endphp
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:.78rem;font-weight:700;color:#fff;background:{{ $pc }};">
                          {{ ucfirst($ticket->priority) }}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Status</td>
                      <td style="padding:5px 0;">
                        <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:.78rem;font-weight:700;color:#fff;background:#6366f1;">
                          {{ ucfirst($ticket->status) }}
                        </span>
                      </td>
                    </tr>
                    @if($ticket->sla_due_at)
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">SLA Due</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;">{{ $ticket->sla_due_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    @endif
                    <tr>
                      <td style="padding:5px 0;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Submitted On</td>
                      <td style="padding:5px 0;font-size:.9rem;color:#0f172a;">{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                  </table>

                </td>
              </tr>
            </table>

            {{-- Description --}}
            @if($ticket->description)
            <div style="margin-bottom:28px;">
              <p style="margin:0 0 8px;font-size:.82rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Description</p>
              <p style="margin:0;font-size:.9rem;color:#334155;line-height:1.6;background:#f8fafc;border-radius:6px;padding:14px 16px;border:1px solid #e2e8f0;">
                {{ $ticket->description }}
              </p>
            </div>
            @endif

            {{-- CTA Button --}}
            <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
              <tr>
                <td align="center" style="border-radius:8px;background:linear-gradient(135deg,#6366f1,#7c3aed);">
                  <a href="{{ url('/tickets') }}"
                     style="display:inline-block;padding:13px 32px;font-size:.9rem;font-weight:700;color:#ffffff;text-decoration:none;letter-spacing:.03em;">
                    View Ticket &rarr;
                  </a>
                </td>
              </tr>
            </table>

            <p style="margin:0;font-size:.85rem;color:#64748b;">
              Thanks,<br>
              <strong style="color:#0f172a;">The FluxTickets Team</strong>
            </p>

          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="background:#1e293b;border-radius:0 0 12px 12px;padding:18px 36px;text-align:center;">
            <p style="margin:0 0 4px;font-size:.78rem;color:#94a3b8;">
              You received this email because a ticket was assigned to you on FluxTickets.
            </p>
            <p style="margin:0;font-size:.75rem;color:#64748b;">
              &copy; {{ date('Y') }} FluxTickets. All rights reserved.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
