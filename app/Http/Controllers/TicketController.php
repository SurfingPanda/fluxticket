<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TicketNotification;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // ── Helper: create a notification (never notify yourself) ──────────────
    private function notify(int $userId, string $type, \App\Models\Ticket $ticket, string $message): void
    {
        if ($userId === auth()->id()) return;

        TicketNotification::create([
            'user_id'       => $userId,
            'type'          => $type,
            'ticket_id'     => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'message'       => $message,
        ]);
    }

    // ── Helper: find a user by name ────────────────────────────────────────
    private function userByName(?string $name): ?\App\Models\User
    {
        if (!$name) return null;
        return \App\Models\User::where('name', $name)->first();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'     => ['required', 'string', 'max:255'],
            'category'    => ['required', 'string'],
            'type'        => ['nullable', 'string'],
            'priority'    => ['required', 'in:low,medium,high'],
            'assignee'    => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'attachment'  => ['nullable', 'file', 'max:10240'],
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
        }

        $ticket = \App\Models\Ticket::create([
            'ticket_number' => \App\Models\Ticket::generateNumber($data['type'] ?? ''),
            'user_id'       => auth()->id(),
            'subject'       => $data['subject'],
            'category'      => $data['category'],
            'type'          => $data['type'] ?? null,
            'priority'      => $data['priority'],
            'assignee'      => $data['assignee'] ?? null,
            'description'   => $data['description'],
            'attachment'    => $path,
            'status'        => 'open',
            'sla_due_at'    => \App\Models\Ticket::slaDeadline($data['priority']),
        ]);

        // Notify the assigned agent if one was specified
        if (!empty($data['assignee'])) {
            $assignee = $this->userByName($data['assignee']);
            if ($assignee) {
                $this->notify($assignee->id, 'assigned', $ticket,
                    "New ticket {$ticket->ticket_number} has been assigned to you: \"{$ticket->subject}\"");
            }
        }

        return back()->with('success', 'Ticket submitted successfully!');
    }

    public function update(Request $request, \App\Models\Ticket $ticket)
    {
        $data = $request->validate([
            'status'           => ['required', 'in:open,progress,resolved,closed'],
            'priority'         => ['required', 'in:low,medium,high'],
            'assignee'         => ['nullable', 'string', 'max:255'],
            'resolution'       => ['nullable', 'string'],
            'resolution_image' => ['nullable', 'image', 'max:10240'],
        ]);

        $oldStatus   = $ticket->status;
        $oldAssignee = $ticket->assignee;

        // Recalculate SLA if priority changed
        if ($ticket->priority !== $data['priority']) {
            $ticket->sla_due_at = \App\Models\Ticket::slaDeadline($data['priority'], $ticket->created_at);
        }

        $ticket->status     = $data['status'];
        $ticket->priority   = $data['priority'];
        $ticket->assignee   = $data['assignee'] ?? null;
        $ticket->resolution = $data['resolution'] ?? null;

        if ($request->hasFile('resolution_image')) {
            $ticket->resolution_image = $request->file('resolution_image')
                ->store('resolution-images', 'public');
        }

        if (in_array($data['status'], ['resolved', 'closed']) && !$ticket->resolved_at) {
            $ticket->resolved_by = auth()->user()->name;
            $ticket->resolved_at = now();
        }

        $ticket->save();

        // Notify the ticket submitter about the status change
        if ($oldStatus !== $data['status']) {
            $statusLabel = ['open' => 'Open', 'progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];
            $label    = $statusLabel[$data['status']] ?? $data['status'];
            $oldLabel = $statusLabel[$oldStatus] ?? $oldStatus;
            $this->notify($ticket->user_id, 'status_changed', $ticket,
                "Your ticket {$ticket->ticket_number} status changed to \"{$label}\".");

            // Log status change as an activity note
            \App\Models\TicketNote::create([
                'ticket_id' => $ticket->id,
                'user_id'   => auth()->id(),
                'type'      => 'status_change',
                'content'   => "Status changed from **{$oldLabel}** to **{$label}**",
            ]);
        }

        // Notify a newly assigned agent
        if ($data['assignee'] && $data['assignee'] !== $oldAssignee) {
            $assignee = $this->userByName($data['assignee']);
            if ($assignee) {
                $this->notify($assignee->id, 'assigned', $ticket,
                    "Ticket {$ticket->ticket_number} has been assigned to you: \"{$ticket->subject}\"");
            }
        }

        return back()->with('success', "Ticket {$ticket->ticket_number} updated successfully!");
    }

    public function assignToMe(\App\Models\Ticket $ticket)
    {
        $ticket->assignee = auth()->user()->name;
        if ($ticket->status === 'open') {
            $ticket->status = 'progress';
        }
        $ticket->save();

        // Notify the ticket submitter
        $this->notify($ticket->user_id, 'assigned', $ticket,
            "Your ticket {$ticket->ticket_number} has been accepted by " . auth()->user()->name . ".");

        return back()->with('success', "You have been assigned to {$ticket->ticket_number}.");
    }

    public function route(Request $request, \App\Models\Ticket $ticket)
    {
        $data = $request->validate([
            'routed_to'    => ['required', 'string', 'max:255'],
            'department'   => ['required', 'string', 'max:255'],
            'routing_note' => ['nullable', 'string'],
        ]);

        $ticket->routed_to    = $data['routed_to'];
        $ticket->department   = $data['department'];
        $ticket->routing_note = $data['routing_note'] ?? null;
        $ticket->routed_at    = now();
        $ticket->assignee     = $data['routed_to'];
        $ticket->status       = 'progress';
        $ticket->save();

        // Log routing event in notes timeline
        $noteContent = "Routed to **{$data['routed_to']}** ({$data['department']})";
        if (!empty($data['routing_note'])) {
            $noteContent .= "\n> {$data['routing_note']}";
        }
        \App\Models\TicketNote::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'content'   => $noteContent,
            'type'      => 'route_event',
        ]);

        // Notify the agent the ticket was routed to
        $routedUser = $this->userByName($data['routed_to']);
        if ($routedUser) {
            $this->notify($routedUser->id, 'routed', $ticket,
                "Ticket {$ticket->ticket_number} has been assigned to you ({$data['department']}): \"{$ticket->subject}\"");
        }

        // Notify the ticket submitter
        $this->notify($ticket->user_id, 'routed', $ticket,
            "Your ticket {$ticket->ticket_number} has been routed to {$data['routed_to']} ({$data['department']}).");

        return back()->with('success', "Ticket {$ticket->ticket_number} routed to {$data['routed_to']} ({$data['department']}).");
    }

    public function addNote(Request $request, \App\Models\Ticket $ticket)
    {
        $data = $request->validate([
            'content'    => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('note-attachments', 'public');
        }

        $note = \App\Models\TicketNote::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => auth()->id(),
            'content'    => $data['content'],
            'type'       => 'note',
            'attachment' => $path,
        ]);

        $note->load('user');

        // Notify the ticket submitter that a note was added
        $this->notify($ticket->user_id, 'note_added', $ticket,
            "A note was added to your ticket {$ticket->ticket_number} by " . auth()->user()->name . ".");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'note' => $note]);
        }

        return back()->with('success', 'Note added to ticket.');
    }

    public function printView(\App\Models\Ticket $ticket)
    {
        return view('tickets.print', compact('ticket'));
    }

    public function attachKba(\App\Models\Ticket $ticket, \App\Models\KnowledgeArticle $article)
    {
        $alreadyLinked = $ticket->knowledgeArticles()->where('knowledge_article_id', $article->id)->exists();
        $ticket->knowledgeArticles()->syncWithoutDetaching([$article->id]);
        if (!$alreadyLinked) {
            $article->increment('times_used');
        }
        return response()->json([
            'ok'      => true,
            'article' => [
                'id'         => $article->id,
                'kba_number' => $article->kba_number,
                'title'      => $article->title,
                'times_used' => $article->fresh()->times_used,
            ],
        ]);
    }

    public function detachKba(\App\Models\Ticket $ticket, \App\Models\KnowledgeArticle $article)
    {
        $ticket->knowledgeArticles()->detach($article->id);
        return response()->json(['ok' => true]);
    }
}
