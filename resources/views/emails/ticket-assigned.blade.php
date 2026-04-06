<x-mail::message>
# You have been assigned a ticket

A ticket has been assigned to you on **{{ config('app.name') }}**.

<x-mail::panel>
**Ticket #:** {{ $ticket->ticket_number }}
**Subject:** {{ $ticket->subject }}
**Type:** {{ $ticket->type ?? 'N/A' }}
**Priority:** {{ ucfirst($ticket->priority) }}
**Status:** {{ ucfirst($ticket->status) }}
</x-mail::panel>

@if($ticket->description)
**Description:**
{{ $ticket->description }}
@endif

<x-mail::button :url="url('/tickets')">
View Ticket
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
