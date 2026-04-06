<x-mail::message>
# Ticket Status Updated

Your ticket status has been updated on **{{ config('app.name') }}**.

<x-mail::panel>
**Ticket #:** {{ $ticket->ticket_number }}
**Subject:** {{ $ticket->subject }}
**Type:** {{ $ticket->type ?? 'N/A' }}
**Priority:** {{ ucfirst($ticket->priority) }}
**Status:** ~~{{ ucfirst($oldStatus) }}~~ → **{{ ucfirst($newStatus) }}**
</x-mail::panel>

@if($ticket->resolution)
**Resolution:**
{{ $ticket->resolution }}
@endif

<x-mail::button :url="url('/tickets')">
View Ticket
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
