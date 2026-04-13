<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number', 'user_id', 'requester', 'requester_id', 'requester_dept',
        'subject', 'category', 'type', 'priority', 'assignee', 'department', 'description', 'attachment',
        'status', 'sla_due_at', 'resolution', 'resolution_image', 'resolved_by', 'resolved_at',
        'rejected_by', 'rejected_at', 'rejection_reason',
        'routed_to', 'routing_note', 'routed_at',
    ];

    protected $casts = [
        'resolved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'routed_at'    => 'datetime',
        'sla_due_at'   => 'datetime',
    ];

    /** SLA deadline in days per priority */
    public const SLA_DAYS = [
        'high'   => 2,
        'medium' => 3,
        'low'    => 7,
    ];

    /** Compute the SLA due timestamp from a priority */
    public static function slaDeadline(string $priority, \Carbon\Carbon $from = null): \Carbon\Carbon
    {
        $days = static::SLA_DAYS[$priority] ?? 7;
        return ($from ?? now())->copy()->addDays($days);
    }

    /**
     * SLA status: 'met' | 'ok' | 'warning' | 'breached'
     * - met      → ticket resolved/closed within SLA
     * - ok       → open, > 25 % time remaining
     * - warning  → open, ≤ 25 % time remaining
     * - breached → open, past due OR resolved late
     */
    public function getSlaStatusAttribute(): string
    {
        if (!$this->sla_due_at) return 'ok';

        $done = in_array($this->status, ['resolved', 'closed', 'rejected']);
        $compareAt = $done ? ($this->resolved_at ?? $this->rejected_at ?? now()) : now();

        if ($compareAt->gt($this->sla_due_at)) {
            return $done ? 'breached' : 'breached';
        }

        if ($done) return 'met';

        // Warning: less than 25 % of total window left
        $total     = $this->created_at->diffInSeconds($this->sla_due_at);
        $remaining = now()->diffInSeconds($this->sla_due_at);
        return ($remaining / max($total, 1)) <= 0.25 ? 'warning' : 'ok';
    }

    public function getSlaLabelAttribute(): string
    {
        return match($this->sla_status) {
            'met'     => 'Met',
            'ok'      => 'On Track',
            'warning' => 'At Risk',
            'breached'=> 'Breached',
            default   => '—',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notes()
    {
        return $this->hasMany(TicketNote::class)->with('user')->orderBy('created_at');
    }

    public function knowledgeArticles()
    {
        return $this->belongsToMany(KnowledgeArticle::class, 'ticket_knowledge_article');
    }

    public static function generateNumber(string $type = ''): string
    {
        $prefixes = [
            'Service Request' => 'SQR',
            'Change Request'  => 'CRQ',
            'Incident'        => 'ICT',
            'Question'        => 'QTN',
        ];

        $prefix = $prefixes[$type] ?? 'FLX';
        $count  = static::where('type', $type)->count();

        return $prefix . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
