<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/**
 * Authorization rules for tickets.
 *
 * Note: no before() super-admin bypass is used — super admins are allowed
 * explicitly per ability, because the "claim" rule (assign) must block
 * everyone, super admins included, from taking an already-assigned ticket.
 */
class TicketPolicy
{
    /** Statuses where the ticket is finished and most mutations are blocked. */
    private const TERMINAL = ['resolved', 'closed', 'rejected'];

    /** See a ticket: its creator, requester, assignee, or anyone in its department. */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin()
            || $ticket->user_id === $user->id
            || $ticket->requester_id === $user->id
            || $ticket->assignee_id === $user->id
            || ($user->department !== null && $ticket->department === $user->department);
    }

    /** Edit ticket fields (status, priority, resolution): the assigned agent. */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin() || $ticket->assignee_id === $user->id;
    }

    /**
     * Claim an unassigned ticket (the "Accept" / assign-to-me action).
     *
     * Department-scoped: an agent may claim a ticket routed to their own
     * department, or one not yet routed anywhere. A ticket already assigned
     * to someone else cannot be taken — by anyone, super admins included.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        if ($ticket->assignee_id !== null) {
            return false;
        }
        if (in_array($ticket->status, self::TERMINAL, true)) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $ticket->department === null
            || $ticket->department === $user->department;
    }

    /** Route a ticket to another department/agent: the current assignee. */
    public function route(User $user, Ticket $ticket): bool
    {
        if (in_array($ticket->status, self::TERMINAL, true)) {
            return false;
        }

        return $user->isSuperAdmin() || $ticket->assignee_id === $user->id;
    }

    /** Reject a ticket: the assigned agent or the requester. */
    public function reject(User $user, Ticket $ticket): bool
    {
        if (in_array($ticket->status, self::TERMINAL, true)) {
            return false;
        }

        return $user->isSuperAdmin()
            || $ticket->assignee_id === $user->id
            || $ticket->requester_id === $user->id;
    }

    /** Add a note: anyone involved in the ticket (creator, requester, assignee). */
    public function addNote(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin()
            || $ticket->user_id === $user->id
            || $ticket->requester_id === $user->id
            || $ticket->assignee_id === $user->id;
    }
}
